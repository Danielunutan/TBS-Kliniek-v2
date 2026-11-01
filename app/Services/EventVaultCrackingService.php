<?php

namespace App\Services;

use App\Models\Character;
use App\Models\EventVaultCracking;
use App\Models\GameMessage;
use App\Models\GameNotification;
use App\Models\VaultCode;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EventVaultCrackingService
{
    public const EVENT_START = '2026-11-02 00:00:00';

    public const EVENT_END = '2026-11-04 23:59:59';

    public const PRIZE_STARS = 2500;

    public const PRIZE_VAULT_CODES = 100;

    public const NOTIFICATION_TYPE = 'event_winner_vault_cracking';

    public const EVENT_SLUG = 'de-grote-kluiscode-challenge-2500-sterren-en-100-codes';

    /**
     * Check whether the vault code challenge is currently active.
     */
    public static function isActive(): bool
    {
        $now = now();
        $start = Carbon::parse(self::EVENT_START);
        $end = Carbon::parse(self::EVENT_END);

        return $now->greaterThanOrEqualTo($start) && $now->lessThanOrEqualTo($end);
    }

    /**
     * Check whether the challenge has finished.
     */
    public static function hasEnded(): bool
    {
        return now()->greaterThan(Carbon::parse(self::EVENT_END));
    }

    /**
     * Record vault codes cracked by a character if the challenge is active.
     */
    public static function record(Character|int $character, int $amount = 1, string $source = 'code_redeem'): void
    {
        if ($amount <= 0 || ! self::isActive()) {
            return;
        }

        $characterId = $character instanceof Character ? $character->id : (int) $character;
        if (! $characterId) {
            return;
        }

        try {
            EventVaultCracking::create([
                'character_id' => $characterId,
                'amount' => $amount,
                'source' => mb_substr($source, 0, 50),
                'created_at' => now(),
            ]);
        } catch (\Throwable) {
            // Silently fail so gameplay is never interrupted
        }
    }

    /**
     * Get real-time leaderboard data for the vault code cracking challenge.
     *
     * @return array{
     *     is_active: bool,
     *     has_ended: bool,
     *     starts_at: string,
     *     ends_at: string,
     *     remaining_seconds: int,
     *     prize_stars: int,
     *     prize_vault_codes: int,
     *     leaderboard: array<int, array{
     *         rank: int,
     *         character_id: int,
     *         name: string,
     *         level: int,
     *         avatar_url: string,
     *         total_cracked: int,
     *         total_cracked_formatted: string
     *     }>,
     *     my_rank: ?array{
     *         rank: int,
     *         character_id: int,
     *         name: string,
     *         level: int,
     *         avatar_url: string,
     *         total_cracked: int,
     *         total_cracked_formatted: string
     *     },
     *     total_participants: int,
     *     total_codes_cracked: int,
     *     total_codes_cracked_formatted: string
     * }
     */
    public static function getLeaderboardData(?int $myCharacterId = null, int $limit = 15): array
    {
        // Auto-resolve winner once the event deadline has passed
        if (self::hasEnded() && ! GameNotification::where('type', self::NOTIFICATION_TYPE)->exists()) {
            try {
                self::resolveWinner();
            } catch (\Throwable $e) {
                Log::warning('EventVaultCrackingService auto-resolve failed: '.$e->getMessage());
            }
        }

        $start = Carbon::parse(self::EVENT_START);
        $end = Carbon::parse(self::EVENT_END);
        $now = now();

        $isActive = self::isActive();
        $hasEnded = self::hasEnded();
        $remainingSec = max(0, $end->timestamp - $now->timestamp);

        // Fetch aggregated cracked codes during the event window
        $rows = EventVaultCracking::select('character_id', DB::raw('SUM(amount) as total_cracked'))
            ->where('created_at', '>=', $start)
            ->where('created_at', '<=', $end)
            ->groupBy('character_id')
            ->orderByDesc('total_cracked')
            ->get();

        $totalParticipants = $rows->count();
        $totalCodesCracked = (int) $rows->sum('total_cracked');

        $charIds = $rows->pluck('character_id')->all();
        $characters = Character::whereIn('id', $charIds)
            ->get(['id', 'name', 'level', 'avatar', 'gender'])
            ->keyBy('id');

        $leaderboard = [];
        $rank = 1;
        $myRankData = null;

        foreach ($rows as $row) {
            $char = $characters->get($row->character_id);
            if (! $char) {
                continue;
            }

            $cracked = (int) $row->total_cracked;
            $entry = [
                'rank' => $rank,
                'character_id' => $char->id,
                'name' => $char->name,
                'level' => $char->level,
                'avatar_url' => $char->avatar_url,
                'total_cracked' => $cracked,
                'total_cracked_formatted' => number_format($cracked, 0, ',', '.').' codes',
            ];

            if ($rank <= $limit) {
                $leaderboard[] = $entry;
            }

            if ($myCharacterId && $char->id === $myCharacterId) {
                $myRankData = $entry;
            }

            $rank++;
        }

        // If player has not cracked anything yet
        if ($myCharacterId && ! $myRankData) {
            $myChar = Character::find($myCharacterId);
            if ($myChar) {
                $myRankData = [
                    'rank' => $totalParticipants + 1,
                    'character_id' => $myChar->id,
                    'name' => $myChar->name,
                    'level' => $myChar->level,
                    'avatar_url' => $myChar->avatar_url,
                    'total_cracked' => 0,
                    'total_cracked_formatted' => '0 codes',
                ];
            }
        }

        return [
            'is_active' => $isActive,
            'has_ended' => $hasEnded,
            'starts_at' => $start->format('d-m-Y H:i'),
            'ends_at' => 'Woensdag 23:59',
            'remaining_seconds' => $remainingSec,
            'prize_stars' => self::PRIZE_STARS,
            'prize_vault_codes' => self::PRIZE_VAULT_CODES,
            'leaderboard' => $leaderboard,
            'my_rank' => $myRankData,
            'total_participants' => $totalParticipants,
            'total_codes_cracked' => $totalCodesCracked,
            'total_codes_cracked_formatted' => number_format($totalCodesCracked, 0, ',', '.').' codes',
        ];
    }

    /**
     * Resolve the challenge and reward the #1 winner with 2,500 stars and 100 vault codes.
     */
    public static function resolveWinner(bool $force = false): ?array
    {
        $start = Carbon::parse(self::EVENT_START);
        $end = Carbon::parse(self::EVENT_END);

        // Guard against premature runs or double payouts
        if ((! $force && ! self::hasEnded()) || GameNotification::where('type', self::NOTIFICATION_TYPE)->exists()) {
            return null;
        }

        $topCracker = EventVaultCracking::select('character_id', DB::raw('SUM(amount) as total_cracked'))
            ->where('created_at', '>=', $start)
            ->where('created_at', '<=', $end)
            ->groupBy('character_id')
            ->orderByDesc('total_cracked')
            ->first();

        if (! $topCracker) {
            return null;
        }

        $winner = Character::with('user')->find($topCracker->character_id);
        if (! $winner) {
            return null;
        }

        $totalCracked = (int) $topCracker->total_cracked;

        return DB::transaction(function () use ($winner, $totalCracked): ?array {
            /** @var Character|null $lockedWinner */
            $lockedWinner = Character::where('id', $winner->id)->lockForUpdate()->first();
            if (! $lockedWinner) {
                return null;
            }

            // Re-check under row lock to ensure concurrency safety
            if (GameNotification::where('type', self::NOTIFICATION_TYPE)->exists()) {
                return null;
            }

            // Award 2,500 Stars
            $lockedWinner->stars = ($lockedWinner->stars ?? 0) + self::PRIZE_STARS;
            $lockedWinner->save();

            // Award 100 unique Vault Codes
            for ($i = 0; $i < self::PRIZE_VAULT_CODES; $i++) {
                VaultCode::create([
                    'code' => VaultCode::generateUniqueCode('EVENT'),
                    'reward_cash' => rand(3000, 7500),
                    'reward_stars' => (rand(1, 100) <= 20 ? 1 : 0),
                    'reward_tokens' => 2,
                    'character_id' => $lockedWinner->id,
                    'is_redeemed' => false,
                ]);
            }

            $crackedFmt = number_format($totalCracked, 0, ',', '.').' codes';
            $starsFmt = number_format(self::PRIZE_STARS, 0, ',', '.');
            $codesFmt = number_format(self::PRIZE_VAULT_CODES, 0, ',', '.');

            GameNotification::create([
                'character_id' => $lockedWinner->id,
                'type' => self::NOTIFICATION_TYPE,
                'icon' => 'fa-trophy',
                'title' => '🏆 GEFELICITEERD: Winnaar De Grote Kluiscode Challenge!',
                'message' => "Meesterkraker! Jij bent met {$crackedFmt} gekraakte kluiscodes op #1 geëindigd in De Grote Kluiscode Challenge en wint {$starsFmt} Sterren ⭐ én {$codesFmt} Kluiscodes 🔐!",
                'is_read' => false,
            ]);

            GameMessage::create([
                'recipient_id' => $lockedWinner->id,
                'sender_id' => null,
                'sender_name' => 'Directie TBS Kliniek',
                'subject' => "🏆 HOOFDPRIJS: {$starsFmt} Sterren & {$codesFmt} Kluiscodes voor De Grote Kluiscode Challenge!",
                'content' => "Beste {$lockedWinner->name},\n\nGefeliciteerd met je fantastische overwinning in 'De Grote Kluiscode Challenge'!\n\nTot woensdag 23:59 heb jij in totaal {$crackedFmt} kluiscodes succesvol gekraakt in de kluisterminal. Daarmee claim jij de onbetwiste 1e plaats!\n\nDe hoofdprijs van {$starsFmt} Sterren ⭐ én {$codesFmt} unieke Kluiscodes 🔐 is zojuist direct bijgeschreven op jouw account en kluisinventaris.\n\nMet criminele groet,\nDe Kliniekleiding",
                'type' => 'event',
            ]);

            // Broadcast in clinic chat
            try {
                app(ChatBotService::class)->broadcast("🏆 CHALLENGE WINNAAR: {$lockedWinner->name} heeft De Grote Kluiscode Challenge gewonnen met {$crackedFmt} gekraakte kluiscodes en wint {$starsFmt} Sterren ⭐ en {$codesFmt} Kluiscodes 🔐! Iedereen bedankt voor het meespelen!");
            } catch (\Throwable $e) {
                Log::warning('EventVaultCrackingService broadcast failed: '.$e->getMessage());
            }

            return [
                'winner' => $lockedWinner,
                'total_cracked' => $totalCracked,
            ];
        });
    }
}
