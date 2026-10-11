<!DOCTYPE html>
<html lang="nl" class="h-full bg-[#f8fafc]">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'TBS Kliniek - Het #1 Misdaad & Gevangenis Browsergame van Nederland')</title>
    
    <!-- PRIMARY SEO META TAGS -->
    <meta name="description" content="Speel gratis TBS Kliniek, hét ultieme Nederlandse misdaad en gevangenis RPG browsergame. Beter dan 112 Simulator of Baddie: vecht op de luchtplaats, kraak de medicijnkluis, bouw gangs op en heers over de kliniek!">
    <meta name="keywords" content="TBS Kliniek, TBS game, misdaad browser game, 112 simulator alternatief, baddie game, online rpg nederland, gevangenis spel, bendeoorlogen, tekstbased rpg, boeven spel, maffia game, online simulator, gratis online spelen">
    <meta name="author" content="TBS Kliniek">
    <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">
    <link rel="canonical" href="{{ url()->current() }}">

    <!-- OPEN GRAPH / FACEBOOK / DISCORD -->
    <meta property="og:type" content="website">
    <meta property="og:locale" content="nl_NL">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:site_name" content="TBS Kliniek">
    <meta property="og:title" content="TBS Kliniek - Overleef de afdeling, leid je bende en heers over de kliniek.">
    <meta property="og:description" content="Dé toonaangevende online misdaad- & gevangenissimulator. Vecht in de arena, claim je gang-uurloon en heers over de afdelingen!">
    <meta property="og:image" content="{{ asset('assets/illustrations/tbs_purple_hero.jpg') }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">

    <!-- TWITTER / X CARDS -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:url" content="{{ url()->current() }}">
    <meta name="twitter:title" content="TBS Kliniek - Het #1 Nederlandse Gevangenis & Misdaad RPG">
    <meta name="twitter:description" content="Sluit je aan bij de beruchtste bende van de kliniek. Gratis online speelbaar!">
    <meta name="twitter:image" content="{{ asset('assets/illustrations/tbs_purple_hero.jpg') }}">

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
                'description' => 'Het populairste Nederlandse misdaad browsergame en gevangenis RPG.',
                'inLanguage' => 'nl-NL',
            ],
            [
                '@type' => 'VideoGame',
                '@id' => url('/') . '#game',
                'name' => 'TBS Kliniek',
                'url' => url('/'),
                'genre' => ['RPG', 'Tekstbased RPG', 'Misdaad Game', 'Strategie', 'Gevangenis Simulator'],
                'gamePlatform' => ['Web Browser', 'iOS', 'Android', 'PC'],
                'applicationCategory' => 'GameApplication',
                'operatingSystem' => 'Any web browser',
                'description' => 'TBS Kliniek is een Nederlands online tekstbased RPG waarin je overleeft in een forensische instelling, bendes opricht, kluizen kraakt en vecht voor frikandelbroodjes en respect.',
                'offers' => [
                    '@type' => 'Offer',
                    'price' => '0',
                    'priceCurrency' => 'EUR',
                    'availability' => 'https://schema.org/InStock',
                ],
            ],
        ],
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}
    </script>

    <link rel="icon" type="image/png" href="{{ asset('assets/logo.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&family=Outfit:wght@600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <meta name="turbo-prefetch" content="false">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        html, body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            text-rendering: optimizeLegibility;
        }
    </style>
