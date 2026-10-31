<?php

namespace App\Http\Controllers;

use App\Models\Character;
use App\Models\GameSetting;
use App\Services\CooldownService;
use App\Services\EconomyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CasinoController extends Controller
{
    public function __construct(
        protected EconomyService $economy,
        protected CooldownService $cooldownService,
    ) {}

    public function index()
    {
        $character = Auth::user()->character;

        return view('game.casino.index', compact('character'));
    }

    public function play(Request $request)
    {
        $character = Auth::user()->character;

        if ($character->isImprisoned()) {
            $msg = 'Vanuit de isoleercel kun je niet gokken!';

            return $request->expectsJson()
                ? response()->json(['success' => false, 'message' => $msg], 403)
                : redirect()->route('prison')->with('error', $msg);
        }

        if ($character->isComatose()) {
            $msg = 'Je verkeert in Code Blauw (Hartstilstand)! Dien eerst een '.
                Character::REVIVE_SERUM_NAME.' toe.';

            return $request->expectsJson()
                ? response()->json(['success' => false, 'message' => $msg], 422)
                : back()->with('error', $msg);
        }

        if ($this->cooldownService->isOnCooldown($character, 'casino_dice')) {
            $remaining = $this->cooldownService->getRemainingSeconds($character, 'casino_dice');
            $msg = "⏳ De croupier verzamelt de fiches! Wacht nog {$remaining}s.";

            return $request->expectsJson()
                ? response()->json(['success' => false, 'message' => $msg], 429)
                : back()->with('error', $msg);
        }

        $validated = $request->validate([
            'game' => 'required|in:coinflip,dice',
            'bet' => 'required|integer|min:10|max:10000000',
        ]);

        $bet = $validated['bet'];

        if ($character->cash < $bet) {
            $msg = 'Je hebt niet genoeg contant geld (€) om in te zetten!';

            return $request->expectsJson()
                ? response()->json(['success' => false, 'message' => $msg], 422)
                : back()->with('error', $msg);
        }

        $deducted = $this->economy->deductCash($character, $bet);
        if (! $deducted) {
            $msg = 'Je hebt niet genoeg contant geld (€) om in te zetten!';

            return $request->expectsJson()
                ? response()->json(['success' => false, 'message' => $msg], 422)
                : back()->with('error', $msg);
        }

        $this->cooldownService->setCooldown($character, 'casino_dice', 5);

        // Configured win rate check
        $winRate = (int) GameSetting::get('win_rate_casino', 45);
        $win = rand(1, 100) <= $winRate;
        $isDice = $validated['game'] === 'dice';

        if ($win) {
            $winnings = $bet * 2;
            $this->economy->addCash($character, $winnings);
            $character->refresh();

            $msg = $isDice
                ? 'Gefeliciteerd! De dobbelstenen rolden in jouw voordeel en je won €'.number_format($winnings, 0, ',', '.').'!'
                : 'Gefeliciteerd! De munt landde goed en je won €'.number_format($winnings, 0, ',', '.').'!';

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'win' => true,
                    'message' => $msg,
                    'winnings' => $winnings,
                    'cash' => $character->cash,
                ]);
            }

            return back()->with('success', $msg);
        } else {
            $character->refresh();
            $msg = $isDice
                ? 'Helaas, de worp viel verkeerd. Mo pakt je inzet van €'.number_format($bet, 0, ',', '.').'.'
                : 'Helaas, de munt viel verkeerd. Mo pakt je inzet van €'.number_format($bet, 0, ',', '.').'.';

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'win' => false,
                    'message' => $msg,
                    'winnings' => 0,
                    'cash' => $character->cash,
                ]);
            }

            return back()->with('error', $msg);
        }
    }
}
