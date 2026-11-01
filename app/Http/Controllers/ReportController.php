<?php

namespace App\Http\Controllers;

use App\Models\Character;
use App\Models\ChatMessage;
use App\Models\DirectMessage;
use App\Models\GameNotification;
use App\Models\Gang;
use App\Models\Report;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class ReportController extends Controller
{
    /**
     * Submit a report for a character, gang, or chat message.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'reported_type' => 'required|in:character,gang,chat_message,direct_message',
            'reported_id' => 'required|integer',
            'reason' => 'required|string|max:255',
            'details' => 'nullable|string|max:1000',
        ]);

        /** @var User $user */
        $user = Auth::user();
        $character = $user?->character;

        if (! $character) {
            return response()->json([
                'success' => false,
                'message' => 'Geen actieve patiënt gevonden.',
            ], 403);
        }

        // Anti-spam cooldown (1 report per 20 seconds)
        $cooldownKey = "report_cooldown_char_{$character->id}";
        if (Cache::has($cooldownKey)) {
            return response()->json([
                'success' => false,
                'message' => 'Wacht even met het versturen van nog een melding. De moderatie heeft je eerdere melding ontvangen.',
            ], 429);
        }

        // Resolve snapshot of target name & enforce self-reporting and access checks
        $targetName = null;
        if ($validated['reported_type'] === 'character') {
            $target = Character::find($validated['reported_id']);
            if (! $target) {
                return response()->json(['success' => false, 'message' => 'Patiënt niet gevonden.'], 404);
            }
            if ($target->id === $character->id || $target->user_id === $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Je kunt jezelf niet rapporteren.',
                ], 422);
            }
            $targetName = $target->name;
        } elseif ($validated['reported_type'] === 'gang') {
            $target = Gang::find($validated['reported_id']);
            if (! $target) {
                return response()->json(['success' => false, 'message' => 'Bende niet gevonden.'], 404);
            }
            if ($target->leader_character_id === $character->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Je kunt je eigen bende niet rapporteren.',
                ], 422);
            }
            $targetName = $target->name;
        } elseif ($validated['reported_type'] === 'chat_message') {
            $target = ChatMessage::with('character')->find($validated['reported_id']);
            if (! $target) {
                return response()->json(['success' => false, 'message' => 'Chatbericht niet gevonden.'], 404);
            }
            if ($target->character_id === $character->id || ($target->character && $target->character->user_id === $user->id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Je kunt je eigen chatbericht niet rapporteren.',
                ], 422);
            }
            $targetName = $target->character?->name ?? 'Inmate #'.$target->character_id;
            // Append message excerpt into details if empty
            if (empty($validated['details'])) {
                $validated['details'] = 'Gerapporteerd bericht: "'.$target->message.'"';
            }
        } elseif ($validated['reported_type'] === 'direct_message') {
            $target = DirectMessage::with(['sender', 'recipient'])->find($validated['reported_id']);
            if (! $target) {
                return response()->json(['success' => false, 'message' => 'Privébericht niet gevonden.'], 404);
            }
            if ($target->sender_id !== $character->id && $target->recipient_id !== $character->id && ! $user->isStaff()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Je hebt geen toegang tot dit privébericht.',
                ], 403);
            }
            if ($target->sender_id === $character->id || ($target->sender && $target->sender->user_id === $user->id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Je kunt je eigen privébericht niet rapporteren.',
                ], 422);
            }
            $targetName = $target->sender?->name ?? 'Inmate #'.$target->sender_id;
            // Append message excerpt into details if empty
            if (empty($validated['details'])) {
                $validated['details'] = 'Gerapporteerd privébericht: "'.$target->message.'"';
            }
        }

        // Check for existing pending duplicate
        $existing = Report::where('reporter_id', $character->id)
            ->where('reported_type', $validated['reported_type'])
            ->where('reported_id', $validated['reported_id'])
            ->where('status', 'pending')
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'Je hebt hier al een openstaande melding over ingediend. De moderatie pakt dit zo snel mogelijk op.',
            ], 422);
        }

        $report = Report::create([
            'reporter_id' => $character->id,
            'reported_type' => $validated['reported_type'],
            'reported_id' => $validated['reported_id'],
            'reported_name' => $targetName,
            'reason' => strip_tags($validated['reason']),
            'details' => ! empty($validated['details']) ? strip_tags($validated['details']) : null,
            'status' => 'pending',
        ]);

        // Send instant alert to all online/active staff / moderators
        try {
            $staffUsers = User::whereIn('role', ['admin', 'moderator', 'owner'])
                ->orWhere('is_staff', true)
                ->with('character')
                ->get();

            $targetUrl = $report->getTargetUrl();
            $typeLabel = match ($validated['reported_type']) {
                'character' => 'Patiënt',
                'gang' => 'Bende',
                'chat_message' => 'Chatbericht',
                'direct_message' => 'Privébericht',
                default => 'Melding',
            };

            foreach ($staffUsers as $staffUser) {
                if ($staffUser->character && $staffUser->character->id !== $character->id) {
                    GameNotification::create([
                        'character_id' => $staffUser->character->id,
                        'type' => 'moderation',
                        'icon' => 'fa-flag',
                        'title' => "🚨 Nieuwe Melding: {$targetName}",
                        'message' => "Patiënt {$character->name} heeft {$typeLabel} '{$targetName}' gerapporteerd wegens: {$validated['reason']}.\n\nBekijk direct het dossier: {$targetUrl}",
                    ]);
                }
            }
        } catch (\Throwable $e) {
            // Ignore notification failure so report is always recorded
        }

        Cache::put($cooldownKey, true, now()->addSeconds(20));

        return response()->json([
            'success' => true,
            'message' => 'Melding succesvol ontvangen! De kliniek leiding / moderatie is op de hoogte gebracht.',
        ]);
    }
}
