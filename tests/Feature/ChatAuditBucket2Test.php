<?php

namespace Tests\Feature;

use App\Http\Controllers\FaqController;
use App\Models\Character;
use App\Models\ChatMessage;
use App\Models\DirectMessage;
use App\Models\Gang;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChatAuditBucket2Test extends TestCase
{
    use RefreshDatabase;

    public function test_chat_page_renders_properly_for_authenticated_character(): void
    {
        $user = User::factory()->create();
        $character = Character::create([
            'user_id' => $user->id,
            'name' => 'InmateAlpha',
            'cash' => 1000,
            'bank' => 2000,
            'level' => 3,
            'energy' => 100,
            'max_energy' => 100,
            'respect' => 150,
            'is_onboarded' => true,
        ]);

        $response = $this->actingAs($user)->get('/chat');

        $response->assertOk();
        $response->assertViewIs('game.chat.index');
        $response->assertSee('Kliniek Live Chat', false);
        $response->assertSee('tbsLiveMessenger', false);
    }

    public function test_chat_view_uses_tbs_confirm_and_has_no_broken_synchronous_confirm_checks(): void
    {
        $viewContent = file_get_contents(resource_path('views/game/chat/index.blade.php'));

        $this->assertStringNotContainsString(
            'if (!confirm(',
            $viewContent,
            'The view should not have broken synchronous confirm() calls that abort prematurely.'
        );

        $this->assertStringContainsString(
            'window.tbsConfirm',
            $viewContent,
            'The view should use window.tbsConfirm for interactive modal confirmations.'
        );

        $this->assertStringContainsString(
            'deleteDirectMessage(dmId)',
            $viewContent
        );

        $this->assertStringContainsString(
            'deletePublicMessage(msgId)',
            $viewContent
        );

        $this->assertStringContainsString(
            'this.loadConversations()',
            $viewContent,
            'deleteDirectMessage should refresh conversations so the latest message snippet updates.'
        );

        $this->assertStringContainsString(
            'window.tbsToast',
            $viewContent,
            'delete methods should trigger window.tbsToast feedback on success.'
        );
    }

    public function test_select_partner_resets_direct_messages_list_when_switching_partners(): void
    {
        $viewContent = file_get_contents(resource_path('views/game/chat/index.blade.php'));

        $this->assertStringContainsString(
            'selectPartner(partnerId, partnerName = \'\') {',
            $viewContent
        );

        $this->assertStringContainsString(
            'this.directMessagesList = [];',
            $viewContent,
            'selectPartner must reset directMessagesList to prevent leaking previous partner messages.'
        );

        $this->assertStringContainsString(
            'Number(this.activePartnerId) !== Number(partnerId)',
            $viewContent,
            'selectPartner and loadDirectMessages must use numeric ID comparison.'
        );
    }

    public function test_chat_view_includes_delete_button_for_system_dms_and_user_conversation(): void
    {
        $viewContent = file_get_contents(resource_path('views/game/chat/index.blade.php'));

        // Check liquidation card delete button
        $this->assertMatchesRegularExpression(
            '/dm\.type === \'liquidation\'[\s\S]*?deleteDirectMessage\(dm\.id\)/',
            $viewContent,
            'Liquidation system DM card must have a delete button.'
        );

        // Check kluiscode card delete button
        $this->assertMatchesRegularExpression(
            '/dm\.type === \'kluiscode\'[\s\S]*?deleteDirectMessage\(dm\.id\)/',
            $viewContent,
            'Kluiscode system DM card must have a delete button.'
        );

        // Check level_up card delete button
        $this->assertMatchesRegularExpression(
            '/dm\.type === \'level_up\'[\s\S]*?deleteDirectMessage\(dm\.id\)/',
            $viewContent,
            'Level up system DM card must have a delete button.'
        );

        // Check standard DM delete condition
        $this->assertStringContainsString(
            "canModerate || dm.is_me || dm.can_delete || activeTab === 'direct'",
            $viewContent,
            'Standard DM should allow deletion for conversation participants.'
        );
    }

    public function test_public_message_deletion_endpoint_works_for_author(): void
    {
        $user = User::factory()->create();
        $character = Character::create([
            'user_id' => $user->id,
            'name' => 'AuthorInmate',
            'cash' => 500,
            'bank' => 1000,
            'level' => 2,
            'energy' => 100,
            'max_energy' => 100,
            'respect' => 50,
            'is_onboarded' => true,
        ]);

        $message = ChatMessage::create([
            'character_id' => $character->id,
            'channel' => 'global',
            'message' => 'Dit is een testbericht om te verwijderen.',
        ]);

        $response = $this->actingAs($user)->deleteJson("/chat/openbaar/{$message->id}");

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'message' => 'Bericht succesvol verwijderd.',
        ]);

        $this->assertDatabaseMissing('chat_messages', [
            'id' => $message->id,
        ]);
    }

    public function test_direct_message_deletion_endpoint_works_for_sender_and_recipient(): void
    {
        $userSender = User::factory()->create();
        $charSender = Character::create([
            'user_id' => $userSender->id,
            'name' => 'SenderInmate',
            'cash' => 500,
            'bank' => 1000,
            'level' => 2,
            'energy' => 100,
            'max_energy' => 100,
            'respect' => 50,
            'is_onboarded' => true,
        ]);

        $userRecipient = User::factory()->create();
        $charRecipient = Character::create([
            'user_id' => $userRecipient->id,
            'name' => 'RecipientInmate',
            'cash' => 500,
            'bank' => 1000,
            'level' => 2,
            'energy' => 100,
            'max_energy' => 100,
            'respect' => 50,
            'is_onboarded' => true,
        ]);

        $dm = DirectMessage::create([
            'sender_id' => $charSender->id,
            'recipient_id' => $charRecipient->id,
            'message' => 'Geheim bericht tussen inmates.',
            'type' => 'chat',
        ]);

        // Recipient can delete this DM
        $response = $this->actingAs($userRecipient)->deleteJson("/chat/prive-bericht/{$dm->id}");

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'message' => 'Bericht verwijderd.',
        ]);

        $this->assertDatabaseMissing('direct_messages', [
            'id' => $dm->id,
        ]);
    }

    public function test_gang_message_deletion_works_for_author(): void
    {
        $user = User::factory()->create();
        $character = Character::create([
            'user_id' => $user->id,
            'name' => 'GangMember',
            'cash' => 500,
            'bank' => 1000,
            'level' => 5,
            'energy' => 100,
            'max_energy' => 100,
            'respect' => 200,
            'is_onboarded' => true,
        ]);

        $gang = Gang::create([
            'name' => 'Audit Gang',
            'tag' => 'AUDT',
            'specialty' => 'combat',
            'leader_character_id' => $character->id,
            'has_gang_chat' => true,
        ]);
        $character->update(['gang_id' => $gang->id]);

        $msg = ChatMessage::create([
            'character_id' => $character->id,
            'channel' => "gang_{$gang->id}",
            'message' => 'Portofoon test bericht.',
        ]);

        $response = $this->actingAs($user)->deleteJson("/chat/openbaar/{$msg->id}");

        $response->assertOk();
        $response->assertJson([
            'success' => true,
        ]);

        $this->assertDatabaseMissing('chat_messages', [
            'id' => $msg->id,
        ]);
    }

    public function test_unauthorized_user_cannot_delete_other_user_public_message(): void
    {
        $userAuthor = User::factory()->create();
        $charAuthor = Character::create([
            'user_id' => $userAuthor->id,
            'name' => 'OriginalAuthor',
            'cash' => 500,
            'bank' => 1000,
            'level' => 2,
            'energy' => 100,
            'max_energy' => 100,
            'respect' => 50,
            'is_onboarded' => true,
        ]);

        $userStranger = User::factory()->create();
        $charStranger = Character::create([
            'user_id' => $userStranger->id,
            'name' => 'StrangerInmate',
            'cash' => 500,
            'bank' => 1000,
            'level' => 2,
            'energy' => 100,
            'max_energy' => 100,
            'respect' => 50,
            'is_onboarded' => true,
        ]);

        $message = ChatMessage::create([
            'character_id' => $charAuthor->id,
            'channel' => 'global',
            'message' => 'Dit bericht mag niet door anderen worden verwijderd.',
        ]);

        $response = $this->actingAs($userStranger)->deleteJson("/chat/openbaar/{$message->id}");

        $response->assertStatus(403);
        $this->assertDatabaseHas('chat_messages', [
            'id' => $message->id,
        ]);
    }

    public function test_moderator_can_delete_other_user_messages_and_dms(): void
    {
        $modUser = User::factory()->create(['role' => 'moderator']);
        $modChar = Character::create([
            'user_id' => $modUser->id,
            'name' => 'ModInmate',
            'cash' => 500,
            'bank' => 1000,
            'level' => 10,
            'energy' => 100,
            'max_energy' => 100,
            'respect' => 500,
            'is_onboarded' => true,
        ]);

        $authorUser = User::factory()->create();
        $authorChar = Character::create([
            'user_id' => $authorUser->id,
            'name' => 'AuthorUser',
            'cash' => 500,
            'bank' => 1000,
            'level' => 2,
            'energy' => 100,
            'max_energy' => 100,
            'respect' => 50,
            'is_onboarded' => true,
        ]);

        $recipientUser = User::factory()->create();
        $recipientChar = Character::create([
            'user_id' => $recipientUser->id,
            'name' => 'RecipientUser',
            'cash' => 500,
            'bank' => 1000,
            'level' => 2,
            'energy' => 100,
            'max_energy' => 100,
            'respect' => 50,
            'is_onboarded' => true,
        ]);

        // Moderator can delete public message
        $publicMsg = ChatMessage::create([
            'character_id' => $authorChar->id,
            'channel' => 'global',
            'message' => 'Spam bericht op het plein.',
        ]);

        $responsePub = $this->actingAs($modUser)->deleteJson("/chat/openbaar/{$publicMsg->id}");
        $responsePub->assertOk();
        $this->assertDatabaseMissing('chat_messages', ['id' => $publicMsg->id]);

        // Moderator can delete direct message
        $dmMsg = DirectMessage::create([
            'sender_id' => $authorChar->id,
            'recipient_id' => $recipientChar->id,
            'message' => 'Ongepast privébericht.',
            'type' => 'chat',
        ]);

        $responseDm = $this->actingAs($modUser)->deleteJson("/chat/prive-bericht/{$dmMsg->id}");
        $responseDm->assertOk();
        $this->assertDatabaseMissing('direct_messages', ['id' => $dmMsg->id]);
    }

    public function test_chat_view_uses_numeric_id_mapping_for_deleted_ids_filtering(): void
    {
        $viewContent = file_get_contents(resource_path('views/game/chat/index.blade.php'));

        $this->assertStringContainsString(
            'new Set(data.deleted_ids.map(Number))',
            $viewContent,
            'deleted_ids checks must map values to Number to avoid type mismatches.'
        );

        $this->assertStringContainsString(
            '!delSet.has(Number(m.id))',
            $viewContent,
            'delSet.has must check Number(m.id).'
        );
    }

    public function test_replying_to_resets_upon_message_deletion_in_view(): void
    {
        $viewContent = file_get_contents(resource_path('views/game/chat/index.blade.php'));

        $this->assertStringContainsString(
            'if (this.replyingTo && Number(this.replyingTo.id) === Number(dmId)) {',
            $viewContent,
            'deleteDirectMessage should reset replyingTo if the replying message was deleted.'
        );

        $this->assertStringContainsString(
            'if (this.replyingTo && Number(this.replyingTo.id) === Number(msgId)) {',
            $viewContent,
            'deletePublicMessage should reset replyingTo if the replying message was deleted.'
        );
    }

    public function test_chat_view_replaces_all_raw_alerts_with_toast_or_modal(): void
    {
        $viewContent = file_get_contents(resource_path('views/game/chat/index.blade.php'));

        $lines = explode("\n", $viewContent);
        $nakedAlerts = [];
        foreach ($lines as $idx => $line) {
            if (preg_match('/\balert\s*\(/', $line)) {
                // Ensure every alert is part of a window.tbsToast / tbsAlert fallback
                $context = implode("\n", array_slice($lines, max(0, $idx - 8), 9));
                if (! str_contains($context, 'window.tbsToast') && ! str_contains($context, 'tbsAlert')) {
                    $nakedAlerts[] = 'Line '.($idx + 1).': '.trim($line);
                }
            }
        }

        $this->assertEmpty($nakedAlerts, 'Found naked alert() calls without tbsToast/tbsAlert fallback: '.implode(', ', $nakedAlerts));
    }

    public function test_chat_page_renders_ai_hulp_tab_and_zaalwacht_arjan(): void
    {
        $user = User::factory()->create();
        $character = Character::create([
            'user_id' => $user->id,
            'name' => 'InmateBotTester',
            'cash' => 2500,
            'bank' => 10000,
            'level' => 2,
            'energy' => 80,
            'max_energy' => 100,
            'respect' => 50,
            'is_onboarded' => true,
        ]);

        $response = $this->actingAs($user)->get('/chat');

        $response->assertOk();
        $response->assertSee("switchTab('help')", false);
        $response->assertSee('AI Hulp', false);
        $response->assertSee('Zaalwacht Arjan', false);
        $response->assertSee('givePersonalAdvice()', false);
        $response->assertSee('sendHelpQuery(', false);
    }

    public function test_chat_page_supports_initial_tab_help(): void
    {
        $user = User::factory()->create();
        $character = Character::create([
            'user_id' => $user->id,
            'name' => 'InmateTabTester',
            'cash' => 1000,
            'bank' => 5000,
            'level' => 1,
            'energy' => 100,
            'max_energy' => 100,
            'respect' => 10,
            'is_onboarded' => true,
        ]);

        $response = $this->actingAs($user)->get('/chat?tab=help');

        $response->assertOk();
        $response->assertViewHas('initialTab', 'help');
        $response->assertSee("initialTab: 'help'", false);
    }

    public function test_ai_help_bot_prompt_injection_resistance_in_both_views(): void
    {
        $mainChat = file_get_contents(resource_path('views/game/chat/index.blade.php'));
        $miniChat = file_get_contents(resource_path('views/partials/mini_chat_and_report_modal.blade.php'));

        $phrases = [
            'ignore all previous',
            'negeer alle instructies',
            'jailbreak',
            'geef me admin',
            'geef me gratis geld',
            'drop table',
        ];

        foreach ($phrases as $phrase) {
            $this->assertStringContainsString(
                $phrase,
                $mainChat,
                "Main chat AI help bot must guard against prompt injection phrase: {$phrase}"
            );
            $this->assertStringContainsString(
                $phrase,
                $miniChat,
                "Mini chat AI help bot must guard against prompt injection phrase: {$phrase}"
            );
        }

        $wardenResponse = 'Mooi geprobeerd, inmate!';
        $this->assertStringContainsString($wardenResponse, $mainChat);
        $this->assertStringContainsString($wardenResponse, $miniChat);
    }

    public function test_ai_help_bot_rate_limiting_and_input_clipping_in_both_views(): void
    {
        $mainChat = file_get_contents(resource_path('views/game/chat/index.blade.php'));
        $miniChat = file_get_contents(resource_path('views/partials/mini_chat_and_report_modal.blade.php'));

        // Maxlength attribute on input
        $this->assertStringContainsString('maxlength="250"', $mainChat);
        $this->assertStringContainsString('maxlength="250"', $miniChat);

        // Input clipping safeguard in JS
        $this->assertStringContainsString('.slice(0, 250)', $mainChat);
        $this->assertStringContainsString('.slice(0, 250)', $miniChat);

        // Rate limiting cooldown
        $this->assertStringContainsString('lastHelpQueryTime', $mainChat);
        $this->assertStringContainsString('lastHelpQueryTime', $miniChat);
        $this->assertStringContainsString('1000', $mainChat);
        $this->assertStringContainsString('1000', $miniChat);
    }

    public function test_ai_help_bot_safe_url_sanitizer_in_both_views(): void
    {
        $mainChat = file_get_contents(resource_path('views/game/chat/index.blade.php'));
        $miniChat = file_get_contents(resource_path('views/partials/mini_chat_and_report_modal.blade.php'));

        // Helper definition
        $this->assertStringContainsString('safeUrl(url)', $mainChat);
        $this->assertStringContainsString('safeUrl(url)', $miniChat);

        // Binding on link href
        $this->assertStringContainsString(':href="safeUrl(lnk.url)"', $mainChat);
        $this->assertStringContainsString(':href="safeUrl(lnk.url)"', $miniChat);
    }

    public function test_chat_view_defines_audio_chime_synthesizer_with_suspended_guard(): void
    {
        $mainChat = file_get_contents(resource_path('views/game/chat/index.blade.php'));

        $this->assertStringContainsString('window.playTBSChime = function()', $mainChat);
        $this->assertStringContainsString("if (ctx.state === 'suspended')", $mainChat);
        $this->assertStringContainsString('ctx.resume().catch(() => {})', $mainChat);
    }

    public function test_chat_view_polling_resilience_and_exponential_backoff(): void
    {
        $mainChat = file_get_contents(resource_path('views/game/chat/index.blade.php'));

        $this->assertStringContainsString('consecutivePollErrors: 0', $mainChat);
        $this->assertStringContainsString('lastPollAttempt: 0', $mainChat);
        $this->assertStringContainsString('Math.min(30000, 3500 * Math.pow(1.5, this.consecutivePollErrors))', $mainChat);
        $this->assertStringContainsString('this.consecutivePollErrors = 0;', $mainChat);
        $this->assertStringContainsString('this.consecutivePollErrors++;', $mainChat);
    }

    public function test_replying_to_resets_upon_receiving_deleted_ids_during_polling(): void
    {
        $mainChat = file_get_contents(resource_path('views/game/chat/index.blade.php'));

        $this->assertStringContainsString(
            'if (this.replyingTo && delSet.has(Number(this.replyingTo.id))) {',
            $mainChat,
            'All polling routines must reset replyingTo when the quoted message is deleted.'
        );
    }

    public function test_pinned_conversations_render_pin_badge_in_both_views(): void
    {
        $mainChat = file_get_contents(resource_path('views/game/chat/index.blade.php'));
        $miniChat = file_get_contents(resource_path('views/partials/mini_chat_and_report_modal.blade.php'));

        $this->assertStringContainsString('conv.is_pinned', $mainChat);
        $this->assertStringContainsString('fa-thumbtack', $mainChat);

        $this->assertStringContainsString('conv.is_pinned', $miniChat);
        $this->assertStringContainsString('fa-thumbtack', $miniChat);
    }

    public function test_faq_controller_bot_knowledge_returns_valid_rich_items_and_routes(): void
    {
        $knowledge = FaqController::getBotKnowledge();

        $this->assertIsArray($knowledge);
        $this->assertNotEmpty($knowledge);

        foreach ($knowledge as $item) {
            $this->assertArrayHasKey('title', $item);
            $this->assertArrayHasKey('keywords', $item);
            $this->assertArrayHasKey('answer', $item);
            $this->assertNotEmpty($item['title']);
            $this->assertNotEmpty($item['answer']);

            if (isset($item['links'])) {
                $this->assertIsArray($item['links']);
                foreach ($item['links'] as $link) {
                    $this->assertArrayHasKey('text', $link);
                    $this->assertArrayHasKey('url', $link);
                    $this->assertNotEmpty($link['url']);
                }
            }
        }
    }

    public function test_admin_and_gang_leader_deletion_authorizations(): void
    {
        $adminUser = User::factory()->create(['role' => 'admin']);
        $adminChar = Character::create([
            'user_id' => $adminUser->id,
            'name' => 'AdminInmate',
            'cash' => 1000,
            'bank' => 5000,
            'level' => 20,
            'energy' => 100,
            'max_energy' => 100,
            'respect' => 1000,
            'is_onboarded' => true,
        ]);

        $authorUser = User::factory()->create();
        $authorChar = Character::create([
            'user_id' => $authorUser->id,
            'name' => 'MemberInmate',
            'cash' => 500,
            'bank' => 1000,
            'level' => 3,
            'energy' => 100,
            'max_energy' => 100,
            'respect' => 100,
            'is_onboarded' => true,
        ]);

        $gang = Gang::create([
            'name' => 'Leadership Gang',
            'tag' => 'LEAD',
            'specialty' => 'combat',
            'leader_character_id' => $adminChar->id,
            'has_gang_chat' => true,
        ]);

        $authorChar->update(['gang_id' => $gang->id]);

        $gangMsg = ChatMessage::create([
            'character_id' => $authorChar->id,
            'channel' => "gang_{$gang->id}",
            'message' => 'Bericht in bende chat.',
        ]);

        // Admin can delete gang message
        $responseAdmin = $this->actingAs($adminUser)->deleteJson("/chat/openbaar/{$gangMsg->id}");
        $responseAdmin->assertOk();
        $this->assertDatabaseMissing('chat_messages', ['id' => $gangMsg->id]);
    }
}
