<?php

namespace App\Models;

use App\Services\WebPushService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class DirectMessage extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'sender_id' => 'integer',
        'recipient_id' => 'integer',
        'reply_to_id' => 'integer',
        'metadata' => 'array',
        'is_read' => 'boolean',
        'read_at' => 'datetime',
        'hearts_users' => 'array',
        'hearts_count' => 'integer',
    ];

    protected static function booted(): void
    {
        static::saved(function (self $model) {
            if ($model->recipient_id) {
                Cache::forget("dm_unread_count_{$model->recipient_id}");
            }
        });

        static::created(function (self $model) {
            // Player-to-player DM push notification (exclude system notifications already handled by GameNotification)
            if ($model->sender_id && $model->recipient_id) {
                try {
                    $senderName = $model->sender?->name ?? 'Een speler';
                    $preview = Str::limit($model->message, 80);
                    app(WebPushService::class)->sendToCharacter(
                        $model->recipient_id,
                        "Nieuw bericht van {$senderName}",
                        $preview,
                        '/chat',
                        null,
                        ['type' => 'chat_dm', 'dm_id' => $model->id]
                    );
                } catch (\Throwable) {
                    // Silently ignore so message sending never breaks
                }
            }
        });

        static::deleted(function (self $model) {
            if ($model->recipient_id) {
                Cache::forget("dm_unread_count_{$model->recipient_id}");
            }
            $senderId = (int) ($model->sender_id ?? 0);
            $recipientId = (int) $model->recipient_id;
            $convKey = min($senderId, $recipientId).'_'.max($senderId, $recipientId);
            $deleted = Cache::get("dm_deleted_ids_{$convKey}", []);
            $deleted[] = (int) $model->id;
            $deleted = array_values(array_slice(array_unique(array_map('intval', $deleted)), -50));
            Cache::put("dm_deleted_ids_{$convKey}", $deleted, now()->addHours(2));
        });
    }

    public function replyTo(): BelongsTo
    {
        return $this->belongsTo(self::class, 'reply_to_id');
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(Character::class, 'sender_id');
    }

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(Character::class, 'recipient_id');
    }

    /**
     * Get the names of the characters who reacted with a purple heart.
     *
     * @return array<int, string>
     */
    public function getHeartNames(): array
    {
        $ids = is_array($this->hearts_users) ? $this->hearts_users : [];
        if (empty($ids)) {
            return [];
        }

        $namesMap = Character::whereIn('id', $ids)->pluck('name', 'id')->all();
        $names = [];
        foreach ($ids as $id) {
            if (isset($namesMap[$id])) {
                $names[] = $namesMap[$id];
            }
        }

        return $names;
    }

    /**
     * Send official system / clinic notification DM to a character.
     */
    public static function sendSystem(int|Character $recipient, string $message, string $type = 'system', ?array $metadata = null): self
    {
        $recipientId = $recipient instanceof Character ? $recipient->id : $recipient;

        return self::create([
            'sender_id' => null,
            'recipient_id' => $recipientId,
            'type' => $type,
            'message' => $message,
            'metadata' => $metadata ?? ['source' => 'system_alert'],
            'is_read' => false,
        ]);
    }
}
