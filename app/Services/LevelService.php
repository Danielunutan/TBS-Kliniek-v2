<?php

namespace App\Services;

use App\Models\Character;
use App\Models\GameMessage;
use App\Models\GameNotification;

class LevelService
{
    /**
     * Calculate XP required to reach the next level.
     * Balanced curve: smooth progression for levels 1-15, steady challenge for 16-35,
     * and a natural exponential scaling above 35 so top players stay in a compact competitive bracket
     * without out-leveling rivals or entering empty isolated level zones.
     */
    public function getXpRequiredForNextLevel(int $currentLevel): int
    {
        $safeLevel = max(1, $currentLevel);

        if ($safeLevel <= 15) {
            return (int) ceil(400 * ($safeLevel ** 1.6));
        }

        return (int) ceil(450 * ($safeLevel ** 1.9));
    }

    /**
     * Add XP to a character and handle potential level-up(s).
     *
     * @return array{leveled_up: bool, old_level: int, new_level: int, cash_reward: int, xp_added: int}
     */
    public function addXp(Character $character, int $xpGain, bool $applyCatchUp = true): array
    {
        $xpGain = max(0, $xpGain);
        $comfortBonus = $character->getCellComfortBonusPct();
        if ($comfortBonus > 0 && $xpGain > 0) {
            $xpGain = (int) round($xpGain * (1.0 + ($comfortBonus / 100)));
        }

        // Natural catch-up bonus: early/mid levels (under level 20) earn +20% bonus XP to stay competitive
        if ($applyCatchUp && $character->level < 20 && $xpGain > 0) {
            $xpGain = (int) round($xpGain * 1.20);
        }

        $oldLevel = $character->level;
        $character->xp += $xpGain;
        $character->total_xp += $xpGain;

        $leveledUp = false;
        $totalCashReward = 0;
        $maxLevelUpsPerAction = 1;
        $iterations = 0;

        while ($iterations < $maxLevelUpsPerAction) {
            $required = $this->getXpRequiredForNextLevel($character->level);

            if ($character->xp >= $required) {
                $character->xp -= $required;
                $character->level++;
                $leveledUp = true;
                $iterations++;

                // Level up rewards: stats, cash bonus, tokens, balanced +25 energy boost (no infinite full refill loop)
                $bonusCash = $character->level * 150;
                $bonusTokens = max(1, (int) floor($character->level / 2));
                $totalCashReward += $bonusCash;
                $character->cash += $bonusCash;
                $character->vault_tokens = ($character->vault_tokens ?? 0) + $bonusTokens;
                $character->energy = min($character->max_energy, (int) $character->energy + 25);
                if (! $character->isComatose()) {
                    $character->health = min($character->max_health, (int) $character->health + 30);
                }
                $character->addRespect(5);
            } else {
                break;
            }
        }

        $character->save();

        if ($leveledUp) {
            app(ChatBotService::class)->sendLevelUp($character, $character->level, [
                '€'.number_format($totalCashReward, 0, ',', '.').' Contant Bonus',
                '+5 Respect',
                '+25 Energie & +30 Gezondheid',
            ]);

            GameNotification::create([
                'character_id' => $character->id,
                'type' => 'level_up',
                'icon' => 'fa-trophy',
                'title' => "Gefeliciteerd! Level {$character->level} bereikt!",
                'message' => "Je bent gestegen naar Level {$character->level}! Je hebt +25 energie en +30 gezondheid hersteld en je hebt €".number_format($totalCashReward, 0, ',', '.').' bonus ontvangen.',
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
            'xp_added' => $xpGain,
        ];
    }
}
