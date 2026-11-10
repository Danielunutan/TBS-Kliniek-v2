<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ComplexWorldClientTest extends TestCase
{
    use RefreshDatabase;

    public function test_complex_virtual_world_page_renders_successfully(): void
    {
        $user = User::factory()->create();
        $character = Character::factory()->create([
            'user_id' => $user->id,
            'name' => 'BajesKoning',
            'level' => 5,
        ]);

        $response = $this->actingAs($user)->get(route('complex.index'));

        $response->assertOk();
        $response->assertSee('TBS Complex');
        $response->assertSee('2D Virtuele Wereld');
        $response->assertSee('BajesKoning');
        $response->assertSee('Navigator');
        $response->assertSee('Meubels');
        $response->assertSee('gameCanvas');
    }
}

