<?php

namespace App\Services;

use App\Models\Character;
use App\Models\PushSubscription;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;
use Throwable;

class WebPushService
{
    protected ?WebPush $webPush = null;

    public function __construct()
    {
        $subject = config('webpush.vapid.subject');
        $publicKey = config('webpush.vapid.public_key');
        $privateKey = config('webpush.vapid.private_key');

        if ($subject && $publicKey && $privateKey) {
            try {
                $this->webPush = new WebPush([
                    'VAPID' => [
                        'subject' => $subject,
                        'publicKey' => $publicKey,
                        'privateKey' => $privateKey,
                    ],
                ]);
                $this->webPush->setAutomaticPadding(false);
            } catch (Throwable $e) {
                Log::warning('WebPush initialization failed: '.$e->getMessage());
                $this->webPush = null;
            }
        }
    }

    /**
     * Get the VAPID public key for frontend push subscription.
     */
    public function getPublicKey(): ?string
    {
        return config('webpush.vapid.public_key');
    }

    /**
     * Dispatch a Web Push notification to all active devices of a Character's owner.
     */
    public function sendToCharacter(
        Character|int $character,
        string $title,
        string $body,
        ?string $url = null,
        ?string $icon = null,
        array $extra = []
    ): int {
        $char = is_int($character) ? Character::find($character) : $character;
        if (! $char || ! $char->user_id) {
            return 0;
        }

        $user = User::find($char->user_id);
        if (! $user) {
            return 0;
        }

        return $this->sendToUser($user, $title, $body, $url, $icon, $extra);
    }

    /**
     * Dispatch a Web Push notification to all active devices of a User.
     */
    public function sendToUser(
        User $user,
        string $title,
        string $body,
        ?string $url = null,
        ?string $icon = null,
        array $extra = []
    ): int {
        if (! $this->webPush) {
            return 0;
        }

        $subscriptions = PushSubscription::where('user_id', $user->id)->get();
        if ($subscriptions->isEmpty()) {
            return 0;
        }

        $cleanBody = strip_tags($body);
        // Replace markdown bold/italic
        $cleanBody = preg_replace('/[*_`#]/', '', $cleanBody);
        $cleanBody = trim((string) preg_replace('/\s+/', ' ', $cleanBody));

        // Deduplication: prevent sending identical push notification to same user within 6 seconds
        $dedupKey = 'webpush_dedup_'.$user->id.'_'.md5(mb_strtolower($title).'|'.mb_substr($cleanBody, 0, 40));
        if (! Cache::add($dedupKey, 1, now()->addSeconds(6))) {
            return 0;
        }

        $payload = json_encode([
            'title' => $title,
            'body' => $cleanBody,
            'icon' => $icon ?: config('webpush.defaults.icon', '/assets/icons/icon-192x192.png'),
            'badge' => config('webpush.defaults.badge', '/assets/icons/icon-192x192.png'),
            'data' => array_merge([
                'url' => $url ?: '/dashboard',
                'timestamp' => now()->timestamp,
            ], $extra),
        ]);

        $sentCount = 0;
        $staleIds = [];

        foreach ($subscriptions as $sub) {
            try {
                $webPushSub = Subscription::create([
                    'endpoint' => $sub->endpoint,
                    'publicKey' => $sub->public_key,
                    'authToken' => $sub->auth_token,
                    'contentEncoding' => $sub->content_encoding ?: 'aesgcm',
                ]);

                $report = $this->webPush->sendOneNotification($webPushSub, $payload);

                if ($report->isSuccess()) {
                    $sentCount++;
                } else {
                    $statusCode = $report->getResponse()?->getStatusCode();
                    // 404 Not Found or 410 Gone indicates the browser/user revoked permissions
                    if ($statusCode === 404 || $statusCode === 410 || $report->isSubscriptionExpired()) {
                        $staleIds[] = $sub->id;
                    }
                }
            } catch (Throwable $e) {
                Log::debug('Push notification dispatch error: '.$e->getMessage(), ['sub_id' => $sub->id]);
            }
        }

        // Clean up expired subscriptions
        if (! empty($staleIds)) {
            PushSubscription::whereIn('id', $staleIds)->delete();
        }

        return $sentCount;
    }
}
