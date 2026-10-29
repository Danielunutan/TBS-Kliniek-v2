<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\User;
use App\Services\CooldownService;
use App\Services\TurnstileService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FightAttackLimitTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_fight_page_displays_daily_attacks_status(): void
    {
        $user = User::where('email', 'demo@tbskliniek.nl')->first();
        $this->actingAs($user);

        $response = $this->get(route('fight.index'));
        $response->assertStatus(200);
        $response->assertSee('Dagelijkse Aanvallen');
        $response->assertSee('100');
        $response->assertSee('3s cooldown');
    }

    public function test_attack_applies_3_second_cooldown(): void
    {
        $user = User::where('email', 'demo@tbskliniek.nl')->first();
        $attacker = $user->character;
        $attacker->update([
            'energy' => 100,
            'health' => 100,
            'is_in_prison' => false,
            'daily_attacks_count' => 0,
            'daily_attacks_date' => Carbon::today()->format('Y-m-d'),
        ]);

        $defender = Character::where('id', '!=', $attacker->id)->first();
        $defender->update(['is_in_prison' => false, 'protection_until' => null]);

        $this->actingAs($user);

        // 1. First attack succeeds and redirects to suspense arena
        $response = $this->post(route('fight.attack'), [
            'opponent_id' => $defender->id,
        ]);
        $response->assertRedirect(route('fight.arena', $defender));
        $response->assertSessionHas('arena_battle');

        $attacker->refresh();
        $this->assertEquals(1, $attacker->daily_attacks_count);

        /** @var CooldownService $cooldownService */
        $cooldownService = app(CooldownService::class);
        $this->assertTrue($cooldownService->isOnCooldown($attacker, 'fight_attack'));
        $this->assertGreaterThan(0, $cooldownService->getRemainingSeconds($attacker, 'fight_attack'));

        // 2. Second immediate attack is blocked by 3-second cooldown
        $secondResponse = $this->post(route('fight.attack'), [
            'opponent_id' => $defender->id,
        ]);
        $secondResponse->assertSessionHas('error');
        $this->assertStringContainsString('Even ademhalen', session('error'));
    }

    public function test_attack_limit_capped_at_100_per_day(): void
    {
        $user = User::where('email', 'demo@tbskliniek.nl')->first();
        $attacker = $user->character;
        $attacker->update([
            'energy' => 100,
            'health' => 100,
            'is_in_prison' => false,
            'daily_attacks_count' => 100,
            'daily_attacks_date' => Carbon::today()->format('Y-m-d'),
        ]);

        $defender = Character::where('id', '!=', $attacker->id)->first();
        $defender->update(['is_in_prison' => false, 'protection_until' => null]);

        $this->actingAs($user);

        // Clear cooldown just in case
        app(CooldownService::class)->clearCooldown($attacker, 'fight_attack');

        $response = $this->post(route('fight.attack'), [
            'opponent_id' => $defender->id,
        ]);

        $response->assertSessionHas('error');
        $this->assertStringContainsString('dagelijkse limiet van 100 aanvallen bereikt', session('error'));
    }

    public function test_captcha_verification_on_attack(): void
    {
        $user = User::where('email', 'demo@tbskliniek.nl')->first();
        $attacker = $user->character;
        $attacker->update([
            'energy' => 100,
            'health' => 100,
            'is_in_prison' => false,
            'daily_attacks_count' => 5,
            'daily_attacks_date' => Carbon::today()->format('Y-m-d'),
        ]);

        $defender = Character::where('id', '!=', $attacker->id)->first();
        $defender->update(['is_in_prison' => false, 'protection_until' => null]);

        $this->actingAs($user);

        // Set session captcha
        session([
            'fight_captcha_required' => true,
            'fight_captcha_ans' => 25,
            'fight_captcha_q' => '15 + 10',
        ]);

        // Clear cooldown
        app(CooldownService::class)->clearCooldown($attacker, 'fight_attack');

        // Setup Turnstile mock
        $turnstileMock = $this->mock(TurnstileService::class);
        $turnstileMock->shouldReceive('verify')->with('invalid_token', \Mockery::any())->andReturn(false);
        $turnstileMock->shouldReceive('verify')->with('valid_token', \Mockery::any())->andReturn(true);

        // 1. Failed Turnstile verification fails
        $failResponse = $this->post(route('fight.attack'), [
            'opponent_id' => $defender->id,
            'cf-turnstile-response' => 'invalid_token',
        ]);
        $failResponse->assertSessionHas('error');
        $this->assertStringContainsString('Zaalwacht controle vereist', session('error'));

        // 2. Successful Turnstile verification succeeds
        session(['fight_captcha_required' => true]);
        app(CooldownService::class)->clearCooldown($attacker, 'fight_attack');

        $successResponse = $this->post(route('fight.attack'), [
            'opponent_id' => $defender->id,
            'cf-turnstile-response' => 'valid_token',
        ]);
        $successResponse->assertRedirect(route('fight.arena', $defender));
        $successResponse->assertSessionHas('arena_battle');
    }

    public function test_attack_steals_percentage_of_victim_cash(): void
    {
        $user = User::where('email', 'demo@tbskliniek.nl')->first();
        $attacker = $user->character;
        $attacker->update([
            'energy' => 100,
            'health' => 100,
            'strength' => 500, // Guarantee win
            'defense' => 500,
            'cash' => 100,
            'is_in_prison' => false,
            'daily_attacks_count' => 0,
            'daily_attacks_date' => Carbon::today()->format('Y-m-d'),
        ]);

        $defender = Character::where('id', '!=', $attacker->id)->first();
        $defender->update([
            'is_in_prison' => false,
            'protection_until' => null,
            'strength' => 1,
            'defense' => 1,
            'cash' => 1000,
        ]);

        $this->actingAs($user);
        app(CooldownService::class)->clearCooldown($attacker, 'fight_attack');

        $response = $this->post(route('fight.attack'), [
            'opponent_id' => $defender->id,
        ]);

        $response->assertRedirect(route('fight.arena', $defender));
        $arenaBattle = session('arena_battle');
        $this->assertTrue($arenaBattle['won']);
        $this->assertGreaterThanOrEqual(100, $arenaBattle['stolen_cash']); // between 10% and 25% of 1000 = 100..250
        $this->assertLessThanOrEqual(250, $arenaBattle['stolen_cash']);

        // BEFORE resolving (during timer): cash has NOT been transferred yet!
        $defender->refresh();
        $attacker->refresh();
        $this->assertEquals(1000, $defender->cash);
        $this->assertEquals(100, $attacker->cash);

        // AFTER timer (resolve endpoint called): cash is transferred!
        $resolveResponse = $this->post(route('fight.arena.resolve', $defender));
        $resolveResponse->assertOk();
        $resolveResponse->assertJson(['success' => true, 'won' => true]);

        $defender->refresh();
        $attacker->refresh();
        $this->assertEquals(1000 - $arenaBattle['stolen_cash'], $defender->cash);
        $this->assertEquals(100 + $arenaBattle['stolen_cash'], $attacker->cash);
    }
}
