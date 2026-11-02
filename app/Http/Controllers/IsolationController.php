<?php

namespace App\Http\Controllers;

use App\Models\BankTransaction;
use App\Models\Character;
use App\Models\GameNotification;
use App\Services\EconomyService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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

        $isProtected = $character->hasIsolationProtection();
        $remainingSeconds = $character->getIsolationProtectionRemainingSeconds();
        $remainingMinutes = $isProtected && $character->protection_until ? max(0, (int) ceil(now()->diffInSeconds($character->protection_until, false) / 60)) : 0;
        $remainingHours = $isProtected ? round($remainingMinutes / 60, 1) : 0;

        $hasCashProtection = $character->hasCashProtection();
        $cashProtectionSeconds = $character->getCashProtectionRemainingSeconds();
        $hasVest = $character->hasVest();
        $vestSeconds = $character->getVestRemainingSeconds();

        return view('game.isolation.index', compact(
            'character',
            'packages',
            'isProtected',
            'remainingSeconds',
            'remainingMinutes',
            'remainingHours',
            'hasCashProtection',
            'cashProtectionSeconds',
            'hasVest',
            'vestSeconds'
        ));
    }

    public function activate(Request $request): RedirectResponse
    {
        $character = Auth::user()->character;

        if ($character->isImprisoned()) {
            return back()->with('error', 'Je zit al in de isoleercel als straf! Je kunt nu geen vrijwillige isolatie kopen.');
        }

        if ($character->isComatose()) {
            return back()->with('error', 'Je verkeert in Code Blauw (Hartstilstand)! Dien eerst een '.Character::REVIVE_SERUM_NAME.' toe.');
        }

        $validated = $request->validate([
            'hours' => ['required', 'integer', 'min:1', 'max:72'],
            'currency' => ['required', 'string', 'in:cash,stars'],
        ]);

        $hours = (int) $validated['hours'];
        $currency = $validated['currency'];

        $costCash = $hours * self::RATE_CASH_PER_HOUR;
        $costStars = $hours * self::RATE_STARS_PER_HOUR;

        // Pay and extend under one row lock so parallel requests cannot spend the same balance twice.
        $errorMessage = DB::transaction(function () use ($character, $currency, $hours, $costCash, $costStars): ?string {
            /** @var Character $locked */
            $locked = Character::where('id', $character->id)->lockForUpdate()->first();

            if ($currency === 'stars') {
                if (($locked->stars ?? 0) < $costStars) {
                    return "Onvoldoende sterren! Je hebt {$costStars} Sterren ⭐ nodig voor {$hours} uur isolatie (25 sterren per uur).";
                }

                $locked->stars -= $costStars;
            } else {
                // Check cash or combined cash + bank
                if (($locked->cash + $locked->bank) < $costCash) {
                    return 'Onvoldoende geld! Je hebt €'.number_format($costCash, 0, ',', '.')." nodig voor {$hours} uur isolatie (€50.000 per uur).";
                }

                if ($locked->cash >= $costCash) {
                    $locked->cash -= $costCash;
                } else {
                    $remainder = $costCash - $locked->cash;
                    $locked->cash = 0;
                    $locked->bank -= $remainder;
                    BankTransaction::record(
                        $locked,
                        'withdrawal',
                        'bank',
                        $remainder,
                        (int) $locked->bank,
                        "Vrijwillige Isolatie ({$hours} uur)"
                    );
                }
            }

            // Extend if already active, or start fresh
            if ($locked->protection_until && Carbon::now()->lt($locked->protection_until)) {
                $locked->protection_until = $locked->protection_until->copy()->addHours($hours);
            } else {
                $locked->protection_until = Carbon::now()->addHours($hours);
            }
            $locked->save();

            return null;
        });

        if ($errorMessage !== null) {
            return back()->with('error', $errorMessage);
        }

        $character->refresh();
        $paidLabel = $currency === 'stars'
            ? "{$costStars} Sterren ⭐"
            : '€'.number_format($costCash, 0, ',', '.');

        GameNotification::create([
            'character_id' => $character->id,
            'type' => 'protection',
            'icon' => 'fa-shield-halved',
            'title' => 'Vrijwillige Isolatie geactiveerd',
            'message' => "Je hebt {$hours} uur isolatie gekocht voor {$paidLabel}. Beschermd tot {$character->protection_until->format('d-m H:i')}.",
        ]);

        return back()->with('success', "🛡️ Vrijwillige isolatie geactiveerd voor {$hours} uur! Je bent volledig beschermd tot {$character->protection_until->format('d-m H:i')} (Betaald: {$paidLabel}).");
    }

    /**
     * Voluntarily end active isolation early.
     */
    public function terminate(): RedirectResponse
    {
        /** @var Character $character */
        $character = Auth::user()->character;

        if (! $character->hasIsolationProtection()) {
            return back()->with('info', 'Je cel zit momenteel niet op slot in vrijwillige isolatie.');
        }

        $character->breakProtection();

        GameNotification::create([
            'character_id' => $character->id,
            'type' => 'protection',
            'icon' => 'fa-door-open',
            'title' => 'Isolatie Vroegtijdig Beëindigd',
            'message' => 'Je hebt je vrijwillige isolatie handmatig beëindigd. Je celdeur is weer van het slot.',
        ]);

        return back()->with('success', '🔓 Vrijwillige isolatie beëindigd! Je celdeur is ontgrendeld en je kunt weer deelnemen aan gevechten en overvallen.');
    }
}
