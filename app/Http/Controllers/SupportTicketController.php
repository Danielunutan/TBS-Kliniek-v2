<?php

namespace App\Http\Controllers;

use App\Models\SupportTicket;
use App\Models\SupportTicketMessage;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SupportTicketController extends Controller
{
    /**
     * Show player's tickets overview.
     */
    public function index(): View
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;

        $tickets = SupportTicket::where('user_id', $user->id)
            ->with(['lastMessage', 'character'])
            ->latest('updated_at')
            ->paginate(15);

        $openCount = SupportTicket::where('user_id', $user->id)
            ->where('status', '!=', 'gesloten')
            ->count();

        $answeredCount = SupportTicket::where('user_id', $user->id)
            ->where('status', 'beantwoord')
            ->count();

        return view('game.tickets.index', compact('character', 'tickets', 'openCount', 'answeredCount'));
    }

    /**
     * Show ticket creation form.
     */
    public function create(): View
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;

        return view('game.tickets.create', compact('character'));
    }

    /**
     * Store new support ticket and initial message.
     */
    public function store(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;

        $category = $request->input('category');

        if ($category === 'gang_overstap') {
            $validated = $request->validate([
                'category' => ['required', 'string', 'in:gang_overstap'],
                'priority' => ['nullable', 'string', 'in:laag,normaal,hoog,spoed'],
                'gang_name' => ['required', 'string', 'min:3', 'max:25'],
                'gang_tag' => ['required', 'string', 'min:2', 'max:4'],
                'external_gang_link' => ['required', 'string', 'max:255'],
                'member_names' => ['nullable', 'string', 'max:1500'],
                'proof_details' => ['nullable', 'string', 'max:2500'],
            ], [
                'gang_name.required' => 'Vul de gewenste bendenaam in (min. 3, max. 25 tekens).',
                'gang_tag.required' => 'Vul een bende tag of afkorting in (2 tot 4 tekens).',
                'external_gang_link.required' => 'Vul de link naar jullie bende op de andere website of game in.',
            ]);

            $gangTag = strtoupper(strip_tags($validated['gang_tag']));
            $gangName = strip_tags($validated['gang_name']);
            $subject = "Bende Overstap Bonus: {$gangName} [{$gangTag}]";
            $priority = $validated['priority'] ?? 'normaal';

            $metadata = [
                'gang_name' => $gangName,
                'gang_tag' => $gangTag,
                'external_gang_link' => strip_tags($validated['external_gang_link']),
                'member_names' => ! empty($validated['member_names']) ? strip_tags($validated['member_names']) : null,
                'proof_details' => ! empty($validated['proof_details']) ? strip_tags($validated['proof_details']) : null,
            ];

            $messageContent = "👑 AANVRAAG BENDE OVERSTAP BONUS\n\n"
                ."• Gewenste Bendenaam: {$gangName}\n"
                ."• Bende Tag: [{$gangTag}]\n"
                ."• Link naar bende op andere game: {$metadata['external_gang_link']}\n"
                .(! empty($metadata['member_names']) ? "• Overstappende Leden:\n{$metadata['member_names']}\n\n" : "\n")
                .(! empty($metadata['proof_details']) ? "• Toelichting / Bewijs:\n{$metadata['proof_details']}" : '');
        } else {
            $validated = $request->validate([
                'subject' => ['required', 'string', 'min:3', 'max:120'],
                'category' => ['required', 'string', 'in:bug,vraag,feedback,vip,gang_overstap,overig'],
                'priority' => ['required', 'string', 'in:laag,normaal,hoog,spoed'],
                'message' => ['required', 'string', 'min:5', 'max:4000'],
            ], [
                'subject.required' => 'Vul een duidelijk onderwerp in.',
                'category.required' => 'Kies een passende categorie.',
                'message.required' => 'Geef een duidelijke omschrijving van je vraag of feedback.',
                'message.min' => 'Je bericht moet minimaal 5 tekens bevatten.',
            ]);

            $subject = $validated['subject'];
            $priority = $validated['priority'];
            $metadata = null;
            $messageContent = $validated['message'];
        }

        $ticketNumber = 'TBS-'.strtoupper(Str::random(3)).'-'.rand(100, 999);

        $ticket = SupportTicket::create([
            'user_id' => $user->id,
            'character_id' => $character->id,
            'ticket_number' => $ticketNumber,
            'subject' => $subject,
            'category' => $category,
            'priority' => $priority,
            'status' => 'open',
            'last_reply_at' => now(),
            'last_reply_by_staff' => false,
            'metadata' => $metadata,
        ]);

        SupportTicketMessage::create([
            'support_ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'character_id' => $character->id,
            'is_staff' => false,
            'message' => $messageContent,
        ]);

        return redirect()->route('help.tickets.show', $ticket)
            ->with('success', 'Je ticket (#'.$ticket->ticket_number.') is succesvol aangemaakt! Onze medewerkers zullen zo snel mogelijk reageren.');
    }

    /**
     * Display ticket conversation details.
     */
    public function show(SupportTicket $ticket): View
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;

        if ($ticket->user_id !== $user->id && ! $user->isStaff()) {
            abort(403, 'Je hebt geen toegang tot dit support ticket.');
        }

        $ticket->load(['messages.user.character', 'character']);

        return view('game.tickets.show', compact('character', 'ticket'));
    }

    /**
     * Post a response to an existing ticket.
     */
    public function reply(Request $request, SupportTicket $ticket): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $character = $user->character;

        if ($ticket->user_id !== $user->id && ! $user->isStaff()) {
            abort(403, 'Je hebt geen toegang tot dit support ticket.');
        }

        if ($ticket->status === 'gesloten') {
            return back()->with('error', 'Dit ticket is gesloten. Open een nieuw ticket als je verdere hulp nodig hebt.');
        }

        $validated = $request->validate([
            'message' => ['required', 'string', 'min:2', 'max:4000'],
        ], [
            'message.required' => 'Vul een reactiebericht in.',
        ]);

        SupportTicketMessage::create([
            'support_ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'character_id' => $character?->id,
            'is_staff' => false,
            'message' => $validated['message'],
        ]);

        $ticket->update([
            'status' => 'open',
            'last_reply_at' => now(),
            'last_reply_by_staff' => false,
        ]);

        return back()->with('success', 'Je reactie is succesvol geplaatst.');
    }

    /**
     * Close the ticket by player.
     */
    public function close(SupportTicket $ticket): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        if ($ticket->user_id !== $user->id && ! $user->isStaff()) {
            abort(403, 'Je hebt geen toegang tot dit support ticket.');
        }

        $ticket->update([
            'status' => 'gesloten',
            'closed_at' => now(),
        ]);

        return back()->with('success', 'Ticket #'.$ticket->ticket_number.' is succesvol gemarkeerd als gesloten.');
    }
}
