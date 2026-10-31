<?php

namespace App\Support;

use Carbon\Carbon;

class TreatmentCaseCatalog
{
    /**
     * All 5 clinical treating specialists.
     */
    public const DOCTORS = [
        0 => [
            'name' => 'Dr. H. van Beek',
            'role' => 'Hoofdpsychiater & Medisch Directeur',
            'big' => 'BIG: 492.019.481',
            'quote' => 'Rust, structuur en absolute eerlijkheid over je delictverleden zijn vereist voor enige vorm van vrijheid.',
            'avatar_icon' => 'fa-user-doctor',
            'badge_color' => 'bg-purple-50 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 border-purple-200/80 dark:border-purple-800/70',
        ],
        1 => [
            'name' => 'Yvonne Meijer',
            'role' => 'Sociaal Psychiatrisch Verpleegkundige',
            'big' => 'BIG: 284.910.332',
            'quote' => 'Ik zie hoe je met je emoties omgaat op de zaal. Een modelhouding loont zich altijd in dit paviljoen.',
            'avatar_icon' => 'fa-user-nurse',
            'badge_color' => 'bg-teal-50 dark:bg-teal-950/60 text-teal-700 dark:text-teal-300 border-teal-200/80 dark:border-teal-800/70',
        ],
        2 => [
            'name' => 'Dr. Robert Visser',
            'role' => 'Forensisch Psycholoog & Delictanalist',
            'big' => 'BIG: 719.402.115',
            'quote' => 'Manipulatie en gladde praatjes prik ik direct door. Laat me zien dat je daadwerkelijk veranderd bent.',
            'avatar_icon' => 'fa-clipboard-user',
            'badge_color' => 'bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 border-indigo-200/80 dark:border-indigo-800/70',
        ],
        3 => [
            'name' => 'Dr. Anouk Schipper',
            'role' => 'Verslavingsarts & Farmacoloog',
            'big' => 'BIG: 583.190.442',
            'quote' => 'De handel in medicijnen en contrabande brengt de hele afdeling in gevaar. We controleren streng op compliance.',
            'avatar_icon' => 'fa-stethoscope',
            'badge_color' => 'bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border-rose-200/80 dark:border-rose-800/70',
        ],
        4 => [
            'name' => 'Mark van Dam',
            'role' => 'Sociaal Therapeut & Arbeidsbegeleider',
            'big' => 'BIG: 391.849.201',
            'quote' => 'Op de werkplaats en tijdens sportactiviteiten zien we wie er echt klaar is voor een stap naar buiten.',
            'avatar_icon' => 'fa-briefcase-medical',
            'badge_color' => 'bg-amber-50 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 border-amber-200/80 dark:border-amber-800/70',
        ],
    ];

