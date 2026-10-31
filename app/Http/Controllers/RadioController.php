<?php

namespace App\Http\Controllers;

use App\Models\Character;
use App\Models\RadioShoutout;
use App\Models\RadioShoutoutLike;
use App\Models\User;
use App\Services\CooldownService;
use App\Services\EconomyService;
use App\Services\LevelService;
use App\Services\PrisonService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RadioController extends Controller
{
    public const TRACKS = [
        'André Hazes - Bloed, Zweet en Tranen',
        'Boef - Habiba',
        'Django Wagner - Kali',
        'DJ Paul Elstak - Rainbow in the Sky',
        'Frans Bauer - Heb Je Even Voor Mij',
        'Ronnie Flex & Frenna - Energie',
        'Manke Nelis - Kleine Jodeljongen',
        'Hef - Kofferbak',
        'Willy Alberti - Niemand Laat Zijn Eigen Kind Alleen',
        'Charly Lownoise & Mental Theo - Wonderful Days',
    ];

    public function __construct(
        protected EconomyService $economy,
        protected LevelService $level,
        protected CooldownService $cooldownService,
        protected PrisonService $prisonService,
    ) {}

    public function index(): View
    {
        /** @var User $user */
        $user = Auth::user();
        /** @var Character $character */
        $character = $user->character;

        $shoutouts = RadioShoutout::with(['character', 'likes'])
            ->latest()
            ->paginate(20);

        $shoutoutCooldown = $this->cooldownService->getRemainingSeconds($character, 'radio_shoutout');

        // Bepaal de top shoutout van vandaag (meeste likes)
        $topShoutout = RadioShoutout::where('created_at', '>=', now()->startOfDay())
            ->orderByDesc('likes_count')
            ->first();

        $tracks = self::TRACKS;

        return view('game.radio.index', compact(
            'character',
            'shoutouts',
            'shoutoutCooldown',
            'topShoutout',
            'tracks'
        ));
    }

    public function shoutout(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        /** @var Character $character */
        $character = $user->character;

        if ($character->isImprisoned()) {
            return redirect()->route('prison')->with('error', 'In de isoleercel heb je geen toegang tot de radiozender van de afdeling!');
        }

        if ($character->isComatose()) {
            return back()->with('error', 'Je verkeert in Code Blauw (Hartstilstand)! Dien eerst een '.Character::REVIVE_SERUM_NAME.' toe.');
        }

        if ($this->cooldownService->isOnCooldown($character, 'radio_shoutout')) {
            $remaining = $this->cooldownService->getRemainingSeconds($character, 'radio_shoutout').' seconden';

            return back()->with('error', "De microfoon van de radiostudio is bezet door een andere patiënt. Wacht nog {$remaining}.");
        }

        $validated = $request->validate([
            'message' => ['required', 'string', 'min:3', 'max:200'],
            'track_title' => ['required', 'string', 'max:100'],
            'is_anonymous' => ['nullable', 'boolean'],
        ]);

        $cost = 150;
        if (! $this->economy->deductCash($character, $cost)) {
            return back()->with('error', 'Een shoutout uitzenden kost €'.number_format($cost, 0, ',', '.').' kleingeld voor de zendkosten!');
        }

        $this->cooldownService->setCooldown($character, 'radio_shoutout');

        $gangName = $character->gang ? $character->gang->name : null;

        RadioShoutout::create([
            'character_id' => $character->id,
            'character_name' => $character->name,
            'gang_name' => $gangName,
            'is_anonymous' => (bool) ($validated['is_anonymous'] ?? false),
            'track_title' => $validated['track_title'],
            'message' => strip_tags(trim($validated['message'])),
            'likes_count' => 0,
        ]);

        $this->level->addXp($character, 15);

        return back()->with('success', "📻 LIVE ON AIR! Jouw plaatje '{$validated['track_title']}' en shoutout knallen nu over de afdelingsspeakers (+15 XP)!");
    }

    public function like(RadioShoutout $shoutout): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        /** @var Character $character */
        $character = $user->character;

        if ($character->isComatose()) {
            return back()->with('error', 'Je verkeert in Code Blauw (Hartstilstand)! Dien eerst een '.Character::REVIVE_SERUM_NAME.' toe.');
        }

        if ($shoutout->character_id === $character->id) {
            return back()->with('error', 'Je kunt niet je eigen radioshoutout liken!');
        }

        $like = RadioShoutoutLike::firstOrCreate([
            'radio_shoutout_id' => $shoutout->id,
            'character_id' => $character->id,
        ]);

        if (! $like->wasRecentlyCreated) {
            return back()->with('error', 'Je hebt dit radiosterretje al respect gegeven!');
        }

        $shoutout->increment('likes_count');

        return back()->with('success', '📻 Respect gegeven aan dit radioterugblikje!');
    }
}
