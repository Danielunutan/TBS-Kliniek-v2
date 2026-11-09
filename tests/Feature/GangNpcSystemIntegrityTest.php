<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\CharacterVehicle;
use App\Models\Gang;
use App\Models\GangNpc;
use App\Models\GangProperty;
use App\Models\User;
use App\Models\Vehicle;
use App\Services\GangWageService;
use App\Support\GangPropertyCatalog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Tests\TestCase;

class GangNpcSystemIntegrityTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Create a gang with a leader character and optional luxury NPC capacity.
     *
     * @return array{0: User, 1: Character, 2: Gang}
     */
    private function createGangWithLeader(int $treasury = 25000000, int $npcCapacity = 10): array
    {
        $user = User::factory()->create();
        $character = Character::factory()->create([
            'user_id' => $user->id,
            'cash' => 100000,
            'bank' => 250000,
            'level' => 15,
            'health' => 100,
            'max_health' => 100,
            'energy' => 100,
            'max_energy' => 100,
            'gang_role' => 'leader',
        ]);

        $gang = Gang::create([
            'name' => 'Integrity Cartel '.rand(100, 999),
            'tag' => 'INT',
            'specialty' => 'smokkel',
            'treasury' => $treasury,
            'leader_character_id' => $character->id,
        ]);

        $character->update(['gang_id' => $gang->id]);

        if ($npcCapacity > 2) {
            GangProperty::create([
                'gang_id' => $gang->id,
                'property_key' => 'gang_villa',
                'name' => 'Zwaarbeveiligd Landgoed & Gang Villa',
                'type' => 'luxury',
                'purchase_price' => 7500000,
                'npc_capacity' => $npcCapacity,
                'storage_capacity' => 50,
                'vehicle_slots' => 4,
            ]);
        }

        return [$user, $character, $gang];
    }

    public function test_vehicle_tuning_npc_discounts_gang_garage_repairs_and_enforces_cap_and_expiry(): void
    {
        Carbon::setTestNow(now());
        [$user, $character, $gang] = $this->createGangWithLeader(treasury: 10000000, npcCapacity: 10);

        $vehicle = Vehicle::create([
            'name' => 'Armored Interceptor',
            'slug' => 'armored-interceptor-'.Str::random(5),
            'type' => 'auto',
            'speed' => 90,
            'price' => 200000,
            'price_cash' => 200000,
            'description' => 'Fleet interceptor',
        ]);

        // Base condition at 50% yields €10,000 repair cost on a €200,000 vehicle.
        $gangVehicle = CharacterVehicle::create([
            'character_id' => $character->id,
            'gang_id' => $gang->id,
            'vehicle_id' => $vehicle->id,
            'in_gang_garage' => true,
            'current_condition' => 50,
        ]);

        $personalVehicle = CharacterVehicle::create([
            'character_id' => $character->id,
            'vehicle_id' => $vehicle->id,
            'in_gang_garage' => false,
            'current_condition' => 50,
        ]);

        $this->assertEquals(10000, $gangVehicle->repair_cost);
        $this->assertEquals(10000, $personalVehicle->repair_cost);

        // Hire a -40% vehicle_tuning specialist.
        GangNpc::create([
            'gang_id' => $gang->id,
            'npc_key' => 'mechanic_alpha',
            'name' => 'Klaus "De Lasser"',
            'role_title' => 'Fleet Specialist & Meestermonteur',
            'bonus_type' => 'vehicle_tuning',
            'bonus_value' => 40,
            'hired_at' => now(),
            'expires_at' => now()->addMinutes(30),
        ]);

        // Discount applies only to the gang garage vehicle.
        $this->assertEquals(6000, $gangVehicle->fresh()->repair_cost);
        $this->assertEquals(10000, $personalVehicle->fresh()->repair_cost);

        // Repair deducts €6,000 from the gang treasury.
        $repairRes = $this->actingAs($user)->post(route('garage.repair', $gangVehicle->id));
        $repairRes->assertSessionHas('success');
        $this->assertEquals(10000000 - 6000, $gang->fresh()->treasury);
        $this->assertEquals(100, $gangVehicle->fresh()->current_condition);

        // Damage the vehicle again to verify the 75% discount cap.
        $gangVehicle->refresh()->update(['current_condition' => 50]);
        GangNpc::create([
            'gang_id' => $gang->id,
            'npc_key' => 'mechanic_beta',
            'name' => 'Nico "Gasklep"',
            'role_title' => 'Fleet Specialist & Meestermonteur',
            'bonus_type' => 'vehicle_tuning',
            'bonus_value' => 50,
            'hired_at' => now(),
            'expires_at' => now()->addMinutes(30),
        ]);

        // Clamp combined 90% discount to the 75% limit (€2,500).
        $this->assertEquals(75, $gang->fresh()->npc_vehicle_tuning_percent);
        $this->assertEquals(2500, $gangVehicle->fresh()->repair_cost);

        // Block catalog hire of another vehicle_tuning NPC when the gang reaches the 75% cap.
        $catalogTuningNpc = collect(GangPropertyCatalog::npcs())->firstWhere('bonus_type', 'vehicle_tuning');
        $this->assertNotNull($catalogTuningNpc);
        $treasuryBeforeBlockedHire = $gang->fresh()->treasury;

        $blockedHire = $this->actingAs($user)->post(route('gangs.panden.hire-npc', $catalogTuningNpc['key']));
        $blockedHire->assertSessionHas('error');
        $this->assertEquals($treasuryBeforeBlockedHire, $gang->fresh()->treasury);

        // Advance time past contract expiration to restore the €10,000 repair cost.
        Carbon::setTestNow(now()->addMinutes(31));
        $this->assertEquals(0, $gang->fresh()->npc_vehicle_tuning_percent);
        $this->assertEquals(10000, $gangVehicle->fresh()->repair_cost);

        Carbon::setTestNow();
    }

    public function test_storage_boost_npc_expands_gang_depot_capacity_until_contract_expires(): void
    {
        Carbon::setTestNow(now());
        [$user, $character, $gang] = $this->createGangWithLeader(treasury: 10000000, npcCapacity: 10);
        $character->update(['masks' => 120]);

        // Base villa provides 50 storage slots, so a donation of 80 masks fails.
        $this->assertEquals(50, $gang->fresh()->total_storage_capacity);
        $failDonate = $this->actingAs($user)->post(route('gangs.donate-masks'), ['quantity' => 80]);
        $failDonate->assertSessionHas('error');
        $this->assertEquals(0, (int) $gang->fresh()->masks);

        // Active storage_boost NPC adds 60 slots for a total of 110 slots.
        GangNpc::create([
            'gang_id' => $gang->id,
            'npc_key' => 'havenmeester_1',
            'name' => 'Henk "De Kolos"',
            'role_title' => 'Logistiek Havenmeester',
            'bonus_type' => 'storage_boost',
            'bonus_value' => 60,
            'hired_at' => now(),
            'expires_at' => now()->addMinutes(30),
        ]);

        $this->assertEquals(110, $gang->fresh()->total_storage_capacity);

        // Donation of 80 masks now succeeds.
        $okDonate = $this->actingAs($user)->post(route('gangs.donate-masks'), ['quantity' => 80]);
        $okDonate->assertSessionHas('success');
        $this->assertEquals(80, (int) $gang->fresh()->masks);

        // Contract expiration restores 50 slots, preserves stored masks, and blocks new donations.
        Carbon::setTestNow(now()->addMinutes(31));
        $this->assertEquals(50, $gang->fresh()->total_storage_capacity);
        $this->assertEquals(80, (int) $gang->fresh()->masks);

        $afterExpiryDonate = $this->actingAs($user)->post(route('gangs.donate-masks'), ['quantity' => 5]);
        $afterExpiryDonate->assertSessionHas('error');
        $this->assertEquals(80, (int) $gang->fresh()->masks);

        Carbon::setTestNow();
    }

    public function test_production_npc_assignment_ui_guards_and_batch_specialist_collision_protection(): void
    {
        Carbon::setTestNow(now());
        [$user, $character, $gang] = $this->createGangWithLeader(treasury: 25000000, npcCapacity: 10);

        $lab = GangProperty::create([
            'gang_id' => $gang->id,
            'property_key' => 'chemisch_lab',
            'name' => 'Geheim Chemisch Smokkel-Lab',
            'type' => 'production',
            'purchase_price' => 1750000,
            'production_duration_minutes' => 100,
            'production_reward_cash' => 200000,
            'production_reward_respect' => 75,
            'production_status' => 'idle',
        ]);

        // Stationed showroom chemisch_specialist (-30%) yields 70m, not the -50% batch duration.
        $showroomChemist = GangNpc::create([
            'gang_id' => $gang->id,
            'npc_key' => 'chemisch_specialist',
            'name' => 'Dr. Walter V.',
            'role_title' => 'Synthese & Druk Meester',
            'bonus_type' => 'production_speed',
            'bonus_value' => 30,
            'hired_at' => now(),
            'expires_at' => now()->addMinutes(30),
        ]);

        $wageNpc = GangNpc::create([
            'gang_id' => $gang->id,
            'npc_key' => 'witwasser_test',
            'name' => 'Marco "De Boekhouder"',
            'role_title' => 'Financieel Adviseur & Witwasser',
            'bonus_type' => 'wage_bonus',
            'bonus_value' => 12,
            'hired_at' => now(),
            'expires_at' => now()->addMinutes(30),
        ]);

        // Panden page renders the assign-npc form and the -30% speed label.
        $pageRes = $this->actingAs($user)->get(route('gangs.panden'));
        $pageRes->assertOk();
        $pageRes->assertSee(route('gangs.panden.assign-npc', $lab), false);
        $pageRes->assertSee('-30% Productietijd', false);

        // Reject assignment of a non-production NPC to a production lab.
        $badAssign = $this->actingAs($user)->post(route('gangs.panden.assign-npc', $lab), [
            'npc_id' => $wageNpc->id,
        ]);
        $badAssign->assertSessionHas('error');
        $this->assertNull($wageNpc->fresh()->assigned_property_id);

        // Assign the active production_speed NPC to obtain a 70-minute cycle.
        $goodAssign = $this->actingAs($user)->post(route('gangs.panden.assign-npc', $lab), [
            'npc_id' => $showroomChemist->id,
        ]);
        $goodAssign->assertSessionHas('success');
        $this->assertEquals(70, $lab->fresh()->effective_duration_minutes);

        // Station a +25% yield NPC, then start production with the paid +100% batch manager.
        $yieldNpc = GangNpc::create([
            'gang_id' => $gang->id,
            'npc_key' => 'yield_specialist_25',
            'name' => 'Dimitri Jr.',
            'role_title' => 'Productie & Oogst Manager',
            'bonus_type' => 'production_yield',
            'bonus_value' => 25,
            'hired_at' => now(),
            'expires_at' => now()->addMinutes(30),
        ]);

        $this->actingAs($user)->post(route('gangs.panden.assign-npc', $lab), [
            'npc_id' => $yieldNpc->id,
        ])->assertSessionHas('success');

        $this->assertEquals(250000, $lab->fresh()->effective_reward_cash);

        // Paid batch manager (+100% -> €400,000) returns the stationed showroom NPC to the pool.
        $startRes = $this->actingAs($user)->post(route('gangs.panden.start-production', $lab), [
            'batch_specialist' => 'productie_manager',
        ]);
        $startRes->assertSessionHas('success');

        $this->assertNull($yieldNpc->fresh()->assigned_property_id);
        $this->assertEquals(400000, $lab->fresh()->effective_reward_cash);

        // Station a showroom productie_manager (+25%) on a second idle lab without clearing the first lab.
        $secondLab = GangProperty::create([
            'gang_id' => $gang->id,
            'property_key' => 'wietkwekerij',
            'name' => 'Ondergrondse Wietkwekerij',
            'type' => 'production',
            'purchase_price' => 450000,
            'production_duration_minutes' => 60,
            'production_reward_cash' => 100000,
            'production_reward_respect' => 30,
            'production_status' => 'idle',
        ]);

        $showroomManager = GangNpc::create([
            'gang_id' => $gang->id,
            'npc_key' => 'productie_manager',
            'name' => 'Dimitri "De Opzichter"',
            'role_title' => 'Fabrieks- & Logistiek Manager',
            'bonus_type' => 'production_yield',
            'bonus_value' => 25,
            'hired_at' => now(),
            'expires_at' => now()->addMinutes(30),
        ]);

        $this->actingAs($user)->post(route('gangs.panden.assign-npc', $secondLab), [
            'npc_id' => $showroomManager->id,
        ])->assertSessionHas('success');

        $this->assertEquals('productie_manager', $lab->fresh()->assigned_npc_key);
        $this->assertEquals(400000, $lab->fresh()->effective_reward_cash);
        $this->assertEquals(125000, $secondLab->fresh()->effective_reward_cash);

        // Expired showroom productie_manager on an idle lab yields base cash (€100,000), not +100%.
        Carbon::setTestNow(now()->addMinutes(31));
        $this->assertEquals(100000, $secondLab->fresh()->effective_reward_cash);

        Carbon::setTestNow();
    }

    public function test_wage_service_reads_model_wage_bonus_and_clamps_negative_values(): void
    {
        [$user, $character, $gang] = $this->createGangWithLeader();

        GangNpc::create([
            'gang_id' => $gang->id,
            'npc_key' => 'custom_launderer_key',
            'name' => 'Custom Launderer',
            'role_title' => 'Witwasser',
            'bonus_type' => 'wage_bonus',
            'bonus_value' => 15,
            'hired_at' => now(),
            'expires_at' => now()->addMinutes(30),
        ]);

        GangNpc::create([
            'gang_id' => $gang->id,
            'npc_key' => 'corrupted_negative_launderer',
            'name' => 'Corrupted Launderer',
            'role_title' => 'Witwasser',
            'bonus_type' => 'wage_bonus',
            'bonus_value' => -25,
            'hired_at' => now(),
            'expires_at' => now()->addMinutes(30),
        ]);

        $breakdown = app(GangWageService::class)->getWageBreakdown($gang->fresh(), $character);
        $this->assertEquals(15, $breakdown['npc_bonus_pct']);
    }
}
