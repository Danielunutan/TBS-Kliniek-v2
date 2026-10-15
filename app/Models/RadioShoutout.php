<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RadioShoutout extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'is_anonymous' => 'boolean',
        'likes_count' => 'integer',
    ];

    public function character(): BelongsTo
    {
        return $this->belongsTo(Character::class);
    }

    public function likes(): HasMany
    {
        return $this->hasMany(RadioShoutoutLike::class);
    }

    public function isLikedBy(int $characterId): bool
    {
        return $this->likes()->where('character_id', $characterId)->exists();
    }
}
