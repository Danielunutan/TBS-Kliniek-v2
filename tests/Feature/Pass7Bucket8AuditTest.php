<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Pass7Bucket8AuditTest extends TestCase
{
    use RefreshDatabase;

    public function test_infirmary_index_renders_correctly(): void
    {
        $user = User::factory()->create();
        $character = Character::factory()->create(['user_id' => $user->id, 'health' => 50, 'max_health' => 100]);

        $response = $this->actingAs($user)->get(route('infirmary.index'));

        $response->assertStatus(200);
        $response->assertSee('Ziekenboeg');
    }

    public function test_settings_account_index_renders_correctly(): void
    {
        $user = User::factory()->create();
        $character = Character::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get(route('settings.account.index'));

        $response->assertStatus(200);
        $response->assertSee('Account & Beveiliging');
    }
}
