@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-black text-slate-900 uppercase tracking-wide flex items-center gap-2">
                <i class="fa-solid fa-mask text-purple-700"></i>
                <span>Diefstal & Criminaliteit</span>
            </h1>
            <p class="text-xs text-slate-600 mt-1">
                Zakrol medebewoners of waag een overval op gevoelige locaties in de kliniek. Pas op: de bewakers patrouilleren constant. Als je wordt betrapt ga je direct de isoleercel in!
            </p>
        </div>
        <div class="flex items-center gap-2 text-xs font-bold">
            <span class="bg-emerald-100 text-emerald-800 px-3 py-1.5 rounded border border-emerald-200">
                <i class="fa-solid fa-check mr-1"></i> {{ $character->thefts_succeeded }} Geslaagd
            </span>
            <span class="bg-rose-100 text-rose-800 px-3 py-1.5 rounded border border-rose-200">
                <i class="fa-solid fa-xmark mr-1"></i> {{ $character->thefts_failed }} Mislukt
            </span>
        </div>
    </div>

    <!-- Crime Tiers Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @foreach($tiers as $key => $tier)
        @php
            $hasLevel = $character->level >= $tier['required_level'];
            $hasEnergy = $character->energy >= $tier['energy_cost'];
        @endphp
        <div class="bg-white rounded-xl border {{ $hasLevel ? 'border-slate-200' : 'border-slate-300 opacity-75 bg-slate-50' }} shadow-sm overflow-hidden flex flex-col justify-between">
            <div class="p-5 space-y-3">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h2 class="text-base font-black text-slate-900">{{ $tier['name'] }}</h2>
                        <span class="text-xs font-semibold text-purple-800">
                            Risico: {{ $tier['jail_minutes'] }} minuten isoleercel bij betrapping
                        </span>
                    </div>
                    <span class="px-2 py-0.5 rounded text-[11px] font-black {{ $hasLevel ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }} shrink-0">
                        Min. Level {{ $tier['required_level'] }}
                    </span>
                </div>

                <!-- Specs -->
                <div class="grid grid-cols-3 gap-2 pt-2 text-xs">
                    <div class="bg-yellow-50/70 p-2 rounded border border-yellow-200">
                        <span class="text-[10px] text-yellow-800 uppercase font-bold block">Energie</span>
                        <span class="font-black text-yellow-900"><i class="fa-solid fa-bolt text-yellow-600 mr-1"></i>{{ $tier['energy_cost'] }}</span>
                    </div>
                    <div class="bg-amber-50/70 p-2 rounded border border-amber-200">
                        <span class="text-[10px] text-amber-800 uppercase font-bold block">Buit</span>
                        <span class="font-black text-amber-900"><i class="fa-solid fa-coins text-amber-600 mr-1"></i>{{ $tier['min_cash'] }}-{{ $tier['max_cash'] }} KC</span>
                    </div>
                    <div class="bg-purple-50/70 p-2 rounded border border-purple-200">
                        <span class="text-[10px] text-purple-800 uppercase font-bold block">Kans</span>
                        <span class="font-black text-purple-900"><i class="fa-solid fa-percent text-purple-600 mr-1"></i>{{ $tier['base_chance'] }}%</span>
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
                    <button type="submit" @if(! $hasLevel || ! $hasEnergy || $character->isImprisoned()) disabled @endif class="bg-rose-600 hover:bg-rose-500 disabled:opacity-40 disabled:cursor-not-allowed text-white font-black px-4 py-2 rounded text-xs shadow transition flex items-center gap-1.5 cursor-pointer">
                        <i class="fa-solid fa-hand-sparkles"></i>
                        <span>Slag Slaan</span>
                    </button>
                </form>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Recent Crime History -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
        <h3 class="text-xs font-black uppercase text-slate-800 tracking-wider flex items-center gap-2 mb-3">
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
