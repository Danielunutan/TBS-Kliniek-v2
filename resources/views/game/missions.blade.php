@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{ tab: 'all' }">
    <!-- Header with Tabs (Matches middle-center screen in mockup) -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-black text-slate-900 uppercase tracking-tight">
                Missies
            </h1>
            <p class="text-xs text-slate-500 mt-0.5">
                Voltooi dagelijkse taken en verhaallijnen om respect en KliniekCash te verdienen.
            </p>
        </div>

        <!-- Filter tabs matching mockup -->
        <div class="flex items-center gap-1 bg-slate-100 p-1 rounded-xl text-xs font-bold text-slate-600 flex-wrap">
            <button type="button" @click="tab = 'all'" :class="tab === 'all' ? 'bg-purple-900 text-white shadow-xs' : 'hover:bg-slate-200'" class="px-3 py-1.5 rounded-lg transition">Alle missies</button>
            <button type="button" @click="tab = 'daily'" :class="tab === 'daily' ? 'bg-purple-900 text-white shadow-xs' : 'hover:bg-slate-200'" class="px-3 py-1.5 rounded-lg transition">Dagelijkse</button>
            <button type="button" @click="tab = 'weekly'" :class="tab === 'weekly' ? 'bg-purple-900 text-white shadow-xs' : 'hover:bg-slate-200'" class="px-3 py-1.5 rounded-lg transition">Wekelijkse</button>
            <button type="button" @click="tab = 'cash'" :class="tab === 'cash' ? 'bg-purple-900 text-white shadow-xs' : 'hover:bg-slate-200'" class="px-3 py-1.5 rounded-lg transition">Geld</button>
            <button type="button" @click="tab = 'training'" :class="tab === 'training' ? 'bg-purple-900 text-white shadow-xs' : 'hover:bg-slate-200'" class="px-3 py-1.5 rounded-lg transition">Training</button>
            <button type="button" @click="tab = 'theft'" :class="tab === 'theft' ? 'bg-purple-900 text-white shadow-xs' : 'hover:bg-slate-200'" class="px-3 py-1.5 rounded-lg transition">Diefstal</button>
        </div>
    </div>

    <!-- Missions List (Matches layout in middle-center mockup) -->
    <div class="space-y-3">
        @foreach($missions as $m)
        @php
            $isUnlocked = $character->level >= $m->required_level;
        @endphp
        <div class="bg-white rounded-2xl border border-slate-200/80 p-4 sm:p-5 shadow-xs flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <!-- Left Info -->
            <div class="flex items-center gap-4 min-w-0">
                <!-- Portrait Icon / Avatar -->
                <div class="w-14 h-14 rounded-2xl bg-purple-950 text-amber-300 flex items-center justify-center text-xl shrink-0 overflow-hidden shadow-xs border border-purple-800">
                    <img src="{{ asset('assets/characters/henkie.jpg') }}" alt="Mission" class="w-full h-full object-cover">
                </div>

                <div class="min-w-0 space-y-1">
                    <h2 class="font-black text-slate-900 text-sm tracking-tight truncate">{{ $m->title }}</h2>
                    <p class="text-xs text-slate-500 leading-relaxed truncate">{{ $m->description }}</p>
                    
                    <div class="flex items-center gap-3 text-[11px] font-bold">
                        <span class="text-amber-600 flex items-center gap-1">
                            <i class="fa-solid fa-coins text-amber-500 text-[10px]"></i>
                            <span>{{ number_format($m->min_cash_reward, 0, ',', '.') }} KC</span>
                        </span>
                        <span class="text-purple-600 flex items-center gap-1">
                            <i class="fa-solid fa-star text-purple-500 text-[10px]"></i>
                            <span>{{ $m->xp_reward }} XP</span>
                        </span>
                        <span class="text-slate-400">
                            Min. Lvl {{ $m->required_level }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Right Action / Lock (Matches orange 'Start' button or purple lock in mockup) -->
            <div class="shrink-0 w-full sm:w-auto">
                @if($isUnlocked)
                <form method="POST" action="{{ route('missions.execute', $m) }}">
                    @csrf
                    <button type="submit" @if($character->energy < $m->energy_cost || $character->isImprisoned()) disabled @endif class="w-full sm:w-auto bg-gradient-to-r from-orange-500 to-amber-500 hover:from-orange-600 hover:to-amber-600 disabled:opacity-40 disabled:cursor-not-allowed text-white font-black py-2.5 px-6 rounded-xl text-xs shadow-xs transition flex items-center justify-center gap-1.5 cursor-pointer">
                        Start
                    </button>
                </form>
                @else
                <div class="w-10 h-10 rounded-xl bg-purple-950 text-purple-300 flex items-center justify-center text-sm shadow-xs border border-purple-800 ml-auto">
                    <i class="fa-solid fa-lock"></i>
                </div>
                @endif
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection
