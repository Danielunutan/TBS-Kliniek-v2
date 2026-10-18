<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ControlPointLog extends Model
{
    protected $fillable = [
        'control_point_id',
        'character_id',
        'gang_id',
        'held_from',
        'held_until',
        'duration_minutes',
        'reward_cash',
        'reward_stars',
        'overtaken_by_character_id',
    ];

    protected $casts = [
        'held_from' => 'datetime',
        'held_until' => 'datetime',
        'duration_minutes' => 'integer',
        'reward_cash' => 'integer',
        'reward_stars' => 'integer',
    ];

    public function controlPoint(): BelongsTo
    {
        return $this->belongsTo(ControlPoint::class, 'control_point_id');
    }

    public function character(): BelongsTo
    {
        return $this->belongsTo(Character::class, 'character_id');
    }

    public function gang(): BelongsTo
    {
        return $this->belongsTo(Gang::class, 'gang_id');
    }

    public function overtakenBy(): BelongsTo
    {
        return $this->belongsTo(Character::class, 'overtaken_by_character_id');
    }
}
