<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Character;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Pass6Bucket10AuditTest extends TestCase
{
    use RefreshDatabase;

    public function test_bucket10_views_and_legal_pages_render_correctly(): void
    {
        $response = $this->get(route('legal.terms'));
        $response->assertStatus(200);

        $response = $this->get(route('legal.privacy'));
        $response->assertStatus(200);

        $response = $this->get(route('legal.cookies'));
        $response->assertStatus(200);

        $response = $this->get(route('legal.disclaimer'));
        $response->assertStatus(200);
    }

    public function test_maintenance_page_renders_with_bypass(): void
    {
        $response = $this->get('/?bypass=daniel');
        $response->assertStatus(200);
    }

    public function test_authenticated_user_can_access_dashboard_and_realtime_stats(): void
    {
        $user = User::factory()->create([
            'is_onboarded' => true,
        ]);
        $character = new Character([
            'user_id' => $user->id,
            'name' => 'Test Patient',
            'username' => 'testpatient',
            'cash' => 50000,
            'bank' => 100000,
            'energy' => 100,
            'max_energy' => 100,
            'health' => 100,
            'max_health' => 100,
            'respect' => 50,
            'level' => 1,
            'profile_color' => 'purple',
        ]);
        $character->save();

        $response = $this->actingAs($user)->get(route('dashboard'));
        $response->assertStatus(200);

        $responseStats = $this->actingAs($user)->getJson(route('character.realtime-stats'));
        $responseStats->assertStatus(200);
        $responseStats->assertJsonStructure(['cash', 'bank', 'energy', 'health']);
    }
}
