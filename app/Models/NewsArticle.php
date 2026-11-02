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
        'created_at',
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
     * Scope a query to only include published articles whose publish time has arrived.
     */
    public function scopePublished($query)
    {
        return $query->where('is_published', true)
            ->where(function ($q) {
                $q->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            });
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
     * Tags that survive sanitization. Every other tag loses its markup but keeps its text.
     */
    private const SANITIZE_ALLOWED_TAGS = '<p><br><h2><h3><h4><h5><h6><strong><b><em><i><u><s><del><ul><ol><li><blockquote><code><pre><hr><a><img><div><span><table><thead><tbody><tr><th><td>';

    /**
     * Attributes that survive sanitization. Every other attribute, including all on* event handlers, is removed.
     */
    private const SANITIZE_ALLOWED_ATTRIBUTES = ['href', 'src', 'alt', 'title', 'colspan', 'rowspan', 'class', 'target', 'rel'];

    /**
     * Sanitize HTML with an allow-list of tags, attributes, and URL schemes.
     *
     * A blocklist fails on encoded input, such as "&#106;avascript:", because browsers decode entities after
     * the server checks the text. The DOM parser decodes entities first, so the checks see the same value as the browser.
     */
    public static function sanitizeHtml(?string $html): string
    {
        if (! $html) {
            return '';
        }

        $withoutExecutableBlocks = (string) preg_replace('~<(script|style)\b[^>]*>.*?</\1>~is', '', $html);
        $stripped = strip_tags($withoutExecutableBlocks, self::SANITIZE_ALLOWED_TAGS);

        $document = new \DOMDocument('1.0', 'UTF-8');
        $previousErrorSetting = libxml_use_internal_errors(true);
        // The wrapper div gives one known root. Parser warnings are ignored because the output is re-serialized.
        $document->loadHTML('<meta charset="UTF-8"><div>'.$stripped.'</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        libxml_use_internal_errors($previousErrorSetting);

        $container = $document->getElementsByTagName('div')->item(0);
        if (! $container) {
            return '';
        }

        foreach ($container->getElementsByTagName('*') as $element) {
            self::sanitizeElementAttributes($element);
        }

        $sanitized = '';
        foreach ($container->childNodes as $child) {
            $sanitized .= $document->saveHTML($child);
        }

        return $sanitized;
    }

    /**
     * Remove attributes that are not allowed and neutralize unsafe URLs on one element.
     */
    private static function sanitizeElementAttributes(\DOMElement $element): void
    {
        $attributeNames = [];
        foreach ($element->attributes as $attribute) {
            $attributeNames[] = $attribute->name;
        }

        foreach ($attributeNames as $attributeName) {
            $normalizedName = strtolower($attributeName);

            if (! in_array($normalizedName, self::SANITIZE_ALLOWED_ATTRIBUTES, true)) {
                $element->removeAttribute($attributeName);

                continue;
            }

            if (in_array($normalizedName, ['href', 'src'], true) && ! self::isSafeUrl($element->getAttribute($attributeName))) {
                $element->setAttribute($attributeName, '#');
            }
        }
    }

    /**
     * Check whether a URL uses an allowed scheme. Browsers ignore control characters and spaces inside a scheme,
     * so those are removed before the check.
     */
    private static function isSafeUrl(string $url): bool
    {
        $normalizedUrl = strtolower((string) preg_replace('/[\x00-\x20]+/', '', $url));

        return (bool) preg_match('~^(https?://|mailto:|/(?!/)|#)~', $normalizedUrl);
    }

    /**
     * Convert markdown and custom BBCode tags to safe, styled HTML.
     */
    public function getFormattedContentAttribute(): string
    {
        // If content is already formatted HTML, allow safe formatting tags without double-escaping
        if (preg_match('/<(?:p|div|h[1-6]|strong|em|ul|ol|li|br|blockquote|code|table|span|hr)\b/i', $this->content)) {
            return self::sanitizeHtml($this->content);
        }

        $text = str_replace(["\r\n", "\r"], "\n", e($this->content));

        // [img]url[/img] and ![alt](url) - support both https?:// and root-relative /assets/...
        $text = preg_replace(
            '/\[img\]((?:https?:\/\/|\/(?!\/))[^\s<"\']+)\[\/img\]/i',
            '<div class="my-4"><img src="$1" alt="Nieuws Afbeelding" class="rounded-2xl max-w-full max-h-96 object-contain border border-slate-200/80 dark:border-slate-800 shadow-xs" loading="lazy" onerror="this.style.display=\'none\'" /></div>',
            $text
        );
        $text = preg_replace(
            '/!\[([^\]]*)\]\(((?:https?:\/\/|\/(?!\/))[^\s<"\']+)\)/i',
            '<div class="my-4"><img src="$2" alt="$1" class="rounded-2xl max-w-full max-h-96 object-contain border border-slate-200/80 dark:border-slate-800 shadow-xs" loading="lazy" onerror="this.style.display=\'none\'" /></div>',
            $text
        );

        // [link=url]label[/link] or [url=url]label[/url] - support both https?:// and /relative
        $renderLink = function (string $rawUrl, string $label): string {
            $url = match ($rawUrl) {
                '/kluis' => '/kluis-kraak',
                '/koffers' => '/pakketjes',
                default => $rawUrl,
            };
            $isExternal = str_starts_with(strtolower($url), 'http');
            $targetAttr = $isExternal ? ' target="_blank" rel="noopener noreferrer"' : '';
            $iconClass = $isExternal ? 'fa-arrow-up-right-from-square' : 'fa-arrow-right';

            return '<a href="'.$url.'"'.$targetAttr.' class="text-purple-600 dark:text-purple-400 hover:text-purple-800 dark:hover:text-purple-300 font-extrabold underline decoration-purple-300 dark:decoration-purple-700 hover:decoration-purple-600 transition inline-flex items-center gap-1">'.$label.' <i class="fa-solid '.$iconClass.' text-[10px]"></i></a>';
        };

        $text = preg_replace_callback(
            '/\[(?:link|url)=((?:https?:\/\/|\/(?!\/))[^\s<"\']+)\](.*?)\[\/(?:link|url)\]/i',
            fn ($m) => $renderLink($m[1], $m[2]),
            $text
        );
        $text = preg_replace_callback(
            '/\[([^\]]+)\]\(((?:https?:\/\/|\/(?!\/))[^\s<"\']+)\)/i',
            fn ($m) => $renderLink($m[2], $m[1]),
            $text
        );

        // Event Winner Card: [winner=Name|Score|Prize1|Prize2]
        $text = preg_replace_callback('/\[winner=([^\]|]+)\|([^\]|]+)\|([^\]|]+)\|([^\]]+)\]/isu', function ($m) {
            $name = trim($m[1]);
            $score = trim($m[2]);
            $prize1 = trim($m[3]);
            $prize2 = trim($m[4]);
            $rawName = html_entity_decode($name, ENT_QUOTES, 'UTF-8');
            $profileUrl = '/speler/'.rawurlencode($rawName);

            return "\n\n".'<div class="my-4 p-5 rounded-2xl bg-amber-500/10 dark:bg-amber-950/40 border border-amber-300 dark:border-amber-700/60 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">'
                .'<div class="flex items-center gap-3.5">'
                .'<div class="w-12 h-12 rounded-2xl bg-amber-500 text-slate-950 flex items-center justify-center text-2xl shrink-0 shadow-xs font-black">🏆</div>'
                .'<div>'
                .'<div class="text-[10px] font-black uppercase tracking-wider text-amber-700 dark:text-amber-300">Officiële Kampioen</div>'
                .'<a href="'.$profileUrl.'" class="text-base sm:text-lg font-black text-slate-900 dark:text-white hover:text-amber-600 dark:hover:text-amber-400 transition leading-tight block">'.$name.'</a>'
                .'<div class="text-xs text-slate-600 dark:text-slate-300 font-bold mt-0.5">Totaal geproduceerd: <span class="font-mono text-emerald-600 dark:text-emerald-400 font-black">'.$score.'</span></div>'
                .'</div>'
                .'</div>'
                .'<div class="text-left sm:text-right self-start sm:self-center">'
                .'<span class="text-[10px] font-black uppercase tracking-wider text-slate-400 block">Uitgekeerd Prijzenpakket</span>'
                .'<span class="text-sm sm:text-base font-black text-emerald-600 dark:text-emerald-400 font-mono">'.$prize1.'</span>'
                .'<span class="text-xs font-black text-amber-500 font-mono block">+ '.$prize2.'</span>'
                .'</div>'
                .'</div>'."\n\n";
        }, $text);

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

        // Strip special embed shortcodes from prose
        $text = str_ireplace([
            '[cash_event_leaderboard]',
            '[drug_event_leaderboard]',
            '[vault_challenge_leaderboard]',
            '[vault_event_leaderboard]',
        ], '', $text);

        // Headers
        $text = preg_replace('/^####\s+(.*?)$/m', '<h4 class="text-sm font-extrabold text-slate-900 dark:text-white mt-4 mb-2 tracking-tight">$1</h4>', $text);
        $text = preg_replace('/^###\s+(.*?)$/m', '<h3 class="text-base font-black text-slate-900 dark:text-white mt-5 mb-2.5 tracking-tight">$1</h3>', $text);
        $text = preg_replace('/^##\s+(.*?)$/m', '<h2 class="text-lg font-black text-slate-900 dark:text-white mt-6 mb-3 tracking-tight">$1</h2>', $text);
        $text = preg_replace('/\[h2\](.*?)\[\/h2\]/i', '<h2 class="text-lg font-black text-slate-900 dark:text-white mt-6 mb-3 tracking-tight">$1</h2>', $text);
        $text = preg_replace('/\[h3\](.*?)\[\/h3\]/i', '<h3 class="text-base font-black text-slate-900 dark:text-white mt-5 mb-2.5 tracking-tight">$1</h3>', $text);
        $text = preg_replace('/\[h4\](.*?)\[\/h4\]/i', '<h4 class="text-sm font-extrabold text-slate-900 dark:text-white mt-4 mb-2 tracking-tight">$1</h4>', $text);

        // Unordered list items (parse before single-asterisk italics so '* ' bullets are not treated as italics)
        $text = preg_replace('/^[ \t]*[\-\*]\s+(.*?)$/m', '<li class="ml-5 list-disc text-slate-700 dark:text-slate-300 font-medium leading-relaxed">$1</li>', $text);

        // Bold, italic, underline, strikethrough
        $text = preg_replace('/\[b\](.*?)\[\/b\]/i', '<strong class="font-extrabold text-slate-900 dark:text-white">$1</strong>', $text);
        $text = preg_replace('/\*\*(.*?)\*\*/s', '<strong class="font-extrabold text-slate-900 dark:text-white">$1</strong>', $text);
        $text = preg_replace('/\[i\](.*?)\[\/i\]/i', '<em class="italic text-slate-700 dark:text-slate-300">$1</em>', $text);
        $text = preg_replace('/(?<!\*)\*(?!\*|\s)([^\*\n]+?)(?<!\*|\s)\*(?!\*)/', '<em class="italic text-slate-700 dark:text-slate-300">$1</em>', $text);
        $text = preg_replace('/\[u\](.*?)\[\/u\]/i', '<span class="underline decoration-slate-400">$1</span>', $text);
        $text = preg_replace('/\[s\](.*?)\[\/s\]/i', '<del class="line-through text-slate-400">$1</del>', $text);
        $text = preg_replace('/~~(.*?)~~/s', '<del class="line-through text-slate-400">$1</del>', $text);

        // Quote
        $text = preg_replace(
            '/\[quote\](.*?)\[\/quote\]/is',
            '<blockquote class="border-l-4 border-purple-500 bg-purple-50/60 dark:bg-purple-950/40 rounded-r-xl p-3.5 my-3 text-xs sm:text-sm font-semibold text-purple-900 dark:text-purple-200 italic">$1</blockquote>',
            $text
        );
        $text = preg_replace(
            '/^>\s+(.*?)$/m',
            '<blockquote class="border-l-4 border-purple-500 bg-purple-50/60 dark:bg-purple-950/40 rounded-r-xl p-3.5 my-3 text-xs sm:text-sm font-semibold text-purple-900 dark:text-purple-200 italic">$1</blockquote>',
            $text
        );

        // Community Credit Box: [credit=Username]Message[/credit]
        $text = preg_replace_callback('/\[credit=([a-zA-Z0-9_\-\s]+)\](.*?)\[\/credit\]/is', function ($m) {
            $username = trim($m[1]);
            $msg = trim($m[2]);
            $profileUrl = '/speler/'.rawurlencode($username);

            return '<div class="my-4 p-4 rounded-2xl bg-purple-50/70 dark:bg-slate-800 border border-purple-200 dark:border-purple-800/60 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-3.5">
                <div class="flex items-start sm:items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-purple-600 text-white flex items-center justify-center text-lg shrink-0 shadow-xs">
                        <i class="fa-solid fa-lightbulb"></i>
                    </div>
                    <div>
                        <div class="text-[10px] font-black uppercase tracking-wider text-purple-600 dark:text-purple-400">Community Idee & Eervolle Vermelding</div>
                        <div class="text-xs text-slate-700 dark:text-slate-200 mt-0.5 leading-relaxed">'.$msg.'</div>
                    </div>
                </div>
                <a href="'.$profileUrl.'" class="shrink-0 self-start sm:self-center px-3.5 py-2 rounded-xl bg-purple-600 hover:bg-purple-700 active:scale-95 text-white font-black text-xs transition shadow-2xs inline-flex items-center gap-1.5">
                    <span>Profiel '.htmlspecialchars($username).'</span>
                    <i class="fa-solid fa-arrow-right text-[10px]"></i>
                </a>
            </div>';
        }, $text);

        // Inline code `code`
        $text = preg_replace(
            '/`([^`\n]+)`/',
            '<code class="px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-purple-700 dark:text-purple-300 font-mono text-xs">$1</code>',
            $text
        );

        // Horizontal Rule
        $text = preg_replace('/^---$/m', "\n\n".'<hr class="my-3 border-slate-200 dark:border-slate-800" />'."\n\n", $text);

        // Unordered list items
        $text = preg_replace('/^-\s+(.*?)$/m', '<li class="ml-5 list-disc text-slate-700 dark:text-slate-300 font-medium leading-relaxed">$1</li>', $text);

        // Group consecutive <li> elements into a <ul>
        $text = preg_replace_callback('~((?:<li[^>]*>.*?</li>\s*)+)~s', function ($m) {
            $cleanItems = preg_replace('~</li>\s+<li~', '</li><li', trim($m[1]));

            return "\n\n".'<ul class="my-2 space-y-0.5">'.$cleanItems.'</ul>'."\n\n";
        }, $text);

        // Ensure block-level headers and quotes have paragraph boundaries
        $text = preg_replace('~(<(?:h[1-6]|blockquote)[^>]*>.*?</(?:h[1-6]|blockquote)>)~is', "\n\n$1\n\n", $text);

        // Normalise multiple blank lines to exactly two newlines
        $text = preg_replace("/\n{2,}/", "\n\n", $text);

        // Split on double newlines and wrap plain-text segments in <p>
        $segments = preg_split("/\n\n/", $text);
        $segments = array_map(function ($seg) {
            $trimmed = trim($seg);
            if ($trimmed === '') {
                return '';
            }

            // Leave segments that already start with a block-level HTML tag as-is
            if (preg_match('~^\s*<(?:h[1-6]|hr|ul|ol|li|div|blockquote|p)[ >]~i', $trimmed)) {
                return $trimmed;
            }

            return '<p class="mb-2 leading-relaxed">'.nl2br($trimmed).'</p>';
        }, $segments);

        return implode('', $segments);
    }
}
