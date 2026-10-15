<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CharacterArmWrestleStat extends Model
{
    use HasFactory;

    protected $fillable = [
        'character_id',
        'wins',
        'losses',
        'current_streak',
        'best_streak',
        'total_profit',
    ];

    /**
     * @return BelongsTo<Character, $this>
     */
    public function character(): BelongsTo
    {
        return $this->belongsTo(Character::class);
    }
}
