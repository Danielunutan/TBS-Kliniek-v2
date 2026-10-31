@extends('layouts.app')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-12 gap-6" x-data="{ search: '', activeTab: 'all' }">

    <!-- Left Column: Search, Type Select & Filter Tabs -->
    <div class="lg:col-span-3 space-y-4">
        <!-- Categories Navigation -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xs border border-slate-200/90 dark:border-slate-800 overflow-hidden p-3 space-y-1.5">
            <div class="px-2 py-1 text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">
                Categorieën
            </div>

            <a href="{{ route('leaderboard.index', ['type' => 'level']) }}" 
                class="w-full px-3 py-2 rounded-xl text-xs font-semibold flex items-center justify-between transition {{ ($type ?? 'level') === 'level' ? 'bg-purple-600 text-white font-black shadow-xs' : 'text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                <span class="flex items-center gap-2.5">
                    <i class="fa-solid fa-ranking-star {{ ($type ?? 'level') === 'level' ? 'text-white' : 'text-purple-500' }}"></i>
                    <span>Level & Ervaring</span>
                </span>
                @if(($type ?? 'level') === 'level')
                    <i class="fa-solid fa-check text-[10px]"></i>
                @endif
            </a>

            <a href="{{ route('leaderboard.index', ['type' => 'respect']) }}" 
                class="w-full px-3 py-2 rounded-xl text-xs font-semibold flex items-center justify-between transition {{ ($type ?? 'level') === 'respect' ? 'bg-purple-600 text-white font-black shadow-xs' : 'text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                <span class="flex items-center gap-2.5">
                    <i class="fa-solid fa-trophy {{ ($type ?? 'level') === 'respect' ? 'text-white' : 'text-amber-500' }}"></i>
                    <span>Meeste Respect</span>
                </span>
                @if(($type ?? 'level') === 'respect')
                    <i class="fa-solid fa-check text-[10px]"></i>
                @endif
            </a>

            <a href="{{ route('leaderboard.index', ['type' => 'wealth']) }}" 
                class="w-full px-3 py-2 rounded-xl text-xs font-semibold flex items-center justify-between transition {{ ($type ?? 'level') === 'wealth' ? 'bg-purple-600 text-white font-black shadow-xs' : 'text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                <span class="flex items-center gap-2.5">
                    <i class="fa-solid fa-sack-dollar {{ ($type ?? 'level') === 'wealth' ? 'text-white' : 'text-emerald-500' }}"></i>
                    <span>Rijkste Patiënten</span>
                </span>
                @if(($type ?? 'level') === 'wealth')
                    <i class="fa-solid fa-check text-[10px]"></i>
                @endif
            </a>

            <a href="{{ route('leaderboard.index', ['type' => 'crimes']) }}" 
                class="w-full px-3 py-2 rounded-xl text-xs font-semibold flex items-center justify-between transition {{ ($type ?? 'level') === 'crimes' ? 'bg-purple-600 text-white font-black shadow-xs' : 'text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60' }}">
                <span class="flex items-center gap-2.5">
                    <i class="fa-solid fa-mask {{ ($type ?? 'level') === 'crimes' ? 'text-white' : 'text-rose-500' }}"></i>
                    <span>Top Criminelen</span>
                </span>
                @if(($type ?? 'level') === 'crimes')
                    <i class="fa-solid fa-check text-[10px]"></i>
                @endif
            </a>
        </div>

        <!-- Filter & Search Card -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xs border border-slate-200/90 dark:border-slate-800 overflow-hidden p-3.5 space-y-2">
            <button @click="activeTab = 'all'" 
                :class="activeTab === 'all' ? 'bg-slate-100 dark:bg-slate-800 text-slate-900 dark:text-white font-bold shadow-2xs' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800/40'" 
                class="w-full px-3 py-2 rounded-xl text-xs font-semibold flex items-center justify-between transition cursor-pointer">
                <span class="flex items-center gap-2">
                    <i class="fa-solid fa-list-ol text-slate-400"></i>
                    <span>Volledige Top 50</span>
                </span>
                <span class="text-[10px] font-bold opacity-80">{{ is_countable($leaderboard ?? null) ? count($leaderboard) : (is_countable($topCharacters ?? null) ? count($topCharacters) : 0) }}</span>
            </button>

            <button @click="activeTab = 'top10'" 
                :class="activeTab === 'top10' ? 'bg-slate-100 dark:bg-slate-800 text-slate-900 dark:text-white font-bold shadow-2xs' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800/40'" 
                class="w-full px-3 py-2 rounded-xl text-xs font-semibold flex items-center justify-between transition cursor-pointer">
                <span class="flex items-center gap-2">
                    <i class="fa-solid fa-crown text-amber-500"></i>
                    <span>Top 10 Spelers</span>
                </span>
                <span class="text-[10px] font-bold opacity-80">10</span>
            </button>

            <!-- Search Box -->
            <div class="pt-2 border-t border-slate-100 dark:border-slate-800">
                <div class="relative">
                    <input type="text" x-model="search" placeholder="Zoek speler op naam..." class="w-full bg-slate-50 dark:bg-slate-800/90 text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 text-xs px-3 py-2 pl-8 rounded-xl border border-slate-200 dark:border-slate-700 focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500 transition">
                    <i class="fa-solid fa-magnifying-glass absolute left-2.5 top-2.5 text-slate-400 text-xs"></i>
                </div>
            </div>
        </div>

        <!-- Info Card: Current User Position -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xs border border-slate-200/90 dark:border-slate-800 p-4">
            <h4 class="text-xs font-bold text-slate-900 dark:text-slate-100 mb-1">Jouw Status</h4>
            <p class="text-xs text-slate-500 dark:text-slate-400 mb-3">Verdien XP, contant geld en respect om te stijgen in de ranglijsten.</p>
            <div class="space-y-2">
                <div class="flex items-center justify-between p-2.5 bg-slate-50/90 dark:bg-slate-800/60 rounded-xl border border-slate-200/80 dark:border-slate-700/70">
                    <span class="text-xs font-bold text-slate-700 dark:text-slate-200">{{ $character->name }}</span>
                    <span class="text-xs font-extrabold text-purple-600 dark:text-purple-400">Level {{ $character->level }}</span>
                </div>
                <div class="flex items-center justify-between px-2 text-[11px] text-slate-500 dark:text-slate-400">
                    <span>Respect:</span>
                    <span class="font-bold text-slate-700 dark:text-slate-200">{{ number_format($character->respect, 0, ',', '.') }}</span>
                </div>
                <div class="flex items-center justify-between px-2 text-[11px] text-slate-500 dark:text-slate-400">
                    <span>Totaal Geld:</span>
                    <span class="font-bold text-emerald-600 dark:text-emerald-400">€{{ number_format($character->cash + $character->bank, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column: The Leaderboard -->
    <div class="lg:col-span-9">
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xs border border-slate-200/90 dark:border-slate-800 overflow-hidden">
            
            <div class="p-5 border-b border-slate-200/80 dark:border-slate-800 flex items-center justify-between">
                <div>
                    <h1 class="text-lg font-black text-slate-900 dark:text-slate-100">
                        Patiënten Ranglijst &bull; 
                        <span class="text-purple-600 dark:text-purple-400">
                            @switch($type ?? 'level')
                                @case('respect') Meeste Respect @break
                                @case('wealth') Rijkste Patiënten @break
                                @case('crimes') Grootste Criminelen @break
                                @default Level & Ervaring
                            @endswitch
                        </span>
                    </h1>
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        @switch($type ?? 'level')
                            @case('respect') De patiënten met het meeste aanzien en respect binnen de inrichting. @break
                            @case('wealth') De rijkste patiënten gesorteerd op contant geld en bankrekening. @break
                            @case('crimes') De meest succesvolle criminelen gesorteerd op geslaagde diefstallen. @break
                            @default De meest ervaren patiënten van de TBS kliniek op basis van level en XP.
                        @endswitch
                    </p>
                </div>
                <div class="w-9 h-9 rounded-xl bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 flex items-center justify-center text-sm border border-amber-100 dark:border-amber-800/40">
                    <i class="fa-solid fa-trophy"></i>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/90 dark:bg-slate-800/60 text-[10px] uppercase tracking-wider font-bold text-slate-500 dark:text-slate-400 border-b border-slate-200/80 dark:border-slate-800">
                            <th class="p-3.5 w-14 text-center">Positie</th>
                            <th class="p-3.5">Patiënt</th>
                            <th class="p-3.5">Bende</th>
                            <th class="p-3.5 text-right">
                                @switch($type ?? 'level')
                                    @case('respect') Respect @break
                                    @case('wealth') Vermogen @break
                                    @case('crimes') Geslaagde Diefstallen @break
                                    @default Level & XP
                                @endswitch
                            </th>
                            <th class="p-3.5 w-24 text-right">Actie</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-xs">
                        @forelse(($leaderboard ?? $topCharacters ?? []) as $index => $player)
                            <tr x-show="(!search || @js(strtolower((string) $player->name)).includes(search.toLowerCase())) && (activeTab === 'all' || {{ $index }} < 10)" 
                                class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors {{ $player->id === $character->id ? 'bg-indigo-50/40 dark:bg-purple-950/40 font-semibold' : '' }}">
                                
                                <td class="p-3.5 text-center font-bold">
                                    @if($index === 0)
                                        <span class="w-7 h-7 rounded-full bg-amber-100 dark:bg-amber-500/20 text-amber-700 dark:text-amber-300 border border-amber-300 dark:border-amber-500/40 flex items-center justify-center mx-auto text-xs font-black shadow-2xs">
                                            <i class="fa-solid fa-crown text-[10px]"></i>
                                        </span>
                                    @elseif($index === 1)
                                        <span class="w-7 h-7 rounded-full bg-slate-200 dark:bg-slate-700 text-slate-800 dark:text-slate-100 border border-slate-300 dark:border-slate-600 flex items-center justify-center mx-auto text-xs font-black shadow-2xs">
                                            2
                                        </span>
                                    @elseif($index === 2)
                                        <span class="w-7 h-7 rounded-full bg-amber-50 dark:bg-amber-950/40 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-700/50 flex items-center justify-center mx-auto text-xs font-black shadow-2xs">
                                            3
                                        </span>
                                    @else
                                        <span class="text-slate-400 dark:text-slate-500 font-bold">{{ $index + 1 }}</span>
                                    @endif
                                </td>

                                <td class="p-3.5">
                                    <div class="flex items-center gap-3">
                                        <div class="relative shrink-0">
                                            @php
                                                $playerBorder = $player->getEquippedBorderInfo();
                                            @endphp
                                            <img src="{{ $player->avatar_url }}" alt="{{ $player->name }}" class="w-8 h-8 rounded-lg object-cover {{ $playerBorder ? $playerBorder['ring'] . ' ' . $playerBorder['border'] . ' border' : 'border border-slate-200 dark:border-slate-700' }}">
                                            @if($playerBorder)
                                                <span class="absolute -top-1.5 -right-1.5 text-[10px] leading-none drop-shadow select-none">{{ $playerBorder['emoji'] }}</span>
                                            @else
                                                <span class="absolute -bottom-1 -right-1 w-2.5 h-2.5 rounded-full border-2 border-white dark:border-slate-900 {{ $player->isOnline() ? 'bg-emerald-500' : 'bg-slate-300 dark:bg-slate-600' }}" title="{{ $player->online_status_label }}"></span>
                                            @endif
                                        </div>
                                        <div>
                                            <a href="{{ route('profile.show', $player->name) }}" class="font-bold text-slate-900 dark:text-slate-100 hover:text-purple-600 dark:hover:text-purple-400 transition block leading-tight">
                                                {{ $player->name }}
                                            </a>
                                            <span class="text-[10px] font-semibold {{ $player->isOnline() ? 'text-emerald-600 dark:text-emerald-400 font-bold' : 'text-slate-400' }}">Level {{ $player->level }} &bull; {{ $player->isOnline() ? 'Online' : 'Offline' }}</span>
                                        </div>
                                    </div>
                                </td>

                                <td class="p-3.5">
                                    @if($player->gang)
                                        <a href="{{ route('gangs.show', $player->gang->id) }}" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[11px] font-bold bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 hover:text-purple-600 dark:text-slate-200 dark:hover:text-purple-300 border border-slate-200 dark:border-slate-700 transition" title="Bekijk {{ $player->gang->name }}">
                                            <i class="fa-solid fa-users text-[10px] text-slate-400"></i>
                                            <span>{{ $player->gang->name }}</span>
                                        </a>
                                    @else
                                        <span class="text-slate-400 text-[11px] italic">Geen bende</span>
                                    @endif
                                </td>

                                <td class="p-3.5 text-right font-extrabold text-slate-900 dark:text-slate-100">
                                    @switch($type ?? 'level')
                                        @case('respect')
                                            <span class="text-amber-600 dark:text-amber-400">{{ number_format($player->respect, 0, ',', '.') }}</span>
                                            @break
                                        @case('wealth')
                                            <span class="text-emerald-600 dark:text-emerald-400">€{{ number_format($player->total_wealth ?? ($player->cash + $player->bank), 0, ',', '.') }}</span>
                                            @break
                                        @case('crimes')
                                            <span class="text-rose-600 dark:text-rose-400">{{ number_format($player->thefts_succeeded ?? 0, 0, ',', '.') }}</span>
                                            @break
                                        @default
                                            <span>Lvl {{ $player->level }}</span>
                                            <span class="text-[10px] text-slate-400 font-normal block">{{ number_format($player->xp, 0, ',', '.') }} XP</span>
                                    @endswitch
                                </td>

                                <td class="p-3.5 text-right">
                                    <a href="{{ route('profile.show', $player->name) }}" class="px-2.5 py-1.5 rounded-xl text-[11px] font-bold bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 border border-slate-200/80 dark:border-slate-700 transition-all duration-150 active:scale-95 cursor-pointer inline-block">
                                        Bekijk
                                    </a>
                                </td>

                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="p-8 text-center text-slate-500 dark:text-slate-400">
                                    Nog geen gegevens beschikbaar voor deze ranglijst.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </div>

</div>
@endsection
