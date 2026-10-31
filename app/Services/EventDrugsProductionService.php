<?php

namespace App\Services;

use App\Models\Character;
use App\Models\EventDrugProduction;
use App\Models\GameMessage;
use App\Models\GameNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EventDrugsProductionService
{
    public const EVENT_START = '2026-10-31 00:00:00';

    public const EVENT_END = '2026-11-01 23:59:59';

    public const PRIZE_CASH = 5000000;

    public const PRIZE_STARS = 1000;

    public const NOTIFICATION_TYPE = 'event_winner_drugs_production';

    public const EVENT_SLUG = 'de-grote-drugsproductie-5m-en-1000-sterren';

    /**
     * Check whether the drugs production event is currently active.
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
     * Record drugs produced by a character if the event is active.
     */
    public static function record(Character|int $character, int $amount, string $drugType = 'drugs'): void
    {
        if ($amount <= 0 || ! self::isActive()) {
            return;
        }

        $characterId = $character instanceof Character ? $character->id : (int) $character;
        if (! $characterId) {
            return;
        }

        try {
            EventDrugProduction::create([
                'character_id' => $characterId,
                'amount' => $amount,
                'drug_type' => mb_substr($drugType, 0, 50),
                'created_at' => now(),
            ]);
        } catch (\Throwable) {
            // Silently fail so gameplay is never interrupted
        }
    }

    /**
     * Get real-time leaderboard data for the drugs production event.
     *
     * @return array{
     *     is_active: bool,
     *     has_ended: bool,
     *     starts_at: string,
     *     ends_at: string,
     *     remaining_seconds: int,
     *     prize_cash: int,
     *     prize_cash_formatted: string,
     *     prize_stars: int,
     *     leaderboard: array,
     *     my_rank: ?array,
     *     total_participants: int,
     *     total_drugs_produced: int,
     *     total_drugs_produced_formatted: string
     * }
     */
    public static function getLeaderboardData(?int $myCharacterId = null, int $limit = 15): array
    {
        // Auto-resolve winner once the event deadline has passed
        if (self::hasEnded() && ! GameNotification::where('type', self::NOTIFICATION_TYPE)->exists()) {
            try {
                self::resolveWinner();
            } catch (\Throwable $e) {
                Log::warning('EventDrugsProductionService auto-resolve failed: '.$e->getMessage());
            }
        }

        $start = Carbon::parse(self::EVENT_START);
        $end = Carbon::parse(self::EVENT_END);
        $now = now();

        $isActive = self::isActive();
        $hasEnded = self::hasEnded();
        $remainingSec = max(0, $end->timestamp - $now->timestamp);

        // Fetch aggregated drugs produced during the event period
        $rows = EventDrugProduction::select('character_id', DB::raw('SUM(amount) as total_produced'))
            ->where('created_at', '>=', $start)
            ->where('created_at', '<=', $end)
            ->groupBy('character_id')
            ->orderByDesc('total_produced')
            ->get();

        $totalParticipants = $rows->count();
        $totalDrugsProduced = (int) $rows->sum('total_produced');

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

            $produced = (int) $row->total_produced;
            $entry = [
                'rank' => $rank,
                'character_id' => $char->id,
                'name' => $char->name,
                'level' => $char->level,
                'avatar_url' => $char->avatar_url,
                'total_produced' => $produced,
                'total_produced_formatted' => number_format($produced, 0, ',', '.').' stuks',
            ];

            if ($rank <= $limit) {
                $leaderboard[] = $entry;
            }

            if ($myCharacterId && $char->id === $myCharacterId) {
                $myRankData = $entry;
            }

            $rank++;
        }

        // If player has not produced anything yet
        if ($myCharacterId && ! $myRankData) {
            $myChar = Character::find($myCharacterId);
            if ($myChar) {
                $myRankData = [
                    'rank' => $totalParticipants + 1,
                    'character_id' => $myChar->id,
                    'name' => $myChar->name,
                    'level' => $myChar->level,
                    'avatar_url' => $myChar->avatar_url,
                    'total_produced' => 0,
                    'total_produced_formatted' => '0 stuks',
                ];
            }
        }

        return [
            'is_active' => $isActive,
            'has_ended' => $hasEnded,
            'starts_at' => $start->format('d-m-Y H:i'),
            'ends_at' => 'Zondag 23:59',
            'remaining_seconds' => $remainingSec,
            'prize_cash' => self::PRIZE_CASH,
            'prize_cash_formatted' => '€'.number_format(self::PRIZE_CASH, 0, ',', '.'),
            'prize_stars' => self::PRIZE_STARS,
            'leaderboard' => $leaderboard,
            'my_rank' => $myRankData,
            'total_participants' => $totalParticipants,
            'total_drugs_produced' => $totalDrugsProduced,
            'total_drugs_produced_formatted' => number_format($totalDrugsProduced, 0, ',', '.').' stuks',
        ];
    }

    /**
     * Resolve the event and reward the #1 winner with €5,000,000 cash and 1,000 stars.
     */
    public static function resolveWinner(): ?array
    {
        $start = Carbon::parse(self::EVENT_START);
        $end = Carbon::parse(self::EVENT_END);

        // Guard against premature runs or double payouts
        if (! self::hasEnded() || GameNotification::where('type', self::NOTIFICATION_TYPE)->exists()) {
            return null;
        }

        $topProducer = EventDrugProduction::select('character_id', DB::raw('SUM(amount) as total_produced'))
            ->where('created_at', '>=', $start)
            ->where('created_at', '<=', $end)
            ->groupBy('character_id')
            ->orderByDesc('total_produced')
            ->first();

        if (! $topProducer) {
            return null;
        }

        $winner = Character::with('user')->find($topProducer->character_id);
        if (! $winner) {
            return null;
        }

        $totalProduced = (int) $topProducer->total_produced;

        return DB::transaction(function () use ($winner, $totalProduced): array {
            // Re-check under transaction to ensure concurrency safety
            if (GameNotification::where('type', self::NOTIFICATION_TYPE)->exists()) {
                return [];
            }

            /** @var Character $lockedWinner */
            $lockedWinner = Character::where('id', $winner->id)->lockForUpdate()->first();
            if (! $lockedWinner) {
                return [];
            }

            $lockedWinner->cash = ($lockedWinner->cash ?? 0) + self::PRIZE_CASH;
            $lockedWinner->stars = ($lockedWinner->stars ?? 0) + self::PRIZE_STARS;
            $lockedWinner->save();

            $producedFmt = number_format($totalProduced, 0, ',', '.').' stuks';
            $cashFmt = '€'.number_format(self::PRIZE_CASH, 0, ',', '.');
            $starsFmt = number_format(self::PRIZE_STARS, 0, ',', '.');

            GameNotification::create([
                'character_id' => $lockedWinner->id,
                'type' => self::NOTIFICATION_TYPE,
                'icon' => 'fa-trophy',
                'title' => '🏆 GEFELICITEERD: Winnaar De Grote Drugsproductie!',
                'message' => "Geweldig gedaan! Jij bent met {$producedFmt} geproduceerde drugs op #1 geëindigd in De Grote Drugsproductie en wint de hoofdprijs van {$cashFmt} contant geld en {$starsFmt} Sterren ⭐!",
                'is_read' => false,
            ]);

            GameMessage::create([
                'recipient_id' => $lockedWinner->id,
                'sender_id' => null,
                'sender_name' => 'Directie TBS Kliniek',
                'subject' => "🏆 HOOFDPRIJS: {$cashFmt} & {$starsFmt} Sterren voor De Grote Drugsproductie!",
                'content' => "Beste {$lockedWinner->name},\n\nGefeliciteerd met je fantastische overwinning in het weekendevent 'De Grote Drugsproductie'!\n\nTot zondag 23:59 heb jij in totaal {$producedFmt} drugs geproduceerd en de kliniek voorzien van ongekende voorraden. Daarmee claim jij de onbetwiste 1e plaats!\n\nDe hoofdprijs van {$cashFmt} contant geld én {$starsFmt} Sterren ⭐ is zojuist direct bijgeschreven op jouw account.\n\nMet criminele groet,\nDe Kliniekleiding",
                'type' => 'event',
            ]);

            // Broadcast in clinic chat
            try {
                app(ChatBotService::class)->broadcast("🏆 EVENT WINNAAR: {$lockedWinner->name} heeft het event De Grote Drugsproductie gewonnen met {$producedFmt} geproduceerde drugs en wint {$cashFmt} cash en {$starsFmt} Sterren ⭐! Iedereen bedankt voor het meespelen!");
            } catch (\Throwable $e) {
                Log::warning('EventDrugsProductionService broadcast failed: '.$e->getMessage());
            }

            return [
                'winner' => $lockedWinner,
                'total_produced' => $totalProduced,
            ];
        });
    }
}
