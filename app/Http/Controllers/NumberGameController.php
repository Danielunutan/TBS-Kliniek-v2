<?php

namespace App\Http\Controllers;

use App\Models\Character;
use App\Services\CooldownService;
use App\Services\EconomyService;
use App\Services\LevelService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class NumberGameController extends Controller
{
    public function __construct(
        protected EconomyService $economy,
        protected LevelService $level,
        protected CooldownService $cooldownService,
    ) {}

    public function index(): View
    {
        $character = Auth::user()->character;

        return view('game.numbergame.index', compact('character'));
    }

    public function play(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'number' => ['required', 'integer', 'between:1,20'],
            'bet' => ['required', 'integer', 'min:50', 'max:50000'],
        ]);

        return DB::transaction(function () use ($user, $validated) {
            /** @var Character $character */
            $character = Character::where('id', $user->character->id)->lockForUpdate()->first();

            if ($character->isImprisoned()) {
                return back()->with('error', 'Vanuit de isoleercel kun je niet gokken!');
            }

            if ($character->isComatose()) {
                return back()->with('error', 'Je verkeert in Code Blauw (Hartstilstand)! Dien eerst een '.
                    Character::REVIVE_SERUM_NAME.' toe.');
            }

            if ($this->cooldownService->isOnCooldown($character, 'casino_numbergame')) {
                $remaining = $this->cooldownService->getRemainingSeconds($character, 'casino_numbergame');

                return back()->with('error', "⏳ De nummers worden opnieuw geschud! Wacht nog {$remaining}s.");
            }

            $bet = (int) $validated['bet'];
            if (! $this->economy->deductCash($character, $bet)) {
                return back()->with('error', 'Je hebt onvoldoende contant geld. Inzet: €'.number_format($bet, 0, ',', '.').'.');
            }

            $drawn = rand(1, 20);
            $guess = (int) $validated['number'];

            $this->cooldownService->setCooldown($character, 'casino_numbergame', 8);

            if ($guess === $drawn) {
                // Jackpot: 15x multiplier
                $payout = $bet * 15;
                $this->economy->addCash($character, $payout);
                $this->level->addXp($character, 50);
                $character->addRespect(5);
                $character->save();

                return back()->with('success', "🎯 JACKPOT! Getrokken nummer: {$drawn}! Je raadde het precies! Je wint €".number_format($payout, 0, ',', '.').' (15x)! +50 XP, +5 Respect!');
            }

            if (abs($guess - $drawn) <= 1) {
                // Near miss (1 number off): 2x multiplier
                $payout = $bet * 2;
                $this->economy->addCash($character, $payout);
                $this->level->addXp($character, 20);

                return back()->with('success', "Bijna raak! Getrokken: {$drawn} (jij: {$guess}). Je wint €".number_format($payout, 0, ',', '.').' (2x bonus voor bijna!) +20 XP.');
            }

            return back()->with('error', "Mis! Getrokken nummer: {$drawn}. Jij koos: {$guess}. Je verliest €".number_format($bet, 0, ',', '.').'.');
        });
    }
}
