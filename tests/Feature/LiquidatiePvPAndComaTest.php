<?php

namespace Tests\Feature;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LiquidatiePvPAndComaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_liquidatie_page_renders_successfully(): void
    {
        $user = User::where('email', 'demo@tbskliniek.nl')->first();
        $this->actingAs($user);

        $response = $this->get(route('hitman.index'));
        $response->assertStatus(200);
        $response->assertSee('Liquidatie');
        $response->assertSee('Wapenarsenaal');
        $response->assertSee('Munitievoorraad');
        $response->assertSee('Kogelwerend Vest');
    }

    public function test_buy_bullets_and_vest_with_stars(): void
    {
        $user = User::where('email', 'demo@tbskliniek.nl')->first();
        $char = $user->character;
        $char->update([
            'stars' => 300,
            'bullets' => 10,
            'vest_until' => null,
        ]);

        $this->actingAs($user);

        // Buy +50 bullets with 50 stars
        $respBullets = $this->post(route('hitman.buy_bullets'));
        $respBullets->assertSessionHas('success');
        $char->refresh();
        $this->assertEquals(60, $char->bullets);
        $this->assertEquals(250, $char->stars);

        // Buy 12u vest with 150 stars
        $respVest = $this->post(route('hitman.buy_vest'));
        $respVest->assertSessionHas('success');
        $char->refresh();
        $this->assertTrue($char->hasVest());
        $this->assertEquals(100, $char->stars);
    }

    public function test_player_cannot_hustle_while_comatose(): void
    {
        $user = User::where('email', 'demo@tbskliniek.nl')->first();
        $char = $user->character;
        $char->update([
            'coma_until' => Carbon::now()->addHours(4),
            'energy' => 100,
        ]);

        $this->actingAs($user);

        $resp = $this->post(route('hustle.attempt'), ['hustle_id' => 1]);
        $resp->assertSessionHas('error');
        $this->assertStringContainsString('Intensive Care', session('error'));
    }

    public function test_shooting_target_applies_balance_loot_cooldown_and_blood_feud(): void
    {
        $user = User::where('email', 'demo@tbskliniek.nl')->first();
        $shooter = $user->character;
        $shooter->update([
            'bullets' => 200,
            'energy' => 100,
            'level' => 10,
            'power' => 200,
            'cash' => 500,
            'coma_until' => null,
            'protection_until' => null,
        ]);

        $targetUser = User::where('email', '!=', 'demo@tbskliniek.nl')->first();
        $target = $targetUser->character;
        $target->update([
            'level' => 10,
            'protection_until' => null,
            'coma_until' => null,
            'defense' => 10,
            'power' => 10,
            'cash' => 2500,
            'respect' => 1000,
            'vest_until' => null,
        ]);

        $this->actingAs($user);

        $response = $this->post(route('hitman.shoot'), [
            'target_id' => $target->id,
            'bullets' => 100,
        ]);

        $response->assertSessionHasNoErrors();
        $shooter->refresh();
        $target->refresh();

        $this->assertEquals(100, $shooter->bullets);

        if ($target->isComatose()) {
            // 35% Cash plunder (not 100%)
            $expectedLoot = (int) round(2500 * 0.35); // 875
            $this->assertEquals(500 + $expectedLoot, $shooter->cash);
            $this->assertEquals(2500 - $expectedLoot, $target->cash);
            $this->assertEquals(1, $shooter->kills_count);
            $this->assertEquals(1, $target->deaths_count);

            // Recovery protection is active
            $this->assertTrue($target->isProtected());

            // Blood Feud created
            $this->assertDatabaseHas('blood_feuds', [
                'attacker_id' => $shooter->id,
                'victim_id' => $target->id,
                'is_avenged' => false,
            ]);

            // Attempting to shoot again immediately triggers personal 3h cooldown
            $shooter->update(['bullets' => 100, 'energy' => 100]);
            $target->update(['coma_until' => null, 'protection_until' => null]);
            $cooldownResp = $this->post(route('hitman.shoot'), [
                'target_id' => $target->id,
                'bullets' => 50,
            ]);
            $cooldownResp->assertSessionHas('error');
            $this->assertStringContainsString('recent al geviseerd', session('error'));
        } else {
            // In case of backfire
            $this->assertTrue($shooter->isComatose());
        }
    }
}
