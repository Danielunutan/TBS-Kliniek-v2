<?php

namespace App\Http\Controllers;

use App\Models\Character;
use App\Models\CharacterItem;
use App\Models\GameMessage;
use App\Models\Item;
use App\Models\User;
use App\Models\VaultCode;
use App\Services\AuditLogService;
use App\Services\DailyBattleService;
use App\Services\EconomyService;
use App\Services\EnergyService;
use App\Services\LevelService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ShopController extends Controller
{
    public const DAILY_DEAL_DISCOUNT_PCT = 15;

    public const DAILY_DEAL_CANDIDATES = [
        'kapsalon-paviljoen-7',
        'energy-drink-xxl',
        'portie-bitterballen',
        'bami-schijf-speciaal',
        'saucijzenbroodje',
        'sterke-kliniek-koffie',
        'broodje-bal-mayo',
    ];

    public const BUNDLES = [
        'kliniek_starter' => [
            'id' => 'kliniek_starter',
            'name' => 'Opname & Start-Rantsoen 🏥',
            'category' => 'smokkel',
            'cost_stars' => 75,
            'codes' => 10,
            'koffers' => 3,
            'cash' => 150000,
            'masks' => 10,
        ],
        'smokkel_buit' => [
            'id' => 'smokkel_buit',
            'name' => 'Koeriers & Lading Cargo 📦',
            'category' => 'smokkel',
            'cost_stars' => 150,
            'codes' => 15,
            'koffers' => 6,
            'cash' => 500000,
            'masks' => 25,
        ],
        'afdelingsbaas_deal' => [
            'id' => 'afdelingsbaas_deal',
            'name' => 'Paviljoenleider Voorraadkist 💼',
            'category' => 'kluisbuit',
            'cost_stars' => 250,
            'codes' => 25,
            'koffers' => 10,
            'cash' => 1500000,
            'masks' => 40,
        ],
        'syndicaat_pack' => [
            'id' => 'syndicaat_pack',
            'name' => 'Onderwereld Kartel Transport 👑',
            'category' => 'syndicaat',
            'cost_stars' => 500,
            'codes' => 50,
            'koffers' => 25,
            'cash' => 5000000,
            'masks' => 100,
        ],
        'koffer_booster_xl' => [
            'id' => 'koffer_booster_xl',
            'name' => 'Schaduwhandel Monopolie XL 💉',
            'category' => 'kluisbuit',
            'cost_stars' => 1000,
            'codes' => 120,
            'koffers' => 60,
            'cash' => 15000000,
            'masks' => 250,
        ],
        'eindbaas_kluis_xxl' => [
            'id' => 'eindbaas_kluis_xxl',
            'name' => 'Hoofddirectie Reservekluis XXL 🏆',
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
        protected LevelService $levelService,
    ) {}

    /**
     * Select one deterministic daily deal item based on the current date.
     */
    public function getDailyDealItem(?Collection $items = null): ?Item
    {
        $buyable = ($items ?? Item::where('is_buyable', true)->orderBy('id')->get())->sortBy('id')->values();
        if ($buyable->isEmpty()) {
            return null;
        }

        $preferredPool = $buyable->filter(fn (Item $item) => in_array($item->slug, self::DAILY_DEAL_CANDIDATES, true))->values();
        $pool = $preferredPool->isNotEmpty()
            ? $preferredPool
            : $buyable->filter(fn (Item $item) => ! in_array($item->slug, ['smokkelpakketje', 'test-gadget', 'frikandelbroodje'], true) && $item->category !== 'equipment')->values();

        if ($pool->isEmpty()) {
            return null;
        }

        $seed = abs(crc32('tbs-daily-deal-'.Carbon::today()->toDateString()));

        return $pool->get($seed % $pool->count());
    }

    /**
     * Render the Grid-shop with category filters, Daily Deal, and live stat comparison.
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

        $dailyDealItem = $this->getDailyDealItem($items);
        $dailyDealDiscountPct = self::DAILY_DEAL_DISCOUNT_PCT;

        $playerStatsComparison = [
            'energy' => (int) $character->energy,
            'max_energy' => (int) max(1, $character->max_energy),
            'health' => (int) $character->health,
            'max_health' => (int) max(1, $character->max_health),
            'strength' => (int) $character->getEffectiveStrength(),
            'base_strength' => (int) ($character->strength ?? 10),
            'defense' => (int) $character->getEffectiveDefense(),
            'stamina' => (int) $character->getEffectiveStamina(),
            'base_stamina' => (int) ($character->stamina ?? 10),
            'intelligence' => (int) $character->getEffectiveIntelligence(),
            'charisma' => (int) $character->getEffectiveCharisma(),
            'discipline' => (int) $character->getEffectiveDiscipline(),
            'stat_cap' => (int) $character->getMaxStatForLevel(),
        ];

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
            'topRankings',
            'dailyDealItem',
            'dailyDealDiscountPct',
            'playerStatsComparison'
        ));
    }

    /**
     * Buy an item with cash or bank balance, applying Charisma and Daily Deal discounts.
     */
    public function buy(Request $request, Item $item): RedirectResponse|JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;

        if (! $item->is_buyable || $item->slug === 'smokkelpakketje') {
            $msg = 'Dit artikel is uitgesloten van reguliere toonbankverkoop.';
            if ($request->expectsJson() || $request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }

            return back()->with('shop_tab', 'items')->with('error', $msg);
        }

        $isEquipment = $item->category === 'equipment';
        $isRestrictedStatItem = in_array($item->slug, ['eiwitshake-bodybuilder', 'multivitaminen-focus'], true);

        if ($isEquipment || $isRestrictedStatItem) {
            $quantity = 1;

            if ($isEquipment) {
                $alreadyOwned = CharacterItem::where('character_id', $character->id)
                    ->where('item_id', $item->id)
                    ->where('quantity', '>=', 1)
                    ->exists();

                if ($alreadyOwned) {
                    $errorMsg = "{$item->name} zit al in je plunjezak! Uitrustingsstukken zijn uniek en leveren doorlopend passieve gevechtskracht.";

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
            }

            if ($isRestrictedStatItem) {
                $perks = $character->perks ?? [];
                $cooldownKey = 'last_bought_'.str_replace('-', '_', $item->slug);
                if (! empty($perks[$cooldownKey])) {
                    $lastBought = Carbon::parse($perks[$cooldownKey]);
                    $cooldownUntil = $lastBought->copy()->addHours(6);
                    if ($cooldownUntil->isFuture()) {
                        $diffMinutes = (int) now()->diffInMinutes($cooldownUntil);
                        $hours = intdiv($diffMinutes, 60);
                        $minutes = $diffMinutes % 60;
                        $timeRemaining = $hours > 0 ? "{$hours} uur en {$minutes} minuten" : "{$minutes} minuten";

                        $errorMsg = "Klinisch doseringslimiet bereikt: je mag {$item->name} maximaal 1x per 6 uur aanschaffen! Volgende afgifte over {$timeRemaining}.";

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
                }
            }
        } else {
            $quantity = max(1, min(50, (int) $request->input('quantity', 1)));
        }

        // Charisma discount up to 15% (1% per 10 charisma) + Daily Deal discount (15%), capped at 25% total
        $charismaDiscountPct = min(15, (int) floor($character->getEffectiveCharisma() / 10));
        $dailyDealItem = $this->getDailyDealItem();
        $isDailyDeal = $dailyDealItem && $dailyDealItem->id === $item->id;
        $dailyDealBonus = $isDailyDeal ? self::DAILY_DEAL_DISCOUNT_PCT : 0;

        $discountPct = min(25, $charismaDiscountPct + $dailyDealBonus);
        $unitPrice = $discountPct > 0 ? (int) round($item->price_kc * (1 - ($discountPct / 100))) : $item->price_kc;
        $totalCost = $unitPrice * $quantity;

        // Stat boost items, drinks and masks that do not take standard depot inventory
        $isDirectDelivery = in_array($item->slug, [
            'bivakmuts-masker',
            'eiwitshake-bodybuilder',
            'multivitaminen-focus',
            'haloperidol-forte',
        ], true);

        if (! $isDirectDelivery && ! $character->canAddStorageItem($quantity)) {
            $storageUsed = $character->getStorageUsed();
            $storageCap = $character->getStorageCapacity();
            $storageRemaining = max(0, $storageCap - $storageUsed);

            $errorMsg = $storageRemaining > 0
                ? "Je celopslag telt nog slechts {$storageRemaining} vrije vak(ken) ({$storageUsed}/{$storageCap})! Verlaag het aantal naar maximaal {$storageRemaining}x."
                : "Je celopslag zit volledig vol ({$storageUsed}/{$storageCap} vakken bezet)! Ruim eerst voorraad op in je Depot of breid je capaciteit uit.";

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

        $discountParts = [];
        if ($isDailyDeal) {
            $discountParts[] = "{$dailyDealBonus}% Dagdeal";
        }
        if ($charismaDiscountPct > 0) {
            $discountParts[] = "{$charismaDiscountPct}% Charisma";
        }
        $discountNote = ! empty($discountParts) ? ' (inclusief '.implode(' + ', $discountParts).' korting! 🔥)' : '';
        $formatted = number_format($totalCost, 0, ',', '.');
        $ownedCount = 0;
        $successMsg = '';

        $purchaseSuccess = DB::transaction(function () use ($character, $totalCost, $item, $quantity, $isDirectDelivery, $isRestrictedStatItem, $discountNote, $formatted, &$ownedCount, &$successMsg): bool {
            /** @var Character $locked */
            $locked = Character::where('id', $character->id)->lockForUpdate()->first();
            $totalAvailable = (int) ($locked->cash + $locked->bank);

            if ($totalAvailable < $totalCost) {
                return false;
            }

            if ($item->category === 'equipment') {
                $alreadyOwnedUnderLock = CharacterItem::where('character_id', $locked->id)
                    ->where('item_id', $item->id)
                    ->where('quantity', '>=', 1)
                    ->lockForUpdate()
                    ->exists();
                if ($alreadyOwnedUnderLock) {
                    return false;
                }
            } elseif (! $isDirectDelivery && ! $locked->canAddStorageItem($quantity)) {
                return false;
            }

            if ($isRestrictedStatItem) {
                $lockedPerks = $locked->perks ?? [];
                $cooldownKey = 'last_bought_'.str_replace('-', '_', $item->slug);
                if (! empty($lockedPerks[$cooldownKey]) && Carbon::parse($lockedPerks[$cooldownKey])->addHours(6)->isFuture()) {
                    return false;
                }
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
                $successMsg = "Afrekening voltooid: {$quantity}x {$item->name} ingeslagen voor €{$formatted}{$discountNote}! Direct +{$quantity} Masker(s) 🎭 klaargelegd voor Zakkenrollen (totaal: {$locked->masks}).";
            } elseif ($item->slug === 'smokkelpakketje') {
                $locked->unopened_bags = ($locked->unopened_bags ?? 0) + $quantity;
                $locked->save();
                $ownedCount = (int) $locked->unopened_bags;
                $successMsg = "Afrekening voltooid: {$quantity}x {$item->name} ingeslagen voor €{$formatted}{$discountNote}! Direct +{$quantity} Pakket(ten) 📦 bijgeschreven (totaal: {$locked->unopened_bags}).";
            } elseif ($item->category === 'equipment') {
                $inv = CharacterItem::firstOrNew([
                    'character_id' => $locked->id,
                    'item_id' => $item->id,
                ]);
                $inv->quantity = 1;
                $inv->save();
                $ownedCount = 1;
                $successMsg = "Uitrustingsstuk {$item->name} vergrendeld in je plunjezak voor €{$formatted}{$discountNote}! Je gevechtsstatistieken zijn permanent verhoogd.";
            } else {
                $inv = CharacterItem::firstOrNew([
                    'character_id' => $locked->id,
                    'item_id' => $item->id,
                ]);
                $inv->quantity = ($inv->quantity ?? 0) + $quantity;
                $inv->save();
                $ownedCount = (int) $inv->quantity;
                $successMsg = "Toonbankdeal afgerond: {$quantity}x {$item->name} aangeschaft voor €{$formatted}{$discountNote}!";

                if (in_array($item->slug, ['eiwitshake-bodybuilder', 'multivitaminen-focus'], true)) {
                    $perks = $locked->perks ?? [];
                    $cooldownKey = 'last_bought_'.str_replace('-', '_', $item->slug);
                    $perks[$cooldownKey] = now()->toISOString();
                    $locked->perks = $perks;
                    $locked->save();
                }
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
            $character->perks = $locked->perks;

            return true;
        });

        if (! $purchaseSuccess) {
            $errorMsg = "Saldo ontoereikend! Voor {$quantity}x {$item->name} is €{$formatted} vereist.";

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

        try {
            AuditLogService::log(
                action: 'shop_buy',
                category: 'shop',
                description: "Winkelaankoop: {$quantity}x {$item->name} voor €{$formatted}",
                character: $character,
                user: $user,
                amount: $totalCost,
                details: [
                    'item_id' => $item->id,
                    'item_name' => $item->name,
                    'item_slug' => $item->slug,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'total_cost' => $totalCost,
                    'discount_pct' => $discountPct,
                    'is_daily_deal' => $isDailyDeal,
                ]
            );
        } catch (\Throwable) {
            // Ignore logging error
        }

        if ($request->expectsJson() || $request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $successMsg,
                'item_id' => $item->id,
                'item_name' => $item->name,
                'quantity_bought' => $quantity,
                'owned_count' => $ownedCount,
                'is_daily_deal' => $isDailyDeal,
                'discount_pct' => $discountPct,
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
    public function consume(Request $request, CharacterItem $characterItem): RedirectResponse|JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;

        return DB::transaction(function () use ($request, $characterItem, $character): RedirectResponse|JsonResponse {
            /** @var CharacterItem|null $lockedItem */
            $lockedItem = CharacterItem::where('id', $characterItem->id)
                ->where('character_id', $character->id)
                ->lockForUpdate()
                ->first();

            if (! $lockedItem || $lockedItem->quantity < 1) {
                if ($request->wantsJson() || $request->ajax()) {
                    return response()->json(['success' => false, 'message' => 'Dit artikel ontbreekt in je plunjezak.'], 422);
                }

                return back()->with('shop_tab', 'inventaris')->with('error', 'Dit artikel ontbreekt in je plunjezak.');
            }

            /** @var Character $lockedCharacter */
            $lockedCharacter = Character::where('id', $character->id)
                ->lockForUpdate()
                ->first();

            $this->energyService->refresh($lockedCharacter);

            if ($lockedCharacter->isComatose()) {
                $comaMsg = 'Kritieke Code Blauw status! Dien eerst een '.Character::REVIVE_SERUM_NAME.' toe om bij bewustzijn te komen.';
                if ($request->wantsJson() || $request->ajax()) {
                    return response()->json(['success' => false, 'message' => $comaMsg], 422);
                }

                return back()->with('shop_tab', 'inventaris')->with('error', $comaMsg);
            }

            $item = $lockedItem->item;
            if (! $item) {
                if ($request->wantsJson() || $request->ajax()) {
                    return response()->json(['success' => false, 'message' => 'Artikelgegevens niet gevonden.'], 422);
                }

                return back()->with('shop_tab', 'inventaris')->with('error', 'Artikelgegevens niet gevonden.');
            }

            if ($item->category === 'equipment' || ! $item->isConsumableItem()) {
                if ($request->wantsJson() || $request->ajax()) {
                    return response()->json(['success' => false, 'message' => "{$item->name} fungeert als vaste uitrusting en versterkt doorlopend je eigenschappen vanuit je plunjezak!"], 422);
                }

                return back()->with('shop_tab', 'inventaris')->with('info', "{$item->name} fungeert als vaste uitrusting en versterkt doorlopend je eigenschappen vanuit je plunjezak!");
            }

            // Voorkom verspilling van herstel-items als zowel energie als gezondheid al 100% vol zijn
            $isPureRecovery = ! in_array($item->slug, ['eiwitshake-bodybuilder', 'multivitaminen-focus', 'bivakmuts-masker', 'smokkelpakketje', 'ijzerzaagje', 'haloperidol-forte'], true);
            if ($isPureRecovery) {
                $hasEnergyBenefit = ($item->energy_restore > 0 || in_array($item->slug, ['adrenaline-injectie', 'sigaretten', 'ritalin', 'telefoon'], true))
                    && $lockedCharacter->energy < $lockedCharacter->max_energy;
                $hasHealthBenefit = ($item->health_restore > 0 || in_array($item->slug, ['ehbo-noodverbanddoos', 'sigaretten', 'ritalin', 'telefoon'], true))
                    && $lockedCharacter->health < $lockedCharacter->max_health;
                $isRecoveryItem = ($item->energy_restore > 0 || $item->health_restore > 0 || in_array($item->slug, ['adrenaline-injectie', 'ehbo-noodverbanddoos', 'sigaretten', 'ritalin', 'telefoon'], true));

                if ($isRecoveryItem && ! $hasEnergyBenefit && ! $hasHealthBenefit) {
                    $alreadyFullMsg = "Je vitale functies — Energie ({$lockedCharacter->energy}/{$lockedCharacter->max_energy}) én Gezondheid ({$lockedCharacter->health}/{$lockedCharacter->max_health}) — zijn al maximaal! Houd {$item->name} achter de hand voor een later moment.";
                    if ($request->wantsJson() || $request->ajax()) {
                        return response()->json(['success' => false, 'message' => $alreadyFullMsg], 422);
                    }

                    return back()
                        ->with('shop_tab', 'inventaris')
                        ->with('info', $alreadyFullMsg);
                }
            }

            $energyBefore = $lockedCharacter->energy;
            $healthBefore = $lockedCharacter->health;
            $customFeedback = [];

            // Bereken hoeveel stuks geconsumeerd worden (optie voor enkel, specifiek aantal of tot 100% vol)
            $requestedQty = max(1, (int) $request->input('quantity', 1));
            // Cooldown items must not use "fill to max". Otherwise a player could stockpile them and bypass the 6-hour cooldown.
            if ($request->boolean('fill_max') && ! in_array($item->slug, ['eiwitshake-bodybuilder', 'multivitaminen-focus'], true)) {
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
                } elseif ($item->slug === 'strip-oxazepam') {
                    $perHealth = $item->health_restore > 0 ? $item->health_restore : 35;
                } elseif ($item->slug === 'strip-ritalin') {
                    $perEnergy = $item->energy_restore > 0 ? $item->energy_restore : 45;
                } elseif ($item->slug === 'haloperidol-forte') {
                    $perEnergy = $item->energy_restore > 0 ? $item->energy_restore : 20;
                    $perHealth = $item->health_restore > 0 ? $item->health_restore : 60;
                }

                $neededForEnergy = ($perEnergy > 0 && $energyDeficit > 0) ? (int) ceil($energyDeficit / $perEnergy) : 0;
                $neededForHealth = ($perHealth > 0 && $healthDeficit > 0) ? (int) ceil($healthDeficit / $perHealth) : 0;
                $needed = max(1, max($neededForEnergy, $neededForHealth));
                $consumeQty = max(1, min($lockedItem->quantity, $needed));
            } else {
                if (in_array($item->slug, ['eiwitshake-bodybuilder', 'multivitaminen-focus'], true)) {
                    $useCooldownKey = 'last_used_'.str_replace('-', '_', $item->slug);
                    $perks = $lockedCharacter->perks ?? [];
                    if (! empty($perks[$useCooldownKey])) {
                        $cooldownUntil = Carbon::parse($perks[$useCooldownKey])->addHours(6);
                        if ($cooldownUntil->isFuture()) {
                            $diffMinutes = (int) now()->diffInMinutes($cooldownUntil);
                            $hours = intdiv($diffMinutes, 60);
                            $minutes = $diffMinutes % 60;
                            $timeRemaining = $hours > 0 ? "{$hours} uur en {$minutes} minuten" : "{$minutes} minuten";

                            $errorMsg = "Spier- en opnamepauze actief: je mag {$item->name} maximaal 1x per 6 uur innemen. Volgende dosis over {$timeRemaining}.";

                            if ($request->expectsJson() || $request->wantsJson() || $request->ajax()) {
                                return response()->json([
                                    'success' => false,
                                    'message' => $errorMsg,
                                ], 422);
                            }

                            return back()
                                ->with('shop_tab', 'inventaris')
                                ->with('error', $errorMsg);
                        }
                    }
                    $consumeQty = 1;
                } else {
                    $consumeQty = max(1, min($lockedItem->quantity, $requestedQty));
                }
            }

            // 1. Contraband / Smokkelwaren herstel
            if ($item->slug === 'sigaretten') {
                $restoreEnergy = ($item->energy_restore > 0 ? $item->energy_restore : 20) * $consumeQty;
                $restoreHealth = ($item->health_restore > 0 ? $item->health_restore : 10) * $consumeQty;
                $lockedCharacter->energy = min($lockedCharacter->max_energy, $lockedCharacter->energy + $restoreEnergy);
                $lockedCharacter->health = min($lockedCharacter->max_health, $lockedCharacter->health + $restoreHealth);
                $customFeedback[] = "Rookpauze achter het luchthek afgerond ({$consumeQty}x)! 🚬";
            } elseif ($item->slug === 'ritalin') {
                $restoreEnergy = ($item->energy_restore > 0 ? $item->energy_restore : 35) * $consumeQty;
                $restoreHealth = ($item->health_restore > 0 ? $item->health_restore : 15) * $consumeQty;
                $lockedCharacter->energy = min($lockedCharacter->max_energy, $lockedCharacter->energy + $restoreEnergy);
                $lockedCharacter->health = min($lockedCharacter->max_health, $lockedCharacter->health + $restoreHealth);
                $customFeedback[] = "Concentratie-impuls geactiveerd ({$consumeQty}x Ritalin)! 💊";
            } elseif ($item->slug === 'telefoon') {
                $restoreEnergy = ($item->energy_restore > 0 ? $item->energy_restore : 50) * $consumeQty;
                $restoreHealth = ($item->health_restore > 0 ? $item->health_restore : 25) * $consumeQty;
                $lockedCharacter->energy = min($lockedCharacter->max_energy, $lockedCharacter->energy + $restoreEnergy);
                $lockedCharacter->health = min($lockedCharacter->max_health, $lockedCharacter->health + $restoreHealth);
                $customFeedback[] = 'Geheime lijn met buitenwereld geopend voor mentale oppepper! 📱';
            } elseif ($item->slug === 'strip-oxazepam') {
                $restoreHealth = ($item->health_restore > 0 ? $item->health_restore : 35) * $consumeQty;
                $lockedCharacter->health = min($lockedCharacter->max_health, $lockedCharacter->health + $restoreHealth);
                $maxStat = $lockedCharacter->getMaxStatForLevel();
                $curDis = (int) ($lockedCharacter->discipline ?? 10);
                if ($curDis < $maxStat) {
                    $lockedCharacter->discipline = min($maxStat, $curDis + $consumeQty);
                }
                $customFeedback[] = "Hartslag gestabiliseerd (+{$restoreHealth} Gezondheid, +{$consumeQty} Discipline)! 💊";
            } elseif ($item->slug === 'strip-ritalin') {
                $restoreEnergy = ($item->energy_restore > 0 ? $item->energy_restore : 45) * $consumeQty;
                $lockedCharacter->energy = min($lockedCharacter->max_energy, $lockedCharacter->energy + $restoreEnergy);
                $this->levelService->addXp($lockedCharacter, 25 * $consumeQty);
                $customFeedback[] = "Mentale versnelling geactiveerd (+{$restoreEnergy} Energie, +25 XP per strip)! ⚡";
            } elseif ($item->slug === 'haloperidol-forte') {
                $restoreEnergy = ($item->energy_restore > 0 ? $item->energy_restore : 20) * $consumeQty;
                $restoreHealth = ($item->health_restore > 0 ? $item->health_restore : 60) * $consumeQty;
                $lockedCharacter->energy = min($lockedCharacter->max_energy, $lockedCharacter->energy + $restoreEnergy);
                $lockedCharacter->health = min($lockedCharacter->max_health, $lockedCharacter->health + $restoreHealth);
                $customFeedback[] = "💥 NOODAMPUL INGEWERKT! Pijngrens uitgeschakeld (+{$restoreEnergy} Energie, +{$restoreHealth} Gezondheid)!";
            } else {
                // Reguliere herstel items
                if ($item->slug === 'adrenaline-injectie') {
                    $lockedCharacter->energy = $lockedCharacter->max_energy;
                    $customFeedback[] = "Energiepeil direct naar 100% gestuwd ({$lockedCharacter->max_energy}/{$lockedCharacter->max_energy})! ⚡";
                } elseif ($item->energy_restore > 0) {
                    $lockedCharacter->energy = min($lockedCharacter->max_energy, $lockedCharacter->energy + ($item->energy_restore * $consumeQty));
                }

                if ($item->slug === 'ehbo-noodverbanddoos') {
                    $lockedCharacter->health = $lockedCharacter->max_health;
                    $customFeedback[] = "Levenspunten direct naar 100% geheeld ({$lockedCharacter->max_health}/{$lockedCharacter->max_health} HP)! ❤️";
                } elseif ($item->health_restore > 0) {
                    $lockedCharacter->health = min($lockedCharacter->max_health, $lockedCharacter->health + ($item->health_restore * $consumeQty));
                }
            }

            // 2. Stat boosts (begrensd per level & max 1x per 6 uur)
            if ($item->slug === 'eiwitshake-bodybuilder') {
                $maxStat = $lockedCharacter->getMaxStatForLevel();
                $cur = (int) ($lockedCharacter->strength ?? 10);
                if ($cur < $maxStat) {
                    $gain = min($consumeQty, $maxStat - $cur);
                    $lockedCharacter->strength = $cur + $gain;
                    $customFeedback[] = "+{$gain} Kracht 💪 (stand: {$lockedCharacter->strength}/{$maxStat})!";
                } else {
                    $customFeedback[] = "Krachtplafond ({$maxStat}) voor je huidige level is al bereikt!";
                }
                $perks = $lockedCharacter->perks ?? [];
                $perks['last_used_eiwitshake_bodybuilder'] = now()->toISOString();
                $lockedCharacter->perks = $perks;
            }
            if ($item->slug === 'multivitaminen-focus') {
                $maxStat = $lockedCharacter->getMaxStatForLevel();
                $cur = (int) ($lockedCharacter->stamina ?? 10);
                if ($cur < $maxStat) {
                    $gain = min($consumeQty, $maxStat - $cur);
                    $lockedCharacter->stamina = $cur + $gain;
                    $customFeedback[] = "+{$gain} Conditie 🏃 (stand: {$lockedCharacter->stamina}/{$maxStat})!";
                } else {
                    $customFeedback[] = "Conditieplafond ({$maxStat}) voor je huidige level is al bereikt!";
                }
                $perks = $lockedCharacter->perks ?? [];
                $perks['last_used_multivitaminen_focus'] = now()->toISOString();
                $lockedCharacter->perks = $perks;
            }

            // 3. Masker toevoegen
            if ($item->slug === 'bivakmuts-masker') {
                $lockedCharacter->masks = ($lockedCharacter->masks ?? 0) + $consumeQty;
                $customFeedback[] = "+{$consumeQty} Masker(s) 🎭 klaargelegd (totaal: {$lockedCharacter->masks})!";
            }

            // 4. Ongeopend pakketje toevoegen
            if ($item->slug === 'smokkelpakketje') {
                $lockedCharacter->unopened_bags = ($lockedCharacter->unopened_bags ?? 0) + $consumeQty;
                $customFeedback[] = "+{$consumeQty} Ongeopend Pakket(ten) 📦 klaargelegd (totaal: {$lockedCharacter->unopened_bags})!";
            }

            // 5. IJzerzaagje (Strafvermindering cel)
            if ($item->slug === 'ijzerzaagje') {
                if (! $lockedCharacter->isImprisoned() || ! $lockedCharacter->prison_until) {
                    if ($request->wantsJson() || $request->ajax()) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Je verblijft momenteel niet in een gesloten cel! Houd je ijzerzaagje achter de hand voor noodgevallen.',
                        ], 422);
                    }

                    return back()
                        ->with('shop_tab', 'inventaris')
                        ->with('error', 'Je verblijft momenteel niet in een gesloten cel! Houd je ijzerzaagje achter de hand voor noodgevallen.');
                }

                $newPrisonUntil = $lockedCharacter->prison_until->copy()->subMinutes(30 * $consumeQty);
                if ($newPrisonUntil->isPast()) {
                    $lockedCharacter->is_in_prison = false;
                    $lockedCharacter->prison_until = null;
                    $lockedCharacter->prison_reason = null;
                    $customFeedback[] = 'Spijlen doorgezaagd! Je staat direct weer op vrije voeten! 🔓';
                } else {
                    $lockedCharacter->prison_until = $newPrisonUntil;
                    $customFeedback[] = (30 * $consumeQty).' minuten celstraf weggevijld! ⏳';
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

            $msg = $consumeQty > 1 ? "Voorraadinname: {$consumeQty}x {$item->name} benut!" : "Artikel {$item->name} ingenomen!";
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
                    $msg .= ' (Energie en Gezondheid stonden al op 100%)';
                }
            }

            Cache::forget("char_realtime_stats_{$lockedCharacter->id}");

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $msg,
                    'energy' => $lockedCharacter->energy,
                    'max_energy' => $lockedCharacter->max_energy,
                    'health' => $lockedCharacter->health,
                    'max_health' => $lockedCharacter->max_health,
                    'remaining_quantity' => $lockedItem->quantity > 0 ? $lockedItem->quantity : 0,
                ]);
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
            'item_type' => ['required', 'string', 'in:full_restore,prison_escape,coma_revive,mask_1,mask_5,ammo_pack,bullets_50,bulletproof_vest,vault_code,protection_24h,xxl_cases_3,package_master,package_150,vip_week'],
        ]);

        $configs = [
            'full_restore' => ['cost' => 5, 'name' => 'Volledige Vitale Reset (100% Energie & HP) ⚡'],
            'prison_escape' => ['cost' => 10, 'name' => 'Directe Celvrijlating (Vrijbrief) 🔓'],
            'coma_revive' => ['cost' => Character::REVIVE_SERUM_STARS, 'name' => Character::REVIVE_SERUM_NAME.' 💉'],
            'mask_1' => ['cost' => 5, 'name' => '1x Bivakmuts 🎭'],
            'mask_5' => ['cost' => 15, 'name' => '5x Bivakmutsen 🎭'],
            'ammo_pack' => ['cost' => 20, 'name' => 'Tactisch Munitiedepot (250 Kogels + Vest) 🎯'],
            'bullets_50' => ['cost' => 50, 'name' => 'Patronendoos (+50 Kogels) 🎯'],
            'bulletproof_vest' => ['cost' => 150, 'name' => 'Gepantserd Kevlar Vest (12 Uur Schild) 🛡️'],
            'vault_code' => ['cost' => 25, 'name' => 'Gecodeerde Kluis-Voucher 🔐'],
            'protection_24h' => ['cost' => 30, 'name' => '24-Uurs Paviljoenimmuniteit 🛡️'],
            'xxl_cases_3' => ['cost' => 35, 'name' => '3x Verzegelde XXL Transportkoffers 💼'],
            'package_master' => ['cost' => 75, 'name' => 'Kartel Commandokist 📦'],
            'package_150' => ['cost' => 100, 'name' => 'Hoofdkwartier Voorraadkist 📦'],
            'vip_week' => ['cost' => 500, 'name' => '7 Dagen Ere-Patiënt (VIP Status) 👑'],
        ];

        $cfg = $configs[$validated['item_type']];

        return DB::transaction(function () use ($character, $validated, $cfg): RedirectResponse {
            /** @var Character $lockedCharacter */
            $lockedCharacter = Character::where('id', $character->id)->lockForUpdate()->first();

            if (($lockedCharacter->stars ?? 0) < $cfg['cost']) {
                return back()->with('shop_tab', 'sterren')->with('error', "Sterrentegoed ontoereikend: voor {$cfg['name']} zijn {$cfg['cost']} Sterren ⭐ vereist (huidig saldo: {$lockedCharacter->stars}).");
            }

            if ($validated['item_type'] === 'prison_escape') {
                if (! $lockedCharacter->isImprisoned()) {
                    return back()->with('shop_tab', 'sterren')->with('error', 'Je verblijft momenteel niet achter gesloten celdeuren!');
                }
                $lockedCharacter->stars -= $cfg['cost'];
                $lockedCharacter->is_in_prison = false;
                $lockedCharacter->prison_until = null;
                $lockedCharacter->prison_reason = null;
                $lockedCharacter->prison_escape_attempted = false;
                $lockedCharacter->ad_prison_progress = 0;
                $lockedCharacter->save();

                return back()
                    ->with('shop_tab', 'sterren')
                    ->with('success', "🔓 CELDEUR ONTGRENDELD! Je hebt je strafdossier afgekocht voor {$cfg['cost']} Sterren ⭐ en staat direct buiten!");
            }

            if ($validated['item_type'] === 'coma_revive') {
                if (! $lockedCharacter->needsReviveSerum() && $lockedCharacter->getLiquidationStreakDeaths() === 0) {
                    return back()->with('shop_tab', 'sterren')->with('error', 'Geen reanimatie vereist: je verkeert niet in Code Blauw en je liquidatieteller staat al op 0/10!');
                }
                $lockedCharacter->stars -= $cfg['cost'];
                $lockedCharacter->applyReviveSerum();

                return back()
                    ->with('shop_tab', 'sterren')
                    ->with('success', '💉 '.Character::REVIVE_SERUM_NAME." toegediend voor {$cfg['cost']} Sterren ⭐! Hartslag hersteld naar 100% HP, liquidatieteller gereset naar 0/10 en 30 minuten noodschild geactiveerd!");
            }

            if ($validated['item_type'] === 'full_restore' && $lockedCharacter->isComatose()) {
                return back()->with('shop_tab', 'sterren')->with('error', 'Kritieke Code Blauw status! Dien eerst een '.Character::REVIVE_SERUM_NAME.' toe voordat je een vitale reset gebruikt.');
            }

            $lockedCharacter->stars -= $cfg['cost'];

            if ($validated['item_type'] === 'full_restore') {
                $lockedCharacter->energy = $lockedCharacter->max_energy;
                $lockedCharacter->health = $lockedCharacter->max_health;
                $lockedCharacter->save();

                return back()
                    ->with('shop_tab', 'sterren')
                    ->with('success', "⚡ Vitale functies gereset! Energie ({$lockedCharacter->max_energy}/{$lockedCharacter->max_energy}) én Gezondheid ({$lockedCharacter->max_health}/{$lockedCharacter->max_health} HP) staan weer op 100% voor {$cfg['cost']} Sterren ⭐.");
            }

            if ($validated['item_type'] === 'mask_1') {
                $lockedCharacter->masks = ($lockedCharacter->masks ?? 0) + 1;
                $lockedCharacter->save();

                return back()
                    ->with('shop_tab', 'sterren')
                    ->with('success', "Uitgifte voltooid: +1x Bivakmuts 🎭 bijgeschreven voor {$cfg['cost']} Sterren ⭐.");
            }

            if ($validated['item_type'] === 'mask_5') {
                $lockedCharacter->masks = ($lockedCharacter->masks ?? 0) + 5;
                $lockedCharacter->save();

                return back()
                    ->with('shop_tab', 'sterren')
                    ->with('success', "Uitgifte voltooid: +5x Bivakmutsen 🎭 bijgeschreven voor {$cfg['cost']} Sterren ⭐.");
            }

            if ($validated['item_type'] === 'ammo_pack') {
                $lockedCharacter->bullets = ($lockedCharacter->bullets ?? 0) + 250;
                $currentVest = $lockedCharacter->vest_until && now()->lt($lockedCharacter->vest_until) ? $lockedCharacter->vest_until : now();
                $lockedCharacter->vest_until = $currentVest->copy()->addHours(24);
                $perks = $lockedCharacter->perks ?? [];
                $perks['vest_tier'] = 'kevlar_24h';
                $lockedCharacter->perks = $perks;
                $lockedCharacter->save();

                return back()
                    ->with('shop_tab', 'sterren')
                    ->with('success', "🎯 Munitiedepot geleverd: +250 Kogels én 24 uur Kevlar-pantser geactiveerd voor {$cfg['cost']} Sterren ⭐!");
            }

            if ($validated['item_type'] === 'bullets_50') {
                $lockedCharacter->bullets = ($lockedCharacter->bullets ?? 0) + 50;
                $lockedCharacter->save();

                return back()
                    ->with('shop_tab', 'sterren')
                    ->with('success', "🎯 Patronendoos geleverd: +50 Kogels toegevoegd aan je wapenkluis voor {$cfg['cost']} Sterren ⭐!");
            }

            if ($validated['item_type'] === 'bulletproof_vest') {
                $currentVest = $lockedCharacter->vest_until && now()->lt($lockedCharacter->vest_until) ? $lockedCharacter->vest_until : now();
                $lockedCharacter->vest_until = $currentVest->copy()->addHours(12);
                $perks = $lockedCharacter->perks ?? [];
                $perks['vest_tier'] = 'titanium_12h';
                $lockedCharacter->perks = $perks;
                $lockedCharacter->save();

                return back()
                    ->with('shop_tab', 'sterren')
                    ->with('success', "🛡️ Pantservest aangetrokken: 12 uur ballistische bescherming geactiveerd voor {$cfg['cost']} Sterren ⭐!");
            }

            if ($validated['item_type'] === 'vault_code') {
                $codeStr = VaultCode::generateUniqueCode('SHOP');
                VaultCode::create([
                    'code' => $codeStr,
                    'reward_cash' => rand(15000, 35000),
                    'reward_stars' => rand(2, 4),
                    'reward_tokens' => 5,
                    'character_id' => $lockedCharacter->id,
                    'is_redeemed' => false,
                ]);

                GameMessage::create([
                    'recipient_id' => $lockedCharacter->id,
                    'sender_name' => 'Paviljoen Bevoorrading',
                    'subject' => '🔐 Jouw Gecodeerde Kluis-Voucher!',
                    'content' => "Beste {$lockedCharacter->name},\n\nJe gecodeerde toegangsvoucher voor de MedicijnKluis staat klaar:\n\n👉 **{$codeStr}**\n\nVerzilver deze reeks bij de MedicijnKluis voor direct banktegoed, sterren en draaimunten!",
                    'type' => 'reward',
                ]);

                $lockedCharacter->save();

                return back()
                    ->with('shop_tab', 'sterren')
                    ->with('success', "Kluis-voucher {$codeStr} afgeleverd in je berichtenbox en kluisoverzicht voor {$cfg['cost']} Sterren ⭐!");
            }

            if ($validated['item_type'] === 'protection_24h') {
                $curProt = $lockedCharacter->protection_until && now()->lt($lockedCharacter->protection_until) ? $lockedCharacter->protection_until : now();
                $lockedCharacter->protection_until = $curProt->copy()->addHours(24);
                $lockedCharacter->save();

                return back()
                    ->with('shop_tab', 'sterren')
                    ->with('success', "🛡️ Paviljoenimmuniteit actief! Geen enkele rivaal kan je aanvallen tot {$lockedCharacter->protection_until->format('d-m-Y H:i')} ({$cfg['cost']} Sterren ⭐)!");
            }

            if ($validated['item_type'] === 'xxl_cases_3') {
                $lockedCharacter->xxl_cases = ($lockedCharacter->xxl_cases ?? 0) + 3;
                $lockedCharacter->unopened_bags = ($lockedCharacter->unopened_bags ?? 0) + 3;
                $lockedCharacter->save();

                return back()
                    ->with('shop_tab', 'sterren')
                    ->with('success', "💼 Lading geleverd: +3x Verzegelde XXL Transportkoffers klaargezet voor {$cfg['cost']} Sterren ⭐!");
            }

            if ($validated['item_type'] === 'package_master' || $validated['item_type'] === 'package_150') {
                $lockedCharacter->bank += 100000;
                $lockedCharacter->masks = ($lockedCharacter->masks ?? 0) + 10;
                $lockedCharacter->bullets = ($lockedCharacter->bullets ?? 0) + 500;
                $lockedCharacter->xxl_cases = ($lockedCharacter->xxl_cases ?? 0) + 2;
                $lockedCharacter->unopened_bags = ($lockedCharacter->unopened_bags ?? 0) + 2;

                for ($i = 0; $i < 5; $i++) {
                    VaultCode::create([
                        'code' => VaultCode::generateUniqueCode('MSTR'),
                        'reward_cash' => rand(5000, 10000),
                        'reward_stars' => rand(1, 2),
                        'reward_tokens' => 3,
                        'character_id' => $lockedCharacter->id,
                        'is_redeemed' => false,
                    ]);
                }

                $lockedCharacter->save();

                return back()
                    ->with('shop_tab', 'sterren')
                    ->with('success', "👑 KARTEL COMMANDOKIST GEOPEND! +€100.000 bankgeld, +10 Maskers, +500 Kogels, +2 XXL Koffers en 5 Kluiscodes bijgeschreven voor {$cfg['cost']} Sterren ⭐!");
            }

            if ($validated['item_type'] === 'vip_week') {
                $hasPermanentVip = ($lockedCharacter->is_vip && $lockedCharacter->vip_expires_at === null)
                    || $lockedCharacter->getVipTier() === 4;
                $lockedCharacter->is_vip = true;
                if (! $hasPermanentVip) {
                    $currentExpiry = ($lockedCharacter->vip_expires_at && $lockedCharacter->vip_expires_at->isFuture())
                        ? $lockedCharacter->vip_expires_at
                        : now();
                    $lockedCharacter->vip_expires_at = $currentExpiry->copy()->addDays(7);
                }
                $lockedCharacter->save();

                return back()
                    ->with('shop_tab', 'sterren')
                    ->with('success', "👑 ERE-STATUS TOEGEKEND! 7 Dagen VIP-privileges geactiveerd voor {$cfg['cost']} Sterren ⭐!");
            }

            return back()->with('shop_tab', 'sterren');
        });
    }

    /**
     * Purchase high-value bundles with Stars ⭐ (Codes, Koffers, Cash, Masks).
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
            if ($nameClean !== '' && ! str_contains(strtolower($nameClean), 'eigen account')) {
                $foundChar = Character::where('name', $nameClean)->first();
                if (! $foundChar) {
                    return back()->with('shop_tab', 'pakketten')->with('error', 'De opgegeven ontvanger kon niet worden gevonden.');
                }
                $recipientId = $foundChar->id;
            }
        }

        $bundle = self::BUNDLES[$resolvedBundleId];
        $quantity = (int) $validated['quantity'];
        $totalCost = $bundle['cost_stars'] * $quantity;

        return DB::transaction(function () use ($character, $recipientId, $bundle, $quantity, $totalCost): RedirectResponse {
            $isGift = ! empty($recipientId) && (int) $recipientId !== $character->id;
            $lockIds = $isGift ? [$character->id, (int) $recipientId] : [$character->id];
            sort($lockIds);
            $lockedChars = Character::whereIn('id', $lockIds)->orderBy('id')->lockForUpdate()->get()->keyBy('id');

            /** @var Character $lockedCharacter */
            $lockedCharacter = $lockedChars->get($character->id);

            if ($isGift && ! $lockedChars->has((int) $recipientId)) {
                return back()->with('shop_tab', 'pakketten')->with('error', 'De gekozen ontvanger bestaat niet.');
            }

            if (($lockedCharacter->stars ?? 0) < $totalCost) {
                return back()->with('shop_tab', 'pakketten')->with('error', "Sterrentegoed ontoereikend! Voor dit pakket zijn {$totalCost} Sterren ⭐ vereist (huidig saldo: {$lockedCharacter->stars}).");
            }

            $lockedCharacter->stars -= $totalCost;

            /** @var Character $target */
            $target = $isGift ? ($lockedChars->get((int) $recipientId) ?? Character::findOrFail($recipientId)) : $lockedCharacter;

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

            // Generate vault voucher codes for recipient in bulk chunks to avoid hundreds of individual INSERT queries
            $nowTs = now();
            $codeRows = [];
            $seenCodes = [];
            for ($i = 0; $i < $totalCodes; $i++) {
                do {
                    $codeStr = VaultCode::generateUniqueCode('BDL');
                } while (isset($seenCodes[$codeStr]));
                $seenCodes[$codeStr] = true;

                $codeRows[] = [
                    'code' => $codeStr,
                    'reward_cash' => rand(2500, 7500),
                    'reward_stars' => rand(1, 3),
                    'reward_tokens' => 2,
                    'character_id' => $target->id,
                    'is_redeemed' => false,
                    'created_at' => $nowTs,
                    'updated_at' => $nowTs,
                ];
            }
            foreach (array_chunk($codeRows, 200) as $chunk) {
                VaultCode::insert($chunk);
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
                    'subject' => "🎁 KOERIERSZENDING: {$bundle['name']} ontvangen!",
                    'content' => "Beste {$target->name},\n\n{$lockedCharacter->name} heeft een verzegelde voorraadzending op jouw naam laten afleveren:\n\n📦 **{$bundle['name']} ({$quantity}x)**\n• +€".number_format($totalCash, 0, ',', '.')." banktegoed 🏦\n• +{$totalBags} Koffers 🧳\n• +{$totalMasks} Maskers 🎭\n• +{$totalCodes} Kluiscodes 🔐\n\nDirect bijgeschreven op je dossier!",
                    'type' => 'reward',
                ]);

                return back()
                    ->with('shop_tab', 'pakketten')
                    ->with('success', "🎁 Zending afgeleverd! {$quantity}x {$bundle['name']} overgedragen aan {$target->name} voor {$totalCost} Sterren ⭐!");
            }

            $bonusMsg = $bonusBags > 0 ? " (inclusief +{$bonusBags} extra XXL bonuskoffer)!" : '!';

            return back()
                ->with('shop_tab', 'pakketten')
                ->with('success', "🎉 Pakketlevering voltooid: {$bundle['name']} vrijgegeven voor {$totalCost} Sterren ⭐{$bonusMsg} Bijschrijving: +€".number_format($totalCash, 0, ',', '.')." banktegoed, +{$totalBags} koffers, +{$totalMasks} maskers en +{$totalCodes} kluiscodes!");
        });
    }

    /**
     * Automatically consume items from inventory in 1 click until Energy, Health, or both reach 100%.
     */
    public function autoRefill(Request $request): RedirectResponse|JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;

        $targetStat = (string) $request->input('stat', 'both');
        if (! in_array($targetStat, ['energy', 'health', 'both'], true)) {
            $targetStat = 'both';
        }

        return DB::transaction(function () use ($request, $character, $targetStat): RedirectResponse|JsonResponse {
            /** @var Character $lockedCharacter */
            $lockedCharacter = Character::where('id', $character->id)->lockForUpdate()->first();
            if ($lockedCharacter->isComatose()) {
                $msg = 'Kritieke Code Blauw status! Dien eerst een '.Character::REVIVE_SERUM_NAME.' toe.';

                return ($request->wantsJson() || $request->ajax())
                    ? response()->json(['success' => false, 'message' => $msg], 422)
                    : back()->with('error', $msg);
            }
            $this->energyService->refresh($lockedCharacter);

            $needsEnergy = in_array($targetStat, ['energy', 'both'], true) && $lockedCharacter->energy < $lockedCharacter->max_energy;
            $needsHealth = in_array($targetStat, ['health', 'both'], true) && $lockedCharacter->health < $lockedCharacter->max_health;

            if (! $needsEnergy && ! $needsHealth) {
                $msg = $targetStat === 'energy'
                    ? "Je Energie staat al op het maximum ({$lockedCharacter->energy}/{$lockedCharacter->max_energy})!"
                    : ($targetStat === 'health'
                        ? "Je Gezondheid staat al op het maximum ({$lockedCharacter->health}/{$lockedCharacter->max_health} HP)!"
                        : 'Zowel Energie als Gezondheid staan al op 100%!');

                if ($request->wantsJson() || $request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'already_full' => true,
                        'message' => $msg,
                        'energy' => $lockedCharacter->energy,
                        'max_energy' => $lockedCharacter->max_energy,
                        'health' => $lockedCharacter->health,
                        'max_health' => $lockedCharacter->max_health,
                        'character' => [
                            'energy' => $lockedCharacter->energy,
                            'max_energy' => $lockedCharacter->max_energy,
                            'health' => $lockedCharacter->health,
                            'max_health' => $lockedCharacter->max_health,
                        ],
                    ], 200);
                }

                return back()->with('info', $msg);
            }

            $items = CharacterItem::where('character_id', $lockedCharacter->id)
                ->where('quantity', '>', 0)
                ->with('item')
                ->lockForUpdate()
                ->get()
                ->filter(function (CharacterItem $ci) {
                    $item = $ci->item;
                    if (! $item || $item->category === 'equipment') {
                        return false;
                    }
                    if (in_array($item->slug, ['bivakmuts-masker', 'smokkelpakketje', 'ijzerzaagje', 'eiwitshake-bodybuilder', 'multivitaminen-focus'], true)) {
                        return false;
                    }

                    return $item->energy_restore > 0 || $item->health_restore > 0 || in_array($item->slug, ['sigaretten', 'ritalin', 'telefoon', 'strip-oxazepam', 'strip-ritalin', 'haloperidol-forte', 'adrenaline-injectie', 'ehbo-noodverbanddoos'], true);
                });

            if ($items->isEmpty()) {
                $msg = 'Geen bruikbare rantsoenen of herstelartikelen in je plunjezak gevonden! Sla eerst voorraad in bij de Winkel.';
                if ($request->wantsJson() || $request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => $msg,
                        'energy' => $lockedCharacter->energy,
                        'max_energy' => $lockedCharacter->max_energy,
                        'health' => $lockedCharacter->health,
                        'max_health' => $lockedCharacter->max_health,
                        'character' => [
                            'energy' => $lockedCharacter->energy,
                            'max_energy' => $lockedCharacter->max_energy,
                            'health' => $lockedCharacter->health,
                            'max_health' => $lockedCharacter->max_health,
                        ],
                    ], 422);
                }

                return back()->with('error', $msg);
            }

            $energyBefore = $lockedCharacter->energy;
            $healthBefore = $lockedCharacter->health;
            $consumedSummary = [];

            foreach ($items as $ci) {
                $wantEnergyNow = in_array($targetStat, ['energy', 'both'], true) && $lockedCharacter->energy < $lockedCharacter->max_energy;
                $wantHealthNow = in_array($targetStat, ['health', 'both'], true) && $lockedCharacter->health < $lockedCharacter->max_health;

                if (! $wantEnergyNow && ! $wantHealthNow) {
                    break;
                }

                $item = $ci->item;
                $perEnergy = (int) ($item->energy_restore ?? 0);
                $perHealth = (int) ($item->health_restore ?? 0);

                if ($item->slug === 'sigaretten') {
                    $perEnergy = max($perEnergy, 20);
                    $perHealth = max($perHealth, 10);
                } elseif ($item->slug === 'ritalin') {
                    $perEnergy = max($perEnergy, 35);
                    $perHealth = max($perHealth, 15);
                } elseif ($item->slug === 'telefoon') {
                    $perEnergy = max($perEnergy, 50);
                    $perHealth = max($perHealth, 25);
                } elseif ($item->slug === 'strip-oxazepam') {
                    $perHealth = max($perHealth, 35);
                } elseif ($item->slug === 'strip-ritalin') {
                    $perEnergy = max($perEnergy, 45);
                } elseif ($item->slug === 'haloperidol-forte') {
                    $perEnergy = max($perEnergy, 20);
                    $perHealth = max($perHealth, 60);
                } elseif ($item->slug === 'adrenaline-injectie') {
                    $perEnergy = $lockedCharacter->max_energy;
                } elseif ($item->slug === 'ehbo-noodverbanddoos') {
                    $perHealth = $lockedCharacter->max_health;
                }

                $helpsEnergy = $wantEnergyNow && $perEnergy > 0;
                $helpsHealth = $wantHealthNow && $perHealth > 0;

                if (! $helpsEnergy && ! $helpsHealth) {
                    continue;
                }

                $energyDeficit = $helpsEnergy ? max(0, $lockedCharacter->max_energy - $lockedCharacter->energy) : 0;
                $healthDeficit = $helpsHealth ? max(0, $lockedCharacter->max_health - $lockedCharacter->health) : 0;

                $neededE = ($helpsEnergy && $perEnergy > 0) ? (int) ceil($energyDeficit / $perEnergy) : 0;
                $neededH = ($helpsHealth && $perHealth > 0) ? (int) ceil($healthDeficit / $perHealth) : 0;
                $needed = max(1, max($neededE, $neededH));
                $useQty = min((int) $ci->quantity, $needed);

                if ($useQty <= 0) {
                    continue;
                }

                if ($perEnergy > 0) {
                    $lockedCharacter->energy = min($lockedCharacter->max_energy, $lockedCharacter->energy + ($perEnergy * $useQty));
                }
                if ($perHealth > 0) {
                    $lockedCharacter->health = min($lockedCharacter->max_health, $lockedCharacter->health + ($perHealth * $useQty));
                }
                if ($item->slug === 'strip-oxazepam') {
                    $maxStat = $lockedCharacter->getMaxStatForLevel();
                    $curDis = (int) ($lockedCharacter->discipline ?? 10);
                    if ($curDis < $maxStat) {
                        $lockedCharacter->discipline = min($maxStat, $curDis + $useQty);
                    }
                } elseif ($item->slug === 'strip-ritalin') {
                    $this->levelService->addXp($lockedCharacter, 25 * $useQty);
                }

                $ci->quantity -= $useQty;
                if ($ci->quantity <= 0) {
                    $ci->delete();
                } else {
                    $ci->save();
                }

                $consumedSummary[] = "{$useQty}x {$item->name}";
            }

            if (empty($consumedSummary)) {
                $msg = $targetStat === 'energy'
                    ? 'Geen enkel artikel in je plunjezak herstelt Energie!'
                    : 'Geen enkel artikel in je plunjezak herstelt Gezondheid (HP)!';

                if ($request->wantsJson() || $request->ajax()) {
                    return response()->json(['success' => false, 'message' => $msg], 422);
                }

                return back()->with('error', $msg);
            }

            $lockedCharacter->save();
            Cache::forget("char_realtime_stats_{$lockedCharacter->id}");

            $energyGained = $lockedCharacter->energy - $energyBefore;
            $healthGained = $lockedCharacter->health - $healthBefore;

            $gains = [];
            if ($energyGained > 0) {
                $gains[] = "+{$energyGained} Energie ⚡ ({$lockedCharacter->energy}/{$lockedCharacter->max_energy})";
            }
            if ($healthGained > 0) {
                $gains[] = "+{$healthGained} HP ❤️ ({$lockedCharacter->health}/{$lockedCharacter->max_health})";
            }

            $msg = 'Noodrantsoen ingenomen ('.implode(', ', $consumedSummary).'): '.implode(' & ', $gains).'!';

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $msg,
                    'energy' => $lockedCharacter->energy,
                    'max_energy' => $lockedCharacter->max_energy,
                    'health' => $lockedCharacter->health,
                    'max_health' => $lockedCharacter->max_health,
                    'reload_dropdown' => true,
                    'character' => [
                        'energy' => $lockedCharacter->energy,
                        'max_energy' => $lockedCharacter->max_energy,
                        'health' => $lockedCharacter->health,
                        'max_health' => $lockedCharacter->max_health,
                    ],
                ]);
            }

            return back()->with('success', $msg);
        });
    }
}
