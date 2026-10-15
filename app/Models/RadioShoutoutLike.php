<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RadioShoutoutLike extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function shoutout(): BelongsTo
    {
        return $this->belongsTo(RadioShoutout::class, 'radio_shoutout_id');
    }

    public function character(): BelongsTo
    {
        return $this->belongsTo(Character::class);
    }
}
