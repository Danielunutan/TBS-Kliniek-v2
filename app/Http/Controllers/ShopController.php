<?php

namespace App\Http\Controllers;

use App\Models\Character;
use App\Models\CharacterItem;
use App\Models\GameMessage;
use App\Models\Item;
use App\Models\User;
use App\Models\VaultCode;
use App\Services\DailyBattleService;
use App\Services\EconomyService;
use App\Services\EnergyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ShopController extends Controller
{
    public const BUNDLES = [
        'kliniek_starter' => [
            'id' => 'kliniek_starter',
            'name' => 'Kliniek Starterspakket 🏥',
            'category' => 'smokkel',
            'cost_stars' => 75,
            'codes' => 10,
            'koffers' => 3,
            'cash' => 150000,
            'masks' => 10,
        ],
        'smokkel_buit' => [
            'id' => 'smokkel_buit',
            'name' => 'Smokkel & Buitkoffer Pack 📦',
            'category' => 'smokkel',
            'cost_stars' => 150,
            'codes' => 15,
            'koffers' => 6,
            'cash' => 500000,
            'masks' => 25,
        ],
        'afdelingsbaas_deal' => [
            'id' => 'afdelingsbaas_deal',
            'name' => 'Afdelingsbaas Buitdeal 💼',
            'category' => 'kluisbuit',
            'cost_stars' => 250,
            'codes' => 25,
            'koffers' => 10,
            'cash' => 1500000,
            'masks' => 40,
        ],
        'syndicaat_pack' => [
            'id' => 'syndicaat_pack',
            'name' => 'Kliniek Maffia Syndicaat Pack 👑',
            'category' => 'syndicaat',
            'cost_stars' => 500,
            'codes' => 50,
            'koffers' => 25,
            'cash' => 5000000,
            'masks' => 100,
        ],
        'koffer_booster_xl' => [
            'id' => 'koffer_booster_xl',
            'name' => 'Zwarte Markt Imperium XL 💉',
            'category' => 'kluisbuit',
            'cost_stars' => 1000,
            'codes' => 120,
            'koffers' => 60,
            'cash' => 15000000,
            'masks' => 250,
        ],
        'eindbaas_kluis_xxl' => [
            'id' => 'eindbaas_kluis_xxl',
            'name' => 'TBS Kliniek Eindbaas Kluis XXL 🏆',
            'category' => 'syndicaat',
            'cost_stars' => 2500,
            'codes' => 350,
            'koffers' => 150,
            'cash' => 50000000,
            'masks' => 600,
        ],
    ];

    public const BUNDLE_ALIASES = [
        'deluluknaller' => 'kliniek_starter',
        'snel_schaamteloos' => 'smokkel_buit',
        'big_bucks' => 'afdelingsbaas_deal',
        'sprinkle_sprinkle' => 'syndicaat_pack',
        'rijk_roekeloos_xxl' => 'eindbaas_kluis_xxl',
    ];

    public function __construct(
        protected EconomyService $economyService,
        protected EnergyService $energyService,
    ) {}

    /**
     * Show shop, bundles and inventory (Matching Screenshot 3).
     */
    public function index(): View
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;
        $this->energyService->refresh($character);

        $items = Item::where('is_buyable', true)->get();
        $inventory = CharacterItem::where('character_id', $character->id)
            ->where('quantity', '>', 0)
            ->with('item')
            ->get();

        $bundles = self::BUNDLES;
        $otherPatients = Character::where('id', '!=', $character->id)
            ->select(['id', 'name', 'level', 'respect'])
            ->orderBy('name')
            ->take(150)
            ->get();
        $topRankings = Character::getTopRankings(10);

        return view('game.shop', compact(
            'character',
            'items',
            'inventory',
            'bundles',
            'otherPatients',
            'topRankings'
        ));
    }

    /**
     * Buy an item with regular cash.
     */
    public function buy(Request $request, Item $item): RedirectResponse|JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;

        if (! $item->is_buyable || $item->slug === 'smokkelpakketje') {
            $msg = 'Dit item kan niet via de reguliere winkel worden gekocht!';
            if ($request->expectsJson() || $request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }

            return back()->with('shop_tab', 'items')->with('error', $msg);
        }

        $quantity = max(1, min(50, (int) $request->input('quantity', 1)));

        // Charisma discount up to 15% (1% per 10 charisma)
        $discountPct = min(15, (int) floor($character->getEffectiveCharisma() / 10));
        $unitPrice = $discountPct > 0 ? (int) round($item->price_kc * (1 - ($discountPct / 100))) : $item->price_kc;
        $totalCost = $unitPrice * $quantity;

        // Stat boost items, combat equipment and masks do not take storage in inventory
        $isDirectDelivery = in_array($item->slug, [
            'bivakmuts-masker',
            'boksbeugel',
            'stalen-neus-kisten',
            'eiwitshake-bodybuilder',
            'multivitaminen-focus',
            'haloperidol-forte',
        ], true) || ($item->attack_power > 0) || ($item->defense_power > 0);

        if (! $isDirectDelivery && ! $character->canAddStorageItem($quantity)) {
            $storageUsed = $character->getStorageUsed();
            $storageCap = $character->getStorageCapacity();
            $storageRemaining = max(0, $storageCap - $storageUsed);

            $errorMsg = $storageRemaining > 0
                ? "Je opbergplaats heeft nog maar {$storageRemaining} vrije plek(ken) ({$storageUsed}/{$storageCap})! Je kunt maximaal {$storageRemaining}x van dit voorwerp kopen."
                : "Je opbergplaats zit helemaal vol ({$storageUsed}/{$storageCap} plaatsen)! Verkoop of gebruik spullen in je Depot, level up of word VIP voor +50 extra voorraadplekken.";

            if ($request->expectsJson() || $request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMsg,
                    'storage_used' => $storageUsed,
                    'storage_capacity' => $storageCap,
                    'storage_remaining' => $storageRemaining,
                ], 422);
            }

            return back()
                ->with('shop_tab', 'items')
                ->with('error', $errorMsg);
        }

        $discountNote = $discountPct > 0 ? " (inclusief {$discountPct}% Charisma-korting! 😎)" : '';
        $formatted = number_format($totalCost, 0, ',', '.');
        $ownedCount = 0;
        $successMsg = '';

        $purchaseSuccess = DB::transaction(function () use ($character, $totalCost, $item, $quantity, $discountNote, $formatted, &$ownedCount, &$successMsg): bool {
            /** @var Character $locked */
            $locked = Character::where('id', $character->id)->lockForUpdate()->first();
            $totalAvailable = (int) ($locked->cash + $locked->bank);

            if ($totalAvailable < $totalCost) {
                return false;
            }

            // Pay from cash first; if cash is insufficient, cover difference from bank
            if ($locked->cash >= $totalCost) {
                $locked->cash -= $totalCost;
            } else {
                $remaining = $totalCost - $locked->cash;
                $locked->cash = 0;
                $locked->bank = max(0, $locked->bank - $remaining);
            }
            $locked->save();

            // Deliver item under lock - direct stats do NOT go into inventory
            if ($item->slug === 'bivakmuts-masker') {
                $locked->masks = ($locked->masks ?? 0) + $quantity;
                $locked->save();
                $ownedCount = (int) $locked->masks;
                $successMsg = "Je hebt {$quantity}x {$item->name} gekocht voor €{$formatted}{$discountNote}! Direct +{$quantity} Masker(s) 🎭 toegevoegd aan je voorraad voor Zakkenrollen (totaal: {$locked->masks}).";
            } elseif ($item->slug === 'smokkelpakketje') {
                $locked->unopened_bags = ($locked->unopened_bags ?? 0) + $quantity;
                $locked->save();
                $ownedCount = (int) $locked->unopened_bags;
                $successMsg = "Je hebt {$quantity}x {$item->name} gekocht voor €{$formatted}{$discountNote}! Direct +{$quantity} Pakket(ten) 📦 toegevoegd aan Koffers & Buit (totaal: {$locked->unopened_bags}).";
            } elseif ($item->slug === 'boksbeugel') {
                $gain = 5 * $quantity;
                $locked->strength = ($locked->strength ?? 10) + $gain;
                $locked->save();
                $ownedCount = (int) $locked->strength;
                $successMsg = "Je hebt {$quantity}x {$item->name} gekocht voor €{$formatted}{$discountNote}! Direct +{$gain} Kracht 💪 toegekend aan je karakter (nu: {$locked->strength})!";
            } elseif ($item->slug === 'stalen-neus-kisten') {
                $gain = 6 * $quantity;
                $locked->defense = ($locked->defense ?? 10) + $gain;
                $locked->save();
                $ownedCount = (int) $locked->defense;
                $successMsg = "Je hebt {$quantity}x {$item->name} gekocht voor €{$formatted}{$discountNote}! Direct +{$gain} Bescherming & Verdediging 🛡️ toegekend aan je karakter (nu: {$locked->defense})!";
            } elseif ($item->slug === 'eiwitshake-bodybuilder') {
                $gain = 1 * $quantity;
                $locked->strength = ($locked->strength ?? 10) + $gain;
                $locked->save();
                $ownedCount = (int) $locked->strength;
                $successMsg = "Je hebt {$quantity}x {$item->name} gekocht voor €{$formatted}{$discountNote}! Direct +{$gain} Permanente Kracht 💪 toegekend aan je karakter (nu: {$locked->strength})!";
            } elseif ($item->slug === 'multivitaminen-focus') {
                $gain = 1 * $quantity;
                $locked->stamina = ($locked->stamina ?? 10) + $gain;
                $locked->save();
                $ownedCount = (int) $locked->stamina;
                $successMsg = "Je hebt {$quantity}x {$item->name} gekocht voor €{$formatted}{$discountNote}! Direct +{$gain} Permanente Conditie 🏃 toegekend aan je karakter (nu: {$locked->stamina})!";
            } elseif ($item->slug === 'haloperidol-forte') {
                $gainStr = 12 * $quantity;
                $gainDef = 8 * $quantity;
                $gainHp = 60 * $quantity;
                $locked->strength = ($locked->strength ?? 10) + $gainStr;
                $locked->defense = ($locked->defense ?? 10) + $gainDef;
                $locked->health = min($locked->max_health, $locked->health + $gainHp);
                $locked->save();
                $ownedCount = (int) $locked->strength;
                $successMsg = "Je hebt {$quantity}x {$item->name} gekocht voor €{$formatted}{$discountNote}! Direct +{$gainStr} Kracht, +{$gainDef} Bescherming & +{$gainHp} HP 💥 toegekend aan je karakter!";
            } elseif (($item->attack_power > 0) || ($item->defense_power > 0)) {
                $gainStr = ($item->attack_power ?? 0) * $quantity;
                $gainDef = ($item->defense_power ?? 0) * $quantity;
                if ($gainStr > 0) {
                    $locked->strength = ($locked->strength ?? 10) + $gainStr;
                }
                if ($gainDef > 0) {
                    $locked->defense = ($locked->defense ?? 10) + $gainDef;
                }
                $locked->save();
                $ownedCount = (int) $locked->strength;
                $successMsg = "Je hebt {$quantity}x {$item->name} gekocht voor €{$formatted}{$discountNote}! Directe combat stats toegekend aan je karakter!";
            } else {
                $inv = CharacterItem::firstOrNew([
                    'character_id' => $locked->id,
                    'item_id' => $item->id,
                ]);
                $inv->quantity = ($inv->quantity ?? 0) + $quantity;
                $inv->save();
                $ownedCount = (int) $inv->quantity;
                $successMsg = "Je hebt {$quantity}x {$item->name} gekocht voor €{$formatted}{$discountNote}!";
            }

            // Sync original model instance
            $character->cash = $locked->cash;
            $character->bank = $locked->bank;
            $character->masks = $locked->masks;
            $character->unopened_bags = $locked->unopened_bags;
            $character->strength = $locked->strength;
            $character->defense = $locked->defense;
            $character->stamina = $locked->stamina;
            $character->health = $locked->health;

            return true;
        });

        if (! $purchaseSuccess) {
            $errorMsg = "Niet genoeg geld! {$quantity}x {$item->name} kost €{$formatted}.";

            if ($request->expectsJson() || $request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMsg,
                ], 422);
            }

            return back()
                ->with('shop_tab', 'items')
                ->with('error', $errorMsg);
        }

        app(DailyBattleService::class)->record($character, 'payments', $totalCost);

        if ($request->expectsJson() || $request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $successMsg,
                'item_id' => $item->id,
                'item_name' => $item->name,
                'quantity_bought' => $quantity,
                'owned_count' => $ownedCount,
                'cash' => (int) $character->cash,
                'cash_formatted' => number_format($character->cash, 0, ',', '.'),
                'bank' => (int) $character->bank,
                'bank_formatted' => number_format($character->bank, 0, ',', '.'),
                'total' => (int) ($character->cash + $character->bank),
                'total_formatted' => number_format($character->cash + $character->bank, 0, ',', '.'),
                'total_inventory_count' => (int) CharacterItem::where('character_id', $character->id)->where('quantity', '>', 0)->sum('quantity'),
                'masks' => (int) ($character->masks ?? 0),
                'storage_used' => (int) $character->getStorageUsed(),
                'storage_capacity' => (int) $character->getStorageCapacity(),
                'storage_remaining' => max(0, (int) ($character->getStorageCapacity() - $character->getStorageUsed())),
            ]);
        }

        return back()
            ->with('shop_tab', 'items')
            ->with('success', $successMsg);
    }

    /**
     * Consume an item from inventory.
     */
    public function consume(Request $request, CharacterItem $characterItem): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;

        return DB::transaction(function () use ($request, $characterItem, $character): RedirectResponse {
            /** @var CharacterItem|null $lockedItem */
            $lockedItem = CharacterItem::where('id', $characterItem->id)
                ->where('character_id', $character->id)
                ->lockForUpdate()
                ->first();

            if (! $lockedItem || $lockedItem->quantity < 1) {
                return back()->with('shop_tab', 'inventaris')->with('error', 'Voorwerp niet beschikbaar in je inventaris.');
            }

            /** @var Character $lockedCharacter */
            $lockedCharacter = Character::where('id', $character->id)
                ->lockForUpdate()
                ->first();

            $this->energyService->refresh($lockedCharacter);

            $item = $lockedItem->item;
            if (! $item) {
                return back()->with('shop_tab', 'inventaris')->with('error', 'Item niet gevonden.');
            }

            if ($item->category === 'equipment') {
                return back()->with('shop_tab', 'inventaris')->with('info', "{$item->name} is uitrusting en biedt automatisch passieve gevechtskracht in je rugzak!");
            }

            // Voorkom verspilling van herstel-items als zowel energie als gezondheid al 100% vol zijn
            $isPureRecovery = ! in_array($item->slug, ['eiwitshake-bodybuilder', 'multivitaminen-focus', 'bivakmuts-masker', 'smokkelpakketje', 'ijzerzaagje'], true);
            if ($isPureRecovery) {
                $hasEnergyBenefit = ($item->energy_restore > 0 || in_array($item->slug, ['adrenaline-injectie', 'sigaretten', 'ritalin', 'telefoon'], true))
                    && $lockedCharacter->energy < $lockedCharacter->max_energy;
                $hasHealthBenefit = ($item->health_restore > 0 || in_array($item->slug, ['ehbo-noodverbanddoos', 'sigaretten', 'ritalin', 'telefoon'], true))
                    && $lockedCharacter->health < $lockedCharacter->max_health;
                $isRecoveryItem = ($item->energy_restore > 0 || $item->health_restore > 0 || in_array($item->slug, ['adrenaline-injectie', 'ehbo-noodverbanddoos', 'sigaretten', 'ritalin', 'telefoon'], true));

                if ($isRecoveryItem && ! $hasEnergyBenefit && ! $hasHealthBenefit) {
                    return back()
                        ->with('shop_tab', 'inventaris')
                        ->with('info', "Zowel je Energie ({$lockedCharacter->energy}/{$lockedCharacter->max_energy}) als Gezondheid ({$lockedCharacter->health}/{$lockedCharacter->max_health}) zijn al maximaal! Bewaar {$item->name} voor wanneer je het nodig hebt.");
                }
            }

            $energyBefore = $lockedCharacter->energy;
            $healthBefore = $lockedCharacter->health;
            $customFeedback = [];

            // Bereken hoeveel stuks geconsumeerd worden (optie voor enkel, specifiek aantal of tot 100% vol)
            $requestedQty = max(1, (int) $request->input('quantity', 1));
            if ($request->boolean('fill_max')) {
                $energyDeficit = max(0, $lockedCharacter->max_energy - $lockedCharacter->energy);
                $healthDeficit = max(0, $lockedCharacter->max_health - $lockedCharacter->health);

                $perEnergy = $item->energy_restore;
                $perHealth = $item->health_restore;
                if ($item->slug === 'sigaretten') {
                    $perEnergy = 20;
                    $perHealth = 10;
                } elseif ($item->slug === 'ritalin') {
                    $perEnergy = 35;
                    $perHealth = 15;
                } elseif ($item->slug === 'telefoon') {
                    $perEnergy = 50;
                    $perHealth = 25;
                } elseif ($item->slug === 'adrenaline-injectie' || $item->slug === 'ehbo-noodverbanddoos') {
                    $perEnergy = 100;
                    $perHealth = 100;
                }

                $neededForEnergy = ($perEnergy > 0 && $energyDeficit > 0) ? (int) ceil($energyDeficit / $perEnergy) : 0;
                $neededForHealth = ($perHealth > 0 && $healthDeficit > 0) ? (int) ceil($healthDeficit / $perHealth) : 0;
                $needed = max(1, max($neededForEnergy, $neededForHealth));
                $consumeQty = max(1, min($lockedItem->quantity, $needed));
            } else {
                $consumeQty = max(1, min($lockedItem->quantity, $requestedQty));
            }

            // 1. Contraband / Smokkelwaren herstel
            if ($item->slug === 'sigaretten') {
                $restoreEnergy = ($item->energy_restore > 0 ? $item->energy_restore : 20) * $consumeQty;
                $restoreHealth = ($item->health_restore > 0 ? $item->health_restore : 10) * $consumeQty;
                $lockedCharacter->energy = min($lockedCharacter->max_energy, $lockedCharacter->energy + $restoreEnergy);
                $lockedCharacter->health = min($lockedCharacter->max_health, $lockedCharacter->health + $restoreHealth);
                $customFeedback[] = "Lekker {$consumeQty}x peuk gerookt op de binnenplaats! 🚬";
            } elseif ($item->slug === 'ritalin') {
                $restoreEnergy = ($item->energy_restore > 0 ? $item->energy_restore : 35) * $consumeQty;
                $restoreHealth = ($item->health_restore > 0 ? $item->health_restore : 15) * $consumeQty;
                $lockedCharacter->energy = min($lockedCharacter->max_energy, $lockedCharacter->energy + $restoreEnergy);
                $lockedCharacter->health = min($lockedCharacter->max_health, $lockedCharacter->health + $restoreHealth);
                $customFeedback[] = "Scherpe focus en alertheid gekregen ({$consumeQty}x Ritalin)! 💊";
            } elseif ($item->slug === 'telefoon') {
                $restoreEnergy = ($item->energy_restore > 0 ? $item->energy_restore : 50) * $consumeQty;
                $restoreHealth = ($item->health_restore > 0 ? $item->health_restore : 25) * $consumeQty;
                $lockedCharacter->energy = min($lockedCharacter->max_energy, $lockedCharacter->energy + $restoreEnergy);
                $lockedCharacter->health = min($lockedCharacter->max_health, $lockedCharacter->health + $restoreHealth);
                $customFeedback[] = 'Kort contact gehad met de buitenwereld voor morele steun! 📱';
            } elseif ($item->slug === 'strip-oxazepam') {
                $restoreHealth = 35 * $consumeQty;
                $lockedCharacter->health = min($lockedCharacter->max_health, $lockedCharacter->health + $restoreHealth);
                $lockedCharacter->discipline = ($lockedCharacter->discipline ?? 10) + (1 * $consumeQty);
                $customFeedback[] = "Rust keert terug in je hoofd (+{$restoreHealth} Gezondheid, +{$consumeQty} Weerbaarheid/Discipline)! 💊";
            } elseif ($item->slug === 'strip-ritalin') {
                $restoreEnergy = 45 * $consumeQty;
                $lockedCharacter->energy = min($lockedCharacter->max_energy, $lockedCharacter->energy + $restoreEnergy);
                $lockedCharacter->xp += (25 * $consumeQty);
                $customFeedback[] = "Laserfocus en adrenalinekick (+{$restoreEnergy} Energie, +25 XP per strip)! ⚡";
            } elseif ($item->slug === 'haloperidol-forte') {
                $restoreHealth = 60 * $consumeQty;
                $lockedCharacter->health = min($lockedCharacter->max_health, $lockedCharacter->health + $restoreHealth);
                $lockedCharacter->strength += (2 * $consumeQty);
                $lockedCharacter->defense += (2 * $consumeQty);
                $customFeedback[] = "💥 BERSERK STATUS! Alle pijn verdoofd (+{$restoreHealth} Gezondheid, +{$consumeQty}x 2 Kracht & 2 Verdediging)!";
            } else {
                // Reguliere herstel items
                if ($item->slug === 'adrenaline-injectie') {
                    $lockedCharacter->energy = $lockedCharacter->max_energy;
                    $customFeedback[] = "100% Volledige Energie hersteld ({$lockedCharacter->max_energy}/{$lockedCharacter->max_energy})! ⚡";
                } elseif ($item->energy_restore > 0) {
                    $lockedCharacter->energy = min($lockedCharacter->max_energy, $lockedCharacter->energy + ($item->energy_restore * $consumeQty));
                }

                if ($item->slug === 'ehbo-noodverbanddoos') {
                    $lockedCharacter->health = $lockedCharacter->max_health;
                    $customFeedback[] = "100% Volledige Gezondheid hersteld ({$lockedCharacter->max_health}/{$lockedCharacter->max_health} HP)! ❤️";
                } elseif ($item->health_restore > 0) {
                    $lockedCharacter->health = min($lockedCharacter->max_health, $lockedCharacter->health + ($item->health_restore * $consumeQty));
                }
            }

            // 2. Permanente stat boosts
            if ($item->slug === 'eiwitshake-bodybuilder') {
                $lockedCharacter->strength += (1 * $consumeQty);
                $customFeedback[] = "+{$consumeQty} Permanente Kracht 💪 (nu: {$lockedCharacter->strength})!";
            }
            if ($item->slug === 'multivitaminen-focus') {
                $lockedCharacter->stamina += (1 * $consumeQty);
                $customFeedback[] = "+{$consumeQty} Permanente Conditie 🏃 (nu: {$lockedCharacter->stamina})!";
            }

            // 3. Masker toevoegen
            if ($item->slug === 'bivakmuts-masker') {
                $lockedCharacter->masks = ($lockedCharacter->masks ?? 0) + $consumeQty;
                $customFeedback[] = "+{$consumeQty} Masker(s) 🎭 toegevoegd aan je voorraad (totaal: {$lockedCharacter->masks})!";
            }

            // 4. Ongeopend pakketje toevoegen
            if ($item->slug === 'smokkelpakketje') {
                $lockedCharacter->unopened_bags = ($lockedCharacter->unopened_bags ?? 0) + $consumeQty;
                $customFeedback[] = "+{$consumeQty} Ongeopend Pakket(ten) 📦 toegevoegd aan Koffers & Buit (totaal: {$lockedCharacter->unopened_bags})!";
            }

            // 5. IJzerzaagje (Strafvermindering cel)
            if ($item->slug === 'ijzerzaagje') {
                if ($lockedCharacter->is_in_prison && $lockedCharacter->prison_until) {
                    $newPrisonUntil = $lockedCharacter->prison_until->copy()->subMinutes(30 * $consumeQty);
                    if ($newPrisonUntil->isPast()) {
                        $lockedCharacter->is_in_prison = false;
                        $lockedCharacter->prison_until = null;
                        $lockedCharacter->prison_reason = null;
                        $customFeedback[] = 'Tralies doorgezaagd! Je bent direct ontsnapt uit de cel! 🔓';
                    } else {
                        $lockedCharacter->prison_until = $newPrisonUntil;
                        $customFeedback[] = (30 * $consumeQty).' minuten straftijd afgezagen van je celstraf! ⏳';
                    }
                } else {
                    $customFeedback[] = 'Zaagje bewaard en getest! Klaar voor je volgende uitbraak! ⏳';
                }
            }

            $lockedCharacter->save();

            $lockedItem->quantity -= $consumeQty;
            if ($lockedItem->quantity <= 0) {
                $lockedItem->delete();
            } else {
                $lockedItem->save();
            }

            $energyGained = $lockedCharacter->energy - $energyBefore;
            $healthGained = $lockedCharacter->health - $healthBefore;

            $msg = $consumeQty > 1 ? "Je hebt {$consumeQty}x {$item->name} geconsumeerd!" : "Je hebt {$item->name} gebruikt!";
            $details = [];
            if (! empty($customFeedback)) {
                $details[] = implode(' ', $customFeedback);
            }
            $statGains = [];
            if ($energyGained > 0) {
                $statGains[] = "+{$energyGained} Energie ⚡";
            }
            if ($healthGained > 0) {
                $statGains[] = "+{$healthGained} HP ❤️";
            }
            if (! empty($statGains)) {
                $details[] = '('.implode(', ', $statGains).')';
            }

            if (! empty($details)) {
                $msg .= ' '.implode(' ', $details);
            } elseif ($energyGained === 0 && $healthGained === 0 && empty($customFeedback)) {
                if ($lockedCharacter->energy >= $lockedCharacter->max_energy && $lockedCharacter->health >= $lockedCharacter->max_health) {
                    $msg .= ' (Energie en Gezondheid waren al maximaal)';
                }
            }

            return back()
                ->with('shop_tab', 'inventaris')
                ->with('success', $msg);
        });
    }

    /**
     * Purchase exclusive items with Stars ⭐ (Maskers, Kluiscodes, Pakketjes).
     */
    public function buyStarItem(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;

        $validated = $request->validate([
            'item_type' => ['required', 'string', 'in:mask_1,mask_5,vault_code,package_150'],
        ]);

        $configs = [
            'mask_1' => ['cost' => 15, 'name' => '1x Masker 🎭'],
            'mask_5' => ['cost' => 65, 'name' => '5x Maskers 🎭'],
            'vault_code' => ['cost' => 45, 'name' => 'Geheime MedicijnKluis Code 🔐'],
            'package_150' => ['cost' => 150, 'name' => 'Exclusief Master Pakket 📦'],
        ];

        $cfg = $configs[$validated['item_type']];

        return DB::transaction(function () use ($character, $validated, $cfg): RedirectResponse {
            /** @var Character $lockedCharacter */
            $lockedCharacter = Character::where('id', $character->id)->lockForUpdate()->first();

            if (($lockedCharacter->stars ?? 0) < $cfg['cost']) {
                return back()->with('shop_tab', 'sterren')->with('error', "Je hebt minimaal {$cfg['cost']} Sterren ⭐ nodig voor {$cfg['name']}! (Je hebt er {$lockedCharacter->stars}).");
            }

            $lockedCharacter->stars -= $cfg['cost'];

            if ($validated['item_type'] === 'mask_1') {
                $lockedCharacter->masks = ($lockedCharacter->masks ?? 0) + 1;
                $lockedCharacter->save();

                return back()
                    ->with('shop_tab', 'sterren')
                    ->with('success', "Gekocht! 1x Masker 🎭 toegevoegd aan je voorraad voor {$cfg['cost']} Sterren ⭐.");
            }

            if ($validated['item_type'] === 'mask_5') {
                $lockedCharacter->masks = ($lockedCharacter->masks ?? 0) + 5;
                $lockedCharacter->save();

                return back()
                    ->with('shop_tab', 'sterren')
                    ->with('success', "Gekocht! 5x Maskers 🎭 toegevoegd aan je voorraad voor {$cfg['cost']} Sterren ⭐.");
            }

            if ($validated['item_type'] === 'vault_code') {
                $codeStr = 'SHOP-'.strtoupper(substr(md5(uniqid((string) mt_rand(), true)), 0, 8));
                VaultCode::create([
                    'code' => $codeStr,
                    'reward_cash' => rand(3000, 7500),
                    'reward_stars' => rand(2, 4),
                    'reward_tokens' => 3,
                    'character_id' => $lockedCharacter->id,
                    'is_redeemed' => false,
                ]);

                GameMessage::create([
                    'recipient_id' => $lockedCharacter->id,
                    'sender_name' => 'Kliniek Shop Dealer',
                    'subject' => '🔐 Jouw Aangeschafte MedicijnKluis Code!',
                    'content' => "Beste {$lockedCharacter->name},\n\nHier is jouw aangeschafte kluiscode voucher:\n\n👉 **{$codeStr}**\n\nWissel hem direct in op de MedicijnKluis voor wit geld op je bank, extra sterren en maskers!",
                    'type' => 'reward',
                ]);

                $lockedCharacter->save();

                return back()
                    ->with('shop_tab', 'sterren')
                    ->with('success', "Gekocht! Geheime kluiscode voucher {$codeStr} bezorgd in je postvak en toegevoegd aan je kluiscodes!");
            }

            if ($validated['item_type'] === 'package_150') {
                $lockedCharacter->unopened_bags = ($lockedCharacter->unopened_bags ?? 0) + 1;
                $lockedCharacter->save();

                return back()
                    ->with('shop_tab', 'sterren')
                    ->with('success', "Gekocht! Exclusief Master Pakket 📦 toegevoegd aan je ongeopende koffers voor {$cfg['cost']} Sterren ⭐. Open hem direct bij Koffers!");
            }

            return back()->with('shop_tab', 'sterren');
        });
    }

    /**
     * Purchase high-value bundles with Stars ⭐ (Codes, Koffers, Cash, Masks) - Matching Screenshot 3.
     */
    public function buyBundle(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;

        $allowedBundleIds = array_unique(array_merge(array_keys(self::BUNDLES), array_keys(self::BUNDLE_ALIASES)));
        $validated = $request->validate([
            'bundle_id' => ['required', 'string', 'in:'.implode(',', $allowedBundleIds)],
            'quantity' => ['required', 'integer', 'min:1', 'max:10'],
            'recipient_id' => ['nullable'],
            'recipient_name' => ['nullable', 'string'],
        ]);

        $rawBundleId = $validated['bundle_id'];
        $resolvedBundleId = self::BUNDLE_ALIASES[$rawBundleId] ?? $rawBundleId;

        $recipientId = $validated['recipient_id'] ?? null;
        if (empty($recipientId) && ! empty($validated['recipient_name'])) {
            $nameClean = trim($validated['recipient_name']);
            if (! str_contains(strtolower($nameClean), 'eigen account')) {
                $foundChar = Character::where('name', $nameClean)->first();
                if ($foundChar) {
                    $recipientId = $foundChar->id;
                }
            }
        }

        $bundle = self::BUNDLES[$resolvedBundleId];
        $quantity = (int) $validated['quantity'];
        $totalCost = $bundle['cost_stars'] * $quantity;

        return DB::transaction(function () use ($character, $recipientId, $bundle, $quantity, $totalCost): RedirectResponse {
            /** @var Character $lockedCharacter */
            $lockedCharacter = Character::where('id', $character->id)->lockForUpdate()->first();

            if (($lockedCharacter->stars ?? 0) < $totalCost) {
                return back()->with('error', "Niet genoeg sterren! Deze bundel kost {$totalCost} Sterren ⭐ (Je hebt er {$lockedCharacter->stars}).");
            }

            $lockedCharacter->stars -= $totalCost;

            // Determine target recipient (either self or gifted friend)
            $isGift = ! empty($recipientId) && (int) $recipientId !== $lockedCharacter->id;
            /** @var Character $target */
            $target = $isGift ? Character::where('id', $recipientId)->lockForUpdate()->findOrFail($recipientId) : $lockedCharacter;

            // Total contents calculated
            $totalCash = $bundle['cash'] * $quantity;
            $totalBags = $bundle['koffers'] * $quantity;
            $totalMasks = $bundle['masks'] * $quantity;
            $totalCodes = $bundle['codes'] * $quantity;

            // Bonus: XXL koffer bonus for high value purchase (>= 100 stars)
            $bonusBags = ($bundle['cost_stars'] >= 100) ? (1 * $quantity) : 0;
            $totalBags += $bonusBags;

            // Apply rewards to target
            $target->bank += $totalCash;
            $target->unopened_bags = ($target->unopened_bags ?? 0) + $totalBags;
            $target->masks = ($target->masks ?? 0) + $totalMasks;

            // Generate vault voucher codes for recipient
            for ($i = 0; $i < min(25, $totalCodes); $i++) {
                $codeStr = 'BDL-'.strtoupper(substr(md5(uniqid((string) mt_rand(), true)), 0, 7));
                VaultCode::create([
                    'code' => $codeStr,
                    'reward_cash' => rand(2500, 7500),
                    'reward_stars' => rand(1, 3),
                    'reward_tokens' => 2,
                    'character_id' => $target->id,
                    'is_redeemed' => false,
                ]);
            }

            $target->save();
            if ($isGift) {
                $lockedCharacter->save();
            }

            if ($isGift) {
                GameMessage::create([
                    'recipient_id' => $target->id,
                    'sender_id' => $lockedCharacter->id,
                    'sender_name' => $lockedCharacter->name,
                    'subject' => "🎁 VERRASSING! Je hebt een {$bundle['name']} cadeau gekregen!",
                    'content' => "Beste {$target->name},\n\n{$lockedCharacter->name} heeft jou zojuist een exclusief cadeau gestuurd:\n\n📦 **{$bundle['name']} ({$quantity}x)**\n• +€".number_format($totalCash, 0, ',', '.')." wit bankgeld 🏦\n• +{$totalBags} Koffers 🧳\n• +{$totalMasks} Maskers 🎭\n• +{$totalCodes} Geheime Kluiscodes 🔐 (klaar om te verzilveren op de MedicijnKluis!)\n\nVeel succes in de TBS Kliniek!",
                    'type' => 'reward',
                ]);

                return back()
                    ->with('shop_tab', 'pakketten')
                    ->with('success', "🎁 Prachtig! Je hebt {$quantity}x {$bundle['name']} succesvol cadeau gedaan aan {$target->name} voor {$totalCost} Sterren ⭐!");
            }

            $bonusMsg = $bonusBags > 0 ? " inclusief +{$bonusBags} bonus XXL koffer(s)!" : '!';

            return back()
                ->with('shop_tab', 'pakketten')
                ->with('success', "🎉 Gefeliciteerd! {$bundle['name']} ontvangen voor {$totalCost} Sterren ⭐{$bonusMsg} Inhoud: +€".number_format($totalCash, 0, ',', '.')." bankgeld, +{$totalBags} koffers, +{$totalMasks} maskers en +{$totalCodes} kluiscodes!");
        });
    }
}
