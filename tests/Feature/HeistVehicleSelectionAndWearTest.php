<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\CharacterVehicle;
use App\Models\Gang;
use App\Models\GangProperty;
use App\Models\Heist;
use App\Models\User;
use App\Models\Vehicle;
use Database\Seeders\VehicleAndHeistSeeder;
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

    public function test_user_cannot_select_vehicle_owned_by_another_player(): void
    {
        $user1 = User::factory()->create();
        $char1 = Character::create([
            'user_id' => $user1->id,
            'name' => 'Driver1',
            'cash' => 50000,
            'bank' => 100000,
            'level' => 10,
            'energy' => 100,
            'max_energy' => 100,
            'masks' => 20,
            'is_onboarded' => true,
        ]);

        $user2 = User::factory()->create();
        $char2 = Character::create([
            'user_id' => $user2->id,
            'name' => 'Driver2',
            'cash' => 50000,
            'bank' => 100000,
            'level' => 10,
            'energy' => 100,
            'max_energy' => 100,
            'masks' => 20,
            'is_onboarded' => true,
        ]);

        $car = Vehicle::create([
            'name' => 'Fast Car',
            'slug' => 'fast-car',
            'type' => 'car',
            'price_cash' => 50000,
            'speed_pct' => 80,
            'reliability_pct' => 80,
            'min_level' => 1,
        ]);

        $char1Car = CharacterVehicle::create([
            'character_id' => $char1->id,
            'vehicle_id' => $car->id,
            'current_condition' => 90,
            'tuned_speed' => 80,
        ]);

        $char2Car = CharacterVehicle::create([
            'character_id' => $char2->id,
            'vehicle_id' => $car->id,
            'current_condition' => 95,
            'tuned_speed' => 80,
        ]);

        $heist = Heist::create([
            'name' => 'Solo Street Heist',
            'slug' => 'solo-street-heist',
            'min_level' => 1,
            'energy_cost' => 10,
            'required_mask_count' => 1,
            'required_vehicle_type' => 'car',
            'min_speed_required' => 50,
            'min_condition_required' => 50,
            'difficulty' => 'makkelijk',
            'payout_min' => 5000,
            'payout_max' => 10000,
            'respect_reward' => 5,
            'xp_reward' => 25,
            'is_gang_heist' => false,
        ]);

        // Char1 tries to use Char2's car
        $response = $this->actingAs($user1)->post(route('heists.launch', $heist), [
            'character_vehicle_id' => $char2Car->id,
        ]);

        $response->assertSessionHas('error', 'Dit voertuig is niet in jouw bezit en kan niet worden geselecteerd!');
        $char2Car->refresh();
        $this->assertSame(95, $char2Car->current_condition);
    }

    public function test_user_cannot_select_destroyed_vehicle_or_total_loss(): void
    {
        $user = User::factory()->create();
        $char = Character::create([
            'user_id' => $user->id,
            'name' => 'WreckDriver',
            'cash' => 50000,
            'bank' => 100000,
            'level' => 10,
            'energy' => 100,
            'max_energy' => 100,
            'masks' => 20,
            'is_onboarded' => true,
        ]);

        $car = Vehicle::create([
            'name' => 'Wrecked Sedan',
            'slug' => 'wrecked-sedan',
            'type' => 'car',
            'price_cash' => 20000,
            'speed_pct' => 60,
            'reliability_pct' => 60,
            'min_level' => 1,
        ]);

        $wreckedCar = CharacterVehicle::create([
            'character_id' => $char->id,
            'vehicle_id' => $car->id,
            'current_condition' => 0,
            'tuned_speed' => 60,
        ]);

        $this->assertTrue($wreckedCar->isDestroyed());
        $this->assertSame(100, $wreckedCar->damage_percentage);
        $this->assertSame(100, $wreckedCar->wear_percentage);

        $heist = Heist::create([
            'name' => 'Target Heist',
            'slug' => 'target-heist',
            'min_level' => 1,
            'energy_cost' => 10,
            'required_mask_count' => 1,
            'required_vehicle_type' => 'car',
            'min_speed_required' => 20,
            'min_condition_required' => 0,
            'difficulty' => 'makkelijk',
            'payout_min' => 5000,
            'payout_max' => 10000,
            'respect_reward' => 5,
            'xp_reward' => 25,
            'is_gang_heist' => false,
        ]);

        $response = $this->actingAs($user)->post(route('heists.launch', $heist), [
            'character_vehicle_id' => $wreckedCar->id,
        ]);

        $response->assertSessionHas('error');
        $this->assertStringContainsString('total loss', session('error'));
    }

    public function test_user_cannot_select_vehicle_in_gang_garage_for_solo_heist(): void
    {
        $user = User::factory()->create();
        $char = Character::create([
            'user_id' => $user->id,
            'name' => 'GarageTester',
            'cash' => 50000,
            'bank' => 100000,
            'level' => 10,
            'energy' => 100,
            'max_energy' => 100,
            'masks' => 20,
            'is_onboarded' => true,
        ]);

        $car = Vehicle::create([
            'name' => 'Gang Van',
            'slug' => 'gang-van',
            'type' => 'car',
            'price_cash' => 30000,
            'speed_pct' => 60,
            'reliability_pct' => 80,
            'min_level' => 1,
        ]);

        $gangCar = CharacterVehicle::create([
            'character_id' => $char->id,
            'vehicle_id' => $car->id,
            'current_condition' => 80,
            'tuned_speed' => 60,
            'in_gang_garage' => true,
        ]);

        $heist = Heist::create([
            'name' => 'Solo Mini Heist',
            'slug' => 'solo-mini-heist',
            'min_level' => 1,
            'energy_cost' => 10,
            'required_mask_count' => 1,
            'required_vehicle_type' => 'car',
            'min_speed_required' => 40,
            'min_condition_required' => 30,
            'difficulty' => 'makkelijk',
            'payout_min' => 5000,
            'payout_max' => 10000,
            'respect_reward' => 5,
            'xp_reward' => 25,
            'is_gang_heist' => false,
        ]);

        $response = $this->actingAs($user)->post(route('heists.launch', $heist), [
            'character_vehicle_id' => $gangCar->id,
        ]);

        $response->assertSessionHas('error');
        $this->assertStringContainsString('Bende Garage', session('error'));
    }

    public function test_user_can_explicitly_select_qualifying_vehicle(): void
    {
        $user = User::factory()->create();
        $char = Character::create([
            'user_id' => $user->id,
            'name' => 'FleetSelector',
            'cash' => 50000,
            'bank' => 100000,
            'level' => 10,
            'energy' => 100,
            'max_energy' => 100,
            'masks' => 20,
            'is_onboarded' => true,
        ]);

        $fastCar = Vehicle::create([
            'name' => 'Fast Exotic',
            'slug' => 'fast-exotic',
            'type' => 'car',
            'price_cash' => 100000,
            'speed_pct' => 95,
            'reliability_pct' => 80,
            'min_level' => 1,
        ]);

        $reliableCar = Vehicle::create([
            'name' => 'Reliable Sedan',
            'slug' => 'reliable-sedan',
            'type' => 'car',
            'price_cash' => 40000,
            'speed_pct' => 70,
            'reliability_pct' => 80,
            'min_level' => 1,
        ]);

        $fastCharVehicle = CharacterVehicle::create([
            'character_id' => $char->id,
            'vehicle_id' => $fastCar->id,
            'current_condition' => 100,
            'tuned_speed' => 95,
        ]);

        $reliableCharVehicle = CharacterVehicle::create([
            'character_id' => $char->id,
            'vehicle_id' => $reliableCar->id,
            'current_condition' => 100,
            'tuned_speed' => 70,
        ]);

        $heist = Heist::create([
            'name' => 'City Heist',
            'slug' => 'city-heist',
            'min_level' => 1,
            'energy_cost' => 10,
            'required_mask_count' => 1,
            'required_vehicle_type' => 'car',
            'min_speed_required' => 50,
            'min_condition_required' => 50,
            'difficulty' => 'makkelijk',
            'payout_min' => 5000,
            'payout_max' => 10000,
            'respect_reward' => 5,
            'xp_reward' => 25,
            'is_gang_heist' => false,
        ]);

        // Explicitly select the reliableCar instead of the auto-selected fastest car
        $response = $this->actingAs($user)->post(route('heists.launch', $heist), [
            'character_vehicle_id' => $reliableCharVehicle->id,
        ]);

        $response->assertSessionHasNoErrors();
        $reliableCharVehicle->refresh();
        $fastCharVehicle->refresh();

        // The chosen reliable car incurred wear, while the fast car remains at 100%
        $this->assertLessThan(100, $reliableCharVehicle->current_condition);
        $this->assertSame(100, $fastCharVehicle->current_condition);
    }

    public function test_reinforced_armored_vehicle_provides_wear_reduction(): void
    {
        $vehicle = Vehicle::create([
            'name' => 'Gepantserde Mercedes G63 AMG V8',
            'slug' => 'gepantserde-mercedes-g63-amg-v8',
            'type' => 'car',
            'price_cash' => 150000,
            'speed_pct' => 85,
            'reliability_pct' => 96,
            'min_level' => 1,
        ]);

        $charVehicle = new CharacterVehicle([
            'current_condition' => 100,
        ]);
        $charVehicle->setRelation('vehicle', $vehicle);

        $this->assertTrue($charVehicle->isReinforced());
        $this->assertSame(0.80, $charVehicle->getWearMultiplier());
    }

    public function test_comatose_or_needing_revive_serum_player_blocked_from_heist(): void
    {
        $user = User::factory()->create();
        $char = Character::create([
            'user_id' => $user->id,
            'name' => 'ComaVictim',
            'cash' => 50000,
            'bank' => 100000,
            'level' => 10,
            'energy' => 100,
            'max_energy' => 100,
            'masks' => 20,
            'is_onboarded' => true,
            'deaths_count' => 10,
            'perks' => ['liquidation_streak_deaths' => 10],
        ]);

        $this->assertTrue($char->needsReviveSerum());

        $heist = Heist::create([
            'name' => 'Block Heist',
            'slug' => 'block-heist',
            'min_level' => 1,
            'energy_cost' => 10,
            'required_mask_count' => 1,
            'required_vehicle_type' => 'any',
            'min_speed_required' => 10,
            'min_condition_required' => 10,
            'difficulty' => 'makkelijk',
            'payout_min' => 5000,
            'payout_max' => 10000,
            'respect_reward' => 5,
            'xp_reward' => 25,
            'is_gang_heist' => false,
        ]);

        $response = $this->actingAs($user)->post(route('heists.launch', $heist));
        $response->assertSessionHas('error');
        $this->assertStringContainsString('Code Blauw', session('error'));
    }

    public function test_heist_methods_handle_zero_boundaries_without_errors(): void
    {
        $heist = new Heist([
            'min_speed_required' => 0,
            'difficulty' => 'makkelijk',
        ]);

        $cv = new CharacterVehicle([
            'current_condition' => 0,
            'tuned_speed' => 0,
        ]);

        $this->assertSame(0, $heist->calculateSpeedBonus(null));
        $this->assertSame(0, $heist->calculateConditionBonus(null));

        $speedBonus = $heist->calculateSpeedBonus($cv);
        $conditionBonus = $heist->calculateConditionBonus($cv);

        $this->assertIsInt($speedBonus);
        $this->assertIsInt($conditionBonus);
    }

    public function test_heist_getaway_vehicles_relation_returns_valid_relation(): void
    {
        $heist = Heist::create([
            'name' => 'Relation Test Heist',
            'slug' => 'relation-test-heist',
            'min_level' => 1,
            'energy_cost' => 10,
            'required_mask_count' => 1,
            'required_vehicle_type' => 'car',
            'min_speed_required' => 20,
            'min_condition_required' => 20,
            'difficulty' => 'makkelijk',
            'payout_min' => 1000,
            'payout_max' => 2000,
            'respect_reward' => 5,
            'xp_reward' => 25,
            'is_gang_heist' => false,
        ]);

        $this->assertNotNull($heist->getawayVehicles);
        $this->assertCount(0, $heist->getawayVehicles);
    }

    public function test_gang_heists_index_tab_renders_without_500_error_when_gang_has_vehicle(): void
    {
        $user = User::factory()->create();
        $char = Character::create([
            'user_id' => $user->id,
            'name' => 'BendeLeader',
            'level' => 10,
            'energy' => 100,
            'max_energy' => 100,
            'masks' => 20,
            'is_onboarded' => true,
        ]);
        $gang = Gang::create([
            'name' => 'TestBende',
            'tag' => 'TB',
            'leader_character_id' => $char->id,
            'specialty' => 'combat',
            'masks' => 50,
        ]);
        $char->update(['gang_id' => $gang->id]);

        $car = Vehicle::create([
            'name' => 'Bende Wagen',
            'slug' => 'bende-wagen',
            'type' => 'car',
            'price_cash' => 50000,
            'speed_pct' => 80,
            'reliability_pct' => 80,
            'min_level' => 1,
        ]);

        CharacterVehicle::create([
            'character_id' => $char->id,
            'gang_id' => $gang->id,
            'vehicle_id' => $car->id,
            'in_gang_garage' => true,
            'current_condition' => 100,
            'tuned_speed' => 80,
        ]);

        Heist::create([
            'name' => 'Grote Bende Heist',
            'slug' => 'grote-bende-heist',
            'min_level' => 1,
            'energy_cost' => 10,
            'required_mask_count' => 1,
            'required_vehicle_type' => 'car',
            'min_speed_required' => 50,
            'min_condition_required' => 50,
            'difficulty' => 'normaal',
            'payout_min' => 5000,
            'payout_max' => 10000,
            'respect_reward' => 5,
            'xp_reward' => 25,
            'is_gang_heist' => true,
            'min_gang_members' => 1,
            'gang_treasury_share_pct' => 50,
            'gang_respect_reward' => 100,
        ]);

        $response = $this->actingAs($user)->get(route('heists.index', ['tab' => 'gang']));
        $response->assertOk();
        $response->assertSee('Grote Bende Heist');
        $response->assertSee('Bende Wagen');
    }

    public function test_gang_heists_tab_renders_cleanly_without_gang(): void
    {
        $user = User::factory()->create();
        Character::create([
            'user_id' => $user->id,
            'name' => 'SoloWanderer',
            'level' => 10,
            'energy' => 100,
            'max_energy' => 100,
            'masks' => 20,
            'is_onboarded' => true,
        ]);

        Heist::create([
            'name' => 'Gang Syndicate Heist',
            'slug' => 'gang-syndicate-heist',
            'min_level' => 1,
            'energy_cost' => 10,
            'required_mask_count' => 1,
            'required_vehicle_type' => 'car',
            'min_speed_required' => 50,
            'min_condition_required' => 50,
            'difficulty' => 'normaal',
            'payout_min' => 5000,
            'payout_max' => 10000,
            'respect_reward' => 5,
            'xp_reward' => 25,
            'is_gang_heist' => true,
            'min_gang_members' => 1,
        ]);

        $response = $this->actingAs($user)->get(route('heists.index', ['tab' => 'gang']));
        $response->assertOk();
        $response->assertSee('Geen Bende Gevonden');
    }

    public function test_gang_heists_tab_renders_cleanly_when_gang_has_no_vehicles(): void
    {
        $user = User::factory()->create();
        $char = Character::create([
            'user_id' => $user->id,
            'name' => 'PedestrianGangster',
            'level' => 10,
            'energy' => 100,
            'max_energy' => 100,
            'masks' => 20,
            'is_onboarded' => true,
        ]);
        $gang = Gang::create([
            'name' => 'Walkers Guild',
            'tag' => 'WG',
            'leader_character_id' => $char->id,
            'specialty' => 'combat',
            'masks' => 50,
        ]);
        $char->update(['gang_id' => $gang->id]);

        Heist::create([
            'name' => 'Pedestrian Target Heist',
            'slug' => 'pedestrian-target-heist',
            'min_level' => 1,
            'energy_cost' => 10,
            'required_mask_count' => 1,
            'required_vehicle_type' => 'car',
            'min_speed_required' => 50,
            'min_condition_required' => 50,
            'difficulty' => 'normaal',
            'payout_min' => 5000,
            'payout_max' => 10000,
            'respect_reward' => 5,
            'xp_reward' => 25,
            'is_gang_heist' => true,
            'min_gang_members' => 1,
        ]);

        $response = $this->actingAs($user)->get(route('heists.index', ['tab' => 'gang']));
        $response->assertOk();
        $response->assertSee('Koppel Wagen in Bende Garage');
    }

    public function test_launching_gang_heist_deducts_masks_and_applies_wear(): void
    {
        $user = User::factory()->create();
        $char = Character::create([
            'user_id' => $user->id,
            'name' => 'HeistBoss',
            'level' => 10,
            'energy' => 100,
            'max_energy' => 100,
            'masks' => 0,
            'is_onboarded' => true,
        ]);
        $gang = Gang::create([
            'name' => 'Crown Syndicate',
            'tag' => 'CS',
            'leader_character_id' => $char->id,
            'specialty' => 'combat',
            'masks' => 10,
            'treasury' => 100000,
        ]);
        $char->update(['gang_id' => $gang->id]);

        $car = Vehicle::create([
            'name' => 'Syndicate Van',
            'slug' => 'syndicate-van',
            'type' => 'car',
            'price_cash' => 50000,
            'speed_pct' => 80,
            'reliability_pct' => 80,
            'min_level' => 1,
        ]);

        $cv = CharacterVehicle::create([
            'character_id' => $char->id,
            'gang_id' => $gang->id,
            'vehicle_id' => $car->id,
            'in_gang_garage' => true,
            'current_condition' => 100,
            'tuned_speed' => 80,
        ]);

        $heist = Heist::create([
            'name' => 'Depot Raad Heist',
            'slug' => 'depot-raad-heist',
            'min_level' => 1,
            'energy_cost' => 10,
            'required_mask_count' => 5,
            'required_vehicle_type' => 'car',
            'min_speed_required' => 50,
            'min_condition_required' => 50,
            'difficulty' => 'makkelijk',
            'payout_min' => 5000,
            'payout_max' => 10000,
            'respect_reward' => 5,
            'xp_reward' => 25,
            'is_gang_heist' => true,
            'min_gang_members' => 1,
            'gang_treasury_share_pct' => 50,
            'gang_respect_reward' => 50,
        ]);

        $response = $this->actingAs($user)->post(route('heists.launch', $heist), [
            'character_vehicle_id' => $cv->id,
        ]);

        $response->assertSessionHasNoErrors();
        $gang->refresh();
        $cv->refresh();

        // Masks were deducted from gang depot (10 - 5 = 5)
        $this->assertSame(5, $gang->masks);
        // Vehicle condition was degraded due to wear
        $this->assertLessThan(100, $cv->current_condition);
    }

    public function test_gang_has_required_property_handles_strings_and_empty_inputs(): void
    {
        $heist = new Heist([
            'is_gang_heist' => true,
            'required_property_keys' => 'kleine_loods,haven_loods',
        ]);

        $gang = Gang::create([
            'name' => 'Property Gang',
            'tag' => 'PG',
            'specialty' => 'combat',
        ]);

        GangProperty::create([
            'gang_id' => $gang->id,
            'property_key' => 'kleine_loods',
            'name' => 'Kleine Loods',
            'type' => 'storage',
            'purchase_price' => 50000,
        ]);

        $this->assertTrue($heist->gangHasRequiredProperty($gang));

        // Corrupted / empty property keys safely return true
        $corruptHeist = new Heist([
            'is_gang_heist' => true,
            'required_property_keys' => '',
        ]);
        $this->assertTrue($corruptHeist->gangHasRequiredProperty($gang));
    }

    public function test_gang_heists_tab_renders_with_seeded_heists_and_gang_properties(): void
    {
        $this->seed(VehicleAndHeistSeeder::class);

        $user = User::factory()->create();
        $char = Character::create([
            'user_id' => $user->id,
            'name' => 'SeededGangster',
            'level' => 15,
            'energy' => 100,
            'max_energy' => 100,
            'masks' => 50,
            'is_onboarded' => true,
        ]);

        $gang = Gang::create([
            'name' => 'Elite Cartel',
            'tag' => 'EC',
            'leader_character_id' => $char->id,
            'specialty' => 'combat',
            'masks' => 100,
            'treasury' => 5000000,
            'respect' => 1000,
        ]);
        $char->update(['gang_id' => $gang->id]);

        GangProperty::create([
            'gang_id' => $gang->id,
            'property_key' => 'chop_shop_garage',
            'name' => 'Chop-Shop Garage',
            'type' => 'storage',
            'purchase_price' => 100000,
        ]);

        $car = Vehicle::first();
        CharacterVehicle::create([
            'character_id' => $char->id,
            'gang_id' => $gang->id,
            'vehicle_id' => $car->id,
            'in_gang_garage' => true,
            'current_condition' => 95,
            'tuned_speed' => 90,
        ]);

        $response = $this->actingAs($user)->get(route('heists.index', ['tab' => 'gang']));
        $response->assertOk();
        $response->assertSee('Het Heist Planningsbord');
        $response->assertSee('Bende Heists');
        $response->assertSee('Chop-Shop: -25% Slijtage');
    }
}
