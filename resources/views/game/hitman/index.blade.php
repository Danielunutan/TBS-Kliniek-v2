@extends('layouts.app')

@section('title', 'Liquidatie & Wapenarsenaal - TBS Kliniek')

@section('content')
<div class="space-y-4" x-data="{
    activeTab: 'shoot',
    search: '',
    selectedId: '',
    selectedName: '',
    openDropdown: false,
    bulletAmount: 50,
    isSearching: false,
    searchResults: [],
    targets: [
        @foreach($targets as $target)
        {
            id: '{{ $target->id }}',
            name: '{{ addslashes($target->name ?? 'Onbekend') }}',
            level: {{ $target->level }},
            respect: {{ $target->respect }},
            health: {{ $target->health }},
            cash: '{{ number_format($target->cash, 0, ',', '.') }}',
            avatar: '{{ $target->avatar_url }}',
            isOnline: {{ $target->isOnline() ? 'true' : 'false' }},
            inPrison: {{ $target->isImprisoned() ? 'true' : 'false' }},
            inComa: {{ $target->isComatose() ? 'true' : 'false' }},
            isProtected: {{ $target->isProtected() ? 'true' : 'false' }},
            hasBloodFeud: {{ $target->has_blood_feud ? 'true' : 'false' }},
            onCooldown: {{ $target->on_cooldown ? 'true' : 'false' }},
            cooldownSeconds: {{ $target->cooldown_seconds ?? 0 }},
            dailyDeathsReached: {{ $target->daily_deaths_reached ? 'true' : 'false' }},
            isLevelRestricted: {{ $target->is_level_restricted ? 'true' : 'false' }}
        },
        @endforeach
    ],
    get displayedTargets() {
        if (!this.search.trim()) return this.targets;
        if (this.searchResults.length > 0) return this.searchResults;
        return this.targets.filter(t => t.name.toLowerCase().includes(this.search.toLowerCase()));
    },
    async performSearch() {
        this.openDropdown = true;
        this.selectedId = '';
        const query = this.search.trim();
        if (query.length >= 1) {
            this.isSearching = true;
            try {
                const res = await fetch(`{{ route('hitman.search') }}?q=${encodeURIComponent(query)}`);
                const data = await res.json();
                this.searchResults = data.targets || [];
            } catch(e) {
                this.searchResults = [];
            } finally {
                this.isSearching = false;
            }
        } else {
            this.searchResults = [];
        }
    },
    select(target) {
        this.selectedId = target.id;
        this.selectedName = target.name;
        this.search = target.name;
        this.openDropdown = false;
    }
}">

    <!-- Combat Mode App Tab Switcher -->
    <div class="grid grid-cols-2 gap-2 bg-slate-100 dark:bg-slate-800 p-1.5 rounded-2xl border border-slate-200 dark:border-slate-700">
        <a href="{{ route('fight.index') }}" class="py-2.5 px-3 rounded-xl hover:bg-white/60 dark:hover:bg-slate-900/60 text-slate-600 dark:text-slate-300 font-extrabold text-xs text-center transition flex items-center justify-center gap-2">
            <i class="fa-solid fa-sack-dollar text-emerald-600"></i>
            <span>Geld Stelen (Luchtplaats)</span>
        </a>
        <a href="{{ route('hitman.index') }}" class="py-2.5 px-3 rounded-xl bg-white dark:bg-slate-900 text-rose-600 dark:text-rose-400 font-black text-xs text-center shadow-xs flex items-center justify-center gap-1.5 border border-rose-200 dark:border-rose-800">
            <i class="fa-solid fa-crosshairs text-rose-600"></i>
            <span>Liquidatie & Kogels</span>
            <span class="text-[9px] bg-rose-100 dark:bg-rose-950 text-rose-700 dark:text-rose-300 font-black px-1.5 py-0.5 rounded-full">ACTIEF</span>
        </a>
    </div>

    <!-- HERO HEADER (Compact & Mobile-Optimized) -->
    <div class="bg-gradient-to-br from-slate-900 via-purple-950 to-slate-900 rounded-2xl p-4 sm:p-6 text-white shadow-lg border border-purple-900/60 relative overflow-hidden">
        <div class="relative z-10 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 sm:w-13 sm:h-13 rounded-2xl bg-rose-600/30 border border-rose-500/50 text-rose-400 flex items-center justify-center text-xl sm:text-2xl shadow-inner shrink-0">
                    <i class="fa-solid fa-crosshairs"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="text-[9px] sm:text-[10px] font-black uppercase tracking-widest px-2 py-0.5 rounded-full bg-rose-600 text-white">
                            TBS Onderwereld
                        </span>
                        <span class="text-[10px] font-bold text-amber-400 flex items-center gap-1">
                            <i class="fa-solid fa-shield-halved"></i> Backfire Actief
                        </span>
                    </div>
                    <h1 class="text-lg sm:text-2xl font-black tracking-tight text-white mt-0.5">
                        Liquidatie & Wapenarsenaal
                    </h1>
                    <p class="text-xs text-slate-300 max-w-xl leading-snug hidden sm:block">
                        Schiet rivalen neer met kogels en plunder 35% van hun contante geld. Pas op voor <strong class="text-rose-400">Backfire</strong> als de tegenstander een kogelvrij vest draagt!
                    </p>
                </div>
            </div>

            <!-- Tab Switcher (Touch Pills) -->
            <div class="flex items-center gap-1 bg-slate-800/80 p-1 rounded-xl shrink-0 border border-slate-700/60 w-full sm:w-auto">
                <button type="button" 
                        @click="activeTab = 'shoot'" 
                        :class="activeTab === 'shoot' ? 'bg-rose-600 text-white font-black shadow-xs' : 'text-slate-400 font-bold hover:text-white'"
                        class="flex-1 sm:flex-initial px-3 py-2 rounded-lg text-xs transition cursor-pointer flex items-center justify-center gap-1.5">
                    <i class="fa-solid fa-gun"></i>
                    <span>Liquidatie (Kogels)</span>
                </button>
                <button type="button" 
                        @click="activeTab = 'contracts'" 
                        :class="activeTab === 'contracts' ? 'bg-purple-600 text-white font-black shadow-xs' : 'text-slate-400 font-bold hover:text-white'"
                        class="flex-1 sm:flex-initial px-3 py-2 rounded-lg text-xs transition cursor-pointer flex items-center justify-center gap-1.5">
                    <i class="fa-solid fa-file-contract"></i>
                    <span>Contracten</span>
                </button>
            </div>
        </div>
    </div>

    <!-- QUICK WEAPON & ARMOR STATS (Koop Kogels & Vest met Sterren) -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5 sm:gap-3 text-xs">
        
        <!-- 1. BULLETS CARD -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-purple-900/40 rounded-2xl p-3.5 shadow-xs flex flex-col justify-between gap-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-xl bg-amber-500/10 text-amber-500 flex items-center justify-center text-sm font-black shrink-0">
                        <i class="fa-solid fa-boxes-stacked"></i>
                    </div>
                    <div>
                        <span class="text-[10px] uppercase font-bold text-slate-400 block leading-none">Munitievoorraad</span>
                        <span class="text-base font-black text-slate-900 dark:text-white font-mono leading-tight">
                            {{ number_format($character->bullets ?? 0, 0, ',', '.') }} Kogels
                        </span>
                    </div>
                </div>
                <span class="text-[10px] font-black text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-950/60 px-2 py-0.5 rounded border border-amber-200 dark:border-amber-800">
                    50 ⭐ = 50 Kogels
                </span>
            </div>

            <div>
                <form action="{{ route('hitman.buy_bullets') }}" method="POST" class="w-full">
                    @csrf
                    <button type="submit" 
                            class="btn-star-gold w-full py-2.5 px-3 rounded-xl active:scale-95 text-slate-900 dark:text-white font-black text-xs transition cursor-pointer flex items-center justify-center gap-1.5 shadow-xs">
                        <i class="fa-solid fa-star text-xs"></i>
                        <span>Koop +50 Kogels (50 Sterren ⭐)</span>
                    </button>
                </form>
            </div>
        </div>

        <!-- 2. BULLETPROOF VEST CARD -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-purple-900/40 rounded-2xl p-3.5 shadow-xs flex flex-col justify-between gap-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-xl bg-purple-500/10 text-purple-500 flex items-center justify-center text-sm font-black shrink-0">
                        <i class="fa-solid fa-shield-halved"></i>
                    </div>
                    <div>
                        <span class="text-[10px] uppercase font-bold text-slate-400 block leading-none">Kogelwerend Vest</span>
                        <span class="text-xs font-black {{ $character->hasVest() ? 'text-emerald-500' : 'text-slate-400' }} leading-tight block mt-0.5">
                            @if($character->hasVest())
                                🛡️ Actief (nog {{ ceil($character->getVestRemainingSeconds() / 3600) }}u)
                            @else
                                ❌ Geen Pantser (Kwetsbaar)
                            @endif
                        </span>
                    </div>
                </div>
                <span class="text-[10px] font-black text-purple-600 dark:text-purple-400 bg-purple-50 dark:bg-purple-950/60 px-2 py-0.5 rounded border border-purple-200 dark:border-purple-800">
                    150 ⭐ = 12u Vest
                </span>
            </div>

            <div>
                <form action="{{ route('hitman.buy_vest') }}" method="POST" class="w-full">
                    @csrf
                    <button type="submit" 
                            class="w-full py-2.5 px-3 rounded-xl bg-purple-600 hover:bg-purple-700 active:scale-95 text-white font-black text-xs transition cursor-pointer flex items-center justify-center gap-1.5 shadow-xs">
                        <i class="fa-solid fa-shield-halved text-amber-300 text-xs"></i>
                        <span>Koop 12u Vest (150 Sterren ⭐)</span>
                    </button>
                </form>
            </div>
        </div>

        <!-- 3. COMBAT RECORD -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-purple-900/40 rounded-2xl p-3.5 shadow-xs flex flex-col justify-between gap-2">
            <div>
                <span class="text-[10px] uppercase font-bold text-slate-400 block leading-none mb-1">Moordstatistieken</span>
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-[10px] text-slate-500 block">Geliquideerd</span>
                        <span class="text-sm font-black text-rose-600 dark:text-rose-400 font-mono">{{ $character->kills_count ?? 0 }} Kills</span>
                    </div>
                    <div class="text-right">
                        <span class="text-[10px] text-slate-500 block">Neergeschoten</span>
                        <span class="text-sm font-black text-slate-600 dark:text-slate-300 font-mono">{{ $character->deaths_count ?? 0 }} Dood</span>
                    </div>
                </div>
            </div>
            <div class="text-[10px] text-slate-400 flex items-center justify-between pt-1 border-t border-slate-100 dark:border-slate-800">
                <span>Energie: <strong class="text-emerald-500">{{ $character->energy }}/{{ $character->max_energy }}</strong></span>
                <span>Contant: <strong class="text-amber-500">€{{ number_format($character->cash, 0, ',', '.') }}</strong></span>
            </div>
        </div>

    </div>

    <!-- FAIR PLAY BALANCERING & RIVALITEIT REGELS -->
    <div class="bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 text-xs space-y-2.5">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2 text-slate-800 dark:text-slate-200 font-black text-xs uppercase tracking-wide">
                <i class="fa-solid fa-scale-balanced text-amber-500 text-sm"></i>
                <span>Eerlijk Spel &amp; Rivaliteit Regels</span>
            </div>
            <span class="text-[10px] font-bold text-slate-500 dark:text-slate-400 bg-slate-200 dark:bg-slate-800 px-2 py-0.5 rounded-full">Anti-Griefing Actief</span>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5 text-[11px] text-slate-600 dark:text-slate-400">
            <div class="bg-white dark:bg-slate-800/90 p-3 rounded-xl border border-slate-200/80 dark:border-slate-700/80">
                <strong class="text-slate-900 dark:text-white block font-black mb-0.5"><i class="fa-solid fa-clock-rotate-left text-purple-500 mr-1"></i> 3 Uur Doelwit Cooldown</strong>
                Je kunt dezelfde speler maximaal 1x per 3 uur viseren om continu treiteren te voorkomen.
            </div>
            <div class="bg-white dark:bg-slate-800/90 p-3 rounded-xl border border-slate-200/80 dark:border-slate-700/80">
                <strong class="text-slate-900 dark:text-white block font-black mb-0.5"><i class="fa-solid fa-shield-heart text-emerald-500 mr-1"></i> 30m Herstelschild &amp; Max 3/Dag</strong>
                Na ontwaken 30 min rust. Slachtoffers kunnen maximaal 3x per 24 uur fatale schade oplopen.
            </div>
            <div class="bg-white dark:bg-slate-800/90 p-3 rounded-xl border border-slate-200/80 dark:border-slate-700/80">
                <strong class="text-slate-900 dark:text-white block font-black mb-0.5"><i class="fa-solid fa-layer-group text-rose-500 mr-1"></i> Max 15 Levels &amp; 35% Buit</strong>
                Alleen gelijke tegenstanders. Bij actieve Bloedwraak vervallen levelrestricties!
            </div>
        </div>
    </div>

    @if(isset($bloodFeuds) && $bloodFeuds->isNotEmpty())
        <!-- BLOEDWRAAK VETES BANNER -->
        <div class="bg-gradient-to-r from-red-950 via-rose-950 to-slate-950 border-2 border-red-600/70 rounded-2xl p-4 sm:p-5 text-white shadow-xl space-y-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <div class="w-10 h-10 rounded-xl bg-red-600/30 border border-red-500/60 text-red-400 flex items-center justify-center text-lg shrink-0">
                        <i class="fa-solid fa-skull-crossbones animate-pulse"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-black text-white flex items-center gap-1.5">
                            <span>🩸 ACTIEVE BLOEDWRAAK VETES</span>
                            <span class="text-[10px] bg-red-600 px-2 py-0.5 rounded-full font-black">{{ $bloodFeuds->count() }}</span>
                        </h3>
                        <p class="text-xs text-rose-200">
                            Jij of een bendelid is neergeschoten! Vergeld de aanval voor <strong>+50 extra Respect</strong>, <strong>+€10.000 bonus</strong> en <strong>+150 Bende Respect</strong> (level- en cooldownrestricties vervallen).
                        </p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 pt-1">
                @foreach($bloodFeuds as $feud)
                    <div class="bg-black/50 border border-red-700/60 rounded-xl p-3 flex items-center justify-between gap-3">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <img src="{{ $feud->attacker->avatar_url ?? '' }}" class="w-10 h-10 rounded-xl object-cover border border-red-500/60 shrink-0">
                            <div class="min-w-0">
                                <span class="font-black text-xs text-white block truncate">{{ $feud->attacker->name ?? 'Onbekend' }}</span>
                                <span class="text-[10px] text-rose-300 block truncate">
                                    Schoot {{ $feud->victim->name ?? 'jouw bendelid' }} neer • nog {{ max(0, (int) round(now()->diffInHours($feud->expires_at, false))) }}u
                                </span>
                            </div>
                        </div>
                        <button type="button" 
                                @click="select({ id: '{{ $feud->attacker_id }}', name: '{{ addslashes($feud->attacker->name ?? '') }}' }); activeTab = 'shoot'"
                                class="px-3 py-1.5 rounded-lg bg-red-600 hover:bg-red-500 active:scale-95 text-white font-black text-[11px] shrink-0 transition cursor-pointer shadow-xs">
                            Vergelden &raquo;
                        </button>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- TAB 1: LIQUIDATIE MET KOGELS & BACKFIRE -->
    <div x-show="activeTab === 'shoot'" class="space-y-4">
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-purple-900/40 rounded-2xl p-4 sm:p-6 shadow-xs space-y-4">
            
            <div class="border-b border-slate-100 dark:border-slate-800 pb-3">
                <h2 class="text-sm sm:text-base font-black text-slate-900 dark:text-white flex items-center gap-2">
                    <i class="fa-solid fa-crosshairs text-rose-600"></i>
                    <span>Patiënt Liquideren (Schieten)</span>
                </h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                    Selecteer een doelwit en bepaal hoeveel kogels je vuurt. Dodelijke voltreffer = doelwit 1 uur in Coma (ICU), 30m herstelschild en 35% contant geld geplunderd!
                </p>
            </div>

            <form action="{{ route('hitman.shoot') }}" method="POST" class="space-y-4">
                @csrf

                <!-- Target Search / Select -->
                <div class="relative">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                        1. Kies Doelwit (Zoek op naam)
                    </label>
                    <input type="hidden" name="target_id" :value="selectedId" required>
                    <input type="hidden" name="target_name" :value="search">

                    <div class="relative flex items-center">
                        <div class="absolute left-3.5 top-1/2 -translate-y-1/2 flex items-center pointer-events-none text-rose-500 text-sm z-10">
                            <i class="fa-solid fa-crosshairs"></i>
                        </div>
                        <input type="text"
                               x-model="search"
                               @focus="openDropdown = true; if(search.trim().length >= 1) performSearch()"
                               @input.debounce.250ms="performSearch()"
                               @click.outside="openDropdown = false"
                               required
                               autocomplete="off"
                               placeholder="Typ naam van medepatiënt..."
                               class="w-full bg-slate-50 dark:bg-slate-800/90 border border-slate-300 dark:border-purple-800/60 focus:bg-white dark:focus:bg-[#12082b] focus:border-rose-500 rounded-xl py-2.5 sm:py-3 pl-10 pr-10 text-xs sm:text-sm font-bold text-slate-900 dark:text-white focus:outline-none transition shadow-xs">
                        <div class="absolute right-3.5 top-1/2 -translate-y-1/2 flex items-center pointer-events-none text-slate-400 text-xs z-10">
                            <template x-if="isSearching">
                                <i class="fa-solid fa-circle-notch fa-spin text-rose-500"></i>
                            </template>
                            <template x-if="!isSearching">
                                <i class="fa-solid fa-chevron-down transition-transform duration-150" :class="openDropdown ? 'rotate-180' : ''"></i>
                            </template>
                        </div>
                    </div>

                    <!-- Dropdown List -->
                    <div x-show="openDropdown" 
                         x-transition
                         class="absolute z-50 left-0 right-0 mt-1 max-h-56 overflow-y-auto bg-white dark:bg-[#140830] rounded-xl shadow-xl border border-slate-200 dark:border-purple-800/80 divide-y divide-slate-100 dark:divide-purple-900/40">
                        <template x-for="t in displayedTargets" :key="t.id">
                            <button type="button" 
                                    @click="select(t)"
                                    class="w-full text-left px-3.5 py-2 hover:bg-rose-50 dark:hover:bg-rose-950/40 flex items-center justify-between text-xs transition cursor-pointer group">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <div class="relative shrink-0">
                                        <img :src="t.avatar" class="w-8 h-8 rounded-xl object-cover border border-slate-200 dark:border-slate-700">
                                        <span class="absolute -bottom-0.5 -right-0.5 w-2 h-2 rounded-full" :class="t.isOnline ? 'bg-emerald-500' : 'bg-slate-400'"></span>
                                    </div>
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-1.5 flex-wrap">
                                            <span class="font-extrabold text-slate-900 dark:text-white group-hover:text-rose-500 block truncate" x-text="t.name"></span>
                                            <template x-if="t.hasBloodFeud">
                                                <span class="text-[9px] font-black px-1.5 py-0.2 rounded bg-red-600 text-white animate-pulse">🩸 Bloedwraak</span>
                                            </template>
                                            <template x-if="t.inPrison">
                                                <span class="text-[9px] font-bold px-1.5 py-0.2 rounded bg-amber-500/10 text-amber-500 border border-amber-500/20">Isoleercel</span>
                                            </template>
                                            <template x-if="t.inComa">
                                                <span class="text-[9px] font-bold px-1.5 py-0.2 rounded bg-rose-500/10 text-rose-500 border border-rose-500/20">Coma</span>
                                            </template>
                                            <template x-if="t.isProtected">
                                                <span class="text-[9px] font-bold px-1.5 py-0.2 rounded bg-blue-500/10 text-blue-500 border border-blue-500/20">Beschermd</span>
                                            </template>
                                            <template x-if="t.dailyDeathsReached">
                                                <span class="text-[9px] font-bold px-1.5 py-0.2 rounded bg-purple-500/10 text-purple-400 border border-purple-500/20">Max 3/dag</span>
                                            </template>
                                            <template x-if="t.onCooldown">
                                                <span class="text-[9px] font-bold px-1.5 py-0.2 rounded bg-amber-500/10 text-amber-400 border border-amber-500/20">Wachttijd</span>
                                            </template>
                                            <template x-if="t.isLevelRestricted">
                                                <span class="text-[9px] font-bold px-1.5 py-0.2 rounded bg-slate-500/10 text-slate-400 border border-slate-500/20">>15 Lvl Verschil</span>
                                            </template>
                                        </div>
                                        <span class="text-[10px] text-slate-400 truncate block">Lvl <span x-text="t.level"></span> • <span class="text-emerald-500 font-mono font-bold" x-text="'€' + t.cash"></span> op zak</span>
                                    </div>
                                </div>
                                <span x-show="selectedId === t.id" class="text-rose-500 font-black text-sm shrink-0">
                                    <i class="fa-solid fa-check"></i>
                                </span>
                            </button>
                        </template>
                        <template x-if="isSearching">
                            <div class="p-3 text-xs text-slate-400 text-center flex items-center justify-center gap-2">
                                <i class="fa-solid fa-circle-notch fa-spin text-rose-500"></i>
                                <span>Zoeken in patiëntendossiers...</span>
                            </div>
                        </template>
                        <template x-if="!isSearching && displayedTargets.length === 0">
                            <div class="p-3 text-xs text-slate-400 text-center">
                                Geen patiënt gevonden met '<span x-text="search"></span>'
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Bullets Selector (Quick Chips + Input) -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                        2. Aantal Kogels Afvuren
                    </label>
                    <div class="grid grid-cols-4 gap-2 mb-2">
                        <template x-for="amt in [25, 50, 100, 250]" :key="amt">
                            <button type="button" 
                                    @click="bulletAmount = amt"
                                    :class="bulletAmount === amt ? 'bg-rose-600 text-white font-black border-rose-600' : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-700 hover:border-rose-400'"
                                    class="py-2 rounded-xl text-xs font-bold border transition cursor-pointer flex items-center justify-center gap-1">
                                <i class="fa-solid fa-gun text-[10px]"></i>
                                <span x-text="amt + 'x'"></span>
                            </button>
                        </template>
                    </div>
                    <div class="relative flex items-center">
                        <input type="number" 
                               name="bullets" 
                               x-model.number="bulletAmount" 
                               min="25" 
                               max="1000"
                               required
                               class="w-full bg-slate-50 dark:bg-slate-800/90 border border-slate-300 dark:border-purple-800/60 focus:bg-white dark:focus:bg-[#12082b] focus:border-rose-500 rounded-xl py-2 px-3 text-xs sm:text-sm font-bold text-slate-900 dark:text-white font-mono">
                        <span class="absolute right-3 text-xs font-bold text-slate-400 pointer-events-none">Kogels</span>
                    </div>
                </div>

                <!-- Danger / Backfire Alert Warning -->
                <div class="p-3 rounded-xl bg-amber-500/10 border border-amber-400/40 text-amber-900 dark:text-amber-200 text-xs flex items-start gap-2.5">
                    <i class="fa-solid fa-triangle-exclamation text-amber-500 text-sm mt-0.5 shrink-0"></i>
                    <div class="leading-relaxed text-[11px]">
                        <strong>Pas op voor Backfire!</strong> Als het doelwit een kogelwerend vest draagt of te sterk is, ketsen je kogels af en schiet je jezelf 1 uur in coma!
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit"
                        @click="if(window.tbsVibrate) window.tbsVibrate([20, 40, 20]);"
                        {{ $character->isImprisoned() || ($character->bullets ?? 0) < 25 ? 'disabled' : '' }}
                        class="w-full py-3.5 px-6 rounded-xl font-black text-xs sm:text-sm uppercase tracking-wider text-white transition shadow-md flex items-center justify-center gap-2 cursor-pointer active:scale-95 {{ $character->isImprisoned() || ($character->bullets ?? 0) < 25 ? 'bg-slate-400 dark:bg-slate-800 cursor-not-allowed opacity-50' : 'bg-gradient-to-r from-rose-600 via-red-600 to-rose-700 hover:from-rose-700 hover:to-red-800' }}">
                    <i class="fa-solid fa-crosshairs text-base"></i>
                    <span>Vuur Kogels Af! (20 EP)</span>
                </button>
            </form>

        </div>
    </div>

    <!-- TAB 2: ONDERWERELD CONTRACTEN (Huurmoorden) -->
    <div x-show="activeTab === 'contracts'" class="space-y-4" style="display: none;">
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-purple-900/40 rounded-2xl p-4 sm:p-6 shadow-xs space-y-4">
            <div class="border-b border-slate-100 dark:border-slate-800 pb-3">
                <h2 class="text-sm sm:text-base font-black text-slate-900 dark:text-white flex items-center gap-2">
                    <i class="fa-solid fa-file-contract text-purple-600"></i>
                    <span>Huurmoordenaar Inhuren</span>
                </h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                    Geen kogels? Betaal een huurmoordenaar met contant geld om het vuile werk voor je op te knappen.
                </p>
            </div>

            <form action="{{ route('hitman.contract') }}" method="POST" class="space-y-4">
                @csrf
                <input type="hidden" name="target_id" :value="selectedId" required>
                <input type="hidden" name="target_name" :value="search">

                <!-- Target Confirmation -->
                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 flex items-center justify-between text-xs">
                    <div>
                        <span class="text-slate-400 block text-[10px]">Geselecteerd doelwit:</span>
                        <strong class="text-slate-900 dark:text-white font-black" x-text="search || 'Selecteer eerst een doelwit in het hoofdtapje'"></strong>
                    </div>
                    <button type="button" @click="activeTab = 'shoot'" class="text-purple-600 dark:text-purple-400 font-bold hover:underline">
                        Wijzig doelwit &raquo;
                    </button>
                </div>

                <!-- Contracts Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    @foreach($contracts as $contract)
                    <label class="relative flex flex-col justify-between p-3.5 border-2 border-slate-200 dark:border-slate-700 rounded-xl cursor-pointer hover:border-purple-500 transition has-[:checked]:border-purple-600 dark:has-[:checked]:border-purple-500 has-[:checked]:bg-purple-50/20">
                        <input type="radio" name="contract_id" value="{{ $contract['id'] }}" class="peer sr-only" required {{ $loop->first ? 'checked' : '' }}>
                        <div>
                            <div class="text-xs font-black text-slate-900 dark:text-white">{{ $contract['name'] }}</div>
                            <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5 leading-snug">{{ $contract['desc'] }}</div>
                        </div>
                        <div class="mt-3 pt-2.5 border-t border-slate-100 dark:border-slate-800 text-[11px] space-y-1">
                            <div class="flex justify-between">
                                <span class="text-slate-400">Prijs:</span>
                                <span class="font-extrabold text-amber-500">€{{ number_format($contract['cost'], 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-400">Energie:</span>
                                <span class="font-bold text-emerald-500">{{ $contract['energy'] }} EP</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-400">Slagingskans:</span>
                                <span class="font-bold text-purple-500">{{ $contract['rate'] }}%</span>
                            </div>
                        </div>
                    </label>
                    @endforeach
                </div>

                <button type="submit"
                        {{ $character->isImprisoned() ? 'disabled' : '' }}
                        class="w-full py-3 px-6 rounded-xl font-black text-xs uppercase tracking-wider text-white transition shadow-sm bg-purple-600 hover:bg-purple-700 cursor-pointer active:scale-95">
                    <i class="fa-solid fa-file-contract mr-1.5"></i> Contract Bevestigen
                </button>
            </form>
        </div>
    </div>

</div>
@endsection
