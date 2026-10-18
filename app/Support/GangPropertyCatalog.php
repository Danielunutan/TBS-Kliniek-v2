<?php

namespace App\Support;

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
            // 1. OPSLAG & STALLING
            // ==========================================
            'kleine_loods' => [
                'key' => 'kleine_loods',
                'name' => 'Kleine Opslagloods & Garage',
                'category' => 'storage',
                'category_label' => 'Opslag & Stalling',
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
            'industrieele_loods' => [
                'key' => 'industrieele_loods',
                'name' => 'Industriële Distributieloods',
                'category' => 'storage',
                'category_label' => 'Opslag & Stalling',
                'icon' => 'fa-boxes-stacked',
                'color' => 'blue',
                'price' => 500000,
                'storage_capacity' => 150,
                'vehicle_slots' => 5,
                'npc_capacity' => 0,
                'production_duration_minutes' => 0,
                'production_reward_cash' => 0,
                'production_reward_respect' => 0,
                'tagline' => 'Forse opslagruimte & wagenpark stalling',
                'description' => 'Grote loods met roldeuren en palletstellingen. Biedt 150 opslagplaatsen voor buit en plaats voor 5 zware voertuigen van bendeleden.',
                'perks_summary' => '+150 Bende Opslag • 5 Voertuigplekken',
            ],
            'ondergrondse_bunker' => [
                'key' => 'ondergrondse_bunker',
                'name' => 'Versterkte Ondergrondse Bunker',
                'category' => 'storage',
                'category_label' => 'Opslag & Stalling',
                'icon' => 'fa-vault',
                'color' => 'slate',
                'price' => 1500000,
                'storage_capacity' => 350,
                'vehicle_slots' => 10,
                'npc_capacity' => 0,
                'production_duration_minutes' => 0,
                'production_reward_cash' => 0,
                'production_reward_respect' => 0,
                'tagline' => 'Ondoordringbare kluisbunker',
                'description' => 'Militaire atoomschuilkelder onder de kliniekvleugel. Biedt maximale beveiliging tegen invallen, 350 opslagplaatsen en 10 zware wagenplekken.',
                'perks_summary' => '+350 Bende Opslag • 10 Voertuigplekken • Verdedigingsbonus',
            ],

            // ==========================================
            // 2. PRODUCTIE & FABRIEKEN
            // ==========================================
            'valsgeld_drukkerij' => [
                'key' => 'valsgeld_drukkerij',
                'name' => 'Ondergrondse Valsgeld Drukkerij',
                'category' => 'production',
                'category_label' => 'Productie & Fabriek',
                'icon' => 'fa-money-bill-transfer',
                'color' => 'emerald',
                'price' => 750000,
                'storage_capacity' => 0,
                'vehicle_slots' => 0,
                'npc_capacity' => 0,
                'production_duration_minutes' => 120, // 2 uur
                'production_reward_cash' => 75000,
                'production_reward_respect' => 30,
                'tagline' => 'Drukt stapels zwarte KliniekCash',
                'description' => 'Professionele offset drukpersen in een geluiddichte kelder. Leiders kunnen periodiek een drukcyclus starten om €75.000 en 30 Respect voor de bende te produceren.',
                'perks_summary' => 'Productie: €75.000 Cash + 30 Respect (per 2 uur run)',
            ],
            'chemisch_lab' => [
                'key' => 'chemisch_lab',
                'name' => 'Geheim Chemisch Smokkel-Lab',
                'category' => 'production',
                'category_label' => 'Productie & Fabriek',
                'icon' => 'fa-flask-vial',
                'color' => 'cyan',
                'price' => 1500000,
                'storage_capacity' => 0,
                'vehicle_slots' => 0,
                'npc_capacity' => 0,
                'production_duration_minutes' => 240, // 4 uur
                'production_reward_cash' => 180000,
                'production_reward_respect' => 65,
                'tagline' => 'Grootschalige smokkelwaar synthese',
                'description' => 'Geavanceerde kolven, distillatievaten en chemische reactoren. Levert bij elke voltooide productiecyclus €180.000 in de bende treasury op en 65 Respect.',
                'perks_summary' => 'Productie: €180.000 Cash + 65 Respect (per 4 uur run)',
            ],
            'wapenfabriek' => [
                'key' => 'wapenfabriek',
                'name' => 'Verborgen Wapenfabriek & Smelterij',
                'category' => 'production',
                'category_label' => 'Productie & Fabriek',
                'icon' => 'fa-gun',
                'color' => 'rose',
                'price' => 3000000,
                'storage_capacity' => 0,
                'vehicle_slots' => 0,
                'npc_capacity' => 0,
                'production_duration_minutes' => 360, // 6 uur
                'production_reward_cash' => 400000,
                'production_reward_respect' => 150,
                'tagline' => 'Zware vuurwapens & munitie fabricage',
                'description' => 'CNC-freesmachines en draaibanken voor de assemblage van hoogwaardige vuurwapens en munitie. Levert maar liefst €400.000 en 150 Respect per cyclus op.',
                'perks_summary' => 'Productie: €400.000 Cash + 150 Respect (per 6 uur run)',
            ],

            // ==========================================
            // 3. LUXE PANDEN & VILLA'S
            // ==========================================
            'luxe_penthouse' => [
                'key' => 'luxe_penthouse',
                'name' => 'Luxe Sky-Penthouse HQ',
                'category' => 'luxury',
                'category_label' => 'Luxe Pand & Villa',
                'icon' => 'fa-city',
                'color' => 'purple',
                'price' => 2500000,
                'storage_capacity' => 50,
                'vehicle_slots' => 2,
                'npc_capacity' => 3,
                'production_duration_minutes' => 0,
                'production_reward_cash' => 0,
                'production_reward_respect' => 75,
                'wage_bonus_pct' => 10,
                'tagline' => 'Hoofdkwartier met vertrekken voor 3 NPC Rekruten',
                'description' => 'Exclusief penthouse op de bovenste verdieping van de kliniek. Biedt onderdak aan 3 specialistische NPC bendeleden en geeft +10% bonus op alle collectieve bendelonen.',
                'perks_summary' => '3 NPC Rekruten Plekken • +10% Bende Uurloon • 75 Respect',
            ],
            'gang_villa' => [
                'key' => 'gang_villa',
                'name' => 'Zwaarbeveiligd Landgoed & Gang Villa',
                'category' => 'luxury',
                'category_label' => 'Luxe Pand & Villa',
                'icon' => 'fa-landmark-dome',
                'color' => 'amber',
                'price' => 6000000,
                'storage_capacity' => 150,
                'vehicle_slots' => 6,
                'npc_capacity' => 8,
                'production_duration_minutes' => 0,
                'production_reward_cash' => 0,
                'production_reward_respect' => 250,
                'wage_bonus_pct' => 25,
                'tagline' => 'Het ultieme cartel hoofdkwartier',
                'description' => 'Imposant zwaarbeveiligd landgoed met helikopterplatform en commandocentrale. Biedt plaats aan 8 NPC bendeleden, +25% bonus op bendelonen en 250 Respect.',
                'perks_summary' => '8 NPC Rekruten Plekken • +25% Bende Uurloon • 250 Respect',
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
     * Available NPC recruits catalog.
     *
     * @return array<string, array<string, mixed>>
     */
    public static function npcs(): array
    {
        return [
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
                'description' => 'Loyale geweldenaar die patrouilleert rond je panden. Geeft +20% extra verdedigingskracht bij vijandige bendeaanvallen en levert 50 Respect op.',
                'benefit_label' => '+20% Verdediging bij Bendeoorlog',
            ],
        ];
    }
}
