<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class ProfileVisit
 *
 * @property int $id
 * @property int $visitor_id
 * @property int $visited_id
 * @property Carbon $visited_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read Character $visitor
 * @property-read Character $visited
 */
class ProfileVisit extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'visitor_id',
        'visited_id',
        'visited_at',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'visited_at' => 'datetime',
    ];

    /**
     * The character who visited the profile.
     */
    public function visitor(): BelongsTo
    {
        return $this->belongsTo(Character::class, 'visitor_id');
    }

    /**
     * The character whose profile was visited.
     */
    public function visited(): BelongsTo
    {
        return $this->belongsTo(Character::class, 'visited_id');
    }
}
