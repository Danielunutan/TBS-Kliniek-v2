<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CharacterMemoryStat extends Model
{
    use HasFactory;

    protected $fillable = [
        'character_id',
        'games_played',
        'games_won',
        'total_cash_won',
        'best_time_seconds',
        'best_moves',
        'cooldown_until',
    ];

    protected function casts(): array
    {
        return [
            'games_played' => 'integer',
            'games_won' => 'integer',
            'total_cash_won' => 'integer',
            'best_time_seconds' => 'integer',
            'best_moves' => 'integer',
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