    /**
     * 25 Detailed clinical archetypes covering every facet of life in the forensic psychiatric facility.
     */
    public const ARCHETYPES = [
        0 => [
            'title' => 'Het Smokkelnetwerk bij de Wasruimte',
            'location' => 'Centrale Wasruimte & Linnenkamer',
            'incident_intro' => 'Tijdens het vouwen van het linnengoed heeft beveiliger Henk een verdacht pakketje onderschept tussen de kussenslopen. Jouw naam werd genoemd door een medepatiënt.',
            'q1' => [
                'theme' => 'Eerlijkheid & Contrabande',
                'question' => 'Zaalwacht Henk beweert dat jij regelmatig stiekem tassen versjouwt in de linnenkamer. Wat is jouw lezing van dit incident?',
                'options' => [
                    'a' => ['text' => 'Ik vouw alleen netjes mijn eigen kleding en meld verdachte pakketjes direct bij de leiding.', 'score' => 25, 'note' => 'Toont volledige transparantie en distantieert zich van contrabande.', 'respect' => 0],
                    'b' => ['text' => 'Ik let op mijn eigen werk en hou me afzijdig van andermans zaken.', 'score' => 10, 'note' => 'Neutraal, maar weigert opening van zaken over mede-inmates.', 'respect' => 1],
                    'c' => ['text' => 'Die zaalwacht lult uit zijn nek. Als hij blijft zeuren heeft hij een groot probleem.', 'score' => -30, 'note' => 'Ernstige verbale intimidatie jegens het beveiligingspersoneel.', 'respect' => 3],
                    'd' => ['text' => 'Misschien moet Henk zelf eens onderzocht worden op wat hij naar binnen brengt.', 'score' => -15, 'note' => 'Defensief en zoekt de schuld bij de controlerende instanties.', 'respect' => 1],
                ],
            ],
            'q2' => [
                'theme' => 'Emotieregulatie bij Beschuldiging',
                'question' => 'Je werd ter plekke gefouilleerd voor de ogen van de hele afdeling. Hoe voelde dat voor je?',
                'options' => [
                    'a' => ['text' => 'Het was vervelend, maar ik begrijp dat veiligheidsprotocollen voor iedereen gelden.', 'score' => 25, 'note' => 'Goede rationalisatie en aanvaarding van institutionele controle.', 'respect' => 0],
                    'b' => ['text' => 'Ik werd boos van binnen, maar heb me rustig op mijn cel afgezonderd.', 'score' => 15, 'note' => 'Herkenning van innerlijke woede met tijdige de-escalatie.', 'respect' => 0],
                    'c' => ['text' => 'Mijn bloed kookte. Ik had die bewaker het liefst tegen de droger gesmeten.', 'score' => -30, 'note' => 'Fantasieën over fysiek geweld jegens personeel.', 'respect' => 2],
                    'd' => ['text' => 'Ik lach erom. Ze vinden toch nooit wat ik niet wil dat ze vinden.', 'score' => -20, 'note' => 'Provocerend en overtuigd van eigen onaantastbaarheid.', 'respect' => 2],
                ],
            ],
            'q3' => [
                'theme' => 'Verlof Doelstelling',
                'question' => 'Als het behandelteam jou vandaag verlof toekent, hoe voorkom je contact met oude smokkelnetwerken?',
                'options' => [
                    'a' => ['text' => 'Ik blijf strikt binnen de afgesproken route met mijn sociotherapeut.', 'score' => 25, 'note' => 'Duidelijk voornemen tot naleving van verlofvoorwaarden.', 'respect' => 0],
                    'b' => ['text' => 'Ik focus me op een wandeling in het park en vermijd winkelcentra.', 'score' => 15, 'note' => 'Vermijdt hoog-risico situaties bewust.', 'respect' => 0],
                    'c' => ['text' => 'Mijn netwerk is mijn zaak. Wat ik buiten doe ziet toch niemand.', 'score' => -35, 'note' => 'Expliciete weigering tot toezicht; direct recidiverisico.', 'respect' => 4],
                    'd' => ['text' => 'Ik doe gewoon wat me uitkomt, zolang ik maar op tijd terug ben.', 'score' => -15, 'note' => 'Onderschatting van de verlofregels en toezichtsafspraken.', 'respect' => 1],
                ],
            ],
        ],
        1 => [
            'title' => 'Agressie en Rumoer tijdens het Luchtuur',
            'location' => 'Grote Luchtplaats Paviljoen Oost',
            'incident_intro' => 'Gisterenmiddag liep een discussie over een basketbalwedstrijd uit de hand. Er werd geschreeuwd en meerdere patiënten moesten door de zaalwacht uit elkaar worden gehaald.',
            'q1' => [
                'theme' => 'Conflicthantering',
                'question' => 'Wat was jouw aandeel toen de spanningen opliepen bij het basketbalveld?',
                'options' => [
                    'a' => ['text' => 'Ik ben direct weggelopen naar de bankjes om escalatie te voorkomen.', 'score' => 25, 'note' => 'Uitstekende de-escalerende keuze gemaakt.', 'respect' => 0],
                    'b' => ['text' => 'Ik probeerde de boel te sussen met woorden, zonder zelf partij te kiezen.', 'score' => 15, 'note' => 'Constructieve sociale bemiddeling getoond.', 'respect' => 1],
                    'c' => ['text' => 'Ik sprong er direct tussen om mijn ganglid te verdedigen met de vuist.', 'score' => -35, 'note' => 'Actieve participatie in groepsgeweld op de luchtplaats.', 'respect' => 4],
                    'd' => ['text' => 'Ik stond te juichen aan de zijkant. Een beetje actie kan geen kwaad.', 'score' => -15, 'note' => 'Sensatiebelust en stimuleert anti-sociaal groepsgedrag.', 'respect' => 1],
                ],
            ],
            'q2' => [
                'theme' => 'Zelfreflectie op Spierkracht',
                'question' => 'Je traint veel in het krachthonk. Gebruik je jouw fysieke kracht om anderen te intimideren?',
                'options' => [
                    'a' => ['text' => 'Nee dokter, sporten is voor mij een gezonde uitlaatklep om stress kwijt te raken.', 'score' => 25, 'note' => 'Gezond inzicht in sport als emotieregulatiemiddel.', 'respect' => 0],
                    'b' => ['text' => 'Mensen kijken tegen me op, maar ik val niemand lastig zolang ze mij met rust laten.', 'score' => 10, 'note' => 'Grensverkenning, maar toont basis van wederzijds respect.', 'respect' => 1],
                    'c' => ['text' => 'Op de afdeling geldt het recht van de sterkste. Zonder spieren ben je vogelvrij.', 'score' => -30, 'note' => 'Gevaarlijke machocultuur en intimiderende levenshouding.', 'respect' => 3],
                    'd' => ['text' => 'Als mensen bang voor me zijn is dat hun eigen probleem.', 'score' => -20, 'note' => 'Gebrek aan empathie jegens kwetsbare mede-patiënten.', 'respect' => 2],
                ],
            ],
            'q3' => [
                'theme' => 'Reïntegratie & Rust',
                'question' => 'Wat ga je doen als iemand je op straat tijdens verlof per ongeluk aanstoot?',
                'options' => [
                    'a' => ['text' => 'Ik bied netjes mijn excuses aan en loop gewoon rustig door.', 'score' => 25, 'note' => 'Volwassen conflictvermijding in de maatschappij.', 'respect' => 0],
                    'b' => ['text' => 'Ik kijk de andere kant op en vraag mijn begeleider om door te lopen.', 'score' => 20, 'note' => 'Maakt adequaat gebruik van professionele steun.', 'respect' => 0],
                    'c' => ['text' => 'Diegene kan een klap op zijn neus verwachten. Niemand raakt me aan.', 'score' => -35, 'note' => 'Extreem gevaarlijk kort lontje; ontoelaatbaar op straat.', 'respect' => 3],
                    'd' => ['text' => 'Ik scheld hem verrot en laat merken wie ik ben.', 'score' => -20, 'note' => 'Verbaal agressief en verhoogde kans op escalatie buiten.', 'respect' => 1],
                ],
            ],
        ],
        2 => [
            'title' => 'De Medicatiecontrole & Weigering van Druppels',
            'location' => 'Medische Post & Apotheek',
            'incident_intro' => 'De nachtzuster meldde dat een patiënt gisteravond zijn kalmerende medicatie niet heeft doorgeslikt. Het vermoeden bestaat dat er pillen worden opgespaard.',
            'q1' => [
                'theme' => 'Medicatietrouw',
                'question' => 'Ervaar je weerstand tegen de door ons voorgeschreven stemmingsstabilisatoren?',
                'options' => [
                    'a' => ['text' => 'Nee, ik neem ze trouw in en merk dat ze me helpen helder na te denken.', 'score' => 25, 'note' => 'Prima therapietrouw en inzicht in de werking van medicatie.', 'respect' => 0],
                    'b' => ['text' => 'Soms word ik er wat sloom van, maar ik overleg dat netjes met de arts.', 'score' => 20, 'note' => 'Bespreekt bijwerkingen op een constructieve manier.', 'respect' => 0],
                    'c' => ['text' => 'Jullie stoppen gif in mijn lijf om me mak te houden. Ik slik niks meer!', 'score' => -30, 'note' => 'Paranoïde afwijzing van noodzakelijke medische behandeling.', 'respect' => 2],
                    'd' => ['text' => 'Ik doe net alsof ik ze slik en verkoop ze voor sigaretten op de gang.', 'score' => -35, 'note' => 'Openlijke bekentenis van illegaal medicijnverkeer op de zaal.', 'respect' => 4],
                ],
            ],
            'q2' => [
                'theme' => 'Zelfbeheersing bij Bijwerkingen',
                'question' => 'Hoe ga je om met dagen waarop je je onrustig of neerslachtig voelt?',
                'options' => [
                    'a' => ['text' => 'Ik meld me bij Yvonne of ga een uurtje schilderen bij creatieve therapie.', 'score' => 25, 'note' => 'Constructief copinggedrag aangetoond.', 'respect' => 0],
                    'b' => ['text' => 'Ik zet muziek op via mijn hoofdtelefoon en wacht tot het overwaait.', 'score' => 15, 'note' => 'Passieve maar niet-schadelijke vorm van emotieregulatie.', 'respect' => 0],
                    'c' => ['text' => 'Ik trap tegen de deuren totdat de zaalwacht me aandacht geeft.', 'score' => -25, 'note' => 'Destructief aandachttrekkend gedrag in de gangen.', 'respect' => 1],
                    'd' => ['text' => 'Ik regel wat illegale pillen van een mede-inmate.', 'score' => -30, 'note' => 'Gebruik van ongecontroleerde verdovende middelen.', 'respect' => 2],
                ],
            ],
            'q3' => [
                'theme' => 'Verantwoordelijkheid op Verlof',
                'question' => 'Kunnen we erop vertrouwen dat je buiten de hekken nuchter blijft en geen drugs gebruikt?',
                'options' => [
                    'a' => ['text' => 'Absoluut. Mijn reïntegratie en vrijheid zijn me veel te dierbaar.', 'score' => 25, 'note' => 'Krachtige motivatie voor abstinentie en delictvrij leven.', 'respect' => 0],
                    'b' => ['text' => 'Ik heb geen enkele behoefte aan middelen als ik buiten mag rondlopen.', 'score' => 15, 'note' => 'Positieve intentie uitgesproken.', 'respect' => 0],
                    'c' => ['text' => 'Zodra de begeleider wegkijkt scoor ik direct een joint bij de coffeeshop.', 'score' => -35, 'note' => 'Zeker risico op middelengebruik tijdens verlofsituatie.', 'respect' => 3],
                    'd' => ['text' => 'Een biertje op een terras moet toch kunnen? Wat een gezeik altijd.', 'score' => -20, 'note' => 'Bagatelliseert middelengebruik tegen de regels in.', 'respect' => 1],
                ],
            ],
        ],
        3 => [
            'title' => 'Geheime Smartphones achter het Ventilatierooster',
            'location' => 'Celblok 3 & Sanitaire Ruimtes',
            'incident_intro' => 'Tijdens een technische controle van de luchtbehandeling is een opgeladen smartphone met meerdere simkaarten aangetroffen in een schacht nabij jouw vleugel.',
            'q1' => [
                'theme' => 'Digitale Contrabande',
                'question' => 'Heb jij weet van ongeoorloofde telefoons of internettoegang op deze afdeling?',
                'options' => [
                    'a' => ['text' => 'Nee dokter, ik gebruik alleen de officiële afdelingstelefoon volgens het rooster.', 'score' => 25, 'note' => 'Houdt zich keurig aan de communicatievoorschriften.', 'respect' => 0],
                    'b' => ['text' => 'Iedereen weet dat er toestellen rondgaan, maar ik blijf erbuiten.', 'score' => 10, 'note' => 'Eerlijk over het afdelingsklimaat, claimt geen bezit.', 'respect' => 0],
                    'c' => ['text' => 'Natuurlijk heb ik een foon. Hoe moet ik anders zaken regelen buiten?', 'score' => -35, 'note' => 'Openlijk trotseren van het verbod op ongecontroleerde communicatie.', 'respect' => 4],
                    'd' => ['text' => 'Zelfs als ik het wist zou ik nooit snitchen aan het behandelteam.', 'score' => -15, 'note' => 'Hantering van de criminele zwijgcode (omerta).', 'respect' => 2],
                ],
            ],
            'q2' => [
                'theme' => 'Contact met het Buitenmilieu',
                'question' => 'Waarom is het zo belangrijk dat contact met de buitenwereld gereguleerd verloopt?',
                'options' => [
                    'a' => ['text' => 'Om te voorkomen dat oude criminele contacten of slachtoffers benaderd worden.', 'score' => 25, 'note' => 'Uitstekend inzicht in slachtofferbescherming.', 'respect' => 0],
                    'b' => ['text' => 'Omdat de kliniek wil weten wie er belt, begrijp ik best.', 'score' => 15, 'note' => 'Begrip voor de institutionele controle.', 'respect' => 0],
                    'c' => ['text' => 'Jullie willen ons gewoon isoleren en gek maken in dit gesticht.', 'score' => -25, 'note' => 'Paranoïde interpretatie van het veiligheidsbeleid.', 'respect' => 1],
                    'd' => ['text' => 'Het boeit me niet wat jullie vinden, ik praat met wie ik wil.', 'score' => -30, 'note' => 'Antisociaal en verwerpt maatschappelijke kaders.', 'respect' => 2],
                ],
            ],
            'q3' => [
                'theme' => 'Verlofafspraken Naleven',
                'question' => 'Wat doe je als een onbekende op straat jou een telefoon aanbiedt tijdens verlof?',
                'options' => [
                    'a' => ['text' => 'Ik weiger direct en meld het meteen bij mijn begeleider.', 'score' => 25, 'note' => 'Adequaat handelen bij externe verleidingen.', 'respect' => 0],
                    'b' => ['text' => 'Ik loop door zonder aandacht te schenken aan die persoon.', 'score' => 15, 'note' => 'Vermijdt interactie op straat.', 'respect' => 0],
                    'c' => ['text' => 'Ik stop hem snel in mijn zak en smokkel hem mee naar binnen.', 'score' => -35, 'note' => 'Actief smokkelvoornemen tijdens verlof.', 'respect' => 3],
                    'd' => ['text' => 'Ik vraag wat hij ervoor wil hebben.', 'score' => -25, 'note' => 'Handelsmentaliteit boven reïntegratiebelang gesteld.', 'respect' => 1],
                ],
            ],
        ],
        4 => [
            'title' => 'Het Incident in de Houtwerkplaats',
            'location' => 'Arbeidstherapie Paviljoen Hout & Metaal',
            'incident_intro' => 'Tijdens de houtbewerking is geconstateerd dat er een zwaar beitelmes ontbrak op het gereedschapsbord. De hele groep moest wachten tot het gereedschap terecht was.',
            'q1' => [
                'theme' => 'Veiligheid & Verantwoordelijkheid',
                'question' => 'Hoe heb jij gereageerd op de commotie rondom het vermiste gereedschap?',
                'options' => [
                    'a' => ['text' => 'Ik heb rustig meegeholpen met zoeken onder de werkbanken.', 'score' => 25, 'note' => 'Hulpvaardig en toont burgerzin in de werkplaats.', 'respect' => 0],
                    'b' => ['text' => 'Ik bleef rustig wachten op mijn kruk zoals de werkmeester vroeg.', 'score' => 15, 'note' => 'Volgt instructies van de werkbegeleider netjes op.', 'respect' => 0],
                    'c' => ['text' => 'Ik schold de werkmeester uit dat hij zijn eigen rommel moet bijhouden.', 'score' => -25, 'note' => 'Agressief naar het pedagogisch personeel.', 'respect' => 1],
                    'd' => ['text' => 'Ik hoopte dat iemand hem stiekem meenam voor een goede steekpartij.', 'score' => -35, 'note' => 'Verheerlijking van ernstig geweld en wapenbezit.', 'respect' => 3],
                ],
            ],
            'q2' => [
                'theme' => 'Doelmatigheid van Arbeid',
                'question' => 'Wat betekent de dagbesteding in de werkplaats eigenlijk voor jou?',
                'options' => [
                    'a' => ['text' => 'Het geeft me een dagritme, voldoening en bereidt me voor op een echte baan buiten.', 'score' => 25, 'note' => 'Realistisch reïntegratieperspectief geformuleerd.', 'respect' => 0],
                    'b' => ['text' => 'Het doodt de tijd en levert een paar euro zakgeld op per uur.', 'score' => 15, 'note' => 'Pragmatische maar acceptabele werkhouding.', 'respect' => 0],
                    'c' => ['text' => 'Dwangarbeid voor de directie. Ik doe zo min mogelijk.', 'score' => -20, 'note' => 'Weerstand en gebrek aan werkmotivatie.', 'respect' => 1],
                    'd' => ['text' => 'Een plek om scherpe voorwerpen te fabriceren.', 'score' => -35, 'note' => 'Zorgwekkende uitspraak over wapenfabricage.', 'respect' => 3],
                ],
            ],
            'q3' => [
                'theme' => 'Arbeidsfitheid Buiten',
                'question' => 'Zou jij tijdens begeleid verlof bereid zijn om vrijwilligerswerk te doen in de wijk?',
                'options' => [
                    'a' => ['text' => 'Zeker, ik wil graag iets terugdoen voor de maatschappij en vertrouwen winnen.', 'score' => 25, 'note' => 'Sterke pro-sociale motivatie getoond.', 'respect' => 0],
                    'b' => ['text' => 'Als het me dichter bij mijn definitieve vrijheid brengt, doe ik dat.', 'score' => 15, 'note' => 'Doelgericht gemotiveerd.', 'respect' => 0],
                    'c' => ['text' => 'Gratis werken voor vreemden? Ben je helemaal besodemieterd dokter.', 'score' => -25, 'note' => 'Verwerpt maatschappelijk herstel en vrijwilligersinzet.', 'respect' => 1],
                    'd' => ['text' => 'Alleen als ik de kas kan meenemen.', 'score' => -35, 'note' => 'Crimineel opportunisme openlijk uitgesproken.', 'respect' => 3],
                ],
            ],
        ],
        5 => [
            'title' => 'De Ruzie in de Bezoekersruimte (Familie & Pakketjes)',
            'location' => 'Bezoekerszaal & Toegangscontrole',
            'incident_intro' => 'Tijdens het bezoekuur gisteren ontstond een luidruchtige confrontatie tussen een bezoeker en de beveiliging. Er werd geprobeerd niet-toegestane goederen over te dragen.',
            'q1' => [
                'theme' => 'Familiebanden & Emotionele Druk',
                'question' => 'Hoe ga jij om met spanningen of teleurstellingen na afloop van een bezoekmoment?',
                'options' => [
                    'a' => ['text' => 'Ik bespreek mijn emoties met mijn mentor en neem even tijd voor mezelf op cel.', 'score' => 25, 'note' => 'Uitstekende emotieverwerking en gebruik van begeleiding.', 'respect' => 0],
                    'b' => ['text' => 'Ik ga een halfuurtje hardlopen op de binnenplaats om de spanning kwijt te raken.', 'score' => 15, 'note' => 'Fysieke ontlading zonder agressie.', 'respect' => 0],
                    'c' => ['text' => 'Ik sloop mijn celinrichting zodra de deur in het slot valt.', 'score' => -30, 'note' => 'Destructieve agressie tegen kliniekeigendommen.', 'respect' => 2],
                    'd' => ['text' => 'Ik eis dat de directie mijn bezoekregeling per direct verdubbelt.', 'score' => -15, 'note' => 'Eisen stellend en gebrek aan realiteitszin.', 'respect' => 1],
                ],
            ],
            'q2' => [
                'theme' => 'Druk vanuit het Criminele Verleden',
                'question' => 'Proberen mensen van buiten jou onder druk te zetten om dingen te regelen?',
                'options' => [
                    'a' => ['text' => 'Nee dokter, ik heb gebroken met foute contacten en kies voor mijn eigen herstel.', 'score' => 25, 'note' => 'Duidelijke begrenzing naar het criminele milieu.', 'respect' => 0],
                    'b' => ['text' => 'Soms bellen ze, maar ik kap die gesprekken snel af.', 'score' => 15, 'note' => 'Weerstand tegen externe druk aangetoond.', 'respect' => 0],
                    'c' => ['text' => 'Ik ben niemand verantwoording schuldig over wie er op bezoek komt.', 'score' => -25, 'note' => 'Weerstand tegen toezicht op sociaal netwerk.', 'respect' => 2],
                    'd' => ['text' => 'Buiten heb ik nog rekeningen openstaan die ik hoe dan ook ga vereffenen.', 'score' => -35, 'note' => 'Expliciete wraakgevoelens en delictgevaar buiten.', 'respect' => 3],
                ],
            ],
            'q3' => [
                'theme' => 'Veiligheid tijdens Buitencontact',
                'question' => 'Stel dat je tijdens verlof een oud bendelid tegenkomt in de stad, wat doe je?',
                'options' => [
                    'a' => ['text' => 'Ik negeer hem, loop direct met mijn begeleider de andere kant op en meld het.', 'score' => 25, 'note' => 'Uitzonderlijk verstandig en veilig gehandeld.', 'respect' => 0],
                    'b' => ['text' => 'Ik knik kort en loop snel door zonder stil te blijven staan.', 'score' => 15, 'note' => 'De-escalerend ontweken.', 'respect' => 0],
                    'c' => ['text' => 'Ik schud hem de hand en wissel stiekem nummers uit.', 'score' => -30, 'note' => 'Actief herstel van criminele associaties.', 'respect' => 3],
                    'd' => ['text' => 'Ik maak direct ruzie over wie er nog geld van wie krijgt.', 'score' => -35, 'note' => 'Directe kans op geweldpleging op straat.', 'respect' => 4],
                ],
            ],
        ],
        6 => [
            'title' => 'Nachtelijke Onrust & Bonken op de Verwarming',
            'location' => 'Celgang West & Nachtwachtpost',
            'incident_intro' => 'Rond 02:30 uur vannacht werd de hele afdeling gewekt door aanhoudend gebonk op de verwarmingsbuizen. De nachtdienst moest met versterking de gang op.',
            'q1' => [
                'theme' => 'Nachtrust & Slapeloosheid',
                'question' => 'Hoe reageer jij wanneer je nachtrust wordt verstoord door lawaai van medepatiënten?',
                'options' => [
                    'a' => ['text' => 'Ik doe oordoppen in, probeer rustig te blijven en meld het de volgende ochtend netjes.', 'score' => 25, 'note' => 'Geduldig en veroorzaakt geen secundaire onrust.', 'respect' => 0],
                    'b' => ['text' => 'Ik druk op de intercom en vraag de nachtdienst beleefd of het stiller kan.', 'score' => 15, 'note' => 'Gebruikt de juiste communicatiekanalen.', 'respect' => 0],
                    'c' => ['text' => 'Ik ga keihard terugslaan op mijn stalen deur totdat de hele kliniek wakker is.', 'score' => -30, 'note' => 'Aanstichter van collectieve afdelingsonrust.', 'respect' => 2],
                    'd' => ['text' => 'Ik schreeuw doodsbedreigingen door het luikje naar de buurman.', 'score' => -35, 'note' => 'Ernstige bedreigingen geuit in de nachtelijke uren.', 'respect' => 3],
                ],
            ],
            'q2' => [
                'theme' => 'Dag- en Nachtritme',
                'question' => 'Lukt het je om een stabiel dag- en nachtritme vast te houden in de inrichting?',
                'options' => [
                    'a' => ['text' => 'Ja, ik sta om 07:30 op, maak mijn bed op en ben op tijd bij het ontbijt.', 'score' => 25, 'note' => 'Uitstekende persoonlijke structuur en zelfzorg.', 'respect' => 0],
                    'b' => ['text' => 'Meestal wel, hoewel ik in het weekend soms wat langer blijf liggen.', 'score' => 15, 'note' => 'Normaal leefpatroon.', 'respect' => 0],
                    'c' => ['text' => 'Ik slaap overdag en leef \'s nachts. Niemand bepaalt wanneer ik slaap.', 'score' => -25, 'note' => 'Trotseert de afdelingsstructuur en dagbesteding.', 'respect' => 1],
                    'd' => ['text' => 'Mijn hoofd stopt nooit met malen. Ik slaap nauwelijks twee uur.', 'score' => -10, 'note' => 'Hoge spanning en slaapdeprivatie geconstateerd.', 'respect' => 0],
                ],
            ],
            'q3' => [
                'theme' => 'Verlofdiscipline & Tijdslimiet',
                'question' => 'Hoe ga je zorgen dat je tijdens verlof stipt op de afgesproken minuut terug bent bij de poort?',
                'options' => [
                    'a' => ['text' => 'Ik stel een wekker in en plan altijd een kwartier speling in.', 'score' => 25, 'note' => 'Proactieve planning en betrouwbaarheid.', 'respect' => 0],
                    'b' => ['text' => 'Ik blijf dicht bij de begeleider, dus die houdt de tijd in de gaten.', 'score' => 15, 'note' => 'Leunt op toezicht, voldoende.', 'respect' => 0],
                    'c' => ['text' => 'Als ik een halfuurtje te laat kom is dat toch geen ramp? Jullie overdrijven altijd.', 'score' => -25, 'note' => 'Overschrijdt grenzen en negeert strakke meldtijden.', 'respect' => 1],
                    'd' => ['text' => 'Als het me bevalt buiten kom ik misschien wel helemaal niet terug.', 'score' => -35, 'note' => 'Openlijk ontvluchtingsgevaar geuit.', 'respect' => 4],
                ],
            ],
        ],
        7 => [
            'title' => 'De Voedselhandel & Chantage in de Keuken',
            'location' => 'Centrale Recreatiekeuken & Kantine',
            'incident_intro' => 'De kok heeft geklaagd dat patiënten elkaar onder druk zetten om toetjes en warme maaltijden af te staan. Er zijn signalen van afpersing rondom het avondeten.',
            'q1' => [
                'theme' => 'Afpersing & Respectcultuur',
                'question' => 'Heb jij ooit eten of privileges afgeperst van zwakkere patiënten op de afdeling?',
                'options' => [
                    'a' => ['text' => 'Absoluut niet. Iedereen heeft recht op zijn eigen maaltijd en respect.', 'score' => 25, 'note' => 'Eerbiedigt de rechten van kwetsbare medepatiënten.', 'respect' => 0],
                    'b' => ['text' => 'We ruilen wel eens een toetje voor een banaan, maar altijd vrijwillig.', 'score' => 15, 'note' => 'Gezonde informele ruilhandel zonder dwang.', 'respect' => 0],
                    'c' => ['text' => 'Als iemand zijn vlaflip niet opeet pak ik hem. Zo werkt het nou eenmaal hier.', 'score' => -25, 'note' => 'Grensoverschrijdend en intimiderend dominant gedrag.', 'respect' => 2],
                    'd' => ['text' => 'In de bajes betaalt de zwakke belasting aan de sterke.', 'score' => -35, 'note' => 'Actieve exploitatie van mede-inmates in de groep.', 'respect' => 4],
                ],
            ],
            'q2' => [
                'theme' => 'Grip op Impulsen',
                'question' => 'Wat doe je als het eten in de kantine een keer tegenvalt of koud is?',
                'options' => [
                    'a' => ['text' => 'Ik eet wat ik lust en meld het netjes via de maaltijdencommissie.', 'score' => 25, 'note' => 'Passende feedbackcultuur gehanteerd.', 'respect' => 0],
                    'b' => ['text' => 'Ik warm het even op in de magnetron en zeur er niet over.', 'score' => 15, 'note' => 'Praktische zelfredzaamheid getoond.', 'respect' => 0],
                    'c' => ['text' => 'Ik gooi het bord door het doorgeefluik recht naar de kok.', 'score' => -35, 'note' => 'Fysiek agressief incident jegens keukenpersoneel.', 'respect' => 3],
                    'd' => ['text' => 'Ik ga in hongerstaking totdat we biefstuk krijgen.', 'score' => -20, 'note' => 'Manipulatieve protestactie aangekondigd.', 'respect' => 1],
                ],
            ],
            'q3' => [
                'theme' => 'Gedrag in Horecagelegenheden',
                'question' => 'Hoe ga je je gedragen als je tijdens verlof een broodje gaat eten in een eetcafé?',
                'options' => [
                    'a' => ['text' => 'Ik wacht netjes op mijn beurt, bedank de bediening en eet rustig op.', 'score' => 25, 'note' => 'Keurige maatschappelijke omgangsvormen.', 'respect' => 0],
                    'b' => ['text' => 'Ik betaal mijn rekening en zorg dat ik geen aandacht trek.', 'score' => 15, 'note' => 'Normaal sociaal gedrag.', 'respect' => 0],
                    'c' => ['text' => 'Ik loop weg zonder te betalen als de ober even niet kijkt.', 'score' => -35, 'note' => 'Neiging tot vermogensdelicten buiten.', 'respect' => 3],
                    'd' => ['text' => 'Ik maak herrie als het langer dan 5 minuten duurt.', 'score' => -20, 'note' => 'Lage frustratietolerantie in openbare ruimtes.', 'respect' => 1],
                ],
            ],
        ],
        8 => [
            'title' => 'Het Incident in de Fitnesszaal & Krachthonk',
            'location' => 'Sportzaal & Krachthonk Paviljoen 7',
            'incident_intro' => 'Tijdens het bankdrukken gisteravond is er een zware halterschijf tegen een spiegel gegooid na een verhitte discussie over wie er aan de beurt was.',
            'q1' => [
                'theme' => 'Spanning in de Sportruimte',
                'question' => 'De sportbegeleider meldt dat de sfeer in het krachthonk grimmig en intimiderend was. Wat was jouw rol?',
                'options' => [
                    'a' => ['text' => 'Ik hield me afzijdig op de hometrainer en heb mijn training rustig afgemaakt.', 'score' => 25, 'note' => 'Verstandig afstand gehouden van de conflictsituatie.', 'respect' => 0],
                    'b' => ['text' => 'Ik heb geholpen de gewichten netjes op het rek terug te leggen.', 'score' => 20, 'note' => 'Prosociaal en ordelijk gedrag.', 'respect' => 0],
                    'c' => ['text' => 'Iemand daagde me uit over mijn maximale bench press, dus ik moest wel reageren.', 'score' => -25, 'note' => 'Krenkbaar ego en uitgelokt tot machogedrag.', 'respect' => 2],
                    'd' => ['text' => 'In het krachthonk bepalen wij wie er traint, niet de sportleraar.', 'score' => -35, 'note' => 'Verwerping van het toezicht van de sportinstructeur.', 'respect' => 3],
                ],
            ],
            'q2' => [
                'theme' => 'Verwerken van Verlies & Krenking',
                'question' => 'Hoe reageer je wanneer iemand jou verbaal kleineert of uitdaagt in de groep?',
                'options' => [
                    'a' => ['text' => 'Ik negeer de opmerking. Mijn eigenwaarde hangt niet af van andermans mening.', 'score' => 25, 'note' => 'Hoge emotionele maturiteit en stabiel zelfbeeld.', 'respect' => 0],
                    'b' => ['text' => 'Ik geef een gevatte opmerking terug en lach het weg.', 'score' => 15, 'note' => 'Verbale weerbaarheid zonder escalatie.', 'respect' => 1],
                    'c' => ['text' => 'Wie mij beledigt gaat neer. Niemand kleineert mij zonder bloed.', 'score' => -35, 'note' => 'Narcistische krenking direct omgezet in fysiek geweld.', 'respect' => 4],
                    'd' => ['text' => 'Ik zweer wraak en wacht op het juiste moment als er geen camera\'s zijn.', 'score' => -30, 'note' => 'Berekenend en wraakzuchtig gedragspatroon.', 'respect' => 2],
                ],
            ],
            'q3' => [
                'theme' => 'Prikkelregulatie in de Maatschappij',
                'question' => 'De buitenwereld is druk en luidruchtig. Kun jij omgaan met de prikkels op straat?',
                'options' => [
                    'a' => ['text' => 'Ja, ik heb ademhalingstechnieken geoefend en geef het direct aan als het te druk wordt.', 'score' => 25, 'note' => 'Uitstekende zelfregulatie en signalering.', 'respect' => 0],
                    'b' => ['text' => 'Ik focus me op rustige straten en volg het tempo van mijn begeleider.', 'score' => 20, 'note' => 'Veilige aanpak gekozen.', 'respect' => 0],
                    'c' => ['text' => 'Als iemand te dichtbij loopt duw ik hem hard aan de kant.', 'score' => -30, 'note' => 'Gevaarlijke fysieke reactie op normale sociale nabijheid.', 'respect' => 2],
                    'd' => ['text' => 'Ik raak snel overprikkeld en dan weet ik niet meer wat ik doe.', 'score' => -15, 'note' => 'Eerlijk over overprikkeling, maar vereist nog extra training.', 'respect' => 0],
                ],
            ],
        ],
        9 => [
            'title' => 'De Psychologische Evaluatie & Delictinzicht',
            'location' => 'Spreekkamer Behandelstaf Paviljoen 7',
            'incident_intro' => 'De halfjaarlijkse risicotaxatie en delictanalyse staat op de agenda. Het behandelteam toetst jouw mate van empathie en schuldinzicht.',
            'q1' => [
                'theme' => 'Inleving in Slachtoffers',
                'question' => 'Wanneer je terugdenkt aan de feiten waardoor je hier bent geplaatst, wat voel je dan?',
                'options' => [
                    'a' => ['text' => 'Diepe spijt van het leed dat ik anderen heb aangedaan. Ik wil dit nooit meer herhalen.', 'score' => 25, 'note' => 'Oprecht slachtofferinzicht en schuldbesef getoond.', 'respect' => 0],
                    'b' => ['text' => 'Ik besef dat het fout was en werk hard om een ander mens te worden.', 'score' => 20, 'note' => 'Gezonde motivatie tot gedragsverandering.', 'respect' => 0],
                    'c' => ['text' => 'Mijn slachtoffers hadden het zelf uitgelokt. Ik ben hier de echte dupe.', 'score' => -35, 'note' => 'Totale afwezigheid van empathie; victim blaming.', 'respect' => 2],
                    'd' => ['text' => 'Gedane zaken nemen geen keer. Waarom moet ik er steeds over praten?', 'score' => -20, 'note' => 'Afweer en weigering van delictverdieping.', 'respect' => 1],
                ],
            ],
            'q2' => [
                'theme' => 'Persoonlijkheidsgroei in Behandeling',
                'question' => 'Wat heb je tot nu toe echt geleerd tijdens je verblijf in de TBS Kliniek?',
                'options' => [
                    'a' => ['text' => 'Mijn risicosignalen herkennen en op tijd om hulp vragen voordat de bom barst.', 'score' => 25, 'note' => 'Optimaal delictpreventieplan geïnternaliseerd.', 'respect' => 0],
                    'b' => ['text' => 'Dat geweld niets oplost en alleen maar leidt tot langere opsluiting.', 'score' => 15, 'note' => 'Rationeel inzicht in de nadelen van delictgedrag.', 'respect' => 0],
                    'c' => ['text' => 'Dat je hier alleen maar moet zeggen wat de dokters willen horen.', 'score' => -25, 'note' => 'Cynisch en manipulatief ten opzichte van de behandeling.', 'respect' => 2],
                    'd' => ['text' => 'Helemaal niks. Ik ben wie ik ben en niemand verandert mij.', 'score' => -35, 'note' => 'Volledige therapieresistentie geconstateerd.', 'respect' => 3],
                ],
            ],
            'q3' => [
                'theme' => 'Eindoordeel voor Begeleid Verlof',
                'question' => 'Waarom vindt jij dat de kliniekdirectie jou vandaag buiten de poort moet laten?',
                'options' => [
                    'a' => ['text' => 'Omdat ik bewezen heb betrouwbaar te zijn en stap voor stap wil reïntegreren.', 'score' => 25, 'note' => 'Gefundeerd en realistisch verzoek om verlof.', 'respect' => 0],
                    'b' => ['text' => 'Omdat ik me al weken aan alle afspraken houd en toe ben aan een frisse neus.', 'score' => 20, 'note' => 'Positief gedrag als basis voor privilege.', 'respect' => 0],
                    'c' => ['text' => 'Omdat ik anders gek word binnen deze muren en er iets breekt.', 'score' => -25, 'note' => 'Chanterend en dreigend met decompensatie.', 'respect' => 1],
                    'd' => ['text' => 'Omdat ik recht heb op vrijheid, klaar.', 'score' => -30, 'note' => 'Claimend en miskent de juridische kaders van de maatregel.', 'respect' => 2],
                ],
            ],
        ],
    ];

