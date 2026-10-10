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
    ];

    /**
     * Costs in Stars ⭐ to skip cooldown instantly.
     */
    public const SKIP_COSTS = [
        'produce_sigaretten' => 1,
        'produce_ritalin' => 2,
        'produce_telefoon' => 3,

        'hustle' => 1,
        'pickpocket' => 1,
        'locker' => 1,
        'package' => 1,
        'extort' => 2,
    ];

    public function getDuration(string $action): int
    {
        return self::DURATIONS[$action] ?? 180;
    }

    public function getSkipCost(string $action): int
    {
        return self::SKIP_COSTS[$action] ?? 1;
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
