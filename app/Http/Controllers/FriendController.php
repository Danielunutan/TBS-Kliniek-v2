<?php

namespace App\Http\Controllers;

use App\Models\Character;
use App\Models\Friend;
use App\Models\GameMessage;
use App\Services\EnergyService;
use App\Services\LevelService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class FriendController extends Controller
{
    public function __construct(
        protected EnergyService $energyService,
        protected LevelService $levelService,
    ) {}

    /**
     * Show friends list, BFF showcase, and mass message form.
     */
    public function index(): View
    {
        $character = Auth::user()->character;
        $this->energyService->refresh($character);

        $friends = Friend::with('friend')
            ->where('character_id', $character->id)
            ->where('status', 'accepted')
            ->get();

        $bff = $friends->firstWhere('is_bff', true);

        // Inbound pending friend requests (sent to current character)
        $pendingRequests = Friend::with('character')
            ->where('friend_id', $character->id)
            ->where('status', 'pending')
            ->get();

        // Outbound pending requests (sent by current character)
        $sentRequests = Friend::with('friend')
            ->where('character_id', $character->id)
            ->where('status', 'pending')
            ->get();

        // All patients available to add as friend
        $existingFriendIds = Friend::where('character_id', $character->id)->pluck('friend_id')->push($character->id);
        $availablePatients = Character::whereNotIn('id', $existingFriendIds)
            ->orderBy('name')
            ->take(50)
            ->get();

        // 24-hour mass message cooldown check
        $canMassMessage = true;
        $massMessageCooldown = 0;
        if ($character->last_friends_mass_message_at) {
            $diffHours = $character->last_friends_mass_message_at->diffInHours(now());
            if ($diffHours < 24) {
                $canMassMessage = false;
                $massMessageCooldown = 24 - $diffHours;
            }
        }

        // Generate custom TBS captcha challenge for giving respect
        $numA = rand(3, 9);
        $numB = rand(2, 8);
        session(['friend_captcha_ans' => $numA + $numB]);

        $topRankings = Character::orderByDesc('respect')->take(10)->get();

        return view('game.friends.index', compact(
            'character',
            'friends',
            'bff',
            'pendingRequests',
            'sentRequests',
            'availablePatients',
            'canMassMessage',
            'massMessageCooldown',
            'numA',
            'numB',
            'topRankings'
        ));
    }

    /**
     * Send a friend request to a patient.
     */
    public function add(Request $request): RedirectResponse
    {
        $character = Auth::user()->character;

        $validated = $request->validate([
            'friend_name' => ['required', 'string', 'exists:characters,name'],
        ]);

        $target = Character::where('name', $validated['friend_name'])->firstOrFail();

        if ($target->id === $character->id) {
            return back()->with('error', 'Je kunt jezelf niet als vriend toevoegen!');
        }

        $existing = Friend::where('character_id', $character->id)
            ->where('friend_id', $target->id)
            ->first();

        if ($existing) {
            if ($existing->status === 'accepted') {
                return back()->with('info', "{$target->name} staat al in je vriendenlijst.");
            }

            return back()->with('info', "Je hebt al een openstaand verzoek verzonden naar {$target->name}.");
        }

        // Check if target already sent a pending request to character
        $reversePending = Friend::where('character_id', $target->id)
            ->where('friend_id', $character->id)
            ->where('status', 'pending')
            ->first();

        if ($reversePending) {
            $reversePending->status = 'accepted';
            $reversePending->save();

            Friend::firstOrCreate([
                'character_id' => $character->id,
                'friend_id' => $target->id,
            ], [
                'is_bff' => false,
                'status' => 'accepted',
            ]);

            GameMessage::create([
                'recipient_id' => $target->id,
                'sender_id' => $character->id,
                'sender_name' => $character->name,
                'subject' => "🎉 {$character->name} heeft je vriendschapsverzoek geaccepteerd!",
                'content' => "Patiënt {$target->name},\n\nJullie zijn nu officieel vrienden in de TBS Kliniek!",
                'type' => 'player',
            ]);

            return back()->with('success', "{$target->name} had jou ook al een verzoek gestuurd! Jullie zijn nu vrienden!");
        }

        Friend::create([
            'character_id' => $character->id,
            'friend_id' => $target->id,
            'is_bff' => false,
            'status' => 'pending',
        ]);

        GameMessage::create([
            'recipient_id' => $target->id,
            'sender_id' => $character->id,
            'sender_name' => $character->name,
            'subject' => "🤝 Vriendschapsverzoek van {$character->name}",
            'content' => "Patiënt {$target->name},\n\n{$character->name} heeft je een vriendschapsverzoek gestuurd in de TBS Kliniek! Bekijk je berichten of vriendenpagina om dit verzoek te accepteren.",
            'type' => 'friend_request',
        ]);

        return back()->with('success', "Vriendschapsverzoek succesvol verstuurd naar {$target->name} via een officieel kliniekbericht!");
    }

    /**
     * Accept an inbound friend request.
     */
    public function accept(Friend $friend): RedirectResponse
    {
        $character = Auth::user()->character;

        if ($friend->friend_id !== $character->id || $friend->status !== 'pending') {
            return back()->with('error', 'Ongeldig of reeds behandeld vriendschapsverzoek.');
        }

        $sender = $friend->character;

        $friend->status = 'accepted';
        $friend->save();

        // Create reciprocal friendship record
        Friend::updateOrCreate([
            'character_id' => $character->id,
            'friend_id' => $sender->id,
        ], [
            'is_bff' => false,
            'status' => 'accepted',
        ]);

        // Send confirmation message to requester
        GameMessage::create([
            'recipient_id' => $sender->id,
            'sender_id' => $character->id,
            'sender_name' => $character->name,
            'subject' => "🎉 {$character->name} heeft je vriendschapsverzoek geaccepteerd!",
            'content' => "Patiënt {$sender->name},\n\n{$character->name} heeft je vriendschapsverzoek zojuist geaccepteerd! Jullie zijn nu vrienden in de TBS Kliniek en kunnen elkaar respect geven.",
            'type' => 'player',
        ]);

        // Mark friend_request message as read
        GameMessage::where('recipient_id', $character->id)
            ->where('sender_id', $sender->id)
            ->where('type', 'friend_request')
            ->update(['is_read' => true]);

        return back()->with('success', "Je bent nu officieel vrienden met {$sender->name}!");
    }

    /**
     * Reject an inbound friend request.
     */
    public function reject(Friend $friend): RedirectResponse
    {
        $character = Auth::user()->character;

        if ($friend->friend_id !== $character->id) {
            return back()->with('error', 'Geen bevoegdheid.');
        }

        $senderName = $friend->character?->name ?? 'Onbekend';
        $senderId = $friend->character_id;

        $friend->delete();

        GameMessage::where('recipient_id', $character->id)
            ->where('sender_id', $senderId)
            ->where('type', 'friend_request')
            ->update(['is_read' => true]);

        return back()->with('info', "Vriendschapsverzoek van {$senderName} afgewezen.");
    }

    /**
     * Toggle BFF (Best Friend Forever / Beste Medepatiënt).
     */
    public function toggleBff(Friend $friend): RedirectResponse
    {
        $character = Auth::user()->character;

        if ($friend->character_id !== $character->id) {
            return back()->with('error', 'Geen bevoegdheid.');
        }

        if (! $friend->is_bff) {
            // Unset any previous BFF
            Friend::where('character_id', $character->id)->update(['is_bff' => false]);
            $friend->is_bff = true;
            $friend->save();

            return back()->with('success', "💖 {$friend->friend->name} is nu ingesteld als jouw officiële BFF!");
        }

        $friend->is_bff = false;
        $friend->save();

        return back()->with('info', "BFF status van {$friend->friend->name} verwijderd.");
    }

    /**
     * Remove a friend.
     */
    public function remove(Friend $friend): RedirectResponse
    {
        $character = Auth::user()->character;

        if ($friend->character_id !== $character->id) {
            return back()->with('error', 'Geen bevoegdheid.');
        }

        $targetId = $friend->friend_id;
        $name = $friend->friend->name ?? 'Patiënt';
        $friend->delete();

        Friend::where('character_id', $targetId)
            ->where('friend_id', $character->id)
            ->delete();

        return back()->with('success', "{$name} is verwijderd uit je vriendenlijst.");
    }

    /**
     * Give daily respect to friend with TBS captcha verification.
     */
    public function giveRespect(Request $request, Friend $friend): RedirectResponse
    {
        $character = Auth::user()->character;

        if ($friend->character_id !== $character->id) {
            return back()->with('error', 'Geen bevoegdheid.');
        }

        // Verify custom captcha
        $expected = session('friend_captcha_ans');
        $given = (int) $request->input('captcha_answer');

        if ($expected && $given !== $expected) {
            return back()->with('error', 'Foutief antwoord op de beveiligingsverificatie! Probeer het opnieuw.');
        }

        if ($friend->last_respected_at && $friend->last_respected_at->diffInHours(now()) < 24) {
            $hoursLeft = 24 - $friend->last_respected_at->diffInHours(now());

            return back()->with('error', "Je hebt {$friend->friend->name} vandaag al respect gegeven! Wacht nog {$hoursLeft} uur.");
        }

        $target = $friend->friend;
        $respectBonus = $friend->is_bff ? 2 : 1;
        $target->respect += $respectBonus;
        $target->save();

        $friend->last_respected_at = now();
        $friend->save();

        $this->levelService->addXp($character, 50);

        GameMessage::create([
            'recipient_id' => $target->id,
            'sender_id' => $character->id,
            'sender_name' => $character->name,
            'subject' => "🔥 Respect ontvangen van {$character->name}!",
            'content' => "Beste {$target->name},\n\nJouw vriend {$character->name} heeft je zojuist +{$respectBonus} Respect gegeven!".($friend->is_bff ? ' (BFF Bonus!)' : ''),
            'type' => 'reward',
        ]);

        return back()->with('success', "🔥 Respect toegekend aan {$target->name}! (+{$respectBonus} Respect voor hen, +50 XP voor jou).");
    }

    /**
     * Send mass broadcast message to all friends (1x per 24 hours).
     */
    public function massMessage(Request $request): RedirectResponse
    {
        $character = Auth::user()->character;

        if ($character->last_friends_mass_message_at && $character->last_friends_mass_message_at->diffInHours(now()) < 24) {
            $hoursLeft = 24 - $character->last_friends_mass_message_at->diffInHours(now());

            return back()->with('error', "⏳ Massabericht naar vrienden kan maar 1x per 24 uur verzonden worden. Wacht nog {$hoursLeft} uur.");
        }

        $validated = $request->validate([
            'subject' => ['required', 'string', 'max:100'],
            'message' => ['required', 'string', 'max:1000'],
        ]);

        $friends = Friend::with('friend')->where('character_id', $character->id)->get();

        if ($friends->isEmpty()) {
            return back()->with('error', 'Je hebt nog geen vrienden in je vriendenlijst om een bericht naar te sturen!');
        }

        foreach ($friends as $f) {
            GameMessage::create([
                'recipient_id' => $f->friend->id,
                'sender_id' => $character->id,
                'sender_name' => $character->name,
                'subject' => "💌 [Vrienden Broadcast] {$validated['subject']}",
                'content' => $validated['message'],
                'type' => 'player',
            ]);
        }

        $character->last_friends_mass_message_at = now();
        $character->save();

        return back()->with('success', "💌 Massabericht succesvol verzonden naar al je {$friends->count()} vrienden!");
    }
}
