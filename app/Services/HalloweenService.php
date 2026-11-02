<?php

namespace App\Services;

use App\Models\Character;
use App\Models\HalloweenQuestProgress;
use Carbon\Carbon;
use Illuminate\Database\QueryException;

class HalloweenService
{
    public const EVENT_NAME = '🍂 De Gouden Herfstoogst 2026';

    public const EVENT_END_DATE = '2026-11-30 23:59:59';

    public const DAILY_QUESTS = [
        'daily_theft_3' => [
            'key' => 'daily_theft_3',
            'type' => 'daily',
            'category' => 'theft',
            'title' => 'Vallende Bladeren, Losse Zakken',
            'desc' => 'Voer minimaal 3 geslaagde diefstallen of zakkenrollerijen uit tijdens de herfststorm.',
            'target' => 3,
            'icon' => 'fa-leaf',
            'seeds' => 25,
            'xp' => 60,
            'respect' => 2,
        ],
        'daily_brawl_2' => [
            'key' => 'daily_brawl_2',
            'type' => 'daily',
            'category' => 'brawl',
            'title' => 'Storm op de Luchtplaats',
            'desc' => 'Daag 2 medepatiënten uit op de winderige luchtplaats tussen de dwarrelende herfstbladeren.',
            'target' => 2,
            'icon' => 'fa-wind',
            'seeds' => 30,
            'xp' => 75,
            'respect' => 3,
        ],
        'daily_hustle_5' => [
            'key' => 'daily_hustle_5',
            'type' => 'daily',
            'category' => 'hustle',
            'title' => 'De Oogst Binnenhalen',
            'desc' => 'Voltooi 5 hossels op de afdeling om je wintervoorraad en contanten veilig te stellen.',
            'target' => 5,
            'icon' => 'fa-wheat-awn',
            'seeds' => 25,
            'cash' => 2500,
            'xp' => 60,
        ],
        'daily_training_3' => [
            'key' => 'daily_training_3',
            'type' => 'daily',
            'category' => 'training',
            'title' => 'Houthakkers Krachttraining',
            'desc' => 'Train 3 keer in de fitnessruimte om je lichaam te harden tegen de gure herfstkou.',
            'target' => 3,
            'icon' => 'fa-dumbbell',
            'seeds' => 20,
            'xp' => 50,
        ],
        'daily_vault_1' => [
            'key' => 'daily_vault_1',
            'type' => 'daily',
            'category' => 'vault',
            'title' => 'De Gouden Oogstkluis',
            'desc' => 'Kraak 1 kluis of verzilver een kluiscode voucher voor de grote najaarsbuit.',
            'target' => 1,
            'icon' => 'fa-vault',
            'seeds' => 35,
            'tokens' => 1,
            'xp' => 75,
        ],
    ];

    public const WEEKLY_QUESTS = [
        'weekly_thefts_15' => [
            'key' => 'weekly_thefts_15',
            'type' => 'weekly',
            'category' => 'theft',
            'title' => 'Meester van de Herfstnevel',
            'desc' => 'Voer 15 succesvolle diefstallen of zakkenrollerijen uit in de dichte herfstmist.',
            'target' => 15,
            'icon' => 'fa-sack-dollar',
            'seeds' => 140,
            'xp' => 350,
            'respect' => 8,
        ],
        'weekly_fights_8' => [
            'key' => 'weekly_fights_8',
            'type' => 'weekly',
            'category' => 'brawl_win',
            'title' => 'Heerser van het Stormplein',
            'desc' => 'Win 8 gevechten of berovingen op de herfstige luchtplaats.',
            'target' => 8,
            'icon' => 'fa-fire-flame-curved',
            'seeds' => 160,
            'xp' => 450,
            'respect' => 12,
        ],
        'weekly_cash_35k' => [
            'key' => 'weekly_cash_35k',
            'type' => 'weekly',
            'category' => 'cash_earned',
            'title' => 'De Gouden Oogstkas',
            'desc' => 'Verdien in totaal minimaal €35.000 contant aan buit en smokkelhandel tijdens de herfst.',
            'target' => 35000,
            'icon' => 'fa-coins',
            'seeds' => 200,
            'xp' => 500,
            'respect' => 15,
        ],
        'weekly_hustles_25' => [
            'key' => 'weekly_hustles_25',
            'type' => 'weekly',
            'category' => 'hustle',
            'title' => 'Baronnen van het Najaar',
            'desc' => 'Voer 25 succesvolle hossels of smokkelacties uit voor het herfstkartel.',
            'target' => 25,
            'icon' => 'fa-seedling',
            'seeds' => 150,
            'xp' => 400,
            'respect' => 10,
        ],
    ];

