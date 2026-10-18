<?php

namespace App\Services;

use App\Models\Character;
use App\Models\HalloweenQuestProgress;
use Carbon\Carbon;

class HalloweenService
{
    public const EVENT_END_DATE = '2026-11-05 23:59:59';

    public const DAILY_QUESTS = [
        'daily_theft_3' => [
            'key' => 'daily_theft_3',
            'type' => 'daily',
            'category' => 'theft',
            'title' => 'Zakkenroller Spooktocht',
            'desc' => 'Voer minimaal 3 geslaagde diefstallen of zakkenrollerijen uit.',
            'target' => 3,
            'icon' => 'fa-mask',
            'seeds' => 25,
            'xp' => 50,
            'respect' => 1,
        ],
        'daily_brawl_2' => [
            'key' => 'daily_brawl_2',
            'type' => 'daily',
            'category' => 'brawl',
            'title' => 'Luchtplaats Terreur',
            'desc' => 'Val 2 medepatiënten aan op de luchtplaats om angst te zaaien.',
            'target' => 2,
            'icon' => 'fa-skull-crossbones',
            'seeds' => 30,
            'xp' => 60,
            'respect' => 2,
        ],
        'daily_hustle_5' => [
            'key' => 'daily_hustle_5',
            'type' => 'daily',
            'category' => 'hustle',
            'title' => 'Spookachtige Hossels',
            'desc' => 'Voltooi 5 hossels op de afdeling om contanten binnen te halen.',
            'target' => 5,
            'icon' => 'fa-handshake',
            'seeds' => 25,
            'cash' => 1500,
            'xp' => 45,
        ],
        'daily_training_3' => [
            'key' => 'daily_training_3',
            'type' => 'daily',
            'category' => 'training',
            'title' => 'Zombie Krachttraining',
            'desc' => 'Train 3 keer in de fitnessruimte om je spieren op te pompen.',
            'target' => 3,
            'icon' => 'fa-dumbbell',
            'seeds' => 20,
            'xp' => 40,
        ],
        'daily_vault_1' => [
            'key' => 'daily_vault_1',
            'type' => 'daily',
            'category' => 'vault',
            'title' => 'Nachtelijke Kluisjacht',
            'desc' => 'Kraak 1 kluis of vind een kluiscode voucher in de kliniek.',
            'target' => 1,
            'icon' => 'fa-vault',
            'seeds' => 35,
            'tokens' => 1,
            'xp' => 50,
        ],
    ];

    public const WEEKLY_QUESTS = [
        'weekly_thefts_15' => [
            'key' => 'weekly_thefts_15',
            'type' => 'weekly',
            'category' => 'theft',
            'title' => 'Griezelige Zakkenroller Meester',
            'desc' => 'Voer 15 succesvolle diefstallen of zakkenrollerijen uit.',
            'target' => 15,
            'icon' => 'fa-sack-dollar',
            'seeds' => 120,
            'xp' => 250,
            'respect' => 5,
        ],
        'weekly_fights_8' => [
            'key' => 'weekly_fights_8',
            'type' => 'weekly',
            'category' => 'brawl_win',
            'title' => 'Het Grote Kliniek Bloedbad',
            'desc' => 'Win 8 gevechten of berovingen op de luchtplaats.',
            'target' => 8,
            'icon' => 'fa-fire-flame-curved',
            'seeds' => 150,
            'xp' => 350,
            'respect' => 10,
        ],
        'weekly_cash_35k' => [
            'key' => 'weekly_cash_35k',
            'type' => 'weekly',
            'category' => 'cash_earned',
            'title' => 'Het Grote Pompoen Fortuin',
            'desc' => 'Verdien in totaal minimaal €35.000 contant aan buit of misdaad.',
            'target' => 35000,
            'icon' => 'fa-coins',
            'seeds' => 180,
            'xp' => 400,
            'respect' => 15,
        ],
        'weekly_hustles_25' => [
            'key' => 'weekly_hustles_25',
            'type' => 'weekly',
            'category' => 'hustle',
            'title' => 'Heerser van de Onderwereld',
            'desc' => 'Voer 25 succesvolle hossels of smokkelacties uit.',
            'target' => 25,
            'icon' => 'fa-ghost',
            'seeds' => 140,
            'xp' => 300,
            'respect' => 8,
        ],
    ];

