<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NewsArticle extends Model
{
    use HasFactory;

    protected $fillable = [
        'character_id',
        'title',
        'slug',
        'emoji',
        'image_url',
        'content',
        'is_pinned',
        'comments_enabled',
        'is_published',
        'views_count',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'is_pinned' => 'boolean',
            'comments_enabled' => 'boolean',
            'is_published' => 'boolean',
            'views_count' => 'integer',
            'published_at' => 'datetime',
        ];
    }

    /**
     * Get author of the article.
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(Character::class, 'character_id');
    }

    /**
     * Get comments on the article.
     */
    public function comments(): HasMany
    {
        return $this->hasMany(NewsComment::class)->latest();
    }

    /**
     * Convert markdown and custom BBCode tags to safe, styled HTML.
     */
    public function getFormattedContentAttribute(): string
    {
        $text = e($this->content);

        // [img]url[/img] and ![alt](url) - support both https?:// and root-relative /assets/...
        $text = preg_replace(
            '/\[img\]((?:https?:\/\/|\/)[^\s<"\']+)\[\/img\]/i',
            '<div class="my-4"><img src="$1" alt="Nieuws Afbeelding" class="rounded-2xl max-w-full max-h-96 object-contain border border-slate-200/80 shadow-xs" loading="lazy" onerror="this.style.display=\'none\'" /></div>',
            $text
        );
        $text = preg_replace(
            '/!\[([^\]]*)\]\(((?:https?:\/\/|\/)[^\s<"\']+)\)/i',
            '<div class="my-4"><img src="$2" alt="$1" class="rounded-2xl max-w-full max-h-96 object-contain border border-slate-200/80 shadow-xs" loading="lazy" onerror="this.style.display=\'none\'" /></div>',
            $text
        );

        // [link=url]label[/link] or [url=url]label[/url] - support both https?:// and /relative
        $text = preg_replace(
            '/\[(?:link|url)=((?:https?:\/\/|\/)[^\s<"\']+)\](.*?)\[\/(?:link|url)\]/i',
            '<a href="$1" target="_blank" rel="noopener noreferrer" class="text-purple-600 hover:text-purple-800 font-extrabold underline decoration-purple-300 hover:decoration-purple-600 transition inline-flex items-center gap-1">$2 <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i></a>',
            $text
        );
        $text = preg_replace(
            '/\[([^\]]+)\]\(((?:https?:\/\/|\/)[^\s<"\']+)\)/i',
            '<a href="$2" target="_blank" rel="noopener noreferrer" class="text-purple-600 hover:text-purple-800 font-extrabold underline decoration-purple-300 hover:decoration-purple-600 transition inline-flex items-center gap-1">$1 <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i></a>',
            $text
        );

        // Colors: [color=red|purple|amber|emerald|blue|#hex]text[/color]
        $text = preg_replace_callback('/\[color=([a-z0-9#]+)\](.*?)\[\/color\]/is', function ($m) {
            $col = $m[1];
            $style = str_starts_with($col, '#') ? "color: {$col};" : match (strtolower($col)) {
                'purple' => 'color: #7e22ce;',
                'red' => 'color: #dc2626;',
                'emerald', 'green' => 'color: #059669;',
                'amber', 'gold', 'yellow' => 'color: #d97706;',
                'blue' => 'color: #2563eb;',
                default => "color: {$col};",
            };

            return "<span style=\"{$style}\" class=\"font-bold\">{$m[2]}</span>";
        }, $text);

        // Center alignment [center]...[/center]
        $text = preg_replace('/\[center\](.*?)\[\/center\]/is', '<div class="text-center my-2">$1</div>', $text);

        // Headers
        $text = preg_replace('/^###\s+(.*?)$/m', '<h3 class="text-base font-black text-slate-900 mt-4 mb-2 tracking-tight">$1</h3>', $text);
        $text = preg_replace('/^##\s+(.*?)$/m', '<h2 class="text-lg font-black text-slate-900 mt-5 mb-2.5 tracking-tight">$1</h2>', $text);
        $text = preg_replace('/\[h2\](.*?)\[\/h2\]/i', '<h2 class="text-lg font-black text-slate-900 mt-5 mb-2.5 tracking-tight">$1</h2>', $text);
        $text = preg_replace('/\[h3\](.*?)\[\/h3\]/i', '<h3 class="text-base font-black text-slate-900 mt-4 mb-2 tracking-tight">$1</h3>', $text);

        // Bold, italic, underline, strikethrough
        $text = preg_replace('/\[b\](.*?)\[\/b\]/i', '<strong class="font-extrabold text-slate-900">$1</strong>', $text);
        $text = preg_replace('/\*\*(.*?)\*\*/s', '<strong class="font-extrabold text-slate-900">$1</strong>', $text);
        $text = preg_replace('/\[i\](.*?)\[\/i\]/i', '<em class="italic text-slate-700">$1</em>', $text);
        $text = preg_replace('/(?<!\*)\*(?!\*)(.*?)(?<!\*)\*(?!\*)/s', '<em class="italic text-slate-700">$1</em>', $text);
        $text = preg_replace('/\[u\](.*?)\[\/u\]/i', '<span class="underline decoration-slate-400">$1</span>', $text);
        $text = preg_replace('/\[s\](.*?)\[\/s\]/i', '<del class="line-through text-slate-400">$1</del>', $text);
        $text = preg_replace('/~~(.*?)~~/s', '<del class="line-through text-slate-400">$1</del>', $text);

        // Quote
        $text = preg_replace(
            '/\[quote\](.*?)\[\/quote\]/is',
            '<blockquote class="border-l-4 border-purple-500 bg-purple-50/60 rounded-r-xl p-3.5 my-3 text-xs sm:text-sm font-semibold text-purple-900 italic">$1</blockquote>',
            $text
        );
        $text = preg_replace(
            '/^>\s+(.*?)$/m',
            '<blockquote class="border-l-4 border-purple-500 bg-purple-50/60 rounded-r-xl p-3.5 my-3 text-xs sm:text-sm font-semibold text-purple-900 italic">$1</blockquote>',
            $text
        );

        // Horizontal Rule
        $text = preg_replace('/^---$/m', '<hr class="my-4 border-slate-200" />', $text);

        // Unordered list items
        $text = preg_replace('/^-\s+(.*?)$/m', '<li class="ml-4 list-disc text-slate-700 font-medium">$1</li>', $text);

        return nl2br($text);
    }
}
