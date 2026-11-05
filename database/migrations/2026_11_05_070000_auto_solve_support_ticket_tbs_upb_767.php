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

        $ticket = SupportTicket::where('ticket_number', 'TBS-UPB-767')
            ->orWhere('id', 767)
            ->orWhere(function ($q) {
                $q->where('subject', 'like', '%kliniek terminal%')
                    ->orWhere('subject', 'like', '%terminal%');
            })
            ->first();

        if (! $ticket) {
            return;
        }

        // Avoid duplicate replies if already answered with this content
        $alreadyReplied = $ticket->messages()
            ->where('is_staff', true)
            ->where('message', 'like', '%Cypher Poorten Decryptie%')
            ->exists();

        if ($alreadyReplied) {
            if ($ticket->status !== 'beantwoord' && $ticket->status !== 'gesloten') {
                $ticket->update([
                    'status' => 'beantwoord',
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

        $recipientName = $ticket->character?->name ?? 'OOHPA Arjan';

        $replyMessage = "Beste {$recipientName},\n\n"
            ."Ontzettend bedankt voor je melding via dit ticket! 💻⚡\n\n"
            ."Je had helemaal gelijk: bij Puzzel 2 van de Kliniek Terminal (de Cypher Poorten Decryptie) trad een probleem op waardoor de plusknoppen (+) op sommige apparaten niet goed reageerden, en als de timer afliep raakten de poortfrequenties gedesynchroniseerd. Daardoor kon je de datakanalen inderdaad niet vol krijgen en liep de hack vast.\n\n"
            ."We hebben dit zojuist direct grondig aangepakt en geoptimaliseerd in de TBS Kliniek Terminal (/hacking):\n\n"
            ."1. **Verbeterde Plusknoppen & Touch Ergonomie**:\n"
            ."   - De plus- en minknoppen zijn vergroot met een ruime interactieve touch-zone en responsieve klik/tik-feedback.\n"
            ."   - Snelle tikken op mobiel worden nu altijd direct geregistreerd zonder vertraging of ongewenste schermzoom.\n\n"
            ."2. **Nieuwe 'VOL' Knop per Datakanaal**:\n"
            ."   - Naast de plus- en minknoppen heeft elk kanaal nu direct een eigen **'VOL'** knop waarmee je de poort met één klik direct exact op zijn vereiste doelfrequentie zet.\n\n"
            ."3. **Nieuwe 'Alles Vol Zetten (Auto-Sync)' Knop**:\n"
            ."   - Precies zoals je vroeg: onder de 4 datakanalen vind je nu direct de knop **'⚡ Alles Vol Zetten (Auto-Sync)'**! Hiermee synchroniseer je alle 4 de poorten in één enkele klik direct naar 100% en vlieg je soepel door naar Puzzel 3 (Root Access)!\n\n"
            ."4. **Reset- & Doelwaarde Bescherming**:\n"
            ."   - De frequenties stoppen nu altijd exact op hun vereiste doelfrequentie (45, 80, 90 en 60 MHz) en kunnen nooit meer voorbij het doel schieten.\n"
            ."   - Als de alarmtimer afloopt, resetten alle waarden weer netjes naar de beginstand zodat je altijd opnieuw kunt proberen.\n\n"
            ."Nogmaals enorm bedankt voor het melden van deze bug. Je kunt nu direct weer naar **'Kliniek Terminal'** in het menu om je dagelijkse cyber hack uit te voeren en te draaien aan de Hacker Spinner!\n\n"
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
            'status' => 'beantwoord',
            'last_reply_at' => now(),
            'last_reply_by_staff' => true,
        ]);

        try {
            AuditLogService::log(
                action: 'support_ticket_reply',
                category: 'moderation',
                description: "Support ticket #{$ticket->ticket_number} automatisch beantwoord via migratie door ".($staffUser?->name ?? 'Daniel'),
                character: $ticket->character,
                user: $ticket->user,
                details: [
                    'ticket_id' => $ticket->id,
                    'ticket_number' => $ticket->ticket_number,
                    'new_status' => 'beantwoord',
                    'migration' => '2026_11_05_070000_auto_solve_support_ticket_tbs_upb_767',
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
