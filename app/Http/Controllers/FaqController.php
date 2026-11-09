<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class FaqController extends Controller
{
    /**
     * Complete FAQ knowledge base categories and questions covering every system in TBS Kliniek.
     * Written in ultra-clear, beginner-friendly language from start to pro endgame.
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
                        'a' => "💡 IN HET KORT: Je begint onderaan als nieuwe patiënt en werkt jezelf in 6 simpele stappen op tot de rijkste en sterkste baas van de kliniek.\n\n📋 HET COMPLETE STAPPENPLAN (VAN BEGINNER TOT PRO):\n\n1. 🧭 STAP 1: Je Eerste 10 Minuten (Veilig Starten)\n• Ga naar de Ziekenboeg en laat Zuster Ingrid je gratis verzorgen (+45 HP per behandeling).\n• Draai 1x gratis aan het Rad van Fortuin voor gratis geld, energie of sterren.\n• Zet AL je contante geld direct op De Bank! Geld in je broekzak kan namelijk gestolen worden.\n• Voltooi de 5 Rookie Taken op je Dashboard voor in totaal €6.500 cash, 30 kogels, 145 XP en 1 dag gratis VIP!\n\n2. 🔨 STAP 2: Veilig Geld & Ervaring (XP) Verdienen (Level 1 t/m 3)\n• Werk bij Werkmeester Henk in de Dagbesteding. Dit is 100% veilig: je kunt hier NOOIT bij gepakt worden en belandt nooit in de isoleercel.\n• Doe je eerste opdrachten bij 'Missies' voor makkelijk startgeld.\n• Doe elke 2 uur het Behandelaarsgesprek (3 vragen bij Dr. Vink) voor €2.500 cash en 3 uur Begeleid Verlof buiten de kliniek.\n\n3. 💪 STAP 3: Sterker Worden in de Gym (Level 3 t/m 6)\n• Gebruik je energie in de Fitnessruimte om Kracht (harder slaan), Verdediging (minder schade krijgen), Conditie, Intelligentie, Charisma en Discipline te trainen.\n• Koop een goedkoop wapen en een vest in de Kliniek Winkel (deze werken direct automatisch na aankoop!).\n\n4. 🥷 STAP 4: Eerste Misdaden & Maskers Scoren (Level 6 t/m 8)\n• Doe snelle klusjes bij 'Hosselen' (van €400 tot €65.000) voor vlotte winst.\n• Koop of win een Bivakmuts (Masker) om te kunnen 'Zakkenrollen' en grote klussen te doen.\n• Onderschep smokkelkoffers op het perron en breek ze open.\n\n5. 🥊 STAP 5: Vechten op de Luchtplaats & Reizen (Level 8 t/m 12)\n• Val zwakkere spelers aan op de Luchtplaats (Respectlijst) om hun respect en 10% tot 25% van hun contante zakgeld te stelen.\n• Reis tussen de 8 afdelingen via 'Verplaatsen'. Elke reis geeft je meteen +35 XP!\n\n6. 👑 STAP 6: Pro Endgame - Bendes, Vastgoed Imperium, Garage Vloot & Heists (Level 12+)\n• Richt een eigen Bende op of sluit je aan bij een bestaande groep voor het vernieuwde Bende-Uurloon (kiosken tot 1.000, passieve opbrengsten per gestald type voertuig in het wagenpark, vaste pandbijdragen, respect- en heerschappijbonussen!).\n• Beheer het gezamenlijke wagenpark in de Bende Garage: revisies worden 100% vergoed uit de bendekas en leden kunnen wagens permanent doneren voor +50 Respect.\n• Schaf Bende Panden aan tot €1,5 miljard, voldoe aan de garage-vlooteisen en start lucratieve productiecycli.\n• Strijd mee in 24-uurs bendeoorlogen om kliniekzones voor 7 dagen +25% heerschappij-bonus en profiteer van Bende Wraakrecht zonder level-limieten.\n• Pleeg grootschalige Bende Co-Op Heists met 50/50 buitverdeling en win elke nacht Sterren ⭐ in de 24 Uur Battle!\n\n🔥 PRO-TIP: Koppel op dag 1 meteen je Discord (+3 dagen gratis VIP & €2.500) en zet Push-meldingen aan (+€25.000 contant, +25 respect & +20 energie)!",
                        'links' => [
                            ['text' => 'Naar Dashboard (Rookie Taken)', 'url' => route('dashboard')],
                            ['text' => 'Naar Bendes', 'url' => route('gangs.index')],
                            ['text' => 'Naar Bende Garage', 'url' => route('gangs.garage')],
                            ['text' => 'Naar Bende Panden', 'url' => route('gangs.panden')],
                        ],
                    ],
                    [
                        'q' => 'Wat is het doel van TBS Kliniek?',
                        'a' => "💡 IN HET KORT: Het doel is om van een arme nieuwe patiënt uit te groeien tot de machtigste en rijkste crimineel van de hele inrichting.\n\n📋 WAAR STRIJD JE VOOR?\n• 🥊 Nummer 1 op de Ranglijst: Verzamel het meeste Respect van alle spelers;\n• 👥 De Machtigste Bende: Bouw met vrienden een eigen bende, koop syndicaatpanden tot €1,5 miljard, beheer een vloot in de Bende Garage en verdien elk uur passief bende-uurloon;\n• ⚔️ 24-Uurs Bendeoorlogen: Verover strategische kliniekzones (Kantine, Luchtplaats, Apotheek) voor 7 dagen lang +25% heerschappij-bonus over het hele bende-uurloon;\n• 🏆 De 24 Uur Battle Winnen: Eindig elke dag in de top 3 van de 7 dagelijkse categorieën en win gratis Gouden Sterren ⭐, Kluiscodes, XXL Koffers en Bivakmutsen;\n• ⛏️ De Grote Uitbraak: Graaf stiekem met een eetlepel een tunnel van 0% naar 100% onder je cel en ontsnap;\n• 🔐 De Kluis Kraken: Raad de code van de Centrale Medicijnkluis of wissel geheime kluiscodes in!\n\n🔥 PRO-TIP: Je bepaalt zelf je speelstijl: je kunt 100% vreedzaam rijk worden als handelaar en werker, of juist als gevreesde vechtersbaas en bendeleider heersen.",
                        'links' => [
                            ['text' => 'Ranglijst Bekijken', 'url' => route('leaderboard.index')],
                            ['text' => '24 Uur Battle', 'url' => route('battle.index')],
                        ],
                    ],
                    [
                        'q' => 'Hoe pak ik op Dag 1 meteen meer dan €35.000 gratis startgeld en 4 dagen Gratis VIP?',
                        'a' => "💡 IN HET KORT: Door 4 simpele startersbonussen te claimen heb je binnen 5 minuten al meer dan €35.000 startkapitaal op zak.\n\n📋 ZO PAK JE ALLE GRATIS STARTERSBONUSSEN:\n1. 🧭 Intake Rondleiding (€1.000): Klik bovenaan deze pagina op 'Start Rondleiding Nu' en doorloop de 5 korte uitlegstappen.\n2. 🎯 De 5 Rookie Taken op je Dashboard (€6.500 totaal + 1 Dag VIP + 30 Kogels): Op je Dashboard staan 5 makkelijke beginnersopdrachten (€1.500 uit de losse taken + €5.000 eindprijs, 145 XP, 5 sigaretten, +1 Kracht/Verdediging én de titel 'Kliniek Overlever'!).\n3. 🔔 Push-Meldingen Aanzetten (€25.000 + 25 Respect + 20 Energie): Klik op de meldingen-knop om meldingen toe te staan en ontvang direct €25.000 cash!\n4. 🎮 Discord Koppelen (€2.500 + 3 Dagen VIP): Koppel eenmalig je Discord-account via het menu en ontvang direct €2.500 én 3 dagen gratis VIP.\n\n🔥 PRO-TIP: Stort al dit gratis geld meteen op De Bank voordat je gaat vechten!",
                        'links' => [
                            ['text' => 'Naar Dashboard', 'url' => route('dashboard')],
                            ['text' => 'Discord Koppelen', 'url' => route('discord.verify')],
                            ['text' => 'Naar De Bank', 'url' => route('bank.index')],
                        ],
                    ],
                    [
                        'q' => 'Ik ben zojuist binnengebracht! Wat moet ik nu als allereerste aanklikken?',
                        'a' => "💡 IN HET KORT: Genees jezelf gratis, draai aan het gratis geluksrad, doe veilig werk en zet al je geld op de bank.\n\n📋 VOLG DEZE 5 SIMPELE KLIKS:\n1. 🏥 Klik op 'Voortgang > Ziekenboeg': Laat Zuster Ingrid je gratis verzorgen (+45 HP per beurt).\n2. 🎰 Klik op 'Bonus & Kluizen > Rad van Fortuin': Draai 1x gratis voor je eerste cadeau.\n3. 🔨 Klik op 'Voortgang > Dagbesteding': Werk een beurt bij Werkmeester Henk. Dit levert altijd geld op en kan niet mislukken.\n4. 📜 Klik op 'Voortgang > Missies': Claim je eerste voltooide missie.\n5. 🏦 Klik op 'Economie > De Bank': Klik op 'Alles Storten' zodat niemand je geld kan stelen!",
                        'links' => [
                            ['text' => '1. Ziekenboeg', 'url' => route('infirmary.index')],
                            ['text' => '2. Rad van Fortuin', 'url' => route('daily.index')],
                            ['text' => '3. De Bank', 'url' => route('bank.index')],
                        ],
                    ],
                    [
                        'q' => 'Wat betekenen alle balken en icoontjes bovenaan mijn scherm (Energie, HP, Geld, Sterren, Respect, XP)?',
                        'a' => "💡 IN HET KORT: Bovenaan zie je jouw levensbalk, je uithoudingsvermogen (energie), je portemonnee en je niveau.\n\n📋 SIMPELE UITLEG PER ICOONTJE:\n• ⚡ ENERGIE (Bliksem): Heb je nodig voor bijna elke actie. Vult elke 5 minuten vanzelf weer met 1 punt aan (elke 3 min voor VIP).\n• ❤️ GEZONDHEID / HP (Hartje): Jouw levensbalk (maximaal 100). Als deze laag is, moet je even langs Zuster Ingrid in de Ziekenboeg.\n• 💵 CONTANT GELD: Geld dat los in je broekzak zit. Pas op: andere spelers kunnen dit stelen!\n• 🏦 BANKGELD: Geld dat veilig op je bankrekening staat. Niemand kan dit stelen via de Respectlijst of zakkenrollen.\n• 💰 TOTAAL VERMOGEN: Jouw contante geld en bankgeld bij elkaar opgeteld.\n• ⭐ STERREN: Gouden sterren waarmee je jezelf direct uit de isoleercel koopt of wachttijden overslaat.\n• 🥊 RESPECT: Jouw aanzien in de kliniek. Hoe meer respect, hoe hoger je op de ranglijst staat.\n• 📊 LEVEL & XP: Elke actie geeft Ervaringspunten (XP). Is je XP-balk vol? Dan ga je een Level omhoog en speel je nieuwe onderdelen vrij!",
                    ],
                    [
                        'q' => 'Wat is het verschil tussen Contant Geld en Bankgeld, en waarom moet ik contant geld ALTIJD direct storten?',
                        'a' => "💡 IN HET KORT: Geld in je broekzak (contant) kan gestolen worden; geld op De Bank staat achter slot en grendel!\n\n📋 ZO ZIT HET:\n• 💵 Contant Geld op zak: Als iemand jou aanvalt op de Luchtplaats en wint, steelt diegene 10% tot 25% van jouw contante geld. Ook zakkenrollers kunnen uit je broekzak stelen.\n• 🏦 Geld op De Bank: Is 100% veilig voor Luchtplaats-aanvallen en zakkenrollers. Storten is altijd gratis en onbeperkt (tot maar liefst 1 Biljoen Euro dankzij onze 64-bit modernisering)!\n• 🎁 Gratis Zakgeld: Zit je nog niet in een bende? Dan stort de kliniek elke 15 minuten automatisch €1.000 gratis zakgeld op je bankrekening (tot wel €25.000 per kwartier voor VIP supporters)!\n\n🔥 PRO-TIP: Bij een liquidatie wordt er 0% van je bankrekening gestolen: je bankgeld en kluis zijn altijd 100% veilig! Aanvallers maken uitsluitend buit op contant zakgeld (maximaal 45%, verlaagbaar tot 22,5% via de Verborgen Vloerkluis celupgrade).",
                        'links' => [
                            ['text' => 'Direct naar De Bank', 'url' => route('bank.index')],
                        ],
                    ],
                    [
                        'q' => 'Wat gebeurt er als mijn levensbalk (HP) op 0 komt? Ben ik dan mijn account kwijt?',
                        'a' => "💡 IN HET KORT: Nee, je raakt je account of spullen NOOIT kwijt! Je moet alleen even gratis genezen in de Ziekenboeg.\n\n📋 WAT GEBEURT ER BIJ 0 HP?\n• Als je een vuistgevecht op de Luchtplaats verliest en op 0 HP komt, ben je tijdelijk gewond (geen coma!).\n• Ga simpelweg naar 'Voortgang > Ziekenboeg' en klik op 'Laat je behandelen'.\n• Zuster Ingrid geeft je direct en 100% gratis +45 HP per behandeling (of eet een snack/verbanddoos), waarna je meteen verder kunt spelen!",
                        'links' => [
                            ['text' => 'Gratis Genezen in Ziekenboeg', 'url' => route('infirmary.index')],
                        ],
                    ],
                    [
                        'q' => 'Wat zijn Sterren ⭐ en hoe verdien ik ze 100% gratis?',
                        'a' => "💡 IN HET KORT: Sterren ⭐ zijn speciale gouden munten voor handige extra's, en je kunt ze gewoon gratis in het spel winnen.\n\n📋 7 MANIEREN OM GRATIS STERREN TE VERDIENEN:\n1. 🎰 Rad van Fortuin: Draai elke dag gratis.\n2. 🏆 24 Uur Battle: Eindig om 00:00 uur in de top 3 van een van de 7 dagklassementen.\n3. 🚩 Controlepunten: Houd een controlepunt (op de Luchtplaats, Kantine, Sportzaal, Depot of Medicijnkluis) 30 minuten vast en ontvang +1 gratis Ster.\n4. 🚆 Begeleid Verlof & Koffers: Scoor een Ster bij het Station tijdens verlof of via geheime Kluiscodes.\n5. ⛏️ Uitbraaktunnel: Graaf je tunnel tot 100% en ontvang +25 gratis Sterren.\n6. 📺 Sponsorkamer: Bekijk korte video's voor mijlpaalbeloningen.\n7. 🎫 Battle Pass: Speel gratis beloningen vrij in het seizoensoverzicht.",
                        'links' => [
                            ['text' => 'Rad van Fortuin', 'url' => route('daily.index')],
                            ['text' => '24 Uur Battle', 'url' => route('battle.index')],
                        ],
                    ],
                    [
                        'q' => 'Wat is Discipline en waarom heb ik dat nodig?',
                        'a' => "💡 IN HET KORT: Discipline laat zien hoe gedisciplineerd jij bent en helpt bij de psychiater en in de kliniek.\n\n📋 HOE BOUW JE DISCIPLINE OP?\n• Je kunt Discipline direct trainen in het Krachthonk (bij Directeur Gerda Graaigraag) óf verdienen door legaal te werken bij 'Voortgang > Dagbesteding' (Werkmeester Henk).\n• Hoe hoger je Discipline, hoe makkelijker je goedkeuring krijgt bij het Behandelaarsgesprek voor 3 uur Begeleid Verlof!",
                        'links' => [
                            ['text' => 'Naar Dagbesteding', 'url' => route('daycare.index')],
                            ['text' => 'Naar Krachthonk', 'url' => route('training.index')],
                        ],
                    ],
                ],
            ],
            'statistieken' => [
                'title' => '⚡ Energie, Gezondheid, Gym & Perks',
                'icon' => 'fa-bolt',
                'questions' => [
                    [
                        'q' => 'Hoe herstelt mijn Energie en hoe krijg ik direct een volle energiebalk?',
                        'a' => "💡 IN HET KORT: Je energie vult vanzelf elke paar minuten aan, of je eet een snack uit je inventaris om direct verder te gaan.\n\n📋 AUTOMATISCH HERSTEL:\n• Standaard speler: +1 energie per 5 minuten;\n• VIP / Tier 1: +1 energie per 3 minuten;\n• Tier 2 Elite: +1 energie per 2,5 minuut;\n• Tier 3 & 4 (Baas / Lifetime): +1 energie per 2 minuten!\n\n📋 DIRECT EXTRA ENERGIE NODIG?\n1. 🍫 Kantinesnacks eten: Koop chocoladerepen of energiedrankjes in de Kliniek Winkel en klik op 'Gebruiken' in je Inventaris.\n2. 🎰 Rad van Fortuin: Draai gratis voor een energie-prijs.\n3. ⭐ Sterren / VIP Shop: Vul je energie én HP in één klap naar 100% met Sterren of 10 VIP Punten.",
                        'links' => [
                            ['text' => 'Winkel & Snacks', 'url' => route('shop.index', ['tab' => 'inventaris'])],
                            ['text' => 'VIP Shop', 'url' => route('vip.index')],
                        ],
                    ],
                    [
                        'q' => 'Wat doen Kracht, Verdediging, Conditie en de andere eigenschappen in de Gym?',
                        'a' => "💡 IN HET KORT: In het Krachthonk train je 6 eigenschappen (kost 10 Energie + een klein bedrag aan cash per training) om gevechten en missies te winnen.\n\n📋 WAT DOET ELKE EIGENSCHAP?\n• 🥊 KRACHT: Hoe harder jij slaat wanneer je iemand aanvalt op de Luchtplaats.\n• 🛡️ VERDEDIGING: Hoeveel schade jij tegenhoudt als iemand jou aanvalt.\n• 🏃 CONDITIE: Je fysieke uithoudingsvermogen in de kliniek.\n• 🧠 INTELLIGENTIE: Verhoogt je slaagkans bij slimme acties zoals Pillen Palmen en Hacking.\n• 🗣️ CHARISMA: Geeft tot +35% extra geldbonus bij Hosselen!\n• 📋 DISCIPLINE: Helpt bij het Behandelaarsgesprek en grote operaties.\n\n🔥 PRO-TIP: Train in het begin vooral Kracht en Verdediging gelijkmatig om gevechten op de Luchtplaats te winnen!",
                        'links' => [
                            ['text' => 'Naar Krachttraining (Gym)', 'url' => route('training.index')],
                        ],
                    ],
                    [
                        'q' => 'Wat zijn Perks (24-uurs Boosters) en welke kan ik activeren?',
                        'a' => "💡 IN HET KORT: Perks zijn tijdelijke superkrachten van 24 uur die je met Sterren ⭐ kunt aanzetten.\n\n📋 DE 3 SPECIALE PERKS ('/perks'):\n1. 👛 Grotere Broekzakken (25 ⭐): Je verdient 24 uur lang +10% extra contant geld bij al je Hossels en Zakkenrol-acties.\n2. 🔐 Kluis & Opslag Uitbreiding (20 ⭐): Je krijgt 24 uur lang +25 extra opslagplekken voor spullen.\n3. ⚖️ Topadvocaat op Speeddial (40 ⭐): Word je gepakt? Je advocaat haalt 24 uur lang direct 25% van elke isoleercel-straftijd af!",
                        'links' => [
                            ['text' => 'Perks Bekijken', 'url' => route('perks.index')],
                        ],
                    ],
                    [
                        'q' => 'Wat is Vechtoefening en hoe spar ik zonder risico?',
                        'a' => "💡 IN HET KORT: Bij Vechtoefening oefen je op trainingspoppen, zodat je sterker wordt zonder dat je geld of respect kunt verliezen.\n\n📋 WAAROM DOEN?\n• Ideaal als je nog niet sterk genoeg bent voor echte spelers op de Luchtplaats;\n• Je verdient veilig gevechtservaring en XP.",
                        'links' => [
                            ['text' => 'Naar Vechtoefening', 'url' => route('combat_training.index')],
                        ],
                    ],
                    [
                        'q' => 'Hoe werken wapens, kogelwerende vesten, snacks en EHBO-kits?',
                        'a' => "💡 IN HET KORT: Wapens en pantsers zijn direct na aankoop automatisch actief; snacks en EHBO-kits gebruik je via je Inventaris.\n\n📋 ZO WERKT HET:\n• 🔫 Wapens & Pantsers: Zodra je een wapen of vest koopt in de Kliniek Winkel, levert het direct en doorlopend passieve gevechtskracht op (je hoeft niets handmatig aan te trekken!).\n• 🍫 Snacks & Verbanddozen: Klik in 'Winkel > Mijn Inventaris' op 'Gebruiken' voor directe Energie of Gezondheid (HP).",
                        'links' => [
                            ['text' => 'Mijn Inventaris Openen', 'url' => route('shop.index', ['tab' => 'inventaris'])],
                        ],
                    ],
                ],
            ],
            'werk_voortgang' => [
                'title' => '🔨 Dagbesteding, Ziekenboeg & Verlof',
                'icon' => 'fa-person-digging',
                'questions' => [
                    [
                        'q' => 'Waarom is Dagbesteding & Werkplaats 100% veilig voor beginners?',
                        'a' => "💡 IN HET KORT: Omdat werken in de werkplaats legaal is, heb je 0% kans op de isoleercel en verdien je altijd geld!\n\n📋 VOORDELEN VAN WERKMEESTER HENK:\n• 🟢 100% Veilig: Je kiest uit verschillende klussen (van Wasgoed Vouwen op Level 1 tot Elektronica en Endgame Tiers op hogere levels, kost 3 tot 18 Energie).\n• 💵 Vast Loon & Level-Scaling: Je krijgt altijd direct contant geld uitbetaald; hogere levels ontgrendelen geavanceerde klussen met fors hogere lonen, respect en XP!\n• 📈 Gratis Stat-Bonus & XP: Bij elke klus heb je 50% kans op +1 extra statpunt (Discipline, Conditie, Kracht of Intelligentie)!",
                        'links' => [
                            ['text' => 'Veilig Werken bij Henk', 'url' => route('daycare.index')],
                        ],
                    ],
                    [
                        'q' => 'Wat is het Behandelaarsgesprek bij Dr. Vink en hoe verdien ik 3 uur Verlof?',
                        'a' => "💡 IN HET KORT: Beantwoord elke 2 uur 3 vragen van psychiater Dr. Vink op een rustige manier en verdien €2.500 cash + 3 uur Begeleid Verlof!\n\n📋 ZO WERKT HET GESPREK:\n• Elke 2 uur kun je op gesprek onder 'Voortgang > Behandeling'.\n• Dr. Vink stelt je 3 vragen over hoe jij reageert. Kies altijd het meest rustige, beleefde en meewerkende antwoord!\n• 🟢 Scoor je 60% of hoger? Dan krijg je direct €2.500 cash, 120 XP én 3 UUR BEGELEID VERLOF!\n• 🔴 Scoor je onder de 35% door agressieve antwoorden? Dan krijg je een kalmeringsspuit (60 minuten sedatie en -30 Energie).",
                        'links' => [
                            ['text' => 'Naar Behandelaarsgesprek', 'url' => route('treatment.index')],
                        ],
                    ],
                    [
                        'q' => 'Wat kan ik doen tijdens mijn 3 uur Begeleid Verlof buiten de poorten?',
                        'a' => "💡 IN HET KORT: Tijdens je verlof kun je 3 speciale buiten-missies doen voor veel contant geld, een Gouden Ster, een geheime Kluiscode of een Smokkelkoffer!\n\n📋 DE 3 BUITEN-MISSIES:\n1. 🛒 Supermarkt Plunderen (12 Energie): Verdien €2.500 tot €7.500 contant + 90 XP + 2 Respect;\n2. 🚆 Station Oplichting (16 Energie): Verdien €1.500 tot €4.000 contant + 140 XP + 4 Respect + 25% kans op 1 Gouden Ster ⭐;\n3. 🛅 Bagagekluisjes Openbreken (15 Energie): Verdien €3.000 tot €6.000 contant + 120 XP + 3 Respect + 50% kans op een geheime Kluiscode óf een Smokkelkoffer!\n\n🔥 PRO-TIP: Voer alle drie de buiten-missies meteen uit zodra je verlof ingaat!",
                        'links' => [
                            ['text' => 'Verlof & Behandeling', 'url' => route('treatment.index')],
                        ],
                    ],
                    [
                        'q' => 'Wat is Pillen Palmen in de Ziekenboeg?',
                        'a' => "💡 IN HET KORT: Voor 10 Energie smokkel je stiekem Kalmeringspillen, Ritalin of Haloperidol uit de medicijnkar van Zuster Ingrid.\n\n📋 HOE WERKT PILLEN PALMEN?\n• Ga naar 'Voortgang > Ziekenboeg' en klik op 'Pillen Palmen' (kost 10 Energie, basis-slaagkans 65% + Intelligentie-bonus).\n• Lukt het? Dan buit je direct 1 medicijn-item (Kalmeringspil, Ritalin of Haloperidol Forte) + 50 XP + 4 Respect.\n• Word je betrapt door Zuster Ingrid? Dan ga je de Isoleercel in!",
                        'links' => [
                            ['text' => 'Naar de Ziekenboeg', 'url' => route('infirmary.index')],
                        ],
                    ],
                ],
            ],
            'isoleercel' => [
                'title' => '⛓️ Isoleercel, Straf & Ontsnappingen',
                'icon' => 'fa-dungeon',
                'questions' => [
                    [
                        'q' => 'Waarom zit ik in de Isoleercel en wat kan ik nog doen?',
                        'a' => "💡 IN HET KORT: Als een misdaad mislukt, sluit de bewaker je tijdelijk op in de isoleercel.\n\n📋 WAT KUN JE WEL EN NIET DOEN?\n• ❌ Niet mogelijk: Misdaden plegen, vechten, reizen of geld storten bij de Geldmaat (stort je geld dus altijd vóór een misdaad!).\n• ✅ Wel mogelijk: Chatten met medepatiënten én jezelf op 6 manieren sneller bevrijden!",
                        'links' => [
                            ['text' => 'Naar Isoleercel', 'url' => route('prison')],
                        ],
                    ],
                    [
                        'q' => 'Hoe kom ik meteen of sneller vrij uit de Isoleercel (Alle 6 manieren)?',
                        'a' => "💡 IN HET KORT: Je hoeft niet te wachten tot de klok afloopt; je kunt jezelf op meerdere manieren sneller bevrijden!\n\n📋 DE MANIEREN OM VRIJ TE KOMEN:\n1. 🏃 Luchtschacht-Uitbraak: Probeer 1x per celstraf zelf door de luchtschacht te kruipen (kost 20 Energie). Lukt het? Dan ben je METEEN VRIJ (+15 Respect & +35 XP)!\n2. 🪚 IJzerzaagje Gebruiken: Zaag de tralies door met een ijzerzaagje uit je inventaris (-30 minuten straftijd per zaagje).\n3. 🧺 Stoomwasserij-Dienst: Werk even in de hete wasserij (kost 10 Energie) om je straf met 45 seconden in te korten.\n4. 💰 Nachtcipier Omkopen: Geef de bewaker €2.500 contant voor 75% kans op 90 seconden strafvermindering.\n5. ⭐ Direct Vrijkopen met Sterren: Koop jezelf direct vrij met Sterren ⭐ (10 ⭐ via de Sterrenwinkel of 25 ⭐ direct in de cel).\n6. 🤝 Hulp van een Vriend / BFF: Vraag in de chat of een vrije medespeler jouw celdeur van buitenaf openbreekt!",
                        'links' => [
                            ['text' => 'Naar Isoleercel', 'url' => route('prison')],
                            ['text' => 'Sterrenwinkel (10⭐ Vrijkopen)', 'url' => route('shop.index')],
                        ],
                    ],
                    [
                        'q' => 'Waarom staat er \'Je Zit Zelf Vast\' bij de namen onderaan de Isoleercel pagina?',
                        'a' => "💡 IN HET KORT: De lijst onderaan is om andere gevangenen te bevrijden, en dat kan alleen als je zelf vrij rondloopt.\n\n📋 SIMPEL UITGELEGD:\n• 🔴 Zit je zelf opgesloten? Gebruik dan de knoppen BOVENAAN de pagina om jezelf vrij te maken.\n• 🟢 Loop je vrij rond? Dan kun je ONDERAAN de pagina op 'Uitbreken' klikken bij opgesloten vrienden om hun deur open te breken!",
                    ],
                    [
                        'q' => 'Wat is de Uitbraaktunnel onder mijn cel en wat krijg ik bij 100%?',
                        'a' => "💡 IN HET KORT: Met een eetlepel graaf je stiekem een tunnel van 0% naar 100% onder je celvloer voor een megabeloning en 25 gratis Sterren!\n\n📋 ZO WERKT DE TUNNEL ('/ontsnappingstunnel'):\n• Je krijgt bij de start van je tunnel direct 1 gratis Eetlepel cadeau!\n• Elke graafbeurt kost 15 Energie en graaft 2% tot 5% verder.\n• Vergeet niet om het uitgegraven zand stiekem buiten te dumpen zodra je zakken vol zitten!\n• Bereik je 100%? Dan slaagt de Grote Uitbraak: je wint een grote som geld, veel XP en +25 gratis Sterren ⭐!",
                        'links' => [
                            ['text' => 'Naar Uitbraaktunnel', 'url' => route('tunnel.index')],
                        ],
                    ],
                ],
            ],
            'illegale_acties' => [
                'title' => '🕵️ Illegale Acties, Smokkel & Diefstal',
                'icon' => 'fa-handcuffs',
                'questions' => [
                    [
                        'q' => 'Wat is het verschil tussen Hosselen, Diefstal en Smokkelen?',
                        'a' => "💡 IN HET KORT: Hosselen is voor snel geld (€400 tot €65.000), Diefstal voor grotere buit, en Smokkelen is 4 soorten contrabande produceren en met winst verkopen.\n\n📋 WELKE KIES JE?\n• 🚬 Hosselen ('/hosselen'): 6 klussen op de afdeling (€400 tot €65.000 + tot 35% extra via Charisma). Beste keuze voor beginners!\n• 🥷 Diefstal ('/diefstal'): Spannendere diefstallen met mooie beloningen.\n• 🌿 Smokkelen & Dealen ('/smokkelen'): Produceer en verhandel Sigaretten, Ritalin, Haloperidol en Nederwiet/Kaswiet Toppen wanneer de marktkoers hoog staat!",
                        'links' => [
                            ['text' => 'Hosselen', 'url' => route('hustle.index')],
                            ['text' => 'Diefstal', 'url' => route('theft.index')],
                            ['text' => 'Smokkelen', 'url' => route('smuggle.index')],
                        ],
                    ],
                    [
                        'q' => 'Hoe werkt Zakkenrollen en waarom zijn Maskers (Bivakmutsen) zo belangrijk?',
                        'a' => "💡 IN HET KORT: Voor Zakkenrollen en Grote Heists heb je verplicht een Bivakmuts 🎭 nodig om onherkenbaar toe te slaan!\n\n📋 STAP VOOR STAP ZAKKENROLLEN ('/zakkenrollen'):\n1. Zorg dat je minimaal 1 Bivakmuts 🎭 op voorraad hebt (te koop in de winkel of te winnen bij Battles, Kluiscodes en Koffers).\n2. Kies je doelwit bij Zakkenrollen om contant geld en items buit te maken.\n\n🔥 PRO-TIP: Houd altijd een voorraadje Bivakmutsen achter de hand voor Zakkenrollen en Grote Heists!",
                        'links' => [
                            ['text' => 'Naar Zakkenrollen', 'url' => route('pickpocket.index')],
                            ['text' => 'Koop Bivakmuts in Winkel', 'url' => route('shop.index')],
                        ],
                    ],
                    [
                        'q' => 'Hoe werken Afpersen, Smokkel Droppings, Kluisjes Kraken en Hacking?',
                        'a' => "💡 IN HET KORT: Dit zijn 4 extra manieren om zwart geld, sigaretten, pillen, kluismunten en smokkelpakketjes te verdienen.\n\n📋 OVERZICHT:\n• 👊 Afpersen ('/afpersen'): Dwing doelwitten om jou zwijggeld te betalen.\n• 🛸 Tennisbal & Drone Droppings ('/smokkel-droppings'): Vang een Tennisbal Dropping op (15 Energie) of bestel vanaf Level 3 een Drone Dropping (€500 + 25 Energie voor €1.800–€4.800 cash, sigaretten, Ritalin en kans op een Smokkelpakketje).\n• 🗄️ Kluisjes Kraken ('/kluisjes'): Breek personeelskluisjes open met een lockpick of loper.\n• 💻 Hacking ('/hacking'): Breek in op het computernetwerk van de kliniek voor digitaal zwart geld.",
                        'links' => [
                            ['text' => 'Afpersen', 'url' => route('extort.index')],
                            ['text' => 'Droppings', 'url' => route('droppings.index')],
                            ['text' => 'Kluisjes Kraken', 'url' => route('lockers.index')],
                            ['text' => 'Hacking', 'url' => route('hacking.index')],
                        ],
                    ],
                ],
            ],
            'garage_heists' => [
                'title' => '🏎️ Smokkel Garage, Bende Vloot & Grote Heists',
                'icon' => 'fa-masks-theater',
                'questions' => [
                    [
                        'q' => 'Hoe werkt de Roterende Smokkel Garage en wagenhandel?',
                        'a' => "💡 IN HET KORT: In de garage koop je voertuigen voor overvallen en bende-producties. Elk half uur arriveert er een nieuwe lading van 8 wagens in wisselende staat!\n\n📋 DE ROULERENDE WAGENMARKT ('/garage'):\n• 🕒 Elk Half Uur Nieuwe Lading: Om het hele en halve uur arriveert een nieuwe selectie van 8 unieke voertuigen.\n• 🔧 Projectwagens & Koopjes: Voertuigen worden aangeboden met wisselende schadecondities. Een opknapper met lagere conditie kent een gigantische korting: repareer hem zelf voordelig in je werkplaats en verkoop hem door of doneer hem aan je bende!\n• 🚀 Getunede Motoren: Sommige wagens arriveren met fabrieks-tuning en extra topsnelheid. Dit snelheidsvoordeel blijft permanent behouden!\n• 🏎️ 28 Voertuigmodellen: Van Tomos en Zip Scooter tot Golf GTI, G63 AMG, Speedboten en Vluchthelikopters die je allemaal kunt inzetten.",
                        'links' => [
                            ['text' => 'Naar Smokkel Garage', 'url' => route('garage.index')],
                        ],
                    ],
                    [
                        'q' => 'Hoe voer ik een Grote Heist of Bende Co-Op Heist uit?',
                        'a' => "💡 IN HET KORT: Voor een Heist heb je een snel voertuig, een bivakmuts en energie nodig. Bij Bende Heists strijd je samen en split je de miljoenenbuit 50/50!\n\n📋 SOLO HEISTS VS BENDE HEISTS ('/heists'):\n• 🥷 Solo Overvallen: Breek in bij een Juwelier of Geldtransport voor directe cash, XP en kluiscodes.\n• 👥 Bende Co-Op Heists: Grootschalige syndicaatkraken (zoals Douane Depot en Fort Goudkluis Heist).\n• 💰 50/50 Buitverdeling: Bij een geslaagde Bende Heist ontvang jij 50% van de buit direct contant op zak. De overige 50% wordt automatisch gestort in de gezamenlijke bendekas!\n• 📈 Co-Op Bonus: Hoe meer actieve leden jouw bende telt, hoe hoger de gezamenlijke slagingskans.\n• 🏎️ Snelheidseis: Zware heists vereisen een vluchtwagen in de Bende Garage met voldoende topsnelheid (bijv. minimaal 95 km/u voor Fort Goudkluis)!",
                        'links' => [
                            ['text' => 'Naar Grote Heists', 'url' => route('heists.index')],
                        ],
                    ],
                    [
                        'q' => 'Hoe werkt de Bende Garage, gratis revisies via de bendekas en wagens doneren (+50 Respect)?',
                        'a' => "💡 IN HET KORT: In de Bende Garage bundelt de bende wagens voor heists en pandproducties. Schade wordt 100% vergoed uit de bendekas en leden kunnen wagens permanent doneren voor +50 Respect!\n\n📋 ALLE BENDE GARAGE REGELS ('/gangs/garage'):\n• 🛠️ Revisies 100% uit Bendekas: Voertuigen die in de Bende Garage staan worden uitsluitend en direct hersteld met geld uit de bendekas. Jouw privégeld en persoonlijke bankrekening worden nooit aangeraakt!\n• 🎁 Wagens Definitief Doneren (+50 Respect): Klik op 'Doneer Definitief aan Bende' om een wagen permanent over te dragen aan het wagenpark. De donateur krijgt direct +50 Respect (wat ook de rang van je bende versterkt!) en de wagen wordt onvervreemdbaar 'Bende Eigendom'.\n• 🚜 Slopen voor Bendekas: Dreigt de garage vol te raken? Leiders en co-leiders kunnen overtollige bende-eigendom wagens ontmantelen via de rode knop voor direct 25% van de aanschafwaarde terug in de bendekas!",
                        'links' => [
                            ['text' => 'Naar Bende Garage', 'url' => route('gangs.garage')],
                        ],
                    ],
                ],
            ],
            'koffers_kraken' => [
                'title' => '📦 Koffers Onderscheppen, Kraken & XXL Buit',
                'icon' => 'fa-briefcase',
                'questions' => [
                    [
                        'q' => 'Hoe werkt Koffers Onderscheppen en wat zit er in een XXL Koffer?',
                        'a' => "💡 IN HET KORT: Onderschep pakketjes voor cash, KluisMunten 🔑 en Smokkelkoffers, en breek koffers open voor duizenden euro's!\n\n📋 SOORTEN KOFFERS ('/pakketjes'):\n1. 🧰 Gewone Smokkelkoffer: Breek open voor €1.250 tot €6.562 cash (met Ruwe Kracht 1,75x multiplier!) of handige spullen.\n2. 💼 XXL Smokkelkoffer: De hoofdprijs (te winnen bij o.a. de 24 Uur Battle of in de Sterrenwinkel)! Bevat €25.000 tot €75.000 veilig bankgeld én zeldzame buit!",
                        'links' => [
                            ['text' => 'Koffers Onderscheppen', 'url' => route('packages.index')],
                        ],
                    ],
                ],
            ],
            'vechten' => [
                'title' => '🥊 Respectlijst, Vechten & Gevechtsregels',
                'icon' => 'fa-hand-fist',
                'questions' => [
                    [
                        'q' => 'Hoe werkt vechten op de Respectlijst (Luchtplaats) en wat win ik ermee?',
                        'a' => "💡 IN HET KORT: Daag een speler uit op de Luchtplaats; als je wint steel je zijn Respect én 10% tot 25% van zijn contante zakgeld!\n\n📋 BELANGRIJKE GEVECHTSREGELS:\n• 🥊 Winnaar: Jouw totale Kracht (inclusief passieve wapenbonus, bende-bonus en buffs) strijdt tegen de Verdediging (inclusief pantser) van je tegenstander.\n• 🔢 Daglimiet: Standaard spelers mogen maximaal 100 gevechten per dag doen (VIP-leden mogen 1.000 gevechten per dag doen). Om 00:00 uur reset de teller.\n• 🧮 Twijfel je of je kunt winnen? Gebruik eerst de 'Interactieve Gevecht Simulator' bovenaan deze FAQ-pagina!",
                        'links' => [
                            ['text' => 'Naar de Luchtplaats', 'url' => route('fight.index')],
                        ],
                    ],
                    [
                        'q' => 'Hoe bescherm ik mezelf als ik steeds word aangevallen door sterkere spelers?',
                        'a' => "💡 IN HET KORT: Zet je geld op de bank, activeer het Medisch Schild of sluit jezelf veilig op in de Vrijwillige Isolatie bunker.\n\n📋 4 MANIEREN OM VEILIG TE ZIJN:\n1. 🛡️ Medisch Schild (Ziekenboeg Beveiliging): Als je net behandeld bent, beschermt een medisch schild jou tegen nieuwe aanvallen. Wil je zelf iemand aanvallen? Dan waarschuwt een pop-up met bevestiging je vooraf, zodat je je schild nooit per ongeluk verspeelt!\n2. 🛡️ Vrijwillige Isolatie ('/vrijwillige-isolatie'): Kruip tijdelijk in de beveiligde bunker. Zolang je daar zit, kan NIEMAND op de Luchtplaats jou aanvallen!\n3. 🏦 Geld op De Bank: Als je €0 contant op zak hebt, kunnen aanvallers geen cent van je stelen.\n4. 🎯 Kopgeld Zetten ('/kopgeld'): Zet een premie op het hoofd van je pester, zodat sterke spelers hem voor jou verslaan!",
                        'links' => [
                            ['text' => 'Vrijwillige Isolatie', 'url' => route('isolation.index')],
                            ['text' => 'Kopgeld Plaatsen', 'url' => route('bounties.index')],
                        ],
                    ],
                    [
                        'q' => 'Wat is Bende Wraakrecht (Retaliation) na een aanval op een bendelid?',
                        'a' => "💡 IN HET KORT: Wordt een lid van jouw bende aangevallen op de Luchtplaats? Dan krijgt jouw hele bende 24 uur lang officieel wraakrecht op die aanvaller!\n\n📋 HOE WERKT BENDE WRAAKRECHT?\n• 🩸 Geen Level-Limiet: Normaal kun je alleen spelers binnen jouw eigen levelbereik aanvallen. Bij Bende Wraakrecht vervallen deze beperkingen tijdelijk tegen die specifieke dader!\n• 👊 Samen Vergelding Nemen: De sterkste bazen van jouw bende kunnen de aanvaller direct mores leren en zijn respect en geld plunderen.",
                        'links' => [
                            ['text' => 'Naar de Luchtplaats', 'url' => route('fight.index')],
                        ],
                    ],
                    [
                        'q' => 'Wat doen Informanten en hoe werkt Armpjedrukken?',
                        'a' => "💡 IN HET KORT: Informanten verklappen wie veel contant geld op zak heeft; bij Armpjedrukken strijd je 1-tegen-1 om een geldinzet.\n\n📋 EXTRA'S:\n• 🕵️ Informanten ('/informanten'): Koop een tip om te zien welk doelwit vergeten is zijn geld op de bank te zetten.\n• 💪 Armpjedrukken ('/armpjedrukken'): Daag medepatiënten uit aan de tafel in de recreatiezaal.",
                        'links' => [
                            ['text' => 'Informanten', 'url' => route('informants.index')],
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
                        'q' => 'Wat is Liquidatie 2.0 en hoe werkt schieten met kogels?',
                        'a' => "💡 IN HET KORT: Bij Liquidatie 2.0 schiet je met kogels op een rivaal (+25% Sluipaanval-schade als je op dezelfde afdeling staat!) en roof je 45% van zijn contante geld én 7% van zijn bank!\n\n📋 ZO WERKT HET STAP VOOR STAP ('/liquidatie'):\n1. 🕵️ Zoek je Doelwit: Je kunt van afstand schieten (Afstandssalvo), óf via 'Camera- & Gangpad Schaduwing' kijken waar je doelwit staat en naar dezelfde afdeling reizen voor +25% Sluipaanval-bonus!\n2. 🔫 Kogels Inzetten: Vanaf Dossier-Graad 25 (met 30% Koudbloedigheid) is 1 kogel al dodelijk (Genadeschot). Daaronder schiet je een salvo van minimaal 10 kogels.\n3. 💰 De Buit: Bij een voltreffer pak je 45% van zijn contante zakgeld én 7% van zijn bankrekening!\n\n🔥 PRO-TIP: Gratis kogels nodig? Voltooi je Rookie Taken (+30 kogels) of kijk korte video's in de Sponsorkamer!",
                        'links' => [
                            ['text' => 'Naar Liquidatie 2.0', 'url' => route('hitman.index')],
                            ['text' => 'Gratis Kogels (Sponsorkamer)', 'url' => route('sponsor.index')],
                        ],
                    ],
                    [
                        'q' => 'Wat gebeurt er als ik word neergeschoten (Brancard vs 10/10 Code Blauw)?',
                        'a' => "💡 IN HET KORT: De eerste 9 keer kun je direct doorspelen; pas bij de 10e keer kom je in Code Blauw (Coma) en heb je een Forensisch Reanimatie-Serum nodig.\n\n📋 GEEN ZORGEN, ZO WERKT HET:\n• 🚑 Treffer 1 t/m 9 (Direct Doorspelen!): Je gaat NIET in coma. Je houdt 20 HP over, wordt naar een andere afdeling gebracht en krijgt 15 minuten een beschermend schild.\n• 💉 Treffer 10 van de 10 (Code Blauw): Pas bij 10/10 treffers krijg je een hartstilstand (de gewone Ziekenboeg werkt dan niet). Je koopt dan het Forensisch Reanimatie-Serum (€1.500.000 of 75 Sterren ⭐) om direct weer met 100% HP en 0/10 treffers op te staan. Heb je geen €1,5 miljoen? Dan helpt de medische noodkrediet-regeling je altijd weer op de been!",
                    ],
                    [
                        'q' => 'Wat doen Backfire-Vesten (Kogels Terugkaatsen) en 24u Cash Bescherming?',
                        'a' => "💡 IN HET KORT: Een Backfire-vest kaatst kogels terug op de schutter; 24u Cash Bescherming zorgt dat niemand één euro van je kan stelen!\n\n📋 ULTIEME VERDEDIGING:\n• 🛡️ Backfire-Vesten (30% tot 75% terugkaatskans): Koop een vest in het Wapenarsenaal. Schiet iemand met te weinig vuurkracht op jou? Dan ketst de kogel terug en wordt de schutter zélf afgevoerd op een brancard!\n• 🔒 24 Uur Cash Bescherming (VIP Shop): Zolang dit schild aanstaat, kan een schutter €0 contant en €0 van je bank stelen!",
                        'links' => [
                            ['text' => 'Backfire-Vest Kopen', 'url' => route('hitman.index')],
                            ['text' => '24u Cash Bescherming (VIP Shop)', 'url' => route('vip.index')],
                        ],
                    ],
                    [
                        'q' => 'Wat is Bloedwraak (Blood Feuds) voor bendes?',
                        'a' => "💡 IN HET KORT: Wordt iemand uit jouw bende neergeschoten? Dan mag jouw hele bende 24 uur lang zonder level-limiet wraak nemen op de dader!\n\n📋 VOORDELEN VAN BLOEDWRAAK:\n• Geen 15-level aanvalslimiet tegen de dader gedurende 24 uur;\n• Wie de dader terugpakt wint direct +50 Respect, €25.000 cash én +150 Bende-Respect voor de club!",
                    ],
                    [
                        'q' => 'Wat is Koudbloedigheid, waarom heb ik het nodig en hoe train ik het?',
                        'a' => "💡 IN HET KORT: Koudbloedigheid (0%–100%) is jouw precisie- en kaliberfocus bij Liquidatie 2.0. Vanaf Dossier-Graad 25 én minimaal 30% Koudbloedigheid kun je met een dodelijk 1-Patroon Genadeschot schieten!\n\n📋 3 MANIEREN OM KOUDBLOEDIGHEID TE TRAINEN / VERHOGEN:\n1. 🎯 Ondergrondse Schietbaan ('/liquidatie'): Voor €350.000 contant/bank + 15 Energie krijg je direct +10% Koudbloedigheid en +120 XP (of 25 Sterren ⭐ voor +15% Koudbloedigheid zonder energiekosten).\n2. 🥊 Vechtoefening ('/vechtoefening'): Elke training tegen een bokspop geeft direct +4% Koudbloedigheid erbij (naast statpunten en XP)!\n3. 📦 Grote Munitiekisten Kopen ('/liquidatie'): De Paviljoen Munitiekist geeft direct +5%, de Syndicaat Wapenkist +10%, en de Kartel Arsenaalcontainer +15% Koudbloedigheid!\n\n🔥 LET OP: Schieten kost focus (-12% bij een Genadeschot, -6% bij een salvo). Zakt je Koudbloedigheid onder de 30%? Dan trilt je hand en kun je tijdelijk niet met 1 patroon schieten tot je weer bijtraint!",
                        'links' => [
                            ['text' => 'Naar Liquidatie & Schietbaan', 'url' => route('hitman.index')],
                            ['text' => 'Naar Vechtoefening (+4%)', 'url' => route('combat_training.index')],
                        ],
                    ],
                    [
                        'q' => 'Wat is het verschil tussen een 1-Patroon Genadeschot en een Salvo (Dossier-Graad 25)?',
                        'a' => "💡 IN HET KORT: Een Genadeschot vuurt exact 1 patroon af met maximale precisie; een Salvo vuurt minimaal 10 patronen tegelijk af.\n\n📋 VOORWAARDEN & VERSCHILLEN:\n• 🎯 1-Patroon Genadeschot: Vereist Dossier-Graad 25+ (Forensisch Scherpschutter) én minimaal 30% Koudbloedigheid. Kost slechts 1 patroon en verlaagt je Koudbloedigheid met -12% focus.\n• 💥 Normaal Salvo: Kan op elke graad worden geschoten (minimaal 10 patronen). Kost -6% Koudbloedigheid focus per schot. Voor een salvo dat jouw graad aanbeveelt ontvang je bovendien een +20% Barrage Bonus!\n\n🔥 PRO-TIP: Houd je Koudbloedigheid altijd boven de 30% door na een liquidatie even te trainen op de Ondergrondse Schietbaan!",
                        'links' => [
                            ['text' => 'Naar Liquidatie 2.0', 'url' => route('hitman.index')],
                        ],
                    ],
                ],
            ],
            'economie' => [
                'title' => '🏦 Bank, Leningen, Reizen & Marktplaats',
                'icon' => 'fa-building-columns',
                'questions' => [
                    [
                        'q' => 'Hoe werken Storten, Opnemen, Zakgeld en Leningen bij De Bank?',
                        'a' => "💡 IN HET KORT: Op De Bank bewaar je gratis en onbeperkt je geld (tot 1 Biljoen Euro!), ontvang je elk kwartier gratis zakgeld en kun je geld lenen tegen 8,5% rente.\n\n📋 ALLE BANKFUNCTIES ('/bank'):\n• 📥 Geld Storten: 100% gratis en onbeperkt. Dankzij onze 64-bit modernisering kun je probleemloos honderden miljarden storten en overboeken zonder vast te lopen op oude 2-miljard limieten!\n• 📤 Geld Opnemen: Kost 5% opnamebelasting (slechts 3% voor VIP). Deze belasting gaat 100% in de pot van de Afdelingsloterij!\n• 🎁 Gratis Zakgeld (Elke 15 min): Spelers zonder bende krijgen elke 15 minuten automatisch €1.000 op hun bank (VIP tot €25.000 per 15 min).\n• 💳 Geld Lenen (vanaf Level 1): Leen €10.000 tot €50.000 × jouw Level tegen slechts 8,5% rente (10% automatische aflossing uit je inkomsten).\n• 🔢 Bank Pincode: Stel een eigen 4-cijferige pincode in voor extra beveiliging van je kluis.",
                        'links' => [
                            ['text' => 'Naar De Bank', 'url' => route('bank.index')],
                        ],
                    ],
                    [
                        'q' => 'Wat zijn Cel Upgrades en hoe richt ik mijn eigen verblijf in?',
                        'a' => "💡 IN HET KORT: In je eigen cel ('/cel') kun je luxe verbouwingen en upgrades installeren voor status, sneller energieherstel en bescherming!\n\n📋 VOORDELEN VAN CEL UPGRADES:\n• 🛏️ Luxe Inrichting: Upgrade je matras, celdeur, ventilatieroosters en trainingshoek.\n• ⚡ Passieve Voordelen: Snellere energieregeneratie en celverdediging tegen plotselinge invallen.\n• 🏛️ Status & Geldput: Een veilige plek om je verdiende vermogen te investeren in permanente comfort en prestige.",
                        'links' => [
                            ['text' => 'Mijn Cel & Upgrades', 'url' => route('cell.index')],
                        ],
                    ],
                    [
                        'q' => 'Hoe werkt Verplaatsen (Reizen) tussen de 8 afdelingen en hoe verdien ik +35 XP?',
                        'a' => "💡 IN HET KORT: Reis tussen de 8 klinieklocaties (Afdeling A t/m D, Luchtplaats, Werkplaats, Bezoekerscentrum en Recreatiezaal) en krijg bij elke reis meteen +35 XP cadeau!\n\n📋 WAAROM REIZEN ('/essentials/verplaatsen')?\n• Elke reis tussen de 8 locaties geeft je direct +35 XP (dé snelste manier om als beginner in level te stijgen!).\n• Ga je naar exact dezelfde afdeling als je doelwit bij Liquidatie 2.0? Dan krijg je +25% Sluipaanval-schadebonus.\n• VIP-leden krijgen altijd 50% korting op de reiskosten.",
                        'links' => [
                            ['text' => 'Nu Verplaatsen (+35 XP)', 'url' => route('travel.index')],
                        ],
                    ],
                    [
                        'q' => 'Hoe werken de Marktplaats, de Kliniek Winkel en het Depot?',
                        'a' => "💡 IN HET KORT: In de Winkel koop je nieuwe spullen, op de Marktplaats handel je met andere spelers en in het Depot sla je extra spullen op.\n\n📋 OVERZICHT:\n• 🛒 Kliniek Winkel ('/winkel'): Koop wapens, vesten, snacks en verbanddozen.\n• 🤝 Marktplaats ('/marktplaats'): Verkoop jouw gevonden koffers, maskers of wapens voor je eigen vraagprijs aan andere spelers.\n• 🏭 Het Depot ('/depot'): Je persoonlijke opslagloods voor spullen die je wilt bewaren.",
                        'links' => [
                            ['text' => 'Kliniek Winkel', 'url' => route('shop.index')],
                            ['text' => 'Marktplaats', 'url' => route('marketplace.index')],
                            ['text' => 'Het Depot', 'url' => route('depot.index')],
                        ],
                    ],
                ],
            ],
            'loterij_casino' => [
                'title' => '🎰 Casino, Minigames & Afdelingsloterij',
                'icon' => 'fa-dice',
                'questions' => [
                    [
                        'q' => 'Hoe werkt de 14-daagse TBS Afdelingsloterij en de 50% Laatste Kwartier Korting?',
                        'a' => "💡 IN HET KORT: Elke 14 dagen wint één speler de gigantische Loterij Jackpot, gevuld met alle bankbelastingen van de hele kliniek!\n\n📋 ZO WIN JE DE JACKPOT ('/loterij'):\n• 🎟️ Prijs per lot: €10.000 contant of 15 Sterren ⭐.\n• ⚡ PRO-TIP (Laatste Kwartier Korting): In de allerlaatste 15 minuten voor de trekking kosten loten maar de helft (€5.000 contant of 8 Sterren ⭐ per stuk)!\n• 💰 Uitbetaling: De winnaar krijgt 30% veilig op de bank en 70% contant uitbetaald.",
                        'links' => [
                            ['text' => 'Naar de Afdelingsloterij', 'url' => route('lottery.index')],
                        ],
                    ],
                    [
                        'q' => 'Welke spellen kan ik spelen in het Casino en bij de Minigames?',
                        'a' => "💡 IN HET KORT: Waag een gokje bij 5 casinospellen of train je brein bij Vier-op-een-rij, True Crime en Memory!\n\n📋 CASINO ('/casino'):\n• Roulette (tot 36x je inzet), Hoger/Lager, Nummerspel, Steen-Papier-Schaar en de Fruitautomaat (Slots).\n\n📋 MINIGAMES:\n• 🔴 Vier-op-een-rij ('/vier-op-een-rij'): Speel tegen de AI of andere patiënten.\n• 🕵️ True Crime ('/true-crime'): Los misdaadsdossiers op voor geld en kluiscodes.\n• 🧠 Geheugentraining ('/memory'): Zoek dezelfde kaarten bij elkaar binnen de tijd voor mooie bonussen!",
                        'links' => [
                            ['text' => 'Casino Openen', 'url' => route('casino.index')],
                            ['text' => 'Memory Spelen', 'url' => route('memory.index')],
                            ['text' => 'True Crime', 'url' => route('true_crime.index')],
                        ],
                    ],
                ],
            ],
            'gangs' => [
                'title' => '👥 Bendes, Vastgoed, Uurloon & Bendeoorlogen',
                'icon' => 'fa-users',
                'questions' => [
                    [
                        'q' => 'Wat is de Gang Overstap Bonus (€5.000.000 + 31 Dagen Gratis VIP)?',
                        'a' => "💡 IN HET KORT: Kom je met je vriendengroep of bende van een ander spel naar TBS Kliniek? Dan krijgen jullie gratis een bende, €5 miljoen startgeld én een maand gratis VIP voor iedereen!\n\n📋 ZO VRAAG JE HET AAN IN 1 MINUUT:\n1. Ga naar 'Ondersteuning > Nieuw Ticket Aanmaken'.\n2. Kies het onderwerp '🔥 Gang Overstap Bonus'.\n3. Typ jullie gewenste bendenaam en van welk spel jullie komen.\n4. Ons team zet jullie bende direct klaar met €5.000.000 in de kluis en 31 dagen VIP voor elk lid!",
                        'links' => [
                            ['text' => 'Overstap Ticket Aanmaken', 'url' => route('help.tickets.create')],
                        ],
                    ],
                    [
                        'q' => 'Hoe werkt het Bende-Uurloon en waar komt het geld vandaan?',
                        'a' => "💡 IN HET KORT: Elk heel uur ontvang je als actief bendelid automatisch salaris op je bankrekening, gebaseerd op de bezittingen en vloot van jouw bende!\n\n📋 DE 5 INKOMSTENPIJLERS VAN HET UURLOON ('/gangs'):\n1. 🏪 Kantine Smokkelkiosken: Een stabiele basis tot maximaal 1.000 effectieve kiosken (€50/u per kiosk = maximaal €50.000/u als solide bodem).\n2. 🏎️ Actief Bende Wagenpark: Voertuigen in de Bende Garage genereren 24/7 passief loon (€1.500/u per motor, €4.000/u per auto, €12.500/u per boot en €25.000/u per helikopter of vliegtuig).\n3. 🏢 Bende Panden & Vastgoed: Elk gekocht pand levert een vaste uurloonbijdrage plus een percentagebonus (tot wel +40% per topfaciliteit).\n4. ⭐ Bende Respect Bonus: +1% bonus over de totale pot per 100.000 respect (tot maximaal +50%).\n5. 👑 Heerschappij Bonus (+25%): Wint jouw bende een 24-uurs bendeoorlog? Dan geniet de bende 7 dagen lang van een extra +25% heerschappij-bonus over de totale pot!\n\n📋 SALARISSCHAAL PER RANG:\n• Recruut: 50% van het basisbedrag;\n• Lid: 100% van het basisbedrag;\n• Veteraan: 110% van het basisbedrag;\n• Onderbaas: 125% van het basisbedrag;\n• Co-Leader: 140% van het basisbedrag;\n• Leider: 150% van het basisbedrag.\n\n🔥 PRO-TIP: Bekijk de live calculator op de bendepagina om precies te zien waar elke euro vandaan komt!",
                        'links' => [
                            ['text' => 'Naar Bendes', 'url' => route('gangs.index')],
                        ],
                    ],
                    [
                        'q' => 'Hoe werken Bende Panden, de 10 nieuwe syndicaatfaciliteiten en productiecycli?',
                        'a' => "💡 IN HET KORT: Bendes schaffen syndicaatpanden aan vanuit de bendekas om miljoenen aan cash, duizenden respect en flinke uurloonbonussen te produceren!\n\n📋 ALLES OVER BENDE PANDEN ('/gangs/panden'):\n• 🏢 10 Nieuwe Mega-Panden: Met prijzen van €15 miljoen tot maar liefst €1,5 miljard voor miljardairs-kartels (van Drone Smokkelbases en Tuner Hubs tot Diamantslijperijen en Kartel Citadels).\n• ⚙️ Productie Starten: Leiders starten productieopdrachten (met cycli van 2,5 tot 8 uur) die miljoenen cash en duizenden respect opleveren bij voltooiing.\n• 🚗 Garage-Vlooteisen: Veel panden vereisen specifieke voertuigen in de Bende Garage (bijv. minimaal 3 snelle wagens ≥80 km/u of meerdere helikopters). Zonder die vloot kan de productie niet starten!\n• 🕒 Roterende Showroom: Elk half uur toont de makelaar een wisselend aanbod verdeeld over 4 prijsklassen.",
                        'links' => [
                            ['text' => 'Naar Bende Panden', 'url' => route('gangs.panden')],
                        ],
                    ],
                    [
                        'q' => 'Wat zijn Bende Specialist NPC\'s en hoe werken 30-minuten contracten?',
                        'a' => "💡 IN HET KORT: Rekruteer chemici, witwassers en zwaarbewapende bewakers voor contracten van 30 minuten per productiecyclus!\n\n📋 VOORDELEN VAN SPECIALISTEN ('/gangs/panden'):\n• 🧪 Chemici: Verhogen de opbrengst en versnellen de productietijd van drugslabs en raffinaderijen.\n• 💼 Witwassers: Maximaliseren de contante uitbetaling bij geldtransporten en drukkerijen.\n• 🛡️ Huurlingen: Verhogen de bende-verdediging en beschermen je faciliteiten tegen invallen van rivalen.\n• 💰 Eerlijke Dynamische Kosten: Inhuurkosten schalen mee met de basisopbrengst van het pand (35% voor Dimitri (+100% Cash), 25% voor Dr. Walter (-50% Productietijd) en 15% voor Boris (Lab Verdediging)), zodat inhuren altijd winstgevend is.\n• ⏱️ Live Countdown: Een duidelijke timer toont exact wanneer het contract afloopt.",
                        'links' => [
                            ['text' => 'Bende Panden & Specialisten', 'url' => route('gangs.panden')],
                        ],
                    ],
                    [
                        'q' => 'Hoe werken 24-Uurs Bendeoorlogen om Kliniek Zones, Heerschappij en Wraakrecht?',
                        'a' => "💡 IN HET KORT: Strijd 24 uur met je hele bende om de macht over strategische zones (Kantine, Luchtplaats, etc.) voor een gigantische +25% bonus op het bende-uurloon!\n\n📋 ZO WERKEN ZONE-OORLOGEN:\n• ⚔️ 24 Uur Strijd: Val leden van de rivaliserende bende aan om gevechtspunten te verzamelen voor jouw organisatie.\n• 👑 Heerschappij Bonus: De winnende bende heerst over de zone en incasseert 7 dagen lang een extra +25% heerschappij-bonus over het totale bende-uurloon!\n• 🩸 Bende Wraakrecht (Retaliation): Valt een rivaal een lid van jouw bende aan? Dan krijgt jouw hele bende 24 uur lang wraakrecht waarbij normale level-limieten vervallen!",
                        'links' => [
                            ['text' => 'Naar Bendes', 'url' => route('gangs.index')],
                        ],
                    ],
                    [
                        'q' => 'Hoe werken de 5 Controlepunten en hoe verdien ik daar elke minuut geld + gratis Sterren mee?',
                        'a' => "💡 IN HET KORT: Verover een van de 5 Controlepunten direct op de locatiepagina zelf (Luchtplaats, Kantine, Sportzaal, Depot of Medicijnkluis) voor geld per minuut én +1 gratis Ster ⭐ na 30 minuten!\n\n📋 ZO WERKT HET:\n• Ga naar de Luchtplaats, Kantine (Winkel), Sportzaal (Gym), Het Depot of de Medicijnkluis en klik bovenaan op 'Controlepunt Overnemen'.\n• Zolang jouw naam op het punt staat, tikt de geldteller elke minuut voor jou door.\n• Houd je het punt minstens 30 minuten vast voordat iemand anders het overneemt? Dan krijg je bovenop je geld ook nog +1 gratis Gouden Ster ⭐ uitbetaald!",
                        'links' => [
                            ['text' => 'Luchtplaats Controlepunt', 'url' => route('fight.index')],
                            ['text' => 'Medicijnkluis Controlepunt', 'url' => route('vault.index')],
                        ],
                    ],
                ],
            ],
            'bonussen' => [
                'title' => '🎁 Bonussen, Kluiskraak & Battle Pass',
                'icon' => 'fa-vault',
                'questions' => [
                    [
                        'q' => 'Hoe werken het Rad van Fortuin, de 22:00 Inlog Bonus en de Battle Pass?',
                        'a' => "💡 IN HET KORT: Elke dag dat je inlogt liggen er gratis draaibeurten, kluiscodes en seizoensprijzen voor je klaar.\n\n📋 VERGEET DEZE 3 GRATIS BONUSSEN NIET:\n1. 🎰 Rad van Fortuin ('/rad-van-fortuin'): Elke 24 uur 1x gratis draaien (VIP mag 2x per dag draaien).\n2. 🕙 Avond Inlog Bonus (22:00 - 22:30 uur): Log je tussen 22:00 en 22:30 uur in? Dan krijg je 3 gratis kluiscodes én bouw je jouw 7-daagse streak op!\n3. 🎫 Battle Pass ('/seizoen-1/battle-pass'): Verdien tijdens het spelen automatisch punten en claim gratis geld, koffers en sterren.",
                        'links' => [
                            ['text' => 'Rad van Fortuin', 'url' => route('daily.index')],
                            ['text' => 'Battle Pass', 'url' => route('battlepass.index')],
                        ],
                    ],
                    [
                        'q' => 'Hoe kraak ik de Centrale Medicijnkluis zonder dat het alarm afgaat?',
                        'a' => "💡 IN HET KORT: Raad de 3-cijferige code met je KluisMunten 🔑 en 'hoger/lager' tips, of wissel geheime Kluiscodes/Vouchers in voor gratis bankgeld en Sterren!\n\n📋 SLIM KLUIS KRAKEN ('/kluis-kraak'):\n• Na elke poging vertelt het spel of de geheime 3-cijferige code hoger of lager is (beloning: €650–€2.800 cash + 1–2 KluisMunten + 175 XP).\n• 🔥 PRO-TIP: Koop eerst een 'Alarm Jammer' op de kluispagina zodat het alarm niet afgaat bij een foute gok!\n• Heb je een geheime Kluiscode of Voucher (bijv. uit de 22:00 bonus, 24u Battle of Discord)? Wissel die hier in voor bankgeld, Gouden Sterren en Bivakmutsen!",
                        'links' => [
                            ['text' => 'Naar MedicijnKluis', 'url' => route('vault.index')],
                        ],
                    ],
                ],
            ],
            'competitie' => [
                'title' => '🏆 Ranglijsten & 24 Uur Battle',
                'icon' => 'fa-trophy',
                'questions' => [
                    [
                        'q' => 'Hoe win ik elke dag gratis Sterren, Kluiscodes en XXL Koffers met de 24 Uur Battle?',
                        'a' => "💡 IN HET KORT: Elke dag strijden alle spelers in 7 categorieën; wie om 00:00 uur in de top 3 staat, wint gratis Gouden Sterren ⭐, Kluiscodes 🔐, XXL Smokkelkoffers en Bivakmutsen!\n\n📋 DE 7 CATEGORIEËN ('/24-uur-battle'):\n1. 🥊 Afdelingsslachting (Meeste Gevechten gewonnen);\n2. 🚬 Kliniek Hosselaars (Meeste Hossels geslaagd);\n3. 👊 Afpersings-Syndicaat (Meeste Afpersingen geslaagd);\n4. ⛓️ Uitbraak-Experts (Meeste Gevangenen bevrijd);\n5. 💊 Pillen-Productie (Meeste Contrabande geproduceerd);\n6. 💵 Contrabande-Afzet (Meeste Contrabande verhandeld);\n7. 🎯 Genadeloze Liquidaties (Meeste Doelwitten geliquideerd).\n\n🔥 PRO-TIP: Elke categorie keert dagelijks 10 Sterren ⭐, 10 Kluiscodes 🔐, 10 XXL Koffers en 10 Bivakmutsen uit aan de Top 3 (verdeling 5 / 3 / 2)!",
                        'links' => [
                            ['text' => '24 Uur Battle Stand', 'url' => route('battle.index')],
                        ],
                    ],
                ],
            ],
            'vip_sponsor' => [
                'title' => '👑 VIP Lidmaatschap & Sponsorkamer (100% Gratis!)',
                'icon' => 'fa-crown',
                'questions' => [
                    [
                        'q' => 'Hoe word ik 100% GRATIS VIP en welke voordelen krijg ik allemaal?',
                        'a' => "💡 IN HET KORT: Je hoeft in TBS Kliniek nooit echt geld uit te geven; je verdient gratis VIP via Discord (3 dagen) en de Sponsorkamer (24 uur per 250 korte video's)!\n\n📋 ZO PAK JE GRATIS VIP:\n• 🎮 Koppel je Discord ('/discord/verifieer'): Direct 3 DAGEN GRATIS VIP + €2.500.\n• ✅ Rookie Taak op Dashboard: Kijk 1 sponsorvideo en claim direct 1 DAG GRATIS VIP.\n• 📺 Sponsorkamer ('/sponsorkamer'): Elke 15-seconden video geeft meteen €2.500, kogels of celstraf-korting, en bij 250 video's krijg je steeds opnieuw 24 UUR GRATIS VIP!\n\n📋 ALLE VIP VOORDELEN OP EEN RIJ:\n• ⚡ Sneller energieherstel (elke 3 min i.p.v. 5 min);\n• 👑 5 VIP Punten per actief speeluur voor de VIP Shop;\n• 🎰 2x per dag gratis draaien aan het Rad van Fortuin;\n• 🥊 1.000 gevechten per dag i.p.v. 100;\n• 🗺️ 50% korting op reizen tussen afdelingen;\n• 🏦 Slechts 3% bankbelasting i.p.v. 5%;\n• 🎁 Hoger gratis zakgeld per kwartier (vanaf €2.500 tot €25.000 per 15 min).",
                        'links' => [
                            ['text' => 'Naar Sponsorkamer', 'url' => route('sponsor.index')],
                            ['text' => 'VIP Shop & Status', 'url' => route('vip.index')],
                        ],
                    ],
                    [
                        'q' => 'Zijn donaties via Buy Me a Coffee verplicht?',
                        'a' => "💡 IN HET KORT: Nee, nooit! TBS Kliniek is een 100% hobbyproject zonder winstoogmerk.\n\n📋 HOE ZIT HET?\n• Alles in het spel is gratis te bereiken.\n• Wie vrijwillig een koffiedonatie doet via Buy Me a Coffee helpt mee om de maandelijkse server- en hostingkosten te betalen en krijgt als bedankje extra supporter-voordelen.",
                    ],
                ],
            ],
            'sociaal' => [
                'title' => '📰 Krant, Chat, Cinema & Vrienden',
                'icon' => 'fa-compass',
                'questions' => [
                    [
                        'q' => 'Hoe werken De Krant, de Tijdlijn en de Cinema?',
                        'a' => "💡 IN HET KORT: Lees wie er vandaag is gearresteerd en kijk samen video's in de bioscoop!\n\n📋 GEZELLIGHEID IN DE KLINIEK:\n• 📰 Het Afdelingsblad ('/afdelingsblad'): De dagelijkse krant met alle grote overvallen en loterijwinnaars.\n• 📱 De Tijdlijn ('/tijdlijn'): Deel berichten en like updates van medepatiënten.\n• 🍿 Cinema ('/cinema'): Kijk samen YouTube-video's in de virtuele filmzaal en chat live mee.",
                        'links' => [
                            ['text' => 'De Krant', 'url' => route('newspaper.index')],
                            ['text' => 'Tijdlijn', 'url' => route('timeline.index')],
                            ['text' => 'Cinema', 'url' => route('cinema.index')],
                        ],
                    ],
                    [
                        'q' => 'Wat is het BFF-hartje bij Vrienden en hoe werkt Zaalwacht Arjan in de Chat?',
                        'a' => "💡 IN HET KORT: Met een BFF (Beste Vriend) breek je elkaar sneller uit de cel, en in de chat rechtsonder beantwoordt Zaalwacht Arjan al je vragen!\n\n📋 HANDIG OM TE WETEN:\n• 💛 BFF Kiezen ('/vrienden'): Voeg vrienden toe en kies 1 speler als jouw BFF voor een gouden hartje en snellere uitbraakhulp.\n• 🤖 Zaalwacht Arjan: Klik rechtsonder op de chatballon om met andere spelers te praten óf om direct een vraag te stellen aan onze slimme hulpbot Arjan.",
                        'links' => [
                            ['text' => 'Mijn Vriendenlijst', 'url' => route('friends.index')],
                        ],
                    ],
                ],
            ],
            'account' => [
                'title' => '⚙️ Account, Regels & Beveiliging',
                'icon' => 'fa-gear',
                'questions' => [
                    [
                        'q' => 'Hoe pas ik mijn profiel, naam, kleurenthema en Dark Mode aan?',
                        'a' => "💡 IN HET KORT: Op je profiel kies je een eigen foto, profielkader en 1 van de 7 kleuren; rechtsboven zet je Dark Mode aan.\n\n📋 AANPASSEN:\n• 🎨 Profiel & Kleur ('/profiel'): Kies uit Paars, Blauw, Groen, Rood, Amber, Roze of Cyaan.\n• 🖼️ Profielkaders ('/profielkaders'): Zet een stoere rand om je profielfoto.\n• 🌙 Dark / Light Mode: Klik op het zonnetje of maantje rechtsboven.\n• ✏️ Naam & Wachtwoord ('/instellingen/account'): Je naam kun je 1x per 6 maanden gratis aanpassen. Ook kun je hier met 1 klik op 'Overal Uitloggen' klikken.",
                        'links' => [
                            ['text' => 'Mijn Profiel', 'url' => route('profile.me')],
                            ['text' => 'Account Instellingen', 'url' => route('settings.account')],
                        ],
                    ],
                    [
                        'q' => 'Wat zijn de regels over meerdere accounts, familieleden en VPN?',
                        'a' => "💡 IN HET KORT: Je mag maximaal 1 account per persoon hebben, maximaal 2 accounts per huisadres (voor familie), en VPN is verboden.\n\n📋 DE 3 GOUDEN SPELREGELS:\n1. 👤 1 Account per persoon: Zelf op meerdere accounts spelen (multi-accounting) zorgt voor een directe blokkade.\n2. 🏠 Max 2 familieleden op hetzelfde internet (IP): Speel je met je broer, zus of partner thuis? Dat mag tot 2 personen. Zijn jullie thuis met 3 of meer? Stuur dan eerst even een Support Ticket.\n3. 🚫 Geen VPN of Proxy: Speel altijd op je gewone eigen internetverbinding.",
                    ],
                    [
                        'q' => 'Hoe meld ik een vervelende speler of maak ik een Support Ticket aan?',
                        'a' => "💡 IN HET KORT: Gebruik het rode vlaggetje bij berichten om schelden te melden, of maak een Support Ticket aan voor hulp van het team.\n\n📋 HULP NODIG?\n• 🚩 Rode Vlaggetje: Zie je iemand schelden of valsspelen in de chat of op een profiel? Klik op het vlaggetje om dit direct bij de moderators te melden.\n• 🎫 Support Ticket ('/help/tickets/nieuw'): Voor al je vragen, bugs of je Gang Overstap Bonus.",
                        'links' => [
                            ['text' => 'Nieuw Ticket Aanmaken', 'url' => route('help.tickets.create')],
                        ],
                    ],
                ],
            ],
            'begrippenlijst' => [
                'title' => '📚 Simpel Woordenboek (Alle Termen Uitgelegd)',
                'icon' => 'fa-book-atlas',
                'questions' => [
                    [
                        'q' => 'Wat betekenen alle game-termen zoals HP, XP, Cooldown, Heist en Ricochet?',
                        'a' => "💡 IN HET KORT: Hier staan alle veelgebruikte woorden uit de kliniek op een rij in gewone mensentaal.\n\n📋 HET SIMPELE WOORDENBOEK:\n• ❤️ HP (Gezondheid): Je levensbalk van 0 tot 100.\n• 📊 XP (Ervaring): Punten waarmee je een Level omhoog gaat.\n• ⏱️ Cooldown: De korte wachttijd voordat je dezelfde knop weer mag indrukken.\n• 🕛 Reset (00:00 uur): Om middernacht beginnen alle dagtellers en de 24 Uur Battle weer opnieuw.\n• 🎭 Heist: Een grote geplande overval met een vluchtauto en een bivakmuts.\n• 🎯 Liquidatie: Op iemand schieten met kogels (+25% Sluipaanval-bonus op dezelfde afdeling).\n• 🛡️ Backfire / Ricochet: Een speciaal vest waardoor een kogel terugketst op de schutter.\n• 💉 Code Blauw: Wanneer je 10 keer bent neergeschoten en een Forensisch Reanimatie-Serum nodig hebt.\n• 👑 BFF: Jouw Beste Vriend in het spel die jou sneller uit de isoleercel kan breken.",
                    ],
                ],
            ],
        ];
    }

    /**
     * Get rich, highly indexed knowledge base specifically for the AI Helpbot widget (Zaalwacht Arjan).
     * Includes extensive keywords, synonyms, newb-friendly explanations, follow-up suggestions, and direct navigation links.
     */
    public static function getBotKnowledge(): array
    {
        return [
            [
                'title' => '🗺️ Beginnersgids: Stappenplan van Start tot Einde',
                'keywords' => ['beginnen', 'nieuw', 'start', 'begin', 'starten', 'uitleg', 'eerste', 'stappen', 'tips', 'beginner', 'overleven', 'stappenplan', 'einde', 'doel', 'gids'],
                'answer' => "💡 IN HET KORT: Welkom in FPC De Balk! Volg deze 5 simpele stappen om direct veilig rijk en sterk te worden:\n\n1. 🏥 Ziekenboeg: Zuster Ingrid verzorgt je 100% gratis (+45 HP per beurt).\n2. 🎡 Rad van Fortuin: Draai elke dag gratis voor cash, energie of sterren.\n3. 🏦 De Bank (GOUDEN REGEL): Stort AL je contant geld direct op de bank zodat niemand je berooft!\n4. 🎯 Rookie Taken: Voltooi de 5 startopdrachten op je Dashboard voor €6.500 cash, 30 kogels, 145 XP en 1 dag gratis VIP.\n5. 🔨 Dagbesteding: Werk veilig bij Werkmeester Henk (0% kans op de isoleercel!).\n\n🔥 PRO-TIP: Klik op '🎯 Advies op Maat' onderin de chat voor een persoonlijke check van jouw karakter!",
                'links' => [
                    ['text' => '🎯 Naar Dashboard', 'url' => route('dashboard')],
                    ['text' => '🏦 Naar De Bank', 'url' => route('bank.index')],
                    ['text' => '🔨 Dagbesteding', 'url' => route('daycare.index')],
                ],
                'suggestions' => [
                    'Hoe pak ik €35.000 gratis startgeld?',
                    'Hoe verdien ik snel geld?',
                    'Hoe beveilig ik mijn contante geld op de bank?',
                ],
            ],
            [
                'title' => '🎁 €35.000+ Gratis Startgeld op Dag 1',
                'keywords' => ['startgeld', 'rookie', 'taken', '35000', '35k', 'push', 'meldingen', 'notificaties', 'welkomstbonus', 'startersbonus', 'rondleiding', 'pak', 'intake', 'tour', 'intakegesprek'],
                'answer' => "💡 IN HET KORT: Op je eerste dag ligt er meer dan €35.000 aan gratis geld, 30 kogels en 4 dagen gratis VIP voor je klaar!\n\n📋 ZO PAK JE ALLE STARTERSBONUSSEN:\n• 🧭 Intake Rondleiding (FAQ): Geeft direct €1.000 cash.\n• 🎯 5 Rookie Taken (Dashboard): Geeft in totaal €6.500 cash (€1.500 + €5.000 eindprijs) + 30 kogels + 145 XP + 5 sigaretten + 1 dag gratis VIP.\n• 🔔 Push-Meldingen aanzetten (Dashboard): Geeft éénmalig €25.000 cash + 25 Respect + 20 Energie!\n• 🎮 Discord koppelen: Geeft direct €2.500 cash + 3 dagen gratis VIP!\n\n🔥 PRO-TIP: Stort na elke beloning je cash meteen gratis op De Bank!",
                'links' => [
                    ['text' => '🎯 Rookie Taken & Push Bonus', 'url' => route('dashboard')],
                    ['text' => '🎮 Discord Koppelen (+3d VIP)', 'url' => route('discord.verify')],
                    ['text' => '🏦 Storten op De Bank', 'url' => route('bank.index')],
                ],
                'suggestions' => [
                    'Hoe werkt het Behandelaarsgesprek en 3u Verlof?',
                    'Hoe verdien ik 100% gratis VIP?',
                    'Hoe beveilig ik mijn contante geld op de bank?',
                ],
            ],
            [
                'title' => '💰 Snel Geld Verdienen (Overzicht van Beginner tot Pro)',
                'keywords' => ['snel', 'geld', 'verdienen', 'verdien', 'rijk', 'inkomen', 'poen', 'cash', 'buit', 'manieren', 'miljonair'],
                'answer' => "💡 IN HET KORT: Zo verdien je op elk niveau het snelste geld in de kliniek:\n\n1. 🎁 Dag 1 Bonussen (€35.000+): Doe je 5 Rookie Taken, zet Push-meldingen aan (€25.000) en koppel Discord (€2.500).\n2. 🔨 Veilige Dagbesteding (€150 - €5.000): Werk bij Werkmeester Henk met 0% kans op de isoleercel!\n3. 🧠 Behandelaarsgesprek (elke 2 uur): Scoor 60%+ bij Dr. Vink voor €2.500 cash + 3 uur Begeleid Verlof (tot €17.500+ extra uit buiten-missies).\n4. 🚬 Hosselen & Smokkelen: Doe klussen tot €65.000 of verhandel Sigaretten, Ritalin, Haloperidol en Wiet.\n5. 🎭 Grote Heists & Bendes: Pleeg overvallen met een vluchtauto en ontvang elk uur automatisch Bende-Uurloon!\n\n🔥 PRO-TIP: Zet je verdiende geld altijd direct op De Bank!",
                'links' => [
                    ['text' => '🔨 Veilig Werken', 'url' => route('daycare.index')],
                    ['text' => '🚬 Naar Hosselen', 'url' => route('hustle.index')],
                    ['text' => '🧠 Behandelaarsgesprek', 'url' => route('treatment.index')],
                ],
                'suggestions' => [
                    'Hoe pak ik €35.000 gratis startgeld?',
                    'Hoe werkt veilig werken in de Dagbesteding?',
                    'Hoe werken Grote Heists en vluchtauto\'s?',
                ],
            ],
            [
                'title' => '⚡ Energie & Snel Herstellen',
                'keywords' => ['energie', 'energy', 'herstellen', 'herstel', 'leeg', 'weinig', 'snack', 'snacks', 'eten', 'chocola', 'kantine', 'refill', 'moe', 'stroom'],
                'answer' => "💡 IN HET KORT: Energie heb je nodig voor werken, misdaden, trainen en vechten. Het vult vanzelf aan én via snacks.\n\n📋 ZO KRIJG JE ENERGIE TERUG:\n• ⏱️ Automatisch: +1 Energie per 5 minuten (+1 per 3 min voor VIP, tot +1 per 2 min voor hogere VIP tiers).\n• 🍫 Kantine Snacks: Koop en gebruik eten in de Kliniek Winkel (onder je Inventaris-tabblad).\n• 🎡 Rad van Fortuin & Push-Bonus: Gratis kans op directe energie.\n• ⭐ Sterren / VIP Shop: Koop een directe 100% Energie + HP Refill (kost 10 VIP Punten of Sterren).\n\n🔥 PRO-TIP: Laat je energie nooit urenlang op 100/100 vol staan; geef het uit in de Werkplaats of het Krachthonk!",
                'links' => [
                    ['text' => '🍫 Kantine & Inventaris', 'url' => route('shop.index', ['tab' => 'inventaris'])],
                    ['text' => '🎡 Rad van Fortuin', 'url' => route('daily.index')],
                    ['text' => '👑 VIP Shop', 'url' => route('vip.index')],
                ],
                'suggestions' => [
                    'Hoe genees ik gratis mijn gezondheid (HP)?',
                    'Hoe werkt veilig werken in de Dagbesteding?',
                    'Hoe train ik mijn Kracht en Verdediging?',
                ],
            ],
            [
                'title' => '❤️ Gezondheid (HP), Pillen Palmen & Ziekenboeg',
                'keywords' => ['gezondheid', 'health', 'hp', 'levens', 'dood', 'gewond', 'genezen', 'healen', 'ziekenboeg', 'dokter', 'zuster', 'ingrid', 'brenda', 'verband', 'ehbo', 'pillen', 'palmen'],
                'answer' => "💡 IN HET KORT: Is je Gezondheid (HP) laag? Zuster Ingrid in de Ziekenboeg verzorgt je 100% GRATIS!\n\n📋 WAT KUN JE DOEN IN DE ZIEKENBOEG?\n• 💖 Gratis Verpleging: Klik bij Zuster Ingrid op behandelen voor direct +45 HP (elke 4 minuten gratis).\n• 💊 Pillen Palmen (10 Energie): Smokkel stiekem een Kalmeringspil, Ritalin of Haloperidol Forte uit de medicijnkar (+50 XP & +4 Respect, 65%+ slaagkans).\n• 🩹 Verbanddozen: In de Kliniek Winkel koop je EHBO-kits voor direct herstel vanuit je inventaris.\n\n🔥 PRO-TIP: Als je op de Luchtplaats naar 0 HP zakt, raak je je account of spullen nooit kwijt — ga gewoon even langs Zuster Ingrid!",
                'links' => [
                    ['text' => '🏥 Gratis Genezen bij Ingrid', 'url' => route('infirmary.index')],
                    ['text' => '🛒 EHBO & Winkel', 'url' => route('shop.index')],
                ],
                'suggestions' => [
                    'Wat is Code Blauw en een Reanimatie-Serum?',
                    'Hoe bescherm ik mezelf tegen aanvallen?',
                    'Hoe werkt vechten en de respectlijst?',
                ],
            ],
            [
                'title' => '🏦 De Bank, Gratis Zakgeld & Leningen',
                'keywords' => ['bank', 'storten', 'sparen', 'opnemen', 'rente', 'beroofd', 'gestolen', 'kwijt', 'contant', 'contante', 'lening', 'lenen', 'zakgeld', 'beveiligen', 'overmaken', 'pincode'],
                'answer' => "💡 IN HET KORT: Bewaar NOOIT contant geld op zak! Op De Bank is je geld veilig tegen straatroof, zakkenrollers en de Luchtplaats.\n\n📋 ALLE VOORDELEN VAN DE BANK:\n• 🔒 Gratis & Onbeperkt Storten: Storten op de bank is altijd 100% gratis en onbeperkt (let op: vanuit de isoleercel kun je niet pinnen of storten, dus stort altijd vooraf!).\n• 💶 Gratis Zakgeld (Zonder Bende): Zit je niet in een bende? Dan krijg je elke 15 minuten automatisch €1.000 gratis op je bank (tot €25.000 per 15 min voor VIP)!\n• 💳 Leningen (vanaf Level 1): Leen €10.000 tot €50.000 × jouw Level tegen 8,5% rente (10% automatische aflossing uit je inkomsten).\n\n🔥 PRO-TIP: Maak er een gewoonte van om na elke klus meteen op 'Alles Storten' te klikken.",
                'links' => [
                    ['text' => '🏦 Naar De Bank', 'url' => route('bank.index')],
                ],
                'suggestions' => [
                    'Hoe verdien ik snel geld?',
                    'Hoe werken Bendes en het Bende-Uurloon?',
                    'Hoe bescherm ik mezelf tegen aanvallen?',
                ],
            ],
            [
                'title' => '⛓️ Isoleercel & 6 Manieren om Snel te Ontsnappen',
                'keywords' => ['isoleercel', 'gevangenis', 'bajes', 'cel', 'vast', 'straf', 'straftijd', 'gepakt', 'betrapt', 'vrijkopen', 'ontsnappen', 'uitbreken', 'forceren', 'ijzerzaagje', 'luchtschacht', 'sneller'],
                'answer' => "💡 IN HET KORT: Zit je vast na een mislukte misdaad? Je hoeft niet te wachten tot de klok op 0 staat!\n\n📋 6 MANIEREN OM SNELLER VRIJ TE KOMEN:\n1. 🌀 Luchtschacht-Uitbraak: Kost 20 energie; bij succes ben je meteen vrij (+15 Respect & +35 XP).\n2. 🪚 IJzerzaagje: Haalt direct 30 minuten van je straf af.\n3. 🧺 Stoomwasserij: Werk in je cel (10 energie) om elke keer 45 seconden straf weg te spelen.\n4. 💵 Nachtcipier Omkopen: Betaal €2.500 voor 75% kans op 90 seconden strafvermindering.\n5. ⭐ Direct Vrijkopen met Sterren: Koop jezelf direct vrij met Sterren ⭐ (10 ⭐ via de Sterrenwinkel of 25 ⭐ in de cel).\n6. 🤝 Vrienden / BFF: Laat een vrije medepatiënt of je BFF je celdeur van buitenaf openbreken!",
                'links' => [
                    ['text' => '⛓️ Naar Isoleercel', 'url' => route('prison')],
                    ['text' => '⭐ Sterrenwinkel (10⭐ Vrij)', 'url' => route('shop.index')],
                ],
                'suggestions' => [
                    'Hoe kom ik aan een Bivakmuts (masker)?',
                    'Wat doet een BFF (Best Friend Forever)?',
                    'Hoe werkt veilig werken in de Dagbesteding?',
                ],
            ],
            [
                'title' => '🥊 Respectlijst, Luchtplaats & Gevechtsregels',
                'keywords' => ['respectlijst', 'vechten', 'luchtplaats', 'slaylist', 'slay', 'duel', 'arena', 'showdown', 'respect', 'slaan', 'winnen', 'gevecht'],
                'answer' => "💡 IN HET KORT: Op de Luchtplaats vecht je tegen andere spelers om hun Respect en 10% tot 25% van hun contante zakgeld te roven!\n\n📋 ZO WIN JE GEVECHTEN:\n• ⚔️ Jouw Gevechtskracht: Je totale Kracht (inclusief passieve wapenbonus, bende-bonus en buffs) strijdt tegen de Verdediging (inclusief pantser) van je tegenstander.\n• 🔢 Aanvalslimiet: Gratis spelers mogen 100x per dag aanvallen, VIPs 1.000x per dag.\n• 🧮 Gevecht Simulator: Op de FAQ-pagina kun je vooraf precies je winstkans berekenen!\n\n🔥 PRO-TIP: Val spelers aan die veel contant geld op zak hebben én een lagere verdediging dan jouw kracht!",
                'links' => [
                    ['text' => '🥊 Naar Luchtplaats', 'url' => route('fight.index')],
                    ['text' => '🏋️ Trainen in Krachthonk', 'url' => route('training.index')],
                    ['text' => '🛒 Wapens & Pantsers', 'url' => route('shop.index')],
                ],
                'suggestions' => [
                    'Hoe train ik mijn Kracht en Verdediging?',
                    'Hoe bescherm ik mezelf tegen aanvallen?',
                    'Hoe werkt Liquidatie 2.0 met kogels?',
                ],
            ],
            [
                'title' => '🛡️ Vrijwillige Isolatie & Bescherming tegen Aanvallen',
                'keywords' => ['vrijwillige', 'isolatie', 'bescherming', 'beschermen', 'bunker', 'veiligheid', 'aangevallen', 'voorkom', 'voorkomen', 'stop', 'shield', 'pester'],
                'answer' => "💡 IN HET KORT: Word je steeds aangevallen? Gebruik De Bank, Vrijwillige Isolatie of 24u Cash Bescherming!\n\n📋 3 MANIEREN OM JEZELF TE BESCHERMEN:\n1. 🏦 Alles op De Bank: Wie €0 contant op zak heeft, levert aanvallers op de Luchtplaats geen euro op.\n2. 🛡️ Vrijwillige Isolatie: Zet dit gratis aan onder 'Geweld & Strijd > Vrijwillige Isolatie'. Zolang je in de bunker zit, kan niemand op de Luchtplaats je aanvallen!\n3. 👑 24u Cash Bescherming (VIP Shop): Beschermt je 24 uur lang tegen geldroof (zowel contant als bankgeld) bij Liquidatie 2.0!\n\n🔥 PRO-TIP: Je kunt ook Kopgeld op het hoofd van je aanvaller zetten zodat sterke spelers hem aanpakken.",
                'links' => [
                    ['text' => '🛡️ Vrijwillige Isolatie', 'url' => route('isolation.index')],
                    ['text' => '🏦 Naar De Bank', 'url' => route('bank.index')],
                    ['text' => '👑 24u Cash Bescherming', 'url' => route('vip.index')],
                ],
                'suggestions' => [
                    'Wat doet een Backfire-vest tegen kogels?',
                    'Hoe train ik mijn Kracht en Verdediging?',
                    'Hoe werkt veilig werken in de Dagbesteding?',
                ],
            ],
            [
                'title' => '🏋️ Krachthonk (Gym): Kracht, Verdediging, Conditie & Discipline',
                'keywords' => ['trainen', 'training', 'krachthonk', 'sportschool', 'gym', 'kracht', 'verdediging', 'conditie', 'intelligentie', 'charisma', 'discipline', 'sterker', 'stats', 'statistieken'],
                'answer' => "💡 IN HET KORT: In het Krachthonk zet je 10 Energie (+ een klein bedrag aan cash) om in blijvende eigenschappen!\n\n📋 DE 6 EIGENSCHAPPEN IN DE GYM:\n• 💪 Kracht: Je deelt veel hardere klappen uit op de Luchtplaats.\n• 🛡️ Verdediging: Je vangt klappen op en verliest minder snel HP.\n• 🏃 Conditie: Vergroot je fysieke uithoudingsvermogen.\n• 🧠 Intelligentie: Verhoogt je slaagkans bij o.a. Pillen Palmen en Hacking.\n• 🗣️ Charisma: Geeft tot +35% extra geldbonus bij Hosselen!\n• 📋 Discipline: Verhoogt je slaagkans bij het Behandelaarsgesprek (ook te verdienen bij Dagbesteding).\n\n🔥 PRO-TIP: Koop ook een Wapen en Pantser in de winkel; die werken direct automatisch na aankoop!",
                'links' => [
                    ['text' => '🏋️ Naar Krachthonk', 'url' => route('training.index')],
                    ['text' => '🛒 Wapens & Pantsers', 'url' => route('shop.index')],
                ],
                'suggestions' => [
                    'Hoe werkt vechten en de respectlijst?',
                    'Hoe krijg ik energie?',
                    'Wat zijn 24-uurs Perks (Boosters)?',
                ],
            ],
            [
                'title' => '🔨 Veilige Dagbesteding & Werkplaats (0% Celkans)',
                'keywords' => ['dagbesteding', 'werkplaats', 'werken', 'werk', 'henk', 'werkmeester', 'loon', 'salaris', 'houtbewerking', 'wasserette', 'wasgoed', 'celkans'],
                'answer' => "💡 IN HET KORT: Bij Werkmeester Henk in de Dagbesteding verdien je 100% veilig geld zonder ooit in de isoleercel te belanden!\n\n📋 WAAROM WERKEN IN DE WERKPLAATS?\n• ✅ 0% kans op straf of de isoleercel.\n• 💶 5 Klussen (3 tot 18 Energie): Verdien altijd gegarandeerd €150 tot €5.000 contant loon + XP.\n• 📈 50% kans op +1 gratis statpunt (Discipline, Conditie, Kracht of Intelligentie) bij elke klus!\n\n🔥 PRO-TIP: Ideaal voor beginners én wanneer je even zonder risico wilt sparen.",
                'links' => [
                    ['text' => '🔨 Naar Dagbesteding', 'url' => route('daycare.index')],
                ],
                'suggestions' => [
                    'Hoe werkt het Behandelaarsgesprek en 3u Verlof?',
                    'Hoe kraak ik de Centrale Medicijnkluis?',
                    'Hoe werkt Hosselen en Zakkenrollen?',
                ],
            ],
            [
                'title' => '🧠 Behandelaarsgesprek (Elke 2 Uur) & 3 Uur Begeleid Verlof',
                'keywords' => ['behandelaar', 'behandelaarsgesprek', 'gesprek', 'verlof', 'psychiater', 'vink', 'sedatie', 'buiten', 'supermarkt', 'station', 'bagagekluisjes', 'dilemma'],
                'answer' => "💡 IN HET KORT: Beantwoord elke 2 uur 3 vragen van psychiater Dr. Vink op een rustige manier voor €2.500 cash + 3 uur Begeleid Verlof!\n\n📋 ZO WERKT HET:\n1. 🗣️ Kies bij de 3 vragen altijd het beleefde, meewerkende en rustige antwoord.\n2. 🎫 Scoor je 60% of hoger? Dan krijg je direct €2.500 cash, 120 XP én 3 UUR BEGELEID VERLOF!\n3. 🌆 Tijdens je verlof doe je 3 buiten-missies:\n   • Supermarkt Plunderen (12⚡): €2.500–€7.500 cash;\n   • Station Oplichting (16⚡): €1.500–€4.000 cash + 25% kans op 1 Gouden Ster ⭐;\n   • Bagagekluisjes (15⚡): €3.000–€6.000 cash + 50% kans op een geheime Kluiscode of Smokkelkoffer!\n\n🔥 PRO-TIP: Scoor niet onder de 35%, anders krijg je 60 minuten Sedatie en -30 Energie.",
                'links' => [
                    ['text' => '🧠 Naar Behandelaarsgesprek', 'url' => route('treatment.index')],
                ],
                'suggestions' => [
                    'Hoe werkt Koffers Onderscheppen en een XXL Koffer?',
                    'Hoe pak ik €35.000 gratis startgeld?',
                    'Hoe graaf ik de Uitbraaktunnel voor +25 Sterren?',
                ],
            ],
            [
                'title' => '🕵️ Hosselen, Zakkenrollen & Bivakmutsen (Maskers)',
                'keywords' => ['hosselen', 'zakkenrollen', 'bivakmuts', 'bivakmutsen', 'masker', 'maskers', 'skibril', 'gasmasker', 'pakkans', 'klusjes'],
                'answer' => "💡 IN HET KORT: Met Hosselen verdien je tot €65.000 per klus; voor Zakkenrollen en Grote Heists heb je een Bivakmuts 🎭 nodig!\n\n📋 HOE WERKT HET SLIMST?\n• 🚬 Hosselen (6 klussen): Verdien €400 tot €65.000 (+ tot 35% extra via je Charisma).\n• 🧤 Zakkenrollen: Vereist minimaal 1 Bivakmuts 🎭 om onherkenbaar contant geld en items te rollen (dagelijks limiet van 100 diefstallen per slachtoffer, of 1.000 voor VIP-leden).\n• 🥷 Hoe kom je aan Bivakmutsen? Koop ze in de Kliniek Winkel, op de Marktplaats, of win ze via Geheime Kluiscodes en de 24 Uur Battle!",
                'links' => [
                    ['text' => '🚬 Naar Hosselen', 'url' => route('hustle.index')],
                    ['text' => '🧤 Naar Zakkenrollen', 'url' => route('pickpocket.index')],
                    ['text' => '🛒 Bivakmuts Kopen', 'url' => route('shop.index')],
                ],
                'suggestions' => [
                    'Hoe werken Grote Heists en vluchtauto\'s?',
                    'Hoe werkt Smokkelen en 24-uurs Perks?',
                    'Hoe kom ik sneller uit de isoleercel?',
                ],
            ],
            [
                'title' => '🥷 Diefstal, Afpersen, Droppings, Kluisjes & Hacking',
                'keywords' => ['diefstal', 'afpersen', 'droppings', 'tennisbal', 'drone', 'kluisjes', 'personeelskluisjes', 'hacking', 'hacken', 'misdaad', 'misdrijven'],
                'answer' => "💡 IN HET KORT: Naast Hosselen heb je 5 extra illegale acties voor grote buit en contrabande:\n\n• 🥷 Diefstal ('/diefstal'): Kruimel- en zware diefstallen voor contant geld en XP.\n• 👊 Afpersen ('/afpersen'): Dwing doelwitten om jou beschermingsgeld te betalen.\n• 🛸 Tennisbal & Drone Droppings ('/smokkel-droppings'): Vang een Tennisbal op (15⚡) of bestel vanaf Level 3 een Drone (€500 + 25⚡ voor €1.800–€4.800 cash, sigaretten, Ritalin en kans op een Smokkelpakketje).\n• 🗄️ Kluisjes Kraken ('/kluisjes'): Breek kluisjes open met een lockpick.\n• 💻 Hacking ('/hacking'): Kraak het klinieknetwerk (hogere Intelligentie helpt!).",
                'links' => [
                    ['text' => '🥷 Diefstal', 'url' => route('theft.index')],
                    ['text' => '👊 Afpersen', 'url' => route('extort.index')],
                    ['text' => '🛸 Droppings', 'url' => route('droppings.index')],
                    ['text' => '💻 Hacking', 'url' => route('hacking.index')],
                ],
                'suggestions' => [
                    'Hoe werkt Hosselen en Zakkenrollen?',
                    'Hoe werkt Smokkelen en 24-uurs Perks?',
                    'Hoe beveilig ik mijn contante geld op de bank?',
                ],
            ],
            [
                'title' => '💊 Smokkelen, Dealen & 24-Uurs Perks (Boosters)',
                'keywords' => ['smokkelen', 'smokkel', 'dealen', 'wiet', 'nederwiet', 'ritalin', 'haloperidol', 'sigaretten', 'drugs', 'handel', 'koers', 'perks', 'perk', 'booster', 'boosters', 'broekzakken', 'topadvocaat'],
                'answer' => "💡 IN HET KORT: Produceer en verhandel 4 soorten smokkelwaar en zet 24-uurs Sterren-Perks aan voor extra winst!\n\n📋 SMOKKELEN & DE 3 PERKS:\n• 🌿 Smokkelen ('/smokkelen'): Produceer en verhandel Sigaretten, Ritalin, Haloperidol en Nederwiet/Kaswiet Toppen wanneer de koers hoog staat.\n• 👛 Grotere Broekzakken (25 ⭐): 24 uur lang +10% extra cash bij Hosselen en Zakkenrollen.\n• 🔐 Kluis & Opslag Uitbreiding (20 ⭐): 24 uur lang +25 extra opslagplekken.\n• ⚖️ Topadvocaat op Speeddial (40 ⭐): 24 uur lang 25% kortere isoleercel-straftijd!",
                'links' => [
                    ['text' => '💊 Naar Smokkelen', 'url' => route('smuggle.index')],
                    ['text' => '⚡ 24-uurs Perks', 'url' => route('perks.index')],
                ],
                'suggestions' => [
                    'Hoe werkt Verplaatsen (+35 XP per reis)?',
                    'Hoe werken Grote Heists en vluchtauto\'s?',
                    'Hoe beveilig ik mijn contante geld op de bank?',
                ],
            ],
            [
                'title' => '🔐 Centrale MedicijnKluis, Alarm Jammer & Kluiscodes',
                'keywords' => ['kluis', 'medicijnkluis', 'centrale', 'code', 'kluiscode', 'kluiscodes', 'kluismunten', 'raden', 'jammer', 'alarm', 'voucher', 'vouchers', 'promocode'],
                'answer' => "💡 IN HET KORT: Raad de 3-cijferige code van de Centrale Medicijnkluis met KluisMunten 🔑, of wissel geheime Kluiscodes in!\n\n📋 ZO KRAAK JE DE KLUIS:\n1. 📡 Koop eerst een Alarm Jammer zodat je niet in de isoleercel belandt bij een foute gok.\n2. 🔢 Gebruik je KluisMunten 🔑 en volg de Hoger/Lager hints om de 3-cijferige code te kraken (€650–€2.800 cash + 1–2 KluisMunten + 175 XP).\n3. 🎟️ Geheime Kluiscodes / Vouchers (uit de 22:00 bonus, Verlof, 24u Battle of Discord) wissel je hier ook in voor bankgeld, Gouden Sterren en Bivakmutsen!",
                'links' => [
                    ['text' => '🔐 Kluis Kraken & Vouchers', 'url' => route('vault.index')],
                ],
                'suggestions' => [
                    'Hoe graaf ik de Uitbraaktunnel voor +25 Sterren?',
                    'Hoe werkt Koffers Onderscheppen en een XXL Koffer?',
                    'Hoe verdien ik gratis Gouden Sterren?',
                ],
            ],
            [
                'title' => '⛏️ Uitbraaktunnel Graven (0% naar 100% & +25 Sterren)',
                'keywords' => ['tunnel', 'graven', 'uitbraaktunnel', 'eetlepel', 'lepel', 'schep', 'ontsnappingstunnel', 'zand', 'dumpen', 'uitbraak'],
                'answer' => "💡 IN HET KORT: Graaf met een eetlepel onder je celvloer van 0% naar 100% voor een enorme geldbeloning én +25 gratis Gouden Sterren ⭐!\n\n📋 STAP VOOR STAP GRAVEN:\n1. 🥄 Gratis Eetlepel: Bij de start van je tunnel krijg je direct je eerste Eetlepel gratis!\n2. ⛏️ Graven (15 Energie): Elke graafbeurt brengt je 2% tot 5% dichter bij de 100%.\n3. 🪣 Zand Dumpen: Zitten je zakken vol met zand? Dump het zand eerst stiekem buiten voordat je verder graaft!\n4. 🏆 Bij 100% slaagt de Grote Uitbraak: je wint een grote som cash, XP + 25 Gouden Sterren!",
                'links' => [
                    ['text' => '⛏️ Naar Uitbraaktunnel', 'url' => route('tunnel.index')],
                ],
                'suggestions' => [
                    'Hoe verdien ik gratis Gouden Sterren?',
                    'Hoe werkt het Behandelaarsgesprek en 3u Verlof?',
                    'Hoe kraak ik de Centrale Medicijnkluis?',
                ],
            ],
            [
                'title' => '👥 Bendes & Het Vernieuwde Bende-Uurloon',
                'keywords' => ['gang', 'bende', 'bendes', 'uurloon', 'gangs', 'bendekas', 'smokkelkiosk', 'kiosk', 'kiosken', 'salaris', 'loonschaal', 'rang', 'vlootvergoeding', 'heerschappij'],
                'answer' => "💡 IN HET KORT: Als lid van een bende ontvang je elk heel uur automatisch salaris op je bankrekening uit bezittingen en vloot!\n\n📋 DE 5 UURLOON-PIJLERS:\n1. 🏪 Kiosken: Stabiele basis tot max 1.000 kiosken (€50/u per kiosk = max €50.000/u).\n2. 🏎️ Bende Wagenpark: 24/7 passief loon per gestald voertuig (€1.500 motor, €4.000 auto, €12.500 boot, €25.000 helikopter/vliegtuig).\n3. 🏢 Panden & Vastgoed: Vaste uurloonbijdragen plus procentuele bonussen tot +40% per topfaciliteit.\n4. ⭐ Respect Bonus: +1% per 100.000 respect tot +50%.\n5. 👑 Heerschappij Bonus (+25%): 7 dagen lang +25% bonus na het winnen van een 24u bendeoorlog!\n\n💰 Salarisschaal per Rang: Recruut 50%, Lid 100%, Veteraan 110%, Onderbaas 125%, Co-Leader 140%, Leider 150%.",
                'links' => [
                    ['text' => '👥 Naar Bendes', 'url' => route('gangs.index')],
                ],
                'suggestions' => [
                    'Hoe werkt de Bende Garage en wagens doneren?',
                    'Hoe werken Bende Panden en productiecycli?',
                    'Hoe werken 24-Uurs Bendeoorlogen en wraakrecht?',
                ],
            ],
            [
                'title' => '🏎️ Bende Garage, Gratis Revisies & Wagens Doneren (+50 Respect)',
                'keywords' => ['bende garage', 'gestald', 'stallen', 'doneren', 'gedoneerd', 'bende-eigendom', 'slopen', 'reviseren', 'bendekas', 'wagenpark'],
                'answer' => "💡 IN HET KORT: In de Bende Garage bundelt de gang wagens voor heists en producties. Revisies zijn 100% gratis uit de bendekas en wagens doneren geeft +50 Respect!\n\n📋 REGELS VAN DE BENDE GARAGE:\n• 🛠️ Revisies 100% uit Bendekas: Schade wordt uitsluitend betaald uit de bendekas. Jouw privégeld wordt nooit aangeraakt!\n• 🎁 Wagens Doneren (+50 Respect): Schenk een wagen aan de bende. De donateur krijgt direct +50 Respect en de wagen wordt permanent 'Bende Eigendom'.\n• 🚜 Slopen voor Bendekas: Leiders kunnen overtollige bende-eigendom wagens ontmantelen voor direct 25% van de aanschafwaarde terug in de kas.",
                'links' => [
                    ['text' => '🏎️ Naar Bende Garage', 'url' => route('gangs.garage')],
                ],
                'suggestions' => [
                    'Hoe werkt het Bende-Uurloon?',
                    'Hoe werken Bende Panden en productiecycli?',
                    'Hoe werken Grote Bende Heists?',
                ],
            ],
            [
                'title' => '🏭 Bende Panden, Productie & Garage-Vlooteisen',
                'keywords' => ['panden', 'bende panden', 'vastgoed', 'productie', 'faciliteiten', 'citadel', 'vlooteisen', 'vlooteis', 'makelaar', 'showroom'],
                'answer' => "💡 IN HET KORT: Schaf syndicaatpanden aan (€15M tot €1,5 Miljard) om miljoenen cash, duizenden respect en uurloonbonussen te produceren!\n\n📋 PANDEN & PRODUCTIE:\n• 🏢 10 Nieuwe Faciliteiten: Van Drone Smokkelbases tot Kartel Citadels van €1,5 miljard.\n• ⚙️ Productiecycli: Leiders starten opdrachten (2,5 tot 8 uur) die miljoenen zwart geld en duizenden respect opleveren.\n• 🚗 Garage-Vlooteisen: Geavanceerde panden vereisen operationele wagens in de Bende Garage (zoals 3 snelle wagens ≥80 km/u of meerdere helikopters) om te kunnen produceren!\n• 🕒 Roterende Showroom: Wisselt elk half uur over 4 eerlijke prijsklassen.",
                'links' => [
                    ['text' => '🏭 Naar Bende Panden', 'url' => route('gangs.panden')],
                ],
                'suggestions' => [
                    'Wat zijn Bende Specialist NPC\'s?',
                    'Hoe werkt de Bende Garage en wagens doneren?',
                    'Hoe werkt het Bende-Uurloon?',
                ],
            ],
            [
                'title' => '🧑‍🔬 Bende Specialist NPC\'s (30-Minuten Contracten)',
                'keywords' => ['specialist', 'specialisten', 'npc', 'chemici', 'witwasser', 'huurling', 'contract', 'contracten', 'countdown'],
                'answer' => "💡 IN HET KORT: Huur chemici, witwassers en zwaarbewapende bewakers in voor een contract van 30 minuten per productiecyclus!\n\n📋 SPECIALISTEN INZETTEN:\n• 🧪 Chemici: Verhogen de opbrengst en versnellen de productietijd van productielabs.\n• 💼 Witwassers: Maximaliseren de contante winst bij transporten en drukkerijen.\n• 🛡️ Huurlingen: Verhogen bende-verdediging en beschermen faciliteiten tegen invallen.\n• 💰 Dynamische Kosten: Prijzen schalen mee met de pandopbrengst (35% voor Dimitri (+100% Cash), 25% voor Dr. Walter (-50% Tijd) en 15% voor Boris (Verdediging)).\n• ⏱️ Live Countdown: Een realtime timer toont exact wanneer het contract afloopt.",
                'links' => [
                    ['text' => '🧑‍🔬 Bende Specialisten', 'url' => route('gangs.panden')],
                ],
                'suggestions' => [
                    'Hoe werken Bende Panden en productiecycli?',
                    'Hoe werkt het Bende-Uurloon?',
                ],
            ],
            [
                'title' => '⚔️ 24-Uurs Bendeoorlogen om Zones, Heerschappij (+25%) & Wraakrecht',
                'keywords' => ['bendeoorlog', 'bendeoorlogen', 'zone', 'zones', 'heerschappij', 'kantine', 'luchtplaats', 'apotheek', 'wraakrecht', 'retaliation'],
                'answer' => "💡 IN HET KORT: Strijd 24 uur met je bende om heerschappij over kliniekzones voor een gigantische +25% bonus op het bende-uurloon!\n\n📋 ZONE-OORLOGEN & WRAAKRECHT:\n• ⚔️ 24 Uur Gevechten: Val rivaliserende bendeleden aan om gevechtspunten te verzamelen.\n• 👑 Heerschappij Bonus: De winnende bende heerst over de zone en incasseert 7 dagen lang een extra +25% bonus over het totale bende-uurloon!\n• 🩸 Bende Wraakrecht: Wordt een bendelid aangevallen? Dan mag de hele bende 24 uur lang wraak nemen zonder normale level-limieten!",
                'links' => [
                    ['text' => '⚔️ Bende Oorlogen', 'url' => route('gangs.index')],
                ],
                'suggestions' => [
                    'Hoe werkt het Bende-Uurloon?',
                    'Hoe werkt de Bende Garage en wagens doneren?',
                ],
            ],
            [
                'title' => '💎 Grote Bende Co-Op Heists (50/50 Buit & Snelheidseisen)',
                'keywords' => ['bende heist', 'bende heists', 'coop', 'co-op', 'fort goudkluis', 'goudkluis', 'douane', 'depot', '50/50', 'buitverdeling', 'snelheidseis'],
                'answer' => "💡 IN HET KORT: Pleeg grootschalige syndicaatkraken met je bende waarbij de miljoenenbuit 50/50 wordt verdeeld!\n\n📋 REGELS VAN BENDE HEISTS ('/heists'):\n• 💰 50/50 Verdeling: 50% van de buit belandt direct contant in jouw zak, 50% gaat direct in de gezamenlijke bendekas + duizenden respectpunten!\n• 📈 Co-Op Bonus: Hoe meer actieve leden jouw bende heeft, hoe hoger de gezamenlijke slagingskans.\n• 🏎️ Snelheidseis: Zware heists (zoals Fort Goudkluis) vereisen een wagen in de Bende Garage met voldoende topsnelheid (bijv. minimaal 95 km/u).",
                'links' => [
                    ['text' => '💎 Naar Grote Heists', 'url' => route('heists.index')],
                ],
                'suggestions' => [
                    'Hoe werkt de Bende Garage en wagens doneren?',
                    'Hoe werkt de Smokkel Garage en wagenhandel?',
                ],
            ],
            [
                'title' => '🏎️ Roterende Smokkel Garage (Wisselende Condities & Opknappers)',
                'keywords' => ['garage', 'voertuig', 'voertuigen', 'auto', 'autos', 'vluchtauto', 'vluchtwagen', 'scooter', 'opknapper', 'tuning', 'getuned', 'conditie', 'repareren'],
                'answer' => "💡 IN HET KORT: Elk half uur arriveert een nieuwe lading van 8 unieke voertuigen met wisselende condities en zeldzame motortuning!\n\n📋 DE ROULERENDE WAGENMARKT ('/garage'):\n• 🕒 Elk Half Uur Nieuw: Om het hele en halve uur arriveert een nieuwe vracht van 8 unieke wagens.\n• 🔧 Projectwagens: Schadebolides met lage conditie koop je spotgoedkoop in om ze met winst op te knappen.\n• 🚀 Fabriekstuning: Zeldzame wagens hebben opgevoerde motoren met blijvende extra topsnelheid.\n• 🏎️ 28 Modellen: Van Tomos en sedans tot speedboten en helikopters voor solo en bende heists!",
                'links' => [
                    ['text' => '🏎️ Smokkel Garage', 'url' => route('garage.index')],
                ],
                'suggestions' => [
                    'Hoe werken Grote Bende Heists?',
                    'Hoe werkt de Bende Garage en wagens doneren?',
                ],
            ],
            [
                'title' => '🛏️ Cel Upgrades & Persoonlijk Verblijf',
                'keywords' => ['cel', 'cell', 'upgrades', 'celupgrades', 'matras', 'verblijf', 'deur', 'verdediging', 'inrichten', 'geldput'],
                'answer' => "💡 IN HET KORT: Richt je eigen gevangeniscel ('/cel') in met 4 exclusieve upgrade-lijnen tot level 5!\n\n📋 DE 4 CELUPGRADES:\n• 🛏️ Luxe Boxspring & Traagschuim Matras: Tot +40% sneller energieherstel.\n• 🚪 Versterkte Stalen Deur & Camerasysteem: Tot +2.000 passieve celverdediging tegen overvallen.\n• 🔐 Verborgen Vloerkluis & Geheim Luik: Beschermt tot 50% van je contante zakgeld tegen buit.\n• 📺 Luxe Lounge, Smart TV & Geluidsset: Tot +25% bonus op XP en Respect bij al je acties!",
                'links' => [
                    ['text' => '🛏️ Mijn Cel & Upgrades', 'url' => route('cell.index')],
                ],
                'suggestions' => [
                    'Hoe beveilig ik mijn contante geld op de bank?',
                    'Hoe werkt het Bende-Uurloon?',
                ],
            ],
            [
                'title' => '🏦 Banktransacties tot 1 Biljoen Euro (64-Bit Modernisering)',
                'keywords' => ['biljoen', 'miljard', '2 miljard', 'limiet', 'banklimiet', 'overboeken', 'storten', 'precisie', '64-bit'],
                'answer' => "💡 IN HET KORT: De database van De Bank is gemoderniseerd naar 64-bit getallen. Geen 2-miljard limieten meer!\n\n📋 BANKCAPACITEIT:\n• 🏦 Tot 1 Biljoen Euro: Stortingen, opnames en overboekingen ondersteunen bedragen tot maar liefst €1.000.000.000.000 zonder dat bedragen overstromen of vastlopen.\n• 🔒 100% Veilig: Bankgeld is veilig voor zakkenrollers en de Luchtplaats.",
                'links' => [
                    ['text' => '🏦 Naar De Bank', 'url' => route('bank.index')],
                ],
                'suggestions' => [
                    'Hoe beveilig ik mijn contante geld op de bank?',
                    'Hoe werkt het Bende-Uurloon?',
                ],
            ],
            [
                'title' => '🎯 Liquidatie 2.0, Backfire-Vesten, Bloedwraak & Code Blauw',
                'keywords' => ['liquidatie', 'liquidaties', 'kogel', 'kogels', 'bullets', 'schieten', 'hitman', 'coma', 'blauw', 'serum', 'reanimatie', 'vest', 'backfire', 'ricochet', 'bloedwraak', 'blood', 'feud', 'feuds', 'schaduwen', 'moord', 'brancard'],
                'answer' => "💡 IN HET KORT: Bij Liquidatie 2.0 vuur je met kogels op een rivaal binnen jouw levelbereik (max 15 levels verschil) om maximaal €25.000.000 contant geld te plunderen!\n\n📋 ZO WERKT HET:\n• 🔒 Harde 15-Level Limiet: Je kunt alléén spelers liquideren die maximaal 15 levels hoger of lager zijn. Geen enkele uitzondering meer om pestgedrag van low-level alts definitief te stoppen!\n• ⚔️ Anti-Hit-and-Run Gevechtsstatus: Wie een aanval of schot uitvoert, raakt 30 minuten in gevechtsstatus en kan in die tijd géén vrijwillige isolatie kopen om weg te kruipen achter een schild.\n• 💰 Buit-Plafond: Er geldt een hard maximum van €25.000.000 buit per geslaagde liquidatie. Je kluis en bank zijn daarnaast altijd 100% veilig!\n• 🛡️ Backfire-Vest: Kaatst kogels van zwakkere schutters terug zodat de dader zélf op de brancard belandt!\n• 🩸 Bloedwraak: Wordt een bendelid beschoten? Dan krijgt jouw bende 7 dagen lang vergeldingsrecht binnen het 15-level bereik (+€25.000 & +50 Respect bij succes).\n• 💉 Code Blauw (Coma): Treffer 1 t/m 9 kun je direct doorspelen (met 60 min Medisch Infuus-Schild). Pas bij 10/10 treffers kom je in Code Blauw.",
                'links' => [
                    ['text' => '🎯 Naar Liquidatie 2.0', 'url' => route('hitman.index')],
                    ['text' => '🗺️ Verplaatsen (+25% Bonus)', 'url' => route('travel.index')],
                    ['text' => '👑 24u Cash Bescherming', 'url' => route('vip.index')],
                ],
                'suggestions' => [
                    'Hoe bescherm ik mezelf tegen aanvallen?',
                    'Hoe verdien ik 100% gratis VIP?',
                    'Hoe werkt Verplaatsen (+35 XP per reis)?',
                ],
            ],
            [
                'title' => '🎯 Koudbloedigheid, Schietbaan & 1-Patroon Genadeschot',
                'keywords' => ['koudbloedigheid', 'koudbloedig', 'kaliberfocus', 'focus', 'schietbaan', 'ondergrondse', 'genadeschot', 'precisieschot', '1-patroon', 'patroon', 'patronen', 'salvo', 'munitiekist', 'munitiekisten', 'dossier', 'dossiergraad', 'graad', 'graad 25', 'kaliber', 'richtvermogen'],
                'answer' => "💡 IN HET KORT: Koudbloedigheid (0%–100%) is je precisiefocus bij Liquidatie 2.0. Vanaf Dossier-Graad 25 én minimaal 30% Koudbloedigheid schiet je met een dodelijk 1-Patroon Genadeschot!\n\n📋 3 MANIEREN OM KOUDBLOEDIGHEID TE TRAINEN / VERHOGEN:\n1. 🎯 Ondergrondse Schietbaan ('/liquidatie'): Voor €350.000 contant/bank + 15 Energie krijg je direct +10% Koudbloedigheid en +120 XP (of 25 Sterren ⭐ voor +15% Koudbloedigheid zonder energiekosten).\n2. 🥊 Vechtoefening ('/vechtoefening'): Elke trainingsronde tegen een oefenpop geeft direct +4% Koudbloedigheid (naast stats & XP)!\n3. 📦 Grote Munitiekisten Kopen ('/liquidatie'): De Paviljoen Munitiekist geeft direct +5%, de Syndicaat Wapenkist +10%, en de Kartel Arsenaalcontainer +15% Koudbloedigheid!\n\n🔥 LET OP: Elk schot kost focus (-12% bij een Genadeschot, -6% bij een salvo). Zakt je Koudbloedigheid onder de 30%? Dan trilt je hand en kun je pas weer met 1 patroon schieten als je hebt bijgetraind!",
                'links' => [
                    ['text' => '🎯 Naar Liquidatie & Schietbaan', 'url' => route('hitman.index')],
                    ['text' => '🥊 Naar Vechtoefening (+4%)', 'url' => route('combat_training.index')],
                ],
                'suggestions' => [
                    'Hoe werkt Liquidatie 2.0 met kogels?',
                    'Wat doet een Backfire-vest tegen kogels?',
                    'Hoe werkt vechten en de respectlijst?',
                ],
            ],
            [
                'title' => '📦 Koffers Onderscheppen & XXL Smokkelkoffers',
                'keywords' => ['koffer', 'koffers', 'xxl', 'onderscheppen', 'pakketjes', 'breekijzer', 'lockpick', 'smokkelkoffer'],
                'answer' => "💡 IN HET KORT: Onderschep pakketjes voor cash, KluisMunten 🔑 en Smokkelkoffers, en breek ze open voor grote bedragen!\n\n📋 SOORTEN KOFFERS ('/pakketjes'):\n• 🧰 Gewone Smokkelkoffer: Levert tot €6.562 contant geld op (met de 1,75x Ruwe Kracht bonus) of handige items.\n• 💼 XXL Smokkelkoffer: De meest waardevolle koffer (te winnen bij o.a. de 24 Uur Battle of in de Sterrenwinkel)! Bevat €25.000 tot €75.000 veilig bankgeld én zeldzame buit!",
                'links' => [
                    ['text' => '📦 Koffers Onderscheppen', 'url' => route('packages.index')],
                ],
                'suggestions' => [
                    'Hoe win ik de 24 Uur Battle?',
                    'Hoe kraak ik de Centrale Medicijnkluis?',
                    'Hoe werkt het Behandelaarsgesprek en 3u Verlof?',
                ],
            ],
            [
                'title' => '🛒 Kliniek Winkel, Marktplaats, Inventaris & Het Depot',
                'keywords' => ['winkel', 'marktplaats', 'depot', 'inventaris', 'wapens', 'wapen', 'pantser', 'uitrusten', 'kopen', 'verkopen', 'opslag'],
                'answer' => "💡 IN HET KORT: In de Winkel koop je nieuwe spullen, op de Marktplaats handel je met spelers en in het Depot bewaar je extra voorraad.\n\n📋 HANDIG OM TE WETEN:\n• 🔫 Wapens & Pantsers zijn direct actief: Zodra je een wapen of vest koopt in de Kliniek Winkel, telt de kracht direct en automatisch mee in gevechten (je hoeft niets aan te trekken!).\n• 🍫 Snacks & EHBO: Gebruik je via het tabblad 'Mijn Inventaris' in de winkel.\n• 🤝 Marktplaats & Depot: Verkoop spullen aan andere patiënten op de Marktplaats of sla ze veilig op in Het Depot.",
                'links' => [
                    ['text' => '🛒 Kliniek Winkel', 'url' => route('shop.index')],
                    ['text' => '🤝 Marktplaats', 'url' => route('marketplace.index')],
                    ['text' => '🏭 Het Depot', 'url' => route('depot.index')],
                ],
                'suggestions' => [
                    'Hoe train ik mijn Kracht en Verdediging?',
                    'Hoe werkt vechten en de respectlijst?',
                    'Hoe krijg ik energie?',
                ],
            ],
            [
                'title' => '🥊 Vechtoefening, Informanten, Kopgeld & Armpjedrukken',
                'keywords' => ['vechtoefening', 'sparren', 'informant', 'informanten', 'kopgeld', 'premie', 'armpjedrukken'],
                'answer' => "💡 IN HET KORT: Extra gevechtsonderdelen om veilig te oefenen, rijke doelwitten op te sporen of premies te innen:\n\n• 🥊 Vechtoefening ('/vechtoefening'): Spar zonder risico op trainingspoppen voor XP en gevechtservaring.\n• 🕵️ Informanten ('/informanten'): Koop een tip om te zien welke speler veel contant geld op zak heeft.\n• 🎯 Kopgeld ('/kopgeld'): Zet een premie op een rivaal of versla doelwitten op de premielijst.\n• 💪 Armpjedrukken ('/armpjedrukken'): Daag medepatiënten 1-tegen-1 uit om een geldinzet!",
                'links' => [
                    ['text' => '🥊 Vechtoefening', 'url' => route('combat_training.index')],
                    ['text' => '🕵️ Informanten', 'url' => route('informants.index')],
                    ['text' => '🎯 Kopgeld', 'url' => route('bounties.index')],
                    ['text' => '💪 Armpjedrukken', 'url' => route('armwrestling.index')],
                ],
                'suggestions' => [
                    'Hoe werkt vechten en de respectlijst?',
                    'Hoe bescherm ik mezelf tegen aanvallen?',
                    'Hoe train ik mijn Kracht en Verdediging?',
                ],
            ],
            [
                'title' => '🏆 24 Uur Battle, Rad van Fortuin, 22:00 Bonus & Battle Pass',
                'keywords' => ['battle', '24u', 'dagklassement', 'ranglijst', 'rad', 'fortuin', '2200', 'avond', 'inlog', 'battlepass', 'seizoen'],
                'answer' => "💡 IN HET KORT: Elke dag kun je gratis Gouden Sterren ⭐, Kluiscodes 🔐 en XXL Koffers winnen via de 24 Uur Battle en dagbonussen!\n\n📋 ALLE DAGELIJKSE PRIJZEN:\n• 🏆 24 Uur Battle (7 Categorieën): Strijd mee in Gevechten, Hossels, Afpersingen, Uitbraken, Pillen-Productie, Contrabande-Afzet en Liquidaties. De Top 3 wint om 00:00u Gouden Sterren ⭐, Kluiscodes 🔐, XXL Koffers en Bivakmutsen!\n• 🎡 Rad van Fortuin: Elke dag 1x gratis draaien (VIP 2x per dag).\n• 🕙 22:00 - 22:30 Avond Bonus: Log in tussen 22:00 en 22:30 uur voor 3 gratis kluiscodes!\n• 🎫 Battle Pass: Verdien gratis beloningen door actief te spelen.",
                'links' => [
                    ['text' => '🏆 24 Uur Battle', 'url' => route('battle.index')],
                    ['text' => '🎡 Rad van Fortuin', 'url' => route('daily.index')],
                    ['text' => '🎫 Battle Pass', 'url' => route('battlepass.index')],
                ],
                'suggestions' => [
                    'Hoe kraak ik de Centrale Medicijnkluis?',
                    'Hoe werkt Koffers Onderscheppen en een XXL Koffer?',
                    'Hoe verdien ik 100% gratis VIP?',
                ],
            ],
            [
                'title' => '🤝 Vrienden, BFF (Best Friend), Krant & Cinema',
                'keywords' => ['vrienden', 'vriend', 'bff', 'friend', 'cinema', 'bioscoop', 'krant', 'afdelingsblad', 'tijdlijn', 'hartjes'],
                'answer' => "💡 IN HET KORT: Samen sta je veel sterker in de kliniek! Voeg vrienden toe en kies één BFF (Best Friend Forever).\n\n📋 SOCIAAL & GEZELLIGHEID:\n• 💛 BFF & Vrienden ('/vrienden'): Vrienden en je BFF kunnen jouw isoleerceldeur veel sneller van buitenaf openbreken!\n• 🍿 Cinema: Kijk samen YouTube-video's in de virtuele filmzaal en chat live mee.\n• 📰 Afdelingsblad & Tijdlijn: Lees het laatste klinieknieuws en deel updates met medepatiënten.",
                'links' => [
                    ['text' => '🤝 Vrienden & BFF', 'url' => route('friends.index')],
                    ['text' => '🍿 Cinema', 'url' => route('cinema.index')],
                ],
                'suggestions' => [
                    'Hoe kom ik sneller uit de isoleercel?',
                    'Hoe werken Bendes en het Bende-Uurloon?',
                    'Wat zijn de spelregels van TBS Kliniek?',
                ],
            ],
            [
                'title' => '📚 Simpel Woordenboek (HP, XP, Cooldown, Ricochet, BFF)',
                'keywords' => ['woordenboek', 'begrippen', 'termen', 'betekenis', 'afkortingen', 'cooldown', 'reset', 'xp'],
                'answer' => "💡 IN HET KORT: Alle veelgebruikte kliniek-termen simpel uitgelegd:\n\n• ❤️ HP (Gezondheid): Je levensbalk van 0 tot 100 (gratis +45 HP bij Zuster Ingrid).\n• 📊 XP (Ervaring): Punten waarmee je een Level omhoog gaat (+35 XP per reis!).\n• ⏱️ Cooldown: De korte wachttijd voordat je een actie opnieuw kunt doen.\n• 🕛 Reset (00:00u): Om middernacht resetten de dagtellers en de 24 Uur Battle.\n• 🎭 Heist: Grote overval met vluchtauto + bivakmuts.\n• 🛡️ Backfire / Ricochet: Vest dat vijandelijke kogels terugkaatst op de schutter.\n• 💉 Code Blauw: Coma na 10 kogeltreffers (vereist Reanimatie-Serum).",
                'links' => [
                    ['text' => '📖 Volledige Gids & FAQ', 'url' => route('faq.index')],
                ],
                'suggestions' => [
                    'Wat moet ik doen als beginner?',
                    'Hoe werkt Liquidatie 2.0 met kogels?',
                    'Hoe werken Grote Heists en vluchtauto\'s?',
                ],
            ],
            [
                'title' => '⚙️ Account, Spelregels (Geen VPN) & Support Tickets',
                'keywords' => ['account', 'wachtwoord', 'naam', 'kleur', 'dark', 'regels', 'spelregels', 'vpn', 'multi', 'dubbelaccount', 'ticket', 'tickets', 'support', 'beheer', 'melden', 'familie', 'ip', 'huisgenoten', 'accounts', 'hetzelfde'],
                'answer' => "💡 IN HET KORT: Speel eerlijk en houd het gezellig. Heb je hulp nodig of wil je de Gang Overstap Bonus claimen? Open een Support Ticket!\n\n📋 DE 3 GOUDEN SPELREGELS:\n1. 👤 Maximaal 1 account per persoon (multi-accounts worden direct geblokkeerd).\n2. 🏠 Maximaal 2 accounts op hetzelfde IP-adres (voor familie/huisgenoten; meld 3+ vooraf via een ticket).\n3. 🚫 Gebruik géén VPN of Proxy.\n\n🚩 Scheldt iemand in de chat? Klik op het rode vlaggetje bij het bericht of open een Support Ticket!",
                'links' => [
                    ['text' => '🎫 Nieuw Support Ticket', 'url' => route('help.tickets.create')],
                    ['text' => '⚙️ Account Instellingen', 'url' => route('settings.account')],
                ],
                'suggestions' => [
                    'Wat is de Gang Overstap Bonus (€5 miljoen)?',
                    'Hoe verdien ik 100% gratis VIP?',
                    'Wat moet ik doen als beginner?',
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
        $search = trim((string) $request->input('search', ''));

        $totalQuestions = 0;
        foreach ($categories as $cat) {
            $totalQuestions += count($cat['questions'] ?? []);
        }

        return view('game.faq', compact('categories', 'search', 'totalQuestions'));
    }
}
