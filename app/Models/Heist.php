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
            'makkelijk' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
            'normaal' => 'bg-blue-100 text-blue-800 border-blue-200',
            'zwaar' => 'bg-amber-100 text-amber-800 border-amber-200',
            'extreem' => 'bg-rose-100 text-rose-800 border-rose-200',
            'legendarisch' => 'bg-purple-100 text-purple-800 border-purple-200 animate-pulse',
            default => 'bg-slate-100 text-slate-800 border-slate-200',
        };
    }
}
