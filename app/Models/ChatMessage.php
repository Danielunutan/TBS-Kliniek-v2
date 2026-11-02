<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;

class ChatMessage extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'character_id' => 'integer',
        'reply_to_id' => 'integer',
        'hearts_users' => 'array',
        'hearts_count' => 'integer',
    ];

    protected static function booted(): void
    {
        static::deleted(function (self $model) {
            if ($model->channel) {
                $channel = $model->channel;
                $deleted = Cache::get("chat_deleted_ids_{$channel}", []);
                $deleted[] = (int) $model->id;
                $deleted = array_values(array_slice(array_unique(array_map('intval', $deleted)), -50));
                Cache::put("chat_deleted_ids_{$channel}", $deleted, now()->addHours(2));
            }
        });
    }

    public function character(): BelongsTo
    {
        return $this->belongsTo(Character::class);
    }

    public function replyTo(): BelongsTo
    {
        return $this->belongsTo(self::class, 'reply_to_id');
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
}
