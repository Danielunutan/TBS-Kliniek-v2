<?php

namespace App\Http\Controllers;

use App\Models\Character;
use App\Models\Friend;
use App\Models\GameNotification;
use App\Models\GuestbookEntry;
use App\Models\User;
use App\Services\EnergyService;
use App\Services\LevelService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function __construct(
        protected EnergyService $energyService,
        protected LevelService $levelService,
    ) {}

    /**
     * Show player profile by name.
     */
    public function show(?string $name = null): View|RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $myCharacter = $user->character;
        $this->energyService->refresh($myCharacter);

        if (! $name) {
            $profileCharacter = $myCharacter;
        } else {
            $profileCharacter = Character::where('name', $name)->first();
            if (! $profileCharacter) {
                return redirect()->route('dashboard')->with('error', "Patiënt '{$name}' is niet aangetroffen in het dossier.");
            }
        }

        $nextLevelXp = $this->levelService->getXpRequiredForNextLevel($profileCharacter->level);
        $xpPercentage = min(100, max(0, (int) round(($profileCharacter->xp / max(1, $nextLevelXp)) * 100)));

        $guestbookEntries = $profileCharacter->guestbookEntries()->with('author')->paginate(10);

        // Load accepted friends for this character
        $friends = Friend::with(['friend.gang'])
            ->where('character_id', $profileCharacter->id)
            ->where('status', 'accepted')
            ->get();
        $friendsCount = $friends->count();
        $bff = $friends->firstWhere('is_bff', true);

        // Friendship status between the logged in character ($myCharacter) and viewed character ($profileCharacter)
        $isOwnProfile = $myCharacter->id === $profileCharacter->id;
        $friendStatus = 'none'; // 'none', 'accepted', 'pending_sent', 'pending_received'
        $pendingRequestRecord = null;

        if (! $isOwnProfile) {
            $outgoing = Friend::where('character_id', $myCharacter->id)
                ->where('friend_id', $profileCharacter->id)
                ->first();
            $incoming = Friend::where('character_id', $profileCharacter->id)
                ->where('friend_id', $myCharacter->id)
                ->first();

            if (($outgoing && $outgoing->status === 'accepted') || ($incoming && $incoming->status === 'accepted')) {
                $friendStatus = 'accepted';
            } elseif ($outgoing && $outgoing->status === 'pending') {
                $friendStatus = 'pending_sent';
            } elseif ($incoming && $incoming->status === 'pending') {
                $friendStatus = 'pending_received';
                $pendingRequestRecord = $incoming;
            }
        }

        // $character must be passed for layouts.app navigation bar and sidebar
        $character = $myCharacter;

        return view('game.profile', compact(
            'character',
            'myCharacter',
            'profileCharacter',
            'nextLevelXp',
            'xpPercentage',
            'guestbookEntries',
            'friends',
            'friendsCount',
            'bff',
            'isOwnProfile',
            'friendStatus',
            'pendingRequestRecord'
        ));
    }

    /**
     * Post a new guestbook message.
     */
    public function postGuestbook(Request $request, string $name): RedirectResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'min:3', 'max:500'],
        ]);

        /** @var User $user */
        $user = Auth::user();
        $authorCharacter = $user->character;

        $targetCharacter = Character::where('name', $name)->firstOrFail();

        GuestbookEntry::create([
            'profile_character_id' => $targetCharacter->id,
            'author_character_id' => $authorCharacter->id,
            'message' => strip_tags($validated['message']),
        ]);

        if ($targetCharacter->id !== $authorCharacter->id) {
            GameNotification::create([
                'character_id' => $targetCharacter->id,
                'type' => 'guestbook',
                'icon' => 'fa-book-open',
                'title' => 'Nieuw gastenboekbericht!',
                'message' => "{$authorCharacter->name} heeft een bericht achtergelaten in je gastenboek.",
            ]);
        }

        return back()
            ->with('profile_tab', 'gastenboek')
            ->with('success', 'Je bericht is geplaatst in het gastenboek!');
    }

    /**
     * Update own status and bio.
     */
    public function updateBio(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'status_message' => ['nullable', 'string', 'max:120'],
            'bio' => ['nullable', 'string', 'max:1000'],
        ]);

        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;

        $character->update([
            'status_message' => strip_tags($validated['status_message'] ?? $character->status_message),
            'bio' => strip_tags($validated['bio'] ?? $character->bio),
        ]);

        return back()->with('success', 'Je bio en status zijn succesvol bijgewerkt.');
    }

    /**
     * Update player avatar portrait.
     */
    public function updateAvatar(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'avatar' => ['required', 'string'],
        ]);

        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;

        $allowedAvatars = [
            'assets/characters/henkie.jpg',
            'assets/characters/avatar_male_1.jpg',
            'assets/characters/avatar_male_2.jpg',
            'assets/characters/avatar_male_3.jpg',
            'assets/characters/avatar_male_4.jpg',
            'assets/characters/avatar_male_5.jpg',
            'assets/characters/avatar_male_6.jpg',
            'assets/characters/avatar_female_1.jpg',
            'assets/characters/avatar_female_2.jpg',
            'assets/characters/avatar_female_3.jpg',
            'assets/characters/avatar_female_4.jpg',
            'assets/characters/avatar_female_5.jpg',
            'assets/characters/avatar_female_6.jpg',
        ];

        if (! in_array($validated['avatar'], $allowedAvatars, true)) {
            return back()->with('error', 'Ongeldige profielfoto geselecteerd.');
        }

        $character->update(['avatar' => $validated['avatar']]);

        return back()->with('success', 'Je profielfoto is succesvol aangepast!');
    }
}
