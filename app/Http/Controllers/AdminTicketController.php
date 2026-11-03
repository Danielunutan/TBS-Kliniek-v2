<?php

namespace App\Http\Controllers;

use App\Models\Character;
use App\Models\CharacterVehicle;
use App\Models\Gang;
use App\Models\SupportTicket;
use App\Models\SupportTicketMessage;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class AdminTicketController extends Controller
{
    /**
     * List all support tickets in Admin Panel with filtering.
     */
    public function index(Request $request): View
    {
        $currentUser = Auth::user();
        if (! $currentUser || ! $currentUser->isStaff()) {
            abort(403, 'Alleen staff medewerkers hebben toegang tot support tickets.');
        }

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
        $currentUser = Auth::user();
        if (! $currentUser || ! $currentUser->isStaff()) {
            abort(403, 'Alleen staff medewerkers hebben toegang tot support tickets.');
        }

        $ticket->load(['messages.user.character', 'character.gang', 'user']);

        return view('admin.tickets.show', compact('ticket'));
    }

    /**
     * Reply to ticket as staff and update status.
     */
    public function reply(Request $request, SupportTicket $ticket): RedirectResponse
    {
        $currentUser = Auth::user();
        if (! $currentUser || ! $currentUser->isStaff()) {
            abort(403, 'Alleen staff medewerkers kunnen reageren op support tickets.');
        }

        $validated = $request->validate([
            'message' => ['required', 'string', 'min:2', 'max:5000'],
            'status' => ['required', 'string', 'in:open,in_behandeling,beantwoord,gesloten'],
        ], [
            'message.required' => 'Vul een antwoordbericht in voor de speler.',
            'status.required' => 'Selecteer een status.',
        ]);

        $cleanMessage = preg_replace('#<script(.*?)>(.*?)</script>#is', '', (string) $validated['message']);
        $cleanMessage = trim(strip_tags($cleanMessage));
        if ($cleanMessage === '') {
            return back()->with('error', 'Vul een geldig antwoordbericht in voor de speler.');
        }

        SupportTicketMessage::create([
            'support_ticket_id' => $ticket->id,
            'user_id' => Auth::id(),
            'character_id' => Auth::user()?->character?->id,
            'is_staff' => true,
            'message' => $cleanMessage,
        ]);

        $ticket->update([
            'status' => $validated['status'],
            'last_reply_at' => now(),
            'last_reply_by_staff' => true,
            'closed_at' => $validated['status'] === 'gesloten' ? now() : null,
        ]);

        return back()->with('success', 'Reactie succesvol verzonden naar '.($ticket->character?->name ?? 'de speler').' en status geüpdatet naar '.$ticket->status_label.'!');
    }

    /**
     * Quick status update for ticket.
     */
    public function updateStatus(Request $request, SupportTicket $ticket): RedirectResponse
    {
        $currentUser = Auth::user();
        if (! $currentUser || ! $currentUser->isStaff()) {
            abort(403, 'Alleen staff medewerkers kunnen ticket statussen wijzigen.');
        }

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
        /** @var User|null $currentUser */
        $currentUser = Auth::user();
        if (! $currentUser || ! $currentUser->isAdmin()) {
            return back()->with('error', 'Alleen beheerders kunnen gang overstap aanvragen goedkeuren.');
        }

        if (! $ticket->isGangTransfer()) {
            return back()->with('error', 'Dit ticket is geen Gang Overstap Bonus aanvraag.');
        }

        if ($ticket->isGangTransferApproved()) {
            return back()->with('error', 'Deze Gang Overstap Bonus is al goedgekeurd!');
        }

        $validated = $request->validate([
            'gang_name' => ['required', 'string', 'min:3', 'max:25', 'unique:gangs,name'],
            'gang_tag' => ['required', 'string', 'min:2', 'max:4', 'unique:gangs,tag'],
            'bonus_cash' => ['nullable', 'integer', 'min:0', 'max:50000000'],
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

        $staffUser = Auth::user();
        $staffName = $staffUser?->name ? ucfirst($staffUser->name) : 'Daniel';

        $gang = DB::transaction(function () use ($character, $ticket, $gangName, $gangTag, $bonusCash, $vipDays, $vipBoostUntil, $validated, $staffName): ?Gang {
            /** @var SupportTicket|null $lockedTicket */
            $lockedTicket = SupportTicket::where('id', $ticket->id)->lockForUpdate()->first();
            if (! $lockedTicket || $lockedTicket->isGangTransferApproved()) {
                return null;
            }

            /** @var Character|null $lockedCharacter */
            $lockedCharacter = Character::where('id', $character->id)->lockForUpdate()->first();
            if (! $lockedCharacter) {
                return null;
            }

            // Leave existing gang if any and return stored vehicles
            if ($lockedCharacter->gang_id) {
                $oldGang = Gang::find($lockedCharacter->gang_id);
                CharacterVehicle::where('character_id', $lockedCharacter->id)
                    ->where('in_gang_garage', true)
                    ->update([
                        'in_gang_garage' => false,
                        'gang_id' => null,
                        'stored_at' => null,
                        'stored_by_character_id' => null,
                    ]);

                if ($oldGang) {
                    if ($oldGang->treasurer_id === $lockedCharacter->id) {
                        $oldGang->treasurer_id = null;
                    }
                    if ($oldGang->underboss_id === $lockedCharacter->id) {
                        $oldGang->underboss_id = null;
                    }
                    if ($oldGang->leader_character_id === $lockedCharacter->id) {
                        $nextLeader = Character::where('gang_id', $oldGang->id)
                            ->where('id', '!=', $lockedCharacter->id)
                            ->orderByRaw("CASE WHEN gang_role = 'coleader' THEN 0 ELSE 1 END")
                            ->first();
                        if ($nextLeader) {
                            $oldGang->leader_character_id = $nextLeader->id;
                            $nextLeader->gang_role = 'leader';
                            $nextLeader->save();
                            $oldGang->save();
                        } else {
                            CharacterVehicle::where('gang_id', $oldGang->id)
                                ->update([
                                    'in_gang_garage' => false,
                                    'gang_id' => null,
                                    'stored_at' => null,
                                    'stored_by_character_id' => null,
                                ]);
                            $oldGang->delete();
                        }
                    } else {
                        $oldGang->save();
                    }
                }

                $lockedCharacter->gang_id = null;
                $lockedCharacter->gang_role = 'member';
                $lockedCharacter->save();
            }

            // Create the new Gang
            $newGang = Gang::create([
                'name' => $gangName,
                'tag' => $gangTag,
                'leader_character_id' => $lockedCharacter->id,
                'specialty' => 'Kantine Controle',
                'description' => 'Officiële overstapbende in TBS Kliniek. Gestart met €'.number_format($bonusCash, 0, ',', '.').' overstapbonus!',
                'treasury' => $bonusCash,
                'emblem' => 'fa-crown',
                'vip_boost_until' => $vipBoostUntil,
            ]);

            // Assign leader and grant personal VIP as well
            $lockedCharacter->gang_id = $newGang->id;
            $lockedCharacter->gang_role = 'leader';
            $lockedCharacter->last_gang_wage_at = now()->startOfHour();
            $leaderHasPermanentVip = ($lockedCharacter->is_vip && $lockedCharacter->vip_expires_at === null)
                || $lockedCharacter->getVipTier() === 4;
            $lockedCharacter->is_vip = true;
            if (! $leaderHasPermanentVip) {
                $leaderBaseExpiry = ($lockedCharacter->vip_expires_at && $lockedCharacter->vip_expires_at->isFuture())
                    ? $lockedCharacter->vip_expires_at->copy()
                    : now();
                $lockedCharacter->vip_expires_at = $leaderBaseExpiry->addDays($vipDays);
            }
            $lockedCharacter->save();

            // If member names were specified in ticket metadata, also grant existing players personal VIP and auto-join
            $memberNames = $lockedTicket->metadata['member_names'] ?? null;
            if (! empty($memberNames)) {
                $names = array_filter(array_map('trim', explode(',', str_replace(["\n", "\r", ';'], ',', $memberNames))));
                if (! empty($names)) {
                    $matchedChars = Character::whereIn('name', $names)->get();
                    foreach ($matchedChars as $mc) {
                        $mcHasPermanentVip = ($mc->is_vip && $mc->vip_expires_at === null)
                            || $mc->getVipTier() === 4;
                        $mc->is_vip = true;
                        if (! $mcHasPermanentVip) {
                            $mcBaseExpiry = ($mc->vip_expires_at && $mc->vip_expires_at->isFuture())
                                ? $mc->vip_expires_at->copy()
                                : now();
                            $mc->vip_expires_at = $mcBaseExpiry->addDays($vipDays);
                        }
                        if (! $mc->gang_id) {
                            $mc->gang_id = $newGang->id;
                            $mc->gang_role = 'member';
                            $mc->last_gang_wage_at = now()->startOfHour();
                        }
                        $mc->save();
                    }
                }
            }

            $customReply = $validated['reply_message'] ?? null;
            $autoReply = $customReply ?: "Gefeliciteerd! Jouw aanvraag voor de Gang Overstap Bonus is goedgekeurd door TBS Kliniek Staff!\n\nJe bende '{$newGang->name}' [{$newGang->tag}] is direct voor je opgericht en we hebben € ".number_format($bonusCash, 0, ',', '.')." startkapitaal in jullie kluis gestort. Daarnaast hebben jij en al je bendeleden {$vipDays} dagen gratis VIP geactiveerd gekregen!\n\nNodig al je leden uit via het Bendepaneel en veel succes met het veroveren van de kliniek!\n\nMet vriendelijke groet,\n{$staffName}\nTBS Kliniek Staff";

            SupportTicketMessage::create([
                'support_ticket_id' => $lockedTicket->id,
                'user_id' => Auth::id(),
                'character_id' => Auth::user()?->character?->id,
                'is_staff' => true,
                'message' => $autoReply,
            ]);

            $metadata = $lockedTicket->metadata ?? [];
            $metadata['bonus_approved_at'] = now()->toIso8601String();
            $metadata['approved_by_user_id'] = Auth::id();
            $metadata['approved_by_name'] = $staffName;
            $metadata['created_gang_id'] = $newGang->id;
            $metadata['created_gang_name'] = $newGang->name;
            $metadata['created_gang_tag'] = $newGang->tag;
            $metadata['bonus_cash'] = $bonusCash;
            $metadata['vip_days'] = $vipDays;

            $lockedTicket->update([
                'metadata' => $metadata,
                'status' => 'beantwoord',
                'last_reply_at' => now(),
                'last_reply_by_staff' => true,
            ]);

            return $newGang;
        });

        if (! $gang) {
            return back()->with('error', 'Deze Gang Overstap Bonus is al goedgekeurd!');
        }

        try {
            AuditLogService::log(
                action: 'gang_transfer_approved',
                category: 'moderation',
                description: "Gang Overstap Bonus goedgekeurd voor {$gangName} door {$staffName}",
                character: $character,
                user: $character->user,
                amount: $bonusCash,
                details: [
                    'ticket_id' => $ticket->id,
                    'gang_id' => $gang->id,
                    'gang_name' => $gang->name,
                    'gang_tag' => $gang->tag,
                    'vip_days' => $vipDays,
                    'approved_by_id' => Auth::id(),
                    'approved_by_name' => $staffName,
                ]
            );
        } catch (\Throwable $e) {
            Log::warning('Failed to log gang_transfer_approved audit record: '.$e->getMessage());
        }

        return back()->with('success', "Gang Overstap Bonus succesvol goedgekeurd! Bende '{$gang->name}' [{$gang->tag}] is aangemaakt met €".number_format($bonusCash, 0, ',', '.')." kluisgeld en {$vipDays} dagen VIP.");
    }
}
