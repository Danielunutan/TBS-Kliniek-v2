<?php

namespace App\Support;

class ProfileStickerCatalog
{
    /**
     * Alias for stickers() for broader compatibility.
     *
     * @return array<string, array<string, mixed>>
     */
    public static function all(): array
    {
        return self::stickers();
    }

    /**
     * All available collectible stickers for patient profiles.
     *
     * @return array<string, array<string, mixed>>
     */
    public static function stickers(): array
    {
        return [
            'skull' => [
                'key' => 'skull',
                'name' => 'Gouden Doodskop',
                'icon' => 'fa-skull',
                'color' => 'amber',
                'badge_class' => 'bg-amber-100 dark:bg-amber-950/80 text-amber-500 border-amber-300 dark:border-amber-700',
                'rarity' => 'Zeldzaam',
                'description' => 'Gegoten doodshoofd van puur goud, gereserveerd voor gevreesde veteranen.',
            ],
            'handcuffs' => [
                'key' => 'handcuffs',
                'name' => 'Verbrijzelde Handboeien',
                'icon' => 'fa-handcuffs',
                'color' => 'slate',
                'badge_class' => 'bg-slate-100 dark:bg-slate-800 text-slate-400 border-slate-300 dark:border-slate-600',
                'rarity' => 'Klassiek',
                'description' => 'Symbool van een patiënt die nooit getemd kan worden.',
            ],
            'dagger' => [
                'key' => 'dagger',
                'name' => 'Kliniek Steekwapen',
                'icon' => 'fa-crosshairs',
                'color' => 'rose',
                'badge_class' => 'bg-rose-100 dark:bg-rose-950/80 text-rose-500 border-rose-300 dark:border-rose-700',
                'rarity' => 'Gevaarlijk',
                'description' => 'Zelfgemaakt geïmproviseerd wapen voor de wandelgangen.',
            ],
            'smoking' => [
                'key' => 'smoking',
                'name' => 'Gouden Peuk',
                'icon' => 'fa-smoking',
                'color' => 'amber',
                'badge_class' => 'bg-amber-100 dark:bg-amber-950/80 text-amber-600 border-amber-300 dark:border-amber-700',
                'rarity' => 'Populair',
                'description' => 'De onofficiële munteenheid en levensader van de TBS Kliniek.',
            ],
            'vip_star' => [
                'key' => 'vip_star',
                'name' => 'Cartel Diamant & Ster',
                'icon' => 'fa-star',
                'color' => 'purple',
                'badge_class' => 'bg-purple-100 dark:bg-purple-950/80 text-purple-500 border-purple-300 dark:border-purple-700',
                'rarity' => 'Elite',
                'description' => 'Schitterende ster toegekend aan gerespecteerde weldoeners en cartelbonzen.',
            ],
            'fire' => [
                'key' => 'fire',
                'name' => 'TBS Vlammenzee',
                'icon' => 'fa-fire',
                'color' => 'orange',
                'badge_class' => 'bg-orange-100 dark:bg-orange-950/80 text-orange-500 border-orange-300 dark:border-orange-700',
                'rarity' => 'Intens',
                'description' => 'Voor patiënten die continu voor oproer en brandjes zorgen.',
            ],
            'money' => [
                'key' => 'money',
                'name' => 'Stapel Bloedgeld',
                'icon' => 'fa-money-bill-wave',
                'color' => 'emerald',
                'badge_class' => 'bg-emerald-100 dark:bg-emerald-950/80 text-emerald-500 border-emerald-300 dark:border-emerald-700',
                'rarity' => 'Winstgevend',
                'description' => 'Dikke rollen contant geld vergaard via overvallen en witwassen.',
            ],
            'shield' => [
                'key' => 'shield',
                'name' => 'Bende Beschermer',
                'icon' => 'fa-shield-halved',
                'color' => 'blue',
                'badge_class' => 'bg-blue-100 dark:bg-blue-950/80 text-blue-500 border-blue-300 dark:border-blue-700',
                'rarity' => 'Loyaliteit',
                'description' => 'Onverwoestbaar schild van bende-solidariteit en eer.',
            ],
            'crown' => [
                'key' => 'crown',
                'name' => 'Kliniek Koning',
                'icon' => 'fa-crown',
                'color' => 'yellow',
                'badge_class' => 'bg-amber-100 dark:bg-amber-950/80 text-amber-500 border-amber-300 dark:border-amber-600',
                'rarity' => 'Legendarisch',
                'description' => 'Wie de kroon draagt, zwaait de scepter over alle paviljoens.',
            ],
            'pumpkin' => [
                'key' => 'pumpkin',
                'name' => 'Spooky Jack-O\'-Lantern',
                'icon' => 'fa-ghost',
                'color' => 'orange',
                'badge_class' => 'bg-orange-100 dark:bg-orange-950/80 text-orange-500 border-orange-300 dark:border-orange-700',
                'rarity' => 'Halloween',
                'description' => 'Griezelige pompoengeest die door de isolatiegangen spookt.',
            ],
            'biohazard' => [
                'key' => 'biohazard',
                'name' => 'Paviljoen 13 Brandmerk',
                'icon' => 'fa-biohazard',
                'color' => 'lime',
                'badge_class' => 'bg-lime-100 dark:bg-lime-950/80 text-lime-500 border-lime-300 dark:border-lime-700',
                'rarity' => 'Gevaarlijk',
                'description' => 'Waarschuwing voor chemisch besmettingsgevaar in de gesloten afdeling.',
            ],
            'mask' => [
                'key' => 'mask',
                'name' => 'Heist Bivakmuts',
                'icon' => 'fa-mask',
                'color' => 'slate',
                'badge_class' => 'bg-slate-100 dark:bg-slate-800 text-slate-300 border-slate-300 dark:border-slate-600',
                'rarity' => 'Stealth',
                'description' => 'Zwarte bivakmuts voor stille overvallen en nachtelijke rooftochten.',
            ],
            'dice' => [
                'key' => 'dice',
                'name' => 'Lucky High-Roller Dobbelstenen',
                'icon' => 'fa-dice',
                'color' => 'purple',
                'badge_class' => 'bg-purple-100 dark:bg-purple-950/80 text-purple-500 border-purple-300 dark:border-purple-700',
                'rarity' => 'Gokker',
                'description' => 'Voor spelers die altijd all-in gaan in het ondergrondse casino.',
            ],
            'boxing' => [
                'key' => 'boxing',
                'name' => 'IJzeren Boksbeugel',
                'icon' => 'fa-hand-back-fist',
                'color' => 'rose',
                'badge_class' => 'bg-rose-100 dark:bg-rose-950/80 text-rose-500 border-rose-300 dark:border-rose-700',
                'rarity' => 'Vechtersbaas',
                'description' => 'Versterkt je vuist voor brute gevechten op de luchtplaats.',
            ],
        ];
    }

