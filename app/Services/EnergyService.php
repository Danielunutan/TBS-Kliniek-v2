<?php

namespace App\Services;

use App\Models\Character;

class EnergyService
{
    /**
     * Seconds per 1 energy regeneration point (5 minutes = 300 seconds).
     */
    public const REGEN_SECONDS = 300;

    public function __construct(
        protected ?AllowanceService $allowanceService = null,
    ) {
        $this->allowanceService = $allowanceService ?? app(AllowanceService::class);
    }

    /**
     * Refresh character's energy and health based on elapsed server timestamp, and pay pending allowances.
     */
    public function refresh(Character $character): Character
    {
        $tier = (int) ($character->perks['supporter_tier'] ?? 0);
        if ($character->is_vip && $tier >= 1 && $character->vip_expires_at && $character->vip_expires_at->isPast()) {
            if ($tier === 4) {
                $character->vip_expires_at = now()->addYears(99);
                $character->save();
            } elseif ($character->vip_expires_at->lt(now()->setDate(2026, 11, 1)->startOfDay())) {
                $character->vip_expires_at = now()->addDays(30);
                $character->save();
            }
        }

        $this->allowanceService->payoutPendingAllowance($character);

        $now = now();
        $lastRefill = $character->last_energy_refill_at ?? $character->created_at ?? $now;

        $secondsElapsed = $now->diffInSeconds($lastRefill);

        $regenInterval = match ($character->getSupporterTier()) {
            4 => 120, // Tier 4 / Lifetime: 2 min per punt (60% sneller)
            3 => 120, // Tier 3 Baas: 2 min per punt (60% sneller)
            2 => 150, // Tier 2 Elite: 2.5 min per punt (50% sneller)
            1 => 180, // Tier 1 VIP: 3 min per punt (40% sneller)
            default => $character->isVip() ? 180 : self::REGEN_SECONDS,
        };

        if ($secondsElapsed >= $regenInterval) {
            $pointsToRegen = (int) floor($secondsElapsed / $regenInterval);
            $leftoverSeconds = $secondsElapsed % $regenInterval;

            $newEnergy = min($character->max_energy, $character->energy + $pointsToRegen);
            $newHealth = min($character->max_health, $character->health + $pointsToRegen);

            $character->energy = $newEnergy;
            $character->health = $newHealth;
            // Advance the timestamp by the intervals consumed
            $character->last_energy_refill_at = $now->copy()->subSeconds($leftoverSeconds);
            $character->save();
        }

        return $character;
    }

    /**
     * Consume energy if character has enough.
     */
    public function consume(Character $character, int $amount): bool
    {
        $this->refresh($character);

        if ($character->energy < $amount) {
            return false;
        }

        $character->energy -= $amount;
        $character->save();

        return true;
    }
}
