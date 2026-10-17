<?php

namespace App\Http\Controllers;

use App\Models\Character;
use App\Models\ChatMessage;
use App\Models\DirectMessage;
use App\Models\Friend;
use App\Models\User;
use App\Services\EnergyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ChatController extends Controller
{
    public function __construct(
        protected EnergyService $energyService,
    ) {}

    /**
     * Main Chat & Messenger Page.
     */
    public function index(Request $request): View
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;
        $this->energyService->refresh($character);

        $initialTab = $request->query('tab', 'global');
        $initialRecipient = (int) $request->query('recipient', 0);
        if ($request->has('recipient') || $request->query('tab') === 'direct') {
            $initialTab = 'direct';
        }

        // Friends list for quick starting new direct messages
        $friendIds = Friend::where('character_id', $character->id)
            ->where('status', 'accepted')
            ->pluck('friend_id');

        $friends = Character::whereIn('id', $friendIds)
            ->orderBy('name')
            ->get(['id', 'name', 'avatar', 'level', 'last_active_at']);

        $totalUnreadDms = DirectMessage::where('recipient_id', $character->id)
            ->where('is_read', false)
            ->count();

        return view('game.chat.index', compact(
            'character',
            'friends',
            'initialTab',
            'initialRecipient',
            'totalUnreadDms'
        ));
    }

    /**
     * Get public chat messages stream.
     */
    public function publicMessages(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;

        $channel = $request->query('channel', 'global');
        $afterId = (int) $request->query('after_id', 0);

        $query = ChatMessage::with(['character.gang', 'character.user'])
            ->where('channel', $channel);

        if ($afterId > 0) {
            $messages = $query->where('id', '>', $afterId)
                ->orderBy('id', 'asc')
                ->take(50)
                ->get();
        } else {
            $messages = $query->orderBy('id', 'desc')
                ->take(40)
                ->get()
                ->reverse()
                ->values();
        }

        $formatted = $messages->map(function (ChatMessage $msg) use ($character) {
            $char = $msg->character;
            $user = $char?->user;
            $gang = $char?->gang;

            return [
                'id' => $msg->id,
                'character_id' => $msg->character_id,
                'name' => $char?->name ?? 'Onbekende Inmate',
                'avatar' => $char?->avatar ?? 'avatar-1.png',
                'level' => $char?->level ?? 1,
                'is_staff' => (bool) ($user?->is_admin || $user?->is_moderator),
                'staff_role' => $user?->is_admin ? 'Directeur' : ($user?->is_moderator ? 'Bewaker' : null),
                'gang_tag' => $gang?->tag ?? null,
                'gang_color' => $gang?->color ?? 'purple',
                'message' => $msg->message,
                'time' => $msg->created_at->format('H:i'),
                'is_me' => $msg->character_id === $character->id,
            ];
        });

        return response()->json([
            'success' => true,
            'messages' => $formatted,
            'last_id' => $messages->last()?->id ?? $afterId,
        ]);
    }

    /**
     * Send message to public chat.
     */
    public function sendPublic(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;

        if ($character->isImprisoned()) {
            return response()->json([
                'success' => false,
                'message' => 'Vanuit de isoleercel kun je niet meepraten in de recreatieruimte! Kijk een ad om direct vrij te komen.',
            ], 422);
        }

        $cacheKey = "chat_rate_limit_{$character->id}";
        if (Cache::has($cacheKey)) {
            return response()->json([
                'success' => false,
                'message' => 'Rustig aan! Wacht even 2 seconden voor je volgende bericht.',
            ], 429);
        }

        $rawMessage = trim((string) $request->input('message', ''));
        $cleanMessage = strip_tags($rawMessage);

        if ($cleanMessage === '') {
            return response()->json([
                'success' => false,
                'message' => 'Typ eerst een bericht.',
            ], 422);
        }

        if (mb_strlen($cleanMessage) > 350) {
            return response()->json([
                'success' => false,
                'message' => 'Bericht is te lang (maximaal 350 tekens).',
            ], 422);
        }

        $channel = $request->input('channel', 'global');

        $chatMsg = ChatMessage::create([
            'character_id' => $character->id,
            'channel' => $channel,
            'message' => $cleanMessage,
        ]);

        Cache::put($cacheKey, true, now()->addSeconds(2));

        $gang = $character->gang;

        return response()->json([
            'success' => true,
            'message' => [
                'id' => $chatMsg->id,
                'character_id' => $character->id,
                'name' => $character->name,
                'avatar' => $character->avatar ?? 'avatar-1.png',
                'level' => $character->level,
                'is_staff' => (bool) ($user->is_admin || $user->is_moderator),
                'staff_role' => $user->is_admin ? 'Directeur' : ($user->is_moderator ? 'Bewaker' : null),
                'gang_tag' => $gang?->tag ?? null,
                'gang_color' => $gang?->color ?? 'purple',
                'message' => $chatMsg->message,
                'time' => $chatMsg->created_at->format('H:i'),
                'is_me' => true,
            ],
        ]);
    }

    /**
     * Get list of direct message conversations (including Kliniek Directie bot).
     */
    public function conversations(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;

        // 1. Pinned Kliniek Directie Bot Conversation
        $botLastMessage = DirectMessage::whereNull('sender_id')
            ->where('recipient_id', $character->id)
            ->latest('id')
            ->first();

        $botUnread = DirectMessage::whereNull('sender_id')
            ->where('recipient_id', $character->id)
            ->where('is_read', false)
            ->count();

        $conversations = [];

        $conversations[] = [
            'partner_id' => 0,
            'name' => '🏥 Kliniek Directie',
            'avatar' => 'directie-bot.png',
            'is_bot' => true,
            'is_online' => true,
            'level' => 99,
            'last_message' => $botLastMessage ? Str::limit($botLastMessage->message, 45) : 'Officiële kluiscodes, beloningen en level updates.',
            'last_time' => $botLastMessage ? $botLastMessage->created_at->diffForHumans(null, true, true) : '',
            'unread_count' => $botUnread,
            'is_pinned' => true,
        ];

        // 2. Fetch distinct inmate conversation partner IDs
        $sentTo = DirectMessage::where('sender_id', $character->id)
            ->whereNotNull('recipient_id')
            ->pluck('recipient_id');

        $receivedFrom = DirectMessage::where('recipient_id', $character->id)
            ->whereNotNull('sender_id')
            ->pluck('sender_id');

        $partnerIds = $sentTo->merge($receivedFrom)->unique()->values();

        if ($partnerIds->isNotEmpty()) {
            $partners = Character::whereIn('id', $partnerIds)
                ->get()
                ->keyBy('id');

            foreach ($partnerIds as $pId) {
                $partner = $partners->get($pId);
                if (! $partner) {
                    continue;
                }

                $lastMsg = DirectMessage::where(function ($q) use ($character, $pId) {
                    $q->where('sender_id', $character->id)->where('recipient_id', $pId);
                })->orWhere(function ($q) use ($character, $pId) {
                    $q->where('sender_id', $pId)->where('recipient_id', $character->id);
                })
                    ->latest('id')
                    ->first();

                $unread = DirectMessage::where('sender_id', $pId)
                    ->where('recipient_id', $character->id)
                    ->where('is_read', false)
                    ->count();

                $isOnline = $partner->last_active_at && $partner->last_active_at->diffInMinutes(now()) < 5;

                $conversations[] = [
                    'partner_id' => $partner->id,
                    'name' => $partner->name,
                    'avatar' => $partner->avatar ?? 'avatar-1.png',
                    'is_bot' => false,
                    'is_online' => (bool) $isOnline,
                    'level' => $partner->level,
                    'last_message' => $lastMsg ? Str::limit($lastMsg->message, 45) : 'Nieuw gesprek gestart.',
                    'last_time' => $lastMsg ? $lastMsg->created_at->diffForHumans(null, true, true) : '',
                    'unread_count' => $unread,
                    'is_pinned' => false,
                ];
            }
        }

        return response()->json([
            'success' => true,
            'conversations' => $conversations,
        ]);
    }

    /**
     * Get direct messages with a specific partner (or 0 for Kliniek Directie).
     */
    public function directMessages(Request $request, int $partnerId): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;

        if ($partnerId === 0) {
            // Kliniek Directie Bot messages
            $messages = DirectMessage::whereNull('sender_id')
                ->where('recipient_id', $character->id)
                ->orderBy('id', 'desc')
                ->take(40)
                ->get()
                ->reverse()
                ->values();

            // Mark as read
            DirectMessage::whereNull('sender_id')
                ->where('recipient_id', $character->id)
                ->where('is_read', false)
                ->update(['is_read' => true]);

            $partnerData = [
                'id' => 0,
                'name' => '🏥 Kliniek Directie',
                'avatar' => 'directie-bot.png',
                'is_bot' => true,
                'is_online' => true,
                'level' => 99,
                'status_label' => 'Officieel Behandelcomité & Systeem Bot',
            ];
        } else {
            $partner = Character::find($partnerId);
            if (! $partner) {
                return response()->json(['success' => false, 'message' => 'Patiënt niet gevonden.'], 404);
            }

            $messages = DirectMessage::where(function ($q) use ($character, $partnerId) {
                $q->where('sender_id', $character->id)->where('recipient_id', $partnerId);
            })->orWhere(function ($q) use ($character, $partnerId) {
                $q->where('sender_id', $partnerId)->where('recipient_id', $character->id);
            })
                ->orderBy('id', 'desc')
                ->take(50)
                ->get()
                ->reverse()
                ->values();

            // Mark incoming as read
            DirectMessage::where('sender_id', $partnerId)
                ->where('recipient_id', $character->id)
                ->where('is_read', false)
                ->update(['is_read' => true]);

            $isOnline = $partner->last_active_at && $partner->last_active_at->diffInMinutes(now()) < 5;

            $partnerData = [
                'id' => $partner->id,
                'name' => $partner->name,
                'avatar' => $partner->avatar ?? 'avatar-1.png',
                'is_bot' => false,
                'is_online' => (bool) $isOnline,
                'level' => $partner->level,
                'status_label' => $isOnline ? 'Nu online' : ($partner->last_active_at ? 'Laatst gezien '.$partner->last_active_at->diffForHumans() : 'Offline'),
            ];
        }

        $formatted = $messages->map(function (DirectMessage $msg) use ($character) {
            return [
                'id' => $msg->id,
                'sender_id' => $msg->sender_id,
                'recipient_id' => $msg->recipient_id,
                'message' => $msg->message,
                'type' => $msg->type,
                'metadata' => $msg->metadata,
                'time' => $msg->created_at->format('H:i'),
                'date' => $msg->created_at->format('d-m-Y'),
                'is_me' => $msg->sender_id === $character->id,
            ];
        });

        return response()->json([
            'success' => true,
            'partner' => $partnerData,
            'messages' => $formatted,
        ]);
    }

    /**
     * Send direct message to an inmate.
     */
    public function sendDirect(Request $request, int $partnerId): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;

        if ($partnerId === 0) {
            return response()->json([
                'success' => false,
                'message' => 'Je kunt niet antwoorden op officiële berichten van de Kliniek Directie.',
            ], 422);
        }

        if ($character->isImprisoned()) {
            return response()->json([
                'success' => false,
                'message' => 'In de isoleercel kun je geen privégesprekken voeren! Kijk een ad om direct vrij te komen.',
            ], 422);
        }

        $partner = Character::find($partnerId);
        if (! $partner) {
            return response()->json(['success' => false, 'message' => 'Patiënt niet gevonden.'], 404);
        }

        if ($partner->id === $character->id) {
            return response()->json(['success' => false, 'message' => 'Je kunt geen privébericht naar jezelf sturen.'], 422);
        }

        $rawMessage = trim((string) $request->input('message', ''));
        $cleanMessage = strip_tags($rawMessage);

        if ($cleanMessage === '') {
            return response()->json(['success' => false, 'message' => 'Typ eerst een bericht.'], 422);
        }

        if (mb_strlen($cleanMessage) > 1000) {
            return response()->json(['success' => false, 'message' => 'Bericht is te lang (maximaal 1000 tekens).'], 422);
        }

        $msg = DirectMessage::create([
            'sender_id' => $character->id,
            'recipient_id' => $partner->id,
            'message' => $cleanMessage,
            'type' => 'chat',
            'is_read' => false,
        ]);

        return response()->json([
            'success' => true,
            'message' => [
                'id' => $msg->id,
                'sender_id' => $character->id,
                'recipient_id' => $partner->id,
                'message' => $msg->message,
                'type' => 'chat',
                'metadata' => null,
                'time' => $msg->created_at->format('H:i'),
                'is_me' => true,
            ],
        ]);
    }

    /**
     * Get unread DMs count for layout badges.
     */
    public function unreadCount(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user?->character;

        if (! $character) {
            return response()->json(['unread' => 0]);
        }

        $count = DirectMessage::where('recipient_id', $character->id)
            ->where('is_read', false)
            ->count();

        return response()->json(['unread' => $count]);
    }
}
