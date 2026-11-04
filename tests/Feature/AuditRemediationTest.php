<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\User;
use App\Services\RegistrationIpLimitService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class AuditRemediationTest extends TestCase
{
    use RefreshDatabase;

    private function player(): User
    {
        $user = User::factory()->create();
        Character::factory()->create(['user_id' => $user->id, 'cash' => 10000]);

        return $user;
    }

    public function test_memory_rejects_a_forged_finish_without_a_server_game(): void
    {
        $user = $this->player();

        $this->actingAs($user)->postJson(route('memory.finish'), [
            'won' => true, 'time_taken' => 3, 'lives_left' => 3, 'moves' => 8,
        ])->assertStatus(422);

        $this->assertSame(10000, $user->character->fresh()->cash);
    }

    public function test_memory_server_tracks_moves_and_settles_only_once(): void
    {
        $user = $this->player();
        $this->actingAs($user);
        $game = $this->postJson(route('memory.start'))->assertOk()->json('game');
        $this->postJson(route('memory.finish'), ['game_id' => $game['id'], 'won' => true])->assertStatus(422);
        $this->postJson(route('memory.move'), ['game_id' => $game['id'], 'card' => 0])->assertStatus(422);
        $this->travel(5)->seconds();

        $pairs = [];
        foreach ($game['deck'] as $index => $card) {
            $pairs[$card['id']][] = $index;
        }
        foreach ($pairs as [$first, $second]) {
            $this->postJson(route('memory.move'), ['game_id' => $game['id'], 'card' => $first])->assertOk();
            $this->travel(1)->seconds();
            $this->postJson(route('memory.move'), ['game_id' => $game['id'], 'card' => $second])->assertJsonPath('match', true);
            $this->travel(1)->seconds();
        }
        $this->postJson(route('memory.finish'), ['game_id' => $game['id']])->assertJsonPath('won', true);
        $cash = $user->character->fresh()->cash;
        $this->postJson(route('memory.finish'), ['game_id' => $game['id']])->assertStatus(422);
        $this->assertSame($cash, $user->character->fresh()->cash);
    }

    public function test_memory_timeout_is_a_loss_and_round_is_resumed(): void
    {
        $user = $this->player();
        $this->actingAs($user);
        $game = $this->postJson(route('memory.start'))->assertOk()->json('game');
        $this->postJson(route('memory.start'))->assertJsonPath('game.id', $game['id']);
        $this->travel(65)->seconds();
        $this->postJson(route('memory.finish'), ['game_id' => $game['id'], 'won' => true])->assertJsonPath('won', false);
    }

    public function test_memory_mismatches_cost_lives_and_other_players_cannot_use_the_round(): void
    {
        $user = $this->player();
        $this->actingAs($user);
        $game = $this->postJson(route('memory.start'))->assertOk()->json('game');
        $this->travel(5)->seconds();
        $first = 0;
        $second = collect($game['deck'])->search(fn ($card) => $card['id'] !== $game['deck'][$first]['id']);
        $this->postJson(route('memory.move'), ['game_id' => $game['id'], 'card' => $first])->assertOk();
        $this->postJson(route('memory.move'), ['game_id' => $game['id'], 'card' => $first])->assertStatus(422);
        $this->postJson(route('memory.move'), ['game_id' => $game['id'], 'card' => $second])
            ->assertJsonPath('match', false)->assertJsonPath('game.lives', 2);
        $this->actingAs($this->player())->postJson(route('memory.finish'), ['game_id' => $game['id']])->assertStatus(422);
    }

    public function test_registration_limit_transaction_rolls_back_the_lock_row_when_creation_fails(): void
    {
        $service = app(RegistrationIpLimitService::class);
        try {
            $service->register('198.51.100.44', function (): void {
                throw new \RuntimeException('Account creation failed');
            });
            $this->fail('Account creation should fail.');
        } catch (\RuntimeException $e) {
            $this->assertSame('Account creation failed', $e->getMessage());
        }
        $this->assertDatabaseCount('registration_ip_locks', 0);
    }

    public function test_push_claim_does_not_trust_client_standalone_flags(): void
    {
        $user = $this->player();

        $this->actingAs($user)->withHeader('X-TBS-Standalone', '1')
            ->postJson(route('community.claim', 'push'), [
                'is_standalone' => true,
                'notification_granted' => true,
            ])->assertJsonPath('success', false);

        $this->assertDatabaseMissing('social_reward_claims', ['user_id' => $user->id, 'platform' => 'push']);
    }

    public function test_discord_mutations_do_not_accept_get(): void
    {
        $this->assertSame(['POST'], Route::getRoutes()->getByName('discord.link')->methods());
        $this->assertSame(['POST'], Route::getRoutes()->getByName('discord.sync_roles')->methods());
    }
}
