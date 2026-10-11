<?php

namespace App\Http\Controllers;

use App\Models\GameMessage;
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

        return view('game.marketplace.index', compact('character', 'listings', 'myListings', 'myVaultCodes', 'filterType'));
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
            'item_type' => ['required', 'string', 'in:stars,packages,cash,vault_codes,masks'],
            'amount' => ['required', 'integer', 'min:1'],
            'price' => ['required', 'integer', 'min:10', 'max:5000000'],
            'currency' => ['required', 'string', 'in:cash,stars'],
            'vault_code_id' => ['nullable', 'integer', 'exists:vault_codes,id'],
        ]);

        $itemType = $validated['item_type'];
        $amount = (int) $validated['amount'];
        $price = (int) $validated['price'];
        $currency = $validated['currency'];

        // Escrow deduction based on item type
        if ($itemType === 'stars') {
            if (($character->stars ?? 0) < $amount) {
                return back()->with('error', "Je hebt niet genoeg Sterren ⭐ (je hebt er {$character->stars}).");
            }
            $character->stars -= $amount;
            $title = "{$amount}x Exclusieve Sterren ⭐";
        } elseif ($itemType === 'packages') {
            if (($character->unopened_bags ?? 0) < $amount) {
                return back()->with('error', "Je hebt niet genoeg ongeopende smokkeltassen/koffers (je hebt er {$character->unopened_bags}).");
            }
            $character->unopened_bags -= $amount;
            $title = "{$amount}x Smokkelkoffer 🧳";
        } elseif ($itemType === 'masks') {
            if (($character->masks ?? 0) < $amount) {
                return back()->with('error', "Je hebt niet genoeg Maskers 🎭 (je hebt er {$character->masks}).");
            }
            $character->masks -= $amount;
            $title = "{$amount}x Masker 🎭";
        } elseif ($itemType === 'cash') {
            if ($character->cash < $amount) {
                return back()->with('error', 'Je hebt niet genoeg contant geld om aan te bieden.');
            }
            $character->cash -= $amount;
            $title = '€'.number_format($amount, 0, ',', '.').' Contant Geld';
        } elseif ($itemType === 'vault_codes') {
            $vaultCode = VaultCode::where('id', $request->input('vault_code_id'))
                ->where('character_id', $character->id)
                ->where('is_redeemed', false)
                ->first();

            if (! $vaultCode) {
                return back()->with('error', 'Selecteer een geldige kluiscode uit jouw bezit.');
            }

            // Transfer code ownership to escrow (null character_id during listing)
            $vaultCode->character_id = null;
            $vaultCode->save();
            $title = "Geheime Kluiscode Voucher ({$vaultCode->code})";
            $amount = 1;
        } else {
            return back()->with('error', 'Ongeldig item type.');
        }

        $character->save();

        MarketplaceListing::create([
            'seller_id' => $character->id,
            'item_type' => $itemType,
            'title' => $title,
            'amount' => $amount,
            'price' => $price,
            'currency' => $currency,
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

        $seller = $listing->seller;

        // Payment check & execution
        if ($listing->currency === 'cash') {
            if ($buyer->cash < $listing->price) {
                return back()->with('error', 'Je hebt minimaal €'.number_format($listing->price, 0, ',', '.').' contant nodig voor deze aankoop.');
            }
            $buyer->cash -= $listing->price;

            // Clean money directly credited to seller's bank!
            if ($seller) {
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
        if ($listing->item_type === 'stars') {
            $buyer->stars = ($buyer->stars ?? 0) + $listing->amount;
        } elseif ($listing->item_type === 'packages') {
            $buyer->unopened_bags = ($buyer->unopened_bags ?? 0) + $listing->amount;
        } elseif ($listing->item_type === 'masks') {
            $buyer->masks = ($buyer->masks ?? 0) + $listing->amount;
        } elseif ($listing->item_type === 'cash') {
            $buyer->cash += $listing->amount;
        } elseif ($listing->item_type === 'vault_codes') {
            // Find escrowed code or create one
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
        if ($listing->item_type === 'stars') {
            $character->stars = ($character->stars ?? 0) + $listing->amount;
        } elseif ($listing->item_type === 'packages') {
            $character->unopened_bags = ($character->unopened_bags ?? 0) + $listing->amount;
        } elseif ($listing->item_type === 'masks') {
            $character->masks = ($character->masks ?? 0) + $listing->amount;
        } elseif ($listing->item_type === 'cash') {
            $character->cash += $listing->amount;
        } elseif ($listing->item_type === 'vault_codes') {
            $code = VaultCode::whereNull('character_id')->where('is_redeemed', false)->first();
            if ($code) {
                $code->character_id = $character->id;
                $code->save();
            }
        }

        $character->save();
        $listing->delete();

        return back()->with('success', 'Aanbieding ingetrokken. Je items zijn teruggeplaatst in je inventaris.');
    }
}
