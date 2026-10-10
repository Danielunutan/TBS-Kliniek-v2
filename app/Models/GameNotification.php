<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class GameNotification
 *
 * @property int $id
 * @property int $character_id
 * @property string $type
 * @property string $icon
 * @property string $title
 * @property string $message
 * @property bool $is_read
 */
class GameNotification extends Model
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
        'is_read' => 'boolean',
    ];

    public function character(): BelongsTo
    {
        return $this->belongsTo(Character::class);
    }
}
