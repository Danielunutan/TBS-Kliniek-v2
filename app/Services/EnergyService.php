<?php

namespace App\Services;

use App\Models\Character;

class EnergyService
{
    /**
     * Seconds per 1 energy regeneration point (5 minutes = 300 seconds).
     */
    public const REGEN_SECONDS = 300;

    /**
     * Refresh character's energy and health based on elapsed server timestamp.
     */
    public function refresh(Character $character): Character
    {
        $now = now();
        $lastRefill = $character->last_energy_refill_at ?? $character->created_at ?? $now;

        $secondsElapsed = $now->diffInSeconds($lastRefill);

        if ($secondsElapsed >= self::REGEN_SECONDS) {
            $pointsToRegen = (int) floor($secondsElapsed / self::REGEN_SECONDS);
            $leftoverSeconds = $secondsElapsed % self::REGEN_SECONDS;

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
