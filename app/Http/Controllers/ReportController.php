<?php

namespace App\Http\Controllers;

use App\Models\Character;
use App\Models\ChatMessage;
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
            'reported_type' => 'required|in:character,gang,chat_message',
            'reported_id' => 'required|integer',
            'reason' => 'required|string|max:255',
            'details' => 'nullable|string|max:1000',
        ]);

        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;

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

        // Self-reporting check
        if ($validated['reported_type'] === 'character' && (int) $validated['reported_id'] === $character->id) {
            return response()->json([
                'success' => false,
                'message' => 'Je kunt jezelf niet rapporteren.',
            ], 422);
        }

        // Resolve snapshot of target name
        $targetName = null;
        if ($validated['reported_type'] === 'character') {
            $target = Character::find($validated['reported_id']);
            if (! $target) {
                return response()->json(['success' => false, 'message' => 'Patiënt niet gevonden.'], 404);
            }
            $targetName = $target->name;
        } elseif ($validated['reported_type'] === 'gang') {
            $target = Gang::find($validated['reported_id']);
            if (! $target) {
                return response()->json(['success' => false, 'message' => 'Bende niet gevonden.'], 404);
            }
            $targetName = $target->name;
        } elseif ($validated['reported_type'] === 'chat_message') {
            $target = ChatMessage::with('character')->find($validated['reported_id']);
            if (! $target) {
                return response()->json(['success' => false, 'message' => 'Chatbericht niet gevonden.'], 404);
            }
            $targetName = $target->character?->name ?? 'Inmate #'.$target->character_id;
            // Append message excerpt into details if empty
            if (empty($validated['details'])) {
                $validated['details'] = 'Geraporteerd bericht: "'.$target->message.'"';
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

        Report::create([
            'reporter_id' => $character->id,
            'reported_type' => $validated['reported_type'],
            'reported_id' => $validated['reported_id'],
            'reported_name' => $targetName,
            'reason' => $validated['reason'],
            'details' => $validated['details'],
            'status' => 'pending',
        ]);

        Cache::put($cooldownKey, true, now()->addSeconds(20));

        return response()->json([
            'success' => true,
            'message' => 'Melding succesvol ontvangen! De kliniek leiding / moderatie is op de hoogte gebracht.',
        ]);
    }
}
