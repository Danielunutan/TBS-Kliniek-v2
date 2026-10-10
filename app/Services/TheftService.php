<?php

namespace App\Services;

use App\Models\Character;
use App\Models\TheftRecord;

class TheftService
{
    /**
     * Theft tiers.
     *
     * @var array<string, array{name: string, required_level: int, energy_cost: int, base_chance: int, min_cash: int, max_cash: int, xp: int, jail_minutes: int}>
     */
    public const TIERS = [
        'pocket' => [
            'name' => 'Zakrollen bij de rookruimte',
            'required_level' => 1,
            'energy_cost' => 8,
            'base_chance' => 82,
            'min_cash' => 15,
            'max_cash' => 45,
            'xp' => 15,
            'jail_minutes' => 3,
        ],
        'snack_tray' => [
            'name' => 'Frikandelbroodjes stelen uit de warmhoudkast',
            'required_level' => 2,
            'energy_cost' => 12,
            'base_chance' => 74,
            'min_cash' => 40,
            'max_cash' => 90,
            'xp' => 25,
            'jail_minutes' => 5,
        ],
        'doctor_desk' => [
            'name' => 'Kliniekportemonnee grissen van Dokter Debbie',
            'required_level' => 4,
            'energy_cost' => 18,
            'base_chance' => 62,
            'min_cash' => 95,
            'max_cash' => 220,
            'xp' => 45,
            'jail_minutes' => 8,
        ],
        'safe_heist' => [
            'name' => 'Kluis van Paviljoen 13 kraken',
            'required_level' => 8,
            'energy_cost' => 30,
            'base_chance' => 48,
            'min_cash' => 300,
            'max_cash' => 750,
            'xp' => 110,
            'jail_minutes' => 15,
        ],
    ];

    public function __construct(
        protected EnergyService $energyService,
        protected EconomyService $economyService,
        protected LevelService $levelService,
        protected PrisonService $prisonService,
    ) {}

    /**
     * Execute a theft action.
     *
     * @return array{success: bool, message: string, cash_stolen: int, xp_gained: int, jailed: bool, jail_minutes: int}
     */
    public function execute(Character $character, string $tierKey, ?Character $victim = null): array
    {
        if (! array_key_exists($tierKey, self::TIERS)) {
            return [
                'success' => false,
                'message' => 'Onbekende diefstalactie.',
                'cash_stolen' => 0,
                'xp_gained' => 0,
                'jailed' => false,
                'jail_minutes' => 0,
            ];
        }

        if ($character->isImprisoned()) {
            return [
                'success' => false,
                'message' => 'Je zit achter slot en grendel en kunt niemand beroven!',
                'cash_stolen' => 0,
                'xp_gained' => 0,
                'jailed' => false,
                'jail_minutes' => 0,
            ];
        }

        $tier = self::TIERS[$tierKey];

        if ($character->level < $tier['required_level']) {
            return [
                'success' => false,
                'message' => "Je hebt minimaal Level {$tier['required_level']} nodig voor: {$tier['name']}.",
                'cash_stolen' => 0,
                'xp_gained' => 0,
                'jailed' => false,
                'jail_minutes' => 0,
            ];
        }

        if (! $this->energyService->consume($character, $tier['energy_cost'])) {
            return [
                'success' => false,
                'message' => "Niet genoeg energie! Dit vereist {$tier['energy_cost']} energie.",
                'cash_stolen' => 0,
                'xp_gained' => 0,
                'jailed' => false,
                'jail_minutes' => 0,
            ];
        }

        // Calculate chance based on agility/intelligence
        $bonus = (int) floor(($character->intelligence + $character->stamina) / 15);
        $totalChance = min(92, max(20, $tier['base_chance'] + $bonus));

        $roll = random_int(1, 100);
        $isSuccess = $roll <= $totalChance;

        if ($isSuccess) {
            $cashStolen = random_int($tier['min_cash'], $tier['max_cash']);
            $xpGained = $tier['xp'];
            $respectGained = 2;

            $this->economyService->addCash($character, $cashStolen);
            $this->levelService->addXp($character, $xpGained);

            $character->respect += $respectGained;
            $character->thefts_succeeded++;
            $character->save();

            $logMsg = "Geslaagde diefstal: {$tier['name']} (+{$cashStolen} KC, +{$xpGained} XP)";

            TheftRecord::create([
                'thief_id' => $character->id,
                'victim_id' => $victim?->id,
                'target_name' => $victim ? $victim->name : $tier['name'],
                'success' => true,
                'cash_stolen' => $cashStolen,
                'xp_gained' => $xpGained,
                'respect_gained' => $respectGained,
                'energy_spent' => $tier['energy_cost'],
                'jailed' => false,
                'jail_minutes' => 0,
                'log_message' => $logMsg,
            ]);

            return [
                'success' => true,
                'message' => "Buit binnen! Je stal {$cashStolen} KC en verdiende {$xpGained} XP!",
                'cash_stolen' => $cashStolen,
                'xp_gained' => $xpGained,
                'jailed' => false,
                'jail_minutes' => 0,
            ];
        }

        // Failure!
        $character->thefts_failed++;
        $character->respect = max(0, $character->respect - 1);
        $character->save();

        $jailRoll = random_int(1, 100);
        $jailed = $jailRoll <= 65;
        $jailMinutes = $jailed ? $tier['jail_minutes'] : 0;

        if ($jailed) {
            $this->prisonService->jail($character, $jailMinutes, "Betrapt bij: {$tier['name']}");
        }

        $logMsg = "Mislukte diefstal: {$tier['name']}".($jailed ? " ({$jailMinutes} min gevangenisstraf)" : ' (Weggerend)');

        TheftRecord::create([
            'thief_id' => $character->id,
            'victim_id' => $victim?->id,
            'target_name' => $victim ? $victim->name : $tier['name'],
            'success' => false,
            'cash_stolen' => 0,
            'xp_gained' => 0,
            'respect_gained' => -1,
            'energy_spent' => $tier['energy_cost'],
            'jailed' => $jailed,
            'jail_minutes' => $jailMinutes,
            'log_message' => $logMsg,
        ]);

        $message = $jailed
            ? "Alarm geslagen! De bewaking heeft je in de kraag gevat. Je zit {$jailMinutes} minuten in de isoleercel!"
            : 'Net op tijd weggekomen! Je hebt niets kunnen stelen, maar ontkwam ternauwernood aan de bewaking.';

        return [
            'success' => false,
            'message' => $message,
            'cash_stolen' => 0,
            'xp_gained' => 0,
            'jailed' => $jailed,
            'jail_minutes' => $jailMinutes,
        ];
    }
}
