<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class Character
 *
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property string $title
 * @property string $avatar
 * @property string|null $bio
 * @property string $status_message
 * @property int $level
 * @property int $xp
 * @property int $total_xp
 * @property int $cash
 * @property int $bank
 * @property int $energy
 * @property int $max_energy
 * @property Carbon $last_energy_refill_at
 * @property int $health
 * @property int $max_health
 * @property int $respect
 * @property int $strength
 * @property int $defense
 * @property int $stamina
 * @property int $intelligence
 * @property int $charisma
 * @property int $discipline
 * @property int $fights_won
 * @property int $fights_lost
 * @property int $thefts_succeeded
 * @property int $thefts_failed
 * @property int $missions_completed
 * @property int $prison_sentences
 * @property int $prison_escapes
 * @property bool $is_in_prison
 * @property Carbon|null $prison_until
 * @property string|null $prison_reason
 * @property int|null $gang_id
 * @property string $gang_role
 * @property Carbon|null $protection_until
 * @property Carbon|null $last_active_at
 * @property Carbon|null $last_gang_wage_at
 */
class Character extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $guarded = ['id'];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'last_energy_refill_at' => 'datetime',
        'last_gang_wage_at' => 'datetime',
        'last_allowance_at' => 'datetime',
        'prison_until' => 'datetime',
        'protection_until' => 'datetime',
        'last_active_at' => 'datetime',
        'is_in_prison' => 'boolean',
        'level' => 'integer',
        'xp' => 'integer',
        'total_xp' => 'integer',
        'cash' => 'integer',
        'bank' => 'integer',
        'energy' => 'integer',
        'max_energy' => 'integer',
        'health' => 'integer',
        'max_health' => 'integer',
        'vault_tokens' => 'integer',
        'stars' => 'integer',
        'unopened_bags' => 'integer',
        'xxl_cases' => 'integer',
        'daily_attacks_count' => 'integer',
        'claimed_level_rewards' => 'array',
        'strength' => 'integer',
        'defense' => 'integer',
        'stamina' => 'integer',
        'intelligence' => 'integer',
        'charisma' => 'integer',
        'discipline' => 'integer',
        'vip_points' => 'integer',
        'is_vip' => 'boolean',
        'vip_expires_at' => 'datetime',
        'last_vip_reward_at' => 'datetime',
        'username_changed_at' => 'datetime',
    ];

    /**
     * Check if character has active VIP status.
     */
    public function isVip(): bool
    {
        if (! $this->is_vip) {
            return false;
        }

        if ($this->vip_expires_at && $this->vip_expires_at->isPast()) {
            return false;
        }

        return true;
    }

    /**
     * Get number of attacks performed today (resets automatically at midnight).
     */
    public function getTodayAttacksCountAttribute(): int
    {
        if ($this->daily_attacks_date !== now()->format('Y-m-d')) {
            return 0;
        }

        return $this->daily_attacks_count ?? 0;
    }

    /**
     * Get remaining daily attacks out of 100 max.
     */
    public function getRemainingDailyAttacksAttribute(): int
    {
        return max(0, 100 - $this->today_attacks_count);
    }

    /**
     * Get received messages.
     */
    public function receivedMessages(): HasMany
    {
        return $this->hasMany(GameMessage::class, 'recipient_id')->latest();
    }

    /**
     * Get sent messages.
     */
    public function sentMessages(): HasMany
    {
        return $this->hasMany(GameMessage::class, 'sender_id')->latest();
    }

    /**
     * Get the user that owns the character.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the gang the character belongs to.
     */
    public function gang(): BelongsTo
    {
        return $this->belongsTo(Gang::class);
    }

    /**
     * Get completed missions records.
     */
    public function missions(): HasMany
    {
        return $this->hasMany(CharacterMission::class);
    }

    /**
     * Get guestbook entries for this character.
     */
    public function guestbookEntries(): HasMany
    {
        return $this->hasMany(GuestbookEntry::class, 'profile_character_id')->latest();
    }

    public function friends(): HasMany
    {
        return $this->hasMany(Friend::class);
    }

    public function timelinePosts(): HasMany
    {
        return $this->hasMany(TimelinePost::class)->latest();
    }

    /**
     * Get notifications.
     */
    public function notifications(): HasMany
    {
        return $this->hasMany(GameNotification::class)->latest();
    }

    /**
     * Get character items in inventory.
     */
    public function items(): HasMany
    {
        return $this->hasMany(CharacterItem::class);
    }

    /**
     * Get vault codes owned by this character.
     */
    public function vaultCodes(): HasMany
    {
        return $this->hasMany(VaultCode::class, 'character_id');
    }

    /**
     * Get unredeemed vault codes owned by this character.
     */
    public function unredeemedVaultCodes(): HasMany
    {
        return $this->hasMany(VaultCode::class, 'character_id')->where('is_redeemed', false);
    }

    /**
     * Check if currently serving prison time.
     */
    public function isImprisoned(): bool
    {
        $until = $this->prison_until;
        $hasTime = $until && now()->lt($until);

        if (! $this->is_in_prison && ! $hasTime) {
            return false;
        }

        if ($hasTime) {
            return true;
        }

        $this->update([
            'is_in_prison' => false,
            'prison_until' => null,
            'prison_reason' => null,
        ]);

        return false;
    }

    /**
     * Get remaining prison time in seconds.
     */
    public function remainingPrisonSeconds(): int
    {
        $until = $this->prison_until;
        if (! $until) {
            return 0;
        }

        $secs = (int) now()->diffInSeconds($until, false);
        if ($secs <= 0) {
            if ($this->is_in_prison) {
                $this->update([
                    'is_in_prison' => false,
                    'prison_until' => null,
                    'prison_reason' => null,
                ]);
            }

            return 0;
        }

        return $secs;
    }

    /**
     * Get remaining prison time in minutes.
     */
    public function remainingPrisonMinutes(): int
    {
        $secs = $this->remainingPrisonSeconds();

        return (int) ceil($secs / 60);
    }

    /**
     * Check if currently under voluntary isolation protection.
     */
    public function isProtected(): bool
    {
        return $this->protection_until && now()->lt($this->protection_until);
    }

    /**
     * Get full avatar URL or default fallback.
     */
    public function getAvatarUrlAttribute(): string
    {
        if (empty($this->avatar)) {
            return asset('assets/characters/avatar_male_1.jpg');
        }

        $path = ltrim($this->avatar, '/');
        if (! str_starts_with($path, 'assets/')) {
            $path = 'assets/'.$path;
        }

        return asset($path);
    }

    /**
     * Cooldown records for this character.
     */
    public function cooldowns(): HasMany
    {
        return $this->hasMany(CharacterCooldown::class);
    }

    /**
     * Get remaining cooldown seconds for an action (or 0 if not on cooldown).
     */
    public function cooldownRemaining(string $action): int
    {
        $cd = $this->cooldowns()->where('action', $action)->first();
        if (! $cd || ! $cd->cooldown_until) {
            return 0;
        }

        $diff = now()->diffInSeconds($cd->cooldown_until, false);

        return max(0, (int) $diff);
    }

    /**
     * Check if character is currently on cooldown for an action.
     */
    public function isOnCooldown(string $action): bool
    {
        return $this->cooldownRemaining($action) > 0;
    }

    /**
     * Get support tickets created by character.
     */
    public function supportTickets(): HasMany
    {
        return $this->hasMany(SupportTicket::class);
    }

    /**
     * Check if this character is accepted friends with another character.
     */
    public function isFriendWith(Character|int $other): bool
    {
        $otherId = $other instanceof Character ? $other->id : $other;

        return Friend::where('character_id', $this->id)
            ->where('friend_id', $otherId)
            ->where('status', 'accepted')
            ->exists();
    }

    /**
     * Check if this character has gang officer/management permissions in their gang.
     */
    public function isGangOfficer(): bool
    {
        if (! $this->gang_id) {
            return false;
        }

        return $this->gang?->isOfficer($this) ?? false;
    }

    /**
     * Check if character user can moderate content across the platform.
     */
    public function canModerate(): bool
    {
        return $this->user?->canModerate() ?? false;
    }

    /**
     * Check if character user is owner.
     */
    public function isOwner(): bool
    {
        return $this->user?->isOwner() ?? false;
    }

    /**
     * Check if character user is admin.
     */
    public function isAdmin(): bool
    {
        return $this->user?->isAdmin() ?? false;
    }

    /**
     * Check if character user is moderator.
     */
    public function isModerator(): bool
    {
        return $this->user?->isModerator() ?? false;
    }
}
