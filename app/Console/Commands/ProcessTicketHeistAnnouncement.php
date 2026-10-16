<?php

namespace App\Console\Commands;

use App\Models\Character;
use App\Models\RadioShoutout;
use App\Models\SupportTicket;
use App\Models\SupportTicketMessage;
use App\Models\TimelinePost;
use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('game:process-heist-announcement')]
#[Description('Beantwoord ticket TBS-YON-008 namens Daniel en publiceert aankondigingen op de tijdlijn en radio')]
class ProcessTicketHeistAnnouncement extends Command
{
    public function handle(): int
    {
        $this->info('Zoeken naar Daniel / Staff beheerder...');

        // Zoek Daniel op basis van e-mail, naam of staff status
        $adminUser = User::where('email', 'danielunutan@icloud.com')
            ->orWhere('name', 'like', '%daniel%')
            ->orWhere('email', 'like', '%daniel%')
            ->orWhere('is_staff', true)
            ->orWhereIn('role', ['admin', 'owner'])
            ->first();

        if (! $adminUser) {
            $adminUser = User::first();
        }

        /** @var Character|null $adminCharacter */
        $adminCharacter = $adminUser?->character;

        if (! $adminCharacter) {
            $adminCharacter = Character::first();
        }

        $this->info("Beheerder gevonden: User ID {$adminUser->id} ({$adminUser->name}), Character: {$adminCharacter?->name}");

        // 1. Ticket beantwoorden
        $ticket = SupportTicket::where('ticket_number', 'TBS-YON-008')
            ->orWhere('subject', 'like', '%VOERTUIGEN & HEISTS%')
            ->first();

        if ($ticket) {
            $this->info("Ticket gevonden: #{$ticket->ticket_number} - {$ticket->subject}");

            $neatMessage = "Beste Rainbow,\n\nOntzettend bedankt voor je uitgebreide en creatieve suggestie!\n\nIk heb je idee samen met het team grondig geanalyseerd en we waren er zó enthousiast over dat we het zojuist per direct LIVE hebben gebouwd in TBS Kliniek! 🚀\n\nHieronder vind je een duidelijk overzicht van alle nieuwe systemen:\n\n━━━━━━━━━━━━━━━━━━━━━━━━━━\n🏎️ 1. DE SMOKKEL GARAGE (Economie & Gokken)\n━━━━━━━━━━━━━━━━━━━━━━━━━━\nIn de garage staat nu een wagenpark van 8 unieke voertuigen klaar voor aankoop met contant geld of sterren:\n\n• Scooter Zip (45 km/u) - Snel & wendbaar\n• Golf GTI (120 km/u) - Betrouwbare getaway\n• Snelle Speedboot (140 km/u) - Voor waterroutes\n• BMW M5 F90 (190 km/u) - Razendsnel vluchtmonster\n• Gepantserde G63 AMG (160 km/u) - Maximale bescherming\n• Geldtransportbus (110 km/u) - Onopvallende dekmantel\n• Robinson R44 Helikopter (220 km/u) - Luchtescape\n• Cessna Smokkelvliegtuig (260 km/u) - Het ultieme smokkeltoestel\n\n━━━━━━━━━━━━━━━━━━━━━━━━━━\n🛠️ 2. VOERTUIG CONDITIE & ONDERHOUD\n━━━━━━━━━━━━━━━━━━━━━━━━━━\nElk voertuig beschikt over Topsnelheid en Conditie (0% tot 100%):\n\n• Tijdens heists lopen voertuigen slijtage en schade op door achtervolgingen.\n• Naarmate de conditie daalt, neemt de effectieve topsnelheid en je slaagkans af.\n• In de werkplaats van de garage kun je je wagen direct laten oplappen tegen contant geld om maximale prestaties te behouden.\n\n━━━━━━━━━━━━━━━━━━━━━━━━━━\n🎭 3. GROTE HEISTS (Illegale Acties)\n━━━━━━━━━━━━━━━━━━━━━━━━━━\nHigh-stakes overvallen met gigantische buitbedragen:\n\n• Medicijn Transport - Buit: €12.500 tot €25.000 (Min. Level 3)\n• Juwelier Ramkraak - Buit: €35.000 tot €65.000 (Min. Level 5)\n• Casino Buitkraak - Buit: €85.000 tot €160.000 (Min. Level 7)\n• Haven Container Smokkel - Buit: €150.000 tot €280.000 (Min. Level 10)\n• Centrale Bank Goudkluis - Buit: €300.000 tot €600.000 (Min. Level 12)\n\nVereisten & Risico's:\n• Een actief vluchtvoertuig met voldoende snelheid en conditie.\n• Maskers (bivakmutsen) tegen herkenning door bewaking.\n• Naast megabuit en veel XP maak je kans op zeldzame kluiscodes voor de Centrale Medicijnkluis!\n• Let op: Bij een mislukte overval vlieg je direct voor langere tijd de isoleercel in.\n\n━━━━━━━━━━━━━━━━━━━━━━━━━━\n🤝 4. BENDE HEISTS (Roadmap)\n━━━━━━━━━━━━━━━━━━━━━━━━━━\nJe idee voor gezamenlijke Heists met bendeleden en een automatische buitverdeling naar de bendekluis is geweldig! We hebben dit direct als prioriteit #1 genoteerd voor de aankomende Gang Update.\n\nNogmaals hartelijk dank voor het meedenken en je scherpe feedback! Neem snel een kijkje in de Smokkel Garage en heel veel succes met je eerste Heist! 🔥\n\nMet vriendelijke groet,\n\nDaniel\nTBS Kliniek Staff & Beheer";

            $existingMsg = $ticket->messages()->where('is_staff', true)->where('message', 'like', '%Beste Rainbow%')->first();

            if ($existingMsg) {
                $existingMsg->update(['message' => $neatMessage]);
                $this->info("Bestaand bericht in ticket #{$ticket->ticket_number} succesvol bijgewerkt met nette indeling.");
            } else {
                SupportTicketMessage::create([
                    'support_ticket_id' => $ticket->id,
                    'user_id' => $adminUser->id,
                    'character_id' => $adminCharacter?->id,
                    'is_staff' => true,
                    'message' => $neatMessage,
                ]);
                $this->info("Nieuw net bericht geplaatst in ticket #{$ticket->ticket_number}.");
            }

            $ticket->update([
                'status' => 'beantwoord',
                'last_reply_at' => now(),
                'last_reply_by_staff' => true,
            ]);
        } else {
            $this->warn('Ticket niet lokaal gevonden (wordt uitgevoerd op productie server).');
        }

        // 2. Posten op Tijdlijn (Chat)
        if ($adminCharacter) {
            TimelinePost::create([
                'character_id' => $adminCharacter->id,
                'content' => '🚨 UPDATE LIVE: Smokkel Garage & Grote Heists staan nu online! Scoor vluchtwagens, zet maskers op en overval kluizen voor megabuit! Shoutout naar Rainbow voor het idee! 🏎️💥',
                'likes_count' => 3,
            ]);
            $this->info('Aankondiging succesvol geplaatst op de Tijdlijn.');

            // 3. Posten op TBS FM Radio Shoutout
            RadioShoutout::create([
                'character_id' => $adminCharacter->id,
                'character_name' => $adminCharacter->name,
                'gang_name' => $adminCharacter->gang?->name ?? 'TBS Staff',
                'track_title' => 'Charly Lownoise & Mental Theo - Wonderful Days',
                'message' => '🚨 NIEUWE UPDATE LIVE: Smokkel Garage & Grote Heists staan nu online! Haal je wagens op en kraak die kluizen! Shoutout naar Rainbow! 🔥🏎️',
                'is_anonymous' => false,
                'likes_count' => 2,
            ]);
            $this->info('Aankondiging succesvol geplaatst op TBS FM Radio Shoutouts.');
        }

        return self::SUCCESS;
    }
}
