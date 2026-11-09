<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\Gang;
use App\Models\GangWar;
use App\Models\User;
use App\Services\GangWageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GangWageScalingAndWarsTest extends TestCase
{
    use RefreshDatabase;

    public function test_gang_wage_scales_with_character_level_and_role(): void
    {
        $service = app(GangWageService::class);

        $gang = Gang::create([
            'name' => 'The Syndicate',
            'tag' => 'SYN',
            'specialty' => 'violence',
            'description' => 'Test gang',
            'respect' => 1000,
            'treasury' => 1000000,
            'attack_power' => 1000,
            'total_defense' => 1000,
            'bling_shops' => 5,
        ]);

        $lowLevelMember = Character::factory()->create([
            'gang_id' => $gang->id,
            'gang_role' => 'member',
            'level' => 1,
            'last_active_at' => now(),
        ]);

        $highLevelLeader = Character::factory()->create([
            'gang_id' => $gang->id,
            'gang_role' => 'leader',
            'level' => 50,
            'last_active_at' => now(),
        ]);

        $lowWage = $service->calculateHourlyWage($gang, $lowLevelMember);
        $highWage = $service->calculateHourlyWage($gang, $highLevelLeader);

        // High level leader must earn significantly more than level 1 member
        $this->assertGreaterThan($lowWage, $highWage);

        // Level 50 gives ~2.225x, leader gives 1.25x -> ratio should be > 2.5x
        $this->assertGreaterThan(2.5 * $lowWage, $highWage);
    }

    public function test_can_declare_24_hour_gang_war(): void
    {
        $leaderUser = User::factory()->create();
        $leaderChar = Character::factory()->create([
            'user_id' => $leaderUser->id,
            'gang_role' => 'leader',
            'level' => 30,
        ]);

        $gangA = Gang::create([
            'name' => 'Bloedbroeders',
            'tag' => 'BLD',
            'specialty' => 'violence',
            'description' => 'Gang A',
            'treasury' => 1000000,
            'leader_character_id' => $leaderChar->id,
        ]);
        $leaderChar->gang_id = $gangA->id;
        $leaderChar->save();

        $gangB = Gang::create([
            'name' => 'Oostfront',
            'tag' => 'OST',
            'specialty' => 'violence',
            'description' => 'Gang B',
            'treasury' => 1000000,
        ]);

        $response = $this->actingAs($leaderUser)->post(route('gangs.declare-war'), [
            'target_gang_id' => $gangB->id,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $activeWar = GangWar::findActiveForGang($gangA->id);
        $this->assertNotNull($activeWar);
        $this->assertEquals($gangA->id, $activeWar->challenger_gang_id);
        $this->assertEquals($gangB->id, $activeWar->defender_gang_id);
        $this->assertEquals('active', $activeWar->status);
        $this->assertTrue($activeWar->isActive());
    }

    public function test_capturing_zone_during_war_awards_score_and_settlement_grants_rewards(): void
    {
        $gangA = Gang::create([
            'name' => 'Syndicate One',
            'tag' => 'S1',
            'specialty' => 'violence',
            'description' => 'Gang A',
            'treasury' => 2000000,
            'respect' => 500,
        ]);

        $gangB = Gang::create([
            'name' => 'Syndicate Two',
            'tag' => 'S2',
            'specialty' => 'violence',
            'description' => 'Gang B',
            'treasury' => 1000000,
            'respect' => 500,
        ]);

        $war = GangWar::create([
            'challenger_gang_id' => $gangA->id,
            'defender_gang_id' => $gangB->id,
            'status' => 'active',
            'started_at' => now()->subHours(25), // Expired
            'ends_at' => now()->subHour(),
            'challenger_score' => 60,
            'defender_score' => 20,
            'war_pot' => 500000,
            'respect_pot' => 100,
        ]);

        // Settle war
        $winner = $war->settle();

        $this->assertNotNull($winner);
        $this->assertEquals($gangA->id, $winner->id);

        $gangA->refresh();
        $gangB->refresh();

        // Winner gets dominance and cash
        $this->assertTrue($gangA->hasWarDominance());
        $this->assertGreaterThan(2000000, $gangA->treasury);

        // Loser loses 10% treasury
        $this->assertLessThan(1000000, $gangB->treasury);
    }

    public function test_gang_war_settle_respects_protected_treasury(): void
    {
        $gangA = Gang::create([
            'name' => 'Syndicate Alpha',
            'tag' => 'SA',
            'specialty' => 'violence',
            'treasury' => 500000,
            'respect' => 100,
        ]);

        $gangB = Gang::create([
            'name' => 'Syndicate Beta',
            'tag' => 'SB',
            'specialty' => 'violence',
            'treasury' => 1000000000, // 1 Billion
            'protected_treasury' => 1000000000, // All 1 Billion is protected
            'respect' => 100,
        ]);

        $war = GangWar::create([
            'challenger_gang_id' => $gangA->id,
            'defender_gang_id' => $gangB->id,
            'status' => 'active',
            'started_at' => now()->subHours(25),
            'ends_at' => now()->subHour(),
            'challenger_score' => 100,
            'defender_score' => 10,
            'war_pot' => 500000,
            'respect_pot' => 100,
        ]);

        // Settle war where gangB loses but has 100% protected treasury
        $winner = $war->settle();

        $this->assertNotNull($winner);
        $this->assertEquals($gangA->id, $winner->id);

        $gangB->refresh();
        // Since lootable treasury is 0, protected treasury is untouched
        $this->assertEquals(1000000000, $gangB->treasury);
        $this->assertEquals(1000000000, $gangB->protected_treasury);
    }

    public function test_expired_active_war_can_be_found_and_settled(): void
    {
        $gangA = Gang::create([
            'name' => 'Challenger Gang',
            'tag' => 'CHL',
            'specialty' => 'violence',
            'treasury' => 500000,
        ]);

        $gangB = Gang::create([
            'name' => 'Defender Gang',
            'tag' => 'DEF',
            'specialty' => 'violence',
            'treasury' => 500000,
        ]);

        $war = GangWar::create([
            'challenger_gang_id' => $gangA->id,
            'defender_gang_id' => $gangB->id,
            'status' => 'active',
            'started_at' => now()->subHours(25),
            'ends_at' => now()->subMinute(),
            'challenger_score' => 50,
            'defender_score' => 30,
            'war_pot' => 500000,
        ]);

        // findActiveForGang must still find the unsettled expired war so the UI can settle it
        $found = GangWar::findActiveForGang($gangA->id);
        $this->assertNotNull($found);
        $this->assertEquals($war->id, $found->id);

        $found->settle();
        $this->assertEquals('finished', $war->fresh()->status);

        // Once finished, findActiveForGang must return null
        $this->assertNull(GangWar::findActiveForGang($gangA->id));
    }

    public function test_negative_respect_does_not_produce_negative_wage_bonus(): void
    {
        $service = app(GangWageService::class);

        $gang = Gang::create([
            'name' => 'Zero Respect Gang',
            'tag' => 'ZRG',
            'specialty' => 'violence',
            'respect' => -500000,
            'treasury' => 100000,
            'bling_shops' => 0,
        ]);

        $wage = $service->calculateHourlyWage($gang);
        $this->assertGreaterThanOrEqual(GangWageService::BASE_HOURLY_WAGE, $wage);

        $breakdown = $service->getWageBreakdown($gang);
        $this->assertEquals(0, $breakdown['respect_bonus_pct']);
    }

    public function test_refund_excess_kiosks_command_increments_protected_treasury(): void
    {
        $gang = Gang::create([
            'name' => 'Kiosk Hoarder Gang',
            'tag' => 'KHG',
            'specialty' => 'contraband',
            'bling_shops' => 1500, // 500 excess
            'treasury' => 100000,
            'protected_treasury' => 50000,
        ]);

        $this->artisan('gang:refund-excess-kiosks', ['--max' => 1000, '--price' => 50000])
            ->assertSuccessful();

        $gang->refresh();
        $this->assertEquals(1000, $gang->bling_shops);
        // 500 excess * €50,000 = €25,000,000 refund
        $this->assertEquals(100000 + 25000000, $gang->treasury);
        $this->assertEquals(50000 + 25000000, $gang->protected_treasury);
    }
}
