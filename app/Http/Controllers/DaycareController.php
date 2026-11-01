<?php

namespace App\Http\Controllers;

use App\Models\Character;
use App\Models\User;
use App\Services\CooldownService;
use App\Services\DailyBattleService;
use App\Services\EconomyService;
use App\Services\EnergyService;
use App\Services\LevelService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DaycareController extends Controller
{
    public function __construct(
        protected EconomyService $economyService,
        protected EnergyService $energyService,
        protected LevelService $levelService,
        protected CooldownService $cooldownService,
    ) {}

    /**
     * Show the dagbesteding & duties page.
     */
    public function index(): View
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;

        $this->energyService->refresh($character);

        $jobs = [
            1 => [
                'id' => 1,
                'name' => 'Aardappels jassen in de gaarkeuken',
                'description' => 'Schil bergen piepers voor het avondeten van de hele kliniek.',
                'energy' => 3,
                'min_cash' => 150,
                'max_cash' => 350,
                'xp' => 15,
                'stat_bonus' => '+1 Discipline',
                'icon' => 'fa-kitchen-set',
                'cooldown' => 60,
                'risk' => 'Geen (0% risico op isolatie)',
            ],
            2 => [
                'id' => 2,
                'name' => 'Gangen & recreatiezaal dweilen',
                'description' => 'Trek de vloeren strak en zorg dat er geen koffievlekken achterblijven.',
                'energy' => 6,
                'min_cash' => 400,
                'max_cash' => 850,
                'xp' => 25,
                'stat_bonus' => '+1 Conditie',
                'icon' => 'fa-broom',
                'cooldown' => 120,
                'risk' => 'Geen (0% risico)',
            ],
            3 => [
                'id' => 3,
                'name' => 'Waszakken sjouwen naar de stomerij',
                'description' => 'Til zware manden met gevangeniskleding naar de centrale wasserette.',
                'energy' => 10,
                'min_cash' => 900,
                'max_cash' => 1800,
                'xp' => 45,
                'stat_bonus' => '+1 Kracht',
                'icon' => 'fa-shirt',
                'cooldown' => 180,
                'risk' => 'Geen (0% risico)',
            ],
            4 => [
                'id' => 4,
                'name' => 'Houtbewerking & meubels repareren',
                'description' => 'Zaag, timmer en repareer houten tafels en krukjes in de kliniekwerkplaats.',
                'energy' => 14,
                'min_cash' => 1600,
                'max_cash' => 3200,
                'xp' => 70,
                'stat_bonus' => '+1 Intelligentie & 20% kans op Kluiscode',
                'icon' => 'fa-hammer',
                'cooldown' => 240,
                'risk' => 'Geen (0% risico)',
            ],
            5 => [
                'id' => 5,
                'name' => 'Binnentuin & plantsoenonderhoud',
                'description' => 'Schoffel de perken rond het recreatieterrein en snoei de struiken bij de omheining.',
                'energy' => 18,
                'min_cash' => 2500,
                'max_cash' => 5000,
                'xp' => 100,
                'stat_bonus' => '+1 Conditie & 25% kans op wisselgeld',
                'icon' => 'fa-seedling',
                'cooldown' => 300,
                'risk' => 'Geen (0% risico)',
            ],
        ];

        $activeCooldown = $this->cooldownService->getRemainingSeconds($character, 'dagbesteding');
        $daycareSkipCost = $this->cooldownService->getSkipCost('dagbesteding');

        return view('game.daycare.index', compact('character', 'jobs', 'activeCooldown', 'daycareSkipCost'));
    }

    /**
     * Perform a dagbesteding shift.
     */
    public function perform(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;

        if ($character->isImprisoned()) {
            return redirect()->route('prison')->with('error', 'In de isoleercel heb je geen toestemming voor dagbesteding!');
        }

        if ($character->isComatose()) {
            return back()->with('error', 'Je verkeert in Code Blauw (Hartstilstand)! Dien eerst een '.Character::REVIVE_SERUM_NAME.' toe.');
        }

        $jobId = (int) $request->input('job_id');
        $jobs = [
            1 => ['energy' => 3,  'min' => 150,  'max' => 350,  'xp' => 15,  'stat' => 'discipline', 'cooldown' => 60],
            2 => ['energy' => 6,  'min' => 400,  'max' => 850,  'xp' => 25,  'stat' => 'stamina',    'cooldown' => 120],
            3 => ['energy' => 10, 'min' => 900,  'max' => 1800, 'xp' => 45,  'stat' => 'strength',   'cooldown' => 180],
            4 => ['energy' => 14, 'min' => 1600, 'max' => 3200, 'xp' => 70,  'stat' => 'intelligence', 'cooldown' => 240],
            5 => ['energy' => 18, 'min' => 2500, 'max' => 5000, 'xp' => 100, 'stat' => 'stamina',    'cooldown' => 300],
        ];

        if (! isset($jobs[$jobId])) {
            return back()->with('error', 'Ongeldige werktaak geselecteerd.');
        }

        $cfg = $jobs[$jobId];

        return DB::transaction(function () use ($character, $jobId, $cfg): RedirectResponse {
            /** @var Character $lockedCharacter */
            $lockedCharacter = Character::where('id', $character->id)->lockForUpdate()->first();

            if ($this->cooldownService->isOnCooldown($lockedCharacter, 'dagbesteding')) {
                $seconds = $this->cooldownService->getRemainingSeconds($lockedCharacter, 'dagbesteding');

                return back()->with('error', "⏳ Werkpauze actief! De zaalwacht stuurt je even terug naar de koffietafel. Wacht nog {$seconds}s of reset direct via een sponsor advertentie.");
            }

            if (! $this->energyService->consume($lockedCharacter, $cfg['energy'])) {
                return back()->with('error', "Niet genoeg energie! Je hebt minstens {$cfg['energy']} energie nodig om deze taak uit te voeren.");
            }

            // Set cooldown for dagbesteding
            $this->cooldownService->setCooldown($lockedCharacter, 'dagbesteding', $cfg['cooldown']);

            // Earn money and stats
            $cashEarned = rand($cfg['min'], $cfg['max']);
            $this->economyService->addCash($lockedCharacter, $cashEarned);
            $this->levelService->addXp($lockedCharacter, $cfg['xp']);

            // Track stats
            $lockedCharacter->dagbesteding_shifts_completed = ($lockedCharacter->dagbesteding_shifts_completed ?? 0) + 1;
            $lockedCharacter->dagbesteding_earned_total = ($lockedCharacter->dagbesteding_earned_total ?? 0) + $cashEarned;

            $extraMessage = '';

            // Stat gain chance (50% on organic job, capped by level)
            if (rand(1, 100) <= 50) {
                $statName = $cfg['stat'];
                $maxStat = $lockedCharacter->getMaxStatForLevel();
                if (($lockedCharacter->$statName ?? 10) < $maxStat) {
                    $lockedCharacter->$statName = ($lockedCharacter->$statName ?? 10) + 1;
                    $extraMessage .= ' 💪 Jouw toewijding werpt vruchten af! +1 '.ucfirst($statName).'!';
                }
            }

            // Job 4 bonus chance: Found a locker code
            if ($jobId === 4 && rand(1, 100) <= 20) {
                $lockedCharacter->vault_tokens = ($lockedCharacter->vault_tokens ?? 0) + 1;
                $extraMessage .= ' 🔑 Tussen het zaagsel vond je een achtergelaten kluiscode (+1 Kluiscode)!';
            }

            // Job 5 bonus chance: Found extra cash in bushes
            if ($jobId === 5 && rand(1, 100) <= 25) {
                $bonusCash = rand(500, 1500);
                $this->economyService->addCash($lockedCharacter, $bonusCash);
                $extraMessage .= ' 🍂 In de struiken vond je een weggewaaid briefje van €'.number_format($bonusCash, 0, ',', '.').'!';
            }

            $lockedCharacter->save();

            app(DailyBattleService::class)->record($lockedCharacter, 'hustles', 1);

            return back()->with('success', 'Taak succesvol afgerond! Je ontving €'.number_format($cashEarned, 0, ',', '.')." loon en +{$cfg['xp']} XP.{$extraMessage}");
        });
    }
}
