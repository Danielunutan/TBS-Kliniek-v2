<?php

namespace App\Services;

use App\Models\Character;
use App\Models\EventHustleEntry;
use App\Models\GameMessage;
use App\Models\GameNotification;
use App\Models\NewsArticle;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EventHustleChallengeService
{
    public const TIMEZONE = 'Europe/Amsterdam';

    public const EVENT_START = '2026-11-09 17:00:00';

    public const EVENT_END = '2026-11-13 23:59:59';

    public const WINNER_PUBLISH_AT = '2026-11-14 00:00:00';

    public const PRIZE_STARS = 750;

    public const PRIZE_CASH = 150000000;

    public const QUALIFYING_HUSTLE_IDS = [4, 5, 6];

    public const NOTIFICATION_TYPE = 'event_winner_hustle_challenge';

    public const EVENT_SLUG = 'de-grote-hossel-challenge-750-sterren-150m';

    public const WINNER_ARTICLE_SLUG = 'winnaar-bekend-de-grote-hossel-challenge';

    /**
     * Check whether the hustle challenge is currently active.
     */
    public static function isActive(): bool
    {
        $now = now();
        $start = Carbon::parse(self::EVENT_START, self::TIMEZONE);
        $end = Carbon::parse(self::EVENT_END, self::TIMEZONE);

        return $now->greaterThanOrEqualTo($start) && $now->lessThanOrEqualTo($end);
    }

    /**
     * Check whether the challenge has finished.
     */
    public static function hasEnded(): bool
    {
        return now()->greaterThan(Carbon::parse(self::EVENT_END, self::TIMEZONE));
    }

    /**
     * Record a successful qualifying hustle turn if the event is active.
     */
    public static function record(Character|int $character, int $hustleId): void
    {
        if (! in_array($hustleId, self::QUALIFYING_HUSTLE_IDS, true) || ! self::isActive()) {
            return;
        }

        if ($character instanceof Character && in_array($character->name, Character::EXCLUDED_SYSTEM_NAMES, true)) {
            return;
        }

        $characterId = $character instanceof Character ? $character->id : (int) $character;
        if (! $characterId) {
            return;
        }

        try {
            EventHustleEntry::create([
                'character_id' => $characterId,
                'hustle_id' => $hustleId,
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('Fout bij registreren event hossel', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Get real-time leaderboard data for the hustle challenge.
     *
     * @return array{
     *     is_active: bool,
     *     has_ended: bool,
     *     starts_at: string,
     *     ends_at: string,
     *     remaining_seconds: int,
     *     prize_stars: int,
     *     prize_cash: int,
     *     prize_cash_formatted: string,
     *     leaderboard: array<int, array{
     *         rank: int,
     *         character_id: int,
     *         name: string,
     *         level: int,
     *         avatar_url: string,
     *         total_hustles: int,
     *         total_hustles_formatted: string
     *     }>,
     *     my_rank: ?array{
     *         rank: int,
     *         character_id: int,
     *         name: string,
     *         level: int,
     *         avatar_url: string,
     *         total_hustles: int,
     *         total_hustles_formatted: string
     *     },
     *     total_participants: int,
     *     total_hustles_completed: int,
     *     total_hustles_completed_formatted: string
     * }
     */
    public static function getLeaderboardData(?int $myCharacterId = null, int $limit = 15): array
    {
        if (self::hasEnded() && ! GameNotification::where('type', self::NOTIFICATION_TYPE)->exists()) {
            try {
                self::distributePrize();
            } catch (\Throwable $e) {
                Log::error('Fout bij automatische uitreiking hossel challenge', ['error' => $e->getMessage()]);
            }
        }

        $start = Carbon::parse(self::EVENT_START, self::TIMEZONE);
        $end = Carbon::parse(self::EVENT_END, self::TIMEZONE);
        $now = now();

        $isActive = self::isActive();
        $hasEnded = self::hasEnded();
        $remainingSec = max(0, $end->timestamp - $now->timestamp);

        $rows = EventHustleEntry::select('character_id', DB::raw('COUNT(*) as total_hustles'), DB::raw('MIN(created_at) as first_hustle_at'))
            ->where('created_at', '>=', $start)
            ->where('created_at', '<=', $end)
            ->whereHas('character', function ($q) {
                $q->whereNotIn('name', Character::EXCLUDED_SYSTEM_NAMES);
            })
            ->groupBy('character_id')
            ->orderByDesc('total_hustles')
            ->orderBy('first_hustle_at', 'asc')
            ->get();

        $totalParticipants = $rows->count();
        $totalHustlesCompleted = (int) $rows->sum('total_hustles');

        $charIds = $rows->pluck('character_id')->all();
        $characters = Character::whereIn('id', $charIds)
            ->whereNotIn('name', Character::EXCLUDED_SYSTEM_NAMES)
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

            $count = (int) $row->total_hustles;
            $entry = [
                'rank' => $rank,
                'character_id' => $char->id,
                'name' => $char->name,
                'level' => $char->level,
                'avatar_url' => $char->avatar_url,
                'total_hustles' => $count,
                'total_hustles_formatted' => number_format($count, 0, ',', '.').' beurten',
            ];

            if ($rank <= $limit) {
                $leaderboard[] = $entry;
            }

            if ($myCharacterId && $char->id === $myCharacterId) {
                $myRankData = $entry;
            }

            $rank++;
        }

        if ($myCharacterId && ! $myRankData) {
            $myChar = Character::find($myCharacterId);
            if ($myChar && ! in_array($myChar->name, Character::EXCLUDED_SYSTEM_NAMES, true)) {
                $myRankData = [
                    'rank' => null,
                    'character_id' => $myChar->id,
                    'name' => $myChar->name,
                    'level' => $myChar->level,
                    'avatar_url' => $myChar->avatar_url,
                    'total_hustles' => 0,
                    'total_hustles_formatted' => '0 beurten',
                ];
            }
        }

        return [
            'is_active' => $isActive,
            'has_ended' => $hasEnded,
            'starts_at' => $start->toIso8601String(),
            'ends_at' => $end->toIso8601String(),
            'remaining_seconds' => $remainingSec,
            'prize_stars' => self::PRIZE_STARS,
            'prize_cash' => self::PRIZE_CASH,
            'prize_cash_formatted' => '€'.number_format(self::PRIZE_CASH, 0, ',', '.'),
            'leaderboard' => $leaderboard,
            'my_rank' => $myRankData,
            'total_participants' => $totalParticipants,
            'total_hustles_completed' => $totalHustlesCompleted,
            'total_hustles_completed_formatted' => number_format($totalHustlesCompleted, 0, ',', '.'),
        ];
    }

    /**
     * Distribute the grand prize to the #1 player after the challenge has ended.
     */
    public static function distributePrize(): ?array
    {
        $start = Carbon::parse(self::EVENT_START, self::TIMEZONE);
        $end = Carbon::parse(self::EVENT_END, self::TIMEZONE);

        $topHustler = EventHustleEntry::select('character_id', DB::raw('COUNT(*) as total_hustles'), DB::raw('MIN(created_at) as first_hustle_at'))
            ->where('created_at', '>=', $start)
            ->where('created_at', '<=', $end)
            ->whereHas('character', function ($q) {
                $q->whereNotIn('name', Character::EXCLUDED_SYSTEM_NAMES);
            })
            ->groupBy('character_id')
            ->orderByDesc('total_hustles')
            ->orderBy('first_hustle_at', 'asc')
            ->first();

        if (! $topHustler) {
            return null;
        }

        $winner = Character::with('user')->find($topHustler->character_id);
        if (! $winner) {
            return null;
        }

        $totalHustles = (int) $topHustler->total_hustles;

        return DB::transaction(function () use ($winner, $totalHustles): ?array {
            /** @var Character|null $lockedWinner */
            $lockedWinner = Character::where('id', $winner->id)->lockForUpdate()->first();
            if (! $lockedWinner) {
                return null;
            }

            if (GameNotification::where('type', self::NOTIFICATION_TYPE)->exists()) {
                return null;
            }

            $lockedWinner->stars = ($lockedWinner->stars ?? 0) + self::PRIZE_STARS;
            $lockedWinner->bank = ($lockedWinner->bank ?? 0) + self::PRIZE_CASH;
            $lockedWinner->save();

            GameNotification::create([
                'character_id' => $lockedWinner->id,
                'type' => self::NOTIFICATION_TYPE,
                'icon' => 'fa-trophy',
                'title' => 'Hoofdprijs Grote Hossel Challenge Gewonnen!',
                'message' => 'Gefeliciteerd! Met '.number_format($totalHustles, 0, ',', '.').' succesvolle hosselbeurten heb je de eerste plaats behaald en 750 Gouden Sterren plus 150 miljoen euro contant gewonnen.',
            ]);

            GameMessage::create([
                'recipient_id' => $lockedWinner->id,
                'sender_name' => 'Kliniek Directie',
                'subject' => 'Officiële Uitreiking Grote Hossel Challenge',
                'content' => "Beste {$lockedWinner->name},\n\nNamens de voltallige kliniekleiding feliciteren wij jou van harte met het winnen van De Grote Hossel Challenge!\n\nMet maar liefst ".number_format($totalHustles, 0, ',', '.')." succesvolle hosselbeurten op de topklussen sta jij fier op nummer 1.\n\nJouw hoofdprijs is direct bijgeschreven op jouw account met 750 Gouden Sterren ⭐ en € 150.000.000 op jouw bankrekening 💰\n\nBedankt voor jouw inzet en gefeliciteerd met deze uitzonderlijke prestatie!\n\nMet vriendelijke groet,\nDaniel\nTBS Kliniek Leiding",
                'type' => 'reward',
            ]);

            return [
                'winner' => $lockedWinner,
                'total_hustles' => $totalHustles,
                'prize_stars' => self::PRIZE_STARS,
                'prize_cash' => self::PRIZE_CASH,
            ];
        });
    }

    /**
     * Create or update the announcement news article for the challenge.
     */
    public static function syncChallengeArticle(): ?NewsArticle
    {
        $authorChar = Character::where('name', 'Daniël')
            ->orWhere('name', 'Daniel')
            ->first() ?? Character::first();

        if (! $authorChar) {
            return null;
        }

        $title = 'SPOED EVENT De Grote Hossel Challenge - Win 750 Sterren + €150 Miljoen!';

        $content = "Beste patiënten en bewoners van de TBS Kliniek,\n\n"
            .'Vanaf nu organiseren wij opnieuw de grote Hossel Challenge die loopt van nu tot en met vrijdag 13 november 23.59 uur! '
            ."De speler die aan het einde van de race de allermeeste succesvolle hosselbeurten heeft neergezet op de zwaarste klussen, wint de ultieme hoofdprijs van maar liefst **750 Gouden Sterren ⭐ én €150.000.000 contant** op de bank!\n\n"
            ."[hustle_challenge_leaderboard]\n\n"
            ."### De Officiële Spelregels\n\n"
            ."Om te zorgen voor een eerlijke en bloedstollende strijd gelden er duidelijke regels.\n\n"
            ."#### Welke Hossels Tellen Mee?\n"
            ."Om het maximale risico en spanning te garanderen tellen **uitsluitend** de volgende 3 top hossels mee voor de ranglijst\n\n"
            ."1. **Recreatie Bookie (16 Energie)**\n"
            ."Gokpool organiseren in de recreatieruimte. Laat medepatiënten wedden op pingpong en darten met 15% huisvoordeel.\n"
            ."Slagingskans 59%, opbrengst €7.500 tot €16.500, +240 XP en +12 Respect.\n\n"
            ."2. **Shag Kartel (20 Energie)**\n"
            ."Kantine sigaretten kartel opzetten. Controleer de aanvoer van zware shag, vapes en aanstekers over alle afdelingen.\n"
            ."Slagingskans 49%, opbrengst €15.000 tot €32.000, +350 XP en +18 Respect.\n\n"
            ."3. **Kliniek Telecom (24 Energie)**\n"
            ."Illegale smartphone lijn verhuren. Verhuur een binnengesmokkelde mini-smartphone voor 15 minuten per belbeurt.\n"
            ."Slagingskans 42%, opbrengst €26.000 tot €65.000, +500 XP en +28 Respect.\n\n"
            ."*Let goed op, de lagere routineklussen zoals Bewaker Afleiden, Keukenpersoneel Omkopen en Temazepam Achteroverdrukken tellen NIET mee voor deze challenge. Alleen de 3 hierboven genoemde topklussen zijn voorzien van een speciaal Challenge Label in de werkplaats en registreren punten op de ranglijst.*\n\n"
            ."#### De Prijzenpot\n\n"
            ."De absolute nummer 1 aan het einde van de challenge ontvangt direct\n"
            ."- **750 Gouden Sterren ⭐** direct op je account bijgeschreven\n"
            ."- **€150.000.000 contant bankgeld 💰** gestort op je rekening\n"
            ."- Eeuwige roem en een vermelding in het officiële Afdelingsblad\n\n"
            ."#### Speelduur en Afsluiting\n"
            ."Het event start direct en eindigt exact op **vrijdag 13 november om 23.59 uur**. De live ranglijst hierboven update automatisch elke 15 seconden zodat je precies ziet wie er aan kop gaat.\n\n"
            ."Veel succes allemaal en moge de slimste hosselaar winnen!\n\n"
            ."Daniel\nTBS Kliniek Leiding";

        $article = NewsArticle::firstOrNew(['slug' => self::EVENT_SLUG]);
        $article->fill([
            'character_id' => $authorChar->id,
            'title' => $title,
            'slug' => self::EVENT_SLUG,
            'content' => $content,
            'is_pinned' => true,
            'is_published' => true,
            'comments_enabled' => true,
            'published_at' => now(),
        ]);
        $article->save();

        return $article;
    }
}
