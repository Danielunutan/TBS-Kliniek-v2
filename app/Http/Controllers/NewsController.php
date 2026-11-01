<?php

namespace App\Http\Controllers;

use App\Models\Character;
use App\Models\GameNotification;
use App\Models\NewsArticle;
use App\Models\NewsComment;
use App\Models\User;
use App\Services\EnergyService;
use App\Services\EventDrugsProductionService;
use App\Services\EventVaultCrackingService;
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

        $this->ensureEndedEventsResolved();

        $pinnedArticles = NewsArticle::with(['author.gang', 'author.user'])
            ->withCount('comments')
            ->published()
            ->where('is_pinned', true)
            ->orderByRaw('COALESCE(published_at, created_at) DESC')
            ->orderByDesc('id')
            ->get();

        $articles = NewsArticle::with(['author.gang', 'author.user'])
            ->withCount('comments')
            ->published()
            ->orderByDesc('is_pinned')
            ->orderByRaw('COALESCE(published_at, created_at) DESC')
            ->orderByDesc('id')
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

        $this->ensureEndedEventsResolved();

        $isStaff = $user->isStaff() || $user->isAdmin();
        $article = NewsArticle::with(['author.gang', 'author.user'])
            ->withCount('comments')
            ->where('slug', $slug)
            ->when(! $isStaff, fn ($q) => $q->published())
            ->firstOrFail();

        $article->increment('views_count');

        $comments = $article->comments()
            ->with(['character.gang', 'character.user'])
            ->latest()
            ->paginate(15);

        $otherNews = NewsArticle::where('id', '!=', $article->id)
            ->published()
            ->orderByRaw('COALESCE(published_at, created_at) DESC')
            ->orderByDesc('id')
            ->take(5)
            ->get();

        return view('game.news.show', compact('character', 'article', 'comments', 'otherNews'));
    }

    /**
     * Ensure completed events have resolved their winners and synced winner announcements.
     */
    protected function ensureEndedEventsResolved(): void
    {
        try {
            if (EventDrugsProductionService::hasEnded() && ! GameNotification::where('type', EventDrugsProductionService::NOTIFICATION_TYPE)->exists()) {
                EventDrugsProductionService::resolveWinner();
            }

            $winnerArticle = NewsArticle::where('slug', EventDrugsProductionService::WINNER_ARTICLE_SLUG)->first(['id', 'title', 'content']);
            if (
                ! $winnerArticle
                || str_contains($winnerArticle->content, '<div')
                || str_contains($winnerArticle->content, 'ds. Fabian van Salmen')
                || str_starts_with($winnerArticle->title, '🏆')
            ) {
                EventDrugsProductionService::syncWinnerArticle();
            }

            $challengeArticle = NewsArticle::where('slug', EventVaultCrackingService::EVENT_SLUG)->first(['id', 'title', 'content']);
            if (
                ! $challengeArticle
                || str_contains($challengeArticle->content, '/kluis]')
                || str_contains($challengeArticle->content, '/koffers]')
                || str_starts_with($challengeArticle->title, '🔐')
            ) {
                EventVaultCrackingService::syncChallengeArticle();
            }

            if (EventVaultCrackingService::hasEnded() && ! GameNotification::where('type', EventVaultCrackingService::NOTIFICATION_TYPE)->exists()) {
                EventVaultCrackingService::resolveWinner();
            }
        } catch (\Throwable) {
            // Do not interrupt page rendering if event resolution fails
        }
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

        if ($character->isComatose()) {
            return back()->with('error', 'Je verkeert in Code Blauw (Hartstilstand)! Dien eerst een '.Character::REVIVE_SERUM_NAME.' toe.');
        }

        $validated = $request->validate([
            'content' => ['required', 'string', 'min:2', 'max:500'],
        ]);

        $cleanedContent = trim(strip_tags((string) $validated['content']));
        if (mb_strlen($cleanedContent) < 2) {
            return back()->with('error', 'Voer een geldige reactie van minimaal 2 tekens in.');
        }

        NewsComment::create([
            'news_article_id' => $article->id,
            'character_id' => $character->id,
            'content' => $cleanedContent,
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
