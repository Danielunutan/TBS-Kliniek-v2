<?php

namespace App\Http\Controllers;

use App\Models\Character;
use App\Models\User;
use App\Services\CooldownService;
use App\Services\DailyBattleService;
use App\Services\EconomyService;
use App\Services\EnergyService;
use App\Services\EventCashHuntService;
use App\Services\EventHustleChallengeService;
use App\Services\HalloweenService;
use App\Services\LevelService;
use App\Services\PrisonService;
use App\Services\RandomDropService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class HustleController extends Controller
{
    public const HUSTLES = [
        1 => [
            'id' => 1,
            'name' => 'Bewaker afleiden bij de sluis',
            'tier_name' => 'Routine Klus',
            'description' => 'Maak een gezellig praatje over voetbal om wat kleingeld of sigaretten te bietsen.',
            'cost' => 5,
            'rate' => 85,
            'cooldown' => 45,
            'cash_min' => 400,
            'cash_max' => 1100,
            'xp' => 45,
            'respect' => 2,
            'jail_chance' => 0,
            'jail_minutes' => 0,
            'risk_label' => 'Zeer Veilig (0% celkans)',
            'risk_level' => 'low',
            'icon' => 'fa-comments',
        ],
        2 => [
            'id' => 2,
            'name' => 'Keukenpersoneel omkopen voor snacks',
            'tier_name' => 'Kantine Deal',
            'description' => 'Ritsel versgebakken frikandelbroodjes en snacks die je direct kan doorverkopen.',
            'cost' => 8,
            'rate' => 75,
            'cooldown' => 75,
            'cash_min' => 1200,
            'cash_max' => 2800,
            'xp' => 90,
            'respect' => 4,
            'jail_chance' => 0,
            'jail_minutes' => 0,
            'risk_label' => 'Laag Risico (0% celkans)',
            'risk_level' => 'low',
            'icon' => 'fa-burger',
        ],
        3 => [
            'id' => 3,
            'name' => 'Medicijnen achteroverdrukken bij de zuster',
            'tier_name' => 'Farmacie Smokkel',
            'description' => 'Grijp snel een doordrukstrip temazepam als zuster Ingrid haar dossier invult.',
            'cost' => 12,
            'rate' => 65,
            'cooldown' => 120,
            'cash_min' => 3200,
            'cash_max' => 7500,
            'xp' => 150,
            'respect' => 7,
            'jail_chance' => 20,
            'jail_minutes' => 3,
            'risk_label' => 'Gemiddeld (20% celkans)',
            'risk_level' => 'medium',
            'icon' => 'fa-pills',
        ],
        4 => [
            'id' => 4,
            'name' => 'Gokpool organiseren in de recreatieruimte',
            'tier_name' => 'Recreatie Bookie',
            'description' => 'Laat medepatiënten wedden op pingpong en darten met 15% huisvoordeel.',
            'cost' => 16,
            'rate' => 55,
            'cooldown' => 150,
            'cash_min' => 7500,
            'cash_max' => 16500,
            'xp' => 240,
            'respect' => 12,
            'jail_chance' => 25,
            'jail_minutes' => 5,
            'risk_label' => 'Verhoogd (25% celkans)',
            'risk_level' => 'high',
            'icon' => 'fa-dice',
        ],
        5 => [
            'id' => 5,
            'name' => 'Kantine sigaretten kartel opzetten',
            'tier_name' => 'Shag Kartel',
            'description' => 'Controleer de aanvoer van zware shag, vapes en aanstekers over alle afdelingen.',
            'cost' => 20,
            'rate' => 45,
            'cooldown' => 180,
            'cash_min' => 15000,
            'cash_max' => 32000,
            'xp' => 350,
            'respect' => 18,
            'jail_chance' => 30,
            'jail_minutes' => 8,
            'risk_label' => 'Hoog Risico (30% celkans)',
            'risk_level' => 'high',
            'icon' => 'fa-smoking',
        ],
        6 => [
            'id' => 6,
            'name' => 'Illegale smartphone lijn verhuren',
            'tier_name' => 'Kliniek Telecom',
            'description' => 'Verhuur een binnengesmokkelde mini-smartphone voor 15 minuten per belbeurt.',
            'cost' => 24,
            'rate' => 38,
            'cooldown' => 210,
            'cash_min' => 26000,
            'cash_max' => 65000,
            'xp' => 500,
            'respect' => 28,
            'jail_chance' => 35,
            'jail_minutes' => 12,
            'risk_label' => 'Extreem Risico (35% celkans)',
            'risk_level' => 'extreme',
            'icon' => 'fa-mobile-screen',
        ],
    ];

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

        $statBonus = min(25, (int) floor(($character->getEffectiveCharisma() * 1.5 + $character->getEffectiveIntelligence() * 1.2) / 7));
        $activeVehicle = $character->activeVehicle();
        $motorBonus = ($activeVehicle && $activeVehicle->vehicle && $activeVehicle->vehicle->type === 'motor') ? 8 : 0;

        $hustles = [];
        foreach (self::HUSTLES as $id => $h) {
            $effectiveRate = min(98, $h['rate'] + $statBonus + $motorBonus);
            $hustles[$id] = array_merge($h, [
                'effective_rate' => $effectiveRate,
                'stat_bonus' => $statBonus,
                'motor_bonus' => $motorBonus,
            ]);
        }

        return view('game.hustle.index', compact('character', 'hustles', 'statBonus', 'motorBonus', 'activeVehicle'));
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

            return back()->with('error', "⚠️ Je ligt in Intensive Care Coma (nog {$mins} min)! Ontwaak direct met een Epinefrine-Ampul of Sterren ⭐.");
        }

        $validated = $request->validate([
            'hustle_id' => 'required|integer|between:1,6',
        ]);

        $hustle = self::HUSTLES[$validated['hustle_id']] ?? null;
        if (! $hustle) {
            return back()->with('error', 'Ongeldige hossel gekozen.');
        }

        return DB::transaction(function () use ($character, $hustle): RedirectResponse {
            /** @var Character $lockedCharacter */
            $lockedCharacter = Character::where('id', $character->id)->lockForUpdate()->first();

            if ($this->cooldownService->isOnCooldown($lockedCharacter, 'hustle')) {
                $remaining = $this->cooldownService->getRemainingSeconds($lockedCharacter, 'hustle');
                $mins = ceil($remaining / 60);

                return back()->with('error', "⏳ Hossel cooldown actief! Wacht nog {$remaining}s (~{$mins} min) of reset direct met Sterren ⭐.");
            }

            if (! $this->energy->consume($lockedCharacter, $hustle['cost'])) {
                return back()->with('error', 'Niet genoeg energie! Je hebt minstens '.$hustle['cost'].' energie nodig.');
            }

            // Set cooldown for hustle
            $this->cooldownService->setCooldown($lockedCharacter, 'hustle', $hustle['cooldown']);

            // Stat bonus: Charisma & Intelligence boost hustle success rate
            $statBonus = min(25, (int) floor(($lockedCharacter->getEffectiveCharisma() * 1.5 + $lockedCharacter->getEffectiveIntelligence() * 1.2) / 7));
            $activeVehicle = $lockedCharacter->activeVehicle();
            $motorBonus = ($activeVehicle && $activeVehicle->vehicle && $activeVehicle->vehicle->type === 'motor') ? 8 : 0;
            $effectiveRate = min(98, $hustle['rate'] + $statBonus + $motorBonus);

            $roll = rand(1, 100);

            if ($roll <= $effectiveRate) {
                // Charisma bonus on cash reward
                $cashMultiplier = 1 + min(0.35, ($lockedCharacter->getEffectiveCharisma() * 0.006));
                $cash = (int) round(rand($hustle['cash_min'], $hustle['cash_max']) * $cashMultiplier);

                if ($lockedCharacter->hasPerk('grotere_broekzakken')) {
                    $cash = (int) round($cash * 1.10);
                }

                if ($motorBonus > 0) {
                    $cash = (int) round($cash * 1.10); // +10% fast scooter/motor delivery bonus!
                }

                // Zwart geld contant!
                $this->economy->addCash($lockedCharacter, $cash);
                $this->level->addXp($lockedCharacter, $hustle['xp']);
                $lockedCharacter->addRespect($hustle['respect']);

                // 25% chance to gain +1 permanent Charisma from smooth hustling (capped at level maximum)
                $statGainMsg = '';
                $maxStatForLevel = $lockedCharacter->getMaxStatForLevel();
                if (rand(1, 100) <= 25 && (int) ($lockedCharacter->charisma ?? 10) < $maxStatForLevel) {
                    $lockedCharacter->charisma = min($maxStatForLevel, ($lockedCharacter->charisma ?? 10) + 1);
                    $statGainMsg = ' 😎 Gladde babbel beloond: +1 Charisma!';
                }
                // 20% chance to drop illegal bullets from a successful hustle
                $bulletDrop = (rand(1, 100) <= 20) ? rand(5, 15) : 0;
                $bulletTxt = '';
                if ($bulletDrop > 0) {
                    $lockedCharacter->bullets = ($lockedCharacter->bullets ?? 0) + $bulletDrop;
                    $bulletTxt = " +{$bulletDrop} Kogels 🔫";
                }

                $lockedCharacter->save();

                EventCashHuntService::record($lockedCharacter, $cash, 'hustle');
                EventHustleChallengeService::record($lockedCharacter, (int) $hustle['id']);
                app(DailyBattleService::class)->record($lockedCharacter, 'hustles', 1);
                HalloweenService::record($lockedCharacter, 'hustle', 1);
                HalloweenService::record($lockedCharacter, 'cash_earned', $cash);

                $dropMsg = RandomDropService::roll($lockedCharacter);
                $successMsg = '🎉 Hossel geslaagd! Je scoorde €'.number_format($cash, 0, ',', '.')." contant, +{$hustle['xp']} XP en +{$hustle['respect']} respect!{$bulletTxt}{$statGainMsg}";
                if ($dropMsg) {
                    $successMsg .= ' '.$dropMsg;
                }

                return back()->with('success', $successMsg);
            }

            // Failure handling
            // Check if high stamina allows escaping isolation
            $escapeChance = min(40, (int) floor($lockedCharacter->getEffectiveStamina() / 12));
            $jailChance = $hustle['jail_chance'] ?? 0;

            if ($jailChance > 0 && rand(1, 100) <= $jailChance) {
                if (rand(1, 100) <= $escapeChance) {
                    return back()->with('error', '⚠️ Hossel mislukt! De zaalwacht sloeg alarm, maar dankzij je snelle conditie wist je hem af te schudden en ontliep je de isolatie!');
                }

                $jailReduction = min(45, (int) floor($lockedCharacter->getEffectiveDiscipline() / 5));
                $jailSeconds = max(45, random_int(60, 110) - $jailReduction);

                // Clear hustle cooldown if sent to jail so they don't get double punished
                $this->cooldownService->clearCooldown($lockedCharacter, 'hustle');
                $this->prison->jailSeconds($lockedCharacter, $jailSeconds, 'Gepakt tijdens hosselen.');

                return redirect()->route('prison')->with('error', "🚨 Betrapt tijdens het hosselen! {$jailSeconds} sec in de isoleercel. Koop jezelf direct vrij met Sterren ⭐ of waag een uitbraak!");
            }

            return back()->with('error', 'Hossel mislukt! Iemand waarschuwde de zaalwacht en je moest snel maken dat je wegkwam.');
        });
    }
}
