<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;

class DirectMessage extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
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

        static::deleted(function (self $model) {
            if ($model->recipient_id) {
                Cache::forget("dm_unread_count_{$model->recipient_id}");
            }
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
