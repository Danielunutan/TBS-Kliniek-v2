<?php

namespace App\Services;

use App\Models\BankTransaction;
use App\Models\Character;
use Illuminate\Support\Facades\DB;

class AllowanceService
{
    /**
     * Seconds per 1 allowance interval (15 minutes = 900 seconds).
     */
    public const ALLOWANCE_INTERVAL_SECONDS = 900;

    /**
     * Allowance amount paid out every 15 minutes to players without a gang (€1.000).
     */
    public const ALLOWANCE_AMOUNT = 1000;

    /**
     * Determine allowance amount paid out per 15 minutes based on character VIP status and tier.
     */
    public function getAllowanceAmountFor(Character $character): int
    {
        if (! $character->isVip()) {
            return self::ALLOWANCE_AMOUNT;
        }

        return match ($character->getSupporterTier()) {
            4 => 25000, // Tier 4 / Lifetime: €25.000 per kwartier (€100.000/uur)
            3 => 10000, // Tier 3 Kliniek Baas: €10.000 per kwartier (€40.000/uur)
            2 => 5000,  // Tier 2 Gangster Elite: €5.000 per kwartier (€20.000/uur)
            default => 2500, // Tier 1 / General VIP: €2.500 per kwartier (€10.000/uur)
        };
    }

    /**
     * Check and pay out pending allowance to character bank account for inmates without a gang.
     *
     * @return array{cycles: int, amount: int, amount_per_quarter: int}|null
     */
    public function payoutPendingAllowance(Character $character): ?array
    {
        // Exempt owners and gang members do not receive solo inmate allowance payouts.
        if ($character->gang_id || $character->isGameExempt()) {
            return null;
        }

        // Lock the character row so the 15-minute cron job and web requests cannot double-pay the same allowance cycles.
        return DB::transaction(function () use ($character): ?array {
            /** @var Character|null $locked */
            $locked = Character::where('id', $character->id)->lockForUpdate()->first();
            if (! $locked || $locked->gang_id || $locked->isGameExempt()) {
                return null;
            }

            $now = now();

            // Initialize if never set: set to 1 interval ago so player receives their initial allowance right away
            if (! $locked->last_allowance_at) {
                $locked->last_allowance_at = $now->copy()->subSeconds(self::ALLOWANCE_INTERVAL_SECONDS);
                $locked->save();
            }

            if ($locked->last_allowance_at > $now) {
                return null;
            }

            $secondsElapsed = (int) $locked->last_allowance_at->diffInSeconds($now);

            if ($secondsElapsed < self::ALLOWANCE_INTERVAL_SECONDS) {
                return null;
            }

            $amountPerQuarter = $this->getAllowanceAmountFor($locked);

            // Cap backlog at max 96 cycles (24 hours)
            $rawCycles = (int) floor($secondsElapsed / self::ALLOWANCE_INTERVAL_SECONDS);
            $cycles = min(96, $rawCycles);
            $leftoverSeconds = $secondsElapsed % self::ALLOWANCE_INTERVAL_SECONDS;
            $totalPayout = $cycles * $amountPerQuarter;

            if ($totalPayout <= 0) {
                $locked->last_allowance_at = $now->copy()->subSeconds($leftoverSeconds);
                $locked->save();
                $character->last_allowance_at = $locked->last_allowance_at;

                return null;
            }

            // Clean deposit directly to character bank account
            $locked->bank += $totalPayout;
            $locked->wage_protection_until = now()->addMinutes(15);
            $locked->last_allowance_at = $now->copy()->subSeconds($leftoverSeconds);
            $locked->save();

            $character->bank = $locked->bank;
            $character->wage_protection_until = $locked->wage_protection_until;
            $character->last_allowance_at = $locked->last_allowance_at;

            $vipNote = $locked->isVip() ? ' (VIP Bonus)' : '';
            BankTransaction::record(
                $locked,
                'allowance',
                'bank',
                $totalPayout,
                $locked->bank,
                'TBS Patiënten Uitkering'.$vipNote.' ontvangen (+€'.number_format($totalPayout, 0, ',', '.').')'
            );

            return [
                'cycles' => $cycles,
                'amount' => $totalPayout,
                'amount_per_quarter' => $amountPerQuarter,
            ];
        });
    }

    /**
     * Get detailed status and countdown for character allowance.
     *
     * @return array{eligible: bool, amount_per_quarter: int, base_amount: int, vip_bonus: int, is_vip: bool, vip_tier: int, pending_cycles: int, pending_amount: int, seconds_until_next: int, next_human: string, last_payout_at: ?string}
     */
    public function getAllowanceDetails(Character $character): array
    {
        if ($character->gang_id || $character->isGameExempt()) {
            return [
                'eligible' => false,
                'amount_per_quarter' => 0,
                'base_amount' => self::ALLOWANCE_AMOUNT,
                'vip_bonus' => 0,
                'is_vip' => $character->isVip(),
                'vip_tier' => $character->getSupporterTier(),
                'pending_cycles' => 0,
                'pending_amount' => 0,
                'seconds_until_next' => self::ALLOWANCE_INTERVAL_SECONDS,
                'next_human' => '0m 0s',
                'last_payout_at' => null,
            ];
        }

        $now = now();
        $lastPayout = $character->last_allowance_at ?? $character->created_at ?? $now;
        $secondsElapsed = ($lastPayout > $now) ? 0 : (int) $lastPayout->diffInSeconds($now);

        $amountPerQuarter = $this->getAllowanceAmountFor($character);
        $baseAmount = self::ALLOWANCE_AMOUNT;
        $vipBonus = max(0, $amountPerQuarter - $baseAmount);

        $pendingCycles = min(96, (int) floor($secondsElapsed / self::ALLOWANCE_INTERVAL_SECONDS));
        $pendingAmount = $pendingCycles * $amountPerQuarter;

        $secondsUntilNext = self::ALLOWANCE_INTERVAL_SECONDS - ($secondsElapsed % self::ALLOWANCE_INTERVAL_SECONDS);
        $minutesLeft = (int) floor($secondsUntilNext / 60);
        $secondsLeft = $secondsUntilNext % 60;
        $nextHuman = "{$minutesLeft}m {$secondsLeft}s";

        return [
            'eligible' => true,
            'amount_per_quarter' => $amountPerQuarter,
            'base_amount' => $baseAmount,
            'vip_bonus' => $vipBonus,
            'is_vip' => $character->isVip(),
            'vip_tier' => $character->getSupporterTier(),
            'pending_cycles' => $pendingCycles,
            'pending_amount' => $pendingAmount,
            'seconds_until_next' => $secondsUntilNext,
            'next_human' => $nextHuman,
            'last_payout_at' => $character->last_allowance_at?->format('H:i d-m-Y'),
        ];
    }

    /**
     * Batch pay all active non-gang inmates (for cron / console command).
     */
    public function payAllNonGangMembers(): int
    {
        $characters = Character::playable()->whereNull('gang_id')->get();
        $totalPaid = 0;

        foreach ($characters as $character) {
            $payout = $this->payoutPendingAllowance($character);
            if ($payout) {
                $totalPaid += $payout['amount'];
            }
        }

        return $totalPaid;
    }
}
