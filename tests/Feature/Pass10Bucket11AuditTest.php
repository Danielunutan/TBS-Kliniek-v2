<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\LotteryRound;
use App\Models\MarketplaceListing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Tests\TestCase;

class Pass10Bucket11AuditTest extends TestCase
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
            'name' => 'AuditPatient11',
            'cash' => 250000,
            'bank' => 500000,
            'stars' => 100,
            'energy' => 100,
            'health' => 100,
            'level' => 10,
            'respect' => 1500,
        ]);
    }

    public function test_hacking_view_renders_with_js_stage_and_null_safe_logs(): void
    {
        $view = view('game.hacking.index', [
            'character' => $this->character,
            'canHack' => true,
            'currentStage' => '1',
            'secondsUntilMidnight' => 3600,
            'wheelSectors' => [
                ['short' => 'Cash', 'label' => '€50.000 Cash', 'emoji' => '💶', 'color' => '#10b981'],
            ],
            'recentLogs' => collect([
                (object) [
                    'character' => (object) ['name' => 'ZeroCool'],
                    'reward_label' => '€50.000 Cash',
                    'created_at' => null,
                    'vip_bonus_applied' => false,
                ],
            ]),
        ])->render();

        $this->assertStringContainsString('Cyber Inbraak & Mainframe Root', $view);
        $this->assertStringContainsString('"1"', $view);
        $this->assertStringContainsString('Zojuist', $view);
        $this->assertStringContainsString('Alles Vol Zetten', $view);
        $this->assertStringContainsString('setPortMax', $view);
        $this->assertStringContainsString('syncAllPorts', $view);
    }

    public function test_halloween_quests_and_shop_render_with_guards(): void
    {
        $questsView = view('game.halloween.quests', [
            'character' => $this->character,
            'timeRemaining' => ['formatted' => '3 dagen'],
            'dailyQuests' => [
                [
                    'key' => 'theft_5',
                    'title' => 'Zakkenrollen',
                    'desc' => 'Rol 5 zakken',
                    'category' => 'theft',
                    'icon' => 'fa-mask',
                    'seeds' => 10,
                    'current' => 2,
                    'target' => 5,
                    'percent' => 40,
                    'is_claimed' => false,
                    'can_claim' => false,
                    'cash' => 1000,
                    'xp' => 50,
                    'respect' => 10,
                    'tokens' => 1,
                ],
            ],
            'weeklyQuests' => [],
        ])->render();

        $this->assertStringContainsString('Herfstoogst Quests &amp; Mijlpalen', $questsView);

        $this->character->equipped_border = 'pumpkin_glow';
        $this->character->save();

        $shopView = view('game.halloween.shop', [
            'character' => $this->character,
            'timeRemaining' => ['formatted' => '3 dagen'],
            'borders' => [
                [
                    'key' => 'pumpkin_glow',
                    'name' => 'Pumpkin Glow',
                    'preview_class' => 'border-amber-400',
                    'description' => 'Griezelige gloed',
                    'price_seeds' => 50,
                    'badge' => 'Halloween',
                ],
            ],
            'collectibles' => [],
            'boosts' => [],
        ])->render();

        $this->assertStringContainsString('Herfstoogst Oogstwinkel', $shopView);
        $this->assertStringContainsString('Rand Uitschakelen', $shopView);
    }

    public function test_heists_view_renders_with_null_safe_log(): void
    {
        $view = view('game.heists.index', [
            'character' => $this->character,
            'activeVehicle' => null,
            'myGang' => null,
            'soloHeists' => collect(),
            'gangHeists' => collect(),
            'recentLogs' => collect([
                (object) [
                    'character' => (object) ['name' => 'BankRobber'],
                    'gang' => null,
                    'heist' => (object) ['name' => 'Juwelier'],
                    'created_at' => null,
                    'vehicle' => null,
                    'success' => true,
                    'cash_earned' => 50000,
                    'gang_cash_earned' => 0,
                    'respect_earned' => 25,
                ],
            ]),
        ])->render();

        $this->assertStringContainsString('Het Heist Planningsbord', $view);
        $this->assertStringContainsString('Zojuist', $view);
    }

    public function test_highlow_view_handles_default_card_safely(): void
    {
        $view = view('game.highlow.index', [
            'character' => $this->character,
            'currentCard' => null,
        ])->render();

        $this->assertStringContainsString('Hoger of Lager', $view);
        $this->assertStringContainsString('7', $view);
    }

    public function test_hitman_view_renders_with_null_safe_blood_feud(): void
    {
        $view = view('game.hitman\index', [
            'character' => $this->character,
            'targets' => collect(),
            'gradeInfo' => [
                'grade' => 10,
                'title' => 'Gevorderde Schutter',
                'phase' => 'Scherpschutter',
                'min_bullets' => 20,
                'armor_pierce' => 5,
            ],
            'locations' => [
                'wing_a' => ['name' => 'Paviljoen 1 (Gesloten)'],
            ],
            'travelCost' => 3500,
            'bloodFeuds' => collect([
                (object) [
                    'attacker' => (object) ['id' => 999, 'name' => 'Nemesis', 'level' => 15],
                    'victim' => null,
                    'expires_at' => null,
                ],
            ]),
            'enrichedIntelOps' => [],
            'gradesCatalog' => [],
            'intelCatalog' => [],
            'vestCatalog' => [],
            'ammoCatalog' => [],
            'contracts' => [],
        ])->render();

        $this->assertStringContainsString('Liquidatie &amp; Wapenarsenaal', $view);
        $this->assertStringContainsString('Verlopen', $view);
    }

    public function test_hustle_view_renders_with_safe_stat_bonus(): void
    {
        $view = view('game.hustle.index', [
            'character' => $this->character,
            'hustles' => [
                1 => [
                    'id' => 1,
                    'name' => 'Sigaretten Doorverkopen',
                    'tier_name' => 'Basis',
                    'description' => 'Verkoop peuken',
                    'icon' => 'fa-smoking',
                    'rate' => 70,
                    'effective_rate' => 85,
                    'cash_min' => 500,
                    'cash_max' => 1500,
                    'xp' => 10,
                    'respect' => 5,
                    'cooldown' => 60,
                    'cost' => 5,
                    'risk_level' => 'low',
                    'risk_label' => 'Laag Risico',
                ],
            ],
            'statBonus' => null,
        ])->render();

        $this->assertStringContainsString('Hosselen &amp; Klussen', $view);
        $this->assertStringContainsString('+0% Slagingskans Bonus', $view);
    }

    public function test_infirmary_view_handles_owned_pills_with_null_item(): void
    {
        $view = view('game.infirmary.index', [
            'character' => $this->character,
            'takeCooldown' => 0,
            'palmCooldown' => 0,
            'palmSuccessRate' => 65,
            'palmCooldownSeconds' => 180,
            'pills' => collect(),
            'ownedPills' => collect([
                (object) [
                    'item' => null,
                    'quantity' => 2,
                ],
            ]),
        ])->render();

        $this->assertStringContainsString('Ziekenboeg & Medicijnpost', $view);
        $this->assertStringContainsString('Onbekend medicijn', $view);
    }

    public function test_isolation_view_safely_extracts_vest_name_with_data_get(): void
    {
        $this->character->protection_until = now()->addHours(2);
        $this->character->save();

        $view = view('game.isolation.index', [
            'character' => $this->character,
            'isProtected' => true,
            'remainingSeconds' => 7200,
            'remainingMinutes' => 120,
            'remainingHours' => 2,
            'hasCashProtection' => false,
            'cashProtectionSeconds' => 0,
            'hasVest' => true,
            'vestSeconds' => 3600,
            'packages' => [
                ['label' => '1 Uur', 'hours' => 1, 'cost_cash' => 50000, 'cost_stars' => 25],
            ],
        ])->render();

        $this->assertStringContainsString('Vrijwillige Isolatie', $view);
        $this->assertStringContainsString('Kogelwerend Backfire-Vest', $view);
    }

    public function test_lockers_view_disables_skip_button_when_comatose_or_imprisoned(): void
    {
        $this->character->is_in_prison = true;
        $this->character->save();

        $view = view('game.lockers.index', [
            'character' => $this->character,
            'lockers' => [],
        ])->render();

        $this->assertStringContainsString('Kluisjes Kraken', $view);
        $this->assertStringContainsString('disabled', $view);
    }

    public function test_lottery_view_renders_cleanly_with_null_safe_winner(): void
    {
        $round = LotteryRound::create([
            'round_number' => 1,
            'jackpot' => 1000000,
            'tax_collected' => 200000,
            'total_tickets_sold' => 50,
            'draws_at' => now()->addDays(5),
            'is_drawn' => false,
        ]);

        $view = view('game.lottery.index', [
            'character' => $this->character,
            'round' => $round,
            'userTickets' => collect(),
            'userTicketsCount' => 0,
            'winChance' => 0,
            'pastRounds' => collect([
                (object) [
                    'round_number' => 1,
                    'draws_at' => now()->subDay(),
                    'winner' => null,
                    'winner_payout' => 500000,
                ],
            ]),
            'cashPricePerTicket' => 10000,
            'starsPricePerTicket' => 15,
            'isLastQuarter' => false,
        ])->render();

        $this->assertStringContainsString('Grijp de', $view);
        $this->assertStringContainsString('Geen winnaar', $view);
    }

    public function test_marketplace_disables_create_and_cancel_when_imprisoned(): void
    {
        $this->character->is_in_prison = true;
        $this->character->save();

        $item = new MarketplaceListing([
            'seller_id' => $this->character->id,
            'item_type' => 'depot_item',
            'title' => 'Test Item',
            'amount' => 1,
            'price' => 5000,
            'currency' => 'cash',
        ]);
        $item->id = 101;
        $item->setRelation('seller', $this->character);

        $paginator = new LengthAwarePaginator(collect([$item]), 1, 20, 1);

        $view = view('game.marketplace.index', [
            'character' => $this->character,
            'listings' => $paginator,
            'filterType' => 'all',
            'inventoryItems' => collect([
                (object) [
                    'id' => 1,
                    'quantity' => 1,
                    'item' => null,
                ],
            ]),
            'ownedVehicles' => collect([
                (object) [
                    'id' => 1,
                    'current_condition' => 100,
                    'effective_speed' => 120,
                    'is_active' => false,
                    'vehicle' => null,
                ],
            ]),
            'gangProperties' => collect(),
            'myVaultCodes' => collect(),
        ])->render();

        $this->assertStringContainsString('De Kliniek Marktplaats', $view);
        $this->assertStringContainsString('Onbekend', $view);
        $this->assertStringContainsString('Onbekend voertuig', $view);
    }

    public function test_members_and_memory_views_render_properly(): void
    {
        $membersView = view('game.members.index', [
            'character' => $this->character,
            'initialFilter' => 'all',
            'initialSearch' => '',
            'totalCount' => 1,
            'onlineCount' => 1,
            'gangCount' => 0,
            'soloCount' => 1,
            'prisonCount' => 0,
            'membersData' => [
                [
                    'id' => $this->character->id,
                    'name' => $this->character->name,
                    'title' => 'Patiënt',
                    'avatar_url' => '/avatar.png',
                    'profile_url' => '/speler/'.$this->character->name,
                    'message_url' => '/berichten/nieuw',
                    'level' => 10,
                    'respect' => 1500,
                    'respect_formatted' => '1.500',
                    'is_online' => true,
                    'is_vip' => false,
                    'is_self' => true,
                    'is_in_prison' => false,
                    'gang_id' => null,
                    'gang_name' => null,
                    'gang_tag' => null,
                    'role_label' => null,
                    'last_active_human' => 'Zojuist',
                    'online_label' => 'Online',
                ],
            ],
        ])->render();

        $this->assertStringContainsString('Ledenlijst &amp; Online Patiënten', $membersView);

        $memoryStat = (object) [
            'games_won' => 5,
            'games_played' => 10,
            'best_time_seconds' => 42,
        ];

        $memoryView = view('game.memory.index', [
            'character' => $this->character,
            'deck' => [],
            'cooldownRemaining' => 0,
            'stat' => $memoryStat,
        ])->render();

        $this->assertStringContainsString('TBS Kliniek Memory: Psychose Test', $memoryView);
        $this->assertStringContainsString('canPlay: true', $memoryView);
    }
}
