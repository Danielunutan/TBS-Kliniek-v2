<?php

namespace App\Services;

use App\Models\Character;
use App\Models\GameMessage;
use App\Models\Referral;
use Illuminate\Support\Facades\DB;

class ReferralService
{
    /**
     * Record a new referral when a player registers.
     */
    public function recordReferral(Character $newCharacter, ?string $referrerIdentifier, ?string $ipAddress = null): ?Referral
    {
        if (empty($referrerIdentifier)) {
            return null;
        }

        $cleanIdentifier = trim($referrerIdentifier);

        // Find referrer by ID, by Character username, or by Character name
        $referrer = null;
        if (is_numeric($cleanIdentifier)) {
            $referrer = Character::find((int) $cleanIdentifier);
        }
        if (! $referrer) {
            $referrer = Character::where('username', $cleanIdentifier)->first();
        }
        if (! $referrer) {
            $referrer = Character::whereRaw('LOWER(username) = ?', [strtolower($cleanIdentifier)])->first();
        }
        if (! $referrer) {
            $referrer = Character::where('name', $cleanIdentifier)->first();
        }
        if (! $referrer) {
            $referrer = Character::whereRaw('LOWER(name) = ?', [strtolower($cleanIdentifier)])->first();
        }

        if (! $referrer || $referrer->id === $newCharacter->id || ($referrer->user_id && $newCharacter->user_id && $referrer->user_id === $newCharacter->user_id)) {
            return null;
        }

        return DB::transaction(function () use ($referrer, $newCharacter, $ipAddress): ?Referral {
            // Avoid duplicate entry if already recorded
            $existing = Referral::where('referred_id', $newCharacter->id)->lockForUpdate()->first();
            if ($existing) {
                return $existing;
            }

            $referral = Referral::create([
                'referrer_id' => $referrer->id,
                'referred_id' => $newCharacter->id,
                'ip_address' => $ipAddress,
                'rewarded' => false,
            ]);

            $this->checkAndGrantRewards($referrer);

            return $referral;
        });
    }

    /**
     * Check if referrer has 3 or more unrewarded referrals and grant 1 month VIP per trio.
     */
    public function checkAndGrantRewards(Character $referrer): int
    {
        return DB::transaction(function () use ($referrer): int {
            /** @var Character|null $lockedReferrer */
            $lockedReferrer = Character::where('id', $referrer->id)->lockForUpdate()->first();
            if (! $lockedReferrer) {
                return 0;
            }

            $unrewarded = Referral::where('referrer_id', $lockedReferrer->id)
                ->where('rewarded', false)
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            $monthsGranted = 0;

            while ($unrewarded->count() >= 3) {
                $trio = $unrewarded->splice(0, 3);

                foreach ($trio as $item) {
                    $item->update([
                        'rewarded' => true,
                        'rewarded_at' => now(),
                    ]);
                }

                // Extend or activate VIP for 1 week (7 days), preserving permanent VIP
                $hasPermanentVip = ($lockedReferrer->is_vip && $lockedReferrer->vip_expires_at === null)
                    || $lockedReferrer->getVipTier() === 4;
                $lockedReferrer->is_vip = true;
                if (! $hasPermanentVip) {
                    if ($lockedReferrer->vip_expires_at && $lockedReferrer->vip_expires_at->isFuture()) {
                        $lockedReferrer->vip_expires_at = $lockedReferrer->vip_expires_at->copy()->addDays(7);
                    } else {
                        $lockedReferrer->vip_expires_at = now()->addDays(7);
                    }
                }
                $lockedReferrer->save();
                $referrer->is_vip = $lockedReferrer->is_vip;
                $referrer->vip_expires_at = $lockedReferrer->vip_expires_at;

                $monthsGranted++;

                // Send notification message to referrer
                $untilFormatted = $lockedReferrer->vip_expires_at ? $lockedReferrer->vip_expires_at->format('d-m-Y H:i') : 'onbepaalde tijd';
                GameMessage::create([
                    'recipient_id' => $lockedReferrer->id,
                    'sender_id' => null,
                    'sender_name' => 'Directie TBS Kliniek',
                    'type' => 'vip_reward',
                    'subject' => '🎁 1 Week Gratis VIP Ontvangen (Referral Beloning)!',
                    'content' => "Geweldig nieuws! Je hebt 3 nieuwe patiënten aangebracht via jouw persoonlijke referral link. Je VIP status is automatisch geactiveerd of met 7 dagen verlengd (geldig t/m {$untilFormatted})! Bedankt voor het laten groeien van TBS Kliniek.",
                ]);
            }

            return $monthsGranted;
        });
    }

    /**
     * Get statistics for a character's referral dashboard.
     *
     * @return array{total: int, unrewarded: int, rewarded_months: int, progress: int, needed: int, username: string, has_username: bool, link: string}
     */
    public function getStats(Character $character): array
    {
        $total = Referral::where('referrer_id', $character->id)->count();
        $unrewarded = Referral::where('referrer_id', $character->id)->where('rewarded', false)->count();
        $rewardedCount = Referral::where('referrer_id', $character->id)->where('rewarded', true)->count();
        $rewardedMonths = (int) floor($rewardedCount / 3);
        $progress = $unrewarded % 3;
        $needed = 3 - $progress;

        // Use custom configured username if available, otherwise character username or ID
        $rawUsername = $character->getRawOriginal('username');
        $hasCustomUsername = ! empty($rawUsername);
        $refCode = $hasCustomUsername ? $rawUsername : $character->username;
        if (empty($refCode)) {
            $refCode = (string) $character->id;
        }

        return [
            'total' => $total,
            'unrewarded' => $unrewarded,
            'rewarded_months' => $rewardedMonths,
            'progress' => $progress,
            'needed' => $needed,
            'username' => $refCode,
            'has_username' => $hasCustomUsername,
            'link' => rtrim(url('/'), '/').'/?ref='.$refCode,
        ];
    }
}
