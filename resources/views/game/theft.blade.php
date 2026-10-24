@extends('layouts.app')

@section('title', 'Diefstal & Klusjes - TBS Kliniek')

@section('content')
<div class="space-y-6">

    <!-- HERO HEADER -->
    <div class="rounded-2xl bg-white dark:bg-slate-900 p-6 sm:p-7 text-slate-800 dark:text-slate-100 shadow-xs border border-slate-200 dark:border-slate-800 relative overflow-hidden">
        <div class="absolute -right-10 -bottom-10 w-48 h-48 bg-purple-500/10 dark:bg-purple-500/5 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-purple-50 dark:bg-purple-950/60 border border-purple-100 dark:border-purple-800/80 flex items-center justify-center text-2xl text-purple-600 dark:text-purple-400 shadow-2xs shrink-0">
                    <i class="fa-solid fa-mask"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="text-[10px] font-black uppercase tracking-widest text-purple-700 dark:text-purple-300 bg-purple-50 dark:bg-purple-950/60 px-2.5 py-0.5 rounded-full border border-purple-200 dark:border-purple-800">
                            TBS Kliniek Illegaal
                        </span>
                    </div>
                    <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight mt-1">
                        Diefstal & Klusjes
                    </h1>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 max-w-xl leading-relaxed">
                        Zakrol medebewoners of waag een overval op gevoelige locaties in de kliniek. Pas op: de bewakers patrouilleren constant. Als je wordt betrapt ga je direct de isoleercel in!
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-2 text-xs font-bold shrink-0">
                <span class="bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 px-3 py-1.5 rounded-xl border border-emerald-200 dark:border-emerald-800 flex items-center gap-1.5">
                    <i class="fa-solid fa-check text-emerald-600 dark:text-emerald-400"></i> {{ $character->thefts_succeeded }} Geslaagd
                </span>
                <span class="bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 px-3 py-1.5 rounded-xl border border-rose-200 dark:border-rose-800 flex items-center gap-1.5">
                    <i class="fa-solid fa-xmark text-rose-600 dark:text-rose-400"></i> {{ $character->thefts_failed }} Mislukt
                </span>
            </div>
        </div>
    </div>

    <!-- PVP INMATE ROBBERY CARD -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xs p-6 space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-100 dark:border-slate-800 pb-3">
            <div>
                <h2 class="text-sm font-black text-slate-900 dark:text-white flex items-center gap-2 tracking-tight">
                    <i class="fa-solid fa-users-viewfinder text-rose-600 dark:text-rose-400"></i>
                    <span>Medepatiënten Beroven (PvP Zakkenrollen)</span>
                </h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                    Steel contant geld rechtstreeks van andere patiënten. Het slachtoffer ontvangt direct een incident-alarm in zijn/haar berichtenbox!
                </p>
            </div>
            <span class="text-[11px] font-bold bg-amber-50 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-800 px-2.5 py-1 rounded-xl shrink-0 flex items-center gap-1.5">
                <i class="fa-solid fa-shield-halved text-amber-500"></i> Bankgeld is beschermd
            </span>
        </div>

        @if($victims->isEmpty())
            <p class="text-xs text-slate-400 dark:text-slate-500 py-4 text-center">Er zijn momenteel geen andere patiënten binnen bereik.</p>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3">
                @foreach($victims as $vic)
                <div class="bg-slate-50 dark:bg-slate-800/60 rounded-xl border border-slate-200/80 dark:border-slate-700/80 p-3.5 flex flex-col justify-between gap-3 hover:border-purple-300 dark:hover:border-purple-600 transition">
                    <div class="flex items-center gap-3">
                        <div class="relative shrink-0">
                            <img src="{{ $vic->avatar_url }}" alt="{{ $vic->name }}" class="w-10 h-10 rounded-xl object-cover border border-slate-200 dark:border-slate-700 shadow-2xs">
                            <span class="absolute -bottom-1 -right-1 w-2.5 h-2.5 rounded-full border-2 border-white dark:border-slate-900 {{ $vic->isOnline() ? 'bg-emerald-500' : 'bg-slate-400' }}" title="{{ $vic->online_status_label }}"></span>
                        </div>
                        <div class="min-w-0">
                            <h4 class="text-xs font-black text-slate-900 dark:text-white truncate">{{ $vic->name }}</h4>
                            <span class="text-[10px] {{ $vic->isOnline() ? 'text-emerald-600 dark:text-emerald-400 font-bold' : 'text-slate-400' }} block">Level {{ $vic->level }} &bull; {{ $vic->isOnline() ? 'Online' : 'Offline' }}</span>
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-2 border-t border-slate-200/60 dark:border-slate-700/60">
                        <div>
                            <span class="text-[9px] font-extrabold uppercase text-slate-400 block leading-none mb-0.5">Op Zak</span>
                            <span class="text-xs font-black text-emerald-600 dark:text-emerald-400 font-mono">€{{ number_format($vic->cash, 0, ',', '.') }}</span>
                        </div>

                        <form method="POST" action="{{ route('theft.steal') }}">
                            @csrf
                            <input type="hidden" name="tier" value="pocket">
                            <input type="hidden" name="victim_id" value="{{ $vic->id }}">
                            <button type="submit" @if($character->energy < 8 || $character->isImprisoned()) disabled @endif 
                                    class="bg-rose-600 hover:bg-rose-700 disabled:opacity-40 disabled:cursor-not-allowed text-white text-[11px] font-extrabold px-3 py-1.5 rounded-lg shadow-2xs transition active:scale-[0.98] flex items-center gap-1 cursor-pointer">
                                <i class="fa-solid fa-hand-holding-dollar text-[10px]"></i>
                                <span>Rollen (8 EP)</span>
                            </button>
                        </form>
                    </div>
                </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- CRIME TIERS GRID -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @foreach($tiers as $key => $tier)
        @php
            $hasLevel = $character->level >= $tier['required_level'];
            $hasEnergy = $character->energy >= $tier['energy_cost'];
        @endphp
        <div class="bg-white dark:bg-slate-900 rounded-2xl border {{ $hasLevel ? 'border-slate-200 dark:border-slate-800 hover:border-purple-300 dark:hover:border-purple-600' : 'border-slate-200 dark:border-slate-800/50 opacity-75' }} shadow-xs overflow-hidden flex flex-col justify-between transition">
            <div class="p-5 sm:p-6 space-y-3">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h2 class="text-base font-black text-slate-900 dark:text-white">{{ $tier['name'] }}</h2>
                        <span class="text-xs font-semibold text-purple-600 dark:text-purple-400">
                            Risico: {{ $tier['jail_minutes'] }} minuten isoleercel bij betrapping
                        </span>
                    </div>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider {{ $hasLevel ? 'bg-emerald-100 dark:bg-emerald-950/80 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800' : 'bg-rose-100 dark:bg-rose-950/80 text-rose-800 dark:text-rose-300 border border-rose-200 dark:border-rose-800' }} shrink-0">
                        Min. Level {{ $tier['required_level'] }}
                    </span>
                </div>

                <!-- Specs -->
                <div class="grid grid-cols-3 gap-2 pt-2 text-xs">
                    <div class="bg-slate-50 dark:bg-slate-800/60 p-2.5 rounded-xl border border-slate-100 dark:border-slate-700/60">
                        <span class="text-[9px] text-slate-400 uppercase font-black tracking-wider block">Energie</span>
                        <span class="font-black text-amber-600 dark:text-amber-400 mt-0.5 block"><i class="fa-solid fa-bolt mr-1"></i>{{ $tier['energy_cost'] }}</span>
                    </div>
                    <div class="bg-slate-50 dark:bg-slate-800/60 p-2.5 rounded-xl border border-slate-100 dark:border-slate-700/60">
                        <span class="text-[9px] text-slate-400 uppercase font-black tracking-wider block">Buit</span>
                        <span class="font-black text-emerald-600 dark:text-emerald-400 font-mono mt-0.5 block truncate"><i class="fa-solid fa-coins mr-1"></i>€{{ number_format($tier['min_cash'], 0, ',', '.') }}–{{ number_format($tier['max_cash'], 0, ',', '.') }}</span>
                    </div>
                    <div class="bg-slate-50 dark:bg-slate-800/60 p-2.5 rounded-xl border border-slate-100 dark:border-slate-700/60">
                        <span class="text-[9px] text-slate-400 uppercase font-black tracking-wider block">Kans</span>
                        <span class="font-black text-purple-600 dark:text-purple-400 mt-0.5 block"><i class="fa-solid fa-percent mr-1"></i>{{ $tier['base_chance'] }}%</span>
                    </div>
                </div>
            </div>

            <!-- Action Button -->
            <div class="px-5 py-3.5 bg-slate-50/70 dark:bg-slate-800/50 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
                <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">
                    @if(! $hasLevel)
                        <i class="fa-solid fa-lock text-slate-400 mr-1"></i> Level {{ $tier['required_level'] }} vereist
                    @elseif(! $hasEnergy)
                        <i class="fa-solid fa-battery-empty text-amber-500 mr-1"></i> Energie ontoereikend
                    @else
                        <i class="fa-solid fa-hand-holding-dollar text-emerald-600 dark:text-emerald-400 mr-1"></i> Doelwit gereed
                    @endif
                </span>

                <form method="POST" action="{{ route('theft.steal') }}">
                    @csrf
                    <input type="hidden" name="tier" value="{{ $key }}">
                    <button type="submit" @if(! $hasLevel || ! $hasEnergy || $character->isImprisoned()) disabled @endif 
                            class="bg-rose-600 hover:bg-rose-700 disabled:opacity-40 disabled:cursor-not-allowed text-white font-black px-4 py-2 rounded-xl text-xs shadow-xs transition active:scale-95 flex items-center gap-1.5 cursor-pointer">
                        <i class="fa-solid fa-hand-sparkles"></i>
                        <span>Slag Slaan</span>
                    </button>
                </form>
            </div>
        </div>
        @endforeach
    </div>

    <!-- RECENT CRIME HISTORY -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xs p-5 sm:p-6">
        <h3 class="text-xs font-black uppercase text-slate-900 dark:text-white tracking-wider flex items-center gap-2 mb-3">
            <i class="fa-solid fa-list-check text-purple-600 dark:text-purple-400"></i>
            <span>Jouw Recente Diefstalgeschiedenis</span>
        </h3>
        <div class="divide-y divide-slate-100 dark:divide-slate-800 text-xs">
            @forelse($recentThefts as $rec)
            <div class="py-2.5 flex items-center justify-between gap-3">
                <div class="flex items-center gap-2 min-w-0">
                    @if($rec->success)
                        <span class="w-5 h-5 rounded-full bg-emerald-100 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300 flex items-center justify-center text-[10px] shrink-0 font-bold"><i class="fa-solid fa-check"></i></span>
                    @else
                        <span class="w-5 h-5 rounded-full bg-rose-100 dark:bg-rose-950 text-rose-700 dark:text-rose-300 flex items-center justify-center text-[10px] shrink-0 font-bold"><i class="fa-solid fa-xmark"></i></span>
                    @endif
                    <span class="font-medium text-slate-800 dark:text-slate-200 truncate">{{ $rec->log_message }}</span>
                </div>
                <span class="text-[11px] text-slate-400 shrink-0 font-mono">{{ $rec->created_at->diffForHumans() }}</span>
            </div>
            @empty
            <p class="text-slate-400 dark:text-slate-500 py-3 text-center">Nog geen diefstallen geregistreerd in je dossier.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
