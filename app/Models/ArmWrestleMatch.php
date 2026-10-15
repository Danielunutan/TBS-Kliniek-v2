<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArmWrestleMatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'challenger_id',
        'opponent_id',
        'type',
        'npc_key',
        'bet_amount',
        'winner_id',
        'status',
        'xp_earned',
        'respect_earned',
        'challenger_clicks',
        'match_duration_ms',
    ];

    /**
     * @return BelongsTo<Character, $this>
     */
    public function challenger(): BelongsTo
    {
        return $this->belongsTo(Character::class, 'challenger_id');
    }

    /**
     * @return BelongsTo<Character, $this>
     */
    public function opponent(): BelongsTo
    {
        return $this->belongsTo(Character::class, 'opponent_id');
    }

    /**
     * @return BelongsTo<Character, $this>
     */
    public function winner(): BelongsTo
    {
        return $this->belongsTo(Character::class, 'winner_id');
    }
}
