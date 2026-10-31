@props([
    'badge',
    'size' => 'md',
])

@php
    $tier = $badge['tier'] ?? 'bronze';
    $unlocked = (bool) ($badge['unlocked'] ?? false);
    $assetId = $badge['asset_id'] ?? 'intake';
    $uid = 'badge_' . ($badge['id'] ?? $assetId) . '_' . $tier . '_' . ($unlocked ? 'u' : 'l') . '_' . uniqid();

    $sizeClasses = match($size) {
        'sm' => 'w-14 h-16',
        'lg' => 'w-24 h-28',
        default => 'w-20 h-24',
    };

    // Color definitions based on tier
    $tierGradients = [
        'bronze' => [
            'ribbon_main' => '#9a3412',
            'ribbon_accent' => '#ea580c',
            'ribbon_stripe' => '#fed7aa',
            'outer_stop1' => '#fed7aa',
            'outer_stop2' => '#c2410c',
            'outer_stop3' => '#7c2d12',
            'inner_stop1' => '#9a3412',
            'inner_stop2' => '#7c2d12',
            'inner_stop3' => '#3b0a04',
            'accent' => '#ffedd5',
            'rim_glow' => '#fb923c',
            'stars' => 1,
        ],
        'silver' => [
            'ribbon_main' => '#1e3a8a',
            'ribbon_accent' => '#3b82f6',
            'ribbon_stripe' => '#bfdbfe',
            'outer_stop1' => '#ffffff',
            'outer_stop2' => '#94a3b8',
            'outer_stop3' => '#334155',
            'inner_stop1' => '#e2e8f0',
            'inner_stop2' => '#64748b',
            'inner_stop3' => '#1e293b',
            'accent' => '#f8fafc',
            'rim_glow' => '#cbd5e1',
            'stars' => 2,
        ],
        'gold' => [
            'ribbon_main' => '#581c87',
            'ribbon_accent' => '#9333ea',
            'ribbon_stripe' => '#fef08a',
            'outer_stop1' => '#fef9c3',
            'outer_stop2' => '#eab308',
            'outer_stop3' => '#854d0e',
            'inner_stop1' => '#facc15',
            'inner_stop2' => '#ca8a04',
            'inner_stop3' => '#713f12',
            'accent' => '#fef08a',
            'rim_glow' => '#fde047',
            'stars' => 3,
        ],
        'diamond' => [
            'ribbon_main' => '#0f172a',
            'ribbon_accent' => '#06b6d4',
            'ribbon_stripe' => '#a5f3fc',
            'outer_stop1' => '#ffffff',
            'outer_stop2' => '#38bdf8',
            'outer_stop3' => '#0369a1',
            'inner_stop1' => '#67e8f9',
            'inner_stop2' => '#0284c7',
            'inner_stop3' => '#0f172a',
            'accent' => '#e0f2fe',
            'rim_glow' => '#38bdf8',
            'stars' => 4,
        ],
    ];

    $cfg = $tierGradients[$tier] ?? $tierGradients['bronze'];

    if (!$unlocked) {
        $cfg['ribbon_main'] = '#1e293b';
        $cfg['ribbon_accent'] = '#334155';
        $cfg['ribbon_stripe'] = '#475569';
        $cfg['outer_stop1'] = '#64748b';
        $cfg['outer_stop2'] = '#334155';
        $cfg['outer_stop3'] = '#0f172a';
        $cfg['inner_stop1'] = '#334155';
        $cfg['inner_stop2'] = '#1e293b';
        $cfg['inner_stop3'] = '#020617';
        $cfg['accent'] = '#64748b';
        $cfg['rim_glow'] = '#475569';
    }
@endphp

