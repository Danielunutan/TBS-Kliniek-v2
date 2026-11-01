<?php

namespace App\Models;

use App\Services\WebPushService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupportTicketMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'support_ticket_id',
        'user_id',
        'character_id',
        'is_staff',
        'message',
    ];

    protected $casts = [
        'is_staff' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::created(function (SupportTicketMessage $msg) {
            if ($msg->is_staff && $msg->ticket && $msg->ticket->user_id) {
                try {
                    $user = $msg->ticket->user;
                    if ($user) {
                        $ticketSubject = $msg->ticket->subject ?? "#{$msg->ticket->id}";
                        app(WebPushService::class)->sendToUser(
                            $user,
                            "Support Reactie: {$ticketSubject}",
                            $msg->message,
                            "/help/tickets/{$msg->ticket->id}",
                            null,
                            ['type' => 'support_reply', 'ticket_id' => $msg->ticket->id]
                        );
                    }
                } catch (\Throwable) {
                    // Silently ignore push failure
                }
            }
        });
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(SupportTicket::class, 'support_ticket_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function character(): BelongsTo
    {
        return $this->belongsTo(Character::class);
    }

    /**
     * Format message with styled markdown and lists.
     */
    public function getFormattedMessageAttribute(): string
    {
        $text = e((string) ($this->message ?? ''));

        // Disarm dangerous schemes
        $text = preg_replace('/(javascript|vbscript|data):/i', '$1&#58;', $text);

        // Bold **text**
        $text = preg_replace('/\*\*(.*?)\*\*/s', '<strong class="font-extrabold text-slate-900 dark:text-white">$1</strong>', $text);

        // Italic *text*
        $text = preg_replace('/(?<!\*)\*(?!\*)(.*?)(?<!\*)\*(?!\*)/s', '<em class="italic text-slate-600 dark:text-slate-400">$1</em>', $text);

        // Numbered step headers: e.g. "1. Title"
        $text = preg_replace_callback('/^(\d+)\.\s+(.*?)$/m', function ($m) {
            return '<div class="flex items-start gap-2.5 my-2.5 font-medium"><span class="w-5 h-5 rounded-md bg-purple-100 dark:bg-purple-950/80 text-purple-700 dark:text-purple-300 font-mono font-black text-xs flex items-center justify-center shrink-0 border border-purple-200 dark:border-purple-800/80 mt-0.5">'.$m[1].'</span><div class="grow leading-relaxed">'.$m[2].'</div></div>';
        }, $text);

        // Sub-bullets: lines starting with "   - " or "- "
        $text = preg_replace_callback('/^\s*[-•]\s+(.*?)$/m', function ($m) {
            return '<div class="flex items-start gap-2 my-1 pl-4 text-slate-700 dark:text-slate-300"><span class="text-purple-500 font-bold shrink-0 mt-0.5">•</span><span class="leading-relaxed">'.$m[1].'</span></div>';
        }, $text);

        // Paragraph breaks
        $text = preg_replace("/\n\n+/", '<div class="my-2.5"></div>', $text);

        // Remaining line breaks
        $text = nl2br($text);

        // Clean up redundant breaks after containers
        $text = str_replace(['</div><br />', '</div><br/>', '</div><br>'], '</div>', $text);

        return $text;
    }
}
