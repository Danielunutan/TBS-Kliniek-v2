<!DOCTYPE html>
<html lang="nl" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="TBS Kliniek">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="theme-color" content="#0b0f19" media="(prefers-color-scheme: dark)">
    <meta name="theme-color" content="#ffffff" media="(prefers-color-scheme: light)">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="turbo-cache-control" content="no-cache">
    <!-- ANTI-FOUC DARK MODE THEME SCRIPT -->
    <script>
        (function() {
            try {
                const savedTheme = localStorage.getItem('tbs_theme');
                if (savedTheme === 'dark') {
                    document.documentElement.classList.add('dark');
                } else {
                    document.documentElement.classList.remove('dark');
                }
            } catch(e) {}
        })();
    </script>
    <title>@yield('title', 'TBS Kliniek - Het #1 Misdaad & Gevangenis Browsergame van Nederland')</title>
    
    <!-- PRIMARY SEO META TAGS -->
    <meta name="description" content="@yield('meta_description', 'Speel gratis TBS Kliniek, hét toonaangevende Nederlandse misdaad- en gevangenis RPG browsergame. Vecht op de luchtplaats, kraak medicijnkluizen, richt gangs op en heers over de kliniek!')">
    <meta name="keywords" content="@yield('meta_keywords', 'TBS Kliniek, TBS game, misdaad browser game, online rpg nederland, gevangenis spel, boevenbende, tekstbased rpg, maffia game, misdaadspel online, gratis online spelen, browsergame nederland 2026')">
    <meta name="author" content="TBS Kliniek">
    <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">
    <link rel="canonical" href="{{ url()->current() }}">

    <!-- OPEN GRAPH / FACEBOOK / DISCORD -->
    <meta property="og:type" content="website">
    <meta property="og:locale" content="nl_NL">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:site_name" content="TBS Kliniek">
    <meta property="og:title" content="@yield('og_title', 'TBS Kliniek - Het #1 Misdaad & Gevangenis Browsergame van Nederland')">
    <meta property="og:description" content="@yield('og_description', 'Hét toonaangevende Nederlandse misdaadspel en gevangenis RPG. Kraak kluizen, vecht op de afdeling, leid je gang en heers over de TBS kliniek!')">
    <meta property="og:image" content="{{ asset('assets/illustrations/tbs_crew_lineup.jpg') }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">

    <!-- TWITTER / X CARDS -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:site" content="@tbskliniek">
    <meta name="twitter:creator" content="@tbskliniek">
    <meta name="twitter:url" content="{{ url()->current() }}">
    <meta name="twitter:title" content="TBS Kliniek - Het #1 Nederlandse Gevangenis & Misdaad RPG">
    <meta name="twitter:description" content="Dé moderne Nederlandse tekstbased misdaad game. Gratis online speelbaar!">
    <meta name="twitter:image" content="{{ asset('assets/illustrations/tbs_crew_lineup.jpg') }}">

    <!-- STRUCTURED DATA (SCHEMA.ORG JSON-LD) -->
    <script type="application/ld+json">
    {!! json_encode([
        '@context' => 'https://schema.org',
        '@graph' => [
            [
                '@type' => 'WebSite',
                '@id' => url('/') . '#website',
                'url' => url('/'),
                'name' => 'TBS Kliniek',
                'description' => 'Hét populairste Nederlandse misdaad browsergame en gevangenis RPG. Kraak kluizen, vecht op de afdeling en heers over de kliniek.',
                'inLanguage' => 'nl-NL',
            ],
            [
                '@type' => 'VideoGame',
                '@id' => url('/') . '#game',
                'name' => 'TBS Kliniek',
                'url' => url('/'),
                'genre' => ['Misdaad Game', 'Tekstbased RPG', 'Gevangenis Simulator', 'Boeven Spel', 'Strategie', 'Browsergame'],
                'gamePlatform' => ['Web Browser', 'iOS', 'Android', 'PC', 'Mac'],
                'applicationCategory' => 'GameApplication',
                'operatingSystem' => 'Any web browser',
                'description' => 'TBS Kliniek is een Nederlands online tekstbased RPG waarin je overleeft in een forensische instelling, bendes opricht, kluizen kraakt en vecht voor respect en buit.',
                'keywords' => 'TBS Kliniek, misdaad browser game, tekst rpg nederland, gevangenis spel, boevenbende',
                'offers' => [
                    '@type' => 'Offer',
                    'price' => '0',
                    'priceCurrency' => 'EUR',
                    'availability' => 'https://schema.org/InStock',
                ],
                'aggregateRating' => [
                    '@type' => 'AggregateRating',
                    'ratingValue' => '4.9',
                    'ratingCount' => '342',
                    'bestRating' => '5',
                    'worstRating' => '1',
                ],
            ],
        ],
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}
    </script>

    <link rel="icon" type="image/png" href="{{ asset('assets/logo.png') }}?v=3">
    <link rel="shortcut icon" href="{{ asset('assets/logo.png') }}?v=3">
    <link rel="apple-touch-icon" href="{{ asset('assets/logo.png') }}?v=3">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&family=Outfit:wght@600;700;800;900&display=swap" rel="stylesheet">

    <!-- PWA & MOBILE NATIVE APP CAPABILITIES -->
    <link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
    <link rel="apple-touch-icon" href="{{ asset('assets/icons/icon-192x192.png') }}">
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function() {
                navigator.serviceWorker.register('/sw.js').catch(function() {});
            });
        }
    </script>
    <script src="{{ asset('assets/js/tbs-push.js') }}" defer></script>

    <!-- GOOGLE ADSENSE (Tijdelijk uitgeschakeld) -->
    {{--
    <meta name="google-adsense-account" content="ca-pub-2509357892188070">
    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-2509357892188070"
    --}}


    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        [x-cloak] {
            display: none !important;
        }

        /* TBS Floating In-App Toast Design System */
        @keyframes tbsToastCountdown {
            0% { transform: scaleX(1); }
            100% { transform: scaleX(0); }
        }
        .tbs-toast-progress-bar {
            animation-name: tbsToastCountdown;
            animation-timing-function: linear;
            animation-fill-mode: forwards;
        }
        .tbs-toast-card:hover .tbs-toast-progress-bar {
            animation-play-state: paused;
        }
        .tbs-toast-card {
            background-color: #ffffff !important;
            color: #0f172a !important;
            border: 1px solid #e2e8f0 !important;
            box-shadow: 0 20px 25px -5px rgba(15, 23, 42, 0.12), 0 8px 10px -6px rgba(15, 23, 42, 0.08) !important;
        }
        html.dark .tbs-toast-card {
            background-color: #0f172a !important;
            color: #f8fafc !important;
            border-color: #334155 !important;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.8) !important;
        }
        .tbs-toast-badge-success { background-color: #ecfdf5 !important; color: #059669 !important; border: 1px solid #a7f3d0 !important; }
        .tbs-toast-badge-error   { background-color: #fff1f2 !important; color: #e11d48 !important; border: 1px solid #fecdd3 !important; }
        .tbs-toast-badge-warning { background-color: #fffbeb !important; color: #d97706 !important; border: 1px solid #fde68a !important; }
        .tbs-toast-badge-info    { background-color: #f5f3ff !important; color: #7c3aed !important; border: 1px solid #ddd6fe !important; }

        html.dark .tbs-toast-badge-success { background-color: rgba(6, 78, 59, 0.6) !important; color: #34d399 !important; border-color: #065f46 !important; }
        html.dark .tbs-toast-badge-error   { background-color: rgba(136, 19, 55, 0.6) !important; color: #fb7185 !important; border-color: #9f1239 !important; }
        html.dark .tbs-toast-badge-warning { background-color: rgba(120, 53, 15, 0.6) !important; color: #fbbf24 !important; border-color: #92400e !important; }
        html.dark .tbs-toast-badge-info    { background-color: rgba(91, 33, 182, 0.6) !important; color: #c084fc !important; border-color: #6b21a8 !important; }

        .tbs-toast-bar-success { background-color: #10b981 !important; box-shadow: 0 0 10px rgba(16, 185, 129, 0.6); }
        .tbs-toast-bar-error   { background-color: #f43f5e !important; box-shadow: 0 0 10px rgba(244, 63, 94, 0.6); }
        .tbs-toast-bar-warning { background-color: #f59e0b !important; box-shadow: 0 0 10px rgba(245, 158, 11, 0.6); }
        .tbs-toast-bar-info    { background-color: #9333ea !important; box-shadow: 0 0 10px rgba(147, 51, 234, 0.6); }
        html, body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            text-rendering: optimizeLegibility;
        }

        /* Seamless Rich Dark Mode Overrides for TBS Kliniek */
        html.dark body {
            background-color: #0b0f19 !important;
            color: #e2e8f0 !important;
        }
        html.dark .bg-white {
            background-color: #111827 !important;
        }
        html.dark .bg-slate-50 {
            background-color: #0f172a !important;
        }
        html.dark .bg-slate-100 {
            background-color: #1e293b !important;
        }
        html.dark .bg-slate-200 {
            background-color: #1e293b !important;
        }
        html.dark .bg-slate-300 {
            background-color: #334155 !important;
        }
        /* Fix ugly semi-transparent grey washes in darkmode */
        html.dark [class*="bg-slate-50/"],
        html.dark [class*="bg-slate-100/"] {
            background-color: rgba(15, 23, 42, 0.6) !important;
        }
        html.dark [class*="bg-slate-200/"],
        html.dark [class*="bg-slate-300/"] {
            background-color: rgba(30, 41, 59, 0.6) !important;
        }
        html.dark .border-slate-100,
        html.dark .border-slate-200,
        html.dark [class*="border-slate-100/"],
        html.dark [class*="border-slate-200/"] {
            border-color: #1e293b !important;
        }
        html.dark .divide-slate-100 > :not([hidden]) ~ :not([hidden]),
        html.dark .divide-slate-200 > :not([hidden]) ~ :not([hidden]) {
            border-color: #1e293b !important;
        }
        html.dark .text-slate-900,
        html.dark .text-slate-800 {
            color: #f8fafc !important;
        }
        html.dark .text-slate-700 {
            color: #e2e8f0 !important;
        }
        html.dark .text-slate-600,
        html.dark .text-slate-500 {
            color: #94a3b8 !important;
        }
        html.dark .text-purple-700 {
            color: #c084fc !important;
        }
        html.dark .text-purple-800,
        html.dark .text-purple-900 {
            color: #e9d5ff !important;
        }
        html.dark .text-amber-900,
        html.dark .text-amber-950 {
            color: #fef3c7 !important;
        }
        html.dark .text-amber-800 {
            color: #fde68a !important;
        }
        html.dark .text-amber-700 {
            color: #fbbf24 !important;
        }
        html.dark .text-amber-600,
        html.dark .text-amber-500 {
            color: #f59e0b !important;
        }
        html.dark .bg-purple-50,
        html.dark .bg-purple-100,
        html.dark [class*="bg-purple-50/"],
        html.dark [class*="bg-purple-100/"] {
            background-color: rgba(168, 85, 247, 0.12) !important;
            border-color: rgba(168, 85, 247, 0.25) !important;
        }
        /* Remove ugly gradient buttons and banner bars in dark mode */
        html.dark button[class*="bg-gradient-to-"],
        html.dark a[class*="bg-gradient-to-"]:not([class*="rounded-full"]) {
            background-image: none !important;
        }
        html.dark div[class*="bg-gradient-to-r"]:not([class*="rounded-full"]),
        html.dark div[class*="bg-gradient-to-br"]:not([class*="rounded-full"]) {
            background-image: none !important;
        }
        html.dark .bg-amber-50,
        html.dark .bg-amber-100,
        html.dark [class*="bg-amber-50/"],
        html.dark [class*="bg-amber-100/"] {
            background-color: rgba(245, 158, 11, 0.14) !important;
            border-color: rgba(245, 158, 11, 0.35) !important;
        }
        html.dark .border-amber-100,
        html.dark .border-amber-200,
        html.dark [class*="border-amber-100/"],
        html.dark [class*="border-amber-200/"] {
            border-color: rgba(245, 158, 11, 0.35) !important;
        }
        html.dark .text-yellow-900,
        html.dark .text-yellow-950 {
            color: #fef9c3 !important;
        }
        html.dark .text-yellow-800 {
            color: #fef08a !important;
        }
        html.dark .bg-yellow-50,
        html.dark .bg-yellow-100,
        html.dark [class*="bg-yellow-50/"],
        html.dark [class*="bg-yellow-100/"] {
            background-color: rgba(234, 179, 8, 0.14) !important;
            border-color: rgba(234, 179, 8, 0.35) !important;
        }
        html.dark input:not([type="checkbox"]):not([type="radio"]),
        html.dark select,
        html.dark textarea {
            background-color: #111827 !important;
            border-color: #1e293b !important;
            color: #f8fafc !important;
        }

        /* Active selectors & Radio cards in Dark Mode (No more ugly white background!) */
        html.dark label:has(input[type="radio"]:checked),
        html.dark label:has(input[type="checkbox"]:checked) {
            background-color: rgba(147, 51, 234, 0.18) !important;
            border-color: #a855f7 !important;
            box-shadow: 0 0 0 1px rgba(168, 85, 247, 0.4) !important;
        }
        html.dark label:has(input[type="radio"]:checked) span,
        html.dark label:has(input[type="checkbox"]:checked) span {
            color: #f8fafc !important;
        }

        /* Prevent ugly white flashes when hovering labels or cards in dark mode */
        html.dark label:hover,
        html.dark .hover\:bg-slate-50:hover,
        html.dark [class*="hover:bg-slate-50"]:hover,
        html.dark [class*="hover:bg-slate-100"]:hover,
        html.dark [class*="hover:bg-white"]:hover {
            background-color: rgba(30, 41, 59, 0.75) !important;
        }

        /* Vibrant Action Buttons in Dark Mode (Eliminates dead colorless slate-900 buttons) */
        html.dark button.bg-slate-900,
        html.dark a.bg-slate-900:not([href*="tiktok"]):not([class*="dark:bg-"]),
        html.dark button[class*="bg-slate-900"]:not([class*="dark:bg-"]) {
            background-color: #9333ea !important;
            color: #ffffff !important;
        }
        html.dark button.bg-slate-900:hover,
        html.dark a.bg-slate-900:hover:not([href*="tiktok"]):not([class*="dark:bg-"]),
        html.dark button[class*="bg-slate-900"]:not([class*="dark:bg-"]):hover {
            background-color: #7e22ce !important;
            color: #ffffff !important;
        }

        /* ======================================================== */
        /* UNIVERSAL SLEEK SCROLLBARS FOR TBS KLINIEK               */
        /* ======================================================== */
        /* Standard modern CSS (Firefox & Chromium 121+) */
        * {
            scrollbar-width: thin;
            scrollbar-color: rgba(148, 163, 184, 0.4) transparent;
        }

        html.dark * {
            scrollbar-color: rgba(148, 163, 184, 0.25) transparent;
        }

        /* WebKit / Chromium (Chrome, Edge, Safari, Brave, Opera) */
        *::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        *::-webkit-scrollbar-track {
            background: transparent !important;
        }

        *::-webkit-scrollbar-thumb {
            background-color: rgba(148, 163, 184, 0.35);
            border-radius: 9999px;
            border: 1px solid transparent;
        }

        *::-webkit-scrollbar-thumb:hover {
            background-color: rgba(100, 116, 139, 0.65);
        }

        *::-webkit-scrollbar-corner {
            background: transparent !important;
        }

        /* Dark Mode Scrollbars */
        html.dark *::-webkit-scrollbar-thumb {
            background-color: rgba(148, 163, 184, 0.22);
        }

        html.dark *::-webkit-scrollbar-thumb:hover {
            background-color: rgba(168, 85, 247, 0.6);
        }

        /* Thin Scrollbar Utility (for horizontal pills, tabs & dropdowns) */
        .scrollbar-thin,
        .custom-scrollbar {
            scrollbar-width: thin !important;
            scrollbar-color: rgba(148, 163, 184, 0.3) transparent !important;
        }

        html.dark .scrollbar-thin,
        html.dark .custom-scrollbar {
            scrollbar-color: rgba(168, 85, 247, 0.3) transparent !important;
        }

        .scrollbar-thin::-webkit-scrollbar,
        .custom-scrollbar::-webkit-scrollbar {
            width: 4px !important;
            height: 4px !important;
        }

        .scrollbar-thin::-webkit-scrollbar-track,
        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent !important;
        }

        .scrollbar-thin::-webkit-scrollbar-thumb,
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background-color: rgba(148, 163, 184, 0.35) !important;
            border-radius: 9999px !important;
        }

        .scrollbar-thin::-webkit-scrollbar-thumb:hover,
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background-color: rgba(147, 51, 234, 0.65) !important;
        }

        /* Hide Scrollbar completely when smooth touch/drag scrolling is preferred */
        .scrollbar-none,
        .no-scrollbar {
            -ms-overflow-style: none !important;
            scrollbar-width: none !important;
        }

        .scrollbar-none::-webkit-scrollbar,
        .no-scrollbar::-webkit-scrollbar {
            display: none !important;
            width: 0 !important;
            height: 0 !important;
        }

        /* Prevent mobile swipe/pull-to-refresh reload in all chat stream containers */
        #tbsChatScrollContainer,
        #tbsMiniChatScroll,
        #tbsMiniGangScroll,
        #tbsMiniDirectScroll,
        .tbs-chat-wrapper {
            overscroll-behavior-y: contain !important;
            -webkit-overflow-scrolling: touch !important;
        }
    </style>

    @php
        $character = $character ?? (Auth::check() ? Auth::user()->character : null);
        $onlineCount = $character ? \App\Models\Character::getOnlineCount() : 0;
        $reqXp = 100;
        $pctXp = 0;
        if ($character) {
            $lvlService = app(\App\Services\LevelService::class);
            $reqXp = $lvlService->getXpRequiredForNextLevel($character->level);
            $pctXp = min(100, max(0, round(($character->xp / max(1, $reqXp)) * 100)));
        }
        $activeCategory = '';
        if (request()->routeIs('halloween.*') || request()->is('event*', 'halloween*')) {
            $activeCategory = 'event';
        } elseif (request()->routeIs('gangs.*') || request()->is('gangs*', 'bendes*')) {
            $activeCategory = 'bende';
        } elseif (request()->routeIs('timeline.*', 'friends.*', 'leaderboard.*', 'battle.*', 'prison', 'news.*', 'newspaper.*', 'roadmap.*') || request()->is('tijdlijn*', 'vrienden*', 'ranglijst*', 'battle*', 'isoleercel*', 'nieuws*', 'afdelingsblad*', 'roadmap*')) {
            $activeCategory = 'algemeen';
        } elseif (request()->routeIs('depot.*', 'missions.*', 'training.*', 'treatment.*', 'infirmary.*', 'daycare.*') || request()->is('depot*', 'missies*', 'training*', 'behandeling*', 'ziekenboeg*', 'dagbesteding*')) {
            $activeCategory = 'voortgang';
        } elseif (request()->routeIs('hustle.*', 'smuggle.*', 'extort.*', 'lockers.*') || request()->is('hosselen*', 'smokkelen*', 'afpersen*', 'kluisjes*')) {
            $activeCategory = 'misdaad';
        } elseif (request()->routeIs('connect_four.*', 'true_crime.*', 'memory.*') || request()->is('vier-op-een-rij*', 'true-crime*', 'memory*')) {
            $activeCategory = 'specials';
        } elseif (request()->routeIs('vault.*', 'packages.*', 'pickpocket.*') || request()->is('kluis-kraak*', 'pakketjes*', 'zakkenrollen*', 'vault*')) {
            $activeCategory = 'bonus';
        } elseif (request()->routeIs('fight.*', 'theft.*', 'combat_training.*', 'armwrestling.*', 'hitman.*', 'informants.*', 'isolation.*', 'murder_stats.*') || request()->is('luchtplaats*', 'vechten*', 'diefstal*', 'klusjes*', 'vechtoefening*', 'armpjedrukken*', 'liquidatie*', 'informanten*', 'vrijwillige-isolatie*', 'geweldsstatistieken*')) {
            $activeCategory = 'geweld';
        } elseif (request()->routeIs('bank.*', 'travel.*', 'casino.*', 'rps.*', 'highlow.*', 'numbergame.*', 'roulette.*', 'slots.*', 'shop.*', 'marketplace.*', 'lottery.*') || request()->is('bank*', 'verplaatsen*', 'reizen*', 'casino*', 'gokhal*', 'winkel*', 'shop*', 'marktplaats*', 'loterij*')) {
            $activeCategory = 'economie';
        } elseif (request()->routeIs('profile.*', 'vip.*', 'settings.*', 'admin.*', 'faq.*', 'help.*') || request()->is('profiel*', 'speler*', 'vip*', 'instellingen*', 'admin*', 'faq*', 'kennisbank*', 'help*')) {
            $activeCategory = 'account';
        }
    @endphp

    @if($character)
    <script>
        function formatMoneyNL(val) {
            return '€' + new Intl.NumberFormat('nl-NL').format(Math.max(0, Math.round(val)));
        }

        function animateNumber(start, end, duration, onUpdate, onComplete) {
            start = Number(start) || 0;
            end = Number(end) || 0;
            if (start === end) {
                onUpdate(end);
                if (onComplete) onComplete();
                return;
            }
            const startTime = performance.now();
            const diff = end - start;
            function frame(now) {
                const elapsed = now - startTime;
                const progress = Math.min(1, elapsed / duration);
                const ease = 1 - Math.pow(1 - progress, 3);
                const current = Math.round(start + (diff * ease));
                onUpdate(current);
                if (progress < 1) {
                    requestAnimationFrame(frame);
                } else {
                    onUpdate(end);
                    if (onComplete) onComplete();
                }
            }
            requestAnimationFrame(frame);
        }

        function tbsStatsTracker() {
            return {
                mobileMenuOpen: false,
                mobileQuickSheetOpen: false,
                stats: {
                    cash: {{ (int)$character->cash }},
                    cashFormatted: '€{{ number_format($character->cash, 0, ',', '.') }}',
                    cash_formatted: '€{{ number_format($character->cash, 0, ',', '.') }}',
                    bank: {{ (int)$character->bank }},
                    bankFormatted: '€{{ number_format($character->bank, 0, ',', '.') }}',
                    bank_formatted: '€{{ number_format($character->bank, 0, ',', '.') }}',
                    total: {{ (int)($character->cash + $character->bank) }},
                    totalFormatted: '€{{ number_format($character->cash + $character->bank, 0, ',', '.') }}',
                    total_formatted: '€{{ number_format($character->cash + $character->bank, 0, ',', '.') }}',
                    energy: {{ (int)$character->energy }},
                    max_energy: {{ (int)$character->max_energy }},
                    energy_pct: {{ min(100, max(0, round(($character->energy / max(1, $character->max_energy)) * 100))) }},
                    health: {{ (int)$character->health }},
                    max_health: {{ (int)$character->max_health }},
                    health_pct: {{ min(100, max(0, round(($character->health / max(1, $character->max_health)) * 100))) }},
                    xp: {{ (int)$character->xp }},
                    req_xp: {{ (int)$reqXp }},
                    xp_pct: {{ (int)$pctXp }},
                    xpFormatted: '{{ number_format($character->xp, 0, ',', '.') }} / {{ number_format($reqXp, 0, ',', '.') }} XP',
                    stars: {{ (int)($character->stars ?? 0) }},
                    starsFormatted: '{{ number_format($character->stars ?? 0, 0, ',', '.') }}',
                    stars_formatted: '{{ number_format($character->stars ?? 0, 0, ',', '.') }}',
                    vip_points: {{ (int)($character->vip_points ?? 0) }},
                    vipPointsFormatted: '{{ number_format($character->vip_points ?? 0, 0, ',', '.') }}',
                    vip_points_formatted: '{{ number_format($character->vip_points ?? 0, 0, ',', '.') }}',
                    respect: '{{ $character->formatted_respect }}',
                    formatted_respect: '{{ $character->formatted_respect }}',
                    onlineCount: {{ (int)$onlineCount }}
                },
                init() {
                    window.tbsStatsTrackerInstance = this;
                    window.tbsApplyStats = (newStats) => this.applyStats(newStats);
                    window.tbsRefreshStats = (force = false) => this.refresh(force);

                    if (window.tbsStatsTrackerInterval) {
                        clearInterval(window.tbsStatsTrackerInterval);
                        window.tbsStatsTrackerInterval = null;
                    }

                    window.tbsStatsTrackerInterval = setInterval(() => {
                        if (document.visibilityState === 'visible') {
                            this.refresh();
                        }
                    }, 20000);
                    window.addEventListener('stats:refresh', () => this.refresh(true));
                },
                lastRefreshTime: 0,
                isRefreshing: false,
                applyStats(data) {
                    if (!data) return;

                    const targetCash = (data.cash !== undefined) ? parseInt(data.cash) : this.stats.cash;
                    const targetBank = (data.bank !== undefined) ? parseInt(data.bank) : this.stats.bank;
                    const targetTotal = (data.total !== undefined) ? parseInt(data.total) : (targetCash + targetBank);
                    const targetEnergy = (data.energy !== undefined) ? parseInt(data.energy) : this.stats.energy;
                    const maxEnergy = (data.max_energy !== undefined) ? parseInt(data.max_energy) : this.stats.max_energy;
                    const targetHealth = (data.health !== undefined) ? parseInt(data.health) : this.stats.health;
                    const maxHealth = (data.max_health !== undefined) ? parseInt(data.max_health) : this.stats.max_health;
                    const targetXp = (data.xp !== undefined) ? parseInt(data.xp) : this.stats.xp;
                    const reqXp = (data.req_xp !== undefined || data.next_level_xp !== undefined) ? parseInt(data.req_xp || data.next_level_xp) : this.stats.req_xp;

                    // Animate cash
                    animateNumber(this.stats.cash, targetCash, 400, (val) => {
                        this.stats.cash = val;
                        this.stats.cashFormatted = formatMoneyNL(val);
                        this.stats.cash_formatted = this.stats.cashFormatted;
                    });

                    // Animate bank
                    animateNumber(this.stats.bank, targetBank, 400, (val) => {
                        this.stats.bank = val;
                        this.stats.bankFormatted = formatMoneyNL(val);
                        this.stats.bank_formatted = this.stats.bankFormatted;
                    });

                    // Animate total
                    animateNumber(this.stats.total, targetTotal, 400, (val) => {
                        this.stats.total = val;
                        this.stats.totalFormatted = formatMoneyNL(val);
                        this.stats.total_formatted = this.stats.totalFormatted;
                    });

                    // Animate energy
                    this.stats.max_energy = maxEnergy;
                    animateNumber(this.stats.energy, targetEnergy, 400, (val) => {
                        this.stats.energy = val;
                        const calcPct = maxEnergy > 0 ? Math.min(100, Math.max(0, Math.round((val / maxEnergy) * 100))) : 0;
                        this.stats.energy_pct = calcPct;
                        this.stats.energy_percent = calcPct;
                    });

                    // Animate health
                    this.stats.max_health = maxHealth;
                    animateNumber(this.stats.health, targetHealth, 400, (val) => {
                        this.stats.health = val;
                        const calcPct = maxHealth > 0 ? Math.min(100, Math.max(0, Math.round((val / maxHealth) * 100))) : 0;
                        this.stats.health_pct = calcPct;
                        this.stats.health_percent = calcPct;
                    });

                    // Animate XP
                    this.stats.req_xp = reqXp;
                    animateNumber(this.stats.xp, targetXp, 400, (val) => {
                        this.stats.xp = val;
                        const pct = reqXp > 0 ? Math.min(100, Math.max(0, Math.round((val / reqXp) * 100))) : 0;
                        this.stats.xp_pct = pct;
                        this.stats.xpFormatted = new Intl.NumberFormat('nl-NL').format(val) + ' / ' + new Intl.NumberFormat('nl-NL').format(reqXp) + ' XP';
                    });

                    if (data.stars !== undefined) {
                        this.stats.stars = data.stars;
                        this.stats.starsFormatted = new Intl.NumberFormat('nl-NL').format(data.stars);
                    }
                    if (data.vip_points !== undefined) {
                        this.stats.vip_points = data.vip_points;
                        this.stats.vipPointsFormatted = new Intl.NumberFormat('nl-NL').format(data.vip_points);
                    }
                    if (data.respect !== undefined || data.formatted_respect !== undefined) {
                        this.stats.respect = data.formatted_respect || data.respect;
                    }
                    if (data.onlineCount !== undefined) {
                        this.stats.onlineCount = data.onlineCount;
                    }
                },
                async refresh(force = false) {
                    const now = Date.now();
                    if (!force && (now - this.lastRefreshTime < 8000)) return;
                    if (this.isRefreshing) return;
                    this.isRefreshing = true;
                    this.lastRefreshTime = now;
                    try {
                        const res = await fetch('{{ route('character.realtime-stats') }}', {
                            credentials: 'same-origin',
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });
                        if (res.ok) {
                            const data = await res.json();
                            this.applyStats(data);
                        }
                    } catch (e) {
                    } finally {
                        this.isRefreshing = false;
                    }
                }
            };
        }

        window.tbsGoBack = function() {
            if (window.history.length > 1) {
                window.history.back();
            } else if (window.Turbo && typeof window.Turbo.visit === 'function') {
                window.Turbo.visit('{{ route('dashboard') }}');
            } else {
                window.location.href = '{{ route('dashboard') }}';
            }
        };

        window.tbsReloadPage = function(btn) {
            if (btn) {
                const icon = (btn instanceof HTMLElement) ? btn.querySelector('i') : null;
                if (icon) icon.classList.add('fa-spin');
            }
            if (typeof window.tbsRefreshStats === 'function') {
                window.tbsRefreshStats();
            }
            if (window.Turbo && typeof window.Turbo.visit === 'function') {
                window.Turbo.clearCache();
                window.Turbo.visit(window.location.href, { action: 'replace' });
            } else {
                window.location.reload();
            }
        };
    </script>
    @else
    <script>
        function tbsStatsTracker() {
            return {
                mobileMenuOpen: false,
                stats: {}
            };
        }

        window.tbsGoBack = function() {
            if (window.history.length > 1) {
                window.history.back();
            } else if (window.Turbo && typeof window.Turbo.visit === 'function') {
                window.Turbo.visit('/');
            } else {
                window.location.href = '/';
            }
        };

        window.tbsReloadPage = function(btn) {
            if (btn) {
                const icon = (btn instanceof HTMLElement) ? btn.querySelector('i') : null;
                if (icon) icon.classList.add('fa-spin');
            }
            if (window.Turbo && typeof window.Turbo.visit === 'function') {
                window.Turbo.clearCache();
                window.Turbo.visit(window.location.href, { action: 'replace' });
            } else {
                window.location.reload();
            }
        };
    </script>
    @endif
</head>
<body class="min-h-full flex flex-col lg:flex-row bg-[#f8fafc] text-slate-800 antialiased selection:bg-slate-900 selection:text-white dark:bg-[#0b0f19] dark:text-slate-100" @if($character) x-data="tbsStatsTracker()" @else x-data="{ mobileMenuOpen: false }" @endif>

    @if($character)
    <!-- SIDEBAR NAVIGATION (Desktop & Tablet) - Clean Modern White Sidebar -->
    <aside class="hidden lg:flex lg:flex-col w-64 bg-white border-r border-slate-200/90 shrink-0 min-h-screen text-slate-700 z-40 sticky top-0 h-screen overflow-y-auto shadow-xs select-none"
           x-data="{
               openCategory: '{{ $activeCategory ?? '' }}',
               init() {
                   const serverCat = '{{ $activeCategory ?? '' }}';
                   if (!serverCat) {
                       const saved = localStorage.getItem('tbs_sidebar_category');
                       if (saved) {
                           this.openCategory = saved;
                       }
                   } else {
                       localStorage.setItem('tbs_sidebar_category', serverCat);
                   }
               },
               toggleCategory(cat) {
                   this.openCategory = (this.openCategory === cat) ? '' : cat;
                   localStorage.setItem('tbs_sidebar_category', this.openCategory);
               }
           }">
        <!-- Logo Header (TBS Kliniek Brand Emblem & Typography) -->
        <div class="px-5 py-4 flex items-center border-b border-slate-100 bg-white sticky top-0 z-10">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3 group" title="TBS Kliniek Dashboard">
                <div class="w-11 h-11 shrink-0 drop-shadow-md group-hover:scale-105 transition-transform flex items-center justify-center">
                    <img src="{{ asset('assets/logo.png') }}?v=3" alt="TBS Kliniek Emblem" class="w-full h-full object-contain">
                </div>
                <div class="flex flex-col">
                    <div class="flex items-center gap-1.5">
                        <span class="text-sm font-black tracking-tight text-slate-900 leading-none">TBS <span class="text-purple-600">KLINIEK</span></span>
                    </div>
                    <div class="mt-1">
                        <a href="{{ route('members.index', ['filter' => 'online']) }}" class="inline-flex items-center gap-1.5 text-[9px] font-black uppercase tracking-wider text-emerald-700 bg-emerald-50 hover:bg-emerald-100 dark:bg-emerald-950/60 dark:text-emerald-300 dark:hover:bg-emerald-900/60 px-2 py-0.5 rounded-full border border-emerald-200 dark:border-emerald-800 transition cursor-pointer" title="{{ $onlineCount }} patiënten momenteel online in de kliniek (klik voor online lijst)">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            <span>{{ $onlineCount }} Online</span>
                        </a>
                    </div>
                </div>
            </a>
        </div>

        <!-- Navigation Sections with Collapsible Categories -->
        <nav class="flex-1 px-3 py-3 space-y-2 text-xs font-semibold">

            <!-- 🔍 COMMAND PALETTE & DIRECT SEARCH (CTRL + K) -->
            <div class="mb-2.5">
                <button type="button" 
                        onclick="if(window.tbsOpenSpotlight){window.tbsOpenSpotlight();}"
                        class="w-full flex items-center justify-between px-3 py-2 rounded-xl bg-slate-50 hover:bg-purple-50/60 dark:bg-purple-950/40 dark:hover:bg-purple-900/50 text-slate-500 hover:text-purple-700 dark:text-purple-300 dark:hover:text-white border border-slate-200/80 dark:border-purple-900/40 transition shadow-2xs group cursor-pointer text-left">
                    <span class="flex items-center gap-2.5">
                        <i class="fa-solid fa-magnifying-glass text-purple-600 dark:text-purple-400 group-hover:scale-110 transition-transform text-xs"></i>
                        <span class="text-xs font-bold text-slate-600 dark:text-slate-300">Zoek functie...</span>
                    </span>
                    <kbd class="text-[9px] font-black uppercase text-slate-400 dark:text-purple-400/80 bg-white dark:bg-purple-900/60 px-1.5 py-0.5 rounded border border-slate-200 dark:border-purple-800 shadow-2xs">Ctrl+K</kbd>
                </button>
            </div>

            <!-- ⭐ SNELLE TOEGANG (Favorieten 6-Grid) -->
            <div class="mb-2.5 p-2 rounded-xl bg-slate-50/80 dark:bg-purple-950/30 border border-slate-200/70 dark:border-purple-900/40">
                <div class="flex items-center justify-between px-1 mb-1.5 text-[9px] font-black uppercase tracking-wider text-slate-400 dark:text-purple-400/80">
                    <span class="flex items-center gap-1">
                        <i class="fa-solid fa-star text-amber-400 text-[8px]"></i> Snelle Toegang
                    </span>
                    <span class="text-[8px] opacity-70">Favorieten</span>
                </div>
                <div class="grid grid-cols-3 gap-1">
                    <a href="{{ route('hustle.index') }}" 
                       class="flex flex-col items-center justify-center p-1.5 rounded-lg bg-white dark:bg-[#160b2e] hover:bg-emerald-50 dark:hover:bg-emerald-950/40 text-slate-700 dark:text-slate-200 border border-slate-200/60 dark:border-purple-900/40 hover:border-emerald-300 transition text-center group"
                       title="Hosselen">
                        <i class="fa-solid fa-hand-holding-dollar text-emerald-500 group-hover:scale-110 transition-transform text-xs mb-0.5"></i>
                        <span class="text-[9px] font-bold truncate w-full">Hosselen</span>
                    </a>
                    <a href="{{ route('theft.index') }}" 
                       class="flex flex-col items-center justify-center p-1.5 rounded-lg bg-white dark:bg-[#160b2e] hover:bg-blue-50 dark:hover:bg-blue-950/40 text-slate-700 dark:text-slate-200 border border-slate-200/60 dark:border-purple-900/40 hover:border-blue-300 transition text-center group"
                       title="Diefstal">
                        <i class="fa-solid fa-briefcase text-blue-500 group-hover:scale-110 transition-transform text-xs mb-0.5"></i>
                        <span class="text-[9px] font-bold truncate w-full">Diefstal</span>
                    </a>
                    <a href="{{ route('bank.index') }}" 
                       class="flex flex-col items-center justify-center p-1.5 rounded-lg bg-white dark:bg-[#160b2e] hover:bg-emerald-50 dark:hover:bg-emerald-950/40 text-slate-700 dark:text-slate-200 border border-slate-200/60 dark:border-purple-900/40 hover:border-emerald-300 transition text-center group"
                       title="De Bank">
                        <i class="fa-solid fa-building-columns text-emerald-500 group-hover:scale-110 transition-transform text-xs mb-0.5"></i>
                        <span class="text-[9px] font-bold truncate w-full">Bank</span>
                    </a>
                    <a href="{{ route('gangs.index') }}" 
                       class="flex flex-col items-center justify-center p-1.5 rounded-lg bg-white dark:bg-[#160b2e] hover:bg-purple-50 dark:hover:bg-purple-950/40 text-slate-700 dark:text-slate-200 border border-slate-200/60 dark:border-purple-900/40 hover:border-purple-300 transition text-center group"
                       title="Mijn Bende">
                        <i class="fa-solid fa-shield-halved text-purple-600 dark:text-purple-400 group-hover:scale-110 transition-transform text-xs mb-0.5"></i>
                        <span class="text-[9px] font-bold truncate w-full">Bende</span>
                    </a>
                    <a href="{{ route('daycare.index') }}" 
                       class="flex flex-col items-center justify-center p-1.5 rounded-lg bg-white dark:bg-[#160b2e] hover:bg-amber-50 dark:hover:bg-amber-950/40 text-slate-700 dark:text-slate-200 border border-slate-200/60 dark:border-purple-900/40 hover:border-amber-300 transition text-center group"
                       title="Dagbesteding">
                        <i class="fa-solid fa-person-digging text-amber-500 group-hover:scale-110 transition-transform text-xs mb-0.5"></i>
                        <span class="text-[9px] font-bold truncate w-full">Werken</span>
                    </a>
                    <a href="{{ route('infirmary.index') }}" 
                       class="flex flex-col items-center justify-center p-1.5 rounded-lg bg-white dark:bg-[#160b2e] hover:bg-teal-50 dark:hover:bg-teal-950/40 text-slate-700 dark:text-slate-200 border border-slate-200/60 dark:border-purple-900/40 hover:border-teal-300 transition text-center group"
                       title="Ziekenboeg">
                        <i class="fa-solid fa-hospital-user text-teal-500 group-hover:scale-110 transition-transform text-xs mb-0.5"></i>
                        <span class="text-[9px] font-bold truncate w-full">Kliniek</span>
                    </a>
                </div>
            </div>

            <!-- Vaste Hoofdnavigatie Boven Dropdowns (Dashboard & Berichten & Post) -->
            <div class="space-y-1 pb-2 mb-1 border-b border-slate-100 dark:border-slate-800/60">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('dashboard') ? 'nav-item-active bg-indigo-50/90 dark:bg-purple-950/60 text-indigo-700 dark:text-purple-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                    <i class="fa-solid fa-gauge w-4 text-center text-indigo-500 dark:text-indigo-400"></i>
                    <span>Dashboard</span>
                </a>
                <a href="{{ route('chat.index') }}" class="flex items-center justify-between px-3 py-2 rounded-lg transition {{ request()->routeIs('chat.*') ? 'nav-item-active bg-emerald-50/90 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                    <div class="flex items-center gap-3">
                        <i class="fa-solid fa-comments w-4 text-center text-emerald-500 dark:text-emerald-400"></i>
                        <span>Live Chat</span>
                    </div>
                    @php
                        $unreadChatCount = $character ? $character->unreadDirectMessagesCount() : 0;
                    @endphp
                    @if($unreadChatCount > 0)
                        <span class="bg-emerald-500 text-white text-[10px] font-extrabold px-1.5 py-0.2 rounded-full animate-pulse">{{ $unreadChatCount }}</span>
                    @else
                        <span class="bg-emerald-100 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-400 text-[9px] font-black uppercase px-1.5 py-0.5 rounded tracking-wide">Live</span>
                    @endif
                </a>
                <a href="{{ route('cinema.index') }}" class="flex items-center justify-between px-3 py-2 rounded-lg transition {{ request()->routeIs('cinema.*') ? 'nav-item-active bg-purple-50/90 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                    <div class="flex items-center gap-3">
                        <i class="fa-solid fa-film w-4 text-center text-purple-500 dark:text-purple-400"></i>
                        <span>Cinema Watchparties</span>
                    </div>
                    <span class="bg-gradient-to-r from-purple-500 to-amber-500 text-white text-[9px] font-black uppercase px-1.5 py-0.5 rounded tracking-wide shadow-2xs">NIEUW</span>
                </a>
            </div>

            <!-- 1. ALGEMEEN & INFO -->
            <div class="space-y-1">
                <button @click="toggleCategory('algemeen')" 
                        type="button" 
                        class="w-full flex items-center justify-between px-3 py-2 text-[10px] font-extrabold uppercase tracking-wider text-slate-500 dark:text-slate-400 hover:text-sky-600 dark:hover:text-sky-300 hover:bg-sky-50/50 dark:hover:bg-sky-950/20 rounded-xl transition group">
                    <span class="flex items-center gap-2">
                        <span class="w-5 h-5 rounded-md bg-sky-100 dark:bg-sky-950/60 text-sky-600 dark:text-sky-400 flex items-center justify-center text-[10px] group-hover:scale-105 transition-transform"><i class="fa-solid fa-shield-halved"></i></span>
                        <span>Algemeen & Info</span>
                    </span>
                    <i class="fa-solid fa-chevron-down text-[9px] transition-transform duration-200" :class="openCategory === 'algemeen' ? 'rotate-180' : ''" @if(($activeCategory ?? '') === 'algemeen') style="transform: rotate(180deg);" @endif></i>
                </button>
                <div x-show="openCategory === 'algemeen'" x-collapse x-cloak @if(($activeCategory ?? '') !== 'algemeen') style="display: none;" @endif class="space-y-0.5 pl-1">
                    <a href="{{ route('news.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('news.*') ? 'nav-item-active bg-indigo-50/90 dark:bg-purple-950/60 text-indigo-700 dark:text-purple-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                        <i class="fa-solid fa-newspaper w-4 text-center text-purple-600 dark:text-purple-400"></i>
                        <span>Kliniek Nieuws</span>
                    </a>
                    <a href="{{ route('newspaper.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('newspaper.*') ? 'nav-item-active bg-indigo-50/90 dark:bg-purple-950/60 text-indigo-700 dark:text-purple-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                        <i class="fa-solid fa-file-lines w-4 text-center text-indigo-500 dark:text-indigo-400"></i>
                        <span>Het Afdelingsblad</span>
                    </a>
                    <a href="{{ route('radio.index') }}" class="flex items-center justify-between px-3 py-2 rounded-lg transition {{ request()->routeIs('radio.*') ? 'nav-item-active bg-indigo-50/90 dark:bg-purple-950/60 text-indigo-700 dark:text-purple-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                        <div class="flex items-center gap-3">
                            <i class="fa-solid fa-radio w-4 text-center text-purple-500 dark:text-purple-400"></i>
                            <span>TBS FM (Radio)</span>
                        </div>
                        <span class="inline-flex items-center gap-1 text-[9px] font-black uppercase text-rose-600 dark:text-rose-400 bg-rose-50 dark:bg-rose-950/60 px-1.5 py-0.5 rounded border border-rose-200/60 dark:border-rose-900">
                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500 animate-pulse"></span>
                            Live
                        </span>
                    </a>
                    <a href="{{ route('timeline.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('timeline.*') ? 'nav-item-active bg-indigo-50/90 dark:bg-purple-950/60 text-indigo-700 dark:text-purple-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                        <i class="fa-solid fa-bullhorn w-4 text-center text-sky-500 dark:text-sky-400"></i>
                        <span>Tijdlijn (Feed)</span>
                    </a>
                    <a href="{{ route('friends.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('friends.*') ? 'nav-item-active bg-indigo-50/90 dark:bg-purple-950/60 text-indigo-700 dark:text-purple-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                        <i class="fa-solid fa-user-group w-4 text-center text-pink-500 dark:text-pink-400"></i>
                        <span>Vrienden & BFF</span>
                    </a>
                    <a href="{{ route('members.index') }}" class="flex items-center justify-between px-3 py-2 rounded-lg transition {{ request()->routeIs('members.*') ? 'nav-item-active bg-indigo-50/90 dark:bg-purple-950/60 text-indigo-700 dark:text-purple-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                        <div class="flex items-center gap-3">
                            <i class="fa-solid fa-users w-4 text-center text-emerald-500 dark:text-emerald-400"></i>
                            <span>Leden & Online</span>
                        </div>
                        <span class="inline-flex items-center gap-1 text-[9px] font-black uppercase text-emerald-700 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-950/60 px-1.5 py-0.5 rounded-full border border-emerald-200 dark:border-emerald-800">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            <span x-text="(stats.onlineCount !== undefined) ? stats.onlineCount : {{ (int) $onlineCount }}">{{ $onlineCount }}</span>
                        </span>
                    </a>
                    <a href="{{ route('leaderboard.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('leaderboard.*') ? 'nav-item-active bg-indigo-50/90 dark:bg-purple-950/60 text-indigo-700 dark:text-purple-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                        <i class="fa-solid fa-trophy w-4 text-center text-amber-500 dark:text-amber-400"></i>
                        <span>Ranglijsten</span>
                    </a>
                    <a href="{{ route('battle.index') }}" class="flex items-center justify-between px-3 py-2 rounded-lg transition {{ request()->routeIs('battle.*') ? 'nav-item-active bg-indigo-50/90 dark:bg-purple-950/60 text-indigo-700 dark:text-purple-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                        <div class="flex items-center gap-3">
                            <i class="fa-solid fa-bolt w-4 text-center text-purple-600 dark:text-purple-400"></i>
                            <span>24 Uur Battle</span>
                        </div>
                        <span class="inline-flex items-center gap-1 text-[9px] font-black uppercase text-purple-600 dark:text-purple-400 bg-purple-50 dark:bg-purple-950/60 px-1.5 py-0.5 rounded border border-purple-200/60 dark:border-purple-800">
                            Battle
                        </span>
                    </a>
                    <a href="{{ route('prison') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('prison') ? 'nav-item-active bg-indigo-50/90 dark:bg-purple-950/60 text-indigo-700 dark:text-purple-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                        <i class="fa-solid fa-dungeon w-4 text-center text-rose-500 dark:text-rose-400"></i>
                        <span>Isoleercel</span>
                    </a>
                    <a href="{{ route('roadmap.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('roadmap.*') ? 'nav-item-active bg-indigo-50/90 dark:bg-purple-950/60 text-indigo-700 dark:text-purple-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                        <i class="fa-solid fa-compass w-4 text-center text-amber-500 dark:text-amber-400"></i>
                        <span>Toekomst & Roadmap</span>
                    </a>
                </div>
            </div>

            <!-- 2. BENDE & GANGS -->
            <div class="space-y-1">
                <button @click="toggleCategory('bende')" 
                        type="button" 
                        class="w-full flex items-center justify-between px-3 py-2 text-[10px] font-extrabold uppercase tracking-wider text-slate-500 dark:text-slate-400 hover:text-purple-600 dark:hover:text-purple-300 hover:bg-purple-50/50 dark:hover:bg-purple-950/20 rounded-xl transition group">
                    <span class="flex items-center gap-2">
                        <span class="w-5 h-5 rounded-md bg-purple-100 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400 flex items-center justify-center text-[10px] group-hover:scale-105 transition-transform"><i class="fa-solid fa-users"></i></span>
                        <span>Bendes</span>
                    </span>
                    <div class="flex items-center gap-1.5">
                        @if($character->gang)
                            <span class="text-[9px] font-black uppercase text-purple-700 dark:text-purple-300 bg-purple-100 dark:bg-purple-950/80 px-1.5 py-0.2 rounded border border-purple-200 dark:border-purple-800">
                                [{{ $character->gang->tag }}]
                            </span>
                        @endif
                        <i class="fa-solid fa-chevron-down text-[9px] transition-transform duration-200" :class="openCategory === 'bende' ? 'rotate-180' : ''" @if(($activeCategory ?? '') === 'bende') style="transform: rotate(180deg);" @endif></i>
                    </div>
                </button>
                <div x-show="openCategory === 'bende'" x-collapse x-cloak @if(($activeCategory ?? '') !== 'bende') style="display: none;" @endif class="space-y-0.5 pl-1">
                    @if($character->gang)
                        <a href="{{ route('gangs.index', ['tab' => 'mijn_gang']) }}" class="flex items-center justify-between px-3 py-2 rounded-lg transition {{ request()->routeIs('gangs.*') && (!request('tab') || request('tab') === 'mijn_gang') ? 'nav-item-active bg-indigo-50/90 dark:bg-purple-950/60 text-indigo-700 dark:text-purple-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                            <div class="flex items-center gap-3">
                                <i class="fa-solid fa-shield-halved w-4 text-center text-purple-600 dark:text-purple-400"></i>
                                <span>Mijn Bende</span>
                            </div>
                            <span class="text-[10px] font-extrabold text-purple-600 dark:text-purple-400">{{ $character->gang->name }}</span>
                        </a>
                        <a href="{{ route('gangs.index', ['tab' => 'alle_bendes']) }}" class="flex items-center justify-between px-3 py-2 rounded-lg transition {{ request()->routeIs('gangs.*') && request('tab') === 'alle_bendes' ? 'nav-item-active bg-indigo-50/90 dark:bg-purple-950/60 text-indigo-700 dark:text-purple-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                            <div class="flex items-center gap-3">
                                <i class="fa-solid fa-address-book w-4 text-center text-purple-500 dark:text-purple-400"></i>
                                <span>Alle Bendes</span>
                            </div>
                            <span class="text-[9px] font-extrabold uppercase text-slate-500 dark:text-slate-400 bg-slate-100 dark:bg-slate-800 px-1.5 py-0.5 rounded">Lijst</span>
                        </a>
                        <a href="{{ route('gangs.index', ['tab' => 'leden']) }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('gangs.*') && request('tab') === 'leden' ? 'nav-item-active bg-indigo-50/90 dark:bg-purple-950/60 text-indigo-700 dark:text-purple-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                            <i class="fa-solid fa-user-group w-4 text-center text-indigo-500 dark:text-indigo-400"></i>
                            <span>Bende Leden</span>
                        </a>
                        <a href="{{ route('gangs.index', ['tab' => 'oorlog']) }}" class="flex items-center justify-between px-3 py-2 rounded-lg transition {{ request()->routeIs('gangs.*') && request('tab') === 'oorlog' ? 'nav-item-active bg-rose-50/90 dark:bg-rose-950/50 text-rose-700 dark:text-rose-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                            <div class="flex items-center gap-3">
                                <i class="fa-solid fa-crosshairs w-4 text-center text-rose-500 dark:text-rose-400"></i>
                                <span>Bende Oorlog</span>
                            </div>
                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500 animate-pulse"></span>
                        </a>
                        <a href="{{ route('gangs.panden') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('gangs.panden*') ? 'nav-item-active bg-indigo-50/90 dark:bg-purple-950/60 text-indigo-700 dark:text-purple-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                            <i class="fa-solid fa-building w-4 text-center text-purple-600 dark:text-purple-400"></i>
                            <span>Bende Panden</span>
                        </a>
                        <a href="{{ route('gangs.garage') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('gangs.garage*') ? 'nav-item-active bg-indigo-50/90 dark:bg-purple-950/60 text-indigo-700 dark:text-purple-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                            <i class="fa-solid fa-warehouse w-4 text-center text-purple-600 dark:text-purple-400"></i>
                            <span>Bende Garage</span>
                        </a>
                        <a href="{{ route('heists.index', ['tab' => 'gang']) }}" class="flex items-center justify-between px-3 py-2 rounded-lg transition {{ request()->routeIs('heists.*') && request('tab') === 'gang' ? 'nav-item-active bg-indigo-50/90 dark:bg-purple-950/60 text-indigo-700 dark:text-purple-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                            <div class="flex items-center gap-3">
                                <i class="fa-solid fa-masks-theater w-4 text-center text-rose-500 dark:text-rose-400"></i>
                                <span>Bende Heists</span>
                            </div>
                            <span class="text-[9px] font-black uppercase text-amber-500 bg-amber-500/10 px-1.5 py-0.5 rounded border border-amber-500/20">Co-Op</span>
                        </a>
                        <a href="{{ route('gangs.index', ['tab' => 'ranglijst']) }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('gangs.*') && request('tab') === 'ranglijst' ? 'nav-item-active bg-indigo-50/90 dark:bg-purple-950/60 text-indigo-700 dark:text-purple-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                            <i class="fa-solid fa-trophy w-4 text-center text-amber-500 dark:text-amber-400"></i>
                            <span>Bende Ranglijst</span>
                        </a>
                        @if($character->gang->isOfficer($character))
                        <a href="{{ route('gangs.index', ['tab' => 'beheer']) }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('gangs.*') && request('tab') === 'beheer' ? 'nav-item-active bg-indigo-50/90 dark:bg-purple-950/60 text-indigo-700 dark:text-purple-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                            <i class="fa-solid fa-users-gear w-4 text-center text-purple-600 dark:text-purple-400"></i>
                            <span>Bende Beheer</span>
                        </a>
                        @endif
                    @else
                        <a href="{{ route('gangs.index', ['tab' => 'alle_bendes']) }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('gangs.*') && (!request('tab') || request('tab') === 'alle_bendes') ? 'nav-item-active bg-indigo-50/90 dark:bg-purple-950/60 text-indigo-700 dark:text-purple-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                            <i class="fa-solid fa-address-book w-4 text-center text-purple-600 dark:text-purple-400"></i>
                            <span>Alle Bendes</span>
                        </a>
                        <a href="{{ route('gangs.index', ['tab' => 'ranglijst']) }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('gangs.*') && request('tab') === 'ranglijst' ? 'nav-item-active bg-indigo-50/90 dark:bg-purple-950/60 text-indigo-700 dark:text-purple-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                            <i class="fa-solid fa-trophy w-4 text-center text-amber-500 dark:text-amber-400"></i>
                            <span>Bende Ranglijst</span>
                        </a>
                        <a href="{{ route('gangs.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/60">
                            <i class="fa-solid fa-plus w-4 text-center text-emerald-500 dark:text-emerald-400"></i>
                            <span>Bende Oprichten</span>
                        </a>
                    @endif

                </div>
            </div>

            <!-- 🎃 EVENT: HALLOWEEN 2026 -->
            <div class="space-y-1">
                <button @click="toggleCategory('event')" 
                        type="button" 
                        class="w-full flex items-center justify-between px-3 py-2 text-[10px] font-extrabold uppercase tracking-wider text-orange-600 dark:text-orange-400 hover:text-orange-500 hover:bg-orange-50/50 dark:hover:bg-orange-950/20 rounded-xl transition group">
                    <span class="flex items-center gap-2">
                        <span class="w-5 h-5 rounded-md bg-orange-100 dark:bg-orange-950/60 text-orange-600 dark:text-orange-400 flex items-center justify-center text-[10px] group-hover:scale-105 transition-transform"><i class="fa-solid fa-ghost"></i></span>
                        <span class="font-black">Event</span>
                    </span>
                    <div class="flex items-center gap-1.5">
                        <span class="text-[8px] font-black uppercase text-orange-700 dark:text-orange-300 bg-orange-100 dark:bg-orange-950/80 px-1.5 py-0.5 rounded border border-orange-300 dark:border-orange-800 tracking-wider">
                            HALLOWEEN
                        </span>
                        <i class="fa-solid fa-chevron-down text-[9px] transition-transform duration-200" :class="openCategory === 'event' ? 'rotate-180' : ''" @if(($activeCategory ?? '') === 'event') style="transform: rotate(180deg);" @endif></i>
                    </div>
                </button>
                <div x-show="openCategory === 'event'" x-collapse x-cloak @if(($activeCategory ?? '') !== 'event') style="display: none;" @endif class="space-y-0.5 pl-1">
                    <a href="{{ route('halloween.quests') }}" class="flex items-center justify-between px-3 py-2 rounded-lg transition {{ request()->routeIs('halloween.quests*') ? 'nav-item-active bg-orange-500/10 dark:bg-orange-950/40 text-orange-600 dark:text-orange-400 font-bold border border-orange-500/30' : 'text-slate-600 dark:text-slate-400 hover:text-orange-500 hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                        <div class="flex items-center gap-3">
                            <i class="fa-solid fa-scroll w-4 text-center text-orange-500"></i>
                            <span>Halloween Quests</span>
                        </div>
                        <span class="text-[9px] font-bold text-amber-500 bg-amber-500/10 px-1.5 py-0.5 rounded border border-amber-500/20">Quests</span>
                    </a>
                    <a href="{{ route('halloween.shop') }}" class="flex items-center justify-between px-3 py-2 rounded-lg transition {{ request()->routeIs('halloween.shop*') ? 'nav-item-active bg-orange-500/10 dark:bg-orange-950/40 text-orange-600 dark:text-orange-400 font-bold border border-orange-500/30' : 'text-slate-600 dark:text-slate-400 hover:text-orange-500 hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                        <div class="flex items-center gap-3">
                            <i class="fa-solid fa-store w-4 text-center text-purple-500 dark:text-purple-400"></i>
                            <span>Halloween Shop</span>
                        </div>
                        <span class="text-[9px] font-black text-orange-500">
                            {{ number_format($character->pumpkin_seeds ?? 0, 0, ',', '.') }} 🎃
                        </span>
                    </a>
                </div>
            </div>

            <!-- 3. VOORTGANG & GEZONDHEID -->
            <div class="space-y-1">
                <button @click="toggleCategory('voortgang')" 
                        type="button" 
                        class="w-full flex items-center justify-between px-3 py-2 text-[10px] font-extrabold uppercase tracking-wider text-slate-500 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-300 hover:bg-indigo-50/50 dark:hover:bg-indigo-950/20 rounded-xl transition group">
                    <span class="flex items-center gap-2">
                        <span class="w-5 h-5 rounded-md bg-indigo-100 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-[10px] group-hover:scale-105 transition-transform"><i class="fa-solid fa-chart-line"></i></span>
                        <span>Voortgang & Gezondheid</span>
                    </span>
                    <i class="fa-solid fa-chevron-down text-[9px] transition-transform duration-200" :class="openCategory === 'voortgang' ? 'rotate-180' : ''" @if(($activeCategory ?? '') === 'voortgang') style="transform: rotate(180deg);" @endif></i>
                </button>
                <div x-show="openCategory === 'voortgang'" x-collapse x-cloak @if(($activeCategory ?? '') !== 'voortgang') style="display: none;" @endif class="space-y-0.5 pl-1">
                    <a href="{{ route('depot.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('depot.*') ? 'nav-item-active bg-indigo-50/90 dark:bg-purple-950/60 text-indigo-700 dark:text-purple-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                        <i class="fa-solid fa-boxes-stacked w-4 text-center text-purple-500 dark:text-purple-400"></i>
                        <span>Depot (Inventaris)</span>
                    </a>
                    <a href="{{ route('missions.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('missions.*') ? 'nav-item-active bg-indigo-50/90 dark:bg-purple-950/60 text-indigo-700 dark:text-purple-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                        <i class="fa-solid fa-list-check w-4 text-center text-indigo-500 dark:text-indigo-400"></i>
                        <span>Missies & Opdrachten</span>
                    </a>
                    <a href="{{ route('training.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('training.*') ? 'nav-item-active bg-indigo-50/90 dark:bg-purple-950/60 text-indigo-700 dark:text-purple-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                        <i class="fa-solid fa-dumbbell w-4 text-center text-purple-500 dark:text-purple-400"></i>
                        <span>Krachttraining</span>
                    </a>
                    <a href="{{ route('treatment.index') }}" class="flex items-center justify-between px-3 py-2 rounded-lg transition {{ request()->routeIs('treatment.*') ? 'nav-item-active bg-indigo-50/90 dark:bg-purple-950/60 text-indigo-700 dark:text-purple-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                        <div class="flex items-center gap-3">
                            <i class="fa-solid fa-brain w-4 text-center text-rose-500 dark:text-rose-400"></i>
                            <span>Behandelaarsgesprek</span>
                        </div>
                        @if($character->isOnLeave())
                        <span class="bg-emerald-100 dark:bg-emerald-900/60 text-emerald-800 dark:text-emerald-300 text-[9px] font-black uppercase px-1.5 py-0.5 rounded tracking-wide animate-pulse">
                            Verlof!
                        </span>
                        @elseif($character->isSedated())
                        <span class="bg-rose-100 dark:bg-rose-900/60 text-rose-800 dark:text-rose-300 text-[9px] font-black uppercase px-1.5 py-0.5 rounded tracking-wide">
                            Sedatie
                        </span>
                        @endif
                    </a>
                    <a href="{{ route('infirmary.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('infirmary.*') ? 'nav-item-active bg-indigo-50/90 dark:bg-purple-950/60 text-indigo-700 dark:text-purple-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                        <i class="fa-solid fa-hospital-user w-4 text-center text-teal-500 dark:text-teal-400"></i>
                        <span>Ziekenboeg & Pillen</span>
                    </a>
                    <a href="{{ route('daycare.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('daycare.*') ? 'nav-item-active bg-indigo-50/90 dark:bg-purple-950/60 text-indigo-700 dark:text-purple-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                        <i class="fa-solid fa-person-digging w-4 text-center text-amber-500 dark:text-amber-400"></i>
                        <span>Dagbesteding & Werk</span>
                    </a>
                </div>
            </div>

            <!-- 4. ILLEGALE ACTIES & MISDAAD -->
            <div class="space-y-1">
                <button @click="toggleCategory('misdaad')" 
                        type="button" 
                        class="w-full flex items-center justify-between px-3 py-2 text-[10px] font-extrabold uppercase tracking-wider text-slate-500 dark:text-slate-400 hover:text-rose-600 dark:hover:text-rose-300 hover:bg-rose-50/50 dark:hover:bg-rose-950/20 rounded-xl transition group">
                    <span class="flex items-center gap-2">
                        <span class="w-5 h-5 rounded-md bg-rose-100 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 flex items-center justify-center text-[10px] group-hover:scale-105 transition-transform"><i class="fa-solid fa-handcuffs"></i></span>
                        <span>Illegale Acties</span>
                    </span>
                    <i class="fa-solid fa-chevron-down text-[9px] transition-transform duration-200" :class="openCategory === 'misdaad' ? 'rotate-180' : ''" @if(($activeCategory ?? '') === 'misdaad') style="transform: rotate(180deg);" @endif></i>
                </button>
                <div x-show="openCategory === 'misdaad'" x-collapse x-cloak @if(($activeCategory ?? '') !== 'misdaad') style="display: none;" @endif class="space-y-0.5 pl-1">
                    <a href="{{ route('hustle.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('hustle.*') ? 'nav-item-active bg-indigo-50/90 dark:bg-purple-950/60 text-indigo-700 dark:text-purple-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                        <i class="fa-solid fa-hand-holding-dollar w-4 text-center text-emerald-500 dark:text-emerald-400"></i>
                        <span>Hosselen</span>
                    </a>
                    <a href="{{ route('smuggle.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('smuggle.*') ? 'nav-item-active bg-indigo-50/90 dark:bg-purple-950/60 text-indigo-700 dark:text-purple-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                        <i class="fa-solid fa-capsules w-4 text-center text-teal-500 dark:text-teal-400"></i>
                        <span>Dealen & Smokkel</span>
                    </a>
                    <a href="{{ route('extort.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('extort.*') ? 'nav-item-active bg-indigo-50/90 dark:bg-purple-950/60 text-indigo-700 dark:text-purple-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                        <i class="fa-solid fa-person-military-pointing w-4 text-center text-orange-500 dark:text-orange-400"></i>
                        <span>Afpersen</span>
                    </a>
                    <a href="{{ route('lockers.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('lockers.*') ? 'nav-item-active bg-indigo-50/90 dark:bg-purple-950/60 text-indigo-700 dark:text-purple-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                        <i class="fa-solid fa-id-card w-4 text-center text-blue-500 dark:text-blue-400"></i>
                        <span>Kluisjes Kraken</span>
                    </a>
                    <a href="{{ route('droppings.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('droppings.*') ? 'nav-item-active bg-indigo-50/90 dark:bg-purple-950/60 text-indigo-700 dark:text-purple-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                        <i class="fa-solid fa-plane-arrival w-4 text-center text-emerald-500 dark:text-emerald-400"></i>
                        <span>Tennisbal & Drones</span>
                    </a>
                    <a href="{{ route('tunnel.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('tunnel.*') ? 'nav-item-active bg-indigo-50/90 dark:bg-purple-950/60 text-indigo-700 dark:text-purple-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                        <i class="fa-solid fa-person-digging w-4 text-center text-amber-500 dark:text-amber-400"></i>
                        <span>Uitbraaktunnel</span>
                    </a>
                    <a href="{{ route('heists.index') }}" class="flex items-center justify-between px-3 py-2 rounded-lg transition {{ request()->routeIs('heists.*') ? 'nav-item-active bg-indigo-50/90 dark:bg-purple-950/60 text-indigo-700 dark:text-purple-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                        <div class="flex items-center gap-3">
                            <i class="fa-solid fa-masks-theater w-4 text-center text-rose-500 dark:text-rose-400"></i>
                            <span>Grote Heists</span>
                        </div>
                        <span class="bg-rose-100 dark:bg-rose-900/60 text-rose-700 dark:text-rose-300 text-[9px] font-black uppercase px-1.5 py-0.5 rounded tracking-wide animate-pulse">
                            Nieuw
                        </span>
                    </a>
                </div>
            </div>

            <!-- 5. BONUS & KLUIZEN -->
            <div class="space-y-1">
                <button @click="toggleCategory('bonus')" 
                        type="button" 
                        class="w-full flex items-center justify-between px-3 py-2 text-[10px] font-extrabold uppercase tracking-wider text-slate-500 dark:text-slate-400 hover:text-amber-600 dark:hover:text-amber-300 hover:bg-amber-50/50 dark:hover:bg-amber-950/20 rounded-xl transition group">
                    <span class="flex items-center gap-2">
                        <span class="w-5 h-5 rounded-md bg-amber-100 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center text-[10px] group-hover:scale-105 transition-transform"><i class="fa-solid fa-gift"></i></span>
                        <span>Bonus & Kluizen</span>
                    </span>
                    <i class="fa-solid fa-chevron-down text-[9px] transition-transform duration-200" :class="openCategory === 'bonus' ? 'rotate-180' : ''" @if(($activeCategory ?? '') === 'bonus') style="transform: rotate(180deg);" @endif></i>
                </button>
                <div x-show="openCategory === 'bonus'" x-collapse x-cloak @if(($activeCategory ?? '') !== 'bonus') style="display: none;" @endif class="space-y-0.5 pl-1">
                    <a href="{{ route('daily.index') }}" class="flex items-center justify-between px-3 py-2 rounded-lg transition {{ request()->routeIs('daily.*') ? 'nav-item-active bg-indigo-50/90 dark:bg-purple-950/60 text-indigo-700 dark:text-purple-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                        <div class="flex items-center gap-3">
                            <i class="fa-solid fa-dharmachakra w-4 text-center text-amber-500 dark:text-amber-400"></i>
                            <span>Rad van Fortuin</span>
                        </div>
                        @if($character->canDailySpin())
                        <span class="bg-amber-100 dark:bg-amber-900/60 text-amber-700 dark:text-amber-300 text-[9px] font-black uppercase px-1.5 py-0.5 rounded tracking-wide animate-pulse">
                            Gratis
                        </span>
                        @endif
                    </a>
                    <a href="{{ route('vault.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('vault.*') ? 'nav-item-active bg-indigo-50/90 dark:bg-purple-950/60 text-indigo-700 dark:text-purple-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                        <i class="fa-solid fa-vault w-4 text-center text-purple-500 dark:text-purple-400"></i>
                        <span>MedicijnKluis (Kraak)</span>
                    </a>
                    <a href="{{ route('packages.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('packages.*') ? 'nav-item-active bg-indigo-50/90 dark:bg-purple-950/60 text-indigo-700 dark:text-purple-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                        <i class="fa-solid fa-box-open w-4 text-center text-amber-500 dark:text-amber-400"></i>
                        <span>Koffers & Pakketjes</span>
                    </a>
                    <a href="{{ route('pickpocket.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('pickpocket.*') ? 'nav-item-active bg-indigo-50/90 dark:bg-purple-950/60 text-indigo-700 dark:text-purple-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                        <i class="fa-solid fa-mask w-4 text-center text-indigo-500 dark:text-indigo-400"></i>
                        <span>Zakkenrollen</span>
                    </a>
                    <a href="{{ route('hacking.index') }}" class="flex items-center justify-between px-3 py-2 rounded-lg transition {{ request()->routeIs('hacking.*') ? 'nav-item-active bg-emerald-50/90 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                        <div class="flex items-center gap-3">
                            <i class="fa-solid fa-terminal w-4 text-center text-emerald-500 dark:text-emerald-400"></i>
                            <span>Kliniek Terminal</span>
                        </div>
                        @if($character->canDailyHack())
                        <span class="bg-emerald-100 dark:bg-emerald-900/60 text-emerald-700 dark:text-emerald-300 text-[9px] font-black uppercase px-1.5 py-0.5 rounded tracking-wide animate-pulse">
                            Hack
                        </span>
                        @endif
                    </a>
                </div>
            </div>

            <!-- 6. MINIGAMES & SPECIALS -->
            <div class="space-y-1">
                <button @click="toggleCategory('specials')" 
                        type="button" 
                        class="w-full flex items-center justify-between px-3 py-2 text-[10px] font-extrabold uppercase tracking-wider text-slate-500 dark:text-slate-400 hover:text-fuchsia-600 dark:hover:text-fuchsia-300 hover:bg-fuchsia-50/50 dark:hover:bg-fuchsia-950/20 rounded-xl transition group">
                    <span class="flex items-center gap-2">
                        <span class="w-5 h-5 rounded-md bg-fuchsia-100 dark:bg-fuchsia-950/60 text-fuchsia-600 dark:text-fuchsia-400 flex items-center justify-center text-[10px] group-hover:scale-105 transition-transform"><i class="fa-solid fa-gamepad"></i></span>
                        <span>Minigames & Specials</span>
                    </span>
                    <div class="flex items-center gap-1.5">
                        <i class="fa-solid fa-chevron-down text-[9px] transition-transform duration-200" :class="openCategory === 'specials' ? 'rotate-180' : ''" @if(($activeCategory ?? '') === 'specials') style="transform: rotate(180deg);" @endif></i>
                    </div>
                </button>
                <div x-show="openCategory === 'specials'" x-collapse x-cloak @if(($activeCategory ?? '') !== 'specials') style="display: none;" @endif class="space-y-0.5 pl-1">
                    <a href="{{ route('connect_four.index') }}" class="flex items-center justify-between px-3 py-2 rounded-lg transition {{ request()->routeIs('connect_four.*') ? 'nav-item-active bg-indigo-50/90 dark:bg-purple-950/60 text-indigo-700 dark:text-purple-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                        <div class="flex items-center gap-3">
                            <i class="fa-solid fa-chess-board w-4 text-center text-emerald-500 dark:text-emerald-400"></i>
                            <span>4 op 'n Rij (Arjan)</span>
                        </div>
                        <span class="bg-emerald-100 dark:bg-emerald-900/60 text-emerald-700 dark:text-emerald-300 text-[9px] font-black uppercase px-1.5 py-0.5 rounded tracking-wide">
                            OOHPA
                        </span>
                    </a>
                    <a href="{{ route('true_crime.index') }}" class="flex items-center justify-between px-3 py-2 rounded-lg transition {{ request()->routeIs('true_crime.*') ? 'nav-item-active bg-indigo-50/90 dark:bg-purple-950/60 text-indigo-700 dark:text-purple-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                        <div class="flex items-center gap-3">
                            <i class="fa-solid fa-fingerprint w-4 text-center text-purple-500 dark:text-purple-400"></i>
                            <span>True Crime (Caroline)</span>
                        </div>
                    </a>
                    <a href="{{ route('memory.index') }}" class="flex items-center justify-between px-3 py-2 rounded-lg transition {{ request()->routeIs('memory.*') ? 'nav-item-active bg-indigo-50/90 dark:bg-purple-950/60 text-indigo-700 dark:text-purple-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                        <div class="flex items-center gap-3">
                            <i class="fa-solid fa-brain w-4 text-center text-pink-500 dark:text-pink-400"></i>
                            <span>Memory</span>
                        </div>
                        <span class="bg-gradient-to-r from-pink-500 to-rose-500 text-white text-[9px] font-black uppercase px-2 py-0.5 rounded-full tracking-wide shadow-xs">
                            Nieuw
                        </span>
                    </a>
                </div>
            </div>

            <!-- 7. GEWELD & STRIJD -->
            <div class="space-y-1">
                <button @click="toggleCategory('geweld')" 
                        type="button" 
                        class="w-full flex items-center justify-between px-3 py-2 text-[10px] font-extrabold uppercase tracking-wider text-slate-500 dark:text-slate-400 hover:text-red-600 dark:hover:text-red-300 hover:bg-red-50/50 dark:hover:bg-red-950/20 rounded-xl transition group">
                    <span class="flex items-center gap-2">
                        <span class="w-5 h-5 rounded-md bg-red-100 dark:bg-red-950/60 text-red-600 dark:text-red-400 flex items-center justify-center text-[10px] group-hover:scale-105 transition-transform"><i class="fa-solid fa-fire"></i></span>
                        <span>Geweld & Strijd</span>
                    </span>
                    <i class="fa-solid fa-chevron-down text-[9px] transition-transform duration-200" :class="openCategory === 'geweld' ? 'rotate-180' : ''" @if(($activeCategory ?? '') === 'geweld') style="transform: rotate(180deg);" @endif></i>
                </button>
                <div x-show="openCategory === 'geweld'" x-collapse x-cloak @if(($activeCategory ?? '') !== 'geweld') style="display: none;" @endif class="space-y-0.5 pl-1">
                    <a href="{{ route('fight.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('fight.*') ? 'nav-item-active bg-indigo-50/90 dark:bg-purple-950/60 text-indigo-700 dark:text-purple-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                        <i class="fa-solid fa-skull w-4 text-center text-rose-500 dark:text-rose-400"></i>
                        <span>Respectlijst</span>
                    </a>
                    <a href="{{ route('theft.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('theft.*') ? 'nav-item-active bg-indigo-50/90 dark:bg-purple-950/60 text-indigo-700 dark:text-purple-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                        <i class="fa-solid fa-briefcase w-4 text-center text-slate-500 dark:text-slate-400"></i>
                        <span>Klusjes & Diefstal</span>
                    </a>
                    <a href="{{ route('combat_training.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('combat_training.*') ? 'nav-item-active bg-indigo-50/90 dark:bg-purple-950/60 text-indigo-700 dark:text-purple-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                        <i class="fa-solid fa-bullseye w-4 text-center text-indigo-500 dark:text-indigo-400"></i>
                        <span>Vechtoefening</span>
                    </a>
                    <a href="{{ route('armwrestling.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('armwrestling.*') ? 'nav-item-active bg-indigo-50/90 dark:bg-purple-950/60 text-indigo-700 dark:text-purple-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                        <i class="fa-solid fa-hand-back-fist w-4 text-center text-amber-500 dark:text-amber-400"></i>
                        <span>Armpjedruk Arena</span>
                    </a>
                    <a href="{{ route('hitman.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('hitman.*') ? 'nav-item-active bg-indigo-50/90 dark:bg-purple-950/60 text-indigo-700 dark:text-purple-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                        <i class="fa-solid fa-crosshairs w-4 text-center text-rose-600 dark:text-rose-400"></i>
                        <span>Liquidatie</span>
                    </a>
                    <a href="{{ route('bounties.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('bounties.*') ? 'nav-item-active bg-indigo-50/90 dark:bg-purple-950/60 text-indigo-700 dark:text-purple-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                        <i class="fa-solid fa-skull-crossbones w-4 text-center text-rose-500 dark:text-rose-400"></i>
                        <span>Kopgeld & Premies</span>
                    </a>
                    <a href="{{ route('informants.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('informants.*') ? 'nav-item-active bg-indigo-50/90 dark:bg-purple-950/60 text-indigo-700 dark:text-purple-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                        <i class="fa-solid fa-user-secret w-4 text-center text-slate-500 dark:text-slate-400"></i>
                        <span>Informanten</span>
                    </a>
                    <a href="{{ route('isolation.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('isolation.*') ? 'nav-item-active bg-indigo-50/90 dark:bg-purple-950/60 text-indigo-700 dark:text-purple-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                        <i class="fa-solid fa-ghost w-4 text-center text-purple-500 dark:text-purple-400"></i>
                        <span>Vrijwillige Isolatie</span>
                    </a>
                </div>
            </div>

            <!-- 8. ECONOMIE & GOKKEN -->
            <div class="space-y-1">
                <button @click="toggleCategory('economie')" 
                        type="button" 
                        class="w-full flex items-center justify-between px-3 py-2 text-[10px] font-extrabold uppercase tracking-wider text-slate-500 dark:text-slate-400 hover:text-emerald-600 dark:hover:text-emerald-300 hover:bg-emerald-50/50 dark:hover:bg-emerald-950/20 rounded-xl transition group">
                    <span class="flex items-center gap-2">
                        <span class="w-5 h-5 rounded-md bg-emerald-100 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-[10px] group-hover:scale-105 transition-transform"><i class="fa-solid fa-coins"></i></span>
                        <span>Economie & Gokken</span>
                    </span>
                    <i class="fa-solid fa-chevron-down text-[9px] transition-transform duration-200" :class="openCategory === 'economie' ? 'rotate-180' : ''" @if(($activeCategory ?? '') === 'economie') style="transform: rotate(180deg);" @endif></i>
                </button>
                <div x-show="openCategory === 'economie'" x-collapse x-cloak @if(($activeCategory ?? '') !== 'economie') style="display: none;" @endif class="space-y-0.5 pl-1">
                    <a href="{{ route('bank.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('bank.*') ? 'nav-item-active bg-indigo-50/90 dark:bg-purple-950/60 text-indigo-700 dark:text-purple-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                        <i class="fa-solid fa-building-columns w-4 text-center text-emerald-500 dark:text-emerald-400"></i>
                        <span>De Bank</span>
                    </a>
                    <a href="{{ route('travel.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('travel.*') ? 'nav-item-active bg-indigo-50/90 dark:bg-purple-950/60 text-indigo-700 dark:text-purple-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                        <i class="fa-solid fa-signs-post w-4 text-center text-teal-500 dark:text-teal-400"></i>
                        <span>Verplaatsen</span>
                    </a>
                    <a href="{{ route('casino.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('casino.*', 'rps.*', 'highlow.*', 'numbergame.*', 'roulette.*', 'slots.*') ? 'nav-item-active bg-indigo-50/90 dark:bg-purple-950/60 text-indigo-700 dark:text-purple-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                        <i class="fa-solid fa-dice w-4 text-center text-rose-500 dark:text-rose-400"></i>
                        <span>Casino & Gokhal</span>
                    </a>
                    <a href="{{ route('lottery.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('lottery.*') ? 'nav-item-active bg-amber-50/90 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                        <i class="fa-solid fa-crown w-4 text-center text-amber-500 dark:text-amber-400"></i>
                        <span>Afdelingsloterij</span>
                        <span class="ml-auto bg-amber-400 text-slate-950 font-black text-[9px] px-1.5 py-0.5 rounded uppercase tracking-wider">Pot</span>
                    </a>
                    <a href="{{ route('shop.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('shop.*') ? 'nav-item-active bg-indigo-50/90 dark:bg-purple-950/60 text-indigo-700 dark:text-purple-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                        <i class="fa-solid fa-cart-shopping w-4 text-center text-amber-500 dark:text-amber-400"></i>
                        <span>Kliniek Winkel</span>
                    </a>
                    <a href="{{ route('garage.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('garage.*') ? 'nav-item-active bg-indigo-50/90 dark:bg-purple-950/60 text-indigo-700 dark:text-purple-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                        <i class="fa-solid fa-warehouse w-4 text-center text-cyan-500 dark:text-cyan-400"></i>
                        <span>Smokkel Garage</span>
                    </a>
                    <a href="{{ route('perks.index') }}" class="flex items-center justify-between px-3 py-2 rounded-lg transition {{ request()->routeIs('perks.*') ? 'nav-item-active bg-indigo-50/90 dark:bg-purple-950/60 text-indigo-700 dark:text-purple-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                        <div class="flex items-center gap-3">
                            <i class="fa-solid fa-award w-4 text-center text-amber-500 dark:text-amber-400"></i>
                            <span>Karakter Perks</span>
                        </div>
                        <span class="bg-amber-100 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 text-[9px] font-black uppercase px-1.5 py-0.5 rounded tracking-wide">
                            ⭐ Perks
                        </span>
                    </a>
                    <a href="{{ route('marketplace.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('marketplace.*') ? 'nav-item-active bg-indigo-50/90 dark:bg-purple-950/60 text-indigo-700 dark:text-purple-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                        <i class="fa-solid fa-shop w-4 text-center text-purple-600 dark:text-purple-400"></i>
                        <span>Marktplaats</span>
                    </a>
                </div>
            </div>

            <!-- 6. HELPTOOL, PROFIEL & ACCOUNT (Direct boven Veilig Uitloggen) -->
            <div class="space-y-0.5 pt-3 mt-2 border-t border-slate-100 dark:border-slate-800/60">
                <a href="{{ route('faq.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('faq.*') ? 'nav-item-active bg-indigo-50/90 dark:bg-purple-950/60 text-indigo-700 dark:text-purple-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                    <i class="fa-solid fa-headset w-4 text-center text-indigo-500 dark:text-indigo-400"></i>
                    <span>Helptool & FAQ</span>
                </a>
                <a href="{{ route('help.tickets.index') }}" class="flex items-center justify-between px-3 py-2 rounded-lg transition {{ request()->routeIs('help.tickets.*') ? 'nav-item-active bg-indigo-50/90 dark:bg-purple-950/60 text-indigo-700 dark:text-purple-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                    <div class="flex items-center gap-3">
                        <i class="fa-solid fa-ticket w-4 text-center text-purple-500 dark:text-purple-400"></i>
                        <span>Support Tickets</span>
                    </div>
                    @php
                        $userOpenTicketsDesk = Auth::check() ? Auth::user()->openSupportTicketsCount() : 0;
                    @endphp
                    @if($userOpenTicketsDesk > 0)
                    <span class="text-[9px] bg-emerald-500 text-white font-black px-1.5 py-0.5 rounded uppercase">Reactie!</span>
                    @endif
                </a>
                <a href="{{ route('help.tickets.create') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('help.tickets.create') ? 'nav-item-active bg-indigo-50/90 dark:bg-purple-950/60 text-indigo-700 dark:text-purple-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                    <i class="fa-solid fa-plus w-4 text-center text-emerald-500 dark:text-emerald-400"></i>
                    <span>Nieuw Ticket Aanmaken</span>
                </a>
                <a href="{{ route('profile.me') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('profile.*') ? 'nav-item-active bg-indigo-50/90 dark:bg-purple-950/60 text-indigo-700 dark:text-purple-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                    <i class="fa-solid fa-id-badge w-4 text-center text-blue-500 dark:text-blue-400"></i>
                    <span>Mijn Profiel</span>
                </a>
                <a href="{{ route('vip.index') }}" class="flex items-center justify-between px-3 py-2 rounded-lg transition {{ request()->routeIs('vip.*') ? 'nav-item-active bg-amber-50/90 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 font-bold' : 'text-amber-700 dark:text-amber-400 hover:text-amber-900 dark:hover:text-amber-300 hover:bg-amber-50/60 dark:hover:bg-amber-950/30' }}">
                    <div class="flex items-center gap-3">
                        <i class="fa-solid fa-crown w-4 text-center text-amber-500 dark:text-amber-400"></i>
                        <span class="font-extrabold">VIP Shop & Punten</span>
                    </div>
                </a>
                <a href="{{ route('sponsor.index') }}" class="flex items-center justify-between px-3 py-2 rounded-lg transition {{ request()->routeIs('sponsor.*') ? 'nav-item-active bg-purple-50/90 dark:bg-purple-950/60 text-purple-800 dark:text-purple-300 font-bold' : 'text-purple-700 dark:text-purple-300 hover:text-purple-900 dark:hover:text-purple-200 hover:bg-purple-50/60 dark:hover:bg-purple-950/30' }}">
                    <div class="flex items-center gap-3">
                        <i class="fa-solid fa-tv w-4 text-center text-purple-600 dark:text-purple-400"></i>
                        <span class="font-extrabold">Sponsorkamer</span>
                    </div>
                    <span class="text-[9px] bg-purple-600 text-white font-black px-1.5 py-0.5 rounded uppercase">Gratis VIP</span>
                </a>
                <a href="{{ route('settings.account') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('settings.*') ? 'nav-item-active bg-indigo-50/90 dark:bg-purple-950/60 text-indigo-700 dark:text-purple-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                    <i class="fa-solid fa-gear w-4 text-center text-slate-500 dark:text-slate-400"></i>
                    <span>Account Instellingen</span>
                </a>
                @if(Auth::check() && Auth::user()->isStaff())
                <a href="{{ route('admin.dashboard') }}" class="flex items-center justify-between px-3 py-2 rounded-lg transition {{ request()->routeIs('admin.*') ? 'nav-item-active bg-purple-100/90 dark:bg-purple-950/70 text-purple-900 dark:text-purple-300 font-bold' : 'text-purple-700 dark:text-purple-300 hover:text-purple-950 dark:hover:text-purple-200 hover:bg-purple-50 dark:hover:bg-purple-950/30' }}">
                    <div class="flex items-center gap-3">
                        <i class="fa-solid fa-shield-halved w-4 text-center text-purple-600 dark:text-purple-400"></i>
                        <span class="font-black">Medewerkers Admin</span>
                    </div>
                    <span class="text-[9px] bg-purple-600 text-white font-black px-1.5 py-0.5 rounded uppercase">Staff</span>
                </a>
                @endif
            </div>

        </nav>

        <!-- Sidebar Footer / PWA Install & Logout -->
        <div class="p-3 border-t border-slate-100 dark:border-slate-800/80 bg-slate-50/50 dark:bg-slate-900/60 mt-auto">
            <button type="button" onclick="if(window.tbsOpenPwaModal){window.tbsOpenPwaModal();}" class="w-full flex items-center justify-between gap-2 px-3 py-2 rounded-xl text-xs font-bold text-purple-700 dark:text-purple-300 bg-purple-50 hover:bg-purple-100 dark:bg-purple-950/60 dark:hover:bg-purple-900/60 mb-2 transition cursor-pointer border border-purple-200/60 dark:border-purple-800/40">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-mobile-screen-button text-purple-600 dark:text-purple-400"></i>
                    <span>Download App</span>
                </div>
                <span class="text-[9px] bg-purple-600 text-white font-black px-1.5 py-0.5 rounded uppercase tracking-wider">App</span>
            </button>
            <form method="POST" action="{{ route('logout') }}"
                  data-confirm="Weet je zeker dat je wilt uitloggen uit de TBS Kliniek?"
                  data-confirm-title="Veilig Uitloggen"
                  data-confirm-btn="Ja, Uitloggen"
                  data-confirm-type="logout"
                  data-confirm-icon="fa-right-from-bracket">
                @csrf
                <button type="submit" class="w-full flex items-center justify-center gap-2 px-3 py-2.5 rounded-xl text-xs font-bold text-slate-600 dark:text-slate-400 hover:text-rose-600 dark:hover:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/30 transition cursor-pointer">
                    <i class="fa-solid fa-right-from-bracket"></i>
                    <span>Veilig Uitloggen</span>
                </button>
            </form>
        </div>
    </aside>

    <!-- MOBILE FULL SLIDE-OVER NAVIGATION DRAWER -->
    @include('partials.mobile_drawer')
    @endif

    <!-- MAIN APP WRAPPER -->
    <div class="flex-1 flex flex-col min-w-0">
        @if(isset($character))
        <!-- NATIVE MOBILE TOP-HUD (Compact Single-Line, Max Gaming Screen Space) -->
        <header class="lg:hidden bg-white/95 dark:bg-[#110726]/95 backdrop-blur-md text-slate-800 dark:text-slate-100 px-2 py-2 flex items-center justify-between border-b border-slate-200/90 dark:border-purple-900/40 sticky top-0 z-40 shadow-xs select-none" style="padding-top: max(0.5rem, env(safe-area-inset-top));">
            
            <!-- 1. Left: Back, Avatar, & Level/Name -->
            <div class="flex items-center gap-1.5 shrink-0 min-w-0">
                <!-- WebApp Terug Knop -->
                <button type="button" 
                        onclick="window.tbsGoBack()" 
                        class="w-8 h-8 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-purple-950/70 dark:hover:bg-purple-900/70 text-slate-700 dark:text-purple-200 flex items-center justify-center text-xs transition cursor-pointer shrink-0 active:scale-90 border border-slate-200/60 dark:border-purple-800/40" 
                        title="Vorige pagina (Terug)"
                        aria-label="Terug">
                    <i class="fa-solid fa-arrow-left text-[11px]"></i>
                </button>

                <!-- Avatar + Level & Naam (Tap = Profiel) -->
                @php
                    $mobileTopBorder = $character ? $character->getEquippedBorderInfo() : null;
                @endphp
                <a href="{{ route('profile.me') }}" class="flex items-center gap-1.5 min-w-0 group" title="Bekijk profiel">
                    <div class="relative shrink-0 flex items-center justify-center">
                        <div class="w-7 h-7 rounded-full p-[1px] flex items-center justify-center overflow-hidden {{ $mobileTopBorder ? str_replace('ring-4', 'ring-2', $mobileTopBorder['ring']) : 'ring-1 ring-purple-300 dark:ring-purple-700' }}">
                            <img src="{{ $character->avatar_url }}" alt="{{ $character->name }}" class="w-full h-full rounded-full object-cover">
                        </div>
                        <span class="absolute -bottom-0.5 -right-0.5 w-2 h-2 rounded-full bg-emerald-500 ring-1 ring-white dark:ring-[#110726]"></span>
                    </div>
                    <div class="flex flex-col min-w-0 max-w-[50px] xs:max-w-[70px]">
                        <span class="text-[11px] font-black text-slate-900 dark:text-white truncate leading-none">{{ $character->name }}</span>
                        <span class="text-[9px] font-extrabold text-purple-600 dark:text-purple-400 leading-none mt-0.5">LVL {{ $character->level }}</span>
                    </div>
                </a>
            </div>

            <!-- 2. Center: Core Vitals (⚡ Energy, ❤️ Health, 💰 Cash) -->
            <div class="flex items-center gap-1 shrink-0">
                <!-- Energy Pill (Tap = Quick sheet of boost) -->
                <button type="button" 
                        @click="mobileQuickSheetOpen = true"
                        class="flex flex-col justify-center bg-slate-100/90 hover:bg-slate-200 dark:bg-purple-950/60 dark:hover:bg-purple-900/60 border border-slate-200/80 dark:border-purple-800/40 px-1.5 py-1 rounded-xl cursor-pointer active:scale-95 transition"
                        title="Energie (klik voor acties)">
                    <div class="flex items-center gap-1 text-[10px] leading-none font-extrabold text-emerald-600 dark:text-emerald-400">
                        <i class="fa-solid fa-bolt text-[9px] text-emerald-500"></i>
                        <span class="font-mono text-[10px]" x-text="stats.energy">{{ $character->energy }}</span><span class="text-[8px] text-slate-400 dark:text-purple-300/60 font-mono">/<span x-text="stats.max_energy">{{ $character->max_energy }}</span></span>
                    </div>
                    <div class="w-10 xs:w-12 bg-slate-200/80 dark:bg-purple-900/50 rounded-full h-1 mt-1 overflow-hidden">
                        <div class="bg-emerald-500 h-full rounded-full transition-all duration-300" :style="'width: ' + Math.max(0, Math.min(100, stats.energy_pct)) + '%'" style="width: {{ min(100, max(0, round(($character->energy / max(1, $character->max_energy)) * 100))) }}%"></div>
                    </div>
                </button>

                <!-- Health Pill (Tap = Quick sheet of ziekenhuis) -->
                <button type="button" 
                        @click="mobileQuickSheetOpen = true"
                        class="flex flex-col justify-center bg-slate-100/90 hover:bg-slate-200 dark:bg-purple-950/60 dark:hover:bg-purple-900/60 border border-slate-200/80 dark:border-purple-800/40 px-1.5 py-1 rounded-xl cursor-pointer active:scale-95 transition"
                        title="Gezondheid (klik voor genezen)">
                    <div class="flex items-center gap-1 text-[10px] leading-none font-extrabold text-rose-600 dark:text-rose-400">
                        <i class="fa-solid fa-heart text-[9px] text-rose-500"></i>
                        <span class="font-mono text-[10px]" x-text="stats.health">{{ $character->health }}</span><span class="text-[8px] text-slate-400 dark:text-purple-300/60 font-mono">/<span x-text="stats.max_health">{{ $character->max_health }}</span></span>
                    </div>
                    <div class="w-10 xs:w-12 bg-slate-200/80 dark:bg-purple-900/50 rounded-full h-1 mt-1 overflow-hidden">
                        <div class="bg-rose-500 h-full rounded-full transition-all duration-300" :style="'width: ' + Math.max(0, Math.min(100, stats.health_pct)) + '%'" style="width: {{ min(100, max(0, round(($character->health / max(1, $character->max_health)) * 100))) }}%"></div>
                    </div>
                </button>

                <!-- Cash Pill (Tap = Quick sheet of bank storten) -->
                <button type="button" 
                        @click="mobileQuickSheetOpen = true"
                        class="hidden xs:flex items-center gap-1 bg-slate-100/90 hover:bg-slate-200 dark:bg-purple-950/60 dark:hover:bg-purple-900/60 border border-slate-200/80 dark:border-purple-800/40 px-1.5 py-1.5 rounded-xl cursor-pointer active:scale-95 transition"
                        title="Contant geld (klik om te storten)">
                    <i class="fa-solid fa-money-bill-wave text-[10px] text-emerald-500"></i>
                    <span class="font-black text-slate-900 dark:text-white text-[10px] font-mono leading-none tracking-tight" x-text="stats.cashFormatted">€{{ number_format($character->cash, 0, ',', '.') }}</span>
                </button>
            </div>

            <!-- 3. Right: Refresh, Live Chat & Menu Toggle -->
            <div class="flex items-center gap-1 shrink-0">
                <!-- Direct Soft Reload Button -->
                <button type="button" 
                        onclick="window.tbsReloadPage(this)" 
                        class="w-8 h-8 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-purple-950/70 dark:hover:bg-purple-900/70 text-slate-700 dark:text-purple-200 flex items-center justify-center text-xs transition cursor-pointer shrink-0 active:scale-90 border border-slate-200/60 dark:border-purple-800/40" 
                        title="Pagina vernieuwen" 
                        aria-label="Vernieuwen">
                    <i class="fa-solid fa-rotate-right text-[11px]"></i>
                </button>

                <!-- Chat Button with Notification Ping -->
                @php
                    $unreadChatCount = $character ? $character->unreadDirectMessagesCount() : 0;
                @endphp
                <a href="{{ route('chat.index') }}" class="relative w-8 h-8 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-purple-950/70 dark:hover:bg-purple-900/70 text-slate-700 dark:text-purple-200 flex items-center justify-center text-xs transition cursor-pointer shrink-0 active:scale-90 border border-slate-200/60 dark:border-purple-800/40" title="Live Chat & DMs" aria-label="Live Chat">
                    <i class="fa-solid fa-comments text-xs"></i>
                    @if($unreadChatCount > 0)
                        <span class="absolute top-1 right-1 w-2 h-2 rounded-full bg-emerald-500 ring-2 ring-white animate-ping"></span>
                        <span class="absolute top-1 right-1 w-2 h-2 rounded-full bg-emerald-500 ring-2 ring-white"></span>
                    @endif
                </a>

                <!-- Hamburger Drawer Trigger -->
                <button @click="mobileMenuOpen = true" class="w-8 h-8 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-purple-950/70 dark:hover:bg-purple-900/70 text-slate-700 dark:text-purple-200 flex items-center justify-center text-xs transition cursor-pointer shrink-0 active:scale-90 border border-slate-200/60 dark:border-purple-800/40" aria-label="Open menu">
                    <i class="fa-solid fa-bars text-sm"></i>
                </button>
            </div>
        </header>

        <!-- TOP MODERN WHITE HEADER BAR (DESKTOP) - Styled after Reference Design in Site Colors -->
        <header class="hidden lg:block bg-white dark:bg-[#110726] border-b border-slate-200/90 dark:border-purple-900/40 sticky top-0 z-30 shadow-xs">
            <div class="w-full px-4 sm:px-6 lg:px-6 xl:px-8 2xl:px-10 h-20 flex items-center justify-between gap-2 xl:gap-4 min-w-0">
                
                <!-- 1. LEFT: WebApp Navigation Controls & Player Profile Summary -->
                <div class="flex items-center gap-3 shrink-0">
                    <!-- WebApp Navigation Buttons (Back & Refresh for Standalone App) -->
                    <div class="flex items-center gap-1 shrink-0 mr-0.5">
                        <button type="button" 
                                onclick="window.tbsGoBack()" 
                                class="w-8 h-8 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-purple-950/60 dark:hover:bg-purple-900/60 text-slate-700 dark:text-purple-300 flex items-center justify-center text-xs transition cursor-pointer border border-slate-200/60 dark:border-purple-800/40 active:scale-95 shadow-2xs" 
                                title="Vorige pagina (Terug)"
                                aria-label="Terug">
                            <i class="fa-solid fa-arrow-left"></i>
                        </button>
                        <button type="button" 
                                onclick="window.tbsReloadPage(this)" 
                                class="w-8 h-8 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-purple-950/60 dark:hover:bg-purple-900/60 text-slate-700 dark:text-purple-300 flex items-center justify-center text-xs transition cursor-pointer border border-slate-200/60 dark:border-purple-800/40 active:scale-95 shadow-2xs" 
                                title="Pagina vernieuwen"
                                aria-label="Vernieuwen">
                            <i class="fa-solid fa-rotate-right"></i>
                        </button>
                    </div>

                    <a href="{{ route('profile.me') }}" class="relative group shrink-0" title="Bekijk mijn profiel">
                        @php
                            $headerBorder = $character->getEquippedBorderInfo();
                        @endphp
                        <div class="relative">
                            <div class="w-12 h-12 rounded-full p-0.5 transition-all bg-white dark:bg-purple-950/60 {{ $headerBorder ? $headerBorder['ring'] . ' ' . $headerBorder['glow'] : 'ring-2 ring-indigo-500/30 group-hover:ring-indigo-500 shadow-2xs' }}">
                                <img src="{{ $character->avatar_url }}" alt="{{ $character->name }}" class="w-full h-full rounded-full object-cover bg-slate-100">
                            </div>
                            @if($headerBorder)
                                <span class="absolute -bottom-1 -right-1 text-sm select-none drop-shadow leading-none animate-bounce" style="animation-duration: 2.5s;" title="{{ $headerBorder['name'] }} Frame">
                                    {{ $headerBorder['emoji'] }}
                                </span>
                            @endif
                        </div>
                    </a>
                    <div class="flex flex-col gap-1 min-w-0">
                        <div class="flex items-center gap-2">
                            <a href="{{ route('profile.me') }}" class="font-black text-slate-900 dark:text-white text-sm hover:text-indigo-600 dark:hover:text-purple-400 transition leading-tight py-0.5 truncate max-w-[140px]">
                                {{ $character->name }}
                            </a>
                            <span class="bg-slate-100 dark:bg-purple-950/60 text-slate-700 dark:text-purple-300 border border-slate-200/80 dark:border-purple-800/40 font-black text-[9px] px-2 py-0.5 rounded uppercase tracking-wider shadow-2xs shrink-0">
                                LVL {{ $character->level }}
                            </span>
                        </div>
                        <div class="w-36 sm:w-44 bg-slate-100 dark:bg-purple-950/60 rounded-full h-2 overflow-hidden border border-slate-200/80 dark:border-purple-900/40">
                            <div class="bg-indigo-600 dark:bg-purple-500 h-full rounded-full transition-all duration-300" :style="'width: ' + stats.xp_pct + '%'" style="width: {{ $pctXp }}%"></div>
                        </div>
                        <div class="flex items-center justify-between text-[10px] leading-normal pt-0.5">
                            @if($character->gang)
                                <a href="{{ route('gangs.index') }}" class="text-indigo-600 hover:text-indigo-700 dark:text-purple-400 dark:hover:text-purple-300 font-extrabold truncate max-w-[105px] py-0.5 block hover:underline" title="{{ $character->gang->name }}">
                                    {{ $character->gang->name }}
                                </a>
                            @else
                                <span class="text-indigo-600 dark:text-purple-400 font-extrabold truncate max-w-[105px] py-0.5 block" title="{{ $character->title ?? '#TBS-'.$character->id }}">
                                    {{ $character->title ?? '#TBS-'.$character->id }}
                                </span>
                            @endif
                            <span class="font-medium text-slate-400 dark:text-purple-300/70 font-mono py-0.5 shrink-0 whitespace-nowrap" x-text="stats.xpFormatted">
                                {{ number_format($character->xp, 0, ',', '.') }} / {{ number_format($reqXp, 0, ',', '.') }} XP
                            </span>
                        </div>
                    </div>
                </div>

                <!-- VERTICAL DIVIDER -->
                <div class="h-10 w-px bg-slate-200/90 dark:bg-purple-900/40 shrink-0 hidden xl:block"></div>

                <!-- 2. CENTER-LEFT: Clean Energy & Health Dual Filled Pill Bars -->
                <div class="hidden xl:flex flex-col justify-center gap-1.5 w-36 2xl:w-44 shrink-0">
                    <!-- Energy (Clickable to Inventory) -->
                    <a href="{{ route('shop.index', ['tab' => 'inventaris']) }}" 
                       class="group cursor-pointer block"
                       title="Energie: klik voor Inventaris">
                        <div class="flex items-center justify-between text-[10px] font-bold mb-1 leading-none">
                            <span class="group-hover:text-emerald-600 dark:group-hover:text-emerald-400 text-slate-500 dark:text-purple-300/80 font-black uppercase tracking-wider flex items-center gap-1 transition">
                                <i class="fa-solid fa-bolt text-emerald-500 text-[9px]"></i> ENERGIE
                            </span>
                            <span class="text-emerald-600 dark:text-emerald-400 font-extrabold font-mono text-[10px]" x-text="stats.energy + '/' + stats.max_energy">
                                {{ $character->energy }}/{{ $character->max_energy }}
                            </span>
                        </div>
                        <div class="w-full bg-slate-100 dark:bg-purple-950/60 rounded-full h-2 overflow-hidden border border-slate-200/80 dark:border-purple-900/40">
                            <div class="bg-emerald-500 h-full rounded-full transition-all duration-500 ease-out" :class="stats.energy <= 0 ? 'opacity-0 !w-0' : 'opacity-100'" :style="'width: ' + Math.max(0, Math.min(100, stats.energy_pct)) + '%'" style="width: {{ min(100, max(0, round(($character->energy / max(1, $character->max_energy)) * 100))) }}%"></div>
                        </div>
                    </a>

                    <!-- Health (Clickable to Inventory) -->
                    <a href="{{ route('shop.index', ['tab' => 'inventaris']) }}" 
                       class="group cursor-pointer block"
                       title="Gezondheid: klik voor Inventaris">
                        <div class="flex items-center justify-between text-[10px] font-bold mb-1 leading-none">
                            <span class="group-hover:text-rose-600 dark:group-hover:text-rose-400 text-slate-500 dark:text-purple-300/80 font-black uppercase tracking-wider flex items-center gap-1 transition">
                                <i class="fa-solid fa-heart text-rose-500 text-[9px]"></i> GEZONDHEID
                            </span>
                            <span class="text-rose-600 dark:text-rose-400 font-extrabold font-mono text-[10px]" x-text="stats.health + '/' + stats.max_health">
                                {{ $character->health }}/{{ $character->max_health }}
                            </span>
                        </div>
                        <div class="w-full bg-slate-100 dark:bg-purple-950/60 rounded-full h-2 overflow-hidden border border-slate-200/80 dark:border-purple-900/40">
                            <div class="bg-rose-500 h-full rounded-full transition-all duration-500 ease-out" :class="stats.health <= 0 ? 'opacity-0 !w-0' : 'opacity-100'" :style="'width: ' + Math.max(0, Math.min(100, stats.health_pct)) + '%'" style="width: {{ min(100, max(0, round(($character->health / max(1, $character->max_health)) * 100))) }}%"></div>
                        </div>
                    </a>
                </div>

                <!-- VERTICAL DIVIDER -->
                <div class="h-10 w-px bg-slate-200/90 dark:bg-purple-900/40 shrink-0 hidden xl:block"></div>

                <!-- 3. CENTER-RIGHT: Financial Overview (Wallet Icon, Total + Cash & Bank Subtitle) -->
                <a href="{{ route('bank.index') }}" class="flex items-center gap-3 group cursor-pointer shrink-0 hover:opacity-90 transition select-none" title="Totaal Vermogen &bull; Klik voor De Bank">
                    <div class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-purple-950/60 border border-slate-200/90 dark:border-purple-900/40 text-slate-600 dark:text-purple-300 group-hover:bg-indigo-600 dark:group-hover:bg-purple-600 group-hover:text-white group-hover:border-indigo-600 transition flex items-center justify-center text-lg shadow-2xs">
                        <i class="fa-solid fa-wallet"></i>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-base sm:text-lg font-black text-slate-900 dark:text-white tracking-tight leading-none group-hover:text-indigo-600 dark:group-hover:text-purple-400 transition" x-text="stats.totalFormatted">
                            €{{ number_format($character->cash + $character->bank, 0, ',', '.') }}
                        </span>
                        <span class="hidden 2xl:inline text-[10px] font-medium text-slate-400 dark:text-purple-300/70 mt-1 leading-none">
                            <span x-text="stats.cashFormatted">€{{ number_format($character->cash, 0, ',', '.') }}</span> contant &middot; <span x-text="stats.bankFormatted">€{{ number_format($character->bank, 0, ',', '.') }}</span> bank
                        </span>
                    </div>
                </a>

                <!-- VERTICAL DIVIDER -->
                <div class="h-10 w-px bg-slate-200/90 dark:bg-purple-900/40 shrink-0 hidden xl:block"></div>

                <!-- 4. RIGHT: VIP, STARS & RESPECT -->
                <div class="flex items-center gap-3 xl:gap-4 shrink-0">
                    <!-- VIP -->
                    <a href="{{ route('vip.index') }}" class="flex flex-col items-center gap-1 group" title="VIP Punten">
                        <span class="text-[8px] font-black uppercase tracking-wider text-purple-700 dark:text-purple-300 bg-purple-100 dark:bg-purple-950/60 px-1.5 py-0.5 rounded shadow-2xs border border-transparent dark:border-purple-800/40">
                            VIP
                        </span>
                        <div class="flex items-center gap-1 text-slate-900 dark:text-white font-black text-xs sm:text-sm leading-none group-hover:text-purple-600 dark:group-hover:text-purple-400 transition">
                            <i class="fa-solid fa-star text-purple-500 text-xs"></i>
                            <span x-text="stats.vipPointsFormatted">{{ number_format($character->vip_points ?? 0, 0, ',', '.') }}</span>
                        </div>
                    </a>

                    <!-- STARS -->
                    <a href="{{ route('vip.index') }}" class="flex flex-col items-center gap-1 group" title="Sterren">
                        <span class="text-[8px] font-black uppercase tracking-wider text-amber-800 dark:text-amber-300 bg-amber-100 dark:bg-amber-950/60 px-1.5 py-0.5 rounded shadow-2xs border border-transparent dark:border-amber-800/40">
                            STERREN
                        </span>
                        <div class="flex items-center gap-1 text-slate-900 dark:text-white font-black text-xs sm:text-sm leading-none group-hover:text-amber-600 dark:group-hover:text-amber-400 transition">
                            <i class="fa-solid fa-star text-amber-500 text-xs"></i>
                            <span x-text="stats.starsFormatted">{{ number_format($character->stars ?? 0, 0, ',', '.') }}</span>
                        </div>
                    </a>

                    <!-- RESPECT -->
                    <div class="hidden xl:flex flex-col items-center gap-1 cursor-default select-none" title="Respect: {{ $character->formatted_respect }} (Vandaag: +{{ $character->formatted_today_respect }})">
                        <span class="text-[8px] font-black uppercase tracking-wider text-indigo-600 dark:text-indigo-300 bg-indigo-50 dark:bg-indigo-950/60 px-1.5 py-0.5 rounded shadow-2xs border border-transparent dark:border-indigo-800/40">
                            RESPECT
                        </span>
                        <div class="flex items-center gap-1 text-slate-900 dark:text-white font-black text-xs sm:text-sm leading-none">
                            <i class="fa-solid fa-fire text-amber-500 text-xs"></i>
                            <span x-text="stats.respect">{{ $character->formatted_respect }}</span>
                        </div>
                        <span class="sr-only">Respect: {{ $character->formatted_respect }} Vandaag: {{ $character->formatted_today_respect }}</span>
                    </div>

                    <!-- 🎃 HALLOWEEN POMPOENPITTEN -->
                    @if(\App\Services\HalloweenService::isEventActive() || ($character->pumpkin_seeds ?? 0) > 0)
                    <a href="{{ route('halloween.shop') }}" class="flex flex-col items-center gap-1 group" title="Halloween Pompoenpitten &bull; Klik voor Shop">
                        <span class="text-[8px] font-black uppercase tracking-wider text-orange-800 dark:text-orange-300 bg-orange-100 dark:bg-orange-950/60 px-1.5 py-0.5 rounded shadow-2xs border border-transparent dark:border-orange-800/40 flex items-center gap-0.5">
                            <span>🎃</span> PITTEN
                        </span>
                        <div class="flex items-center gap-1 text-orange-600 dark:text-orange-400 font-black text-xs sm:text-sm leading-none group-hover:scale-105 transition-transform">
                            <i class="fa-solid fa-seedling text-amber-500 text-xs"></i>
                            <span>{{ number_format($character->pumpkin_seeds ?? 0, 0, ',', '.') }}</span>
                        </div>
                    </a>
                    @endif
                </div>

                <!-- VERTICAL DIVIDER -->
                <div class="h-10 w-px bg-slate-200/90 dark:bg-purple-900/40 shrink-0"></div>

                <!-- 5. FAR RIGHT: Search, Radio, Theme Switch & Profile Icon -->
                <div class="flex items-center gap-2 xl:gap-2.5 shrink-0">
                    <!-- TBS RADIO TOPBAR WIDGET -->
                    @include('partials.radio_player', ['isMobile' => false])

                    <!-- Sleek Animated Dark/Light Switch Slider -->
                    <button type="button" onclick="window.tbsToggleTheme()" 
                            class="relative inline-flex h-7 w-13 shrink-0 cursor-pointer rounded-full border-2 border-transparent bg-slate-300 dark:bg-purple-950/80 transition-colors duration-200 ease-in-out focus:outline-none shadow-inner overflow-hidden" 
                            title="Wissel Thema (Licht / Donker)"
                            aria-label="Wissel Thema">
                        <span class="theme-toggle-knob pointer-events-none inline-flex h-6 w-6 transform rounded-full bg-white dark:bg-purple-900 shadow-md ring-0 transition duration-200 ease-in-out translate-x-0 dark:translate-x-6 items-center justify-center overflow-hidden">
                            <span class="theme-toggle-sun items-center justify-center">
                                <i class="fa-solid fa-sun text-amber-500 text-[10px]"></i>
                            </span>
                            <span class="theme-toggle-moon items-center justify-center">
                                <i class="fa-solid fa-moon text-indigo-300 text-[10px]"></i>
                            </span>
                        </span>
                    </button>

                    <a href="{{ route('profile.me') }}" class="w-8 h-8 rounded-xl hover:bg-slate-100 dark:hover:bg-purple-950/60 border border-transparent hover:border-slate-200 dark:hover:border-purple-900/40 flex items-center justify-center text-slate-500 dark:text-purple-300/70 hover:text-slate-900 dark:hover:text-white transition" title="Mijn Profiel">
                        <i class="fa-solid fa-user"></i>
                    </a>
                </div>

            </div>
        </header>
        @else
        <!-- TOP MODERN GUEST HEADER BAR -->
        <header x-data="{ guestMenuOpen: false }" class="bg-white/95 dark:bg-[#110726]/95 backdrop-blur-md border-b border-slate-200 dark:border-purple-900/40 sticky top-0 z-40 shadow-xs">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-18 flex items-center justify-between gap-3">
                <!-- Logo & Title -->
                <a href="{{ Auth::check() ? route('dashboard') : route('login') }}" data-turbo="false" data-turbo-prefetch="false" class="flex items-center gap-3 group shrink-0">
                    <div class="w-11 h-11 shrink-0 group-hover:scale-105 transition-transform flex items-center justify-center">
                        <img src="{{ asset('assets/logo.png') }}?v=3" alt="TBS Kliniek" class="w-full h-full object-contain drop-shadow-md">
                    </div>
                    <div>
                        <span class="block text-base font-extrabold tracking-tight text-slate-900 dark:text-white uppercase leading-none">
                            TBS <span class="text-indigo-600 dark:text-purple-400">KLINIEK</span>
                        </span>
                    </div>
                </a>

                <!-- Desktop / Tablet Navigation Menu -->
                <nav class="hidden md:flex items-center gap-2">
                    @if(Auth::check())
                        <a href="{{ route('dashboard') }}" class="px-3.5 py-2 rounded-xl text-xs sm:text-sm font-bold text-slate-700 dark:text-slate-200 hover:text-purple-600 dark:hover:text-purple-400 hover:bg-slate-100 dark:hover:bg-purple-950/60 transition flex items-center gap-2">
                            <i class="fa-solid fa-house text-purple-600 dark:text-purple-400 text-xs"></i>
                            <span>Dashboard</span>
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="px-3.5 py-2 rounded-xl text-xs sm:text-sm font-bold text-slate-700 dark:text-slate-200 hover:text-purple-600 dark:hover:text-purple-400 hover:bg-slate-100 dark:hover:bg-purple-950/60 transition flex items-center gap-2">
                            <i class="fa-solid fa-right-to-bracket text-purple-600 dark:text-purple-400 text-xs"></i>
                            <span>Inloggen</span>
                        </a>
                        <a href="{{ route('register') }}" class="px-4 py-2 rounded-xl text-xs sm:text-sm font-extrabold text-white bg-purple-600 hover:bg-purple-700 active:scale-95 transition shadow-xs flex items-center gap-2">
                            <i class="fa-solid fa-user-plus text-xs"></i>
                            <span>Registreren</span>
                        </a>
                    @endif
                </nav>

                <!-- Right Side: Theme Switch + Socials (TikTok & Discord) + Mobile Button -->
                <div class="flex items-center gap-2 sm:gap-3">
                    <!-- Guest Dark Mode Animated Switch -->
                    <button type="button" onclick="window.tbsToggleTheme()" 
                            class="relative inline-flex h-7 w-13 shrink-0 cursor-pointer rounded-full border-2 border-transparent bg-slate-300 dark:bg-purple-950/80 transition-colors duration-200 ease-in-out focus:outline-none shadow-inner overflow-hidden" 
                            title="Wissel Thema (Licht / Donker)"
                            aria-label="Wissel Thema">
                        <span class="theme-toggle-knob pointer-events-none inline-flex h-6 w-6 transform rounded-full bg-white dark:bg-purple-900 shadow-md ring-0 transition duration-200 ease-in-out translate-x-0 dark:translate-x-6 items-center justify-center overflow-hidden">
                            <span class="theme-toggle-sun items-center justify-center">
                                <i class="fa-solid fa-sun text-amber-500 text-[10px]"></i>
                            </span>
                            <span class="theme-toggle-moon items-center justify-center">
                                <i class="fa-solid fa-moon text-indigo-300 text-[10px]"></i>
                            </span>
                        </span>
                    </button>

                    <a href="https://www.tiktok.com/@tbsklinieknl" target="_blank" rel="noopener noreferrer" class="hidden sm:inline-flex items-center gap-1.5 bg-slate-900 hover:bg-black text-white text-xs sm:text-sm font-extrabold px-3 py-2 rounded-xl transition shadow-xs hover:shadow-sm active:scale-[0.98]">
                        <i class="fa-brands fa-tiktok text-sm"></i>
                        <span>TikTok</span>
                    </a>

                    <a href="https://discord.gg/tbs" target="_blank" rel="noopener noreferrer" class="hidden sm:inline-flex items-center gap-2 bg-[#5865F2] hover:bg-[#4752C4] text-white text-xs sm:text-sm font-extrabold px-3.5 sm:px-4 py-2 rounded-xl transition shadow-xs hover:shadow-sm active:scale-[0.98]">
                        <i class="fa-brands fa-discord text-sm"></i>
                        <span>Discord</span>
                    </a>

                    <!-- Mobile Hamburger Toggle Button -->
                    <button type="button" @click="guestMenuOpen = !guestMenuOpen" 
                            class="md:hidden w-10 h-10 rounded-xl bg-slate-100 dark:bg-purple-950/60 border border-slate-200 dark:border-purple-900/40 text-slate-700 dark:text-purple-200 flex items-center justify-center hover:bg-slate-200 dark:hover:bg-purple-900/60 transition"
                            aria-label="Open Menu">
                        <i class="fa-solid fa-bars text-base" x-show="!guestMenuOpen"></i>
                        <i class="fa-solid fa-xmark text-base" x-show="guestMenuOpen" style="display: none;"></i>
                    </button>
                </div>
            </div>

            <!-- Mobile Collapsible Menu Dropdown -->
            <div x-show="guestMenuOpen" 
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 -translate-y-2"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 translate-y-0"
                 x-transition:leave-end="opacity-0 -translate-y-2"
                 class="md:hidden border-t border-slate-200 dark:border-purple-900/40 bg-white/98 dark:bg-[#110726]/98 px-4 pt-3 pb-5 space-y-2 shadow-lg"
                 style="display: none;">
                
                @if(Auth::check())
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-bold text-slate-800 dark:text-white hover:bg-purple-50 dark:hover:bg-purple-950/60 transition">
                        <i class="fa-solid fa-house text-purple-600 dark:text-purple-400 w-5 text-center"></i>
                        <span>Dashboard</span>
                    </a>
                @else
                    <a href="{{ route('login') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-bold text-slate-800 dark:text-white hover:bg-purple-50 dark:hover:bg-purple-950/60 transition">
                        <i class="fa-solid fa-right-to-bracket text-purple-600 dark:text-purple-400 w-5 text-center"></i>
                        <span>Inloggen</span>
                    </a>
                    <a href="{{ route('register') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-extrabold text-white bg-purple-600 hover:bg-purple-700 transition">
                        <i class="fa-solid fa-user-plus w-5 text-center"></i>
                        <span>Nieuw Account Registreren</span>
                    </a>
                @endif

                <div class="pt-2 border-t border-slate-100 dark:border-purple-900/40 flex items-center justify-between gap-2">
                    <a href="https://discord.gg/tbs" target="_blank" rel="noopener noreferrer" class="flex-1 py-2 rounded-xl bg-[#5865F2] text-white text-xs font-bold text-center flex items-center justify-center gap-1.5">
                        <i class="fa-brands fa-discord"></i> Discord
                    </a>
                    <a href="https://www.tiktok.com/@tbsklinieknl" target="_blank" rel="noopener noreferrer" class="flex-1 py-2 rounded-xl bg-slate-900 text-white text-xs font-bold text-center flex items-center justify-center gap-1.5">
                        <i class="fa-brands fa-tiktok"></i> TikTok
                    </a>
                </div>
            </div>
        </header>
        @endif

        <!-- MAIN CONTENT CONTAINER (pt-4 sm:pt-8, pb-28 zodat mobiele bottom dock niets overlapt) -->
        <main id="tbs-main-container" class="flex-1 w-full max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 pt-4 sm:pt-8 pb-28 lg:pb-12">
            <!-- Active Warning Notification Alert Banner for User -->
            @if(Auth::check() && Auth::user()->unacknowledgedWarnings->isNotEmpty())
            @foreach(Auth::user()->unacknowledgedWarnings as $unackedWarning)
            <div class="mb-6 bg-gradient-to-r from-amber-500/10 via-amber-500/5 to-rose-500/10 dark:from-amber-950/40 dark:to-rose-950/40 border-2 border-amber-400 dark:border-amber-600 rounded-2xl p-4 sm:p-5 shadow-lg relative overflow-hidden">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div class="flex items-start gap-3.5">
                        <div class="w-11 h-11 rounded-2xl bg-amber-400 text-slate-950 flex items-center justify-center text-xl shrink-0 shadow-sm mt-0.5">
                            <i class="fa-solid fa-triangle-exclamation"></i>
                        </div>
                        <div class="space-y-1">
                            <div class="flex items-center gap-2">
                                <span class="bg-amber-400 text-slate-950 text-[10px] font-black uppercase px-2.5 py-0.5 rounded-full tracking-wider">
                                    Officiële Waarschuwing
                                </span>
                                <span class="text-[11px] text-slate-400 dark:text-slate-500">
                                    {{ $unackedWarning->created_at->diffForHumans() }}
                                </span>
                            </div>
                            <h3 class="text-sm font-extrabold text-slate-900 dark:text-white">
                                Let op: Je hebt een officiële berisping ontvangen van de Zaalwacht / Directie
                            </h3>
                            <div class="text-xs text-slate-700 dark:text-slate-300 bg-white/70 dark:bg-slate-900/60 p-3 rounded-xl border border-amber-200 dark:border-amber-800/60 leading-relaxed font-medium">
                                "{{ $unackedWarning->reason }}"
                            </div>
                        </div>
                    </div>

                    <form action="{{ route('warning.acknowledge', $unackedWarning) }}" method="POST" class="shrink-0 w-full md:w-auto">
                        @csrf
                        <button type="submit" class="w-full md:w-auto px-5 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-600 active:scale-[0.98] text-slate-950 font-black text-xs transition shadow flex items-center justify-center gap-2 cursor-pointer">
                            <i class="fa-solid fa-check-double"></i>
                            <span>Begrepen, ik houd me aan de regels</span>
                        </button>
                    </form>
                </div>
            </div>
            @endforeach
            @endif

            <!-- Active Intensive Care Coma Alert Banner -->
            @if(isset($character) && $character->isComatose())
            <div x-data="{ 
                    sec: {{ $character->getComaRemainingSeconds() }},
                    formatTime(s) {
                        if (s <= 0) return '00:00';
                        const h = Math.floor(s / 3600);
                        const m = Math.floor((s % 3600) / 60);
                        const remainder = s % 60;
                        return (h > 0 ? h + 'u ' : '') + (m < 10 ? '0' : '') + m + 'm ' + (remainder < 10 ? '0' : '') + remainder + 's';
                    },
                    init() {
                        if (this.sec > 0) {
                            const timer = setInterval(() => {
                                this.sec--;
                                if (this.sec <= 0) {
                                    clearInterval(timer);
                                    window.location.reload();
                                }
                            }, 1000);
                        }
                    }
                 }" 
                 x-show="sec > 0"
                 class="mb-6 bg-red-950/90 text-white border-2 border-red-500 rounded-2xl p-4 sm:p-5 shadow-2xl flex flex-col md:flex-row items-center justify-between gap-4 relative z-20">
                <div class="flex items-center gap-3.5">
                    <div class="w-12 h-12 rounded-2xl bg-red-600 text-white flex items-center justify-center text-xl shadow-md shrink-0 animate-pulse">
                        <i class="fa-solid fa-heart-pulse"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded-full bg-red-500 text-white">Noodgeval</span>
                            <h3 class="text-sm sm:text-base font-black tracking-tight text-white">Intensive Care: Je ligt in Coma!</h3>
                        </div>
                        <p class="text-xs text-red-200 mt-0.5">
                            Je bent neergeschoten of geraakt door backfire. Resterende comatijd: <span class="font-mono font-black text-amber-300" x-text="formatTime(sec)"></span>.
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-2 shrink-0 w-full sm:w-auto">
                    <button type="button" 
                            onclick="window.tbsOpenSponsorAd('coma_revive')" 
                            class="w-full sm:w-auto py-2.5 px-4 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 active:scale-95 text-white font-black text-xs transition cursor-pointer flex items-center justify-center gap-2 shadow-md">
                        <i class="fa-solid fa-syringe text-sm"></i>
                        <span>Kijk Video ({{ (int)($character->ad_coma_progress ?? 0) }}/3 Ads Ontwaken)</span>
                    </button>
                </div>
            </div>
            @endif

            <!-- Active Prison / Isoleercel Alert Banner with Live Seconds Ticker & Star Buyout -->
            @if(isset($character) && $character->isImprisoned() && !request()->routeIs('prison'))
            @php
                $topReqPrisonAds = $character->getRequiredPrisonAds();
                $topCurrPrisonAds = (int) ($character->ad_prison_progress ?? 0);
            @endphp
            <div x-data="{ 
                    sec: {{ $character->remainingPrisonSeconds() }},
                    reloaded: false,
                    formatTime(s) {
                        if (s <= 0) return '00:00';
                        const m = Math.floor(s / 60);
                        const remainder = s % 60;
                        return (m < 10 ? '0' : '') + m + ':' + (remainder < 10 ? '0' : '') + remainder;
                    },
                    reloadSite() {
                        if (this.reloaded) return;
                        this.reloaded = true;
                        this.sec = 0;
                        if (window.Turbo) {
                            window.Turbo.clearCache();
                            window.Turbo.visit(window.location.href, { action: 'replace' });
                        } else {
                            window.location.reload();
                        }
                    },
                    init() {
                        if (this.sec <= 0) {
                            this.reloadSite();
                            return;
                        }
                        const timer = setInterval(() => {
                            this.sec--;
                            if (this.sec <= 0) {
                                clearInterval(timer);
                                this.reloadSite();
                            }
                        }, 1000);
                    }
                 }" 
                 x-show="sec > 0"
                 class="mb-6 bg-white dark:bg-slate-900 border-2 border-rose-400 dark:border-rose-600/80 rounded-2xl p-4 sm:p-5 shadow-lg flex flex-col md:flex-row items-center justify-between gap-4 relative z-20">
                <div class="flex items-center gap-3.5 w-full md:w-auto">
                    <div class="w-11 h-11 rounded-xl bg-rose-100 dark:bg-rose-950/80 border border-rose-300 dark:border-rose-700 text-rose-600 dark:text-rose-400 flex items-center justify-center text-xl shrink-0 shadow-xs">
                        <i class="fa-solid fa-lock"></i>
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="text-xs font-black text-rose-700 dark:text-rose-400 uppercase tracking-wide flex items-center gap-1.5">
                                <i class="fa-solid fa-triangle-exclamation text-rose-500"></i>
                                Vast in de isoleercel
                            </span>
                            <span class="px-2.5 py-0.5 rounded-full bg-rose-100 dark:bg-rose-950 text-rose-700 dark:text-rose-300 text-xs font-mono font-black border border-rose-300 dark:border-rose-800" x-text="formatTime(sec)">
                                {{ gmdate('i:s', $character->remainingPrisonSeconds()) }}
                            </span>
                        </div>
                        <p class="text-xs text-slate-600 dark:text-slate-300 mt-1 font-medium">
                            Resterende straftijd: <strong class="text-slate-900 dark:text-white font-mono" x-text="formatTime(sec) + ' (' + sec + 's)'">{{ gmdate('i:s', $character->remainingPrisonSeconds()) }} ({{ $character->remainingPrisonSeconds() }}s)</strong>.
                            Reden: <span class="text-rose-700 dark:text-rose-400 font-bold">{{ $character->prison_reason ?? 'Wangedrag in de kliniek.' }}</span>
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-2.5 w-full md:w-auto justify-end shrink-0">
                    <button type="button" 
                            onclick="window.tbsOpenSponsorAd('prison', () => { window.location.reload(); })" 
                            class="flex-1 md:flex-initial px-4 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-700 active:scale-95 text-white font-extrabold text-xs uppercase tracking-wider transition shadow-sm hover:shadow-md cursor-pointer flex items-center justify-center gap-2 whitespace-nowrap"
                            title="Elke ad vermindert je straftijd met 3 minuten. Bij {{ $topReqPrisonAds }} ads word je direct vrijgelaten!">
                        <i class="fa-solid fa-tv text-amber-300"></i>
                        <span>Kijk Ad</span>
                        <span class="px-2 py-0.5 rounded-md bg-purple-900/80 text-[10px] font-black text-purple-200">{{ $topCurrPrisonAds }}/{{ $topReqPrisonAds }} Vrijlating</span>
                    </button>
                    <a href="{{ route('prison') }}" class="flex-1 md:flex-initial px-4 py-2.5 rounded-xl bg-purple-50 dark:bg-purple-950/60 hover:bg-purple-100 dark:hover:bg-purple-900/60 border border-purple-200 dark:border-purple-800 text-purple-700 dark:text-purple-300 font-extrabold text-xs transition text-center shrink-0 flex items-center justify-center gap-2 whitespace-nowrap">
                        <i class="fa-solid fa-building-shield"></i>
                        <span>Isoleercel</span>
                    </a>
                </div>
            </div>
            @endif

            <!-- Flash messages -->
            @if(session('evening_drop_awarded'))
            <div class="mb-6 bg-amber-500/10 dark:bg-slate-900 border-2 border-amber-400 dark:border-amber-500/60 p-4 sm:p-5 rounded-2xl shadow-md flex items-center justify-between gap-4 animate-in fade-in slide-in-from-top-4 duration-300">
                <div class="flex items-center gap-3.5">
                    <div class="w-12 h-12 rounded-xl bg-amber-400 text-slate-950 flex items-center justify-center text-xl font-black shrink-0 shadow-sm animate-bounce">
                        🎁
                    </div>
                    <div>
                        <h4 class="text-sm font-black text-slate-900 dark:text-white flex items-center gap-2">
                            <span>22:00 - 22:30 INLOG BONUS!</span>
                            <span class="bg-amber-400 text-slate-950 text-[10px] font-black px-2 py-0.5 rounded-full uppercase">3x Kluiscodes</span>
                        </h4>
                        <p class="text-xs text-slate-600 dark:text-slate-300 mt-0.5">
                            Omdat je tussen 22:00 en 22:30 bent ingelogd in de TBS Kliniek heb je jouw dagelijkse inlogbonus ontvangen: <strong>3 gratis geheime kluiscodes</strong> in je kluis en postvak!
                        </p>
                    </div>
                </div>
                <a href="{{ route('vault.index') }}" class="shrink-0 hidden sm:inline-flex items-center gap-2 px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-black shadow-xs transition active:scale-95">
                    <i class="fa-solid fa-vault text-amber-400"></i>
                    <span>Naar MedicijnKluis</span>
                </a>
            </div>
            @endif

            @auth
            @php
                $navChar = Auth::user()?->character;
            @endphp
            @if($navChar && !request()->routeIs('depot.*') && $navChar->getStorageUsed() >= $navChar->getStorageCapacity())
            <div class="mb-6 bg-gradient-to-r from-rose-50 via-rose-100/80 to-purple-50 dark:from-rose-950/90 dark:via-rose-900/70 dark:to-purple-950/90 border-2 border-rose-300 dark:border-rose-600/80 p-4 rounded-2xl shadow-sm dark:shadow-xl flex flex-col sm:flex-row sm:items-center justify-between gap-3 transition">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-rose-600 text-white flex items-center justify-center text-lg shrink-0 shadow-sm animate-bounce">
                        <i class="fa-solid fa-box-archive"></i>
                    </div>
                    <div>
                        <p class="text-xs font-black uppercase tracking-wider text-rose-700 dark:text-rose-300 flex items-center gap-1.5">
                            <i class="fa-solid fa-triangle-exclamation text-amber-500"></i>
                            <span>Inventaris Overvol ({{ $navChar->getStorageUsed() }}/{{ $navChar->getStorageCapacity() }} plekken)</span>
                        </p>
                        <p class="text-xs text-rose-950 dark:text-rose-100 font-medium mt-0.5 leading-relaxed">
                            Je persoonlijke opbergplaats zit over zijn limiet! Verkoop overtollige maskers of spullen in je Depot om weer buit, drops en kluizen te kunnen ontvangen.
                        </p>
                    </div>
                </div>
                <a href="{{ route('depot.index', ['tab' => 'storage']) }}" class="px-4 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-black text-xs transition shrink-0 text-center shadow-xs flex items-center justify-center gap-1.5 cursor-pointer">
                    <i class="fa-solid fa-coins text-amber-950"></i>
                    <span>Depot Openen &amp; Verkopen</span>
                </a>
            </div>
            @endif
            @endauth

            @yield('content')
        </main>

        <!-- SITE-WIDE JURIDISCHE & COMPLIANCE FOOTER (Nederland & Europa AVG/GDPR) -->
        <footer class="mt-auto border-t border-slate-200 dark:border-purple-900/40 bg-white dark:bg-[#0c061a] pt-10 pb-20 lg:pb-10 text-xs text-slate-500 dark:text-slate-400">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 space-y-8">
                
                <!-- Main Footer Grid (4 Perfectly Balanced Columns) -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
                    
                    <!-- Col 1: Branding, Intro & Social Icons -->
                    <div class="space-y-3.5">
                        <div class="flex items-center gap-2.5">
                            <img src="{{ asset('assets/logo.png') }}?v=3" alt="TBS Kliniek" class="w-8 h-8 object-contain drop-shadow-xs">
                            <span class="font-black text-slate-900 dark:text-white text-base">TBS <span class="text-purple-600 dark:text-purple-400">Kliniek</span></span>
                        </div>
                        <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                            Het #1 Nederlandse misdaad- &amp; gevangenis browsergame. Overleef de afdeling, leid je bende en heers over de kliniek.
                        </p>

                        <!-- Sleek Social Media Icon Buttons -->
                        <div class="space-y-1.5 pt-1">
                            <span class="text-[10px] font-black uppercase tracking-wider text-slate-400 dark:text-purple-300/70 block">
                                Volg Onze Socials
                            </span>
                            <div class="flex flex-wrap items-center gap-2">
                                <!-- Discord -->
                                <a href="https://discord.gg/tbs" target="_blank" rel="noopener noreferrer" 
                                   class="w-9 h-9 rounded-xl bg-slate-100 dark:bg-purple-950/60 hover:bg-[#5865F2] hover:text-white text-[#5865F2] border border-slate-200 dark:border-purple-900/40 flex items-center justify-center text-sm transition shadow-2xs group" 
                                   title="Officiële Discord">
                                    <i class="fa-brands fa-discord group-hover:scale-110 transition-transform"></i>
                                </a>

                                <!-- YouTube -->
                                <a href="https://www.youtube.com/@tbsklinieknl" target="_blank" rel="noopener noreferrer" 
                                   class="w-9 h-9 rounded-xl bg-slate-100 dark:bg-purple-950/60 hover:bg-[#FF0000] hover:text-white text-[#FF0000] border border-slate-200 dark:border-purple-900/40 flex items-center justify-center text-sm transition shadow-2xs group" 
                                   title="YouTube @tbsklinieknl">
                                    <i class="fa-brands fa-youtube group-hover:scale-110 transition-transform"></i>
                                </a>

                                <!-- TikTok -->
                                <a href="https://www.tiktok.com/@tbsklinieknl" target="_blank" rel="noopener noreferrer" 
                                   class="w-9 h-9 rounded-xl bg-slate-100 dark:bg-purple-950/60 hover:bg-slate-900 hover:text-white text-slate-800 dark:text-slate-200 border border-slate-200 dark:border-purple-900/40 flex items-center justify-center text-sm transition shadow-2xs group" 
                                   title="TikTok @tbsklinieknl">
                                    <i class="fa-brands fa-tiktok group-hover:scale-110 transition-transform"></i>
                                </a>

                                <!-- Instagram -->
                                <a href="https://www.instagram.com/tbsklinieknl/" target="_blank" rel="noopener noreferrer" 
                                   class="w-9 h-9 rounded-xl bg-slate-100 dark:bg-purple-950/60 hover:bg-gradient-to-tr hover:from-[#fd1d1d] hover:to-[#833ab4] hover:text-white text-[#e1306c] border border-slate-200 dark:border-purple-900/40 flex items-center justify-center text-sm transition shadow-2xs group" 
                                   title="Instagram @tbsklinieknl">
                                    <i class="fa-brands fa-instagram group-hover:scale-110 transition-transform"></i>
                                </a>

                                <!-- Twitter / X -->
                                <a href="https://x.com/tbskliniek" target="_blank" rel="noopener noreferrer" 
                                   class="w-9 h-9 rounded-xl bg-slate-100 dark:bg-purple-950/60 hover:bg-black hover:text-white text-slate-900 dark:text-white border border-slate-200 dark:border-purple-900/40 flex items-center justify-center text-xs transition shadow-2xs group" 
                                   title="Twitter / X @tbskliniek">
                                    <i class="fa-brands fa-x-twitter group-hover:scale-110 transition-transform"></i>
                                </a>

                                <!-- Hyves -->
                                <a href="https://hyves.nl/#my-profile/hyve?id=Zv3DWejo" target="_blank" rel="noopener noreferrer" 
                                   class="w-9 h-9 rounded-xl bg-slate-100 dark:bg-purple-950/60 hover:bg-[#f36f21] hover:text-white text-[#f36f21] border border-slate-200 dark:border-purple-900/40 flex items-center justify-center transition shadow-2xs group" 
                                   title="Hyves (TBS Kliniek)">
                                    <span class="w-4 h-4 rounded bg-[#f36f21] group-hover:bg-white text-white group-hover:text-[#f36f21] flex items-center justify-center font-black text-[10px] leading-none shrink-0 shadow-2xs transition-colors">H</span>
                                </a>
                            </div>

                            <div class="pt-1">
                                <a href="{{ route('community.index') }}" class="inline-flex items-center gap-1.5 text-[11px] font-extrabold text-purple-600 dark:text-purple-400 hover:underline">
                                    <i class="fa-solid fa-gift text-amber-500"></i>
                                    <span>Community Hub &amp; Beloningen &raquo;</span>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Col 2: Spel & Actie -->
                    <div class="space-y-2.5">
                        <h4 class="font-extrabold text-xs uppercase tracking-wider text-slate-900 dark:text-white">Spel & Features</h4>
                        <ul class="space-y-1.5 text-xs text-slate-600 dark:text-slate-400">
                            <li><a href="{{ route('dashboard') }}" class="hover:text-purple-600 dark:hover:text-purple-400 transition">Dashboard & Profiel</a></li>
                            <li><a href="{{ route('fight.index') }}" class="hover:text-purple-600 dark:hover:text-purple-400 transition">De Luchtplaats (Vechten)</a></li>
                            <li><a href="{{ route('vault.index') }}" class="hover:text-purple-600 dark:hover:text-purple-400 transition">MedicijnKluis & Codes</a></li>
                            <li><a href="{{ route('community.index') }}" class="hover:text-purple-600 dark:hover:text-purple-400 transition font-bold text-amber-600 dark:text-amber-400 flex items-center gap-1.5">
                                <i class="fa-solid fa-gift text-amber-500 text-[10px]"></i> Community Beloningen
                            </a></li>
                            <li><a href="{{ route('sponsor.index') }}" class="hover:text-purple-600 dark:hover:text-purple-400 transition font-bold text-purple-700 dark:text-purple-400 flex items-center gap-1.5">
                                <i class="fa-solid fa-tv text-purple-600 dark:text-purple-400 text-[10px]"></i> Sponsorkamer (Gratis VIP)
                            </a></li>
                            <li><a href="{{ route('vip.index') }}" class="hover:text-purple-600 dark:hover:text-purple-400 transition">VIP Lidmaatschap (Gratis via Ads)</a></li>
                            <li><a href="{{ route('roadmap.index') }}" class="hover:text-purple-600 dark:hover:text-purple-400 transition">Roadmap & Updates</a></li>
                        </ul>
                    </div>

                    <!-- Col 3: Hulp & Ondersteuning -->
                    <div class="space-y-2.5">
                        <h4 class="font-extrabold text-xs uppercase tracking-wider text-slate-900 dark:text-white">Ondersteuning</h4>
                        <ul class="space-y-1.5 text-xs text-slate-600 dark:text-slate-400">
                            <li><a href="{{ route('faq.index') }}" class="hover:text-purple-600 dark:hover:text-purple-400 transition">Kennisbank & FAQ</a></li>
                            <li><a href="{{ route('help.tickets.index') }}" class="hover:text-purple-600 dark:hover:text-purple-400 transition">Support Ticketsysteem</a></li>
                            <li><a href="{{ route('help.tickets.create') }}" class="hover:text-purple-600 dark:hover:text-purple-400 transition">Nieuw Ticket Openen</a></li>
                            <li><a href="{{ route('settings.account') }}" class="hover:text-purple-600 dark:hover:text-purple-400 transition">Account & Beveiliging</a></li>
                        </ul>
                    </div>

                    <!-- Col 4: Juridisch & Privacy (NL / EU) -->
                    <div class="space-y-2.5">
                        <h4 class="font-extrabold text-xs uppercase tracking-wider text-slate-900 dark:text-white">Juridisch & Privacy</h4>
                        <ul class="space-y-1.5 text-xs text-slate-600 dark:text-slate-400">
                            <li><a href="{{ route('legal.terms') }}" class="hover:text-purple-600 dark:hover:text-purple-400 transition">Algemene Voorwaarden</a></li>
                            <li><a href="{{ route('legal.privacy') }}" class="hover:text-purple-600 dark:hover:text-purple-400 transition">Privacybeleid (AVG / GDPR)</a></li>
                            <li><a href="{{ route('legal.cookies') }}" class="hover:text-purple-600 dark:hover:text-purple-400 transition">Cookiebeleid</a></li>
                            <li><a href="{{ route('legal.disclaimer') }}" class="hover:text-purple-600 dark:hover:text-purple-400 transition">Disclaimer & Colofon</a></li>
                            <li>
                                <button type="button" onclick="window.tbsOpenCookieSettings()" class="hover:text-purple-600 dark:hover:text-purple-400 transition text-left cursor-pointer flex items-center gap-1.5 text-purple-700 dark:text-purple-400 font-bold">
                                    <i class="fa-solid fa-cookie-bite text-amber-600 text-[10px]"></i> Cookie-instellingen Beheren
                                </button>
                            </li>
                        </ul>
                    </div>

                </div>

                <!-- Satire / Fictief Verklaring Bar -->
                <div class="p-4 rounded-xl bg-slate-50 dark:bg-purple-950/40 border border-slate-200 dark:border-purple-900/40 text-[11px] text-slate-500 dark:text-slate-400 leading-relaxed flex items-start gap-3">
                    <i class="fa-solid fa-circle-info text-purple-600 dark:text-purple-400 text-sm mt-0.5 shrink-0"></i>
                    <div>
                        <strong class="text-slate-800 dark:text-slate-200 font-bold">Fictief Rollenspel Verklaring:</strong> TBS Kliniek is een 100% fictief online browsergame (RPG / satire) ter recreatief vermaak. Deze website is op geen enkele wijze gelieerd aan het Ministerie van Justitie en Veiligheid, de Dienst Justitiële Inrichtingen (DJI) of bestaande forensische psychiatrische centra. In-game valuta en items hebben geen reële economische waarde.
                    </div>
                </div>

                <!-- Copyright Bar -->
                <div class="pt-4 border-t border-slate-100 dark:border-purple-900/40 flex flex-col sm:flex-row items-center justify-between gap-3 text-[11px] text-slate-400 dark:text-slate-500">
                    <div>
                        &copy; {{ date('Y') }} TBS Kliniek (tbs-kliniek.nl). Alle rechten voorbehouden.
                    </div>
                    <div class="flex items-center gap-4">
                        <a href="{{ route('legal.terms') }}" class="hover:underline hover:text-purple-600 dark:hover:text-purple-400">Voorwaarden</a>
                        <span>•</span>
                        <a href="{{ route('legal.privacy') }}" class="hover:underline hover:text-purple-600 dark:hover:text-purple-400">Privacy</a>
                        <span>•</span>
                        <a href="{{ route('legal.cookies') }}" class="hover:underline hover:text-purple-600 dark:hover:text-purple-400">Cookies</a>
                        <span>•</span>
                        <a href="{{ route('legal.disclaimer') }}" class="hover:underline hover:text-purple-600 dark:hover:text-purple-400">Colofon</a>
                    </div>
                </div>

            </div>
        </footer>

        @if(isset($character))
        <!-- NATIVE MOBILE APP BOTTOM DOCK -->
        <nav class="lg:hidden fixed bottom-0 inset-x-0 bg-white/95 dark:bg-[#110726]/95 backdrop-blur-md border-t border-slate-200/90 dark:border-purple-900/50 z-30 flex items-center justify-around py-1.5 px-2 shadow-2xl select-none" style="padding-bottom: max(0.375rem, env(safe-area-inset-bottom));">
            
            <!-- 1. Home / Dashboard -->
            <a href="{{ route('dashboard') }}" class="flex-1 flex flex-col items-center gap-0.5 py-1 text-[10px] font-bold {{ request()->routeIs('dashboard') ? 'text-purple-600 dark:text-purple-400 font-black' : 'text-slate-500 dark:text-slate-400 hover:text-slate-700' }} active:scale-90 transition">
                <i class="fa-solid fa-house text-base"></i>
                <span>Home</span>
            </a>

            <!-- 2. Hosselen / Misdaad -->
            <a href="{{ route('hustle.index') }}" class="flex-1 flex flex-col items-center gap-0.5 py-1 text-[10px] font-bold {{ request()->routeIs('hustle.*') ? 'text-purple-600 dark:text-purple-400 font-black' : 'text-slate-500 dark:text-slate-400 hover:text-slate-700' }} active:scale-90 transition">
                <i class="fa-solid fa-hand-holding-dollar text-base"></i>
                <span>Hosselen</span>
            </a>

            <!-- 3. CENTER GLOWING QUICK-ACTION FAB -->
            <div class="flex-1 flex flex-col items-center justify-center -mt-5">
                <button type="button" 
                        @click="mobileQuickSheetOpen = !mobileQuickSheetOpen; if(window.tbsVibrate) window.tbsVibrate(15);"
                        class="relative w-13 h-13 rounded-full bg-gradient-to-tr from-purple-600 via-indigo-600 to-purple-500 text-white flex items-center justify-center shadow-lg shadow-purple-600/40 active:scale-90 transition-transform cursor-pointer border-3 border-white dark:border-[#110726] group"
                        aria-label="Snelmenu Acties">
                    <span class="absolute inset-0 rounded-full bg-purple-500/20 animate-ping"></span>
                    <i class="fa-solid fa-bolt text-lg group-hover:scale-110 transition-transform"></i>
                </button>
                <span class="text-[9px] font-black text-purple-600 dark:text-purple-400 mt-0.5 tracking-tight">Acties</span>
            </div>

            <!-- 4. Bendes -->
            <a href="{{ route('gangs.index') }}" @click="if(window.tbsVibrate) window.tbsVibrate(10);" class="flex-1 flex flex-col items-center gap-0.5 py-1 text-[10px] font-bold {{ request()->routeIs('gangs.*') ? 'text-purple-600 dark:text-purple-400 font-black' : 'text-slate-500 dark:text-slate-400 hover:text-slate-700' }} active:scale-90 transition">
                <i class="fa-solid fa-users text-base"></i>
                <span>Bendes</span>
            </a>

            <!-- 5. Chat / Berichten -->
            <a href="{{ route('chat.index') }}" @click="if(window.tbsVibrate) window.tbsVibrate(10);" class="flex-1 flex flex-col items-center gap-0.5 py-1 text-[10px] font-bold {{ request()->routeIs('chat.*') ? 'text-purple-600 dark:text-purple-400 font-black' : 'text-slate-500 dark:text-slate-400 hover:text-slate-700' }} active:scale-90 transition relative">
                <div class="relative">
                    <i class="fa-solid fa-comments text-base"></i>
                    @if(isset($unreadChatCount) && $unreadChatCount > 0)
                        <span class="absolute -top-1 -right-1 w-2 h-2 rounded-full bg-emerald-500 ring-2 ring-white dark:ring-[#110726]"></span>
                    @endif
                </div>
                <span>Chat</span>
            </a>
        </nav>

        <!-- NATIVE SLIDE-UP QUICK ACTIONS BOTTOM SHEET -->
        <div x-show="mobileQuickSheetOpen" 
             x-cloak 
             class="lg:hidden fixed inset-0 z-50 flex flex-col justify-end" 
             role="dialog" 
             aria-modal="true">
            
            <!-- Backdrop -->
            <div x-show="mobileQuickSheetOpen"
                 x-transition:enter="transition-opacity ease-out duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition-opacity ease-in duration-150"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @click="mobileQuickSheetOpen = false"
                 class="fixed inset-0 bg-slate-950/75 backdrop-blur-xs"></div>

            <!-- Slide-Up Card -->
            <div x-show="mobileQuickSheetOpen"
                 x-transition:enter="transition ease-out duration-250 transform-gpu"
                 x-transition:enter-start="translate-y-full"
                 x-transition:enter-end="translate-y-0"
                 x-transition:leave="transition ease-in duration-200 transform-gpu"
                 x-transition:leave-start="translate-y-0"
                 x-transition:leave-end="translate-y-full"
                 class="relative bg-white dark:bg-[#0f172a] rounded-t-3xl shadow-2xl border-t border-purple-500/30 max-h-[85vh] overflow-y-auto p-4 z-10 flex flex-col gap-4 text-slate-800 dark:text-slate-100"
                 style="padding-bottom: max(1.5rem, env(safe-area-inset-bottom));">
                
                <!-- Pull Bar Handle (Tap or Swipe Down to Dismiss) -->
                <div class="py-2 -my-2 flex justify-center cursor-pointer select-none touch-none"
                     x-data="{ touchStartY: 0 }"
                     @touchstart="touchStartY = $event.touches[0].clientY"
                     @touchmove="if ($event.touches[0].clientY - touchStartY > 40) { mobileQuickSheetOpen = false; if(window.tbsVibrate) window.tbsVibrate(15); }"
                     @click="mobileQuickSheetOpen = false">
                    <div class="w-12 h-1.5 rounded-full bg-slate-300 dark:bg-purple-900/80 shrink-0"></div>
                </div>

                <!-- Header / Player Summary -->
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-purple-900/40 pb-3">
                    <div class="flex items-center gap-2.5">
                        <div class="w-10 h-10 rounded-full p-0.5 bg-purple-100 dark:bg-purple-950 ring-2 ring-purple-500/30 overflow-hidden">
                            <img src="{{ $character->avatar_url }}" alt="{{ $character->name }}" class="w-full h-full rounded-full object-cover">
                        </div>
                        <div>
                            <div class="font-black text-sm text-slate-900 dark:text-white leading-tight">{{ $character->name }}</div>
                            <div class="text-[10px] text-purple-600 dark:text-purple-400 font-extrabold">LVL {{ $character->level }} &bull; {{ $character->formatted_respect }} Respect</div>
                        </div>
                    </div>
                    <button @click="mobileQuickSheetOpen = false" class="w-8 h-8 rounded-xl bg-slate-100 dark:bg-purple-950/60 text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-white flex items-center justify-center text-sm transition cursor-pointer">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <!-- Stats Overview Grid (Cards) -->
                <div class="grid grid-cols-2 gap-2 text-xs">
                    <!-- Cash & Deposit -->
                    <div class="p-2.5 rounded-2xl bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200/80 dark:border-emerald-800/40 flex flex-col justify-between">
                        <div class="flex items-center justify-between text-[10px] font-bold text-emerald-800 dark:text-emerald-300">
                            <span class="flex items-center gap-1"><i class="fa-solid fa-wallet"></i> Contant</span>
                            <span class="font-mono font-black text-xs" x-text="stats.cashFormatted">€{{ number_format($character->cash, 0, ',', '.') }}</span>
                        </div>
                        @if($character->cash > 0)
                        <form action="{{ route('bank.deposit') }}" method="POST" class="mt-2">
                            @csrf
                            <input type="hidden" name="amount" value="{{ (int)$character->cash }}">
                            <button type="submit" class="w-full py-1 px-2 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white font-black text-[10px] uppercase tracking-wider transition active:scale-95 shadow-xs flex items-center justify-center gap-1 cursor-pointer">
                                <i class="fa-solid fa-building-columns text-[9px]"></i> Alles Storten
                            </button>
                        </form>
                        @else
                        <a href="{{ route('bank.index') }}" class="mt-2 w-full py-1 px-2 rounded-lg bg-emerald-600/20 text-emerald-700 dark:text-emerald-300 font-extrabold text-[10px] text-center block">
                            Naar Bank &raquo;
                        </a>
                        @endif
                    </div>

                    <!-- Bank Balance -->
                    <div class="p-2.5 rounded-2xl bg-indigo-50 dark:bg-purple-950/40 border border-indigo-200/80 dark:border-purple-800/40 flex flex-col justify-between">
                        <div class="flex items-center justify-between text-[10px] font-bold text-indigo-800 dark:text-purple-300">
                            <span class="flex items-center gap-1"><i class="fa-solid fa-building-columns"></i> Bank Saldo</span>
                            <span class="font-mono font-black text-xs" x-text="stats.bankFormatted">€{{ number_format($character->bank, 0, ',', '.') }}</span>
                        </div>
                        <a href="{{ route('bank.index') }}" class="mt-2 w-full py-1 px-2 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white font-black text-[10px] uppercase tracking-wider transition active:scale-95 shadow-xs flex items-center justify-center gap-1">
                            <i class="fa-solid fa-vault text-[9px]"></i> Bank Beheer
                        </a>
                    </div>

                    <!-- Energy -->
                    <div class="p-2.5 rounded-2xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-purple-900/40 flex flex-col justify-between">
                        <div class="flex items-center justify-between text-[10px] font-bold text-slate-700 dark:text-slate-300">
                            <span class="flex items-center gap-1"><i class="fa-solid fa-bolt text-amber-500"></i> Energie</span>
                            <span class="font-mono font-black text-xs text-emerald-600 dark:text-emerald-400" x-text="stats.energy + '/' + stats.max_energy">{{ $character->energy }}/{{ $character->max_energy }}</span>
                        </div>
                        <div class="w-full bg-slate-200 dark:bg-slate-800 rounded-full h-1.5 my-1.5 overflow-hidden">
                            <div class="bg-emerald-500 h-full rounded-full transition-all" :style="'width: ' + stats.energy_pct + '%'"></div>
                        </div>
                        <a href="{{ route('shop.index', ['tab' => 'inventaris']) }}" class="w-full py-1 px-2 rounded-lg bg-amber-500 hover:bg-amber-400 text-slate-950 font-black text-[10px] uppercase tracking-wider transition active:scale-95 shadow-xs flex items-center justify-center gap-1">
                            <i class="fa-solid fa-pills text-[9px]"></i> Booster Nemen
                        </a>
                    </div>

                    <!-- Health -->
                    <div class="p-2.5 rounded-2xl bg-rose-50 dark:bg-rose-950/30 border border-rose-200/80 dark:border-rose-800/40 flex flex-col justify-between">
                        <div class="flex items-center justify-between text-[10px] font-bold text-rose-800 dark:text-rose-300">
                            <span class="flex items-center gap-1"><i class="fa-solid fa-heart text-rose-500"></i> Gezondheid</span>
                            <span class="font-mono font-black text-xs text-rose-600 dark:text-rose-400" x-text="stats.health + '/' + stats.max_health">{{ $character->health }}/{{ $character->max_health }}</span>
                        </div>
                        <div class="w-full bg-slate-200 dark:bg-slate-800 rounded-full h-1.5 my-1.5 overflow-hidden">
                            <div class="bg-rose-500 h-full rounded-full transition-all" :style="'width: ' + stats.health_pct + '%'"></div>
                        </div>
                        <a href="{{ route('infirmary.index') }}" class="w-full py-1 px-2 rounded-lg bg-rose-600 hover:bg-rose-500 text-white font-black text-[10px] uppercase tracking-wider transition active:scale-95 shadow-xs flex items-center justify-center gap-1">
                            <i class="fa-solid fa-kit-medical text-[9px]"></i> Genezen
                        </a>
                    </div>
                </div>

                <!-- 🚀 Snelle Acties Grid (Large Touch Buttons) -->
                <div>
                    <span class="text-[10px] font-black uppercase text-slate-400 dark:text-purple-300/70 tracking-wider mb-2 block">Direct Naar</span>
                    <div class="grid grid-cols-4 gap-2 text-center text-xs font-bold">
                        <a href="{{ route('hustle.index') }}" class="p-2 rounded-2xl bg-slate-100 hover:bg-purple-100 dark:bg-purple-950/60 dark:hover:bg-purple-900/60 border border-slate-200 dark:border-purple-800/40 flex flex-col items-center gap-1 transition active:scale-95">
                            <div class="w-8 h-8 rounded-xl bg-purple-600/10 text-purple-600 dark:text-purple-300 flex items-center justify-center text-sm">
                                <i class="fa-solid fa-hand-holding-dollar"></i>
                            </div>
                            <span class="text-[9px] truncate w-full">Hosselen</span>
                        </a>

                        <a href="{{ route('fight.index') }}" class="p-2 rounded-2xl bg-slate-100 hover:bg-emerald-100 dark:bg-purple-950/60 dark:hover:bg-purple-900/60 border border-slate-200 dark:border-purple-800/40 flex flex-col items-center gap-1 transition active:scale-95">
                            <div class="w-8 h-8 rounded-xl bg-emerald-600/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-sm">
                                <i class="fa-solid fa-hand-fist"></i>
                            </div>
                            <span class="text-[9px] truncate w-full">Stelen</span>
                        </a>

                        <a href="{{ route('hitman.index') }}" class="p-2 rounded-2xl bg-rose-50 hover:bg-rose-100 dark:bg-rose-950/60 dark:hover:bg-rose-900/60 border border-rose-200 dark:border-rose-800/40 flex flex-col items-center gap-1 transition active:scale-95">
                            <div class="w-8 h-8 rounded-xl bg-rose-600/10 text-rose-600 dark:text-rose-400 flex items-center justify-center text-sm">
                                <i class="fa-solid fa-crosshairs"></i>
                            </div>
                            <span class="text-[9px] truncate w-full text-rose-700 dark:text-rose-300 font-black">Liquidatie</span>
                        </a>

                        <a href="{{ route('packages.index') }}" class="p-2 rounded-2xl bg-amber-50 hover:bg-amber-100 dark:bg-amber-950/60 dark:hover:bg-amber-900/60 border border-amber-200 dark:border-amber-800/40 flex flex-col items-center gap-1 transition active:scale-95">
                            <div class="w-8 h-8 rounded-xl bg-amber-600/10 text-amber-600 dark:text-amber-400 flex items-center justify-center text-sm">
                                <i class="fa-solid fa-briefcase"></i>
                            </div>
                            <span class="text-[9px] truncate w-full text-amber-800 dark:text-amber-300 font-black">Koffers</span>
                        </a>

                        <a href="{{ route('vault.index') }}" class="p-2 rounded-2xl bg-slate-100 hover:bg-amber-100 dark:bg-purple-950/60 dark:hover:bg-purple-900/60 border border-slate-200 dark:border-purple-800/40 flex flex-col items-center gap-1 transition active:scale-95">
                            <div class="w-8 h-8 rounded-xl bg-amber-600/10 text-amber-600 dark:text-amber-400 flex items-center justify-center text-sm">
                                <i class="fa-solid fa-key"></i>
                            </div>
                            <span class="text-[9px] truncate w-full">Kluis</span>
                        </a>

                        <a href="{{ route('marketplace.index') }}" class="p-2 rounded-2xl bg-slate-100 hover:bg-indigo-100 dark:bg-purple-950/60 dark:hover:bg-purple-900/60 border border-slate-200 dark:border-purple-800/40 flex flex-col items-center gap-1 transition active:scale-95">
                            <div class="w-8 h-8 rounded-xl bg-indigo-600/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-sm">
                                <i class="fa-solid fa-store"></i>
                            </div>
                            <span class="text-[9px] truncate w-full">Markt</span>
                        </a>

                        <a href="{{ route('sponsor.index') }}" class="p-2 rounded-2xl bg-purple-50 hover:bg-purple-100 dark:bg-purple-950/60 dark:hover:bg-purple-900/60 border border-purple-200 dark:border-purple-800/40 flex flex-col items-center gap-1 transition active:scale-95">
                            <div class="w-8 h-8 rounded-xl bg-purple-600/10 text-purple-600 dark:text-purple-300 flex items-center justify-center text-sm">
                                <i class="fa-solid fa-tv"></i>
                            </div>
                            <span class="text-[9px] truncate w-full text-purple-700 dark:text-purple-300 font-black">Sponsor Ads</span>
                        </a>

                        <button type="button" 
                                @click="mobileQuickSheetOpen = false; if(window.tbsOpenSpotlight){window.tbsOpenSpotlight();}"
                                class="p-2 rounded-2xl bg-slate-100 hover:bg-purple-100 dark:bg-purple-950/60 dark:hover:bg-purple-900/60 border border-slate-200 dark:border-purple-800/40 flex flex-col items-center gap-1 transition active:scale-95 cursor-pointer">
                            <div class="w-8 h-8 rounded-xl bg-purple-600/10 text-purple-600 dark:text-purple-300 flex items-center justify-center text-sm">
                                <i class="fa-solid fa-magnifying-glass"></i>
                            </div>
                            <span class="text-[9px] truncate w-full">Zoeken</span>
                        </button>
                    </div>
                </div>

                <!-- Navigation Controls (Terug, Vernieuwen & Thema Wissel) -->
                <div class="grid grid-cols-3 gap-2 pt-1 border-t border-slate-100 dark:border-purple-900/40">
                    <button type="button" 
                            onclick="window.tbsGoBack()" 
                            class="py-2 rounded-xl bg-slate-100 dark:bg-purple-950/60 text-slate-700 dark:text-purple-300 font-extrabold text-[11px] flex items-center justify-center gap-1.5 active:scale-95 transition cursor-pointer">
                        <i class="fa-solid fa-arrow-left"></i> Terug
                    </button>
                    <button type="button" 
                            onclick="window.tbsReloadPage(this)" 
                            class="py-2 rounded-xl bg-slate-100 dark:bg-purple-950/60 text-slate-700 dark:text-purple-300 font-extrabold text-[11px] flex items-center justify-center gap-1.5 active:scale-95 transition cursor-pointer">
                        <i class="fa-solid fa-rotate-right"></i> Refresh
                    </button>
                    <button type="button" 
                            onclick="window.tbsToggleTheme()" 
                            class="py-2 rounded-xl bg-slate-100 dark:bg-purple-950/60 text-slate-700 dark:text-purple-300 font-extrabold text-[11px] flex items-center justify-center gap-1.5 active:scale-95 transition cursor-pointer">
                        <i class="fa-solid fa-circle-half-stroke"></i> Thema
                    </button>
                </div>

            </div>
        </div>
        @endif
    </div>

    <!-- GLOBAL TBS CONFIRMATION & ALERT MODAL (Eigen TBS Kliniek CSS & Alpine.js) -->
    <div x-data="{
        isOpen: false,
        isAlert: false,
        title: 'Weet je het zeker?',
        message: '',
        confirmBtn: 'Ja, doorgaan',
        cancelBtn: 'Annuleren',
        type: 'danger',
        icon: 'fa-triangle-exclamation',
        callback: null,
        init() {
            window.tbsConfirm = (opts) => {
                this.isAlert = false;
                this.title = opts.title || 'Weet je het zeker?';
                this.message = opts.message || 'Weet je zeker dat je deze actie wilt uitvoeren?';
                this.confirmBtn = opts.confirmBtn || 'Ja, uitvoeren';
                this.cancelBtn = opts.cancelBtn || 'Annuleren';
                this.type = opts.type || 'danger';
                this.icon = opts.icon || (this.type === 'attack' ? 'fa-bomb' : (this.type === 'logout' ? 'fa-right-from-bracket' : (this.type === 'warning' ? 'fa-triangle-exclamation' : 'fa-trash-can')));
                this.callback = opts.onConfirm || null;
                this.isOpen = true;
            };

            window.tbsAlert = (opts) => {
                if (typeof opts === 'string') opts = { message: opts };
                this.isAlert = true;
                this.title = opts.title || 'Melding';
                this.message = opts.message || '';
                this.confirmBtn = opts.confirmBtn || 'Begrepen';
                this.type = opts.type || 'info';
                this.icon = opts.icon || (this.type === 'success' ? 'fa-circle-check' : (this.type === 'danger' ? 'fa-circle-xmark' : 'fa-circle-info'));
                this.callback = opts.onConfirm || null;
                this.isOpen = true;
            };
        },
        confirm() {
            this.isOpen = false;
            if (typeof this.callback === 'function') {
                const cb = this.callback;
                this.callback = null;
                cb();
            }
        },
        cancel() {
            this.isOpen = false;
            this.callback = null;
        }
    }"
    @keydown.escape.window="cancel()"
    x-show="isOpen"
    x-cloak
    style="display: none;"
    class="fixed inset-0 z-[100] flex items-center justify-center p-4">
        
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-slate-950/80 transition-opacity" 
             x-show="isOpen"
             x-transition:enter="ease-out duration-150"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-100"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="cancel()"></div>

        <!-- Modal Dialog (Clean Light/Dark TBS Kliniek Styling) -->
        <div class="relative w-full max-w-md bg-white dark:bg-slate-900 rounded-3xl shadow-2xl border border-slate-200/90 dark:border-slate-800 overflow-hidden transform-gpu will-change-transform transition-all z-10"
             x-show="isOpen"
             x-transition:enter="ease-out duration-150"
             x-transition:enter-start="opacity-0 scale-95 translate-y-2"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="ease-in duration-100"
             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
             x-transition:leave-end="opacity-0 scale-95 translate-y-2">
            
            <!-- Top Gradient Bar -->
            <div class="h-1.5 w-full"
                 :class="{
                     'bg-gradient-to-r from-rose-500 via-pink-500 to-rose-600': type === 'attack' || type === 'danger',
                     'bg-gradient-to-r from-purple-800 via-indigo-700 to-slate-900 dark:from-purple-600 dark:to-indigo-600': type === 'logout',
                     'bg-gradient-to-r from-amber-400 via-orange-400 to-amber-500': type === 'warning',
                     'bg-gradient-to-r from-emerald-500 to-teal-500': type === 'success',
                     'bg-gradient-to-r from-purple-600 to-indigo-600': type === 'info'
                 }"></div>

            <!-- Modal Content -->
            <div class="p-6 sm:p-7 text-center">
                <!-- Close Button (X) -->
                <button type="button" @click="cancel()" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition w-8 h-8 rounded-full hover:bg-slate-100 dark:hover:bg-slate-800 flex items-center justify-center cursor-pointer">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>

                <!-- Icon Halo -->
                <div class="w-16 h-16 rounded-2xl flex items-center justify-center text-2xl mx-auto shadow-inner border transition-transform"
                     :class="{
                         'bg-rose-50 dark:bg-rose-950/50 text-rose-600 dark:text-rose-400 border-rose-200 dark:border-rose-900/60 ring-8 ring-rose-50/60 dark:ring-rose-950/30': type === 'attack' || type === 'danger',
                         'bg-purple-50 dark:bg-purple-950/50 text-purple-700 dark:text-purple-300 border-purple-200 dark:border-purple-900/60 ring-8 ring-purple-50/60 dark:ring-purple-950/30': type === 'logout',
                         'bg-amber-50 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400 border-amber-200 dark:border-amber-900/60 ring-8 ring-amber-50/60 dark:ring-amber-950/30': type === 'warning',
                         'bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 border-emerald-200 dark:border-emerald-900/60 ring-8 ring-emerald-50/60 dark:ring-emerald-950/30': type === 'success',
                         'bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 border-indigo-200 dark:border-indigo-900/60 ring-8 ring-indigo-50/60 dark:ring-indigo-950/30': type === 'info'
                     }">
                    <i class="fa-solid" :class="icon"></i>
                </div>

                <!-- Title & Message -->
                <h3 class="text-base sm:text-lg font-black text-slate-900 dark:text-white mt-4 tracking-tight" x-text="title"></h3>
                <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 mt-2 leading-relaxed px-2 font-medium" x-text="message"></p>

                <!-- Action Buttons -->
                <div class="mt-6 flex items-center gap-3">
                    <button type="button" 
                            x-show="!isAlert"
                            @click="cancel()" 
                            class="flex-1 py-2.5 px-4 rounded-xl border border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 font-bold text-xs transition cursor-pointer"
                            x-text="cancelBtn">
                    </button>
                    <button type="button" 
                            @click="confirm()" 
                            class="flex-1 py-2.5 px-4 rounded-xl text-white font-black text-xs uppercase tracking-wider transition shadow-md hover:shadow-lg cursor-pointer flex items-center justify-center gap-1.5"
                            :class="{
                                'bg-rose-600 hover:bg-rose-700 shadow-rose-200 dark:shadow-none': type === 'attack' || type === 'danger',
                                'bg-slate-900 hover:bg-slate-800 dark:bg-slate-800 dark:hover:bg-slate-700 shadow-slate-200 dark:shadow-none': type === 'logout',
                                'bg-amber-600 hover:bg-amber-700 shadow-amber-200 dark:shadow-none': type === 'warning',
                                'bg-emerald-600 hover:bg-emerald-700 shadow-emerald-200 dark:shadow-none': type === 'success',
                                'bg-indigo-600 hover:bg-indigo-700 shadow-indigo-200 dark:shadow-none': type === 'info'
                            }">
                        <i class="fa-solid" :class="icon"></i>
                        <span x-text="confirmBtn"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- GLOBAL SCRIPT FOR CONFIRM & ALERT INTERCEPTION -->
    <script>
        (function() {
            if (window.tbsConfirmInitialized) return;
            window.tbsConfirmInitialized = true;

            function parseConfirmOptions(msg, el) {
                let type = (el && el.dataset && el.dataset.confirmType) || 'danger';
                let icon = (el && el.dataset && el.dataset.confirmIcon) || 'fa-triangle-exclamation';
                let btn = (el && el.dataset && el.dataset.confirmBtn) || 'Ja, doorgaan';
                let title = (el && el.dataset && el.dataset.confirmTitle) || 'Bevestiging Vereist';

                // If explicit title or btn is set on element, prefer it
                if (el?.dataset?.confirmTitle) {
                    return { type, icon, btn, title };
                }

                const lower = (msg || '').toLowerCase();
                if (lower.includes('kopen') || lower.includes('aanschaffen') || lower.includes('investeren') || lower.includes('aankoop')) {
                    type = el?.dataset?.confirmType || 'success';
                    icon = el?.dataset?.confirmIcon || 'fa-building-circle-check';
                    btn = el?.dataset?.confirmBtn || 'Ja, Kopen';
                    title = el?.dataset?.confirmTitle || (lower.includes('pand') || lower.includes('hangar') || lower.includes('complex') ? 'Bendepand Kopen' : 'Aankoop Bevestigen');
                } else if (lower.includes('aannemen') || lower.includes('inhuren') || lower.includes('recruteren')) {
                    type = el?.dataset?.confirmType || 'success';
                    icon = el?.dataset?.confirmIcon || 'fa-user-plus';
                    btn = el?.dataset?.confirmBtn || 'Ja, Aannemen';
                    title = el?.dataset?.confirmTitle || 'Specialist Aannemen';
                } else if (lower.includes('heist') || lower.includes('onderschepping') || lower.includes('overval')) {
                    type = el?.dataset?.confirmType || 'attack';
                    icon = el?.dataset?.confirmIcon || 'fa-bomb';
                    btn = el?.dataset?.confirmBtn || 'Ja, Start Heist!';
                    title = el?.dataset?.confirmTitle || 'Heist Operatie Starten';
                } else if (lower.includes('aanvallen') || lower.includes('oorlog') || lower.includes('vecht')) {
                    type = el?.dataset?.confirmType || 'attack';
                    icon = el?.dataset?.confirmIcon || 'fa-crosshairs';
                    btn = el?.dataset?.confirmBtn || 'Ja, Aanvallen!';
                    title = el?.dataset?.confirmTitle || 'Aanval Bevestigen';
                } else if (lower.includes('verwijderen') || lower.includes('opheffen') || lower.includes('wissen')) {
                    type = el?.dataset?.confirmType || 'danger';
                    icon = el?.dataset?.confirmIcon || 'fa-trash-can';
                    btn = el?.dataset?.confirmBtn || 'Verwijderen';
                    title = el?.dataset?.confirmTitle || 'Verwijderen Bevestigen';
                } else if (lower.includes('uitloggen')) {
                    type = el?.dataset?.confirmType || 'logout';
                    icon = el?.dataset?.confirmIcon || 'fa-right-from-bracket';
                    btn = el?.dataset?.confirmBtn || 'Ja, Uitloggen';
                    title = el?.dataset?.confirmTitle || 'Veilig Uitloggen';
                } else if (lower.includes('sluiten')) {
                    type = el?.dataset?.confirmType || 'warning';
                    icon = el?.dataset?.confirmIcon || 'fa-lock';
                    btn = el?.dataset?.confirmBtn || 'Ja, Sluiten';
                    title = el?.dataset?.confirmTitle || 'Ticket Sluiten';
                } else if (lower.includes('bende verlaten') || lower.includes('gang verlaten') || (lower.includes('verlaten') && !lower.includes('kopen') && !lower.includes('aanschaffen') && !lower.includes('hangar') && !lower.includes('pand'))) {
                    type = el?.dataset?.confirmType || 'warning';
                    icon = el?.dataset?.confirmIcon || 'fa-right-from-bracket';
                    btn = el?.dataset?.confirmBtn || 'Ja, Bende Verlaten';
                    title = el?.dataset?.confirmTitle || 'Bende Verlaten';
                } else if (lower.includes('verbannen') || lower.includes('ban')) {
                    type = el?.dataset?.confirmType || 'danger';
                    icon = el?.dataset?.confirmIcon || 'fa-gavel';
                    btn = el?.dataset?.confirmBtn || 'Ja, Verbannen';
                    title = el?.dataset?.confirmTitle || 'Speler Verbannen';
                } else if (lower.includes('goedkeuren') || lower.includes('bonus')) {
                    type = el?.dataset?.confirmType || 'warning';
                    icon = el?.dataset?.confirmIcon || 'fa-circle-check';
                    btn = el?.dataset?.confirmBtn || 'Ja, Goedkeuren';
                    title = el?.dataset?.confirmTitle || 'Bonus Goedkeuren';
                }

                return { type, icon, btn, title };
            }

            function extractConfirmMessage(str) {
                if (!str) return null;
                const match = str.match(/confirm\s*\(\s*(['"`])(.*?)\1\s*\)/s);
                return match ? match[2] : null;
            }

            // 1. Intercept CLICKS in capture phase before inline onclick runs
            document.addEventListener('click', function(e) {
                const trigger = e.target.closest('[data-confirm], button, a, input[type="submit"], input[type="button"]');
                if (!trigger) return;

                if (trigger.dataset.tbsConfirmed === 'true') {
                    delete trigger.dataset.tbsConfirmed;
                    return;
                }

                const onclickAttr = trigger.getAttribute('onclick');
                let confirmMsg = null;
                let isLegacyOnclick = false;

                if (trigger.dataset && trigger.dataset.confirm) {
                    confirmMsg = trigger.dataset.confirm;
                } else if (onclickAttr && onclickAttr.includes('confirm(')) {
                    confirmMsg = extractConfirmMessage(onclickAttr) || trigger.getAttribute('data-confirm');
                    isLegacyOnclick = true;
                }

                if (!confirmMsg) return;

                // Stop native inline onclick and browser confirm dialog!
                e.preventDefault();
                e.stopPropagation();
                e.stopImmediatePropagation();

                const form = trigger.closest('form');
                const parsed = parseConfirmOptions(confirmMsg, trigger);

                if (window.tbsConfirm) {
                    window.tbsConfirm({
                        title: parsed.title,
                        message: confirmMsg,
                        confirmBtn: parsed.btn,
                        cancelBtn: trigger.dataset.confirmCancel || 'Annuleren',
                        type: parsed.type,
                        icon: parsed.icon,
                        onConfirm: function() {
                            trigger.dataset.tbsConfirmed = 'true';
                            if (form && (trigger.type === 'submit' || trigger.tagName === 'BUTTON' || trigger.tagName === 'INPUT')) {
                                form.dataset.tbsConfirmed = 'true';
                                if (isLegacyOnclick) {
                                    trigger.removeAttribute('onclick');
                                    form.requestSubmit ? form.requestSubmit(trigger) : form.submit();
                                    setTimeout(() => trigger.setAttribute('onclick', onclickAttr), 150);
                                } else {
                                    form.requestSubmit ? form.requestSubmit(trigger) : form.submit();
                                }
                            } else if (trigger.tagName === 'A' && trigger.href) {
                                window.location.href = trigger.href;
                            } else {
                                if (isLegacyOnclick) {
                                    trigger.removeAttribute('onclick');
                                    trigger.click();
                                    setTimeout(() => trigger.setAttribute('onclick', onclickAttr), 150);
                                } else {
                                    trigger.click();
                                }
                            }
                        }
                    });
                }
            }, true);

            // 2. Intercept FORM SUBMITS (e.g. onsubmit="return confirm(...)") in capture phase
            document.addEventListener('submit', function(e) {
                const form = e.target;
                if (!form || form.dataset.tbsConfirmed === 'true') {
                    if (form) delete form.dataset.tbsConfirmed;
                    return;
                }

                const submitter = e.submitter || (document.activeElement && form.contains(document.activeElement) ? document.activeElement : null);
                const confirmSource = (submitter && submitter.dataset && submitter.dataset.confirm) ? submitter : form;

                let confirmMsg = null;
                let isLegacyOnsubmit = false;
                const onsubmitAttr = form.getAttribute('onsubmit');

                if (confirmSource.dataset && confirmSource.dataset.confirm) {
                    confirmMsg = confirmSource.dataset.confirm;
                } else if (onsubmitAttr && onsubmitAttr.includes('confirm(')) {
                    confirmMsg = extractConfirmMessage(onsubmitAttr);
                    isLegacyOnsubmit = true;
                }

                if (!confirmMsg) return;

                e.preventDefault();
                e.stopPropagation();
                e.stopImmediatePropagation();

                const parsed = parseConfirmOptions(confirmMsg, confirmSource);

                if (window.tbsConfirm) {
                    window.tbsConfirm({
                        title: parsed.title,
                        message: confirmMsg,
                        confirmBtn: parsed.btn,
                        cancelBtn: confirmSource.dataset.confirmCancel || 'Annuleren',
                        type: parsed.type,
                        icon: parsed.icon,
                        onConfirm: function() {
                            form.dataset.tbsConfirmed = 'true';
                            if (isLegacyOnsubmit) {
                                form.removeAttribute('onsubmit');
                                form.submit();
                                setTimeout(() => form.setAttribute('onsubmit', onsubmitAttr), 150);
                            } else {
                                form.submit();
                            }
                        }
                    });
                }
            }, true);

            // 3. Fallback: override window.confirm & window.alert so native browser popups are never used
            window.confirm = function(msg) {
                if (window.tbsConfirm) {
                    const parsed = parseConfirmOptions(msg, null);
                    window.tbsConfirm({
                        title: parsed.title,
                        message: msg,
                        confirmBtn: parsed.btn,
                        cancelBtn: 'Annuleren',
                        type: parsed.type,
                        icon: parsed.icon
                    });
                }
                return false;
            };

            window.alert = function(msg) {
                if (window.tbsAlert) {
                    window.tbsAlert({ message: msg });
                }
            };
        })();
    </script>

    <!-- FLOATING MINI LIVE CHAT & UNIVERSAL REPORT MODAL -->
    @auth
        @include('partials.mini_chat_and_report_modal')
        <!-- GLOBAL SPOTLIGHT COMMAND PALETTE & FAST SEARCH (CTRL + K) -->
        @include('partials.spotlight_modal')
    @endauth

    <!-- GLOBAL REWARDED SPONSOR AD WATCH MODAL & COOKIE CONSENT -->
    @include('partials.sponsor_and_cookie_modals')
    <!-- PWA NATIVE APP PROMPT & BANNER -->
    @include('partials.pwa_prompt')

    <!-- BUY ME A COFFEE WIDGET (Positioned cleanly above Live Chat) -->
    <style>
        #bmc-wbtn {
            position: fixed !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            width: 44px !important;
            height: 44px !important;
            min-width: 44px !important;
            min-height: 44px !important;
            max-width: 44px !important;
            max-height: 44px !important;
            background: linear-gradient(135deg, #a855f7 0%, #7c3aed 100%) !important;
            color: #ffffff !important;
            border-radius: 9999px !important;
            border: 2px solid rgba(255, 255, 255, 0.35) !important;
            box-shadow: 0 4px 14px rgba(124, 58, 237, 0.4), 0 2px 4px rgba(0, 0, 0, 0.15) !important;
            top: auto !important;
            bottom: calc(env(safe-area-inset-bottom, 0px) + 120px) !important;
            right: 16px !important;
            left: auto !important;
            z-index: 9997 !important;
            transition: transform 0.2s cubic-bezier(0.34, 1.56, 0.64, 1), box-shadow 0.2s ease, opacity 0.2s ease !important;
            cursor: pointer !important;
            overflow: hidden !important;
            text-decoration: none !important;
            padding: 0 !important;
            margin: 0 !important;
        }
        #bmc-wbtn:hover {
            transform: scale(1.1) !important;
            box-shadow: 0 6px 20px rgba(124, 58, 237, 0.6) !important;
        }
        #bmc-wbtn:active {
            transform: scale(0.95) !important;
        }
        #bmc-wbtn img {
            width: 24px !important;
            height: 24px !important;
            min-width: 24px !important;
            min-height: 24px !important;
            max-width: 24px !important;
            max-height: 24px !important;
            display: block !important;
            margin: auto !important;
            object-fit: contain !important;
            filter: drop-shadow(0 1px 2px rgba(0,0,0,0.25)) !important;
            pointer-events: none !important;
        }
        @media (min-width: 1024px) {
            #bmc-wbtn {
                position: fixed !important;
                top: auto !important;
                bottom: 74px !important;
                right: 24px !important;
                left: auto !important;
            }
        }
        /* When Live Chat popup is open, hide BMC button so it does not obstruct the chat window */
        #bmc-wbtn svg {
            width: 18px !important;
            height: 18px !important;
            display: block !important;
            margin: auto !important;
        }
        body.tbs-chat-open #bmc-wbtn {
            opacity: 0 !important;
            pointer-events: none !important;
            transform: scale(0.8) !important;
        }
        #bmc-iframe {
            z-index: 999999 !important;
        }
        @media (max-width: 1023px) {
            #bmc-wbtn, #bmc-iframe, #bmc-permanent-holder, #tbs-floating-mini-chat {
                display: none !important;
            }
        }
    </style>
    <div id="bmc-permanent-holder" data-turbo-permanent></div>
    <script data-name="BMC-Widget" data-cfasync="false" src="https://cdnjs.buymeacoffee.com/1.0.0/widget.prod.min.js" data-id="tbskliniek" data-description="Support me on Buy me a coffee!" data-message="" data-color="#7c3aed" data-position="Right" data-x_margin="24" data-y_margin="74"></script>
    <script>
        (function() {
            function adoptBmcElements() {
                const holder = document.getElementById('bmc-permanent-holder');
                if (!holder) return;

                const bmcBtn = document.getElementById('bmc-wbtn');
                const bmcIframe = document.getElementById('bmc-iframe');

                if (bmcBtn && bmcBtn.parentNode !== holder) {
                    holder.appendChild(bmcBtn);
                }
                if (bmcIframe) {
                    const overlay = bmcIframe.parentElement || bmcIframe;
                    if (overlay && overlay !== holder && overlay.parentNode !== holder) {
                        holder.appendChild(overlay);
                    }
                }
            }

            // Run check periodically to preserve BMC widget elements across Turbo navigations
            let checkCount = 0;
            const timer = setInterval(() => {
                adoptBmcElements();
                checkCount++;
                if (document.getElementById('bmc-wbtn') && document.getElementById('bmc-iframe')) {
                    clearInterval(timer);
                } else if (checkCount > 30) {
                    clearInterval(timer);
                }
            }, 300);

            document.addEventListener('turbo:load', adoptBmcElements);
        })();
    </script>

    <!-- GLOBAL THEME TOGGLE SCRIPT & BFCACHE RELOAD HANDLER -->
    <script>
        window.tbsToggleTheme = function() {
            if (document.documentElement.classList.contains('dark')) {
                document.documentElement.classList.remove('dark');
                localStorage.setItem('tbs_theme', 'light');
            } else {
                document.documentElement.classList.add('dark');
                localStorage.setItem('tbs_theme', 'dark');
            }
        };

        // When navigating back/forward via browser history (bfcache), ensure fresh state & styles
        window.addEventListener('pageshow', function(event) {
            if (event.persisted) {
                window.location.reload();
            }
        });
    </script>

    <!-- TBS IN-APP FLOATING TOAST NOTIFICATION CONTAINER (App Feel with Progress Bar & Close Action) -->
    <div id="tbs-toast-container" 
         x-data="{
             toasts: [],
             addToast(msg, type = 'success', duration = 4500) {
                 if (!msg) return;
                 const trimmed = String(msg).trim();
                 if (!trimmed) return;

                 // Deduplicate: ignore if an identical message is already currently displayed
                 if (this.toasts.some(t => t.msg === trimmed)) return;

                 // Rate-limit identical messages within 2 seconds
                 if (window._tbsLastToast && window._tbsLastToast.msg === trimmed && (Date.now() - window._tbsLastToast.time) < 2000) {
                     return;
                 }
                 window._tbsLastToast = { msg: trimmed, time: Date.now() };

                 // Keep maximum 2 toasts visible at any time to prevent flooding the screen
                 if (this.toasts.length >= 2) {
                     const oldest = this.toasts.shift();
                     if (oldest && oldest.timer) clearTimeout(oldest.timer);
                 }

                 const id = 'toast_' + Date.now() + '_' + Math.random().toString(36).substr(2, 6);
                 const toastObj = {
                     id: id,
                     msg: trimmed,
                     type: type,
                     duration: duration,
                     timer: null
                 };
                 toastObj.timer = setTimeout(() => {
                     this.removeToast(toastObj);
                 }, duration);
                 this.toasts.push(toastObj);
             },
             removeToast(toast) {
                 if (toast && toast.timer) clearTimeout(toast.timer);
                 this.toasts = this.toasts.filter(t => t.id !== toast.id);
             }
         }"
         x-init="
             window.tbsToast = (msg, type, duration) => addToast(msg, type, duration);
         "
         class="tbs-toast-container fixed left-1/2 -translate-x-1/2 lg:left-[calc(50%+8rem)] z-[9999999] flex flex-col items-center gap-2.5 pointer-events-none max-w-lg w-[94%] sm:w-auto">
        <template x-for="toast in toasts" :key="toast.id">
            <div x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 -translate-y-4 scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                 x-transition:leave-end="opacity-0 -translate-y-2 scale-95"
                 class="pointer-events-auto relative overflow-hidden flex flex-col rounded-2xl border shadow-2xl min-w-[300px] sm:min-w-[380px] max-w-md w-full bg-white dark:bg-[#120a2e] text-slate-900 dark:text-white transition-all select-none border-slate-200 dark:border-purple-800">
                
                <div class="flex items-center gap-3 p-3.5 sm:p-4">
                    <!-- Icon Badge -->
                    <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0 text-white font-black text-sm shadow-sm"
                         :class="{
                             'bg-emerald-500': toast.type === 'success',
                             'bg-rose-500': toast.type === 'error' || toast.type === 'danger',
                             'bg-amber-500': toast.type === 'warning',
                             'bg-purple-600': toast.type === 'info'
                         }">
                        <template x-if="toast.type === 'success'">
                            <i class="fa-solid fa-check text-base"></i>
                        </template>
                        <template x-if="toast.type === 'error' || toast.type === 'danger'">
                            <i class="fa-solid fa-exclamation text-base"></i>
                        </template>
                        <template x-if="toast.type === 'warning'">
                            <i class="fa-solid fa-triangle-exclamation text-sm"></i>
                        </template>
                        <template x-if="toast.type === 'info'">
                            <i class="fa-solid fa-info text-base"></i>
                        </template>
                    </div>

                    <!-- Message Text -->
                    <div class="flex-1 pr-1 text-xs sm:text-[13px] font-extrabold leading-snug break-words text-slate-800 dark:text-slate-100" 
                         x-text="toast.msg"></div>

                    <!-- Kruisje (Close Button) with direct reliable hit area and visual response -->
                    <button type="button" 
                            @click.stop="removeToast(toast)" 
                            class="w-8 h-8 rounded-xl bg-slate-100 dark:bg-purple-950/80 hover:bg-slate-200 dark:hover:bg-purple-900/60 active:scale-90 text-slate-400 hover:text-slate-700 dark:hover:text-white flex items-center justify-center transition cursor-pointer shrink-0 ml-1 border border-slate-200/60 dark:border-purple-800/60"
                            title="Melding sluiten">
                        <i class="fa-solid fa-xmark text-sm pointer-events-none"></i>
                    </button>
                </div>

                <!-- Tijdbalkje / Loading Progress Bar (ScaleX countdown with glowing fill) -->
                <div class="w-full h-1 bg-slate-100 dark:bg-slate-900/80 overflow-hidden relative">
                    <div class="h-full w-full"
                         :class="{
                             'bg-emerald-500 shadow-[0_0_8px_rgba(16,185,129,0.8)]': toast.type === 'success',
                             'bg-rose-500 shadow-[0_0_8px_rgba(244,63,94,0.8)]': toast.type === 'error' || toast.type === 'danger',
                             'bg-amber-500 shadow-[0_0_8px_rgba(245,158,11,0.8)]': toast.type === 'warning',
                             'bg-purple-600 shadow-[0_0_8px_rgba(147,51,234,0.8)]': toast.type === 'info'
                         }"
                         :style="'transform-origin: left center; animation: tbsToastCountdown ' + (toast.duration || 4500) + 'ms linear forwards;'"></div>
                </div>
            </div>
        </template>
    </div>

    @auth
    <!-- TBS PUSH PROMPT INCENTIVE BANNER (PC & Mobiel) -->
    <div id="tbs-push-incentive-banner"
         x-data="tbsPushBanner()"
         x-init="init()"
         x-show="visible"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-6 scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
         x-transition:leave-end="opacity-0 translate-y-6 scale-95"
         style="display: none;"
         class="fixed bottom-20 sm:bottom-6 right-3 sm:right-6 left-3 sm:left-auto sm:max-w-md z-[999998] p-4 rounded-2xl bg-slate-900/95 dark:bg-[#130b2e]/95 backdrop-blur-md border border-purple-500/40 shadow-2xl text-white select-none">
        
        <div class="flex items-start gap-3">
            <div class="relative w-10 h-10 rounded-xl bg-purple-600/30 border border-purple-500/50 flex items-center justify-center text-purple-300 shrink-0 mt-0.5">
                <i class="fa-solid fa-bell text-lg animate-bounce"></i>
                <span class="absolute -top-1 -right-1 w-2.5 h-2.5 bg-emerald-400 rounded-full animate-ping"></span>
                <span class="absolute -top-1 -right-1 w-2.5 h-2.5 bg-emerald-500 rounded-full"></span>
            </div>

            <div class="flex-1 space-y-1.5 min-w-0">
                <div class="flex items-center justify-between gap-2">
                    <h4 class="text-xs sm:text-sm font-black text-white tracking-wide">
                        Mis Geen Aanvallen &amp; Diefstallen!
                    </h4>
                    <button type="button" @click="dismiss()" class="text-slate-400 hover:text-white transition p-1 -mr-1 cursor-pointer" title="Later herinneren">
                        <i class="fa-solid fa-xmark text-sm"></i>
                    </button>
                </div>

                <p class="text-[11px] text-slate-300 leading-relaxed">
                    Ontvang direct een melding op je pc of telefoon als je offline bestolen of aangevallen wordt.
                </p>

                <div class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md bg-amber-400/15 border border-amber-400/30 text-amber-300 font-extrabold text-[10px]">
                    <i class="fa-solid fa-gift"></i>
                    <span>Ontvang direct € 25.000 contant + 25 respect bij activatie!</span>
                </div>

                <template x-if="isIosNonStandalone">
                    <div class="p-2 rounded-lg bg-purple-950/60 border border-purple-800/60 text-[10px] text-purple-200 mt-2 flex items-center gap-2">
                        <i class="fa-solid fa-mobile-screen-button text-xs text-purple-400 shrink-0"></i>
                        <span>Op iPhone? Tik op <i class="fa-solid fa-arrow-up-from-bracket"></i> <b>Delen</b> en kies <b>Zet op beginscherm</b> voor meldingen.</span>
                    </div>
                </template>

                <div class="pt-2 flex items-center gap-2">
                    <button type="button" @click="enablePush()" :disabled="loading"
                            class="flex-1 py-2 px-3 rounded-xl bg-purple-600 hover:bg-purple-500 text-white font-extrabold text-xs transition flex items-center justify-center gap-1.5 shadow-md cursor-pointer disabled:opacity-50">
                        <i class="fa-solid" :class="loading ? 'fa-spinner animate-spin' : 'fa-bell'"></i>
                        <span x-text="loading ? 'Bezig...' : 'Nu Inschakelen &amp; Bonus Pakken'"></span>
                    </button>
                    <button type="button" @click="dismiss()"
                            class="py-2 px-3 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 font-bold text-xs transition cursor-pointer">
                        Later
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function tbsPushBanner() {
            return {
                visible: false,
                loading: false,
                isIosNonStandalone: false,

                async init() {
                    const isIos = /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;
                    const isStandalone = ('standalone' in navigator && navigator.standalone) || window.matchMedia('(display-mode: standalone)').matches;
                    this.isIosNonStandalone = isIos && !isStandalone;

                    const dismissedUntil = localStorage.getItem('tbs_push_dismissed_until');
                    if (dismissedUntil && Number(dismissedUntil) > Date.now()) {
                        return;
                    }

                    if (!('Notification' in window) || !('serviceWorker' in navigator)) {
                        return;
                    }

                    if (Notification.permission === 'granted' || Notification.permission === 'denied') {
                        return;
                    }

                    setTimeout(() => {
                        this.visible = true;
                    }, 2500);
                },

                dismiss() {
                    this.visible = false;
                    localStorage.setItem('tbs_push_dismissed_until', String(Date.now() + (48 * 60 * 60 * 1000)));
                },

                async enablePush() {
                    if (!window.tbsPush) return;
                    this.loading = true;
                    try {
                        const res = await window.tbsPush.subscribe();
                        if (res.success) {
                            this.visible = false;
                            localStorage.setItem('tbs_push_dismissed_until', String(Date.now() + (365 * 24 * 60 * 60 * 1000)));
                            
                            const msg = res.message || 'Pushnotificaties zijn geactiveerd!';
                            if (typeof window.tbsToast === 'function') {
                                window.tbsToast(msg, 'success', 6000);
                            } else {
                                alert(msg);
                            }
                        } else {
                            if (typeof window.tbsToast === 'function') {
                                window.tbsToast(res.message || 'Inschakelen mislukt.', 'error');
                            } else {
                                alert(res.message || 'Inschakelen mislukt.');
                            }
                        }
                    } catch (err) {
                        if (typeof window.tbsToast === 'function') {
                            window.tbsToast(err.message || 'Fout bij inschakelen.', 'error');
                        } else {
                            alert(err.message || 'Fout bij inschakelen.');
                        }
                    } finally {
                        this.loading = false;
                    }
                }
            };
        }
    </script>
    @endauth

    @if(!request()->header('X-TBS-Background-Refresh') && !request()->header('X-TBS-Action'))
    @php
        $flashService = app(\App\Services\FlashNotificationService::class);
        $flashUserIdentifier = Auth::id() ?? session()->getId();
        $resilientAlert = $flashService->consume($flashUserIdentifier);

        $flashSuccess = session('success') ?? session('status');
        if (!$flashSuccess && session()->has('tbs_flash_success')) {
            $flashTime = (int) (session('tbs_flash_time') ?? 0);
            if ($flashTime === 0 || (now()->timestamp - $flashTime) <= 60) {
                $flashSuccess = session('tbs_flash_success');
            }
        }
        if (!$flashSuccess && $resilientAlert && $resilientAlert['type'] === 'success') {
            $flashSuccess = $resilientAlert['message'];
        }
        if (session()->has('tbs_flash_success')) {
            session()->forget(['tbs_flash_success', 'tbs_flash_time']);
        }

        $flashError = session('error') ?? (isset($errors) && $errors->any() ? $errors->first() : null);
        if (!$flashError && session()->has('tbs_flash_error')) {
            $flashTime = (int) (session('tbs_flash_time') ?? 0);
            if ($flashTime === 0 || (now()->timestamp - $flashTime) <= 60) {
                $flashError = session('tbs_flash_error');
            }
        }
        if (!$flashError && $resilientAlert && $resilientAlert['type'] === 'error') {
            $flashError = $resilientAlert['message'];
        }
        if (session()->has('tbs_flash_error')) {
            session()->forget(['tbs_flash_error', 'tbs_flash_time']);
        }
    @endphp

    @if($flashSuccess)
    <script>
        (function() {
            function fireToast() {
                if (typeof window.tbsToast === 'function') {
                    window.tbsToast(@json($flashSuccess), 'success');
                } else {
                    setTimeout(fireToast, 50);
                }
            }
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', fireToast);
            } else {
                fireToast();
            }
        })();
    </script>
    @endif

    @if($flashError)
    <script>
        (function() {
            function fireToast() {
                if (typeof window.tbsToast === 'function') {
                    window.tbsToast(@json($flashError), 'error');
                } else {
                    setTimeout(fireToast, 50);
                }
            }
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', fireToast);
            } else {
                fireToast();
            }
        })();
    </script>
    @endif
    @endif
</body>
</html>
