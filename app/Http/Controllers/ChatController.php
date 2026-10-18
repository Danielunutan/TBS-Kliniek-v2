<?php

namespace App\Http\Controllers;

use App\Models\Character;
use App\Models\ChatMessage;
use App\Models\DirectMessage;
use App\Models\Friend;
use App\Models\Gang;
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

        if (str_starts_with($channel, 'gang_')) {
            $gangId = (int) str_replace('gang_', '', $channel);
            if (! $character->gang_id || (int) $character->gang_id !== $gangId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Je hebt geen toegang tot deze bende chat.',
                ], 403);
            }
            $gang = Gang::find($gangId);
            if (! $gang || ! $gang->has_gang_chat) {
                return response()->json([
                    'success' => false,
                    'locked' => true,
                    'message' => 'Deze bende heeft de Portofoon (Gang Chat) nog niet aangeschaft in de Bende Winkel.',
                ], 403);
            }
        }

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

            $isOwner = (bool) ($user?->isOwner());
            $isAdmin = (bool) ($user?->isAdmin());
            $isMod = (bool) ($user?->isModerator() || $user?->canModerate());

            $staffRole = $isOwner ? 'Eigenaar' : ($isAdmin ? 'Directie' : ($isMod ? 'Moderator' : null));
            $staffBadgeColor = $isOwner ? 'amber' : ($isAdmin ? 'purple' : ($isMod ? 'blue' : null));

            return [
                'id' => $msg->id,
                'character_id' => $msg->character_id,
                'name' => $char?->name ?? 'Onbekende Inmate',
                'avatar' => $char?->avatar_url ?? asset('characters/avatar-default.png'),
                'level' => $char?->level ?? 1,
                'respect' => $char?->respect ?? 0,
                'is_owner' => $isOwner,
                'is_admin' => $isAdmin,
                'is_moderator' => $isMod,
                'is_staff' => (bool) ($user?->isStaff() || $isMod),
                'staff_role' => $staffRole,
                'staff_badge_color' => $staffBadgeColor,
                'gang_tag' => $gang?->tag ?? null,
                'gang_color' => $gang?->color ?? 'purple',
                'message' => $msg->message,
                'time' => $msg->created_at->format('H:i'),
                'is_me' => $msg->character_id === $character->id,
                'reply_to_id' => $msg->reply_to_id,
                'reply_to_name' => $msg->reply_to_name,
                'reply_to_snippet' => $msg->reply_to_snippet,
                'hearts_count' => (int) ($msg->hearts_count ?? 0),
                'has_hearted' => in_array($character->id, $msg->hearts_users ?? [], true),
            ];
        });

        return response()->json([
            'success' => true,
            'messages' => $formatted,
            'last_id' => $messages->last()?->id ?? $afterId,
        ]);
    }

    /**
     * Validate that message does not contain disallowed links.
     * Only discord invites (discord.gg/..., discord.com/invite/...) are permitted.
     */
    protected function validateChatLinks(string $message): ?JsonResponse
    {
        $hasUrlPattern = (bool) preg_match('/(?:https?:\/\/|www\.)[^\s]+/i', $message)
            || (bool) preg_match('/\b[a-zA-Z0-9_\-]+\.(?:com|nl|be|net|org|io|gg|me|xyz|ru|co|de|info|online|site|app|link|top|tv)\b/i', $message);

        if (! $hasUrlPattern) {
            return null;
        }

        preg_match_all('/(?:https?:\/\/|www\.)?[a-zA-Z0-9_\-]+\.[a-zA-Z0-9_\-\.\/]+/i', $message, $matches);
        $tokens = $matches[0] ?? [];

        foreach ($tokens as $token) {
            $cleanToken = trim(rtrim($token, '.,!?):;'));
            $isDiscordInvite = (bool) preg_match('/^(?:https?:\/\/)?(?:www\.)?(?:discord\.gg|discord\.com\/invite)\/[a-zA-Z0-9_\-]+$/i', $cleanToken);
            if (! $isDiscordInvite) {
                return response()->json([
                    'success' => false,
                    'message' => 'Het delen van externe links of websites is niet toegestaan, behalve officiële Discord invites!',
                ], 422);
            }
        }

        return null;
    }

    /**
     * Anti-spam validation for chat and direct messages.
     */
    protected function validateChatSpam(Character $character, string $message, string $type = 'public'): ?JsonResponse
    {
        $charId = $character->id;

        // 1. Rapid-fire cooldown (2 seconds between messages)
        $cooldownKey = "chat_cooldown_{$charId}_{$type}";
        if (Cache::has($cooldownKey)) {
            return response()->json([
                'success' => false,
                'message' => 'Rustig aan! Wacht even 2 seconden voor je volgende bericht.',
            ], 429);
        }

        // 2. Velocity burst limit: max 4 messages per 8 seconds
        $velocityKey = "chat_velocity_{$charId}_{$type}";
        $count = (int) Cache::get($velocityKey, 0);
        if ($count >= 4) {
            return response()->json([
                'success' => false,
                'message' => 'Anti-spam: Je stuurt te snel berichten achter elkaar. Wacht even 8 seconden.',
            ], 429);
        }

        // 3. Duplicate message spam protection (exact same text within 30 seconds)
        $lastMsgKey = "chat_last_msg_{$charId}_{$type}";
        $lastMsg = Cache::get($lastMsgKey);
        if ($lastMsg !== null && mb_strtolower(trim((string) $lastMsg)) === mb_strtolower(trim($message))) {
            return response()->json([
                'success' => false,
                'message' => 'Anti-spam: Je hebt dit identieke bericht zojuist al verstuurd. Niet spammen a.u.b.!',
            ], 422);
        }

        // 4. Repeated character flooding (e.g. "aaaaaa..." or "?????...")
        if (preg_match('/(\S)\1{12,}/u', $message)) {
            return response()->json([
                'success' => false,
                'message' => 'Anti-spam: Je bericht bevat te veel herhalende tekens achter elkaar.',
            ], 422);
        }

        // Set cooldown and increment burst counter
        Cache::put($cooldownKey, true, now()->addSeconds(2));
        Cache::put($velocityKey, $count + 1, now()->addSeconds(8));
        Cache::put($lastMsgKey, $message, now()->addSeconds(30));

        return null;
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

        $rawMessage = trim((string) $request->input('message', ''));
        $cleanMessage = strip_tags($rawMessage);

        if ($cleanMessage === '') {
            return response()->json([
                'success' => false,
                'message' => 'Typ eerst een bericht.',
            ], 422);
        }

        if (mb_strlen($cleanMessage) > 250) {
            return response()->json([
                'success' => false,
                'message' => 'Bericht is te lang (maximaal 250 tekens).',
            ], 422);
        }

        if ($linkError = $this->validateChatLinks($cleanMessage)) {
            return $linkError;
        }

        if ($spamError = $this->validateChatSpam($character, $cleanMessage, 'public')) {
            return $spamError;
        }

        $channel = $request->input('channel', 'global');

        if (str_starts_with($channel, 'gang_')) {
            $gangId = (int) str_replace('gang_', '', $channel);
            if (! $character->gang_id || (int) $character->gang_id !== $gangId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Je bent geen lid van deze bende!',
                ], 403);
            }
            $gang = Gang::find($gangId);
            if (! $gang || ! $gang->has_gang_chat) {
                return response()->json([
                    'success' => false,
                    'message' => 'Jullie bende heeft de Besloten Gang Chat nog niet ontgrendeld voor 500 sterren.',
                ], 422);
            }
        }

        $replyToId = (int) $request->input('reply_to_id', 0);
        $replyToName = null;
        $replyToSnippet = null;
        if ($replyToId > 0) {
            $targetMsg = ChatMessage::with('character')->find($replyToId);
            if ($targetMsg) {
                $replyToName = $targetMsg->character?->name ?? 'Onbekend';
                $replyToSnippet = Str::limit($targetMsg->message, 80);
            } else {
                $replyToId = null;
            }
        } else {
            $replyToId = null;
        }

        $chatMsg = ChatMessage::create([
            'character_id' => $character->id,
            'channel' => $channel,
            'message' => $cleanMessage,
            'reply_to_id' => $replyToId,
            'reply_to_name' => $replyToName,
            'reply_to_snippet' => $replyToSnippet,
            'hearts_count' => 0,
            'hearts_users' => [],
        ]);

        $gang = $character->gang;

        $isOwner = (bool) ($user->isOwner());
        $isAdmin = (bool) ($user->isAdmin());
        $isMod = (bool) ($user->isModerator() || $user->canModerate());

        $staffRole = $isOwner ? 'Eigenaar' : ($isAdmin ? 'Directie' : ($isMod ? 'Moderator' : null));
        $staffBadgeColor = $isOwner ? 'amber' : ($isAdmin ? 'purple' : ($isMod ? 'blue' : null));

        return response()->json([
            'success' => true,
            'message' => [
                'id' => $chatMsg->id,
                'character_id' => $character->id,
                'name' => $character->name,
                'avatar' => $character->avatar_url,
                'level' => $character->level,
                'respect' => $character->respect ?? 0,
                'is_owner' => $isOwner,
                'is_admin' => $isAdmin,
                'is_moderator' => $isMod,
                'is_staff' => (bool) ($user->isStaff() || $isMod),
                'staff_role' => $staffRole,
                'staff_badge_color' => $staffBadgeColor,
                'gang_tag' => $gang?->tag ?? null,
                'gang_color' => $gang?->color ?? 'purple',
                'message' => $chatMsg->message,
                'time' => $chatMsg->created_at->format('H:i'),
                'is_me' => true,
                'reply_to_id' => $chatMsg->reply_to_id,
                'reply_to_name' => $chatMsg->reply_to_name,
                'reply_to_snippet' => $chatMsg->reply_to_snippet,
                'hearts_count' => 0,
                'has_hearted' => false,
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

        $botPreview = $botLastMessage
            ? Str::limit(str_replace(['**', '__'], '', $botLastMessage->message), 45)
            : 'Officiële kluiscodes, beloningen en level updates.';

        $conversations[] = [
            'partner_id' => 0,
            'name' => '🏥 Kliniek Directie',
            'avatar' => 'directie-bot.png',
            'is_bot' => true,
            'is_online' => true,
            'level' => 99,
            'last_message' => $botPreview,
            'last_time' => $botLastMessage ? $botLastMessage->created_at->diffForHumans(null, true, true) : '',
            'unread_count' => $botUnread,
            'is_pinned' => true,
        ];

        // 2. Fetch distinct inmate conversation partner IDs (excluding legacy migrated notices)
        $sentTo = DirectMessage::where('sender_id', $character->id)
            ->whereNotNull('recipient_id')
            ->pluck('recipient_id');

        $receivedFrom = DirectMessage::where('recipient_id', $character->id)
            ->whereNotNull('sender_id')
            ->where(function ($q) {
                $q->whereNull('metadata->source')
                    ->orWhere('metadata->source', '!=', 'system_migration');
            })
            ->where('message', 'not like', '%vriendschapsverzoek%')
            ->pluck('sender_id');

        $partnerIds = $sentTo->merge($receivedFrom)->unique()->values();

        if ($partnerIds->isNotEmpty()) {
            $partners = Character::whereIn('id', $partnerIds)
                ->get()
                ->keyBy('id');

            $inmateConversations = [];

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

                if (! $lastMsg) {
                    continue;
                }

                $unread = DirectMessage::where('sender_id', $pId)
                    ->where('recipient_id', $character->id)
                    ->where('is_read', false)
                    ->count();

                $isOnline = $partner->last_active_at && $partner->last_active_at->diffInMinutes(now()) < 5;

                $cleanPreview = Str::limit(str_replace(['**', '__'], '', $lastMsg->message), 45);

                $inmateConversations[] = [
                    'partner_id' => $partner->id,
                    'name' => $partner->name,
                    'avatar' => $partner->avatar_url,
                    'is_bot' => false,
                    'is_online' => (bool) $isOnline,
                    'level' => $partner->level,
                    'last_message' => $cleanPreview,
                    'last_time' => $lastMsg->created_at->diffForHumans(null, true, true),
                    'last_time_ts' => $lastMsg->created_at->timestamp,
                    'last_msg_id' => $lastMsg->id,
                    'unread_count' => $unread,
                    'is_pinned' => false,
                ];
            }

            // Strictly sort by latest message timestamp descending (newest message always at top)
            usort($inmateConversations, function ($a, $b) {
                return $b['last_time_ts'] <=> $a['last_time_ts'];
            });

            foreach ($inmateConversations as $c) {
                $conversations[] = $c;
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
                ->update(['is_read' => true, 'read_at' => now()]);

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
                ->update(['is_read' => true, 'read_at' => now()]);

            $isOnline = $partner->last_active_at && $partner->last_active_at->diffInMinutes(now()) < 5;

            $partnerData = [
                'id' => $partner->id,
                'name' => $partner->name,
                'avatar' => $partner->avatar_url,
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
                'is_read' => (bool) $msg->is_read,
                'read_at' => $msg->read_at ? $msg->read_at->format('H:i') : null,
                'reply_to_id' => $msg->reply_to_id,
                'reply_to_name' => $msg->reply_to_name,
                'reply_to_snippet' => $msg->reply_to_snippet,
                'hearts_count' => (int) ($msg->hearts_count ?? 0),
                'has_hearted' => in_array($character->id, $msg->hearts_users ?? [], true),
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

        if (mb_strlen($cleanMessage) > 500) {
            return response()->json(['success' => false, 'message' => 'Bericht is te lang (maximaal 500 tekens).'], 422);
        }

        if ($linkError = $this->validateChatLinks($cleanMessage)) {
            return $linkError;
        }

        if ($spamError = $this->validateChatSpam($character, $cleanMessage, 'direct')) {
            return $spamError;
        }

        $replyToId = (int) $request->input('reply_to_id', 0);
        $replyToName = null;
        $replyToSnippet = null;
        if ($replyToId > 0) {
            $targetDm = DirectMessage::with('sender')->find($replyToId);
            if ($targetDm) {
                $replyToName = $targetDm->sender?->name ?? 'Kliniek Directie';
                $replyToSnippet = Str::limit($targetDm->message, 80);
            } else {
                $replyToId = null;
            }
        } else {
            $replyToId = null;
        }

        $msg = DirectMessage::create([
            'sender_id' => $character->id,
            'recipient_id' => $partner->id,
            'message' => $cleanMessage,
            'type' => 'chat',
            'is_read' => false,
            'read_at' => null,
            'reply_to_id' => $replyToId,
            'reply_to_name' => $replyToName,
            'reply_to_snippet' => $replyToSnippet,
            'hearts_count' => 0,
            'hearts_users' => [],
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
                'is_read' => false,
                'read_at' => null,
                'reply_to_id' => $msg->reply_to_id,
                'reply_to_name' => $msg->reply_to_name,
                'reply_to_snippet' => $msg->reply_to_snippet,
                'hearts_count' => 0,
                'has_hearted' => false,
            ],
        ]);
    }

    /**
     * Poll incoming notifications for both DMs and unread count.
     */
    public function pollIncoming(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user?->character;

        if (! $character) {
            return response()->json(['success' => false], 403);
        }

        $afterDmId = (int) $request->query('after_dm_id', 0);

        // Find latest unread DM for this character
        $query = DirectMessage::with(['sender'])
            ->where('recipient_id', $character->id)
            ->where('is_read', false);

        if ($afterDmId > 0) {
            $query->where('id', '>', $afterDmId);
        }

        /** @var DirectMessage|null $latestDm */
        $latestDm = $query->orderBy('id', 'desc')->first();

        $unreadCount = DirectMessage::where('recipient_id', $character->id)
            ->where('is_read', false)
            ->count();

        $dmData = null;
        if ($latestDm) {
            $sender = $latestDm->sender;
            $dmData = [
                'id' => $latestDm->id,
                'sender_id' => $latestDm->sender_id ?? 0,
                'sender_name' => $sender ? $sender->name : '🏥 Kliniek Directie',
                'sender_avatar' => $sender ? $sender->avatar_url : asset('characters/avatar-default.png'),
                'message' => Str::limit($latestDm->message, 80),
                'time' => $latestDm->created_at->format('H:i'),
                'is_bot' => $latestDm->sender_id === null,
            ];
        }

        return response()->json([
            'success' => true,
            'unread_dms' => $unreadCount,
            'new_dm' => $dmData,
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

    /**
     * Search players dynamically to quickly start a new private chat.
     */
    public function searchPlayers(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user?->character;

        if (! $character) {
            return response()->json(['success' => false, 'players' => []], 403);
        }

        $query = trim((string) $request->query('q', ''));
        if (mb_strlen($query) < 2) {
            return response()->json(['success' => true, 'players' => []]);
        }

        $players = Character::with(['gang', 'user'])
            ->where('id', '!=', $character->id)
            ->where('name', 'like', "%{$query}%")
            ->orderBy('level', 'desc')
            ->take(12)
            ->get();

        $formatted = $players->map(function (Character $p) {
            $user = $p->user;
            $isOwner = (bool) ($user?->isOwner());
            $isAdmin = (bool) ($user?->isAdmin());
            $isMod = (bool) ($user?->isModerator() || $user?->canModerate());
            $staffRole = $isOwner ? 'Eigenaar' : ($isAdmin ? 'Directie' : ($isMod ? 'Moderator' : null));

            return [
                'id' => $p->id,
                'name' => $p->name,
                'avatar' => $p->avatar_url,
                'level' => $p->level,
                'respect' => $p->respect,
                'is_online' => $p->isOnline(),
                'is_staff' => (bool) ($user?->isStaff() || $isMod),
                'staff_role' => $staffRole,
                'gang_tag' => $p->gang?->tag,
                'gang_color' => $p->gang?->color ?? 'purple',
            ];
        });

        return response()->json([
            'success' => true,
            'players' => $formatted,
        ]);
    }

    /**
     * Toggle a purple heart (💜) on a public/gang chat message.
     */
    public function togglePublicHeart(Request $request, int $id): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;

        $msg = ChatMessage::findOrFail($id);
        $hearts = is_array($msg->hearts_users) ? $msg->hearts_users : [];

        if (in_array($character->id, $hearts, true)) {
            $hearts = array_values(array_filter($hearts, fn ($uid) => $uid !== $character->id));
            $hasHearted = false;
        } else {
            $hearts[] = $character->id;
            $hasHearted = true;
        }

        $msg->hearts_users = $hearts;
        $msg->hearts_count = count($hearts);
        $msg->save();

        return response()->json([
            'success' => true,
            'hearts_count' => $msg->hearts_count,
            'has_hearted' => $hasHearted,
        ]);
    }

    /**
     * Toggle a purple heart (💜) on a direct message.
     */
    public function toggleDirectHeart(Request $request, int $id): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;

        $msg = DirectMessage::findOrFail($id);

        if ($msg->sender_id !== $character->id && $msg->recipient_id !== $character->id) {
            return response()->json(['success' => false, 'message' => 'Geen toegang.'], 403);
        }

        $hearts = is_array($msg->hearts_users) ? $msg->hearts_users : [];

        if (in_array($character->id, $hearts, true)) {
            $hearts = array_values(array_filter($hearts, fn ($uid) => $uid !== $character->id));
            $hasHearted = false;
        } else {
            $hearts[] = $character->id;
            $hasHearted = true;
        }

        $msg->hearts_users = $hearts;
        $msg->hearts_count = count($hearts);
        $msg->save();

        return response()->json([
            'success' => true,
            'hearts_count' => $msg->hearts_count,
            'has_hearted' => $hasHearted,
        ]);
    }

    /**
     * Delete a public/gang chat message (Moderators/Staff only).
     */
    public function deletePublicMessage(Request $request, int $id): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();

        if (! ($user->isStaff() || $user->canModerate())) {
            return response()->json([
                'success' => false,
                'message' => 'Alleen moderators kunnen chatberichten verwijderen.',
            ], 403);
        }

        $msg = ChatMessage::findOrFail($id);
        $msg->delete();

        return response()->json([
            'success' => true,
            'message' => 'Bericht succesvol verwijderd.',
        ]);
    }

    /**
     * Delete a direct message (Sender, Recipient or Moderator).
     */
    public function deleteDirectMessage(Request $request, int $id): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;

        $msg = DirectMessage::findOrFail($id);

        $canDelete = ($msg->sender_id === $character->id)
            || ($msg->recipient_id === $character->id)
            || $user->isStaff()
            || $user->canModerate();

        if (! $canDelete) {
            return response()->json([
                'success' => false,
                'message' => 'Geen bevoegdheid om dit bericht te verwijderen.',
            ], 403);
        }

        $msg->delete();

        return response()->json([
            'success' => true,
            'message' => 'Bericht verwijderd.',
        ]);
    }
}
