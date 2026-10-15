<?php

namespace App\Http\Controllers;

use App\Models\NewsArticle;
use App\Models\NewsComment;
use App\Models\User;
use App\Services\EnergyService;
use App\Services\ReferralService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class NewsController extends Controller
{
    public function __construct(
        protected EnergyService $energyService,
    ) {}

    /**
     * Display public news overview.
     */
    public function index(): View
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;
        $this->energyService->refresh($character);

        $pinnedArticles = NewsArticle::with(['author.gang', 'author.user'])
            ->withCount('comments')
            ->where('is_published', true)
            ->where('is_pinned', true)
            ->latest()
            ->get();

        $articles = NewsArticle::with(['author.gang', 'author.user'])
            ->withCount('comments')
            ->where('is_published', true)
            ->orderByDesc('is_pinned')
            ->latest()
            ->paginate(10);

        $referralService = app(ReferralService::class);
        $referralStats = $referralService->getStats($character);

        return view('game.news.index', compact('character', 'pinnedArticles', 'articles', 'referralStats'));
    }

    /**
     * Display single news article.
     */
    public function show(string $slug): View
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;
        $this->energyService->refresh($character);

        $article = NewsArticle::with(['author.gang', 'author.user'])
            ->where('slug', $slug)
            ->where('is_published', true)
            ->firstOrFail();

        $article->increment('views_count');

        $comments = $article->comments()
            ->with(['character.gang', 'character.user'])
            ->latest()
            ->paginate(15);

        $otherNews = NewsArticle::where('id', '!=', $article->id)
            ->where('is_published', true)
            ->latest()
            ->take(5)
            ->get();

        return view('game.news.show', compact('character', 'article', 'comments', 'otherNews'));
    }

    /**
     * Post a reaction/comment on an article.
     */
    public function comment(Request $request, NewsArticle $article): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;

        if (! $article->comments_enabled) {
            return back()->with('error', 'Dit nieuwsbericht is gesloten voor reacties.');
        }

        if ($character->isImprisoned()) {
            return back()->with('error', 'In de isoleercel heb je geen toegang om reacties te plaatsen.');
        }

        $validated = $request->validate([
            'content' => ['required', 'string', 'min:2', 'max:500'],
        ]);

        NewsComment::create([
            'news_article_id' => $article->id,
            'character_id' => $character->id,
            'content' => strip_tags($validated['content']),
        ]);

        return back()->with('success', 'Je reactie is succesvol geplaatst onder dit nieuwsbericht!');
    }

    /**
     * Delete a comment (author or moderator).
     */
    public function destroyComment(NewsComment $comment): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;

        $canDelete = ($comment->character_id === $character->id) || $user->canModerate();

        if (! $canDelete) {
            return back()->with('error', 'Je hebt geen toestemming om deze reactie te verwijderen.');
        }

        $comment->delete();

        return back()->with('success', 'Reactie succesvol verwijderd.');
    }
}
