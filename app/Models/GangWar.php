<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class GangWar extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'started_at' => 'datetime',
        'ends_at' => 'datetime',
        'challenger_score' => 'integer',
        'defender_score' => 'integer',
        'war_pot' => 'integer',
        'respect_pot' => 'integer',
    ];

    public function challengerGang(): BelongsTo
    {
        return $this->belongsTo(Gang::class, 'challenger_gang_id');
    }

    public function defenderGang(): BelongsTo
    {
        return $this->belongsTo(Gang::class, 'defender_gang_id');
    }

    public function winnerGang(): BelongsTo
    {
        return $this->belongsTo(Gang::class, 'winner_gang_id');
    }

    /**
     * Check if this war is currently active and within the 24h window.
     */
    public function isActive(): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        if ($this->ends_at && $this->ends_at->isPast()) {
            return false;
        }

        return true;
    }

    /**
     * Seconds remaining in the 24-hour war.
     */
    public function getTimeRemainingSeconds(): int
    {
        if (! $this->ends_at) {
            return 0;
        }

        return max(0, (int) now()->diffInSeconds($this->ends_at, false));
    }

    /**
     * Award war score points to a participating gang.
     */
    public function addScore(int $gangId, int $points): void
    {
        if (! $this->isActive()) {
            return;
        }

        if ((int) $this->challenger_gang_id === $gangId) {
            $this->increment('challenger_score', $points);
        } elseif ((int) $this->defender_gang_id === $gangId) {
            $this->increment('defender_score', $points);
        }
    }

    /**
     * Settle the 24-hour gang war once the time has expired or upon manual resolution.
     */
    public function settle(): ?Gang
    {
        if ($this->status === 'finished') {
            return $this->winnerGang;
        }

        return DB::transaction(function () {
            $lockedWar = self::where('id', $this->id)->lockForUpdate()->first();
            if (! $lockedWar || $lockedWar->status === 'finished') {
                return $lockedWar?->winnerGang;
            }

            $challenger = Gang::where('id', $lockedWar->challenger_gang_id)->lockForUpdate()->first();
            $defender = Gang::where('id', $lockedWar->defender_gang_id)->lockForUpdate()->first();

            $winner = null;
            $loser = null;

            if ($lockedWar->challenger_score > $lockedWar->defender_score) {
                $winner = $challenger;
                $loser = $defender;
            } elseif ($lockedWar->defender_score > $lockedWar->challenger_score) {
                $winner = $defender;
                $loser = $challenger;
            }

            $respectReward = $lockedWar->respect_pot ?: 100;
            $cashStolen = 0;

            if ($winner && $loser) {
                $lockedWar->winner_gang_id = $winner->id;

                // Transfer 10% of losing lootable treasury (or minimum €250k up to lootable amount) to winner
                $loserLootable = max(0, (int) ($loser->lootable_treasury ?? $loser->treasury ?? 0));
                $transferAmount = $loserLootable > 0 ? max(250000, (int) round($loserLootable * 0.10)) : 0;
                $cashStolen = min($loserLootable, $transferAmount);

                $loser->treasury = max(0, ($loser->treasury ?? 0) - $cashStolen);
                if (Schema::hasColumn('gangs', 'respect')) {
                    $loser->respect = max(0, ($loser->respect ?? 0) - (int) round($respectReward / 2));
                }
                $loser->save();

                $winner->treasury = ($winner->treasury ?? 0) + $cashStolen + ($lockedWar->war_pot ?: 0);
                if (Schema::hasColumn('gangs', 'respect')) {
                    $winner->respect = ($winner->respect ?? 0) + $respectReward;
                }
                // Grant 24h Kliniek Heerschappij (+25% hourly wage for gang)
                $winner->war_dominance_until = now()->addHours(24);
                $winner->save();

                if ($winner->leader) {
                    $winner->leader->addRespect($respectReward);
                }

                $lockedWar->summary = "🏆 Overwinning voor {$winner->name}! Score: {$lockedWar->challenger_score} vs {$lockedWar->defender_score}. Buit: €".number_format($cashStolen + ($lockedWar->war_pot ?: 0), 0, ',', '.')." en +{$respectReward} Respect!";
            } else {
                // Onbeslist
                $lockedWar->summary = "Gelijkspel! Beide bendes hebben de strijd gestaakt met gelijke score ({$lockedWar->challenger_score} - {$lockedWar->defender_score}).";
            }

            $lockedWar->status = 'finished';
            $lockedWar->save();

            return $winner;
        });
    }

    /**
     * Find active war involving the given gang.
     */
    public static function findActiveForGang(int $gangId): ?self
    {
        return self::where(function ($q) use ($gangId) {
            $q->where('challenger_gang_id', $gangId)
                ->orWhere('defender_gang_id', $gangId);
        })
            ->where('status', 'active')
            ->latest('id')
            ->first();
    }

    /**
     * Check if two gangs are currently at war with each other.
     */
    public static function areGangsAtWar(int $gangA, int $gangB): ?self
    {
        return self::where('status', 'active')
            ->where('ends_at', '>', now())
            ->where(function ($q) use ($gangA, $gangB) {
                $q->where(function ($sub) use ($gangA, $gangB) {
                    $sub->where('challenger_gang_id', $gangA)
                        ->where('defender_gang_id', $gangB);
                })->orWhere(function ($sub) use ($gangA, $gangB) {
                    $sub->where('challenger_gang_id', $gangB)
                        ->where('defender_gang_id', $gangA);
                });
            })
            ->first();
    }
}
