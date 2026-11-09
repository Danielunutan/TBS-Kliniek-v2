<?php

namespace App\Services;

use App\Models\Character;
use App\Models\EventCashEarning;
use App\Models\GameMessage;
use App\Models\GameNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EventCashHuntService
{
    public const EVENT_START = '2026-10-29 16:30:00';

    public const EVENT_END = '2026-10-30 00:00:00';

    public const PRIZE_STARS = 750;

    public const NOTIFICATION_TYPE = 'event_winner_cash_hunt';

    /**
     * Check whether the cash hunt event is currently active.
     */
    public static function isActive(): bool
    {
        $now = now();
        $start = Carbon::parse(self::EVENT_START);
        $end = Carbon::parse(self::EVENT_END);

        return $now->greaterThanOrEqualTo($start) && $now->lessThan($end);
    }

    /**
     * Check whether the event has finished.
     */
    public static function hasEnded(): bool
    {
        return now()->greaterThanOrEqualTo(Carbon::parse(self::EVENT_END));
    }

    /**
     * Record cash earned by a character if the event is active.
     */
    public static function record(Character|int $character, int $amount, string $source = 'in_game'): void
    {
        if ($amount <= 0 || ! self::isActive()) {
            return;
        }

        $charModel = $character instanceof Character ? $character : Character::with('user')->find((int) $character);
        if (! $charModel || $charModel->isGameExempt()) {
            return;
        }

        $characterId = $charModel->id;

        try {
            EventCashEarning::create([
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
     * Get the real-time leaderboard data.
     *
     * @return array{
     *     is_active: bool,
     *     has_ended: bool,
     *     starts_at: string,
     *     ends_at: string,
     *     remaining_seconds: int,
     *     prize_stars: int,
     *     leaderboard: array,
     *     my_rank: ?array,
     *     total_participants: int,
     *     total_cash_generated: int
     * }
     */
    public static function getLeaderboardData(?int $myCharacterId = null, int $limit = 15): array
    {
        $start = Carbon::parse(self::EVENT_START);
        $end = Carbon::parse(self::EVENT_END);
        $now = now();

        // Auto-resolve winner once the event deadline has passed
        if (self::hasEnded() && ! GameNotification::whereIn('type', [self::NOTIFICATION_TYPE, 'event_winner'])->where('created_at', '>=', $end)->exists()) {
            try {
                self::resolveWinner();
            } catch (\Throwable $e) {
                Log::warning('EventCashHuntService auto-resolve failed: '.$e->getMessage());
            }
        }

        $isActive = self::isActive();
        $hasEnded = self::hasEnded();
        $remainingSec = max(0, $end->timestamp - $now->timestamp);

        // Fetch aggregated earnings for the event period with deterministic tie-breaking
        $rows = EventCashEarning::select('character_id', DB::raw('SUM(amount) as total_earned'), DB::raw('MIN(created_at) as first_earned_at'))
            ->where('created_at', '>=', $start)
            ->where('created_at', '<', $end)
            ->whereHas('character', function ($q) {
                $q->playable();
            })
            ->groupBy('character_id')
            ->orderByDesc('total_earned')
            ->orderBy('first_earned_at', 'asc')
            ->get();

        $totalParticipants = $rows->count();
        $totalCashGenerated = (int) $rows->sum('total_earned');

        // Load character details for all earners (excluding system accounts)
        $charIds = $rows->pluck('character_id')->all();
        $characters = Character::whereIn('id', $charIds)
            ->playable()
            ->get(['id', 'name', 'level', 'avatar', 'avatar_custom', 'gender'])
            ->keyBy('id');

        $leaderboard = [];
        $rank = 1;
        $myRankData = null;

        foreach ($rows as $row) {
            $char = $characters->get($row->character_id);
            if (! $char) {
                continue;
            }

            $earned = (int) $row->total_earned;
            $entry = [
                'rank' => $rank,
                'character_id' => $char->id,
                'name' => $char->name,
                'level' => $char->level,
                'avatar_url' => $char->avatar_url,
                'total_earned' => $earned,
                'total_earned_formatted' => '€'.number_format($earned, 0, ',', '.'),
            ];

            if ($rank <= $limit) {
                $leaderboard[] = $entry;
            }

            if ($myCharacterId && $char->id === $myCharacterId) {
                $myRankData = $entry;
            }

            $rank++;
        }

        // If player has not earned anything yet
        if ($myCharacterId && ! $myRankData) {
            $myChar = Character::with('user')->find($myCharacterId);
            if ($myChar && ! $myChar->isGameExempt()) {
                $myRankData = [
                    'rank' => $totalParticipants + 1,
                    'character_id' => $myChar->id,
                    'name' => $myChar->name,
                    'level' => $myChar->level,
                    'avatar_url' => $myChar->avatar_url,
                    'total_earned' => 0,
                    'total_earned_formatted' => '€0',
                ];
            }
        }

        return [
            'is_active' => $isActive,
            'has_ended' => $hasEnded,
            'starts_at' => $start->format('H:i'),
            'ends_at' => '00:00',
            'remaining_seconds' => $remainingSec,
            'prize_stars' => self::PRIZE_STARS,
            'leaderboard' => $leaderboard,
            'my_rank' => $myRankData,
            'total_participants' => $totalParticipants,
            'total_cash_generated' => $totalCashGenerated,
            'total_cash_generated_formatted' => '€'.number_format($totalCashGenerated, 0, ',', '.'),
        ];
    }

    /**
     * Resolve the event and reward the #1 winner with 750 stars.
     */
    public static function resolveWinner(bool $force = false): ?array
    {
        $start = Carbon::parse(self::EVENT_START);
        $end = Carbon::parse(self::EVENT_END);

        // The command runs every night, but the event ran only once. Pay the prize once, after the event has ended.
        // Without this check the same winner received 750 stars every night.
        if ((! $force && ! self::hasEnded()) || GameNotification::whereIn('type', [self::NOTIFICATION_TYPE, 'event_winner'])->where('created_at', '>=', $end)->exists()) {
            return null;
        }

        $topEarner = EventCashEarning::select('character_id', DB::raw('SUM(amount) as total_earned'), DB::raw('MIN(created_at) as first_earned_at'))
            ->where('created_at', '>=', $start)
            ->where('created_at', '<', $end)
            ->whereHas('character', function ($q) {
                $q->playable();
            })
            ->groupBy('character_id')
            ->orderByDesc('total_earned')
            ->orderBy('first_earned_at', 'asc')
            ->first();

        if (! $topEarner) {
            return null;
        }

        $winner = Character::with('user')->find($topEarner->character_id);
        if (! $winner) {
            return null;
        }

        $totalEarned = (int) $topEarner->total_earned;

        return DB::transaction(function () use ($winner, $totalEarned, $end): ?array {
            $lockedWinner = Character::where('id', $winner->id)->lockForUpdate()->first();
            if (! $lockedWinner) {
                return null;
            }

            if (GameNotification::whereIn('type', [self::NOTIFICATION_TYPE, 'event_winner'])->where('created_at', '>=', $end)->exists()) {
                return null;
            }

            $lockedWinner->stars = ($lockedWinner->stars ?? 0) + self::PRIZE_STARS;
            $lockedWinner->save();

            $earnedFmt = '€'.number_format($totalEarned, 0, ',', '.');

            GameNotification::create([
                'character_id' => $lockedWinner->id,
                'type' => self::NOTIFICATION_TYPE,
                'icon' => 'fa-trophy',
                'title' => '🏆 GEFELICITEERD: Winnaar De Grote Geldjacht!',
                'message' => "Geweldig gedaan! Jij bent met {$earnedFmt} aan inkomsten vanavond op #1 geëindigd in De Grote Geldjacht en wint de hoofdprijs van 750 Sterren ⭐!",
                'is_read' => false,
            ]);

            GameMessage::create([
                'recipient_id' => $lockedWinner->id,
                'sender_id' => null,
                'sender_name' => 'Directie TBS Kliniek',
                'subject' => '🏆 HOOFDPRIJS: 750 Sterren voor De Grote Geldjacht!',
                'content' => "Beste {$lockedWinner->name},\n\nGefeliciteerd met je fantastische overwinning in het avondevent 'De Grote Geldjacht'!\n\nTussen 16:30 en 00:00 uur heb jij met noeste arbeid, slimme hossels en harde overvallen in totaal {$earnedFmt} binnengehaald. Daarmee claim jij de onbetwiste 1e plaats!\n\nDe hoofdprijs van 750 Sterren ⭐ is zojuist direct bijgeschreven op jouw profiel.\n\nMet vriendelijke groet,\nDe Kliniekleiding",
                'type' => 'event',
            ]);

            // Broadcast in clinic chat
            try {
                app(ChatBotService::class)->broadcast("🏆 EVENT WINNAAR: {$lockedWinner->name} heeft vanavond De Grote Geldjacht gewonnen met {$earnedFmt} binnengehaald geld en wint 750 Sterren ⭐! Iedereen bedankt voor het meespelen!");
            } catch (\Throwable $e) {
                Log::warning('EventCashHuntService broadcast failed: '.$e->getMessage());
            }

            return [
                'winner' => $lockedWinner,
                'total_earned' => $totalEarned,
            ];
        });
    }
}
