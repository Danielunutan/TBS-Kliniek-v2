<?php

namespace Database\Seeders;

use App\Models\Character;
use App\Models\CharacterItem;
use App\Models\CharacterMission;
use App\Models\GameNotification;
use App\Models\Gang;
use App\Models\GuestbookEntry;
use App\Models\Item;
use App\Models\Mission;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database matching the official UI design.
     */
    public function run(): void
    {
        // 1. Create Gangs
        $gangKantine = Gang::create([
            'name' => 'De Kantinebende',
            'tag' => 'KB',
            'description' => 'Wie de frituur beheerst, beheerst de kliniek. Geld, handel en de lekkerste snacks.',
            'specialty' => 'Geld | Handel | Invloed',
            'emblem' => 'fa-burger',
            'treasury' => 8450,
        ]);

        $gangPlofkippen = Gang::create([
            'name' => 'De Paarse Plofkippen',
            'tag' => 'PP',
            'description' => 'Geleid door Yordi de Regelaar. Altijd op zoek naar de beste deals en groepsmissies.',
            'specialty' => 'Groepsmissies & Invloed',
            'emblem' => 'fa-feather',
            'treasury' => 5200,
        ]);

        $gangHalters = Gang::create([
            'name' => 'De IJzeren Halters',
            'tag' => 'IJH',
            'description' => 'Geleid door Barry Biceps. Niemand durft voor te kruipen als de Halters trainen.',
            'specialty' => 'Kracht & Gevechten',
            'emblem' => 'fa-dumbbell',
            'treasury' => 6100,
        ]);

        $gangGerda = Gang::create([
            'name' => 'De Administratieve Nachtmerrie',
            'tag' => 'DAN',
            'description' => 'Directeur Gerda regeert met formulieren, stempels en onverbiddelijke bezuinigingen.',
            'specialty' => 'Intelligentie & Strategie',
            'emblem' => 'fa-file-signature',
            'treasury' => 24000,
        ]);

        $gangWasmachine = Gang::create([
            'name' => 'De Wasmachine Warriors',
            'tag' => 'WW',
            'description' => 'Ome Willem weet precies welke spullen en geheimen verdwijnen in trommel 4.',
            'specialty' => 'Verzamelen & Geheimen',
            'emblem' => 'fa-soap',
            'treasury' => 3800,
        ]);

        // 2. Primary Demo User: Henkie (Matches the exact mockup!)
        $henkieUser = User::create([
            'name' => 'Henkie de Frikandelkoning',
            'email' => 'demo@tbskliniek.nl',
            'password' => Hash::make('wachtwoord123'),
        ]);

        $henkieChar = Character::create([
            'user_id' => $henkieUser->id,
            'name' => 'Henkie',
            'title' => 'Kantinelegende',
            'avatar' => 'characters/henkie.jpg',
            'bio' => 'Henkie de Frikandelkoning. Altijd in voor een goed broodje en een stevige deal. De kantine van de kliniek is mijn speeltuin.',
            'status_message' => 'Frikandelbroodjes zijn geen snack, het is een levensstijl.',
            'level' => 12,
            'xp' => 320,
            'total_xp' => 4800,
            'cash' => 2450,
            'bank' => 15230,
            'energy' => 85,
            'max_energy' => 100,
            'health' => 95,
            'max_health' => 100,
            'respect' => 1250,
            'strength' => 35,
            'defense' => 28,
            'stamina' => 32,
            'intelligence' => 25,
            'charisma' => 20,
            'discipline' => 18,
            'missions_completed' => 34,
            'thefts_succeeded' => 12,
            'thefts_failed' => 3,
            'fights_won' => 15,
            'fights_lost' => 7,
            'prison_sentences' => 2,
            'prison_escapes' => 1,
            'gang_id' => $gangKantine->id,
            'gang_role' => 'leader',
        ]);
        $gangKantine->update(['leader_character_id' => $henkieChar->id]);

        // Secondary / Fellow Inmates from mockup
        $rianneUser = User::create(['name' => 'BakkerieRianne', 'email' => 'rianne@tbskliniek.nl', 'password' => Hash::make('secret')]);
        $rianneChar = Character::create([
            'user_id' => $rianneUser->id,
            'name' => 'BakkerieRianne',
            'title' => 'Broodjesspecialist',
            'avatar' => 'characters/avatar-default.png',
            'bio' => 'Houdt van vers bladerdeeg en scherpe dealtjes.',
            'level' => 10,
            'xp' => 120,
            'total_xp' => 3200,
            'cash' => 850,
            'bank' => 4500,
            'respect' => 340,
            'strength' => 22,
            'defense' => 20,
            'stamina' => 24,
            'intelligence' => 28,
            'charisma' => 25,
            'discipline' => 22,
            'gang_id' => $gangKantine->id,
        ]);

        $queenUser = User::create(['name' => 'FrikandelQueen', 'email' => 'queen@tbskliniek.nl', 'password' => Hash::make('secret')]);
        $queenChar = Character::create([
            'user_id' => $queenUser->id,
            'name' => 'FrikandelQueen',
            'title' => 'Snackkoningin',
            'avatar' => 'characters/avatar-default.png',
            'bio' => 'Altijd vooraan bij de warmhoudkast.',
            'level' => 9,
            'xp' => 210,
            'total_xp' => 2800,
            'cash' => 620,
            'bank' => 3800,
            'respect' => 290,
            'strength' => 18,
            'defense' => 19,
            'stamina' => 22,
            'intelligence' => 24,
            'charisma' => 30,
            'discipline' => 18,
            'gang_id' => $gangKantine->id,
        ]);

        $moederUser = User::create(['name' => 'MoederJellief', 'email' => 'jellief@tbskliniek.nl', 'password' => Hash::make('secret')]);
        $moederChar = Character::create([
            'user_id' => $moederUser->id,
            'name' => 'MoederJellief',
            'title' => 'Luchtplaats Veteraan',
            'avatar' => 'characters/avatar-default.png',
            'bio' => 'Gerda kan de pot op, niemand pakt mijn sigaretten af.',
            'level' => 14,
            'xp' => 540,
            'total_xp' => 6400,
            'cash' => 1400,
            'bank' => 8900,
            'respect' => 780,
            'strength' => 30,
            'defense' => 32,
            'stamina' => 35,
            'intelligence' => 22,
            'charisma' => 28,
            'discipline' => 25,
            'gang_id' => $gangPlofkippen->id,
        ]);

        $kevinUser = User::create(['name' => 'KoffieKevin', 'email' => 'kevin@tbskliniek.nl', 'password' => Hash::make('secret')]);
        $kevinChar = Character::create([
            'user_id' => $kevinUser->id,
            'name' => 'KoffieKevin',
            'title' => 'Automaatmeester',
            'avatar' => 'characters/avatar-default.png',
            'bio' => 'Kent alle kliniekroddels bij de koffiehoek.',
            'level' => 8,
            'xp' => 180,
            'total_xp' => 2200,
            'cash' => 480,
            'bank' => 2400,
            'respect' => 210,
            'strength' => 15,
            'defense' => 16,
            'stamina' => 20,
            'intelligence' => 29,
            'charisma' => 22,
            'discipline' => 20,
            'gang_id' => $gangWasmachine->id,
        ]);

        // 3. Missions (Matching exact titles and rewards in mockup)
        $missions = [
            [
                'category' => 'all',
                'storyline' => 'Kantineklusjes',
                'story_step' => 1,
                'title' => 'Koffie en Kaas',
                'slug' => 'koffie-en-kaas',
                'description' => 'Verdien 500 KC in de kantine door warme snacks te distribueren.',
                'giver_name' => 'Henkie de Frikandelkoning',
                'location_name' => 'De Kantine',
                'required_level' => 1,
                'energy_cost' => 8,
                'success_rate' => 90,
                'min_cash_reward' => 450,
                'max_cash_reward' => 450,
                'xp_reward' => 120,
                'respect_reward' => 5,
                'success_message' => 'Missie Koffie en Kaas voltooid! + 450 KC + 120 XP',
                'sort_order' => 1,
            ],
            [
                'category' => 'all',
                'storyline' => 'Eerste Diefstal',
                'story_step' => 1,
                'title' => 'De Eerste Diefstal',
                'slug' => 'de-eerste-diefstal',
                'description' => 'Steel 250 KC van een andere speler of onoplettende bewoner.',
                'giver_name' => 'Ricardo Ritselaar',
                'location_name' => 'Rookruimte Oostvleugel',
                'required_level' => 1,
                'energy_cost' => 10,
                'success_rate' => 82,
                'min_cash_reward' => 300,
                'max_cash_reward' => 300,
                'xp_reward' => 90,
                'respect_reward' => 4,
                'success_message' => 'De buit is binnen! + 300 KC + 90 XP',
                'sort_order' => 2,
            ],
            [
                'category' => 'all',
                'storyline' => 'Training',
                'story_step' => 1,
                'title' => 'Dagelijkse Training',
                'slug' => 'dagelijkse-training',
                'description' => 'Train 2 keer vandaag in de sportzaal bij Barry Biceps.',
                'giver_name' => 'Barry Biceps',
                'location_name' => 'De Sportzaal',
                'required_level' => 1,
                'energy_cost' => 10,
                'success_rate' => 95,
                'min_cash_reward' => 50,
                'max_cash_reward' => 50,
                'xp_reward' => 150,
                'respect_reward' => 6,
                'success_message' => 'Goede workout! + 50 KC + 150 XP',
                'sort_order' => 3,
            ],
            [
                'category' => 'all',
                'storyline' => 'Administratie Mysterie',
                'story_step' => 2,
                'title' => 'Het Verdwenen Budget',
                'slug' => 'het-verdwenen-budget',
                'description' => 'Zoek het verdwenen kliniekbudget in het kantoor van Gerda.',
                'giver_name' => 'Directeur Gerda Graaigraag',
                'location_name' => 'Administratievleugel',
                'required_level' => 15,
                'energy_cost' => 20,
                'success_rate' => 65,
                'min_cash_reward' => 750,
                'max_cash_reward' => 750,
                'xp_reward' => 200,
                'respect_reward' => 10,
                'success_message' => 'Je vond een geheime kluis! + 750 KC + 200 XP',
                'sort_order' => 4,
            ],
            [
                'category' => 'all',
                'storyline' => 'Het Mysterie van Paviljoen 13',
                'story_step' => 1,
                'title' => 'Paviljoen 13',
                'slug' => 'paviljoen-13',
                'description' => 'Ontdek het geheim van Paviljoen 13 en vind de verdwenen documenten.',
                'giver_name' => 'Samantha Slotjes',
                'location_name' => 'Corridor Paviljoen 13',
                'required_level' => 20,
                'energy_cost' => 25,
                'success_rate' => 55,
                'min_cash_reward' => 1000,
                'max_cash_reward' => 1000,
                'xp_reward' => 300,
                'respect_reward' => 15,
                'success_message' => 'Paviljoen 13 ontgrendeld! + 1.000 KC + 300 XP',
                'sort_order' => 5,
            ],
            [
                'category' => 'medicatie',
                'storyline' => 'De Farmacie Maffia',
                'story_step' => 1,
                'title' => 'De Pillenronde Saboteren',
                'slug' => 'pillenronde-saboteren',
                'description' => 'Leid zuster Ingrid af en graai de oxazepam uit het medicijnkarretje.',
                'giver_name' => 'Zuster Ingrid',
                'location_name' => 'Apotheek Paviljoen B',
                'required_level' => 2,
                'energy_cost' => 12,
                'success_rate' => 85,
                'min_cash_reward' => 350,
                'max_cash_reward' => 500,
                'xp_reward' => 140,
                'respect_reward' => 7,
                'success_message' => 'Slaappillen buitgemaakt! + 450 KC + 140 XP',
                'sort_order' => 6,
            ],
            [
                'category' => 'smokkel',
                'storyline' => 'Wasserette Netwerk',
                'story_step' => 1,
                'title' => 'Pekkie Peuken Smokkelen',
                'slug' => 'pekkie-peuken-smokkelen',
                'description' => 'Verstop vijf pakjes zware shag tussen het vuile beddengoed.',
                'giver_name' => 'Rooie Ronald',
                'location_name' => 'De Wasserette',
                'required_level' => 3,
                'energy_cost' => 14,
                'success_rate' => 80,
                'min_cash_reward' => 480,
                'max_cash_reward' => 620,
                'xp_reward' => 170,
                'respect_reward' => 8,
                'success_message' => 'De peuken zijn geleverd bij cel 14! + 550 KC + 170 XP',
                'sort_order' => 7,
            ],
            [
                'category' => 'administratie',
                'storyline' => 'Weekendverlof Sabotage',
                'story_step' => 1,
                'title' => 'Verlofformulier Vervalsen',
                'slug' => 'verlofformulier-vervalsen',
                'description' => 'Sluip het archief in en zet de officiële stempel van de psychiater op je dossier.',
                'giver_name' => 'Notaris Nico',
                'location_name' => 'Archiefkelder',
                'required_level' => 5,
                'energy_cost' => 16,
                'success_rate' => 75,
                'min_cash_reward' => 600,
                'max_cash_reward' => 800,
                'xp_reward' => 220,
                'respect_reward' => 10,
                'success_message' => 'Stempel geplaatst zonder dat iemand het merkte! + 700 KC + 220 XP',
                'sort_order' => 8,
            ],
            [
                'category' => 'gevecht',
                'storyline' => 'Territoriumstrijd',
                'story_step' => 1,
                'title' => 'Mopstok Gevecht Luchtplaats',
                'slug' => 'mopstok-gevecht',
                'description' => 'Laat aan de nieuwkomers zien wie de betonnen bankjes beheert tijdens het uurtje luchten.',
                'giver_name' => 'Barry Biceps',
                'location_name' => 'De Luchtplaats',
                'required_level' => 7,
                'energy_cost' => 18,
                'success_rate' => 72,
                'min_cash_reward' => 750,
                'max_cash_reward' => 950,
                'xp_reward' => 260,
                'respect_reward' => 14,
                'success_message' => 'Respect afgedwongen op de luchtplaats! + 850 KC + 260 XP',
                'sort_order' => 9,
            ],
            [
                'category' => 'administratie',
                'storyline' => 'Omkoping & Invloed',
                'story_step' => 1,
                'title' => 'Hoofdbehandelaar Omkopen',
                'slug' => 'hoofdbehandelaar-omkopen',
                'description' => 'Schuif een dikke enveloppe in de aktetas van Dr. Van Driel voor een gunstig advies.',
                'giver_name' => 'Dr. Van Driel',
                'location_name' => 'Spreekkamer 4',
                'required_level' => 10,
                'energy_cost' => 20,
                'success_rate' => 68,
                'min_cash_reward' => 1100,
                'max_cash_reward' => 1350,
                'xp_reward' => 320,
                'respect_reward' => 16,
                'success_message' => 'Dr. Van Driel knikt instemmend. + 1.200 KC + 320 XP',
                'sort_order' => 10,
            ],
            [
                'category' => 'casino',
                'storyline' => 'Nachtelijke Spelen',
                'story_step' => 1,
                'title' => 'Nachtpoker Ketelhuis',
                'slug' => 'nachtpoker-ketelhuis',
                'description' => 'Organiseer een illegale pokertafel achter de stoomketels om middernacht.',
                'giver_name' => 'Slinkse Sam',
                'location_name' => 'Het Ketelhuis',
                'required_level' => 12,
                'energy_cost' => 22,
                'success_rate' => 64,
                'min_cash_reward' => 1400,
                'max_cash_reward' => 1700,
                'xp_reward' => 380,
                'respect_reward' => 18,
                'success_message' => 'De pot binnengehaald met een full house! + 1.500 KC + 380 XP',
                'sort_order' => 11,
            ],
            [
                'category' => 'beveiliging',
                'storyline' => 'Blind Spots',
                'story_step' => 1,
                'title' => 'Camera’s Afplakken Blok B',
                'slug' => 'cameras-afplakken',
                'description' => 'Plak met kauwgom en folie de bewakingscamera’s af zodat er een blinde vlek ontstaat.',
                'giver_name' => 'Hacker Harold',
                'location_name' => 'Gang Blok B',
                'required_level' => 14,
                'energy_cost' => 24,
                'success_rate' => 60,
                'min_cash_reward' => 1600,
                'max_cash_reward' => 1900,
                'xp_reward' => 420,
                'respect_reward' => 20,
                'success_message' => 'Camera’s staan op zwart beeld! + 1.750 KC + 420 XP',
                'sort_order' => 12,
            ],
            [
                'category' => 'kantine',
                'storyline' => 'Kantinelegende',
                'story_step' => 2,
                'title' => 'De Gouden Frituurmand',
                'slug' => 'gouden-frituurmand',
                'description' => 'Steel de legendarische roestvrijstalen frietmand uit de privékeuken van de chefkok.',
                'giver_name' => 'Henkie de Frikandelkoning',
                'location_name' => 'Grote Instellingskeuken',
                'required_level' => 18,
                'energy_cost' => 26,
                'success_rate' => 58,
                'min_cash_reward' => 2000,
                'max_cash_reward' => 2400,
                'xp_reward' => 500,
                'respect_reward' => 25,
                'success_message' => 'De frituurmand is in jouw handen! Henkie buigt voor je. + 2.200 KC + 500 XP',
                'sort_order' => 13,
            ],
            [
                'category' => 'uitbraak',
                'storyline' => 'De Meesterplan',
                'story_step' => 1,
                'title' => 'Sleutelbos Hoofdbewaker Imprint',
                'slug' => 'sleutelbos-imprint',
                'description' => 'Druk de hoofdsleutel van bewaker Van Loon in een stuk zachte hotelzeep.',
                'giver_name' => 'Sleutelman Sjaak',
                'location_name' => 'Controlepost Noord',
                'required_level' => 22,
                'energy_cost' => 28,
                'success_rate' => 50,
                'min_cash_reward' => 2500,
                'max_cash_reward' => 3000,
                'xp_reward' => 650,
                'respect_reward' => 30,
                'success_message' => 'Perfecte afdruk gemaakt! De kopie is in de maak. + 2.800 KC + 650 XP',
                'sort_order' => 14,
            ],
            [
                'category' => 'uitbraak',
                'storyline' => 'De Grote Vrijheid',
                'story_step' => 2,
                'title' => 'De Uitbraaktunnel Graven',
                'slug' => 'uitbraaktunnel-graven',
                'description' => 'Graaf onder de buitenmuur door met kantinelepels tijdens het recreatie-uur.',
                'giver_name' => 'Onbekende Infiltrant',
                'location_name' => 'De Binnentuin',
                'required_level' => 25,
                'energy_cost' => 30,
                'success_rate' => 45,
                'min_cash_reward' => 4000,
                'max_cash_reward' => 5000,
                'xp_reward' => 1000,
                'respect_reward' => 50,
                'success_message' => 'De tunnel reikt tot voorbij het hek! Legendarische status bereikt! + 4.500 KC + 1.000 XP',
                'sort_order' => 15,
            ],
        ];

        foreach ($missions as $m) {
            $created = Mission::create($m);
            if ($created->id <= 2) {
                CharacterMission::create([
                    'character_id' => $henkieChar->id,
                    'mission_id' => $created->id,
                    'completed_count' => 1,
                    'last_attempted_at' => now()->subHours(2),
                ]);
            }
        }

        // 4. Shop Items (Matching exact mockup winkel card)
        $items = [
            [
                'name' => 'Frikandelbroodje',
                'slug' => 'frikandelbroodje',
                'description' => 'Warm uit de frituur met curry. Geeft directe energie.',
                'category' => 'consumable',
                'rarity' => 'common',
                'price_kc' => 100,
                'energy_restore' => 25,
                'icon' => 'fa-hotdog',
            ],
            [
                'name' => 'Trainingshandschoenen',
                'slug' => 'trainingshandschoenen',
                'description' => 'Goede grip op zware gewichten in de gym.',
                'category' => 'equipment',
                'rarity' => 'common',
                'price_kc' => 250,
                'strength_bonus' => 5,
                'icon' => 'fa-mitten',
            ],
            [
                'name' => 'Sporttas',
                'slug' => 'sporttas',
                'description' => 'Ruime tas voor al je smokkelwaar en kleren.',
                'category' => 'equipment',
                'rarity' => 'uncommon',
                'price_kc' => 400,
                'stamina_bonus' => 10,
                'icon' => 'fa-bag-shopping',
            ],
            [
                'name' => 'Paarse Trainingsjas',
                'slug' => 'paarse-trainingsjas',
                'description' => 'Stijlvol paars retro trainingsjack. Iedereen herkent je.',
                'category' => 'equipment',
                'rarity' => 'rare',
                'price_kc' => 600,
                'charisma_bonus' => 5,
                'icon' => 'fa-vest',
            ],
            [
                'name' => 'Gouden Frikandel',
                'slug' => 'gouden-frikandel',
                'description' => 'Het legendarische statussymbool van de kantine.',
                'category' => 'collectible',
                'rarity' => 'legendary',
                'price_kc' => 1500,
                'health_restore' => 50,
                'icon' => 'fa-crown',
            ],
            [
                'name' => 'De Sleutel van Paviljoen 13',
                'slug' => 'sleutel-paviljoen-13',
                'description' => 'Speciaal item dat de deuren van de oude vleugel opent.',
                'category' => 'collectible',
                'rarity' => 'epic',
                'price_kc' => 5000,
                'intelligence_bonus' => 12,
                'icon' => 'fa-key',
            ],
            [
                'name' => 'Oude Sleutel',
                'slug' => 'oude-sleutel',
                'description' => 'Een roestige sleutel gevonden bij de wasserij.',
                'category' => 'collectible',
                'rarity' => 'uncommon',
                'price_kc' => 800,
                'intelligence_bonus' => 10,
                'icon' => 'fa-key-skeleton',
            ],
            [
                'name' => 'De Legendarische Halter',
                'slug' => 'legendarische-halter',
                'description' => 'Gietijzeren halter van 100 kg gesmeed door Barry.',
                'category' => 'equipment',
                'rarity' => 'legendary',
                'price_kc' => 10000,
                'strength_bonus' => 20,
                'icon' => 'fa-dumbbell',
            ],
        ];

        foreach ($items as $itemData) {
            $item = Item::create($itemData);
            if ($item->slug === 'frikandelbroodje') {
                CharacterItem::create([
                    'character_id' => $henkieChar->id,
                    'item_id' => $item->id,
                    'quantity' => 2,
                ]);
            }
        }

        // 5. Guestbook Entries (Matching mockup exactly!)
        GuestbookEntry::create([
            'profile_character_id' => $henkieChar->id,
            'author_character_id' => $rianneChar->id,
            'message' => 'Gefeliciteerd met level 25. Nu nog leren hoe je van Henkie wint.',
            'created_at' => now()->subHours(8),
        ]);

        GuestbookEntry::create([
            'profile_character_id' => $henkieChar->id,
            'author_character_id' => $queenChar->id,
            'message' => 'Je hebt mijn geld gejat. Ik zie je straks in de kantine.',
            'created_at' => now()->subHours(5),
        ]);

        GuestbookEntry::create([
            'profile_character_id' => $henkieChar->id,
            'author_character_id' => $moederChar->id,
            'message' => 'Respect voor die ontsnapping. Gerda heeft drie formulieren ingevuld uit woede.',
            'created_at' => now()->subHours(2),
        ]);

        GuestbookEntry::create([
            'profile_character_id' => $henkieChar->id,
            'author_character_id' => $kevinChar->id,
            'message' => 'Leuke bio. Heeft Gerda die voor je geschreven?',
            'created_at' => now()->subMinutes(45),
        ]);

        // 6. Notifications / Activities
        GameNotification::create([
            'character_id' => $henkieChar->id,
            'type' => 'mission',
            'icon' => 'fa-check',
            'title' => "Je hebt de missie 'Koffie en Kaas' voltooid!",
            'message' => '+ 450 KC  + 120 XP',
            'is_read' => true,
            'created_at' => now()->subHours(2),
        ]);

        GameNotification::create([
            'character_id' => $henkieChar->id,
            'type' => 'training',
            'icon' => 'fa-bolt',
            'title' => 'Je hebt 1 energie opgebruikt bij Training.',
            'message' => '+ 75 XP',
            'is_read' => false,
            'created_at' => now()->subHours(3),
        ]);
    }
}
