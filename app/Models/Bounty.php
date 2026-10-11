<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Bounty extends Model
{
    use HasFactory;

    protected $fillable = [
        'target_character_id',
        'placed_by_character_id',
        'amount',
        'reason',
        'is_claimed',
        'claimed_by_character_id',
        'claimed_at',
    ];

    protected $casts = [
        'amount' => 'integer',
        'is_claimed' => 'boolean',
        'claimed_at' => 'datetime',
    ];

    public function target(): BelongsTo
    {
        return $this->belongsTo(Character::class, 'target_character_id');
    }

    public function placedBy(): BelongsTo
    {
        return $this->belongsTo(Character::class, 'placed_by_character_id');
    }

    public function claimedBy(): BelongsTo
    {
        return $this->belongsTo(Character::class, 'claimed_by_character_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_claimed', false);
    }
}
