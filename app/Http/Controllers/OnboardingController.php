<?php

namespace App\Http\Controllers;

use App\Models\Character;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class OnboardingController extends Controller
{
    /**
     * Show the onboarding wizard page.
     */
    public function index(): View|RedirectResponse
    {
        $user = Auth::user();
        if (! $user) {
            return redirect()->route('login');
        }

        $character = $user->character;

        // If already onboarded, send straight to dashboard
        if ($user->is_onboarded && $character?->is_onboarded) {
            return redirect()->route('dashboard');
        }

        // Available starter avatars
        $avatars = [
            ['path' => 'assets/characters/avatar_male_1.jpg', 'label' => 'Mocro Maffia'],
            ['path' => 'assets/characters/avatar_male_2.jpg', 'label' => 'Straathosselaar'],
            ['path' => 'assets/characters/avatar_male_3.jpg', 'label' => 'De Bankkraker'],
            ['path' => 'assets/characters/avatar_male_4.jpg', 'label' => 'De Veteraan'],
            ['path' => 'assets/characters/avatar_female_1.jpg', 'label' => 'Bendeleidster'],
            ['path' => 'assets/characters/avatar_female_2.jpg', 'label' => 'De Smokkelaarster'],
            ['path' => 'assets/characters/avatar_female_3.jpg', 'label' => 'De Witwasser'],
            ['path' => 'assets/characters/henkie.jpg', 'label' => 'Henkie de Hooligan'],
            ['path' => 'assets/characters/barry.jpg', 'label' => 'Bajes Barry'],
            ['path' => 'assets/characters/ricardo.jpg', 'label' => 'Ricardo de Enforcer'],
        ];

        // Available profile accent colors
        $colors = [
            ['id' => 'purple', 'name' => 'TBS Paars', 'hex' => '#7c3aed', 'bg' => 'bg-purple-600', 'border' => 'border-purple-500'],
            ['id' => 'rose', 'name' => 'Bloedrood', 'hex' => '#e11d48', 'bg' => 'bg-rose-600', 'border' => 'border-rose-500'],
            ['id' => 'emerald', 'name' => 'Smaragdgroen', 'hex' => '#059669', 'bg' => 'bg-emerald-600', 'border' => 'border-emerald-500'],
            ['id' => 'amber', 'name' => 'Goudgeel', 'hex' => '#d97706', 'bg' => 'bg-amber-600', 'border' => 'border-amber-500'],
            ['id' => 'blue', 'name' => 'Nachtblauw', 'hex' => '#2563eb', 'bg' => 'bg-blue-600', 'border' => 'border-blue-500'],
            ['id' => 'indigo', 'name' => 'Diep Indigo', 'hex' => '#4f46e5', 'bg' => 'bg-indigo-600', 'border' => 'border-indigo-500'],
            ['id' => 'dark', 'name' => 'Stealth Zwart', 'hex' => '#18181b', 'bg' => 'bg-zinc-800', 'border' => 'border-zinc-700'],
        ];

        // Suggest clean username
        $suggestedUsername = $character?->username ?: strtolower((string) preg_replace('/[^a-zA-Z0-9_-]/', '', (string) ($user->name ?? 'inmate')));
        if (strlen($suggestedUsername) < 3) {
            $suggestedUsername = 'inmate_'.rand(100, 999);
        }

        $suggestedDisplayName = $character?->display_name ?: ($character?->name ?: ($user->name ?: 'Nieuwe Patiënt'));

        return view('auth.onboarding', compact(
            'user',
            'character',
            'avatars',
            'colors',
            'suggestedUsername',
            'suggestedDisplayName'
        ));
    }

    /**
     * Real-time AJAX username availability check.
     */
    public function checkUsername(Request $request): JsonResponse
    {
        $username = strtolower(trim((string) $request->input('username', '')));

        if (! preg_match('/^[a-zA-Z0-9_\-]{3,20}$/', $username)) {
            return response()->json([
                'available' => false,
                'message' => 'Gebruikersnaam moet tussen 3 en 20 tekens zijn en mag alleen letters, cijfers, underscores (_) of streepjes (-) bevatten.',
            ]);
        }

        $currentCharId = Auth::user()?->character?->id;
        $exists = Character::where('username', $username)
            ->when($currentCharId, fn ($q) => $q->where('id', '!=', $currentCharId))
            ->exists();

        if ($exists) {
            return response()->json([
                'available' => false,
                'message' => "Gebruikersnaam '{$username}' is al bezet door een andere patiënt. Kies een andere naam.",
            ]);
        }

        return response()->json([
            'available' => true,
            'message' => "Gebruikersnaam '{$username}' is beschikbaar!",
        ]);
    }

    /**
     * Upload cropped custom avatar (base64 image from crop modal).
     */
    public function uploadAvatar(Request $request): JsonResponse
    {
        $user = Auth::user();
        if (! $user) {
            return response()->json(['success' => false, 'message' => 'Niet ingelogd.'], 401);
        }

        $request->validate([
            'image' => ['required', 'string'], // base64 data url
        ]);

        $imageData = $request->input('image');
        if (! preg_match('/^data:image\/(png|jpeg|jpg|webp);base64,/', $imageData, $matches)) {
            return response()->json(['success' => false, 'message' => 'Ongeldig afbeeldingsformaat. Alleen JPG, PNG of WebP zijn toegestaan.'], 422);
        }

        $data = substr($imageData, strpos($imageData, ',') + 1);
        $data = base64_decode($data);
        if ($data === false) {
            return response()->json(['success' => false, 'message' => 'Afbeelding kon niet worden gedecodeerd.'], 422);
        }

        if (strlen($data) > 5 * 1024 * 1024) {
            return response()->json(['success' => false, 'message' => 'Afbeelding is te groot (maximaal 5MB).'], 422);
        }

        $imageInfo = @getimagesizefromstring($data);
        if ($imageInfo === false || ! in_array($imageInfo['mime'], ['image/jpeg', 'image/png', 'image/webp'], true)) {
            return response()->json(['success' => false, 'message' => 'Ongeldige afbeeldingsdata gedetecteerd.'], 422);
        }

        $ext = $matches[1] === 'jpeg' ? 'jpg' : $matches[1];
        $filename = 'avatar_user_'.$user->id.'_'.time().'_'.Str::random(6).'.'.$ext;
        $uploadDir = public_path('uploads/avatars');

        if (! is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $filePath = $uploadDir.'/'.$filename;
        file_put_contents($filePath, $data);

        $relativePath = 'uploads/avatars/'.$filename;

        return response()->json([
            'success' => true,
            'url' => asset($relativePath),
            'relative_path' => $relativePath,
        ]);
    }

    /**
     * Save completed onboarding data.
     */
    public function complete(Request $request): RedirectResponse
    {
        $user = Auth::user();
        if (! $user) {
            return redirect()->route('login');
        }

        $character = $user->character;

        // A finished dossier must not be submitted again: it would repeat the specialty cash bonus and allow free renames.
        if ($user->is_onboarded && $character?->is_onboarded) {
            return redirect()->route('dashboard')->with('info', 'Je patiëntendossier is al compleet.');
        }

        if (! $character) {
            $character = Character::create([
                'user_id' => $user->id,
                'name' => 'inmate_'.Str::random(6),
                'tag' => 'TBS',
                'title' => 'Nieuwe Patiënt',
                'specialty' => 'Allrounder',
                'cash' => 500,
                'bank' => 2500,
            ]);
        }

        // Strict validation: username must be alphanumeric, birthdate strictly 16+
        $minDate = now()->subYears(100)->format('Y-m-d');
        $maxDate = now()->subYears(16)->format('Y-m-d');

        $validated = $request->validate([
            'username' => [
                'required',
                'string',
                'min:3',
                'max:20',
                'regex:/^[a-zA-Z0-9_\-]+$/',
                'unique:characters,username,'.$character->id,
            ],
            'display_name' => ['required', 'string', 'min:2', 'max:30', 'unique:characters,name,'.$character->id],
            'birthdate' => [
                'required',
                'date',
                'after_or_equal:'.$minDate,
                'before_or_equal:'.$maxDate,
            ],
            'avatar' => ['required', 'string', 'max:255'],
            'profile_color' => ['required', 'string', 'in:purple,rose,emerald,amber,blue,indigo,dark'],
            'bio' => ['nullable', 'string', 'max:300'],
            'specialty' => ['required', 'string', 'in:hossel,vecht,brein'],
        ], [
            'username.regex' => 'De gebruikersnaam mag GEEN spaties of speciale tekens bevatten (alleen letters a-z, cijfers 0-9, underscores _ en streepjes -).',
            'birthdate.before_or_equal' => 'Je moet minimaal 16 jaar oud zijn om te kunnen spelen in de TBS Kliniek (16+ regelgeving).',
        ]);

        $cleanUsername = strtolower(trim($validated['username']));
        $displayName = trim($validated['display_name']);

        $avatarInput = trim($validated['avatar']);
        $allowedPresetAvatars = [
            'assets/characters/avatar_male_1.jpg',
            'assets/characters/avatar_male_2.jpg',
            'assets/characters/avatar_male_3.jpg',
            'assets/characters/avatar_male_4.jpg',
            'assets/characters/avatar_female_1.jpg',
            'assets/characters/avatar_female_2.jpg',
            'assets/characters/avatar_female_3.jpg',
            'assets/characters/henkie.jpg',
            'assets/characters/barry.jpg',
            'assets/characters/ricardo.jpg',
        ];
        for ($i = 1; $i <= 20; $i++) {
            $allowedPresetAvatars[] = "assets/characters/avatar_{$i}.jpg";
        }
        $isOwnUploadedAvatar = (bool) preg_match('/^uploads\/avatars\/avatar_user_'.$user->id.'_\d+_[a-zA-Z0-9]+\.(jpg|png|webp)$/', $avatarInput)
            && file_exists(public_path($avatarInput));

        if (! in_array($avatarInput, $allowedPresetAvatars, true) && ! $isOwnUploadedAvatar) {
            return back()->withErrors(['avatar' => 'Kies een geldige profielfoto.'])->withInput();
        }

        // Update character
        $character->username = $cleanUsername;
        $character->display_name = $displayName;
        $character->name = $displayName; // maintain compatibility
        $character->birthdate = $validated['birthdate'];
        $character->profile_color = $validated['profile_color'];
        $character->bio = ($validated['bio'] ?? null) ?: 'Nieuwe patiënt binnengebracht op afdeling TBS.';
        $character->avatar = $avatarInput;
        $character->is_onboarded = true;

        // Apply starting specialty bonus
        if ($validated['specialty'] === 'hossel') {
            $character->charisma = max($character->charisma, 15);
            $character->cash += 500;
            $character->title = 'Slimme Hosselaar';
        } elseif ($validated['specialty'] === 'vecht') {
            $character->strength = max($character->strength, 15);
            $character->defense = max($character->defense, 12);
            $character->title = 'Krachtige Vechter';
        } elseif ($validated['specialty'] === 'brein') {
            $character->intelligence = max($character->intelligence, 15);
            $character->discipline = max($character->discipline, 12);
            $character->title = 'Meesterbrein';
        }

        $character->save();

        // Update user
        $user->name = $cleanUsername;
        $user->birthdate = $validated['birthdate'];
        $user->is_onboarded = true;
        $user->save();

        return redirect()->route('dashboard')->with('success', "Welkom in de TBS Kliniek, {$displayName} (@{$cleanUsername})! Je patiëntendossier is compleet.");
    }
}
