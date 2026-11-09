<?php

namespace Tests\Feature;

use App\Models\Bounty;
use App\Models\Character;
use App\Models\DailyBattleScore;
use App\Models\Friend;
use App\Models\Gang;
use App\Models\User;
use App\Services\AllowanceService;
use App\Services\LevelService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class OwnersNonParticipatingMigrationTest extends TestCase
{
    use RefreshDatabase;

    private const MIGRATION_FILE = 'migrations/2026_11_09_190000_promote_daniel_and_dave_to_non_participating_owners.php';

    /**
     * Verify that the migration promotes staff accounts for Daniel and Dave to exempt owners and cleans gameplay state.
     */
    public function test_migration_promotes_daniel_and_dave_admins_to_exempt_owners_and_cleans_gangs_and_stats(): void
    {
        $danielUser = User::factory()->create([
            'name' => 'Daniel',
            'email' => 'danielunutan@icloud.com',
            'role' => 'admin',
            'is_staff' => true,
            'is_onboarded' => true,
        ]);
        $danielChar = Character::factory()->create([
            'user_id' => $danielUser->id,
            'name' => 'Daniël',
            'title' => 'Luchtplaats Vechter',
            'level' => 28,
            'xp' => 4500,
            'total_xp' => 80000,
            'respect' => 3200,
            'today_respect' => 45,
            'cash' => 500000,
            'bank' => 2500000,
            'fights_won' => 42,
            'fights_lost' => 5,
            'is_in_prison' => true,
            'prison_until' => now()->addMinutes(20),
            'bio' => 'Beheer en ontwikkeling van de kliniek.',
        ]);

        $marcoUser = User::factory()->create(['role' => 'user', 'is_onboarded' => true]);
        $marcoChar = Character::factory()->create([
            'user_id' => $marcoUser->id,
            'name' => 'MarcoVanBlokB',
            'level' => 15,
            'respect' => 900,
            'cash' => 10000,
        ]);

        $sharedGang = Gang::create([
            'name' => 'Directie Kartel',
            'tag' => 'DIRK',
            'specialty' => 'smuggling',
            'emblem' => 'fa-skull',
            'leader_character_id' => $danielChar->id,
            'treasury' => 100000,
        ]);
        $danielChar->update(['gang_id' => $sharedGang->id, 'gang_role' => 'leader']);
        $marcoChar->update(['gang_id' => $sharedGang->id, 'gang_role' => 'co_leader']);

        $daveUser = User::factory()->create([
            'name' => 'Dave',
            'email' => 'dave@tbskliniek.nl',
            'role' => 'admin',
            'is_staff' => true,
            'is_onboarded' => true,
        ]);
        $daveChar = Character::factory()->create([
            'user_id' => $daveUser->id,
            'name' => 'Dave',
            'level' => 19,
            'xp' => 1200,
            'total_xp' => 34000,
            'respect' => 1850,
            'cash' => 120000,
            'bank' => 900000,
            'thefts_succeeded' => 30,
        ]);

        $soloGang = Gang::create([
            'name' => 'Solo Beheer Bende',
            'tag' => 'SOLO',
            'specialty' => 'violence',
            'emblem' => 'fa-hand-fist',
            'leader_character_id' => $daveChar->id,
            'treasury' => 25000,
        ]);
        $daveChar->update(['gang_id' => $soloGang->id, 'gang_role' => 'leader']);

        Bounty::create([
            'target_character_id' => $daveChar->id,
            'placed_by_character_id' => $marcoChar->id,
            'amount' => 50000,
            'reason' => 'Test bounty',
            'is_claimed' => false,
        ]);

        DailyBattleScore::create([
            'battle_date' => now()->toDateString(),
            'character_id' => $daveChar->id,
            'score_slay' => 50,
            'score_kills' => 10,
        ]);

        // Create a non-staff regular player with a similar name to verify the migration leaves regular players untouched.
        $regularDaveUser = User::factory()->create([
            'name' => 'Dave',
            'email' => 'speler.dave@example.nl',
            'role' => 'user',
            'is_staff' => false,
            'is_onboarded' => true,
        ]);
        $regularDaveChar = Character::factory()->create([
            'user_id' => $regularDaveUser->id,
            'name' => 'DaveVanPaviljoen3',
            'level' => 12,
            'respect' => 450,
            'cash' => 15000,
            'bank' => 40000,
        ]);

        $migration = require database_path(self::MIGRATION_FILE);
        $migration->up();

        $danielUser->refresh();
        $danielChar->refresh();
        $daveUser->refresh();
        $daveChar->refresh();
        $marcoChar->refresh();
        $regularDaveUser->refresh();
        $regularDaveChar->refresh();

        $this->assertSame('owner', $danielUser->role);
        $this->assertTrue($danielUser->is_staff);
        $this->assertTrue($danielChar->is_game_exempt);
        $this->assertTrue($danielChar->isGameExempt());
        $this->assertSame('Eigenaar', $danielChar->title);
        $this->assertSame(0, $danielChar->level);
        $this->assertSame(0, $danielChar->xp);
        $this->assertSame(0, $danielChar->total_xp);
        $this->assertEquals(0, $danielChar->respect);
        $this->assertSame(0, $danielChar->cash);
        $this->assertSame(0, $danielChar->bank);
        $this->assertSame(0, $danielChar->fights_won);
        $this->assertFalse($danielChar->is_in_prison);
        $this->assertNull($danielChar->prison_until);
        $this->assertNull($danielChar->gang_id);
        $this->assertSame('Beheer en ontwikkeling van de kliniek.', $danielChar->bio);

        $this->assertSame('owner', $daveUser->role);
        $this->assertTrue($daveUser->is_staff);
        $this->assertTrue($daveChar->is_game_exempt);
        $this->assertTrue($daveChar->isGameExempt());
        $this->assertSame('Eigenaar', $daveChar->title);
        $this->assertSame(0, $daveChar->level);
        $this->assertEquals(0, $daveChar->respect);
        $this->assertSame(0, $daveChar->cash);
        $this->assertSame(0, $daveChar->bank);
        $this->assertNull($daveChar->gang_id);

        // Shared gang keeps remaining members and promotes Marco to leader.
        $sharedGang->refresh();
        $this->assertSame($marcoChar->id, $sharedGang->leader_character_id);
        $this->assertSame('leader', $marcoChar->gang_role);

        // Empty 1-person gang is disbanded cleanly.
        $this->assertDatabaseMissing('gangs', ['id' => $soloGang->id]);

        // Open bounties and daily battle scores for exempt owners are removed, and Marco receives his bounty refund.
        $this->assertDatabaseMissing('bounties', ['target_character_id' => $daveChar->id, 'is_claimed' => false]);
        $this->assertSame(60000, $marcoChar->cash);
        $this->assertDatabaseMissing('daily_battle_scores', ['character_id' => $daveChar->id]);

        // Non-staff player remains untouched.
        $this->assertSame('user', $regularDaveUser->role);
        $this->assertFalse((bool) $regularDaveChar->is_game_exempt);
        $this->assertFalse($regularDaveChar->isGameExempt());
        $this->assertSame(12, $regularDaveChar->level);
        $this->assertEquals(450, $regularDaveChar->respect);
        $this->assertSame(15000, $regularDaveChar->cash);
    }

    /**
     * Verify that owners are absent from rankings and target lists and cannot be attacked, robbed, bountied, or given respect.
     */
    public function test_owners_are_excluded_from_rankings_target_lists_and_cannot_be_attacked_or_robbed(): void
    {
        Cache::flush();

        $danielUser = User::factory()->create(['role' => 'owner', 'is_staff' => true, 'is_onboarded' => true]);
        $daniel = Character::factory()->create([
            'user_id' => $danielUser->id,
            'name' => 'OwnerDanielUnique',
            'title' => 'Eigenaar',
            'level' => 0,
            'respect' => 0,
            'cash' => 0,
            'bank' => 0,
            'current_location' => 'Paviljoen 1 (Gesloten)',
        ]);

        $daveUser = User::factory()->create(['role' => 'owner', 'is_staff' => true, 'is_onboarded' => true]);
        $dave = Character::factory()->create([
            'user_id' => $daveUser->id,
            'name' => 'OwnerDaveUnique',
            'title' => 'Eigenaar',
            'level' => 0,
            'respect' => 0,
            'cash' => 0,
            'bank' => 0,
            'current_location' => 'Paviljoen 1 (Gesloten)',
        ]);

        $playerUser = User::factory()->create(['role' => 'user', 'is_onboarded' => true]);
        $player = Character::factory()->create([
            'user_id' => $playerUser->id,
            'name' => 'SanderDeSloper',
            'level' => 10,
            'respect' => 250,
            'cash' => 250000,
            'bank' => 100000,
            'bullets' => 100,
            'energy' => 100,
            'health' => 100,
            'current_location' => 'Paviljoen 1 (Gesloten)',
        ]);

        $rivalUser = User::factory()->create(['role' => 'user', 'is_onboarded' => true]);
        $rival = Character::factory()->create([
            'user_id' => $rivalUser->id,
            'name' => 'KarelVanDeKantine',
            'level' => 9,
            'respect' => 180,
            'cash' => 10000,
            'current_location' => 'Paviljoen 1 (Gesloten)',
        ]);

        $playableNames = Character::playable()->pluck('name')->all();
        $this->assertContains('SanderDeSloper', $playableNames);
        $this->assertContains('KarelVanDeKantine', $playableNames);
        $this->assertNotContains('OwnerDanielUnique', $playableNames);
        $this->assertNotContains('OwnerDaveUnique', $playableNames);

        $topRankingNames = Character::getTopRankings(10)->pluck('name')->all();
        $this->assertNotContains('OwnerDanielUnique', $topRankingNames);
        $this->assertNotContains('OwnerDaveUnique', $topRankingNames);

        // Leaderboard and target pages exclude owners.
        $this->actingAs($playerUser)->get(route('leaderboard.index', ['type' => 'level']))
            ->assertOk()
            ->assertSee('KarelVanDeKantine')
            ->assertDontSee('OwnerDanielUnique')
            ->assertDontSee('OwnerDaveUnique');

        $this->actingAs($playerUser)->get(route('fight.index'))
            ->assertOk()
            ->assertSee('KarelVanDeKantine')
            ->assertDontSee('OwnerDanielUnique')
            ->assertDontSee('OwnerDaveUnique');

        $this->actingAs($playerUser)->get(route('theft.index'))
            ->assertOk()
            ->assertSee('KarelVanDeKantine')
            ->assertDontSee('OwnerDanielUnique')
            ->assertDontSee('OwnerDaveUnique');

        $this->actingAs($playerUser)->get(route('hitman.index'))
            ->assertOk()
            ->assertDontSee('OwnerDanielUnique')
            ->assertDontSee('OwnerDaveUnique');

        $searchJson = $this->actingAs($playerUser)
            ->getJson(route('hitman.search', ['q' => 'Owner']))
            ->assertOk()
            ->json('targets');
        $this->assertEmpty($searchJson);

        $this->actingAs($playerUser)->get(route('bounties.index'))
            ->assertOk()
            ->assertDontSee('OwnerDanielUnique')
            ->assertDontSee('OwnerDaveUnique');

        // Direct PvP POST attacks against owners fail closed.
        $this->actingAs($playerUser)
            ->from(route('fight.index'))
            ->post(route('fight.attack'), ['opponent_id' => $dave->id])
            ->assertRedirect(route('fight.index'))
            ->assertSessionHas('error');

        $this->actingAs($playerUser)
            ->from(route('theft.index'))
            ->post(route('theft.steal'), ['tier' => 'pocket', 'victim_id' => $dave->id])
            ->assertRedirect(route('theft.index'))
            ->assertSessionHas('error');

        $this->actingAs($playerUser)
            ->from(route('hitman.index'))
            ->post(route('hitman.shoot'), ['target_id' => $daniel->id, 'bullets' => 25])
            ->assertRedirect(route('hitman.index'))
            ->assertSessionHas('error');

        $this->actingAs($playerUser)
            ->from(route('hitman.index'))
            ->post(route('hitman.contract'), ['target_id' => $daniel->id, 'contract_id' => 1])
            ->assertRedirect(route('hitman.index'))
            ->assertSessionHas('error');

        $this->actingAs($playerUser)
            ->from(route('hitman.index'))
            ->post(route('hitman.intel'), ['target_id' => $daniel->id, 'intel_type' => 'standard_15m', 'currency' => 'cash'])
            ->assertRedirect(route('hitman.index'))
            ->assertSessionHas('error');

        $this->actingAs($playerUser)
            ->from(route('bounties.index'))
            ->post(route('bounties.store'), ['target_id' => $daniel->id, 'amount' => 10000])
            ->assertRedirect(route('bounties.index'))
            ->assertSessionHas('error');
        $this->assertDatabaseMissing('bounties', ['target_character_id' => $daniel->id]);

        $this->actingAs($playerUser)
            ->from(route('profile.show', $dave->name))
            ->post(route('profile.respect', $dave->name))
            ->assertRedirect(route('profile.show', $dave->name))
            ->assertSessionHas('error');

        $this->assertEquals(0, $dave->fresh()->respect);
        $this->assertSame(100, $daniel->fresh()->health);
    }

    /**
     * Verify that owners keep viewing, admin, and social access while gameplay mutations and stat gains stay blocked.
     */
    public function test_owners_can_view_pages_and_use_staff_and_social_features_while_gameplay_mutations_are_blocked(): void
    {
        $daveUser = User::factory()->create([
            'name' => 'Dave',
            'role' => 'owner',
            'is_staff' => true,
            'is_onboarded' => true,
        ]);
        $dave = Character::factory()->create([
            'user_id' => $daveUser->id,
            'name' => 'Dave',
            'title' => 'Eigenaar',
            'level' => 0,
            'xp' => 0,
            'total_xp' => 0,
            'respect' => 0,
            'cash' => 0,
            'bank' => 0,
            'energy' => 100,
            'health' => 100,
        ]);

        $otherUser = User::factory()->create(['role' => 'user', 'is_onboarded' => true]);
        $otherChar = Character::factory()->create([
            'user_id' => $otherUser->id,
            'name' => 'PietjePaviljoen',
            'level' => 5,
            'respect' => 80,
        ]);

        // Viewing game and admin pages succeeds without granting passive allowance cash or causing level-0 errors.
        $this->actingAs($daveUser)->get(route('dashboard'))->assertOk();
        $this->actingAs($daveUser)->get(route('fight.index'))->assertOk();
        $this->actingAs($daveUser)->get(route('bank.index'))->assertOk();
        $this->actingAs($daveUser)->get(route('members.index'))->assertOk();
        $this->actingAs($daveUser)->get(route('profile.show', $dave->name))->assertOk();
        $this->actingAs($daveUser)->get(route('leaderboard.index'))->assertOk();
        $this->actingAs($daveUser)->get(route('newspaper.index'))->assertOk();
        $this->actingAs($daveUser)->get(route('armwrestling.index'))->assertOk();
        $this->actingAs($daveUser)->get(route('prison'))->assertOk();
        $this->actingAs($daveUser)->get(route('hitman.index'))->assertOk();
        $this->actingAs($daveUser)->get(route('bounties.index'))->assertOk();
        $this->actingAs($daveUser)->get(route('murder_stats.index'))->assertOk();
        $this->actingAs($daveUser)->get(route('battle.index'))->assertOk();
        $this->actingAs($daveUser)->get(route('gangs.index'))->assertOk();
        $this->actingAs($daveUser)->get(route('shop.index'))->assertOk();
        $this->actingAs($daveUser)->get(route('admin.dashboard'))->assertOk();

        $this->assertNull(app(AllowanceService::class)->payoutPendingAllowance($dave));
        $this->assertSame(0, $dave->fresh()->bank);

        // Claiming Discord link bonus on an exempt owner does not grant €2,500 gameplay cash.
        $this->assertTrue($dave->claimDiscordReward());
        $this->assertSame(0, $dave->fresh()->cash);
        $this->assertNotNull($dave->fresh()->discord_reward_claimed_at);

        // Friends cannot grant gameplay respect to an exempt owner via friends.respect either.
        $friendship = Friend::create([
            'character_id' => $otherChar->id,
            'friend_id' => $dave->id,
            'status' => 'accepted',
        ]);
        $this->actingAs($otherUser)
            ->withSession(['friend_captcha_ans' => 7])
            ->from(route('friends.index'))
            ->post(route('friends.respect', $friendship), ['captcha_answer' => 7])
            ->assertRedirect(route('friends.index'))
            ->assertSessionHas('error');
        $this->assertEquals(0, $dave->fresh()->respect);

        // Direct stat service calls do not grant level, XP, cash, or respect to exempt owners.
        $xpResult = app(LevelService::class)->addXp($dave, 5000);
        $dave->refresh();
        $this->assertFalse($xpResult['leveled_up']);
        $this->assertSame(0, $dave->level);
        $this->assertSame(0, $dave->xp);
        $this->assertSame(0, $dave->cash);

        $dave->addRespect(50);
        $dave->save();
        $this->assertEquals(0, $dave->fresh()->respect);

        // Non-gameplay profile, chat, and friend actions succeed.
        $this->actingAs($daveUser)
            ->from(route('profile.me'))
            ->post(route('profile.update'), [
                'status_message' => 'Kliniek Eigenaar aanwezig',
                'bio' => 'Staat boven de partijen.',
            ])
            ->assertRedirect(route('profile.me'))
            ->assertSessionHas('success');
        $this->assertSame('Staat boven de partijen.', $dave->fresh()->bio);

        $this->actingAs($daveUser)
            ->postJson(route('chat.public.send'), ['message' => 'Welkom in de kliniek!'])
            ->assertOk();

        $this->actingAs($daveUser)
            ->from(route('profile.show', $otherChar->name))
            ->post(route('friends.add'), ['friend_name' => $otherChar->name])
            ->assertRedirect(route('profile.show', $otherChar->name))
            ->assertSessionHas('success');

        // Gameplay POST routes (both named and unnamed aliases) are blocked by middleware.
        $this->actingAs($daveUser)
            ->from(route('hustle.index'))
            ->post(route('hustle.attempt'), ['hustle_id' => 1])
            ->assertRedirect(route('hustle.index'))
            ->assertSessionHas('error');

        $this->actingAs($daveUser)
            ->from(route('gangs.index'))
            ->post('/bende/aanmaken', ['name' => 'Verboden Bende', 'tag' => 'VRBD'])
            ->assertRedirect(route('gangs.index'))
            ->assertSessionHas('error');

        $this->actingAs($daveUser)
            ->postJson(route('hustle.attempt'), ['hustle_id' => 1])
            ->assertStatus(403)
            ->assertJsonPath('success', false);
    }

    /**
     * Verify that the members directory and profile view display Eigenaar status and hide combat and stat elements.
     */
    public function test_members_directory_and_profile_page_render_eigenaar_without_gameplay_stats_or_attack_buttons(): void
    {
        $danielUser = User::factory()->create([
            'name' => 'Daniel',
            'role' => 'owner',
            'is_staff' => true,
            'is_onboarded' => true,
        ]);
        $daniel = Character::factory()->create([
            'user_id' => $danielUser->id,
            'name' => 'Daniël',
            'title' => 'Eigenaar',
            'level' => 0,
            'respect' => 0,
            'cash' => 0,
            'bank' => 0,
        ]);

        $viewerUser = User::factory()->create(['role' => 'user', 'is_onboarded' => true]);
        Character::factory()->create([
            'user_id' => $viewerUser->id,
            'name' => 'BezoekerHenk',
            'level' => 6,
            'respect' => 120,
            'cash' => 5000,
            'energy' => 100,
        ]);

        $membersResponse = $this->actingAs($viewerUser)->get(route('members.index'));
        $membersResponse->assertOk();
        $membersData = collect($membersResponse->viewData('membersData'));
        $danielEntry = $membersData->firstWhere('name', 'Daniël');
        $this->assertNotNull($danielEntry);
        $this->assertTrue($danielEntry['is_owner']);
        $this->assertTrue($danielEntry['is_game_exempt']);

        $profileResponse = $this->actingAs($viewerUser)->get(route('profile.show', $daniel->name));
        $profileResponse->assertOk();
        $profileResponse->assertSee('Eigenaar');
        $profileResponse->assertSee('Kliniek Eigenaar');
        $profileResponse->assertDontSee('Beroof Patiënt');
        $profileResponse->assertDontSee('Geef Respect (+1)');
        $profileResponse->assertDontSee('Voortgang naar Level');
    }
}
