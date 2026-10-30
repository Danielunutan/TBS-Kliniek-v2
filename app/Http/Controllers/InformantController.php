<?php

namespace App\Http\Controllers;

use App\Models\Character;
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
        /** @var Character $character */
        $character = Auth::user()->character;
        $this->energy->refresh($character);

        $informants = [
            ['id' => 1, 'name' => 'Gangpadverkenner',  'desc' => 'Scoort je informatie over rijke patiënten op jouw afdeling.', 'cost' => 500,  'energy' => 5,  'reward_min' => 300,  'reward_max' => 900,  'xp' => 30, 'icon' => 'fa-eye'],
            ['id' => 2, 'name' => 'Keukenmol',          'desc' => 'Vertelt je over waardevolle bezittingen in de kantine kluis.', 'cost' => 1200, 'energy' => 8,  'reward_min' => 700,  'reward_max' => 2200, 'xp' => 65, 'icon' => 'fa-bowl-food'],
            ['id' => 3, 'name' => 'Bewakersverrader',   'desc' => 'Een bewaker op de loonlijst. Stuurt je info over patrouilles.', 'cost' => 3000, 'energy' => 12, 'reward_min' => 1500, 'reward_max' => 5000, 'xp' => 130, 'icon' => 'fa-shield-halved'],
            ['id' => 4, 'name' => 'Directie-insider',   'desc' => 'Topspion met toegang tot het kantoor van de directeur.', 'cost' => 8000, 'energy' => 18, 'reward_min' => 4000, 'reward_max' => 12000, 'xp' => 250, 'icon' => 'fa-user-tie'],
        ];

        $intelCatalog = Character::intelCatalog();
        $activeIntelOps = $character->getActiveIntelOps();
        $attackerWing = $character->current_location ?? 'Paviljoen 1 (Gesloten)';
        $intelTargetIds = collect($activeIntelOps)->pluck('target_id')->unique()->all();
        $intelTargetsById = Character::whereIn('id', $intelTargetIds)->get()->keyBy('id');

        $enrichedIntelOps = array_map(function (array $op) use ($intelTargetsById, $attackerWing): array {
            /** @var Character|null $targetModel */
            $targetModel = $intelTargetsById->get($op['target_id']);
            if ($targetModel && $op['is_ready']) {
                $targetWing = $targetModel->current_location ?? 'Paviljoen 1 (Gesloten)';
                $op['revealed_location'] = $targetWing;
                $op['same_wing'] = ($targetWing === $attackerWing);
                $op['has_vest'] = $targetModel->hasVest();
                $op['vest_name'] = $targetModel->getActiveVestInfo()['name'] ?? null;
                $op['cash_formatted'] = '€'.number_format((int) $targetModel->cash, 0, ',', '.');
                $op['bank_formatted'] = '€'.number_format((int) $targetModel->bank, 0, ',', '.');
                $op['is_wing_locked'] = $targetModel->isWingLocked();
            } else {
                $op['revealed_location'] = null;
                $op['same_wing'] = false;
                $op['has_vest'] = false;
                $op['vest_name'] = null;
                $op['cash_formatted'] = '???';
                $op['bank_formatted'] = '???';
                $op['is_wing_locked'] = false;
            }

            return $op;
        }, $activeIntelOps);

        $patients = Character::where('id', '!=', $character->id)
            ->orderByDesc('level')
            ->limit(50)
            ->get(['id', 'name', 'level']);

        return view('game.informants.index', compact('character', 'informants', 'intelCatalog', 'enrichedIntelOps', 'patients'));
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

        $this->energy->refresh($character);
        if ($character->energy < $info['energy']) {
            return back()->with('error', "Je hebt minimaal {$info['energy']} energie nodig voor deze informant!");
        }

        if (! $this->economy->deductCash($character, $info['cost'])) {
            return back()->with('error', 'Je hebt €'.number_format($info['cost'], 0, ',', '.')." nodig om de {$info['label']} in te huren.");
        }

        if (! $this->energy->consume($character, $info['energy'])) {
            $this->economy->addCash($character, $info['cost']);

            return back()->with('error', "Je hebt minimaal {$info['energy']} energie nodig voor deze informant!");
        }

        // 80% success rate for informants (intel always comes through eventually)
        $roll = rand(1, 100);
        if ($roll <= 80) {
            $reward = rand($info['reward_min'], $info['reward_max']);
            $this->economy->addCash($character, $reward);
            $this->level->addXp($character, $info['xp']);
            $maxStat = $character->getMaxStatForLevel();
            if ((int) ($character->intelligence ?? 10) < $maxStat) {
                $character->intelligence = min($maxStat, (int) ($character->intelligence ?? 10) + 1);
            }
            $character->respect += 1;
            $character->save();

            return back()->with('success', "Je {$info['label']} levert goudwaardige intel op! Je int €".number_format($reward, 0, ',', '.')." aan buit en winst. +{$info['xp']} XP, +1 Intelligentie.");
        }

        // Informant was caught or turned double agent
        return back()->with('error', "Je {$info['label']} is betrapt en gevlucht. Je verloor je investering van €".number_format($info['cost'], 0, ',', '.').'.');
    }
}
