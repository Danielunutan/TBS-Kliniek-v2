<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Heist extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'min_level' => 'integer',
        'energy_cost' => 'integer',
        'required_mask_count' => 'integer',
        'min_speed_required' => 'integer',
        'min_condition_required' => 'integer',
        'payout_min' => 'integer',
        'payout_max' => 'integer',
        'xp_reward' => 'integer',
        'respect_reward' => 'integer',
        'isolation_risk_minutes' => 'integer',
    ];

    public function logs(): HasMany
    {
        return $this->hasMany(HeistLog::class);
    }

    public function getDifficultyBadgeAttribute(): string
    {
        return match ($this->difficulty) {
            'makkelijk' => 'bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800/60',
            'normaal' => 'bg-blue-100 dark:bg-blue-950/60 text-blue-800 dark:text-blue-300 border-blue-200 dark:border-blue-800/60',
            'zwaar' => 'bg-amber-100 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 border-amber-200 dark:border-amber-800/60',
            'extreem' => 'bg-rose-100 dark:bg-rose-950/60 text-rose-800 dark:text-rose-300 border-rose-200 dark:border-rose-800/60',
            'legendarisch' => 'bg-purple-100 dark:bg-purple-950/60 text-purple-800 dark:text-purple-300 border-purple-200 dark:border-purple-800/60 animate-pulse',
            default => 'bg-slate-100 dark:bg-slate-800 text-slate-800 dark:text-slate-300 border-slate-200 dark:border-slate-700',
        };
    }
}
