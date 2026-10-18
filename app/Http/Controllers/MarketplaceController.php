<?php

namespace App\Http\Controllers;

use App\Models\CharacterItem;
use App\Models\CharacterVehicle;
use App\Models\GameMessage;
use App\Models\Gang;
use App\Models\GangProperty;
use App\Models\Item;
use App\Models\MarketplaceListing;
use App\Models\User;
use App\Models\VaultCode;
use App\Services\EconomyService;
use App\Services\EnergyService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class MarketplaceController extends Controller
{
    public function __construct(
        protected EconomyService $economy,
        protected EnergyService $energy,
    ) {}

    /**
     * Display the clinic marketplace.
     */
    public function index(Request $request): View
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;
        $this->energy->refresh($character);

        $filterType = $request->query('type', 'all');

        $query = MarketplaceListing::with('seller')
            ->where('is_sold', false);

        if ($filterType !== 'all') {
            $query->where('item_type', $filterType);
        }

        $listings = $query->latest()->get();

        $myListings = MarketplaceListing::where('seller_id', $character->id)
            ->where('is_sold', false)
            ->latest()
            ->get();

        $myVaultCodes = VaultCode::where('character_id', $character->id)
            ->where('is_redeemed', false)
            ->get();

        // Assets available to sell from Depot, Garage and Panden
        $inventoryItems = CharacterItem::where('character_id', $character->id)
            ->where('quantity', '>', 0)
            ->with('item')
            ->get();

        $ownedVehicles = CharacterVehicle::where('character_id', $character->id)
            ->where('is_for_sale', false)
            ->with('vehicle')
            ->orderByDesc('is_active')
            ->get();

        $gangProperties = ($character->gang && $character->gang->isOfficer($character))
            ? $character->gang->properties()->where('is_for_sale', false)->get()
            : collect();

        return view('game.marketplace.index', compact(
            'character',
            'listings',
            'myListings',
            'myVaultCodes',
            'inventoryItems',
            'ownedVehicles',
            'gangProperties',
            'filterType'
        ));
    }

    /**
     * Create a new marketplace listing.
     */
    public function store(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;

        if ($character->isImprisoned()) {
            return back()->with('error', 'Vanuit de isoleercel kun je niet handelen op de Marktplaats!');
        }

        $validated = $request->validate([
            'item_type' => ['required', 'string', 'in:stars,packages,cash,vault_codes,masks,pumpkin_seeds,depot_item,vehicle,property'],
            'amount' => ['required', 'integer', 'min:1'],
            'price' => ['required', 'integer', 'min:10', 'max:50000000'],
            'currency' => ['required', 'string', 'in:cash,stars'],
            'vault_code_id' => ['nullable', 'integer', 'exists:vault_codes,id'],
            'character_item_id' => ['nullable', 'integer', 'exists:character_items,id'],
            'character_vehicle_id' => ['nullable', 'integer', 'exists:character_vehicles,id'],
            'gang_property_id' => ['nullable', 'integer', 'exists:gang_properties,id'],
        ]);

        $itemType = $validated['item_type'];
        $amount = (int) $validated['amount'];
        $price = (int) $validated['price'];
        $currency = $validated['currency'];
        $relatedId = null;
        $metadata = null;

        // 1. DEPOT ITEMS
        if ($itemType === 'depot_item') {
            $charItem = CharacterItem::where('id', $request->input('character_item_id'))
                ->where('character_id', $character->id)
                ->with('item')
                ->first();

            if (! $charItem || ! $charItem->item) {
                return back()->with('error', 'Selecteer een geldig item uit je depot.');
            }

            if ($charItem->quantity < $amount) {
                return back()->with('error', "Je hebt slechts {$charItem->quantity}x {$charItem->item->name} in je depot.");
            }

            $charItem->quantity -= $amount;
            if ($charItem->quantity <= 0) {
                $charItem->delete();
            } else {
                $charItem->save();
            }

            $title = "{$amount}x {$charItem->item->name}";
            $relatedId = $charItem->item_id;
            $metadata = [
                'item_id' => $charItem->item_id,
                'item_name' => $charItem->item->name,
                'rarity' => $charItem->item->rarity ?? 'common',
                'category' => $charItem->item->category ?? 'item',
                'image' => $charItem->item->image_path,
            ];
        }

        // 2. GARAGE VEHICLES
        elseif ($itemType === 'vehicle') {
            $charVehicle = CharacterVehicle::where('id', $request->input('character_vehicle_id'))
                ->where('character_id', $character->id)
                ->with('vehicle')
                ->first();

            if (! $charVehicle || ! $charVehicle->vehicle) {
                return back()->with('error', 'Selecteer een geldig voertuig uit je garage.');
            }

            // Put vehicle into escrow (is_for_sale true, deactivated)
            $charVehicle->is_for_sale = true;
            $charVehicle->is_active = false;
            $charVehicle->save();

            $title = "{$charVehicle->vehicle->name} (Conditie: {$charVehicle->current_condition}%)";
            $amount = 1;
            $relatedId = $charVehicle->id;
            $metadata = [
                'vehicle_id' => $charVehicle->vehicle_id,
                'vehicle_name' => $charVehicle->vehicle->name,
                'condition' => $charVehicle->current_condition,
                'speed' => $charVehicle->effective_speed,
                'image' => $charVehicle->vehicle->image_path,
            ];
        }

        // 3. BANDE PANDEN
        elseif ($itemType === 'property') {
            if (! $character->gang || ! $character->gang->isOfficer($character)) {
                return back()->with('error', 'Alleen de bendeleider of beheerders kunnen panden verkopen op de marktplaats!');
            }

            $gangProperty = GangProperty::where('id', $request->input('gang_property_id'))
                ->where('gang_id', $character->gang_id)
                ->first();

            if (! $gangProperty) {
                return back()->with('error', 'Selecteer een geldig bende pand in bezit van jouw bende.');
            }

            // Put property into escrow (is_for_sale true, idle)
            $oldGangId = $character->gang_id;
            $gangName = $character->gang->name;
            $gangProperty->is_for_sale = true;
            $gangProperty->production_status = 'idle';
            $gangProperty->save();

            $title = "Bende Pand: {$gangProperty->name}";
            $amount = 1;
            $relatedId = $gangProperty->id;
            $metadata = [
                'property_id' => $gangProperty->id,
                'property_key' => $gangProperty->property_key,
                'property_name' => $gangProperty->name,
                'seller_gang_id' => $oldGangId,
                'seller_gang_name' => $gangName,
                'category' => $gangProperty->type,
            ];
        }

        // 4. STARS
        elseif ($itemType === 'stars') {
            if (($character->stars ?? 0) < $amount) {
                return back()->with('error', "Je hebt niet genoeg Sterren ⭐ (je hebt er {$character->stars}).");
            }
            $character->stars -= $amount;
            $character->save();
            $title = "{$amount}x Exclusieve Sterren ⭐";
        }

        // 5. PACKAGES
        elseif ($itemType === 'packages') {
            if (($character->unopened_bags ?? 0) < $amount) {
                return back()->with('error', "Je hebt niet genoeg ongeopende smokkeltassen/koffers (je hebt er {$character->unopened_bags}).");
            }
            $character->unopened_bags -= $amount;
            $character->save();
            $title = "{$amount}x Smokkelkoffer 🧳";
        }

        // 6. MASKS
        elseif ($itemType === 'masks') {
            if (($character->masks ?? 0) < $amount) {
                return back()->with('error', "Je hebt niet genoeg Maskers 🎭 (je hebt er {$character->masks}).");
            }
            $character->masks -= $amount;
            $character->save();
            $title = "{$amount}x Masker 🎭";
        }

        // 7. PUMPKIN SEEDS
        elseif ($itemType === 'pumpkin_seeds') {
            if (($character->pumpkin_seeds ?? 0) < $amount) {
                return back()->with('error', "Je hebt niet genoeg Pompoenpitten 🎃 (je hebt er {$character->pumpkin_seeds}).");
            }
            $character->pumpkin_seeds -= $amount;
            $character->save();
            $title = "{$amount}x Halloween Pompoenpitten 🎃";
        }

        // 8. CASH
        elseif ($itemType === 'cash') {
            if ($character->cash < $amount) {
                return back()->with('error', 'Je hebt niet genoeg contant geld om aan te bieden.');
            }
            $character->cash -= $amount;
            $character->save();
            $title = '€'.number_format($amount, 0, ',', '.').' Contant Geld';
        }

        // 9. VAULT CODES
        elseif ($itemType === 'vault_codes') {
            $vaultCode = VaultCode::where('id', $request->input('vault_code_id'))
                ->where('character_id', $character->id)
                ->where('is_redeemed', false)
                ->first();

            if (! $vaultCode) {
                return back()->with('error', 'Selecteer een geldige kluiscode uit jouw bezit.');
            }

            // Transfer code ownership to escrow
            $vaultCode->character_id = null;
            $vaultCode->save();
            $title = "Geheime Kluiscode Voucher ({$vaultCode->code})";
            $amount = 1;
        } else {
            return back()->with('error', 'Ongeldig item type.');
        }

        MarketplaceListing::create([
            'seller_id' => $character->id,
            'item_type' => $itemType,
            'related_id' => $relatedId,
            'title' => $title,
            'amount' => $amount,
            'price' => $price,
            'currency' => $currency,
            'metadata' => $metadata,
            'is_sold' => false,
        ]);

        return back()->with('success', "Jouw aanbod '{$title}' staat nu online op de Marktplaats!");
    }

    /**
     * Buy an item from the marketplace.
     */
    public function buy(MarketplaceListing $listing): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $buyer = $user->character;

        if ($buyer->isImprisoned()) {
            return back()->with('error', 'Vanuit de isoleercel kun je geen aankopen doen!');
        }

        if ($listing->is_sold) {
            return back()->with('error', 'Dit artikel is zojuist al verkocht aan een andere patiënt.');
        }

        if ($listing->seller_id === $buyer->id) {
            return back()->with('error', 'Je kunt je eigen marktaanbod niet kopen! Gebruik annuleren als je het terug wilt.');
        }

        // Specific pre-purchase validations
        if ($listing->item_type === 'vehicle') {
            if ($buyer->getGarageUsed() >= $buyer->getGarageCapacity()) {
                return back()->with('error', 'Je garage zit vol! Verhoog je capaciteit of verkoop eerst een wagen om plaats te maken.');
            }
        }

        if ($listing->item_type === 'property') {
            if (! $buyer->gang || ! $buyer->gang->isOfficer($buyer)) {
                return back()->with('error', 'Je moet leider of beheerder zijn van een bende om een bende pand te kopen op de marktplaats!');
            }

            $propKey = $listing->metadata['property_key'] ?? null;
            if ($buyer->gang->properties()->where('property_key', $propKey)->exists()) {
                return back()->with('error', 'Jouw bende bezit dit type pand al!');
            }
        }

        $seller = $listing->seller;

        // Payment check & execution
        if ($listing->currency === 'cash') {
            if ($buyer->cash < $listing->price) {
                return back()->with('error', 'Je hebt minimaal €'.number_format($listing->price, 0, ',', '.').' contant nodig voor deze aankoop.');
            }
            $buyer->cash -= $listing->price;

            // Property sale proceeds can be routed to the selling gang treasury if specified
            if ($listing->item_type === 'property' && isset($listing->metadata['seller_gang_id'])) {
                $sellerGang = Gang::find($listing->metadata['seller_gang_id']);
                if ($sellerGang) {
                    $sellerGang->increment('treasury', $listing->price);
                } elseif ($seller) {
                    $seller->bank += $listing->price;
                    $seller->save();
                }
            } elseif ($seller) {
                $seller->bank += $listing->price;
                $seller->save();
            }
        } elseif ($listing->currency === 'stars') {
            if (($buyer->stars ?? 0) < $listing->price) {
                return back()->with('error', "Je hebt minimaal {$listing->price} Sterren ⭐ nodig voor deze aankoop.");
            }
            $buyer->stars -= $listing->price;

            if ($seller) {
                $seller->stars = ($seller->stars ?? 0) + $listing->price;
                $seller->save();
            }
        }

        // Deliver purchased item to buyer
        if ($listing->item_type === 'depot_item') {
            $charItem = CharacterItem::firstOrCreate([
                'character_id' => $buyer->id,
                'item_id' => $listing->related_id,
            ], [
                'quantity' => 0,
                'is_equipped' => false,
            ]);
            $charItem->quantity += $listing->amount;
            $charItem->save();
        } elseif ($listing->item_type === 'vehicle') {
            $charVehicle = CharacterVehicle::where('id', $listing->related_id)->first();

            if ($charVehicle) {
                $charVehicle->character_id = $buyer->id;
                $charVehicle->is_for_sale = false;
                $charVehicle->is_active = false;
                $charVehicle->save();
            }
        } elseif ($listing->item_type === 'property') {
            $gangProperty = GangProperty::where('id', $listing->related_id)->first();

            if ($gangProperty && $buyer->gang_id) {
                $gangProperty->gang_id = $buyer->gang_id;
                $gangProperty->is_for_sale = false;
                $gangProperty->production_status = 'idle';
                $gangProperty->save();
            }
        } elseif ($listing->item_type === 'stars') {
            $buyer->stars = ($buyer->stars ?? 0) + $listing->amount;
        } elseif ($listing->item_type === 'packages') {
            $buyer->unopened_bags = ($buyer->unopened_bags ?? 0) + $listing->amount;
        } elseif ($listing->item_type === 'masks') {
            $buyer->masks = ($buyer->masks ?? 0) + $listing->amount;
        } elseif ($listing->item_type === 'pumpkin_seeds') {
            $buyer->pumpkin_seeds = ($buyer->pumpkin_seeds ?? 0) + $listing->amount;
        } elseif ($listing->item_type === 'cash') {
            $buyer->cash += $listing->amount;
        } elseif ($listing->item_type === 'vault_codes') {
            $code = VaultCode::whereNull('character_id')->where('is_redeemed', false)->first();
            if ($code) {
                $code->character_id = $buyer->id;
                $code->save();
            } else {
                VaultCode::create([
                    'code' => 'KLUIS-'.strtoupper(substr(md5(uniqid((string) mt_rand(), true)), 0, 8)),
                    'reward_cash' => 2500,
                    'reward_stars' => 2,
                    'reward_tokens' => 4,
                    'character_id' => $buyer->id,
                    'is_redeemed' => false,
                ]);
            }
        }

        $buyer->save();

        $listing->is_sold = true;
        $listing->buyer_id = $buyer->id;
        $listing->save();

        if ($seller) {
            $currName = $listing->currency === 'stars' ? "{$listing->price} Sterren ⭐" : '€'.number_format($listing->price, 0, ',', '.').' (wit op de bank)';
            GameMessage::create([
                'recipient_id' => $seller->id,
                'sender_id' => $buyer->id,
                'sender_name' => 'Marktplaats Systeem',
                'subject' => "🛒 Artikel Verkocht: {$listing->title}",
                'content' => "Goed nieuws!\n\nPatiënt {$buyer->name} heeft zojuist jouw aanbod '{$listing->title}' gekocht voor {$currName}.",
                'type' => 'trade',
            ]);
        }

        return back()->with('success', "Aankoop voltooid! Je hebt '{$listing->title}' ontvangen.");
    }

    /**
     * Cancel an active listing and reclaim assets.
     */
    public function cancel(MarketplaceListing $listing): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;

        if ($listing->seller_id !== $character->id) {
            return back()->with('error', 'Dit is niet jouw aanbieding!');
        }

        if ($listing->is_sold) {
            return back()->with('error', 'Dit artikel is reeds verkocht.');
        }

        // Refund escrowed assets
        if ($listing->item_type === 'depot_item') {
            $charItem = CharacterItem::firstOrCreate([
                'character_id' => $character->id,
                'item_id' => $listing->related_id,
            ], [
                'quantity' => 0,
                'is_equipped' => false,
            ]);
            $charItem->quantity += $listing->amount;
            $charItem->save();
        } elseif ($listing->item_type === 'vehicle') {
            $charVehicle = CharacterVehicle::where('id', $listing->related_id)->first();
            if ($charVehicle) {
                $charVehicle->is_for_sale = false;
                $charVehicle->save();
            }
        } elseif ($listing->item_type === 'property') {
            $gangProperty = GangProperty::where('id', $listing->related_id)->first();
            if ($gangProperty) {
                $gangProperty->is_for_sale = false;
                $gangProperty->save();
            }
        } elseif ($listing->item_type === 'stars') {
            $character->stars = ($character->stars ?? 0) + $listing->amount;
            $character->save();
        } elseif ($listing->item_type === 'packages') {
            $character->unopened_bags = ($character->unopened_bags ?? 0) + $listing->amount;
            $character->save();
        } elseif ($listing->item_type === 'masks') {
            $character->masks = ($character->masks ?? 0) + $listing->amount;
            $character->save();
        } elseif ($listing->item_type === 'pumpkin_seeds') {
            $character->pumpkin_seeds = ($character->pumpkin_seeds ?? 0) + $listing->amount;
            $character->save();
        } elseif ($listing->item_type === 'cash') {
            $character->cash += $listing->amount;
            $character->save();
        } elseif ($listing->item_type === 'vault_codes') {
            $code = VaultCode::whereNull('character_id')->where('is_redeemed', false)->first();
            if ($code) {
                $code->character_id = $character->id;
                $code->save();
            }
        }

        $listing->delete();

        return back()->with('success', 'Aanbieding ingetrokken. Je items/bezittingen zijn teruggeplaatst in je bezit.');
    }
}
