<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\DirectMessage;
use App\Models\GameNotification;
use App\Models\Gang;
use App\Models\Report;
use App\Models\User;
use App\Services\ChatBotService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
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
        $response->assertSee('Bende Chat');

        // And when in a gang
        $gang = Gang::create([
            'name' => 'Bloedbroeders',
            'tag' => 'BB',
            'specialty' => 'Vleugel A',
            'leader_character_id' => $character->id,
            'gang_stars' => 150,
            'has_gang_chat' => true,
        ]);
        $character->update(['gang_id' => $gang->id]);
        $user->unsetRelation('character');

        $responseGang = $this->actingAs($user)->get(route('chat.index', ['tab' => 'gang']));
        $responseGang->assertOk();
        $responseGang->assertSee('Bloedbroeders');
        $responseGang->assertSee('[BB]');
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

    public function test_chat_anti_spam_blocks_duplicate_messages_and_flooding(): void
    {
        [$user, $character] = $this->createInmate();

        // 1. Send first message -> succeeds
        $res1 = $this->actingAs($user)->postJson(route('chat.public.send'), [
            'channel' => 'global',
            'message' => 'Eerste testbericht!',
        ]);
        $res1->assertOk();

        // 2. Immediate send (cooldown) -> 429
        $res2 = $this->actingAs($user)->postJson(route('chat.public.send'), [
            'channel' => 'global',
            'message' => 'Tweede bericht meteen',
        ]);
        $res2->assertStatus(429);
        $res2->assertJson(['success' => false]);
        $this->assertStringContainsString('Rustig aan', $res2->json('message'));

        // Clear cooldown cache to test duplicate protection
        Cache::forget("chat_cooldown_{$character->id}_public");

        // 3. Duplicate message -> 422
        $res3 = $this->actingAs($user)->postJson(route('chat.public.send'), [
            'channel' => 'global',
            'message' => 'Eerste testbericht!',
        ]);
        $res3->assertStatus(422);
        $this->assertStringContainsString('Anti-spam', $res3->json('message'));

        // Clear cache to test character flooding
        Cache::forget("chat_cooldown_{$character->id}_public");

        // 4. Repeated character flooding (e.g. 20 a's) -> 422
        $res4 = $this->actingAs($user)->postJson(route('chat.public.send'), [
            'channel' => 'global',
            'message' => 'Hallo aaaaaaaaaaaaaaaaaaaaaaaa wat doe je',
        ]);
        $res4->assertStatus(422);
        $this->assertStringContainsString('herhalende tekens', $res4->json('message'));
    }

    public function test_poll_incoming_endpoint_returns_unread_dms_and_newest_dm_info(): void
    {
        [$senderUser, $senderChar] = $this->createInmate(['name' => 'SecretSender']);
        [$receiverUser, $receiverChar] = $this->createInmate(['name' => 'RecipientPlayer']);

        // Send direct message
        $sendRes = $this->actingAs($senderUser)->postJson(route('chat.direct.send', ['partnerId' => $receiverChar->id]), [
            'message' => 'Hé kom je zo naar de werkplaats?',
        ]);
        $sendRes->assertOk();

        // Recipient polls incoming notifications
        $pollRes = $this->actingAs($receiverUser)->getJson(route('chat.poll-incoming'));
        $pollRes->assertOk();
        $pollRes->assertJson([
            'success' => true,
            'unread_dms' => 1,
            'new_dm' => [
                'sender_id' => $senderChar->id,
                'sender_name' => 'SecretSender',
                'message' => 'Hé kom je zo naar de werkplaats?',
            ],
        ]);
    }

    public function test_chat_blocks_external_links_except_discord_invites(): void
    {
        [$user, $character] = $this->createInmate();

        // 1. External link like https://google.com should be rejected
        $res1 = $this->actingAs($user)->postJson(route('chat.public.send'), [
            'channel' => 'global',
            'message' => 'Check deze site https://google.com eens!',
        ]);
        $res1->assertStatus(422);
        $this->assertStringContainsString('niet toegestaan', $res1->json('message'));

        Cache::flush();

        // 2. Discord invite (discord.gg/...) should be permitted
        $res2 = $this->actingAs($user)->postJson(route('chat.public.send'), [
            'channel' => 'global',
            'message' => 'Join onze discord op discord.gg/tbskliniekgame',
        ]);
        $res2->assertOk();
        $res2->assertJson(['success' => true]);
    }

    public function test_chat_reply_stores_quote_information(): void
    {
        [$user1, $char1] = $this->createInmate(['name' => 'OriginalPoster']);
        [$user2, $char2] = $this->createInmate(['name' => 'Replier']);

        // User 1 sends message
        $orig = $this->actingAs($user1)->postJson(route('chat.public.send'), [
            'channel' => 'global',
            'message' => 'Wie gaat er mee trainen in de gym?',
        ]);
        $origId = $orig->json('message.id');

        Cache::flush();

        // User 2 replies to User 1
        $reply = $this->actingAs($user2)->postJson(route('chat.public.send'), [
            'channel' => 'global',
            'message' => 'Ik ga mee!',
            'reply_to_id' => $origId,
        ]);

        $reply->assertOk();
        $this->assertEquals($origId, $reply->json('message.reply_to_id'));
        $this->assertEquals('OriginalPoster', $reply->json('message.reply_to_name'));
        $this->assertEquals('Wie gaat er mee trainen in de gym?', $reply->json('message.reply_to_snippet'));
    }

    public function test_chat_toggle_purple_heart(): void
    {
        [$user1, $char1] = $this->createInmate();
        [$user2, $char2] = $this->createInmate();

        $msgRes = $this->actingAs($user1)->postJson(route('chat.public.send'), [
            'channel' => 'global',
            'message' => 'Lekker bezig!',
        ]);
        $msgId = $msgRes->json('message.id');

        // User 2 hearts message
        $heart1 = $this->actingAs($user2)->postJson(route('chat.public.heart', $msgId));
        $heart1->assertOk();
        $heart1->assertJson(['success' => true, 'hearts_count' => 1, 'has_hearted' => true]);

        // User 2 unhearts message
        $heart2 = $this->actingAs($user2)->postJson(route('chat.public.heart', $msgId));
        $heart2->assertOk();
        $heart2->assertJson(['success' => true, 'hearts_count' => 0, 'has_hearted' => false]);
    }

    public function test_moderator_can_delete_messages(): void
    {
        [$regularUser, $regChar] = $this->createInmate();
        [$modUser, $modChar] = $this->createInmate();
        $modUser->update(['role' => 'moderator']);

        $msgRes = $this->actingAs($regularUser)->postJson(route('chat.public.send'), [
            'channel' => 'global',
            'message' => 'Ongepast berichtje',
        ]);
        $msgId = $msgRes->json('message.id');

        [$otherUser, $otherChar] = $this->createInmate();

        // Other regular user cannot delete
        $delFail = $this->actingAs($otherUser)->deleteJson(route('chat.public.delete', $msgId));
        $delFail->assertStatus(403);

        // Moderator can delete
        $delOk = $this->actingAs($modUser)->deleteJson(route('chat.public.delete', $msgId));
        $delOk->assertOk();
        $this->assertDatabaseMissing('chat_messages', ['id' => $msgId]);
    }

    public function test_gang_chat_channel_permissions(): void
    {
        [$user, $character] = $this->createInmate();
        $gang = Gang::create([
            'name' => 'The Kings',
            'tag' => 'KNG',
            'specialty' => 'Vleugel A',
            'leader_character_id' => $character->id,
            'has_gang_chat' => false,
        ]);
        $character->update(['gang_id' => $gang->id]);

        // Gang chat not yet unlocked
        $resLocked = $this->actingAs($user)->getJson(route('chat.public.messages', ['channel' => 'gang_'.$gang->id]));
        $resLocked->assertStatus(403);
        $resLocked->assertJson(['locked' => true]);

        // Unlock gang chat
        $gang->update(['has_gang_chat' => true]);

        // Now accessible
        $resUnlocked = $this->actingAs($user)->getJson(route('chat.public.messages', ['channel' => 'gang_'.$gang->id]));
        $resUnlocked->assertOk();
    }

    public function test_direct_messages_read_receipts(): void
    {
        [$user1, $char1] = $this->createInmate();
        [$user2, $char2] = $this->createInmate();

        $sendRes = $this->actingAs($user1)->postJson(route('chat.direct.send', $char2->id), [
            'message' => 'Heb je de buit?',
        ]);
        $sendRes->assertOk();
        $dmId = $sendRes->json('message.id');

        $this->assertDatabaseHas('direct_messages', [
            'id' => $dmId,
            'is_read' => false,
            'read_at' => null,
        ]);

        // User 2 opens conversation
        $this->actingAs($user2)->getJson(route('chat.direct.messages', $char1->id))->assertOk();

        $this->assertDatabaseHas('direct_messages', [
            'id' => $dmId,
            'is_read' => true,
        ]);
        $this->assertNotNull(DirectMessage::find($dmId)->read_at);
    }

    public function test_conversations_are_sorted_by_latest_message_descending_with_pinned_bot(): void
    {
        [$user, $char] = $this->createInmate();
        [$userOld, $charOld] = $this->createInmate(['name' => 'OldPartner']);
        [$userNew, $charNew] = $this->createInmate(['name' => 'NewPartner']);

        // Older message with charOld
        DirectMessage::create([
            'sender_id' => $charOld->id,
            'recipient_id' => $char->id,
            'type' => 'chat',
            'message' => 'Oud bericht van gisteren',
            'created_at' => now()->subDay(),
        ]);

        // Newer message with charNew
        DirectMessage::create([
            'sender_id' => $charNew->id,
            'recipient_id' => $char->id,
            'type' => 'chat',
            'message' => 'Vers bericht zojuist',
            'created_at' => now(),
        ]);

        $res = $this->actingAs($user)->getJson(route('chat.conversations'));
        $res->assertOk();

        $convs = $res->json('conversations');
        $this->assertCount(3, $convs);

        // Pinned Bot is first
        $this->assertEquals(0, $convs[0]['partner_id']);
        $this->assertTrue($convs[0]['is_bot']);

        // Newest partner is second
        $this->assertEquals($charNew->id, $convs[1]['partner_id']);

        // Older partner is third
        $this->assertEquals($charOld->id, $convs[2]['partner_id']);
    }

    public function test_theft_and_game_notifications_automatically_deliver_direct_messages_to_victim(): void
    {
        [$userVictim, $charVictim] = $this->createInmate(['name' => 'Victim']);
        [$userThief, $charThief] = $this->createInmate(['name' => 'Thief']);

        // Create a GameNotification for theft
        GameNotification::create([
            'character_id' => $charVictim->id,
            'type' => 'theft',
            'icon' => 'fa-sack-dollar',
            'title' => 'Bestolen op de Luchtplaats!',
            'message' => "Patiënt {$charThief->name} heeft €500 gerold uit je zakken!",
        ]);

        // Verify DirectMessage was automatically generated for victim
        $this->assertDatabaseHas('direct_messages', [
            'sender_id' => null,
            'recipient_id' => $charVictim->id,
            'type' => 'theft',
            'is_read' => false,
        ]);

        $dm = DirectMessage::where('recipient_id', $charVictim->id)->whereNull('sender_id')->latest('id')->first();
        $this->assertStringContainsString('Bestolen op de Luchtplaats!', $dm->message);
        $this->assertStringContainsString('€500', $dm->message);
    }

    public function test_player_can_search_players_for_mention_autocomplete(): void
    {
        [$user, $character] = $this->createInmate(['name' => 'MePlayer']);
        [$user2, $char2] = $this->createInmate(['name' => 'ScarfaceDan', 'last_active_at' => now()]);
        [$user3, $char3] = $this->createInmate(['name' => 'DonVito', 'last_active_at' => now()->subMinutes(2)]);

        // 1. Search with query 'scar'
        $response = $this->actingAs($user)->getJson(route('chat.search-players', ['q' => 'scar']));
        $response->assertOk();
        $response->assertJson(['success' => true]);
        $players = $response->json('players');
        $this->assertCount(1, $players);
        $this->assertEquals('ScarfaceDan', $players[0]['name']);

        // 2. Empty query returns active players
        $emptyResponse = $this->actingAs($user)->getJson(route('chat.search-players', ['q' => '']));
        $emptyResponse->assertOk();
        $emptyResponse->assertJson(['success' => true]);
        $emptyPlayers = $emptyResponse->json('players');
        $this->assertNotEmpty($emptyPlayers);
        $playerNames = collect($emptyPlayers)->pluck('name')->toArray();
        $this->assertContains('ScarfaceDan', $playerNames);
        $this->assertContains('DonVito', $playerNames);
        // Should not include self
        $this->assertNotContains('MePlayer', $playerNames);
    }

    public function test_player_can_poll_direct_messages_with_after_id(): void
    {
        [$user1, $char1] = $this->createInmate(['name' => 'PlayerOne']);
        [$user2, $char2] = $this->createInmate(['name' => 'PlayerTwo']);

        $dm1 = DirectMessage::create([
            'sender_id' => $char2->id,
            'recipient_id' => $char1->id,
            'message' => 'Eerste bericht',
            'is_read' => false,
        ]);

        $dm2 = DirectMessage::create([
            'sender_id' => $char2->id,
            'recipient_id' => $char1->id,
            'message' => 'Tweede bericht',
            'is_read' => false,
        ]);

        // Poll without after_id returns both
        $responseAll = $this->actingAs($user1)->getJson("/chat/prive/{$char2->id}");
        $responseAll->assertOk();
        $this->assertCount(2, $responseAll->json('messages'));

        // Poll with after_id = dm1->id returns only dm2
        $responseDiff = $this->actingAs($user1)->getJson("/chat/prive/{$char2->id}?after_id={$dm1->id}");
        $responseDiff->assertOk();
        $this->assertCount(1, $responseDiff->json('messages'));
        $this->assertEquals($dm2->id, $responseDiff->json('messages.0.id'));
    }

    public function test_search_players_with_gang_only_filter(): void
    {
        [$user1, $char1] = $this->createInmate(['name' => 'AlfaLid1']);

        $gang1 = Gang::create([
            'name' => 'Bende Alfa',
            'tag' => 'ALFA',
            'specialty' => 'Vleugel A',
            'leader_character_id' => $char1->id,
            'gang_stars' => 100,
            'has_gang_chat' => true,
        ]);
        $char1->update(['gang_id' => $gang1->id]);

        [$user2, $char2] = $this->createInmate(['name' => 'AlfaLid2', 'gang_id' => $gang1->id]);

        [$user3, $char3] = $this->createInmate(['name' => 'BetaLid1']);

        $gang2 = Gang::create([
            'name' => 'Bende Beta',
            'tag' => 'BETA',
            'specialty' => 'Vleugel B',
            'leader_character_id' => $char3->id,
            'gang_stars' => 50,
            'has_gang_chat' => true,
        ]);
        $char3->update(['gang_id' => $gang2->id]);

        // 1. Unfiltered search: returns both gang1 and gang2 members
        $responseAll = $this->actingAs($user1)->getJson('/chat/zoek-spelers?q=Lid');
        $responseAll->assertOk();
        $playerNames = collect($responseAll->json('players'))->pluck('name')->all();
        $this->assertContains('AlfaLid2', $playerNames);
        $this->assertContains('BetaLid1', $playerNames);

        // 2. Gang-only search: returns only members of gang1
        $responseGangOnly = $this->actingAs($user1)->getJson('/chat/zoek-spelers?q=Lid&gang_only=1');
        $responseGangOnly->assertOk();
        $gangPlayerNames = collect($responseGangOnly->json('players'))->pluck('name')->all();
        $this->assertContains('AlfaLid2', $gangPlayerNames);
        $this->assertNotContains('BetaLid1', $gangPlayerNames);
    }
}
