<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class DashboardWidgetAndWeatherTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Fake the Open-Meteo geocoding and forecast endpoints so the tests do not depend on the network.
     * Certificate checks stay on, so the tests need no live TLS connection.
     */
    private function fakeOpenMeteoResponses(string $cityName, float $latitude, float $longitude): void
    {
        Http::fake([
            'geocoding-api.open-meteo.com/*' => Http::response([
                'results' => [
                    ['name' => $cityName, 'latitude' => $latitude, 'longitude' => $longitude],
                ],
            ]),
            'api.open-meteo.com/*' => Http::response([
                'current' => [
                    'temperature_2m' => 12.3,
                    'relative_humidity_2m' => 71,
                    'apparent_temperature' => 10.9,
                    'precipitation' => 0.0,
                    'rain' => 0.0,
                    'showers' => 0.0,
                    'weather_code' => 3,
                    'wind_speed_10m' => 14.2,
                ],
                'hourly' => [
                    'time' => [],
                    'precipitation_probability' => [],
                    'precipitation' => [],
                ],
            ]),
        ]);
    }

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
        $response->assertSee('MIXXR.nl Entertainment Nieuws');
        $response->assertSee('MIXXR Radio Deck');
        $response->assertSee('Geheime Ontsnappingstunnel');
        $response->assertSee('Dossier Dr. H. van Beek');
        $response->assertSee('Koffers');
        $response->assertSee('Kluiscodes');
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

        // 2. Unlock tunnel widget (cost: 20 stars)
        $successResponse = $this->actingAs($user)->postJson(route('dashboard.widgets.unlock'), [
            'widget_key' => 'tunnel',
        ]);

        $successResponse->assertStatus(200);
        $successResponse->assertJson([
            'success' => true,
            'stars' => 5,
            'widget_key' => 'tunnel',
        ]);

        $character->refresh();
        $this->assertEquals(5, $character->stars);
        $this->assertTrue($character->isWidgetUnlocked('tunnel'));

        // 3. Try to unlock again with insufficient stars (5 remaining, requires 15 for weather)
        $insufficientResponse = $this->actingAs($user)->postJson(route('dashboard.widgets.unlock'), [
            'widget_key' => 'weather',
        ]);
        $insufficientResponse->assertStatus(400);
        $this->assertFalse($character->isWidgetUnlocked('weather'));

        // 4. Test standard HTML form POST fallback redirect
        $formPostResponse = $this->actingAs($user)->from(route('dashboard'))->post(route('dashboard.widgets.unlock'), [
            'widget_key' => 'weather',
        ]);
        $formPostResponse->assertRedirect(route('dashboard'));
        $formPostResponse->assertSessionHas('error');
    }

    public function test_legacy_unlocked_widgets_map_to_new_system_widgets(): void
    {
        $user = User::factory()->create();
        $character = $this->createCharacter($user, [
            'unlocked_widgets' => ['peuken_market', 'security', 'roulette'],
        ]);

        $this->assertTrue($character->isWidgetUnlocked('tunnel'));
        $this->assertTrue($character->isWidgetUnlocked('treatment'));
        $this->assertTrue($character->isWidgetUnlocked('packages'));

        // nunl and mixxr_news are bidirectional aliases
        $character->unlocked_widgets = ['nunl'];
        $this->assertTrue($character->isWidgetUnlocked('nunl'));
        $this->assertTrue($character->isWidgetUnlocked('mixxr_news'));

        $character->unlocked_widgets = ['mixxr_news'];
        $this->assertTrue($character->isWidgetUnlocked('nunl'));
        $this->assertTrue($character->isWidgetUnlocked('mixxr_news'));

        // Radio widget unlock
        $character->unlocked_widgets = ['radio'];
        $this->assertTrue($character->isWidgetUnlocked('radio'));
    }

    public function test_weather_location_persists_to_character_database(): void
    {
        $user = User::factory()->create();
        $character = $this->createCharacter($user);
        $this->fakeOpenMeteoResponses('Eindhoven', 51.44, 5.48);

        $response = $this->actingAs($user)->getJson(route('dashboard.weather.coords', [
            'city' => 'Eindhoven',
        ]));

        $response->assertStatus(200);
        $character->refresh();
        $this->assertEquals('Eindhoven', $character->weather_location);
        $this->assertNotNull($character->weather_lat);
        $this->assertNotNull($character->weather_lon);

        // Reset clears DB fields
        $resetResponse = $this->actingAs($user)->getJson(route('dashboard.weather.coords', [
            'reset' => 1,
        ]));
        $resetResponse->assertStatus(200);
        $character->refresh();
        $this->assertNull($character->weather_location);
        $this->assertNull($character->weather_lat);
        $this->assertNull($character->weather_lon);
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

    public function test_weather_by_city_endpoint_returns_data(): void
    {
        $user = User::factory()->create();
        $this->createCharacter($user);
        $this->fakeOpenMeteoResponses('Rotterdam', 51.92, 4.48);

        $response = $this->actingAs($user)->getJson(route('dashboard.weather.coords', [
            'city' => 'Rotterdam',
        ]));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
        $response->assertJsonPath('weather.location', 'Rotterdam');
    }

    public function test_dashboard_renders_code_blauw_banner_without_reload_loop(): void
    {
        $user = User::factory()->create();
        $character = $this->createCharacter($user, [
            'perks' => ['liquidation_streak_deaths' => 10],
            'coma_until' => null,
        ]);

        $this->assertTrue($character->needsReviveSerum());
        $this->assertSame(0, $character->getComaRemainingSeconds());

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Code Blauw • 10/10 Geliquideerd');
        $response->assertSee(Character::REVIVE_SERUM_NAME.' Vereist!');
        $response->assertDontSee('getComaRemainingSeconds');
        // Ensure Code Blauw banner has no auto-reload timer x-data
        $content = $response->getContent();
        $this->assertStringNotContainsString('sec: 1', $content);
        $this->assertStringNotContainsString('if (this.sec <= 0) { window.location.reload(); }', $content);
    }

    public function test_dashboard_does_not_render_prison_banner_when_seconds_zero(): void
    {
        $user = User::factory()->create();
        $character = $this->createCharacter($user, [
            'is_in_prison' => true,
            'prison_until' => now()->subMinute(),
        ]);

        $this->assertSame(0, $character->remainingPrisonSeconds());

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertDontSee('Vast in de isoleercel');
    }
}
