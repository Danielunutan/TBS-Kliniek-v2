<?php

namespace App\Services;

use App\Models\Character;
use App\Models\GameMessage;

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
     * Check and pay out pending allowance to character bank account for inmates without a gang.
     *
     * @return array{cycles: int, amount: int, amount_per_quarter: int}|null
     */
    public function payoutPendingAllowance(Character $character): ?array
    {
        // Only players without a gang are eligible
        if ($character->gang_id) {
            return null;
        }

        $now = now();

        // Initialize if never set: set to 1 interval ago so player receives their initial allowance right away
        if (! $character->last_allowance_at) {
            $character->last_allowance_at = $now->copy()->subSeconds(self::ALLOWANCE_INTERVAL_SECONDS);
            $character->save();
        }

        if ($character->last_allowance_at > $now) {
            return null;
        }

        $secondsElapsed = (int) $character->last_allowance_at->diffInSeconds($now);

        if ($secondsElapsed < self::ALLOWANCE_INTERVAL_SECONDS) {
            return null;
        }

        // Cap backlog at max 96 cycles (24 hours = €96.000)
        $rawCycles = (int) floor($secondsElapsed / self::ALLOWANCE_INTERVAL_SECONDS);
        $cycles = min(96, $rawCycles);
        $leftoverSeconds = $secondsElapsed % self::ALLOWANCE_INTERVAL_SECONDS;
        $totalPayout = $cycles * self::ALLOWANCE_AMOUNT;

        if ($totalPayout <= 0) {
            $character->last_allowance_at = $now->copy()->subSeconds($leftoverSeconds);
            $character->save();

            return null;
        }

        // Clean deposit directly to character bank account
        $character->bank += $totalPayout;
        $character->last_allowance_at = $now->copy()->subSeconds($leftoverSeconds);
        $character->save();

        GameMessage::create([
            'recipient_id' => $character->id,
            'sender_id' => $character->id,
            'sender_name' => 'Kliniek Sociale Zaken',
            'subject' => '🏥 TBS Uitkering: €'.number_format($totalPayout, 0, ',', '.'),
            'content' => "Beste {$character->name},\n\nEr is zojuist €".number_format($totalPayout, 0, ',', '.')." aan TBS Patiënten Uitkering (€1.000 per kwartier voor patiënten zonder bende) gestort op je bankrekening voor {$cycles} kwartier(en)!",
            'type' => 'system',
        ]);

        return [
            'cycles' => $cycles,
            'amount' => $totalPayout,
            'amount_per_quarter' => self::ALLOWANCE_AMOUNT,
        ];
    }

    /**
     * Get detailed status and countdown for character allowance.
     *
     * @return array{eligible: bool, amount_per_quarter: int, pending_cycles: int, pending_amount: int, seconds_until_next: int, next_human: string, last_payout_at: ?string}
     */
    public function getAllowanceDetails(Character $character): array
    {
        if ($character->gang_id) {
            return [
                'eligible' => false,
                'amount_per_quarter' => 0,
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

        $pendingCycles = (int) floor($secondsElapsed / self::ALLOWANCE_INTERVAL_SECONDS);
        $pendingAmount = $pendingCycles * self::ALLOWANCE_AMOUNT;

        $secondsUntilNext = self::ALLOWANCE_INTERVAL_SECONDS - ($secondsElapsed % self::ALLOWANCE_INTERVAL_SECONDS);
        $minutesLeft = (int) floor($secondsUntilNext / 60);
        $secondsLeft = $secondsUntilNext % 60;
        $nextHuman = "{$minutesLeft}m {$secondsLeft}s";

        return [
            'eligible' => true,
            'amount_per_quarter' => self::ALLOWANCE_AMOUNT,
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
        $characters = Character::whereNull('gang_id')->get();
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
