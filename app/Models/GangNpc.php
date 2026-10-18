<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GangNpc extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'bonus_value' => 'integer',
        'hired_at' => 'datetime',
    ];

    public function gang(): BelongsTo
    {
        return $this->belongsTo(Gang::class);
    }

    public function assignedProperty(): BelongsTo
    {
        return $this->belongsTo(GangProperty::class, 'assigned_property_id');
    }
}
