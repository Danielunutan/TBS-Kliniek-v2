<?php

namespace App\Http\Controllers;

use App\Models\Character;
use App\Models\SocialRewardClaim;
use App\Services\WebPushService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PushSubscriptionController extends Controller
{
    public function __construct(
        protected WebPushService $webPushService
    ) {}

    /**
     * Return the VAPID public key.
     */
    public function key(): JsonResponse
    {
        return response()->json([
            'public_key' => $this->webPushService->getPublicKey(),
        ]);
    }

    /**
     * Check if the authenticated user has active subscriptions.
     */
    public function status(Request $request): JsonResponse
    {
        $user = $request->user();
        $count = $user ? $user->pushSubscriptions()->count() : 0;

        return response()->json([
            'subscribed' => $count > 0,
            'count' => $count,
        ]);
    }

    /**
     * Store or refresh a Web Push subscription.
     */
    public function subscribe(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'endpoint' => ['required', 'string'],
            'public_key' => ['nullable', 'string'],
            'auth_token' => ['nullable', 'string'],
            'content_encoding' => ['nullable', 'string'],
        ]);

        $user = $request->user();
        if (! $user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $endpoint = $validated['endpoint'];
        $hash = hash('sha256', $endpoint);

        $subscription = $user->pushSubscriptions()->updateOrCreate(
            ['endpoint_hash' => $hash],
            [
                'endpoint' => $endpoint,
                'public_key' => $validated['public_key'] ?? null,
                'auth_token' => $validated['auth_token'] ?? null,
                'content_encoding' => $validated['content_encoding'] ?? 'aesgcm',
                'device_name' => substr((string) $request->header('User-Agent', 'Onbekend'), 0, 190),
            ]
        );

        $rewardGranted = false;
        $rewardMessage = null;

        $character = $user->character;
        if ($character) {
            DB::transaction(function () use ($user, $character, $request, &$rewardGranted, &$rewardMessage): void {
                /** @var Character|null $lockedCharacter */
                $lockedCharacter = Character::where('id', $character->id)->lockForUpdate()->first();
                if (! $lockedCharacter) {
                    return;
                }

                $alreadyClaimed = SocialRewardClaim::where('user_id', $user->id)
                    ->where('platform', 'push')
                    ->lockForUpdate()
                    ->exists();

                if (! $alreadyClaimed) {
                    $cashBonus = 25000;
                    $respectBonus = 25;
                    $energyBonus = 20;

                    $lockedCharacter->cash += $cashBonus;
                    $lockedCharacter->energy = min($lockedCharacter->max_energy, $lockedCharacter->energy + $energyBonus);
                    $lockedCharacter->addRespect($respectBonus);

                    SocialRewardClaim::create([
                        'user_id' => $user->id,
                        'character_id' => $lockedCharacter->id,
                        'platform' => 'push',
                        'reward_cash' => $cashBonus,
                        'reward_respect' => $respectBonus,
                        'reward_energy' => $energyBonus,
                        'ip_address' => $request->ip(),
                        'claimed_at' => now(),
                    ]);

                    $rewardGranted = true;
                    $rewardMessage = '🎉 Welkomstbeloning: € '.number_format($cashBonus, 0, ',', '.').' contant, '.$respectBonus.' respect en '.$energyBonus.' energie bijgeschreven!';
                }
            });
        }

        return response()->json([
            'success' => true,
            'id' => $subscription->id,
            'message' => $rewardMessage ?: 'Meldingen succesvol ingeschakeld!',
            'reward_granted' => $rewardGranted,
            'reward_message' => $rewardMessage,
        ]);
    }

    /**
     * Unsubscribe an endpoint.
     */
    public function unsubscribe(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $endpoint = $request->input('endpoint');
        if ($endpoint) {
            $hash = hash('sha256', $endpoint);
            $user->pushSubscriptions()->where('endpoint_hash', $hash)->delete();
        } else {
            $user->pushSubscriptions()->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Meldingen uitgeschakeld.',
        ]);
    }

    /**
     * Send an instant test push notification to the authenticated user.
     */
    public function test(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $sent = $this->webPushService->sendToUser(
            $user,
            'TBS Kliniek: Meldingen Actief! 🔔',
            'Top! Je ontvangt nu native notificaties bij diefstallen, aanvallen en berichten, zelfs als je offline bent.',
            '/dashboard'
        );

        if ($sent > 0) {
            return response()->json([
                'success' => true,
                'sent_devices' => $sent,
                'message' => "Testnotificatie succesvol verzonden naar {$sent} apparaat(en)!",
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Geen actief push-abonnement gevonden voor jouw browser. Schakel eerst meldingen in.',
        ], 400);
    }
}
