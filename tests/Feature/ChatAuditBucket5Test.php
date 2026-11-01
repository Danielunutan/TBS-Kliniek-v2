<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\CinemaMessage;
use App\Models\CinemaRoom;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class ChatAuditBucket5Test extends TestCase
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

    public function test_player_can_send_chat_message_in_cinema_room(): void
    {
        [$user, $character] = $this->createInmate();

        $room = CinemaRoom::create([
            'title' => 'Gezellige Filmzaal',
            'slug' => 'gezellige-filmzaal',
            'host_character_id' => $character->id,
            'youtube_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'youtube_video_id' => 'dQw4w9WgXcQ',
            'is_official' => false,
            'is_closed' => false,
        ]);

        $response = $this->actingAs($user)->postJson(route('cinema.send-message', $room->id), [
            'message' => 'Wat een geweldige documentaire!',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => [
                'text' => 'Wat een geweldige documentaire!',
                'character_name' => $character->name,
                'can_delete' => true,
            ],
        ]);

        $this->assertDatabaseHas('cinema_messages', [
            'room_id' => $room->id,
            'character_id' => $character->id,
            'message' => 'Wat een geweldige documentaire!',
        ]);
    }

    public function test_send_message_strips_html_tags_for_xss_protection(): void
    {
        [$user, $character] = $this->createInmate();

        $room = CinemaRoom::create([
            'title' => 'XSS Test Zaal',
            'slug' => 'xss-test-zaal',
            'host_character_id' => $character->id,
            'youtube_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'youtube_video_id' => 'dQw4w9WgXcQ',
            'is_official' => false,
            'is_closed' => false,
        ]);

        $response = $this->actingAs($user)->postJson(route('cinema.send-message', $room->id), [
            'message' => '<script>alert("hacked")</script>Hallo <b>allemaal</b>!',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => [
                'text' => 'alert("hacked")Hallo allemaal!',
            ],
        ]);

        $msg = CinemaMessage::where('room_id', $room->id)->where('character_id', $character->id)->first();
        $this->assertNotNull($msg);
        $this->assertStringNotContainsString('<script>', $msg->message);
        $this->assertStringNotContainsString('<b>', $msg->message);
    }

    public function test_send_message_enforces_imprisoned_and_comatose_guards(): void
    {
        [$user, $character] = $this->createInmate();

        $room = CinemaRoom::create([
            'title' => 'Guard Zaal',
            'slug' => 'guard-zaal',
            'host_character_id' => $character->id,
            'youtube_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'youtube_video_id' => 'dQw4w9WgXcQ',
            'is_official' => false,
            'is_closed' => false,
        ]);

        // 1. Imprisoned check
        $character->update(['prison_until' => now()->addMinutes(10)]);
        $character->refresh();
        $this->assertTrue($character->isImprisoned());

        $prisonResponse = $this->actingAs($user)->postJson(route('cinema.send-message', $room->id), [
            'message' => 'Poging tot chat vanuit isoleercel',
        ]);
        $prisonResponse->assertStatus(422);
        $prisonResponse->assertJson(['success' => false]);
        $this->assertStringContainsString('isoleercel', $prisonResponse->json('message'));

        // 2. Comatose check
        $character->update([
            'prison_until' => null,
            'coma_until' => now()->addMinutes(10),
            'health' => 0,
        ]);
        $character->refresh();
        $this->assertTrue($character->isComatose());

        $comaResponse = $this->actingAs($user)->postJson(route('cinema.send-message', $room->id), [
            'message' => 'Poging tot chat in coma',
        ]);
        $comaResponse->assertStatus(422);
        $comaResponse->assertJson(['success' => false]);
        $this->assertStringContainsString('Code Blauw', $comaResponse->json('message'));
    }

    public function test_send_message_enforces_anti_spam_cooldown_and_duplicates(): void
    {
        [$user, $character] = $this->createInmate();

        $room = CinemaRoom::create([
            'title' => 'Anti Spam Zaal',
            'slug' => 'anti-spam-zaal',
            'host_character_id' => $character->id,
            'youtube_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'youtube_video_id' => 'dQw4w9WgXcQ',
            'is_official' => false,
            'is_closed' => false,
        ]);

        // Send first message
        $first = $this->actingAs($user)->postJson(route('cinema.send-message', $room->id), [
            'message' => 'Eerste unieke bericht in de zaal',
        ]);
        $first->assertStatus(200);

        // Immediate second message hits rapid-fire cooldown (2 seconds)
        $rapid = $this->actingAs($user)->postJson(route('cinema.send-message', $room->id), [
            'message' => 'Tweede bericht te snel',
        ]);
        $rapid->assertStatus(429);
        $this->assertStringContainsString('Wacht even 2 seconden', $rapid->json('message'));

        // Clear cooldown but test duplicate detection
        Cache::forget("cinema_chat_cooldown_{$character->id}");
        $duplicate = $this->actingAs($user)->postJson(route('cinema.send-message', $room->id), [
            'message' => 'Eerste unieke bericht in de zaal',
        ]);
        $duplicate->assertStatus(422);
        $this->assertStringContainsString('identieke bericht zojuist al verstuurd', $duplicate->json('message'));

        // Test character flooding
        Cache::forget("cinema_chat_cooldown_{$character->id}");
        $flood = $this->actingAs($user)->postJson(route('cinema.send-message', $room->id), [
            'message' => 'aaaaaaaaaaaaaaaaaaaaaa',
        ]);
        $flood->assertStatus(422);
        $this->assertStringContainsString('te veel herhalende tekens', $flood->json('message'));
    }

    public function test_link_validation_blocks_disallowed_external_links_for_players(): void
    {
        [$user, $character] = $this->createInmate();
        [$staffUser, $staffChar] = $this->createInmate([], isStaff: true);

        $room = CinemaRoom::create([
            'title' => 'Link Test Zaal',
            'slug' => 'link-test-zaal',
            'host_character_id' => $character->id,
            'youtube_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'youtube_video_id' => 'dQw4w9WgXcQ',
            'is_official' => false,
            'is_closed' => false,
        ]);

        // Disallowed phishing / external site
        $blocked = $this->actingAs($user)->postJson(route('cinema.send-message', $room->id), [
            'message' => 'Kijk eens op https://gratis-stars-tbs.com voor gratis items!',
        ]);
        $blocked->assertStatus(422);
        $this->assertStringContainsString('niet toegestaan', $blocked->json('message'));

        // Allowed YouTube link
        Cache::forget("cinema_chat_cooldown_{$character->id}");
        $allowedYt = $this->actingAs($user)->postJson(route('cinema.send-message', $room->id), [
            'message' => 'Check deze clip: https://youtube.com/watch?v=dQw4w9WgXcQ',
        ]);
        $allowedYt->assertStatus(200);

        // Allowed Discord link
        Cache::forget("cinema_chat_cooldown_{$character->id}");
        $allowedDc = $this->actingAs($user)->postJson(route('cinema.send-message', $room->id), [
            'message' => 'Kom gezellig in https://discord.gg/tbskliniek',
        ]);
        $allowedDc->assertStatus(200);

        // Staff can post any link
        $staffLink = $this->actingAs($staffUser)->postJson(route('cinema.send-message', $room->id), [
            'message' => 'Officiële aankondiging: https://rijksoverheid.nl/nieuws',
        ]);
        $staffLink->assertStatus(200);
    }

    public function test_author_host_and_moderator_can_delete_messages(): void
    {
        [$hostUser, $hostChar] = $this->createInmate();
        [$authorUser, $authorChar] = $this->createInmate();
        [$otherUser, $otherChar] = $this->createInmate();
        [$staffUser, $staffChar] = $this->createInmate([], isStaff: true);

        $room = CinemaRoom::create([
            'title' => 'Moderation Zaal',
            'slug' => 'moderation-zaal',
            'host_character_id' => $hostChar->id,
            'youtube_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'youtube_video_id' => 'dQw4w9WgXcQ',
            'is_official' => false,
            'is_closed' => false,
        ]);

        // Create 3 messages by author
        $msg1 = CinemaMessage::create([
            'room_id' => $room->id,
            'character_id' => $authorChar->id,
            'message' => 'Bericht 1 - Auteur verwijdert dit',
        ]);

        $msg2 = CinemaMessage::create([
            'room_id' => $room->id,
            'character_id' => $authorChar->id,
            'message' => 'Bericht 2 - Host verwijdert dit',
        ]);

        $msg3 = CinemaMessage::create([
            'room_id' => $room->id,
            'character_id' => $authorChar->id,
            'message' => 'Bericht 3 - Staff verwijdert dit',
        ]);

        // 1. Random other user CANNOT delete author's message
        $forbidden = $this->actingAs($otherUser)->postJson(route('cinema.send-message', $room->id), [
            'action' => 'delete',
            'delete_id' => $msg1->id,
        ]);
        $forbidden->assertStatus(403);
        $this->assertDatabaseHas('cinema_messages', ['id' => $msg1->id]);

        // 2. Author CAN delete their own message
        $authorDelete = $this->actingAs($authorUser)->postJson(route('cinema.send-message', $room->id), [
            'action' => 'delete',
            'delete_id' => $msg1->id,
        ]);
        $authorDelete->assertStatus(200);
        $authorDelete->assertJson(['success' => true, 'deleted_id' => $msg1->id]);
        $this->assertDatabaseMissing('cinema_messages', ['id' => $msg1->id]);

        // 3. Room Host CAN delete messages in their room
        $hostDelete = $this->actingAs($hostUser)->postJson(route('cinema.send-message', $room->id), [
            'action' => 'delete',
            'delete_id' => $msg2->id,
        ]);
        $hostDelete->assertStatus(200);
        $this->assertDatabaseMissing('cinema_messages', ['id' => $msg2->id]);

        // 4. Staff CAN delete messages
        $staffDelete = $this->actingAs($staffUser)->postJson(route('cinema.send-message', $room->id), [
            'action' => 'delete',
            'delete_id' => $msg3->id,
        ]);
        $staffDelete->assertStatus(200);
        $this->assertDatabaseMissing('cinema_messages', ['id' => $msg3->id]);

        // 5. Polling endpoint returns deleted_ids
        $poll = $this->actingAs($authorUser)->getJson(route('cinema.get-messages', $room->id));
        $poll->assertStatus(200);
        $deletedIds = $poll->json('deleted_ids');
        $this->assertIsArray($deletedIds);
        $this->assertContains($msg1->id, $deletedIds);
        $this->assertContains($msg2->id, $deletedIds);
        $this->assertContains($msg3->id, $deletedIds);
    }

    public function test_queue_adder_can_delete_own_video_and_other_player_cannot(): void
    {
        [$hostUser, $hostChar] = $this->createInmate();
        [$viewerUser, $viewerChar] = $this->createInmate();
        [$otherUser, $otherChar] = $this->createInmate();

        $room = CinemaRoom::create([
            'title' => 'Queue Auth Zaal',
            'slug' => 'queue-auth-zaal',
            'host_character_id' => $hostChar->id,
            'youtube_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'youtube_video_id' => 'dQw4w9WgXcQ',
            'video_queue' => [],
            'is_official' => false,
            'is_closed' => false,
        ]);

        // Viewer adds a video
        $addRes = $this->actingAs($viewerUser)->postJson(route('cinema.queue.add', $room->slug), [
            'youtube_url' => 'https://www.youtube.com/watch?v=1la4bUKxMJE',
            'title' => 'Viewer Video',
        ]);
        $addRes->assertStatus(200);

        $room->refresh();
        $queue = $room->getQueue();
        $this->assertCount(1, $queue);
        $queueId = $queue[0]['id'];

        // Other user cannot remove viewer's video
        $otherRemove = $this->actingAs($otherUser)->postJson(route('cinema.queue.remove', [$room->slug, $queueId]));
        $otherRemove->assertStatus(403);
        $room->refresh();
        $this->assertCount(1, $room->getQueue());

        // Viewer (the adder) CAN remove their own queued video
        $viewerRemove = $this->actingAs($viewerUser)->postJson(route('cinema.queue.remove', [$room->slug, $queueId]));
        $viewerRemove->assertStatus(200);
        $room->refresh();
        $this->assertCount(0, $room->getQueue());
    }

    public function test_youtube_shorts_and_live_urls_are_correctly_extracted(): void
    {
        // Standard video
        $this->assertEquals('dQw4w9WgXcQ', CinemaRoom::extractYoutubeId('https://www.youtube.com/watch?v=dQw4w9WgXcQ'));

        // Short link
        $this->assertEquals('dQw4w9WgXcQ', CinemaRoom::extractYoutubeId('https://youtu.be/dQw4w9WgXcQ'));

        // YouTube Shorts URL
        $this->assertEquals('dQw4w9WgXcQ', CinemaRoom::extractYoutubeId('https://www.youtube.com/shorts/dQw4w9WgXcQ'));

        // YouTube Live URL
        $this->assertEquals('dQw4w9WgXcQ', CinemaRoom::extractYoutubeId('https://www.youtube.com/live/dQw4w9WgXcQ'));

        // Raw 11-char ID
        $this->assertEquals('dQw4w9WgXcQ', CinemaRoom::extractYoutubeId('dQw4w9WgXcQ'));
    }

    public function test_cinema_index_uses_data_confirm_attributes(): void
    {
        [$user, $character] = $this->createInmate();

        CinemaRoom::create([
            'title' => 'Index Test Zaal',
            'slug' => 'index-test-zaal',
            'host_character_id' => $character->id,
            'youtube_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'youtube_video_id' => 'dQw4w9WgXcQ',
            'is_official' => false,
            'is_closed' => false,
        ]);

        $response = $this->actingAs($user)->get(route('cinema.index'));
        $response->assertStatus(200);

        // Verify data-confirm is used instead of broken onsubmit="return confirm(...)"
        $response->assertSee('data-confirm="Weet je zeker dat je deze zaal wilt sluiten?"', false);
        $response->assertDontSee('onsubmit="return confirm(\'Weet je zeker dat je deze zaal wilt sluiten?\');"', false);

        $indexBlade = file_get_contents(resource_path('views/game/cinema/index.blade.php'));
        $this->assertStringNotContainsString('onsubmit="return confirm(', $indexBlade);
        $this->assertStringContainsString('data-confirm="Weet je zeker dat je deze zaal wilt sluiten?"', $indexBlade);
    }
}
