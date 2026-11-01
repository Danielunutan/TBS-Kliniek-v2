<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class Pass10Bucket15AuditTest extends TestCase
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
            'name' => 'AuditChar15',
            'cash' => 25000,
            'bank' => 50000,
            'stars' => 120,
            'level' => 3,
            'health' => 100,
            'max_health' => 100,
            'is_in_prison' => false,
            'prison_until' => null,
            'coma_until' => null,
        ]);
    }

    public function test_mini_chat_and_report_modal_renders_cleanly_for_normal_character(): void
    {
        $this->actingAs($this->user);

        $view = view('partials.mini_chat_and_report_modal', [
            'character' => $this->character,
        ])->render();

        $this->assertStringContainsString('isInPrison: false', $view);
        $this->assertStringContainsString('isComatose: false', $view);
        $this->assertStringContainsString(':disabled="isInPrison || isComatose"', $view);
        $this->assertStringContainsString('tbsMiniLiveChat', $view);
        $this->assertStringContainsString('tbsGlobalReportModal', $view);
    }

    public function test_mini_chat_and_report_modal_handles_comatose_state(): void
    {
        $this->actingAs($this->user);

        $this->character->health = 0;
        $this->character->coma_until = Carbon::now()->addMinutes(20);
        $this->character->save();

        $view = view('partials.mini_chat_and_report_modal', [
            'character' => $this->character,
        ])->render();

        $this->assertStringContainsString('isComatose: true', $view);
        $this->assertStringContainsString('Zaalwacht Arjan: Je ligt momenteel in coma in de Ziekenboeg!', $view);
    }

    public function test_mini_chat_and_report_modal_handles_imprisoned_state(): void
    {
        $this->actingAs($this->user);

        $this->character->is_in_prison = true;
        $this->character->prison_until = Carbon::now()->addMinutes(15);
        $this->character->save();

        $view = view('partials.mini_chat_and_report_modal', [
            'character' => $this->character,
        ])->render();

        $this->assertStringContainsString('isInPrison: true', $view);
        $this->assertStringContainsString('Zaalwacht Arjan: Je zit momenteel in de isoleercel!', $view);
    }

    public function test_mini_chat_and_report_modal_renders_for_guest(): void
    {
        $view = view('partials.mini_chat_and_report_modal')->render();

        $this->assertStringContainsString('isInPrison: false', $view);
        $this->assertStringContainsString('isComatose: false', $view);
        $this->assertStringContainsString('Live Chat', $view);
        $this->assertStringContainsString('tbsMiniLiveChat', $view);
    }

    public function test_mobile_drawer_renders_cleanly_and_shows_badges(): void
    {
        $this->actingAs($this->user);

        // Normal state
        $view = view('partials.mobile_drawer', [
            'character' => $this->character,
        ])->render();

        $this->assertStringContainsString('Isoleercel', $view);
        $this->assertStringContainsString('Ziekenboeg & Pillen', $view);
        $this->assertStringNotContainsString('Opgesloten', $view);
        $this->assertStringNotContainsString('Coma', $view);

        // Imprisoned state
        $this->character->is_in_prison = true;
        $this->character->prison_until = Carbon::now()->addMinutes(15);
        $this->character->save();

        $imprisonedView = view('partials.mobile_drawer', [
            'character' => $this->character,
        ])->render();
        $this->assertStringContainsString('Opgesloten', $imprisonedView);

        // Comatose state
        $this->character->is_in_prison = false;
        $this->character->prison_until = null;
        $this->character->health = 0;
        $this->character->coma_until = Carbon::now()->addMinutes(15);
        $this->character->save();

        $comatoseView = view('partials.mobile_drawer', [
            'character' => $this->character,
        ])->render();
        $this->assertStringContainsString('Coma', $comatoseView);
    }

    public function test_other_partials_render_without_blade_errors(): void
    {
        $this->actingAs($this->user);

        // pwa_prompt
        $pwaView = view('partials.pwa_prompt')->render();
        $this->assertStringContainsString('TBS Kliniek App', $pwaView);

        // radio_player
        $radioView = view('partials.radio_player')->render();
        $this->assertStringContainsString('MIXXR Radio', $radioView);

        // rookie_quests_widget
        $rookieData = [
            'grand_claimed' => false,
            'completed_count' => 2,
            'total_count' => 5,
            'progress_pct' => 40,
            'tasks' => [
                'profile' => [
                    'is_claimed' => true,
                    'can_claim' => false,
                    'title' => 'Profiel instellen',
                    'desc' => 'Kies een avatar en status.',
                    'reward_label' => '€500 contant',
                    'icon' => 'fa-user',
                    'route' => '/profiel',
                    'route_label' => 'Naar Profiel',
                ],
                'hustle' => [
                    'is_claimed' => false,
                    'can_claim' => true,
                    'title' => 'Eerste Hossel',
                    'desc' => 'Doe je eerste hosselactie.',
                    'reward_label' => '€1.000 contant',
                    'icon' => 'fa-bolt',
                    'route' => '/hosselen',
                    'route_label' => 'Hosselen',
                ],
            ],
        ];
        $rookieView = view('partials.rookie_quests_widget', ['rookieData' => $rookieData])->render();
        $this->assertStringContainsString('Rookie Beloningsspoor', $rookieView);
        $this->assertStringContainsString('Klaar om te Claimen!', $rookieView);

        // season_countdown_widget
        $countdownView = view('partials.season_countdown_widget')->render();
        $this->assertStringContainsString('SEIZOEN 1 AFTELLER', $countdownView);

        // season_welcome_modal
        $seasonWelcomeView = view('partials.season_welcome_modal')->render();
        $this->assertStringContainsString('Seizoen 1 Officieel Geopend', $seasonWelcomeView);
        $this->assertStringContainsString('SEIZOEN1', $seasonWelcomeView);

        // sponsor_and_cookie_modals
        $modalsView = view('partials.sponsor_and_cookie_modals')->render();
        $this->assertStringContainsString('Cookie-instellingen Beheren', $modalsView);

        // spotlight_modal
        $spotlightView = view('partials.spotlight_modal')->render();
        $this->assertStringContainsString('tbsSpotlightModal', $spotlightView);
    }

    public function test_all_pagination_views_render_properly(): void
    {
        $items = collect(range(1, 50));
        $paginator = new LengthAwarePaginator(
            $items->forPage(2, 10),
            $items->count(),
            10,
            2,
            ['path' => '/test-pagination']
        );

        $views = [
            'vendor.pagination.bootstrap-3',
            'vendor.pagination.bootstrap-4',
            'vendor.pagination.bootstrap-5',
            'vendor.pagination.semantic-ui',
            'vendor.pagination.simple-bootstrap-3',
            'vendor.pagination.simple-bootstrap-4',
            'vendor.pagination.simple-bootstrap-5',
            'vendor.pagination.simple-tailwind',
            'vendor.pagination.tailwind',
        ];

        foreach ($views as $viewName) {
            $rendered = $paginator->links($viewName)->render();
            $this->assertNotEmpty($rendered, "Pagination view {$viewName} rendered empty output.");
        }
    }

    public function test_routes_have_rate_limiting_throttle_middleware(): void
    {
        $routesToCheck = [
            'community.claim' => 'throttle:10,1',
            'sponsor.start' => 'throttle:30,1',
            'sponsor.claim' => 'throttle:30,1',
            'dashboard.peuken.sell' => 'throttle:30,1',
            'dashboard.radar.sweep' => 'throttle:30,1',
            'training.train' => 'throttle:60,1',
            'hustle.attempt' => 'throttle:60,1',
            'combat_training.train' => 'throttle:60,1',
            'news.comment' => 'throttle:15,1',
            'roadmap.vote' => 'throttle:20,1',
            'armwrestling.challenge.create' => 'throttle:20,1',
            'armwrestling.challenge.cancel' => 'throttle:30,1',
            'armwrestling.challenge.accept' => 'throttle:30,1',
            'battlepass.claim' => 'throttle:30,1',
            'battlepass.claim_all' => 'throttle:15,1',
        ];

        foreach ($routesToCheck as $routeName => $expectedThrottle) {
            $route = Route::getRoutes()->getByName($routeName);
            $this->assertNotNull($route, "Route {$routeName} not found.");

            $middleware = $route->gatherMiddleware();
            $this->assertContains(
                $expectedThrottle,
                $middleware,
                "Route {$routeName} is missing middleware {$expectedThrottle}. Actual: ".implode(', ', $middleware)
            );
        }
    }

    public function test_console_routes_and_scheduled_commands_are_valid(): void
    {
        $this->artisan('list')->assertSuccessful();
    }

    public function test_chat_mention_pills_use_compact_single_line_styling(): void
    {
        $miniChatSource = file_get_contents(resource_path('views/partials/mini_chat_and_report_modal.blade.php'));
        $fullChatSource = file_get_contents(resource_path('views/game/chat/index.blade.php'));
        $cinemaRoomSource = file_get_contents(resource_path('views/game/cinema/room.blade.php'));

        foreach ([$miniChatSource, $fullChatSource, $cinemaRoomSource] as $source) {
            $this->assertStringContainsString('inline-block whitespace-nowrap align-baseline leading-none', $source);
            $this->assertStringContainsString('>@${username}</a>', $source);
            $this->assertStringNotContainsString('[overflow-wrap:anywhere]', $source);
        }
    }
}
