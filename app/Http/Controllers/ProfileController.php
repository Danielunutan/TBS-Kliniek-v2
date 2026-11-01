<?php

namespace App\Http\Controllers;

use App\Models\Character;
use App\Models\Friend;
use App\Models\GameNotification;
use App\Models\GuestbookEntry;
use App\Models\ProfileRespect;
use App\Models\ProfileVisit;
use App\Models\User;
use App\Services\EnergyService;
use App\Services\HalloweenService;
use App\Services\LevelService;
use App\Support\ProfileStickerCatalog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
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
            $profileCharacter = Character::with(['user', 'gang'])->where('name', $name)->first();
            if (! $profileCharacter) {
                return redirect()->route('dashboard')->with('error', "Patiënt '{$name}' is niet aangetroffen in het dossier.");
            }
        }

        $nextLevelXp = $this->levelService->getXpRequiredForNextLevel($profileCharacter->level);
        $xpPercentage = min(100, max(0, (int) round(($profileCharacter->xp / max(1, $nextLevelXp)) * 100)));

        $guestbookEntries = $profileCharacter->guestbookEntries()->with('author.user')->paginate(10);

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

        // Respect and profile customization data
        $hasGivenRespectToday = false;
        if (! $isOwnProfile) {
            $hasGivenRespectToday = ProfileRespect::where('giver_id', $myCharacter->id)
                ->where('receiver_id', $profileCharacter->id)
                ->where('created_at', '>=', now()->subHours(24))
                ->exists();
        }
        $totalRespectsReceived = ProfileRespect::where('receiver_id', $profileCharacter->id)->count();
        $badges = $profileCharacter->getBadges();
        $badgeSets = Character::getBadgeSets();
        $psychiatricReport = $profileCharacter->getPsychiatricReport();
        $availableTitles = $isOwnProfile ? $myCharacter->getAvailableTitles() : [];

        // Record profile visit if viewing someone else's profile
        if (! $isOwnProfile) {
            ProfileVisit::updateOrCreate(
                [
                    'visitor_id' => $myCharacter->id,
                    'visited_id' => $profileCharacter->id,
                ],
                [
                    'visited_at' => now(),
                ]
            );
        }

        // Recent visitors to this profile (Laatste Bezoekers)
        $recentVisitors = ProfileVisit::with(['visitor.gang', 'visitor.user'])
            ->where('visited_id', $profileCharacter->id)
            ->where('visitor_id', '!=', $profileCharacter->id)
            ->orderByDesc('visited_at')
            ->limit(12)
            ->get();

        // Profiles recently visited by this character (Laatst bezochte TBS'ers)
        $recentlyVisited = ProfileVisit::with(['visited.gang', 'visited.user'])
            ->where('visitor_id', $profileCharacter->id)
            ->where('visited_id', '!=', $profileCharacter->id)
            ->orderByDesc('visited_at')
            ->limit(12)
            ->get();

        // Profile customization: Stickers, Borders and Background Themes
        $unlockedStickers = $myCharacter->getUnlockedStickers();
        $allStickers = ProfileStickerCatalog::stickers();
        $allThemes = ProfileStickerCatalog::themes();
        $allBorders = HalloweenService::getBorders();
        $unlockedBorders = $myCharacter->unlocked_borders ?? [];
        $activeSticker = $profileCharacter->getActiveSticker();
        $activeTheme = $profileCharacter->getActiveProfileTheme();

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
            'pendingRequestRecord',
            'hasGivenRespectToday',
            'totalRespectsReceived',
            'badges',
            'badgeSets',
            'psychiatricReport',
            'availableTitles',
            'unlockedStickers',
            'allStickers',
            'allThemes',
            'allBorders',
            'unlockedBorders',
            'activeSticker',
            'activeTheme',
            'recentVisitors',
            'recentlyVisited'
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

        if ($authorCharacter->isImprisoned()) {
            return back()->with('error', 'Vanuit de isoleercel kun je geen gastenboekberichten plaatsen!');
        }

        if ($authorCharacter->isComatose()) {
            return back()->with('error', 'Je verkeert in Code Blauw (Hartstilstand)! Dien eerst een '.Character::REVIVE_SERUM_NAME.' toe.');
        }

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
     * Delete a guestbook entry (author, profile owner, or moderator).
     */
    public function destroyGuestbook(GuestbookEntry $entry): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;

        $canDelete = ($entry->author_character_id === $character->id)
            || ($entry->profile_character_id === $character->id)
            || $user->canModerate();

        if (! $canDelete) {
            return back()->with('error', 'Je hebt geen toestemming om dit gastenboekbericht te verwijderen.');
        }

        $entry->delete();

        return back()
            ->with('profile_tab', 'gastenboek')
            ->with('success', 'Gastenboekbericht succesvol verwijderd.');
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
     * Update player avatar portrait (preset or custom upload).
     */
    public function updateAvatar(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;

        // 1. Custom file upload directly from device
        if ($request->hasFile('custom_avatar')) {
            $request->validate([
                'custom_avatar' => ['required', 'image', 'mimes:jpeg,png,jpg,webp,gif', 'max:5120'],
            ]);

            $file = $request->file('custom_avatar');
            // Use the extension of the detected content type, not the client file name. The mimes rule checks content,
            // so a file named *.html with image bytes would otherwise be stored and served as HTML.
            $ext = strtolower($file->guessExtension() ?: 'jpg');
            $filename = 'avatar_user_'.$user->id.'_'.time().'_'.Str::random(6).'.'.$ext;
            $uploadDir = public_path('uploads/avatars');

            if (! is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $file->move($uploadDir, $filename);
            $relativePath = 'uploads/avatars/'.$filename;

            $character->avatar_custom = $relativePath;
            $character->avatar = $relativePath;
            $character->save();

            return back()->with('success', '📸 Je eigen profielfoto is succesvol geüpload en direct ingesteld!');
        }

        // 2. Preset selection
        $validated = $request->validate([
            'avatar' => ['required', 'string'],
        ]);

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

        $character->avatar_custom = null;
        $character->avatar = $validated['avatar'];
        $character->save();

        return back()->with('success', 'Je profielfoto is succesvol aangepast!');
    }

    /**
     * Offer a cigarette & respect point to a fellow inmate.
     */
    public function giveRespect(string $name): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $myCharacter = $user->character;

        if ($myCharacter->isImprisoned()) {
            return back()->with('error', 'Vanuit de isoleercel kun je geen respect uitdelen!');
        }

        if ($myCharacter->isComatose()) {
            return back()->with('error', 'Je verkeert in Code Blauw (Hartstilstand)! Dien eerst een '.Character::REVIVE_SERUM_NAME.' toe.');
        }

        $targetCharacter = Character::where('name', $name)->firstOrFail();

        if ($targetCharacter->id === $myCharacter->id) {
            return back()->with('error', 'Je kunt jezelf geen respect aanbieden!');
        }

        $errorMsg = DB::transaction(function () use ($myCharacter, $targetCharacter): ?string {
            Character::where('id', $myCharacter->id)->lockForUpdate()->first();

            $recent = ProfileRespect::where('giver_id', $myCharacter->id)
                ->where('receiver_id', $targetCharacter->id)
                ->where('created_at', '>=', now()->subHours(24))
                ->first();

            if ($recent) {
                $hoursLeft = max(1, 24 - (int) $recent->created_at->diffInHours(now()));

                return "Je hebt {$targetCharacter->name} vandaag al respect gegeven! Probeer het over ~{$hoursLeft}u weer.";
            }

            ProfileRespect::create([
                'giver_id' => $myCharacter->id,
                'receiver_id' => $targetCharacter->id,
            ]);

            $targetCharacter->addRespect(1);
            $targetCharacter->save();

            GameNotification::create([
                'character_id' => $targetCharacter->id,
                'type' => 'respect',
                'icon' => 'fa-smoking',
                'title' => 'Respect ontvangen!',
                'message' => "{$myCharacter->name} heeft jou een sigaret & respectpunt aangeboden op je profiel! (+1 Respect)",
            ]);

            return null;
        });

        if ($errorMsg !== null) {
            return back()->with('error', $errorMsg);
        }

        return back()->with('success', "Je hebt {$targetCharacter->name} een peuk en respect aangeboden! (+1 Respect)");
    }

    /**
     * Update active character dossier title.
     */
    public function updateTitle(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:60'],
        ]);

        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;

        $availableTitles = collect($character->getAvailableTitles());
        $matchedTitle = $availableTitles->firstWhere('id', $validated['title']);

        if (! $matchedTitle || ! $matchedTitle['unlocked']) {
            return back()->with('error', 'Deze titel heb je nog niet ontgrendeld!');
        }

        $character->update(['title' => $matchedTitle['id']]);

        return back()->with('success', "Je actieve titel is succesvol gewijzigd naar '{$matchedTitle['name']}'!");
    }

    /**
     * Update complete patient profile customizations (stickers, background theme, color, title, bio, status).
     */
    public function customize(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;

        $validated = $request->validate([
            'profile_sticker' => ['nullable', 'string', 'max:50'],
            'profile_bg_theme' => ['nullable', 'string', 'max:50'],
            'profile_color' => ['nullable', 'string', 'in:purple,rose,emerald,amber,blue,indigo,dark'],
            'title' => ['nullable', 'string', 'max:60'],
            'status_message' => ['nullable', 'string', 'max:120'],
            'bio' => ['nullable', 'string', 'max:1000'],
            'equipped_border' => ['nullable', 'string', 'max:50'],
            'custom_name_color' => ['nullable', 'string', 'max:30'],
            'custom_name_gradient' => ['nullable', 'string', 'max:40'],
            'custom_name_glow' => ['nullable', 'string', 'max:30'],
            'sparkle_enabled' => ['nullable', 'boolean'],
        ]);

        $updates = [];

        // 1. Sticker validation
        if ($request->has('profile_sticker')) {
            $stickerKey = $validated['profile_sticker'];
            if ($stickerKey === '' || $stickerKey === 'none') {
                $updates['profile_sticker'] = null;
            } else {
                $unlocked = $character->getUnlockedStickers();
                if (in_array($stickerKey, $unlocked, true)) {
                    $updates['profile_sticker'] = $stickerKey;
                }
            }
        }

        // 2. Background theme
        if (! empty($validated['profile_bg_theme'])) {
            $themes = ProfileStickerCatalog::themes();
            if (isset($themes[$validated['profile_bg_theme']])) {
                $updates['profile_bg_theme'] = $validated['profile_bg_theme'];
            }
        }

        // 3. Profile Accent Color
        if (! empty($validated['profile_color'])) {
            $updates['profile_color'] = $validated['profile_color'];
        }

        // 4. Dossier Title
        if (! empty($validated['title'])) {
            $availableTitles = collect($character->getAvailableTitles());
            $matched = $availableTitles->firstWhere('id', $validated['title']);
            if ($matched && $matched['unlocked']) {
                $updates['title'] = $matched['id'];
            }
        }

        // 5. Bio & Status
        if ($request->has('status_message')) {
            $updates['status_message'] = strip_tags((string) $validated['status_message']);
        }
        if ($request->has('bio')) {
            $updates['bio'] = strip_tags((string) $validated['bio']);
        }

        // 6. Avatar Border / Frame
        if ($request->has('equipped_border')) {
            $borderKey = $validated['equipped_border'];
            if (empty($borderKey) || $borderKey === 'none') {
                $updates['equipped_border'] = null;
            } else {
                if ($character->hasBorder($borderKey)) {
                    $updates['equipped_border'] = $borderKey;
                }
            }
        }

        // 7. Name Color, Kleuroverloop (Gradient), Glow & Sparkle Shimmer
        $perks = $character->perks ?? [];
        $perksChanged = false;

        if ($character->canCustomizeNameColor()) {
            if ($request->has('custom_name_color')) {
                $allowedColors = array_keys(Character::nameColorCatalog());
                $chosenColor = $validated['custom_name_color'] ?? null;
                $perks['custom_name_color'] = ($chosenColor && in_array($chosenColor, $allowedColors, true)) ? $chosenColor : null;
                $perksChanged = true;
            }

            if ($request->has('custom_name_gradient')) {
                $allowedGradients = array_keys(Character::nameGradientCatalog());
                $chosenGradient = $validated['custom_name_gradient'] ?? 'none';
                $perks['custom_name_gradient'] = in_array($chosenGradient, $allowedGradients, true) ? $chosenGradient : 'none';
                $perksChanged = true;
            }

            if ($request->has('custom_name_glow')) {
                $allowedGlows = array_keys(Character::nameGlowCatalog());
                $chosenGlow = $validated['custom_name_glow'] ?? 'none';
                $perks['custom_name_glow'] = in_array($chosenGlow, $allowedGlows, true) ? $chosenGlow : 'none';
                $perksChanged = true;
            }
        }

        if ($character->canUseSparkleEffect() && $request->has('name_style_submitted')) {
            $perks['sparkle_enabled'] = (bool) $request->input('sparkle_enabled', false);
            $perksChanged = true;
        }

        if ($perksChanged) {
            $updates['perks'] = $perks;
        }

        if (! empty($updates)) {
            $character->update($updates);
        }

        return back()->with('success', '🎨 Je profielpersonalisatie is succesvol opgeslagen!');
    }
}
