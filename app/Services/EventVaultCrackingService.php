<?php

namespace App\Services;

use App\Models\Character;
use App\Models\EventVaultCracking;
use App\Models\GameMessage;
use App\Models\GameNotification;
use App\Models\NewsArticle;
use App\Models\VaultCode;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class EventVaultCrackingService
{
    public const TIMEZONE = 'Europe/Amsterdam';

    public const EVENT_START = '2026-11-02 00:00:00';

    public const EVENT_END = '2026-11-04 23:59:59';

    public const WINNER_PUBLISH_AT = '2026-11-05 00:00:00';

    public const PRIZE_STARS = 2500;

    public const PRIZE_VAULT_CODES = 100;

    public const NOTIFICATION_TYPE = 'event_winner_vault_cracking';

    public const EVENT_SLUG = 'de-grote-kluiscode-challenge-2500-sterren-en-100-codes';

    public const WINNER_ARTICLE_SLUG = 'winnaar-bekend-de-grote-kluiscode-challenge';

    /**
     * Check whether the vault code challenge is currently active.
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

        $start = Carbon::parse(self::EVENT_START, self::TIMEZONE);
        $end = Carbon::parse(self::EVENT_END, self::TIMEZONE);
        $now = now();

        $isActive = self::isActive();
        $hasEnded = self::hasEnded();
        $remainingSec = max(0, $end->timestamp - $now->timestamp);

        // Fetch aggregated cracked codes during the event window with deterministic tie-breaking
        $rows = EventVaultCracking::select('character_id', DB::raw('SUM(amount) as total_cracked'), DB::raw('MIN(created_at) as first_cracked_at'))
            ->where('created_at', '>=', $start)
            ->where('created_at', '<=', $end)
            ->groupBy('character_id')
            ->orderByDesc('total_cracked')
            ->orderBy('first_cracked_at', 'asc')
            ->get();

        $totalParticipants = $rows->count();
        $totalCodesCracked = (int) $rows->sum('total_cracked');

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
        $start = Carbon::parse(self::EVENT_START, self::TIMEZONE);
        $end = Carbon::parse(self::EVENT_END, self::TIMEZONE);

        // Guard against premature runs or double payouts
        if ((! $force && ! self::hasEnded()) || GameNotification::where('type', self::NOTIFICATION_TYPE)->exists()) {
            return null;
        }

        $topCracker = EventVaultCracking::select('character_id', DB::raw('SUM(amount) as total_cracked'), DB::raw('MIN(created_at) as first_cracked_at'))
            ->where('created_at', '>=', $start)
            ->where('created_at', '<=', $end)
            ->whereHas('character', function ($q) {
                $q->whereNotIn('name', Character::EXCLUDED_SYSTEM_NAMES);
            })
            ->groupBy('character_id')
            ->orderByDesc('total_cracked')
            ->orderBy('first_cracked_at', 'asc')
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

            try {
                self::syncWinnerArticle(true);
            } catch (\Throwable $e) {
                Log::warning('EventVaultCrackingService winner article sync failed: '.$e->getMessage());
            }

            return [
                'winner' => $lockedWinner,
                'total_cracked' => $totalCracked,
            ];
        });
    }

    /**
     * Synchronize the challenge explanation news article and its game links.
     */
    public static function syncChallengeArticle(): ?NewsArticle
    {
        if (! Schema::hasTable('news_articles')) {
            return null;
        }

        $authorChar = null;
        if (Schema::hasTable('characters')) {
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
        }

        $publishDate = Carbon::parse(self::EVENT_START, self::TIMEZONE);
        $challengeTitle = 'Nieuwe Challenge: De Grote Kluiscode Jacht — Win 2.500 Sterren & 100 Kluiscodes!';
        $challengeSlug = self::EVENT_SLUG;

        $challengeContent = <<<'MARKDOWN'
Beste patiënten, bendeleden en meesterkrakers van de TBS Kliniek,

Het slot van de beveiligde kluisdeuren rammelt en het kraakalarm staat op scherp! Direct na het afronden van het weekendevent starten we per **maandag 2 november 2026 om 00:00 uur** met een gloednieuwe, zenuwslopende competitie: **De Grote Kluiscode Challenge**! 🔐⚡

Er zijn de afgelopen weken honderden geheime codes, vouchers en kluiscombinaties verspreid door het complex. Wie bezit de vaardigheid, het geduld en de brute kracht om de meeste codes te kraken en er met de megabuit vandoor te gaan?

---

### ⏱️ Looptijd van de Challenge
- **Start:** Maandag 2 november 2026 om 00:00 uur stipt (per direct live!)
- **Eindtijd:** **Woensdag 4 november 2026 om 23:59:59 uur**
- **Belangrijke voorwaarde:** Alleen kluiscodes die **binnen deze looptijd** zijn gekraakt, tellen mee voor het klassement!

---

### 🏆 De Prijzenpot
Voor de absolute nummer 1 van de kliniek ligt er een ongeëvenaard prijzenpakket klaar:
- ⭐ **2.500 Exclusieve Sterren** (direct bijgeschreven op je profiel!)
- 🔐 **100 Geheime Kluiscodes** (direct toegevoegd aan je kluisinventaris vol met cash, sterren en tokens!)
- 👑 **De officiële eretitel 'Meesterkraker van de Kliniek'**

[quote]💡 Let op: Gebruik de bestaande prijzenpot van 2.500 sterren en 100 kluiscodes optimaal om je machtspositie in de kliniek definitief te vestigen![/quote]

---

### 🎯 Het Doel: Zoveel Mogelijk Kluiscodes Kraken!
Het doel van deze challenge is simpel maar genadeloos: **kraak zoveel mogelijk kluiscodes** tussen maandag 00:00 uur en woensdag 23:59 uur.

Hoe doe je mee en waar haal je kluiscodes vandaan?
- 🔑 **1. Codes Inwisselen & Kantoorkluis Kraken:** Ga naar [url=/kluis-kraak]Kluis Kraak[/url] en voer je kluiscodes in bij de **Kluiscode Decoder Terminal**, of raad de cijfercode van de zware kantoorkluis. Elke succesvol gekraakte code telt direct voor **+1 punt** op de ranglijst!
- 📦 **2. Koffers & Sporttassen Openen:** Breek onderschepte sporttassen en XXL koffers open bij [url=/pakketjes]Koffers & Pakketjes[/url] om extra kluiscodes buit te maken!
- 💻 **3. Cyber Hacking, Heists & Rad van Fortuin:** Verdien extra kluiscodes door de terminal te kraken bij [url=/hacking]Cyber Hacking[/url], overvallen te plegen bij [url=/heists]Grote Heists[/url], te draaien aan het [url=/rad-van-fortuin]Rad van Fortuin[/url] of op verlof te gaan bij [url=/behandeling]Behandeling & Verlof[/url].
- 🛒 **4. Winkel & Marktplaats:** Koop extra kluiscodes en bundels in de [url=/winkel]Kliniek Winkel[/url], rechtstreeks in [url=/kluis-kraak]Kluis Kraak[/url] of van medepatiënten op de [url=/marktplaats]Marktplaats[/url]!

---

### 📊 Live Ranglijst & Klassement
Hieronder zie je het realtime rankingboard van de challenge. De ranglijst wordt tijdens de hele challengeperiode **automatisch elke 15 seconden bijgewerkt**. Per speler zie je de actuele positie, spelersnaam en het totale aantal gekraakte kluiscodes, gerangschikt van hoog naar laag.

[vault_challenge_leaderboard]

---

Pak je koevoet, verzamel al je vouchers en kraak jezelf naar de top van de TBS Kliniek!

Veel succes aan alle deelnemers!

*Met criminele groet,*  
**Daniël**  
*Directie TBS Kliniek*
MARKDOWN;

        NewsArticle::where('is_pinned', true)
            ->where('slug', '!=', $challengeSlug)
            ->update(['is_pinned' => false]);

        $article = NewsArticle::updateOrCreate(
            ['slug' => $challengeSlug],
            [
                'character_id' => $authorChar?->id,
                'title' => $challengeTitle,
                'emoji' => '🔐',
                'image_url' => null,
                'content' => $challengeContent,
                'is_pinned' => true,
                'is_published' => true,
                'comments_enabled' => true,
                'published_at' => $publishDate,
                'created_at' => $publishDate,
            ]
        );

        if ($article->created_at?->format('Y-m-d H:i:s') !== $publishDate->format('Y-m-d H:i:s')) {
            $article->forceFill([
                'created_at' => $publishDate,
                'published_at' => $publishDate,
            ])->saveQuietly();
        }

        return $article;
    }

    /**
     * Publish the winner announcement news article once the vault challenge has ended.
     */
    public static function syncWinnerArticle(bool $force = false): ?NewsArticle
    {
        if ((! $force && ! self::hasEnded()) || ! Schema::hasTable('news_articles') || ! Schema::hasTable('event_vault_crackings')) {
            return null;
        }

        $start = Carbon::parse(self::EVENT_START, self::TIMEZONE);
        $end = Carbon::parse(self::EVENT_END, self::TIMEZONE);

        $rows = EventVaultCracking::select('character_id', DB::raw('SUM(amount) as total_cracked'), DB::raw('MIN(created_at) as first_cracked_at'))
            ->where('created_at', '>=', $start)
            ->where('created_at', '<=', $end)
            ->groupBy('character_id')
            ->orderByDesc('total_cracked')
            ->orderBy('first_cracked_at', 'asc')
            ->get();

        if ($rows->isEmpty()) {
            return null;
        }

        $charIds = $rows->pluck('character_id')->all();
        $characters = Character::whereIn('id', $charIds)->get(['id', 'name'])->keyBy('id');

        $ranked = [];
        foreach ($rows as $row) {
            $char = $characters->get($row->character_id);
            if ($char) {
                $cleanName = str_replace(['|', '[', ']', '<', '>'], '', $char->name);
                $ranked[] = [
                    'name' => $cleanName,
                    'cracked' => (int) $row->total_cracked,
                    'cracked_formatted' => number_format((int) $row->total_cracked, 0, ',', '.'),
                ];
            }
        }

        if (empty($ranked)) {
            return null;
        }

        $winnerName = $ranked[0]['name'];
        $winnerCracked = $ranked[0]['cracked_formatted'];
        $totalCodes = (int) $rows->sum('total_cracked');
        $totalCodesFormatted = number_format($totalCodes, 0, ',', '.');

        $podiumLines = [];
        $podiumLines[] = "- 🥇 **#1 {$winnerName}** — {$winnerCracked} gekraakte kluiscodes *(Winnaar van 2.500 Sterren ⭐ + 100 Kluiscodes 🔐)*";

        if (isset($ranked[1])) {
            $podiumLines[] = "- 🥈 **#2 {$ranked[1]['name']}** — {$ranked[1]['cracked_formatted']} gekraakte kluiscodes *(Zilveren podiumplaats)*";
        }
        if (isset($ranked[2])) {
            $podiumLines[] = "- 🥉 **#3 {$ranked[2]['name']}** — {$ranked[2]['cracked_formatted']} gekraakte kluiscodes *(Bronzen podiumplaats)*";
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

        $publishDate = Carbon::parse(self::WINNER_PUBLISH_AT, self::TIMEZONE);
        $winnerTitle = "Winnaar Bekend: De Grote Kluiscode Challenge — {$winnerName} Kroont Zich Tot Meesterkraker!";

        $winnerContent = <<<MARKDOWN
Beste patiënten, bendeleden en meesterkrakers,

Na drie dagen vol spanning bij de kluisdeuren is **De Grote Kluiscode Challenge** op woensdagavond om **23:59:59 uur** officieel tot een einde gekomen! 🔐⚡

De kluisadministratie heeft alle ingewisselde codes en gekraakte kluizen binnen de looptijd definitief geteld en gecontroleerd.

---

### 👑 De Officiële Winnaar & Meesterkraker

De absolute nummer 1 van de kliniek op basis van de definitieve resultaten is geworden:

[winner={$winnerName}|{$winnerCracked} codes|2.500 Sterren ⭐|100 Kluiscodes 🔐]

Van harte gefeliciteerd, **{$winnerName}**! De hoofdprijs van **2.500 Sterren ⭐** én **100 unieke Kluiscodes 🔐** is direct bijgeschreven op jouw account en staat klaar in [url=/kluis-kraak]Kluis Kraak[/url]!

---

### 📊 De Definitieve Eindstand & Podium

Hieronder vind je de officiële topklassering van de definitieve resultaten:

{$podiumMarkdown}

In totaal hebben alle deelnemers tijdens de challenge gezamenlijk **{$totalCodesFormatted} kluiscodes** weten te kraken!

Bedankt aan alle codekrakers voor een geweldige strijd!

*Met criminele groet,*  
**Daniël**  
*Directie TBS Kliniek*
MARKDOWN;

        $article = NewsArticle::updateOrCreate(
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
                'created_at' => $publishDate,
            ]
        );

        if ($article->created_at?->format('Y-m-d H:i:s') !== $publishDate->format('Y-m-d H:i:s')) {
            $article->forceFill([
                'created_at' => $publishDate,
                'published_at' => $publishDate,
            ])->saveQuietly();
        }

        return $article;
    }
}
