<?php

namespace App\Http\Controllers;

use App\Services\EconomyService;
use App\Services\LevelService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class HighLowController extends Controller
{
    public function __construct(
        protected EconomyService $economy,
        protected LevelService $level,
    ) {}

    public function index(): View
    {
        $character = Auth::user()->character;
        $currentCard = session('highlow_card', rand(1, 13));
        session(['highlow_card' => $currentCard]);

        return view('game.highlow.index', compact('character', 'currentCard'));
    }

    public function play(Request $request): RedirectResponse
    {
        $character = Auth::user()->character;

        if ($character->isImprisoned()) {
            return back()->with('error', 'Vanuit de isoleercel kun je niet gokken!');
        }

        $validated = $request->validate([
            'choice' => ['required', 'in:hoger,lager'],
            'bet' => ['required', 'integer', 'min:50', 'max:50000'],
        ]);

        $bet = (int) $validated['bet'];
        if ($character->cash < $bet) {
            return back()->with('error', 'Je hebt onvoldoende contant geld. Inzet: €'.number_format($bet, 0, ',', '.').'.');
        }

        $currentCard = (int) session('highlow_card', rand(1, 13));
        $nextCard = rand(1, 13);
        session(['highlow_card' => $nextCard]);

        $cardLabels = [1 => 'A', 2 => '2', 3 => '3', 4 => '4', 5 => '5', 6 => '6', 7 => '7',
            8 => '8', 9 => '9', 10 => '10', 11 => 'J', 12 => 'Q', 13 => 'K'];

        $won = ($validated['choice'] === 'hoger' && $nextCard > $currentCard)
            || ($validated['choice'] === 'lager' && $nextCard < $currentCard);

        $tie = $nextCard === $currentCard;

        if ($tie) {
            return back()->with('success', "Gelijkspel! Huidig: {$cardLabels[$currentCard]}, Nieuw: {$cardLabels[$nextCard]}. Inzet teruggestort.");
        }

        if ($won) {
            $this->economy->addCash($character, $bet);
            $this->level->addXp($character, 15);

            return back()->with('success', "Gewonnen! Huidig: {$cardLabels[$currentCard]} → Nieuw: {$cardLabels[$nextCard]}. +€".number_format($bet, 0, ',', '.').'! +15 XP.');
        }

        $character->cash -= $bet;
        $character->save();

        return back()->with('error', "Verloren! Huidig: {$cardLabels[$currentCard]} → Nieuw: {$cardLabels[$nextCard]}. -€".number_format($bet, 0, ',', '.').'.');
    }
}
