<?php

namespace App\Http\Controllers;

use App\Models\Character;
use App\Services\CooldownService;
use App\Services\EconomyService;
use App\Services\EventCashHuntService;
use App\Services\LevelService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class RpsController extends Controller
{
    public function __construct(
        protected EconomyService $economy,
        protected LevelService $level,
        protected CooldownService $cooldownService,
    ) {}

    public function index(): View
    {
        $character = Auth::user()->character;

        return view('game.rps.index', compact('character'));
    }

    public function play(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'choice' => ['required', 'in:steen,papier,schaar'],
            'bet' => ['required', 'integer', 'min:50', 'max:50000'],
        ]);

        return DB::transaction(function () use ($user, $validated) {
            /** @var Character $character */
            $character = Character::where('id', $user->character->id)->lockForUpdate()->first();

            if ($character->isImprisoned()) {
                return back()->with('error', 'Vanuit de isoleercel kun je niet gokken!');
            }

            if ($character->isComatose()) {
                return back()->with('error', 'Je verkeert in Code Blauw (Hartstilstand)! Dien eerst een '.Character::REVIVE_SERUM_NAME.' toe.');
            }

            if ($this->cooldownService->isOnCooldown($character, 'casino_rps')) {
                $remaining = $this->cooldownService->getRemainingSeconds($character, 'casino_rps');

                return back()->with('error', "⏳ Even schudden! Wacht nog {$remaining}s voor een volgend potje.");
            }

            $bet = (int) $validated['bet'];

            // Atomic cash deduction before executing spin/round
            if (! $this->economy->deductCash($character, $bet)) {
                return back()->with('error', 'Je hebt onvoldoende contant geld. Je inzet: €'.number_format($bet, 0, ',', '.').'.');
            }

            $this->cooldownService->setCooldown($character, 'casino_rps', 5);

            $options = ['steen', 'papier', 'schaar'];
            $houseChoice = $options[array_rand($options)];
            $playerChoice = $validated['choice'];

            $wins = ['steen' => 'schaar', 'papier' => 'steen', 'schaar' => 'papier'];
            $labels = ['steen' => '🪨 Steen', 'papier' => '📄 Papier', 'schaar' => '✂️ Schaar'];

            if ($playerChoice === $houseChoice) {
                $this->economy->addCash($character, $bet, 'rps_refund', false);

                return back()->with('success', "Gelijkspel! Jij koos {$labels[$playerChoice]}, huis koos {$labels[$houseChoice]}. Inzet terug.");
            }

            if ($wins[$playerChoice] === $houseChoice) {
                $payout = $bet * 2;
                $this->economy->addCash($character, $payout, 'rps', false);
                EventCashHuntService::record($character, $bet, 'rps');
                $this->level->addXp($character, 15);

                return back()->with('success', "Gewonnen! Jij: {$labels[$playerChoice]} vs Huis: {$labels[$houseChoice]}. Je wint €".number_format($bet, 0, ',', '.').'! +15 XP.');
            }

            return back()->with('error', "Verloren! Jij: {$labels[$playerChoice]} vs Huis: {$labels[$houseChoice]}. Je verliest €".number_format($bet, 0, ',', '.').'.');
        });
    }
}
