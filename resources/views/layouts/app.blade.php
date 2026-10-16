<!DOCTYPE html>
<html lang="nl" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <meta name="turbo-prefetch" content="false">

    <!-- GOOGLE ADSENSE (Tijdelijk uitgeschakeld) -->
    {{--
    <meta name="google-adsense-account" content="ca-pub-2509357892188070">
    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-2509357892188070"
         crossorigin="anonymous"></script>
    --}}

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        [x-cloak] {
            display: none !important;
        }
        html, body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            text-rendering: optimizeLegibility;
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
        if (request()->routeIs('timeline.*', 'friends.*', 'leaderboard.*', 'battle.*', 'prison', 'gangs.*', 'news.*', 'newspaper.*', 'roadmap.*') || request()->is('tijdlijn*', 'vrienden*', 'ranglijst*', 'battle*', 'isoleercel*', 'gangs*', 'bendes*', 'nieuws*', 'afdelingsblad*', 'roadmap*')) {
            $activeCategory = 'algemeen';
        } elseif (request()->routeIs('missions.*', 'training.*', 'treatment.*', 'infirmary.*', 'daycare.*') || request()->is('missies*', 'training*', 'behandeling*', 'ziekenboeg*', 'dagbesteding*')) {
            $activeCategory = 'voortgang';
        } elseif (request()->routeIs('hustle.*', 'smuggle.*', 'extort.*', 'lockers.*') || request()->is('hosselen*', 'smokkelen*', 'afpersen*', 'kluisjes*')) {
            $activeCategory = 'misdaad';
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
        function tbsStatsTracker() {
            return {
                mobileMenuOpen: false,
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
                    energy_pct: {{ max(18, min(100, round(($character->energy / max(1, $character->max_energy)) * 100))) }},
                    health: {{ (int)$character->health }},
                    max_health: {{ (int)$character->max_health }},
                    health_pct: {{ max(18, min(100, round(($character->health / max(1, $character->max_health)) * 100))) }},
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
                    setInterval(() => {
                        if (document.visibilityState === 'visible') {
                            this.refresh();
                        }
                    }, 4000);
                    window.addEventListener('stats:refresh', () => this.refresh());
                },
                async refresh() {
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
                            this.stats = {
                                ...this.stats,
                                ...data,
                                cashFormatted: data.cashFormatted || data.cash_formatted || this.stats.cashFormatted,
                                bankFormatted: data.bankFormatted || data.bank_formatted || this.stats.bankFormatted,
                                totalFormatted: data.totalFormatted || data.total_formatted || this.stats.totalFormatted,
                                starsFormatted: data.starsFormatted || data.stars_formatted || this.stats.starsFormatted,
                                vipPointsFormatted: data.vipPointsFormatted || data.vip_points_formatted || this.stats.vipPointsFormatted,
                                respect: data.formatted_respect || data.respect || this.stats.respect,
                                onlineCount: (data.onlineCount !== undefined) ? data.onlineCount : this.stats.onlineCount
                            };
                        }
                    } catch (e) {}
                }
            };
        }
    </script>
    @else
    <script>
        function tbsStatsTracker() {
            return {
                mobileMenuOpen: false,
                stats: {}
            };
        }
    </script>
    @endif
