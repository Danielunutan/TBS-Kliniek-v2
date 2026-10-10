<!DOCTYPE html>
<html lang="nl" class="h-full bg-slate-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TBS Kliniek - Respect verdien je. Frikandelbroodjes moet je bevechten.</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full flex flex-col bg-slate-100 text-slate-800 antialiased selection:bg-purple-900 selection:text-white">
    <!-- Top Bar: Game Brand & Auth Header -->
    <header class="bg-[#241142] text-white border-b-2 border-purple-900 shadow-md sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-14">
                <!-- Brand Title -->
                <div class="flex items-center gap-3">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-2 group">
                        <span class="inline-flex items-center justify-center w-8 h-8 rounded bg-purple-700 text-amber-300 font-black shadow-inner">
                            <i class="fa-solid fa-hospital text-sm"></i>
                        </span>
                        <div>
                            <span class="text-lg font-black tracking-wider uppercase text-amber-400 group-hover:text-amber-300 transition-colors">TBS Kliniek</span>
                            <span class="hidden sm:inline-block text-[10px] uppercase font-bold tracking-widest text-purple-300 ml-1.5 px-1.5 py-0.5 bg-purple-950/70 rounded border border-purple-800">Paviljoen 7</span>
                        </div>
                    </a>
                </div>

                @if(isset($character))
                <!-- Character Quick Stats in Header -->
                <div class="flex items-center gap-3 sm:gap-6 text-xs font-semibold">
                    <!-- Cash -->
                    <div class="flex items-center gap-1.5 bg-purple-950/80 px-2.5 py-1 rounded border border-purple-800/80">
                        <i class="fa-solid fa-coins text-amber-400"></i>
                        <span><strong class="text-amber-300">{{ number_format($character->cash, 0, ',', '.') }}</strong> <span class="text-purple-300">KC</span></span>
                    </div>

                    <!-- Bank -->
                    <div class="hidden sm:flex items-center gap-1.5 bg-purple-950/80 px-2.5 py-1 rounded border border-purple-800/80">
                        <i class="fa-solid fa-building-columns text-emerald-400"></i>
                        <span><strong class="text-emerald-300">{{ number_format($character->bank, 0, ',', '.') }}</strong> <span class="text-purple-300">KC</span></span>
                    </div>

                    <!-- Energy -->
                    <div class="flex items-center gap-1.5 bg-purple-950/80 px-2.5 py-1 rounded border border-purple-800/80">
                        <i class="fa-solid fa-bolt text-yellow-400"></i>
                        <span><strong>{{ $character->energy }}</strong><span class="text-purple-400">/{{ $character->max_energy }}</span></span>
                    </div>

                    <!-- User Actions -->
                    <div class="flex items-center gap-2 border-l border-purple-800 pl-3">
                        <a href="{{ route('profile.me') }}" class="text-purple-200 hover:text-white font-bold flex items-center gap-1.5 py-1 px-2 rounded hover:bg-purple-800/50 transition">
                            <i class="fa-solid fa-user-circle"></i>
                            <span class="hidden md:inline">{{ $character->name }}</span>
                        </a>
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" title="Uitloggen" class="text-purple-300 hover:text-red-400 p-1.5 rounded hover:bg-purple-800/40 transition">
                                <i class="fa-solid fa-right-from-bracket"></i>
                            </button>
                        </form>
                    </div>
                </div>
                @endif
            </div>
        </div>

        @if(isset($character))
        <!-- Secondary Player Status Bar -->
        <div class="bg-[#190a30] border-t border-purple-950/80 px-4 py-2 text-xs">
            <div class="max-w-7xl mx-auto flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-4 flex-wrap">
                    <!-- Level & XP Bar -->
                    <div class="flex items-center gap-2">
                        <span class="bg-amber-500 text-purple-950 font-black px-1.5 py-0.5 rounded text-[11px]">LVL {{ $character->level }}</span>
                        @php
                            $lvlService = app(\App\Services\LevelService::class);
                            $reqXp = $lvlService->getXpRequiredForNextLevel($character->level);
                            $pctXp = min(100, max(0, round(($character->xp / max(1, $reqXp)) * 100)));
                        @endphp
                        <div class="w-28 sm:w-40 bg-purple-950 rounded-full h-3.5 border border-purple-800/60 overflow-hidden relative" title="XP: {{ $character->xp }} / {{ $reqXp }}">
                            <div class="bg-gradient-to-r from-amber-500 to-yellow-300 h-full transition-all duration-300" style="width: {{ $pctXp }}%"></div>
                            <span class="absolute inset-0 flex items-center justify-center text-[10px] font-bold text-white drop-shadow">{{ $pctXp }}% XP</span>
                        </div>
                    </div>

                    <!-- Health Bar -->
                    <div class="flex items-center gap-1.5">
                        <i class="fa-solid fa-heart-pulse text-rose-500"></i>
                        <span class="text-slate-300 font-medium">Gezondheid:</span>
                        <div class="w-20 sm:w-28 bg-purple-950 rounded-full h-3 border border-purple-800/60 overflow-hidden">
                            <div class="bg-rose-500 h-full" style="width: {{ min(100, max(0, round(($character->health / max(1, $character->max_health)) * 100))) }}%"></div>
                        </div>
                        <span class="font-bold text-rose-300 text-[11px]">{{ $character->health }}/{{ $character->max_health }}</span>
                    </div>

                    <!-- Respect -->
                    <div class="flex items-center gap-1.5">
                        <i class="fa-solid fa-shield text-indigo-400"></i>
                        <span class="text-slate-300">Respect:</span>
                        <span class="font-bold text-indigo-300">{{ $character->respect }}</span>
                    </div>
                </div>

                @if($character->isImprisoned())
                <div class="flex items-center gap-2 bg-red-950/90 text-red-300 border border-red-700 px-3 py-1 rounded font-bold animate-pulse">
                    <i class="fa-solid fa-lock"></i>
                    <span>Isoleercel: nog {{ $character->remainingPrisonMinutes() }} min</span>
                    <a href="{{ route('prison') }}" class="underline hover:text-white ml-1">Bekijk cellenblok</a>
                </div>
                @endif
            </div>
        </div>
        @endif
    </header>

    <!-- Main Navigation Bar -->
    @if(isset($character))
    <nav class="bg-white border-b border-slate-200 shadow-sm sticky top-14 z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-start gap-1 overflow-x-auto py-2 text-xs font-bold text-slate-600 no-scrollbar">
                <a href="{{ route('dashboard') }}" class="px-3 py-2 rounded flex items-center gap-1.5 transition-colors {{ request()->routeIs('dashboard') ? 'bg-purple-900 text-white' : 'hover:bg-purple-50 hover:text-purple-900' }}">
                    <i class="fa-solid fa-house"></i>
                    <span>Hoofdkwartier</span>
                </a>
                <a href="{{ route('missions.index') }}" class="px-3 py-2 rounded flex items-center gap-1.5 transition-colors {{ request()->routeIs('missions.*') ? 'bg-purple-900 text-white' : 'hover:bg-purple-50 hover:text-purple-900' }}">
                    <i class="fa-solid fa-bullseye"></i>
                    <span>Missies</span>
                </a>
                <a href="{{ route('training.index') }}" class="px-3 py-2 rounded flex items-center gap-1.5 transition-colors {{ request()->routeIs('training.*') ? 'bg-purple-900 text-white' : 'hover:bg-purple-50 hover:text-purple-900' }}">
                    <i class="fa-solid fa-dumbbell"></i>
                    <span>Gym & Training</span>
                </a>
                <a href="{{ route('theft.index') }}" class="px-3 py-2 rounded flex items-center gap-1.5 transition-colors {{ request()->routeIs('theft.*') ? 'bg-purple-900 text-white' : 'hover:bg-purple-50 hover:text-purple-900' }}">
                    <i class="fa-solid fa-mask"></i>
                    <span>Diefstal & Misdaad</span>
                </a>
                <a href="{{ route('bank.index') }}" class="px-3 py-2 rounded flex items-center gap-1.5 transition-colors {{ request()->routeIs('bank.*') ? 'bg-purple-900 text-white' : 'hover:bg-purple-50 hover:text-purple-900' }}">
                    <i class="fa-solid fa-building-columns"></i>
                    <span>De Bank</span>
                </a>
                <a href="{{ route('shop.index') }}" class="px-3 py-2 rounded flex items-center gap-1.5 transition-colors {{ request()->routeIs('shop.*') ? 'bg-purple-900 text-white' : 'hover:bg-purple-50 hover:text-purple-900' }}">
                    <i class="fa-solid fa-shop"></i>
                    <span>Kantinewinkel</span>
                </a>
                <a href="{{ route('prison') }}" class="px-3 py-2 rounded flex items-center gap-1.5 transition-colors {{ request()->routeIs('prison') ? 'bg-purple-900 text-white' : 'hover:bg-purple-50 hover:text-purple-900' }}">
                    <i class="fa-solid fa-lock"></i>
                    <span>Isoleercel</span>
                </a>
                <a href="{{ route('profile.me') }}" class="px-3 py-2 rounded flex items-center gap-1.5 transition-colors {{ request()->routeIs('profile.*') ? 'bg-purple-900 text-white' : 'hover:bg-purple-50 hover:text-purple-900' }}">
                    <i class="fa-solid fa-id-card"></i>
                    <span>Dossier & Gastenboek</span>
                </a>
                <a href="{{ route('gangs.index') }}" class="px-3 py-2 rounded flex items-center gap-1.5 transition-colors {{ request()->routeIs('gangs.*') ? 'bg-purple-900 text-white' : 'hover:bg-purple-50 hover:text-purple-900' }}">
                    <i class="fa-solid fa-users"></i>
                    <span>Gangs</span>
                </a>
                <a href="{{ route('leaderboard.index') }}" class="px-3 py-2 rounded flex items-center gap-1.5 transition-colors {{ request()->routeIs('leaderboard.*') ? 'bg-purple-900 text-white' : 'hover:bg-purple-50 hover:text-purple-900' }}">
                    <i class="fa-solid fa-trophy"></i>
                    <span>Ranglijst</span>
                </a>
            </div>
        </div>
    </nav>
    @endif

    <!-- Main Container -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <!-- Flash messages -->
        @if(session('success'))
        <div class="mb-5 bg-emerald-50 border-l-4 border-emerald-500 p-4 rounded-r shadow-sm flex items-start gap-3">
            <i class="fa-solid fa-circle-check text-emerald-600 text-lg mt-0.5"></i>
            <div>
                <p class="text-sm font-bold text-emerald-900">Gelukt!</p>
                <p class="text-xs text-emerald-800 mt-0.5">{{ session('success') }}</p>
            </div>
        </div>
        @endif

        @if(session('error'))
        <div class="mb-5 bg-rose-50 border-l-4 border-rose-500 p-4 rounded-r shadow-sm flex items-start gap-3">
            <i class="fa-solid fa-triangle-exclamation text-rose-600 text-lg mt-0.5"></i>
            <div>
                <p class="text-sm font-bold text-rose-900">Mislukt of geblokkeerd!</p>
                <p class="text-xs text-rose-800 mt-0.5">{{ session('error') }}</p>
            </div>
        </div>
        @endif

        @if($errors->any())
        <div class="mb-5 bg-rose-50 border-l-4 border-rose-500 p-4 rounded-r shadow-sm">
            <div class="flex items-center gap-2 mb-1">
                <i class="fa-solid fa-circle-xmark text-rose-600"></i>
                <span class="text-sm font-bold text-rose-900">Controleer de invoer:</span>
            </div>
            <ul class="list-disc list-inside text-xs text-rose-800 space-y-0.5 pl-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-slate-200 mt-12 py-6 text-center text-xs text-slate-500">
        <div class="max-w-7xl mx-auto px-4">
            <p class="font-bold text-slate-700">TBS Kliniek &copy; {{ date('Y') }} — Een Nederlandse Multiplayer Browsergame</p>
            <p class="mt-1 text-[11px] text-slate-400">Respect verdien je. Frikandelbroodjes moet je bevechten. Volledig gebouwd met Laravel.</p>
        </div>
    </footer>
</body>
</html>
