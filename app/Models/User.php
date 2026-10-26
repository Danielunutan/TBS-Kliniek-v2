<?php

namespace App\Models;

use App\Mail\VerifyEmailCodeMail;
use Carbon\CarbonInterface;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Mail;

#[Fillable(['name', 'email', 'password', 'google_id', 'discord_id', 'is_staff', 'role', 'two_factor_secret', 'two_factor_enabled', 'birthdate', 'is_onboarded', 'is_banned', 'banned_until', 'ban_reason', 'banned_by_id', 'banned_at', 'email_verification_code', 'email_verification_code_expires_at'])]
#[Hidden(['password', 'remember_token', 'two_factor_secret', 'email_verification_code'])]
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The model's default values for attributes.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'is_onboarded' => true,
    ];

    public ?int $openSupportTicketsCountValue = null;

    /**
     * Get the player's character.
     */
    public function character(): HasOne
    {
        return $this->hasOne(Character::class);
    }

    /**
     * Check if user is the platform owner.
     */
    public function isOwner(): bool
    {
        return $this->role === 'owner' || in_array($this->email, ['admin@tbskliniek.nl', 'danielunutan@icloud.com'], true);
    }

    /**
     * Check if user is an administrator.
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin' || $this->isOwner();
    }

    /**
     * Check if user is a moderator.
     */
    public function isModerator(): bool
    {
        return $this->role === 'moderator' || $this->isAdmin();
    }

    /**
     * Check if user has moderation privileges.
     */
    public function canModerate(): bool
    {
        return $this->isModerator() || (bool) $this->is_staff || $this->isOwner();
    }

    /**
     * Check if user is staff / admin.
     */
    public function isStaff(): bool
    {
        return $this->canModerate() || (bool) $this->is_staff || in_array($this->email, ['admin@tbskliniek.nl', 'demo@tbskliniek.nl', 'danielunutan@icloud.com'], true);
    }

    /**
     * Get styling badge attributes for user rank.
     *
     * @return array{label: string, bg: string, text: string, border: string, icon: string}
     */
    public function getRoleBadgeAttribute(): array
    {
        if ($this->isOwner()) {
            return [
                'label' => 'Eigenaar',
                'bg' => 'bg-amber-100',
                'text' => 'text-amber-800',
                'border' => 'border-amber-200',
                'icon' => 'fa-crown',
            ];
        }

        if ($this->role === 'admin') {
            return [
                'label' => 'Beheerder',
                'bg' => 'bg-purple-100',
                'text' => 'text-purple-800',
                'border' => 'border-purple-200',
                'icon' => 'fa-user-shield',
            ];
        }

        if ($this->role === 'moderator') {
            return [
                'label' => 'Moderator',
                'bg' => 'bg-blue-100',
                'text' => 'text-blue-800',
                'border' => 'border-blue-200',
                'icon' => 'fa-gavel',
            ];
        }

        return [
            'label' => 'Lid',
            'bg' => 'bg-slate-100',
            'text' => 'text-slate-600',
            'border' => 'border-slate-200',
            'icon' => 'fa-user',
        ];
    }

    public function supportTickets(): HasMany
    {
        return $this->hasMany(SupportTicket::class);
    }

    public function openSupportTicketsCount(): int
    {
        return $this->openSupportTicketsCountValue ??= $this->supportTickets()->where('status', 'beantwoord')->count();
    }

    /**
     * Warnings issued to this user.
     */
    public function warnings(): HasMany
    {
        return $this->hasMany(UserWarning::class)->latest();
    }

    /**
     * Active unacknowledged warnings.
     */
    public function unacknowledgedWarnings(): HasMany
    {
        return $this->hasMany(UserWarning::class)->whereNull('acknowledged_at')->latest();
    }

    /**
     * Ban history for this user.
     */
    public function bans(): HasMany
    {
        return $this->hasMany(UserBan::class)->latest();
    }

    /**
     * Staff member who banned this user.
     */
    public function bannedBy(): BelongsTo
    {
        return $this->belongsTo(self::class, 'banned_by_id');
    }

    /**
     * Check if user is currently banned.
     */
    public function isBanned(): bool
    {
        if (! $this->is_banned) {
            return false;
        }

        // Auto-unban if temporary ban has expired
        if ($this->banned_until !== null && now()->greaterThanOrEqualTo($this->banned_until)) {
            $this->unban();

            return false;
        }

        return true;
    }

    /**
     * Ban this user.
     */
    public function ban(string $reason, ?CarbonInterface $until = null, ?self $bannedBy = null): UserBan
    {
        $this->is_banned = true;
        $this->banned_until = $until;
        $this->ban_reason = $reason;
        $this->banned_by_id = $bannedBy?->id;
        $this->banned_at = now();
        $this->save();

        return UserBan::create([
            'user_id' => $this->id,
            'banned_by_id' => $bannedBy?->id,
            'reason' => $reason,
            'banned_until' => $until,
            'is_active' => true,
        ]);
    }

    /**
     * Unban this user.
     */
    public function unban(?self $unbannedBy = null): void
    {
        $this->is_banned = false;
        $this->banned_until = null;
        $this->ban_reason = null;
        $this->banned_by_id = null;
        $this->banned_at = null;
        $this->save();

        UserBan::where('user_id', $this->id)
            ->where('is_active', true)
            ->update([
                'is_active' => false,
                'unbanned_by_id' => $unbannedBy?->id,
                'unbanned_at' => now(),
            ]);
    }

    /**
     * Issue an official warning to this user.
     */
    public function warn(string $reason, ?self $issuer = null): UserWarning
    {
        return UserWarning::create([
            'user_id' => $this->id,
            'issuer_id' => $issuer?->id,
            'reason' => $reason,
        ]);
    }

    /**
     * Send the email verification notification using a 6-digit code expiring in 30 minutes.
     */
    public function sendEmailVerificationNotification(): void
    {
        $code = sprintf('%06d', random_int(100000, 999999));
        $expiresAt = now()->addMinutes(30);

        $this->forceFill([
            'email_verification_code' => $code,
            'email_verification_code_expires_at' => $expiresAt,
        ])->saveQuietly();

        Mail::to($this->email)->send(new VerifyEmailCodeMail($this, $code, $expiresAt));
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'email_verification_code_expires_at' => 'datetime',
            'password' => 'hashed',
            'is_staff' => 'boolean',
            'two_factor_enabled' => 'boolean',
            'is_onboarded' => 'boolean',
            'birthdate' => 'date',
            'is_banned' => 'boolean',
            'banned_until' => 'datetime',
            'banned_at' => 'datetime',
        ];
    }
}
