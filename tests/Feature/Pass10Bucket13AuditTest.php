<?php

namespace Tests\Feature;

use App\Http\Controllers\SmuggleController;
use App\Http\Controllers\SponsorAdController;
use App\Http\Controllers\TreatmentController;
use App\Models\Character;
use App\Models\EscapeTunnel;
use App\Models\SupportTicket;
use App\Models\SupportTicketMessage;
use App\Models\TimelinePost;
use App\Models\User;
use App\Services\TrainingService;
use App\Support\TreatmentCaseCatalog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\ViewErrorBag;
use Tests\TestCase;

class Pass10Bucket13AuditTest extends TestCase
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
            'name' => 'AuditPatient13',
            'cash' => 250000,
            'bank' => 500000,
            'stars' => 100,
            'energy' => 100,
            'health' => 100,
            'level' => 10,
            'respect' => 1500,
        ]);
    }

    public function test_sponsor_view_handles_disabled_ads_and_coma(): void
    {
        $this->actingAs($this->user);

        // 1. Ads disabled globally
        config(['services.robinads.enabled' => false]);
        $this->assertFalse(SponsorAdController::areAdsEnabled());

        $view = view('game.sponsor.index', [
            'character' => $this->character,
            'adsLeftToday' => 500,
            'adsToday' => 0,
            'totalAds' => 0,
            'vipProgress' => 0,
            'adsUntilVip' => 250,
            'adDailyData' => [],
        ])->render();

        $this->assertStringContainsString('Sponsoradvertenties zijn momenteel tijdelijk uitgeschakeld', $view);
        $this->assertStringContainsString('disabled', $view);

        // 2. Character in coma
        config(['services.robinads.enabled' => true]);
        $this->character->update([
            'health' => 0,
            'coma_until' => now()->addMinutes(30),
        ]);
        $this->assertTrue($this->character->isComatose());

        $viewComa = view('game.sponsor.index', [
            'character' => $this->character,
            'adsLeftToday' => 500,
            'adsToday' => 0,
            'totalAds' => 0,
            'vipProgress' => 0,
            'adsUntilVip' => 250,
            'adDailyData' => [],
        ])->render();

        $this->assertStringContainsString('Je ligt momenteel op de Intensive Care', $viewComa);
        $this->assertStringContainsString('Ontwaak gratis via 3 Sponsor Ads', $viewComa);
    }

    public function test_shop_view_guards_comatose_and_insufficient_stars(): void
    {
        $this->actingAs($this->user);

        // In coma with 0 stars
        $this->character->update([
            'health' => 0,
            'coma_until' => now()->addMinutes(30),
            'stars' => 0,
        ]);

        $statsComparison = [
            'energy' => 100,
            'max_energy' => 100,
            'health' => 0,
            'max_health' => 100,
            'strength' => 10,
            'base_strength' => 10,
            'defense' => 10,
            'stamina' => 10,
            'base_stamina' => 10,
            'intelligence' => 10,
            'charisma' => 10,
            'discipline' => 10,
            'stat_cap' => 100,
        ];

        $view = view('game.shop', [
            'character' => $this->character,
            'items' => collect([]),
            'bundles' => collect([]),
            'inventory' => collect([]),
            'otherPatients' => null,
            'topRankings' => collect([]),
            'dailyDealItem' => null,
            'dailyDealDiscountPct' => 0,
            'playerStatsComparison' => $statsComparison,
            'activeTab' => 'sterren',
        ])->render();

        $this->assertStringContainsString('Direct Resetten (5 ⭐)', $view);
        // Vitale reset must be disabled because of coma and 0 stars
        $this->assertStringContainsString('disabled', $view);

        // If imprisoned with 0 stars, cell escape is disabled
        $this->character->update([
            'health' => 100,
            'coma_until' => null,
            'prison_until' => now()->addMinutes(10),
            'stars' => 0,
        ]);
        $this->assertTrue($this->character->isImprisoned());

        $viewPrison = view('game.shop', [
            'character' => $this->character,
            'items' => collect([]),
            'bundles' => collect([]),
            'inventory' => collect([]),
            'otherPatients' => [],
            'topRankings' => collect([]),
            'dailyDealItem' => null,
            'dailyDealDiscountPct' => 0,
            'playerStatsComparison' => $statsComparison,
            'activeTab' => 'sterren',
        ])->render();

        $this->assertStringContainsString('Vrijlating Kopen (10 ⭐)', $viewPrison);
    }

    public function test_timeline_view_renders_safely_and_guards_comatose_or_imprisoned(): void
    {
        $this->actingAs($this->user);

        // 1. Normal state with post having null created_at
        $post = new TimelinePost([
            'character_id' => $this->character->id,
            'content' => 'Hallo afdeling!',
        ]);
        $post->id = 1;
        $post->created_at = null;
        $post->setRelation('character', $this->character);
        $post->setRelation('comments', collect([]));
        $post->setRelation('likes', collect([]));

        $paginator = new LengthAwarePaginator(
            collect([$post]),
            1,
            15,
            1,
            ['path' => route('timeline.index')]
        );

        $view = view('game.timeline.index', [
            'character' => $this->character,
            'posts' => $paginator,
        ])->render();

        $this->assertStringContainsString('Hallo afdeling!', $view);

        // 2. Imprisoned state
        $this->character->update(['prison_until' => now()->addMinutes(15)]);
        $viewImprisoned = view('game.timeline.index', [
            'character' => $this->character,
            'posts' => $paginator,
        ])->render();

        $this->assertStringContainsString('Je zit momenteel in de isoleercel', $viewImprisoned);
        $this->assertStringContainsString('Plaatsen van berichten is tijdelijk geblokkeerd', $viewImprisoned);
    }

    public function test_truecrime_view_guards_comatose_and_insufficient_stars(): void
    {
        $this->actingAs($this->user);

        $this->character->update([
            'health' => 0,
            'coma_until' => now()->addMinutes(30),
            'stars' => 0,
        ]);

        $state = (object) [
            'cases_solved_count' => 5,
            'cases_failed_count' => 1,
            'total_rewards_cash' => 5000,
            'total_rewards_respect' => 20,
        ];

        $view = view('game.truecrime.index', [
            'character' => $this->character,
            'state' => $state,
            'totalCasesCount' => 500,
            'cooldownRemaining' => 3600,
            'adsWatched' => 0,
            'activeCase' => null,
            'lastCase' => null,
        ])->render();

        $this->assertStringContainsString('Je ligt momenteel op de Intensive Care', $view);
        $this->assertStringContainsString('Wachttijd Direct Overslaan (15 Sterren ⭐)', $view);
        $this->assertStringContainsString('disabled', $view);
    }

    public function test_treatment_view_handles_gmdate_and_guards(): void
    {
        $this->actingAs($this->user);

        $this->character->update([
            'health' => 0,
            'coma_until' => now()->addMinutes(30),
            'stars' => 10,
        ]);

        $dailyCase = TreatmentCaseCatalog::getCaseForDay(1);

        $view = view('game.treatment.index', [
            'character' => $this->character,
            'dailyCase' => $dailyCase,
            'isOnCooldown' => true,
            'remainingCooldown' => 120,
            'activeQuestions' => $dailyCase['questions'] ?? [],
            'leaveActions' => TreatmentController::LEAVE_ACTIONS,
            'isLeaveActionOnCooldown' => false,
            'remainingLeaveCooldown' => 0,
            'lastReview' => null,
        ])->render();

        $this->assertStringContainsString('Je ligt momenteel op de Intensive Care', $view);
        $this->assertStringContainsString('00:02:00', $view);
        $this->assertStringContainsString('Omzeilen (35 Sterren)', $view);
        $this->assertStringContainsString('disabled', $view);
    }

    public function test_settings_account_renders_safely_when_username_change_locked_and_null_timestamp(): void
    {
        $this->actingAs($this->user);

        $this->character->username_changed_at = null;

        $view = view('game.settings.account', [
            'user' => $this->user,
            'character' => $this->character,
            'canChangeUsername' => false,
            'daysUntilChange' => 30,
            'sessions' => collect([]),
        ])->render();

        $this->assertStringContainsString('@Gebruikersnaam vergrendeld', $view);
        $this->assertStringContainsString('30 dagen', $view);
    }

    public function test_slots_view_shows_alert_when_imprisoned_or_comatose(): void
    {
        $this->actingAs($this->user);

        $this->character->update(['prison_until' => now()->addMinutes(20)]);

        $view = view('game.slots.index', [
            'character' => $this->character,
        ])->render();

        $this->assertStringContainsString('Je zit momenteel in de isoleercel', $view);
        $this->assertStringContainsString('Fruitautomaat', $view);
    }

    public function test_smuggle_view_shows_alert_and_guards(): void
    {
        $this->actingAs($this->user);

        $this->character->update([
            'health' => 0,
            'coma_until' => now()->addMinutes(30),
        ]);

        $view = view('game.smuggle.index', [
            'character' => $this->character,
            'transportRoutes' => SmuggleController::TRANSPORT_ROUTES,
            'cigarettes' => 10,
            'ritalin' => 5,
            'weed' => 2,
            'phones' => 1,
            'orders' => [],
        ])->render();

        $this->assertStringContainsString('Je ligt momenteel op de Intensive Care', $view);
        $this->assertStringContainsString('Synthese-Lab', $view);
    }

    public function test_theft_view_handles_null_timestamps_and_guards(): void
    {
        $this->actingAs($this->user);

        $this->character->update([
            'health' => 0,
            'coma_until' => now()->addMinutes(30),
        ]);

        $fakeTheft = (object) [
            'id' => 1,
            'success' => true,
            'log_message' => 'Succesvol portemonnee gerold',
            'created_at' => null,
        ];

        $view = view('game.theft', [
            'character' => $this->character,
            'victims' => collect([]),
            'tiers' => [
                'pocket' => [
                    'name' => 'Zakkenrollen',
                    'jail_minutes' => 5,
                    'required_level' => 1,
                    'energy_cost' => 8,
                    'min_cash' => 50,
                    'max_cash' => 250,
                    'base_chance' => 75,
                ],
            ],
            'recentThefts' => collect([$fakeTheft]),
        ])->render();

        $this->assertStringContainsString('Je ligt momenteel op de Intensive Care', $view);
        $this->assertStringContainsString('Succesvol portemonnee gerold', $view);
    }

    public function test_tickets_views_render_null_timestamps_safely(): void
    {
        $this->actingAs($this->user);

        $ticket = new SupportTicket([
            'user_id' => $this->user->id,
            'ticket_number' => 'TBS-12345',
            'subject' => 'Hulp nodig bij missie',
            'category' => 'vraag',
            'priority' => 'normaal',
            'status' => 'open',
        ]);
        $ticket->id = 1;
        $ticket->updated_at = null;
        $ticket->created_at = null;
        $ticket->closed_at = null;
        $ticket->setRelation('lastMessage', null);
        $ticket->setRelation('messages', collect([]));

        $paginator = new LengthAwarePaginator(
            collect([$ticket]),
            1,
            15,
            1,
            ['path' => route('help.tickets.index')]
        );

        $indexView = view('game.tickets.index', [
            'character' => $this->character,
            'tickets' => $paginator,
            'status' => 'open',
            'counts' => ['all' => 1, 'open' => 1, 'in_behandeling' => 0, 'beantwoord' => 0, 'gesloten' => 0],
            'openCount' => 1,
            'answeredCount' => 0,
        ])->render();

        $this->assertStringContainsString('TBS-12345', $indexView);
        $this->assertStringContainsString('Hulp nodig bij missie', $indexView);

        $msg = new SupportTicketMessage([
            'support_ticket_id' => 1,
            'user_id' => $this->user->id,
            'character_id' => $this->character->id,
            'is_staff' => false,
            'message' => 'Wat moet ik doen bij stap 2?',
        ]);
        $msg->id = 1;
        $msg->created_at = null;
        $msg->setRelation('user', $this->user);
        $msg->setRelation('character', $this->character);
        $ticket->setRelation('messages', collect([$msg]));

        $showView = view('game.tickets.show', [
            'ticket' => $ticket,
            'errors' => new ViewErrorBag,
        ])->render();

        $this->assertStringContainsString('Wat moet ik doen bij stap 2?', $showView);
    }

    public function test_training_view_shows_alert_when_imprisoned(): void
    {
        $this->actingAs($this->user);

        $this->character->update(['prison_until' => now()->addMinutes(10)]);

        $view = view('game.training', [
            'character' => $this->character,
            'stats' => TrainingService::STATS,
            'recentLogs' => collect([]),
        ])->render();

        $this->assertStringContainsString('Je zit momenteel in de isoleercel', $view);
        $this->assertStringContainsString('De Sportzaal & Krachttraining', $view);
    }

    public function test_travel_view_guards_imprisoned_and_comatose(): void
    {
        $this->actingAs($this->user);

        $this->character->update(['prison_until' => now()->addMinutes(10)]);

        $view = view('game.travel.index', [
            'character' => $this->character,
            'locations' => [
                'wing_a' => [
                    'name' => 'Vleugel A (Gesloten)',
                    'icon' => 'fa-hospital',
                    'desc' => 'Zwaar bewaakt',
                    'badge' => 'Beveiligd',
                ],
            ],
            'travelCost' => 500,
        ])->render();

        $this->assertStringContainsString('Je zit momenteel in de isoleercel', $view);
        $this->assertStringContainsString('disabled', $view);
    }

    public function test_tunnel_view_shows_alert_when_comatose(): void
    {
        $this->actingAs($this->user);

        $this->character->update([
            'health' => 0,
            'coma_until' => now()->addMinutes(30),
        ]);

        $tunnel = (object) [
            'stage' => 1,
            'progress_pct' => 20,
            'spoons_used' => 5,
            'sand_in_socks' => 10,
            'is_completed' => false,
        ];

        $stages = EscapeTunnel::STAGES;
        $currentStageInfo = $stages[1];

        $view = view('game.tunnel.index', [
            'character' => $this->character,
            'tunnel' => $tunnel,
            'stages' => $stages,
            'currentStageInfo' => $currentStageInfo,
            'digCooldown' => 0,
            'dumpCooldown' => 0,
        ])->render();

        $this->assertStringContainsString('Je ligt momenteel op de Intensive Care', $view);
        $this->assertStringContainsString('Coma (Code Blauw)', $view);
    }

    public function test_vault_view_shows_alert_when_imprisoned(): void
    {
        $this->actingAs($this->user);

        $this->character->update(['prison_until' => now()->addMinutes(10)]);

        $view = view('game.vault.index', [
            'character' => $this->character,
            'myCodes' => collect([]),
            'topRankings' => collect([]),
            'prefillCode' => null,
        ])->render();

        $this->assertStringContainsString('Je zit momenteel in de isoleercel', $view);
        $this->assertStringContainsString('Medicijnkluis & Vouchers', $view);
    }
}
