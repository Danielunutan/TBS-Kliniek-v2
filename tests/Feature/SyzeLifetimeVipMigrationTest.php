<?php

namespace Tests\Feature;

use App\Http\Controllers\BuyMeACoffeeWebhookController;
use App\Models\BuyMeACoffeeOrder;
use App\Models\Character;
use App\Models\GameNotification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SyzeLifetimeVipMigrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_migration_grants_lifetime_vip_and_all_perks_to_syze_when_not_yet_granted(): void
    {
        $user = User::factory()->create([
            'email' => 'suzedirksen@hotmail.com',
            'name' => 'Syze',
        ]);

        $character = Character::factory()->create([
            'user_id' => $user->id,
            'name' => 'Syze',
            'is_vip' => false,
            'vip_expires_at' => null,
            'stars' => 10,
            'masks' => 5,
            'xxl_cases' => 0,
            'perks' => [],
        ]);

        $order = BuyMeACoffeeOrder::create([
            'id' => 12,
            'event' => 'membership.manual',
            'external_id' => 'manual_6ae997a123',
            'supporter_name' => 'Syze',
            'supporter_email' => 'suzedirksen@hotmail.com',
            'amount' => 125.00,
            'currency' => 'EUR',
            'membership_tier' => 'VIP LIFETIME',
            'character_id' => $character->id,
            'status' => 'manually_linked',
            'is_membership' => true,
            'membership_status' => 'active',
            'payload' => ['manual' => true],
        ]);

        // Run the recovery migration
        $migration = require database_path('migrations/2026_11_03_091915_change_characters_vip_expires_at_to_datetime_and_grant_lifetime_vip_to_syze.php');
        $migration->up();

        $character->refresh();
        $order->refresh();

        // 1. VIP status and expiration
        $this->assertTrue($character->is_vip);
        $this->assertTrue($character->isVip());
        $this->assertNotNull($character->vip_expires_at);
        $this->assertGreaterThan(2100, $character->vip_expires_at->year);

        // 2. Rewards granted (+1.500 stars, +30 masks, +10 xxl cases)
        $this->assertEquals(1510, $character->stars);
        $this->assertEquals(35, $character->masks);
        $this->assertEquals(10, $character->xxl_cases);

        // 3. Perks granted (+€50.000/u, custom name color, sparkle, supporter_tier 4)
        $this->assertEquals(4, $character->getSupporterTier());
        $this->assertEquals(50000, $character->perks['hourly_cash_bonus'] ?? 0);
        $this->assertTrue($character->perks['has_custom_name_color'] ?? false);
        $this->assertTrue($character->perks['has_sparkle_effect'] ?? false);
        $this->assertTrue($character->perks['is_donator'] ?? false);
        $this->assertTrue($character->canCustomizeNameColor());
        $this->assertTrue($character->canUseSparkleEffect());

        // 4. Order and notification verified
        $this->assertTrue($order->payload['rewards_granted'] ?? false);
        $this->assertNotNull($order->payload['rewards_granted_at'] ?? null);

        $notification = GameNotification::where('character_id', $character->id)
            ->where('type', 'vip_membership')
            ->first();
        $this->assertNotNull($notification);
        $this->assertStringContainsString('VIP LIFETIME', $notification->title);
    }

    public function test_migration_does_not_grant_double_rewards_if_run_again(): void
    {
        $user = User::factory()->create([
            'email' => 'suzedirksen@hotmail.com',
            'name' => 'Syze',
        ]);

        $character = Character::factory()->create([
            'user_id' => $user->id,
            'name' => 'Syze',
            'is_vip' => false,
            'vip_expires_at' => null,
            'stars' => 10,
            'masks' => 5,
            'xxl_cases' => 0,
            'perks' => [],
        ]);

        BuyMeACoffeeOrder::create([
            'id' => 12,
            'event' => 'membership.manual',
            'external_id' => 'manual_6ae997a123',
            'supporter_name' => 'Syze',
            'supporter_email' => 'suzedirksen@hotmail.com',
            'amount' => 125.00,
            'currency' => 'EUR',
            'membership_tier' => 'VIP LIFETIME',
            'character_id' => $character->id,
            'status' => 'manually_linked',
            'is_membership' => true,
            'membership_status' => 'active',
            'payload' => ['manual' => true],
        ]);

        $migration = require database_path('migrations/2026_11_03_091915_change_characters_vip_expires_at_to_datetime_and_grant_lifetime_vip_to_syze.php');

        // First run
        $migration->up();

        $character->refresh();
        $this->assertEquals(1510, $character->stars);
        $this->assertEquals(35, $character->masks);
        $this->assertEquals(10, $character->xxl_cases);

        // Second run (simulate rerun of migration)
        $migration->up();

        $character->refresh();
        // Values must remain identical (not doubled!)
        $this->assertEquals(1510, $character->stars);
        $this->assertEquals(35, $character->masks);
        $this->assertEquals(10, $character->xxl_cases);
    }

    public function test_admin_regrant_does_not_duplicate_lifetime_vip_rewards(): void
    {
        $adminUser = User::where('email', 'demo@tbskliniek.nl')->first();
        $adminUser->role = 'admin';
        $adminUser->is_staff = true;
        $adminUser->save();

        $user = User::factory()->create([
            'email' => 'suzedirksen@hotmail.com',
            'name' => 'Syze',
        ]);

        $character = Character::factory()->create([
            'user_id' => $user->id,
            'name' => 'Syze',
            'is_vip' => true,
            'vip_expires_at' => now()->addYears(99),
            'stars' => 1500,
            'masks' => 30,
            'xxl_cases' => 10,
            'perks' => [
                'supporter_tier' => 4,
                'has_custom_name_color' => true,
                'has_sparkle_effect' => true,
                'hourly_cash_bonus' => 50000,
            ],
        ]);

        $order = BuyMeACoffeeOrder::create([
            'id' => 12,
            'event' => 'membership.manual',
            'external_id' => 'manual_6ae997a123',
            'supporter_name' => 'Syze',
            'supporter_email' => 'suzedirksen@hotmail.com',
            'amount' => 125.00,
            'currency' => 'EUR',
            'membership_tier' => 'VIP LIFETIME',
            'character_id' => $character->id,
            'status' => 'manually_linked',
            'is_membership' => true,
            'membership_status' => 'active',
            'payload' => ['manual' => true],
        ]);

        // Attempt admin regrant
        $response = $this->actingAs($adminUser)->post(route('admin.buymeacoffee.regrant', $order));

        $response->assertRedirect();
        $response->assertSessionHas('info');

        $character->refresh();
        // Values must not increase
        $this->assertEquals(1500, $character->stars);
        $this->assertEquals(30, $character->masks);
        $this->assertEquals(10, $character->xxl_cases);
    }

    public function test_webhook_grant_membership_tier_does_not_duplicate_lifetime_vip_items(): void
    {
        $user = User::factory()->create([
            'email' => 'suzedirksen@hotmail.com',
            'name' => 'Syze',
        ]);

        $character = Character::factory()->create([
            'user_id' => $user->id,
            'name' => 'Syze',
            'is_vip' => true,
            'vip_expires_at' => now()->addYears(99),
            'stars' => 1500,
            'masks' => 30,
            'xxl_cases' => 10,
            'perks' => [
                'supporter_tier' => 4,
                'has_custom_name_color' => true,
                'has_sparkle_effect' => true,
                'hourly_cash_bonus' => 50000,
            ],
        ]);

        $webhookController = app(BuyMeACoffeeWebhookController::class);
        $webhookController->grantMembershipTier($character, 'VIP LIFETIME');

        $character->refresh();
        $this->assertEquals(1500, $character->stars);
        $this->assertEquals(30, $character->masks);
        $this->assertEquals(10, $character->xxl_cases);
    }
}
