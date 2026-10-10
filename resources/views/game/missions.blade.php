@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-black text-slate-900 uppercase tracking-wide flex items-center gap-2">
                <i class="fa-solid fa-bullseye text-purple-700"></i>
                <span>Kliniek Missies & Opdrachten</span>
            </h1>
            <p class="text-xs text-slate-600 mt-1">
                Voer opdrachten uit voor de bewoners en het personeel. Verdien KliniekCash, XP en verhoog je aanzien in de kliniek.
            </p>
        </div>

        <!-- Filter tabs -->
        <div class="flex items-center gap-1 bg-slate-100 p-1 rounded-lg text-xs font-bold text-slate-600 flex-wrap">
            <a href="{{ route('missions.index', ['category' => 'all']) }}" class="px-3 py-1.5 rounded transition {{ $selectedCategory === 'all' ? 'bg-purple-900 text-white shadow-sm' : 'hover:bg-slate-200' }}">Alles</a>
            <a href="{{ route('missions.index', ['category' => 'beginners']) }}" class="px-3 py-1.5 rounded transition {{ $selectedCategory === 'beginners' ? 'bg-purple-900 text-white shadow-sm' : 'hover:bg-slate-200' }}">Beginners</a>
            <a href="{{ route('missions.index', ['category' => 'story']) }}" class="px-3 py-1.5 rounded transition {{ $selectedCategory === 'story' ? 'bg-purple-900 text-white shadow-sm' : 'hover:bg-slate-200' }}">Verhaallijn</a>
            <a href="{{ route('missions.index', ['category' => 'cash']) }}" class="px-3 py-1.5 rounded transition {{ $selectedCategory === 'cash' ? 'bg-purple-900 text-white shadow-sm' : 'hover:bg-slate-200' }}">Geld</a>
            <a href="{{ route('missions.index', ['category' => 'theft']) }}" class="px-3 py-1.5 rounded transition {{ $selectedCategory === 'theft' ? 'bg-purple-900 text-white shadow-sm' : 'hover:bg-slate-200' }}">Diefstal</a>
        </div>
    </div>

    <!-- Missions Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @forelse($missions as $m)
        @php
            $completedCount = $characterProgress[$m->id] ?? 0;
            $hasLevel = $character->level >= $m->required_level;
            $hasEnergy = $character->energy >= $m->energy_cost;
        @endphp
        <div class="bg-white rounded-xl border {{ $hasLevel ? 'border-slate-200' : 'border-slate-300 opacity-75 bg-slate-50' }} shadow-sm overflow-hidden flex flex-col justify-between">
            <div class="p-5 space-y-3">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <h2 class="text-base font-black text-slate-900">{{ $m->title }}</h2>
                            @if($m->storyline)
                            <span class="px-2 py-0.5 rounded bg-purple-100 text-purple-900 font-bold text-[10px]">
                                {{ $m->storyline }}
                            </span>
                            @endif
                        </div>
                        <div class="flex items-center gap-3 text-xs text-slate-500 mt-1">
                            <span><i class="fa-solid fa-user-tie text-purple-600 mr-1"></i>{{ $m->giver_name }}</span>
                            <span><i class="fa-solid fa-location-dot text-rose-500 mr-1"></i>{{ $m->location_name }}</span>
                        </div>
                    </div>

                    <div class="text-right shrink-0">
                        <span class="inline-block px-2 py-0.5 rounded text-[11px] font-black {{ $hasLevel ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                            Min. Level {{ $m->required_level }}
                        </span>
                        @if($completedCount > 0)
                        <div class="text-[10px] text-slate-400 font-semibold mt-1">
                            <i class="fa-solid fa-check text-emerald-500"></i> {{ $completedCount }}x voltooid
                        </div>
                        @endif
                    </div>
                </div>

                <p class="text-xs text-slate-600 leading-relaxed bg-slate-50 p-2.5 rounded border border-slate-100">
                    {{ $m->description }}
                </p>

                <!-- Requirements & Rewards Grid -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 pt-1 text-xs">
                    <div class="bg-yellow-50/70 p-2 rounded border border-yellow-200/80">
                        <span class="text-[10px] text-yellow-800 uppercase font-bold block">Energie</span>
                        <span class="font-black text-yellow-900"><i class="fa-solid fa-bolt text-yellow-600 mr-1"></i>{{ $m->energy_cost }}</span>
                    </div>
                    <div class="bg-amber-50/70 p-2 rounded border border-amber-200/80">
                        <span class="text-[10px] text-amber-800 uppercase font-bold block">Beloning</span>
                        <span class="font-black text-amber-900"><i class="fa-solid fa-coins text-amber-600 mr-1"></i>{{ $m->min_cash_reward }}-{{ $m->max_cash_reward }} KC</span>
                    </div>
                    <div class="bg-purple-50/70 p-2 rounded border border-purple-200/80">
                        <span class="text-[10px] text-purple-800 uppercase font-bold block">Ervaring</span>
                        <span class="font-black text-purple-900"><i class="fa-solid fa-star text-purple-600 mr-1"></i>+{{ $m->xp_reward }} XP</span>
                    </div>
                    <div class="bg-indigo-50/70 p-2 rounded border border-indigo-200/80">
                        <span class="text-[10px] text-indigo-800 uppercase font-bold block">Slagingskans</span>
                        <span class="font-black text-indigo-900"><i class="fa-solid fa-percent text-indigo-600 mr-1"></i>{{ $m->success_rate }}%</span>
                    </div>
                </div>
            </div>

            <!-- Action Bar -->
            <div class="px-5 py-3 bg-slate-50 border-t border-slate-200 flex items-center justify-between">
                <span class="text-xs text-slate-500 font-medium">
                    @if(! $hasLevel)
                        <i class="fa-solid fa-lock text-slate-400 mr-1"></i> Level te laag
                    @elseif(! $hasEnergy)
                        <i class="fa-solid fa-battery-empty text-yellow-600 mr-1"></i> Te weinig energie
                    @else
                        <i class="fa-solid fa-circle-check text-emerald-500 mr-1"></i> Klaar voor actie
                    @endif
                </span>

                <form method="POST" action="{{ route('missions.execute', $m) }}">
                    @csrf
                    <button type="submit" @if(! $hasLevel || ! $hasEnergy || $character->isImprisoned()) disabled @endif class="bg-amber-500 hover:bg-amber-400 disabled:opacity-40 disabled:cursor-not-allowed text-purple-950 font-black px-5 py-2 rounded text-xs shadow transition flex items-center gap-1.5 cursor-pointer">
                        <i class="fa-solid fa-play"></i> Missie Uitvoeren
                    </button>
                </form>
            </div>
        </div>
        @empty
        <div class="col-span-2 bg-white p-8 text-center rounded-xl border border-slate-200 text-slate-500 text-sm">
            Geen missies gevonden in deze categorie.
        </div>
        @endforelse
    </div>
</div>
@endsection
