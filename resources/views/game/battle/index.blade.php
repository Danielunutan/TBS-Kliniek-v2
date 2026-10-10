@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{ 
    modalOpen: false, 
    modalCategory: '', 
    modalScores: [], 
    loadingScores: false,
    secReset: {{ (int) $secondsUntilReset }},
    formatCountdown(sec) {
        const total = Math.max(0, Math.floor(sec));
        const h = String(Math.floor(total / 3600)).padStart(2, '0');
        const m = String(Math.floor((total % 3600) / 60)).padStart(2, '0');
        const s = String(total % 60).padStart(2, '0');
        return `${h}:${m}:${s}`;
    },
    openCategoryDetails(catKey, catTitle) {
        this.modalOpen = true;
        this.modalCategory = catTitle;
        this.loadingScores = true;
        this.modalScores = [];
        fetch('{{ url('/24-uur-battle/categorie') }}/' + catKey)
            .then(res => res.json())
            .then(data => {
                this.modalScores = data.scores;
                this.loadingScores = false;
            })
            .catch(() => {
                this.loadingScores = false;
            });
    }
}" x-init="if (secReset > 0) { setInterval(() => { secReset--; if (secReset <= 0) window.location.reload(); }, 1000); }">

    <!-- Top Hero Banner -->
    <div class="rounded-2xl shadow-sm p-6 sm:p-8 text-white relative overflow-hidden bg-slate-950 border border-purple-900/40">
        <!-- Background Banner Asset (TBS Paarse Style) -->
        <div class="absolute inset-0 z-0 pointer-events-none">
            <img src="{{ asset('assets/illustrations/banner_battle.jpg') }}" alt="24 Uur Battle" class="w-full h-full object-cover object-center opacity-45">
            <div class="absolute inset-0 bg-gradient-to-r from-slate-950 via-slate-950/85 to-purple-950/70"></div>
        </div>

        <div class="relative z-10 flex flex-col md:flex-row items-center justify-between gap-6">
            <div class="space-y-2 text-center md:text-left">
                <div class="inline-flex items-center gap-2 bg-purple-500/20 text-purple-300 text-xs font-bold px-3 py-1 rounded-full border border-purple-400/20">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                    <span>Automatische 24-Uurs Dagbattle</span>
                </div>
                <h1 class="text-2xl sm:text-4xl font-extrabold tracking-tight text-white uppercase">
                    DE 24 UUR <span class="text-purple-300">Battle</span>
                </h1>
                <p class="text-slate-300 text-xs sm:text-sm font-medium max-w-xl leading-relaxed">
                    Strijd dagelijks mee om de absolute macht in de kliniek en win unieke beloningen zoals <strong class="text-white underline">XXL Koffers 🧳</strong>, Sterren ⭐ en Maskers 🎭 als je in de top 3 eindigt!
                </p>
            </div>

            <!-- Countdown Timer Card -->
            <div class="bg-white/10 backdrop-blur-xs border border-white/15 rounded-xl p-5 text-center shrink-0 shadow-xs min-w-[210px]">
                <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-300 block mb-1">
                    Volgende Reset & Uitbetaling
                </span>
                <div class="text-2xl sm:text-3xl font-black font-mono text-amber-300 tracking-wider" 
                     x-text="formatCountdown(secReset)">
                    {{ $resetTimeFormatted }}
                </div>
                <span class="text-[10px] text-slate-400 block mt-1 font-medium">Prijzen worden direct gestort</span>
            </div>
        </div>
    </div>

    <!-- 7 BATTLE CATEGORY CARDS GRID -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        @foreach($categories as $catKey => $cat)
        <div class="bg-white border border-slate-200 hover:border-purple-300 transition-all rounded-xl p-5 shadow-xs flex flex-col justify-between space-y-4">
            <!-- Header with Category Title & Clickable Arrow -->
            <div>
                <button type="button" 
                        @click="openCategoryDetails('{{ $catKey }}', '{{ $cat['title'] }}')"
                        class="w-full flex items-center justify-between pb-3 border-b border-slate-100 text-left group cursor-pointer">
                    <span class="text-xs font-extrabold uppercase tracking-wider text-slate-900 group-hover:text-purple-600 transition flex items-center gap-2">
                        <i class="fa-solid {{ $cat['icon'] }} text-purple-600"></i>
                        <span>{{ $cat['title'] }}</span>
                    </span>
                    <i class="fa-solid fa-chevron-right text-[11px] text-slate-400 group-hover:translate-x-1 group-hover:text-purple-600 transition"></i>
                </button>

                <!-- Rewards List (1, 2, 3) -->
                <div class="space-y-2 pt-3 text-xs">
                    <!-- Rank 1 Reward -->
                    <div class="flex items-center justify-between p-2.5 rounded-lg bg-amber-50/70 border border-amber-200/70 transition">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <span class="w-6 h-6 rounded-md bg-amber-400 text-slate-950 flex items-center justify-center text-xs font-black shrink-0 shadow-xs">
                                1
                            </span>
                            <span class="font-extrabold text-slate-900 text-xs truncate">
                                {{ $cat['rewards'][1]['label'] }}
                            </span>
                        </div>
                        <span class="text-[10px] font-black uppercase text-amber-800 bg-amber-200/60 px-2 py-0.5 rounded shrink-0">Hoofdprijs</span>
                    </div>

                    <!-- Rank 2 Reward -->
                    <div class="flex items-center justify-between p-2 rounded-lg bg-slate-50 border border-slate-100 transition">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <span class="w-6 h-6 rounded-md bg-slate-200 text-slate-700 flex items-center justify-center text-xs font-black shrink-0">
                                2
                            </span>
                            <span class="font-bold text-slate-700 text-xs truncate">
                                {{ $cat['rewards'][2]['label'] }}
                            </span>
                        </div>
                    </div>

                    <!-- Rank 3 Reward -->
                    <div class="flex items-center justify-between p-2 rounded-lg bg-slate-50 border border-slate-100 transition">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <span class="w-6 h-6 rounded-md bg-orange-100 text-orange-900 flex items-center justify-center text-xs font-black shrink-0">
                                3
                            </span>
                            <span class="font-bold text-slate-700 text-xs truncate">
                                {{ $cat['rewards'][3]['label'] }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Current Leaderboard in Category Preview -->
            <div class="pt-3 border-t border-slate-100">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">
                        Huidige Top 3:
                    </span>
                    <button type="button" 
                            @click="openCategoryDetails('{{ $catKey }}', '{{ $cat['title'] }}')"
                            class="text-[10px] font-bold text-purple-600 hover:text-purple-800 transition cursor-pointer">
                        Bekijk alle &rarr;
                    </button>
                </div>
                <div class="space-y-1.5">
                    @forelse($cat['top3'] ?? [] as $topPlayer)
                    <div class="flex items-center justify-between px-2.5 py-1.5 rounded-lg bg-slate-50 border border-slate-100 text-xs hover:bg-purple-50/40 transition">
                        <div class="flex items-center gap-2 min-w-0">
                            <span class="w-4 font-mono font-black text-center {{ $topPlayer['rank'] === 1 ? 'text-amber-500' : 'text-slate-400' }}">#{{ $topPlayer['rank'] }}</span>
                            <span class="font-bold text-slate-800 truncate">{{ $topPlayer['name'] }}</span>
                        </div>
                        <span class="font-mono font-extrabold text-purple-700 text-xs shrink-0 ml-2">{{ number_format($topPlayer['score'], 0, ',', '.') }}</span>
                    </div>
                    @empty
                    <div class="text-xs text-slate-400 italic py-2 text-center bg-slate-50 rounded-lg">Nog geen activiteit vandaag</div>
                    @endforelse
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Bottom Podium Ranglijst -->
    <div class="bg-white border border-slate-200 rounded-xl p-6 shadow-xs space-y-5">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <div class="flex items-center gap-2 text-xs font-extrabold uppercase tracking-wider text-slate-900">
                <i class="fa-solid fa-trophy text-amber-500"></i>
                <span>RANGLIJST TOP PATIËNTEN</span>
            </div>
            <a href="{{ route('leaderboard.index') }}" class="text-xs font-bold text-purple-600 hover:text-purple-800 transition">
                Volledige ranglijst bekijken &rarr;
            </a>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-5 lg:grid-cols-10 gap-3 pt-2">
            @foreach($topRankings as $rankIdx => $topChar)
            <div class="flex flex-col items-center text-center group">
                <div class="relative mb-2">
                    <img src="{{ $topChar->avatar_url }}" alt="{{ $topChar->name }}" 
                         onerror="this.onerror=null; this.src='{{ asset('assets/characters/avatar_male_1.jpg') }}';"
                         class="w-14 h-14 sm:w-16 sm:h-16 rounded-full object-cover border-2 {{ $rankIdx === 0 ? 'border-amber-400 ring-2 ring-amber-400/30' : ($rankIdx === 1 ? 'border-purple-400' : 'border-slate-200') }} group-hover:scale-105 transition-transform shadow-xs">
                    
                    <span class="absolute -top-1 -left-1 w-5 h-5 rounded-full {{ $rankIdx === 0 ? 'bg-amber-400 text-slate-950 font-black' : ($rankIdx < 3 ? 'bg-purple-600 text-white font-bold' : 'bg-slate-800 text-white font-bold') }} text-[11px] flex items-center justify-center shadow-xs">
                        {{ $rankIdx + 1 }}
                    </span>

                    <span class="absolute -bottom-1 -right-1 w-4 h-4 rounded-full bg-emerald-500 text-white text-[9px] flex items-center justify-center">
                        <i class="fa-solid fa-check"></i>
                    </span>
                </div>

                <a href="{{ route('profile.show', $topChar->name) }}" class="text-xs font-bold text-slate-900 group-hover:text-purple-600 transition truncate w-full block" title="{{ $topChar->name }}">
                    {{ $topChar->name }}
                </a>
                <span class="text-[10px] font-bold text-slate-400">
                    {{ number_format($topChar->respect, 0, ',', '.') }}
                </span>
            </div>
            @endforeach
        </div>
    </div>

    <!-- MODAL FOR FULL CATEGORY LEADERBOARD -->
    <div x-show="modalOpen" 
         x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-xs p-4"
         @click.self="modalOpen = false">
        <div class="bg-white border border-slate-200 rounded-2xl max-w-lg w-full p-6 text-slate-900 shadow-xl space-y-4 relative max-h-[85vh] flex flex-col">
            <!-- Modal Header -->
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2 font-extrabold text-slate-900 text-sm uppercase tracking-wider">
                    <i class="fa-solid fa-trophy text-amber-500"></i>
                    <span x-text="modalCategory"></span>
                </div>
                <button @click="modalOpen = false" class="text-slate-400 hover:text-slate-700 transition text-lg cursor-pointer">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <!-- Modal Content -->
            <div class="overflow-y-auto flex-1 space-y-2 pr-1">
                <template x-if="loadingScores">
                    <div class="py-12 text-center text-purple-600 font-bold text-xs animate-pulse">
                        <i class="fa-solid fa-spinner fa-spin text-lg mb-2 block"></i>
                        Ranglijst ophalen...
                    </div>
                </template>

                <template x-if="!loadingScores && modalScores.length === 0">
                    <div class="py-12 text-center text-slate-400 font-medium text-xs">
                        Nog geen scores geregistreerd in deze categorie vandaag!
                    </div>
                </template>

                <template x-if="!loadingScores && modalScores.length > 0">
                    <div class="space-y-1.5">
                        <template x-for="item in modalScores" :key="item.rank">
                            <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 border border-slate-100 hover:border-purple-200 transition">
                                <div class="flex items-center gap-3">
                                    <span class="w-6 font-bold text-sm text-purple-700 text-center" x-text="'#' + item.rank"></span>
                                    <img :src="item.avatar" 
                                         onerror="this.onerror=null; this.src='{{ asset('assets/characters/avatar_male_1.jpg') }}';" 
                                         class="w-8 h-8 rounded-full object-cover border border-slate-200 shadow-xs">
                                    <div>
                                        <span class="font-bold text-slate-900 text-xs block" x-text="item.name"></span>
                                        <span class="text-[10px] text-slate-400" x-text="'Level ' + item.level"></span>
                                    </div>
                                </div>
                                <span class="font-mono font-bold text-purple-700 text-xs" x-text="item.score"></span>
                            </div>
                        </template>
                    </div>
                </template>
            </div>

            <!-- Modal Footer -->
            <div class="pt-3 border-t border-slate-100 text-center">
                <button type="button" @click="modalOpen = false" class="px-5 py-2 rounded-lg bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs uppercase cursor-pointer transition">
                    Sluiten
                </button>
            </div>
        </div>
    </div>

</div>
@endsection
