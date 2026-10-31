<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\TimelinePost;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Pass6Bucket2AuditTest extends TestCase
{
    use RefreshDatabase;

    public function test_timeline_rejects_html_only_posts_and_comments_after_strip_tags(): void
    {
        $user = User::factory()->create(['is_onboarded' => true]);
        $character = Character::factory()->create([
            'user_id' => $user->id,
            'name' => 'TijdlijnTester',
        ]);

        $response = $this->actingAs($user)->post(route('timeline.store'), [
            'content' => '<b></b><i> </i>',
        ]);
        $response->assertSessionHas('error', 'Voer een geldig bericht van minimaal 2 tekens in.');
        $this->assertSame(0, TimelinePost::count());

        $post = TimelinePost::create([
            'character_id' => $character->id,
            'content' => 'Echt bericht op de tijdlijn',
            'likes_count' => 0,
        ]);

        $commentResponse = $this->actingAs($user)->post(route('timeline.comment', $post), [
            'content' => '<b> </b>',
        ]);
        $commentResponse->assertSessionHas('error', 'Voer een geldige reactie in.');
        $this->assertSame(0, $post->comments()->count());
    }

    public function test_timeline_like_toggles_and_updates_likes_count_atomically(): void
    {
        $user = User::factory()->create(['is_onboarded' => true]);
        $character = Character::factory()->create([
            'user_id' => $user->id,
            'name' => 'LikerPatient',
        ]);

        $post = TimelinePost::create([
            'character_id' => $character->id,
            'content' => 'Status bericht',
            'likes_count' => 0,
        ]);

        $this->actingAs($user)->postJson(route('timeline.like', $post), ['emoji' => '🔥'])
            ->assertOk()
            ->assertJsonPath('action', 'liked')
            ->assertJsonPath('likes_count', 1);

        $this->actingAs($user)->postJson(route('timeline.like', $post), ['emoji' => '🔥'])
            ->assertOk()
            ->assertJsonPath('action', 'unliked')
            ->assertJsonPath('likes_count', 0);
    }

    public function test_travel_controller_blocks_comatose_character_from_moving(): void
    {
        $user = User::factory()->create(['is_onboarded' => true]);
        $character = Character::factory()->create([
            'user_id' => $user->id,
            'current_location' => 'paviljoen_a',
            'cash' => 1000,
            'xp' => 0,
            'health' => 0,
            'coma_until' => now()->addHours(2),
        ]);

        $response = $this->actingAs($user)->post(route('travel.move'), [
            'location' => 'sportzaal',
        ]);

        $response->assertSessionHas('error');
        $character->refresh();
        $this->assertSame('paviljoen_a', $character->current_location);
        $this->assertSame(0, $character->xp);
    }

    public function test_character_remaining_seconds_helpers_use_ceil_and_preserve_prison_state(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-31 10:00:00.400'));

        try {
            $until = Carbon::parse('2026-10-31 10:00:01');
            $user = User::factory()->create(['is_onboarded' => true]);
            $character = Character::factory()->create([
                'user_id' => $user->id,
                'is_in_prison' => true,
                'prison_until' => $until,
                'leave_until' => $until,
                'sedated_until' => $until,
                'protection_until' => $until,
                'cash_protection_until' => $until,
                'vest_until' => $until,
                'health' => 0,
                'coma_until' => $until,
            ]);

            $this->assertSame(1, $character->remainingPrisonSeconds());
            $this->assertTrue((bool) $character->fresh()->is_in_prison);
            $character->is_in_prison = false;
            $character->prison_until = null;
            $this->assertSame(1, $character->getLeaveRemainingSeconds());
            $this->assertSame(1, $character->getSedatedRemainingSeconds());
            $this->assertSame(1, $character->getIsolationProtectionRemainingSeconds());
            $this->assertSame(1, $character->getCashProtectionRemainingSeconds());
            $this->assertSame(1, $character->getComaRemainingSeconds());
            $this->assertSame(1, $character->getVestRemainingSeconds());
        } finally {
            Carbon::setTestNow();
        }
    }
}
