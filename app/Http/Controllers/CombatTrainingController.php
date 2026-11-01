<?php

namespace App\Http\Controllers;

use App\Models\Character;
use App\Models\TrainingLog;
use App\Services\EconomyService;
use App\Services\EnergyService;
use App\Services\HalloweenService;
use App\Services\LevelService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
        $character = Auth::user()?->character;
        if (! $character) {
            abort(404, 'Karakter niet gevonden.');
        }

        $this->energy->refresh($character);
        $character->syncStatsWithLevelCap();

        $sessions = [
            ['id' => 1, 'name' => 'Schaduwboksen',        'desc' => 'Basis boksoefening om je reflexen en slagkracht te trainen.', 'energy' => 10, 'cost' => 150,  'stat' => 'strength',   'label' => 'Kracht',       'gain' => 1, 'xp' => 40, 'level_xp' => 80,  'icon' => 'fa-hand-fist'],
            ['id' => 2, 'name' => 'Schietstandoefening',  'desc' => 'Train je dekking en scherpschuttervaardigheden op een dummy.', 'energy' => 12, 'cost' => 250,  'stat' => 'defense',    'label' => 'Verdediging',  'gain' => 1, 'xp' => 55, 'level_xp' => 110, 'icon' => 'fa-bullseye'],
            ['id' => 3, 'name' => 'Uithoudingsvermogen',  'desc' => 'Intensieve duurloop en conditie-training.',                   'energy' => 15, 'cost' => 400,  'stat' => 'stamina',    'label' => 'Conditie',     'gain' => 1, 'xp' => 70, 'level_xp' => 140, 'icon' => 'fa-person-running'],
            ['id' => 4, 'name' => 'Tactiek & Discipline', 'desc' => 'Mentale training van militaire tactieken en zelfbeheersing.', 'energy' => 18, 'cost' => 600,  'stat' => 'discipline', 'label' => 'Discipline',   'gain' => 1, 'xp' => 90, 'level_xp' => 180, 'icon' => 'fa-chess'],
        ];

        return view('game.combat_training.index', compact('character', 'sessions'));
    }

    public function train(Request $request): RedirectResponse
    {
        $character = Auth::user()?->character;
        if (! $character) {
            return redirect()->route('dashboard')->with('error', 'Geen personage gevonden.');
        }

        if ($character->isImprisoned()) {
            return redirect()->route('prison')->with('error', 'Vanuit de isoleercel kun je niet trainen!');
        }

        if ($character->isComatose()) {
            return back()->with('error', 'Je verkeert in Code Blauw (Hartstilstand)! Dien eerst een '.Character::REVIVE_SERUM_NAME.' toe.');
        }

        $validated = $request->validate([
            'session_id' => ['required', 'integer', 'in:1,2,3,4'],
            'mode' => ['nullable', 'string', 'in:stat,level'],
        ]);

        $character->syncStatsWithLevelCap();

        $config = [
            1 => ['energy' => 10, 'cost' => 150, 'stat' => 'strength',   'gain' => 1, 'xp' => 40, 'level_xp' => 80,  'label' => 'Kracht'],
            2 => ['energy' => 12, 'cost' => 250, 'stat' => 'defense',    'gain' => 1, 'xp' => 55, 'level_xp' => 110, 'label' => 'Verdediging'],
            3 => ['energy' => 15, 'cost' => 400, 'stat' => 'stamina',    'gain' => 1, 'xp' => 70, 'level_xp' => 140, 'label' => 'Uithoudingsvermogen'],
            4 => ['energy' => 18, 'cost' => 600, 'stat' => 'discipline', 'gain' => 1, 'xp' => 90, 'level_xp' => 180, 'label' => 'Discipline'],
        ];

        $session = $config[$validated['session_id']];
        $mode = $validated['mode'] ?? 'stat';
        $statKey = $session['stat'];

        return DB::transaction(function () use ($character, $session, $mode, $statKey): RedirectResponse {
            /** @var Character $locked */
            $locked = Character::where('id', $character->id)->lockForUpdate()->first();
            $maxStat = $locked->getMaxStatForLevel();
            $currentStat = (int) $locked->{$statKey};

            if ($mode === 'stat' && $currentStat >= $maxStat) {
                return back()->with('error', "🔒 LEVEL-PLAFOND BEREIKT! Je hebt het maximum van {$maxStat} {$session['label']} bereikt voor Level {$locked->level}. Kies 'Focus op Level (+{$session['level_xp']} XP)' om te stijgen naar Level ".($locked->level + 1).' en je plafond met +50 te verhogen!');
            }

            if ($locked->cash < $session['cost']) {
                return back()->with('error', 'Je hebt €'.number_format($session['cost'], 0, ',', '.').' nodig voor deze training. Je hebt onvoldoende contant geld.');
            }

            if (! $this->energy->consume($locked, $session['energy'])) {
                return back()->with('error', "Je hebt minimaal {$session['energy']} energie nodig voor deze trainingssessie!");
            }

            $locked->decrement('cash', $session['cost']);

            if ($mode === 'level') {
                $gain = 0;
                $xpEarned = (int) $session['level_xp'];
            } else {
                $gain = min((int) $session['gain'], max(0, $maxStat - $currentStat));
                $locked->{$statKey} = min($maxStat, $currentStat + $gain);
                $xpEarned = (int) $session['xp'];
            }

            $locked->save();

            TrainingLog::create([
                'character_id' => $locked->id,
                'stat' => $statKey,
                'stat_gain' => $gain,
                'energy_cost' => $session['energy'],
                'cash_cost' => $session['cost'],
                'trainer' => 'Barry Biceps',
            ]);

            $levelResult = $this->level->addXp($locked, $xpEarned);
            $newFocus = $locked->adjustColdBloodedPct(4);

            HalloweenService::record($locked, 'training', 1);

            $coldText = $newFocus < 100 ? " én +4% Koudbloedigheid (nu {$newFocus}%)" : '';
            $updatedMaxStat = $locked->getMaxStatForLevel();

            if ($mode === 'level') {
                $levelUpNotice = $levelResult['leveled_up']
                    ? " 🎉 Je bent gestegen naar Level {$locked->level} (nieuw stat-plafond: {$updatedMaxStat})!"
                    : '';

                return back()->with('success', "Training voltooid (Focus op Level)! +{$xpEarned} XP verdiend{$coldText}! -€".number_format($session['cost'], 0, ',', '.').".{$levelUpNotice}");
            }

            return back()->with('success', "Training voltooid! Je {$session['label']} stijgt met +{$gain} ({$locked->{$statKey}}/{$updatedMaxStat}){$coldText}! -€".number_format($session['cost'], 0, ',', '.').", +{$xpEarned} XP.");
        });
    }
}
