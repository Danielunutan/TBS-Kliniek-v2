<?php

namespace Tests\Feature;

use App\Models\CharacterCooldown;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IsolationProtectionAndPackagePrisonTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $user = User::where('email', 'demo@tbskliniek.nl')->first();
        if ($user?->character) {
            $user->character->update([
                'health' => 100,
                'code_blue' => false,
                'prison_until' => null,
                'is_in_prison' => false,
                'prison_escaped_at' => null,
            ]);
        }
    }

    public function test_can_view_isolation_page_with_new_pricing(): void
    {
        $user = User::where('email', 'demo@tbskliniek.nl')->first();
        $this->actingAs($user);

        $response = $this->get(route('isolation.index'));
        $response->assertStatus(200);
        $response->assertSee('50.000');
        $response->assertSee('25 Sterren');
    }

    public function test_can_buy_isolation_with_cash_at_50k_per_hour(): void
    {
        $user = User::where('email', 'demo@tbskliniek.nl')->first();
        $char = $user->character;
        $char->update([
            'cash' => 150000,
            'bank' => 0,
            'protection_until' => null,
            'is_in_prison' => false,
        ]);

        $this->actingAs($user);

        // Buy 2 hours for 100k cash
        $response = $this->post(route('isolation.activate'), [
            'hours' => 2,
            'currency' => 'cash',
        ]);

        $response->assertSessionHas('success');
        $char->refresh();

        $this->assertEquals(50000, $char->cash);
        $this->assertNotNull($char->protection_until);
        $this->assertTrue(Carbon::now()->lt($char->protection_until));
        $this->assertGreaterThanOrEqual(115, Carbon::now()->diffInMinutes($char->protection_until));
    }

    public function test_can_buy_isolation_with_stars_at_25_per_hour(): void
    {
        $user = User::where('email', 'demo@tbskliniek.nl')->first();
        $char = $user->character;
        $char->update([
            'stars' => 75,
            'protection_until' => null,
            'is_in_prison' => false,
        ]);

        $this->actingAs($user);

        // Buy 3 hours for 75 stars
        $response = $this->post(route('isolation.activate'), [
            'hours' => 3,
            'currency' => 'stars',
        ]);

        $response->assertSessionHas('success');
        $char->refresh();

        $this->assertEquals(0, $char->stars);
        $this->assertNotNull($char->protection_until);
        $this->assertTrue(Carbon::now()->lt($char->protection_until));
        $this->assertGreaterThanOrEqual(175, Carbon::now()->diffInMinutes($char->protection_until));
    }

    public function test_cannot_buy_isolation_with_insufficient_funds(): void
    {
        $user = User::where('email', 'demo@tbskliniek.nl')->first();
        $char = $user->character;
        $char->update([
            'cash' => 20000,
            'bank' => 0,
            'stars' => 10,
            'protection_until' => null,
        ]);

        $this->actingAs($user);

        // Fail cash
        $res1 = $this->post(route('isolation.activate'), [
            'hours' => 1,
            'currency' => 'cash',
        ]);
        $res1->assertSessionHas('error');

        // Fail stars
        $res2 = $this->post(route('isolation.activate'), [
            'hours' => 1,
            'currency' => 'stars',
        ]);
        $res2->assertSessionHas('error');
    }

    public function test_failed_package_interception_sends_to_prison_with_isolation_message(): void
    {
        $user = User::where('email', 'demo@tbskliniek.nl')->first();
        $char = $user->character;
        $char->update([
            'is_in_prison' => false,
            'energy' => 100,
        ]);

        $this->actingAs($user);

        // Intercept package until a failure triggers (rate for package 4 is 25%, so fails frequently)
        $caught = false;
        for ($i = 0; $i < 5; $i++) {
            $char->update(['is_in_prison' => false, 'energy' => 100]);
            CharacterCooldown::where('character_id', $char->id)->delete();
            $this->actingAs($user->fresh());
            $response = $this->post(route('packages.intercept'), [
                'package_id' => 4,
            ]);
            $char->refresh();

            if ($char->isImprisoned()) {
                $caught = true;
                $response->assertRedirect(route('prison'));
                $response->assertSessionHas('error');
                $error = session('error');
                $this->assertStringContainsString('sec vast in isolatie', $error);
                break;
            }
        }

        $this->assertTrue($caught, 'Failed package interception should send player to prison');
    }

    public function test_character_protection_remaining_seconds_helpers_handle_active_and_expired_states(): void
    {
        $user = User::where('email', 'demo@tbskliniek.nl')->first();
        $char = $user->character;

        // Edge case: null and expired timestamps return 0 seconds
        $char->update([
            'protection_until' => Carbon::now()->subMinutes(10),
            'cash_protection_until' => null,
        ]);
        $char->refresh();

        $this->assertFalse($char->hasIsolationProtection());
        $this->assertSame(0, $char->getIsolationProtectionRemainingSeconds());
        $this->assertSame(0, $char->getCashProtectionRemainingSeconds());
        $this->assertSame(0, $char->getProtectionRemainingSeconds());
        $this->assertNull($char->getProtectionExpiresAt());

        // Active 4h isolation and 8h cash protection
        $char->update([
            'protection_until' => Carbon::now()->addHours(4),
            'cash_protection_until' => Carbon::now()->addHours(8),
        ]);
        $char->refresh();

        $this->assertTrue($char->hasIsolationProtection());
        $this->assertGreaterThan(14300, $char->getIsolationProtectionRemainingSeconds());
        $this->assertGreaterThan(28700, $char->getCashProtectionRemainingSeconds());
        $this->assertSame($char->getCashProtectionRemainingSeconds(), $char->getProtectionRemainingSeconds());
        $this->assertNotNull($char->getProtectionExpiresAt());
    }

    public function test_active_protection_displays_live_countdown_timer_on_isolation_shop_and_vip_pages(): void
    {
        $user = User::where('email', 'demo@tbskliniek.nl')->first();
        $char = $user->character;
        $char->update([
            'cash' => 500000,
            'protection_until' => Carbon::now()->addHours(8),
            'cash_protection_until' => Carbon::now()->addHours(24),
            'vest_until' => Carbon::now()->addHours(4),
            'vest_type' => 'tactical_3h',
            'is_in_prison' => false,
        ]);

        $this->actingAs($user);

        $isolationRes = $this->get(route('isolation.index'));
        $isolationRes->assertStatus(200);
        $isolationRes->assertSee('data-testid="isolation-countdown-timer"', false);
        $isolationRes->assertSee('Resterende beveiligingstijd', false);

        $shopRes = $this->get(route('shop.index'));
        $shopRes->assertStatus(200);
        $shopRes->assertSee('data-testid="shop-protection-countdown"', false);
        $shopRes->assertSee('data-testid="shop-vest-countdown"', false);

        $vipRes = $this->get(route('vip.index'));
        $vipRes->assertStatus(200);
        $vipRes->assertSee('data-testid="vip-cash-protection-countdown"', false);

        $hitmanRes = $this->get(route('hitman.index'));
        $hitmanRes->assertStatus(200);
        $hitmanRes->assertSee('data-testid="hitman-vest-countdown"', false);
    }
}
