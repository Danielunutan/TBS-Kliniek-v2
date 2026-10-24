<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrueCrimeCase extends Model
{
    use HasFactory;

    protected $fillable = [
        'case_number',
        'title',
        'category',
        'difficulty',
        'incident_report',
        'clues',
        'suspects',
        'culprit_index',
        'solution_explanation',
        'reward_cash',
        'reward_xp',
        'reward_respect',
    ];

    protected function casts(): array
    {
        return [
            'case_number' => 'integer',
            'clues' => 'array',
            'suspects' => 'array',
            'culprit_index' => 'integer',
            'reward_cash' => 'integer',
            'reward_xp' => 'integer',
            'reward_respect' => 'integer',
        ];
    }
}
