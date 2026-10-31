<?php

namespace App\Services;

use App\Models\BankTransaction;
use App\Models\Character;
use App\Models\Loan;
use App\Models\LotteryRound;
use Illuminate\Support\Facades\DB;

class LoanService
{
    /**
     * Request a new loan.
     *
     * @return array{success: bool, message: string, loan?: Loan}
     */
    public function requestLoan(Character $character, int $amount, string $depositTo = 'cash'): array
    {
        if ($amount < Loan::MIN_LOAN_AMOUNT) {
            return [
                'success' => false,
                'message' => 'Het minimale leenbedrag is €'.number_format(Loan::MIN_LOAN_AMOUNT, 0, ',', '.').'.',
            ];
        }

        $available = Loan::getBeschikbareLening($character);
        if ($amount > $available) {
            return [
                'success' => false,
                'message' => 'Je kunt maximaal €'.number_format($available, 0, ',', '.').' lenen op basis van jouw level.',
            ];
        }

        $interestRate = Loan::DEFAULT_INTEREST_RATE;
        $interestAmount = (int) round(($amount * $interestRate) / 100);
        $totalDue = $amount + $interestAmount;

        // Generate unique loan number like #5332
        $loanNumber = '#'.rand(1000, 9999);
        while (Loan::where('loan_number', $loanNumber)->exists()) {
            $loanNumber = '#'.rand(1000, 9999);
        }

        /** @var Loan|null $loan */
        $loan = DB::transaction(function () use ($character, $amount, $interestRate, $interestAmount, $totalDue, $loanNumber, $depositTo): ?Loan {
            /** @var Character $lockedChar */
            $lockedChar = Character::where('id', $character->id)->lockForUpdate()->first();

            // Re-check the capacity under the character lock so parallel requests cannot exceed the limit.
            if ($amount > Loan::getBeschikbareLening($lockedChar)) {
                return null;
            }

            $loan = Loan::create([
                'character_id' => $lockedChar->id,
                'loan_number' => $loanNumber,
                'amount' => $amount,
                'interest_rate' => $interestRate,
                'interest_amount' => $interestAmount,
                'total_due' => $totalDue,
                'total_paid' => 0,
                'remaining_due' => $totalDue,
                'status' => 'active',
                'started_at' => now(),
            ]);

            if ($depositTo === 'bank') {
                $lockedChar->bank += $amount;
                $balanceAfter = $lockedChar->bank;
            } else {
                $lockedChar->cash += $amount;
                $balanceAfter = $lockedChar->cash;
            }
            $lockedChar->save();

            $character->cash = $lockedChar->cash;
            $character->bank = $lockedChar->bank;

            $destinationText = $depositTo === 'bank' ? 'op bankrekening gestort' : 'contant ontvangen';
            BankTransaction::record(
                $lockedChar,
                'loan_received',
                $depositTo,
                $amount,
                $balanceAfter,
                "Lening {$loanNumber} afgesloten (+€".number_format($amount, 0, ',', '.')." {$destinationText}, €".number_format($interestAmount, 0, ',', '.').' rente/tax naar loterijpot)'
            );

            // Voeg de leningtax (rente) toe aan de actieve loterij jackpot pot
            LotteryRound::addTax($interestAmount);

            return $loan;
        });

        $formattedAmount = number_format($amount, 0, ',', '.');
        $formattedTax = number_format($interestAmount, 0, ',', '.');

        return [
            'success' => true,
            'message' => "Lening {$loanNumber} van €{$formattedAmount} is succesvol afgesloten! De leningtax (€{$formattedTax}) vloeit direct door naar de TBS Afdelingsloterij Jackpot!",
            'loan' => $loan,
        ];
    }

