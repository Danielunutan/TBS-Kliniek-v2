<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\GameMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MessageBulkActionTest extends TestCase
{
    use RefreshDatabase;

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
            'cash' => 5000,
            'bank' => 10000,
            'stars' => 10,
            'respect' => 10,
            'title' => 'Modelpatiënt',
        ], $attrs));
    }

    public function test_inbox_displays_unread_messages_without_auto_marking_them(): void
    {
        $user = User::factory()->create();
        $char = $this->createCharacter($user);

        GameMessage::create([
            'recipient_id' => $char->id,
            'sender_id' => null,
            'sender_name' => 'Directie TBS',
            'subject' => 'Officiële Waarschuwing',
            'content' => 'Gedragscorrectie vereist.',
            'type' => 'system',
            'is_read' => false,
        ]);

        $response = $this->actingAs($user)->get(route('messages.index'));
        $response->assertRedirect(route('chat.index', ['tab' => 'direct']));

        // Message should still be unread until explicitly marked!
        $this->assertDatabaseHas('game_messages', [
            'recipient_id' => $char->id,
            'subject' => 'Officiële Waarschuwing',
            'is_read' => false,
        ]);
    }

    public function test_player_can_mark_all_messages_as_read(): void
    {
        $user = User::factory()->create();
        $char = $this->createCharacter($user);

        GameMessage::create([
            'recipient_id' => $char->id,
            'sender_id' => null,
            'sender_name' => 'Directie TBS',
            'subject' => 'Bericht 1',
            'content' => 'Inhoud 1',
            'type' => 'system',
            'is_read' => false,
        ]);

        GameMessage::create([
            'recipient_id' => $char->id,
            'sender_id' => null,
            'sender_name' => 'Beveiliging',
            'subject' => 'Bericht 2',
            'content' => 'Inhoud 2',
            'type' => 'theft',
            'is_read' => false,
        ]);

        $this->assertEquals(2, GameMessage::where('recipient_id', $char->id)->where('is_read', false)->count());

        $response = $this->actingAs($user)->post(route('messages.mark-all-read'));
        $response->assertSessionHas('success');

        $this->assertEquals(0, GameMessage::where('recipient_id', $char->id)->where('is_read', false)->count());
        $this->assertEquals(2, GameMessage::where('recipient_id', $char->id)->where('is_read', true)->count());
    }

    public function test_player_can_mark_single_message_as_read(): void
    {
        $user = User::factory()->create();
        $char = $this->createCharacter($user);

        $msg = GameMessage::create([
            'recipient_id' => $char->id,
            'sender_id' => null,
            'sender_name' => 'Directie',
            'subject' => 'Privé brief',
            'content' => 'Geheim',
            'type' => 'system',
            'is_read' => false,
        ]);

        $response = $this->actingAs($user)->post(route('messages.mark-read', $msg));
        $response->assertSessionHas('success');

        $this->assertTrue($msg->fresh()->is_read);
    }

    public function test_player_cannot_mark_other_players_message_as_read(): void
    {
        $userA = User::factory()->create();
        $charA = $this->createCharacter($userA);

        $userB = User::factory()->create();
        $charB = $this->createCharacter($userB);

        $msg = GameMessage::create([
            'recipient_id' => $charB->id,
            'sender_id' => null,
            'sender_name' => 'Directie',
            'subject' => 'Voor B',
            'content' => 'Prive',
            'type' => 'system',
            'is_read' => false,
        ]);

        $response = $this->actingAs($userA)->post(route('messages.mark-read', $msg));
        $response->assertStatus(403);
    }

    public function test_player_can_destroy_all_messages_in_inbox(): void
    {
        $user = User::factory()->create();
        $char = $this->createCharacter($user);

        GameMessage::create([
            'recipient_id' => $char->id,
            'sender_id' => null,
            'sender_name' => 'Directie TBS',
            'subject' => 'Weg ermee 1',
            'content' => 'Test',
            'type' => 'system',
            'is_read' => true,
        ]);

        GameMessage::create([
            'recipient_id' => $char->id,
            'sender_id' => null,
            'sender_name' => 'Dief',
            'subject' => 'Weg ermee 2',
            'content' => 'Test',
            'type' => 'theft',
            'is_read' => true,
        ]);

        $this->assertEquals(2, GameMessage::where('recipient_id', $char->id)->count());

        $response = $this->actingAs($user)->delete(route('messages.destroy-all'), [
            'scope' => 'filter',
            'filter' => 'all',
        ]);
        $response->assertSessionHas('success');

        $this->assertEquals(0, GameMessage::where('recipient_id', $char->id)->count());
    }

    public function test_player_can_destroy_only_read_messages(): void
    {
        $user = User::factory()->create();
        $char = $this->createCharacter($user);

        // 1 read message
        GameMessage::create([
            'recipient_id' => $char->id,
            'sender_id' => null,
            'sender_name' => 'Oud',
            'subject' => 'Reeds gelezen',
            'content' => 'Test',
            'type' => 'system',
            'is_read' => true,
        ]);

        // 1 unread message
        GameMessage::create([
            'recipient_id' => $char->id,
            'sender_id' => null,
            'sender_name' => 'Nieuw',
            'subject' => 'Belangrijk ongelezen',
            'content' => 'Niet verwijderen',
            'type' => 'system',
            'is_read' => false,
        ]);

        $response = $this->actingAs($user)->delete(route('messages.destroy-all'), [
            'scope' => 'read_only',
        ]);
        $response->assertSessionHas('success');

        $this->assertEquals(1, GameMessage::where('recipient_id', $char->id)->count());
        $this->assertDatabaseHas('game_messages', [
            'recipient_id' => $char->id,
            'subject' => 'Belangrijk ongelezen',
            'is_read' => false,
        ]);
    }
}
