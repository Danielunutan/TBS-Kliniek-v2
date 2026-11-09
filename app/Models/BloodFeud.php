<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class BloodFeud
 *
 * @property int $id
 * @property int $attacker_id
 * @property int $victim_id
 * @property int|null $victim_gang_id
 * @property Carbon $expires_at
 * @property bool $is_avenged
 * @property int|null $avenged_by_id
 * @property Carbon|null $avenged_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class BloodFeud extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'expires_at' => 'datetime',
        'avenged_at' => 'datetime',
        'is_avenged' => 'boolean',
    ];

    public function attacker(): BelongsTo
    {
        return $this->belongsTo(Character::class, 'attacker_id');
    }

    public function victim(): BelongsTo
    {
        return $this->belongsTo(Character::class, 'victim_id');
    }

    public function victimGang(): BelongsTo
    {
        return $this->belongsTo(Gang::class, 'victim_gang_id');
    }

    public function avengedBy(): BelongsTo
    {
        return $this->belongsTo(Character::class, 'avenged_by_id');
    }

    public function isActive(): bool
    {
        return ! $this->is_avenged && $this->expires_at?->isFuture() === true;
    }

    /**
     * Scope for feuds that are active and can be avenged against the attacker.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_avenged', false)
            ->where('expires_at', '>', now());
    }

    /**
     * Check if a given player (or their gang) has an active blood feud claim against $targetId.
     */
    public static function hasClaimAgainst(Character $avenger, int $targetId): ?self
    {
        return self::active()
            ->where('attacker_id', $targetId)
            ->where(function (Builder $query) use ($avenger) {
                $query->where('victim_id', $avenger->id);
                if ($avenger->gang_id) {
                    $query->orWhere('victim_gang_id', $avenger->gang_id);
                }
            })
            ->latest()
            ->first();
    }

    /**
     * Check if an attacker has legitimate retaliation rights against a target.
     * Retaliation awards bonus cash, respect, and gang respect upon successful liquidation
     * (subject to the hard 15-level difference limit).
     */
    public static function canRetaliate(Character $avenger, Character|int $target): bool
    {
        $targetId = $target instanceof Character ? $target->id : (int) $target;
        $targetModel = $target instanceof Character ? $target : Character::find($targetId);

        if (! $targetModel || (int) $avenger->id === (int) $targetId) {
            return false;
        }

        // 1. Direct active blood feud claim
        if (self::hasClaimAgainst($avenger, $targetId) !== null) {
            return true;
        }

        // 2. Target has killed ANY member of avenger's gang (or avenger) in the last 7 days
        $hasKilledGangMemberRecently = self::where('attacker_id', $targetId)
            ->where(function (Builder $query) use ($avenger) {
                $query->where('victim_id', $avenger->id);
                if ($avenger->gang_id) {
                    $query->orWhere('victim_gang_id', $avenger->gang_id);
                }
            })
            ->where('created_at', '>=', now()->subDays(7))
            ->exists();

        if ($hasKilledGangMemberRecently) {
            return true;
        }

        // 3. Active 24-Hour Bendeoorlog between their gangs
        if ($avenger->gang_id && $targetModel->gang_id) {
            if (class_exists(GangWar::class) && GangWar::areGangsAtWar((int) $avenger->gang_id, (int) $targetModel->gang_id)) {
                return true;
            }
        }

        // 4. Target commanded a GangAttack on avenger's gang within the last 7 days
        if ($avenger->gang_id) {
            $hasAttackedGang = GangAttack::where('target_gang_id', $avenger->gang_id)
                ->where('character_id', $targetId)
                ->where('created_at', '>=', now()->subDays(7))
                ->exists();

            if ($hasAttackedGang) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get active feuds and recent grudges for display in the Hitman Bloedwraak banner.
     */
    public static function getActiveFeudsForCharacter(Character $character)
    {
        return self::where(function (Builder $query) use ($character) {
            $query->where('victim_id', $character->id);
            if ($character->gang_id) {
                $query->orWhere('victim_gang_id', $character->gang_id);
            }
        })
            ->where('created_at', '>=', now()->subDays(7))
            ->with(['attacker', 'victim'])
            ->latest()
            ->take(10)
            ->get();
    }
}
