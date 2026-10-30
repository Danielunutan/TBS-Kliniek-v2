<?php

namespace Database\Seeders;

use App\Models\Character;
use App\Models\CharacterItem;
use App\Models\Gang;
use App\Models\Item;
use App\Models\Mission;
use App\Models\NewsArticle;
use App\Models\User;
use App\Support\MissionCatalog;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     * In production, only game definitions (missions and shop items) are seeded.
     * In testing, fixtures are seeded to support the test suite.
     */
    public function run(): void
    {
        // 1. Base Missions (50 complete missions)
        Mission::whereIn('slug', ['sleutelbos-imprint', 'uitbraaktunnel-graven'])->delete();
        foreach (MissionCatalog::all() as $m) {
            Mission::updateOrCreate(['slug' => $m['slug']], $m);
        }

        // 2. Base Shop Items
        $items = [
            // --- ETENSOPTIES & DRINKEN UIT DE KANTINE ---
            [
                'name' => 'Frikandelbroodje',
                'slug' => 'frikandelbroodje',
                'description' => 'Warm uit de frituur met curry. Geeft directe energie.',
                'category' => 'consumable',
                'rarity' => 'common',
                'price_kc' => 100,
                'energy_restore' => 25,
                'health_restore' => 0,
                'icon' => 'fa-hotdog',
            ],
            [
                'name' => 'Saucijzenbroodje',
                'slug' => 'saucijzenbroodje',
                'description' => 'Warm bladerdeeg gevuld met gekruid gehakt uit de bakkerij.',
                'category' => 'consumable',
                'rarity' => 'common',
                'price_kc' => 110,
                'energy_restore' => 30,
                'health_restore' => 0,
                'icon' => 'fa-bread-slice',
            ],
            [
                'name' => 'Bami Schijf Speciaal',
                'slug' => 'bami-schijf-speciaal',
                'description' => 'Krokant gefrituurde bamischijf met curry en uitjes uit de kantine.',
                'category' => 'consumable',
                'rarity' => 'common',
                'price_kc' => 120,
                'energy_restore' => 20,
                'health_restore' => 10,
                'icon' => 'fa-burger',
            ],
            [
                'name' => 'Broodje Bal met Mayo',
                'slug' => 'broodje-bal-mayo',
                'description' => 'Stevige warme gehaktbal in jus op een pistoletje met Zaanse mayonaise.',
                'category' => 'consumable',
                'rarity' => 'common',
                'price_kc' => 150,
                'energy_restore' => 35,
                'health_restore' => 15,
                'icon' => 'fa-drumstick-bite',
            ],
            [
                'name' => 'Kop Sterke Kliniek Koffie',
                'slug' => 'sterke-kliniek-koffie',
                'description' => 'Dubbele espresso uit de automaat van Blok B. Directe oppepper.',
                'category' => 'consumable',
                'rarity' => 'common',
                'price_kc' => 40,
                'energy_restore' => 15,
                'health_restore' => 0,
                'icon' => 'fa-mug-hot',
            ],
            [
                'name' => 'Energy Drink XXL',
                'slug' => 'energy-drink-xxl',
                'description' => 'Maxi blik taurine. Herstelt 50 energie direct om langer te hosselen.',
                'category' => 'consumable',
                'rarity' => 'uncommon',
                'price_kc' => 200,
                'energy_restore' => 50,
                'health_restore' => 0,
                'icon' => 'fa-bolt',
            ],
            [
                'name' => 'Portie Bitterballen (6 stuks)',
                'slug' => 'portie-bitterballen',
                'description' => 'Gloeiend hete frituursnacks met Zaanse mosterd uit de kantinekeuken.',
                'category' => 'consumable',
                'rarity' => 'uncommon',
                'price_kc' => 220,
                'energy_restore' => 45,
                'health_restore' => 20,
                'icon' => 'fa-cookie-bite',
            ],
            [
                'name' => 'Kapsalon Paviljoen 7',
                'slug' => 'kapsalon-paviljoen-7',
                'description' => 'Grote bak döner, friet, gesmolten kaas en knoflooksaus. Vult je hele maag.',
                'category' => 'consumable',
                'rarity' => 'rare',
                'price_kc' => 450,
                'energy_restore' => 75,
                'health_restore' => 50,
                'icon' => 'fa-bowl-food',
            ],
            [
                'name' => 'Gouden Frikandel',
                'slug' => 'gouden-frikandel',
                'description' => 'Het legendarische statussymbool van de kantine. Herstelt direct +50 HP.',
                'category' => 'consumable',
                'rarity' => 'legendary',
                'price_kc' => 1500,
                'energy_restore' => 0,
                'health_restore' => 50,
                'icon' => 'fa-crown',
            ],

            // --- FUNCTIONELE ITEMS MET ECHTE GAME MECHANICS ---
            [
                'name' => 'Zwart Bivakmuts Masker',
                'slug' => 'bivakmuts-masker',
                'description' => 'Verbergt je identiteit bij diefstal, hosselen en overvallen. Voegt +1 Masker 🎭 toe aan je voorraad.',
                'category' => 'consumable',
                'rarity' => 'uncommon',
                'price_kc' => 2500,
                'energy_restore' => 0,
                'health_restore' => 0,
                'icon' => 'fa-mask',
            ],
            [
                'name' => 'Smokkelpakketje Blok B',
                'slug' => 'smokkelpakketje',
                'description' => 'Verzegeld smokkelpakketje. Voegt direct +1 Koffer/Pakket 📦 toe om te openen bij Koffers & Buit!',
                'category' => 'consumable',
                'rarity' => 'rare',
                'price_kc' => 850,
                'energy_restore' => 0,
                'health_restore' => 0,
                'icon' => 'fa-box',
                'is_buyable' => false,
            ],
            [
                'name' => 'Strip Oxazepam',
                'slug' => 'strip-oxazepam',
                'description' => 'Medische rustgever ontvreemd uit de apotheek. Herstelt direct +75 HP na zware gevechten.',
                'category' => 'consumable',
                'rarity' => 'uncommon',
                'price_kc' => 300,
                'energy_restore' => 0,
                'health_restore' => 75,
                'icon' => 'fa-pills',
            ],
            [
                'name' => 'EHBO Noodverbanddoos',
                'slug' => 'ehbo-noodverbanddoos',
                'description' => 'Compleet medisch traumapakket. Geneest je direct naar 100% Volledige Gezondheid (Max HP)!',
                'category' => 'consumable',
                'rarity' => 'rare',
                'price_kc' => 800,
                'energy_restore' => 0,
                'health_restore' => 100,
                'icon' => 'fa-kit-medical',
            ],
            [
                'name' => 'Adrenaline Injectie',
                'slug' => 'adrenaline-injectie',
                'description' => 'Zuivere adrenalinepen. Herstelt direct al je Energie naar 100% Maximaal!',
                'category' => 'consumable',
                'rarity' => 'epic',
                'price_kc' => 1000,
                'energy_restore' => 100,
                'health_restore' => 0,
                'icon' => 'fa-syringe',
            ],
            [
                'name' => 'Eiwitshake Bodybuilder',
                'slug' => 'eiwitshake-bodybuilder',
                'description' => 'Zware proteïneshake van Barry Biceps. Geeft een permanente toename van +1 Kracht (Strength)!',
                'category' => 'consumable',
                'rarity' => 'rare',
                'price_kc' => 1250,
                'energy_restore' => 10,
                'health_restore' => 10,
                'strength_bonus' => 1,
                'icon' => 'fa-bottle-water',
            ],
            [
                'name' => 'Multivitaminen & Focus',
                'slug' => 'multivitaminen-focus',
                'description' => 'Sportvitaminen voor uithoudingsvermogen. Geeft een permanente toename van +1 Conditie (Stamina)!',
                'category' => 'consumable',
                'rarity' => 'rare',
                'price_kc' => 1250,
                'energy_restore' => 10,
                'health_restore' => 10,
                'stamina_bonus' => 1,
                'icon' => 'fa-capsules',
            ],
            [
                'name' => 'IJzerzaagje (Ontsnappingsset)',
                'slug' => 'ijzerzaagje',
                'description' => 'Gehard stalen zaagje verstopt in een zeepblok. Verkort direct je isolatiestraf met 30 minuten!',
                'category' => 'consumable',
                'rarity' => 'epic',
                'price_kc' => 950,
                'energy_restore' => 0,
                'health_restore' => 0,
                'icon' => 'fa-screwdriver-wrench',
            ],
            [
                'name' => 'Sigaretten',
                'slug' => 'sigaretten',
                'description' => 'Pakjes zware tabak, het meest verhandelde betaalmiddel in de kliniek.',
                'category' => 'contraband',
                'rarity' => 'common',
                'price_kc' => 25,
                'energy_restore' => 20,
                'health_restore' => 10,
                'icon' => 'fa-smoking',
                'is_buyable' => false,
            ],
            [
                'name' => 'Ritalin Strips',
                'slug' => 'ritalin',
                'description' => 'Illegaal verhandelde medicatie van de psychiatrische afdeling.',
                'category' => 'contraband',
                'rarity' => 'uncommon',
                'price_kc' => 80,
                'energy_restore' => 35,
                'health_restore' => 15,
                'icon' => 'fa-pills',
                'is_buyable' => false,
            ],
            [
                'name' => 'Gesmokkelde Telefoon',
                'slug' => 'telefoon',
                'description' => 'Een kleine mobiel om contact te onderhouden met de buitenwereld.',
                'category' => 'contraband',
                'rarity' => 'rare',
                'price_kc' => 500,
                'energy_restore' => 50,
                'health_restore' => 25,
                'icon' => 'fa-mobile-screen',
                'is_buyable' => false,
            ],

            // --- UITRUSTING & COLLECTIBLES (GEVECHTEN & MISSIES) ---
            [
                'name' => 'Boksbeugel (Gevechtswapen)',
                'slug' => 'boksbeugel',
                'description' => 'Gietijzeren boksbeugel. Biedt directe slagkracht (+5 Kracht bonus in gevechten).',
                'category' => 'equipment',
                'rarity' => 'uncommon',
                'price_kc' => 500,
                'strength_bonus' => 5,
                'icon' => 'fa-hand-back-fist',
            ],
            [
                'name' => 'Stalen Neus Kisten',
                'slug' => 'stalen-neus-kisten',
                'description' => 'Zware werkschoenen met stalen neuzen voor gevechten (+6 Verdediging bonus).',
                'category' => 'equipment',
                'rarity' => 'uncommon',
                'price_kc' => 650,
                'defense_bonus' => 6,
                'icon' => 'fa-shoe-prints',
            ],
            [
                'name' => 'Trainingshandschoenen',
                'slug' => 'trainingshandschoenen',
                'description' => 'Goede grip op zware gewichten in de gym (+3 Kracht bonus).',
                'category' => 'equipment',
                'rarity' => 'common',
                'price_kc' => 250,
                'strength_bonus' => 3,
                'icon' => 'fa-hand-fist',
            ],
            [
                'name' => 'Paarse Trainingsjas',
                'slug' => 'paarse-trainingsjas',
                'description' => 'Stijlvol paars retro trainingsjack. Iedereen herkent je (+5 Charisma).',
                'category' => 'equipment',
                'rarity' => 'rare',
                'price_kc' => 600,
                'charisma_bonus' => 5,
                'icon' => 'fa-shirt',
            ],
            [
                'name' => 'Oude Sleutel',
                'slug' => 'oude-sleutel',
                'description' => 'Een roestige sleutel gevonden bij de wasserij (+10 Intelligentie).',
                'category' => 'equipment',
                'rarity' => 'uncommon',
                'price_kc' => 800,
                'intelligence_bonus' => 10,
                'icon' => 'fa-key',
            ],
            [
                'name' => 'De Sleutel van Paviljoen 13',
                'slug' => 'sleutel-paviljoen-13',
                'description' => 'Speciaal item dat de deuren van de oude vleugel opent (+12 Intelligentie).',
                'category' => 'equipment',
                'rarity' => 'epic',
                'price_kc' => 5000,
                'intelligence_bonus' => 12,
                'icon' => 'fa-key',
            ],
            [
                'name' => 'De Legendarische Halter',
                'slug' => 'legendarische-halter',
                'description' => 'Gietijzeren halter van 100 kg gesmeed door Barry (+20 Kracht).',
                'category' => 'equipment',
                'rarity' => 'legendary',
                'price_kc' => 10000,
                'strength_bonus' => 20,
                'icon' => 'fa-dumbbell',
            ],
        ];

        foreach ($items as $itemData) {
            Item::updateOrCreate(['slug' => $itemData['slug']], $itemData);
        }

        // 3. Base News Articles (matching screenshots)
        $defaultArticles = [
            [
                'title' => 'Hyves',
                'slug' => 'hyves',
                'emoji' => '🏥',
                'is_pinned' => true,
                'comments_enabled' => false,
                'is_published' => true,
                'content' => "TBS Kliniek zit nu op Hyves!\n\nJoin onze Hyve voor exclusieve kliniek-updates, winacties en geheime smokkelcodes. Houd je dossier in de gaten en respecteer de afdelingsregels!\n\nGroeten,\nKliniek Directie",
                'created_at' => now()->subDays(12),
                'published_at' => now()->subDays(12),
            ],
            [
                'title' => 'iOS App Update',
                'slug' => 'ios-app-update',
                'emoji' => '💗',
                'is_pinned' => false,
                'comments_enabled' => true,
                'is_published' => true,
                'content' => "[h2]Grote Mobiele & iOS Update[/h2]\n\nDe webapp is geoptimaliseerd voor alle mobiele browsers en iOS Safari. Met snellere laadtijden en een handig app-icoon op je startscherm mis je geen enkele bendeaanval of uitkering meer!\n\n[quote]Tip: Voeg de website toe aan je beginscherm via 'Deel' -> 'Zet op beginscherm' op je telefoon.[/quote]\n\nLaat ons hieronder weten hoe de game draait op jouw mobiel!",
                'created_at' => now()->subMonths(2),
                'published_at' => now()->subMonths(2),
            ],
            [
                'title' => 'Ghost Mode update',
                'slug' => 'ghost-mode-update',
                'emoji' => '👻',
                'is_pinned' => false,
                'comments_enabled' => true,
                'is_published' => true,
                'content' => "[h2]Nachtelijke Sluiptochten in de TBS Kliniek[/h2]\n\nMet de nieuwste [b]Ghost Mode[/b] update kunnen bendes en sluwe patiënten ongemerkt opereren tijdens de avondklok.\n\n- Verborgen verplaatsing over de binnenplaats\n- Minder kans om direct gesnapt te worden bij diefstallen\n- Nieuwe smokkelroutes in Paviljoen 13\n\nVeel succes met jullie operaties in de schaduw!",
                'created_at' => now()->subMonths(2)->subWeeks(1),
                'published_at' => now()->subMonths(2)->subWeeks(1),
            ],
        ];

        foreach ($defaultArticles as $dArt) {
            NewsArticle::firstOrCreate(['slug' => $dArt['slug']], $dArt);
        }

        // 4. Testing-only Fixtures
        if (app()->environment('testing')) {
            $this->seedTestFixtures();
        }
    }

    /**
     * Fixtures only populated during automated tests (in-memory SQLite).
     */
    private function seedTestFixtures(): void
    {
        $gangKantine = Gang::create([
            'name' => 'De Kantinebende',
            'tag' => 'KB',
            'description' => 'Wie de frituur beheerst, beheerst de kliniek.',
            'specialty' => 'Geld | Handel | Invloed',
            'emblem' => 'fa-burger',
            'treasury' => 8450,
        ]);

        $gangPlofkippen = Gang::create([
            'name' => 'De Paarse Plofkippen',
            'tag' => 'PP',
            'description' => 'Geleid door Yordi de Regelaar.',
            'specialty' => 'Groepsmissies & Invloed',
            'emblem' => 'fa-feather',
            'treasury' => 5200,
        ]);

        $gangHalters = Gang::create([
            'name' => 'De IJzeren Halters',
            'tag' => 'IJH',
            'description' => 'Geleid door Barry Biceps.',
            'specialty' => 'Kracht & Gevechten',
            'emblem' => 'fa-dumbbell',
            'treasury' => 6100,
        ]);

        $gangGerda = Gang::create([
            'name' => 'De Administratieve Nachtmerrie',
            'tag' => 'DAN',
            'description' => 'Directeur Gerda.',
            'specialty' => 'Intelligentie & Strategie',
            'emblem' => 'fa-file-signature',
            'treasury' => 24000,
        ]);

        $gangWasmachine = Gang::create([
            'name' => 'De Wasmachine Warriors',
            'tag' => 'WW',
            'description' => 'Ome Willem.',
            'specialty' => 'Verzamelen & Geheimen',
            'emblem' => 'fa-soap',
            'treasury' => 3800,
        ]);

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
            'bio' => 'Henkie de Frikandelkoning.',
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
            'gang_id' => $gangKantine->id,
        ]);

        $rianneUser = User::create(['name' => 'BakkerieRianne', 'email' => 'rianne@tbskliniek.nl', 'password' => Hash::make('secret')]);
        $rianneChar = Character::create([
            'user_id' => $rianneUser->id,
            'name' => 'BakkerieRianne',
            'title' => 'Broodjeskoningin',
            'avatar' => 'characters/rianne.jpg',
            'bio' => 'Smokkelt stiekem extra saucijzenbroodjes naar binnen.',
            'level' => 10,
            'xp' => 240,
            'total_xp' => 3200,
            'cash' => 850,
            'bank' => 4500,
            'respect' => 410,
            'strength' => 22,
            'defense' => 18,
            'stamina' => 25,
            'intelligence' => 30,
            'charisma' => 35,
            'discipline' => 20,
            'gang_id' => $gangKantine->id,
        ]);

        $queenUser = User::create(['name' => 'FrikandelQueen', 'email' => 'queen@tbskliniek.nl', 'password' => Hash::make('secret')]);
        $queenChar = Character::create([
            'user_id' => $queenUser->id,
            'name' => 'FrikandelQueen',
            'title' => 'Snackbaas',
            'avatar' => 'characters/frikandelqueen.jpg',
            'bio' => 'Niemand raakt de frituurpan aan.',
            'level' => 9,
            'xp' => 120,
            'total_xp' => 2700,
            'cash' => 620,
            'bank' => 3800,
            'respect' => 380,
            'strength' => 26,
            'defense' => 20,
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
            'bio' => 'Gerda kan de pot op.',
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

        // Attach item and initial mission records for Henkie fixture
        $frikandel = Item::where('slug', 'frikandelbroodje')->first();
        if ($frikandel) {
            CharacterItem::firstOrCreate([
                'character_id' => $henkieChar->id,
                'item_id' => $frikandel->id,
            ], [
                'quantity' => 2,
            ]);
        }
    }
}
