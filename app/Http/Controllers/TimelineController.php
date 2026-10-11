<?php

namespace App\Http\Controllers;

use App\Models\Character;
use App\Models\TimelinePost;
use App\Models\User;
use App\Services\EnergyService;
use App\Services\LevelService;
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
        $character = Auth::user()->character;
        $this->energyService->refresh($character);

        $posts = TimelinePost::with(['character.gang', 'character.user'])
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
        $character = Auth::user()->character;

        if ($character->isImprisoned()) {
            return back()->with('error', 'In de isoleercel heb je geen toegang tot de publieke feed!');
        }

        $validated = $request->validate([
            'content' => ['required', 'string', 'min:2', 'max:280'],
        ]);

        TimelinePost::create([
            'character_id' => $character->id,
            'content' => strip_tags($validated['content']),
            'likes_count' => 0,
        ]);

        $this->levelService->addXp($character, 10);

        return back()->with('success', 'Je bericht is live geplaatst op de openbare kliniek tijdlijn! (+10 XP)');
    }

    /**
     * Like a timeline post.
     */
    public function like(TimelinePost $post): RedirectResponse
    {
        $post->increment('likes_count');

        return back()->with('success', 'Je hebt dit bericht geliked! ❤️');
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
