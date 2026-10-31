<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Pass6Bucket6AuditTest extends TestCase
{
    use RefreshDatabase;

    public function test_connect_four_view_includes_skip_cooldown_with_stars_action(): void
    {
        $user = User::factory()->create(['is_onboarded' => true]);
        Character::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get(route('connect_four.index'));
        $response->assertOk();
        $response->assertSee(route('connect_four.skip_cooldown_stars'), false);
    }

    public function test_cinema_index_loads_successfully(): void
    {
        $user = User::factory()->create(['is_onboarded' => true]);
        Character::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get(route('cinema.index'));
        $response->assertOk();
        $response->assertSee('Kliniek Cinema & Watchparties');
    }
}
