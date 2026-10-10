<?php

namespace App\Http\Controllers;

use App\Services\EconomyService;
use App\Services\LevelService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RpsController extends Controller
{
    public function __construct(
        protected EconomyService $economy,
        protected LevelService $level,
    ) {}

    public function index(): View
    {
        $character = Auth::user()->character;

        return view('game.rps.index', compact('character'));
    }

    public function play(Request $request): RedirectResponse
    {
        $character = Auth::user()->character;

        if ($character->isImprisoned()) {
            return back()->with('error', 'Vanuit de isoleercel kun je niet gokken!');
        }

        $validated = $request->validate([
            'choice' => ['required', 'in:steen,papier,schaar'],
            'bet' => ['required', 'integer', 'min:50', 'max:50000'],
        ]);

        $bet = (int) $validated['bet'];
        if ($character->cash < $bet) {
            return back()->with('error', "Je hebt onvoldoende contant geld. Je inzet: {$bet} KC.");
        }

        $options = ['steen', 'papier', 'schaar'];
        $houseChoice = $options[array_rand($options)];
        $playerChoice = $validated['choice'];

        $wins = ['steen' => 'schaar', 'papier' => 'steen', 'schaar' => 'papier'];
        $labels = ['steen' => '🪨 Steen', 'papier' => '📄 Papier', 'schaar' => '✂️ Schaar'];

        if ($playerChoice === $houseChoice) {
            return back()->with('success', "Gelijkspel! Jij koos {$labels[$playerChoice]}, huis koos {$labels[$houseChoice]}. Inzet terug.");
        }

        if ($wins[$playerChoice] === $houseChoice) {
            $this->economy->addCash($character, $bet);
            $this->level->addXp($character, 15);

            return back()->with('success', "Gewonnen! Jij: {$labels[$playerChoice]} vs Huis: {$labels[$houseChoice]}. Je wint {$bet} KC! +15 XP.");
        }

        $character->cash -= $bet;
        $character->save();

        return back()->with('error', "Verloren! Jij: {$labels[$playerChoice]} vs Huis: {$labels[$houseChoice]}. Je verliest {$bet} KC.");
    }
}
