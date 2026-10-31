<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\Gang;
use App\Models\User;
use Database\Seeders\VehicleAndHeistSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Pass6Bucket7AuditTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(VehicleAndHeistSeeder::class);
    }

    public function test_bucket_7_views_render_and_disable_actions_when_comatose(): void
    {
        $user = User::factory()->create(['is_onboarded' => true]);
        $character = Character::factory()->create([
            'user_id' => $user->id,
            'cash' => 10000000,
            'energy' => 100,
            'max_energy' => 100,
            'health' => 0,
            'coma_until' => now()->addHours(2),
            'bullets' => 500,
            'stars' => 50,
            'level' => 10,
        ]);
        $gang = Gang::factory()->create([
            'leader_character_id' => $character->id,
            'treasury' => 50000000,
        ]);
        $character->update(['gang_id' => $gang->id, 'gang_role' => 'leader']);

        $this->actingAs($user);

        $this->get(route('gangs.index'))->assertOk();

        $gangGarage = $this->get(route('gangs.garage'));
        $gangGarage->assertOk();

        $gangPanden = $this->get(route('gangs.panden'));
        $gangPanden->assertOk();
        $gangPanden->assertSee('disabled', false);

        $garage = $this->get(route('garage.index'));
        $garage->assertOk();
        $garage->assertSee('disabled', false);

        $hacking = $this->get(route('hacking.index'));
        $hacking->assertOk();
        $hacking->assertSee('canHack: false', false);
        $hacking->assertSee('PATIËNT IN COMA (CODE BLAUW)', false);

        $quests = $this->get(route('halloween.quests'));
        $quests->assertOk();

        $shop = $this->get(route('halloween.shop'));
        $shop->assertOk();

        $heists = $this->get(route('heists.index'));
        $heists->assertOk();
        $heists->assertSee('Niet beschikbaar in Code Blauw (Coma)', false);

        $hitman = $this->get(route('hitman.index'));
        $hitman->assertOk();
        $hitman->assertSee('disabled', false);

        $highlow = $this->get(route('highlow.index'));
        $highlow->assertOk();
        $highlow->assertSee('value="lager"', false);
        $highlow->assertSee('disabled', false);

        $hustle = $this->get(route('hustle.index'));
        $hustle->assertOk();
        $hustle->assertSee(':disabled="seconds > 0 || true"', false);
    }

    public function test_bucket_7_views_render_and_disable_actions_when_imprisoned(): void
    {
        $user = User::factory()->create(['is_onboarded' => true]);
        $character = Character::factory()->create([
            'user_id' => $user->id,
            'cash' => 10000000,
            'energy' => 100,
            'max_energy' => 100,
            'health' => 100,
            'prison_until' => now()->addMinutes(30),
            'bullets' => 500,
            'stars' => 50,
            'level' => 10,
        ]);
        $gang = Gang::factory()->create([
            'leader_character_id' => $character->id,
            'treasury' => 50000000,
        ]);
        $character->update(['gang_id' => $gang->id, 'gang_role' => 'leader']);

        $this->actingAs($user);

        $hacking = $this->get(route('hacking.index'));
        $hacking->assertOk();
        $hacking->assertSee('canHack: false', false);
        $hacking->assertSee('OPGESLOTEN IN DE ISOLEERCEL', false);

        $heists = $this->get(route('heists.index'));
        $heists->assertOk();
        $heists->assertSee('Niet beschikbaar in de isoleercel', false);

        $hustle = $this->get(route('hustle.index'));
        $hustle->assertOk();
        $hustle->assertSee(':disabled="seconds > 0 || true"', false);
    }

    public function test_bucket_7_views_render_active_when_healthy(): void
    {
        $user = User::factory()->create(['is_onboarded' => true]);
        $character = Character::factory()->create([
            'user_id' => $user->id,
            'cash' => 10000000,
            'energy' => 100,
            'max_energy' => 100,
            'health' => 100,
            'bullets' => 500,
            'stars' => 50,
            'level' => 10,
        ]);
        $gang = Gang::factory()->create([
            'leader_character_id' => $character->id,
            'treasury' => 50000000,
        ]);
        $character->update(['gang_id' => $gang->id, 'gang_role' => 'leader']);

        $this->actingAs($user);

        $hacking = $this->get(route('hacking.index'));
        $hacking->assertOk();
        $hacking->assertSee('canHack: true', false);

        $hustle = $this->get(route('hustle.index'));
        $hustle->assertOk();
        $hustle->assertSee(':disabled="seconds > 0 || false"', false);
    }
}
