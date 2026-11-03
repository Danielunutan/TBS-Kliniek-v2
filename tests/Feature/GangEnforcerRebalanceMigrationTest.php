<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\GameMessage;
use App\Models\GameNotification;
use App\Models\Gang;
use App\Models\GangNpc;
use App\Models\GangProperty;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GangEnforcerRebalanceMigrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_migration_caps_excess_enforcers_refunds_treasury_and_notifies_gang(): void
    {
        $user = User::factory()->create();
        $leader = Character::create([
            'user_id' => $user->id,
            'name' => 'BadLeader',
            'gender' => 'male',
            'level' => 20,
        ]);

        $gang = Gang::create([
            'name' => 'OverStackedGang',
            'tag' => 'OSG',
            'specialty' => 'Vleugel A',
            'leader_character_id' => $leader->id,
            'attack_power' => 50000,
            'protection_points' => 100000,
            'treasury' => 250000,
        ]);
        $leader->update(['gang_id' => $gang->id, 'gang_role' => 'leader']);

        // Create 6 Enforcers totaling 160% bonus (each costing 500,000)
        // 1: 30%, 2: 30%, 3: 25%, 4: 15% -> sum = 100%
        // 5: 30%, 6: 30% -> excess = 60%, 2 NPCs removed, 1,000,000 refunded
        GangNpc::create([
            'gang_id' => $gang->id,
            'npc_key' => 'enforcer_kapitein',
            'name' => 'Boris 1',
            'role_title' => 'Enforcer',
            'bonus_type' => 'defense',
            'bonus_value' => 30,
            'hired_at' => now()->subHours(5),
        ]);
        GangNpc::create([
            'gang_id' => $gang->id,
            'npc_key' => 'enforcer_kapitein',
            'name' => 'Boris 2',
            'role_title' => 'Enforcer',
            'bonus_type' => 'defense',
            'bonus_value' => 30,
            'hired_at' => now()->subHours(4),
        ]);
        GangNpc::create([
            'gang_id' => $gang->id,
            'npc_key' => 'enforcer_kapitein',
            'name' => 'Boris 3',
            'role_title' => 'Enforcer',
            'bonus_type' => 'defense',
            'bonus_value' => 25,
            'hired_at' => now()->subHours(3),
        ]);
        GangNpc::create([
            'gang_id' => $gang->id,
            'npc_key' => 'enforcer_kapitein',
            'name' => 'Boris 4',
            'role_title' => 'Enforcer',
            'bonus_type' => 'defense',
            'bonus_value' => 15,
            'hired_at' => now()->subHours(2),
        ]);
        GangNpc::create([
            'gang_id' => $gang->id,
            'npc_key' => 'enforcer_kapitein',
            'name' => 'Boris 5 (Excess)',
            'role_title' => 'Enforcer',
            'bonus_type' => 'defense',
            'bonus_value' => 30,
            'hired_at' => now()->subHour(),
        ]);
        GangNpc::create([
            'gang_id' => $gang->id,
            'npc_key' => 'enforcer_kapitein',
            'name' => 'Boris 6 (Excess)',
            'role_title' => 'Enforcer',
            'bonus_type' => 'defense',
            'bonus_value' => 30,
            'hired_at' => now(),
        ]);

        $this->assertEquals(6, $gang->npcs()->count());
        $this->assertEquals(160, $gang->npcs()->where('bonus_type', 'defense')->sum('bonus_value'));

        // Run the migration logic
        $migration = require database_path('migrations/2026_11_03_133000_rebalance_gang_enforcers_and_refund_excess.php');
        $migration->up();

        $gang->refresh();

        // Kept exactly 4 Enforcers summing to 100%
        $remainingEnforcers = $gang->npcs()->where('bonus_type', 'defense')->get();
        $this->assertCount(4, $remainingEnforcers);
        $this->assertEquals(100, $remainingEnforcers->sum('bonus_value'));
        $this->assertEquals(100, $gang->npc_defense_bonus_percent);
        $this->assertEquals(200000, $gang->total_defense); // 100k base + 100% = 200k

        // 2 excess enforcers @ 500k each = 1,000,000 refunded
        // Original 250,000 + 1,000,000 = 1,250,000
        $this->assertEquals(1250000, $gang->treasury);

        // Verify leader received GameMessage
        $message = GameMessage::where('recipient_id', $leader->id)->latest('id')->first();
        $this->assertNotNull($message);
        $this->assertStringContainsString('Enforcer Limiet & Bende Kas Compensatie', $message->subject);
        $this->assertStringContainsString('€1.000.000', $message->content);
        $this->assertStringContainsString('+100%', $message->content);

        // Verify officer notification
        $notification = GameNotification::where('character_id', $leader->id)->latest('id')->first();
        $this->assertNotNull($notification);
        $this->assertStringContainsString('€1.000.000 Teruggestort', $notification->title);
    }

    public function test_hiring_enforcer_npc_is_blocked_when_gang_is_already_at_100_percent_cap(): void
    {
        $user = User::factory()->create();
        $leader = Character::create([
            'user_id' => $user->id,
            'name' => 'CapLeader',
            'gender' => 'male',
            'level' => 25,
        ]);

        $gang = Gang::create([
            'name' => 'CappedEnforcerGang',
            'tag' => 'CEG',
            'specialty' => 'Vleugel A',
            'leader_character_id' => $leader->id,
            'attack_power' => 100000,
            'protection_points' => 100000,
            'treasury' => 5000000,
        ]);
        $leader->update(['gang_id' => $gang->id, 'gang_role' => 'leader']);

        GangProperty::create([
            'gang_id' => $gang->id,
            'property_key' => 'bende_villa',
            'name' => 'Bende Villa',
            'type' => 'luxury',
            'purchase_price' => 1000000,
            'level' => 1,
            'npc_capacity' => 10,
            'storage_capacity' => 0,
            'vehicle_slots' => 0,
        ]);

        GangNpc::create([
            'gang_id' => $gang->id,
            'npc_key' => 'enforcer_kapitein',
            'name' => 'Boris Max',
            'role_title' => 'Enforcer',
            'bonus_type' => 'defense',
            'bonus_value' => 100,
            'hired_at' => now()->subHour(),
        ]);

        $response = $this->actingAs($user)->post(route('gangs.panden.hire-npc', 'enforcer_kapitein'));

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertStringContainsString(
            'maximale Enforcer-verdedigingslimiet van +100%',
            (string) session('error')
        );

        $gang->refresh();
        $this->assertEquals(5000000, $gang->treasury);
        $this->assertEquals(1, $gang->npcs()->count());
    }

    public function test_victory_against_gang_with_enforcers_reduces_base_protection_proportionally(): void
    {
        $attackerUser = User::factory()->create();
        $attacker = Character::create([
            'user_id' => $attackerUser->id,
            'name' => 'AttackerBoss',
            'gender' => 'male',
            'level' => 30,
        ]);

        $defenderUser = User::factory()->create();
        $defender = Character::create([
            'user_id' => $defenderUser->id,
            'name' => 'DefenderBoss',
            'gender' => 'male',
            'level' => 30,
        ]);

        $attackerGang = Gang::create([
            'name' => 'StrongAttackers',
            'tag' => 'ATK',
            'specialty' => 'Vleugel A',
            'leader_character_id' => $attacker->id,
            'attack_power' => 10000000,
            'protection_points' => 100000,
            'treasury' => 100000,
        ]);
        $attacker->update(['gang_id' => $attackerGang->id, 'gang_role' => 'leader']);

        $defenderGang = Gang::create([
            'name' => 'EnforcedDefenders',
            'tag' => 'DEF',
            'specialty' => 'Vleugel B',
            'leader_character_id' => $defender->id,
            'attack_power' => 50000,
            'protection_points' => 100000,
            'treasury' => 100000,
        ]);
        $defender->update(['gang_id' => $defenderGang->id, 'gang_role' => 'leader']);

        GangNpc::create([
            'gang_id' => $defenderGang->id,
            'npc_key' => 'enforcer_kapitein',
            'name' => 'Boris Guard',
            'role_title' => 'Enforcer',
            'bonus_type' => 'defense',
            'bonus_value' => 100,
            'hired_at' => now()->subHour(),
        ]);

        $this->assertEquals(200000, $defenderGang->fresh()->total_defense);

        $response = $this->actingAs($attackerUser)->post(route('gangs.attack'), [
            'target_gang_id' => $defenderGang->id,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $defenderGang->refresh();
        // 10% to 20% of 100,000 base protection_points = 10,000 to 20,000 lost (remaining: 80,000 to 90,000)
        $this->assertGreaterThanOrEqual(80000, $defenderGang->protection_points);
        $this->assertLessThanOrEqual(90000, $defenderGang->protection_points);
    }

    public function test_rebalance_normalizes_uneven_procedural_enforcers_to_exact_100_percent(): void
    {
        $user = User::factory()->create();
        $leader = Character::create([
            'user_id' => $user->id,
            'name' => 'ProceduralLeader',
            'gender' => 'male',
            'level' => 25,
        ]);

        $gang = Gang::create([
            'name' => 'ProceduralEnforcersGang',
            'tag' => 'PEG',
            'specialty' => 'Vleugel A',
            'leader_character_id' => $leader->id,
            'attack_power' => 100000,
            'protection_points' => 100000,
            'treasury' => 100000,
        ]);
        $leader->update(['gang_id' => $gang->id, 'gang_role' => 'leader']);

        // Create procedural Enforcers: 35% + 35% + 22% (= 92%) + 30% + 30% (= 152% total)
        foreach ([35, 35, 22, 30, 30] as $idx => $bonus) {
            GangNpc::create([
                'gang_id' => $gang->id,
                'npc_key' => 'enforcer_kapitein',
                'name' => 'Procedural Boris '.($idx + 1),
                'role_title' => 'Enforcer',
                'bonus_type' => 'defense',
                'bonus_value' => $bonus,
                'hired_at' => now()->subHours(5 - $idx),
            ]);
        }

        $migration = require database_path('migrations/2026_11_03_133000_rebalance_gang_enforcers_and_refund_excess.php');
        $migration->up();

        $gang->refresh();
        $this->assertCount(3, $gang->npcs()->where('bonus_type', 'defense')->get());
        $this->assertEquals(100, (int) $gang->npcs()->where('bonus_type', 'defense')->sum('bonus_value'));
        $this->assertEquals(100, $gang->npc_defense_bonus_percent);
        $this->assertEquals(1100000, $gang->treasury); // 100k + 2 * 500k refunded
    }
}
