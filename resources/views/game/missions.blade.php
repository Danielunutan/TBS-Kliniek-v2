@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{ activeFilter: 'all' }">
    
    <!-- Clean TBS Kliniek Title Banner -->
    <div class="rounded-2xl bg-white p-6 sm:p-8 text-slate-800 shadow-xs border border-slate-200 relative overflow-hidden">
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-purple-50 border border-purple-100 flex items-center justify-center text-2xl text-purple-600 shadow-2xs shrink-0">
                    <i class="fa-solid fa-list-check"></i>
                </div>
                <div>
                    <div class="inline-flex items-center gap-2 bg-purple-50 text-purple-700 text-xs font-bold px-3 py-0.5 rounded-full border border-purple-200 mb-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span>TBS Kliniek Dossier</span>
                    </div>
                    <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight mt-0.5">
                        Kliniek Missies & Opdrachten
                    </h1>
                    <p class="text-slate-500 text-xs sm:text-sm mt-0.5 max-w-2xl leading-relaxed">
                        Voer officiële en geheime opdrachten uit voor sleutelfiguren in de kliniek. Beloningen zijn 100% wit geld en worden <strong>rechtstreeks op je bankrekening</strong> gestort!
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-3 bg-slate-50 px-5 py-3 rounded-2xl border border-slate-200 shrink-0 shadow-2xs">
                <div class="w-10 h-10 rounded-xl bg-amber-50 border border-amber-200 text-amber-600 flex items-center justify-center font-bold text-lg">
                    <i class="fa-solid fa-bullseye"></i>
                </div>
                <div>
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wide block leading-none">Voltooid Totaal</span>
                    <span class="text-lg font-black text-slate-900">{{ $character->missions_completed }} missies</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Category Filter Tabs -->
    <div class="flex items-center gap-2 overflow-x-auto pb-1 custom-scrollbar">
        <button @click="activeFilter = 'all'" 
            :class="activeFilter === 'all' ? 'bg-purple-600 text-white font-bold shadow-xs' : 'bg-white text-slate-600 hover:text-slate-900 hover:bg-slate-50 border border-slate-200/80'" 
            class="px-4 py-2 rounded-lg text-xs font-semibold transition whitespace-nowrap cursor-pointer">
            Alle Missies ({{ count($missions) }})
        </button>
        <button @click="activeFilter = 'starter'" 
            :class="activeFilter === 'starter' ? 'bg-slate-900 text-white font-bold shadow-xs' : 'bg-white text-slate-600 hover:text-slate-900 hover:bg-slate-50 border border-slate-200/80'" 
            class="px-4 py-2 rounded-lg text-xs font-semibold transition whitespace-nowrap cursor-pointer">
            Niveau 1 - 5 (Beginner)
        </button>
        <button @click="activeFilter = 'advanced'" 
            :class="activeFilter === 'advanced' ? 'bg-slate-900 text-white font-bold shadow-xs' : 'bg-white text-slate-600 hover:text-slate-900 hover:bg-slate-50 border border-slate-200/80'" 
            class="px-4 py-2 rounded-lg text-xs font-semibold transition whitespace-nowrap cursor-pointer">
            Niveau 6 - 15 (Gevorderd)
        </button>
        <button @click="activeFilter = 'elite'" 
            :class="activeFilter === 'elite' ? 'bg-slate-900 text-white font-bold shadow-xs' : 'bg-white text-slate-600 hover:text-slate-900 hover:bg-slate-50 border border-slate-200/80'" 
            class="px-4 py-2 rounded-lg text-xs font-semibold transition whitespace-nowrap cursor-pointer">
            Niveau 16+ (Mastermind)
        </button>
    </div>

    <!-- Mission Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        @foreach($missions as $m)
            @php
                $isUnlocked = $character->level >= $m->required_level;
                $completedTimes = $characterProgress[$m->id] ?? 0;
                $filterGroup = $m->required_level <= 5 ? 'starter' : ($m->required_level <= 15 ? 'advanced' : 'elite');
                $scenario = $scenarios[$m->id] ?? null;
            @endphp

            <div x-show="activeFilter === 'all' || activeFilter === '{{ $filterGroup }}'" 
                class="bg-white rounded-2xl shadow-xs border border-slate-200/90 overflow-hidden flex flex-col justify-between transition hover:shadow-md {{ !$isUnlocked ? 'opacity-70 bg-slate-50/50' : '' }}">
                
                <div class="p-5 sm:p-6 space-y-4">
                    <!-- Header of card -->
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <div class="w-11 h-11 rounded-xl flex items-center justify-center shrink-0 shadow-2xs {{ $isUnlocked ? 'bg-indigo-50 text-indigo-700 border border-indigo-100' : 'bg-slate-100 text-slate-400 border border-slate-200' }}">
                                <i class="fa-solid {{ $isUnlocked ? 'fa-list-check' : 'fa-lock' }} text-base"></i>
                            </div>
                            <div>
                                <h3 class="font-black text-slate-900 text-sm sm:text-base leading-snug">{{ $m->title }}</h3>
                                <div class="flex items-center gap-2 mt-0.5">
                                    <span class="text-[10px] font-semibold text-slate-500"><i class="fa-solid fa-user text-slate-400 mr-1"></i>{{ $m->giver_name }}</span>
                                    <span class="text-slate-300">&bull;</span>
                                    <span class="text-[10px] font-semibold text-slate-500"><i class="fa-solid fa-location-dot text-slate-400 mr-1"></i>{{ $m->location_name }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Level requirement badge -->
                        <span class="px-2.5 py-1 rounded-lg text-[10px] font-black uppercase {{ $isUnlocked ? 'bg-slate-100 text-slate-700' : 'bg-rose-50 text-rose-700 border border-rose-200' }}">
                            Level {{ $m->required_level }}
                        </span>
                    </div>

                    <!-- General Description -->
                    <p class="text-xs text-slate-600 leading-relaxed">{{ $m->description }}</p>

                    @if($scenario && $isUnlocked)
                    <!-- INTERACTIVE STORY SCENARIO & NPC DILEMMA -->
                    <div class="bg-slate-50 rounded-xl border border-slate-200 p-4 space-y-3">
                        <div class="flex items-center gap-3 border-b border-slate-200/60 pb-2.5">
                            <img src="{{ asset($scenario['npc_avatar']) }}" alt="{{ $scenario['npc_name'] }}" class="w-9 h-9 rounded-lg object-cover border border-slate-200 bg-white shrink-0">
                            <div>
                                <h4 class="font-black text-xs text-slate-900 leading-tight">{{ $scenario['npc_name'] }}</h4>
                                <span class="text-[10px] font-semibold text-indigo-600">{{ $scenario['npc_role'] }}</span>
                            </div>
                        </div>

                        <!-- Dialogue Quote -->
                        <div class="relative text-xs text-slate-700 italic bg-white p-3 rounded-lg border border-slate-200/80 leading-relaxed">
                            <i class="fa-solid fa-comment-dots text-indigo-400 mr-1"></i>
                            "{{ $scenario['dilemma'] }}"
                        </div>
                    </div>
                    @endif

                    <!-- Reward chips: White money to bank! -->
                    <div class="flex flex-wrap items-center gap-2 text-xs font-bold pt-1">
                        <span class="flex items-center gap-1.5 bg-emerald-50 text-emerald-800 border border-emerald-200 px-2.5 py-1 rounded-lg text-[11px]" title="Wit geld gestort op je bankrekening">
                            <i class="fa-solid fa-building-columns text-emerald-600 text-[10px]"></i>
                            €{{ number_format($m->min_cash_reward, 0, ',', '.') }} (Bank)
                        </span>
                        <span class="flex items-center gap-1.5 bg-indigo-50 text-indigo-800 border border-indigo-200 px-2.5 py-1 rounded-lg text-[11px]">
                            <i class="fa-solid fa-star text-indigo-600 text-[10px]"></i>
                            +{{ $m->xp_reward }} XP
                        </span>
                        <span class="flex items-center gap-1.5 bg-slate-50 text-slate-700 border border-slate-200 px-2.5 py-1 rounded-lg text-[11px]">
                            <i class="fa-solid fa-bolt text-amber-500 text-[10px]"></i>
                            {{ $m->energy_cost }} Energie
                        </span>
                        <span class="flex items-center gap-1.5 bg-purple-50 text-purple-800 border border-purple-200 px-2.5 py-1 rounded-lg text-[11px]">
                            <i class="fa-solid fa-award text-purple-600 text-[10px]"></i>
                            +{{ $m->respect_reward }} Respect
                        </span>
                    </div>
                </div>

                <!-- Footer Action & Scenario Choices -->
                <div class="px-5 py-4 bg-slate-50/80 border-t border-slate-100 space-y-3">
                    <div class="flex items-center justify-between text-[11px] font-semibold text-slate-500">
                        @if($completedTimes > 0)
                            <span class="text-emerald-700 font-bold"><i class="fa-solid fa-circle-check text-emerald-500 mr-1"></i>{{ $completedTimes }}x succesvol afgerond</span>
                        @else
                            <span class="text-slate-400">Nog niet voltooid</span>
                        @endif
                    </div>

                    @if($isUnlocked)
                        <form action="{{ route('missions.execute', $m->id) }}" method="POST" class="space-y-3">
                            @csrf

                            @if($scenario)
                            <!-- Choice Radio Options -->
                            <div class="space-y-1.5">
                                <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400 block">Kies jouw tactische antwoord / actie:</span>
                                @foreach($scenario['choices'] as $optKey => $optText)
                                <label class="flex items-start gap-2.5 p-2 rounded-lg bg-white border border-slate-200 hover:border-indigo-400 hover:bg-indigo-50/30 cursor-pointer transition text-xs font-semibold text-slate-800">
                                    <input type="radio" name="choice" value="{{ $optKey }}" required class="mt-0.5 text-indigo-600 focus:ring-indigo-500">
                                    <span><strong class="text-indigo-600">{{ $optKey }}:</strong> {{ $optText }}</span>
                                </label>
                                @endforeach
                            </div>
                            @endif

                            <button type="submit" class="w-full bg-slate-900 hover:bg-slate-800 text-white font-extrabold text-xs py-2.5 px-4 rounded-xl transition shadow-xs flex items-center justify-center gap-2 cursor-pointer">
                                <span>Beslissing Bevestigen & Uitvoeren</span>
                                <i class="fa-solid fa-arrow-right text-[10px]"></i>
                            </button>
                        </form>
                    @else
                        <div class="text-center py-2 text-xs font-bold text-slate-400 flex items-center justify-center gap-1.5">
                            <i class="fa-solid fa-lock text-[10px]"></i> Vereist Level {{ $m->required_level }}
                        </div>
                    @endif
                </div>

            </div>
        @endforeach
    </div>

</div>
@endsection