</head>
<body class="min-h-full flex flex-col lg:flex-row bg-[#f8fafc] text-slate-800 antialiased selection:bg-slate-900 selection:text-white" x-data="{ mobileMenuOpen: false }">

    @php
        $character = $character ?? (Auth::check() ? Auth::user()->character : null);
        $activeCategory = 'algemeen';
        if (request()->routeIs('hustle.*', 'smuggle.*', 'extort.*', 'lockers.*') || request()->is('hosselen*', 'smokkelen*', 'afpersen*', 'kluisjes*')) {
            $activeCategory = 'misdaad';
        } elseif (request()->routeIs('vault.*', 'packages.*', 'pickpocket.*') || request()->is('kluis-kraak*', 'pakketjes*', 'zakkenrollen*', 'vault*')) {
            $activeCategory = 'bonus';
        } elseif (request()->routeIs('fight.*', 'theft.*', 'combat_training.*', 'hitman.*', 'informants.*', 'isolation.*') || request()->is('luchtplaats*', 'vechten*', 'diefstal*', 'klusjes*', 'vechtoefening*', 'liquidatie*', 'informanten*', 'vrijwillige-isolatie*')) {
            $activeCategory = 'geweld';
        } elseif (request()->routeIs('bank.*', 'travel.*', 'casino.*', 'rps.*', 'highlow.*', 'numbergame.*', 'roulette.*', 'slots.*', 'shop.*', 'marketplace.*') || request()->is('bank*', 'verplaatsen*', 'reizen*', 'casino*', 'gokhal*', 'winkel*', 'shop*', 'marktplaats*')) {
            $activeCategory = 'economie';
        } elseif (request()->routeIs('missions.*', 'training.*', 'profile.*') || request()->is('missies*', 'training*', 'profiel*', 'speler*')) {
            $activeCategory = 'voortgang';
        }
    @endphp

    @if(isset($character) && $character)
    <!-- SIDEBAR NAVIGATION (Desktop & Tablet) - Clean Modern White Sidebar -->
    <aside class="hidden lg:flex lg:flex-col w-68 bg-white border-r border-slate-200/90 shrink-0 min-h-screen text-slate-700 z-40 sticky top-0 h-screen overflow-y-auto shadow-xs select-none"
           x-data="{
               openCategory: localStorage.getItem('tbs_sidebar_category') || '{{ $activeCategory }}',
               init() {
                   const serverCat = '{{ $activeCategory }}';
                   if (serverCat && serverCat !== 'algemeen') {
                       this.openCategory = serverCat;
                       localStorage.setItem('tbs_sidebar_category', serverCat);
                   } else if (!localStorage.getItem('tbs_sidebar_category')) {
                       this.openCategory = serverCat;
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
                    <img src="{{ asset('assets/logo.png') }}" alt="TBS Kliniek Emblem" class="w-full h-full object-contain">
                </div>
                <div class="flex flex-col">
                    <div class="flex items-center gap-1.5">
                        <span class="text-sm font-black tracking-tight text-slate-900 leading-none">TBS <span class="text-purple-600">KLINIEK</span></span>
                    </div>
                    <div class="mt-1">
                        <span class="inline-flex items-center gap-1.5 text-[9px] font-black uppercase tracking-wider text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            <span>Live</span>
                        </span>
                    </div>
                </div>
            </a>
        </div>

        <!-- Navigation Sections with Collapsible Categories -->
        <nav class="flex-1 px-3 py-4 space-y-2 text-xs font-semibold">

            <!-- 1. ALGEMEEN & POST -->
            <div class="space-y-1">
                <button @click="toggleCategory('algemeen')" 
                        type="button" 
                        class="w-full flex items-center justify-between px-3 py-2 text-[10px] font-extrabold uppercase tracking-wider text-slate-400 hover:text-slate-700 transition">
                    <span class="flex items-center gap-2">
                        <i class="fa-solid fa-shield-halved text-slate-400"></i> Algemeen & Post
                    </span>
                    <i class="fa-solid fa-chevron-down text-[9px] transition-transform duration-200" :class="openCategory === 'algemeen' ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="openCategory === 'algemeen'" x-collapse class="space-y-0.5 pl-1">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('dashboard') ? 'bg-indigo-50 text-indigo-700 font-bold border border-indigo-100' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                        <i class="fa-solid fa-gauge w-4 text-center text-indigo-500"></i>
                        <span>Dashboard</span>
                    </a>
                    <a href="{{ route('messages.index') }}" class="flex items-center justify-between px-3 py-2 rounded-lg transition {{ request()->routeIs('messages.*') ? 'bg-indigo-50 text-indigo-700 font-bold border border-indigo-100' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                        <div class="flex items-center gap-3">
                            <i class="fa-solid fa-envelope w-4 text-center text-blue-500"></i>
                            <span>Berichten & Post</span>
                        </div>
                        @php
                            $unreadMsgCount = \App\Models\GameMessage::where('recipient_id', $character->id)->where('is_read', false)->count();
                        @endphp
                        @if($unreadMsgCount > 0)
                            <span class="bg-rose-500 text-white text-[10px] font-extrabold px-1.5 py-0.2 rounded-full">{{ $unreadMsgCount }}</span>
                        @endif
                    </a>
                    <a href="{{ route('timeline.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('timeline.*') ? 'bg-indigo-50 text-indigo-700 font-bold border border-indigo-100' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                        <i class="fa-solid fa-bullhorn w-4 text-center text-sky-500"></i>
                        <span>Tijdlijn (Feed)</span>
                    </a>
                    <a href="{{ route('friends.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('friends.*') ? 'bg-indigo-50 text-indigo-700 font-bold border border-indigo-100' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                        <i class="fa-solid fa-user-group w-4 text-center text-pink-500"></i>
                        <span>Vrienden & BFF</span>
                    </a>
                    <a href="{{ route('leaderboard.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('leaderboard.*') ? 'bg-indigo-50 text-indigo-700 font-bold border border-indigo-100' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                        <i class="fa-solid fa-trophy w-4 text-center text-amber-500"></i>
                        <span>Ranglijsten</span>
                    </a>
                    <a href="{{ route('battle.index') }}" class="flex items-center justify-between px-3 py-2 rounded-lg transition {{ request()->routeIs('battle.*') ? 'bg-purple-50 text-purple-700 font-bold border border-purple-100' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                        <div class="flex items-center gap-3">
                            <i class="fa-solid fa-bolt w-4 text-center text-purple-600"></i>
                            <span>24 Uur Battle</span>
                        </div>
                        <span class="bg-purple-100 text-purple-700 text-[10px] font-black uppercase px-2 py-0.5 rounded-md border border-purple-200">
                            Live
                        </span>
                    </a>
                    <a href="{{ route('prison') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('prison') ? 'bg-indigo-50 text-indigo-700 font-bold border border-indigo-100' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                        <i class="fa-solid fa-dungeon w-4 text-center text-rose-500"></i>
                        <span>Isoleercel</span>
                    </a>
                    <a href="{{ route('gangs.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('gangs.*') ? 'bg-indigo-50 text-indigo-700 font-bold border border-indigo-100' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                        <i class="fa-solid fa-users w-4 text-center text-purple-500"></i>
                        <span>Bendes & Gangs</span>
                    </a>
                </div>
            </div>

            <!-- 2. ILLEGALE ACTIES -->
            <div class="space-y-1">
                <button @click="toggleCategory('misdaad')" 
                        type="button" 
                        class="w-full flex items-center justify-between px-3 py-2 text-[10px] font-extrabold uppercase tracking-wider text-slate-400 hover:text-slate-700 transition">
                    <span class="flex items-center gap-2">
                        <i class="fa-solid fa-handcuffs text-slate-400"></i> Illegale Acties
                    </span>
                    <i class="fa-solid fa-chevron-down text-[9px] transition-transform duration-200" :class="openCategory === 'misdaad' ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="openCategory === 'misdaad'" x-collapse class="space-y-0.5 pl-1">
                    <a href="{{ route('hustle.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('hustle.*') ? 'bg-indigo-50 text-indigo-700 font-bold border border-indigo-100' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                        <i class="fa-solid fa-hand-holding-dollar w-4 text-center text-emerald-500"></i>
                        <span>Hosselen</span>
                    </a>
                    <a href="{{ route('smuggle.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('smuggle.*') ? 'bg-indigo-50 text-indigo-700 font-bold border border-indigo-100' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                        <i class="fa-solid fa-capsules w-4 text-center text-teal-500"></i>
                        <span>Dealen & Smokkel</span>
                    </a>
                    <a href="{{ route('extort.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('extort.*') ? 'bg-indigo-50 text-indigo-700 font-bold border border-indigo-100' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                        <i class="fa-solid fa-person-military-pointing w-4 text-center text-orange-500"></i>
                        <span>Afpersen</span>
                    </a>
                    <a href="{{ route('lockers.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('lockers.*') ? 'bg-indigo-50 text-indigo-700 font-bold border border-indigo-100' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                        <i class="fa-solid fa-id-card w-4 text-center text-blue-500"></i>
                        <span>Kluisjes Kraken</span>
                    </a>
                </div>
            </div>

            <!-- 3. BONUS & KLUIZEN (Apart Onderdeel) -->
            <div class="space-y-1">
                <button @click="toggleCategory('bonus')" 
                        type="button" 
                        class="w-full flex items-center justify-between px-3 py-2 text-[10px] font-extrabold uppercase tracking-wider text-purple-600 hover:text-purple-800 transition">
                    <span class="flex items-center gap-2">
                        <i class="fa-solid fa-gift text-purple-500"></i> Bonus & Kluizen
                    </span>
                    <i class="fa-solid fa-chevron-down text-[9px] transition-transform duration-200" :class="openCategory === 'bonus' ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="openCategory === 'bonus'" x-collapse class="space-y-0.5 pl-1">
                    <a href="{{ route('vault.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('vault.*') ? 'bg-purple-50 text-purple-700 font-bold border border-purple-100' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                        <i class="fa-solid fa-vault w-4 text-center text-purple-500"></i>
                        <span>MedicijnKluis (Kraak)</span>
                    </a>
                    <a href="{{ route('packages.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('packages.*') ? 'bg-purple-50 text-purple-700 font-bold border border-purple-100' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                        <i class="fa-solid fa-box-open w-4 text-center text-amber-500"></i>
                        <span>Koffers & Pakketjes</span>
                    </a>
                    <a href="{{ route('pickpocket.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('pickpocket.*') ? 'bg-purple-50 text-purple-700 font-bold border border-purple-100' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                        <i class="fa-solid fa-mask w-4 text-center text-indigo-500"></i>
                        <span>Zakkenrollen</span>
                    </a>
                </div>
            </div>

            <!-- 3. GEWELD & STRIJD -->
            <div class="space-y-1">
                <button @click="toggleCategory('geweld')" 
                        type="button" 
                        class="w-full flex items-center justify-between px-3 py-2 text-[10px] font-extrabold uppercase tracking-wider text-slate-400 hover:text-slate-700 transition">
                    <span class="flex items-center gap-2">
                        <i class="fa-solid fa-fire text-slate-400"></i> Geweld & Strijd
                    </span>
                    <i class="fa-solid fa-chevron-down text-[9px] transition-transform duration-200" :class="openCategory === 'geweld' ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="openCategory === 'geweld'" x-collapse class="space-y-0.5 pl-1">
                    <a href="{{ route('fight.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('fight.*') ? 'bg-indigo-50 text-indigo-700 font-bold border border-indigo-100' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                        <i class="fa-solid fa-skull w-4 text-center text-rose-500"></i>
                        <span>Slaylist (Respectlijst)</span>
                    </a>
                    <a href="{{ route('theft.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('theft.*') ? 'bg-indigo-50 text-indigo-700 font-bold border border-indigo-100' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                        <i class="fa-solid fa-briefcase w-4 text-center text-slate-600"></i>
                        <span>Klusjes & Diefstal</span>
                    </a>
                    <a href="{{ route('combat_training.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('combat_training.*') ? 'bg-indigo-50 text-indigo-700 font-bold border border-indigo-100' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                        <i class="fa-solid fa-bullseye w-4 text-center text-indigo-500"></i>
                        <span>Vechtoefening</span>
                    </a>
                    <a href="{{ route('hitman.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('hitman.*') ? 'bg-indigo-50 text-indigo-700 font-bold border border-indigo-100' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                        <i class="fa-solid fa-crosshairs w-4 text-center text-rose-600"></i>
                        <span>Liquidatie</span>
                    </a>
                    <a href="{{ route('informants.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('informants.*') ? 'bg-indigo-50 text-indigo-700 font-bold border border-indigo-100' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                        <i class="fa-solid fa-user-secret w-4 text-center text-slate-700"></i>
                        <span>Informanten</span>
                    </a>
                    <a href="{{ route('isolation.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('isolation.*') ? 'bg-indigo-50 text-indigo-700 font-bold border border-indigo-100' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                        <i class="fa-solid fa-ghost w-4 text-center text-purple-500"></i>
                        <span>Vrijwillige Isolatie</span>
                    </a>
                </div>
            </div>

            <!-- 4. ECONOMIE & GOKKEN -->
            <div class="space-y-1">
                <button @click="toggleCategory('economie')" 
                        type="button" 
                        class="w-full flex items-center justify-between px-3 py-2 text-[10px] font-extrabold uppercase tracking-wider text-slate-400 hover:text-slate-700 transition">
                    <span class="flex items-center gap-2">
                        <i class="fa-solid fa-coins text-slate-400"></i> Economie & Gokken
                    </span>
                    <i class="fa-solid fa-chevron-down text-[9px] transition-transform duration-200" :class="openCategory === 'economie' ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="openCategory === 'economie'" x-collapse class="space-y-0.5 pl-1">
                    <a href="{{ route('bank.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('bank.*') ? 'bg-indigo-50 text-indigo-700 font-bold border border-indigo-100' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                        <i class="fa-solid fa-building-columns w-4 text-center text-emerald-500"></i>
                        <span>De Bank</span>
                    </a>
                    <a href="{{ route('travel.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('travel.*') ? 'bg-indigo-50 text-indigo-700 font-bold border border-indigo-100' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                        <i class="fa-solid fa-signs-post w-4 text-center text-teal-500"></i>
                        <span>Verplaatsen</span>
                    </a>
                    <a href="{{ route('casino.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('casino.*', 'rps.*', 'highlow.*', 'numbergame.*', 'roulette.*', 'slots.*') ? 'bg-indigo-50 text-indigo-700 font-bold border border-indigo-100' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                        <i class="fa-solid fa-dice w-4 text-center text-rose-500"></i>
                        <span>Casino & Gokhal</span>
                    </a>
                    <a href="{{ route('shop.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('shop.*') ? 'bg-indigo-50 text-indigo-700 font-bold border border-indigo-100' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                        <i class="fa-solid fa-cart-shopping w-4 text-center text-amber-500"></i>
                        <span>Kliniek Winkel</span>
                    </a>
                    <a href="{{ route('marketplace.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('marketplace.*') ? 'bg-indigo-50 text-indigo-700 font-bold border border-indigo-100' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                        <i class="fa-solid fa-shop w-4 text-center text-purple-600"></i>
                        <span>Marktplaats</span>
                    </a>
                </div>
            </div>

            <!-- 5. VOORTGANG & STATUS -->
            <div class="space-y-1">
                <button @click="toggleCategory('voortgang')" 
                        type="button" 
                        class="w-full flex items-center justify-between px-3 py-2 text-[10px] font-extrabold uppercase tracking-wider text-slate-400 hover:text-slate-700 transition">
                    <span class="flex items-center gap-2">
                        <i class="fa-solid fa-chart-line text-slate-400"></i> Voortgang & Profiel
                    </span>
                    <i class="fa-solid fa-chevron-down text-[9px] transition-transform duration-200" :class="openCategory === 'voortgang' ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="openCategory === 'voortgang'" x-collapse class="space-y-0.5 pl-1">
                    <a href="{{ route('missions.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('missions.*') ? 'bg-indigo-50 text-indigo-700 font-bold border border-indigo-100' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                        <i class="fa-solid fa-list-check w-4 text-center text-indigo-500"></i>
                        <span>Missies & Opdrachten</span>
                    </a>
                    <a href="{{ route('training.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('training.*') ? 'bg-indigo-50 text-indigo-700 font-bold border border-indigo-100' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                        <i class="fa-solid fa-dumbbell w-4 text-center text-purple-500"></i>
                        <span>Krachttraining</span>
                    </a>
                    <a href="{{ route('profile.me') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('profile.*') ? 'bg-indigo-50 text-indigo-700 font-bold border border-indigo-100' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                        <i class="fa-solid fa-id-badge w-4 text-center text-blue-500"></i>
                        <span>Mijn Profiel</span>
                    </a>
                    <a href="{{ route('vip.index') }}" class="flex items-center justify-between px-3 py-2 rounded-lg transition {{ request()->routeIs('vip.*') ? 'bg-amber-50 text-amber-800 font-bold border border-amber-200' : 'text-amber-700 hover:text-amber-900 hover:bg-amber-50/60' }}">
                        <div class="flex items-center gap-3">
                            <i class="fa-solid fa-crown w-4 text-center text-amber-500"></i>
                            <span class="font-extrabold">VIP Shop & Punten</span>
                        </div>
                        <span class="text-[9px] bg-gradient-to-r from-amber-400 to-yellow-500 text-slate-950 font-black px-1.5 py-0.2 rounded-sm shadow-2xs">€7,50</span>
                    </a>
                    <a href="{{ route('faq.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('faq.*') ? 'bg-indigo-50 text-indigo-700 font-bold border border-indigo-100' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                        <i class="fa-solid fa-circle-question w-4 text-center text-sky-500"></i>
                        <span>Kennisbank & FAQ</span>
                    </a>
                    <a href="{{ route('settings.account') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('settings.*') ? 'bg-indigo-50 text-indigo-700 font-bold border border-indigo-100' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                        <i class="fa-solid fa-gear w-4 text-center text-slate-500"></i>
                        <span>Account Instellingen</span>
                    </a>
                    @if(Auth::check() && Auth::user()->isStaff())
                    <a href="{{ route('admin.dashboard') }}" class="flex items-center justify-between px-3 py-2 rounded-lg transition {{ request()->routeIs('admin.*') ? 'bg-purple-100 text-purple-900 font-bold border border-purple-300' : 'text-purple-700 hover:text-purple-950 hover:bg-purple-50' }}">
                        <div class="flex items-center gap-3">
                            <i class="fa-solid fa-shield-halved w-4 text-center text-purple-600"></i>
                            <span class="font-black">Medewerkers Admin</span>
                        </div>
                        <span class="text-[9px] bg-purple-600 text-white font-black px-1.5 py-0.2 rounded-full uppercase">Staff</span>
                    </a>
                    @endif
                </div>
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
                <button type="submit" class="w-full flex items-center justify-center gap-2 px-3 py-2.5 rounded-xl text-xs font-bold text-slate-600 hover:text-rose-600 hover:bg-rose-50 border border-transparent hover:border-rose-100 transition cursor-pointer">
                    <i class="fa-solid fa-right-from-bracket"></i>
                    <span>Veilig Uitloggen</span>
                </button>
            </form>
        </div>
    </aside>

    <!-- MOBILE TOP HEADER BAR -->
    <div class="lg:hidden bg-white text-slate-800 px-4 py-3 flex items-center justify-between border-b border-slate-200 sticky top-0 z-50 shadow-xs">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5">
            <div class="w-9 h-9 shrink-0 drop-shadow-xs flex items-center justify-center">
                <img src="{{ asset('assets/logo.png') }}" alt="TBS Kliniek Logo" class="w-full h-full object-contain">
            </div>
            <span class="text-sm font-black tracking-tight text-slate-900">TBS <span class="text-purple-600">KLINIEK</span></span>
        </a>
        <div class="flex items-center gap-2.5">
            <span class="text-xs font-extrabold bg-emerald-50 text-emerald-800 border border-emerald-200 px-2 py-0.5 rounded-md">
                €{{ number_format($character->cash, 0, ',', '.') }}
            </span>
            <button @click="mobileMenuOpen = !mobileMenuOpen" class="text-slate-600 p-1.5 focus:outline-none rounded-lg hover:bg-slate-100 cursor-pointer">
                <i class="fa-solid" :class="mobileMenuOpen ? 'fa-xmark' : 'fa-bars'"></i>
            </button>
        </div>
    </div>

    <!-- MOBILE ACCORDION MENU -->
    <div x-show="mobileMenuOpen" x-cloak class="lg:hidden bg-white border-b border-slate-200 px-4 py-3 space-y-1 text-xs z-50 shadow-md">
        <div class="font-extrabold text-[10px] uppercase text-slate-400 pt-1 pb-0.5">Snelkoppelingen</div>
        <a href="{{ route('dashboard') }}" class="block py-2 px-3 rounded-lg font-bold text-slate-700 hover:bg-slate-50">Dashboard</a>
        <a href="{{ route('vip.index') }}" class="block py-2 px-3 rounded-lg font-bold text-amber-700 hover:bg-amber-50">👑 VIP Shop & Punten</a>
        <a href="{{ route('battle.index') }}" class="block py-2 px-3 rounded-lg font-bold text-pink-700 hover:bg-pink-50">🏆 24 Uur Battle</a>
        <a href="{{ route('hustle.index') }}" class="block py-2 px-3 rounded-lg font-bold text-slate-700 hover:bg-slate-50">Hosselen</a>
        <a href="{{ route('smuggle.index') }}" class="block py-2 px-3 rounded-lg font-bold text-slate-700 hover:bg-slate-50">Dealen & Smokkel</a>
        <a href="{{ route('fight.index') }}" class="block py-2 px-3 rounded-lg font-bold text-slate-700 hover:bg-slate-50">Luchtplaats (PvP)</a>
        <a href="{{ route('bank.index') }}" class="block py-2 px-3 rounded-lg font-bold text-slate-700 hover:bg-slate-50">De Bank</a>
        <a href="{{ route('casino.index') }}" class="block py-2 px-3 rounded-lg font-bold text-slate-700 hover:bg-slate-50">Casino & Gokhal</a>
        <a href="{{ route('gangs.index') }}" class="block py-2 px-3 rounded-lg font-bold text-slate-700 hover:bg-slate-50">Bendes</a>
        <a href="{{ route('profile.me') }}" class="block py-2 px-3 rounded-lg font-bold text-slate-700 hover:bg-slate-50">Mijn Profiel</a>
        <a href="{{ route('faq.index') }}" class="block py-2 px-3 rounded-lg font-bold text-slate-700 hover:bg-slate-50">❓ Kennisbank & FAQ</a>
        <a href="{{ route('settings.account') }}" class="block py-2 px-3 rounded-lg font-bold text-slate-700 hover:bg-slate-50">⚙️ Account Instellingen</a>
        @if(Auth::check() && Auth::user()->isStaff())
            <a href="{{ route('admin.dashboard') }}" class="block py-2 px-3 rounded-lg font-bold text-purple-700 hover:bg-purple-50">🛡️ Medewerkers Admin</a>
        @endif
        <form method="POST" action="{{ route('logout') }}" class="pt-2 border-t border-slate-100"
              data-confirm="Weet je zeker dat je wilt uitloggen uit de TBS Kliniek?"
              data-confirm-title="Veilig Uitloggen"
              data-confirm-btn="Ja, Uitloggen"
              data-confirm-type="logout"
              data-confirm-icon="fa-right-from-bracket">
            @csrf
            <button type="submit" class="w-full text-left py-2 px-3 rounded-lg font-bold text-rose-600 hover:bg-rose-50 cursor-pointer">Uitloggen</button>
        </form>
    </div>
    @endif

    <!-- MAIN APP WRAPPER -->
    <div class="flex-1 flex flex-col min-w-0">
        @if(isset($character))
        <!-- TOP MODERN WHITE HEADER BAR -->
        <header class="bg-white border-b border-slate-200/90 sticky top-0 z-30 shadow-xs">
            <div class="w-full px-4 sm:px-6 lg:px-8 xl:px-10 h-18 flex items-center justify-between gap-4 sm:gap-6">
                
                <!-- Left: Player Profile summary -->
                <div class="flex items-center gap-3 sm:gap-4 shrink-0">
                    <a href="{{ route('profile.me') }}" class="relative group">
                        <img src="{{ $character->avatar_url }}" alt="{{ $character->name }}" class="w-11 h-11 rounded-full object-cover border-2 border-slate-200 shadow-xs group-hover:scale-105 transition-transform bg-slate-100">
                        <span class="absolute -bottom-1 -right-1 bg-slate-900 text-white font-black text-[9px] px-1.5 py-0.5 rounded-full border border-white">
                            {{ $character->level }}
                        </span>
                    </a>
                    <div>
                        <div class="flex items-center gap-2">
                            <a href="{{ route('profile.me') }}" class="font-extrabold text-slate-900 text-sm hover:text-indigo-600 transition">
                                {{ $character->name }}
                            </a>
                            <span class="text-[11px] font-bold text-slate-400">Level {{ $character->level }}</span>
                        </div>
                        @php
                            $lvlService = app(\App\Services\LevelService::class);
                            $reqXp = $lvlService->getXpRequiredForNextLevel($character->level);
                            $pctXp = min(100, max(0, round(($character->xp / max(1, $reqXp)) * 100)));
                        @endphp
                        <div class="flex items-center gap-2 mt-0.5">
                            <div class="w-24 sm:w-32 bg-slate-100 rounded-full h-2 overflow-hidden border border-slate-200">
                                <div class="bg-indigo-600 h-full rounded-full transition-all duration-300" style="width: {{ $pctXp }}%"></div>
                            </div>
                            <span class="text-[10px] font-bold text-slate-400">{{ number_format($character->xp, 0, ',', '.') }} / {{ number_format($reqXp, 0, ',', '.') }} XP</span>
                        </div>
                    </div>
                </div>

                <!-- Right: Stat badges (Cash, Bank, Respect, Stars, VIP, Energy, Health) -->
                <div class="flex items-center gap-2.5 lg:gap-3.5 text-xs font-semibold overflow-x-auto py-1 scrollbar-none">
                    <!-- Cash -->
                    <div class="flex items-center gap-2 bg-slate-50 border border-slate-200 px-3 py-1.5 rounded-xl shadow-2xs shrink-0">
                        <span class="w-6 h-6 rounded-lg bg-emerald-500 text-white flex items-center justify-center text-xs font-black shadow-xs">
                            <i class="fa-solid fa-euro-sign"></i>
                        </span>
                        <div>
                            <span class="text-[9px] uppercase font-bold text-slate-400 block leading-none">Contant</span>
                            <span class="font-extrabold text-slate-900 text-xs">€{{ number_format($character->cash, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <!-- Bank -->
                    <div class="flex items-center gap-2 bg-slate-50 border border-slate-200 px-3 py-1.5 rounded-xl shadow-2xs shrink-0">
                        <span class="w-6 h-6 rounded-lg bg-indigo-600 text-white flex items-center justify-center text-xs font-bold shadow-xs">
                            <i class="fa-solid fa-building-columns"></i>
                        </span>
                        <div>
                            <span class="text-[9px] uppercase font-bold text-slate-400 block leading-none">Bank</span>
                            <span class="font-extrabold text-slate-900 text-xs">€{{ number_format($character->bank, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <!-- Respect -->
                    <div class="flex items-center gap-2 bg-slate-50 border border-slate-200 px-3 py-1.5 rounded-xl shadow-2xs shrink-0">
                        <span class="w-6 h-6 rounded-lg bg-purple-600 text-white flex items-center justify-center text-xs font-bold shadow-xs">
                            <i class="fa-solid fa-award"></i>
                        </span>
                        <div>
                            <span class="text-[9px] uppercase font-bold text-slate-400 block leading-none">Respect</span>
                            <span class="font-extrabold text-purple-700 text-xs">{{ number_format($character->respect, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <!-- Sterren (VIP Valuta) -->
                    <a href="{{ route('vip.index') }}" class="flex items-center gap-2 bg-amber-50/70 border border-amber-200 px-3 py-1.5 rounded-xl shadow-2xs shrink-0 hover:bg-amber-100/70 transition" title="Exclusieve Sterren Shop">
                        <span class="w-6 h-6 rounded-lg bg-amber-500 text-white flex items-center justify-center text-xs font-bold shadow-xs">
                            <i class="fa-solid fa-star"></i>
                        </span>
                        <div>
                            <span class="text-[9px] uppercase font-bold text-amber-600 block leading-none">Sterren</span>
                            <span class="font-extrabold text-amber-800 text-xs">{{ number_format($character->stars ?? 0, 0, ',', '.') }}</span>
                        </div>
                    </a>

                    <!-- VIP Punten -->
                    <a href="{{ route('vip.index') }}" class="flex items-center gap-2 bg-purple-50/80 border border-purple-200 px-3 py-1.5 rounded-xl shadow-2xs shrink-0 hover:bg-purple-100 transition" title="VIP Punten Shop & Discord Voordelen">
                        <span class="w-6 h-6 rounded-lg bg-gradient-to-br from-purple-600 to-indigo-700 text-white flex items-center justify-center text-xs font-bold shadow-xs">
                            <i class="fa-solid fa-crown text-[10px] text-amber-300"></i>
                        </span>
                        <div>
                            <div class="flex items-center gap-1 leading-none">
                                <span class="text-[9px] uppercase font-bold text-purple-600 block">VIP Punten</span>
                                @if($character->isVip())
                                    <span class="text-[8px] bg-amber-400 text-slate-950 font-black px-1 rounded-sm">VIP</span>
                                @endif
                            </div>
                            <span class="font-extrabold text-purple-900 text-xs font-mono">{{ number_format($character->vip_points ?? 0, 0, ',', '.') }}</span>
                        </div>
                    </a>

                    <!-- Energie -->
                    <div class="flex items-center gap-2 bg-slate-50 border border-slate-200 px-3 py-1.5 rounded-xl shadow-2xs shrink-0">
                        <span class="w-6 h-6 rounded-lg bg-emerald-500 text-white flex items-center justify-center text-xs font-bold shadow-xs">
                            <i class="fa-solid fa-bolt"></i>
                        </span>
                        <div>
                            <div class="flex items-center justify-between gap-2">
                                <span class="text-[9px] uppercase font-bold text-slate-400 leading-none">Energie</span>
                                <span class="font-extrabold text-emerald-700 text-[11px]">{{ $character->energy }}/{{ $character->max_energy }}</span>
                            </div>
                            <div class="w-18 bg-slate-200 rounded-full h-1.5 overflow-hidden mt-0.5">
                                <div class="bg-emerald-500 h-full rounded-full" style="width: {{ min(100, max(0, round(($character->energy / max(1, $character->max_energy)) * 100))) }}%"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Gezondheid -->
                    <div class="flex items-center gap-2 bg-slate-50 border border-slate-200 px-3 py-1.5 rounded-xl shadow-2xs shrink-0">
                        <span class="w-6 h-6 rounded-lg bg-rose-500 text-white flex items-center justify-center text-xs font-bold shadow-xs">
                            <i class="fa-solid fa-heart-pulse"></i>
                        </span>
                        <div>
                            <div class="flex items-center justify-between gap-2">
                                <span class="text-[9px] uppercase font-bold text-slate-400 leading-none">Gezondheid</span>
                                <span class="font-extrabold text-rose-700 text-[11px]">{{ $character->health }}/{{ $character->max_health }}</span>
                            </div>
                            <div class="w-18 bg-slate-200 rounded-full h-1.5 overflow-hidden mt-0.5">
                                <div class="bg-rose-500 h-full rounded-full" style="width: {{ min(100, max(0, round(($character->health / max(1, $character->max_health)) * 100))) }}%"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Icons on Far Right -->
                    <div class="flex items-center gap-1 pl-2 border-l border-slate-200 text-slate-400">
                        <a href="{{ route('profile.me') }}" class="w-8 h-8 rounded-lg hover:bg-slate-100 flex items-center justify-center text-slate-500 hover:text-slate-900 transition" title="Mijn Profiel">
                            <i class="fa-solid fa-user"></i>
                        </a>
                    </div>
                </div>
            </div>
        </header>
        @else
        <!-- TOP MODERN GUEST HEADER BAR -->
        <header class="bg-white/95 backdrop-blur-md border-b border-slate-200 sticky top-0 z-40 shadow-xs">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-18 flex items-center justify-between gap-4">
                <a href="{{ route('login') }}" data-turbo="false" data-turbo-prefetch="false" class="flex items-center gap-3 group">
                    <div class="w-11 h-11 shrink-0 group-hover:scale-105 transition-transform flex items-center justify-center">
                        <img src="{{ asset('assets/logo.png') }}" alt="TBS Kliniek" class="w-full h-full object-contain drop-shadow-md">
                    </div>
                    <div>
                        <span class="block text-base font-extrabold tracking-tight text-slate-900 uppercase leading-none">
                            TBS <span class="text-indigo-600">KLINIEK</span>
                        </span>
                    </div>
                </a>

                <div class="flex items-center gap-3">
                    <a href="https://discord.gg" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 bg-[#5865F2] hover:bg-[#4752C4] text-white text-xs sm:text-sm font-extrabold px-3.5 sm:px-4 py-2 rounded-xl transition shadow-xs hover:shadow-sm active:scale-[0.98]">
                        <i class="fa-brands fa-discord text-sm"></i>
                        <span>Word lid van onze Discord</span>
                    </a>
                </div>
            </div>
        </header>
        @endif

        <!-- MAIN CONTENT CONTAINER (pt-8 zorgt ervoor dat pagina content NOOIT meer tegen de bovenbalk plakt) -->
        <main class="flex-1 w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-8 pb-20">
            <!-- Active Prison / Isoleercel Alert Banner with Live Seconds Ticker & Star Buyout -->
            @if(isset($character) && $character->isImprisoned() && !request()->routeIs('prison'))
            <div x-data="{ sec: {{ $character->remainingPrisonSeconds() }} }" 
                 x-init="if (sec > 0) { const timer = setInterval(() => { sec--; if (sec <= 0) { clearInterval(timer); window.location.reload(); } }, 1000); }" 
                 class="mb-6 bg-gradient-to-r from-purple-950 via-[#230438] to-purple-950 border-2 border-pink-500/80 p-4 rounded-2xl shadow-xl flex flex-col sm:flex-row items-center justify-between gap-4 text-white">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-pink-500/20 border border-pink-500/40 text-pink-400 flex items-center justify-center text-xl shrink-0 animate-pulse">
                        <i class="fa-solid fa-lock"></i>
                    </div>
                    <div>
                        <p class="text-xs font-black text-rose-300 uppercase tracking-wider">🔒 Vast in de isoleercel</p>
                        <p class="text-xs text-purple-200 mt-0.5">
                            Resterende straftijd: <strong class="text-white font-mono" x-text="sec + ' sec vast'">{{ $character->remainingPrisonSeconds() }} sec vast</strong>.
                            Reden: <span class="text-pink-200 font-semibold">{{ $character->prison_reason ?? 'Wangedrag in de kliniek.' }}</span>
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-2 w-full sm:w-auto">
                    <form action="{{ route('prison.buyout') }}" method="POST" class="w-full sm:w-auto">
                        @csrf
                        <button type="submit" 
                                class="w-full sm:w-auto px-4 py-2 rounded-xl bg-amber-500 hover:bg-amber-600 active:scale-95 text-white font-black text-xs uppercase tracking-wider transition shadow-sm cursor-pointer flex items-center justify-center gap-1.5">
                            <i class="fa-solid fa-star text-amber-200"></i>
                            <span>Koop Vrij (2 ⭐)</span>
                        </button>
                    </form>
                    <a href="{{ route('prison') }}" class="px-3 py-2 rounded-xl bg-purple-900/60 hover:bg-purple-800 border border-pink-500/30 text-pink-200 font-bold text-xs transition text-center shrink-0">
                        Gevangenis
                    </a>
                </div>
            </div>
            @endif

            <!-- Flash messages -->
            @if(session('success'))
            <div class="mb-6 bg-emerald-50 border border-emerald-200 p-4 rounded-xl shadow-xs flex items-start gap-3">
                <i class="fa-solid fa-circle-check text-emerald-600 text-lg mt-0.5"></i>
                <div>
                    <p class="text-xs font-extrabold text-emerald-900">Actie Geslaagd!</p>
                    <p class="text-xs text-emerald-800 mt-0.5">{{ session('success') }}</p>
                </div>
            </div>
            @endif

            @if(session('error'))
            <div class="mb-6 bg-rose-50 border border-rose-200 p-4 rounded-xl shadow-xs flex items-start gap-3">
                <i class="fa-solid fa-triangle-exclamation text-rose-600 text-lg mt-0.5"></i>
                <div>
                    <p class="text-xs font-extrabold text-rose-900">Let op / Tegenwerking!</p>
                    <p class="text-xs text-rose-800 mt-0.5">{{ session('error') }}</p>
                </div>
            </div>
            @endif

            @yield('content')
        </main>

        <!-- SITE-WIDE FOOTER (Altijd zichtbaar, schoon en professioneel) -->
        <footer class="mt-auto border-t border-slate-200 bg-white py-6 text-center text-xs text-slate-500">
            <div class="max-w-7xl mx-auto px-4 flex flex-col sm:flex-row items-center justify-between gap-3">
                <div class="flex items-center gap-2.5">
                    <img src="{{ asset('assets/logo.png') }}" alt="TBS Kliniek" class="w-7 h-7 object-contain drop-shadow-xs">
                    <span class="font-extrabold text-slate-800 text-sm">TBS <span class="text-purple-600">Kliniek</span></span>
                </div>
                <div>
                    <span class="font-medium text-slate-600">Overleef de afdeling, leid je bende en heers over de kliniek.</span>
                </div>
                <div class="text-[11px] text-slate-400">
                    &copy; {{ date('Y') }} TBS Kliniek. Alle rechten voorbehouden.
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
                        btn = '⚔️ Ja, Aanvallen!';
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
    <div x-data="{
        isOpen: false,
        inputQuery: '',
        messages: [
            {
                from: 'bot',
                text: 'Hoi patiënt! 🏥 Ik ben de TBS Zaalwacht AI. Heb je vragen over het spel, VIP worden, gevechten, de kluis of je account? Stel gerust je vraag of klik op een snel onderwerp hieronder!',
                links: []
            }
        ],
        faqKnowledge: [
            {
                keywords: ['vip', 'doneren', 'kopen', 'discord', 'ticket', '7,50'],
                title: 'VIP Lidmaatschap (€7,50 pm)',
                answer: 'Word lid van onze officiële Discord via discord.gg/tbs en maak een ticket aan voor VIP (€7,50 per maand). Elke actieve VIP ontvangt automatisch 5 VIP Punten per uur! VIP punten kun je inwisselen in de VIP Shop.',
                links: [{ text: 'Naar VIP Shop', url: '{{ route('vip.index') }}' }]
            },
            {
                keywords: ['vechten', 'aanval', 'aanvallen', 'luchtplaats', '100', 'arena'],
                title: 'Luchtplaats & Aanvallen Limiet',
                answer: 'Je mag maximaal 100 aanvallen per dag uitvoeren en maximaal 100 keer per dag hetzelfde doelwit aanvallen. Elk gevecht brengt je naar het interactieve 5-seconden Arena Scherm!',
                links: [{ text: 'Naar de Luchtplaats', url: '{{ route('fight.index') }}' }]
            },
            {
                keywords: ['kluis', 'kraken', 'code', 'jammer', 'hint'],
                title: 'Kluis-Kraak & Codes',
                answer: 'De centrale kluis bevat een 4-cijferige geheime code. Raad de code met behulp van hints (te hoog / te laag). Let op: na 3 fouten gaat het alarm af tenzij je een Alarm Jammer hebt.',
                links: [{ text: 'Kluis Kraken', url: '{{ route('vault.index') }}' }]
            },
            {
                keywords: ['wachtwoord', 'vergeten', 'kwijt', 'reset'],
                title: 'Wachtwoord Vergeten',
                answer: 'Ben je je wachtwoord vergeten? Ga naar de wachtwoord vergeten pagina en vul je e-mailadres in. Je ontvangt direct een veilige reset-link in je inbox.',
                links: [{ text: 'Wachtwoord Resetten', url: '{{ route('password.request') }}' }]
            },
            {
                keywords: ['naam', 'username', 'gebruikersnaam', '6 maanden'],
                title: 'Gebruikersnaam Wijzigen',
                answer: 'Je mag je gebruikersnaam maximaal 1 keer per 6 maanden aanpassen onder Account Instellingen (/instellingen/account). Dit voorkomt identiteitsfraude in de kliniek.',
                links: [{ text: 'Account Instellingen', url: '{{ route('settings.account') }}' }]
            },
            {
                keywords: ['2fa', 'sessies', 'uitloggen', 'apparaat', 'afstand'],
                title: 'Beveiliging & Remote Sessies',
                answer: 'Onder Account Instellingen kun je 2FA inschakelen en alle actieve apparaten bekijken. Met je wachtwoord kun je alle andere apparaten direct op afstand uitloggen.',
                links: [{ text: 'Sessies Beheren', url: '{{ route('settings.account') }}' }]
            },
            {
                keywords: ['isoleercel', 'gevangenis', 'ontsnappen', 'straf', 'vast'],
                title: 'Isoleercel & Ontsnappen',
                answer: 'Als je gepakt wordt bij een misdrijf beland je in de isoleercel. Wacht je straftijd af, vraag een ganggenoot je celdeur te forceren of koop jezelf vrij voor 2 Sterren.',
                links: [{ text: 'Naar Gevangenis', url: '{{ route('prison') }}' }]
            },
            {
                keywords: ['gang', 'bende', 'uurloon', 'loon', 'doneren'],
                title: 'Gangs & Bende Uurloon',
                answer: 'Leden van een gang kunnen elk uur hun gang-uurloon claimen vanuit de gezamenlijke bende-kluis!',
                links: [{ text: 'Naar Gangs', url: '{{ route('gangs.index') }}' }]
            }
        ],
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

            // Match answer
            setTimeout(() => {
                const qLower = query.toLowerCase();
                let matched = this.faqKnowledge.find(item => 
                    item.keywords.some(k => qLower.includes(k)) || 
                    qLower.includes(item.title.toLowerCase())
                );

                if (matched) {
                    this.messages.push({
                        from: 'bot',
                        text: matched.title + ':\n' + matched.answer,
                        links: matched.links || []
                    });
                } else {
                    this.messages.push({
                        from: 'bot',
                        text: 'Ik kon geen specifiek antwoord vinden op jouw vraag. Bekijk onze volledige Kennisbank & FAQ pagina met alle speluitleg!',
                        links: [{ text: 'Volledige FAQ Bekijken', url: '{{ route('faq') }}' }]
                    });
                }

                this.$nextTick(() => {
                    const el = this.$refs.chatBox;
                    if (el) el.scrollTop = el.scrollHeight;
                });
            }, 300);
        }
    }" class="fixed bottom-5 right-5 z-50">

        <!-- CHAT WINDOW POPUP (Website Style: Clean Light Card with Royal Purple Accents) -->
        <div x-show="isOpen" x-cloak
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-6 scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 scale-100"
             x-transition:leave-end="opacity-0 translate-y-6 scale-95"
             class="fixed bottom-20 right-5 w-[90vw] sm:w-96 max-h-[520px] bg-white border border-slate-200/90 rounded-2xl shadow-2xl flex flex-col overflow-hidden text-slate-800 z-50">
            
            <!-- Header -->
            <div class="bg-gradient-to-r from-purple-600 via-indigo-600 to-purple-700 p-3.5 border-b border-purple-500/30 flex items-center justify-between text-white shadow-xs">
                <div class="flex items-center gap-3">
                    <div class="relative">
                        <div class="w-9 h-9 rounded-xl bg-white/20 border border-white/30 backdrop-blur-xs flex items-center justify-center text-sm shadow-xs">
                            <i class="fa-solid fa-robot text-white"></i>
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
                        <div :class="msg.from === 'bot' ? 'bg-white border border-slate-200 text-slate-800' : 'bg-gradient-to-r from-purple-600 to-indigo-600 text-white font-medium'"
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

                <!-- Quick Suggested Topic Pills -->
                <div class="pt-2">
                    <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400 block mb-1.5">Snel Onderwerp Kiezen:</span>
                    <div class="flex flex-wrap gap-1.5">
                        <button type="button" @click="sendQuery('Hoe word ik VIP?')" class="px-2.5 py-1 rounded-full bg-white hover:bg-purple-600 hover:text-white hover:border-purple-600 border border-slate-200 text-slate-700 text-[10px] font-bold transition cursor-pointer shadow-2xs">
                            👑 VIP Lidmaatschap
                        </button>
                        <button type="button" @click="sendQuery('Hoeveel aanvallen mag ik doen?')" class="px-2.5 py-1 rounded-full bg-white hover:bg-purple-600 hover:text-white hover:border-purple-600 border border-slate-200 text-slate-700 text-[10px] font-bold transition cursor-pointer shadow-2xs">
                            ⚔️ 100 Aanvallen Limiet
                        </button>
                        <button type="button" @click="sendQuery('Hoe kraak ik de kluis?')" class="px-2.5 py-1 rounded-full bg-white hover:bg-purple-600 hover:text-white hover:border-purple-600 border border-slate-200 text-slate-700 text-[10px] font-bold transition cursor-pointer shadow-2xs">
                            🔑 Kluis Kraak
                        </button>
                        <button type="button" @click="sendQuery('Wachtwoord vergeten')" class="px-2.5 py-1 rounded-full bg-white hover:bg-purple-600 hover:text-white hover:border-purple-600 border border-slate-200 text-slate-700 text-[10px] font-bold transition cursor-pointer shadow-2xs">
                            🔒 Wachtwoord Vergeten
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
                class="px-4 py-3 rounded-2xl bg-gradient-to-r from-purple-700 to-indigo-600 hover:from-purple-600 hover:to-indigo-500 text-white font-black text-xs shadow-xl shadow-purple-900/40 flex items-center gap-2.5 cursor-pointer transition transform hover:scale-105 active:scale-95 border border-purple-400/30">
            <div class="relative">
                <i class="fa-solid fa-comments text-base"></i>
                <span class="absolute -top-1 -right-1 w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
            </div>
            <span class="tracking-wide">Kliniek Helpbot</span>
        </button>
    </div>
    @endauth
</body>
</html>
