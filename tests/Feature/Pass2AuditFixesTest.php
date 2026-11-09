<?php

namespace Tests\Feature;

use App\Http\Controllers\FaqController;
use App\Models\Character;
use App\Models\Gang;
use App\Models\GangNpc;
use App\Models\GangProperty;
use App\Models\User;
use App\Services\GangWageService;
use App\Support\GangPropertyCatalog;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Pass2AuditFixesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    private function createLeaderAndGang(int $treasury = 50000000): array
    {
        $user = User::factory()->create();
        $character = Character::factory()->create([
            'user_id' => $user->id,
            'level' => 25,
            'energy' => 100,
            'max_energy' => 100,
            'is_in_prison' => false,
            'coma_until' => null,
        ]);

        $gang = Gang::create([
            'name' => 'Syndicaat Pass2',
            'tag' => 'P2S',
            'leader_character_id' => $character->id,
            'treasury' => $treasury,
            'protected_treasury' => 0,
            'specialty' => 'violence',
            'description' => 'Pass 2 fixes test gang',
        ]);

        $character->update([
            'gang_id' => $gang->id,
            'gang_role' => 'leader',
        ]);

        // Provide NPC capacity
        GangProperty::create([
            'gang_id' => $gang->id,
            'property_key' => 'luxe_penthouse',
            'name' => 'Luxe Penthouse',
            'type' => 'headquarters',
            'cost' => 10000000,
            'purchase_price' => 10000000,
            'npc_capacity' => 10,
            'storage_capacity' => 100,
            'vehicle_slots' => 5,
            'purchased_at' => now(),
        ]);

        return [$user, $character, $gang];
    }

    public function test_expired_npc_does_not_affect_production_speed(): void
    {
        Carbon::setTestNow(now());
        [$user, $character, $gang] = $this->createLeaderAndGang();

        $property = GangProperty::create([
            'gang_id' => $gang->id,
            'property_key' => 'meth_lab',
            'name' => 'Meth Lab',
            'type' => 'production',
            'cost' => 1000000,
            'purchase_price' => 1000000,
            'production_duration_minutes' => 60,
            'purchased_at' => now(),
        ]);

        $npc = GangNpc::create([
            'gang_id' => $gang->id,
            'npc_key' => 'laborant_walter',
            'name' => 'Dr. Walter',
            'role_title' => 'Chemicus',
            'bonus_type' => 'production_speed',
            'bonus_value' => 50,
            'hired_at' => now(),
            'expires_at' => now()->addMinutes(30),
            'assigned_property_id' => $property->id,
        ]);

        // Active NPC cuts duration in half: 60 * (1 - 0.50) = 30 minutes
        $this->assertEquals(30, $property->fresh()->effective_duration_minutes);

        // Fast-forward past 30 minutes so NPC expires
        Carbon::setTestNow(now()->addMinutes(35));
        $this->assertTrue($npc->fresh()->isExpired());

        // Expired NPC must not reduce duration; base duration 60 minutes must return
        $this->assertEquals(60, $property->fresh()->effective_duration_minutes);

        Carbon::setTestNow();
    }

    public function test_gang_wage_breakdown_reports_only_contributing_npcs_count(): void
    {
        Carbon::setTestNow(now());
        [$user, $character, $gang] = $this->createLeaderAndGang();

        $wageNpc = collect(GangPropertyCatalog::npcs())->firstWhere('bonus_type', 'wage_bonus');
        $this->assertNotNull($wageNpc);
        $npcKey = $wageNpc['key'];
        $val = (int) $wageNpc['bonus_value'];

        // Create 2 active wage bonus NPCs
        for ($i = 0; $i < 2; $i++) {
            GangNpc::create([
                'gang_id' => $gang->id,
                'npc_key' => $npcKey,
                'name' => $wageNpc['name'].' '.$i,
                'role_title' => $wageNpc['role_title'],
                'bonus_type' => 'wage_bonus',
                'bonus_value' => $val,
                'hired_at' => now(),
                'expires_at' => now()->addMinutes(30),
            ]);
        }

        // Create 1 expired wage bonus NPC
        GangNpc::create([
            'gang_id' => $gang->id,
            'npc_key' => $npcKey,
            'name' => $wageNpc['name'].' Expired',
            'role_title' => $wageNpc['role_title'],
            'bonus_type' => 'wage_bonus',
            'bonus_value' => $val,
            'hired_at' => now()->subMinutes(60),
            'expires_at' => now()->subMinutes(30),
        ]);

        $wageService = app(GangWageService::class);
        $breakdown = $wageService->getWageBreakdown($gang, $character);

        // Contributing count must be 2, not 3
        $this->assertEquals(2, $breakdown['npcs_count']);
        $this->assertEquals(1000, $breakdown['npcs_income']); // 2 * €500
        $this->assertEquals($val * 2, $breakdown['npc_bonus_pct']); // 2 * bonus_value

        Carbon::setTestNow();
    }

    public function test_uurloon_page_purges_expired_npcs_for_gang(): void
    {
        Carbon::setTestNow(now());
        [$user, $character, $gang] = $this->createLeaderAndGang();

        $expiredNpc = GangNpc::create([
            'gang_id' => $gang->id,
            'npc_key' => 'witwasser_marcel',
            'name' => 'Stale Marcel',
            'role_title' => 'Witwasser',
            'bonus_type' => 'wage_bonus',
            'bonus_value' => 10,
            'hired_at' => now()->subMinutes(40),
            'expires_at' => now()->subMinutes(10),
        ]);

        $response = $this->actingAs($user)->get(route('gangs.uurloon'));
        $response->assertOk();

        // The stale NPC must have been purged from database
        $this->assertDatabaseMissing('gang_npcs', ['id' => $expiredNpc->id]);

        Carbon::setTestNow();
    }

    public function test_npc_defense_bonus_ignores_expired_npcs_when_relation_not_loaded(): void
    {
        Carbon::setTestNow(now());
        [$user, $character, $gang] = $this->createLeaderAndGang();

        GangNpc::create([
            'gang_id' => $gang->id,
            'npc_key' => 'enforcer_kapitein',
            'name' => 'Boris Stale',
            'role_title' => 'Enforcer',
            'bonus_type' => 'defense',
            'bonus_value' => 20,
            'hired_at' => now()->subMinutes(50),
            'expires_at' => now()->subMinutes(20),
        ]);

        // Fresh instance without eager loaded npcs
        $cleanGang = Gang::where('id', $gang->id)->first();
        $this->assertFalse($cleanGang->relationLoaded('npcs'));
        $this->assertEquals(0, $cleanGang->npc_defense_bonus_percent);

        Carbon::setTestNow();
    }

    public function test_faq_and_news_contain_specialist_limit_information(): void
    {
        $faqData = FaqController::getFaqData();

        $faqSerialized = json_encode($faqData);
        $this->assertStringContainsString('Maximaal 5 per Type', $faqSerialized);
        $this->assertStringContainsString('100%', $faqSerialized);

        $newsSource = file_get_contents(app_path('Console/Commands/PublishUpdateNews.php'));
        $this->assertStringContainsString('Specialistenlimiet', $newsSource);
        $this->assertStringContainsString('maximaal 5 actieve contracten', $newsSource);
    }

    public function test_npc_refund_migration_increments_protected_treasury(): void
    {
        [$user, $character, $gang] = $this->createLeaderAndGang(1000000);
        $gang->update(['protected_treasury' => 500000]);

        // Create 7 active Boris Enforcer NPCs (2 excess, cost €500k each)
        for ($i = 0; $i < 7; $i++) {
            GangNpc::create([
                'gang_id' => $gang->id,
                'npc_key' => 'enforcer_kapitein',
                'name' => 'Boris '.$i,
                'role_title' => 'Enforcer',
                'bonus_type' => 'defense',
                'bonus_value' => 20,
                'hired_at' => now(),
                'expires_at' => now()->addMinutes(30),
            ]);
        }

        $migration = require database_path('migrations/2026_11_09_152000_limit_same_npc_instances_to_five_and_refund.php');
        $migration->up();

        $gang->refresh();
        // 5 kept, 2 refunded
        $this->assertEquals(5, GangNpc::where('gang_id', $gang->id)->where('npc_key', 'enforcer_kapitein')->count());
        // Protected treasury increased by €1.000.000 (2 * €500.000)
        $this->assertEquals(1500000, $gang->protected_treasury);
    }

    public function test_declare_war_fails_if_treasury_dropped_below_entry_stake(): void
    {
        [$user, $character, $gang] = $this->createLeaderAndGang(100000); // 100k < 250k entry stake
        $targetGang = Gang::create([
            'name' => 'Target Rival Gang',
            'tag' => 'TRG',
            'specialty' => 'drugs',
            'treasury' => 10000000,
        ]);

        $response = $this->actingAs($user)->post(route('gangs.declare-war'), [
            'target_gang_id' => $targetGang->id,
        ]);
        $response->assertSessionHas('error');
        $this->assertStringContainsString('Er is minimaal €250.000 in de bende kluis nodig', session('error'));
    }

    public function test_hire_npc_purges_expired_npcs_before_checking_limits(): void
    {
        Carbon::setTestNow(now());
        [$user, $character, $gang] = $this->createLeaderAndGang(50000000);

        // Fill capacity to maximum (10 slots) with 10 expired NPCs
        for ($i = 0; $i < 10; $i++) {
            GangNpc::create([
                'gang_id' => $gang->id,
                'npc_key' => 'chemisch_specialist',
                'name' => 'Dr. Walter '.$i,
                'role_title' => 'Chemicus',
                'bonus_type' => 'production_speed',
                'bonus_value' => 30,
                'hired_at' => now()->subMinutes(60),
                'expires_at' => now()->subMinutes(10),
            ]);
        }

        // Before hiring, gang has 10 NPCs in DB
        $this->assertEquals(10, GangNpc::where('gang_id', $gang->id)->count());

        // Hiring new NPC must purge the 10 expired ones and succeed
        $response = $this->actingAs($user)->post(route('gangs.panden.hire-npc', 'chemisch_specialist'));
        $response->assertSessionHas('success');

        // Old expired ones deleted, only the newly hired 1 remains
        $this->assertEquals(1, GangNpc::where('gang_id', $gang->id)->count());

        Carbon::setTestNow();
    }
}
