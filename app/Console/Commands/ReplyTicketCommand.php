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
    protected $signature = 'ticket:auto-reply {ticket_number}';

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
        $ticket = SupportTicket::where('ticket_number', $ticketNumber)
            ->orWhere('ticket_number', 'like', "%{$ticketNumber}%")
            ->orWhere('id', $ticketNumber)
            ->orWhere('subject', 'like', "%{$ticketNumber}%")
            ->orWhereHas('character', function ($q) use ($ticketNumber) {
                $q->where('name', 'like', "%{$ticketNumber}%");
            })
            ->first();

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
            ->first() ?? User::first();

        if (! $danielUser) {
            $this->error('Geen staff user gevonden voor auto-reply.');

            return Command::FAILURE;
        }

        $danielCharacter = $danielUser->character ?? Character::where('name', 'like', '%daniel%')->first() ?? Character::first();

        if ($ticket->id == 35 || str_contains(strtolower($ticket->subject), 'verwijder') || str_contains(strtolower($ticket->subject), 'account verqijderen')) {
            $replyMessage = "Beste {$ticket->character?->name},\n\n"
                ."Bedankt voor je bericht via dit ticket inzake je account.\n\n"
                ."Je mag voor het definitief laten verwijderen van je account en alle bijbehorende gegevens een e-mail sturen naar **privacy@tbs-kliniek.nl** vanaf het e-mailadres dat aan jouw account gekoppeld is.\n\n"
                ."Zodra wij jouw e-mail hebben ontvangen en geverifieerd, zullen wij conform de Algemene Verordening Gegevensbescherming (AVG) jouw account en alle persoonsgegevens binnen de wettelijke termijn definitief wissen uit onze systemen.\n\n"
                ."Mocht je in de tussentijd nog vragen hebben, laat het ons dan gerust weten.\n\n"
                ."Met vriendelijke groet,\n"
                .'Daniël • TBS Kliniek Leiding & Privacy';
        } elseif ($ticketNumber === 'TBS-CJS-614' || str_contains(strtolower($ticket->subject), 'afkoelen') || str_contains(strtolower($ticket->character?->name ?? ''), 'carootje')) {
            $replyMessage = "Beste carootje,\n\n"
                ."Ontzettend bedankt voor je melding en scherpe observatie via dit ticket! ⏱️🔥\n\n"
                ."Je had helemaal gelijk: voorheen bleven de cooldown timers op het scherm statisch stilstaan op de server-tijd waarop de pagina geladen was. Als je wachtte tot je weer een actie mocht uitvoeren, liep de timer in de browser niet realtime af en bleven de actieknoppen vergrendeld totdat je de pagina handmatig ververste. Dat speelde natuurlijk niet fijn als je even zat te wachten.\n\n"
                ."We hebben dit zojuist direct grondig aangepakt en opgelost voor de gehele game:\n\n"
                ."1. **Realtime Aflopende Cooldowns**:\n"
                ."   - Alle acties met een cooldown wachttijd (zoals **Zakkenrollen**, **Afpersen**, **Hosselen**, **Kluisjes Kraken**, **Pakketjes Onderscheppen**, **Smokkelproducties** en **Bende Oorlog**) tellen nu direct elke seconde live en realtime af in je browser.\n"
                ."   - Je ziet de minuten en seconden nu vloeiend aftikken (bijv. 02:59 -> 02:58 -> ...).\n\n"
                ."2. **Automatische Ontgrendeling zonder Herladen**:\n"
                ."   - Zodra de timer 00:00 bereikt, verdwijnt de cooldown banner direct vanzelf en springen alle actieknoppen automatisch op actief ('Klaar voor actie').\n"
                ."   - Je hoeft de pagina dus NOOIT meer handmatig te verversen (F5); zodra je cooldown voorbij is kun je direct doorklikken en verder spelen!\n\n"
                ."3. **Gesynchroniseerd met Cooldown Skip via Sponsor Ads**:\n"
                ."   - Ook wanneer je tussentijds een sponsor video bekijkt om je cooldown te resetten, ontgrendelt alles direct.\n\n"
                ."Nogmaals enorm bedankt voor het melden van deze bug, dankzij jouw ticket is de ervaring voor alle spelers in de TBS Kliniek nu een stuk aangenamer en vlotter geworden!\n\n"
                ."Veel succes en speelplezier in de kliniek!\n\n"
                ."Met vriendelijke groet,\n"
                .'Daniel • TBS Kliniek Leiding';
        } elseif ($ticketNumber === 'TBS-UPB-767' || $ticket->id === 767 || (str_contains(strtolower($ticket->subject), 'terminal') && (str_contains(strtolower($ticket->subject), 'kliniek') || str_contains(strtolower($ticket->character?->name ?? ''), 'arjan')))) {
            $replyMessage = "Beste {$ticket->character?->name},\n\n"
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
        } elseif ($ticketNumber === 'TBS-GE4-318' || $ticket->id === 14 || str_contains(strtolower($ticket->subject), 'hacking') || str_contains(strtolower($ticket->subject), 'computer')) {
            $replyMessage = "Beste Rainbow,\n\n"
                ."Wat een fenomenaal en vernieuwend idee voor TBS Kliniek! Een echte interactieve cyber-terminal met minigames en een beloningsrad sluit perfect aan bij de mysterieuze, clandestiene sfeer van de kliniek. We hebben jouw complete concept direct ontworpen, geprogrammeerd en zojuist live gezet onder 'Bonus & Kluizen' -> 'Kliniek Terminal' (/hacking)! 💻⚡🔓\n\n"
                ."Dit is wat we allemaal exact volgens jouw wensen hebben gerealiseerd:\n\n"
                ."1. **Computer Terminal Beeld (Cyber Terminal)**:\n"
                ."   - Een sfeervol groen/cyaan retro-hacker computerscherm met terminal glow, live system diagnostics en terminal audio/visuele feedback.\n"
                ."   - Je krijgt **dagelijks 1 keer de kans** om een terminal inbraak uit te voeren.\n\n"
                ."2. **3 Puzzels per dag met Oplopende Moeilijkheid & 1 Minuut Countdown Timer**:\n"
                ."   - Precies zoals gevraagd: je hebt **telkens exact 60 seconden** per puzzel om de beveiliging te omzeilen voordat het alarm afgaat!\n"
                ."   - **Puzzel 1 (Firewall Omzeilen)**: Vind en klik de juiste hexadecimale encryptie-sleutels in het beveiligingsraster.\n"
                ."   - **Puzzel 2 (Cypher Poorten Decryptie)**: Schakel de 4 synchrone data-poorten in op de vereiste doel-frequenties.\n"
                ."   - **Puzzel 3 (Hoofdserver Root Access)**: Verbind het stroomcircuit en activeer de 3 hoofdprocessoren om volledige admin override te bereiken!\n\n"
                ."3. **Hacker Spinner met 10 Unieke Kansen**:\n"
                ."   - Zodra je alle 3 de puzzels binnen de tijd hebt gekraakt, ontgrendelt direct het 10-delige neon **Hacker Spinner** beloningsrad:\n"
                ."     1. 💶 **€ 25.000 Cash**\n"
                ."     2. 💼 **1x Smokkelkoffer** (direct aan je depot toegevoegd)\n"
                ."     3. 🗝️ **1x Unieke Kluiscode** (direct bruikbaar in de centrale MedicijnKluis)\n"
                ."     4. 🧳 **1x XXL Koffer**\n"
                ."     5. ⭐ **5x VIP Punten / Sterren**\n"
                ."     6. 💰 **€ 75.000 Mega Buit**\n"
                ."     7. 🚬 **15x Sigaretten**\n"
                ."     8. 🎭 **4x Heist Maskers**\n"
                ."     9. 📱 **3x Smokkel Telefoons**\n"
                ."     10. 💊 **10x Ritalin Strips**\n\n"
                ."4. **Exclusieve VIP Bonus Feature**:\n"
                ."   - Precies zoals je vroeg: spelers met een actieve **VIP status** ontvangen bij elke succesvolle draai een **exclusieve VIP Bonus** *bovenop* hun gewonnen prijs!\n"
                ."   - Als VIP speler krijg je direct: **+€25.000 extra cash**, **+3 extra VIP Sterren ⭐**, **+1 extra XXL Koffer 🧳** én **+4 extra Heist Maskers 🎭** bekroond met een gouden koninklijke badge!\n\n"
                ."5. **Directe Koppeling in het Menu**:\n"
                ."   - In het hoofdmenu onder 'Bonus & Kluizen' vind je nu direct **'Kliniek Terminal'**. Als je dagelijkse hack nog klaarstaat, zie je er een pulserende badge bij branden!\n\n"
                ."Nogmaals ontzettend bedankt voor je creatieve input en je enthousiasme voor TBS Kliniek. Kruip achter het toetsenbord, versla de 60-seconden klok en draai aan dat rad!\n\n"
                ."Met vriendelijke groet,\n"
                .'Daniel • TBS Kliniek Leiding';
        } elseif ($ticketNumber === 'TBS-SQM-770' || $ticketNumber === 'TBS-SQN-778' || $ticket->id === 13 || str_contains(strtolower($ticket->subject), 'grote heists') || (str_contains(strtolower($ticket->subject), 'bende') && str_contains(strtolower($ticket->subject), 'heist'))) {
            $replyMessage = "Beste Rainbow,\n\n"
                ."Wat een meesterlijk idee! Het koppelen van bendeleden quota, vereiste bende panden én bendevoertuigen uit de garage brengt het hele bendesysteem samen tot één gigantisch co-op spektakel. We hebben je concept direct volledig uitgewerkt en zojuist live gezet in TBS Kliniek! 💣💰🏎️\n\n"
                ."Dit is hoe de nieuwe Grote Bende Heists nu exact werken volgens jouw voorstel:\n\n"
                ."1. **Bendeleden Quota Systeem (2+, 3+, 5+ en 10+ Leden)**:\n"
                ."   - **Tier 1 (2+ Leden)**: *DVO Celtransport Onderschepping* en de nieuwe *Explosieve Geldautomaat Plofkraak* (€220.000 – €480.000 buit).\n"
                ."   - **Tier 2 (3+ Leden)**: *Holland Casino Cash Vault* en het *Underground Chemisch Smokkeldepot* (€550.000 – €1.150.000 buit).\n"
                ."   - **Tier 3 (5+ Leden)**: *Douane Haven Containeroverslag Rotterdam* en de *Internationale Diamantenconvooi Overval* (€1.350.000 – €2.800.000 buit).\n"
                ."   - **Tier 4 (10+ Leden - Syndicate Masterpiece)**: *DNB A2 Goudtransport* en de ultieme *Centrale Reservereden Fort Goudkluis Heist* (tot maar liefst €8.500.000 buit + 3.500 Bende Respect)!\n\n"
                ."2. **Benodigde Bende Panden (Gekoppeld aan /gangs/panden)**:\n"
                ."   - Precies zoals je vroeg: grote bende heists kunnen enkel gepland en gelanceerd worden als jullie bende het juiste vastgoedobject bezit:\n"
                ."     • Loods of Garagebox Complex voor de ramkraken\n"
                ."     • Chemisch Smokkel-Lab of Fabriek voor de chemische raid\n"
                ."     • Oude Scheepswerf & Havenloods voor de havenoverval\n"
                ."     • Zwaar Transport Terminal of Smuggle Vliegveld Hangar voor het convooi\n"
                ."     • Ondergrondse Bunker of Gang Villa HQ voor de ultieme nationale goudkraak!\n\n"
                ."3. **Benodigde Vluchtwagens & Directe Bende Garage Koppeling**:\n"
                ."   - Elke heist stelt specifieke eisen aan het vluchtvoertuig (auto, snelle speedboot, helikopter of gepantserde truck, topsnelheid en conditie).\n"
                ."   - **Super co-op feature**: Bezit je zelf niet het vereiste voertuig? Geen probleem! Als een bendelid een geschikte wagen heeft gestald in de gezamenlijke **Bende Garage**, kan die wagen direct worden ingezet voor de bende heist!\n\n"
                ."4. **50/50 Buitverdeling & Bende Respect**:\n"
                ."   - Bij succes ontvangt de uitvoerder direct 50% persoonlijke contanten, wordt de overige 50% direct gestort in de gezamenlijke bendekas én stijgt jullie benderanking met honderden tot duizenden Respectpunten!\n\n"
                ."5. **Directe Menu Koppelingen**:\n"
                ."   - In het hoofdmenu onder 'Bende & Gangs' vind je nu directe knoppen naar zowel de **Bende Garage** als **Bende Heists** (/heists?tab=gang).\n\n"
                ."Nogmaals ontzettend bedankt voor je fantastische visie en het actief meebouwen aan TBS Kliniek. Roep je bendeleden bij elkaar, check jullie panden en garage, en kraak die kluizen!\n\n"
                ."Met vriendelijke groet,\n"
                .'Daniel • TBS Kliniek Leiding';
        } elseif ($ticketNumber === 'TBS-OAE-551' || $ticket->id === 12 || str_contains(strtolower($ticket->subject), 'aanpasbare profielen') || str_contains(strtolower($ticket->subject), 'profiel')) {
            $replyMessage = "Beste Rainbow,\n\n"
                ."Wat een geweldige feature suggestie! Profielen zijn het visitekaartje van iedere speler in TBS Kliniek, en de wens voor meer creativiteit en personalisatie sluit daar perfect op aan. We hebben jouw complete concept direct opgepakt, ontworpen en zojuist volledig live gezet in de game! 🎨✨\n\n"
                ."Dit is wat we allemaal exact hebben geïmplementeerd:\n\n"
                ."1. **'Profiel Personaliseren' Knop (Rechtsboven)**:\n"
                ."   - Wanneer je op je eigen profielpagina (/profiel) bent, vind je rechtsboven nu direct de paars/roze knop **'Profiel Personaliseren'**.\n"
                ."   - Hiermee open je een gloednieuw interactief Personalisatie Hub modal waarin je al je instellingen overzichtelijk beheert.\n\n"
                ."2. **Stickers Showcase Slot & Sticker Inventaris**:\n"
                ."   - Op je profielkaart vind je nu een speciaal **Sticker Showcase Slot** waar je gekozen sticker trots schittert naast je patiëntendossier.\n"
                ."   - In het personalisatiemenu kun je kiezen uit je sticker-inventaris met unieke icoontjes (o.a. Doodskop 💀, Handboeien ⛓️, Gouden Kroon 👑, Schaduw Dolk 🗡️, Jack-o'-Lantern 🎃, VIP Ster ⭐, Bende Buidel 💰 en meer).\n"
                ."   - Je sticker heeft een interactieve hover-tooltip en geeft direct karakter aan je profiel!\n\n"
                ."3. **Aanpasbare Achtergrondthema's & Accentkleuren**:\n"
                ."   - Je kunt nu kiezen uit **8 unieke achtergrondstijlen & sferen**:\n"
                ."     • Standaard Kliniek Donker (Sleek Slate)\n"
                ."     • Middernacht Neon (Deep Indigo & Violet)\n"
                ."     • Crimson Bloedbad (Dark Red & Crimson Glow)\n"
                ."     • Toxic Emerald (Clandestiene Groene Gloed)\n"
                ."     • Cartel Goud (Luxe Amber & Gouden Tinten)\n"
                ."     • Marineblauw Fort (High-Security Blue)\n"
                ."     • Cyberpunk Neon (Futuristische Pink & Violet Glow)\n"
                ."     • Halloween Night (Spooky Oranje & Paarse Mist)\n"
                ."   - Daarnaast kun je met één klik je favoriete neon accentkleur kiezen of een custom hex code invoeren.\n\n"
                ."4. **Gastenboek met Eigenaar Beheerrechten**:\n"
                ."   - Iedereen die jouw profiel passeert kan een felicitatie, waarschuwing of shout-out achterlaten in jouw Gastenboek.\n"
                ."   - Als eigenaar van het profiel heb jij nu volledige controle: bij elk bericht staat een rode prullenbak/verwijderknop waarmee je ongewenste berichten direct uit je gastenboek wist!\n\n"
                ."5. **Titels, Tags & Dossier Status**:\n"
                ."   - Toon je actieve bendetag, rol of specialisatie (zoals Slimme Hosselaar, Huurmoordenaar, Zware Crimineel of Veteraan) direct onder je naam.\n"
                ."   - Pas ook direct je persoonlijke bio en statusbericht aan via dezelfde hub.\n\n"
                ."Nogmaals ontzettend bedankt voor je fantastische suggestie en je actieve bijdrage om TBS Kliniek leuker en interactiever te maken! Neem snel een kijkje op je profiel om jouw eigen unieke stijl in te stellen.\n\n"
                ."Met vriendelijke groet,\n"
                .'Daniel • TBS Kliniek Leiding';
        } elseif ($ticketNumber === 'TBS-RRK-331' || $ticketNumber === 'TBS-B8K-331' || $ticket->id === 11 || str_contains(strtolower($ticket->subject), 'garage update') || (str_contains(strtolower($ticket->subject), 'panden') && str_contains(strtolower($ticket->subject), 'garage'))) {
            $replyMessage = "Beste Rainbow,\n\n"
                ."Wauw, wat een geweldige reeks ideeën en suggesties! We hebben je feedback van A tot Z direct opgepakt en zojuist volledig gebouwd en live gezet in de game! 🚗🏢👥\n\n"
                ."Dit is wat we allemaal exact hebben geïmplementeerd:\n\n"
                ."1. **Nieuwe 'Bende Garage' & Gezamenlijk Wagenpark (/gangs/garage)**:\n"
                ."   - In het Bende Menu vind je nu de nieuwe knop **Bende Garage** (inclusief live teller van de bezetting: gestald / totaal).\n"
                ."   - Bendeleden kunnen hun eigen voertuigen nu met één klik stallen in de gezamenlijke Bende Garage.\n"
                ."   - Eigenaars en bendeleiders kunnen gestalde voertuigen op elk moment weer eenvoudig terughalen naar hun persoonlijke wagenpark.\n"
                ."   - Het overzicht toont overzichtelijk wie welke wagen heeft gestald, de conditie, effectieve snelheid en statistieken!\n\n"
                ."2. **Directe Koppeling met Garage & Opslag Panden**:\n"
                ."   - Precies zoals je aangaf: bendes krijgen enkel en alleen meer stallingsruimte door het aankopen van garage- en opslagpanden!\n"
                ."   - Elk pand levert nu specifieke wagenplekken op:\n"
                ."     • Kleine Loods & Garage: 2 wagenplekken\n"
                ."     • Ondergronds Garageboxen Complex: 4 wagenplekken\n"
                ."     • Oude Scheepswerf & Havenloods: 6 wagenplekken\n"
                ."     • Industriële Distributieloods: 8 wagenplekken\n"
                ."     • Chop-Shop Garage: 12 wagenplekken\n"
                ."     • Zwaar Transport Terminal: 16 wagenplekken\n"
                ."     • Smuggle Vliegveld Hangar: 20 wagenplekken\n"
                ."     • Ondergrondse Bunker & Forteiland: tot maar liefst 25 zware wagenplekken!\n\n"
                ."3. **30-Minuten Rotatie voor Panden (net als de Smokkel Garage)**:\n"
                ."   - Op **/gangs/panden** vind je nu bovenaan de **30-Minuten Panden Rotatie Showroom** met live countdown timer (:00 en :30 van elk uur).\n"
                ."   - Elk half uur verschijnt een nieuwe selectie van 8 unieke panden met tijdelijke rotatie-kortingen tot wel 15% die met de bendekas aangeschaft kunnen worden!\n"
                ."   - Het pandenaanbod is fors uitgebreid naar meer dan 30 verschillende vastgoedobjecten verdeeld over garages, teelt & fabrieken, stripclubs, casino suites en burchten.\n\n"
                ."4. **100+ Unieke NPC Rekruten in de Rotatie**:\n"
                ."   - De NPC pool is uitgebreid van slechts 3 naar een gigantische database van **ruim 115 unieke onderwereld personages** (met eigen bijnamen, achtergronden en specialisaties)!\n"
                ."   - Elk half uur roteert een team van 6 verschillende specialisten de showroom in:\n"
                ."     • Productie & Oogst Managers (+15% tot +45% extra cash opbrengst)\n"
                ."     • Proces & Snelheidsmeesters (-15% tot -40% productietijd)\n"
                ."     • Zwaarbewapende Enforcers (+12% tot +35% verdediging bij bendeoorlogen)\n"
                ."     • Witwassers & Financieel Adviseurs (+5% tot +18% bende uurloon bonus)\n"
                ."     • Fleet Specialisten & Monteurs (-20% tot -50% reparatiekosten in de garage)\n"
                ."     • Logistiek Havenmeesters (+25 tot +120 extra bende opslagruimte)\n\n"
                ."Nogmaals ontzettend bedankt voor je fantastische bijdrage en het meedenken om TBS Kliniek nog completer te maken. Heel veel succes met het uitbreiden van het Rainbow Bloodz wagenpark!\n\n"
                ."Met vriendelijke groet,\n"
                .'Daniel • TBS Kliniek Leiding';
        } elseif ($ticketNumber === 'TBS-JCG-100' || $ticket->id === 10 || str_contains(strtolower($ticket->subject), 'controlepunt') || str_contains(strtolower($ticket->subject), 'controle punt')) {
            $replyMessage = "Beste Rainbow,\n\n"
                ."Wat een waanzinnig goed King-of-the-Hill concept voor de TBS Kliniek! We vonden het idee zó vet dat we het direct van top tot teen hebben gebouwd en zojuist live hebben gezet in de game! 🚩👑\n\n"
                ."Dit is hoe het nieuwe Controlepunten systeem nu exact werkt zoals jij hebt voorgesteld:\n\n"
                ."1. **5 Strategische Controlepunten in de Kliniek**:\n"
                ."   - **De Luchtplaats**: Heers over de open buitenplaats.\n"
                ."   - **De Kantine**: Beheers het centrale ontmoetingspunt van de kliniek.\n"
                ."   - **De Sportzaal**: Laat zien wie de sterkste heerser is in de gym.\n"
                ."   - **Centraal Depot**: Controleer alle goederen en voorraadstromen.\n"
                ."   - **De Medicijnkluis**: Grijp de macht over de zwaarst beveiligde kluis.\n\n"
                ."2. **Opeisen & Veroveren (2⚡ Energie)**:\n"
                ."   - Via de nieuwe pagina **/controlepunten** (en de interactieve controlepunt-banners op de locaties zelf) kun je met één klik een punt opeisen of afpakken van een rivaal.\n"
                ."   - Zolang jij op het controlepunt staat, blijft het stevig in jouw bezit en stijgt je buit!\n\n"
                ."3. **Stijgende Buit per Minuut (+€50 & +1 Ster ⭐)**:\n"
                ."   - Exact volgens jouw formule:\n"
                ."     • 1 minuut = €50 + 1 Ster ⭐\n"
                ."     • 2 minuten = €100 + 2 Sterren ⭐\n"
                ."     • 10 minuten = €500 + 10 Sterren ⭐\n"
                ."     • 60 minuten = €3.000 + 60 Sterren ⭐!\n\n"
                ."4. **Automatische Uitbetaling & Notificatie**:\n"
                ."   - Zodra een andere speler het punt van jou overneemt, stopt de teller en wordt de volledige opgebouwde buit (cash én sterren) direct op jouw saldo bijgeschreven!\n"
                ."   - Je ontvangt direct een in-game notificatie én een officieel uitbetalingsbericht in je postvak met het complete overzicht van jouw heerschappij.\n\n"
                ."5. **Bende Eer & Live Overname Feed**:\n"
                ."   - Als je bij een bende zit, schittert jouw bendetag als heersende bende over dat punt.\n"
                ."   - Onderaan de pagina vind je een live historiek met alle recente overnames, heerschappij-tijden en uitbetaalde beloningen.\n\n"
                ."Nogmaals enorm bedankt voor dit gave idee en het meebouwen aan een levendige TBS Kliniek! Ga snel naar 'Controlepunten' in het Bende-menu of surf naar /controlepunten om je eerste hotspot te claimen!\n\n"
                ."Met vriendelijke groet,\n"
                .'Daniel • TBS Kliniek Leiding';
        } elseif ($ticketNumber === 'TBS-BKI-972' || $ticket->id === 9 || str_contains(strtolower($ticket->subject), 'krachttraining')) {
            $replyMessage = "Beste ozzy,\n\n"
                ."Bedankt voor je melding en scherpe oog! We hebben dit probleem zojuist grondig aangepakt en opgelost:\n\n"
                ."1. **In-place Krachttraining (Geen Paginaverspringing meer)**:\n"
                ."   - Krachttraining voert upgrades nu volledig vloeiend 'in-place' uit via asynchrone updates.\n"
                ."   - De pagina herlaadt niet meer na elke klik, waardoor het scherm en het zijbalkmenu ('Voortgang') niet meer onverwacht verspringen of inklappen.\n"
                ."   - Je statistieken, benodigde cash, energie en saldo worden direct live en soepel bijgewerkt met duidelijke subtiele feedback.\n\n"
                ."2. **Snelle +5x Upgrade Knop**:\n"
                ."   - Om het trainen nog vlotter te maken, is er naast de 'Trainen (+1)' knop nu ook direct een snelle '+5x' knop toegevoegd waarmee je in één klik 5 punten tegelijkertijd traint.\n\n"
                ."3. **Realtime Status Bovenbalk**:\n"
                ."   - Zowel je energie- als geldsaldo in de bovenbalk worden nu direct synchroon bijgewerkt bij elke training.\n\n"
                ."Nogmaals dank voor het melden van deze bug en je waardevolle feedback. Veel succes met het opbouwen van je statistieken!\n\n"
                ."Met vriendelijke groet,\n"
                .'Daniel • TBS Kliniek Leiding';
        } elseif ($ticketNumber === 'TBS-ZVV-887' || $ticketNumber === 'TBS-7VV-RR7' || $ticket->id === 8 || str_contains(strtolower($ticket->subject), 'marktplaats')) {
            $replyMessage = "Beste Rainbow,\n\n"
                ."Heel scherp opgemerkt! We hebben de Marktplaats zojuist een grote update gegeven zodat alle nieuwe spelonderdelen nu volledig verhandelbaar zijn tussen spelers onderling:\n\n"
                ."1. **Depot Items Verkopen**:\n"
                ."   - Je kunt nu direct al je goederen, wapens, bescherming en zeldzame buit uit je persoonlijke Depot te koop aanbieden aan medepatiënten.\n"
                ."   - Je bepaalt zelf het aantal en de vraagprijs (in contanten of sterren).\n\n"
                ."2. **Garage Voertuigen Verhandelen**:\n"
                ."   - Je kunt nu al je vluchtwagens, speedboten en helikopters uit je Smokkel Garage te koop zetten op de Marktplaats!\n"
                ."   - De actuele conditie (%) en topsnelheid worden overzichtelijk getoond aan potentiële kopers.\n"
                ."   - Zodra een speler je wagen koopt, wordt deze direct overgedragen naar diens garage en ontvang jij het aankoopbedrag direct op je bank!\n\n"
                ."3. **Bende Panden Verhandelen**:\n"
                ."   - Bendeleiders en beheerders kunnen nu ook aangekochte Bende Panden (loodsen, bunkers, fabrieken en villa's) te koop aanbieden op de Marktplaats.\n"
                ."   - Bij verkoop wordt de volledige opbrengst automatisch direct bijgeschreven in de gezamenlijke bendekas (treasury)!\n\n"
                ."4. **Nieuwe Filters & Snelle Koppelingen**:\n"
                ."   - Op de Marktplaats vind je nu handige filters voor Depot Items, Garage Voertuigen en Bende Panden.\n"
                ."   - Ook zijn er directe snelkoppelingen toegevoegd in je Depot, Garage en Bende Panden overzicht naar de Marktplaats.\n\n"
                ."Nogmaals dank voor het meedenken en je waardevolle feedback. Veel succes met handelen!\n\n"
                ."Met vriendelijke groet,\n"
                .'Daniel • TBS Kliniek Leiding';
        } elseif ($ticketNumber === 'TBS-U3M-955' || str_contains(strtolower($ticket->subject), 'halloween')) {
            $replyMessage = "Beste Rainbow,\n\n"
                ."Wat een geweldig idee! We zijn direct aan de slag gegaan en hebben het complete **Halloween Event** zojuist live gezet in de TBS Kliniek! 🎃🍬\n\n"
                ."Dit is wat we voor jullie hebben geïmplementeerd:\n\n"
                ."1. **Nieuwe EVENT Categorie in het Menu**:\n"
                ."   - In het hoofdmenu en zijbalk vind je nu direct de speciale **🎃 EVENT** categorie (actief t/m 5 november 23:59).\n\n"
                ."2. **Halloween Quests (Dagelijks & Wekelijks)**:\n"
                ."   - **Dagelijkse Quests**: Zoals zakkenrollerijen / diefstallen, luchtplaats brawls, hossels en fitness trainingen om elke dag verse pitten te verdienen.\n"
                ."   - **Wekelijkse Quests**: Grote uitdagingen zoals 'Griezelige Zakkenroller Meester', 'Het Grote Kliniek Bloedbad' (8 gevechten winnen), 'Het Grote Pompoen Fortuin' (€35.000 buit) en 'Heerser van de Onderwereld'.\n"
                ."   - Alle quests belonen je met **Halloween Pompoenpitten 🎃**, extra XP en Respect!\n\n"
                ."3. **Halloween Shop**:\n"
                ."   - **Exclusieve Avatar Profielranden**: Pimp je avatar met *Spooky Pumpkin Glow*, *Vampire Blood*, *Phantom Mist* en *Gothic King* (inclusief neon glow effects op je profiel en header)!\n"
                ."   - **Halloween Thema Hebbedingetjes & Collectibles**: Verzamel *Jack-O'-Lantern Pompoenen*, *Spookachtig Snoepgoed* en een *Vampier Vleermuis in Kooitje* voor je cel/inventaris.\n"
                ."   - **Krachtige Halloween Boosts**: Scoor het *Groene Heksenbrouwsel* (volledig HP herstel + 75 Energie), een *Jack-O'-Lantern Masker Bundel* (5x ongemerkt zakkenrollen) en de *Magere Hein Zegening* (24 uur lang +15% Vecht- en Verdedigingskracht)!\n\n"
                ."Heel erg bedankt voor het meedenken en je toffe suggestie om de sfeer in de kliniek nog spannender te maken! Heel veel succes en griezelplezier tijdens het evenement.\n\n"
                ."Met vriendelijke groet,\n"
                .'Daniel • TBS Kliniek Leiding';
        } elseif ($ticketNumber === 'TBS-565-167' || str_contains(strtolower($ticket->subject), 'panden')) {
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
        } elseif ($ticketNumber === 'TBS-A9M-535' || $ticket->id === 60 || str_contains(strtolower($ticket->subject), 'uitbreektunnel') || str_contains(strtolower($ticket->subject), 'tunnel') || str_contains(strtolower($ticket->character?->name ?? ''), 'whoopsie')) {
            $replyMessage = "Beste Whoopsie,\n\n"
                ."Wat een fantastische suggestie en super leuk dat je zo actief meedenkt met TBS Kliniek! 🥄🔥\n\n"
                ."Je had helemaal gelijk: zodra je de tunnel voorheen voltooide, zat je 'vast' met een dichtgestort gat en hield de bezigheid op. Zonde natuurlijk voor doorgewinterde spelers zoals jij!\n\n"
                ."We hebben jouw complete voorstel zojuist direct uitgewerkt en live gezet in de game:\n\n"
                ."1. **Uitbraaktunnel Reset Systeem**:\n"
                ."   - Zodra je tunnel voltooid is, vind je nu direct de knop **'Start Nieuwe Uitbraaktunnel (Reset)'**.\n"
                ."   - Hiermee sluip je stilletjes terug naar je cel en hang je een nieuwe poster op. Je kunt direct weer lepel voor lepel beginnen vanaf Etappe 1!\n"
                ."   - Je totale aantal succesvolle ontsnappingen blijft netjes bewaard en telt door.\n\n"
                ."2. **3 Uur Voortvluchtig Buiten de Muren**:\n"
                ."   - Net zoals bij Begeleid Verlof ben je na een succesvolle uitbraak nu **3 uur lang Voortvluchtig** buiten de kliniekmuren!\n"
                ."   - Bovenin de tunnelpagina zie je een live aftellende timer waarin de bewaking naar je zoekt.\n\n"
                ."3. **3 Exclusieve Uitbraak Heists & Buiten-Overvallen**:\n"
                ."   - Zolang je buiten de hekken bent, krijg je toegang tot 3 unieke heists:\n"
                ."     • 🏠 **Onderduikers Safehouse Kraak**: Plunder een verlaten onderduikadres in het sparrenbos (€5.000 – €12.500 buit + 120 XP).\n"
                ."     • 🏛️ **Kliniekdirecteur Residentie Inbraak**: Sluip de privévilla van de directie binnen voor geheime dossiers en kluisbuit (€20.000 – €45.000 + 250 XP en VIP Sterren ⭐).\n"
                ."     • 🚛 **Geldtransport Onderschepping**: Onderschep het waardetransport over de provinciale weg voor brute megabuit (€50.000 – €100.000 + 450 XP en kans op Smokkelkoffers 🧳)!\n\n"
                ."Omdat jij de tunnel al voltooid had, staat de resetknop op /ontsnappingstunnel direct voor je klaar. Je kunt dus meteen weer aan de slag met een verse tunnel!\n\n"
                ."Nogmaals ontzettend bedankt voor je toffe idee en je enthousiaste woorden over het spel. Heel veel succes met graven en plunderen!\n\n"
                ."Met vriendelijke groet,\n"
                .'Dave • TBS Kliniek Leiding';
        } elseif ($ticketNumber === 'TBS-7UD-342' || str_contains(strtolower($ticket->subject), '3x geliquideerd') || str_contains(strtolower($ticket->subject), 'anti-pest') || str_contains(strtolower($ticket->character?->name ?? ''), 'dennis')) {
            $replyMessage = "Beste Dennis,\n\n"
                ."Bedankt voor je melding en scherpe oplettendheid via dit ticket! 🎯🛡️\n\n"
                ."We hebben het incident direct voor je nagekeken in de server- en update-logs. De limiet van maximaal 3 liquidaties per dader per dag werkt technisch zoals bedoeld, maar jouw situatie ontstond door de overgang naar de nieuwe update:\n\n"
                ."1. **Tijdstip van de Balans-Update (4 november)**:\n"
                ."   - De grote Balans-Update met het Anti-Pestprotocol is op 4 november rond 10:30-10:48 uur live gezet.\n"
                ."   - Eén van jouw liquidaties vond plaats vóórdat de update live was. De eerdere liquidatie van die ochtend telde daardoor nog niet mee in de nieuwe dagteller.\n"
                ."   - Na de update had de dader op diezelfde kalenderdag nog 3 geldige liquidaties beschikbaar volgens het nieuwe protocol.\n\n"
                ."2. **Dagteller per Kalenderdag (00:00 - 23:59)**:\n"
                ."   - De anti-pestlimiet geldt per kalenderdag en reset elke nacht om 00:00:00 uur middernacht, niet per rollend venster van 24 uur.\n\n"
                ."3. **Automatisch Medisch Schild (5x Liquidaties)**:\n"
                ."   - Het beschermende noodprotocol activeert automatisch bij de 5e liquidatie op één dag. Omdat je op 4 liquidaties zat, was die specifieke bescherming nog net niet van toepassing.\n\n"
                ."Het systeem blokkeert nu wel degelijk verdere pogingen zodra een aanvaller de 3 liquidaties op dezelfde speler binnen één kalenderdag bereikt.\n\n"
                ."Mocht je in de toekomst toch nog onregelmatigheden ervaren, laat het ons gerust weten. Veel succes en speelplezier in de kliniek!\n\n"
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