    public const SHOP_ITEMS = [
        // Borders
        'pumpkin_glow' => [
            'key' => 'pumpkin_glow',
            'type' => 'border',
            'category' => 'borders',
            'name' => '🎃 Spooky Pumpkin Glow',
            'description' => 'Verlicht je profiel met een vurige neon-oranje pompoen aura en gloeiend rand-effect.',
            'price_seeds' => 120,
            'badge' => 'Exclusief',
            'icon' => 'fa-jack-o-lantern',
            'preview_class' => 'ring-4 ring-orange-500 shadow-[0_0_22px_rgba(249,115,22,0.85)] border-amber-400',
        ],
        'vampire_blood' => [
            'key' => 'vampire_blood',
            'type' => 'border',
            'category' => 'borders',
            'name' => '🩸 Bloederige Vampier Rand',
            'description' => 'Duistere karmijnrode bloedspatten en een angstaanjagende vampieren gloed rondom je avatar.',
            'price_seeds' => 180,
            'badge' => 'Gothic',
            'icon' => 'fa-droplet',
            'preview_class' => 'ring-4 ring-rose-700 shadow-[0_0_22px_rgba(225,29,72,0.85)] border-rose-900',
        ],
        'phantom_mist' => [
            'key' => 'phantom_mist',
            'type' => 'border',
            'category' => 'borders',
            'name' => '👻 Spookhuis Paarse Mist',
            'description' => 'Geanimeerde ectoplasma nevel met oplichtende spookogen rondom je avatar.',
            'price_seeds' => 220,
            'badge' => 'Geanimeerd',
            'icon' => 'fa-ghost',
            'preview_class' => 'ring-4 ring-purple-500 shadow-[0_0_25px_rgba(168,85,247,0.85)] border-purple-300',
        ],
        'gothic_king' => [
            'key' => 'gothic_king',
            'type' => 'border',
            'category' => 'borders',
            'name' => '👑 Magere Hein Kroon Rand',
            'description' => 'Het ultieme Halloween meester-frame met gouden skeletvleugels en sinistere kroongloed.',
            'price_seeds' => 350,
            'badge' => 'Legendarisch',
            'icon' => 'fa-crown',
            'preview_class' => 'ring-4 ring-amber-400 shadow-[0_0_25px_rgba(251,191,36,0.9)] border-amber-200',
        ],

        // Collectibles
        'candy_bag' => [
            'key' => 'candy_bag',
            'type' => 'collectible',
            'category' => 'collectibles',
            'name' => '🍬 Snoep of Je Leven Zakje',
            'description' => 'Griezelig snoepzakje vol lekkers. Geeft bij consumptie direct +35 Energie!',
            'price_seeds' => 40,
            'badge' => 'Snoepgoed',
            'icon' => 'fa-candy-cane',
            'consumable' => true,
        ],
        'voodoo_doll' => [
            'key' => 'voodoo_doll',
            'type' => 'collectible',
            'category' => 'collectibles',
            'name' => '🕯️ Behekste Voodoo Pop',
            'description' => 'Zeldzame handgemaakte pop vol naalden om rivalen op de gangen mee te vervloeken.',
            'price_seeds' => 90,
            'badge' => 'Zeldzaam',
            'icon' => 'fa-wand-magic-sparkles',
            'consumable' => false,
        ],
        'pet_bat' => [
            'key' => 'pet_bat',
            'type' => 'collectible',
            'category' => 'collectibles',
            'name' => '🦇 Vampier Vleermuis in Kooitje',
            'description' => 'Een levende vleermuis die rusteloos fladdert in een ijzeren celkooitje. Uniek Halloween pronkstuk!',
            'price_seeds' => 160,
            'badge' => 'Huisdier',
            'icon' => 'fa-crow',
            'consumable' => false,
        ],

        // Boosts
        'witch_brew' => [
            'key' => 'witch_brew',
            'type' => 'boost',
            'category' => 'boosts',
            'name' => '🧪 Groen Heksenbrouwsel',
            'description' => 'Borrelend elixer uit de heksenketel. Herstelt direct 100% Gezondheid en geeft +75 Energie!',
            'price_seeds' => 60,
            'badge' => 'Direct Effect',
            'icon' => 'fa-flask-vial',
        ],
        'pumpkin_mask' => [
            'key' => 'pumpkin_mask',
            'type' => 'boost',
            'category' => 'boosts',
            'name' => '🎭 Jack-O\'-Lantern Maskers (x5)',
            'description' => 'Bundel van 5 exclusieve Halloween maskers voor gegarandeerd ongemerkt zakkenrollen!',
            'price_seeds' => 85,
            'badge' => '5x Maskers',
            'icon' => 'fa-masks-theater',
        ],
        'grim_blessing' => [
            'key' => 'grim_blessing',
            'type' => 'boost',
            'category' => 'boosts',
            'name' => '💀 Magere Hein Zegening (24 Uur)',
            'description' => 'Ontvang 24 uur lang +15% extra Kracht & Verdediging bij alle gevechten op de luchtplaats!',
            'price_seeds' => 200,
            'badge' => '24u Perk',
            'icon' => 'fa-shield-halved',
        ],
    ];

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
        if (! self::isEventActive()) {
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
        $record = HalloweenQuestProgress::firstOrCreate([
            'character_id' => $character->id,
            'quest_key' => $quest['key'],
            'period_key' => $periodKey,
        ], [
            'current_count' => 0,
            'target_count' => $quest['target'],
            'is_claimed' => false,
        ]);

        if (! $record->is_claimed) {
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
