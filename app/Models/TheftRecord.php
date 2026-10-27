<?php

namespace App\Models;

use App\Services\AuditLogService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class TheftRecord
 *
 * @property int $id
 * @property int $thief_id
 * @property int|null $victim_id
 * @property string $target_name
 * @property bool $success
 * @property int $cash_stolen
 * @property int $xp_gained
 * @property int $respect_gained
 * @property int $energy_spent
 * @property bool $jailed
 * @property int $jail_minutes
 * @property string $log_message
 */
class TheftRecord extends Model
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
        'success' => 'boolean',
        'jailed' => 'boolean',
        'cash_stolen' => 'integer',
        'xp_gained' => 'integer',
        'respect_gained' => 'integer',
        'energy_spent' => 'integer',
        'jail_minutes' => 'integer',
    ];

    public function thief(): BelongsTo
    {
        return $this->belongsTo(Character::class, 'thief_id');
    }

    public function victim(): BelongsTo
    {
        return $this->belongsTo(Character::class, 'victim_id');
    }

    protected static function booted(): void
    {
        static::created(function (TheftRecord $th): void {
            try {
                $thief = $th->thief;
                $victim = $th->victim;
                $targetName = $victim ? $victim->name : ($th->target_name ?: 'Onbekend doelwit');
                $status = $th->success ? 'Succesvol' : ($th->jailed ? "Mislukt (opgepakt voor {$th->jail_minutes}m)" : 'Mislukt');
                $stolen = (int) $th->cash_stolen;
                $desc = "Zakkenrollen: {$targetName} - {$status} (€".number_format($stolen, 0, ',', '.').')';

                AuditLogService::log(
                    action: 'theft',
                    category: 'crime',
                    description: $desc,
                    character: $thief,
                    user: $thief?->user,
                    amount: $stolen,
                    details: [
                        'source_key' => "theft_{$th->id}",
                        'success' => (bool) $th->success,
                        'cash_stolen' => $stolen,
                        'victim_id' => $th->victim_id,
                        'victim_name' => $victim?->name,
                        'jailed' => (bool) $th->jailed,
                    ]
                );
            } catch (\Throwable) {
                // Ignore to prevent breaking game logic
            }
        });
    }
}
