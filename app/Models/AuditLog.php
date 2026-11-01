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

    public function scopePlayer(Builder $query, int|string|Character|User|null $player): Builder
    {
        if (! $player) {
            return $query;
        }

        if ($player instanceof Character) {
            return $query->where('character_id', $player->id);
        }

        if ($player instanceof User) {
            return $query->where('user_id', $player->id);
        }

        $id = (int) $player;

        return $query->where(function (Builder $q) use ($id) {
            $q->where('character_id', $id)
                ->orWhere('user_id', $id);
        });
    }

    public function scopeIp(Builder $query, ?string $ip): Builder
    {
        if (! $ip) {
            return $query;
        }

        return $query->where('ip_address', trim($ip));
    }

    public function scopeMinScore(Builder $query, ?int $minScore): Builder
    {
        if ($minScore === null || $minScore <= 0) {
            return $query;
        }

        return $query->where('risk_score', '>=', $minScore);
    }

    public function scopeAction(Builder $query, ?string $action): Builder
    {
        if (! $action || $action === 'all') {
            return $query;
        }

        return $query->where('action', $action);
    }

    public function scopeHighestRisk(Builder $query): Builder
    {
        return $query->orderByDesc('risk_score')->orderByDesc('id');
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
            'vip' => 'fa-solid fa-gem text-amber-400',
            'system' => 'fa-solid fa-server text-blue-500',
            'security' => 'fa-solid fa-shield-halved text-rose-500',
            'casino' => 'fa-solid fa-dice text-amber-500',
            'marketplace' => 'fa-solid fa-scale-balanced text-teal-500',
            'heist' => 'fa-solid fa-vault text-orange-500',
            'hacking' => 'fa-solid fa-terminal text-emerald-400',
            'bounty' => 'fa-solid fa-crosshairs text-rose-400',
            default => 'fa-solid fa-list-check text-slate-500',
        };
    }

    public function getCategoryLabel(): string
    {
        return match ($this->category) {
            'economy' => 'Economie & Bank',
            'shop' => 'Winkel & Kantine',
            'crime' => 'Misdaad & Diefstal',
            'gang' => 'Bendes & Allianties',
            'combat' => 'Gevechten & Kracht',
            'training' => 'Training & Gym',
            'moderation' => 'Moderatie & Sancties',
            'admin' => 'Beheer & Admin',
            'vip' => 'VIP & Donaties',
            'system' => 'Systeem & Onderhoud',
            'security' => 'Beveiliging & Fraude',
            'casino' => 'Casino & Kansspel',
            'marketplace' => 'Marktplaats & Handel',
            'heist' => 'Overvallen & Heists',
            'hacking' => 'Hacking & Data',
            'bounty' => 'Bounty & Premies',
            default => ucfirst($this->category ?: 'Overig'),
        };
    }
}
