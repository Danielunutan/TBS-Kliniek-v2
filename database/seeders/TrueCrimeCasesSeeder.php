<?php

namespace Database\Seeders;

use App\Models\TrueCrimeCase;
use Illuminate\Database\Seeder;

class TrueCrimeCasesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (TrueCrimeCase::count() >= 500) {
            return;
        }

        $categories = [
            'Moord & Doodslag',
            'Vergiftiging & Medicatie',
            'Sabotage & Brandstichting',
            'Smokkelcomplot & Afpersing',
            'Mysterieuze Verdwijning',
            'Medische Fraude & Doofpot',
        ];

        $difficulties = ['Beginner', 'Gemiddeld', 'Gevorderd', 'Forensisch Expert'];

        $themes = [
            ['title' => 'De Giftige Medicijnbeker in Paviljoen %s', 'cat' => 'Vergiftiging & Medicatie', 'victim' => 'Zaalwacht %s', 'weapon' => 'een overdosis kaliumcyanide vermengd in de koffiemok'],
            ['title' => 'Het Dodelijke Schaarincident in het Textielatelier', 'cat' => 'Moord & Doodslag', 'victim' => 'Medepatiënt %s', 'weapon' => 'een industriële stofschaar met afgevijld serienummer'],
            ['title' => 'De Nachtelijke Brandstichting in de Wasserij', 'cat' => 'Sabotage & Brandstichting', 'victim' => 'Hoofdbeveiliger %s', 'weapon' => 'een geïmproviseerde ontstekingslont met wasbenzine'],
            ['title' => 'De Val uit het Trappenhuis van Vleugel %s', 'cat' => 'Moord & Doodslag', 'victim' => 'Informant %s', 'weapon' => 'een brute duw over de balustrade na een felle worsteling'],
            ['title' => 'Het Vermiste Forensisch Dossier van Dr. %s', 'cat' => 'Medische Fraude & Doofpot', 'victim' => 'Psychiater Dr. %s', 'weapon' => 'een zware marmeren presse-papier op het achterhoofd'],
            ['title' => 'De Gesaboteerde Remleiding van het Transportbusje', 'cat' => 'Sabotage & Brandstichting', 'victim' => 'Chauffeur %s', 'weapon' => 'een doorgeknipte hydraulische remleiding met een betonschaar'],
            ['title' => 'De Gewurgde Smokkelaar in de Ketelruimte', 'cat' => 'Smokkelcomplot & Afpersing', 'victim' => 'Smokkelaar %s', 'weapon' => 'een stalen gitaarsnaar met houten handvatten'],
            ['title' => 'De Vergiftigde Lunch in de Personeelskantine', 'cat' => 'Vergiftiging & Medicatie', 'victim' => 'Hoofdbehandelaar %s', 'weapon' => 'geconcentreerd rattenvergif door de erwtensoep'],
            ['title' => 'Het Bloedspoor achter de Krachthonk-Bank', 'cat' => 'Moord & Doodslag', 'victim' => 'Patiënt %s', 'weapon' => 'een massieve gietijzeren halterschijf van 15 kg'],
            ['title' => 'De Sabotage aan de Noodstroomgenerator', 'cat' => 'Sabotage & Brandstichting', 'victim' => 'Onderhoudsmonteur %s', 'weapon' => 'doorgesneden hoogspanningskabels tijdens stroomuitval'],
            ['title' => 'De Spoorloze Verdwijning uit de Isoleercel', 'cat' => 'Mysterieuze Verdwijning', 'victim' => 'Gevaarlijke patiënt %s', 'weapon' => 'een duplosleutel en een verdovend chloroformgaasje'],
            ['title' => 'Het Dodelijke Infuus op Afdeling %s', 'cat' => 'Vergiftiging & Medicatie', 'victim' => 'Belangrijke getuige %s', 'weapon' => 'een dodelijke luchtembolie via het infuusslangetje'],
            ['title' => 'Het Lijk in de Compostbak van de Moestuin', 'cat' => 'Moord & Doodslag', 'victim' => 'Tuinman %s', 'weapon' => 'een geslepen tuinschep diep in de borstkas'],
            ['title' => 'De Neergeslagen Nachtwacht bij de Buitendeur', 'cat' => 'Moord & Doodslag', 'victim' => 'Nachtwaker %s', 'weapon' => 'een loden pijp verborgen in een gereedschapskist'],
            ['title' => 'Het Gecodeerde Smokkelcomplot in de Bibliotheek', 'cat' => 'Smokkelcomplot & Afpersing', 'victim' => 'Bibliothecaris %s', 'weapon' => 'verstikking met plastic verpakkingsfolie'],
        ];

        $firstNames = ['Wesley', 'Marco', 'Dennis', 'Gerard', 'Patrick', 'Bennie', 'Michel', 'Raymond', 'Danny', 'Stefan', 'Karel', 'Remco', 'Frits', 'Sander', 'Jeroen', 'Koen', 'Ruud', 'Ronald', 'Henk', 'Erwin', 'Brenda', 'Monique', 'Natasja', 'Chantal', 'Sandra', 'Patricia', 'Wendy', 'Yvonne', 'Bianca', 'Astrid', 'Dave', 'Jeffrey', 'Nico', 'Sjors', 'Peter'];
        $lastNames = ['van Dijk', 'Bakker', 'de Jong', 'Visser', 'Smit', 'Bosch', 'de Wit', 'Jansen', 'van Dam', 'Kramer', 'de Ruiter', 'Vos', 'van der Meer', 'Postma', 'Brouwer', 'Willems', 'Vermeulen', 'Scholten', 'Hoekstra', 'Dekker', 'van Leeuwen', 'Timmermans', 'Kuipers', 'Groen'];

        $roles = [
            'Medepatiënt (Vleugel A)',
            'Zaalwacht & Beveiliger',
            'Keukenmedewerker',
            'Afdelingsverpleegkundige',
            'Onderhoudsmonteur',
            'Schoonmaker',
            'Kantinebaas',
            'Bibliotheekassistent',
            'Klusjesman Werkplaats',
            'Sportbegeleider Krachthonk',
        ];

        $motives = [
            'Had een torenhoge gokschuld van tienduizenden euro\'s bij het slachtoffer.',
            'Werd door het slachtoffer afgeperst met compromitterende smokkelbrieven.',
            'Wilde koste wat het kost voorkomen dat het slachtoffer ging getuigen bij de directie.',
            'Had ruzie gekregen over de verdeling van binnengesmokkelde contrabande.',
            'Werd gepasseerd voor een vervroegde verlofregeling en gaf het slachtoffer de schuld.',
            'Ontdekte dat het slachtoffer stiekem geld verduisterde uit de afdelingskas.',
            'Was bang dat het slachtoffer een geheim uitbraakplan zou verklappen aan de zaalwachten.',
            'Koesterde een diepe wraak voor een incident tijdens de luchtplaats-wandeling.',
        ];

        $locations = ['Paviljoen A', 'Paviljoen B', 'Paviljoen C', 'Vleugel 3', 'Vleugel 4', 'Afdeling Gesloten', 'Sectie Noord', 'Sectie Zuid'];

        $cases = [];

        for ($i = 1; $i <= 500; $i++) {
            $theme = $themes[($i - 1) % count($themes)];
            $loc = $locations[($i * 3) % count($locations)];
            $victimName = $firstNames[($i * 5) % count($firstNames)].' '.$lastNames[($i * 7) % count($lastNames)];

            $title = sprintf(str_replace('%s', '%s', $theme['title']), ($theme['cat'] === 'Vergiftiging & Medicatie' || str_contains($theme['title'], 'Vleugel') ? $loc : $victimName));
            $title = 'Zaak #'.str_pad($i, 3, '0', STR_PAD_LEFT).': '.$title;

            $diff = $difficulties[($i - 1) % count($difficulties)];

            // Select 4 suspects
            $suspects = [];
            $usedNames = [$victimName];
            $culpritIdx = ($i + 2) % 4;

            // Generate distinctive clue details
            $culpritShoeSize = 41 + (($i + 1) % 5); // 41 to 45
            $culpritBadgeId = 'TK-'.(100 + (($i * 13) % 899));
            $chemicalTrace = ['cyanideresten', 'wasbenzine-sporen', 'industrieel smeervet', 'sporen van rattenkruit', 'chloroform-residu'][($i % 5)];
            $culpritTime = sprintf('%02d:%02d', 21 + (($i % 3)), 10 + (($i * 7) % 48));

            $attempt = 0;
            for ($s = 0; $s < 4; $s++) {
                do {
                    $fnIdx = (($i * 11) + ($s * 7) + $attempt) % count($firstNames);
                    $lnIdx = (($i * 13) + ($s * 9) + $attempt) % count($lastNames);
                    $sName = $firstNames[$fnIdx].' '.$lastNames[$lnIdx];
                    $attempt++;
                } while (in_array($sName, $usedNames) && $attempt < 100);
                $usedNames[] = $sName;

                $role = $roles[(($i * 3) + $s) % count($roles)];
                $motive = $motives[(($i * 2) + $s) % count($motives)];

                $isCulprit = ($s === $culpritIdx);

                if ($isCulprit) {
                    $alibi = "Beweert dat hij rond {$culpritTime} in zijn eigen kamer lag te slapen, maar niemand heeft dit gezien. Draagt schoenmaat {$culpritShoeSize} en bezit sleutelpas {$culpritBadgeId}.";
                    $statement = 'Ik zweer het je, Caroline! Ik lag gewoon te pitten. Ik heb niks met die hele toestand te maken!';
                } else {
                    $safeTime = sprintf('%02d:%02d', 20 + (($s + 1) % 3), 15 + (($s * 11) % 40));
                    $innocentShoe = ($culpritShoeSize == 43) ? 41 : 44;
                    $alibi = [
                        "Zat ten tijde van het incident om {$culpritTime} met zaalwacht Peter te kaarten in de recreatieruimte (bevestigd door logboek).",
                        "Werd om {$culpritTime} gefilmd bij de medicijnbalie terwijl zuster Brenda pillen uitdeelde (waterdicht alibi).",
                        'Lag onder toezicht in de ziekenboeg aan een hartmonitor tussen 21:00 en 23:30 (volledig geregistreerd).',
                    ][$s % 3];
                    $statement = 'Je kunt mijn alibi gewoon navragen. Ik was daar op dat moment helemaal niet in de buurt.';
                }

                $suspects[] = [
                    'name' => $sName,
                    'role' => $role,
                    'motive' => $motive,
                    'alibi' => $alibi,
                    'statement' => $statement,
                    'is_culprit' => $isCulprit,
                ];
            }

            // Clues
            $clues = [
                [
                    'type' => 'Plaats Delict',
                    'title' => 'Sporenonderzoek Plaats Delict',
                    'detail' => "Op de vloer van het delict zijn verse moddersporen veiliggesteld van een zware werklaars, maat {$culpritShoeSize}. Daarnaast zijn sporen van {$chemicalTrace} aangetroffen.",
                    'icon' => 'fa-shoe-prints',
                ],
                [
                    'type' => 'Cameratoezicht',
                    'title' => 'Elektronische Toegangslog & CCTV',
                    'detail' => "De digitale deursensor registreerde om exact {$culpritTime} een ongeautoriseerde doorgang met personeelspas {$culpritBadgeId}. De schim op camerabeeld droeg donkere kleding.",
                    'icon' => 'fa-video',
                ],
                [
                    'type' => 'Forensisch Lab',
                    'title' => 'Toxicologisch & Materiaal Rapport',
                    'detail' => "Het slachtoffer is om het leven gebracht met {$theme['weapon']}. De vingerafdrukken op de handgreep vertonen een litteken op de rechterduim dat exact matcht met verdachte ".$suspects[$culpritIdx]['name'].'.',
                    'icon' => 'fa-flask-vial',
                ],
                [
                    'type' => 'Getuigenverhoor',
                    'title' => 'Verslag Caroline\'s Ondervraging',
                    'detail' => 'Bij inspectie van de kledingkast van '.$suspects[$culpritIdx]['name']." trof Caroline een doosje met {$chemicalTrace} aan en een bebloed kledingstuk weggemoffeld achter de plint.",
                    'icon' => 'fa-file-signature',
                ],
            ];

            $incidentReport = "Op de avond van het incident werd {$victimName} levenloos aangetroffen. Onderzoek wees uit dat er sprake was van opzet waarbij {$theme['weapon']} is gebruikt. Caroline van het Forensisch Dossierbeheer heeft de plaats delict direct afgezet en vier hoofdverdachten afgezonderd voor verhoor. Aan jou als kliniekspeurder de taak om de forensische puzzelstukjes in elkaar te passen en de dader te ontmaskeren!";

            $solution = 'De dader is onomstotelijk **'.$suspects[$culpritIdx]['name'].'**! '.
                "Zijn alibi hield geen stand: pas {$culpritBadgeId} werd op exact {$culpritTime} gebruikt om toegang te krijgen, ".
                "zijn schoenmaat ({$culpritShoeSize}) matchte met de aangetroffen voetafdruk, en Caroline vond de doorslaggevende sporen van {$chemicalTrace} in zijn verblijfsruimte. ".
                'De overige drie verdachten hadden stuk voor stuk een waterdicht en extern geverifieerd alibi.';

            $rewardCash = 30000 + ($i * 100); // 30k to 80k
            $rewardXp = 40 + ($i % 30);
            $rewardRespect = 15 + ($i % 15);

            $cases[] = [
                'case_number' => $i,
                'title' => $title,
                'category' => $theme['cat'],
                'difficulty' => $diff,
                'incident_report' => $incidentReport,
                'clues' => json_encode($clues),
                'suspects' => json_encode($suspects),
                'culprit_index' => $culpritIdx,
                'solution_explanation' => $solution,
                'reward_cash' => $rewardCash,
                'reward_xp' => $rewardXp,
                'reward_respect' => $rewardRespect,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            // Insert in chunks of 50 to optimize performance
            if (count($cases) >= 50) {
                TrueCrimeCase::insert($cases);
                $cases = [];
            }
        }

        if (! empty($cases)) {
            TrueCrimeCase::insert($cases);
        }
    }
}
