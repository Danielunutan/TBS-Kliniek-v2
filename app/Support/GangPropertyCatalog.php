<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;

class GangPropertyCatalog
{
    /**
     * All available properties that gangs can purchase with gang treasury.
     *
     * @return array<string, array<string, mixed>>
     */
    public static function all(): array
    {
        return [
            // ==========================================
            // 1. GARAGES, OPSLAG & STALLING
            // ==========================================
            'kleine_loods' => [
                'key' => 'kleine_loods',
                'name' => 'Kleine Opslagloods & Garage',
                'category' => 'storage',
                'category_label' => 'Garage & Opslag',
                'icon' => 'fa-warehouse',
                'color' => 'amber',
                'price' => 150000,
                'storage_capacity' => 50,
                'vehicle_slots' => 2,
                'npc_capacity' => 0,
                'production_duration_minutes' => 0,
                'production_reward_cash' => 0,
                'production_reward_respect' => 0,
                'tagline' => 'Starter opslag voor goederen & voertuigen',
                'description' => 'Eenvoudige loods aan de rand van het haventerrein. Biedt directe stalling voor 2 bendevoertuigen en 50 extra bende opslagplaatsen.',
                'perks_summary' => '+50 Bende Opslag • 2 Voertuigplekken',
            ],
            'garagebox_complex' => [
                'key' => 'garagebox_complex',
                'name' => 'Ondergronds Garageboxen Complex',
                'category' => 'storage',
                'category_label' => 'Garage & Stalling',
                'icon' => 'fa-car-tunnel',
                'color' => 'cyan',
                'price' => 320000,
                'storage_capacity' => 75,
                'vehicle_slots' => 4,
                'npc_capacity' => 0,
                'production_duration_minutes' => 0,
                'production_reward_cash' => 0,
                'production_reward_respect' => 0,
                'tagline' => 'Geluidsdichte garageboxen met camerabeveiliging',
                'description' => 'Reeks beveiligde ondergrondse garageboxen onder een verlaten parkeergarage. Biedt plaats aan 4 vluchtvoertuigen en 75 opslagplaatsen.',
                'perks_summary' => '+75 Bende Opslag • 4 Voertuigplekken',
            ],
            'haven_loods' => [
                'key' => 'haven_loods',
                'name' => 'Oude Scheepswerf & Havenloods',
                'category' => 'storage',
                'category_label' => 'Haven & Wagenpark',
                'icon' => 'fa-anchor',
                'color' => 'sky',
                'price' => 480000,
                'storage_capacity' => 120,
                'vehicle_slots' => 6,
                'npc_capacity' => 0,
                'production_duration_minutes' => 0,
                'production_reward_cash' => 0,
                'production_reward_respect' => 0,
                'tagline' => 'Ideale havenlocatie voor snelle bendevoertuigen',
                'description' => 'Zwaar verroeste maar functionele scheepsloods met brede roldeuren en directe uitvalswegen. Biedt stalling voor 6 bendevoertuigen.',
                'perks_summary' => '+120 Bende Opslag • 6 Voertuigplekken',
            ],
            'industrieele_loods' => [
                'key' => 'industrieele_loods',
                'name' => 'Industriële Distributieloods',
                'category' => 'storage',
                'category_label' => 'Garage & Opslag',
                'icon' => 'fa-boxes-stacked',
                'color' => 'blue',
                'price' => 750000,
                'storage_capacity' => 200,
                'vehicle_slots' => 8,
                'npc_capacity' => 0,
                'production_duration_minutes' => 0,
                'production_reward_cash' => 0,
                'production_reward_respect' => 0,
                'tagline' => 'Forse opslagruimte & wagenpark stalling',
                'description' => 'Grote distributieloods met roldeuren, palletstellingen en versterkte vloer. Biedt 200 opslagplaatsen voor buit en plaats voor 8 bendevoertuigen.',
                'perks_summary' => '+200 Bende Opslag • 8 Voertuigplekken',
            ],
            'chop_shop_garage' => [
                'key' => 'chop_shop_garage',
                'name' => 'Illegale Omkat & Chop-Shop Garage',
                'category' => 'storage',
                'category_label' => 'Garage & Tuning',
                'icon' => 'fa-wrench',
                'color' => 'orange',
                'price' => 1100000,
                'storage_capacity' => 250,
                'vehicle_slots' => 12,
                'npc_capacity' => 1,
                'production_duration_minutes' => 0,
                'production_reward_cash' => 0,
                'production_reward_respect' => 20,
                'tagline' => 'Geavanceerde werkplaats voor wagenpark modificaties',
                'description' => 'Volledig uitgeruste autowerkplaats met spuitcabine, hefbruggen en lasapparatuur. Biedt plaats aan 12 bendevoertuigen en 1 NPC monteur/specialist.',
                'perks_summary' => '+250 Bende Opslag • 12 Voertuigplekken • 1 NPC Slot',
            ],
            'trucking_terminal' => [
                'key' => 'trucking_terminal',
                'name' => 'Zwaar Transport & Trailer Terminal',
                'category' => 'storage',
                'category_label' => 'Transport Hub',
                'icon' => 'fa-truck-front',
                'color' => 'indigo',
                'price' => 1800000,
                'storage_capacity' => 450,
                'vehicle_slots' => 16,
                'npc_capacity' => 2,
                'production_duration_minutes' => 0,
                'production_reward_cash' => 0,
                'production_reward_respect' => 40,
                'tagline' => 'Centraal logistiek knooppunt voor het zware wagenpark',
                'description' => 'Enorm terrein met overdekte oprijlanen, benzinepompen en laaddocks. Biedt ruimte aan 16 bendevoertuigen en 450 bende opslagplaatsen.',
                'perks_summary' => '+450 Bende Opslag • 16 Voertuigplekken • 2 NPC Slots',
            ],
            'vliegveld_hangar' => [
                'key' => 'vliegveld_hangar',
                'name' => 'Verlaten Smuggle Vliegveld Hangar',
                'category' => 'storage',
                'category_label' => 'Luchtvaart Hangar',
                'icon' => 'fa-plane',
                'color' => 'violet',
                'price' => 2800000,
                'storage_capacity' => 650,
                'vehicle_slots' => 20,
                'npc_capacity' => 3,
                'production_duration_minutes' => 0,
                'production_reward_cash' => 0,
                'production_reward_respect' => 80,
                'tagline' => 'Gigantische hangar aan een illegale landingsbaan',
                'description' => 'Enorme vliegtuigloods met robuuste staalconstructie. Geschikt voor helikopters, pantserwagens en snelle supercars. Biedt 20 wagenplekken en 650 opslag.',
                'perks_summary' => '+650 Bende Opslag • 20 Voertuigplekken • 3 NPC Slots',
            ],
            'ondergrondse_bunker' => [
                'key' => 'ondergrondse_bunker',
                'name' => 'Versterkte Ondergrondse Bunker',
                'category' => 'storage',
                'category_label' => 'Militaire Bunker',
                'icon' => 'fa-vault',
                'color' => 'slate',
                'price' => 3800000,
                'storage_capacity' => 900,
                'vehicle_slots' => 25,
                'npc_capacity' => 4,
                'production_duration_minutes' => 0,
                'production_reward_cash' => 0,
                'production_reward_respect' => 120,
                'tagline' => 'Ondoordringbare kluisbunker & vlootcommandocentrum',
                'description' => 'Militaire atoomschuilkelder onder de kliniekvleugel. Biedt maximale beveiliging tegen invallen, 900 opslagplaatsen en maar liefst 25 zware wagenplekken.',
                'perks_summary' => '+900 Bende Opslag • 25 Voertuigplekken • 4 NPC Slots • Bende Pantser',
            ],

            // ==========================================
            // 2. PRODUCTIE, LABS & FABRIEKEN
            // ==========================================
            'wietkwekerij' => [
                'key' => 'wietkwekerij',
                'name' => 'Ondergrondse Hydrocultuur Kwekerij',
                'category' => 'production',
                'category_label' => 'Productie & Teelt',
                'icon' => 'fa-cannabis',
                'color' => 'emerald',
                'price' => 450000,
                'storage_capacity' => 30,
                'vehicle_slots' => 1,
                'npc_capacity' => 1,
                'production_duration_minutes' => 90, // 1.5 uur
                'production_reward_cash' => 50000,
                'production_reward_respect' => 20,
                'tagline' => 'Snelle continue oogsten van medicinale wiet',
                'description' => 'Geautomatiseerde kweekruimte met LED-groeilampen en koolstoffilters. Levert elke 90 minuten €50.000 cash en 20 Respect op.',
                'perks_summary' => 'Productie: €50.000 Cash + 20 Respect (per 1.5 uur run)',
            ],
            'valsgeld_drukkerij' => [
                'key' => 'valsgeld_drukkerij',
                'name' => 'Ondergrondse Valsgeld Drukkerij',
                'category' => 'production',
                'category_label' => 'Productie & Drukkerij',
                'icon' => 'fa-money-bill-transfer',
                'color' => 'teal',
                'price' => 850000,
                'storage_capacity' => 40,
                'vehicle_slots' => 1,
                'npc_capacity' => 1,
                'production_duration_minutes' => 120, // 2 uur
                'production_reward_cash' => 90000,
                'production_reward_respect' => 35,
                'tagline' => 'Drukt stapels zwarte KliniekCash biljetten',
                'description' => 'Professionele offset drukpersen in een geluiddichte kelder. Leiders kunnen periodiek een drukcyclus starten om €90.000 en 35 Respect te claimen.',
                'perks_summary' => 'Productie: €90.000 Cash + 35 Respect (per 2 uur run)',
            ],
            'sigaretten_fabriek' => [
                'key' => 'sigaretten_fabriek',
                'name' => 'Illegale Sigaretten Fabriek & Verpakker',
                'category' => 'production',
                'category_label' => 'Productie & Tabak',
                'icon' => 'fa-smoking',
                'color' => 'amber',
                'price' => 1250000,
                'storage_capacity' => 60,
                'vehicle_slots' => 2,
                'npc_capacity' => 2,
                'production_duration_minutes' => 150, // 2.5 uur
                'production_reward_cash' => 135000,
                'production_reward_respect' => 50,
                'tagline' => 'Rolautomaten voor belastingvrije sigaretten',
                'description' => 'Grote sigarettenrollerij met verpakkingslijn. Levert bij elke afgeronde run €135.000 contant geld op voor de bendekas en 50 benderespect.',
                'perks_summary' => 'Productie: €135.000 Cash + 50 Respect (per 2.5 uur run)',
            ],
            'chemisch_lab' => [
                'key' => 'chemisch_lab',
                'name' => 'Geheim Chemisch Smokkel-Lab',
                'category' => 'production',
                'category_label' => 'Productie & Chemie',
                'icon' => 'fa-flask-vial',
                'color' => 'cyan',
                'price' => 1750000,
                'storage_capacity' => 80,
                'vehicle_slots' => 2,
                'npc_capacity' => 2,
                'production_duration_minutes' => 240, // 4 uur
                'production_reward_cash' => 210000,
                'production_reward_respect' => 75,
                'tagline' => 'Grootschalige smokkelwaar synthese',
                'description' => 'Geavanceerde kolven, distillatievaten en chemische reactoren. Levert bij elke voltooide productiecyclus €210.000 in de bende treasury op en 75 Respect.',
                'perks_summary' => 'Productie: €210.000 Cash + 75 Respect (per 4 uur run)',
            ],
            'crypto_mining_bunker' => [
                'key' => 'crypto_mining_bunker',
                'name' => 'Ondergrondse Crypto Mining Farm',
                'category' => 'production',
                'category_label' => 'Productie & Crypto',
                'icon' => 'fa-microchip',
                'color' => 'indigo',
                'price' => 2400000,
                'storage_capacity' => 50,
                'vehicle_slots' => 2,
                'npc_capacity' => 2,
                'production_duration_minutes' => 180, // 3 uur
                'production_reward_cash' => 275000,
                'production_reward_respect' => 95,
                'tagline' => 'Racks vol ASIC miners aangesloten op afgetapte stroom',
                'description' => 'Stille, zwaar gekoelde bunker vol miners die non-stop ontraceerbare cryptocurrency delven. Levert €275.000 per 3 uur op.',
                'perks_summary' => 'Productie: €275.000 Cash + 95 Respect (per 3 uur run)',
            ],
            'wapenfabriek' => [
                'key' => 'wapenfabriek',
                'name' => 'Verborgen Wapenfabriek & Smelterij',
                'category' => 'production',
                'category_label' => 'Productie & Wapens',
                'icon' => 'fa-gun',
                'color' => 'rose',
                'price' => 3500000,
                'storage_capacity' => 100,
                'vehicle_slots' => 3,
                'npc_capacity' => 3,
                'production_duration_minutes' => 360, // 6 uur
                'production_reward_cash' => 450000,
                'production_reward_respect' => 180,
                'tagline' => 'Zware vuurwapens & munitie fabricage',
                'description' => 'CNC-freesmachines en draaibanken voor assemblage van zware vuurwapens. Levert maar liefst €450.000 en 180 Respect per cyclus op.',
                'perks_summary' => 'Productie: €450.000 Cash + 180 Respect (per 6 uur run)',
            ],
            'synthese_complex' => [
                'key' => 'synthese_complex',
                'name' => 'Industrieel Narcotica Synthese Complex',
                'category' => 'production',
                'category_label' => 'Productie & Pharma',
                'icon' => 'fa-capsules',
                'color' => 'fuchsia',
                'price' => 5000000,
                'storage_capacity' => 150,
                'vehicle_slots' => 4,
                'npc_capacity' => 4,
                'production_duration_minutes' => 480, // 8 uur
                'production_reward_cash' => 700000,
                'production_reward_respect' => 280,
                'tagline' => 'Het grootste illegale lab van de regio',
                'description' => 'Farmaceutische precisieapparatuur die gigantische batches hoogwaardige partydrugs produceert. Levert maar liefst €700.000 per cyclus op.',
                'perks_summary' => 'Productie: €700.000 Cash + 280 Respect (per 8 uur run)',
            ],

            // ==========================================
            // 3. LUXE PANDEN, VILLA'S & HOOFDKWARTIEREN
            // ==========================================
            'stripclub_lounge' => [
                'key' => 'stripclub_lounge',
                'name' => 'VIP Gentleman\'s Club & Witwas Lounge',
                'category' => 'luxury',
                'category_label' => 'Luxe Pand & Club',
                'icon' => 'fa-martini-glass-citrus',
                'color' => 'pink',
                'price' => 1200000,
                'storage_capacity' => 60,
                'vehicle_slots' => 3,
                'npc_capacity' => 2,
                'production_duration_minutes' => 0,
                'production_reward_cash' => 0,
                'production_reward_respect' => 50,
                'wage_bonus_pct' => 6,
                'tagline' => 'Bruisend nachtleven dekmantel voor witwaspraktijken',
                'description' => 'Exclusieve nachtclub met VIP kamers en beveiligd kantoor. Biedt plaats aan 2 NPC specialisten en verhoogt bendelonen met +6%.',
                'perks_summary' => '2 NPC Slots • 3 Voertuigplekken • +6% Bende Uurloon • 50 Respect',
            ],
            'casino_penthouse' => [
                'key' => 'casino_penthouse',
                'name' => 'Geheime High-Roller Casino Suite',
                'category' => 'luxury',
                'category_label' => 'Luxe Pand & Casino',
                'icon' => 'fa-dice',
                'color' => 'amber',
                'price' => 1900000,
                'storage_capacity' => 80,
                'vehicle_slots' => 4,
                'npc_capacity' => 3,
                'production_duration_minutes' => 0,
                'production_reward_cash' => 0,
                'production_reward_respect' => 70,
                'wage_bonus_pct' => 9,
                'tagline' => 'Illegaal gokparadijs voor de onderwereld elite',
                'description' => 'Roulette tafels, blackjack hoeken en poker salons achter een onopvallende boekenkast. Biedt 3 NPC slots en +9% bende uurloon bonus.',
                'perks_summary' => '3 NPC Slots • 4 Voertuigplekken • +9% Bende Uurloon • 70 Respect',
            ],
            'luxe_penthouse' => [
                'key' => 'luxe_penthouse',
                'name' => 'Luxe Sky-Penthouse HQ',
                'category' => 'luxury',
                'category_label' => 'Luxe Pand & Penthouse',
                'icon' => 'fa-city',
                'color' => 'purple',
                'price' => 2750000,
                'storage_capacity' => 100,
                'vehicle_slots' => 5,
                'npc_capacity' => 4,
                'production_duration_minutes' => 0,
                'production_reward_cash' => 0,
                'production_reward_respect' => 90,
                'wage_bonus_pct' => 12,
                'tagline' => 'Hoofdkwartier met panoramisch uitzicht over de stad',
                'description' => 'Exclusief penthouse op de bovenste verdieping met vergaderruimte en kogelwerend glas. Biedt onderdak aan 4 NPC specialisten en +12% uurloon bonus.',
                'perks_summary' => '4 NPC Slots • 5 Voertuigplekken • +12% Bende Uurloon • 90 Respect',
            ],
            'haven_jachthuis' => [
                'key' => 'haven_jachthuis',
                'name' => 'Zwaarbewaakt Havenjachthuis & Pier HQ',
                'category' => 'luxury',
                'category_label' => 'Luxe Pand & Jachthaven',
                'icon' => 'fa-ship',
                'color' => 'blue',
                'price' => 3900000,
                'storage_capacity' => 160,
                'vehicle_slots' => 7,
                'npc_capacity' => 5,
                'production_duration_minutes' => 0,
                'production_reward_cash' => 0,
                'production_reward_respect' => 140,
                'wage_bonus_pct' => 16,
                'tagline' => 'Exclusief maritiem hoofdkwartier aan open water',
                'description' => 'Luxe jachthuis met eigen aanlegsteigers voor smokkelboten en garage voor snelle sportwagens. Biedt plaats aan 5 NPC specialisten.',
                'perks_summary' => '5 NPC Slots • 7 Voertuigplekken • +16% Bende Uurloon • 140 Respect',
            ],
            'monumentaal_grachtenpand' => [
                'key' => 'monumentaal_grachtenpand',
                'name' => 'Monumentaal Grachtenpand Hoofdkantoor',
                'category' => 'luxury',
                'category_label' => 'Luxe Pand & Grachtenpand',
                'icon' => 'fa-monument',
                'color' => 'emerald',
                'price' => 5200000,
                'storage_capacity' => 220,
                'vehicle_slots' => 9,
                'npc_capacity' => 7,
                'production_duration_minutes' => 0,
                'production_reward_cash' => 0,
                'production_reward_respect' => 200,
                'wage_bonus_pct' => 20,
                'tagline' => 'Historisch prestige pand met geheime kluiskelders',
                'description' => 'Statig 17e-eeuws grachtenpand in het hart van de stad. Voorzien van discreet kogelwerend interieur, 7 NPC plekken en +20% uurloon bonus.',
                'perks_summary' => '7 NPC Slots • 9 Voertuigplekken • +20% Bende Uurloon • 200 Respect',
            ],
            'gang_villa' => [
                'key' => 'gang_villa',
                'name' => 'Zwaarbeveiligd Landgoed & Gang Villa',
                'category' => 'luxury',
                'category_label' => 'Luxe Pand & Villa',
                'icon' => 'fa-landmark-dome',
                'color' => 'amber',
                'price' => 7500000,
                'storage_capacity' => 350,
                'vehicle_slots' => 14,
                'npc_capacity' => 10,
                'production_duration_minutes' => 0,
                'production_reward_cash' => 0,
                'production_reward_respect' => 300,
                'wage_bonus_pct' => 28,
                'tagline' => 'Het ultieme cartel hoofdkwartier met commandocentrale',
                'description' => 'Imposant zwaarbeveiligd landgoed met helikopterplatform en commandocentrale. Biedt plaats aan 10 NPC bendeleden, 14 wagenplekken en +28% uurloon bonus.',
                'perks_summary' => '10 NPC Rekruten Plekken • 14 Voertuigplekken • +28% Bende Uurloon • 300 Respect',
            ],
            'fort_eiland' => [
                'key' => 'fort_eiland',
                'name' => 'Privé Vestingeiland Cartel Citadel',
                'category' => 'luxury',
                'category_label' => 'Fort & Citadel',
                'icon' => 'fa-chess-rook',
                'color' => 'rose',
                'price' => 12500000,
                'storage_capacity' => 800,
                'vehicle_slots' => 25,
                'npc_capacity' => 15,
                'production_duration_minutes' => 0,
                'production_reward_cash' => 0,
                'production_reward_respect' => 600,
                'wage_bonus_pct' => 40,
                'tagline' => 'Onaantastbare burcht in internationale wateren',
                'description' => 'Een eigen forteiland compleet omringd door radarinstallaties, zware wachttorens en ondergrondse haven. Biedt onderdak aan 15 NPC elite specialisten en 25 bendevoertuigen.',
                'perks_summary' => '15 NPC Slots • 25 Voertuigplekken • +40% Bende Uurloon • 600 Respect • Maximale Verdediging',
            ],
        ];
    }

