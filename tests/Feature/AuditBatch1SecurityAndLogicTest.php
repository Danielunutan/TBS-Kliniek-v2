<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\CinemaRoom;
use App\Models\NewsArticle;
use App\Models\SupportTicket;
use App\Models\User;
use App\Models\UserWarning;
use App\Services\CooldownService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditBatch1SecurityAndLogicTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    protected function createCharacter(User $user, array $attrs = []): Character
    {
        return Character::create(array_merge([
            'user_id' => $user->id,
            'name' => 'Speler_'.rand(1000, 9999),
            'level' => 5,
            'xp' => 100,
            'health' => 100,
            'max_health' => 100,
            'energy' => 100,
            'max_energy' => 100,
            'cash' => 25000,
            'bank' => 50000,
            'stars' => 10,
            'respect' => 50,
            'strength' => 30,
            'speed' => 20,
            'stamina' => 20,
            'is_in_prison' => false,
        ], $attrs));
    }

    /**
     * Test that Bank operations (deposit, withdraw, request loan) enforce PIN verification.
     */
    public function test_bank_enforces_pin_verification_on_sensitive_transactions(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $character = $this->createCharacter($user, [
            'cash' => 10000,
            'bank' => 20000,
        ]);
        $character->setBankPin('1234');

        $this->actingAs($user);

        // 1. Direct deposit attempt without unlocking PIN must be rejected
        $depositResponse = $this->post(route('bank.deposit'), ['amount' => 5000]);
        $depositResponse->assertRedirect(route('bank.index', ['tab' => 'vault']));
        $depositResponse->assertSessionHas('error');
        $this->assertStringContainsString('pincode', session('error'));

        // 2. Direct withdraw attempt without unlocking PIN must be rejected
        $withdrawResponse = $this->post(route('bank.withdraw'), ['amount' => 5000]);
        $withdrawResponse->assertRedirect(route('bank.index', ['tab' => 'vault']));
        $withdrawResponse->assertSessionHas('error');
        $this->assertStringContainsString('pincode', session('error'));

        // 3. Direct loan request attempt without unlocking PIN must be rejected
        $loanResponse = $this->post(route('bank.loans.request'), ['amount' => 10000, 'deposit_to' => 'cash']);
        $loanResponse->assertRedirect(route('bank.index', ['tab' => 'vault']));
        $loanResponse->assertSessionHas('error');
        $this->assertStringContainsString('pincode', session('error'));

        // 4. Once unlocked in session, deposit succeeds
        $this->withSession(['atm_unlocked_'.$character->id => true]);
        $validDepositResponse = $this->post(route('bank.deposit'), ['amount' => 5000]);
        $validDepositResponse->assertRedirect(route('bank.index', ['tab' => 'vault']));
        $validDepositResponse->assertSessionHas('success');
    }

    /**
     * Test that AdminTicketController gang transfer approval requires admin role.
     */
    public function test_admin_ticket_gang_transfer_approval_requires_admin_privileges(): void
    {
        /** @var User $targetUser */
        $targetUser = User::factory()->create();
        $targetCharacter = $this->createCharacter($targetUser, ['name' => 'GangLeaderCandidate']);

        $ticket = SupportTicket::create([
            'ticket_number' => 'TICK-12345',
            'user_id' => $targetUser->id,
            'character_id' => $targetCharacter->id,
            'category' => 'gang_overstap',
            'subject' => 'Aanvraag Gang Overstap',
            'status' => 'open',
            'priority' => 'high',
            'description' => 'Overstap vanaf Barribob',
        ]);

        /** @var User $moderator */
        $moderator = User::factory()->create([
            'role' => 'moderator',
            'is_staff' => true,
        ]);
        $this->createCharacter($moderator);

        // Moderator attempt must be rejected
        $this->actingAs($moderator);
        $modResponse = $this->post(route('admin.tickets.approve_gang', $ticket), [
            'gang_name' => 'Syndicaat 1',
            'gang_tag' => 'SYN1',
            'bonus_cash' => 5000000,
            'vip_days' => 31,
        ]);
        $modResponse->assertSessionHas('error');
        $this->assertStringContainsString('Alleen beheerders', session('error'));

        /** @var User $admin */
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_staff' => true,
        ]);
        $this->createCharacter($admin);

        // Admin attempt succeeds
        $this->actingAs($admin);
        $adminResponse = $this->post(route('admin.tickets.approve_gang', $ticket), [
            'gang_name' => 'Syndicaat 1',
            'gang_tag' => 'SYN1',
            'bonus_cash' => 5000000,
            'vip_days' => 31,
        ]);
        $adminResponse->assertSessionHas('success');
        $this->assertDatabaseHas('gangs', ['name' => 'Syndicaat 1', 'tag' => 'SYN1']);
    }

    /**
     * Test that AdminController player updates prevent moderator privilege escalation and currency tampering.
     */
    public function test_admin_controller_restricts_role_and_currency_modifications_to_admin(): void
    {
        /** @var User $targetUser */
        $targetUser = User::factory()->create(['role' => 'user']);
        $targetCharacter = $this->createCharacter($targetUser, [
            'cash' => 1000,
            'bank' => 2000,
            'stars' => 5,
            'respect' => 10,
            'vip_points' => 0,
        ]);

        /** @var User $moderator */
        $moderator = User::factory()->create([
            'role' => 'moderator',
            'is_staff' => true,
        ]);
        $this->createCharacter($moderator);

        $this->actingAs($moderator);

        // Moderator attempting to change role to moderator must be blocked
        $roleResponse = $this->post(route('admin.player.update', $targetCharacter), [
            'role' => 'moderator',
            'cash' => 1000,
            'bank' => 2000,
            'stars' => 5,
            'respect' => 10,
            'vip_points' => 0,
            'health' => 100,
            'energy' => 100,
        ]);
        $roleResponse->assertSessionHas('error');
        $this->assertStringContainsString('Alleen administrators kunnen gebruikersrollen', session('error'));

        // Moderator attempting to increase cash must be blocked
        $cashResponse = $this->post(route('admin.player.update', $targetCharacter), [
            'role' => 'user',
            'cash' => 9999999,
            'bank' => 2000,
            'stars' => 5,
            'respect' => 10,
            'vip_points' => 0,
            'health' => 100,
            'energy' => 100,
        ]);
        $cashResponse->assertSessionHas('error');
        $this->assertStringContainsString('Alleen administrators kunnen economische tegoeden', session('error'));

        /** @var User $admin */
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_staff' => true,
        ]);
        $this->createCharacter($admin);

        // Admin can update player
        $this->actingAs($admin);
        $adminResponse = $this->post(route('admin.player.update', $targetCharacter), [
            'role' => 'user',
            'cash' => 50000,
            'bank' => 2000,
            'stars' => 5,
            'respect' => 10,
            'vip_points' => 0,
            'health' => 100,
            'energy' => 100,
        ]);
        $adminResponse->assertSessionHas('success');
        $targetCharacter->refresh();
        $this->assertEquals(50000, $targetCharacter->cash);
    }

    /**
     * Test that AdminModerationController warning deletion is restricted to issuer or admin.
     */
    public function test_admin_moderation_warning_deletion_permissions(): void
    {
        /** @var User $issuerAdmin */
        $issuerAdmin = User::factory()->create(['role' => 'admin', 'is_staff' => true]);
        /** @var User $issuerMod */
        $issuerMod = User::factory()->create(['role' => 'moderator', 'is_staff' => true]);
        /** @var User $otherMod */
        $otherMod = User::factory()->create(['role' => 'moderator', 'is_staff' => true]);
        /** @var User $targetUser */
        $targetUser = User::factory()->create(['role' => 'user']);

        $warningByAdmin = UserWarning::create([
            'user_id' => $targetUser->id,
            'issuer_id' => $issuerAdmin->id,
            'reason' => 'Admin warning',
        ]);

        $warningByMod = UserWarning::create([
            'user_id' => $targetUser->id,
            'issuer_id' => $issuerMod->id,
            'reason' => 'Mod warning',
        ]);

        // Another moderator cannot delete warning issued by someone else
        $this->actingAs($otherMod);
        $otherModResponse = $this->delete(route('admin.moderation.warning.destroy', $warningByMod));
        $otherModResponse->assertSessionHas('error');
        $this->assertStringContainsString('alleen waarschuwingen intrekken die je zelf hebt uitgedeeld', session('error'));
        $this->assertDatabaseHas('user_warnings', ['id' => $warningByMod->id]);

        // Issuer moderator can delete their own warning
        $this->actingAs($issuerMod);
        $modResponse = $this->delete(route('admin.moderation.warning.destroy', $warningByMod));
        $modResponse->assertSessionHas('success');
        $this->assertDatabaseMissing('user_warnings', ['id' => $warningByMod->id]);

        // Admin can delete any warning
        $this->actingAs($issuerAdmin);
        $adminResponse = $this->delete(route('admin.moderation.warning.destroy', $warningByAdmin));
        $adminResponse->assertSessionHas('success');
        $this->assertDatabaseMissing('user_warnings', ['id' => $warningByAdmin->id]);
    }

    /**
     * Test that news article deletion requires administrator role.
     */
    public function test_admin_news_destruction_requires_administrator(): void
    {
        /** @var User $moderator */
        $moderator = User::factory()->create(['role' => 'moderator', 'is_staff' => true]);
        $article = NewsArticle::create([
            'title' => 'Belangrijke Update',
            'slug' => 'belangrijke-update',
            'content' => 'Update inhoud',
            'emoji' => '📰',
            'is_published' => true,
        ]);

        // Moderator attempt must fail
        $this->actingAs($moderator);
        $modResponse = $this->delete(route('admin.news.destroy', $article));
        $modResponse->assertSessionHas('error');
        $this->assertStringContainsString('Alleen administrators', session('error'));
        $this->assertDatabaseHas('news_articles', ['id' => $article->id]);

        /** @var User $admin */
        $admin = User::factory()->create(['role' => 'admin', 'is_staff' => true]);
        $this->actingAs($admin);
        $adminResponse = $this->delete(route('admin.news.destroy', $article));
        $adminResponse->assertSessionHas('success');
        $this->assertDatabaseMissing('news_articles', ['id' => $article->id]);
    }

    /**
     * Test that cinema official room broadcast is restricted to administrator.
     */
    public function test_cinema_official_room_restricted_to_admin(): void
    {
        /** @var User $moderator */
        $moderator = User::factory()->create(['role' => 'moderator', 'is_staff' => true]);
        $this->createCharacter($moderator);

        $this->actingAs($moderator);
        $this->post(route('cinema.create'), [
            'title' => 'Moderator Zaal',
            'youtube_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'is_official' => 1,
        ]);

        $modRoom = CinemaRoom::where('title', 'Moderator Zaal')->first();
        $this->assertNotNull($modRoom);
        $this->assertFalse((bool) $modRoom->is_official);

        /** @var User $admin */
        $admin = User::factory()->create(['role' => 'admin', 'is_staff' => true]);
        $this->createCharacter($admin);

        $this->actingAs($admin);
        $this->post(route('cinema.create'), [
            'title' => 'Directie Zaal',
            'youtube_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'is_official' => 1,
        ]);

        $adminRoom = CinemaRoom::where('title', 'Directie Zaal')->first();
        $this->assertNotNull($adminRoom);
        $this->assertTrue((bool) $adminRoom->is_official);
    }

    /**
     * Test that ArmWrestle NPC validation rejects unrealistic bot claims.
     */
    public function test_arm_wrestle_anti_cheat_on_npc_finish(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $character = $this->createCharacter($user, [
            'cash' => 200000,
            'energy' => 100,
            'strength' => 150,
        ]);

        $this->actingAs($user);

        // Spoofed Mike win claim with only 500ms and 3 clicks must not be honored as a win
        $cheatResponse = $this->postJson(route('armwrestling.npc.finish'), [
            'npc_key' => 'mike',
            'won' => true,
            'duration_ms' => 500,
            'clicks' => 3,
        ]);

        $cheatResponse->assertStatus(200);
        $this->assertFalse($cheatResponse->json('won'));

        // Legitimate duel with sufficient duration and clicks succeeds
        app(CooldownService::class)->clearCooldown($character, 'arm_wrestling');
        $character->cash = 200000;
        $character->energy = 100;
        $character->save();

        $legitResponse = $this->postJson(route('armwrestling.npc.finish'), [
            'npc_key' => 'mike',
            'won' => true,
            'duration_ms' => 3000,
            'clicks' => 20,
        ]);

        $legitResponse->assertStatus(200);
        $this->assertTrue($legitResponse->json('won'));
    }

    /**
     * Test that DiscordShieldController game linking requires valid OAuth session and prevents duplicate linking.
     */
    public function test_discord_shield_account_linking_requires_oauth_and_prevents_duplicate(): void
    {
        /** @var User $user1 */
        $user1 = User::factory()->create(['discord_id' => '123456789012345678']);
        $this->createCharacter($user1);

        /** @var User $user2 */
        $user2 = User::factory()->create(['discord_id' => null]);
        $this->createCharacter($user2);

        $this->actingAs($user2);

        // Attempting to link without Discord OAuth session redirects to discord.redirect (no IP sniffing)
        $noOAuthResponse = $this->get(route('discord.link'));
        $noOAuthResponse->assertRedirect(route('discord.redirect'));

        // Attempting to link when Discord ID is already bound to user1
        $this->withSession(['verified_discord_id' => '123456789012345678']);
        $duplicateResponse = $this->get(route('discord.link'));
        $duplicateResponse->assertRedirect(route('discord.verify'));
        $duplicateResponse->assertSessionHas('error');
        $this->assertStringContainsString('al gekoppeld', session('error'));
    }

    /**
     * Test that user registration checks uniqueness across both users and characters.
     */
    public function test_registration_validates_username_uniqueness(): void
    {
        /** @var User $existingUser */
        $existingUser = User::factory()->create(['name' => 'KarelBoef', 'email' => 'karel@tbskliniek.nl']);

        $response = $this->from('/registreren')->post(route('register.post'), [
            'username' => 'KarelBoef',
            'email' => 'newplayer@tbskliniek.nl',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
            'gender' => 'male',
            'terms' => '1',
        ]);

        $response->assertSessionHasErrors(['username']);
    }
}
