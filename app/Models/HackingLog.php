<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HackingLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'character_id',
        'reward_id',
        'reward_label',
        'vip_bonus_applied',
        'vip_bonus_label',
    ];

    protected $casts = [
        'vip_bonus_applied' => 'boolean',
    ];

    public function character(): BelongsTo
    {
        return $this->belongsTo(Character::class);
    }
}
