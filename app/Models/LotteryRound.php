<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
     * Get or create current active lottery round.
     */
    public static function current(): self
    {
        /** @var self|null $current */
        $current = self::where('is_drawn', false)->orderByDesc('id')->first();

        if (! $current) {
            $latest = self::orderByDesc('id')->first();
            $nextRound = $latest ? $latest->round_number + 1 : 1;

            $current = self::create([
                'round_number' => $nextRound,
                'jackpot' => 25000,
                'tax_collected' => 0,
                'starts_at' => now(),
                'draws_at' => now()->addDays(14),
                'is_drawn' => false,
                'total_tickets_sold' => 0,
            ]);
        }

        return $current;
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
