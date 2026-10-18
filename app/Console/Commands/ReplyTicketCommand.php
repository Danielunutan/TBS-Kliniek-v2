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

        if ($ticketNumber === 'TBS-565-167' || str_contains(strtolower($ticket->subject), 'panden')) {
            $replyMessage = "Beste Rainbow,\n\n"
                ."Wauw, wat een geweldige en waardevolle suggestie voor de bende-wereld! We hebben je idee zojuist direct uitgewerkt en zojuist volledig live geïmplementeerd in TBS Kliniek:\n\n"
                ."1. **Nieuw Bende Panden & Vastgoed Systeem (/gangs/panden)**:\n"
                ."   - In het Bende Menu en de zijbalk vind je nu direct het nieuwe onderdeel 'Bende Panden'.\n"
                ."   - Panden worden gefinancierd via de gezamenlijke bendekas (treasury) door de leiders/beheerders.\n\n"
                ."2. **3 Soorten Panden & Vastgoed**:\n"
                ."   - **Opslag & Stallingen (Loodsen & Bunkers)**:\n"
                ."     - *Kleine Opslagloods & Garage*: +50 bende opslagplaatsen & 2 voertuigstallingen.\n"
                ."     - *Industriële Distributieloods*: +150 opslagplaatsen & 5 voertuigstallingen.\n"
                ."     - *Versterkte Ondergrondse Bunker*: +350 opslagplaatsen & 10 zware wagenplekken + verdedigingsbonus.\n"
                ."   - **Productie & Fabrieken**:\n"
                ."     - *Ondergrondse Valsgeld Drukkerij*: Produceert €75.000 cash en 30 Respect per 2-uurs cyclus.\n"
                ."     - *Geheim Chemisch Smokkel-Lab*: Produceert €180.000 cash en 65 Respect per 4-uurs cyclus.\n"
                ."     - *Verborgen Wapenfabriek & Smelterij*: Produceert €400.000 cash en 150 Respect per 6-uurs cyclus.\n"
                ."     - Bendeleiders kunnen de productiecyclus starten. Zodra de timer klaar is, claim je de buit direct naar de bendekas!\n"
                ."   - **Luxe Panden & Villa's (HQ & NPC Rekruten)**:\n"
                ."     - *Luxe Sky-Penthouse HQ*: Biedt plaats aan 3 NPC Rekruten + 10% bonus op alle bendelonen.\n"
                ."     - *Zwaarbeveiligd Landgoed & Gang Villa*: Biedt plaats aan 8 NPC Rekruten + 25% bonus op bendelonen en 250 Respect.\n\n"
                ."3. **NPC Bendeleden Inhuren & Inzetten in Fabrieken**:\n"
                ."   - Zodra je bende een Penthouse of Villa bezit, kun je gespecialiseerde NPC's aannemen uit de bendekas:\n"
                ."     - *Dimitri \"De Opzichter\"*: Wijs hem toe aan een fabriek voor +25% extra cash opbrengst!\n"
                ."     - *Dr. Walter V. (Chemisch Specialist)*: Verkort productietijden met 30%.\n"
                ."     - *Boris \"De Sloper\" (Enforcer)*: Biedt +20% verdedigingskracht bij bendeaanvallen.\n\n"
                ."Ontzettend bedankt voor je fantastische bijdrage en het meedenken om TBS Gang en de kliniek naar een hoger niveau te tillen! Veel succes met het opbouwen van jullie vastgoedimperium.\n\n"
                ."Met vriendelijke groet,\n"
                .'Daniel • TBS Kliniek Leiding';
        } else {
            $replyMessage = "Beste {$ticket->character?->name},\n\n"
                ."Bedankt voor je support ticket. We hebben je verzoek zorgvuldig bekeken en afgehandeld.\n\n"
                ."Mocht je verdere vragen of suggesties hebben, laat het ons gerust weten!\n\n"
                ."Met vriendelijke groet,\n"
                .'Daniel • TBS Kliniek Leiding';
        }

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
