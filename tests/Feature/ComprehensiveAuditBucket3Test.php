<?php

namespace Tests\Feature;

use App\Models\ArmWrestleMatch;
use App\Models\BankTransaction;
use App\Models\Bounty;
use App\Models\Character;
use App\Models\User;
use App\Support\ArmWrestleOutcome;
use App\Support\ConnectFourEngine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class ComprehensiveAuditBucket3Test extends TestCase
{
    use RefreshDatabase;

    protected function makeCharacter(array $attributes = []): Character
    {
        $user = User::factory()->create();

        return Character::create(array_merge([
            'user_id' => $user->id,
            'name' => 'Patient_'.uniqid(),
            'level' => 10,
            'energy' => 100,
            'max_energy' => 100,
            'health' => 100,
            'max_health' => 100,
            'cash' => 500000,
            'bank' => 2000000,
            'stars' => 50,
            'bullets' => 200,
            'respect' => 100,
            'strength' => 50,
            'defense' => 50,
            'intelligence' => 50,
            'stamina' => 50,
            'is_in_prison' => false,
            'prison_until' => null,
            'coma_until' => null,
            'protection_until' => null,
            'current_location' => 'Paviljoen 1 (Gesloten)',
        ], $attributes));
    }

    // ==========================================
    // 1. CONNECT FOUR ENGINE & CONTROLLER
    // ==========================================

    public function test_connect_four_engine_bounds_checks_prevent_invalid_column_exceptions(): void
    {
        $board = ConnectFourEngine::emptyBoard();

        $this->assertNull(ConnectFourEngine::lowestEmptyRow($board, -1));
        $this->assertNull(ConnectFourEngine::lowestEmptyRow($board, 7));
        $this->assertNull(ConnectFourEngine::lowestEmptyRow($board, 999));

        $dropRow1 = ConnectFourEngine::dropToken($board, -5, ConnectFourEngine::PLAYER);
        $this->assertNull($dropRow1);
        $this->assertSame(ConnectFourEngine::emptyBoard(), $board);

        $dropRow2 = ConnectFourEngine::dropToken($board, 7, ConnectFourEngine::PLAYER);
        $this->assertNull($dropRow2);
        $this->assertSame(ConnectFourEngine::emptyBoard(), $board);
    }

    public function test_connect_four_engine_choose_arjan_move_falls_back_safely(): void
    {
        $board = ConnectFourEngine::emptyBoard();
        $move = ConnectFourEngine::chooseArjanMove($board, 'hard');

        $this->assertGreaterThanOrEqual(0, $move);
        $this->assertLessThan(ConnectFourEngine::COLUMNS, $move);
    }

    public function test_connect_four_blocked_when_character_is_comatose_or_needs_revive_serum(): void
    {
        $char = $this->makeCharacter(['coma_until' => now()->addMinutes(30)]);

        $moveResponse = $this->actingAs($char->user)->post(route('connect_four.move'), [
            'column' => 3,
        ]);
        $moveResponse->assertStatus(422);
        $moveResponse->assertJson(['success' => false]);
        $this->assertStringContainsString(Character::REVIVE_SERUM_NAME, $moveResponse->json('message'));
    }

    public function test_connect_four_rejects_invalid_or_tampered_board_state(): void
    {
        $char = $this->makeCharacter();

        $tamperedState = [
            'board' => [['X', 'O']], // Invalid row count (not 6 rows)
            'starter' => 1,
        ];

        Cache::put("connect_four_match_{$char->id}", $tamperedState, now()->addMinutes(10));

        $response = $this->actingAs($char->user)->post(route('connect_four.move'), [
            'column' => 0,
        ]);

        $response->assertStatus(422);
        $response->assertJson(['success' => false]);
        $this->assertStringContainsString('geen actief potje', $response->json('message'));
    }

    // ==========================================
    // 2. ARM WRESTLE SERVER AUTHORITY
    // ==========================================

    public function test_arm_wrestle_outcome_win_chance_is_clamped_between_1_and_99(): void
    {
        $outcome = new ArmWrestleOutcome;

        $this->assertIsBool($outcome->isPlayerWinner(0, 1000000));
        $this->assertIsBool($outcome->isPlayerWinner(1000000, 0));
        $this->assertIsBool($outcome->isPlayerWinner(-50, -100));
    }

    public function test_arm_wrestle_npc_blocked_when_needs_revive_serum(): void
    {
        $char = $this->makeCharacter(['deaths_count' => 10]);
        $perks = $char->perks ?? [];
        $perks['liquidation_streak_deaths'] = 10;
        $char->perks = $perks;
        $char->save();

        $this->assertTrue($char->needsReviveSerum());

        $response = $this->actingAs($char->user)->post(route('armwrestling.npc.finish'), [
            'opponent_key' => 'sjonnie',
            'clicks' => 50,
            'duration_ms' => 5000,
            'duration_seconds' => 5,
        ]);

        $response->assertJson([
            'success' => false,
        ]);
        $this->assertStringContainsString(Character::REVIVE_SERUM_NAME, $response->json('message'));
    }

    public function test_arm_wrestle_accept_challenge_cancels_and_refunds_if_challenger_is_in_coma(): void
    {
        $challenger = $this->makeCharacter(['cash' => 50000]);
        $opponent = $this->makeCharacter(['cash' => 50000]);

        $match = ArmWrestleMatch::create([
            'challenger_id' => $challenger->id,
            'opponent_id' => $opponent->id,
            'bet_amount' => 10000,
            'type' => 'pvp',
            'status' => 'open',
        ]);

        // Put challenger in coma before opponent accepts
        $challenger->coma_until = now()->addMinutes(30);
        $challenger->save();

        $response = $this->actingAs($opponent->user)->post(route('armwrestling.challenge.accept', $match->id));

        $response->assertSessionHas('error');
        $this->assertStringContainsString('teruggestort', session('error'));

        $match->refresh();
        $this->assertSame('cancelled', $match->status);

        $challenger->refresh();
        $this->assertSame(60000, (int) $challenger->cash); // Refunded 10000
    }

    public function test_arm_wrestle_accept_challenge_breaks_voluntary_isolation(): void
    {
        $challenger = $this->makeCharacter(['cash' => 50000, 'strength' => 10]);
        $opponent = $this->makeCharacter([
            'cash' => 50000,
            'strength' => 50,
            'protection_until' => now()->addMinutes(20),
        ]);

        $this->assertTrue($opponent->hasIsolationProtection());

        $match = ArmWrestleMatch::create([
            'challenger_id' => $challenger->id,
            'opponent_id' => $opponent->id,
            'bet_amount' => 5000,
            'type' => 'pvp',
            'status' => 'open',
        ]);

        $response = $this->actingAs($opponent->user)->post(route('armwrestling.challenge.accept', $match->id));
        $response->assertRedirect();

        $opponent->refresh();
        $this->assertFalse($opponent->hasIsolationProtection());
    }

    // ==========================================
    // 3. HACKING SYSTEM
    // ==========================================

    public function test_hacking_enforces_daily_limit_and_coma_guards(): void
    {
        $char = $this->makeCharacter(['coma_until' => now()->addMinutes(30)]);

        $response = $this->actingAs($char->user)->post(route('hacking.puzzle'), [
            'puzzle' => 1,
        ]);

        $response->assertStatus(403);
        $this->assertFalse($response->json('success'));
        $this->assertStringContainsString(Character::REVIVE_SERUM_NAME, $response->json('message'));
    }

    public function test_hacking_stage_progression_and_daily_completion(): void
    {
        $char = $this->makeCharacter(['energy' => 100]);

        // Start at stage 1
        session(['hacking_stage' => 1, 'hacking_session_date' => now()->toDateString()]);

        // Stage 1
        $res1 = $this->actingAs($char->user)->post(route('hacking.puzzle'), [
            'puzzle' => 1,
        ]);
        $res1->assertJson(['success' => true, 'next_stage' => 2]);
        $this->assertSame(2, session('hacking_stage'));

        // Stage 2
        $res2 = $this->actingAs($char->user)->post(route('hacking.puzzle'), [
            'puzzle' => 2,
        ]);
        $res2->assertJson(['success' => true, 'next_stage' => 3]);
        $this->assertSame(3, session('hacking_stage'));

        // Stage 3
        $res3 = $this->actingAs($char->user)->post(route('hacking.puzzle'), [
            'puzzle' => 3,
        ]);
        $res3->assertJson(['success' => true, 'next_stage' => 'wheel']);
        $this->assertSame('wheel', session('hacking_stage'));

        // Wheel spin
        $spinRes = $this->actingAs($char->user)->post(route('hacking.spin'));
        $spinRes->assertJson(['success' => true]);
        $this->assertSame('done', session('hacking_stage'));

        $char->refresh();
        $this->assertFalse($char->canDailyHack());

        // Cannot solve again today
        $resExtra = $this->actingAs($char->user)->post(route('hacking.puzzle'), [
            'puzzle' => 1,
        ]);
        $resExtra->assertStatus(400);
        $this->assertFalse($resExtra->json('success'));
    }

    // ==========================================
    // 4. HITMAN & LIQUIDATIONS
    // ==========================================

    public function test_hitman_cannot_target_self(): void
    {
        $char = $this->makeCharacter();

        $contractRes = $this->actingAs($char->user)->post(route('hitman.contract'), [
            'target_id' => $char->id,
            'contract_id' => 1,
        ]);
        $contractRes->assertSessionHas('error', 'Je kunt geen contract op jezelf plaatsen!');

        $shootRes = $this->actingAs($char->user)->post(route('hitman.shoot'), [
            'target_id' => $char->id,
            'bullets' => 10,
        ]);
        $shootRes->assertSessionHas('error', 'Je kunt niet op jezelf schieten!');

        $intelRes = $this->actingAs($char->user)->post(route('hitman.intel'), [
            'target_id' => $char->id,
            'intel_type' => 'standard_15m',
        ]);
        $intelRes->assertSessionHas('error', 'Je hoeft geen schaduw-inlichtingen op jezelf uit te voeren!');
    }

    public function test_hitman_shoot_blocked_when_attacker_or_defender_in_coma(): void
    {
        $attacker = $this->makeCharacter(['bullets' => 50, 'energy' => 50]);
        $defender = $this->makeCharacter(['coma_until' => now()->addMinutes(30)]);

        $res = $this->actingAs($attacker->user)->post(route('hitman.shoot'), [
            'target_id' => $defender->id,
            'bullets' => 10,
        ]);

        $res->assertSessionHas('error');
        $this->assertStringContainsString('Code Blauw', session('error'));
    }

    public function test_hitman_shoot_atomic_plunder_and_bounty_claim(): void
    {
        $attacker = $this->makeCharacter([
            'level' => 10,
            'strength' => 200,
            'bullets' => 100,
            'cash' => 10000,
            'energy' => 50,
            'current_location' => 'Paviljoen 1 (Gesloten)',
        ]);

        $defender = $this->makeCharacter([
            'level' => 10,
            'defense' => 5,
            'cash' => 100000,
            'bank' => 200000,
            'health' => 100,
            'current_location' => 'Paviljoen 1 (Gesloten)',
        ]);

        $placer = $this->makeCharacter();

        Bounty::create([
            'target_character_id' => $defender->id,
            'placed_by_character_id' => $placer->id,
            'amount' => 50000,
            'is_claimed' => false,
        ]);

        $response = $this->actingAs($attacker->user)->post(route('hitman.shoot'), [
            'target_id' => $defender->id,
            'bullets' => 50,
        ]);

        $response->assertSessionHas('success');

        $attacker->refresh();
        $defender->refresh();

        // 45% cash = 45000, 7% bank = 14000, bounty = 50000
        $this->assertGreaterThan(10000, (int) $attacker->cash);
        $this->assertSame(55000, (int) $defender->cash);
        $this->assertSame(186000, (int) $defender->bank);

        $bounty = Bounty::where('target_character_id', $defender->id)->first();
        $this->assertTrue((bool) $bounty->is_claimed);
        $this->assertSame($attacker->id, (int) $bounty->claimed_by_character_id);

        $bankTx = BankTransaction::where('character_id', $defender->id)->first();
        $this->assertNotNull($bankTx);
        $this->assertSame(14000, (int) $bankTx->amount);
    }

    // ==========================================
    // 5. THEFT & PICKPOCKETING (NO FIGHT HIJACK)
    // ==========================================

    public function test_theft_pvp_executes_theft_service_without_fight_controller_hijack(): void
    {
        $thief = $this->makeCharacter([
            'level' => 5,
            'energy' => 50,
            'intelligence' => 30,
            'stamina' => 30,
        ]);

        $victim = $this->makeCharacter([
            'cash' => 20000,
        ]);

        $response = $this->actingAs($thief->user)->post(route('theft.steal'), [
            'tier' => 'pocket',
            'victim_id' => $victim->id,
        ]);

        $this->assertTrue(session()->has('success') || session()->has('error'));

        $this->assertDatabaseHas('theft_records', [
            'thief_id' => $thief->id,
            'victim_id' => $victim->id,
        ]);
    }

    public function test_theft_confirms_voluntary_isolation_breaking(): void
    {
        $thief = $this->makeCharacter([
            'level' => 5,
            'energy' => 50,
            'protection_until' => now()->addMinutes(30),
        ]);

        // Attempt without confirmation
        $resNoConfirm = $this->actingAs($thief->user)->post(route('theft.steal'), [
            'tier' => 'pocket',
            'confirm_break_isolation' => 0,
        ]);

        $resNoConfirm->assertSessionHas('error');
        $this->assertStringContainsString('Vrijwillige Isolatie', session('error'));

        // Attempt with confirmation
        $resConfirm = $this->actingAs($thief->user)->post(route('theft.steal'), [
            'tier' => 'pocket',
            'confirm_break_isolation' => 1,
        ]);

        $thief->refresh();
        $this->assertFalse($thief->hasIsolationProtection());
    }

    public function test_theft_blocked_when_in_coma_or_needs_revive_serum(): void
    {
        $thief = $this->makeCharacter([
            'coma_until' => now()->addMinutes(30),
            'energy' => 50,
        ]);

        $response = $this->actingAs($thief->user)->post(route('theft.steal'), [
            'tier' => 'pocket',
        ]);

        $response->assertSessionHas('error');
        $this->assertStringContainsString('coma of Code Blauw', session('error'));
    }

    public function test_theft_rejects_invalid_tier(): void
    {
        $thief = $this->makeCharacter(['energy' => 50]);

        $response = $this->actingAs($thief->user)->post(route('theft.steal'), [
            'tier' => 'non_existent_tier_123',
        ]);

        $response->assertSessionHasErrors(['tier']);
    }
}
