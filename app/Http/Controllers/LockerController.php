<?php

namespace App\Http\Controllers;

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

class LockerController extends Controller
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

        $lockers = [
            ['id' => 1, 'name' => 'Roestig Kluisje (Kantine)', 'desc' => 'Oud slot, simpel te kraken met een paperclip.', 'energy' => 5, 'min' => 100, 'max' => 400, 'rate' => 50, 'xp' => 30, 'icon' => 'fa-lock-open'],
            ['id' => 2, 'name' => 'Personeelskluisje (Wachthok)', 'desc' => 'Iets beter slot. Meer inhoud ook.', 'energy' => 10, 'min' => 300, 'max' => 900, 'rate' => 38, 'xp' => 65, 'icon' => 'fa-key'],
            ['id' => 3, 'name' => 'Beveiligd Kluisje (Verpleging)', 'desc' => 'Digitaal slot. Vereist wat techniek.', 'energy' => 15, 'min' => 700, 'max' => 2000, 'rate' => 28, 'xp' => 115, 'icon' => 'fa-microchip'],
            ['id' => 4, 'name' => 'Kluisje Directeur (Kantoor)', 'desc' => 'Zwaarst beveiligd. Meeste geld, flink risico.', 'energy' => 20, 'min' => 1500, 'max' => 4500, 'rate' => 18, 'xp' => 210, 'icon' => 'fa-briefcase'],
            ['id' => 5, 'name' => 'Behandelkamer Medicijnkluis', 'desc' => 'Gepantserd slot vol verdovende middelen en cash.', 'energy' => 24, 'min' => 2500, 'max' => 6500, 'rate' => 14, 'xp' => 280, 'icon' => 'fa-capsules'],
            ['id' => 6, 'name' => 'Centrale Geldkluis (Administratie)', 'desc' => 'Kliniek hoofdkas. Extreem bewaakt en gecodeerd.', 'energy' => 28, 'min' => 4500, 'max' => 12500, 'rate' => 10, 'xp' => 400, 'icon' => 'fa-vault'],
        ];

        return view('game.lockers.index', compact('character', 'lockers'));
    }

    public function crack(Request $request): RedirectResponse
    {
        $character = Auth::user()->character;

        if ($character->isImprisoned()) {
            return redirect()->route('prison')->with('error', 'Vanuit de isoleercel kun je geen kluisjes kraken!');
        }

        if ($this->cooldownService->isOnCooldown($character, 'locker')) {
            $remaining = $this->cooldownService->getRemainingSeconds($character, 'locker');
            $mins = ceil($remaining / 60);

            return back()->with('error', "⏳ Kluisjes cooldown actief! Wacht nog {$remaining}s (~{$mins} min) of sla over met 1 Ster ⭐.");
        }

        $validated = $request->validate([
            'locker_id' => ['required', 'integer', 'in:1,2,3,4,5,6'],
        ]);

        $config = [
            1 => ['energy' => 5,  'min' => 100,  'max' => 400,   'rate' => 50, 'xp' => 30,  'jail' => false, 'jail_min' => 0],
            2 => ['energy' => 10, 'min' => 300,  'max' => 900,   'rate' => 38, 'xp' => 65,  'jail' => false, 'jail_min' => 0],
            3 => ['energy' => 15, 'min' => 700,  'max' => 2000,  'rate' => 28, 'xp' => 115, 'jail' => true,  'jail_min' => 10],
            4 => ['energy' => 20, 'min' => 1500, 'max' => 4500,  'rate' => 18, 'xp' => 210, 'jail' => true,  'jail_min' => 20],
            5 => ['energy' => 24, 'min' => 2500, 'max' => 6500,  'rate' => 14, 'xp' => 280, 'jail' => true,  'jail_min' => 25],
            6 => ['energy' => 28, 'min' => 4500, 'max' => 12500, 'rate' => 10, 'xp' => 400, 'jail' => true,  'jail_min' => 35],
        ];

        $locker = $config[$validated['locker_id']];

        if (! $this->energy->consume($character, $locker['energy'])) {
            return back()->with('error', "Je hebt minimaal {$locker['energy']} energie nodig om dit kluisje te kraken!");
        }

        // Set 3 min cooldown (180s)
        $this->cooldownService->setCooldown($character, 'locker', 180);

        $roll = rand(1, 100);
        if ($roll <= $locker['rate']) {
            $cash = rand($locker['min'], $locker['max']);
            // Zwart geld contant!
            $this->economy->addCash($character, $cash);
            $this->level->addXp($character, $locker['xp']);
            $character->respect += 1;
            $character->save();

            $dropMsg = RandomDropService::roll($character);
            $successMsg = 'Kluisje opengeknald! Je vindt €'.number_format($cash, 0, ',', '.')." contant erin! +{$locker['xp']} XP.";
            if ($dropMsg) {
                $successMsg .= ' '.$dropMsg;
            }

            return back()->with('success', $successMsg);
        }

        if ($locker['jail'] && rand(1, 100) <= 60) {
            $jailSeconds = random_int(90, 150);
            $this->prison->jailSeconds($character, $jailSeconds, 'Betrapt bij het kraken van een kluisje.');

            return redirect()->route('prison')->with('error', "Poging mislukt! {$jailSeconds} sec vast in isolatie! (Alarm bij kluisjes)");
        }

        $dmg = rand(3, 8);
        $character->health = max(1, $character->health - $dmg);
        $character->save();

        return back()->with('error', "Kluisje kraken mislukt! Het slot gaf een elektrische schok. -{$dmg} HP.");
    }
}
