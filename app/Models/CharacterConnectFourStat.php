<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CharacterConnectFourStat extends Model
{
    use HasFactory;

    protected $fillable = [
        'character_id',
        'matches_played',
        'current_player_wins',
        'current_arjan_wins',
        'current_draws',
        'total_tournaments_played',
        'total_tournaments_won',
        'total_games_won',
        'total_cash_won',
        'cooldown_until',
    ];

    protected function casts(): array
    {
        return [
            'matches_played' => 'integer',
            'current_player_wins' => 'integer',
            'current_arjan_wins' => 'integer',
            'current_draws' => 'integer',
            'total_tournaments_played' => 'integer',
            'total_tournaments_won' => 'integer',
            'total_games_won' => 'integer',
            'total_cash_won' => 'integer',
            'cooldown_until' => 'datetime',
        ];
    }

    public function character(): BelongsTo
    {
        return $this->belongsTo(Character::class);
    }

    public function isOnCooldown(): bool
    {
        return $this->cooldown_until && $this->cooldown_until->isFuture();
    }

    public function getCooldownSecondsRemaining(): int
    {
        if (! $this->isOnCooldown()) {
            return 0;
        }

        return max(0, (int) ceil(now()->diffInSeconds($this->cooldown_until)));
    }
}