</head>
<body class="min-h-full flex flex-col lg:flex-row bg-[#f8fafc] text-slate-800 antialiased selection:bg-slate-900 selection:text-white dark:bg-[#0b0f19] dark:text-slate-100" @if($character) x-data="tbsStatsTracker()" @else x-data="{ mobileMenuOpen: false }" @endif>

    @if($character)
    <!-- SIDEBAR NAVIGATION (Desktop & Tablet) - Clean Modern White Sidebar -->
    <aside class="hidden lg:flex lg:flex-col w-64 bg-white border-r border-slate-200/90 shrink-0 min-h-screen text-slate-700 z-40 sticky top-0 h-screen overflow-y-auto shadow-xs select-none"
           x-data="{
               openCategory: localStorage.getItem('tbs_sidebar_category') || '{{ $activeCategory }}',
               init() {
                   const serverCat = '{{ $activeCategory }}';
                   if (serverCat) {
                       this.openCategory = serverCat;
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
        <nav class="flex-1 px-3 py-4 space-y-2 text-xs font-semibold">

            <!-- Vaste Hoofdnavigatie Boven Dropdowns (Dashboard & Berichten & Post) -->
            <div class="space-y-1 pb-2 mb-1 border-b border-slate-100 dark:border-slate-800/60">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('dashboard') ? 'nav-item-active bg-indigo-50/90 dark:bg-purple-950/60 text-indigo-700 dark:text-purple-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                    <i class="fa-solid fa-gauge w-4 text-center text-indigo-500 dark:text-indigo-400"></i>
                    <span>Dashboard</span>
                </a>
                <a href="{{ route('messages.index') }}" class="flex items-center justify-between px-3 py-2 rounded-lg transition {{ request()->routeIs('messages.*') ? 'nav-item-active bg-indigo-50/90 dark:bg-purple-950/60 text-indigo-700 dark:text-purple-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                    <div class="flex items-center gap-3">
                        <i class="fa-solid fa-envelope w-4 text-center text-blue-500 dark:text-blue-400"></i>
                        <span>Berichten & Post</span>
                    </div>
                    @php
                        $unreadMsgCount = \App\Models\GameMessage::where('recipient_id', $character->id)->where('is_read', false)->count();
                    @endphp
                    @if($unreadMsgCount > 0)
                        <span class="bg-rose-500 text-white text-[10px] font-extrabold px-1.5 py-0.2 rounded-full">{{ $unreadMsgCount }}</span>
                    @endif
                </a>
            </div>

            <!-- 1. ALGEMEEN (Dropdown / Accordion) -->
            <div class="space-y-1">
                <button @click="toggleCategory('algemeen')" 
                        type="button" 
                        class="w-full flex items-center justify-between px-3 py-2 text-[10px] font-extrabold uppercase tracking-wider text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 transition">
                    <span class="flex items-center gap-2">
                        <i class="fa-solid fa-shield-halved text-slate-400"></i> Algemeen
                    </span>
                    <i class="fa-solid fa-chevron-down text-[9px] transition-transform duration-200" :class="openCategory === 'algemeen' ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="openCategory === 'algemeen'" x-collapse class="space-y-0.5 pl-1">
                    <a href="{{ route('news.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('news.*') ? 'nav-item-active bg-indigo-50/90 dark:bg-purple-950/60 text-indigo-700 dark:text-purple-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                        <i class="fa-solid fa-newspaper w-4 text-center text-purple-600 dark:text-purple-400"></i>
                        <span>Kliniek Nieuws</span>
                    </a>
                    <a href="{{ route('newspaper.index') }}" class="flex items-center justify-between px-3 py-2 rounded-lg transition {{ request()->routeIs('newspaper.*') ? 'nav-item-active bg-indigo-50/90 dark:bg-purple-950/60 text-indigo-700 dark:text-purple-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                        <div class="flex items-center gap-3">
                            <i class="fa-solid fa-file-lines w-4 text-center text-indigo-500 dark:text-indigo-400"></i>
                            <span>Het Afdelingsblad</span>
                        </div>
                        <span class="bg-indigo-100 dark:bg-indigo-900/60 text-indigo-700 dark:text-indigo-300 text-[9px] font-black uppercase px-1.5 py-0.5 rounded tracking-wide">
                            Editie
                        </span>
                    </a>
                    <a href="{{ route('radio.index') }}" class="flex items-center justify-between px-3 py-2 rounded-lg transition {{ request()->routeIs('radio.*') ? 'nav-item-active bg-indigo-50/90 dark:bg-purple-950/60 text-indigo-700 dark:text-purple-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                        <div class="flex items-center gap-3">
                            <i class="fa-solid fa-radio w-4 text-center text-purple-500 dark:text-purple-400"></i>
                            <span>TBS FM (Radio)</span>
                        </div>
                        <span class="bg-rose-100 dark:bg-rose-900/60 text-rose-700 dark:text-rose-300 text-[9px] font-black uppercase px-1.5 py-0.5 rounded tracking-wide animate-pulse">
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
                        <span class="bg-purple-100 dark:bg-purple-900/60 text-purple-700 dark:text-purple-300 text-[9px] font-black uppercase px-1.5 py-0.5 rounded tracking-wide">
                            Live
                        </span>
                    </a>
                    <a href="{{ route('prison') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('prison') ? 'nav-item-active bg-indigo-50/90 dark:bg-purple-950/60 text-indigo-700 dark:text-purple-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                        <i class="fa-solid fa-dungeon w-4 text-center text-rose-500 dark:text-rose-400"></i>
                        <span>Isoleercel</span>
                    </a>
                    <a href="{{ route('gangs.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('gangs.*') ? 'nav-item-active bg-indigo-50/90 dark:bg-purple-950/60 text-indigo-700 dark:text-purple-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                        <i class="fa-solid fa-users w-4 text-center text-purple-500 dark:text-purple-400"></i>
                        <span>Bendes & Gangs</span>
                    </a>
                    <a href="{{ route('roadmap.index') }}" class="flex items-center justify-between px-3 py-2 rounded-lg transition {{ request()->routeIs('roadmap.*') ? 'nav-item-active bg-indigo-50/90 dark:bg-purple-950/60 text-indigo-700 dark:text-purple-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                        <div class="flex items-center gap-3">
                            <i class="fa-solid fa-compass w-4 text-center text-amber-500 dark:text-amber-400"></i>
                            <span>Toekomst & Roadmap</span>
                        </div>
                        <span class="bg-purple-100 dark:bg-purple-900/60 text-purple-700 dark:text-purple-300 text-[9px] font-black uppercase px-1.5 py-0.5 rounded tracking-wide">
                            10 Updates
                        </span>
                    </a>
                </div>
            </div>

            <!-- 2. VOORTGANG (Apart Kopje) -->
            <div class="space-y-1">
                <button @click="toggleCategory('voortgang')" 
                        type="button" 
                        class="w-full flex items-center justify-between px-3 py-2 text-[10px] font-extrabold uppercase tracking-wider text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 transition">
                    <span class="flex items-center gap-2">
                        <i class="fa-solid fa-chart-line text-indigo-500"></i> Voortgang
                    </span>
                    <i class="fa-solid fa-chevron-down text-[9px] transition-transform duration-200" :class="openCategory === 'voortgang' ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="openCategory === 'voortgang'" x-collapse class="space-y-0.5 pl-1">
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
                    <a href="{{ route('infirmary.index') }}" class="flex items-center justify-between px-3 py-2 rounded-lg transition {{ request()->routeIs('infirmary.*') ? 'nav-item-active bg-indigo-50/90 dark:bg-purple-950/60 text-indigo-700 dark:text-purple-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                        <div class="flex items-center gap-3">
                            <i class="fa-solid fa-hospital-user w-4 text-center text-teal-500 dark:text-teal-400"></i>
                            <span>Ziekenboeg & Pillen</span>
                        </div>
                        <span class="bg-teal-100 dark:bg-teal-900/60 text-teal-700 dark:text-teal-300 text-[9px] font-black uppercase px-1.5 py-0.5 rounded tracking-wide">
                            Heal
                        </span>
                    </a>
                    <a href="{{ route('daycare.index') }}" class="flex items-center justify-between px-3 py-2 rounded-lg transition {{ request()->routeIs('daycare.*') ? 'nav-item-active bg-indigo-50/90 dark:bg-purple-950/60 text-indigo-700 dark:text-purple-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                        <div class="flex items-center gap-3">
                            <i class="fa-solid fa-person-digging w-4 text-center text-amber-500 dark:text-amber-400"></i>
                            <span>Dagbesteding & Werk</span>
                        </div>
                        <span class="bg-amber-100 dark:bg-amber-900/60 text-amber-700 dark:text-amber-300 text-[9px] font-black uppercase px-1.5 py-0.5 rounded tracking-wide">
                            Veilig
                        </span>
                    </a>
                </div>
            </div>

            <!-- 3. ILLEGALE ACTIES -->
            <div class="space-y-1">
                <button @click="toggleCategory('misdaad')" 
                        type="button" 
                        class="w-full flex items-center justify-between px-3 py-2 text-[10px] font-extrabold uppercase tracking-wider text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 transition">
                    <span class="flex items-center gap-2">
                        <i class="fa-solid fa-handcuffs text-slate-400"></i> Illegale Acties
                    </span>
                    <i class="fa-solid fa-chevron-down text-[9px] transition-transform duration-200" :class="openCategory === 'misdaad' ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="openCategory === 'misdaad'" x-collapse class="space-y-0.5 pl-1">
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
                    <a href="{{ route('droppings.index') }}" class="flex items-center justify-between px-3 py-2 rounded-lg transition {{ request()->routeIs('droppings.*') ? 'nav-item-active bg-indigo-50/90 dark:bg-purple-950/60 text-indigo-700 dark:text-purple-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                        <div class="flex items-center gap-3">
                            <i class="fa-solid fa-plane-arrival w-4 text-center text-emerald-500 dark:text-emerald-400"></i>
                            <span>Tennisbal & Drones</span>
                        </div>
                        <span class="bg-emerald-100 dark:bg-emerald-900/60 text-emerald-700 dark:text-emerald-300 text-[9px] font-black uppercase px-1.5 py-0.5 rounded tracking-wide">
                            Drop
                        </span>
                    </a>
                    <a href="{{ route('tunnel.index') }}" class="flex items-center justify-between px-3 py-2 rounded-lg transition {{ request()->routeIs('tunnel.*') ? 'nav-item-active bg-indigo-50/90 dark:bg-purple-950/60 text-indigo-700 dark:text-purple-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                        <div class="flex items-center gap-3">
                            <i class="fa-solid fa-person-digging w-4 text-center text-amber-500 dark:text-amber-400"></i>
                            <span>Uitbraaktunnel</span>
                        </div>
                        <span class="bg-amber-100 dark:bg-amber-900/60 text-amber-700 dark:text-amber-300 text-[9px] font-black uppercase px-1.5 py-0.5 rounded tracking-wide">
                            Tunnel
                        </span>
                    </a>
                </div>
            </div>

            <!-- 3. BONUS & KLUIZEN (Apart Onderdeel) -->
            <div class="space-y-1">
                <button @click="toggleCategory('bonus')" 
                        type="button" 
                        class="w-full flex items-center justify-between px-3 py-2 text-[10px] font-extrabold uppercase tracking-wider text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 transition">
                    <span class="flex items-center gap-2">
                        <i class="fa-solid fa-gift text-purple-500"></i> Bonus & Kluizen
                    </span>
                    <i class="fa-solid fa-chevron-down text-[9px] transition-transform duration-200" :class="openCategory === 'bonus' ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="openCategory === 'bonus'" x-collapse class="space-y-0.5 pl-1">
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
                </div>
            </div>

            <!-- 4. GEWELD & STRIJD -->
            <div class="space-y-1">
                <button @click="toggleCategory('geweld')" 
                        type="button" 
                        class="w-full flex items-center justify-between px-3 py-2 text-[10px] font-extrabold uppercase tracking-wider text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 transition">
                    <span class="flex items-center gap-2">
                        <i class="fa-solid fa-fire text-slate-400"></i> Geweld & Strijd
                    </span>
                    <i class="fa-solid fa-chevron-down text-[9px] transition-transform duration-200" :class="openCategory === 'geweld' ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="openCategory === 'geweld'" x-collapse class="space-y-0.5 pl-1">
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
                    <a href="{{ route('armwrestling.index') }}" class="flex items-center justify-between px-3 py-2 rounded-lg transition {{ request()->routeIs('armwrestling.*') ? 'nav-item-active bg-indigo-50/90 dark:bg-purple-950/60 text-indigo-700 dark:text-purple-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                        <div class="flex items-center gap-3">
                            <i class="fa-solid fa-hand-back-fist w-4 text-center text-amber-500 dark:text-amber-400"></i>
                            <span>Armpjedruk Arena</span>
                        </div>
                        <span class="bg-amber-100 dark:bg-amber-900/60 text-amber-700 dark:text-amber-300 text-[9px] font-black uppercase px-1.5 py-0.5 rounded tracking-wide">
                            Duel
                        </span>
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

            <!-- 5. ECONOMIE & GOKKEN -->
            <div class="space-y-1">
                <button @click="toggleCategory('economie')" 
                        type="button" 
                        class="w-full flex items-center justify-between px-3 py-2 text-[10px] font-extrabold uppercase tracking-wider text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 transition">
                    <span class="flex items-center gap-2">
                        <i class="fa-solid fa-coins text-slate-400"></i> Economie & Gokken
                    </span>
                    <i class="fa-solid fa-chevron-down text-[9px] transition-transform duration-200" :class="openCategory === 'economie' ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="openCategory === 'economie'" x-collapse class="space-y-0.5 pl-1">
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
                        $userOpenTicketsDesk = Auth::check() ? Auth::user()->supportTickets()->where('status', 'beantwoord')->count() : 0;
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
                    <span class="text-[9px] bg-amber-400 text-slate-950 font-black px-1.5 py-0.5 rounded shadow-2xs">€7,50</span>
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

        <!-- Sidebar Footer / Logout -->
        <div class="p-3 border-t border-slate-100 bg-slate-50/50 mt-auto">
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

    <!-- MOBILE TOP HEADER BAR -->
    <div class="lg:hidden bg-white dark:bg-[#110726] text-slate-800 dark:text-slate-100 px-3.5 py-2.5 flex items-center justify-between border-b border-slate-200 dark:border-purple-900/40 sticky top-0 z-40 shadow-xs">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-2">
            <div class="w-8 h-8 shrink-0 drop-shadow-xs flex items-center justify-center">
                <img src="{{ asset('assets/logo.png') }}?v=3" alt="TBS Kliniek Logo" class="w-full h-full object-contain">
            </div>
            <div class="flex flex-col">
                <span class="text-xs font-black tracking-tight text-slate-900 dark:text-white leading-none">TBS <span class="text-purple-600 dark:text-purple-400">KLINIEK</span></span>
                <a href="{{ route('members.index', ['filter' => 'online']) }}" class="text-[9px] font-bold text-emerald-600 dark:text-emerald-400 flex items-center gap-1 mt-0.5 hover:underline" title="Online patiënten (klik voor online overzicht)">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> 
                    <span x-text="stats.onlineCount + ' Online'">{{ $onlineCount }} Online</span>
                </a>
            </div>
        </a>
        <div class="flex items-center gap-2">
            @php
                $unreadMsgCount = \App\Models\GameMessage::where('recipient_id', $character->id)->where('is_read', false)->count();
            @endphp
            <a href="{{ route('messages.index') }}" class="relative p-2 rounded-xl text-slate-500 dark:text-purple-300 hover:text-purple-600 hover:bg-slate-100 dark:hover:bg-purple-950/60 transition" title="Berichten">
                <i class="fa-solid fa-envelope text-sm"></i>
                @if($unreadMsgCount > 0)
                    <span class="absolute top-1 right-1 w-2 h-2 rounded-full bg-rose-500 ring-2 ring-white"></span>
                @endif
            </a>

            <a href="{{ route('profile.me') }}" class="flex items-center gap-1.5 bg-slate-100 dark:bg-purple-950/60 hover:bg-slate-200 dark:hover:bg-purple-900/60 px-2 py-1 rounded-xl transition border border-transparent dark:border-purple-800/40">
                <img src="{{ $character->avatar_url }}" alt="{{ $character->name }}" class="w-6 h-6 rounded-full object-cover">
                <span class="text-[10px] font-extrabold text-slate-700 dark:text-purple-200 max-w-[70px] truncate leading-normal py-0.5">{{ $character->name }}</span>
            </a>

            @if(Auth::check() && Auth::user()->isStaff())
            <a href="{{ route('admin.dashboard') }}" class="p-2 rounded-xl text-purple-700 dark:text-purple-300 bg-purple-50 dark:bg-purple-950/60 hover:bg-purple-100 dark:hover:bg-purple-900/60 transition border border-transparent dark:border-purple-800/40" title="Medewerkers Admin">
                <i class="fa-solid fa-shield-halved text-sm"></i>
            </a>
            @endif

            <!-- Mobile Dark Mode Animated Switch -->
            <button type="button" onclick="window.tbsToggleTheme()" 
                    class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent bg-slate-300 dark:bg-purple-950/80 transition-colors duration-200 ease-in-out focus:outline-none shadow-inner overflow-hidden" 
                    title="Wissel Thema"
                    aria-label="Wissel Thema">
                <span class="theme-toggle-knob pointer-events-none inline-flex h-5 w-5 transform rounded-full bg-white dark:bg-purple-900 shadow-md ring-0 transition duration-200 ease-in-out translate-x-0 dark:translate-x-5 items-center justify-center overflow-hidden">
                    <span class="theme-toggle-sun items-center justify-center">
                        <i class="fa-solid fa-sun text-amber-500 text-[9px]"></i>
                    </span>
                    <span class="theme-toggle-moon items-center justify-center">
                        <i class="fa-solid fa-moon text-indigo-300 text-[9px]"></i>
                    </span>
                </span>
            </button>

            <button @click="mobileMenuOpen = true" class="text-slate-700 dark:text-purple-200 p-2 focus:outline-none rounded-xl hover:bg-slate-100 dark:hover:bg-purple-950/60 cursor-pointer active:scale-95 transition" aria-label="Open menu">
                <i class="fa-solid fa-bars text-base"></i>
            </button>
        </div>
    </div>

    <!-- MOBILE QUICK STATS STRIP -->
    <div class="lg:hidden bg-white dark:bg-[#110726] border-b border-slate-200/90 dark:border-purple-900/40 px-3 py-1.5 flex items-center gap-2.5 overflow-x-auto text-[11px] font-bold scrollbar-none sticky top-[49px] z-30 shadow-2xs">
        <!-- Unified Money Block -->
        <a href="{{ route('bank.index') }}" class="flex items-center gap-2 bg-slate-50 dark:bg-purple-950/50 border border-slate-200 dark:border-purple-900/40 px-2.5 py-1 rounded-xl shrink-0 text-slate-700 dark:text-purple-200 shadow-2xs" title="Naar De Bank">
            <span class="w-5 h-5 rounded-lg bg-indigo-50 dark:bg-purple-900/50 text-indigo-600 dark:text-purple-300 flex items-center justify-center text-[10px] font-bold"><i class="fa-solid fa-wallet"></i></span>
            <div class="flex flex-col leading-tight">
                <span class="font-extrabold text-slate-900 dark:text-white text-xs" x-text="stats.totalFormatted">€{{ number_format($character->cash + $character->bank, 0, ',', '.') }}</span>
                <span class="text-[9px] text-slate-400 dark:text-purple-300/70 font-medium">
                    <span x-text="stats.cashFormatted">€{{ number_format($character->cash, 0, ',', '.') }}</span> c &middot; <span x-text="stats.bankFormatted">€{{ number_format($character->bank, 0, ',', '.') }}</span> b
                </span>
            </div>
        </a>

        <!-- Energy -->
        <a href="{{ route('shop.index', ['tab' => 'inventaris']) }}" class="flex flex-col gap-0.5 bg-slate-50 dark:bg-purple-950/50 border border-slate-200 dark:border-purple-900/40 px-2.5 py-1 rounded-xl shrink-0 shadow-2xs min-w-[100px]" title="Energie: klik voor Inventaris">
            <div class="flex items-center justify-between text-[9px] leading-none">
                <span class="text-slate-400 dark:text-purple-300/70 font-black flex items-center gap-1 uppercase tracking-wider"><i class="fa-solid fa-bolt text-emerald-500"></i> ENERGIE</span>
                <span class="text-emerald-700 dark:text-emerald-400 font-black font-mono" x-text="stats.energy + '/' + stats.max_energy">{{ $character->energy }}/{{ $character->max_energy }}</span>
            </div>
            <div class="w-full bg-slate-200 dark:bg-purple-900/50 rounded-full h-1.5 overflow-hidden">
                <div class="bg-emerald-500 h-full rounded-full transition-all duration-300" :style="'width: ' + stats.energy_pct + '%'" style="width: {{ min(100, max(0, round(($character->energy / max(1, $character->max_energy)) * 100))) }}%"></div>
            </div>
        </a>

        <!-- Health -->
        <a href="{{ route('shop.index', ['tab' => 'inventaris']) }}" class="flex flex-col gap-0.5 bg-slate-50 dark:bg-purple-950/50 border border-slate-200 dark:border-purple-900/40 px-2.5 py-1 rounded-xl shrink-0 shadow-2xs min-w-[100px]" title="Gezondheid: klik voor Inventaris">
            <div class="flex items-center justify-between text-[9px] leading-none">
                <span class="text-slate-400 dark:text-purple-300/70 font-black flex items-center gap-1 uppercase tracking-wider"><i class="fa-solid fa-heart text-rose-500"></i> GEZONDHEID</span>
                <span class="text-rose-700 dark:text-rose-400 font-black font-mono" x-text="stats.health + '/' + stats.max_health">{{ $character->health }}/{{ $character->max_health }}</span>
            </div>
            <div class="w-full bg-slate-200 dark:bg-purple-900/50 rounded-full h-1.5 overflow-hidden">
                <div class="bg-rose-500 h-full rounded-full transition-all duration-300" :style="'width: ' + stats.health_pct + '%'" style="width: {{ min(100, max(0, round(($character->health / max(1, $character->max_health)) * 100))) }}%"></div>
            </div>
        </a>

        <!-- Respect -->
        <div class="flex items-center gap-1.5 bg-purple-50 dark:bg-purple-950/60 border border-purple-200 dark:border-purple-900/50 px-2 py-1 rounded-xl shrink-0 text-purple-800 dark:text-purple-300 shadow-2xs" title="Totaal Respect">
            <i class="fa-solid fa-fire-flame-curved text-purple-500 text-[10px]"></i>
            <span x-text="stats.respect">{{ $character->formatted_respect }}</span>
        </div>

        <!-- Stars -->
        <a href="{{ route('vip.index') }}" class="flex items-center gap-1.5 bg-amber-50 dark:bg-amber-950/60 border border-amber-200 dark:border-amber-900/50 px-2 py-1 rounded-xl shrink-0 text-amber-800 dark:text-amber-300 shadow-2xs" title="Sterren">
            <i class="fa-solid fa-star text-amber-500 text-[10px]"></i>
            <span x-text="stats.starsFormatted">{{ number_format($character->stars ?? 0, 0, ',', '.') }}</span>
        </a>
    </div>

    <!-- MOBILE FULL SLIDE-OVER NAVIGATION DRAWER -->
    <div x-show="mobileMenuOpen" x-cloak class="lg:hidden fixed inset-0 z-50 flex justify-end" role="dialog" aria-modal="true">
        <div x-show="mobileMenuOpen" 
             x-transition:enter="transition-opacity ease-linear duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-300"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="mobileMenuOpen = false" 
             class="fixed inset-0 bg-slate-950/60 backdrop-blur-xs"></div>

        <div x-show="mobileMenuOpen"
             x-transition:enter="transition ease-in-out duration-300 transform"
             x-transition:enter-start="translate-x-full"
             x-transition:enter-end="translate-x-0"
             x-transition:leave="transition ease-in-out duration-300 transform"
             x-transition:leave-start="translate-x-0"
             x-transition:leave-end="translate-x-full"
             class="relative w-[85vw] max-w-sm bg-white dark:bg-[#0f172a] h-full shadow-2xl flex flex-col z-10 overflow-hidden text-slate-800 dark:text-slate-100">
            
            <div class="p-4 bg-white dark:bg-[#0f172a] text-slate-900 dark:text-white flex items-center justify-between border-b border-slate-200 dark:border-slate-800">
                <div class="flex items-center gap-3">
                    <img src="{{ $character->avatar_url }}" alt="{{ $character->name }}" class="w-10 h-10 rounded-full object-cover border-2 border-purple-200 dark:border-purple-800">
                    <div>
                        <div class="font-extrabold text-sm leading-tight text-slate-900 dark:text-white">{{ $character->name }}</div>
                        <div class="text-[11px] text-purple-700 dark:text-purple-300 font-bold">Level {{ $character->level }} &bull; {{ $character->title ?? 'Patiënt' }}</div>
                    </div>
                </div>
                <button @click="mobileMenuOpen = false" class="p-2 rounded-lg text-slate-400 hover:text-slate-700 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 transition cursor-pointer">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <div class="flex-1 overflow-y-auto p-4 space-y-4 text-xs font-semibold">
                <div class="grid grid-cols-2 gap-2">
                    <a href="{{ route('dashboard') }}" @click="mobileMenuOpen = false" class="flex items-center gap-2 p-2.5 rounded-xl transition {{ request()->routeIs('dashboard') ? 'bg-indigo-50/90 dark:bg-purple-950/60 text-indigo-700 dark:text-purple-300 font-black' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/60 font-bold' }}">
                        <i class="fa-solid fa-gauge text-indigo-600 dark:text-indigo-400"></i> Dashboard
                    </a>
                    <a href="{{ route('messages.index') }}" @click="mobileMenuOpen = false" class="flex items-center justify-between p-2.5 rounded-xl transition {{ request()->routeIs('messages.*') ? 'bg-indigo-50/90 dark:bg-purple-950/60 text-indigo-700 dark:text-purple-300 font-black' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/60 font-bold' }}">
                        <span class="flex items-center gap-2"><i class="fa-solid fa-envelope text-blue-500 dark:text-blue-400"></i> Post</span>
                        @if($unreadMsgCount > 0)<span class="bg-rose-500 text-white text-[10px] font-extrabold px-1.5 py-0.2 rounded-full">{{ $unreadMsgCount }}</span>@endif
                    </a>
                </div>
                <a href="{{ route('vip.index') }}" @click="mobileMenuOpen = false" class="flex items-center justify-between p-2.5 rounded-xl bg-amber-50 dark:bg-amber-950/40 text-amber-800 dark:text-amber-300 font-bold transition hover:bg-amber-100/60 dark:hover:bg-amber-900/50">
                    <span class="flex items-center gap-2"><i class="fa-solid fa-crown text-amber-500"></i> VIP Shop</span>
                    <span class="text-[10px] font-black uppercase tracking-wider text-amber-700 dark:text-amber-300 bg-amber-100/70 dark:bg-amber-900/60 px-2 py-0.5 rounded-full">Exclusief</span>
                </a>

                <!-- 1. Algemeen -->
                <div class="space-y-1">
                    <span class="text-[10px] font-black uppercase tracking-wider text-slate-400 block px-2">Algemeen</span>
                    <a href="{{ route('news.index') }}" @click="mobileMenuOpen = false" class="flex items-center gap-2.5 px-3 py-2 rounded-lg hover:bg-slate-100 dark:hover:bg-purple-950/30 transition text-slate-700 dark:text-slate-300 {{ request()->routeIs('news.*') ? 'bg-indigo-50/90 dark:bg-purple-950/60 text-indigo-700 dark:text-purple-300 font-bold' : '' }}">
                        <i class="fa-solid fa-newspaper w-4 text-purple-600 dark:text-purple-400"></i> Kliniek Nieuws
                    </a>
                    <a href="{{ route('newspaper.index') }}" @click="mobileMenuOpen = false" class="flex items-center justify-between px-3 py-2 rounded-lg hover:bg-slate-100 dark:hover:bg-purple-950/30 transition text-slate-700 dark:text-slate-300 {{ request()->routeIs('newspaper.*') ? 'bg-indigo-50/90 dark:bg-purple-950/60 text-indigo-700 dark:text-purple-300 font-bold' : '' }}">
                        <span class="flex items-center gap-2.5"><i class="fa-solid fa-file-lines w-4 text-indigo-500 dark:text-indigo-400"></i> Het Afdelingsblad</span>
                        <span class="bg-indigo-100 dark:bg-indigo-900/60 text-indigo-700 dark:text-indigo-300 text-[9px] font-black uppercase px-1.5 py-0.5 rounded tracking-wide">Editie</span>
                    </a>
                    <a href="{{ route('radio.index') }}" @click="mobileMenuOpen = false" class="flex items-center justify-between px-3 py-2 rounded-lg hover:bg-slate-100 dark:hover:bg-purple-950/30 transition text-slate-700 dark:text-slate-300 {{ request()->routeIs('radio.*') ? 'bg-indigo-50/90 dark:bg-purple-950/60 text-indigo-700 dark:text-purple-300 font-bold' : '' }}">
                        <span class="flex items-center gap-2.5"><i class="fa-solid fa-radio w-4 text-purple-500 dark:text-purple-400"></i> TBS FM Radio</span>
                        <span class="bg-rose-100 dark:bg-rose-900/60 text-rose-700 dark:text-rose-300 text-[9px] font-black uppercase px-1.5 py-0.5 rounded tracking-wide animate-pulse">Live</span>
                    </a>
                    <a href="{{ route('timeline.index') }}" @click="mobileMenuOpen = false" class="flex items-center gap-2.5 px-3 py-2 rounded-lg hover:bg-slate-100 dark:hover:bg-purple-950/30 transition text-slate-700 dark:text-slate-300 {{ request()->routeIs('timeline.*') ? 'bg-indigo-50/90 dark:bg-purple-950/60 text-indigo-700 dark:text-purple-300 font-bold' : '' }}">
                        <i class="fa-solid fa-bullhorn w-4 text-sky-500 dark:text-sky-400"></i> Tijdlijn (Feed)
                    </a>
                    <a href="{{ route('friends.index') }}" @click="mobileMenuOpen = false" class="flex items-center gap-2.5 px-3 py-2 rounded-lg hover:bg-slate-100 dark:hover:bg-purple-950/30 transition text-slate-700 dark:text-slate-300 {{ request()->routeIs('friends.*') ? 'bg-indigo-50/90 dark:bg-purple-950/60 text-indigo-700 dark:text-purple-300 font-bold' : '' }}">
                        <i class="fa-solid fa-user-group w-4 text-pink-500 dark:text-pink-400"></i> Vrienden & BFF
                    </a>
                    <a href="{{ route('members.index') }}" @click="mobileMenuOpen = false" class="flex items-center justify-between px-3 py-2 rounded-lg hover:bg-slate-100 dark:hover:bg-purple-950/30 transition text-slate-700 dark:text-slate-300 {{ request()->routeIs('members.*') ? 'bg-indigo-50/90 dark:bg-purple-950/60 text-indigo-700 dark:text-purple-300 font-bold' : '' }}">
                        <span class="flex items-center gap-2.5"><i class="fa-solid fa-users w-4 text-emerald-500 dark:text-emerald-400"></i> Leden & Online</span>
                        <span class="inline-flex items-center gap-1 text-[9px] font-black uppercase text-emerald-700 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-950/60 px-1.5 py-0.5 rounded-full border border-emerald-200 dark:border-emerald-800">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            <span x-text="stats.onlineCount || {{ $onlineCount }}">{{ $onlineCount }}</span>
                        </span>
                    </a>
                    <a href="{{ route('leaderboard.index') }}" @click="mobileMenuOpen = false" class="flex items-center gap-2.5 px-3 py-2 rounded-lg hover:bg-slate-100 dark:hover:bg-purple-950/30 transition text-slate-700 dark:text-slate-300 {{ request()->routeIs('leaderboard.*') ? 'bg-indigo-50/90 dark:bg-purple-950/60 text-indigo-700 dark:text-purple-300 font-bold' : '' }}">
                        <i class="fa-solid fa-trophy w-4 text-amber-500 dark:text-amber-400"></i> Ranglijsten
                    </a>
                    <a href="{{ route('battle.index') }}" @click="mobileMenuOpen = false" class="flex items-center justify-between px-3 py-2 rounded-lg hover:bg-slate-100 dark:hover:bg-purple-950/30 transition text-slate-700 dark:text-slate-300 {{ request()->routeIs('battle.*') ? 'bg-indigo-50/90 dark:bg-purple-950/60 text-indigo-700 dark:text-purple-300 font-bold' : '' }}">
                        <span class="flex items-center gap-2.5"><i class="fa-solid fa-bolt w-4 text-purple-600 dark:text-purple-400"></i> 24 Uur Battle</span>
                        <span class="bg-purple-100 dark:bg-purple-900/60 text-purple-700 dark:text-purple-300 text-[9px] font-black uppercase px-1.5 py-0.5 rounded tracking-wide">Live</span>
                    </a>
                    <a href="{{ route('prison') }}" @click="mobileMenuOpen = false" class="flex items-center gap-2.5 px-3 py-2 rounded-lg hover:bg-slate-100 dark:hover:bg-purple-950/30 transition text-slate-700 dark:text-slate-300 {{ request()->routeIs('prison') ? 'bg-indigo-50/90 dark:bg-purple-950/60 text-indigo-700 dark:text-purple-300 font-bold' : '' }}">
                        <i class="fa-solid fa-dungeon w-4 text-rose-500 dark:text-rose-400"></i> Isoleercel
                    </a>
                    <a href="{{ route('gangs.index') }}" @click="mobileMenuOpen = false" class="flex items-center gap-2.5 px-3 py-2 rounded-lg hover:bg-slate-100 dark:hover:bg-purple-950/30 transition text-slate-700 dark:text-slate-300 {{ request()->routeIs('gangs.*') ? 'bg-indigo-50/90 dark:bg-purple-950/60 text-indigo-700 dark:text-purple-300 font-bold' : '' }}">
                        <i class="fa-solid fa-users w-4 text-purple-500 dark:text-purple-400"></i> Bendes & Gangs
                    </a>
                </div>

                <!-- 2. Voortgang -->
                <div class="space-y-1">
                    <span class="text-[10px] font-black uppercase tracking-wider text-slate-400 block px-2">Voortgang</span>
                    <a href="{{ route('missions.index') }}" @click="mobileMenuOpen = false" class="flex items-center gap-2.5 px-3 py-2 rounded-lg hover:bg-slate-100 dark:hover:bg-purple-950/30 transition text-slate-700 dark:text-slate-300 {{ request()->routeIs('missions.*') ? 'bg-indigo-50/90 dark:bg-purple-950/60 text-indigo-700 dark:text-purple-300 font-bold' : '' }}">
                        <i class="fa-solid fa-list-check w-4 text-indigo-500 dark:text-indigo-400"></i> Missies & Opdrachten
                    </a>
                    <a href="{{ route('training.index') }}" @click="mobileMenuOpen = false" class="flex items-center gap-2.5 px-3 py-2 rounded-lg hover:bg-slate-100 dark:hover:bg-purple-950/30 transition text-slate-700 dark:text-slate-300 {{ request()->routeIs('training.*') ? 'bg-indigo-50/90 dark:bg-purple-950/60 text-indigo-700 dark:text-purple-300 font-bold' : '' }}">
                        <i class="fa-solid fa-dumbbell w-4 text-purple-500 dark:text-purple-400"></i> Krachttraining
                    </a>
                    <a href="{{ route('treatment.index') }}" @click="mobileMenuOpen = false" class="flex items-center gap-2.5 px-3 py-2 rounded-lg hover:bg-slate-100 dark:hover:bg-purple-950/30 transition text-slate-700 dark:text-slate-300 {{ request()->routeIs('treatment.*') ? 'bg-indigo-50/90 dark:bg-purple-950/60 text-indigo-700 dark:text-purple-300 font-bold' : '' }}">
                        <i class="fa-solid fa-brain w-4 text-rose-500 dark:text-rose-400"></i> Behandelaarsgesprek
                    </a>
                    <a href="{{ route('infirmary.index') }}" @click="mobileMenuOpen = false" class="flex items-center justify-between px-3 py-2 rounded-lg hover:bg-slate-100 dark:hover:bg-purple-950/30 transition text-slate-700 dark:text-slate-300 {{ request()->routeIs('infirmary.*') ? 'bg-indigo-50/90 dark:bg-purple-950/60 text-indigo-700 dark:text-purple-300 font-bold' : '' }}">
                        <span class="flex items-center gap-2.5"><i class="fa-solid fa-hospital-user w-4 text-teal-500 dark:text-teal-400"></i> Ziekenboeg & Pillen</span>
                        <span class="bg-teal-100 dark:bg-teal-900/60 text-teal-700 dark:text-teal-300 text-[9px] font-black uppercase px-1.5 py-0.5 rounded tracking-wide">Heal</span>
                    </a>
                    <a href="{{ route('daycare.index') }}" @click="mobileMenuOpen = false" class="flex items-center justify-between px-3 py-2 rounded-lg hover:bg-slate-100 dark:hover:bg-purple-950/30 transition text-slate-700 dark:text-slate-300 {{ request()->routeIs('daycare.*') ? 'bg-indigo-50/90 dark:bg-purple-950/60 text-indigo-700 dark:text-purple-300 font-bold' : '' }}">
                        <span class="flex items-center gap-2.5"><i class="fa-solid fa-person-digging w-4 text-amber-500 dark:text-amber-400"></i> Dagbesteding</span>
                        <span class="bg-amber-100 dark:bg-amber-900/60 text-amber-700 dark:text-amber-300 text-[9px] font-black uppercase px-1.5 py-0.5 rounded tracking-wide">Veilig</span>
                    </a>
                </div>

                <!-- 3. Illegale Acties -->
                <div class="space-y-1">
                    <span class="text-[10px] font-black uppercase tracking-wider text-slate-400 block px-2">Illegale Acties</span>
                    <a href="{{ route('hustle.index') }}" @click="mobileMenuOpen = false" class="flex items-center gap-2.5 px-3 py-2 rounded-lg hover:bg-slate-100 dark:hover:bg-purple-950/30 transition text-slate-700 dark:text-slate-300 {{ request()->routeIs('hustle.*') ? 'bg-indigo-50/90 dark:bg-purple-950/60 text-indigo-700 dark:text-purple-300 font-bold' : '' }}">
                        <i class="fa-solid fa-hand-holding-dollar w-4 text-emerald-500 dark:text-emerald-400"></i> Hosselen
                    </a>
                    <a href="{{ route('smuggle.index') }}" @click="mobileMenuOpen = false" class="flex items-center gap-2.5 px-3 py-2 rounded-lg hover:bg-slate-100 dark:hover:bg-purple-950/30 transition text-slate-700 dark:text-slate-300 {{ request()->routeIs('smuggle.*') ? 'bg-indigo-50/90 dark:bg-purple-950/60 text-indigo-700 dark:text-purple-300 font-bold' : '' }}">
                        <i class="fa-solid fa-capsules w-4 text-teal-500 dark:text-teal-400"></i> Dealen & Smokkel
                    </a>
                    <a href="{{ route('extort.index') }}" @click="mobileMenuOpen = false" class="flex items-center gap-2.5 px-3 py-2 rounded-lg hover:bg-slate-100 dark:hover:bg-purple-950/30 transition text-slate-700 dark:text-slate-300 {{ request()->routeIs('extort.*') ? 'bg-indigo-50/90 dark:bg-purple-950/60 text-indigo-700 dark:text-purple-300 font-bold' : '' }}">
                        <i class="fa-solid fa-person-military-pointing w-4 text-orange-500 dark:text-orange-400"></i> Afpersen
                    </a>
                    <a href="{{ route('lockers.index') }}" @click="mobileMenuOpen = false" class="flex items-center gap-2.5 px-3 py-2 rounded-lg hover:bg-slate-100 dark:hover:bg-purple-950/30 transition text-slate-700 dark:text-slate-300 {{ request()->routeIs('lockers.*') ? 'bg-indigo-50/90 dark:bg-purple-950/60 text-indigo-700 dark:text-purple-300 font-bold' : '' }}">
                        <i class="fa-solid fa-id-card w-4 text-blue-500 dark:text-blue-400"></i> Kluisjes Kraken
                    </a>
                    <a href="{{ route('droppings.index') }}" @click="mobileMenuOpen = false" class="flex items-center justify-between px-3 py-2 rounded-lg hover:bg-slate-100 dark:hover:bg-purple-950/30 transition text-slate-700 dark:text-slate-300 {{ request()->routeIs('droppings.*') ? 'bg-indigo-50/90 dark:bg-purple-950/60 text-indigo-700 dark:text-purple-300 font-bold' : '' }}">
                        <span class="flex items-center gap-2.5"><i class="fa-solid fa-plane-arrival w-4 text-emerald-500 dark:text-emerald-400"></i> Tennisbal & Drones</span>
                        <span class="bg-emerald-100 dark:bg-emerald-900/60 text-emerald-700 dark:text-emerald-300 text-[9px] font-black uppercase px-1.5 py-0.5 rounded tracking-wide">Drop</span>
                    </a>
                    <a href="{{ route('tunnel.index') }}" @click="mobileMenuOpen = false" class="flex items-center justify-between px-3 py-2 rounded-lg hover:bg-slate-100 dark:hover:bg-purple-950/30 transition text-slate-700 dark:text-slate-300 {{ request()->routeIs('tunnel.*') ? 'bg-indigo-50/90 dark:bg-purple-950/60 text-indigo-700 dark:text-purple-300 font-bold' : '' }}">
                        <span class="flex items-center gap-2.5"><i class="fa-solid fa-person-digging w-4 text-amber-500 dark:text-amber-400"></i> Uitbraaktunnel</span>
                        <span class="bg-amber-100 dark:bg-amber-900/60 text-amber-700 dark:text-amber-300 text-[9px] font-black uppercase px-1.5 py-0.5 rounded tracking-wide">Tunnel</span>
                    </a>
                </div>

                <!-- 4. Bonus & Kluizen -->
                <div class="space-y-1">
                    <span class="text-[10px] font-black uppercase tracking-wider text-slate-400 block px-2">Bonus & Kluizen</span>
                    <a href="{{ route('daily.index') }}" @click="mobileMenuOpen = false" class="flex items-center justify-between px-3 py-2 rounded-lg hover:bg-slate-100 dark:hover:bg-purple-950/30 transition text-slate-700 dark:text-slate-300 {{ request()->routeIs('daily.*') ? 'bg-indigo-50/90 dark:bg-purple-950/60 text-indigo-700 dark:text-purple-300 font-bold' : '' }}">
                        <div class="flex items-center gap-2.5">
                            <i class="fa-solid fa-dharmachakra w-4 text-amber-500 dark:text-amber-400"></i> Rad van Fortuin
                        </div>
                        @if($character->canDailySpin())
                        <span class="bg-amber-100 dark:bg-amber-900/60 text-amber-700 dark:text-amber-300 text-[9px] font-black uppercase px-1.5 py-0.5 rounded tracking-wide animate-pulse">
                            Gratis
                        </span>
                        @endif
                    </a>
                    <a href="{{ route('vault.index') }}" @click="mobileMenuOpen = false" class="flex items-center gap-2.5 px-3 py-2 rounded-lg hover:bg-slate-100 dark:hover:bg-purple-950/30 transition text-slate-700 dark:text-slate-300 {{ request()->routeIs('vault.*') ? 'bg-indigo-50/90 dark:bg-purple-950/60 text-indigo-700 dark:text-purple-300 font-bold' : '' }}">
                        <i class="fa-solid fa-vault w-4 text-purple-500 dark:text-purple-400"></i> MedicijnKluis (Kraak)
                    </a>
                    <a href="{{ route('packages.index') }}" @click="mobileMenuOpen = false" class="flex items-center gap-2.5 px-3 py-2 rounded-lg hover:bg-slate-100 dark:hover:bg-purple-950/30 transition text-slate-700 dark:text-slate-300 {{ request()->routeIs('packages.*') ? 'bg-indigo-50/90 dark:bg-purple-950/60 text-indigo-700 dark:text-purple-300 font-bold' : '' }}">
                        <i class="fa-solid fa-box-open w-4 text-amber-500 dark:text-amber-400"></i> Koffers & Pakketjes
                    </a>
                    <a href="{{ route('pickpocket.index') }}" @click="mobileMenuOpen = false" class="flex items-center gap-2.5 px-3 py-2 rounded-lg hover:bg-slate-100 dark:hover:bg-purple-950/30 transition text-slate-700 dark:text-slate-300 {{ request()->routeIs('pickpocket.*') ? 'bg-indigo-50/90 dark:bg-purple-950/60 text-indigo-700 dark:text-purple-300 font-bold' : '' }}">
                        <i class="fa-solid fa-mask w-4 text-indigo-500 dark:text-indigo-400"></i> Zakkenrollen
                    </a>
                </div>

                <!-- 5. Geweld & Strijd -->
                <div class="space-y-1">
                    <span class="text-[10px] font-black uppercase tracking-wider text-slate-400 block px-2">Geweld & Strijd</span>
                    <a href="{{ route('fight.index') }}" @click="mobileMenuOpen = false" class="flex items-center gap-2.5 px-3 py-2 rounded-lg hover:bg-slate-100 dark:hover:bg-purple-950/30 transition text-slate-700 dark:text-slate-300 {{ request()->routeIs('fight.*') ? 'bg-indigo-50/90 dark:bg-purple-950/60 text-indigo-700 dark:text-purple-300 font-bold' : '' }}">
                        <i class="fa-solid fa-skull w-4 text-rose-500 dark:text-rose-400"></i> Respectlijst
                    </a>
                    <a href="{{ route('theft.index') }}" @click="mobileMenuOpen = false" class="flex items-center gap-2.5 px-3 py-2 rounded-lg hover:bg-slate-100 dark:hover:bg-purple-950/30 transition text-slate-700 dark:text-slate-300 {{ request()->routeIs('theft.*') ? 'bg-indigo-50/90 dark:bg-purple-950/60 text-indigo-700 dark:text-purple-300 font-bold' : '' }}">
                        <i class="fa-solid fa-briefcase w-4 text-slate-500 dark:text-slate-400"></i> Klusjes & Diefstal
                    </a>
                    <a href="{{ route('combat_training.index') }}" @click="mobileMenuOpen = false" class="flex items-center gap-2.5 px-3 py-2 rounded-lg hover:bg-slate-100 dark:hover:bg-purple-950/30 transition text-slate-700 dark:text-slate-300 {{ request()->routeIs('combat_training.*') ? 'bg-indigo-50/90 dark:bg-purple-950/60 text-indigo-700 dark:text-purple-300 font-bold' : '' }}">
                        <i class="fa-solid fa-bullseye w-4 text-indigo-500 dark:text-indigo-400"></i> Vechtoefening
                    </a>
                    <a href="{{ route('armwrestling.index') }}" @click="mobileMenuOpen = false" class="flex items-center justify-between px-3 py-2 rounded-lg hover:bg-slate-100 dark:hover:bg-purple-950/30 transition text-slate-700 dark:text-slate-300 {{ request()->routeIs('armwrestling.*') ? 'bg-indigo-50/90 dark:bg-purple-950/60 text-indigo-700 dark:text-purple-300 font-bold' : '' }}">
                        <span class="flex items-center gap-2.5"><i class="fa-solid fa-hand-back-fist w-4 text-amber-500 dark:text-amber-400"></i> Armpjedruk Arena</span>
                        <span class="bg-amber-100 dark:bg-amber-900/60 text-amber-700 dark:text-amber-300 text-[9px] font-black uppercase px-1.5 py-0.5 rounded tracking-wide">Duel</span>
                    </a>
                    <a href="{{ route('hitman.index') }}" @click="mobileMenuOpen = false" class="flex items-center gap-2.5 px-3 py-2 rounded-lg hover:bg-slate-100 dark:hover:bg-purple-950/30 transition text-slate-700 dark:text-slate-300 {{ request()->routeIs('hitman.*') ? 'bg-indigo-50/90 dark:bg-purple-950/60 text-indigo-700 dark:text-purple-300 font-bold' : '' }}">
                        <i class="fa-solid fa-crosshairs w-4 text-rose-600 dark:text-rose-400"></i> Liquidatie
                    </a>
                    <a href="{{ route('bounties.index') }}" @click="mobileMenuOpen = false" class="flex items-center gap-2.5 px-3 py-2 rounded-lg hover:bg-slate-100 dark:hover:bg-purple-950/30 transition text-slate-700 dark:text-slate-300 {{ request()->routeIs('bounties.*') ? 'bg-indigo-50/90 dark:bg-purple-950/60 text-indigo-700 dark:text-purple-300 font-bold' : '' }}">
                        <i class="fa-solid fa-skull-crossbones w-4 text-rose-500 dark:text-rose-400"></i> Kopgeld & Premies
                    </a>
                    <a href="{{ route('informants.index') }}" @click="mobileMenuOpen = false" class="flex items-center gap-2.5 px-3 py-2 rounded-lg hover:bg-slate-100 dark:hover:bg-purple-950/30 transition text-slate-700 dark:text-slate-300 {{ request()->routeIs('informants.*') ? 'bg-indigo-50/90 dark:bg-purple-950/60 text-indigo-700 dark:text-purple-300 font-bold' : '' }}">
                        <i class="fa-solid fa-user-secret w-4 text-slate-500 dark:text-slate-400"></i> Informanten
                    </a>
                    <a href="{{ route('isolation.index') }}" @click="mobileMenuOpen = false" class="flex items-center gap-2.5 px-3 py-2 rounded-lg hover:bg-slate-100 dark:hover:bg-purple-950/30 transition text-slate-700 dark:text-slate-300 {{ request()->routeIs('isolation.*') ? 'bg-indigo-50/90 dark:bg-purple-950/60 text-indigo-700 dark:text-purple-300 font-bold' : '' }}">
                        <i class="fa-solid fa-ghost w-4 text-purple-500 dark:text-purple-400"></i> Vrijwillige Isolatie
                    </a>
                </div>

                <!-- 6. Economie & Gokken -->
                <div class="space-y-1">
                    <span class="text-[10px] font-black uppercase tracking-wider text-slate-400 block px-2">Economie & Gokken</span>
                    <a href="{{ route('bank.index') }}" @click="mobileMenuOpen = false" class="flex items-center gap-2.5 px-3 py-2 rounded-lg hover:bg-slate-100 dark:hover:bg-purple-950/30 transition text-slate-700 dark:text-slate-300 {{ request()->routeIs('bank.*') ? 'bg-indigo-50/90 dark:bg-purple-950/60 text-indigo-700 dark:text-purple-300 font-bold' : '' }}">
                        <i class="fa-solid fa-building-columns w-4 text-emerald-500 dark:text-emerald-400"></i> De Bank
                    </a>
                    <a href="{{ route('travel.index') }}" @click="mobileMenuOpen = false" class="flex items-center gap-2.5 px-3 py-2 rounded-lg hover:bg-slate-100 dark:hover:bg-purple-950/30 transition text-slate-700 dark:text-slate-300 {{ request()->routeIs('travel.*') ? 'bg-indigo-50/90 dark:bg-purple-950/60 text-indigo-700 dark:text-purple-300 font-bold' : '' }}">
                        <i class="fa-solid fa-signs-post w-4 text-teal-500 dark:text-teal-400"></i> Verplaatsen
                    </a>
                    <a href="{{ route('casino.index') }}" @click="mobileMenuOpen = false" class="flex items-center gap-2.5 px-3 py-2 rounded-lg hover:bg-slate-100 dark:hover:bg-purple-950/30 transition text-slate-700 dark:text-slate-300 {{ request()->routeIs('casino.*', 'rps.*', 'highlow.*', 'numbergame.*', 'roulette.*', 'slots.*') ? 'bg-indigo-50/90 dark:bg-purple-950/60 text-indigo-700 dark:text-purple-300 font-bold' : '' }}">
                        <i class="fa-solid fa-dice w-4 text-rose-500 dark:text-rose-400"></i> Casino & Gokhal
                    </a>
                    <a href="{{ route('lottery.index') }}" @click="mobileMenuOpen = false" class="flex items-center justify-between px-3 py-2 rounded-lg hover:bg-slate-100 dark:hover:bg-purple-950/30 transition text-slate-700 dark:text-slate-300 {{ request()->routeIs('lottery.*') ? 'bg-amber-50/90 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 font-bold' : '' }}">
                        <span class="flex items-center gap-2.5">
                            <i class="fa-solid fa-crown w-4 text-amber-500 dark:text-amber-400"></i> Afdelingsloterij
                        </span>
                        <span class="bg-amber-400 text-slate-950 font-black text-[9px] px-1.5 py-0.5 rounded uppercase tracking-wider">Pot</span>
                    </a>
                    <a href="{{ route('shop.index') }}" @click="mobileMenuOpen = false" class="flex items-center gap-2.5 px-3 py-2 rounded-lg hover:bg-slate-100 dark:hover:bg-purple-950/30 transition text-slate-700 dark:text-slate-300 {{ request()->routeIs('shop.*') ? 'bg-indigo-50/90 dark:bg-purple-950/60 text-indigo-700 dark:text-purple-300 font-bold' : '' }}">
                        <i class="fa-solid fa-cart-shopping w-4 text-amber-500 dark:text-amber-400"></i> Kliniek Winkel
                    </a>
                    <a href="{{ route('marketplace.index') }}" @click="mobileMenuOpen = false" class="flex items-center gap-2.5 px-3 py-2 rounded-lg hover:bg-slate-100 dark:hover:bg-purple-950/30 transition text-slate-700 dark:text-slate-300 {{ request()->routeIs('marketplace.*') ? 'bg-indigo-50/90 dark:bg-purple-950/60 text-indigo-700 dark:text-purple-300 font-bold' : '' }}">
                        <i class="fa-solid fa-shop w-4 text-purple-600 dark:text-purple-400"></i> Marktplaats
                    </a>
                </div>

                <!-- Helptool, Profiel & Account (Direct boven Veilig Uitloggen) -->
                <div class="space-y-1 pt-2 border-t border-slate-100 dark:border-slate-800/60">
                    <a href="{{ route('faq.index') }}" @click="mobileMenuOpen = false" class="flex items-center gap-2.5 px-3 py-2 rounded-lg hover:bg-slate-100 dark:hover:bg-purple-950/30 transition text-slate-700 dark:text-slate-300 {{ request()->routeIs('faq.*') ? 'bg-indigo-50/90 dark:bg-purple-950/60 text-indigo-700 dark:text-purple-300 font-bold' : '' }}">
                        <i class="fa-solid fa-headset w-4 text-indigo-500 dark:text-indigo-400"></i> Helptool & FAQ
                    </a>
                    <a href="{{ route('help.tickets.index') }}" @click="mobileMenuOpen = false" class="flex items-center justify-between px-3 py-2 rounded-lg hover:bg-slate-100 dark:hover:bg-purple-950/30 transition text-slate-700 dark:text-slate-300 {{ request()->routeIs('help.tickets.*') ? 'bg-indigo-50/90 dark:bg-purple-950/60 text-indigo-700 dark:text-purple-300 font-bold' : '' }}">
                        <span class="flex items-center gap-2.5"><i class="fa-solid fa-ticket w-4 text-purple-500 dark:text-purple-400"></i> Support Tickets</span>
                        @php
                            $userOpenTicketsMob = Auth::check() ? Auth::user()->supportTickets()->where('status', 'beantwoord')->count() : 0;
                        @endphp
                        @if($userOpenTicketsMob > 0)
                        <span class="text-[9px] bg-emerald-500 text-white font-black px-1.5 py-0.5 rounded uppercase">Reactie!</span>
                        @endif
                    </a>
                    <a href="{{ route('help.tickets.create') }}" @click="mobileMenuOpen = false" class="flex items-center gap-2.5 px-3 py-2 rounded-lg hover:bg-slate-100 dark:hover:bg-purple-950/30 transition text-slate-700 dark:text-slate-300 {{ request()->routeIs('help.tickets.create') ? 'bg-indigo-50/90 dark:bg-purple-950/60 text-indigo-700 dark:text-purple-300 font-bold' : '' }}">
                        <i class="fa-solid fa-plus w-4 text-emerald-500 dark:text-emerald-400"></i> Nieuw Ticket Aanmaken
                    </a>
                    <a href="{{ route('profile.me') }}" @click="mobileMenuOpen = false" class="flex items-center gap-2.5 px-3 py-2 rounded-lg hover:bg-slate-100 dark:hover:bg-purple-950/30 transition text-slate-700 dark:text-slate-300 {{ request()->routeIs('profile.*') ? 'bg-indigo-50/90 dark:bg-purple-950/60 text-indigo-700 dark:text-purple-300 font-bold' : '' }}">
                        <i class="fa-solid fa-id-badge w-4 text-blue-500 dark:text-blue-400"></i> Mijn Profiel
                    </a>
                    <a href="{{ route('vip.index') }}" @click="mobileMenuOpen = false" class="flex items-center justify-between px-3 py-2 rounded-lg hover:bg-slate-100 dark:hover:bg-purple-950/30 transition text-slate-700 dark:text-slate-300 {{ request()->routeIs('vip.*') ? 'bg-amber-50/90 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 font-bold' : '' }}">
                        <span class="flex items-center gap-2.5"><i class="fa-solid fa-crown w-4 text-amber-500 dark:text-amber-400"></i> VIP Shop & Punten</span>
                        <span class="text-[9px] bg-amber-400 text-slate-950 font-black px-1.5 py-0.5 rounded shadow-2xs">€7,50</span>
                    </a>
                    <a href="{{ route('sponsor.index') }}" @click="mobileMenuOpen = false" class="flex items-center justify-between px-3 py-2 rounded-lg hover:bg-slate-100 dark:hover:bg-purple-950/30 transition text-purple-700 dark:text-purple-300 {{ request()->routeIs('sponsor.*') ? 'bg-purple-50/90 dark:bg-purple-950/60 text-purple-800 dark:text-purple-300 font-bold' : '' }}">
                        <span class="flex items-center gap-2.5"><i class="fa-solid fa-tv w-4 text-purple-600 dark:text-purple-400"></i> Sponsorkamer</span>
                        <span class="text-[9px] bg-purple-600 text-white font-black px-1.5 py-0.5 rounded uppercase">Gratis VIP</span>
                    </a>
                    <a href="{{ route('settings.account') }}" @click="mobileMenuOpen = false" class="flex items-center gap-2.5 px-3 py-2 rounded-lg hover:bg-slate-100 dark:hover:bg-purple-950/30 transition text-slate-700 dark:text-slate-300 {{ request()->routeIs('settings.*') ? 'bg-indigo-50/90 dark:bg-purple-950/60 text-indigo-700 dark:text-purple-300 font-bold' : '' }}">
                        <i class="fa-solid fa-gear w-4 text-slate-500 dark:text-slate-400"></i> Account Instellingen
                    </a>
                    @if(Auth::check() && Auth::user()->isStaff())
                    <a href="{{ route('admin.dashboard') }}" @click="mobileMenuOpen = false" class="flex items-center justify-between px-3 py-2 rounded-lg bg-purple-100/90 dark:bg-purple-950/70 text-purple-900 dark:text-purple-300 font-bold">
                        <span class="flex items-center gap-2.5"><i class="fa-solid fa-shield-halved w-4 text-purple-600 dark:text-purple-400"></i> Medewerkers Admin</span>
                        <span class="text-[9px] bg-purple-600 text-white font-black px-1.5 py-0.5 rounded uppercase">Staff</span>
                    </a>
                    @endif
                </div>

                <div class="pt-2 border-t border-slate-100 dark:border-slate-800/60">
                    <form method="POST" action="{{ route('logout') }}"
                          data-confirm="Weet je zeker dat je wilt uitloggen uit de TBS Kliniek?"
                          data-confirm-title="Veilig Uitloggen"
                          data-confirm-btn="Ja, Uitloggen"
                          data-confirm-type="logout"
                          data-confirm-icon="fa-right-from-bracket">
                        @csrf
                        <button type="submit" class="w-full flex items-center justify-center gap-2 py-2.5 px-3 rounded-xl font-bold text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/30 border border-transparent transition cursor-pointer">
                            <i class="fa-solid fa-right-from-bracket"></i>
                            <span>Veilig Uitloggen</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- MAIN APP WRAPPER -->
    <div class="flex-1 flex flex-col min-w-0">
        @if(isset($character))
        <!-- TOP MODERN WHITE HEADER BAR (DESKTOP) - Styled after Reference Design in Site Colors -->
        <header class="hidden lg:block bg-white dark:bg-[#110726] border-b border-slate-200/90 dark:border-purple-900/40 sticky top-0 z-30 shadow-xs">
            <div class="w-full px-4 sm:px-6 lg:px-8 xl:px-10 h-20 flex items-center justify-between gap-4">
                
                <!-- 1. LEFT: Player Profile Summary (Avatar with ring, Name, LVL Badge, XP Bar & Handle) -->
                <div class="flex items-center gap-3.5 shrink-0">
                    <a href="{{ route('profile.me') }}" class="relative group shrink-0" title="Bekijk mijn profiel">
                        <div class="w-12 h-12 rounded-full p-0.5 ring-2 ring-indigo-500/30 group-hover:ring-indigo-500 transition-all bg-white dark:bg-purple-950/60 shadow-2xs">
                            <img src="{{ $character->avatar_url }}" alt="{{ $character->name }}" class="w-full h-full rounded-full object-cover bg-slate-100">
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
                <div class="h-10 w-px bg-slate-200/90 dark:bg-purple-900/40 shrink-0"></div>

                <!-- 2. CENTER-LEFT: Clean Energy & Health Dual Filled Pill Bars -->
                <div class="flex flex-col justify-center gap-2 w-44 sm:w-48 shrink-0">
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
                            <div class="bg-emerald-500 h-full rounded-full transition-all duration-300" :style="'width: ' + stats.energy_pct + '%'" style="width: {{ min(100, max(0, round(($character->energy / max(1, $character->max_energy)) * 100))) }}%"></div>
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
                            <div class="bg-rose-500 h-full rounded-full transition-all duration-300" :style="'width: ' + stats.health_pct + '%'" style="width: {{ min(100, max(0, round(($character->health / max(1, $character->max_health)) * 100))) }}%"></div>
                        </div>
                    </a>
                </div>

                <!-- VERTICAL DIVIDER -->
                <div class="h-10 w-px bg-slate-200/90 dark:bg-purple-900/40 shrink-0"></div>

                <!-- 3. CENTER-RIGHT: Financial Overview (Wallet Icon, Total + Cash & Bank Subtitle) -->
                <a href="{{ route('bank.index') }}" class="flex items-center gap-3 group cursor-pointer shrink-0 hover:opacity-90 transition select-none" title="Totaal Vermogen &bull; Klik voor De Bank">
                    <div class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-purple-950/60 border border-slate-200/90 dark:border-purple-900/40 text-slate-600 dark:text-purple-300 group-hover:bg-indigo-600 dark:group-hover:bg-purple-600 group-hover:text-white group-hover:border-indigo-600 transition flex items-center justify-center text-lg shadow-2xs">
                        <i class="fa-solid fa-wallet"></i>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-base sm:text-lg font-black text-slate-900 dark:text-white tracking-tight leading-none group-hover:text-indigo-600 dark:group-hover:text-purple-400 transition" x-text="stats.totalFormatted">
                            €{{ number_format($character->cash + $character->bank, 0, ',', '.') }}
                        </span>
                        <span class="text-[10px] font-medium text-slate-400 dark:text-purple-300/70 mt-1 leading-none">
                            <span x-text="stats.cashFormatted">€{{ number_format($character->cash, 0, ',', '.') }}</span> contant &middot; <span x-text="stats.bankFormatted">€{{ number_format($character->bank, 0, ',', '.') }}</span> bank
                        </span>
                    </div>
                </a>

                <!-- VERTICAL DIVIDER -->
                <div class="h-10 w-px bg-slate-200/90 dark:bg-purple-900/40 shrink-0"></div>

                <!-- 4. RIGHT: VIP, STARS & RESPECT -->
                <div class="flex items-center gap-4 shrink-0">
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
                    <div class="flex flex-col items-center gap-1 cursor-default select-none" title="Respect">
                        <span class="text-[8px] font-black uppercase tracking-wider text-indigo-600 dark:text-indigo-300 bg-indigo-50 dark:bg-indigo-950/60 px-1.5 py-0.5 rounded shadow-2xs border border-transparent dark:border-indigo-800/40">
                            RESPECT
                        </span>
                        <div class="flex items-center gap-1 text-slate-900 dark:text-white font-black text-xs sm:text-sm leading-none">
                            <i class="fa-solid fa-fire text-amber-500 text-xs"></i>
                            <span x-text="stats.respect">{{ $character->formatted_respect }}</span>
                        </div>
                    </div>
                </div>

                <!-- VERTICAL DIVIDER -->
                <div class="h-10 w-px bg-slate-200/90 dark:bg-purple-900/40 shrink-0"></div>

                <!-- 5. FAR RIGHT: Online status, Admin button & Profile Icon -->
                <div class="flex items-center gap-2.5 shrink-0">
                    <!-- Online Indicator -->
                    <a href="{{ route('members.index', ['filter' => 'online']) }}" class="flex items-center gap-1.5 text-[10px] font-black text-emerald-800 dark:text-emerald-300 bg-emerald-50 hover:bg-emerald-100 dark:bg-emerald-950/50 dark:hover:bg-emerald-900/50 border border-emerald-200/80 dark:border-emerald-800/50 px-2.5 py-1.5 rounded-xl shadow-2xs select-none transition cursor-pointer" title="Bekijk alle patiënten die nu online zijn">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span class="text-[9px] uppercase font-bold text-emerald-700 dark:text-emerald-400" x-text="stats.onlineCount + ' Online'">{{ $onlineCount }} Online</span>
                    </a>

                    @if(Auth::check() && Auth::user()->isStaff())
                    <a href="{{ route('admin.dashboard') }}" class="px-2.5 py-1.5 rounded-xl bg-purple-50 dark:bg-purple-950/60 hover:bg-purple-100 dark:hover:bg-purple-900/60 text-purple-700 dark:text-purple-300 border border-purple-200 dark:border-purple-800/50 font-extrabold text-xs flex items-center gap-1.5 transition shadow-2xs" title="Medewerkers Admin Panel">
                        <i class="fa-solid fa-shield-halved text-purple-600 dark:text-purple-400"></i>
                        <span class="hidden xl:inline">Admin</span>
                    </a>
                    @endif

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
        <header class="bg-white/95 backdrop-blur-md border-b border-slate-200 sticky top-0 z-40 shadow-xs">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-18 flex items-center justify-between gap-4">
                <a href="{{ route('login') }}" data-turbo="false" data-turbo-prefetch="false" class="flex items-center gap-3 group">
                    <div class="w-11 h-11 shrink-0 group-hover:scale-105 transition-transform flex items-center justify-center">
                        <img src="{{ asset('assets/logo.png') }}?v=3" alt="TBS Kliniek" class="w-full h-full object-contain drop-shadow-md">
                    </div>
                    <div>
                        <span class="block text-base font-extrabold tracking-tight text-slate-900 uppercase leading-none">
                            TBS <span class="text-indigo-600">KLINIEK</span>
                        </span>
                    </div>
                </a>

                <div class="flex items-center gap-3">
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

                    <a href="https://discord.gg/tbs" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 bg-[#5865F2] hover:bg-[#4752C4] text-white text-xs sm:text-sm font-extrabold px-3.5 sm:px-4 py-2 rounded-xl transition shadow-xs hover:shadow-sm active:scale-[0.98]">
                        <i class="fa-brands fa-discord text-sm"></i>
                        <span>Word lid van onze Discord</span>
                    </a>
                </div>
            </div>
        </header>
        @endif

        <!-- MAIN CONTENT CONTAINER (pt-4 sm:pt-8, pb-28 zodat mobiele bottom dock niets overlapt) -->
        <main class="flex-1 w-full max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 pt-4 sm:pt-8 pb-28 lg:pb-12">
            <!-- Active Prison / Isoleercel Alert Banner with Live Seconds Ticker & Star Buyout -->
            @if(isset($character) && $character->isImprisoned() && !request()->routeIs('prison'))
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
                 class="mb-6 bg-white border border-rose-200/90 rounded-xl p-4 sm:p-5 shadow-xs flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="flex items-center gap-3.5 w-full sm:w-auto">
                    <div class="w-10 h-10 rounded-xl bg-rose-50 border border-rose-200 text-rose-600 flex items-center justify-center text-lg shrink-0">
                        <i class="fa-solid fa-lock"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-black text-rose-700 uppercase tracking-wide flex items-center gap-1.5">
                                <i class="fa-solid fa-triangle-exclamation text-rose-500"></i>
                                Vast in de isoleercel
                            </span>
                            <span class="px-2 py-0.5 rounded-full bg-rose-50 border border-rose-200 text-rose-700 text-[10px] font-mono font-extrabold" x-text="formatTime(sec)">
                                {{ gmdate('i:s', $character->remainingPrisonSeconds()) }}
                            </span>
                        </div>
                        <p class="text-xs text-slate-600 mt-1 font-medium">
                            Resterende straftijd: <strong class="text-slate-900 font-mono" x-text="formatTime(sec) + ' (' + sec + 's)'">{{ gmdate('i:s', $character->remainingPrisonSeconds()) }} ({{ $character->remainingPrisonSeconds() }}s)</strong>.
                            Reden: <span class="text-rose-700 font-semibold">{{ $character->prison_reason ?? 'Wangedrag in de kliniek.' }}</span>
                        </p>
                    </div>
                </div>
                <div class="flex flex-wrap items-center gap-2 w-full sm:w-auto shrink-0">
                    <form action="{{ route('prison.buyout') }}" method="POST" class="w-full sm:w-auto">
                        @csrf
                        <input type="hidden" name="method" value="cash">
                        <button type="submit" 
                                class="w-full sm:w-auto px-3.5 py-2.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white font-extrabold text-xs uppercase tracking-wider transition shadow-xs cursor-pointer flex items-center justify-center gap-1.5">
                            <i class="fa-solid fa-money-bill-wave text-white"></i>
                            <span>Borgtocht (€250)</span>
                        </button>
                    </form>
                    <form action="{{ route('prison.buyout') }}" method="POST" class="w-full sm:w-auto">
                        @csrf
                        <input type="hidden" name="method" value="stars">
                        <button type="submit" 
                                class="w-full sm:w-auto px-3.5 py-2.5 rounded-lg bg-amber-500 hover:bg-amber-600 active:scale-95 text-white font-extrabold text-xs uppercase tracking-wider transition shadow-xs cursor-pointer flex items-center justify-center gap-1.5">
                            <i class="fa-solid fa-star text-white"></i>
                            <span>2 Sterren</span>
                        </button>
                    </form>
                    <a href="{{ route('prison') }}" class="w-full sm:w-auto px-3.5 py-2.5 rounded-lg bg-purple-50 hover:bg-purple-100 border border-purple-200 text-purple-700 font-extrabold text-xs transition text-center shrink-0 flex items-center justify-center gap-1.5">
                        <i class="fa-solid fa-building-shield"></i>
                        <span>Gevangenis</span>
                    </a>
                </div>
            </div>
            @endif

            <!-- Flash messages -->
            @if(session('evening_drop_awarded'))
            <div class="mb-6 bg-gradient-to-r from-amber-500/10 via-purple-500/10 to-indigo-500/10 border-2 border-amber-400 p-4 sm:p-5 rounded-2xl shadow-md flex items-center justify-between gap-4 animate-in fade-in slide-in-from-top-4 duration-300">
                <div class="flex items-center gap-3.5">
                    <div class="w-12 h-12 rounded-xl bg-amber-400 text-slate-950 flex items-center justify-center text-xl font-black shrink-0 shadow-sm animate-bounce">
                        
                    </div>
                    <div>
                        <h4 class="text-sm font-black text-slate-900 flex items-center gap-2">
                            <span>22:00 AVONDRONDE BELONING!</span>
                            <span class="bg-amber-400 text-slate-950 text-[10px] font-black px-2 py-0.5 rounded-full uppercase">3x Kluiscodes</span>
                        </h4>
                        <p class="text-xs text-slate-600 mt-0.5">
                            Omdat je aanwezig bent in de TBS Kliniek heb je <strong>3 gratis geheime kluiscodes</strong> ontvangen in je dossier en postvak!
                        </p>
                    </div>
                </div>
                <a href="{{ route('vault.index') }}" class="shrink-0 hidden sm:inline-flex items-center gap-2 px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-black shadow-xs transition active:scale-95">
                    <i class="fa-solid fa-vault text-amber-400"></i>
                    <span>Naar MedicijnKluis</span>
                </a>
            </div>
            @endif

            @if(session('success') || session('status'))
            <div class="mb-6 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/60 p-4 rounded-xl shadow-xs flex items-start gap-3">
                <i class="fa-solid fa-circle-check text-emerald-600 dark:text-emerald-400 text-lg mt-0.5 shrink-0"></i>
                <div>
                    <p class="text-xs font-extrabold text-emerald-900 dark:text-emerald-200">Actie Geslaagd!</p>
                    <p class="text-xs text-emerald-800 dark:text-emerald-300 mt-0.5 leading-relaxed">{{ session('success') ?? session('status') }}</p>
                </div>
            </div>
            @endif

            @if(session('error'))
            <div class="mb-6 bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800/60 p-4 rounded-xl shadow-xs flex items-start gap-3">
                <i class="fa-solid fa-triangle-exclamation text-rose-600 dark:text-rose-400 text-lg mt-0.5 shrink-0"></i>
                <div>
                    <p class="text-xs font-extrabold text-rose-900 dark:text-rose-200">Let op / Tegenwerking!</p>
                    <p class="text-xs text-rose-800 dark:text-rose-300 mt-0.5 leading-relaxed">{{ session('error') }}</p>
                </div>
            </div>
            @endif

            @yield('content')
        </main>

        <!-- SITE-WIDE JURIDISCHE & COMPLIANCE FOOTER (Nederland & Europa AVG/GDPR) -->
        <footer class="mt-auto border-t border-slate-200 bg-white pt-10 pb-20 lg:pb-10 text-xs text-slate-500">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 space-y-8">
                
                <!-- Main Footer Grid -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                    
                    <!-- Col 1: Branding & Intro -->
                    <div class="space-y-3 md:col-span-1">
                        <div class="flex items-center gap-2.5">
                            <img src="{{ asset('assets/logo.png') }}?v=3" alt="TBS Kliniek" class="w-8 h-8 object-contain drop-shadow-xs">
                            <span class="font-black text-slate-900 text-base">TBS <span class="text-purple-600">Kliniek</span></span>
                        </div>
                        <p class="text-xs text-slate-500 leading-relaxed">
                            Het #1 Nederlandse misdaad- & gevangenis browsergame. Overleef de afdeling, leid je bende en heers over de kliniek.
                        </p>
                        <div class="flex items-center gap-2 pt-1">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700 text-[10px] font-bold border border-emerald-200">
                                <i class="fa-solid fa-lock text-[9px]"></i> SSL Beveiligd
                            </span>
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-purple-50 text-purple-700 text-[10px] font-bold border border-purple-200">
                                <i class="fa-solid fa-shield-halved text-[9px]"></i> AVG Compliant
                            </span>
                        </div>
                    </div>

                    <!-- Col 2: Spel & Actie -->
                    <div class="space-y-2.5">
                        <h4 class="font-extrabold text-xs uppercase tracking-wider text-slate-900">Spel & Features</h4>
                        <ul class="space-y-1.5 text-xs text-slate-600">
                            <li><a href="{{ route('dashboard') }}" class="hover:text-purple-600 transition">Dashboard & Profiel</a></li>
                            <li><a href="{{ route('fight.index') }}" class="hover:text-purple-600 transition">De Luchtplaats (Vechten)</a></li>
                            <li><a href="{{ route('vault.index') }}" class="hover:text-purple-600 transition">MedicijnKluis & Codes</a></li>
                            <li><a href="{{ route('sponsor.index') }}" class="hover:text-purple-600 transition font-bold text-purple-700 flex items-center gap-1.5">
                                <i class="fa-solid fa-tv text-purple-600 text-[10px]"></i> Sponsorkamer (Gratis VIP)
                            </a></li>
                            <li><a href="{{ route('vip.index') }}" class="hover:text-purple-600 transition">VIP Lidmaatschap (Binnenkort Gratis via Ads)</a></li>
                            <li><a href="{{ route('roadmap.index') }}" class="hover:text-purple-600 transition">Roadmap & Updates</a></li>
                        </ul>
                    </div>

                    <!-- Col 3: Hulp & Ondersteuning -->
                    <div class="space-y-2.5">
                        <h4 class="font-extrabold text-xs uppercase tracking-wider text-slate-900">Ondersteuning</h4>
                        <ul class="space-y-1.5 text-xs text-slate-600">
                            <li><a href="{{ route('faq.index') }}" class="hover:text-purple-600 transition">Kennisbank & FAQ</a></li>
                            <li><a href="{{ route('help.tickets.index') }}" class="hover:text-purple-600 transition">Support Ticketsysteem</a></li>
                            <li><a href="{{ route('help.tickets.create') }}" class="hover:text-purple-600 transition">Nieuw Ticket Openen</a></li>
                            <li><a href="{{ route('settings.account') }}" class="hover:text-purple-600 transition">Account & Beveiliging</a></li>
                            <li><a href="https://discord.gg/tbs" target="_blank" rel="noopener" class="hover:text-purple-600 transition flex items-center gap-1.5">
                                <i class="fa-brands fa-discord text-indigo-500"></i> Officiële Discord
                            </a></li>
                            <li><a href="https://www.tiktok.com/@tbsklinieknl" target="_blank" rel="noopener" class="hover:text-purple-600 transition flex items-center gap-1.5 font-bold text-slate-800">
                                <i class="fa-brands fa-tiktok text-slate-900 text-xs"></i> TikTok @tbsklinieknl
                            </a></li>
                        </ul>
                    </div>

                    <!-- Col 4: Juridisch & Privacy (NL / EU) -->
                    <div class="space-y-2.5">
                        <h4 class="font-extrabold text-xs uppercase tracking-wider text-slate-900">Juridisch & Privacy</h4>
                        <ul class="space-y-1.5 text-xs text-slate-600">
                            <li><a href="{{ route('legal.terms') }}" class="hover:text-purple-600 transition">Algemene Voorwaarden</a></li>
                            <li><a href="{{ route('legal.privacy') }}" class="hover:text-purple-600 transition">Privacybeleid (AVG / GDPR)</a></li>
                            <li><a href="{{ route('legal.cookies') }}" class="hover:text-purple-600 transition">Cookiebeleid</a></li>
                            <li><a href="{{ route('legal.disclaimer') }}" class="hover:text-purple-600 transition">Disclaimer & Colofon</a></li>
                            <li>
                                <button type="button" onclick="window.tbsOpenCookieSettings()" class="hover:text-purple-600 transition text-left cursor-pointer flex items-center gap-1.5 text-purple-700 font-bold">
                                    <i class="fa-solid fa-cookie-bite text-amber-600 text-[10px]"></i> Cookie-instellingen Beheren
                                </button>
                            </li>
                        </ul>
                    </div>

                </div>

                <!-- Satire / Fictief Verklaring Bar -->
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 text-[11px] text-slate-500 leading-relaxed flex items-start gap-3">
                    <i class="fa-solid fa-circle-info text-purple-600 text-sm mt-0.5 shrink-0"></i>
                    <div>
                        <strong class="text-slate-800 font-bold">Fictief Rollenspel Verklaring:</strong> TBS Kliniek is een 100% fictief online browsergame (RPG / satire) ter recreatief vermaak. Deze website is op geen enkele wijze gelieerd aan het Ministerie van Justitie en Veiligheid, de Dienst Justitiële Inrichtingen (DJI) of bestaande forensische psychiatrische centra. In-game valuta en items hebben geen reële economische waarde.
                    </div>
                </div>


                <!-- Copyright Bar -->
                <div class="pt-4 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-3 text-[11px] text-slate-400">
                    <div>
                        &copy; {{ date('Y') }} TBS Kliniek (tbs-kliniek.nl). Alle rechten voorbehouden.
                    </div>
                    <div class="flex items-center gap-4">
                        <a href="{{ route('legal.terms') }}" class="hover:underline">Voorwaarden</a>
                        <span>•</span>
                        <a href="{{ route('legal.privacy') }}" class="hover:underline">Privacy</a>
                        <span>•</span>
                        <a href="{{ route('legal.cookies') }}" class="hover:underline">Cookies</a>
                        <span>•</span>
                        <a href="{{ route('legal.disclaimer') }}" class="hover:underline">Colofon</a>
                    </div>
                </div>

            </div>
        </footer>

        @if(isset($character))
        <!-- MOBILE BOTTOM DOCK NAV -->
        <nav class="lg:hidden fixed bottom-0 inset-x-0 bg-white border-t border-slate-200 z-50 flex items-center justify-around py-2.5 px-3 shadow-lg">
            <a href="{{ route('dashboard') }}" class="flex flex-col items-center gap-1 text-[11px] font-bold {{ request()->routeIs('dashboard') ? 'text-indigo-600' : 'text-slate-400' }}">
                <i class="fa-solid fa-house text-base"></i>
                <span>Home</span>
            </a>
            <a href="{{ route('hustle.index') }}" class="flex flex-col items-center gap-1 text-[11px] font-bold {{ request()->routeIs('hustle.*') ? 'text-indigo-600' : 'text-slate-400' }}">
                <i class="fa-solid fa-hand-holding-dollar text-base"></i>
                <span>Hosselen</span>
            </a>
            <a href="{{ route('fight.index') }}" class="flex flex-col items-center gap-1 text-[11px] font-bold {{ request()->routeIs('fight.*') ? 'text-indigo-600' : 'text-slate-400' }}">
                <i class="fa-solid fa-hand-fist text-base"></i>
                <span>Luchtplaats</span>
            </a>
            <a href="{{ route('gangs.index') }}" class="flex flex-col items-center gap-1 text-[11px] font-bold {{ request()->routeIs('gangs.*') ? 'text-indigo-600' : 'text-slate-400' }}">
                <i class="fa-solid fa-users text-base"></i>
                <span>Bendes</span>
            </a>
            <a href="{{ route('profile.me') }}" class="flex flex-col items-center gap-1 text-[11px] font-bold {{ request()->routeIs('profile.*') ? 'text-indigo-600' : 'text-slate-400' }}">
                <i class="fa-solid fa-user text-base"></i>
                <span>Profiel</span>
            </a>
        </nav>
        @endif
    </div>

    <!-- GLOBAL TBS CONFIRMATION MODAL (Eigen CSS & Alpine.js) -->
    <div x-data="{
        isOpen: false,
        title: 'Weet je het zeker?',
        message: '',
        confirmBtn: 'Ja, doorgaan',
        cancelBtn: 'Annuleren',
        type: 'danger',
        icon: 'fa-triangle-exclamation',
        callback: null,
        init() {
            window.tbsConfirm = (opts) => {
                this.title = opts.title || 'Weet je het zeker?';
                this.message = opts.message || 'Weet je zeker dat je deze actie wilt uitvoeren?';
                this.confirmBtn = opts.confirmBtn || 'Ja, uitvoeren';
                this.cancelBtn = opts.cancelBtn || 'Annuleren';
                this.type = opts.type || 'danger';
                this.icon = opts.icon || (this.type === 'attack' ? 'fa-crosshairs' : (this.type === 'logout' ? 'fa-right-from-bracket' : (this.type === 'warning' ? 'fa-triangle-exclamation' : 'fa-trash-can')));
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
        
        <!-- Backdrop with Blur -->
        <div class="fixed inset-0 bg-slate-950/75 backdrop-blur-xs transition-opacity" 
             x-show="isOpen"
             x-transition:enter="ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="cancel()"></div>

        <!-- Modal Dialog -->
        <div class="relative w-full max-w-md bg-white rounded-3xl shadow-2xl border border-slate-200/90 overflow-hidden transform transition-all z-10"
             x-show="isOpen"
             x-transition:enter="ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95 translate-y-2"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
             x-transition:leave-end="opacity-0 scale-95 translate-y-2">
            
            <!-- Top Gradient Bar -->
            <div class="h-1.5 w-full"
                 :class="{
                     'bg-gradient-to-r from-rose-500 via-pink-500 to-rose-600': type === 'attack' || type === 'danger',
                     'bg-gradient-to-r from-purple-800 via-indigo-700 to-slate-900': type === 'logout',
                     'bg-gradient-to-r from-amber-400 via-orange-400 to-amber-500': type === 'warning',
                     'bg-gradient-to-r from-purple-600 to-indigo-600': type === 'info'
                 }"></div>

            <!-- Modal Content -->
            <div class="p-6 sm:p-7 text-center">
                <!-- Close Button (X) -->
                <button type="button" @click="cancel()" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 transition w-8 h-8 rounded-full hover:bg-slate-100 flex items-center justify-center cursor-pointer">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>

                <!-- Icon Halo -->
                <div class="w-16 h-16 rounded-2xl flex items-center justify-center text-2xl mx-auto shadow-inner border transition-transform"
                     :class="{
                         'bg-rose-50 text-rose-600 border-rose-200 ring-8 ring-rose-50/60': type === 'attack' || type === 'danger',
                         'bg-purple-50 text-purple-700 border-purple-200 ring-8 ring-purple-50/60': type === 'logout',
                         'bg-amber-50 text-amber-600 border-amber-200 ring-8 ring-amber-50/60': type === 'warning',
                         'bg-indigo-50 text-indigo-600 border-indigo-200 ring-8 ring-indigo-50/60': type === 'info'
                     }">
                    <i class="fa-solid" :class="icon"></i>
                </div>

                <!-- Title & Message -->
                <h3 class="text-base sm:text-lg font-black text-slate-900 mt-4 tracking-tight" x-text="title"></h3>
                <p class="text-xs text-slate-600 mt-2 leading-relaxed px-2 font-medium" x-text="message"></p>

                <!-- Action Buttons -->
                <div class="mt-6 flex items-center gap-3">
                    <button type="button" 
                            @click="cancel()" 
                            class="flex-1 py-2.5 px-4 rounded-xl border border-slate-200 hover:bg-slate-100 text-slate-700 font-bold text-xs transition cursor-pointer"
                            x-text="cancelBtn">
                    </button>
                    <button type="button" 
                            @click="confirm()" 
                            class="flex-1 py-2.5 px-4 rounded-xl text-white font-black text-xs uppercase tracking-wider transition shadow-md hover:shadow-lg cursor-pointer flex items-center justify-center gap-1.5"
                            :class="{
                                'bg-rose-600 hover:bg-rose-700 shadow-rose-200': type === 'attack' || type === 'danger',
                                'bg-slate-900 hover:bg-slate-800 shadow-slate-200': type === 'logout',
                                'bg-amber-600 hover:bg-amber-700 shadow-amber-200': type === 'warning',
                                'bg-indigo-600 hover:bg-indigo-700 shadow-indigo-200': type === 'info'
                            }">
                        <i class="fa-solid" :class="icon"></i>
                        <span x-text="confirmBtn"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- GLOBAL SCRIPT FOR CONFIRM INTERCEPTION -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Intercept form submissions with data-confirm or legacy onsubmit="return confirm(...)"
            document.addEventListener('submit', function(e) {
                const form = e.target;
                if (!form || form.dataset.tbsConfirmed === 'true') {
                    if (form) delete form.dataset.tbsConfirmed;
                    return;
                }

                const submitter = e.submitter || (document.activeElement && form.contains(document.activeElement) ? document.activeElement : null);
                const confirmSource = (submitter && submitter.dataset && submitter.dataset.confirm) ? submitter : form;

                // 1. Explicit data-confirm attribute on form or submit button
                if (confirmSource.dataset && confirmSource.dataset.confirm) {
                    e.preventDefault();
                    e.stopImmediatePropagation();
                    if (window.tbsConfirm) {
                        window.tbsConfirm({
                            title: confirmSource.dataset.confirmTitle || 'Weet je het zeker?',
                            message: confirmSource.dataset.confirm,
                            confirmBtn: confirmSource.dataset.confirmBtn || 'Ja, doorgaan',
                            cancelBtn: confirmSource.dataset.confirmCancel || 'Annuleren',
                            type: confirmSource.dataset.confirmType || 'danger',
                            icon: confirmSource.dataset.confirmIcon,
                            onConfirm: function() {
                                form.dataset.tbsConfirmed = 'true';
                                form.submit();
                            }
                        });
                    } else if (confirm(confirmSource.dataset.confirm)) {
                        form.dataset.tbsConfirmed = 'true';
                        form.submit();
                    }
                    return;
                }

                // 2. Legacy onsubmit attribute with confirm(...)
                const onsubmitAttr = form.getAttribute('onsubmit');
                if (onsubmitAttr && onsubmitAttr.includes('confirm(')) {
                    e.preventDefault();
                    e.stopImmediatePropagation();
                    const match = onsubmitAttr.match(/confirm\s*\(\s*(['"])(.*?)\1\s*\)/);
                    const msg = match ? match[2] : 'Weet je zeker dat je wilt doorgaan?';

                    let type = 'danger';
                    let icon = 'fa-triangle-exclamation';
                    let btn = 'Ja, doorgaan';
                    let title = 'Bevestiging vereist';

                    const lower = msg.toLowerCase();
                    if (lower.includes('aanvallen') || lower.includes('oorlog') || lower.includes('overval')) {
                        type = 'attack';
                        icon = 'fa-crosshairs';
                        btn = 'Ja, Aanvallen!';
                        title = 'Bende Oorlog Aanval';
                    } else if (lower.includes('verwijderen') || lower.includes('opheffen') || lower.includes('weigeren') || lower.includes('annuleren')) {
                        type = 'danger';
                        icon = 'fa-trash-can';
                        btn = 'Verwijderen';
                        title = 'Verwijderen Bevestigen';
                    } else if (lower.includes('uitloggen')) {
                        type = 'logout';
                        icon = 'fa-right-from-bracket';
                        btn = 'Ja, Uitloggen';
                        title = 'Veilig Uitloggen';
                    }

                    if (window.tbsConfirm) {
                        window.tbsConfirm({
                            title: title,
                            message: msg,
                            confirmBtn: btn,
                            cancelBtn: 'Annuleren',
                            type: type,
                            icon: icon,
                            onConfirm: function() {
                                form.removeAttribute('onsubmit');
                                form.dataset.tbsConfirmed = 'true';
                                form.submit();
                                form.setAttribute('onsubmit', onsubmitAttr);
                            }
                        });
                    } else if (confirm(msg)) {
                        form.dataset.tbsConfirmed = 'true';
                        form.submit();
                    }
                }
            }, true);

            // Intercept click on links / buttons with data-confirm
            document.addEventListener('click', function(e) {
                const el = e.target.closest('a[data-confirm], button[data-confirm]:not([type="submit"])');
                if (!el || el.dataset.tbsConfirmed === 'true') {
                    if (el) delete el.dataset.tbsConfirmed;
                    return;
                }

                e.preventDefault();
                e.stopImmediatePropagation();

                if (window.tbsConfirm) {
                    window.tbsConfirm({
                        title: el.dataset.confirmTitle || 'Weet je het zeker?',
                        message: el.dataset.confirm,
                        confirmBtn: el.dataset.confirmBtn || 'Ja, doorgaan',
                        cancelBtn: el.dataset.confirmCancel || 'Annuleren',
                        type: el.dataset.confirmType || 'danger',
                        icon: el.dataset.confirmIcon,
                        onConfirm: function() {
                            el.dataset.tbsConfirmed = 'true';
                            if (el.tagName === 'A') {
                                window.location.href = el.href;
                            } else {
                                el.click();
                            }
                        }
                    });
                }
            }, true);
        });
    </script>

    <!-- FLOATING KENNISBANK CHAT ASSISTANT WIDGET (TBS Kliniek Styling) - ALLEEN VOOR INGELOGDE SPELERS -->
    @auth
    <script>
        (function() {
            const initHelpbot = () => {
                if (window.Alpine) {
                    window.Alpine.data('tbsHelpbot', () => ({
                        isOpen: false,
                        inputQuery: '',
                        messages: [
                            {
                                from: 'bot',
                                text: 'Hoi patiënt! Ik ben de TBS Zaalwacht AI. Ik weet álles over de kliniek: van energie, de isoleercel en de Respectlijst tot de centrale kluis, gangs en 24u gratis VIP. Stel gerust je vraag of klik hieronder op een snel onderwerp!',
                                links: []
                            }
                        ],
                        faqKnowledge: {!! json_encode(\App\Http\Controllers\FaqController::getBotKnowledge()) !!},
                        sendQuery(customText = null) {
                            const query = (customText || this.inputQuery).trim();
                            if (!query) return;

                            // Add user message
                            this.messages.push({ from: 'user', text: query });
                            this.inputQuery = '';

                            // Scroll down
                            this.$nextTick(() => {
                                const el = this.$refs.chatBox;
                                if (el) el.scrollTop = el.scrollHeight;
                            });

                            // Match answer with intelligent scoring
                            setTimeout(() => {
                                const qRaw = query.toLowerCase();
                                const qClean = qRaw.replace(/[^a-z0-9\s]/g, ' ');
                                const words = qClean.split(/\s+/).filter(w => w.length > 2 && !['wat', 'hoe', 'kan', 'het', 'een', 'die', 'van', 'naar', 'voor', 'met', 'zijn', 'mijn', 'waar', 'over', 'alle', 'welke', 'moet', 'doen', 'krijg'].includes(w));

                                const isGreeting = ['hallo', 'hoi', 'hey', 'goedendag', 'help', 'tips', 'uitleg', 'beginnen', 'starter'].some(g => qRaw.includes(g));

                                let bestItem = null;
                                let bestScore = 0;

                                for (const item of this.faqKnowledge) {
                                    let score = 0;
                                    const itemTitleLower = item.title.toLowerCase();
                                    const itemAnswerLower = item.answer.toLowerCase();

                                    // Direct title exact phrase match
                                    if (qRaw.includes(itemTitleLower) || itemTitleLower.includes(qRaw)) {
                                        score += 20;
                                    }

                                    // Keyword matches
                                    if (item.keywords && Array.isArray(item.keywords)) {
                                        for (const kw of item.keywords) {
                                            if (qRaw === kw) {
                                                score += 15;
                                            } else if (qRaw.includes(kw)) {
                                                score += kw.length > 4 ? 10 : 6;
                                            } else {
                                                for (const w of words) {
                                                    if (kw === w) score += 8;
                                                    else if (kw.startsWith(w) || w.startsWith(kw)) score += 4;
                                                }
                                            }
                                        }
                                    }

                                    // Content relevance score
                                    for (const w of words) {
                                        if (itemAnswerLower.includes(w)) {
                                            score += 2;
                                        }
                                    }

                                    if (score > bestScore) {
                                        bestScore = score;
                                        bestItem = item;
                                    }
                                }

                                if (bestScore >= 5 && bestItem) {
                                    this.messages.push({
                                        from: 'bot',
                                        text: bestItem.title + ':\n' + bestItem.answer,
                                        links: bestItem.links || []
                                    });
                                } else if (isGreeting) {
                                    const starterItem = this.faqKnowledge.find(i => i.keywords && i.keywords.includes('beginnen')) || this.faqKnowledge[0];
                                    this.messages.push({
                                        from: 'bot',
                                        text: 'Hoi patiënt!  Goed dat je het vraagt! Hier is de beknopte spoedcursus voor de TBS Kliniek:\n\n' + starterItem.answer,
                                        links: starterItem.links || []
                                    });
                                } else {
                                    this.messages.push({
                                        from: 'bot',
                                        text: 'Ik begreep je vraag niet helemaal. Probeer een korter zoekwoord zoals \'energie\', \'vechten\', \'bank\', \'kluis\', \'verlof\', \'bende\' of \'isoleercel\'.\n\nOf bekijk direct ons complete Handboek & Kennisbank:',
                                        links: [
                                            { text: 'Volledige FAQ Bekijken', url: '{{ route('faq') }}' },
                                            { text: 'Support Ticket Aanmaken', url: '{{ route('help.tickets.create') }}' }
                                        ]
                                    });
                                }

                                this.$nextTick(() => {
                                    const el = this.$refs.chatBox;
                                    if (el) el.scrollTop = el.scrollHeight;
                                });
                            }, 300);
                        }
                    }));
                }
            };

            if (window.Alpine) {
                initHelpbot();
            } else {
                document.addEventListener('alpine:init', initHelpbot);
            }
        })();
    </script>
    <div x-data="tbsHelpbot" class="hidden md:block fixed bottom-5 right-5 z-50">

        <!-- CHAT WINDOW POPUP (Website Style: Clean Light Card with Royal Purple Accents) -->
        <div x-show="isOpen" x-cloak style="display: none;"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-6 scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 scale-100"
             x-transition:leave-end="opacity-0 translate-y-6 scale-95"
             class="fixed bottom-20 right-5 w-[90vw] sm:w-96 max-h-[520px] bg-white border border-slate-200/90 rounded-2xl shadow-2xl flex flex-col overflow-hidden text-slate-800 z-50">
            
            <!-- Header -->
            <div class="bg-purple-700 p-3.5 border-b border-purple-800 flex items-center justify-between text-white shadow-xs">
                <div class="flex items-center gap-3">
                    <div class="relative">
                        <div class="w-9 h-9 rounded-xl bg-white/20 border border-white/30 backdrop-blur-xs flex items-center justify-center p-1 shadow-xs overflow-hidden">
                            <img src="{{ asset('assets/logo.png') }}?v=3" alt="TBS Logo" class="w-full h-full object-contain">
                        </div>
                        <span class="absolute -bottom-0.5 -right-0.5 w-2.5 h-2.5 rounded-full bg-emerald-400 border-2 border-purple-700"></span>
                    </div>
                    <div>
                        <h4 class="text-xs font-black tracking-tight text-white flex items-center gap-1.5">
                            <span>TBS Zaalwacht AI</span>
                            <span class="bg-white/20 text-white text-[9px] font-extrabold px-1.5 py-0.2 rounded border border-white/25">HELP</span>
                        </h4>
                        <p class="text-[10px] text-purple-100">Kennisbank & Vraagbaak</p>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <a href="{{ route('faq') }}" title="Volledige FAQ Pagina" class="text-white/80 hover:text-white text-xs transition p-1">
                        <i class="fa-solid fa-arrow-up-right-from-square"></i>
                    </a>
                    <button @click="isOpen = false" class="text-white/80 hover:text-white text-base transition cursor-pointer p-1">
                        &times;
                    </button>
                </div>
            </div>

            <!-- Messages Thread -->
            <div x-ref="chatBox" class="flex-1 p-4 overflow-y-auto space-y-3 max-h-80 text-xs bg-slate-50/70">
                <template x-for="(msg, i) in messages" :key="i">
                    <div :class="msg.from === 'bot' ? 'flex flex-col items-start' : 'flex flex-col items-end'">
                        <div :class="msg.from === 'bot' ? 'bg-white border border-slate-200 text-slate-800' : 'bg-purple-600 text-white font-medium'"
                             class="rounded-2xl p-3 max-w-[85%] space-y-2 shadow-2xs">
                            <p class="whitespace-pre-line leading-relaxed" x-text="msg.text"></p>
                            
                            <!-- Links if available -->
                            <template x-if="msg.links && msg.links.length > 0">
                                <div class="pt-1.5 flex flex-wrap gap-1.5 border-t border-slate-100">
                                    <template x-for="lnk in msg.links">
                                        <a :href="lnk.url" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-purple-50 hover:bg-purple-100 text-purple-700 hover:text-purple-800 text-[10px] font-extrabold border border-purple-200 transition">
                                            <span x-text="lnk.text"></span> &raquo;
                                        </a>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>

                <!-- Quick Suggested Topic Pills (Newb Friendly) -->
                <div class="pt-2">
                    <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400 block mb-1.5">Snel Onderwerp Kiezen:</span>
                    <div class="flex flex-wrap gap-1.5">
                        <button type="button" @click="sendQuery('Eerste stappen gids')" class="px-2.5 py-1 rounded-full bg-white hover:bg-purple-600 hover:text-white hover:border-purple-600 border border-slate-200 text-slate-700 text-[10px] font-bold transition cursor-pointer shadow-2xs inline-flex items-center gap-1.5">
                            <i class="fa-solid fa-graduation-cap text-purple-600 text-[10px]"></i> Eerste Stappen Gids
                        </button>
                        <button type="button" @click="sendQuery('Hoe krijg ik energie?')" class="px-2.5 py-1 rounded-full bg-white hover:bg-purple-600 hover:text-white hover:border-purple-600 border border-slate-200 text-slate-700 text-[10px] font-bold transition cursor-pointer shadow-2xs inline-flex items-center gap-1.5">
                            <i class="fa-solid fa-bolt text-emerald-500 text-[10px]"></i> Energie Herstel
                        </button>
                        <button type="button" @click="sendQuery('Waarom moet ik geld op de bank zetten?')" class="px-2.5 py-1 rounded-full bg-white hover:bg-purple-600 hover:text-white hover:border-purple-600 border border-slate-200 text-slate-700 text-[10px] font-bold transition cursor-pointer shadow-2xs inline-flex items-center gap-1.5">
                            <i class="fa-solid fa-building-columns text-emerald-600 text-[10px]"></i> Geld op de Bank
                        </button>
                        <button type="button" @click="sendQuery('Hoe kom ik sneller uit de isoleercel?')" class="px-2.5 py-1 rounded-full bg-white hover:bg-purple-600 hover:text-white hover:border-purple-600 border border-slate-200 text-slate-700 text-[10px] font-bold transition cursor-pointer shadow-2xs inline-flex items-center gap-1.5">
                            <i class="fa-solid fa-dungeon text-rose-500 text-[10px]"></i> Isoleercel Ontsnappen
                        </button>
                        <button type="button" @click="sendQuery('Hoe werkt de Respectlijst?')" class="px-2.5 py-1 rounded-full bg-white hover:bg-purple-600 hover:text-white hover:border-purple-600 border border-slate-200 text-slate-700 text-[10px] font-bold transition cursor-pointer shadow-2xs inline-flex items-center gap-1.5">
                            <i class="fa-solid fa-hand-fist text-purple-600 text-[10px]"></i> Respectlijst Vechten
                        </button>
                        <button type="button" @click="sendQuery('Wat is veilige dagbesteding?')" class="px-2.5 py-1 rounded-full bg-white hover:bg-purple-600 hover:text-white hover:border-purple-600 border border-slate-200 text-slate-700 text-[10px] font-bold transition cursor-pointer shadow-2xs inline-flex items-center gap-1.5">
                            <i class="fa-solid fa-person-digging text-amber-500 text-[10px]"></i> Veilige Dagbesteding
                        </button>
                        <button type="button" @click="sendQuery('Hoe kraak ik de kluis?')" class="px-2.5 py-1 rounded-full bg-white hover:bg-purple-600 hover:text-white hover:border-purple-600 border border-slate-200 text-slate-700 text-[10px] font-bold transition cursor-pointer shadow-2xs inline-flex items-center gap-1.5">
                            <i class="fa-solid fa-key text-amber-500 text-[10px]"></i> Medicijnkluis Kraken
                        </button>
                        <button type="button" @click="sendQuery('Hoe verdien ik gratis VIP?')" class="px-2.5 py-1 rounded-full bg-white hover:bg-purple-600 hover:text-white hover:border-purple-600 border border-slate-200 text-slate-700 text-[10px] font-bold transition cursor-pointer shadow-2xs inline-flex items-center gap-1.5">
                            <i class="fa-solid fa-tv text-purple-600 text-[10px]"></i> 24u Gratis VIP
                        </button>
                        <button type="button" @click="sendQuery('Hoe werkt het Behandelaarsgesprek en verlof?')" class="px-2.5 py-1 rounded-full bg-white hover:bg-purple-600 hover:text-white hover:border-purple-600 border border-slate-200 text-slate-700 text-[10px] font-bold transition cursor-pointer shadow-2xs inline-flex items-center gap-1.5">
                            <i class="fa-solid fa-user-doctor text-teal-600 text-[10px]"></i> Verlof & Psychiater
                        </button>
                    </div>
                </div>
            </div>

            <!-- Input Bar -->
            <form @submit.prevent="sendQuery()" class="p-3 bg-white border-t border-slate-200 flex items-center gap-2">
                <input type="text" x-model="inputQuery" placeholder="Stel een vraag aan de kennisbank..." 
                       class="flex-1 bg-slate-50 border border-slate-200 focus:border-purple-500 focus:bg-white rounded-xl px-3 py-2 text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-purple-500 transition">
                <button type="submit" class="w-9 h-9 rounded-xl bg-purple-600 hover:bg-purple-700 text-white flex items-center justify-center text-xs transition cursor-pointer shadow-sm">
                    <i class="fa-solid fa-paper-plane"></i>
                </button>
            </form>
        </div>

        <!-- FLOATING LAUNCHER BUTTON -->
        <button type="button" @click="isOpen = !isOpen"
                class="px-4 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-black text-xs shadow-lg shadow-purple-900/20 flex items-center gap-2.5 cursor-pointer transition transform hover:scale-102 active:scale-98 border border-purple-500">
            <div class="relative w-5 h-5 shrink-0 flex items-center justify-center">
                <img src="{{ asset('assets/logo.png') }}?v=3" alt="TBS Helpbot" class="w-full h-full object-contain">
                <span class="absolute -top-0.5 -right-0.5 w-2 h-2 rounded-full bg-emerald-400 ring-2 ring-purple-600"></span>
            </div>
            <span class="tracking-wide">Kliniek Helpbot</span>
        </button>
    </div>
    @endauth

    @if(isset($character) && $character)
    <!-- MOBILE BOTTOM NAVIGATION DOCK (Native App Feel) -->
    <nav class="lg:hidden fixed bottom-0 left-0 right-0 z-40 bg-white/95 backdrop-blur-md border-t border-slate-200/90 shadow-[0_-4px_16px_rgba(0,0,0,0.06)] px-2 py-1.5 flex items-center justify-around select-none">
        <!-- 1. Dashboard -->
        <a href="{{ route('dashboard') }}" class="flex flex-col items-center justify-center py-1 px-2.5 rounded-xl transition {{ request()->routeIs('dashboard') ? 'text-purple-600 font-extrabold' : 'text-slate-500 hover:text-slate-800 font-semibold' }}">
            <i class="fa-solid fa-house text-base"></i>
            <span class="text-[10px] mt-0.5">Home</span>
        </a>

        <!-- 2. Missies -->
        <a href="{{ route('missions.index') }}" class="flex flex-col items-center justify-center py-1 px-2.5 rounded-xl transition {{ request()->routeIs('missions.*') ? 'text-purple-600 font-extrabold' : 'text-slate-500 hover:text-slate-800 font-semibold' }}">
            <i class="fa-solid fa-list-check text-base"></i>
            <span class="text-[10px] mt-0.5">Missies</span>
        </a>

        <!-- 3. Luchtplaats / Vechten -->
        <a href="{{ route('fight.index') }}" class="flex flex-col items-center justify-center py-1 px-2.5 rounded-xl transition {{ request()->routeIs('fight.*') ? 'text-purple-600 font-extrabold' : 'text-slate-500 hover:text-slate-800 font-semibold' }}">
            <i class="fa-solid fa-hand-fist text-base"></i>
            <span class="text-[10px] mt-0.5">Vechten</span>
        </a>

        <!-- 4. Bank -->
        <a href="{{ route('bank.index') }}" class="flex flex-col items-center justify-center py-1 px-2.5 rounded-xl transition {{ request()->routeIs('bank.*') ? 'text-purple-600 font-extrabold' : 'text-slate-500 hover:text-slate-800 font-semibold' }}">
            <i class="fa-solid fa-building-columns text-base"></i>
            <span class="text-[10px] mt-0.5">Bank</span>
        </a>

        <!-- 5. Menu -->
        <button type="button" @click="mobileMenuOpen = true" class="flex flex-col items-center justify-center py-1 px-2.5 rounded-xl text-slate-500 hover:text-slate-800 font-semibold transition cursor-pointer">
            <i class="fa-solid fa-bars text-base"></i>
            <span class="text-[10px] mt-0.5">Menu</span>
        </button>
    </nav>
    @endif
    <!-- GLOBAL REWARDED SPONSOR AD WATCH MODAL (Adsterra Smartlink & Anti-abuse Validation) -->
    <div x-data="{
        isOpen: false,
        rewardType: 'cash',
        rewardTitle: '€ 2.500 Zakgeld',
        rewardIcon: 'fa-money-bill-wave',
        status: 'idle', // 'loading', 'watching', 'ready_to_claim', 'claiming', 'claimed', 'error'
        statusMessage: '',
        secondsLeft: 15,
        totalSeconds: 15,
        token: null,
        smartlinkUrl: '',
        callback: null,
        async checkAdblock() {
            try {
                const bait = document.createElement('div');
                bait.className = 'pub_300x250 pub_300x250m pub_728x90 text-ad textAd text_ad text_ads text-ads text-ad-links banner-ad ad-unit';
                bait.style.cssText = 'width: 1px !important; height: 1px !important; position: absolute !important; left: -10000px !important; top: -10000px !important;';
                document.body.appendChild(bait);
                const isBlocked = (bait.offsetParent === null || bait.offsetHeight === 0 || window.getComputedStyle(bait).display === 'none');
                bait.remove();
                if (isBlocked) return true;

                // Secondary test: network fetch to standard ad script
                const netCheck = await fetch('https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js', { method: 'HEAD', mode: 'no-cors' }).catch(() => true);
                return netCheck === true;
            } catch(e) {
                return false;
            }
        },
        init() {
            window.tbsOpenSponsorAd = async (rewardType = 'cash', callback = null) => {
                this.rewardType = rewardType;
                this.callback = callback;
                this.secondsLeft = 15;
                this.totalSeconds = 15;
                this.status = 'loading';
                this.statusMessage = 'Sponsor advertentie initialiseren...';
                this.isOpen = true;

                // Check for adblocker
                const hasAdblock = await this.checkAdblock();
                if (hasAdblock) {
                    this.status = 'adblock_detected';
                    this.statusMessage = 'Adblocker gedetecteerd! Schakel je adblocker uit voor TBS Kliniek of whitelist de website om de sponsor te bekijken en gratis beloningen te verdienen.';
                    return;
                }

                if (rewardType === 'prison') {
                    this.rewardTitle = '-15 Min Isoleercel Straf';
                    this.rewardIcon = 'fa-hourglass-half';
                } else if (rewardType === 'cooldown') {
                    this.rewardTitle = ' Reset Alle Cooldowns';
                    this.rewardIcon = 'fa-bolt';
                } else if (rewardType === 'mask') {
                    this.rewardTitle = '+1 Bivakmuts / Masker';
                    this.rewardIcon = 'fa-masks-theater';
                } else if (rewardType === 'token') {
                    this.rewardTitle = '+1 Kluismunt';
                    this.rewardIcon = 'fa-coins';
                } else if (rewardType === 'star') {
                    this.rewardTitle = '+1 Gratis Ster';
                    this.rewardIcon = 'fa-star';
                } else {
                    this.rewardTitle = '€ 2.500 Contant Zakgeld';
                    this.rewardIcon = 'fa-money-bill-wave';
                }

                fetch('{{ route('sponsor.start') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ reward_type: this.rewardType })
                })
                .then(res => res.json())
                .then(data => {
                    if (!data.success) {
                        this.status = 'error';
                        this.statusMessage = data.message || 'Kon advertentie niet starten.';
                        return;
                    }

                    this.token = data.token;
                    this.smartlinkUrl = data.smartlink_url;
                    this.secondsLeft = data.duration || 15;
                    this.totalSeconds = data.duration || 15;
                    this.status = 'watching';
                    this.statusMessage = 'De sponsor advertentie is geopend in een nieuw tabblad. Bekijk deze tijdens de afteltimer om je beloning te claimen!';

                    // Open smartlink in new tab (adsterra smartlink)
                    try {
                        window.open(this.smartlinkUrl, '_blank');
                    } catch(e) {
                        console.warn('Popup blocked, click manually', e);
                    }

                    // Countdown timer
                    const timer = setInterval(() => {
                        this.secondsLeft--;
                        if (this.secondsLeft <= 0) {
                            clearInterval(timer);
                            this.status = 'ready_to_claim';
                            this.statusMessage = 'Gefeliciteerd! De kijktijd is voltooid. Klik hieronder om jouw beloning direct te claimen!';
                        }
                    }, 1000);
                })
                .catch(err => {
                    this.status = 'error';
                    this.statusMessage = 'Er is een netwerkfout opgetreden bij het laden van de sponsor.';
                });
            };
        },
        openAdTabAgain() {
            if (this.smartlinkUrl) {
                window.open(this.smartlinkUrl, '_blank');
            }
        },
        claim() {
            this.status = 'claiming';
            this.statusMessage = 'Beloning verifiëren en verwerken...';

            fetch('{{ route('sponsor.claim') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ token: this.token })
            })
            .then(res => res.json())
            .then(data => {
                if (!data.success) {
                    this.status = 'error';
                    this.statusMessage = data.message || 'Kon beloning niet claimen.';
                    return;
                }

                this.status = 'claimed';
                this.statusMessage = data.message;

                setTimeout(() => {
                    this.isOpen = false;
                    if (typeof this.callback === 'function') {
                        this.callback(data);
                    } else {
                        // Return seamlessly to the exact same page
                        window.location.reload();
                    }
                }, 1600);
            })
            .catch(err => {
                this.status = 'error';
                this.statusMessage = 'Netwerkfout bij claimen van beloning.';
            });
        },
        closeModal() {
            this.isOpen = false;
        }
    }"
    x-show="isOpen"
    x-cloak
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/70 backdrop-blur-xs select-none">
        
        <div @click.away="if(status !== 'watching') closeModal()"
             class="bg-white border border-purple-200 rounded-3xl p-6 sm:p-8 max-w-md w-full shadow-2xl relative overflow-hidden text-center space-y-5">
            
            <!-- Top Badges -->
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-purple-50 text-purple-700 text-[11px] font-black border border-purple-200">
                    <i class="fa-solid fa-tv text-purple-600"></i> TBS Sponsorkamer
                </div>
                <button type="button" @click="closeModal()" class="text-slate-400 hover:text-slate-600 text-lg transition cursor-pointer p-1">
                    &times;
                </button>
            </div>

            <!-- Reward Display Box -->
            <div class="p-4 rounded-2xl bg-gradient-to-br from-purple-50 to-indigo-50/50 border border-purple-100 space-y-2">
                <div class="w-14 h-14 mx-auto rounded-2xl bg-white text-purple-600 border border-purple-200 flex items-center justify-center text-2xl shadow-xs">
                    <i class="fa-solid" :class="rewardIcon"></i>
                </div>
                <div>
                    <span class="text-[10px] font-extrabold uppercase tracking-wider text-purple-600">Jouw Gekozen Beloning</span>
                    <h3 class="text-lg font-black text-slate-900" x-text="rewardTitle"></h3>
                </div>
            </div>

            <!-- WATCHING PROGRESS & TIMER -->
            <template x-if="status === 'watching'">
                <div class="space-y-4">
                    <div class="space-y-2">
                        <div class="flex items-center justify-between text-xs font-bold text-slate-700">
                            <span>Sponsor bekijken...</span>
                            <span class="font-mono text-purple-700 text-sm" x-text="secondsLeft + 's resterend'">15s</span>
                        </div>
                        <div class="w-full bg-slate-100 rounded-full h-3 border border-slate-200 overflow-hidden">
                            <div class="bg-gradient-to-r from-purple-600 to-indigo-600 h-full rounded-full transition-all duration-1000 shadow-xs"
                                 :style="'width: ' + ((totalSeconds - secondsLeft) / totalSeconds * 100) + '%;'"></div>
                        </div>
                    </div>

                    <p class="text-xs text-slate-500 leading-relaxed" x-text="statusMessage"></p>



                    <button type="button" @click="openAdTabAgain()" class="text-xs text-purple-600 hover:underline font-bold inline-flex items-center gap-1 cursor-pointer">
                        <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i> Advertentietabblad opnieuw openen
                    </button>
                </div>
            </template>

            <!-- READY TO CLAIM BUTTON (Pulsing Green) -->
            <template x-if="status === 'ready_to_claim'">
                <div class="space-y-4 animate-fade-in">
                    <div class="w-12 h-12 mx-auto rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-xl shadow-xs">
                        <i class="fa-solid fa-check"></i>
                    </div>
                    <p class="text-xs text-slate-600 font-medium" x-text="statusMessage"></p>

                    <button type="button" @click="claim()"
                            class="w-full py-3.5 px-6 rounded-2xl bg-gradient-to-r from-emerald-500 to-green-600 hover:from-emerald-600 hover:to-green-700 text-white font-black text-sm uppercase tracking-wider shadow-lg shadow-emerald-500/25 transition transform hover:scale-102 active:scale-98 cursor-pointer flex items-center justify-center gap-2">
                        <i class="fa-solid fa-gift text-base"></i>
                        <span>Claim Beloning & Terug naar Spel</span>
                    </button>
                </div>
            </template>

            <!-- CLAIMING SPINNER -->
            <template x-if="status === 'claiming'">
                <div class="py-6 space-y-3">
                    <div class="w-10 h-10 border-4 border-purple-200 border-t-purple-600 rounded-full animate-spin mx-auto"></div>
                    <p class="text-xs text-slate-600 font-bold" x-text="statusMessage"></p>
                </div>
            </template>

            <!-- CLAIMED CELEBRATION -->
            <template x-if="status === 'claimed'">
                <div class="py-4 space-y-3 animate-fade-in">
                    <div class="w-14 h-14 mx-auto rounded-full bg-emerald-500 text-white flex items-center justify-center text-2xl shadow-lg shadow-emerald-500/30">
                        <i class="fa-solid fa-trophy"></i>
                    </div>
                    <h4 class="text-base font-black text-emerald-800">Gelukt!</h4>
                    <p class="text-xs text-slate-700 font-semibold" x-text="statusMessage"></p>
                    <span class="text-[11px] text-slate-400 block">Je wordt direct teruggebracht naar de pagina...</span>
                </div>
            </template>

            <!-- ADBLOCK DETECTED STATE -->
            <template x-if="status === 'adblock_detected'">
                <div class="space-y-4 animate-fade-in">
                    <div class="w-14 h-14 mx-auto rounded-2xl bg-amber-100 text-amber-700 border border-amber-200 flex items-center justify-center text-2xl shadow-xs">
                        <i class="fa-solid fa-shield-halved"></i>
                    </div>
                    <div>
                        <h4 class="text-base font-black text-slate-900">Adblocker Actief</h4>
                        <p class="text-xs text-slate-600 mt-1 leading-relaxed">
                            Advertenties houden de <strong>TBS Kliniek gratis online</strong> voor iedereen. Schakel je adblocker uit voor deze website of zet <code>tbs-kliniek.nl</code> op de whitelist om advertenties te bekijken en beloningen te claimen!
                        </p>
                    </div>
                    <div class="pt-2 flex flex-col gap-2">
                        <button type="button" @click="window.tbsOpenSponsorAd(rewardType, callback)" 
                                class="w-full py-3 px-4 rounded-xl bg-purple-600 hover:bg-purple-700 active:scale-95 text-white font-extrabold text-xs shadow-xs transition flex items-center justify-center gap-2 cursor-pointer">
                            <i class="fa-solid fa-rotate-right"></i>
                            <span>Ik heb hem uitgeschakeld (Opnieuw proberen)</span>
                        </button>
                        <button type="button" @click="closeModal()" class="w-full py-2 px-4 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-bold transition">
                            Sluiten
                        </button>
                    </div>
                </div>
            </template>

            <!-- ERROR STATE -->
            <template x-if="status === 'error'">
                <div class="space-y-4">
                    <div class="w-12 h-12 mx-auto rounded-full bg-rose-100 text-rose-600 flex items-center justify-center text-xl">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                    </div>
                    <p class="text-xs text-rose-700 font-bold" x-text="statusMessage"></p>
                    <button type="button" @click="closeModal()" class="w-full py-2.5 px-4 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition">
                        Sluiten
                    </button>
                </div>
            </template>

        </div>
    </div>

    <!-- GLOBAL AVG / GDPR INTERACTIEVE COOKIE BANNER & VOORKEUREN MODAL -->
    <div x-data="{
        showBanner: false,
        showModal: false,
        prefs: {
            necessary: true,
            functional: true,
            marketing: true
        },
        init() {
            window.tbsOpenCookieSettings = () => {
                this.showModal = true;
            };

            const consent = localStorage.getItem('tbs_cookie_consent');
            if (!consent) {
                this.showBanner = true;
            } else {
                try {
                    this.prefs = JSON.parse(consent);
                } catch(e) {
                    this.showBanner = true;
                }
            }
        },
        acceptAll() {
            this.prefs = { necessary: true, functional: true, marketing: true };
            this.savePreferences();
        },
        acceptNecessary() {
            this.prefs = { necessary: true, functional: false, marketing: false };
            this.savePreferences();
        },
        savePreferences() {
            localStorage.setItem('tbs_cookie_consent', JSON.stringify(this.prefs));
            this.showBanner = false;
            this.showModal = false;
        }
    }">

        <!-- FLOATING BOTTOM COOKIE CONSENT BANNER (Website Style: Clean Light Card) -->
        <div x-show="showBanner" x-cloak
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-8"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 translate-y-8"
             class="fixed bottom-4 left-4 right-4 md:left-auto md:right-4 md:max-w-md z-40 bg-white/95 backdrop-blur-md border border-slate-200 rounded-2xl p-5 shadow-2xl space-y-3.5 text-slate-800">
            
            <div class="flex items-start gap-3">
                <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 border border-purple-200 flex items-center justify-center text-lg shrink-0 shadow-2xs">
                    <i class="fa-solid fa-cookie-bite"></i>
                </div>
                <div class="space-y-1">
                    <h4 class="text-sm font-black text-slate-900 tracking-tight">Wij respecteren jouw privacy & cookies</h4>
                    <p class="text-xs text-slate-500 leading-relaxed">
                        TBS Kliniek gebruikt functionele cookies voor inloggen en beveiliging, en optionele advertentiecookies van partners zoals Google AdSense en de gratis Sponsorkamer. Conform de AVG bepalen wij dit samen met jou.
                    </p>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row items-center gap-2 pt-1">
                <button type="button" @click="acceptAll()"
                        class="w-full sm:flex-1 py-2 px-3.5 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-extrabold text-xs transition cursor-pointer shadow-xs text-center">
                    Alles Accepteren
                </button>
                <button type="button" @click="acceptNecessary()"
                        class="w-full sm:flex-1 py-2 px-3.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition cursor-pointer text-center">
                    Alleen Noodzakelijk
                </button>
            </div>

            <div class="flex items-center justify-between text-[11px] text-slate-400 pt-1 border-t border-slate-100">
                <button type="button" @click="showModal = true" class="text-purple-600 hover:underline font-bold cursor-pointer">
                    Voorkeuren Aanpassen &raquo;
                </button>
                <a href="{{ route('legal.cookies') }}" class="hover:underline">Lees ons Cookiebeleid</a>
            </div>
        </div>

        <!-- COOKIE PREFERENCES MODAL (AVG Conform) -->
        <div x-show="showModal" x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs select-none">
            
            <div @click.away="showModal = false"
                 class="bg-white border border-slate-200 rounded-3xl p-6 sm:p-7 max-w-lg w-full shadow-2xl space-y-5 text-slate-800">
                
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <div class="flex items-center gap-2.5">
                        <i class="fa-solid fa-cookie-bite text-purple-600 text-lg"></i>
                        <h3 class="text-base font-black text-slate-900">Cookie-instellingen Beheren</h3>
                    </div>
                    <button type="button" @click="showModal = false" class="text-slate-400 hover:text-slate-600 text-lg cursor-pointer p-1">
                        &times;
                    </button>
                </div>

                <div class="space-y-3.5 max-h-80 overflow-y-auto text-xs pr-1">
                    
                    <!-- 1. Noodzakelijk -->
                    <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200 flex items-start justify-between gap-3">
                        <div class="space-y-0.5">
                            <h5 class="font-bold text-slate-900">Noodzakelijke Cookies</h5>
                            <p class="text-slate-500 text-[11px]">Vereist voor inloggen, sessiebeheer en CSRF-beveiliging. Kunnen niet worden uitgeschakeld.</p>
                        </div>
                        <input type="checkbox" checked disabled class="mt-1 rounded text-purple-600 cursor-not-allowed">
                    </div>

                    <!-- 2. Functioneel -->
                    <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200 flex items-start justify-between gap-3">
                        <div class="space-y-0.5">
                            <h5 class="font-bold text-slate-900">Functionele Voorkeuren</h5>
                            <p class="text-slate-500 text-[11px]">Onthoudt interface-instellingen, menu-status en helpbot gesprekken.</p>
                        </div>
                        <input type="checkbox" x-model="prefs.functional" class="mt-1 rounded text-purple-600 focus:ring-purple-500 cursor-pointer">
                    </div>

                    <!-- 3. Advertenties / Sponsoren -->
                    <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200 flex items-start justify-between gap-3">
                        <div class="space-y-0.5">
                            <h5 class="font-bold text-slate-900">Advertenties & Google AdSense</h5>
                            <p class="text-slate-500 text-[11px]">Maakt het mogelijk om advertenties van Google AdSense en sponsoren te tonen om het spel gratis te houden en beloningen te verdienen.</p>
                        </div>
                        <input type="checkbox" x-model="prefs.marketing" class="mt-1 rounded text-purple-600 focus:ring-purple-500 cursor-pointer">
                    </div>

                </div>

                <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-100">
                    <button type="button" @click="acceptAll()"
                            class="py-2 px-3.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition cursor-pointer">
                        Alles Accepteren
                    </button>
                    <button type="button" @click="savePreferences()"
                            class="py-2 px-4 rounded-xl bg-purple-600 hover:bg-purple-700 text-white text-xs font-extrabold transition cursor-pointer shadow-xs">
                        Voorkeuren Opslaan
                    </button>
                </div>

            </div>
        </div>

    </div>

    <!-- GLOBAL THEME TOGGLE SCRIPT -->
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
    </script>
</body>
</html>
