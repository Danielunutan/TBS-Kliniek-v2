<?php

namespace App\Http\Controllers;

use App\Models\Character;
use App\Services\CooldownService;
use App\Services\EconomyService;
use App\Services\LevelService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SlotsController extends Controller
{
    public function __construct(
        protected EconomyService $economy,
        protected LevelService $level,
        protected CooldownService $cooldownService,
    ) {}

    public function index(): View
    {
        $character = Auth::user()->character;

        return view('game.slots.index', compact('character'));
    }

    public function spin(Request $request): mixed
    {
        $character = Auth::user()->character;

        if ($character->isImprisoned()) {
            $msg = 'Vanuit de isoleercel kun je niet gokken!';

            return $request->expectsJson()
                ? response()->json(['success' => false, 'message' => $msg], 403)
                : back()->with('error', $msg);
        }

        if ($character->isComatose()) {
            $msg = 'Je verkeert in Code Blauw (Hartstilstand)! Dien eerst een '.Character::REVIVE_SERUM_NAME.' toe.';

            return $request->expectsJson()
                ? response()->json(['success' => false, 'message' => $msg], 422)
                : back()->with('error', $msg);
        }

        if ($this->cooldownService->isOnCooldown($character, 'casino_slots')) {
            $remaining = $this->cooldownService->getRemainingSeconds($character, 'casino_slots');
            $msg = "⏳ De gokkast draait nog! Wacht nog {$remaining}s.";

            return $request->expectsJson()
                ? response()->json(['success' => false, 'message' => $msg], 429)
                : back()->with('error', $msg);
        }

        $validated = $request->validate([
            'bet' => ['required', 'integer', 'min:50', 'max:50000'],
        ]);

        $bet = (int) $validated['bet'];
        $msg = 'Je hebt onvoldoende contant geld. Inzet: €'.number_format($bet, 0, ',', '.').'.';

        if (! $this->economy->deductCash($character, $bet)) {
            return $request->expectsJson()
                ? response()->json(['success' => false, 'message' => $msg], 422)
                : back()->with('error', $msg);
        }

        $this->cooldownService->setCooldown($character, 'casino_slots', 4);

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
            $character->refresh();

            $msg = "🎰 JACKPOT! [{$r1} {$r2} {$r3}] — Alle drie gelijk! Je wint €".number_format($payout, 0, ',', '.')." ({$multiplier}x)! +40 XP, +3 Respect!";

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'reels' => [$r1, $r2, $r3],
                    'type' => 'jackpot',
                    'multiplier' => $multiplier,
                    'payout' => $payout,
                    'net_profit' => $payout - $bet,
                    'message' => $msg,
                    'cash' => $character->cash,
                    'xp' => $character->xp,
                    'respect' => $character->respect,
                ]);
            }

            return back()->with('success', $msg);
        }

        // Two match
        if ($r1 === $r2 || $r2 === $r3 || $r1 === $r3) {
            $payout = (int) ($bet * 1.5);
            $this->economy->addCash($character, $payout);
            $this->level->addXp($character, 10);
            $character->refresh();

            $msg = "Twee gelijk! [{$r1} {$r2} {$r3}] — Kleine win! Je ontvangt €".number_format($payout, 0, ',', '.').' terug. +10 XP.';

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'reels' => [$r1, $r2, $r3],
                    'type' => 'two_match',
                    'multiplier' => 1.5,
                    'payout' => $payout,
                    'net_profit' => $payout - $bet,
                    'message' => $msg,
                    'cash' => $character->cash,
                    'xp' => $character->xp,
                    'respect' => $character->respect,
                ]);
            }

            return back()->with('success', $msg);
        }

        // No match
        $character->refresh();

        $msg = "Helaas! [{$r1} {$r2} {$r3}] — Geen combinatie. Je verliest €".number_format($bet, 0, ',', '.').'. Probeer opnieuw!';

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'reels' => [$r1, $r2, $r3],
                'type' => 'loss',
                'multiplier' => 0,
                'payout' => 0,
                'net_profit' => -$bet,
                'message' => $msg,
                'cash' => $character->cash,
                'xp' => $character->xp,
                'respect' => $character->respect,
            ]);
        }

        return back()->with('error', $msg);
    }
}
