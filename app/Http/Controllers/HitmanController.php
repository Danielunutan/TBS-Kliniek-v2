<?php

namespace App\Http\Controllers;

use App\Http\Middleware\SerializeCharacterActions;
use App\Models\BankTransaction;
use App\Models\BloodFeud;
use App\Models\Bounty;
use App\Models\Character;
use App\Models\GameNotification;
use App\Models\User;
use App\Services\AuditLogService;
use App\Services\ChatBotService;
use App\Services\CooldownService;
use App\Services\EconomyService;
use App\Services\EnergyService;
use App\Services\EventCashHuntService;
use App\Services\LevelService;
use App\Services\PrisonService;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class HitmanController extends Controller
{
    public function __construct(
        protected EconomyService $economy,
        protected EnergyService $energy,
        protected LevelService $level,
        protected PrisonService $prison,
        protected CooldownService $cooldownService,
    ) {}

    public function index(): View|RedirectResponse
    {
        /** @var User|null $user */
        $user = Auth::user();
        /** @var Character|null $character */
        $character = $user?->character;

        if (! $character) {
            return redirect()->route('character.create');
        }

        $this->energy->refresh($character);

        $bloodFeuds = BloodFeud::active()
            ->with(['attacker', 'victim'])
            ->where(function ($query) use ($character) {
                $query->where('victim_id', $character->id);
                if ($character->gang_id) {
                    $query->orWhere('victim_gang_id', $character->gang_id);
                }
            })
            ->latest()
            ->get();

        $activeIntelOps = $character->getActiveIntelOps();
        $intelMap = collect($activeIntelOps)->keyBy('target_id');
        $attackerWing = $character->current_location ?? 'Paviljoen 1 (Gesloten)';

        // Enrich active intel operations with live target status when ready
        $intelTargetIds = collect($activeIntelOps)->pluck('target_id')->unique()->all();
        $intelTargetsById = Character::whereIn('id', $intelTargetIds)->get()->keyBy('id');

        $enrichedIntelOps = array_map(function (array $op) use ($intelTargetsById, $attackerWing): array {
            /** @var Character|null $targetModel */
            $targetModel = $intelTargetsById->get($op['target_id']);
            if ($targetModel && $op['is_ready']) {
                $targetWing = $targetModel->current_location ?? 'Paviljoen 1 (Gesloten)';
                $op['revealed_location'] = $targetWing;
                $op['same_wing'] = ($targetWing === $attackerWing);
                $op['has_vest'] = $targetModel->hasVest();
                $op['vest_name'] = $targetModel->getActiveVestInfo()['name'] ?? null;
                $op['cash_formatted'] = '€'.number_format((int) $targetModel->cash, 0, ',', '.');
                $op['bank_formatted'] = '€'.number_format((int) $targetModel->bank, 0, ',', '.');
                $op['is_protected'] = $targetModel->hasIsolationProtection();
                $op['is_wing_locked'] = $targetModel->isWingLocked();
                $op['streak_deaths'] = $targetModel->getLiquidationStreakDeaths();
            } else {
                $op['revealed_location'] = null;
                $op['same_wing'] = false;
                $op['has_vest'] = false;
                $op['vest_name'] = null;
                $op['cash_formatted'] = '???';
                $op['bank_formatted'] = '???';
                $op['is_protected'] = false;
                $op['is_wing_locked'] = false;
                $op['streak_deaths'] = 0;
            }

            return $op;
        }, $activeIntelOps);

        $targets = Character::where('id', '!=', $character->id)
            ->whereNotIn('name', Character::EXCLUDED_SYSTEM_NAMES)
            ->where('is_in_prison', false)
            ->orderByDesc('level')
            ->limit(20)
            ->get()
            ->map(function (Character $target) use ($character, $intelMap, $attackerWing) {
                $hasBloodFeud = BloodFeud::hasClaimAgainst($character, $target->id) !== null;
                $onCooldown = false;
                $cooldownSec = 0;
                $streakDeaths = $target->getLiquidationStreakDeaths();
                $pairKillKey = "daily_liquidations_{$character->id}_{$target->id}_".now()->toDateString();
                $dailyDeathsReached = $target->needsReviveSerum()
                    || $target->getDailyDeathsCount() >= Character::MAX_DAILY_DEATHS_BEFORE_SHIELD
                    || (int) Cache::get($pairKillKey, 0) >= Character::MAX_DAILY_LIQUIDATIONS_PER_TARGET;
                $isLevelRestricted = ! $hasBloodFeud && (abs($character->level - $target->level) > 15);

                $intel = $intelMap->get($target->id);
                $hasReadyIntel = $intel && ! empty($intel['is_ready']);
                $targetWing = $target->current_location ?? 'Paviljoen 1 (Gesloten)';
                $sameWing = ($attackerWing === $targetWing);

                $target->has_blood_feud = $hasBloodFeud;
                $target->on_cooldown = $onCooldown;
                $target->cooldown_seconds = $cooldownSec;
                $target->daily_deaths_reached = $dailyDeathsReached;
                $target->streak_deaths = $streakDeaths;
                $target->is_level_restricted = $isLevelRestricted;
                $target->has_ready_intel = $hasReadyIntel;
                $target->has_pending_intel = $intel && empty($intel['is_ready']);
                $target->intel_wait_seconds = $intel ? (int) ($intel['wait_remaining_sec'] ?? 0) : 0;
                $target->revealed_location = ($hasReadyIntel || $sameWing) ? $targetWing : null;
                $target->in_same_wing = $sameWing;

                return $target;
            });

        $contracts = [
            ['id' => 1, 'name' => 'Waarschuwingsslag',   'desc' => 'Stuur een dreigend pakket. Geen dodelijk letsel, wel schade.', 'cost' => 15000,  'energy' => 15, 'damage' => 20, 'rate' => 75, 'xp' => 60],
            ['id' => 2, 'name' => 'Klap bij de kantine', 'desc' => 'Een stevige aframmeling na het avondeten.',                    'cost' => 35000,  'energy' => 20, 'damage' => 35, 'rate' => 65, 'xp' => 110],
            ['id' => 3, 'name' => 'Gewelddadige overval', 'desc' => 'Bewusteloos slaan en beroven op de luchtplaats.',             'cost' => 75000,  'energy' => 25, 'damage' => 55, 'rate' => 50, 'xp' => 200],
        ];

        $gradeInfo = $character->getSyndicateGradeInfo();
        $gradesCatalog = Character::syndicateGradesCatalog();
        $vestCatalog = Character::vestCatalog();
        $ammoCatalog = Character::ammoCatalog();
        $intelCatalog = Character::intelCatalog();
        $locations = TravelController::LOCATIONS;
        $travelCost = $this->calculateTravelCost($character);

        return view('game.hitman.index', compact(
            'character',
            'targets',
            'contracts',
            'bloodFeuds',
            'gradeInfo',
            'gradesCatalog',
            'vestCatalog',
            'ammoCatalog',
            'intelCatalog',
            'enrichedIntelOps',
            'locations',
            'travelCost'
        ));
    }

    /**
     * Search all patient targets across the clinic via live AJAX search.
     */
    public function search(Request $request): JsonResponse
    {
        /** @var Character|null $character */
        $character = Auth::user()?->character;

        if (! $character) {
            return response()->json(['targets' => []]);
        }

        $q = trim((string) $request->query('q', ''));

        if (mb_strlen($q) < 1) {
            return response()->json(['targets' => []]);
        }

        $intelMap = collect($character->getActiveIntelOps())->keyBy('target_id');
        $attackerWing = $character->current_location ?? 'Paviljoen 1 (Gesloten)';

        $escapedQ = str_replace(['!', '%', '_'], ['!!', '!%', '!_'], strtolower($q));
        $targets = Character::where('id', '!=', $character->id)
            ->whereRaw("LOWER(name) LIKE ? ESCAPE '!'", ['%'.$escapedQ.'%'])
            ->orderBy('name')
            ->limit(20)
            ->get()
            ->map(function (Character $target) use ($character, $intelMap, $attackerWing) {
                $hasBloodFeud = BloodFeud::hasClaimAgainst($character, $target->id) !== null;
                $onCooldown = false;
                $cooldownSec = 0;
                $pairKillKey = "daily_liquidations_{$character->id}_{$target->id}_".now()->toDateString();
                $dailyDeathsReached = $target->needsReviveSerum()
                    || $target->getDailyDeathsCount() >= Character::MAX_DAILY_DEATHS_BEFORE_SHIELD
                    || (int) Cache::get($pairKillKey, 0) >= Character::MAX_DAILY_LIQUIDATIONS_PER_TARGET;
                $isLevelRestricted = ! $hasBloodFeud && (abs($character->level - $target->level) > 15);

                $intel = $intelMap->get($target->id);
                $hasReadyIntel = $intel && ! empty($intel['is_ready']);
                $targetWing = $target->current_location ?? 'Paviljoen 1 (Gesloten)';
                $sameWing = ($attackerWing === $targetWing);

                return [
                    'id' => (string) $target->id,
                    'name' => $target->name,
                    'level' => $target->level,
                    'respect' => $target->respect,
                    'health' => $target->health,
                    'cash' => number_format($target->cash, 0, ',', '.'),
                    'avatar' => $target->avatar_url,
                    'isOnline' => $target->isOnline(),
                    'inPrison' => $target->isImprisoned(),
                    'inComa' => $target->needsReviveSerum(),
                    'isProtected' => $target->hasIsolationProtection(),
                    'hasBloodFeud' => $hasBloodFeud,
                    'onCooldown' => $onCooldown,
                    'cooldownSeconds' => $cooldownSec,
                    'dailyDeathsReached' => $dailyDeathsReached,
                    'streakDeaths' => $target->getLiquidationStreakDeaths(),
                    'isLevelRestricted' => $isLevelRestricted,
                    'hasReadyIntel' => $hasReadyIntel,
                    'hasPendingIntel' => $intel && empty($intel['is_ready']),
                    'revealedLocation' => ($hasReadyIntel || $sameWing) ? $targetWing : null,
                    'inSameWing' => $sameWing,
                ];
            });

        return response()->json(['targets' => $targets]);
    }

    /**
     * Start a 15-minute (or 7-minute express) Shadow Intel tracking operation on a target.
     */
    public function startIntel(Request $request): RedirectResponse
    {
        /** @var Character|null $character */
        $character = Auth::user()?->character;

        if (! $character) {
            return redirect()->route('character.create');
        }

        if ($character->isImprisoned()) {
            return redirect()->route('prison')->with('error', 'Vanuit de isoleercel kun je geen schaduw-inlichtingen aanvragen!');
        }

        if ($character->isComatose() || $character->needsReviveSerum()) {
            return back()->with('error', 'Je verkeert in coma of Code Blauw (Hartstilstand)! Dien eerst een '.Character::REVIVE_SERUM_NAME.' toe.');
        }

        if (! $request->filled('target_id') && $request->filled('target_name')) {
            $nameInput = trim((string) $request->input('target_name'));
            $found = Character::whereRaw('LOWER(name) = ?', [strtolower($nameInput)])->first();
            if ($found) {
                $request->merge(['target_id' => $found->id]);
            }
        }

        $validated = $request->validate([
            'target_id' => ['required', 'exists:characters,id'],
            'intel_type' => ['nullable', 'string', 'in:standard_15m,express_7m,lockdown_15m'],
            'currency' => ['nullable', 'string', 'in:cash,stars'],
        ]);

        $target = Character::findOrFail($validated['target_id']);
        if ($target->id === $character->id) {
            return back()->with('error', 'Je hoeft geen schaduw-inlichtingen op jezelf uit te voeren!');
        }

        $catalog = Character::intelCatalog();
        $typeKey = $validated['intel_type'] ?? 'standard_15m';
        $intelCfg = $catalog[$typeKey] ?? $catalog['standard_15m'];
        $currency = $validated['currency'] ?? 'cash';

        return DB::transaction(function () use ($character, $target, $intelCfg, $currency): RedirectResponse {
            $firstId = min($character->id, $target->id);
            $secondId = max($character->id, $target->id);
            $lockedPair = Character::whereIn('id', [$firstId, $secondId])
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            /** @var Character|null $locked */
            $locked = $lockedPair->get($character->id);
            /** @var Character|null $lockedTarget */
            $lockedTarget = $lockedPair->get($target->id);

            if (! $locked || ! $lockedTarget) {
                return back()->with('error', 'Doelwit kon niet worden gevonden.');
            }

            if ($locked->isImprisoned() || $locked->isComatose() || $locked->needsReviveSerum()) {
                return back()->with('error', 'Je kunt op dit moment geen schaduw-inlichtingen aanvragen.');
            }

            if ($currency === 'stars') {
                $starCost = $intelCfg['cost_stars'];
                if (($locked->stars ?? 0) < $starCost) {
                    return back()->with('error', "Je hebt {$starCost} Sterren ⭐ nodig voor {$intelCfg['name']}.");
                }
                $locked->stars -= $starCost;
            } else {
                $cashCost = $intelCfg['cost_cash'];
                if (($locked->cash + $locked->bank) < $cashCost) {
                    return back()->with('error', 'Je hebt €'.number_format($cashCost, 0, ',', '.')." (contant + bank) nodig voor {$intelCfg['name']}.");
                }
                $this->deductCashOrBank($locked, $cashCost);
            }

            $nowTs = now()->timestamp;
            $readyAt = now()->addMinutes($intelCfg['wait_minutes'])->timestamp;
            $expiresAt = now()->addMinutes($intelCfg['wait_minutes'] + $intelCfg['valid_minutes'])->timestamp;

            $perks = $locked->perks ?? [];
            $ops = is_array($perks['active_intel_ops'] ?? null) ? $perks['active_intel_ops'] : [];

            // Remove expired or duplicate entries for same target
            $ops = array_values(array_filter($ops, function ($op) use ($lockedTarget, $nowTs) {
                return is_array($op)
                    && (int) ($op['target_id'] ?? 0) !== $lockedTarget->id
                    && (int) ($op['expires_at'] ?? 0) > $nowTs;
            }));

            $ops[] = [
                'target_id' => $lockedTarget->id,
                'target_name' => $lockedTarget->name,
                'type' => $intelCfg['id'],
                'type_name' => $intelCfg['name'],
                'started_at' => $nowTs,
                'ready_at' => $readyAt,
                'expires_at' => $expiresAt,
                'lockdown_minutes' => $intelCfg['lockdown_minutes'],
            ];

            $perks['active_intel_ops'] = $ops;
            $locked->perks = $perks;
            $locked->save();

            if ($intelCfg['lockdown_minutes'] > 0) {
                $targetPerks = $lockedTarget->perks ?? [];
                $targetPerks['wing_lockdown_start'] = $readyAt;
                $targetPerks['wing_lockdown_until'] = $readyAt + ($intelCfg['lockdown_minutes'] * 60);
                $lockedTarget->perks = $targetPerks;
                $lockedTarget->save();
            }

            $lockMsg = $intelCfg['lockdown_minutes'] > 0
                ? " Zodra het rapport binnen is, wordt de vleugel van {$lockedTarget->name} bovendien {$intelCfg['lockdown_minutes']} minuten lang vergrendeld!"
                : '';

            return back()->with('success', "🕵️ {$intelCfg['name']} gestart op {$lockedTarget->name}! Over {$intelCfg['wait_minutes']} minuten ligt het volledige observatiedossier (inclusief exacte kliniek-vleugel) voor je klaar.{$lockMsg}");
        });
    }

    /**
     * Quick-travel directly to a clinic wing from the Liquidation / Intel screen.
     */
    public function quickTravel(Request $request): RedirectResponse
    {
        /** @var Character|null $character */
        $character = Auth::user()?->character;

        if (! $character) {
            return redirect()->route('character.create');
        }

        if ($character->isImprisoned()) {
            return redirect()->route('prison')->with('error', 'Vanuit de isoleercel kun je je niet verplaatsen!');
        }

        if ($character->isComatose() || $character->needsReviveSerum()) {
            return back()->with('error', 'Je verkeert in coma of Code Blauw (Hartstilstand)! Dien eerst een '.Character::REVIVE_SERUM_NAME.' toe.');
        }

        if ($character->isWingLocked()) {
            $mins = (int) ceil($character->getWingLockRemainingSeconds() / 60);

            return back()->with('error', "🔒 AFDELING VERGRENDELD! Een rivaal heeft jouw huidige vleugel op slot gezet. Je kunt nog {$mins} min. niet reizen!");
        }

        $validated = $request->validate([
            'destination' => ['required', 'string'],
        ]);

        $destInput = trim($validated['destination']);
        $matchedLocation = null;

        foreach (TravelController::LOCATIONS as $key => $loc) {
            if ($key === $destInput || strcasecmp($loc['name'], $destInput) === 0) {
                $matchedLocation = $loc['name'];
                break;
            }
        }

        if (! $matchedLocation) {
            return back()->with('error', 'Ongeldige kliniek-afdeling opgegeven.');
        }

        $currentLoc = $character->current_location ?? 'Paviljoen 1 (Gesloten)';
        if ($currentLoc === $matchedLocation) {
            return back()->with('info', "Je bevindt je al op {$matchedLocation}!");
        }

        $cost = $this->calculateTravelCost($character);

        return DB::transaction(function () use ($character, $matchedLocation, $cost): RedirectResponse {
            /** @var Character|null $locked */
            $locked = Character::where('id', $character->id)->lockForUpdate()->first();

            if (! $locked || $locked->isImprisoned() || $locked->isComatose() || $locked->needsReviveSerum()) {
                return back()->with('error', 'Je kunt je op dit moment niet verplaatsen.');
            }

            if (($locked->cash + $locked->bank) < $cost) {
                return back()->with('error', 'Je hebt €'.number_format($cost, 0, ',', '.').' nodig om de bewakers om te kopen voor verplaatsing.');
            }

            $this->deductCashOrBank($locked, $cost);
            $locked->current_location = $matchedLocation;
            $locked->save();

            $this->level->addXp($locked, 35);

            return back()->with('success', '🏃 Bewakers omgekocht (€'.number_format($cost, 0, ',', '.').")! Je bent direct doorgesluisd naar {$matchedLocation}. Je staat nu in dezelfde ruimte om toe te slaan!");
        });
    }

    /**
     * Train Koudbloedigheid (%) at the Underground Shooting Range.
     */
    public function trainColdBlooded(Request $request): RedirectResponse
    {
        /** @var Character|null $character */
        $character = Auth::user()?->character;

        if (! $character) {
            return redirect()->route('character.create');
        }

        $this->energy->refresh($character);

        if ($character->isImprisoned()) {
            return redirect()->route('prison')->with('error', 'Vanuit de isoleercel kun je niet op de ondergrondse schietbaan trainen!');
        }

        if ($character->isComatose() || $character->needsReviveSerum()) {
            return back()->with('error', 'Je verkeert in coma of Code Blauw (Hartstilstand)! Dien eerst een '.Character::REVIVE_SERUM_NAME.' toe.');
        }

        if ($character->getColdBloodedPct() >= 100) {
            return back()->with('info', 'Je Koudbloedigheid staat al op het maximale niveau van 100%!');
        }

        $validated = $request->validate([
            'currency' => ['nullable', 'string', 'in:cash,stars'],
        ]);
        $currency = $validated['currency'] ?? 'cash';
        $cashCost = 350000;
        $starCost = 25;
        $energyCost = 15;

        if ($currency !== 'stars' && $character->energy < $energyCost) {
            return back()->with('error', "Je hebt minimaal {$energyCost} energie nodig voor een intensieve schietbaansessie!");
        }

        return DB::transaction(function () use ($character, $currency, $cashCost, $starCost, $energyCost): RedirectResponse {
            /** @var Character|null $locked */
            $locked = Character::where('id', $character->id)->lockForUpdate()->first();

            if (! $locked || $locked->isImprisoned() || $locked->isComatose() || $locked->needsReviveSerum()) {
                return back()->with('error', 'Je kunt op dit moment niet trainen.');
            }

            if ($currency === 'stars') {
                if (($locked->stars ?? 0) < $starCost) {
                    return back()->with('error', "Je hebt {$starCost} Sterren ⭐ nodig voor een VIP Precisie-Sessie.");
                }
                $locked->stars -= $starCost;
                $gain = 15;
            } else {
                if (($locked->cash + $locked->bank) < $cashCost) {
                    return back()->with('error', 'Een sessie op de Ondergrondse Schietbaan kost €'.number_format($cashCost, 0, ',', '.').' (contant of bank).');
                }
                if (! $this->energy->consume($locked, $energyCost)) {
                    return back()->with('error', "Je hebt minimaal {$energyCost} energie nodig voor een intensieve schietbaansessie!");
                }
                $this->deductCashOrBank($locked, $cashCost);
                $gain = 10;
            }

            $perks = $locked->perks ?? [];
            $currentPct = $locked->getColdBloodedPct();
            $newPct = min(100, $currentPct + $gain);
            $perks['cold_blooded_pct'] = $newPct;
            $locked->perks = $perks;
            $locked->save();

            $this->level->addXp($locked, 120);

            return back()->with('success', "🎯 Ondergrondse Schietbaan voltooid! Je Koudbloedigheid is gestegen naar {$newPct}% (+{$gain}% Kaliberfocus & +120 XP).");
        });
    }

    /**
     * Buy the expensive Forensisch Reanimatie-Serum (Epinefrine-Ampul) to revive after 10 liquidations or reset the counter.
     */
    public function buyReviveSerum(Request $request): RedirectResponse
    {
        /** @var Character|null $character */
        $character = Auth::user()?->character;

        if (! $character) {
            return redirect()->route('character.create');
        }

        $validated = $request->validate([
            'currency' => ['nullable', 'string', 'in:cash,stars'],
        ]);
        $currency = $validated['currency'] ?? 'cash';

        return DB::transaction(function () use ($character, $currency): RedirectResponse {
            /** @var Character $locked */
            $locked = Character::where('id', $character->id)->lockForUpdate()->first();

            $serumName = Character::REVIVE_SERUM_NAME;
            $cashPrice = Character::REVIVE_SERUM_CASH;
            $starPrice = Character::REVIVE_SERUM_STARS;

            if (! $locked->needsReviveSerum() && $locked->getLiquidationStreakDeaths() === 0) {
                return back()->with('info', 'Je Reanimatie-Dossier staat op 0/10 en je bent volledig stabiel! Je hebt momenteel geen Epinefrine-Ampul nodig.');
            }

            if ($currency === 'stars') {
                if (($locked->stars ?? 0) < $starPrice) {
                    return back()->with('error', "Je hebt {$starPrice} Sterren ⭐ nodig voor een {$serumName}!");
                }
                $locked->stars -= $starPrice;
                $paidLabel = "{$starPrice} Sterren ⭐";
            } else {
                $totalWealth = (int) $locked->cash + (int) $locked->bank;
                if ($totalWealth < $cashPrice) {
                    // If player is locked in Code Blauw (10/10 liquidations) and doesn't have €1.5M, take their remaining wealth (nood-krediet) so they are never permanently soft-locked
                    if ($locked->needsReviveSerum() && $totalWealth > 0) {
                        $this->deductCashOrBank($locked, $totalWealth);
                        $paidLabel = '€'.number_format($totalWealth, 0, ',', '.').' (Spoed-Beslaglegging Code Blauw)';
                    } elseif ($locked->needsReviveSerum()) {
                        $paidLabel = 'Medisch Nood-Krediet (Code Blauw)';
                    } else {
                        return back()->with('error', "Een {$serumName} kost €".number_format($cashPrice, 0, ',', '.').' (contant + bank) of '.$starPrice.' Sterren ⭐.');
                    }
                } else {
                    $this->deductCashOrBank($locked, $cashPrice);
                    $paidLabel = '€'.number_format($cashPrice, 0, ',', '.');
                }
            }

            $locked->applyReviveSerum();

            AuditLogService::log(
                action: 'hitman_buy_revive_serum',
                category: 'combat',
                description: "{$locked->name} kocht {$serumName} ({$paidLabel})",
                character: $locked,
                user: $locked->user
            );

            return back()->with('success', "💉 CODE BLAUW OPGEHEVEN! De {$serumName} is direct in het hart toegediend ({$paidLabel}). Je bent weer volledig tot leven gewekt met 100% HP, je liquidatieteller staat weer op 0/10 en je hebt 30 minuten Medisch Schild!");
        });
    }

    public function contract(Request $request): RedirectResponse
    {
        /** @var Character|null $character */
        $character = Auth::user()?->character;

        if (! $character) {
            return redirect()->route('character.create');
        }

        if ($character->isImprisoned()) {
            return redirect()->route('prison')->with('error', 'Vanuit de isoleercel kun je geen liquidatieopdrachten uitvoeren!');
        }

        if ($character->isInVoluntaryIsolation() && ! $request->boolean('confirm_break_isolation')) {
            $remaining = $character->protection_until->diffForHumans(null, true);

            return back()
                ->with('error', "🔒 Je cel is vergrendeld in Vrijwillige Isolatie (nog {$remaining})! Bevestig dat je je isolatie wilt beëindigen om een contract uit te zetten.")
                ->with('error_url', route('isolation.index'));
        }

        if ($character->isComatose() || $character->needsReviveSerum()) {
            return back()->with('error', 'Je verkeert in coma of Code Blauw (Hartstilstand)! Dien eerst een '.Character::REVIVE_SERUM_NAME.' toe.');
        }

        if (! $request->filled('target_id') && $request->filled('target_name')) {
            $nameInput = trim((string) $request->input('target_name'));
            $found = Character::whereRaw('LOWER(name) = ?', [strtolower($nameInput)])->first();
            if ($found) {
                $request->merge(['target_id' => $found->id]);
            }
        }

        $validated = $request->validate([
            'target_id' => ['required', 'exists:characters,id'],
            'contract_id' => ['required', 'integer', 'in:1,2,3'],
        ]);

        $config = [
            1 => ['cost' => 15000, 'energy' => 15, 'damage' => 20, 'rate' => 75, 'xp' => 60,  'label' => 'Waarschuwingsslag'],
            2 => ['cost' => 35000, 'energy' => 20, 'damage' => 35, 'rate' => 65, 'xp' => 110, 'label' => 'Klap bij de kantine'],
            3 => ['cost' => 75000, 'energy' => 25, 'damage' => 55, 'rate' => 50, 'xp' => 200, 'label' => 'Gewelddadige overval'],
        ];

        $contract = $config[$validated['contract_id']];
        $target = Character::findOrFail($validated['target_id']);

        if ($target->id === $character->id) {
            return back()->with('error', 'Je kunt geen contract op jezelf plaatsen!');
        }

        if ($target->isImprisoned()) {
            return back()->with('error', 'Dit doelwit zit in de isoleercel en kan niet worden aangevallen.');
        }

        if ($target->isComatose() || $target->needsReviveSerum()) {
            return back()->with('error', "{$target->name} ligt al in coma of Code Blauw (Hartstilstand na 10 liquidaties)!");
        }

        if ($target->hasIsolationProtection()) {
            return back()->with('error', "{$target->name} heeft actieve bescherming en kan niet worden aangevallen.");
        }

        $bloodFeud = BloodFeud::hasClaimAgainst($character, $target->id);

        if (! $bloodFeud && ($character->level - $target->level > 15)) {
            return back()->with('error', "{$target->name} is te laag in level (maximaal 15 levels verschil). Zoek een tegenstander van jouw niveau!");
        }

        if (($character->cash + $character->bank) < $contract['cost']) {
            return back()->with('error', 'Dit contract kost €'.number_format($contract['cost'], 0, ',', '.').'. Je hebt onvoldoende geld.');
        }

        return Cache::lock('character_action_'.$target->user_id, 10)->block(5, function () use ($character, $target, $contract, $bloodFeud): RedirectResponse {
            return DB::transaction(function () use ($character, $target, $contract, $bloodFeud): RedirectResponse {
                $firstId = min($character->id, $target->id);
                $secondId = max($character->id, $target->id);
                $lockedPair = Character::whereIn('id', [$firstId, $secondId])
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');

                /** @var Character|null $lockedAttacker */
                $lockedAttacker = $lockedPair->get($character->id);
                /** @var Character|null $lockedTarget */
                $lockedTarget = $lockedPair->get($target->id);

                if (! $lockedAttacker || ! $lockedTarget) {
                    return back()->with('error', 'Doelwit kon niet worden gevonden.');
                }

                if ($lockedAttacker->isImprisoned() || $lockedAttacker->isComatose() || $lockedAttacker->needsReviveSerum()) {
                    return back()->with('error', 'Je kunt dit contract momenteel niet uitvoeren.');
                }

                if ($lockedTarget->isImprisoned()) {
                    return back()->with('error', 'Dit doelwit zit in de isoleercel en kan niet worden aangevallen.');
                }

                if ($lockedTarget->isComatose() || $lockedTarget->needsReviveSerum()) {
                    return back()->with('error', "{$lockedTarget->name} ligt al in coma of Code Blauw (Hartstilstand na 10 liquidaties)!");
                }

                if ($lockedTarget->hasIsolationProtection()) {
                    return back()->with('error', "{$lockedTarget->name} heeft actieve bescherming en kan niet worden aangevallen.");
                }

                if (! $bloodFeud && ($lockedAttacker->level - $lockedTarget->level > 15)) {
                    return back()->with('error', "{$lockedTarget->name} is te laag in level (maximaal 15 levels verschil). Zoek een tegenstander van jouw niveau!");
                }

                if (($lockedAttacker->cash + $lockedAttacker->bank) < $contract['cost']) {
                    return back()->with('error', 'Dit contract kost €'.number_format($contract['cost'], 0, ',', '.').'. Je hebt onvoldoende geld.');
                }

                if (! $this->energy->consume($lockedAttacker, $contract['energy'])) {
                    return back()->with('error', "Je hebt minimaal {$contract['energy']} energie nodig voor dit contract!");
                }

                $lockedAttacker->breakProtection();
                $this->deductCashOrBank($lockedAttacker, $contract['cost']);

                $roll = rand(1, 100);
                if ($roll <= $contract['rate']) {
                    $lockedTarget->health = max(5, $lockedTarget->health - $contract['damage']);
                    $lockedTarget->save();

                    $this->level->addXp($lockedAttacker, $contract['xp']);
                    $lockedAttacker->addRespect(5);
                    $lockedAttacker->save();

                    GameNotification::create([
                        'character_id' => $lockedTarget->id,
                        'type' => 'attack',
                        'icon' => 'fa-skull-crossbones',
                        'title' => 'Je bent aangevallen!',
                        'message' => "Een onbekende heeft een onderwereld-contract op je uitgevoerd ({$contract['label']}). Je verloor {$contract['damage']} HP.",
                    ]);

                    return back()->with('success', "Contract succesvol uitgevoerd! {$lockedTarget->name} verliest {$contract['damage']} HP. +{$contract['xp']} XP, +5 Respect.");
                }

                if (rand(1, 100) <= 35) {
                    $this->prison->jail($lockedAttacker, 15, 'Betrapt bij het uitvoeren van een liquidatieopdracht.');

                    return redirect()->route('prison')->with('error', 'Contract mislukt! Jij bent opgepakt door de bewaking. 15 minuten isoleercel.');
                }

                return back()->with('error', 'Contract mislukt! Je handlanger botste op de bewaking en is gevlucht.');
            });
        });
    }

    /**
     * Direct Liquidation 2.0:
     * - Requires Same Clinic Wing (`current_location`)
     * - Enforces Dossier-Graad (1-30) & Koudbloedigheid (%) for 1-Bullet Genadeschot vs Salvo
     * - Plunders 45% Cash on lethal hit (Bank & Kluis are 100% safe)
     * - Unlimited hunting with Stretcher Evacuation (20 HP + new random wing + 15m shield) on hits 1-9
     * - At 10th liquidation: target enters Code Blauw and requires the €1.500.000 Forensisch Reanimatie-Serum!
     */
    public function shoot(Request $request): RedirectResponse
    {
        /** @var Character|null $attacker */
        $attacker = Auth::user()?->character;

        if (! $attacker) {
            return redirect()->route('character.create');
        }

        $this->energy->refresh($attacker);

        if ($attacker->isImprisoned()) {
            return redirect()->route('prison')->with('error', 'Vanuit de isoleercel kun je niemand liquideren!');
        }

        if ($attacker->isInVoluntaryIsolation() && ! $request->boolean('confirm_break_isolation')) {
            $remaining = $attacker->protection_until->diffForHumans(null, true);

            return back()
                ->with('error', "🔒 Je cel is vergrendeld in Vrijwillige Isolatie (nog {$remaining})! Bevestig dat je je isolatie wilt beëindigen om een liquidatie uit te voeren.")
                ->with('error_url', route('isolation.index'));
        }

        if ($attacker->isComatose() || $attacker->needsReviveSerum()) {
            return back()->with('error', '⚠️ Je verkeert in coma of Code Blauw (Hartstilstand na 10 liquidaties)! Dien eerst een '.Character::REVIVE_SERUM_NAME.' toe om weer tot leven te komen.');
        }

        if (! $request->filled('target_id') && $request->filled('target_name')) {
            $nameInput = trim((string) $request->input('target_name'));
            $found = Character::whereRaw('LOWER(name) = ?', [strtolower($nameInput)])->first();
            if ($found) {
                $request->merge(['target_id' => $found->id]);
            }
        }

        $validated = $request->validate([
            'target_id' => ['required', 'exists:characters,id'],
            'bullets' => ['required', 'integer', 'min:1', 'max:10000'],
        ]);

        $defender = Character::findOrFail($validated['target_id']);

        if ($defender->id === $attacker->id) {
            return back()->with('error', 'Je kunt niet op jezelf schieten!');
        }

        if ($defender->isImprisoned()) {
            return back()->with('error', 'Dit doelwit zit veilig in de isoleercel en kan niet worden geliquideerd.');
        }

        if ($defender->isComatose() || $defender->needsReviveSerum()) {
            return back()->with('error', "{$defender->name} is al 10x geliquideerd of ligt in coma / Code Blauw (Hartstilstand) te wachten op een Reanimatie-Serum!");
        }

        if ($defender->hasIsolationProtection()) {
            return back()->with('error', "{$defender->name} heeft momenteel een actief Medisch Infuus-Schild of Isolatiebescherming en kan even niet worden beschoten.");
        }

        $todayStr = now()->toDateString();
        $pairKillKey = "daily_liquidations_{$attacker->id}_{$defender->id}_{$todayStr}";
        if ((int) Cache::get($pairKillKey, 0) >= Character::MAX_DAILY_LIQUIDATIONS_PER_TARGET) {
            return back()->with('error', "⛔ Anti-Pestprotocol: Je hebt {$defender->name} vandaag al ".Character::MAX_DAILY_LIQUIDATIONS_PER_TARGET.' keer geliquideerd! Zoek vandaag een ander doelwit.');
        }

        if ($defender->getDailyDeathsCount() >= Character::MAX_DAILY_DEATHS_BEFORE_SHIELD) {
            return back()->with('error', "🏥 Medisch Noodprotocol: {$defender->name} is vandaag al ".Character::MAX_DAILY_DEATHS_BEFORE_SHIELD.' keer geliquideerd en staat de rest van de dag onder verscherpte IC-bewaking!');
        }

        // Clinic Wing / Location status
        $attackerWing = $attacker->current_location ?? 'Paviljoen 1 (Gesloten)';
        $defenderWing = $defender->current_location ?? 'Paviljoen 1 (Gesloten)';
        $isSameWing = ($attackerWing === $defenderWing);

        // Validate Dossier Grade & Koudbloedigheid bullet requirements
        $bulletCount = (int) $validated['bullets'];
        $gradeInfo = $attacker->getSyndicateGradeInfo();
        $coldBloodedPct = $attacker->getColdBloodedPct();
        $isOneTap = ($bulletCount === 1 && $attacker->canOneTapExecute());

        if ($bulletCount === 1 && ! $attacker->canOneTapExecute()) {
            if ($gradeInfo['grade'] < 25) {
                return back()->with('error', "🔒 1-PATROON GENADESCHOT VERGRENDELD! Voor een 1-patroon precisie-executie heb je minimaal Dossier-Graad 25 (Forensisch Scherpschutter) nodig. Jouw huidige status is Graad {$gradeInfo['grade']} ({$gradeInfo['title']}). Vuur een salvo af (minimaal 10 patronen) of train je Koudbloedigheid!");
            }

            if ($coldBloodedPct < 30) {
                return back()->with('error', "❄️ TE WEINIG KOUDBLOEDIGHEID! Je Koudbloedigheid is {$coldBloodedPct}% (minimaal 30% vereist voor een 1-patroon Genadeschot). Train eerst je Koudbloedigheid op de Schietbaan of vuur een salvo af!");
            }
        }

        if ($bulletCount < 10 && ! $isOneTap) {
            return back()->with('error', 'Een salvo vereist minimaal 10 patronen (tenzij je het 1-Patroon Genadeschot hebt ontgrendeld)!');
        }

        if (($attacker->bullets ?? 0) < $bulletCount) {
            return back()->with('error', "Onvoldoende munitie! Je hebt {$attacker->bullets} patronen op zak, maar je probeert er {$bulletCount} af te vuren. Koop een munitiekist in het Wapenarsenaal.");
        }

        $bloodFeud = BloodFeud::hasClaimAgainst($attacker, $defender->id);
        $hasOpenBounty = class_exists(Bounty::class) && Bounty::where('target_character_id', $defender->id)->where('is_claimed', false)->exists();

        if (! $bloodFeud && ! $hasOpenBounty && abs($attacker->level - $defender->level) > 15) {
            return back()->with('error', "Het levelverschil tussen jou en {$defender->name} is te groot (maximaal 15 levels verschil, tenzij er Bloedwraak of een Kopgeld-premie openstaat).");
        }

        // Take the target's action lock (the same lock his own requests use). Two shooters on one target then run one
        // after the other, so the second one sees the protection, cash and bounties that the first one left behind.
        $targetLock = Cache::lock('character_action_'.$defender->user_id, SerializeCharacterActions::LOCK_TTL_SECONDS);

        try {
            return $targetLock->block(3, function () use ($attacker, $defender, $bulletCount, $gradeInfo, $coldBloodedPct, $isOneTap): RedirectResponse {
                return DB::transaction(function () use ($attacker, $defender, $bulletCount, $gradeInfo, $coldBloodedPct, $isOneTap): RedirectResponse {
                    $firstId = min($attacker->id, $defender->id);
                    $secondId = max($attacker->id, $defender->id);
                    $lockedPair = Character::whereIn('id', [$firstId, $secondId])
                        ->orderBy('id')
                        ->lockForUpdate()
                        ->get()
                        ->keyBy('id');

                    /** @var Character|null $lockedAttacker */
                    $lockedAttacker = $lockedPair->get($attacker->id);
                    /** @var Character|null $lockedDefender */
                    $lockedDefender = $lockedPair->get($defender->id);

                    if (! $lockedAttacker || ! $lockedDefender) {
                        return back()->with('error', 'Tegenstander kon niet worden gevonden.');
                    }

                    if ($lockedAttacker->isImprisoned() || $lockedAttacker->isComatose() || $lockedAttacker->needsReviveSerum()) {
                        return back()->with('error', 'Je kunt momenteel geen liquidatie uitvoeren.');
                    }

                    if ($lockedDefender->isImprisoned() || $lockedDefender->isComatose() || $lockedDefender->needsReviveSerum() || $lockedDefender->hasIsolationProtection()) {
                        return back()->with('error', "{$lockedDefender->name} is zojuist al geraakt of beschermd en kan nu niet worden beschoten.");
                    }

                    $pairKillKey = "daily_liquidations_{$lockedAttacker->id}_{$lockedDefender->id}_".now()->toDateString();
                    $pairKillsToday = (int) Cache::get($pairKillKey, 0);
                    if ($pairKillsToday >= Character::MAX_DAILY_LIQUIDATIONS_PER_TARGET) {
                        return back()->with('error', "⛔ Anti-Pestprotocol: Je hebt {$lockedDefender->name} vandaag al ".Character::MAX_DAILY_LIQUIDATIONS_PER_TARGET.' keer geliquideerd! Zoek vandaag een ander doelwit.');
                    }

                    if ($lockedDefender->getDailyDeathsCount() >= Character::MAX_DAILY_DEATHS_BEFORE_SHIELD) {
                        return back()->with('error', "🏥 Medisch Noodprotocol: {$lockedDefender->name} is vandaag al ".Character::MAX_DAILY_DEATHS_BEFORE_SHIELD.' keer geliquideerd en staat de rest van de dag onder verscherpte IC-bewaking!');
                    }

                    if (($lockedAttacker->bullets ?? 0) < $bulletCount) {
                        return back()->with('error', "Onvoldoende munitie! Je hebt {$lockedAttacker->bullets} patronen op zak, maar je probeert er {$bulletCount} af te vuren. Koop een munitiekist in het Wapenarsenaal.");
                    }

                    if (! $this->energy->consume($lockedAttacker, 20)) {
                        return back()->with('error', 'Je hebt minimaal 20 energie nodig om een liquidatie uit te voeren.');
                    }

                    $lockedAttacker->breakProtection();

                    // Deduct bullets & Koudbloedigheid focus
                    $lockedAttacker->bullets -= $bulletCount;
                    $lockedAttacker->save();
                    $lockedAttacker->adjustColdBloodedPct($isOneTap ? -12 : -6);

                    $attackerWing = $lockedAttacker->current_location ?? 'Paviljoen 1 (Gesloten)';
                    $defenderWing = $lockedDefender->current_location ?? 'Paviljoen 1 (Gesloten)';
                    $isSameWing = ($attackerWing === $defenderWing);

                    // Calculate Attack Power vs Defender Armor
                    if ($isOneTap) {
                        $attackerPower = 650 + ($gradeInfo['grade'] * 25) + ($lockedAttacker->getEffectiveStrength() * 2.5) + ($coldBloodedPct * 3.5);
                    } else {
                        $attackerPower = ($bulletCount * 8.0) + ($lockedAttacker->getEffectiveStrength() * 2.5) + ($gradeInfo['grade'] * 20) + rand(20, 50);

                        // Bonus if player fires at or above their grade's recommended heavy barrage
                        if ($bulletCount >= $gradeInfo['min_bullets']) {
                            $attackerPower *= 1.25; // +25% heavy barrage bonus!
                        }
                    }

                    // Sluipaanval / Direct Zicht Bonus (+25% power when in the same clinic wing!)
                    if ($isSameWing) {
                        $attackerPower *= 1.25;
                    }

                    $defenderDefense = ($lockedDefender->getEffectiveDefense() * 1.8) + rand(10, 30);
                    $hasVest = $lockedDefender->hasVest();
                    $vestInfo = $lockedDefender->getActiveVestInfo();

                    if ($hasVest && $vestInfo) {
                        $pierceFactor = max(0.35, 1 - ($gradeInfo['armor_pierce'] / 100));
                        $defenderDefense += (int) round($vestInfo['defense_bonus'] * $pierceFactor);
                    }

                    // 1. BACKFIRE CHECK (Target's Backfire Vest ricochets the attack back onto the shooter!)
                    $backfireChance = $hasVest ? $lockedDefender->getVestBackfireChance() : 12;
                    if ($attackerPower < $defenderDefense && rand(1, 100) <= $backfireChance) {
                        $lockedAttacker->deaths_count = ($lockedAttacker->deaths_count ?? 0) + 1;
                        $lockedAttacker->incrementDailyDeaths();
                        $attackerStreak = $lockedAttacker->incrementLiquidationStreakDeaths();
                        $evacWing = $this->evacuateToRandomWing($lockedAttacker);

                        if ($attackerStreak < Character::MAX_LIQUIDATIONS_BEFORE_SERUM) {
                            $lockedAttacker->health = 20;
                            $lockedAttacker->coma_until = null;
                            $lockedAttacker->grantMedicalShield(15);
                            $lockedAttacker->save();
                        } else {
                            $lockedAttacker->health = 0;
                            $lockedAttacker->coma_until = now()->addHours(6);
                            $lockedAttacker->grantMedicalShield(30);
                            $lockedAttacker->save();
                        }

                        $lockedDefender->addRespect(25);
                        $lockedDefender->save();

                        $vestLabel = $vestInfo['name'] ?? 'Backfire-Pantser';

                        GameNotification::create([
                            'character_id' => $lockedDefender->id,
                            'type' => 'shield',
                            'icon' => 'fa-shield-halved',
                            'title' => '🛡️ BACKFIRE VOLTREFFER (+25 Respect)!',
                            'message' => "{$lockedAttacker->name} vuurde op {$defenderWing} {$bulletCount} patro(o)n(en) op je af, maar jouw {$vestLabel} kaatste het schot terug! {$lockedAttacker->name} is zwaargewond per brancard afgevoerd naar een andere afdeling!",
                        ]);

                        try {
                            $backfireDm = $attackerStreak >= Character::MAX_LIQUIDATIONS_BEFORE_SERUM
                                ? "💥 DODELIJKE BACKFIRE OP {$defenderWing} (10/10): Het {$vestLabel} van {$lockedDefender->name} kaatste je schot terug! Je verkeert nu in Code Blauw (Hartstilstand) en hebt een ".Character::REVIVE_SERUM_NAME.' nodig om weer tot leven te komen!'
                                : "💥 BACKFIRE OP {$defenderWing} ({$attackerStreak}/10): Het {$vestLabel} van {$lockedDefender->name} kaatste jouw kogels terug! Je bent met 20 HP per brancard geëvacueerd naar {$evacWing} en kunt direct eten om te healen.";
                            app(ChatBotService::class)->sendLiquidationNotice($lockedAttacker, $backfireDm, [
                                'subtype' => 'backfire',
                                'location' => $defenderWing,
                                'attacker_id' => $lockedAttacker->id,
                                'defender_id' => $lockedDefender->id,
                            ]);
                        } catch (\Throwable) {
                        }

                        if ($attackerStreak >= Character::MAX_LIQUIDATIONS_BEFORE_SERUM) {
                            return back()->with('error', "💥 DODELIJKE BACKFIRE (10/10 LIQUIDATIES)! Het {$vestLabel} van {$lockedDefender->name} kaatste je schot terug! Dit was je 10e fatale treffer: je verkeert nu in Code Blauw (Hartstilstand) en hebt een ".Character::REVIVE_SERUM_NAME.' nodig om weer tot leven te komen!');
                        }

                        return back()->with('error', "💥 BACKFIRE OP {$defenderWing}! Het {$vestLabel} van {$lockedDefender->name} kaatste jouw kogels vol terug! Je bent gezakt naar 20 HP (Liquidatie-teller: {$attackerStreak}/10), per brancard geëvacueerd naar {$evacWing} en kunt direct eten om te healen!");
                    }

                    // 2. LETHAL LIQUIDATION HIT (45% Cash stolen, bank 100% safe!)
                    if ($attackerPower >= $defenderDefense) {
                        // 25% chance a heavy vest absorbs a non-overwhelming hit and shatters
                        if ($hasVest && $attackerPower < ($defenderDefense * 1.35) && rand(1, 100) <= 25) {
                            $lockedDefender->vest_until = null;
                            $dmg = min(max(1, $lockedDefender->health - 15), rand(35, 65));
                            $lockedDefender->health = max(15, $lockedDefender->health - $dmg);
                            $lockedDefender->save();

                            GameNotification::create([
                                'character_id' => $lockedDefender->id,
                                'type' => 'attack',
                                'icon' => 'fa-shield-halved',
                                'title' => 'Pantser vernietigd bij aanslag!',
                                'message' => "{$lockedAttacker->name} schoot op {$defenderWing} met {$bulletCount} patro(o)n(en)! Je {$vestInfo['name']} ving de fatale klap op maar is daarbij volledig vernietigd (-{$dmg} HP).",
                            ]);

                            return back()->with('info', "🛡️ PANTSEBREUK! Je schot op {$lockedDefender->name} heeft zijn {$vestInfo['name']} volledig aan flarden geschoten (-{$dmg} HP)! Het doelwit heeft nu géén pantser meer.");
                        }

                        // Plunder 45% Cash only - Bank & Kluis are 100% safe upon liquidation! (halved by 50% when defender has active Cash Shield)
                        $shieldMultiplier = $lockedDefender->hasCashProtection() ? 0.50 : 1.0;
                        $stolenCash = (int) floor(max(0, (int) $lockedDefender->cash) * 0.45 * $shieldMultiplier);
                        $stolenBank = 0;
                        $totalStolen = $stolenCash;

                        if ($stolenCash > 0) {
                            $lockedDefender->cash = max(0, (int) $lockedDefender->cash - $stolenCash);
                        }
                        if ($totalStolen > 0) {
                            $lockedAttacker->cash = (int) $lockedAttacker->cash + $totalStolen;
                            EventCashHuntService::record($lockedAttacker, $totalStolen, 'hitman_loot');
                        }

                        $lockedDefender->deaths_count = ($lockedDefender->deaths_count ?? 0) + 1;
                        $lockedDefender->incrementDailyDeaths();
                        Cache::put($pairKillKey, $pairKillsToday + 1, now()->endOfDay());

                        // Increment 0/10 liquidation streak counter
                        $defenderStreak = $lockedDefender->incrementLiquidationStreakDeaths();
                        $newWing = $this->evacuateToRandomWing($lockedDefender);

                        if ($defenderStreak < Character::MAX_LIQUIDATIONS_BEFORE_SERUM) {
                            // Victims on hits 1-9 enter a 10-minute Intensive Care Coma with 60-minute post-coma medical shield (70 min total)
                            $lockedDefender->health = 0;
                            $lockedDefender->coma_until = now()->addMinutes(Character::LIQUIDATION_IC_COMA_MINUTES);
                            $lockedDefender->grantMedicalShield(Character::LIQUIDATION_IC_COMA_MINUTES + 60);
                            $lockedDefender->save();
                        } else {
                            // 10th liquidation: Code Blauw (requires Forensisch Reanimatie-Serum)
                            $lockedDefender->grantMedicalShield(60);
                            $lockedDefender->save();
                        }

                        $lockedAttacker->kills_count = ($lockedAttacker->kills_count ?? 0) + 1;
                        $baseRespect = 60;

                        // Claim any open Bounties on defender
                        $bountyReward = 0;
                        $bountyPlacerIds = collect();
                        if (class_exists(Bounty::class)) {
                            $openBounties = Bounty::where('target_character_id', $lockedDefender->id)
                                ->where('is_claimed', false)
                                ->lockForUpdate()
                                ->get();

                            foreach ($openBounties as $bounty) {
                                $bountyReward += (int) $bounty->amount;
                                if ($bounty->placed_by_character_id) {
                                    $bountyPlacerIds->push((int) $bounty->placed_by_character_id);
                                }
                                $bounty->update([
                                    'is_claimed' => true,
                                    'claimed_by_character_id' => $lockedAttacker->id,
                                    'claimed_at' => now(),
                                ]);
                            }

                            if ($bountyReward > 0) {
                                $lockedAttacker->cash += $bountyReward;
                                EventCashHuntService::record($lockedAttacker, $bountyReward, 'hitman_bounty');
                            }
                        }

                        // Handle Blood Feud resolution or creation
                        $bloodFeud = BloodFeud::hasClaimAgainst($lockedAttacker, $lockedDefender->id);
                        $avengedVictim = null;
                        if ($bloodFeud) {
                            $avengedVictim = $bloodFeud->victim_id ? Character::find($bloodFeud->victim_id) : null;
                            $bloodFeud->update([
                                'is_avenged' => true,
                                'avenged_by_id' => $lockedAttacker->id,
                                'avenged_at' => now(),
                            ]);
                            $baseRespect += 50;
                            $lockedAttacker->cash += 25000;
                            if ($lockedAttacker->gang && Schema::hasColumn('gangs', 'respect')) {
                                $lockedAttacker->gang->increment('respect', 150);
                            }
                        } else {
                            BloodFeud::create([
                                'attacker_id' => $lockedAttacker->id,
                                'victim_id' => $lockedDefender->id,
                                'victim_gang_id' => $lockedDefender->gang_id,
                                'expires_at' => now()->addHours(24),
                            ]);
                        }

                        $lockedAttacker->addRespect($baseRespect);
                        $this->level->addXp($lockedAttacker, 400);
                        $lockedAttacker->save();

                        $shotTypeLabel = $isOneTap ? 'een 1-Patroon Genadeschot' : "een salvo van {$bulletCount} patronen".($bulletCount >= $gradeInfo['min_bullets'] ? ' (Zware Barrage)' : '');
                        $locationNotice = $isSameWing ? "op {$defenderWing} (+25% Sluipaanval-bonus)" : "op {$defenderWing} (Afstandssalvo vanaf {$attackerWing})";
                        $lootBreakdown = '€'.number_format($stolenCash, 0, ',', '.').' contant (45%, kluis/bank 100% veilig)';

                        // Send 1 unified private message from Kliniek Directie to each involved party BEFORE GameNotification::create
                        // so GameNotification::booted() does not create a duplicate DM for the victim.
                        try {
                            $totalFmt = '€'.number_format($totalStolen, 0, ',', '.');
                            $headline = "🎯 LIQUIDATIE OP {$defenderWing}: {$lockedAttacker->name} heeft {$lockedDefender->name} neergeschoten met {$shotTypeLabel} en {$totalFmt} contant geld buitgemaakt (kluis/bank 100% veilig)!";
                            $feudSection = $bloodFeud
                                ? "\n\n🩸 BLOEDWRAAK VOLTROKKEN: {$lockedAttacker->name} heeft {$lockedDefender->name} op {$defenderWing} geliquideerd en de bende-vete gewroken!"
                                : '';
                            $bountySection = $bountyReward > 0
                                ? "\n\n💰 KOPGELD GEÏND: Openstaande premie van €".number_format($bountyReward, 0, ',', '.')." op {$lockedDefender->name} is uitbetaald aan {$lockedAttacker->name}!"
                                : '';

                            $commonMeta = [
                                'subtype' => $bloodFeud ? 'blood_feud' : 'liquidation',
                                'location' => $defenderWing,
                                'attacker_id' => $lockedAttacker->id,
                                'defender_id' => $lockedDefender->id,
                                'stolen_cash' => $stolenCash,
                                'stolen_bank' => 0,
                                'total_stolen' => $totalStolen,
                            ];

                            $botService = app(ChatBotService::class);

                            // 1. Attacker report
                            $attackerSummary = "\n\n📊 Jouw Resultaat: +{$baseRespect} Respect, +400 XP! Doelwitstatus: {$defenderStreak}/10"
                                .($defenderStreak >= Character::MAX_LIQUIDATIONS_BEFORE_SERUM ? ' (Code Blauw Hartstilstand).' : ' (Per brancard afgevoerd).');
                            $botService->sendLiquidationNotice($lockedAttacker, $headline.$feudSection.$bountySection.$attackerSummary, array_merge($commonMeta, ['role' => 'attacker']));

                            // 2. Defender (victim) report
                            $defenderMedical = $defenderStreak >= Character::MAX_LIQUIDATIONS_BEFORE_SERUM
                                ? "\n\n🚨 Medische Status (10/10 — CODE BLAUW): Omdat je nu 10x bent geliquideerd (10/10), verkeer je in Code Blauw en moet je het ".Character::REVIVE_SERUM_NAME.' aanschaffen om weer tot leven te komen!'
                                : "\n\n🏥 Medische Status ({$defenderStreak}/10): Je bent met spoed per brancard naar de Intensive Care afgevoerd (".Character::LIQUIDATION_IC_COMA_MINUTES." min IC-Coma & 60 min Medisch Infuus-Schild) op {$newWing}!";
                            $botService->sendLiquidationNotice($lockedDefender, $headline.$feudSection.$bountySection.$defenderMedical, array_merge($commonMeta, ['role' => 'defender']));

                            // 3. Additional involved parties (original blood-feud victim and/or bounty placers)
                            $notifiedIds = [$lockedAttacker->id, $lockedDefender->id];

                            if ($avengedVictim && ! in_array($avengedVictim->id, $notifiedIds, true)) {
                                $botService->sendLiquidationNotice($avengedVictim, $headline.$feudSection.$bountySection, array_merge($commonMeta, ['role' => 'avenged_victim']));
                                $notifiedIds[] = $avengedVictim->id;
                            }

                            if ($bountyPlacerIds->isNotEmpty()) {
                                $bountyPlacers = Character::whereIn('id', $bountyPlacerIds->unique()->diff($notifiedIds))->get();
                                foreach ($bountyPlacers as $placer) {
                                    $botService->sendLiquidationNotice($placer, $headline.$bountySection.$feudSection, array_merge($commonMeta, ['role' => 'bounty_placer']));
                                    $notifiedIds[] = $placer->id;
                                }
                            }
                        } catch (\Throwable) {
                        }

                        if ($defenderStreak >= Character::MAX_LIQUIDATIONS_BEFORE_SERUM) {
                            GameNotification::create([
                                'character_id' => $lockedDefender->id,
                                'type' => 'skull',
                                'icon' => 'fa-heart-crack',
                                'title' => '🚨 10/10 GELIQUIDEERD — CODE BLAUW!',
                                'message' => "{$lockedAttacker->name} heeft je {$locationNotice} geliquideerd met {$shotTypeLabel}! Er is {$lootBreakdown} geplunderd. Omdat je nu 10x bent geliquideerd (10/10), verkeer je in Code Blauw en moet je het ".Character::REVIVE_SERUM_NAME.' aanschaffen om weer tot leven te komen!',
                            ]);
                        } else {
                            GameNotification::create([
                                'character_id' => $lockedDefender->id,
                                'type' => 'skull',
                                'icon' => 'fa-skull',
                                'title' => "🚨 GELIQUIDEERD OP {$defenderWing} ({$defenderStreak}/10)!",
                                'message' => "{$lockedAttacker->name} heeft je {$locationNotice} geliquideerd met {$shotTypeLabel}! Er is {$lootBreakdown} geplunderd. Je ligt ".Character::LIQUIDATION_IC_COMA_MINUTES." min op de Intensive Care ({$newWing}) met 60 min Medisch Infuus-Schild!",
                            ]);
                        }

                        $bountyMsg = $bountyReward > 0 ? ' + €'.number_format($bountyReward, 0, ',', '.').' Kopgeld-premie geïnd!' : '';
                        $feudMsg = $bloodFeud ? ' 🩸 BLOEDWRAAK VOLTROKKEN (+50 Respect, +€25.000 Bonus)!' : '';
                        $codeBlauwMsg = $defenderStreak >= Character::MAX_LIQUIDATIONS_BEFORE_SERUM
                            ? " {$lockedDefender->name} heeft 10/10 liquidaties bereikt en ligt in Code Blauw Hartstilstand!"
                            : " Doelwit ({$defenderStreak}/10) is per brancard afgevoerd naar een onbekende afdeling.";

                        return back()->with('success', "🎯 LIQUIDATIE VOLTROKKEN! Je hebt {$lockedDefender->name} {$locationNotice} neergeschoten met {$shotTypeLabel}! Totale buit: €".number_format($totalStolen, 0, ',', '.')." ({$lootBreakdown}){$bountyMsg}{$feudMsg}{$codeBlauwMsg} +{$baseRespect} Respect, +400 XP!");
                    }

                    // 3. GRAZED / SURVIVED (Attacker didn't have enough firepower to penetrate defense)
                    $dmg = rand(20, 45);
                    $lockedDefender->health = max(15, $lockedDefender->health - $dmg);
                    $lockedDefender->save();

                    GameNotification::create([
                        'character_id' => $lockedDefender->id,
                        'type' => 'attack',
                        'icon' => 'fa-gun',
                        'title' => "Onder vuur genomen op {$defenderWing}!",
                        'message' => "{$lockedAttacker->name} vuurde {$bulletCount} patro(o)n(en) op je af vanaf {$attackerWing}! Je raakte gewond (-{$dmg} HP), maar je verdediging hield stand.",
                    ]);

                    return back()->with('error', "⚠️ SCHAMPSCHOT! Je vuurde {$bulletCount} patro(o)n(en) af op {$lockedDefender->name} (-{$dmg} HP), maar zijn verdediging/pantser hield stand. Vuur een zwaarder salvo af (aanbevolen: {$gradeInfo['min_bullets']} patronen) of verhoog je Dossier-Graad & Kracht!");
                });
            });
        } catch (LockTimeoutException) {
            return back()->with('error', "{$defender->name} wordt op dit moment al aangevallen. Probeer het over enkele seconden opnieuw.");
        }
    }

    /**
     * Buy Ammunition Crates with Cash/Bank or Stars ⭐.
     */
    public function buyBullets(Request $request): RedirectResponse
    {
        /** @var User|null $user */
        $user = Auth::user();
        /** @var Character|null $character */
        $character = $user?->character;

        if (! $character) {
            return redirect()->route('character.create');
        }

        $catalog = Character::ammoCatalog();
        $validated = $request->validate([
            'ammo_tier' => ['nullable', 'string', 'in:'.implode(',', array_keys($catalog))],
            'currency' => ['nullable', 'string', 'in:cash,stars'],
        ]);
        $tierKey = $validated['ammo_tier'] ?? 'box_50';
        $ammo = $catalog[$tierKey] ?? $catalog['box_50'];
        $currency = $validated['currency'] ?? 'stars';

        return DB::transaction(function () use ($character, $ammo, $currency): RedirectResponse {
            /** @var Character $locked */
            $locked = Character::where('id', $character->id)->lockForUpdate()->first();

            if ($locked->isImprisoned()) {
                return back()->with('error', 'Vanuit de isoleercel kun je geen munitie aanschaffen!');
            }

            if ($locked->isComatose() || $locked->needsReviveSerum()) {
                return back()->with('error', 'Je verkeert in coma of Code Blauw (Hartstilstand)! Dien eerst een '.Character::REVIVE_SERUM_NAME.' toe.');
            }

            if ($currency === 'cash') {
                $costCash = $ammo['cost_cash'];
                if (($locked->cash + $locked->bank) < $costCash) {
                    return back()->with('error', "Onvoldoende geld! {$ammo['name']} kost €".number_format($costCash, 0, ',', '.').' (contant + bank gecombineerd).');
                }
                $this->deductCashOrBank($locked, $costCash);
                $paidText = '€'.number_format($costCash, 0, ',', '.');
            } else {
                $costStars = $ammo['cost_stars'];
                if (($locked->stars ?? 0) < $costStars) {
                    return back()->with('error', "Onvoldoende sterren! Je hebt {$costStars} Sterren ⭐ nodig voor {$ammo['name']} (je hebt er {$locked->stars}).");
                }
                $locked->stars -= $costStars;
                $paidText = "{$costStars} Sterren ⭐";
            }

            $locked->bullets = ($locked->bullets ?? 0) + $ammo['bullets'];

            $focusBonusMsg = '';
            if ($ammo['cold_blooded_bonus'] > 0) {
                $perks = $locked->perks ?? [];
                $newPct = min(100, $locked->getColdBloodedPct() + $ammo['cold_blooded_bonus']);
                $perks['cold_blooded_pct'] = $newPct;
                $locked->perks = $perks;
                $focusBonusMsg = " en +{$ammo['cold_blooded_bonus']}% Koudbloedigheid (nu {$newPct}%)";
            }

            $locked->save();

            AuditLogService::log(
                action: 'hitman_buy_bullets',
                category: 'economy',
                description: "{$locked->name} kocht {$ammo['name']} voor {$paidText}",
                character: $locked,
                user: $locked->user,
                amount: $ammo['bullets']
            );

            return back()->with('success', "🔫 {$ammo['name']} aangeschaft voor {$paidText}! +".number_format($ammo['bullets'], 0, ',', '.')." patronen{$focusBonusMsg} toegevoegd aan je arsenaal.");
        });
    }

    /**
     * Buy Black Market Backfire Vest (including the 3-Hour Tactical Backfire Vest for Cash!) with Cash/Bank or Stars ⭐.
     */
    public function buyVest(Request $request): RedirectResponse
    {
        /** @var User|null $user */
        $user = Auth::user();
        /** @var Character|null $character */
        $character = $user?->character;

        if (! $character) {
            return redirect()->route('character.create');
        }

        $catalog = Character::vestCatalog();
        $validated = $request->validate([
            'vest_tier' => ['nullable', 'string', 'in:'.implode(',', array_keys($catalog))],
            'currency' => ['nullable', 'string', 'in:cash,stars'],
        ]);
        $defaultTier = $validated['vest_tier'] ?? 'titanium_12h';
        $vest = $catalog[$defaultTier] ?? $catalog['tactical_3h'];
        $currency = $validated['currency'] ?? 'stars';

        return DB::transaction(function () use ($character, $vest, $currency): RedirectResponse {
            /** @var Character $locked */
            $locked = Character::where('id', $character->id)->lockForUpdate()->first();

            if ($locked->isImprisoned()) {
                return back()->with('error', 'Vanuit de isoleercel kun je geen kogelwerend vest aanschaffen!');
            }

            if ($locked->isComatose() || $locked->needsReviveSerum()) {
                return back()->with('error', 'Je verkeert in coma of Code Blauw (Hartstilstand)! Dien eerst een '.Character::REVIVE_SERUM_NAME.' toe.');
            }

            if ($currency === 'cash') {
                $costCash = $vest['cost_cash'];
                if (($locked->cash + $locked->bank) < $costCash) {
                    return back()->with('error', "Onvoldoende geld! {$vest['name']} ({$vest['hours']} uur) kost €".number_format($costCash, 0, ',', '.').' (contant + bank gecombineerd).');
                }
                $this->deductCashOrBank($locked, $costCash);
                $paidText = '€'.number_format($costCash, 0, ',', '.');
            } else {
                $costStars = $vest['cost_stars'];
                if (($locked->stars ?? 0) < $costStars) {
                    return back()->with('error', "Onvoldoende sterren! Je hebt {$costStars} Sterren ⭐ nodig voor {$vest['name']} (je hebt er {$locked->stars}).");
                }
                $locked->stars -= $costStars;
                $paidText = "{$costStars} Sterren ⭐";
            }

            $currentVest = $locked->vest_until && now()->lt($locked->vest_until) ? $locked->vest_until : now();
            $locked->vest_until = $currentVest->copy()->addHours($vest['hours']);

            $perks = $locked->perks ?? [];
            $perks['vest_tier'] = $vest['id'];
            $locked->perks = $perks;
            $locked->save();

            AuditLogService::log(
                action: 'hitman_buy_vest',
                category: 'economy',
                description: "{$locked->name} kocht {$vest['name']} ({$vest['hours']}u) voor {$paidText}",
                character: $locked,
                user: $locked->user
            );

            $hoursLeft = (int) ceil(now()->diffInSeconds($locked->vest_until) / 3600);

            return back()->with('success', "🛡️ {$vest['name']} aangetrokken voor {$paidText}! Je hebt nu {$hoursLeft} uur lang +{$vest['defense_bonus']} Verdediging en {$vest['backfire_chance']}% kans op dodelijke Backfire-terugslag!");
        });
    }

    /**
     * Deduct an amount from Cash first, and any remainder from Bank.
     */
    protected function deductCashOrBank(Character $character, int $amount): void
    {
        if ($amount <= 0) {
            return;
        }

        DB::transaction(function () use ($character, $amount): void {
            /** @var Character $locked */
            $locked = Character::where('id', $character->id)->lockForUpdate()->first();
            if (($locked->cash + $locked->bank) < $amount) {
                throw new \RuntimeException('Onvoldoende saldo voor deze actie.');
            }
            if ($locked->cash >= $amount) {
                $locked->cash -= $amount;
            } else {
                $remainder = $amount - (int) $locked->cash;
                $locked->cash = 0;
                $locked->bank = max(0, (int) $locked->bank - $remainder);
                BankTransaction::record(
                    $locked,
                    'withdrawal',
                    'bank',
                    $remainder,
                    (int) $locked->bank,
                    'Liquidatie / Contract Uitgave'
                );
            }

            $locked->save();
            $character->cash = $locked->cash;
            $character->bank = $locked->bank;
        });
    }

    /**
     * Evacuate a wounded victim by stretcher to a random different clinic wing.
     */
    protected function evacuateToRandomWing(Character $victim): string
    {
        $currentWing = $victim->current_location ?? 'Paviljoen 1 (Gesloten)';
        $allWings = array_column(TravelController::LOCATIONS, 'name');
        $otherWings = array_values(array_filter($allWings, fn (string $w) => $w !== $currentWing));

        $newWing = ! empty($otherWings)
            ? $otherWings[array_rand($otherWings)]
            : 'Paviljoen 2 (Half-open)';

        $victim->current_location = $newWing;
        $victim->save();

        return $newWing;
    }

    protected function calculateTravelCost(Character $character): int
    {
        $base = 2500 + ($character->level * 150);

        if ($character->isVip()) {
            $base = (int) round($base * 0.5);
        }

        return max(500, $base);
    }
}
