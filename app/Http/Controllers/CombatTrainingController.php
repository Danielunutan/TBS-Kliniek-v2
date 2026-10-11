<?php

namespace App\Http\Controllers;

use App\Services\EconomyService;
use App\Services\EnergyService;
use App\Services\LevelService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CombatTrainingController extends Controller
{
    public function __construct(
        protected EconomyService $economy,
        protected EnergyService $energy,
        protected LevelService $level,
    ) {}

    public function index(): View
    {
        $character = Auth::user()->character;
        $this->energy->refresh($character);

        $sessions = [
            ['id' => 1, 'name' => 'Schaduwboksen',        'desc' => 'Basis boksoefening om je reflexen te trainen.', 'energy' => 10, 'cost' => 150,  'stat' => 'strength',  'gain' => 1, 'xp' => 40, 'icon' => 'fa-hand-fist'],
            ['id' => 2, 'name' => 'Schietstandoefening',  'desc' => 'Train je scherpschuttervaardigheden op een dummy.', 'energy' => 12, 'cost' => 250,  'stat' => 'defense',   'gain' => 1, 'xp' => 55, 'icon' => 'fa-bullseye'],
            ['id' => 3, 'name' => 'Uithoudingsvermogen',  'desc' => 'Intensieve duurloop en conditie-training.', 'energy' => 15, 'cost' => 400,  'stat' => 'stamina',   'gain' => 1, 'xp' => 70, 'icon' => 'fa-person-running'],
            ['id' => 4, 'name' => 'Tactiek & Discipline', 'desc' => 'Mentale training van militaire tactieken.', 'energy' => 18, 'cost' => 600,  'stat' => 'discipline', 'gain' => 1, 'xp' => 90, 'icon' => 'fa-chess'],
        ];

        return view('game.combat_training.index', compact('character', 'sessions'));
    }

    public function train(Request $request): RedirectResponse
    {
        $character = Auth::user()->character;

        if ($character->isImprisoned()) {
            return redirect()->route('prison')->with('error', 'Vanuit de isoleercel kun je niet trainen!');
        }

        $validated = $request->validate([
            'session_id' => ['required', 'integer', 'in:1,2,3,4'],
        ]);

        $config = [
            1 => ['energy' => 10, 'cost' => 150, 'stat' => 'strength',  'gain' => 1, 'xp' => 40, 'label' => 'Kracht'],
            2 => ['energy' => 12, 'cost' => 250, 'stat' => 'defense',   'gain' => 1, 'xp' => 55, 'label' => 'Verdediging'],
            3 => ['energy' => 15, 'cost' => 400, 'stat' => 'stamina',   'gain' => 1, 'xp' => 70, 'label' => 'Uithoudingsvermogen'],
            4 => ['energy' => 18, 'cost' => 600, 'stat' => 'discipline', 'gain' => 1, 'xp' => 90, 'label' => 'Discipline'],
        ];

        $session = $config[$validated['session_id']];

        if ($character->cash < $session['cost']) {
            return back()->with('error', 'Je hebt €'.number_format($session['cost'], 0, ',', '.').' nodig voor deze training. Je hebt onvoldoende contant geld.');
        }

        if (! $this->energy->consume($character, $session['energy'])) {
            return back()->with('error', "Je hebt minimaal {$session['energy']} energie nodig voor deze trainingssessie!");
        }

        $character->cash -= $session['cost'];
        $character->{$session['stat']} += $session['gain'];
        $character->save();

        $this->level->addXp($character, $session['xp']);

        return back()->with('success', "Training voltooid! Je {$session['label']} stijgt met +{$session['gain']}! -€".number_format($session['cost'], 0, ',', '.').", +{$session['xp']} XP.");
    }
}
