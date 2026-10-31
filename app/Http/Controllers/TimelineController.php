<?php

namespace App\Http\Controllers;

use App\Models\Character;
use App\Models\TimelineComment;
use App\Models\TimelineLike;
use App\Models\TimelinePost;
use App\Models\User;
use App\Services\EnergyService;
use App\Services\LevelService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TimelineController extends Controller
{
    /**
     * Number of posts per day that give XP.
     */
    public const DAILY_XP_POSTS = 10;

    /**
     * Number of comments per day that give XP.
     */
    public const DAILY_XP_COMMENTS = 20;

    public function __construct(
        protected EnergyService $energyService,
        protected LevelService $levelService,
    ) {}

    /**
     * Show timeline feed (Openbare Twitter / Feed van de Kliniek).
     */
    public function index(): View
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;
        $this->energyService->refresh($character);

        $posts = TimelinePost::with([
            'character.gang',
            'character.user',
            'comments' => fn ($q) => $q->with(['character.gang', 'character.user'])->oldest(),
            'likes',
        ])
            ->latest()
            ->paginate(15);

        $topRankings = Character::getTopRankings(10);

        return view('game.timeline.index', compact('character', 'posts', 'topRankings'));
    }

    /**
     * Post a new status on the public timeline.
     */
    public function store(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;

        if ($character->isImprisoned()) {
            return back()->with('error', 'In de isoleercel heb je geen toegang tot de publieke feed!');
        }

        if ($character->isComatose()) {
            return back()->with('error', 'Je verkeert in Code Blauw (Hartstilstand)! Dien eerst een '.Character::REVIVE_SERUM_NAME.' toe.');
        }

        $validated = $request->validate([
            'content' => ['required', 'string', 'min:2', 'max:280'],
        ]);

        $cleanContent = trim(strip_tags($validated['content']));
        if (mb_strlen($cleanContent) < 2) {
            return back()->with('error', 'Voer een geldig bericht van minimaal 2 tekens in.');
        }

        TimelinePost::create([
            'character_id' => $character->id,
            'content' => $cleanContent,
            'likes_count' => 0,
        ]);

        // Posting costs nothing, so XP is capped per day; otherwise a script could post its way to any level.
        // Track daily XP awards in Cache rather than counting live rows so post-delete-repost cannot reset the cap.
        $xpCacheKey = 'timeline_xp_posts:'.$character->id.':'.now()->toDateString();
        Cache::add($xpCacheKey, 0, now()->endOfDay());
        $postsToday = (int) Cache::increment($xpCacheKey);
        if ($postsToday > self::DAILY_XP_POSTS) {
            return back()->with('success', 'Je bericht is live geplaatst op de openbare kliniek tijdlijn! (Dagelijkse XP-limiet voor berichten bereikt)');
        }

        $this->levelService->addXp($character, 10);

        return back()->with('success', 'Je bericht is live geplaatst op de openbare kliniek tijdlijn! (+10 XP)');
    }

    /**
     * Like or react to a timeline post with an emoji.
     */
    public function like(Request $request, TimelinePost $post): RedirectResponse|JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;

        $emoji = $request->input('emoji', '❤️');
        $allowedEmojis = ['❤️', '🔥', '😂', '👑', '💀', '🥊', '👍', '💪'];
        if (! in_array($emoji, $allowedEmojis, true)) {
            $emoji = '❤️';
        }

        [$action, $count] = DB::transaction(function () use ($post, $character, $emoji): array {
            TimelinePost::where('id', $post->id)->lockForUpdate()->first();

            $existing = TimelineLike::where('timeline_post_id', $post->id)
                ->where('character_id', $character->id)
                ->lockForUpdate()
                ->first();

            $action = 'liked';
            if ($existing) {
                if ($existing->emoji === $emoji) {
                    // Toggle unlike
                    $existing->delete();
                    $action = 'unliked';
                } else {
                    // Change reaction emoji
                    $existing->update(['emoji' => $emoji]);
                    $action = 'changed';
                }
            } else {
                TimelineLike::create([
                    'timeline_post_id' => $post->id,
                    'character_id' => $character->id,
                    'emoji' => $emoji,
                ]);
            }

            $count = $post->likes()->count();
            $post->update(['likes_count' => $count]);

            return [$action, $count];
        });

        if ($request->expectsJson() || $request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'action' => $action,
                'likes_count' => $count,
                'emoji' => $action === 'unliked' ? null : $emoji,
            ]);
        }

        $msg = match ($action) {
            'unliked' => 'Reactie verwijderd.',
            'changed' => "Reactie aangepast naar {$emoji}!",
            default => "Je hebt gereageerd met {$emoji}!",
        };

        return back()->with('success', $msg);
    }

    /**
     * Add a comment / reply to a timeline post.
     */
    public function comment(Request $request, TimelinePost $post): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;

        if ($character->isImprisoned()) {
            return back()->with('error', 'In de isoleercel kun je niet reageren op de tijdlijn!');
        }

        if ($character->isComatose()) {
            return back()->with('error', 'Je verkeert in Code Blauw (Hartstilstand)! Dien eerst een '.Character::REVIVE_SERUM_NAME.' toe.');
        }

        $validated = $request->validate([
            'content' => ['required', 'string', 'min:1', 'max:300'],
        ]);

        $cleanComment = trim(strip_tags($validated['content']));
        if ($cleanComment === '') {
            return back()->with('error', 'Voer een geldige reactie in.');
        }

        TimelineComment::create([
            'timeline_post_id' => $post->id,
            'character_id' => $character->id,
            'content' => $cleanComment,
        ]);

        $xpCommentKey = 'timeline_xp_comments:'.$character->id.':'.now()->toDateString();
        Cache::add($xpCommentKey, 0, now()->endOfDay());
        $commentsToday = (int) Cache::increment($xpCommentKey);
        if ($commentsToday > self::DAILY_XP_COMMENTS) {
            return back()->with('success', 'Je reactie is geplaatst! (Dagelijkse XP-limiet voor reacties bereikt)');
        }

        $this->levelService->addXp($character, 5);

        return back()->with('success', 'Je reactie is geplaatst! (+5 XP)');
    }

    /**
     * Delete a comment on a timeline post.
     */
    public function destroyComment(TimelineComment $comment): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;

        if ($comment->character_id !== $character->id && ! $user->canModerate()) {
            return back()->with('error', 'Je hebt geen toestemming om deze reactie te verwijderen.');
        }

        $comment->delete();

        return back()->with('success', 'Reactie succesvol verwijderd.');
    }

    /**
     * Delete timeline post (by author or moderators/admins/owner).
     */
    public function destroy(TimelinePost $post): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;

        if ($post->character_id !== $character->id && ! $user->canModerate()) {
            return back()->with('error', 'Je hebt geen toestemming om dit bericht te verwijderen.');
        }

        $isModeratorAction = ($post->character_id !== $character->id);

        $post->delete();

        if ($isModeratorAction) {
            return back()->with('success', 'Bericht succesvol verwijderd van de tijdlijn (als moderator/beheerder).');
        }

        return back()->with('success', 'Bericht verwijderd van de tijdlijn.');
    }
}
