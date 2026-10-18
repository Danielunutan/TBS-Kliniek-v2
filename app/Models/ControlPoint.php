<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ControlPoint extends Model
{
    protected $fillable = [
        'key',
        'name',
        'location_name',
        'page_route',
        'description',
        'icon',
        'current_character_id',
        'current_gang_id',
        'captured_at',
        'cash_per_minute',
        'stars_per_minute',
        'total_captures',
        'energy_cost',
    ];

    protected $casts = [
        'captured_at' => 'datetime',
        'cash_per_minute' => 'integer',
        'stars_per_minute' => 'integer',
        'total_captures' => 'integer',
        'energy_cost' => 'integer',
    ];

    public function holder(): BelongsTo
    {
        return $this->belongsTo(Character::class, 'current_character_id');
    }

    public function gang(): BelongsTo
    {
        return $this->belongsTo(Gang::class, 'current_gang_id');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(ControlPointLog::class, 'control_point_id');
    }

    /**
     * Calculate how many full minutes the current holder has been holding the point.
     */
    public function getHeldMinutes(): int
    {
        if (! $this->captured_at) {
            return 0;
        }

        return max(0, (int) $this->captured_at->diffInMinutes(now()));
    }

    /**
     * Calculate currently accumulated cash waiting to be paid out upon dethronement.
     */
    public function getPendingCash(): int
    {
        return $this->getHeldMinutes() * $this->cash_per_minute;
    }

    /**
     * Calculate currently accumulated stars waiting to be paid out upon dethronement.
     */
    public function getPendingStars(): int
    {
        return $this->getHeldMinutes() * $this->stars_per_minute;
    }
}
