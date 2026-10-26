<?php

namespace App\Models;

use App\Services\WebPushService;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'issuer_id', 'reason', 'acknowledged_at'])]
class UserWarning extends Model
{
    use HasFactory;

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'acknowledged_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::created(function (UserWarning $warning) {
            try {
                if ($warning->user) {
                    app(WebPushService::class)->sendToUser(
                        $warning->user,
                        '⚠️ Officiële Waarschuwing Ontvangen',
                        $warning->reason ?? 'Je hebt een officiële waarschuwing ontvangen van de leiding.',
                        '/dashboard',
                        null,
                        ['type' => 'user_warning', 'warning_id' => $warning->id]
                    );
                }
            } catch (\Throwable) {
                // Silently ignore push failure
            }
        });
    }

    /**
     * User who received the warning.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Staff member who issued the warning.
     */
    public function issuer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issuer_id');
    }

    /**
     * Check if the warning has been acknowledged.
     */
    public function isAcknowledged(): bool
    {
        return $this->acknowledged_at !== null;
    }

    /**
     * Mark the warning as acknowledged by the player.
     */
    public function acknowledge(): void
    {
        $this->acknowledged_at = now();
        $this->save();
    }
}
