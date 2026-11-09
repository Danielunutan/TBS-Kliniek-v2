<?php

namespace App\Http\Controllers;

use App\Models\BankTransaction;
use App\Models\Character;
use App\Models\CharacterVehicle;
use App\Models\Gang;
use App\Models\User;
use App\Models\Vehicle;
use App\Services\AuditLogService;
use App\Services\EconomyService;
use App\Services\EnergyService;
use App\Services\LevelService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class GarageController extends Controller
{
    public function __construct(
        protected EnergyService $energyService,
        protected EconomyService $economyService,
    ) {}

    /**
     * Get the current 30-minute rotating vehicle showroom listings.
     * Rotates at :00 and :30 of every hour with 8 unique dynamic offers.
     *
     * @return array{slot: int, expires_at: int, seconds_left: int, listings: array}
     */
    public function getRotatingShowroom(): array
    {
        $slotDuration = 1800; // 30 minutes
        $currentTimestamp = time();
        $slot = (int) floor($currentTimestamp / $slotDuration);
        $secondsLeft = $slotDuration - ($currentTimestamp % $slotDuration);
        $expiresAt = ($slot + 1) * $slotDuration;

        $cacheKey = "garage_showroom_rotation_{$slot}";

        $listings = Cache::remember($cacheKey, $secondsLeft + 5, function () use ($slot) {
            $allVehicles = Vehicle::where('is_available', true)->where('price_cash', '>', 0)->get();
            if ($allVehicles->isEmpty()) {
                return [];
            }

            // Deterministic pseudo-random seed based on 30-minute slot
            // Every inmate sees the exact same exciting offers during this half hour!
            mt_srand($slot * 31337);

            // Group into 4 level tiers to guarantee rich variety for both new and veteran inmates
            $t1 = $allVehicles->where('min_level', '<=', 3)->shuffle();
            $t2 = $allVehicles->whereBetween('min_level', [4, 7])->shuffle();
            $t3 = $allVehicles->whereBetween('min_level', [8, 12])->shuffle();
            $t4 = $allVehicles->where('min_level', '>=', 13)->shuffle();

            $selected = collect();
            $selected = $selected->merge($t1->take(2));
            $selected = $selected->merge($t2->take(2));
            $selected = $selected->merge($t3->take(2));
            $selected = $selected->merge($t4->take(2));

            if ($selected->count() < 8) {
                $remaining = $allVehicles->whereNotIn('id', $selected->pluck('id'))->shuffle();
                $selected = $selected->merge($remaining->take(8 - $selected->count()));
            }

            $items = [];
            foreach ($selected->values() as $index => $vehicle) {
                // Dynamic condition: 52% to 100%
                $condition = mt_rand(52, 100);

                // Speed tuning variance: -3% to +8%
                $speedVariance = mt_rand(-3, 8);
                $tunedSpeed = max(25, min(100, $vehicle->speed_pct + $speedVariance));

                // Price formula:
                // Worn condition gives a steep bargain discount (up to 30% off!)
                // Positive speed tuning adds a realistic street value premium
                $conditionFactor = 0.70 + (($condition - 50) / 50) * 0.30;
                $speedFactor = 1.0 + ($speedVariance * 0.015);
                $priceMultiplier = max(0.65, min(1.25, $conditionFactor * $speedFactor));

                $priceCash = (int) (round(($vehicle->price_cash * $priceMultiplier) / 50) * 50);
                $priceStars = $vehicle->price_stars > 0 ? (int) max(1, round($vehicle->price_stars * $priceMultiplier)) : 0;

                $token = substr(hash_hmac('sha256', "{$slot}_{$vehicle->id}_{$condition}_{$tunedSpeed}_{$priceCash}", config('app.key')), 0, 16);

                // Store plain array data (not Eloquent model) to prevent cache deserialization errors
                // after deployments or cache flushes that clear class definitions from OPcache.
                $items[] = [
                    'index' => $index,
                    'token' => $token,
                    'vehicle' => [
                        'id' => $vehicle->id,
                        'name' => $vehicle->name,
                        'slug' => $vehicle->slug,
                        'type' => $vehicle->type,
                        'brand' => $vehicle->brand,
                        'icon' => $vehicle->icon,
                        'badge_color' => $vehicle->badge_color,
                        'speed_pct' => $vehicle->speed_pct,
                        'reliability_pct' => $vehicle->reliability_pct,
                        'min_level' => $vehicle->min_level,
                        'description' => $vehicle->description,
                        'price_cash' => $vehicle->price_cash,
                        'price_stars' => $vehicle->price_stars,
                        'type_label' => $vehicle->type_label,
                    ],
                    'condition' => $condition,
                    'speed_variance' => $speedVariance,
                    'tuned_speed' => $tunedSpeed,
                    'price_cash' => $priceCash,
                    'price_stars' => $priceStars,
                    'original_price_cash' => $vehicle->price_cash,
                    'original_speed' => $vehicle->speed_pct,
                ];
            }

            mt_srand();

            return $items;
        });

        return [
            'slot' => $slot,
            'expires_at' => $expiresAt,
            'seconds_left' => $secondsLeft,
            'listings' => $listings,
        ];
    }

    /**
     * Show the garage showroom and owned vehicles.
     */
    public function index(): View
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;
        $this->energyService->refresh($character);

        $ownedVehicles = CharacterVehicle::where('character_id', $character->id)
            ->where('in_gang_garage', false)
            ->with('vehicle')
            ->orderByDesc('is_active')
            ->get();

        $gangStoredVehicles = $character->gang_id
            ? CharacterVehicle::where('character_id', $character->id)->where('in_gang_garage', true)->with('vehicle')->get()
            : collect();

        $activeVehicle = $ownedVehicles->firstWhere('is_active', true) ?? $ownedVehicles->first();

        // 30-minute rotating showroom listings
        $rotation = $this->getRotatingShowroom();
        // Convert the cached plain vehicle arrays back to stdClass objects so the view can use arrow notation ($veh->icon etc.)
        // The cache intentionally stores plain arrays (no Eloquent models) to prevent "incomplete object" errors on deploys.
        $rotatingListings = array_map(function (array $item) {
            $item['vehicle'] = (object) $item['vehicle'];

            return $item;
        }, $rotation['listings'] ?? []);
        $secondsUntilRotation = $rotation['seconds_left'];

        $topRankings = Character::getTopRankings(10);

        $starVehicles = Vehicle::where('is_available', true)
            ->where('price_cash', 0)
            ->where('price_stars', '>', 0)
            ->orderBy('price_stars')
            ->get();

        return view('game.garage.index', compact(
            'character',
            'ownedVehicles',
            'gangStoredVehicles',
            'activeVehicle',
            'rotatingListings',
            'secondsUntilRotation',
            'topRankings',
            'starVehicles'
        ));
    }

    /**
     * Buy a vehicle from the garage showroom.
     */
    public function buy(Request $request, Vehicle $vehicle): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;

        if ($character->isImprisoned()) {
            return redirect()->route('prison')->with('error', 'Vanuit de isoleercel kun je geen voertuigen kopen!');
        }

        if ($character->isComatose()) {
            return back()->with('error', 'Je verkeert in Code Blauw (Hartstilstand)! Dien eerst een '.Character::REVIVE_SERUM_NAME.' toe.');
        }

        if (! $vehicle->is_available) {
            return back()->with('error', "De {$vehicle->name} is momenteel niet beschikbaar in de showroom!");
        }

        if ($character->level < $vehicle->min_level) {
            return back()->with('error', "Je moet minimaal Level {$vehicle->min_level} zijn om de {$vehicle->name} te besturen!");
        }

        if (! $character->canAddVehicle()) {
            return back()->with('error', "Je persoonlijke garage zit vol ({$character->getGarageUsed()}/{$character->getGarageCapacity()} plaatsen)! Bereik een hoger level of activeer VIP voor +3 extra garageplekken.");
        }

        // Check if buying from the active 30-minute rotating showroom
        $listingToken = $request->input('listing_token');
        $rotation = $this->getRotatingShowroom();
        $matchedListing = collect($rotation['listings'])->firstWhere('token', $listingToken);

        // A showroom token only discounts the vehicle it was issued for; otherwise a cheap scooter token would buy a supercar.
        if ($matchedListing && (int) ($matchedListing['vehicle']['id'] ?? 0) !== (int) $vehicle->id) {
            $matchedListing = null;
        }

        $finalPriceCash = $matchedListing ? $matchedListing['price_cash'] : $vehicle->price_cash;
        $finalPriceStars = $matchedListing ? $matchedListing['price_stars'] : $vehicle->price_stars;
        $initialCondition = $matchedListing ? $matchedListing['condition'] : 100;
        $initialTunedSpeed = $matchedListing ? $matchedListing['tuned_speed'] : $vehicle->speed_pct;

        $paymentMethod = $request->input('payment', 'cash');

        try {
            DB::transaction(function () use ($character, $vehicle, $paymentMethod, $finalPriceStars, $finalPriceCash, $initialCondition, $initialTunedSpeed): void {
                /** @var Character $lockedCharacter */
                $lockedCharacter = Character::where('id', $character->id)->lockForUpdate()->first();

                if (! $lockedCharacter->canAddVehicle()) {
                    throw new \RuntimeException("Je persoonlijke garage zit vol ({$lockedCharacter->getGarageUsed()}/{$lockedCharacter->getGarageCapacity()} plaatsen)! Bereik een hoger level of activeer VIP voor +3 extra garageplekken.");
                }

                if ($vehicle->price_cash === 0 || $paymentMethod === 'stars') {
                    if ($finalPriceStars <= 0) {
                        throw new \RuntimeException('Dit voertuig kan niet met sterren gekocht worden.');
                    }

                    if (($lockedCharacter->stars ?? 0) < $finalPriceStars) {
                        throw new \RuntimeException("Je hebt minimaal {$finalPriceStars} Sterren ⭐ nodig (je hebt er {$lockedCharacter->stars}).");
                    }

                    $lockedCharacter->stars -= $finalPriceStars;
                    $lockedCharacter->save();
                } else {
                    $totalFunds = ($lockedCharacter->cash ?? 0) + ($lockedCharacter->bank ?? 0);
                    if ($totalFunds < $finalPriceCash) {
                        $formattedCost = number_format($finalPriceCash, 0, ',', '.');

                        throw new \RuntimeException("Niet genoeg saldo! Deze {$vehicle->name} kost momenteel €{$formattedCost}.");
                    }

                    if (($lockedCharacter->cash ?? 0) >= $finalPriceCash) {
                        $lockedCharacter->cash -= $finalPriceCash;
                    } else {
                        $diff = $finalPriceCash - $lockedCharacter->cash;
                        $lockedCharacter->cash = 0;
                        $lockedCharacter->bank -= $diff;
                        BankTransaction::record(
                            $lockedCharacter,
                            'withdrawal',
                            'bank',
                            $diff,
                            (int) $lockedCharacter->bank,
                            "Aankoop {$vehicle->name} (Garage Showroom)"
                        );
                    }
                    $lockedCharacter->save();
                }

                // Deactivate existing vehicles if needed
                $hasVehicles = CharacterVehicle::where('character_id', $lockedCharacter->id)->exists();

                CharacterVehicle::create([
                    'character_id' => $lockedCharacter->id,
                    'vehicle_id' => $vehicle->id,
                    'current_condition' => $initialCondition,
                    'tuned_speed' => $initialTunedSpeed,
                    'is_active' => ! $hasVehicles,
                    'total_heists_done' => 0,
                ]);
            });
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        $tuneMsg = ($initialTunedSpeed !== $vehicle->speed_pct) ? " (Tuned topsnelheid: {$initialTunedSpeed}%)" : '';

        return back()->with('success', "🏎️ Gefeliciteerd! De {$vehicle->name} staat nu in je garage met {$initialCondition}% conditie{$tuneMsg}!");
    }

    /**
     * Set a vehicle as the active getaway vehicle for heists.
     */
    public function setActive(CharacterVehicle $characterVehicle): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;

        if ($character->isImprisoned()) {
            return redirect()->route('prison')->with('error', 'Vanuit de isoleercel kun je je vluchtvoertuig niet wijzigen!');
        }

        if ($character->isComatose()) {
            return back()->with('error', 'Je verkeert in Code Blauw (Hartstilstand)! Dien eerst een '.Character::REVIVE_SERUM_NAME.' toe.');
        }

        if ($characterVehicle->character_id !== $character->id) {
            return back()->with('error', 'Dit voertuig is niet in jouw bezit.');
        }

        if ($characterVehicle->is_for_sale || $characterVehicle->in_gang_garage) {
            return back()->with('error', 'Een voertuig dat te koop staat of in de bendegarage gestald is kan niet als actief voertuig gekozen worden.');
        }

        CharacterVehicle::where('character_id', $character->id)->update(['is_active' => false]);
        $characterVehicle->update(['is_active' => true]);

        return back()->with('success', "⚡ {$characterVehicle->vehicle->name} is nu geselecteerd als je actieve vluchtvoertuig!");
    }

    /**
     * Repair an owned vehicle to 100% condition.
     */
    public function repair(CharacterVehicle $characterVehicle): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;

        if ($character->isImprisoned()) {
            return redirect()->route('prison')->with('error', 'Vanuit de isoleercel kun je geen voertuigen reviseren!');
        }

        if ($character->isComatose()) {
            return back()->with('error', 'Je verkeert in Code Blauw (Hartstilstand)! Dien eerst een '.Character::REVIVE_SERUM_NAME.' toe.');
        }

        // Check ownership or gang officer permission if the vehicle is stored in the gang garage
        $isOwner = $characterVehicle->character_id === $character->id;
        $isGangOfficer = $characterVehicle->in_gang_garage
            && $character->gang_id
            && (int) $characterVehicle->gang_id === (int) $character->gang_id
            && $character->gang?->isOfficer($character);

        if (! ($isOwner || $isGangOfficer)) {
            return back()->with('error', 'Dit voertuig is niet in jouw bezit en je hebt geen bevoegdheid binnen de bende om het te reviseren.');
        }

        if ($characterVehicle->current_condition >= 100) {
            return back()->with('info', "De {$characterVehicle->vehicle->name} is al in topconditie (100%)!");
        }

        // Case 1: Voertuig staat in de Bende Garage -> Mag UITSLUITEND met bendekas geld gereviseerd worden
        if ($characterVehicle->in_gang_garage) {
            $gang = $character->gang;
            if (! $gang || (int) $characterVehicle->gang_id !== (int) $gang->id) {
                return back()->with('error', 'Dit voertuig staat niet in de garage van jouw bende.');
            }

            $isOwner = (int) $characterVehicle->character_id === (int) $character->id;
            $isGangOfficer = $gang->isOfficer($character);
            if (! ($isOwner || $isGangOfficer)) {
                return back()->with('error', 'Alleen de eigenaar of bendebeheerders kunnen bendevoertuigen reviseren.');
            }

            return DB::transaction(function () use ($character, $characterVehicle, $gang) {
                /** @var Gang $lockedGang */
                $lockedGang = Gang::where('id', $gang->id)->lockForUpdate()->firstOrFail();
                /** @var CharacterVehicle $lockedVehicle */
                $lockedVehicle = CharacterVehicle::where('id', $characterVehicle->id)->lockForUpdate()->firstOrFail();

                if ($lockedVehicle->current_condition >= 100) {
                    return back()->with('info', "De {$lockedVehicle->vehicle->name} is al in topconditie (100%)!");
                }

                $cost = $lockedVehicle->repair_cost;
                if ($lockedGang->treasury < $cost) {
                    $formattedCost = number_format($cost, 0, ',', '.');
                    $formattedTreasury = number_format($lockedGang->treasury, 0, ',', '.');

                    return back()->with('error', "Onvoldoende saldo in de bendekas! Reviseren van {$lockedVehicle->vehicle->name} kost €{$formattedCost} uit de bendekas (huidige bendekas: €{$formattedTreasury}).");
                }

                // DEDUCT FROM GANG TREASURY ONLY - NO PERSONAL FUNDS TOUCHED!
                $lockedGang->treasury -= $cost;
                $lockedGang->save();

                $lockedVehicle->update(['current_condition' => 100]);

                AuditLogService::log(
                    action: 'gang.repair_vehicle',
                    category: 'gang',
                    description: "Bendevoertuig '{$lockedVehicle->vehicle->name}' gereviseerd naar 100% voor €".number_format($cost, 0, ',', '.')." uit de bendekas van bende {$lockedGang->name}",
                    character: $character,
                    user: $character->user,
                    amount: $cost,
                    details: [
                        'gang_id' => $lockedGang->id,
                        'vehicle_id' => $lockedVehicle->id,
                        'cost' => $cost,
                    ]
                );

                return back()->with('success', "🔧 Bende Garage revisie voltooid! {$lockedVehicle->vehicle->name} is weer in topconditie (100%) gebracht voor €".number_format($cost, 0, ',', '.').' vanuit de bendekas (persoonlijk saldo onaangeroerd).');
            });
        }

        // Case 2: Persoonlijk voertuig in eigen privévloot -> Betaald met persoonlijk geld
        $isOwner = (int) $characterVehicle->character_id === (int) $character->id;
        if (! $isOwner) {
            return back()->with('error', 'Dit voertuig is niet in jouw bezit.');
        }

        return DB::transaction(function () use ($character, $characterVehicle) {
            /** @var Character $lockedCharacter */
            $lockedCharacter = Character::where('id', $character->id)->lockForUpdate()->first();
            /** @var CharacterVehicle $lockedVehicle */
            $lockedVehicle = CharacterVehicle::where('id', $characterVehicle->id)->lockForUpdate()->first();

            if ($lockedVehicle->current_condition >= 100) {
                return back()->with('info', "De {$characterVehicle->vehicle->name} is al in topconditie (100%)!");
            }

            $cost = $lockedVehicle->repair_cost;
            $totalFunds = ($lockedCharacter->cash ?? 0) + ($lockedCharacter->bank ?? 0);

            if ($totalFunds < $cost) {
                $formattedCost = number_format($cost, 0, ',', '.');

                return back()->with('error', "Niet genoeg geld! Volledige revisie kost €{$formattedCost}.");
            }

            if (($lockedCharacter->cash ?? 0) >= $cost) {
                $lockedCharacter->cash -= $cost;
            } else {
                $diff = $cost - $lockedCharacter->cash;
                $lockedCharacter->cash = 0;
                $lockedCharacter->bank -= $diff;
                BankTransaction::record(
                    $lockedCharacter,
                    'withdrawal',
                    'bank',
                    $diff,
                    (int) $lockedCharacter->bank,
                    "Voertuig Revisie ({$characterVehicle->vehicle->name})"
                );
            }
            $lockedCharacter->save();

            $lockedVehicle->update(['current_condition' => 100]);

            return back()->with('success', "🔧 Garage revisie voltooid! {$characterVehicle->vehicle->name} is weer 100% gerepareerd voor €".number_format($cost, 0, ',', '.').'!');
        });
    }

    /**
     * Sell an owned vehicle back to the garage dealer.
     * Rewards cash safely into bank, plus XP for active car trading.
     */
    public function sell(CharacterVehicle $characterVehicle): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;

        if ($character->isImprisoned()) {
            return back()->with('error', 'Vanuit de isoleercel kun je geen voertuigen verkopen!');
        }

        if ($character->isComatose() || $character->needsReviveSerum()) {
            return back()->with('error', 'Je verkeert in Code Blauw (Hartstilstand)! Dien eerst een '.Character::REVIVE_SERUM_NAME.' toe.');
        }

        if ($characterVehicle->character_id !== $character->id) {
            return back()->with('error', 'Dit voertuig is niet van jou.');
        }

        if ($characterVehicle->is_gang_owned) {
            return back()->with('error', 'Dit voertuig is bende-eigendom en kan niet via de dealer verkocht worden.');
        }

        if ($characterVehicle->in_gang_garage) {
            return back()->with('error', 'Dit voertuig staat gestald in de Bende Garage en kan niet direct verkocht worden. Haal het eerst op.');
        }

        return DB::transaction(function () use ($character, $characterVehicle): RedirectResponse {
            /** @var Character|null $lockedChar */
            $lockedChar = Character::where('id', $character->id)->lockForUpdate()->first();
            if (! $lockedChar || $lockedChar->isImprisoned() || $lockedChar->isComatose() || $lockedChar->needsReviveSerum()) {
                return back()->with('error', 'Je bent momenteel niet in staat om voertuigen te verkopen.');
            }

            /** @var CharacterVehicle|null $lockedVehicle */
            $lockedVehicle = CharacterVehicle::where('id', $characterVehicle->id)->lockForUpdate()->first();
            if (! $lockedVehicle || $lockedVehicle->character_id !== $character->id || $lockedVehicle->in_gang_garage || $lockedVehicle->is_gang_owned) {
                return back()->with('error', 'Voertuig kon niet worden verwerkt.');
            }

            $vehicleName = $lockedVehicle->vehicle->name ?? 'Voertuig';
            $basePrice = (int) (($lockedVehicle->vehicle->price_cash ?? 0) > 0
                ? $lockedVehicle->vehicle->price_cash
                : (($lockedVehicle->vehicle->price_stars ?? 1) * 2000));

            // Resell value: 65% of base price scaled by current condition
            $conditionFactor = max(0.1, ($lockedVehicle->current_condition / 100));
            $resellValue = max(500, (int) round($basePrice * 0.65 * $conditionFactor));

            // Add directly and safely to character's bank
            $lockedChar->bank = (int) $lockedChar->bank + $resellValue;
            $lockedChar->save();

            BankTransaction::record(
                $lockedChar,
                'deposit',
                'bank',
                $resellValue,
                (int) $lockedChar->bank,
                "Voertuig Inruil: {$vehicleName}"
            );

            app(LevelService::class)->addXp($lockedChar, 40);

            AuditLogService::log(
                action: 'garage.sell_vehicle',
                category: 'economy',
                description: "Voertuig '{$vehicleName}' verkocht aan dealer voor €".number_format($resellValue, 0, ',', '.').' veilig op bank',
                character: $lockedChar,
                user: $lockedChar->user,
                amount: $resellValue
            );

            $lockedVehicle->delete();

            return back()->with('success', "🤝 Deal gesloten! Je hebt '{$vehicleName}' succesvol verkocht voor €".number_format($resellValue, 0, ',', '.').' (veilig bijgeschreven op je bank) +40 XP!');
        });
    }
}
