<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;

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
        'active_game',
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
            'active_game' => 'array',
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

    public function getDailyGamesCount(): int
    {
        $today = now()->toDateString();
        $key = 'memory_daily_games_'.$this->character_id.'_'.$today;

        $cached = Cache::get($key);
        if ($cached !== null) {
            return (int) $cached;
        }

        $charPerks = $this->character?->perks ?? [];
        if (($charPerks['memory_daily_date'] ?? null) === $today) {
            $count = (int) ($charPerks['memory_daily_count'] ?? 0);
            Cache::put($key, $count, now()->endOfDay());

            return $count;
        }

        return 0;
    }

    public function incrementDailyGames(): int
    {
        $today = now()->toDateString();
        $key = 'memory_daily_games_'.$this->character_id.'_'.$today;
        $current = $this->getDailyGamesCount();
        $newCount = $current + 1;
        Cache::put($key, $newCount, now()->endOfDay());

        $char = $this->character;
        if ($char) {
            $perks = $char->perks ?? [];
            $perks['memory_daily_date'] = $today;
            $perks['memory_daily_count'] = $newCount;
            $char->perks = $perks;
            $char->save();
        }

        return $newCount;
    }

    public function hasReachedDailyLimit(int $limit = 30): bool
    {
        return $this->getDailyGamesCount() >= $limit;
    }
}
