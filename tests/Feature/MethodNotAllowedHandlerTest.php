<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MethodNotAllowedHandlerTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_on_post_route_redirects_gracefully_instead_of_405(): void
    {
        $user = User::factory()->create();
        $character = Character::create([
            'user_id' => $user->id,
            'name' => 'Tester',
            'health' => 100,
            'max_health' => 100,
            'energy' => 100,
            'max_energy' => 100,
            'cash' => 5000,
            'bank' => 0,
            'level' => 5,
            'experience' => 0,
            'onboarding_completed' => true,
        ]);

        $response = $this->actingAs($user)->get('/gangs/winkel/kopen');

        $response->assertRedirect(route('gangs.index', ['tab' => 'shop']));
    }

    public function test_get_on_generic_post_route_triggers_graceful_redirect(): void
    {
        $user = User::factory()->create();
        $character = Character::create([
            'user_id' => $user->id,
            'name' => 'Tester2',
            'health' => 100,
            'max_health' => 100,
            'energy' => 100,
            'max_energy' => 100,
            'cash' => 5000,
            'bank' => 0,
            'level' => 5,
            'experience' => 0,
            'onboarding_completed' => true,
        ]);

        // Route that only accepts POST and has no explicit GET fallback
        $response = $this->actingAs($user)->get('/gangs/aanmaken');

        $response->assertRedirect(route('gangs.index'));
        $response->assertSessionHas('info');
    }
}
