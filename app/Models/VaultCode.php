<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VaultCode extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'reward_cash' => 'integer',
        'reward_stars' => 'integer',
        'reward_tokens' => 'integer',
        'is_redeemed' => 'boolean',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(Character::class, 'character_id');
    }

    public function redeemedBy(): BelongsTo
    {
        return $this->belongsTo(Character::class, 'redeemed_by_id');
    }
}
