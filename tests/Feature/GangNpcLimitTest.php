<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\Gang;
use App\Models\GangNpc;
use App\Models\GangProperty;
use App\Models\User;
use App\Services\GangWageService;
use App\Support\GangPropertyCatalog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GangNpcLimitTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_officer_can_hire_npc_up_to_five_times_and_sixth_is_blocked(): void
    {
        $user = User::factory()->create();
        $character = Character::factory()->create([
            'user_id' => $user->id,
            'stars' => 100,
        ]);

        $gang = Gang::create([
            'name' => 'Limit Test Gang',
            'tag' => 'LTG',
            'leader_character_id' => $character->id,
            'treasury' => 100000000,
            'specialty' => 'violence',
            'description' => 'Testing 5-limit per NPC',
        ]);

        $character->update([
            'gang_id' => $gang->id,
            'gang_role' => 'leader',
        ]);

        // Provide 100 NPC capacity via Villa/Penthouse properties
        for ($i = 0; $i < 15; $i++) {
            GangProperty::create([
                'gang_id' => $gang->id,
                'property_key' => 'gang_villa_' . $i,
                'name' => 'Villa ' . $i,
                'type' => 'headquarters',
                'purchase_price' => 1000000,
                'npc_capacity' => 8,
                'storage_capacity' => 100,
                'vehicle_slots' => 5,
            ]);
        }

        $allNpcs = GangPropertyCatalog::npcs();
        $npcKey = array_key_first($allNpcs);
        $npcData = $allNpcs[$npcKey];
        $cost = (int) $npcData['cost'];

        $initialTreasury = $gang->treasury;

        $this->actingAs($user);

        // Hire 5 times
        for ($i = 1; $i <= 5; $i++) {
            $response = $this->post(route('gangs.panden.hire-npc', $npcKey));
            $response->assertSessionHas('success');
            $this->assertEquals($i, GangNpc::where('gang_id', $gang->id)->where('npc_key', $npcKey)->active()->count());
        }

        $gang->refresh();
        $this->assertEquals($initialTreasury - (5 * $cost), $gang->treasury);

        // 6th attempt must be rejected and not charge treasury
        $response6 = $this->post(route('gangs.panden.hire-npc', $npcKey));
        $response6->assertSessionHas('error');

        $gang->refresh();
        $this->assertEquals(5, GangNpc::where('gang_id', $gang->id)->where('npc_key', $npcKey)->active()->count());
        $this->assertEquals($initialTreasury - (5 * $cost), $gang->treasury);

        // Another NPC key can still be hired
        $keys = array_keys($allNpcs);
        $otherKey = $keys[1] ?? null;
        if ($otherKey) {
            $responseOther = $this->post(route('gangs.panden.hire-npc', $otherKey));
            $responseOther->assertSessionHas('success');
            $this->assertEquals(1, GangNpc::where('gang_id', $gang->id)->where('npc_key', $otherKey)->active()->count());
        }
    }

    public function test_wage_service_caps_identical_npc_wage_bonus_at_five(): void
    {
        $user = User::factory()->create();
        $character = Character::factory()->create([
            'user_id' => $user->id,
        ]);

        $gang = Gang::create([
            'name' => 'Wage Cap Gang',
            'tag' => 'WCG',
            'leader_character_id' => $character->id,
            'treasury' => 1000000,
            'specialty' => 'violence',
            'description' => 'Wage cap test',
        ]);

        $character->update([
            'gang_id' => $gang->id,
            'gang_role' => 'leader',
        ]);

        // Find an NPC with wage_bonus
        $wageNpc = null;
        foreach (GangPropertyCatalog::npcs() as $k => $def) {
            if (($def['bonus_type'] ?? '') === 'wage_bonus') {
                $wageNpc = $def;
                break;
            }
        }

        $this->assertNotNull($wageNpc);

        // Create 10 active instances directly in DB
        for ($i = 0; $i < 10; $i++) {
            GangNpc::create([
                'gang_id' => $gang->id,
                'npc_key' => $wageNpc['key'],
                'name' => $wageNpc['name'],
                'role_title' => $wageNpc['role_title'],
                'bonus_type' => 'wage_bonus',
                'bonus_value' => (int) $wageNpc['bonus_value'],
                'hired_at' => now(),
                'expires_at' => now()->addMinutes(30),
            ]);
        }

        $wageService = app(GangWageService::class);
        $breakdown = $wageService->getWageBreakdown($gang, $character);

        // Even though 10 instances exist in DB, only 5 should be counted in wage bonus
        $expectedWageBonusPct = 5 * (int) $wageNpc['bonus_value'];
        $this->assertEquals($expectedWageBonusPct, $breakdown['npc_bonus_pct']);
        $this->assertEquals(5 * 500, $breakdown['npcs_income']);
    }
}
