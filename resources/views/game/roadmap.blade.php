@extends('layouts.app')

@section('title', 'Toekomst & Roadmap - TBS Kliniek')

@section('content')
<div class="space-y-6" x-data="{
    activeTab: 'planned',
    phaseFilter: 'all',
    matches(f) {
        if (this.phaseFilter === 'all') return true;
        if (this.phaseFilter === 'fase1' && f.phase.includes('Fase 1')) return true;
        if (this.phaseFilter === 'fase2' && f.phase.includes('Fase 2')) return true;
        if (this.phaseFilter === 'fase3' && f.phase.includes('Fase 3')) return true;
        return false;
    }
}">

    

    <!-- Clean TBS Kliniek Title Banner -->
    <div class="rounded-2xl bg-white dark:bg-slate-900 p-6 sm:p-8 text-slate-800 dark:text-slate-100 shadow-xs border border-slate-200/90 dark:border-slate-800 relative overflow-hidden">
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-5">
            <div class="flex items-start sm:items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-purple-50 dark:bg-purple-950/70 border border-purple-100 dark:border-purple-800/60 flex items-center justify-center text-2xl text-purple-600 dark:text-purple-400 shadow-2xs shrink-0">
                    <i class="fa-solid fa-compass"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="text-[10px] font-black uppercase tracking-widest text-purple-700 dark:text-purple-300 bg-purple-50 dark:bg-purple-950/80 px-2.5 py-0.5 rounded-md border border-purple-200 dark:border-purple-800/60">
                            TBS Kliniek Ontwikkeling
                        </span>
                        <span class="text-[10px] font-bold text-amber-700 dark:text-amber-300 bg-amber-50 dark:bg-amber-950/80 px-2 py-0.5 rounded-md border border-amber-200 dark:border-amber-800/60">
                            Open Beta
                        </span>
                    </div>
                    <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight mt-1">
                        Toekomst & Roadmap
                    </h1>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 max-w-2xl leading-relaxed">
                        Ontdek wat er binnenkort naar de TBS Kliniek komt of bekijk welke functies zojuist live zijn gezet. Breng jouw stem uit op de feature die jij als eerste wilt zien!
                    </p>
                </div>
            </div>

            <!-- Stats Pill Counters -->
            <div class="flex flex-wrap items-center gap-2 text-xs font-bold shrink-0">
                <span class="bg-purple-50 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 px-3.5 py-2 rounded-xl border border-purple-200 dark:border-purple-800 shadow-2xs flex items-center gap-1.5">
                    <i class="fa-solid fa-layer-group text-purple-600 dark:text-purple-400"></i>
                    <span>{{ count($features ?? []) }} Gepland</span>
                </span>
                <span class="bg-emerald-50 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 px-3.5 py-2 rounded-xl border border-emerald-200 dark:border-emerald-800 shadow-2xs flex items-center gap-1.5">
                    <i class="fa-solid fa-circle-check text-emerald-600 dark:text-emerald-400"></i>
                    <span>{{ count($completedFeatures ?? []) }} Reeds Live</span>
                </span>
            </div>
        </div>

        <!-- Main Tab Navigation -->
        <div class="mt-6 pt-5 border-t border-slate-100 dark:border-slate-800 flex flex-wrap items-center gap-2">
            <button type="button" @click="activeTab = 'planned'"
                    :class="activeTab === 'planned' ? 'bg-purple-600 text-white shadow-xs font-black' : 'bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold border border-slate-200/80 dark:border-slate-700'"
                    class="px-4 py-2 rounded-xl text-xs transition cursor-pointer flex items-center gap-2">
                <i class="fa-solid fa-rocket text-xs"></i>
                <span>Geplande Features & Stemmen ({{ count($features) }})</span>
            </button>
            <button type="button" @click="activeTab = 'completed'"
                    :class="activeTab === 'completed' ? 'bg-emerald-600 text-white shadow-xs font-black' : 'bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold border border-slate-200/80 dark:border-slate-700'"
                    class="px-4 py-2 rounded-xl text-xs transition cursor-pointer flex items-center gap-2">
                <i class="fa-solid fa-circle-check text-xs"></i>
                <span>Recent Uitgebracht & Live ({{ count($completedFeatures ?? []) }})</span>
            </button>
        </div>
    </div>

    <!-- TAB 1: PLANNED FEATURES -->
    <div x-show="activeTab === 'planned'" class="space-y-6">
        <!-- Filter Bar & Helper Notice -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/90 dark:border-slate-800 shadow-xs p-3.5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
            <div class="flex flex-wrap items-center gap-1.5 text-xs">
                <button @click="phaseFilter = 'all'"
                    :class="phaseFilter === 'all' ? 'bg-purple-600 text-white font-black shadow-2xs' : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 font-bold border border-slate-200/80 dark:border-slate-700'"
                    class="px-3.5 py-1.5 rounded-xl transition cursor-pointer">
                    Alles ({{ count($features) }})
                </button>
                <button @click="phaseFilter = 'fase1'"
                    :class="phaseFilter === 'fase1' ? 'bg-purple-600 text-white font-black shadow-2xs' : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 font-bold border border-slate-200/80 dark:border-slate-700'"
                    class="px-3.5 py-1.5 rounded-xl transition cursor-pointer flex items-center gap-1.5">
                    <i class="fa-solid fa-fire text-[10px]" :class="phaseFilter === 'fase1' ? 'text-amber-300' : 'text-amber-500 dark:text-amber-300'"></i> Fase 1: Binnenkort
                </button>
                <button @click="phaseFilter = 'fase2'"
                    :class="phaseFilter === 'fase2' ? 'bg-purple-600 text-white font-black shadow-2xs' : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 font-bold border border-slate-200/80 dark:border-slate-700'"
                    class="px-3.5 py-1.5 rounded-xl transition cursor-pointer flex items-center gap-1.5">
                    <i class="fa-solid fa-clock text-[10px]"></i> Fase 2: Gepland
                </button>
                <button @click="phaseFilter = 'fase3'"
                    :class="phaseFilter === 'fase3' ? 'bg-purple-600 text-white font-black shadow-2xs' : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 font-bold border border-slate-200/80 dark:border-slate-700'"
                    class="px-3.5 py-1.5 rounded-xl transition cursor-pointer flex items-center gap-1.5">
                    <i class="fa-solid fa-lightbulb text-[10px]" :class="phaseFilter === 'fase3' ? 'text-amber-300' : 'text-amber-500 dark:text-amber-300'"></i> Fase 3: Toekomst
                </button>
            </div>

            <div class="text-[11px] text-slate-500 dark:text-slate-400 font-semibold flex items-center gap-1.5">
                <i class="fa-solid fa-circle-check text-emerald-500"></i>
                <span>Jouw stem beïnvloedt rechtstreeks de ontwikkelvolgorde.</span>
            </div>
        </div>

        <!-- Feature Cards Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach($features as $featureKey => $f)
            <div x-show="matches({ phase: @js($f['phase']) })"
                class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/90 dark:border-slate-800 shadow-xs hover:border-purple-300 dark:hover:border-purple-700/80 transition-all duration-200 flex flex-col justify-between p-5 sm:p-6 {{ $f['has_voted'] ? 'ring-2 ring-purple-400/40 bg-purple-50/10 dark:bg-purple-950/20' : '' }}">
                
                <div class="space-y-3.5">
                    <!-- Header with Icon & Badges -->
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-start gap-3.5 min-w-0">
                            <div class="w-11 h-11 rounded-xl border border-slate-200 dark:border-slate-800 flex items-center justify-center text-lg shrink-0 shadow-2xs {{ $f['icon_style'] }}">
                                <i class="fa-solid {{ $f['icon'] }}"></i>
                            </div>
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-1.5 mb-1">
                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-black uppercase tracking-wider {{ $f['status_badge'] }} border">
                                        {{ $f['status'] }}
                                    </span>
                                    <span class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">
                                        &bull; {{ $f['category'] }}
                                    </span>
                                </div>
                                <h2 class="text-sm sm:text-base font-black text-slate-900 dark:text-white leading-snug">
                                    {{ $f['title'] }}
                                </h2>
                                <p class="text-[11px] font-bold text-purple-700 dark:text-purple-400 mt-0.5">
                                    {{ $f['subtitle'] }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Description -->
                    <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed font-normal">
                        {{ $f['description'] }}
                    </p>

                    <!-- Kernonderdelen Checklist Box -->
                    <div class="bg-slate-50/90 dark:bg-slate-800/60 rounded-xl p-3 border border-slate-200/80 dark:border-slate-700/70 space-y-1.5">
                        <span class="text-[9px] font-black uppercase text-slate-400 dark:text-slate-500 tracking-wider block">Kernonderdelen:</span>
                        <ul class="space-y-1 text-xs text-slate-700 dark:text-slate-300 font-medium">
                            @foreach($f['highlights'] ?? [] as $highlight)
                            <li class="flex items-center gap-2">
                                <i class="fa-solid fa-check text-purple-600 dark:text-purple-400 text-[10px] shrink-0"></i>
                                <span class="text-[11px]">{{ $highlight }}</span>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                </div>

                <!-- Card Footer: Votes & Action Button -->
                <div class="border-t border-slate-100 dark:border-slate-800 pt-3.5 mt-4 flex items-center justify-between gap-3">
                    <div class="flex items-center gap-2 text-xs">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70 font-black text-slate-800 dark:text-slate-200 text-[11px]">
                            <i class="fa-solid fa-fire text-amber-500 text-[10px]"></i>
                            <span>{{ number_format($f['votes'] ?? 0, 0, ',', '.') }} Stemmen</span>
                        </span>
                        <span class="text-[10px] font-bold text-slate-400 hidden sm:inline">
                            {{ $f['phase'] }}
                        </span>
                    </div>

                    <!-- Vote Form Button -->
                    <form action="{{ route('roadmap.vote', $featureKey) }}" method="POST">
                        @csrf
                        @if($f['has_voted'])
                        <button type="submit" 
                            class="px-3.5 py-1.5 rounded-xl bg-purple-50 dark:bg-purple-950/60 hover:bg-purple-100 dark:hover:bg-purple-900/60 border border-purple-200/80 dark:border-purple-800/70 text-purple-800 dark:text-purple-200 font-extrabold text-xs transition-all duration-150 active:scale-95 flex items-center gap-1.5 cursor-pointer shadow-2xs">
                            <i class="fa-solid fa-circle-check text-purple-600 dark:text-purple-400"></i>
                            <span>Gestemd! (Klik om in te trekken)</span>
                        </button>
                        @else
                        <button type="submit" 
                            class="px-4 py-2 rounded-xl bg-purple-600 hover:bg-purple-700 dark:bg-purple-600 dark:hover:bg-purple-500 active:scale-95 text-white font-black text-xs transition-all duration-150 flex items-center gap-1.5 cursor-pointer shadow-xs">
                            <i class="fa-solid fa-thumbs-up text-amber-300 text-[10px]"></i>
                            <span>Stem op deze feature</span>
                        </button>
                        @endif
                    </form>
                </div>

            </div>
            @endforeach
        </div>
    </div>

    <!-- TAB 2: COMPLETED & RELEASED FEATURES -->
    <div x-show="activeTab === 'completed'" class="space-y-6">
        <div class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/60 text-xs text-emerald-800 dark:text-emerald-200 font-bold flex items-center justify-between gap-4">
            <div class="flex items-center gap-2.5">
                <i class="fa-solid fa-circle-check text-emerald-600 dark:text-emerald-400 text-base shrink-0"></i>
                <span>Onderstaande systemen stonden eerder op de roadmap en zijn inmiddels <strong>volledig gebouwd, getest en live</strong> speelbaar in de kliniek!</span>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach($completedFeatures ?? [] as $compKey => $c)
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/90 dark:border-slate-800 shadow-xs flex flex-col justify-between p-5 sm:p-6">
                <div class="space-y-3">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-start gap-3.5 min-w-0">
                            <div class="w-11 h-11 rounded-xl border border-slate-200 dark:border-slate-800 flex items-center justify-center text-lg shrink-0 shadow-2xs {{ $c['icon_style'] }}">
                                <i class="fa-solid {{ $c['icon'] }}"></i>
                            </div>
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-1.5 mb-1">
                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-black uppercase tracking-wider {{ $c['badge_style'] }} border">
                                        {{ $c['badge'] }}
                                    </span>
                                    <span class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">
                                        &bull; {{ $c['category'] }}
                                    </span>
                                </div>
                                <h3 class="text-sm sm:text-base font-black text-slate-900 dark:text-white leading-snug">
                                    {{ $c['title'] }}
                                </h3>
                                <p class="text-[11px] font-bold text-emerald-700 dark:text-emerald-400 mt-0.5">
                                    {{ $c['subtitle'] }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed font-normal">
                        {{ $c['description'] }}
                    </p>
                </div>

                <div class="border-t border-slate-100 dark:border-slate-800 pt-3.5 mt-4 flex items-center justify-between gap-3">
                    <span class="text-[11px] font-bold text-slate-400 dark:text-slate-500 flex items-center gap-1.5">
                        <i class="fa-solid fa-check-double text-emerald-500"></i>
                        <span>Actief in spelversie Open Beta</span>
                    </span>

                    @if(\Illuminate\Support\Facades\Route::has($c['route']))
                    <a href="{{ route($c['route']) }}" class="px-4 py-2 rounded-xl bg-purple-600 hover:bg-purple-700 dark:bg-purple-600 dark:hover:bg-purple-500 active:scale-95 text-white font-black text-xs transition-all duration-150 inline-flex items-center gap-1.5 shadow-xs cursor-pointer">
                        <span>{{ $c['action_label'] }}</span>
                        <i class="fa-solid fa-arrow-right text-[10px]"></i>
                    </a>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Suggestion Box Bottom -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-6 shadow-xs flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div class="flex items-start gap-3.5">
            <div class="w-12 h-12 rounded-xl bg-purple-50 dark:bg-purple-950/70 border border-purple-100 dark:border-purple-800/60 text-purple-600 dark:text-purple-400 flex items-center justify-center text-xl shrink-0 shadow-2xs">
                <i class="fa-solid fa-lightbulb"></i>
            </div>
            <div>
                <h3 class="text-sm font-black text-slate-900 dark:text-white">Heb jij zelf een briljant idee voor de TBS Kliniek?</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 leading-relaxed">
                    Mis je een specifiek misdrijf, mini-game, bendesysteem of een hilarische gebeurtenis? Deel het met ons via een suggestie-ticket!
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2 shrink-0 w-full sm:w-auto">
            <a href="{{ route('help.tickets.create') }}" class="w-full sm:w-auto px-4 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-700 dark:bg-purple-600 dark:hover:bg-purple-500 active:scale-95 text-white font-black text-xs transition-all duration-150 text-center shadow-xs inline-flex items-center justify-center gap-1.5 cursor-pointer">
                <i class="fa-solid fa-paper-plane text-[10px]"></i>
                <span>Suggestie Indienen</span>
            </a>
        </div>
    </div>

</div>
@endsection
