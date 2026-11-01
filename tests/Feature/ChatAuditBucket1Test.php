<?php

namespace Tests\Feature;

use App\Http\Middleware\EnsureUserIsOnboarded;
use App\Models\Character;
use App\Models\ChatMessage;
use App\Models\DirectMessage;
use App\Models\Gang;
use App\Models\User;
use App\Services\ChatBotService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class ChatAuditBucket1Test extends TestCase
{
    use RefreshDatabase;

    private function createInmate(array $attributes = [], array $userAttributes = []): array
    {
        $user = User::factory()->create($userAttributes);
        $character = Character::create(array_merge([
            'user_id' => $user->id,
            'name' => 'Inmate_'.rand(10000, 99999),
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

    /**
     * Bug 1: Early return in publicMessages when after_id >= cachedLastId must include deleted_ids.
     */
    public function test_public_messages_early_return_includes_deleted_ids(): void
    {
        Cache::flush();
        [$user, $character] = $this->createInmate();

        $msg1 = ChatMessage::create([
            'character_id' => $character->id,
            'channel' => 'global',
            'message' => 'Eerste openbare bericht',
            'hearts_count' => 0,
            'hearts_users' => [],
        ]);

        // First fetch to warm up cached chat_last_id_global
        $response1 = $this->actingAs($user)->getJson(route('chat.public.messages', ['channel' => 'global']));
        $response1->assertOk();
        $this->assertEquals((int) $msg1->id, (int) Cache::get('chat_last_id_global'));

        // Delete the message
        $deleteResp = $this->actingAs($user)->deleteJson(route('chat.public.delete', ['id' => $msg1->id]));
        $deleteResp->assertOk();
        $this->assertDatabaseMissing('chat_messages', ['id' => $msg1->id]);

        $cachedDeleted = Cache::get('chat_deleted_ids_global', []);
        $this->assertContains((int) $msg1->id, $cachedDeleted);

        // Client polls with after_id equal to last message id
        $responseEarly = $this->actingAs($user)->getJson(route('chat.public.messages', [
            'channel' => 'global',
            'after_id' => $msg1->id,
        ]));

        $responseEarly->assertOk();
        $responseEarly->assertJson([
            'success' => true,
            'messages' => [],
            'last_id' => (int) $msg1->id,
        ]);

        $deletedIds = $responseEarly->json('deleted_ids');
        $this->assertIsArray($deletedIds);
        $this->assertContains((int) $msg1->id, $deletedIds);
    }

    /**
     * Bug 2: SQL precedence in directMessages when after_id > 0 must not return all historical messages from sender.
     */
    public function test_direct_messages_after_id_scoping_does_not_leak_older_sent_messages(): void
    {
        Cache::flush();
        [$userA, $charA] = $this->createInmate(['name' => 'Alice']);
        [$userB, $charB] = $this->createInmate(['name' => 'Bob']);

        // Message 1: Alice -> Bob (old)
        $msg1 = DirectMessage::create([
            'sender_id' => $charA->id,
            'recipient_id' => $charB->id,
            'message' => 'Alice bericht 1 (oud)',
            'type' => 'chat',
            'is_read' => true,
        ]);

        // Message 2: Bob -> Alice (old)
        $msg2 = DirectMessage::create([
            'sender_id' => $charB->id,
            'recipient_id' => $charA->id,
            'message' => 'Bob bericht 2 (oud)',
            'type' => 'chat',
            'is_read' => true,
        ]);

        // Message 3: Bob -> Alice (new)
        $msg3 = DirectMessage::create([
            'sender_id' => $charB->id,
            'recipient_id' => $charA->id,
            'message' => 'Bob bericht 3 (nieuw)',
            'type' => 'chat',
            'is_read' => false,
        ]);

        // Alice polls with after_id = msg2->id
        // With previous bug (A or B and id > X), msg1 was returned because it matched branch A regardless of id > X!
        $response = $this->actingAs($userA)->getJson(route('chat.direct.messages', [
            'partnerId' => $charB->id,
            'after_id' => $msg2->id,
        ]));

        $response->assertOk();
        $messages = $response->json('messages');

        $this->assertCount(1, $messages);
        $this->assertEquals((int) $msg3->id, (int) $messages[0]['id']);
        $this->assertEquals('Bob bericht 3 (nieuw)', $messages[0]['message']);
    }

    /**
     * Bug 3: Cross-Channel Quote IDOR is blocked (cannot quote gang message in global chat).
     */
    public function test_cross_channel_quote_idor_is_blocked(): void
    {
        Cache::flush();
        [$gangUser, $gangChar] = $this->createInmate(['name' => 'GangLeader']);
        $gang = Gang::create([
            'name' => 'Secret Syndicate',
            'tag' => 'SS',
            'specialty' => 'Vleugel B',
            'leader_character_id' => $gangChar->id,
            'gang_stars' => 500,
            'has_gang_chat' => true,
        ]);
        $gangChar->update(['gang_id' => $gang->id]);

        $gangChannel = "gang_{$gang->id}";

        // Confidential message posted in gang chat
        $secretGangMsg = ChatMessage::create([
            'character_id' => $gangChar->id,
            'channel' => $gangChannel,
            'message' => 'Top secret bendecode: ALPHA-99',
            'hearts_count' => 0,
            'hearts_users' => [],
        ]);

        // Outsider attempts to quote the secret gang message in global chat
        [$outsiderUser, $outsiderChar] = $this->createInmate(['name' => 'CuriousOutsider']);

        $sendResp = $this->actingAs($outsiderUser)->postJson(route('chat.public.send'), [
            'channel' => 'global',
            'message' => 'Kijk wat ik heb ontdekt!',
            'reply_to_id' => $secretGangMsg->id,
        ]);

        $sendResp->assertOk();
        $msgData = $sendResp->json('message');

        // Quote IDOR must be prevented: reply_to fields must be null
        $this->assertNull($msgData['reply_to_id']);
        $this->assertNull($msgData['reply_to_name']);
        $this->assertNull($msgData['reply_to_snippet']);

        $created = ChatMessage::find($msgData['id']);
        $this->assertNull($created->reply_to_id);
        $this->assertNull($created->reply_to_name);
        $this->assertNull($created->reply_to_snippet);
    }

    /**
     * Valid quotes within the same channel work normally.
     */
    public function test_same_channel_quote_works(): void
    {
        Cache::flush();
        [$userA, $charA] = $this->createInmate(['name' => 'InmateAlpha']);
        [$userB, $charB] = $this->createInmate(['name' => 'InmateBeta']);

        $originalMsg = ChatMessage::create([
            'character_id' => $charA->id,
            'channel' => 'global',
            'message' => 'Wie wil er meedoen met een overval?',
            'hearts_count' => 0,
            'hearts_users' => [],
        ]);

        $sendResp = $this->actingAs($userB)->postJson(route('chat.public.send'), [
            'channel' => 'global',
            'message' => 'Ik doe mee!',
            'reply_to_id' => $originalMsg->id,
        ]);

        $sendResp->assertOk();
        $msgData = $sendResp->json('message');

        $this->assertEquals((int) $originalMsg->id, (int) $msgData['reply_to_id']);
        $this->assertEquals($charA->name, $msgData['reply_to_name']);
        $this->assertStringContainsString('Wie wil er meedoen', $msgData['reply_to_snippet']);
    }

    /**
     * Bug 4: Private DM Quote IDOR is blocked (cannot quote an unrelated pair's DM).
     */
    public function test_private_dm_quote_idor_is_blocked(): void
    {
        Cache::flush();
        [$userA, $charA] = $this->createInmate(['name' => 'AliceSecret']);
        [$userB, $charB] = $this->createInmate(['name' => 'BobSecret']);

        // Confidential DM between Alice and Bob
        $secretDm = DirectMessage::create([
            'sender_id' => $charA->id,
            'recipient_id' => $charB->id,
            'message' => 'Privé kluiscode: 987654',
            'type' => 'chat',
            'is_read' => true,
        ]);

        // Unrelated inmates Charlie and Dave
        [$userC, $charC] = $this->createInmate(['name' => 'CharlieHacker']);
        [$userD, $charD] = $this->createInmate(['name' => 'DaveVictim']);

        // Charlie tries to quote Alice & Bob's secret DM in a DM to Dave
        $sendResp = $this->actingAs($userC)->postJson(route('chat.direct.send', ['partnerId' => $charD->id]), [
            'message' => 'Heb jij dit al gezien?',
            'reply_to_id' => $secretDm->id,
        ]);

        $sendResp->assertOk();
        $dmData = $sendResp->json('message');

        // Quote IDOR must be prevented: reply_to fields must be null
        $this->assertNull($dmData['reply_to_id']);
        $this->assertNull($dmData['reply_to_name']);
        $this->assertNull($dmData['reply_to_snippet']);

        $createdDm = DirectMessage::find($dmData['id']);
        $this->assertNull($createdDm->reply_to_id);
        $this->assertNull($createdDm->reply_to_name);
        $this->assertNull($createdDm->reply_to_snippet);
    }

    /**
     * Valid DM quote within the same conversation works normally.
     */
    public function test_same_conversation_dm_quote_works(): void
    {
        Cache::flush();
        [$userA, $charA] = $this->createInmate(['name' => 'AlicePartner']);
        [$userB, $charB] = $this->createInmate(['name' => 'BobPartner']);

        $originalDm = DirectMessage::create([
            'sender_id' => $charA->id,
            'recipient_id' => $charB->id,
            'message' => 'Zullen we straks handelen?',
            'type' => 'chat',
            'is_read' => true,
        ]);

        $replyResp = $this->actingAs($userB)->postJson(route('chat.direct.send', ['partnerId' => $charA->id]), [
            'message' => 'Ja, ontmoet me op de binnenplaats.',
            'reply_to_id' => $originalDm->id,
        ]);

        $replyResp->assertOk();
        $dmData = $replyResp->json('message');

        $this->assertEquals((int) $originalDm->id, (int) $dmData['reply_to_id']);
        $this->assertEquals($charA->name, $dmData['reply_to_name']);
        $this->assertStringContainsString('Zullen we straks handelen', $dmData['reply_to_snippet']);
    }

    /**
     * Bug 5: Gang Chat Heart IDOR is blocked for non-gang members and locked gangs.
     */
    public function test_gang_chat_heart_idor_is_blocked_for_outsiders(): void
    {
        Cache::flush();
        [$leaderUser, $leaderChar] = $this->createInmate(['name' => 'GangLeaderHeart']);
        $gang = Gang::create([
            'name' => 'Heart Gang',
            'tag' => 'HG',
            'specialty' => 'Vleugel C',
            'leader_character_id' => $leaderChar->id,
            'gang_stars' => 500,
            'has_gang_chat' => true,
        ]);
        $leaderChar->update(['gang_id' => $gang->id]);

        $gangMsg = ChatMessage::create([
            'character_id' => $leaderChar->id,
            'channel' => "gang_{$gang->id}",
            'message' => 'Exclusief bendebericht!',
            'hearts_count' => 0,
            'hearts_users' => [],
        ]);

        // Outsider tries to toggle heart on private gang chat
        [$outsiderUser, $outsiderChar] = $this->createInmate(['name' => 'HeartOutsider']);

        $toggleResp = $this->actingAs($outsiderUser)->postJson(route('chat.public.heart', ['id' => $gangMsg->id]));
        $toggleResp->assertStatus(403);
        $toggleResp->assertJson(['success' => false, 'message' => 'Je hebt geen toegang tot deze bende chat.']);

        // Gang member can toggle heart
        $memberResp = $this->actingAs($leaderUser)->postJson(route('chat.public.heart', ['id' => $gangMsg->id]));
        $memberResp->assertOk();
        $memberResp->assertJson(['success' => true, 'hearts_count' => 1, 'has_hearted' => true]);

        // Gang member untoggles heart
        $memberResp2 = $this->actingAs($leaderUser)->postJson(route('chat.public.heart', ['id' => $gangMsg->id]));
        $memberResp2->assertOk();
        $memberResp2->assertJson(['success' => true, 'hearts_count' => 0, 'has_hearted' => false]);
    }

    /**
     * Bug 5: Gang Chat Heart is blocked when gang chat has not been unlocked.
     */
    public function test_gang_chat_heart_is_blocked_when_gang_chat_locked(): void
    {
        Cache::flush();
        [$user, $character] = $this->createInmate(['name' => 'LockedGangMember']);
        $gang = Gang::create([
            'name' => 'Poor Gang',
            'tag' => 'PG',
            'specialty' => 'Vleugel D',
            'leader_character_id' => $character->id,
            'gang_stars' => 10,
            'has_gang_chat' => false, // Locked
        ]);
        $character->update(['gang_id' => $gang->id]);

        $gangMsg = ChatMessage::create([
            'character_id' => $character->id,
            'channel' => "gang_{$gang->id}",
            'message' => 'Bendebericht in gesloten chat',
            'hearts_count' => 0,
            'hearts_users' => [],
        ]);

        $toggleResp = $this->actingAs($user)->postJson(route('chat.public.heart', ['id' => $gangMsg->id]));
        $toggleResp->assertStatus(403);
        $toggleResp->assertJson(['success' => false, 'message' => 'Deze bende heeft de gang chat nog niet ontgrendeld.']);
    }

    /**
     * Bug 6: Deleting public messages permissions (author, staff/moderator, and non-author rejection).
     */
    public function test_delete_public_message_permissions(): void
    {
        Cache::flush();
        [$authorUser, $authorChar] = $this->createInmate(['name' => 'MsgAuthor']);
        [$strangerUser, $strangerChar] = $this->createInmate(['name' => 'Stranger']);
        [$modUser, $modChar] = $this->createInmate(['name' => 'ModeratorUser'], ['role' => 'moderator']);

        $msg = ChatMessage::create([
            'character_id' => $authorChar->id,
            'channel' => 'global',
            'message' => 'Te verwijderen bericht',
            'hearts_count' => 0,
            'hearts_users' => [],
        ]);

        // Stranger cannot delete author's message
        $strangerResp = $this->actingAs($strangerUser)->deleteJson(route('chat.public.delete', ['id' => $msg->id]));
        $strangerResp->assertStatus(403);
        $this->assertDatabaseHas('chat_messages', ['id' => $msg->id]);

        // Author can delete own message
        $authorResp = $this->actingAs($authorUser)->deleteJson(route('chat.public.delete', ['id' => $msg->id]));
        $authorResp->assertOk();
        $this->assertDatabaseMissing('chat_messages', ['id' => $msg->id]);

        // Moderator can delete another inmate's message
        $msg2 = ChatMessage::create([
            'character_id' => $authorChar->id,
            'channel' => 'global',
            'message' => 'Spam bericht van author',
            'hearts_count' => 0,
            'hearts_users' => [],
        ]);

        $modResp = $this->actingAs($modUser)->deleteJson(route('chat.public.delete', ['id' => $msg2->id]));
        $modResp->assertOk();
        $this->assertDatabaseMissing('chat_messages', ['id' => $msg2->id]);
    }

    /**
     * Bug 6: Deleting direct message permissions (sender, recipient, moderator, stranger block).
     */
    public function test_delete_direct_message_permissions(): void
    {
        Cache::flush();
        [$senderUser, $senderChar] = $this->createInmate(['name' => 'DmSender']);
        [$recipientUser, $recipientChar] = $this->createInmate(['name' => 'DmRecipient']);
        [$strangerUser, $strangerChar] = $this->createInmate(['name' => 'DmStranger']);
        [$modUser, $modChar] = $this->createInmate(['name' => 'DmMod'], ['role' => 'moderator']);

        $dm = DirectMessage::create([
            'sender_id' => $senderChar->id,
            'recipient_id' => $recipientChar->id,
            'message' => 'Test DM bericht',
            'type' => 'chat',
            'is_read' => false,
        ]);

        // Stranger cannot delete
        $strangerResp = $this->actingAs($strangerUser)->deleteJson(route('chat.direct.delete', ['id' => $dm->id]));
        $strangerResp->assertStatus(403);
        $this->assertDatabaseHas('direct_messages', ['id' => $dm->id]);

        // Recipient can delete
        $recipientResp = $this->actingAs($recipientUser)->deleteJson(route('chat.direct.delete', ['id' => $dm->id]));
        $recipientResp->assertOk();
        $this->assertDatabaseMissing('direct_messages', ['id' => $dm->id]);

        // New DM where sender deletes
        $dm2 = DirectMessage::create([
            'sender_id' => $senderChar->id,
            'recipient_id' => $recipientChar->id,
            'message' => 'Verkeerd gestuurd!',
            'type' => 'chat',
            'is_read' => false,
        ]);

        $senderResp = $this->actingAs($senderUser)->deleteJson(route('chat.direct.delete', ['id' => $dm2->id]));
        $senderResp->assertOk();
        $this->assertDatabaseMissing('direct_messages', ['id' => $dm2->id]);

        // New DM where moderator deletes
        $dm3 = DirectMessage::create([
            'sender_id' => $senderChar->id,
            'recipient_id' => $recipientChar->id,
            'message' => 'Overtreding in DM',
            'type' => 'chat',
            'is_read' => false,
        ]);

        $modResp = $this->actingAs($modUser)->deleteJson(route('chat.direct.delete', ['id' => $dm3->id]));
        $modResp->assertOk();
        $this->assertDatabaseMissing('direct_messages', ['id' => $dm3->id]);
    }

    /**
     * Null character guards check that requests return 403 when user has no character.
     */
    public function test_null_character_guards(): void
    {
        $userWithoutChar = User::factory()->create();

        $this->withoutMiddleware(EnsureUserIsOnboarded::class)
            ->actingAs($userWithoutChar)
            ->getJson(route('chat.conversations'))
            ->assertStatus(403);

        $this->withoutMiddleware(EnsureUserIsOnboarded::class)
            ->actingAs($userWithoutChar)
            ->getJson(route('chat.direct.messages', ['partnerId' => 1]))
            ->assertStatus(403);

        $this->withoutMiddleware(EnsureUserIsOnboarded::class)
            ->actingAs($userWithoutChar)
            ->postJson(route('chat.public.send'), ['message' => 'Test'])
            ->assertStatus(403);

        $this->withoutMiddleware(EnsureUserIsOnboarded::class)
            ->actingAs($userWithoutChar)
            ->postJson(route('chat.direct.send', ['partnerId' => 1]), ['message' => 'Test'])
            ->assertStatus(403);

        $this->withoutMiddleware(EnsureUserIsOnboarded::class)
            ->actingAs($userWithoutChar)
            ->postJson(route('chat.public.heart', ['id' => 1]))
            ->assertStatus(403);

        $this->withoutMiddleware(EnsureUserIsOnboarded::class)
            ->actingAs($userWithoutChar)
            ->postJson(route('chat.direct.heart', ['id' => 1]))
            ->assertStatus(403);

        $this->withoutMiddleware(EnsureUserIsOnboarded::class)
            ->actingAs($userWithoutChar)
            ->deleteJson(route('chat.public.delete', ['id' => 1]))
            ->assertStatus(403);

        $this->withoutMiddleware(EnsureUserIsOnboarded::class)
            ->actingAs($userWithoutChar)
            ->deleteJson(route('chat.direct.delete', ['id' => 1]))
            ->assertStatus(403);
    }

    /**
     * can_delete attribute is included and correctly computed in message responses.
     */
    public function test_can_delete_attribute_in_message_responses(): void
    {
        Cache::flush();
        [$authorUser, $authorChar] = $this->createInmate(['name' => 'AuthorInmate']);
        [$viewerUser, $viewerChar] = $this->createInmate(['name' => 'ViewerInmate']);
        [$modUser, $modChar] = $this->createInmate(['name' => 'ModInmate'], ['role' => 'moderator']);

        $msg = ChatMessage::create([
            'character_id' => $authorChar->id,
            'channel' => 'global',
            'message' => 'Hallo allemaal',
            'hearts_count' => 0,
            'hearts_users' => [],
        ]);

        // Author views: can_delete should be true
        $authorFetch = $this->actingAs($authorUser)->getJson(route('chat.public.messages', ['channel' => 'global']));
        $authorFetch->assertOk();
        $this->assertTrue($authorFetch->json('messages.0.can_delete'));

        // Viewer views: can_delete should be false
        $viewerFetch = $this->actingAs($viewerUser)->getJson(route('chat.public.messages', ['channel' => 'global']));
        $viewerFetch->assertOk();
        $this->assertFalse($viewerFetch->json('messages.0.can_delete'));

        // Moderator views: can_delete should be true
        $modFetch = $this->actingAs($modUser)->getJson(route('chat.public.messages', ['channel' => 'global']));
        $modFetch->assertOk();
        $this->assertTrue($modFetch->json('messages.0.can_delete'));

        // DM responses always have can_delete = true for conversation participants
        $dm = DirectMessage::create([
            'sender_id' => $authorChar->id,
            'recipient_id' => $viewerChar->id,
            'message' => 'Privé hallo',
            'type' => 'chat',
            'is_read' => false,
        ]);

        $dmFetch = $this->actingAs($viewerUser)->getJson(route('chat.direct.messages', ['partnerId' => $authorChar->id]));
        $dmFetch->assertOk();
        $this->assertTrue($dmFetch->json('messages.0.can_delete'));
    }

    /**
     * Recipient can delete bot / clinic system direct messages.
     */
    public function test_recipient_can_delete_bot_system_dm(): void
    {
        Cache::flush();
        [$user, $character] = $this->createInmate(['name' => 'BotRecipient']);

        $botDm = DirectMessage::sendSystem($character, 'Belangrijke kluiscode ontvangen!', 'kluiscode');
        $this->assertNull($botDm->sender_id);
        $this->assertEquals((int) $character->id, (int) $botDm->recipient_id);

        $deleteResp = $this->actingAs($user)->deleteJson(route('chat.direct.delete', ['id' => $botDm->id]));
        $deleteResp->assertOk();
        $this->assertDatabaseMissing('direct_messages', ['id' => $botDm->id]);

        $convKey = '0_'.$character->id;
        $cachedDeleted = Cache::get("dm_deleted_ids_{$convKey}", []);
        $this->assertContains((int) $botDm->id, $cachedDeleted);
    }

    /**
     * Gang message deletion permissions (author vs outsider).
     */
    public function test_gang_message_deletion_permissions(): void
    {
        Cache::flush();
        [$leaderUser, $leaderChar] = $this->createInmate(['name' => 'GangDelLeader']);
        [$outsiderUser, $outsiderChar] = $this->createInmate(['name' => 'GangDelOutsider']);

        $gang = Gang::create([
            'name' => 'Delete Gang',
            'tag' => 'DG',
            'specialty' => 'Vleugel E',
            'leader_character_id' => $leaderChar->id,
            'gang_stars' => 500,
            'has_gang_chat' => true,
        ]);
        $leaderChar->update(['gang_id' => $gang->id]);

        $gangMsg = ChatMessage::create([
            'character_id' => $leaderChar->id,
            'channel' => "gang_{$gang->id}",
            'message' => 'Dit bendebericht gaat weg.',
            'hearts_count' => 0,
            'hearts_users' => [],
        ]);

        // Outsider cannot delete
        $outsiderResp = $this->actingAs($outsiderUser)->deleteJson(route('chat.public.delete', ['id' => $gangMsg->id]));
        $outsiderResp->assertStatus(403);
        $this->assertDatabaseHas('chat_messages', ['id' => $gangMsg->id]);

        // Author can delete
        $leaderResp = $this->actingAs($leaderUser)->deleteJson(route('chat.public.delete', ['id' => $gangMsg->id]));
        $leaderResp->assertOk();
        $this->assertDatabaseMissing('chat_messages', ['id' => $gangMsg->id]);

        $cachedDeleted = Cache::get("chat_deleted_ids_gang_{$gang->id}", []);
        $this->assertContains((int) $gangMsg->id, $cachedDeleted);
    }

    /**
     * ChatBotService correctly dispatches broadcasts and notifications.
     */
    public function test_chatbot_service_methods(): void
    {
        Cache::flush();
        [$adminUser, $adminChar] = $this->createInmate(['name' => 'ClinicDirector'], ['role' => 'admin']);
        [$playerUser, $playerChar] = $this->createInmate(['name' => 'PlayerInmate']);

        $service = app(ChatBotService::class);

        // 1. Broadcast
        $broadcastMsg = $service->broadcast('Mededeling van de directie: lunchpauze!', 'global');
        $this->assertNotNull($broadcastMsg);
        $this->assertEquals('Mededeling van de directie: lunchpauze!', $broadcastMsg->message);
        $this->assertEquals((int) $broadcastMsg->id, (int) Cache::get('chat_last_id_global'));

        // 2. Level Up
        $lvlMsg = $service->sendLevelUp($playerChar, 5, ['€ 10.000', '5 Sterren']);
        $this->assertDatabaseHas('direct_messages', [
            'id' => $lvlMsg->id,
            'recipient_id' => $playerChar->id,
            'type' => 'level_up',
        ]);

        // 3. Vault code
        $vaultMsg = $service->sendVaultCode($playerChar, 'VAULT-777', 'Missie voltooid');
        $this->assertDatabaseHas('direct_messages', [
            'id' => $vaultMsg->id,
            'recipient_id' => $playerChar->id,
            'type' => 'kluiscode',
        ]);

        // 4. System notice
        $noticeMsg = $service->sendSystemNotice($playerChar, 'Systeemwaarschuwing', 'Pas op voor bewakers');
        $this->assertDatabaseHas('direct_messages', [
            'id' => $noticeMsg->id,
            'recipient_id' => $playerChar->id,
            'type' => 'system',
        ]);

        // 5. Liquidation notice
        $liqMsg = $service->sendLiquidationNotice($playerChar, 'Incident op de gang');
        $this->assertDatabaseHas('direct_messages', [
            'id' => $liqMsg->id,
            'recipient_id' => $playerChar->id,
            'type' => 'liquidation',
        ]);
    }
}
