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

    public function test_failed_package_interception_sends_to_prison_with_baddie_message(): void
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
                $this->assertStringContainsString('sec vast, baddie 💋', $error);
                break;
            }
        }

        $this->assertTrue($caught, 'Failed package interception should send player to prison');
    }
}
