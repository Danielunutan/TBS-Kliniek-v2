<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardWidgetAndWeatherTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    protected function createCharacter(User $user, array $attrs = []): Character
    {
        return Character::create(array_merge([
            'user_id' => $user->id,
            'name' => 'Boef_'.rand(1000, 9999),
            'level' => 1,
            'xp' => 0,
            'health' => 100,
            'max_health' => 100,
            'energy' => 100,
            'max_energy' => 100,
            'cash' => 500,
            'bank' => 1000,
            'stars' => 10,
            'respect' => 10,
        ], $attrs));
    }

    public function test_dashboard_loads_with_weather_news_and_widgets(): void
    {
        $user = User::factory()->create();
        $this->createCharacter($user, [
            'stars' => 50,
            'unlocked_widgets' => ['weather'],
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Kliniek Weerstation');
        $response->assertSee('Algemeen Buitenwereld Nieuws');
        $response->assertSee('Peuken Beurs');
        $response->assertSee('Bewakingsradar');
    }

    public function test_player_can_unlock_premium_widget_with_stars(): void
    {
        $user = User::factory()->create();
        $character = $this->createCharacter($user, [
            'stars' => 25,
            'unlocked_widgets' => [],
        ]);

        // 1. Try to unlock with invalid key
        $failResponse = $this->actingAs($user)->postJson(route('dashboard.widgets.unlock'), [
            'widget_key' => 'non_existent_widget',
        ]);
        $failResponse->assertStatus(422);

        // 2. Unlock radio widget (cost: 20 stars)
        $successResponse = $this->actingAs($user)->postJson(route('dashboard.widgets.unlock'), [
            'widget_key' => 'radio',
        ]);

        $successResponse->assertStatus(200);
        $successResponse->assertJson([
            'success' => true,
            'stars' => 5,
            'widget_key' => 'radio',
        ]);

        $character->refresh();
        $this->assertEquals(5, $character->stars);
        $this->assertTrue($character->isWidgetUnlocked('radio'));

        // 3. Try to unlock again with insufficient stars (5 remaining, requires 15 for weather)
        $insufficientResponse = $this->actingAs($user)->postJson(route('dashboard.widgets.unlock'), [
            'widget_key' => 'weather',
        ]);
        $insufficientResponse->assertStatus(400);
        $this->assertFalse($character->isWidgetUnlocked('weather'));
    }

    public function test_weather_by_coordinates_endpoint_returns_data(): void
    {
        $user = User::factory()->create();
        $this->createCharacter($user);

        $response = $this->actingAs($user)->getJson(route('dashboard.weather.coords', [
            'lat' => 52.37,
            'lon' => 4.89,
        ]));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'weather' => [
                'location',
                'temp',
                'feels_like',
                'condition',
                'icon',
                'wind',
                'humidity',
                'rain_chance',
                'rain_mm',
                'has_buien',
                'buien_text',
                'yard_status',
                'yard_open',
            ],
        ]);
    }
}
