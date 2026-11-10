<?php

use App\Models\Character;
use App\Models\SupportTicket;
use App\Models\SupportTicketMessage;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Answers the support ticket "Controlepunt niet aanwezig" that players
     * report as TBS-0Q9-581 (zero) or TBS-OQ9-581 (letter O). A new migration
     * is used because an earlier migration that already ran is never run again.
     */
    public function up(): void
    {
        if (! Schema::hasTable('support_tickets') || ! Schema::hasTable('support_ticket_messages')) {
            return;
        }

        $ticket = SupportTicket::whereIn('ticket_number', ['TBS-0Q9-581', 'TBS-OQ9-581'])->first()
            ?? SupportTicket::where('subject', 'like', '%controlepunt niet aanwezig%')
                ->whereHas('character', fn ($q) => $q->where('name', 'WRDMarco'))
                ->first();

        if (! $ticket) {
            // Log instead of failing, so the deploy continues. Staff can then answer by hand.
            Log::warning('Ticket TBS-OQ9-581 niet gevonden; geen automatisch antwoord geplaatst.');

            return;
        }

        // Avoid a duplicate reply when the reply text already exists.
        $alreadyReplied = $ticket->messages()
            ->where('is_staff', true)
            ->where('message', 'like', '%De Ondergrondse Smokkellijn%')
            ->exists();

        if ($alreadyReplied) {
            if ($ticket->status !== 'gesloten') {
                $ticket->update([
                    'status' => 'gesloten',
                    'last_reply_at' => now(),
                    'last_reply_by_staff' => true,
                ]);
            }

            return;
        }

        $staffUser = User::where('email', 'danielunutan@icloud.com')
            ->orWhere('email', 'like', '%daniel%')
            ->orWhere('role', 'owner')
            ->orWhere('role', 'admin')
            ->orWhere('is_staff', true)
            ->first() ?? User::first();

        $staffCharacter = $staffUser?->character
            ?? Character::where('name', 'like', '%daniel%')->first()
            ?? Character::first();

        $recipientName = $ticket->character?->name ?? 'WRDMarco';

        $replyMessage = "Beste {$recipientName},\n\n"
            ."Bedankt voor je melding over het ontbrekende controlepunt tijdens de 24-uurs bendeoorlog. Je had gelijk: het controlepunt 'De Ondergrondse Smokkellijn' (Smokkelcorridor & Buitenhek) bestond wel in het systeem, maar de banner werd nergens getoond. Daardoor kon niemand het zien of opeisen.\n\n"
            ."We hebben dit opgelost:\n\n"
            ."1. **Smokkellijn Banner op de Smokkelpagina**:\n"
            ."   - De banner van 'De Ondergrondse Smokkellijn' staat nu direct bovenaan de pagina **Smokkelen** (het Synthese-Lab & Koeriersnetwerk).\n"
            ."   - Je kunt het controlepunt vanaf daar opeisen of overnemen, net als de Luchtplaats, Kantine, Sportzaal, Depot en Medicijnkluis.\n\n"
            ."Nogmaals bedankt voor je melding. We sluiten dit ticket hierbij af. Veel succes tijdens de bendeoorlog!\n\n"
            ."Met vriendelijke groet,\n"
            .'Daniel • TBS Kliniek Leiding';

        SupportTicketMessage::create([
            'support_ticket_id' => $ticket->id,
            'user_id' => $staffUser?->id,
            'character_id' => $staffCharacter?->id,
            'is_staff' => true,
            'message' => $replyMessage,
        ]);

        $ticket->update([
            'status' => 'gesloten',
            'last_reply_at' => now(),
            'last_reply_by_staff' => true,
        ]);

        try {
            AuditLogService::log(
                action: 'support_ticket_reply',
                category: 'moderation',
                description: "Support ticket #{$ticket->ticket_number} automatisch beantwoord en gesloten via migratie door ".($staffUser?->name ?? 'Daniel'),
                character: $ticket->character,
                user: $ticket->user,
                details: [
                    'ticket_id' => $ticket->id,
                    'ticket_number' => $ticket->ticket_number,
                    'new_status' => 'gesloten',
                    'migration' => '2026_11_10_130000_auto_solve_support_ticket_tbs_oq9_581',
                ]
            );
        } catch (Throwable $e) {
            Log::warning('Failed to log support_ticket_reply audit record in migration: '.$e->getMessage());
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op: preserving ticket communication history is idempotent.
    }
};
