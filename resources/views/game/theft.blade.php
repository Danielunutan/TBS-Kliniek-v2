@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Purple TBS Kliniek Title Banner -->
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-purple-950 via-indigo-900 to-purple-900 p-6 sm:p-8 text-white shadow-lg border border-purple-800/40">
        <div class="absolute -right-10 -bottom-10 w-64 h-64 bg-purple-500/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-purple-500/20 border border-purple-400/30 flex items-center justify-center text-2xl text-purple-300 shadow-inner">
                    <i class="fa-solid fa-mask"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="text-[10px] font-black uppercase tracking-widest text-purple-300 bg-purple-900/80 px-2 py-0.5 rounded-md border border-purple-700/50">TBS Kliniek Illegaal</span>
                    </div>
                    <h1 class="text-xl sm:text-2xl font-black text-white tracking-tight mt-1">
                        Diefstal & Klusjes
                    </h1>
                    <p class="text-xs text-purple-200/80 mt-0.5 max-w-xl">
                        Zakrol medebewoners of waag een overval op gevoelige locaties in de kliniek. Pas op: de bewakers patrouilleren constant. Als je wordt betrapt ga je direct de isoleercel in!
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-2 text-xs font-bold">
                <span class="bg-emerald-500/20 text-emerald-300 px-3 py-1.5 rounded-xl border border-emerald-400/30">
                    <i class="fa-solid fa-check mr-1"></i> {{ $character->thefts_succeeded }} Geslaagd
                </span>
                <span class="bg-rose-500/20 text-rose-300 px-3 py-1.5 rounded-xl border border-rose-400/30">
                    <i class="fa-solid fa-xmark mr-1"></i> {{ $character->thefts_failed }} Mislukt
                </span>
            </div>
        </div>
    </div>
    <!-- PvP Inmate Robbery Card -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-xs p-6 space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-100 pb-3">
            <div>
                <h2 class="text-sm font-extrabold text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-users-viewfinder text-rose-600"></i>
                    <span>Medepatiënten Beroven (PvP Zakkenrollen)</span>
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">
                    Steel contant geld rechtstreeks van andere patiënten. Het slachtoffer ontvangt direct een incident-alarm in zijn/haar berichtenbox!
                </p>
            </div>
            <span class="text-[11px] font-bold bg-amber-50 text-amber-800 border border-amber-200 px-2.5 py-1 rounded-lg shrink-0">
                <i class="fa-solid fa-shield-halved text-amber-500 mr-1"></i> Bankgeld is beschermd
            </span>
        </div>

        @if($victims->isEmpty())
        <p class="text-xs text-slate-400 py-3 text-center">Er zijn momenteel geen andere patiënten binnen bereik.</p>
        @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3">
            @foreach($victims as $vic)
            <div class="bg-slate-50 rounded-xl border border-slate-200/80 p-3.5 flex flex-col justify-between gap-3 hover:border-slate-300 transition">
                <div class="flex items-center gap-3">
                    <img src="{{ $vic->avatar_url }}" alt="{{ $vic->name }}" class="w-10 h-10 rounded-xl object-cover border border-slate-200 shadow-2xs shrink-0">
                    <div class="min-w-0">
                        <h4 class="text-xs font-black text-slate-900 truncate">{{ $vic->name }}</h4>
                        <span class="text-[10px] text-slate-500 font-bold block">Level {{ $vic->level }} &bull; {{ $vic->gender === 'female' ? 'Vrouw' : 'Man' }}</span>
                    </div>
                </div>

                <div class="flex items-center justify-between pt-1 border-t border-slate-200/60">
                    <div>
                        <span class="text-[9px] font-extrabold uppercase text-slate-400 block">Op Zak</span>
                        <span class="text-xs font-black text-emerald-600">€{{ number_format($vic->cash, 0, ',', '.') }}</span>
                    </div>

                    <form method="POST" action="{{ route('theft.steal') }}">
                        @csrf
                        <input type="hidden" name="tier" value="pocket">
                        <input type="hidden" name="victim_id" value="{{ $vic->id }}">
                        <button type="submit" @if($character->energy < 8 || $character->isImprisoned()) disabled @endif 
                                class="bg-rose-600 hover:bg-rose-700 disabled:opacity-40 disabled:cursor-not-allowed text-white text-[11px] font-extrabold px-3 py-1.5 rounded-lg shadow-2xs transition active:scale-[0.98] flex items-center gap-1">
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

    <!-- Crime Tiers Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @foreach($tiers as $key => $tier)
        @php
            $hasLevel = $character->level >= $tier['required_level'];
            $hasEnergy = $character->energy >= $tier['energy_cost'];
        @endphp
        <div class="bg-white rounded-lg border {{ $hasLevel ? 'border-slate-200' : 'border-slate-300 opacity-75 bg-slate-50' }} shadow-sm overflow-hidden flex flex-col justify-between">
            <div class="p-5 space-y-3">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h2 class="text-base font-bold text-slate-900">{{ $tier['name'] }}</h2>
                        <span class="text-xs font-semibold text-purple-800">
                            Risico: {{ $tier['jail_minutes'] }} minuten isoleercel bij betrapping
                        </span>
                    </div>
                    <span class="px-2 py-0.5 rounded text-[11px] font-bold {{ $hasLevel ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }} shrink-0">
                        Min. Level {{ $tier['required_level'] }}
                    </span>
                </div>

                <!-- Specs -->
                <div class="grid grid-cols-3 gap-2 pt-2 text-xs">
                    <div class="bg-yellow-50/70 p-2 rounded border border-yellow-200">
                        <span class="text-[10px] text-yellow-800 uppercase font-bold block">Energie</span>
                        <span class="font-bold text-yellow-900"><i class="fa-solid fa-bolt text-yellow-600 mr-1"></i>{{ $tier['energy_cost'] }}</span>
                    </div>
                    <div class="bg-amber-50/70 p-2 rounded border border-amber-200">
                        <span class="text-[10px] text-amber-800 uppercase font-bold block">Buit</span>
                        <span class="font-bold text-amber-900"><i class="fa-solid fa-coins text-amber-600 mr-1"></i>€{{ number_format($tier['min_cash'], 0, ',', '.') }}–€{{ number_format($tier['max_cash'], 0, ',', '.') }}</span>
                    </div>
                    <div class="bg-purple-50/70 p-2 rounded border border-purple-200">
                        <span class="text-[10px] text-purple-800 uppercase font-bold block">Kans</span>
                        <span class="font-bold text-purple-900"><i class="fa-solid fa-percent text-purple-600 mr-1"></i>{{ $tier['base_chance'] }}%</span>
                    </div>
                </div>
            </div>

            <!-- Action Button -->
            <div class="px-5 py-3 bg-slate-50 border-t border-slate-200 flex items-center justify-between">
                <span class="text-xs text-slate-500 font-medium">
                    @if(! $hasLevel)
                        <i class="fa-solid fa-lock text-slate-400 mr-1"></i> Level {{ $tier['required_level'] }} vereist
                    @elseif(! $hasEnergy)
                        <i class="fa-solid fa-battery-empty text-yellow-600 mr-1"></i> Energie ontoereikend
                    @else
                        <i class="fa-solid fa-hand-holding-dollar text-emerald-600 mr-1"></i> Doelwit in het vizier
                    @endif
                </span>

                <form method="POST" action="{{ route('theft.steal') }}">
                    @csrf
                    <input type="hidden" name="tier" value="{{ $key }}">
                    <button type="submit" @if(! $hasLevel || ! $hasEnergy || $character->isImprisoned()) disabled @endif class="bg-rose-600 hover:bg-rose-500 disabled:opacity-40 disabled:cursor-not-allowed text-white font-bold px-4 py-2 rounded text-xs shadow transition flex items-center gap-1.5 cursor-pointer">
                        <i class="fa-solid fa-hand-sparkles"></i>
                        <span>Slag Slaan</span>
                    </button>
                </form>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Recent Crime History -->
    <div class="bg-white rounded-lg border border-slate-200 shadow-sm p-5">
        <h3 class="text-xs font-bold uppercase text-slate-800 tracking-wider flex items-center gap-2 mb-3">
            <i class="fa-solid fa-list-check text-purple-700"></i>
            <span>Jouw Recente Diefstalgeschiedenis</span>
        </h3>
        <div class="divide-y divide-slate-100 text-xs">
            @forelse($recentThefts as $rec)
            <div class="py-2.5 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    @if($rec->success)
                        <span class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center text-[10px]"><i class="fa-solid fa-check"></i></span>
                    @else
                        <span class="w-5 h-5 rounded-full bg-rose-100 text-rose-700 flex items-center justify-center text-[10px]"><i class="fa-solid fa-xmark"></i></span>
                    @endif
                    <span class="font-medium text-slate-800">{{ $rec->log_message }}</span>
                </div>
                <span class="text-[11px] text-slate-400">{{ $rec->created_at->diffForHumans() }}</span>
            </div>
            @empty
            <p class="text-slate-500 py-2">Nog geen diefstallen geregistreerd in je dossier.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
