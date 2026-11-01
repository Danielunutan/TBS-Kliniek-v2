<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\ChatMessage;
use App\Models\DirectMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class ChatAuditBucket3Test extends TestCase
{
    use RefreshDatabase;

    private function createInmate(array $attributes = [], array $userAttributes = []): array
    {
        $user = User::factory()->create($userAttributes);
        $character = Character::create(array_merge([
            'user_id' => $user->id,
            'name' => 'Inmate'.rand(1000, 9999),
            'cash' => 1000,
            'bank' => 5000,
            'stars' => 50,
            'level' => 2,
            'xp' => 10,
            'health' => 100,
            'max_health' => 100,
            'energy' => 100,
            'max_energy' => 100,
            'respect' => 25,
            'is_onboarded' => true,
        ], $attributes));

        return [$user, $character];
    }

    public function test_mini_chat_blade_view_does_not_contain_broken_synchronous_confirm_calls(): void
    {
        $viewContent = file_get_contents(resource_path('views/partials/mini_chat_and_report_modal.blade.php'));

        $this->assertStringNotContainsString(
            'if (!confirm(',
            $viewContent,
            'The mini chat view must not use broken synchronous confirm() calls because window.confirm returns false.'
        );

        $this->assertStringContainsString(
            'window.tbsConfirm',
            $viewContent,
            'The mini chat view must use window.tbsConfirm for interactive modal confirmations.'
        );

        $this->assertStringContainsString(
            "title: 'Bericht Verwijderen'",
            $viewContent
        );

        $this->assertStringContainsString(
            "title: 'Privébericht Verwijderen'",
            $viewContent
        );
    }

    public function test_mini_chat_blade_view_uses_type_safe_numeric_comparison_for_deletion(): void
    {
        $viewContent = file_get_contents(resource_path('views/partials/mini_chat_and_report_modal.blade.php'));

        $this->assertStringContainsString(
            'Number(m.id) !== Number(msgId)',
            $viewContent,
            'deletePublicMessage must use type-safe numeric comparison.'
        );

        $this->assertStringContainsString(
            'Number(m.id) !== Number(dmId)',
            $viewContent,
            'deleteDirectMessage must use type-safe numeric comparison.'
        );

        $this->assertStringContainsString(
            'Number(this.replyingTo.id) === Number(msgId)',
            $viewContent,
            'deletePublicMessage must clear replyingTo when matching message is deleted.'
        );

        $this->assertStringContainsString(
            'Number(this.directReplyingTo.id) === Number(dmId)',
            $viewContent,
            'deleteDirectMessage must clear directReplyingTo when matching direct message is deleted.'
        );

        $this->assertStringContainsString(
            'this.loadConversations()',
            $viewContent,
            'deleteDirectMessage must reload conversations to refresh the preview snippet and time.'
        );
    }

    public function test_mini_chat_blade_view_normalizes_deleted_ids_with_number(): void
    {
        $viewContent = file_get_contents(resource_path('views/partials/mini_chat_and_report_modal.blade.php'));

        $this->assertStringContainsString(
            'const delSet = new Set(data.deleted_ids.map(Number));',
            $viewContent,
            'Polling deleted_ids sets must normalize incoming IDs to Numbers.'
        );

        $this->assertStringContainsString(
            '!delSet.has(Number(m.id))',
            $viewContent,
            'Filtering by deleted_ids must check Number(m.id).'
        );
    }

    public function test_mini_chat_blade_view_guards_async_state_when_switching_dm_partners(): void
    {
        $viewContent = file_get_contents(resource_path('views/partials/mini_chat_and_report_modal.blade.php'));

        $this->assertStringContainsString(
            'Number(currentActiveId) !== Number(pId)',
            $viewContent,
            'openDirectChat and pollActiveDirectMessages must guard against race conditions when switching partners.'
        );

        $this->assertStringContainsString(
            'this.cancelDirectReply()',
            $viewContent,
            'openDirectChat and tab switching must clear active reply quotes.'
        );
    }

    public function test_mini_chat_blade_view_renders_delete_button_for_system_cards_and_recipients(): void
    {
        $viewContent = file_get_contents(resource_path('views/partials/mini_chat_and_report_modal.blade.php'));

        // Check system DM cards have delete button
        $this->assertStringContainsString(
            "dm.type === 'kluiscode'",
            $viewContent
        );
        $this->assertStringContainsString(
            '@click="deleteDirectMessage(dm.id)"',
            $viewContent
        );

        // Check recipient direct message delete permission
        $this->assertStringContainsString(
            "canModerate || dm.is_me || dm.can_delete || activeTab === 'direct'",
            $viewContent,
            'Direct message delete button must be visible to recipient in direct chat.'
        );

        // Check mobile accessibility
        $this->assertStringContainsString(
            'opacity-80 sm:opacity-0 sm:group-hover:opacity-100 group-focus-within:opacity-100 hover:opacity-100',
            $viewContent,
            'Direct message action buttons must be accessible on mobile touchscreens without requiring hover.'
        );
    }

    public function test_mini_chat_and_report_modal_use_tbs_toast_and_alert_helpers(): void
    {
        $viewContent = file_get_contents(resource_path('views/partials/mini_chat_and_report_modal.blade.php'));

        $this->assertStringContainsString(
            'window.tbsToast',
            $viewContent,
            'The view must use window.tbsToast for user feedback.'
        );

        $this->assertStringContainsString(
            '#tbsConfirmModal, .tbs-confirm-modal, [x-data*="isAlert"]',
            $viewContent,
            'The view must elevate confirm modal z-index above mini chat.'
        );
    }

    public function test_authenticated_inmate_renders_dashboard_with_mini_chat_and_report_modal(): void
    {
        [$user, $character] = $this->createInmate();

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('id="tbs-floating-mini-chat"', false);
        $response->assertSee('Melding Indienen');
        $response->assertSee('tbsGlobalReportModal()', false);
        $response->assertSee('tbsMiniLiveChat(', false);
    }

    public function test_author_can_delete_own_public_chat_message(): void
    {
        [$user, $character] = $this->createInmate();

        $msg = ChatMessage::create([
            'character_id' => $character->id,
            'channel' => 'global',
            'message' => 'Bericht om te verwijderen',
            'hearts_count' => 0,
            'hearts_users' => [],
        ]);

        $response = $this->actingAs($user)->deleteJson("/chat/openbaar/{$msg->id}");

        $response->assertOk();
        $response->assertJson(['success' => true]);

        $this->assertDatabaseMissing('chat_messages', [
            'id' => $msg->id,
        ]);

        $deletedIds = Cache::get('chat_deleted_ids_global', []);
        $this->assertContains($msg->id, $deletedIds);
    }

    public function test_moderator_can_delete_other_players_public_message(): void
    {
        [$userA, $characterA] = $this->createInmate();
        [$modUser, $modCharacter] = $this->createInmate([], ['role' => 'moderator']);

        $msg = ChatMessage::create([
            'character_id' => $characterA->id,
            'channel' => 'global',
            'message' => 'Spam bericht',
            'hearts_count' => 0,
            'hearts_users' => [],
        ]);

        $response = $this->actingAs($modUser)->deleteJson("/chat/openbaar/{$msg->id}");

        $response->assertOk();
        $response->assertJson(['success' => true]);

        $this->assertDatabaseMissing('chat_messages', [
            'id' => $msg->id,
        ]);
    }

    public function test_regular_player_cannot_delete_other_players_public_message(): void
    {
        [$userA, $characterA] = $this->createInmate();
        [$userB, $characterB] = $this->createInmate();

        $msg = ChatMessage::create([
            'character_id' => $characterA->id,
            'channel' => 'global',
            'message' => 'Niet jouw bericht',
            'hearts_count' => 0,
            'hearts_users' => [],
        ]);

        $response = $this->actingAs($userB)->deleteJson("/chat/openbaar/{$msg->id}");

        $response->assertStatus(403);
        $this->assertDatabaseHas('chat_messages', [
            'id' => $msg->id,
        ]);
    }

    public function test_recipient_can_delete_direct_message_including_system_notifications(): void
    {
        [$user, $character] = $this->createInmate();

        // System notification DM (sender_id = null)
        $systemDm = DirectMessage::create([
            'sender_id' => null,
            'recipient_id' => $character->id,
            'message' => 'Je hebt kluiscode gekregen',
            'type' => 'kluiscode',
            'metadata' => ['code' => 'TEST1234'],
            'is_read' => true,
        ]);

        $response = $this->actingAs($user)->deleteJson("/chat/prive-bericht/{$systemDm->id}");

        $response->assertOk();
        $response->assertJson(['success' => true]);

        $this->assertDatabaseMissing('direct_messages', [
            'id' => $systemDm->id,
        ]);
    }

    public function test_inmate_can_submit_report_via_universal_report_endpoint(): void
    {
        [$userA, $characterA] = $this->createInmate();
        [$userB, $characterB] = $this->createInmate();

        $msg = ChatMessage::create([
            'character_id' => $characterB->id,
            'channel' => 'global',
            'message' => 'Ongepaste taal op het plein',
            'hearts_count' => 0,
            'hearts_users' => [],
        ]);

        $response = $this->actingAs($userA)->postJson(route('report.store'), [
            'reported_type' => 'chat_message',
            'reported_id' => $msg->id,
            'reason' => 'Belediging / Schelden',
            'details' => 'Schold zomaar op de chat',
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('reports', [
            'reporter_id' => $characterA->id,
            'reported_type' => 'chat_message',
            'reported_id' => $msg->id,
            'reason' => 'Belediging / Schelden',
            'status' => 'pending',
        ]);
    }
}