    /**
     * Retired Halloween 2026 items and borders.
     * Kept as permanent collector metadata so owners retain them forever, while new purchases are blocked.
     */
    public const RETIRED_HALLOWEEN_ITEMS = [
        'pumpkin_glow',
        'vampire_blood',
        'phantom_mist',
        'gothic_king',
        'candy_bag',
        'voodoo_doll',
        'pet_bat',
        'witch_brew',
        'pumpkin_mask',
        'grim_blessing',
    ];

    public const SHOP_ITEMS = [
        // Active Autumn 2026 Borders (Haalbaar)
        'autumn_ember' => [
            'key' => 'autumn_ember',
            'type' => 'border',
            'category' => 'borders',
            'name' => '🍂 Vallende Herfstbladeren',
            'description' => 'Omring je profielfoto met een warme koper- en ambergloed en dwarrelende gouden herfstbladeren.',
            'price_seeds' => 120,
            'badge' => 'Haalbaar',
            'obtainable' => true,
            'status_label' => 'Haalbaar',
            'event_name' => '🍂 De Gouden Herfstoogst 2026',
            'icon' => 'fa-leaf',
            'preview_class' => 'ring-4 ring-amber-500 shadow-[0_0_22px_rgba(245,158,11,0.85)] border-orange-400',
        ],
        'harvest_moon' => [
            'key' => 'harvest_moon',
            'type' => 'border',
            'category' => 'borders',
            'name' => '🌕 Bloedrode Oogstmaan',
            'description' => 'Een diepe karmozijnrode en goudgele aura geïnspireerd op de heldere volle oogstmaan boven de kliniek.',
            'price_seeds' => 180,
            'badge' => 'Haalbaar',
            'obtainable' => true,
            'status_label' => 'Haalbaar',
            'event_name' => '🍂 De Gouden Herfstoogst 2026',
            'icon' => 'fa-moon',
            'preview_class' => 'ring-4 ring-orange-600 shadow-[0_0_22px_rgba(234,88,12,0.85)] border-rose-500',
        ],
        'storm_oak' => [
            'key' => 'storm_oak',
            'type' => 'border',
            'category' => 'borders',
            'name' => '🌾 Gouden Stormeik',
            'description' => 'Smaragdgroene bosgloed met bronzen eikenbladeren voor de onverzettelijke overlevers van het najaar.',
            'price_seeds' => 240,
            'badge' => 'Haalbaar',
            'obtainable' => true,
            'status_label' => 'Haalbaar',
            'event_name' => '🍂 De Gouden Herfstoogst 2026',
            'icon' => 'fa-tree',
            'preview_class' => 'ring-4 ring-emerald-500 shadow-[0_0_25px_rgba(16,185,129,0.85)] border-amber-300',
        ],
        'crimson_harvest_king' => [
            'key' => 'crimson_harvest_king',
            'type' => 'border',
            'category' => 'borders',
            'name' => '🍁 Keizer van de Herfstoogst',
            'description' => 'Het ultieme herfst-meesterkader met vurige esdoornbladeren en een koninklijke gouden oogstgloed.',
            'price_seeds' => 350,
            'badge' => 'Haalbaar',
            'obtainable' => true,
            'status_label' => 'Haalbaar',
            'event_name' => '🍂 De Gouden Herfstoogst 2026',
            'icon' => 'fa-crown',
            'preview_class' => 'ring-4 ring-yellow-400 shadow-[0_0_28px_rgba(250,204,21,0.95)] border-orange-300',
        ],

        // Active Autumn 2026 Collectibles
        'spiced_cider' => [
            'key' => 'spiced_cider',
            'type' => 'collectible',
            'category' => 'collectibles',
            'name' => '🍎 Warme Kruidencider',
            'description' => 'Ambachtelijke beker warme appelcider met kaneel en kruidnagel. Geeft bij consumptie direct +35 Energie!',
            'price_seeds' => 40,
            'badge' => 'Oogstdrank',
            'icon' => 'fa-mug-hot',
            'consumable' => true,
        ],
        'golden_acorn_totem' => [
            'key' => 'golden_acorn_totem',
            'type' => 'collectible',
            'category' => 'collectibles',
            'name' => '🌰 Gouden Eikel Trofee',
            'description' => 'Zeldzaam handgesneden eikenhouten beeldje met echt bladgoud. Een waardevol pronkstuk voor in je cel en depot.',
            'price_seeds' => 90,
            'badge' => 'Zeldzaam',
            'icon' => 'fa-award',
            'consumable' => false,
        ],
        'harvest_owl' => [
            'key' => 'harvest_owl',
            'type' => 'collectible',
            'category' => 'collectibles',
            'name' => '🦉 Koperen Bosuil',
            'description' => 'Een majestueuze bosuil die vanuit je celraam over de herfsttuin van de kliniek waakt. Uniek Herfstoogst verzamelobject!',
            'price_seeds' => 160,
            'badge' => 'Metgezel',
            'icon' => 'fa-feather-pointed',
            'consumable' => false,
        ],

        // Active Autumn 2026 Boosts
        'autumn_stew' => [
            'key' => 'autumn_stew',
            'type' => 'boost',
            'category' => 'boosts',
            'name' => '🍲 Krachtige Wildstoofpot',
            'description' => 'Dampende ketel herfststoofpot uit de kliniekkeuken. Herstelt direct 100% Gezondheid en geeft +75 Energie!',
            'price_seeds' => 60,
            'badge' => 'Direct Effect',
            'icon' => 'fa-bowl-food',
        ],
        'mist_cloak_pack' => [
            'key' => 'mist_cloak_pack',
            'type' => 'boost',
            'category' => 'boosts',
            'name' => '🧣 Herfstnevel Maskers (x5)',
            'description' => 'Bundel van 5 donkere herfststorm-maskers om ongezien toe te slaan bij het zakkenrollen en overvallen!',
            'price_seeds' => 85,
            'badge' => '5x Maskers',
            'icon' => 'fa-masks-theater',
        ],
        'harvest_blessing' => [
            'key' => 'harvest_blessing',
            'type' => 'boost',
            'category' => 'boosts',
            'name' => '🌾 Zegening van de Oogst (24 Uur)',
            'description' => 'Ontvang 24 uur lang +15% extra Kracht & Verdediging bij alle gevechten op de luchtplaats!',
            'price_seeds' => 200,
            'badge' => '24u Perk',
            'icon' => 'fa-shield-halved',
        ],
    ];

