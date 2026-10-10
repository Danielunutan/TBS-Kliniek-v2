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
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Create Gangs
        $gangKantine = Gang::create([
            'name' => 'De Kantinebende',
            'tag' => 'KB',
            'description' => 'Geleid door Henkie de Frikandelkoning. Wie de frituur beheerst, beheerst de kliniek.',
            'specialty' => 'Geld & Handel',
            'emblem' => 'fa-utensils',
            'treasury' => 45000,
        ]);

        $gangPlofkippen = Gang::create([
            'name' => 'De Paarse Plofkippen',
            'tag' => 'PP',
            'description' => 'Geleid door Yordi de Regelaar. Altijd op zoek naar de beste deals en groepsmissies.',
            'specialty' => 'Groepsmissies & Invloed',
            'emblem' => 'fa-feather',
            'treasury' => 28000,
        ]);

        $gangHalters = Gang::create([
            'name' => 'De IJzeren Halters',
            'tag' => 'IJH',
            'description' => 'Geleid door Barry Biceps. Niemand durft voor te kruipen als de Halters trainen.',
            'specialty' => 'Kracht & Gevechten',
            'emblem' => 'fa-dumbbell',
            'treasury' => 31500,
        ]);

        $gangGerda = Gang::create([
            'name' => 'De Administratieve Nachtmerrie',
            'tag' => 'DAN',
            'description' => 'Directeur Gerda regeert met formulieren, stempels en onverbiddelijke bezuinigingen.',
            'specialty' => 'Intelligentie & Strategie',
            'emblem' => 'fa-file-signature',
            'treasury' => 95000,
        ]);

        $gangWasmachine = Gang::create([
            'name' => 'De Wasmachine Warriors',
            'tag' => 'WW',
            'description' => 'Ome Willem weet precies welke spullen en geheimen verdwijnen in trommel 4.',
            'specialty' => 'Verzamelen & Geheimen',
            'emblem' => 'fa-soap',
            'treasury' => 18200,
        ]);

        // 2. Create Users & Characters
        $demoUser = User::create([
            'name' => 'Kees Knakworst',
            'email' => 'demo@tbskliniek.nl',
            'password' => Hash::make('wachtwoord123'),
        ]);

        $demoCharacter = Character::create([
            'user_id' => $demoUser->id,
            'name' => 'KeesKnakworst',
            'title' => 'Kantinefluisteraar',
            'avatar' => 'characters/avatar-kees.png',
            'bio' => 'Al drie maanden op Paviljoen 7. Bekend van de beruchte frikandellenruilactie tijdens het ochtendappèl. Zoekt nog een betrouwbare gang.',
            'status_message' => 'Heeft iemand nog een extra koffiemuntje liggen?',
            'level' => 3,
            'xp' => 140,
            'total_xp' => 540,
            'cash' => 450,
            'bank' => 2200,
            'energy' => 85,
            'max_energy' => 100,
            'health' => 95,
            'max_health' => 100,
            'respect' => 32,
            'strength' => 16,
            'defense' => 14,
            'stamina' => 15,
            'intelligence' => 13,
            'charisma' => 18,
            'discipline' => 11,
            'missions_completed' => 12,
            'thefts_succeeded' => 6,
            'thefts_failed' => 2,
            'fights_won' => 4,
            'fights_lost' => 1,
            'prison_sentences' => 1,
            'prison_escapes' => 0,
            'gang_id' => $gangKantine->id,
            'gang_role' => 'member',
        ]);

        // Rival characters / NPCs
        $henkieUser = User::create(['name' => 'Henkie', 'email' => 'henkie@tbskliniek.nl', 'password' => Hash::make('secret')]);
        $henkieChar = Character::create([
            'user_id' => $henkieUser->id,
            'name' => 'Henkie de Frikandelkoning',
            'title' => 'Kantinebaas',
            'avatar' => 'characters/henkie.png',
            'bio' => 'Zonder mij draait deze hele kliniek niet. Frikandellen zijn de enige echte valuta.',
            'level' => 25,
            'xp' => 1200,
            'total_xp' => 45000,
            'cash' => 12500,
            'bank' => 98000,
            'respect' => 450,
            'strength' => 35,
            'defense' => 30,
            'stamina' => 32,
            'intelligence' => 28,
            'charisma' => 45,
            'discipline' => 20,
            'gang_id' => $gangKantine->id,
            'gang_role' => 'leader',
        ]);
        $gangKantine->update(['leader_character_id' => $henkieChar->id]);

        $gerdaUser = User::create(['name' => 'Gerda', 'email' => 'gerda@tbskliniek.nl', 'password' => Hash::make('secret')]);
        $gerdaChar = Character::create([
            'user_id' => $gerdaUser->id,
            'name' => 'Directeur Gerda Graaigraag',
            'title' => 'Kliniekdirecteur',
            'avatar' => 'characters/gerda.png',
            'bio' => 'Elk formulier moet in drievoud ingeleverd worden. Bezuinigingen zijn een zegen.',
            'level' => 40,
            'xp' => 3400,
            'total_xp' => 98000,
            'cash' => 25000,
            'bank' => 450000,
            'respect' => 780,
            'strength' => 20,
            'defense' => 45,
            'stamina' => 35,
            'intelligence' => 60,
            'charisma' => 50,
            'discipline' => 75,
            'gang_id' => $gangGerda->id,
            'gang_role' => 'leader',
        ]);
        $gangGerda->update(['leader_character_id' => $gerdaChar->id]);

        $barryUser = User::create(['name' => 'Barry', 'email' => 'barry@tbskliniek.nl', 'password' => Hash::make('secret')]);
        $barryChar = Character::create([
            'user_id' => $barryUser->id,
            'name' => 'Barry Biceps',
            'title' => 'Gymbaas',
            'avatar' => 'characters/barry.png',
            'bio' => 'Als het niet zwaar is, telt het niet. Eerst 100 push-ups, daarna pas praten.',
            'level' => 22,
            'xp' => 800,
            'total_xp' => 38000,
            'cash' => 3400,
            'bank' => 42000,
            'respect' => 390,
            'strength' => 58,
            'defense' => 40,
            'stamina' => 48,
            'intelligence' => 15,
            'charisma' => 22,
            'discipline' => 30,
            'gang_id' => $gangHalters->id,
            'gang_role' => 'leader',
        ]);
        $gangHalters->update(['leader_character_id' => $barryChar->id]);

        // 3. Missions
        $missions = [
            [
                'category' => 'beginners',
                'storyline' => 'Eerste Dagen in de Kliniek',
                'story_step' => 1,
                'title' => 'Ochtendappèl Overleven',
                'slug' => 'ochtendappel-overleven',
                'description' => 'Sta strak in de rij bij Bewaker Barry zonder te gapen of je pyjamabroek binnenstebuiten te dragen.',
                'giver_name' => 'Directeur Gerda Graaigraag',
                'location_name' => 'Grote Centrale Hal',
                'required_level' => 1,
                'energy_cost' => 6,
                'success_rate' => 92,
                'min_cash_reward' => 20,
                'max_cash_reward' => 45,
                'xp_reward' => 20,
                'respect_reward' => 1,
                'success_message' => 'Gerda knikte kort met haar klembord. Je bent niet naar de gang gestuurd!',
                'fail_message' => 'Barry zag je stiekem gapen en liet je 10 push-ups doen.',
                'sort_order' => 1,
            ],
            [
                'category' => 'beginners',
                'storyline' => 'Eerste Dagen in de Kliniek',
                'story_step' => 2,
                'title' => 'Koffiemuntje Ritselen',
                'slug' => 'koffiemuntje-ritselen',
                'description' => 'De automaatkoffie is smerig, maar heet water is vloeibaar goud. Vind een muntje bij de rookruimte.',
                'giver_name' => 'Kevin Koffie',
                'location_name' => 'Koffiehoek Paviljoen 4',
                'required_level' => 1,
                'energy_cost' => 8,
                'success_rate' => 85,
                'min_cash_reward' => 30,
                'max_cash_reward' => 60,
                'xp_reward' => 25,
                'respect_reward' => 2,
                'success_message' => 'Een glanzend muntje lag tussen de klinkers van de rookruimte.',
                'fail_message' => 'De bewaking hield net een inspectie en joeg iedereen weg.',
                'sort_order' => 2,
            ],
            [
                'category' => 'story',
                'storyline' => 'De Grote Frikandelroof',
                'story_step' => 1,
                'title' => 'Inspectie van de Frituur',
                'slug' => 'inspectie-van-de-frituur',
                'description' => 'Henkie vermoedt dat Paviljoen 13 stiekem Mora-voorraden achterhoudt. Leid de kantinejuf af en check het slot van het magazijn.',
                'giver_name' => 'Henkie de Frikandelkoning',
                'location_name' => 'De Kantine',
                'required_level' => 2,
                'energy_cost' => 10,
                'success_rate' => 80,
                'min_cash_reward' => 45,
                'max_cash_reward' => 90,
                'xp_reward' => 35,
                'respect_reward' => 3,
                'success_message' => 'Je ontdekte het geheime rooster achter de snackautomaat!',
                'fail_message' => 'De kantinedame smeet een pollepel naar je hoofd. Maak dat je wegkomt!',
                'sort_order' => 3,
            ],
            [
                'category' => 'story',
                'storyline' => 'Het Mysterie van Paviljoen 13',
                'story_step' => 1,
                'title' => 'Sluipen naar de Oude Vleugel',
                'slug' => 'sluipen-naar-de-oude-vleugel',
                'description' => 'Paviljoen 13 is al tien jaar gesloten wegens "vreemde dampen". Samantha beweert dat er nog altijd licht brandt.',
                'giver_name' => 'Samantha Slotjes',
                'location_name' => 'Corridor Paviljoen 13',
                'required_level' => 3,
                'energy_cost' => 14,
                'success_rate' => 75,
                'min_cash_reward' => 80,
                'max_cash_reward' => 150,
                'xp_reward' => 50,
                'respect_reward' => 4,
                'success_message' => 'Je vond een roestige sleutelkaart en mysterieuze aantekeningen van Dokter Debbie!',
                'fail_message' => 'Een sensor ging af! Je moest halsoverkop terugduiken onder de pingpongtafel.',
                'sort_order' => 4,
            ],
            [
                'category' => 'cash',
                'storyline' => null,
                'story_step' => null,
                'title' => 'Kliniekpost Sorteren',
                'slug' => 'kliniekpost-sorteren',
                'description' => 'Help de postbode met het stempelen van officiële brieven. Soms plakt er per ongeluk contant geld tussen de dossiers.',
                'giver_name' => 'Mo de Meesterstrateeg',
                'location_name' => 'De Administratiepost',
                'required_level' => 2,
                'energy_cost' => 9,
                'success_rate' => 84,
                'min_cash_reward' => 60,
                'max_cash_reward' => 110,
                'xp_reward' => 30,
                'respect_reward' => 2,
                'success_message' => 'Netjes afgerond! En tussen twee formulieren zat een briefje van 50 KC.',
                'fail_message' => 'Je liet de inktpot vallen over Gerda haar rapportage. Snel wegwezen!',
                'sort_order' => 5,
            ],
            [
                'category' => 'theft',
                'storyline' => null,
                'story_step' => null,
                'title' => 'Voorraadkast van de Psychiater Plunderen',
                'slug' => 'voorraadkast-psychiater',
                'description' => 'In de lade van Dokter Debbie liggen stressballen en dure importchocolade. Grijp je kans terwijl ze in overleg zit.',
                'giver_name' => 'Ricardo Ritselaar',
                'location_name' => 'Spreekkamer Debbie',
                'required_level' => 4,
                'energy_cost' => 18,
                'success_rate' => 68,
                'min_cash_reward' => 120,
                'max_cash_reward' => 260,
                'xp_reward' => 65,
                'respect_reward' => 5,
                'success_message' => 'Buit binnen! Debbie zocht net naar haar bril, dus merkte niets.',
                'fail_message' => 'De deur zwaaide open! De bewakers hebben je op heterdaad betrapt.',
                'sort_order' => 6,
            ],
        ];

        foreach ($missions as $m) {
            $createdMission = Mission::create($m);

            // Give demo character some progress on early missions
            if ($createdMission->id <= 2) {
                CharacterMission::create([
                    'character_id' => $demoCharacter->id,
                    'mission_id' => $createdMission->id,
                    'completed_count' => 3,
                    'last_attempted_at' => now()->subHours(2),
                ]);
            }
        }

        // 4. Items in Shop
        $items = [
            [
                'name' => 'Warm Frikandelbroodje',
                'slug' => 'warm-frikandelbroodje',
                'description' => 'Vers uit de kantineoven van Henkie. Herstelt direct 30 energie.',
                'category' => 'consumable',
                'rarity' => 'common',
                'price_kc' => 65,
                'energy_restore' => 30,
                'health_restore' => 10,
                'icon' => 'fa-hotdog',
            ],
            [
                'name' => 'Lauwe Automaatkoffie',
                'slug' => 'lauwe-automaatkoffie',
                'description' => 'Met een verdachte schuimlaag. Geeft een snelle energieboost van 15 punten.',
                'category' => 'consumable',
                'rarity' => 'common',
                'price_kc' => 35,
                'energy_restore' => 15,
                'icon' => 'fa-mug-hot',
            ],
            [
                'name' => 'Kaassoufflé met Mosterd',
                'slug' => 'kaassouffle-mosterd',
                'description' => 'Gloeiend heet aan de binnenkant. Geneest 35 gezondheid.',
                'category' => 'consumable',
                'rarity' => 'uncommon',
                'price_kc' => 90,
                'health_restore' => 35,
                'icon' => 'fa-cheese',
            ],
            [
                'name' => 'Leren Trainingshandschoenen',
                'slug' => 'leren-trainingshandschoenen',
                'description' => 'Geven extra grip op de roestige halters van Barry. Verhoogt je Kracht met +3.',
                'category' => 'equipment',
                'rarity' => 'uncommon',
                'price_kc' => 280,
                'strength_bonus' => 3,
                'icon' => 'fa-mitten',
            ],
            [
                'name' => 'Paarse Kliniek Trainingsjas',
                'slug' => 'paarse-trainingsjas',
                'description' => 'Gevoerd met fleece tegen de tochtige gangen. Verhoogt Verdediging met +4.',
                'category' => 'equipment',
                'rarity' => 'rare',
                'price_kc' => 520,
                'defense_bonus' => 4,
                'icon' => 'fa-vest',
            ],
            [
                'name' => 'De Sleutel van Paviljoen 13',
                'slug' => 'sleutel-paviljoen-13',
                'description' => 'Een zware koperen loper die op verdachte kasten past. Verhoogt Intelligentie met +5.',
                'category' => 'collectible',
                'rarity' => 'epic',
                'price_kc' => 1400,
                'intelligence_bonus' => 5,
                'icon' => 'fa-key',
            ],
            [
                'name' => 'De Gouden Frikandel',
                'slug' => 'de-gouden-frikandel',
                'description' => 'Het ultieme statussymbool in de TBS Kliniek. Alleen voor echte legendes.',
                'category' => 'collectible',
                'rarity' => 'legendary',
                'price_kc' => 10000,
                'icon' => 'fa-crown',
            ],
        ];

        foreach ($items as $itemData) {
            $createdItem = Item::create($itemData);

            if ($createdItem->slug === 'warm-frikandelbroodje') {
                CharacterItem::create([
                    'character_id' => $demoCharacter->id,
                    'item_id' => $createdItem->id,
                    'quantity' => 2,
                ]);
            }
        }

        // 5. Guestbook entries for KeesKnakworst
        GuestbookEntry::create([
            'profile_character_id' => $demoCharacter->id,
            'author_character_id' => $henkieChar->id,
            'message' => 'Gefeliciteerd met level 3, Kees. Nu nog leren hoe je fatsoenlijk een kroket verdedigt in de lunchrij!',
            'created_at' => now()->subHours(6),
        ]);

        GuestbookEntry::create([
            'profile_character_id' => $demoCharacter->id,
            'author_character_id' => $gerdaChar->id,
            'message' => 'Kees, je bent vergeten bijlage 4B van je verlofaanvraag te ondertekenen. Morgen om 08:00 uur melden op kantoor.',
            'created_at' => now()->subHours(3),
        ]);

        GuestbookEntry::create([
            'profile_character_id' => $demoCharacter->id,
            'author_character_id' => $barryChar->id,
            'message' => 'Zag je benchpressen vanochtend. Goede inzet, maar je ellebogen moeten strakker langs je lijf. Kom vanmiddag trainen!',
            'created_at' => now()->subHour(),
        ]);

        // 6. Notifications for Kees
        GameNotification::create([
            'character_id' => $demoCharacter->id,
            'type' => 'welcome',
            'icon' => 'fa-hospital',
            'title' => 'Welkom in TBS Kliniek!',
            'message' => 'Je dossier is aangemaakt. Zorg dat je traint, missies uitvoert en je geld op de bank zet voor iemand het steelt.',
            'is_read' => true,
            'created_at' => now()->subDays(1),
        ]);

        GameNotification::create([
            'character_id' => $demoCharacter->id,
            'type' => 'mission',
            'icon' => 'fa-check',
            'title' => 'Missie voltooit: Ochtendappèl Overleven',
            'message' => 'Je ontving 35 KC en 20 XP voor een vlekkeloze opkomst.',
            'is_read' => false,
            'created_at' => now()->subHours(2),
        ]);
    }
}
