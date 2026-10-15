@extends('layouts.app')

@section('content')
@php
    $totalCount = $totalMissionsCount ?? count($missions);
    $completedCount = $completedMissionsCount ?? ($character->missions_completed ?? 0);
    $progressPct = $totalCount > 0 ? min(100, round(($completedCount / $totalCount) * 100)) : 0;
@endphp

<div class="space-y-4" x-data="{
    statusFilter: 'all',
    levelFilter: 'all',
    search: '',
    matches(m) {
        if (this.statusFilter === 'available' && (!m.isUnlocked || m.isCompleted)) return false;
        if (this.statusFilter === 'completed' && !m.isCompleted) return false;
        if (this.statusFilter === 'locked' && (m.isUnlocked || m.isCompleted)) return false;

        if (this.levelFilter === 'starter' && m.level > 5) return false;
        if (this.levelFilter === 'mid' && (m.level < 6 || m.level > 15)) return false;
        if (this.levelFilter === 'advanced' && (m.level < 16 || m.level > 25)) return false;
        if (this.levelFilter === 'elite' && m.level < 26) return false;

        if (this.search.trim() !== '') {
            const q = this.search.toLowerCase();
            return m.title.toLowerCase().includes(q) || m.giver.toLowerCase().includes(q) || m.location.toLowerCase().includes(q);
        }

        return true;
    }
}">

    <!-- Compact Title & Progress Card -->
    <div class="rounded-xl bg-white p-4 sm:p-5 text-slate-800 shadow-2xs border border-slate-200 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-purple-50 border border-purple-100 flex items-center justify-center text-lg text-purple-600 shadow-2xs shrink-0">
                <i class="fa-solid fa-list-check"></i>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-base sm:text-lg font-black text-slate-900 tracking-tight leading-tight">
                        Kliniek Missies & Dossiers
                    </h1>
                    <span class="text-[10px] font-bold bg-purple-100 text-purple-700 px-2 py-0.5 rounded-full border border-purple-200">
                        {{ $totalCount }} Missies
                    </span>
                </div>
                <p class="text-slate-500 text-xs mt-0.5 leading-snug">
                    Elke missie is eenmalig. Beloningen zijn wit bankgeld , XP en dagelijks Respect!
                </p>
            </div>
        </div>

        <!-- Progress Widget -->
        <div class="bg-slate-50 border border-slate-200 px-3.5 py-2 rounded-xl shrink-0 min-w-[210px]">
            <div class="flex items-center justify-between text-xs font-bold text-slate-700 mb-1">
                <span>Voortgang</span>
                <span class="text-purple-700 font-extrabold">{{ $completedCount }} / {{ $totalCount }} ({{ $progressPct }}%)</span>
            </div>
            <div class="w-full bg-slate-200 rounded-full h-2 overflow-hidden">
                <div class="bg-gradient-to-r from-purple-600 to-emerald-500 h-full rounded-full transition-all duration-300" style="width: {{ $progressPct }}%"></div>
            </div>
        </div>
    </div>

    <!-- Compact Multi-Filter Controls -->
    <div class="bg-white dark:bg-[#111827] border border-slate-200/80 dark:border-slate-800 rounded-xl p-3 shadow-2xs space-y-2.5">
        <!-- Status Filter Tabs -->
        <div class="flex flex-wrap items-center justify-between gap-2">
            <div class="flex flex-wrap items-center gap-1.5 text-xs">
                <button @click="statusFilter = 'all'"
                    :class="statusFilter === 'all' ? 'bg-purple-600 text-white font-bold shadow-2xs' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700'"
                    class="px-3 py-1.5 rounded-lg transition cursor-pointer">
                    Alle ({{ count($missions) }})
                </button>
                <button @click="statusFilter = 'available'"
                    :class="statusFilter === 'available' ? 'bg-indigo-600 text-white font-bold shadow-2xs' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700'"
                    class="px-3 py-1.5 rounded-lg transition cursor-pointer">
                    Beschikbaar
                </button>
                <button @click="statusFilter = 'completed'"
                    :class="statusFilter === 'completed' ? 'bg-emerald-600 text-white font-bold shadow-2xs' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700'"
                    class="px-3 py-1.5 rounded-lg transition cursor-pointer flex items-center gap-1">
                    <i class="fa-solid fa-check text-[10px]"></i> Voltooid ({{ $completedCount }})
                </button>
                <button @click="statusFilter = 'locked'"
                    :class="statusFilter === 'locked' ? 'bg-slate-800 dark:bg-slate-700 text-white font-bold shadow-2xs' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700'"
                    class="px-3 py-1.5 rounded-lg transition cursor-pointer flex items-center gap-1">
                    <i class="fa-solid fa-lock text-[10px]"></i> Vergrendeld
                </button>
            </div>

            <!-- Search input -->
            <div class="relative w-full sm:w-56">
                <i class="fa-solid fa-magnifying-glass absolute left-3 top-2.5 text-slate-400 text-xs"></i>
                <input type="text" x-model="search" placeholder="Zoek missie..."
                    class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg pl-8 pr-3 py-1.5 text-xs text-slate-800 dark:text-slate-200 placeholder:text-slate-400 focus:outline-none focus:border-purple-600">
            </div>
        </div>

        <!-- Level Range Pill Filters -->
        <div class="flex items-center gap-1.5 overflow-x-auto pb-0.5 text-[11px] font-semibold border-t border-slate-100 pt-2 scrollbar-none">
            <span class="text-slate-400 mr-1 uppercase text-[9px] font-black shrink-0">Niveau:</span>
            <button @click="levelFilter = 'all'"
                :class="levelFilter === 'all' ? 'bg-slate-900 text-white font-bold' : 'text-slate-600 hover:bg-slate-100 border border-slate-200'"
                class="px-2.5 py-1 rounded-md transition cursor-pointer shrink-0">
                Alles
            </button>
            <button @click="levelFilter = 'starter'"
                :class="levelFilter === 'starter' ? 'bg-slate-900 text-white font-bold' : 'text-slate-600 hover:bg-slate-100 border border-slate-200'"
                class="px-2.5 py-1 rounded-md transition cursor-pointer shrink-0">
                Lvl 1 - 5 (Starter)
            </button>
            <button @click="levelFilter = 'mid'"
                :class="levelFilter === 'mid' ? 'bg-slate-900 text-white font-bold' : 'text-slate-600 hover:bg-slate-100 border border-slate-200'"
                class="px-2.5 py-1 rounded-md transition cursor-pointer shrink-0">
                Lvl 6 - 15 (Gevorderd)
            </button>
            <button @click="levelFilter = 'advanced'"
                :class="levelFilter === 'advanced' ? 'bg-slate-900 text-white font-bold' : 'text-slate-600 hover:bg-slate-100 border border-slate-200'"
                class="px-2.5 py-1 rounded-md transition cursor-pointer shrink-0">
                Lvl 16 - 25 (Expert)
            </button>
            <button @click="levelFilter = 'elite'"
                :class="levelFilter === 'elite' ? 'bg-slate-900 text-white font-bold' : 'text-slate-600 hover:bg-slate-100 border border-slate-200'"
                class="px-2.5 py-1 rounded-md transition cursor-pointer shrink-0">
                Lvl 26+ (Mastermind)
            </button>
        </div>
    </div>

    <!-- Compact Mission Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5">
        @foreach($missions as $m)
            @php
                $isUnlocked = $character->level >= $m->required_level;
                $completedTimes = $characterProgress[$m->id] ?? 0;
                $isCompleted = $completedTimes > 0;
                $scenario = $scenarios[$m->id] ?? null;
            @endphp

            <div x-show="matches({
                    id: {{ $m->id }},
                    title: '{{ addslashes($m->title) }}',
                    giver: '{{ addslashes($m->giver_name) }}',
                    location: '{{ addslashes($m->location_name) }}',
                    level: {{ $m->required_level }},
                    isUnlocked: {{ $isUnlocked ? 'true' : 'false' }},
                    isCompleted: {{ $isCompleted ? 'true' : 'false' }}
                })"
                class="bg-white rounded-xl shadow-2xs border p-3.5 sm:p-4 flex flex-col justify-between transition hover:shadow-xs {{ $isCompleted ? 'border-emerald-300 bg-emerald-50/20' : (!$isUnlocked ? 'border-slate-200 bg-slate-50/70 opacity-70' : 'border-slate-200 hover:border-purple-300') }}">
                
                <!-- Card Header -->
                <div class="space-y-2">
                    <div class="flex items-start justify-between gap-2.5">
                        <div class="flex items-start gap-2.5 min-w-0">
                            <!-- Category Icon -->
                            <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0 text-xs shadow-2xs {{ $isCompleted ? 'bg-emerald-100 text-emerald-700' : ($isUnlocked ? 'bg-purple-100 text-purple-700' : 'bg-slate-200 text-slate-400') }}">
                                @if($isCompleted)
                                    <i class="fa-solid fa-check"></i>
                                @elseif($isUnlocked)
                                    <i class="fa-solid fa-list-check"></i>
                                @else
                                    <i class="fa-solid fa-lock"></i>
                                @endif
                            </div>

                            <div class="min-w-0">
                                <h3 class="font-black text-slate-900 text-xs sm:text-sm leading-snug truncate {{ $isCompleted ? 'text-emerald-950' : '' }}">
                                    {{ $m->title }}
                                </h3>
                                <div class="flex items-center gap-1.5 text-[10px] text-slate-400 truncate">
                                    <span class="truncate"><i class="fa-solid fa-user text-[9px] mr-0.5"></i>{{ $m->giver_name }}</span>
                                    <span>&bull;</span>
                                    <span class="truncate"><i class="fa-solid fa-location-dot text-[9px] mr-0.5"></i>{{ $m->location_name }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Level / Completed Badge -->
                        <div class="shrink-0">
                            @if($isCompleted)
                                <span class="px-2 py-0.5 rounded-md text-[10px] font-black uppercase bg-emerald-100 text-emerald-800 border border-emerald-200 flex items-center gap-1">
                                    <i class="fa-solid fa-check text-[9px]"></i> Voltooid
                                </span>
                            @elseif(!$isUnlocked)
                                <span class="px-2 py-0.5 rounded-md text-[10px] font-black uppercase bg-rose-50 text-rose-700 border border-rose-200 flex items-center gap-1">
                                    <i class="fa-solid fa-lock text-[8px]"></i> Lvl {{ $m->required_level }}
                                </span>
                            @else
                                <span class="px-2 py-0.5 rounded-md text-[10px] font-black uppercase bg-indigo-50 text-indigo-700 border border-indigo-200">
                                    Lvl {{ $m->required_level }}
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- Compact Description -->
                    <p class="text-xs text-slate-600 line-clamp-2 leading-relaxed">
                        {{ $m->description }}
                    </p>

                    <!-- Interactive Scenario (if defined and NOT yet completed) -->
                    @if($scenario && $isUnlocked && !$isCompleted)
                    <div class="bg-purple-50/70 border border-purple-200/90 rounded-lg p-2.5 text-xs space-y-2">
                        <div class="flex items-start gap-2.5">
                            <img src="{{ asset($scenario['npc_avatar']) }}" alt="{{ $scenario['npc_name'] }}" class="w-7 h-7 rounded-md object-cover border border-purple-200 bg-white shrink-0 mt-0.5 shadow-2xs">
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <span class="font-black text-[11px] text-purple-950 leading-tight">{{ $scenario['npc_name'] }}</span>
                                    @if(!empty($scenario['npc_role']))
                                    <span class="text-[9px] text-purple-600 font-semibold">({{ $scenario['npc_role'] }})</span>
                                    @endif
                                </div>
                                <p class="text-[11px] text-purple-900 mt-1 leading-relaxed font-medium">
                                    "{{ $scenario['dilemma'] }}"
                                </p>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Reward Chips Row -->
                    <div class="flex flex-wrap items-center gap-1.5 pt-1 text-[11px] font-bold">
                        <span class="flex items-center gap-1 bg-emerald-50 text-emerald-800 border border-emerald-200/80 px-2 py-0.5 rounded-md text-[10px]" title="Wit geld op bankrekening">
                            <i class="fa-solid fa-building-columns text-[9px] text-emerald-600"></i>
                            €{{ number_format($m->min_cash_reward, 0, ',', '.') }}
                        </span>
                        <span class="flex items-center gap-1 bg-indigo-50 text-indigo-800 border border-indigo-200/80 px-2 py-0.5 rounded-md text-[10px]">
                            <i class="fa-solid fa-star text-[9px] text-indigo-600"></i>
                            +{{ $m->xp_reward }} XP
                        </span>
                        <span class="flex items-center gap-1 bg-slate-100 text-slate-700 border border-slate-200 px-2 py-0.5 rounded-md text-[10px]">
                            <i class="fa-solid fa-bolt text-[9px] text-amber-500"></i>
                            {{ $m->energy_cost }}
                        </span>
                        <span class="flex items-center gap-1 bg-purple-50 text-purple-800 border border-purple-200/80 px-2 py-0.5 rounded-md text-[10px]">
                            <i class="fa-solid fa-fire text-[9px] text-purple-600"></i>
                            +{{ $m->respect_reward }} Resp
                        </span>
                    </div>
                </div>

                <!-- Footer Action Area -->
                <div class="pt-3 mt-3 border-t border-slate-100">
                    @if($isCompleted)
                        <!-- Completed Pill (Cannot execute again!) -->
                        <div class="w-full py-1.5 px-3 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold text-center flex items-center justify-center gap-1.5 select-none">
                            <i class="fa-solid fa-circle-check text-emerald-600"></i>
                            <span>Missie Voltooid (Eenmalig afgerond)</span>
                        </div>
                    @elseif(!$isUnlocked)
                        <!-- Locked Pill -->
                        <div class="w-full py-1.5 px-3 rounded-lg bg-slate-100 text-slate-400 text-xs font-bold text-center flex items-center justify-center gap-1.5 select-none">
                            <i class="fa-solid fa-lock text-[10px]"></i>
                            <span>Ontgrendelt op Level {{ $m->required_level }}</span>
                        </div>
                    @else
                        <!-- Execute Form -->
                        <form action="{{ route('missions.execute', $m->id) }}" method="POST" class="space-y-2">
                            @csrf

                            @if($scenario)
                            <div class="space-y-1">
                                <span class="text-[9px] font-black uppercase text-purple-900 block tracking-wider">Kies tactische actie:</span>
                                <div class="grid grid-cols-1 gap-1">
                                    @foreach($scenario['choices'] as $optKey => $optText)
                                    <label class="flex items-start gap-2 p-1.5 rounded-md bg-slate-50 border border-slate-200 hover:border-purple-400 hover:bg-purple-50/50 cursor-pointer text-[11px] font-medium text-slate-800 transition">
                                        <input type="radio" name="choice" value="{{ $optKey }}" required class="text-purple-600 focus:ring-purple-500 mt-0.5 shrink-0">
                                        <span class="leading-tight"><strong class="text-purple-700">{{ $optKey }}:</strong> {{ $optText }}</span>
                                    </label>
                                    @endforeach
                                </div>
                            </div>
                            @endif

                            <button type="submit"
                                class="w-full bg-slate-900 hover:bg-slate-800 text-white font-extrabold text-xs py-2 px-3 rounded-lg transition shadow-2xs flex items-center justify-center gap-1.5 cursor-pointer active:scale-[0.99]">
                                <span>Missie Uitvoeren ( {{ $m->energy_cost }})</span>
                                <i class="fa-solid fa-arrow-right text-[10px]"></i>
                            </button>
                        </form>
                    @endif
                </div>

            </div>
        @endforeach
    </div>

</div>
@endsection
