<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\CharacterCooldown;
use App\Models\User;
use App\Models\VaultCode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SponsorAdAndLegalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.robinads.enabled' => true]);
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
            'cash' => 1000,
            'bank' => 5000,
            'stars' => 5,
            'masks' => 2,
            'vault_tokens' => 1,
            'respect' => 10,
            'ads_watched_today' => 0,
            'ads_watched_total' => 0,
            'last_active_at' => now(),
        ], $attrs));
    }

    public function test_public_legal_pages_are_accessible_for_guests(): void
    {
        $this->get(route('legal.terms'))->assertStatus(200)->assertSee('Algemene Voorwaarden');
        $this->get(route('legal.privacy'))->assertStatus(200)->assertSee('Privacybeleid');
        $this->get(route('legal.cookies'))->assertStatus(200)->assertSee('Cookiebeleid');
        $this->get(route('legal.disclaimer'))->assertStatus(200)->assertSee('Disclaimer & Colofon');
    }

    public function test_legal_pages_state_hobby_project_no_kvk_and_voluntary_donations(): void
    {
        $routes = [
            route('legal.terms'),
            route('legal.privacy'),
            route('legal.cookies'),
            route('legal.disclaimer'),
        ];

        foreach ($routes as $url) {
            $res = $this->get($url);
            $res->assertStatus(200);
            $res->assertSee('KvK');
            $res->assertSee('donatie', false);
            $res->assertSee('hobby', false);
        }
    }

    public function test_sponsorkamer_index_shows_for_authenticated_character(): void
    {
        $user = User::factory()->create();
        $character = $this->createCharacter($user);

        $response = $this->actingAs($user)->get(route('sponsor.index'));

        $response->assertStatus(200);
        $response->assertSee('Sponsorkamer');
        $response->assertSee('24 Uur Gratis VIP Voortgang');
    }

    public function test_start_ad_session_returns_token_and_smartlink(): void
    {
        $user = User::factory()->create();
        $character = $this->createCharacter($user);

        $response = $this->actingAs($user)->postJson(route('sponsor.start'), [
            'reward_type' => 'cash',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'duration' => 15,
        ]);
        $response->assertJsonStructure(['token', 'smartlink_url']);
    }

    public function test_sponsor_redirect_safely_redirects_to_sponsor_index(): void
    {
        $user = User::factory()->create();
        $this->createCharacter($user);

        $response = $this->actingAs($user)->get(route('sponsor.redirect'));

        $response->assertRedirect(route('sponsor.index'));
    }

    public function test_claim_ad_fails_if_minimum_watch_duration_not_met(): void
    {
        $user = User::factory()->create();
        $character = $this->createCharacter($user);

        // Start session
        $this->actingAs($user)->postJson(route('sponsor.start'), ['reward_type' => 'cash']);
        $session = session('tbs_ad_session');

        // Immediately attempt claim (< 14 seconds)
        $response = $this->actingAs($user)->postJson(route('sponsor.claim'), [
            'token' => $session['token'],
        ]);

        $response->assertStatus(422);
        $response->assertJson(['success' => false]);
    }

    public function test_claim_ad_grants_cash_reward_after_watch_duration(): void
    {
        $user = User::factory()->create();
        $character = $this->createCharacter($user, ['cash' => 1000]);

        // Mock started 15 seconds ago
        $token = 'test_token_12345';
        $this->actingAs($user)->withSession([
            'tbs_ad_session' => [
                'token' => $token,
                'started_at' => now()->subSeconds(16)->timestamp,
                'character_id' => $character->id,
                'reward_type' => 'cash',
            ],
        ]);

        $response = $this->postJson(route('sponsor.claim'), [
            'token' => $token,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $character->refresh();
        $this->assertEquals(3500, $character->cash); // 1000 + 2500
        $this->assertEquals(1, $character->ads_watched_today);
        $this->assertEquals(1, $character->ads_watched_total);
    }

    public function test_claim_ad_reduces_prison_sentence(): void
    {
        $user = User::factory()->create();
        $character = $this->createCharacter($user, [
            'is_in_prison' => true,
            'prison_until' => now()->addMinutes(30),
            'prison_reason' => 'Betrapt bij diefstal',
        ]);

        $token = 'prison_token_abc';
        $this->actingAs($user)->withSession([
            'tbs_ad_session' => [
                'token' => $token,
                'started_at' => now()->subSeconds(16)->timestamp,
                'character_id' => $character->id,
                'reward_type' => 'prison',
            ],
        ]);

        $response = $this->postJson(route('sponsor.claim'), [
            'token' => $token,
        ]);

        $response->assertStatus(200);
        $character->refresh();

        // 30-minute sentence requires 5 ads; 1 ad reduces sentence by 3 minutes and records progress = 1
        $this->assertTrue((bool) $character->is_in_prison);
        $this->assertEquals(1, $character->ad_prison_progress);
    }

    public function test_claim_ad_frees_prisoner_if_under_15_minutes(): void
    {
        $user = User::factory()->create();
        $character = $this->createCharacter($user, [
            'is_in_prison' => true,
            'prison_until' => now()->addSeconds(60),
            'prison_reason' => 'Korte isoleercel straf',
        ]);

        $token = 'prison_free_token';
        $this->actingAs($user)->withSession([
            'tbs_ad_session' => [
                'token' => $token,
                'started_at' => now()->subSeconds(16)->timestamp,
                'character_id' => $character->id,
                'reward_type' => 'prison',
            ],
        ]);

        $response = $this->postJson(route('sponsor.claim'), [
            'token' => $token,
        ]);

        $response->assertStatus(200);
        $character->refresh();

        $this->assertFalse((bool) $character->is_in_prison);
        $this->assertNull($character->prison_until);
    }

    public function test_claim_ad_clears_all_active_cooldowns(): void
    {
        $user = User::factory()->create();
        $character = $this->createCharacter($user);

        // Put character on cooldown for hustle and pickpocket
        CharacterCooldown::create([
            'character_id' => $character->id,
            'action' => 'hustle',
            'cooldown_until' => now()->addMinutes(3),
        ]);
        CharacterCooldown::create([
            'character_id' => $character->id,
            'action' => 'pickpocket',
            'cooldown_until' => now()->addMinutes(2),
        ]);

        $this->assertGreaterThan(0, $character->cooldownRemaining('hustle'));

        $token = 'cooldown_clear_token';
        $this->actingAs($user)->withSession([
            'tbs_ad_session' => [
                'token' => $token,
                'started_at' => now()->subSeconds(16)->timestamp,
                'character_id' => $character->id,
                'reward_type' => 'cooldown',
            ],
        ]);

        $response = $this->postJson(route('sponsor.claim'), [
            'token' => $token,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertEquals(0, $character->cooldownRemaining('hustle'));
        $this->assertEquals(0, $character->cooldownRemaining('pickpocket'));
    }

    public function test_claim_ad_cooldown_for_specific_produce_action_only_clears_that_action(): void
    {
        $user = User::factory()->create();
        $character = $this->createCharacter($user);

        CharacterCooldown::create([
            'character_id' => $character->id,
            'action' => 'produce_sigaretten',
            'cooldown_until' => now()->addMinutes(5),
        ]);
        CharacterCooldown::create([
            'character_id' => $character->id,
            'action' => 'produce_ritalin',
            'cooldown_until' => now()->addMinutes(15),
        ]);
        CharacterCooldown::create([
            'character_id' => $character->id,
            'action' => 'produce_telefoon',
            'cooldown_until' => now()->addMinutes(30),
        ]);

        $this->assertGreaterThan(0, $character->cooldownRemaining('produce_sigaretten'));
        $this->assertGreaterThan(0, $character->cooldownRemaining('produce_ritalin'));
        $this->assertGreaterThan(0, $character->cooldownRemaining('produce_telefoon'));

        $token = 'sig_produce_cooldown_token';
        $this->actingAs($user)->withSession([
            'tbs_ad_session' => [
                'token' => $token,
                'started_at' => now()->subSeconds(16)->timestamp,
                'character_id' => $character->id,
                'reward_type' => 'cooldown:produce_sigaretten',
            ],
        ]);

        $response = $this->postJson(route('sponsor.claim'), [
            'token' => $token,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        // Only sigaretten should be cleared!
        $this->assertEquals(0, $character->cooldownRemaining('produce_sigaretten'));
        $this->assertGreaterThan(0, $character->cooldownRemaining('produce_ritalin'));
        $this->assertGreaterThan(0, $character->cooldownRemaining('produce_telefoon'));
    }

    public function test_watching_250_ads_unlocks_24h_vip(): void
    {
        $user = User::factory()->create();
        $character = $this->createCharacter($user, [
            'ads_watched_total' => 249,
            'is_vip' => false,
            'vip_expires_at' => null,
        ]);

        $token = 'vip_milestone_token';
        $this->actingAs($user)->withSession([
            'tbs_ad_session' => [
                'token' => $token,
                'started_at' => now()->subSeconds(16)->timestamp,
                'character_id' => $character->id,
                'reward_type' => 'cash',
            ],
        ]);

        $response = $this->postJson(route('sponsor.claim'), [
            'token' => $token,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['vip_unlocked' => true]);

        $character->refresh();
        $this->assertEquals(250, $character->ads_watched_total);
        $this->assertTrue((bool) $character->is_vip);
        $this->assertNotNull($character->vip_expires_at);
        $this->assertTrue($character->vip_expires_at->isFuture());
    }

    public function test_watching_10_ads_dispatches_vault_code_voucher(): void
    {
        $user = User::factory()->create();
        $character = $this->createCharacter($user, [
            'ads_watched_total' => 9,
            'ad_mask_progress' => 2,
        ]);

        $token = 'code_milestone_token';
        $this->actingAs($user)->withSession([
            'tbs_ad_session' => [
                'token' => $token,
                'started_at' => now()->subSeconds(16)->timestamp,
                'character_id' => $character->id,
                'reward_type' => 'mask',
            ],
        ]);

        $response = $this->postJson(route('sponsor.claim'), [
            'token' => $token,
        ]);

        $response->assertStatus(200);
        $character->refresh();

        $this->assertEquals(10, $character->ads_watched_total);
        $this->assertEquals(3, $character->masks); // 2 + 1

        // Check VaultCode created
        $code = VaultCode::where('character_id', $character->id)->where('code', 'LIKE', 'SPONSOR-%')->first();
        $this->assertNotNull($code);
        $this->assertEquals(15000, $code->reward_cash);
        $this->assertEquals(3, $code->reward_stars);
    }

    public function test_sponsorkamer_and_ad_endpoints_are_fully_disabled_when_ads_disabled(): void
    {
        config(['services.robinads.enabled' => false]);
        $user = User::factory()->create();
        $this->createCharacter($user);

        // 1. Sponsorkamer index redirects to shop with disabled message
        $this->actingAs($user)->get(route('sponsor.index'))
            ->assertRedirect(route('shop.index'))
            ->assertSessionHas('info');

        // 2. Start ad returns 403
        $this->actingAs($user)->postJson(route('sponsor.start'), ['reward_type' => 'cash'])
            ->assertStatus(403)
            ->assertJson(['success' => false]);

        // 3. Claim ad returns 403
        $this->actingAs($user)->postJson(route('sponsor.claim'), ['token' => 'dummy_token'])
            ->assertStatus(403)
            ->assertJson(['success' => false]);

        // 4. Redirect route returns to dashboard
        $this->actingAs($user)->get(route('sponsor.redirect'))
            ->assertRedirect(route('dashboard'))
            ->assertSessionHas('info');

        // 5. Status endpoint reports ads_enabled as false
        $this->actingAs($user)->getJson(route('sponsor.status'))
            ->assertOk()
            ->assertJson(['ads_enabled' => false]);
    }
}
