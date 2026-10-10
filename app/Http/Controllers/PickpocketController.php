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

class PickpocketController extends Controller
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

        $actions = [
            ['id' => 1, 'name' => 'Bezoeker in de wachtkamer', 'desc' => 'Snel een portemonnee of smartphone meegrissen in de drukte.', 'energy' => 6, 'min' => 80, 'max' => 350, 'rate' => 52, 'xp' => 35, 'icon' => 'fa-person-walking'],
            ['id' => 2, 'name' => 'Verpleegster bij medicijnenuitgifte', 'desc' => 'Piepklein momentje als ze niet kijkt naar haar sleutelbos.', 'energy' => 10, 'min' => 200, 'max' => 800, 'rate' => 40, 'xp' => 70, 'icon' => 'fa-user-nurse'],
            ['id' => 3, 'name' => 'Medewerker bij de kantinekassa', 'desc' => 'Snelle greep uit de kassalade tijdens het wisselen.', 'energy' => 14, 'min' => 500, 'max' => 1800, 'rate' => 28, 'xp' => 115, 'icon' => 'fa-cash-register'],
            ['id' => 4, 'name' => 'Kliniekconsulent in de gang', 'desc' => 'Luxe lederen aktetas leeghalen van een externe inspecteur.', 'energy' => 18, 'min' => 1000, 'max' => 3500, 'rate' => 18, 'xp' => 200, 'icon' => 'fa-vault'],
            ['id' => 5, 'name' => 'Advocaat op bezoek', 'desc' => 'Dure portemonnee en contante voorschotten uit de binnenzak rollen.', 'energy' => 22, 'min' => 2000, 'max' => 5500, 'rate' => 14, 'xp' => 260, 'icon' => 'fa-briefcase'],
            ['id' => 6, 'name' => 'Hoofdbehandelaar tijdens ronde', 'desc' => 'Uiterst riskante zakkenrollerij op de hoogste afdelingschef.', 'energy' => 26, 'min' => 3500, 'max' => 9000, 'rate' => 10, 'xp' => 360, 'icon' => 'fa-stethoscope'],
        ];

        return view('game.pickpocket.index', compact('character', 'actions'));
    }

    public function steal(Request $request): RedirectResponse
    {
        $character = Auth::user()->character;

        if ($character->isImprisoned()) {
            return redirect()->route('prison')->with('error', 'Je kunt niet zakkenrollen vanuit de isoleercel!');
        }

        if ($this->cooldownService->isOnCooldown($character, 'pickpocket')) {
            $remaining = $this->cooldownService->getRemainingSeconds($character, 'pickpocket');
            $mins = ceil($remaining / 60);

            return back()->with('error', "⏳ Zakkenrollen cooldown actief! Wacht nog {$remaining}s (~{$mins} min) of sla over met 1 Ster ⭐.");
        }

        $validated = $request->validate([
            'action_id' => ['required', 'integer', 'in:1,2,3,4,5,6'],
        ]);

        $config = [
            1 => ['energy' => 6,  'min' => 80,   'max' => 350,  'rate' => 52, 'xp' => 35,  'jail' => false, 'jail_min' => 0],
            2 => ['energy' => 10, 'min' => 200,  'max' => 800,  'rate' => 40, 'xp' => 70,  'jail' => false, 'jail_min' => 0],
            3 => ['energy' => 14, 'min' => 500,  'max' => 1800, 'rate' => 28, 'xp' => 115, 'jail' => true,  'jail_min' => 7],
            4 => ['energy' => 18, 'min' => 1000, 'max' => 3500, 'rate' => 18, 'xp' => 200, 'jail' => true,  'jail_min' => 18],
            5 => ['energy' => 22, 'min' => 2000, 'max' => 5500, 'rate' => 14, 'xp' => 260, 'jail' => true,  'jail_min' => 24],
            6 => ['energy' => 26, 'min' => 3500, 'max' => 9000, 'rate' => 10, 'xp' => 360, 'jail' => true,  'jail_min' => 35],
        ];

        $action = $config[$validated['action_id']];

        if (($character->masks ?? 0) < 1) {
            return back()->with('error', 'Je hebt minimaal 1 Masker 🎭 nodig om ongemerkt te zakkenrollen! Koop maskers in de shop voor Sterren ⭐.');
        }

        if (! $this->energy->consume($character, $action['energy'])) {
            return back()->with('error', "Je hebt minimaal {$action['energy']} energie nodig voor dit zakkenrollen!");
        }

        // Deduct 1 mask per move
        $character->masks -= 1;
        $character->save();

        // Set 2 min cooldown (120s)
        $this->cooldownService->setCooldown($character, 'pickpocket', 120);

        $roll = rand(1, 100);
        if ($roll <= $action['rate']) {
            $cash = rand($action['min'], $action['max']);
            // Zwart geld contant!
            $this->economy->addCash($character, $cash);
            $this->level->addXp($character, $action['xp']);
            $character->respect += 1;
            $character->save();

            $dropMsg = RandomDropService::roll($character);
            $successMsg = 'Succesvol gerold! Je pakt €'.number_format($cash, 0, ',', '.')." contant uit de zakken! +{$action['xp']} XP (1 masker verbruikt).";
            if ($dropMsg) {
                $successMsg .= ' '.$dropMsg;
            }

            return back()->with('success', $successMsg);
        }

        if ($action['jail'] && rand(1, 100) <= 60) {
            $jailSeconds = random_int(85, 140);
            $this->prison->jailSeconds($character, $jailSeconds, 'Betrapt bij zakkenrollen.');

            return redirect()->route('prison')->with('error', "Poging mislukt! {$jailSeconds} sec vast in isolatie! (Gepakt door zaalwacht)");
        }

        $dmg = rand(2, 6);
        $character->health = max(1, $character->health - $dmg);
        $character->save();

        return back()->with('error', "Zakkenrollen mislukt! Je kreeg een duw van het slachtoffer. -{$dmg} HP.");
    }
}
