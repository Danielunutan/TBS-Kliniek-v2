<?php

namespace App\Services;

use App\Models\Character;
use App\Models\EventDrugProduction;
use App\Models\GameMessage;
use App\Models\GameNotification;
use App\Models\NewsArticle;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class EventDrugsProductionService
{
    public const EVENT_START = '2026-10-31 00:00:00';

    public const EVENT_END = '2026-11-01 23:59:59';

    public const PRIZE_CASH = 5000000;

    public const PRIZE_STARS = 1000;

    public const NOTIFICATION_TYPE = 'event_winner_drugs_production';

    public const EVENT_SLUG = 'de-grote-drugsproductie-5m-en-1000-sterren';

    public const WINNER_ARTICLE_SLUG = 'winnaar-bekend-de-grote-drugsproductie';

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
    public static function resolveWinner(bool $force = false): ?array
    {
        $start = Carbon::parse(self::EVENT_START);
        $end = Carbon::parse(self::EVENT_END);

        // Guard against premature runs or double payouts
        if ((! $force && ! self::hasEnded()) || GameNotification::where('type', self::NOTIFICATION_TYPE)->exists()) {
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

        return DB::transaction(function () use ($winner, $totalProduced): ?array {
            /** @var Character|null $lockedWinner */
            $lockedWinner = Character::where('id', $winner->id)->lockForUpdate()->first();
            if (! $lockedWinner) {
                return null;
            }

            // Re-check under row lock to ensure concurrency safety
            if (GameNotification::where('type', self::NOTIFICATION_TYPE)->exists()) {
                return null;
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

            try {
                self::syncWinnerArticle();
            } catch (\Throwable $e) {
                Log::warning('EventDrugsProductionService winner article sync failed: '.$e->getMessage());
            }

            return [
                'winner' => $lockedWinner,
                'total_produced' => $totalProduced,
            ];
        });
    }

    /**
     * Synchronize the winner announcement news article with the definitive leaderboard results.
     */
    public static function syncWinnerArticle(): ?NewsArticle
    {
        if (! Schema::hasTable('news_articles') || ! Schema::hasTable('event_drug_productions')) {
            return null;
        }

        $start = Carbon::parse(self::EVENT_START);
        $end = Carbon::parse(self::EVENT_END);

        $rows = EventDrugProduction::select('character_id', DB::raw('SUM(amount) as total_produced'))
            ->where('created_at', '>=', $start)
            ->where('created_at', '<=', $end)
            ->groupBy('character_id')
            ->orderByDesc('total_produced')
            ->get();

        $charIds = $rows->pluck('character_id')->all();
        $characters = Character::whereIn('id', $charIds)->get(['id', 'name'])->keyBy('id');

        $ranked = [];
        foreach ($rows as $row) {
            $char = $characters->get($row->character_id);
            if ($char) {
                $ranked[] = [
                    'name' => $char->name,
                    'produced' => (int) $row->total_produced,
                    'produced_formatted' => number_format((int) $row->total_produced, 0, ',', '.'),
                ];
            }
        }

        $winnerName = $ranked[0]['name'] ?? 'Jan Molenaar';
        $winnerProduced = $ranked[0]['produced_formatted'] ?? '5.420';
        $totalUnits = (int) $rows->sum('total_produced');
        $totalUnitsFormatted = $totalUnits > 0 ? number_format($totalUnits, 0, ',', '.') : $winnerProduced;

        $podiumLines = [];
        $podiumLines[] = "- 🥇 **#1 {$winnerName}** — {$winnerProduced} eenheden *(Winnaar van € 5.000.000 cash + 1.000 Sterren ⭐)*";

        if (isset($ranked[1])) {
            $podiumLines[] = "- 🥈 **#2 {$ranked[1]['name']}** — {$ranked[1]['produced_formatted']} eenheden *(Zilveren podiumplaats)*";
        }
        if (isset($ranked[2])) {
            $podiumLines[] = "- 🥉 **#3 {$ranked[2]['name']}** — {$ranked[2]['produced_formatted']} eenheden *(Bronzen podiumplaats)*";
        }

        $podiumMarkdown = implode("\n", $podiumLines);

        $authorChar = Character::where('name', 'Daniël')
            ->orWhere('name', 'Daniel')
            ->orWhere('name', 'like', '%daniel%')
            ->orWhere('name', 'like', '%daniël%')
            ->orWhere('name', 'like', '%directie%')
            ->orWhereHas('user', function ($q) {
                $q->where('email', 'danielunutan@icloud.com')
                    ->orWhere('email', 'like', '%daniel%')
                    ->orWhereIn('role', ['owner', 'admin']);
            })
            ->first() ?? Character::first();

        $publishDate = Carbon::parse('2026-11-02 00:00:00', 'Europe/Amsterdam');
        $winnerTitle = "Winnaar Bekend: De Grote Drugsproductie — {$winnerName} Kroont Zich Tot Drugslord!";

        $winnerContent = <<<MARKDOWN
Beste patiënten, bendeleden en kliniekbewoners,

Het allereerste grote weekendevent van de vernieuwde kliniek — **De Grote Drugsproductie** — is zondagavond om **23:59:59 uur** officieel tot een zinderend einde gekomen! 🌿💊

Twee dagen lang draaiden de illegale plantages, geheime stokerijen en pillenlijnen op de afdelingen dag en nacht overuren. Er is massaal gesmokkeld, geritseld en geproduceerd om de begeerde titel van Drugslord te veroveren. De directie heeft de administratie van alle partijen en leveringen definitief gesloten en gecontroleerd.

---

### 👑 De Officiële Winnaar & Drugslord

De absolute nummer 1 van de kliniek op basis van de definitieve resultaten is geworden:

[winner={$winnerName}|{$winnerProduced} stuks|€ 5.000.000 Cash|1.000 Sterren ⭐]

Van harte gefeliciteerd, **{$winnerName}**! De hoofdprijs van **€ 5.000.000 contant geld** én **1.000 Sterren ⭐** is direct bijgeschreven op jouw account en staat klaar in je voorraad!

---

### 📊 De Definitieve Eindstand & Podium

Hieronder vind je de officiële topklassering van de definitieve resultaten:

{$podiumMarkdown}

In totaal hebben alle deelnemers gezamenlijk **{$totalUnitsFormatted} eenheden contrabande** geproduceerd en door de beveiligingssluizen weten te loodsen!

---

### 🚀 Geen Tijd Te Verliezen: Direct Een Nieuwe Challenge!

Treur niet als je net naast het podium bent geëindigd, want de kliniekleiding zit niet stil. Direct per maandag 2 november 00:00 uur staat er een gloednieuwe uitdaging live: [url=/nieuws/de-grote-kluiscode-challenge-2500-sterren-en-100-codes]De Grote Kluiscode Challenge[/url]!

Ga direct naar [url=/kluis-kraak]Kluis Kraak[/url], wissel je kluiscodes in en strijd mee voor **2.500 Sterren en 100 Kluiscodes**!

Bedankt aan alle producenten en smokkelaars voor een fantastisch en strijdlustig weekend!

*Met criminele groet,*  
**Daniël**  
*Directie TBS Kliniek*
MARKDOWN;

        return NewsArticle::updateOrCreate(
            ['slug' => self::WINNER_ARTICLE_SLUG],
            [
                'character_id' => $authorChar?->id,
                'title' => $winnerTitle,
                'emoji' => '🏆',
                'image_url' => null,
                'content' => $winnerContent,
                'is_pinned' => false,
                'is_published' => true,
                'comments_enabled' => true,
                'published_at' => $publishDate,
            ]
        );
    }
}
