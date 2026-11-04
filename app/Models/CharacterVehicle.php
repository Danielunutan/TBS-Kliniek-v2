<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CharacterVehicle extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'current_condition' => 'integer',
        'tuned_speed' => 'integer',
        'is_active' => 'boolean',
        'is_for_sale' => 'boolean',
        'in_gang_garage' => 'boolean',
        'stored_at' => 'datetime',
        'total_heists_done' => 'integer',
    ];

    public function character(): BelongsTo
    {
        return $this->belongsTo(Character::class);
    }

    public function gang(): BelongsTo
    {
        return $this->belongsTo(Gang::class);
    }

    public function storedBy(): BelongsTo
    {
        return $this->belongsTo(Character::class, 'stored_by_character_id');
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function heistLogs(): HasMany
    {
        return $this->hasMany(HeistLog::class, 'vehicle_id', 'vehicle_id')
            ->when($this->character_id !== null, fn ($q) => $q->where('character_id', $this->character_id));
    }

    public function getEffectiveSpeedAttribute(): int
    {
        $base = $this->tuned_speed ?? ($this->vehicle?->speed_pct ?? 50);
        $base = max(15, (int) $base);

        $condition = max(0, min(100, (int) $this->current_condition));
        // Condition affects actual performance: below 50% condition, vehicle gets slower
        if ($condition < 50) {
            $penalty = (int) round((50 - $condition) * 0.5);

            return max(15, $base - $penalty);
        }

        return $base;
    }

    public function getRepairCostAttribute(): int
    {
        $condition = max(0, min(100, (int) $this->current_condition));
        $missing = 100 - $condition;
        if ($missing <= 0) {
            return 0;
        }
        // Base vehicle price factor (approx 10% of vehicle value for 100% repair)
        $vehiclePrice = max(0, (int) ($this->vehicle?->price_cash ?? 10000));
        $costPerPct = max(50, (int) round($vehiclePrice * 0.001));
        $baseCost = $missing * $costPerPct;

        // Chop-Shop Garage provides 20% discount on gang vehicle fleet repairs
        if ($this->in_gang_garage && $this->gang_id && $this->gang?->hasProperty('chop_shop_garage')) {
            return (int) round($baseCost * 0.80);
        }

        return $baseCost;
    }

    /**
     * Get damage percentage (0% to 100%).
     */
    public function getDamagePercentageAttribute(): int
    {
        return 100 - max(0, min(100, (int) $this->current_condition));
    }

    /**
     * Alias for damage percentage.
     */
    public function getWearPercentageAttribute(): int
    {
        return $this->damage_percentage;
    }

    /**
     * Check if vehicle is destroyed / wrecked (condition <= 0).
     */
    public function isDestroyed(): bool
    {
        return (int) $this->current_condition <= 0;
    }

    /**
     * Check if the vehicle is impounded or unusable.
     */
    public function isImpounded(): bool
    {
        return (bool) ($this->is_impounded ?? false);
    }

    /**
     * Check if vehicle is available for a heist.
     */
    public function isAvailableForHeist(): bool
    {
        return ! $this->is_for_sale && ! $this->isDestroyed() && ! $this->isImpounded();
    }

    /**
     * Check if the vehicle is reinforced / armored.
     */
    public function isReinforced(): bool
    {
        if (! $this->vehicle) {
            return false;
        }

        $name = strtolower($this->vehicle->name ?? '');
        $slug = strtolower($this->vehicle->slug ?? '');

        return ($this->vehicle->reliability_pct ?? 0) >= 90
            || str_contains($name, 'gepantserd')
            || str_contains($name, 'armored')
            || str_contains($slug, 'gepantserd')
            || str_contains($slug, 'armored');
    }

    /**
     * Get the wear multiplier for this vehicle.
     * Reinforced vehicles take 20% less wear (multiplier 0.80).
     * High reliability vehicles (>80%) also receive wear reduction.
     */
    public function getWearMultiplier(): float
    {
        if ($this->isReinforced()) {
            return 0.80;
        }

        $reliability = (int) ($this->vehicle?->reliability_pct ?? 75);
        if ($reliability > 80) {
            return max(0.80, round(1.0 - (($reliability - 80) * 0.01), 2));
        }

        return 1.0;
    }
}
