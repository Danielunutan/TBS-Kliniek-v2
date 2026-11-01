<?php

namespace App\Console\Commands;

use App\Models\Character;
use App\Models\GameNotification;
use App\Models\SupportTicket;
use App\Models\SupportTicketMessage;
use App\Models\User;
use Illuminate\Console\Command;

class ResolveOpenTicketsCommand extends Command
{
    protected $signature = 'ticket:resolve-open-tickets';

    protected $description = 'Compensate player RW24 and answer tickets TBS-SNU-774 and TBS-YXJ-967';

    public function handle(): int
    {
        $adminUser = User::where('role', 'owner')->first()
            ?? User::where('role', 'admin')->first()
            ?? User::where('is_staff', true)->first();

        if (! $adminUser) {
            $this->error('Geen admin gevonden.');

            return self::FAILURE;
        }

        $rw24 = Character::where('name', 'RW24')->first();
        if (! $rw24) {
            $this->error('Speler RW24 niet gevonden.');

            return self::FAILURE;
        }

        // 1. Compensate RW24: +75 VIP points, +50 stars, activate 24h cash protection
        $rw24->vip_points = ($rw24->vip_points ?? 0) + 75;
        $rw24->stars = ($rw24->stars ?? 0) + 50;
        $rw24->cash_protection_until = now()->addHours(24);
        $rw24->save();

        GameNotification::create([
            'character_id' => $rw24->id,
            'type' => 'reward',
            'icon' => 'fa-shield-halved',
            'title' => '🛡️ 24u Cash Bescherming & VIP Compensatie Ontvangen!',
            'message' => 'Je hebt +75 VIP-punten, +50 Sterren en 24 uur Cash Bescherming ontvangen als reactie op je support tickets.',
            'is_read' => false,
        ]);

        $this->info("RW24 gecompenseerd: +75 VIP-punten (nu: {$rw24->vip_points}), +50 sterren (nu: {$rw24->stars}), protection_until: {$rw24->protection_until}");

        // 2. Answer Ticket TBS-SNU-774 (VIP Diefstal Immuniteit Bug)
        $t1 = SupportTicket::where('ticket_number', 'TBS-SNU-774')->first();
        if ($t1) {
            $msg1 = "Beste RW24,\n\n"
                ."Allereerst onze oprechte excuses voor het ongemak! We hebben dit direct tot op de bodem onderzocht en je had helemaal gelijk:\n\n"
                ."In het vechtsysteem op de Luchtplaats werd de actieve cashbescherming wel al keurig gehandhaafd, maar in het diefstalsysteem (zakkenrollen / overvallen) bleek de controle op actieve cashbescherming over het hoofd te zijn gezien. Hierdoor konden andere spelers alsnog proberen je zakken te rollen.\n\n"
                ."**Wat we zojuist voor je hebben gedaan:**\n"
                ."1. **Bug opgelost:** De code is zojuist gerepareerd en direct naar de live server gepusht. Actieve cash-bescherming blokkeert nu 100% waterdicht alle diefstal- en vechtpogingen van andere spelers.\n"
                ."2. **Compensatie:** We hebben je 50 VIP-punten direct teruggestort én 25 extra VIP-punten als beloning en dank voor het melden bijgeschreven (+75 VIP-punten in totaal op je saldo).\n"
                ."3. **Gratis bescherming:** We hebben je 24 Uur Cash Bescherming zojuist opnieuw gratis voor 24 uur geactiveerd op je personage!\n\n"
                ."Nogmaals hartelijk dank voor je oplettendheid en het melden via dit ticket. Veel speelplezier in de kliniek!\n\n"
                ."Met vriendelijke groet,\n"
                .'Kliniek Directie & Support';

            SupportTicketMessage::create([
                'support_ticket_id' => $t1->id,
                'user_id' => $adminUser->id,
                'character_id' => $adminUser->character?->id,
                'is_staff' => true,
                'message' => $msg1,
            ]);

            $t1->update([
                'status' => 'beantwoord',
                'last_reply_at' => now(),
                'last_reply_by_staff' => true,
            ]);

            $this->info('Ticket TBS-SNU-774 beantwoord.');
        }

        // 3. Answer Ticket TBS-YXJ-967 (Suggestie Auto-Consume)
        $t2 = SupportTicket::where('ticket_number', 'TBS-YXJ-967')->first();
        if ($t2) {
            $msg2 = "Beste RW24,\n\n"
                ."Wat een ontzettend goede en creatieve suggestie! \n\n"
                ."We hebben hier direct naar gekeken. We hebben inmiddels in de navigatiebalk een snelle-consumptieknop toegevoegd waarmee je met één klik direct eten/drinken uit je rugzak nuttigt, maar jouw idee om een permanente 'Auto-Consume' ability / VIP-perk vrij te spelen (waarbij je energie na heists of acties automatisch aanvult zolang je snacks zoals Frikandel XXL in je rugzak hebt) is een fantastische toevoeging!\n\n"
                ."We hebben jouw voorstel genoteerd op de feature-roadmap voor de komende gameplay-update. Als dank voor het meedenken en je waardevolle feedback hebben we zojuist **50 Sterren ⭐** direct aan je profiel toegevoegd!\n\n"
                ."Blijf vooral meedenken, dat waarderen we enorm!\n\n"
                ."Met vriendelijke groet,\n"
                .'Kliniek Directie & Support';

            SupportTicketMessage::create([
                'support_ticket_id' => $t2->id,
                'user_id' => $adminUser->id,
                'character_id' => $adminUser->character?->id,
                'is_staff' => true,
                'message' => $msg2,
            ]);

            $t2->update([
                'status' => 'beantwoord',
                'last_reply_at' => now(),
                'last_reply_by_staff' => true,
            ]);

            $this->info('Ticket TBS-YXJ-967 beantwoord.');
        }

        return self::SUCCESS;
    }
}
