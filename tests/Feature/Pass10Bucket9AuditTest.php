<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\CharacterItem;
use App\Models\CharacterVehicle;
use App\Models\Gang;
use App\Models\Item;
use App\Models\User;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Pass10Bucket9AuditTest extends TestCase
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
            'name' => 'AuditPatient9',
            'cash' => 250000,
            'bank' => 500000,
            'stars' => 100,
            'energy' => 100,
            'max_energy' => 100,
            'health' => 100,
            'max_health' => 100,
            'level' => 10,
            'respect' => 1500,
        ]);
    }

    public function test_dashboard_view_renders_with_guards_and_null_safe_attributes(): void
    {
        $view = view('game.dashboard', [
            'character' => $this->character,
            'rankProgress' => [
                'current_level' => 10,
                'next_level' => 11,
                'current_xp' => 1500,
                'next_level_xp' => 3000,
                'progress_percentage' => 50,
                'xp_needed' => 1500,
            ],
            'stats' => [
                'strength' => 50,
                'defense' => 50,
                'stamina' => 50,
                'total' => 150,
            ],
            'unreadMessagesCount' => 2,
            'activeCooldowns' => [],
            'activeLotteryRound' => null,
            'dailyLoginStreak' => 3,
            'hasClaimedLoginBonusToday' => false,
            'todaysLoginBonusReward' => ['cash' => 1000, 'stars' => 5],
            'weather' => null,
            'currentWeather' => null,
            'currentMarketPrices' => null,
            'nextLotteryDraw' => null,
            'totalLotteryPool' => 0,
            'recentActivities' => collect([
                (object) [
                    'id' => 1,
                    'type' => 'crime',
                    'icon' => 'fa-mask',
                    'title' => 'Misdaad gepleegd',
                    'description' => 'Je hebt met succes een kluis gekraakt.',
                    'created_at' => null,
                ],
            ]),
            'tunnelProgress' => [
                'can_dig' => true,
                'energy_cost' => 15,
                'current_stage_name' => null,
            ],
            'hospitalCost' => 500,
            'prisonBailCost' => 1500,
            'rookieData' => null, // null-safe check
            'isLoginBonusActive' => false,
        ])->render();

        $this->assertStringContainsString('AuditPatient9', $view);
        $this->assertStringContainsString('Kliniek Dashboard', $view);

        // Test with comatose character
        $this->character->coma_until = Carbon::now()->addMinutes(15);
        $this->character->save();

        $comaView = view('game.dashboard', [
            'character' => $this->character,
            'rankProgress' => ['current_level' => 10, 'next_level' => 11, 'current_xp' => 1500, 'next_level_xp' => 3000, 'progress_percentage' => 50, 'xp_needed' => 1500],
            'stats' => ['strength' => 50, 'defense' => 50, 'stamina' => 50, 'total' => 150],
            'unreadMessagesCount' => 0,
            'activeCooldowns' => [],
            'dailyLoginStreak' => 1,
            'hasClaimedLoginBonusToday' => true,
            'todaysLoginBonusReward' => null,
            'weather' => null,
            'currentWeather' => null,
            'currentMarketPrices' => null,
            'nextLotteryDraw' => null,
            'totalLotteryPool' => 0,
            'recentActivities' => collect(),
            'tunnelProgress' => ['can_dig' => false, 'energy_cost' => 15],
            'hospitalCost' => 500,
            'prisonBailCost' => 1500,
            'rookieData' => null,
            'isLoginBonusActive' => false,
        ])->render();

        $this->assertStringContainsString('Ziekenboeg', $comaView);
    }

    public function test_daycare_view_renders_with_alpine_timer_and_guards(): void
    {
        $jobs = [
            [
                'id' => 1,
                'name' => 'Wassalon Sorteren',
                'description' => 'Sorteer de witte lakens en dwangbuizen.',
                'icon' => 'fa-shirt',
                'energy' => 10,
                'min_cash' => 50,
                'max_cash' => 150,
                'xp' => 25,
                'stat_bonus' => '+1 Conditie',
            ],
        ];

        // Normal state with active cooldown
        $view = view('game.daycare.index', [
            'character' => $this->character,
            'jobs' => $jobs,
            'activeCooldown' => 45,
            'daycareSkipCost' => 10,
        ])->render();

        $this->assertStringContainsString('Dagbesteding & Werkplaats', $view);
        $this->assertStringContainsString('cd: 45', $view);
        $this->assertStringContainsString('Koffiepauze Actief', $view);

        // Imprisoned state
        $this->character->prison_until = Carbon::now()->addMinutes(10);
        $this->character->is_in_prison = true;
        $this->character->save();

        $jailView = view('game.daycare.index', [
            'character' => $this->character,
            'jobs' => $jobs,
            'activeCooldown' => 0,
            'daycareSkipCost' => 10,
        ])->render();

        $this->assertStringContainsString('Je zit in de isoleercel en kunt op dit moment geen dagbesteding verrichten', $jailView);
        $this->assertStringContainsString('disabled', $jailView);

        // Comatose state
        $this->character->prison_until = null;
        $this->character->is_in_prison = false;
        $this->character->coma_until = Carbon::now()->addMinutes(10);
        $this->character->save();

        $comaView = view('game.daycare.index', [
            'character' => $this->character,
            'jobs' => $jobs,
            'activeCooldown' => 0,
            'daycareSkipCost' => 10,
        ])->render();

        $this->assertStringContainsString('Je ligt in het ziekenhuis/coma en kunt op dit moment geen dagbesteding verrichten', $comaView);
    }

    public function test_depot_view_renders_with_named_routes_and_guards(): void
    {
        $unredeemedCodes = collect([
            (object) [
                'id' => 1,
                'code' => 'SAFE999',
                'reward_cash' => 25000,
                'reward_stars' => 10,
            ],
        ]);

        $item = Item::create([
            'name' => 'Koffie Extra Sterk',
            'slug' => 'koffie-extra-sterk',
            'description' => 'Sterke zwarte koffie voor nachtwakers.',
            'category' => 'consumable',
            'rarity' => 'common',
            'price_kc' => 100,
            'energy_restore' => 20,
            'health_restore' => 10,
        ]);

        $charItem = CharacterItem::create([
            'character_id' => $this->character->id,
            'item_id' => $item->id,
            'quantity' => 5,
        ]);

        $vehicle = Vehicle::create([
            'name' => 'Smokkel Quad',
            'slug' => 'smokkel-quad',
            'type' => 'quad',
            'brand' => 'Yamaha',
            'price_cash' => 5000,
            'speed_pct' => 80,
            'reliability_pct' => 90,
            'min_level' => 1,
            'description' => 'Ideaal over hek.',
        ]);

        $charVehicle = CharacterVehicle::create([
            'character_id' => $this->character->id,
            'vehicle_id' => $vehicle->id,
            'current_condition' => 80,
            'is_active' => false,
        ]);

        $view = view('game.depot.index', [
            'character' => $this->character,
            'garageUsed' => 1,
            'garageCapacity' => 5,
            'garagePercent' => 20,
            'storageUsed' => 5,
            'storageCapacity' => 50,
            'storagePercent' => 10,
            'nextTierLevel' => 15,
            'ownedVehicles' => collect([$charVehicle]),
            'inventoryItems' => collect([$charItem]),
            'eventItems' => collect(),
            'pumpkinSeeds' => 0,
            'unredeemedCodes' => $unredeemedCodes,
            'activeTab' => 'garage',
        ])->render();

        $this->assertStringContainsString('Depot & Centrale Opslag', $view);
        $this->assertStringContainsString('kluis-kraak?code=SAFE999', $view);
        $this->assertStringContainsString(route('depot.sell-item', ':id'), $view);

        // Test Comatose guards
        $this->character->coma_until = Carbon::now()->addMinutes(20);
        $this->character->save();

        $comaView = view('game.depot.index', [
            'character' => $this->character,
            'garageUsed' => 1,
            'garageCapacity' => 5,
            'garagePercent' => 20,
            'storageUsed' => 5,
            'storageCapacity' => 50,
            'storagePercent' => 10,
            'nextTierLevel' => 15,
            'ownedVehicles' => collect([$charVehicle]),
            'inventoryItems' => collect([$charItem]),
            'eventItems' => collect(),
            'pumpkinSeeds' => 0,
            'unredeemedCodes' => $unredeemedCodes,
            'activeTab' => 'storage',
        ])->render();

        $this->assertStringContainsString('Je ligt in het ziekenhuis/coma', $comaView);
        $this->assertStringContainsString('disabled', $comaView);
    }

    public function test_droppings_view_renders_with_dynamic_alpine_cooldowns_and_guards(): void
    {
        $recentDrops = collect([
            (object) [
                'id' => 1,
                'type' => 'tennisball',
                'caught' => true,
                'loot_summary' => '€500 contant + 1x Ritalin',
                'created_at' => null, // Test null-safety of created_at?->diffForHumans()
            ],
        ]);

        $view = view('game.droppings.index', [
            'character' => $this->character,
            'tennisCooldown' => 120,
            'droneCooldown' => 300,
            'totalIntercepted' => 5,
            'totalCashMade' => 2500,
            'recentDroppings' => $recentDrops,
        ])->render();

        $this->assertStringContainsString('Tennisballen & Nachtelijke Drones', $view);
        $this->assertStringContainsString('cd: 120', $view);
        $this->assertStringContainsString('cd: 300', $view);

        // Imprisoned test
        $this->character->prison_until = Carbon::now()->addMinutes(10);
        $this->character->is_in_prison = true;
        $this->character->save();

        $jailView = view('game.droppings.index', [
            'character' => $this->character,
            'tennisCooldown' => 0,
            'droneCooldown' => 0,
            'totalIntercepted' => 5,
            'totalCashMade' => 2500,
            'recentDroppings' => $recentDrops,
        ])->render();

        $this->assertStringContainsString('Je zit in de isoleercel en kunt op dit moment geen droppings opvangen of bestellen', $jailView);
    }

    public function test_extort_view_renders_with_guards_and_alpine_timer(): void
    {
        $targets = [
            [
                'id' => 1,
                'name' => 'Stagiair Verpleegkundige',
                'desc' => 'Nieuw en gemakkelijk te intimideren.',
                'icon' => 'fa-user-nurse',
                'min' => 100,
                'max' => 300,
                'energy' => 15,
                'rate' => 85,
                'cooldown' => 180,
            ],
        ];

        $view = view('game.extort.index', [
            'character' => $this->character,
            'targets' => $targets,
            'extortSkipCost' => 10,
        ])->render();

        $this->assertStringContainsString('Afpersen &amp; Chantage', $view);
        $this->assertStringContainsString('Stagiair Verpleegkundige', $view);

        // Imprisoned check
        $this->character->prison_until = Carbon::now()->addMinutes(15);
        $this->character->is_in_prison = true;
        $this->character->save();

        $jailView = view('game.extort.index', [
            'character' => $this->character,
            'targets' => $targets,
            'extortSkipCost' => 10,
        ])->render();

        $this->assertStringContainsString('Je zit in de isoleercel en kunt op dit moment niemand afpersen', $jailView);
    }

    public function test_faq_view_renders_with_unicode_category_trimming_and_js_escaping(): void
    {
        $categories = [
            'algemeen' => [
                'title' => '📋 Algemeen & Regels',
                'icon' => 'fa-book',
                'questions' => [
                    [
                        'q' => 'Wat is TBS Kliniek?',
                        'a' => 'Een interactief misdaadspel in een forensisch psychiatrische kliniek.',
                    ],
                ],
            ],
        ];

        $view = view('game.faq', [
            'character' => $this->character,
            'categories' => $categories,
        ])->render();

        $this->assertStringContainsString('Algemeen &amp; Regels', $view);
        $this->assertStringContainsString("selectCategory('algemeen')", $view);
    }

    public function test_fight_arena_view_renders_with_null_safe_left_today(): void
    {
        $defender = Character::factory()->create([
            'name' => 'DefenderPatient',
            'cash' => 10000,
            'respect' => 800,
        ]);

        $view = view('game.fight.arena', [
            'character' => $this->character,
            'defender' => $defender,
            'arenaBattle' => [
                'resolved' => false,
                'won' => false,
                // Notice 'left_today' is omitted to test null-safe fallback
                'max_today' => 100,
            ],
        ])->render();

        $this->assertStringContainsString('Aanvallen over vandaag: 0 / 100', $view);
        $this->assertStringContainsString('DefenderPatient', $view);
    }

    public function test_fight_index_view_escapes_opponent_names_safely_and_enforces_guards(): void
    {
        $opponent = Character::factory()->create([
            'name' => 'Patrick "Slick" O\'Connor',
            'cash' => 25000,
            'respect' => 950,
        ]);

        $view = view('game.fight.index', [
            'character' => $this->character,
            'opponents' => collect([$opponent]),
            'sort' => 'respect',
        ])->render();

        // The opponent name with single quotes and double quotes must be safely escaped via @js
        $this->assertStringContainsString('Patrick \u0022Slick\u0022 O\u0027Connor', $view);

        // Imprisoned check
        $this->character->prison_until = Carbon::now()->addMinutes(15);
        $this->character->is_in_prison = true;
        $this->character->save();

        $jailView = view('game.fight.index', [
            'character' => $this->character,
            'opponents' => collect([$opponent]),
            'sort' => 'respect',
        ])->render();

        $this->assertStringContainsString('Je zit in de isoleercel en kunt op dit moment niemand op de luchtplaats beroven of aanvallen', $jailView);
    }

    public function test_friends_view_uses_named_routes_and_enforces_guards(): void
    {
        $friend = Character::factory()->create([
            'name' => 'KamerMaatje',
            'respect' => 500,
        ]);

        $view = view('game.friends.index', [
            'character' => $this->character,
            'bff' => null,
            'friends' => collect([$friend]),
            'incomingRequests' => collect(),
            'outgoingRequests' => collect(),
            'pendingRequests' => collect(),
            'sentRequests' => collect(),
            'canMassMessage' => true,
            'massMessageCooldown' => 0,
            'numA' => 4,
            'numB' => 7,
            'availablePatients' => collect([$friend]),
            'topRankings' => collect([$friend]),
        ])->render();

        $this->assertStringContainsString(route('friends.respect', ':id'), $view);

        // Comatose check
        $this->character->coma_until = Carbon::now()->addMinutes(15);
        $this->character->save();

        $comaView = view('game.friends.index', [
            'character' => $this->character,
            'bff' => null,
            'friends' => collect([$friend]),
            'incomingRequests' => collect(),
            'outgoingRequests' => collect(),
            'pendingRequests' => collect(),
            'sentRequests' => collect(),
            'canMassMessage' => true,
            'massMessageCooldown' => 0,
            'numA' => 4,
            'numB' => 7,
            'availablePatients' => null, // tests null-safe collection fallback ($availablePatients ?? collect())
            'topRankings' => collect([$friend]),
        ])->render();

        $this->assertStringContainsString('Je ligt in het ziekenhuis/coma en kunt op dit moment geen respect uitdelen of massa-berichten versturen', $comaView);
    }

    public function test_gang_show_view_enforces_imprisoned_and_comatose_guards(): void
    {
        $leader = Character::factory()->create(['name' => 'GangBoss']);
        $gang = Gang::create([
            'name' => 'Noorderhaven Syndicaat',
            'tag' => 'NHS',
            'leader_character_id' => $leader->id,
            'specialty' => 'Agressie',
            'min_respect' => 100,
            'is_open' => true,
        ]);

        $view = view('game.gang_show', [
            'character' => $this->character,
            'gang' => $gang,
            'isMyGang' => false,
            'hasPendingRequest' => false,
            'onlineMembersCount' => 1,
            'totalRespect' => 2500,
            'gangMembersData' => [],
        ])->render();

        $this->assertStringContainsString('Noorderhaven Syndicaat', $view);
        $this->assertStringContainsString('Lidmaatschap Aanvragen', $view);

        // Imprisoned check
        $this->character->prison_until = Carbon::now()->addMinutes(15);
        $this->character->is_in_prison = true;
        $this->character->save();

        $jailView = view('game.gang_show', [
            'character' => $this->character,
            'gang' => $gang,
            'isMyGang' => false,
            'hasPendingRequest' => false,
            'onlineMembersCount' => 1,
            'totalRespect' => 2500,
            'gangMembersData' => [],
        ])->render();

        $this->assertStringContainsString('Je zit in de isoleercel en kunt op dit moment geen bendelidmaatschap aanvragen', $jailView);
        $this->assertStringContainsString('disabled', $jailView);
    }
}
