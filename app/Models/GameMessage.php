<?php

namespace App\Models;

use App\Services\WebPushService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GameMessage extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'is_read' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::created(function (GameMessage $gameMsg) {
            $isPlayerDm = $gameMsg->sender_id
                && $gameMsg->type === 'player'
                && ! str_contains($gameMsg->subject ?? '', 'Vriendschapsverzoek')
                && ! str_contains($gameMsg->subject ?? '', 'vriendschapsverzoek');
            $senderId = $isPlayerDm ? $gameMsg->sender_id : null;
            $formattedMessage = (! empty($gameMsg->subject) && ! $isPlayerDm)
                ? "📢 **{$gameMsg->subject}**\n\n{$gameMsg->content}"
                : ($gameMsg->content ?? '');

            if (! $isPlayerDm) {
                $alreadyExists = DirectMessage::where('recipient_id', $gameMsg->recipient_id)
                    ->whereNull('sender_id')
                    ->where('created_at', '>=', now()->subSeconds(3))
                    ->where(function ($q) use ($gameMsg) {
                        $q->where('metadata->game_message_id', $gameMsg->id);
                        $subjectPrefix = trim(substr($gameMsg->subject ?? '', 0, 15));
                        if ($subjectPrefix !== '') {
                            $escapedPrefix = addcslashes($subjectPrefix, '%_\\');
                            $q->orWhere('message', 'like', '%'.$escapedPrefix.'%');
                        }
                    })
                    ->exists();

                if ($alreadyExists) {
                    return;
                }
            }

            DirectMessage::create([
                'sender_id' => $senderId,
                'recipient_id' => $gameMsg->recipient_id,
                'type' => $gameMsg->type ?? ($isPlayerDm ? 'chat' : 'system'),
                'message' => $formattedMessage,
                'metadata' => [
                    'source' => 'system_alert',
                    'subject' => $gameMsg->subject,
                    'original_sender' => $gameMsg->sender_name ?? 'Directie TBS Kliniek',
                    'game_message_id' => $gameMsg->id,
                ],
                'is_read' => (bool) $gameMsg->is_read,
            ]);

            // Dispatch Web Push notification for GameMessage (friend requests, system notices, allowance, marketplace, etc.)
            try {
                $pushTitle = ! empty($gameMsg->subject) ? "TBS Kliniek: {$gameMsg->subject}" : ($gameMsg->sender_name ? "Bericht van {$gameMsg->sender_name}" : 'TBS Kliniek Bericht');
                $subLower = mb_strtolower($gameMsg->subject ?? '');
                $targetUrl = match (true) {
                    str_contains($subLower, 'vriend') => '/vrienden',
                    str_contains($subLower, 'marktplaats') => '/marktplaats',
                    str_contains($subLower, 'zakgeld') || str_contains($subLower, 'bank') => '/bank',
                    str_contains($subLower, 'bende') || str_contains($subLower, 'gang') || $gameMsg->type === 'gang' => '/gangs',
                    str_contains($subLower, 'diefstal') => '/bank',
                    str_contains($subLower, 'gevangenis') || str_contains($subLower, 'isolatie') => '/gevangenis',
                    default => '/chat',
                };

                app(WebPushService::class)->sendToCharacter(
                    $gameMsg->recipient_id,
                    $pushTitle,
                    $gameMsg->content ?? '',
                    $targetUrl,
                    null,
                    ['type' => 'game_message', 'message_id' => $gameMsg->id]
                );
            } catch (\Throwable) {
                // Silently ignore push failure
            }
        });
    }

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(Character::class, 'recipient_id');
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(Character::class, 'sender_id');
    }
}
