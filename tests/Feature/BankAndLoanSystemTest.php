<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\Loan;
use App\Models\LotteryRound;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BankAndLoanSystemTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    private function createInmate(array $attributes = []): array
    {
        $user = User::factory()->create();
        $character = Character::create(array_merge([
            'user_id' => $user->id,
            'name' => 'LoanInmate'.rand(1000, 9999),
            'cash' => 25000,
            'bank' => 50000,
            'stars' => 20,
            'level' => 3,
            'xp' => 300,
            'health' => 100,
            'max_health' => 100,
            'energy' => 100,
            'max_energy' => 100,
            'respect' => 25,
        ], $attributes));

        return [$user, $character];
    }

    public function test_bank_financial_center_renders_cleanly_with_tabs(): void
    {
        [$user, $character] = $this->createInmate(['level' => 5]);

        $response = $this->actingAs($user)->get(route('bank.index'));

        $response->assertOk();
        $response->assertSee('Management');
        $response->assertSee('Financieel overzicht');
        $response->assertSee('Leningen');
        $response->assertSee('Transacties');
        $response->assertSee('Nieuwe lening aanvragen');
        $response->assertSee('Rente & Leningtax (8,5%)', false);
    }

    public function test_player_can_request_loan_within_capacity(): void
    {
        [$user, $character] = $this->createInmate(['level' => 4, 'cash' => 10000]);
        $initialCash = $character->cash;

        $lotteryRound = LotteryRound::current();
        $initialJackpot = $lotteryRound->jackpot;
        $initialTaxCollected = $lotteryRound->tax_collected;

        // Level 4 capacity = €400.000. Request €50.000 loan.
        $response = $this->actingAs($user)->post(route('bank.loans.request'), [
            'amount' => 50000,
            'deposit_to' => 'cash',
        ]);

        $response->assertRedirect(route('bank.index', ['tab' => 'loans']));
        $response->assertSessionHas('success');

        $character->refresh();
        $this->assertEquals($initialCash + 50000, $character->cash);

        $lotteryRound->refresh();
        $this->assertEquals($initialJackpot + 4250, $lotteryRound->jackpot);
        $this->assertEquals($initialTaxCollected + 4250, $lotteryRound->tax_collected);

        $this->assertDatabaseHas('loans', [
            'character_id' => $character->id,
            'amount' => 50000,
            'interest_rate' => 8.5,
            'interest_amount' => 4250,
            'total_due' => 54250,
            'remaining_due' => 54250,
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('bank_transactions', [
            'character_id' => $character->id,
            'type' => 'loan_received',
            'amount' => 50000,
        ]);
    }

    public function test_loan_request_validates_minimum_and_maximum_capacity(): void
    {
        [$user, $character] = $this->createInmate(['level' => 1]); // Capacity €50.000

        // 1. Below minimum (€5.000 < €10.000)
        $res1 = $this->actingAs($user)->post(route('bank.loans.request'), [
            'amount' => 5000,
            'deposit_to' => 'cash',
        ]);
        $res1->assertSessionHasErrors('amount');

        // 2. Exceeds max capacity (€100.000 > €50.000)
        $res2 = $this->actingAs($user)->post(route('bank.loans.request'), [
            'amount' => 100000,
            'deposit_to' => 'cash',
        ]);
        $res2->assertRedirect(route('bank.index', ['tab' => 'loans']));
        $res2->assertSessionHas('error');
    }

    public function test_player_can_repay_loan_partially_and_fully(): void
    {
        [$user, $character] = $this->createInmate(['cash' => 100000]);

        // Request loan: €20.000 -> interest €1.700 -> total due €21.700
        $this->actingAs($user)->post(route('bank.loans.request'), [
            'amount' => 20000,
            'deposit_to' => 'cash',
        ]);

        $loan = Loan::where('character_id', $character->id)->latest('id')->first();
        $this->assertNotNull($loan);
        $this->assertEquals(21700, $loan->total_due);

        $character->refresh();
        $cashBeforeRepay = $character->cash;

        // 1. Partial Repayment: repay €10.000 from cash
        $repay1 = $this->actingAs($user)->post(route('bank.loans.repay', $loan), [
            'amount' => 10000,
            'source' => 'cash',
        ]);
        $repay1->assertSessionHas('success');

        $character->refresh();
        $loan->refresh();

        $this->assertEquals($cashBeforeRepay - 10000, $character->cash);
        $this->assertEquals(10000, $loan->total_paid);
        $this->assertEquals(11700, $loan->remaining_due);
        $this->assertEquals('active', $loan->status);

        // 2. Full Repayment of remaining €11.700
        $repay2 = $this->actingAs($user)->post(route('bank.loans.repay-full', $loan), [
            'source' => 'cash',
        ]);
        $repay2->assertSessionHas('success');

        $character->refresh();
        $loan->refresh();

        $this->assertEquals(21700, $loan->total_paid);
        $this->assertEquals(0, $loan->remaining_due);
        $this->assertEquals('paid', $loan->status);
        $this->assertNotNull($loan->paid_at);

        $this->assertDatabaseHas('bank_transactions', [
            'character_id' => $character->id,
            'type' => 'loan_repayment',
        ]);
    }

    public function test_deposit_and_withdraw_record_transactions(): void
    {
        [$user, $character] = $this->createInmate(['cash' => 20000, 'bank' => 10000]);

        // Deposit €5.000
        $this->actingAs($user)->post(route('bank.deposit'), ['amount' => 5000]);

        $this->assertDatabaseHas('bank_transactions', [
            'character_id' => $character->id,
            'type' => 'deposit',
            'amount' => 5000,
        ]);

        // Withdraw €2.000
        $this->actingAs($user)->post(route('bank.withdraw'), ['amount' => 2000]);

        $this->assertDatabaseHas('bank_transactions', [
            'character_id' => $character->id,
            'type' => 'withdraw',
            'amount' => -2000,
        ]);
    }
}