    /**
     * Get rich configuration and styling tokens for all avatar borders/frames in the game.
     * Includes both active ("Haalbaar") borders and retired collector borders ("Niet meer haalbaar").
     *
     * @return array<string, array{key: string, name: string, emoji: string, badge_title: string, price_seeds: int, badge: string, obtainable: bool, status_label: string, overview_status_label: string, event_key: string, event_name: string, currency_emoji: string, how_to_get: string, description: string, ring: string, border: string, glow: string, badge_gradient: string, preview_class: string}>
     */
    public static function getBorders(): array
    {
        return [
            // =========================================================================
            // 🍂 DE GOUDEN HERFSTOOGST 2026 (ACTIEF EVENT — HAALBAAR)
            // =========================================================================
            'autumn_ember' => [
                'key' => 'autumn_ember',
                'name' => 'Vallende Herfstbladeren',
                'emoji' => '🍂',
                'badge_title' => 'Vallende Herfstbladeren Rand',
                'price_seeds' => 120,
                'badge' => 'Herfstoogst',
                'obtainable' => true,
                'status_label' => 'Haalbaar',
                'overview_status_label' => 'Haalbaar',
                'event_key' => 'autumn_2026',
                'event_name' => '🍂 De Gouden Herfstoogst 2026',
                'currency_emoji' => '🍂',
                'how_to_get' => 'Nu verkrijgbaar in de Herfstoogst Winkel voor 120 Gouden Eikels (🍂).',
                'description' => 'Omring je profielfoto met een warme koper- en ambergloed en dwarrelende gouden herfstbladeren.',
                'ring' => 'ring-4 ring-amber-500 ring-offset-2 ring-offset-slate-900',
                'border' => 'border-orange-400',
                'glow' => 'shadow-[0_0_25px_rgba(245,158,11,0.9)]',
                'badge_gradient' => 'from-amber-500 via-orange-500 to-amber-700',
                'preview_class' => 'ring-4 ring-amber-500 shadow-[0_0_22px_rgba(245,158,11,0.85)] border-orange-400',
            ],
            'harvest_moon' => [
                'key' => 'harvest_moon',
                'name' => 'Bloedrode Oogstmaan',
                'emoji' => '🌕',
                'badge_title' => 'Bloedrode Oogstmaan Rand',
                'price_seeds' => 180,
                'badge' => 'Oogstmaan',
                'obtainable' => true,
                'status_label' => 'Haalbaar',
                'overview_status_label' => 'Haalbaar',
                'event_key' => 'autumn_2026',
                'event_name' => '🍂 De Gouden Herfstoogst 2026',
                'currency_emoji' => '🍂',
                'how_to_get' => 'Nu verkrijgbaar in de Herfstoogst Winkel voor 180 Gouden Eikels (🍂).',
                'description' => 'Een diepe karmozijnrode en goudgele aura geïnspireerd op de heldere volle oogstmaan boven de kliniek.',
                'ring' => 'ring-4 ring-orange-600 ring-offset-2 ring-offset-slate-900',
                'border' => 'border-rose-500',
                'glow' => 'shadow-[0_0_25px_rgba(234,88,12,0.9)]',
                'badge_gradient' => 'from-orange-500 via-rose-600 to-amber-800',
                'preview_class' => 'ring-4 ring-orange-600 shadow-[0_0_22px_rgba(234,88,12,0.85)] border-rose-500',
            ],
            'storm_oak' => [
                'key' => 'storm_oak',
                'name' => 'Gouden Stormeik',
                'emoji' => '🌾',
                'badge_title' => 'Gouden Stormeik Rand',
                'price_seeds' => 240,
                'badge' => 'Geanimeerd',
                'obtainable' => true,
                'status_label' => 'Haalbaar',
                'overview_status_label' => 'Haalbaar',
                'event_key' => 'autumn_2026',
                'event_name' => '🍂 De Gouden Herfstoogst 2026',
                'currency_emoji' => '🍂',
                'how_to_get' => 'Nu verkrijgbaar in de Herfstoogst Winkel voor 240 Gouden Eikels (🍂).',
                'description' => 'Smaragdgroene bosgloed met bronzen eikenbladeren voor de onverzettelijke overlevers van het najaar.',
                'ring' => 'ring-4 ring-emerald-500 ring-offset-2 ring-offset-slate-900',
                'border' => 'border-amber-300',
                'glow' => 'shadow-[0_0_28px_rgba(16,185,129,0.9)]',
                'badge_gradient' => 'from-emerald-500 via-teal-600 to-amber-600',
                'preview_class' => 'ring-4 ring-emerald-500 shadow-[0_0_25px_rgba(16,185,129,0.85)] border-amber-300',
            ],
            'crimson_harvest_king' => [
                'key' => 'crimson_harvest_king',
                'name' => 'Keizer van de Herfstoogst',
                'emoji' => '🍁',
                'badge_title' => 'Keizer van de Herfstoogst Rand',
                'price_seeds' => 350,
                'badge' => 'Legendarisch',
                'obtainable' => true,
                'status_label' => 'Haalbaar',
                'overview_status_label' => 'Haalbaar',
                'event_key' => 'autumn_2026',
                'event_name' => '🍂 De Gouden Herfstoogst 2026',
                'currency_emoji' => '🍂',
                'how_to_get' => 'Nu verkrijgbaar in de Herfstoogst Winkel voor 350 Gouden Eikels (🍂).',
                'description' => 'Het ultieme herfst-meesterkader met vurige esdoornbladeren en een koninklijke gouden oogstgloed.',
                'ring' => 'ring-4 ring-yellow-400 ring-offset-2 ring-offset-slate-900',
                'border' => 'border-orange-300',
                'glow' => 'shadow-[0_0_30px_rgba(250,204,21,0.95)]',
                'badge_gradient' => 'from-yellow-400 via-amber-500 to-red-600',
                'preview_class' => 'ring-4 ring-yellow-400 shadow-[0_0_28px_rgba(250,204,21,0.95)] border-orange-300',
            ],

            // =========================================================================
            // 🎃 HALLOWEEN EVENT 2026 (AFGELOPEN — NIET MEER HAALBAAR / VERZAMELOBJECT)
            // =========================================================================
            'pumpkin_glow' => [
                'key' => 'pumpkin_glow',
                'name' => 'Spooky Pumpkin Glow',
                'emoji' => '🎃',
                'badge_title' => 'Halloween Pompoen Gloed',
                'price_seeds' => 120,
                'badge' => 'Verzamelobject',
                'obtainable' => false,
                'status_label' => 'Niet meer haalbaar',
                'overview_status_label' => 'Niet haalbaar',
                'event_key' => 'halloween_2026',
                'event_name' => '🎃 Halloween Event 2026',
                'currency_emoji' => '🎃',
                'how_to_get' => 'Exclusief verzamelobject uit het Halloween Event 2026 — Niet meer haalbaar voor nieuwe spelers.',
                'description' => 'Verlicht je profiel met een vurige neon-oranje pompoen aura en gloeiend rand-effect.',
                'ring' => 'ring-4 ring-orange-500 ring-offset-2 ring-offset-slate-900',
                'border' => 'border-orange-400',
                'glow' => 'shadow-[0_0_25px_rgba(249,115,22,0.9)]',
                'badge_gradient' => 'from-amber-400 via-orange-500 to-amber-600',
                'preview_class' => 'ring-4 ring-orange-500 shadow-[0_0_22px_rgba(249,115,22,0.85)] border-amber-400',
            ],
            'vampire_blood' => [
                'key' => 'vampire_blood',
                'name' => 'Bloederige Vampier',
                'emoji' => '🩸',
                'badge_title' => 'Bloederige Vampier Rand',
                'price_seeds' => 180,
                'badge' => 'Verzamelobject',
                'obtainable' => false,
                'status_label' => 'Niet meer haalbaar',
                'overview_status_label' => 'Niet haalbaar',
                'event_key' => 'halloween_2026',
                'event_name' => '🎃 Halloween Event 2026',
                'currency_emoji' => '🎃',
                'how_to_get' => 'Exclusief verzamelobject uit het Halloween Event 2026 — Niet meer haalbaar voor nieuwe spelers.',
                'description' => 'Duistere karmijnrode bloedspatten en een angstaanjagende vampieren gloed rondom je avatar.',
                'ring' => 'ring-4 ring-rose-700 ring-offset-2 ring-offset-slate-900',
                'border' => 'border-rose-600',
                'glow' => 'shadow-[0_0_25px_rgba(225,29,72,0.9)]',
                'badge_gradient' => 'from-rose-600 via-red-700 to-rose-950',
                'preview_class' => 'ring-4 ring-rose-700 shadow-[0_0_22px_rgba(225,29,72,0.85)] border-rose-900',
            ],
            'phantom_mist' => [
                'key' => 'phantom_mist',
                'name' => 'Spookhuis Paarse Mist',
                'emoji' => '👻',
                'badge_title' => 'Spookhuis Paarse Mist Rand',
                'price_seeds' => 220,
                'badge' => 'Verzamelobject',
                'obtainable' => false,
                'status_label' => 'Niet meer haalbaar',
                'overview_status_label' => 'Niet haalbaar',
                'event_key' => 'halloween_2026',
                'event_name' => '🎃 Halloween Event 2026',
                'currency_emoji' => '🎃',
                'how_to_get' => 'Exclusief verzamelobject uit het Halloween Event 2026 — Niet meer haalbaar voor nieuwe spelers.',
                'description' => 'Geanimeerde ectoplasma nevel met oplichtende spookogen rondom je avatar.',
                'ring' => 'ring-4 ring-purple-500 ring-offset-2 ring-offset-slate-900',
                'border' => 'border-purple-300',
                'glow' => 'shadow-[0_0_28px_rgba(168,85,247,0.9)]',
                'badge_gradient' => 'from-purple-500 via-indigo-600 to-purple-900',
                'preview_class' => 'ring-4 ring-purple-500 shadow-[0_0_25px_rgba(168,85,247,0.85)] border-purple-300',
            ],
            'gothic_king' => [
                'key' => 'gothic_king',
                'name' => 'Magere Hein Kroon',
                'emoji' => '👑',
                'badge_title' => 'Magere Hein Kroon Rand',
                'price_seeds' => 350,
                'badge' => 'Verzamelobject',
                'obtainable' => false,
                'status_label' => 'Niet meer haalbaar',
                'overview_status_label' => 'Niet haalbaar',
                'event_key' => 'halloween_2026',
                'event_name' => '🎃 Halloween Event 2026',
                'currency_emoji' => '🎃',
                'how_to_get' => 'Exclusief verzamelobject uit het Halloween Event 2026 — Niet meer haalbaar voor nieuwe spelers.',
                'description' => 'Het ultieme Halloween meester-frame met gouden skeletvleugels en sinistere kroongloed.',
                'ring' => 'ring-4 ring-amber-400 ring-offset-2 ring-offset-slate-900',
                'border' => 'border-amber-200',
                'glow' => 'shadow-[0_0_30px_rgba(251,191,36,0.95)]',
                'badge_gradient' => 'from-amber-300 via-yellow-400 to-amber-600',
                'preview_class' => 'ring-4 ring-amber-400 shadow-[0_0_25px_rgba(251,191,36,0.9)] border-amber-200',
            ],
        ];
    }

