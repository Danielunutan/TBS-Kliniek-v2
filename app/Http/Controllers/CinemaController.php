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
use Illuminate\Support\Facades\Cache;
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
    public function index(Request $request): View|RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;

        if (! $character) {
            return redirect()->route('dashboard')->with('error', 'Geen personage gevonden.');
        }

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

        if (! $character) {
            return redirect()->route('dashboard')->with('error', 'Geen personage gevonden.');
        }

        if ($character->isImprisoned()) {
            return redirect()->route('prison')->with('error', 'Vanuit de isoleercel kun je geen bioscoopzaal openen!');
        }

        if ($character->isComatose()) {
            return back()->with('error', 'Je verkeert in Code Blauw (Hartstilstand)! Dien eerst een '.Character::REVIVE_SERUM_NAME.' toe.');
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'min:3', 'max:80'],
            'description' => ['nullable', 'string', 'max:250'],
            'youtube_url' => ['required', 'string'],
            'is_private' => ['nullable', 'boolean'],
            'passcode' => ['nullable', 'required_if:is_private,1', 'string', 'min:1', 'max:20'],
        ]);

        $videoId = CinemaRoom::extractYoutubeId($validated['youtube_url']);
        if (! $videoId) {
            return back()->withInput()->with('error', 'Ongeldige YouTube URL of video ID! Plak een geldige YouTube link (bijv. https://www.youtube.com/watch?v=... of https://youtu.be/...).');
        }

        $cleanTitle = strip_tags(trim($validated['title']));
        $cleanDesc = ! empty($validated['description']) ? strip_tags(trim($validated['description'])) : 'Gezellig samen kijken op de afdeling.';

        $baseSlug = Str::slug($cleanTitle) ?: 'cinema-zaal';
        $slug = $baseSlug;
        $count = 1;
        while (CinemaRoom::where('slug', $slug)->exists()) {
            $slug = "{$baseSlug}-{$count}";
            $count++;
        }

        $isPrivate = ! empty($validated['is_private']);
        $passcode = $isPrivate ? ($validated['passcode'] ?? null) : null;
        $isOfficial = $user->isAdmin() && $request->boolean('is_official');

        $room = CinemaRoom::create([
            'title' => $cleanTitle,
            'slug' => $slug,
            'description' => $cleanDesc,
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
        if (! $room->is_private || (int) $room->host_character_id === (int) $character->id || $user->isStaff() || (method_exists($user, 'canModerate') && $user->canModerate())) {
            return true;
        }

        $providedPass = $request->input('pin', $request->query('pin', session("cinema_pin_{$room->id}")));

        return ! empty($room->passcode) && $providedPass !== null && (string) $providedPass === (string) $room->passcode;
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

        if (! $character) {
            return redirect()->route('dashboard')->with('error', 'Geen personage gevonden.');
        }

        // Check private room password
        if ($room->is_private && (int) $room->host_character_id !== (int) $character->id && ! $user->isStaff()) {
            $providedPass = (string) $request->query('pin', session("cinema_pin_{$room->id}"));
            if (empty($room->passcode) || $providedPass !== (string) $room->passcode) {
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
            ->with(['character.user'])
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

        $canDeleteAny = (bool) ($user->isStaff() || (method_exists($user, 'canModerate') && $user->canModerate()) || ((int) $room->host_character_id === (int) $character->id));

        $initialMessages = $messages->map(function ($m) use ($character, $canDeleteAny) {
            $isAuthor = $character && ((int) $m->character_id === (int) $character->id);

            return [
                'id' => (int) $m->id,
                'character_id' => (int) $m->character_id,
                'text' => $m->message,
                'gif_url' => $m->gif_url,
                'sticker_key' => $m->sticker_key,
                'character_name' => $m->character?->name ?? 'Onbekend',
                'avatar' => asset($m->character?->avatar ?? 'characters/avatar-default.png'),
                'is_staff' => (bool) ($m->character?->user?->isStaff()),
                'time' => $m->created_at?->format('H:i') ?? '',
                'can_delete' => (bool) ($isAuthor || $canDeleteAny),
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

        if (! $character) {
            return redirect()->route('dashboard')->with('error', 'Geen personage gevonden.');
        }

        if ($character->isComatose()) {
            $msg = 'Je verkeert in Code Blauw (Hartstilstand)! Dien eerst een '.Character::REVIVE_SERUM_NAME.' toe.';

            return $request->wantsJson()
                ? response()->json(['success' => false, 'message' => $msg], 422)
                : back()->with('error', $msg);
        }

        if ((int) $room->host_character_id !== (int) $character->id && ! $user->isStaff()) {
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

        if (! $character) {
            return redirect()->route('dashboard')->with('error', 'Geen personage gevonden.');
        }

        if ($character->isImprisoned()) {
            $msg = 'Vanuit de isoleercel kun je geen video\'s toevoegen aan de wachtrij!';

            return $request->wantsJson()
                ? response()->json(['success' => false, 'message' => $msg], 422)
                : back()->with('error', $msg);
        }

        if ($character->isComatose()) {
            $msg = 'Je verkeert in Code Blauw (Hartstilstand)! Dien eerst een '.Character::REVIVE_SERUM_NAME.' toe.';

            return $request->wantsJson()
                ? response()->json(['success' => false, 'message' => $msg], 422)
                : back()->with('error', $msg);
        }

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

        $itemTitle = ! empty($validated['title']) ? strip_tags(trim($validated['title'])) : "YouTube Video ({$videoId})";

        $item = [
            'id' => (string) Str::uuid(),
            'title' => Str::limit($itemTitle, 80),
            'youtube_url' => $validated['youtube_url'],
            'youtube_video_id' => $videoId,
            'thumbnail' => "https://img.youtube.com/vi/{$videoId}/mqdefault.jpg",
            'added_by_name' => $character->name,
            'added_by_character_id' => (int) $character->id,
            'added_by_avatar' => asset($character->avatar ?? 'characters/avatar-default.png'),
            'added_at' => now()->format('H:i'),
        ];

        $queue = DB::transaction(function () use ($room, $item) {
            /** @var CinemaRoom $lockedRoom */
            $lockedRoom = CinemaRoom::where('id', $room->id)->lockForUpdate()->firstOrFail();
            if ($lockedRoom->is_closed) {
                return false;
            }
            $currentQueue = $lockedRoom->getQueue();
            if (count($currentQueue) >= 25) {
                return null;
            }
            $currentQueue[] = $item;
            $lockedRoom->video_queue = $currentQueue;
            $lockedRoom->save();

            return $currentQueue;
        });

        if ($queue === false) {
            $msg = 'Deze zaal is gesloten.';

            return $request->wantsJson()
                ? response()->json(['success' => false, 'message' => $msg], 422)
                : back()->with('error', $msg);
        }

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

        if (! $character) {
            return redirect()->route('dashboard')->with('error', 'Geen personage gevonden.');
        }

        if ($character->isImprisoned()) {
            $msg = 'Vanuit de isoleercel kun je geen video\'s starten!';

            return $request->wantsJson()
                ? response()->json(['success' => false, 'message' => $msg], 422)
                : back()->with('error', $msg);
        }

        if ($character->isComatose()) {
            $msg = 'Je verkeert in Code Blauw (Hartstilstand)! Dien eerst een '.Character::REVIVE_SERUM_NAME.' toe.';

            return $request->wantsJson()
                ? response()->json(['success' => false, 'message' => $msg], 422)
                : back()->with('error', $msg);
        }

        if ($room->is_closed) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Deze zaal is gesloten.'], 422);
            }

            return back()->with('error', 'Deze zaal is gesloten.');
        }

        if ((int) $room->host_character_id !== (int) $character->id && ! $user->isStaff()) {
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
            if ($lockedRoom->is_closed) {
                return;
            }
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

        if (! $character) {
            return redirect()->route('dashboard')->with('error', 'Geen personage gevonden.');
        }

        if ($character->isImprisoned()) {
            $msg = 'Vanuit de isoleercel kun je geen items uit de wachtrij verwijderen!';

            return $request->wantsJson()
                ? response()->json(['success' => false, 'message' => $msg], 422)
                : back()->with('error', $msg);
        }

        if ($character->isComatose()) {
            $msg = 'Je verkeert in Code Blauw (Hartstilstand)! Dien eerst een '.Character::REVIVE_SERUM_NAME.' toe.';

            return $request->wantsJson()
                ? response()->json(['success' => false, 'message' => $msg], 422)
                : back()->with('error', $msg);
        }

        if ($room->is_closed) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Deze zaal is gesloten.'], 422);
            }

            return back()->with('error', 'Deze zaal is gesloten.');
        }

        $result = DB::transaction(function () use ($room, $queueId, $character, $user) {
            /** @var CinemaRoom $lockedRoom */
            $lockedRoom = CinemaRoom::where('id', $room->id)->lockForUpdate()->firstOrFail();
            $queue = $lockedRoom->getQueue();

            $targetItem = null;
            foreach ($queue as $item) {
                if ($item['id'] === $queueId) {
                    $targetItem = $item;
                    break;
                }
            }

            if (! $targetItem) {
                return ['success' => false, 'status' => 404, 'message' => 'Video niet gevonden in wachtrij.'];
            }

            $isHost = ((int) $lockedRoom->host_character_id === (int) $character->id);
            $isStaffOrMod = $user->isStaff() || (method_exists($user, 'canModerate') && $user->canModerate());
            $isAdder = isset($targetItem['added_by_character_id']) && ((int) $targetItem['added_by_character_id'] === (int) $character->id);

            if (! ($isHost || $isStaffOrMod || $isAdder)) {
                return ['success' => false, 'status' => 403, 'message' => 'Alleen de zaalhost of de toevoeger kan deze video verwijderen.'];
            }

            $remaining = array_values(array_filter($queue, fn ($item) => $item['id'] !== $queueId));
            $lockedRoom->video_queue = $remaining;
            $lockedRoom->save();

            return ['success' => true, 'queue' => $remaining];
        });

        if (! $result['success']) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $result['message']], $result['status']);
            }

            return back()->with('error', $result['message']);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'queue' => $result['queue'],
                'message' => 'Video verwijderd uit wachtrij.',
            ]);
        }

        return back()->with('success', 'Video verwijderd uit wachtrij.');
    }

    /**
     * Send a message, reaction sticker, or GIF in the room chat, or delete a message.
     */
    public function sendMessage(Request $request, CinemaRoom $room): JsonResponse|RedirectResponse
    {
        // Allow deleting chat messages via this endpoint
        if ($request->input('action') === 'delete' || $request->filled('delete_id')) {
            $deleteId = (int) ($request->input('delete_id') ?: $request->input('message_id'));

            return $this->deleteMessage($request, $room, $deleteId);
        }

        if ($room->is_closed) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Deze zaal is gesloten.'], 422);
            }

            return back()->with('error', 'Deze zaal is gesloten.');
        }

        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;

        if (! $character) {
            return $request->wantsJson()
                ? response()->json(['success' => false, 'message' => 'Geen personage gevonden.'], 401)
                : redirect()->route('dashboard')->with('error', 'Geen personage gevonden.');
        }

        if ($character->isImprisoned()) {
            $msg = 'Vanuit de isoleercel kun je geen berichten sturen in de bioscoop!';

            return $request->wantsJson()
                ? response()->json(['success' => false, 'message' => $msg], 422)
                : back()->with('error', $msg);
        }

        if ($character->isComatose()) {
            $msg = 'Je verkeert in Code Blauw (Hartstilstand)! Dien eerst een '.Character::REVIVE_SERUM_NAME.' toe.';

            return $request->wantsJson()
                ? response()->json(['success' => false, 'message' => $msg], 422)
                : back()->with('error', $msg);
        }

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

        $rawMessage = $validated['message'] ?? null;
        $cleanMessage = null;
        if ($rawMessage !== null && trim((string) $rawMessage) !== '') {
            $cleanMessage = strip_tags(trim((string) $rawMessage));
            if ($cleanMessage === '') {
                if ($request->wantsJson()) {
                    return response()->json(['success' => false, 'message' => 'Bericht bevat ongeldige HTML-tekens.'], 422);
                }

                return back()->with('error', 'Bericht bevat ongeldige HTML-tekens.');
            }
        }

        if (! empty($validated['gif_url']) && ! str_starts_with($validated['gif_url'], 'https://')) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Alleen beveiligde HTTPS GIF links zijn toegestaan.'], 422);
            }

            return back()->with('error', 'Alleen beveiligde HTTPS GIF links zijn toegestaan.');
        }

        if (empty($cleanMessage) && empty($validated['gif_url']) && empty($validated['sticker_key'])) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Typ een bericht, selecteer een sticker of kies een GIF reactie!'], 422);
            }

            return back()->with('error', 'Bericht mag niet leeg zijn.');
        }

        if (! empty($cleanMessage)) {
            if ($linkError = $this->validateChatLinks($cleanMessage)) {
                if ($request->wantsJson()) {
                    return response()->json(['success' => false, 'message' => $linkError], 422);
                }

                return back()->with('error', $linkError);
            }

            if ($spamError = $this->validateChatSpam($character, $cleanMessage)) {
                if ($request->wantsJson()) {
                    return response()->json(['success' => false, 'message' => $spamError['message']], $spamError['status']);
                }

                return back()->with('error', $spamError['message']);
            }
        }

        $msg = CinemaMessage::create([
            'room_id' => $room->id,
            'character_id' => $character->id,
            'message' => $cleanMessage ?: null,
            'gif_url' => $validated['gif_url'] ?? null,
            'sticker_key' => $validated['sticker_key'] ?? null,
        ]);

        $msg->load(['character.user']);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => [
                    'id' => (int) $msg->id,
                    'character_id' => (int) $character->id,
                    'text' => $msg->message,
                    'gif_url' => $msg->gif_url,
                    'sticker_key' => $msg->sticker_key,
                    'character_name' => $character->name,
                    'avatar' => asset($character->avatar ?? 'characters/avatar-default.png'),
                    'is_staff' => (bool) $user->isStaff(),
                    'time' => $msg->created_at ? $msg->created_at->format('H:i') : now()->format('H:i'),
                    'can_delete' => true,
                ],
            ]);
        }

        return back();
    }

    /**
     * Delete a cinema message (Author, Room Host, Staff or Moderator).
     */
    public function deleteMessage(Request $request, CinemaRoom $room, ?int $messageId = null): JsonResponse|RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;

        if (! $character) {
            return $request->wantsJson()
                ? response()->json(['success' => false, 'message' => 'Geen personage gevonden.'], 401)
                : redirect()->route('dashboard')->with('error', 'Geen personage gevonden.');
        }

        $id = (int) ($messageId ?: $request->input('delete_id', $request->input('message_id', 0)));
        $message = CinemaMessage::where('room_id', $room->id)->find($id);

        if (! $message) {
            return $request->wantsJson()
                ? response()->json(['success' => false, 'message' => 'Bericht niet gevonden.'], 404)
                : back()->with('error', 'Bericht niet gevonden.');
        }

        $isAuthor = ((int) $message->character_id === (int) $character->id);
        $isHost = ((int) $room->host_character_id === (int) $character->id);
        $isStaffOrMod = $user->isStaff() || (method_exists($user, 'canModerate') && $user->canModerate());

        if (! ($isAuthor || $isHost || $isStaffOrMod)) {
            return $request->wantsJson()
                ? response()->json(['success' => false, 'message' => 'Je hebt geen rechten om dit bericht te verwijderen.'], 403)
                : back()->with('error', 'Je hebt geen rechten om dit bericht te verwijderen.');
        }

        $cacheKey = "cinema_deleted_ids_{$room->id}";
        $deleted = Cache::get($cacheKey, []);
        $deleted[] = (int) $message->id;
        $deleted = array_slice(array_unique(array_map('intval', $deleted)), -50);
        Cache::put($cacheKey, $deleted, now()->addHours(2));

        $message->delete();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Bericht succesvol verwijderd.',
                'deleted_id' => $id,
            ]);
        }

        return back()->with('success', 'Bericht verwijderd.');
    }

    /**
     * Poll latest messages and synchronized state in room (JSON endpoint).
     */
    public function getMessages(Request $request, CinemaRoom $room): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;

        if (! $character) {
            return response()->json(['success' => false, 'message' => 'Geen personage gevonden.'], 401);
        }

        if (! $this->canAccessRoom($request, $room, $user, $character)) {
            return response()->json(['success' => false, 'message' => 'Geen toegang tot deze privézaal.'], 403);
        }

        $afterId = (int) $request->query('after_id', 0);
        $canDeleteAny = (bool) ($user->isStaff() || (method_exists($user, 'canModerate') && $user->canModerate()) || ((int) $room->host_character_id === (int) $character->id));

        $messages = CinemaMessage::where('room_id', $room->id)
            ->where('id', '>', $afterId)
            ->with(['character.user'])
            ->oldest()
            ->take(50)
            ->get()
            ->map(function ($m) use ($character, $canDeleteAny) {
                $isAuthor = $character && ((int) $m->character_id === (int) $character->id);

                return [
                    'id' => (int) $m->id,
                    'character_id' => (int) $m->character_id,
                    'text' => $m->message,
                    'gif_url' => $m->gif_url,
                    'sticker_key' => $m->sticker_key,
                    'character_name' => $m->character?->name ?? 'Onbekend',
                    'avatar' => asset($m->character?->avatar ?? 'characters/avatar-default.png'),
                    'is_staff' => (bool) ($m->character?->user?->isStaff()),
                    'time' => $m->created_at?->format('H:i') ?? '',
                    'can_delete' => (bool) ($isAuthor || $canDeleteAny),
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
            'deleted_ids' => Cache::get("cinema_deleted_ids_{$room->id}", []),
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

        if (! $character) {
            return response()->json(['success' => false, 'message' => 'Geen personage gevonden.'], 401);
        }

        if ($character->isImprisoned() || $character->isComatose()) {
            return response()->json(['success' => false, 'message' => 'Actie niet toegestaan in jouw huidige staat.'], 422);
        }

        if ($room->is_closed) {
            return response()->json(['success' => false, 'message' => 'Deze zaal is gesloten.'], 422);
        }

        if ((int) $room->host_character_id !== (int) $character->id && ! $user->isStaff()) {
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

        if (! $character) {
            return redirect()->route('dashboard')->with('error', 'Geen personage gevonden.');
        }

        if ($character->isImprisoned()) {
            return back()->with('error', 'Vanuit de isoleercel kun je de bioscoopvideo niet wijzigen!');
        }

        if ($character->isComatose()) {
            return back()->with('error', 'Je verkeert in Code Blauw (Hartstilstand)! Dien eerst een '.Character::REVIVE_SERUM_NAME.' toe.');
        }

        if ($room->is_closed) {
            return back()->with('error', 'Deze zaal is gesloten.');
        }

        if ((int) $room->host_character_id !== (int) $character->id && ! $user->isStaff()) {
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

    /**
     * Validate chat links: regular players can only share discord invites or youtube links.
     */
    protected function validateChatLinks(string $message): ?string
    {
        /** @var User|null $user */
        $user = Auth::user();
        if ($user && ($user->isStaff() || $user->isAdmin() || $user->isOwner() || $user->isModerator() || (method_exists($user, 'canModerate') && $user->canModerate()))) {
            return null;
        }

        $hasUrlPattern = (bool) preg_match('/(?:https?:\/\/|www\.)[^\s]+/i', $message)
            || (bool) preg_match('/\b[a-zA-Z0-9_\-]+\.(?:com|nl|be|net|org|io|gg|me|xyz|ru|co|de|info|online|site|app|link|top|tv)\b/i', $message);

        if (! $hasUrlPattern) {
            return null;
        }

        preg_match_all('/(?:https?:\/\/|www\.)?[a-zA-Z0-9_\-]+\.[a-zA-Z0-9_\-\.\/]+/i', $message, $matches);
        $tokens = $matches[0] ?? [];

        foreach ($tokens as $token) {
            $cleanToken = trim(rtrim($token, '.,!?):;'));
            $isAllowed = (bool) preg_match('/^(?:https?:\/\/)?(?:www\.)?(?:discord\.gg|discord\.com\/invite|youtube\.com|youtu\.be)\/[a-zA-Z0-9_\-\.\/\?\=\&]+$/i', $cleanToken);
            if (! $isAllowed) {
                return 'Het delen van externe links of websites is niet toegestaan, behalve YouTube en officiële Discord invites!';
            }
        }

        return null;
    }

    /**
     * Anti-spam validation for cinema chat messages.
     *
     * @return array{message: string, status: int}|null
     */
    protected function validateChatSpam(Character $character, string $message): ?array
    {
        /** @var User|null $user */
        $user = Auth::user();
        if ($user && ($user->isStaff() || $user->isAdmin() || $user->isOwner() || $user->isModerator() || (method_exists($user, 'canModerate') && $user->canModerate()))) {
            return null;
        }

        $charId = $character->id;

        // 1. Rapid-fire cooldown (2 seconds between messages)
        $cooldownKey = "cinema_chat_cooldown_{$charId}";
        if (Cache::has($cooldownKey)) {
            return ['message' => 'Rustig aan! Wacht even 2 seconden voor je volgende bericht.', 'status' => 429];
        }

        // 2. Velocity burst limit: max 4 messages per 8 seconds
        $velocityKey = "cinema_chat_velocity_{$charId}";
        $count = (int) Cache::get($velocityKey, 0);
        if ($count >= 4) {
            return ['message' => 'Anti-spam: Je stuurt te snel berichten achter elkaar. Wacht even 8 seconden.', 'status' => 429];
        }

        // 3. Duplicate message spam protection (exact same text within 30 seconds)
        $lastMsgKey = "cinema_chat_last_msg_{$charId}";
        $lastMsg = Cache::get($lastMsgKey);
        if ($lastMsg !== null && mb_strtolower(trim((string) $lastMsg)) === mb_strtolower(trim($message))) {
            return ['message' => 'Anti-spam: Je hebt dit identieke bericht zojuist al verstuurd. Niet spammen a.u.b.!', 'status' => 422];
        }

        // 4. Repeated character flooding (e.g. "aaaaaa..." or "?????...")
        if (preg_match('/(\S)\1{12,}/u', $message)) {
            return ['message' => 'Anti-spam: Je bericht bevat te veel herhalende tekens achter elkaar.', 'status' => 422];
        }

        // Set cooldown and increment burst counter
        Cache::put($cooldownKey, true, now()->addSeconds(2));
        Cache::put($velocityKey, $count + 1, now()->addSeconds(8));
        Cache::put($lastMsgKey, $message, now()->addSeconds(30));

        return null;
    }
}