    /**
     * Special milestone cases for holiday / seasonal days of the year.
     */
    public const SPECIAL_DAYS = [
        1 => [ // 1 Januari
            'title' => 'Nieuwjaarssessie: Balans van het Afgelopen Jaar',
            'incident_intro' => 'De jaarwisseling op de gesloten afdeling verliep rumoerig. Er is illegaal geknald met luciferskoppen en de sfeer was gespannen.',
        ],
        117 => [ // 27 April (Koningsdag)
            'title' => 'Koningsdagsessie: Oranjekoorts & Feestchaos',
            'incident_intro' => 'Tijdens de Koningsdagviering in de recreatiezaal ontstond ruzie over de verdeling van tompoucen en extra rooktijd.',
        ],
        359 => [ // 25 December (Eerste Kerstdag)
            'title' => 'Kerstsessie: Eenzaamheid & Emotionele Druk',
            'incident_intro' => 'De feestdagen zijn emotioneel zwaar voor de afdeling. Meerdere patiënten kregen geen bezoek en de frustratie loopt hoog op.',
        ],
        365 => [ // 31 December (Oudejaarsavond)
            'title' => 'Oudejaarssessie: Het Aftelincident & Sluitingstijd',
            'incident_intro' => 'De laatste uren van het jaar zijn aangebroken. De zaalwacht houdt verscherpt toezicht op celdeuren en brandveiligheid.',
        ],
    ];

