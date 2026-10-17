<?php

namespace App\Services;

use App\Models\Character;
use App\Models\CharacterCooldown;

class CooldownService
{
    /**
     * Cooldown durations in seconds.
     */
    public const DURATIONS = [
        // Production: higher difficulty = longer cooldown (5 min, 10 min, 20 min)
        'produce_sigaretten' => 300,
        'produce_ritalin' => 600,
        'produce_telefoon' => 1200,

        // Hosselen: 3 minutes (180s)
        'hustle' => 180,

        // Crimes / Illicit actions
        'pickpocket' => 120, // 2 min
        'locker' => 180,     // 3 min
        'package' => 180,    // 3 min
        'extort' => 240,     // 4 min

        // Behandelaarsgesprek & Verlof
        'treatment_session' => 7200,      // 2 uur tussen gesprekken
        'treatment_leave_action' => 180,  // 3 min tussen verlofactiviteiten

        // Nieuwe Functies
        'dropping_tennisball' => 600,     // 10 min
        'dropping_drone' => 1800,         // 30 min
        'radio_shoutout' => 90,           // 1.5 min
        'tunnel_dig' => 180,              // 3 min
        'tunnel_dump_sand' => 120,        // 2 min

        // Casino Games Cooldowns (anti-spam / anti-cheat)
        'casino_highlow' => 6,
        'casino_roulette' => 8,
        'casino_slots' => 4,
        'casino_dice' => 5,
        'casino_numbergame' => 8,
        'casino_rps' => 5,
    ];

    /**
     * Costs in Stars ⭐ to skip cooldown instantly.
     */
    public const SKIP_COSTS = [
        'hustle' => 10,
        'pickpocket' => 10,
        'locker' => 10,
        'package' => 10,
        'dagbesteding' => 10,
        'infirmary_palm' => 10,
        'tunnel_dig' => 15,
        'dropping_tennisball' => 15,
        'extort' => 20,
        'produce_sigaretten' => 20,
        'produce_ritalin' => 35,
        'treatment_session' => 35,
        'dropping_drone' => 40,
        'produce_telefoon' => 50,
    ];

    /**
     * Calculate dynamic cooldown duration in seconds based on success rate (slagingskans in percentage 1-100).
     * High success rate (low risk, small hustle) -> short cooldown.
     * Low success rate (high risk, big cash/respect) -> longer cooldown.
     */
    public function calculateBySuccessRate(int $ratePercentage): int
    {
        $rate = max(1, min(100, $ratePercentage));

        if ($rate >= 50) {
            return 75; // 1m 15s (makkelijk, snel weer proberen)
        } elseif ($rate >= 40) {
            return 120; // 2m 00s
        } elseif ($rate >= 30) {
            return 180; // 3m 00s
        } elseif ($rate >= 20) {
            return 240; // 4m 00s
        } elseif ($rate >= 15) {
            return 300; // 5m 00s
        } else {
            return 360; // 6m 00s (uiterst zwaar, hoogste beloning)
        }
    }

    public function getDuration(string $action): int
    {
        return self::DURATIONS[$action] ?? 180;
    }

    public function getSkipCost(string $action): int
    {
        return self::SKIP_COSTS[$action] ?? 10;
    }

    public function getRemainingSeconds(Character $character, string $action): int
    {
        return $character->cooldownRemaining($action);
    }

    public function isOnCooldown(Character $character, string $action): bool
    {
        return $this->getRemainingSeconds($character, $action) > 0;
    }

    public function setCooldown(Character $character, string $action, ?int $seconds = null): void
    {
        $seconds = $seconds ?? $this->getDuration($action);

        CharacterCooldown::updateOrCreate(
            ['character_id' => $character->id, 'action' => $action],
            ['cooldown_until' => now()->addSeconds($seconds)]
        );
    }

    public function clearCooldown(Character $character, string $action): void
    {
        CharacterCooldown::where('character_id', $character->id)
            ->where('action', $action)
            ->delete();
    }

    /**
     * @return array{success: bool, message: string}
     */
    public function skipWithStars(Character $character, string $action): array
    {
        $cost = $this->getSkipCost($action);

        if (($character->stars ?? 0) < $cost) {
            return [
                'success' => false,
                'message' => "Je hebt minimaal {$cost} Sterren ⭐ nodig om deze cooldown over te slaan! (Je hebt: ".($character->stars ?? 0).')',
            ];
        }

        $character->stars -= $cost;
        $character->save();

        $this->clearCooldown($character, $action);

        return [
            'success' => true,
            'message' => "⚡ Cooldown succesvol overgeslagen met {$cost} Sterren ⭐! Je kunt direct weer doorgaan.",
        ];
    }
}
