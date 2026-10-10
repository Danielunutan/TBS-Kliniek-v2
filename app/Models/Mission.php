<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class Mission
 *
 * @property int $id
 * @property string $category
 * @property string|null $storyline
 * @property int|null $story_step
 * @property string $title
 * @property string $slug
 * @property string $description
 * @property string $giver_name
 * @property string $location_name
 * @property int $required_level
 * @property int $energy_cost
 * @property int $success_rate
 * @property int $min_cash_reward
 * @property int $max_cash_reward
 * @property int $xp_reward
 * @property int $respect_reward
 * @property string|null $fail_message
 * @property string|null $success_message
 * @property int $sort_order
 */
class Mission extends Model
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
        'required_level' => 'integer',
        'energy_cost' => 'integer',
        'success_rate' => 'integer',
        'min_cash_reward' => 'integer',
        'max_cash_reward' => 'integer',
        'xp_reward' => 'integer',
        'respect_reward' => 'integer',
        'sort_order' => 'integer',
    ];

    /**
     * Get player progression on this mission.
     */
    public function progress(): HasMany
    {
        return $this->hasMany(CharacterMission::class);
    }
}