<div class="relative flex flex-col items-center justify-center shrink-0 {{ $sizeClasses }} group select-none">
    <svg class="w-full h-full drop-shadow-md transition-transform duration-300 group-hover:scale-105" viewBox="0 0 100 125" fill="none" xmlns="http://www.w3.org/2000/svg">
        <defs>
            <!-- Drop Shadow Filter -->
            <filter id="shadow_{{ $uid }}" x="-15%" y="-10%" width="130%" height="130%" filterUnits="userSpaceOnUse">
                <feDropShadow dx="0" dy="3" stdDeviation="3" flood-color="#000000" flood-opacity="0.45" />
            </filter>

            <!-- Ribbon Gradient -->
            <linearGradient id="ribbon_grad_{{ $uid }}" x1="0%" y1="0%" x2="100%" y2="0%">
                <stop offset="0%" stop-color="{{ $cfg['ribbon_main'] }}" />
                <stop offset="25%" stop-color="{{ $cfg['ribbon_accent'] }}" />
                <stop offset="50%" stop-color="{{ $cfg['ribbon_stripe'] }}" />
                <stop offset="75%" stop-color="{{ $cfg['ribbon_accent'] }}" />
                <stop offset="100%" stop-color="{{ $cfg['ribbon_main'] }}" />
            </linearGradient>

            <!-- Outer Metallic Bevel Gradient -->
            <linearGradient id="outer_grad_{{ $uid }}" x1="0%" y1="0%" x2="100%" y2="100%">
                <stop offset="0%" stop-color="{{ $cfg['outer_stop1'] }}" />
                <stop offset="50%" stop-color="{{ $cfg['outer_stop2'] }}" />
                <stop offset="100%" stop-color="{{ $cfg['outer_stop3'] }}" />
            </linearGradient>

            <!-- Inner Face Gradient -->
            <radialGradient id="inner_grad_{{ $uid }}" cx="50%" cy="40%" r="55%">
                <stop offset="0%" stop-color="{{ $cfg['inner_stop1'] }}" />
                <stop offset="70%" stop-color="{{ $cfg['inner_stop2'] }}" />
                <stop offset="100%" stop-color="{{ $cfg['inner_stop3'] }}" />
            </radialGradient>

            <!-- Glass Reflection Glare Gradient -->
            <linearGradient id="glare_{{ $uid }}" x1="0%" y1="0%" x2="0%" y2="100%">
                <stop offset="0%" stop-color="#ffffff" stop-opacity="0.4" />
                <stop offset="80%" stop-color="#ffffff" stop-opacity="0.0" />
            </linearGradient>
        </defs>

        <!-- 1. HANGING SILK RIBBON -->
        <g filter="url(#shadow_{{ $uid }})">
            <!-- Left Ribbon Tail -->
            <path d="M 28 0 L 44 0 L 46 44 L 36 36 L 28 44 Z" fill="{{ $cfg['ribbon_main'] }}" />
            <!-- Right Ribbon Tail -->
            <path d="M 56 0 L 72 0 L 72 44 L 64 36 L 54 44 Z" fill="{{ $cfg['ribbon_main'] }}" />
            <!-- Main Folded Center Ribbon -->
            <path d="M 32 0 L 68 0 L 68 45 L 50 38 L 32 45 Z" fill="url(#ribbon_grad_{{ $uid }})" stroke="{{ $unlocked ? 'rgba(255,255,255,0.2)' : 'rgba(0,0,0,0.4)' }}" stroke-width="0.8" />
            <!-- Ribbon Center Pinstripes -->
            <line x1="47" y1="0" x2="47" y2="40" stroke="{{ $cfg['ribbon_stripe'] }}" stroke-width="1.2" opacity="0.8" />
            <line x1="53" y1="0" x2="53" y2="40" stroke="{{ $cfg['ribbon_stripe'] }}" stroke-width="1.2" opacity="0.8" />
            <!-- Metallic Buckle / Attachment Bar -->
            <rect x="30" y="38" width="40" height="5" rx="2" fill="url(#outer_grad_{{ $uid }})" stroke="#000" stroke-opacity="0.3" stroke-width="0.5" />
            <circle cx="33" cy="40.5" r="1.2" fill="#fff" opacity="0.7" />
            <circle cx="67" cy="40.5" r="1.2" fill="#fff" opacity="0.7" />
        </g>

        <!-- 2. MEDALLION BODY -->
        <g filter="url(#shadow_{{ $uid }})">
            <!-- Outer Ring (Metallic Bevel) -->
            <circle cx="50" cy="78" r="38" fill="url(#outer_grad_{{ $uid }})" stroke="{{ $unlocked ? '#000' : '#475569' }}" stroke-opacity="{{ $unlocked ? '0.25' : '0.8' }}" stroke-width="0.8" />

            <!-- Beaded / Riveted Outer Border -->
            <circle cx="50" cy="78" r="34" fill="none" stroke="{{ $cfg['outer_stop3'] }}" stroke-width="1" stroke-dasharray="2 3" opacity="0.7" />

            <!-- Inner Recessed Metallic Dish -->
            <circle cx="50" cy="78" r="32" fill="url(#inner_grad_{{ $uid }})" stroke="{{ $cfg['outer_stop1'] }}" stroke-opacity="0.5" stroke-width="0.8" />

            <!-- Sunburst Rays Subtle Pattern -->
            @if($unlocked)
            <g opacity="0.12" stroke="#fff" stroke-width="1.2">
                <line x1="50" y1="48" x2="50" y2="108" />
                <line x1="20" y1="78" x2="80" y2="78" />
                <line x1="29" y1="57" x2="71" y2="99" />
                <line x1="29" y1="99" x2="71" y2="57" />
            </g>
            @endif

            <!-- 3. CUSTOM EMBLEM SVG ICON (24 UNIQUE ASSETS) -->
            <g transform="translate(50, 78)">
                @if(!$unlocked)
                    <!-- LOCKED: Heavy Weathered Padlock -->
                    <g transform="translate(0, -2)">
                        <!-- Lock Shackle -->
                        <path d="M -8 -4 L -8 -11 C -8 -16, 8 -16, 8 -11 L 8 -4" fill="none" stroke="#94a3b8" stroke-width="3.5" stroke-linecap="round" />
                        <!-- Lock Body -->
                        <rect x="-13" y="-4" width="26" height="22" rx="4" fill="#1e293b" stroke="#475569" stroke-width="1.8" />
                        <!-- Glowing Keyhole -->
                        <circle cx="0" cy="4" r="3" fill="#f59e0b" opacity="0.9" />
                        <polygon points="-2,5 2,5 3,11 -3,11" fill="#f59e0b" opacity="0.9" />
                        <!-- Rivets -->
                        <circle cx="-9" cy="0" r="1" fill="#64748b" />
                        <circle cx="9" cy="0" r="1" fill="#64748b" />
                        <circle cx="-9" cy="14" r="1" fill="#64748b" />
                        <circle cx="9" cy="14" r="1" fill="#64748b" />
                    </g>
                @else
                    <!-- UNLOCKED: DEDICATED VECTOR EMBLEMS -->
                    @switch($assetId)
                        @case('intake')
                            <!-- 1. Officiële Opname: Dossier Map met Stempel & Zegel -->
                            <g transform="translate(0, 0)">
                                <rect x="-14" y="-14" width="28" height="28" rx="3" fill="#fef08a" stroke="#ca8a04" stroke-width="1.2" />
                                <path d="M -10 -9 L 10 -9 M -10 -4 L 10 -4 M -10 1 L 4 1 M -10 6 L 2 6" stroke="#854d0e" stroke-width="1.4" stroke-linecap="round" />
                                <!-- Wax Seal -->
                                <circle cx="7" cy="6" r="5" fill="#dc2626" />
                                <circle cx="7" cy="6" r="3.2" fill="none" stroke="#fecaca" stroke-width="0.8" />
                                <path d="M 5.8 6 L 6.8 7.2 L 8.6 5" stroke="#fff" stroke-width="1" stroke-linecap="round" />
                            </g>
                            @break

                        @case('veteran')
                            <!-- 2. Kliniek Veteraan: Ereteken Chevrons & Lauwerkrans -->
                            <g transform="translate(0, -1)">
                                <!-- Chevrons -->
                                <path d="M -12 -8 L 0 -1 L 12 -8 L 12 -4 L 0 3 L -12 -4 Z" fill="#fff" opacity="0.95" />
                                <path d="M -12 -1 L 0 6 L 12 -1 L 12 3 L 0 10 L -12 3 Z" fill="#fff" opacity="0.95" />
                                <path d="M -12 6 L 0 13 L 12 6 L 12 9 L 0 16 L -12 9 Z" fill="#fef08a" opacity="0.9" />
                                <!-- Top Star -->
                                <polygon points="0,-16 2.5,-11 8,-11 3.5,-7 5.5,-2 0,-5 -5.5,-2 -3.5,-7 -8,-11 -2.5,-11" fill="#fef08a" stroke="#ca8a04" stroke-width="0.5" />
                            </g>
                            @break

                        @case('legend')
                            <!-- 3. Afdelings Legende: Keizerlijke TBS Kroon met Juwelen -->
                            <g transform="translate(0, 1)">
                                <path d="M -15 9 L -15 -5 L -8 2 L 0 -10 L 8 2 L 15 -5 L 15 9 Z" fill="#fbbf24" stroke="#78350f" stroke-width="1.2" />
                                <rect x="-15" y="7" width="30" height="4" rx="1" fill="#b45309" stroke="#78350f" stroke-width="0.8" />
                                <!-- Crown Jewels -->
                                <circle cx="-15" cy="-6" r="2.2" fill="#ef4444" stroke="#fff" stroke-width="0.6" />
                                <circle cx="0" cy="-11" r="2.8" fill="#38bdf8" stroke="#fff" stroke-width="0.8" />
                                <circle cx="15" cy="-6" r="2.2" fill="#ef4444" stroke="#fff" stroke-width="0.6" />
                                <circle cx="-8" cy="9" r="1.3" fill="#10b981" />
                                <circle cx="0" cy="9" r="1.6" fill="#38bdf8" />
                                <circle cx="8" cy="9" r="1.3" fill="#10b981" />
                            </g>
                            @break

                        @case('respect')
                            <!-- 4. Onbetwist Respect: Vurig Hart omringd met Prikkeldraad -->
                            <g transform="translate(0, 0)">
                                <!-- Burning Flames -->
                                <path d="M -6 -15 Q 0 -9 3 -16 Q 8 -7 0 -2 Q -12 -8 -6 -15 Z" fill="#f97316" opacity="0.8" />
                                <path d="M 0 -17 Q 5 -11 7 -15 Q 11 -8 5 -3 Z" fill="#facc15" />
                                <!-- Heart -->
                                <path d="M 0 11 C -16 -1 -12 -12 0 -6 C 12 -12 16 -1 0 11 Z" fill="#ef4444" stroke="#991b1b" stroke-width="1.2" />
                                <!-- Barbed Wire wrap -->
                                <path d="M -14 0 Q 0 -5 14 2" fill="none" stroke="#f8fafc" stroke-width="1.6" stroke-linecap="round" />
                                <path d="M -5 -4 L -3 0 M 4 -1 L 6 3" stroke="#f8fafc" stroke-width="1.6" stroke-linecap="round" />
                            </g>
                            @break

                        @case('brawler')
                            <!-- 5. Luchtplaats Kampioen: Zware Gebalde Bokshandschoen -->
                            <g transform="translate(0, 1)">
                                <path d="M -11 4 C -14 -4 -7 -14 3 -12 C 10 -10 13 -4 11 4 C 10 9 6 12 -2 12 C -8 12 -10 9 -11 4 Z" fill="#ef4444" stroke="#7f1d1d" stroke-width="1.2" />
                                <path d="M -9 -1 C -12 -3 -8 -8 -3 -7" fill="none" stroke="#fee2e2" stroke-width="1.4" stroke-linecap="round" />
                                <!-- Glove Cuff / Wrap -->
                                <rect x="-8" y="9" width="14" height="4" rx="1.5" fill="#f8fafc" stroke="#475569" stroke-width="0.8" />
                                <line x1="-3" y1="9" x2="-3" y2="13" stroke="#dc2626" stroke-width="1" />
                                <!-- Impact sparks -->
                                <path d="M 12 -8 L 15 -11 M 14 -4 L 18 -4 M 11 0 L 15 2" stroke="#fef08a" stroke-width="1.4" stroke-linecap="round" />
                            </g>
                            @break

                        @case('gladiator')
                            <!-- 6. Gladiator: Gekruiste Romeinse Zwaarden & Slagschild -->
                            <g transform="translate(0, 0)">
                                <!-- Swords crossed -->
                                <line x1="-14" y1="-14" x2="14" y2="14" stroke="#f8fafc" stroke-width="2.4" stroke-linecap="round" />
                                <line x1="14" y1="-14" x2="-14" y2="14" stroke="#f8fafc" stroke-width="2.4" stroke-linecap="round" />
                                <!-- Hilts -->
                                <rect x="-16" y="-16" width="4" height="4" fill="#d97706" />
                                <rect x="12" y="-16" width="4" height="4" fill="#d97706" />
                                <!-- Centered Studded Shield -->
                                <circle cx="0" cy="0" r="10" fill="#3b82f6" stroke="#1e3a8a" stroke-width="1.5" />
                                <circle cx="0" cy="0" r="4" fill="#fbbf24" stroke="#b45309" stroke-width="1" />
                                <circle cx="-6" cy="0" r="1" fill="#fff" />
                                <circle cx="6" cy="0" r="1" fill="#fff" />
                                <circle cx="0" cy="-6" r="1" fill="#fff" />
                                <circle cx="0" cy="6" r="1" fill="#fff" />
                            </g>
                            @break

                        @case('executioner')
                            <!-- 7. Bajes Beul: Doodshoofd met Gekruiste Strijdbijlen -->
                            <g transform="translate(0, 0)">
                                <!-- Crossed Battleaxes -->
                                <line x1="-13" y1="-13" x2="13" y2="13" stroke="#94a3b8" stroke-width="2.2" />
                                <line x1="13" y1="-13" x2="-13" y2="13" stroke="#94a3b8" stroke-width="2.2" />
                                <path d="M -15 -12 Q -12 -16 -8 -13 L -11 -9 Z" fill="#e2e8f0" stroke="#475569" stroke-width="0.8" />
                                <path d="M 15 -12 Q 12 -16 8 -13 L 11 -9 Z" fill="#e2e8f0" stroke="#475569" stroke-width="0.8" />
                                <!-- Skull -->
                                <path d="M -9 -2 C -9 -10 9 -10 9 -2 C 9 3 6 5 6 9 L -6 9 C -6 5 -9 3 -9 -2 Z" fill="#f8fafc" stroke="#334155" stroke-width="1.2" />
                                <!-- Eye Sockets -->
                                <circle cx="-4" cy="-2" r="2.2" fill="#0f172a" />
                                <circle cx="4" cy="-2" r="2.2" fill="#0f172a" />
                                <polygon points="0,1 -1.5,4 1.5,4" fill="#0f172a" />
                                <!-- Teeth -->
                                <line x1="-3" y1="7" x2="-3" y2="9" stroke="#334155" stroke-width="1" />
                                <line x1="0" y1="7" x2="0" y2="9" stroke="#334155" stroke-width="1" />
                                <line x1="3" y1="7" x2="3" y2="9" stroke="#334155" stroke-width="1" />
                            </g>
                            @break

                        @case('strength')
                            <!-- 8. IJzervreter: Gietijzeren Halter & Bliksemschichten -->
                            <g transform="translate(0, 0)">
                                <!-- Barbell Bar -->
                                <rect x="-16" y="-2" width="32" height="4" rx="1" fill="#cbd5e1" stroke="#334155" stroke-width="0.8" />
                                <!-- Left Weights -->
                                <rect x="-14" y="-12" width="4" height="24" rx="2" fill="#38bdf8" stroke="#0369a1" stroke-width="1" />
                                <rect x="-18" y="-9" width="3" height="18" rx="1.5" fill="#0284c7" />
                                <!-- Right Weights -->
                                <rect x="10" y="-12" width="4" height="24" rx="2" fill="#38bdf8" stroke="#0369a1" stroke-width="1" />
                                <rect x="15" y="-9" width="3" height="18" rx="1.5" fill="#0284c7" />
                                <!-- Center Grip -->
                                <line x1="-5" y1="-2" x2="5" y2="-2" stroke="#f8fafc" stroke-width="1.5" stroke-dasharray="1 1" />
                                <!-- Muscle Lightning -->
                                <polygon points="0,-14 -3,-6 1,-6 -1,-1 4,-7 0,-7" fill="#fde047" stroke="#ca8a04" stroke-width="0.6" />
                            </g>
                            @break

                        @case('pickpocket')
                            <!-- 9. Vlugge Vingers: Grijpende Hand & Sleutelbos -->
                            <g transform="translate(0, 1)">
                                <!-- Key ring -->
                                <circle cx="8" cy="-5" r="4.5" fill="none" stroke="#fbbf24" stroke-width="1.5" />
                                <path d="M 11 -3 L 15 2 L 14 3 L 13 2 L 12 4" stroke="#fbbf24" stroke-width="1.2" stroke-linecap="round" />
                                <!-- Snatching Hand -->
                                <path d="M -12 7 C -10 3 -8 -4 -1 -6 C 4 -7 7 -3 5 1 C 4 3 0 4 -2 5 C -4 6 -6 8 -10 9 Z" fill="#fed7aa" stroke="#c2410c" stroke-width="1" />
                                <!-- Swift motion lines -->
                                <path d="M -15 -3 Q -10 -8 -5 -8" fill="none" stroke="#fff" stroke-width="1.2" stroke-linecap="round" />
                                <path d="M -14 1 Q -9 -3 -4 -3" fill="none" stroke="#fff" stroke-width="1.2" stroke-linecap="round" />
                            </g>
                            @break

                        @case('burglar')
                            <!-- 10. Meester Inbreker: Koevoet & Kluisrad -->
                            <g transform="translate(0, 0)">
                                <!-- Vault Dial Ring -->
                                <circle cx="0" cy="0" r="11" fill="#1e293b" stroke="#94a3b8" stroke-width="2" />
                                <circle cx="0" cy="0" r="5" fill="#64748b" />
                                <line x1="0" y1="-11" x2="0" y2="-8" stroke="#fff" stroke-width="1.5" />
                                <line x1="11" y1="0" x2="8" y2="0" stroke="#fff" stroke-width="1.5" />
                                <line x1="0" y1="11" x2="0" y2="8" stroke="#fff" stroke-width="1.5" />
                                <line x1="-11" y1="0" x2="-8" y2="0" stroke="#fff" stroke-width="1.5" />
                                <!-- Crowbar pry -->
                                <path d="M -15 13 L 9 -11 L 13 -8 L 11 -6 L 9 -7 L -13 15 Z" fill="#ef4444" stroke="#7f1d1d" stroke-width="0.8" />
                                <path d="M 9 -11 Q 14 -14 15 -9" fill="none" stroke="#ef4444" stroke-width="2" stroke-linecap="round" />
                            </g>
                            @break

                        @case('shadow')
                            <!-- 11. Kliniek Schaduw: Bivak Schim met Gloeiende Ogen -->
                            <g transform="translate(0, 0)">
                                <!-- Shadow Silhouette Hood -->
                                <path d="M -12 11 C -15 3 -12 -12 0 -13 C 12 -12 15 3 12 11 C 7 13 -7 13 -12 11 Z" fill="#0f172a" stroke="#64748b" stroke-width="1.2" />
                                <!-- Face opening slit -->
                                <ellipse cx="0" cy="-2" rx="9" ry="5" fill="#1e293b" />
                                <!-- Glowing sharp eyes -->
                                <polygon points="-7,-3 -2,-1 -6,-1" fill="#a855f7" />
                                <polygon points="7,-3 2,-1 6,-1" fill="#a855f7" />
                                <circle cx="-4" cy="-2" r="1" fill="#fff" />
                                <circle cx="4" cy="-2" r="1" fill="#fff" />
                            </g>
                            @break

                        @case('mask')
                            <!-- 12. Bivak Meester: Bivakmasker met Gekruiste Lockpicks -->
                            <g transform="translate(0, 0)">
                                <!-- Crossed Lockpicks -->
                                <line x1="-14" y1="14" x2="14" y2="-14" stroke="#38bdf8" stroke-width="1.8" stroke-linecap="round" />
                                <line x1="14" y1="14" x2="-14" y2="-14" stroke="#38bdf8" stroke-width="1.8" stroke-linecap="round" />
                                <!-- Mask -->
                                <path d="M -10 11 C -12 3 -11 -12 0 -13 C 11 -12 12 3 10 11 C 6 13 -6 13 -10 11 Z" fill="#1e293b" stroke="#38bdf8" stroke-width="1.4" />
                                <!-- Eye holes -->
                                <ellipse cx="-4" cy="-3" rx="2.4" ry="1.8" fill="#38bdf8" />
                                <ellipse cx="4" cy="-3" rx="2.4" ry="1.8" fill="#38bdf8" />
                                <!-- Mouth hole -->
                                <ellipse cx="0" cy="5" rx="3.5" ry="1.5" fill="#0284c7" />
                            </g>
                            @break

                        @case('money_bronze')
                            <!-- 13. Kruimelspaarder: Bundels Bankbiljetten & Euromunten -->
                            <g transform="translate(0, 1)">
                                <!-- Cash bundle 1 -->
                                <rect x="-13" y="-6" width="22" height="12" rx="2" fill="#10b981" stroke="#047857" stroke-width="1" transform="rotate(-8)" />
                                <!-- Cash bundle 2 -->
                                <rect x="-10" y="-3" width="22" height="12" rx="2" fill="#34d399" stroke="#065f46" stroke-width="1" />
                                <circle cx="1" cy="3" r="3" fill="#a7f3d0" />
                                <!-- Shiny Coins in front -->
                                <circle cx="8" cy="7" r="5" fill="#f59e0b" stroke="#b45309" stroke-width="0.8" />
                                <text x="8" y="9.5" font-size="6" font-weight="900" text-anchor="middle" fill="#fff">€</text>
                            </g>
                            @break

                        @case('tycoon')
                            <!-- 14. Zwartgeld Tycoon: Open Geldkoffer met Stapels Cash -->
                            <g transform="translate(0, 1)">
                                <!-- Briefcase Lid Open -->
                                <polygon points="-13,-3 13,-3 11,-12 -11,-12" fill="#475569" stroke="#1e293b" stroke-width="1" />
                                <!-- Briefcase Body -->
                                <rect x="-14" y="-3" width="28" height="15" rx="2" fill="#334155" stroke="#0f172a" stroke-width="1.2" />
                                <!-- Cash Stacks inside -->
                                <rect x="-10" y="-7" width="10" height="6" fill="#10b981" stroke="#047857" stroke-width="0.6" />
                                <rect x="1" y="-8" width="9" height="7" fill="#34d399" stroke="#065f46" stroke-width="0.6" />
                                <!-- Briefcase Locks & Handle -->
                                <circle cx="-9" cy="0" r="1.5" fill="#fbbf24" />
                                <circle cx="9" cy="0" r="1.5" fill="#fbbf24" />
                                <path d="M -4 12 L -4 15 L 4 15 L 4 12" fill="none" stroke="#64748b" stroke-width="1.5" />
                            </g>
                            @break

                        @case('millionaire')
                            <!-- 15. Kliniek Miljonair: Goudstaven Piramide met Diamant -->
                            <g transform="translate(0, 1)">
                                <!-- Bottom Gold Bars -->
                                <polygon points="-14,10 -3,10 -5,5 -12,5" fill="#f59e0b" stroke="#78350f" stroke-width="0.8" />
                                <polygon points="-2,10 9,10 7,5 0,5" fill="#fbbf24" stroke="#78350f" stroke-width="0.8" />
                                <polygon points="10,10 15,10 13,5 11,5" fill="#d97706" />
                                <!-- Top Gold Bar -->
                                <polygon points="-8,5 4,5 2,0 -6,0" fill="#fde047" stroke="#78350f" stroke-width="0.8" />
                                <!-- Crowning Radiant Diamond -->
                                <polygon points="0,-13 6,-7 0,-1 -6,-7" fill="#38bdf8" stroke="#fff" stroke-width="0.8" />
                                <polygon points="0,-13 2,-7 0,-1 -2,-7" fill="#bae6fd" />
                                <!-- Sparkle glints -->
                                <circle cx="-7" cy="-10" r="0.8" fill="#fff" />
                                <circle cx="7" cy="-10" r="0.8" fill="#fff" />
                            </g>
                            @break

                        @case('oligarch')
                            <!-- 16. Oligarch: Gigantische Geldzak met Diamanten Kroon & Euro -->
                            <g transform="translate(0, 1)">
                                <!-- Money Bag -->
                                <path d="M 0 -8 C -4 -13 -7 -13 -6 -8 C -14 -2 -14 11 0 12 C 14 11 14 -2 6 -8 C 7 -13 4 -13 0 -8 Z" fill="#0284c7" stroke="#082f49" stroke-width="1.4" />
                                <!-- Gold rope tie -->
                                <line x1="-5" y1="-6" x2="5" y2="-6" stroke="#fbbf24" stroke-width="2" />
                                <!-- Euro Sign -->
                                <text x="0" y="6" font-size="11" font-weight="900" text-anchor="middle" fill="#fff" font-family="Arial, sans-serif">€</text>
                                <!-- Floating Diamond Crown on top -->
                                <path d="M -8 -11 L -4 -8 L 0 -14 L 4 -8 L 8 -11 L 6 -6 L -6 -6 Z" fill="#38bdf8" stroke="#fff" stroke-width="0.7" />
                            </g>
                            @break

                        @case('friend')
                            <!-- 17. Kameraadschap: Handdruk met Doorgeknipte Handboeien -->
                            <g transform="translate(0, 0)">
                                <!-- Broken Handcuffs link -->
                                <circle cx="-8" cy="-8" r="4.5" fill="none" stroke="#94a3b8" stroke-width="1.5" />
                                <circle cx="8" cy="-8" r="4.5" fill="none" stroke="#94a3b8" stroke-width="1.5" />
                                <line x1="-4" y1="-8" x2="4" y2="-8" stroke="#f8fafc" stroke-width="1.5" stroke-dasharray="2 1" />
                                <!-- Solid Handshake -->
                                <path d="M -13 4 L -5 0 L 0 3 L 5 0 L 13 4" fill="none" stroke="#f97316" stroke-width="3" stroke-linecap="round" />
                                <path d="M -2 3 L 2 3" stroke="#fed7aa" stroke-width="3" stroke-linecap="round" />
                                <!-- Heart between palms -->
                                <path d="M 0 5 C -3 2 -2 0 0 1.5 C 2 0 3 2 0 5 Z" fill="#ef4444" />
                            </g>
                            @break

                        @case('network')
                            <!-- 18. Gezelligheidsdier: Sociaal Netwerk Radar & Patiëntenknooppunten -->
                            <g transform="translate(0, 0)">
                                <!-- Radar circles -->
                                <circle cx="0" cy="0" r="13" fill="none" stroke="#94a3b8" stroke-width="0.8" opacity="0.4" />
                                <circle cx="0" cy="0" r="7" fill="none" stroke="#94a3b8" stroke-width="0.8" opacity="0.6" />
                                <!-- Network Connection Lines -->
                                <line x1="0" y1="0" x2="-9" y2="-7" stroke="#3b82f6" stroke-width="1.2" />
                                <line x1="0" y1="0" x2="9" y2="-7" stroke="#3b82f6" stroke-width="1.2" />
                                <line x1="0" y1="0" x2="0" y2="10" stroke="#3b82f6" stroke-width="1.2" />
                                <!-- Center Profile -->
                                <circle cx="0" cy="0" r="3.5" fill="#f59e0b" stroke="#fff" stroke-width="1" />
                                <!-- Connected Inmate Nodes -->
                                <circle cx="-9" cy="-7" r="2.8" fill="#3b82f6" stroke="#fff" stroke-width="0.8" />
                                <circle cx="9" cy="-7" r="2.8" fill="#3b82f6" stroke="#fff" stroke-width="0.8" />
                                <circle cx="0" cy="10" r="2.8" fill="#3b82f6" stroke="#fff" stroke-width="0.8" />
                            </g>
                            @break

                        @case('gang')
                            <!-- 19. Bende Broederschap: Bende Wapen met Boksbeugel & Bandana -->
                            <g transform="translate(0, 0)">
                                <!-- Brass Knuckles shape -->
                                <path d="M -11 -5 C -11 -10 11 -10 11 -5 C 11 -1 9 1 0 1 C -9 1 -11 -1 -11 -5 Z" fill="#475569" stroke="#0f172a" stroke-width="1.2" />
                                <circle cx="-6" cy="-5" r="2" fill="#1e293b" />
                                <circle cx="-2" cy="-5" r="2" fill="#1e293b" />
                                <circle cx="2" cy="-5" r="2" fill="#1e293b" />
                                <circle cx="6" cy="-5" r="2" fill="#1e293b" />
                                <!-- Bandana Skull -->
                                <circle cx="0" cy="5" r="6" fill="#f8fafc" stroke="#0f172a" stroke-width="1" />
                                <!-- Bandana wrap -->
                                <path d="M -6 3 Q 0 0 6 3 L 6 0 L -6 0 Z" fill="#ef4444" />
                                <circle cx="-2.5" cy="5.5" r="1.3" fill="#0f172a" />
                                <circle cx="2.5" cy="5.5" r="1.3" fill="#0f172a" />
                            </g>
                            @break

                        @case('recruiter')
                            <!-- 20. Kliniek Recruiter: Megofoon / Omroeper met Uitnodigingsgolven -->
                            <g transform="translate(0, 1)">
                                <!-- Megaphone Horn -->
                                <polygon points="-9,-2 3,-7 3,7 -9,2" fill="#38bdf8" stroke="#0369a1" stroke-width="1.2" />
                                <ellipse cx="3" cy="0" rx="2" ry="7" fill="#0284c7" stroke="#0369a1" stroke-width="1" />
                                <rect x="-13" y="-3" width="4" height="6" rx="1" fill="#64748b" />
                                <path d="M -8 2 L -6 8 L -4 8 L -6 2" fill="#475569" />
                                <!-- Broadcast Sound Waves -->
                                <path d="M 8 -5 Q 11 0 8 5" fill="none" stroke="#facc15" stroke-width="1.5" stroke-linecap="round" />
                                <path d="M 11 -8 Q 15 0 11 8" fill="none" stroke="#facc15" stroke-width="1.5" stroke-linecap="round" />
                            </g>
                            @break

                        @case('task')
                            <!-- 21. Klusjesman: Gekruiste Moersleutel & Bajesdweil -->
                            <g transform="translate(0, 0)">
                                <!-- Mop -->
                                <line x1="-12" y1="-12" x2="12" y2="12" stroke="#d97706" stroke-width="2" stroke-linecap="round" />
                                <path d="M 9 9 Q 14 14 11 15 Q 13 10 14 8 Z" fill="#f8fafc" stroke="#94a3b8" stroke-width="0.8" />
                                <!-- Wrench crossed -->
                                <line x1="12" y1="-12" x2="-12" y2="12" stroke="#94a3b8" stroke-width="2.4" stroke-linecap="round" />
                                <circle cx="11" cy="-11" r="3.5" fill="#cbd5e1" stroke="#475569" stroke-width="1" />
                                <circle cx="11" cy="-11" r="1.5" fill="#1e293b" />
                                <!-- Sparkle Stars -->
                                <polygon points="-5,-8 -4,-5 -1,-4 -4,-3 -5,0 -6,-3 -9,-4 -6,-5" fill="#fde047" />
                            </g>
                            @break

                        @case('mission_master')
                            <!-- 22. Missie Meester: Scherpschutter Crosshair & Bullseye -->
                            <g transform="translate(0, 0)">
                                <!-- Concentric Target Rings -->
                                <circle cx="0" cy="0" r="12" fill="none" stroke="#ef4444" stroke-width="1.8" />
                                <circle cx="0" cy="0" r="8" fill="none" stroke="#f8fafc" stroke-width="1.2" />
                                <circle cx="0" cy="0" r="4" fill="#ef4444" stroke="#7f1d1d" stroke-width="1" />
                                <circle cx="0" cy="0" r="1.5" fill="#fde047" />
                                <!-- Crosshairs -->
                                <line x1="0" y1="-14" x2="0" y2="-9" stroke="#fff" stroke-width="1.8" />
                                <line x1="0" y1="9" x2="0" y2="14" stroke="#fff" stroke-width="1.8" />
                                <line x1="-14" y1="0" x2="-9" y2="0" stroke="#fff" stroke-width="1.8" />
                                <line x1="9" y1="0" x2="14" y2="0" stroke="#fff" stroke-width="1.8" />
                            </g>
                            @break

                        @case('commander')
                            <!-- 23. Operatie Commandeur: Generaals Insigne & Commandostrepen -->
                            <g transform="translate(0, -1)">
                                <!-- Eagle wings -->
                                <path d="M 0 3 Q -10 -5 -15 -1 Q -11 6 0 11 Q 11 6 15 -1 Q 10 -5 0 3 Z" fill="#fbbf24" stroke="#78350f" stroke-width="1" />
                                <!-- Center Star -->
                                <polygon points="0,-10 2.5,-3 10,-3 4,2 6,9 0,5 -6,9 -4,2 -10,-3 -2.5,-3" fill="#f8fafc" stroke="#ca8a04" stroke-width="0.8" />
                                <!-- Command Bars below -->
                                <rect x="-8" y="11" width="16" height="2.5" rx="1" fill="#fde047" stroke="#78350f" stroke-width="0.5" />
                                <rect x="-6" y="14.5" width="12" height="2.5" rx="1" fill="#fde047" stroke="#78350f" stroke-width="0.5" />
                            </g>
                            @break

                        @case('discipline')
                            <!-- 24. IJzeren Discipline: Stopwatch Timer & Gouden Lauwerkrans -->
                            <g transform="translate(0, 0)">
                                <!-- Stopwatch Body -->
                                <circle cx="0" cy="1" r="11" fill="#0284c7" stroke="#e0f2fe" stroke-width="1.8" />
                                <!-- Top Clicker Button -->
                                <rect x="-2" y="-14" width="4" height="4" fill="#38bdf8" stroke="#0369a1" stroke-width="0.8" />
                                <circle cx="0" cy="-14" r="2.5" fill="none" stroke="#38bdf8" stroke-width="1" />
                                <!-- Watch Face -->
                                <circle cx="0" cy="1" r="8" fill="#f8fafc" stroke="#0369a1" stroke-width="1" />
                                <!-- Ticks & Hands -->
                                <line x1="0" y1="-5" x2="0" y2="-4" stroke="#0f172a" stroke-width="1.2" />
                                <line x1="6" y1="1" x2="5" y2="1" stroke="#0f172a" stroke-width="1.2" />
                                <line x1="0" y1="1" x2="0" y2="-3" stroke="#ef4444" stroke-width="1.4" stroke-linecap="round" />
                                <line x1="0" y1="1" x2="3" y2="2" stroke="#0f172a" stroke-width="1.4" stroke-linecap="round" />
                                <circle cx="0" cy="1" r="1.2" fill="#ef4444" />
                            </g>
                            @break

                        @default
                            <!-- Fallback Star -->
                            <polygon points="0,-12 3,-4 11,-4 5,2 7,10 0,5 -7,10 -5,2 -11,-4 -3,-4" fill="#fbbf24" stroke="#b45309" stroke-width="1" />
                    @endswitch
                @endif
            </g>

            <!-- 4. GLASS SHINE REFLECTION (TOP-LEFT ARC) -->
            @if($unlocked)
            <path d="M 23 64 A 32 32 0 0 1 77 64 A 32 20 0 0 0 23 64 Z" fill="url(#glare_{{ $uid }})" />
            @endif

            <!-- 5. TIER STARS (BOTTOM BANNER) -->
            <g transform="translate(50, 102)">
                <rect x="-14" y="-4" width="28" height="8" rx="4" fill="{{ $unlocked ? 'rgba(0,0,0,0.65)' : 'rgba(0,0,0,0.85)' }}" stroke="{{ $cfg['rim_glow'] }}" stroke-width="0.8" />
                @php
                    $numStars = $cfg['stars'] ?? 1;
                    $starOffsets = match($numStars) {
                        1 => [0],
                        2 => [-4, 4],
                        3 => [-7, 0, 7],
                        4 => [-9, -3, 3, 9],
                        default => [0],
                    };
                @endphp
                @foreach($starOffsets as $xOff)
                    <polygon points="{{ $xOff }},{{ -2.2 }} {{ $xOff + 0.8 }},{{ -0.6 }} {{ $xOff + 2.4 }},{{ -0.6 }} {{ $xOff + 1.1 }},{{ 0.5 }} {{ $xOff + 1.5 }},{{ 2.2 }} {{ $xOff }},{{ 1.1 }} {{ $xOff - 1.5 }},{{ 2.2 }} {{ $xOff - 1.1 }},{{ 0.5 }} {{ $xOff - 2.4 }},{{ -0.6 }} {{ $xOff - 0.8 }},{{ -0.6 }}"
                        fill="{{ $unlocked ? $cfg['rim_glow'] : '#64748b' }}" stroke="{{ $unlocked ? '#fff' : '#334155' }}" stroke-width="0.3" />
                @endforeach
            </g>
        </g>
    </svg>
</div>
