<?php

namespace Tests\Feature;

use App\Models\BloodFeud;
use App\Models\Character;
use App\Models\Gang;
use App\Models\GangAttack;
use App\Models\GangWar;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GangRetaliationTest extends TestCase
{
    use RefreshDatabase;

    public function test_gang_leader_can_retaliate_against_enemy_who_attacked_gang_member_despite_level_difference(): void
    {
        $myGang = Gang::create([
            'name' => 'Syndicate A',
            'tag' => 'SYNA',
            'specialty' => 'violence',
            'description' => 'Gang A',
            'treasury' => 100000,
        ]);

        $leaderUser = User::factory()->create();
        $leader = Character::factory()->create([
            'user_id' => $leaderUser->id,
            'name' => 'BossieArjan',
            'gang_id' => $myGang->id,
            'gang_role' => 'leader',
            'level' => 60,
            'strength' => 500,
            'bullets' => 200,
            'energy' => 100,
            'current_location' => 'Paviljoen 1 (Gesloten)',
        ]);

        $victim = Character::factory()->create([
            'name' => 'LowMember',
            'gang_id' => $myGang->id,
            'gang_role' => 'member',
            'level' => 10,
            'current_location' => 'Paviljoen 1 (Gesloten)',
        ]);

        $enemyUser = User::factory()->create();
        $enemy = Character::factory()->create([
            'user_id' => $enemyUser->id,
            'name' => 'EnemyRival',
            'gang_id' => null,
            'level' => 12,
            'defense' => 10,
            'health' => 100,
            'current_location' => 'Paviljoen 1 (Gesloten)',
        ]);

        // Level difference between leader (60) and enemy (12) is 48 (> 15).
        // Before retaliation rights, leader is blocked from shooting enemy:
        $this->assertFalse(BloodFeud::canRetaliate($leader, $enemy));

        $resBlocked = $this->actingAs($leaderUser)->post(route('hitman.shoot'), [
            'target_id' => $enemy->id,
            'bullets' => 10,
        ]);
        $resBlocked->assertSessionHas('error');
        $this->assertStringContainsString('levelverschil', session('error'));

        // Now enemy attacks or liquidates our gang member (LowMember)
        BloodFeud::create([
            'attacker_id' => $enemy->id,
            'victim_id' => $victim->id,
            'victim_gang_id' => $myGang->id,
            'expires_at' => now()->addDays(7),
            'is_avenged' => false,
        ]);

        // Retaliation right is immediately active for ANY member/leader of Syndicate A!
        $this->assertTrue(BloodFeud::canRetaliate($leader, $enemy));

        // Leader can now shoot enemy despite 48-level difference!
        $resAllowed = $this->actingAs($leaderUser)->post(route('hitman.shoot'), [
            'target_id' => $enemy->id,
            'bullets' => 20,
        ]);
        $resAllowed->assertSessionHas('success');
    }

    public function test_gang_members_bypass_level_limits_during_active_gang_war(): void
    {
        $gangA = Gang::create([
            'name' => 'Warriors A',
            'tag' => 'WARA',
            'specialty' => 'violence',
            'treasury' => 100000,
        ]);

        $gangB = Gang::create([
            'name' => 'Warriors B',
            'tag' => 'WARB',
            'specialty' => 'violence',
            'treasury' => 100000,
        ]);

        $war = GangWar::create([
            'challenger_gang_id' => $gangA->id,
            'defender_gang_id' => $gangB->id,
            'status' => 'active',
            'started_at' => now(),
            'ends_at' => now()->addHours(24),
            'zone' => 'Luchtplaats',
        ]);
        $this->assertNotNull($war);

        $memberAUser = User::factory()->create();
        $memberA = Character::factory()->create([
            'user_id' => $memberAUser->id,
            'gang_id' => $gangA->id,
            'level' => 50,
            'bullets' => 100,
            'energy' => 100,
            'current_location' => 'Paviljoen 1 (Gesloten)',
        ]);

        $memberB = Character::factory()->create([
            'gang_id' => $gangB->id,
            'level' => 10,
            'defense' => 10,
            'health' => 100,
            'current_location' => 'Paviljoen 1 (Gesloten)',
        ]);

        // Level difference is 40, but active 24h war grants full combat rights!
        $this->assertTrue(BloodFeud::canRetaliate($memberA, $memberB));

        $res = $this->actingAs($memberAUser)->post(route('hitman.shoot'), [
            'target_id' => $memberB->id,
            'bullets' => 15,
        ]);
        $res->assertSessionHas('success');
    }

    public function test_radio_feature_removed_and_redirects(): void
    {
        $user = User::factory()->create();
        $character = Character::factory()->create(['user_id' => $user->id]);

        $res = $this->actingAs($user)->get(route('radio.index'));
        $res->assertRedirect(route('dashboard'));

        $dashRes = $this->actingAs($user)->get(route('dashboard'));
        $dashRes->assertOk();
        $dashRes->assertSee('MIXXR Radio', false);
    }
}
