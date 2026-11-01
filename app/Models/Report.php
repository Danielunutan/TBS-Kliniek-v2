<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Report extends Model
{
    use HasFactory;

    protected $fillable = [
        'reporter_id',
        'reported_type',
        'reported_id',
        'reported_name',
        'reason',
        'details',
        'status',
        'handled_by',
        'handled_at',
        'mod_notes',
    ];

    protected $casts = [
        'handled_at' => 'datetime',
    ];

    /**
     * The character who submitted the report.
     */
    public function reporter(): BelongsTo
    {
        return $this->belongsTo(Character::class, 'reporter_id');
    }

    /**
     * The user (staff member) who handled the report.
     */
    public function handler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handled_by');
    }

    /**
     * Resolve the target model being reported.
     */
    public function getTargetEntity(): mixed
    {
        return match ($this->reported_type) {
            'character' => Character::find($this->reported_id),
            'gang' => Gang::find($this->reported_id),
            'chat_message' => ChatMessage::with('character')->find($this->reported_id),
            'direct_message' => DirectMessage::with(['sender', 'recipient'])->find($this->reported_id),
            default => null,
        };
    }

    /**
     * Get the reported character, whether reported directly or via a chat message / direct message.
     */
    public function getReportedCharacter(): ?Character
    {
        if ($this->reported_type === 'character') {
            return Character::with('user')->find($this->reported_id)
                ?: Character::with('user')->where('name', $this->reported_name)->first();
        }

        if ($this->reported_type === 'chat_message') {
            $msg = ChatMessage::with('character.user')->find($this->reported_id);
            if ($msg && $msg->character) {
                return $msg->character;
            }
            if ($this->reported_name) {
                return Character::with('user')->where('name', $this->reported_name)->first();
            }
        }

        if ($this->reported_type === 'direct_message') {
            $msg = DirectMessage::with('sender.user')->find($this->reported_id);
            if ($msg && $msg->sender) {
                return $msg->sender;
            }
            if ($this->reported_name) {
                return Character::with('user')->where('name', $this->reported_name)->first();
            }
        }

        return null;
    }

    /**
     * Get the User account of the reported player (for banning or warning).
     */
    public function getReportedUser(): ?User
    {
        return $this->getReportedCharacter()?->user;
    }

    /**
     * Get the direct public URL for the reported entity.
     */
    public function getTargetUrl(): string
    {
        if ($this->reported_type === 'gang') {
            return route('gangs.show', $this->reported_id);
        }

        if ($this->reported_type === 'direct_message') {
            $msg = DirectMessage::find($this->reported_id);
            if ($msg) {
                $partnerId = ($msg->sender_id === $this->reporter_id) ? $msg->recipient_id : $msg->sender_id;
                if ($partnerId) {
                    return route('chat.index', ['tab' => 'direct', 'recipient' => $partnerId]);
                }
            }
        }

        $character = $this->getReportedCharacter();
        $characterName = $character?->name ?? $this->reported_name;

        if ($characterName) {
            return route('profile.show', $characterName);
        }

        return route('admin.moderation.index');
    }
}
