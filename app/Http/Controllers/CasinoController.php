<?php

namespace App\Http\Controllers;

use App\Services\EconomyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CasinoController extends Controller
{
    private EconomyService $economy;

    public function __construct(EconomyService $economy)
    {
        $this->economy = $economy;
    }

    public function index()
    {
        $character = Auth::user()->character;

        return view('game.casino.index', compact('character'));
    }

    public function play(Request $request)
    {
        $character = Auth::user()->character;

        $validated = $request->validate([
            'game' => 'required|in:coinflip,dice',
            'bet' => 'required|integer|min:10',
        ]);

        $bet = $validated['bet'];

        if ($character->cash < $bet) {
            $msg = 'Je hebt niet genoeg contant geld (€) om in te zetten!';

            return $request->expectsJson()
                ? response()->json(['success' => false, 'message' => $msg], 422)
                : back()->with('error', $msg);
        }

        if ($character->is_jailed) {
            $msg = 'Vanuit de isoleercel kun je niet gokken!';

            return $request->expectsJson()
                ? response()->json(['success' => false, 'message' => $msg], 403)
                : redirect()->route('prison')->with('error', $msg);
        }

        $this->economy->deductCash($character, $bet);

        // Simple Coin Flip (50/50)
        $win = rand(0, 1) === 1;

        if ($win) {
            $winnings = $bet * 2;
            $this->economy->addCash($character, $winnings);
            $character->refresh();

            $msg = 'Gefeliciteerd! De munt landde goed en je won €'.number_format($winnings, 0, ',', '.').'!';

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
            $msg = 'Helaas, de munt viel verkeerd. Mo pakt je inzet van €'.number_format($bet, 0, ',', '.').'.';

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
