<?php

namespace App\Services;

use App\Models\Character;
use App\Models\GameMessage;
use App\Models\GameNotification;

class LevelService
{
    /**
     * Calculate XP required to reach the next level.
     * Formula: ceil(350 * level ^ 1.75)
     * Provides a solid, balanced progression curve where early levels require multiple completed activities
     * and mid/late levels require dedicated effort without rapid consecutive level leaps.
     */
    public function getXpRequiredForNextLevel(int $currentLevel): int
    {
        return (int) ceil(350 * ($currentLevel ** 1.75));
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

                // Level up rewards: max stats increase, cash bonus, vault tokens, full energy refill
                $bonusCash = $character->level * 150;
                $bonusTokens = max(1, (int) floor($character->level / 2));
                $totalCashReward += $bonusCash;
                $character->cash += $bonusCash;
                $character->vault_tokens = ($character->vault_tokens ?? 0) + $bonusTokens;
                $character->energy = $character->max_energy;
                $character->health = $character->max_health;
                $character->respect += 5;
            } else {
                break;
            }
        }

        $character->save();

        if ($leveledUp) {
            app(ChatBotService::class)->sendLevelUp($character, $character->level, [
                '€'.number_format($totalCashReward, 0, ',', '.').' Contant Bonus',
                '+5 Respect',
                'Volledige Energie & HP Refill',
            ]);

            GameNotification::create([
                'character_id' => $character->id,
                'type' => 'level_up',
                'icon' => 'fa-trophy',
                'title' => "Gefeliciteerd! Level {$character->level} bereikt!",
                'message' => "Je bent gestegen naar Level {$character->level}! Je energie en gezondheid zijn volledig hersteld en je hebt €".number_format($totalCashReward, 0, ',', '.').' bonus ontvangen.',
            ]);

            GameMessage::create([
                'recipient_id' => $character->id,
                'sender_name' => 'Directie TBS Kliniek',
                'subject' => "Dossier Update: Level {$character->level} Bereikt!",
                'content' => "Beste {$character->name},\n\nVolgens de behandelcommissie heb je een nieuwe mijlpaal bereikt en ben je gepromoveerd naar Level {$character->level}!\n\nJe hebt €".number_format($totalCashReward, 0, ',', '.')." aan zakgeld ontvangen en extra KluisMunten voor het kraken van kliniekkluizen en koffers.\n\nGa zo door,\nDirecteur van TBS Kliniek",
                'type' => 'level',
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
