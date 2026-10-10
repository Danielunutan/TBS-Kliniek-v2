<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\Mission;
use App\Models\User;
use App\Services\EconomyService;
use App\Services\LevelService;
use App\Services\MissionService;
use App\Services\TrainingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GameFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_landing_page_loads_with_tbs_kliniek_branding(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('TBS', false);
        $response->assertSee('KLINIEK', false);
        $response->assertSee('Henkie', false);
    }

    public function test_demo_login_authenticates_and_redirects_to_dashboard(): void
    {
        $response = $this->post('/demo-login');

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticated();

        $dashboardResponse = $this->get('/dashboard');
        $dashboardResponse->assertStatus(200);
        $dashboardResponse->assertSee('Henkie');
        $dashboardResponse->assertSee('Dashboard');
    }

    public function test_level_service_calculates_and_processes_rollover_xp(): void
    {
        $levelService = app(LevelService::class);
        $character = Character::first();
        $character->update(['xp' => 0]);

        $initialLevel = $character->level;
        $reqXp = $levelService->getXpRequiredForNextLevel($initialLevel);

        $result = $levelService->addXp($character, $reqXp + 50);

        $this->assertTrue($result['leveled_up']);
        $this->assertEquals($initialLevel + 1, $character->level);
        $this->assertEquals(50, $character->xp);
    }

    public function test_economy_service_deposits_and_withdraws_safely(): void
    {
        $economy = app(EconomyService::class);
        $character = Character::first();

        $character->update(['cash' => 500, 'bank' => 1000]);

        $this->assertTrue($economy->deposit($character, 200));
        $this->assertEquals(300, $character->cash);
        $this->assertEquals(1200, $character->bank);

        $this->assertTrue($economy->withdraw($character, 500));
        $this->assertEquals(800, $character->cash);
        $this->assertEquals(700, $character->bank);
    }

    public function test_mission_execution_updates_character_stats(): void
    {
        $missionService = app(MissionService::class);
        $character = Character::first();
        $mission = Mission::first();

        $character->update(['energy' => 100, 'level' => 10]);

        $result = $missionService->execute($character, $mission);

        $this->assertTrue($result['success']);
        $this->assertGreaterThan(0, $result['cash_earned']);
        $this->assertGreaterThan(0, $result['xp_earned']);
    }

    public function test_training_service_increases_attributes(): void
    {
        $trainingService = app(TrainingService::class);
        $character = Character::first();

        $character->update(['strength' => 10, 'cash' => 1000, 'energy' => 100]);

        $result = $trainingService->train($character, 'strength');

        $this->assertTrue($result['success']);
        $this->assertEquals(11, $character->fresh()->strength);
    }

    public function test_posting_guestbook_message_persists(): void
    {
        $user = User::where('email', 'demo@tbskliniek.nl')->first();
        $this->actingAs($user);

        $response = $this->post("/speler/{$user->character->name}/gastenboek", [
            'message' => 'Testbericht voor het dossier!',
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('guestbook_entries', [
            'message' => 'Testbericht voor het dossier!',
        ]);
    }
}
