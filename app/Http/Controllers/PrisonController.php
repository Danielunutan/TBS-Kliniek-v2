<?php

namespace App\Http\Controllers;

use App\Models\Character;
use App\Models\GameMessage;
use App\Models\GameNotification;
use App\Models\User;
use App\Services\DailyBattleService;
use App\Services\EnergyService;
use App\Services\LevelService;
use App\Services\PrisonService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PrisonController extends Controller
{
    /**
     * Tactical breakout methods available to free players when rescuing an inmate.
     *
     * @var array<string, array{
     *     name: string,
     *     subtitle: string,
     *     description: string,
     *     icon: string,
     *     chance_mod: int,
     *     respect: int,
     *     xp: int,
     *     slay_bonus: float,
     *     stat_type: string,
     *     stat_chance: int,
     *     fail_min_sec: int,
     *     fail_max_sec: int
     * }>
     */
    public const BREAKOUT_METHODS = [
        'stil' => [
            'name' => 'Stille Sluipuitbraak',
            'subtitle' => 'Lockpick & Schaduwcorridor',
            'description' => 'Manipuleer het elektronische celslot geruisloos met een geprepareerde draadpen (+6% slaagkans, kortere straf bij betrapping).',
            'icon' => 'fa-user-ninja',
            'chance_mod' => 6,
            'respect' => 2,
            'xp' => 140,
            'slay_bonus' => 0.1,
            'stat_type' => 'intelligence',
            'stat_chance' => 25,
            'fail_min_sec' => 90,
            'fail_max_sec' => 130,
        ],
        'geweld' => [
            'name' => 'Breekijzer & Forceerwerk',
            'subtitle' => 'Brute Kracht & Scharnierbreuk',
            'description' => 'Wrik de gepantserde celdeur met bruut geweld uit het kozijn (-3% slaagkans, maar +4 Respect, +210 XP & 40% kans op +1 Kracht!).',
            'icon' => 'fa-hammer',
            'chance_mod' => -3,
            'respect' => 4,
            'xp' => 210,
            'slay_bonus' => 0.2,
            'stat_type' => 'strength',
            'stat_chance' => 40,
            'fail_min_sec' => 150,
            'fail_max_sec' => 210,
        ],
    ];

    /**
     * Sentence reduction actions available to imprisoned players.
     *
     * @var array<string, array{
     *     name: string,
     *     subtitle: string,
     *     description: string,
     *     icon: string,
     *     cost_label: string,
     *     energy_cost: int,
     *     cash_cost: int,
     *     reduce_seconds: int
     * }>
     */
    public const REDUCE_ACTIONS = [
        'wasserij' => [
            'name' => 'Stoomwasserij-Dienst',
            'subtitle' => '100% Veilig • -45 sec Straftijd',
            'description' => 'Draai een zware ploegendienst tussen de hete stoompersen voor gegarandeerde strafverkorting en +15 XP.',
            'icon' => 'fa-shirt',
            'cost_label' => '10 Energie',
            'energy_cost' => 10,
            'cash_cost' => 0,
            'reduce_seconds' => 45,
        ],
        'omkopen' => [
            'name' => 'Nachtcipier Omkopen',
            'subtitle' => '75% Kans op -90 sec • 25% Kans op +30 sec',
            'description' => 'Schuif zwijggeld onder het celrooster door zodat de dienstdoende cipier je digitale klok terugdraait.',
            'icon' => 'fa-hand-holding-dollar',
            'cost_label' => '€ 2.500',
            'energy_cost' => 0,
            'cash_cost' => 2500,
            'reduce_seconds' => 90,
        ],
    ];

    public const REDUCE_COOLDOWN_SECONDS = 90;

    public function __construct(
        protected PrisonService $prisonService,
        protected EnergyService $energyService,
        protected LevelService $levelService,
    ) {}

    /**
     * Show prison status, sentence reduction dashboard, and interactive cellblock cards.
     */
    public function index(): View
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;
        $this->energyService->refresh($character);

        // Auto-release any characters whose prison time has expired
        Character::where('is_in_prison', true)
            ->where(function ($q) {
                $q->whereNull('prison_until')
                    ->orWhere('prison_until', '<=', now());
            })
            ->update([
                'is_in_prison' => false,
                'prison_until' => null,
                'prison_reason' => null,
                'prison_escape_attempted' => false,
                'ad_prison_progress' => 0,
            ]);

        $character->refresh();
        $isImprisoned = $character->isImprisoned();
        $remainingMinutes = $character->remainingPrisonMinutes();
        $remainingSeconds = $character->remainingPrisonSeconds();

        $perks = is_array($character->perks) ? $character->perks : [];
        $cooldownUntilTs = (int) ($perks['prison_action_cooldown_until'] ?? 0);
        $prisonActionCooldownRemaining = max(0, $cooldownUntilTs - now()->timestamp);

        // Real prisoners currently locked up with active time
        $realPrisoners = Character::where('is_in_prison', true)
            ->whereNotNull('prison_until')
            ->where('prison_until', '>', now())
            ->where('id', '!=', $character->id)
            ->orderBy('prison_until')
            ->get();

        $jailList = collect();
        $baseBonus = (int) floor(($character->getEffectiveIntelligence() + $character->getEffectiveStrength()) / 20);

        foreach ($realPrisoners as $rp) {
            $remSecs = max(0, (int) ceil(now()->diffInSeconds($rp->prison_until, false)));
            if ($remSecs <= 0) {
                $rp->update([
                    'is_in_prison' => false,
                    'prison_until' => null,
                    'prison_reason' => null,
                ]);

                continue;
            }

            $baseChance = min(35, max(8, 8 + $baseBonus));
            $chanceStil = min(45, max(10, $baseChance + self::BREAKOUT_METHODS['stil']['chance_mod']));
            $chanceGeweld = min(35, max(5, $baseChance + self::BREAKOUT_METHODS['geweld']['chance_mod']));

            $jailList->push([
                'id' => $rp->id,
                'name' => $rp->name,
                'reason' => $rp->prison_reason ?: 'Overtreding van het huisreglement',
                'time' => gmdate('i:s', $remSecs),
                'remaining_seconds' => $remSecs,
                'chance' => $chanceStil,
                'chance_stil' => $chanceStil,
                'chance_geweld' => $chanceGeweld,
                'avatar' => $rp->avatar_url,
                'model' => $rp,
            ]);
        }

        // Breakout Stats for character
        $attempts = $character->breakout_attempts ?? 0;
        $escapes = $character->prison_escapes ?? 0;
        $successRate = $attempts > 0 ? round(($escapes / $attempts) * 100, 1).'%' : '0%';

        $stats = [
            'escapes_today' => $escapes,
            'attempts_today' => $attempts,
            'breakout_xp' => $successRate,
            'slay' => number_format((float) ($character->breakout_slay ?? 0), 1),
            'best_day_date' => now()->format('d-m-Y'),
            'best_day_count' => $escapes,
        ];

        // Top real breakers leaderboard
        $topBreakers = Character::where('prison_escapes', '>', 0)
            ->orderByDesc('prison_escapes')
            ->take(5)
            ->get();

        // Top 10 for bottom podium ranglijst
        $topRankings = Character::getTopRankings(10);

        $breakoutMethods = self::BREAKOUT_METHODS;
        $reduceActions = self::REDUCE_ACTIONS;

        return view('game.prison', compact(
            'character',
            'isImprisoned',
            'remainingMinutes',
            'remainingSeconds',
            'jailList',
            'stats',
            'topBreakers',
            'topRankings',
            'breakoutMethods',
            'reduceActions',
            'prisonActionCooldownRemaining'
        ));
    }

    /**
     * Attempt to escape from the isolation cell (isoleercel).
     */
    public function escape(Request $request): RedirectResponse|JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;

        if (! $character->isImprisoned()) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Je zit momenteel niet in de isoleercel!',
                    'redirect' => route('prison'),
                ]);
            }

            return redirect()->route('prison')->with('info', 'Je zit momenteel niet in de isoleercel!');
        }

        if ($character->isComatose()) {
            $msg = 'Je verkeert in Code Blauw (Hartstilstand)! Dien eerst een '.Character::REVIVE_SERUM_NAME.' toe.';
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $msg,
                ], 422);
            }

            return back()->with('error', $msg);
        }

        $result = $this->prisonService->attemptEscape($character, $this->energyService);

        if ($result['success']) {
            $character->refresh();
            $character->breakout_slay = ($character->breakout_slay ?? 0) + 0.1;
            $character->save();

            $this->levelService->addXp($character, 35);

            app(DailyBattleService::class)->record($character, 'breakouts', 1);

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $result['message'],
                    'redirect' => route('dashboard'),
                ]);
            }

            return redirect()->route('dashboard')->with('success', $result['message']);
        }

        if ($request->expectsJson() || $request->ajax()) {
            $character->refresh();
            $remaining = $character->isImprisoned() ? max(0, (int) ceil(now()->diffInSeconds($character->prison_until, false))) : 0;

            return response()->json([
                'success' => false,
                'message' => $result['message'],
                'remaining_seconds' => $remaining,
                'escape_attempted' => true,
            ]);
        }

        return redirect()->route('prison')->with('error', $result['message']);
    }

    /**
     * Buy self out of prison using stars.
     */
    public function buyout(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;

        if (! $character->isImprisoned()) {
            return back()->with('info', 'Je zit momenteel niet in de isoleercel!');
        }

        $cost = 25;

        $bought = DB::transaction(function () use ($character, $cost): bool {
            /** @var Character $locked */
            $locked = Character::where('id', $character->id)->lockForUpdate()->first();

            if (! $locked->isImprisoned() || ($locked->stars ?? 0) < $cost) {
                return false;
            }

            $locked->stars -= $cost;
            $locked->is_in_prison = false;
            $locked->prison_until = null;
            $locked->prison_reason = null;
            $locked->prison_escape_attempted = false;
            $locked->ad_prison_progress = 0;
            $locked->save();

            return true;
        });

        if (! $bought) {
            return back()->with('error', "Onvoldoende Sterren ⭐: directe vrijkoop uit het celblok vereist {$cost} Sterren.");
        }

        return back()->with('success', 'Vrijkoopakte getekend met 25 Sterren ⭐! De elektronische sluis opent en je staat weer op de vrije afdeling.');
    }

    /**
     * Perform an in-cell action (laundry shift or guard bribe) to shorten the active sentence.
     */
    public function reduceSentence(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'action' => ['nullable', 'string', 'in:wasserij,omkopen'],
        ]);

        $actionKey = $validated['action'] ?? 'wasserij';
        $actionMeta = self::REDUCE_ACTIONS[$actionKey] ?? self::REDUCE_ACTIONS['wasserij'];

        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;
        $this->energyService->refresh($character);

        if (! $character->isImprisoned()) {
            return redirect()->route('prison')->with('info', 'Je zit momenteel niet in de isoleercel!');
        }

        if ($character->isComatose()) {
            return back()->with('error', 'Je verkeert in Code Blauw (Hartstilstand)! Dien eerst een '.Character::REVIVE_SERUM_NAME.' toe.');
        }

        $perks = is_array($character->perks) ? $character->perks : [];
        $cooldownUntilTs = (int) ($perks['prison_action_cooldown_until'] ?? 0);
        $cooldownRemaining = max(0, $cooldownUntilTs - now()->timestamp);

        if ($cooldownRemaining > 0) {
            return back()->with('error', "De gangwacht houdt je cel momenteel extra in de gaten. Wacht nog {$cooldownRemaining} seconden voor je volgende actie.");
        }

        if ($actionKey === 'wasserij') {
            $energyCost = $actionMeta['energy_cost'];
            if (! $this->energyService->consume($character, $energyCost)) {
                return back()->with('error', "Onvoldoende uithoudingsvermogen: een dienst in de stoomwasserij kost {$energyCost} energie.");
            }

            $reduceSeconds = $actionMeta['reduce_seconds'];
            $released = false;
            $processed = false;

            DB::transaction(function () use ($character, $reduceSeconds, &$released, &$processed): void {
                /** @var Character|null $locked */
                $locked = Character::where('id', $character->id)->lockForUpdate()->first();
                if (! $locked || ! $locked->isImprisoned()) {
                    return;
                }

                $processed = true;
                $lockedPerks = is_array($locked->perks) ? $locked->perks : [];

                $currentUntil = $locked->prison_until ? $locked->prison_until->copy() : now();
                $newUntil = $currentUntil->subSeconds($reduceSeconds);

                if ($newUntil->lte(now())) {
                    $locked->is_in_prison = false;
                    $locked->prison_until = null;
                    $locked->prison_reason = null;
                    $locked->prison_escape_attempted = false;
                    $locked->ad_prison_progress = 0;
                    unset($lockedPerks['prison_action_cooldown_until']);
                    $released = true;
                } else {
                    $locked->prison_until = $newUntil;
                    $lockedPerks['prison_action_cooldown_until'] = now()->addSeconds(self::REDUCE_COOLDOWN_SECONDS)->timestamp;
                }

                $locked->perks = $lockedPerks;
                $locked->save();
            });

            if (! $processed) {
                return redirect()->route('prison')->with('info', 'Je zit momenteel niet in de isoleercel!');
            }

            $this->levelService->addXp($character, 15);

            if ($released) {
                return redirect()->route('prison')->with('success', "Stoomwasserij-dienst voltooid (-{$reduceSeconds} sec & +15 XP)! Je resterende straftijd is volledig weggewerkt en je bent direct vrijgelaten!");
            }

            return redirect()->route('prison')->with('success', "Stoomwasserij-dienst afgerond! Wegens goed gedrag op de werkvloer is je celstraf met {$reduceSeconds} seconden ingekort (+15 XP).");
        }

        // Action: omkopen (Guard Bribe)
        $bribeCost = $actionMeta['cash_cost'];
        $outcome = DB::transaction(function () use ($character, $bribeCost, $actionMeta): array {
            /** @var Character|null $locked */
            $locked = Character::where('id', $character->id)->lockForUpdate()->first();
            if (! $locked || ! $locked->isImprisoned()) {
                return ['status' => 'not_imprisoned'];
            }

            $totalFunds = (int) $locked->cash + (int) $locked->bank;
            if ($totalFunds < $bribeCost) {
                return ['status' => 'insufficient_funds'];
            }

            if ((int) $locked->cash >= $bribeCost) {
                $locked->cash -= $bribeCost;
            } else {
                $remainder = $bribeCost - (int) $locked->cash;
                $locked->cash = 0;
                $locked->bank = max(0, (int) $locked->bank - $remainder);
            }

            $lockedPerks = is_array($locked->perks) ? $locked->perks : [];
            $lockedPerks['prison_action_cooldown_until'] = now()->addSeconds(self::REDUCE_COOLDOWN_SECONDS)->timestamp;

            $roll = random_int(1, 100);
            $currentUntil = $locked->prison_until ? $locked->prison_until->copy() : now();

            if ($roll <= 75) {
                $reduceSecs = $actionMeta['reduce_seconds'];
                $newUntil = $currentUntil->subSeconds($reduceSecs);
                $released = false;

                if ($newUntil->lte(now())) {
                    $locked->is_in_prison = false;
                    $locked->prison_until = null;
                    $locked->prison_reason = null;
                    $locked->prison_escape_attempted = false;
                    $locked->ad_prison_progress = 0;
                    unset($lockedPerks['prison_action_cooldown_until']);
                    $released = true;
                } else {
                    $locked->prison_until = $newUntil;
                }

                $locked->perks = $lockedPerks;
                $locked->addRespect(1);
                $locked->save();

                return [
                    'status' => 'bribe_success',
                    'released' => $released,
                    'seconds' => $reduceSecs,
                ];
            }

            $locked->perks = $lockedPerks;

            $penaltySecs = 30;
            $locked->prison_until = $currentUntil->addSeconds($penaltySecs);
            $locked->save();

            return [
                'status' => 'bribe_backfire',
                'seconds' => $penaltySecs,
            ];
        });

        if ($outcome['status'] === 'not_imprisoned') {
            return redirect()->route('prison')->with('info', 'Je zit momenteel niet in de isoleercel!');
        }

        if ($outcome['status'] === 'insufficient_funds') {
            return back()->with('error', 'Onvoldoende saldo: de nachtcipier eist € '.number_format($bribeCost, 0, ',', '.').' aan zwijggeld.');
        }

        if ($outcome['status'] === 'bribe_success') {
            if (! empty($outcome['released'])) {
                return redirect()->route('prison')->with('success', "Zwijggeld geaccepteerd (-{$outcome['seconds']} sec & +1 Respect)! De nachtcipier heeft je celdeur van het slot gehaald: je bent vrij!");
            }

            return redirect()->route('prison')->with('success', "Zwijggeld geaccepteerd! De nachtcipier heeft de digitale celklok met {$outcome['seconds']} seconden teruggedraaid (+1 Respect).");
        }

        return redirect()->route('prison')->with('error', "Verraad! De afdelingshoofd-cipier zag het biljet onder het luik doorglijden: € 2.500 geconfisqueerd en +{$outcome['seconds']} seconden extra straftijd.");
    }

    /**
     * Attempt to break out another inmate using a selected tactical approach ('stil' or 'geweld').
     */
    public function breakout(Request $request, string $prisoner): RedirectResponse
    {
        $id = $prisoner;

        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;

        if ($character->isImprisoned()) {
            return back()->with('error', 'Je zit zelf achter de gepantserde deur en kunt geen andere cel openbreken!');
        }

        if ($character->isComatose()) {
            return back()->with('error', 'Je verkeert in Code Blauw (Hartstilstand)! Dien eerst een '.Character::REVIVE_SERUM_NAME.' toe.');
        }

        $methodKey = (string) $request->input('method', 'stil');
        if (! array_key_exists($methodKey, self::BREAKOUT_METHODS)) {
            $methodKey = 'stil';
        }
        $methodMeta = self::BREAKOUT_METHODS[$methodKey];

        // Validate the target before energy is spent so an invalid attempt costs nothing.
        $isSimulatedTarget = in_array($id, ['sim_1', 'sim_2', 'sim_3'], true);
        if (! $isSimulatedTarget && ! is_numeric($id)) {
            return back()->with('error', 'Ongeldig celdossier geselecteerd voor deze bevrijdingsactie.');
        }

        $targetInmate = is_numeric($id) ? Character::find($id) : null;
        $targetName = $targetInmate ? $targetInmate->name : 'deze patiënt';

        if (is_numeric($id) && (! $targetInmate || ! $targetInmate->isImprisoned())) {
            return back()->with('info', 'Deze patiënt is inmiddels al vrijgelaten uit het celblok!');
        }

        if ($targetInmate && $targetInmate->id === $character->id) {
            return back()->with('error', 'Je kunt niet je eigen cel van buitenaf openbreken; gebruik het ontsnappingspaneel!');
        }

        $energyCost = 15;
        if (! $this->energyService->consume($character, $energyCost)) {
            return back()->with('error', "Onvoldoende energie: voor aanpak '{$methodMeta['name']}' is minimaal {$energyCost} energie vereist!");
        }

        $character->breakout_attempts = ($character->breakout_attempts ?? 0) + 1;
        $character->save();

        // Breakout chance based on effective stats + chosen tactical method modifier
        $chanceBonus = (int) floor(($character->getEffectiveIntelligence() + $character->getEffectiveStrength()) / 20);
        $baseRate = min(35, max(8, 8 + $chanceBonus));
        $successRate = $methodKey === 'stil'
            ? min(45, max(10, $baseRate + $methodMeta['chance_mod']))
            : min(35, max(5, $baseRate + $methodMeta['chance_mod']));

        $roll = random_int(1, 100);

        if ($roll <= $successRate) {
            $character->prison_escapes = ($character->prison_escapes ?? 0) + 1;
            $character->breakout_slay = ($character->breakout_slay ?? 0) + $methodMeta['slay_bonus'];
            $character->addRespect($methodMeta['respect']);
            $this->levelService->addXp($character, $methodMeta['xp']);

            // Stat bonus chance depending on chosen method (Intelligence for stealth, Strength for force)
            $statBonusMsg = '';
            if (random_int(1, 100) <= $methodMeta['stat_chance']) {
                $statField = $methodMeta['stat_type'];
                $currentStatVal = (int) ($character->{$statField} ?? 10);
                if ($currentStatVal < $character->getMaxStatForLevel()) {
                    $character->{$statField} = min($character->getMaxStatForLevel(), $currentStatVal + 1);
                    $statBonusMsg = $statField === 'intelligence' ? ' (+1 Intelligentie 🧠)' : ' (+1 Kracht 💪)';
                }
            }
            $character->save();

            app(DailyBattleService::class)->record($character, 'breakouts', 1);

            // Release real inmate under lock
            if ($targetInmate) {
                DB::transaction(function () use ($targetInmate, $character, $methodMeta) {
                    /** @var Character|null $lockedInmate */
                    $lockedInmate = Character::where('id', $targetInmate->id)->lockForUpdate()->first();
                    if (! $lockedInmate || ! $lockedInmate->isImprisoned()) {
                        return;
                    }

                    $lockedInmate->is_in_prison = false;
                    $lockedInmate->prison_until = null;
                    $lockedInmate->prison_reason = null;
                    $lockedInmate->prison_escape_attempted = false;
                    $lockedInmate->ad_prison_progress = 0;
                    $lockedInmate->save();

                    GameNotification::create([
                        'character_id' => $lockedInmate->id,
                        'type' => 'prison',
                        'icon' => 'fa-door-open',
                        'title' => 'Bevrijd uit het Celblok!',
                        'message' => "{$character->name} heeft je via '{$methodMeta['name']}' uit de isoleercel bevrijd!",
                    ]);

                    GameMessage::create([
                        'recipient_id' => $lockedInmate->id,
                        'sender_id' => $character->id,
                        'sender_name' => $character->name,
                        'subject' => '🔓 CELDOORBRAAK: Je bent op vrije voeten!',
                        'content' => "Patiënt {$lockedInmate->name},\n\n{$character->name} heeft zojuist via de tactiek '{$methodMeta['name']}' je celvergrendeling gekraakt en je uit de isolatievleugel gehaald!",
                        'type' => 'jail',
                    ]);
                });
            }

            return back()->with('success', "🔓 Operatie '{$methodMeta['name']}' geslaagd! De celvergrendeling bezwijkt en {$targetName} staat op vrije voeten! Beloning: +{$methodMeta['respect']} Respect 🔥 en +{$methodMeta['xp']} XP{$statBonusMsg}.");
        }

        // FOUTE UITBRAAK: Kom zelf direct in de isoleercel!
        $jailSeconds = random_int($methodMeta['fail_min_sec'], $methodMeta['fail_max_sec']);
        $this->prisonService->jailSeconds($character, $jailSeconds, "Onderschept tijdens '{$methodMeta['name']}' bij cel van {$targetName}.");
        $actualJailSeconds = max(1, $character->fresh()->remainingPrisonSeconds());

        return redirect()->route('prison')->with('error', "Operatie '{$methodMeta['name']}' onderschept! Je zit {$actualJailSeconds} sec vast in isolatie nadat het interventieteam toesloeg. Verkort je straf via de stoomwasserij of gebruik Sterren ⭐!");
    }
}
