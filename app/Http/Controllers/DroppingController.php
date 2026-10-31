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

        $tennisCooldown = $this->cooldownService->getRemainingSeconds($character, 'dropping_tennisball');
        $droneCooldown = $this->cooldownService->getRemainingSeconds($character, 'dropping_drone');

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
            'recentDroppings',
            'totalIntercepted',
            'totalCashMade'
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

        if ($this->cooldownService->isOnCooldown($character, 'dropping_tennisball')) {
            $remaining = ceil($this->cooldownService->getRemainingSeconds($character, 'dropping_tennisball') / 60).' minuten';

            return back()->with('error', "De luchtplaats wordt scherp in de gaten gehouden. Wacht nog {$remaining} tot de volgende worp.");
        }

        $energyCost = 15;
        if ($character->energy < $energyCost) {
            return back()->with('error', "Je hebt minimaal {$energyCost} energie nodig om op het juiste moment naar het hek te sprinten!");
        }

        $character->energy -= $energyCost;
        $character->save();

        // 10% kans dat de bewaarder je pakt
        $caught = mt_rand(1, 100) <= 10;
        if ($caught) {
            $this->cooldownService->setCooldown($character, 'dropping_tennisball');
            $this->prisonService->jail($character, 5, 'Betrapt bij het oprapen van een met tape verzwaarde tennisbal bij het buitenhek.');

            SmuggleDropping::create([
                'character_id' => $character->id,
                'type' => 'tennisball',
                'loot_summary' => 'Betrapt door zaalwacht! Tennisbal in beslag genomen.',
                'cash_gained' => 0,
                'xp_gained' => 5,
                'caught' => true,
            ]);

            return redirect()->route('prison')->with('error', 'ALARM! Bewaker De Jong zag je sprinten en greep je net voordat je de bal kon verstoppen. 5 minuten isoleercel!');
        }

        // Buit genereren
        $cash = mt_rand(250, 1200);
        $xp = mt_rand(20, 45);
        $lootItems = [];

        $this->economy->addCash($character, $cash);
        $this->level->addXp($character, $xp);

        // 70% kans op sigaretten
        if (mt_rand(1, 100) <= 70) {
            $sigAmount = mt_rand(2, 5);
            $sigItem = Item::where('slug', 'sigaretten')->first();
            if ($sigItem) {
                $ci = CharacterItem::firstOrCreate(['character_id' => $character->id, 'item_id' => $sigItem->id], ['quantity' => 0]);
                $ci->increment('quantity', $sigAmount);
                $lootItems[] = "{$sigAmount}x Sigaretten";
            }
        }

        // 35% kans op ritalin
        if (mt_rand(1, 100) <= 35) {
            $ritAmount = mt_rand(1, 2);
            $ritItem = Item::where('slug', 'ritalin')->first();
            if ($ritItem) {
                $ci = CharacterItem::firstOrCreate(['character_id' => $character->id, 'item_id' => $ritItem->id], ['quantity' => 0]);
                $ci->increment('quantity', $ritAmount);
                $lootItems[] = "{$ritAmount}x Ritalin";
            }
        }

        // 10% bonus op strip oxazepam
        if (mt_rand(1, 100) <= 10) {
            $stripItem = Item::where('slug', 'strip-oxazepam')->first();
            if ($stripItem) {
                $ci = CharacterItem::firstOrCreate(['character_id' => $character->id, 'item_id' => $stripItem->id], ['quantity' => 0]);
                $ci->increment('quantity', 1);
                $lootItems[] = '1x Strip Oxazepam';
            }
        }

        $lootStr = '€'.number_format($cash, 0, ',', '.').($lootItems ? ', '.implode(', ', $lootItems) : '');

        SmuggleDropping::create([
            'character_id' => $character->id,
            'type' => 'tennisball',
            'loot_summary' => $lootStr,
            'cash_gained' => $cash,
            'xp_gained' => $xp,
            'caught' => false,
        ]);

        $this->cooldownService->setCooldown($character, 'dropping_tennisball');

        return back()->with('success', "RAAK! Je dook als een snoek naar de heg en griste de opengeknipte tennisbal mee naar binnen. Buit: {$lootStr} (+{$xp} XP)!");
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

        if ($this->cooldownService->isOnCooldown($character, 'dropping_drone')) {
            $remaining = ceil($this->cooldownService->getRemainingSeconds($character, 'dropping_drone') / 60).' minuten';

            return back()->with('error', "De dronepiloot moet zijn accu's opladen en van locatie wisselen. Wacht nog {$remaining}.");
        }

        $droneCost = 500;
        if ($character->cash < $droneCost) {
            return back()->with('error', 'Je hebt minimaal €'.number_format($droneCost, 0, ',', '.').' contant nodig om de dronepiloot via prepaid crypto vooruit te betalen!');
        }

        $energyCost = 25;
        if ($character->energy < $energyCost) {
            return back()->with('error', "Je hebt minimaal {$energyCost} energie nodig voor de nachtelijke coördinatie!");
        }

        $character->cash -= $droneCost;
        $character->energy -= $energyCost;
        $character->save();

        // 12% kans dat zoeklichten de drone onderscheppen
        $caught = mt_rand(1, 100) <= 12;
        if ($caught) {
            $this->cooldownService->setCooldown($character, 'dropping_drone');
            SmuggleDropping::create([
                'character_id' => $character->id,
                'type' => 'drone',
                'loot_summary' => 'Onderschept door zoeklichten en vogelweringsnetten!',
                'cash_gained' => 0,
                'xp_gained' => 10,
                'caught' => true,
            ]);

            return back()->with('error', 'MISLUKT! Een schijnwerper van de wachttoren verblindde de dronepiloot. De drone crashte op het buitenste prikkeldraad. Je bent je €500 aanbetaling kwijt!');
        }

        // Succesvolle high-tier drop
        $cash = mt_rand(1800, 4800);
        $xp = mt_rand(60, 110);
        $lootItems = [];

        $this->economy->addCash($character, $cash);
        $this->level->addXp($character, $xp);

        // Grote hoeveelheden sigaretten & ritalin
        $sigAmount = mt_rand(6, 12);
        $sigItem = Item::where('slug', 'sigaretten')->first();
        if ($sigItem) {
            $ci = CharacterItem::firstOrCreate(['character_id' => $character->id, 'item_id' => $sigItem->id], ['quantity' => 0]);
            $ci->increment('quantity', $sigAmount);
            $lootItems[] = "{$sigAmount}x Sigaretten";
        }

        $ritAmount = mt_rand(3, 6);
        $ritItem = Item::where('slug', 'ritalin')->first();
        if ($ritItem) {
            $ci = CharacterItem::firstOrCreate(['character_id' => $character->id, 'item_id' => $ritItem->id], ['quantity' => 0]);
            $ci->increment('quantity', $ritAmount);
            $lootItems[] = "{$ritAmount}x Ritalin";
        }

        // 40% kans op Smokkelpakketje of Zware Medicatie
        $specialRoll = mt_rand(1, 100);
        if ($specialRoll <= 25) {
            $pakket = Item::where('slug', 'smokkelpakketje')->first();
            if ($pakket) {
                $ci = CharacterItem::firstOrCreate(['character_id' => $character->id, 'item_id' => $pakket->id], ['quantity' => 0]);
                $ci->increment('quantity', 1);
                $lootItems[] = '1x Smokkelpakketje 📦';
            }
        } elseif ($specialRoll <= 50) {
            $halo = Item::where('slug', 'haloperidol-forte')->first();
            if ($halo) {
                $ci = CharacterItem::firstOrCreate(['character_id' => $character->id, 'item_id' => $halo->id], ['quantity' => 0]);
                $ci->increment('quantity', 1);
                $lootItems[] = '1x Haloperidol Forte 💥';
            }
        }

        $lootStr = '€'.number_format($cash, 0, ',', '.').($lootItems ? ', '.implode(', ', $lootItems) : '');

        SmuggleDropping::create([
            'character_id' => $character->id,
            'type' => 'drone',
            'loot_summary' => $lootStr,
            'cash_gained' => $cash,
            'xp_gained' => $xp,
            'caught' => false,
        ]);

        $this->cooldownService->setCooldown($character, 'dropping_drone');

        return back()->with('success', "🎯 PRECISIEDROPPING GESLAAGD! De zwarte drone loste geruisloos zijn waterdichte pakket recht door het luchtrooster van jouw afdeling. Buit: {$lootStr} (+{$xp} XP)!");
    }
}
