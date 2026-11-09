<?php

namespace App\Models;

use App\Support\GangPropertyCatalog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class GangProperty extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'purchase_price' => 'integer',
        'level' => 'integer',
        'storage_capacity' => 'integer',
        'vehicle_slots' => 'integer',
        'npc_capacity' => 'integer',
        'production_duration_minutes' => 'integer',
        'production_reward_cash' => 'integer',
        'production_reward_respect' => 'integer',
        'is_for_sale' => 'boolean',
        'production_started_at' => 'datetime',
        'production_ends_at' => 'datetime',
    ];

    public function gang(): BelongsTo
    {
        return $this->belongsTo(Gang::class);
    }

    public function assignedNpc(): HasOne
    {
        return $this->hasOne(GangNpc::class, 'assigned_property_id');
    }

    public function getCatalogDefinition(): ?array
    {
        if (empty($this->property_key)) {
            return null;
        }

        return GangPropertyCatalog::get($this->property_key);
    }

    public function checkFleetRequirements(): array
    {
        if (! $this->gang || empty($this->property_key)) {
            return [
                'qualified' => true,
                'description' => null,
                'rules' => [],
                'reason' => null,
            ];
        }

        return GangPropertyCatalog::checkFleetRequirements($this->gang, $this->property_key);
    }

    public function isProduction(): bool
    {
        return $this->type === 'production';
    }

    public function isStorage(): bool
    {
        return $this->type === 'storage';
    }

    public function isLuxury(): bool
    {
        return $this->type === 'luxury';
    }

    public function isProducing(): bool
    {
        return $this->production_status === 'producing' && $this->production_ends_at && $this->production_ends_at->isFuture();
    }

    public function isReadyToClaim(): bool
    {
        if ($this->production_status === 'ready') {
            return true;
        }

        return $this->production_status === 'producing' && $this->production_ends_at && $this->production_ends_at->isPast();
    }

    public function isProductionReady(): bool
    {
        return $this->isReadyToClaim();
    }

    public function getRemainingProductionMinutesAttribute(): int
    {
        return (int) ceil($this->productionSecondsRemaining() / 60);
    }

    public function getEffectiveRewardCashAttribute(): int
    {
        return $this->getEffectiveRewards()['cash'] ?? (int) $this->production_reward_cash;
    }

    public function getEffectiveRewardRespectAttribute(): int
    {
        return $this->getEffectiveRewards()['respect'] ?? (int) $this->production_reward_respect;
    }

    public function getEffectiveDurationMinutesAttribute(): int
    {
        $baseMinutes = (int) ($this->production_duration_minutes ?? 60);

        if ($this->assigned_npc_key === 'chemisch_specialist') {
            return max(5, (int) round($baseMinutes * 0.5)); // Halved duration for hired master chemist!
        }

        $assignedNpc = GangNpc::where('gang_id', $this->gang_id)
            ->where('assigned_property_id', $this->id)
            ->first();

        if ($assignedNpc && $assignedNpc->isActive() && $assignedNpc->bonus_type === 'production_speed') {
            $speedFactor = 1 - min(0.75, $assignedNpc->bonus_value / 100);

            return max(5, (int) round($baseMinutes * $speedFactor));
        }

        return $baseMinutes;
    }

    public function productionSecondsRemaining(): int
    {
        if (! $this->production_ends_at) {
            return 0;
        }

        return max(0, (int) ceil(now()->diffInSeconds($this->production_ends_at, false)));
    }

    /**
     * Calculate effective cash and respect yields considering assigned NPCs.
     *
     * @return array{cash: int, respect: int}
     */
    public function getEffectiveRewards(): array
    {
        $baseCash = (int) $this->production_reward_cash;
        $baseRespect = (int) $this->production_reward_respect;

        // Check if gang has assigned an NPC to this property
        $assignedNpc = GangNpc::where('gang_id', $this->gang_id)
            ->where('assigned_property_id', $this->id)
            ->first();

        $hasAssignedNpcYield = false;
        if ($assignedNpc && $assignedNpc->isActive() && $assignedNpc->bonus_type === 'production_yield') {
            $bonusFactor = 1 + ($assignedNpc->bonus_value / 100);
            $baseCash = (int) round($baseCash * $bonusFactor);
            $hasAssignedNpcYield = true;
        }

        // Batch production manager doubles cash payout (+100%) when hired for a cycle
        if ($this->assigned_npc_key === 'productie_manager') {
            if (! $hasAssignedNpcYield) {
                $baseCash = (int) round($baseCash * 2.0);
            }
        }

        return [
            'cash' => $baseCash,
            'respect' => $baseRespect,
        ];
    }
}
