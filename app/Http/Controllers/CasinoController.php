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
            return back()->with('error', 'Je hebt niet genoeg KliniekCash om in te zetten!');
        }

        if ($character->is_jailed) {
            return redirect()->route('prison')->with('error', 'Vanuit de isoleercel kun je niet gokken!');
        }

        $this->economy->subtractCash($character, $bet, 'Inzet casino');

        // Simple Coin Flip (50/50)
        $win = rand(0, 1) === 1;

        if ($win) {
            $winnings = $bet * 2;
            $this->economy->addCash($character, $winnings, 'Winst casino');

            return back()->with('success', "Gefeliciteerd! Je won het potje en pakte {$winnings} KC!");
        } else {
            return back()->with('error', 'Helaas, je hebt verloren. Mo pakt je inzet.');
        }
    }
}
