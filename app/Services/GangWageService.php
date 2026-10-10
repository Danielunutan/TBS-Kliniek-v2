<?php

namespace App\Services;

use App\Models\Character;
use App\Models\GameMessage;
use App\Models\Gang;

class GangWageService
{
    /**
     * Seconds per 1 wage hour cycle (3600 seconds = 1 hour).
     */
    public const WAGE_INTERVAL_SECONDS = 3600;

    /**
     * Base hourly wage for any gang member.
     */
    public const BASE_HOURLY_WAGE = 150;

    /**
     * Extra hourly wage added per Bling Bling Shop owned by the gang.
     */
    public const SHOP_BONUS_WAGE = 50;

    /**
     * Calculate hourly wage for a given gang.
     */
    public function calculateHourlyWage(?Gang $gang): int
    {
        if (! $gang) {
            return 0;
        }

        return self::BASE_HOURLY_WAGE + (($gang->bling_shops ?? 0) * self::SHOP_BONUS_WAGE);
    }

    /**
     * Calculate 24-hour daily wage for a gang.
     */
    public function calculateDailyWage(?Gang $gang): int
    {
        return $this->calculateHourlyWage($gang) * 24;
    }

    /**
     * Get detailed payout status and countdown for a character.
     *
     * @return array{in_gang: bool, hourly_wage: int, daily_wage: int, pending_hours: int, pending_amount: int, seconds_until_next: int, next_payout_human: string, last_payout_at: ?string}
     */
    public function getWageDetails(Character $character): array
    {
        if (! $character->gang_id || ! $character->gang) {
            return [
                'in_gang' => false,
                'hourly_wage' => 0,
                'daily_wage' => 0,
                'pending_hours' => 0,
                'pending_amount' => 0,
                'seconds_until_next' => self::WAGE_INTERVAL_SECONDS,
                'next_payout_human' => '0m 0s',
                'last_payout_at' => null,
            ];
        }

        $hourlyWage = $this->calculateHourlyWage($character->gang);
        $dailyWage = $hourlyWage * 24;

        $now = now();
        $lastPayout = $character->last_gang_wage_at ?? $character->updated_at ?? $now;
        $secondsElapsed = ($lastPayout > $now) ? 0 : (int) $lastPayout->diffInSeconds($now);

        $pendingHours = (int) floor($secondsElapsed / self::WAGE_INTERVAL_SECONDS);
        $pendingAmount = $pendingHours * $hourlyWage;

        $secondsUntilNext = self::WAGE_INTERVAL_SECONDS - ($secondsElapsed % self::WAGE_INTERVAL_SECONDS);
        $minutesLeft = (int) floor($secondsUntilNext / 60);
        $secondsLeft = $secondsUntilNext % 60;
        $nextHuman = "{$minutesLeft}m {$secondsLeft}s";

        return [
            'in_gang' => true,
            'hourly_wage' => $hourlyWage,
            'daily_wage' => $dailyWage,
            'pending_hours' => $pendingHours,
            'pending_amount' => $pendingAmount,
            'seconds_until_next' => $secondsUntilNext,
            'next_payout_human' => $nextHuman,
            'last_payout_at' => $character->last_gang_wage_at?->format('H:i d-m-Y'),
        ];
    }

    /**
     * Check and automatically pay out all accrued hourly wages to the character's bank account.
     *
     * @return array{hours: int, amount: int, hourly_wage: int, gang_name: string}|null
     */
    public function payoutPendingWages(Character $character): ?array
    {
        if (! $character->gang_id) {
            return null;
        }

        $gang = $character->gang ?? Gang::find($character->gang_id);
        if (! $gang) {
            return null;
        }

        $now = now();

        // Initialize if never set
        if (! $character->last_gang_wage_at) {
            $character->last_gang_wage_at = $now;
            $character->save();

            return null;
        }

        if ($character->last_gang_wage_at > $now) {
            return null;
        }

        $secondsElapsed = (int) $character->last_gang_wage_at->diffInSeconds($now);

        if ($secondsElapsed < self::WAGE_INTERVAL_SECONDS) {
            return null;
        }

        $hours = (int) floor($secondsElapsed / self::WAGE_INTERVAL_SECONDS);
        $leftoverSeconds = $secondsElapsed % self::WAGE_INTERVAL_SECONDS;

        $hourlyWage = $this->calculateHourlyWage($gang);
        $totalPayout = $hours * $hourlyWage;

        if ($totalPayout <= 0) {
            $character->last_gang_wage_at = $now->copy()->subSeconds($leftoverSeconds);
            $character->save();

            return null;
        }

        // Clean white bank deposit directly to character bank account!
        $character->bank += $totalPayout;
        $character->last_gang_wage_at = $now->copy()->subSeconds($leftoverSeconds);
        $character->save();

        // Optional notification message for the player
        GameMessage::create([
            'recipient_id' => $character->id,
            'sender_id' => $character->id,
            'sender_name' => 'Bende Salaris Administratie',
            'subject' => '💰 Bende Uurloon Uitbetaald: €'.number_format($totalPayout, 0, ',', '.'),
            'content' => "Gefeliciteerd!\n\nJe hebt zojuist voor {$hours} gewerkte uren bij bende '{$gang->name}' in totaal €".number_format($totalPayout, 0, ',', '.').' wit salaris ontvangen op je bankrekening (Uurloon: €'.number_format($hourlyWage, 0, ',', '.').').',
            'type' => 'gang',
        ]);

        return [
            'hours' => $hours,
            'amount' => $totalPayout,
            'hourly_wage' => $hourlyWage,
            'gang_name' => $gang->name,
        ];
    }

    /**
     * Batch pay all active gang members across the game (for cron / console command).
     */
    public function payAllMembers(): int
    {
        $characters = Character::whereNotNull('gang_id')->with('gang')->get();
        $totalPaidOut = 0;

        foreach ($characters as $character) {
            $payout = $this->payoutPendingWages($character);
            if ($payout) {
                $totalPaidOut += $payout['amount'];
            }
        }

        return $totalPaidOut;
    }
}
