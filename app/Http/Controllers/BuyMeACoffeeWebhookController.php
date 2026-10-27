<?php

namespace App\Http\Controllers;

use App\Models\Character;
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

        // Try to identify the player
        $character = $this->resolveCharacter($data);

        if ($character) {
            $this->processEvent($event, $data, $character);
        } else {
            Log::warning('BuyMeACoffee Webhook: Player could not be matched automatically', [
                'event' => $event,
                'data' => $data,
            ]);
        }

        return response()->json([
            'status' => 'success',
            'matched' => $character !== null,
        ], 200);
    }

    /**
     * Resolve character from message, note, email, questions or custom fields.
     */
    protected function resolveCharacter(array $data): ?Character
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
    protected function processEvent(string $event, array $data, Character $character): void
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
                // Check if refunded
                if (str_contains($eventNormalized, 'refunded')) {
                    Log::info("BuyMeACoffee: Donation refunded for {$locked->name}", $data);

                    return;
                }

                $coffees = (int) ($data['support_coffees'] ?? $data['quantity'] ?? 1);
                $amount = (float) ($data['amount'] ?? $data['total_amount'] ?? 0);

                // Tiered rewards based on number of coffees or total donation amount
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
                    Log::info("BuyMeACoffee: Membership cancelled or paused for {$locked->name}");

                    return;
                }

                $tierName = (string) ($data['membership_tier_name'] ?? $data['tier_name'] ?? $data['level_name'] ?? '');
                $this->grantMembershipTier($locked, $tierName, $data);
            }
        });
    }

    /**
     * Grant rewards for an Extra purchase.
     */
    protected function grantExtraReward(Character $character, string $title, array $data): void
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
            // Default generic star reward
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
    protected function grantMembershipTier(Character $character, string $tierName, array $data): void
    {
        $tierLower = strtolower($tierName);
        $character->is_vip = true;
        $perks = $character->perks ?? [];

        // Check if Lifetime or recurring monthly
        if (str_contains($tierLower, 'lifetime')) {
            $character->vip_expires_at = Carbon::now()->addYears(99);
            $character->stars = ($character->stars ?? 0) + 1500;
            $character->xxl_cases = ($character->xxl_cases ?? 0) + 10;
            $character->masks = ($character->masks ?? 0) + 30;
            $perks['supporter_tier'] = 4;
            $perks['has_custom_name_color'] = true;
            $perks['has_sparkle_effect'] = true;
            $perks['hourly_cash_bonus'] = 50000;
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
        }

        $character->perks = $perks;
        $character->save();

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
