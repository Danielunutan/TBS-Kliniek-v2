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
        // Condition affects actual performance: below 50% condition, vehicle gets slower
        if ($this->current_condition < 50) {
            $penalty = (int) round((50 - $this->current_condition) * 0.5);

            return max(15, $base - $penalty);
        }

        return $base;
    }

    public function getRepairCostAttribute(): int
    {
        $missing = 100 - $this->current_condition;
        if ($missing <= 0) {
            return 0;
        }
        // Base vehicle price factor (approx 10% of vehicle value for 100% repair)
        $costPerPct = max(50, (int) round(($this->vehicle?->price_cash ?? 10000) * 0.001));

        return $missing * $costPerPct;
    }
}
