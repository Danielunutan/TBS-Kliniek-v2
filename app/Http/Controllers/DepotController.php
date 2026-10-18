<?php

namespace App\Http\Controllers;

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
            ->whereHas('item', function ($q) {
                $q->where('slug', 'not like', 'halloween_%')
                    ->whereNotIn('category', ['special', 'collectible']);
            })
            ->with('item')
            ->get();

        // 3. Persoonlijk Varia (Event Opslag & Zeldzame verzamelobjecten)
        $eventItems = CharacterItem::where('character_id', $character->id)
            ->whereHas('item', function ($q) {
                $q->where('slug', 'like', 'halloween_%')
                    ->orWhereIn('category', ['special', 'collectible']);
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
        $borderCatalog = array_filter(HalloweenService::SHOP_ITEMS, fn ($i) => $i['category'] === 'borders');
        $collectibleCatalog = array_filter(HalloweenService::SHOP_ITEMS, fn ($i) => $i['category'] === 'collectibles');
        $activePerks = $character->active_perks ?? [];
        $unredeemedCodes = VaultCode::where('character_id', $character->id)->where('is_redeemed', false)->get();

        $activeTab = $request->input('tab', 'storage');

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

        if ($characterItem->character_id !== $character->id || $characterItem->quantity < 1) {
            return back()->with('error', 'Dit voorwerp bevindt zich niet in je persoonlijke opbergplaats.');
        }

        $item = $characterItem->item;
        if (! $item) {
            return back()->with('error', 'Item gegevens niet gevonden.');
        }

        $requestedQty = max(1, (int) $request->input('quantity', 1));
        $quantity = min($requestedQty, $characterItem->quantity);

        // Sell price: approx 60% of base price, minimum €500 per unit
        $pricePerUnit = max(500, (int) round(($item->price_kc > 0 ? $item->price_kc : 1000) * 0.6));
        $totalEarned = $pricePerUnit * $quantity;

        // Deduct inventory
        $characterItem->quantity -= $quantity;
        if ($characterItem->quantity <= 0) {
            $characterItem->delete();
        } else {
            $characterItem->save();
        }

        // Add cash to player
        $character->cash = ($character->cash ?? 0) + $totalEarned;
        $character->save();

        $formattedCash = number_format($totalEarned, 0, ',', '.');

        return back()
            ->with('tab', 'storage')
            ->with('success', "💰 Succesvol {$quantity}x {$item->name} verkocht voor €{$formattedCash}! Er is weer opbergruimte vrijgemaakt in je Depot.");
    }
}
