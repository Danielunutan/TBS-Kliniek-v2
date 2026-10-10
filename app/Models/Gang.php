<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class Gang
 *
 * @property int $id
 * @property string $name
 * @property string $tag
 * @property int|null $leader_character_id
 * @property string|null $description
 * @property string $specialty
 * @property string $emblem
 * @property int $treasury
 */
class Gang extends Model
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
        'treasury' => 'integer',
    ];

    /**
     * Get the leader of the gang.
     */
    public function leader(): BelongsTo
    {
        return $this->belongsTo(Character::class, 'leader_character_id');
    }

    /**
     * Get the members of the gang.
     */
    public function members(): HasMany
    {
        return $this->hasMany(Character::class);
    }
}
