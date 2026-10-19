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
        $ticket = SupportTicket::where('ticket_number', $ticketNumber)
            ->orWhere('ticket_number', 'like', "%{$ticketNumber}%")
            ->orWhere('id', $ticketNumber)
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
            ->first();

        $danielCharacter = $danielUser?->character ?? Character::where('name', 'like', '%daniel%')->first();

        if ($ticketNumber === 'TBS-SQM-770' || $ticketNumber === 'TBS-SQN-778' || $ticket->id === 13 || str_contains(strtolower($ticket->subject), 'grote heists') || (str_contains(strtolower($ticket->subject), 'bende') && str_contains(strtolower($ticket->subject), 'heist'))) {
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