    /**
     * Repay an active loan partially or fully.
     *
     * @return array{success: bool, message: string, amount_paid: int, paid_full: bool}
     */
    public function repayLoan(Character $character, Loan $loan, int $amount, string $source = 'cash'): array
    {
        if ($loan->character_id !== $character->id || $loan->status !== 'active') {
            return [
                'success' => false,
                'message' => 'Deze lening is niet actief of niet van jou.',
                'amount_paid' => 0,
                'paid_full' => false,
            ];
        }

        if ($amount <= 0) {
            return [
                'success' => false,
                'message' => 'Voer een geldig aflosbedrag in van minimaal € 1.',
                'amount_paid' => 0,
                'paid_full' => false,
            ];
        }

        return DB::transaction(function () use ($character, $loan, $amount, $source): array {
            /** @var Character $lockedChar */
            $lockedChar = Character::where('id', $character->id)->lockForUpdate()->first();
            /** @var Loan $lockedLoan */
            $lockedLoan = Loan::where('id', $loan->id)->lockForUpdate()->first();

            if ($lockedLoan->status !== 'active') {
                return [
                    'success' => false,
                    'message' => 'Deze lening is reeds volledig afgelost.',
                    'amount_paid' => 0,
                    'paid_full' => true,
                ];
            }

            $actualRepay = min($amount, $lockedLoan->remaining_due);

            $availableFunds = $source === 'bank' ? $lockedChar->bank : $lockedChar->cash;
            if ($availableFunds < $actualRepay) {
                $fundLabel = $source === 'bank' ? 'banktegoed' : 'contant geld op zak';

                return [
                    'success' => false,
                    'message' => "Niet genoeg {$fundLabel} om €".number_format($actualRepay, 0, ',', '.').' af te lossen.',
                    'amount_paid' => 0,
                    'paid_full' => false,
                ];
            }

            if ($source === 'bank') {
                $lockedChar->bank -= $actualRepay;
                $balanceAfter = $lockedChar->bank;
            } else {
                $lockedChar->cash -= $actualRepay;
                $balanceAfter = $lockedChar->cash;
            }
            $lockedChar->save();

            $lockedLoan->total_paid += $actualRepay;
            $lockedLoan->remaining_due -= $actualRepay;

            $isFull = false;
            if ($lockedLoan->remaining_due <= 0) {
                $lockedLoan->remaining_due = 0;
                $lockedLoan->status = 'paid';
                $lockedLoan->paid_at = now();
                $isFull = true;
            }
            $lockedLoan->save();

            $character->cash = $lockedChar->cash;
            $character->bank = $lockedChar->bank;

            $desc = $isFull
                ? "Lening {$lockedLoan->loan_number} VOLLEDIG afgelost (-€".number_format($actualRepay, 0, ',', '.').')'
                : "Aflossing lening {$lockedLoan->loan_number} (-€".number_format($actualRepay, 0, ',', '.').')';

            BankTransaction::record(
                $lockedChar,
                'loan_repayment',
                $source,
                -$actualRepay,
                $balanceAfter,
                $desc
            );

            $formatted = number_format($actualRepay, 0, ',', '.');
            $msg = $isFull
                ? "Gefeliciteerd! Lening {$lockedLoan->loan_number} is met €{$formatted} VOLLEDIG afgelost!"
                : "Je hebt €{$formatted} succesvol afgelost op lening {$lockedLoan->loan_number}. Nog te betalen: €".number_format($lockedLoan->remaining_due, 0, ',', '.').'.';

            return [
                'success' => true,
                'message' => $msg,
                'amount_paid' => $actualRepay,
                'paid_full' => $isFull,
            ];
        });
    }

    /**
     * Repay an active loan in full.
     *
     * @return array{success: bool, message: string, amount_paid: int, paid_full: bool}
     */
    public function repayLoanFull(Character $character, Loan $loan, string $source = 'cash'): array
    {
        return $this->repayLoan($character, $loan, $loan->remaining_due, $source);
    }

    /**
     * Automatically deduct 10% of income/profit to repay oldest active loan.
     *
     * @return int Amount deducted and repaid (0 if no active loans)
     */
    public function autoRepayFromIncome(Character $character, int $incomeAmount): int
    {
        if ($incomeAmount <= 0) {
            return 0;
        }

        /** @var Loan|null $oldestLoan */
        $oldestLoan = Loan::where('character_id', $character->id)
            ->where('status', 'active')
            ->orderBy('id', 'asc')
            ->first();

        if (! $oldestLoan) {
            return 0;
        }

        $deduction = (int) floor($incomeAmount * 0.10);
        if ($deduction <= 0) {
            return 0;
        }

        $res = $this->repayLoan($character, $oldestLoan, $deduction, 'cash');

        return $res['success'] ? $res['amount_paid'] : 0;
    }
}
