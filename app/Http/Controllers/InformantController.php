<?php

namespace App\Http\Controllers;

use App\Services\EconomyService;
use App\Services\EnergyService;
use App\Services\LevelService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class InformantController extends Controller
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

        $informants = [
            ['id' => 1, 'name' => 'Gangpadverkenner',  'desc' => 'Scoort je informatie over rijke patiënten op jouw afdeling.', 'cost' => 500,  'energy' => 5,  'reward_min' => 300,  'reward_max' => 900,  'xp' => 30, 'icon' => 'fa-eye'],
            ['id' => 2, 'name' => 'Keukenmol',          'desc' => 'Vertelt je over waardevolle bezittingen in de kantine kluis.', 'cost' => 1200, 'energy' => 8,  'reward_min' => 700,  'reward_max' => 2200, 'xp' => 65, 'icon' => 'fa-bowl-food'],
            ['id' => 3, 'name' => 'Bewakersverrader',   'desc' => 'Een bewaker op de loonlijst. Stuurt je info over patrouilles.', 'cost' => 3000, 'energy' => 12, 'reward_min' => 1500, 'reward_max' => 5000, 'xp' => 130, 'icon' => 'fa-shield-halved'],
            ['id' => 4, 'name' => 'Directie-insider',   'desc' => 'Topspion met toegang tot het kantoor van de directeur.', 'cost' => 8000, 'energy' => 18, 'reward_min' => 4000, 'reward_max' => 12000, 'xp' => 250, 'icon' => 'fa-user-tie'],
        ];

        return view('game.informants.index', compact('character', 'informants'));
    }

    public function hire(Request $request): RedirectResponse
    {
        $character = Auth::user()->character;

        if ($character->isImprisoned()) {
            return redirect()->route('prison')->with('error', 'Je kunt geen informanten inhuren vanuit de isoleercel!');
        }

        $validated = $request->validate([
            'informant_id' => ['required', 'integer', 'in:1,2,3,4'],
        ]);

        $config = [
            1 => ['cost' => 500,  'energy' => 5,  'reward_min' => 300,  'reward_max' => 900,  'xp' => 30,  'label' => 'Gangpadverkenner'],
            2 => ['cost' => 1200, 'energy' => 8,  'reward_min' => 700,  'reward_max' => 2200, 'xp' => 65,  'label' => 'Keukenmol'],
            3 => ['cost' => 3000, 'energy' => 12, 'reward_min' => 1500, 'reward_max' => 5000, 'xp' => 130, 'label' => 'Bewakersverrader'],
            4 => ['cost' => 8000, 'energy' => 18, 'reward_min' => 4000, 'reward_max' => 12000, 'xp' => 250, 'label' => 'Directie-insider'],
        ];

        $info = $config[$validated['informant_id']];

        if ($character->cash < $info['cost']) {
            return back()->with('error', 'Je hebt €'.number_format($info['cost'], 0, ',', '.')." nodig om de {$info['label']} in te huren.");
        }

        if (! $this->energy->consume($character, $info['energy'])) {
            return back()->with('error', "Je hebt minimaal {$info['energy']} energie nodig voor deze informant!");
        }

        $character->cash -= $info['cost'];
        $character->save();

        // 80% success rate for informants (intel always comes through eventually)
        $roll = rand(1, 100);
        if ($roll <= 80) {
            $reward = rand($info['reward_min'], $info['reward_max']);
            $this->economy->addCash($character, $reward);
            $this->level->addXp($character, $info['xp']);
            $character->intelligence += 1;
            $character->respect += 1;
            $character->save();

            return back()->with('success', "Je {$info['label']} levert goudwaardige intel op! Je int €".number_format($reward, 0, ',', '.')." aan buit en winst. +{$info['xp']} XP, +1 Intelligentie.");
        }

        // Informant was caught or turned double agent
        return back()->with('error', "Je {$info['label']} is betrapt en gevlucht. Je verloor je investering van €".number_format($info['cost'], 0, ',', '.').'.');
    }
}
