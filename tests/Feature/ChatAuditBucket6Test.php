<?php

namespace Tests\Feature;

use App\Http\Controllers\MessageController;
use App\Models\Character;
use App\Models\ChatMessage;
use App\Models\DirectMessage;
use App\Models\Friend;
use App\Models\GameMessage;
use App\Models\Gang;
use App\Models\Report;
use App\Models\SupportTicket;
use App\Models\SupportTicketMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class ChatAuditBucket6Test extends TestCase
{
    use RefreshDatabase;

    protected function createCharacter(User $user, array $attrs = []): Character
    {
        return Character::create(array_merge([
            'user_id' => $user->id,
            'name' => 'Speler_'.rand(1000, 9999),
            'level' => 1,
            'xp' => 0,
            'health' => 100,
            'max_health' => 100,
            'energy' => 100,
            'max_energy' => 100,
            'cash' => 2000,
            'bank' => 5000,
            'stars' => 10,
            'respect' => 50,
        ], $attrs));
    }

    /*
    |--------------------------------------------------------------------------
    | MessageController & GameMessage Tests
    |--------------------------------------------------------------------------
    */

    public function test_messages_index_redirects_to_direct_chat(): void
    {
        $user = User::factory()->create();
        $char = $this->createCharacter($user);

        $response = $this->actingAs($user)->get(route('messages.index'));
        $response->assertRedirect(route('chat.index', ['tab' => 'direct']));
    }

    public function test_messages_index_with_recipient_redirects_to_partner(): void
    {
        $userA = User::factory()->create();
        $charA = $this->createCharacter($userA, ['name' => 'Alice']);

        $userB = User::factory()->create();
        $charB = $this->createCharacter($userB, ['name' => 'Bob']);

        $response = $this->actingAs($userA)->get(route('messages.index', ['recipient' => 'Bob']));
        $response->assertRedirect(route('chat.index', ['tab' => 'direct', 'recipient' => $charB->id]));
    }

    public function test_send_message_delivers_successfully_to_friend_and_creates_dm_alert(): void
    {
        $userA = User::factory()->create();
        $charA = $this->createCharacter($userA, ['name' => 'Zender_1']);

        $userB = User::factory()->create();
        $charB = $this->createCharacter($userB, ['name' => 'Ontvanger_1']);

        Friend::create([
            'character_id' => $charA->id,
            'friend_id' => $charB->id,
            'status' => 'accepted',
        ]);

        $response = $this->actingAs($userA)->post(route('messages.send'), [
            'recipient_name' => $charB->name,
            'subject' => 'Geheim overleg',
            'content' => 'Ontmoet me om 22:00 in de werkplaats.',
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('game_messages', [
            'sender_id' => $charA->id,
            'recipient_id' => $charB->id,
            'subject' => 'Geheim overleg',
            'content' => 'Ontmoet me om 22:00 in de werkplaats.',
        ]);

        $this->assertDatabaseHas('direct_messages', [
            'sender_id' => $charA->id,
            'recipient_id' => $charB->id,
        ]);
    }

    public function test_send_message_blocked_when_sender_is_imprisoned(): void
    {
        $userA = User::factory()->create();
        $charA = $this->createCharacter($userA, [
            'name' => 'Gevangen_1',
            'prison_until' => now()->addMinutes(10),
        ]);

        $userB = User::factory()->create();
        $charB = $this->createCharacter($userB, ['name' => 'Vrij_1']);

        Friend::create([
            'character_id' => $charA->id,
            'friend_id' => $charB->id,
            'status' => 'accepted',
        ]);

        $response = $this->actingAs($userA)->post(route('messages.send'), [
            'recipient_name' => $charB->name,
            'subject' => 'Hulp',
            'content' => 'Help me ontsnappen!',
        ]);

        $response->assertSessionHas('error', 'Vanuit de isoleercel kun je geen brieven versturen!');
        $this->assertDatabaseMissing('game_messages', ['sender_id' => $charA->id]);
    }

    public function test_send_message_blocked_when_sender_is_comatose(): void
    {
        $userA = User::factory()->create();
        $charA = $this->createCharacter($userA, [
            'name' => 'Coma_1',
            'health' => 0,
            'coma_until' => now()->addMinutes(15),
        ]);

        $userB = User::factory()->create();
        $charB = $this->createCharacter($userB, ['name' => 'Levend_1']);

        Friend::create([
            'character_id' => $charA->id,
            'friend_id' => $charB->id,
            'status' => 'accepted',
        ]);

        $response = $this->actingAs($userA)->post(route('messages.send'), [
            'recipient_name' => $charB->name,
            'subject' => 'Reanimeer mij',
            'content' => 'Dien serum toe!',
        ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('game_messages', ['sender_id' => $charA->id]);
    }

    public function test_send_message_blocked_when_sending_to_oneself(): void
    {
        $user = User::factory()->create();
        $char = $this->createCharacter($user, ['name' => 'Zelf_1']);

        $response = $this->actingAs($user)->post(route('messages.send'), [
            'recipient_name' => $char->name,
            'subject' => 'Aan mijzelf',
            'content' => 'Hallo ik',
        ]);

        $response->assertSessionHas('error', 'Je kunt geen brief naar jezelf sturen!');
    }

    public function test_send_message_blocked_when_recipient_is_not_friend(): void
    {
        $userA = User::factory()->create();
        $charA = $this->createCharacter($userA, ['name' => 'GeenVriendA']);

        $userB = User::factory()->create();
        $charB = $this->createCharacter($userB, ['name' => 'GeenVriendB']);

        $response = $this->actingAs($userA)->post(route('messages.send'), [
            'recipient_name' => $charB->name,
            'subject' => 'Hoi onbekende',
            'content' => 'Ken je mij?',
        ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('game_messages', ['sender_id' => $charA->id]);
    }

    public function test_send_message_sanitizes_xss_and_disarms_dangerous_schemes(): void
    {
        $userA = User::factory()->create();
        $charA = $this->createCharacter($userA, ['name' => 'Hacker_1']);

        $userB = User::factory()->create();
        $charB = $this->createCharacter($userB, ['name' => 'Slachtoffer_1']);

        Friend::create([
            'character_id' => $charA->id,
            'friend_id' => $charB->id,
            'status' => 'accepted',
        ]);

        $response = $this->actingAs($userA)->post(route('messages.send'), [
            'recipient_name' => $charB->name,
            'subject' => '<script>alert("xss")</script>Veilige Titel',
            'content' => '<b onclick="evil()">Klik hier</b>: javascript:alert(1) en data:text/html',
        ]);

        $response->assertSessionHas('success');

        $msg = GameMessage::where('sender_id', $charA->id)->first();
        $this->assertNotNull($msg);
        $this->assertStringNotContainsString('<script>', $msg->subject);
        $this->assertEquals('Veilige Titel', $msg->subject);
        $this->assertStringNotContainsString('onclick', $msg->content);
        $this->assertStringNotContainsString('javascript:', $msg->content);
        $this->assertStringContainsString('javascript&#58;', $msg->content);
    }

    public function test_store_alias_method_works_identically_to_send(): void
    {
        $userA = User::factory()->create();
        $charA = $this->createCharacter($userA, ['name' => 'AliasSender']);

        $userB = User::factory()->create();
        $charB = $this->createCharacter($userB, ['name' => 'AliasReceiver']);

        Friend::create([
            'character_id' => $charA->id,
            'friend_id' => $charB->id,
            'status' => 'accepted',
        ]);

        $this->actingAs($userA);

        $controller = app(MessageController::class);
        $request = Request::create('/berichten', 'POST', [
            'recipient_name' => $charB->name,
            'subject' => 'Store Test',
            'content' => 'Inhoud via store()',
        ]);
        $request->setUserResolver(fn () => $userA);

        $response = $controller->store($request);
        $this->assertTrue($response->isRedirection());

        $this->assertDatabaseHas('game_messages', [
            'sender_id' => $charA->id,
            'recipient_id' => $charB->id,
            'subject' => 'Store Test',
        ]);
    }

    public function test_mark_read_and_idor_protection(): void
    {
        $userA = User::factory()->create();
        $charA = $this->createCharacter($userA);

        $userB = User::factory()->create();
        $charB = $this->createCharacter($userB);

        $msg = GameMessage::create([
            'recipient_id' => $charA->id,
            'sender_id' => null,
            'sender_name' => 'Directie',
            'subject' => 'Privé brief',
            'content' => 'Strikt vertrouwelijk',
            'type' => 'system',
            'is_read' => false,
        ]);

        // User B tries to mark User A's message -> 403
        $forbiddenResponse = $this->actingAs($userB)->post(route('messages.mark-read', $msg));
        $forbiddenResponse->assertStatus(403);
        $this->assertFalse($msg->fresh()->is_read);

        // User A marks their own message -> 200/Redirect
        $successResponse = $this->actingAs($userA)->post(route('messages.mark-read', $msg));
        $successResponse->assertSessionHas('success');
        $this->assertTrue($msg->fresh()->is_read);
    }

    public function test_destroy_deletes_single_message_and_cleans_synced_dm(): void
    {
        $user = User::factory()->create();
        $char = $this->createCharacter($user);

        $msg = GameMessage::create([
            'recipient_id' => $char->id,
            'sender_id' => null,
            'sender_name' => 'Systeem',
            'subject' => 'Te Verwijderen',
            'content' => 'Weg ermee',
            'type' => 'system',
            'is_read' => true,
        ]);

        $syncedDm = DirectMessage::where('metadata->game_message_id', $msg->id)->first();
        $this->assertNotNull($syncedDm);

        $response = $this->actingAs($user)->delete(route('messages.destroy', $msg));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('game_messages', ['id' => $msg->id]);
        $this->assertDatabaseMissing('direct_messages', ['id' => $syncedDm->id]);
    }

    public function test_destroy_forbidden_for_other_player_idor(): void
    {
        $userA = User::factory()->create();
        $charA = $this->createCharacter($userA);

        $userB = User::factory()->create();
        $charB = $this->createCharacter($userB);

        $msg = GameMessage::create([
            'recipient_id' => $charA->id,
            'sender_name' => 'Directie',
            'subject' => 'Aan A',
            'content' => 'Inhoud',
            'type' => 'system',
            'is_read' => false,
        ]);

        $response = $this->actingAs($userB)->delete(route('messages.destroy', $msg));
        $response->assertStatus(403);
        $this->assertDatabaseHas('game_messages', ['id' => $msg->id]);
    }

    public function test_destroy_all_with_filter_and_scope_unread_and_everything(): void
    {
        $user = User::factory()->create();
        $char = $this->createCharacter($user);

        GameMessage::create([
            'recipient_id' => $char->id,
            'sender_name' => 'Boef',
            'subject' => 'Diefstal melding',
            'content' => 'Bestolen',
            'type' => 'theft',
            'is_read' => false,
        ]);

        GameMessage::create([
            'recipient_id' => $char->id,
            'sender_name' => 'Directie',
            'subject' => 'Systeem melding',
            'content' => 'Arrestatie',
            'type' => 'system',
            'is_read' => false,
        ]);

        // Destroy only theft messages
        $res = $this->actingAs($user)->delete(route('messages.destroy-all'), [
            'scope' => 'filter',
            'filter' => 'theft',
        ]);
        $res->assertSessionHas('success');
        $this->assertEquals(1, GameMessage::where('recipient_id', $char->id)->count());

        // Destroy remaining with everything
        $res2 = $this->actingAs($user)->delete(route('messages.destroy-all'), [
            'scope' => 'everything',
        ]);
        $res2->assertSessionHas('success');
        $this->assertEquals(0, GameMessage::where('recipient_id', $char->id)->count());
    }

    public function test_bulk_action_deletes_and_marks_read_selected_messages(): void
    {
        $user = User::factory()->create();
        $char = $this->createCharacter($user);

        $otherUser = User::factory()->create();
        $otherChar = $this->createCharacter($otherUser);

        $m1 = GameMessage::create([
            'recipient_id' => $char->id,
            'sender_name' => 'Directie',
            'subject' => 'M1',
            'content' => 'C1',
            'type' => 'system',
            'is_read' => false,
        ]);

        $m2 = GameMessage::create([
            'recipient_id' => $char->id,
            'sender_name' => 'Directie',
            'subject' => 'M2',
            'content' => 'C2',
            'type' => 'system',
            'is_read' => false,
        ]);

        $otherMsg = GameMessage::create([
            'recipient_id' => $otherChar->id,
            'sender_name' => 'Directie',
            'subject' => 'M Other',
            'content' => 'C Other',
            'type' => 'system',
            'is_read' => false,
        ]);

        $controller = app(MessageController::class);

        // Bulk mark read
        $reqRead = Request::create('/berichten/bulk', 'POST', [
            'action' => 'mark_read',
            'message_ids' => [$m1->id, $otherMsg->id],
        ]);
        $reqRead->setUserResolver(fn () => $user);

        $controller->bulkAction($reqRead);

        $this->assertTrue($m1->fresh()->is_read);
        $this->assertFalse($otherMsg->fresh()->is_read); // Scoped strictly to char!

        // Bulk delete
        $reqDel = Request::create('/berichten/bulk', 'POST', [
            'action' => 'delete',
            'message_ids' => [$m1->id, $m2->id],
        ]);
        $reqDel->setUserResolver(fn () => $user);

        $controller->bulkAction($reqDel);

        $this->assertDatabaseMissing('game_messages', ['id' => $m1->id]);
        $this->assertDatabaseMissing('game_messages', ['id' => $m2->id]);
        $this->assertDatabaseHas('game_messages', ['id' => $otherMsg->id]);
    }

    /*
    |--------------------------------------------------------------------------
    | ReportController & Report Tests
    |--------------------------------------------------------------------------
    */

    public function test_report_character_creates_pending_report_and_snapshots_name(): void
    {
        $reporterUser = User::factory()->create();
        $reporter = $this->createCharacter($reporterUser, ['name' => 'Melder']);

        $suspectUser = User::factory()->create();
        $suspect = $this->createCharacter($suspectUser, ['name' => 'Oplichter']);

        $response = $this->actingAs($reporterUser)->postJson(route('report.store'), [
            'reported_type' => 'character',
            'reported_id' => $suspect->id,
            'reason' => 'Belediging en scam in kantine',
            'details' => 'Hij probeerde nepgeld te verkopen.',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('reports', [
            'reporter_id' => $reporter->id,
            'reported_type' => 'character',
            'reported_id' => $suspect->id,
            'reported_name' => $suspect->name,
            'status' => 'pending',
        ]);
    }

    public function test_report_character_blocked_for_self_reporting(): void
    {
        $user = User::factory()->create();
        $char = $this->createCharacter($user, ['name' => 'ZelfMelder']);

        $response = $this->actingAs($user)->postJson(route('report.store'), [
            'reported_type' => 'character',
            'reported_id' => $char->id,
            'reason' => 'Ik ben stout geweest',
        ]);

        $response->assertStatus(422);
        $response->assertJson(['success' => false, 'message' => 'Je kunt jezelf niet rapporteren.']);
    }

    public function test_report_gang_creates_report_and_blocks_leader_self_report(): void
    {
        $leaderUser = User::factory()->create();
        $leader = $this->createCharacter($leaderUser, ['name' => 'Baas']);

        $gang = Gang::create([
            'name' => 'Mafia TBS',
            'tag' => 'MTBS',
            'specialty' => 'Kantine',
            'leader_character_id' => $leader->id,
            'treasury' => 1000,
        ]);

        // Leader tries to report own gang -> 422
        $leaderRes = $this->actingAs($leaderUser)->postJson(route('report.store'), [
            'reported_type' => 'gang',
            'reported_id' => $gang->id,
            'reason' => 'Mijn eigen bende rapporteert zichzelf',
        ]);
        $leaderRes->assertStatus(422);
        $leaderRes->assertJson(['success' => false, 'message' => 'Je kunt je eigen bende niet rapporteren.']);

        // Outsider reports gang -> 200
        $outsiderUser = User::factory()->create();
        $outsider = $this->createCharacter($outsiderUser, ['name' => 'Buitenstaander']);

        Cache::flush();

        $res = $this->actingAs($outsiderUser)->postJson(route('report.store'), [
            'reported_type' => 'gang',
            'reported_id' => $gang->id,
            'reason' => 'Ongepaste bendenaam en profiel',
        ]);
        $res->assertStatus(200);

        $this->assertDatabaseHas('reports', [
            'reporter_id' => $outsider->id,
            'reported_type' => 'gang',
            'reported_id' => $gang->id,
            'reported_name' => $gang->name,
        ]);
    }

    public function test_report_chat_message_creates_report_and_blocks_own_message(): void
    {
        $authorUser = User::factory()->create();
        $author = $this->createCharacter($authorUser, ['name' => 'Chatter']);

        $chatMsg = ChatMessage::create([
            'character_id' => $author->id,
            'channel' => 'global',
            'message' => 'Vervelende uitspraak in de chat',
        ]);

        // Author tries to report own chat message -> 422
        $selfRes = $this->actingAs($authorUser)->postJson(route('report.store'), [
            'reported_type' => 'chat_message',
            'reported_id' => $chatMsg->id,
            'reason' => 'Mijn eigen bericht melden',
        ]);
        $selfRes->assertStatus(422);
        $selfRes->assertJson(['success' => false, 'message' => 'Je kunt je eigen chatbericht niet rapporteren.']);

        // Other player reports chat message -> 200
        $watcherUser = User::factory()->create();
        $watcher = $this->createCharacter($watcherUser, ['name' => 'Toeschouwer']);

        Cache::flush();

        $res = $this->actingAs($watcherUser)->postJson(route('report.store'), [
            'reported_type' => 'chat_message',
            'reported_id' => $chatMsg->id,
            'reason' => 'Haatzaaien in chat',
        ]);
        $res->assertStatus(200);

        $report = Report::where('reported_type', 'chat_message')->where('reported_id', $chatMsg->id)->first();
        $this->assertNotNull($report);
        $this->assertEquals('Chatter', $report->reported_name);
        $this->assertStringContainsString('Vervelende uitspraak in de chat', $report->details);
    }

    public function test_report_direct_message_creates_report_and_blocks_idor_and_sender_self_report(): void
    {
        $senderUser = User::factory()->create();
        $sender = $this->createCharacter($senderUser, ['name' => 'DMSender']);

        $receiverUser = User::factory()->create();
        $receiver = $this->createCharacter($receiverUser, ['name' => 'DMReceiver']);

        $thirdUser = User::factory()->create();
        $thirdChar = $this->createCharacter($thirdUser, ['name' => 'DerdePersoon']);

        $dm = DirectMessage::create([
            'sender_id' => $sender->id,
            'recipient_id' => $receiver->id,
            'message' => 'Prive bedreiging via DM',
            'is_read' => false,
        ]);

        // 1. Sender cannot report their own sent DM -> 422
        $senderRes = $this->actingAs($senderUser)->postJson(route('report.store'), [
            'reported_type' => 'direct_message',
            'reported_id' => $dm->id,
            'reason' => 'Mijn eigen DM',
        ]);
        $senderRes->assertStatus(422);
        $senderRes->assertJson(['success' => false, 'message' => 'Je kunt je eigen privébericht niet rapporteren.']);

        // 2. Third person (IDOR attempt) cannot report DM they are not part of -> 403
        Cache::flush();
        $thirdRes = $this->actingAs($thirdUser)->postJson(route('report.store'), [
            'reported_type' => 'direct_message',
            'reported_id' => $dm->id,
            'reason' => 'Afgeluisterde DM',
        ]);
        $thirdRes->assertStatus(403);
        $thirdRes->assertJson(['success' => false, 'message' => 'Je hebt geen toegang tot dit privébericht.']);

        // 3. Legitimate recipient reports harassment -> 200
        Cache::flush();
        $recRes = $this->actingAs($receiverUser)->postJson(route('report.store'), [
            'reported_type' => 'direct_message',
            'reported_id' => $dm->id,
            'reason' => 'Intimidatie in privé chat',
        ]);
        $recRes->assertStatus(200);

        $report = Report::where('reported_type', 'direct_message')->where('reported_id', $dm->id)->first();
        $this->assertNotNull($report);
        $this->assertEquals('DMSender', $report->reported_name);
        $this->assertStringContainsString('Prive bedreiging via DM', $report->details);

        // Check model helpers
        $this->assertEquals($sender->id, $report->getReportedCharacter()?->id);
        $this->assertStringContainsString('tab=direct', $report->getTargetUrl());
    }

    public function test_report_duplicate_and_anti_spam_cooldown(): void
    {
        $userA = User::factory()->create();
        $charA = $this->createCharacter($userA);

        $userB = User::factory()->create();
        $charB = $this->createCharacter($userB);

        Cache::flush();

        // First report -> 200
        $res1 = $this->actingAs($userA)->postJson(route('report.store'), [
            'reported_type' => 'character',
            'reported_id' => $charB->id,
            'reason' => 'Test melding 1',
        ]);
        $res1->assertStatus(200);

        // Immediate next report -> 429 cooldown
        $resCooldown = $this->actingAs($userA)->postJson(route('report.store'), [
            'reported_type' => 'character',
            'reported_id' => $charB->id,
            'reason' => 'Test melding 2',
        ]);
        $resCooldown->assertStatus(429);

        // Clear cooldown, but same target -> 422 duplicate pending
        Cache::forget("report_cooldown_char_{$charA->id}");

        $resDuplicate = $this->actingAs($userA)->postJson(route('report.store'), [
            'reported_type' => 'character',
            'reported_id' => $charB->id,
            'reason' => 'Test melding duplicate',
        ]);
        $resDuplicate->assertStatus(422);
        $resDuplicate->assertJson(['success' => false, 'message' => 'Je hebt hier al een openstaande melding over ingediend. De moderatie pakt dit zo snel mogelijk op.']);
    }

    /*
    |--------------------------------------------------------------------------
    | SupportTicketController & AdminTicketController Tests
    |--------------------------------------------------------------------------
    */

    public function test_user_can_create_ticket_with_xss_sanitization(): void
    {
        $user = User::factory()->create();
        $char = $this->createCharacter($user);

        $response = $this->actingAs($user)->post(route('help.tickets.store'), [
            'subject' => '<script>alert("hack")</script>Vraag over VIP status',
            'category' => 'vip',
            'priority' => 'hoog',
            'message' => '<b>Mijn VIP</b> punten zijn niet bijgeschreven! <img src=x onerror=alert(1)>',
        ]);

        $ticket = SupportTicket::latest('id')->first();
        $this->assertNotNull($ticket);
        $this->assertStringNotContainsString('<script>', $ticket->subject);
        $this->assertEquals('Vraag over VIP status', $ticket->subject);

        $msg = $ticket->messages->first();
        $this->assertNotNull($msg);
        $this->assertStringNotContainsString('<img', $msg->message);
        $this->assertStringNotContainsString('onerror', $msg->message);
    }

    public function test_user_can_create_gang_transfer_ticket_with_link_validation(): void
    {
        $user = User::factory()->create();
        $char = $this->createCharacter($user);

        $response = $this->actingAs($user)->post(route('help.tickets.store'), [
            'category' => 'gang_overstap',
            'gang_name' => 'Peaky Gangsters',
            'gang_tag' => 'PEAK',
            'external_gang_link' => 'barribob.nl/gang/peaky',
            'member_names' => 'Lid1, Lid2',
            'proof_details' => 'Wij zijn top 3 bende',
            'priority' => 'hoog',
        ]);

        $ticket = SupportTicket::latest('id')->first();
        $this->assertNotNull($ticket);
        $this->assertEquals('gang_overstap', $ticket->category);
        $this->assertStringStartsWith('https://barribob.nl', $ticket->metadata['external_gang_link']);
        $this->assertEquals('PEAK', $ticket->metadata['gang_tag']);
    }

    public function test_user_can_reply_and_cannot_reply_to_closed_ticket(): void
    {
        $user = User::factory()->create();
        $char = $this->createCharacter($user);

        $ticket = SupportTicket::create([
            'user_id' => $user->id,
            'character_id' => $char->id,
            'ticket_number' => 'TBS-TST-001',
            'subject' => 'Hulp bij kluis',
            'category' => 'vraag',
            'priority' => 'normaal',
            'status' => 'open',
        ]);

        // Active reply
        $res = $this->actingAs($user)->post(route('help.tickets.reply', $ticket), [
            'message' => 'Ik heb nog een toevoeging op mijn vraag.',
        ]);
        $res->assertSessionHas('success');
        $this->assertCount(1, $ticket->fresh()->messages);

        // Close ticket
        $this->actingAs($user)->post(route('help.tickets.close', $ticket));
        $this->assertEquals('gesloten', $ticket->fresh()->status);

        // Attempt reply to closed ticket -> blocked
        $resClosed = $this->actingAs($user)->post(route('help.tickets.reply', $ticket), [
            'message' => 'Kan ik nog reageren?',
        ]);
        $resClosed->assertSessionHas('error');
        $this->assertCount(1, $ticket->fresh()->messages);
    }

    public function test_user_cannot_access_or_reply_to_another_users_ticket_idor(): void
    {
        $userA = User::factory()->create();
        $charA = $this->createCharacter($userA);

        $userB = User::factory()->create();
        $charB = $this->createCharacter($userB);

        $ticketA = SupportTicket::create([
            'user_id' => $userA->id,
            'character_id' => $charA->id,
            'ticket_number' => 'TBS-PRV-999',
            'subject' => 'Geheim dossier A',
            'category' => 'bug',
            'priority' => 'hoog',
            'status' => 'open',
        ]);

        // User B tries to view ticket A -> 403
        $this->actingAs($userB)->get(route('help.tickets.show', $ticketA))->assertStatus(403);

        // User B tries to reply to ticket A -> 403
        $this->actingAs($userB)->post(route('help.tickets.reply', $ticketA), [
            'message' => 'Ongewenste reactie',
        ])->assertStatus(403);

        // User B tries to close ticket A -> 403
        $this->actingAs($userB)->post(route('help.tickets.close', $ticketA))->assertStatus(403);
    }

    public function test_ticket_formatted_message_escapes_xss_and_formats_markdown(): void
    {
        $msg = new SupportTicketMessage([
            'message' => "**Vetgedrukt** en *schuingedrukt*\n1. Eerste stap\n- Opsomming\n<script>alert(1)</script>javascript:void(0)",
        ]);

        $formatted = $msg->formatted_message;

        $this->assertStringContainsString('Vetgedrukt', $formatted);
        $this->assertStringContainsString('font-extrabold', $formatted);
        $this->assertStringContainsString('schuingedrukt', $formatted);
        $this->assertStringNotContainsString('<script>', $formatted);
        $this->assertStringContainsString('&lt;script&gt;', $formatted);
        $this->assertStringNotContainsString('javascript:', $formatted);
        $this->assertStringContainsString('javascript&#58;', $formatted);
    }

    public function test_non_staff_cannot_access_admin_ticket_controller(): void
    {
        $regularUser = User::factory()->create(['role' => 'user', 'is_staff' => false]);
        $char = $this->createCharacter($regularUser);

        $ticket = SupportTicket::create([
            'user_id' => $regularUser->id,
            'character_id' => $char->id,
            'ticket_number' => 'TBS-ADM-001',
            'subject' => 'Ticket voor admin test',
            'category' => 'vraag',
            'priority' => 'normaal',
            'status' => 'open',
        ]);

        $this->actingAs($regularUser)->post(route('admin.tickets.reply', $ticket), [
            'message' => 'Admin reactie poging',
            'status' => 'beantwoord',
        ])->assertStatus(403);

        $this->actingAs($regularUser)->post(route('admin.tickets.status', $ticket), [
            'status' => 'gesloten',
        ])->assertStatus(403);
    }

    public function test_staff_can_reply_update_status_and_reopen_clears_closed_at(): void
    {
        $staffUser = User::factory()->create(['role' => 'moderator', 'is_staff' => true]);
        $staffChar = $this->createCharacter($staffUser, ['name' => 'ModStaff']);

        $playerUser = User::factory()->create();
        $playerChar = $this->createCharacter($playerUser, ['name' => 'VragendeSpeler']);

        $ticket = SupportTicket::create([
            'user_id' => $playerUser->id,
            'character_id' => $playerChar->id,
            'ticket_number' => 'TBS-STF-123',
            'subject' => 'Hulpvraag speler',
            'category' => 'vraag',
            'priority' => 'normaal',
            'status' => 'open',
        ]);

        // Staff replies and marks closed
        $replyRes = $this->actingAs($staffUser)->post(route('admin.tickets.reply', $ticket), [
            'message' => 'Dit probleem is voor je opgelost!',
            'status' => 'gesloten',
        ]);
        $replyRes->assertSessionHas('success');

        $ticket->refresh();
        $this->assertEquals('gesloten', $ticket->status);
        $this->assertNotNull($ticket->closed_at);
        $this->assertTrue($ticket->last_reply_by_staff);

        // Staff updates status back to 'in_behandeling' -> closed_at cleared!
        $statusRes = $this->actingAs($staffUser)->post(route('admin.tickets.status', $ticket), [
            'status' => 'in_behandeling',
        ]);
        $statusRes->assertSessionHas('success');

        $ticket->refresh();
        $this->assertEquals('in_behandeling', $ticket->status);
        $this->assertNull($ticket->closed_at);
    }

    public function test_admin_can_approve_gang_transfer_and_non_admin_is_rejected(): void
    {
        $adminUser = User::factory()->create(['role' => 'admin', 'is_staff' => true]);
        $adminChar = $this->createCharacter($adminUser, ['name' => 'SuperAdmin']);

        $modUser = User::factory()->create(['role' => 'moderator', 'is_staff' => true]);
        $modChar = $this->createCharacter($modUser, ['name' => 'ModeratorPiet']);

        $playerUser = User::factory()->create();
        $playerChar = $this->createCharacter($playerUser, ['name' => 'BendeLeiderInWording']);

        $ticket = SupportTicket::create([
            'user_id' => $playerUser->id,
            'character_id' => $playerChar->id,
            'ticket_number' => 'TBS-GNG-999',
            'subject' => 'Gang Overstap Bonus: Cosa Nostra [CN]',
            'category' => 'gang_overstap',
            'priority' => 'hoog',
            'status' => 'open',
            'metadata' => [
                'gang_name' => 'Cosa Nostra',
                'gang_tag' => 'CN',
                'external_gang_link' => 'https://example.com/gang/cn',
            ],
        ]);

        // Moderator (non-admin) cannot approve -> rejected
        $modRes = $this->actingAs($modUser)->post(route('admin.tickets.approve_gang', $ticket), [
            'gang_name' => 'Cosa Nostra',
            'gang_tag' => 'CN',
            'bonus_cash' => 5000000,
            'vip_days' => 14,
        ]);
        $modRes->assertSessionHas('error', 'Alleen beheerders kunnen gang overstap aanvragen goedkeuren.');
        $this->assertFalse($ticket->fresh()->isGangTransferApproved());

        // Admin approves -> success!
        $adminRes = $this->actingAs($adminUser)->post(route('admin.tickets.approve_gang', $ticket), [
            'gang_name' => 'Cosa Nostra',
            'gang_tag' => 'CN',
            'bonus_cash' => 5000000,
            'vip_days' => 14,
        ]);
        $adminRes->assertSessionHas('success');

        $gang = Gang::where('name', 'Cosa Nostra')->first();
        $this->assertNotNull($gang);
        $this->assertEquals(5000000, $gang->treasury);
        $this->assertEquals($playerChar->id, $gang->leader_character_id);

        $playerChar->refresh();
        $this->assertEquals($gang->id, $playerChar->gang_id);
        $this->assertEquals('leader', $playerChar->gang_role);
        $this->assertTrue($playerChar->isVip());
    }
}
