<?php

namespace App\Http\Controllers;

use App\Models\CinemaMessage;
use App\Models\CinemaRoom;
use App\Models\User;
use App\Support\ProfileStickerCatalog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CinemaController extends Controller
{
    /**
     * Curated GIF reactions gallery for instant fun watchparty interaction.
     */
    public const GIF_GALLERY = [
        ['title' => 'Popcorn Eten', 'url' => 'https://media.giphy.com/media/gl0mkIZOW6Nwc/giphy.gif'],
        ['title' => 'Popcorn Hype', 'url' => 'https://media.giphy.com/media/t3sZxY5zS5B0z5zMIz/giphy.gif'],
        ['title' => 'Lachen Gieren', 'url' => 'https://media.giphy.com/media/10JhviFuU2gWD6/giphy.gif'],
        ['title' => 'Stuk Gaan', 'url' => 'https://media.giphy.com/media/26n6Gx9moCgs1DnFe/giphy.gif'],
        ['title' => 'Klap & Respect', 'url' => 'https://media.giphy.com/media/ytTYwIlbD1FBu/giphy.gif'],
        ['title' => 'Mindblown', 'url' => 'https://media.giphy.com/media/26ufdipQqU2lhNA4g/giphy.gif'],
        ['title' => 'Geld Strooien', 'url' => 'https://media.giphy.com/media/3o6gDWzmAzrpi5DQU8/giphy.gif'],
        ['title' => 'Politie Alarm', 'url' => 'https://media.giphy.com/media/RYncCB9oza0j6/giphy.gif'],
        ['title' => 'Facepalm', 'url' => 'https://media.giphy.com/media/WrNfErAnGV7lm/giphy.gif'],
        ['title' => 'Gevaarlijk / Gangster', 'url' => 'https://media.giphy.com/media/l41lI4bYmcsPJX9Go/giphy.gif'],
        ['title' => 'Vreugdedans', 'url' => 'https://media.giphy.com/media/artj92V8o75VPL7AeQ/giphy.gif'],
        ['title' => 'Spanning / Oei', 'url' => 'https://media.giphy.com/media/3o7TKvxnUVN35OT55K/giphy.gif'],
    ];

    /**
     * Curated popular playlist presets.
     */
    public const PRESET_VIDEOS = [
        ['title' => 'TBS Kliniek Promo & Trailers', 'id' => 'dQw4w9WgXcQ', 'category' => 'Trailers'],
        ['title' => 'Grootste Bankovervallen Documentaire', 'id' => '1la4bUKxMJE', 'category' => 'Misdaad Docu'],
        ['title' => 'Lofi Prison Beats to Chill/Escape to', 'id' => 'jfKfPfyJRdk', 'category' => 'Muziek Stream'],
        ['title' => 'Spannende Misdaad & Gevangenis Dossiers', 'id' => '1la4bUKxMJE', 'category' => 'Dossiers'],
    ];

    /**
     * Display the Cinema Lobby with active watchparties and official rooms.
     */
    public function index(Request $request): View
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;

        $officialRooms = CinemaRoom::where('is_official', true)->with('host')->latest()->get();
        $patientRooms = CinemaRoom::where('is_official', false)->with('host')->latest()->take(20)->get();

        $gifs = self::GIF_GALLERY;
        $presets = self::PRESET_VIDEOS;
        $stickers = ProfileStickerCatalog::stickers();

        return view('game.cinema.index', compact('character', 'officialRooms', 'patientRooms', 'gifs', 'presets', 'stickers'));
    }

    /**
     * Create a new Cinema watchparty room.
     */
    public function createRoom(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;

        $validated = $request->validate([
            'title' => ['required', 'string', 'min:3', 'max:80'],
            'description' => ['nullable', 'string', 'max:250'],
            'youtube_url' => ['required', 'string'],
            'is_private' => ['nullable', 'boolean'],
            'passcode' => ['nullable', 'string', 'max:20'],
        ]);

        $videoId = CinemaRoom::extractYoutubeId($validated['youtube_url']);
        if (! $videoId) {
            return back()->withInput()->with('error', 'Ongeldige YouTube URL of video ID! Plak een geldige YouTube link (bijv. https://www.youtube.com/watch?v=... of https://youtu.be/...).');
        }

        $baseSlug = Str::slug($validated['title']);
        $slug = $baseSlug ?: 'cinema-zaal-'.rand(100, 999);
        $count = 1;
        while (CinemaRoom::where('slug', $slug)->exists()) {
            $slug = "{$baseSlug}-{$count}";
            $count++;
        }

        $isPrivate = ! empty($validated['is_private']);
        $passcode = $isPrivate ? ($validated['passcode'] ?? null) : null;

        $room = CinemaRoom::create([
            'title' => $validated['title'],
            'slug' => $slug,
            'description' => $validated['description'] ?? 'Gezellig samen kijken op de afdeling.',
            'host_character_id' => $character->id,
            'youtube_url' => $validated['youtube_url'],
            'youtube_video_id' => $videoId,
            'is_official' => $user->isStaff() && $request->boolean('is_official'),
            'is_private' => $isPrivate,
            'passcode' => $passcode,
            'views_count' => 1,
        ]);

        // Post welcome message in room
        CinemaMessage::create([
            'room_id' => $room->id,
            'character_id' => $character->id,
            'message' => "🍿 Welkom in bioscoopzaal '{$room->title}'! De video is gestart. Veel kijkplezier!",
        ]);

        return redirect()->route('cinema.show', $room->slug)
            ->with('success', "🎬 Bioscoopzaal '{$room->title}' succesvol aangemaakt! Deel de link met medepatiënten of in Discord voor jullie watchparty.");
    }

    /**
     * Show a cinema theater room with synchronized video & live session chat.
     */
    public function show(Request $request, CinemaRoom $room): View|RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;

        // Check private room password
        if ($room->is_private && $room->host_character_id !== $character->id && ! $user->isStaff()) {
            $providedPass = $request->query('pin', session("cinema_pin_{$room->id}"));
            if ($providedPass !== $room->passcode) {
                return redirect()->route('cinema.index')->with('error', 'Deze zaal is privé beveiligd met een toegangscode!');
            }
        }

        $room->increment('views_count');

        $messages = CinemaMessage::where('room_id', $room->id)
            ->with('character')
            ->latest()
            ->take(60)
            ->get()
            ->reverse()
            ->values();

        $gifs = self::GIF_GALLERY;
        $presets = self::PRESET_VIDEOS;
        $stickers = ProfileStickerCatalog::stickers();

        $initialMessages = $messages->map(function ($m) {
            return [
                'id' => $m->id,
                'text' => $m->message,
                'gif_url' => $m->gif_url,
                'character_name' => $m->character?->name ?? 'Onbekend',
                'avatar' => asset($m->character?->avatar ?? 'characters/avatar-default.png'),
                'is_staff' => (bool) ($m->character?->user?->isStaff()),
                'time' => $m->created_at?->format('H:i') ?? '',
            ];
        })->values();

        return view('game.cinema.room', compact('character', 'room', 'messages', 'initialMessages', 'gifs', 'presets', 'stickers'));
    }

    /**
     * Send a message, reaction sticker, or GIF in the room chat.
     */
    public function sendMessage(Request $request, CinemaRoom $room): JsonResponse|RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;

        $validated = $request->validate([
            'message' => ['nullable', 'string', 'max:500'],
            'gif_url' => ['nullable', 'url', 'max:500'],
            'sticker_key' => ['nullable', 'string', 'max:64'],
        ]);

        if (empty($validated['message']) && empty($validated['gif_url']) && empty($validated['sticker_key'])) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Typ een bericht, selecteer een sticker of kies een GIF reactie!'], 422);
            }

            return back()->with('error', 'Bericht mag niet leeg zijn.');
        }

        $msg = CinemaMessage::create([
            'room_id' => $room->id,
            'character_id' => $character->id,
            'message' => $validated['message'] ?? null,
            'gif_url' => $validated['gif_url'] ?? null,
            'sticker_key' => $validated['sticker_key'] ?? null,
        ]);

        $msg->load('character');

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => [
                    'id' => $msg->id,
                    'text' => $msg->message,
                    'gif_url' => $msg->gif_url,
                    'sticker_key' => $msg->sticker_key,
                    'character_name' => $character->name,
                    'avatar' => asset($character->avatar),
                    'is_staff' => (bool) $user->isStaff(),
                    'time' => $msg->created_at->format('H:i'),
                ],
            ]);
        }

        return back();
    }

    /**
     * Poll latest messages in room (JSON endpoint).
     */
    public function getMessages(Request $request, CinemaRoom $room): JsonResponse
    {
        $afterId = (int) $request->query('after_id', 0);

        $messages = CinemaMessage::where('room_id', $room->id)
            ->where('id', '>', $afterId)
            ->with('character')
            ->oldest()
            ->take(50)
            ->get()
            ->map(function ($m) {
                return [
                    'id' => $m->id,
                    'text' => $m->message,
                    'gif_url' => $m->gif_url,
                    'sticker_key' => $m->sticker_key,
                    'character_name' => $m->character?->name ?? 'Onbekend',
                    'avatar' => asset($m->character?->avatar ?? 'characters/avatar-default.png'),
                    'is_staff' => (bool) ($m->character?->user?->isStaff()),
                    'time' => $m->created_at->format('H:i'),
                ];
            });

        return response()->json([
            'success' => true,
            'video_id' => $room->youtube_video_id,
            'views' => $room->views_count,
            'messages' => $messages,
        ]);
    }

    /**
     * Update room YouTube video (Host or Staff only).
     */
    public function updateVideo(Request $request, CinemaRoom $room): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;

        if ($room->host_character_id !== $character->id && ! $user->isStaff()) {
            return back()->with('error', 'Alleen de zaalhost kan de video wijzigen!');
        }

        $validated = $request->validate([
            'youtube_url' => ['required', 'string'],
        ]);

        $videoId = CinemaRoom::extractYoutubeId($validated['youtube_url']);
        if (! $videoId) {
            return back()->with('error', 'Ongeldige YouTube video link.');
        }

        $room->youtube_url = $validated['youtube_url'];
        $room->youtube_video_id = $videoId;
        $room->save();

        CinemaMessage::create([
            'room_id' => $room->id,
            'character_id' => $character->id,
            'message' => "🎬 Zaalhost {$character->name} heeft een nieuwe video opgezet!",
        ]);

        return back()->with('success', 'Video succesvol gewijzigd! Iedereen in de zaal kijkt nu mee.');
    }
}
