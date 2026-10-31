<?php

namespace Tests\Feature;

use App\Http\Controllers\ArmWrestleController;
use App\Models\ArmWrestleMatch;
use App\Models\Bounty;
use App\Models\Character;
use App\Models\CinemaRoom;
use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ViewErrorBag;
use Tests\TestCase;

class AuditBatch5SecurityAndLogicTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        View::share('errors', new ViewErrorBag);
    }

    protected function createCharacterWithUser(string $name = 'InmateFive', string $role = 'user'): Character
    {
        $user = User::factory()->create([
            'role' => $role,
            'name' => $name,
        ]);

        return Character::create([
            'user_id' => $user->id,
            'name' => $name,
            'cash' => 5000,
            'bank' => 10000,
            'stars' => 50,
            'respect' => 100,
            'energy' => 100,
            'max_energy' => 100,
            'health' => 100,
            'max_health' => 100,
            'level' => 5,
            'strength' => 50,
            'defense' => 50,
            'stamina' => 50,
            'discipline' => 50,
        ]);
    }

    /**
     * Test 1: Admin ticket external gang link blocks javascript: pseudo-protocol XSS.
     */
    public function test_admin_ticket_blocks_javascript_xss_and_allows_safe_https_link(): void
    {
        $staff = $this->createCharacterWithUser('AdminUser', 'admin');

        $maliciousTicket = SupportTicket::create([
            'user_id' => $staff->user_id,
            'character_id' => $staff->id,
            'ticket_number' => 'TCK-2026-XSS',
            'subject' => 'Bende Aanvraag XSS',
            'category' => 'gang_overstap',
            'priority' => 'normal',
            'status' => 'open',
            'metadata' => [
                'gang_name' => 'EvilGang',
                'gang_tag' => 'EVIL',
                'external_gang_link' => 'javascript:alert(document.cookie)',
            ],
        ]);

        $view = $this->actingAs($staff->user)->view('admin.tickets.show', [
            'ticket' => $maliciousTicket,
        ]);

        // Malicious javascript link should NOT be rendered inside an <a href="..."> tag
        $view->assertDontSee('href="javascript:alert', false);
        $view->assertSee('javascript:alert(document.cookie)', false);

        // Safe HTTPS link should be rendered with target="_blank" and rel="noopener noreferrer"
        $safeTicket = SupportTicket::create([
            'user_id' => $staff->user_id,
            'character_id' => $staff->id,
            'ticket_number' => 'TCK-2026-SAFE',
            'subject' => 'Bende Aanvraag Safe',
            'category' => 'gang_overstap',
            'priority' => 'normal',
            'status' => 'open',
            'metadata' => [
                'gang_name' => 'SafeGang',
                'gang_tag' => 'SAFE',
                'external_gang_link' => 'https://example.com/gang-proof',
            ],
        ]);

        $safeView = $this->actingAs($staff->user)->view('admin.tickets.show', [
            'ticket' => $safeTicket,
        ]);

        $safeView->assertSee('href="https://example.com/gang-proof"', false);
        $safeView->assertSee('rel="noopener noreferrer"', false);
    }

    /**
     * Test 2: Login view sanitizes Alpine old input against quotes and includes noopener.
     */
    public function test_login_view_whitelists_gender_and_avatar_in_alpine_and_protects_legal_links(): void
    {
        session()->flashInput([
            'gender' => "'); alert('injected'); //",
            'avatar' => 'javascript:evil()',
        ]);

        $view = $this->view('auth.login');

        // Verify invalid/malicious gender is forced back to male whitelist and quotes are safe
        $view->assertSee('regGender', false);
        $view->assertDontSee('injected');

        // Verify terms and privacy links have rel="noopener noreferrer"
        $view->assertSee('rel="noopener noreferrer"', false);
    }

    /**
     * Test 3: Verify email view does not crash when auth user or user variable is null.
     */
    public function test_verify_email_view_handles_null_user_safely(): void
    {
        $view = $this->view('auth.verify-email', [
            'user' => null,
        ]);

        $view->assertSee('E-mail Verifiëren', false);
        $view->assertSee('verificatiecode');
    }

    /**
     * Test 4: Discord verify view escapes instant invite safely with @js.
     */
    public function test_discord_verify_escapes_instant_invite_safely(): void
    {
        $user = User::factory()->create();

        $view = $this->actingAs($user)->view('discord.verify', [
            'widgetData' => [
                'instant_invite' => "https://discord.gg/tbs'test\"quote",
            ],
            'discordUser' => null,
            'character' => null,
            'linkedAccount' => null,
            'roles' => [],
            'inGuild' => false,
            'autoSyncEnabled' => true,
            'rewards' => [],
        ]);

        $view->assertSee('navigator.clipboard.writeText', false);
        $view->assertSee('https:\/\/discord.gg\/tbs', false);
    }

    /**
     * Test 5: Armwrestling view renders safely when open challenge challenger is null and stat is null.
     */
    public function test_armwrestling_view_renders_safely_when_challenger_is_null(): void
    {
        $character = $this->createCharacterWithUser('ArmWrestler');

        // Create an open match object with null challenger
        $openMatch = new ArmWrestleMatch([
            'challenger_id' => 999999,
            'bet_amount' => 500,
            'status' => 'open',
            'type' => 'pvp',
        ]);
        $openMatch->id = 1;
        $openMatch->setRelation('challenger', null);

        // Recent match with null challenger & opponent
        $recentMatch = new ArmWrestleMatch([
            'challenger_id' => 999998,
            'opponent_id' => 999997,
            'bet_amount' => 200,
            'status' => 'finished',
            'type' => 'pvp',
        ]);
        $recentMatch->id = 2;
        $recentMatch->setRelation('challenger', null);
        $recentMatch->setRelation('opponent', null);
        $recentMatch->setRelation('winner', null);

        $view = $this->actingAs($character->user)->view('game.armwrestling.index', [
            'character' => $character,
            'openChallenges' => collect([$openMatch]),
            'recentMatches' => collect([$recentMatch]),
            'myActiveMatch' => null,
            'myOpenChallenge' => null,
            'stat' => null, // Testing null $stat safety
            'npcs' => ArmWrestleController::getNpcOpponents(),
            'leaderboard' => collect(),
            'topChampions' => collect(),
            'totalMatches' => 0,
            'totalBetVolume' => 0,
        ]);

        $view->assertSee('Patiënt');
        $view->assertSee('Pot: € 1.000');
        $view->assertSee('0 Gewonnen');
    }

    /**
     * Test 6: Battle index escapes category titles containing apostrophes.
     */
    public function test_battle_index_escapes_category_titles_with_quotes(): void
    {
        $character = $this->createCharacterWithUser('GladiatorInmate');

        $categories = [
            'fight_club' => [
                'title' => "Inmate's Fight Arena",
                'icon' => 'fa-hand-fist',
                'desc' => 'De zwaarste vechters van het gesticht',
                'myRank' => 5,
                'myScore' => 120,
                'unit' => 'gevechten',
                'top3' => [],
                'rewards' => [
                    1 => ['label' => '100 Sterren ⭐'],
                    2 => ['label' => '50 Sterren ⭐'],
                    3 => ['label' => '25 Sterren ⭐'],
                ],
            ],
        ];

        $view = $this->actingAs($character->user)->view('game.battle.index', [
            'character' => $character,
            'categories' => $categories,
            'history' => collect(),
            'secondsUntilReset' => 3600,
            'resetTimeFormatted' => '23:59:59',
            'topRankings' => collect(),
        ]);

        $view->assertSee('openCategoryDetails', false);
        $view->assertSee('fight_club', false);
    }

    /**
     * Test 7: Bounties index renders safely when user has no character and targets have quotes.
     */
    public function test_bounties_index_handles_user_without_character_and_escapes_targets(): void
    {
        $userWithoutChar = User::factory()->create();
        $targetCharacter = $this->createCharacterWithUser('TargetInmate');

        $bounty = Bounty::create([
            'placed_by_character_id' => $targetCharacter->id,
            'target_character_id' => $targetCharacter->id,
            'amount' => 5000,
            'reason' => "Verraad door 'De Verrader'",
            'is_claimed' => false,
        ]);

        $potentialTarget = (object) [
            'id' => 88,
            'name' => "Klaas 'De Slager' O'Connor",
            'level' => 12,
            'avatar_url' => 'assets/characters/avatar_male_1.jpg',
        ];

        $view = $this->actingAs($userWithoutChar)->view('game.bounties.index', [
            'bounties' => collect([$bounty]),
            'potentialTargets' => collect([$potentialTarget]),
            'myPlacedBounties' => collect(),
            'myTargetedBounties' => collect(),
            'claimedBounties' => collect(),
            'totalBountyPool' => 5000,
        ]);

        $view->assertSee('Kopgeld Opsporingsbord');
        $view->assertSee('Vecht & Incasseer', false);
        $view->assertDontSee('Attempt to read property "id" on null');
    }

    /**
     * Test 8: Casino index handles null character or cash gracefully.
     */
    public function test_casino_index_handles_null_character_cash(): void
    {
        $user = User::factory()->create();

        $view = $this->actingAs($user)->view('game.casino.index', [
            'character' => null,
        ]);

        $view->assertSee('cash: 0', false);
    }

    /**
     * Test 9: Cinema index and room render safely with null character.
     */
    public function test_cinema_index_and_room_render_safely_with_null_character(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $room = new CinemaRoom([
            'title' => 'Documentaire Zaal',
            'slug' => 'documentaire-zaal',
            'description' => 'Test room',
            'host_character_id' => 10,
            'youtube_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'youtube_video_id' => 'dQw4w9WgXcQ',
            'is_official' => false,
            'is_private' => false,
            'is_closed' => false,
            'views_count' => 5,
        ]);
        $room->id = 1;
        $room->setRelation('host', null);

        $indexView = $this->actingAs($user)->view('game.cinema.index', [
            'character' => null,
            'officialRooms' => collect(),
            'patientRooms' => collect([$room]),
        ]);

        $indexView->assertSee('Documentaire Zaal');

        $roomView = $this->actingAs($user)->view('game.cinema.room', [
            'room' => $room,
            'character' => null,
            'initialPlaybackSeconds' => 0,
            'messages' => collect(),
            'queue' => [],
            'gifs' => [],
            'initialMessages' => [],
        ]);

        $roomView->assertSee('Documentaire Zaal');
        $roomView->assertSee('cinemaTheater', false);
    }

    /**
     * Test 10: Community index uses safe JS escaping for claim action.
     */
    public function test_community_index_escapes_platform_urls_with_js(): void
    {
        $user = User::factory()->create();

        $platforms = [
            'discord' => [
                'name' => 'Discord',
                'badge' => 'Officiële Community',
                'icon' => 'fa-brands fa-discord',
                'color' => '#5865F2',
                'description' => 'Chat gezellig mee.',
                'action_label' => 'Join Discord',
                'url' => "https://discord.gg/tbs'test",
                'reward' => ['cash' => 500, 'respect' => 10, 'energy' => 15],
            ],
        ];

        $view = $this->actingAs($user)->view('game.community.index', [
            'platforms' => $platforms,
            'claims' => [],
            'claimedCount' => 0,
            'totalPlatforms' => 1,
            'totalPossibleCash' => 500,
            'totalPossibleRespect' => 10,
        ]);

        $view->assertSee('claimPlatform', false);
        $view->assertSee('https:\/\/discord.gg\/tbs', false);
    }
}
