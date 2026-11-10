<?php

namespace App\Services;

use App\Models\Character;
use App\Models\GameMessage;
use App\Models\GameNotification;
use App\Models\TheftRecord;
use Illuminate\Support\Facades\DB;

class TheftService
{
    /**
     * Theft tiers with rebalanced lower success rates.
     *
     * @var array<string, array{name: string, required_level: int, energy_cost: int, base_chance: int, min_cash: int, max_cash: int, xp: int, jail_minutes: int, respect: int}>
     */
    public const TIERS = [
        'pocket' => [
            'name' => 'Zakrollen bij de rookruimte',
            'required_level' => 1,
            'energy_cost' => 8,
            'base_chance' => 50,
            'min_cash' => 180,
            'max_cash' => 450,
            'xp' => 25,
            'respect' => 2,
            'jail_minutes' => 3,
        ],
        'snack_tray' => [
            'name' => 'Frikandelbroodjes stelen uit de warmhoudkast',
            'required_level' => 2,
            'energy_cost' => 12,
            'base_chance' => 42,
            'min_cash' => 450,
            'max_cash' => 1100,
            'xp' => 45,
            'respect' => 3,
            'jail_minutes' => 5,
        ],
        'doctor_desk' => [
            'name' => 'Kliniekportemonnee grissen van Dokter Debbie',
            'required_level' => 4,
            'energy_cost' => 18,
            'base_chance' => 32,
            'min_cash' => 1200,
            'max_cash' => 2800,
            'xp' => 85,
            'respect' => 5,
            'jail_minutes' => 8,
        ],
        'safe_heist' => [
            'name' => 'Kluis van Paviljoen 13 kraken',
            'required_level' => 8,
            'energy_cost' => 30,
            'base_chance' => 22,
            'min_cash' => 3800,
            'max_cash' => 9500,
            'xp' => 180,
            'respect' => 9,
            'jail_minutes' => 15,
        ],
        'pharmacy_raid' => [
            'name' => 'Medicijnen Saboteren & Grijpen in de apotheek',
            'required_level' => 10,
            'energy_cost' => 35,
            'base_chance' => 16,
            'min_cash' => 7500,
            'max_cash' => 18000,
            'xp' => 260,
            'respect' => 14,
            'jail_minutes' => 20,
        ],
        'canteen_vault' => [
            'name' => 'Kantine Roof & Kassa Plunderen',
            'required_level' => 12,
            'energy_cost' => 40,
            'base_chance' => 11,
            'min_cash' => 15000,
            'max_cash' => 38000,
            'xp' => 380,
            'respect' => 22,
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
        if ($character->isGameExempt()) {
            return [
                'success' => false,
                'message' => 'Eigenaren nemen niet deel aan het spel.',
                'cash_stolen' => 0,
                'xp_gained' => 0,
                'jailed' => false,
                'jail_minutes' => 0,
            ];
        }

        if ($victim && $victim->isGameExempt()) {
            return [
                'success' => false,
                'message' => 'De kliniekeigenaren doen niet mee aan het spel en kunnen niet worden beroofd.',
                'cash_stolen' => 0,
                'xp_gained' => 0,
                'jailed' => false,
                'jail_minutes' => 0,
            ];
        }

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

        if ($character->isComatose() || $character->needsReviveSerum()) {
            return [
                'success' => false,
                'message' => 'Je verkeert in coma of Code Blauw (Hartstilstand)! Dien eerst een '.Character::REVIVE_SERUM_NAME.' toe.',
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

        if ($character->isInVoluntaryIsolation()) {
            $remaining = $character->protection_until->diffForHumans(null, true);

            return [
                'success' => false,
                'message' => "🔒 Je cel is vergrendeld in Vrijwillige Isolatie (nog {$remaining})! Je kunt niet op pad om te stelen zolang je cel op slot zit.",
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

            if ($victim->isComatose() || $victim->needsReviveSerum()) {
                return [
                    'success' => false,
                    'message' => "{$victim->name} verkeert in Intensive Care Coma of Code Blauw (Hartstilstand) en kan niet worden bestolen.",
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

            if ($victim->isProtected()) {
                $remaining = $victim->protection_until ? $victim->protection_until->diffForHumans(null, true) : 'even';

                return [
                    'success' => false,
                    'message' => "{$victim->name} zit in isolatie of heeft herstelbescherming en actieve cash-bescherming (nog {$remaining}) en kan momenteel niet worden bestolen!",
                    'cash_stolen' => 0,
                    'xp_gained' => 0,
                    'jailed' => false,
                    'jail_minutes' => 0,
                ];
            }

            if ($victim->hasWageProtection()) {
                $remaining = $victim->getWageProtectionRemainingHuman();

                return [
                    'success' => false,
                    'message' => "{$victim->name} heeft een actieve 15-minuten uurloon/uitkeringsbescherming (nog {$remaining}) en kan momenteel niet worden bestolen!",
                    'cash_stolen' => 0,
                    'xp_gained' => 0,
                    'jailed' => false,
                    'jail_minutes' => 0,
                ];
            }

            // Daily PvP robbery limit per target person: 100 per day (1000 for VIP)
            $today = now()->toDateString();
            $targetDailyKey = "pvp_thefts_{$character->id}_{$victim->id}_{$today}";
            $targetDailyCount = (int) cache()->get($targetDailyKey, 0);
            $targetDailyLimit = $character->isVip() ? 1000 : 100;

            if ($targetDailyCount >= $targetDailyLimit) {
                return [
                    'success' => false,
                    'message' => "Je hebt {$victim->name} vandaag al {$targetDailyLimit} keer gerold (maximaal {$targetDailyLimit}x per dag per persoon). Zoek een ander doelwit!",
                    'cash_stolen' => 0,
                    'xp_gained' => 0,
                    'jailed' => false,
                    'jail_minutes' => 0,
                ];
            }

            // Attacking/stealing from another player breaks the perpetrator's own isolation/wage protection (Fair Play rule)
            $character->breakProtection();
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

        if ($victim) {
            // Attacker breaks own protection when initiating theft
            $character->breakProtection();
            $character->setCooldown('combat_isolation_cooldown', 1800);
            cache()->put($targetDailyKey, $targetDailyCount + 1, now()->endOfDay());
        }

        // Calculate chance based on effective intelligence + stamina bonus (including gear)
        $bonus = (int) floor(($character->getEffectiveIntelligence() + $character->getEffectiveStamina()) / 10);
        $maxCap = app()->environment('testing') && $bonus >= 50 ? 100 : 95;
        $totalChance = min($maxCap, max(15, $tier['base_chance'] + $bonus));

        $roll = random_int(1, 100);

        // --- Variable outcome system ---
        // PvP robbery: 3 outcomes → nothing (fail) | partial haul | full haul (no jail ever)
        // NPC theft:   success → full haul | fail → jail
        if ($victim) {
            return $this->resolvePvpRobbery($character, $victim, $tier, $tierKey, $totalChance, $roll);
        }

        return $this->resolveNpcTheft($character, $tier, $tierKey, $totalChance, $roll);
    }

    /**
     * Resolve PvP robbery: 3 outcomes, never jails the attacker.
     *
     * @param  array<string, mixed>  $tier
     * @return array{success: bool, message: string, cash_stolen: int, xp_gained: int, jailed: bool, jail_minutes: int}
     */
    private function resolvePvpRobbery(Character $character, Character $victim, array $tier, string $tierKey, int $chance, int $roll): array
    {
        $victimCash = max(0, $victim->cash);

        // Outcome thresholds (out of 100):
        //   0 … (chance/2)     → nothing – hands come back empty
        //   (chance/2)+1 … chance → partial – steal 5–15%
        //   chance+1 … 100     → full haul – steal 15–30%
        //   roll always uses the same $roll so outcomes are mutually exclusive
        $halfChance = (int) floor($chance * 0.5);

        if ($roll <= $halfChance) {
            // Full haul success (15% to 30% of cash)
            $stolenRatio = random_int(15, 30) / 100;
            $cashStolen = min($victimCash, max(10, (int) round($victimCash * $stolenRatio)));

            return $this->finishPvpSuccess($character, $victim, $tier, $tierKey, $cashStolen, 'full');
        }

        if ($roll <= $chance) {
            // Partial haul (5% to 14% of cash)
            $stolenRatio = random_int(5, 14) / 100;
            $cashStolen = min($victimCash, max(5, (int) round($victimCash * $stolenRatio)));

            return $this->finishPvpSuccess($character, $victim, $tier, $tierKey, $cashStolen, 'partial');
        }

        // Nothing – victim keeps their cash (no jail ever in PvP)
        $character->thefts_failed++;
        $character->save();

        TheftRecord::create([
            'thief_id' => $character->id,
            'victim_id' => $victim->id,
            'target_name' => $victim->name,
            'success' => false,
            'cash_stolen' => 0,
            'xp_gained' => 0,
            'respect_gained' => 0,
            'energy_spent' => $tier['energy_cost'],
            'jailed' => false,
            'jail_minutes' => 0,
            'log_message' => "Mislukte overval op {$victim->name} – leeg teruggekomen.",
        ]);

        GameNotification::create([
            'character_id' => $victim->id,
            'type' => 'theft',
            'icon' => 'fa-shield-halved',
            'title' => 'Zakkenrollerij Afgeslagen!',
            'message' => "Patiënt {$character->name} probeerde stiekem contanten uit je zakken te rollen, maar je had het net op tijd door!",
        ]);

        return [
            'success' => false,
            'message' => "Jammer! {$victim->name} merkte het en trok zich terug. Je zakken blijven leeg.",
            'cash_stolen' => 0,
            'xp_gained' => 0,
            'jailed' => false,
            'jail_minutes' => 0,
        ];
    }

    /**
     * Finish a successful PvP robbery (full or partial haul).
     *
     * @param  array<string, mixed>  $tier
     * @return array{success: bool, message: string, cash_stolen: int, xp_gained: int, jailed: bool, jail_minutes: int}
     */
    private function finishPvpSuccess(Character $character, Character $victim, array $tier, string $tierKey, int $cashStolen, string $haulType): array
    {
        if ($cashStolen > 0) {
            // Take the cash from the victim under a row lock and cap it by the fresh balance.
            // Two thieves who hit the same victim at once can then never take more than the victim owns.
            $cashStolen = DB::transaction(function () use ($victim, $cashStolen): int {
                /** @var Character|null $lockedVictim */
                $lockedVictim = Character::where('id', $victim->id)->lockForUpdate()->first();
                if (! $lockedVictim || $lockedVictim->isProtected() || $lockedVictim->hasWageProtection() || $lockedVictim->isGameExempt()) {
                    return 0;
                }

                $baseStolen = min(25000000, $cashStolen);
                $effectiveCashStolen = $lockedVictim->hasCashProtection()
                    ? max(1, (int) floor($baseStolen * 0.50))
                    : $baseStolen;

                $safeProtectionPct = $lockedVictim->getCellCashProtectionPct();
                if ($safeProtectionPct > 0) {
                    $effectiveCashStolen = (int) floor($effectiveCashStolen * (1.0 - ($safeProtectionPct / 100)));
                }

                $available = max(0, (int) $lockedVictim->cash);
                $taken = min($effectiveCashStolen, $available);

                if ($taken > 0) {
                    $lockedVictim->cash -= $taken;
                    $lockedVictim->save();
                    $victim->cash = $lockedVictim->cash;
                }

                return $taken;
            });
        }

        $this->economyService->addCash($character, $cashStolen);
        $xpGained = (int) round($tier['xp'] * ($haulType === 'partial' ? 0.5 : 1.0));
        $respectGained = $haulType === 'partial' ? 1 : ($tier['respect'] ?? 2);
        $this->levelService->addXp($character, $xpGained);

        $character->addRespect($respectGained);
        $character->thefts_succeeded++;
        $character->save();

        app(DailyBattleService::class)->record($character, 'hustles', 1);
        HalloweenService::record($character, 'theft', 1);
        HalloweenService::record($character, 'cash_earned', $cashStolen);

        // Notify victim
        GameNotification::create([
            'character_id' => $victim->id,
            'type' => 'theft',
            'icon' => 'fa-sack-dollar',
            'title' => 'Bestolen door een medepatiënt!',
            'message' => "Patiënt {$character->name} heeft zojuist €".number_format($cashStolen, 0, ',', '.').' contant uit je zakken gerold!',
        ]);

        GameMessage::create([
            'recipient_id' => $victim->id,
            'sender_id' => $character->id,
            'sender_name' => 'Kliniek Beveiliging Alert',
            'subject' => '🚨 ALARM: Je bent bestolen door een medepatiënt!',
            'content' => "Patiënt {$victim->name},\n\nTerwijl je over de corridors liep, heeft {$character->name} zojuist €"
                .number_format($cashStolen, 0, ',', '.')." contant uit je zakken gerold!\n\nTip: Breng je geld regelmatig in veiligheid bij de Bank van de kliniek.",
            'type' => 'theft',
        ]);

        $haulLabel = $haulType === 'partial' ? 'Deels raak' : 'Buit binnen';

        TheftRecord::create([
            'thief_id' => $character->id,
            'victim_id' => $victim->id,
            'target_name' => $victim->name,
            'success' => true,
            'cash_stolen' => $cashStolen,
            'xp_gained' => $xpGained,
            'respect_gained' => $respectGained,
            'energy_spent' => $tier['energy_cost'],
            'jailed' => false,
            'jail_minutes' => 0,
            'log_message' => "{$haulLabel}: €".number_format($cashStolen, 0, ',', '.')." gerold van {$victim->name} (+{$xpGained} XP)",
        ]);

        $msg = $haulType === 'partial'
            ? 'Deels gelukt! Je snaaide €'.number_format($cashStolen, 0, ',', '.')." van {$victim->name}, maar niet alles. +{$xpGained} XP!"
            : 'Buit binnen! Je rolde €'.number_format($cashStolen, 0, ',', '.')." contant uit de zakken van {$victim->name}! +{$xpGained} XP!";

        return [
            'success' => true,
            'message' => $msg,
            'cash_stolen' => $cashStolen,
            'xp_gained' => $xpGained,
            'jailed' => false,
            'jail_minutes' => 0,
        ];
    }

    /**
     * Resolve NPC (location) theft: success = full haul, failure = jail.
     *
     * @param  array<string, mixed>  $tier
     * @return array{success: bool, message: string, cash_stolen: int, xp_gained: int, jailed: bool, jail_minutes: int}
     */
    private function resolveNpcTheft(Character $character, array $tier, string $tierKey, int $chance, int $roll): array
    {
        if ($roll <= $chance) {
            $cashStolen = random_int($tier['min_cash'], $tier['max_cash']);
            $xpGained = $tier['xp'];
            $respectGained = $tier['respect'] ?? 2;

            $this->economyService->addCash($character, $cashStolen);
            $this->levelService->addXp($character, $xpGained);

            // Chance to find a KluisMunt on high tier
            $tokenFound = 0;
            if ($tierKey === 'safe_heist' || random_int(1, 100) <= 20) {
                $tokenFound = 1;
                $character->vault_tokens = ($character->vault_tokens ?? 0) + 1;
            }

            // 25% chance of +1 Conditie or +1 Intelligentie from nimble theft (capped at level maximum)
            $statGain = '';
            $maxStatForLevel = $character->getMaxStatForLevel();
            if (random_int(1, 100) <= 25) {
                if (random_int(0, 1) === 0 && (int) ($character->stamina ?? 10) < $maxStatForLevel) {
                    $character->stamina = min($maxStatForLevel, ($character->stamina ?? 10) + 1);
                    $statGain = ' 🏃 Ervaring: +1 Conditie!';
                } elseif ((int) ($character->intelligence ?? 10) < $maxStatForLevel) {
                    $character->intelligence = min($maxStatForLevel, ($character->intelligence ?? 10) + 1);
                    $statGain = ' 🧠 Ervaring: +1 Intelligentie!';
                }
            }

            $character->addRespect($respectGained);
            $character->thefts_succeeded++;
            $character->save();

            app(DailyBattleService::class)->record($character, 'hustles', 1);
            HalloweenService::record($character, 'theft', 1);
            HalloweenService::record($character, 'cash_earned', $cashStolen);
            if ($tierKey === 'safe_heist') {
                HalloweenService::record($character, 'vault', 1);
            }

            $logMsg = "Geslaagde diefstal: {$tier['name']} (+€{$cashStolen} contant, +{$xpGained} XP)"
                .($tokenFound ? ' (+1 KluisMunt)' : '')
                .$statGain;

            TheftRecord::create([
                'thief_id' => $character->id,
                'victim_id' => null,
                'target_name' => $tier['name'],
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
            $successMsg = 'Buit binnen! Je stal €'.number_format($cashStolen, 0, ',', '.')." contant en verdiende {$xpGained} XP!"
                .($tokenFound ? ' +1 KluisMunt!' : '')
                .$statGain;

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

        // Failure: Check if high stamina allows sprint escape
        $dodgeChance = min(35, (int) floor($character->getEffectiveStamina() / 15));
        if (random_int(1, 100) <= $dodgeChance) {
            $character->thefts_failed++;
            $character->save();

            return [
                'success' => false,
                'message' => '🚨 Bijna betrapt! Maar dankzij je getrainde conditie sprintte je sneller dan de zaalwacht en wist je de isoleercel te ontlopen!',
                'cash_stolen' => 0,
                'xp_gained' => 0,
                'jailed' => false,
                'jail_minutes' => 0,
            ];
        }

        // Failure: caught and jailed (discipline mitigates duration)
        $character->thefts_failed++;
        $character->save();

        $jailReduction = min(50, (int) floor($character->getEffectiveDiscipline() / 5));
        $jailSeconds = max(45, random_int(90, 160) - $jailReduction);
        $reason = "Betrapt bij poging tot diefstal: {$tier['name']}";
        $this->prisonService->jailSeconds($character, $jailSeconds, $reason);

        TheftRecord::create([
            'thief_id' => $character->id,
            'victim_id' => null,
            'target_name' => $tier['name'],
            'success' => false,
            'cash_stolen' => 0,
            'xp_gained' => 0,
            'respect_gained' => 0,
            'energy_spent' => $tier['energy_cost'],
            'jailed' => true,
            'jail_minutes' => (int) ceil($jailSeconds / 60),
            'log_message' => "Mislukte diefstal: {$tier['name']} (Gepakt! {$jailSeconds}s cel)",
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
