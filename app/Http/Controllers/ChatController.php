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
use Illuminate\Support\Facades\DB;
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
        $character = $user?->character;

        if (! $character) {
            abort(404, 'Karakter niet gevonden.');
        }

        $this->energyService->refresh($character);

        $initialTab = $request->query('tab', 'global');
        $initialRecipient = (int) $request->query('recipient', 0);
        if ($request->has('recipient') || $request->query('tab') === 'direct') {
            $initialTab = 'direct';
        } elseif ($request->query('tab') === 'gang') {
            $initialTab = 'gang';
        } elseif ($request->query('tab') === 'help') {
            $initialTab = 'help';
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

        $gang = $character->gang;
        $hasGang = (bool) $gang;
        $gangHasChat = (bool) ($gang?->has_gang_chat);
        $gangId = (int) ($gang?->id ?? 0);
        $gangName = (string) ($gang?->name ?? 'Bende');
        $gangTag = (string) ($gang?->tag ?? '');
        $gangStars = (int) ($gang?->gang_stars ?? 0);
        $characterStars = (int) ($character->stars ?? 0);
        $canModerate = (bool) ($user->isStaff() || $user->canModerate() || $user->isModerator() || $user->isOwner() || $user->isAdmin());

        $characterNames = Cache::remember('all_character_names_list', 300, function () {
            return Character::orderByRaw('LENGTH(name) DESC')->pluck('name')->all();
        });

        return view('game.chat.index', compact(
            'character',
            'friends',
            'initialTab',
            'initialRecipient',
            'totalUnreadDms',
            'hasGang',
            'gangHasChat',
            'gangId',
            'gangName',
            'gangTag',
            'gangStars',
            'characterStars',
            'canModerate',
            'characterNames'
        ));
    }

    public function publicMessages(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user?->character;

        if (! $character) {
            return response()->json(['success' => false, 'message' => 'Geen personage gevonden.'], 403);
        }

        $channel = (string) $request->query('channel', 'global');
        if ($channel !== 'global' && ! preg_match('/^gang_\d+$/', $channel)) {
            return response()->json([
                'success' => false,
                'message' => 'Ongeldig chatkanaal.',
            ], 422);
        }

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

        if ($afterId > 0) {
            $cachedLastId = Cache::get("chat_last_id_{$channel}");
            if ($cachedLastId !== null && $afterId >= (int) $cachedLastId) {
                return response()->json([
                    'success' => true,
                    'messages' => [],
                    'deleted_ids' => array_values(Cache::get("chat_deleted_ids_{$channel}", [])),
                    'last_id' => (int) $cachedLastId,
                ]);
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

        if ($messages->isNotEmpty()) {
            Cache::put("chat_last_id_{$channel}", (int) $messages->max('id'), now()->addHours(24));
        } elseif ($afterId > 0) {
            Cache::put("chat_last_id_{$channel}", $afterId, now()->addHours(24));
        }

        $isViewerStaff = (bool) (
            $user->isStaff()
            || $user->canModerate()
            || $user->isModerator()
            || $user->isAdmin()
            || $user->isOwner()
            || in_array($user->role, ['moderator', 'admin', 'owner'], true)
        );

        $allHeartUserIds = $messages->flatMap(function (ChatMessage $m) {
            return is_array($m->hearts_users) ? $m->hearts_users : [];
        })->filter()->unique()->values();

        $heartUserNames = $allHeartUserIds->isNotEmpty()
            ? Character::whereIn('id', $allHeartUserIds)->pluck('name', 'id')->all()
            : [];

        $formatted = $messages->map(function (ChatMessage $msg) use ($character, $isViewerStaff, $heartUserNames) {
            $char = $msg->character;
            $authorUser = $char?->user;
            $gang = $char?->gang;

            $isOwner = (bool) ($authorUser?->isOwner());
            $isAdmin = (bool) ($authorUser?->isAdmin());
            $isMod = (bool) ($authorUser?->isModerator() || $authorUser?->canModerate());

            $staffRole = $isOwner ? 'Eigenaar' : ($isAdmin ? 'Directie' : ($isMod ? 'Moderator' : null));
            $staffBadgeColor = $isOwner ? 'amber' : ($isAdmin ? 'purple' : ($isMod ? 'blue' : null));
            $vipBadge = $char?->vip_badge;

            $heartUserIds = is_array($msg->hearts_users) ? $msg->hearts_users : [];
            $heartNames = [];
            foreach ($heartUserIds as $uId) {
                if (isset($heartUserNames[$uId])) {
                    $heartNames[] = $heartUserNames[$uId];
                }
            }

            $isGangLeaderOrCoLeader = false;
            if ($character && str_starts_with($msg->channel, 'gang_')) {
                $gangChannelId = (int) str_replace('gang_', '', $msg->channel);
                if ((int) $character->gang_id === $gangChannelId && in_array($character->gang_role, ['leader', 'co_leader'], true)) {
                    $isGangLeaderOrCoLeader = true;
                }
            }

            return [
                'id' => (int) $msg->id,
                'character_id' => (int) $msg->character_id,
                'name' => $char?->name ?? 'Onbekende Inmate',
                'avatar' => $char?->avatar_url ?? asset('characters/avatar-default.png'),
                'level' => (int) ($char?->level ?? 1),
                'respect' => (int) ($char?->respect ?? 0),
                'is_owner' => $isOwner,
                'is_admin' => $isAdmin,
                'is_moderator' => $isMod,
                'is_staff' => (bool) ($authorUser?->isStaff() || $isMod),
                'staff_role' => $staffRole,
                'staff_badge_color' => $staffBadgeColor,
                'is_vip' => (bool) ($char?->isVip()),
                'vip_tier' => (int) ($char?->getSupporterTier() ?? 0),
                'vip_badge_label' => $vipBadge['short_label'] ?? null,
                'vip_badge_full' => $vipBadge['label'] ?? null,
                'vip_badge_icon' => $vipBadge['icon'] ?? 'fa-crown',
                'vip_badge_class' => $vipBadge['class'] ?? null,
                'name_color' => $char?->getCustomNameColor(),
                'name_gradient' => $char?->getCustomNameGradient() ?? 'none',
                'name_glow' => $char?->getCustomNameGlow() ?? 'none',
                'has_sparkle' => (bool) ($char?->isSparkleActive()),
                'name_classes' => $char ? $char->getNameCssClasses('') : '',
                'name_style' => $char ? $char->getNameInlineStyle() : '',
                'gang_id' => $gang?->id,
                'gang_url' => $gang ? route('gangs.show', $gang->id) : null,
                'gang_tag' => $gang?->tag ?? null,
                'gang_color' => $gang?->color ?? 'purple',
                'message' => $msg->message,
                'time' => $msg->created_at->format('H:i'),
                'is_me' => (int) $msg->character_id === (int) $character->id,
                'reply_to_id' => $msg->reply_to_id ? (int) $msg->reply_to_id : null,
                'reply_to_name' => $msg->reply_to_name,
                'reply_to_snippet' => $msg->reply_to_snippet,
                'hearts_count' => (int) ($msg->hearts_count ?? 0),
                'has_hearted' => in_array($character->id, $heartUserIds, true),
                'hearts_users' => array_values($heartUserIds),
                'hearts_names' => $heartNames,
                'hearts_text' => implode(', ', $heartNames),
                'can_delete' => (bool) ($msg->character_id === $character->id || $isViewerStaff || $isGangLeaderOrCoLeader),
            ];
        });

        $deletedIds = array_values(Cache::get("chat_deleted_ids_{$channel}", []));

        return response()->json([
            'success' => true,
            'messages' => $formatted,
            'deleted_ids' => $deletedIds,
            'last_id' => $messages->last()?->id ?? $afterId,
        ]);
    }

    /**
     * Validate that message does not contain disallowed links.
     * Staff members can post links without restriction.
     * Regular players are only permitted discord invites.
     */
    protected function validateChatLinks(string $message): ?JsonResponse
    {
        /** @var User|null $user */
        $user = Auth::user();
        if ($user && ($user->isStaff() || $user->isAdmin() || $user->isOwner() || $user->isModerator())) {
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
        $character = $user?->character;

        if (! $character) {
            return response()->json(['success' => false, 'message' => 'Geen personage gevonden.'], 403);
        }

        if ($character->isImprisoned()) {
            return response()->json([
                'success' => false,
                'message' => 'Vanuit de isoleercel kun je niet meepraten in de recreatieruimte!',
            ], 422);
        }

        if ($character->isComatose()) {
            return response()->json([
                'success' => false,
                'message' => 'Je verkeert in Code Blauw (Hartstilstand)! Dien eerst een '.Character::REVIVE_SERUM_NAME.' toe.',
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

        $channel = (string) $request->input('channel', 'global');
        if ($channel !== 'global' && ! preg_match('/^gang_\d+$/', $channel)) {
            return response()->json([
                'success' => false,
                'message' => 'Ongeldig chatkanaal.',
            ], 422);
        }

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
            $targetMsg = ChatMessage::with('character')
                ->where('channel', $channel)
                ->find($replyToId);
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

        Cache::put("chat_last_id_{$channel}", (int) $chatMsg->id, now()->addHours(24));

        $gang = $character->gang;

        $isOwner = (bool) ($user->isOwner());
        $isAdmin = (bool) ($user->isAdmin());
        $isMod = (bool) ($user->isModerator() || $user->canModerate());

        $staffRole = $isOwner ? 'Eigenaar' : ($isAdmin ? 'Directie' : ($isMod ? 'Moderator' : null));
        $staffBadgeColor = $isOwner ? 'amber' : ($isAdmin ? 'purple' : ($isMod ? 'blue' : null));
        $vipBadge = $character->vip_badge;

        return response()->json([
            'success' => true,
            'message' => [
                'id' => (int) $chatMsg->id,
                'character_id' => (int) $character->id,
                'name' => $character->name,
                'avatar' => $character->avatar_url,
                'level' => (int) $character->level,
                'respect' => (int) ($character->respect ?? 0),
                'is_owner' => $isOwner,
                'is_admin' => $isAdmin,
                'is_moderator' => $isMod,
                'is_staff' => (bool) ($user->isStaff() || $isMod),
                'staff_role' => $staffRole,
                'staff_badge_color' => $staffBadgeColor,
                'is_vip' => (bool) ($character->isVip()),
                'vip_tier' => (int) ($character->getSupporterTier()),
                'vip_badge_label' => $vipBadge['short_label'] ?? null,
                'vip_badge_full' => $vipBadge['label'] ?? null,
                'vip_badge_icon' => $vipBadge['icon'] ?? 'fa-crown',
                'vip_badge_class' => $vipBadge['class'] ?? null,
                'name_color' => $character->getCustomNameColor(),
                'name_gradient' => $character->getCustomNameGradient(),
                'name_glow' => $character->getCustomNameGlow(),
                'has_sparkle' => (bool) ($character->isSparkleActive()),
                'name_classes' => $character->getNameCssClasses(''),
                'name_style' => $character->getNameInlineStyle(),
                'gang_tag' => $gang?->tag ?? null,
                'gang_color' => $gang?->color ?? 'purple',
                'message' => $chatMsg->message,
                'time' => $chatMsg->created_at->format('H:i'),
                'is_me' => true,
                'reply_to_id' => $chatMsg->reply_to_id ? (int) $chatMsg->reply_to_id : null,
                'reply_to_name' => $chatMsg->reply_to_name,
                'reply_to_snippet' => $chatMsg->reply_to_snippet,
                'hearts_count' => 0,
                'has_hearted' => false,
                'hearts_users' => [],
                'hearts_names' => [],
                'hearts_text' => '',
                'can_delete' => (bool) ($chatMsg->character_id === $character->id || $user?->isStaff() || $user?->canModerate() || $user?->isModerator() || $user?->isAdmin() || $user?->isOwner()),
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
        $character = $user?->character;

        if (! $character) {
            return response()->json(['success' => false, 'message' => 'Geen personage gevonden.', 'conversations' => []], 403);
        }

        // 1. Pinned Kliniek Directie Bot Conversation
        $botLastMessage = DirectMessage::whereNull('sender_id')
            ->where('recipient_id', $character->id)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
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

            $unreadCounts = DirectMessage::where('recipient_id', $character->id)
                ->whereIn('sender_id', $partnerIds)
                ->where('is_read', false)
                ->selectRaw('sender_id, count(*) as total')
                ->groupBy('sender_id')
                ->pluck('total', 'sender_id');

            $inmateConversations = [];

            foreach ($partnerIds as $pId) {
                $partner = $partners->get($pId);
                if (! $partner) {
                    continue;
                }

                $lastMsg = DirectMessage::where(function ($outer) use ($character, $pId) {
                    $outer->where(function ($q) use ($character, $pId) {
                        $q->where('sender_id', $character->id)->where('recipient_id', $pId);
                    })->orWhere(function ($q) use ($character, $pId) {
                        $q->where('sender_id', $pId)->where('recipient_id', $character->id);
                    });
                })
                    ->latest('id')
                    ->first();

                if (! $lastMsg) {
                    continue;
                }

                $unread = (int) ($unreadCounts->get($pId, 0));

                $isOnline = $partner->isOnline();

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
        $character = $user?->character;

        if (! $character) {
            return response()->json(['success' => false, 'message' => 'Geen personage gevonden.'], 403);
        }

        $afterId = (int) $request->query('after_id', 0);

        if ($partnerId === 0) {
            // Kliniek Directie Bot messages (ordered chronologically so backfilled historical messages stay in order)
            $query = DirectMessage::whereNull('sender_id')
                ->where('recipient_id', $character->id);

            if ($afterId > 0) {
                $messages = $query->where('id', '>', $afterId)
                    ->orderBy('created_at', 'asc')
                    ->orderBy('id', 'asc')
                    ->get();
            } else {
                $messages = $query->orderByDesc('created_at')
                    ->orderByDesc('id')
                    ->take(40)
                    ->get()
                    ->reverse()
                    ->values();
            }

            // Mark as read
            DirectMessage::whereNull('sender_id')
                ->where('recipient_id', $character->id)
                ->where('is_read', false)
                ->update(['is_read' => true, 'read_at' => now()]);

            Cache::forget("dm_unread_count_{$character->id}");

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

            $query = DirectMessage::with('sender')
                ->where(function ($outer) use ($character, $partnerId) {
                    $outer->where(function ($q) use ($character, $partnerId) {
                        $q->where('sender_id', $character->id)->where('recipient_id', $partnerId);
                    })->orWhere(function ($q) use ($character, $partnerId) {
                        $q->where('sender_id', $partnerId)->where('recipient_id', $character->id);
                    });
                });

            if ($afterId > 0) {
                $messages = $query->where('id', '>', $afterId)->orderBy('id', 'asc')->get();
            } else {
                $messages = $query->orderBy('id', 'desc')->take(50)->get()->reverse()->values();
            }

            // Mark incoming as read
            DirectMessage::where('sender_id', $partnerId)
                ->where('recipient_id', $character->id)
                ->where('is_read', false)
                ->update(['is_read' => true, 'read_at' => now()]);

            Cache::forget("dm_unread_count_{$character->id}");

            $isOnline = $partner->isOnline();

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

        $allHeartUserIds = $messages->flatMap(function (DirectMessage $m) {
            return is_array($m->hearts_users) ? $m->hearts_users : [];
        })->filter()->unique()->values();

        $heartUserNames = $allHeartUserIds->isNotEmpty()
            ? Character::whereIn('id', $allHeartUserIds)->pluck('name', 'id')->all()
            : [];

        $formatted = $messages->map(function (DirectMessage $msg) use ($character, $heartUserNames) {
            $senderName = $msg->sender_id === null ? '🏥 Kliniek Directie' : ($msg->sender?->name ?? 'Patiënt');
            $senderAvatar = $msg->sender_id === null
                ? null
                : ($msg->sender?->avatar_url ?? asset('assets/characters/avatar_male_1.jpg'));

            $heartUserIds = is_array($msg->hearts_users) ? $msg->hearts_users : [];
            $heartNames = [];
            foreach ($heartUserIds as $uId) {
                if (isset($heartUserNames[$uId])) {
                    $heartNames[] = $heartUserNames[$uId];
                }
            }

            return [
                'id' => (int) $msg->id,
                'sender_id' => $msg->sender_id !== null ? (int) $msg->sender_id : null,
                'sender_name' => $senderName,
                'sender_avatar' => $senderAvatar,
                'is_bot' => $msg->sender_id === null,
                'recipient_id' => (int) $msg->recipient_id,
                'message' => $msg->message,
                'type' => $msg->type,
                'metadata' => $msg->metadata,
                'time' => $msg->created_at->isToday() ? $msg->created_at->format('H:i') : $msg->created_at->format('d-m H:i'),
                'date' => $msg->created_at->format('d-m-Y'),
                'created_at' => $msg->created_at->format('d-m-Y H:i'),
                'timestamp' => $msg->created_at->timestamp,
                'is_me' => (int) $msg->sender_id === (int) $character->id,
                'is_read' => (bool) $msg->is_read,
                'read_at' => $msg->read_at ? $msg->read_at->format('H:i') : null,
                'reply_to_id' => $msg->reply_to_id ? (int) $msg->reply_to_id : null,
                'reply_to_name' => $msg->reply_to_name,
                'reply_to_snippet' => $msg->reply_to_snippet,
                'hearts_count' => (int) ($msg->hearts_count ?? 0),
                'has_hearted' => in_array($character->id, $heartUserIds, true),
                'hearts_users' => array_values($heartUserIds),
                'hearts_names' => $heartNames,
                'hearts_text' => implode(', ', $heartNames),
                'can_delete' => true,
            ];
        });

        $convKey = min($character->id, (int) $partnerId).'_'.max($character->id, (int) $partnerId);
        $deletedIds = array_values(Cache::get("dm_deleted_ids_{$convKey}", []));

        return response()->json([
            'success' => true,
            'partner' => $partnerData,
            'messages' => $formatted,
            'deleted_ids' => $deletedIds,
        ]);
    }

    /**
     * Send direct message to an inmate.
     */
    public function sendDirect(Request $request, int $partnerId): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user?->character;

        if (! $character) {
            return response()->json(['success' => false, 'message' => 'Geen personage gevonden.'], 403);
        }

        if ($partnerId === 0) {
            return response()->json([
                'success' => false,
                'message' => 'Je kunt niet antwoorden op officiële berichten van de Kliniek Directie.',
            ], 422);
        }

        if ($character->isImprisoned()) {
            return response()->json([
                'success' => false,
                'message' => 'In de isoleercel kun je geen privégesprekken voeren!',
            ], 422);
        }

        if ($character->isComatose()) {
            return response()->json([
                'success' => false,
                'message' => 'Je verkeert in Code Blauw (Hartstilstand)! Dien eerst een '.Character::REVIVE_SERUM_NAME.' toe.',
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
            $targetDm = DirectMessage::with('sender')
                ->where(function ($outer) use ($character, $partner) {
                    $outer->where(function ($q) use ($character, $partner) {
                        $q->where('sender_id', $character->id)->where('recipient_id', $partner->id);
                    })->orWhere(function ($q) use ($character, $partner) {
                        $q->where('sender_id', $partner->id)->where('recipient_id', $character->id);
                    });
                })
                ->find($replyToId);

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

        Cache::forget("dm_unread_count_{$partner->id}");

        return response()->json([
            'success' => true,
            'message' => [
                'id' => (int) $msg->id,
                'sender_id' => (int) $character->id,
                'recipient_id' => (int) $partner->id,
                'message' => $msg->message,
                'type' => 'chat',
                'metadata' => null,
                'time' => $msg->created_at->format('H:i'),
                'is_me' => true,
                'is_read' => false,
                'read_at' => null,
                'reply_to_id' => $msg->reply_to_id ? (int) $msg->reply_to_id : null,
                'reply_to_name' => $msg->reply_to_name,
                'reply_to_snippet' => $msg->reply_to_snippet,
                'hearts_count' => 0,
                'has_hearted' => false,
                'hearts_users' => [],
                'hearts_names' => [],
                'hearts_text' => '',
                'can_delete' => true,
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

        // Direct real-time unread count (indexed, instant)
        $unreadCount = DirectMessage::where('recipient_id', $character->id)
            ->where('is_read', false)
            ->count();

        // If no unread DMs exist and no specific after_dm_id requested, return immediately
        if ($unreadCount === 0 && $afterDmId === 0) {
            return response()->json([
                'success' => true,
                'unread_dms' => 0,
                'new_dm' => null,
            ]);
        }

        // Find latest unread DM for this character
        $query = DirectMessage::with(['sender'])
            ->where('recipient_id', $character->id)
            ->where('is_read', false);

        if ($afterDmId > 0) {
            $query->where('id', '>', $afterDmId);
        }

        /** @var DirectMessage|null $latestDm */
        $latestDm = $query->orderBy('id', 'desc')->first();

        $dmData = null;
        if ($latestDm) {
            $sender = $latestDm->sender;
            $dmData = [
                'id' => (int) $latestDm->id,
                'sender_id' => $latestDm->sender_id !== null ? (int) $latestDm->sender_id : 0,
                'sender_name' => $sender ? $sender->name : '🏥 Kliniek Directie',
                'sender_avatar' => $sender ? $sender->avatar_url : null,
                'message' => Str::limit($latestDm->message, 80),
                'full_message' => $latestDm->message,
                'type' => $latestDm->type ?? 'chat',
                'metadata' => $latestDm->metadata,
                'time' => $latestDm->created_at->format('H:i'),
                'timestamp' => $latestDm->created_at->timestamp,
                'is_bot' => $latestDm->sender_id === null,
                'reply_to_id' => $latestDm->reply_to_id ? (int) $latestDm->reply_to_id : null,
                'reply_to_name' => $latestDm->reply_to_name,
                'reply_to_snippet' => $latestDm->reply_to_snippet,
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
        $channel = (string) $request->query('channel', '');
        $gangOnly = $request->boolean('gang_only') || $channel === 'gang' || str_starts_with($channel, 'gang_');

        $queryBuilder = Character::with(['gang', 'user'])
            ->where('id', '!=', $character->id);

        if ($gangOnly) {
            if (! $character->gang_id) {
                return response()->json(['success' => true, 'players' => []]);
            }
            $queryBuilder->where('gang_id', $character->gang_id);
        }

        if ($query === '') {
            $players = $queryBuilder
                ->when(! $gangOnly, fn ($q) => $q->whereNotNull('last_active_at')->orderBy('last_active_at', 'desc'))
                ->when($gangOnly, fn ($q) => $q->orderBy('name', 'asc'))
                ->take($gangOnly ? 25 : 8)
                ->get();
        } else {
            $players = $queryBuilder
                ->where('name', 'like', "%{$query}%")
                ->when(! $gangOnly, fn ($q) => $q->orderByRaw('CASE WHEN last_active_at >= ? THEN 0 ELSE 1 END', [now()->subMinutes(15)]))
                ->orderBy('level', 'desc')
                ->take($gangOnly ? 25 : 8)
                ->get();
        }

        $formatted = $players->map(function (Character $p) {
            $user = $p->user;
            $isOwner = (bool) ($user?->isOwner());
            $isAdmin = (bool) ($user?->isAdmin());
            $isMod = (bool) ($user?->isModerator() || $user?->canModerate());
            $staffRole = $isOwner ? 'Eigenaar' : ($isAdmin ? 'Directie' : ($isMod ? 'Moderator' : null));

            return [
                'id' => (int) $p->id,
                'name' => $p->name,
                'avatar' => $p->avatar_url,
                'level' => (int) $p->level,
                'respect' => (int) $p->respect,
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
        $character = $user?->character;

        if (! $character) {
            return response()->json(['success' => false, 'message' => 'Geen personage gevonden.'], 403);
        }

        if ($character->isComatose()) {
            return response()->json([
                'success' => false,
                'message' => 'Je verkeert in Code Blauw (Hartstilstand)! Dien eerst een '.Character::REVIVE_SERUM_NAME.' toe.',
            ], 422);
        }

        if ($character->isImprisoned()) {
            return response()->json([
                'success' => false,
                'message' => 'Vanuit de isoleercel kun je niet reageren op openbare berichten!',
            ], 422);
        }

        return DB::transaction(function () use ($id, $character): JsonResponse {
            $msg = ChatMessage::where('id', $id)->lockForUpdate()->firstOrFail();

            if (str_starts_with($msg->channel, 'gang_')) {
                $gangId = (int) str_replace('gang_', '', $msg->channel);
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
                        'message' => 'Deze bende heeft de gang chat nog niet ontgrendeld.',
                    ], 403);
                }
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

            $heartNames = $msg->getHeartNames();

            return response()->json([
                'success' => true,
                'hearts_count' => $msg->hearts_count,
                'has_hearted' => $hasHearted,
                'hearts_users' => $hearts,
                'hearts_names' => $heartNames,
                'hearts_text' => implode(', ', $heartNames),
            ]);
        });
    }

    /**
     * Toggle a purple heart (💜) on a direct message.
     */
    public function toggleDirectHeart(Request $request, int $id): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user?->character;

        if (! $character) {
            return response()->json(['success' => false, 'message' => 'Geen personage gevonden.'], 403);
        }

        if ($character->isComatose()) {
            return response()->json([
                'success' => false,
                'message' => 'Je verkeert in Code Blauw (Hartstilstand)! Dien eerst een '.Character::REVIVE_SERUM_NAME.' toe.',
            ], 422);
        }

        if ($character->isImprisoned()) {
            return response()->json([
                'success' => false,
                'message' => 'In de isoleercel kun je niet reageren op privéberichten!',
            ], 422);
        }

        return DB::transaction(function () use ($id, $character): JsonResponse {
            $msg = DirectMessage::where('id', $id)->lockForUpdate()->firstOrFail();

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

            $heartNames = $msg->getHeartNames();

            return response()->json([
                'success' => true,
                'hearts_count' => $msg->hearts_count,
                'has_hearted' => $hasHearted,
                'hearts_users' => $hearts,
                'hearts_names' => $heartNames,
                'hearts_text' => implode(', ', $heartNames),
            ]);
        });
    }

    /**
     * Delete a public/gang chat message (Moderators/Staff only).
     */
    public function deletePublicMessage(Request $request, int $id): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user?->character;

        $isModeratorOrStaff = (bool) (
            $user?->isStaff()
            || $user?->canModerate()
            || $user?->isModerator()
            || $user?->isAdmin()
            || $user?->isOwner()
            || in_array($user?->role, ['moderator', 'admin', 'owner'], true)
        );

        if (! $character && ! $isModeratorOrStaff) {
            return response()->json(['success' => false, 'message' => 'Geen personage gevonden.'], 403);
        }

        $msg = ChatMessage::findOrFail($id);

        $isAuthor = $character && ((int) $msg->character_id === (int) $character->id);

        $isGangLeaderOrCoLeader = false;
        if ($character && str_starts_with($msg->channel, 'gang_')) {
            $gangId = (int) str_replace('gang_', '', $msg->channel);
            if ((int) $character->gang_id === $gangId && in_array($character->gang_role, ['leader', 'co_leader'], true)) {
                $isGangLeaderOrCoLeader = true;
            }
        }

        if (! ($isModeratorOrStaff || $isAuthor || $isGangLeaderOrCoLeader)) {
            return response()->json([
                'success' => false,
                'message' => 'Alleen moderators, bendeleiders of de auteur kunnen chatberichten verwijderen.',
            ], 403);
        }

        if (str_starts_with($msg->channel, 'gang_') && ! $isModeratorOrStaff && ! $isGangLeaderOrCoLeader) {
            $gangId = (int) str_replace('gang_', '', $msg->channel);
            if (! $character?->gang_id || (int) $character->gang_id !== $gangId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Je hebt geen toegang tot deze bende chat.',
                ], 403);
            }
        }

        $channel = $msg->channel;
        $deleted = Cache::get("chat_deleted_ids_{$channel}", []);
        $deleted[] = (int) $id;
        $deleted = array_values(array_slice(array_unique(array_map('intval', $deleted)), -50));
        Cache::put("chat_deleted_ids_{$channel}", $deleted, now()->addHours(2));

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
        $character = $user?->character;

        $isModeratorOrStaff = (bool) (
            $user?->isStaff()
            || $user?->canModerate()
            || $user?->isModerator()
            || $user?->isAdmin()
            || $user?->isOwner()
            || in_array($user?->role, ['moderator', 'admin', 'owner'], true)
        );

        if (! $character && ! $isModeratorOrStaff) {
            return response()->json(['success' => false, 'message' => 'Geen personage gevonden.'], 403);
        }

        $msg = DirectMessage::findOrFail($id);

        $isSender = $character && ((int) $msg->sender_id === (int) $character->id);
        $isRecipient = $character && ((int) $msg->recipient_id === (int) $character->id);

        if (! ($isSender || $isRecipient || $isModeratorOrStaff)) {
            return response()->json([
                'success' => false,
                'message' => 'Geen bevoegdheid om dit bericht te verwijderen.',
            ], 403);
        }

        $senderId = (int) ($msg->sender_id ?? 0);
        $recipientId = (int) $msg->recipient_id;
        $convKey = min($senderId, $recipientId).'_'.max($senderId, $recipientId);
        $deleted = Cache::get("dm_deleted_ids_{$convKey}", []);
        $deleted[] = (int) $id;
        $deleted = array_values(array_slice(array_unique(array_map('intval', $deleted)), -50));
        Cache::put("dm_deleted_ids_{$convKey}", $deleted, now()->addHours(2));

        Cache::forget("dm_unread_count_{$recipientId}");

        $msg->delete();

        return response()->json([
            'success' => true,
            'message' => 'Bericht verwijderd.',
        ]);
    }
}
