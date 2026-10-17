<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\Report;
use App\Models\User;
use App\Services\ChatBotService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LiveChatTest extends TestCase
{
    use RefreshDatabase;

    private function createInmate(array $attributes = []): array
    {
        $user = User::factory()->create();
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

    public function test_player_can_view_chat_page(): void
    {
        [$user, $character] = $this->createInmate();

        $response = $this->actingAs($user)->get(route('chat.index'));

        $response->assertOk();
        $response->assertViewIs('game.chat.index');
        $response->assertSee('Kliniek Plein');
    }

    public function test_player_can_send_and_retrieve_public_messages(): void
    {
        [$user, $character] = $this->createInmate();

        $sendResponse = $this->actingAs($user)->postJson(route('chat.public.send'), [
            'channel' => 'global',
            'message' => 'Gezellig op de binnenplaats!',
        ]);

        $sendResponse->assertOk();
        $sendResponse->assertJson(['success' => true]);

        $this->assertDatabaseHas('chat_messages', [
            'character_id' => $character->id,
            'channel' => 'global',
            'message' => 'Gezellig op de binnenplaats!',
        ]);

        $fetchResponse = $this->actingAs($user)->getJson(route('chat.public.messages', ['channel' => 'global']));
        $fetchResponse->assertOk();
        $fetchResponse->assertJsonFragment([
            'message' => 'Gezellig op de binnenplaats!',
            'name' => $character->name,
        ]);
    }

    public function test_public_chat_rate_limits_rapid_messages(): void
    {
        [$user, $character] = $this->createInmate();

        $this->actingAs($user)->postJson(route('chat.public.send'), [
            'channel' => 'global',
            'message' => 'Eerste bericht',
        ])->assertOk();

        // Rapid second message within 2 seconds
        $rapidResponse = $this->actingAs($user)->postJson(route('chat.public.send'), [
            'channel' => 'global',
            'message' => 'Tweede snelle bericht',
        ]);

        $rapidResponse->assertStatus(429);
        $rapidResponse->assertJsonFragment(['message' => 'Rustig aan! Wacht even 2 seconden voor je volgende bericht.']);
    }

    public function test_player_can_send_and_receive_direct_messages(): void
    {
        [$user1, $char1] = $this->createInmate(['name' => 'SenderInmate']);
        [$user2, $char2] = $this->createInmate(['name' => 'ReceiverInmate']);

        // User 1 sends DM to User 2
        $sendResponse = $this->actingAs($user1)->postJson(route('chat.direct.send', $char2->id), [
            'message' => 'Psst, heb jij nog wat sigaretten?',
        ]);

        $sendResponse->assertOk();
        $sendResponse->assertJson(['success' => true]);

        $this->assertDatabaseHas('direct_messages', [
            'sender_id' => $char1->id,
            'recipient_id' => $char2->id,
            'message' => 'Psst, heb jij nog wat sigaretten?',
            'is_read' => false,
        ]);

        // User 2 checks unread count
        $unreadResponse = $this->actingAs($user2)->getJson(route('chat.unread-count'));
        $unreadResponse->assertOk();
        $unreadResponse->assertJson(['unread' => 1]);

        // User 2 opens the DM conversation with User 1
        $threadResponse = $this->actingAs($user2)->getJson(route('chat.direct.messages', $char1->id));
        $threadResponse->assertOk();
        $threadResponse->assertJsonFragment([
            'message' => 'Psst, heb jij nog wat sigaretten?',
        ]);

        // After reading, is_read is true and unread count is 0
        $this->assertDatabaseHas('direct_messages', [
            'sender_id' => $char1->id,
            'recipient_id' => $char2->id,
            'is_read' => true,
        ]);

        $unreadAfter = $this->actingAs($user2)->getJson(route('chat.unread-count'));
        $unreadAfter->assertJson(['unread' => 0]);
    }

    public function test_chatbot_service_sends_level_up_card(): void
    {
        [$user, $character] = $this->createInmate();

        /** @var ChatBotService $chatBot */
        $chatBot = app(ChatBotService::class);
        $chatBot->sendLevelUp($character, 5, ['Max Health +10', 'Max Energie +5']);

        $this->assertDatabaseHas('direct_messages', [
            'sender_id' => null,
            'recipient_id' => $character->id,
            'type' => 'level_up',
        ]);

        $threadResponse = $this->actingAs($user)->getJson(route('chat.direct.messages', 0));
        $threadResponse->assertOk();
        $threadResponse->assertJsonFragment([
            'type' => 'level_up',
            'level' => 5,
        ]);
    }

    public function test_chatbot_service_sends_kluiscode_voucher_and_vault_prefills(): void
    {
        [$user, $character] = $this->createInmate();

        /** @var ChatBotService $chatBot */
        $chatBot = app(ChatBotService::class);
        $chatBot->sendVaultCode($character, 'TEST-VAULT-777', 'Geheime Sponsor Buit');

        $this->assertDatabaseHas('direct_messages', [
            'sender_id' => null,
            'recipient_id' => $character->id,
            'type' => 'kluiscode',
        ]);

        // Verify thread has kluiscode voucher
        $threadResponse = $this->actingAs($user)->getJson(route('chat.direct.messages', 0));
        $threadResponse->assertOk();
        $threadResponse->assertJsonFragment([
            'type' => 'kluiscode',
            'code' => 'TEST-VAULT-777',
        ]);

        // Verify vault page accepts ?code=TEST-VAULT-777 and prefills
        $vaultResponse = $this->actingAs($user)->get(route('vault.index', ['code' => 'TEST-VAULT-777']));
        $vaultResponse->assertOk();
        $vaultResponse->assertSee('TEST-VAULT-777');
    }

    public function test_player_can_search_other_players_for_chat(): void
    {
        [$user, $character] = $this->createInmate(['name' => 'SearcherBob']);
        $this->createInmate(['name' => 'DraculaVampire']);
        $this->createInmate(['name' => 'ScarfaceTony']);

        $response = $this->actingAs($user)->getJson(route('chat.search-players', ['q' => 'Dracula']));

        $response->assertOk();
        $response->assertJsonCount(1, 'players');
        $response->assertJsonFragment(['name' => 'DraculaVampire']);
    }

    public function test_public_messages_include_respect_avatar_and_staff_badges(): void
    {
        $ownerUser = User::factory()->create(['role' => 'owner']);
        $ownerChar = Character::create([
            'user_id' => $ownerUser->id,
            'name' => 'DirecteurMax',
            'cash' => 1000,
            'bank' => 5000,
            'stars' => 50,
            'level' => 10,
            'xp' => 1000,
            'health' => 100,
            'max_health' => 100,
            'energy' => 100,
            'max_energy' => 100,
            'respect' => 777,
        ]);

        $this->actingAs($ownerUser)->postJson(route('chat.public.send'), [
            'channel' => 'global',
            'message' => 'Orde op het plein a.u.b.!',
        ])->assertOk();

        $fetchResponse = $this->actingAs($ownerUser)->getJson(route('chat.public.messages', ['channel' => 'global']));
        $fetchResponse->assertOk();
        $fetchResponse->assertJsonFragment([
            'name' => 'DirecteurMax',
            'respect' => 777,
            'is_owner' => true,
            'staff_role' => 'Eigenaar',
        ]);
    }

    public function test_player_can_report_inmate_and_chat_message(): void
    {
        [$reporterUser, $reporterChar] = $this->createInmate(['name' => 'GoodCitizen']);
        [$badGuyUser, $badGuyChar] = $this->createInmate(['name' => 'BadInmate']);

        $reportCharResponse = $this->actingAs($reporterUser)->postJson(route('report.store'), [
            'reported_type' => 'character',
            'reported_id' => $badGuyChar->id,
            'reason' => 'harassment',
            'details' => 'Deze speler scheldt iedereen uit.',
        ]);

        $reportCharResponse->assertOk();
        $reportCharResponse->assertJson(['success' => true]);

        $this->assertDatabaseHas('reports', [
            'reporter_id' => $reporterChar->id,
            'reported_type' => 'character',
            'reported_id' => $badGuyChar->id,
            'reported_name' => 'BadInmate',
            'status' => 'pending',
        ]);
    }

    public function test_moderator_can_resolve_report(): void
    {
        $modUser = User::factory()->create(['role' => 'moderator']);
        $modChar = Character::create([
            'user_id' => $modUser->id,
            'name' => 'ZaalwachtBram',
            'cash' => 1000,
            'bank' => 5000,
            'stars' => 50,
            'level' => 5,
            'xp' => 500,
            'health' => 100,
            'max_health' => 100,
            'energy' => 100,
            'max_energy' => 100,
            'respect' => 50,
        ]);

        [$victimUser, $victimChar] = $this->createInmate(['name' => 'Slachtoffer']);

        $report = Report::create([
            'reporter_id' => $victimChar->id,
            'reported_type' => 'character',
            'reported_id' => 999,
            'reported_name' => 'TrollPlayer',
            'reason' => 'cheating',
            'details' => 'Gebruikt macros of speedhacks',
            'status' => 'pending',
        ]);

        $resolveResponse = $this->actingAs($modUser)->post(route('admin.moderation.report.resolve', $report));
        $resolveResponse->assertRedirect();

        $this->assertDatabaseHas('reports', [
            'id' => $report->id,
            'status' => 'resolved',
            'handled_by' => $modChar->id,
        ]);
    }
}
