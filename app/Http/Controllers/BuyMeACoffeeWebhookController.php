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
     * Resolve character from message, note, email, or custom parameter.
     */
    protected function resolveCharacter(array $data): ?Character
    {
        // 1. Search in note or message for TBS-XXXX code
        $message = (string) ($data['support_message'] ?? $data['message'] ?? $data['note'] ?? '');
        if (preg_match('/TBS[-_]?(\d+)/i', $message, $matches)) {
            $charId = (int) $matches[1];
            $char = Character::find($charId);
            if ($char) {
                return $char;
            }
        }

        // 2. Check if message is exactly a character name
        if (! empty($message)) {
            $charByName = Character::where('name', trim($message))->first();
            if ($charByName) {
                return $charByName;
            }
        }

        // 3. Match by payer email
        $payerEmail = $data['payer_email'] ?? $data['email'] ?? null;
        if ($payerEmail) {
            $user = User::where('email', strtolower(trim((string) $payerEmail)))->first();
            if ($user && $user->character) {
                return $user->character;
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

            // A. Single Coffee / Support (Free Donation)
            if (str_contains($eventNormalized, 'support_created')) {
                $coffees = (int) ($data['support_coffees'] ?? 1);
                // Each coffee = 25 Stars in game
                $starsAwarded = max(25, $coffees * 25);
                $locked->stars = ($locked->stars ?? 0) + $starsAwarded;
                $locked->save();

                AuditLogService::log(
                    action: 'buymeacoffee_support',
                    category: 'economy',
                    description: "BuyMeACoffee donatie ontvangen: +{$starsAwarded} Sterren toegekend aan {$locked->name}",
                    character: $locked,
                    user: $locked->user,
                    amount: $starsAwarded,
                    details: $data
                );
            }

            // B. Extras (Shop Items)
            if (str_contains($eventNormalized, 'extra_purchased')) {
                $extraTitle = (string) ($data['extra_title'] ?? $data['extra_name'] ?? '');
                $this->grantExtraReward($locked, $extraTitle, $data);
            }

            // C. Membership Started or Renewed
            if (str_contains($eventNormalized, 'membership_started') || str_contains($eventNormalized, 'membership_updated')) {
                $tierName = (string) ($data['membership_tier_name'] ?? $data['tier_name'] ?? '');
                $this->grantMembershipTier($locked, $tierName, $data);
            }

            // D. Membership Cancelled
            if (str_contains($eventNormalized, 'membership_cancelled') || str_contains($eventNormalized, 'membership_paused')) {
                // Keep VIP until expiry date if provided
                Log::info("BuyMeACoffee: Membership cancelled for {$locked->name}");
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
        } elseif (str_contains($titleLower, '150 ster') || str_contains($titleLower, 'medium')) {
            $character->stars = ($character->stars ?? 0) + 150;
        } elseif (str_contains($titleLower, '500 ster') || str_contains($titleLower, 'groot')) {
            $character->stars = ($character->stars ?? 0) + 500;
        } elseif (str_contains($titleLower, 'koffer') || str_contains($titleLower, 'xxl')) {
            $character->xxl_cases = ($character->xxl_cases ?? 0) + 2;
        } else {
            // Default generic star reward
            $character->stars = ($character->stars ?? 0) + 100;
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
     * Grant VIP and tier perks.
     */
    protected function grantMembershipTier(Character $character, string $tierName, array $data): void
    {
        $tierLower = strtolower($tierName);

        $character->is_vip = true;
        // Extend VIP by 31 days
        $currentExpiry = $character->vip_expires_at && $character->vip_expires_at->isFuture()
            ? $character->vip_expires_at
            : Carbon::now();
        $character->vip_expires_at = $currentExpiry->copy()->addDays(31);

        // Tier bonus stars
        $perks = $character->perks ?? [];
        if (str_contains($tierLower, 'baas') || str_contains($tierLower, 'tier 3') || str_contains($tierLower, 'tycoon')) {
            $character->stars = ($character->stars ?? 0) + 200;
            $character->xxl_cases = ($character->xxl_cases ?? 0) + 1;
            $perks['supporter_tier'] = 3;
        } elseif (str_contains($tierLower, 'vip') || str_contains($tierLower, 'tier 2') || str_contains($tierLower, 'battle')) {
            $character->stars = ($character->stars ?? 0) + 75;
            $perks['supporter_tier'] = 2;
        } else {
            $character->stars = ($character->stars ?? 0) + 25;
            $perks['supporter_tier'] = 1;
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
