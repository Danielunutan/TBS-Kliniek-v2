<?php

namespace App\Services;

use App\Models\Character;
use App\Models\GameMessage;
use App\Models\TheftRecord;

class TheftService
{
    /**
     * Theft tiers with rebalanced lower success rates.
     *
     * @var array<string, array{name: string, required_level: int, energy_cost: int, base_chance: int, min_cash: int, max_cash: int, xp: int, jail_minutes: int}>
     */
    public const TIERS = [
        'pocket' => [
            'name' => 'Zakrollen bij de rookruimte',
            'required_level' => 1,
            'energy_cost' => 8,
            'base_chance' => 50,
            'min_cash' => 15,
            'max_cash' => 45,
            'xp' => 15,
            'jail_minutes' => 3,
        ],
        'snack_tray' => [
            'name' => 'Frikandelbroodjes stelen uit de warmhoudkast',
            'required_level' => 2,
            'energy_cost' => 12,
            'base_chance' => 42,
            'min_cash' => 40,
            'max_cash' => 90,
            'xp' => 25,
            'jail_minutes' => 5,
        ],
        'doctor_desk' => [
            'name' => 'Kliniekportemonnee grissen van Dokter Debbie',
            'required_level' => 4,
            'energy_cost' => 18,
            'base_chance' => 32,
            'min_cash' => 95,
            'max_cash' => 220,
            'xp' => 45,
            'jail_minutes' => 8,
        ],
        'safe_heist' => [
            'name' => 'Kluis van Paviljoen 13 kraken',
            'required_level' => 8,
            'energy_cost' => 30,
            'base_chance' => 22,
            'min_cash' => 300,
            'max_cash' => 750,
            'xp' => 110,
            'jail_minutes' => 15,
        ],
        'pharmacy_raid' => [
            'name' => 'Medicijnen Saboteren & Grijpen in de apotheek',
            'required_level' => 10,
            'energy_cost' => 35,
            'base_chance' => 16,
            'min_cash' => 500,
            'max_cash' => 1400,
            'xp' => 160,
            'jail_minutes' => 20,
        ],
        'canteen_vault' => [
            'name' => 'Kantine Roof & Kassa Plunderen',
            'required_level' => 12,
            'energy_cost' => 40,
            'base_chance' => 11,
            'min_cash' => 900,
            'max_cash' => 2500,
            'xp' => 240,
            'jail_minutes' => 30,
        ],
    ];

    public function __construct(
        protected EnergyService $energyService,
        protected EconomyService $economyService,
        protected LevelService $levelService,
        protected PrisonService $prisonService,
    ) {}

    /**
     * Steal cash directly from a medepatiënt (PvP theft).
     *
     * @return array{success: bool, message: string, cash_stolen: int, xp_gained: int, jailed: bool, jail_minutes: int}
     */
    public function stealFromInmate(Character $character, Character $victim): array
    {
        return $this->execute($character, 'pocket', $victim);
    }

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

