<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\CharacterVehicle;
use App\Models\Gang;
use App\Models\GangProperty;
use App\Models\Heist;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HeistVehicleSelectionAndWearTest extends TestCase
{
    use RefreshDatabase;

    public function test_effective_vehicle_selects_vehicle_with_highest_condition_when_duplicate_exists(): void
    {
        $user = User::factory()->create();
        $character = Character::create([
            'user_id' => $user->id,
            'name' => 'HeistCaptain',
            'cash' => 50000,
            'bank' => 100000,
            'level' => 10,
            'energy' => 100,
            'max_energy' => 100,
            'masks' => 20,
            'is_onboarded' => true,
        ]);

        $chopper = Vehicle::create([
            'name' => 'Robinson R44 Helikopter',
            'slug' => 'robinson-r44',
            'type' => 'helicopter',
            'price_cash' => 250000,
            'speed_pct' => 85,
            'reliability_pct' => 80,
            'min_level' => 5,
        ]);

        // Helicopter 1: Active, but degraded condition (35%)
        $activeWornChopper = CharacterVehicle::create([
            'character_id' => $character->id,
            'vehicle_id' => $chopper->id,
            'current_condition' => 35,
            'tuned_speed' => 85,
            'is_active' => true,
        ]);

        // Helicopter 2: Inactive, pristine condition (100%)
        $backupPristineChopper = CharacterVehicle::create([
            'character_id' => $character->id,
            'vehicle_id' => $chopper->id,
            'current_condition' => 100,
            'tuned_speed' => 85,
            'is_active' => false,
        ]);

        $heist = Heist::create([
            'name' => 'Luchtbrug Overval',
            'slug' => 'luchtbrug-overval',
            'description' => 'Solo helicopter heist',
            'min_level' => 5,
            'energy_cost' => 10,
            'required_mask_count' => 1,
            'required_vehicle_type' => 'helicopter',
            'min_speed_required' => 50,
            'min_condition_required' => 30,
            'difficulty' => 'normaal',
            'payout_min' => 10000,
            'payout_max' => 20000,
            'respect_reward' => 10,
            'xp_reward' => 50,
            'is_gang_heist' => false,
        ]);

        $effective = $heist->getEffectiveVehicle($character);

        $this->assertNotNull($effective);
        $this->assertSame($backupPristineChopper->id, $effective->id, 'Expected the duplicate helicopter with 100% condition to be selected over the worn active 35% helicopter.');
    }

    public function test_effective_vehicle_selects_qualifying_vehicle_when_active_vehicle_is_broken(): void
    {
        $user = User::factory()->create();
        $character = Character::create([
            'user_id' => $user->id,
            'name' => 'BreakoutDriver',
            'cash' => 50000,
            'bank' => 100000,
            'level' => 10,
            'energy' => 100,
            'max_energy' => 100,
            'masks' => 20,
            'is_onboarded' => true,
        ]);

        $car = Vehicle::create([
            'name' => 'Golf GTI',
            'slug' => 'golf-gti',
            'type' => 'car',
            'price_cash' => 30000,
            'speed_pct' => 60,
            'reliability_pct' => 80,
            'min_level' => 1,
        ]);

        // Car 1: Active, broken (below 50% min condition)
        $brokenActiveCar = CharacterVehicle::create([
            'character_id' => $character->id,
            'vehicle_id' => $car->id,
            'current_condition' => 20,
            'tuned_speed' => 60,
            'is_active' => true,
        ]);

        // Car 2: Inactive, healthy (80% condition)
        $healthyBackupCar = CharacterVehicle::create([
            'character_id' => $character->id,
            'vehicle_id' => $car->id,
            'current_condition' => 80,
            'tuned_speed' => 60,
            'is_active' => false,
        ]);

        $heist = Heist::create([
            'name' => 'Snelle Straatoverval',
            'slug' => 'snelle-straatoverval',
            'description' => 'Car heist requiring 50% condition',
            'min_level' => 1,
            'energy_cost' => 10,
            'required_mask_count' => 1,
            'required_vehicle_type' => 'car',
            'min_speed_required' => 40,
            'min_condition_required' => 50,
            'difficulty' => 'makkelijk',
            'payout_min' => 5000,
            'payout_max' => 10000,
            'respect_reward' => 5,
            'xp_reward' => 25,
            'is_gang_heist' => false,
        ]);

        $effective = $heist->getEffectiveVehicle($character);

        $this->assertNotNull($effective);
        $this->assertSame($healthyBackupCar->id, $effective->id);
    }

    public function test_gang_chop_shop_garage_reduces_wear_multiplier_and_repair_cost(): void
    {
        $user = User::factory()->create();
        $character = Character::create([
            'user_id' => $user->id,
            'name' => 'SyndicateBoss',
            'cash' => 500000,
            'bank' => 1000000,
            'level' => 15,
            'energy' => 100,
            'max_energy' => 100,
            'masks' => 50,
            'is_onboarded' => true,
        ]);

        $gang = Gang::create([
            'name' => 'Elite Cartel',
            'tag' => 'CART',
            'specialty' => 'combat',
            'leader_character_id' => $character->id,
            'treasury' => 500000,
            'masks' => 100,
        ]);

        $character->update(['gang_id' => $gang->id]);

        $vehicle = Vehicle::create([
            'name' => 'BMW M5 F90',
            'slug' => 'bmw-m5-f90',
            'type' => 'car',
            'price_cash' => 150000,
            'speed_pct' => 90,
            'reliability_pct' => 85,
            'min_level' => 8,
        ]);

        $gangVehicle = CharacterVehicle::create([
            'character_id' => $character->id,
            'gang_id' => $gang->id,
            'vehicle_id' => $vehicle->id,
            'current_condition' => 50, // 50% missing -> base repair cost = 50 * 150 = 7500
            'tuned_speed' => 90,
            'in_gang_garage' => true,
        ]);

        // Without chop-shop property
        $this->assertSame(1.0, $gang->getHeistWearMultiplier());
        $this->assertSame(7500, $gangVehicle->repair_cost);

        // Add chop_shop_garage property
        GangProperty::create([
            'gang_id' => $gang->id,
            'property_key' => 'chop_shop_garage',
            'name' => 'Illegale Omkat & Chop-Shop Garage',
            'type' => 'storage',
            'purchase_price' => 1100000,
            'storage_capacity' => 250,
            'vehicle_slots' => 12,
            'npc_capacity' => 1,
        ]);

        $gang->load('properties');
        $gangVehicle->load('gang.properties');

        $this->assertSame(0.75, $gang->getHeistWearMultiplier());
        // 20% discount on 7500 = 6000
        $this->assertSame(6000, $gangVehicle->repair_cost);
    }

    public function test_heists_index_page_displays_effective_vehicle_when_active_vehicle_is_damaged(): void
    {
        $user = User::factory()->create();
        $character = Character::create([
            'user_id' => $user->id,
            'name' => 'IndexDriver',
            'cash' => 50000,
            'bank' => 100000,
            'level' => 10,
            'energy' => 100,
            'max_energy' => 100,
            'masks' => 20,
            'is_onboarded' => true,
        ]);

        $car = Vehicle::create([
            'name' => 'Golf GTI',
            'slug' => 'golf-gti',
            'type' => 'car',
            'price_cash' => 30000,
            'speed_pct' => 60,
            'reliability_pct' => 80,
            'min_level' => 1,
        ]);

        // Broken active car (20% condition)
        CharacterVehicle::create([
            'character_id' => $character->id,
            'vehicle_id' => $car->id,
            'current_condition' => 20,
            'tuned_speed' => 60,
            'is_active' => true,
        ]);

        // Pristine backup car (100% condition)
        CharacterVehicle::create([
            'character_id' => $character->id,
            'vehicle_id' => $car->id,
            'current_condition' => 100,
            'tuned_speed' => 60,
            'is_active' => false,
        ]);

        $heist = Heist::create([
            'name' => 'Winkel Overval',
            'slug' => 'winkel-overval',
            'description' => 'Test heist requiring 50% condition',
            'min_level' => 1,
            'energy_cost' => 10,
            'required_mask_count' => 1,
            'required_vehicle_type' => 'car',
            'min_speed_required' => 40,
            'min_condition_required' => 50,
            'difficulty' => 'makkelijk',
            'payout_min' => 5000,
            'payout_max' => 10000,
            'respect_reward' => 5,
            'xp_reward' => 25,
            'is_gang_heist' => false,
        ]);

        $response = $this->actingAs($user)->get(route('heists.index'));
        $response->assertOk();
        // The page should show the 100% condition from the pristine duplicate car and allow launching
        $response->assertSee('100%');
        $response->assertSee('Start Solo Heist');
    }

    public function test_launching_heist_uses_best_condition_duplicate_vehicle(): void
    {
        $user = User::factory()->create();
        $character = Character::create([
            'user_id' => $user->id,
            'name' => 'LaunchPilot',
            'cash' => 50000,
            'bank' => 100000,
            'level' => 10,
            'energy' => 100,
            'max_energy' => 100,
            'masks' => 20,
            'is_onboarded' => true,
        ]);

        $chopper = Vehicle::create([
            'name' => 'Robinson R44 Helikopter',
            'slug' => 'robinson-r44',
            'type' => 'helicopter',
            'price_cash' => 250000,
            'speed_pct' => 85,
            'reliability_pct' => 80,
            'min_level' => 5,
        ]);

        // Chopper 1: Active, condition 35%
        $wornChopper = CharacterVehicle::create([
            'character_id' => $character->id,
            'vehicle_id' => $chopper->id,
            'current_condition' => 35,
            'tuned_speed' => 85,
            'is_active' => true,
        ]);

        // Chopper 2: Inactive, condition 100%
        $pristineChopper = CharacterVehicle::create([
            'character_id' => $character->id,
            'vehicle_id' => $chopper->id,
            'current_condition' => 100,
            'tuned_speed' => 85,
            'is_active' => false,
        ]);

        $heist = Heist::create([
            'name' => 'Haven Heist',
            'slug' => 'haven-heist',
            'description' => 'Chopper solo heist',
            'min_level' => 5,
            'energy_cost' => 10,
            'required_mask_count' => 1,
            'required_vehicle_type' => 'helicopter',
            'min_speed_required' => 50,
            'min_condition_required' => 30,
            'difficulty' => 'makkelijk',
            'payout_min' => 10000,
            'payout_max' => 20000,
            'respect_reward' => 10,
            'xp_reward' => 50,
            'is_gang_heist' => false,
        ]);

        $response = $this->actingAs($user)->post(route('heists.launch', $heist));
        $response->assertSessionHasNoErrors();

        $wornChopper->refresh();
        $pristineChopper->refresh();

        // The worn chopper was NOT used, so its condition remains unchanged at 35%
        $this->assertSame(35, $wornChopper->current_condition);
        // The pristine chopper was used, so it incurred wear and tear (below 100%)
        $this->assertLessThan(100, $pristineChopper->current_condition);
    }

    public function test_heist_update_news_article_exists_and_is_published(): void
    {
        $this->artisan('migrate');

        $this->assertDatabaseHas('news_articles', [
            'slug' => 'heist-garage-update-slimme-voertuigselectie-beste-conditie-chop-shop-slijtagebescherming',
            'is_published' => true,
        ]);
    }
}
