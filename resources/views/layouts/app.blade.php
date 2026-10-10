<!DOCTYPE html>
<html lang="nl" class="h-full bg-[#f0f3f8]">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TBS Kliniek - Respect verdien je. Frikandelbroodjes moet je bevechten.</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full flex flex-col lg:flex-row bg-[#f0f3f8] text-slate-800 antialiased selection:bg-purple-900 selection:text-white" x-data="{ mobileMenuOpen: false }">

    @if(isset($character))
    <!-- SIDEBAR NAVIGATION (Desktop & Tablet) -->
    <aside class="hidden lg:flex lg:flex-col w-64 bg-[#1e0b38] border-r border-purple-950/60 shrink-0 min-h-screen text-slate-200 z-40 sticky top-0 h-screen overflow-y-auto no-scrollbar shadow-2xl">
        <!-- Logo Header -->
        <div class="p-5 flex items-center justify-between border-b border-purple-900/40">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3 group">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-purple-800 to-amber-500 p-0.5 shadow-md group-hover:scale-105 transition-transform">
                    <div class="w-full h-full bg-[#1e0b38] rounded-[10px] flex items-center justify-center">
                        <i class="fa-solid fa-crown text-amber-400 text-lg"></i>
                    </div>
                </div>
                <div>
                    <span class="block text-xl font-black tracking-tight text-white uppercase drop-shadow leading-none">
                        TBS <span class="text-amber-400">KLINIEK</span>
                    </span>
                    <span class="text-[9px] font-bold uppercase tracking-widest text-purple-300/80">Paviljoen 7</span>
                </div>
            </a>
        </div>

        <!-- Navigation Links -->
        <nav class="flex-1 px-3 py-4 space-y-1 text-[13px] font-semibold">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3.5 px-3.5 py-2.5 rounded-xl transition-all {{ request()->routeIs('dashboard') ? 'bg-[#6d28d9] text-white font-bold shadow-lg shadow-purple-950/50' : 'text-purple-200/80 hover:text-white hover:bg-purple-900/40' }}">
                <span class="w-5 text-center text-sm"><i class="fa-solid fa-house"></i></span>
                <span>Dashboard</span>
            </a>

            <a href="{{ route('missions.index') }}" class="flex items-center gap-3.5 px-3.5 py-2.5 rounded-xl transition-all {{ request()->routeIs('missions.*') ? 'bg-[#6d28d9] text-white font-bold shadow-lg shadow-purple-950/50' : 'text-purple-200/80 hover:text-white hover:bg-purple-900/40' }}">
                <span class="w-5 text-center text-sm"><i class="fa-solid fa-bullseye"></i></span>
                <span>Missies</span>
            </a>

            <a href="{{ route('training.index') }}" class="flex items-center gap-3.5 px-3.5 py-2.5 rounded-xl transition-all {{ request()->routeIs('training.*') ? 'bg-[#6d28d9] text-white font-bold shadow-lg shadow-purple-950/50' : 'text-purple-200/80 hover:text-white hover:bg-purple-900/40' }}">
                <span class="w-5 text-center text-sm"><i class="fa-solid fa-dumbbell"></i></span>
                <span>Training</span>
            </a>

            <a href="{{ route('shop.index') }}" class="flex items-center gap-3.5 px-3.5 py-2.5 rounded-xl transition-all {{ request()->routeIs('shop.*') ? 'bg-[#6d28d9] text-white font-bold shadow-lg shadow-purple-950/50' : 'text-purple-200/80 hover:text-white hover:bg-purple-900/40' }}">
                <span class="w-5 text-center text-sm"><i class="fa-solid fa-cart-shopping"></i></span>
                <span>Winkel</span>
            </a>

            <a href="{{ route('theft.index') }}" class="flex items-center gap-3.5 px-3.5 py-2.5 rounded-xl transition-all {{ request()->routeIs('theft.*') ? 'bg-[#6d28d9] text-white font-bold shadow-lg shadow-purple-950/50' : 'text-purple-200/80 hover:text-white hover:bg-purple-900/40' }}">
                <span class="w-5 text-center text-sm"><i class="fa-solid fa-mask"></i></span>
                <span>Diefstal</span>
            </a>

            <a href="{{ route('bank.index') }}" class="flex items-center gap-3.5 px-3.5 py-2.5 rounded-xl transition-all {{ request()->routeIs('bank.*') ? 'bg-[#6d28d9] text-white font-bold shadow-lg shadow-purple-950/50' : 'text-purple-200/80 hover:text-white hover:bg-purple-900/40' }}">
                <span class="w-5 text-center text-sm"><i class="fa-solid fa-building-columns"></i></span>
                <span>De Bank</span>
            </a>

            <a href="{{ route('prison') }}" class="flex items-center gap-3.5 px-3.5 py-2.5 rounded-xl transition-all {{ request()->routeIs('prison') ? 'bg-[#6d28d9] text-white font-bold shadow-lg shadow-purple-950/50' : 'text-purple-200/80 hover:text-white hover:bg-purple-900/40' }}">
                <span class="w-5 text-center text-sm"><i class="fa-solid fa-lock"></i></span>
                <span>Gevangenis</span>
            </a>

            <a href="{{ route('gangs.index') }}" class="flex items-center gap-3.5 px-3.5 py-2.5 rounded-xl transition-all {{ request()->routeIs('gangs.*') ? 'bg-[#6d28d9] text-white font-bold shadow-lg shadow-purple-950/50' : 'text-purple-200/80 hover:text-white hover:bg-purple-900/40' }}">
                <span class="w-5 text-center text-sm"><i class="fa-solid fa-users"></i></span>
                <span>Gangs</span>
            </a>

            <a href="{{ route('profile.me') }}" class="flex items-center gap-3.5 px-3.5 py-2.5 rounded-xl transition-all {{ request()->routeIs('profile.*') ? 'bg-[#6d28d9] text-white font-bold shadow-lg shadow-purple-950/50' : 'text-purple-200/80 hover:text-white hover:bg-purple-900/40' }}">
                <span class="w-5 text-center text-sm"><i class="fa-solid fa-user"></i></span>
                <span>Profiel</span>
            </a>

            <a href="{{ route('profile.show', $character->name) }}" class="flex items-center gap-3.5 px-3.5 py-2.5 rounded-xl transition-all text-purple-200/80 hover:text-white hover:bg-purple-900/40">
                <span class="w-5 text-center text-sm"><i class="fa-solid fa-book-open"></i></span>
                <span>Gastenboek</span>
            </a>

            <a href="{{ route('leaderboard.index') }}" class="flex items-center gap-3.5 px-3.5 py-2.5 rounded-xl transition-all {{ request()->routeIs('leaderboard.*') ? 'bg-[#6d28d9] text-white font-bold shadow-lg shadow-purple-950/50' : 'text-purple-200/80 hover:text-white hover:bg-purple-900/40' }}">
                <span class="w-5 text-center text-sm"><i class="fa-solid fa-trophy"></i></span>
                <span>Ranglijsten</span>
            </a>
        </nav>

        <!-- Sidebar Footer / Logout -->
        <div class="p-4 border-t border-purple-900/40">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full flex items-center gap-2.5 px-3.5 py-2 rounded-xl text-xs font-bold text-purple-300 hover:text-rose-300 hover:bg-purple-900/40 transition">
                    <i class="fa-solid fa-right-from-bracket"></i>
                    <span>Uitloggen</span>
                </button>
            </form>
        </div>
    </aside>

    <!-- MOBILE TOP HEADER -->
    <div class="lg:hidden bg-[#1e0b38] text-white px-4 py-3 flex items-center justify-between border-b border-purple-900/60 sticky top-0 z-50">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-2">
            <i class="fa-solid fa-crown text-amber-400"></i>
            <span class="font-black text-base uppercase tracking-tight text-white">TBS <span class="text-amber-400">KLINIEK</span></span>
        </a>
        <div class="flex items-center gap-3">
            <span class="text-xs font-bold bg-purple-900/80 px-2 py-1 rounded text-amber-300">
                {{ number_format($character->cash, 0, ',', '.') }} KC
            </span>
            <button @click="mobileMenuOpen = !mobileMenuOpen" class="text-white p-1.5 focus:outline-none">
                <i class="fa-solid" :class="mobileMenuOpen ? 'fa-xmark' : 'fa-bars'"></i>
            </button>
        </div>
    </div>

    <!-- MOBILE ACCORDION MENU -->
    <div x-show="mobileMenuOpen" x-cloak class="lg:hidden bg-[#1e0b38] text-white px-4 py-3 space-y-1 text-xs border-b border-purple-900 z-50">
        <a href="{{ route('dashboard') }}" class="block py-2 px-3 rounded font-bold hover:bg-purple-900">Dashboard</a>
        <a href="{{ route('missions.index') }}" class="block py-2 px-3 rounded font-bold hover:bg-purple-900">Missies</a>
        <a href="{{ route('training.index') }}" class="block py-2 px-3 rounded font-bold hover:bg-purple-900">Training</a>
        <a href="{{ route('shop.index') }}" class="block py-2 px-3 rounded font-bold hover:bg-purple-900">Winkel</a>
        <a href="{{ route('theft.index') }}" class="block py-2 px-3 rounded font-bold hover:bg-purple-900">Diefstal</a>
        <a href="{{ route('bank.index') }}" class="block py-2 px-3 rounded font-bold hover:bg-purple-900">De Bank</a>
        <a href="{{ route('prison') }}" class="block py-2 px-3 rounded font-bold hover:bg-purple-900">Gevangenis</a>
        <a href="{{ route('gangs.index') }}" class="block py-2 px-3 rounded font-bold hover:bg-purple-900">Gangs</a>
        <a href="{{ route('profile.me') }}" class="block py-2 px-3 rounded font-bold hover:bg-purple-900">Profiel & Gastenboek</a>
        <a href="{{ route('leaderboard.index') }}" class="block py-2 px-3 rounded font-bold hover:bg-purple-900">Ranglijsten</a>
    </div>
    @endif

    <!-- MAIN APP WRAPPER -->
    <div class="flex-1 flex flex-col min-w-0">
        @if(isset($character))
        <!-- TOP MODERN WHITE HEADER BAR (Matches top-right of image) -->
        <header class="bg-white border-b border-slate-200/80 sticky top-0 z-30 shadow-xs">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-18 flex items-center justify-between gap-4">
                <!-- Left: Player Profile summary -->
                <div class="flex items-center gap-3 sm:gap-4 shrink-0">
                    <a href="{{ route('profile.me') }}" class="relative group">
                        <img src="{{ asset('assets/characters/henkie.jpg') }}" alt="{{ $character->name }}" class="w-11 h-11 rounded-full object-cover border-2 border-purple-600 shadow-sm group-hover:scale-105 transition-transform">
                        <span class="absolute -bottom-1 -right-1 bg-amber-500 text-purple-950 font-black text-[9px] px-1 rounded-full border border-white">
                            {{ $character->level }}
                        </span>
                    </a>
                    <div>
                        <div class="flex items-center gap-2">
                            <a href="{{ route('profile.me') }}" class="font-black text-slate-900 text-sm hover:text-purple-700 transition">
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
                                <div class="bg-gradient-to-r from-purple-600 to-indigo-500 h-full rounded-full" style="width: {{ $pctXp }}%"></div>
                            </div>
                            <span class="text-[10px] font-bold text-slate-400">{{ number_format($character->xp, 0, ',', '.') }} / {{ number_format($reqXp, 0, ',', '.') }} XP</span>
                        </div>
                    </div>
                </div>

                <!-- Right: Stat badges (Cash, Bank, Energy, Health) -->
                <div class="flex items-center gap-2 sm:gap-4 text-xs font-semibold overflow-x-auto py-1">
                    <!-- KliniekCash -->
                    <div class="flex items-center gap-2 bg-amber-50/80 border border-amber-200/80 px-3 py-1.5 rounded-xl shadow-2xs shrink-0">
                        <span class="w-6 h-6 rounded-lg bg-amber-400 text-purple-950 flex items-center justify-center text-xs font-black shadow-xs">
                            <i class="fa-solid fa-coins"></i>
                        </span>
                        <div>
                            <span class="text-[9px] uppercase font-bold text-slate-400 block leading-none">KliniekCash</span>
                            <span class="font-black text-slate-900 text-xs">{{ number_format($character->cash, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <!-- Banktegoed -->
                    <div class="flex items-center gap-2 bg-indigo-50/80 border border-indigo-200/80 px-3 py-1.5 rounded-xl shadow-2xs shrink-0">
                        <span class="w-6 h-6 rounded-lg bg-indigo-600 text-white flex items-center justify-center text-xs font-bold shadow-xs">
                            <i class="fa-solid fa-building-columns"></i>
                        </span>
                        <div>
                            <span class="text-[9px] uppercase font-bold text-slate-400 block leading-none">Banktegoed</span>
                            <span class="font-black text-slate-900 text-xs">{{ number_format($character->bank, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <!-- Energie -->
                    <div class="hidden md:flex items-center gap-2 bg-emerald-50/80 border border-emerald-200/80 px-3 py-1.5 rounded-xl shadow-2xs shrink-0">
                        <span class="w-6 h-6 rounded-lg bg-emerald-500 text-white flex items-center justify-center text-xs font-bold shadow-xs">
                            <i class="fa-solid fa-bolt"></i>
                        </span>
                        <div>
                            <div class="flex items-center justify-between gap-2">
                                <span class="text-[9px] uppercase font-bold text-slate-400 leading-none">Energie</span>
                                <span class="font-black text-emerald-800 text-[11px]">{{ $character->energy }}/{{ $character->max_energy }}</span>
                            </div>
                            <div class="w-16 bg-slate-200 rounded-full h-1.5 overflow-hidden mt-0.5">
                                <div class="bg-emerald-500 h-full rounded-full" style="width: {{ min(100, max(0, round(($character->energy / max(1, $character->max_energy)) * 100))) }}%"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Gezondheid -->
                    <div class="hidden md:flex items-center gap-2 bg-rose-50/80 border border-rose-200/80 px-3 py-1.5 rounded-xl shadow-2xs shrink-0">
                        <span class="w-6 h-6 rounded-lg bg-rose-500 text-white flex items-center justify-center text-xs font-bold shadow-xs">
                            <i class="fa-solid fa-heart-pulse"></i>
                        </span>
                        <div>
                            <div class="flex items-center justify-between gap-2">
                                <span class="text-[9px] uppercase font-bold text-slate-400 leading-none">Gezondheid</span>
                                <span class="font-black text-rose-800 text-[11px]">{{ $character->health }}/{{ $character->max_health }}</span>
                            </div>
                            <div class="w-16 bg-slate-200 rounded-full h-1.5 overflow-hidden mt-0.5">
                                <div class="bg-rose-500 h-full rounded-full" style="width: {{ min(100, max(0, round(($character->health / max(1, $character->max_health)) * 100))) }}%"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Icons on Far Right -->
                    <div class="flex items-center gap-1 pl-2 border-l border-slate-200 text-slate-400">
                        <a href="{{ route('profile.me') }}" class="w-8 h-8 rounded-lg hover:bg-slate-100 flex items-center justify-center text-slate-500 hover:text-purple-700 transition" title="Meldingen">
                            <i class="fa-solid fa-bell"></i>
                        </a>
                        <a href="{{ route('profile.show', $character->name) }}" class="w-8 h-8 rounded-lg hover:bg-slate-100 flex items-center justify-center text-slate-500 hover:text-purple-700 transition" title="Gastenboek">
                            <i class="fa-solid fa-envelope"></i>
                        </a>
                    </div>
                </div>
            </div>
        </header>
        @endif

        <!-- MAIN CONTENT CONTAINER -->
        <main class="flex-1 w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 pb-24 lg:pb-8">
            <!-- Flash messages -->
            @if(session('success'))
            <div class="mb-5 bg-emerald-50 border-l-4 border-emerald-500 p-4 rounded-xl shadow-xs flex items-start gap-3">
                <i class="fa-solid fa-circle-check text-emerald-600 text-lg mt-0.5"></i>
                <div>
                    <p class="text-sm font-bold text-emerald-900">Gelukt!</p>
                    <p class="text-xs text-emerald-800 mt-0.5">{{ session('success') }}</p>
                </div>
            </div>
            @endif

            @if(session('error'))
            <div class="mb-5 bg-rose-50 border-l-4 border-rose-500 p-4 rounded-xl shadow-xs flex items-start gap-3">
                <i class="fa-solid fa-triangle-exclamation text-rose-600 text-lg mt-0.5"></i>
                <div>
                    <p class="text-sm font-bold text-rose-900">Mislukt of tegengehouden!</p>
                    <p class="text-xs text-rose-800 mt-0.5">{{ session('error') }}</p>
                </div>
            </div>
            @endif

            @yield('content')
        </main>

        @if(isset($character))
        <!-- MOBILE BOTTOM DOCK NAV (Matches bottom-right mobile mockup) -->
        <nav class="lg:hidden fixed bottom-0 inset-x-0 bg-white border-t border-slate-200 z-50 flex items-center justify-around py-2 px-3 shadow-lg">
            <a href="{{ route('dashboard') }}" class="flex flex-col items-center gap-1 text-[11px] font-bold {{ request()->routeIs('dashboard') ? 'text-purple-700' : 'text-slate-400' }}">
                <i class="fa-solid fa-house text-base"></i>
                <span>Home</span>
            </a>
            <a href="{{ route('missions.index') }}" class="flex flex-col items-center gap-1 text-[11px] font-bold {{ request()->routeIs('missions.*') ? 'text-purple-700' : 'text-slate-400' }}">
                <i class="fa-solid fa-bullseye text-base"></i>
                <span>Missies</span>
            </a>
            <a href="{{ route('shop.index') }}" class="flex flex-col items-center gap-1 text-[11px] font-bold {{ request()->routeIs('shop.*') ? 'text-purple-700' : 'text-slate-400' }}">
                <i class="fa-solid fa-cart-shopping text-base"></i>
                <span>Winkel</span>
            </a>
            <a href="{{ route('gangs.index') }}" class="flex flex-col items-center gap-1 text-[11px] font-bold {{ request()->routeIs('gangs.*') ? 'text-purple-700' : 'text-slate-400' }}">
                <i class="fa-solid fa-users text-base"></i>
                <span>Gangs</span>
            </a>
            <a href="{{ route('profile.me') }}" class="flex flex-col items-center gap-1 text-[11px] font-bold {{ request()->routeIs('profile.*') ? 'text-purple-700' : 'text-slate-400' }}">
                <i class="fa-solid fa-user text-base"></i>
                <span>Profiel</span>
            </a>
        </nav>
        @endif
    </div>
</body>
</html>
