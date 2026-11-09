<?php

namespace App\Support;

class CellUpgradeCatalog
{
    /**
     * Complete catalog of Cell Upgrades for player quarters.
     * High-tier prestige money sinks designed for end-game players.
     */
    public const UPGRADES = [
        'bed' => [
            'key' => 'bed',
            'name' => 'Luxe Boxspring & Traagschuim Matras',
            'category' => 'Energie & Herstel',
            'icon' => 'fa-bed',
            'color' => 'indigo',
            'description' => 'Verbeter je nachtrust en celrust drastisch. Een hoogwaardig matras zorgt voor diepere ontspanning waardoor je energie veel sneller regenereert.',
            'max_level' => 5,
            'tiers' => [
                1 => ['cost' => 500000, 'benefit' => '+8% Sneller energieherstel', 'reduction' => 0.08],
                2 => ['cost' => 1500000, 'benefit' => '+16% Sneller energieherstel', 'reduction' => 0.16],
                3 => ['cost' => 4000000, 'benefit' => '+24% Sneller energieherstel', 'reduction' => 0.24],
                4 => ['cost' => 10000000, 'benefit' => '+32% Sneller energieherstel', 'reduction' => 0.32],
                5 => ['cost' => 25000000, 'benefit' => '+40% Sneller energieherstel', 'reduction' => 0.40],
            ],
        ],
        'door' => [
            'key' => 'door',
            'name' => 'Versterkte Stalen Deur & Camerasysteem',
            'category' => 'Verdediging & Veiligheid',
            'icon' => 'fa-shield-halved',
            'color' => 'rose',
            'description' => 'Versterk je cel met gepantserde stalen platen, zware grendels en een privécamerasysteem. Biedt directe passieve verdediging tegen aanvallen en overvallen.',
            'max_level' => 5,
            'tiers' => [
                1 => ['cost' => 750000, 'benefit' => '+100 Passieve celverdediging', 'defense' => 100],
                2 => ['cost' => 2500000, 'benefit' => '+250 Passieve celverdediging', 'defense' => 250],
                3 => ['cost' => 6000000, 'benefit' => '+500 Passieve celverdediging', 'defense' => 500],
                4 => ['cost' => 15000000, 'benefit' => '+1.000 Passieve celverdediging', 'defense' => 1000],
                5 => ['cost' => 35000000, 'benefit' => '+2.000 Passieve celverdediging', 'defense' => 2000],
            ],
        ],
        'safe' => [
            'key' => 'safe',
            'name' => 'Verborgen Vloerkluis & Geheim Luik',
            'category' => 'Vermogensbescherming',
            'icon' => 'fa-vault',
            'color' => 'emerald',
            'description' => 'Een meesterlijk gecamoufleerde kluis onder de vloertegels van je cel. Beschermt een aanzienlijk deel van je contante zakgeld tegen verlies bij gevechten.',
            'max_level' => 5,
            'tiers' => [
                1 => ['cost' => 500000, 'benefit' => '10% van contant zakgeld beschermd tegen buit', 'protection' => 10],
                2 => ['cost' => 2000000, 'benefit' => '20% van contant zakgeld beschermd tegen buit', 'protection' => 20],
                3 => ['cost' => 5000000, 'benefit' => '30% van contant zakgeld beschermd tegen buit', 'protection' => 30],
                4 => ['cost' => 12500000, 'benefit' => '40% van contant zakgeld beschermd tegen buit', 'protection' => 40],
                5 => ['cost' => 30000000, 'benefit' => '50% van contant zakgeld beschermd tegen buit', 'protection' => 50],
            ],
        ],
        'comfort' => [
            'key' => 'comfort',
            'name' => 'Luxe Lounge, Smart TV & Geluidsset',
            'category' => 'Comfort & Aanzien',
            'icon' => 'fa-tv',
            'color' => 'amber',
            'description' => 'Transformeer je kale cel tot een vorstelijke penthouse suite met 4K flatscreen, surround sound en fluwelen fauteuils. Geeft bonus respect en ervaringspunten.',
            'max_level' => 5,
            'tiers' => [
                1 => ['cost' => 1000000, 'benefit' => '+5% Bonus XP & Respect bij acties', 'bonus_pct' => 5],
                2 => ['cost' => 3000000, 'benefit' => '+10% Bonus XP & Respect bij acties', 'bonus_pct' => 10],
                3 => ['cost' => 7500000, 'benefit' => '+15% Bonus XP & Respect bij acties', 'bonus_pct' => 15],
                4 => ['cost' => 18000000, 'benefit' => '+20% Bonus XP & Respect bij acties', 'bonus_pct' => 20],
                5 => ['cost' => 40000000, 'benefit' => '+25% Bonus XP & Respect bij acties', 'bonus_pct' => 25],
            ],
        ],
    ];

    /**
     * Get all upgrades.
     *
     * @return array<string, array<string, mixed>>
     */
    public static function all(): array
    {
        return self::UPGRADES;
    }

    /**
     * Get a specific upgrade definition.
     *
     * @return array<string, mixed>|null
     */
    public static function get(string $key): ?array
    {
        return self::UPGRADES[$key] ?? null;
    }

    /**
     * Get next tier details for an upgrade.
     *
     * @return array<string, mixed>|null
     */
    public static function getNextTier(string $key, int $currentLevel): ?array
    {
        $upgrade = self::get($key);
        if (! $upgrade) {
            return null;
        }

        $nextLevel = $currentLevel + 1;
        if ($nextLevel > ($upgrade['max_level'] ?? 5)) {
            return null;
        }

        $tierData = $upgrade['tiers'][$nextLevel] ?? null;
        if (! $tierData) {
            return null;
        }

        return array_merge($tierData, ['level' => $nextLevel]);
    }
}
