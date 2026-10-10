<?php

namespace App\Services;

use App\Models\Character;
use App\Models\CharacterMission;
use App\Models\Mission;

class MissionService
{
    public function __construct(
        protected LevelService $levelService,
        protected EconomyService $economyService,
        protected EnergyService $energyService,
        protected PrisonService $prisonService,
    ) {}

    /**
     * Execute a mission for a character.
     *
     * @return array{success: bool, message: string, cash_earned: int, xp_earned: int, respect_earned: int}
     */
    public function execute(Character $character, Mission $mission): array
    {
        if ($character->isImprisoned()) {
            return [
                'success' => false,
                'message' => 'Je zit in de isoleercel en kunt geen missies uitvoeren!',
                'cash_earned' => 0,
                'xp_earned' => 0,
                'respect_earned' => 0,
            ];
        }

        if ($character->level < $mission->required_level) {
            return [
                'success' => false,
                'message' => "Je moet minimaal Level {$mission->required_level} zijn voor deze missie.",
                'cash_earned' => 0,
                'xp_earned' => 0,
                'respect_earned' => 0,
            ];
        }

        if (! $this->energyService->consume($character, $mission->energy_cost)) {
            return [
                'success' => false,
                'message' => "Niet genoeg energie! Deze missie vereist {$mission->energy_cost} energie.",
                'cash_earned' => 0,
                'xp_earned' => 0,
                'respect_earned' => 0,
            ];
        }

        // Calculate success chance with intelligence / discipline minor bonus
        $bonus = (int) floor(($character->intelligence + $character->discipline) / 20);
        $totalChance = min(98, max(15, $mission->success_rate + $bonus));

        $roll = random_int(1, 100);
        $isSuccess = $roll <= $totalChance;

        $progression = CharacterMission::firstOrNew([
            'character_id' => $character->id,
            'mission_id' => $mission->id,
        ]);
        $progression->last_attempted_at = now();

        if ($isSuccess) {
            $cashEarned = random_int($mission->min_cash_reward, $mission->max_cash_reward);
            $xpEarned = $mission->xp_reward;
            $respectEarned = $mission->respect_reward;

            $this->economyService->addCash($character, $cashEarned);
            $this->levelService->addXp($character, $xpEarned);
            $character->respect += $respectEarned;
            $character->missions_completed++;
            $character->save();

            $progression->completed_count = ($progression->completed_count ?? 0) + 1;
            $progression->save();

            $msg = $mission->success_message ?: "Missie succesvol afgerond! Je ontving {$cashEarned} KC en {$xpEarned} XP.";

            return [
                'success' => true,
                'message' => $msg,
                'cash_earned' => $cashEarned,
                'xp_earned' => $xpEarned,
                'respect_earned' => $respectEarned,
            ];
        }

        $progression->save();

        // On failure: chance to get jailed for risky missions (if category is crime/theft)
        $failMsg = $mission->fail_message ?: 'Missie mislukt! Je bent betrapt of de deal liep in de soep. Probeer het later opnieuw.';

        if ($mission->category === 'theft' || $mission->category === 'high_risk') {
            $jailRoll = random_int(1, 100);
            if ($jailRoll <= 35) {
                $jailMinutes = random_int(3, 8);
                $this->prisonService->jail($character, $jailMinutes, "Mislukte missie: {$mission->title}");
                $failMsg .= " Bewakers grepen je in de kraag! {$jailMinutes} minuten celstraf!";
            }
        }

        return [
            'success' => false,
            'message' => $failMsg,
            'cash_earned' => 0,
            'xp_earned' => 0,
            'respect_earned' => 0,
        ];
    }
}
