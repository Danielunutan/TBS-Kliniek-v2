<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\CinemaRoom;
use App\Models\DirectMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CinemaWatchpartyFeatureTest extends TestCase
{
    use RefreshDatabase;

    private function createInmate(array $attributes = [], bool $isStaff = false): array
    {
        $user = User::factory()->create(['role' => $isStaff ? 'admin' : 'player']);
        $character = Character::create(array_merge([
            'user_id' => $user->id,
            'name' => 'Inmate'.rand(1000, 9999),
            'cash' => 1000,
            'bank' => 5000,
            'stars' => 50,
            'level' => 1,
            'xp' => 0,
            'health' => 100,
            'max_health' => 100,
            'energy' => 100,
            'max_energy' => 100,
            'respect' => 10,
        ], $attributes));

        return [$user, $character];
    }

    public function test_cinema_lobby_shows_active_rooms_and_hides_closed_rooms(): void
    {
        [$user, $character] = $this->createInmate();

        $openRoom = CinemaRoom::create([
            'title' => 'Open Watchparty',
            'slug' => 'open-watchparty',
            'host_character_id' => $character->id,
            'youtube_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'youtube_video_id' => 'dQw4w9WgXcQ',
            'is_official' => false,
            'is_closed' => false,
        ]);

        $closedRoom = CinemaRoom::create([
            'title' => 'Gesloten Watchparty',
            'slug' => 'gesloten-watchparty',
            'host_character_id' => $character->id,
            'youtube_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'youtube_video_id' => 'dQw4w9WgXcQ',
            'is_official' => false,
            'is_closed' => true,
        ]);

        $response = $this->actingAs($user)->get(route('cinema.index'));

        $response->assertStatus(200);
        $response->assertSee('Open Watchparty');
        $response->assertDontSee('Gesloten Watchparty');
    }

    public function test_host_can_close_room_and_closed_room_redirects(): void
    {
        [$user, $character] = $this->createInmate();

        $room = CinemaRoom::create([
            'title' => 'My Movie Night',
            'slug' => 'my-movie-night',
            'host_character_id' => $character->id,
            'youtube_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'youtube_video_id' => 'dQw4w9WgXcQ',
            'is_official' => false,
            'is_closed' => false,
        ]);

        // Host closes the room
        $response = $this->actingAs($user)->post(route('cinema.close', $room->slug));
        $response->assertRedirect(route('cinema.index'));

        $room->refresh();
        $this->assertTrue($room->is_closed);

        // Visiting closed room redirects to cinema index
        $visitResponse = $this->actingAs($user)->get(route('cinema.show', $room->slug));
        $visitResponse->assertRedirect(route('cinema.index'));
    }

    public function test_non_host_cannot_close_room(): void
    {
        [$hostUser, $hostChar] = $this->createInmate();
        [$otherUser, $otherChar] = $this->createInmate();

        $room = CinemaRoom::create([
            'title' => 'Host Only Room',
            'slug' => 'host-only-room',
            'host_character_id' => $hostChar->id,
            'youtube_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'youtube_video_id' => 'dQw4w9WgXcQ',
            'is_official' => false,
            'is_closed' => false,
        ]);

        $response = $this->actingAs($otherUser)->post(route('cinema.close', $room->slug));
        $response->assertSessionHas('error');

        $room->refresh();
        $this->assertFalse($room->is_closed);
    }

    public function test_users_can_add_to_queue_and_host_can_play_and_remove(): void
    {
        [$hostUser, $hostChar] = $this->createInmate();
        [$viewerUser, $viewerChar] = $this->createInmate();

        $room = CinemaRoom::create([
            'title' => 'Queue Testing Zaal',
            'slug' => 'queue-testing-zaal',
            'host_character_id' => $hostChar->id,
            'youtube_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'youtube_video_id' => 'dQw4w9WgXcQ',
            'video_queue' => [],
            'is_official' => false,
            'is_closed' => false,
        ]);

        // Viewer adds a video to queue
        $addResponse = $this->actingAs($viewerUser)->postJson(route('cinema.queue.add', $room->slug), [
            'youtube_url' => 'https://www.youtube.com/watch?v=1la4bUKxMJE',
            'title' => 'Misdaad Docu Deel 1',
        ]);

        $addResponse->assertStatus(200);
        $addResponse->assertJson(['success' => true]);

        $room->refresh();
        $queue = $room->getQueue();
        $this->assertCount(1, $queue);
        $this->assertEquals('1la4bUKxMJE', $queue[0]['youtube_video_id']);
        $queueId = $queue[0]['id'];

        // Host plays the video from the queue
        $playResponse = $this->actingAs($hostUser)->postJson(route('cinema.queue.play', [$room->slug, $queueId]));
        $playResponse->assertStatus(200);
        $playResponse->assertJson(['success' => true, 'video_id' => '1la4bUKxMJE']);

        $room->refresh();
        $this->assertEquals('1la4bUKxMJE', $room->youtube_video_id);
        $this->assertCount(0, $room->getQueue());
    }

    public function test_staff_creating_official_room_broadcasts_directie_chat_to_all_characters(): void
    {
        [$staffUser, $staffChar] = $this->createInmate([], isStaff: true);
        [$charAUser, $charA] = $this->createInmate();
        [$charBUser, $charB] = $this->createInmate();

        $response = $this->actingAs($staffUser)->post(route('cinema.create'), [
            'title' => 'Staff Special Stream',
            'youtube_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'is_official' => 1,
        ]);

        $response->assertRedirect();

        $room = CinemaRoom::where('title', 'Staff Special Stream')->first();
        $this->assertNotNull($room);
        $this->assertTrue($room->is_official);

        // Verify DirectMessage with sender_id = null (Directie bot) was sent to characters
        $noticeA = DirectMessage::where('recipient_id', $charA->id)
            ->whereNull('sender_id')
            ->where('type', 'system')
            ->first();
        $this->assertNotNull($noticeA, 'Character A should receive Directie Chat notice');
        $this->assertStringContainsString('Staff Special Stream', $noticeA->message);

        $noticeB = DirectMessage::where('recipient_id', $charB->id)
            ->whereNull('sender_id')
            ->where('type', 'system')
            ->first();
        $this->assertNotNull($noticeB, 'Character B should receive Directie Chat notice');
    }

    public function test_realtime_polling_endpoint_returns_queue_and_status(): void
    {
        [$user, $character] = $this->createInmate();

        $room = CinemaRoom::create([
            'title' => 'Realtime Zaal',
            'slug' => 'realtime-zaal',
            'host_character_id' => $character->id,
            'youtube_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'youtube_video_id' => 'dQw4w9WgXcQ',
            'video_queue' => [
                [
                    'id' => 'test-uuid-1',
                    'title' => 'Test Video',
                    'youtube_url' => 'https://youtu.be/dQw4w9WgXcQ',
                    'youtube_video_id' => 'dQw4w9WgXcQ',
                    'thumbnail' => 'https://img.youtube.com/vi/dQw4w9WgXcQ/mqdefault.jpg',
                    'added_by_name' => 'Host',
                    'added_at' => '20:00',
                ],
            ],
            'is_official' => false,
            'is_closed' => false,
        ]);

        $response = $this->actingAs($user)->getJson(route('cinema.get-messages', $room->id));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'is_closed' => false,
            'video_id' => 'dQw4w9WgXcQ',
        ]);
        $this->assertCount(1, $response->json('video_queue'));
        $this->assertArrayHasKey('playback_seconds', $response->json());
    }

    public function test_host_can_sync_playback_time_and_non_host_is_forbidden(): void
    {
        [$hostUser, $hostChar] = $this->createInmate();
        [$viewerUser, $viewerChar] = $this->createInmate();

        $room = CinemaRoom::create([
            'title' => 'Sync Zaal',
            'slug' => 'sync-zaal',
            'host_character_id' => $hostChar->id,
            'youtube_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'youtube_video_id' => 'dQw4w9WgXcQ',
            'video_started_at' => now(),
            'seek_offset' => 0,
            'is_official' => false,
            'is_closed' => false,
        ]);

        // Host syncs playback to 45 seconds
        $syncResponse = $this->actingAs($hostUser)->postJson(route('cinema.sync', $room->slug), [
            'current_seconds' => 45,
        ]);

        $syncResponse->assertStatus(200);
        $syncResponse->assertJson(['success' => true]);
        $this->assertGreaterThanOrEqual(45, $syncResponse->json('playback_seconds'));

        // Viewer trying to sync gets 403
        $viewerSync = $this->actingAs($viewerUser)->postJson(route('cinema.sync', $room->slug), [
            'current_seconds' => 10,
        ]);
        $viewerSync->assertStatus(403);
    }
}