    /**
     * Get the complete psychiatric case for a specific day of the year (1..365).
     * Guaranteed to change every single day at midnight!
     *
     * @return array<string, mixed>
     */
    public static function getCaseForDay(int $dayOfYear): array
    {
        $day = max(1, min(366, $dayOfYear));

        // Deterministic rotation across archetypes and doctors
        $archetypeCount = count(self::ARCHETYPES);
        $archetypeIdx = ($day - 1) % $archetypeCount;
        $archetype = self::ARCHETYPES[$archetypeIdx];

        $doctorCount = count(self::DOCTORS);
        $doctorIdx = ($day + intdiv($day - 1, $archetypeCount)) % $doctorCount;
        $doctor = self::DOCTORS[$doctorIdx];

        // Specific holiday override if present
        $special = self::SPECIAL_DAYS[$day] ?? null;
        $title = $special['title'] ?? "Casus #D{$day}: {$archetype['title']}";
        $incident = $special['incident_intro'] ?? $archetype['incident_intro'];

        // Format Dutch date label
        $dateObj = Carbon::create(date('Y'), 1, 1)->addDays($day - 1);
        $monthsNl = [
            1 => 'januari', 2 => 'februari', 3 => 'maart', 4 => 'april',
            5 => 'mei', 6 => 'juni', 7 => 'juli', 8 => 'augustus',
            9 => 'september', 10 => 'oktober', 11 => 'november', 12 => 'december',
        ];
        $dateLabel = $dateObj->day.' '.$monthsNl[$dateObj->month];

        $caseCode = 'TBS-'.date('Y').'-D'.str_pad((string) $day, 3, '0', STR_PAD_LEFT);

        return [
            'day_of_year' => $day,
            'date_label' => $dateLabel,
            'case_code' => $caseCode,
            'title' => $title,
            'location' => $archetype['location'],
            'incident' => $incident,
            'doctor' => $doctor,
            'questions' => [
                1 => $archetype['q1'],
                2 => $archetype['q2'],
                3 => $archetype['q3'],
            ],
        ];
    }
}
