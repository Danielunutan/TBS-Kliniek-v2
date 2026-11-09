<?php

namespace Tests\Feature;

use App\Models\BloodFeud;
use App\Models\Character;
use App\Models\Gang;
use App\Models\GangWar;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GangRetaliationTest extends TestCase
{
    use RefreshDatabase;

    public function test_gang_leader_can_retaliate_once_per_hour_outside_15_levels(): void
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
        $resBlocked = $this->actingAs($leaderUser)->post(route('hitman.shoot'), [
            'target_id' => $enemy->id,
            'bullets' => 10,
        ]);
        $resBlocked->assertSessionHas('error');
        $this->assertStringContainsString('levelverschil', session('error'));

        // Enemy attacks or liquidates our gang member (LowMember) -> creates Bloedwraak!
        BloodFeud::create([
            'attacker_id' => $enemy->id,
            'victim_id' => $victim->id,
            'victim_gang_id' => $myGang->id,
            'expires_at' => now()->addDays(7),
            'is_avenged' => false,
        ]);

        // First retaliation hit is allowed!
        $resAllowed = $this->actingAs($leaderUser)->post(route('hitman.shoot'), [
            'target_id' => $enemy->id,
            'bullets' => 20,
        ]);
        $resAllowed->assertSessionHas('success');

        // Reset defender coma status for test purposes
        $enemy->refresh();
        $enemy->coma_until = null;
        $enemy->protection_until = null;
        $enemy->save();

        // Second hit within 1 hour is BLOCKED by Dennis 1-hour retaliation slot!
        $resSecond = $this->actingAs($leaderUser)->post(route('hitman.shoot'), [
            'target_id' => $enemy->id,
            'bullets' => 20,
        ]);
        $resSecond->assertSessionHas('error');
        $this->assertStringContainsString('Vergeldingsslot', session('error'));
    }

    public function test_combat_isolation_cooldown_blocks_buying_isolation_after_attack(): void
    {
        $attackerUser = User::factory()->create();
        $attacker = Character::factory()->create([
            'user_id' => $attackerUser->id,
            'cash' => 500000,
            'level' => 20,
            'bullets' => 50,
            'energy' => 100,
            'current_location' => 'Paviljoen 1 (Gesloten)',
        ]);

        $defender = Character::factory()->create([
            'level' => 20,
            'current_location' => 'Paviljoen 1 (Gesloten)',
        ]);

        $res = $this->actingAs($attackerUser)->post(route('hitman.shoot'), [
            'target_id' => $defender->id,
            'bullets' => 10,
        ]);

        $attacker->refresh();
        $this->assertTrue($attacker->isOnCooldown('combat_isolation_cooldown'));

        // Attacker attempts to buy voluntary isolation to hide behind shield
        $resIso = $this->actingAs($attackerUser)->post(route('isolation.activate'), [
            'hours' => 1,
            'currency' => 'cash',
        ]);
        $resIso->assertSessionHas('error');
        $this->assertStringContainsString('Gevechtsstatus', session('error'));
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
        $dashRes->assertDontSee('Radio Kliniek FM', false);
    }
}
