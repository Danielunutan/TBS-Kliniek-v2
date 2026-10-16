<?php

namespace App\Console\Commands;

use App\Models\Character;
use App\Models\SupportTicket;
use App\Models\SupportTicketMessage;
use App\Models\User;
use Illuminate\Console\Command;

class ReplyTicketCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ticket:reply {ticket_number}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send official staff reply to a support ticket under Daniel account';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $ticketNumber = $this->argument('ticket_number');
        $ticket = SupportTicket::where('ticket_number', $ticketNumber)->first();

        if (! $ticket) {
            $this->error("Ticket {$ticketNumber} niet gevonden.");

            return Command::FAILURE;
        }

        // Vind het account van Daniel (admin/owner)
        $danielUser = User::where('email', 'danielunutan@icloud.com')
            ->orWhere('email', 'like', '%daniel%')
            ->orWhere('role', 'owner')
            ->orWhere('role', 'admin')
            ->orWhere('is_staff', true)
            ->first();

        $danielCharacter = $danielUser?->character ?? Character::where('name', 'like', '%daniel%')->first();

        $replyMessage = "Beste Rainbow,\n\n"
            ."Wat een fantastische en gestructureerde suggestie! We hebben je idee zojuist direct besproken én volledig live geïmplementeerd in TBS Kliniek:\n\n"
            ."1. **Nieuw Depot tabblad onder Voortgang**: In de linkernavigatie vind je nu direct onder Voortgang het nieuwe 'Depot (Inventaris)' overzicht (/depot).\n"
            ."2. **3 Duidelijke Categorieën**:\n"
            ."   - **Persoonlijke Garage**: Al je gekochte scooters, auto's, motoren en heli's overzichtelijk met conditie, vluchtsnelheid en actieve heist-status.\n"
            ."   - **Persoonlijke Opbergplaats**: Je wapens, sigaretten, pillen, telefoons en overval-maskers.\n"
            ."   - **Persoonlijk Varia (Event Opslag)**: Speciaal ingericht met onbeperkte capaciteit voor grote events (zoals de aankomende Halloween 2026 Griezelweek)!\n"
            ."3. **Level & VIP Limieten**:\n"
            ."   - Level 1-9: 2 Garageplekken & 30 Opbergplekken\n"
            ."   - Level 10-19: 3 Garageplekken & 50 Opbergplekken\n"
            ."   - Level 20-29: 4 Garageplekken & 75 Opbergplekken\n"
            ."   - Level 30+: 5+ Garageplekken & 100+ Opbergplekken\n"
            ."   - **VIP Spelers**: Ontvangen automatisch +3 Garageplekken en +50 Opbergplekken extra bonus!\n"
            ."4. **Direct Verkopen & Ruimte Vrijmaken**: Via het Depot kun je overtollige voorraad met één klik verkopen voor cash om weer ruimte vrij te maken voor farmen en diefstallen.\n\n"
            ."Ontzettend bedankt voor je betrokkenheid en het meedenken om TBS Kliniek nog beter en leuker te maken!\n\n"
            ."Met vriendelijke groet,\n"
            .'Daniel • TBS Kliniek Leiding';

        SupportTicketMessage::create([
            'support_ticket_id' => $ticket->id,
            'user_id' => $danielUser?->id,
            'character_id' => $danielCharacter?->id,
            'is_staff' => true,
            'message' => $replyMessage,
        ]);

        $ticket->update([
            'status' => 'beantwoord',
            'last_reply_at' => now(),
            'last_reply_by_staff' => true,
        ]);

        $this->info("Ticket {$ticketNumber} succesvol beantwoord onder account {$danielUser?->email} ({$danielCharacter?->name}) en gemarkeerd als 'beantwoord'!");

        return Command::SUCCESS;
    }
}
