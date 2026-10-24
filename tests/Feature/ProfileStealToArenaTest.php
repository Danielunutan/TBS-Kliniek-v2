<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileStealToArenaTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_steal_button_points_to_fight_attack(): void
    {
        $attackerUser = User::factory()->create();
        $attacker = Character::create([
            'user_id' => $attackerUser->id,
            'name' => 'Attacker',
            'level' => 5,
            'energy' => 100,
            'max_energy' => 100,
            'health' => 100,
            'max_health' => 100,
            'cash' => 5000,
            'is_onboarded' => true,
        ]);

        $victimUser = User::factory()->create();
        $victim = Character::create([
            'user_id' => $victimUser->id,
            'name' => 'TargetVictim',
            'level' => 5,
            'energy' => 100,
            'max_energy' => 100,
            'health' => 100,
            'max_health' => 100,
            'cash' => 25000,
            'is_onboarded' => true,
        ]);

        $this->actingAs($attackerUser);

        $response = $this->get(route('profile.show', $victim->name));

        $response->assertOk();
        $response->assertSee(route('fight.attack'));
        $response->assertSee('Beroof Patiënt');
        $response->assertSee('opponent_id');
        $response->assertSee((string) $victim->id);
    }

    public function test_stealing_from_profile_redirects_to_respect_arena_loading_screen(): void
    {
        $attackerUser = User::factory()->create();
        $attacker = Character::create([
            'user_id' => $attackerUser->id,
            'name' => 'Attacker2',
            'level' => 10,
            'energy' => 100,
            'max_energy' => 100,
            'health' => 100,
            'max_health' => 100,
            'cash' => 1000,
            'strength' => 50,
            'defense' => 50,
            'is_onboarded' => true,
        ]);

        $victimUser = User::factory()->create();
        $victim = Character::create([
            'user_id' => $victimUser->id,
            'name' => 'TargetVictim2',
            'level' => 5,
            'energy' => 100,
            'max_energy' => 100,
            'health' => 100,
            'max_health' => 100,
            'cash' => 50000,
            'strength' => 5,
            'defense' => 5,
            'is_onboarded' => true,
        ]);

        $this->actingAs($attackerUser);

        // Submit attack from profile
        $response = $this->post(route('fight.attack'), [
            'opponent_id' => $victim->id,
            'from_profile' => 1,
        ]);

        $response->assertRedirect(route('fight.arena', $victim));
        $response->assertSessionHas('arena_battle');

        // Follow redirect to the arena loading / suspense screen
        $arenaResponse = $this->get(route('fight.arena', $victim));
        $arenaResponse->assertOk();
        $arenaResponse->assertSee('PATIËNT OVERMEESTEREN');
        $arenaResponse->assertSee('Respectlijst');
        $arenaResponse->assertSee('Profiel van '.$victim->name);
    }
}
