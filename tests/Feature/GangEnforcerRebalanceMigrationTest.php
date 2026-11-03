<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\GameMessage;
use App\Models\GameNotification;
use App\Models\Gang;
use App\Models\GangNpc;
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
}
