<?php

namespace Tests\Feature;

use App\Models\Character;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CpaleadPostbackTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        config(['services.cpalead.password' => 'test_cpalead_secret']);
    }

    public function test_postback_rejects_missing_lead_id_or_subid(): void
    {
        $response = $this->get('/api/cpalead/postback?password=test_cpalead_secret');
        $response->assertStatus(400);

        $response2 = $this->get('/api/cpalead/postback?password=test_cpalead_secret&lead_id=123');
        $response2->assertStatus(400);
    }

    public function test_postback_credits_character_and_stores_conversion(): void
    {
        $character = Character::first();
        $initialCash = $character->cash;
        $initialTotalAds = $character->ads_watched_total ?? 0;

        $response = $this->get('/api/cpalead/postback?'.http_build_query([
            'password' => 'test_cpalead_secret',
            'subid' => $character->id,
            'subid2' => 'cash',
            'lead_id' => 'cpalead_lead_test_001',
            'payout' => '0.75',
            'campaign_id' => '9988',
            'campaign_name' => 'Survey Test',
            'ip' => '127.0.0.1',
        ]));

        $response->assertOk();
        $response->assertSee('OK');

        $character->refresh();
        $this->assertEquals($initialCash + 2500, $character->cash);
        $this->assertEquals($initialTotalAds + 1, $character->ads_watched_total);

        $this->assertDatabaseHas('cpalead_conversions', [
            'lead_id' => 'cpalead_lead_test_001',
            'character_id' => $character->id,
            'payout' => 0.75,
        ]);
    }

    public function test_postback_duplicate_lead_does_not_credit_twice(): void
    {
        $character = Character::first();
        $initialCash = $character->cash;

        // First call
        $this->get('/api/cpalead/postback?'.http_build_query([
            'password' => 'test_cpalead_secret',
            'subid' => $character->id,
            'lead_id' => 'cpalead_lead_duplicate_test',
            'payout' => '1.00',
        ]))->assertOk();

        $character->refresh();
        $afterFirstCash = $character->cash;
        $this->assertEquals($initialCash + 2500, $afterFirstCash);

        // Second call with same lead_id
        $res2 = $this->get('/api/cpalead/postback?'.http_build_query([
            'password' => 'test_cpalead_secret',
            'subid' => $character->id,
            'lead_id' => 'cpalead_lead_duplicate_test',
            'payout' => '1.00',
        ]));

        $res2->assertOk();
        $res2->assertSee('Duplicate');

        // Cash must NOT have increased again
        $character->refresh();
        $this->assertEquals($afterFirstCash, $character->cash);
    }

    public function test_postback_vip_milestone_only_triggers_on_exact_250_multiples_and_prison_resets_progress(): void
    {
        $character = Character::first();
        $character->update([
            'ads_watched_total' => 249,
            'is_vip' => false,
            'vip_expires_at' => null,
            'is_in_prison' => true,
            'prison_until' => now()->addMinutes(5),
            'ad_prison_progress' => 2,
            'prison_escape_attempted' => true,
        ]);

        // 250th ad unlocks 24h VIP and releases from prison
        $this->get('/api/cpalead/postback?'.http_build_query([
            'password' => 'test_cpalead_secret',
            'subid' => 'TBS-'.$character->id,
            'subid2' => 'prison',
            'lead_id' => 'cpalead_lead_vip_250',
            'payout' => '0.50',
        ]))->assertOk();

        $character->refresh();
        $this->assertEquals(250, $character->ads_watched_total);
        $this->assertTrue((bool) $character->is_vip);
        $this->assertFalse((bool) $character->is_in_prison);
        $this->assertEquals(0, $character->ad_prison_progress);
        $this->assertFalse((bool) $character->prison_escape_attempted);

        // Expire VIP and verify 251st ad does NOT re-grant free VIP
        $character->update([
            'is_vip' => false,
            'vip_expires_at' => now()->subMinute(),
        ]);

        $this->get('/api/cpalead/postback?'.http_build_query([
            'password' => 'test_cpalead_secret',
            'subid' => $character->id,
            'subid2' => 'cash',
            'lead_id' => 'cpalead_lead_vip_251',
            'payout' => '0.50',
        ]))->assertOk();

        $character->refresh();
        $this->assertEquals(251, $character->ads_watched_total);
        $this->assertFalse((bool) $character->is_vip);
    }

    public function test_postback_rejects_invalid_password_and_grants_10_ad_milestone_vault_code_and_combat_rewards(): void
    {
        $character = Character::first();

        // Wrong password returns 403
        $this->get('/api/cpalead/postback?'.http_build_query([
            'password' => 'wrong_secret',
            'subid' => $character->id,
            'lead_id' => 'cpalead_unauth_01',
        ]))->assertStatus(403);

        // Set character to 9 total ads and 2/3 bullets progress
        $initialBullets = $character->bullets ?? 0;
        $character->update([
            'ads_watched_total' => 9,
            'ad_bullets_progress' => 2,
        ]);

        $this->get('/api/cpalead/postback?'.http_build_query([
            'password' => 'test_cpalead_secret',
            'subid' => $character->id,
            'subid2' => 'bullets',
            'lead_id' => 'cpalead_milestone_10',
            'payout' => '0.50',
        ]))->assertOk();

        $character->refresh();
        $this->assertEquals(10, $character->ads_watched_total);
        $this->assertEquals(0, $character->ad_bullets_progress);
        $this->assertEquals($initialBullets + 50, $character->bullets);

        $this->assertDatabaseHas('vault_codes', [
            'character_id' => $character->id,
            'reward_cash' => 15000,
            'reward_stars' => 3,
            'is_redeemed' => false,
        ]);
    }

    public function test_postback_handles_vest_and_coma_revive_rewards(): void
    {
        $this->freezeTime();
        $character = Character::first();
        $character->update([
            'health' => 0,
            'coma_until' => now()->addMinutes(45),
            'perks' => ['liquidation_streak_deaths' => 7],
            'ad_coma_progress' => 2,
            'vest_until' => now()->addHours(6),
            'ad_vest_progress' => 1,
        ]);

        $vestResponse = $this->get('/api/cpalead/postback?'.http_build_query([
            'password' => 'test_cpalead_secret',
            'subid' => $character->id,
            'subid2' => 'vest',
            'lead_id' => 'cpalead_vest_lead_01',
            'payout' => '0.80',
        ]));

        $vestResponse->assertOk();
        $vestResponse->assertSee('OK');

        $character->refresh();
        $this->assertEquals(0, $character->ad_vest_progress);
        $this->assertEquals(18 * 3600, $character->getVestRemainingSeconds());
        $this->assertDatabaseHas('cpalead_conversions', [
            'lead_id' => 'cpalead_vest_lead_01',
            'reward_granted' => '12u Kogelwerend Vest 🛡️',
        ]);

        $comaResponse = $this->get('/api/cpalead/postback?'.http_build_query([
            'password' => 'test_cpalead_secret',
            'subid' => $character->id,
            'subid2' => 'coma_revive',
            'lead_id' => 'cpalead_coma_lead_02',
            'payout' => '0.90',
        ]));

        $comaResponse->assertOk();
        $comaResponse->assertSee('OK');

        $character->refresh();
        $this->assertEquals(0, $character->ad_coma_progress);
        $this->assertNull($character->coma_until);
        $this->assertFalse($character->isComatose());
        $this->assertGreaterThanOrEqual(50, $character->health);
        $this->assertEquals(0, $character->getLiquidationStreakDeaths());
        $this->assertDatabaseHas('cpalead_conversions', [
            'lead_id' => 'cpalead_coma_lead_02',
            'reward_granted' => 'Ontwaakt uit Intensive Care Coma 💉',
        ]);
    }

    public function test_postback_handles_daily_xp_daily_stars_vault_code_and_lottery_ticket(): void
    {
        $character = Character::first();
        $initialTotalXp = $character->total_xp ?? 0;
        $initialStars = $character->stars ?? 0;

        // 1. daily_xp grants +50 XP and marks xp_claimed
        $this->get('/api/cpalead/postback?'.http_build_query([
            'password' => 'test_cpalead_secret',
            'subid' => $character->id,
            'subid2' => 'daily_xp',
            'lead_id' => 'cpalead_daily_xp_01',
            'payout' => '0.50',
        ]))->assertOk();

        $character->refresh();
        $this->assertEquals($initialTotalXp + 50, $character->total_xp);
        $this->assertTrue($character->getAdDailyData()['xp_claimed']);

        // 2. daily_stars grants +5 stars and marks stars_claimed
        $this->get('/api/cpalead/postback?'.http_build_query([
            'password' => 'test_cpalead_secret',
            'subid' => $character->id,
            'subid2' => 'daily_stars',
            'lead_id' => 'cpalead_daily_stars_02',
            'payout' => '0.50',
        ]))->assertOk();

        $character->refresh();
        $this->assertEquals($initialStars + 5, $character->stars);
        $this->assertTrue($character->getAdDailyData()['stars_claimed']);

        // 3. lottery_ticket creates an ads lottery ticket
        $this->get('/api/cpalead/postback?'.http_build_query([
            'password' => 'test_cpalead_secret',
            'subid' => $character->id,
            'subid2' => 'lottery_ticket',
            'lead_id' => 'cpalead_lottery_03',
            'payout' => '0.50',
        ]))->assertOk();

        $this->assertDatabaseHas('lottery_tickets', [
            'character_id' => $character->id,
            'currency' => 'ads',
        ]);
    }

    public function test_postback_prison_reward_falls_back_to_cash_when_character_not_in_prison(): void
    {
        $character = Character::first();
        $character->update([
            'is_in_prison' => false,
            'prison_until' => null,
            'ad_prison_progress' => 1,
        ]);
        $initialCash = $character->cash;

        $response = $this->get('/api/cpalead/postback?'.http_build_query([
            'password' => 'test_cpalead_secret',
            'subid' => $character->id,
            'subid2' => 'prison',
            'lead_id' => 'cpalead_prison_fallback_01',
            'payout' => '0.50',
        ]));

        $response->assertOk();
        $character->refresh();
        $this->assertEquals($initialCash + 2500, $character->cash);
        $this->assertEquals(0, $character->ad_prison_progress);
        $this->assertDatabaseHas('cpalead_conversions', [
            'lead_id' => 'cpalead_prison_fallback_01',
            'reward_granted' => '€2.500 zakgeld (Niet in cel)',
        ]);
    }
}
