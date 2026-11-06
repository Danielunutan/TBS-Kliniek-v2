<?php

namespace App\Http\Controllers;

use App\Models\Character;
use App\Models\CharacterItem;
use App\Models\Item;
use App\Models\SmuggleDropping;
use App\Models\User;
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

class DroppingController extends Controller
{
    public function __construct(
        protected EconomyService $economy,
        protected EnergyService $energy,
        protected LevelService $level,
        protected CooldownService $cooldownService,
        protected PrisonService $prisonService,
    ) {}

    public function index(): View
    {
        /** @var User $user */
        $user = Auth::user();
        /** @var Character $character */
        $character = $user->character;
        $this->energy->refresh($character);

        $levelMultiplier = min(3.5, 1 + max(0, ($character->level - 1) * 0.04));

        $tennisCooldown = $this->cooldownService->getRemainingSeconds($character, 'dropping_tennisball');
        $droneCooldown = $this->cooldownService->getRemainingSeconds($character, 'dropping_drone');
        $helicopterCooldown = $this->cooldownService->getRemainingSeconds($character, 'dropping_helicopter');

        $recentDroppings = SmuggleDropping::where('character_id', $character->id)
            ->latest()
            ->limit(10)
            ->get();

        $totalIntercepted = SmuggleDropping::where('character_id', $character->id)->where('caught', false)->count();
        $totalCashMade = SmuggleDropping::where('character_id', $character->id)->where('caught', false)->sum('cash_gained');

        return view('game.droppings.index', compact(
            'character',
            'tennisCooldown',
            'droneCooldown',
            'helicopterCooldown',
            'recentDroppings',
            'totalIntercepted',
            'totalCashMade',
            'levelMultiplier'
        ));
    }

    public function catchTennisball(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        /** @var Character $character */
        $character = $user->character;
        $this->energy->refresh($character);

        if ($character->isImprisoned()) {
            return redirect()->route('prison')->with('error', 'Je zit in de isoleercel en kunt niet op de luchtplaats komen!');
        }

        if ($character->isComatose()) {
            return back()->with('error', 'Je verkeert in Code Blauw (Hartstilstand)! Dien eerst een '.Character::REVIVE_SERUM_NAME.' toe.');
        }

        return DB::transaction(function () use ($character): RedirectResponse {
            /** @var Character $lockedCharacter */
            $lockedCharacter = Character::where('id', $character->id)->lockForUpdate()->first();

            if ($this->cooldownService->isOnCooldown($lockedCharacter, 'dropping_tennisball')) {
                $remaining = ceil($this->cooldownService->getRemainingSeconds($lockedCharacter, 'dropping_tennisball') / 60).' minuten';

                return back()->with('error', "De luchtplaats wordt scherp in de gaten gehouden. Wacht nog {$remaining} tot de volgende worp.");
            }

            $energyCost = 15;
            if (! $this->energy->consume($lockedCharacter, $energyCost)) {
                return back()->with('error', "Je hebt minimaal {$energyCost} energie nodig om op het juiste moment naar het hek te sprinten!");
            }

            $this->cooldownService->setCooldown($lockedCharacter, 'dropping_tennisball');

            // 10% kans dat de bewaarder je pakt
            $caught = mt_rand(1, 100) <= 10;
            if ($caught) {
                $this->level->addXp($lockedCharacter, 5);
                $this->prisonService->jail($lockedCharacter, 5, 'Betrapt bij het oprapen van een met tape verzwaarde tennisbal bij het buitenhek.');

                SmuggleDropping::create([
                    'character_id' => $lockedCharacter->id,
                    'type' => 'tennisball',
                    'loot_summary' => 'Betrapt door zaalwacht! Tennisbal in beslag genomen.',
                    'cash_gained' => 0,
                    'xp_gained' => 5,
                    'caught' => true,
                ]);

                return redirect()->route('prison')->with('error', 'ALARM! Bewaker De Jong zag je sprinten en greep je net voordat je de bal kon verstoppen. 5 minuten isoleercel!');
            }

            // Buit genereren
            $levelMultiplier = min(3.5, 1 + max(0, ($lockedCharacter->level - 1) * 0.04));
            $cash = (int) round(mt_rand(250, 1200) * $levelMultiplier);
            $xp = (int) round(mt_rand(20, 45) * $levelMultiplier);
            $lootItems = [];

            $this->economy->addCash($lockedCharacter, $cash);
            $this->level->addXp($lockedCharacter, $xp);

            // 70% kans op sigaretten
            if (mt_rand(1, 100) <= 70) {
                $sigAmount = mt_rand(2, 5);
                $sigItem = Item::where('slug', 'sigaretten')->first();
                if ($sigItem) {
                    $ci = CharacterItem::firstOrCreate(['character_id' => $lockedCharacter->id, 'item_id' => $sigItem->id], ['quantity' => 0]);
                    $ci->increment('quantity', $sigAmount);
                    $lootItems[] = "{$sigAmount}x Sigaretten";
                }
            }

            // 35% kans op ritalin
            if (mt_rand(1, 100) <= 35) {
                $ritAmount = mt_rand(1, 2);
                $ritItem = Item::where('slug', 'ritalin')->first();
                if ($ritItem) {
                    $ci = CharacterItem::firstOrCreate(['character_id' => $lockedCharacter->id, 'item_id' => $ritItem->id], ['quantity' => 0]);
                    $ci->increment('quantity', $ritAmount);
                    $lootItems[] = "{$ritAmount}x Ritalin";
                }
            }

            // 10% bonus op strip oxazepam
            if (mt_rand(1, 100) <= 10) {
                $stripItem = Item::where('slug', 'strip-oxazepam')->first();
                if ($stripItem) {
                    $ci = CharacterItem::firstOrCreate(['character_id' => $lockedCharacter->id, 'item_id' => $stripItem->id], ['quantity' => 0]);
                    $ci->increment('quantity', 1);
                    $lootItems[] = '1x Strip Oxazepam';
                }
            }

            $lootStr = '€'.number_format($cash, 0, ',', '.').($lootItems ? ', '.implode(', ', $lootItems) : '');

            SmuggleDropping::create([
                'character_id' => $lockedCharacter->id,
                'type' => 'tennisball',
                'loot_summary' => $lootStr,
                'cash_gained' => $cash,
                'xp_gained' => $xp,
                'caught' => false,
            ]);

            $bonusTag = ($levelMultiplier > 1.0) ? ' (inclusief level bonus)' : '';

            return back()->with('success', "RAAK! Je dook als een snoek naar de heg en griste de opengeknipte tennisbal mee naar binnen. Buit: {$lootStr} (+{$xp} XP{$bonusTag})!");
        });
    }

