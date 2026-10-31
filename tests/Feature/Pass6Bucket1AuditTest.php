<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\CharacterConnectFourStat;
use App\Models\Friend;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Pass6Bucket1AuditTest extends TestCase
{
    use RefreshDatabase;

    public function test_connect_four_records_today_respect_when_respect_is_awarded(): void
    {
        $user = User::factory()->create(['is_onboarded' => true]);
        $character = Character::factory()->create([
            'user_id' => $user->id,
            'respect' => 24,
            'today_respect' => 0,
            'today_respect_date' => null,
            'cash' => 1000,
        ]);

        $response = $this->actingAs($user)->postJson(route('connect_four.finish'), [
            'winner' => 'player',
            'moves' => 8,
        ]);

        $response->assertOk()->assertJsonPath('success', true);
        $character->refresh();
        $this->assertSame(27, $character->respect);
        $this->assertSame(3.0, (float) $character->today_respect);
        $this->assertSame(now()->format('Y-m-d'), $character->today_respect_date);
    }

    public function test_connect_four_skip_cooldown_with_stars_resets_cooldown(): void
    {
        $user = User::factory()->create(['is_onboarded' => true]);
        $character = Character::factory()->create([
            'user_id' => $user->id,
            'stars' => 20,
        ]);

        CharacterConnectFourStat::create([
            'character_id' => $character->id,
            'matches_played' => 5,
            'cooldown_until' => now()->addMinutes(10),
        ]);

        $response = $this->actingAs($user)->post(route('connect_four.skip_cooldown_stars'));
        $response->assertRedirect(route('connect_four.index'));

        $character->refresh();
        $this->assertSame(10, $character->stars);
        $stat = CharacterConnectFourStat::where('character_id', $character->id)->firstOrFail();
        $this->assertFalse($stat->isOnCooldown());
    }

    public function test_friend_controller_formats_cooldown_hours_as_integer_and_handles_add(): void
    {
        $user = User::factory()->create(['is_onboarded' => true]);
        $character = Character::factory()->create([
            'user_id' => $user->id,
            'name' => 'PatientAlpha',
            'last_friends_mass_message_at' => now()->subMinutes(35),
        ]);

        $targetUser = User::factory()->create(['is_onboarded' => true]);
        $target = Character::factory()->create([
            'user_id' => $targetUser->id,
            'name' => 'PatientBeta',
        ]);

        $this->actingAs($user)->post(route('friends.add'), [
            'friend_name' => $target->name,
        ])->assertRedirect();

        $this->assertSame(1, Friend::where('character_id', $character->id)->where('friend_id', $target->id)->count());

        $response = $this->actingAs($user)->post(route('friends.mass-message'), [
            'subject' => 'Test',
            'message' => 'Test bericht',
        ]);
        $response->assertSessionHas('error', '⏳ Massabericht naar vrienden kan maar 1x per 24 uur verzonden worden. Wacht nog 24 uur.');
    }
}
