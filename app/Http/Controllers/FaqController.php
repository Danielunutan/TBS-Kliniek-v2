<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class FaqController extends Controller
{
    /**
     * Complete FAQ knowledge base categories and questions covering every system in TBS Kliniek.
     */
    public static function getFaqData(): array
    {
        return [
            'beginners' => [
                'title' => ' Beginnersgids: Eerste Stappen & Overleven',
                'icon' => 'fa-graduation-cap',
                'questions' => [
                    [
                        'q' => 'Ik ben nieuw in de TBS Kliniek! Wat moet ik als eerste doen op dag 1?',
                        'a' => "Welkom in de kliniek, patiënt! Volg dit stappenplan om direct een vliegende start te maken zonder risico:\n\n1.  Ga naar 'Voortgang > Missies & Opdrachten'. Voltooi de eerste missies voor makkelijk contant geld en veel ervaringspunten (XP).\n2.  Ga naar 'Voortgang > Ziekenboeg'. Laat je behandelen door Zuster Brenda om je Gezondheid (HP) direct gratis naar 100% te brengen.\n3. ️ Ga naar 'Voortgang > Dagbesteding'. Werk veilig in de wasserette of werkplaats met Werkmeester Henk. Dit levert gegarandeerd eerlijk geld op en je loopt 0% risico op de isoleercel!\n4.  Ga naar 'Bonus & Kluizen > Rad van Fortuin'. Draai 1x gratis aan het rad voor extra contant geld, sterren of energie.\n5.  HEEL BELANGRIJK: Ga direct naar 'Economie > De Bank' en stort al je contante geld op je rekening! Als je contant geld op zak houdt, kunnen andere spelers je beroven op de Respectlijst of via zakkenrollen.",
                    ],
                    [
                        'q' => 'Wat is het doel van TBS Kliniek?',
                        'a' => 'In TBS Kliniek bouw je je eigen reputatie op. Je klimt van binnengebrachte groentje op tot de onbetwiste Koning van de Luchtplaats! Je stijgt in level door XP te verzamelen, traint je vechtstatistieken, richt een bende (gang) op met vrienden, kraakt de centrale kluis en strijdt om de hoogste positie op de Respectlijst en de dagelijkse 24 Uur Battle.',
                    ],
                    [
                        'q' => 'Wat betekenen de balken bovenin mijn scherm (Energie, Gezondheid, Vermogen, Sterren, Respect)?',
                        'a' => "Bovenin je scherm zie je altijd jouw status:\n•  ENERGIE: Dit heb je nodig om acties, misdaden en gevechten uit te voeren. Energie herstelt automatisch elke 3 minuten.\n• ️ GEZONDHEID (HP): Jouw levensbalk. Als je verliest in een gevecht daalt deze. Herstel je HP in de Ziekenboeg of met EHBO-kits uit de winkel.\n•  TOTAAL VERMOGEN: Het gecombineerde saldo van jouw contante geld en je veilige bankrekening.\n• ⭐ STERREN: Exclusieve valuta waarmee je jezelf direct uit de isoleercel kunt kopen of zeldzame voordelen kunt ontgrendelen.\n•  RESPECT: Jouw reputatie en rang in de kliniek. Hoe hoger je respect, hoe hoger je positie op de ranglijst!",
                    ],
                    [
                        'q' => 'Hoe verdien ik Experience Points (XP) om in Level te stijgen?',
                        'a' => 'Je verdient XP door actief te spelen: missies voltooien, hosselen op de gang, gevechten winnen op de Respectlijst, werken in de werkplaats en zelfs door je te verplaatsen tussen kliniekafdelingen (+35 XP per reis). Zodra je XP-balk vol is stijg je automatisch een level, waarmee nieuwe misdaden, betere wapens en hogere statistieken ontgrendelen.',
                    ],
                ],
            ],
            'statistieken' => [
                'title' => ' Energie, Gezondheid & Krachttraining',
                'icon' => 'fa-bolt',
                'questions' => [
                    [
                        'q' => 'Hoe herstelt mijn Energie en hoe krijg ik sneller energie?',
                        'a' => "Jouw energie vult zich automatisch elke 3 minuten aan met een vast aantal punten. Wil je direct weer verder spelen?\n• Eet kantinesnacks zoals chocoladerepen of energiedrankjes uit je inventaris (te koop in de Kliniek Winkel).\n• Draai aan het Rad van Fortuin voor een gratis energieboost.\n• Bekijk een korte sponsoradvertentie in de Sponsorkamer om je cooldowns te resetten.\n• Koop een energie-refill in de VIP Shop met VIP Punten.",
                    ],
                    [
                        'q' => 'Wat moet ik doen als mijn Gezondheid (HP) bijna 0 is?',
                        'a' => "Als je HP laag is, loop je groot gevaar op de Respectlijst. Je kunt direct genezen via:\n1.  De Ziekenboeg ('Voortgang > Ziekenboeg'): Zuster Brenda lapt je gratis op!\n2.  EHBO-Verbanddozen: Koop verbanddozen of pijnstillers in de Kliniek Winkel en gebruik ze via 'Winkel > Mijn Inventaris'.\n3. ⏱️ Automatisch herstel: Je gezondheid tikt elke paar minuten langzaam vanzelf weer omhoog.",
                    ],
                    [
                        'q' => 'Wat doen Kracht, Verdediging en Snelheid en hoe train ik ze?',
                        'a' => "Onder 'Voortgang > Krachttraining' gebruik je energie om te trainen in de fitnessruimte:\n•  KRACHT (Push-ups): Bepaalt hoeveel schade jij toebrengt aan tegenstanders in gevechten.\n• ️ VERDEDIGING (Bankdrukken): Bepaalt hoeveel inkomende schade jij blokkeert als iemand jou aanvalt.\n•  SNELHEID (Hardlopen): Bepaalt jouw ontwijkkans en geeft je vaker de eerste klap in de gevechtsarena!",
                    ],
                ],
            ],
            'vechten' => [
                'title' => ' Respectlijst, Vechten & Gevechtsregels',
                'icon' => 'fa-hand-fist',
                'questions' => [
                    [
                        'q' => 'Hoe werkt de Respectlijst op de luchtplaats?',
                        'a' => "Op de Respectlijst zie je alle actieve patiënten in de kliniek gerangschikt op niveau en aanzien. Klik op 'Aanvallen' naast een speler om een duel te starten. Als je wint, steel je een percentage van het respect én contante zakgeld van je tegenstander! Zorg er dus altijd voor dat je eigen contante geld veilig op de bank staat.",
                    ],
                    [
                        'q' => 'Hoeveel aanvallen mag ik per dag uitvoeren?',
                        'a' => "Om het spel eerlijk en leuk te houden voor iedereen geldt een vaste limiet:\n• Maximaal 100 aanvallen per dag in totaal.\n• Maximaal 100 aanvallen per dag op hetzelfde specifieke doelwit (om pesterijen en farming te voorkomen).\nDe teller reset elke nacht automatisch om 00:00 uur.",
                    ],
                    [
                        'q' => 'Wat is de 5-seconden Arena Showdown?',
                        'a' => 'Zodra je een gevecht start betreed je de interactieve gevechtsarena. Gedurende 5 seconden zie en hoor je de rake klappen, ontwijkingen en kritieke treffers over en weer vliegen, waarna direct de einduitslag, HP-schade, buit en respectwinst spectaculair worden onthuld.',
                    ],
                    [
                        'q' => 'Wat als ik steeds word aangevallen door sterkere spelers?',
                        'a' => "Geen zorgen! Je hebt meerdere manieren om jezelf te beschermen:\n• ️ Vrijwillige Isolatie ('Geweld & Strijd > Vrijwillige Isolatie'): Sluit jezelf tijdelijk vrijwillig op. Niemand op de hele Respectlijst kan jou dan aanvallen!\n•  Bank: Zet al je geld op de bank, zodat aanvallers niks van je kunnen stelen als je verliest.\n•  Kopgeld: Plaats een bounty op de speler die jou lastigvalt, zodat andere sterke patiënten hem voor jou in elkaar slaan!",
                    ],
                    [
                        'q' => 'Hoe werken Kopgeld (Bounties) en Liquidatie (Hitman)?',
                        'a' => "•  KOPGELD: Plaats een geldbeloning op het hoofd van een rivaal. De eerste patiënt die dat doelwit knock-out slaat op de luchtplaats int automatisch de volledige premie!\n•  LIQUIDATIE (Hitman): Onder 'Geweld & Strijd > Liquidatie' kun je anonieme huurmoordenaars inhuren om een doelwit direct een flinke klap toe te brengen, waardoor ze meteen veel HP verliezen.",
                    ],
                    [
                        'q' => 'Wat doen Informanten en Vechtoefening?',
                        'a' => "• ️ INFORMANTEN: Koop vertrouwelijke inlichtingen over vermogende patiënten, actuele bewakingsroutes en geheime geldstromen.\n•  VECHTOEFENING: Spar met trainingspoppen en medepatiënten om je gevechtstechniek te perfectioneren zonder het risico op respectverlies.",
                    ],
                ],
            ],
            'isoleercel' => [
                'title' => ' Isoleercel, Straf & Ontsnappingen',
                'icon' => 'fa-dungeon',
                'questions' => [
                    [
                        'q' => 'Waarom zit ik in de Isoleercel en wat kan ik wel of niet doen?',
                        'a' => 'Als een illegale actie (zoals diefstal, afpersen, pillen palmen of een kluiskraak) mislukt, betrapt de zaalwacht je en vlieg je direct de isoleercel in voor een straftijd (in seconden). Terwijl je vastzit kun je geen misdaden plegen of vechten op de luchtplaats.',
                    ],
                    [
                        'q' => 'Hoe kom ik sneller vrij uit de Isoleercel?',
                        'a' => "Je hebt 4 opties om weer op vrije voeten te komen:\n1. ⏱️ Uitzitten: Wacht rustig tot de straftimer afloopt.\n2.  Sponsorkamer: Bekijk een 15-seconden videoadvertentie in de Sponsorkamer voor directe -15 minuten strafvermindering!\n3.  Celdeur forceren: Vraag een vriend of ganggenoot om jouw celdeur open te breken vanaf de gevangenispagina.\n4. ⭐ Vrijkopen met Sterren: Gebruik 2 Sterren om direct per direct vrijgelaten te worden.",
                    ],
                    [
                        'q' => 'Wat is de Uitbraaktunnel onder mijn cel?',
                        'a' => "Onder 'Illegale Acties > Uitbraaktunnel' kun je met een aangeschafte schep in het geheim een tunnel graven onder de vloer van je cel. Dump het zand ongezien op de binnenplaats en graaf elke dag meters vooruit. Bereik je de buitenmuur (100 meter), dan ontgrendel je de legendarische Grote Uitbraak met unieke beloningen!",
                    ],
                ],
            ],
            'werk_voortgang' => [
                'title' => ' Dagbesteding, Ziekenboeg & Verlof',
                'icon' => 'fa-person-digging',
                'questions' => [
                    [
                        'q' => 'Waarom is Dagbesteding & Werkplaats 100% veilig voor beginners?',
                        'a' => "In de Werkplaats ('Voortgang > Dagbesteding') werk je onder toezicht van Werkmeester Henk. In tegenstelling tot misdrijven is dit 100% legaal: er is absoluut GÉÉN risico op de isoleercel! Je verdient gegarandeerd eerlijk dagloon, bouwt Discipline op en hebt zelfs kans om geheime kluiscodes te vinden.",
                    ],
                    [
                        'q' => 'Wat is het dagelijkse Behandelaarsgesprek en hoe verdien ik Verlof?',
                        'a' => "Elke nacht om 00:00 uur staat er een nieuw psychologisch dilemma voor je klaar bij jouw behandelend specialist (psychiater of sociotherapeut). Kies het meest sociale, beheerste en verantwoorde antwoord:\n• Scoor je 60% of hoger? Dan beloont de kliniek je met 3 UUR BEGELEID VERLOF buiten de kliniek!\n• Scoor je 0% (agressief antwoord)? Dan word je voor 15 minuten platgespoten (chemische sedatie) en kun je tijdelijk geen acties doen.",
                    ],
                    [
                        'q' => 'Wat kan ik doen tijdens mijn 3 uur Begeleid Verlof buiten de poorten?',
                        'a' => "Zodra je verlof actief is verschijnen er 3 unieke buiten-missies:\n1.  Boodschappen hosselen bij de lokale supermarkt (€3.500 + 45 XP)\n2.  Ontmoeting op het Centraal Station (€6.000 + 65 XP)\n3.  Geheime aflevering bij Station Kluisjes (€10.000 + 100 XP + Smokkelkoffer XXL)",
                    ],
                    [
                        'q' => 'Wat is Pillen Palmen in de Ziekenboeg?',
                        'a' => "Naast gratis HP-herstel kun je in de Ziekenboeg kiezen voor 'Pillen Palmen'. Hierbij verstop je zware verdovende medicijnen onder je tong tijdens het uitdeeluurtje. Lukt het? Dan verkoop je de medicatie voor duizenden euro's aan afdelingsdealers. Word je betrapt? Dan vlieg je linea recta de isoleercel in!",
                    ],
                ],
            ],
            'illegale_acties' => [
                'title' => ' Illegale Acties, Smokkel & Drones',
                'icon' => 'fa-handcuffs',
                'questions' => [
                    [
                        'q' => 'Wat is het verschil tussen Hosselen en Dealen & Smokkelen?',
                        'a' => "•  HOSSELEN: Korte, directe dealtjes op de gang (shag rollen, aanstekers verkopen, telefoons doorsluizen) met directe contante buit en XP.\n•  DEALEN & SMOKKEL: Een uitgebreid productiesysteem. Je koopt grondstoffen (zoals wietzaadjes of chemische stoffen) in, laat ze rijpen in je cel, en verkoopt de partij vervolgens met enorme winstmarge aan dealers op de afdelingen.",
                    ],
                    [
                        'q' => 'Hoe werkt Afpersen?',
                        'a' => "Onder 'Illegale Acties > Afpersen' zet je zwakkere patiënten, bezoekers en zelfs corrupte zaalwachten onder zware druk voor wekelijks zwijggeld. Hoe hoger het doelwit, hoe groter de buit maar ook hoe hoger het risico op arrestatie.",
                    ],
                    [
                        'q' => 'Wat zijn Tennisbal & Drone Droppings?',
                        'a' => 'Vrienden en handlangers van buiten de kliniek smokkelen goederen over de gevangenismuren. Vang tennisballen op tijdens de luchtplaats of bestuur een geheime nachtdrone om zeldzame kluismunten, contant geld en smokkelbuit binnen te halen!',
                    ],
                    [
                        'q' => 'Wat is Kluisjes Kraken?',
                        'a' => 'In de personeels- en bezoekerskleedkamers staan kluisjes. Met behulp van een loper of lockpicks kun je proberen deze open te breken voor kluismunten, cash en zeldzame spullen.',
                    ],
                ],
            ],
            'bonussen' => [
                'title' => ' Bonussen, Kluiskraak & Dagelijkse Streaks',
                'icon' => 'fa-vault',
                'questions' => [
                    [
                        'q' => 'Wat is de Dagelijkse Inlog Bonus (22:00 - 22:30) en hoe werken Streaks?',
                        'a' => 'Elke avond tussen 22:00 en 22:30 is het Inlog Bonus venster geopend! Log je in tijdens dit halfuur, dan ontvang je automatisch 3 gratis kluiscodes én kun je jouw 7-daagse login streak beloning claimen voor contant geld, koffers, maskers en kluismunten. Daarnaast mag je elke 24 uur gratis draaien aan het Rad van Fortuin!',
                    ],
                    [
                        'q' => 'Hoe kraak ik de Centrale Medicijnkluis?',
                        'a' => "Onder 'Bonus & Kluizen > MedicijnKluis' staat de grote kluis met een geheime 4-cijferige code. Bij elke gok krijg je tips (bijv. 'de code is hoger' of 'de code is lager'). Let op: na een aantal foute pogingen gaat het alarm af tenzij je vooraf een Alarm Jammer hebt geactiveerd!",
                    ],
                    [
                        'q' => 'Waar vind ik geheime Kluiscodes en vouchers?',
                        'a' => 'Geheime kluiscodes kun je vinden tijdens het openen van Smokkelkoffers XXL, via speciale vondsten in de Werkplaats, bij radio-evenementen of in aankondigingen op onze officiële Discord.',
                    ],
                    [
                        'q' => 'Wat doen Maskers () bij Zakkenrollen?',
                        'a' => 'Bij het zakkenrollen kun je contant geld én bivakmutsen/maskers stelen. Als je een masker bezit, halveert dit automatisch je pakkans bij arrestaties door de zaalwacht!',
                    ],
                    [
                        'q' => 'Wat zijn Smokkelpakketjes en Koffers XXL?',
                        'a' => 'Onderschep smokkeltassen op het laadperron. Open gewone tassen voor directe buit, of spaar voor Koffers XXL waarin legendarische wapens, exclusieve boosters en enorme geldbedragen verborgen zitten.',
                    ],
                ],
            ],
            'economie' => [
                'title' => ' Bank, Verplaatsen & Marktplaats',
                'icon' => 'fa-building-columns',
                'questions' => [
                    [
                        'q' => 'Waarom moet ik ALTIJD mijn geld op de Bank zetten?',
                        'a' => 'Dit is de gouden regel van TBS Kliniek: contant geld op zak is vogelvrij! Andere spelers kunnen jouw contante geld stelen via de Respectlijst of via zakkenrollen. Geld op je bankrekening is 100% veilig beschermd tegen diefstal én levert je dagelijks rente op!',
                    ],
                    [
                        'q' => 'Hoe werkt Verplaatsen (Reizen) tussen afdelingen?',
                        'a' => "Onder 'Economie > Verplaatsen' koop je bewakers om om te reizen tussen paviljoens (zoals Paviljoen 1 Gesloten, Paviljoen 2 Half-open, Het Kookhuis, De Recreatiezaal en De Buitenplaats). Elke succesvolle reis levert je direct +35 XP op! VIP-leden betalen slechts de helft van het omkoopgeld.",
                    ],
                    [
                        'q' => 'Hoe werkt de Marktplaats voor spelershandel?',
                        'a' => 'Op de Marktplaats handelen patiënten onderling in wapens, bepantsering, koffers en zeldzame kluismunten. Je kunt zelf voorwerpen te koop aanbieden voor je eigen gekozen vraagprijs, of koopjes inslaan van andere spelers.',
                    ],
                    [
                        'q' => 'Wat is de TBS Afdelingsloterij?',
                        'a' => 'De loterij heeft een automatische trekking elke 14 dagen. Je koopt loten met contant geld. 70% van alle inleg vult de gigantische Jackpot. Na 14 dagen trekt het systeem één winnend lotnummer dat de hele pot in één keer wint!',
                    ],
                ],
            ],
            'casino' => [
                'title' => ' Casino & Gokhal',
                'icon' => 'fa-dice',
                'questions' => [
                    [
                        'q' => 'Welke gokspellen kan ik spelen in het Casino?',
                        'a' => "In het Casino vind je 5 complete spellen om je vermogen te vermenigvuldigen:\n•  ROULETTE: Zet in op Rood, Zwart, Even, Oneven of specifieke getallen (tot 36x uitbetaling).\n• 🃏 HOGER / LAGER: Raad of de volgende speelkaart hoger of lager is dan de huidige kaart.\n•  NUMMERSPEL: Raad het geheime getal tussen 1 en 10 voor een snelle winst.\n• ️ STEEN-PAPIER-SCHAAR: Speel tegen het huis voor dubbele inleg.\n•  FRUITHAL / SLOTS: Draai aan de rollen en probeer 3 dezelfde symbolen of jokers op één lijn te krijgen voor de jackpot!",
                    ],
                ],
            ],
            'gangs' => [
                'title' => ' Bendes, Gangs & Bende-Uurloon',
                'icon' => 'fa-users',
                'questions' => [
                    [
                        'q' => 'Wat is de Gang Overstap Bonus en hoe claim ik €5.000.000 + 31 Dagen Gratis VIP?',
                        'a' => "Speel jij met je bende of vriendengroep op een andere browser-game of misdaadspel (zoals Barribob, Boevenbende, OSG, etc.) en willen jullie overstappen naar TBS Kliniek? Dan belonen we jullie vorstelijk met de officiële Gang Overstap Bonus!\n\n👑 Wat jullie ontvangen bij goedkeuring:\n• 🛡️ Direct gratis oprichting van jullie eigen bende (geen level- of sterrenkosten)\n• 💰 € 5.000.000 kluisgeld direct gestort in jullie gezamenlijke bende-pot\n• ⭐ 31 DAGEN GRATIS VIP voor de leider én alle overstappende bendeleden!\n\n📋 Hoe vraag je het aan?\n1. Open een Support Ticket via 'Ondersteuning > Nieuw Ticket Aanmaken'.\n2. Selecteer de categorie '🔥 Gang Overstap Bonus'.\n3. Vul je gewenste bendenaam, tag en de link naar jullie gang op de andere website/game in.\n4. Zodra onze medewerkers je aanvraag controleren en goedkeuren, staat alles direct klaar in het spel!",
                    ],
                    [
                        'q' => 'Hoe richt ik een gang op en wat heb ik nodig?',
                        'a' => 'Vanaf Level 3 kun je op de Gangs pagina je eigen bende oprichten. Als bendeleider kies je een eigen bendenaam, afkorting/tag en logo. Je kunt medespelers uitnodigen, rollen toekennen en de gezamenlijke bende-kluis beheren.',
                    ],
                    [
                        'q' => 'Hoe werkt het Bende-Uurloon?',
                        'a' => 'De bendeleider stelt in hoeveel procent van de gezamenlijke bende-kluis elk uur als uurloon wordt uitgekeerd. Elk actief bendelid kan vervolgens ieder uur zijn eigen loon claimen via de Gangs pagina!',
                    ],
                    [
                        'q' => 'Wat zijn Gang Wars?',
                        'a' => 'Bendes kunnen elkaar de oorlog verklaren in Gang Wars. Door leden van de vijandige bende te verslaan op de luchtplaats verdient jouw bende oorlogspunten en kun je de kluis van de tegenstander plunderen!',
                    ],
                ],
            ],
            'competitie' => [
                'title' => ' Ranglijsten & 24 Uur Battle',
                'icon' => 'fa-trophy',
                'questions' => [
                    [
                        'q' => 'Hoe werkt de 24 Uur Battle en hoe win ik gratis Sterren?',
                        'a' => "De 24 Uur Battle is de dagelijkse competitie van TBS Kliniek. Er zijn 5 klassementen:\n1.  Meeste Misdrijven gepleegd\n2.  Meeste Gevechten gewonnen\n3.  Meeste Respect verdiend\n4.  Meeste Casinowinst behaald\n5. ️ Meeste Arbeid verricht in de werkplaats\nElke nacht om 00:00 uur wint de top 3 van elke categorie gratis Sterren en exclusieve badges!",
                    ],
                    [
                        'q' => 'Hoe kom ik hoog op de algemene Ranglijst?',
                        'a' => 'De algemene ranglijst sorteert spelers op hun totale Respectscore. Versla tegenstanders met meer respect dan jij, blijf trainen, voltooi missies en vraag dagelijks respect aan je vrienden om de nummer 1 positie te veroveren.',
                    ],
                ],
            ],
            'vip_sponsor' => [
                'title' => ' VIP Lidmaatschap & Sponsorkamer',
                'icon' => 'fa-crown',
                'questions' => [
                    [
                        'q' => 'Wat kost VIP en welke voordelen krijg ik?',
                        'a' => "VIP kost slechts €7,50 per maand via onze officiële Discord (discord.gg/tbs). Als VIP profiteer je van enorme voordelen:\n•  5 VIP Punten per speeluur (inwisselbaar in de VIP Shop voor zeldzame boosts en titels)\n•  2x Dagelijks draaien aan het Rad van Fortuin\n• ️ 50% Korting op alle reiskosten tussen afdelingen\n•  Gouden VIP-naamkleur en exclusieve statusbadge op je profiel\n•  Snellere energie-opbouw en toegang tot de VIP Lounge",
                    ],
                    [
                        'q' => 'Hoe verdien ik 24 UUR GRATIS VIP via de Sponsorkamer?',
                        'a' => "In de Sponsorkamer ('/sponsorkamer') kun je korte videoadvertenties van 15 seconden bekijken. Voor elke video ontvang je direct €2.500 zakgeld, maskers, kluismunten of -15 minuten strafvermindering in de isoleercel. Zodra je in totaal 50 advertenties hebt bekeken, ontgrendelt het systeem automatisch 24 UUR GRATIS VIP op jouw account!",
                    ],
                ],
            ],
            'navigatie' => [
                'title' => ' Radio, Krant, Tijdlijn & Vrienden',
                'icon' => 'fa-compass',
                'questions' => [
                    [
                        'q' => 'Wat is Het Afdelingsblad ("De Kliniekbode")?',
                        'a' => 'Het Afdelingsblad is de dagelijkse geautomatiseerde krant van de kliniek. Hierin lees je het laatste nieuws, de grootste overvallen van de dag, recente arrestaties in de isoleercel, kluiskraak-primeurs en een hilarisch weerbericht.',
                    ],
                    [
                        'q' => 'Hoe werkt TBS FM Radio en hoe stuur ik een shoutout?',
                        'a' => "Onder 'Algemeen > TBS FM' luister je live naar muziekstreams terwijl je speelt! Daarnaast kun je een openbare shoutout sturen naar alle luisterende patiënten. Andere spelers kunnen jouw shoutout liken met vlammetjes .",
                    ],
                    [
                        'q' => 'Hoe werkt de Tijdlijn (Sociale Feed)?',
                        'a' => 'De Tijdlijn is het interne sociale netwerk van de kliniek. Deel statusberichten, schep op over je laatste kluiskraak, like berichten van anderen en reageer op medepatiënten.',
                    ],
                    [
                        'q' => 'Wat is het BFF-systeem bij Vrienden?',
                        'a' => "Onder 'Algemeen > Vrienden' kun je medespelers toevoegen aan je vriendenlijst. Je kunt één speler benoemen tot jouw officiële 'BFF' (Beste Vriend Voor Altijd). Je BFF krijgt een speciaal gouden hartje op zijn profiel en jullie kunnen elkaars celdeur extra snel openbreken!",
                    ],
                ],
            ],
            'garage_heists' => [
                'title' => '🏎️ Smokkel Garage, Voertuigen & Grote Heists',
                'icon' => 'fa-masks-theater',
                'questions' => [
                    [
                        'q' => 'Hoe werkt de Smokkel Garage en welke voertuigen kan ik kopen?',
                        'a' => "In de Smokkel Garage ('Economie & Gokken > Smokkel Garage') kun je snelle vluchtwagens, boten, helikopters en smokkelvliegtuigen kopen met contant geld of sterren! Denk aan een Scooter Zip, Golf GTI, snelle Speedboot, BMW M5 F90, Gepantserde G63 AMG, Robinson Helikopter of Cessna Vliegtuig. Elk voertuig heeft een eigen Topsnelheid en Conditie.",
                    ],
                    [
                        'q' => 'Waarom heeft een voertuig Conditie en hoe repareer ik schade?',
                        'a' => 'Wanneer je een voertuig gebruikt tijdens Grote Heists ontstaat er slijtage door vluchtachtervolgingen en kogelinslagen. Naarmate de conditie daalt, neemt de effectieve topsnelheid af, waardoor je slaagkans bij heists vermindert! In de Smokkel Garage kun je je voertuig altijd laten repareren tegen een eerlijk tarief.',
                    ],
                    [
                        'q' => 'Hoe voer ik een Grote Heist uit en wat heb ik nodig?',
                        'a' => "Onder 'Illegale Acties > Grote Heists' vind je high-stakes overvallen zoals het Medicijn Transport, Juwelierskraak, Casino Overval, Haven Container Heist en de Centrale Bank Goudkluis. Om te starten heb je een actief vluchtvoertuig nodig (met voldoende snelheid en conditie) én maskers (bivakmutsen) tegen herkenning.",
                    ],
                    [
                        'q' => 'Wat levert een geslaagde Heist op en wat is het risico?',
                        'a' => "Een geslaagde heist levert gigantische buitbedragen op (tot honderdduizenden euro's), flinke XP en zeldzame kluiscodes voor de Centrale Medicijnkluis! Mislukt de heist? Dan vlieg je direct de zwaar beveiligde isoleercel in voor langdurige hechtenis.",
                    ],
                ],
            ],
            'account' => [
                'title' => '️ Account, Profiel & Beveiliging',
                'icon' => 'fa-gear',
                'questions' => [
                    [
                        'q' => 'Hoe pas ik mijn profielaccentkleur, avatar en bio aan?',
                        'a' => "Ga naar 'Mijn Profiel' in het menu. Klik op 'Profiel Bewerken' of 'Kleur Kiezen'. Je kunt kiezen uit 7 accentkleuren (Paars, Blauw, Groen, Rood, Amber, Roze en Cyaan). Deze kleur kleurt jouw dossierbalk en profielkaarten! Ook kun je een eigen avatar uploaden en een criminele achtergrondbio instellen.",
                    ],
                    [
                        'q' => 'Hoe wissel ik tussen Dark Mode en Light Mode?',
                        'a' => 'Rechtsboven in de menubalk vind je de zon/maan schakelaar. Klik hierop om direct soepel te wisselen tussen een lichte weergave en een donkere modus.',
                    ],
                    [
                        'q' => 'Hoe wijzig ik mijn gebruikersnaam of wachtwoord?',
                        'a' => "Ga naar 'Account Instellingen' (/instellingen/account). Je kunt je gebruikersnaam 1 keer per 6 maanden aanpassen. Wachtwoorden kun je hier direct wijzigen of herstellen via de 'Wachtwoord Vergeten' link op het inlogscherm.",
                    ],
                    [
                        'q' => 'Wat doet "Overal Uitloggen" (Remote Sessions)?',
                        'a' => 'Onder Account Instellingen zie je alle telefoons, tablets en computers waar jouw account momenteel op is ingelogd. Met 1 klik log je alle andere apparaten op afstand uit voor maximale veiligheid.',
                    ],
                    [
                        'q' => 'Hoe neem ik contact op met de beheerders of moderators?',
                        'a' => "Heb je een vraag, bug of probleem? Klik in het menu op 'Nieuw Ticket Aanmaken' of 'Support Tickets'. Je ticket komt direct binnen bij het beheerdersteam en je krijgt snel antwoord in je notificaties!",
                    ],
                ],
            ],
        ];
    }

    /**
     * Get rich, highly indexed knowledge base specifically for the AI Helpbot widget.
     * Includes extensive keywords, synonyms, newb-friendly explanations and direct navigation links.
     */
    public static function getBotKnowledge(): array
    {
        return [
            [
                'title' => ' Eerste Stappen & Beginnersgids',
                'keywords' => ['beginnen', 'nieuw', 'start', 'begin', 'starten', 'uitleg', 'hoe werkt', 'eerste stappen', 'wat moet ik doen', 'tips', 'beginner', 'overleven'],
                'answer' => "Welkom in de TBS Kliniek! Hier zijn de 5 belangrijkste stappen voor beginners:\n1. Doe eerst veilige missies onder 'Voortgang > Missies' voor gratis geld en XP.\n2. Ga naar de Ziekenboeg om je Gezondheid (HP) direct gratis naar 100% te brengen.\n3. Doe veilige Dagbesteding in de werkplaats (100% legaal, 0% pakkans!).\n4. Draai gratis aan het Rad van Fortuin voor een mooie bonus.\n5. BELANGRIJKSTE REGEL: Stort al je contant geld altijd meteen op de Bank, anders word je beroofd!",
                'links' => [
                    ['text' => 'Naar Missies', 'url' => route('missions.index')],
                    ['text' => 'Naar de Bank', 'url' => route('bank.index')],
                    ['text' => 'Naar de Ziekenboeg', 'url' => route('infirmary.index')],
                ],
            ],
            [
                'title' => ' Energie & Herstel',
                'keywords' => ['energie', 'energy', 'herstellen', 'op', 'leeg', 'weinig energie', 'kracht', 'snack', 'eten', 'chocola'],
                'answer' => 'Je hebt Energie nodig voor misdrijven, vechten en hosselen. Jouw energie vult automatisch elke 3 minuten aan! Sneller energie nodig? Eet kantinesnacks uit je inventaris, win energie op het Rad van Fortuin, of bekijk een advertentie in de Sponsorkamer.',
                'links' => [
                    ['text' => 'Kantine & Snacks', 'url' => route('shop.index', ['tab' => 'inventaris'])],
                    ['text' => 'Sponsorkamer', 'url' => route('sponsor.index')],
                ],
            ],
            [
                'title' => '️ Gezondheid (HP) & Ziekenboeg',
                'keywords' => ['gezondheid', 'health', 'hp', 'dood', 'gewond', 'genezen', 'healen', 'ziekenboeg', 'dokter', 'zuster', 'brenda', 'verband', 'ehbo', 'pillen'],
                'answer' => 'Is je Gezondheid laag door een verloren gevecht? Ga direct naar de Ziekenboeg! Zuster Brenda lapt je daar gratis op. Ook kun je in de Kliniek Winkel verbanddozen en EHBO-kits kopen om direct ter plekke te genezen.',
                'links' => [
                    ['text' => 'Naar de Ziekenboeg', 'url' => route('infirmary.index')],
                    ['text' => 'Kliniek Winkel', 'url' => route('shop.index')],
                ],
            ],
            [
                'title' => ' De Bank & Veilig Sparen',
                'keywords' => ['bank', 'geld', 'storten', 'sparen', 'veilig', 'rente', 'beroofd', 'gestolen', 'kwijt', 'contant', 'cash'],
                'answer' => 'Houd NOOIT veel contant geld op zak! Andere spelers kunnen jouw contante geld stelen via de Respectlijst of via zakkenrollen. Geld op je bankrekening is 100% veilig én je ontvangt dagelijks automatisch rente over je saldo.',
                'links' => [
                    ['text' => 'Naar De Bank', 'url' => route('bank.index')],
                ],
            ],
            [
                'title' => ' Isoleercel & Ontsnappen',
                'keywords' => ['isoleercel', 'gevangenis', 'cel', 'vast', 'straf', 'straftijd', 'gepakt', 'betrapt', 'vrijkopen', 'ontsnappen', 'deur forceren'],
                'answer' => "Als een misdrijf mislukt beland je in de isoleercel. Je komt sneller vrij door:\n• Een gratis 15s video te kijken in de Sponsorkamer (-15 min strafvermindering!).\n• Een vriend of ganggenoot te vragen je celdeur open te breken.\n• Jezelf direct vrij te kopen met 2 Sterren.",
                'links' => [
                    ['text' => 'Naar Isoleercel', 'url' => route('prison')],
                    ['text' => 'Sponsorkamer (-15 min)', 'url' => route('sponsor.index')],
                ],
            ],
            [
                'title' => ' Respectlijst & Luchtplaats Vechten',
                'keywords' => ['respectlijst', 'vechten', 'luchtplaats', 'slaylist', 'slay', 'aanvallen', 'duel', 'arena', 'showdown', '100 aanvallen', 'respect'],
                'answer' => 'Op de Respectlijst daag je andere patiënten uit op de luchtplaats. Win je het 5-seconden duel, dan steel je een deel van hun respect en contant geld! Je mag maximaal 100 aanvallen per dag doen en maximaal 100 op hetzelfde doelwit (reset om 00:00 uur).',
                'links' => [
                    ['text' => 'Naar Respectlijst', 'url' => route('fight.index')],
                ],
            ],
            [
                'title' => '️ Vrijwillige Isolatie (Bescherming)',
                'keywords' => ['vrijwillige isolatie', 'bescherming', 'bunker', 'veiligheid', 'aangevallen', 'te veel aanvallen', 'stop aanvallen', 'shield'],
                'answer' => "Word je constant aangevallen door sterkere spelers? Activeer Vrijwillige Isolatie onder 'Geweld & Strijd'. Zolang deze bunkerbescherming actief is, kan niemand op de hele Respectlijst jou aanvallen!",
                'links' => [
                    ['text' => 'Vrijwillige Isolatie', 'url' => route('isolation.index')],
                ],
            ],
            [
                'title' => ' Veilige Dagbesteding & Werkplaats',
                'keywords' => ['dagbesteding', 'werkplaats', 'werken', 'veilig werk', 'geen isoleercel', 'henk', 'loon', 'salaris', 'houtbewerking', 'wasserette'],
                'answer' => 'In de Werkplaats met Werkmeester Henk werk je 100% veilig en legaal! Er is GEEN ENKEL RISICO op de isoleercel. Je verdient gegarandeerd eerlijk dagloon, bouwt Discipline op en hebt kans om geheime kluiscodes te vinden.',
                'links' => [
                    ['text' => 'Naar Dagbesteding', 'url' => route('daycare.index')],
                ],
            ],
            [
                'title' => ' Behandelaarsgesprek & 3 Uur Verlof',
                'keywords' => ['behandelaar', 'behandelaarsgesprek', 'gesprek', 'verlof', 'psychiater', 'sedatie', 'buiten', 'supermarkt', 'station', 'dilemma'],
                'answer' => 'Elke dag om 00:00 uur staat er een psychologisch dilemma klaar bij je specialist. Scoor je minstens 60%? Dan word je beloond met 3 UUR BEGELEID VERLOF buiten de kliniek met speciale buiten-missies bij de supermarkt en het station voor mega buit!',
                'links' => [
                    ['text' => 'Behandelaarsgesprek', 'url' => route('treatment.index')],
                ],
            ],
            [
                'title' => ' Illegale Acties: Hosselen, Smokkel & Diefstal',
                'keywords' => ['hosselen', 'smokkelen', 'dealen', 'wiet', 'drugs', 'diefstal', 'klusjes', 'afpersen', 'misdaad', 'misdrijf'],
                'answer' => "• Hosselen: Korte deals op de gang (shag rollen, telefoons) voor snelle buit.\n• Smokkelen: Kweek wiet of brouw chemicaliën in je cel en verkoop de voorraad met flinke winst.\n• Afpersen: Zet zwakke patiënten en bewakers onder druk voor wekelijks zwijggeld.\n• Klusjes & Diefstal: Klassieke misdaden met vaste kansen op succes.",
                'links' => [
                    ['text' => 'Hosselen', 'url' => route('hustle.index')],
                    ['text' => 'Smokkelen & Dealen', 'url' => route('smuggle.index')],
                    ['text' => 'Klusjes & Diefstal', 'url' => route('theft.index')],
                ],
            ],
            [
                'title' => ' MedicijnKluis & Geheime Codes',
                'keywords' => ['kluis', 'kluis kraak', 'code', 'raden', 'medicijnkluis', 'jammer', 'alarm jammer', 'kluiscode', 'voucher'],
                'answer' => 'De centrale Medicijnkluis bevat een geheime 4-cijferige code. Bij elke gok krijg je aanwijzingen (hoger of lager). Koop vooraf een Alarm Jammer zodat het alarm niet afgaat bij een foute gok! Ook kun je hier geheime vouchers en kluiscodes verzilveren.',
                'links' => [
                    ['text' => 'Kluis Kraken', 'url' => route('vault.index')],
                ],
            ],
            [
                'title' => '️ Uitbraaktunnel Graven',
                'keywords' => ['tunnel', 'graven', 'uitbraaktunnel', 'schep', 'ontsnappingstunnel', 'zand dumpen', 'vrijheid', '100 meter'],
                'answer' => 'Koop een schep en graaf elke dag in het geheim een tunnel onder je celvloer. Dump het zand ongezien op de binnenplaats. Zodra je tunnel 100 meter bereikt ontgrendel je de Grote Uitbraakpoging!',
                'links' => [
                    ['text' => 'Naar Uitbraaktunnel', 'url' => route('tunnel.index')],
                ],
            ],
            [
                'title' => ' Zakkenrollen & Maskers',
                'keywords' => ['zakkenrollen', 'zakkenroller', 'masker', 'maskers', 'bivakmuts', 'stelen', 'pakkans'],
                'answer' => 'Besluip bewakers of patiënten om geld en maskers () te rollen. Het bezitten van een masker halveert jouw pakkans bij toekomstige misdrijven!',
                'links' => [
                    ['text' => 'Zakkenrollen', 'url' => route('pickpocket.index')],
                ],
            ],
            [
                'title' => ' Rad van Fortuin & Streaks',
                'keywords' => ['rad', 'rad van fortuin', 'spin', 'draaien', 'gratis', 'streak', 'dagelijks', 'inloggen'],
                'answer' => 'Draai elke 24 uur gratis aan het Rad van Fortuin voor contant geld, sterren, energie of koffers. Door elke dag achter elkaar in te loggen bouw je een dagelijkse streak op met steeds grotere jackpotuitkeringen!',
                'links' => [
                    ['text' => 'Rad van Fortuin', 'url' => route('daily.index')],
                ],
            ],
            [
                'title' => ' Bendes, Gangs & Uurloon',
                'keywords' => ['gang', 'bende', 'bendes', 'uurloon', 'gang kluis', 'bende kluis', 'gang wars', 'oorlog', 'bendeleider', 'oprichten'],
                'answer' => 'Vanaf Level 3 kun je op de Gangs pagina een eigen bende oprichten of je aansluiten bij een bestaande gang. Bendes hebben een gezamenlijke kluis en upgrades. Leden kunnen elk uur een vast gang-uurloon claimen!',
                'links' => [
                    ['text' => 'Naar Gangs', 'url' => route('gangs.index')],
                ],
            ],
            [
                'title' => '️ Verplaatsen (Reizen) & Paviljoens',
                'keywords' => ['reizen', 'verplaatsen', 'paviljoen', 'locatie', 'kookhuis', 'luchtplaats', 'omkopen', 'bewaker omkopen', '35 xp'],
                'answer' => "Onder 'Economie > Verplaatsen' koop je bewakers om naar andere afdelingen zoals Paviljoen 1, Paviljoen 2, Het Kookhuis of De Recreatiezaal. Elke succesvolle reis levert je direct +35 XP op! VIPs krijgen 50% korting.",
                'links' => [
                    ['text' => 'Verplaatsen', 'url' => route('travel.index')],
                ],
            ],
            [
                'title' => '⭐ VIP Lidmaatschap (Binnenkort Gratis via Ads)',
                'keywords' => ['vip', 'gratis', 'ads', 'advertenties', 'sponsorkamer', 'vip punten', 'gouden naam', 'voordelen'],
                'answer' => "VIP lidmaatschap wordt binnenkort volledig gratis beschikbaar via het bekijken van sponsor-advertenties in de Sponsorkamer! Als VIP ontvang je:\n• 5 VIP Punten per speeluur (inwisselbaar in de VIP Shop voor zeldzame spullen).\n• 2x Dagelijks draaien aan het Rad van Fortuin.\n• 50% Korting op alle reiskosten.\n• Gouden VIP-badge en exclusieve rangtitels.",
                'links' => [
                    ['text' => 'VIP Informatie', 'url' => route('vip.index')],
                    ['text' => 'Sponsorkamer', 'url' => route('sponsor.index')],
                ],
            ],
            [
                'title' => ' Sponsorkamer & 24 UUR GRATIS VIP',
                'keywords' => ['sponsorkamer', 'sponsor', 'ad', 'advertentie', 'video', 'reclame', 'gratis vip', 'smartlink', 'beloning'],
                'answer' => 'In de Sponsorkamer kun je korte 15s videoadvertenties bekijken voor €2.500 zakgeld, maskers, kluismunten of -15 minuten straftijd in de isoleercel. Na 50 bekeken advertenties ontgrendel je automatisch 24 UUR GRATIS VIP!',
                'links' => [
                    ['text' => 'Naar Sponsorkamer', 'url' => route('sponsor.index')],
                ],
            ],
            [
                'title' => ' Casino & Gokhal',
                'keywords' => ['casino', 'gokhal', 'gokken', 'roulette', 'hoger lager', 'nummerspel', 'slots', 'fruitautomaat', 'blackjack'],
                'answer' => 'In het Casino speel je Roulette, Hoger/Lager, Nummerspel, Steen-Papier-Schaar en de klassieke Fruitautomaat/Slots. Speel verstandig en zet nooit al je geld in één keer in!',
                'links' => [
                    ['text' => 'Naar het Casino', 'url' => route('casino.index')],
                ],
            ],
            [
                'title' => '️ Afdelingsloterij (Jackpot)',
                'keywords' => ['loterij', 'lotto', 'loten', 'jackpot', 'afdelingsloterij', 'winnen', 'pot'],
                'answer' => 'De kliniekloterij heeft een vaste trekkingscyclus van 14 dagen. Koop loten met contant geld. 70% van alle inleg vult de Jackpot. Na 14 dagen wint één getrokken lot de hele pot!',
                'links' => [
                    ['text' => 'Naar Loterij', 'url' => route('lottery.index')],
                ],
            ],
            [
                'title' => ' TBS FM Radio & Krant',
                'keywords' => ['radio', 'tbs fm', 'muziek', 'shoutout', 'krant', 'afdelingsblad', 'kliniekbode', 'nieuws'],
                'answer' => 'Luister live naar TBS FM Radio terwijl je speelt en stuur een publieke shoutout naar alle patiënten! In Het Afdelingsblad lees je de dagelijkse krant met recente overvallen en arrestaties.',
                'links' => [
                    ['text' => 'TBS FM Radio', 'url' => route('radio.index')],
                    ['text' => 'Het Afdelingsblad', 'url' => route('newspaper.index')],
                ],
            ],
            [
                'title' => '️ Account, Wachtwoord & Beveiliging',
                'keywords' => ['account', 'wachtwoord', 'vergeten', 'reset', 'gebruikersnaam', 'naam wijzigen', 'kleur', 'dark mode', '2fa', 'uitloggen', 'sessies'],
                'answer' => 'Onder Account Instellingen (/instellingen/account) kun je je gebruikersnaam 1x per 6 maanden aanpassen, je wachtwoord wijzigen, 2FA instellen en alle andere apparaten op afstand uitloggen. Wisselen tussen Dark en Light mode doe je met het zon/maan icoon rechtsbovenin.',
                'links' => [
                    ['text' => 'Account Instellingen', 'url' => route('settings.account')],
                    ['text' => 'Mijn Profiel', 'url' => route('profile.me')],
                ],
            ],
            [
                'title' => ' Support Tickets & Hulp van Beheer',
                'keywords' => ['ticket', 'support', 'help', 'hulp', 'beheer', 'moderator', 'admin', 'melden', 'vraag', 'probleem', 'contact'],
                'answer' => "Heb je een vraag, bug of probleem dat niet in de helpbot staat? Open direct een ticket onder 'Support Tickets' of 'Nieuw Ticket Aanmaken'. Het beheerdersteam helpt je snel verder!",
                'links' => [
                    ['text' => 'Nieuw Ticket Maken', 'url' => route('help.tickets.create')],
                    ['text' => 'Mijn Tickets', 'url' => route('help.tickets.index')],
                ],
            ],
            [
                'title' => '🏎️ Smokkel Garage & Voertuigen',
                'keywords' => ['garage', 'voertuig', 'auto', 'wagen', 'vluchtwagen', 'scooter', 'motor', 'bmw', 'gti', 'vliegtuig', 'helikopter', 'speedboot', 'repareren', 'conditie', 'snelheid', 'wagenpark'],
                'answer' => 'In de Smokkel Garage koop je snelle vluchtvoertuigen zoals de Golf GTI, BMW M5, Speedboot of Helikopter. Elk voertuig heeft topsnelheid en conditie. Let op: heists veroorzaken schade! Repareer je wagen in de garage om maximale snelheid en slaagkansen te behouden.',
                'links' => [
                    ['text' => 'Naar Smokkel Garage', 'url' => route('garage.index')],
                    ['text' => 'Naar Grote Heists', 'url' => route('heists.index')],
                ],
            ],
            [
                'title' => ' Grote Heists & Overvallen',
                'keywords' => ['heist', 'heists', 'overval', 'kraak', 'bank', 'juwelier', 'casino', 'vlucht', 'vluchtwagen', 'ramkraak', 'goudkluis', 'container', 'buit', 'heistlog'],
                'answer' => 'Grote Heists zijn zware, strategische overvallen met mega buit! Je hebt een minimaal level, voldoende maskers (bivakmutsen) en een snel vluchtvoertuig in goede conditie nodig. Geslaagde heists leveren enorme bedragen en kluiscodes op. Bij een mislukking beland je in de isoleercel!',
                'links' => [
                    ['text' => 'Naar Grote Heists', 'url' => route('heists.index')],
                    ['text' => 'Smokkel Garage', 'url' => route('garage.index')],
                ],
            ],
        ];
    }

    /**
     * Show FAQ page with categorized knowledge.
     */
    public function index(Request $request): View
    {
        $categories = self::getFaqData();
        $search = strtolower(trim($request->input('search', '')));

        if (! empty($search)) {
            $filtered = [];
            foreach ($categories as $catKey => $cat) {
                $matchingQuestions = array_filter($cat['questions'], function ($item) use ($search) {
                    return str_contains(strtolower($item['q']), $search) || str_contains(strtolower($item['a']), $search);
                });

                if (! empty($matchingQuestions)) {
                    $catCopy = $cat;
                    $catCopy['questions'] = array_values($matchingQuestions);
                    $filtered[$catKey] = $catCopy;
                }
            }
            $categories = $filtered;
        }

        return view('game.faq', compact('categories', 'search'));
    }
}
