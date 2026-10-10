<?php

namespace App\Services;

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

            return true;
        });
    }

    /**
     * Add cash with transaction lock.
     */
    public function addCash(Character $character, int $amount): void
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
}