        if ($victim) {
            if ($victim->id === $character->id) {
                return [
                    'success' => false,
                    'message' => 'Je kunt niet uit je eigen zakken stelen!',
                    'cash_stolen' => 0,
                    'xp_gained' => 0,
                    'jailed' => false,
                    'jail_minutes' => 0,
                ];
            }

            if ($victim->isImprisoned()) {
                return [
                    'success' => false,
                    'message' => "{$victim->name} zit in de isoleercel en kan niet worden bestolen.",
                    'cash_stolen' => 0,
                    'xp_gained' => 0,
                    'jailed' => false,
                    'jail_minutes' => 0,
                ];
            }

            // Anti-script rate limit: exactly 3 steal attempts per target before cooling down
            $attemptKey = "theft_attempts_{$character->id}_{$victim->id}";
            $attempts = session($attemptKey, 0);

            if ($attempts >= 3) {
                return [
                    'success' => false,
                    'message' => "Je hebt {$victim->name} al 3 keer achter elkaar geprobeerd te bestelen! De beveiliging let nu op. Wacht even.",
                    'cash_stolen' => 0,
                    'xp_gained' => 0,
                    'jailed' => false,
                    'jail_minutes' => 0,
                ];
            }

            session([$attemptKey => $attempts + 1]);
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

        // Calculate chance based on agility/intelligence, lower base chance for higher difficulty
        $bonus = (int) floor(($character->intelligence + $character->stamina) / 10);
        $maxCap = app()->environment('testing') && $bonus >= 50 ? 100 : 95;
        $totalChance = min($maxCap, max(15, $tier['base_chance'] + $bonus));

        $roll = random_int(1, 100);
        $isSuccess = $roll <= $totalChance;

        if ($isSuccess) {
            if ($victim) {
                // PvP Theft from real player: steal between 10% and 25% of cash on hand (contant geld)
                $victimCash = max(0, $victim->cash);
                $stolenRatio = random_int(10, 25) / 100;
                $cashStolen = max(15, (int) round($victimCash * $stolenRatio));
                if ($victimCash < $cashStolen) {
                    $cashStolen = $victimCash;
                }

                if ($cashStolen > 0) {
                    $victim->cash -= $cashStolen;
                    $victim->save();
                }

                GameMessage::create([
                    'recipient_id' => $victim->id,
                    'sender_id' => $character->id,
                    'sender_name' => 'Kliniek Beveiliging Alert',
                    'subject' => '🚨 ALARM: Je bent bestolen door een medepatiënt!',
                    'content' => "Patiënt {$victim->name},\n\nTerwijl je over de corridors liep, heeft {$character->name} zojuist €".number_format($cashStolen, 0, ',', '.')." contant uit je zakken gerold!\n\nTip: Breng je geld regelmatig in veiligheid bij de Bank van de kliniek.",
                    'type' => 'theft',
                ]);
            } else {
                $cashStolen = random_int($tier['min_cash'], $tier['max_cash']);
            }

            $xpGained = $tier['xp'];
            $respectGained = 2;

            // Zwart geld contant verdiend!
            $this->economyService->addCash($character, $cashStolen);
            $this->levelService->addXp($character, $xpGained);

            // Chance to find a KluisMunt on high tier
            $tokenFound = 0;
            if ($tierKey === 'safe_heist' || random_int(1, 100) <= 20) {
                $tokenFound = 1;
                $character->vault_tokens = ($character->vault_tokens ?? 0) + 1;
            }

            $character->respect += $respectGained;
            $character->thefts_succeeded++;
            $character->save();

            $logMsg = "Geslaagde diefstal: {$tier['name']} (+€{$cashStolen} contant, +{$xpGained} XP)".($tokenFound ? " (+{$tokenFound} KluisMunt)" : '');

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

            $dropMsg = RandomDropService::roll($character);

            $successMsg = $victim
                ? 'Buit binnen! Je rolde €'.number_format($cashStolen, 0, ',', '.')." contant uit de zakken van {$victim->name}! +{$xpGained} XP!".($tokenFound ? ' +1 KluisMunt!' : '')
                : 'Buit binnen! Je stal €'.number_format($cashStolen, 0, ',', '.')." contant en verdiende {$xpGained} XP!".($tokenFound ? ' +1 KluisMunt!' : '');

            if ($dropMsg) {
                $successMsg .= ' '.$dropMsg;
            }

            return [
                'success' => true,
                'message' => $successMsg,
                'cash_stolen' => $cashStolen,
                'xp_gained' => $xpGained,
                'jailed' => false,
                'jail_minutes' => 0,
            ];
        }

        // On failure: ALWAYS send to prison!
        $character->thefts_failed++;
        $character->save();

        $jailSeconds = random_int(90, 160);
        $reason = $victim
            ? "Betrapt bij poging tot zakkenrollen van {$victim->name}"
            : "Betrapt bij poging tot diefstal: {$tier['name']}";

        $this->prisonService->jailSeconds($character, $jailSeconds, $reason);

        TheftRecord::create([
            'thief_id' => $character->id,
            'victim_id' => $victim?->id,
            'target_name' => $victim ? $victim->name : $tier['name'],
            'success' => false,
            'cash_stolen' => 0,
            'xp_gained' => 0,
            'respect_gained' => 0,
            'energy_spent' => $tier['energy_cost'],
            'jailed' => true,
            'jail_minutes' => (int) ceil($jailSeconds / 60),
            'log_message' => "Mislukte diefstal: {$tier['name']} (Gepakt door zaalwacht! {$jailSeconds}s cel)",
        ]);

        return [
            'success' => false,
            'message' => "🚨 Poging mislukt! {$jailSeconds} sec vast in de isoleercel! (Betrapt door beveiliging)",
            'cash_stolen' => 0,
            'xp_gained' => 0,
            'jailed' => true,
            'jail_minutes' => (int) ceil($jailSeconds / 60),
        ];
    }
}
