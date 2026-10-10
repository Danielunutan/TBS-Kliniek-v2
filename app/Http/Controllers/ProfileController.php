<?php

namespace App\Http\Controllers;

use App\Models\Character;
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

        return view('game.profile', compact(
            'myCharacter',
            'profileCharacter',
            'nextLevelXp',
            'xpPercentage',
            'guestbookEntries'
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

        return back()->with('success', 'Je bericht is geplaatst in het gastenboek!');
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
}
