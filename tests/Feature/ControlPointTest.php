<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\ControlPoint;
use App\Models\ControlPointLog;
use App\Models\GameMessage;
use App\Models\GameNotification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ControlPointTest extends TestCase
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
            'cash' => 500,
            'bank' => 1000,
            'stars' => 10,
            'respect' => 10,
            'strength' => 10,
            'defense' => 10,
            'stamina' => 10,
            'intelligence' => 10,
            'charisma' => 10,
            'discipline' => 10,
        ], $attrs));
    }

    public function test_user_can_view_control_points_page(): void
    {
        $user = User::factory()->create();
        $character = $this->createCharacter($user);

        $response = $this->actingAs($user)->get(route('control-points.index'));

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('info');
    }

    public function test_player_can_claim_free_control_point(): void
    {
        $user = User::factory()->create();
        $character = $this->createCharacter($user, ['energy' => 20]);
        $point = ControlPoint::where('key', 'luchtplaats')->first();

        $response = $this->actingAs($user)
            ->post(route('control-points.capture', $point));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $character->refresh();
        $this->assertEquals(18, $character->energy);

        $point->refresh();
        $this->assertEquals($character->id, $point->current_character_id);
        $this->assertNotNull($point->captured_at);
        $this->assertEquals(1, $point->total_captures);
    }

    public function test_player_cannot_reclaim_own_control_point(): void
    {
        $user = User::factory()->create();
        $character = $this->createCharacter($user, ['energy' => 20]);
        $point = ControlPoint::where('key', 'luchtplaats')->first();

        // First capture
        $this->actingAs($user)->post(route('control-points.capture', $point));

        // Attempt second capture
        $response = $this->actingAs($user)->post(route('control-points.capture', $point));

        $response->assertRedirect();
        $response->assertSessionHas('error');

        $character->refresh();
        // Energy was only spent once (20 - 2 = 18)
        $this->assertEquals(18, $character->energy);
    }

    public function test_imprisoned_player_cannot_claim_control_point(): void
    {
        $user = User::factory()->create();
        $character = $this->createCharacter($user, [
            'energy' => 20,
            'is_in_prison' => true,
            'prison_until' => now()->addMinutes(15),
        ]);
        $point = ControlPoint::where('key', 'kantine')->first();

        $response = $this->actingAs($user)->post(route('control-points.capture', $point));

        $response->assertRedirect();
        $response->assertSessionHas('error');

        $point->refresh();
        $this->assertNull($point->current_character_id);
    }

    public function test_player_with_low_energy_cannot_claim(): void
    {
        $user = User::factory()->create();
        $character = $this->createCharacter($user, ['energy' => 1]);
        $point = ControlPoint::where('key', 'sportzaal')->first();

        $response = $this->actingAs($user)->post(route('control-points.capture', $point));

        $response->assertRedirect();
        $response->assertSessionHas('error');

        $point->refresh();
        $this->assertNull($point->current_character_id);
    }

    public function test_overtaking_control_point_pays_out_previous_holder_and_logs_history(): void
    {
        $userA = User::factory()->create();
        $charA = $this->createCharacter($userA, [
            'name' => 'Koning_A',
            'cash' => 100,
            'stars' => 5,
            'energy' => 20,
        ]);

        $userB = User::factory()->create();
        $charB = $this->createCharacter($userB, [
            'name' => 'Uitdager_B',
            'energy' => 20,
        ]);

        $point = ControlPoint::where('key', 'depot')->first();

        // Char A captures the point
        $this->actingAs($userA)->post(route('control-points.capture', $point));

        // Simulate that 12 minutes elapsed
        $capturedTime = now()->subMinutes(12);
        $point->update(['captured_at' => $capturedTime]);

        // Char B captures the point from Char A
        $response = $this->actingAs($userB)->post(route('control-points.capture', $point));
        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Check Char A payout: 12 minutes * 50 = €600, 12 minutes * 1 = 12 stars
        $charA->refresh();
        $this->assertEquals(100 + 600, $charA->cash);
        $this->assertEquals(5 + 12, $charA->stars);

        // Check Point holder is now Char B
        $point->refresh();
        $this->assertEquals($charB->id, $point->current_character_id);
        $this->assertEquals(2, $point->total_captures);

        // Check ControlPointLog
        $log = ControlPointLog::where('character_id', $charA->id)->latest('id')->first();
        $this->assertNotNull($log);
        $this->assertEquals(12, $log->duration_minutes);
        $this->assertEquals(600, $log->reward_cash);
        $this->assertEquals(12, $log->reward_stars);
        $this->assertEquals($charB->id, $log->overtaken_by_character_id);

        // Check notification for Char A
        $this->assertTrue(
            GameNotification::where('character_id', $charA->id)
                ->where('type', 'control_point')
                ->exists()
        );

        // Check official in-game message for Char A
        $this->assertTrue(
            GameMessage::where('recipient_id', $charA->id)
                ->where('subject', 'like', '%Uitbetaling Controlepunt%')
                ->exists()
        );
    }
}
