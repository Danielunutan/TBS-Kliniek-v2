<?php

namespace App\Models;

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
