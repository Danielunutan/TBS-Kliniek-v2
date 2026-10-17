<?php

namespace App\Http\Controllers;

use App\Services\CooldownService;
use App\Services\EconomyService;
use App\Services\EnergyService;
use App\Services\LevelService;
use App\Services\PrisonService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ExtortController extends Controller
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
        $character = Auth::user()->character;
        $this->energy->refresh($character);

        $targets = [
            ['id' => 1, 'name' => 'Kliniekdirecteur-assistent', 'desc' => 'Chantage met geheime cliëntendossiers. Gevaarlijk maar lucratief.', 'energy' => 10, 'min' => 400, 'max' => 1200, 'rate' => 26, 'cooldown' => $this->cooldownService->calculateBySuccessRate(26), 'xp' => 95, 'icon' => 'fa-user-tie'],
            ['id' => 2, 'name' => 'Caféhouder in de kantine', 'desc' => 'Eis een deel van de dagomzet en sigarettenvoorraad af.', 'energy' => 12, 'min' => 450, 'max' => 1200, 'rate' => 38, 'cooldown' => $this->cooldownService->calculateBySuccessRate(38), 'xp' => 85, 'icon' => 'fa-mug-hot'],
            ['id' => 3, 'name' => 'Jonge verslaafde', 'desc' => 'Betaalt snel uit angst maar vertelt ook snel.', 'energy' => 14, 'min' => 600, 'max' => 1800, 'rate' => 34, 'cooldown' => $this->cooldownService->calculateBySuccessRate(34), 'xp' => 110, 'icon' => 'fa-user-injured'],
            ['id' => 4, 'name' => 'Rijke patiënt (Paviljoen 6)', 'desc' => 'Grote beloning van een verwende erfgenaam in de kliniek.', 'energy' => 18, 'min' => 1500, 'max' => 4000, 'rate' => 22, 'cooldown' => $this->cooldownService->calculateBySuccessRate(22), 'xp' => 180, 'icon' => 'fa-gem'],
            ['id' => 5, 'name' => 'Kliniekarts & Psychiater', 'desc' => 'Dwing privileges en medicijnvoorraden af met harde chantage.', 'energy' => 22, 'min' => 2500, 'max' => 6500, 'rate' => 15, 'cooldown' => $this->cooldownService->calculateBySuccessRate(15), 'xp' => 240, 'icon' => 'fa-user-doctor'],
            ['id' => 6, 'name' => 'Hoofd Beveiliging', 'desc' => 'Het allerhoogste risico. Corrupte deals onder druk zetten.', 'energy' => 28, 'min' => 4000, 'max' => 11000, 'rate' => 10, 'cooldown' => $this->cooldownService->calculateBySuccessRate(10), 'xp' => 350, 'icon' => 'fa-shield-halved'],
        ];

        $extortSkipCost = $this->cooldownService->getSkipCost('extort');

        return view('game.extort.index', compact('character', 'targets', 'extortSkipCost'));
    }

    public function extort(Request $request): RedirectResponse
    {
        $character = Auth::user()->character;

        if ($character->isImprisoned()) {
            return redirect()->route('prison')->with('error', 'Je kunt niet afpersen vanuit de isoleercel!');
        }

        if ($this->cooldownService->isOnCooldown($character, 'extort')) {
            $remaining = $this->cooldownService->getRemainingSeconds($character, 'extort');
            $mins = ceil($remaining / 60);

            return back()->with('error', "⏳ Afpersen cooldown actief! Wacht nog {$remaining}s (~{$mins} min) of reset direct via een sponsor advertentie.");
        }

        $validated = $request->validate([
            'target_id' => ['required', 'integer', 'in:1,2,3,4,5,6'],
        ]);

        $config = [
            1 => ['energy' => 10, 'min' => 400,  'max' => 1200,  'rate' => 26, 'xp' => 95,  'jail' => true,  'jail_min' => 10],
            2 => ['energy' => 12, 'min' => 450,  'max' => 1200,  'rate' => 38, 'xp' => 85,  'jail' => false, 'jail_min' => 0],
            3 => ['energy' => 14, 'min' => 600,  'max' => 1800,  'rate' => 34, 'xp' => 110, 'jail' => true,  'jail_min' => 6],
            4 => ['energy' => 18, 'min' => 1500, 'max' => 4000,  'rate' => 22, 'xp' => 180, 'jail' => true,  'jail_min' => 15],
            5 => ['energy' => 22, 'min' => 2500, 'max' => 6500,  'rate' => 15, 'xp' => 240, 'jail' => true,  'jail_min' => 20],
            6 => ['energy' => 28, 'min' => 4000, 'max' => 11000, 'rate' => 10, 'xp' => 350, 'jail' => true,  'jail_min' => 30],
        ];

        $target = $config[$validated['target_id']];

        if (! $this->energy->consume($character, $target['energy'])) {
            return back()->with('error', "Je hebt minimaal {$target['energy']} energie nodig om af te persen!");
        }

        $cooldownSeconds = $this->cooldownService->calculateBySuccessRate($target['rate']);
        $this->cooldownService->setCooldown($character, 'extort', $cooldownSeconds);

        $roll = rand(1, 100);
        if ($roll <= $target['rate']) {
            $cash = rand($target['min'], $target['max']);
            // Zwart geld contant!
            $this->economy->addCash($character, $cash);
            $this->level->addXp($character, $target['xp']);
            $character->respect += 2;
            $character->save();

            return back()->with('success', 'Afpersing geslaagd! Het doelwit betaalt €'.number_format($cash, 0, ',', '.')." contant uit angst. +{$target['xp']} XP.");
        }

        if ($target['jail'] && rand(1, 100) <= 60) {
            $jailSeconds = random_int(100, 180);
            $this->prison->jailSeconds($character, $jailSeconds, 'Betrapt bij afpersing.');

            return redirect()->route('prison')->with('error', "Poging mislukt! {$jailSeconds} sec vast in isolatie! (Doelwit sloeg alarm!)");
        }

        return back()->with('error', 'Afpersing mislukt! Het doelwit weigerde te buigen en dreigde alarm te slaan.');
    }
}
