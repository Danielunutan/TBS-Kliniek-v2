<?php

namespace Tests\Feature;

use App\Http\Controllers\ArmWrestleController;
use App\Models\Character;
use App\Models\User;
use App\Support\ArmWrestleOutcome;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArmWrestleServerOutcomeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    protected function createStrongPlayer(User $user): Character
    {
        return Character::create([
            'user_id' => $user->id,
            'name' => 'Rozemarijn Bakker',
            'cash' => 100000,
            'bank' => 0,
            'respect' => 100,
            'energy' => 100,
            'max_energy' => 100,
            'health' => 100,
            'max_health' => 100,
            'strength' => 60,
            'level' => 1,
            'xp' => 0,
            'last_energy_refresh' => now(),
        ]);
    }

    public function test_a_client_claimed_win_does_not_pay_when_the_server_roll_is_a_loss(): void
    {
        $this->fixArmWrestleRoll(false);
        $user = User::factory()->create();
        $character = $this->createStrongPlayer($user);
        $npc = ArmWrestleController::getNpcOpponents()['sjonnie'];

        $response = $this->actingAs($user)->postJson(route('armwrestling.npc.finish'), [
            'npc_key' => 'sjonnie',
            'won' => true,
            'clicks' => 500,
            'duration_ms' => 9000,
        ]);

        $response->assertOk()->assertJsonPath('won', false);
        $this->assertEquals(100000 - $npc['bet'], $character->fresh()->cash);
    }

    public function test_a_server_roll_win_pays_the_npc_payout(): void
    {
        $this->fixArmWrestleRoll(true);
        $user = User::factory()->create();
        $character = $this->createStrongPlayer($user);
        $npc = ArmWrestleController::getNpcOpponents()['sjonnie'];

        $response = $this->actingAs($user)->postJson(route('armwrestling.npc.finish'), [
            'npc_key' => 'sjonnie',
            'won' => false,
            'clicks' => 0,
            'duration_ms' => 100,
        ]);

        $response->assertOk()->assertJsonPath('won', true);
        $this->assertEquals(100000 - $npc['bet'] + $npc['win_payout'], $character->fresh()->cash);
    }

    public function test_the_outcome_roll_favours_a_stronger_player(): void
    {
        $outcome = new ArmWrestleOutcome;
        $strongWins = 0;

        for ($attempt = 0; $attempt < 2000; $attempt++) {
            if ($outcome->isPlayerWinner(150, 12)) {
                $strongWins++;
            }
        }

        // With strength 150 against 12, the win chance is about 93 percent. Any result below 80 percent is a defect.
        $this->assertGreaterThan(1600, $strongWins);
    }
}
