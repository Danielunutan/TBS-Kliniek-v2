<?php

namespace App\Http\Controllers;

use App\Models\Character;
use App\Services\CooldownService;
use App\Services\EconomyService;
use App\Services\EnergyService;
use App\Services\HalloweenService;
use App\Services\LevelService;
use App\Services\PrisonService;
use App\Services\RandomDropService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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

        $levelMultiplier = min(3.5, 1 + max(0, ($character->level - 1) * 0.04));

        $baseLockers = [
            ['id' => 1, 'name' => 'Roestig Kluisje (Kantine)', 'desc' => 'Oud slot, simpel te kraken met een paperclip.', 'energy' => 5, 'min' => 100, 'max' => 400, 'rate' => 50, 'cooldown' => $this->cooldownService->calculateBySuccessRate(50), 'xp' => 30, 'icon' => 'fa-lock-open', 'min_level' => 1],
            ['id' => 2, 'name' => 'Personeelskluisje (Wachthok)', 'desc' => 'Iets beter slot. Meer inhoud ook.', 'energy' => 10, 'min' => 300, 'max' => 900, 'rate' => 38, 'cooldown' => $this->cooldownService->calculateBySuccessRate(38), 'xp' => 65, 'icon' => 'fa-key', 'min_level' => 1],
            ['id' => 3, 'name' => 'Beveiligd Kluisje (Verpleging)', 'desc' => 'Digitaal slot. Vereist wat techniek.', 'energy' => 15, 'min' => 700, 'max' => 2000, 'rate' => 28, 'cooldown' => $this->cooldownService->calculateBySuccessRate(28), 'xp' => 115, 'icon' => 'fa-microchip', 'min_level' => 1],
            ['id' => 4, 'name' => 'Kluisje Directeur (Kantoor)', 'desc' => 'Zwaarst beveiligd. Meeste geld, flink risico.', 'energy' => 20, 'min' => 1500, 'max' => 4500, 'rate' => 18, 'cooldown' => $this->cooldownService->calculateBySuccessRate(18), 'xp' => 210, 'icon' => 'fa-briefcase', 'min_level' => 1],
            ['id' => 5, 'name' => 'Behandelkamer Medicijnkluis', 'desc' => 'Gepantserd slot vol verdovende middelen en cash.', 'energy' => 24, 'min' => 2500, 'max' => 6500, 'rate' => 14, 'cooldown' => $this->cooldownService->calculateBySuccessRate(14), 'xp' => 280, 'icon' => 'fa-capsules', 'min_level' => 1],
            ['id' => 6, 'name' => 'Centrale Geldkluis (Administratie)', 'desc' => 'Kliniek hoofdkas. Extreem bewaakt en gecodeerd.', 'energy' => 28, 'min' => 4500, 'max' => 12500, 'rate' => 10, 'cooldown' => $this->cooldownService->calculateBySuccessRate(10), 'xp' => 400, 'icon' => 'fa-vault', 'min_level' => 1],
            ['id' => 7, 'name' => 'Gepantserde Directietresor', 'desc' => 'Zwaarbeveiligde directiekluis met biometrisch slot en reserves.', 'energy' => 32, 'min' => 8500, 'max' => 24000, 'rate' => 8, 'cooldown' => $this->cooldownService->calculateBySuccessRate(8), 'xp' => 600, 'icon' => 'fa-shield', 'min_level' => 15],
            ['id' => 8, 'name' => 'Ondergrondse Ministeriekluis', 'desc' => 'Ongekraakte stalen kluis in de fundering vol zwart geld en smokkelbuit.', 'energy' => 36, 'min' => 16000, 'max' => 50000, 'rate' => 5, 'cooldown' => $this->cooldownService->calculateBySuccessRate(5), 'xp' => 950, 'icon' => 'fa-dungeon', 'min_level' => 25],
        ];

        $lockers = array_map(function (array $l) use ($levelMultiplier): array {
            $l['min'] = (int) round($l['min'] * $levelMultiplier);
            $l['max'] = (int) round($l['max'] * $levelMultiplier);
            $l['xp'] = (int) round($l['xp'] * $levelMultiplier);

            return $l;
        }, $baseLockers);

        return view('game.lockers.index', compact('character', 'lockers', 'levelMultiplier'));
    }

    public function crack(Request $request): RedirectResponse
    {
        $character = Auth::user()->character;

        if ($character->isImprisoned()) {
            return redirect()->route('prison')->with('error', 'Vanuit de isoleercel kun je geen kluisjes kraken!');
        }

        if ($character->isComatose()) {
            return back()->with('error', 'Je verkeert in Code Blauw (Hartstilstand)! Dien eerst een '.Character::REVIVE_SERUM_NAME.' toe.');
        }

        $validated = $request->validate([
            'locker_id' => ['required', 'integer', 'in:1,2,3,4,5,6,7,8'],
        ]);

        $config = [
            1 => ['energy' => 5,  'min' => 100,   'max' => 400,   'rate' => 50, 'xp' => 30,  'jail' => false, 'jail_min' => 0,  'min_level' => 1],
            2 => ['energy' => 10, 'min' => 300,   'max' => 900,   'rate' => 38, 'xp' => 65,  'jail' => false, 'jail_min' => 0,  'min_level' => 1],
            3 => ['energy' => 15, 'min' => 700,   'max' => 2000,  'rate' => 28, 'xp' => 115, 'jail' => true,  'jail_min' => 10, 'min_level' => 1],
            4 => ['energy' => 20, 'min' => 1500,  'max' => 4500,  'rate' => 18, 'xp' => 210, 'jail' => true,  'jail_min' => 20, 'min_level' => 1],
            5 => ['energy' => 24, 'min' => 2500,  'max' => 6500,  'rate' => 14, 'xp' => 280, 'jail' => true,  'jail_min' => 25, 'min_level' => 1],
            6 => ['energy' => 28, 'min' => 4500,  'max' => 12500, 'rate' => 10, 'xp' => 400, 'jail' => true,  'jail_min' => 35, 'min_level' => 1],
            7 => ['energy' => 32, 'min' => 8500,  'max' => 24000, 'rate' => 8,  'xp' => 600, 'jail' => true,  'jail_min' => 40, 'min_level' => 15],
            8 => ['energy' => 36, 'min' => 16000, 'max' => 50000, 'rate' => 5,  'xp' => 950, 'jail' => true,  'jail_min' => 50, 'min_level' => 25],
        ];

        $locker = $config[$validated['locker_id']];

        if ($character->level < ($locker['min_level'] ?? 1)) {
            return back()->with('error', "Voor dit kluisje is minimaal Level {$locker['min_level']} vereist!");
        }

        return DB::transaction(function () use ($character, $locker, $validated): RedirectResponse {
            /** @var Character $lockedCharacter */
            $lockedCharacter = Character::where('id', $character->id)->lockForUpdate()->first();

            if ($this->cooldownService->isOnCooldown($lockedCharacter, 'locker')) {
                $remaining = $this->cooldownService->getRemainingSeconds($lockedCharacter, 'locker');
                $mins = ceil($remaining / 60);

                return back()->with('error', "⏳ Kluisjes cooldown actief! Wacht nog {$remaining}s (~{$mins} min) of reset direct met Sterren ⭐.");
            }

            if (! $this->energy->consume($lockedCharacter, $locker['energy'])) {
                return back()->with('error', "Je hebt minimaal {$locker['energy']} energie nodig om dit kluisje te kraken!");
            }

            // Dynamic cooldown duration based on success rate
            $cooldownSeconds = $this->cooldownService->calculateBySuccessRate($locker['rate']);
            $this->cooldownService->setCooldown($lockedCharacter, 'locker', $cooldownSeconds);

            $levelMultiplier = min(3.5, 1 + max(0, ($lockedCharacter->level - 1) * 0.04));
            $scaledMin = (int) round($locker['min'] * $levelMultiplier);
            $scaledMax = (int) round($locker['max'] * $levelMultiplier);
            $scaledXp = (int) round($locker['xp'] * $levelMultiplier);

            $roll = rand(1, 100);
            if ($roll <= $locker['rate']) {
                $cash = rand($scaledMin, $scaledMax);
                // Zwart geld contant!
                $this->economy->addCash($lockedCharacter, $cash);
                $this->level->addXp($lockedCharacter, $scaledXp);
                $respectGain = ($validated['locker_id'] >= 7) ? 3 : 1;
                $lockedCharacter->addRespect($respectGain);
                $lockedCharacter->save();

                HalloweenService::record($lockedCharacter, 'vault', 1);

                $dropMsg = RandomDropService::roll($lockedCharacter);
                $bonusTag = ($levelMultiplier > 1.0) ? ' (inclusief level bonus)' : '';
                $successMsg = 'Kluisje opengeknald! Je vindt €'.number_format($cash, 0, ',', '.')." contant erin! +{$scaledXp} XP{$bonusTag}.";
                if ($dropMsg) {
                    $successMsg .= ' '.$dropMsg;
                }

                return back()->with('success', $successMsg);
            }

            if ($locker['jail'] && rand(1, 100) <= 60) {
                $jailSeconds = random_int(90, 150);
                $this->prison->jailSeconds($lockedCharacter, $jailSeconds, 'Betrapt bij het kraken van een kluisje.');

                return redirect()->route('prison')->with('error', "Poging mislukt! {$jailSeconds} sec vast in isolatie! (Alarm bij kluisjes)");
            }

            $dmg = rand(3, 8);
            $lockedCharacter->health = max(1, $lockedCharacter->health - $dmg);
            $lockedCharacter->save();

            return back()->with('error', "Kluisje kraken mislukt! Het slot gaf een elektrische schok. -{$dmg} HP.");
        });
    }
}
