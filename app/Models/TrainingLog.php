<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class TrainingLog
 *
 * @property int $id
 * @property int $character_id
 * @property string $stat
 * @property int $stat_gain
 * @property int $energy_cost
 * @property int $cash_cost
 * @property string $trainer
 */
class TrainingLog extends Model
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
        'stat_gain' => 'integer',
        'energy_cost' => 'integer',
        'cash_cost' => 'integer',
    ];

    public function character(): BelongsTo
    {
        return $this->belongsTo(Character::class);
    }
}
