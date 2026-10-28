<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArmWrestleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

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
            'cash' => 0,
            'bank' => 50000,
            'stars' => 10,
            'respect' => 10,
            'strength' => 25,
            'speed' => 15,
            'stamina' => 15,
            'is_in_prison' => false,
        ], $attrs));
    }

    public function test_player_can_play_arm_wrestling_with_zero_cash_and_bank_balance(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $character = $this->createCharacter($user, [
            'cash' => 0,
            'bank' => 50000,
            'strength' => 25,
        ]);

        $response = $this->actingAs($user)->get(route('armwrestling.index'));
        $response->assertStatus(200);
        $response->assertSee('Tafelduel Aangaan');

        // Play against Sjonnie (bet: 1500)
        $finishResponse = $this->actingAs($user)->postJson(route('armwrestling.npc.finish'), [
            'npc_key' => 'sjonnie',
            'won' => true,
            'clicks' => 25,
            'duration_seconds' => 5,
        ]);

        $finishResponse->assertStatus(200);
        $finishResponse->assertJson(['success' => true]);

        $character->refresh();
        // Started with 0 cash and 50000 bank.
        // Bet was 1500 deducted from bank (bank -> 48500), payout was 3000 to cash (cash -> 3000).
        $this->assertEquals(48500, $character->bank);
        $this->assertEquals(3000, $character->cash);
        $this->assertEquals(95, $character->energy);
    }

    public function test_player_can_create_challenge_with_bank_balance(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $character = $this->createCharacter($user, [
            'cash' => 500,
            'bank' => 20000,
            'strength' => 30,
        ]);

        $response = $this->actingAs($user)->post(route('armwrestling.challenge.create'), [
            'bet_amount' => 10000,
        ]);

        $response->assertSessionHas('success');
        $character->refresh();

        // 500 cash wiped, remaining 9500 taken from bank (20000 - 9500 = 10500)
        $this->assertEquals(0, $character->cash);
        $this->assertEquals(10500, $character->bank);
    }

    public function test_arm_wrestling_page_renders_cleanly_when_attributes_are_null(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $character = $this->createCharacter($user, [
            'cash' => 0,
            'bank' => 25000,
            'strength' => 50,
        ]);
        // Explicitly simulate missing or null speed attribute (like on production)
        unset($character->speed);

        $response = $this->actingAs($user)->get(route('armwrestling.index'));
        $response->assertStatus(200);
        // Verify valid JavaScript without empty playerSpeed: ,
        $response->assertDontSee('playerSpeed: ,');
        $response->assertSee('playerSpeed: 10,');
        $response->assertSee('Tafelduel Aangaan');
    }

    public function test_player_cannot_spam_arm_wrestling_due_to_cooldown(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $this->createCharacter($user, [
            'cash' => 50000,
            'bank' => 50000,
            'strength' => 25,
            'energy' => 100,
        ]);

        Carbon::setTestNow('2026-01-01 12:00:00');

        // First match succeeds
        $first = $this->actingAs($user)->postJson(route('armwrestling.npc.finish'), [
            'npc_key' => 'sjonnie',
            'won' => true,
            'clicks' => 20,
            'duration_seconds' => 4,
        ]);
        $first->assertStatus(200);

        // Immediate duplicate request within 1 second returns 200 without breaking or throwing 422
        $duplicate = $this->actingAs($user)->postJson(route('armwrestling.npc.finish'), [
            'npc_key' => 'sjonnie',
            'won' => true,
            'clicks' => 20,
            'duration_seconds' => 4,
        ]);
        $duplicate->assertStatus(200);
        $duplicate->assertJson(['success' => true]);

        // After duplicate grace period (e.g. 5 seconds later), attempts while on cooldown are blocked with 422
        $this->travel(5)->seconds();

        $second = $this->actingAs($user)->postJson(route('armwrestling.npc.finish'), [
            'npc_key' => 'sjonnie',
            'won' => true,
            'clicks' => 20,
            'duration_seconds' => 4,
        ]);
        $second->assertStatus(422);
        $second->assertJson(['success' => false]);
    }

    public function test_cheat_attempt_with_insufficient_clicks_or_duration_fails_server_side(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $character = $this->createCharacter($user, [
            'cash' => 200000,
            'bank' => 200000,
            'strength' => 100,
            'energy' => 100,
        ]);

        // Autoclicker sending 0 duration or 0 clicks claiming won: true against Mike
        $cheatAttempt = $this->actingAs($user)->postJson(route('armwrestling.npc.finish'), [
            'npc_key' => 'mike',
            'won' => true,
            'clicks' => 2, // Way too few clicks
            'duration_seconds' => 0,
        ]);

        $cheatAttempt->assertStatus(200);
        $cheatAttempt->assertJson([
            'success' => true,
            'won' => false, // Overruled server-side to false!
        ]);
    }
}
