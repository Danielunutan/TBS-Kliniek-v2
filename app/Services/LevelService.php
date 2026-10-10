<?php

namespace App\Services;

use App\Models\Character;
use App\Models\GameNotification;

class LevelService
{
    /**
     * Calculate XP required to reach the next level.
     * Formula: ceil(100 * level ^ 1.65)
     */
    public function getXpRequiredForNextLevel(int $currentLevel): int
    {
        return (int) ceil(100 * ($currentLevel ** 1.65));
    }

    /**
     * Add XP to a character and handle potential level-up(s).
     *
     * @return array{leveled_up: bool, old_level: int, new_level: int, cash_reward: int}
     */
    public function addXp(Character $character, int $xpGain): array
    {
        $oldLevel = $character->level;
        $character->xp += $xpGain;
        $character->total_xp += $xpGain;

        $leveledUp = false;
        $totalCashReward = 0;

        while (true) {
            $required = $this->getXpRequiredForNextLevel($character->level);

            if ($character->xp >= $required) {
                $character->xp -= $required;
                $character->level++;
                $leveledUp = true;

                // Level up rewards: max stats increase, cash bonus, full energy refill
                $bonusCash = $character->level * 150;
                $totalCashReward += $bonusCash;
                $character->cash += $bonusCash;
                $character->energy = $character->max_energy;
                $character->health = $character->max_health;
                $character->respect += 5;
            } else {
                break;
            }
        }

        $character->save();

        if ($leveledUp) {
            GameNotification::create([
                'character_id' => $character->id,
                'type' => 'level_up',
                'icon' => 'fa-trophy',
                'title' => "Gefeliciteerd! Level {$character->level} bereikt!",
                'message' => "Je bent gestegen naar Level {$character->level}! Je energie en gezondheid zijn volledig hersteld en je hebt {$totalCashReward} KC bonus ontvangen.",
            ]);
        }

        return [
            'leveled_up' => $leveledUp,
            'old_level' => $oldLevel,
            'new_level' => $character->level,
            'cash_reward' => $totalCashReward,
        ];
    }
}
