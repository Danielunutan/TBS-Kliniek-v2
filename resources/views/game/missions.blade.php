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
        if (this.levelFilter === 'elite' && (m.level < 26 || m.level > 35)) return false;
        if (this.levelFilter === 'legendary' && m.level < 36) return false;

        if (this.search.trim() !== '') {
            const q = this.search.toLowerCase();
            return m.title.toLowerCase().includes(q) || m.giver.toLowerCase().includes(q) || m.location.toLowerCase().includes(q);
        }

        return true;
    }
}">

    <!-- Compact Title & Progress Card -->
    <div class="rounded-2xl bg-white dark:bg-slate-900 p-5 text-slate-800 dark:text-slate-100 shadow-xs border border-slate-200/90 dark:border-slate-800 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <div class="w-12 h-12 rounded-xl bg-purple-50 dark:bg-purple-950/60 border border-purple-200/80 dark:border-purple-800/70 flex items-center justify-center text-xl text-purple-600 dark:text-purple-400 shadow-2xs shrink-0">
                <i class="fa-solid fa-list-check"></i>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-base sm:text-lg font-black text-slate-900 dark:text-white tracking-tight leading-tight">
                        Kliniek Missies & Dossiers
                    </h1>
                    <span class="text-[10px] font-black bg-purple-50 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 px-2.5 py-0.5 rounded-full border border-purple-200/80 dark:border-purple-800/70">
                        {{ $totalCount }} Missies
                    </span>
                </div>
                <p class="text-slate-500 dark:text-slate-400 text-xs mt-0.5 leading-snug">
                    Elke missie is eenmalig. Beloningen zijn wit bankgeld, XP en dagelijks Respect!
                </p>
            </div>
        </div>

        <!-- Progress Widget -->
        <div class="bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70 px-3.5 py-2.5 rounded-xl shrink-0 min-w-[210px]">
            <div class="flex items-center justify-between text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                <span>Voortgang</span>
                <span class="text-purple-700 dark:text-purple-400 font-extrabold">{{ $completedCount }} / {{ $totalCount }} ({{ $progressPct }}%)</span>
            </div>
            <div class="w-full bg-slate-200 dark:bg-slate-700 rounded-full h-2 overflow-hidden">
                <div class="bg-gradient-to-r from-purple-600 to-emerald-500 h-full rounded-full transition-all duration-300" style="width: {{ $progressPct }}%"></div>
            </div>
        </div>
    </div>

    <!-- Compact Multi-Filter Controls -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-4 shadow-xs space-y-3">
        <!-- Status Filter Tabs -->
        <div class="flex flex-wrap items-center justify-between gap-2">
            <div class="flex flex-wrap items-center gap-1.5 text-xs">
                <button @click="statusFilter = 'all'"
                    :class="statusFilter === 'all' ? 'bg-purple-600 text-white font-black shadow-xs' : 'bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 border border-slate-200/80 dark:border-slate-700 font-bold'"
                    class="px-3 py-1.5 rounded-xl transition-all duration-150 cursor-pointer">
                    Alle ({{ count($missions) }})
                </button>
                <button @click="statusFilter = 'available'"
                    :class="statusFilter === 'available' ? 'bg-purple-600 text-white font-black shadow-xs' : 'bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 border border-slate-200/80 dark:border-slate-700 font-bold'"
                    class="px-3 py-1.5 rounded-xl transition-all duration-150 cursor-pointer">
                    Beschikbaar
                </button>
                <button @click="statusFilter = 'completed'"
                    :class="statusFilter === 'completed' ? 'bg-emerald-600 text-white font-black shadow-xs' : 'bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 border border-slate-200/80 dark:border-slate-700 font-bold'"
                    class="px-3 py-1.5 rounded-xl transition-all duration-150 cursor-pointer flex items-center gap-1">
                    <i class="fa-solid fa-check text-[10px]"></i> Voltooid ({{ $completedCount }})
                </button>
                <button @click="statusFilter = 'locked'"
                    :class="statusFilter === 'locked' ? 'bg-slate-800 dark:bg-slate-700 text-white font-black shadow-xs' : 'bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 border border-slate-200/80 dark:border-slate-700 font-bold'"
                    class="px-3 py-1.5 rounded-xl transition-all duration-150 cursor-pointer flex items-center gap-1">
                    <i class="fa-solid fa-lock text-[10px]"></i> Vergrendeld
                </button>
            </div>

            <!-- Search input -->
            <div class="relative w-full sm:w-56">
                <i class="fa-solid fa-magnifying-glass absolute left-3 top-2.5 text-slate-400 text-xs"></i>
                <input type="text" x-model="search" placeholder="Zoek missie..."
                    class="w-full bg-slate-50 dark:bg-slate-800/90 border border-slate-200 dark:border-slate-700 rounded-xl pl-8 pr-3 py-1.5 text-xs text-slate-900 dark:text-slate-100 placeholder:text-slate-400 dark:placeholder:text-slate-500 focus:outline-none focus:border-purple-500 dark:focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition">
            </div>
        </div>

        <!-- Level Range Pill Filters -->
        <div class="flex items-center gap-1.5 overflow-x-auto pb-0.5 text-[11px] font-semibold border-t border-slate-100 dark:border-slate-800 pt-2 scrollbar-none">
            <span class="text-slate-400 mr-1 uppercase text-[9px] font-black shrink-0">Niveau:</span>
            <button @click="levelFilter = 'all'"
                :class="levelFilter === 'all' ? 'bg-purple-600 text-white font-black shadow-xs' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 border border-slate-200/80 dark:border-slate-700'"
                class="px-2.5 py-1 rounded-xl transition cursor-pointer shrink-0">
                Alles
            </button>
            <button @click="levelFilter = 'starter'"
                :class="levelFilter === 'starter' ? 'bg-purple-600 text-white font-black shadow-xs' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 border border-slate-200/80 dark:border-slate-700'"
                class="px-2.5 py-1 rounded-xl transition cursor-pointer shrink-0">
                Lvl 1 - 5 (Starter)
            </button>
            <button @click="levelFilter = 'mid'"
                :class="levelFilter === 'mid' ? 'bg-purple-600 text-white font-black shadow-xs' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 border border-slate-200/80 dark:border-slate-700'"
                class="px-2.5 py-1 rounded-xl transition cursor-pointer shrink-0">
                Lvl 6 - 15 (Gevorderd)
            </button>
            <button @click="levelFilter = 'advanced'"
                :class="levelFilter === 'advanced' ? 'bg-purple-600 text-white font-black shadow-xs' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 border border-slate-200/80 dark:border-slate-700'"
                class="px-2.5 py-1 rounded-xl transition cursor-pointer shrink-0">
                Lvl 16 - 25 (Expert)
            </button>
            <button @click="levelFilter = 'elite'"
                :class="levelFilter === 'elite' ? 'bg-purple-600 text-white font-black shadow-xs' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 border border-slate-200/80 dark:border-slate-700'"
                class="px-2.5 py-1 rounded-xl transition cursor-pointer shrink-0">
                Lvl 26 - 35 (Elite)
            </button>
            <button @click="levelFilter = 'legendary'"
                :class="levelFilter === 'legendary' ? 'bg-amber-500 text-slate-950 font-black shadow-xs' : 'text-amber-800 dark:text-amber-300 hover:bg-amber-50 dark:hover:bg-amber-950/40 border border-amber-200/80 dark:border-amber-800/70'"
                class="px-2.5 py-1 rounded-xl transition cursor-pointer shrink-0 flex items-center gap-1">
                <i class="fa-solid fa-crown text-[10px]" :class="levelFilter === 'legendary' ? 'text-slate-950' : 'text-amber-500'"></i> Lvl 36+ (Legendes)
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
                $scenario = $scenarios[$m->slug] ?? ($scenarios[$m->id] ?? null);
            @endphp

            <div x-show="matches({
                    id: {{ $m->id }},
                    title: @js($m->title),
                    giver: @js($m->giver_name),
                    location: @js($m->location_name),
                    level: {{ (int) $m->required_level }},
                    isUnlocked: {{ $isUnlocked ? 'true' : 'false' }},
                    isCompleted: {{ $isCompleted ? 'true' : 'false' }}
                })"
                class="rounded-2xl shadow-xs border p-4 sm:p-5 flex flex-col justify-between transition-all duration-200 {{ $isCompleted ? 'border-emerald-200/80 dark:border-emerald-800/70 bg-emerald-50/30 dark:bg-emerald-950/20' : (!$isUnlocked ? 'border-slate-200/60 dark:border-slate-800/50 bg-slate-50/70 dark:bg-slate-900/40 opacity-70' : 'bg-white dark:bg-slate-900 border-slate-200/90 dark:border-slate-800 hover:border-purple-300 dark:hover:border-purple-700/80') }}">
                
                <!-- Card Header -->
                <div class="space-y-2">
                    <div class="flex items-start justify-between gap-2.5">
                        <div class="flex items-start gap-2.5 min-w-0">
                            <!-- Category Icon -->
                            <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0 text-xs shadow-2xs {{ $isCompleted ? 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200/80 dark:border-emerald-800/70' : ($isUnlocked ? 'bg-purple-50 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 border border-purple-200/80 dark:border-purple-800/70' : 'bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-slate-500 border border-slate-200/80 dark:border-slate-700') }}">
                                @if($isCompleted)
                                    <i class="fa-solid fa-check"></i>
                                @elseif($isUnlocked)
                                    <i class="fa-solid fa-list-check"></i>
                                @else
                                    <i class="fa-solid fa-lock"></i>
                                @endif
                            </div>

                            <div class="min-w-0">
                                <h3 class="font-black text-slate-900 dark:text-white text-xs sm:text-sm leading-snug truncate {{ $isCompleted ? 'text-emerald-950 dark:text-emerald-300' : '' }}">
                                    {{ $m->title }}
                                </h3>
                                <div class="flex items-center gap-1.5 text-[10px] text-slate-400 dark:text-slate-500 truncate">
                                    <span class="truncate"><i class="fa-solid fa-user text-[9px] mr-0.5"></i>{{ $m->giver_name }}</span>
                                    <span>&bull;</span>
                                    <span class="truncate"><i class="fa-solid fa-location-dot text-[9px] mr-0.5"></i>{{ $m->location_name }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Level / Completed Badge -->
                        <div class="shrink-0">
                            @if($isCompleted)
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200/80 dark:border-emerald-800/70 flex items-center gap-1">
                                    <i class="fa-solid fa-check text-[9px]"></i> Voltooid
                                </span>
                            @elseif(!$isUnlocked)
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border border-rose-200/80 dark:border-rose-800/70 flex items-center gap-1">
                                    <i class="fa-solid fa-lock text-[8px]"></i> Lvl {{ $m->required_level }}
                                </span>
                            @else
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase bg-purple-50 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 border border-purple-200/80 dark:border-purple-800/70">
                                    Lvl {{ $m->required_level }}
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- Compact Description -->
                    <p class="text-xs text-slate-600 dark:text-slate-400 line-clamp-2 leading-relaxed">
                        {{ $m->description }}
                    </p>

                    <!-- Interactive Scenario (if defined and NOT yet completed) -->
                    @if($scenario && $isUnlocked && !$isCompleted)
                    <div class="bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70 rounded-xl p-3 text-xs space-y-2">
                        <div class="flex items-start gap-2.5">
                            <img src="{{ asset($scenario['npc_avatar']) }}" alt="{{ $scenario['npc_name'] }}" class="w-7 h-7 rounded-md object-cover border border-purple-200/80 dark:border-purple-800/70 bg-white shrink-0 mt-0.5 shadow-2xs">
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <span class="font-black text-[11px] text-purple-950 dark:text-purple-200 leading-tight">{{ $scenario['npc_name'] }}</span>
                                    @if(!empty($scenario['npc_role']))
                                    <span class="text-[9px] text-purple-600 dark:text-purple-400 font-semibold">({{ $scenario['npc_role'] }})</span>
                                    @endif
                                </div>
                                <p class="text-[11px] text-slate-700 dark:text-slate-300 mt-1 leading-relaxed font-medium">
                                    "{{ $scenario['dilemma'] }}"
                                </p>
                            </div>
                        </div>
                    </div>
                    @endif

                    @php
                        $mPrimary = match ($m->category) {
                            'gevecht', 'intimidatie', 'kracht' => $character->getEffectiveStrength(),
                            'smokkel', 'vluchten' => $character->getEffectiveStamina(),
                            'inbraak', 'tactisch', 'hack', 'ontsnapping' => $character->getEffectiveIntelligence(),
                            'kantine', 'sociaal', 'afpersing', 'bende' => $character->getEffectiveCharisma(),
                            default => $character->getEffectiveDiscipline(),
                        };
                        $mSecondary = match ($m->category) {
                            'gevecht', 'intimidatie', 'kracht' => $character->getEffectiveDefense(),
                            'smokkel', 'vluchten' => $character->getEffectiveCharisma(),
                            'inbraak', 'tactisch', 'hack', 'ontsnapping' => $character->getEffectiveDiscipline(),
                            'kantine', 'sociaal', 'afpersing', 'bende' => $character->getEffectiveIntelligence(),
                            default => $character->getEffectiveStamina(),
                        };
                        $mBaseChance = ($m->success_rate >= 100) ? 100 : min(72, (int) round($m->success_rate * 0.80));
                        $mBonus = (int) floor(($mPrimary * 1.5 + $mSecondary) / 14);
                        $mSuccessChance = ($m->success_rate >= 100) ? 100 : min(90, max(35, $mBaseChance + $mBonus));
                    @endphp
                    <!-- Reward Chips Row -->
                    <div class="flex flex-wrap items-center gap-1.5 pt-1 text-[11px] font-bold">
                        <span class="flex items-center gap-1 bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200/80 dark:border-emerald-800/70 px-2 py-0.5 rounded-xl text-[10px]" title="Wit geld op bankrekening">
                            <i class="fa-solid fa-building-columns text-[9px] text-emerald-600 dark:text-emerald-400"></i>
                            €{{ number_format($m->min_cash_reward, 0, ',', '.') }}
                        </span>
                        <span class="flex items-center gap-1 bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 border border-indigo-200/80 dark:border-indigo-800/70 px-2 py-0.5 rounded-xl text-[10px]">
                            <i class="fa-solid fa-star text-[9px] text-indigo-600 dark:text-indigo-400"></i>
                            +{{ $m->xp_reward }} XP
                        </span>
                        <span class="flex items-center gap-1 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200/80 dark:border-slate-700 px-2 py-0.5 rounded-xl text-[10px]">
                            <i class="fa-solid fa-bolt text-[9px] text-amber-500"></i>
                            {{ $m->energy_cost }}
                        </span>
                        <span class="flex items-center gap-1 bg-purple-50 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 border border-purple-200/80 dark:border-purple-800/70 px-2 py-0.5 rounded-xl text-[10px]">
                            <i class="fa-solid fa-fire text-[9px] text-purple-600 dark:text-purple-400"></i>
                            +{{ $m->respect_reward }} Resp
                        </span>
                        <span class="flex items-center gap-1 bg-amber-50 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 border border-amber-200/80 dark:border-amber-800/70 px-2 py-0.5 rounded-xl text-[10px]" title="Jouw uitvoeringskans bij het juiste antwoord (hogere stats verhogen deze kans)">
                            <i class="fa-solid fa-dice text-[9px] text-amber-600 dark:text-amber-400"></i>
                            {{ $mSuccessChance }}% Slagingskans
                        </span>
                    </div>
                </div>

                <!-- Footer Action Area -->
                <div class="pt-3 mt-3 border-t border-slate-100 dark:border-slate-800">
                    @if($isCompleted)
                        <!-- Completed Pill (Cannot execute again!) -->
                        <div class="w-full py-2 px-3 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200/80 dark:border-emerald-800/70 text-emerald-700 dark:text-emerald-300 text-xs font-bold text-center flex items-center justify-center gap-1.5 select-none">
                            <i class="fa-solid fa-circle-check text-emerald-600 dark:text-emerald-400"></i>
                            <span>Missie Voltooid (Eenmalig afgerond)</span>
                        </div>
                    @elseif(!$isUnlocked)
                        <!-- Locked Pill -->
                        <div class="w-full py-2 px-3 rounded-xl bg-slate-100 dark:bg-slate-800/80 text-slate-400 dark:text-slate-500 border border-slate-200/80 dark:border-slate-700/60 text-xs font-bold text-center flex items-center justify-center gap-1.5 select-none">
                            <i class="fa-solid fa-lock text-[10px]"></i>
                            <span>Ontgrendelt op Level {{ $m->required_level }}</span>
                        </div>
                    @else
                        <!-- Execute Form -->
                        <form action="{{ route('missions.execute', $m->id) }}" method="POST" class="space-y-2">
                            @csrf

                            @if($scenario)
                            <div class="space-y-1">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="text-[9px] font-black uppercase text-purple-900 dark:text-purple-300 block tracking-wider">Kies tactische actie:</span>
                                    <span class="text-[9px] font-bold text-slate-400 dark:text-slate-500">Juist antwoord + {{ $mSuccessChance }}% kansrol</span>
                                </div>
                                <div class="grid grid-cols-1 gap-1">
                                    @foreach($scenario['choices'] as $optKey => $optText)
                                    <label class="flex items-start gap-2 p-2.5 rounded-xl bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70 hover:border-purple-300 dark:hover:border-purple-600 has-[:checked]:border-purple-500 dark:has-[:checked]:border-purple-500 has-[:checked]:bg-purple-50/60 dark:has-[:checked]:bg-purple-950/50 cursor-pointer text-[11px] font-medium text-slate-800 dark:text-slate-200 transition-all">
                                        <input type="radio" name="choice" value="{{ $optKey }}" required class="text-purple-600 focus:ring-purple-500 mt-0.5 shrink-0">
                                        <span class="leading-tight"><strong class="text-purple-700 dark:text-purple-400">{{ $optKey }}:</strong> {{ $optText }}</span>
                                    </label>
                                    @endforeach
                                </div>
                            </div>
                            @endif

                            <button type="submit"
                                class="w-full bg-purple-600 hover:bg-purple-700 dark:bg-purple-600 dark:hover:bg-purple-500 active:scale-95 text-white font-black text-xs py-2.5 px-3 rounded-xl transition-all duration-150 shadow-xs flex items-center justify-center gap-1.5 cursor-pointer">
                                <span>Missie Uitvoeren (⚡ {{ $m->energy_cost }})</span>
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
