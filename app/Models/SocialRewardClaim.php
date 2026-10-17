<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SocialRewardClaim extends Model
{
    protected $fillable = [
        'user_id',
        'character_id',
        'platform',
        'reward_cash',
        'reward_respect',
        'reward_energy',
        'ip_address',
        'claimed_at',
    ];

    protected $casts = [
        'claimed_at' => 'datetime',
        'reward_cash' => 'integer',
        'reward_respect' => 'integer',
        'reward_energy' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function character(): BelongsTo
    {
        return $this->belongsTo(Character::class);
    }
}
