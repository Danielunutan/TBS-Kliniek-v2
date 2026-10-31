<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class SupportTicket extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'character_id',
        'ticket_number',
        'subject',
        'category',
        'priority',
        'status',
        'last_reply_at',
        'last_reply_by_staff',
        'closed_at',
        'metadata',
    ];

    protected $casts = [
        'last_reply_at' => 'datetime',
        'closed_at' => 'datetime',
        'last_reply_by_staff' => 'boolean',
        'metadata' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function character(): BelongsTo
    {
        return $this->belongsTo(Character::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(SupportTicketMessage::class)->oldest();
    }

    public function lastMessage(): HasOne
    {
        return $this->hasOne(SupportTicketMessage::class)->latestOfMany();
    }

    public function isGangTransfer(): bool
    {
        return $this->category === 'gang_overstap';
    }

    public function isGangTransferApproved(): bool
    {
        return ! empty($this->metadata['bonus_approved_at']);
    }

    public function getCategoryLabelAttribute(): string
    {
        return match ($this->category) {
            'bug' => 'Bug / Foutmelding',
            'vraag' => 'Vraag over de Game',
            'feedback' => 'Feedback & Suggestie',
            'vip' => 'VIP & Donaties',
            'gang_overstap' => 'Gang Overstap Bonus',
            default => 'Overige Vraag',
        };
    }

    public function getCategoryIconAttribute(): string
    {
        return match ($this->category) {
            'bug' => 'fa-bug',
            'vraag' => 'fa-circle-question',
            'feedback' => 'fa-lightbulb',
            'vip' => 'fa-crown',
            'gang_overstap' => 'fa-users-between-lines',
            default => 'fa-message',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'open' => 'Open',
            'in_behandeling' => 'In Behandeling',
            'beantwoord' => 'Beantwoord',
            'gesloten' => 'Gesloten',
            default => ucfirst($this->status),
        };
    }

    public function getStatusBadgeClassesAttribute(): string
    {
        return match ($this->status) {
            'open' => 'bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border-amber-200 dark:border-amber-800/60',
            'in_behandeling' => 'bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 border-blue-200 dark:border-blue-800/60',
            'beantwoord' => 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800/60',
            'gesloten' => 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 border-slate-200 dark:border-slate-700',
            default => 'bg-slate-50 dark:bg-slate-800 text-slate-600 dark:text-slate-300 border-slate-200 dark:border-slate-700',
        };
    }

    public function getPriorityLabelAttribute(): string
    {
        return match ($this->priority) {
            'laag' => 'Laag',
            'hoog' => 'Hoog',
            'spoed' => 'Spoed / Urgent',
            default => 'Normaal',
        };
    }

    public function getPriorityBadgeClassesAttribute(): string
    {
        return match ($this->priority) {
            'laag' => 'bg-slate-50 dark:bg-slate-800 text-slate-600 dark:text-slate-300 border-slate-200 dark:border-slate-700',
            'hoog' => 'bg-orange-50 dark:bg-orange-950/60 text-orange-700 dark:text-orange-300 border-orange-200 dark:border-orange-800/60',
            'spoed' => 'bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border-rose-200 dark:border-rose-800/60 font-extrabold',
            default => 'bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 border-blue-200 dark:border-blue-800/60',
        };
    }

    public function isOpen(): bool
    {
        return $this->status !== 'gesloten';
    }
}
