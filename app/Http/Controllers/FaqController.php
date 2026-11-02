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
     * Get rich, highly indexed knowledge base specifically for the AI Helpbot widget (Zaalwacht Arjan).
     * Includes extensive keywords, synonyms, newb-friendly explanations, follow-up suggestions, and direct navigation links.
     */
    public static function getBotKnowledge(): array
    {
        $coreKnowledge = [
            [
                'title' => '🗺️ Beginnersgids: Stappenplan van Start tot Einde',
                'keywords' => ['beginnen', 'nieuw', 'start', 'begin', 'starten', 'uitleg', 'eerste stappen', 'tips', 'beginner', 'overleven', 'stappenplan', 'start tot einde', 'doel', 'spel uitleg'],
                'answer' => "💡 IN HET KORT: Welkom in FPC De Balk! Volg deze 5 simpele stappen om direct veilig rijk en sterk te worden:\n\n1. 🏥 Ziekenboeg: Zuster Brenda geneest je 100% gratis naar 100 HP.\n2. 🎡 Rad van Fortuin: Draai elke dag gratis voor cash, energie of sterren.\n3. 🏦 De Bank (GOUDEN REGEL): Stort AL je contant geld direct op de bank zodat niemand je berooft!\n4. 🎯 Rookie Taken: Voltooi de 5 startopdrachten op je Dashboard voor €6.500 cash, 30 kogels en 1 dag gratis VIP.\n5. 🔨 Dagbesteding: Werk veilig bij Werkmeester Henk (0% kans op de isoleercel!).\n\n🔥 PRO-TIP: Klik op '🎯 Advies op Maat' onderin de chat voor een persoonlijke check van jouw karakter!",
                'links' => [
                    ['text' => '🎯 Naar Dashboard', 'url' => route('dashboard')],
                    ['text' => '🏦 Naar De Bank', 'url' => route('bank.index')],
                    ['text' => '🔨 Dagbesteding', 'url' => route('daycare.index')],
                ],
                'suggestions' => [
                    'Hoe pak ik €35.000 gratis startgeld?',
                    'Hoe beveilig ik mijn geld op de bank?',
                    'Hoe verdien ik 100% gratis VIP?',
                ],
            ],
            [
                'title' => '🎁 €35.000+ Gratis Startgeld op Dag 1',
                'keywords' => ['startgeld', 'rookie', 'rookie taken', 'gratis geld', 'eerste dag', '35000', '35k', 'push', 'meldingen', 'notificaties', 'rad van fortuin', 'bonus', 'welkomstbonus'],
                'answer' => "💡 IN HET KORT: Op je allereerste dag ligt er voor meer dan €35.000 aan gratis geld, 30 kogels en gratis VIP voor je klaar!\n\n📋 ZO PAK JE ALLES OP DAG 1:\n• 🎯 5 Rookie Taken (Dashboard): Geeft €6.500 cash + 30 kogels + 150 XP + 1 dag gratis VIP.\n• 🔔 Push-Meldingen aanzetten (Dashboard): Geeft éénmalig €10.000 cash + 25 Gouden Sterren.\n• 🧠 Behandelaarsgesprek + 3u Verlof: Geeft tot €19.500 cash + een XXL Koffer.\n• 💬 Discord koppelen (Account): Geeft direct 3 dagen gratis VIP!\n\n🔥 PRO-TIP: Stort na elke beloning je cash meteen op De Bank!",
                'links' => [
                    ['text' => '🎯 Rookie Taken & Push Bonus', 'url' => route('dashboard')],
                    ['text' => '🧠 Behandelaarsgesprek', 'url' => route('treatment.index')],
                    ['text' => '🏦 Storten op De Bank', 'url' => route('bank.index')],
                ],
                'suggestions' => [
                    'Hoe werkt het Behandelaarsgesprek?',
                    'Hoe verdien ik 100% gratis VIP?',
                    'Hoe beveilig ik mijn geld op de bank?',
                ],
            ],
            [
                'title' => '⚡ Energie & Snel Herstellen',
                'keywords' => ['energie', 'energy', 'herstellen', 'leeg', 'weinig energie', 'kracht', 'snack', 'snacks', 'eten', 'chocola', 'kantine', 'refill', 'moe'],
                'answer' => "💡 IN HET KORT: Energie heb je nodig voor werken, misdaden, trainen en vechten. Het vult vanzelf aan én via snacks.\n\n📋 ZO KRIJG JE ENERGIE TERUG:\n• ⏱️ Automatisch: +1 Energie per 5 minuten (+1 per 3 minuten als VIP).\n• 🍫 Kantine Snacks: Koop en gebruik eten in de Kliniek Winkel (onder je Inventaris-tabblad).\n• 🎡 Rad van Fortuin & Dagelijkse Beloning: Gratis kans op directe energie.\n• ⭐ Sterren / VIP Shop: Koop een directe 100% Energie Refill.\n\n🔥 PRO-TIP: Laat je energie nooit urenlang op 100/100 vol staan; geef het uit in de Werkplaats of het Krachthonk!",
                'links' => [
                    ['text' => '🍫 Kantine & Inventaris', 'url' => route('shop.index', ['tab' => 'inventaris'])],
                    ['text' => '🎡 Rad van Fortuin', 'url' => route('daily.index')],
                    ['text' => '👑 VIP Shop', 'url' => route('vip.index')],
                ],
                'suggestions' => [
                    'Hoe genees ik gratis mijn gezondheid (HP)?',
                    'Hoe werkt veilig geld verdienen in de Dagbesteding?',
                    'Hoe train ik mijn Kracht en Verdediging?',
                ],
            ],
            [
                'title' => '❤️ Gezondheid (HP), Pillen Palmen & Ziekenboeg',
                'keywords' => ['gezondheid', 'health', 'levens', 'dood', 'gewond', 'genezen', 'healen', 'ziekenboeg', 'dokter', 'zuster', 'brenda', 'verband', 'ehbo', 'pillen', 'pillen palmen'],
                'answer' => "💡 IN HET KORT: Is je Gezondheid (HP) onder de 100? Zuster Brenda in de Ziekenboeg geneest je 100% GRATIS!\n\n📋 WAT KUN JE DOEN IN DE ZIEKENBOEG?\n• 💖 Gratis Verpleging: Klik bij Zuster Brenda op verzorgen en je hebt direct weer 100 HP.\n• 💊 Pillen Palmen: Smokkel stiekem medicatie achterover voor €350 tot €1.200 winst (kost 15 energie).\n• 🤝 Patiënten Verplegen: Help gewonde medepatiënten uit de brand voor extra beloningen.\n\n🔥 PRO-TIP: Vecht nooit op de Luchtplaats met lage HP. Als je 0 HP bereikt, raak je in coma!",
                'links' => [
                    ['text' => '🏥 Gratis Genezen bij Brenda', 'url' => route('infirmary.index')],
                    ['text' => '🛒 EHBO & Winkel', 'url' => route('shop.index')],
                ],
                'suggestions' => [
                    'Wat is Code Blauw en een coma?',
                    'Hoe bescherm ik mezelf met Vrijwillige Isolatie?',
                    'Hoe werkt vechten op de Respectlijst?',
                ],
            ],
            [
                'title' => '🏦 De Bank, Gratis Zakgeld & Leningen',
                'keywords' => ['bank', 'geld', 'storten', 'sparen', 'veilig', 'rente', 'beroofd', 'gestolen', 'kwijt', 'contant', 'cash', 'lening', 'lenen', 'zakgeld', 'beveiligen', 'overmaken'],
                'answer' => "💡 IN HET KORT: Bewaar NOOIT contant geld op zak! Op De Bank is je geld veilig tegen straatroof, zakkenrollers en de Luchtplaats.\n\n📋 ALLE VOORDELEN VAN DE BANK:\n• 🔒 Veilig Storten: Je hebt 5 gratis bankacties per dag (als VIP onbeperkt gratis storten).\n• 💶 Gratis Zakgeld (Zonder Bende): Zit je nog niet in een bende? Dan krijg je elke 15 minuten automatisch €1.000 gratis op je bank (tot €5.000 max, of €25.000 voor VIP)!\n• 💳 Leningen (vanaf Level 2): Leen €2.500 tot €25.000 voor een wapen, auto of schep. Let op: los op tijd af, anders houdt de bank 50% van je inkomsten in!\n\n🔥 PRO-TIP: Maak er een gewoonte van om vóór je uitlogt altijd op 'Alles Storten' te klikken.",
                'links' => [
                    ['text' => '🏦 Naar De Bank', 'url' => route('bank.index')],
                ],
                'suggestions' => [
                    'Hoe verdien ik snel en veilig geld?',
                    'Hoe werken Bendes en het Bende-Uurloon?',
                    'Hoe voorkom ik dat ik word aangevallen?',
                ],
            ],
            [
                'title' => '⛓️ Isoleercel & 6 Manieren om Snel te Ontsnappen',
                'keywords' => ['isoleercel', 'gevangenis', 'bajes', 'cel', 'vast', 'straf', 'straftijd', 'gepakt', 'betrapt', 'vrijkopen', 'ontsnappen', 'uitbreken', 'deur forceren', 'ijzerzaagje'],
                'answer' => "💡 IN HET KORT: Zit je vast na een mislukte misdaad? Je hoeft niet te wachten tot de klok op 0 staat!\n\n📋 6 MANIEREN OM SNELLER VRIJ TE KOMEN:\n1. 🌀 Luchtschacht-Uitbraak: Kost 20 energie; bij succes ben je meteen vrij.\n2. 🪚 IJzerzaagje: Haalt direct 30 minuten van je straf af.\n3. 🧺 Stoomwasserij: Werk in je cel om elke keer 45 seconden straf weg te spelen.\n4. 💵 Bewaker Omkopen: Betaal €2.500 voor 90 seconden strafvermindering.\n5. 📺 Sponsorkamer / Sterren: Kijk een korte video voor -30 min straf of koop jezelf direct vrij met Sterren ⭐.\n6. 🤝 Vrienden / BFF: Laat een medepatiënt of je BFF je celdeur van buitenaf openbreken!\n\n🔥 PRO-TIP: Draag een bivakmuts bij misdaden om je kans op de isoleercel met tot 50% te verlagen!",
                'links' => [
                    ['text' => '⛓️ Naar Isoleercel', 'url' => route('prison')],
                    ['text' => '📺 Sponsorkamer', 'url' => route('sponsor.index')],
                ],
                'suggestions' => [
                    'Hoe kom ik aan een bivakmuts (masker)?',
                    'Wat doet een BFF (Best Friend Forever)?',
                    'Waar kan ik 100% veilig werken zonder celkans?',
                ],
            ],
            [
                'title' => '🥊 Respectlijst, Luchtplaats & 24u Battles',
                'keywords' => ['respectlijst', 'vechten', 'luchtplaats', 'slaylist', 'slay', 'aanvallen', 'duel', 'arena', 'showdown', 'respect', 'battle', 'slaan', 'winnen'],
                'answer' => "💡 IN HET KORT: Op de Luchtplaats vecht je tegen andere spelers om hun Respect en tot 15% van hun contante zakgeld te roven!\n\n📋 ZO WIN JE GEVECHTEN:\n• ⚔️ Jouw Gevechtskracht = Kracht + Verdediging + Snelheid + Wapen + Pantser.\n• 🔢 Aanvalslimiet: Gratis spelers mogen 100x per dag aanvallen, VIPs 1.000x per dag (max 5x dezelfde speler per dag).\n• 🏆 24u Respect Battle: Daag een rivaal uit voor een 24-uurs wedstrijd wie het meeste Respect verdient voor een geldpot!\n\n🔥 PRO-TIP: Val spelers aan die veel contant geld op zak hebben én een lagere kracht dan jij!",
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
                'title' => '🛡️ Vrijwillige Isolatie & Cash Bescherming',
                'keywords' => ['vrijwillige isolatie', 'bescherming', 'bunker', 'veiligheid', 'aangevallen', 'stop aanvallen', 'shield', 'cash bescherming', 'met rust gelaten', 'veilig spelen'],
                'answer' => "💡 IN HET KORT: Word je steeds aangevallen? Gebruik de Bank, Vrijwillige Isolatie of 24u Cash Bescherming!\n\n📋 3 MANIEREN OM VEILIG TE SPELEN:\n1. 🏦 Alles op De Bank: Wie €0 contant op zak heeft, levert aanvallers niets op.\n2. 🛡️ Vrijwillige Isolatie: Zet dit gratis aan onder 'Geweld & Strijd > Vrijwillige Isolatie'. Zolang je in de bunker zit, kan niemand op de Luchtplaats je aanvallen!\n3. 👑 24u Cash Bescherming (VIP Shop): Beschermt je 24 uur lang tegen geldroof bij liquidaties.\n\n🔥 PRO-TIP: Train rustig je Verdediging in het Krachthonk terwijl je geld veilig op de bank staat.",
                'links' => [
                    ['text' => '🛡️ Vrijwillige Isolatie', 'url' => route('isolation.index')],
                    ['text' => '🏦 Naar De Bank', 'url' => route('bank.index')],
                    ['text' => '👑 VIP Shop Bescherming', 'url' => route('vip.index')],
                ],
                'suggestions' => [
                    'Wat doet een Backfire-vest tegen kogels?',
                    'Hoe train ik mijn Verdediging?',
                    'Hoe werkt veilig werken in de Dagbesteding?',
                ],
            ],
            [
                'title' => '🏋️ Krachthonk, Boksring & Discipline Trainen',
                'keywords' => ['trainen', 'training', 'krachthonk', 'sportschool', 'gym', 'kracht', 'verdediging', 'snelheid', 'discipline', 'sterker', 'stats', 'statistieken'],
                'answer' => "💡 IN HET KORT: In het Krachthonk zet je Energie om in blijvende Kracht, Verdediging, Snelheid en Discipline!\n\n📋 WAT DOET ELKE STAT?\n• 💪 Kracht: Je deelt veel hardere klappen uit in gevechten.\n• 🛡️ Verdediging: Je vangt klappen op en verliest minder snel HP.\n• ⚡ Snelheid: Je slaat als eerste toe en ontwijkt aanvallen makkelijker.\n• 🧠 Discipline: Verhoogt je slaagkans bij de Behandelaar, Werkplaats en Heists.\n\n🔥 PRO-TIP: Koop ook een goed Wapen en Pantser in de Kliniek Winkel voor een enorme directe boost!",
                'links' => [
                    ['text' => '🏋️ Naar Krachthonk', 'url' => route('training.index')],
                    ['text' => '🛒 Wapens & Pantsers', 'url' => route('shop.index')],
                ],
                'suggestions' => [
                    'Hoe werkt vechten op de Respectlijst?',
                    'Hoe kom ik snel aan meer Energie?',
                    'Wat zijn 24-uurs Perks (Boosters)?',
                ],
            ],
            [
                'title' => '🔨 Veilige Dagbesteding & Werkplaats (0% Celkans)',
                'keywords' => ['dagbesteding', 'werkplaats', 'werken', 'veilig werk', 'geen isoleercel', 'henk', 'werkmeester', 'loon', 'salaris', 'houtbewerking', 'wasserette'],
                'answer' => "💡 IN HET KORT: Bij Werkmeester Henk in de Dagbesteding verdien je 100% veilig geld zonder ooit in de isoleercel te belanden!\n\n📋 WAAROM WERKEN IN DE WERKPLAATS?\n• ✅ 0% kans op straf of de isoleercel.\n• 💶 Altijd gegarandeerd loon + XP + extra Discipline.\n• 🔑 Kans om tijdens het werk geheime cijfers voor de Medicijnkluis of voorwerpen te vinden!\n\n🔥 PRO-TIP: Ideaal voor beginners én wanneer je even zonder risico wilt sparen voor een wapen of auto.",
                'links' => [
                    ['text' => '🔨 Naar Dagbesteding', 'url' => route('daycare.index')],
                ],
                'suggestions' => [
                    'Hoe werkt het Behandelaarsgesprek en Verlof?',
                    'Hoe kraak ik de Centrale Medicijnkluis?',
                    'Hoe werkt Hosselen en Zakkenrollen?',
                ],
            ],
            [
                'title' => '🧠 Behandelaarsgesprek & 3 Uur Begeleid Verlof',
                'keywords' => ['behandelaar', 'behandelaarsgesprek', 'gesprek', 'verlof', 'psychiater', 'sedatie', 'buiten', 'supermarkt', 'station', 'dilemma', 'begeleid verlof'],
                'answer' => "💡 IN HET KORT: Doe elke dag na 00:00 uur je Behandelaarsgesprek. Kies het rustige antwoord voor 3 uur Begeleid Verlof (€19.500+ buit)!\n\n📋 ZO WERKT HET:\n1. 🗣️ Kies bij de psychiater altijd het beleefde, meewerkende en rustige antwoord.\n2. 🎫 Scoor je 60% of hoger? Dan krijg je 3 UUR BEGELEID VERLOF buiten de kliniek!\n3. 🌆 Tijdens je verlof kun je 3 exclusieve buiten-missies doen (Supermarkt, Station en Juwelier) voor tot €19.500 cash én een XXL Koffer!\n\n🔥 PRO-TIP: Word niet brutaal tegen de behandelaar, anders krijg je 30 minuten Sedatie (-20% energieherstel).",
                'links' => [
                    ['text' => '🧠 Naar Behandelaarsgesprek', 'url' => route('treatment.index')],
                ],
                'suggestions' => [
                    'Wat zit er in een XXL Koffer?',
                    'Hoe pak ik €35.000 gratis startgeld?',
                    'Hoe werkt de Uitbraaktunnel?',
                ],
            ],
            [
                'title' => '🕵️ Hosselen, Zakkenrollen & Bivakmutsen (Maskers)',
                'keywords' => ['hosselen', 'zakkenrollen', 'stelen', 'beroven', 'bivakmuts', 'masker', 'maskers', 'skibril', 'gasmasker', 'pakkans', 'diefstal', 'klusjes', 'misdaad'],
                'answer' => "💡 IN HET KORT: Met Hosselen en Zakkenrollen verdien je snel zwart geld, XP en bivakmutsen die je pakkans tot 50% verlagen!\n\n📋 HOE WERKT HET SLIMST?\n• 🧢 Hosselen: Snelle klusjes op de afdeling voor cash en XP.\n• 🧤 Zakkenrollen: Steel cash én Bivakmutsen van doelwitten.\n• 🥷 Bivakmuts opzetten: Een masker verbergt je gezicht op camera's (tot -50% kans op de isoleercel) en is verplicht voor Grote Heists!\n\n🔥 PRO-TIP: Zet eerst een masker op in je inventaris voordat je zware klussen of Heists uitvoert.",
                'links' => [
                    ['text' => '🧢 Naar Hosselen', 'url' => route('hustle.index')],
                    ['text' => '🧤 Naar Zakkenrollen', 'url' => route('pickpocket.index')],
                    ['text' => '🛒 Maskers & Winkel', 'url' => route('shop.index')],
                ],
                'suggestions' => [
                    'Hoe werken Grote Heists en vluchtauto\'s?',
                    'Hoe werkt Smokkelen en Perks?',
                    'Hoe kom ik snel uit de isoleercel?',
                ],
            ],
            [
                'title' => '💊 Smokkelen, Koersen & 24-Uurs Perks (Boosters)',
                'keywords' => ['smokkelen', 'smokkel', 'dealen', 'wiet', 'drugs', 'handel', 'koers', 'perks', 'perk', 'booster', 'boosters', 'broekzakken', ' handelaar'],
                'answer' => "💡 IN HET KORT: Koop smokkelwaar goedkoop in wanneer de koers laag staat, en verkoop het met dikke winst als de prijs hoog is!\n\n📋 SLIM HANDELEN & PERKS:\n• 📈 Smokkelmarkt: Prijzen wisselen continu. Koop laag in en verkoop hoog.\n• ⚡ 24-Uurs Perks: Activeer tijdelijke boosters zoals 'Grotere Broekzakken' (+10% meer cash uit klussen), ' gladde praatjes' (lagere pakkans) of 'Scherpschutter'.\n\n🔥 PRO-TIP: Combineer Smokkelen met Verplaatsen (+35 XP per reis) tussen de gebouwen!",
                'links' => [
                    ['text' => '💊 Naar Smokkelen', 'url' => route('smuggle.index')],
                    ['text' => '⚡ Naar Perks', 'url' => route('perks.index')],
                ],
                'suggestions' => [
                    'Hoe werkt Verplaatsen (+35 XP per reis)?',
                    'Hoe werkt de Smokkel Garage?',
                    'Hoe beveilig ik mijn winst op de bank?',
                ],
            ],
            [
                'title' => '🔐 MedicijnKluis Kraken, Alarm Jammer & Vouchers',
                'keywords' => ['kluis', 'kluis kraken', 'medicijnkluis', 'code', 'raden', 'jammer', 'alarm jammer', 'kluiscode', 'voucher', 'vouchers', 'promocode'],
                'answer' => "💡 IN HET KORT: Raad de geheime 4-cijferige code van de Centrale Medicijnkluis met de Hoger/Lager hints en win de hele jackpot!\n\n📋 ZO KRAAK JE DE KLUIS:\n1. 📡 Koop eerst een Alarm Jammer in de winkel zodat je niet in de isoleercel belandt bij een foute poging.\n2. 🔢 Vul 4 cijfers in en volg de Hoger/Lager aanwijzingen om de code te vinden.\n3. 🎟️ Heb je een actiecode of Voucher uit Discord? Die kun je ook op de Kluis-pagina inwisselen voor gratis buit!",
                'links' => [
                    ['text' => '🔐 Kluis Kraken & Vouchers', 'url' => route('vault.index')],
                    ['text' => '🛒 Koop Alarm Jammer', 'url' => route('shop.index')],
                ],
                'suggestions' => [
                    'Hoe werkt de Uitbraaktunnel graven?',
                    'Hoe onderschep ik XXL Koffers?',
                    'Hoe verdien ik gratis Gouden Sterren?',
                ],
            ],
            [
                'title' => '⛏️ Uitbraaktunnel Graven (100 Meter & +25 Sterren)',
                'keywords' => ['tunnel', 'graven', 'uitbraaktunnel', 'schep', 'ontsnappingstunnel', 'zand', 'zand dumpen', '100 meter', 'grote uitbraak'],
                'answer' => "💡 IN HET KORT: Graaf onder je celvloer een tunnel van 100 meter naar buiten voor een enorme geldbeloning én +25 gratis Gouden Sterren ⭐!\n\n📋 STAP VOOR STAP GRAVEN:\n1.  🛠️ Koop een Schep in de winkel.\n2. ⛏️ Graaf elke dag meters onder je celvloer.\n3. 🪣 Zand Dumpen: Zitten je broekzakken vol met zand? Dump het zand eerst stiekem buiten op de Luchtplaats voordat de bewakers het zien!\n4. 🏆 Bij 100 meter slaagt de Grote Uitbraak: je wint een grote som cash + 25 Sterren!",
                'links' => [
                    ['text' => '⛏️ Naar Uitbraaktunnel', 'url' => route('tunnel.index')],
                    ['text' => '🛒 Koop een Schep', 'url' => route('shop.index')],
                ],
                'suggestions' => [
                    'Waarvoor gebruik ik Gouden Sterren?',
                    'Hoe werkt het Behandelaarsgesprek?',
                    'Hoe kraak ik de Medicijnkluis?',
                ],
            ],
            [
                'title' => '👥 Bendes, Automatisch Uurloon & Gang Wars',
                'keywords' => ['gang', 'bende', 'bendes', 'uurloon', 'bende uurloon', 'gang kluis', 'bende kluis', 'gang wars', 'oorlog', 'bendeleider', 'oprichten', 'joinen', 'clan'],
                'answer' => "💡 IN HET KORT: Vanaf Level 3 kun je een Bende joinen of oprichten. Leden krijgen elk uur automatisch Bende-Uurloon uit de panden van de bende!\n\n📋 WAAROM EEN BENDE JOINEN?\n• 💶 Automatisch Bende-Uurloon: Hoe meer panden en upgrades jullie bende bezit, hoe meer geld elk lid ieder uur automatisch ontvangt.\n• 💬 Eigen Bende-Chat & Kluis: Overleg privé met je team en spaar samen.\n• ⚔️ Gang Wars & Controlepunten: Vecht samen tegen rivaliserende bendes voor roem en terrein!\n\n🔥 PRO-TIP: Nog geen Level 3? Tot die tijd krijg je op De Bank elke 15 minuten €1.000 gratis zakgeld!",
                'links' => [
                    ['text' => '👥 Naar Bendes', 'url' => route('gangs.index')],
                    ['text' => '🚩 Controlepunten', 'url' => route('control-points.index')],
                ],
                'suggestions' => [
                    'Hoe werken Controlepunten veroveren?',
                    'Wat is de Gang Overstap Bonus (€5 miljoen)?',
                    'Hoe kom ik snel op Level 3?',
                ],
            ],
            [
                'title' => '🚩 Controlepunten Veroveren (Geld per Minuut & Sterren)',
                'keywords' => ['controlepunt', 'controlepunten', 'veroveren', 'territorium', 'gebied', 'geld per minuut', '30 minuten', 'bewaken'],
                'answer' => "💡 IN HET KORT: Verover Controlepunten in de kliniek om elke minuut automatisch contant geld te verdienen én gratis Gouden Sterren te pakken!\n\n📋 ZO WERKEN CONTROLEPUNTEN:\n• 🏴 Veroveren: Neem een locatie over onder 'Geweld & Strijd > Controlepunten'.\n• 💸 Passief Inkomen: Zolang jij (of je bende) het punt vasthoudt, tikt er elke minuut geld binnen.\n• ⭐ Bonus na 30 Minuten: Houd je een controlepunt 30 minuten achter elkaar vast? Dan krijg je +1 gratis Gouden Ster!",
                'links' => [
                    ['text' => '🚩 Naar Controlepunten', 'url' => route('control-points.index')],
                ],
                'suggestions' => [
                    'Hoe werken Bendes en Bende-Uurloon?',
                    'Hoe train ik mijn gevechtskracht?',
                    'Wat is de Gang Overstap Bonus?',
                ],
            ],
            [
                'title' => '🚚 Gang Overstap Bonus (€5 Miljoen + 31 Dagen VIP)',
                'keywords' => ['overstap', 'gang overstap', 'overstap bonus', '5 miljoen', 'ander spel', 'vriendengroep', 'overstappen', 'clan bonus'],
                'answer' => "💡 IN HET KORT: Kom je met je vriendengroep of bende (minimaal 4 spelers) overstappen vanuit een ander tekst- of maffiaspel? Dan krijgen jullie de Gang Overstap Bonus!\n\n📋 WAT KRIJGEN JULLIE GRATIS?\n• 💰 €5.000.000 (€5 miljoen) startkapitaal in jullie Bende-kluis!\n• 👑 31 Dagen GRATIS VIP voor de overstappende bendeleden!\n• 🎫 Aanvragen: Maak met je leider even een Support Ticket aan en noem de namen van je teamleden.",
                'links' => [
                    ['text' => '🎫 Overstap Bonus Aanvragen', 'url' => route('help.tickets.create')],
                    ['text' => '👥 Bende Overzicht', 'url' => route('gangs.index')],
                ],
                'suggestions' => [
                    'Hoe werken Bendes en Bende-Uurloon?',
                    'Wat zijn alle voordelen van VIP?',
                    'Wat zijn de spelregels van TBS Kliniek?',
                ],
            ],
            [
                'title' => '🗺️ Verplaatsen (Reizen tussen Gebouwen voor +35 XP)',
                'keywords' => ['reizen', 'verplaatsen', 'paviljoen', 'locatie', 'kookhuis', 'afdeling', 'gebouwen', '35 xp', 'snel levelen', 'xp verdienen'],
                'answer' => "💡 IN HET KORT: Reis onder 'Economie > Verplaatsen' tussen de kliniekgebouwen. Elke reis geeft je direct +35 XP!\n\n📋 WAAROM VERPLAATSEN?\n• 🚀 Snel Levelen: +35 XP bij elke verplaatsing is de snelste manier om Level 3 (Bendes) te halen.\n• 🎯 Liquidatie 2.0: Om op een doelwit te schieten moet je eerst naar zijn of haar gebouw reizen.\n• 👑 VIP Voordeel: VIP-spelers betalen 50% minder reiskosten!",
                'links' => [
                    ['text' => '🗺️ Verplaatsen (+35 XP)', 'url' => route('travel.index')],
                ],
                'suggestions' => [
                    'Hoe werkt Liquidatie 2.0?',
                    'Hoe verdien ik 100% gratis VIP?',
                    'Hoe werken Bendes vanaf Level 3?',
                ],
            ],
            [
                'title' => '👑 100% Gratis VIP, Sterren & Sponsorkamer',
                'keywords' => ['vip', 'gratis vip', 'ads', 'advertenties', 'sponsorkamer', 'vip punten', 'gouden naam', 'voordelen', 'sterren', 'gouden sterren', 'donatie', 'video'],
                'answer' => "💡 IN HET KORT: Je hoeft in TBS Kliniek nooit echt geld uit te geven: VIP en Gouden Sterren zijn 100% gratis in het spel te verdienen!\n\n📋 4 MANIEREN VOOR GRATIS VIP:\n1. 💬 Discord Koppelen (Account): Geeft direct 3 dagen gratis VIP!\n2. 🎯 Rookie Taken (Dashboard): Geeft direct 1 dag gratis VIP!\n3. 📺 Sponsorkamer: Kijk korte video's van 15s voor €2.500 cash per video én 24 uur gratis VIP bij 250 video's.\n4. ⭐ Gouden Sterren: Verdien sterren via het Rad, Koffers, Controlepunten en de Tunnel en wissel ze in bij de VIP Shop!\n\n🔥 VIP VOORDELEN: Sneller energieherstel (elke 3 min), 1.000x per dag aanvallen, onbeperkt bankstorten en 50% reiskorting!",
                'links' => [
                    ['text' => '📺 Naar Sponsorkamer', 'url' => route('sponsor.index')],
                    ['text' => '👑 VIP & Sterren Shop', 'url' => route('vip.index')],
                    ['text' => '⚙️ Koppel Discord (+3d VIP)', 'url' => route('settings.account')],
                ],
                'suggestions' => [
                    'Hoe pak ik €35.000 gratis startgeld?',
                    'Wat is de Gang Overstap Bonus?',
                    'Hoe graaf ik de Uitbraaktunnel voor +25 Sterren?',
                ],
            ],
            [
                'title' => '🎰 Casino, Afdelingsloterij (50% Korting!) & Minigames',
                'keywords' => ['casino', 'gokhal', 'gokken', 'roulette', 'hoger lager', 'nummerspel', 'slots', 'loterij', 'afdelingsloterij', 'jackpot', 'memory', 'true crime', 'vier op een rij', 'minigames'],
                'answer' => "💡 IN HET KORT: Waag een gokje in het Casino, speel hersenspellen of pak de 14-daagse Afdelingsloterij jackpot!\n\n📋 ALLE SPELLEN & GEHEIME TIPS:\n• 🎟️ Afdelingsloterij: Elke 14 dagen valt de megapot. GEHEIME TIP: In de laatste 15 minuten vóór de trekking zijn loten 50% GOEDKOPER (€500 i.p.v. €1.000)!\n• 🎰 Casino: Speel Roulette, Slots, Hoger/Lager en het Nummerspel.\n• 🧠 Minigames: Speel Kliniek Memory, True Crime Detective of daag een speler uit voor Vier-op-een-rij!",
                'links' => [
                    ['text' => '🎰 Naar Casino', 'url' => route('casino.index')],
                    ['text' => '🎟️ Afdelingsloterij', 'url' => route('lottery.index')],
                    ['text' => '🧠 Kliniek Memory', 'url' => route('memory.index')],
                ],
                'suggestions' => [
                    'Hoe beveilig ik mijn winst op de bank?',
                    'Hoe kraak ik de Centrale Medicijnkluis?',
                    'Hoe onderschep ik XXL Koffers?',
                ],
            ],
            [
                'title' => '🏎️ Smokkel Garage, Voertuigen Repareren & Grote Heists',
                'keywords' => ['garage', 'smokkel garage', 'voertuig', 'auto', 'wagen', 'vluchtwagen', 'scooter', 'heist', 'heists', 'overval', 'repareren', 'conditie'],
                'answer' => "💡 IN HET KORT: Voor Grote Heists (overvallen op juweliers en banken) heb je twee dingen nodig: een Vluchtauto in je Garage én een Bivakmuts!\n\n📋 ZO VOER JE EEN SUCCESVOLLE HEIST UIT:\n1. 🏎️ Koop een vluchtvoertuig in de Smokkel Garage.\n2. 🔧 Conditie 100%: Repareer je voertuig na ritten zodat de motor niet afslaat tijdens de vlucht.\n3. 🥷 Zet een Bivakmuts op en start je Grote Heist voor tienduizenden euro's buit!",
                'links' => [
                    ['text' => '🏎️ Smokkel Garage', 'url' => route('garage.index')],
                    ['text' => '🎭 Grote Heists', 'url' => route('heists.index')],
                ],
                'suggestions' => [
                    'Hoe kom ik aan een Bivakmuts (masker)?',
                    'Hoe kan ik geld lenen bij De Bank?',
                    'Hoe train ik mijn Discipline voor hogere slaagkans?',
                ],
            ],
            [
                'title' => '🎯 Liquidatie 2.0, Backfire-Vesten, Bloedwraak & Code Blauw',
                'keywords' => ['liquidatie', 'kogels', 'bullets', 'schieten', 'hitman', 'coma', 'code blauw', 'serum', 'vest', 'backfire', 'ricochet', 'bloedwraak', 'schaduwen', 'moord'],
                'answer' => "💡 IN HET KORT: Met Liquidatie 2.0 kun je op een rivaal schieten om tot 45% van zijn contante geld én 7% van zijn bankgeld te roven!\n\n📋 ZO WERKT SCHIETEN & VERDEDIGEN:\n• 🕵️ Schaduwen & Reizen: Zoek eerst uit in welk gebouw je doelwit zit en reis naar dezelfde afdeling.\n• 💥 Vuren: Vuur kogels af. Bij een voltreffer pak je 45% cash + 7% bankgeld!\n• 🛡️ Backfire-Vest (Ricochet): Draagt je doelwit een Backfire-vest? Dan kaatst de kogel terug op de schutter!\n• 🩸 Bloedwraak: Word je beschoten? Dan krijg je tijdelijk een schadekorting/bonus om terug te slaan.\n• 💉 Code Blauw (Coma): Na 10 treffers beland je in Code Blauw en heb je een Reanimatie-Serum of de Ziekenboeg nodig.",
                'links' => [
                    ['text' => '🎯 Naar Liquidatie 2.0', 'url' => route('hitman.index')],
                    ['text' => '🗺️ Verplaatsen naar Doelwit', 'url' => route('travel.index')],
                    ['text' => '👑 24u Cash Bescherming', 'url' => route('vip.index')],
                ],
                'suggestions' => [
                    'Hoe bescherm ik mijn geld tegen liquidaties?',
                    'Hoe kom ik gratis aan kogels?',
                    'Hoe werkt Verplaatsen tussen gebouwen?',
                ],
            ],
            [
                'title' => '📦 Koffers Onderscheppen & XXL Koffers',
                'keywords' => ['koffer', 'koffers', 'kraken', 'xxl koffer', 'xxl', 'onderscheppen', 'breekijzer', 'lockpick', 'buitkrat'],
                'answer' => "💡 IN HET KORT: Onderschep binnengesmokkelde koffers en breek ze open voor cash, wapens, kogels en Gouden Sterren!\n\n📋 SOORTEN KOFFERS:\n• 🧰 Gewone Smokkelkoffers: Breek ze open onder 'Voortgang > Koffers' met een breekijzer of lockpick.\n• 🎁 XXL Koffers: De meest waardevolle koffers in het spel! Bevatten tot €250.000 cash, gratis Sterren en zeldzame items (te winnen bij o.a. 3 uur Begeleid Verlof).",
                'links' => [
                    ['text' => '📦 Koffers Onderscheppen', 'url' => route('packages.index')],
                ],
                'suggestions' => [
                    'Hoe krijg ik 3 uur Begeleid Verlof?',
                    'Hoe kraak ik de Centrale Medicijnkluis?',
                    'Waarvoor gebruik ik Gouden Sterren?',
                ],
            ],
            [
                'title' => '🤝 Vrienden, BFF (Best Friend) & Kliniek Radio / Cinema',
                'keywords' => ['vrienden', 'vriend', 'bff', 'best friend', 'radio', 'cinema', 'bioscoop', 'muziek', 'chat', 'sociaal', 'hartjes'],
                'answer' => "💡 IN HET KORT: Samen sta je veel sterker in de kliniek! Voeg vrienden toe en kies één BFF (Best Friend Forever).\n\n📋 VOORDELEN VAN VRIENDEN & BFF:\n• 🤝 Cel Uitbreken: Vrienden en je BFF kunnen jouw isoleerceldeur veel makkelijker van buitenaf openbreken!\n• 📻 Kliniek Radio & Cinema: Luister samen muziek of kijk video's tijdens het spelen.\n• 💬 Live Chat: Gebruik het Plein of stuur directe privéberichten (DM) in deze chatbox!",
                'links' => [
                    ['text' => '🤝 Vrienden & BFF', 'url' => route('friends.index')],
                    ['text' => '📻 Kliniek Radio', 'url' => route('radio.index')],
                ],
                'suggestions' => [
                    'Hoe kom ik snel uit de isoleercel?',
                    'Hoe werken Bendes vanaf Level 3?',
                    'Wat zijn de spelregels in de chat?',
                ],
            ],
            [
                'title' => '📚 Simpel Woordenboek (HP, XP, Cooldown, Ricochet, BFF)',
                'keywords' => ['woordenboek', 'begrippen', 'termen', 'betekenis', 'afkortingen', 'cooldown', 'reset', 'xp', 'hp'],
                'answer' => "💡 IN HET KORT: Alle veelgebruikte kliniek-termen simpel uitgelegd:\n\n• ❤️ HP (Gezondheid): Je levensbalk van 0 tot 100 (gratis vullen bij Zuster Brenda).\n• 📊 XP (Ervaring): Punten waarmee je een Level omhoog gaat (+35 XP per reis!).\n• ⏱️ Cooldown: De korte wachttijd voordat je een actie opnieuw kunt doen.\n• 🕛 Reset (00:00u): Om middernacht resetten je dagelijkse pogingen en het Behandelaarsgesprek.\n• 🎭 Heist: Grote overval met vluchtauto + bivakmuts.\n• 🛡️ Backfire / Ricochet: Vest dat vijandelijke kogels terugkaatst op de schutter.\n• 💉 Code Blauw: Coma na 10 kogeltreffers.",
                'links' => [
                    ['text' => '📖 Volledige Gids & FAQ', 'url' => route('faq.index')],
                ],
                'suggestions' => [
                    'Wat moet ik doen op Dag 1 als beginner?',
                    'Hoe werkt Liquidatie 2.0?',
                    'Hoe werken Grote Heists?',
                ],
            ],
            [
                'title' => '⚙️ Account, Spelregels (Geen VPN) & Support Tickets',
                'keywords' => ['account', 'wachtwoord', 'naam wijzigen', 'kleur', 'dark mode', 'regels', 'spelregels', 'vpn', 'multi', 'dubbelaccount', 'ticket', 'support', 'beheer', 'melden'],
                'answer' => "💡 IN HET KORT: Speel eerlijk en houd het gezellig. Heb je hulp nodig of wil je de Gang Overstap Bonus claimen? Open een Support Ticket!\n\n📋 DE 3 GOUDEN SPELREGELS:\n1. 👤 Maximaal 1 account per persoon (multi-accounts worden direct geblokkeerd).\n2. 🏠 Maximaal 2 accounts op hetzelfde IP-adres (voor familie/huisgenoten; meld 3+ vooraf via een ticket).\n3. 🚫 Gebruik géén VPN of Proxy.\n\n🚩 Scheldt iemand in de chat? Klik op het rode vlaggetje bij het bericht of open een Support Ticket!",
                'links' => [
                    ['text' => '🎫 Nieuw Support Ticket', 'url' => route('help.tickets.create')],
                    ['text' => '⚙️ Account Instellingen', 'url' => route('settings.account')],
                ],
                'suggestions' => [
                    'Wat is de Gang Overstap Bonus?',
                    'Hoe koppel ik Discord voor 3 dagen gratis VIP?',
                    'Wat moet ik doen als beginner?',
                ],
            ],
        ];

        // Append any specific Q&A items from getFaqData() so Zaalwacht Arjan can answer every single question on the FAQ page
        $stopWords = [
            'hoe', 'wat', 'waar', 'waarom', 'wanneer', 'wie', 'welke', 'kan', 'kun', 'krijg',
            'heb', 'ben', 'word', 'moet', 'mag', 'wil', 'doen', 'doe', 'ik', 'je', 'jij', 'mijn',
            'de', 'het', 'een', 'en', 'of', 'in', 'op', 'bij', 'naar', 'van', 'voor', 'met', 'uit',
            'aan', 'om', 'te', 'er', 'niet', 'geen', 'wel', 'ook', 'nog', 'al', 'als', 'dan', 'maar',
            'dat', 'dit', 'zijn', 'is', 'werkt', 'werken',
        ];

        foreach (self::getFaqData() as $cat) {
            foreach ($cat['questions'] ?? [] as $qa) {
                $qText = $qa['q'] ?? '';
                if ($qText === '') {
                    continue;
                }

                $words = preg_split('/[^a-z0-9]+/i', strtolower($qText)) ?: [];
                $keywords = array_values(array_unique(array_filter($words, function (string $w) use ($stopWords): bool {
                    return strlen($w) >= 3 && ! in_array($w, $stopWords, true);
                })));

                $coreKnowledge[] = [
                    'title' => '📖 '.$qText,
                    'keywords' => $keywords,
                    'answer' => $qa['a'] ?? '',
                    'links' => $qa['links'] ?? [
                        ['text' => '📖 Volledige FAQ Gids', 'url' => route('faq.index')],
                    ],
                    'suggestions' => [
                        'Hoe pak ik €35.000 gratis startgeld?',
                        'Hoe beveilig ik mijn geld op de bank?',
                        'Hoe verdien ik 100% gratis VIP?',
                    ],
                ];
            }
        }

        return $coreKnowledge;
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
