<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class CharacterMission
 *
 * @property int $id
 * @property int $character_id
 * @property int $mission_id
 * @property int $completed_count
 * @property Carbon|null $last_attempted_at
 */
class CharacterMission extends Model
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
        'completed_count' => 'integer',
        'last_attempted_at' => 'datetime',
    ];

    public function character(): BelongsTo
    {
        return $this->belongsTo(Character::class);
    }

    public function mission(): BelongsTo
    {
        return $this->belongsTo(Mission::class);
    }
}
