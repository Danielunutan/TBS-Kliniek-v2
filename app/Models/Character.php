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
        'respect' => 'integer',
        'strength' => 'integer',
        'defense' => 'integer',
        'stamina' => 'integer',
        'intelligence' => 'integer',
        'charisma' => 'integer',
        'discipline' => 'integer',
    ];

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
     * Check if currently serving prison time.
     */
    public function isImprisoned(): bool
    {
        if (! $this->is_in_prison) {
            return false;
        }

        if ($this->prison_until && now()->gte($this->prison_until)) {
            $this->update([
                'is_in_prison' => false,
                'prison_until' => null,
                'prison_reason' => null,
            ]);

            return false;
        }

        return true;
    }

    /**
     * Get remaining prison time in minutes.
     */
    public function remainingPrisonMinutes(): int
    {
        if (! $this->is_in_prison || ! $this->prison_until) {
            return 0;
        }

        $diff = now()->diffInMinutes($this->prison_until, false);

        return max(0, (int) ceil($diff));
    }
}
