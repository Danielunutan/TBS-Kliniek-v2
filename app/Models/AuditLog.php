<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    protected $fillable = [
        'user_id',
        'character_id',
        'action',
        'category',
        'description',
        'amount',
        'risk_level',
        'risk_score',
        'risk_reason',
        'risk_flags',
        'ip_address',
        'details',
        'created_at',
        'updated_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'risk_score' => 'integer',
            'risk_flags' => 'array',
            'details' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function character(): BelongsTo
    {
        return $this->belongsTo(Character::class);
    }

    public function scopeRisk(Builder $query, ?string $risk): Builder
    {
        if (! $risk || $risk === 'all') {
            return $query;
        }

        return $query->where('risk_level', $risk);
    }

    public function scopeCategory(Builder $query, ?string $category): Builder
    {
        if (! $category || $category === 'all') {
            return $query;
        }

        return $query->where('category', $category);
    }

    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        if (! $search) {
            return $query;
        }

        $term = '%'.trim($search).'%';

        return $query->where(function ($q) use ($term) {
            $q->where('description', 'like', $term)
                ->orWhere('action', 'like', $term)
                ->orWhere('ip_address', 'like', $term)
                ->orWhere('risk_reason', 'like', $term)
                ->orWhereHas('character', function ($cq) use ($term) {
                    $cq->where('name', 'like', $term);
                })
                ->orWhereHas('user', function ($uq) use ($term) {
                    $uq->where('name', 'like', $term)
                        ->orWhere('email', 'like', $term);
                });
        });
    }

    public function getRiskBadgeClass(): string
    {
        return match ($this->risk_level) {
            'hoog' => 'bg-rose-100 dark:bg-rose-950/70 text-rose-700 dark:text-rose-300 border border-rose-300 dark:border-rose-800',
            'verdacht' => 'bg-amber-100 dark:bg-amber-950/70 text-amber-800 dark:text-amber-300 border border-amber-300 dark:border-amber-800',
            default => 'bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/60',
        };
    }

    public function getRiskIcon(): string
    {
        return match ($this->risk_level) {
            'hoog' => 'fa-solid fa-triangle-exclamation text-rose-600',
            'verdacht' => 'fa-solid fa-radiation text-amber-500',
            default => 'fa-solid fa-shield-check text-emerald-500',
        };
    }

    public function getCategoryIcon(): string
    {
        return match ($this->category) {
            'economy' => 'fa-solid fa-building-columns text-emerald-500',
            'shop' => 'fa-solid fa-cart-shopping text-purple-500',
            'crime' => 'fa-solid fa-user-ninja text-rose-500',
            'gang' => 'fa-solid fa-users-viewfinder text-indigo-500',
            'training' => 'fa-solid fa-dumbbell text-amber-500',
            'combat' => 'fa-solid fa-hand-fist text-red-500',
            'moderation' => 'fa-solid fa-gavel text-rose-600',
            'admin' => 'fa-solid fa-crown text-amber-500',
            default => 'fa-solid fa-list-check text-slate-500',
        };
    }
}
