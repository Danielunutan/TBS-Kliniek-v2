<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'banned_by_id', 'reason', 'banned_until', 'is_active', 'unbanned_by_id', 'unbanned_at'])]
class UserBan extends Model
{
    use HasFactory;

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'banned_until' => 'datetime',
        'unbanned_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * User who was banned.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Staff member who issued the ban.
     */
    public function bannedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'banned_by_id');
    }

    /**
     * Staff member who revoked the ban.
     */
    public function unbannedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'unbanned_by_id');
    }

    /**
     * Check if the ban was permanent.
     */
    public function isPermanent(): bool
    {
        return $this->banned_until === null;
    }

    /**
     * Check if a temporary ban has expired.
     */
    public function isExpired(): bool
    {
        return $this->banned_until !== null && now()->greaterThan($this->banned_until);
    }
}