    /**
     * Get single property definition.
     */
    public static function get(string $key): ?array
    {
        return self::all()[$key] ?? null;
    }

    /**
     * Get the current 30-minute rotating property showroom listings.
     * Rotates at :00 and :30 of every hour, synchronized for all gangs!
     *
     * @return array{slot: int, expires_at: int, seconds_left: int, listings: array}
     */
    public static function getRotatingProperties(): array
    {
        $slotDuration = 1800; // 30 minutes
        $currentTimestamp = time();
        $slot = (int) floor($currentTimestamp / $slotDuration);
        $secondsLeft = $slotDuration - ($currentTimestamp % $slotDuration);
        $expiresAt = ($slot + 1) * $slotDuration;

        $cacheKey = "gang_properties_rotation_{$slot}";

        $listings = Cache::remember($cacheKey, $secondsLeft + 5, function () use ($slot) {
            $all = collect(self::all());
            if ($all->isEmpty()) {
                return [];
            }

            mt_srand($slot * 42421);

            // Group into 4 distinct price/tier brackets to guarantee rich choice
            $tier1 = $all->filter(fn ($p) => $p['price'] <= 600000)->shuffle();
            $tier2 = $all->filter(fn ($p) => $p['price'] > 600000 && $p['price'] <= 1600000)->shuffle();
            $tier3 = $all->filter(fn ($p) => $p['price'] > 1600000 && $p['price'] <= 3500000)->shuffle();
            $tier4 = $all->filter(fn ($p) => $p['price'] > 3500000)->shuffle();

            $selected = collect();
            $selected = $selected->merge($tier1->take(2));
            $selected = $selected->merge($tier2->take(2));
            $selected = $selected->merge($tier3->take(2));
            $selected = $selected->merge($tier4->take(2));

            if ($selected->count() < 8) {
                $remaining = $all->whereNotIn('key', $selected->pluck('key'))->shuffle();
                $selected = $selected->merge($remaining->take(8 - $selected->count()));
            }

            $items = [];
            foreach ($selected->values() as $index => $prop) {
                // Dynamic half-hour special discount / perk: between -15% and +5%
                $variance = mt_rand(-15, 5);
                $discountFactor = 1.0 + ($variance * 0.01);
                $discountedPrice = (int) (round(($prop['price'] * $discountFactor) / 2500) * 2500);

                $token = substr(hash_hmac('sha256', "{$slot}_{$prop['key']}_{$discountedPrice}", config('app.key')), 0, 16);

                $items[] = [
                    'index' => $index,
                    'token' => $token,
                    'property' => $prop,
                    'discount_pct' => -$variance,
                    'dynamic_price' => max(50000, $discountedPrice),
                ];
            }

            return $items;
        });

        return [
            'slot' => $slot,
            'expires_at' => $expiresAt,
            'seconds_left' => $secondsLeft,
            'listings' => $listings,
        ];
    }

