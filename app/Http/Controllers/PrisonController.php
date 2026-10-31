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
    public function __construct(
        protected PrisonService $prisonService,
        protected EnergyService $energyService,
        protected LevelService $levelService,
    ) {}

    /**
     * Show prison status & list of jailed inmates (Matching Screenshot 1).
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
            ]);

        $character->refresh();
        $isImprisoned = $character->isImprisoned();
        $remainingMinutes = $character->remainingPrisonMinutes();
        $remainingSeconds = $character->remainingPrisonSeconds();

        // Real prisoners currently locked up with active time
        $realPrisoners = Character::where('is_in_prison', true)
            ->whereNotNull('prison_until')
            ->where('prison_until', '>', now())
            ->where('id', '!=', $character->id)
            ->orderBy('prison_until')
            ->get();

        $jailList = collect();

        foreach ($realPrisoners as $rp) {
            $remSecs = max(0, (int) now()->diffInSeconds($rp->prison_until, false));
            if ($remSecs <= 0) {
                $rp->update([
                    'is_in_prison' => false,
                    'prison_until' => null,
                    'prison_reason' => null,
                ]);

                continue;
            }

            $chance = min(35, max(5, 5 + (int) floor(($character->getEffectiveIntelligence() + $character->getEffectiveStrength()) / 20)));
            $jailList->push([
                'id' => $rp->id,
                'name' => $rp->name,
                'reason' => $rp->prison_reason ?: 'Wangedrag in de kliniek',
                'time' => gmdate('i:s', $remSecs),
                'remaining_seconds' => $remSecs,
                'chance' => $chance,
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

        return view('game.prison', compact(
            'character',
            'isImprisoned',
            'remainingMinutes',
            'remainingSeconds',
            'jailList',
            'stats',
            'topBreakers',
            'topRankings'
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
            $remaining = $character->isImprisoned() ? max(0, (int) now()->diffInSeconds($character->prison_until, false)) : 0;

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
            return back()->with('error', "Je hebt minimaal {$cost} Sterren ⭐ nodig om jezelf vrij te kopen.");
        }

        return back()->with('success', 'Je hebt jezelf succesvol vrijgekocht met Sterren ⭐! Je bent weer op vrije voeten.');
    }

    /**
     * Attempt to break out another inmate (real players).
     */
    public function breakout(Request $request, string $id): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;

        if ($character->isImprisoned()) {
            return back()->with('error', 'Je zit zelf in de isoleercel!');
        }

        if ($character->isComatose()) {
            return back()->with('error', 'Je verkeert in Code Blauw (Hartstilstand)! Dien eerst een '.Character::REVIVE_SERUM_NAME.' toe.');
        }

        // Validate the target before energy is spent so an invalid attempt costs nothing.
        $targetInmate = is_numeric($id) ? Character::find($id) : null;
        $targetName = $targetInmate ? $targetInmate->name : 'deze patiënt';

        if (is_numeric($id) && (! $targetInmate || ! $targetInmate->isImprisoned())) {
            return back()->with('info', 'Deze patiënt is inmiddels al vrij of niet meer in de isoleercel!');
        }

        if ($targetInmate && $targetInmate->id === $character->id) {
            return back()->with('error', 'Je kunt jezelf niet op deze manier uitbreken, gebruik de ontsnappingsknop!');
        }

        $energyCost = 15;
        if (! $this->energyService->consume($character, $energyCost)) {
            return back()->with('error', "Je hebt minimaal {$energyCost} energie nodig voor een uitbraakpoging!");
        }

        $character->breakout_attempts = ($character->breakout_attempts ?? 0) + 1;
        $character->save();

        // Breakout chance based on effective stats (including gear)
        $chanceBonus = (int) floor(($character->getEffectiveIntelligence() + $character->getEffectiveStrength()) / 20);
        $successRate = min(35, max(8, 8 + $chanceBonus));

        $roll = rand(1, 100);

        if ($roll <= $successRate) {
            $character->prison_escapes = ($character->prison_escapes ?? 0) + 1;
            $character->breakout_slay = ($character->breakout_slay ?? 0) + 0.1;
            $character->addRespect(2);
            $this->levelService->addXp($character, 140);

            // 25% chance of +1 strength from forcing security locks/bars (capped at level maximum)
            if (rand(1, 100) <= 25) {
                $character->strength = min($character->getMaxStatForLevel(), ($character->strength ?? 10) + 1);
            }
            $character->save();

            app(DailyBattleService::class)->record($character, 'breakouts', 1);

            // Release real inmate under lock
            if ($targetInmate) {
                DB::transaction(function () use ($targetInmate, $character) {
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
                        'title' => 'Uitgebroken door een medepatiënt!',
                        'message' => "{$character->name} heeft je zojuist bevrijd uit de isoleercel!",
                    ]);

                    GameMessage::create([
                        'recipient_id' => $lockedInmate->id,
                        'sender_id' => $character->id,
                        'sender_name' => $character->name,
                        'subject' => '🔓 VRIJ! Je bent bevrijd uit de gevangenis!',
                        'content' => "Patiënt {$lockedInmate->name},\n\n{$character->name} heeft zojuist met gevaar voor eigen leven de isoleerceldeur geforceerd en jou bevrijd!",
                        'type' => 'jail',
                    ]);
                });
            }

            return back()->with('success', "🎉 RAAK! De celdeur zwaait open! {$targetName} succesvol uitgebroken! +2 Respect 🔥 en +140 XP verdiend!");
        }

        // FOUTE UITBRAAK: Kom zelf direct in de isoleercel!
        $jailSeconds = random_int(120, 180);
        $this->prisonService->jailSeconds($character, $jailSeconds, "Betrapt bij uitbraakpoging van {$targetName} in de isoleercel.");

        return redirect()->route('prison')->with('error', "Poging mislukt! Je zit {$jailSeconds} sec vast in isolatie (betrapt door cipiers). Gebruik Sterren ⭐ om direct vrij te komen!");
    }
}
