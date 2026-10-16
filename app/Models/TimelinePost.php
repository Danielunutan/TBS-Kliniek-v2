<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TimelinePost extends Model
{
    use HasFactory;

    protected $fillable = [
        'character_id',
        'content',
        'likes_count',
    ];

    public function character(): BelongsTo
    {
        return $this->belongsTo(Character::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(TimelineComment::class)->with(['character.user', 'character.gang'])->latest();
    }

    public function likes(): HasMany
    {
        return $this->hasMany(TimelineLike::class);
    }

    public function isLikedBy(Character $character): bool
    {
        if ($this->relationLoaded('likes')) {
            return $this->likes->contains('character_id', $character->id);
        }

        return $this->likes()->where('character_id', $character->id)->exists();
    }

    public function likedEmojiBy(Character $character): ?string
    {
        if ($this->relationLoaded('likes')) {
            return $this->likes->firstWhere('character_id', $character->id)?->emoji;
        }

        return $this->likes()->where('character_id', $character->id)->value('emoji');
    }
}
