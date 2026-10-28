<?php

namespace App\Support;

use App\Models\Mission;

class MissionScenarioCatalog
{
    /**
     * All 70 interactive mission scenarios.
     *
     * @return array<string, array<string, mixed>>
     */
    public static function scenarios(): array
    {
        return [
            // 1. Koffie en Kaas
            'koffie-en-kaas' => [
                'id' => 1,
                'npc_name' => 'Henkie de Frikandelkoning',
                'npc_avatar' => 'assets/characters/henkie.jpg',
                'npc_role' => 'Kantine Baas Paviljoen 3',
                'dilemma' => 'Hé maat, luister goed. In de warmhoudkast liggen 3 frikandelbroodjes van €150 per stuk. Bewaker Peter wil €100 smeergeld om de deur niet op slot te draaien. Wat is het exacte totaalbedrag dat we nodig hebben?',
                'correct_choice' => 'B',
                'choices' => [
                    'A' => '€450 (alleen de broodjes betalen)',
                    'B' => '€550 (3x €150 + €100 smeergeld)',
                    'C' => '€650 (inclusief royale fooi voor de kok)',
                ],
                'feedback_wrong' => 'Henkie schudt zijn hoofd: "Je rekenwerk klopt voor geen meter! Peter weigerde en smeet de warmhoudkast dicht."',
            ],

            // 2. De Eerste Diefstal
            'de-eerste-diefstal' => [
                'id' => 2,
                'npc_name' => 'Ricardo Ritselaar',
                'npc_avatar' => 'assets/characters/ricardo.jpg',
                'npc_role' => 'Rookruimte Strateeg',
                'dilemma' => 'Patiënt Barry zit te knikkebollen in de rookruimte. Zijn kluissleutel steekt half uit zijn broekzak, maar de gangcamera zwenkt elke 30 seconden. Hoe pak je dit aan?',
                'correct_choice' => 'B',
                'choices' => [
                    'A' => 'Direct rennen en de sleutel met een harde ruk grijpen.',
                    'B' => 'Wachten tot de camera wegdraait, rustig langslopen en vingervlug toeslaan.',
                    'C' => 'Tegen Barry schreeuwen dat de zuster pillen komt uitdelen.',
                ],
                'feedback_wrong' => 'Ricardo zucht: "Je was veel te lomp! Barry schrok wakker en greep je arm vast. Mislukt!"',
            ],

            // 3. Dagelijkse Training
            'dagelijkse-training' => [
                'id' => 3,
                'npc_name' => 'Barry Biceps',
                'npc_avatar' => 'assets/characters/barry.jpg',
                'npc_role' => 'Sportzaal Veteraan',
                'dilemma' => 'Voordat we die halters aanraken wil ik discipline zien. Als jij 4 sets van 12 reps bankdrukt met 60 kilo, hoeveel herhalingen heb je dan in totaal gedrukt?',
                'correct_choice' => 'A',
                'choices' => [
                    'A' => '48 herhalingen in totaal',
                    'B' => '44 herhalingen in totaal',
                    'C' => '52 herhalingen in totaal',
                ],
                'feedback_wrong' => 'Barry lacht je uit: "Als je niet eens kunt tellen, ga je die gewichten laten vallen! Eerst je kop trainen!"',
            ],

            // 4. Zaalwacht Omzeilen
            'zaalwacht-omzeilen' => [
                'id' => 4,
                'npc_name' => 'Slinkse Sam',
                'npc_avatar' => 'assets/characters/avatar_male_2.jpg',
                'npc_role' => 'Gang Schaduwexpert',
                'dilemma' => 'Zaalwacht Willem drinkt om exact 14:15 een kop koffie en staart 2 minuten uit het raam. Er staat een piepende rolstoel in de gang. Wat doe je?',
                'correct_choice' => 'C',
                'choices' => [
                    'A' => 'Met de piepende rolstoel hard voorbij rennen.',
                    'B' => 'Op het raam tikken om te kijken of Willem echt niet oplet.',
                    'C' => 'Op sokken langs de schaduwzijde van de muur glippen om 14:16.',
                ],
                'feedback_wrong' => 'Slinkse Sam zucht: "Willem draaide zich meteen om door het gekraak! Je bent gesnapt!"',
            ],

            // 5. Vaatdoek Wisseltruc
            'vaatdoek-wisseltruc' => [
                'id' => 5,
                'npc_name' => 'Tante Ans',
                'npc_avatar' => 'assets/characters/avatar_female_3.jpg',
                'npc_role' => 'Keuken Moeder',
                'dilemma' => 'De hoofd-kok telt de messen. We willen 2 schilmesjes verbergen in de vuile wasmand. Welke mand pak je?',
                'correct_choice' => 'A',
                'choices' => [
                    'A' => 'De mand met klamme theedoeken die pas morgenochtend wordt opgehaald.',
                    'B' => 'De mand met witte schorten die de kok zo direct naar de wasserette brengt.',
                    'C' => 'Open en bloot bovenop de stapel schone servetten.',
                ],
                'feedback_wrong' => 'Tante Ans roept: "Nee sukkel! De kok pakte die mand direct vast en sneed zijn vinger. Alarm!"',
            ],

            // 6. De Pillenronde Saboteren
            'pillenronde-saboteren' => [
                'id' => 6,
                'npc_name' => 'Zuster Ingrid',
                'npc_avatar' => 'assets/characters/avatar_female_1.jpg',
                'npc_role' => 'Apotheek Hoofd',
                'dilemma' => 'Op het medicijnkarretje staan bakjes Diazepam en vitamine C. Hoe verwissel je de etiketten zonder dat de camera in de gang het ziet?',
                'correct_choice' => 'B',
                'choices' => [
                    'A' => 'Het hele medicijnkarretje omschoppen en hard wegrennen.',
                    'B' => 'Een dienblad met thee laten vallen zodat Ingrid moet dweilen, en dan bliksemsnel wisselen.',
                    'C' => 'Aan Ingrid vragen of ze de Diazepam gratis aan je wil geven.',
                ],
                'feedback_wrong' => 'Ingrid kijkt je vernietigend aan: "Dacht je nou echt dat ik daar in trap? Beveiliging!"',
            ],

            // 7. Peuk Ritselen bij het Hek
            'peuk-ritselen-hek' => [
                'id' => 7,
                'npc_name' => 'Kettingroker Kees',
                'npc_avatar' => 'assets/characters/avatar_male_4.jpg',
                'npc_role' => 'Binnenplaats Regelaar',
                'dilemma' => 'Buiten het hek gooit een contactpersoon een tennisbal met 4 zware shagjes erin. De wind waait met windkracht 5 naar het westen. Waar ga je staan?',
                'correct_choice' => 'A',
                'choices' => [
                    'A' => 'Aan de westkant van het basketbalveld achter de afvalcontainer.',
                    'B' => 'Pal onder de neus van de bewaker bij de oostpoort.',
                    'C' => 'Midden op de open tegelvloer met je armen in de lucht zwaaiend.',
                ],
                'feedback_wrong' => 'Kees kermt: "De bal waaide ver naar het westen en jij stond doodleuk in het oosten! De hond vond hem eerst."',
            ],

            // 8. Afwashulp Afpersen
            'afwashulp-afpersen' => [
                'id' => 8,
                'npc_name' => 'Rooie Ronald',
                'npc_avatar' => 'assets/characters/avatar_male_5.jpg',
                'npc_role' => 'Kantoorknecht Onderwereld',
                'dilemma' => 'Afwashulp Jantje verdient €300 per week. Als we 40% protectiegeld eisen, hoeveel euro moet Jantje dan wekelijks aan ons overhandigen?',
                'correct_choice' => 'C',
                'choices' => [
                    'A' => '€80 per week',
                    'B' => '€100 per week',
                    'C' => '€120 per week (40% van €300)',
                ],
                'feedback_wrong' => 'Ronald slaat op zijn voorhoofd: "Je berekent te weinig! Jantje lachte je vierkant uit en ging naar de zaalwacht."',
            ],

            // 9. Pekkie Peuken Smokkelen
            'pekkie-peuken-smokkelen' => [
                'id' => 9,
                'npc_name' => 'Rooie Ronald',
                'npc_avatar' => 'assets/characters/avatar_male_5.jpg',
                'npc_role' => 'Wasserette Koning',
                'dilemma' => 'Er zitten 20 pakjes peuken verstopt in de trommel van wasmachine 4. Wasmachine 4 gaat over 10 minuten centrifugeren op 1400 toeren. Wat doe je?',
                'correct_choice' => 'B',
                'choices' => [
                    'A' => 'Wachten tot het programma klaar is met wasverzachter.',
                    'B' => 'De hoofdschakelaar van de stopcontacten omlaag trekken en de trommel forceren.',
                    'C' => 'Zeep toevoegen en hopen dat de shag waterproof is.',
                ],
                'feedback_wrong' => 'Ronald roept: "Alle peuken zijn veranderd in natte tabakspap! Totale strop!"',
            ],

            // 10. Pingpong Ballen Diefstal
            'pingpong-ballen-diefstal' => [
                'id' => 10,
                'npc_name' => 'Stille Stef',
                'npc_avatar' => 'assets/characters/avatar_male_6.jpg',
                'npc_role' => 'Recreatie Kluizenaar',
                'dilemma' => 'In de pingpongballen zit fijn wit poeder gesmokkeld. Er liggen 6 witte ballen en 4 oranje ballen in het mandje. De gesmokkelde ballen zijn alleen oranje. Hoeveel procent kans heb je bij een willekeurige greep?',
                'correct_choice' => 'A',
                'choices' => [
                    'A' => '40% kans (4 van de 10 ballen)',
                    'B' => '60% kans (6 van de 10 ballen)',
                    'C' => '25% kans (1 van de 4 ballen)',
                ],
                'feedback_wrong' => 'Stef fluistert: "Verkeerde berekening! Je grabbelde een gewone bal en werd betrapt toen je hem doormidden sneed."',
            ],

            // 11. Douchehokje Confisqueren
            'douchehokje-confisqueren' => [
                'id' => 11,
                'npc_name' => 'Grote Barry',
                'npc_avatar' => 'assets/characters/barry.jpg',
                'npc_role' => 'Blok A Zwaargewicht',
                'dilemma' => 'Drie rivaliserende nieuwelingen willen de warme hoekdouche inpikken. Hoe claim je dit territorium zonder direct bloedvergieten?',
                'correct_choice' => 'C',
                'choices' => [
                    'A' => 'Vriendelijk vragen of ze alsjeblieft opzij willen stappen.',
                    'B' => 'Je handdoek op de vloer gooien en gaan huilen in de hoek.',
                    'C' => 'Breedgeschouderd voor de kraan gaan staan, oogcontact houden en koud je biceps aanspannen.',
                ],
                'feedback_wrong' => 'Grote Barry schudt zijn hoofd: "Geen ruggengraat getoond! Ze duwden je direct onder de ijskoude straal."',
            ],

            // 12. Magazijn Inventaris Vervalsen
            'magazijn-inventaris-vervalsen' => [
                'id' => 12,
                'npc_name' => 'Magazijnmeester Mo',
                'npc_avatar' => 'assets/characters/malik.jpg',
                'npc_role' => 'Centraal Depot Beheerder',
                'dilemma' => 'Op het digitale formulier staan 50 dozen zeep geregistreerd. We hebben er 15 zwart verkocht. Welke code voer je in als breukpercentage?',
                'correct_choice' => 'B',
                'choices' => [
                    'A' => 'Code 99 (Gevallen van een vrachtwagen - vereist handtekening van de directrice)',
                    'B' => 'Code 30 (Waterschade door lekkage spoelbak - geen contra-expertise)',
                    'C' => 'Code 01 (Diefstal door medegedetineerden)',
                ],
                'feedback_wrong' => 'Mo zucht: "Die code vereist direct inspectiebezoek! De accountant staat al voor de deur!"',
            ],

            // 13. Telefoontijd Verlengen
            'telefoontijd-verlengen' => [
                'id' => 13,
                'npc_name' => 'Wesley de Ritselaar',
                'npc_avatar' => 'assets/characters/wesley.jpg',
                'npc_role' => 'Telefooncel Makelaar',
                'dilemma' => 'De telefoon tikt elke minuut 20 cent af. Je hebt €1,80 aan beltegoed ingeworpen. Hoeveel volle minuten kun je maximaal bellen met je advocaat?',
                'correct_choice' => 'A',
                'choices' => [
                    'A' => '9 minuten (€1,80 / €0,20)',
                    'B' => '8 minuten',
                    'C' => '10 minuten',
                ],
                'feedback_wrong' => 'Wesley lacht: "Je verbinding werd midden in het pleidooi verbroken! Slechte timing!"',
            ],

            // 14. Extra Vlees Binnensmokkelen
            'extra-vlees-binnensmokkelen' => [
                'id' => 14,
                'npc_name' => 'Henkie de Frikandelkoning',
                'npc_avatar' => 'assets/characters/henkie.jpg',
                'npc_role' => 'Kantine Baas',
                'dilemma' => 'De koelwagenchauffeur levert 10 kilo biefstuk af voor de directietafel. We willen 3 kilo onderscheppen. Waar verwisselen we het gewicht?',
                'correct_choice' => 'C',
                'choices' => [
                    'A' => 'Vervangen door 3 kilo stenen in de plastic verpakking.',
                    'B' => 'Gewoon de hele doos onder je trui stoppen en fluitend weglopen.',
                    'C' => 'Vervangen door 3 kilo diepvriessoepvlees met dezelfde officiële sticker.',
                ],
                'feedback_wrong' => 'Henkie grijpt zijn hoofd: "Stenen rammelen in een vleeskrat! De chauffeur zag het meteen!"',
            ],

            // 15. Verlofformulier Vervalsen
            'verlofformulier-vervalsen' => [
                'id' => 15,
                'npc_name' => 'Notaris Nico',
                'npc_avatar' => 'assets/characters/avatar_male_1.jpg',
                'npc_role' => 'Documentvervalser Paviljoen 2',
                'dilemma' => 'Welke kleur inkt gebruikt de behandelend arts altijd voor het afstempelen van proefverlof categorie B?',
                'correct_choice' => 'B',
                'choices' => [
                    'A' => 'Felle rode balpenstift',
                    'B' => 'Donkerblauwe archief-inkt met droogstempel',
                    'C' => 'Zwarte viltstift',
                ],
                'feedback_wrong' => 'Nico kreunt: "Rode stift?! Dat is afkeuringsinkt! Je formulier werd direct vernietigd in de versnipperaar!"',
            ],

            // 16. Portier Peter Omkopen
            'portier-peter-omkopen' => [
                'id' => 16,
                'npc_name' => 'Portier Peter',
                'npc_avatar' => 'assets/characters/avatar_male_3.jpg',
                'npc_role' => 'Wachtcommandant Hoofdingang',
                'dilemma' => 'Peter heeft een zwak voor zoetigheid en dure sigaren. Wat leg je subtiel in zijn locker om een oogje dicht te knijpen bij de scanpoort?',
                'correct_choice' => 'A',
                'choices' => [
                    'A' => 'Een doos Cubaanse sigaren en een verse banketstaaf met een briefje "Voor de fijnproever".',
                    'B' => 'Een envelop met briefjes van 5 euro openlijk op zijn bureau leggen.',
                    'C' => 'Een briefje waarin je dreigt zijn banden lek te steken als hij niet meewerkt.',
                ],
                'feedback_wrong' => 'Peter trekt wit weg van woede: "Dreigen aan de poort?! Boevenbusje erbij, isoleercel in!"',
            ],

            // 17. Dokter Debbie Afleiden
            'dokter-debbie-afleiden' => [
                'id' => 17,
                'npc_name' => 'Dokter Debbie',
                'npc_avatar' => 'assets/characters/debbie.jpg',
                'npc_role' => 'Hoofdpsychologe Spreekkamer 1',
                'dilemma' => 'Debbie heeft de sleutel van de dossierkast op haar bureau liggen. Ze vraagt: "Hoe voel je je vandaag over je agressieregulatie?"',
                'correct_choice' => 'B',
                'choices' => [
                    'A' => 'Ik wil slaan! Geef me die sleutels nu meteen!',
                    'B' => '"Ik ervaar een diepe innerlijke rust, dokter. Kunt u me nog eens uitleggen hoe die Freudiaanse afweermechanismen werken?"',
                    'C' => 'Helemaal niks zeggen en strak naar haar neus staren zonder te knipperen.',
                ],
                'feedback_wrong' => 'Debbie tikt op het noodalarm onder haar bureau: "Agressief gedrag gesignaleerd!"',
            ],

            // 18. Schoonmaakwagen Kapen
            'schoonmaakwagen-kapen' => [
                'id' => 18,
                'npc_name' => 'Schoonmaker Sjors',
                'npc_avatar' => 'assets/characters/avatar_male_4.jpg',
                'npc_role' => 'Corridor Schoonmaak',
                'dilemma' => 'In de dubbele bodem van de poetswagen past 1 persoon of 4 gereedschapskisten. Welke wagen neem je?',
                'correct_choice' => 'C',
                'choices' => [
                    'A' => 'De felgele wagen met een piepend wiel dat door de hele gang galmt.',
                    'B' => 'De wagen waar Sjors op dit moment zelf zijn lunchpakket op eet.',
                    'C' => 'De blauwe wagen met vers gesmeerde rubberen wielen en een groot linnen wasvak.',
                ],
                'feedback_wrong' => 'Sjors zag je meteen weglopen met zijn piepende wagen: "Hé dief, blijf van mijn mop af!"',
            ],

            // 19. Bajeskrant Censureren
            'bajeskrant-censureren' => [
                'id' => 19,
                'npc_name' => 'Redacteur Remco',
                'npc_avatar' => 'assets/characters/avatar_male_2.jpg',
                'npc_role' => 'Bibliotheek Drukpers',
                'dilemma' => 'Er staat een artikel in de bajeskrant over onze smokkelroute via het washok. Hoe haal je de oplage van 200 stuks uit de roulatie?',
                'correct_choice' => 'A',
                'choices' => [
                    'A' => 'De drukpers verwisselen met een overdruk van het sportzaalrooster voordat de bewakers controleren.',
                    'B' => 'De hele bibliotheek in de hens steken met een aansteker.',
                    'C' => 'Alle 200 kranten zelf opeten tijdens het ontbijt.',
                ],
                'feedback_wrong' => 'Remco schrikt: "Een brandalarm roept de ME op! Dat was de domste actie ooit!"',
            ],

            // 20. Mopstok Gevecht Luchtplaats
            'mopstok-gevecht' => [
                'id' => 20,
                'npc_name' => 'Barry Biceps',
                'npc_avatar' => 'assets/characters/barry.jpg',
                'npc_role' => 'Luchtplaats Beul',
                'dilemma' => 'Twee bewoners vallen je aan met verzwaarde mopstokken op de natte tegels. Wat is je verdedigende reactie?',
                'correct_choice' => 'B',
                'choices' => [
                    'A' => 'Recht naar voren duiken met je kin omhoog.',
                    'B' => 'Achteruit stappen tegen de bakstenen muur, de eerste slag pareren en met een lage veeg de tegenstander laten uitglijden.',
                    'C' => 'Stil blijven staan en roepen dat geweld verboden is volgens het huisreglement.',
                ],
                'feedback_wrong' => 'Barry kreunt: "Je stond als een zoutpilaar! Vol op je neus geraakt!"',
            ],

            // 21. Sleutelbos Afgieten in Zeep
            'sleutelbos-afgieten' => [
                'id' => 21,
                'npc_name' => 'Slinkse Sam',
                'npc_avatar' => 'assets/characters/avatar_male_2.jpg',
                'npc_role' => 'Meestersmid Werkplaats',
                'dilemma' => 'Bewaker Henk legt zijn sleutelbos 45 seconden op de werkbank. Welk type zeepblok gebruik je voor de zuiverste afdruk?',
                'correct_choice' => 'C',
                'choices' => [
                    'A' => 'Vloeibare handzeep uit de dispenserpompen.',
                    'B' => 'Droge brokkelige zandzeep met schuurkorrels.',
                    'C' => 'Een zacht verwarmd blok harde groene zeep ingevet met vaseline.',
                ],
                'feedback_wrong' => 'Sam lacht spottend: "Vloeibare zeep?! Hoe wilde je daar een sleutel van gieten, genie?!"',
            ],

            // 22. Sigaretten Automaat Kraken
            'sigaretten-automaat-kraken' => [
                'id' => 22,
                'npc_name' => 'Rooie Ronald',
                'npc_avatar' => 'assets/characters/avatar_male_5.jpg',
                'npc_role' => 'Kantine Kraker',
                'dilemma' => 'Het muntmechanisme accepteert alleen 2 euro munten. We gebruiken een afgevijld koperen sluitringetje met diameter 25,75 mm. Welke sleuf pak je?',
                'correct_choice' => 'A',
                'choices' => [
                    'A' => 'De hoofdinwerpgleuf met een magneetstrip erlangs om de sensor te foppen.',
                    'B' => 'De geldteruggavebak met een schroevendraaier open wrikken terwijl de kantinebaas toekijkt.',
                    'C' => 'De stekker uit het stopcontact trekken en hard tegen de ruit trappen.',
                ],
                'feedback_wrong' => 'Ronald roept: "Het glas versplinterde met een luid alarm! De zaalwacht stond direct achter je!"',
            ],

            // 23. Bezoekersruimte Smokkeldeal
            'bezoekersruimte-smokkeldeal' => [
                'id' => 23,
                'npc_name' => 'Samantha Slotjes',
                'npc_avatar' => 'assets/characters/samantha.jpg',
                'npc_role' => 'Bezoekerszaal Contact',
                'dilemma' => 'Tijdens het bezoekuur zit tante Truus tegenover je met een zak chips waar €2.000 cash in zit verstopt. Bewaker Gerrie loopt rondjes. Wanneer wissel je?',
                'correct_choice' => 'B',
                'choices' => [
                    'A' => 'Direct bij binnenkomst de zak chips over de tafel smijten.',
                    'B' => 'Als Gerrie stopt om koffie in te schenken bij tafel 1, de chipszak rustig onder tafel van knie naar knie schuiven.',
                    'C' => 'Aan Gerrie vragen of hij ook een chipje wil proeven.',
                ],
                'feedback_wrong' => 'Samantha slaat haar handen voor haar ogen: "Je bood de bewaker letterlijk de smokkelwaar aan! Onvoorstelbaar!"',
            ],

            // 24. Hoofdbehandelaar Omkopen
            'hoofdbehandelaar-omkopen' => [
                'id' => 24,
                'npc_name' => 'Dr. Van Driel',
                'npc_avatar' => 'assets/characters/elara.jpg',
                'npc_role' => 'Hoofdbehandelaar Forensische Zorg',
                'dilemma' => 'Dr. Van Driel zoekt financiering voor haar privé-onderzoek naar recidive. Hoe benader je haar op een diplomatieke manier?',
                'correct_choice' => 'A',
                'choices' => [
                    'A' => 'Een formele schenkingsovereenkomst via een externe stichting voor haar academische publicatie voorstellen.',
                    'B' => 'Een rolletje van 50 euro biljetten in haar labjas proppen en knipogen.',
                    'C' => 'Dreigen dat je haar auto bekrast als je verlof niet wordt goedgekeurd.',
                ],
                'feedback_wrong' => 'Dr. Van Driel trekt aan de alarmbel: "Dit is een grove poging tot omkoping en bedreiging!"',
            ],

            // 25. Zusterpost Telefoon Aftappen
            'zusterpost-telefoon-aftappen' => [
                'id' => 25,
                'npc_name' => 'Hacker Harold',
                'npc_avatar' => 'assets/characters/avatar_male_6.jpg',
                'npc_role' => 'Telecom Tovenaar',
                'dilemma' => 'In de kabelgoot van Blok C lopen 4 draden: rood (+5V), blauw (audio TX), geel (audio RX), en zwart (aarde). Op welke draden sluit je de zender aan?',
                'correct_choice' => 'C',
                'choices' => [
                    'A' => 'Rood en zwart direct tegen elkaar kortsluiten.',
                    'B' => 'Alleen op de rode draad met een kroonsteentje.',
                    'C' => 'Blauw en geel parallel aftappen met een condensator naar de audiomodule.',
                ],
                'feedback_wrong' => 'Harold schudt zijn hoofd: "Kortsluiting! De hele zusterpost zat zonder stroom en de noodcentrale sloeg aan!"',
            ],

            // 26. Isoleercel Mechanisme Blokkeren
            'isoleercel-deurmechanisme' => [
                'id' => 26,
                'npc_name' => 'Meester Hans',
                'npc_avatar' => 'assets/characters/avatar_male_1.jpg',
                'npc_role' => 'Oud-Bajes Slotenspecialist',
                'dilemma' => 'De zware stalen nachtgrendel valt automatisch in het kozijn. Wat schuif je in het slotgat zodat de deur dicht lijkt maar niet vergrendelt?',
                'correct_choice' => 'B',
                'choices' => [
                    'A' => 'Een hele rol toiletpapier in het gat stampen.',
                    'B' => 'Een gevouwen hard plastic strip van een magnetronmaaltijd met magneetvoetje.',
                    'C' => 'Een bezemsteel tussen de deurkier laten steken.',
                ],
                'feedback_wrong' => 'Meester Hans bromt: "Een bezemsteel tussen de deur?! Zelfs een blinde zaalwacht ziet dat!"',
            ],

            // 27. Beveiligingspas Dupliceren
            'beveiligingspas-dupliceren' => [
                'id' => 27,
                'npc_name' => 'Notaris Nico',
                'npc_avatar' => 'assets/characters/avatar_male_1.jpg',
                'npc_role' => 'RFID Clone Specialist',
                'dilemma' => 'De paslezer werkt op 13,56 MHz MIFARE Classic. Hoe dichtbij moet de RFID-cloner gehouden worden bij de heup van de zaalwacht?',
                'correct_choice' => 'A',
                'choices' => [
                    'A' => 'Binnen 5 tot 10 centimeter gedurende minimaal 2 seconden.',
                    'B' => 'Minstens 10 meter afstand vanaf de overkant van het plein.',
                    'C' => 'Je moet de pas fysiek in de fik steken om de chip te activeren.',
                ],
                'feedback_wrong' => 'Nico zucht: "Vanaf 10 meter afstand scant geen enkele RFID! Je hebt helemaal niks opgenomen!"',
            ],

            // 28. Nachtpoker Ketelhuis
            'nachtpoker-ketelhuis' => [
                'id' => 28,
                'npc_name' => 'Slinkse Sam',
                'npc_avatar' => 'assets/characters/avatar_male_2.jpg',
                'npc_role' => 'Ketelhuis Kaartbaas',
                'dilemma' => 'In de pot zit €1.200 aan sigarettenbonnen. De speler tegenover je raist met €400. Jij hebt Three of a Kind boeren, er ligt een mogelijke Flush. Wat is de statistisch beste zet?',
                'correct_choice' => 'C',
                'choices' => [
                    'A' => 'Meteen all-in gaan en met je vuist op de gasketel slaan.',
                    'B' => 'De kaarten op de grond gooien en claimen dat er valsgespeeld is.',
                    'C' => 'Rustig callen op de Turn om zijn bet-patroon op de River te peilen.',
                ],
                'feedback_wrong' => 'Sam schudt zijn hoofd: "Hij had een geturnde Flush en jij raakte al je fiches in 1 klap kwijt!"',
            ],

            // 29. Kantine Leverancier Onderscheppen
            'kantine-leverancier-onderscheppen' => [
                'id' => 29,
                'npc_name' => 'Henkie de Frikandelkoning',
                'npc_avatar' => 'assets/characters/henkie.jpg',
                'npc_role' => 'Laadkuil Coördinator',
                'dilemma' => 'De vrachtwagen van de Hanos arriveert om 06:30. Chauffeur Daan moet tekenen bij de portier. Hoeveel tijd hebben we in de laadbak?',
                'correct_choice' => 'B',
                'choices' => [
                    'A' => 'Precies 45 minuten omdat Daan altijd uitgebreid gaat douchen.',
                    'B' => 'Exact 6 tot 8 minuten terwijl Daan de vrachtbrief laat afstempelen.',
                    'C' => '2 seconden, dus je moet er met een katapult in schieten.',
                ],
                'feedback_wrong' => 'Henkie trekt aan je jas: "Je bleef te lang hangen! Daan deed de rolcontainer met een klap op slot met jou erin!"',
            ],

            // 30. Camera’s Afplakken Blok B
            'cameras-afplakken' => [
                'id' => 30,
                'npc_name' => 'Hacker Harold',
                'npc_avatar' => 'assets/characters/avatar_male_6.jpg',
                'npc_role' => 'Optisch Strateeg',
                'dilemma' => 'De domecamera in Blok B heeft infrarood nachtzicht. Als je tape op de lens plakt, ziet de meldkamer meteen een zwart scherm. Hoe verblind je de camera subtieler?',
                'correct_choice' => 'A',
                'choices' => [
                    'A' => 'Een dun laagje vaseline op de bol smeren zodat het beeld vaag en wazig lijkt door condens.',
                    'B' => 'Met een spuitbus felrode verf een kruis over de lens spuiten.',
                    'C' => 'Een baksteen tegen de camera gooien.',
                ],
                'feedback_wrong' => 'Harold grijpt zijn haar: "Rode verf triggert direct een lens-obstructie alarm in de meldkamer!"',
            ],

            // 31. Het Verdwenen Budget
            'het-verdwenen-budget' => [
                'id' => 31,
                'npc_name' => 'Directeur Gerda Graaigraag',
                'npc_avatar' => 'assets/characters/caroline.jpg',
                'npc_role' => 'Financieel Directeur',
                'dilemma' => 'Er is €15.000 subsidie voor recreatieve klei-therapie verdwenen. De accountant wil een post zien waar dit bedrag geloofwaardig in kan verdwijnen.',
                'correct_choice' => 'C',
                'choices' => [
                    'A' => '"Aanschaf van gouden toiletborstels voor de directie".',
                    'B' => '"Verloren bij het gokken in Las Vegas".',
                    'C' => '"Groot onderhoud luchtbehandeling en asbestsanering filterinstallatie Paviljoen 4".',
                ],
                'feedback_wrong' => 'Gerda gilt: "Gouden toiletborstels?! De inspectie heeft per direct beslag gelegd op de administratie!"',
            ],

            // 32. Brandmelder Sabotage
            'brandmelder-sabotage' => [
                'id' => 32,
                'npc_name' => 'Fikse Frank',
                'npc_avatar' => 'assets/characters/avatar_male_5.jpg',
                'npc_role' => 'Pyrotechnicus Hal Oost',
                'dilemma' => 'De optische rookmelder werkt met een fotodiode en infraroodstraal. Hoe vertraag je de activering met 10 minuten?',
                'correct_choice' => 'B',
                'choices' => [
                    'A' => 'Een brandende krant direct onder de melder houden.',
                    'B' => 'Een geperforeerd plastic koffiebekertje over de sensor klikken met een elastiekje.',
                    'C' => 'Er water in gieten met een plantenspuit.',
                ],
                'feedback_wrong' => 'Frank rent weg: "De melder ging meteen gillend af! De sprinklers spuiten alles onder!"',
            ],

            // 33. Doktersvoorschrift Herschrijven
            'dokters-voorschrift-herschrijven' => [
                'id' => 33,
                'npc_name' => 'Zuster Ingrid',
                'npc_avatar' => 'assets/characters/avatar_female_1.jpg',
                'npc_role' => 'Apotheek Paviljoen B',
                'dilemma' => 'Op het recept staat "10mg Temazepam". Je wilt de dosering verdubbelen zonder dat de apotheker argwaan krijgt.',
                'correct_choice' => 'A',
                'choices' => [
                    'A' => 'Van het cijfer 1 netjes een 2 maken met identieke zwarte inkt en "2 dd 10mg" noteren in de kantlijn.',
                    'B' => 'Er met een rode stift "GEEF MIJ 100 PILLEN" achter schrijven.',
                    'C' => 'Het hele briefje doorkrassen en opnieuw op een bierviltje schrijven.',
                ],
                'feedback_wrong' => 'Ingrid kijkt je hoofdschuddend aan: "Een bierviltje als doktersrecept?! Ga je cel maar in!"',
            ],

            // 34. Directiewagen Banden Lekprikken
            'directiewagen-banden-lekprikken' => [
                'id' => 34,
                'npc_name' => 'Barry Biceps',
                'npc_avatar' => 'assets/characters/barry.jpg',
                'npc_role' => 'Parkeerplaats Saboteur',
                'dilemma' => 'De Mercedes van de directeur staat onder de lantaarnpaal. Hoe laat je de band leeglopen zonder knalgeluid?',
                'correct_choice' => 'C',
                'choices' => [
                    'A' => 'Met een handgranaat onder het wiel rollen.',
                    'B' => 'Met een botte bijl tegen het loopvlak hakken.',
                    'C' => 'Een klein steentje in het ventieldopje draaien zodat hij geruisloos leegloopt.',
                ],
                'feedback_wrong' => 'Barry vloekt: "Wat een kabaal! De hele bewaking stond binnen 10 seconden buiten!"',
            ],

            // 35. De Gouden Frituurmand
            'gouden-frituurmand' => [
                'id' => 35,
                'npc_name' => 'Henkie de Frikandelkoning',
                'npc_avatar' => 'assets/characters/henkie.jpg',
                'npc_role' => 'Grote Instellingskeuken',
                'dilemma' => 'De frituurovens staan ingesteld op 180 graden. Waar verstoppen we de diamanten tijdens de verrassingsinspectie van de voedsel- en warenautoriteit?',
                'correct_choice' => 'B',
                'choices' => [
                    'A' => 'In het hete frituurvet gooien en hopen dat ze niet smelten.',
                    'B' => 'Ingepakt in hittebestendige folie in de holle stang van de afzuigkapmotor.',
                    'C' => 'Bovenop de snijplank tussen de uienringen laten liggen.',
                ],
                'feedback_wrong' => 'Henkie schreeuwt: "De inspecteur sneed ze bijna doormidden met het koksmes! Kwijt!"',
            ],

            // 36. Bewakersrooster Hacken
            'bewakers-rooster-hacken' => [
                'id' => 36,
                'npc_name' => 'Hacker Harold',
                'npc_avatar' => 'assets/characters/avatar_male_6.jpg',
                'npc_role' => 'Serverruimte Infiltrant',
                'dilemma' => 'Het roostersysteem draait op een lokale Windows server. Welk commando gebruik je om de nachtdienst van bewaker Peter om te wisselen naar de slaperige conciërge?',
                'correct_choice' => 'A',
                'choices' => [
                    'A' => 'Een SQL injection payload via het interne webformulier van het ploegendienstenportaal.',
                    'B' => 'Met een hamer op het toetsenbord slaan tot het scherm flikkert.',
                    'C' => 'Het scherm omdraaien en hopen dat de bewakers andersom gaan lopen.',
                ],
                'feedback_wrong' => 'Harold kijkt wanhopig: "Je hebt het hele datacenter offline geslagen! Foutcode 500 met toeterend alarm!"',
            ],

            // 37. Paviljoen 13 Ontgrendelen
            'paviljoen-13' => [
                'id' => 37,
                'npc_name' => 'Samantha Slotjes',
                'npc_avatar' => 'assets/characters/samantha.jpg',
                'npc_role' => 'Corridor Paviljoen 13',
                'dilemma' => 'Paviljoen 13 is de zwaarbeveiligde bunker met een dubbele magneetsluis. Als deur 1 open is, blijft deur 2 mechanisch geblokkeerd. Hoe kom je erdoor?',
                'correct_choice' => 'B',
                'choices' => [
                    'A' => 'Met je volle gewicht tegen de stalen deur van 500 kilo beuken.',
                    'B' => 'De optische sensor van deur 1 manipuleren met een reflector zodat het systeem denkt dat hij al gesloten is.',
                    'C' => 'Vriendelijk aanbellen bij de intercom en vragen of iemand open wil doen.',
                ],
                'feedback_wrong' => 'Samantha zucht: "De intercombewaker vroeg direct je patiëntnummer en stuurde het arrestatieteam!"',
            ],

            // 38. Binnenplaats Muur Inspecteren
            'binnenplaats-muur-inspecteren' => [
                'id' => 38,
                'npc_name' => 'De Meestergraver',
                'npc_avatar' => 'assets/characters/oohpa_arjan.jpg',
                'npc_role' => 'Tunnel Architect Oostmuur',
                'dilemma' => 'De oostmuur bestaat uit gewapend beton van 40 cm dik. Waar bevindt zich de zwakste constructievoeg volgens de oude bouwtekeningen uit 1974?',
                'correct_choice' => 'C',
                'choices' => [
                    'A' => 'Midden onder de schijnwerper van de hoofdwachttoren.',
                    'B' => 'Vlak naast het wachthuisje waar twee herdershonden slapen.',
                    'C' => 'Achter de regenwaterafvoerbuis 3 meter links van de oude kolenopslag.',
                ],
                'feedback_wrong' => 'De Meestergraver zucht: "Je begon te bikken recht onder de neus van de honden! Ze sloegen meteen aan!"',
            ],

            // 39. Psychiatrisch Rapport Stelen
            'psychiatrisch-rapport-stelen' => [
                'id' => 39,
                'npc_name' => 'Dr. Van Driel',
                'npc_avatar' => 'assets/characters/elara.jpg',
                'npc_role' => 'Archief Toren',
                'dilemma' => 'In je dossier staat een vernietigend advies over tbs-verlenging. Het dossier zit in een stalen ladekast met cijferslot 4 cijfers. De code is het geboortejaar van de oprichter van de kliniek (1968). Welke combinatie toets je in?',
                'correct_choice' => 'B',
                'choices' => [
                    'A' => '0 0 0 0',
                    'B' => '1 9 6 8',
                    'C' => '9 9 9 9',
                ],
                'feedback_wrong' => 'Dr. Van Driel stapt binnen: "Drie keer verkeerde code en het cijferslot blokkeert voor 24 uur met luid piepen!"',
            ],

            // 40. De Centrale Medicijnkluis
            'centrale-medicijnkluis' => [
                'id' => 40,
                'npc_name' => 'Dokter De Vries',
                'npc_avatar' => 'assets/characters/debbie.jpg',
                'npc_role' => 'Centrale Apotheek',
                'dilemma' => 'De kluisdeur heeft een tijdslot dat pas om 08:00 ontgrendelt. Het is nu 03:30. Hoe omzeil je het elektronische relais op het moederbord?',
                'correct_choice' => 'A',
                'choices' => [
                    'A' => 'Een 12V overbruggingsdraad solderen over de collector van transistor Q3.',
                    'B' => 'De hele kluis met een koevoet proberen op te tillen.',
                    'C' => 'Een föhn tegen het tijdmechanisme houden om de tijd sneller te laten lopen.',
                ],
                'feedback_wrong' => 'Dokter De Vries schudt haar hoofd: "Een föhn?! Het display smolt en het brandalarm ging loeien!"',
            ],

            // 41. Schijnwerpers Uitschakelen
            'schijnwerpers-uitschakelen' => [
                'id' => 41,
                'npc_name' => 'Hacker Harold',
                'npc_avatar' => 'assets/characters/avatar_male_6.jpg',
                'npc_role' => 'Wachttoren 2 Technicus',
                'dilemma' => 'Wachttoren 2 verlicht de noordelijke perimeter met 2000 Watt Xenon lampen. Welke zekering in de verdeelkast schakelt alleen toren 2 uit zonder de hekstroom te onderbreken?',
                'correct_choice' => 'C',
                'choices' => [
                    'A' => 'Hoofdschakelaar A (kapt de stroom van de hele kliniek af)',
                    'B' => 'Zekering F1 (perimeter schrikdraad)',
                    'C' => 'Zekering B4 (Schijnwerpercircuit Toren 2 & 3)',
                ],
                'feedback_wrong' => 'Harold roept: "Je trok F1 eruit! Het alarm \'Perimeter Breach\' ging direct af bij de politie!"',
            ],

            // 42. Helikopterplatform Markeren
            'helikopterplatform-markeren' => [
                'id' => 42,
                'npc_name' => 'Slinkse Sam',
                'npc_avatar' => 'assets/characters/avatar_male_2.jpg',
                'npc_role' => 'Dak Paviljoen 4 Strateeg',
                'dilemma' => 'De ontsnappingshelikopter nadert in het holst van de nacht. Welke markering gebruikt de piloot met zijn nachtkijker (NVG)?',
                'correct_choice' => 'B',
                'choices' => [
                    'A' => 'Grote fakkels aansteken met dieselolie op het dak.',
                    'B' => 'Vier infrarood glowsticks in een vierkant van 10 bij 10 meter vastbinden.',
                    'C' => 'Met een fel wit zaklampje recht in de cockpit van de heli schijnen.',
                ],
                'feedback_wrong' => 'Sam gilt: "Je verblindde de piloot met je zaklamp! Hij moest direct afzwaaien!"',
            ],

            // 43. Bewakingshonden Kalmeren
            'bewakingshonden-kalmeren' => [
                'id' => 43,
                'npc_name' => 'Tante Ans',
                'npc_avatar' => 'assets/characters/avatar_female_3.jpg',
                'npc_role' => 'Kennel Expert',
                'dilemma' => 'Twee Mechelse herders stormen grommend op je af bij de achterpoort. Wat haal je uit je jaszak om ze af te leiden?',
                'correct_choice' => 'A',
                'choices' => [
                    'A' => 'Twee malse gebraden runderlappen ingesmeerd met kalmerende valeriaandruppels.',
                    'B' => 'Een bezem waarmee je wild in het rond zwaait.',
                    'C' => 'Harde knalerwten op de grond gooien.',
                ],
                'feedback_wrong' => 'Tante Ans kermt: "Wild zwaaien maakt ze alleen maar valser! Ze grepen direct je broekspijp!"',
            ],

            // 44. Uitkijkpost Saboteren
            'uitkijkpost-saboteren' => [
                'id' => 44,
                'npc_name' => 'De Meestergraver',
                'npc_avatar' => 'assets/characters/oohpa_arjan.jpg',
                'npc_role' => 'Wachttoren 4 Infiltrant',
                'dilemma' => 'De wachtpost heeft een thermoskan koffie staan. Bewaker Klaas neemt elke 20 minuten een flinke slok. Wat voeg je toe?',
                'correct_choice' => 'C',
                'choices' => [
                    'A' => 'Drie eetlepels keukenzout.',
                    'B' => 'Een scheut bleekwater.',
                    'C' => 'Een vloeibaar extract van slaapthee en melatonine.',
                ],
                'feedback_wrong' => 'De Meestergraver schudt zijn hoofd: "Bleekwater ruikt hij van kilometers afstand! Klaas sloeg direct alarm!"',
            ],

            // 45. Noodstroom Aggregaat Platleggen
            'noodstroom-aggregaat-platleggen' => [
                'id' => 45,
                'npc_name' => 'Fikse Frank',
                'npc_avatar' => 'assets/characters/avatar_male_5.jpg',
                'npc_role' => 'Generatorkamer Saboteur',
                'dilemma' => 'De enorme dieselgenerator start automatisch bij spanningsuitval. Hoe voorkom je dat de zware V12 motor aanslaat?',
                'correct_choice' => 'B',
                'choices' => [
                    'A' => 'De uitlaatpijp dichtstoppen met een oude theedoek.',
                    'B' => 'De brandstofafsluiter naar de injectiepomp dichtdraaien en de startaccu loskoppelen.',
                    'C' => 'Een emmer water over het stalen motorblok gooien.',
                ],
                'feedback_wrong' => 'Frank zucht: "Die doek werd met 200 bar uit de pijp geblazen toen de diesel aansloeg!"',
            ],

            // 46. Ontsnappingstunnel Graven
            'ontsnappingstunnel-graven' => [
                'id' => 46,
                'npc_name' => 'De Meestergraver',
                'npc_avatar' => 'assets/characters/oohpa_arjan.jpg',
                'npc_role' => 'Kelder Tunnelbaas',
                'dilemma' => 'We graven onder de recreatiezaal door natte zandgrond. Welke maatregel voorkomt dat het plafond van de tunnel instort op 3 meter diepte?',
                'correct_choice' => 'A',
                'choices' => [
                    'A' => 'Houten stutten om de 50 centimeter met dwarsbalken en multiplex platen plaatsen.',
                    'B' => 'Gewoon extra snel kruipen zodat het zand achter je kan instorten.',
                    'C' => 'Het plafond inspuiten met haarlak om het zand hard te maken.',
                ],
                'feedback_wrong' => 'De Meestergraver graaft je net op tijd uit: "Haarlak?! De halve gang stortte in!"',
            ],

            // 47. Hekstroom Doorknippen
            'hekstroom-doorknippen' => [
                'id' => 47,
                'npc_name' => 'Hacker Harold',
                'npc_avatar' => 'assets/characters/avatar_male_6.jpg',
                'npc_role' => 'Perimeter Hek Specialist',
                'dilemma' => 'Op het buitenhek staat 10.000 Volt pulserende schrikdraadspanning met een lus-detectie. Hoe knip je de draad zonder dat het lus-alarm afgaat?',
                'correct_choice' => 'C',
                'choices' => [
                    'A' => 'Direct doorknippen met een gewone keukenschaar.',
                    'B' => 'Met twee handen tegelijk vastpakken om de stroomkring te aarden.',
                    'C' => 'Eerst een geïsoleerde bypass-overbruggingskabel plaatsen tussen de twee polen, en pas daarna ertussen knippen.',
                ],
                'feedback_wrong' => 'Harold roept: "Direct knippen verbreekt de lus! Het alarm ging direct loeien in de centrale!"',
            ],

            // 48. Politieradio Interferentie
            'politieradio-interferentie' => [
                'id' => 48,
                'npc_name' => 'Slinkse Sam',
                'npc_avatar' => 'assets/characters/avatar_male_2.jpg',
                'npc_role' => 'Radiomast Strateeg',
                'dilemma' => 'Het C2000 communicatienetwerk van de politie gebruikt TETRA encryptie op 380-385 MHz. Hoe veroorzaak je 15 minuten lokale ruis op het terrein?',
                'correct_choice' => 'B',
                'choices' => [
                    'A' => 'Heel hard in een megafoon schreeuwen naar de zendmast.',
                    'B' => 'Een breedband RF-stoorzender activeren aangesloten op de hoogspanningsleiding van de zendmast.',
                    'C' => 'De antennekabel proberen door te kauwen met je tanden.',
                ],
                'feedback_wrong' => 'Sam lacht: "Schreeuwen in een megafoon?! De hele bewaking keek meteen naar je op het dak!"',
            ],

            // 49. Directeur Gijzelen met Vork
            'directeur-gijzelen-met-vork' => [
                'id' => 49,
                'npc_name' => 'Barry Biceps',
                'npc_avatar' => 'assets/characters/barry.jpg',
                'npc_role' => 'Bestuurskamer Aanvoerder',
                'dilemma' => 'Je staat in de bestuurskamer tegenover directeur Gerda. Ze reikt naar de rode alarmknop onder haar mahoniehouten bureau. Wat doe je?',
                'correct_choice' => 'A',
                'choices' => [
                    'A' => 'Haar arm kordaat vastgrijpen, de telefoonhoorn van de haak stoten en met kalme doch ijskoude stem eisen dat ze de poortcodes intikt.',
                    'B' => 'Met de vork in de lucht zwaaien en circusgeluiden maken.',
                    'C' => 'Zelf onder het bureau duiken om te kijken hoe de knop eruitziet.',
                ],
                'feedback_wrong' => 'Barry zucht: "Terwijl jij onder haar bureau dook, drukte ze doodleuk met haar voet op het pedaal!"',
            ],

            // 50. De Ultieme Kliniek Uitbraak
            'ultieme-kliniek-uitbraak' => [
                'id' => 50,
                'npc_name' => 'De Meestergraver',
                'npc_avatar' => 'assets/characters/oohpa_arjan.jpg',
                'npc_role' => 'Buitenpoort Grens Legende',
                'dilemma' => 'Je staat bij de buitenste slagboom. De vluchtauto staat klaar met draaiende motor. Bewaker Peter komt naar buiten gelopen met een klembord. Wat is de definitieve meesterzet?',
                'correct_choice' => 'B',
                'choices' => [
                    'A' => 'Stoppen, je handen omhoog doen en je dossier netjes overhandigen.',
                    'B' => 'Vol gas over de berm langs de slagboom scheuren terwijl de rookgranaten het zicht van de wachttorens volledig verblinden!',
                    'C' => 'Uitstappen en Peter vragen of hij een selfie wil maken voor de bajeskrant.',
                ],
                'feedback_wrong' => 'De Meestergraver schudt zijn hoofd: "Een selfie?! Peter klikte de handboeien meteen om je polsen!"',
            ],

            // 51. Gerechtsdeurwaarder Afschrikken
            'gerechtsdeurwaarder-afschrikken' => [
                'id' => 51,
                'npc_name' => 'Notaris Nico',
                'npc_avatar' => 'assets/characters/avatar_male_1.jpg',
                'npc_role' => 'Juridisch Strateeg',
                'dilemma' => 'De deurwaarder wil beslag leggen op onze zwarte banktegoeden via een gerechtelijk bevel. Welk verweer stopt de executie ter plekke?',
                'correct_choice' => 'A',
                'choices' => [
                    'A' => 'Een formeel schorsingsbesluit tonen op grond van artikel 438 Rv wegens een aanhangig executiegeschil bij de voorzieningenrechter.',
                    'B' => 'Zijn aktetas afpakken en in een plas water gooien.',
                    'C' => 'Doen alsof je doofstom bent en wegrennen naar de kantine.',
                ],
                'feedback_wrong' => 'Nico kreunt: "Een tas in het water gooien is vernieling van bewijs! De politie werd direct ingeschakeld!"',
            ],

            // 52. Justitieel Serverpark Infiltreren
            'justitieel-serverpark-infiltreren' => [
                'id' => 52,
                'npc_name' => 'Hacker Harold',
                'npc_avatar' => 'assets/characters/avatar_male_6.jpg',
                'npc_role' => 'Centrale Serverruimte',
                'dilemma' => 'De firewall van Justitie blokkeert alle externe poorten behalve HTTPS poort 443. Hoe sluip je ongezien data naar buiten?',
                'correct_choice' => 'C',
                'choices' => [
                    'A' => 'Spammen over poort 80 tot de router ontploft.',
                    'B' => 'Een USB-stick met een virus openlijk in het beeldscherm duwen.',
                    'C' => 'Data versleuteld tunnelen via DNS-over-HTTPS (DoH) requests naar een beveiligde externe server.',
                ],
                'feedback_wrong' => 'Harold roept: "Poort 80 staat potdicht! De security alerts stroomden binnen bij het NCSC!"',
            ],

            // 53. Transporthelikopter Radiocodes Kopiëren
            'transporthelikopter-radiocodes' => [
                'id' => 53,
                'npc_name' => 'Slinkse Sam',
                'npc_avatar' => 'assets/characters/avatar_male_2.jpg',
                'npc_role' => 'Dak Helipad Operator',
                'dilemma' => 'De luchtverkeersleiding gebruikt een dagelijks roterende transpondercode (Squawk). Welke 4-cijferige code duidt op een noodgeval met radiostoring?',
                'correct_choice' => 'B',
                'choices' => [
                    'A' => '7500 (Kaping)',
                    'B' => '7600 (Communicatiestoring / Lost Comms)',
                    'C' => '7700 (Algemeen Noodgeval)',
                ],
                'feedback_wrong' => 'Sam zucht: "Code 7500 betekent kaping! Twee F-16 straaljagers stonden binnen 4 minuten boven het complex!"',
            ],

            // 54. Directie Chantage met Verborgen Beelden
            'directie-chantage-beelden' => [
                'id' => 54,
                'npc_name' => 'Samantha Slotjes',
                'npc_avatar' => 'assets/characters/samantha.jpg',
                'npc_role' => 'Bestuurskantoor Spionne',
                'dilemma' => 'We hebben videobeelden waarop directeur Gerda kaviaar en champagne declareert als "therapeutisch materiaal". Hoe presenteer je deze beelden?',
                'correct_choice' => 'A',
                'choices' => [
                    'A' => 'Een anonieme USB-stick in een gouden envelop op haar bureau leggen met een korte eis tot intrekking van alle sancties.',
                    'B' => 'De beelden luidkeels projecteren op het grote scherm in de centrale eetzaal tijdens de warme maaltijd.',
                    'C' => 'De video op TikTok zetten met je eigen naam en celnummer erbij.',
                ],
                'feedback_wrong' => 'Samantha slaat haar hand voor haar mond: "Je eigen naam op TikTok?! De recherche stond binnen een half uur in je cel!"',
            ],

            // 55. Centrale Cameramuur Uitschakelen
            'centrale-cameramuur-uitschakelen' => [
                'id' => 55,
                'npc_name' => 'Hacker Harold',
                'npc_avatar' => 'assets/characters/avatar_male_6.jpg',
                'npc_role' => 'Meldkamer Beveiliging',
                'dilemma' => 'De meldkamer heeft 24 beeldschermen op één HDMI-matrix switch. Wat stuur je over de RS-232 seriële poort om een loop-beeld van 10 minuten geleden te bevriezen?',
                'correct_choice' => 'C',
                'choices' => [
                    'A' => 'Een emmer cola over de stekkerdoos gieten.',
                    'B' => 'De stroomkabel met een bijl doormidden hakken.',
                    'C' => 'Het hexadecimale video-freeze commando [0x02, 0x56, 0x46, 0x01, 0x03] naar de videomatrix zenden.',
                ],
                'feedback_wrong' => 'Harold roept: "Cola in de stekkerdoos veroorzaakte steekvlammen! De brandweer rukte met drie wagens uit!"',
            ],

            // 56. Illegaal Casino Paviljoen 13 Overnemen
            'casino-paviljoen-13-overnemen' => [
                'id' => 56,
                'npc_name' => 'Stille Stef',
                'npc_avatar' => 'assets/characters/avatar_male_6.jpg',
                'npc_role' => 'Bunker Paviljoen 13',
                'dilemma' => 'De illegale roulettetafel draait in de bunker. De croupier manipuleert het kogeltje met een verborgen magneet onder het laken. Hoe ontmasker je hem?',
                'correct_choice' => 'B',
                'choices' => [
                    'A' => 'De roulettetafel omver duwen en alle fiches in je zak proppen.',
                    'B' => 'Een sterke neodymium magneet uit je zak halen en hem demonstratief laten kleven aan het zogenaamde houten speelveld.',
                    'C' => 'Al je geld inzetten op nummer 0 en bidden.',
                ],
                'feedback_wrong' => 'Stef fluistert: "Je verloor alles op nummer 0! De croupier lachte je uit."',
            ],

            // 57. Internationaal Diplomatiek Paspoort
            'diplomatiek-paspoort-fabriceren' => [
                'id' => 57,
                'npc_name' => 'Notaris Nico',
                'npc_avatar' => 'assets/characters/avatar_male_1.jpg',
                'npc_role' => 'Geheime Drukkerij',
                'dilemma' => 'Een echt diplomatiek paspoort heeft OVI (Optically Variable Ink) op het wapenschild dat van goud naar groen kantelt. Welke foliedruk techniek gebruiken we?',
                'correct_choice' => 'A',
                'choices' => [
                    'A' => 'Warmtegevoelige holografische folie met micro-embossing onder 180 bar persdruk.',
                    'B' => 'Glitterlijm van de kleutergroep van de kinderopvang.',
                    'C' => 'Gewoon een gele markeerstift over het papier halen.',
                ],
                'feedback_wrong' => 'Nico kreunt: "Glitterlijm?! Zelfs de portier zag dat dit knutselwerk van niveau groep 3 was!"',
            ],

            // 58. Zaalwachtbrigade Onderwerpen
            'zaalwachtbrigade-onderwerpen' => [
                'id' => 58,
                'npc_name' => 'Barry Biceps',
                'npc_avatar' => 'assets/characters/barry.jpg',
                'npc_role' => 'Recreatiehal Vechtbaas',
                'dilemma' => 'Vier zaalwachten in oproeruitrusting naderen met schilden. Ze vormen een strakke linie. Waar breek je de formatie?',
                'correct_choice' => 'C',
                'choices' => [
                    'A' => 'Blindelings tegen het middelste schild aan beuken.',
                    'B' => 'Je overgeven en smeken om genade.',
                    'C' => 'De flanken omcirkelen, de buitenste schilddrager uit balans trekken en de formatie van achteren opbreken.',
                ],
                'feedback_wrong' => 'Barry zucht: "Tegen het midden aan beuken is precies wat ze willen! Je lag direct onder vier knuppels!"',
            ],

            // 59. Forensische Goudstaven Onderscheppen
            'forensische-goudstaven-onderscheppen' => [
                'id' => 59,
                'npc_name' => 'De Meestergraver',
                'npc_avatar' => 'assets/characters/oohpa_arjan.jpg',
                'npc_role' => 'Transportsluis Meesterbrein',
                'dilemma' => 'Een geldtransporteur brengt in beslag genomen goudstaven naar de beveiligde kluis. Eén goudstaaf van 12,5 kg is puur 24-karaats. Hoeveel weegt een kist met 4 van deze staven?',
                'correct_choice' => 'B',
                'choices' => [
                    'A' => '40 kg',
                    'B' => '50 kg (4 x 12,5 kg)',
                    'C' => '60 kg',
                ],
                'feedback_wrong' => 'De Meestergraver schudt zijn hoofd: "Fout berekend! Je steekwagentje zakte door de wielen wegens overbelasting!"',
            ],

            // 60. Hoofdinspectie Justitie Omkopen
            'hoofdinspectie-justitie-omkopen' => [
                'id' => 60,
                'npc_name' => 'Dr. Van Driel',
                'npc_avatar' => 'assets/characters/elara.jpg',
                'npc_role' => 'Conferentiezaal Inspectie',
                'dilemma' => 'De Hoofdinspecteur van het Ministerie van Justitie is gevoelig voor vastgoedbeleggingen in Zwitserland. Wat bied je aan als "consultancyvergoeding"?',
                'correct_choice' => 'A',
                'choices' => [
                    'A' => 'Een aandelenpakket in een discreet Zwitsers chalet-fonds op naam van zijn familiestichting.',
                    'B' => 'Een plastic zak vol briefjes van €20 rechtstreeks in zijn binnenzak proppen.',
                    'C' => 'Een gratis strippenkaart voor het openbaar vervoer.',
                ],
                'feedback_wrong' => 'Dr. Van Driel zucht: "Een plastic zak met twintigjes?! Hij riep meteen de Rijksrecherche!"',
            ],

            // 61. Hoogspannings Trafo Saboteren
            'hoogspannings-trafo-saboteren' => [
                'id' => 61,
                'npc_name' => 'Fikse Frank',
                'npc_avatar' => 'assets/characters/avatar_male_5.jpg',
                'npc_role' => 'Trafohuis Noordoost',
                'dilemma' => 'De 10 kV transformator wordt gekoeld met minerale olie. Hoe breng je de trafo gecontroleerd in storing zonder explosie?',
                'correct_choice' => 'B',
                'choices' => [
                    'A' => 'Met een tuinslang water in het hoogspanningscompartiment spuiten.',
                    'B' => 'Het Buchholz-relais handmatig triggeren zodat de beveiligingsschakelaar de voeding strak afkapt.',
                    'C' => 'Een ijzeren staaf tussen de drie fasen gooien.',
                ],
                'feedback_wrong' => 'Frank schrikt: "Water op 10.000 Volt?! De steekvlam was in heel de stad te zien!"',
            ],

            // 62. Ondergrondse Waterleiding Kraan Manipuleren
            'waterleiding-kraan-manipuleren' => [
                'id' => 62,
                'npc_name' => 'De Meestergraver',
                'npc_avatar' => 'assets/characters/oohpa_arjan.jpg',
                'npc_role' => 'Kruipruimte Blok C',
                'dilemma' => 'De hoofdafsluiter van het bluswater staat onder 8 bar druk. Welke kant moet de bronzen spindel op gedraaid worden om te sluiten?',
                'correct_choice' => 'A',
                'choices' => [
                    'A' => 'Met de klok mee (rechtsom) tot de aanslag.',
                    'B' => 'Tegen de klok in (linksom) met een breekijzer forceren.',
                    'C' => 'Hard met een voorhamer op de kraanbek slaan.',
                ],
                'feedback_wrong' => 'De Meestergraver zwemt bijna: "Je draaide hem juist helemaal open! De hele kelder staat blank!"',
            ],

            // 63. Directeurskluis met C4 Forceren
            'directeurskluis-c4-forceren' => [
                'id' => 63,
                'npc_name' => 'Barry Biceps',
                'npc_avatar' => 'assets/characters/barry.jpg',
                'npc_role' => 'Paniekkamer Specialist',
                'dilemma' => 'De gepantserde kluisdeur heeft 6 massieve sluitpennen aan de scharnierzijde. Waar plaats je de 250 gram plastische springstof?',
                'correct_choice' => 'C',
                'choices' => [
                    'A' => 'Precies in het midden van de deur op het logo.',
                    'B' => 'Onder de kluis op het tapijt leggen.',
                    'C' => 'Als holle lading rechtstreeks op de bovenste en onderste scharnierpennen met een vertragingsontsteker.',
                ],
                'feedback_wrong' => 'Barry kermt: "Op het tapijt leggen blies alleen een gat in de vloer naar de keuken!"',
            ],

            // 64. Satelliet Uplink Kapen op het Dak
            'satelliet-uplink-kapen' => [
                'id' => 64,
                'npc_name' => 'Hacker Harold',
                'npc_avatar' => 'assets/characters/avatar_male_6.jpg',
                'npc_role' => 'Dakterras Toren A',
                'dilemma' => 'De schotelantenne zendt uit naar Eutelsat op 13° Oost. Op welke elevatiehoek moet de schotel gericht staan in Nederland?',
                'correct_choice' => 'B',
                'choices' => [
                    'A' => '5 graden (bijna horizontaal over de hekken)',
                    'B' => 'Ongeveer 30 graden naar het zuiden gericht',
                    'C' => '90 graden loodrecht omhoog naar de wolken',
                ],
                'feedback_wrong' => 'Harold kijkt naar het scherm: "Loodrecht omhoog?! Je vangt alleen maar regenwater op in de schotel!"',
            ],

            // 65. Meesterontsnapping Rioolcollector
            'meesterontsnapping-rioolcollector' => [
                'id' => 65,
                'npc_name' => 'De Meestergraver',
                'npc_avatar' => 'assets/characters/oohpa_arjan.jpg',
                'npc_role' => 'Centrale Rioolader',
                'dilemma' => 'Het rioolwater stroomt met 2 meter per seconde richting de zuiveringsinstallatie buiten de muren. Er hangt een zwaar stalen rooster met hangslot. Wat gebruik je?',
                'correct_choice' => 'A',
                'choices' => [
                    'A' => 'Een hydraulische spreider aangedreven door een handpomp om de spijlen geruisloos uit elkaar te buigen.',
                    'B' => 'Met je tanden proberen de spijlen door te knagen.',
                    'C' => 'Harde knallers tegen het rooster gooien.',
                ],
                'feedback_wrong' => 'De Meestergraver grijpt zijn neus: "De knallers ontbrandden het methaangas! Steekvlam en direct alarm!"',
            ],

            // 66. Ministerie Schaduwdossiers Kraken
            'ministerie-schaduwdossiers' => [
                'id' => 66,
                'npc_name' => 'Samantha Slotjes',
                'npc_avatar' => 'assets/characters/samantha.jpg',
                'npc_role' => 'Ondergronds Rijksarchief',
                'dilemma' => 'In de geheime archiefkluis liggen de zwarte dossiers van alle hoge justitieambtenaren. Hoe transporteer je 500 gevoelige documenten naar buiten?',
                'correct_choice' => 'C',
                'choices' => [
                    'A' => 'Alle 500 mappen in je broekzakken proppen.',
                    'B' => 'Eén voor één via de postzegelbus versturen met je eigen afzender erop.',
                    'C' => 'In 5 minuten alle documenten scannen met een snelle dubbelzijdige handheld scanner naar een versleutelde micro-SD kaart.',
                ],
                'feedback_wrong' => 'Samantha slaat haar armen over elkaar: "500 mappen in je zakken?! Je paste niet eens meer door de deur!"',
            ],

            // 67. Kliniek Beveiligingschef Onttronen
            'beveiligingschef-onttronen' => [
                'id' => 67,
                'npc_name' => 'Barry Biceps',
                'npc_avatar' => 'assets/characters/barry.jpg',
                'npc_role' => 'Gymnasium Arena Kampioen',
                'dilemma' => 'De gevreesde Chef Beveiliging daagt je uit voor een man-tegen-man duel in de afgesloten sportzaal. Hij zwaait wild met zijn wapenstok. Wat is de winnende tactiek?',
                'correct_choice' => 'B',
                'choices' => [
                    'A' => 'Recht op zijn wapenstok af rennen met je ogen dicht.',
                    'B' => 'Zijn wilde eerste haal ontwijken door zijwaarts te slippen, zijn arm klemmen in een kimura-armklem en hem direct ontwapenen op de vloer!',
                    'C' => 'Gaan huilen en vragen of hij een kopje muntthee wil.',
                ],
                'feedback_wrong' => 'Barry roept: "Ogen dicht?! Hij raakte je vol op je schouder! Neer!"',
            ],

            // 68. Diplomatieke Zeecontainer Onderscheppen
            'diplomatieke-zeecontainer' => [
                'id' => 68,
                'npc_name' => 'Rooie Ronald',
                'npc_avatar' => 'assets/characters/avatar_male_5.jpg',
                'npc_role' => 'Haven Overslagkade',
                'dilemma' => 'De container heeft een verzegeld douaneloodje nummer NL-99482. Hoe open je de container zonder dat de douane ziet dat het zegel verbroken is?',
                'correct_choice' => 'A',
                'choices' => [
                    'A' => 'De scharnierpennen van de achterdeur aan de onderkant uittikken met een vloeibaar stikstof koudespray, zodat het deurslot intact blijft.',
                    'B' => 'Het zegel met een betonschaar doorknippen en met plakband weer aan elkaar plakken.',
                    'C' => 'Met een slijptol een groot gat in de zijkant zagen.',
                ],
                'feedback_wrong' => 'Ronald slaat zijn handen voor haar ogen: "Plakband op een douaneloodje?! De douanehonden stonden direct bij de deur!"',
            ],

            // 69. Forensische Staatsgreep: De Zwarte Poort
            'forensische-staatsgreep-zwarte-poort' => [
                'id' => 69,
                'npc_name' => 'Notaris Nico',
                'npc_avatar' => 'assets/characters/avatar_male_1.jpg',
                'npc_role' => 'Hoofdingang Poortgebouw Commandant',
                'dilemma' => 'We nemen de hoofdbewaking over met een compleet vervalst koninklijk inspectiebesluit. Welk officieel herkenningsteken overtuigt de poortwachten definitief?',
                'correct_choice' => 'C',
                'choices' => [
                    'A' => 'Een feloranje vlaggetje uit je raam zwaaien.',
                    'B' => 'Keihard roepen: "Ik ben de Koning, doe die poort open!"',
                    'C' => 'Een officieel verzegeld Koninklijk Besluit met rijkslakstempel tonen en bevelen dat het protocol Code Oranje onmiddellijk in werking treedt.',
                ],
                'feedback_wrong' => 'Nico kreunt: "Roepen dat je de Koning bent?! Ze hebben direct een dwangbuis klaargelegd!"',
            ],

            // 70. Keizer van de TBS Onderwereld: De Eeuwige Kroon
            'keizer-tbs-onderwereld-eeuwige-kroon' => [
                'id' => 70,
                'npc_name' => 'Barry Biceps',
                'npc_avatar' => 'assets/characters/barry.jpg',
                'npc_role' => 'Kraaiennest Opperbevelhebber',
                'dilemma' => 'Bovenop de hoogste wachttoren heb je de absolute controle over de TBS Kliniek. Alle bewakers, directie en bendes buigen voor je heerschappij. Wat is je eerste officiële decreet als Keizer van de Kliniek?',
                'correct_choice' => 'B',
                'choices' => [
                    'A' => 'Iedereen verplicht 100 uur per dag laten stofzuigen.',
                    'B' => 'Gratis frikandellenbroodjes voor iedereen, direct bankgeld voor alle trouwe volgers en totale immuniteit voor de onderwereld!',
                    'C' => 'Vrijwillig jezelf weer opsluiten in de kelder.',
                ],
                'feedback_wrong' => 'Barry lacht hard: "Jezelf weer opsluiten?! Dat is geen keizersgedrag! De bende zette je direct af!"',
            ],
        ];
    }

    /**
     * Get scenario by mission slug or ID.
     */
    public static function get(Mission|int|string|null $mission): ?array
    {
        if (! $mission) {
            return null;
        }

        $scenarios = self::scenarios();

        if ($mission instanceof Mission) {
            if (isset($scenarios[$mission->slug])) {
                return $scenarios[$mission->slug];
            }

            foreach ($scenarios as $item) {
                if (($item['id'] ?? null) === $mission->id) {
                    return $item;
                }
            }

            return null;
        }

        if (is_string($mission) && isset($scenarios[$mission])) {
            return $scenarios[$mission];
        }

        if (is_numeric($mission)) {
            $intId = (int) $mission;
            foreach ($scenarios as $item) {
                if (($item['id'] ?? null) === $intId) {
                    return $item;
                }
            }
        }

        return null;
    }

    /**
     * Get all scenarios indexed by mission ID.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function allById(): array
    {
        $result = [];
        foreach (self::scenarios() as $scenario) {
            if (isset($scenario['id'])) {
                $result[(int) $scenario['id']] = $scenario;
            }
        }

        return $result;
    }
}
