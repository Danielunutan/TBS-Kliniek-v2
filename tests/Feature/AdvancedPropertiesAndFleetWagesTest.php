<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\CharacterVehicle;
use App\Models\Gang;
use App\Models\GangProperty;
use App\Models\Heist;
use App\Models\User;
use App\Models\Vehicle;
use App\Services\GangWageService;
use App\Support\GangPropertyCatalog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdvancedPropertiesAndFleetWagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_smokkelkiosken_scale_progressively_in_hourly_wage(): void
    {
        $service = app(GangWageService::class);

        // Small gang with 500 kiosks: 500 * 100 = 50.000 (+150 base = 50150)
        $smallGang = Gang::create([
            'name' => 'Small Kiosks',
            'tag' => 'SK',
            'specialty' => 'Witwassen',
            'treasury' => 1000000,
            'bling_shops' => 500,
        ]);
        $this->assertEquals(50150, $service->calculateHourlyWage($smallGang));

        // Giant gang with 2,083,684 kiosks: progressive bracket scaling yields 9,467,368 + 150 base = 9,467,518
        $gang = Gang::create([
            'name' => 'Kiosk Titans',
            'tag' => 'KT',
            'specialty' => 'Witwassen',
            'treasury' => 1000000,
            'bling_shops' => 2083684,
        ]);

        $wage = $service->calculateHourlyWage($gang);
        $this->assertEquals(9467518, $wage);

        $breakdown = $service->getWageBreakdown($gang);
        $this->assertEquals(2083684, $breakdown['kiosks_count']);
        $this->assertEquals(9467368, $breakdown['kiosks_income']);
    }

    public function test_stored_vehicle_fleet_contributes_to_hourly_wage(): void
    {
        $service = app(GangWageService::class);

        $gang = Gang::create([
            'name' => 'Fleet Syndicate',
            'tag' => 'FS',
            'specialty' => 'Transport',
            'treasury' => 1000000,
            'bling_shops' => 0,
        ]);

        $owner = Character::factory()->create([
            'gang_id' => $gang->id,
            'gang_role' => 'member',
            'level' => 1,
        ]);

        $car = Vehicle::where('type', 'auto')->first();
        $heli = Vehicle::where('type', 'helikopter')->first();

        if ($car) {
            CharacterVehicle::create([
                'character_id' => $owner->id,
                'vehicle_id' => $car->id,
                'in_gang_garage' => true,
                'gang_id' => $gang->id,
                'current_condition' => 100,
            ]);
        }

        if ($heli) {
            CharacterVehicle::create([
                'character_id' => $owner->id,
                'vehicle_id' => $heli->id,
                'in_gang_garage' => true,
                'gang_id' => $gang->id,
                'current_condition' => 100,
            ]);
        }

        $wageWithFleet = $service->calculateHourlyWage($gang);
        // Base: 150 + car (4000) + heli (25000) = 29150
        $this->assertGreaterThan(150, $wageWithFleet);
        if ($car && $heli) {
            $this->assertEquals(29150, $wageWithFleet);
        }
    }

    public function test_bende_respect_adds_percentage_bonus(): void
    {
        $service = app(GangWageService::class);

        $gang = Gang::create([
            'name' => 'Respect Cartel',
            'tag' => 'RC',
            'specialty' => 'Dominantie',
            'treasury' => 1000000,
            'bling_shops' => 0,
        ]);

        Character::factory()->create([
            'gang_id' => $gang->id,
            'respect' => 2000000, // 2 million respect = +20% bonus
        ]);

        $wage = $service->calculateHourlyWage($gang);
        // Base: 150 * (1 + 20%) = round(150 * 1.20) = 180
        $this->assertEquals(180, $wage);
    }

    public function test_new_production_properties_exist_in_catalog(): void
    {
        $catalog = GangPropertyCatalog::all();

        $this->assertArrayHasKey('administratie_kantoor', $catalog);
        $this->assertEquals('production', $catalog['administratie_kantoor']['category']);
        $this->assertEquals(120, $catalog['administratie_kantoor']['production_duration_minutes']);

        $this->assertArrayHasKey('vluchtplan_complex', $catalog);
        $this->assertEquals('production', $catalog['vluchtplan_complex']['category']);
        $this->assertEquals(240, $catalog['vluchtplan_complex']['production_duration_minutes']);

        $this->assertArrayHasKey('smokkelhaven_terminal', $catalog);
        $this->assertEquals('production', $catalog['smokkelhaven_terminal']['category']);
        $this->assertEquals(300, $catalog['smokkelhaven_terminal']['production_duration_minutes']);
    }

    public function test_new_heists_are_seeded_and_playable(): void
    {
        $adminRoof = Heist::where('slug', 'kliniek-administratie-roof')->first();
        $this->assertNotNull($adminRoof);
        $this->assertFalse($adminRoof->is_gang_heist);
        $this->assertEquals(6, $adminRoof->min_level);

        $vluchtplanHeist = Heist::where('slug', 'bende-heist-helikopter-vluchtplan')->first();
        $this->assertNotNull($vluchtplanHeist);
        $this->assertTrue($vluchtplanHeist->is_gang_heist);
        $this->assertEquals(4, $vluchtplanHeist->min_gang_members);
        $this->assertEquals('helikopter', $vluchtplanHeist->required_vehicle_type);
        $this->assertContains('vluchtplan_complex', $vluchtplanHeist->required_property_keys);
    }
}
