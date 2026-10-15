<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'google_id', 'discord_id', 'is_staff', 'role', 'two_factor_secret', 'two_factor_enabled', 'birthdate', 'is_onboarded'])]
#[Hidden(['password', 'remember_token', 'two_factor_secret'])]
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

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_staff' => 'boolean',
            'two_factor_enabled' => 'boolean',
            'is_onboarded' => 'boolean',
            'birthdate' => 'date',
        ];
    }
}
