<?php

namespace App\Services;

use App\Models\BankTransaction;
use App\Models\Character;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class EconomyService
{
    /**
     * Deposit cash into bank.
     */
    public function deposit(Character $character, int $amount): bool
    {
        if ($amount <= 0 || $character->cash < $amount) {
            return false;
        }

        return DB::transaction(function () use ($character, $amount): bool {
            /** @var Character $locked */
            $locked = Character::where('id', $character->id)->lockForUpdate()->first();

            if ($locked->cash < $amount) {
                return false;
            }

            $locked->cash -= $amount;
            $locked->bank += $amount;
            $locked->save();

            $character->cash = $locked->cash;
            $character->bank = $locked->bank;

            BankTransaction::record(
                $locked,
                'deposit',
                'bank',
                $amount,
                $locked->bank,
                'Contant gestort op bankrekening (+€'.number_format($amount, 0, ',', '.').')'
            );

            return true;
        });
    }

    /**
     * Withdraw cash from bank.
     */
    public function withdraw(Character $character, int $amount): bool
    {
        if ($amount <= 0 || $character->bank < $amount) {
            return false;
        }

        return DB::transaction(function () use ($character, $amount): bool {
            /** @var Character $locked */
            $locked = Character::where('id', $character->id)->lockForUpdate()->first();

            if ($locked->bank < $amount) {
                return false;
            }

            $locked->bank -= $amount;
            $locked->cash += $amount;
            $locked->save();

            $character->cash = $locked->cash;
            $character->bank = $locked->bank;

            BankTransaction::record(
                $locked,
                'withdraw',
                'bank',
                -$amount,
                $locked->bank,
                'Contant opgenomen van bankrekening (-€'.number_format($amount, 0, ',', '.').')'
            );

            return true;
        });
    }

    /**
     * Withdraw cash from bank with clinic tax deduction.
     */
    public function withdrawWithTax(Character $character, int $totalDebit, int $netCashToAdd, int $taxAmount): bool
    {
        if ($totalDebit <= 0 || $netCashToAdd < 0 || $taxAmount < 0 || $totalDebit !== ($netCashToAdd + $taxAmount) || $character->bank < $totalDebit) {
            return false;
        }

        return DB::transaction(function () use ($character, $totalDebit, $netCashToAdd, $taxAmount): bool {
            /** @var Character $locked */
            $locked = Character::where('id', $character->id)->lockForUpdate()->first();

            if ($locked->bank < $totalDebit) {
                return false;
            }

            $locked->bank -= $totalDebit;
            $locked->cash += $netCashToAdd;
            $locked->save();

            $character->cash = $locked->cash;
            $character->bank = $locked->bank;

            BankTransaction::record(
                $locked,
                'withdraw',
                'bank',
                -$totalDebit,
                $locked->bank,
                'Contant opgenomen van bankrekening (-€'.number_format($totalDebit, 0, ',', '.').' incl. €'.number_format($taxAmount, 0, ',', '.').' tax)'
            );

            return true;
        });
    }

    /**
     * Add cash with transaction lock.
     */
    public function addCash(Character $character, int $amount, string $source = 'in_game'): void
    {
        if ($amount < 0) {
            throw new InvalidArgumentException('Amount must be positive');
        }

        DB::transaction(function () use ($character, $amount): void {
            /** @var Character $locked */
            $locked = Character::where('id', $character->id)->lockForUpdate()->first();
            $locked->cash += $amount;
            $locked->save();

            $character->cash = $locked->cash;
        });

        EventCashHuntService::record($character, $amount, $source);
    }

    /**
     * Deduct cash with transaction lock.
     */
    public function deductCash(Character $character, int $amount): bool
    {
        if ($amount < 0) {
            throw new InvalidArgumentException('Amount must be positive');
        }

        return DB::transaction(function () use ($character, $amount): bool {
            /** @var Character $locked */
            $locked = Character::where('id', $character->id)->lockForUpdate()->first();

            if ($locked->cash < $amount) {
                return false;
            }

            $locked->cash -= $amount;
            $locked->save();

            $character->cash = $locked->cash;

            return true;
        });
    }

    /**
     * Alias for deductCash to ensure compatibility.
     */
    public function subtractCash(Character $character, int $amount, ?string $reason = null): bool
    {
        return $this->deductCash($character, $amount);
    }

    /**
     * Add clean money (wit geld) directly into the character's bank account.
     */
    public function addBankReward(Character $character, int $amount): void
    {
        if ($amount < 0) {
            throw new InvalidArgumentException('Amount must be positive');
        }

        DB::transaction(function () use ($character, $amount): void {
            /** @var Character $locked */
            $locked = Character::where('id', $character->id)->lockForUpdate()->first();
            $locked->bank += $amount;
            $locked->save();

            $character->bank = $locked->bank;
        });
    }

    /**
     * Add stars currency.
     */
    public function addStars(Character $character, int $stars): void
    {
        if ($stars < 0) {
            throw new InvalidArgumentException('Stars must be positive');
        }

        DB::transaction(function () use ($character, $stars): void {
            /** @var Character $locked */
            $locked = Character::where('id', $character->id)->lockForUpdate()->first();
            $locked->stars = ($locked->stars ?? 0) + $stars;
            $locked->save();

            $character->stars = $locked->stars;
        });
    }

    /**
     * Deduct stars currency.
     */
    public function deductStars(Character $character, int $stars): bool
    {
        if ($stars < 0) {
            return false;
        }

        return DB::transaction(function () use ($character, $stars): bool {
            /** @var Character $locked */
            $locked = Character::where('id', $character->id)->lockForUpdate()->first();

            if (($locked->stars ?? 0) < $stars) {
                return false;
            }

            $locked->stars -= $stars;
            $locked->save();

            $character->stars = $locked->stars;

            return true;
        });
    }
}