    /**
     * Get only borders that are currently obtainable ("Haalbaar").
     *
     * @return array<string, array<string, mixed>>
     */
    public static function getObtainableBorders(): array
    {
        return array_filter(self::getBorders(), fn (array $b): bool => (bool) ($b['obtainable'] ?? false));
    }

    /**
     * Get only borders that are retired ("Niet meer haalbaar").
     *
     * @return array<string, array<string, mixed>>
     */
    public static function getRetiredBorders(): array
    {
        return array_filter(self::getBorders(), fn (array $b): bool => ! ($b['obtainable'] ?? false));
    }

    /**
     * Check if the Halloween event is currently running.
     */
    public static function isEventActive(): bool
    {
        return now()->lte(Carbon::parse(self::EVENT_END_DATE));
    }

    /**
     * Get remaining days, hours, and minutes until event ends.
     *
     * @return array{days: int, hours: int, minutes: int, formatted: string}
     */
    public static function getRemainingTime(): array
    {
        $end = Carbon::parse(self::EVENT_END_DATE);
        if (now()->gte($end)) {
            return ['days' => 0, 'hours' => 0, 'minutes' => 0, 'formatted' => 'Afgelopen'];
        }

        $diff = now()->diff($end);

        return [
            'days' => $diff->d,
            'hours' => $diff->h,
            'minutes' => $diff->i,
            'formatted' => "{$diff->d}d {$diff->h}u {$diff->i}m",
        ];
    }

