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
                'title' => '🔰 Beginnersgids - Start tot Einde & Eerste Stappen',
                'icon' => 'fa-graduation-cap',
                'questions' => [
                    [
                        'q' => 'Wat is het complete stappenplan van dag 1 (groentje) tot eindbaas van de TBS Kliniek?',
                        'a' => "Welkom in de TBS Kliniek! Hier is het beproefde 6-fasen stappenplan waarmee je veilig uitgroeit van pas binnengebrachte patiënt tot heerser van de inrichting:\n\n1. 🧭 FASE 1: Binnenkomst & Veiligheid (Eerste 10 Minuten)\n• Laat je direct gratis oplappen naar 100% HP bij Zuster Brenda in de Ziekenboeg.\n• Draai 1x gratis aan het Rad van Fortuin voor een mooie startersbonus.\n• Stort AL je contante geld meteen op De Bank! Contant geld op zak kan namelijk worden geroofd via de Respectlijst of zakkenrollen.\n\n2. 🔨 FASE 2: Risicoloos Geld & XP Verzamelen (Level 1 t/m 3)\n• Begin met 'Voortgang > Missies & Opdrachten' voor snelle contante beloningen en XP.\n• Werk in de Werkplaats / Wasserette bij Werkmeester Henk. Dit is 100% legaal en heeft 0% kans op de isoleercel!\n• Neem elke nacht om 00:00 uur deel aan het Behandelaarsgesprek om 3 uur Begeleid Verlof buiten de kliniek te verdienen.\n\n3. 🥊 FASE 3: Trainen & Basismisdrijven (Level 3 t/m 6)\n• Train je Kracht, Verdediging en Snelheid in de Fitnessruimte (10 energie per sessie).\n• Begin voorzichtig met Hosselen op de gang (shag rollen, telefoons doorsluizen).\n• Probeer zakken te rollen om bivakmutsen/maskers te bemachtigen: een masker halveert je pakkans bij zaalwachten!\n\n4. 🗺️ FASE 4: Reizen & Luchtplaats Gevechten (Level 6 t/m 10)\n• Reis tussen de paviljoens via 'Economie > Verplaatsen' (+35 XP per reis!).\n• Daag spelers met lagere statistieken uit op de Luchtplaats (Respectlijst) om respect en tot 15% van hun contante zakgeld te stelen.\n• Onderschep smokkelkoffers op het perron en kraak ze met een breekijzer of lockpicks.\n\n5. 👥 FASE 5: Bendes, Heists & Passief Inkomen (Level 10 t/m 15)\n• Richt een bende op vanaf Level 3 of sluit je aan bij een bestaande gang (of claim de €5.000.000 Overstap Bonus als je van een andere game komt!).\n• Koop kiosken en vastgoed voor je bende om elk uur automatisch passief Bende-Uurloon te ontvangen.\n• Schaf een snel voertuig aan in de Smokkel Garage en voer Grote Heists uit op juweliers en de centrale bank.\n\n6. 👑 FASE 6: Endgame & Heerschappij (Level 15+)\n• Betreed Liquidatie 2.0: schaduw rivalen, rust een Backfire-vest uit en voer bloedwraak vetes.\n• Strijd elke dag mee in de 24 Uur Battle in 5 klassementen voor gratis Sterren ⭐ en exclusieve trofeeën.\n• Graaf je geheime Uitbraaktunnel naar 100 meter voor de legendarische Grote Uitbraak!",
                        'links' => [
                            ['text' => 'Naar Missies', 'url' => route('missions.index')],
                            ['text' => 'Naar De Bank', 'url' => route('bank.index')],
                            ['text' => 'Naar de Ziekenboeg', 'url' => route('infirmary.index')],
                        ],
                    ],
                    [
                        'q' => 'Wat is het doel van TBS Kliniek?',
                        'a' => "In TBS Kliniek bouw je jouw eigen criminele reputatie op. Je klimt op van zojuist binnengebracht groentje tot de absolute heerser van de inrichting!\n\nHet doel is om te domineren op meerdere fronten:\n• 🥊 Respectlijst & Rang: Strijd om de #1 positie op de Luchtplaats en verover de Hall of Fame;\n• 👥 Bendes & Imperium: Richt een bende (gang) op, claim passief bende-uurloon uit vastgoed en verover strategische Controlepunten;\n• 🏆 24 Uur Battle: Win elke nacht om 00:00 uur gratis Sterren ⭐ in de 5 dagelijkse klassementen;\n• ⛏️ De Grote Uitbraak: Graaf een geheime tunnel van 100 meter onder je cel voor legendarische vrijheid;\n• 🔐 Kluiskraak: Kraak de Centrale Medicijnkluis en plunder de pot!",
                        'links' => [
                            ['text' => 'Ranglijst Bekijken', 'url' => route('leaderboard.index')],
                            ['text' => '24 Uur Battle', 'url' => route('battle.index')],
                        ],
                    ],
                    [
                        'q' => 'Ik ben zojuist binnengebracht! Wat zijn de allereerste dingen die ik moet doen in de eerste 10 minuten?',
                        'a' => "Volg direct deze veilige checklist zodra je account is aangemaakt:\n\n1. 🏥 Ga naar 'Voortgang > Ziekenboeg': Klik op 'Laat je behandelen' bij Zuster Brenda. Je Gezondheid (HP) is nu direct 100%.\n2. 🎁 Ga naar 'Bonus & Kluizen > Rad van Fortuin': Draai 1x gratis aan het rad. Je wint contant geld, sterren of energie.\n3. 🏦 Ga direct naar 'Economie > De Bank': Stort al je contante geld op je rekening. Als je met contant geld op zak rondloopt, stelen andere spelers het van je!\n4. 🔨 Ga naar 'Voortgang > Dagbesteding': Doe veilig werk in de wasserette of werkplaats bij Werkmeester Henk. Dit is 100% veilig en geeft 0% kans op de isoleercel!\n5. 📜 Ga naar 'Voortgang > Missies': Voltooi de eerste beginnersmissie voor extra startkapitaal en XP.\n6. 🧭 Volg de interactieve Intake Rondleiding bovenaan deze pagina voor een extra gratis startersbonus van €1.000!",
                        'links' => [
                            ['text' => 'Ziekenboeg', 'url' => route('infirmary.index')],
                            ['text' => 'Rad van Fortuin', 'url' => route('daily.index')],
                            ['text' => 'De Bank', 'url' => route('bank.index')],
                        ],
                    ],
                    [
                        'q' => 'Wat betekenen alle balken en icoontjes bovenaan mijn scherm (Energie, HP, Geld, Sterren, Respect, Rang, XP)?',
                        'a' => "Bovenaan je scherm staat het dashboard van jouw patiëntendossier:\n\n• ⚡ ENERGIE: Nodig voor alle acties (misdaden, training, gevechten). Herstelt automatisch met 1 punt per 5 minuten (elke 3 minuten voor VIP).\n• ❤️ GEZONDHEID (HP): Jouw levensbalk (max 100). Daalt als je verliest in gevechten. Herstel gratis bij Zuster Brenda of met EHBO-kits.\n• 💵 CONTANT GELD: Geld op zak. Pas op: dit kan worden gestolen bij gevechten of zakkenrollen! Zet het altijd op de bank.\n• 🏦 BANKGELD: Veilig gespaard geld op je bankrekening. 100% beschermd tegen berovingen op de Respectlijst en zakkenrollers.\n• 💰 TOTAAL VERMOGEN: Jouw contante geld plus je banktegoed bij elkaar opgeteld.\n• ⭐ STERREN: Zeldzame valuta om jezelf direct uit de cel te kopen (25 Sterren), wachttijden over te slaan of exclusieve items te ontgrendelen.\n• 🥊 RESPECT: Jouw reputatie in de kliniek. Bepaalt jouw plek op de algemene ranglijst.\n• 🎖️ RANG & LEVEL: Jouw criminele status. Hoe hoger je level, hoe zwaardere overvallen en betere wapens je vrijspeelt.\n• 📊 XP BALK: Toont hoeveel Experience Points je nog nodig hebt tot je volgende level up.",
                    ],
                    [
                        'q' => 'Wat is het verschil tussen Contant Geld en Bankgeld, en waarom moet ik contant geld ALTIJD direct storten?',
                        'a' => "Dit is de allerbelangrijkste overlevingsregel in de TBS Kliniek:\n\n• 💵 CONTANT GELD OP ZAK: Is vogelvrij! Als een andere speler jou aanvalt op de Respectlijst en wint, kan die speler tot 15% van al jouw contante zakgeld stelen. Ook zakkenrollers kunnen contant geld van je pikken.\n• 🏦 BANKGELD: Is veilig opgeborgen achter dikke kluisdeuren. Bankgeld kan NOOIT worden gestolen via de Respectlijst of zakkenrollen!\n• 💡 AUTOMATISCH ZAKGELD: Spelers die nog niet in een bende zitten, ontvangen elke 15 minuten automatisch een zakgelduitkering van €1.000 op hun bankrekening!\n• ⚠️ LET OP: Alleen bij geavanceerde PvP Liquidaties kan een schutter met kogels 7% van de bank plunderen. Dit voorkom je eenvoudig door '24 Uur Cash Bescherming' te activeren in de VIP Shop.",
                        'links' => [
                            ['text' => 'Direct naar De Bank', 'url' => route('bank.index')],
                        ],
                    ],
                    [
                        'q' => 'Hoe verdien ik Experience Points (XP) om in Level te stijgen en wat speel ik vrij?',
                        'a' => "Je verdient XP met vrijwel elke actie in de inrichting:\n• Missies voltooien in de missiecentrale;\n• Werken in de werkplaats bij Werkmeester Henk;\n• Hosselen en illegale misdrijven plegen;\n• Gevechten winnen op de Luchtplaats;\n• Reizen tussen paviljoens (+35 XP per reis!);\n• Koffers kraken en heists uitvoeren.\n\nZodra je XP-balk 100% bereikt, stijg je automatisch een level. Per level speel je nieuwe mogelijkheden vrij: zwaardere misdaden, snellere vluchtauto's in de garage, krachtigere wapens in de winkel, de mogelijkheid om een eigen bende op te richten (vanaf Level 3) en toegang tot exclusieve heists!",
                    ],
                    [
                        'q' => 'Wat is Discipline en waarom is dit belangrijk?',
                        'a' => "Discipline is een speciale gedragsstatistiek die je opbouwt door netjes en eerlijk te werken in de Werkplaats ('Voortgang > Dagbesteding').\n\nDiscipline toont aan de directie en behandelaren dat je constructief meewerkt in de kliniek. Een hoge discipline verhoogt je aanzien, geeft betere beoordelingen bij het dagelijkse Behandelaarsgesprek en vergroot je kansen op goedkeuring van Begeleid Verlof buiten de poorten!",
                        'links' => [
                            ['text' => 'Naar Dagbesteding', 'url' => route('daycare.index')],
                        ],
                    ],
                    [
                        'q' => 'Wat zijn Sterren ⭐ en hoe kom ik eraan zonder ooit echt geld te betalen?',
                        'a' => "Sterren ⭐ zijn de premium valuta van TBS Kliniek, maar je hoeft er NOOIT echt geld voor te betalen! Je kunt Sterren 100% gratis in de game verdienen via:\n\n1. 🎰 Rad van Fortuin: Draai dagelijks voor gratis Sterren op het bord.\n2. 🏆 24 Uur Battle: Eindig in de top 3 van een van de 5 dagelijkse klassementen om middernacht en win gratis Sterren.\n3. 📦 XXL Koffers: Open onderschepte XXL Koffers waarin regelmatig gratis Sterren verborgen zitten.\n4. ⛏️ Grote Uitbraaktunnel: Voltooi je tunnel van 100 meter en ontvang 25 gratis Sterren ⭐.\n5. 📺 Sponsorkamer & Mijlpalen: Bekijk korte sponsorvideo's voor sterrenbeloningen.\n6. 🎫 Battle Pass: Claim gratis sterrenbeloningen op het seizoenspad.",
                        'links' => [
                            ['text' => 'Rad van Fortuin', 'url' => route('daily.index')],
                            ['text' => '24 Uur Battle', 'url' => route('battle.index')],
                        ],
                    ],
                    [
                        'q' => 'Wat gebeurt er als mijn levensbalk (HP) op 0 komt? Ga ik permanent dood?',
                        'a' => "Nee, absoluut niet! In TBS Kliniek bestaat GEEN permadeath (je raakt je account of vorderingen nooit kwijt).\n\nAls je Gezondheid (HP) 0 bereikt door een verloren gevecht op de Respectlijst, ben je tijdelijk gewond. Je kunt dan niet vechten totdat je geneest. Ga simpelweg naar 'Voortgang > Ziekenboeg': Zuster Brenda verzorgt je wonden direct 100% gratis! Ook kun je verbanddozen uit je inventaris gebruiken om jezelf direct weer op de been te helpen.",
                        'links' => [
                            ['text' => 'Naar Ziekenboeg', 'url' => route('infirmary.index')],
                        ],
                    ],
                    [
                        'q' => 'Wat zijn kluismunten, vouchers en smokkeltassen?',
                        'a' => "• 🪙 KLUISMUNTEN: Speciale tokens die je vindt bij heists, drone droppings en koffers. Je kunt ze verzilveren voor unieke items of verkopen op de marktplaats.\n• 🎟️ VOUCHERS & CODES: Geheime promotiecodes die je kunt invoeren bij de Centrale Medicijnkluis voor gratis cash, sterren of items. Codes worden gedeeld op onze officiële Discord of gevonden in XXL koffers.\n• 🎒 SMOKKELTASSEN & KOFFERS: Pakketjes die over de muur of via het laadperron worden binnengesmokkeld. Je onderschept ze onder 'Voortgang > Koffers' en breekt ze open voor buit!",
                        'links' => [
                            ['text' => 'MedicijnKluis', 'url' => route('vault.index')],
                            ['text' => 'Koffers Onderscheppen', 'url' => route('packages.index')],
                        ],
                    ],
                ],
            ],
            'statistieken' => [
                'title' => '⚡ Energie, Gezondheid & Krachttraining',
                'icon' => 'fa-bolt',
                'questions' => [
                    [
                        'q' => 'Hoe herstelt mijn Energie en hoe krijg ik direct extra energie?',
                        'a' => "Jouw energie vult zich automatisch aan met vaste tussenpozen:\n• Standaard spelers: +1 energie per 5 minuten (300 seconden);\n• VIP / Tier 1 supporters: +1 energie per 3 minuten (180 seconden);\n• Tier 2 Elite: +1 energie per 2,5 minuut (150 seconden);\n• Tier 3 / Baas / Lifetime: +1 energie per 2 minuten (120 seconden).\n\nWil je direct weer verder spelen zonder te wachten?\n1. 🍫 Kantinesnacks eten: Eet chocoladerepen, bananen of energiedrankjes uit je inventaris (te koop in de Kliniek Winkel);\n2. 🎰 Rad van Fortuin: Draai voor een directe energieboost;\n3. ⭐ Sterren Shop: Koop direct 100% Energie herstel met Sterren ⭐;\n4. 👑 VIP Shop: Wissel 15 VIP Punten in voor een complete 100% Energie Refill.",
                        'links' => [
                            ['text' => 'Winkel & Snacks', 'url' => route('shop.index', ['tab' => 'inventaris'])],
                            ['text' => 'VIP Shop', 'url' => route('vip.index')],
                        ],
                    ],
                    [
                        'q' => 'Wat moet ik doen als mijn Gezondheid (HP) laag is en hoe genees ik gratis?',
                        'a' => "Als je HP laag is, loop je groot risico om aangevallen te worden op de Respectlijst. Je kunt jezelf op 3 manieren oplappen:\n\n1. 🏥 De Ziekenboeg ('Voortgang > Ziekenboeg'): Zuster Brenda lapt je direct 100% GRATIS op! Dit kost helemaal niks en brengt je HP meteen naar 100.\n2. 🩹 EHBO-Kits & Verbanddozen: Koop verbanddozen of pijnstillers in de Kliniek Winkel en gebruik ze direct via 'Winkel > Mijn Inventaris'. Handig als je midden in een gevechtsreeks zit.\n3. ⏱️ Automatisch herstel: Zolang je niet in een comateuze toestand (Code Blauw) verkeert, herstelt je HP langzaam vanzelf mee met je energie.",
                        'links' => [
                            ['text' => 'Gratis Genezen bij Brenda', 'url' => route('infirmary.index')],
                            ['text' => 'EHBO Kopen', 'url' => route('shop.index')],
                        ],
                    ],
                    [
                        'q' => 'Wat doen Kracht, Verdediging en Snelheid en hoe beïnvloeden ze gevechten, heists en uitbraken?',
                        'a' => "Jouw drie hoofdstatistieken bepalen hoe sterk jouw karakter is in conflictsituaties:\n\n• 🥊 KRACHT (Aanval): Bepaalt hoeveel schade jij toebrengt aan tegenstanders in gevechten op de Respectlijst. Hoe hoger je kracht ten opzichte van de verdediging van je doelwit, hoe groter je winstkans!\n• 🛡️ VERDEDIGING (Pantser): Bepaalt hoeveel schade jij blokkeert wanneer andere spelers jou aanvallen op de Respectlijst. Een hoge verdediging maakt jou een onaantrekkelijk doelwit voor rivalen.\n• 🏃 SNELHEID (Behendigheid): Bepaalt jouw ontwijkkans in gevechten, verhoogt je slaagkans bij uitbraken uit de isoleercel en geeft je vaker het initiatief (de eerste klap) in de gevechtsarena!",
                        'links' => [
                            ['text' => 'Naar Krachttraining', 'url' => route('training.index')],
                        ],
                    ],
                    [
                        'q' => 'Hoe werkt de Krachttraining in de fitnessruimte (Push-ups, Bankdrukken, Hardlopen)?',
                        'a' => "Onder 'Voortgang > Krachttraining' vind je de fitnessruimte van het complex. Elke trainingssessie kost 10 Energie:\n\n• 💪 Push-ups doen: Traint jouw KRACHT (+statistiekpunten afhankelijk van je trainingsniveau);\n• 🏋️ Bankdrukken: Traint jouw VERDEDIGING (+statistiekpunten);\n• 🏃 Hardlopen op de loopband: Traint jouw SNELHEID (+statistiekpunten).\n\nTip voor beginners: Train je statistieken gebalanceerd! Begin met 25 Kracht, 25 Verdediging en 20 Snelheid om jezelf te wapenen tegen vroege belagers.",
                        'links' => [
                            ['text' => 'Direct Trainen', 'url' => route('training.index')],
                        ],
                    ],
                    [
                        'q' => 'Wat is Vechtoefening en hoe spar ik veilig met trainingspoppen?',
                        'a' => "Onder 'Geweld & Strijd > Vechtoefening' kun je sparren met houten trainingspoppen, zandzakken en medepatiënten. Dit is een veilige manier om gevechtservaring op te doen zonder het risico om respect of contant geld te verliezen als het even tegenzit!",
                        'links' => [
                            ['text' => 'Naar Vechtoefening', 'url' => route('combat_training.index')],
                        ],
                    ],
                    [
                        'q' => 'Hoe gebruik ik kantinesnacks en EHBO-kits uit mijn inventaris?',
                        'a' => "Alle voorwerpen die je koopt in de winkel, vindt in koffers of wint bij evenementen belanden in jouw persoonlijke Inventaris ('Winkel > Mijn Inventaris'):\n• Klik op 'Gebruiken' naast een snack om direct energie bij te schrijven;\n• Klik op 'Gebruiken' naast een verbanddoos of medicijn om direct Gezondheid (HP) te herstellen;\n• Klik op 'Uitrusten' bij wapens of bepantsering om jouw aanvals- en verdedigingsbonussen te activeren.",
                        'links' => [
                            ['text' => 'Mijn Inventaris', 'url' => route('shop.index', ['tab' => 'inventaris'])],
                        ],
                    ],
                ],
            ],
            'werk_voortgang' => [
                'title' => '🔨 Dagbesteding, Ziekenboeg & Verlof',
                'icon' => 'fa-person-digging',
                'questions' => [
                    [
                        'q' => 'Waarom is Dagbesteding & Werkplaats 100% veilig voor beginners (0% risico op isoleercel)?',
                        'a' => "In de Werkplaats ('Voortgang > Dagbesteding') werk je onder toezicht van Werkmeester Henk. In tegenstelling tot misdrijven en hosselen is dit 100% LEGAAL:\n• Er is absoluut NUL (0%) kans op betrapping door de zaalwacht;\n• Je kunt NIET in de isoleercel belanden;\n• Je verdient gegarandeerd eerlijk dagloon;\n• Je bouwt Discipline op voor je patiëntendossier;\n• Je hebt zelfs kans om geheime kluiscodes te vinden tussen het zaagsel!",
                        'links' => [
                            ['text' => 'Naar Dagbesteding', 'url' => route('daycare.index')],
                        ],
                    ],
                    [
                        'q' => 'Wat levert werken met Werkmeester Henk precies op (dagloon, discipline, kluiscodes)?',
                        'a' => 'In de Dagbesteding kies je uit verschillende werkzaamheden (zoals de Houtwerkplaats, Wasserette, Metaalbewerking en Tuinonderhoud). Elk afgerond werkblok levert je direct contant dagloon op, voegt disciplinepunten toe aan je dossier en geeft kans op zeldzame bonusvondsten zoals lockpicks, spijkers, ijzerzaagjes en 4-cijferige kluiscodes!',
                        'links' => [
                            ['text' => 'Aan de Slag bij Henk', 'url' => route('daycare.index')],
                        ],
                    ],
                    [
                        'q' => 'Wat is het dagelijkse Behandelaarsgesprek (00:00 dilemma) en hoe verdien ik Verlof?',
                        'a' => "Elke nacht om 00:00 uur staat er een nieuw psychologisch dilemma voor je klaar bij jouw behandelend specialist (de sociotherapeut of psychiater):\n\n• 🧠 Het Dilemma: Je krijgt een praktijksituatie voorgelegd met meerdere antwoordopties. Kies het meest sociale, beheerste en verantwoorde antwoord.\n• 🟢 Scoor je 60% of hoger? Gefeliciteerd! De kliniekdirectie beloont jou met 3 UUR BEGELEID VERLOF buiten de kliniekpoorten!\n• 🔴 Scoor je 0% (zeer agressief antwoord)? Dan beschouwt de arts je als instabiel en word je voor 15 minuten platgespoten (chemische sedatie), waardoor je tijdelijk geen acties kunt uitvoeren.",
                        'links' => [
                            ['text' => 'Behandelaarsgesprek', 'url' => route('treatment.index')],
                        ],
                    ],
                    [
                        'q' => 'Wat kan ik doen tijdens mijn 3 uur Begeleid Verlof buiten de kliniek (Supermarkt, Station)?',
                        'a' => "Zodra jouw 3 uur Begeleid Verlof actief is, verschijnen er 3 unieke buiten-missies die normaal gesproken vergrendeld zijn:\n\n1. 🛒 Boodschappen hosselen bij de lokale supermarkt: Verdien €3.500 contant geld + 45 XP;\n2. 🚆 Ontmoeting op het Centraal Station: Verdien €6.000 contant geld + 65 XP;\n3. 🛅 Geheime aflevering bij Station Kluisjes: Verdien €10.000 contant geld + 100 XP én een Smokkelkoffer XXL!\n\nZorg dat je tijdens je verlofuren direct deze buiten-missies meepakt voor gigantische winsten.",
                        'links' => [
                            ['text' => 'Behandeling & Verlof', 'url' => route('treatment.index')],
                        ],
                    ],
                    [
                        'q' => 'Wat is Pillen Palmen in de Ziekenboeg en wat zijn de winsten en risico\'s?',
                        'a' => "Naast gratis HP-herstel kun je in de Ziekenboeg kiezen voor de illegale actie 'Pillen Palmen':\n\n• 💊 De Actie: Tijdens het medicatie-uitdeeluurtje verstop je zware verdovende pillen onder je tong in plaats van ze in te slikken.\n• 💰 Bij Succes: Je smokkelt de pillen mee naar je cel en verkoopt ze voor duizenden euro's aan afdelingsdealers (+flinke XP);\n• 🚨 Bij Betrapping: Zuster Brenda of de zaalwacht merkt het en gooit je direct de Isoleercel in!\n\nBeginnersadvies: Palm alleen pillen als je een bivakmuts/masker bezit of wanneer je voldoende tijd hebt voor eventuele celstraf.",
                        'links' => [
                            ['text' => 'Naar de Ziekenboeg', 'url' => route('infirmary.index')],
                        ],
                    ],
                    [
                        'q' => 'Wat zijn Missies & Opdrachten en waarom zijn ze perfect voor beginners?',
                        'a' => "Onder 'Voortgang > Missies & Opdrachten' vind je een gestructureerde reeks taken (zoals je eerste gevecht winnen, je bankrekening vullen, een voertuig kopen en een bende joinen). Missies geven gegarandeerd royale contante beloningen, bonus XP en gidsen je stap voor stap door alle spelfuncties!",
                        'links' => [
                            ['text' => 'Mijn Missies Bekijken', 'url' => route('missions.index')],
                        ],
                    ],
                ],
            ],
            'isoleercel' => [
                'title' => '⛓️ Isoleercel, Straf & Ontsnappingen',
                'icon' => 'fa-dungeon',
                'questions' => [
                    [
                        'q' => 'Waarom zit ik in de Isoleercel en wat kan ik wel of niet doen terwijl ik vastzit?',
                        'a' => "Als een illegale actie (zoals diefstal, afpersen, zakkenrollen, pillen palmen of een mislukte heist) mislukt, betrapt de zaalwacht je op heterdaad. Je wordt direct overgebracht naar Sector Z-04 (de Isoleercel) voor een straftijd.\n\n• ❌ WAT NIET KAN: Je kunt geen misdaden plegen, niet reizen tussen afdelingen en niet vechten op de Luchtplaats;\n• ✅ WAT WEL KAN: Je kunt je bankzaken regelen, chatten met vrienden, naar TBS FM luisteren, je inventaris bekijken en jezelf natuurlijk proberen uit te breken!",
                        'links' => [
                            ['text' => 'Naar Isoleercel', 'url' => route('prison')],
                        ],
                    ],
                    [
                        'q' => 'Hoe breek ik mezelf uit de Isoleercel of verkort ik mijn straf (De 6 ontsnappingsmethoden)?',
                        'a' => "Zit je vast? Ga naar 'Isoleercel' in het menu of klik op de rode waarschuwingsbanner. Er zijn 6 manieren om vrij te komen:\n\n1. 🏃 Luchtschacht-Uitbraak (Zelf Uitbreken): Waag 1x per celstraf een directe ontsnappingspoging (kost 20 Energie). Bij succes ben je DIRECT VRIJ (+15 Respect, +35 XP). Bij mislukking: +90 sec celstraf.\n2. 🪚 IJzerzaagje: Gebruik een ijzerzaagje uit je inventaris om spijlen door te zagen. Elk zaagje haalt direct 30 MINUTEN van je straftijd af!\n3. 🧺 Stoomwasserij-Dienst: Draai een zware ploegendienst tussen de hete stoompersen om je straftijd met 45 seconden in te korten (10 Energie, +15 XP).\n4. 💰 Nachtcipier Omkopen: Schuif €2.500 zwijggeld onder de celdeur door voor 75% kans op 90 seconden strafvermindering.\n5. ⭐ Directe Vrijkoopakte of Video: Koop jezelf direct vrij met 25 Sterren ⭐ via de vrijkoopakte, of bekijk een sponsorvideo via Sponsor-Amnestie in de Sponsorkamer.\n6. 🤝 Externe Hulp: Vraag een bevriende speler of bende-lid om jouw celdeur van buitenaf te forceren met een breekijzer!",
                        'links' => [
                            ['text' => 'Naar Isoleercel Vleugel', 'url' => route('prison')],
                            ['text' => 'Sponsorkamer Video Kijken', 'url' => route('sponsor.index')],
                        ],
                    ],
                    [
                        'q' => 'Waarom zijn de knoppen bij medepatiënten vergrendeld als ik zelf in de isoleercel zit?',
                        'a' => "Dat hangt af van je actuele status:\n\n• 🔴 Zit je zélf in de isoleercel? Dan zijn de knoppen onderaan bij medepatiënten vergrendeld met de melding 'Je Zit Zelf Vast'. Vanuit je eigen afgesloten cel kun je immers niet bij de deuren van anderen komen. Gebruik het bovenste actiepaneel om jezelf te bevrijden (luchtschacht, zaagje, wasserij, omkopen of vrijkopen).\n• 🟢 Ben je op vrije voeten? Dan hoef je jezelf niet uit te breken (je celdeur staat al open!). Je kunt dan juist wél ingesloten medepatiënten helpen bevrijden met een breekijzer of sluipuitbraak.",
                    ],
                    [
                        'q' => 'Wat is de geheime Uitbraaktunnel onder mijn celvloer en wat levert 100 meter graven op?',
                        'a' => "Onder 'Illegale Acties > Uitbraaktunnel' kun je met een aangeschafte schep in het geheim een tunnel graven onder de vloer van je cel:\n\n• ⛏️ Graven: Graaf elke dag meters vooruit (kost energie) en dump het zand ongezien op de binnenplaats;\n• 🚨 Waakzaamheid bewakers: Let op dat de cipiers je zand niet ontdekken;\n• 🏆 De Grote Uitbraak (100 Meter): Zodra je tunnel de buitenmuur (100 meter) bereikt, ontgrendel je de legendarische Grote Uitbraak! Dit levert een gigantische geldbuit op, veel XP, respect, 25 gratis Sterren ⭐ én 3 uur voortvluchtige status met toegang tot exclusieve heists!",
                        'links' => [
                            ['text' => 'Naar Uitbraaktunnel', 'url' => route('tunnel.index')],
                        ],
                    ],
                    [
                        'q' => 'Hoe halveren maskers (bivakmutsen) mijn pakkans bij arrestaties?',
                        'a' => "Een bivakmuts of masker is het meest waardevolle kledingstuk voor een crimineel in de kliniek:\n\nZodra je een masker in je inventaris bezit, halveert het spel automatisch jouw pakkans bij vrijwel alle misdrijven, zakkenrollerijen en heists! Word je toch een keer betrapt? Dan verslijt je masker ter bescherming van je identiteit. Zorg dus dat je altijd een voorraadje maskers hebt via zakkenrollen of de marktplaats.",
                        'links' => [
                            ['text' => 'Zakkenrollen voor Maskers', 'url' => route('pickpocket.index')],
                        ],
                    ],
                    [
                        'q' => 'Hoe kan een vriend of bende-lid mij van buitenaf bevrijden?',
                        'a' => "Als jij vastzit, kan een vriend die op vrije voeten is naar de isolatievleugel gaan. Bij jouw naam staat een knop 'Uitbreken'. Met behulp van een breekijzer of een geruisloze sluipactie kan jouw vriend het slot van jouw cel forceren. Lukt het? Dan sta je per direct weer buiten op de gang!",
                    ],
                ],
            ],
            'illegale_acties' => [
                'title' => '🕵️ Illegale Acties, Smokkel & Diefstal',
                'icon' => 'fa-handcuffs',
                'questions' => [
                    [
                        'q' => 'Wat is het verschil tussen Hosselen, Klusjes & Diefstal en Smokkelen?',
                        'a' => "• 🚬 HOSSELEN ('/hosselen'): Korte, snelle straatklusjes op de gang (zoals shag rollen, telefoons verhandelen of aanstekers doorverkopen). Laag energieverbruik, directe contante uitbetaling en snelle XP.\n• 🥷 DIEFSTAL ('/diefstal'): Zwaardere inbraken en diefstallen met oplopende risico's en hogere buitbedragen.\n• 🌿 SMOKKELEN & DEALEN ('/smokkelen'): Een uitgebreid handelssysteem. Je koopt grondstoffen (wietzaadjes, chemische componenten), laat ze rijpen in je cel en verkoopt de partijen met flinke winstmarges aan afdelingsdealers!",
                        'links' => [
                            ['text' => 'Hosselen', 'url' => route('hustle.index')],
                            ['text' => 'Diefstal', 'url' => route('theft.index')],
                            ['text' => 'Smokkelen', 'url' => route('smuggle.index')],
                        ],
                    ],
                    [
                        'q' => 'Hoe werkt Hosselen op de gang (shag rollen, telefoons verhandelen)?',
                        'a' => "Onder 'Illegale Acties > Hosselen' kies je uit verschillende snelle handeltjes. Elk handeltje heeft een succeskans en een energieprijs. Bij succes ontvang je direct contant geld en XP. Mislukt de deal? Dan loop je kans dat de zaalwacht je hoort en je in de cel gooit.",
                        'links' => [
                            ['text' => 'Direct Hosselen', 'url' => route('hustle.index')],
                        ],
                    ],
                    [
                        'q' => 'Hoe werkt Zakkenrollen en hoe steel ik bivakmutsen/maskers?',
                        'a' => "Onder 'Illegale Acties > Zakkenrollen' besluip je zaalwachten, bezoekers of medepatiënten:\n\n1. 👁️ Eerst Observeren: Observeer je doelwit om te zien hoeveel zakgeld ze bij zich dragen en of ze alert zijn;\n2. 🖐️ Zakkenrollen: Sla op het juiste moment toe! Je kunt contant geld stelen én zeldzame bivakmutsen/maskers.\nOnthoud: maskers halveren je pakkans bij latere misdrijven, dus zakkenrollen is dé manier om gratis maskers te scoren!",
                        'links' => [
                            ['text' => 'Naar Zakkenrollen', 'url' => route('pickpocket.index')],
                        ],
                    ],
                    [
                        'q' => 'Hoe werkt Afpersen en wie kan ik onder druk zetten voor wekelijks zwijggeld?',
                        'a' => "Onder 'Illegale Acties > Afpersen' zet je doelwitten onder zware druk voor wekelijks zwijggeld:\n• Zwakkere patiënten (makkelijk doelwit, laag risico);\n• Zaalwachten en verplegers (gemiddeld risico, goede uitbetaling);\n• Corrupte directieleden en externe leveranciers (hoog risico, gigantische buit!).\nZorg dat je voldoende Kracht hebt om overtuigend over te komen!",
                        'links' => [
                            ['text' => 'Naar Afpersen', 'url' => route('extort.index')],
                        ],
                    ],
                    [
                        'q' => 'Wat zijn Tennisbal & Drone Droppings op de luchtplaats?',
                        'a' => "Vrienden en handlangers van buiten de kliniek smokkelen goederen over de metershoge gevangenismuren:\n• 🎾 Tennisbal Droppings: Vang over de muur geworpen tennisballen op tijdens het luchten voor contant geld, gereedschap en kluiscodes;\n• 🛸 Drone Droppings: Bestuur een geheime nachtdrone boven de binnenplaats om zeldzame kluismunten en smokkeltassen binnen te halen!",
                        'links' => [
                            ['text' => 'Smokkel Droppings', 'url' => route('droppings.index')],
                        ],
                    ],
                    [
                        'q' => 'Wat is Kluisjes Kraken in de kleedkamers?',
                        'a' => "In de personeels- en bezoekerskleedkamers staan afgesloten kluisjes ('/kluisjes'). Met behulp van een loper of lockpicks kun je proberen deze open te breken voor kluismunten, contant geld, zeldzame wapens en waardevolle documenten.",
                        'links' => [
                            ['text' => 'Kluisjes Kraken', 'url' => route('lockers.index')],
                        ],
                    ],
                    [
                        'q' => 'Wat is Hacking & Cybercrime in de kliniek?',
                        'a' => "In het computerlokaal ('/hacking') kun je met een gesmokkelde USB-stick of laptop inbreken in de interne klinieksystemen. Hack bewakingscamera's, pas elektronische dossiers aan of sluip binnen in de financiële administratie voor unieke bonussen!",
                        'links' => [
                            ['text' => 'Naar Hacking', 'url' => route('hacking.index')],
                        ],
                    ],
                ],
            ],
            'garage_heists' => [
                'title' => '🏎️ Smokkel Garage, Voertuigen & Grote Heists',
                'icon' => 'fa-masks-theater',
                'questions' => [
                    [
                        'q' => 'Hoe werkt de Smokkel Garage en welke voertuigen kan ik bezitten (van scooter tot helikopter)?',
                        'a' => "In de Smokkel Garage ('Economie > Smokkel Garage') koop je snelle vluchtvoertuigen met contant geld of Sterren ⭐:\n\n• 🛵 Brommers & Scooters: Tomos (€1.200) en Piaggio Zip (€2.500) - ideaal voor beginners;\n• 🚗 Straatauto's & Snelle Wagens: Volkswagen Golf GTI, BMW M5, Audi RS6;\n• 🛥️ Maritiem & Zwaar Pantser: Snelle Speedboot en Gepantserde Mercedes G63 AMG;\n• 🚁 Luchtvaart: Robinson R44 Helikopter en Cessna Smokkelvliegtuig!\n\nElk voertuig heeft een eigen Topsnelheid en Conditiemeter. Hoe sneller je wagen, hoe groter de heists die je kunt overvallen!",
                        'links' => [
                            ['text' => 'Naar Smokkel Garage', 'url' => route('garage.index')],
                        ],
                    ],
                    [
                        'q' => 'Waarom heeft een voertuig Conditie en hoe repareer ik schade na een achtervolging?',
                        'a' => "Wanneer je een voertuig inzet bij Grote Heists, loopt het voertuig schade op door politieachtervolgingen, spijkerstrips en kogelinslagen:\n\n• 📉 Lagere conditie betekent dat de topsnelheid van je voertuig afneemt;\n• 🚨 Is de conditie te laag? Dan daalt je slaagkans bij heists drastisch en loop je groot risico om klemgereden te worden en in de cel te belanden!\n• 🔧 Repareren: In de Smokkel Garage klik je simpelweg op 'Repareren' om monteurs je wagen weer naar 100% topconditie te laten brengen.",
                        'links' => [
                            ['text' => 'Voertuig Repareren', 'url' => route('garage.index')],
                        ],
                    ],
                    [
                        'q' => 'Hoe voer ik een Grote Heist uit (Juwelier, Casino, Bank, Containers) en wat heb ik nodig?',
                        'a' => "Onder 'Illegale Acties > Grote Heists' vind je de zwaarste overvallen van het spel:\n\n1. 💎 Juwelierskraak (Level 5+)\n2. 💊 Medicijn Transport Overval (Level 8+)\n3. 🎰 Casino Geldtransport (Level 12+)\n4. 🚢 Haven Container Heist (Level 15+)\n5. 🏦 Centrale Bank Goudkluis (Level 20+)\n\n📋 VEREISTEN:\n• Een actief vluchtvoertuig in je garage met voldoende topsnelheid en goede conditie;\n• Minimaal 1 Bivakmuts/Masker tegen cameraherkenning;\n• Voldoende Energie om de overval in te zetten.",
                        'links' => [
                            ['text' => 'Naar Grote Heists', 'url' => route('heists.index')],
                        ],
                    ],
                    [
                        'q' => 'Wat zijn Bende-Heists en hoe overval je grote doelen samen met je bendeleden?',
                        'a' => "Bende-Heists zijn grootschalige operaties die je samen met je bende plant. Leden leggen gezamenlijk voertuigen, maskers en vuurkracht in. Slaagt de overval? Dan wordt de megabuit van honderdduizenden euro's gestort in de gezamenlijke Bende-Kluis!",
                        'links' => [
                            ['text' => 'Bende Overzicht', 'url' => route('gangs.index')],
                        ],
                    ],
                    [
                        'q' => 'Wat levert een geslaagde Heist op en wat is de straf als het mislukt?',
                        'a' => "• 💰 BIJ SUCCES: Gigantische contante buit (tienduizenden tot honderdduizenden euro's), honderden XP en zeldzame kluiscodes voor de Centrale Medicijnkluis!\n• 🚨 BIJ MISLUKKING: De alarmbellen gaan af, je voertuig raakt zwaar beschadigd en je vliegt voor langere tijd direct de Isoleercel in.",
                    ],
                ],
            ],
            'koffers_kraken' => [
                'title' => '📦 Koffers Onderscheppen, Kraken & XXL Buit',
                'icon' => 'fa-briefcase',
                'questions' => [
                    [
                        'q' => 'Hoe werkt Koffers Onderscheppen op het perron en hoe kraak ik ze?',
                        'a' => "Onder 'Voortgang > Koffers' kun je smokkelkoffers onderscheppen die het kliniekterrein op worden gesmokkeld via het laadperron. Onderschepte koffers verschijnen in je kofferdepot. Klik op 'Kraken' om het slot te forceren met breekijzers of lockpicks en claim direct de inhoud!",
                        'links' => [
                            ['text' => 'Koffers Onderscheppen', 'url' => route('packages.index')],
                        ],
                    ],
                    [
                        'q' => 'Wat zijn de verschillen tussen Gewone Tassen, Buitkratten en XXL Koffers?',
                        'a' => "• 🎒 GEWONE SMOKKELTASSEN: Bevatten kleine contante bedragen, kantinesnacks of wat munitie;\n• 📦 BUITKRATTEN: Bevatten substantiële contante bedragen (€10.000 - €50.000), verbanddozen en gereedschap;\n• 💼 XXL KOFFERS: De allerzwaarste, met staal verstevigde koffers met gegarandeerde topbeloningen!",
                    ],
                    [
                        'q' => 'Wat zit er in een XXL Koffer (tot €250.000 cash, Sterren, munitie, maskers, stickers)?',
                        'a' => "XXL Koffers zijn de absolute jackpot onder de koffers! Ze bevatten gegarandeerd zeldzame topbeloningen:\n• Tot wel €250.000 contant geld;\n• Gratis Gouden Sterren ⭐;\n• Scherpe patronen voor Liquidatie 2.0;\n• Zeldzame bivakmutsen en maskers;\n• Unieke profielstickers en verzamelobjecten die nergens anders verkrijgbaar zijn!",
                        'links' => [
                            ['text' => 'Naar Koffers', 'url' => route('packages.index')],
                        ],
                    ],
                    [
                        'q' => 'Welk gereedschap heb ik nodig om zware koffers te openen (breekijzers, lockpicks)?',
                        'a' => "Om stevige koffers open te breken heb je gereedschap nodig:\n• Breekijzers: Ideaal voor het snel openwrikken van kratten;\n• Lockpick sets: Nodig om cijfersloten schadevrij te openen;\nBeide gereedschappen vind je tijdens werkplaatswerkzaamheden, hosselen of koop je direct in de Kliniek Winkel.",
                        'links' => [
                            ['text' => 'Winkel Gereedschap', 'url' => route('shop.index')],
                        ],
                    ],
                ],
            ],
            'vechten' => [
                'title' => '🥊 Respectlijst, Vechten & Gevechtsregels',
                'icon' => 'fa-hand-fist',
                'questions' => [
                    [
                        'q' => 'Hoe werkt de Respectlijst op de luchtplaats en hoe daag ik iemand uit?',
                        'a' => "Op de Luchtplaats ('Geweld & Strijd > Luchtplaats / Respectlijst') zie je alle actieve patiënten in de kliniek gerangschikt op respect en niveau. Klik op 'Aanvallen' naast een speler om een duel te starten. Het gevechtssysteem vergelijkt jouw Kracht en Snelheid met de Verdediging en Snelheid van je tegenstander.",
                        'links' => [
                            ['text' => 'Naar Luchtplaats', 'url' => route('fight.index')],
                        ],
                    ],
                    [
                        'q' => 'Hoeveel aanvallen mag ik per dag uitvoeren (100 voor standaards / 1.000 voor VIP, reset 00:00)?',
                        'a' => "Om het spel eerlijk te houden voor beginners en pesterijen tegen te gaan geldt een strikt dagelijks limiet:\n• Standaard spelers: Maximaal 100 aanvallen per dag in totaal, en maximaal 100 aanvallen per dag op hetzelfde specifieke doelwit;\n• VIP-leden: Mogen profiteren van een verhoogd limiet tot maar liefst 1.000 gevechten per dag op de Luchtplaats!\nAlle aanvalstellers worden elke nacht automatisch om 00:00 uur gereset.",
                    ],
                    [
                        'q' => 'Wat is de 5-seconden Arena Showdown en hoe wordt de winnaar berekend?',
                        'a' => "Zodra je op 'Aanvallen' klikt betreed je de interactieve gevechtsarena. Gedurende 5 seconden zie en hoor je de rake klappen, ontwijkingen en kritieke treffers over en weer vliegen. De winnaar wordt berekend op basis van:\n• Jouw Kracht vs de Verdediging van de tegenstander;\n• Uitgeruste wapens en bepantsering;\n• Snelheid (kans op ontwijken en initiatief);\n• Een kleine willekeurige gevechtsfactor (kritieke treffer).",
                    ],
                    [
                        'q' => 'Wat win of verlies ik bij een gevecht op de Respectlijst (respect en tot 15% contant geld)?',
                        'a' => "• 🏆 ALS JE WINT: Je steelt respectpunten van je tegenstander én plundert tot 15% van zijn contante zakgeld! (Geld op zijn bankrekening blijft veilig);\n• 🤕 ALS JE VERLIEST: Je incasseert Gezondheidsschade (HP verlies), verliest een klein beetje respect en als jij contant geld op zak had, kan de verdediger een deel van jouw zakgeld afpakken! Zorg dus dat je contant geld ALTIJD op de bank staat.",
                    ],
                    [
                        'q' => 'Wat als ik steeds word aangevallen door sterkere spelers (Vrijwillige Isolatie / bunker)?',
                        'a' => "Geen paniek! Je hebt 3 ijzersterke manieren om jezelf te beschermen:\n\n1. 🛡️ Vrijwillige Isolatie ('Geweld & Strijd > Vrijwillige Isolatie'): Sluit jezelf tijdelijk vrijwillig op in de beveiligde bunker. Zolang deze modus actief is, kan NIEMAND op de hele Respectlijst jou aanvallen!\n2. 🏦 Bank: Zet AL je contante geld op de bank. Als aanvallers niks van je kunnen stelen, houden ze vanzelf op;\n3. 🎯 Kopgeld: Plaats een flinke bounty op het hoofd van je belager, zodat zwaardere veteranen hem voor jou aanpakken!",
                        'links' => [
                            ['text' => 'Vrijwillige Isolatie Activeren', 'url' => route('isolation.index')],
                        ],
                    ],
                    [
                        'q' => 'Hoe werken Kopgeld (Bounties) op rivalen plaatsen?',
                        'a' => "Onder 'Geweld & Strijd > Kopgeld' kun je een contante premie uitloven op het hoofd van een medespeler. Zodra een andere patiënt die rivaal verslaat op de Luchtplaats of liquideert, int diegene automatisch de volledige premie en ontvang jij een melding!",
                        'links' => [
                            ['text' => 'Kopgeld Plaatsen', 'url' => route('bounties.index')],
                        ],
                    ],
                    [
                        'q' => 'Wat doen Informanten en welke geheime inlichtingen verkopen ze?',
                        'a' => "Onder 'Geweld & Strijd > Informanten' koop je vertrouwelijke inlichtingen over vermogende patiënten, actuele bewakingspatrouilles en geheime geldstromen. Zo weet je precies wie veel contant geld op zak heeft!",
                        'links' => [
                            ['text' => 'Naar Informanten', 'url' => route('informants.index')],
                        ],
                    ],
                    [
                        'q' => 'Wat is Armpjedrukken in de recreatieruimte?',
                        'a' => "In de recreatiezaal ('/armpjedrukken') daag je medespelers uit voor een potje armpjedrukken om contant geld. Wie het snelst klikt en de hoogste Krachtstatistiek heeft wint de inzet!",
                        'links' => [
                            ['text' => 'Armpjedrukken', 'url' => route('armwrestling.index')],
                        ],
                    ],
                ],
            ],
            'liquidatie_pvp' => [
                'title' => '🎯 PvP Liquidatie 2.0, Kogels & Bloedwraak',
                'icon' => 'fa-crosshairs',
                'questions' => [
                    [
                        'q' => 'Wat is Liquidatie 2.0 & Wapenarsenaal en hoe verschilt het van gewone vuistgevechten?',
                        'a' => "Liquidatie 2.0 ('Geweld & Strijd > Liquidatie') is het zware PvP-systeem van TBS Kliniek. In tegenstelling tot vuistduels op de luchtplaats schiet je hier met scherpe patronen op rivalen:\n\n• 📍 Zelfde Afdeling: Je moet fysiek op dezelfde afdeling staan als je slachtoffer;\n• 🩸 Enorme Buit: Een voltreffer rooft maar liefst 45% contant geld én 7% van de bankrekening van het slachtoffer!\n• 🔫 Patronen: Kogels koop je bij de Hitman Wapenhandelaar of verdien je gratis in de Sponsorkamer (3 video-ads = +50 kogels).",
                        'links' => [
                            ['text' => 'Naar Liquidatie', 'url' => route('hitman.index')],
                        ],
                    ],
                    [
                        'q' => 'Waarom moet ik op dezelfde afdeling staan en hoe werkt Camera- & Gangpad Schaduwing (€350k)?',
                        'a' => 'Je kunt niemand neerschieten die zich in een andere vleugel bevindt. Om je doelwit te lokaliseren gebruik je Camera- & Gangpad Schaduwing (€350.000). Het systeem zoekt 15 minuten lang en onthult de exacte afdeling waar je rivaal zich bevindt. Reis er direct heen via Snel Reizen om toe te slaan!',
                        'links' => [
                            ['text' => 'Schaduwen & Zoeken', 'url' => route('hitman.index')],
                        ],
                    ],
                    [
                        'q' => 'Hoe werken de 30 Dossier-Gradaties en Koudbloedigheid (1-patroon genadeschot vs salvo)?',
                        'a' => "• Vanaf Dossier-Graad 25 (Forensisch Scherpschutter) met minimaal 30% Koudbloedigheid kun je een dodelijk 1-Patroon Genadeschot afvuren;\n• Onder Graad 25 (of met minder dan 30% Koudbloedigheid) vuur je een salvo van minimaal 10 patronen af. Koudbloedigheid train je door liquidaties en heists uit te voeren.",
                    ],
                    [
                        'q' => 'Wat gebeurt er als ik word geliquideerd (Brancard-Evacuatie vs 10/10 Code Blauw coma)?',
                        'a' => "1. 🚑 Liquidatie 1 t/m 9 (Geen Coma, direct doorspelen!): Je zit niet vast op een comascherm! Je overleeft op 20 HP, wordt per brancard geëvacueerd naar een willekeurige andere kliniekafdeling en ontvangt 15 minuten een beschermend Medisch Infuus-Schild. Je kunt direct snacks eten om te genezen en gewoon doorspelen!\n\n2. 💉 10x Geliquideerd = Code Blauw (Hartstilstand): Zodra jouw teller 10/10 liquidaties bereikt, treedt Code Blauw in en raak je comateus. Om weer tot leven te komen met 100% HP en je teller te resetten naar 0/10, koop je het Forensisch Reanimatie-Serum voor €1.500.000 (contant/bank) of 75 Sterren ⭐.",
                    ],
                    [
                        'q' => 'Hoe kom ik weer tot leven bij Code Blauw (Forensisch Reanimatie-Serum €1.5M / 75 Sterren of noodkrediet)?',
                        'a' => 'Mocht je comateus zijn geraakt door 10 liquidaties, dan koop je het Epinefrine Reanimatie-Serum via de comapagina voor €1.500.000 of 75 Sterren ⭐. Mocht je minder dan €1,5M bezitten, dan treedt de medische noodkrediet-regeling automatisch in werking, zodat je nooit permanent vastloopt in het spel!',
                    ],
                    [
                        'q' => 'Hoe werken de 4 Backfire-Vesten en het Ricochet-terugkaatsingseffect?',
                        'a' => "In het Wapenarsenaal kies je uit 4 kogelwerende pantserklassen:\n• Licht Kevlar Ondervest (1u) - €500.000 of 35 ⭐ (30% Backfire kans)\n• Zwaar Tactisch Backfire-Vest (3u) - €1.250.000 of 65 ⭐ (55% Backfire kans)\n• Syndicaat Keramisch Pantser (6u) - €2.400.000 of 110 ⭐ (65% Backfire kans)\n• Directie Titanium Exosuit (12u) - €4.500.000 of 150 ⭐ (75% Backfire kans)\n\n⚠️ RICOCHET EFFECT: Als een rivaal op jou schiet terwijl jij een vest draagt, ketst de kogel met de aangegeven kans terug! De schutter incasseert zélf een fatale treffer op zijn eigen liquidatieteller en wordt per brancard afgevoerd!",
                    ],
                    [
                        'q' => 'Wat is Bloedwraak (Blood Feuds) voor bendes en welke bonussen geeft het (+50 respect, €25k, +150 bende-respect)?',
                        'a' => "Als jij of een lid van jouw bende wordt neergeschoten door een rivaal, ontstaat er automatisch een officiële Bloedwraak Vete die 24 uur actief blijft!\n\n🔥 DE VOORDELEN VAN BLOEDWRAAK:\n• Levelrestricties vervallen: De dader mag direct worden neergeschoten, zelfs als het levelverschil groter is dan 15 levels!\n• Cooldowns vervallen: De standaard wachttijd geldt niet tegen de dader.\n• Enorme Wraakbonussen: De speler of het bendelid dat de dader terugslaat verdient +50 extra Respect, een contante bonus van €25.000 én +150 Bende Respect voor de hele club!",
                    ],
                    [
                        'q' => 'Hoe bescherm ik mijn banktegoed en contant geld tegen Liquidaties (24u Cash Bescherming)?',
                        'a' => "De ultieme bescherming tegen PvP Liquidaties is de '24 Uur Cash Bescherming' in de VIP Shop: zolang deze actief is, kan er bij liquidaties 0 euro contant en 0 euro van je bankrekening worden geroofd!",
                        'links' => [
                            ['text' => 'VIP Shop Bescherming', 'url' => route('vip.index')],
                        ],
                    ],
                ],
            ],
            'economie' => [
                'title' => '🏦 Bank, Verplaatsen, Winkel & Marktplaats',
                'icon' => 'fa-building-columns',
                'questions' => [
                    [
                        'q' => 'Waarom moet ik AL mijn geld op de Bank zetten en hoe werkt het automatische zakgeld (€1.000 per 15 min)?',
                        'a' => 'Geld op zak is vogelvrij. Andere spelers kunnen jouw contante geld stelen via de Respectlijst of via zakkenrollen. Geld op je bankrekening is 100% veilig beschermd tegen gevechten op de Respectlijst en zakkenrollen. Bovendien ontvangen patiënten die nog niet in een bende zitten elke 15 minuten automatisch een zakgeld-uitkering van €1.000 op hun bankrekening!',
                        'links' => [
                            ['text' => 'Direct naar De Bank', 'url' => route('bank.index')],
                        ],
                    ],
                    [
                        'q' => 'Wat kost geld opnemen van de bank (5% belasting standaard / 3% VIP) en wat gebeurt er met die belasting?',
                        'a' => "Als je contant geld van je bankrekening opneemt, geldt een kleine transactiebelasting:\n• Standaard spelers: 5% belasting;\n• VIP-leden: Slechts 3% belasting.\n\n🎰 DE JACKPOT LINK: Iedere cent die aan bankbelasting wordt ingehouden, vloeit rechtstreeks in de gigantische Jackpot van de 14-daagse TBS Afdelingsloterij!",
                        'links' => [
                            ['text' => 'Naar De Loterij', 'url' => route('lottery.index')],
                        ],
                    ],
                    [
                        'q' => 'Hoe werkt Verplaatsen (Reizen) tussen paviljoens en hoe verdien ik +35 XP per reis?',
                        'a' => "Onder 'Economie > Verplaatsen' koop je bewakers om om te reizen tussen kliniekvleugels (Paviljoen 1 Gesloten, Paviljoen 2 Half-open, Het Kookhuis Keuken, De Recreatiezaal en De Buitenplaats). Elke succesvolle reis levert je direct +35 XP op! VIP-leden betalen slechts de helft van het omkoopgeld.",
                        'links' => [
                            ['text' => 'Verplaatsen / Reizen', 'url' => route('travel.index')],
                        ],
                    ],
                    [
                        'q' => 'Wat is de Marktplaats en hoe verhandel ik wapens, voertuigen en koffers met andere spelers?',
                        'a' => "Op de Marktplaats ('Economie > Marktplaats') handelen patiënten onderling in wapens, bepantsering, koffers, voertuigen en kluismunten. Je kunt zelf voorwerpen te koop aanbieden voor je eigen gekozen vraagprijs, of koopjes inslaan van andere spelers.",
                        'links' => [
                            ['text' => 'Naar Marktplaats', 'url' => route('marketplace.index')],
                        ],
                    ],
                    [
                        'q' => 'Wat kan ik kopen in de Kliniek Winkel en hoe beheer ik mijn Inventaris?',
                        'a' => "In de Kliniek Winkel ('/winkel') koop je wapens (van boksbeugels en messen tot zware vuurwapens), kogelwerende vesten, EHBO-verbanddozen, gereedschap en snacks. Al je aankopen verschijnen in 'Mijn Inventaris'.",
                        'links' => [
                            ['text' => 'Kliniek Winkel', 'url' => route('shop.index')],
                        ],
                    ],
                    [
                        'q' => 'Wat is het Depot en hoe sla ik extra spullen op?',
                        'a' => "In het Depot ('/depot') bewaar je overtollige wapens, koffers en materialen die je niet direct in je broekzakken wilt meedragen. VIP-leden hebben standaard extra opslagcapaciteit in het Depot.",
                        'links' => [
                            ['text' => 'Naar Depot', 'url' => route('depot.index')],
                        ],
                    ],
                ],
            ],
            'loterij_casino' => [
                'title' => '🎰 Casino, Minigames & Afdelingsloterij',
                'icon' => 'fa-dice',
                'questions' => [
                    [
                        'q' => 'Wat is de TBS Afdelingsloterij en hoe win ik de gigantische 14-daagse Jackpot?',
                        'a' => "De Afdelingsloterij is de officiële megatrekking van de TBS Kliniek met een vaste cyclus van 14 dagen! Iedere cent bank-opnamebelasting en rente vloeit rechtstreeks in deze Jackpot.\n\n👑 Belangrijke Loterij Regels:\n• 🎟️ Lotprijs: Een lot kost €10.000 contant of 5 Sterren ⭐;\n• ⚡ Laatste Kwartier Korting: In de laatste 15 minuten voor de trekking geldt 50% korting op alle loten (€5.000 per lot);\n• 💰 Uitbetaling: De winnaar ontvangt 30% veilig op de bank en 70% direct contant als zakgeld!\n• 🔄 Nieuwe Ronde: Na de trekking start per direct een nieuwe ronde met een startpot van €25.000.",
                        'links' => [
                            ['text' => 'Loten Kopen', 'url' => route('lottery.index')],
                        ],
                    ],
                    [
                        'q' => 'Welke gokspellen kan ik spelen in het Casino (Roulette, Hoger/Lager, Nummerspel, Steen-Papier-Schaar, Slots)?',
                        'a' => "In het Casino ('Economie & Gokken > Casino') vind je 5 complete spellen:\n• 🔴 ROULETTE: Zet in op Rood, Zwart, Even, Oneven of specifieke getallen (tot 36x uitbetaling);\n• 🃏 HOGER / LAGER: Raad of de volgende speelkaart hoger of lager is dan de huidige kaart;\n• 🔢 NUMMERSPEL: Raad het geheime getal tussen 1 en 10 voor een snelle winst;\n• ✌️ STEEN-PAPIER-SCHAAR: Speel tegen het huis voor dubbele inleg;\n• 🍒 FRUITHAL / SLOTS: Draai aan de rollen voor jokers en de fruitjackpot!",
                        'links' => [
                            ['text' => 'Naar het Casino', 'url' => route('casino.index')],
                        ],
                    ],
                    [
                        'q' => 'Hoe werkt de \'Laatste Kwartier Korting\' (50% korting op loten in de laatste 15 minuten)?',
                        'a' => 'In de laatste 15 minuten voordat de klok van de 14-daagse loterij afloopt, halveert de lotprijs automatisch van €10.000 naar slechts €5.000 per lot! Dit is het ideale moment om massaal extra loten in te slaan en jouw winkans te verveelvoudigen.',
                    ],
                    [
                        'q' => 'Hoe wordt de Jackpot uitbetaald aan de winnaar (30% bank, 70% direct contant)?',
                        'a' => "Om het spel spannend te houden wordt de Jackpotpot gesplitst uitbetaald:\n• 30% wordt direct veilig op je bankrekening gestort (beschermd tegen rovers);\n• 70% wordt contant als zakgeld uitbetaald! Ga dus direct naar de bank om dit megabedrag veilig te stellen voordat andere spelers je aanvallen!",
                    ],
                    [
                        'q' => 'Wat zijn de minigames Vier-op-een-rij en True Crime dossiers?',
                        'a' => "• 🔴 VIER-OP-EEN-RIJ ('/vier-op-een-rij'): Speel een tactisch potje vier-op-een-rij tegen medespelers voor leuke prijzen;\n• 🕵️ TRUE CRIME ('/true-crime'): Los mysterieuze forensische dossiers en politierapporten op voor contant geld en zeldzame kluiscodes!",
                        'links' => [
                            ['text' => 'Vier-op-een-rij', 'url' => route('connect_four.index')],
                            ['text' => 'True Crime', 'url' => route('true_crime.index')],
                        ],
                    ],
                ],
            ],
            'gangs' => [
                'title' => '👥 Bendes, Gangs & Bende-Uurloon',
                'icon' => 'fa-users',
                'questions' => [
                    [
                        'q' => 'Wat is de Gang Overstap Bonus en hoe claimen we €5.000.000 + 31 Dagen Gratis VIP?',
                        'a' => "Speel jij met je bende of vriendengroep op een ander misdaadspel (zoals Barribob, Boevenbende, OSG, etc.) en stappen jullie over naar TBS Kliniek? Dan belonen we jullie vorstelijk met de officiële Gang Overstap Bonus!\n\n👑 WAT JULLIE ONTVANGEN:\n• 🛡️ Direct gratis oprichting van jullie eigen bende (geen level- of sterrenkosten);\n• 💰 € 5.000.000 kluisgeld direct gestort in jullie gezamenlijke bende-pot;\n• ⭐ 31 DAGEN GRATIS VIP voor de leider én alle overstappende bendeleden!\n\n📋 HOE AANVRAGEN?\n1. Open een Support Ticket via 'Ondersteuning > Nieuw Ticket Aanmaken';\n2. Selecteer de categorie '🔥 Gang Overstap Bonus';\n3. Vul jullie bendenaam, gewenste tag en een bewijslink van de andere website in;\n4. Zodra het team de aanvraag goedkeurt, staat alles direct klaar in het spel!",
                        'links' => [
                            ['text' => 'Overstap Ticket Aanmaken', 'url' => route('help.tickets.create')],
                        ],
                    ],
                    [
                        'q' => 'Hoe richt ik een bende op vanaf Level 3 en wat kost het?',
                        'a' => "Zodra je karakter Level 3 heeft bereikt, ontgrendelt de optie om een bende op te richten ('Bendes > Oprichten'). Als oprichter kies je een unieke bendenaam, een 3- of 4-letterige clan-tag en een clubembleem. Je kunt vervolgens medespelers uitnodigen via hun profiel of de ledenlijst.",
                        'links' => [
                            ['text' => 'Naar Bendes', 'url' => route('gangs.index')],
                        ],
                    ],
                    [
                        'q' => 'Hoe werkt het Bende-Uurloon en hoe levert vastgoed elk uur passief inkomen op (tot 7 dagen bewaard)?',
                        'a' => "Bendes genereren ieder uur automatisch inkomsten voor al hun actieve leden! Het uurloon wordt berekend op basis van:\n• Basisuurloon (€150) plus €100 extra per aangeschafte Smokkelkiosk;\n• Bende-Vastgoed (loodsen, garageboxcomplexen en bende-villa's die €1.000 tot €75.000+ per uur opleveren);\n• Ingehuurde Bende-NPC's (+€500 per specialist);\n• Bende-Respect bonus.\nNiet-geclaimd loon wordt tot wel 7 dagen (168 uur) bewaard, dus je loopt nooit loon mis!",
                        'links' => [
                            ['text' => 'Mijn Bende Uurloon', 'url' => route('gangs.index')],
                        ],
                    ],
                    [
                        'q' => 'Wat zijn Bende Oorlogen en hoe plunderen we de kluis van een rivaliserende bende?',
                        'a' => 'Bendes kunnen elkaar de oorlog verklaren in Bende Oorlogen. Door leden van de vijandige bende te verslaan op de Luchtplaats of te liquideren verdient jouw club oorlogspunten. De bende die aan het einde voorstaat, plundert een deel van de gezamenlijke kluis van de tegenstander!',
                    ],
                    [
                        'q' => 'Wat zijn Territorium Controlepunten en welke permanente bonussen geven ze aan je gang?',
                        'a' => 'Verspreid over de kliniek liggen strategische controlepunten (de Centrale Keuken, de Luchtplaats-toren, het Laadperron en de Recreatiezaal). Bendes kunnen strijden om de heerschappij over deze punten. Het bezit van een controlepunt levert permanente bonussen op voor alle leden (zoals extra loon, hogere verdediging of sneller energieherstel)!',
                    ],
                    [
                        'q' => 'Wat zijn bende-rollen (leider, co-leider, lid) en hoe werkt de gezamenlijke bende-kluis?',
                        'a' => "De bendeleider kan rollen toewijzen:\n• Leider: Volledige controle over bendenaam, aankopen, oorlogen en kluisuitbetalingen;\n• Co-Leider: Kan leden aannemen, panden kopen en oorlogen managen;\n• Lid: Geniet van het bende-uurloon, de gang-chat en gezamenlijke bescherming;\nAlle leden kunnen geld doneren aan de gezamenlijke Bende-Kluis om gezamenlijk vastgoed aan te schaffen.",
                    ],
                ],
            ],
            'bonussen' => [
                'title' => '🎁 Bonussen, Kluiskraak & Dagelijkse Streaks',
                'icon' => 'fa-vault',
                'questions' => [
                    [
                        'q' => 'Wat is het Rad van Fortuin en hoe vaak mag ik gratis draaien?',
                        'a' => "Het Rad van Fortuin ('Bonus & Kluizen > Rad van Fortuin') is het dagelijkse geluksrad van de inrichting. Standaard spelers mogen elke 24 uur 1x gratis draaien. VIP-leden profiteren zelfs van 2 gratis draaibeurten per dag! De prijzen variëren van contant geld en gratis energie tot Sterren ⭐ en zeldzame koffers.",
                        'links' => [
                            ['text' => 'Draai aan het Rad', 'url' => route('daily.index')],
                        ],
                    ],
                    [
                        'q' => 'Wat is het Dagelijkse Inlog Bonus venster (22:00 - 22:30) en hoe werken Streaks?',
                        'a' => 'Elke avond tussen 22:00 en 22:30 uur is het officiële Inlog Bonus venster geopend! Log je in tijdens dit halfuur, dan ontvang je automatisch 3 gratis kluiscodes én kun je jouw dagelijkse login streak claimen. Door elke dag achter elkaar in te loggen bouw je een 7-daagse streak op met steeds grotere cashbedragen, maskers en kluismunten.',
                    ],
                    [
                        'q' => 'Hoe kraak ik de Centrale Medicijnkluis met de geheime 4-cijferige code en Alarm Jammer?',
                        'a' => "Onder 'Bonus & Kluizen > MedicijnKluis' staat de gigantische centrale kluis met een geheime 4-cijferige code (bijv. 4821):\n\n• 🔍 Aanwijzingen: Na elke gok meldt het systeem of de code 'hoger' of 'lager' is;\n• 🚨 Alarm: Na een aantal foute gokken gaat het alarm af en beland je in de cel;\n• 🛡️ Alarm Jammer: Koop vooraf een Alarm Jammer. Zolang de jammer actief is, wordt het alarm geblokkeerd en kun je ongestoord blijven raden tot je de kluis kraakt en de jackpot int!",
                        'links' => [
                            ['text' => 'Kluis Kraken', 'url' => route('vault.index')],
                        ],
                    ],
                    [
                        'q' => 'Waar vind ik geheime kluiscodes, kluismunten en actievouchers?',
                        'a' => "Geheime kluiscodes en vouchers vind je op verschillende plekken:\n• In het zaagsel van de Werkplaats bij Dagbesteding;\n• Bij het openen van Smokkelkoffers XXL;\n• Tijdens live radio-evenementen op TBS FM;\n• In speciale promotieberichten op onze officiële Discord server!",
                    ],
                    [
                        'q' => 'Wat is de Battle Pass (Seizoen Pass) en hoe claim ik gratis beloningen?',
                        'a' => "In Seizoen 1 van de TBS Battle Pass ('/seizoen-1/battle-pass') verdien je seizoenspunten door dagelijks te spelen. Ontgrendel gratis mijlpalen met exclusieve profielkaders, contant geld, koffers en sterren!",
                        'links' => [
                            ['text' => 'Battle Pass Bekijken', 'url' => route('battlepass.index')],
                        ],
                    ],
                ],
            ],
            'competitie' => [
                'title' => '🏆 Ranglijsten & 24 Uur Battle',
                'icon' => 'fa-trophy',
                'questions' => [
                    [
                        'q' => 'Hoe werkt de 24 Uur Battle en hoe win ik elke nacht gratis Sterren (5 klassementen om 00:00)?',
                        'a' => "De 24 Uur Battle ('/24-uur-battle') is de dagelijkse afdelingsstrijd van TBS Kliniek. Er zijn 5 klassementen waarin iedereen kan meedingen:\n1. 🥷 Meeste Misdrijven gepleegd;\n2. 🥊 Meeste Gevechten gewonnen op de Luchtplaats;\n3. ⭐ Meeste Respect verdiend;\n4. 🎰 Meeste Casinowinst behaald;\n5. 🔨 Meeste Arbeid verricht in de werkplaats.\n\nElke nacht stipt om 00:00 uur wint de top 3 van elk klassement gratis Sterren ⭐ en exclusieve trofeebadges op hun profiel!",
                        'links' => [
                            ['text' => '24 Uur Battle Stand', 'url' => route('battle.index')],
                        ],
                    ],
                    [
                        'q' => 'Hoe kom ik bovenaan de algemene Respectlijst en Hall of Fame?',
                        'a' => 'De algemene ranglijst sorteert patiënten op totale Respectscore. Je klimt omhoog door tegenstanders met meer aanzien uit te dagen en te verslaan, dagelijks te trainen, missies af te ronden en respectuitwisselingen te doen met vrienden.',
                        'links' => [
                            ['text' => 'Algemene Ranglijst', 'url' => route('leaderboard.index')],
                        ],
                    ],
                    [
                        'q' => 'Wat zijn de Geweldsstatistieken (Murder Stats)?',
                        'a' => "Onder 'Geweldsstatistieken' zie je wie vandaag en in de afgelopen 7 dagen de meeste liquidaties en gevechten op zijn geweten heeft. De gevaarlijkste schutters van de kliniek worden hier publiekelijk tentoongesteld!",
                    ],
                ],
            ],
            'vip_sponsor' => [
                'title' => '👑 VIP Lidmaatschap & Sponsorkamer (100% Gratis!)',
                'icon' => 'fa-crown',
                'questions' => [
                    [
                        'q' => 'Wat kost VIP en waarom is het in TBS Kliniek 100% GRATIS te verdienen?',
                        'a' => "VIP is in TBS Kliniek 100% GRATIS te verdienen! Je hoeft nooit echt geld te betalen om van de hoogste VIP privileges te genieten. Je activeert gratis VIP via:\n\n1. 📺 Sponsorkamer: Bij elke 250 korte 15s videoadvertenties die je bekijkt, ontvang je automatisch 24 UUR GRATIS VIP (oneindig herhaalbaar!);\n2. 🎮 Discord Koppeling: Koppel je account eenmalig met onze officiële Discord server en ontvang direct 3 DAGEN GRATIS VIP + €2.500 cash!",
                        'links' => [
                            ['text' => 'Naar Sponsorkamer', 'url' => route('sponsor.index')],
                            ['text' => 'VIP Shop & Status', 'url' => route('vip.index')],
                        ],
                    ],
                    [
                        'q' => 'Hoe verdien ik 24 UUR GRATIS VIP via de Sponsorkamer (250 advertenties)?',
                        'a' => "In de Sponsorkamer ('/sponsorkamer') bekijk je korte videoadvertenties van 15 seconden. Voor elke bekeken video ontvang je direct €2.500 zakgeld, maskers, munitie of strafvermindering in de cel. Zodra jouw teller 250 advertenties bereikt, activeert het systeem direct 24 UUR GRATIS VIP op je account!",
                        'links' => [
                            ['text' => 'Sponsorkamer Openen', 'url' => route('sponsor.index')],
                        ],
                    ],
                    [
                        'q' => 'Hoe verdien ik 3 DAGEN GRATIS VIP door mijn account te koppelen aan Discord?',
                        'a' => "Klik in het menu op 'Discord' of ga naar '/discord/verifieer'. Koppel je Discord-account binnen 10 seconden met onze Discord Shield bot. Zodra de koppeling geslaagd is, ontvang je direct de officiële Patiënten-rol op Discord, €2.500 startkapitaal én 3 DAGEN GRATIS VIP op jouw account!",
                    ],
                    [
                        'q' => 'Welke voordelen krijg ik als VIP (5 VIP punten/uur, sneller herstel, 2x Rad, 1.000 gevechten, etc.)?',
                        'a' => "Als VIP geniet je van ongekende voordelen:\n• 👑 5 VIP Punten per actief speeluur (inwisselbaar in de VIP Shop);\n• ⚡ Sneller energieherstel (1 punt per 3 minuten i.p.v. 5 minuten);\n• 🎰 2x Dagelijks gratis draaien aan het Rad van Fortuin;\n• 🥊 1.000 max aanvallen per dag op de Luchtplaats (i.p.v. 100);\n• 🗺️ 50% Korting op alle reiskosten tussen paviljoens;\n• 🏦 Lager banktarief (3% i.p.v. 5% belasting bij contant opnemen);\n• 🚗 Extra garage- en opslagcapaciteit in het Depot;\n• ⭐ Gouden VIP-badge en exclusieve status op je profiel;\n• 🎁 Toegang tot de VIP Lounge & Shop.",
                        'links' => [
                            ['text' => 'VIP Voordelen Bekijken', 'url' => route('vip.index')],
                        ],
                    ],
                    [
                        'q' => 'Wat kan ik kopen met VIP Punten in de VIP Shop?',
                        'a' => "In de VIP Shop ('/vip-shop') wissel je verdiende VIP Punten in voor krachtige bonussen:\n• 24 Uur Cash Bescherming tegen liquidatieroof;\n• Direct 100% herstel van Energie & Gezondheid;\n• +50 Respect boost;\n• VIP Buitpakket (2x XXL Cases + €500.000 cash);\n• 100 Gouden Sterren ⭐ of verlenging van je VIP status!",
                        'links' => [
                            ['text' => 'Naar VIP Shop', 'url' => route('vip.index')],
                        ],
                    ],
                    [
                        'q' => 'Zijn donaties via Buy Me a Coffee verplicht en waar gaat het geld naartoe?',
                        'a' => 'TBS Kliniek is een 100% hobbymatig non-profit project zonder winstoogmerk. Je hoeft nooit te betalen. Vrijwillige donaties via Buy Me a Coffee worden uitsluitend gebruikt om de server-, database- en hostingkosten te dekken. Als dank voor je steun ontvang je automatisch de bijbehorende supporter privileges.',
                    ],
                ],
            ],
            'sociaal' => [
                'title' => '📻 Radio, Krant, Tijdlijn, Chat & Vrienden',
                'icon' => 'fa-compass',
                'questions' => [
                    [
                        'q' => 'Wat is Het Afdelingsblad (\'De Kliniekbode\') en wat staat erin?',
                        'a' => "Het Afdelingsblad ('/afdelingsblad') is de officiële ochtendkrant van de TBS Kliniek. Hierin lees je actuele nieuwsberichten van de directie, spectaculaire verslagen van geslaagde overvallen, recente arrestaties in de isoleercel en de winnaars van de Afdelingsloterij.",
                        'links' => [
                            ['text' => 'De Krant Lezen', 'url' => route('newspaper.index')],
                        ],
                    ],
                    [
                        'q' => 'Hoe luister ik naar TBS FM Radio en hoe stuur ik een shoutout met vlammetjes?',
                        'a' => "Onder 'Algemeen > TBS FM' luister je tijdens het spelen live naar muziekstreams! Je kunt via het invoerveld een openbare shoutout sturen naar alle luisterende patiënten. Andere spelers kunnen jouw shoutouts belonen met vlammetjes (likes).",
                        'links' => [
                            ['text' => 'Naar TBS FM', 'url' => route('radio.index')],
                        ],
                    ],
                    [
                        'q' => 'Hoe werkt de Tijdlijn (het interne sociale netwerk)?',
                        'a' => "De Tijdlijn ('/tijdlijn') is de sociale feed van de inrichting. Deel statusupdates, foto's en opschepperijen over je laatste overval, like berichten van anderen en reageer op medepatiënten.",
                        'links' => [
                            ['text' => 'Naar Tijdlijn', 'url' => route('timeline.index')],
                        ],
                    ],
                    [
                        'q' => 'Wat is het Vriendensysteem en hoe werkt het BFF-hartje (sneller elkaars celdeur forceren)?',
                        'a' => "Onder 'Algemeen > Vrienden' beheer je jouw vriendenlijst. Je kunt één speciale medespeler benoemen tot jouw officiële 'BFF' (Beste Vriend Voor Altijd). Je BFF krijgt een speciaal gouden hartje op zijn profiel en jullie kunnen elkaars celdeur extra snel openbreken als een van jullie in de isoleercel belandt!",
                        'links' => [
                            ['text' => 'Vriendenlijst', 'url' => route('friends.index')],
                        ],
                    ],
                    [
                        'q' => 'Hoe werkt de Live Chat en Zaalwacht Arjan (de interactieve hulpbot)?',
                        'a' => 'Rechtsonder in je scherm vind je de zwevende Live Chat. Hier kun je live praten met alle aanwezige patiënten. Ook kun je direct een vraag stellen aan Zaalwacht Arjan, de slimme AI-hulpbot die alles weet over spelsystemen, geld verdienen en regels!',
                    ],
                    [
                        'q' => 'Hoe sluit ik me aan bij de officiële TBS Kliniek Discord community?',
                        'a' => "Onze officiële Discord server is dé plek voor gezelligheid, kluiscodes, updates en bende-werving. Ga naar '/discord/verifieer' om lid te worden en claim direct je 3 dagen gratis VIP!",
                    ],
                    [
                        'q' => 'Wat is de Cinema zaal in de kliniek?',
                        'a' => "In de Cinema ('/cinema') kun je samen met andere patiënten in virtuele filmzalen video's bekijken en live meechatten op het grote scherm.",
                        'links' => [
                            ['text' => 'Naar Cinema', 'url' => route('cinema.index')],
                        ],
                    ],
                ],
            ],
            'account' => [
                'title' => '⚙️ Account, Regels & Beveiliging',
                'icon' => 'fa-gear',
                'questions' => [
                    [
                        'q' => 'Hoe pas ik mijn profiel aan (avatar, bio en 7 accentkleuren)?',
                        'a' => "Ga naar 'Mijn Profiel' in het menu. Klik op 'Profiel Bewerken'. Je kunt een eigen avatar uploaden, een criminele achtergrondbio schrijven en kiezen uit 7 accentkleuren (Paars, Blauw, Groen, Rood, Amber, Roze en Cyaan) die jouw dossier en kaarten sieren!",
                        'links' => [
                            ['text' => 'Mijn Profiel', 'url' => route('profile.me')],
                        ],
                    ],
                    [
                        'q' => 'Hoe wissel ik tussen Dark Mode en Light Mode?',
                        'a' => 'Rechtsboven in de navigatiebalk vind je een zon/maan schakelaar. Klik hierop om direct soepel te wisselen tussen een lichte dagmodus en een sfeervolle donkere nachtmodus.',
                    ],
                    [
                        'q' => 'Hoe wijzig ik mijn gebruikersnaam (1x per 6 maanden) of wachtwoord?',
                        'a' => "Ga naar 'Account Instellingen' ('/instellingen/account'). Je kunt je karakternaam 1 keer per 6 maanden aanpassen. Wachtwoorden kun je hier direct bijwerken of herstellen via de 'Wachtwoord Vergeten' link op het inlogscherm.",
                        'links' => [
                            ['text' => 'Account Instellingen', 'url' => route('settings.account')],
                        ],
                    ],
                    [
                        'q' => 'Wat doet \'Overal Uitloggen\' (Remote Sessions)?',
                        'a' => "Onder Account Instellingen zie je een overzicht van alle telefoons, tablets en computers waarop jouw account momenteel is ingelogd. Met 1 klik op 'Overal Uitloggen' beëindig je alle andere sessies op afstand voor maximale privacy en veiligheid.",
                    ],
                    [
                        'q' => 'Wat zijn de officiële kliniekregels omtrent accounts (max 1 account per persoon, max 2 per IP, verbod op VPN/proxies)?',
                        'a' => "Om het spel eerlijk en plezierig te houden gelden duidelijke regels:\n\n• 👤 1 Account per Persoon: Iedere speler mag maximaal 1 account bezitten. Dubbele accounts (multi-accounting) zijn ten strengste verboden en worden direct permanent verbannen;\n• 🏠 Maximaal 2 Accounts per IP-adres: Dit is uitsluitend bedoeld voor gezinsleden op hetzelfde thuisnetwerk. Heb je een groter gezin met 3 of meer spelers? Maak dan vooraf een support ticket aan voor een officiële huishouduitzondering;\n• 🚫 VPN's en Proxies Verboden: Het gebruik van VPN-verbindingen of proxies is niet toegestaan en wordt automatisch geblokkeerd tijdens registratie en login.",
                    ],
                    [
                        'q' => 'Hoe open ik een officieel Support Ticket bij de beheerders?',
                        'a' => "Heb je een vraag, technisch probleem, bug ontdekt of wil je een Gang Overstap Bonus aanvragen? Ga naar 'Ondersteuning > Nieuw Ticket Aanmaken'. Je melding komt direct binnen bij de beheerders en je ontvangt snel antwoord in je notificaties!",
                        'links' => [
                            ['text' => 'Nieuw Ticket Maken', 'url' => route('help.tickets.create')],
                        ],
                    ],
                ],
            ],
            'begrippenlijst' => [
                'title' => '📚 Woordenboek & Handige Begrippenlijst',
                'icon' => 'fa-book-atlas',
                'questions' => [
                    [
                        'q' => 'Wat betekenen veelgebruikte termen zoals HP, XP, Cooldown, Reset en Buff?',
                        'a' => "• HP (Health Points): Jouw levensbalk (max 100). Bij 0 ben je gewond en moet je naar Brenda in de Ziekenboeg;\n• XP (Experience Points): Ervaringspunten om in level te stijgen;\n• Cooldown: De wachttijd voordat je een actie opnieuw kunt uitvoeren;\n• Reset: De dagelijkse middernachtreset (00:00 uur) waarbij alle tellers, missies en battles opnieuw beginnen;\n• Buff / Perk: Een tijdelijke of permanente bonus op je vaardigheden (bijv. sneller energieherstel of lagere banktax);\n• Ricochet: Het terugkaatsen van een kogel op een schutter wanneer het doelwit een Backfire-vest draagt.",
                    ],
                    [
                        'q' => 'Wat is het verschil tussen een Gewone Aanval, een Liquidatie en een Heist?',
                        'a' => "• GEWONE AANVAL (Respectlijst): Een vuistgevecht op de Luchtplaats. Je steelt respect en tot 15% contant geld. Geen kogels nodig;\n• LIQUIDATIE 2.0: Een dodelijk vuurgevecht met scherpe kogels op dezelfde afdeling. Rooft 45% contant én 7% van de bankrekening;\n• HEIST: Een gecoördineerde overval op een extern doelwit (juwelier, casino, bank) met behulp van een vluchtvoertuig en maskers.",
                    ],
                    [
                        'q' => 'Wat is het verschil tussen Contant Geld, Bankgeld, Bende-Kluisgeld en Vermogen?',
                        'a' => "• Contant Geld: Geld in je broekzak. Kwetsbaar voor diefstal;\n• Bankgeld: Geld op je bankrekening. Veilig voor gewone aanvallen en zakkenrollers;\n• Bende-Kluisgeld: Gezamenlijk kapitaal van jouw gang om bende-vastgoed en kiosken aan te schaffen;\n• Vermogen: De optelsom van al jouw contant geld en bankgeld bij elkaar.",
                    ],
                    [
                        'q' => 'Wat betekenen de statussen: Actief, In Isoleercel, In Coma (Code Blauw) en Vrijwillige Isolatie?',
                        'a' => "• ACTIEF (Groen): Je loopt vrij rond en kunt alle acties, misdaden en gevechten uitvoeren;\n• IN ISOLEERCEL (Oranje): Je zit een straftijd uit na een mislukte misdaad. Uitbreken via luchtschacht, zaagje of cipier omkopen;\n• IN COMA / CODE BLAUW (Rood): Je bent 10x geliquideerd. Reanimeren met serum (€1,5M of 75 Sterren);\n• VRIJWILLIGE ISOLATIE (Blauw): Je hebt jezelf vrijwillig in de bunker opgesloten. Niemand kan jou aanvallen!",
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
                'title' => '🗺️ Beginnersgids: Stappenplan van Start tot Einde',
                'keywords' => ['beginnen', 'nieuw', 'start', 'begin', 'starten', 'uitleg', 'hoe werkt', 'eerste stappen', 'wat moet ik doen', 'tips', 'beginner', 'overleven', 'stappenplan', 'start tot einde'],
                'answer' => "Welkom in de TBS Kliniek! Hier is het beproefde stappenplan voor beginners:\n1. Ga naar de Ziekenboeg om je HP gratis naar 100% te brengen.\n2. Draai 1x gratis aan het Rad van Fortuin voor een startersbonus.\n3. BELANGRIJKSTE REGEL: Stort AL je contant geld meteen op De Bank om beroving te voorkomen!\n4. Werk veilig in de Dagbesteding bij Werkmeester Henk (100% legaal, 0% pakkans!).\n5. Voltooi je eerste Missies voor snel geld en XP.\n6. Train Kracht en Verdediging in de Fitnessruimte.",
                'links' => [
                    ['text' => 'Naar Missies', 'url' => route('missions.index')],
                    ['text' => 'Naar de Bank', 'url' => route('bank.index')],
                    ['text' => 'Naar de Ziekenboeg', 'url' => route('infirmary.index')],
                ],
            ],
            [
                'title' => '⚡ Energie & Herstel',
                'keywords' => ['energie', 'energy', 'herstellen', 'op', 'leeg', 'weinig energie', 'kracht', 'snack', 'eten', 'chocola'],
                'answer' => "Je hebt Energie nodig voor misdrijven, vechten en training. Jouw energie herstelt automatisch: 1 punt per 5 minuten (elke 3 minuten voor VIP / Tier 1). Direct weer energie nodig?\n• Eet kantinesnacks uit je inventaris;\n• Draai aan het Rad van Fortuin;\n• Koop een energie-refill in de Sterren Shop of met 15 VIP Punten in de VIP Shop.",
                'links' => [
                    ['text' => 'Kantine & Snacks', 'url' => route('shop.index', ['tab' => 'inventaris'])],
                    ['text' => 'VIP Shop', 'url' => route('vip.index')],
                ],
            ],
            [
                'title' => '❤️ Gezondheid (HP) & Ziekenboeg',
                'keywords' => ['gezondheid', 'health', 'hp', 'dood', 'gewond', 'genezen', 'healen', 'ziekenboeg', 'dokter', 'zuster', 'brenda', 'verband', 'ehbo', 'pillen'],
                'answer' => 'Is je Gezondheid laag door een verloren gevecht? Ga direct naar de Ziekenboeg! Zuster Brenda lapt je daar direct 100% GRATIS op. Ook kun je in de Kliniek Winkel verbanddozen en EHBO-kits kopen om direct ter plekke te genezen.',
                'links' => [
                    ['text' => 'Naar de Ziekenboeg', 'url' => route('infirmary.index')],
                    ['text' => 'Kliniek Winkel', 'url' => route('shop.index')],
                ],
            ],
            [
                'title' => '🏦 De Bank & Veilig Sparen',
                'keywords' => ['bank', 'geld', 'storten', 'sparen', 'veilig', 'rente', 'beroofd', 'gestolen', 'kwijt', 'contant', 'cash'],
                'answer' => 'Houd NOOIT onnodig contant geld op zak! Andere spelers kunnen tot 15% van je contante geld stelen via de Respectlijst of via zakkenrollen. Geld op je bankrekening is 100% veilig beschermd tegen Respectlijst-aanvallen en zakkenrollers. Spelers zonder bende ontvangen bovendien elke 15 minuten €1.000 zakgeld op de bank!',
                'links' => [
                    ['text' => 'Naar De Bank', 'url' => route('bank.index')],
                ],
            ],
            [
                'title' => '⛓️ Isoleercel & Ontsnappen',
                'keywords' => ['isoleercel', 'gevangenis', 'cel', 'vast', 'straf', 'straftijd', 'gepakt', 'betrapt', 'vrijkopen', 'ontsnappen', 'deur forceren'],
                'answer' => "Als een misdrijf mislukt beland je in de isoleercel. Je komt sneller vrij door:\n• Een ijzerzaagje uit je inventaris te gebruiken (-30 min straftijd);\n• Een gratis video te kijken in de Sponsorkamer;\n• Jezelf vrij te kopen met 25 Sterren via de Vrijkoopakte;\n• Een vriend te vragen je celdeur open te breken;\n• Een luchtschacht-uitbraak te wagen (kost 20 energie).",
                'links' => [
                    ['text' => 'Naar Isoleercel', 'url' => route('prison')],
                    ['text' => 'Sponsorkamer', 'url' => route('sponsor.index')],
                ],
            ],
            [
                'title' => '🥊 Respectlijst & Luchtplaats Vechten',
                'keywords' => ['respectlijst', 'vechten', 'luchtplaats', 'slaylist', 'slay', 'aanvallen', 'duel', 'arena', 'showdown', '100 aanvallen', 'respect'],
                'answer' => 'Op de Respectlijst daag je andere patiënten uit op de luchtplaats. Win je het 5-seconden duel, dan steel je respect en tot 15% contant geld! Standaard mag je maximaal 100 aanvallen per dag doen (VIP-leden tot 1.000 per dag). Reset vindt plaats om 00:00 uur.',
                'links' => [
                    ['text' => 'Naar Respectlijst', 'url' => route('fight.index')],
                ],
            ],
            [
                'title' => '🛡️ Vrijwillige Isolatie (Bunkerbescherming)',
                'keywords' => ['vrijwillige isolatie', 'bescherming', 'bunker', 'veiligheid', 'aangevallen', 'te veel aanvallen', 'stop aanvallen', 'shield'],
                'answer' => "Word je constant aangevallen door sterkere spelers? Activeer Vrijwillige Isolatie onder 'Geweld & Strijd'. Zolang deze bunkerbescherming actief is, kan niemand op de hele Respectlijst jou aanvallen!",
                'links' => [
                    ['text' => 'Vrijwillige Isolatie', 'url' => route('isolation.index')],
                ],
            ],
            [
                'title' => '🔨 Veilige Dagbesteding & Werkplaats',
                'keywords' => ['dagbesteding', 'werkplaats', 'werken', 'veilig werk', 'geen isoleercel', 'henk', 'loon', 'salaris', 'houtbewerking', 'wasserette', 'discipline'],
                'answer' => 'In de Werkplaats met Werkmeester Henk werk je 100% veilig en legaal! Er is GEEN ENKEL RISICO op de isoleercel. Je verdient gegarandeerd eerlijk dagloon, bouwt Discipline op en hebt kans om geheime kluiscodes te vinden.',
                'links' => [
                    ['text' => 'Naar Dagbesteding', 'url' => route('daycare.index')],
                ],
            ],
            [
                'title' => '🧠 Behandelaarsgesprek & 3 Uur Verlof',
                'keywords' => ['behandelaar', 'behandelaarsgesprek', 'gesprek', 'verlof', 'psychiater', 'sedatie', 'buiten', 'supermarkt', 'station', 'dilemma'],
                'answer' => 'Elke nacht om 00:00 uur staat er een psychologisch dilemma klaar bij je specialist. Scoor je minstens 60%? Dan word je beloond met 3 UUR BEGELEID VERLOF buiten de kliniek met speciale buiten-missies bij de supermarkt en het station voor mega buit!',
                'links' => [
                    ['text' => 'Behandelaarsgesprek', 'url' => route('treatment.index')],
                ],
            ],
            [
                'title' => '🕵️ Illegale Acties: Hosselen, Smokkel & Diefstal',
                'keywords' => ['hosselen', 'smokkelen', 'dealen', 'wiet', 'drugs', 'diefstal', 'klusjes', 'afpersen', 'misdaad', 'misdrijf'],
                'answer' => "• Hosselen - Korte deals op de gang (shag rollen, telefoons) voor snelle buit.\n• Smokkelen - Kweek wiet of brouw chemicaliën in je cel en verkoop de voorraad met flinke winst.\n• Afpersen - Zet doelwitten onder druk voor wekelijks zwijggeld.\n• Grote Heists - Risicovolle overvallen met vluchtvoertuig en maskers.",
                'links' => [
                    ['text' => 'Hosselen', 'url' => route('hustle.index')],
                    ['text' => 'Smokkelen & Dealen', 'url' => route('smuggle.index')],
                    ['text' => 'Klusjes & Diefstal', 'url' => route('theft.index')],
                ],
            ],
            [
                'title' => '🔐 MedicijnKluis & Geheime Codes',
                'keywords' => ['kluis', 'kluis kraak', 'code', 'raden', 'medicijnkluis', 'jammer', 'alarm jammer', 'kluiscode', 'voucher'],
                'answer' => 'De centrale Medicijnkluis bevat een geheime 4-cijferige code. Bij elke gok krijg je aanwijzingen (hoger of lager). Koop vooraf een Alarm Jammer zodat het alarm niet afgaat bij een foute gok! Ook kun je hier geheime vouchers en kluiscodes verzilveren.',
                'links' => [
                    ['text' => 'Kluis Kraken', 'url' => route('vault.index')],
                ],
            ],
            [
                'title' => '⛏️ Uitbraaktunnel Graven',
                'keywords' => ['tunnel', 'graven', 'uitbraaktunnel', 'schep', 'ontsnappingstunnel', 'zand dumpen', 'vrijheid', '100 meter'],
                'answer' => 'Koop een schep en graaf elke dag in het geheim een tunnel onder je celvloer. Dump het zand ongezien op de binnenplaats. Zodra je tunnel 100 meter bereikt ontgrendel je de legendarische Grote Uitbraak met royale cash en 25 Sterren ⭐!',
                'links' => [
                    ['text' => 'Naar Uitbraaktunnel', 'url' => route('tunnel.index')],
                ],
            ],
            [
                'title' => '🥷 Zakkenrollen & Maskers',
                'keywords' => ['zakkenrollen', 'zakkenroller', 'masker', 'maskers', 'bivakmuts', 'stelen', 'pakkans'],
                'answer' => 'Besluip bewakers of patiënten om geld en maskers te rollen. Het bezitten van een masker halveert jouw pakkans bij alle toekomstige misdrijven en heists!',
                'links' => [
                    ['text' => 'Zakkenrollen', 'url' => route('pickpocket.index')],
                ],
            ],
            [
                'title' => '🎰 Rad van Fortuin & Streaks',
                'keywords' => ['rad', 'rad van fortuin', 'spin', 'draaien', 'gratis', 'streak', 'dagelijks', 'inloggen'],
                'answer' => 'Draai elke 24 uur gratis aan het Rad van Fortuin voor contant geld, sterren, energie of koffers. Door elke dag achter elkaar in te loggen bouw je een dagelijkse streak op met steeds grotere jackpotuitkeringen!',
                'links' => [
                    ['text' => 'Rad van Fortuin', 'url' => route('daily.index')],
                ],
            ],
            [
                'title' => '👥 Bendes, Uurloon & Overstap Bonus',
                'keywords' => ['gang', 'bende', 'bendes', 'uurloon', 'gang kluis', 'bende kluis', 'gang wars', 'oorlog', 'bendeleider', 'oprichten', 'overstap', 'gang overstap'],
                'answer' => 'Vanaf Level 3 kun je op de Bendes pagina een eigen bende oprichten of je aansluiten. Bendes bezitten panden en kiosken die elk uur automatisch bende-uurloon genereren voor alle leden! Kom je van een ander misdaadspel? Vraag dan de Gang Overstap Bonus aan voor gratis bende, €5.000.000 bende-geld en 31 dagen gratis VIP!',
                'links' => [
                    ['text' => 'Naar Bendes', 'url' => route('gangs.index')],
                    ['text' => 'Overstap Ticket Aanmaken', 'url' => route('help.tickets.create')],
                ],
            ],
            [
                'title' => '🗺️ Verplaatsen (Reizen) & Paviljoens',
                'keywords' => ['reizen', 'verplaatsen', 'paviljoen', 'locatie', 'kookhuis', 'luchtplaats', 'omkopen', 'bewaker omkopen', '35 xp'],
                'answer' => "Onder 'Economie > Verplaatsen' koop je bewakers om naar andere afdelingen zoals Paviljoen 1, Paviljoen 2, Het Kookhuis of De Recreatiezaal. Elke succesvolle reis levert je direct +35 XP op! VIPs krijgen 50% korting.",
                'links' => [
                    ['text' => 'Verplaatsen', 'url' => route('travel.index')],
                ],
            ],
            [
                'title' => '👑 VIP Lidmaatschap (100% Gratis via Sponsorkamer & Discord)',
                'keywords' => ['vip', 'gratis', 'ads', 'advertenties', 'sponsorkamer', 'vip punten', 'gouden naam', 'voordelen', 'kosten', 'prijs'],
                'answer' => "VIP is in TBS Kliniek 100% GRATIS! Bekijk advertenties in de Sponsorkamer (24 uur gratis VIP per 250 ads) of koppel Discord (3 dagen gratis VIP). Als VIP ontvang je:\n• 5 VIP Punten per speeluur;\n• Sneller energieherstel (1 punt per 3 min i.p.v. 5 min);\n• 2x Dagelijks draaien aan het Rad van Fortuin;\n• 1.000 gevechten per dag i.p.v. 100;\n• 50% Korting op reiskosten;\n• 3% Banktax i.p.v. 5% bij opnemen.",
                'links' => [
                    ['text' => 'VIP Shop & Status', 'url' => route('vip.index')],
                    ['text' => 'Sponsorkamer', 'url' => route('sponsor.index')],
                ],
            ],
            [
                'title' => '☕ Donaties, VIP & Serversteun',
                'keywords' => ['donatie', 'doneren', 'buymeacoffee', 'koffie', 'kopen', 'aankoop', 'betalen', 'vip kopen', 'sterren kopen', 'echt geld'],
                'answer' => 'TBS Kliniek is een non-profit hobbyproject. Alle dingen zoals supporterpakketten via Buy Me a Coffee vallen onder een vrijwillige donatie ter dekking van lopende hostingkosten. Als dank ontvang je automatisch bijbehorende spelprivileges.',
                'links' => [
                    ['text' => 'VIP & Donaties', 'url' => route('vip.index')],
                ],
            ],
            [
                'title' => '📺 Sponsorkamer & 24 UUR GRATIS VIP',
                'keywords' => ['sponsorkamer', 'sponsor', 'ad', 'advertentie', 'video', 'reclame', 'gratis vip', 'smartlink', 'beloning', 'mijlpaal'],
                'answer' => 'In de Sponsorkamer kun je korte 15s videoadvertenties bekijken voor €2.500 zakgeld, maskers, kluismunten of strafvermindering in de isoleercel. Na elke 250 bekeken advertenties ontgrendel je automatisch 24 UUR GRATIS VIP!',
                'links' => [
                    ['text' => 'Naar Sponsorkamer', 'url' => route('sponsor.index')],
                ],
            ],
            [
                'title' => '🎰 Casino & Gokhal',
                'keywords' => ['casino', 'gokhal', 'gokken', 'roulette', 'hoger lager', 'nummerspel', 'slots', 'fruitautomaat', 'blackjack'],
                'answer' => 'In het Casino speel je Roulette, Hoger/Lager, Nummerspel, Steen-Papier-Schaar en de klassieke Fruitautomaat/Slots. Speel verstandig en zet nooit al je geld in één keer in!',
                'links' => [
                    ['text' => 'Naar het Casino', 'url' => route('casino.index')],
                ],
            ],
            [
                'title' => '🎟️ Afdelingsloterij (Jackpot)',
                'keywords' => ['loterij', 'lotto', 'loten', 'jackpot', 'afdelingsloterij', 'winnen', 'pot'],
                'answer' => 'De kliniekloterij heeft een vaste trekkingscyclus van 14 dagen. Koop loten voor €10.000 (of met 50% korting in het laatste kwartier). Bankopnamebelasting vult de Jackpot. De winnaar ontvangt 30% op de bank en 70% contant!',
                'links' => [
                    ['text' => 'Naar Loterij', 'url' => route('lottery.index')],
                ],
            ],
            [
                'title' => '📻 TBS FM Radio & Krant',
                'keywords' => ['radio', 'tbs fm', 'muziek', 'shoutout', 'krant', 'afdelingsblad', 'kliniekbode', 'nieuws'],
                'answer' => 'Luister live naar TBS FM Radio terwijl je speelt en stuur een publieke shoutout naar alle patiënten! In Het Afdelingsblad lees je de dagelijkse krant met recente overvallen en arrestaties.',
                'links' => [
                    ['text' => 'TBS FM Radio', 'url' => route('radio.index')],
                    ['text' => 'Het Afdelingsblad', 'url' => route('newspaper.index')],
                ],
            ],
            [
                'title' => '⚙️ Account, Wachtwoord & Beveiliging',
                'keywords' => ['account', 'wachtwoord', 'vergeten', 'reset', 'gebruikersnaam', 'naam wijzigen', 'kleur', 'dark mode', '2fa', 'uitloggen', 'sessies', 'regels', 'vpn', 'multi'],
                'answer' => "Onder Account Instellingen (/instellingen/account) kun je je gebruikersnaam 1x per 6 maanden aanpassen, je wachtwoord wijzigen en alle andere apparaten op afstand uitloggen. Iedere speler mag maximaal 1 account hebben; VPN's zijn verboden.",
                'links' => [
                    ['text' => 'Account Instellingen', 'url' => route('settings.account')],
                    ['text' => 'Mijn Profiel', 'url' => route('profile.me')],
                ],
            ],
            [
                'title' => '🎫 Support Tickets & Hulp van Beheer',
                'keywords' => ['ticket', 'support', 'help', 'hulp', 'beheer', 'moderator', 'admin', 'melden', 'vraag', 'probleem', 'contact'],
                'answer' => "Heb je een vraag, bug of probleem dat niet in de FAQ staat? Open direct een ticket onder 'Support Tickets' of 'Nieuw Ticket Aanmaken'. Het beheerdersteam helpt je snel verder!",
                'links' => [
                    ['text' => 'Nieuw Ticket Maken', 'url' => route('help.tickets.create')],
                    ['text' => 'Mijn Tickets', 'url' => route('help.tickets.index')],
                ],
            ],
            [
                'title' => '🏎️ Smokkel Garage & Voertuigen',
                'keywords' => ['garage', 'voertuig', 'auto', 'wagen', 'vluchtwagen', 'scooter', 'motor', 'bmw', 'gti', 'vliegtuig', 'helikopter', 'speedboot', 'repareren', 'conditie', 'snelheid', 'wagenpark'],
                'answer' => 'In de Smokkel Garage koop je snelle vluchtvoertuigen zoals een Zip Scooter, Golf GTI, BMW M5, Speedboot of Helikopter. Elk voertuig heeft topsnelheid en conditie. Repareer je wagen na heists in de garage om maximale snelheid en slaagkansen te behouden.',
                'links' => [
                    ['text' => 'Naar Smokkel Garage', 'url' => route('garage.index')],
                    ['text' => 'Naar Grote Heists', 'url' => route('heists.index')],
                ],
            ],
            [
                'title' => '🎭 Grote Heists & Overvallen',
                'keywords' => ['heist', 'heists', 'overval', 'kraak', 'bank', 'juwelier', 'casino', 'vlucht', 'vluchtwagen', 'ramkraak', 'goudkluis', 'container', 'buit', 'heistlog'],
                'answer' => 'Grote Heists zijn zware overvallen met enorme buit! Je hebt een passend vluchtvoertuig in goede conditie en maskers (bivakmutsen) nodig. Geslaagde heists leveren flinke bedragen en kluiscodes op. Bij mislukking beland je in de isoleercel!',
                'links' => [
                    ['text' => 'Naar Grote Heists', 'url' => route('heists.index')],
                    ['text' => 'Smokkel Garage', 'url' => route('garage.index')],
                ],
            ],
            [
                'title' => '🎯 Liquidatie 2.0 & Schaduwing',
                'keywords' => ['liquidatie', 'kogels', 'bullets', 'schieten', 'hitman', 'wapenhandelaar', 'coma', 'code blauw', 'vest', 'backfire', 'ricochet'],
                'answer' => "Onder 'Geweld & Strijd > Liquidatie' spoor je rivalen op met schaduw-inlichtingen en liquideer je ze met kogels op dezelfde afdeling! Bij treffers 1-9 evacueert het slachtoffer met 20 HP; bij 10/10 treedt Code Blauw in. Draag een Backfire-vest om inkomende schoten terug te kaatsen op de schutter!",
                'links' => [
                    ['text' => 'Naar Liquidatie', 'url' => route('hitman.index')],
                ],
            ],
            [
                'title' => '🏆 Doel van het Spel & 24u Battle',
                'keywords' => ['doel', 'wat is het doel', 'waar speel je voor', 'battle', '24 uur battle', 'overwinning', 'jackpot'],
                'answer' => 'In TBS Kliniek strijd je om de heerschappij! Doe dagelijks mee aan de 24-Uurs Kliniek Battle in 5 klassementen voor gratis Sterren, heers over de Respectlijst en bouw met je bende een machtig imperium van panden en controlepunten op!',
                'links' => [
                    ['text' => '24-Uurs Battle', 'url' => route('battle.index')],
                    ['text' => 'Mijn Bende', 'url' => route('gangs.index')],
                ],
            ],
            [
                'title' => '📦 Koffers Onderscheppen & XXL Koffers',
                'keywords' => ['koffer', 'koffers', 'kraken', 'xxl koffer', 'onderscheppen', 'breekijzer', 'lockpick', 'buitkrat', 'buit'],
                'answer' => "Onder 'Voortgang > Koffers' onderschep je smokkelkoffers op het laadperron. Kraak ze met breekijzers of lockpicks. Zeldzame XXL Koffers bevatten enorme cash bedragen tot €250.000, gratis Sterren ⭐, scherpe kogels en unieke stickers!",
                'links' => [
                    ['text' => 'Koffers Onderscheppen', 'url' => route('packages.index')],
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
        $search = strtolower(trim((string) $request->input('search', '')));

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

        $totalQuestions = 0;
        foreach (self::getFaqData() as $cat) {
            $totalQuestions += count($cat['questions'] ?? []);
        }

        return view('game.faq', compact('categories', 'search', 'totalQuestions'));
    }
}
