<?php

namespace App\Http\Controllers;

use App\Services\CooldownService;
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
        protected CooldownService $cooldownService,
    ) {}

    public function index(): View
    {
        $character = Auth::user()->character;
        $currentCard = session('highlow_card');

        // Never start on King (13) or Ace (1) to prevent 100% risk-free exploitation
        if (! $currentCard || $currentCard <= 1 || $currentCard >= 13) {
            $currentCard = rand(2, 12);
            session(['highlow_card' => $currentCard]);
        }

        return view('game.highlow.index', compact('character', 'currentCard'));
    }

    public function play(Request $request): RedirectResponse
    {
        $character = Auth::user()->character;

        if ($character->isImprisoned()) {
            return back()->with('error', 'Vanuit de isoleercel kun je niet gokken!');
        }

        // Anti-spam cooldown check (6 seconds)
        if ($this->cooldownService->isOnCooldown($character, 'casino_highlow')) {
            $remaining = $this->cooldownService->getRemainingSeconds($character, 'casino_highlow');

            return back()->with('error', "⏳ Rustig aan! De croupier schudt de kaarten. Wacht nog {$remaining}s.");
        }

        $validated = $request->validate([
            'choice' => ['required', 'in:hoger,lager'],
            'bet' => ['required', 'integer', 'min:50', 'max:50000'],
        ]);

        $bet = (int) $validated['bet'];
        if ($character->cash < $bet) {
            return back()->with('error', 'Je hebt onvoldoende contant geld. Inzet: €'.number_format($bet, 0, ',', '.').'.');
        }

        $currentCard = (int) session('highlow_card', rand(2, 12));
        if ($currentCard <= 1 || $currentCard >= 13) {
            $currentCard = rand(2, 12);
        }

        $nextCard = rand(1, 13);

        // Store next base card: if it's 1 or 13, reshuffle to 2..12 for the next round
        $nextStartingCard = ($nextCard <= 1 || $nextCard >= 13) ? rand(2, 12) : $nextCard;
        session(['highlow_card' => $nextStartingCard]);

        // Set cooldown immediately for this turn
        $this->cooldownService->setCooldown($character, 'casino_highlow', 6);

        $cardLabels = [1 => 'A', 2 => '2', 3 => '3', 4 => '4', 5 => '5', 6 => '6', 7 => '7',
            8 => '8', 9 => '9', 10 => '10', 11 => 'J', 12 => 'Q', 13 => 'K'];

        $tie = $nextCard === $currentCard;
        $won = ($validated['choice'] === 'hoger' && $nextCard > $currentCard)
            || ($validated['choice'] === 'lager' && $nextCard < $currentCard);

        // In standard casino rules: Ties (gelijke kaart) are a house win
        if ($tie) {
            $character->cash -= $bet;
            $character->save();

            return back()->with('error', "Gelijkspel! Huidig: {$cardLabels[$currentCard]} tegen Nieuw: {$cardLabels[$nextCard]}. Bij gelijke kaart wint het huis! -€".number_format($bet, 0, ',', '.').'.');
        }

        if ($won) {
            // Dynamic odds calculation based on card difficulty:
            $favorable = $validated['choice'] === 'hoger' ? (13 - $currentCard) : ($currentCard - 1);
            $multiplier = round(max(1.15, min(4.5, (12 / max(1, $favorable)) * 0.95)), 2);
            $profit = (int) round($bet * ($multiplier - 1.0));

            $this->economy->addCash($character, $profit);
            $this->level->addXp($character, 15);

            return back()->with('success', "Gewonnen! Huidig: {$cardLabels[$currentCard]} → Nieuw: {$cardLabels[$nextCard]} ({$multiplier}x)! +€".number_format($profit, 0, ',', '.').'! +15 XP.');
        }

        $character->cash -= $bet;
        $character->save();

        return back()->with('error', "Verloren! Huidig: {$cardLabels[$currentCard]} → Nieuw: {$cardLabels[$nextCard]}. -€".number_format($bet, 0, ',', '.').'.');
    }
}
