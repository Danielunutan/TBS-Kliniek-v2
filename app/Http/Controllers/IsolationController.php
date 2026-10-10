<?php

namespace App\Http\Controllers;

use App\Models\GameNotification;
use App\Services\EconomyService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class IsolationController extends Controller
{
    public const RATE_CASH_PER_HOUR = 50000;

    public const RATE_STARS_PER_HOUR = 25;

    public function __construct(
        protected EconomyService $economyService,
    ) {}

    public function index(): View
    {
        $character = Auth::user()->character;

        $packages = [
            ['hours' => 1, 'cost_cash' => 50000, 'cost_stars' => 25, 'label' => '1 uur isolatie'],
            ['hours' => 2, 'cost_cash' => 100000, 'cost_stars' => 50, 'label' => '2 uur isolatie'],
            ['hours' => 4, 'cost_cash' => 200000, 'cost_stars' => 100, 'label' => '4 uur isolatie'],
            ['hours' => 8, 'cost_cash' => 400000, 'cost_stars' => 200, 'label' => '8 uur isolatie'],
            ['hours' => 12, 'cost_cash' => 600000, 'cost_stars' => 300, 'label' => '12 uur isolatie'],
            ['hours' => 24, 'cost_cash' => 1200000, 'cost_stars' => 600, 'label' => '24 uur isolatie (1 dag)'],
        ];

        $isProtected = $character->protection_until && Carbon::now()->lt($character->protection_until);
        $remainingMinutes = $isProtected ? Carbon::now()->diffInMinutes($character->protection_until) : 0;
        $remainingHours = $isProtected ? round($remainingMinutes / 60, 1) : 0;

        return view('game.isolation.index', compact(
            'character',
            'packages',
            'isProtected',
            'remainingMinutes',
            'remainingHours'
        ));
    }

    public function activate(Request $request): RedirectResponse
    {
        $character = Auth::user()->character;

        if ($character->isImprisoned()) {
            return back()->with('error', 'Je zit al in de isoleercel als straf! Je kunt nu geen vrijwillige isolatie kopen.');
        }

        $validated = $request->validate([
            'hours' => ['required', 'integer', 'min:1', 'max:72'],
            'currency' => ['required', 'string', 'in:cash,stars'],
        ]);

        $hours = (int) $validated['hours'];
        $currency = $validated['currency'];

        $costCash = $hours * self::RATE_CASH_PER_HOUR;
        $costStars = $hours * self::RATE_STARS_PER_HOUR;

        if ($currency === 'stars') {
            if (($character->stars ?? 0) < $costStars) {
                return back()->with('error', "Onvoldoende sterren! Je hebt {$costStars} Sterren ⭐ nodig voor {$hours} uur isolatie (25 sterren per uur).");
            }

            $this->economyService->deductStars($character, $costStars);
            $paidLabel = "{$costStars} Sterren ⭐";
        } else {
            // Check cash or combined cash + bank
            $totalFunds = $character->cash + $character->bank;
            if ($totalFunds < $costCash) {
                return back()->with('error', 'Onvoldoende geld! Je hebt €'.number_format($costCash, 0, ',', '.')." nodig voor {$hours} uur isolatie (€50.000 per uur).");
            }

            if ($character->cash >= $costCash) {
                $character->cash -= $costCash;
            } else {
                $remainder = $costCash - $character->cash;
                $character->cash = 0;
                $character->bank -= $remainder;
            }
            $character->save();
            $paidLabel = '€'.number_format($costCash, 0, ',', '.');
        }

        // Extend if already active, or start fresh
        if ($character->protection_until && Carbon::now()->lt($character->protection_until)) {
            $character->protection_until = $character->protection_until->addHours($hours);
        } else {
            $character->protection_until = Carbon::now()->addHours($hours);
        }
        $character->save();

        GameNotification::create([
            'character_id' => $character->id,
            'type' => 'protection',
            'icon' => 'fa-shield-halved',
            'title' => 'Vrijwillige Isolatie geactiveerd',
            'message' => "Je hebt {$hours} uur isolatie gekocht voor {$paidLabel}. Beschermd tot {$character->protection_until->format('d-m H:i')}.",
        ]);

        return back()->with('success', "🛡️ Vrijwillige isolatie geactiveerd voor {$hours} uur! Je bent volledig beschermd tot {$character->protection_until->format('d-m H:i')} (Betaald: {$paidLabel}).");
    }
}
