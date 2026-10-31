<?php

namespace App\Http\Controllers;

use App\Models\Character;
use App\Models\CharacterVehicle;
use App\Models\Gang;
use App\Models\Heist;
use App\Models\HeistLog;
use App\Models\User;
use App\Models\VaultCode;
use App\Services\DailyBattleService;
use App\Services\EconomyService;
use App\Services\EnergyService;
use App\Services\EventCashHuntService;
use App\Services\LevelService;
use App\Services\PrisonService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
        $soloHeists = $heists->where('is_gang_heist', false);
        $gangHeists = $heists->where('is_gang_heist', true);
        $myGang = $character->gang;
        if ($myGang) {
            $myGang->load(['properties', 'storedVehicles.vehicle']);
        }

        $activeVehicle = CharacterVehicle::where('character_id', $character->id)
            ->where('is_active', true)
            ->with('vehicle')
            ->first() ?? CharacterVehicle::where('character_id', $character->id)->with('vehicle')->first();

        $ownedVehicles = CharacterVehicle::where('character_id', $character->id)->with('vehicle')->get();

        // Recent heists by all players
        $recentLogs = HeistLog::with(['character', 'heist', 'vehicle', 'gang'])
            ->latest()
            ->take(8)
            ->get();

        $topRankings = Character::getTopRankings(10);

        return view('game.heists.index', compact(
            'character',
            'heists',
            'soloHeists',
            'gangHeists',
            'myGang',
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

        if ($character->isComatose()) {
            return back()->with('error', 'Je verkeert in Code Blauw (Hartstilstand)! Dien eerst een '.Character::REVIVE_SERUM_NAME.' toe.');
        }

        if ($character->level < $heist->min_level) {
            return back()->with('error', "Voor deze Heist moet je minimaal Level {$heist->min_level} zijn!");
        }

        // Gang requirements for Bende Heists
        if ($heist->is_gang_heist) {
            if (! $character->gang_id || ! $character->gang) {
                return back()->with('error', 'Deze operatie is een officiële Bende Heist! Sluit je eerst aan bij een bende of richt zelf een bende op om deel te nemen.');
            }

            $gang = $character->gang;
            $gang->load(['properties', 'storedVehicles.vehicle']);
            $memberCount = $gang->members()->count();

            // 1. Quota check (Leden Quota)
            if ($memberCount < $heist->min_gang_members) {
                return back()->with('error', "Onvoldoende bendeleden! Voor de {$heist->name} moet jouw bende minimaal {$heist->min_gang_members} actieve leden tellen (jouw bende heeft momenteel {$memberCount} leden).");
            }

            // 2. Panden requirement check (Benodigde Gang Panden)
            if (! $heist->gangHasRequiredProperty($gang)) {
                return back()->with('error', "Jullie bende bezit nog niet het vereiste pand ({$heist->required_property_name}) om de {$heist->name} te organiseren! Schaf dit pand eerst aan via Bende Panden.");
            }
        }

        // Validate requirements BEFORE consuming energy: Bende Heists vs Solo Heists
        if ($heist->is_gang_heist) {
            $gang = $character->gang;
            // 1. Mask requirement strictly from Gang Depot
            $gangMasks = (int) ($gang->masks ?? 0);
            if ($gangMasks < $heist->required_mask_count) {
                return back()->with('error', "Onvoldoende maskers in het Bende Depot! Voor de {$heist->name} zijn {$heist->required_mask_count} maskers vereist in het Bende Depot (huidige bendevoorraad: {$gangMasks} stuks). Bendeleden kunnen maskers doneren via Bende > Doneren of Depot!");
            }

            // 2. Vehicle requirement strictly from Gang Garage fleet
            $gangVehicles = $gang->storedVehicles()->where('in_gang_garage', true)->with('vehicle')->get();
            if ($gangVehicles->isEmpty()) {
                return back()->with('error', 'Geen bendevoertuig in de Bende Garage! Voor Bende Heists kunnen enkel voertuigen worden gebruikt die zijn gestald in de Bende Garage van jullie panden. Parkeer een vluchtvoertuig in de Bende Garage!');
            }

            $qualifyingVehicles = $gangVehicles->filter(function ($cv) use ($heist) {
                if ($heist->required_vehicle_type && $heist->required_vehicle_type !== 'any' && $cv->vehicle->type !== $heist->required_vehicle_type) {
                    return false;
                }

                return $cv->effective_speed >= $heist->min_speed_required && $cv->current_condition >= $heist->min_condition_required;
            });

            if ($qualifyingVehicles->isEmpty()) {
                return back()->with('error', "Geen enkel voertuig in de Bende Garage voldoet aan de eisen voor de {$heist->name} (Type: {$heist->required_vehicle_type}, Min. Snelheid: {$heist->min_speed_required} km/u, Min. Conditie: {$heist->min_condition_required}%). Breng een geschikte wagen naar de Bende Garage!");
            }

            $charVehicle = $qualifyingVehicles->sortByDesc('effective_speed')->first();

            // Deduct masks directly from Gang Depot atomically under row lock
            $masksDeducted = DB::transaction(function () use ($gang, $heist): bool {
                /** @var Gang|null $lockedGang */
                $lockedGang = Gang::where('id', $gang->id)->lockForUpdate()->first();
                if (! $lockedGang || (int) ($lockedGang->masks ?? 0) < $heist->required_mask_count) {
                    return false;
                }
                $lockedGang->decrement('masks', $heist->required_mask_count);

                return true;
            });

            if (! $masksDeducted) {
                return back()->with('error', "Onvoldoende maskers in het Bende Depot! Voor de {$heist->name} zijn {$heist->required_mask_count} maskers vereist in het Bende Depot.");
            }

            // Energy requirement checked after all gang prerequisites pass
            if (! $this->energyService->consume($character, $heist->energy_cost)) {
                Gang::where('id', $gang->id)->increment('masks', $heist->required_mask_count);

                return back()->with('error', "Niet genoeg energie! Deze operatie kost {$heist->energy_cost} Energie.");
            }
        } else {
            // Solo Heist: Personal masks & Personal garage vehicle
            $currentMasks = $character->masks ?? 0;
            if ($currentMasks < $heist->required_mask_count) {
                return back()->with('error', "Onvoldoende maskers! Voor de {$heist->name} heb je minimaal {$heist->required_mask_count} Bivakmutsen/Maskers 🎭 nodig om je identiteit te verbergen. Koop maskers in de Kliniek Winkel!");
            }

            $charVehicle = $heist->getEffectiveVehicle($character);

            if (! $charVehicle) {
                return back()->with('error', 'Je hebt een geregistreerd vluchtvoertuig nodig (in je eigen garage) om een Heist te plegen!');
            }

            if ($heist->required_vehicle_type && $heist->required_vehicle_type !== 'any' && $charVehicle->vehicle->type !== $heist->required_vehicle_type) {
                return back()->with('error', "Onjuist voertuigtype! Voor de {$heist->name} is een {$heist->required_vehicle_type} vereist ({$heist->required_vehicle_name}). Jouw wagen is een {$charVehicle->vehicle->type}.");
            }

            if ($charVehicle->effective_speed < $heist->min_speed_required) {
                return back()->with('error', "De topsnelheid van je {$charVehicle->vehicle->name} is te laag ({$charVehicle->effective_speed} km/u). Deze heist vereist minimaal {$heist->min_speed_required} km/u. Tune je wagen of gebruik een snellere auto!");
            }

            if ($charVehicle->current_condition < $heist->min_condition_required) {
                return back()->with('error', "De conditie van je {$charVehicle->vehicle->name} is te laag ({$charVehicle->current_condition}%). Deze heist vereist minimaal {$heist->min_condition_required}% conditie. Laat je wagen eerst reviseren in de Garage!");
            }

            // Energy requirement checked after all solo prerequisites pass
            if (! $this->energyService->consume($character, $heist->energy_cost)) {
                return back()->with('error', "Niet genoeg energie! Deze operatie kost {$heist->energy_cost} Energie.");
            }

            // Deduct personal masks
            $character->masks -= $heist->required_mask_count;
            $character->save();
        }

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

        // 5. Gang synergy bonus for Bende Heists
        $gangBonus = 0;
        if ($heist->is_gang_heist && $character->gang) {
            $gangBonus = min(15, $character->gang->members()->count() * 2);
        }

        $finalChance = max(15, min(92, $baseChance + $speedBonus + $conditionBonus + $statBonus + $gangBonus));
        $roll = mt_rand(1, 100);
        $success = ($roll <= $finalChance);

        if ($success) {
            // Calculate payout
            $totalPayout = mt_rand($heist->payout_min, $heist->payout_max);
            $gangPayout = 0;
            $gangMsg = '';

            if ($heist->is_gang_heist && $character->gang) {
                $gang = $character->gang;
                $gangSharePct = $heist->gang_treasury_share_pct ?? 50;
                $gangPayout = (int) round(($totalPayout * $gangSharePct) / 100);
                $playerPayout = $totalPayout - $gangPayout;

                // Atomic increments: two members can finish a gang heist at the same moment, and a read-modify-save
                // would let the second save overwrite the first payout.
                Gang::where('id', $gang->id)->increment('treasury', $gangPayout);
                if ($heist->gang_respect_reward > 0) {
                    Gang::where('id', $gang->id)->increment('respect', $heist->gang_respect_reward);
                }

                $character->cash += $playerPayout;
                $character->addRespect($heist->respect_reward);
                $this->levelService->addXp($character, $heist->xp_reward);

                $formattedGangCash = number_format($gangPayout, 0, ',', '.');
                $gangMsg = " Bende Buit: +€{$formattedGangCash} & +{$heist->gang_respect_reward} Gang Respect direct bijgeschreven in de bendekas van [{$gang->name}]!";
            } else {
                $playerPayout = $totalPayout;
                $character->cash += $playerPayout;
                $character->addRespect($heist->respect_reward);
                $this->levelService->addXp($character, $heist->xp_reward);
            }

            EventCashHuntService::record($character, $playerPayout, 'heist');

            // Vehicle wear & tear (5% to 15% condition lost)
            $conditionLoss = mt_rand(6, 16);
            $newCondition = $this->applyVehicleWear($charVehicle, $conditionLoss, true);

            // Record Daily Battle
            app(DailyBattleService::class)->record($character, 'crimes', 1);

            // 20% bonus vault code drop
            $bonusMsg = '';
            if (mt_rand(1, 100) <= 20) {
                VaultCode::create([
                    'character_id' => $character->id,
                    'code' => VaultCode::generateUniqueCode('HEIST'),
                    'is_redeemed' => false,
                ]);
                $bonusMsg = ' 🗝️ EXTRA: Je vond een zeldzame Kluiscode in de vluchtbuit!';
            }

            HeistLog::create([
                'character_id' => $character->id,
                'heist_id' => $heist->id,
                'vehicle_id' => $charVehicle->vehicle_id,
                'gang_id' => $character->gang_id,
                'success' => true,
                'cash_earned' => $playerPayout,
                'gang_cash_earned' => $gangPayout,
                'xp_earned' => $heist->xp_reward,
                'respect_earned' => $heist->respect_reward,
                'condition_loss' => $conditionLoss,
                'notes' => "Succesvolle vlucht met {$charVehicle->vehicle->name}. Slagingskans was {$finalChance}%.",
            ]);

            $character->save();

            $formattedPlayerCash = number_format($playerPayout, 0, ',', '.');
            $redirectParams = $heist->is_gang_heist ? ['tab' => 'gang'] : [];

            return redirect()->route('heists.index', $redirectParams)->with('success', "💥 KRAAK GESLAAGD! De {$heist->name} is vlekkeloos uitgevoerd met je {$charVehicle->vehicle->name}! Persoonlijke buit: +€{$formattedPlayerCash}, +{$heist->xp_reward} XP en +{$heist->respect_reward} Respect. (Wagen slijtage: -{$conditionLoss}% conditie).{$gangMsg}{$bonusMsg}");
        } else {
            // Failure: Heavy vehicle damage and isolation imprisonment
            $conditionLoss = mt_rand(20, 35);
            $newCondition = $this->applyVehicleWear($charVehicle, $conditionLoss, false);

            // Use the prison service so perks (advocaat), the sentence counter and the notification all apply.
            $prisonMinutes = $heist->isolation_risk_minutes;
            app(PrisonService::class)->jail($character, $prisonMinutes, "Betrapt tijdens {$heist->name}! Klemgereden door arrestatieteam.");

            HeistLog::create([
                'character_id' => $character->id,
                'gang_id' => $heist->is_gang_heist ? $character->gang_id : null,
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

    /**
     * Apply heist wear to a vehicle under a row lock and return the new condition.
     *
     * Gang vehicles are shared, so two members can use the same car at once; the lock stops one wear update from erasing the other.
     */
    private function applyVehicleWear(CharacterVehicle $vehicle, int $conditionLoss, bool $countHeist): int
    {
        return DB::transaction(function () use ($vehicle, $conditionLoss, $countHeist): int {
            /** @var CharacterVehicle $locked */
            $locked = CharacterVehicle::where('id', $vehicle->id)->lockForUpdate()->first();
            $locked->current_condition = max(0, (int) $locked->current_condition - $conditionLoss);
            if ($countHeist) {
                $locked->total_heists_done = (int) $locked->total_heists_done + 1;
            }
            $locked->save();

            $vehicle->current_condition = $locked->current_condition;
            $vehicle->total_heists_done = $locked->total_heists_done;

            return (int) $locked->current_condition;
        });
    }
}
