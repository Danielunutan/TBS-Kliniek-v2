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
use Illuminate\View\View;

class TimelineController extends Controller
{
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

        $topRankings = Character::orderByDesc('respect')->take(10)->get();

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

        $validated = $request->validate([
            'content' => ['required', 'string', 'min:2', 'max:280'],
        ]);

        TimelinePost::create([
            'character_id' => $character->id,
            'content' => trim(strip_tags($validated['content'])),
            'likes_count' => 0,
        ]);

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

        $existing = TimelineLike::where('timeline_post_id', $post->id)
            ->where('character_id', $character->id)
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

        $validated = $request->validate([
            'content' => ['required', 'string', 'min:1', 'max:300'],
        ]);

        TimelineComment::create([
            'timeline_post_id' => $post->id,
            'character_id' => $character->id,
            'content' => trim(strip_tags($validated['content'])),
        ]);

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
