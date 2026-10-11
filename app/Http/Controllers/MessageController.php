<?php

namespace App\Http\Controllers;

use App\Models\Character;
use App\Models\Friend;
use App\Models\GameMessage;
use App\Services\EnergyService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class MessageController extends Controller
{
    public function __construct(
        protected EnergyService $energyService,
    ) {}

    /**
     * Display messages inbox.
     */
    public function index(Request $request): View
    {
        $character = Auth::user()->character;
        $this->energyService->refresh($character);

        $filter = $request->query('filter', 'all');

        $query = GameMessage::where('recipient_id', $character->id)->latest();

        if ($filter === 'system') {
            $query->whereIn('type', ['system', 'jail', 'level']);
        } elseif ($filter === 'theft') {
            $query->where('type', 'theft');
        } elseif ($filter === 'player') {
            $query->whereIn('type', ['player', 'friend_request']);
        }

        $messages = $query->paginate(12)->withQueryString();
        $unreadCount = GameMessage::where('recipient_id', $character->id)->where('is_read', false)->count();

        // Mark visible messages as read
        GameMessage::where('recipient_id', $character->id)->where('is_read', false)->update(['is_read' => true]);

        // Only accepted friends can receive messages
        $friendIds = Friend::where('character_id', $character->id)
            ->where('status', 'accepted')
            ->pluck('friend_id');

        $otherPlayers = Character::whereIn('id', $friendIds)
            ->orderBy('name')
            ->get(['id', 'name', 'avatar', 'level']);

        $initialRecipient = $request->query('recipient', '');

        return view('game.messages', compact('character', 'messages', 'filter', 'unreadCount', 'otherPlayers', 'initialRecipient'));
    }

    /**
     * Send a private message to another inmate (friends only).
     */
    public function send(Request $request): RedirectResponse
    {
        $character = Auth::user()->character;

        if ($character->isImprisoned()) {
            return back()->with('error', 'Vanuit de isoleercel kun je geen brieven versturen!');
        }

        $validated = $request->validate([
            'recipient_name' => ['required', 'string', 'exists:characters,name'],
            'subject' => ['required', 'string', 'max:100'],
            'content' => ['required', 'string', 'max:1000'],
        ], [
            'recipient_name.exists' => 'Deze patiënt bestaat niet in de kliniek.',
            'subject.required' => 'Vul een onderwerp in.',
            'content.required' => 'Vul een bericht in.',
        ]);

        $recipient = Character::where('name', $validated['recipient_name'])->firstOrFail();

        if ($recipient->id === $character->id) {
            return back()->with('error', 'Je kunt geen brief naar jezelf sturen!');
        }

        // Only allowed to send messages to accepted friends
        if (! $character->isFriendWith($recipient)) {
            return back()->with('error', "Je kunt alleen brieven sturen naar patiënten die op je vriendenlijst staan! Voeg {$recipient->name} eerst toe als vriend via Vrienden.");
        }

        GameMessage::create([
            'recipient_id' => $recipient->id,
            'sender_id' => $character->id,
            'sender_name' => $character->name,
            'subject' => strip_tags($validated['subject']),
            'content' => strip_tags($validated['content']),
            'type' => 'player',
        ]);

        return back()->with('success', "Je brief is discreet bezorgd bij {$recipient->name} via de binnendienst!");
    }

    /**
     * Delete a message.
     */
    public function destroy(GameMessage $message): RedirectResponse
    {
        $character = Auth::user()->character;

        if ($message->recipient_id !== $character->id) {
            abort(403);
        }

        $message->delete();

        return back()->with('success', 'Bericht verwijderd uit je dossier.');
    }
}
