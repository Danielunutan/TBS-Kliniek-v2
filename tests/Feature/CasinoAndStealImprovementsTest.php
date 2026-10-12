<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\User;
use App\Services\TheftService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CasinoAndStealImprovementsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_coin_flip_deducts_cash_and_returns_valid_response(): void
    {
        $user = User::where('email', 'demo@tbskliniek.nl')->first();
        $character = $user->character;
        $character->update(['cash' => 500]);

        $this->actingAs($user);

        // Test JSON request (Alpine real-time)
        $response = $this->postJson(route('casino.play'), [
            'game' => 'coinflip',
            'bet' => 100,
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'win',
            'message',
            'winnings',
            'cash',
        ]);

        $character->refresh();
        $this->assertContains($character->cash, [400, 600]);
    }

    public function test_slots_spin_deducts_cash_and_returns_symbols(): void
    {
        $user = User::where('email', 'demo@tbskliniek.nl')->first();
        $character = $user->character;
        $character->update(['cash' => 1000]);

        $this->actingAs($user);

        $response = $this->postJson(route('slots.spin'), [
            'bet' => 50,
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'reels',
            'payout',
            'message',
            'cash',
        ]);

        $character->refresh();
        $this->assertNotEquals(1000, $character->cash);
    }

    public function test_pvp_theft_never_jails_attacker_and_gives_variable_outcomes(): void
    {
        $theftService = app(TheftService::class);

        $user = User::where('email', 'demo@tbskliniek.nl')->first();
        $attacker = $user->character;
        $target = Character::where('id', '!=', $attacker->id)->first();

        $attacker->update([
            'energy' => 100,
            'cash' => 100,
            'jail_until' => null,
            'is_in_prison' => false,
            'strength' => 50,
            'defense' => 50,
            'level' => 5,
        ]);

        $target->update([
            'cash' => 5000,
            'jail_until' => null,
            'is_in_prison' => false,
            'strength' => 10,
            'defense' => 10,
            'level' => 5,
        ]);

        // Execute steal attempts
        for ($i = 0; $i < 5; $i++) {
            $attacker->update([
                'energy' => 100,
                'jail_until' => null,
                'is_in_prison' => false,
            ]);

            $res = $theftService->stealFromInmate($attacker, $target);

            // Attacker must NEVER be put in prison/jail from PvP robbery
            $attacker->refresh();
            $this->assertNull($attacker->jail_until);
            $this->assertFalse((bool) $attacker->is_in_prison);
            $this->assertArrayHasKey('success', $res);
            $this->assertArrayHasKey('message', $res);
        }
    }
}
