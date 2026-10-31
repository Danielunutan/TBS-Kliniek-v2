<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\GameNotification;
use App\Models\Gang;
use App\Models\User;
use App\Services\EnergyService;
use App\Services\PrisonService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RespectNeverLostTest extends TestCase
{
    use RefreshDatabase;

    protected function createCharacter(User $user, array $attrs = []): Character
    {
        return Character::create(array_merge([
            'user_id' => $user->id,
            'name' => 'Boef_'.rand(1000, 9999),
            'level' => 1,
            'xp' => 0,
            'health' => 100,
            'max_health' => 100,
            'energy' => 100,
            'max_energy' => 100,
            'cash' => 5000,
            'bank' => 10000,
            'stars' => 10,
            'respect' => 100,
            'treatment_score' => 50,
            'leave_trips_count' => 0,
            'last_active_at' => now(),
        ], $attrs));
    }

    public function test_treatment_session_never_reduces_respect(): void
    {
        $user = User::factory()->create();
        $character = $this->createCharacter($user, [
            'respect' => 100,
        ]);

        $this->actingAs($user)->withSession(['treatment_questions' => [5]]);

        // Submit option 'a' for question 5 (previously had -1 respect)
        $response = $this->post(route('treatment.submit'), [
            'answers' => [
                5 => 'a',
            ],
        ]);

        $response->assertRedirect();
        $character->refresh();
        $this->assertGreaterThanOrEqual(100, $character->respect);
        $this->assertEquals(100, $character->respect);
    }

    public function test_failed_prison_escape_does_not_reduce_respect(): void
    {
        $user = User::factory()->create(['is_onboarded' => true]);
        $character = $this->createCharacter($user, [
            'is_in_prison' => true,
            'prison_until' => now()->addMinutes(10),
            'prison_escape_attempted' => false,
            'energy' => 100,
            'respect' => 100,
            'intelligence' => 0,
            'stamina' => 0,
        ]);

        $prisonService = app(PrisonService::class);
        $energyService = app(EnergyService::class);

        $res = $prisonService->attemptEscape($character, $energyService);
        $character->refresh();

        $this->assertGreaterThanOrEqual(100, $character->respect);
        if (! $res['success']) {
            $this->assertEquals(100, $character->respect);
        }
    }

    public function test_gang_treasury_withdrawal_does_not_penalize_respect(): void
    {
        $user = User::factory()->create(['is_onboarded' => true]);
        $leader = $this->createCharacter($user, [
            'respect' => 500,
            'cash' => 1000,
            'gang_role' => 'leader',
        ]);

        $gang = Gang::factory()->create([
            'leader_character_id' => $leader->id,
            'treasury' => 100000,
        ]);

        $leader->update(['gang_id' => $gang->id]);

        $this->actingAs($user);

        $response = $this->post(route('gangs.withdraw'), [
            'amount' => 50000,
        ]);

        $response->assertSessionHas('success');
        $leader->refresh();
        $this->assertEquals(500, $leader->respect);
        $this->assertEquals(51000, $leader->cash);
    }

    public function test_hitman_backfire_does_not_reduce_attacker_respect(): void
    {
        $shooterUser = User::factory()->create(['is_onboarded' => true]);
        $shooter = $this->createCharacter($shooterUser, [
            'bullets' => 50,
            'energy' => 100,
            'level' => 10,
            'strength' => 1,
            'current_location' => 'De Luchtplaats',
            'respect' => 50,
        ]);

        $defenderUser = User::factory()->create(['is_onboarded' => true]);
        $defender = $this->createCharacter($defenderUser, [
            'level' => 10,
            'strength' => 500,
            'defense' => 5000,
            'health' => 100,
            'vest_until' => now()->addHours(12),
            'current_location' => 'De Luchtplaats',
        ]);

        $this->actingAs($shooterUser);

        // Run shoot attempts until either backfire occurs or multiple shots resolve
        // With defense 5000 and vest backfire chance 55%, backfire will trigger quickly
        for ($i = 0; $i < 10; $i++) {
            $shooter->update(['energy' => 100, 'bullets' => 50, 'health' => 100, 'coma_until' => null, 'protection_until' => null]);
            $defender->update(['health' => 100, 'coma_until' => null, 'protection_until' => null, 'current_location' => 'De Luchtplaats']);

            $this->post(route('hitman.shoot'), [
                'target_id' => $defender->id,
                'bullets' => 10,
            ]);

            $shooter->refresh();
            $this->assertGreaterThanOrEqual(50, $shooter->respect);
        }
    }

    public function test_hitman_liquidation_does_not_reduce_defender_respect(): void
    {
        $shooterUser = User::factory()->create(['is_onboarded' => true]);
        $shooter = $this->createCharacter($shooterUser, [
            'bullets' => 50,
            'energy' => 100,
            'level' => 10,
            'strength' => 500,
            'current_location' => 'De Luchtplaats',
            'respect' => 200,
        ]);

        $targetUser = User::factory()->create(['is_onboarded' => true]);
        $target = $this->createCharacter($targetUser, [
            'level' => 10,
            'defense' => 10,
            'health' => 100,
            'cash' => 50000,
            'bank' => 50000,
            'respect' => 1000,
            'current_location' => 'De Luchtplaats',
        ]);

        $this->actingAs($shooterUser);

        $response = $this->post(route('hitman.shoot'), [
            'target_id' => $target->id,
            'bullets' => 15,
        ]);

        $response->assertSessionHas('success');
        $target->refresh();
        $this->assertEquals(1000, $target->respect);
    }

    public function test_restore_migration_recovers_lost_respect_and_sends_notification(): void
    {
        $user = User::factory()->create(['is_onboarded' => true]);
        $character = $this->createCharacter($user, [
            'respect' => 5, // Below starting 10
            'is_in_prison' => true,
            'prison_escape_attempted' => true, // +1
            'treatment_history' => [
                'notes' => ['Afstand nemend van criminele subculturen en negatieve invloeden.'], // +1
            ],
        ]);

        // Add a liquidation notification
        GameNotification::create([
            'character_id' => $character->id,
            'type' => 'skull',
            'icon' => 'fa-skull',
            'title' => '🚨 GELIQUIDEERD OP Paviljoen 1',
            'message' => 'Test liquidation',
        ]);

        $migration = require database_path('migrations/2026_10_31_173000_restore_lost_respect_to_affected_characters.php');
        $migration->up();

        $character->refresh();

        // 5 was clamped to 10, then +1 (prison) +1 (treatment) + at least 20 (liquidation)
        $this->assertGreaterThanOrEqual(32, $character->respect);

        $notification = GameNotification::where('character_id', $character->id)
            ->where('title', '⭐ Respect Hersteld!')
            ->first();

        $this->assertNotNull($notification);
        $this->assertStringContainsString('met terugwerkende kracht', $notification->message);
    }
}