    /**
     * Record player activity against daily and weekly Halloween quests.
     */
    public static function record(Character $character, string $category, int $count = 1): void
    {
        if ($count <= 0 || ! self::isEventActive()) {
            return;
        }

        $todayKey = 'daily_'.now()->toDateString();
        $weekKey = 'weekly_'.now()->format('o-W');

        // Check daily quests
        foreach (self::DAILY_QUESTS as $quest) {
            if ($quest['category'] === $category) {
                self::incrementProgress($character, $quest, $todayKey, $count);
            }
        }

        // Check weekly quests
        foreach (self::WEEKLY_QUESTS as $quest) {
            if ($quest['category'] === $category) {
                self::incrementProgress($character, $quest, $weekKey, $count);
            }
        }
    }

    /**
     * Increment progress for a quest period.
     *
     * @param  array<string, mixed>  $quest
     */
    private static function incrementProgress(Character $character, array $quest, string $periodKey, int $amount): void
    {
        if ($amount <= 0) {
            return;
        }

        try {
            $record = HalloweenQuestProgress::firstOrCreate([
                'character_id' => $character->id,
                'quest_key' => $quest['key'],
                'period_key' => $periodKey,
            ], [
                'current_count' => 0,
                'target_count' => $quest['target'],
                'is_claimed' => false,
            ]);
        } catch (QueryException) {
            $record = HalloweenQuestProgress::where('character_id', $character->id)
                ->where('quest_key', $quest['key'])
                ->where('period_key', $periodKey)
                ->first();
        }

        if ($record && ! $record->is_claimed) {
            $record->current_count = min($quest['target'], $record->current_count + $amount);
            $record->save();
        }
    }