    /**
     * Complete 100+ rich NPC recruits catalog.
     *
     * @return array<string, array<string, mixed>>
     */
    public static function npcs(): array
    {
        static $cachedNpcs = null;
        if ($cachedNpcs !== null) {
            return $cachedNpcs;
        }

        // Base 3 legacy characters (preserve exact keys and data)
        $list = [
            'productie_manager' => [
                'key' => 'productie_manager',
                'name' => 'Dimitri "De Opzichter"',
                'role_title' => 'Fabrieks- & Logistiek Manager',
                'icon' => 'fa-user-tie',
                'cost' => 250000,
                'bonus_type' => 'production_yield',
                'bonus_value' => 25,
                'description' => 'Houdt de arbeiders strak onder de duim. Verhoogt de opbrengst van het toegewezen productiepand met +25% cash.',
                'benefit_label' => '+25% Cash opbrengst per cyclus',
            ],
            'chemisch_specialist' => [
                'key' => 'chemisch_specialist',
                'name' => 'Dr. Walter V.',
                'role_title' => 'Synthese & Druk Meester',
                'icon' => 'fa-user-gear',
                'cost' => 350000,
                'bonus_type' => 'production_speed',
                'bonus_value' => 30,
                'description' => 'Geniale expert die reactie- en drukcycli versnelt. Verkort de benodigde productietijd met 30%.',
                'benefit_label' => '-30% Productietijd (snellere cyclus)',
            ],
            'enforcer_kapitein' => [
                'key' => 'enforcer_kapitein',
                'name' => 'Boris "De Sloper"',
                'role_title' => 'Zwaarbewapende Enforcer',
                'icon' => 'fa-user-shield',
                'cost' => 500000,
                'bonus_type' => 'defense',
                'bonus_value' => 20,
                'description' => 'Loyale geweldenaar die patrouilleert rond je panden. Geeft +20% extra verdedigingskracht bij vijandige bendeaanvallen.',
                'benefit_label' => '+20% Verdediging bij Bendeoorlog',
            ],
        ];

        // Procedural generator for 100+ unique criminal underworld NPCs
        $firstNames = [
            'Sjaakie', 'Donny', 'Fatima', 'Marco', 'Svetlana', 'Henk', 'Tariq', 'Bob', 'Klaus', 'Reza',
            'Johnny', 'Vincenzo', 'Mo', 'Ferry', 'Anouk', 'Goran', 'Sammy', 'Bram', 'Youssef', 'Viktor',
            'Dave', 'Rachid', 'Nico', 'Selma', 'Jurgen', 'Piet', 'Denzel', 'Olaf', 'Bilal', 'Guus',
            'Marlon', 'Igor', 'Charly', 'Dion', 'Hamza', 'Koen', 'Sergei', 'Niels', 'Timo', 'Mourad',
            'Arjan', 'Danny', 'Luciano', 'Milan', 'Sem', 'Bulent', 'Stijn', 'Wesley', 'Farid', 'Ruud',
        ];

        $nicknames = [
            'Twee Vingers', 'De Turbo', 'Cashflow', 'De Ritselaar', 'IJskoud', 'De Lasser', 'De Spookrijder',
            'Blinde', 'De Chemicus', 'De Banker', 'Gasklep', 'De Boekhouder', 'Snelle', 'De Moker', 'Vlijmscherp',
            'De Slak', 'Pistool', 'De Notaris', 'Nachtbraker', 'De Sloper', 'IJzeren', 'De Spion', 'De Douanier',
            'Schaduw', 'De Fixer', 'De Kolos', 'De Pitbull', 'Gouden Tand', 'Kettingzaag', 'De Kraak', 'De Valk',
            'Rookgordijn', 'De Professor', 'Bliksem', 'Stille', 'Kogelvrij', 'De Schild', 'De Wreker', 'De Adelaar',
            'Kluiskraker', 'De Koerier', 'Tank', 'De Wachter', 'Volgas', 'IJspegel', 'De Meester', 'De Hacker',
        ];

        $specialties = [
            [
                'type' => 'production_yield',
                'role' => 'Productie & Oogst Manager',
                'icon' => 'fa-arrow-trend-up',
                'label' => '+{val}% Cash opbrengst per cyclus',
                'desc' => 'Haalt het maximale rendement uit fabrieken, drukkerijen en kwekerijen.',
                'min' => 15, 'max' => 45, 'cost_base' => 180000,
            ],
            [
                'type' => 'production_speed',
                'role' => 'Proces & Versneller Meester',
                'icon' => 'fa-stopwatch-fast',
                'label' => '-{val}% Productietijd per cyclus',
                'desc' => 'Optimaliseert cycli zodat je bende veel sneller buit kan claimen.',
                'min' => 15, 'max' => 40, 'cost_base' => 220000,
            ],
            [
                'type' => 'defense',
                'role' => 'Zwaarbewapende Bende Enforcer',
                'icon' => 'fa-shield-halved',
                'label' => '+{val}% Verdedigingskracht bij Bendeoorlog',
                'desc' => 'Verdedigt de bende panden en leden tegen vijandige bende-aanvallen.',
                'min' => 12, 'max' => 35, 'cost_base' => 260000,
            ],
            [
                'type' => 'wage_bonus',
                'role' => 'Financieel Adviseur & Witwasser',
                'icon' => 'fa-hand-holding-dollar',
                'label' => '+{val}% Bende Uurloon Bonus',
                'desc' => 'Wast geld wit en keert structureel extra percentages uit op alle bendelonen.',
                'min' => 5, 'max' => 18, 'cost_base' => 300000,
            ],
            [
                'type' => 'vehicle_tuning',
                'role' => 'Fleet Specialist & Meestermonteur',
                'icon' => 'fa-screwdriver-wrench',
                'label' => '-{val}% Voertuig Reparatiekosten in Garage',
                'desc' => 'Onderhoudt bendevoertuigen in topstaat en verlaagt onderhoudskosten aanzienlijk.',
                'min' => 20, 'max' => 50, 'cost_base' => 240000,
            ],
            [
                'type' => 'storage_boost',
                'role' => 'Logistiek Havenmeester',
                'icon' => 'fa-boxes-packing',
                'label' => '+{val} Extra Bende Opslagcapaciteit',
                'desc' => 'Stapelt goederen efficiënter op pallets en vergroot de totale bende opslagruimte.',
                'min' => 25, 'max' => 120, 'cost_base' => 190000,
            ],
        ];

        // Generate 115 distinct characters
        for ($i = 1; $i <= 115; $i++) {
            $fn = $firstNames[($i * 7) % count($firstNames)];
            $nick = $nicknames[($i * 11) % count($nicknames)];
            $spec = $specialties[$i % count($specialties)];

            $key = 'npc_'.strtolower(preg_replace('/[^a-zA-Z0-9]/', '', "{$fn}_{$nick}_{$i}"));
            $val = $spec['min'] + (($i * 3) % ($spec['max'] - $spec['min'] + 1));
            $cost = $spec['cost_base'] + ($val * 8500);

            $label = str_replace('{val}', (string) $val, $spec['label']);
            $fullName = "{$fn} \"{$nick}\"";

            $list[$key] = [
                'key' => $key,
                'name' => $fullName,
                'role_title' => $spec['role'],
                'icon' => $spec['icon'],
                'cost' => (int) (round($cost / 5000) * 5000),
                'bonus_type' => $spec['type'],
                'bonus_value' => $val,
                'description' => $spec['desc'],
                'benefit_label' => $label,
            ];
        }

        $cachedNpcs = $list;

        return $cachedNpcs;
    }

