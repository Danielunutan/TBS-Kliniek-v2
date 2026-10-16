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
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        $topRankings = Character::orderByDesc('respect')->take(10)->get();

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
     * Attempt prison break for self.
     */
    public function escape(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;

        $result = $this->prisonService->attemptEscape($character, $this->energyService);

        if ($result['success']) {
            $character->prison_escapes++;
            $character->breakout_slay = ($character->breakout_slay ?? 0) + 0.1;
            $character->save();

            app(DailyBattleService::class)->record($character, 'breakouts', 1);

            return redirect()->route('dashboard')->with('success', $result['message']);
        }

        return back()->with('error', $result['message']);
    }

    /**
     * Buy self out of prison using Borgtocht (Cash/Bank) or Stars.
     */
    public function buyout(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;

        if (! $character->isImprisoned()) {
            return back()->with('info', 'Je zit momenteel niet in de isoleercel!');
        }

        // Vrijkopen uit de gevangenis kost altijd Sterren ⭐ (geen cash geld)
        $cost = 5;
        if (($character->stars ?? 0) < $cost) {
            return back()->with('error', "Je hebt minimaal {$cost} Sterren ⭐ nodig om jezelf direct vrij te kopen (je hebt er momenteel {$character->stars}). Binnenkort kun je ook sponsor-ads bekijken om gratis vrij te komen!");
        }

        $character->stars -= $cost;
        $character->is_in_prison = false;
        $character->prison_until = null;
        $character->prison_reason = null;
        $character->save();

        return back()->with('success', "🔓 Vrijgekocht voor {$cost} Sterren ⭐! De celdeur is ontgrendeld en je bent weer op vrije voeten!");
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

        $energyCost = 15;
        if (! $this->energyService->consume($character, $energyCost)) {
            return back()->with('error', "Je hebt minimaal {$energyCost} energie nodig voor een uitbraakpoging!");
        }

        $character->breakout_attempts = ($character->breakout_attempts ?? 0) + 1;
        $character->save();

        $targetInmate = is_numeric($id) ? Character::find($id) : null;
        $targetName = $targetInmate ? $targetInmate->name : 'deze patiënt';

        if (is_numeric($id) && (! $targetInmate || ! $targetInmate->isImprisoned())) {
            return back()->with('info', 'Deze patiënt is inmiddels al vrij of niet meer in de isoleercel!');
        }

        if ($targetInmate && $targetInmate->id === $character->id) {
            return back()->with('error', 'Je kunt jezelf niet op deze manier uitbreken, gebruik de ontsnappingsknop!');
        }

        // Breakout chance based on effective stats (including gear)
        $chanceBonus = (int) floor(($character->getEffectiveIntelligence() + $character->getEffectiveStrength()) / 20);
        $successRate = min(35, max(8, 8 + $chanceBonus));

        $roll = rand(1, 100);

        if ($roll <= $successRate) {
            $character->prison_escapes = ($character->prison_escapes ?? 0) + 1;
            $character->breakout_slay = ($character->breakout_slay ?? 0) + 0.1;
            $character->respect += 2;
            $this->levelService->addXp($character, 140);

            // 25% chance of +1 strength from forcing security locks/bars
            if (rand(1, 100) <= 25) {
                $character->strength = ($character->strength ?? 10) + 1;
            }
            $character->save();

            app(DailyBattleService::class)->record($character, 'breakouts', 1);

            // Release real inmate
            if ($targetInmate && $targetInmate->isImprisoned()) {
                $targetInmate->is_in_prison = false;
                $targetInmate->prison_until = null;
                $targetInmate->prison_reason = null;
                $targetInmate->save();

                GameNotification::create([
                    'character_id' => $targetInmate->id,
                    'type' => 'prison',
                    'icon' => 'fa-door-open',
                    'title' => 'Uitgebroken door een medepatiënt!',
                    'message' => "{$character->name} heeft je zojuist bevrijd uit de isoleercel!",
                ]);

                GameMessage::create([
                    'recipient_id' => $targetInmate->id,
                    'sender_id' => $character->id,
                    'sender_name' => $character->name,
                    'subject' => '🔓 VRIJ! Je bent bevrijd uit de gevangenis!',
                    'content' => "Patiënt {$targetInmate->name},\n\n{$character->name} heeft zojuist met gevaar voor eigen leven de isoleerceldeur geforceerd en jou bevrijd!",
                    'type' => 'jail',
                ]);
            }

            return back()->with('success', "🎉 RAAK! De celdeur zwaait open! {$targetName} succesvol uitgebroken! +2 Respect 🔥 en +140 XP verdiend!");
        }

        // FOUTE UITBRAAK: Kom zelf direct in de isoleercel!
        $jailSeconds = random_int(120, 180);
        $this->prisonService->jailSeconds($character, $jailSeconds, "Betrapt bij uitbraakpoging van {$targetName} in de isoleercel.");

        return redirect()->route('prison')->with('error', "Poging mislukt! Je zit {$jailSeconds} sec vast in isolatie (betrapt door cipiers). Betaal borg (€250) of gebruik 2 Sterren om direct vrij te komen!");
    }
}
