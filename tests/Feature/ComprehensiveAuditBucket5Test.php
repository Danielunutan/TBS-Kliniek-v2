<?php

namespace Tests\Feature;

use App\Http\Controllers\FaqController;
use App\Models\Character;
use App\Models\ChatMessage;
use App\Models\DirectMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class ComprehensiveAuditBucket5Test extends TestCase
{
    use RefreshDatabase;

    private function createInmate(array $attributes = [], bool $isStaff = false, string $role = 'player'): array
    {
        $user = User::factory()->create([
            'role' => $isStaff ? 'admin' : $role,
            'is_onboarded' => true,
        ]);

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
            'is_in_prison' => false,
            'prison_until' => null,
            'coma_until' => null,
            'last_active_at' => now(),
        ], $attributes));

        return [$user, $character];
    }

    public function test_player_can_send_public_chat_message(): void
    {
        [$user, $character] = $this->createInmate(['name' => 'JanBoef']);

        $response = $this->actingAs($user)->postJson(route('chat.public.send'), [
            'message' => 'Hallo allemaal op het plein!',
            'channel' => 'global',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => [
                'message' => 'Hallo allemaal op het plein!',
                'name' => 'JanBoef',
                'is_me' => true,
            ],
        ]);

        $this->assertDatabaseHas('chat_messages', [
            'character_id' => $character->id,
            'message' => 'Hallo allemaal op het plein!',
            'channel' => 'global',
        ]);
    }

    public function test_public_chat_strips_html_tags_for_xss_protection(): void
    {
        [$user, $character] = $this->createInmate();

        $response = $this->actingAs($user)->postJson(route('chat.public.send'), [
            'message' => '<script>alert("xss")</script><b>Vetgedrukt</b> test',
            'channel' => 'global',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => [
                'message' => 'alert("xss")Vetgedrukt test',
            ],
        ]);
    }

    public function test_public_chat_blocks_comatose_and_imprisoned_players(): void
    {
        // 1. Imprisoned player
        [$userPrison, $charPrison] = $this->createInmate([
            'is_in_prison' => true,
            'prison_until' => now()->addMinutes(15),
        ]);

        $responsePrison = $this->actingAs($userPrison)->postJson(route('chat.public.send'), [
            'message' => 'Ik schreeuw vanuit mijn cel!',
            'channel' => 'global',
        ]);
        $responsePrison->assertStatus(422);
        $responsePrison->assertJson(['success' => false]);
        $this->assertStringContainsString('isoleercel', $responsePrison->json('message'));

        // 2. Comatose player
        [$userComa, $charComa] = $this->createInmate([
            'health' => 0,
            'coma_until' => now()->addMinutes(30),
        ]);

        $responseComa = $this->actingAs($userComa)->postJson(route('chat.public.send'), [
            'message' => 'Bericht vanuit hartstilstand',
            'channel' => 'global',
        ]);
        $responseComa->assertStatus(422);
        $responseComa->assertJson(['success' => false]);
        $this->assertStringContainsString('Code Blauw', $responseComa->json('message'));
    }

    public function test_public_chat_enforces_anti_spam_and_link_rules(): void
    {
        [$user, $character] = $this->createInmate();

        // 1. Link filtering: unauthorized external links blocked
        $responseLink = $this->actingAs($user)->postJson(route('chat.public.send'), [
            'message' => 'Kijk op https://malicious-site.com voor gratis items',
            'channel' => 'global',
        ]);
        $responseLink->assertStatus(422);
        $this->assertStringContainsString('externe links', $responseLink->json('message'));

        // 2. Discord invite allowed
        $responseDiscord = $this->actingAs($user)->postJson(route('chat.public.send'), [
            'message' => 'Kom naar onze server: discord.gg/tbskliniek',
            'channel' => 'global',
        ]);
        $responseDiscord->assertStatus(200);

        // 3. Rapid fire cooldown (429)
        $responseCooldown = $this->actingAs($user)->postJson(route('chat.public.send'), [
            'message' => 'Nieuw bericht direct erachteraan',
            'channel' => 'global',
        ]);
        $responseCooldown->assertStatus(429);
        $this->assertStringContainsString('Rustig aan', $responseCooldown->json('message'));

        // 4. Duplicate message spam protection (422) after cooldown reset
        Cache::forget("chat_cooldown_{$character->id}_public");
        $responseDup = $this->actingAs($user)->postJson(route('chat.public.send'), [
            'message' => 'Kom naar onze server: discord.gg/tbskliniek',
            'channel' => 'global',
        ]);
        $responseDup->assertStatus(422);
        $this->assertStringContainsString('identieke bericht', $responseDup->json('message'));
    }

    public function test_player_can_send_direct_message(): void
    {
        [$user1, $char1] = $this->createInmate(['name' => 'Verzender']);
        [$user2, $char2] = $this->createInmate(['name' => 'Ontvanger']);

        $response = $this->actingAs($user1)->postJson(route('chat.direct.send', $char2->id), [
            'message' => 'Hé man, heb je nog een peuk?',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => [
                'message' => 'Hé man, heb je nog een peuk?',
                'is_me' => true,
                'recipient_id' => $char2->id,
            ],
        ]);

        $this->assertDatabaseHas('direct_messages', [
            'sender_id' => $char1->id,
            'recipient_id' => $char2->id,
            'message' => 'Hé man, heb je nog een peuk?',
        ]);
    }

    public function test_direct_message_blocks_comatose_imprisoned_and_system_bot_replies(): void
    {
        [$user1, $char1] = $this->createInmate(['name' => 'Speler']);
        [$user2, $char2] = $this->createInmate(['name' => 'Doelwit']);

        // 1. Replying to Kliniek Directie (partnerId = 0) blocked
        $responseBot = $this->actingAs($user1)->postJson(route('chat.direct.send', 0), [
            'message' => 'Antwoord aan directie',
        ]);
        $responseBot->assertStatus(422);
        $this->assertStringContainsString('Kliniek Directie', $responseBot->json('message'));

        // 2. Sending to self blocked
        $responseSelf = $this->actingAs($user1)->postJson(route('chat.direct.send', $char1->id), [
            'message' => 'Hallo tegen mezelf',
        ]);
        $responseSelf->assertStatus(422);

        // 3. In prison blocked
        $char1->update(['is_in_prison' => true, 'prison_until' => now()->addMinutes(10)]);
        $responsePrison = $this->actingAs($user1)->postJson(route('chat.direct.send', $char2->id), [
            'message' => 'DM vanuit isoleercel',
        ]);
        $responsePrison->assertStatus(422);
        $this->assertStringContainsString('isoleercel', $responsePrison->json('message'));

        // 4. Comatose blocked
        $char1->update(['is_in_prison' => false, 'prison_until' => null, 'health' => 0, 'coma_until' => now()->addMinutes(20)]);
        $responseComa = $this->actingAs($user1)->postJson(route('chat.direct.send', $char2->id), [
            'message' => 'DM in coma',
        ]);
        $responseComa->assertStatus(422);
        $this->assertStringContainsString('Code Blauw', $responseComa->json('message'));
    }

    public function test_toggle_public_heart_reaction_and_condition_guards(): void
    {
        [$authorUser, $authorChar] = $this->createInmate(['name' => 'Auteur']);
        [$reactorUser, $reactorChar] = $this->createInmate(['name' => 'Reageerder']);

        $msg = ChatMessage::create([
            'character_id' => $authorChar->id,
            'message' => 'Een belangrijk bericht op het plein!',
            'channel' => 'global',
            'hearts_count' => 0,
            'hearts_users' => [],
        ]);

        // 1. Give heart
        $resHeart = $this->actingAs($reactorUser)->postJson(route('chat.public.heart', $msg->id));
        $resHeart->assertStatus(200);
        $resHeart->assertJson([
            'success' => true,
            'hearts_count' => 1,
            'has_hearted' => true,
        ]);
        $this->assertEquals([$reactorChar->id], $msg->fresh()->hearts_users);

        // 2. Remove heart (toggle)
        $resUntoggle = $this->actingAs($reactorUser)->postJson(route('chat.public.heart', $msg->id));
        $resUntoggle->assertStatus(200);
        $resUntoggle->assertJson([
            'success' => true,
            'hearts_count' => 0,
            'has_hearted' => false,
        ]);
        $this->assertEquals([], $msg->fresh()->hearts_users);

        // 3. Comatose guard
        $reactorChar->update(['health' => 0, 'coma_until' => now()->addMinutes(25)]);
        $resComa = $this->actingAs($reactorUser)->postJson(route('chat.public.heart', $msg->id));
        $resComa->assertStatus(422);
        $this->assertStringContainsString('Code Blauw', $resComa->json('message'));

        // 4. Prison guard
        $reactorChar->update(['health' => 100, 'coma_until' => null, 'is_in_prison' => true, 'prison_until' => now()->addMinutes(10)]);
        $resPrison = $this->actingAs($reactorUser)->postJson(route('chat.public.heart', $msg->id));
        $resPrison->assertStatus(422);
        $this->assertStringContainsString('isoleercel', $resPrison->json('message'));
    }

    public function test_toggle_direct_heart_reaction_and_access_control(): void
    {
        [$user1, $char1] = $this->createInmate(['name' => 'SpelerEen']);
        [$user2, $char2] = $this->createInmate(['name' => 'SpelerTwee']);
        [$outsiderUser, $outsiderChar] = $this->createInmate(['name' => 'Buitenstaander']);

        $dm = DirectMessage::create([
            'sender_id' => $char1->id,
            'recipient_id' => $char2->id,
            'message' => 'Geheim bericht tussen ons',
            'type' => 'chat',
            'hearts_count' => 0,
            'hearts_users' => [],
        ]);

        // 1. Outsider cannot react to a private DM between other players
        $resOutsider = $this->actingAs($outsiderUser)->postJson(route('chat.direct.heart', $dm->id));
        $resOutsider->assertStatus(403);

        // 2. Recipient can react
        $resHeart = $this->actingAs($user2)->postJson(route('chat.direct.heart', $dm->id));
        $resHeart->assertStatus(200);
        $resHeart->assertJson([
            'success' => true,
            'hearts_count' => 1,
            'has_hearted' => true,
        ]);

        // 3. Comatose guard on DM heart
        $char2->update(['health' => 0, 'coma_until' => now()->addMinutes(20)]);
        $resComa = $this->actingAs($user2)->postJson(route('chat.direct.heart', $dm->id));
        $resComa->assertStatus(422);

        // 4. Prison guard on DM heart
        $char2->update(['health' => 100, 'coma_until' => null, 'is_in_prison' => true, 'prison_until' => now()->addMinutes(10)]);
        $resPrison = $this->actingAs($user2)->postJson(route('chat.direct.heart', $dm->id));
        $resPrison->assertStatus(422);
    }

    public function test_message_deletion_permissions(): void
    {
        [$authorUser, $authorChar] = $this->createInmate(['name' => 'Auteur']);
        [$randomUser, $randomChar] = $this->createInmate(['name' => 'AndereSpeler']);
        [$modUser, $modChar] = $this->createInmate(['name' => 'ModSpeler'], isStaff: true, role: 'moderator');

        $msg = ChatMessage::create([
            'character_id' => $authorChar->id,
            'message' => 'Verwijder mij',
            'channel' => 'global',
        ]);

        // 1. Random player cannot delete author's message
        $resDenied = $this->actingAs($randomUser)->deleteJson(route('chat.public.delete', $msg->id));
        $resDenied->assertStatus(403);
        $this->assertDatabaseHas('chat_messages', ['id' => $msg->id]);

        // 2. Author can delete own message
        $resAuthor = $this->actingAs($authorUser)->deleteJson(route('chat.public.delete', $msg->id));
        $resAuthor->assertStatus(200);
        $this->assertDatabaseMissing('chat_messages', ['id' => $msg->id]);

        // 3. Moderator can delete any message
        $msg2 = ChatMessage::create([
            'character_id' => $authorChar->id,
            'message' => 'Spam bericht door auteur',
            'channel' => 'global',
        ]);
        $resMod = $this->actingAs($modUser)->deleteJson(route('chat.public.delete', $msg2->id));
        $resMod->assertStatus(200);
        $this->assertDatabaseMissing('chat_messages', ['id' => $msg2->id]);
    }

    public function test_search_players_with_query(): void
    {
        [$authUser, $authChar] = $this->createInmate(['name' => 'HoofdSpeler']);
        [$matchUser, $matchChar] = $this->createInmate(['name' => 'TargetSpeler']);
        [$otherUser, $otherChar] = $this->createInmate(['name' => 'VolledigAnderen']);

        // 1. Search with substring
        $response = $this->actingAs($authUser)->getJson('/chat/zoek-spelers?q=Target');
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $players = $response->json('players');
        $this->assertCount(1, $players);
        $this->assertEquals('TargetSpeler', $players[0]['name']);

        // 2. Excludes authenticated character from results
        $resSelf = $this->actingAs($authUser)->getJson('/chat/zoek-spelers?q=HoofdSpeler');
        $resSelf->assertStatus(200);
        $this->assertCount(0, $resSelf->json('players'));
    }

    public function test_faq_page_and_knowledge_base(): void
    {
        [$user, $character] = $this->createInmate();

        $response = $this->actingAs($user)->get(route('faq.index'));
        $response->assertStatus(200);
        $response->assertSee('Kennisbank');

        // Verify FaqController static methods
        $faqData = FaqController::getFaqData();
        $this->assertIsArray($faqData);
        $this->assertArrayHasKey('beginners', $faqData);
        $this->assertNotEmpty($faqData['beginners']['questions']);

        $botKnowledge = FaqController::getBotKnowledge();
        $this->assertIsArray($botKnowledge);
        $this->assertGreaterThanOrEqual(25, count($botKnowledge));
        foreach ($botKnowledge as $article) {
            $this->assertArrayHasKey('title', $article);
            $this->assertArrayHasKey('keywords', $article);
            $this->assertArrayHasKey('answer', $article);
            $this->assertArrayHasKey('links', $article);
            $this->assertArrayHasKey('suggestions', $article);
        }
    }

    public function test_chat_views_and_modals_render_cleanly(): void
    {
        [$user, $character] = $this->createInmate();

        $this->actingAs($user);

        // 1. Test main chat index page via controller
        $chatResponse = $this->get(route('chat.index'));
        $chatResponse->assertStatus(200);
        $chatContent = $chatResponse->getContent();

        $this->assertStringContainsString('tbsLiveMessenger', $chatContent);
        $this->assertStringContainsString('heartsPopup', $chatContent);
        $this->assertStringNotContainsString('pointer-events-none fixed z-[999999]', $chatContent);

        // 2. Test mini chat and report modal partial
        $miniChatView = view('partials.mini_chat_and_report_modal', [
            'character' => $character,
        ])->render();

        $this->assertStringContainsString('tbsMiniLiveChat', $miniChatView);
        $this->assertStringContainsString('isHelpBotThinking', $miniChatView);
        $this->assertStringNotContainsString('pointer-events-none fixed z-[9999999]', $miniChatView);
        $this->assertStringContainsString('confirm(\'Weet je zeker dat je dit bericht wilt verwijderen?\')', $miniChatView);
    }
}
