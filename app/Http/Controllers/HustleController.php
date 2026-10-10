<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\CooldownService;
use App\Services\EconomyService;
use App\Services\EnergyService;
use App\Services\LevelService;
use App\Services\PrisonService;
use App\Services\RandomDropService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class HustleController extends Controller
{
    public function __construct(
        protected EconomyService $economy,
        protected EnergyService $energy,
        protected LevelService $level,
        protected PrisonService $prison,
        protected CooldownService $cooldownService,
    ) {}

    public function index(): View
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;
        $this->energy->refresh($character);

        $hustles = [
            1 => [
                'id' => 1,
                'name' => 'Bewaker afleiden bij de sluis',
                'description' => 'Maak een praatje over voetbal om wat kleingeld of sigaretten te bietsen.',
                'cost' => 5,
                'rate' => 52,
                'cash_min' => 40,
                'cash_max' => 120,
                'xp' => 30,
                'respect' => 1,
                'risk' => 'Gemiddeld (48% mislukkingskans)',
            ],
            2 => [
                'id' => 2,
                'name' => 'Keukenpersoneel omkopen voor warme snacks',
                'description' => 'Ritsel versgebakken snacks die je direct kan doorverkopen.',
                'cost' => 10,
                'rate' => 40,
                'cash_min' => 100,
                'cash_max' => 260,
                'xp' => 60,
                'respect' => 2,
                'risk' => 'Verhoogd (60% risico)',
            ],
            3 => [
                'id' => 3,
                'name' => 'Medicijnen achteroverdrukken bij de zuster',
                'description' => 'Grijp snel een doordrukstrip uit het mandje als zuster Ingrid wegkijkt.',
                'cost' => 15,
                'rate' => 30,
                'cash_min' => 250,
                'cash_max' => 600,
                'xp' => 100,
                'respect' => 3,
                'risk' => 'Hoog (70% risico, kans op isoleercel)',
            ],
            4 => [
                'id' => 4,
                'name' => 'Gokpool organiseren in de recreatieruimte',
                'description' => 'Laat medegevangenen wedden op pingpong en pak het huisvoordeel.',
                'cost' => 20,
                'rate' => 20,
                'cash_min' => 500,
                'cash_max' => 1200,
                'xp' => 160,
                'respect' => 5,
                'risk' => 'Zeer Hoog (80% risico op betrapping)',
            ],
            5 => [
                'id' => 5,
                'name' => 'Kantine sigaretten kartels opzetten',
                'description' => 'Controleer de aanvoer van zware shag en vapes over alle drie de afdelingen.',
                'cost' => 24,
                'rate' => 16,
                'cash_min' => 900,
                'cash_max' => 2200,
                'xp' => 220,
                'respect' => 7,
                'risk' => 'Extreem Hoog (84% risico, direct isoleercel)',
            ],
            6 => [
                'id' => 6,
                'name' => 'Illegale smartphone verhuren',
                'description' => 'Verhuur een binnengesmokkelde prepaid telefoon voor 15 minuten per belbeurt.',
                'cost' => 28,
                'rate' => 12,
                'cash_min' => 1500,
                'cash_max' => 3800,
                'xp' => 320,
                'respect' => 10,
                'risk' => 'Kliniekalarm (88% risico, zware straf)',
            ],
        ];

        return view('game.hustle.index', compact('character', 'hustles'));
    }

    public function attempt(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;

        if ($character->isImprisoned()) {
            return redirect()->route('prison')->with('error', 'Je zit in de isoleercel en kunt niet hosselen!');
        }

        if ($this->cooldownService->isOnCooldown($character, 'hustle')) {
            $remaining = $this->cooldownService->getRemainingSeconds($character, 'hustle');
            $mins = ceil($remaining / 60);

            return back()->with('error', "⏳ Hossel cooldown actief! Wacht nog {$remaining}s (~{$mins} min) of sla de wachttijd over met 1 Ster ⭐.");
        }

        $validated = $request->validate([
            'hustle_id' => 'required|integer|between:1,6',
        ]);

        $hustles = [
            1 => ['cost' => 5,  'rate' => 52, 'cash_min' => 40,   'cash_max' => 120,  'xp' => 30,  'respect' => 1,  'jail_minutes' => 0],
            2 => ['cost' => 10, 'rate' => 40, 'cash_min' => 100,  'cash_max' => 260,  'xp' => 60,  'respect' => 2,  'jail_minutes' => 0],
            3 => ['cost' => 15, 'rate' => 30, 'cash_min' => 250,  'cash_max' => 600,  'xp' => 100, 'respect' => 3,  'jail_minutes' => 6],
            4 => ['cost' => 20, 'rate' => 20, 'cash_min' => 500,  'cash_max' => 1200, 'xp' => 160, 'respect' => 5,  'jail_minutes' => 14],
            5 => ['cost' => 24, 'rate' => 16, 'cash_min' => 900,  'cash_max' => 2200, 'xp' => 220, 'respect' => 7,  'jail_minutes' => 20],
            6 => ['cost' => 28, 'rate' => 12, 'cash_min' => 1500, 'cash_max' => 3800, 'xp' => 320, 'respect' => 10, 'jail_minutes' => 30],
        ];

        $hustle = $hustles[$validated['hustle_id']];

        if (! $this->energy->consume($character, $hustle['cost'])) {
            return back()->with('error', 'Niet genoeg energie! Je hebt minstens '.$hustle['cost'].' energie nodig.');
        }

        // Set 3 minute cooldown (180s)
        $this->cooldownService->setCooldown($character, 'hustle', 180);

        $roll = rand(1, 100);

        if ($roll <= $hustle['rate']) {
            $cash = rand($hustle['cash_min'], $hustle['cash_max']);
            // Zwart geld contant!
            $this->economy->addCash($character, $cash);
            $this->level->addXp($character, $hustle['xp']);
            $character->respect += $hustle['respect'];
            $character->save();

            $dropMsg = RandomDropService::roll($character);
            $successMsg = 'Hossel gelukt! Je scoorde €'.number_format($cash, 0, ',', '.')." contant, +{$hustle['xp']} XP en +{$hustle['respect']} respect!";
            if ($dropMsg) {
                $successMsg .= ' '.$dropMsg;
            }

            return back()->with('success', $successMsg);
        }

        if ($hustle['jail_minutes'] > 0 && rand(1, 100) <= 60) {
            $jailSeconds = random_int(80, 150);
            $this->prison->jailSeconds($character, $jailSeconds, 'Gepakt tijdens hosselen.');

            return redirect()->route('prison')->with('error', "Poging mislukt! {$jailSeconds} sec vast in isolatie! (Gepakt door bewakers)");
        }

        return back()->with('error', 'Hossel mislukt! Iemand waarschuwde de zaalwacht en je moest snel maken dat je wegkwam.');
    }
}
