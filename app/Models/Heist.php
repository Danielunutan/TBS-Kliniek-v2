<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Heist extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'required_property_keys' => 'array',
        'is_gang_heist' => 'boolean',
        'min_gang_members' => 'integer',
        'gang_respect_reward' => 'integer',
        'gang_treasury_share_pct' => 'integer',
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

    /**
     * Check if a gang owns at least one of the required properties for this heist.
     */
    public function gangHasRequiredProperty(?Gang $gang): bool
    {
        if (! $this->is_gang_heist) {
            return true;
        }

        $keys = $this->required_property_keys;
        if (is_string($keys)) {
            $decoded = json_decode($keys, true);
            $keys = is_array($decoded) ? $decoded : array_filter(array_map('trim', explode(',', $keys)));
        }

        if (empty($keys) || ! is_array($keys)) {
            return true;
        }

        if (! $gang) {
            return false;
        }

        $ownedProperties = $gang->relationLoaded('properties') ? $gang->properties : $gang->properties()->get();
        $ownedKeys = $ownedProperties ? $ownedProperties->pluck('property_key')->all() : [];

        foreach ($keys as $key) {
            if (in_array($key, $ownedKeys, true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine the effective vehicle used for this heist.
     *
     * Prioritizes qualifying vehicles by highest effective speed, then highest condition.
     */
    public function getEffectiveVehicle(Character $character, ?int $selectedVehicleId = null): ?CharacterVehicle
    {
        // For gang heists, vehicle must be from the Bende Garage
        if ($this->is_gang_heist) {
            if (! $character->gang) {
                return null;
            }

            $gangVehicles = $character->gang->relationLoaded('storedVehicles')
                ? $character->gang->storedVehicles
                : $character->gang->storedVehicles()->with('vehicle')->get();

            if ($selectedVehicleId) {
                $selected = $gangVehicles->firstWhere('id', $selectedVehicleId);
                if ($selected && $selected->current_condition > 0 && $this->vehicleQualifies($selected)) {
                    return $selected;
                }
            }

            $qualifying = $gangVehicles->filter(fn ($gv) => $this->vehicleQualifies($gv));

            if ($qualifying->isNotEmpty()) {
                return $qualifying->sort(function ($a, $b) {
                    if ($a->effective_speed !== $b->effective_speed) {
                        return $b->effective_speed <=> $a->effective_speed;
                    }

                    return $b->current_condition <=> $a->current_condition;
                })->first();
            }

            return $gangVehicles->where('current_condition', '>', 0)->first() ?? $gangVehicles->first();
        }

        $personalVehicles = CharacterVehicle::where('character_id', $character->id)
            ->where('in_gang_garage', false)
            ->where('is_for_sale', false)
            ->with('vehicle')
            ->get();

        if ($selectedVehicleId) {
            $selected = $personalVehicles->firstWhere('id', $selectedVehicleId);
            if ($selected && $selected->current_condition > 0 && $this->vehicleQualifies($selected)) {
                return $selected;
            }
        }

        $qualifying = $personalVehicles->filter(fn ($pv) => $this->vehicleQualifies($pv));

        if ($qualifying->isNotEmpty()) {
            return $qualifying->sort(function ($a, $b) {
                if ($a->effective_speed !== $b->effective_speed) {
                    return $b->effective_speed <=> $a->effective_speed;
                }

                if ($a->current_condition !== $b->current_condition) {
                    return $b->current_condition <=> $a->current_condition;
                }

                return ($b->is_active ? 1 : 0) <=> ($a->is_active ? 1 : 0);
            })->first();
        }

        return $personalVehicles->where('current_condition', '>', 0)->firstWhere('is_active', true)
            ?? $personalVehicles->where('current_condition', '>', 0)->sortByDesc('current_condition')->first()
            ?? $personalVehicles->firstWhere('is_active', true)
            ?? $personalVehicles->sortByDesc('current_condition')->first();
    }

    /**
     * Getaway vehicle helper alias for this heist.
     */
    public function getawayVehicle(Character $character, ?int $selectedVehicleId = null): ?CharacterVehicle
    {
        return $this->getEffectiveVehicle($character, $selectedVehicleId);
    }

    /**
     * Check if a vehicle meets the minimum speed, condition and type requirements for this heist.
     */
    public function vehicleQualifies(?CharacterVehicle $cv): bool
    {
        if (! $cv || ! $cv->vehicle) {
            return false;
        }

        // Destroyed (0% condition) or for sale vehicles cannot qualify
        if ($cv->current_condition <= 0 || $cv->is_for_sale) {
            return false;
        }

        if ($this->required_vehicle_type && $this->required_vehicle_type !== 'any') {
            if ($cv->vehicle->type !== $this->required_vehicle_type) {
                return false;
            }
        }

        if ($cv->effective_speed < $this->min_speed_required) {
            return false;
        }

        if ($cv->current_condition < $this->min_condition_required) {
            return false;
        }

        return true;
    }

    /**
     * Calculate vehicle speed bonus percentage relative to required speed.
     */
    public function calculateSpeedBonus(?CharacterVehicle $vehicle): int
    {
        if (! $vehicle) {
            return 0;
        }

        $minSpeed = max(0, (int) $this->min_speed_required);
        $speedDiff = (int) $vehicle->effective_speed - $minSpeed;

        return (int) round($speedDiff * 0.4);
    }

    /**
     * Calculate vehicle condition bonus percentage relative to 70% threshold.
     */
    public function calculateConditionBonus(?CharacterVehicle $vehicle): int
    {
        if (! $vehicle) {
            return 0;
        }

        $condition = max(0, min(100, (int) $vehicle->current_condition));

        return (int) round(($condition - 70) * 0.25);
    }

    /**
     * Calculate gang member synergy bonus percentage.
     */
    public function calculateGangBonus(?Character $character): int
    {
        if ($this->is_gang_heist && $character && $character->gang) {
            $memberCount = $character->gang->relationLoaded('members')
                ? $character->gang->members->count()
                : ($character->gang->members_count ?? $character->gang->members()->count());

            return min(15, (int) ($memberCount * 2));
        }

        return 0;
    }

    /**
     * Calculate final heist success chance (clamped between 15% and 92%).
     */
    public function calculateSuccessChance(Character $character, ?CharacterVehicle $vehicle = null): int
    {
        $vehicle = $vehicle ?? $this->getEffectiveVehicle($character);

        $baseChance = match ($this->difficulty) {
            'makkelijk' => 75,
            'normaal' => 65,
            'zwaar' => 52,
            'extreem' => 42,
            'legendarisch' => 32,
            default => 50,
        };

        $speedBonus = $this->calculateSpeedBonus($vehicle);
        $conditionBonus = $this->calculateConditionBonus($vehicle);
        $statBonus = min(15, (int) floor(((int) $character->getEffectiveIntelligence() + (int) $character->getEffectiveDiscipline()) / 30));
        $gangBonus = $this->calculateGangBonus($character);

        return max(15, min(92, $baseChance + $speedBonus + $conditionBonus + $statBonus + $gangBonus));
    }

    /**
     * Calculate arrest risk percentage (chance of failure and isolation imprisonment).
     */
    public function calculateArrestRisk(Character $character, ?CharacterVehicle $vehicle = null): int
    {
        $chance = $this->calculateSuccessChance($character, $vehicle);

        return max(8, min(85, 100 - $chance));
    }

    public function getawayVehicles(): HasManyThrough
    {
        return $this->hasManyThrough(Vehicle::class, HeistLog::class, 'heist_id', 'id', 'id', 'vehicle_id');
    }

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
