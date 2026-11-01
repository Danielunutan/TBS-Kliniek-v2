<?php

namespace Tests\Feature;

use App\Http\Controllers\ConnectFourController;
use App\Models\Character;
use App\Models\CharacterConnectFourStat;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ConnectFourServerAuthorityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    /**
     * @return array{0: User, 1: Character}
     */
    protected function createPlayer(): array
    {
        $user = User::factory()->create();
        $character = Character::create([
            'user_id' => $user->id,
            'name' => 'Rozemarijn Bakker',
            'cash' => 1000,
            'bank' => 500,
            'respect' => 100,
            'energy' => 80,
            'max_energy' => 100,
            'health' => 100,
            'max_health' => 100,
            'level' => 1,
            'xp' => 0,
            'last_energy_refresh' => now(),
        ]);

        return [$user, $character];
    }

    /**
     * Board rows run from the top (row 0) to the bottom (row 5). Cells hold 0 (empty), 1 (player) or 2 (Arjan).
     */
    protected function emptyBoard(): array
    {
        return array_fill(0, 6, array_fill(0, 7, 0));
    }

    protected function seedMatch(Character $character, array $board): void
    {
        Cache::put(ConnectFourController::matchCacheKey($character->id), [
            'board' => $board,
            'starter' => 'player',
        ], now()->addHours(2));
    }

    public function test_the_client_reported_finish_route_no_longer_exists(): void
    {
        $this->assertFalse(Route::has('connect_four.finish'));
    }

    public function test_a_move_without_a_started_match_is_rejected(): void
    {
        [$user, $character] = $this->createPlayer();

        $response = $this->actingAs($user)->postJson(route('connect_four.move'), ['column' => 3]);

        $response->assertStatus(422);
        $this->assertEquals(1000, $character->fresh()->cash);
    }

    public function test_starting_a_match_returns_a_server_board_and_starter(): void
    {
        [$user] = $this->createPlayer();

        $response = $this->actingAs($user)->postJson(route('connect_four.match.start'));

        $response->assertOk()->assertJsonPath('success', true)->assertJsonPath('starter', 'player');
        $this->assertCount(6, $response->json('board'));
        $this->assertNull($response->json('ai'));
    }

    public function test_a_player_move_returns_the_server_reply_from_arjan(): void
    {
        [$user] = $this->createPlayer();
        $this->actingAs($user)->postJson(route('connect_four.match.start'))->assertOk();

        $response = $this->actingAs($user)->postJson(route('connect_four.move'), ['column' => 3]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('status', 'ongoing')
            ->assertJsonPath('player.row', 5)
            ->assertJsonPath('player.col', 3);
        $this->assertIsInt($response->json('ai.col'));
        $this->assertGreaterThanOrEqual(0, $response->json('ai.col'));
        $this->assertLessThanOrEqual(6, $response->json('ai.col'));
    }

    public function test_a_winning_move_is_paid_once_by_the_server(): void
    {
        [$user, $character] = $this->createPlayer();
        $board = $this->emptyBoard();
        $board[5][0] = 1;
        $board[5][1] = 1;
        $board[5][2] = 1;
        $this->seedMatch($character, $board);

        $response = $this->actingAs($user)->postJson(route('connect_four.move'), ['column' => 3]);

        $response->assertOk()
            ->assertJsonPath('status', 'player')
            ->assertJsonPath('winner', 'player')
            ->assertJsonPath('reward_cash', 5000);
        $this->assertEquals(6000, $character->fresh()->cash);
        $this->assertEquals(1, CharacterConnectFourStat::where('character_id', $character->id)->value('current_player_wins'));

        // The match is settled and removed. A repeated request must not pay again.
        $repeat = $this->actingAs($user)->postJson(route('connect_four.move'), ['column' => 3]);

        $repeat->assertStatus(422);
        $this->assertEquals(6000, $character->fresh()->cash);
    }

    public function test_a_move_into_a_full_column_is_rejected(): void
    {
        [$user, $character] = $this->createPlayer();
        $board = $this->emptyBoard();
        for ($row = 0; $row < 6; $row++) {
            $board[$row][3] = $row % 2 === 0 ? 1 : 2;
        }
        $this->seedMatch($character, $board);

        $response = $this->actingAs($user)->postJson(route('connect_four.move'), ['column' => 3]);

        $response->assertStatus(422);
        $this->assertEquals(1000, $character->fresh()->cash);
    }

    public function test_the_column_must_be_a_valid_index(): void
    {
        [$user, $character] = $this->createPlayer();
        $this->seedMatch($character, $this->emptyBoard());

        $response = $this->actingAs($user)->postJson(route('connect_four.move'), ['column' => 9]);

        $response->assertStatus(422);
    }
}
