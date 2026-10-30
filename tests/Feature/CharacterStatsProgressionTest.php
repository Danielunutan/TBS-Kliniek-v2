<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\CharacterItem;
use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CharacterStatsProgressionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_shop_purchase_of_equipment_adds_to_inventory_not_base_stats(): void
    {
        $user = User::factory()->create();
        $character = Character::create([
            'user_id' => $user->id,
            'name' => 'TestVechter',
            'level' => 5,
            'strength' => 20,
            'defense' => 15,
            'stamina' => 10,
            'intelligence' => 10,
            'charisma' => 10,
            'discipline' => 10,
            'health' => 100,
            'max_health' => 100,
            'energy' => 100,
            'max_energy' => 100,
            'cash' => 50000,
            'bank' => 0,
        ]);

        $boks = Item::where('slug', 'boksbeugel')->first();
        if (! $boks) {
            $boks = Item::create([
                'name' => 'Boksbeugel Test',
                'slug' => 'boksbeugel',
                'description' => 'Test boksbeugel',
                'category' => 'equipment',
                'price_kc' => 500,
                'strength_bonus' => 5,
                'is_buyable' => true,
            ]);
        }

        $this->actingAs($user)
            ->post(route('shop.buy', $boks), [
                'quantity' => 1,
            ])
            ->assertSessionHas('success');

        $character->refresh();

        // Base strength MUST stay at 20, not inflated by direct stat boost
        $this->assertEquals(20, $character->strength);

        // Inventory item must exist with quantity 1 (unique equipment)
        $inv = CharacterItem::where('character_id', $character->id)
            ->where('item_id', $boks->id)
            ->first();
        $this->assertNotNull($inv);
        $this->assertEquals(1, $inv->quantity);

        // Attempting to buy again must fail because equipment is unique
        $this->actingAs($user)
            ->post(route('shop.buy', $boks), ['quantity' => 1])
            ->assertSessionHas('error');

        // Effective strength includes single piece bonus (+5) = 25
        $this->assertEquals(25, $character->getEffectiveStrength());
    }

    public function test_level_stat_cap_formula(): void
    {
        $character = new Character([
            'level' => 10,
            'strength' => 20,
        ]);

        // Max stat for level 10: 100 + 10 * 50 = 600
        $this->assertEquals(600, $character->getMaxStatForLevel());
    }

    public function test_normalize_character_state_and_migration_fixes_exceeded_stats_timers_and_equipment(): void
    {
        $user = User::factory()->create();
        $character = Character::create([
            'user_id' => $user->id,
            'name' => 'HerstelTest',
            'level' => 1,
            'xp' => 340, // 10 XP away from level 2 (350 XP required)
            'total_xp' => 100,
            'strength' => 152, // 2 points above level 1 cap (150) -> 90 XP -> levels up to level 2!
            'defense' => 5,    // Below minimum of 10 -> normalized to 10
            'stamina' => 150,
            'intelligence' => 150,
            'charisma' => 150,
            'discipline' => 155, // 5 points above level 1 cap (150) -> 225 XP
            'health' => 180,
            'max_health' => 100,
            'energy' => 250,
            'max_energy' => 200,
            'is_vip' => true,
            'vip_expires_at' => now()->subDay(), // Expired VIP!
            'protection_until' => now()->subHour(), // Expired protection!
            'cash_protection_until' => now()->subMinutes(10), // Expired cash protection!
            'vest_until' => now()->subMinutes(5), // Expired vest!
            'is_in_prison' => true,
            'prison_until' => now()->subMinute(), // Expired prison!
            'daily_spins_today' => 5,
            'last_daily_spin_at' => now()->subDay(),
            'perks' => ['cold_blooded_pct' => 145],
            'cash' => 1000,
            'bank' => 500,
        ]);

        $boks = Item::where('slug', 'boksbeugel')->first();
        $this->assertNotNull($boks);

        // Give duplicate equipment (quantity 3 instead of 1)
        CharacterItem::create([
            'character_id' => $character->id,
            'item_id' => $boks->id,
            'quantity' => 3,
        ]);

        // Run the normalization migration
        $migration = require database_path('migrations/2026_10_30_224500_normalize_all_exceeded_stats_items_and_timers.php');
        $migration->up();

        $character->refresh();

        // 7 overflow points * 45 XP = 315 XP -> 340 + 315 = 655 XP -> Level 2 (350 XP subtracted -> 305 XP left)
        $this->assertEquals(2, $character->level);
        $this->assertEquals(305, $character->xp);
        $this->assertGreaterThanOrEqual($character->xp, $character->total_xp);

        // Exceeded stats were clamped to 150 (not double-counted as both stat + XP), and defense raised to minimum 10
        $this->assertEquals(150, $character->strength);
        $this->assertEquals(10, $character->defense);
        $this->assertEquals(150, $character->discipline);

        // Expired VIP & timers cleaned up, max_energy restored to 100, health & energy clamped
        $this->assertFalse($character->is_vip);
        $this->assertEquals(100, $character->max_energy);
        $this->assertEquals(100, $character->energy);
        $this->assertEquals(100, $character->health);
        $this->assertNull($character->protection_until);
        $this->assertNull($character->cash_protection_until);
        $this->assertNull($character->vest_until);
        $this->assertFalse($character->is_in_prison);
        $this->assertNull($character->prison_until);
        $this->assertEquals(0, $character->daily_spins_today);
        $this->assertEquals(100, $character->perks['cold_blooded_pct']);

        // Duplicate equipment capped at 1 and 2 extra copies refunded to bank at depot sell price
        $inv = CharacterItem::where('character_id', $character->id)->where('item_id', $boks->id)->first();
        $this->assertNotNull($inv);
        $this->assertEquals(1, $inv->quantity);
        $expectedRefund = 2 * $boks->fresh()->getDepotSellPrice();
        $this->assertEquals(500 + $expectedRefund, $character->bank);
    }

    public function test_admin_can_run_normalization_migration_directly_from_admin_dashboard(): void
    {
        $adminUser = User::factory()->create([
            'role' => 'admin',
            'is_staff' => true,
        ]);
        Character::create([
            'user_id' => $adminUser->id,
            'name' => 'AdminBaas',
            'level' => 5,
            'strength' => 180,
            'defense' => 100,
            'stamina' => 100,
            'intelligence' => 100,
            'charisma' => 100,
            'discipline' => 100,
            'health' => 100,
            'max_health' => 100,
            'energy' => 100,
            'max_energy' => 100,
            'cash' => 1000,
            'bank' => 0,
        ]);

        $playerUser = User::factory()->create();
        $exceededChar = Character::create([
            'user_id' => $playerUser->id,
            'name' => 'OverschredenSpeler',
            'level' => 1,
            'xp' => 0,
            'strength' => 160, // 10 above level 1 cap of 150
            'defense' => 150,
            'stamina' => 150,
            'intelligence' => 150,
            'charisma' => 150,
            'discipline' => 150,
            'health' => 100,
            'max_health' => 100,
            'energy' => 100,
            'max_energy' => 100,
            'cash' => 1000,
            'bank' => 0,
        ]);

        // Admin dashboard shows the exceeded player count and button
        $dashboardResponse = $this->actingAs($adminUser)->get(route('admin.dashboard'));
        $dashboardResponse->assertOk();
        $dashboardResponse->assertSee('Herstelmigratie Nu Uitvoeren');
        $dashboardResponse->assertSee('1 speler(s) boven level-plafond');

        // Run normalization from /admin
        $normalizeResponse = $this->actingAs($adminUser)->post(route('admin.system.normalize'));
        $normalizeResponse->assertSessionHas('success');
        $this->assertStringContainsString('1 spelers met overschreden stats hersteld', session('success'));

        $exceededChar->refresh();
        $this->assertEquals(150, $exceededChar->strength);
        $this->assertEquals(2, $exceededChar->level); // 10 * 45 = 450 XP -> Level 2!

        // Simulate a pending migration by deleting the normalization entry from the migrations table
        DB::table('migrations')
            ->where('migration', '2026_10_30_224500_normalize_all_exceeded_stats_items_and_timers')
            ->delete();

        $pendingDashboard = $this->actingAs($adminUser)->get(route('admin.dashboard'));
        $pendingDashboard->assertOk();
        $pendingDashboard->assertSee('1 openstaande migratie(s)');
        $pendingDashboard->assertSee('2026_10_30_224500_normalize_all_exceeded_stats_items_and_timers');

        // Run migrations via admin.system.migrate
        $migrateResponse = $this->actingAs($adminUser)->post(route('admin.system.migrate'));
        $migrateResponse->assertSessionHas('success');

        $this->assertDatabaseHas('migrations', [
            'migration' => '2026_10_30_224500_normalize_all_exceeded_stats_items_and_timers',
        ]);
    }
}
