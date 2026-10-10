<?php

namespace App\Http\Controllers;

use App\Models\CharacterItem;
use App\Models\Item;
use App\Models\User;
use App\Services\EconomyService;
use App\Services\EnergyService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ShopController extends Controller
{
    public function __construct(
        protected EconomyService $economyService,
        protected EnergyService $energyService,
    ) {}

    /**
     * Show shop and inventory.
     */
    public function index(): View
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;
        $this->energyService->refresh($character);

        $items = Item::where('is_buyable', true)->get();
        $inventory = CharacterItem::where('character_id', $character->id)->with('item')->get();

        return view('game.shop', compact('character', 'items', 'inventory'));
    }

    /**
     * Buy an item.
     */
    public function buy(Request $request, Item $item): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;

        if ($character->cash < $item->price_kc) {
            return back()->with('error', "Niet genoeg contant geld! {$item->name} kost {$item->price_kc} KC.");
        }

        if (! $this->economyService->deductCash($character, $item->price_kc)) {
            return back()->with('error', 'Betaling mislukt.');
        }

        $inv = CharacterItem::firstOrNew([
            'character_id' => $character->id,
            'item_id' => $item->id,
        ]);
        $inv->quantity = ($inv->quantity ?? 0) + 1;
        $inv->save();

        return back()->with('success', "Je hebt {$item->name} gekocht voor {$item->price_kc} KC!");
    }

    /**
     * Consume an item from inventory.
     */
    public function consume(Request $request, CharacterItem $inventoryItem): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;

        if ($inventoryItem->character_id !== $character->id || $inventoryItem->quantity < 1) {
            return back()->with('error', 'Voorwerp niet beschikbaar in je inventaris.');
        }

        $item = $inventoryItem->item;

        if ($item->energy_restore > 0) {
            $character->energy = min($character->max_energy, $character->energy + $item->energy_restore);
        }

        if ($item->health_restore > 0) {
            $character->health = min($character->max_health, $character->health + $item->health_restore);
        }

        $character->save();

        $inventoryItem->quantity--;
        if ($inventoryItem->quantity <= 0) {
            $inventoryItem->delete();
        } else {
            $inventoryItem->save();
        }

        return back()->with('success', "Je hebt {$item->name} genuttigd! (+{$item->energy_restore} energie, +{$item->health_restore} gezondheid)");
    }
}
