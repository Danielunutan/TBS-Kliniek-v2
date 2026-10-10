<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class CharacterItem
 *
 * @property int $id
 * @property int $character_id
 * @property int $item_id
 * @property int $quantity
 * @property bool $is_equipped
 */
class CharacterItem extends Model
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
        'quantity' => 'integer',
        'is_equipped' => 'boolean',
    ];

    public function character(): BelongsTo
    {
        return $this->belongsTo(Character::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}
