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
            'last_allowance_at' => now(),
        ], $attrs));
    }

    public function test_player_cannot_play_arm_wrestling_without_sufficient_cash(): void
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
        $response->assertSee('Onvoldoende Contant Geld');

        // Attempt to play against Sjonnie (bet: 1500) with 0 cash
        $finishResponse = $this->actingAs($user)->postJson(route('armwrestling.npc.finish'), [
            'npc_key' => 'sjonnie',
            'won' => true,
            'clicks' => 25,
            'duration_seconds' => 5,
        ]);

        $finishResponse->assertStatus(422);
        $finishResponse->assertJson(['success' => false]);

        $character->refresh();
        $this->assertEquals(50000, $character->bank);
        $this->assertEquals(0, $character->cash);
        $this->assertEquals(100, $character->energy);
    }

    public function test_player_cannot_create_challenge_without_sufficient_cash(): void
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

        $response->assertSessionHas('error');
        $character->refresh();

        $this->assertEquals(500, $character->cash);
        $this->assertEquals(20000, $character->bank);
    }

    public function test_player_with_sufficient_cash_can_create_and_cancel_challenge(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $character = $this->createCharacter($user, [
            'cash' => 15000,
            'bank' => 20000,
            'strength' => 30,
        ]);

        $response = $this->actingAs($user)->post(route('armwrestling.challenge.create'), [
            'bet_amount' => 10000,
        ]);

        $response->assertSessionHas('success');
        $character->refresh();
        $this->assertEquals(5000, $character->cash);
        $this->assertEquals(20000, $character->bank);
    }

    public function test_arm_wrestling_page_renders_cleanly_when_attributes_are_null(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $character = $this->createCharacter($user, [
            'cash' => 25000,
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

        Carbon::setTestNow();
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

        // Fix the server roll to a loss. The client claim of won: true must not change the result.
        $this->fixArmWrestleRoll(false);

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

    public function test_strong_player_fast_legitimate_win_against_mike_is_accepted_and_modal_stays_visible(): void
    {
        $this->fixArmWrestleRoll(true);
        /** @var User $user */
        $user = User::factory()->create();
        $character = $this->createCharacter($user, [
            'cash' => 100000,
            'bank' => 100000,
            'strength' => 300,
            'energy' => 100,
        ]);

        $page = $this->actingAs($user)->get(route('armwrestling.index'));
        $page->assertStatus(200);
        $page->assertSee('x-show="isMatchActive || isSubmitting || matchResult !== null"', false);

        $winResponse = $this->actingAs($user)->postJson(route('armwrestling.npc.finish'), [
            'npc_key' => 'mike',
            'won' => true,
            'clicks' => 4,
            'duration_ms' => 750,
            'duration_seconds' => 1,
        ]);

        $winResponse->assertStatus(200);
        $winResponse->assertJson([
            'success' => true,
            'won' => true,
        ]);

        $character->refresh();
        $this->assertEquals(175000, $character->cash);
    }
}
