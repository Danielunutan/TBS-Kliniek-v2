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

    public function test_sponsor_ad_claims_bullets_vest_and_coma_revive(): void
    {
        $user = User::where('email', 'demo@tbskliniek.nl')->first();
        $char = $user->character;
        $char->update([
            'bullets' => 10,
            'vest_until' => null,
            'coma_until' => Carbon::now()->addHours(5),
        ]);

        $this->actingAs($user);

        // Claim bullets
        $start1 = $this->postJson(route('sponsor.start'), ['type' => 'bullets'])->json();
        $resp1 = $this->postJson(route('sponsor.claim'), ['type' => 'bullets', 'token' => $start1['token'], 'robin_token' => 'verified_test']);
        $resp1->assertJson(['success' => true]);
        $char->refresh();
        $this->assertEquals(60, $char->bullets);

        // Claim vest
        $start2 = $this->postJson(route('sponsor.start'), ['type' => 'vest'])->json();
        $resp2 = $this->postJson(route('sponsor.claim'), ['type' => 'vest', 'token' => $start2['token'], 'robin_token' => 'verified_test']);
        $resp2->assertJson(['success' => true]);
        $char->refresh();
        $this->assertTrue($char->hasVest());

        // Coma revive
        $this->assertTrue($char->isComatose());
        $start3 = $this->postJson(route('sponsor.start'), ['type' => 'coma_revive'])->json();
        $resp3 = $this->postJson(route('sponsor.claim'), ['type' => 'coma_revive', 'token' => $start3['token'], 'robin_token' => 'verified_test']);
        $resp3->assertJson(['success' => true]);
        $char->refresh();
        $this->assertFalse($char->isComatose());
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

    public function test_shooting_target_puts_target_in_coma(): void
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

        // Target should be comatose or shooter took backfire
        $this->assertEquals(100, $shooter->bullets);
        if ($target->isComatose()) {
            $this->assertGreaterThan(500, $shooter->cash);
            $this->assertEquals(0, $target->cash);
            $this->assertEquals(1, $shooter->kills_count);
            $this->assertEquals(1, $target->deaths_count);
        } else {
            // In case of backfire
            $this->assertTrue($shooter->isComatose());
        }
    }
}
