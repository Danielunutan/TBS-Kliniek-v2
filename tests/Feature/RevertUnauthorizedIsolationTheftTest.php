<?php

namespace Tests\Feature;

use App\Http\Controllers\FightController;
use App\Models\Character;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RevertUnauthorizedIsolationTheftTest extends TestCase
{
    use RefreshDatabase;

    private function createCharacter(array $attrs = []): Character
    {
        $user = User::factory()->create();

        return Character::create(array_merge([
            'user_id' => $user->id,
            'name' => 'Speler_'.rand(1000, 9999),
            'level' => 10,
            'strength' => 20,
            'defense' => 20,
            'speed' => 20,
            'cash' => 500000,
            'bank' => 1000000,
            'energy' => 100,
            'max_energy' => 100,
            'health' => 100,
            'max_health' => 100,
            'vip_points' => 50,
            'stars' => 100,
            'respect' => 100,
            'is_in_prison' => false,
        ], $attrs));
    }

    public function test_migration_reverts_stolen_money_to_bella_and_deducts_from_arjan(): void
    {
        // Setup Bella and Arjan
        $bella = $this->createCharacter([
            'name' => 'Bella💗🌸',
            'cash' => 50000,
            'protection_until' => null,
            'fights_lost' => 3,
        ]);

        $arjan = $this->createCharacter([
            'name' => 'OOHPA Arjan',
            'cash' => 600000,
            'bank' => 2000000,
            'thefts_succeeded' => 10,
            'fights_won' => 15,
        ]);

        // Run the specific migration logic
        $migration = require database_path('migrations/2026_10_31_231335_revert_unauthorized_isolation_theft_from_bella.php');
        $migration->up();

        $bella->refresh();
        $arjan->refresh();

        // Bella checks
        $this->assertEquals(50000 + 495028, $bella->cash);
        $this->assertNotNull($bella->protection_until);
        $this->assertTrue(Carbon::parse($bella->protection_until)->isFuture());
        $this->assertEquals(2, $bella->fights_lost);

        // Arjan checks
        $this->assertEquals(600000 - 495028, $arjan->cash);
        $this->assertEquals(9, $arjan->thefts_succeeded);
        $this->assertEquals(14, $arjan->fights_won);

        // Notifications & Messages checks
        $this->assertDatabaseHas('game_notifications', [
            'character_id' => $bella->id,
            'type' => 'shield',
        ]);
        $this->assertDatabaseHas('game_messages', [
            'recipient_id' => $bella->id,
            'subject' => '⚖️ Uitspraak Directie: Beroving Ongeldig Verklaard',
        ]);
        $this->assertDatabaseHas('game_notifications', [
            'character_id' => $arjan->id,
            'type' => 'theft',
        ]);
        $this->assertDatabaseHas('game_messages', [
            'recipient_id' => $arjan->id,
            'subject' => '⚖️ Mededeling Directie: Correctie Buit Luchtplaats',
        ]);
    }

    public function test_attacker_cannot_initiate_luchtplaats_fight_while_in_isolation(): void
    {
        $attacker = $this->createCharacter([
            'level' => 15,
            'energy' => 100,
            'protection_until' => now()->addHours(20),
        ]);

        $defender = $this->createCharacter([
            'level' => 12,
            'cash' => 300000,
        ]);

        $response = $this->actingAs($attacker->user)->post(route('fight.attack'), [
            'opponent_id' => $defender->id,
        ]);

        $response->assertSessionHas('error');
        $this->assertStringContainsString('Vrijwillige Isolatie', session('error'));

        // Isolation protection must NOT be broken!
        $attacker->refresh();
        $this->assertNotNull($attacker->protection_until);
        $this->assertTrue($attacker->hasIsolationProtection());
    }

    public function test_commit_pending_battle_blocks_theft_when_defender_is_protected(): void
    {
        $attacker = $this->createCharacter(['level' => 15]);
        $defender = $this->createCharacter([
            'level' => 12,
            'cash' => 400000,
            'protection_until' => now()->addHours(5),
        ]);

        session([
            'pending_arena_battle' => [
                'attacker_id' => $attacker->id,
                'defender_id' => $defender->id,
                'won' => true,
                'stolen_cash' => 100000,
                'damage_taken' => 10,
                'xp' => 25,
                'respect' => 2,
                'resolved' => false,
            ],
        ]);

        $fightController = app(FightController::class);
        $fightController->commitPendingBattle($attacker);

        $defender->refresh();
        $attacker->refresh();

        // No cash should have been stolen because defender isProtected()!
        $this->assertEquals(400000, $defender->cash);
        $this->assertEquals(500000, $attacker->cash);
    }
}
