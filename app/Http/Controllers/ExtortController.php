<?php

namespace App\Http\Controllers;

use App\Models\Character;
use App\Services\CooldownService;
use App\Services\EconomyService;
use App\Services\EnergyService;
use App\Services\LevelService;
use App\Services\PrisonService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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

        $levelMultiplier = min(3.5, 1 + max(0, ($character->level - 1) * 0.04));

        $baseTargets = [
            ['id' => 1, 'name' => 'Kliniekdirecteur-assistent', 'desc' => 'Chantage met geheime cliëntendossiers. Gevaarlijk maar lucratief.', 'energy' => 10, 'min' => 400, 'max' => 1200, 'rate' => 26, 'cooldown' => $this->cooldownService->calculateBySuccessRate(26), 'xp' => 95, 'icon' => 'fa-user-tie', 'min_level' => 1],
            ['id' => 2, 'name' => 'Caféhouder in de kantine', 'desc' => 'Eis een deel van de dagomzet en sigarettenvoorraad af.', 'energy' => 12, 'min' => 450, 'max' => 1200, 'rate' => 38, 'cooldown' => $this->cooldownService->calculateBySuccessRate(38), 'xp' => 85, 'icon' => 'fa-mug-hot', 'min_level' => 1],
            ['id' => 3, 'name' => 'Jonge verslaafde', 'desc' => 'Betaalt snel uit angst maar vertelt ook snel.', 'energy' => 14, 'min' => 600, 'max' => 1800, 'rate' => 34, 'cooldown' => $this->cooldownService->calculateBySuccessRate(34), 'xp' => 110, 'icon' => 'fa-user-injured', 'min_level' => 1],
            ['id' => 4, 'name' => 'Rijke patiënt (Paviljoen 6)', 'desc' => 'Grote beloning van een verwende erfgenaam in de kliniek.', 'energy' => 18, 'min' => 1500, 'max' => 4000, 'rate' => 22, 'cooldown' => $this->cooldownService->calculateBySuccessRate(22), 'xp' => 180, 'icon' => 'fa-gem', 'min_level' => 1],
            ['id' => 5, 'name' => 'Kliniekarts & Psychiater', 'desc' => 'Dwing privileges en medicijnvoorraden af met harde chantage.', 'energy' => 22, 'min' => 2500, 'max' => 6500, 'rate' => 15, 'cooldown' => $this->cooldownService->calculateBySuccessRate(15), 'xp' => 240, 'icon' => 'fa-user-doctor', 'min_level' => 1],
            ['id' => 6, 'name' => 'Hoofd Beveiliging', 'desc' => 'Het allerhoogste risico. Corrupte deals onder druk zetten.', 'energy' => 28, 'min' => 4000, 'max' => 11000, 'rate' => 10, 'cooldown' => $this->cooldownService->calculateBySuccessRate(10), 'xp' => 350, 'icon' => 'fa-shield-halved', 'min_level' => 1],
            ['id' => 7, 'name' => 'Externe Witwas-Handelaar', 'desc' => 'Dwing de corrupte leverancier van kliniekgoederen een deel van zijn zwarte omzet af.', 'energy' => 32, 'min' => 8000, 'max' => 22000, 'rate' => 8, 'cooldown' => $this->cooldownService->calculateBySuccessRate(8), 'xp' => 500, 'icon' => 'fa-briefcase', 'min_level' => 15],
            ['id' => 8, 'name' => 'Inspecteur Gezondheidszorg', 'desc' => 'Het ultieme doelwit. Chanteer de visiterende rijksinspecteur met compromitterend bewijs.', 'energy' => 36, 'min' => 15000, 'max' => 45000, 'rate' => 6, 'cooldown' => $this->cooldownService->calculateBySuccessRate(6), 'xp' => 800, 'icon' => 'fa-building-shield', 'min_level' => 25],
        ];

        $targets = array_map(function (array $t) use ($levelMultiplier): array {
            $t['min'] = (int) round($t['min'] * $levelMultiplier);
            $t['max'] = (int) round($t['max'] * $levelMultiplier);
            $t['xp'] = (int) round($t['xp'] * $levelMultiplier);

            return $t;
        }, $baseTargets);

        $extortSkipCost = $this->cooldownService->getSkipCost('extort');

        return view('game.extort.index', compact('character', 'targets', 'extortSkipCost', 'levelMultiplier'));
    }

    public function extort(Request $request): RedirectResponse
    {
        $character = Auth::user()->character;

        if ($character->isImprisoned()) {
            return redirect()->route('prison')->with('error', 'Je kunt niet afpersen vanuit de isoleercel!');
        }

        if ($character->isComatose()) {
            return back()->with('error', 'Je verkeert in Code Blauw (Hartstilstand)! Dien eerst een '.Character::REVIVE_SERUM_NAME.' toe.');
        }

        $validated = $request->validate([
            'target_id' => ['required', 'integer', 'in:1,2,3,4,5,6,7,8'],
        ]);

        $config = [
            1 => ['energy' => 10, 'min' => 400,   'max' => 1200,  'rate' => 26, 'xp' => 95,  'jail' => true,  'jail_min' => 10, 'min_level' => 1],
            2 => ['energy' => 12, 'min' => 450,   'max' => 1200,  'rate' => 38, 'xp' => 85,  'jail' => false, 'jail_min' => 0,  'min_level' => 1],
            3 => ['energy' => 14, 'min' => 600,   'max' => 1800,  'rate' => 34, 'xp' => 110, 'jail' => true,  'jail_min' => 6,  'min_level' => 1],
            4 => ['energy' => 18, 'min' => 1500,  'max' => 4000,  'rate' => 22, 'xp' => 180, 'jail' => true,  'jail_min' => 15, 'min_level' => 1],
            5 => ['energy' => 22, 'min' => 2500,  'max' => 6500,  'rate' => 15, 'xp' => 240, 'jail' => true,  'jail_min' => 20, 'min_level' => 1],
            6 => ['energy' => 28, 'min' => 4000,  'max' => 11000, 'rate' => 10, 'xp' => 350, 'jail' => true,  'jail_min' => 30, 'min_level' => 1],
            7 => ['energy' => 32, 'min' => 8000,  'max' => 22000, 'rate' => 8,  'xp' => 500, 'jail' => true,  'jail_min' => 35, 'min_level' => 15],
            8 => ['energy' => 36, 'min' => 15000, 'max' => 45000, 'rate' => 6,  'xp' => 800, 'jail' => true,  'jail_min' => 45, 'min_level' => 25],
        ];

        $target = $config[$validated['target_id']];

        if ($character->level < ($target['min_level'] ?? 1)) {
            return back()->with('error', "Voor dit doelwit is minimaal Level {$target['min_level']} vereist!");
        }

        return DB::transaction(function () use ($character, $target, $validated): RedirectResponse {
            /** @var Character $lockedCharacter */
            $lockedCharacter = Character::where('id', $character->id)->lockForUpdate()->first();

            if ($this->cooldownService->isOnCooldown($lockedCharacter, 'extort')) {
                $remaining = $this->cooldownService->getRemainingSeconds($lockedCharacter, 'extort');
                $mins = ceil($remaining / 60);

                return back()->with('error', "⏳ Afpersen cooldown actief! Wacht nog {$remaining}s (~{$mins} min) of reset direct via een sponsor advertentie.");
            }

            if (! $this->energy->consume($lockedCharacter, $target['energy'])) {
                return back()->with('error', "Je hebt minimaal {$target['energy']} energie nodig om af te persen!");
            }

            $cooldownSeconds = $this->cooldownService->calculateBySuccessRate($target['rate']);
            $this->cooldownService->setCooldown($lockedCharacter, 'extort', $cooldownSeconds);

            $levelMultiplier = min(3.5, 1 + max(0, ($lockedCharacter->level - 1) * 0.04));
            $scaledMin = (int) round($target['min'] * $levelMultiplier);
            $scaledMax = (int) round($target['max'] * $levelMultiplier);
            $scaledXp = (int) round($target['xp'] * $levelMultiplier);

            $roll = rand(1, 100);
            if ($roll <= $target['rate']) {
                $cash = rand($scaledMin, $scaledMax);
                // Zwart geld contant!
                $this->economy->addCash($lockedCharacter, $cash);
                $this->level->addXp($lockedCharacter, $scaledXp);
                $respectGain = ($validated['target_id'] >= 7) ? 4 : 2;
                $lockedCharacter->addRespect($respectGain);
                $lockedCharacter->save();

                $bonusTag = ($levelMultiplier > 1.0) ? ' (inclusief level bonus)' : '';

                return back()->with('success', 'Afpersing geslaagd! Het doelwit betaalt €'.number_format($cash, 0, ',', '.')." contant uit angst. +{$scaledXp} XP{$bonusTag}.");
            }

            if ($target['jail'] && rand(1, 100) <= 60) {
                $jailSeconds = random_int(100, 180);
                $this->prison->jailSeconds($lockedCharacter, $jailSeconds, 'Betrapt bij afpersing.');

                return redirect()->route('prison')->with('error', "Poging mislukt! {$jailSeconds} sec vast in isolatie! (Doelwit sloeg alarm!)");
            }

            return back()->with('error', 'Afpersing mislukt! Het doelwit weigerde te buigen en dreigde alarm te slaan.');
        });
    }
}
