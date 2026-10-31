<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Character;
use Illuminate\Foundation\Testing\RefreshDatabase;

class Pass6Bucket6AuditTest extends TestCase
{
    use RefreshDatabase;

    public function test_bucket_6_cinema_index_loads_successfully(): void
    {
        $user = User::factory()->create();
        $character = Character::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get(route('cinema.index'));
        $response->assertStatus(200);
        $response->assertSee('Kliniek Cinema & Watchparties');
    }
}