    public function orderDrone(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        /** @var Character $character */
        $character = $user->character;
        $this->energy->refresh($character);

        if ($character->level < 3) {
            return back()->with('error', 'Nachtelijke drone-droppings vereisen minimaal Level 3 en contacten buiten de muren!');
        }

        if ($character->isImprisoned()) {
            return redirect()->route('prison')->with('error', 'Je zit in de isoleercel en kunt niet bij het dakraam komen!');
        }

        if ($character->isComatose()) {
            return back()->with('error', 'Je verkeert in Code Blauw (Hartstilstand)! Dien eerst een '.Character::REVIVE_SERUM_NAME.' toe.');
        }

        return DB::transaction(function () use ($character): RedirectResponse {
            /** @var Character $lockedCharacter */
            $lockedCharacter = Character::where('id', $character->id)->lockForUpdate()->first();

            if ($this->cooldownService->isOnCooldown($lockedCharacter, 'dropping_drone')) {
                $remaining = ceil($this->cooldownService->getRemainingSeconds($lockedCharacter, 'dropping_drone') / 60).' minuten';

                return back()->with('error', "De dronepiloot moet zijn accu's opladen en van locatie wisselen. Wacht nog {$remaining}.");
            }

            $droneCost = 500;
            if ($lockedCharacter->cash < $droneCost) {
                return back()->with('error', 'Je hebt minimaal €'.number_format($droneCost, 0, ',', '.').' contant nodig om de dronepiloot via prepaid crypto vooruit te betalen!');
            }

            $energyCost = 25;
            if ($lockedCharacter->energy < $energyCost) {
                return back()->with('error', "Je hebt minimaal {$energyCost} energie nodig voor de nachtelijke coördinatie!");
            }

            if (! $this->economy->deductCash($lockedCharacter, $droneCost)) {
                return back()->with('error', 'Je hebt minimaal €'.number_format($droneCost, 0, ',', '.').' contant nodig om de dronepiloot via prepaid crypto vooruit te betalen!');
            }

            if (! $this->energy->consume($lockedCharacter, $energyCost)) {
                $this->economy->addCash($lockedCharacter, $droneCost);

                return back()->with('error', "Je hebt minimaal {$energyCost} energie nodig voor de nachtelijke coördinatie!");
            }

            $this->cooldownService->setCooldown($lockedCharacter, 'dropping_drone');

            // 12% kans dat zoeklichten de drone onderscheppen
            $caught = mt_rand(1, 100) <= 12;
            if ($caught) {
                $this->level->addXp($lockedCharacter, 10);

                SmuggleDropping::create([
                    'character_id' => $lockedCharacter->id,
                    'type' => 'drone',
                    'loot_summary' => 'Onderschept door zoeklichten en vogelweringsnetten!',
                    'cash_gained' => 0,
                    'xp_gained' => 10,
                    'caught' => true,
                ]);

                return back()->with('error', 'MISLUKT! Een schijnwerper van de wachttoren verblindde de dronepiloot. De drone crashte op het buitenste prikkeldraad. Je bent je €500 aanbetaling kwijt!');
            }

            // Succesvolle high-tier drop
            $levelMultiplier = min(3.5, 1 + max(0, ($lockedCharacter->level - 1) * 0.04));
            $cash = (int) round(mt_rand(1800, 4800) * $levelMultiplier);
            $xp = (int) round(mt_rand(60, 110) * $levelMultiplier);
            $lootItems = [];

            $this->economy->addCash($lockedCharacter, $cash);
            $this->level->addXp($lockedCharacter, $xp);

            // Grote hoeveelheden sigaretten & ritalin
            $sigAmount = mt_rand(6, 12);
            $sigItem = Item::where('slug', 'sigaretten')->first();
            if ($sigItem) {
                $ci = CharacterItem::firstOrCreate(['character_id' => $lockedCharacter->id, 'item_id' => $sigItem->id], ['quantity' => 0]);
                $ci->increment('quantity', $sigAmount);
                $lootItems[] = "{$sigAmount}x Sigaretten";
            }

            $ritAmount = mt_rand(3, 6);
            $ritItem = Item::where('slug', 'ritalin')->first();
            if ($ritItem) {
                $ci = CharacterItem::firstOrCreate(['character_id' => $lockedCharacter->id, 'item_id' => $ritItem->id], ['quantity' => 0]);
                $ci->increment('quantity', $ritAmount);
                $lootItems[] = "{$ritAmount}x Ritalin";
            }

            // 40% kans op Smokkelpakketje of Zware Medicatie
            $specialRoll = mt_rand(1, 100);
            if ($specialRoll <= 25) {
                $pakket = Item::where('slug', 'smokkelpakketje')->first();
                if ($pakket) {
                    $ci = CharacterItem::firstOrCreate(['character_id' => $lockedCharacter->id, 'item_id' => $pakket->id], ['quantity' => 0]);
                    $ci->increment('quantity', 1);
                    $lootItems[] = '1x Smokkelpakketje 📦';
                }
            } elseif ($specialRoll <= 50) {
                $halo = Item::where('slug', 'haloperidol-forte')->first();
                if ($halo) {
                    $ci = CharacterItem::firstOrCreate(['character_id' => $lockedCharacter->id, 'item_id' => $halo->id], ['quantity' => 0]);
                    $ci->increment('quantity', 1);
                    $lootItems[] = '1x Haloperidol Forte 💥';
                }
            }

            $lootStr = '€'.number_format($cash, 0, ',', '.').($lootItems ? ', '.implode(', ', $lootItems) : '');

            SmuggleDropping::create([
                'character_id' => $lockedCharacter->id,
                'type' => 'drone',
                'loot_summary' => $lootStr,
                'cash_gained' => $cash,
                'xp_gained' => $xp,
                'caught' => false,
            ]);

            $bonusTag = ($levelMultiplier > 1.0) ? ' (inclusief level bonus)' : '';

            return back()->with('success', "🎯 PRECISIEDROPPING GESLAAGD! De zwarte drone loste geruisloos zijn waterdichte pakket recht door het luchtrooster van jouw afdeling. Buit: {$lootStr} (+{$xp} XP{$bonusTag})!");
        });
    }

