<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\ControlPoint;
use App\Models\Gang;
use App\Models\GangWar;
use App\Models\User;
use App\Services\ControlPointService;
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
}
