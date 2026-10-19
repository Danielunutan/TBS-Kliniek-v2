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
        if (! $this->is_gang_heist || empty($this->required_property_keys)) {
            return true;
        }

        if (! $gang) {
            return false;
        }

        $ownedKeys = $gang->properties->pluck('property_key')->all();

        foreach ($this->required_property_keys as $key) {
            if (in_array($key, $ownedKeys, true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine the effective vehicle used for this heist (personal active vehicle or qualifying vehicle from Bende Garage).
     */
    public function getEffectiveVehicle(Character $character): ?CharacterVehicle
    {
        $personal = CharacterVehicle::where('character_id', $character->id)
            ->where('is_active', true)
            ->with('vehicle')
            ->first() ?? CharacterVehicle::where('character_id', $character->id)->with('vehicle')->first();

        if ($personal && $this->vehicleQualifies($personal)) {
            return $personal;
        }

        // If gang heist and personal doesn't qualify, look in the Bende Garage
        if ($this->is_gang_heist && $character->gang) {
            $gangVehicles = $character->gang->storedVehicles()->with('vehicle')->get();
            foreach ($gangVehicles as $gv) {
                if ($this->vehicleQualifies($gv)) {
                    return $gv;
                }
            }
        }

        return $personal;
    }

    /**
     * Check if a vehicle meets the minimum speed, condition and type requirements for this heist.
     */
    public function vehicleQualifies(?CharacterVehicle $cv): bool
    {
        if (! $cv || ! $cv->vehicle) {
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
