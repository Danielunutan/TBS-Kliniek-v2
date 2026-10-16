<?php

namespace App\Http\Controllers;

use App\Models\Character;
use App\Models\CharacterVehicle;
use App\Models\User;
use App\Models\Vehicle;
use App\Services\EconomyService;
use App\Services\EnergyService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class GarageController extends Controller
{
    public function __construct(
        protected EnergyService $energyService,
        protected EconomyService $economyService,
    ) {}

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
            ->with('vehicle')
            ->orderByDesc('is_active')
            ->get();

        $activeVehicle = $ownedVehicles->firstWhere('is_active', true) ?? $ownedVehicles->first();

        // Showroom available vehicles
        $showroomVehicles = Vehicle::where('is_available', true)
            ->orderBy('min_level')
            ->get();

        $topRankings = Character::orderByDesc('respect')->take(10)->get();

        return view('game.garage.index', compact(
            'character',
            'ownedVehicles',
            'activeVehicle',
            'showroomVehicles',
            'topRankings'
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

        if ($character->level < $vehicle->min_level) {
            return back()->with('error', "Je moet minimaal Level {$vehicle->min_level} zijn om de {$vehicle->name} te besturen!");
        }

        $paymentMethod = $request->input('payment', 'cash');

        if ($paymentMethod === 'stars' && $vehicle->price_stars > 0) {
            if (($character->stars ?? 0) < $vehicle->price_stars) {
                return back()->with('error', "Je hebt minimaal {$vehicle->price_stars} Sterren ⭐ nodig (je hebt er {$character->stars}).");
            }
            $character->stars -= $vehicle->price_stars;
            $character->save();
        } else {
            $totalFunds = ($character->cash ?? 0) + ($character->bank ?? 0);
            if ($totalFunds < $vehicle->price_cash) {
                $formattedCost = number_format($vehicle->price_cash, 0, ',', '.');

                return back()->with('error', "Niet genoeg saldo! De {$vehicle->name} kost €{$formattedCost}.");
            }

            if (($character->cash ?? 0) >= $vehicle->price_cash) {
                $character->cash -= $vehicle->price_cash;
            } else {
                $diff = $vehicle->price_cash - $character->cash;
                $character->cash = 0;
                $character->bank -= $diff;
            }
            $character->save();
        }

        // Deactivate existing vehicles if needed
        $hasVehicles = CharacterVehicle::where('character_id', $character->id)->exists();

        $charVehicle = CharacterVehicle::create([
            'character_id' => $character->id,
            'vehicle_id' => $vehicle->id,
            'current_condition' => 100,
            'is_active' => ! $hasVehicles,
            'total_heists_done' => 0,
        ]);

        return back()->with('success', "🏎️ Gefeliciteerd! De {$vehicle->name} staat nu in je garage met 100% conditie!");
    }

    /**
     * Set a vehicle as the active getaway vehicle for heists.
     */
    public function setActive(CharacterVehicle $characterVehicle): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;

        if ($characterVehicle->character_id !== $character->id) {
            return back()->with('error', 'Dit voertuig is niet in jouw bezit.');
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

        if ($characterVehicle->character_id !== $character->id) {
            return back()->with('error', 'Dit voertuig is niet in jouw bezit.');
        }

        if ($characterVehicle->current_condition >= 100) {
            return back()->with('info', "De {$characterVehicle->vehicle->name} is al in topconditie (100%)!");
        }

        $cost = $characterVehicle->repair_cost;
        $totalFunds = ($character->cash ?? 0) + ($character->bank ?? 0);

        if ($totalFunds < $cost) {
            $formattedCost = number_format($cost, 0, ',', '.');

            return back()->with('error', "Niet genoeg geld! Volledige revisie kost €{$formattedCost}.");
        }

        if (($character->cash ?? 0) >= $cost) {
            $character->cash -= $cost;
        } else {
            $diff = $cost - $character->cash;
            $character->cash = 0;
            $character->bank -= $diff;
        }
        $character->save();

        $characterVehicle->update(['current_condition' => 100]);

        return back()->with('success', "🔧 Garage revisie voltooid! {$characterVehicle->vehicle->name} is weer 100% gerepareerd voor €".number_format($cost, 0, ',', '.').'!');
    }
}
