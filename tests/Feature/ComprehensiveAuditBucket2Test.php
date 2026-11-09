<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\User;
use App\Services\CooldownService;
use App\Services\TheftService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class ComprehensiveAuditBucket2Test extends TestCase
{
    use RefreshDatabase;

    private function createCharacter(array $attrs = []): Character
    {
        $user = User::factory()->create(['is_onboarded' => true]);

        return Character::create(array_merge([
            'user_id' => $user->id,
            'name' => 'Pat_'.rand(10000, 99999),
            'level' => 15,
            'strength' => 50,
            'defense' => 50,
            'speed' => 50,
            'cash' => 500000,
            'bank' => 1000000,
            'energy' => 100,
            'max_energy' => 100,
            'health' => 100,
            'max_health' => 100,
            'vip_points' => 100,
            'stars' => 300,
            'respect' => 100,
            'bullets' => 100,
            'current_location' => 'Paviljoen 1 (Gesloten)',
            'is_in_prison' => false,
        ], $attrs));
    }

    public function test_protection_caps_are_strictly_enforced_and_clamped(): void
    {
        $char = $this->createCharacter();

        // 1. Legacy oversized timers: 72 hours isolation, 100 hours cash protection
        $char->update([
            'protection_until' => Carbon::now()->addHours(72),
            'cash_protection_until' => Carbon::now()->addHours(100),
        ]);

        $char->refresh();

        // Getters clamp safely to MAX_ISOLATION_HOURS (12h) and MAX_CASH_PROTECTION_HOURS (24h)
        $this->assertEquals(Character::MAX_ISOLATION_HOURS * 3600, $char->getIsolationProtectionRemainingSeconds());
        $this->assertEquals(Character::MAX_CASH_PROTECTION_HOURS * 3600, $char->getCashProtectionRemainingSeconds());
        $this->assertEquals(Character::MAX_CASH_PROTECTION_HOURS * 3600, $char->getProtectionRemainingSeconds());

        $expiresAt = $char->getProtectionExpiresAt();
        $this->assertNotNull($expiresAt);
        $this->assertLessThanOrEqual(Character::MAX_CASH_PROTECTION_HOURS * 3600 + 5, Carbon::now()->diffInSeconds($expiresAt, false));

        // 2. Normalizing character state clamps database timestamps
        $char->normalizeCharacterState();
        $char->refresh();

        $this->assertLessThanOrEqual(Character::MAX_ISOLATION_HOURS * 3600 + 5, Carbon::now()->diffInSeconds($char->protection_until, false));
        $this->assertLessThanOrEqual(Character::MAX_CASH_PROTECTION_HOURS * 3600 + 5, Carbon::now()->diffInSeconds($char->cash_protection_until, false));
    }

    public function test_voluntary_isolation_cannot_be_extended_beyond_12_hours(): void
    {
        $char = $this->createCharacter(['cash' => 2000000]);

        $this->actingAs($char->user);

        // Buy 8 hours of voluntary isolation
        $response1 = $this->post(route('isolation.activate'), [
            'hours' => 8,
            'currency' => 'cash',
        ]);
        $response1->assertSessionHas('success');
        $char->refresh();
        $this->assertTrue($char->hasIsolationProtection());
        $this->assertTrue($char->isInVoluntaryIsolation());

        // Attempting to add 6 more hours (8 + 6 = 14h > 12h max cap) is rejected
        $response2 = $this->post(route('isolation.activate'), [
            'hours' => 6,
            'currency' => 'cash',
        ]);
        $response2->assertSessionHas('error');
        $this->assertStringContainsString('maximaal 12 uur', session('error'));

        // Adding 4 hours (8 + 4 = 12h) is allowed
        $response3 = $this->post(route('isolation.activate'), [
            'hours' => 4,
            'currency' => 'cash',
        ]);
        $response3->assertSessionHas('success');
    }

    public function test_cash_shield_50_percent_reduction_in_theft_arena_and_hitman(): void
    {
        $attacker = $this->createCharacter([
            'level' => 30,
            'strength' => 500,
            'defense' => 500,
            'cash' => 100000,
            'energy' => 100,
        ]);

        $victimWithShield = $this->createCharacter([
            'cash' => 100000,
            'bank' => 200000,
            'cash_protection_until' => Carbon::now()->addHours(20),
            'protection_until' => null,
        ]);

        $this->actingAs($attacker->user);

        // 1. TheftService PvP robbery: cash shield halves stolen amount
        $theftService = app(TheftService::class);
        $result = $theftService->execute($attacker, 'pocket', $victimWithShield);
        if ($result['success']) {
            $this->assertGreaterThan(0, $result['cash_stolen']);
            // Stolen amount must be <= 15% (half of 30% max full haul)
            $this->assertLessThanOrEqual(15000, $result['cash_stolen']);
        }

        // 2. Arena Fight: FightController commitPendingBattle halves stolen cash
        $victimWithShield->refresh();
        $victimWithShield->update(['cash' => 100000]);
        app(CooldownService::class)->clearCooldown($attacker, 'fight_attack');

        $attackResp = $this->post(route('fight.attack'), [
            'opponent_id' => $victimWithShield->id,
        ]);
        $attackResp->assertRedirect(route('fight.arena', $victimWithShield));
        $arenaBattle = session('arena_battle');
        $this->assertTrue($arenaBattle['won']);
        $this->assertTrue($arenaBattle['cash_shield_applied']);

        // Stolen cash must be at most floor(25% * 100,000 * 0.50) = 12,500
        $this->assertLessThanOrEqual(12500, $arenaBattle['stolen_cash']);

        // Resolve arena fight
        $resolveResp = $this->post(route('fight.arena.resolve', $victimWithShield));
        $resolveResp->assertOk();

        // 3. Hitman Liquidation: 50% reduction on both cash and bank
        $victimWithShield->refresh();
        $victimWithShield->update([
            'cash' => 100000,
            'bank' => 200000,
            'current_location' => 'Paviljoen 1 (Gesloten)',
            'protection_until' => null,
        ]);
        $attacker->update([
            'bullets' => 50,
            'current_location' => 'Paviljoen 1 (Gesloten)',
            'energy' => 100,
        ]);

        $attacker->refresh();
        $initialAttackerCash = $attacker->cash;
        $shootResp = $this->post(route('hitman.shoot'), [
            'target_id' => $victimWithShield->id,
            'bullets' => 1,
        ]);
        $shootResp->assertSessionHas('success');

        $victimWithShield->refresh();
        $attacker->refresh();

        // Unshielded loot: 45% of 100k cash (45k), bank is 100% safe
        // Shielded loot: 50% of 45k cash = 22,500
        $loot = $attacker->cash - $initialAttackerCash;
        $this->assertEquals(22500, $loot);
        $this->assertEquals(77500, $victimWithShield->cash);
        $this->assertEquals(200000, $victimWithShield->bank);
    }

    public function test_attacker_breaks_own_protection_when_initiating_aggression(): void
    {
        $attacker = $this->createCharacter([
            'protection_until' => Carbon::now()->addHours(6),
            'cash_protection_until' => Carbon::now()->addHours(12),
        ]);

        $defender = $this->createCharacter(['protection_until' => null]);

        $this->assertTrue($attacker->hasIsolationProtection());
        $this->assertTrue($attacker->hasCashProtection());

        $this->actingAs($attacker->user);
        app(CooldownService::class)->clearCooldown($attacker, 'fight_attack');

        // Attacking in arena with confirmed isolation break
        $this->post(route('fight.attack'), [
            'opponent_id' => $defender->id,
            'confirm_break_isolation' => 1,
        ]);

        $attacker->refresh();
        $this->assertFalse($attacker->hasIsolationProtection());
        $this->assertFalse($attacker->hasCashProtection());
        $this->assertNull($attacker->protection_until);
        $this->assertNull($attacker->cash_protection_until);

        // Verify isolation cooldown of 2 hours was set
        $this->assertTrue($attacker->isOnCooldown('isolation_cooldown'));
        $this->assertGreaterThan(7000, $attacker->cooldownRemaining('isolation_cooldown'));
    }

    public function test_medical_shield_does_not_lock_cell_and_allows_actions(): void
    {
        $char = $this->createCharacter();
        $char->grantMedicalShield(30);
        $char->refresh();

        // Has protection from being attacked
        $this->assertTrue($char->hasIsolationProtection());
        $this->assertTrue($char->isProtected());

        // But is NOT in voluntary isolation: cell door is unlocked!
        $this->assertFalse($char->isInVoluntaryIsolation());
        $this->assertFalse($char->isActionBlockedByIsolation('missions.index'));
        $this->assertFalse($char->isActionBlockedByIsolation('hustle.index'));

        // Can perform theft without voluntary isolation modal/block
        $target = $this->createCharacter(['protection_until' => null]);
        $theftService = app(TheftService::class);
        $result = $theftService->execute($char, 'pocket', $target);

        // Action was NOT blocked by voluntary isolation error
        $this->assertStringNotContainsString('Je cel is vergrendeld in Vrijwillige Isolatie', $result['message']);

        // Initiating action broke own medical shield
        $char->refresh();
        $this->assertFalse($char->hasIsolationProtection());
        // And did NOT trigger isolation cooldown
        $this->assertFalse($char->isOnCooldown('isolation_cooldown'));
    }

    public function test_ic_coma_expiration_restores_health_and_grants_30_min_medical_shield(): void
    {
        $char = $this->createCharacter([
            'health' => 0,
            'coma_until' => Carbon::now()->subMinute(),
            'protection_until' => null,
        ]);

        $this->assertTrue($char->coma_until->isPast());

        // Normalize character state triggers post-coma recovery
        $char->normalizeCharacterState();
        $char->refresh();

        $this->assertFalse($char->isComatose());
        $this->assertEquals(20, $char->health);
        $this->assertTrue($char->hasIsolationProtection());
        $this->assertFalse($char->isInVoluntaryIsolation());
        $this->assertGreaterThanOrEqual(29 * 60, $char->getIsolationProtectionRemainingSeconds());
    }

    public function test_anti_harassment_daily_limits_enforced_and_surfaced(): void
    {
        $attacker = $this->createCharacter();
        $target = $this->createCharacter([
            'protection_until' => null,
            'coma_until' => null,
        ]);

        $this->actingAs($attacker->user);

        // 1. 3x/day per-attacker limit against same target
        $pairKillKey = "daily_liquidations_{$attacker->id}_{$target->id}_".now()->toDateString();
        Cache::put($pairKillKey, Character::MAX_DAILY_LIQUIDATIONS_PER_TARGET, now()->endOfDay());

        $shootResp = $this->post(route('hitman.shoot'), [
            'target_id' => $target->id,
            'bullets' => 1,
        ]);
        $shootResp->assertSessionHas('error');
        $this->assertStringContainsString('Anti-Pestprotocol', session('error'));

        // Check that index and search views flag daily_deaths_reached = true
        $indexResp = $this->get(route('hitman.index'));
        $indexResp->assertOk();
        $targets = $indexResp->viewData('targets');
        $targetInView = $targets->firstWhere('id', $target->id);
        if ($targetInView) {
            $this->assertTrue($targetInView->daily_deaths_reached);
        }

        $searchResp = $this->get(route('hitman.search', ['q' => $target->name]));
        $searchResp->assertOk();
        $found = collect($searchResp->json('targets'))->firstWhere('id', (string) $target->id);
        $this->assertNotNull($found);
        $this->assertTrue($found['dailyDeathsReached']);

        // 2. 5x/day total victim death limit
        Cache::forget($pairKillKey);
        $target->daily_deaths_count = Character::MAX_DAILY_DEATHS_BEFORE_SHIELD;
        $target->daily_deaths_date = now()->format('Y-m-d');
        $target->save();

        $shootResp2 = $this->post(route('hitman.shoot'), [
            'target_id' => $target->id,
            'bullets' => 1,
        ]);
        $shootResp2->assertSessionHas('error');
        $this->assertStringContainsString('Medisch Noodprotocol', session('error'));
    }

    public function test_voluntary_isolation_termination_cleans_up_and_triggers_cooldown(): void
    {
        $char = $this->createCharacter(['cash' => 500000]);
        $this->actingAs($char->user);

        // Activate voluntary isolation
        $this->post(route('isolation.activate'), [
            'hours' => 2,
            'currency' => 'cash',
        ]);
        $char->refresh();
        $this->assertTrue($char->isInVoluntaryIsolation());

        // Terminate voluntary isolation
        $termResp = $this->post(route('isolation.terminate'));
        $termResp->assertSessionHas('success');

        $char->refresh();
        $this->assertFalse($char->hasIsolationProtection());
        $this->assertNull($char->protection_until);
        $this->assertTrue($char->isOnCooldown('isolation_cooldown'));
        $this->assertGreaterThan(7000, $char->cooldownRemaining('isolation_cooldown'));
    }
}
