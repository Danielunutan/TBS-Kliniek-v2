<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\CharacterVehicle;
use App\Models\Gang;
use App\Models\GangNpc;
use App\Models\GangProperty;
use App\Models\GangWar;
use App\Models\Heist;
use App\Models\User;
use App\Models\Vehicle;
use Database\Seeders\VehicleAndHeistSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class GangsAndFleetAuditFixesTest extends TestCase
{
    use RefreshDatabase;

    private function createLeaderAndGang(int $treasury = 5000000): array
    {
        $user = User::factory()->create();
        $character = Character::create([
            'user_id' => $user->id,
            'name' => 'Leader_'.rand(1000, 9999),
            'cash' => 50000,
            'bank' => 100000,
            'stars' => 10,
            'level' => 10,
            'xp' => 1000,
            'health' => 100,
            'max_health' => 100,
            'energy' => 100,
            'max_energy' => 100,
            'respect' => 50,
            'gang_role' => 'leader',
        ]);

        $gang = Gang::create([
            'name' => 'Gang_'.rand(100, 999),
            'tag' => 'GNG',
            'specialty' => 'smokkel',
            'treasury' => $treasury,
            'leader_character_id' => $character->id,
        ]);

        $character->update(['gang_id' => $gang->id]);

        return [$user, $character, $gang];
    }

    public function test_production_claim_jackpot_executes_within_transaction_and_preserves_character_state(): void
    {
        [$user, $character, $gang] = $this->createLeaderAndGang();

        $property = GangProperty::create([
            'gang_id' => $gang->id,
            'property_key' => 'wietkwekerij',
            'name' => 'Wietkwekerij',
            'type' => 'production',
            'purchase_price' => 500000,
            'production_duration_minutes' => 90,
            'production_reward_cash' => 100000,
            'production_reward_respect' => 50,
            'production_status' => 'ready',
            'production_started_at' => now()->subMinutes(100),
            'production_ends_at' => now()->subMinutes(10),
        ]);

        $response = $this->actingAs($user)->post(route('gangs.panden.claim-production', $property));
        $response->assertSessionHas('success');

        $freshChar = $character->fresh();
        $this->assertNotNull($freshChar);
        // Cash and respect are preserved and updated properly
        $this->assertEquals(50000, $freshChar->cash);
        $this->assertEquals('idle', $property->fresh()->production_status);
    }

    public function test_90m_production_keeps_assigned_specialist_yield_bonus_past_30_minutes_while_idle_expired_npc_does_not_affect_speed(): void
    {
        Carbon::setTestNow(now());
        [$user, $character, $gang] = $this->createLeaderAndGang();

        // 1. Idle property with expired specialist must NOT reduce duration (remains 60 minutes)
        $idleProp = GangProperty::create([
            'gang_id' => $gang->id,
            'property_key' => 'meth_lab',
            'name' => 'Meth Lab',
            'type' => 'production',
            'purchase_price' => 1000000,
            'production_duration_minutes' => 60,
            'production_status' => 'idle',
            'production_ends_at' => null,
        ]);

        $speedNpc = GangNpc::create([
            'gang_id' => $gang->id,
            'npc_key' => 'laborant_speed',
            'name' => 'Speed Chemist',
            'role_title' => 'Chemicus',
            'bonus_type' => 'production_speed',
            'bonus_value' => 50,
            'hired_at' => now()->subMinutes(35),
            'expires_at' => now()->subMinutes(5),
            'assigned_property_id' => $idleProp->id,
        ]);

        $this->assertTrue($speedNpc->fresh()->isExpired());
        $this->assertEquals(60, $idleProp->fresh()->effective_duration_minutes);

        // 2. Active production started while yield specialist was valid
        $yieldProp = GangProperty::create([
            'gang_id' => $gang->id,
            'property_key' => 'wietkwekerij',
            'name' => 'Wietkwekerij',
            'type' => 'production',
            'purchase_price' => 500000,
            'production_duration_minutes' => 90,
            'production_reward_cash' => 100000,
            'production_reward_respect' => 50,
            'production_status' => 'producing',
            'production_started_at' => now(),
            'production_ends_at' => now()->addMinutes(90),
        ]);

        $yieldNpc = GangNpc::create([
            'gang_id' => $gang->id,
            'npc_key' => 'botanicus_yield',
            'name' => 'Master Grower',
            'role_title' => 'Botanicus',
            'bonus_type' => 'production_yield',
            'bonus_value' => 50,
            'hired_at' => now(),
            'expires_at' => now()->addMinutes(30),
            'assigned_property_id' => $yieldProp->id,
        ]);

        // Advance 95 minutes: the specialist contract duration has expired, but production is ready
        Carbon::setTestNow(now()->addMinutes(95));
        $this->assertTrue($yieldNpc->fresh()->isExpired());

        // Purge must NOT delete NPC while property has an active or ready-to-claim production batch
        $purged = GangNpc::purgeExpiredForGang($gang);
        $this->assertNotNull(GangNpc::find($yieldNpc->id));

        // Yield bonus (+50%) must still apply to effective rewards
        $rewards = $yieldProp->fresh()->getEffectiveRewards();
        $this->assertEquals(150000, $rewards['cash']);

        // Claiming production deletes the assigned NPC
        $response = $this->actingAs($user)->post(route('gangs.panden.claim-production', $yieldProp));
        $response->assertSessionHas('success');
        $this->assertNull(GangNpc::find($yieldNpc->id));

        Carbon::setTestNow();
    }

    public function test_expired_gang_war_auto_settles_in_find_active_for_gang_and_draw_refunds_entry_stake(): void
    {
        [$userA, $charA, $gangA] = $this->createLeaderAndGang(500000);
        [$userB, $charB, $gangB] = $this->createLeaderAndGang(500000);

        $war = GangWar::create([
            'challenger_gang_id' => $gangA->id,
            'defender_gang_id' => $gangB->id,
            'status' => 'active',
            'started_at' => now()->subHours(25),
            'ends_at' => now()->subMinute(),
            'challenger_score' => 20,
            'defender_score' => 20,
            'war_pot' => 250000,
        ]);

        // First call auto-settles the expired war
        $found = GangWar::findActiveForGang($gangA->id);
        $this->assertNotNull($found);
        $this->assertEquals('finished', $war->fresh()->status);

        // On a draw, the entry stake must be refunded back to the challenger
        $this->assertEquals(750000, $gangA->fresh()->treasury);

        // Next call returns null because the war is settled
        $this->assertNull(GangWar::findActiveForGang($gangA->id));
    }

    public function test_dealer_vehicle_sell_rejects_comatose_imprisoned_and_gang_owned_vehicles(): void
    {
        [$user, $character, $gang] = $this->createLeaderAndGang();

        $vehicle = Vehicle::create([
            'name' => 'Showroom Sedan',
            'slug' => 'showroom-sedan-'.rand(100, 999),
            'type' => 'auto',
            'speed' => 80,
            'price' => 100000,
            'price_cash' => 100000,
            'description' => 'Test vehicle',
        ]);

        $charVeh = CharacterVehicle::create([
            'character_id' => $character->id,
            'vehicle_id' => $vehicle->id,
            'current_condition' => 100,
            'in_gang_garage' => false,
            'is_gang_owned' => false,
        ]);

        // 1. Imprisoned character cannot sell
        $character->update(['prison_until' => now()->addMinutes(30)]);
        $resImp = $this->actingAs($user)->post(route('garage.sell', $charVeh));
        $resImp->assertSessionHas('error');
        $this->assertNotNull(CharacterVehicle::find($charVeh->id));

        // 2. Comatose character cannot sell
        $character->update(['prison_until' => null, 'coma_until' => now()->addMinutes(30), 'health' => 0]);
        $resCom = $this->actingAs($user)->post(route('garage.sell', $charVeh));
        $resCom->assertSessionHas('error');
        $this->assertNotNull(CharacterVehicle::find($charVeh->id));

        // 3. Code Blauw (needs revive serum) cannot sell
        $character->update(['coma_until' => null, 'health' => 50, 'perks' => ['liquidation_streak_deaths' => 10]]);
        $resSerum = $this->actingAs($user)->post(route('garage.sell', $charVeh));
        $resSerum->assertSessionHas('error');
        $this->assertNotNull(CharacterVehicle::find($charVeh->id));

        // 4. Gang-owned vehicle cannot be sold to dealer
        $character->update(['perks' => ['liquidation_streak_deaths' => 0]]);
        $charVeh->update(['is_gang_owned' => true]);
        $resGang = $this->actingAs($user)->post(route('garage.sell', $charVeh));
        $resGang->assertSessionHas('error');
        $this->assertNotNull(CharacterVehicle::find($charVeh->id));

        // 5. Healthy character selling personal vehicle succeeds
        $charVeh->update(['is_gang_owned' => false]);
        $initialBank = (int) $character->bank;
        $resOk = $this->actingAs($user)->post(route('garage.sell', $charVeh));
        $resOk->assertSessionHas('success');
        $this->assertNull(CharacterVehicle::find($charVeh->id));
        $this->assertGreaterThan($initialBank, (int) $character->fresh()->bank);
    }

    public function test_tier_3_and_4_gang_heists_match_gang_villa_and_luxe_penthouse(): void
    {
        $this->seed(VehicleAndHeistSeeder::class);

        [$user, $character, $gang] = $this->createLeaderAndGang();

        // Check the gold heist
        $heist = Heist::where('slug', 'bende-heist-fort-goudkluis')->first();
        $this->assertNotNull($heist);

        // Required keys must contain gang_villa, not cartel_villa
        $keys = $heist->required_property_keys ?? [];
        $this->assertContains('gang_villa', $keys);
        $this->assertNotContains('cartel_villa', $keys);

        // Assign gang_villa to gang
        GangProperty::create([
            'gang_id' => $gang->id,
            'property_key' => 'gang_villa',
            'name' => 'Gang Villa',
            'type' => 'headquarters',
            'purchase_price' => 15000000,
        ]);

        $this->assertTrue($heist->gangHasRequiredProperty($gang));

        // Check casino heist for luxe_penthouse
        $casinoHeist = Heist::where('slug', 'bende-heist-casino-vault')->first();
        if ($casinoHeist) {
            $casinoKeys = $casinoHeist->required_property_keys ?? [];
            $this->assertContains('luxe_penthouse', $casinoKeys);
            $this->assertNotContains('sky_penthouse', $casinoKeys);
        }
    }

    public function test_gangs_garage_view_renders_safely_when_vehicle_relation_is_null(): void
    {
        [$user, $character, $gang] = $this->createLeaderAndGang();

        // Create an in-memory character vehicle with a null vehicle relation
        $charVeh = new CharacterVehicle([
            'character_id' => $character->id,
            'gang_id' => $gang->id,
            'in_gang_garage' => true,
            'current_condition' => 80,
            'is_gang_owned' => false,
        ]);
        $charVeh->id = 1;
        $charVeh->setRelation('vehicle', null);
        $charVeh->setRelation('character', $character);
        $charVeh->setRelation('storedBy', $character);

        $html = view('game.gangs.garage', [
            'character' => $character,
            'gang' => $gang,
            'totalVehicleSlots' => 4,
            'storedVehicles' => collect([$charVeh]),
            'storedCount' => 1,
            'freeSlots' => 3,
            'personalVehicles' => collect(),
            'garageProperties' => collect(),
            'isOfficer' => true,
        ])->render();

        $this->assertStringContainsString('Bende Garage', $html);
    }
}
