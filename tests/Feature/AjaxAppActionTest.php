<?php

namespace Tests\Feature;

use App\Models\Character;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AjaxAppActionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_in_app_ajax_action_returns_json_with_fresh_stats_and_flash(): void
    {
        $character = Character::first();
        $character->update(['energy' => 100, 'cash' => 500]);
        $user = $character->user;

        $this->actingAs($user);

        // Submit action with X-TBS-Action: true (e.g. hustle or smuggle)
        $response = $this->withHeaders([
            'X-TBS-Action' => 'true',
            'X-Requested-With' => 'XMLHttpRequest',
            'Referer' => route('hustle.index'),
        ])->post(route('hustle.attempt'), [
            'hustle_id' => 1,
        ]);

        $response->assertOk();
        $response->assertJsonStructure([
            'success',
            'same_page',
            'flash' => [
                'type',
                'message',
            ],
            'character' => [
                'cash',
                'bank',
                'total',
                'energy',
                'max_energy',
                'energy_pct',
                'health',
                'max_health',
                'health_pct',
            ],
        ]);

        $data = $response->json();
        $this->assertTrue($data['same_page']);
        $this->assertNotEmpty($data['flash']['message']);
    }
}
