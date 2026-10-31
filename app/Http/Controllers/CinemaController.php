<?php

namespace App\Http\Controllers;

use App\Models\Character;
use App\Models\ChatMessage;
use App\Models\CinemaMessage;
use App\Models\CinemaRoom;
use App\Models\User;
use App\Services\ChatBotService;
use App\Support\ProfileStickerCatalog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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

        $officialRooms = CinemaRoom::where('is_official', true)
            ->where('is_closed', false)
            ->with('host')
            ->latest()
            ->get();

        $patientRooms = CinemaRoom::where('is_official', false)
            ->where('is_closed', false)
            ->with('host')
            ->latest()
            ->take(30)
            ->get();

        $gifs = self::GIF_GALLERY;
        $presets = self::PRESET_VIDEOS;
        $stickers = ProfileStickerCatalog::stickers();

        return view('game.cinema.index', compact('character', 'officialRooms', 'patientRooms', 'gifs', 'presets', 'stickers'));
    }

    /**
     * Search YouTube videos for watchparties without requiring an API key.
     */
    public function searchYoutube(Request $request): JsonResponse
    {
        $query = trim((string) $request->query('q', ''));
        if (mb_strlen($query) < 2) {
            return response()->json(['results' => []]);
        }

        // If the query is already a YouTube URL or direct 11-char ID, handle directly
        $directId = CinemaRoom::extractYoutubeId($query);
        if ($directId) {
            return response()->json([
                'results' => [
                    [
                        'id' => $directId,
                        'title' => "Directe YouTube Video ({$directId})",
                        'channel' => 'Directe Link',
                        'thumbnail' => "https://img.youtube.com/vi/{$directId}/mqdefault.jpg",
                        'url' => "https://www.youtube.com/watch?v={$directId}",
                    ],
                ],
            ]);
        }

        try {
            $ch = curl_init('https://www.youtube.com/results?search_query='.urlencode($query));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36');
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 3);
            $html = curl_exec($ch);
            curl_close($ch);

            $results = [];
            if ($html && preg_match('/ytInitialData\s*=\s*({.+?});/s', $html, $matches)) {
                $data = json_decode($matches[1], true);
                $items = $data['contents']['twoColumnSearchResultsRenderer']['primaryContents']['sectionListRenderer']['contents'][0]['itemSectionRenderer']['contents'] ?? [];

                foreach ($items as $item) {
                    if (isset($item['videoRenderer'])) {
                        $v = $item['videoRenderer'];
                        $videoId = $v['videoId'] ?? null;
                        $title = $v['title']['runs'][0]['text'] ?? $v['title']['simpleText'] ?? null;
                        $channel = $v['ownerText']['runs'][0]['text'] ?? null;

                        if ($videoId && $title) {
                            $results[] = [
                                'id' => $videoId,
                                'title' => $title,
                                'channel' => $channel ?? '',
                                'thumbnail' => "https://img.youtube.com/vi/{$videoId}/mqdefault.jpg",
                                'url' => "https://www.youtube.com/watch?v={$videoId}",
                            ];
                        }

                        if (count($results) >= 6) {
                            break;
                        }
                    }
                }
            }

            return response()->json(['results' => $results]);
        } catch (\Throwable $e) {
            return response()->json(['results' => []]);
        }
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

        $baseSlug = Str::slug($validated['title']) ?: 'cinema-zaal';
        $slug = $baseSlug;
        $count = 1;
        while (CinemaRoom::where('slug', $slug)->exists()) {
            $slug = "{$baseSlug}-{$count}";
            $count++;
        }

        $isPrivate = ! empty($validated['is_private']);
        $passcode = $isPrivate ? ($validated['passcode'] ?? null) : null;
        $isOfficial = $user->isStaff() && $request->boolean('is_official');

        $room = CinemaRoom::create([
            'title' => $validated['title'],
            'slug' => $slug,
            'description' => $validated['description'] ?? 'Gezellig samen kijken op de afdeling.',
            'host_character_id' => $character->id,
            'youtube_url' => $validated['youtube_url'],
            'youtube_video_id' => $videoId,
            'video_started_at' => now(),
            'seek_offset' => 0,
            'video_queue' => [],
            'is_official' => $isOfficial,
            'is_private' => $isPrivate,
            'is_closed' => false,
            'passcode' => $passcode,
            'views_count' => 1,
        ]);

        // Post welcome message in room
        CinemaMessage::create([
            'room_id' => $room->id,
            'character_id' => $character->id,
            'message' => "🍿 Welkom in bioscoopzaal '{$room->title}'! De video is gestart. Veel kijkplezier!",
        ]);

        // If this is an Official Hoofdzaal, broadcast to all players via Directie Chat & Public Chat
        if ($isOfficial) {
            $this->broadcastOfficialRoomNotification($room, $character);
        }

        return redirect()->route('cinema.show', $room->slug)
            ->with('success', "🎬 Bioscoopzaal '{$room->title}' succesvol aangemaakt! Deel de link met medepatiënten voor jullie watchparty.");
    }

    /**
     * Broadcast Directie Chat notification to all characters when an official room is opened.
     */
    protected function broadcastOfficialRoomNotification(CinemaRoom $room, Character $creator): void
    {
        $title = "🎬 Officiële Zaal Geopend: {$room->title}";
        $message = "De Kliniek Directie heeft zojuist een officiële bioscoopzaal geopend: '{$room->title}'! Kom gezellig samen kijken, chatten en video's streamen in de zaal.";
        $actionUrl = route('cinema.show', $room->slug);

        try {
            $chatBotService = app(ChatBotService::class);
            Character::select('id')->chunkById(100, function ($characters) use ($chatBotService, $title, $message, $actionUrl) {
                foreach ($characters as $recipient) {
                    $chatBotService->sendSystemNotice($recipient, $title, $message, $actionUrl);
                }
            });
        } catch (\Throwable $e) {
            Log::warning('Failed sending Directie Chat broadcast for cinema room: '.$e->getMessage());
        }

        try {
            ChatMessage::create([
                'character_id' => $creator->id,
                'channel' => 'global',
                'message' => "🎬 **[OFFICIËLE BIOSCOOP]:** De directie heeft zojuist een officiële bioscoopzaal geopend: **{$room->title}**! Kom gezellig meekijken via: {$actionUrl}",
                'hearts_count' => 0,
                'hearts_users' => [],
            ]);
        } catch (\Throwable $e) {
            Log::warning('Failed sending public ChatMessage for official cinema room: '.$e->getMessage());
        }
    }

    /**
     * Check whether the current player is authorized to access a cinema room.
     */
    protected function canAccessRoom(Request $request, CinemaRoom $room, User $user, Character $character): bool
    {
        if (! $room->is_private || $room->host_character_id === $character->id || $user->isStaff()) {
            return true;
        }

        $providedPass = $request->input('pin', $request->query('pin', session("cinema_pin_{$room->id}")));

        return $providedPass !== null && $providedPass === $room->passcode;
    }

    /**
     * Show a cinema theater room with synchronized video, video queue & live session chat.
     */
    public function show(Request $request, CinemaRoom $room): View|RedirectResponse
    {
        if ($room->is_closed) {
            return redirect()->route('cinema.index')->with('info', "De bioscoopzaal '{$room->title}' is gesloten door de zaalhost.");
        }

        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;

        // Check private room password
        if ($room->is_private && $room->host_character_id !== $character->id && ! $user->isStaff()) {
            $providedPass = $request->query('pin', session("cinema_pin_{$room->id}"));
            if ($providedPass !== $room->passcode) {
                return redirect()->route('cinema.index')->with('error', 'Deze zaal is privé beveiligd met een toegangscode!');
            }
            session(["cinema_pin_{$room->id}" => $providedPass]);
        }

        if (! $room->video_started_at) {
            $room->video_started_at = now();
            $room->seek_offset = 0;
            $room->save();
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
        $queue = $room->getQueue();
        $initialPlaybackSeconds = $room->getCurrentPlaybackSeconds();

        $initialMessages = $messages->map(function ($m) {
            return [
                'id' => $m->id,
                'text' => $m->message,
                'gif_url' => $m->gif_url,
                'sticker_key' => $m->sticker_key,
                'character_name' => $m->character?->name ?? 'Onbekend',
                'avatar' => asset($m->character?->avatar ?? 'characters/avatar-default.png'),
                'is_staff' => (bool) ($m->character?->user?->isStaff()),
                'time' => $m->created_at?->format('H:i') ?? '',
            ];
        })->values();

        return view('game.cinema.room', compact('character', 'room', 'messages', 'initialMessages', 'gifs', 'presets', 'stickers', 'queue', 'initialPlaybackSeconds'));
    }

    /**
     * Close an active cinema room (Host or Staff only).
     */
    public function closeRoom(Request $request, CinemaRoom $room): RedirectResponse|JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;

        if ($room->host_character_id !== $character->id && ! $user->isStaff()) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Alleen de zaalhost of een stafflid kan deze zaal sluiten.'], 403);
            }

            return back()->with('error', 'Alleen de zaalhost of een stafflid kan deze zaal sluiten.');
        }

        $room->is_closed = true;
        $room->closed_at = now();
        $room->save();

        CinemaMessage::create([
            'room_id' => $room->id,
            'character_id' => $character->id,
            'message' => "⛔ Bioscoopzaal '{$room->title}' is gesloten door host {$character->name}.",
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'redirect' => route('cinema.index'),
                'message' => 'Bioscoopzaal succesvol gesloten.',
            ]);
        }

        return redirect()->route('cinema.index')->with('success', "Bioscoopzaal '{$room->title}' is succesvol gesloten.");
    }

    /**
     * Add a YouTube video to the room's watchparty queue.
     */
    public function addToQueue(Request $request, CinemaRoom $room): JsonResponse|RedirectResponse
    {
        if ($room->is_closed) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Deze zaal is gesloten.'], 422);
            }

            return back()->with('error', 'Deze zaal is gesloten.');
        }

        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;

        if (! $this->canAccessRoom($request, $room, $user, $character)) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Geen toegang tot deze privézaal.'], 403);
            }

            return redirect()->route('cinema.index')->with('error', 'Deze zaal is privé beveiligd met een toegangscode!');
        }

        $validated = $request->validate([
            'youtube_url' => ['required', 'string'],
            'title' => ['nullable', 'string', 'max:100'],
        ]);

        $videoId = CinemaRoom::extractYoutubeId($validated['youtube_url']);
        if (! $videoId) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Ongeldige YouTube video link!'], 422);
            }

            return back()->with('error', 'Ongeldige YouTube video link!');
        }

        $itemTitle = ! empty($validated['title']) ? trim($validated['title']) : "YouTube Video ({$videoId})";

        $item = [
            'id' => (string) Str::uuid(),
            'title' => Str::limit($itemTitle, 80),
            'youtube_url' => $validated['youtube_url'],
            'youtube_video_id' => $videoId,
            'thumbnail' => "https://img.youtube.com/vi/{$videoId}/mqdefault.jpg",
            'added_by_name' => $character->name,
            'added_by_avatar' => asset($character->avatar ?? 'characters/avatar-default.png'),
            'added_at' => now()->format('H:i'),
        ];

        $queue = DB::transaction(function () use ($room, $item) {
            /** @var CinemaRoom $lockedRoom */
            $lockedRoom = CinemaRoom::where('id', $room->id)->lockForUpdate()->firstOrFail();
            $currentQueue = $lockedRoom->getQueue();
            if (count($currentQueue) >= 25) {
                return null;
            }
            $currentQueue[] = $item;
            $lockedRoom->video_queue = $currentQueue;
            $lockedRoom->save();

            return $currentQueue;
        });

        if ($queue === null) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Wachtrij is vol (maximaal 25 video\'s).'], 422);
            }

            return back()->with('error', 'Wachtrij is vol (maximaal 25 video\'s).');
        }

        CinemaMessage::create([
            'room_id' => $room->id,
            'character_id' => $character->id,
            'message' => "🎶 {$character->name} heeft '{$item['title']}' toegevoegd aan de wachtrij!",
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'queue' => $queue,
                'message' => 'Video toegevoegd aan de wachtrij!',
            ]);
        }

        return back()->with('success', "'{$item['title']}' toegevoegd aan de wachtrij!");
    }

    /**
     * Play a video from the queue immediately (Host or Staff only).
     */
    public function playFromQueue(Request $request, CinemaRoom $room, string $queueId): JsonResponse|RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;

        if ($room->is_closed) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Deze zaal is gesloten.'], 422);
            }

            return back()->with('error', 'Deze zaal is gesloten.');
        }

        if ($room->host_character_id !== $character->id && ! $user->isStaff()) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Alleen de zaalhost kan video\'s starten.'], 403);
            }

            return back()->with('error', 'Alleen de zaalhost kan video\'s starten.');
        }

        $target = null;
        $remaining = [];

        DB::transaction(function () use ($room, $queueId, &$target, &$remaining) {
            /** @var CinemaRoom $lockedRoom */
            $lockedRoom = CinemaRoom::where('id', $room->id)->lockForUpdate()->firstOrFail();
            $queue = $lockedRoom->getQueue();

            foreach ($queue as $item) {
                if ($item['id'] === $queueId && $target === null) {
                    $target = $item;
                } else {
                    $remaining[] = $item;
                }
            }

            if ($target) {
                $lockedRoom->youtube_video_id = $target['youtube_video_id'];
                $lockedRoom->youtube_url = $target['youtube_url'];
                $lockedRoom->video_started_at = now();
                $lockedRoom->seek_offset = 0;
                $lockedRoom->video_queue = $remaining;
                $lockedRoom->save();
            }
        });

        if (! $target) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Video niet gevonden in wachtrij.'], 404);
            }

            return back()->with('error', 'Video niet gevonden in wachtrij.');
        }

        $room->refresh();

        CinemaMessage::create([
            'room_id' => $room->id,
            'character_id' => $character->id,
            'message' => "🎬 Zaalhost {$character->name} speelt nu af uit de wachtrij: {$target['title']}!",
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'video_id' => $room->youtube_video_id,
                'playback_seconds' => 0,
                'queue' => $remaining,
                'message' => "Nu aan het afspelen: {$target['title']}",
            ]);
        }

        return back()->with('success', "Nu aan het afspelen: {$target['title']}");
    }

    /**
     * Remove a video from the queue (Host or Staff only).
     */
    public function removeFromQueue(Request $request, CinemaRoom $room, string $queueId): JsonResponse|RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;

        if ($room->is_closed) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Deze zaal is gesloten.'], 422);
            }

            return back()->with('error', 'Deze zaal is gesloten.');
        }

        if ($room->host_character_id !== $character->id && ! $user->isStaff()) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Alleen de zaalhost kan items uit de wachtrij verwijderen.'], 403);
            }

            return back()->with('error', 'Alleen de zaalhost kan items uit de wachtrij verwijderen.');
        }

        $filtered = DB::transaction(function () use ($room, $queueId) {
            /** @var CinemaRoom $lockedRoom */
            $lockedRoom = CinemaRoom::where('id', $room->id)->lockForUpdate()->firstOrFail();
            $queue = $lockedRoom->getQueue();
            $remaining = array_values(array_filter($queue, fn ($item) => $item['id'] !== $queueId));
            $lockedRoom->video_queue = $remaining;
            $lockedRoom->save();

            return $remaining;
        });

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'queue' => $filtered,
                'message' => 'Video verwijderd uit wachtrij.',
            ]);
        }

        return back()->with('success', 'Video verwijderd uit wachtrij.');
    }

    /**
     * Send a message, reaction sticker, or GIF in the room chat.
     */
    public function sendMessage(Request $request, CinemaRoom $room): JsonResponse|RedirectResponse
    {
        if ($room->is_closed) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Deze zaal is gesloten.'], 422);
            }

            return back()->with('error', 'Deze zaal is gesloten.');
        }

        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;

        if (! $this->canAccessRoom($request, $room, $user, $character)) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Geen toegang tot deze privézaal.'], 403);
            }

            return redirect()->route('cinema.index')->with('error', 'Deze zaal is privé beveiligd met een toegangscode!');
        }

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
                    'avatar' => asset($character->avatar ?? 'characters/avatar-default.png'),
                    'is_staff' => (bool) $user->isStaff(),
                    'time' => $msg->created_at->format('H:i'),
                ],
            ]);
        }

        return back();
    }

    /**
     * Poll latest messages and synchronized state in room (JSON endpoint).
     */
    public function getMessages(Request $request, CinemaRoom $room): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;

        if (! $this->canAccessRoom($request, $room, $user, $character)) {
            return response()->json(['success' => false, 'message' => 'Geen toegang tot deze privézaal.'], 403);
        }

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
            'is_closed' => (bool) $room->is_closed,
            'video_id' => $room->youtube_video_id,
            'playback_seconds' => $room->getCurrentPlaybackSeconds(),
            'video_queue' => $room->getQueue(),
            'views' => $room->views_count,
            'messages' => $messages,
        ]);
    }

    /**
     * Synchronize video playback position from Host to room.
     */
    public function syncPlayback(Request $request, CinemaRoom $room): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;

        if ($room->is_closed) {
            return response()->json(['success' => false, 'message' => 'Deze zaal is gesloten.'], 422);
        }

        if ($room->host_character_id !== $character->id && ! $user->isStaff()) {
            return response()->json(['success' => false, 'message' => 'Alleen de zaalhost kan synchroniseren.'], 403);
        }

        $currentSeconds = max(0, (int) $request->input('current_seconds', 0));
        $room->video_started_at = now();
        $room->seek_offset = $currentSeconds;
        $room->save();

        return response()->json([
            'success' => true,
            'playback_seconds' => $room->getCurrentPlaybackSeconds(),
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

        if ($room->is_closed) {
            return back()->with('error', 'Deze zaal is gesloten.');
        }

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
        $room->video_started_at = now();
        $room->seek_offset = 0;
        $room->save();

        CinemaMessage::create([
            'room_id' => $room->id,
            'character_id' => $character->id,
            'message' => "🎬 Zaalhost {$character->name} heeft een nieuwe video opgezet!",
        ]);

        return back()->with('success', 'Video succesvol gewijzigd! Iedereen in de zaal kijkt nu mee.');
    }
}
