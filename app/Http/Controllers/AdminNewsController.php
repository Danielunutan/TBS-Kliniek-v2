<?php

namespace App\Http\Controllers;

use App\Models\NewsArticle;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdminNewsController extends Controller
{
    /**
     * Overview of news articles in admin panel.
     */
    public function index(): View
    {
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
        return view('admin.news.create');
    }

    /**
     * Store new news article.
     */
    public function store(Request $request): RedirectResponse
    {
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

        $baseSlug = Str::slug($validated['title']);
        $slug = $baseSlug;
        $counter = 1;
        while (NewsArticle::where('slug', $slug)->exists()) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        NewsArticle::create([
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

        return redirect()->route('admin.news.index')->with('success', 'Nieuwsartikel succesvol gepubliceerd!');
    }

    /**
     * Show edit form for existing news article.
     */
    public function edit(NewsArticle $article): View
    {
        return view('admin.news.edit', compact('article'));
    }

    /**
     * Update existing news article.
     */
    public function update(Request $request, NewsArticle $article): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'min:3', 'max:150'],
            'emoji' => ['nullable', 'string', 'max:20'],
            'image_url' => ['nullable', 'url', 'max:255'],
            'content' => ['required', 'string', 'min:10'],
            'is_pinned' => ['nullable', 'boolean'],
            'comments_enabled' => ['nullable', 'boolean'],
            'is_published' => ['nullable', 'boolean'],
        ]);

        $article->update([
            'title' => $validated['title'],
            'emoji' => $validated['emoji'] ?: '📰',
            'image_url' => $validated['image_url'] ?? null,
            'content' => $validated['content'],
            'is_pinned' => $request->boolean('is_pinned'),
            'comments_enabled' => $request->boolean('comments_enabled'),
            'is_published' => $request->boolean('is_published'),
        ]);

        return redirect()->route('admin.news.index')->with('success', 'Nieuwsartikel succesvol bijgewerkt!');
    }

    /**
     * Quick toggle comments enabled/closed on an article.
     */
    public function toggleComments(NewsArticle $article): RedirectResponse
    {
        $article->comments_enabled = ! $article->comments_enabled;
        $article->save();

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

        $article->delete();

        return redirect()->route('admin.news.index')->with('success', 'Nieuwsartikel is definitief verwijderd.');
    }
}
