<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\NewsArticle;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LiquidatiePvPAndComaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_liquidatie_page_renders_v2_features_without_video_ad_button(): void
    {
        $user = User::where('email', 'demo@tbskliniek.nl')->first();
        $this->actingAs($user);

        $response = $this->get(route('hitman.index'));
        $response->assertStatus(200);
        $response->assertSee('Liquidatie &amp; Wapenarsenaal', false);
        $response->assertSee('Zwaar Tactisch Backfire-Vest');
        $response->assertSee(Character::REVIVE_SERUM_NAME);
        $response->assertSee('Schaduw-Inlichtingen (15m)');
        $response->assertDontSee('Gratis Kogel');
        $response->assertDontSee('claimAdBullet');
    }

    public function test_buy_3_hour_backfire_vest_with_cash_and_bullets_with_stars(): void
    {
        $user = User::where('email', 'demo@tbskliniek.nl')->first();
        $char = $user->character;
        $char->update([
            'cash' => 500000,
            'bank' => 1000000,
            'stars' => 300,
            'bullets' => 10,
            'vest_until' => null,
        ]);

        $this->actingAs($user);

        // Buy +50 bullets with 50 stars
        $respBullets = $this->post(route('hitman.buy_bullets'), [
            'ammo_tier' => 'box_50',
            'currency' => 'stars',
        ]);
        $respBullets->assertSessionHas('success');
        $char->refresh();
        $this->assertEquals(60, $char->bullets);
        $this->assertEquals(250, $char->stars);

        // Buy 3-hour Tactical Backfire Vest with €1.250.000 cash + bank
        $respVest = $this->post(route('hitman.buy_vest'), [
            'vest_tier' => 'tactical_3h',
            'currency' => 'cash',
        ]);
        $respVest->assertSessionHas('success');
        $char->refresh();
        $this->assertTrue($char->hasVest());
        $this->assertEquals('tactical_3h', $char->getActiveVestInfo()['id']);
        $this->assertEquals(55, $char->getVestBackfireChance());
        $this->assertEquals(250000, $char->cash + $char->bank);
    }

    public function test_player_cannot_hustle_while_comatose(): void
    {
        $user = User::where('email', 'demo@tbskliniek.nl')->first();
        $char = $user->character;
        $char->update([
            'coma_until' => Carbon::now()->addHours(4),
            'energy' => 100,
        ]);

        $this->actingAs($user);

        $resp = $this->post(route('hustle.attempt'), ['hustle_id' => 1]);
        $resp->assertSessionHas('error');
        $this->assertStringContainsString('Intensive Care', session('error'));
    }

    public function test_same_wing_requirement_15m_intel_one_tap_loot_and_10x_revive_serum(): void
    {
        $user = User::where('email', 'demo@tbskliniek.nl')->first();
        $shooter = $user->character;
        $shooter->update([
            'bullets' => 50,
            'energy' => 100,
            'level' => 26, // Dossier-Graad 26+ (Forensisch Scherpschutter)
            'strength' => 500,
            'cash' => 1000000,
            'bank' => 0,
            'current_location' => 'Paviljoen 1 (Gesloten)',
            'coma_until' => null,
            'protection_until' => null,
            'perks' => ['cold_blooded_pct' => 50],
        ]);

        $targetUser = User::where('email', '!=', 'demo@tbskliniek.nl')->first();
        $target = $targetUser->character;
        $target->update([
            'level' => 20,
            'protection_until' => null,
            'coma_until' => null,
            'defense' => 10,
            'cash' => 100000,
            'bank' => 200000,
            'respect' => 1000,
            'vest_until' => null,
            'current_location' => 'Het Kookhuis (Keuken)',
            'perks' => ['liquidation_streak_deaths' => 8],
        ]);

        $this->actingAs($user);

        // 1. Salvo with too few bullets (< 10) is rejected
        $wrongWingResp = $this->post(route('hitman.shoot'), [
            'target_id' => $target->id,
            'bullets' => 5,
        ]);
        $wrongWingResp->assertSessionHas('error');
        $this->assertStringContainsString('Een salvo vereist minimaal 10 patronen', session('error'));

        // 2. Start 15-minute Shadow Intel operation (€350.000)
        $intelResp = $this->post(route('hitman.intel'), [
            'target_id' => $target->id,
            'intel_type' => 'standard_15m',
            'currency' => 'cash',
        ]);
        $intelResp->assertSessionHas('success');
        $shooter->refresh();
        $this->assertEquals(650000, $shooter->cash);
        $this->assertNotNull($shooter->getIntelForTarget($target->id));

        // 3. Travel to the target's wing ('Het Kookhuis (Keuken)')
        $shooter->update(['current_location' => 'Het Kookhuis (Keuken)']);

        // 4. Execute 1-Bullet Genadeschot (9th liquidation for target -> 20-minute IC coma, evacuated to new wing, 45% cash + 7% bank stolen!)
        $shootResp = $this->post(route('hitman.shoot'), [
            'target_id' => $target->id,
            'bullets' => 1,
        ]);
        $shootResp->assertSessionHas('success');

        $shooter->refresh();
        $target->refresh();

        // 45% of 100,000 cash = 45,000; 7% of 200,000 bank = 14,000; total = 59,000
        $this->assertEquals(49, $shooter->bullets);
        $this->assertEquals(650000 + 59000, $shooter->cash);
        $this->assertEquals(55000, $target->cash);
        $this->assertEquals(186000, $target->bank);
        $this->assertEquals(9, $target->getLiquidationStreakDeaths());
        $this->assertTrue($target->isComatose());
        $this->assertEquals(0, $target->health);
        $this->assertNotEquals('Het Kookhuis (Keuken)', $target->current_location);

        // Simulate the 20-minute IC coma expiring so the target wakes up with 20 HP
        $target->update([
            'coma_until' => Carbon::now()->subMinute(),
            'protection_until' => null,
            'current_location' => 'De Luchtplaats',
        ]);
        $target->normalizeCharacterState();
        $target->refresh();
        $this->assertFalse($target->isComatose());
        $this->assertEquals(20, $target->health);
        $this->assertTrue($target->hasIsolationProtection());

        // Simulate 30-minute post-coma medical recovery shield expiring for 10th shot test
        $target->update(['protection_until' => null]);

        // 5. 10th liquidation triggers Code Blauw (Coma) requiring the Forensisch Reanimatie-Serum
        $shooter->update([
            'energy' => 100,
            'current_location' => 'De Luchtplaats',
            'perks' => ['cold_blooded_pct' => 50],
        ]);

        $tenthResp = $this->post(route('hitman.shoot'), [
            'target_id' => $target->id,
            'bullets' => 1,
        ]);
        $tenthResp->assertSessionHas('success');

        $target->refresh();
        $this->assertEquals(10, $target->getLiquidationStreakDeaths());
        $this->assertTrue($target->needsReviveSerum());
        $this->assertTrue($target->isComatose());

        // 6. Target buys Forensisch Reanimatie-Serum (€1.500.000) to come back to life and reset counter to 0/10
        $target->update(['bank' => 2000000]);
        $this->actingAs($targetUser);

        $reviveResp = $this->post(route('hitman.revive_serum'), [
            'currency' => 'cash',
        ]);
        $reviveResp->assertSessionHas('success');

        $target->refresh();
        $this->assertEquals(0, $target->getLiquidationStreakDeaths());
        $this->assertFalse($target->isComatose());
        $this->assertEquals($target->max_health, $target->health);
    }

    public function test_pvp_news_command_replaces_old_article_with_new_v2_article(): void
    {
        $this->artisan('tbs:publish-pvp-news')->assertExitCode(0);

        $this->assertDatabaseMissing('news_articles', [
            'slug' => 'grote-pvp-update-eerlijke-liquidatie-fair-play-bloedwraak',
        ]);

        $latestPinned = NewsArticle::where('is_pinned', true)->first();
        $this->assertNotNull($latestPinned);
        $this->assertStringContainsString('Liquidatie 2.0', $latestPinned->title);
        $this->assertStringContainsString('45% van het contante geld', $latestPinned->content);
        $this->assertStringContainsString('7% van het geld op de bankrekening', $latestPinned->content);
    }

    public function test_daily_liquidation_anti_bully_cap_blocks_4th_kill_on_same_target(): void
    {
        $user = User::where('email', 'demo@tbskliniek.nl')->first();
        $shooter = $user->character;

        $targetUser = User::where('email', '!=', 'demo@tbskliniek.nl')->first();
        $target = $targetUser->character;

        $this->actingAs($user);

        $target->update(['daily_deaths_count' => 0, 'daily_deaths_date' => now()->format('Y-m-d')]);

        for ($hit = 1; $hit <= 3; $hit++) {
            $shooter->refresh();
            $target->refresh();
            $shooter->update([
                'bullets' => 100,
                'energy' => 100,
                'level' => 26,
                'xp' => 0,
                'strength' => 150,
                'is_in_prison' => false,
                'prison_until' => null,
                'current_location' => 'De Luchtplaats',
                'coma_until' => null,
                'protection_until' => null,
                'perks' => ['cold_blooded_pct' => 50, 'liquidation_streak_deaths' => 0],
            ]);
            $target->update([
                'level' => 25,
                'defense' => 10,
                'health' => 100,
                'is_in_prison' => false,
                'prison_until' => null,
                'vest_until' => null,
                'coma_until' => null,
                'protection_until' => null,
                'current_location' => 'De Luchtplaats',
                'perks' => ['liquidation_streak_deaths' => 0],
            ]);

            $resp = $this->post(route('hitman.shoot'), [
                'target_id' => $target->id,
                'bullets' => 1,
            ]);
            $resp->assertSessionHas('success');
        }

        // 4th attempt on the same target today is blocked by Anti-Pestprotocol (max 3 per target per day)
        $shooter->refresh();
        $target->refresh();
        $shooter->update([
            'bullets' => 100,
            'energy' => 100,
            'is_in_prison' => false,
            'prison_until' => null,
            'current_location' => 'De Luchtplaats',
            'perks' => ['cold_blooded_pct' => 50, 'liquidation_streak_deaths' => 0],
        ]);
        $target->update([
            'health' => 100,
            'is_in_prison' => false,
            'prison_until' => null,
            'vest_until' => null,
            'coma_until' => null,
            'protection_until' => null,
            'current_location' => 'De Luchtplaats',
            'perks' => ['liquidation_streak_deaths' => 0],
        ]);

        $blockedResp = $this->post(route('hitman.shoot'), [
            'target_id' => $target->id,
            'bullets' => 1,
        ]);
        $blockedResp->assertSessionHas('error');
        $this->assertStringContainsString('Anti-Pestprotocol', session('error'));
    }
}
