<?php

namespace App\Models;

use App\Services\WebPushService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class GameNotification
 *
 * @property int $id
 * @property int $character_id
 * @property string $type
 * @property string $icon
 * @property string $title
 * @property string $message
 * @property bool $is_read
 */
class GameNotification extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $guarded = ['id'];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'is_read' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::created(function (GameNotification $notification) {
            $emoji = match ($notification->type) {
                'theft' => '🚨',
                'attack' => '⚔️',
                'bounty_placed', 'bounty_claimed' => '🎯',
                'prison', 'isolation' => '🔒',
                'level' => '⭐',
                'gang' => '🛡️',
                'reward', 'daily_reward' => '🎁',
                default => '📢',
            };

            $title = ! empty($notification->title) ? "**{$notification->title}**\n\n" : '';
            $message = "{$emoji} {$title}{$notification->message}";

            DirectMessage::create([
                'sender_id' => null,
                'recipient_id' => $notification->character_id,
                'type' => $notification->type ?? 'system',
                'message' => $message,
                'metadata' => [
                    'source' => 'game_notification',
                    'icon' => $notification->icon,
                    'title' => $notification->title,
                    'game_notification_id' => $notification->id,
                ],
                'is_read' => (bool) $notification->is_read,
            ]);

            // Dispatch native Web Push notification to desktop and mobile webapp
            try {
                $targetUrl = match ($notification->type) {
                    'theft' => '/bank',
                    'attack' => '/luchtplaats',
                    'bounty_placed', 'bounty_claimed' => '/huurmoordenaar',
                    'prison', 'isolation' => '/gevangenis',
                    'gang' => '/gangs',
                    'reward', 'daily_reward' => '/dashboard',
                    default => '/dashboard',
                };

                $pushTitle = ! empty($notification->title) ? "TBS Kliniek: {$notification->title}" : 'TBS Kliniek Melding';
                app(WebPushService::class)->sendToCharacter(
                    $notification->character_id,
                    $pushTitle,
                    $notification->message,
                    $targetUrl,
                    null,
                    ['type' => $notification->type, 'notification_id' => $notification->id]
                );
            } catch (\Throwable) {
                // Silently ignore so push dispatch never affects gameplay
            }
        });
    }

    public function character(): BelongsTo
    {
        return $this->belongsTo(Character::class);
    }
}