    public function orderHelicopter(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        /** @var Character $character */
        $character = $user->character;
        $this->energy->refresh($character);

        if ($character->level < 15) {
            return back()->with('error', 'Nachtelijke helikopter-droppings vereisen minimaal Level 15 en connecties met het zware kartel!');
        }

        if ($character->isImprisoned()) {
            return redirect()->route('prison')->with('error', 'Je zit in de isoleercel en kunt niet bij het dak komen!');
        }

        if ($character->isComatose()) {
            return back()->with('error', 'Je verkeert in Code Blauw (Hartstilstand)! Dien eerst een '.Character::REVIVE_SERUM_NAME.' toe.');
        }

        return DB::transaction(function () use ($character): RedirectResponse {
            /** @var Character $lockedCharacter */
            $lockedCharacter = Character::where('id', $character->id)->lockForUpdate()->first();

            if ($this->cooldownService->isOnCooldown($lockedCharacter, 'dropping_helicopter')) {
                $remaining = ceil($this->cooldownService->getRemainingSeconds($lockedCharacter, 'dropping_helicopter') / 60).' minuten';

                return back()->with('error', "De helikopterpiloot moet zijn vliegroute aanpassen en bijtanken. Wacht nog {$remaining}.");
            }

            $heliCost = 2500;
            if ($lockedCharacter->cash < $heliCost) {
                return back()->with('error', 'Je hebt minimaal €'.number_format($heliCost, 0, ',', '.').' contant nodig voor de helikopterpiloot en radaraftasting!');
            }

            $energyCost = 35;
            if ($lockedCharacter->energy < $energyCost) {
                return back()->with('error', "Je hebt minimaal {$energyCost} energie nodig voor de zware nachtoperatie!");
            }

            if (! $this->economy->deductCash($lockedCharacter, $heliCost)) {
                return back()->with('error', 'Je hebt minimaal €'.number_format($heliCost, 0, ',', '.').' contant nodig voor de piloot!');
            }

            if (! $this->energy->consume($lockedCharacter, $energyCost)) {
                $this->economy->addCash($lockedCharacter, $heliCost);

                return back()->with('error', "Je hebt minimaal {$energyCost} energie nodig voor de zware nachtoperatie!");
            }

            $this->cooldownService->setCooldown($lockedCharacter, 'dropping_helicopter');

            // 15% kans dat radar/nachtkijkers de drop onderscheppen
            $caught = mt_rand(1, 100) <= 15;
            if ($caught) {
                $this->level->addXp($lockedCharacter, 25);

                SmuggleDropping::create([
                    'character_id' => $lockedCharacter->id,
                    'type' => 'helicopter',
                    'loot_summary' => 'Onderschept door luchtverdedigingsradar en zoeklichten!',
                    'cash_gained' => 0,
                    'xp_gained' => 25,
                    'caught' => true,
                ]);

                return back()->with('error', 'ALARM! Een mobiele radarwagen van de kliniek detecteerde de naderende helikopter. De piloot moest direct afbreken en dumpte de lading in het bos. Je bent je €2.500 kwijt!');
            }

            $levelMultiplier = min(3.5, 1 + max(0, ($lockedCharacter->level - 1) * 0.04));
            $cash = (int) round(mt_rand(6000, 16000) * $levelMultiplier);
            $xp = (int) round(mt_rand(200, 380) * $levelMultiplier);
            $lootItems = [];

            $this->economy->addCash($lockedCharacter, $cash);
            $this->level->addXp($lockedCharacter, $xp);

            // Grote smokkelbuit
            $sigAmount = mt_rand(12, 25);
            $sigItem = Item::where('slug', 'sigaretten')->first();
            if ($sigItem) {
                $ci = CharacterItem::firstOrCreate(['character_id' => $lockedCharacter->id, 'item_id' => $sigItem->id], ['quantity' => 0]);
                $ci->increment('quantity', $sigAmount);
                $lootItems[] = "{$sigAmount}x Sigaretten";
            }

            $ritAmount = mt_rand(5, 10);
            $ritItem = Item::where('slug', 'ritalin')->first();
            if ($ritItem) {
                $ci = CharacterItem::firstOrCreate(['character_id' => $lockedCharacter->id, 'item_id' => $ritItem->id], ['quantity' => 0]);
                $ci->increment('quantity', $ritAmount);
                $lootItems[] = "{$ritAmount}x Ritalin";
            }

            // Kans op oxazepam & kluiscodes
            if (mt_rand(1, 100) <= 50) {
                $oxItem = Item::where('slug', 'strip-oxazepam')->first();
                if ($oxItem) {
                    $ci = CharacterItem::firstOrCreate(['character_id' => $lockedCharacter->id, 'item_id' => $oxItem->id], ['quantity' => 0]);
                    $ci->increment('quantity', 2);
                    $lootItems[] = '2x Strip Oxazepam';
                }
            }

            if (mt_rand(1, 100) <= 35) {
                $lockedCharacter->vault_tokens = ($lockedCharacter->vault_tokens ?? 0) + 2;
                $lootItems[] = '+2 Kluiscodes 🔑';
            }

            $lockedCharacter->save();

            $lootStr = '€'.number_format($cash, 0, ',', '.').($lootItems ? ', '.implode(', ', $lootItems) : '');

            SmuggleDropping::create([
                'character_id' => $lockedCharacter->id,
                'type' => 'helicopter',
                'loot_summary' => $lootStr,
                'cash_gained' => $cash,
                'xp_gained' => $xp,
                'caught' => false,
            ]);

            $bonusTag = ($levelMultiplier > 1.0) ? ' (inclusief level bonus)' : '';

            return back()->with('success', "🚁 STEALTH-HELIKOPTER DROPPING GESLAAGD! De zwarte heli vloog geruisloos over de bomen en gooide een zware militaire waterdichte plunjezak op het dak. Buit: {$lootStr} (+{$xp} XP{$bonusTag})!");
        });
    }
}
