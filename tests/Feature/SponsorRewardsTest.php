<?php

namespace Tests\Feature;

use App\Models\Character;
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
            'prison_until' => now()->addMinutes(30),
            'prison_reason' => 'Vast in isolatie',
        ]);

        $this->assertTrue($character->isImprisoned());

        // First ad releases character
        $res = $this->simulateAdWatch($user, $character, 'prison');
        $res->assertStatus(200);
        $character->refresh();
        $this->assertFalse($character->isImprisoned());

        // Subsequent ad on the same day also succeeds (unlimited per player request)
        $character->update([
            'is_in_prison' => true,
            'prison_until' => now()->addMinutes(30),
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

        // Can immediately start watching again (unlimited!)
        $startRes = $this->actingAs($user)->postJson(route('sponsor.start'), ['reward_type' => 'lottery_ticket']);
        $startRes->assertStatus(200);
    }

    public function test_lottery_index_shows_free_ads_tab_and_progress(): void
    {
        [$user, $character] = $this->createTestUser();
        $character->update(['ad_lottery_progress' => 7]);

        $response = $this->actingAs($user)->get(route('lottery.index'));
        $response->assertStatus(200);
        $response->assertSee('Gratis Loten via Sponsor Advertenties (15 Ads = 1 Lot)');
        $response->assertSee('7 / 15 Ads Bekeken');
        $response->assertSee('Onbeperkt Herhaalbaar');
    }
}
