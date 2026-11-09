<?php

namespace Tests\Feature;

use App\Models\CharacterVehicle;
use App\Models\Gang;
use App\Models\GangProperty;
use App\Models\Heist;
use App\Models\User;
use App\Models\Vehicle;
use Database\Seeders\VehicleAndHeistSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VehiclesAndSpecialistsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->seed(VehicleAndHeistSeeder::class);
    }

    public function test_airplanes_and_motorcycles_qualify_for_heists(): void
    {
        $airHeist = Heist::where('required_vehicle_type', 'helikopter')->first();
        if ($airHeist) {
            $airplane = Vehicle::where('type', 'vliegtuig')->first();
            if (! $airplane) {
                $airplane = Vehicle::create([
                    'name' => 'Cessna Test Vliegtuig',
                    'slug' => 'cessna-test-vliegtuig',
                    'type' => 'vliegtuig',
                    'speed_pct' => 95,
                    'reliability_pct' => 90,
                ]);
            }

            $user = User::where('email', 'demo@tbskliniek.nl')->first();
            $char = $user->character;

            $charAirplane = CharacterVehicle::create([
                'character_id' => $char->id,
                'vehicle_id' => $airplane->id,
                'current_condition' => 100,
                'is_active' => true,
            ]);

            $this->assertTrue($airHeist->vehicleQualifies($charAirplane));
        }

        $carHeist = Heist::where('required_vehicle_type', 'auto')->first();
        if ($carHeist) {
            $motor = Vehicle::where('type', 'motor')->first();
            if (! $motor) {
                $motor = Vehicle::create([
                    'name' => 'Yamaha Test Motor',
                    'slug' => 'yamaha-test-motor',
                    'type' => 'motor',
                    'speed_pct' => 90,
                    'reliability_pct' => 85,
                ]);
            }

            $user = User::where('email', 'demo@tbskliniek.nl')->first();
            $char = $user->character;

            $charMotor = CharacterVehicle::create([
                'character_id' => $char->id,
                'vehicle_id' => $motor->id,
                'current_condition' => 100,
                'is_active' => true,
            ]);

            $this->assertTrue($carHeist->vehicleQualifies($charMotor));
        }
    }

    public function test_airplane_owner_travels_for_free(): void
    {
        $user = User::where('email', 'demo@tbskliniek.nl')->first();
        $char = $user->character;
        $char->update([
            'cash' => 0,
            'current_location' => 'Paviljoen 1 (Gesloten)',
        ]);

        $airplane = Vehicle::where('type', 'vliegtuig')->first() ?? Vehicle::create([
            'name' => 'Gulfstream Test Jet',
            'slug' => 'gulfstream-test-jet',
            'type' => 'vliegtuig',
            'speed_pct' => 100,
            'reliability_pct' => 95,
        ]);

        CharacterVehicle::create([
            'character_id' => $char->id,
            'vehicle_id' => $airplane->id,
            'current_condition' => 100,
            'is_active' => true,
        ]);

        $this->actingAs($user);

        $response = $this->post(route('travel.move'), [
            'location' => 'paviljoen-2-half-open',
        ]);

        $response->assertSessionHas('success');
        $this->assertStringContainsString('Privévlucht geland', session('success'));

        $char->refresh();
        $this->assertEquals('Paviljoen 2 (Half-open)', $char->current_location);
    }

    public function test_gang_production_with_batch_specialist_deducts_treasury_and_applies_bonus(): void
    {
        $user = User::where('email', 'demo@tbskliniek.nl')->first();
        $char = $user->character;

        $gang = Gang::create([
            'name' => 'Specialist Cartel',
            'tag' => 'SPEC',
            'leader_character_id' => $char->id,
            'treasury' => 5000000,
            'specialty' => 'violence',
            'description' => 'Specialist cartel test',
        ]);

        $char->update([
            'gang_id' => $gang->id,
            'gang_role' => 'leader',
        ]);

        $property = GangProperty::create([
            'gang_id' => $gang->id,
            'property_key' => 'speed_lab',
            'name' => 'Speed Lab X',
            'type' => 'production',
            'purchase_price' => 1000000,
            'production_status' => 'idle',
            'production_duration_minutes' => 60,
            'production_reward_cash' => 200000,
            'production_reward_respect' => 25,
        ]);

        $this->actingAs($user);

        // Start with Dr. Walter V. (chemisch_specialist, 25% van €200.000 = €50.000, halves time to 30 min)
        $response = $this->post(route('gangs.panden.start-production', $property), [
            'batch_specialist' => 'chemisch_specialist',
        ]);

        $response->assertSessionHas('success');

        $gang->refresh();
        $property->refresh();

        // €50.000 deducted from treasury!
        $this->assertEquals(4950000, $gang->treasury);
        $this->assertEquals('producing', $property->production_status);
        $this->assertEquals('chemisch_specialist', $property->assigned_npc_key);
        // Duration is halved from 60 to 30 min
        $this->assertEquals(30, $property->effective_duration_minutes);
    }
}
