<?php

namespace Tests\Feature;

use App\Http\Controllers\SponsorAdController;
use App\Models\Character;
use App\Models\CharacterConnectFourStat;
use App\Models\CharacterMemoryStat;
use App\Models\CharacterTrueCrimeState;
use App\Models\LotteryRound;
use App\Models\LotteryTicket;
use App\Models\User;
use App\Models\VaultCode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class SponsorRewardsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        config(['services.robinads.enabled' => true]);
    }

    protected function createTestUser(): array
    {
        $user = User::factory()->create();
        $character = Character::create([
            'user_id' => $user->id,
            'name' => 'SponsorHero',
            'level' => 5,
            'energy' => 100,
            'max_energy' => 100,
            'health' => 100,
            'max_health' => 100,
            'cash' => 1000,
            'stars' => 5,
            'respect' => 10,
            'masks' => 2,
            'ad_mask_progress' => 0,
            'ad_vault_progress' => 0,
            'is_onboarded' => true,
        ]);

        return [$user, $character];
    }

    protected function simulateAdWatch(User $user, Character $character, string $rewardType): TestResponse
    {
        $token = Str::random(32);
        $adData = [
            'token' => $token,
            'started_at' => now()->subSeconds(20)->timestamp,
            'character_id' => $character->id,
            'reward_type' => $rewardType,
            'robin_reward_id' => null,
        ];

        Cache::put("tbs_ad_token_{$token}", $adData, now()->addMinutes(30));
        Cache::put("tbs_ad_char_{$character->id}", $adData, now()->addMinutes(30));

        return $this->actingAs($user)->postJson(route('sponsor.claim'), [
            'token' => $token,
        ]);
    }

    public function test_sponsor_page_shows_respect_and_removes_cooldown_reset(): void
    {
        [$user, $character] = $this->createTestUser();

        $response = $this->actingAs($user)->get(route('sponsor.index'));

        $response->assertStatus(200);
        $response->assertDontSee('Reset Alle Cooldowns');
        $response->assertSee('+1 Respect');
        $response->assertSee('3 Ads per Masker');
        $response->assertSee('Geheime Kluiscode Voucher');
    }

    public function test_respect_reward_grants_one_respect(): void
    {
        [$user, $character] = $this->createTestUser();
        $initialRespect = $character->respect;

        $response = $this->simulateAdWatch($user, $character, 'respect');

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $character->refresh();
        $this->assertEquals($initialRespect + 1, $character->respect);
    }

    public function test_mask_reward_requires_three_ads(): void
    {
        [$user, $character] = $this->createTestUser();
        $initialMasks = $character->masks;

        // Ad 1: progress -> 1, masks unchanged
        $res1 = $this->simulateAdWatch($user, $character, 'mask');
        $res1->assertStatus(200);
        $character->refresh();
        $this->assertEquals(1, $character->ad_mask_progress);
        $this->assertEquals($initialMasks, $character->masks);

        // Ad 2: progress -> 2, masks unchanged
        $res2 = $this->simulateAdWatch($user, $character, 'mask');
        $res2->assertStatus(200);
        $character->refresh();
        $this->assertEquals(2, $character->ad_mask_progress);
        $this->assertEquals($initialMasks, $character->masks);

        // Ad 3: progress -> 0, masks -> +1!
        $res3 = $this->simulateAdWatch($user, $character, 'mask');
        $res3->assertStatus(200);
        $character->refresh();
        $this->assertEquals(0, $character->ad_mask_progress);
        $this->assertEquals($initialMasks + 1, $character->masks);
    }

    public function test_vault_code_reward_requires_three_ads(): void
    {
        [$user, $character] = $this->createTestUser();

        // Ad 1: progress -> 1
        $this->simulateAdWatch($user, $character, 'vault_code');
        $character->refresh();
        $this->assertEquals(1, $character->ad_vault_progress);
        $this->assertEquals(0, VaultCode::where('character_id', $character->id)->count());

        // Ad 2: progress -> 2
        $this->simulateAdWatch($user, $character, 'vault_code');
        $character->refresh();
        $this->assertEquals(2, $character->ad_vault_progress);
        $this->assertEquals(0, VaultCode::where('character_id', $character->id)->count());

        // Ad 3: progress -> 0, VaultCode created!
        $this->simulateAdWatch($user, $character, 'vault_code');
        $character->refresh();
        $this->assertEquals(0, $character->ad_vault_progress);
        $this->assertEquals(1, VaultCode::where('character_id', $character->id)->count());

        $vaultCode = VaultCode::where('character_id', $character->id)->first();
        $this->assertStringStartsWith('SPONSOR-', $vaultCode->code);
    }

    public function test_prison_release_once_per_day_via_ad(): void
    {
        [$user, $character] = $this->createTestUser();
        $character->update([
            'is_in_prison' => true,
            'prison_until' => now()->addSeconds(60),
            'prison_reason' => 'Vast in isolatie',
        ]);

        $this->assertTrue($character->isImprisoned());

        // First ad releases character (<= 90s requires 1 ad)
        $res = $this->simulateAdWatch($user, $character, 'prison');
        $res->assertStatus(200);
        $character->refresh();
        $this->assertFalse($character->isImprisoned());

        // Subsequent ad on the same day also succeeds (unlimited per player request)
        $character->update([
            'is_in_prison' => true,
            'prison_until' => now()->addSeconds(60),
        ]);
        $user->unsetRelation('character');

        $res2 = $this->simulateAdWatch($user, $character, 'prison');
        $res2->assertStatus(200);
        $character->refresh();
        $this->assertFalse($character->isImprisoned());
    }

    public function test_daily_xp_reward_requires_three_ads_and_grants_50_xp(): void
    {
        [$user, $character] = $this->createTestUser();
        $initialXp = $character->xp;

        // Ad 1
        $res1 = $this->simulateAdWatch($user, $character, 'daily_xp');
        $res1->assertStatus(200);
        $character->refresh();
        $this->assertEquals(1, $character->getAdDailyData()['xp_progress']);
        $this->assertFalse($character->getAdDailyData()['xp_claimed']);
        $this->assertEquals($initialXp, $character->xp);

        // Ad 2
        $res2 = $this->simulateAdWatch($user, $character, 'daily_xp');
        $res2->assertStatus(200);
        $character->refresh();
        $this->assertEquals(2, $character->getAdDailyData()['xp_progress']);
        $this->assertFalse($character->getAdDailyData()['xp_claimed']);

        // Ad 3 -> Grants +50 XP and marks claimed
        $res3 = $this->simulateAdWatch($user, $character, 'daily_xp');
        $res3->assertStatus(200);
        $character->refresh();
        $this->assertEquals(3, $character->getAdDailyData()['xp_progress']);
        $this->assertTrue($character->getAdDailyData()['xp_claimed']);
        $this->assertEquals($initialXp + 50, $character->xp);

        // Cannot start again today
        $res4 = $this->actingAs($user)->postJson(route('sponsor.start'), ['reward_type' => 'daily_xp']);
        $res4->assertStatus(422);
    }

    public function test_daily_cash_reward_requires_three_ads_and_grants_50k_cash(): void
    {
        [$user, $character] = $this->createTestUser();
        $initialCash = $character->cash;

        // Ad 1
        $res1 = $this->simulateAdWatch($user, $character, 'daily_cash');
        $res1->assertStatus(200);
        $character->refresh();
        $this->assertEquals(1, $character->getAdDailyData()['cash_progress']);
        $this->assertFalse($character->getAdDailyData()['cash_claimed']);
        $this->assertEquals($initialCash, $character->cash);

        // Ad 2
        $res2 = $this->simulateAdWatch($user, $character, 'daily_cash');
        $res2->assertStatus(200);
        $character->refresh();
        $this->assertEquals(2, $character->getAdDailyData()['cash_progress']);

        // Ad 3 -> Grants +50,000 cash and marks claimed
        $res3 = $this->simulateAdWatch($user, $character, 'daily_cash');
        $res3->assertStatus(200);
        $character->refresh();
        $this->assertEquals(3, $character->getAdDailyData()['cash_progress']);
        $this->assertTrue($character->getAdDailyData()['cash_claimed']);
        $this->assertEquals($initialCash + 50000, $character->cash);

        // Cannot start again today
        $res4 = $this->actingAs($user)->postJson(route('sponsor.start'), ['reward_type' => 'daily_cash']);
        $res4->assertStatus(422);
    }

    public function test_daily_stars_reward_requires_two_ads_and_grants_5_stars(): void
    {
        [$user, $character] = $this->createTestUser();
        $initialStars = $character->stars ?? 0;

        // Ad 1
        $res1 = $this->simulateAdWatch($user, $character, 'daily_stars');
        $res1->assertStatus(200);
        $character->refresh();
        $this->assertEquals(1, $character->getAdDailyData()['stars_progress']);
        $this->assertFalse($character->getAdDailyData()['stars_claimed']);
        $this->assertEquals($initialStars, $character->stars ?? 0);

        // Ad 2 -> Grants +5 stars and marks claimed
        $res2 = $this->simulateAdWatch($user, $character, 'daily_stars');
        $res2->assertStatus(200);
        $character->refresh();
        $this->assertEquals(2, $character->getAdDailyData()['stars_progress']);
        $this->assertTrue($character->getAdDailyData()['stars_claimed']);
        $this->assertEquals($initialStars + 5, $character->stars);

        // Cannot start again today
        $res3 = $this->actingAs($user)->postJson(route('sponsor.start'), ['reward_type' => 'daily_stars']);
        $res3->assertStatus(422);
    }

    public function test_sponsor_index_shows_daily_rewards_and_no_kluismunt_card(): void
    {
        [$user, $character] = $this->createTestUser();

        $response = $this->actingAs($user)->get(route('sponsor.index'));

        $response->assertStatus(200);
        $response->assertSee('5 Sterren ⭐');
        $response->assertSee('€ 50.000 Cash');
        $response->assertSee('+50 XP Boost');
        $response->assertSee('Direct Vrijkomen');
        $response->assertDontSee('+1 Kluismunt');
        $response->assertSee('14-Daagse Loterij Ticket');
        $response->assertSee('15 Ads per Lot');
    }

    public function test_lottery_ticket_reward_requires_fifteen_ads_and_grants_ticket(): void
    {
        [$user, $character] = $this->createTestUser();
        $round = LotteryRound::current();
        $initialJackpot = $round->jackpot;
        $initialTicketsCount = LotteryTicket::where('character_id', $character->id)->count();

        $this->assertEquals(0, $character->ad_lottery_progress);

        // Watch 14 ads: progress advances, no ticket created yet
        for ($i = 1; $i <= 14; $i++) {
            $res = $this->simulateAdWatch($user, $character, 'lottery_ticket');
            $res->assertStatus(200);
            $res->assertJson(['success' => true]);
            $character->refresh();
            $this->assertEquals($i, $character->ad_lottery_progress);
            $this->assertEquals($initialTicketsCount, LotteryTicket::where('character_id', $character->id)->count());
        }

        // 15th ad: grants ticket, resets progress to 0, boosts round jackpot & tickets sold
        $res15 = $this->simulateAdWatch($user, $character, 'lottery_ticket');
        $res15->assertStatus(200);
        $res15->assertJson(['success' => true]);

        $character->refresh();
        $round->refresh();

        $this->assertEquals(0, $character->ad_lottery_progress);
        $this->assertEquals($initialTicketsCount + 1, LotteryTicket::where('character_id', $character->id)->count());
        $this->assertEquals($initialJackpot + 8000, $round->jackpot);

        $newTicket = LotteryTicket::where('character_id', $character->id)->latest('id')->first();
        $this->assertNotNull($newTicket);
        $this->assertEquals('ads', $newTicket->currency);
        $this->assertEquals(15, $newTicket->price_paid);
        $this->assertStringStartsWith('ADS-', $newTicket->ticket_number);

        // If ads are disabled globally, start returns 403; otherwise 200
        $startRes = $this->actingAs($user)->postJson(route('sponsor.start'), ['reward_type' => 'lottery_ticket']);
        $expectedStatus = SponsorAdController::areAdsEnabled() ? 200 : 403;
        $startRes->assertStatus($expectedStatus);
    }

    public function test_lottery_index_shows_cash_and_vip_sterren_tabs(): void
    {
        [$user, $character] = $this->createTestUser();

        $response = $this->actingAs($user)->get(route('lottery.index'));
        $response->assertStatus(200);
        $response->assertSee('Kopen met Contant Geld');
        $response->assertSee('Kopen met VIP Sterren');
    }

    public function test_claim_succeeds_via_timer_even_when_robin_reward_id_exists_but_not_verified(): void
    {
        [$user, $character] = $this->createTestUser();

        $token = Str::random(32);
        $adData = [
            'token' => $token,
            'started_at' => now()->subSeconds(16)->timestamp, // Watched for 16 seconds (> 12s)
            'character_id' => $character->id,
            'reward_type' => 'lottery_ticket',
            'robin_reward_id' => 'rwd_mock_99999', // Simulating Robin Ads ID generated at start()
        ];

        Cache::put("tbs_ad_token_{$token}", $adData, now()->addMinutes(30));
        Cache::put("tbs_ad_char_{$character->id}", $adData, now()->addMinutes(30));

        // When claiming without robin verification token or when Robin Ads returns pending,
        // it should fall back to timer verification because elapsedSeconds >= 12s.
        $response = $this->actingAs($user)->postJson(route('sponsor.claim'), [
            'token' => $token,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $character->refresh();
        $this->assertEquals(1, $character->ad_lottery_progress);
    }

    public function test_bullets_reward_requires_three_ads_and_grants_50_bullets(): void
    {
        [$user, $character] = $this->createTestUser();
        $initialBullets = $character->bullets ?? 0;

        $res1 = $this->simulateAdWatch($user, $character, 'bullets');
        $res1->assertStatus(200);
        $character->refresh();
        $this->assertEquals(1, $character->ad_bullets_progress);
        $this->assertEquals($initialBullets, $character->bullets ?? 0);

        $res2 = $this->simulateAdWatch($user, $character, 'bullets');
        $res2->assertStatus(200);
        $character->refresh();
        $this->assertEquals(2, $character->ad_bullets_progress);

        $res3 = $this->simulateAdWatch($user, $character, 'bullets');
        $res3->assertStatus(200);
        $character->refresh();
        $this->assertEquals(0, $character->ad_bullets_progress);
        $this->assertEquals($initialBullets + 50, $character->bullets);
    }

    public function test_bullets_reward_supports_alias_names(): void
    {
        [$user, $character] = $this->createTestUser();
        $start = $this->actingAs($user)->postJson(route('sponsor.start'), ['reward_type' => 'kogels']);
        $start->assertStatus(200);

        $token = $start->json('token');
        $cached = Cache::get("tbs_ad_token_{$token}");
        $this->assertEquals('bullets', $cached['reward_type']);
    }

    public function test_vest_reward_requires_three_ads_and_grants_12_hours(): void
    {
        [$user, $character] = $this->createTestUser();
        $this->assertFalse($character->hasVest());

        $this->simulateAdWatch($user, $character, 'vest')->assertStatus(200);
        $character->refresh();
        $this->assertEquals(1, $character->ad_vest_progress);

        $this->simulateAdWatch($user, $character, 'kogelvest')->assertStatus(200);
        $character->refresh();
        $this->assertEquals(2, $character->ad_vest_progress);

        $this->simulateAdWatch($user, $character, 'vest')->assertStatus(200);
        $character->refresh();
        $this->assertEquals(0, $character->ad_vest_progress);
        $this->assertTrue($character->hasVest());
    }

    public function test_coma_revive_requires_three_ads_and_revives_character(): void
    {
        [$user, $character] = $this->createTestUser();
        $character->update([
            'health' => 0,
            'coma_until' => now()->addMinutes(30),
        ]);
        $this->assertTrue($character->isComatose());

        $this->simulateAdWatch($user, $character, 'coma_revive')->assertStatus(200);
        $character->refresh();
        $this->assertEquals(1, $character->ad_coma_progress);

        $this->simulateAdWatch($user, $character, 'coma')->assertStatus(200);
        $character->refresh();
        $this->assertEquals(2, $character->ad_coma_progress);

        $this->simulateAdWatch($user, $character, 'ic')->assertStatus(200);
        $character->refresh();
        $this->assertEquals(0, $character->ad_coma_progress);
        $this->assertFalse($character->isComatose());
        $this->assertGreaterThanOrEqual(25, $character->health);
    }

    public function test_claim_uses_request_reward_type_fallback(): void
    {
        [$user, $character] = $this->createTestUser();
        $token = Str::random(32);
        // Session has no reward_type
        $adData = [
            'token' => $token,
            'started_at' => now()->subSeconds(20)->timestamp,
            'character_id' => $character->id,
            'robin_reward_id' => null,
        ];
        Cache::put("tbs_ad_token_{$token}", $adData, now()->addMinutes(30));

        $res = $this->actingAs($user)->postJson(route('sponsor.claim'), [
            'token' => $token,
            'reward_type' => 'bullets',
        ]);
        $res->assertStatus(200);
        $character->refresh();
        $this->assertEquals(1, $character->ad_bullets_progress);
    }

    public function test_unverified_minigame_cooldown_ad_skips_are_rejected(): void
    {
        [$user, $character] = $this->createTestUser();

        $c4Stat = CharacterConnectFourStat::create([
            'character_id' => $character->id,
            'matches_played' => 5,
            'cooldown_until' => now()->addMinutes(10),
        ]);

        $memStat = CharacterMemoryStat::create([
            'character_id' => $character->id,
            'cooldown_until' => now()->addMinutes(15),
        ]);

        $tcState = CharacterTrueCrimeState::create([
            'character_id' => $character->id,
            'cooldown_until' => now()->addHours(24),
            'cooldown_ads_watched' => 0,
        ]);

        // Unverified direct POSTs without a watched ad session must fail and preserve cooldowns
        $this->actingAs($user)->post(route('connect_four.skip_cooldown_ad'))
            ->assertRedirect(route('connect_four.index'))
            ->assertSessionHas('error');
        $this->assertTrue($c4Stat->fresh()->isOnCooldown());

        $this->actingAs($user)->post(route('memory.skip_cooldown_ad'))
            ->assertRedirect(route('memory.index'))
            ->assertSessionHas('error');
        $this->assertTrue($memStat->fresh()->isOnCooldown());

        $this->actingAs($user)->post(route('true_crime.skip_cooldown_ad'))
            ->assertRedirect(route('true_crime.index'))
            ->assertSessionHas('error');
        $this->assertEquals(0, $tcState->fresh()->cooldown_ads_watched);
        $this->assertTrue($tcState->fresh()->isOnCooldown());

        // Verified ad watches reset Connect Four and Memory cooldowns
        $this->simulateAdWatch($user, $character, 'cooldown_connect_four')->assertOk();
        $this->assertFalse($c4Stat->fresh()->isOnCooldown());
        $this->assertEquals(0, $c4Stat->fresh()->matches_played);

        $this->simulateAdWatch($user, $character, 'cooldown_memory')->assertOk();
        $this->assertFalse($memStat->fresh()->isOnCooldown());
    }

    public function test_claim_returns_403_when_ads_are_disabled(): void
    {
        [$user, $character] = $this->createTestUser();
        config(['services.robinads.enabled' => false]);

        $res = $this->actingAs($user)->postJson(route('sponsor.claim'), [
            'token' => 'disabled_test_token',
        ]);

        $res->assertStatus(403);
    }

    public function test_vest_reward_extends_existing_active_vest_duration_and_formats_hours_above_24(): void
    {
        $this->freezeTime();
        [$user, $character] = $this->createTestUser();
        $character->update([
            'vest_until' => now()->addHours(18),
            'ad_vest_progress' => 2,
        ]);

        $this->simulateAdWatch($user, $character, 'vest')->assertStatus(200);
        $character->refresh();
        $user->unsetRelation('character');

        $this->assertEquals(0, $character->ad_vest_progress);
        $this->assertEquals(30 * 3600, $character->getVestRemainingSeconds());

        $indexResponse = $this->actingAs($user)->get(route('sponsor.index'));
        $indexResponse->assertStatus(200);
        $indexResponse->assertSee('Actief (30:00u)');
    }

    public function test_coma_revive_clears_code_blauw_liquidation_streak_and_revives(): void
    {
        [$user, $character] = $this->createTestUser();
        $character->update([
            'health' => 0,
            'coma_until' => null,
            'perks' => ['liquidation_streak_deaths' => 10],
            'ad_coma_progress' => 2,
        ]);

        $this->assertTrue($character->needsReviveSerum());

        // Starting a non-coma ad while in Code Blauw must be rejected
        $blockedStart = $this->actingAs($user)->postJson(route('sponsor.start'), [
            'reward_type' => 'cash',
        ]);
        $blockedStart->assertStatus(422);

        // Starting coma_revive while in Code Blauw must succeed
        $allowedStart = $this->actingAs($user)->postJson(route('sponsor.start'), [
            'reward_type' => 'coma_revive',
        ]);
        $allowedStart->assertStatus(200);

        // Completing the 3rd coma_revive ad clears Code Blauw and restores 50% HP
        $this->simulateAdWatch($user, $character, 'coma_revive')->assertStatus(200);
        $character->refresh();

        $this->assertFalse($character->isComatose());
        $this->assertFalse($character->needsReviveSerum());
        $this->assertEquals(0, $character->getLiquidationStreakDeaths());
        $this->assertEquals(50, $character->health);
    }

    public function test_claim_rejects_mismatched_explicit_token(): void
    {
        [$user, $character] = $this->createTestUser();
        $activeToken = Str::random(32);
        $adData = [
            'token' => $activeToken,
            'started_at' => now()->subSeconds(20)->timestamp,
            'character_id' => $character->id,
            'reward_type' => 'cash',
            'robin_reward_id' => null,
        ];

        Cache::put("tbs_ad_token_{$activeToken}", $adData, now()->addMinutes(30));
        Cache::put("tbs_ad_char_{$character->id}", $adData, now()->addMinutes(30));

        // Claiming with a stale/different explicit token must fail and preserve the active session
        $badResponse = $this->actingAs($user)->postJson(route('sponsor.claim'), [
            'token' => 'stale_or_invalid_token_12345',
        ]);
        $badResponse->assertStatus(422);

        // Claiming with the valid active token succeeds
        $goodResponse = $this->actingAs($user)->postJson(route('sponsor.claim'), [
            'token' => $activeToken,
        ]);
        $goodResponse->assertStatus(200);
    }

    public function test_sponsor_modal_renders_robinsads_sdk_and_interstitial_without_redirect_clobber(): void
    {
        [$user, $character] = $this->createTestUser();

        $response = $this->actingAs($user)->get(route('sponsor.index'));
        $response->assertStatus(200);
        $response->assertSee('https://robinsads.com/ads.js', false);
        $response->assertDontSee('https://robinsads.com/sdk/robinads.min.js', false);
        $response->assertSee('robinSdk.showInterstitial', false);
        $response->assertDontSee("window.location.href = '".route('sponsor.index')."'", false);
    }

    public function test_claim_returns_structured_progress_fields_and_status_returns_all_progress_counters(): void
    {
        [$user, $character] = $this->createTestUser();

        // Guest status check returns 401
        $this->getJson(route('sponsor.status'))->assertStatus(401);

        // First ad of 3 for bullets returns reward_completed=false, progress_current=1, progress_required=3
        $res1 = $this->simulateAdWatch($user, $character, 'bullets');
        $res1->assertStatus(200);
        $res1->assertJson([
            'success' => true,
            'progress_current' => 1,
            'progress_required' => 3,
            'reward_completed' => false,
        ]);

        // Status endpoint returns all multi-ad progress counters
        $statusRes = $this->actingAs($user)->getJson(route('sponsor.status'));
        $statusRes->assertStatus(200);
        $statusRes->assertJson([
            'ads_enabled' => true,
            'ad_bullets_progress' => 1,
            'ad_vest_progress' => 0,
            'ad_coma_progress' => 0,
            'ad_prison_progress' => 0,
            'ad_mask_progress' => 0,
            'ad_lottery_progress' => 0,
            'ad_vault_progress' => 0,
        ]);

        // Complete 2nd and 3rd ad -> reward_completed=true, progress_current=3
        $this->simulateAdWatch($user, $character, 'bullets')->assertStatus(200);
        $res3 = $this->simulateAdWatch($user, $character, 'bullets');
        $res3->assertStatus(200);
        $res3->assertJson([
            'success' => true,
            'progress_current' => 3,
            'progress_required' => 3,
            'reward_completed' => true,
        ]);
    }

    public function test_vip_250_ad_milestone_preserves_lifetime_vip_without_setting_expiry(): void
    {
        [$user, $character] = $this->createTestUser();
        $character->update([
            'ads_watched_total' => 249,
            'is_vip' => true,
            'vip_expires_at' => null,
        ]);

        $res = $this->simulateAdWatch($user, $character, 'cash');
        $res->assertStatus(200);

        $character->refresh();
        $this->assertEquals(250, $character->ads_watched_total);
        $this->assertTrue((bool) $character->is_vip);
        $this->assertNull($character->vip_expires_at);
    }

    public function test_early_prison_release_on_first_ad_when_remaining_prison_time_under_threshold(): void
    {
        [$user, $character] = $this->createTestUser();
        $character->update([
            'is_in_prison' => true,
            'prison_until' => now()->addSeconds(80),
            'prison_reason' => 'Kort vergrijp',
            'ad_prison_progress' => 0,
        ]);

        $this->assertEquals(1, $character->getRequiredPrisonAds());

        $response = $this->simulateAdWatch($user, $character, 'prison');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'reward_type' => 'prison',
            'progress_current' => 1,
            'progress_required' => 1,
            'reward_completed' => true,
            'is_in_prison' => false,
        ]);

        $character->refresh();
        $this->assertFalse($character->isImprisoned());
        $this->assertFalse((bool) $character->is_in_prison);
        $this->assertNull($character->prison_until);
        $this->assertNull($character->prison_reason);
        $this->assertEquals(0, $character->ad_prison_progress);
    }

    public function test_prison_ad_claim_falls_back_to_cash_when_sentence_expired_during_ad_watch(): void
    {
        [$user, $character] = $this->createTestUser();
        $initialCash = $character->cash;

        $character->update([
            'is_in_prison' => true,
            'prison_until' => now()->subSeconds(5),
            'prison_reason' => 'Verlopen straf',
            'ad_prison_progress' => 1,
        ]);

        $response = $this->simulateAdWatch($user, $character, 'prison');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'reward_type' => 'prison',
            'progress_current' => 1,
            'progress_required' => 1,
            'reward_completed' => true,
            'is_in_prison' => false,
        ]);
        $this->assertStringContainsString('€ 2.500 zakgeld', $response->json('reward_details'));

        $character->refresh();
        $this->assertEquals($initialCash + 2500, $character->cash);
        $this->assertFalse($character->isImprisoned());
        $this->assertEquals(0, $character->ad_prison_progress);
    }

    public function test_true_crime_5_ads_completion_preserves_current_case_id_when_not_on_cooldown(): void
    {
        [$user, $character] = $this->createTestUser();

        $activeCaseId = 42;
        $state = CharacterTrueCrimeState::create([
            'character_id' => $character->id,
            'current_case_id' => $activeCaseId,
            'cooldown_until' => null,
            'cooldown_ads_watched' => 4,
        ]);

        $this->assertFalse($state->isOnCooldown());

        $response = $this->simulateAdWatch($user, $character, 'true_crime');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'reward_type' => 'true_crime',
            'progress_current' => 5,
            'progress_required' => 5,
            'reward_completed' => true,
        ]);

        $state->refresh();
        $this->assertEquals(0, $state->cooldown_ads_watched);
        $this->assertNull($state->cooldown_until);
        $this->assertEquals($activeCaseId, $state->current_case_id);
    }
}
