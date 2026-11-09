<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\Gang;
use App\Models\GangNpc;
use App\Models\GangProperty;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class GangPropertiesTest extends TestCase
{
    use RefreshDatabase;

    private function createGangWithOfficer(int $treasury = 5000000): array
    {
        $user = User::factory()->create();
        $character = Character::create([
            'user_id' => $user->id,
            'name' => 'Boss'.rand(1000, 9999),
            'cash' => 50000,
            'bank' => 100000,
            'stars' => 10,
            'level' => 5,
            'xp' => 500,
            'health' => 100,
            'max_health' => 100,
            'energy' => 100,
            'max_energy' => 100,
            'respect' => 50,
            'gang_role' => 'leader',
        ]);

        $gang = Gang::create([
            'name' => 'TBS Cartel '.rand(100, 999),
            'tag' => 'TBSC',
            'specialty' => 'smokkel',
            'treasury' => $treasury,
            'leader_character_id' => $character->id,
        ]);

        $character->update(['gang_id' => $gang->id]);

        return [$user, $character, $gang];
    }

    public function test_gang_member_can_view_panden_page(): void
    {
        [$user, $character, $gang] = $this->createGangWithOfficer();

        $response = $this->actingAs($user)->get(route('gangs.panden'));

        $response->assertOk();
        $response->assertSee('Bende Panden & Vastgoed', false);
        $response->assertSee('Kleine Opslagloods & Garage');
        $response->assertSee('Ondergrondse Valsgeld Drukkerij');
        $response->assertSee('Zwaarbeveiligd Landgoed & Gang Villa');
        $response->assertSee('NPC Bendeleden', false);
    }

    public function test_gang_officer_can_buy_property_with_treasury(): void
    {
        [$user, $character, $gang] = $this->createGangWithOfficer(1000000);

        // Buy kleine_loods (€150.000)
        $response = $this->actingAs($user)
            ->post(route('gangs.panden.buy', 'kleine_loods'));

        $response->assertSessionHas('success');
        $gang->refresh();
        $this->assertEquals(850000, $gang->treasury);

        $property = GangProperty::where('gang_id', $gang->id)->where('property_key', 'kleine_loods')->first();
        $this->assertNotNull($property);
        $this->assertEquals('storage', $property->type);
        $this->assertEquals(50, $property->storage_capacity);
        $this->assertEquals(2, $property->vehicle_slots);

        // Gang total storage and vehicles reflect property
        $this->assertEquals(50, $gang->total_storage_capacity);
        $this->assertEquals(2, $gang->total_vehicle_slots);
    }

    public function test_gang_officer_cannot_buy_property_without_enough_treasury(): void
    {
        [$user, $character, $gang] = $this->createGangWithOfficer(50000); // Only €50k

        $response = $this->actingAs($user)
            ->post(route('gangs.panden.buy', 'kleine_loods')); // costs €150k

        $response->assertSessionHas('error');
        $this->assertEquals(50000, $gang->fresh()->treasury);
        $this->assertFalse(GangProperty::where('gang_id', $gang->id)->exists());
    }

    public function test_production_facility_lifecycle_and_claim(): void
    {
        Carbon::setTestNow(now());
        [$user, $character, $gang] = $this->createGangWithOfficer(2000000);

        // 1. Buy printing facility (€750.000)
        $this->actingAs($user)->post(route('gangs.panden.buy', 'valsgeld_drukkerij'));
        $property = GangProperty::where('gang_id', $gang->id)->where('property_key', 'valsgeld_drukkerij')->first();
        $this->assertNotNull($property);

        // 2. Start production (duration 120 minutes)
        $startRes = $this->actingAs($user)->post(route('gangs.panden.start-production', $property->id));
        $startRes->assertSessionHas('success');

        $property->refresh();
        $this->assertEquals('producing', $property->production_status);
        $this->assertNotNull($property->production_ends_at);
        $this->assertFalse($property->isReadyToClaim());

        // 3. Fast-forward 125 minutes -> ready to claim
        Carbon::setTestNow(now()->addMinutes(125));
        $this->assertTrue($property->isReadyToClaim());

        $initialTreasury = $gang->fresh()->treasury;
        $initialRespect = (int) $character->fresh()->respect;

        // 4. Claim production rewards
        $claimRes = $this->actingAs($user)->post(route('gangs.panden.claim-production', $property->id));
        $claimRes->assertSessionHas('success');

        $property->refresh();
        $this->assertEquals('idle', $property->production_status);
        $this->assertNull($property->production_ends_at);

        // Treasury increased by €90.000 and claiming character respect increased by 35
        $this->assertEquals($initialTreasury + 90000, $gang->fresh()->treasury);
        $this->assertEquals($initialRespect + 35, (int) $character->fresh()->respect);

        Carbon::setTestNow();
    }

    public function test_gang_officer_can_hire_npc_and_assign_to_factory_for_cash_bonus(): void
    {
        [$user, $character, $gang] = $this->createGangWithOfficer(5000000);

        // 1. Fill base capacity (2 slots) so hiring beyond base capacity requires penthouse/villa
        $this->actingAs($user)->post(route('gangs.panden.hire-npc', 'productie_manager'));
        $this->actingAs($user)->post(route('gangs.panden.hire-npc', 'chemisch_specialist'));

        // Attempt to hire 3rd NPC before owning villa/penthouse -> must fail
        $failHire = $this->actingAs($user)->post(route('gangs.panden.hire-npc', 'beveiligings_expert'));
        $failHire->assertSessionHas('error');

        // 2. Buy Penthouse (gives 4 NPC slots)
        $this->actingAs($user)->post(route('gangs.panden.buy', 'luxe_penthouse'));
        $gang->refresh();
        $this->assertEquals(4, $gang->total_npc_capacity);

        // 3. Hire NPC 'productie_manager' (cost €250.000, +25% cash bonus)
        $hireRes = $this->actingAs($user)->post(route('gangs.panden.hire-npc', 'productie_manager'));
        $hireRes->assertSessionHas('success');

        $npc = GangNpc::where('gang_id', $gang->id)->where('npc_key', 'productie_manager')->first();
        $this->assertNotNull($npc);

        // 4. Buy printing facility
        $this->actingAs($user)->post(route('gangs.panden.buy', 'valsgeld_drukkerij'));
        $property = GangProperty::where('gang_id', $gang->id)->where('property_key', 'valsgeld_drukkerij')->first();

        // Base reward is €90.000
        $this->assertEquals(90000, $property->getEffectiveRewards()['cash']);

        // 5. Assign NPC to printing facility
        $assignRes = $this->actingAs($user)->post(route('gangs.panden.assign-npc', $property->id), [
            'npc_id' => $npc->id,
        ]);
        $assignRes->assertSessionHas('success');

        // Effective cash reward now has +25% bonus (€90.000 * 1.25 = €112.500)
        $this->assertEquals(112500, $property->fresh()->getEffectiveRewards()['cash']);
    }

    public function test_hired_npc_has_30_minute_expiration_and_is_purged(): void
    {
        Carbon::setTestNow(now());
        [$user, $character, $gang] = $this->createGangWithOfficer(5000000);

        // Buy Penthouse (gives 4 NPC slots)
        $this->actingAs($user)->post(route('gangs.panden.buy', 'luxe_penthouse'));

        // Hire NPC 'enforcer_kapitein'
        $hireRes = $this->actingAs($user)->post(route('gangs.panden.hire-npc', 'enforcer_kapitein'));
        $hireRes->assertSessionHas('success');

        $npc = GangNpc::where('gang_id', $gang->id)->where('npc_key', 'enforcer_kapitein')->first();
        $this->assertNotNull($npc);
        $this->assertNotNull($npc->expires_at);
        $this->assertEquals(30, (int) round(now()->diffInMinutes($npc->expires_at)));
        $this->assertTrue($npc->isActive());

        // Fast-forward 31 minutes
        Carbon::setTestNow(now()->addMinutes(31));

        $this->assertTrue($npc->fresh()->isExpired());

        // Visiting panden page automatically purges expired NPCs
        $res = $this->actingAs($user)->get(route('gangs.panden'));
        $res->assertOk();

        $this->assertDatabaseMissing('gang_npcs', ['id' => $npc->id]);

        Carbon::setTestNow();
    }

    public function test_expired_npc_does_not_contribute_to_defense_bonus(): void
    {
        Carbon::setTestNow(now());
        [$user, $character, $gang] = $this->createGangWithOfficer(5000000);

        $npc = GangNpc::create([
            'gang_id' => $gang->id,
            'npc_key' => 'enforcer_kapitein',
            'name' => 'Boris',
            'role_title' => 'Enforcer',
            'bonus_type' => 'defense',
            'bonus_value' => 25,
            'hired_at' => now(),
            'expires_at' => now()->addMinutes(30),
        ]);

        $this->assertEquals(25, $gang->fresh()->npc_defense_bonus_percent);

        // Fast-forward past 30 minutes
        Carbon::setTestNow(now()->addMinutes(35));

        $this->assertEquals(0, $gang->fresh()->npc_defense_bonus_percent);

        Carbon::setTestNow();
    }
}