    /**
     * Get all active quests for a character with their current progress and claimed status.
     *
     * @return array{daily: list<array<string, mixed>>, weekly: list<array<string, mixed>>}
     */
    public static function getCharacterQuests(Character $character): array
    {
        $todayKey = 'daily_'.now()->toDateString();
        $weekKey = 'weekly_'.now()->format('o-W');

        $dailyProgress = HalloweenQuestProgress::where('character_id', $character->id)
            ->where('period_key', $todayKey)
            ->get()
            ->keyBy('quest_key');

        $weeklyProgress = HalloweenQuestProgress::where('character_id', $character->id)
            ->where('period_key', $weekKey)
            ->get()
            ->keyBy('quest_key');

        $daily = [];
        foreach (self::DAILY_QUESTS as $q) {
            $prog = $dailyProgress->get($q['key']);
            $current = $prog ? $prog->current_count : 0;
            $isClaimed = $prog ? $prog->is_claimed : false;
            $isComplete = $current >= $q['target'];
            $pct = min(100, (int) round(($current / max(1, $q['target'])) * 100));

            $daily[] = array_merge($q, [
                'current' => $current,
                'is_completed' => $isComplete,
                'is_claimed' => $isClaimed,
                'can_claim' => $isComplete && ! $isClaimed,
                'percent' => $pct,
            ]);
        }

        $weekly = [];
        foreach (self::WEEKLY_QUESTS as $q) {
            $prog = $weeklyProgress->get($q['key']);
            $current = $prog ? $prog->current_count : 0;
            $isClaimed = $prog ? $prog->is_claimed : false;
            $isComplete = $current >= $q['target'];
            $pct = min(100, (int) round(($current / max(1, $q['target'])) * 100));

            $weekly[] = array_merge($q, [
                'current' => $current,
                'is_completed' => $isComplete,
                'is_claimed' => $isClaimed,
                'can_claim' => $isComplete && ! $isClaimed,
                'percent' => $pct,
            ]);
        }

        return [
            'daily' => $daily,
            'weekly' => $weekly,
        ];
    }
}
