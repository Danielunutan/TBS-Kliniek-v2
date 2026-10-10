<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'google_id', 'discord_id', 'is_staff', 'two_factor_secret', 'two_factor_enabled'])]
#[Hidden(['password', 'remember_token', 'two_factor_secret'])]
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the player's character.
     */
    public function character(): HasOne
    {
        return $this->hasOne(Character::class);
    }

    /**
     * Check if user is staff / admin.
     */
    public function isStaff(): bool
    {
        return (bool) $this->is_staff || in_array($this->email, ['admin@tbskliniek.nl', 'demo@tbskliniek.nl'], true);
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
        ];
    }
}