    /**
     * Get single NPC definition.
     */
    public static function getNpc(string $key): ?array
    {
        return self::npcs()[$key] ?? null;
    }

    /**
     * Get 30-minute rotating recruitment offers of 6 unique specialized NPCs.
     *
     * @return array{slot: int, expires_at: int, seconds_left: int, listings: array}
     */
    public static function getRotatingNpcs(): array
    {
        $slotDuration = 1800; // 30 minutes
        $currentTimestamp = time();
        $slot = (int) floor($currentTimestamp / $slotDuration);
        $secondsLeft = $slotDuration - ($currentTimestamp % $slotDuration);
        $expiresAt = ($slot + 1) * $slotDuration;

        $cacheKey = "gang_npcs_rotation_{$slot}";

        $listings = Cache::remember($cacheKey, $secondsLeft + 5, function () use ($slot) {
            $all = collect(self::npcs());
            if ($all->isEmpty()) {
                return [];
            }

            mt_srand($slot * 51517);

            // Shuffled selection of 6 varied NPCs for this half hour
            $shuffled = $all->shuffle();
            $selected = $shuffled->take(6);

            $items = [];
            foreach ($selected->values() as $index => $npc) {
                $token = substr(hash_hmac('sha256', "{$slot}_{$npc['key']}_{$npc['cost']}", config('app.key')), 0, 16);

                $items[] = [
                    'index' => $index,
                    'token' => $token,
                    'npc' => $npc,
                ];
            }

            return $items;
        });

        return [
            'slot' => $slot,
            'expires_at' => $expiresAt,
            'seconds_left' => $secondsLeft,
            'listings' => $listings,
        ];
    }
}
