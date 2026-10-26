<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdminMailLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'sender_email',
        'sender_name',
        'recipient_email',
        'recipient_name',
        'subject',
        'body',
        'status',
        'error_message',
        'ip_address',
    ];

    /**
     * Get the staff user who sent the email.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
