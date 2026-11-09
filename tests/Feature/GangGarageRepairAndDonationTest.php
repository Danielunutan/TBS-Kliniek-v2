<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\CharacterVehicle;
use App\Models\Gang;
use App\Models\GangProperty;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GangGarageRepairAndDonationTest extends TestCase
{
    use RefreshDatabase;

    private function createVehicle(string $name, int $price = 500000): Vehicle
    {
        return Vehicle::create([
            'name' => $name,
            'slug' => \Illuminate\Support\Str::slug($name) . '-' . rand(1000, 9999),
            'type' => 'auto',
            'speed' => 90,
            'price' => $price,
            'price_cash' => $price,
            'description' => 'Test vehicle',
        ]);
    }

    private function setupGangAndMember(int $treasury = 5000000, int $personalCash = 100000): array
    {
        $user = User::factory()->create();
        $char = Character::factory()->create([
            'user_id' => $user->id,
            'cash' => $personalCash,
            'bank' => $personalCash,
            'gang_role' => 'officer',
        ]);

        $gang = Gang::create([
            'name' => 'Syndicate ' . rand(100, 999),
            'tag' => 'SYN',
            'specialty' => 'Transport',
            'treasury' => $treasury,
            'leader_character_id' => $char->id,
        ]);

        // Provide 4 vehicle slots via property
        GangProperty::create([
            'gang_id' => $gang->id,
            'property_key' => 'garagebox_complex',
            'name' => 'Ondergronds Garageboxen Complex',
            'type' => 'storage',
            'vehicle_slots' => 4,
            'storage_capacity' => 75,
            'purchase_price' => 320000,
        ]);

        $char->update(['gang_id' => $gang->id]);

        return [$user, $char, $gang];
    }

    public function test_vehicle_in_gang_garage_repairs_with_gang_treasury_not_personal_funds(): void
    {
        [$user, $char, $gang] = $this->setupGangAndMember(treasury: 500000, personalCash: 100000);
        $veh = $this->createVehicle('Pantser Wagen', price: 200000);

        $charVehicle = CharacterVehicle::create([
            'character_id' => $char->id,
            'gang_id' => $gang->id,
            'vehicle_id' => $veh->id,
            'in_gang_garage' => true,
            'current_condition' => 50, // Damaged: 50% missing
        ]);

        $repairCost = $charVehicle->repair_cost;
        $this->assertGreaterThan(0, $repairCost);

        // Perform repair
        $response = $this->actingAs($user)->post(route('garage.repair', $charVehicle->id));
        $response->assertSessionHas('success');

        $charFresh = $char->fresh();
        $gangFresh = $gang->fresh();
        $vehFresh = $charVehicle->fresh();

        // 1. Vehicle is now 100% repaired
        $this->assertEquals(100, $vehFresh->current_condition);

        // 2. Personal cash and bank are 100% untouched!
        $this->assertEquals(100000, $charFresh->cash);
        $this->assertEquals(100000, $charFresh->bank);

        // 3. Treasury is deducted by exact repair cost
        $this->assertEquals(500000 - $repairCost, $gangFresh->treasury);
    }

    public function test_repair_in_gang_garage_fails_if_gang_treasury_is_insufficient(): void
    {
        [$user, $char, $gang] = $this->setupGangAndMember(treasury: 100, personalCash: 100000);
        $veh = $this->createVehicle('Pantser Wagen', price: 200000);

        $charVehicle = CharacterVehicle::create([
            'character_id' => $char->id,
            'gang_id' => $gang->id,
            'vehicle_id' => $veh->id,
            'in_gang_garage' => true,
            'current_condition' => 20, // High repair cost
        ]);

        // Attempting repair fails due to low bendekas
        $response = $this->actingAs($user)->post(route('garage.repair', $charVehicle->id));
        $response->assertSessionHas('error');

        $this->assertEquals(20, $charVehicle->fresh()->current_condition);
        $this->assertEquals(100, $gang->fresh()->treasury);
    }

    public function test_member_can_donate_vehicle_to_gang_and_it_becomes_gang_owned(): void
    {
        [$user, $char, $gang] = $this->setupGangAndMember(treasury: 1000000);
        $veh = $this->createVehicle('Smokkel Coupe', price: 150000);

        $charVehicle = CharacterVehicle::create([
            'character_id' => $char->id,
            'vehicle_id' => $veh->id,
            'in_gang_garage' => false,
            'is_gang_owned' => false,
            'current_condition' => 100,
        ]);

        $oldRespect = (int) $char->respect;

        // Donate vehicle to gang
        $response = $this->actingAs($user)->post(route('gangs.garage.donate-vehicle', $charVehicle));
        $response->assertSessionHas('success');

        $fresh = $charVehicle->fresh();
        $this->assertTrue($fresh->is_gang_owned);
        $this->assertTrue($fresh->in_gang_garage);
        $this->assertEquals($gang->id, $fresh->gang_id);
        $this->assertEquals($char->id, $fresh->donated_by_character_id);
        $this->assertNotNull($fresh->donated_at);

        // Donor gains 50 respect (which also boosts gang total respect)
        $this->assertEquals($oldRespect + 50, (int) $char->fresh()->respect);

        // Member cannot retrieve a donated vehicle
        $retrieveResponse = $this->actingAs($user)->post(route('gangs.garage.retrieve-vehicle', $charVehicle));
        $retrieveResponse->assertSessionHas('error');
        $this->assertTrue($charVehicle->fresh()->in_gang_garage);
    }
}
