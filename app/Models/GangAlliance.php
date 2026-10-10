<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GangAlliance extends Model
{
    use HasFactory;

    protected $fillable = [
        'gang_id',
        'ally_gang_id',
        'status',
    ];

    public function gang(): BelongsTo
    {
        return $this->belongsTo(Gang::class, 'gang_id');
    }

    public function allyGang(): BelongsTo
    {
        return $this->belongsTo(Gang::class, 'ally_gang_id');
    }
}
