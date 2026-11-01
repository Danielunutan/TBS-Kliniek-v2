<?php

namespace App\Http\Controllers;

use App\Models\Character;
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
use Illuminate\Support\Facades\DB;
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

        $listings = $query->latest()->paginate(25)->withQueryString();

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
            ->where('in_gang_garage', false)
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

        if ($character->isComatose()) {
            return back()->with('error', 'Je verkeert in Code Blauw (Hartstilstand)! Dien eerst een '.Character::REVIVE_SERUM_NAME.' toe.');
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
        $title = '';

        if ($itemType === 'cash' && $currency === 'cash') {
            return back()->with('error', 'Contant geld kan alleen tegen Sterren worden aangeboden op de Marktplaats.');
        }

        try {
            DB::transaction(function () use ($request, $character, $itemType, &$amount, $price, $currency, &$title): void {
                /** @var Character $lockedCharacter */
                $lockedCharacter = Character::where('id', $character->id)->lockForUpdate()->first();
                $relatedId = null;
                $metadata = null;

                // 1. DEPOT ITEMS
                if ($itemType === 'depot_item') {
                    $charItem = CharacterItem::where('id', $request->input('character_item_id'))
                        ->where('character_id', $lockedCharacter->id)
                        ->with('item')
                        ->lockForUpdate()
                        ->first();

                    if (! $charItem || ! $charItem->item) {
                        throw new \RuntimeException('Selecteer een geldig item uit je depot.');
                    }

                    // Unique equipment only stacks to 1 on purchase; cap listing amount to 1 so surplus copies are not lost.
                    if ($charItem->item->category === 'equipment') {
                        $amount = 1;
                    }

                    if ($charItem->quantity < $amount) {
                        throw new \RuntimeException("Je hebt slechts {$charItem->quantity}x {$charItem->item->name} in je depot.");
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
                        ->where('character_id', $lockedCharacter->id)
                        ->where('is_for_sale', false)
                        ->where('in_gang_garage', false)
                        ->with('vehicle')
                        ->lockForUpdate()
                        ->first();

                    if (! $charVehicle || ! $charVehicle->vehicle) {
                        throw new \RuntimeException('Selecteer een geldig voertuig uit je garage dat nog niet te koop staat.');
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

                // 3. BENDE PANDEN
                elseif ($itemType === 'property') {
                    if (! $lockedCharacter->gang || ! $lockedCharacter->gang->isOfficer($lockedCharacter)) {
                        throw new \RuntimeException('Alleen de bendeleider of beheerders kunnen panden verkopen op de marktplaats!');
                    }

                    // Star payments go to the seller personally, so a gang asset sold for stars would move shared value to one officer.
                    if ($currency !== 'cash') {
                        throw new \RuntimeException('Bende panden kunnen alleen voor contant geld verkocht worden; de opbrengst gaat naar de bendekas.');
                    }

                    $gangProperty = GangProperty::where('id', $request->input('gang_property_id'))
                        ->where('gang_id', $lockedCharacter->gang_id)
                        ->where('is_for_sale', false)
                        ->lockForUpdate()
                        ->first();

                    if (! $gangProperty) {
                        throw new \RuntimeException('Selecteer een geldig bende pand in bezit van jouw bende dat nog niet te koop staat.');
                    }

                    // Put property into escrow (is_for_sale true, idle)
                    $oldGangId = $lockedCharacter->gang_id;
                    $gangName = $lockedCharacter->gang->name;
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
                    if (($lockedCharacter->stars ?? 0) < $amount) {
                        throw new \RuntimeException("Je hebt niet genoeg Sterren ⭐ (je hebt er {$lockedCharacter->stars}).");
                    }
                    $lockedCharacter->stars -= $amount;
                    $lockedCharacter->save();
                    $title = "{$amount}x Exclusieve Sterren ⭐";
                }

                // 5. PACKAGES
                elseif ($itemType === 'packages') {
                    if (($lockedCharacter->unopened_bags ?? 0) < $amount) {
                        throw new \RuntimeException("Je hebt niet genoeg ongeopende smokkeltassen/koffers (je hebt er {$lockedCharacter->unopened_bags}).");
                    }
                    $lockedCharacter->unopened_bags -= $amount;
                    $lockedCharacter->save();
                    $title = "{$amount}x Smokkelkoffer 🧳";
                }

                // 6. MASKS
                elseif ($itemType === 'masks') {
                    if (($lockedCharacter->masks ?? 0) < $amount) {
                        throw new \RuntimeException("Je hebt niet genoeg Maskers 🎭 (je hebt er {$lockedCharacter->masks}).");
                    }
                    $lockedCharacter->masks -= $amount;
                    $lockedCharacter->save();
                    $title = "{$amount}x Masker 🎭";
                }

                // 7. PUMPKIN SEEDS
                elseif ($itemType === 'pumpkin_seeds') {
                    if (($lockedCharacter->pumpkin_seeds ?? 0) < $amount) {
                        throw new \RuntimeException("Je hebt niet genoeg Pompoenpitten 🎃 (je hebt er {$lockedCharacter->pumpkin_seeds}).");
                    }
                    $lockedCharacter->pumpkin_seeds -= $amount;
                    $lockedCharacter->save();
                    $title = "{$amount}x Halloween Pompoenpitten 🎃";
                }

                // 8. CASH
                elseif ($itemType === 'cash') {
                    if ($lockedCharacter->cash < $amount) {
                        throw new \RuntimeException('Je hebt niet genoeg contant geld om aan te bieden.');
                    }
                    $lockedCharacter->cash -= $amount;
                    $lockedCharacter->save();
                    $title = '€'.number_format($amount, 0, ',', '.').' Contant Geld';
                }

                // 9. VAULT CODES
                elseif ($itemType === 'vault_codes') {
                    $vaultCode = VaultCode::where('id', $request->input('vault_code_id'))
                        ->where('character_id', $lockedCharacter->id)
                        ->where('is_redeemed', false)
                        ->lockForUpdate()
                        ->first();

                    if (! $vaultCode) {
                        throw new \RuntimeException('Selecteer een geldige kluiscode uit jouw bezit.');
                    }

                    // Transfer code ownership to escrow. The title must not contain the code text: listings are public.
                    $vaultCode->character_id = null;
                    $vaultCode->save();
                    $title = "Geheime Kluiscode Voucher (#{$vaultCode->id})";
                    $amount = 1;
                    $relatedId = $vaultCode->id;
                } else {
                    throw new \RuntimeException('Ongeldig item type.');
                }

                MarketplaceListing::create([
                    'seller_id' => $lockedCharacter->id,
                    'item_type' => $itemType,
                    'related_id' => $relatedId,
                    'title' => $title,
                    'amount' => $amount,
                    'price' => $price,
                    'currency' => $currency,
                    'metadata' => $metadata,
                    'is_sold' => false,
                ]);
            });
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

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

        if ($buyer->isComatose()) {
            return back()->with('error', 'Je verkeert in Code Blauw (Hartstilstand)! Dien eerst een '.Character::REVIVE_SERUM_NAME.' toe.');
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

        try {
            DB::transaction(function () use ($listing, $buyer): void {
                /** @var MarketplaceListing|null $lockedListing */
                $lockedListing = MarketplaceListing::where('id', $listing->id)->lockForUpdate()->first();

                if (! $lockedListing || $lockedListing->is_sold) {
                    throw new \RuntimeException('Dit artikel is zojuist al verkocht aan een andere patiënt.');
                }

                $lockIds = array_values(array_filter(array_unique([$buyer->id, $lockedListing->seller_id])));
                sort($lockIds);
                $lockedCharacters = Character::whereIn('id', $lockIds)->orderBy('id')->lockForUpdate()->get()->keyBy('id');

                /** @var Character $lockedBuyer */
                $lockedBuyer = $lockedCharacters->get($buyer->id);
                /** @var Character|null $lockedSeller */
                $lockedSeller = $lockedListing->seller_id ? $lockedCharacters->get($lockedListing->seller_id) : null;

                if ($lockedListing->item_type === 'depot_item') {
                    $listedItem = Item::find($lockedListing->related_id);
                    if ($listedItem && $listedItem->category === 'equipment') {
                        $alreadyOwnsEquipment = CharacterItem::where('character_id', $lockedBuyer->id)
                            ->where('item_id', $listedItem->id)
                            ->where('quantity', '>=', 1)
                            ->lockForUpdate()
                            ->exists();

                        if ($alreadyOwnsEquipment) {
                            throw new \RuntimeException("Je bezit deze unieke uitrusting ({$listedItem->name}) al in je rugzak (maximaal 1 per soort)!");
                        }
                    } elseif (! $lockedBuyer->canAddStorageItem($lockedListing->amount)) {
                        throw new \RuntimeException('Je opbergplaats zit vol! Maak eerst ruimte in je depot.');
                    }
                } elseif ($lockedListing->item_type === 'vehicle') {
                    if ($lockedBuyer->getGarageUsed() >= $lockedBuyer->getGarageCapacity()) {
                        throw new \RuntimeException('Je garage zit vol! Verhoog je capaciteit of verkoop eerst een wagen om plaats te maken.');
                    }
                }

                // Payment check & deduction
                if ($lockedListing->currency === 'cash') {
                    if ($lockedBuyer->cash < $lockedListing->price) {
                        throw new \RuntimeException('Je hebt minimaal €'.number_format($lockedListing->price, 0, ',', '.').' contant nodig voor deze aankoop.');
                    }
                    $lockedBuyer->cash -= $lockedListing->price;
                } elseif ($lockedListing->currency === 'stars') {
                    if (($lockedBuyer->stars ?? 0) < $lockedListing->price) {
                        throw new \RuntimeException("Je hebt minimaal {$lockedListing->price} Sterren ⭐ nodig voor deze aankoop.");
                    }
                    $lockedBuyer->stars -= $lockedListing->price;
                }

                // Process seller payout
                if ($lockedSeller) {
                    if ($lockedListing->currency === 'cash') {
                        if ($lockedListing->item_type === 'property' && isset($lockedListing->metadata['seller_gang_id'])) {
                            $sellerGang = Gang::find($lockedListing->metadata['seller_gang_id']);
                            if ($sellerGang) {
                                $sellerGang->increment('treasury', $lockedListing->price);
                            } else {
                                $lockedSeller->bank += $lockedListing->price;
                                $lockedSeller->save();
                            }
                        } else {
                            $lockedSeller->bank += $lockedListing->price;
                            $lockedSeller->save();
                        }
                    } elseif ($lockedListing->currency === 'stars') {
                        $lockedSeller->stars = ($lockedSeller->stars ?? 0) + $lockedListing->price;
                        $lockedSeller->save();
                    }
                }

                // Deliver purchased item to buyer
                if ($lockedListing->item_type === 'depot_item') {
                    $listedItem = Item::find($lockedListing->related_id);
                    $charItem = CharacterItem::firstOrCreate([
                        'character_id' => $lockedBuyer->id,
                        'item_id' => $lockedListing->related_id,
                    ], [
                        'quantity' => 0,
                        'is_equipped' => false,
                    ]);
                    if ($listedItem && $listedItem->category === 'equipment') {
                        $charItem->quantity = 1;
                    } else {
                        $charItem->quantity += $lockedListing->amount;
                    }
                    $charItem->save();
                } elseif ($lockedListing->item_type === 'vehicle') {
                    $charVehicle = CharacterVehicle::where('id', $lockedListing->related_id)->lockForUpdate()->first();
                    if (! $charVehicle || ! $charVehicle->is_for_sale) {
                        throw new \RuntimeException('Het aangeboden voertuig kon niet meer worden gevonden of staat niet meer te koop.');
                    }
                    $charVehicle->character_id = $lockedBuyer->id;
                    $charVehicle->in_gang_garage = false;
                    $charVehicle->gang_id = null;
                    $charVehicle->stored_at = null;
                    $charVehicle->stored_by_character_id = null;
                    $charVehicle->is_for_sale = false;
                    $charVehicle->is_active = false;
                    $charVehicle->save();
                } elseif ($lockedListing->item_type === 'property') {
                    $gangProperty = GangProperty::where('id', $lockedListing->related_id)->lockForUpdate()->first();
                    if (! $gangProperty || ! $gangProperty->is_for_sale || ! $lockedBuyer->gang_id) {
                        throw new \RuntimeException('Het aangeboden bendepand of je bende kon niet worden gevonden.');
                    }
                    $buyerGang = Gang::where('id', $lockedBuyer->gang_id)->lockForUpdate()->first();
                    if (! $buyerGang || ! $buyerGang->isOfficer($lockedBuyer)) {
                        throw new \RuntimeException('Je moet leider of beheerder zijn van een bende om een bende pand te kopen op de marktplaats!');
                    }
                    if ($buyerGang->properties()->where('property_key', $gangProperty->property_key)->exists()) {
                        throw new \RuntimeException('Jouw bende bezit dit type pand al!');
                    }
                    $gangProperty->gang_id = $buyerGang->id;
                    $gangProperty->is_for_sale = false;
                    $gangProperty->production_status = 'idle';
                    $gangProperty->save();
                } elseif ($lockedListing->item_type === 'stars') {
                    $lockedBuyer->stars = ($lockedBuyer->stars ?? 0) + $lockedListing->amount;
                } elseif ($lockedListing->item_type === 'packages') {
                    $lockedBuyer->unopened_bags = ($lockedBuyer->unopened_bags ?? 0) + $lockedListing->amount;
                } elseif ($lockedListing->item_type === 'masks') {
                    $lockedBuyer->masks = ($lockedBuyer->masks ?? 0) + $lockedListing->amount;
                } elseif ($lockedListing->item_type === 'pumpkin_seeds') {
                    $lockedBuyer->pumpkin_seeds = ($lockedBuyer->pumpkin_seeds ?? 0) + $lockedListing->amount;
                } elseif ($lockedListing->item_type === 'cash') {
                    $lockedBuyer->cash += $lockedListing->amount;
                } elseif ($lockedListing->item_type === 'vault_codes') {
                    // Deliver exactly the listed code. Never fall back to "any ownerless code": that would hand over another seller's escrow.
                    $code = $lockedListing->related_id
                        ? VaultCode::where('id', $lockedListing->related_id)
                            ->where('is_redeemed', false)
                            ->lockForUpdate()
                            ->first()
                        : null;

                    if (! $code) {
                        throw new \RuntimeException('Deze kluiscode is niet meer geldig. De aankoop is geannuleerd en er is niets afgeschreven.');
                    }

                    $code->character_id = $lockedBuyer->id;
                    $code->save();
                }

                $lockedBuyer->save();
                $buyer->cash = $lockedBuyer->cash;
                $buyer->stars = $lockedBuyer->stars;

                $lockedListing->is_sold = true;
                $lockedListing->buyer_id = $lockedBuyer->id;
                $lockedListing->save();

                if ($lockedSeller) {
                    $currName = $lockedListing->currency === 'stars' ? "{$lockedListing->price} Sterren ⭐" : '€'.number_format($lockedListing->price, 0, ',', '.').' (wit op de bank)';
                    GameMessage::create([
                        'recipient_id' => $lockedSeller->id,
                        'sender_id' => $lockedBuyer->id,
                        'sender_name' => 'Marktplaats Systeem',
                        'subject' => "🛒 Artikel Verkocht: {$lockedListing->title}",
                        'content' => "Goed nieuws!\n\nPatiënt {$lockedBuyer->name} heeft zojuist jouw aanbod '{$lockedListing->title}' gekocht voor {$currName}.",
                        'type' => 'trade',
                    ]);
                }
            });
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
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

        if ($character->isImprisoned()) {
            return back()->with('error', 'Vanuit de isoleercel heb je geen toegang tot de Marktplaats!');
        }

        if ($character->isComatose()) {
            return back()->with('error', 'Je verkeert in Code Blauw (Hartstilstand)! Dien eerst een '.Character::REVIVE_SERUM_NAME.' toe.');
        }

        if ($listing->seller_id !== $character->id) {
            return back()->with('error', 'Dit is niet jouw aanbieding!');
        }

        if ($listing->is_sold) {
            return back()->with('error', 'Dit artikel is reeds verkocht.');
        }

        try {
            DB::transaction(function () use ($listing, $character): void {
                /** @var MarketplaceListing|null $lockedListing */
                $lockedListing = MarketplaceListing::where('id', $listing->id)->lockForUpdate()->first();
                if (! $lockedListing || $lockedListing->is_sold) {
                    throw new \RuntimeException('Dit artikel is reeds verkocht of ingetrokken.');
                }

                /** @var Character $lockedCharacter */
                $lockedCharacter = Character::where('id', $character->id)->lockForUpdate()->first();

                // Refund escrowed assets
                if ($lockedListing->item_type === 'depot_item') {
                    $listedItem = Item::find($lockedListing->related_id);
                    $charItem = CharacterItem::firstOrCreate([
                        'character_id' => $lockedCharacter->id,
                        'item_id' => $lockedListing->related_id,
                    ], [
                        'quantity' => 0,
                        'is_equipped' => false,
                    ]);

                    if ($listedItem && $listedItem->category === 'equipment') {
                        $totalQty = $charItem->quantity + $lockedListing->amount;
                        if ($totalQty > 1) {
                            $excessQty = $totalQty - 1;
                            $lockedCharacter->bank += $excessQty * $listedItem->getDepotSellPrice();
                            $lockedCharacter->save();
                        }
                        $charItem->quantity = 1;
                    } else {
                        if (! $lockedCharacter->canAddStorageItem((int) $lockedListing->amount)) {
                            throw new \RuntimeException('Je persoonlijke depot zit vol! Maak eerst ruimte vrij voordat je dit artikel terugtrekt.');
                        }
                        $charItem->quantity += $lockedListing->amount;
                    }
                    $charItem->save();
                } elseif ($lockedListing->item_type === 'vehicle') {
                    $charVehicle = CharacterVehicle::where('id', $lockedListing->related_id)->lockForUpdate()->first();
                    if ($charVehicle) {
                        $charVehicle->is_for_sale = false;
                        $charVehicle->save();
                    }
                } elseif ($lockedListing->item_type === 'property') {
                    $gangProperty = GangProperty::where('id', $lockedListing->related_id)->lockForUpdate()->first();
                    if ($gangProperty) {
                        $gangProperty->is_for_sale = false;
                        $gangProperty->save();
                    }
                } elseif ($lockedListing->item_type === 'stars') {
                    $lockedCharacter->stars = ($lockedCharacter->stars ?? 0) + $lockedListing->amount;
                    $lockedCharacter->save();
                } elseif ($lockedListing->item_type === 'packages') {
                    $lockedCharacter->unopened_bags = ($lockedCharacter->unopened_bags ?? 0) + $lockedListing->amount;
                    $lockedCharacter->save();
                } elseif ($lockedListing->item_type === 'masks') {
                    $lockedCharacter->masks = ($lockedCharacter->masks ?? 0) + $lockedListing->amount;
                    $lockedCharacter->save();
                } elseif ($lockedListing->item_type === 'pumpkin_seeds') {
                    $lockedCharacter->pumpkin_seeds = ($lockedCharacter->pumpkin_seeds ?? 0) + $lockedListing->amount;
                    $lockedCharacter->save();
                } elseif ($lockedListing->item_type === 'cash') {
                    $lockedCharacter->cash += $lockedListing->amount;
                    $lockedCharacter->save();
                } elseif ($lockedListing->item_type === 'vault_codes') {
                    // Return exactly the listed code; never take an unrelated ownerless code from another seller's escrow.
                    $code = $lockedListing->related_id
                        ? VaultCode::where('id', $lockedListing->related_id)
                            ->where('is_redeemed', false)
                            ->lockForUpdate()
                            ->first()
                        : null;

                    if ($code) {
                        $code->character_id = $lockedCharacter->id;
                        $code->save();
                    }
                }

                $lockedListing->delete();
            });
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Aanbieding ingetrokken. Je items/bezittingen zijn teruggeplaatst in je bezit.');
    }
}
