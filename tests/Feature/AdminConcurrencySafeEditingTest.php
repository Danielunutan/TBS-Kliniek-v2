<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\NewsArticle;
use App\Models\Report;
use App\Models\User;
use App\Models\UserWarning;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminConcurrencySafeEditingTest extends TestCase
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
            'vip_points' => 0,
            'strength' => 30,
            'speed' => 20,
            'stamina' => 20,
            'is_in_prison' => false,
        ], $attrs));
    }

    /**
     * Test that concurrent cash and bank updates in game are not overwritten when admin only edits stars.
     */
    public function test_admin_player_update_preserves_concurrent_cash_and_bank_changes(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_staff' => true]);
        $targetUser = User::factory()->create();
        $targetChar = $this->createCharacter($targetUser, [
            'cash' => 25000,
            'bank' => 50000,
            'stars' => 10,
            'energy' => 80,
        ]);

        // Form loaded with initial snapshot
        $originalPayload = [
            'original_updated_at' => $targetChar->updated_at->timestamp,
            'original_cash' => 25000,
            'original_bank' => 50000,
            'original_stars' => 10,
            'original_respect' => 50,
            'original_vip_points' => 0,
            'original_health' => 100,
            'original_energy' => 80,
            'original_role' => 'user',
            'original_username' => $targetUser->name,
            'original_character_name' => $targetChar->name,
            'original_is_vip' => 0,
        ];

        // While admin was editing stars, player earned cash and deposited in bank, and energy restored
        $targetChar->cash = 35000;
        $targetChar->bank = 75000;
        $targetChar->energy = 95;
        $targetChar->save();

        // Admin submits form with stars updated from 10 to 30, leaving cash/bank/energy as originally loaded
        $response = $this->actingAs($admin)->post(route('admin.player.update', $targetChar), array_merge($originalPayload, [
            'username' => $targetUser->name,
            'character_name' => $targetChar->name,
            'cash' => 25000,
            'bank' => 50000,
            'stars' => 30, // Changed by admin
            'respect' => 50,
            'vip_points' => 0,
            'health' => 100,
            'energy' => 80,
            'role' => 'user',
            'vip_action' => 'no_change',
        ]));

        $response->assertSessionHas('success');
        $response->assertSessionHas('warning');

        $targetChar->refresh();
        // Stars were updated
        $this->assertEquals(30, $targetChar->stars);
        // Cash, bank, energy were preserved from DB and NOT overwritten by stale form values
        $this->assertEquals(35000, $targetChar->cash);
        $this->assertEquals(75000, $targetChar->bank);
        $this->assertEquals(95, $targetChar->energy);
    }

    /**
     * Test direct conflict protection and force_overwrite flag.
     */
    public function test_admin_player_update_conflict_and_force_overwrite(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_staff' => true]);
        $targetUser = User::factory()->create();
        $targetChar = $this->createCharacter($targetUser, ['cash' => 10000]);

        // Form loaded with cash = 10000
        $originalPayload = [
            'original_updated_at' => $targetChar->updated_at->timestamp,
            'original_cash' => 10000,
            'original_bank' => $targetChar->bank,
            'original_stars' => $targetChar->stars,
            'original_respect' => $targetChar->respect,
            'original_vip_points' => $targetChar->vip_points ?? 0,
            'original_health' => $targetChar->health,
            'original_energy' => $targetChar->energy,
            'original_role' => 'user',
            'original_username' => $targetUser->name,
            'original_character_name' => $targetChar->name,
            'original_is_vip' => 0,
        ];

        // Player earned cash concurrently in DB
        $targetChar->cash = 20000;
        $targetChar->save();

        // Admin changed cash to 15000 without force overwrite
        $response = $this->actingAs($admin)->post(route('admin.player.update', $targetChar), array_merge($originalPayload, [
            'cash' => 15000,
            'bank' => $targetChar->bank,
            'stars' => $targetChar->stars,
            'respect' => $targetChar->respect,
            'vip_points' => 0,
            'health' => 100,
            'energy' => 100,
            'role' => 'user',
        ]));

        $response->assertSessionHas('warning');
        $targetChar->refresh();
        // Safe edit preserved concurrent 20000 value
        $this->assertEquals(20000, $targetChar->cash);

        // Now admin submits with force_overwrite = true
        $forceResponse = $this->actingAs($admin)->post(route('admin.player.update', $targetChar), array_merge($originalPayload, [
            'cash' => 15000,
            'bank' => $targetChar->bank,
            'stars' => $targetChar->stars,
            'respect' => $targetChar->respect,
            'vip_points' => 0,
            'health' => 100,
            'energy' => 100,
            'role' => 'user',
            'force_overwrite' => 1,
        ]));

        $forceResponse->assertSessionHas('success');
        $targetChar->refresh();
        $this->assertEquals(15000, $targetChar->cash);
    }

    /**
     * Test that moderators are not falsely blocked when cash changes in the DB concurrently.
     */
    public function test_moderator_can_update_name_even_if_cash_changed_concurrently(): void
    {
        $moderator = User::factory()->create(['role' => 'moderator', 'is_staff' => true]);
        $targetUser = User::factory()->create(['name' => 'OriginalUser']);
        $targetChar = $this->createCharacter($targetUser, ['name' => 'OriginalChar', 'cash' => 5000]);

        $originalPayload = [
            'original_updated_at' => $targetChar->updated_at->timestamp,
            'original_cash' => 5000,
            'original_bank' => $targetChar->bank,
            'original_stars' => $targetChar->stars,
            'original_respect' => $targetChar->respect,
            'original_vip_points' => 0,
            'original_health' => 100,
            'original_energy' => 100,
            'original_role' => 'user',
            'original_username' => 'OriginalUser',
            'original_character_name' => 'OriginalChar',
            'original_is_vip' => 0,
        ];

        // Background cash update
        $targetChar->cash = 8000;
        $targetChar->save();

        // Moderator changes display name, keeps cash at 5000 (as loaded)
        $response = $this->actingAs($moderator)->post(route('admin.player.update', $targetChar), array_merge($originalPayload, [
            'username' => 'OriginalUser',
            'character_name' => 'CleanedCharName',
            'cash' => 5000,
            'bank' => $targetChar->bank,
            'stars' => $targetChar->stars,
            'respect' => $targetChar->respect,
            'vip_points' => 0,
            'health' => 100,
            'energy' => 100,
            'role' => 'user',
        ]));

        $response->assertSessionHas('success');
        $targetChar->refresh();
        $this->assertEquals('CleanedCharName', $targetChar->name);
        $this->assertEquals(8000, $targetChar->cash);
    }

    /**
     * Test news update warns when article was modified concurrently.
     */
    public function test_news_update_warns_on_concurrent_modification(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_staff' => true]);
        $article = NewsArticle::create([
            'title' => 'Eerste Nieuwsbericht',
            'slug' => 'eerste-nieuwsbericht',
            'content' => 'Inhoud van het eerste nieuwsbericht hier.',
            'emoji' => '📰',
            'is_published' => true,
        ]);

        $loadedTimestamp = $article->updated_at->timestamp;

        // Article updated in background
        sleep(1);
        $article->touch();
        $article->save();

        $response = $this->actingAs($admin)->put(route('admin.news.update', $article), [
            'title' => 'Gewijzigd Nieuwsbericht',
            'emoji' => '🔥',
            'content' => 'Nieuwe bijgewerkte inhoud van het artikel.',
            'is_published' => 1,
            'original_updated_at' => $loadedTimestamp,
        ]);

        $response->assertSessionHas('success');
        $response->assertSessionHas('warning');

        $article->refresh();
        $this->assertEquals('Gewijzigd Nieuwsbericht', $article->title);
    }

    /**
     * Test updateChests validates min/max boundaries.
     */
    public function test_update_chests_validates_min_max_boundaries(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_staff' => true]);

        // Max less than min should fail
        $response = $this->actingAs($admin)->post(route('admin.chests.update'), [
            'case_xxl_price_cash' => 50000,
            'case_xxl_price_stars' => 100,
            'case_xxl_min_cash' => 50000,
            'case_xxl_max_cash' => 20000, // Invalid: less than min
            'case_xxl_stars_reward' => 25,
            'bag_sell_reward' => 2500,
            'bag_open_min_cash' => 1000,
            'bag_open_max_cash' => 500, // Invalid: less than min
        ]);

        $response->assertSessionHasErrors(['case_xxl_max_cash', 'bag_open_max_cash']);
    }

    /**
     * Test that user warnings are permanently deleted by deleteWarning.
     */
    public function test_user_warning_is_properly_deleted(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_staff' => true]);
        $targetUser = User::factory()->create();

        $warning = UserWarning::create([
            'user_id' => $targetUser->id,
            'issuer_id' => $admin->id,
            'reason' => 'Test warning to delete',
        ]);

        $response = $this->actingAs($admin)->delete(route('admin.moderation.warning.destroy', $warning));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('user_warnings', ['id' => $warning->id]);
    }

    /**
     * Test that concurrent VIP activation is not revoked when an admin edits stars with a stale non-VIP form snapshot.
     */
    public function test_concurrent_vip_purchase_is_not_revoked_when_admin_edits_stars(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_staff' => true]);
        $targetUser = User::factory()->create();
        $targetChar = $this->createCharacter($targetUser, [
            'cash' => 10000,
            'stars' => 5,
            'is_vip' => false,
            'vip_expires_at' => null,
        ]);

        // Form loaded while player was NOT VIP
        $originalPayload = [
            'original_updated_at' => $targetChar->updated_at->timestamp,
            'original_cash' => 10000,
            'original_bank' => $targetChar->bank,
            'original_stars' => 5,
            'original_respect' => $targetChar->respect,
            'original_vip_points' => 0,
            'original_health' => 100,
            'original_energy' => 100,
            'original_role' => 'user',
            'original_username' => $targetUser->name,
            'original_character_name' => $targetChar->name,
            'original_is_vip' => 0,
        ];

        // Player buys or redeems VIP concurrently while admin has drawer open
        $targetChar->is_vip = true;
        $targetChar->vip_expires_at = now()->addDays(30);
        $targetChar->perks = ['supporter_tier' => 2, 'hourly_cash_bonus' => 7500];
        $targetChar->save();

        // Admin submits form with stars = 25, without checking is_vip (since it was unchecked when loaded)
        $response = $this->actingAs($admin)->post(route('admin.player.update', $targetChar), array_merge($originalPayload, [
            'username' => $targetUser->name,
            'character_name' => $targetChar->name,
            'cash' => 10000,
            'bank' => $targetChar->bank,
            'stars' => 25,
            'respect' => $targetChar->respect,
            'vip_points' => 0,
            'health' => 100,
            'energy' => 100,
            'role' => 'user',
            'vip_action' => 'no_change',
        ]));

        $response->assertSessionHas('success');
        $response->assertSessionHas('warning');
        $response->assertSessionHas('editing_player_id', $targetChar->id);

        $targetChar->refresh();
        $this->assertEquals(25, $targetChar->stars);
        $this->assertTrue($targetChar->isVip());
        $this->assertEquals(2, $targetChar->getSupporterTier());
    }

    /**
     * Test that moderators cannot edit other staff members.
     */
    public function test_moderator_cannot_edit_another_staff_member(): void
    {
        $moderator = User::factory()->create(['role' => 'moderator', 'is_staff' => true]);
        $targetStaff = User::factory()->create(['role' => 'moderator', 'is_staff' => true]);
        $targetChar = $this->createCharacter($targetStaff, ['name' => 'StaffChar']);

        $response = $this->actingAs($moderator)->post(route('admin.player.update', $targetChar), [
            'username' => 'NewStaffName',
            'character_name' => 'StaffChar',
            'cash' => $targetChar->cash,
            'bank' => $targetChar->bank,
            'stars' => $targetChar->stars,
            'respect' => $targetChar->respect,
            'vip_points' => 0,
            'health' => 100,
            'energy' => 100,
            'role' => 'moderator',
        ]);

        $response->assertSessionHas('error', 'Alleen administrators kunnen het profiel of de status van medewerkers wijzigen.');
    }

    /**
     * Test that moderators cannot release players from prison.
     */
    public function test_moderator_cannot_release_player_from_prison(): void
    {
        $moderator = User::factory()->create(['role' => 'moderator', 'is_staff' => true]);
        $targetUser = User::factory()->create(['role' => 'user', 'is_staff' => false]);
        $targetChar = $this->createCharacter($targetUser, ['is_in_prison' => true]);

        $response = $this->actingAs($moderator)->post(route('admin.player.update', $targetChar), [
            'username' => $targetUser->name,
            'character_name' => $targetChar->name,
            'cash' => $targetChar->cash,
            'bank' => $targetChar->bank,
            'stars' => $targetChar->stars,
            'respect' => $targetChar->respect,
            'vip_points' => 0,
            'health' => 100,
            'energy' => 100,
            'role' => 'user',
            'release_prison' => 1,
        ]);

        $response->assertSessionHas('error', 'Alleen administrators kunnen spelers vrijlaten uit de gevangenis.');
        $targetChar->refresh();
        $this->assertTrue($targetChar->is_in_prison);
    }

    /**
     * Test report resolution locks report and sets status.
     */
    public function test_admin_moderation_resolve_report_locks_and_updates_status(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_staff' => true]);
        $reporterUser = User::factory()->create();
        $reporterChar = $this->createCharacter($reporterUser);

        $report = Report::create([
            'reporter_id' => $reporterChar->id,
            'reported_type' => 'character',
            'reported_id' => 999,
            'reported_name' => 'SlechteSpeler',
            'reason' => 'Schelden in shoutbox',
            'details' => 'Speler gebruikte ongepaste taal.',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.moderation.report.resolve', $report), [
            'notes' => 'Speler aangesproken en gewaarschuwd.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $report->refresh();
        $this->assertEquals('resolved', $report->status);
        $this->assertEquals($admin->id, $report->handled_by);
        $this->assertEquals('Speler aangesproken en gewaarschuwd.', $report->mod_notes);
    }
}
