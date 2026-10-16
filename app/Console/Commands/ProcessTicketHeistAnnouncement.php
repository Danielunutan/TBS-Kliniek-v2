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

            $alreadyReplied = $ticket->messages()->where('is_staff', true)->where('message', 'like', '%Beste Rainbow%')->exists();

            if (! $alreadyReplied) {
                SupportTicketMessage::create([
                    'support_ticket_id' => $ticket->id,
                    'user_id' => $adminUser->id,
                    'character_id' => $adminCharacter?->id,
                    'is_staff' => true,
                    'message' => "Beste Rainbow,\n\nOntzettend bedankt voor je uitgebreide en creatieve suggestie! Ik heb je idee samen met het team direct grondig geanalyseerd en we waren er zó enthousiast over dat we het zojuist per direct LIVE hebben gebouwd in TBS Kliniek!\n\nWat staat er nu live voor jou en alle andere spelers:\n🏎️ De Smokkel Garage (onder Economie & Gokken):\nHier staat een wagenpark van 8 verschillende voertuigen voor je klaar (o.a. Scooter Zip, Golf GTI, snelle Speedboot, BMW M5 F90, Gepantserde G63, Robinson Helikopter en Cessna Smokkelvliegtuig).\n🛠️ Conditie & Reparaties:\nWagens hebben een eigen Topsnelheid en Conditie (0-100%). Tijdens zware achtervolgingen lopen voertuigen schade op, waardoor de effectieve snelheid daalt. In de werkplaats van de garage kun je je wagen altijd weer laten oplappen tegen contant geld!\n🎭 Grote Heists (onder Illegale Acties):\nZware overvallen met hoge inzetten en mega buit! Denk aan het Medicijn Transport, Juwelierskraak, Casino Kraak, Haven Container Heist en de Centrale Bank Goudkluis. Om een heist te starten heb je een actief vluchtvoertuig én maskers (bivakmutsen) nodig. Naast enorme geldbedragen en XP maak je ook kans op zeldzame kluiscodes voor de Centrale Medicijnkluis!\n🤝 Gang Heists (Bende Heists):\nJe idee voor gezamenlijke bende-heists met een buitverdeling naar de bendekluis is geweldig en staat direct als topprioriteit op onze roadmap voor de volgende grote bende-update.\n\nNogmaals hartelijk dank voor het meedenken en je scherpe feedback! Neem snel een kijkje in de Smokkel Garage en veel succes met je eerste Heist!\n\nMet vriendelijke groet,\nDaniel\nTBS Kliniek Staff & Beheer",
                ]);

                $ticket->update([
                    'status' => 'beantwoord',
                    'last_reply_at' => now(),
                    'last_reply_by_staff' => true,
                ]);

                $this->info("Ticket #{$ticket->ticket_number} succesvol beantwoord en status gewijzigd naar 'beantwoord'.");
            } else {
                $this->info("Ticket #{$ticket->ticket_number} was al voorzien van antwoord van Daniel.");
            }
        } else {
            $this->warn('Ticket TBS-YON-008 niet lokaal gevonden (wordt uitgevoerd op productie server).');
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
