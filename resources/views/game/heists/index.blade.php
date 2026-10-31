@extends('layouts.app')

@section('title', 'Grote Heists & Bende Overvallen - Het Planningsbord')
@section('meta_description', 'Plan en voer grootschalige overvallen en bende heists uit in TBS Kliniek. Gebruik je getunede vluchtwagen en maskers voor gigantische cash buit en bendekas stortingen.')

@section('content')
<div class="space-y-6">

    <!-- HERO HEADER (TBS Kliniek Website Style) -->
    <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 p-6 sm:p-7 shadow-xs relative overflow-hidden">
        <div class="absolute -right-10 -bottom-10 w-52 h-52 bg-purple-500/10 dark:bg-purple-500/5 rounded-full blur-3xl pointer-events-none"></div>

        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 relative z-10">
            <div class="flex items-start sm:items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-purple-50 dark:bg-purple-950/60 border border-purple-200/80 dark:border-purple-800/70 flex items-center justify-center text-2xl text-purple-600 dark:text-purple-400 shadow-2xs shrink-0">
                    <i class="fa-solid fa-masks-theater"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="text-[10px] font-black uppercase tracking-widest text-purple-700 dark:text-purple-300 bg-purple-50 dark:bg-purple-950/60 px-2.5 py-0.5 rounded-full border border-purple-200/80 dark:border-purple-800/70">
                            TBS Kliniek Illegaal
                        </span>
                        <span class="px-2.5 py-0.5 rounded-full bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border border-rose-200/80 dark:border-rose-800/70 font-black text-[10px] uppercase tracking-wider flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500 animate-ping"></span>
                            High Stakes
                        </span>
                    </div>
                    <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight mt-1 flex items-center gap-2">
                        Het Heist Planningsbord
                    </h1>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 max-w-2xl leading-relaxed">
                        Bereid solo operaties of grootschalige bende-overvallen voor. Elke heist vereist een <strong class="text-slate-800 dark:text-slate-200">Vluchtvoertuig</strong> en <strong class="text-slate-800 dark:text-slate-200">Bivakmutsen / Maskers</strong> om je identiteit geheim te houden. Hoe sneller de wagen en hoe beter de conditie, hoe groter de slagingskans!
                    </p>
                </div>
            </div>

            <!-- INVENTORY & STATUS BADGES (Mobile App Responsive Strip) -->
            <div class="grid grid-cols-3 sm:flex items-center gap-1.5 sm:gap-2.5 shrink-0 w-full sm:w-auto">
                <!-- Solo Maskers Badge -->
                <div class="bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70 rounded-xl p-2 sm:px-3.5 sm:py-2 flex flex-col sm:flex-row items-center sm:items-center gap-1.5 sm:gap-2.5 shadow-2xs text-center sm:text-left">
                    <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-lg bg-indigo-50 dark:bg-indigo-950/60 border border-indigo-200/80 dark:border-indigo-800/70 text-indigo-600 dark:text-indigo-300 flex items-center justify-center text-xs shrink-0">
                        <i class="fa-solid fa-mask"></i>
                    </div>
                    <div class="min-w-0">
                        <span class="text-[8px] sm:text-[9px] uppercase font-extrabold text-slate-400 dark:text-slate-500 block tracking-wider leading-none mb-0.5 truncate">Maskers</span>
                        <span class="text-[11px] sm:text-xs font-black text-slate-900 dark:text-white font-mono leading-none">{{ $character->masks ?? 0 }}x</span>
                    </div>
                    <a href="{{ route('shop.index', ['tab' => 'items']) }}" title="Koop meer maskers" class="text-[10px] sm:text-[11px] text-purple-600 dark:text-purple-400 hover:text-purple-700 font-extrabold whitespace-nowrap hidden sm:inline">+ Koop</a>
                </div>

                @if($myGang)
                <!-- Gang Depot Maskers Badge -->
                <div class="bg-amber-50/90 dark:bg-amber-950/60 border border-amber-200/80 dark:border-amber-800/70 rounded-xl p-2 sm:px-3.5 sm:py-2 flex flex-col sm:flex-row items-center sm:items-center gap-1.5 sm:gap-2.5 shadow-2xs text-center sm:text-left">
                    <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-lg bg-amber-100 dark:bg-amber-900/60 border border-amber-300 dark:border-amber-700 text-amber-700 dark:text-amber-300 flex items-center justify-center text-xs shrink-0">
                        <i class="fa-solid fa-box-archive"></i>
                    </div>
                    <div class="min-w-0">
                        <span class="text-[8px] sm:text-[9px] uppercase font-extrabold text-amber-600 dark:text-amber-400 block tracking-wider leading-none mb-0.5 truncate">Depot</span>
                        <span class="text-[11px] sm:text-xs font-black text-slate-900 dark:text-white font-mono leading-none">{{ $myGang->masks ?? 0 }}x</span>
                    </div>
                    <a href="{{ route('gangs.index', ['tab' => 'depot']) }}" title="Bende Depot" class="text-[10px] sm:text-[11px] text-amber-700 dark:text-amber-400 hover:underline font-extrabold whitespace-nowrap hidden sm:inline">Depot</a>
                </div>
                @else
                <!-- Geen Bende Placeholder Badge -->
                <div class="bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70 rounded-xl p-2 sm:px-3.5 sm:py-2 flex flex-col sm:flex-row items-center sm:items-center gap-1.5 sm:gap-2.5 shadow-2xs text-center sm:text-left opacity-60">
                    <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-lg bg-slate-200 dark:bg-slate-700 text-slate-500 flex items-center justify-center text-xs shrink-0">
                        <i class="fa-solid fa-users"></i>
                    </div>
                    <div class="min-w-0">
                        <span class="text-[8px] sm:text-[9px] uppercase font-extrabold text-slate-400 block tracking-wider leading-none mb-0.5 truncate">Depot</span>
                        <span class="text-[10px] sm:text-xs font-black text-slate-500 font-mono leading-none">Geen</span>
                    </div>
                </div>
                @endif

                <!-- Actief Voertuig Badge -->
                <div class="bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70 rounded-xl p-2 sm:px-3.5 sm:py-2 flex flex-col sm:flex-row items-center sm:items-center gap-1.5 sm:gap-2.5 shadow-2xs text-center sm:text-left">
                    <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-lg bg-purple-50 dark:bg-purple-950/60 border border-purple-200/80 dark:border-purple-800/70 text-purple-600 dark:text-purple-300 flex items-center justify-center text-xs shrink-0">
                        <i class="fa-solid {{ $activeVehicle ? $activeVehicle->vehicle->icon : 'fa-car' }}"></i>
                    </div>
                    <div class="min-w-0">
                        <span class="text-[8px] sm:text-[9px] uppercase font-extrabold text-slate-400 dark:text-slate-500 block tracking-wider leading-none mb-0.5 truncate">Wagen</span>
                        <span class="text-[10px] sm:text-xs font-black text-slate-900 dark:text-white truncate max-w-[80px] sm:max-w-[120px] block leading-none">
                            {{ $activeVehicle ? $activeVehicle->vehicle->name : 'Geen' }}
                        </span>
                    </div>
                    <a href="{{ route('garage.index') }}" title="Garage" class="text-[10px] sm:text-[11px] text-purple-600 dark:text-purple-400 hover:text-purple-700 font-extrabold whitespace-nowrap hidden sm:inline">Garage</a>
                </div>
            </div>
        </div>
    </div>

    <!-- TABS CONTAINER (SOLO VS GANG) -->
    <div x-data="{ mode: '{{ request('tab') === 'gang' ? 'gang' : 'solo' }}' }" class="space-y-4 sm:space-y-6">

        <!-- SEGMENTED PILL TAB SWITCHER (Full-Width Responsive App Switcher) -->
        <div class="flex items-center justify-between gap-4 flex-wrap">
            <div class="w-full sm:w-auto grid grid-cols-2 sm:flex bg-slate-100/90 dark:bg-slate-800/80 p-1.5 rounded-2xl gap-1.5 border border-slate-200/80 dark:border-slate-700/70 shadow-2xs">
                <button type="button" 
                        @click="mode = 'solo'; window.history.replaceState({}, '', '{{ route('heists.index') }}?tab=solo')"
                        :class="mode === 'solo' 
                            ? 'bg-white dark:bg-slate-900 text-purple-700 dark:text-purple-300 shadow-xs border border-slate-200/80 dark:border-slate-700 font-black' 
                            : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white border border-transparent font-bold'"
                        class="px-3 sm:px-5 py-2.5 rounded-xl text-xs transition cursor-pointer flex items-center justify-center gap-1.5 sm:gap-2">
                    <i class="fa-solid fa-user-secret"></i>
                    <span>Solo Overvallen</span>
                    <span class="px-1.5 sm:px-2 py-0.5 rounded-full text-[10px] font-black"
                          :class="mode === 'solo' ? 'bg-purple-100 dark:bg-purple-950 text-purple-700 dark:text-purple-300' : 'bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-400'">
                        {{ count($soloHeists) }}
                    </span>
                </button>

                <button type="button" 
                        @click="mode = 'gang'; window.history.replaceState({}, '', '{{ route('heists.index') }}?tab=gang')"
                        :class="mode === 'gang' 
                            ? 'bg-white dark:bg-slate-900 text-purple-700 dark:text-purple-300 shadow-xs border border-slate-200/80 dark:border-slate-700 font-black' 
                            : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white border border-transparent font-bold'"
                        class="px-3 sm:px-5 py-2.5 rounded-xl text-xs transition cursor-pointer flex items-center justify-center gap-1.5 sm:gap-2">
                    <i class="fa-solid fa-users text-amber-500"></i>
                    <span>Bende Heists</span>
                    <span class="px-1.5 sm:px-2 py-0.5 rounded-full text-[10px] font-black bg-amber-50 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 border border-amber-200/80 dark:border-amber-800/70">
                        {{ count($gangHeists) }}
                    </span>
                </button>
            </div>

            <!-- Quick Info Tip -->
            <div class="hidden sm:flex items-center gap-2 text-xs font-bold text-slate-500 dark:text-slate-400">
                <i class="fa-solid fa-circle-info text-purple-500"></i>
                <span x-show="mode === 'solo'">Solo overvallen storten alle buit 100% contant in jouw zakken.</span>
                <span x-show="mode === 'gang'">Bende heists verdelen 50% contant voor jou &amp; 50% direct in de bendekas!</span>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- TAB 1: SOLO OVERVALLEN -->
        <!-- ========================================== -->
        <div x-show="mode === 'solo'" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            @foreach($soloHeists as $heist)
            @php
                $hasLevel = $character->level >= $heist->min_level;
                $hasMasks = ($character->masks ?? 0) >= $heist->required_mask_count;
                $hasVehicle = !empty($activeVehicle);
                $hasType = $activeVehicle && (!$heist->required_vehicle_type || $heist->required_vehicle_type === 'any' || $activeVehicle->vehicle->type === $heist->required_vehicle_type);
                $hasSpeed = $activeVehicle && ($activeVehicle->effective_speed >= $heist->min_speed_required);
                $hasCondition = $activeVehicle && ($activeVehicle->current_condition >= $heist->min_condition_required);
                $canLaunch = $hasLevel && $hasMasks && $hasVehicle && $hasType && $hasSpeed && $hasCondition && ($character->energy >= $heist->energy_cost);

                $baseChance = match ($heist->difficulty) {
                    'makkelijk' => 75,
                    'normaal' => 65,
                    'zwaar' => 52,
                    'extreem' => 42,
                    'legendarisch' => 32,
                    default => 50,
                };
                $speedDiff = $activeVehicle ? ($activeVehicle->effective_speed - $heist->min_speed_required) : 0;
                $speedBonus = (int) round($speedDiff * 0.4);
                $condBonus = $activeVehicle ? (int) round(($activeVehicle->current_condition - 70) * 0.25) : 0;
                $statBonus = min(15, (int) floor(($character->getEffectiveIntelligence() + $character->getEffectiveDiscipline()) / 30));
                $estChance = max(15, min(92, $baseChance + $speedBonus + $condBonus + $statBonus));
            @endphp

            <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 hover:border-purple-300 dark:hover:border-purple-700/80 rounded-2xl p-5 sm:p-6 shadow-xs flex flex-col justify-between gap-5 transition-all duration-200 group relative overflow-hidden">
                <div class="space-y-4">
                    <!-- Top Bar: Icon + Difficulty Badge -->
                    <div class="flex items-start justify-between gap-2">
                        <div class="w-12 h-12 rounded-xl bg-purple-50 dark:bg-purple-950/60 border border-purple-200/80 dark:border-purple-800/70 text-purple-600 dark:text-purple-300 flex items-center justify-center text-xl shrink-0 group-hover:scale-105 transition-transform shadow-2xs">
                            <i class="fa-solid {{ $heist->icon }}"></i>
                        </div>
                        <div class="text-right space-y-1">
                            <span class="px-2.5 py-0.5 rounded-full border text-[10px] font-black uppercase tracking-wider {{ $heist->difficulty_badge }}">
                                {{ $heist->difficulty }}
                            </span>
                            <div class="text-[11px] font-bold text-slate-400 dark:text-slate-500">Level {{ $heist->min_level }}+</div>
                        </div>
                    </div>

                    <!-- Title & Description -->
                    <div>
                        <h3 class="text-base font-black text-slate-900 dark:text-white group-hover:text-purple-600 dark:group-hover:text-purple-400 transition-colors">
                            {{ $heist->name }}
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 leading-relaxed">
                            {{ $heist->description }}
                        </p>
                    </div>

                    <!-- Payout & Rewards Box -->
                    <div class="bg-slate-50/90 dark:bg-slate-800/60 rounded-xl p-3.5 border border-slate-200/80 dark:border-slate-700/70 space-y-2">
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-slate-500 dark:text-slate-400 font-medium flex items-center gap-1.5">
                                <i class="fa-solid fa-coins text-amber-500"></i> Geschatte Buit:
                            </span>
                            <span class="font-black text-emerald-600 dark:text-emerald-400 font-mono">
                                €{{ number_format($heist->payout_min, 0, ',', '.') }} &ndash; €{{ number_format($heist->payout_max, 0, ',', '.') }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-slate-500 dark:text-slate-400 font-medium flex items-center gap-1.5">
                                <i class="fa-solid fa-star text-purple-500"></i> XP &amp; Respect:
                            </span>
                            <span class="font-bold text-slate-900 dark:text-slate-200">
                                +{{ $heist->xp_reward }} XP / +{{ $heist->respect_reward }} Respect
                            </span>
                        </div>
                    </div>

                    <!-- Requirements Checklist (Compact 2x2 App Grid) -->
                    <div class="grid grid-cols-2 gap-1.5 text-[11px] pt-2 border-t border-slate-100 dark:border-slate-800">
                        <!-- Level -->
                        <div class="flex items-center justify-between p-2 rounded-xl {{ $hasLevel ? 'bg-emerald-50/70 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 border border-emerald-200/80 dark:border-emerald-800/70' : 'bg-slate-50 dark:bg-slate-800/50 text-slate-400 dark:text-slate-500 border border-slate-200/80 dark:border-slate-700/70' }}">
                            <span class="flex items-center gap-1 font-bold truncate">
                                <i class="fa-solid {{ $hasLevel ? 'fa-check text-emerald-600' : 'fa-xmark text-slate-400' }}"></i>
                                <span>Lvl {{ $heist->min_level }}</span>
                            </span>
                            <span class="font-mono font-black text-[10px]">{{ $character->level }}/{{ $heist->min_level }}</span>
                        </div>

                        <!-- Maskers -->
                        <div class="flex items-center justify-between p-2 rounded-xl {{ $hasMasks ? 'bg-emerald-50/70 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 border border-emerald-200/80 dark:border-emerald-800/70' : 'bg-rose-50/70 dark:bg-rose-950/40 text-rose-700 dark:text-rose-400 border border-rose-200/80 dark:border-rose-800/70' }}">
                            <span class="flex items-center gap-1 font-bold truncate">
                                <i class="fa-solid fa-mask {{ $hasMasks ? 'text-emerald-600' : 'text-rose-500' }}"></i>
                                <span>{{ $heist->required_mask_count }}x Masker</span>
                            </span>
                            <span class="font-mono font-black text-[10px]">{{ $character->masks ?? 0 }}/{{ $heist->required_mask_count }}</span>
                        </div>

                        <!-- Vluchtwagen Conditie -->
                        <div class="flex items-center justify-between p-2 rounded-xl {{ ($hasVehicle && $hasCondition) ? 'bg-emerald-50/70 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 border border-emerald-200/80 dark:border-emerald-800/70' : 'bg-rose-50/70 dark:bg-rose-950/40 text-rose-700 dark:text-rose-400 border border-rose-200/80 dark:border-rose-800/70' }}">
                            <span class="flex items-center gap-1 font-bold truncate">
                                <i class="fa-solid fa-car {{ ($hasVehicle && $hasCondition) ? 'text-emerald-600' : 'text-rose-500' }}"></i>
                                <span>Wagen &ge;{{ $heist->min_condition_required }}%</span>
                            </span>
                            <span class="font-mono font-black text-[10px]">{{ $activeVehicle ? $activeVehicle->current_condition : 0 }}%</span>
                        </div>

                        <!-- Energie -->
                        <div class="flex items-center justify-between p-2 rounded-xl {{ $character->energy >= $heist->energy_cost ? 'bg-emerald-50/70 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 border border-emerald-200/80 dark:border-emerald-800/70' : 'bg-slate-50 dark:bg-slate-800/50 text-slate-400 dark:text-slate-500 border border-slate-200/80 dark:border-slate-700/70' }}">
                            <span class="flex items-center gap-1 font-bold truncate">
                                <i class="fa-solid fa-bolt text-amber-500"></i>
                                <span>Kosten</span>
                            </span>
                            <span class="font-mono font-black text-[10px]">{{ $heist->energy_cost }} EP</span>
                        </div>
                    </div>

                    @if($hasVehicle)
                    <!-- Slagingskans Progress Bar -->
                    <div class="space-y-1 pt-2 border-t border-slate-100 dark:border-slate-800">
                        <div class="flex items-center justify-between text-xs font-bold">
                            <span class="text-slate-600 dark:text-slate-400">Geschatte Slagingskans:</span>
                            <span class="font-mono {{ $estChance >= 60 ? 'text-emerald-600 dark:text-emerald-400' : ($estChance >= 40 ? 'text-amber-500' : 'text-rose-600 dark:text-rose-400') }}">{{ $estChance }}%</span>
                        </div>
                        <div class="w-full bg-slate-100 dark:bg-slate-800/80 rounded-full h-2 overflow-hidden">
                            <div class="h-full rounded-full transition-all duration-300 {{ $estChance >= 60 ? 'bg-emerald-500' : ($estChance >= 40 ? 'bg-amber-500' : 'bg-rose-500') }}" style="width: {{ $estChance }}%"></div>
                        </div>
                    </div>
                    @endif
                </div>

                <!-- Action Button -->
                <div class="pt-3 border-t border-slate-100 dark:border-slate-800">
                    @if(! $hasVehicle)
                        <a href="{{ route('garage.index') }}" 
                           class="w-full py-2.5 rounded-xl bg-purple-50 dark:bg-purple-950/50 hover:bg-purple-100 dark:hover:bg-purple-900/60 border border-purple-200/80 dark:border-purple-800/70 text-purple-700 dark:text-purple-300 font-black text-xs text-center transition-all duration-150 active:scale-95 flex items-center justify-center gap-1.5 cursor-pointer">
                            <i class="fa-solid fa-warehouse"></i>
                            <span>Koop Vluchtwagen in Garage</span>
                        </a>
                    @elseif(! $hasType || ! $hasSpeed)
                        <a href="{{ route('garage.index') }}" 
                           class="w-full py-2.5 rounded-xl bg-amber-50 dark:bg-amber-950/50 hover:bg-amber-100 dark:hover:bg-amber-900/60 border border-amber-200/80 dark:border-amber-800/70 text-amber-800 dark:text-amber-300 font-black text-xs text-center transition-all duration-150 active:scale-95 flex items-center justify-center gap-1.5 cursor-pointer">
                            <i class="fa-solid fa-gauge-high"></i>
                            <span>Wagen Vereist &ge;{{ $heist->min_speed_required }} km/u</span>
                        </a>
                    @elseif(! $hasCondition)
                        <a href="{{ route('garage.index') }}" 
                           class="w-full py-2.5 rounded-xl bg-amber-50 dark:bg-amber-950/50 hover:bg-amber-100 dark:hover:bg-amber-900/60 border border-amber-200/80 dark:border-amber-800/70 text-amber-800 dark:text-amber-300 font-black text-xs text-center transition-all duration-150 active:scale-95 flex items-center justify-center gap-1.5 cursor-pointer">
                            <i class="fa-solid fa-wrench"></i>
                            <span>Wagen Repareren in Garage</span>
                        </a>
                    @elseif(! $hasMasks)
                        <a href="{{ route('shop.index', ['tab' => 'items']) }}" 
                           class="w-full py-2.5 rounded-xl bg-indigo-50 dark:bg-indigo-950/50 hover:bg-indigo-100 dark:hover:bg-indigo-900/60 border border-indigo-200/80 dark:border-indigo-800/70 text-indigo-700 dark:text-indigo-300 font-black text-xs text-center transition-all duration-150 active:scale-95 flex items-center justify-center gap-1.5 cursor-pointer">
                            <i class="fa-solid fa-mask"></i>
                            <span>Koop Maskers in Winkel</span>
                        </a>
                    @elseif($character->energy < $heist->energy_cost)
                        <button type="button" disabled class="w-full py-2.5 rounded-xl bg-slate-100 dark:bg-slate-800/80 text-slate-400 dark:text-slate-500 border border-slate-200/80 dark:border-slate-700/60 font-bold text-xs cursor-not-allowed text-center">
                            Niet genoeg energie ({{ $character->energy }}/{{ $heist->energy_cost }})
                        </button>
                    @elseif(! $hasLevel)
                        <button type="button" disabled class="w-full py-2.5 rounded-xl bg-slate-100 dark:bg-slate-800/80 text-slate-400 dark:text-slate-500 border border-slate-200/80 dark:border-slate-700/60 font-bold text-xs cursor-not-allowed text-center">
                            Ontgrendelt op Level {{ $heist->min_level }}
                        </button>
                    @else
                        <form action="{{ route('heists.launch', $heist->id) }}" method="POST">
                            @csrf
                            <button type="submit" 
                                    data-confirm="Weet je zeker dat je de {{ $heist->name }} wilt starten? Bij mislukking riskeer je de isoleercel!"
                                    data-confirm-title="Solo Heist Starten"
                                    data-confirm-btn="Ja, Start Heist!"
                                    data-confirm-type="attack"
                                    data-confirm-icon="fa-bomb"
                                    class="w-full py-3 rounded-xl bg-rose-600 hover:bg-rose-700 dark:bg-rose-600 dark:hover:bg-rose-500 active:scale-95 text-white font-black text-xs uppercase tracking-wider transition-all duration-150 shadow-xs flex items-center justify-center gap-2 cursor-pointer">
                                <i class="fa-solid fa-bomb text-xs"></i>
                                <span>Start Solo Heist</span>
                            </button>
                        </form>
                    @endif
                </div>
            </div>
            @endforeach
        </div>

        <!-- ========================================== -->
        <!-- TAB 2: GROTE BENDE HEISTS (GANG CO-OP) -->
        <!-- ========================================== -->
        <div x-show="mode === 'gang'" class="space-y-6">

            <!-- GANG STATUS HEADER -->
            @if($myGang)
                <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-5 sm:p-6 shadow-xs flex flex-col lg:flex-row items-start lg:items-center justify-between gap-5 relative overflow-hidden">
                    <div class="absolute -right-6 -bottom-6 w-36 h-36 bg-amber-500/10 rounded-full blur-2xl pointer-events-none"></div>

                    <div class="flex items-center gap-4">
                        <div class="w-14 h-14 rounded-2xl bg-amber-50 dark:bg-amber-950/60 border border-amber-200/80 dark:border-amber-800/70 text-amber-600 dark:text-amber-400 flex items-center justify-center text-2xl shrink-0 shadow-2xs">
                            <i class="fa-solid fa-shield-halved"></i>
                        </div>
                        <div>
                            <div class="flex items-center gap-2 flex-wrap">
                                <h2 class="text-base font-black text-slate-900 dark:text-white tracking-tight">
                                    [{{ $myGang->tag }}] {{ $myGang->name }}
                                </h2>
                                <span class="px-2.5 py-0.5 rounded-full bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200/80 dark:border-emerald-800/70 text-emerald-700 dark:text-emerald-300 text-[10px] font-black uppercase tracking-wider">
                                    {{ $myGang->members()->count() }} Leden Actief
                                </span>
                                @if(($myGang->respect ?? 0) > 0)
                                    <span class="px-2.5 py-0.5 rounded-full bg-purple-50 dark:bg-purple-950/60 border border-purple-200/80 dark:border-purple-800/70 text-purple-700 dark:text-purple-300 text-[10px] font-black">
                                        ⭐ {{ number_format($myGang->respect, 0, ',', '.') }} Respect
                                    </span>
                                @endif
                            </div>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 leading-relaxed max-w-xl">
                                Bende Heists keren <strong class="text-slate-800 dark:text-slate-200">50% contanten</strong> rechtstreeks uit naar jou en storten direct <strong class="text-slate-800 dark:text-slate-200">50% in jullie gezamenlijke bendekas</strong> + extra Gang Respect!
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 flex-wrap shrink-0">
                        <!-- Bendekas -->
                        <div class="bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70 rounded-xl px-4 py-2.5 shadow-2xs">
                            <span class="text-[9px] uppercase tracking-wider text-slate-400 dark:text-slate-500 font-extrabold block">Bendekas Saldo</span>
                            <span class="font-mono text-sm font-black text-emerald-600 dark:text-emerald-400">€ {{ number_format($myGang->treasury, 0, ',', '.') }}</span>
                        </div>

                        <!-- Depot Maskers -->
                        <div class="bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70 rounded-xl px-4 py-2.5 shadow-2xs">
                            <span class="text-[9px] uppercase tracking-wider text-slate-400 dark:text-slate-500 font-extrabold block">Bende Depot Maskers</span>
                            <span class="font-mono text-sm font-black text-amber-600 dark:text-amber-400">{{ $myGang->masks ?? 0 }} stuks</span>
                        </div>

                        <!-- Quick Navigation Buttons -->
                        <div class="flex items-center gap-1.5">
                            <a href="{{ route('gangs.index', ['tab' => 'depot']) }}" class="px-3 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 border border-slate-200/80 dark:border-slate-700 font-bold text-xs transition-all duration-150 active:scale-95 shadow-2xs cursor-pointer" title="Bende Depot">
                                <i class="fa-solid fa-box-archive"></i>
                            </a>
                            <a href="{{ route('gangs.garage') }}" class="px-3 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 border border-slate-200/80 dark:border-slate-700 font-bold text-xs transition-all duration-150 active:scale-95 shadow-2xs cursor-pointer" title="Bende Garage">
                                <i class="fa-solid fa-warehouse"></i>
                            </a>
                            <a href="{{ route('gangs.index') }}" class="px-4 py-2 rounded-xl bg-purple-600 hover:bg-purple-700 dark:bg-purple-600 dark:hover:bg-purple-500 text-white font-black text-xs transition-all duration-150 active:scale-95 shadow-xs flex items-center gap-1.5 cursor-pointer">
                                <span>Bendepagina</span>
                                <i class="fa-solid fa-chevron-right text-[10px]"></i>
                            </a>
                        </div>
                    </div>
                </div>
            @else
                <div class="bg-amber-50 dark:bg-amber-950/40 border border-amber-200/80 dark:border-amber-800/70 rounded-2xl p-6 shadow-xs flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-amber-500 text-white flex items-center justify-center text-xl shrink-0 shadow-xs">
                            <i class="fa-solid fa-triangle-exclamation"></i>
                        </div>
                        <div>
                            <h4 class="text-sm font-black text-slate-900 dark:text-white">Geen Bende Gevonden</h4>
                            <p class="text-xs text-slate-600 dark:text-slate-300 mt-0.5 leading-relaxed">
                                Grote Bende Heists vereisen een actieve bende. Sluit je aan bij een bende of richt er zelf een op om mee te doen aan grootschalige overvallen!
                            </p>
                        </div>
                    </div>
                    <a href="{{ route('gangs.index') }}" class="shrink-0 px-5 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-700 dark:bg-purple-600 dark:hover:bg-purple-500 active:scale-95 text-white font-black text-xs transition-all duration-150 shadow-xs flex items-center gap-2 cursor-pointer">
                        <i class="fa-solid fa-users"></i>
                        <span>Bendes Bekijken &amp; Aansluiten</span>
                    </a>
                </div>
            @endif

            <!-- GANG HEIST TARGETS CARDS -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                @foreach($gangHeists as $heist)
                @php
                    $gang = $myGang;
                    $gangMemberCount = $gang ? $gang->members()->count() : 0;
                    $hasGang = !empty($gang);
                    $hasGangMembers = $gangMemberCount >= $heist->min_gang_members;
                    $hasLevel = $character->level >= $heist->min_level;
                    $gangMaskCount = (int) ($gang->masks ?? 0);
                    $hasGangMasks = $gangMaskCount >= $heist->required_mask_count;
                    $hasProperty = $heist->gangHasRequiredProperty($gang);

                    $effectiveVehicle = $heist->getEffectiveVehicle($character);
                    $hasVehicle = !empty($effectiveVehicle);
                    $hasType = $effectiveVehicle && (!$heist->required_vehicle_type || $heist->required_vehicle_type === 'any' || $effectiveVehicle->vehicle->type === $heist->required_vehicle_type);
                    $hasSpeed = $effectiveVehicle && ($effectiveVehicle->effective_speed >= $heist->min_speed_required);
                    $hasCondition = $effectiveVehicle && ($effectiveVehicle->current_condition >= $heist->min_condition_required);
                    $isGangGarageVehicle = $effectiveVehicle && $effectiveVehicle->in_gang_garage;

                    $tierLabel = match (true) {
                        $heist->min_gang_members >= 10 => 'Tier 4: 10+ Leden Quota',
                        $heist->min_gang_members >= 5 => 'Tier 3: 5+ Leden Quota',
                        $heist->min_gang_members >= 3 => 'Tier 2: 3+ Leden Quota',
                        default => 'Tier 1: 2+ Leden Quota',
                    };

                    $tierBadgeColor = match (true) {
                        $heist->min_gang_members >= 10 => 'bg-purple-100 dark:bg-purple-950/70 text-purple-700 dark:text-purple-300 border-purple-300 dark:border-purple-800',
                        $heist->min_gang_members >= 5 => 'bg-rose-100 dark:bg-rose-950/70 text-rose-700 dark:text-rose-300 border-rose-300 dark:border-rose-800',
                        $heist->min_gang_members >= 3 => 'bg-amber-100 dark:bg-amber-950/70 text-amber-800 dark:text-amber-300 border-amber-300 dark:border-amber-800',
                        default => 'bg-blue-100 dark:bg-blue-950/70 text-blue-700 dark:text-blue-300 border-blue-300 dark:border-blue-800',
                    };

                    $baseChance = match ($heist->difficulty) {
                        'makkelijk' => 75,
                        'normaal' => 65,
                        'zwaar' => 52,
                        'extreem' => 42,
                        'legendarisch' => 32,
                        default => 50,
                    };
                    $speedDiff = $effectiveVehicle ? ($effectiveVehicle->effective_speed - $heist->min_speed_required) : 0;
                    $speedBonus = (int) round($speedDiff * 0.4);
                    $condBonus = $effectiveVehicle ? (int) round(($effectiveVehicle->current_condition - 70) * 0.25) : 0;
                    $statBonus = min(15, (int) floor(($character->getEffectiveIntelligence() + $character->getEffectiveDiscipline()) / 30));
                    $gangBonus = min(15, $gangMemberCount * 2);
                    $estChance = max(15, min(92, $baseChance + $speedBonus + $condBonus + $statBonus + $gangBonus));

                    $personalMin = (int) round($heist->payout_min * 0.5);
                    $personalMax = (int) round($heist->payout_max * 0.5);
                    $gangShareMin = $heist->payout_min - $personalMin;
                    $gangShareMax = $heist->payout_max - $personalMax;
                @endphp

                <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 hover:border-purple-300 dark:hover:border-purple-700/80 rounded-2xl p-5 sm:p-6 shadow-xs flex flex-col justify-between gap-5 transition-all duration-200 group relative overflow-hidden">
                    <div class="space-y-4">
                        <!-- Top Row: Icon + Badges -->
                        <div class="flex items-start justify-between gap-2">
                            <div class="w-13 h-13 rounded-2xl bg-gradient-to-br from-purple-600 to-indigo-700 text-white flex items-center justify-center text-2xl shrink-0 group-hover:scale-105 transition-transform shadow-md">
                                <i class="fa-solid {{ $heist->icon }}"></i>
                            </div>
                            <div class="text-right space-y-1">
                                <span class="px-2.5 py-0.5 rounded-full border text-[10px] font-black uppercase tracking-wider {{ $heist->difficulty_badge }}">
                                    {{ $heist->difficulty }}
                                </span>
                                <div class="text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded-md border {{ $tierBadgeColor }} block text-right">
                                    {{ $tierLabel }}
                                </div>
                            </div>
                        </div>

                        <!-- Heist Name & Description -->
                        <div>
                            <div class="flex items-center gap-2 mb-1 flex-wrap">
                                <span class="px-2 py-0.5 rounded-md bg-amber-50 dark:bg-amber-950/60 border border-amber-200/80 dark:border-amber-800/70 text-amber-800 dark:text-amber-300 text-[10px] font-black uppercase">
                                    Bende Co-Op Heist
                                </span>
                                <span class="text-[11px] font-black text-purple-600 dark:text-purple-400">
                                    Level {{ $heist->min_level }}+
                                </span>
                            </div>
                            <h3 class="text-base sm:text-lg font-black text-slate-900 dark:text-white group-hover:text-purple-600 dark:group-hover:text-purple-400 transition-colors">
                                {{ $heist->name }}
                            </h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 leading-relaxed">
                                {{ $heist->description }}
                            </p>
                        </div>

                        <!-- 50/50 Buitverdeling Box -->
                        <div class="bg-slate-50/90 dark:bg-slate-800/60 rounded-xl p-3.5 border border-slate-200/80 dark:border-slate-700/70 space-y-2">
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-slate-600 dark:text-slate-300 font-medium flex items-center gap-1.5">
                                    <i class="fa-solid fa-wallet text-emerald-500"></i> Jouw Deel (50% Contant):
                                </span>
                                <span class="font-black text-emerald-600 dark:text-emerald-400 font-mono">
                                    €{{ number_format($personalMin, 0, ',', '.') }} &ndash; €{{ number_format($personalMax, 0, ',', '.') }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-slate-600 dark:text-slate-300 font-medium flex items-center gap-1.5">
                                    <i class="fa-solid fa-vault text-amber-500"></i> Bendekluis Storting (50%):
                                </span>
                                <span class="font-black text-amber-600 dark:text-amber-400 font-mono">
                                    +€{{ number_format($gangShareMin, 0, ',', '.') }} &ndash; €{{ number_format($gangShareMax, 0, ',', '.') }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between text-xs pt-1.5 border-t border-slate-200/80 dark:border-slate-700/70 text-[11px]">
                                <span class="text-slate-500 dark:text-slate-400">Bende Beloning:</span>
                                <span class="font-extrabold text-purple-700 dark:text-purple-300">+{{ $heist->gang_respect_reward }} Bende Respect ⭐</span>
                            </div>
                        </div>

                        <!-- Checklist Requirements (Compact 2-col Grid) -->
                        <div class="grid grid-cols-2 gap-1.5 text-[11px] pt-2 border-t border-slate-100 dark:border-slate-800">
                            <!-- 1. Gang Leden Quota -->
                            <div class="flex items-center justify-between p-2 rounded-xl {{ $hasGangMembers ? 'bg-emerald-50/70 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 border border-emerald-200/80 dark:border-emerald-800/70' : 'bg-rose-50/70 dark:bg-rose-950/40 text-rose-700 dark:text-rose-400 border border-rose-200/80 dark:border-rose-800/70' }}">
                                <span class="flex items-center gap-1 font-bold truncate">
                                    <i class="fa-solid {{ $hasGangMembers ? 'fa-users text-emerald-600' : 'fa-users text-rose-500' }}"></i>
                                    <span>Leden</span>
                                </span>
                                <span class="font-mono font-black text-[10px]">{{ $gangMemberCount }}/{{ $heist->min_gang_members }}</span>
                            </div>

                            <!-- 2. Depot Maskers -->
                            <div class="flex items-center justify-between p-2 rounded-xl {{ $hasGangMasks ? 'bg-emerald-50/70 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 border border-emerald-200/80 dark:border-emerald-800/70' : 'bg-rose-50/70 dark:bg-rose-950/40 text-rose-700 dark:text-rose-400 border border-rose-200/80 dark:border-rose-800/70' }}">
                                <span class="flex items-center gap-1 font-bold truncate">
                                    <i class="fa-solid fa-mask {{ $hasGangMasks ? 'text-emerald-600' : 'text-rose-500' }}"></i>
                                    <span>Depot</span>
                                </span>
                                <span class="font-mono font-black text-[10px]">{{ $gangMaskCount }}/{{ $heist->required_mask_count }}</span>
                            </div>

                            <!-- 3. Benodigd Voertuig in Bende Garage -->
                            <div class="flex items-center justify-between p-2 rounded-xl {{ ($hasVehicle && $hasCondition && $isGangGarageVehicle) ? 'bg-emerald-50/70 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 border border-emerald-200/80 dark:border-emerald-800/70' : 'bg-rose-50/70 dark:bg-rose-950/40 text-rose-700 dark:text-rose-400 border border-rose-200/80 dark:border-rose-800/70' }}">
                                <span class="flex items-center gap-1 font-bold truncate">
                                    <i class="fa-solid fa-car {{ ($hasVehicle && $hasCondition && $isGangGarageVehicle) ? 'text-emerald-600' : 'text-rose-500' }}"></i>
                                    <span>Garage Wagen</span>
                                </span>
                                <span class="font-mono font-black text-[10px] truncate">
                                    {{ ($hasVehicle && $hasCondition && $isGangGarageVehicle) ? ($effectiveVehicle->current_condition . '%') : 'Nodig' }}
                                </span>
                            </div>

                            <!-- 4. Pand of Energie -->
                            @if($heist->required_property_name)
                            <div class="flex items-center justify-between p-2 rounded-xl {{ $hasProperty ? 'bg-emerald-50/70 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 border border-emerald-200/80 dark:border-emerald-800/70' : 'bg-rose-50/70 dark:bg-rose-950/40 text-rose-700 dark:text-rose-400 border border-rose-200/80 dark:border-rose-800/70' }}">
                                <span class="flex items-center gap-1 font-bold truncate">
                                    <i class="fa-solid fa-building {{ $hasProperty ? 'text-emerald-600' : 'text-rose-500' }}"></i>
                                    <span>Pand</span>
                                </span>
                                <span class="font-mono font-black text-[10px]">{{ $hasProperty ? 'In bezit' : 'Nodig' }}</span>
                            </div>
                            @else
                            <div class="flex items-center justify-between p-2 rounded-xl {{ $character->energy >= $heist->energy_cost ? 'bg-emerald-50/70 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 border border-emerald-200/80 dark:border-emerald-800/70' : 'bg-slate-50 dark:bg-slate-800/50 text-slate-400 dark:text-slate-500 border border-slate-200/80 dark:border-slate-700/70' }}">
                                <span class="flex items-center gap-1 font-bold truncate">
                                    <i class="fa-solid fa-bolt text-amber-500"></i>
                                    <span>Kosten</span>
                                </span>
                                <span class="font-mono font-black text-[10px]">{{ $heist->energy_cost }} EP</span>
                            </div>
                            @endif
                        </div>

                        @if($hasVehicle)
                        <!-- Slagingskans Bar -->
                        <div class="space-y-1 pt-2 border-t border-slate-100 dark:border-slate-800">
                            <div class="flex items-center justify-between text-xs font-bold">
                                <span class="text-slate-600 dark:text-slate-400">Slagingskans (incl. Bende Bonus +{{ $gangBonus }}%):</span>
                                <span class="font-mono {{ $estChance >= 60 ? 'text-emerald-600 dark:text-emerald-400' : ($estChance >= 40 ? 'text-amber-500' : 'text-rose-600 dark:text-rose-400') }}">{{ $estChance }}%</span>
                            </div>
                            <div class="w-full bg-slate-100 dark:bg-slate-800/80 rounded-full h-2 overflow-hidden">
                                <div class="h-full rounded-full transition-all duration-300 {{ $estChance >= 60 ? 'bg-emerald-500' : ($estChance >= 40 ? 'bg-amber-500' : 'bg-rose-500') }}" style="width: {{ $estChance }}%"></div>
                            </div>
                        </div>
                        @endif
                    </div>

                    <!-- Action Button -->
                    <div class="pt-3 border-t border-slate-100 dark:border-slate-800">
                        @if(! $hasGang)
                            <a href="{{ route('gangs.index') }}" 
                               class="w-full py-3 rounded-xl bg-purple-50 dark:bg-purple-950/50 hover:bg-purple-100 dark:hover:bg-purple-900/60 border border-purple-200/80 dark:border-purple-800/70 text-purple-700 dark:text-purple-300 font-black text-xs text-center transition-all duration-150 active:scale-95 flex items-center justify-center gap-1.5 cursor-pointer">
                                <i class="fa-solid fa-users"></i>
                                <span>Sluit je eerst aan bij een Bende</span>
                            </a>
                        @elseif(! $hasGangMembers)
                            <button type="button" disabled class="w-full py-3 rounded-xl bg-slate-100 dark:bg-slate-800/80 text-slate-400 dark:text-slate-500 border border-slate-200/80 dark:border-slate-700/60 font-bold text-xs cursor-not-allowed text-center">
                                Onvoldoende Leden Quota ({{ $gangMemberCount }}/{{ $heist->min_gang_members }})
                            </button>
                        @elseif(! $hasProperty)
                            <a href="{{ route('gangs.panden') }}" 
                               class="w-full py-3 rounded-xl bg-amber-50 dark:bg-amber-950/50 hover:bg-amber-100 dark:hover:bg-amber-900/60 border border-amber-200/80 dark:border-amber-800/70 text-amber-800 dark:text-amber-300 font-black text-xs text-center transition-all duration-150 active:scale-95 flex items-center justify-center gap-1.5 cursor-pointer">
                                <i class="fa-solid fa-building"></i>
                                <span>Koop Pand: {{ $heist->required_property_name }}</span>
                            </a>
                        @elseif(! $hasVehicle || ! $isGangGarageVehicle)
                            <a href="{{ route('gangs.garage') }}" 
                               class="w-full py-3 rounded-xl bg-purple-50 dark:bg-purple-950/50 hover:bg-purple-100 dark:hover:bg-purple-900/60 border border-purple-200/80 dark:border-purple-800/70 text-purple-700 dark:text-purple-300 font-black text-xs text-center transition-all duration-150 active:scale-95 flex items-center justify-center gap-1.5 cursor-pointer">
                                <i class="fa-solid fa-warehouse"></i>
                                <span>Koppel Wagen in Bende Garage</span>
                            </a>
                        @elseif(! $hasType || ! $hasSpeed)
                            <a href="{{ route('garage.index') }}" 
                               class="w-full py-2.5 rounded-xl bg-amber-50 dark:bg-amber-950/50 hover:bg-amber-100 dark:hover:bg-amber-900/60 border border-amber-200/80 dark:border-amber-800/70 text-amber-800 dark:text-amber-300 font-black text-xs text-center transition-all duration-150 active:scale-95 flex items-center justify-center gap-1.5 cursor-pointer">
                                <i class="fa-solid fa-gauge-high"></i>
                                <span>Wagen Vereist &ge;{{ $heist->min_speed_required }} km/u</span>
                            </a>
                        @elseif(! $hasCondition)
                            <a href="{{ route('garage.index') }}" 
                               class="w-full py-3 rounded-xl bg-amber-50 dark:bg-amber-950/50 hover:bg-amber-100 dark:hover:bg-amber-900/60 border border-amber-200/80 dark:border-amber-800/70 text-amber-800 dark:text-amber-300 font-black text-xs text-center transition-all duration-150 active:scale-95 flex items-center justify-center gap-1.5 cursor-pointer">
                                <i class="fa-solid fa-wrench"></i>
                                <span>Wagen Repareren in Garage</span>
                            </a>
                        @elseif(! $hasGangMasks)
                            <a href="{{ route('gangs.index', ['tab' => 'doneren']) }}" 
                               class="w-full py-3 rounded-xl bg-indigo-50 dark:bg-indigo-950/50 hover:bg-indigo-100 dark:hover:bg-indigo-900/60 border border-indigo-200/80 dark:border-indigo-800/70 text-indigo-700 dark:text-indigo-300 font-black text-xs text-center transition-all duration-150 active:scale-95 flex items-center justify-center gap-1.5 cursor-pointer">
                                <i class="fa-solid fa-mask"></i>
                                <span>Doneer {{ $heist->required_mask_count }}x Maskers in Depot</span>
                            </a>
                        @elseif($character->energy < $heist->energy_cost)
                            <button type="button" disabled class="w-full py-3 rounded-xl bg-slate-100 dark:bg-slate-800/80 text-slate-400 dark:text-slate-500 border border-slate-200/80 dark:border-slate-700/60 font-bold text-xs cursor-not-allowed text-center">
                                Niet genoeg energie ({{ $character->energy }}/{{ $heist->energy_cost }})
                            </button>
                        @elseif(! $hasLevel)
                            <button type="button" disabled class="w-full py-3 rounded-xl bg-slate-100 dark:bg-slate-800/80 text-slate-400 dark:text-slate-500 border border-slate-200/80 dark:border-slate-700/60 font-bold text-xs cursor-not-allowed text-center">
                                Vereist Level {{ $heist->min_level }}
                            </button>
                        @else
                            <form action="{{ route('heists.launch', $heist->id) }}" method="POST">
                                @csrf
                                <button type="submit" 
                                        data-confirm="Weet je zeker dat je de Bende Heist {{ $heist->name }} wilt starten namens jullie bende?"
                                        data-confirm-title="Bende Heist Starten"
                                        data-confirm-btn="Ja, Lanceer Heist!"
                                        data-confirm-type="attack"
                                        data-confirm-icon="fa-bomb"
                                        class="w-full py-3.5 rounded-xl bg-rose-600 hover:bg-rose-700 dark:bg-rose-600 dark:hover:bg-rose-500 active:scale-95 text-white font-black text-xs uppercase tracking-wider transition-all duration-150 shadow-xs flex items-center justify-center gap-2 cursor-pointer">
                                    <i class="fa-solid fa-bomb text-xs"></i>
                                    <span>Lanceer Bende Heist Operatie</span>
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>

    </div>

    <!-- RECENT HEISTS TICKER (Clean TBS Kliniek Card) -->
    @if($recentLogs->isNotEmpty())
    <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-5 sm:p-6 shadow-xs space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
            <h3 class="text-xs sm:text-sm font-black text-slate-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
                <i class="fa-solid fa-clock-rotate-left text-purple-600 dark:text-purple-400"></i>
                <span>Recente Heist Operaties</span>
            </h3>
            <span class="text-[11px] font-bold text-slate-400">Laatste 8 acties</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            @foreach($recentLogs as $log)
            <div class="p-3.5 rounded-xl border {{ $log->success ? 'bg-emerald-50/40 dark:bg-emerald-950/20 border-emerald-200/80 dark:border-emerald-800/40' : 'bg-rose-50/40 dark:bg-rose-950/20 border-rose-200/80 dark:border-rose-800/40' }} flex items-center justify-between gap-3 text-xs">
                <div class="flex items-center gap-2.5 min-w-0">
                    <div class="w-8 h-8 rounded-lg {{ $log->success ? 'bg-emerald-600 text-white' : 'bg-rose-600 text-white' }} flex items-center justify-center text-xs shrink-0 shadow-2xs font-bold">
                        <i class="fa-solid {{ $log->success ? 'fa-check' : 'fa-xmark' }}"></i>
                    </div>
                    <div class="min-w-0">
                        <div class="font-black text-slate-900 dark:text-white truncate">
                            {{ $log->character?->name ?? 'Onbekende Speler' }}
                            @if($log->gang)
                                <span class="text-amber-500 font-extrabold text-[10px]">[{{ $log->gang->tag }}]</span>
                            @endif
                            &bull; <span class="font-normal text-slate-600 dark:text-slate-300">{{ $log->heist?->name ?? 'Onbekende Kraak' }}</span>
                        </div>
                        <div class="text-[11px] text-slate-500 dark:text-slate-400">
                            {{ $log->created_at->diffForHumans() }} &bull; {{ $log->vehicle?->name ?? 'Onbekend voertuig' }}
                        </div>
                    </div>
                </div>

                <div class="text-right shrink-0">
                    @if($log->success)
                        <span class="font-black text-emerald-600 dark:text-emerald-400 font-mono block">+€{{ number_format($log->cash_earned, 0, ',', '.') }}</span>
                        @if($log->gang_cash_earned > 0)
                            <span class="text-[10px] text-amber-500 font-bold block">+€{{ number_format($log->gang_cash_earned, 0, ',', '.') }} Bende</span>
                        @endif
                        <span class="text-[10px] text-emerald-500 font-bold">+{{ $log->respect_earned }} Respect</span>
                    @else
                        <span class="font-black text-rose-600 dark:text-rose-400 block">Mislukt</span>
                        <span class="text-[10px] text-rose-500 font-bold">In Isoleercel</span>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

</div>
@endsection
