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
            'open' => 'bg-amber-50 text-amber-700 border-amber-200',
            'in_behandeling' => 'bg-blue-50 text-blue-700 border-blue-200',
            'beantwoord' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            'gesloten' => 'bg-slate-100 text-slate-600 border-slate-200',
            default => 'bg-slate-50 text-slate-600 border-slate-200',
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
            'laag' => 'bg-slate-50 text-slate-600 border-slate-200',
            'hoog' => 'bg-orange-50 text-orange-700 border-orange-200',
            'spoed' => 'bg-rose-50 text-rose-700 border-rose-200 font-extrabold',
            default => 'bg-blue-50 text-blue-700 border-blue-200',
        };
    }

    public function isOpen(): bool
    {
        return $this->status !== 'gesloten';
    }
}