    /**
     * Get single sticker definition.
     */
    public static function getSticker(?string $key): ?array
    {
        if (! $key) {
            return null;
        }

        return self::stickers()[$key] ?? null;
    }

    /**
     * Available background themes for patient profile headers.
     *
     * @return array<string, array<string, mixed>>
     */
    public static function themes(): array
    {
        return [
            'default' => [
                'key' => 'default',
                'name' => 'Klassiek Kliniek Donker',
                'bg_class' => 'bg-white dark:bg-slate-900 border-slate-200/80 dark:border-slate-800',
                'bar_class' => 'bg-purple-600',
                'ring_class' => 'ring-purple-500/40',
                'text_color' => 'text-purple-600 dark:text-purple-400',
                'glow_class' => 'bg-purple-500/10',
                'dot_color' => '#9333ea',
            ],
            'neon_purple' => [
                'key' => 'neon_purple',
                'name' => 'TBS Midnight Neon Paars',
                'bg_class' => 'bg-gradient-to-br from-purple-950/90 via-slate-900 to-indigo-950/90 border-purple-500/40 text-white',
                'bar_class' => 'bg-gradient-to-r from-purple-500 via-fuchsia-500 to-indigo-500',
                'ring_class' => 'ring-purple-500/60 ring-offset-2 ring-offset-slate-950',
                'text_color' => 'text-purple-400',
                'glow_class' => 'bg-purple-600/20',
                'dot_color' => '#c084fc',
            ],
            'crimson_blood' => [
                'key' => 'crimson_blood',
                'name' => 'Bloedrood Onderwereld',
                'bg_class' => 'bg-gradient-to-br from-rose-950/90 via-slate-900 to-red-950/90 border-rose-500/40 text-white',
                'bar_class' => 'bg-gradient-to-r from-red-600 via-rose-500 to-orange-500',
                'ring_class' => 'ring-rose-500/60 ring-offset-2 ring-offset-slate-950',
                'text_color' => 'text-rose-400',
                'glow_class' => 'bg-rose-600/20',
                'dot_color' => '#f43f5e',
            ],
            'midnight_navy' => [
                'key' => 'midnight_navy',
                'name' => 'Cipiers Nachtwacht Blauw',
                'bg_class' => 'bg-gradient-to-br from-sky-950/90 via-slate-900 to-blue-950/90 border-blue-500/40 text-white',
                'bar_class' => 'bg-gradient-to-r from-blue-600 via-sky-500 to-cyan-500',
                'ring_class' => 'ring-blue-500/60 ring-offset-2 ring-offset-slate-950',
                'text_color' => 'text-blue-400',
                'glow_class' => 'bg-blue-600/20',
                'dot_color' => '#38bdf8',
            ],
            'toxic_emerald' => [
                'key' => 'toxic_emerald',
                'name' => 'Giftig Smaragdgroen',
                'bg_class' => 'bg-gradient-to-br from-emerald-950/90 via-slate-900 to-teal-950/90 border-emerald-500/40 text-white',
                'bar_class' => 'bg-gradient-to-r from-emerald-500 via-teal-400 to-lime-500',
                'ring_class' => 'ring-emerald-500/60 ring-offset-2 ring-offset-slate-950',
                'text_color' => 'text-emerald-400',
                'glow_class' => 'bg-emerald-600/20',
                'dot_color' => '#34d399',
            ],
            'golden_cartel' => [
                'key' => 'golden_cartel',
                'name' => 'Goudstaaf Cartel Boss',
                'bg_class' => 'bg-gradient-to-br from-amber-950/90 via-slate-900 to-yellow-950/90 border-amber-500/40 text-white',
                'bar_class' => 'bg-gradient-to-r from-amber-500 via-yellow-400 to-amber-600',
                'ring_class' => 'ring-amber-500/60 ring-offset-2 ring-offset-slate-950',
                'text_color' => 'text-amber-400',
                'glow_class' => 'bg-amber-600/20',
                'dot_color' => '#fbbf24',
            ],
            'halloween_pumpkin' => [
                'key' => 'halloween_pumpkin',
                'name' => 'Halloween Pompoen & Mist',
                'bg_class' => 'bg-gradient-to-br from-orange-950/90 via-slate-950 to-purple-950/90 border-orange-500/40 text-white',
                'bar_class' => 'bg-gradient-to-r from-orange-500 via-amber-500 to-purple-600',
                'ring_class' => 'ring-orange-500/60 ring-offset-2 ring-offset-slate-950',
                'text_color' => 'text-orange-400',
                'glow_class' => 'bg-orange-600/20',
                'dot_color' => '#fb923c',
            ],
            'cyberpunk' => [
                'key' => 'cyberpunk',
                'name' => 'Cyberpunk Terminal',
                'bg_class' => 'bg-gradient-to-br from-fuchsia-950/90 via-slate-950 to-cyan-950/90 border-cyan-500/40 text-white',
                'bar_class' => 'bg-gradient-to-r from-fuchsia-500 via-purple-500 to-cyan-400',
                'ring_class' => 'ring-cyan-500/60 ring-offset-2 ring-offset-slate-950',
                'text_color' => 'text-cyan-400',
                'glow_class' => 'bg-cyan-600/20',
                'dot_color' => '#22d3ee',
            ],
        ];
    }

    /**
     * Get single theme definition.
     */
    public static function getTheme(?string $key): array
    {
        return self::themes()[$key ?? 'default'] ?? self::themes()['default'];
    }
}
