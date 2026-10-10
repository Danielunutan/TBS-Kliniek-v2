<?php

namespace App\Http\Controllers;

use App\Services\EconomyService;
use App\Services\LevelService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SlotsController extends Controller
{
    public function __construct(
        protected EconomyService $economy,
        protected LevelService $level,
    ) {}

    public function index(): View
    {
        $character = Auth::user()->character;

        return view('game.slots.index', compact('character'));
    }

    public function spin(Request $request): RedirectResponse
    {
        $character = Auth::user()->character;

        if ($character->isImprisoned()) {
            return back()->with('error', 'Vanuit de isoleercel kun je niet gokken!');
        }

        $validated = $request->validate([
            'bet' => ['required', 'integer', 'min:50', 'max:50000'],
        ]);

        $bet = (int) $validated['bet'];
        if ($character->cash < $bet) {
            return back()->with('error', "Je hebt onvoldoende contant geld. Inzet: {$bet} KC.");
        }

        $symbols = ['🍋', '🍒', '🍀', '⭐', '💎', '🔔', '7️⃣'];
        $weights = [30,   25,   18,   12,    8,    5,    2]; // total 100

        $spin = function () use ($symbols, $weights): string {
            $rand = rand(1, 100);
            $cumulative = 0;
            foreach ($symbols as $i => $symbol) {
                $cumulative += $weights[$i];
                if ($rand <= $cumulative) {
                    return $symbol;
                }
            }

            return $symbols[0];
        };

        $r1 = $spin();
        $r2 = $spin();
        $r3 = $spin();

        // Jackpot - all 3 match
        if ($r1 === $r2 && $r2 === $r3) {
            $jackpotMultipliers = ['7️⃣' => 50, '💎' => 25, '🔔' => 15, '⭐' => 10, '🍀' => 6, '🍒' => 4, '🍋' => 3];
            $multiplier = $jackpotMultipliers[$r1] ?? 5;
            $payout = $bet * $multiplier;
            $this->economy->addCash($character, $payout);
            $this->level->addXp($character, 40);
            $character->respect += 3;
            $character->save();

            return back()->with('success', "🎰 JACKPOT! [{$r1} {$r2} {$r3}] — Alle drie gelijk! Je wint {$payout} KC ({$multiplier}x)! +40 XP, +3 Respect!");
        }

        // Two match
        if ($r1 === $r2 || $r2 === $r3 || $r1 === $r3) {
            $payout = (int) ($bet * 1.5);
            $this->economy->addCash($character, $payout - $bet); // net gain only
            $this->level->addXp($character, 10);

            return back()->with('success', "Twee gelijk! [{$r1} {$r2} {$r3}] — Kleine win! Je ontvangt {$payout} KC terug. +10 XP.");
        }

        // No match
        $character->cash -= $bet;
        $character->save();

        return back()->with('error', "Helaas! [{$r1} {$r2} {$r3}] — Geen combinatie. Je verliest {$bet} KC. Probeer opnieuw!");
    }
}
