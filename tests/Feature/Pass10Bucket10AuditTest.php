<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\CharacterVehicle;
use App\Models\Gang;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Pass10Bucket10AuditTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Character $character;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['is_onboarded' => true]);
        $this->character = Character::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'AuditPatient10',
            'cash' => 250000,
            'bank' => 500000,
            'stars' => 100,
            'energy' => 100,
            'health' => 100,
            'level' => 10,
            'respect' => 1500,
        ]);
    }

    public function test_gangs_panden_escapes_single_quotes_in_onclick_confirm(): void
    {
        $gang = Gang::factory()->create([
            'name' => 'Syndicaat 10',
            'tag' => 'S10',
            'specialty' => 'Afpersing',
            'leader_character_id' => $this->character->id,
            'treasury' => 5000000,
        ]);

        $this->character->gang_id = $gang->id;
        $this->character->gang_role = 'leader';
        $this->character->save();

        $response = $this->actingAs($this->user)->get(route('gangs.panden'));
        $response->assertOk();

        $content = $response->getContent();

        // Ensure no raw single quote inside onclick="return confirm('...')" breaks JS syntax for Gentleman's Club
        $this->assertStringNotContainsString("confirm('Koop VIP Gentleman's Club", $content);
        $this->assertStringNotContainsString("confirm('Weet je zeker dat je VIP Gentleman's Club", $content);

        // Ensure safe JS escaping is used
        $this->assertStringContainsString('Gentleman\u0027s Club', $content);
    }

    public function test_gangs_view_renders_cleanly_for_player_without_gang_and_has_safe_tab_fallback(): void
    {
        $this->assertNull($this->character->gang_id);
        $this->character->stars = 50; // Less than 500 stars
        $this->character->save();

        $response = $this->actingAs($this->user)->get(route('gangs.index'));
        $response->assertOk();

        $content = $response->getContent();

        // Ensure Alpine fallback redirects invalid gang tabs for players without a gang
        $this->assertStringContainsString("!['alle_bendes', 'oprichten', 'ranglijst'].includes(this.activeGangTab)", $content);

        // Ensure shop tab nullsafe checks work without throwing null pointer warnings/errors
        $this->assertStringContainsString('Bende Winkel', $content);
        $this->assertStringContainsString('Koop Bende Chat (500 ⭐)', $content);
    }

    public function test_gangs_view_disables_actions_when_character_is_comatose_or_imprisoned(): void
    {
        $gang = Gang::factory()->create([
            'name' => 'Syndicaat Isolatie',
            'tag' => 'ISO',
            'specialty' => 'Afpersing',
            'leader_character_id' => $this->character->id,
            'treasury' => 1000000,
        ]);

        $this->character->gang_id = $gang->id;
        $this->character->gang_role = 'leader';
        $this->character->is_in_prison = true;
        $this->character->prison_until = now()->addHours(2); // Imprisoned
        $this->character->save();

        $this->assertTrue($this->character->isImprisoned());

        $response = $this->actingAs($this->user)->get(route('gangs.index'));
        $response->assertOk();

        $content = $response->getContent();

        // Donate buttons should be disabled for imprisoned characters
        $this->assertStringContainsString(':disabled="true"', $content);
        $this->assertStringContainsString('disabled', $content);

        // Test comatose state
        $this->character->is_in_prison = false;
        $this->character->prison_until = null;
        $this->character->coma_until = now()->addHours(2); // Comatose
        $this->character->save();

        $this->assertTrue($this->character->isComatose());

        $responseComa = $this->actingAs($this->user)->get(route('gangs.index'));
        $responseComa->assertOk();
    }

    public function test_gangs_garage_view_renders_with_null_vehicle_safely(): void
    {
        $gang = Gang::factory()->create([
            'name' => 'Syndicaat Garage',
            'tag' => 'GRG',
            'specialty' => 'Afpersing',
            'leader_character_id' => $this->character->id,
            'treasury' => 2000000,
        ]);

        $this->character->gang_id = $gang->id;
        $this->character->gang_role = 'leader';
        $this->character->save();

        $realVehicle = Vehicle::create([
            'name' => 'Getaway Wagen',
            'slug' => 'getaway-wagen',
            'type' => 'sedan',
            'brand' => 'Audi',
            'description' => 'Bende vluchtauto',
            'speed_pct' => 80,
            'reliability_pct' => 85,
            'price_cash' => 40000,
            'price_stars' => 0,
            'is_available' => true,
            'icon' => 'fa-car',
        ]);

        $charVeh = CharacterVehicle::forceCreate([
            'character_id' => $this->character->id,
            'vehicle_id' => $realVehicle->id,
            'current_condition' => 80,
            'in_gang_garage' => false,
            'is_active' => false,
            'is_for_sale' => false,
        ]);

        $response = $this->actingAs($this->user)->get(route('gangs.garage'));
        $response->assertOk();

        // Now test direct view rendering with a vehicle having null vehicle relation
        $charVeh->setRelation('vehicle', null);

        $view = view('game.gangs.garage', [
            'character' => $this->character,
            'gang' => $gang,
            'totalVehicleSlots' => 5,
            'storedVehicles' => collect([$charVeh]),
            'freeSlots' => 4,
            'personalVehicles' => collect([$charVeh]),
            'garageProperties' => collect(),
            'isOfficer' => true,
        ])->render();

        $this->assertStringContainsString('Voertuig', $view);
    }

    public function test_garage_index_view_renders_safely_and_clears_timer_interval(): void
    {
        // Create an active vehicle with a valid vehicle model
        $realVehicle = Vehicle::create([
            'name' => 'Audi RS6 Avant',
            'slug' => 'audi-rs6-avant',
            'type' => 'sedan',
            'brand' => 'Audi',
            'description' => 'Supersnelle vluchtauto',
            'speed_pct' => 85,
            'reliability_pct' => 90,
            'price_cash' => 50000,
            'price_stars' => 0,
            'is_available' => true,
            'icon' => 'fa-car-side',
        ]);

        $charVeh = CharacterVehicle::forceCreate([
            'character_id' => $this->character->id,
            'vehicle_id' => $realVehicle->id,
            'current_condition' => 95,
            'in_gang_garage' => false,
            'is_active' => true,
            'is_for_sale' => false,
        ]);

        $response = $this->actingAs($this->user)->get(route('garage.index'));
        $response->assertOk();

        $content = $response->getContent();

        // Verify timer clears interval on 0 before reloading
        $this->assertStringContainsString('clearInterval(timer);', $content);
        $this->assertStringContainsString('window.location.reload();', $content);

        // Test direct view render with null vehicle relation to verify robustness
        $charVeh->setRelation('vehicle', null);

        $view = view('game.garage.index', [
            'character' => $this->character,
            'ownedVehicles' => collect([$charVeh]),
            'gangStoredVehicles' => collect(),
            'activeVehicle' => $charVeh,
            'rotatingListings' => [],
            'secondsUntilRotation' => 1800,
            'topRankings' => collect(),
            'starVehicles' => collect(),
        ])->render();

        $this->assertStringContainsString('Voertuig', $view);
        $this->assertStringContainsString('clearInterval(timer);', $view);
    }
}
