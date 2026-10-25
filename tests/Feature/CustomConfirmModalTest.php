<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\CharacterVehicle;
use App\Models\Gang;
use App\Models\User;
use App\Models\Vehicle;
use Database\Seeders\VehicleAndHeistSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomConfirmModalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->seed(VehicleAndHeistSeeder::class);
    }

    public function test_heists_page_has_custom_confirm_attributes_and_no_native_browser_confirm(): void
    {
        $user = User::factory()->create();
        $character = Character::create([
            'user_id' => $user->id,
            'name' => 'HeistBoss',
            'level' => 10,
            'energy' => 100,
            'max_energy' => 100,
            'health' => 100,
            'max_health' => 100,
            'cash' => 50000,
            'bank' => 100000,
            'is_onboarded' => true,
        ]);

        $vehicle = Vehicle::first() ?? Vehicle::create([
            'name' => 'Vlucht Scooter',
            'slug' => 'vlucht-scooter',
            'type' => 'scooter',
            'icon' => 'fa-motorcycle',
            'price_cash' => 500,
            'speed_pct' => 50,
            'reliability_pct' => 70,
            'min_level' => 1,
            'is_available' => true,
        ]);

        CharacterVehicle::create([
            'character_id' => $character->id,
            'vehicle_id' => $vehicle->id,
            'current_condition' => 100,
            'tuned_speed' => 50,
            'is_active' => true,
        ]);

        $character->update(['masks' => 10]);

        $response = $this->actingAs($user)->get(route('heists.index'));
        $response->assertStatus(200);

        // Verify data-confirm is present for heists
        $response->assertSee('data-confirm=', false);
        $response->assertSee('data-confirm-title="Solo Heist Starten"', false);

        // Verify no raw inline onclick="return confirm(...) remains in the rendered heists HTML
        $response->assertDontSee('onclick="return confirm', false);

        // Verify the global TBS custom modal markup and functions exist in app layout
        $response->assertSee('window.tbsConfirm', false);
        $response->assertSee('window.tbsAlert', false);
        $response->assertSee('GLOBAL TBS CONFIRMATION & ALERT MODAL', false);
    }

    public function test_gang_panden_page_has_bendepand_kopen_confirm_attributes(): void
    {
        $user = User::factory()->create();
        $character = Character::create([
            'user_id' => $user->id,
            'name' => 'OfficerBoss',
            'level' => 10,
            'energy' => 100,
            'max_energy' => 100,
            'health' => 100,
            'max_health' => 100,
            'cash' => 50000,
            'bank' => 100000,
            'is_onboarded' => true,
        ]);

        $gang = Gang::create([
            'name' => 'TestGangConfirm',
            'tag' => 'TGC',
            'specialty' => 'smuggle',
            'treasury' => 50000000,
            'leader_character_id' => $character->id,
        ]);

        $character->update(['gang_id' => $gang->id]);

        $response = $this->actingAs($user)->get(route('gangs.panden'));
        $response->assertStatus(200);

        // Verify panden buy button has Bendepand Kopen attributes
        $response->assertSee('data-confirm-title="Bendepand Kopen"', false);
        $response->assertSee('data-confirm-btn="Ja, Pand Kopen"', false);
        $response->assertSee('Bendepand Kopen', false);
    }
}
