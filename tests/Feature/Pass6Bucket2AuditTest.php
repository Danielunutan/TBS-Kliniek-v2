<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\TimelinePost;
use App\Models\User;
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
}
