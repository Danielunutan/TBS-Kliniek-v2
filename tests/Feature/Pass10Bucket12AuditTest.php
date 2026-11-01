<?php

namespace Tests\Feature;

use App\Http\Controllers\PickpocketController;
use App\Http\Controllers\PrisonController;
use App\Models\Character;
use App\Models\GameMessage;
use App\Models\NewsArticle;
use App\Models\NewsComment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Tests\TestCase;

class Pass10Bucket12AuditTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Character $character;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['is_onboarded' => true]);
        $this->character = Character::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'AuditPatient12',
            'cash' => 250000,
            'bank' => 500000,
            'stars' => 100,
            'energy' => 100,
            'health' => 100,
            'level' => 10,
            'respect' => 1500,
        ]);
    }

    public function test_messages_view_renders_cleanly_with_null_and_active_created_at(): void
    {
        $this->actingAs($this->user);

        $fakeMessage = new GameMessage([
            'recipient_id' => $this->character->id,
            'subject' => 'Testbericht',
            'sender_id' => null,
            'sender_name' => 'Directie',
            'is_read' => false,
            'type' => 'system',
            'content' => 'Test bericht zonder datum',
        ]);
        $fakeMessage->id = 1;
        $fakeMessage->created_at = null;

        $paginator = new LengthAwarePaginator(
            collect([$fakeMessage]),
            1,
            15,
            1,
            ['path' => route('messages.index')]
        );

        $view = view('game.messages', [
            'character' => $this->character,
            'messages' => $paginator,
            'filter' => 'all',
            'unreadCount' => 1,
            'customFolders' => collect([]),
            'systemUnreadCount' => 1,
            'chatUnreadCount' => 0,
            'attackUnreadCount' => 0,
            'activeFolderId' => null,
            'activeFolder' => null,
        ])->render();

        $this->assertStringContainsString('Directie', $view);
        $this->assertStringContainsString('Testbericht', $view);
    }

    public function test_news_show_view_renders_with_null_safe_timestamps(): void
    {
        $this->actingAs($this->user);

        $article = new NewsArticle([
            'title' => 'Kliniek Bulletin',
            'slug' => 'kliniek-bulletin',
            'content' => 'Inhoud van het bulletin',
            'formatted_content' => '<p>Inhoud van het bulletin</p>',
        ]);
        $article->id = 999;
        $article->created_at = null;

        $comment = new NewsComment([
            'character_id' => $this->character->id,
            'content' => 'Reactie zonder created_at',
        ]);
        $comment->id = 1;
        $comment->created_at = null;
        $comment->setRelation('character', $this->character);

        $commentsPaginator = new LengthAwarePaginator(
            collect([$comment]),
            1,
            10,
            1
        );

        $view = view('game.news.show', [
            'character' => $this->character,
            'article' => $article,
            'comments' => $commentsPaginator,
        ])->render();

        $this->assertStringContainsString('Kliniek Bulletin', $view);
        $this->assertStringContainsString('Directie Kliniek', $view);
        $this->assertStringContainsString('Reactie zonder created_at', $view);
    }

    public function test_packages_skip_button_disabled_with_insufficient_stars(): void
    {
        $this->actingAs($this->user);
        $this->character->stars = 2;
        $this->character->save();

        $view = view('game.packages.index', [
            'character' => $this->character,
            'packages' => [
                ['id' => 1, 'name' => 'Koerierszending', 'desc' => 'Envelop met cash.', 'energy' => 5, 'token_cost' => 0, 'min' => 80, 'max' => 280, 'rate' => 55, 'cooldown' => 60, 'xp' => 28, 'token_chance' => 15, 'icon' => 'fa-envelope'],
            ],
            'pkgCooldown' => 120,
            'packageSkipCost' => 10,
            'kofferLogs' => collect([]),
            'crackMethods' => [],
        ])->render();

        $this->assertStringContainsString('Wachtpost Omkopen (10 ⭐)', $view);
        $this->assertMatchesRegularExpression('/<button[^>]+disabled[^>]*>[\s\S]*?Wachtpost Omkopen/i', $view);
    }

    public function test_pickpocket_skip_button_disabled_with_insufficient_stars(): void
    {
        $this->actingAs($this->user);
        $this->character->stars = 0;
        $this->character->save();

        $view = view('game.pickpocket.index', [
            'character' => $this->character,
            'actions' => [
                ['id' => 1, 'location' => 'Bezoekershal', 'name' => 'Familielid', 'desc' => 'Rol zakken', 'energy' => 6, 'min' => 80, 'max' => 350, 'rate' => 52, 'cooldown' => 60, 'xp' => 35, 'icon' => 'fa-person-walking'],
            ],
            'ppCooldown' => 60,
            'ppSkipCost' => 10,
            'alertness' => 0,
            'isScouted' => false,
            'alertMod' => 0,
            'techniques' => PickpocketController::TECHNIQUES,
        ])->render();

        $this->assertStringContainsString('Wachttijd Afkopen (10 Sterren)', $view);
        $this->assertMatchesRegularExpression('/<button[^>]+disabled[^>]*>[\s\S]*?Wachttijd Afkopen/i', $view);
    }

    public function test_prison_view_disables_actions_when_character_is_comatose(): void
    {
        $this->actingAs($this->user);
        $this->character->is_in_prison = true;
        $this->character->prison_until = now()->addMinutes(10);
        $this->character->coma_until = now()->addMinutes(15);
        $this->character->save();

        $inmate = Character::factory()->create([
            'name' => 'CellMate1',
            'is_in_prison' => true,
            'prison_until' => now()->addMinutes(5),
        ]);

        $view = view('game.prison', [
            'character' => $this->character,
            'isImprisoned' => true,
            'remainingMinutes' => 10,
            'remainingSeconds' => 600,
            'jailList' => collect([$inmate]),
            'stats' => [
                'escapes_today' => 0,
                'attempts_today' => 0,
                'breakout_xp' => '0%',
                'slay' => '0.0',
                'best_day_date' => now()->format('d-m-Y'),
                'best_day_count' => 0,
            ],
            'topBreakers' => collect([]),
            'topRankings' => collect([]),
            'breakoutMethods' => PrisonController::BREAKOUT_METHODS,
            'reduceActions' => PrisonController::REDUCE_ACTIONS,
            'prisonActionCooldownRemaining' => 0,
            'adsProgress' => 0,
            'adsRequired' => 1,
        ])->render();

        // Breakout button should have disabled attribute
        $this->assertMatchesRegularExpression('/<button[^>]+disabled[^>]*>[\s\S]*?Stille Sluipuitbraak/i', $view);
        // Escape button should bind disabled: escapeAttempted || true
        $this->assertStringContainsString(':disabled="escapeAttempted || true"', $view);
        // Reduce actions should bind disabled: reduceCooldownSecs > 0 || true
        $this->assertStringContainsString(':disabled="reduceCooldownSecs > 0 || true"', $view);
    }

    public function test_profile_view_escapes_x_data_with_at_js_and_guards_respect_and_rob(): void
    {
        $this->actingAs($this->user);

        $opponentUser = User::factory()->create(['is_onboarded' => true]);
        $opponent = Character::factory()->create([
            'user_id' => $opponentUser->id,
            'name' => 'Dr. O\'Connor "The Ripper"',
            'title' => 'Master of "Chaos"',
            'cash' => 50000,
            'level' => 10,
            'xp' => 500,
            'is_in_prison' => true,
            'prison_until' => now()->addHours(1),
        ]);

        $view = view('game.profile', [
            'character' => $this->character,
            'myCharacter' => $this->character,
            'profileCharacter' => $opponent,
            'safeProfileTab' => 'dossier',
            'safeCustomTab' => 'title',
            'hasExplicitProfileTab' => false,
            'isOwnProfile' => false,
            'hasGivenRespectToday' => false,
            'totalRespectsReceived' => 10,
            'guestbookEntries' => new LengthAwarePaginator(collect([]), 0, 10, 1),
            'friends' => collect([]),
            'friendsCount' => 0,
            'bff' => null,
            'friendStatus' => 'none',
            'pendingRequestRecord' => null,
            'nextLevelXp' => 1000,
            'xpPercentage' => 50,
            'badges' => collect([]),
            'badgeSets' => [],
            'psychiatricReport' => $opponent->getPsychiatricReport(),
            'availableTitles' => [
                [
                    'id' => 'master_"chaos"',
                    'name' => 'Master of Chaos',
                    'unlocked' => true,
                    'requirement' => 'Bereikt niveau 10',
                ],
            ],
            'unlockedStickers' => [],
            'allStickers' => [],
            'allThemes' => [],
            'allBorders' => [],
            'unlockedBorders' => [],
            'activeSticker' => null,
            'activeTheme' => null,
            'nameGradientCatalog' => [],
            'nameColorCatalog' => [],
        ])->render();

        // Check @js output for selectedTitle with quotes
        $this->assertStringContainsString("selectedTitle: 'Master of \\u0022Chaos\\u0022'", $view);
        // Opponent is in prison -> Rob button must be disabled
        $this->assertMatchesRegularExpression('/<button[^>]+disabled[^>]*>[\s\S]*?Beroof Pati\xc3\xabnt/i', $view);
    }

    public function test_profile_view_disables_respect_when_attacker_is_imprisoned(): void
    {
        $this->actingAs($this->user);
        $this->character->is_in_prison = true;
        $this->character->prison_until = now()->addHours(1);
        $this->character->save();

        $opponentUser = User::factory()->create(['is_onboarded' => true]);
        $opponent = Character::factory()->create([
            'user_id' => $opponentUser->id,
            'name' => 'FreeInmate',
            'title' => 'Patiënt',
            'level' => 10,
            'xp' => 500,
            'cash' => 50000,
        ]);

        $view = view('game.profile', [
            'character' => $this->character,
            'myCharacter' => $this->character,
            'profileCharacter' => $opponent,
            'safeProfileTab' => 'dossier',
            'safeCustomTab' => 'title',
            'hasExplicitProfileTab' => false,
            'isOwnProfile' => false,
            'hasGivenRespectToday' => false,
            'totalRespectsReceived' => 10,
            'guestbookEntries' => new LengthAwarePaginator(collect([]), 0, 10, 1),
            'friends' => collect([]),
            'friendsCount' => 0,
            'bff' => null,
            'friendStatus' => 'none',
            'pendingRequestRecord' => null,
            'nextLevelXp' => 1000,
            'xpPercentage' => 50,
            'badges' => collect([]),
            'badgeSets' => [],
            'psychiatricReport' => $opponent->getPsychiatricReport(),
            'availableTitles' => [],
            'unlockedStickers' => [],
            'allStickers' => [],
            'allThemes' => [],
            'allBorders' => [],
            'unlockedBorders' => [],
            'activeSticker' => null,
            'activeTheme' => null,
            'nameGradientCatalog' => [],
            'nameColorCatalog' => [],
        ])->render();

        // Give Respect button must be disabled when authenticated character is in prison
        $this->assertMatchesRegularExpression('/<button[^>]+disabled[^>]*>[\s\S]*?Geef Respect/i', $view);
    }

    public function test_roadmap_view_renders_cleanly_with_phase_matching(): void
    {
        $this->actingAs($this->user);

        $view = view('game.roadmap', [
            'character' => $this->character,
            'features' => [
                'casino_upgrade' => [
                    'phase' => 'fase1',
                    'icon' => 'fa-dice',
                    'icon_style' => 'bg-emerald-500 text-white',
                    'status' => 'In Ontwikkeling',
                    'status_badge' => 'bg-emerald-100 text-emerald-800',
                    'category' => 'Minigames',
                    'title' => 'Casino Upgrade',
                    'subtitle' => 'Nieuwe spellen',
                    'description' => 'Roulette en Blackjack revisie',
                    'has_voted' => false,
                    'votes' => 12,
                    'votes_count' => 12,
                    'progress_pct' => 60,
                    'highlights' => ['Casino upgrade', 'Nieuwe spellen'],
                ],
            ],
            'categories' => ['Minigames'],
            'stats' => [
                'total_features' => 1,
                'total_votes' => 12,
                'completed' => 0,
                'in_progress' => 1,
            ],
        ])->render();

        $this->assertMatchesRegularExpression('/matches\(\{\s*phase:\s*(&#039;|&quot;|"|\')fase1(&#039;|&quot;|"|\')\s*\}\)/', $view);
        $this->assertStringContainsString('Casino Upgrade', $view);
    }
}
