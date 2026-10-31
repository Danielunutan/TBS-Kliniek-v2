<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CharacterTrueCrimeState extends Model
{
    use HasFactory;

    protected $fillable = [
        'character_id',
        'current_case_id',
        'solved_case_ids',
        'failed_case_ids',
        'cases_solved_count',
        'cases_failed_count',
        'total_rewards_cash',
        'total_rewards_respect',
        'cooldown_until',
        'cooldown_ads_watched',
    ];

    protected function casts(): array
    {
        return [
            'current_case_id' => 'integer',
            'solved_case_ids' => 'array',
            'failed_case_ids' => 'array',
            'cases_solved_count' => 'integer',
            'cases_failed_count' => 'integer',
            'total_rewards_cash' => 'integer',
            'total_rewards_respect' => 'integer',
            'cooldown_until' => 'datetime',
            'cooldown_ads_watched' => 'integer',
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
