<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\DirectMessage;
use App\Models\GameMessage;
use App\Models\GameNotification;
use App\Models\User;
use App\Services\WebPushService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class WebPushNotificationTest extends TestCase
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

    public function test_push_public_key_can_be_retrieved(): void
    {
        $user = User::factory()->create();
        $this->createCharacter($user);

        $response = $this->actingAs($user)->getJson('/push/key');

        $response->assertOk()
            ->assertJsonStructure(['public_key']);
    }

    public function test_push_subscription_can_be_stored_and_unsubscribed(): void
    {
        $user = User::factory()->create();
        $this->createCharacter($user);

        $subPayload = [
            'endpoint' => 'https://fcm.googleapis.com/fcm/send/test-endpoint-token-123',
            'public_key' => 'BPz_test_key_abc_123',
            'auth_token' => 'auth_secret_xyz',
            'content_encoding' => 'aesgcm',
        ];

        // 1. Subscribe
        $subResponse = $this->actingAs($user)->postJson('/push/subscribe', $subPayload);
        $subResponse->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('push_subscriptions', [
            'user_id' => $user->id,
            'endpoint' => 'https://fcm.googleapis.com/fcm/send/test-endpoint-token-123',
        ]);

        // 2. Status
        $statusResponse = $this->actingAs($user)->getJson('/push/status');
        $statusResponse->assertOk()
            ->assertJson(['subscribed' => true]);

        // 3. Unsubscribe
        $unsubResponse = $this->actingAs($user)->postJson('/push/unsubscribe', [
            'endpoint' => 'https://fcm.googleapis.com/fcm/send/test-endpoint-token-123',
        ]);
        $unsubResponse->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseMissing('push_subscriptions', [
            'endpoint' => 'https://fcm.googleapis.com/fcm/send/test-endpoint-token-123',
        ]);
    }

    public function test_game_notification_triggers_push_dispatch(): void
    {
        $user = User::factory()->create();
        $character = $this->createCharacter($user);

        $mockPush = Mockery::mock(WebPushService::class);
        $mockPush->shouldReceive('sendToCharacter')
            ->once()
            ->with(
                $character->id,
                'TBS Kliniek: Bestolen!',
                Mockery::any(),
                '/bank',
                null,
                Mockery::any()
            )
            ->andReturn(1);

        $this->app->instance(WebPushService::class, $mockPush);

        GameNotification::create([
            'character_id' => $character->id,
            'type' => 'theft',
            'title' => 'Bestolen!',
            'message' => 'Iemand heeft €500 van je gestolen!',
            'icon' => 'warning',
        ]);
    }

    public function test_game_message_triggers_push_dispatch(): void
    {
        $user = User::factory()->create();
        $character = $this->createCharacter($user);

        $mockPush = Mockery::mock(WebPushService::class);
        $mockPush->shouldReceive('sendToCharacter')
            ->once()
            ->with(
                $character->id,
                'TBS Kliniek: 🤝 Vriendschapsverzoek ontvangen',
                Mockery::any(),
                '/vrienden',
                null,
                Mockery::any()
            )
            ->andReturn(1);

        $this->app->instance(WebPushService::class, $mockPush);

        GameMessage::create([
            'recipient_id' => $character->id,
            'sender_id' => null,
            'subject' => '🤝 Vriendschapsverzoek ontvangen',
            'content' => 'Je hebt een nieuw vriendschapsverzoek!',
            'type' => 'system',
        ]);
    }

    public function test_player_direct_message_triggers_push_dispatch(): void
    {
        $senderUser = User::factory()->create();
        $senderChar = $this->createCharacter($senderUser, ['name' => 'Scarface']);

        $recipientUser = User::factory()->create();
        $recipientChar = $this->createCharacter($recipientUser, ['name' => 'AlCapone']);

        $mockPush = Mockery::mock(WebPushService::class);
        $mockPush->shouldReceive('sendToCharacter')
            ->once()
            ->with(
                $recipientChar->id,
                'Nieuw bericht van Scarface',
                'Hey kom naar de binnenplaats',
                '/chat',
                null,
                Mockery::any()
            )
            ->andReturn(1);

        $this->app->instance(WebPushService::class, $mockPush);

        DirectMessage::create([
            'sender_id' => $senderChar->id,
            'recipient_id' => $recipientChar->id,
            'type' => 'player',
            'message' => 'Hey kom naar de binnenplaats',
        ]);
    }
}
