<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\Gang;
use App\Models\GangProperty;
use App\Models\User;
use App\Services\TheftService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CashProtectionSystemTest extends TestCase
{
    use RefreshDatabase;

    private function createCharacter(array $attrs = []): Character
    {
        $user = User::factory()->create(['is_onboarded' => true]);

        return Character::create(array_merge([
            'user_id' => $user->id,
            'name' => 'Speler_'.rand(1000, 9999),
            'level' => 10,
            'strength' => 20,
            'defense' => 20,
            'speed' => 20,
            'cash' => 500000,
            'bank' => 1000000,
            'energy' => 100,
            'max_energy' => 100,
            'health' => 100,
            'max_health' => 100,
            'vip_points' => 50,
            'stars' => 100,
            'respect' => 100,
            'is_in_prison' => false,
        ], $attrs));
    }

    public function test_vip_shop_redeem_cash_protection_sets_24h_shield_and_prevents_month_stacking(): void
    {
        $char = $this->createCharacter(['vip_points' => 60]);

        $response = $this->actingAs($char->user)->post(route('vip.redeem'), [
            'reward_type' => 'cash_protection',
        ]);

        $response->assertSessionHas('success');
        $char->refresh();

        $this->assertEquals(40, $char->vip_points);
        $this->assertTrue($char->hasCashProtection());
        $this->assertFalse($char->isProtected());
        $this->assertNotNull($char->cash_protection_until);
        $this->assertLessThanOrEqual(24 * 3600 + 5, $char->getCashProtectionRemainingSeconds());

        // Attempting to stack another 24h while active is rejected
        $stackResponse = $this->actingAs($char->user)->post(route('vip.redeem'), [
            'reward_type' => 'cash_protection',
        ]);
        $stackResponse->assertSessionHas('error');
        $char->refresh();
        $this->assertEquals(40, $char->vip_points);
    }

    public function test_attacking_breaks_both_isolation_and_vip_cash_protection(): void
    {
        $char = $this->createCharacter([
            'cash_protection_until' => now()->addHours(12),
            'protection_until' => now()->addHours(4),
        ]);

        $this->assertTrue($char->hasCashProtection());
        $this->assertTrue($char->hasIsolationProtection());

        $char->breakProtection();
        $char->refresh();

        $this->assertNull($char->protection_until);
        $this->assertNull($char->cash_protection_until);
        $this->assertFalse($char->hasCashProtection());
        $this->assertFalse($char->isProtected());
        $this->assertTrue($char->isOnCooldown('isolation_cooldown'));
    }

    public function test_luchtplaats_attack_halves_loot_when_defender_has_cash_protection(): void
    {
        $attacker = $this->createCharacter([
            'level' => 25,
            'strength' => 250,
            'energy' => 100,
            'cash' => 100000,
            'cash_protection_until' => now()->addHours(10),
        ]);
        $defender = $this->createCharacter([
            'level' => 20,
            'defense' => 10,
            'cash' => 500000,
            'cash_protection_until' => now()->addHours(12),
        ]);

        $response = $this->actingAs($attacker->user)->post(route('fight.attack'), [
            'opponent_id' => $defender->id,
            'auto_resolve' => 1,
        ]);

        $response->assertRedirect();
        $attacker->refresh();
        $defender->refresh();

        // Attacker's own cash shield expired because they attacked
        $this->assertFalse($attacker->hasCashProtection());
        // Defender lost 50% of normal 10%-25% loot (so between 5% and 12.5% of 500,000 = 25,000 to 62,500)
        $stolen = 500000 - (int) $defender->cash;
        $this->assertGreaterThanOrEqual(25000, $stolen);
        $this->assertLessThanOrEqual(62500, $stolen);
    }

    public function test_theft_halves_stolen_cash_when_victim_has_cash_protection(): void
    {
        $thief = $this->createCharacter([
            'level' => 25,
            'speed' => 200,
            'stamina' => 200,
            'intelligence' => 200,
            'energy' => 100,
        ]);
        $victim = $this->createCharacter([
            'level' => 18,
            'defense' => 10,
            'cash' => 250000,
            'cash_protection_until' => now()->addHours(12),
        ]);

        $theftService = app(TheftService::class);
        $result = $theftService->stealFromInmate($thief, $victim);

        if ($result['success']) {
            $this->assertGreaterThan(0, $result['cash_stolen']);
            // Normal PvP full haul is max 30% of 250,000 (75,000); with 50% cash shield max stolen is 37,500
            $this->assertLessThanOrEqual(37500, $result['cash_stolen']);
        } else {
            $this->assertStringNotContainsString('kan momenteel niet worden bestolen', $result['message']);
        }
    }

    public function test_voluntary_isolation_caps_at_12_hours_locks_actions_and_allows_banking(): void
    {
        $char = $this->createCharacter([
            'cash' => 2000000,
            'bank' => 1000000,
            'protection_until' => null,
        ]);

        // 1. Buying 24 hours is rejected by the 12-hour max cap
        $overCap = $this->actingAs($char->user)->post(route('isolation.activate'), [
            'hours' => 24,
            'currency' => 'cash',
        ]);
        $overCap->assertSessionHasErrors('hours');

        // 2. Buying 12 hours succeeds
        $okResp = $this->actingAs($char->user)->post(route('isolation.activate'), [
            'hours' => 12,
            'currency' => 'cash',
        ]);
        $okResp->assertSessionHas('success');
        $char->refresh();
        $this->assertTrue($char->isInVoluntaryIsolation());

        // 3. Active clinic actions (e.g. training) are blocked while locked in voluntary isolation
        $trainResp = $this->actingAs($char->user)->post(route('training.train'), [
            'attribute' => 'strength',
        ]);
        $trainResp->assertSessionHas('error');
        $this->assertStringContainsString('Vrijwillige Isolatie', session('error'));

        // 4. Banking is still allowed while in voluntary isolation
        $bankResp = $this->actingAs($char->user)->post(route('bank.deposit'), [
            'amount' => 100000,
        ]);
        $bankResp->assertSessionHas('success');

        // 5. Terminating isolation early unlocks the cell and sets a 2h cooldown
        $termResp = $this->actingAs($char->user)->post(route('isolation.terminate'));
        $termResp->assertSessionHas('success');
        $char->refresh();
        $this->assertFalse($char->hasIsolationProtection());
        $this->assertTrue($char->isOnCooldown('isolation_cooldown'));

        // 6. Cannot immediately re-activate isolation while on cooldown
        $reenterResp = $this->actingAs($char->user)->post(route('isolation.activate'), [
            'hours' => 1,
            'currency' => 'cash',
        ]);
        $reenterResp->assertSessionHas('error');
    }

    public function test_migration_and_normalizer_clamp_month_long_protection(): void
    {
        $char = $this->createCharacter([
            'protection_until' => now()->addDays(30),
            'cash_protection_until' => now()->addDays(30),
        ]);

        $migration = require database_path('migrations/2026_11_04_100000_rebalance_protection_caps_and_gang_lab_raids.php');
        $migration->up();

        $char->refresh();
        $this->assertLessThanOrEqual(12 * 3600 + 5, $char->getIsolationProtectionRemainingSeconds());
        $this->assertLessThanOrEqual(24 * 3600 + 5, $char->getCashProtectionRemainingSeconds());

        $this->assertDatabaseHas('news_articles', [
            'slug' => 'pvp-bescherming-bende-balans-update-community-poll',
            'is_pinned' => true,
            'is_published' => true,
        ]);
    }

    public function test_gang_lab_ripdeal_steals_from_rival_production_lab(): void
    {
        $raider = $this->createCharacter([
            'level' => 30,
            'strength' => 300,
            'defense' => 300,
            'energy' => 100,
            'cash' => 100000,
        ]);
        $defenderLeader = $this->createCharacter(['level' => 15]);

        $attackerGang = Gang::create([
            'name' => 'Bad Posse',
            'tag' => 'BAD',
            'specialty' => 'smokkel',
            'leader_character_id' => $raider->id,
            'treasury' => 0,
        ]);
        $raider->update(['gang_id' => $attackerGang->id, 'gang_role' => 'leader']);

        $defenderGang = Gang::create([
            'name' => 'Rival Syndicate',
            'tag' => 'RIV',
            'specialty' => 'smokkel',
            'leader_character_id' => $defenderLeader->id,
            'treasury' => 500000,
        ]);
        $defenderLeader->update(['gang_id' => $defenderGang->id, 'gang_role' => 'leader']);

        $lab = GangProperty::create([
            'gang_id' => $defenderGang->id,
            'property_key' => 'wietzolder',
            'name' => 'Wietzolder Achterwijk',
            'category' => 'wiet',
            'type' => 'production',
            'production_status' => 'producing',
            'purchase_price' => 250000,
            'production_duration_minutes' => 240,
            'production_reward_cash' => 200000,
            'production_started_at' => now()->subHour(),
            'production_ends_at' => now()->addHours(3),
        ]);

        $response = $this->actingAs($raider->user)->post(route('gangs.panden.rip', $lab));
        $response->assertSessionHas('success');

        $raider->refresh();
        $attackerGang->refresh();

        // 35% of 200,000 = 70,000 (35,000 personal cash + 35,000 gang treasury)
        $this->assertEquals(135000, $raider->cash);
        $this->assertEquals(35000, $attackerGang->treasury);

        // Second immediate raid on the same lab is blocked by the 2-hour raid shield
        $secondResp = $this->actingAs($raider->user)->post(route('gangs.panden.rip', $lab));
        $secondResp->assertSessionHas('error');
    }
}
