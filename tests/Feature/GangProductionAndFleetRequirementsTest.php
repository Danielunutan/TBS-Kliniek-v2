<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\CharacterVehicle;
use App\Models\Gang;
use App\Models\GangProperty;
use App\Models\User;
use App\Models\Vehicle;
use App\Services\GangWageService;
use App\Support\GangPropertyCatalog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class GangProductionAndFleetRequirementsTest extends TestCase
{
    use RefreshDatabase;

    private function createVehicle(string $name, string $type, int $speed = 80): Vehicle
    {
        return Vehicle::create([
            'name' => $name,
            'slug' => Str::slug($name).'-'.rand(1000, 9999),
            'type' => $type,
            'speed' => $speed,
            'price' => 100000,
            'description' => 'Test vehicle',
        ]);
    }

    private function createGangWithLeader(): array
    {
        $user = User::factory()->create();
        $char = Character::factory()->create([
            'user_id' => $user->id,
            'gang_role' => 'leader',
        ]);
        $gang = Gang::create([
            'name' => 'Syndicate '.rand(100, 999),
            'tag' => 'SYN',
            'specialty' => 'Transport',
            'treasury' => 10000000,
            'leader_character_id' => $char->id,
        ]);
        $char->update(['gang_id' => $gang->id]);

        return [$user, $char, $gang];
    }

    public function test_ten_new_properties_exist_in_catalog_and_wage_service(): void
    {
        $expectedNewKeys = [
            'pantser_konvooi_depot',
            'offshore_smokkel_dok',
            'luchtdropping_hangar',
            'straatrace_omkat_hub',
            'diamant_slijperij',
            'militaire_drone_basis',
            'casino_witwas_complex',
            'spionage_aftap_bunker',
            'chemische_raffinaderij',
            'kartel_distributie_fort',
        ];

        $catalog = GangPropertyCatalog::all();

        foreach ($expectedNewKeys as $key) {
            $this->assertArrayHasKey($key, $catalog, "Property key {$key} missing from GangPropertyCatalog");
            $this->assertEquals('production', $catalog[$key]['category']);
            $this->assertGreaterThan(0, $catalog[$key]['production_reward_cash']);
            $this->assertArrayHasKey($key, GangWageService::PROPERTY_HOURLY_BASE, "Property key {$key} missing from GangWageService::PROPERTY_HOURLY_BASE");
            $this->assertGreaterThan(0, GangWageService::PROPERTY_HOURLY_BASE[$key]);
        }
    }

    public function test_fleet_requirements_check_properly_validates_vehicles(): void
    {
        [$user, $char, $gang] = $this->createGangWithLeader();

        // 1. Without any vehicles, pantser_konvooi_depot requires 2 cars
        $check = GangPropertyCatalog::checkFleetRequirements($gang, 'pantser_konvooi_depot');
        $this->assertFalse($check['qualified']);
        $this->assertNotEmpty($check['reason']);

        // Autonomous property doesn't require vehicles
        $droneCheck = GangPropertyCatalog::checkFleetRequirements($gang, 'militaire_drone_basis');
        $this->assertTrue($droneCheck['qualified']);

        // Add 1 car with 100% condition
        $car = $this->createVehicle('Armored Truck', 'auto', 70);

        CharacterVehicle::create([
            'character_id' => $char->id,
            'gang_id' => $gang->id,
            'vehicle_id' => $car->id,
            'in_gang_garage' => true,
            'current_condition' => 100,
        ]);

        // Still needs 2 cars
        $check2 = GangPropertyCatalog::checkFleetRequirements($gang, 'pantser_konvooi_depot');
        $this->assertFalse($check2['qualified']);

        // Add second car
        CharacterVehicle::create([
            'character_id' => $char->id,
            'gang_id' => $gang->id,
            'vehicle_id' => $car->id,
            'in_gang_garage' => true,
            'current_condition' => 100,
        ]);

        // Now qualified!
        $check3 = GangPropertyCatalog::checkFleetRequirements($gang, 'pantser_konvooi_depot');
        $this->assertTrue($check3['qualified']);
    }

    public function test_starting_production_requires_fleet_and_sets_status(): void
    {
        [$user, $char, $gang] = $this->createGangWithLeader();

        $prop = GangProperty::create([
            'gang_id' => $gang->id,
            'property_key' => 'offshore_smokkel_dok',
            'name' => 'Diepzee Speedboot Dok & Smokkelloods',
            'type' => 'production',
            'purchase_price' => 3200000,
            'storage_capacity' => 220,
            'vehicle_slots' => 4,
            'npc_capacity' => 3,
            'production_duration_minutes' => 270,
            'production_reward_cash' => 620000,
            'production_reward_respect' => 200,
            'production_status' => 'idle',
        ]);

        // Trying to start production without 2 boats fails
        $response = $this->actingAs($user)->post(route('gangs.panden.start-production', $prop));
        $response->assertSessionHas('error');
        $this->assertEquals('idle', $prop->fresh()->production_status);

        // Add 2 boats in gang garage
        $boat = $this->createVehicle('Cigarette Smokkelboot', 'boot', 65);
        for ($i = 0; $i < 2; $i++) {
            CharacterVehicle::create([
                'character_id' => $char->id,
                'gang_id' => $gang->id,
                'vehicle_id' => $boat->id,
                'in_gang_garage' => true,
                'current_condition' => 80,
            ]);
        }

        // Now start production succeeds
        $response2 = $this->actingAs($user)->post(route('gangs.panden.start-production', $prop));
        $response2->assertSessionHas('success');
        $this->assertEquals('producing', $prop->fresh()->production_status);
        $this->assertNotNull($prop->fresh()->production_ends_at);
    }
}
