<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\CharacterTrueCrimeState;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class TrueCrimeCooldownAdTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.robinads.enabled' => true]);
    }

    public function test_true_crime_cooldown_ad_claim_blocked_when_ads_disabled(): void
    {
        config(['services.robinads.enabled' => false]);
        $user = User::factory()->create();
        Character::create([
            'user_id' => $user->id,
            'name' => 'DetectiveBlocked',
            'level' => 1,
            'energy' => 100,
            'max_energy' => 100,
            'health' => 100,
            'max_health' => 100,
            'cash' => 1000,
            'stars' => 0,
            'respect' => 0,
            'is_onboarded' => true,
        ]);

        $this->actingAs($user)->post(route('true_crime.skip_cooldown_ad'))
            ->assertRedirect(route('true_crime.index'))
            ->assertSessionHas('error');
    }

    public function test_user_requires_5_ads_to_bypass_true_crime_cooldown(): void
    {
        $user = User::factory()->create();
        $character = Character::create([
            'user_id' => $user->id,
            'name' => 'DetectivePlayer',
            'level' => 1,
            'energy' => 100,
            'max_energy' => 100,
            'health' => 100,
            'max_health' => 100,
            'cash' => 1000,
            'stars' => 0,
            'respect' => 0,
            'is_onboarded' => true,
            'ads_watched_today' => 0,
            'ads_watched_total' => 0,
        ]);

        $state = CharacterTrueCrimeState::create([
            'character_id' => $character->id,
            'cooldown_until' => now()->addHours(24),
            'cooldown_ads_watched' => 0,
        ]);

        $this->actingAs($user);

        // Watch 4 ads through sponsor claim
        for ($i = 1; $i <= 4; $i++) {
            $token = "test_token_{$i}";
            Cache::put("tbs_ad_token_{$token}", [
                'token' => $token,
                'started_at' => now()->subSeconds(20)->timestamp,
                'character_id' => $character->id,
                'reward_type' => 'true_crime',
                'robin_reward_id' => null,
            ], now()->addMinutes(10));

            $response = $this->postJson(route('sponsor.claim'), [
                'token' => $token,
            ]);

            $response->assertOk();
            $state->refresh();
            $this->assertEquals($i, $state->cooldown_ads_watched);
            $this->assertTrue($state->isOnCooldown());
        }

        // 5th ad should clear cooldown
        $token5 = 'test_token_5';
        Cache::put("tbs_ad_token_{$token5}", [
            'token' => $token5,
            'started_at' => now()->subSeconds(20)->timestamp,
            'character_id' => $character->id,
            'reward_type' => 'true_crime',
            'robin_reward_id' => null,
        ], now()->addMinutes(10));

        $response = $this->postJson(route('sponsor.claim'), [
            'token' => $token5,
        ]);

        $response->assertOk();
        $state->refresh();
        $this->assertEquals(0, $state->cooldown_ads_watched);
        $this->assertNull($state->cooldown_until);
        $this->assertFalse($state->isOnCooldown());
    }

    public function test_cooldown_screen_displays_star_skip_option_and_skips_successfully(): void
    {
        $user = User::factory()->create();
        $character = Character::create([
            'user_id' => $user->id,
            'name' => 'DetectivePlayer2',
            'level' => 1,
            'energy' => 100,
            'max_energy' => 100,
            'health' => 100,
            'max_health' => 100,
            'cash' => 1000,
            'stars' => 20,
            'respect' => 0,
            'is_onboarded' => true,
        ]);

        $state = CharacterTrueCrimeState::create([
            'character_id' => $character->id,
            'cooldown_until' => now()->addHours(24),
            'cooldown_ads_watched' => 0,
        ]);

        $this->actingAs($user);

        $response = $this->get(route('true_crime.index'));

        $response->assertOk();
        $response->assertSee('Wachttijd Direct Overslaan (15 Sterren ⭐)');

        $skipResponse = $this->post(route('true_crime.skip_cooldown_stars'));
        $skipResponse->assertRedirect(route('true_crime.index'));

        $character->refresh();
        $state->refresh();
        $this->assertEquals(5, $character->stars);
        $this->assertFalse($state->isOnCooldown());
    }
}
