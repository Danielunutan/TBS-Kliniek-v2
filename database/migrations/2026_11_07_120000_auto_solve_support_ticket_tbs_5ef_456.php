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
     */
    public function up(): void
    {
        if (! Schema::hasTable('support_tickets') || ! Schema::hasTable('support_ticket_messages')) {
            return;
        }

        $ticket = SupportTicket::where('ticket_number', 'TBS-5EF-456')
            ->orWhere(function ($q) {
                $q->where('subject', 'like', '%isolatie%')
                    ->whereHas('character', function ($cq) {
                        $cq->where('name', 'carootje');
                    });
            })
            ->first();

        if (! $ticket) {
            return;
        }

        // Avoid duplicate replies if already answered with this content
        $alreadyReplied = $ticket->messages()
            ->where('is_staff', true)
            ->where('message', 'like', '%Dynamische Afkoeltijd%')
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

        $recipientName = $ticket->character?->name ?? 'carootje';

        $replyMessage = "Beste {$recipientName},\n\n"
            ."Hartelijk dank voor je bericht en je uitstekende suggestie over de Vrijwillige Isolatie! 🛡️🚪\n\n"
            ."Je had helemaal gelijk: als je bijvoorbeeld nog maar 6 minuten isolatie op de klok had staan en je cel vroegtijdig ontgrendelde, was het niet logisch dat je direct een vaste wachttijd van 2 uur kreeg. De afkoeltijd hoort nooit langer te zijn dan de resterende tijd die je nog in isolatie had!\n\n"
            ."We hebben dit direct aangepast in het isolatiesysteem:\n\n"
            ."1. **Dynamische Afkoeltijd op Maat**:\n"
            ."   - Wanneer je je vrijwillige isolatie voortijdig beëindigt (of je celdeur verlaat voor een aanval), is je afkoeltijd vanaf nu exact gelijk aan de **resterende tijd** die je nog op je isolatieklok had staan (met een maximum van 2 uur).\n"
            ."   - Als je cel dus nog 6 minuten dicht zat en je stapt eruit, hoef je nu ook nog maar **6 minuten** te wachten voordat je er weer in kunt!\n"
            ."   - Had je nog 11 uur staan? Dan geldt het maximum van 2 uur wachttijd, zodat je nooit onevenredig lang buiten spel staat.\n\n"
            ."2. **Duidelijke Meldingen & Tijdsaanduiding**:\n"
            ."   - Zowel in je statusmelding als in de systeemnotificatie zie je nu direct precies hoeveel minuten afkoeltijd er van toepassing is.\n\n"
            ."Nogmaals enorm bedankt voor het meedenken om het spel eerlijker en soepeler te maken! We sluiten dit ticket hierbij netjes af.\n\n"
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
                    'migration' => '2026_11_07_120000_auto_solve_support_ticket_tbs_5ef_456',
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
