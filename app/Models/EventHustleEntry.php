<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventHustleEntry extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'event_hustle_entries';

    protected $fillable = [
        'character_id',
        'hustle_id',
        'created_at',
    ];

    protected $casts = [
        'hustle_id' => 'integer',
        'created_at' => 'datetime',
    ];

    public function character(): BelongsTo
    {
        return $this->belongsTo(Character::class);
    }
}
