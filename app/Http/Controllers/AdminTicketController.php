<?php

namespace App\Http\Controllers;

use App\Models\SupportTicket;
use App\Models\SupportTicketMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AdminTicketController extends Controller
{
    /**
     * List all support tickets in Admin Panel with filtering.
     */
    public function index(Request $request): View
    {
        $query = SupportTicket::with(['character', 'user', 'lastMessage']);

        $currentStatus = $request->input('status', 'actief');

        if ($currentStatus === 'actief') {
            $query->whereIn('status', ['open', 'in_behandeling', 'beantwoord']);
        } elseif (in_array($currentStatus, ['open', 'in_behandeling', 'beantwoord', 'gesloten'], true)) {
            $query->where('status', $currentStatus);
        }

        if ($category = $request->input('category')) {
            $query->where('category', $category);
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('ticket_number', 'like', "%{$search}%")
                    ->orWhere('subject', 'like', "%{$search}%")
                    ->orWhereHas('character', function ($cq) use ($search) {
                        $cq->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('email', 'like', "%{$search}%");
                    });
            });
        }

        $tickets = $query->latest('updated_at')->paginate(20)->withQueryString();

        $stats = [
            'open' => SupportTicket::where('status', 'open')->count(),
            'in_behandeling' => SupportTicket::where('status', 'in_behandeling')->count(),
            'beantwoord' => SupportTicket::where('status', 'beantwoord')->count(),
            'gesloten' => SupportTicket::where('status', 'gesloten')->count(),
            'totaal' => SupportTicket::count(),
        ];

        return view('admin.tickets.index', compact('tickets', 'stats', 'currentStatus'));
    }

    /**
     * View ticket thread and player information.
     */
    public function show(SupportTicket $ticket): View
    {
        $ticket->load(['messages.user.character', 'character.gang', 'user']);

        return view('admin.tickets.show', compact('ticket'));
    }

    /**
     * Reply to ticket as staff and update status.
     */
    public function reply(Request $request, SupportTicket $ticket): RedirectResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'min:2', 'max:5000'],
            'status' => ['required', 'string', 'in:open,in_behandeling,beantwoord,gesloten'],
        ], [
            'message.required' => 'Vul een antwoordbericht in voor de speler.',
            'status.required' => 'Selecteer een status.',
        ]);

        SupportTicketMessage::create([
            'support_ticket_id' => $ticket->id,
            'user_id' => Auth::id(),
            'character_id' => Auth::user()?->character?->id,
            'is_staff' => true,
            'message' => $validated['message'],
        ]);

        $ticket->update([
            'status' => $validated['status'],
            'last_reply_at' => now(),
            'last_reply_by_staff' => true,
            'closed_at' => $validated['status'] === 'gesloten' ? now() : null,
        ]);

        return back()->with('success', 'Reactie succesvol verzonden naar '.$ticket->character->name.' en status geüpdatet naar '.$ticket->status_label.'!');
    }

    /**
     * Quick status update for ticket.
     */
    public function updateStatus(Request $request, SupportTicket $ticket): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'string', 'in:open,in_behandeling,beantwoord,gesloten'],
        ]);

        $ticket->update([
            'status' => $validated['status'],
            'closed_at' => $validated['status'] === 'gesloten' ? now() : null,
        ]);

        return back()->with('success', 'Ticket status gewijzigd naar: '.$ticket->status_label);
    }
}
