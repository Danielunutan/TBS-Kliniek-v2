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

class RouletteController extends Controller
{
    public function __construct(
        protected EconomyService $economy,
        protected LevelService $level,
        protected CooldownService $cooldownService,
    ) {}

    public function index(): View
    {
        $character = Auth::user()->character;

        return view('game.roulette.index', compact('character'));
    }

    public function play(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'bet_type' => ['required', 'in:rood,zwart,groen,even,oneven,eerste_helft,tweede_helft'],
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

            if ($this->cooldownService->isOnCooldown($character, 'casino_roulette')) {
                $remaining = $this->cooldownService->getRemainingSeconds($character, 'casino_roulette');

                return back()->with('error', "⏳ Het roulettewiel draait nog! Wacht nog {$remaining}s.");
            }

            $bet = (int) $validated['bet'];
            if (! $this->economy->deductCash($character, $bet)) {
                return back()->with('error', 'Je hebt onvoldoende contant geld. Inzet: €'.number_format($bet, 0, ',', '.').'.');
            }

            $this->cooldownService->setCooldown($character, 'casino_roulette', 8);

            $number = rand(0, 36);

            $rood = [1, 3, 5, 7, 9, 12, 14, 16, 18, 19, 21, 23, 25, 27, 30, 32, 34, 36];
            $isRood = in_array($number, $rood);
            $isZwart = $number > 0 && ! $isRood;
            $isGroen = $number === 0;
            $isEven = $number > 0 && $number % 2 === 0;
            $isOneven = $number > 0 && $number % 2 !== 0;
            $isEersteHelft = $number >= 1 && $number <= 18;
            $isTweedeHelft = $number >= 19 && $number <= 36;

            $colorLabel = $isGroen ? '🟢 Groen (0)' : ($isRood ? '🔴 Rood' : '⚫ Zwart');

            $betType = $validated['bet_type'];
            $won = match ($betType) {
                'rood' => $isRood,
                'zwart' => $isZwart,
                'groen' => $isGroen,
                'even' => $isEven,
                'oneven' => $isOneven,
                'eerste_helft' => $isEersteHelft,
                'tweede_helft' => $isTweedeHelft,
                default => false,
            };

            $multipliers = [
                'rood' => 2, 'zwart' => 2, 'groen' => 35,
                'even' => 2, 'oneven' => 2,
                'eerste_helft' => 2, 'tweede_helft' => 2,
            ];

            $multiplier = $multipliers[$betType] ?? 2;

            if ($won) {
                $payout = $bet * $multiplier;
                $this->economy->addCash($character, $payout);
                $this->level->addXp($character, 20);

                return back()->with('success', "Gewonnen! Bal landde op {$number} ({$colorLabel}). Je wint €".number_format($payout, 0, ',', '.')." ({$multiplier}x)! +20 XP.");
            }

            return back()->with('error', "Verloren! Bal landde op {$number} ({$colorLabel}). Je verliest €".number_format($bet, 0, ',', '.').'.');
        });
    }
}
