<?php

namespace App\Http\Controllers;

use App\Models\Character;
use App\Models\Gang;
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

        $tickets = $query
            ->orderByRaw("CASE 
                WHEN status = 'open' THEN 1 
                WHEN last_reply_by_staff = 0 AND status != 'gesloten' THEN 2 
                WHEN status = 'in_behandeling' THEN 3 
                WHEN status = 'beantwoord' THEN 4 
                ELSE 5 
            END")
            ->latest('updated_at')
            ->paginate(20)
            ->withQueryString();

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

    /**
     * Approve Gang Transfer bonus: creates gang, injects €5,000,000 into treasury,
     * assigns character as leader, gives 31 days VIP to leader & gang boost, and posts auto-reply.
     */
    public function approveGangTransfer(Request $request, SupportTicket $ticket): RedirectResponse
    {
        if (! $ticket->isGangTransfer()) {
            return back()->with('error', 'Dit ticket is geen Gang Overstap Bonus aanvraag.');
        }

        if ($ticket->isGangTransferApproved()) {
            return back()->with('error', 'Deze Gang Overstap Bonus is al goedgekeurd!');
        }

        $validated = $request->validate([
            'gang_name' => ['required', 'string', 'min:3', 'max:25', 'unique:gangs,name'],
            'gang_tag' => ['required', 'string', 'min:2', 'max:4'],
            'bonus_cash' => ['nullable', 'integer', 'min:0'],
            'vip_days' => ['nullable', 'integer', 'min:1', 'max:365'],
            'reply_message' => ['nullable', 'string', 'max:5000'],
        ]);

        $gangName = strip_tags($validated['gang_name']);
        $gangTag = strtoupper(strip_tags($validated['gang_tag']));
        $bonusCash = (int) ($validated['bonus_cash'] ?? 5000000);
        $vipDays = (int) ($validated['vip_days'] ?? 3);
        $vipBoostUntil = now()->addDays($vipDays);

        $character = $ticket->character;
        if (! $character) {
            return back()->with('error', 'Personage van indiener kon niet gevonden worden.');
        }

        // Leave existing gang if any
        if ($character->gang_id) {
            $character->gang_id = null;
            $character->gang_role = 'member';
            $character->save();
        }

        // Create the new Gang
        $gang = Gang::create([
            'name' => $gangName,
            'tag' => $gangTag,
            'leader_character_id' => $character->id,
            'specialty' => 'Kantine Controle',
            'description' => 'Officiële overstapbende in TBS Kliniek. Gestart met €'.number_format($bonusCash, 0, ',', '.').' overstapbonus!',
            'treasury' => $bonusCash,
            'emblem' => 'fa-crown',
            'vip_boost_until' => $vipBoostUntil,
        ]);

        // Assign leader and grant personal VIP as well
        $character->gang_id = $gang->id;
        $character->gang_role = 'leader';
        $character->last_gang_wage_at = now();
        $character->is_vip = true;
        $character->vip_expires_at = $vipBoostUntil;
        $character->save();

        // If member names were specified in ticket metadata, also grant existing players personal VIP and auto-join
        $memberNames = $ticket->metadata['member_names'] ?? null;
        if (! empty($memberNames)) {
            $names = array_filter(array_map('trim', explode(',', str_replace(["\n", "\r", ';'], ',', $memberNames))));
            if (! empty($names)) {
                $matchedChars = Character::whereIn('name', $names)->get();
                foreach ($matchedChars as $mc) {
                    $mc->is_vip = true;
                    $mc->vip_expires_at = $vipBoostUntil;
                    if (! $mc->gang_id) {
                        $mc->gang_id = $gang->id;
                        $mc->gang_role = 'member';
                    }
                    $mc->save();
                }
            }
        }

        // Format staff auto-reply message
        $staffUser = Auth::user();
        $staffName = $staffUser?->name ? ucfirst($staffUser->name) : 'Daniel';

        $customReply = $validated['reply_message'] ?? null;
        $autoReply = $customReply ?: "Gefeliciteerd! Jouw aanvraag voor de Gang Overstap Bonus is goedgekeurd door TBS Kliniek Staff!\n\nJe bende '{$gang->name}' [{$gang->tag}] is direct voor je opgericht en we hebben € ".number_format($bonusCash, 0, ',', '.')." startkapitaal in jullie kluis gestort. Daarnaast hebben jij en al je bendeleden {$vipDays} dagen gratis VIP geactiveerd gekregen!\n\nNodig al je leden uit via het Bendepaneel en veel succes met het veroveren van de kliniek!\n\nMet vriendelijke groet,\n{$staffName}\nTBS Kliniek Staff";

        SupportTicketMessage::create([
            'support_ticket_id' => $ticket->id,
            'user_id' => Auth::id(),
            'character_id' => Auth::user()?->character?->id,
            'is_staff' => true,
            'message' => $autoReply,
        ]);

        $metadata = $ticket->metadata ?? [];
        $metadata['bonus_approved_at'] = now()->toIso8601String();
        $metadata['approved_by_user_id'] = Auth::id();
        $metadata['approved_by_name'] = $staffName;
        $metadata['created_gang_id'] = $gang->id;
        $metadata['created_gang_name'] = $gang->name;
        $metadata['created_gang_tag'] = $gang->tag;
        $metadata['bonus_cash'] = $bonusCash;
        $metadata['vip_days'] = $vipDays;

        $ticket->update([
            'metadata' => $metadata,
            'status' => 'beantwoord',
            'last_reply_at' => now(),
            'last_reply_by_staff' => true,
        ]);

        return back()->with('success', "Gang Overstap Bonus succesvol goedgekeurd! Bende '{$gang->name}' [{$gang->tag}] is aangemaakt met €".number_format($bonusCash, 0, ',', '.')." kluisgeld en {$vipDays} dagen VIP.");
    }
}
