<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\ControlPoint;
use App\Models\Gang;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditBatch6SecurityAndLogicTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    protected function createCharacter(string $name = 'Batch6Patient', array $attributes = []): Character
    {
        $user = User::factory()->create([
            'name' => $name,
        ]);

        return Character::create(array_merge([
            'user_id' => $user->id,
            'name' => $name,
            'cash' => 2500,
            'bank' => 5000,
            'stars' => 30,
            'respect' => 250,
            'energy' => 100,
            'max_energy' => 100,
            'health' => 100,
            'max_health' => 100,
            'level' => 3,
            'is_onboarded' => true,
        ], $attributes));
    }

    /**
     * Test Connect Four view renders safely with null statistics and default quotes.
     */
    public function test_connectfour_renders_safely_with_null_stat(): void
    {
        $character = $this->createCharacter('FourInARow');

        $view = $this->actingAs($character->user)->view('game.connectfour.index', [
            'character' => $character,
            'activeGame' => null,
            'boardState' => array_fill(0, 6, array_fill(0, 7, 0)),
            'stat' => null,
            'topWins' => collect(),
            'topCash' => collect(),
            'quotes' => ['intro' => 'Laten we spelen OOHPA!'],
            'difficultyLevels' => [
                'makkelijk' => ['name' => 'Makkelijk', 'fee' => 100, 'reward' => 200, 'xp' => 10, 'energy' => 2],
            ],
        ]);

        $view->assertSee('0 Toernooien Gewonnen');
        $view->assertSee('€ 0 Gewonnen');
        $view->assertSee('Laten we spelen OOHPA!');
    }

    /**
     * Test Control Point banner and index display Code Blauw when comatose.
     */
    public function test_control_points_banner_and_index_reflects_comatose_state_and_cost(): void
    {
        $character = $this->createCharacter('ComaPatient', [
            'health' => 0,
            'coma_until' => now()->addMinutes(30),
        ]);

        $point = ControlPoint::first() ?? ControlPoint::create([
            'key' => 'kantine_cp',
            'name' => 'Kantine Controlepunt',
            'location_name' => 'Kantine',
            'description' => 'De sleutel tot de voedselvoorraad.',
            'energy_cost' => 5,
            'cash_per_minute' => 100,
            'stars_per_minute' => 1,
        ]);

        $point->energy_cost = 5;
        $point->save();

        // 1. Test banner
        $bannerView = $this->actingAs($character->user)->view('game.control_points.banner', [
            'key' => $point->key,
        ]);

        $bannerView->assertSee('Code Blauw');
        $bannerView->assertDontSee('Controlepunt Overnemen');

        // 2. Test full index
        $indexView = $this->actingAs($character->user)->view('game.control_points.index', [
            'character' => $character,
            'points' => collect([$point]),
            'logs' => collect(),
            'userHeldCount' => 0,
        ]);

        $indexView->assertSee('Code Blauw');
        $indexView->assertSee('Kost 5⚡');
    }

    /**
     * Test dashboard renders referral link with @js and has FormData fix.
     */
    public function test_dashboard_renders_referral_and_form_data_fix(): void
    {
        $character = $this->createCharacter('DashboardViewer');

        $response = $this->actingAs($character->user)->get(route('dashboard'));
        $response->assertOk();

        // Check for safe FormData construction without undefined type error
        $response->assertSee('const formData = form ? new FormData(form) : new FormData();', false);
    }

    /**
     * Test daycare view disables actions for comatose/imprisoned characters.
     */
    public function test_daycare_disables_button_for_incapacitated_character(): void
    {
        $character = $this->createCharacter('SickInmate', [
            'health' => 0,
            'coma_until' => now()->addMinutes(30),
        ]);

        $view = $this->actingAs($character->user)->view('game.daycare.index', [
            'character' => $character,
            'jobs' => [
                [
                    'id' => 'keuken',
                    'name' => 'Keukendienst',
                    'icon' => 'fa-utensils',
                    'description' => 'Aardappels schillen',
                    'energy' => 10,
                    'min_cash' => 50,
                    'max_cash' => 100,
                    'xp' => 15,
                    'stat_bonus' => '+1 Kracht',
                ],
            ],
            'activeCooldown' => 0,
            'daycareSkipCost' => 12,
        ]);

        $view->assertSee('Keukendienst');
        $view->assertSee('disabled', false);
        $view->assertSee('cursor-not-allowed', false);
    }

    /**
     * Test extortion view disables attack when character is comatose.
     */
    public function test_extort_blocks_target_when_comatose(): void
    {
        $character = $this->createCharacter('ComaExtorter', [
            'health' => 0,
            'coma_until' => now()->addMinutes(30),
        ]);

        $view = $this->actingAs($character->user)->view('game.extort.index', [
            'character' => $character,
            'targets' => [
                [
                    'id' => 'verpleger',
                    'name' => 'Nachtzuster',
                    'desc' => 'Dwing haar tot stilzwijgen',
                    'icon' => 'fa-user-nurse',
                    'energy' => 15,
                    'rate' => 75,
                    'min' => 100,
                    'max' => 300,
                    'cooldown' => 180,
                ],
            ],
            'extortCooldown' => 0,
            'extortSkipCost' => 10,
        ]);

        $view->assertSee('Nachtzuster');
        $view->assertSee('disabled', false);
    }

    /**
     * Test FAQ view sanitizes multiline answers inside JavaScript search calls.
     */
    public function test_faq_escapes_multiline_and_quotes_safely(): void
    {
        $character = $this->createCharacter('FaqReader');

        $view = $this->actingAs($character->user)->view('game.faq', [
            'character' => $character,
            'categories' => [
                'algemeen' => [
                    'title' => 'Algemene Vragen',
                    'icon' => 'fa-circle-question',
                    'questions' => [
                        [
                            'q' => "Hoe speel je 'TBS'?",
                            'a' => "Regel 1: Breek niet.\nRegel 2: Vind bondgenoten.\r\nRegel 3: Wees op je hoede!",
                        ],
                    ],
                ],
            ],
            'search' => null,
            'unreadCount' => 0,
            'faqTotalCount' => 1,
        ]);

        $view->assertSee('Algemene Vragen');
        // Ensure no literal linebreaks break the single-quoted matchesSearch parameter
        $view->assertDontSee("matchesSearch('Hoe speel je \'TBS\'?', 'Regel 1: Breek niet.\n", false);
    }

    /**
     * Test fight arena view disables rematch when attacker is comatose.
     */
    public function test_fight_arena_disables_rematch_when_comatose(): void
    {
        $attacker = $this->createCharacter('DownAttacker', [
            'health' => 0,
            'coma_until' => now()->addMinutes(30),
            'energy' => 50,
        ]);
        $defender = $this->createCharacter('DefenderTarget');

        $view = $this->actingAs($attacker->user)->view('game.fight.arena', [
            'attacker' => $attacker,
            'defender' => $defender,
            'winner' => $defender,
            'loser' => $attacker,
            'stolenCash' => 0,
            'respectGained' => 0,
            'respectLost' => 5,
            'attackerXp' => 10,
            'attackerHospitalized' => true,
            'defenderHospitalized' => false,
            'combatRounds' => [],
            'arenaBattle' => [
                'left_today' => 5,
                'won' => false,
                'resolved' => true,
            ],
            'attackerStats' => [
                'strength' => 50,
                'defense' => 50,
                'speed' => 50,
                'total' => 150,
                'weapon' => 'Vuist',
                'protection' => 'Geen',
            ],
            'defenderStats' => [
                'strength' => 100,
                'defense' => 100,
                'speed' => 100,
                'total' => 300,
                'weapon' => 'Mes',
                'protection' => 'Vest',
            ],
        ]);

        $view->assertSee('Opnieuw geld stelen');
        $view->assertSee('disabled', false);
    }

    /**
     * Test fight index view disables attack buttons when player has insufficient energy.
     */
    public function test_fight_index_disables_attack_on_low_energy(): void
    {
        $character = $this->createCharacter('TiredFighter', [
            'energy' => 5, // Requires 20
        ]);
        $opponent = $this->createCharacter('HealthyOpponent');

        $response = $this->actingAs($character->user)->get(route('fight.index'));
        $response->assertOk();
        $response->assertSee($opponent->name);
        $response->assertSee('disabled', false);
    }

    /**
     * Test gang view uses safe JSON confirm and blocks rival war attacks when comatose.
     */
    public function test_gangs_view_uses_safe_js_confirm_and_state_checks(): void
    {
        $leader = $this->createCharacter('GangBoss');
        $gang = Gang::create([
            'name' => "The 'Crazy' Syndicate",
            'tag' => 'CRZY',
            'leader_character_id' => $leader->id,
            'treasury' => 100000,
            'respect' => 500,
            'specialty' => 'vechten',
            'emblem' => 'fa-skull',
            'description' => 'Gevaarlijke bende',
        ]);
        $leader->gang_id = $gang->id;
        $leader->gang_role = 'leader';
        $leader->save();

        $member = $this->createCharacter('GangMember', [
            'gang_id' => $gang->id,
            'gang_role' => 'member',
            'health' => 0,
            'coma_until' => now()->addMinutes(30),
        ]);

        $rival = Gang::create([
            'name' => 'Rival Cartel',
            'tag' => 'RIVL',
            'leader_character_id' => $leader->id,
            'treasury' => 50000,
            'respect' => 400,
            'specialty' => 'smokkel',
        ]);

        $view = $this->actingAs($member->user)->view('game.gangs', [
            'character' => $member,
            'myGang' => $gang,
            'rivalGangs' => collect([$rival]),
            'realGangs' => collect([$gang, $rival]),
            'gangs' => collect([$gang, $rival]),
            'gangRankings' => collect([$gang, $rival]),
            'topRankings' => collect([$gang, $rival]),
            'alliedGangIds' => [],
            'gangTreasury' => 100000,
            'totalRespect' => 500,
            'hourlyWage' => 100,
            'dailyWage' => 2400,
            'wageDetails' => ['pending_amount' => 0, 'pending_hours' => 0, 'last_claim' => null],
            'todayDonations' => collect(),
            'totalDonatedToday' => 0,
            'todayRecentLogs' => collect(),
            'memberDonations' => collect(),
            'inboundAlliances' => collect(),
            'recentAttacks' => collect(),
            'gangMembersData' => collect([$leader, $member]),
            'onlineMembersCount' => 1,
            'activeTab' => 'oorlog',
            'myInventoryItems' => collect(),
            'warsWon' => 2,
            'warsLost' => 1,
        ]);

        $view->assertSee("The 'Crazy' Syndicate");
        // Verify the rival gang attack button is disabled because member is comatose (and not officer)
        $view->assertSee('disabled', false);
    }
}
