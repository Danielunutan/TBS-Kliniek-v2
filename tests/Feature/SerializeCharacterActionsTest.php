<?php

namespace Tests\Feature;

use App\Http\Middleware\SerializeCharacterActions;
use App\Models\RadioShoutout;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class SerializeCharacterActionsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        // Do not wait in tests: a held lock must be rejected at once.
        config(['game.action_lock_wait_seconds' => 0]);
    }

    private function playerWithCash(): User
    {
        $user = User::where('email', 'demo@tbskliniek.nl')->first();
        $user->character->update([
            'cash' => 5000,
            'is_in_prison' => false,
            'prison_until' => null,
        ]);

        return $user;
    }

    private function shoutoutPayload(): array
    {
        return [
            'message' => 'Groetjes aan Paviljoen 13!',
            'track_title' => 'Bajes Blues',
        ];
    }

    public function test_post_is_rejected_while_an_earlier_request_of_the_same_player_is_running(): void
    {
        $user = $this->playerWithCash();
        $lock = Cache::lock('character_action_'.$user->id, SerializeCharacterActions::LOCK_TTL_SECONDS);
        $this->assertTrue($lock->get());

        $response = $this->actingAs($user)->post(route('radio.shoutout'), $this->shoutoutPayload());

        $response->assertSessionHas('error');
        $this->assertSame(0, RadioShoutout::count());
        $this->assertSame(5000, (int) $user->character->fresh()->cash);

        $lock->release();
    }

    public function test_json_post_gets_http_429_while_the_player_lock_is_held(): void
    {
        $user = $this->playerWithCash();
        $lock = Cache::lock('character_action_'.$user->id, SerializeCharacterActions::LOCK_TTL_SECONDS);
        $this->assertTrue($lock->get());

        $response = $this->actingAs($user)->postJson(route('radio.shoutout'), $this->shoutoutPayload());

        $response->assertStatus(429)->assertJson(['success' => false]);

        $lock->release();
    }

    public function test_post_runs_and_releases_the_lock_when_no_other_request_is_running(): void
    {
        $user = $this->playerWithCash();

        $response = $this->actingAs($user)->post(route('radio.shoutout'), $this->shoutoutPayload());

        $response->assertSessionHas('success');
        $this->assertSame(1, RadioShoutout::count());

        $lock = Cache::lock('character_action_'.$user->id, SerializeCharacterActions::LOCK_TTL_SECONDS);
        $this->assertTrue($lock->get(), 'The player lock must be free after the request ends.');
        $lock->release();
    }

    public function test_read_requests_are_never_blocked_by_the_player_lock(): void
    {
        $user = $this->playerWithCash();
        $lock = Cache::lock('character_action_'.$user->id, SerializeCharacterActions::LOCK_TTL_SECONDS);
        $this->assertTrue($lock->get());

        $this->actingAs($user)->get(route('radio.index'))->assertOk();

        $lock->release();
    }

    public function test_lock_of_one_player_does_not_block_another_player(): void
    {
        $user = $this->playerWithCash();
        $otherUserId = $user->id + 1000;
        $lock = Cache::lock('character_action_'.$otherUserId, SerializeCharacterActions::LOCK_TTL_SECONDS);
        $this->assertTrue($lock->get());

        $response = $this->actingAs($user)->post(route('radio.shoutout'), $this->shoutoutPayload());

        $response->assertSessionHas('success');

        $lock->release();
    }
}
