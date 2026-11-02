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
                        'a' => "💡 IN HET KORT: Je begint onderaan als nieuwe patiënt en werkt jezelf in 6 simpele stappen op tot de rijkste en sterkste baas van de kliniek.\n\n📋 HET COMPLETE STAPPENPLAN (VAN BEGINNER TOT PRO):\n\n1. 🧭 STAP 1: Je Eerste 10 Minuten (Veilig Starten)\n• Ga naar de Ziekenboeg en laat Zuster Brenda je gratis naar 100% Gezondheid (HP) brengen.\n• Draai 1x gratis aan het Rad van Fortuin voor gratis geld, energie of sterren.\n• Zet AL je contante geld direct op De Bank! Geld in je broekzak kan namelijk gestolen worden.\n• Voltooi de 5 Rookie Taken op je Dashboard voor €6.500 cash, 30 kogels en 1 dag gratis VIP!\n\n2. 🔨 STAP 2: Veilig Geld & Ervaring (XP) Verdienen (Level 1 t/m 3)\n• Werk bij Werkmeester Henk in de Dagbesteding. Dit is 100% veilig: je kunt hier NOOIT bij gepakt worden en belandt nooit in de isoleercel.\n• Doe je eerste opdrachten bij 'Missies' voor makkelijk startgeld.\n• Doe elke nacht na 00:00 uur het Behandelaarsgesprek voor 3 uur Begeleid Verlof buiten de kliniek.\n\n3. 💪 STAP 3: Sterker Worden in de Gym (Level 3 t/m 6)\n• Gebruik je energie in de Fitnessruimte om Kracht (harder slaan), Verdediging (minder schade krijgen) en Snelheid (sneller ontsnappen) te trainen.\n• Koop een goedkoop wapen en een vest in de Kliniek Winkel.\n\n4. 🥷 STAP 4: Eerste Misdaden & Maskers Scoren (Level 6 t/m 8)\n• Doe snelle klusjes bij 'Hosselen' (zoals shag rollen) voor vlotte winst.\n• Ga 'Zakkenrollen' om een Bivakmuts (Masker) te stelen. Zolang je een masker hebt, halveert jouw kans om in de isoleercel te belanden!\n• Onderschep smokkelkoffers op het perron en breek ze open.\n\n5. 🥊 STAP 5: Vechten op de Luchtplaats & Reizen (Level 8 t/m 12)\n• Val zwakkere spelers aan op de Luchtplaats (Respectlijst) om hun respect en tot 15% van hun contante zakgeld te stelen.\n• Reis tussen afdelingen via 'Verplaatsen'. Elke reis geeft je meteen +35 XP!\n\n6. 👑 STAP 6: Pro Endgame - Bendes, Heists & Liquidaties (Level 12+)\n• Richt vanaf Level 3 een eigen Bende op of sluit je aan bij een bestaande groep voor gratis Bende-Uurloon.\n• Koop snelle vluchtauto's in de Smokkel Garage en pleeg Grote Heists op banken en juweliers.\n• Verover Controlepunten voor geld per minuut, schiet met kogels bij Liquidatie 2.0 en win elke nacht gratis Sterren ⭐ in de 24 Uur Battle!\n\n🔥 PRO-TIP: Koppel op dag 1 meteen je Discord (+3 dagen gratis VIP & €2.500) en zet Push-meldingen aan (+€25.000 contant & 25 respect)!",
                        'links' => [
                            ['text' => 'Naar Dashboard (Rookie Taken)', 'url' => route('dashboard')],
                            ['text' => 'Naar De Bank', 'url' => route('bank.index')],
                            ['text' => 'Naar Dagbesteding', 'url' => route('daycare.index')],
                        ],
                    ],
                    [
                        'q' => 'Wat is het doel van TBS Kliniek?',
                        'a' => "💡 IN HET KORT: Het doel is om van een arme nieuwe patiënt uit te groeien tot de machtigste en rijkste crimineel van de hele inrichting.\n\n📋 WAAR STRIJD JE VOOR?\n• 🥊 Nummer 1 op de Ranglijst: Verzamel het meeste Respect van alle spelers;\n• 👥 De Machtigste Bende: Bouw met vrienden een eigen gang, koop panden en verdien elk uur automatisch duizenden euro's bende-uurloon;\n• 🏆 De 24 Uur Battle Winnen: Eindig elke dag in de top 3 van de 5 dagelijkse wedstrijden en win gratis Gouden Sterren ⭐;\n• ⛏️ De Grote Uitbraak: Graaf stiekem een tunnel van 100 meter onder je cel en ontsnap;\n• 🔐 De Kluis Kraken: Raad de 4-cijferige code van de Centrale Medicijnkluis en pak de jackpot!\n\n🔥 PRO-TIP: Je bepaalt zelf je speelstijl: je kunt 100% vreedzaam rijk worden als handelaar en werker, of juist als gevreesde vechtersbaas en bendeleider heersen.",
                        'links' => [
                            ['text' => 'Ranglijst Bekijken', 'url' => route('leaderboard.index')],
                            ['text' => '24 Uur Battle', 'url' => route('battle.index')],
                        ],
                    ],
                    [
                        'q' => 'Hoe pak ik op Dag 1 meteen meer dan €35.000 gratis startgeld en 4 dagen Gratis VIP?',
                        'a' => "💡 IN HET KORT: Door 4 simpele startersbonussen te claimen heb je binnen 5 minuten al een gigantisch startkapitaal op zak.\n\n📋 ZO PAK JE ALLE GRATIS STARTERSBONUSSEN:\n1. 🧭 Intake Rondleiding (€1.000): Klik bovenaan deze pagina op 'Start Rondleiding Nu' en doorloop de 5 korte uitlegstappen.\n2. 🎯 De 5 Rookie Taken op je Dashboard (€6.500 + 1 Dag VIP + 30 Kogels): Op je Dashboard staan 5 makkelijke beginnersopdrachten (1 misdaad doen, €500 storten, 1x trainen, 1x vechten, 1 sponsorvideo kijken). Als je ze alle 5 afvinkt, krijg je bovendien €5.000 extra hoofdprijs én de titel 'Kliniek Overlever'!\n3. 🔔 Push-Meldingen Aanzetten (€25.000 + 25 Respect): Klik op de meldingen-knop om meldingen toe te staan en ontvang direct €25.000 cash!\n4. 🎮 Discord Koppelen (€2.500 + 3 Dagen VIP): Koppel eenmalig je Discord-account via het menu en ontvang direct 3 dagen gratis VIP.\n\n🔥 PRO-TIP: Stort al dit gratis geld meteen op De Bank voordat je gaat vechten!",
                        'links' => [
                            ['text' => 'Naar Dashboard', 'url' => route('dashboard')],
                            ['text' => 'Sponsorkamer', 'url' => route('sponsor.index')],
                            ['text' => 'Naar De Bank', 'url' => route('bank.index')],
                        ],
                    ],
                    [
                        'q' => 'Ik ben zojuist binnengebracht! Wat moet ik nu als allereerste aanklikken?',
                        'a' => "💡 IN HET KORT: Genees jezelf gratis, draai aan het gratis geluksrad, doe veilig werk en zet al je geld op de bank.\n\n📋 VOLG DEZE 5 SIMPELE KLIKS:\n1. 🏥 Klik op 'Voortgang > Ziekenboeg': Laat Zuster Brenda je gratis naar 100% Gezondheid brengen.\n2. 🎰 Klik op 'Bonus & Kluizen > Rad van Fortuin': Draai 1x gratis voor je eerste cadeau.\n3. 🔨 Klik op 'Voortgang > Dagbesteding': Werk een beurt bij Werkmeester Henk. Dit levert altijd geld op en kan niet mislukken.\n4. 📜 Klik op 'Voortgang > Missies': Claim je eerste voltooide missie.\n5. 🏦 Klik op 'Economie > De Bank': Klik op 'Alles Storten' zodat niemand je geld kan stelen!",
                        'links' => [
                            ['text' => '1. Ziekenboeg', 'url' => route('infirmary.index')],
                            ['text' => '2. Rad van Fortuin', 'url' => route('daily.index')],
                            ['text' => '3. De Bank', 'url' => route('bank.index')],
                        ],
                    ],
                    [
                        'q' => 'Wat betekenen alle balken en icoontjes bovenaan mijn scherm (Energie, HP, Geld, Sterren, Respect, XP)?',
                        'a' => "💡 IN HET KORT: Bovenaan zie je jouw levensbalk, je uithoudingsvermogen (energie), je portemonnee en je niveau.\n\n📋 SIMPELE UITLEG PER ICOONTJE:\n• ⚡ ENERGIE (Bliksem): Heb je nodig voor bijna elke actie. Vult elke 5 minuten vanzelf weer met 1 punt aan (elke 3 min voor VIP).\n• ❤️ GEZONDHEID / HP (Hartje): Jouw levensbalk (maximaal 100). Als deze laag is, moet je even langs Zuster Brenda in de Ziekenboeg.\n• 💵 CONTANT GELD: Geld dat los in je broekzak zit. Pas op: andere spelers kunnen dit stelen!\n• 🏦 BANKGELD: Geld dat veilig op je bankrekening staat. Niemand kan dit stelen via de Respectlijst of zakkenrollen.\n• 💰 TOTAAL VERMOGEN: Jouw contante geld en bankgeld bij elkaar opgeteld.\n• ⭐ STERREN: Gouden sterren waarmee je jezelf direct uit de isoleercel koopt of wachttijden overslaat.\n• 🥊 RESPECT: Jouw aanzien in de kliniek. Hoe meer respect, hoe hoger je op de ranglijst staat.\n• 📊 LEVEL & XP: Elke actie geeft Ervaringspunten (XP). Is je XP-balk vol? Dan ga je een Level omhoog en speel je nieuwe onderdelen vrij!",
                    ],
                    [
                        'q' => 'Wat is het verschil tussen Contant Geld en Bankgeld, en waarom moet ik contant geld ALTIJD direct storten?',
                        'a' => "💡 IN HET KORT: Geld in je broekzak (contant) kan gestolen worden; geld op De Bank staat achter slot en grendel!\n\n📋 ZO ZIT HET:\n• 💵 Contant Geld op zak: Als iemand jou aanvalt op de Luchtplaats en wint, steelt diegene tot 15% van jouw contante geld. Ook zakkenrollers kunnen uit je broekzak stelen.\n• 🏦 Geld op De Bank: Is 100% veilig voor Luchtplaats-aanvallen en zakkenrollers.\n• 🎁 Gratis Zakgeld: Zit je nog niet in een bende? Dan stort de kliniek elke 15 minuten automatisch €1.000 gratis zakgeld op je bankrekening (tot wel €25.000 per kwartier voor VIP supporters)!\n\n🔥 PRO-TIP: Alleen bij een zware kogel-aanval (Liquidatie 2.0) kan een schutter 7% van je bank stelen. Wil je zelfs dát 100% blokkeren? Activeer dan '24 Uur Cash Bescherming' in de VIP Shop!",
                        'links' => [
                            ['text' => 'Direct naar De Bank', 'url' => route('bank.index')],
                        ],
                    ],
                    [
                        'q' => 'Wat gebeurt er als mijn levensbalk (HP) op 0 komt? Ben ik dan mijn account kwijt?',
                        'a' => "💡 IN HET KORT: Nee, je raakt je account of spullen NOOIT kwijt! Je moet alleen even gratis genezen in de Ziekenboeg.\n\n📋 WAT GEBEURT ER BIJ 0 HP?\n• Als je een vuistgevecht verliest en op 0 HP komt, ben je tijdelijk gewond.\n• Ga simpelweg naar 'Voortgang > Ziekenboeg' en klik op 'Laat je behandelen'.\n• Zuster Brenda geeft je direct en 100% gratis weer 100% Gezondheid, waarna je meteen verder kunt spelen!",
                        'links' => [
                            ['text' => 'Gratis Genezen in Ziekenboeg', 'url' => route('infirmary.index')],
                        ],
                    ],
                    [
                        'q' => 'Wat zijn Sterren ⭐ en hoe verdien ik ze 100% gratis?',
                        'a' => "💡 IN HET KORT: Sterren ⭐ zijn speciale gouden munten voor handige extra's, en je kunt ze gewoon gratis in het spel winnen.\n\n📋 7 MANIEREN OM GRATIS STERREN TE VERDIENEN:\n1. 🎰 Rad van Fortuin: Draai elke dag gratis.\n2. 🏆 24 Uur Battle: Eindig om 00:00 uur in de top 3 van een dagklassement.\n3. 🚩 Controlepunten: Houd een controlepunt 30 minuten vast en ontvang +1 gratis Ster.\n4. 📦 XXL Koffers: Kraak zeldzame XXL smokkelkoffers.\n5. ⛏️ Uitbraaktunnel: Graaf je tunnel tot 100 meter en ontvang +25 gratis Sterren.\n6. 📺 Sponsorkamer: Bekijk korte video's voor mijlpaalbeloningen.\n7. 🎫 Battle Pass: Speel gratis beloningen vrij in het seizoensoverzicht.",
                        'links' => [
                            ['text' => 'Rad van Fortuin', 'url' => route('daily.index')],
                            ['text' => 'Controlepunten', 'url' => route('control-points.index')],
                        ],
                    ],
                    [
                        'q' => 'Wat is Discipline en waarom heb ik dat nodig?',
                        'a' => "💡 IN HET KORT: Discipline laat zien hoe netjes jij je gedraagt en zorgt voor betere beloningen bij de psychiater.\n\n📋 HOE BOUW JE DISCIPLINE OP?\n• Je verdient Discipline door legaal te werken bij 'Voortgang > Dagbesteding' (Werkmeester Henk).\n• Hoe hoger je Discipline, hoe makkelijker je goedkeuring krijgt voor 3 uur Begeleid Verlof buiten de kliniek!",
                        'links' => [
                            ['text' => 'Naar Dagbesteding', 'url' => route('daycare.index')],
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
                        'a' => "💡 IN HET KORT: Je energie vult vanzelf elke paar minuten aan, of je eet een snack uit je inventaris om direct verder te gaan.\n\n📋 AUTOMATISCH HERSTEL:\n• Standaard speler: +1 energie per 5 minuten;\n• VIP / Tier 1: +1 energie per 3 minuten;\n• Tier 2 Elite: +1 energie per 2,5 minuut;\n• Tier 3 & 4 (Baas / Lifetime): +1 energie per 2 minuten!\n\n📋 DIRECT EXTRA ENERGIE NODIG?\n1. 🍫 Kantinesnacks eten: Koop chocoladerepen of energiedrankjes in de Kliniek Winkel en klik op 'Gebruiken' in je Inventaris.\n2. 🎰 Rad van Fortuin: Draai gratis voor een energie-prijs.\n3. ⭐ Sterren / VIP Shop: Vul je balk in één klap naar 100% met Sterren of 15 VIP Punten.",
                        'links' => [
                            ['text' => 'Winkel & Snacks', 'url' => route('shop.index', ['tab' => 'inventaris'])],
                            ['text' => 'VIP Shop', 'url' => route('vip.index')],
                        ],
                    ],
                    [
                        'q' => 'Wat doen Kracht, Verdediging en Snelheid en hoe train ik ze in de Gym?',
                        'a' => "💡 IN HET KORT: Deze drie eigenschappen bepalen of je gevechten wint en hoe makkelijk je ontsnapt.\n\n📋 WAT DOET ELKE EIGENSCHAP (KOST 10 ENERGIE PER TRAINING):\n• 🥊 KRACHT (Push-ups): Hoe harder jij slaat wanneer je iemand aanvalt.\n• 🛡️ VERDEDIGING (Bankdrukken): Hoeveel klappen jij tegenhoudt als iemand jou aanvalt.\n• 🏃 SNELHEID (Hardlopen): Hoe vaak je aanvallen ontwijkt, wie als eerste mag slaan én hoe makkelijk je uit de isoleercel ontsnapt!\n\n🔥 PRO-TIP: Train in het begin alle drie gelijkmatig naar minimaal 25 punten. Zo heb je geen zwakke plek!",
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
                        'q' => 'Hoe gebruik ik wapens, kogelwerende vesten, snacks en EHBO-kits?',
                        'a' => "💡 IN HET KORT: Alles wat je koopt of vindt staat in 'Winkel > Mijn Inventaris', waar je op 'Gebruiken' of 'Uitrusten' klikt.\n\n📋 ZO WERKT JE INVENTARIS:\n• Snacks & Verbanddozen: Klik op 'Gebruiken' voor directe Energie of Gezondheid (HP).\n• Wapens & Vesten: Klik op 'Uitrusten' zodat je ze draagt tijdens gevechten.",
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
                        'a' => "💡 IN HET KORT: Omdat werken in de werkplaats legaal is, heb je 0% kans op de isoleercel en verdien je altijd geld!\n\n📋 VOORDELEN VAN WERKMEESTER HENK:\n• 🟢 100% Veilig: Je wordt nooit gearresteerd.\n• 💵 Vast Loon: Je krijgt altijd direct contant geld uitbetaald.\n• 📈 Discipline & XP: Je bouwt Discipline en ervaringspunten op.\n• 🎁 Bonusvondsten: Tussen het zaagsel vind je regelmatig gratis ijzerzaagjes, lockpicks of geheime 4-cijferige kluiscodes!",
                        'links' => [
                            ['text' => 'Veilig Werken bij Henk', 'url' => route('daycare.index')],
                        ],
                    ],
                    [
                        'q' => 'Wat is het dagelijkse Behandelaarsgesprek (00:00 dilemma) en hoe verdien ik 3 uur Verlof?',
                        'a' => "💡 IN HET KORT: Geef elke dag één verstandig antwoord bij de psychiater en je mag 3 uur buiten de kliniek op verlof!\n\n📋 ZO WERKT HET GESPREK:\n• Elke nacht om 00:00 uur staat er een nieuwe vraag klaar onder 'Voortgang > Behandeling'.\n• De psychiater vraagt hoe jij zou reageren op een lastige situatie. Kies altijd het meest rustige, beleefde en sociale antwoord!\n• 🟢 Scoor je 60% of hoger? Dan krijg je direct 3 UUR BEGELEID VERLOF!\n• 🔴 Kies je een heel agressief antwoord (0%)? Dan krijg je een kalmeringsspuit (15 minuten sedatie) en moet je even rusten.",
                        'links' => [
                            ['text' => 'Naar Behandelaarsgesprek', 'url' => route('treatment.index')],
                        ],
                    ],
                    [
                        'q' => 'Wat kan ik doen tijdens mijn 3 uur Begeleid Verlof buiten de poorten?',
                        'a' => "💡 IN HET KORT: Tijdens je verlof kun je 3 speciale buiten-missies doen die duizenden euro's en een XXL Koffer opleveren!\n\n📋 DE 3 BUITEN-MISSIES:\n1. 🛒 Lokale Supermarkt: Verdien €3.500 contant + 45 XP;\n2. 🚆 Centraal Station: Verdien €6.000 contant + 65 XP;\n3. 🛅 Station Kluisjes: Verdien €10.000 contant + 100 XP + een gratis Smokkelkoffer XXL!\n\n🔥 PRO-TIP: Voer alle drie de buiten-missies meteen uit zodra je verlof ingaat!",
                        'links' => [
                            ['text' => 'Verlof & Behandeling', 'url' => route('treatment.index')],
                        ],
                    ],
                    [
                        'q' => 'Wat is Pillen Palmen in de Ziekenboeg?',
                        'a' => "💡 IN HET KORT: Je verstopt stiekem zware medicijnen onder je tong om ze voor veel geld door te verkopen.\n\n📋 HOE WERKT PILLEN PALMEN?\n• Ga naar 'Voortgang > Ziekenboeg' en kies voor 'Pillen Palmen'.\n• Lukt het? Dan verkoop je de gesmokkelde pillen voor duizenden euro's zwart geld en veel XP.\n• Word je betrapt door Zuster Brenda? Dan ga je direct de Isoleercel in!",
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
                        'a' => "💡 IN HET KORT: Als een misdaad mislukt, sluit de bewaker je tijdelijk op in de isoleercel.\n\n📋 WAT KUN JE WEL EN NIET DOEN?\n• ❌ Niet mogelijk: Misdaden plegen, vechten of reizen naar een andere afdeling.\n• ✅ Wel mogelijk: Geld op de bank zetten, chatten, naar de radio luisteren én op 6 manieren proberen vrij te komen!",
                        'links' => [
                            ['text' => 'Naar Isoleercel', 'url' => route('prison')],
                        ],
                    ],
                    [
                        'q' => 'Hoe kom ik meteen of sneller vrij uit de Isoleercel (Alle 6 manieren)?',
                        'a' => "💡 IN HET KORT: Je hoeft niet te wachten tot de klok afloopt; je kunt jezelf op 6 manieren sneller bevrijden!\n\n📋 DE 6 MANIEREN OM VRIJ TE KOMEN:\n1. 🏃 Luchtschacht-Uitbraak: Probeer 1x per celstraf zelf door de luchtschacht te kruipen (kost 20 Energie). Lukt het? Dan ben je METEEN VRIJ (+15 Respect & +35 XP)!\n2. 🪚 IJzerzaagje Gebruiken: Zaag de tralies door met een ijzerzaagje uit je inventaris (-30 minuten straftijd per zaagje).\n3. 🧺 Stoomwasserij-Dienst: Werk even in de hete wasserij (kost 10 Energie) om je straf met 45 seconden in te korten.\n4. 💰 Nachtcipier Omkopen: Geef de bewaker €2.500 contant voor 75% kans op 90 seconden strafvermindering.\n5. ⭐ Direct Vrijkopen of Video Kijken: Betaal 25 Sterren ⭐ om direct vrij te zijn, of kijk een korte sponsorvideo van 15 seconden.\n6. 🤝 Hulp van een Vriend: Vraag in de chat of een vrije medespeler jouw celdeur van buitenaf openbreekt!",
                        'links' => [
                            ['text' => 'Naar Isoleercel', 'url' => route('prison')],
                            ['text' => 'Sponsorkamer Video', 'url' => route('sponsor.index')],
                        ],
                    ],
                    [
                        'q' => 'Waarom staat er \'Je Zit Zelf Vast\' bij de namen onderaan de Isoleercel pagina?',
                        'a' => "💡 IN HET KORT: De lijst onderaan is om andere gevangenen te bevrijden, en dat kan alleen als je zelf vrij rondloopt.\n\n📋 SIMPEL UITGELEGD:\n• 🔴 Zit je zelf opgesloten? Gebruik dan de knoppen BOVENAAN de pagina om jezelf vrij te maken.\n• 🟢 Loop je vrij rond? Dan kun je ONDERAAN de pagina op 'Uitbreken' klikken bij opgesloten vrienden om hun deur open te breken!",
                    ],
                    [
                        'q' => 'Wat is de Uitbraaktunnel onder mijn cel en wat krijg ik bij 100 meter?',
                        'a' => "💡 IN HET KORT: Met een schep graaf je stiekem een tunnel van 100 meter onder je bed voor een megabeloning en 25 gratis Sterren!\n\n📋 ZO WERKT DE TUNNEL ('/ontsnappingstunnel'):\n• Koop een schep en graaf elke dag een stukje verder.\n• Vergeet niet om het uitgegraven zand stiekem op de binnenplaats te dumpen!\n• Bereik je 100 meter? Dan slaagt de Grote Uitbraak: je wint een grote som geld, veel XP, +25 gratis Sterren ⭐ en 3 uur voortvluchtige status!",
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
                        'a' => "💡 IN HET KORT: Hosselen is voor snel klein geld, Diefstal voor grotere buit, en Smokkelen is goederen inkopen en met dikke winst verkopen.\n\n📋 WELKE KIES JE?\n• 🚬 Hosselen ('/hosselen'): Makkelijke klusjes op de gang (shag rollen, aanstekers verkopen). Beste keuze voor beginners!\n• 🥷 Diefstal ('/diefstal'): Spannendere kruimeldiefstallen met hogere beloningen.\n• 🌿 Smokkelen & Dealen ('/smokkelen'): Koop goedkoop zaden of spullen in, laat ze rijpen in je cel en verkoop ze met grote winst.",
                        'links' => [
                            ['text' => 'Hosselen', 'url' => route('hustle.index')],
                            ['text' => 'Diefstal', 'url' => route('theft.index')],
                            ['text' => 'Smokkelen', 'url' => route('smuggle.index')],
                        ],
                    ],
                    [
                        'q' => 'Hoe werkt Zakkenrollen en waarom zijn Maskers (Bivakmutsen) zo belangrijk?',
                        'a' => "💡 IN HET KORT: Met zakkenrollen steel je geld én maskers; een masker halveert direct je kans om gearresteerd te worden!\n\n📋 STAP VOOR STAP ZAKKENROLLEN ('/zakkenrollen'):\n1. Klik eerst op 'Observeren' om te kijken wie veel geld of een masker op zak heeft.\n2. Klik daarna op 'Stelen' om toe te slaan.\n\n🔥 PRO-TIP: Zorg dat je ALTIJD minstens 1 masker in je bezit hebt voordat je grote misdaden of heists doet. Het scheelt de helft van al je celstraffen!",
                        'links' => [
                            ['text' => 'Naar Zakkenrollen', 'url' => route('pickpocket.index')],
                        ],
                    ],
                    [
                        'q' => 'Hoe werken Afpersen, Smokkel Droppings, Kluisjes Kraken en Hacking?',
                        'a' => "💡 IN HET KORT: Dit zijn 4 extra manieren om zwart geld, kluismunten en zeldzame spullen te verdienen.\n\n📋 OVERZICHT:\n• 👊 Afpersen ('/afpersen'): Dwing zwakkere patiënten of corrupte bewakers om jou zwijggeld te betalen.\n• 🛸 Tennisbal & Drone Droppings ('/smokkel-droppings'): Vang pakketjes op die over de kliniekmuur worden gegooid.\n• 🗄️ Kluisjes Kraken ('/kluisjes'): Breek personeelskluisjes open met een lockpick of loper.\n• 💻 Hacking ('/hacking'): Breek in op het computernetwerk van de kliniek voor digitaal zwart geld.",
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
                'title' => '🏎️ Smokkel Garage, Voertuigen & Grote Heists',
                'icon' => 'fa-masks-theater',
                'questions' => [
                    [
                        'q' => 'Hoe werkt de Smokkel Garage en waarom moet ik mijn voertuig repareren?',
                        'a' => "💡 IN HET KORT: In de garage koop je vluchtvoertuigen voor grote overvallen (Heists) en houd je hun conditie op 100%.\n\n📋 ZO WERKT HET:\n• Je begint klein met een Tomos (€1.200) of Zip Scooter (€2.500) en spaart door voor een Golf GTI, BMW M5, G63 AMG, Speedboot of Helikopter.\n• Tijdens een overval loopt je voertuig schade op. Daalt de conditie? Dan rijdt je auto langzamer en wordt je sneller gepakt!\n• Klik in de garage altijd even op 'Repareren' voordat je een nieuwe Heist start.",
                        'links' => [
                            ['text' => 'Naar Smokkel Garage', 'url' => route('garage.index')],
                        ],
                    ],
                    [
                        'q' => 'Hoe voer ik een Grote Heist uit en wat heb ik ervoor nodig?',
                        'a' => "💡 IN HET KORT: Voor een Grote Heist heb je een snel voertuig, een masker (bivakmuts) en genoeg energie nodig.\n\n📋 DE GROTE OVERVALLEN ('/heists'):\n• Van het overvallen van een Juwelier en Medicijntransport tot het kraken van de Centrale Bank Goudkluis.\n• 🟢 Bij succes: Je wint tot wel honderdduizenden euro's, heel veel XP en geheime kluiscodes!\n• 🔴 Bij mislukking: Je belandt in de isoleercel en je vluchtwagen raakt beschadigd.\n\n🔥 PRO-TIP: Zit je in een bende? Doe dan ook mee aan gezamenlijke Bende-Heists om samen de bende-pot te spekken!",
                        'links' => [
                            ['text' => 'Naar Grote Heists', 'url' => route('heists.index')],
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
                        'a' => "💡 IN HET KORT: Pak smokkelkoffers van het laadperron en breek ze open met een breekijzer of lockpick voor grote prijzen!\n\n📋 DE 3 SOORTEN KOFFERS ('/pakketjes'):\n1. 🎒 Gewone Smokkeltas: Klein zakgeld en kantinesnacks.\n2. 📦 Buitkrat: Mooie geldbedragen (€10.000 - €50.000), EHBO en gereedschap.\n3. 💼 XXL Koffer: De hoofdprijs! Bevat tot €250.000 cash, gratis Sterren ⭐, scherpe kogels, maskers en zeldzame profielstickers!",
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
                        'a' => "💡 IN HET KORT: Daag een speler uit voor een vuistgevecht van 5 seconden; als je wint steel je zijn Respect én tot 15% van zijn contante zakgeld!\n\n📋 BELANGRIJKE GEVECHTSREGELS:\n• 🥊 Winnaar: Wie de hoogste Kracht, beste uitrusting en meeste Snelheid heeft ten opzichte van de Verdediging van de ander, wint het duel.\n• 🔢 Daglimiet: Standaard spelers mogen maximaal 100 gevechten per dag doen (VIP-leden mogen 1.000 gevechten per dag doen). Om 00:00 uur reset de teller.\n• 🧮 Twijfel je of je kunt winnen? Gebruik eerst de 'Interactieve Gevecht Simulator' bovenaan deze FAQ-pagina!",
                        'links' => [
                            ['text' => 'Naar de Luchtplaats', 'url' => route('fight.index')],
                        ],
                    ],
                    [
                        'q' => 'Hoe bescherm ik mezelf als ik steeds word aangevallen door sterkere spelers?',
                        'a' => "💡 IN HET KORT: Zet je geld op de bank of sluit jezelf veilig op in de Vrijwillige Isolatie bunker.\n\n📋 3 MANIEREN OM VEILIG TE ZIJN:\n1. 🛡️ Vrijwillige Isolatie ('/vrijwillige-isolatie'): Kruip tijdelijk in de beveiligde bunker. Zolang je daar zit, kan NIEMAND op de Luchtplaats jou aanvallen!\n2. 🏦 Geld op De Bank: Als je €0 contant op zak hebt, kunnen aanvallers geen cent van je stelen.\n3. 🎯 Kopgeld Zetten ('/kopgeld'): Zet een premie op het hoofd van je pester, zodat sterke spelers hem voor jou verslaan!",
                        'links' => [
                            ['text' => 'Vrijwillige Isolatie', 'url' => route('isolation.index')],
                            ['text' => 'Kopgeld Plaatsen', 'url' => route('bounties.index')],
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
                        'a' => "💡 IN HET KORT: Bij Liquidatie 2.0 schiet je met echte kogels op een rivaal die op dezelfde afdeling staat, en roof je 45% van zijn contante geld én 7% van zijn bank!\n\n📋 ZO WERKT HET STAP VOOR STAP ('/liquidatie'):\n1. 🕵️ Zoek je Doelwit: Je kunt alleen schieten als je op precies dezelfde kliniek-afdeling staat. Gebruik 'Camera- & Gangpad Schaduwing' (€350.000, duurt 15 min) om te zien waar je doelwit is, en reis erheen.\n2. 🔫 Kogels Inzetten: Vanaf Dossier-Graad 25 (met 30% Koudbloedigheid) is 1 kogel al dodelijk (Genadeschot). Daaronder schiet je een salvo van minimaal 10 kogels.\n3. 💰 De Buit: Bij een voltreffer pak je 45% van zijn contante zakgeld én 7% van zijn bankrekening!\n\n🔥 PRO-TIP: Gratis kogels nodig? Kijk 3 korte video's in de Sponsorkamer en ontvang direct +50 gratis kogels!",
                        'links' => [
                            ['text' => 'Naar Liquidatie 2.0', 'url' => route('hitman.index')],
                            ['text' => 'Gratis Kogels (Sponsorkamer)', 'url' => route('sponsor.index')],
                        ],
                    ],
                    [
                        'q' => 'Wat gebeurt er als ik word neergeschoten (Brancard vs 10/10 Code Blauw)?',
                        'a' => "💡 IN HET KORT: De eerste 9 keer kun je direct doorspelen; pas bij de 10e keer kom je in Code Blauw (Coma) en heb je een Reanimatie-Serum nodig.\n\n📋 GEEN ZORGEN, ZO WERKT HET:\n• 🚑 Treffer 1 t/m 9 (Direct Doorspelen!): Je gaat NIET in coma. Je houdt 20 HP over, wordt naar een andere afdeling gebracht en krijgt 15 minuten een beschermend schild.\n• 💉 Treffer 10 van de 10 (Code Blauw): Pas bij 10/10 treffers krijg je een hartstilstand. Je koopt dan het Reanimatie-Serum (€1.500.000 of 75 Sterren ⭐) om direct weer met 100% HP en 0/10 treffers op te staan. Heb je geen €1,5 miljoen? Dan helpt de medische noodkrediet-regeling je altijd weer op de been!",
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
                        'a' => "💡 IN HET KORT: Wordt iemand uit jouw bende neergeschoten? Dan mag jouw hele bende 24 uur lang zonder regels wraak nemen op de dader!\n\n📋 VOORDELEN VAN BLOEDWRAAK:\n• Geen level-limiet en geen wachttijd tegen de dader;\n• Wie de dader terugpakt wint direct +50 Respect, €25.000 cash én +150 Bende-Respect voor de club!",
                    ],
                ],
            ],
            'economie' => [
                'title' => '🏦 Bank, Leningen, Reizen & Marktplaats',
                'icon' => 'fa-building-columns',
                'questions' => [
                    [
                        'q' => 'Hoe werken Storten, Opnemen, Zakgeld en Leningen bij De Bank?',
                        'a' => "💡 IN HET KORT: Op De Bank bewaar je veilig je geld, ontvang je elk kwartier gratis zakgeld en kun je geld lenen.\n\n📋 ALLE BANKFUNCTIES ('/bank'):\n• 📥 Geld Storten: Gratis en onbeperkt. Doe dit altijd meteen!\n• 📤 Geld Opnemen: Kost 5% opnamebelasting (slechts 3% voor VIP). Deze belasting gaat 100% in de pot van de Afdelingsloterij!\n• 🎁 Gratis Zakgeld (Elke 15 min): Spelers zonder bende krijgen elke 15 minuten automatisch €1.000 op hun bank (VIP tot €25.000 per 15 min).\n• 💳 Geld Lenen: Heb je snel kapitaal nodig voor een auto of wapen? Sluit een lening af tegen 15% rente.\n• 🔢 Bank Pincode: Stel een eigen 4-cijferige pincode in voor extra beveiliging van je kluis.",
                        'links' => [
                            ['text' => 'Naar De Bank', 'url' => route('bank.index')],
                        ],
                    ],
                    [
                        'q' => 'Hoe werkt Verplaatsen (Reizen) tussen afdelingen en hoe verdien ik +35 XP?',
                        'a' => "💡 IN HET KORT: Koop een bewaker om om naar een ander gebouw te lopen en krijg bij elke reis meteen +35 XP cadeau!\n\n📋 WAAROM REIZEN ('/essentials/verplaatsen')?\n• Je reist tussen Paviljoen 1, Paviljoen 2, Het Kookhuis, De Recreatiezaal en De Buitenplaats.\n• Elke reis geeft je direct +35 XP (dé snelste manier om als beginner in level te stijgen!).\n• VIP-leden krijgen altijd 50% korting op de reiskosten.",
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
                        'a' => "💡 IN HET KORT: Elke 14 dagen wint één speler de gigantische Loterij Jackpot, gevuld met alle bankbelastingen van de hele kliniek!\n\n📋 ZO WIN JE DE JACKPOT ('/loterij'):\n• 🎟️ Prijs per lot: €10.000 contant of 5 Sterren ⭐.\n• ⚡ PRO-TIP (Laatste Kwartier Korting): In de allerlaatste 15 minuten voor de trekking kosten loten maar de helft (€5.000 per stuk)!\n• 💰 Uitbetaling: De winnaar krijgt 30% veilig op de bank en 70% contant uitbetaald.",
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
                'title' => '👥 Bendes, Uurloon & Controlepunten',
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
                        'q' => 'Hoe werkt het Bende-Uurloon en hoe verdienen we elk uur automatisch geld?',
                        'a' => "💡 IN HET KORT: Zodra je in een bende zit, verdien je elk uur automatisch salaris uit de kiosken en panden van jouw bende!\n\n📋 WAAR KOMT HET UURLOON VANDAAN ('/gangs')?\n• Elke bende geeft een basisloon (€150/u) + €100/u per Smokkelkiosk.\n• Met geld uit de Bende-Kluis koopt de leider Bende-Vastgoed (zoals loodsen, villa's en forten die €1.000 tot €75.000+ per uur opleveren!).\n• Je opgebouwde loon wordt tot 7 dagen (168 uur) voor je bewaard tot je het claimt.",
                        'links' => [
                            ['text' => 'Naar Bendes', 'url' => route('gangs.index')],
                        ],
                    ],
                    [
                        'q' => 'Hoe werken Controlepunten en hoe verdien ik daar elke minuut geld + gratis Sterren mee?',
                        'a' => "💡 IN HET KORT: Verover een Controlepunt in de kliniek en verdien voor elke minuut dat jij de baas bent contant geld én een gratis Ster ⭐ na 30 minuten!\n\n📋 ZO WERKT HET ('/controlepunten'):\n• Klik op 'Overnemen' bij een controlepunt (kost energie).\n• Zolang jouw naam op het punt staat, tikt de geldteller elke minuut voor jou door.\n• Houd je het punt minstens 30 minuten vast voordat iemand anders het overneemt? Dan krijg je bovenop je geld ook nog +1 gratis Gouden Ster ⭐ uitbetaald!",
                        'links' => [
                            ['text' => 'Controlepunten Veroveren', 'url' => route('control-points.index')],
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
                        'a' => "💡 IN HET KORT: Raad de geheime 4-cijferige code met de 'hoger/lager' tips en zet eerst een Alarm Jammer aan zodat je niet in de cel belandt.\n\n📋 SLIM KLUIS KRAKEN ('/kluis-kraak'):\n• Na elke poging vertelt het spel of het geheime getal hoger of lager is.\n• 🔥 PRO-TIP: Koop altijd eerst een 'Alarm Jammer' op de kluispagina! Zolang die aanstaat gaat het alarm niet af bij een foute gok.\n• Heb je een voucher of actiecode van Discord? Die kun je ook op deze pagina inwisselen!",
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
                        'q' => 'Hoe win ik elke dag gratis Sterren met de 24 Uur Battle?',
                        'a' => "💡 IN HET KORT: Elke dag strijden alle spelers in 5 categorieën; wie om 00:00 uur in de top 3 staat, wint gratis Sterren ⭐!\n\n📋 DE 5 CATEGORIEËN ('/24-uur-battle'):\n1. Meeste Misdrijven gepleegd;\n2. Meeste Gevechten gewonnen;\n3. Meeste Respect verdiend;\n4. Meeste Casinowinst behaald;\n5. Meeste Arbeid gedaan in de werkplaats (super makkelijk voor beginners!).\n\n🔥 PRO-TIP: Als beginner kun je heel makkelijk winnen in categorie 5 (Werkplaats Arbeid) omdat grote criminelen vaak vergeten te werken!",
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
                'title' => '📻 Radio, Krant, Chat, Cinema & Vrienden',
                'icon' => 'fa-compass',
                'questions' => [
                    [
                        'q' => 'Hoe werken TBS FM Radio, De Krant, de Tijdlijn en de Cinema?',
                        'a' => "💡 IN HET KORT: Luister muziek tijdens het spelen, lees wie er vandaag is gearresteerd en kijk samen video's in de bioscoop!\n\n📋 GEZELLIGHEID IN DE KLINIEK:\n• 📻 TBS FM Radio ('/tbs-fm'): Luister live muziek en stuur een bericht (shoutout) naar alle spelers.\n• 📰 Het Afdelingsblad ('/afdelingsblad'): De dagelijkse krant met alle grote overvallen en loterijwinnaars.\n• 📱 De Tijdlijn ('/tijdlijn'): Deel berichten en like updates van medepatiënten.\n• 🍿 Cinema ('/cinema'): Kijk samen YouTube-video's in de virtuele filmzaal en chat live mee.",
                        'links' => [
                            ['text' => 'TBS FM Radio', 'url' => route('radio.index')],
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
                        'a' => "💡 IN HET KORT: Hier staan alle veelgebruikte woorden uit de kliniek op een rij in gewone mensentaal.\n\n📋 HET SIMPELE WOORDENBOEK:\n• ❤️ HP (Gezondheid): Je levensbalk van 0 tot 100.\n• 📊 XP (Ervaring): Punten waarmee je een Level omhoog gaat.\n• ⏱️ Cooldown: De korte wachttijd voordat je dezelfde knop weer mag indrukken.\n• 🕛 Reset (00:00 uur): Om middernacht beginnen alle dagtellers en het Behandelaarsgesprek weer opnieuw.\n• 🎭 Heist: Een grote geplande overval met een vluchtauto en een bivakmuts.\n• 🎯 Liquidatie: Op iemand schieten met kogels op dezelfde afdeling.\n• 🛡️ Backfire / Ricochet: Een speciaal vest waardoor een kogel terugketst op de schutter.\n• 💉 Code Blauw: Wanneer je 10 keer bent neergeschoten en een Reanimatie-Serum nodig hebt.\n• 👑 BFF: Jouw Beste Vriend in het spel die jou sneller uit de isoleercel kan breken.",
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
                'keywords' => ['beginnen', 'nieuw', 'start', 'begin', 'starten', 'uitleg', 'hoe werkt', 'eerste stappen', 'wat moet ik doen', 'tips', 'beginner', 'overleven', 'stappenplan', 'start tot einde', 'rookie', 'startgeld'],
                'answer' => "Welkom in de TBS Kliniek! Volg deze simpele eerste stappen:\n1. Ga naar de Ziekenboeg: Zuster Brenda geneest je 100% gratis.\n2. Draai gratis aan het Rad van Fortuin.\n3. GOUDEN REGEL: Zet AL je contant geld direct op De Bank zodat niemand je berooft!\n4. Doe de 5 Rookie Taken op je Dashboard voor €6.500 cash, 30 kogels en 1 dag gratis VIP!\n5. Werk veilig bij Werkmeester Henk in de Dagbesteding (0% kans op de isoleercel).",
                'links' => [
                    ['text' => 'Naar Dashboard', 'url' => route('dashboard')],
                    ['text' => 'Naar De Bank', 'url' => route('bank.index')],
                    ['text' => 'Naar Dagbesteding', 'url' => route('daycare.index')],
                ],
            ],
            [
                'title' => '⚡ Energie & Herstel',
                'keywords' => ['energie', 'energy', 'herstellen', 'op', 'leeg', 'weinig energie', 'kracht', 'snack', 'eten', 'chocola'],
                'answer' => 'Je hebt Energie nodig voor misdaden, vechten en trainen. Energie vult vanzelf aan: +1 punt per 5 minuten (elke 3 min voor VIP). Direct energie nodig? Eet een snack uit je inventaris, draai aan het Rad van Fortuin of haal een refill in de Sterren/VIP Shop!',
                'links' => [
                    ['text' => 'Kantine & Snacks', 'url' => route('shop.index', ['tab' => 'inventaris'])],
                    ['text' => 'VIP Shop', 'url' => route('vip.index')],
                ],
            ],
            [
                'title' => '❤️ Gezondheid (HP) & Ziekenboeg',
                'keywords' => ['gezondheid', 'health', 'hp', 'dood', 'gewond', 'genezen', 'healen', 'ziekenboeg', 'dokter', 'zuster', 'brenda', 'verband', 'ehbo', 'pillen'],
                'answer' => 'Is je Gezondheid (HP) laag? Ga naar de Ziekenboeg! Zuster Brenda geneest je daar direct en 100% GRATIS naar 100 HP. Je kunt ook EHBO-kits kopen in de winkel.',
                'links' => [
                    ['text' => 'Gratis Genezen bij Brenda', 'url' => route('infirmary.index')],
                    ['text' => 'Kliniek Winkel', 'url' => route('shop.index')],
                ],
            ],
            [
                'title' => '🏦 De Bank, Zakgeld & Leningen',
                'keywords' => ['bank', 'geld', 'storten', 'sparen', 'veilig', 'rente', 'beroofd', 'gestolen', 'kwijt', 'contant', 'cash', 'lening', 'lenen', 'zakgeld'],
                'answer' => 'Bewaar NOOIT contant geld in je broekzak! Op De Bank kan niemand je geld stelen via de Respectlijst of zakkenrollen. Zit je niet in een bende? Dan krijg je elke 15 minuten automatisch €1.000 gratis zakgeld op je bank (tot €25.000 voor VIP)!',
                'links' => [
                    ['text' => 'Naar De Bank', 'url' => route('bank.index')],
                ],
            ],
            [
                'title' => '⛓️ Isoleercel & Ontsnappen',
                'keywords' => ['isoleercel', 'gevangenis', 'cel', 'vast', 'straf', 'straftijd', 'gepakt', 'betrapt', 'vrijkopen', 'ontsnappen', 'deur forceren'],
                'answer' => "Zit je in de isoleercel? Je komt sneller vrij op 6 manieren:\n1. Luchtschacht-uitbraak proberen (20 energie);\n2. IJzerzaagje gebruiken (-30 min straf);\n3. Stoomwasserij werken (-45 sec);\n4. Bewaker omkopen (€2.500 voor -90 sec);\n5. Kijk een gratis video in de Sponsorkamer of koop je vrij met 25 Sterren;\n6. Vraag een vriend in de chat om je deur open te breken!",
                'links' => [
                    ['text' => 'Naar Isoleercel', 'url' => route('prison')],
                    ['text' => 'Sponsorkamer', 'url' => route('sponsor.index')],
                ],
            ],
            [
                'title' => '🥊 Respectlijst & Luchtplaats Vechten',
                'keywords' => ['respectlijst', 'vechten', 'luchtplaats', 'slaylist', 'slay', 'aanvallen', 'duel', 'arena', 'showdown', '100 aanvallen', 'respect'],
                'answer' => 'Op de Luchtplaats (Respectlijst) vecht je 5 seconden tegen een andere patiënt. Als je wint steel je zijn Respect én tot 15% van zijn contante zakgeld! Je mag 100 keer per dag aanvallen (VIP 1.000 keer).',
                'links' => [
                    ['text' => 'Naar Luchtplaats', 'url' => route('fight.index')],
                ],
            ],
            [
                'title' => '🛡️ Vrijwillige Isolatie (Bescherming)',
                'keywords' => ['vrijwillige isolatie', 'bescherming', 'bunker', 'veiligheid', 'aangevallen', 'te veel aanvallen', 'stop aanvallen', 'shield'],
                'answer' => "Word je steeds aangevallen? Zet al je geld op de bank én zet Vrijwillige Isolatie aan onder 'Geweld & Strijd'. Zolang je in de bunker zit, kan niemand op de Luchtplaats jou aanvallen!",
                'links' => [
                    ['text' => 'Vrijwillige Isolatie', 'url' => route('isolation.index')],
                ],
            ],
            [
                'title' => '🔨 Veilige Dagbesteding & Werkplaats',
                'keywords' => ['dagbesteding', 'werkplaats', 'werken', 'veilig werk', 'geen isoleercel', 'henk', 'loon', 'salaris', 'houtbewerking', 'wasserette', 'discipline'],
                'answer' => 'Bij Werkmeester Henk in de Werkplaats werk je 100% veilig en legaal! Je hebt 0% kans op de isoleercel, verdient altijd geld, bouwt Discipline op en kunt gratis kluiscodes vinden.',
                'links' => [
                    ['text' => 'Naar Dagbesteding', 'url' => route('daycare.index')],
                ],
            ],
            [
                'title' => '🧠 Behandelaarsgesprek & 3 Uur Verlof',
                'keywords' => ['behandelaar', 'behandelaarsgesprek', 'gesprek', 'verlof', 'psychiater', 'sedatie', 'buiten', 'supermarkt', 'station', 'dilemma'],
                'answer' => 'Geef elke dag na 00:00 uur een net en rustig antwoord bij het Behandelaarsgesprek. Scoor je 60% of hoger? Dan krijg je 3 UUR BEGELEID VERLOF met 3 speciale buiten-missies (tot €19.500 cash + een XXL Koffer)!',
                'links' => [
                    ['text' => 'Behandelaarsgesprek', 'url' => route('treatment.index')],
                ],
            ],
            [
                'title' => '🕵️ Illegale Acties: Hosselen, Smokkel & Diefstal',
                'keywords' => ['hosselen', 'smokkelen', 'dealen', 'wiet', 'drugs', 'diefstal', 'klusjes', 'afpersen', 'misdaad', 'misdrijf', 'perks'],
                'answer' => "• Hosselen: Snelle klusjes op de gang voor beginners.\n• Zakkenrollen: Steel geld én bivakmutsen (een masker halveert je pakkans!).\n• Smokkelen: Koop goederen in en verkoop ze met grote winst.\n• Perks: Activeer 24-uurs boosters zoals Grotere Broekzakken (+10% cash).",
                'links' => [
                    ['text' => 'Hosselen', 'url' => route('hustle.index')],
                    ['text' => 'Zakkenrollen', 'url' => route('pickpocket.index')],
                    ['text' => 'Smokkelen', 'url' => route('smuggle.index')],
                ],
            ],
            [
                'title' => '🔐 MedicijnKluis & Geheime Codes',
                'keywords' => ['kluis', 'kluis kraak', 'code', 'raden', 'medicijnkluis', 'jammer', 'alarm jammer', 'kluiscode', 'voucher'],
                'answer' => 'Raad de 4-cijferige code van de Centrale Medicijnkluis met de hoger/lager hints! Koop eerst een Alarm Jammer zodat het alarm niet afgaat. Ook kun je hier vouchers inwisselen.',
                'links' => [
                    ['text' => 'Kluis Kraken', 'url' => route('vault.index')],
                ],
            ],
            [
                'title' => '⛏️ Uitbraaktunnel Graven',
                'keywords' => ['tunnel', 'graven', 'uitbraaktunnel', 'schep', 'ontsnappingstunnel', 'zand dumpen', 'vrijheid', '100 meter'],
                'answer' => 'Koop een schep en graaf onder je celvloer. Dump het zand stiekem buiten. Bij 100 meter slaagt de Grote Uitbraak en win je veel geld + 25 gratis Sterren ⭐!',
                'links' => [
                    ['text' => 'Naar Uitbraaktunnel', 'url' => route('tunnel.index')],
                ],
            ],
            [
                'title' => '👥 Bendes, Uurloon, Controlepunten & Overstap Bonus',
                'keywords' => ['gang', 'bende', 'bendes', 'uurloon', 'gang kluis', 'bende kluis', 'gang wars', 'oorlog', 'bendeleider', 'oprichten', 'overstap', 'gang overstap', 'controlepunten', 'controlepunt'],
                'answer' => 'Vanaf Level 3 kun je een bende oprichten of joinen voor automatisch Bende-Uurloon uit panden! Verover ook Controlepunten voor geld per minuut (+1 Ster na 30 min). Kom je van een ander spel? Vraag via een ticket de Gang Overstap Bonus aan (€5 miljoen + 31 dagen VIP)!',
                'links' => [
                    ['text' => 'Naar Bendes', 'url' => route('gangs.index')],
                    ['text' => 'Controlepunten', 'url' => route('control-points.index')],
                ],
            ],
            [
                'title' => '🗺️ Verplaatsen (Reizen) & +35 XP',
                'keywords' => ['reizen', 'verplaatsen', 'paviljoen', 'locatie', 'kookhuis', 'luchtplaats', 'omkopen', 'bewaker omkopen', '35 xp'],
                'answer' => "Reis onder 'Economie > Verplaatsen' tussen de kliniekgebouwen. Elke reis geeft je direct +35 XP! VIPs betalen maar de helft.",
                'links' => [
                    ['text' => 'Verplaatsen (+35 XP)', 'url' => route('travel.index')],
                ],
            ],
            [
                'title' => '👑 Gratis VIP & Sponsorkamer',
                'keywords' => ['vip', 'gratis', 'ads', 'advertenties', 'sponsorkamer', 'vip punten', 'gouden naam', 'voordelen', 'kosten', 'prijs', 'donatie', 'buymeacoffee'],
                'answer' => "VIP is 100% GRATIS te verdienen! Koppel je Discord (3 dagen gratis VIP), voltooi de Rookie Taak op het Dashboard (1 dag VIP) of kijk korte 15s video's in de Sponsorkamer (24 uur gratis VIP per 250 ads + €2.500 per video)!",
                'links' => [
                    ['text' => 'Sponsorkamer', 'url' => route('sponsor.index')],
                    ['text' => 'VIP Shop', 'url' => route('vip.index')],
                ],
            ],
            [
                'title' => '🎰 Casino, Loterij & Minigames',
                'keywords' => ['casino', 'gokhal', 'gokken', 'roulette', 'hoger lager', 'nummerspel', 'slots', 'loterij', 'jackpot', 'memory', 'true crime', 'vier op een rij'],
                'answer' => 'Speel Roulette en Slots in het Casino, koop loten voor de 14-daagse Afdelingsloterij (50% korting in de laatste 15 minuten!) of speel Memory, True Crime en Vier-op-een-rij.',
                'links' => [
                    ['text' => 'Casino', 'url' => route('casino.index')],
                    ['text' => 'Afdelingsloterij', 'url' => route('lottery.index')],
                ],
            ],
            [
                'title' => '🏎️ Smokkel Garage & Grote Heists',
                'keywords' => ['garage', 'voertuig', 'auto', 'wagen', 'vluchtwagen', 'scooter', 'heist', 'heists', 'overval', 'repareren', 'conditie'],
                'answer' => 'Koop een vluchtvoertuig in de Smokkel Garage, houd de conditie op 100% door te repareren en voer met een bivakmuts Grote Heists uit op juweliers en banken!',
                'links' => [
                    ['text' => 'Smokkel Garage', 'url' => route('garage.index')],
                    ['text' => 'Grote Heists', 'url' => route('heists.index')],
                ],
            ],
            [
                'title' => '🎯 Liquidatie 2.0, Backfire-Vesten & Code Blauw',
                'keywords' => ['liquidatie', 'kogels', 'bullets', 'schieten', 'hitman', 'coma', 'code blauw', 'vest', 'backfire', 'ricochet', 'bloedwraak'],
                'answer' => 'Schaduw je doelwit, reis naar dezelfde afdeling en vuur kogels af om 45% contant + 7% bankgeld te roven! Draag een Backfire-vest om kogels terug te kaatsen of koop 24u Cash Bescherming in de VIP Shop.',
                'links' => [
                    ['text' => 'Naar Liquidatie 2.0', 'url' => route('hitman.index')],
                ],
            ],
            [
                'title' => '📦 Koffers Onderscheppen & XXL Koffers',
                'keywords' => ['koffer', 'koffers', 'kraken', 'xxl koffer', 'onderscheppen', 'breekijzer', 'lockpick'],
                'answer' => "Onderschep smokkelkoffers onder 'Voortgang > Koffers' en breek ze open met een breekijzer of lockpick. XXL Koffers bevatten tot €250.000, gratis Sterren en kogels!",
                'links' => [
                    ['text' => 'Koffers Onderscheppen', 'url' => route('packages.index')],
                ],
            ],
            [
                'title' => '⚙️ Account, Regels & Support Tickets',
                'keywords' => ['account', 'wachtwoord', 'naam wijzigen', 'kleur', 'dark mode', 'regels', 'vpn', 'multi', 'ticket', 'support', 'help', 'beheer'],
                'answer' => 'Je mag maximaal 1 account per persoon hebben (max 2 per IP voor familieleden, geen VPN). Vragen, hulp nodig of de Gang Overstap Bonus claimen? Maak direct een Support Ticket aan!',
                'links' => [
                    ['text' => 'Nieuw Ticket Maken', 'url' => route('help.tickets.create')],
                    ['text' => 'Account Instellingen', 'url' => route('settings.account')],
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
