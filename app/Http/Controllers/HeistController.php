<?php

namespace App\Http\Controllers;

use App\Models\Character;
use App\Models\CharacterVehicle;
use App\Models\Heist;
use App\Models\HeistLog;
use App\Models\User;
use App\Models\VaultCode;
use App\Services\DailyBattleService;
use App\Services\EconomyService;
use App\Services\EnergyService;
use App\Services\LevelService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class HeistController extends Controller
{
    public function __construct(
        protected EnergyService $energyService,
        protected EconomyService $economyService,
        protected LevelService $levelService,
    ) {}

    /**
     * Show the heists planning board.
     */
    public function index(): View
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;
        $this->energyService->refresh($character);

        $heists = Heist::orderBy('min_level')->get();

        $activeVehicle = CharacterVehicle::where('character_id', $character->id)
            ->where('is_active', true)
            ->with('vehicle')
            ->first() ?? CharacterVehicle::where('character_id', $character->id)->with('vehicle')->first();

        $ownedVehicles = CharacterVehicle::where('character_id', $character->id)->with('vehicle')->get();

        // Recent heists by all players
        $recentLogs = HeistLog::with(['character', 'heist', 'vehicle'])
            ->latest()
            ->take(8)
            ->get();

        $topRankings = Character::orderByDesc('respect')->take(10)->get();

        return view('game.heists.index', compact(
            'character',
            'heists',
            'activeVehicle',
            'ownedVehicles',
            'recentLogs',
            'topRankings'
        ));
    }

    /**
     * Launch a heist operation.
     */
    public function launch(Request $request, Heist $heist): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;

        if ($character->isImprisoned()) {
            return back()->with('error', 'Je zit in de isoleercel en kunt geen Heist organiseren!');
        }

        if ($character->level < $heist->min_level) {
            return back()->with('error', "Voor deze Heist moet je minimaal Level {$heist->min_level} zijn!");
        }

        // Energy requirement
        if (! $this->energyService->consume($character, $heist->energy_cost)) {
            return back()->with('error', "Niet genoeg energie! Deze operatie kost {$heist->energy_cost} Energie.");
        }

        // Mask requirement
        $currentMasks = $character->masks ?? 0;
        if ($currentMasks < $heist->required_mask_count) {
            return back()->with('error', "Onvoldoende maskers! Voor de {$heist->name} heb je minimaal {$heist->required_mask_count} Bivakmutsen/Maskers 🎭 nodig om je identiteit te verbergen. Koop maskers in de Kliniek Winkel!");
        }

        // Vehicle requirement
        $charVehicle = CharacterVehicle::where('character_id', $character->id)
            ->where('is_active', true)
            ->with('vehicle')
            ->first() ?? CharacterVehicle::where('character_id', $character->id)->with('vehicle')->first();

        if (! $charVehicle) {
            return back()->with('error', 'Je hebt een geregistreerd vluchtvoertuig nodig om een Heist te plegen! Ga eerst naar de Smokkel Garage om een voertuig aan te schaffen.');
        }

        if ($charVehicle->current_condition < $heist->min_condition_required) {
            return back()->with('error', "De conditie van je {$charVehicle->vehicle->name} is te laag ({$charVehicle->current_condition}%). Deze heist vereist minimaal {$heist->min_condition_required}% conditie. Laat je wagen eerst reviseren in de Garage!");
        }

        // Deduct masks used in the heist
        $character->masks -= $heist->required_mask_count;
        $character->save();

        // Calculate Success Chance
        // 1. Base difficulty
        $baseChance = match ($heist->difficulty) {
            'makkelijk' => 75,
            'normaal' => 65,
            'zwaar' => 52,
            'extreem' => 42,
            'legendarisch' => 32,
            default => 50,
        };

        // 2. Vehicle speed bonus/penalty (relative to min_speed_required)
        $speedDiff = $charVehicle->effective_speed - $heist->min_speed_required;
        $speedBonus = (int) round($speedDiff * 0.4); // e.g. +10 speed = +4% chance

        // 3. Vehicle condition factor
        $conditionBonus = (int) round(($charVehicle->current_condition - 70) * 0.25); // 100% = +7.5%, 30% = -10%

        // 4. Character stats influence (Intelligence & Discipline for planning)
        $statBonus = min(15, (int) floor(($character->getEffectiveIntelligence() + $character->getEffectiveDiscipline()) / 30));

        $finalChance = max(15, min(92, $baseChance + $speedBonus + $conditionBonus + $statBonus));
        $roll = mt_rand(1, 100);
        $success = ($roll <= $finalChance);

        if ($success) {
            // Calculate payout
            $payout = mt_rand($heist->payout_min, $heist->payout_max);
            $character->cash += $payout;
            $character->respect += $heist->respect_reward;
            $this->levelService->addXp($character, $heist->xp_reward);

            // Vehicle wear & tear (5% to 15% condition lost)
            $conditionLoss = mt_rand(6, 16);
            $newCondition = max(0, $charVehicle->current_condition - $conditionLoss);
            $charVehicle->current_condition = $newCondition;
            $charVehicle->total_heists_done++;
            $charVehicle->save();

            // Record Daily Battle
            app(DailyBattleService::class)->record($character, 'crimes', 1);

            // 20% bonus vault code drop
            $bonusMsg = '';
            if (mt_rand(1, 100) <= 20) {
                VaultCode::create([
                    'character_id' => $character->id,
                    'code' => strtoupper(substr(md5(uniqid()), 0, 8)),
                    'is_used' => false,
                ]);
                $bonusMsg = ' 🗝️ EXTRA: Je vond een zeldzame Kluiscode in de vluchtbuit!';
            }

            HeistLog::create([
                'character_id' => $character->id,
                'heist_id' => $heist->id,
                'vehicle_id' => $charVehicle->vehicle_id,
                'success' => true,
                'cash_earned' => $payout,
                'xp_earned' => $heist->xp_reward,
                'respect_earned' => $heist->respect_reward,
                'condition_loss' => $conditionLoss,
                'notes' => "Succesvolle vlucht met {$charVehicle->vehicle->name}. Slagingskans was {$finalChance}%.",
            ]);

            $character->save();

            $formattedCash = number_format($payout, 0, ',', '.');

            return redirect()->route('heists.index')->with('success', "💥 KRAAK GESLAAGD! De {$heist->name} is vlekkeloos uitgevoerd met je {$charVehicle->vehicle->name}! Buit: +€{$formattedCash}, +{$heist->xp_reward} XP en +{$heist->respect_reward} Respect. (Wagen slijtage: -{$conditionLoss}% conditie).{$bonusMsg}");
        } else {
            // Failure: Heavy vehicle damage and isolation imprisonment
            $conditionLoss = mt_rand(20, 35);
            $newCondition = max(0, $charVehicle->current_condition - $conditionLoss);
            $charVehicle->current_condition = $newCondition;
            $charVehicle->save();

            $prisonMinutes = $heist->isolation_risk_minutes;
            $character->is_in_prison = true;
            $character->prison_until = now()->addMinutes($prisonMinutes);
            $character->prison_reason = "Betrapt tijdens {$heist->name}! Klemgereden door arrestatieteam.";
            $character->save();

            HeistLog::create([
                'character_id' => $character->id,
                'heist_id' => $heist->id,
                'vehicle_id' => $charVehicle->vehicle_id,
                'success' => false,
                'cash_earned' => 0,
                'xp_earned' => 10,
                'respect_earned' => 0,
                'condition_loss' => $conditionLoss,
                'notes' => "Achtervolging mislukt! Klemgereden. Wagenschade: -{$conditionLoss}%.",
            ]);

            return redirect()->route('prison')->with('error', "🚨 HEIST MISLUKT! Het alarm ging af en de politie ramde je {$charVehicle->vehicle->name} van de weg (-{$conditionLoss}% conditie). Je bent overgebracht naar de isoleercel voor {$prisonMinutes} minuten!");
        }
    }
}
