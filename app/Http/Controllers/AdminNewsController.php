<?php

namespace App\Http\Controllers;

use App\Models\NewsArticle;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdminNewsController extends Controller
{
    /**
     * Overview of news articles in admin panel.
     */
    public function index(): View
    {
        abort_unless(Auth::user()?->canModerate(), 403, 'Toegang geweigerd.');

        $articles = NewsArticle::with('author')
            ->withCount('comments')
            ->latest()
            ->paginate(15);

        return view('admin.news.index', compact('articles'));
    }

    /**
     * Show form to write a new news article.
     */
    public function create(): View
    {
        abort_unless(Auth::user()?->canModerate(), 403, 'Toegang geweigerd.');

        return view('admin.news.create');
    }

    /**
     * Store new news article.
     */
    public function store(Request $request): RedirectResponse
    {
        abort_unless(Auth::user()?->canModerate(), 403, 'Toegang geweigerd.');

        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;

        $validated = $request->validate([
            'title' => ['required', 'string', 'min:3', 'max:150'],
            'emoji' => ['nullable', 'string', 'max:20'],
            'image_url' => ['nullable', 'url', 'max:255'],
            'content' => ['required', 'string', 'min:10'],
            'is_pinned' => ['nullable', 'boolean'],
            'comments_enabled' => ['nullable', 'boolean'],
            'is_published' => ['nullable', 'boolean'],
        ]);

        $baseSlug = Str::slug($validated['title']) ?: ('nieuws-'.time());
        $slug = $baseSlug;
        $counter = 1;
        while (NewsArticle::where('slug', $slug)->exists()) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        try {
            $article = DB::transaction(function () use ($character, $validated, $slug, $request): NewsArticle {
                return NewsArticle::create([
                    'character_id' => $character?->id,
                    'title' => $validated['title'],
                    'slug' => $slug,
                    'emoji' => $validated['emoji'] ?: '📰',
                    'image_url' => $validated['image_url'] ?? null,
                    'content' => $validated['content'],
                    'is_pinned' => $request->boolean('is_pinned'),
                    'comments_enabled' => $request->boolean('comments_enabled'),
                    'is_published' => $request->boolean('is_published', true),
                    'published_at' => now(),
                ]);
            });

            try {
                AuditLogService::log(
                    action: 'news_article_created',
                    category: 'admin',
                    description: "Nieuwsartikel '{$article->title}' (#{$article->id}) gepubliceerd door ".(Auth::user()?->name ?? 'Staff'),
                    character: $character,
                    user: Auth::user(),
                    details: [
                        'article_id' => $article->id,
                        'title' => $article->title,
                        'slug' => $slug,
                    ]
                );
            } catch (\Throwable $e) {
                Log::warning('Failed to log news_article_created audit record: '.$e->getMessage());
            }
        } catch (\Throwable $e) {
            Log::error('Failed to create news article: '.$e->getMessage(), ['exception' => $e]);

            return back()->with('error', 'Er is een fout opgetreden bij het publiceren: '.$e->getMessage())->withInput();
        }

        return redirect()->route('admin.news.index')->with('success', 'Nieuwsartikel succesvol gepubliceerd!');
    }

    /**
     * Show edit form for existing news article.
     */
    public function edit(NewsArticle $article): View
    {
        abort_unless(Auth::user()?->canModerate(), 403, 'Toegang geweigerd.');

        return view('admin.news.edit', compact('article'));
    }

    /**
     * Update existing news article with row-level locking.
     */
    public function update(Request $request, NewsArticle $article): RedirectResponse
    {
        abort_unless(Auth::user()?->canModerate(), 403, 'Toegang geweigerd.');

        $validated = $request->validate([
            'title' => ['required', 'string', 'min:3', 'max:150'],
            'emoji' => ['nullable', 'string', 'max:20'],
            'image_url' => ['nullable', 'url', 'max:255'],
            'content' => ['required', 'string', 'min:10'],
            'is_pinned' => ['nullable', 'boolean'],
            'comments_enabled' => ['nullable', 'boolean'],
            'is_published' => ['nullable', 'boolean'],
            'original_updated_at' => ['nullable', 'integer'],
        ]);

        $article->refresh();
        $wasModifiedConcurrently = $request->filled('original_updated_at')
            && $article->updated_at
            && $article->updated_at->timestamp > (int) $request->input('original_updated_at');

        try {
            DB::transaction(function () use ($article, $validated, $request): void {
                /** @var NewsArticle $lockedArticle */
                $lockedArticle = NewsArticle::where('id', $article->id)->lockForUpdate()->firstOrFail();
                $lockedArticle->update([
                    'title' => $validated['title'],
                    'emoji' => $validated['emoji'] ?: '📰',
                    'image_url' => $validated['image_url'] ?? null,
                    'content' => $validated['content'],
                    'is_pinned' => $request->boolean('is_pinned'),
                    'comments_enabled' => $request->boolean('comments_enabled'),
                    'is_published' => $request->boolean('is_published'),
                ]);
            });

            try {
                AuditLogService::log(
                    action: 'news_article_updated',
                    category: 'admin',
                    description: "Nieuwsartikel '{$article->title}' (#{$article->id}) bijgewerkt door ".(Auth::user()?->name ?? 'Staff'),
                    character: Auth::user()?->character,
                    user: Auth::user(),
                    details: [
                        'article_id' => $article->id,
                        'title' => $article->title,
                        'was_modified_concurrently' => $wasModifiedConcurrently,
                    ]
                );
            } catch (\Throwable $e) {
                Log::warning('Failed to log news_article_updated audit record: '.$e->getMessage());
            }
        } catch (\Throwable $e) {
            Log::error("Failed to update news article #{$article->id}: ".$e->getMessage(), ['exception' => $e]);

            return back()->with('error', 'Er is een fout opgetreden bij het bijwerken van het artikel: '.$e->getMessage())->withInput();
        }

        $redirect = redirect()->route('admin.news.index')->with('success', 'Nieuwsartikel succesvol bijgewerkt!');
        if ($wasModifiedConcurrently) {
            $redirect->with('warning', 'Let op: Dit nieuwsartikel is tussentijds gewijzigd door een andere beheerder vóór deze bewerking.');
        }

        return $redirect;
    }

    /**
     * Quick toggle comments enabled/closed on an article with row lock.
     */
    public function toggleComments(NewsArticle $article): RedirectResponse
    {
        abort_unless(Auth::user()?->canModerate(), 403, 'Toegang geweigerd.');

        try {
            DB::transaction(function () use ($article): void {
                /** @var NewsArticle $lockedArticle */
                $lockedArticle = NewsArticle::where('id', $article->id)->lockForUpdate()->firstOrFail();
                $lockedArticle->comments_enabled = ! $lockedArticle->comments_enabled;
                $lockedArticle->save();
            });

            try {
                AuditLogService::log(
                    action: 'news_comments_toggled',
                    category: 'admin',
                    description: "Reacties voor '{$article->title}' gewijzigd door ".(Auth::user()?->name ?? 'Staff'),
                    character: Auth::user()?->character,
                    user: Auth::user(),
                    details: [
                        'article_id' => $article->id,
                        'comments_enabled' => ! $article->comments_enabled,
                    ]
                );
            } catch (\Throwable $e) {
                Log::warning('Failed to log news_comments_toggled audit record: '.$e->getMessage());
            }
        } catch (\Throwable $e) {
            Log::error("Failed to toggle comments on news article #{$article->id}: ".$e->getMessage(), ['exception' => $e]);

            return back()->with('error', 'Fout bij wijzigen van reactie-status: '.$e->getMessage());
        }

        $article->refresh();
        $status = $article->comments_enabled ? 'geopend voor reacties' : 'gesloten voor reacties';

        return back()->with('success', "Nieuwsartikel '{$article->title}' is nu {$status}.");
    }

    /**
     * Delete news article.
     */
    public function destroy(NewsArticle $article): RedirectResponse
    {
        /** @var User|null $currentUser */
        $currentUser = Auth::user();
        if (! $currentUser || ! $currentUser->isAdmin()) {
            return back()->with('error', 'Alleen administrators kunnen nieuwsberichten verwijderen.');
        }

        $articleTitle = $article->title;
        $articleId = $article->id;

        try {
            DB::transaction(function () use ($article): void {
                /** @var NewsArticle $lockedArticle */
                $lockedArticle = NewsArticle::where('id', $article->id)->lockForUpdate()->firstOrFail();
                $lockedArticle->delete();
            });

            try {
                AuditLogService::log(
                    action: 'news_article_deleted',
                    category: 'admin',
                    description: "Nieuwsartikel '{$articleTitle}' (#{$articleId}) definitief verwijderd door ".($currentUser->name ?? 'Admin'),
                    character: $currentUser->character,
                    user: $currentUser,
                    details: [
                        'article_id' => $articleId,
                        'title' => $articleTitle,
                    ]
                );
            } catch (\Throwable $e) {
                Log::warning('Failed to log news_article_deleted audit record: '.$e->getMessage());
            }
        } catch (\Throwable $e) {
            Log::error("Failed to delete news article #{$article->id}: ".$e->getMessage(), ['exception' => $e]);

            return back()->with('error', 'Fout bij verwijderen van artikel: '.$e->getMessage());
        }

        return redirect()->route('admin.news.index')->with('success', 'Nieuwsartikel is definitief verwijderd.');
    }
}
