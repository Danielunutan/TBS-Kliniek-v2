<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GangNpc extends Model
{
    use HasFactory;

    /**
     * Standard contract duration for hired gang specialists (30 minutes).
     */
    public const CONTRACT_DURATION_MINUTES = 30;

    protected $guarded = ['id'];

    protected $casts = [
        'bonus_value' => 'integer',
        'hired_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function gang(): BelongsTo
    {
        return $this->belongsTo(Gang::class);
    }

    public function assignedProperty(): BelongsTo
    {
        return $this->belongsTo(GangProperty::class, 'assigned_property_id');
    }

    /**
     * Scope query to only currently active unexpired NPCs.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where(function (Builder $q) {
            $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
        });
    }

    /**
     * Determine if this NPC contract has expired.
     */
    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    /**
     * Determine if this NPC is currently active.
     */
    public function isActive(): bool
    {
        return ! $this->isExpired();
    }

    /**
     * Get remaining contract time in seconds.
     */
    public function secondsRemaining(): int
    {
        if (! $this->expires_at) {
            return self::CONTRACT_DURATION_MINUTES * 60;
        }

        return max(0, (int) now()->diffInSeconds($this->expires_at, false));
    }

    /**
     * Purge expired NPCs for a gang, clearing property assignments and notifying the leader.
     *
     * @return int Number of expired NPCs cleaned up
     */
    public static function purgeExpiredForGang(?Gang $gang): int
    {
        if (! $gang) {
            return 0;
        }

        $expired = self::where('gang_id', $gang->id)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->get();

        if ($expired->isEmpty()) {
            return 0;
        }

        $leaderId = $gang->leader_character_id;

        foreach ($expired as $npc) {
            if ($npc->assigned_property_id) {
                GangProperty::where('id', $npc->assigned_property_id)
                    ->where('gang_id', $gang->id)
                    ->update(['assigned_npc_key' => null]);
            }

            if ($leaderId && class_exists(GameNotification::class)) {
                GameNotification::create([
                    'character_id' => $leaderId,
                    'type' => 'gang_npc_expired',
                    'icon' => 'fa-user-clock',
                    'title' => "⚠️ NPC Contract Verlopen: {$npc->name}!",
                    'message' => "Het 30-minuten contract van {$npc->name} ({$npc->role_title}) is verlopen. Deze specialist heeft de bende verlaten. Huur direct een nieuwe specialist in via Panden & NPC!",
                ]);
            }

            $npc->delete();
        }

        return $expired->count();
    }
}
