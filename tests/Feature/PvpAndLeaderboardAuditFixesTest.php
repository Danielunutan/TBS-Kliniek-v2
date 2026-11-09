<?php

namespace Tests\Feature;

use App\Models\BloodFeud;
use App\Models\Character;
use App\Models\Gang;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class PvpAndLeaderboardAuditFixesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    public function test_hitman_start_intel_allows_blood_feud_retaliation_across_level_gap(): void
    {
        $myGang = Gang::create([
            'name' => 'Syndicate Intel',
            'tag' => 'SINT',
            'specialty' => 'violence',
            'description' => 'Test gang',
            'treasury' => 100000,
        ]);

        $leaderUser = User::factory()->create();
        $leader = Character::factory()->create([
            'user_id' => $leaderUser->id,
            'name' => 'IntelBoss',
            'gang_id' => $myGang->id,
            'gang_role' => 'leader',
            'level' => 60,
            'cash' => 500000,
            'bank' => 500000,
            'current_location' => 'Paviljoen 1 (Gesloten)',
        ]);

        $victim = Character::factory()->create([
            'name' => 'LowMemberIntel',
            'gang_id' => $myGang->id,
            'gang_role' => 'member',
            'level' => 10,
            'current_location' => 'Paviljoen 1 (Gesloten)',
        ]);

        $enemyUser = User::factory()->create();
        $enemy = Character::factory()->create([
            'user_id' => $enemyUser->id,
            'name' => 'EnemyTarget',
            'gang_id' => null,
            'level' => 12,
            'current_location' => 'Paviljoen 1 (Gesloten)',
        ]);

        // Level difference is 48 (> 15). Intel request must fail without blood feud retaliation rights.
        $responseBlocked = $this->actingAs($leaderUser)->post(route('hitman.intel'), [
            'target_id' => $enemy->id,
            'intel_type' => 'standard_15m',
        ]);
        $responseBlocked->assertSessionHas('error');
        $this->assertStringContainsString('levelverschil', session('error'));

        // Enemy attacks member, which creates a blood feud record.
        BloodFeud::create([
            'attacker_id' => $enemy->id,
            'victim_id' => $victim->id,
            'victim_gang_id' => $myGang->id,
            'expires_at' => now()->addDays(7),
            'is_avenged' => false,
        ]);

        // Leader can start intel because of the retaliation right.
        $responseAllowed = $this->actingAs($leaderUser)->post(route('hitman.intel'), [
            'target_id' => $enemy->id,
            'intel_type' => 'standard_15m',
        ]);
        $responseAllowed->assertSessionHas('success');
    }

    public function test_theft_requires_confirmation_before_breaking_medical_shield(): void
    {
        $user = User::factory()->create();
        $character = Character::factory()->create([
            'user_id' => $user->id,
            'energy' => 50,
            'cash' => 1000,
            'protection_until' => now()->addHours(2),
            'perks' => [
                'medical_shield_until' => now()->addHours(2)->timestamp,
            ],
        ]);

        // Attempt theft without confirmation flag.
        $responsePrompt = $this->actingAs($user)->post(route('theft.steal'), [
            'tier' => 'pocket',
        ]);

        $responsePrompt->assertSessionHas('error');
        $this->assertStringContainsString('Medisch Infuus-Schild', session('error'));

        $character->refresh();
        $this->assertTrue($character->hasMedicalShield());

        // Attempt theft with shield confirmation flag.
        $responseConfirm = $this->actingAs($user)->post(route('theft.steal'), [
            'tier' => 'pocket',
            'confirm_break_shield' => true,
        ]);

        $character->refresh();
        $this->assertFalse($character->hasMedicalShield());
    }

    public function test_theft_service_blocks_theft_against_victim_in_coma_or_code_blue(): void
    {
        $user = User::factory()->create();
        $thief = Character::factory()->create([
            'user_id' => $user->id,
            'energy' => 50,
            'cash' => 1000,
            'current_location' => 'Paviljoen 1 (Gesloten)',
        ]);

        $victimUser = User::factory()->create();
        $victim = Character::factory()->create([
            'user_id' => $victimUser->id,
            'current_location' => 'Paviljoen 1 (Gesloten)',
            'coma_until' => now()->addMinutes(30),
            'cash' => 5000,
        ]);

        $response = $this->actingAs($user)->post(route('theft.steal'), [
            'tier' => 'pocket',
            'victim_id' => $victim->id,
        ]);

        $response->assertSessionHas('error');
        $this->assertStringContainsString('coma', strtolower(session('error')));
    }

    public function test_hitman_view_enables_actions_when_cash_is_zero_but_bank_covers_cost(): void
    {
        $user = User::factory()->create();
        $character = Character::factory()->create([
            'user_id' => $user->id,
            'cash' => 0,
            'bank' => 500000,
            'current_location' => 'Paviljoen 1 (Gesloten)',
            'bullets' => 20,
        ]);

        $response = $this->actingAs($user)->get(route('hitman.index'));
        $response->assertOk();
        $this->assertFalse(($character->cash + $character->bank) < 15000);
    }

    public function test_leaderboard_renders_new_categories_and_excludes_banned_users(): void
    {
        $activeUser = User::factory()->create(['is_banned' => false]);
        $activeChar = Character::factory()->create([
            'user_id' => $activeUser->id,
            'name' => 'ActivePatient',
            'dagbesteding_shifts_completed' => 25,
            'discipline' => 80,
            'missions_completed' => 15,
            'hacks_completed_total' => 10,
        ]);

        $bannedUser = User::factory()->create(['is_banned' => true]);
        $bannedChar = Character::factory()->create([
            'user_id' => $bannedUser->id,
            'name' => 'BannedPatient',
            'dagbesteding_shifts_completed' => 100,
            'discipline' => 100,
            'missions_completed' => 50,
            'hacks_completed_total' => 50,
        ]);

        $this->actingAs($activeUser);

        // Test dagbesteding leaderboard.
        $respDag = $this->get(route('leaderboard.index', ['type' => 'dagbesteding']));
        $respDag->assertOk();
        $respDag->assertSee('Dagbesteding Toppers');
        $respDag->assertSee('Diensten');
        $respDag->assertSee('ActivePatient');
        $respDag->assertDontSee('BannedPatient');

        // Test discipline leaderboard.
        $respDisc = $this->get(route('leaderboard.index', ['type' => 'discipline']));
        $respDisc->assertOk();
        $respDisc->assertSee('Hoogste Discipline');
        $respDisc->assertSee('ActivePatient');
        $respDisc->assertDontSee('BannedPatient');

        // Test missions leaderboard.
        $respMissions = $this->get(route('leaderboard.index', ['type' => 'missions']));
        $respMissions->assertOk();
        $respMissions->assertSee('Meeste Missies');
        $respMissions->assertSee('ActivePatient');
        $respMissions->assertDontSee('BannedPatient');

        // Test hacks leaderboard.
        $respHacks = $this->get(route('leaderboard.index', ['type' => 'hacks']));
        $respHacks->assertOk();
        $respHacks->assertSee('Meester Hackers');
        $respHacks->assertSee('ActivePatient');
        $respHacks->assertDontSee('BannedPatient');
    }
}
