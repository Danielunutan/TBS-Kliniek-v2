<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\CooldownService;
use App\Services\DailyBattleService;
use App\Services\EconomyService;
use App\Services\EnergyService;
use App\Services\HalloweenService;
use App\Services\LevelService;
use App\Services\PrisonService;
use App\Services\RandomDropService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class HustleController extends Controller
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
        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;
        $this->energy->refresh($character);

        $hustles = [
            1 => [
                'id' => 1,
                'name' => 'Bewaker afleiden bij de sluis',
                'description' => 'Maak een praatje over voetbal om wat kleingeld of sigaretten te bietsen.',
                'cost' => 5,
                'rate' => 55,
                'cooldown' => $this->cooldownService->calculateBySuccessRate(55),
                'cash_min' => 350,
                'cash_max' => 950,
                'xp' => 40,
                'respect' => 2,
                'risk' => 'Gemiddeld (45% mislukkingskans)',
            ],
            2 => [
                'id' => 2,
                'name' => 'Keukenpersoneel omkopen voor warme snacks',
                'description' => 'Ritsel versgebakken snacks die je direct kan doorverkopen.',
                'cost' => 10,
                'rate' => 45,
                'cooldown' => $this->cooldownService->calculateBySuccessRate(45),
                'cash_min' => 1000,
                'cash_max' => 2500,
                'xp' => 80,
                'respect' => 4,
                'risk' => 'Verhoogd (55% risico)',
            ],
            3 => [
                'id' => 3,
                'name' => 'Medicijnen achteroverdrukken bij de zuster',
                'description' => 'Grijp snel een doordrukstrip uit het mandje als zuster Ingrid wegkijkt.',
                'cost' => 15,
                'rate' => 35,
                'cooldown' => $this->cooldownService->calculateBySuccessRate(35),
                'cash_min' => 2800,
                'cash_max' => 6500,
                'xp' => 140,
                'respect' => 7,
                'risk' => 'Hoog (65% risico, kans op isoleercel)',
            ],
            4 => [
                'id' => 4,
                'name' => 'Gokpool organiseren in de recreatieruimte',
                'description' => 'Laat medegevangenen wedden op pingpong en pak het huisvoordeel.',
                'cost' => 20,
                'rate' => 25,
                'cooldown' => $this->cooldownService->calculateBySuccessRate(25),
                'cash_min' => 6000,
                'cash_max' => 14000,
                'xp' => 220,
                'respect' => 12,
                'risk' => 'Zeer Hoog (75% risico op betrapping)',
            ],
            5 => [
                'id' => 5,
                'name' => 'Kantine sigaretten kartels opzetten',
                'description' => 'Controleer de aanvoer van zware shag en vapes over alle drie de afdelingen.',
                'cost' => 24,
                'rate' => 18,
                'cooldown' => $this->cooldownService->calculateBySuccessRate(18),
                'cash_min' => 12000,
                'cash_max' => 28000,
                'xp' => 320,
                'respect' => 18,
                'risk' => 'Extreem Hoog (82% risico, direct isoleercel)',
            ],
            6 => [
                'id' => 6,
                'name' => 'Illegale smartphone verhuren',
                'description' => 'Verhuur een binnengesmokkelde prepaid telefoon voor 15 minuten per belbeurt.',
                'cost' => 28,
                'rate' => 14,
                'cooldown' => $this->cooldownService->calculateBySuccessRate(14),
                'cash_min' => 22000,
                'cash_max' => 55000,
                'xp' => 450,
                'respect' => 28,
                'risk' => 'Kliniekalarm (86% risico, zware straf)',
            ],
        ];

        return view('game.hustle.index', compact('character', 'hustles'));
    }

    public function attempt(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;

        if ($character->isImprisoned()) {
            return redirect()->route('prison')->with('error', 'Je zit in de isoleercel en kunt niet hosselen!');
        }

        if ($character->isComatose()) {
            $mins = ceil($character->getComaRemainingSeconds() / 60);

            return back()->with('error', "⚠️ Je ligt in Intensive Care Coma (nog {$mins} min)! Ontwaak direct via een sponsor advertentie.");
        }

        if ($this->cooldownService->isOnCooldown($character, 'hustle')) {
            $remaining = $this->cooldownService->getRemainingSeconds($character, 'hustle');
            $mins = ceil($remaining / 60);

            return back()->with('error', "⏳ Hossel cooldown actief! Wacht nog {$remaining}s (~{$mins} min) of reset direct via een sponsor advertentie.");
        }

        $validated = $request->validate([
            'hustle_id' => 'required|integer|between:1,6',
        ]);

        $hustles = [
            1 => ['cost' => 5,  'rate' => 55, 'cash_min' => 350,   'cash_max' => 950,   'xp' => 40,  'respect' => 2,  'jail_minutes' => 0],
            2 => ['cost' => 10, 'rate' => 45, 'cash_min' => 1000,  'cash_max' => 2500,  'xp' => 80,  'respect' => 4,  'jail_minutes' => 0],
            3 => ['cost' => 15, 'rate' => 35, 'cash_min' => 2800,  'cash_max' => 6500,  'xp' => 140, 'respect' => 7,  'jail_minutes' => 6],
            4 => ['cost' => 20, 'rate' => 25, 'cash_min' => 6000,  'cash_max' => 14000, 'xp' => 220, 'respect' => 12, 'jail_minutes' => 14],
            5 => ['cost' => 24, 'rate' => 18, 'cash_min' => 12000, 'cash_max' => 28000, 'xp' => 320, 'respect' => 18, 'jail_minutes' => 20],
            6 => ['cost' => 28, 'rate' => 14, 'cash_min' => 22000, 'cash_max' => 55000, 'xp' => 450, 'respect' => 28, 'jail_minutes' => 30],
        ];

        $hustle = $hustles[$validated['hustle_id']];

        if (! $this->energy->consume($character, $hustle['cost'])) {
            return back()->with('error', 'Niet genoeg energie! Je hebt minstens '.$hustle['cost'].' energie nodig.');
        }

        // Dynamic cooldown duration based on success rate (55% = 75s, 14% = 360s)
        $cooldownSeconds = $this->cooldownService->calculateBySuccessRate($hustle['rate']);
        $this->cooldownService->setCooldown($character, 'hustle', $cooldownSeconds);

        // Stat bonus: Charisma & Intelligence boost hustle success rate
        $statBonus = min(20, (int) floor(($character->getEffectiveCharisma() * 1.5 + $character->getEffectiveIntelligence()) / 15));
        $effectiveRate = min(85, $hustle['rate'] + $statBonus);

        $roll = rand(1, 100);

        if ($roll <= $effectiveRate) {
            // Charisma bonus on cash reward
            $cashMultiplier = 1 + min(0.30, ($character->getEffectiveCharisma() * 0.005));
            $cash = (int) round(rand($hustle['cash_min'], $hustle['cash_max']) * $cashMultiplier);

            if ($character->hasPerk('grotere_broekzakken')) {
                $cash = (int) round($cash * 1.10);
            }

            // Zwart geld contant!
            $this->economy->addCash($character, $cash);
            $this->level->addXp($character, $hustle['xp']);
            $character->addRespect($hustle['respect']);

            // 20% chance to gain +1 permanent Charisma from smooth hustling
            $statGainMsg = '';
            if (rand(1, 100) <= 20) {
                $character->charisma = ($character->charisma ?? 10) + 1;
                $statGainMsg = ' 😎 Gladde praatjes beloond: +1 Charisma!';
            }
            // 15% chance to drop illegal bullets from a successful hustle
            $bulletDrop = (rand(1, 100) <= 15) ? rand(5, 12) : 0;
            $bulletTxt = '';
            if ($bulletDrop > 0) {
                $character->bullets = ($character->bullets ?? 0) + $bulletDrop;
                $bulletTxt = " +{$bulletDrop} Kogels 🔫";
            }

            $character->save();

            app(DailyBattleService::class)->record($character, 'hustles', 1);
            HalloweenService::record($character, 'hustle', 1);
            HalloweenService::record($character, 'cash_earned', $cash);

            $dropMsg = RandomDropService::roll($character);
            $successMsg = 'Hossel gelukt! Je scoorde €'.number_format($cash, 0, ',', '.')." contant, +{$hustle['xp']} XP en +{$hustle['respect']} respect!{$bulletTxt}{$statGainMsg}";
            if ($dropMsg) {
                $successMsg .= ' '.$dropMsg;
            }

            return back()->with('success', $successMsg);
        }

        // Check if high stamina allows escaping isolation
        $escapeChance = min(35, (int) floor($character->getEffectiveStamina() / 15));
        if ($hustle['jail_minutes'] > 0 && rand(1, 100) <= 60) {
            if (rand(1, 100) <= $escapeChance) {
                return back()->with('error', 'Hossel mislukt! De zaalwacht sloeg alarm, maar dankzij je snelle conditie wist je hem af te schudden en ontliep je de isolatie!');
            }

            $jailReduction = min(45, (int) floor($character->getEffectiveDiscipline() / 5));
            $jailSeconds = max(50, random_int(80, 150) - $jailReduction);
            $this->prison->jailSeconds($character, $jailSeconds, 'Gepakt tijdens hosselen.');

            return redirect()->route('prison')->with('error', "Poging mislukt! {$jailSeconds} sec vast in isolatie! (Gepakt door bewakers)");
        }

        return back()->with('error', 'Hossel mislukt! Iemand waarschuwde de zaalwacht en je moest snel maken dat je wegkwam.');
    }
}
