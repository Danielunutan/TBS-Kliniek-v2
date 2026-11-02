<?php

namespace App\Http\Controllers;

use App\Models\Character;
use App\Models\CharacterItem;
use App\Models\CharacterVehicle;
use App\Models\User;
use App\Models\VaultCode;
use App\Services\EconomyService;
use App\Services\EnergyService;
use App\Services\HalloweenService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DepotController extends Controller
{
    public function __construct(
        protected EnergyService $energyService,
        protected EconomyService $economyService,
    ) {}

    /**
     * Show the player's centralized Depot & Inventory view.
     */
    public function index(Request $request): View
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;
        $this->energyService->refresh($character);

        // 1. Persoonlijke Garage (Voertuigen)
        $ownedVehicles = CharacterVehicle::where('character_id', $character->id)
            ->with('vehicle')
            ->orderByDesc('is_active')
            ->get();

        $activeVehicle = $ownedVehicles->firstWhere('is_active', true) ?? $ownedVehicles->first();

        // 2. Persoonlijke Opbergplaats (Reguliere inventaris)
        $inventoryItems = CharacterItem::where('character_id', $character->id)
            ->where('quantity', '>', 0)
            ->whereHas('item', function ($q) {
                $q->where('slug', 'not like', 'halloween_%')
                    ->where('slug', 'not like', 'autumn_%')
                    ->where('category', '!=', 'special');
            })
            ->with('item')
            ->get();

        // 3. Persoonlijk Varia (Event Opslag & Zeldzame verzamelobjecten)
        $eventItems = CharacterItem::where('character_id', $character->id)
            ->where('quantity', '>', 0)
            ->whereHas('item', function ($q) {
                $q->where('slug', 'like', 'halloween_%')
                    ->orWhere('slug', 'like', 'autumn_%')
                    ->orWhere('category', 'special');
            })
            ->with('item')
            ->get();

        // 4. Capaciteiten berekenen
        $garageCapacity = $character->getGarageCapacity();
        $garageUsed = $character->getGarageUsed();
        $garagePercent = min(100, (int) round(($garageUsed / max(1, $garageCapacity)) * 100));

        $storageCapacity = $character->getStorageCapacity();
        $storageUsed = $character->getStorageUsed();
        $storagePercent = min(100, (int) round(($storageUsed / max(1, $storageCapacity)) * 100));

        // 5. Volgende level threshold info
        $level = $character->level ?? 1;
        $nextTierLevel = null;
        if ($level < 10) {
            $nextTierLevel = 10;
        } elseif ($level < 20) {
            $nextTierLevel = 20;
        } elseif ($level < 30) {
            $nextTierLevel = 30;
        } elseif ($level < 50) {
            $nextTierLevel = 50;
        }

        // 6. Event context & opslag metadata
        $isEventActive = HalloweenService::isEventActive();
        $timeRemaining = HalloweenService::getRemainingTime();
        $pumpkinSeeds = (int) ($character->pumpkin_seeds ?? 0);
        $unlockedBorders = $character->unlocked_borders ?? [];
        $equippedBorder = $character->equipped_border;
        $borderCatalog = HalloweenService::getBorders();
        $collectibleCatalog = array_filter(HalloweenService::SHOP_ITEMS, fn ($i) => $i['category'] === 'borders' ? false : $i['category'] === 'collectibles');
        $activePerks = $character->active_perks ?? [];
        $unredeemedCodes = VaultCode::where('character_id', $character->id)->where('is_redeemed', false)->get();

        $activeTab = session('tab', $request->input('tab', 'storage'));

        return view('game.depot.index', compact(
            'character',
            'ownedVehicles',
            'activeVehicle',
            'inventoryItems',
            'eventItems',
            'garageCapacity',
            'garageUsed',
            'garagePercent',
            'storageCapacity',
            'storageUsed',
            'storagePercent',
            'nextTierLevel',
            'isEventActive',
            'timeRemaining',
            'pumpkinSeeds',
            'unlockedBorders',
            'equippedBorder',
            'borderCatalog',
            'collectibleCatalog',
            'activePerks',
            'unredeemedCodes',
            'activeTab'
        ));
    }

    /**
     * Sell items from personal storage to free up space and earn cash.
     */
    public function sellItem(Request $request, CharacterItem $characterItem): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;

        if ($character->isImprisoned()) {
            return back()->with('error', 'Vanuit de isoleercel kun je geen voorwerpen uit je depot verkopen!');
        }

        if ($character->isComatose()) {
            return back()->with('error', 'Je verkeert in Code Blauw (Hartstilstand)! Dien eerst een '.Character::REVIVE_SERUM_NAME.' toe.');
        }

        return DB::transaction(function () use ($request, $characterItem, $character): RedirectResponse {
            /** @var CharacterItem|null $lockedItem */
            $lockedItem = CharacterItem::where('id', $characterItem->id)
                ->where('character_id', $character->id)
                ->lockForUpdate()
                ->first();

            if (! $lockedItem || $lockedItem->quantity < 1) {
                return back()->with('error', 'Dit voorwerp bevindt zich niet in je persoonlijke opbergplaats.');
            }

            $item = $lockedItem->item;
            if (! $item) {
                return back()->with('error', 'Item gegevens niet gevonden.');
            }

            if (str_starts_with((string) $item->slug, 'halloween_')) {
                return back()
                    ->with('tab', 'varia')
                    ->with('error', '🎃 Halloween-items zijn exclusieve, permanente verzamelobjecten (Niet meer haalbaar) en worden nooit verwijderd of verkocht!');
            }

            $requestedQty = max(1, (int) $request->input('quantity', 1));
            $quantity = min($requestedQty, $lockedItem->quantity);

            // Sell price: 60% of base price via authoritative Item helper
            $pricePerUnit = $item->getDepotSellPrice();
            $totalEarned = $pricePerUnit * $quantity;

            // Deduct inventory
            $lockedItem->quantity -= $quantity;
            if ($lockedItem->quantity <= 0) {
                $lockedItem->delete();
            } else {
                $lockedItem->save();
            }

            /** @var Character $lockedCharacter */
            $lockedCharacter = Character::where('id', $character->id)->lockForUpdate()->first();
            $this->economyService->addCash($lockedCharacter, $totalEarned);

            $formattedCash = number_format($totalEarned, 0, ',', '.');
            $returnTab = (str_starts_with((string) $item->slug, 'halloween_') || str_starts_with((string) $item->slug, 'autumn_') || $item->category === 'special')
                ? 'varia'
                : 'storage';

            return back()
                ->with('tab', $returnTab)
                ->with('success', "💰 Succesvol {$quantity}x {$item->name} verkocht voor €{$formattedCash}! Er is weer opbergruimte vrijgemaakt in je Depot.");
        });
    }

    /**
     * Sell heist masks from personal storage to free up space and earn cash.
     */
    public function sellMasks(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;

        if ($character->isImprisoned()) {
            return back()->with('error', 'Vanuit de isoleercel kun je geen maskers uit je depot verkopen!');
        }

        if ($character->isComatose()) {
            return back()->with('error', 'Je verkeert in Code Blauw (Hartstilstand)! Dien eerst een '.Character::REVIVE_SERUM_NAME.' toe.');
        }

        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        return DB::transaction(function () use ($character, $validated): RedirectResponse {
            /** @var Character $lockedCharacter */
            $lockedCharacter = Character::where('id', $character->id)->lockForUpdate()->first();

            $currentMasks = (int) ($lockedCharacter->masks ?? 0);
            if ($currentMasks < 1) {
                return back()->with('error', 'Je hebt geen maskers om te verkopen.');
            }

            $quantity = min((int) $validated['quantity'], $currentMasks);
            $pricePerMask = 1500; // 60% of €2.500 shop price, consistent across Depot and Vault
            $totalEarned = $pricePerMask * $quantity;

            $lockedCharacter->masks -= $quantity;
            $lockedCharacter->save();
            $this->economyService->addCash($lockedCharacter, $totalEarned);

            $formattedCash = number_format($totalEarned, 0, ',', '.');
            $used = $lockedCharacter->getStorageUsed();
            $cap = $lockedCharacter->getStorageCapacity();

            return back()
                ->with('tab', 'storage')
                ->with('success', "🎭 Succesvol {$quantity}x Overval Masker(s) verkocht aan de heler voor €{$formattedCash}! Je opbergplaats heeft nu weer ruimte ({$used}/{$cap} plekken).");
        });
    }
}
