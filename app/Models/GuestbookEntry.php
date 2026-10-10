<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class GuestbookEntry
 *
 * @property int $id
 * @property int $profile_character_id
 * @property int $author_character_id
 * @property string $message
 */
class GuestbookEntry extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $guarded = ['id'];

    public function profile(): BelongsTo
    {
        return $this->belongsTo(Character::class, 'profile_character_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(Character::class, 'author_character_id');
    }
}
