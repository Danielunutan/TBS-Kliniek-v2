<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GangAttack extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $guarded = ['id'];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'won' => 'boolean',
        'attacker_power' => 'integer',
        'target_defense' => 'integer',
        'stolen_cash' => 'integer',
        'defense_damage' => 'integer',
        'power_damage' => 'integer',
        'respect_gain' => 'decimal:2',
    ];

    public function attackerGang(): BelongsTo
    {
        return $this->belongsTo(Gang::class, 'attacker_gang_id');
    }

    public function targetGang(): BelongsTo
    {
        return $this->belongsTo(Gang::class, 'target_gang_id');
    }

    public function character(): BelongsTo
    {
        return $this->belongsTo(Character::class, 'character_id');
    }
}
