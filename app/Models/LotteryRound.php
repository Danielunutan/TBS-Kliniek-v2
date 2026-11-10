<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LotteryRound extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'starts_at' => 'datetime',
        'draws_at' => 'datetime',
        'is_drawn' => 'boolean',
        'jackpot' => 'integer',
        'tax_collected' => 'integer',
        'winner_payout' => 'integer',
        'total_tickets_sold' => 'integer',
    ];

    public function tickets(): HasMany
    {
        return $this->hasMany(LotteryTicket::class);
    }

    public function winner(): BelongsTo
    {
        return $this->belongsTo(Character::class, 'winner_character_id');
    }

    /**
     * Determine if current round is in its last quarter (final 15 minutes before draw).
     */
    public function isLastQuarter(): bool
    {
        if (! $this->draws_at || $this->is_drawn) {
            return false;
        }

        $secondsLeft = now()->diffInSeconds($this->draws_at, false);

        return $secondsLeft > 0 && $secondsLeft <= 900;
    }

    /**
     * Determine if 80% discount action is currently active.
     */
    public function hasDiscount(): bool
    {
        return true;
    }

    /**
     * Get price per ticket in cash (80% discount: €2.000 instead of €10.000).
     */
    public function getCashPricePerTicket(): int
    {
        return $this->hasDiscount() || $this->isLastQuarter() ? 2000 : 10000;
    }

    /**
     * Get price per ticket in stars (80% discount: 3 stars instead of 15).
     */
    public function getStarsPricePerTicket(): int
    {
        return $this->hasDiscount() || $this->isLastQuarter() ? 3 : 15;
    }

    /**
     * Get or create current active lottery round.
     */
    public static function current(): self
    {
        // First check if an expired active round needs to be drawn
        $expired = self::where('is_drawn', false)
            ->whereNotNull('draws_at')
            ->where('draws_at', '<=', now())
            ->orderByDesc('id')
            ->first();

        if ($expired) {
            return $expired->executeDraw();
        }

        /** @var self|null $current */
        $current = self::where('is_drawn', false)->orderByDesc('id')->first();

        if (! $current) {
            $current = Cache::lock('lottery_round_creation_lock', 10)->block(5, function (): self {
                $existing = self::where('is_drawn', false)->orderByDesc('id')->first();
                if ($existing) {
                    return $existing;
                }

                $latest = self::orderByDesc('id')->first();
                $nextRound = $latest ? $latest->round_number + 1 : 1;

                return self::create([
                    'round_number' => $nextRound,
                    'jackpot' => 25000,
                    'tax_collected' => 0,
                    'starts_at' => now(),
                    'draws_at' => now()->addDays(14),
                    'is_drawn' => false,
                    'total_tickets_sold' => 0,
                ]);
            });
        }

        return $current;
    }

    /**
     * Execute the draw for this round, split payout 30% bank and 70% cash,
     * create the next round, and publish an official news article.
     */
    public function executeDraw(): self
    {
        return DB::transaction(function (): self {
            /** @var self|null $round */
            $round = self::lockForUpdate()->find($this->id);

            if (! $round || $round->is_drawn) {
                /** @var self $existingActive */
                $existingActive = self::where('is_drawn', false)->orderByDesc('id')->first()
                    ?? self::create([
                        'round_number' => ($round?->round_number ?? 1) + 1,
                        'jackpot' => 25000,
                        'tax_collected' => 0,
                        'starts_at' => now(),
                        'draws_at' => now()->addDays(14),
                        'is_drawn' => false,
                        'total_tickets_sold' => 0,
                    ]);

                return $existingActive;
            }

            /** @var LotteryTicket|null $winningTicket */
            $winningTicket = $round->tickets()->whereHas('character')->inRandomOrder()->first();

            if (! $winningTicket) {
                $nextRound = self::create([
                    'round_number' => $round->round_number + 1,
                    'jackpot' => $round->jackpot + 25000,
                    'tax_collected' => 0,
                    'starts_at' => now(),
                    'draws_at' => now()->addDays(14),
                    'is_drawn' => false,
                    'total_tickets_sold' => 0,
                ]);

                $round->update([
                    'is_drawn' => true,
                    'winner_character_id' => null,
                    'winner_payout' => 0,
                    'winning_ticket_number' => 'GEEN LOTEN',
                ]);

                self::publishRolloverNewsArticle($round, $nextRound);

                return $nextRound;
            }

            /** @var Character|null $winner */
            $winner = $winningTicket->character;

            $payout = $round->jackpot;
            $bankPayout = (int) round($payout * 0.30);
            $cashPayout = $payout - $bankPayout; // Exactly 70%

            if ($winner) {
                $winner->increment('bank', $bankPayout);
                $winner->increment('cash', $cashPayout);
            }

            $round->update([
                'is_drawn' => true,
                'winner_character_id' => $winner?->id,
                'winner_payout' => $payout,
                'winning_ticket_number' => $winningTicket->ticket_number,
            ]);

            $nextRound = self::create([
                'round_number' => $round->round_number + 1,
                'jackpot' => 25000,
                'tax_collected' => 0,
                'starts_at' => now(),
                'draws_at' => now()->addDays(14),
                'is_drawn' => false,
                'total_tickets_sold' => 0,
            ]);

            self::publishWinnerNewsArticle($round, $winner, $winningTicket, $payout, $bankPayout, $cashPayout, $nextRound);

            return $nextRound;
        });
    }

    /**
     * Publish official news article for lottery jackpot winner with photo box, payout details & new round announcement.
     */
    public static function publishWinnerNewsArticle(
        LotteryRound $round,
        ?Character $winner,
        LotteryTicket $winningTicket,
        int $payout,
        int $bankPayout,
        int $cashPayout,
        LotteryRound $nextRound
    ): void {
        $authorId = Character::where('id', 6)->exists() ? 6 : (Character::first()?->id ?? 1);
        $formattedPayout = '€'.number_format($payout, 0, ',', '.');
        $formattedBank = '€'.number_format($bankPayout, 0, ',', '.');
        $formattedCash = '€'.number_format($cashPayout, 0, ',', '.');
        $winnerName = e($winner ? $winner->name : 'Onbekend');
        $winnerAvatar = e($winner ? $winner->avatar_url : asset('assets/characters/avatar_male_1.jpg'));
        $winnerProfileUrl = e($winner ? url('/speler/'.urlencode($winner->name)) : '#');
        $winningTicketNumber = e((string) $winningTicket->ticket_number);
        $lotteryUrl = route('lottery.index');

        $html = <<<HTML
<div class="space-y-4">
    <p class="text-sm font-semibold text-slate-700 dark:text-slate-300">
        De langverwachte trekking van <strong>Afdelingsloterij Ronde #{$round->round_number}</strong> heeft zojuist plaatsgevonden! De zaalwachten hebben de kluis geopend en het winnende lotnummer getrokken uit alle ingezonden loten.
    </p>

    <!-- Winnaar Spotlight Kaart / Vakje -->
    <div class="my-5 p-5 rounded-2xl bg-gradient-to-br from-amber-500/10 via-yellow-500/5 to-purple-500/10 dark:from-amber-950/40 dark:via-yellow-950/20 dark:to-purple-950/30 border-2 border-amber-400 dark:border-amber-600 shadow-md">
        <div class="flex flex-col sm:flex-row items-center gap-5">
            <div class="relative shrink-0">
                <img src="{$winnerAvatar}" alt="{$winnerName}" class="w-24 h-24 sm:w-28 sm:h-28 rounded-2xl object-cover border-2 border-amber-400 shadow-md" onerror="this.src='/assets/characters/avatar_male_1.jpg'" />
                <div class="absolute -bottom-2 -right-2 w-8 h-8 rounded-full bg-amber-500 text-slate-950 flex items-center justify-center font-black text-sm shadow">
                    👑
                </div>
            </div>
            <div class="text-center sm:text-left space-y-1.5 flex-1">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-amber-500 text-slate-950 text-[10px] font-black uppercase tracking-wider">
                    🏆 Officiële Jackpot Winnaar
                </span>
                <h3 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white">
                    <a href="{$winnerProfileUrl}" class="hover:text-amber-500 underline decoration-amber-400 transition">
                        {$winnerName}
                    </a>
                </h3>
                <div class="text-xs text-slate-600 dark:text-slate-300 font-medium">
                    Winnend Lotnummer: <span class="font-mono font-black text-amber-600 dark:text-amber-400 bg-amber-100 dark:bg-amber-950 px-2 py-0.5 rounded">{$winningTicketNumber}</span>
                </div>
                <div class="pt-2 flex flex-wrap items-center justify-center sm:justify-start gap-2 text-xs">
                    <span class="px-3 py-1 rounded-xl bg-emerald-100 dark:bg-emerald-950/80 text-emerald-800 dark:text-emerald-300 font-extrabold border border-emerald-300 dark:border-emerald-700">
                        💰 Totale Winstpot: {$formattedPayout}
                    </span>
                </div>
            </div>
        </div>

        <!-- Uitbetaling Splitsing 30% Bank / 70% Contant -->
        <div class="mt-4 pt-4 border-t border-amber-200 dark:border-amber-800/60 grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
            <div class="p-3 rounded-xl bg-white/80 dark:bg-slate-900/80 border border-amber-200/60 dark:border-amber-900/40">
                <span class="text-slate-500 dark:text-slate-400 block font-bold text-[11px]">🏦 30% Gestort op Bank (Veilig)</span>
                <span class="text-sm font-black font-mono text-blue-600 dark:text-blue-400">{$formattedBank}</span>
            </div>
            <div class="p-3 rounded-xl bg-white/80 dark:bg-slate-900/80 border border-amber-200/60 dark:border-amber-900/40">
                <span class="text-slate-500 dark:text-slate-400 block font-bold text-[11px]">💵 70% Contant Uitbetaald (Direct Zakgeld)</span>
                <span class="text-sm font-black font-mono text-emerald-600 dark:text-emerald-400">{$formattedCash}</span>
            </div>
        </div>
    </div>

    <!-- Aankondiging Nieuwe Ronde -->
    <div class="p-4 rounded-xl bg-purple-50 dark:bg-purple-950/40 border border-purple-200 dark:border-purple-800 flex flex-col sm:flex-row items-center justify-between gap-4">
        <div>
            <h4 class="text-sm font-black text-purple-900 dark:text-purple-200 flex items-center gap-2">
                <span>🎟️ Nieuwe Trekking Geopend!</span>
                <span class="px-2 py-0.5 rounded-full bg-purple-600 text-white text-[10px]">Ronde #{$nextRound->round_number}</span>
            </h4>
            <p class="text-xs text-purple-700 dark:text-purple-300 mt-0.5">
                Ronde #{$nextRound->round_number} is per direct gestart met een verse startjackpot van €25.000! Alle bank- en leningbelasting vult direct weer de megapot.
            </p>
        </div>
        <a href="{$lotteryUrl}" class="px-4 py-2 rounded-xl bg-purple-600 hover:bg-purple-500 text-white font-black text-xs transition shadow shrink-0">
            Koop Direct Loten &raquo;
        </a>
    </div>
</div>
HTML;

        NewsArticle::create([
            'character_id' => $authorId,
            'title' => "🎉 Uitslag Loterij Ronde #{$round->round_number}: {$winnerName} pakt {$formattedPayout}!",
            'slug' => 'loterij-ronde-'.$round->round_number.'-uitslag-'.strtolower(Str::random(6)),
            'emoji' => '👑',
            'image_url' => null,
            'content' => $html,
            'is_pinned' => false,
            'comments_enabled' => true,
            'is_published' => true,
            'published_at' => now(),
        ]);
    }

    /**
     * Publish news article when no tickets were sold and jackpot rolled over.
     */
    public static function publishRolloverNewsArticle(LotteryRound $round, LotteryRound $nextRound): void
    {
        $authorId = Character::where('id', 6)->exists() ? 6 : (Character::first()?->id ?? 1);
        $formattedPot = '€'.number_format($nextRound->jackpot, 0, ',', '.');
        $lotteryUrl = route('lottery.index');

        $html = <<<HTML
<div class="space-y-3">
    <p class="text-sm font-semibold text-slate-700 dark:text-slate-300">
        In Ronde #{$round->round_number} van de Afdelingsloterij waren er geen loten verkocht. De jackpot rolt automatisch door naar de volgende ronde!
    </p>
    <div class="p-4 rounded-xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800">
        <h4 class="text-sm font-black text-amber-900 dark:text-amber-200">
            🔥 Jackpot Verhoogd naar {$formattedPot}!
        </h4>
        <p class="text-xs text-amber-700 dark:text-amber-300 mt-1">
            Ronde #{$nextRound->round_number} is per direct geopend. Wie pakt deze gigantische pot?
        </p>
        <a href="{$lotteryUrl}" class="inline-block mt-3 px-4 py-2 rounded-xl bg-amber-500 text-slate-950 font-black text-xs">
            Koop Nu Je Lot &raquo;
        </a>
    </div>
</div>
HTML;

        NewsArticle::create([
            'character_id' => $authorId,
            'title' => "🎰 Loterij Ronde #{$round->round_number}: Jackpot Rollover naar {$formattedPot}!",
            'slug' => 'loterij-ronde-'.$round->round_number.'-rollover-'.strtolower(Str::random(6)),
            'emoji' => '🎰',
            'image_url' => null,
            'content' => $html,
            'is_pinned' => false,
            'comments_enabled' => true,
            'is_published' => true,
            'published_at' => now(),
        ]);
    }

    /**
     * Add tax amount to current lottery jackpot.
     */
    public static function addTax(int $amount): void
    {
        if ($amount <= 0) {
            return;
        }

        $round = self::current();
        $round->increment('jackpot', $amount);
        $round->increment('tax_collected', $amount);
    }
}
