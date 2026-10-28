<?php

namespace App\Http\Controllers;

use App\Models\BuyMeACoffeeOrder;
use App\Models\Character;
use App\Models\GameNotification;
use App\Models\User;
use App\Services\AuditLogService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BuyMeACoffeeWebhookController extends Controller
{
    /**
     * Handle incoming Buy Me a Coffee webhook notifications.
     */
    public function handle(Request $request): JsonResponse
    {
        $secret = config('services.buymeacoffee.webhook_secret');
        $signature = $request->header('x-signature-sha256') ?? $request->header('X-Signature-Sha256');

        if (! empty($secret) && ! empty($signature)) {
            $rawContent = (string) $request->getContent();
            $expected = hash_hmac('sha256', $rawContent, (string) $secret);
            if (! hash_equals($expected, (string) $signature)) {
                Log::warning('BuyMeACoffee Webhook: Signature mismatch', [
                    'received' => $signature,
                    'expected' => $expected,
                ]);

                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid signature',
                ], 403);
            }
        }

        $payload = $request->all();
        Log::info('BuyMeACoffee Webhook received', [
            'headers' => $request->headers->all(),
            'payload' => $payload,
        ]);

        $event = $payload['event'] ?? $payload['type'] ?? 'unknown';
        $data = $payload['data'] ?? $payload['response'] ?? $payload;
        $eventNormalized = strtolower(str_replace(['.', ' '], '_', (string) $event));

        // Extract metadata from BMC payload
        $externalId = (string) ($data['subscription_id'] ?? $data['sub_id'] ?? $data['membership_id'] ?? $data['support_id'] ?? $data['id'] ?? '');
        $supporterName = (string) ($data['supporter_name'] ?? $data['payer_name'] ?? $data['name'] ?? '');
        $supporterEmail = strtolower(trim((string) ($data['supporter_email'] ?? $data['payer_email'] ?? $data['email'] ?? '')));
        $amount = (float) ($data['amount'] ?? $data['total_amount'] ?? $data['item_price'] ?? 0);
        $currency = (string) ($data['currency'] ?? 'EUR');
        $tierName = (string) ($data['membership_tier_name'] ?? $data['tier_name'] ?? $data['level_name'] ?? '');
        $isMembership = str_contains($eventNormalized, 'membership') || str_contains($eventNormalized, 'recurring') || ! empty($tierName);
        $message = (string) ($data['support_message'] ?? $data['message'] ?? $data['note'] ?? '');

        // Try to identify the player
        $character = $this->resolveCharacter($data);

        // Check if this is a cancellation or pause
        $isCancelled = str_contains($eventNormalized, 'cancelled') || str_contains($eventNormalized, 'paused') || str_contains($eventNormalized, 'deleted');

        if ($isCancelled) {
            $existingOrder = ! empty($externalId) ? BuyMeACoffeeOrder::where('external_id', $externalId)->first() : null;
            if (! $character && $existingOrder && $existingOrder->character) {
                $character = $existingOrder->character;
            }

            if ($character) {
                $this->revokeMembership($character, $tierName, $data);
            }

            BuyMeACoffeeOrder::updateOrCreate(
                ['external_id' => $externalId ?: ('cancelled_'.uniqid())],
                [
                    'event' => $event,
                    'supporter_name' => $supporterName ?: null,
                    'supporter_email' => $supporterEmail ?: null,
                    'amount' => $amount,
                    'currency' => $currency,
                    'membership_tier' => $tierName ?: ($existingOrder?->membership_tier),
                    'message' => $message ?: null,
                    'character_id' => $character?->id,
                    'status' => 'cancelled',
                    'is_membership' => true,
                    'membership_status' => 'cancelled',
                    'payload' => $payload,
                    'cancelled_at' => now(),
                ]
            );

            return response()->json([
                'status' => 'success',
                'action' => 'membership_cancelled',
                'matched' => $character !== null,
            ], 200);
        }

        // Normal Order / Donation / Membership
        $order = BuyMeACoffeeOrder::updateOrCreate(
            ['external_id' => $externalId ?: ('order_'.uniqid())],
            [
                'event' => $event,
                'supporter_name' => $supporterName ?: null,
                'supporter_email' => $supporterEmail ?: null,
                'amount' => $amount,
                'currency' => $currency,
                'membership_tier' => $tierName ?: null,
                'message' => $message ?: null,
                'character_id' => $character?->id,
                'status' => $character ? 'matched' : 'unmatched',
                'is_membership' => $isMembership,
                'membership_status' => $isMembership ? 'active' : null,
                'payload' => $payload,
                'linked_at' => $character ? now() : null,
            ]
        );

        if ($character) {
            $this->processEvent($event, $data, $character);
        } else {
            Log::warning('BuyMeACoffee Webhook: Player could not be matched automatically', [
                'event' => $event,
                'data' => $data,
                'order_id' => $order->id,
            ]);
        }

        return response()->json([
            'status' => 'success',
            'order_id' => $order->id,
            'matched' => $character !== null,
        ], 200);
    }

    /**
     * Resolve character from message, note, email, questions or custom fields.
     */
    public function resolveCharacter(array $data): ?Character
    {
        // Gather all potential text fields
        $textPool = [];
        foreach (['support_message', 'message', 'note', 'question', 'answers', 'custom_fields'] as $key) {
            if (! empty($data[$key])) {
                $val = is_array($data[$key]) ? json_encode($data[$key]) : (string) $data[$key];
                $textPool[] = $val;
            }
        }
        $fullText = implode(' ', $textPool);

        // 1. Search for TBS-XXXX code
        if (preg_match('/TBS[-_]?(\d+)/i', $fullText, $matches)) {
            $charId = (int) $matches[1];
            $char = Character::find($charId);
            if ($char) {
                return $char;
            }
        }

        // 2. Check if text matches a character name directly
        foreach ($textPool as $entry) {
            $trimmed = trim($entry);
            if (! empty($trimmed)) {
                $charByName = Character::where('name', $trimmed)->first();
                if ($charByName) {
                    return $charByName;
                }
            }
        }

        // 3. Match by supporter name or payer name
        foreach (['supporter_name', 'payer_name', 'name'] as $nameKey) {
            if (! empty($data[$nameKey])) {
                $name = trim((string) $data[$nameKey]);
                $charByName = Character::where('name', $name)->first();
                if ($charByName) {
                    return $charByName;
                }
            }
        }

        // 4. Match by payer or supporter email
        foreach (['payer_email', 'supporter_email', 'email'] as $emailKey) {
            if (! empty($data[$emailKey])) {
                $email = strtolower(trim((string) $data[$emailKey]));
                $user = User::where('email', $email)->first();
                if ($user && $user->character) {
                    return $user->character;
                }
            }
        }

        return null;
    }

    /**
     * Process different BMC events.
     */
    public function processEvent(string $event, array $data, Character $character): void
    {
        $eventNormalized = strtolower(str_replace(['.', ' '], '_', $event));

        DB::transaction(function () use ($eventNormalized, $data, $character): void {
            /** @var Character $locked */
            $locked = Character::where('id', $character->id)->lockForUpdate()->first();
            if (! $locked) {
                return;
            }

            // A. Single Coffee / Donation / Support
            if (str_contains($eventNormalized, 'donation') || str_contains($eventNormalized, 'support')) {
                if (str_contains($eventNormalized, 'refunded')) {
                    Log::info("BuyMeACoffee: Donation refunded for {$locked->name}", $data);

                    return;
                }

                $coffees = (int) ($data['support_coffees'] ?? $data['quantity'] ?? 1);
                $amount = (float) ($data['amount'] ?? $data['total_amount'] ?? 0);

                if ($coffees >= 5 || $amount >= 15.0) {
                    $starsAwarded = 160;
                    $masksAwarded = 6;
                    $xxlAwarded = 2;
                    $bulletsAwarded = 500;
                } elseif ($coffees >= 3 || $amount >= 9.0) {
                    $starsAwarded = 85;
                    $masksAwarded = 3;
                    $xxlAwarded = 1;
                    $bulletsAwarded = 200;
                } else {
                    $count = max(1, $coffees);
                    $starsAwarded = $amount > 0 ? max(25, (int) round($amount * 10)) : $count * 25;
                    $masksAwarded = $count;
                    $xxlAwarded = 0;
                    $bulletsAwarded = 50 * $count;
                }

                $locked->stars = ($locked->stars ?? 0) + $starsAwarded;
                $locked->masks = ($locked->masks ?? 0) + $masksAwarded;
                $locked->bullets = ($locked->bullets ?? 0) + $bulletsAwarded;
                if ($xxlAwarded > 0) {
                    $locked->xxl_cases = ($locked->xxl_cases ?? 0) + $xxlAwarded;
                }

                $perks = $locked->perks ?? [];
                $perks['is_donator'] = true;
                $locked->perks = $perks;

                $locked->save();

                GameNotification::create([
                    'character_id' => $locked->id,
                    'type' => 'bmac_donation',
                    'icon' => 'fa-mug-hot',
                    'title' => 'Buy Me a Coffee Donatie Ontvangen!',
                    'message' => "Bedankt voor je gulle donatie! Je hebt direct +{$starsAwarded} Sterren, +{$masksAwarded} Maskers en +{$bulletsAwarded} Kogels ontvangen.",
                    'is_read' => false,
                ]);

                AuditLogService::log(
                    action: 'buymeacoffee_donation',
                    category: 'economy',
                    description: "BuyMeACoffee donatie ontvangen: +{$starsAwarded} Sterren, +{$masksAwarded} Maskers, +{$bulletsAwarded} Kogels".($xxlAwarded > 0 ? ", +{$xxlAwarded} XXL Zaak Koffers" : '')." toegekend aan {$locked->name}",
                    character: $locked,
                    user: $locked->user,
                    amount: $starsAwarded,
                    details: $data
                );
            }

            // B. Extras (Shop Items)
            if (str_contains($eventNormalized, 'extra')) {
                if (str_contains($eventNormalized, 'refunded')) {
                    Log::info("BuyMeACoffee: Extra refunded for {$locked->name}", $data);

                    return;
                }

                $extraTitle = (string) ($data['extra_title'] ?? $data['extra_name'] ?? $data['title'] ?? '');
                $this->grantExtraReward($locked, $extraTitle, $data);
            }

            // C. Membership Started or Renewed
            if (str_contains($eventNormalized, 'membership') || str_contains($eventNormalized, 'recurring')) {
                if (str_contains($eventNormalized, 'cancelled') || str_contains($eventNormalized, 'paused')) {
                    $this->revokeMembership($locked, (string) ($data['membership_tier_name'] ?? ''), $data);

                    return;
                }

                $tierName = (string) ($data['membership_tier_name'] ?? $data['tier_name'] ?? $data['level_name'] ?? '');
                $this->grantMembershipTier($locked, $tierName, $data);
            }
        });
    }

    /**
     * Revoke membership and expire VIP for a character.
     */
    public function revokeMembership(Character $character, string $tierName = '', array $data = []): void
    {
        DB::transaction(function () use ($character, $data): void {
            /** @var Character $locked */
            $locked = Character::where('id', $character->id)->lockForUpdate()->first();
            if (! $locked) {
                return;
            }

            $locked->is_vip = false;
            $locked->vip_expires_at = now();
            $perks = $locked->perks ?? [];
            $perks['supporter_tier'] = 0;
            $perks['has_custom_name_color'] = false;
            $perks['has_sparkle_effect'] = false;
            $locked->perks = $perks;
            $locked->save();

            GameNotification::create([
                'character_id' => $locked->id,
                'type' => 'vip_expired',
                'icon' => 'fa-crown',
                'title' => 'Buy Me a Coffee Membership Opgezegd',
                'message' => 'Je supporterslidmaatschap is beëindigd of stopgezet. Je VIP status en privileges zijn per direct verlopen. Heel erg bedankt voor je eerdere support!',
                'is_read' => false,
            ]);

            AuditLogService::log(
                action: 'buymeacoffee_membership_cancelled',
                category: 'vip',
                description: "BuyMeACoffee Membership opgezegd voor {$locked->name}, VIP privileges beëindigd",
                character: $locked,
                user: $locked->user,
                details: $data
            );
        });
    }

    /**
     * Grant rewards for an Extra purchase.
     */
    public function grantExtraReward(Character $character, string $title, array $data = []): void
    {
        $titleLower = strtolower($title);

        if (str_contains($titleLower, '50 ster') || str_contains($titleLower, 'starter')) {
            $character->stars = ($character->stars ?? 0) + 50;
            $character->masks = ($character->masks ?? 0) + 2;
        } elseif (str_contains($titleLower, '150 ster') || str_contains($titleLower, 'medium')) {
            $character->stars = ($character->stars ?? 0) + 150;
            $character->masks = ($character->masks ?? 0) + 5;
        } elseif (str_contains($titleLower, '500 ster') || str_contains($titleLower, 'groot')) {
            $character->stars = ($character->stars ?? 0) + 500;
            $character->masks = ($character->masks ?? 0) + 15;
            $character->xxl_cases = ($character->xxl_cases ?? 0) + 2;
        } elseif (str_contains($titleLower, 'koffer') || str_contains($titleLower, 'xxl')) {
            $character->xxl_cases = ($character->xxl_cases ?? 0) + 2;
            $character->masks = ($character->masks ?? 0) + 3;
        } else {
            $character->stars = ($character->stars ?? 0) + 100;
            $character->masks = ($character->masks ?? 0) + 3;
        }

        $character->save();

        AuditLogService::log(
            action: 'buymeacoffee_extra',
            category: 'economy',
            description: "BuyMeACoffee Extra gekocht ({$title}) door {$character->name}",
            character: $character,
            user: $character->user,
            details: $data
        );
    }

    /**
     * Grant VIP and tier perks based on the 4 live membership packages.
     */
    public function grantMembershipTier(Character $character, string $tierName, array $data = []): void
    {
        $tierLower = strtolower($tierName);
        $character->is_vip = true;
        $perks = $character->perks ?? [];

        if (str_contains($tierLower, 'lifetime')) {
            $character->vip_expires_at = Carbon::now()->addYears(99);
            $character->stars = ($character->stars ?? 0) + 1500;
            $character->xxl_cases = ($character->xxl_cases ?? 0) + 10;
            $character->masks = ($character->masks ?? 0) + 30;
            $perks['supporter_tier'] = 4;
            $perks['has_custom_name_color'] = true;
            $perks['has_sparkle_effect'] = true;
            $perks['hourly_cash_bonus'] = 50000;
            $tierLabel = 'VIP LIFETIME';
        } elseif (str_contains($tierLower, 'baas')) {
            $currentExpiry = $character->vip_expires_at && $character->vip_expires_at->isFuture()
                ? $character->vip_expires_at
                : Carbon::now();
            $character->vip_expires_at = $currentExpiry->copy()->addDays(31);
            $character->stars = ($character->stars ?? 0) + 600;
            $character->xxl_cases = ($character->xxl_cases ?? 0) + 3;
            $character->masks = ($character->masks ?? 0) + 12;
            $perks['supporter_tier'] = 3;
            $perks['has_custom_name_color'] = true;
            $perks['has_sparkle_effect'] = true;
            $perks['hourly_cash_bonus'] = 20000;
            $tierLabel = 'TBS Kliniek Baas';
        } elseif (str_contains($tierLower, 'elite') || str_contains($tierLower, 'gangster')) {
            $currentExpiry = $character->vip_expires_at && $character->vip_expires_at->isFuture()
                ? $character->vip_expires_at
                : Carbon::now();
            $character->vip_expires_at = $currentExpiry->copy()->addDays(31);
            $character->stars = ($character->stars ?? 0) + 250;
            $character->xxl_cases = ($character->xxl_cases ?? 0) + 1;
            $character->masks = ($character->masks ?? 0) + 5;
            $perks['supporter_tier'] = 2;
            $perks['has_custom_name_color'] = true;
            $perks['has_sparkle_effect'] = false;
            $perks['hourly_cash_bonus'] = 7500;
            $tierLabel = 'TBS Gangster Elite';
        } else {
            // TBS V.I.P. Lid (Tier 1)
            $currentExpiry = $character->vip_expires_at && $character->vip_expires_at->isFuture()
                ? $character->vip_expires_at
                : Carbon::now();
            $character->vip_expires_at = $currentExpiry->copy()->addDays(31);
            $character->stars = ($character->stars ?? 0) + 100;
            $character->masks = ($character->masks ?? 0) + 2;
            $perks['supporter_tier'] = 1;
            $perks['has_custom_name_color'] = false;
            $perks['has_sparkle_effect'] = false;
            $perks['hourly_cash_bonus'] = 2500;
            $tierLabel = 'TBS V.I.P. Lid';
        }

        $character->perks = $perks;
        $character->save();

        GameNotification::create([
            'character_id' => $character->id,
            'type' => 'vip_membership',
            'icon' => 'fa-crown',
            'title' => "Buy Me a Coffee: {$tierLabel} Geactiveerd!",
            'message' => "Gefeliciteerd! Je {$tierLabel} membership is succesvol geactiveerd tot {$character->vip_expires_at->format('d-m-Y H:i')}. Geniet van je VIP privileges en beloningen!",
            'is_read' => false,
        ]);

        AuditLogService::log(
            action: 'buymeacoffee_membership',
            category: 'vip',
            description: "BuyMeACoffee Membership geactiveerd ({$tierName}) voor {$character->name}",
            character: $character,
            user: $character->user,
            details: $data
        );
    }
}
