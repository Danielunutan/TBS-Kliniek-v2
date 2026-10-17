@extends('layouts.app')

@section('title', 'Grote Heists & Bende Overvallen - Het Planningsbord')
@section('meta_description', 'Plan en voer grootschalige overvallen en bende heists uit in TBS Kliniek. Gebruik je getunede vluchtwagen en maskers voor gigantische cash buit en bendekas stortingen.')

@section('content')
<div class="space-y-6">

    <!-- HERO HEADER -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-xs relative overflow-hidden">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 relative z-10">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-purple-600 text-white flex items-center justify-center text-2xl shadow-md shrink-0">
                    <i class="fa-solid fa-masks-theater"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h2 class="text-xl font-black text-slate-900 dark:text-white tracking-tight">Het Grote Heist Planningsbord</h2>
                        <span class="px-2.5 py-0.5 rounded-full bg-rose-100 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 font-black text-[10px] uppercase tracking-wider animate-pulse">High Stakes</span>
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 max-w-2xl leading-relaxed">
                        Bereid solo operaties of grootschalige bende-overvallen voor. Elke heist vereist een <strong class="text-slate-800 dark:text-slate-200">Vluchtvoertuig</strong> uit de garage en <strong class="text-slate-800 dark:text-slate-200">Bivakmutsen/Maskers</strong> om je identiteit geheim te houden. Hoe sneller je wagen en hoe hoger de conditie, hoe groter de slaagkans!
                    </p>
                </div>
            </div>

            <!-- PLAYER HEIST INVENTORY BADGES -->
            <div class="flex items-center gap-3 flex-wrap">
                <!-- Maskers Voorraad -->
                <div class="bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-2.5 flex items-center gap-3 shadow-2xs">
                    <div class="w-9 h-9 rounded-lg bg-indigo-100 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 flex items-center justify-center text-base shrink-0">
                        <i class="fa-solid fa-mask"></i>
                    </div>
                    <div>
                        <span class="text-[10px] uppercase font-bold text-slate-400 dark:text-slate-400 block tracking-wider">Voorraad Maskers</span>
                        <span class="text-sm font-black text-slate-900 dark:text-white font-mono">{{ $character->masks ?? 0 }}x 🎭</span>
                    </div>
                    <a href="{{ route('shop.index', ['tab' => 'items']) }}" title="Koop meer maskers in de kliniekwinkel" class="ml-1 text-xs text-purple-600 dark:text-purple-400 hover:text-purple-700 dark:hover:text-purple-300 font-extrabold">
                        + Koop
                    </a>
                </div>

                <!-- Actief Voertuig Badge -->
                <div class="bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-2.5 flex items-center gap-3 shadow-2xs">
                    <div class="w-9 h-9 rounded-lg bg-purple-100 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 flex items-center justify-center text-base shrink-0">
                        <i class="fa-solid {{ $activeVehicle ? $activeVehicle->vehicle->icon : 'fa-car' }}"></i>
                    </div>
                    <div>
                        <span class="text-[10px] uppercase font-bold text-slate-400 dark:text-slate-400 block tracking-wider">Vluchtvoertuig</span>
                        <span class="text-xs font-black text-slate-900 dark:text-white truncate max-w-[140px] block">
                            {{ $activeVehicle ? $activeVehicle->vehicle->name : 'Geen voertuig' }}
                        </span>
                    </div>
                    <a href="{{ route('garage.index') }}" title="Beheer je garage" class="ml-1 text-xs text-purple-600 dark:text-purple-400 hover:text-purple-700 dark:hover:text-purple-300 font-extrabold">
                        Garage
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- HEIST TARGETS CONTAINER (SOLO VS GANG TABS) -->
    <div x-data="{ mode: 'solo' }" class="space-y-6">

        <!-- MODE SWITCHER TABS -->
        <div class="flex items-center gap-2 border-b border-slate-200 dark:border-slate-800 pb-3">
            <button type="button" @click="mode = 'solo'" 
                    :class="mode === 'solo' ? 'bg-purple-600 text-white shadow-xs font-black' : 'bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold'"
                    class="px-5 py-2.5 rounded-xl text-xs transition cursor-pointer flex items-center gap-2">
                <i class="fa-solid fa-user-secret"></i>
                <span>Solo Overvallen ({{ count($soloHeists) }})</span>
            </button>

            <button type="button" @click="mode = 'gang'" 
                    :class="mode === 'gang' ? 'bg-purple-600 text-white shadow-xs font-black' : 'bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold'"
                    class="px-5 py-2.5 rounded-xl text-xs transition cursor-pointer flex items-center gap-2 relative">
                <i class="fa-solid fa-users text-amber-400"></i>
                <span>Grote Bende Heists ({{ count($gangHeists) }})</span>
                <span class="px-1.5 py-0.2 rounded-full bg-amber-400 text-slate-950 font-black text-[9px] uppercase tracking-wider">Co-Op</span>
            </button>
        </div>

        <!-- TAB 1: SOLO HEISTS -->
        <div x-show="mode === 'solo'" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            @foreach($soloHeists as $heist)
            @php
                $hasLevel = $character->level >= $heist->min_level;
                $hasMasks = ($character->masks ?? 0) >= $heist->required_mask_count;
                $hasVehicle = !empty($activeVehicle);
                $hasCondition = $activeVehicle && ($activeVehicle->current_condition >= $heist->min_condition_required);
                $canLaunch = $hasLevel && $hasMasks && $hasVehicle && $hasCondition && ($character->energy >= $heist->energy_cost);

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

            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 hover:border-purple-300 dark:hover:border-purple-600 rounded-2xl p-5 shadow-xs flex flex-col justify-between gap-5 transition group">
                <div class="space-y-3.5">
                    <div class="flex items-start justify-between gap-2">
                        <div class="w-12 h-12 rounded-xl bg-purple-100 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 flex items-center justify-center text-xl shrink-0 group-hover:scale-105 transition-transform shadow-2xs">
                            <i class="fa-solid {{ $heist->icon }}"></i>
                        </div>
                        <div class="text-right space-y-1">
                            <span class="px-2.5 py-0.5 rounded-full border text-[10px] font-black uppercase tracking-wider {{ $heist->difficulty_badge }}">
                                {{ $heist->difficulty }}
                            </span>
                            <div class="text-[11px] font-bold text-slate-400 dark:text-slate-500">Level {{ $heist->min_level }}+</div>
                        </div>
                    </div>

                    <div>
                        <h3 class="text-sm font-black text-slate-900 dark:text-white group-hover:text-purple-600 dark:group-hover:text-purple-400 transition-colors">{{ $heist->name }}</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 leading-relaxed">{{ $heist->description }}</p>
                    </div>

                    <!-- Rewards Preview -->
                    <div class="bg-slate-50 dark:bg-slate-800/60 rounded-xl p-3 border border-slate-100 dark:border-slate-800 space-y-1.5">
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
                                <i class="fa-solid fa-star text-purple-500"></i> XP & Respect:
                            </span>
                            <span class="font-bold text-slate-900 dark:text-slate-200">
                                +{{ $heist->xp_reward }} XP / +{{ $heist->respect_reward }} Respect
                            </span>
                        </div>
                    </div>

                    <!-- Requirements Checklist -->
                    <div class="space-y-1.5 text-xs pt-1 border-t border-slate-100 dark:border-slate-800">
                        <div class="flex items-center justify-between {{ $hasLevel ? 'text-emerald-700 dark:text-emerald-400' : 'text-slate-400 dark:text-slate-500' }}">
                            <span class="flex items-center gap-1.5">
                                <i class="fa-solid {{ $hasLevel ? 'fa-circle-check text-emerald-600 dark:text-emerald-400' : 'fa-circle-xmark text-slate-300 dark:text-slate-600' }}"></i>
                                Minimaal Level {{ $heist->min_level }}
                            </span>
                            <span class="font-mono text-[11px] font-bold">{{ $character->level }}/{{ $heist->min_level }}</span>
                        </div>

                        <div class="flex items-center justify-between {{ $hasMasks ? 'text-emerald-700 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400 font-semibold' }}">
                            <span class="flex items-center gap-1.5">
                                <i class="fa-solid {{ $hasMasks ? 'fa-circle-check text-emerald-600 dark:text-emerald-400' : 'fa-circle-xmark text-rose-500' }}"></i>
                                {{ $heist->required_mask_count }}x Bivakmuts / Masker
                            </span>
                            <span class="font-mono text-[11px] font-bold">{{ $character->masks ?? 0 }}/{{ $heist->required_mask_count }}</span>
                        </div>

                        <div class="flex items-center justify-between {{ $hasCondition ? 'text-emerald-700 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400 font-semibold' }}">
                            <span class="flex items-center gap-1.5">
                                <i class="fa-solid {{ $hasCondition ? 'fa-circle-check text-emerald-600 dark:text-emerald-400' : 'fa-circle-xmark text-rose-500' }}"></i>
                                Min. {{ $heist->min_condition_required }}% Wagen Conditie
                            </span>
                            <span class="font-mono text-[11px] font-bold">{{ $activeVehicle ? $activeVehicle->current_condition : 0 }}%</span>
                        </div>

                        <div class="flex items-center justify-between {{ $character->energy >= $heist->energy_cost ? 'text-emerald-700 dark:text-emerald-400' : 'text-slate-400 dark:text-slate-500' }}">
                            <span class="flex items-center gap-1.5">
                                <i class="fa-solid fa-bolt text-amber-500 text-[10px]"></i>
                                Energie Kosten
                            </span>
                            <span class="font-mono text-[11px] font-bold">{{ $heist->energy_cost }} Energie</span>
                        </div>
                    </div>

                    @if($hasVehicle)
                    <div class="space-y-1 pt-2 border-t border-slate-100 dark:border-slate-800">
                        <div class="flex items-center justify-between text-xs font-bold">
                            <span class="text-slate-600 dark:text-slate-400">Geschatte Slagingskans:</span>
                            <span class="font-mono {{ $estChance >= 60 ? 'text-emerald-600 dark:text-emerald-400' : ($estChance >= 40 ? 'text-amber-500' : 'text-rose-600 dark:text-rose-400') }}">{{ $estChance }}%</span>
                        </div>
                        <div class="w-full bg-slate-100 dark:bg-slate-800 rounded-full h-2 overflow-hidden">
                            <div class="h-full rounded-full transition-all {{ $estChance >= 60 ? 'bg-emerald-500' : ($estChance >= 40 ? 'bg-amber-500' : 'bg-rose-500') }}" style="width: {{ $estChance }}%"></div>
                        </div>
                    </div>
                    @endif
                </div>

                <!-- Action Button -->
                <div class="pt-2 border-t border-slate-100 dark:border-slate-800">
                    @if(! $hasVehicle)
                        <a href="{{ route('garage.index') }}" 
                           class="w-full py-2.5 rounded-xl bg-purple-50 dark:bg-purple-950/50 hover:bg-purple-100 dark:hover:bg-purple-900/60 border border-purple-200 dark:border-purple-800/60 text-purple-700 dark:text-purple-300 font-black text-xs text-center transition flex items-center justify-center gap-1.5">
                            <i class="fa-solid fa-warehouse"></i>
                            <span>Koop Vluchtwagen in Garage</span>
                        </a>
                    @elseif(! $hasCondition)
                        <a href="{{ route('garage.index') }}" 
                           class="w-full py-2.5 rounded-xl bg-amber-50 dark:bg-amber-950/50 hover:bg-amber-100 dark:hover:bg-amber-900/60 border border-amber-300 dark:border-amber-800/60 text-amber-800 dark:text-amber-300 font-black text-xs text-center transition flex items-center justify-center gap-1.5">
                            <i class="fa-solid fa-wrench"></i>
                            <span>Wagen Repareren in Garage</span>
                        </a>
                    @elseif(! $hasMasks)
                        <a href="{{ route('shop.index', ['tab' => 'items']) }}" 
                           class="w-full py-2.5 rounded-xl bg-indigo-50 dark:bg-indigo-950/50 hover:bg-indigo-100 dark:hover:bg-indigo-900/60 border border-indigo-200 dark:border-indigo-800/60 text-indigo-700 dark:text-indigo-300 font-black text-xs text-center transition flex items-center justify-center gap-1.5">
                            <i class="fa-solid fa-mask"></i>
                            <span>Koop Maskers in Winkel</span>
                        </a>
                    @elseif($character->energy < $heist->energy_cost)
                        <button type="button" disabled class="w-full py-2.5 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-slate-500 font-bold text-xs cursor-not-allowed text-center">
                            Niet genoeg energie ({{ $character->energy }}/{{ $heist->energy_cost }})
                        </button>
                    @elseif(! $hasLevel)
                        <button type="button" disabled class="w-full py-2.5 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-slate-500 font-bold text-xs cursor-not-allowed text-center">
                            Ontgrendelt op Level {{ $heist->min_level }}
                        </button>
                    @else
                        <form action="{{ route('heists.launch', $heist->id) }}" method="POST">
                            @csrf
                            <button type="submit" 
                                    onclick="return confirm('Weet je zeker dat je de {{ $heist->name }} wilt starten? Bij mislukking riskeer je de isoleercel!')"
                                    class="w-full py-3 rounded-xl bg-rose-600 hover:bg-rose-700 active:scale-95 text-white font-black text-xs uppercase tracking-wider transition shadow-md flex items-center justify-center gap-2 cursor-pointer">
                                <i class="fa-solid fa-bomb text-xs"></i>
                                <span>Start Solo Heist</span>
                            </button>
                        </form>
                    @endif
                </div>
            </div>
            @endforeach
        </div>

        <!-- TAB 2: GROTE BENDE HEISTS (GANG CO-OP) -->
        <div x-show="mode === 'gang'" class="space-y-5">

            <!-- GANG STATUS HEADER -->
            @if($character->gang)
                <div class="bg-gradient-to-r from-purple-950 via-slate-900 to-slate-900 border border-purple-500/40 rounded-2xl p-5 shadow-xs flex flex-col md:flex-row items-start md:items-center justify-between gap-4 text-white">
                    <div class="flex items-center gap-3.5">
                        <div class="w-12 h-12 rounded-xl bg-amber-500/20 border border-amber-400/40 text-amber-400 flex items-center justify-center text-xl shrink-0 shadow-inner">
                            <i class="fa-solid fa-shield-halved"></i>
                        </div>
                        <div>
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="text-sm font-black text-white tracking-tight">[{{ $character->gang->tag }}] {{ $character->gang->name }}</span>
                                <span class="px-2 py-0.5 rounded-full bg-emerald-500/20 text-emerald-300 text-[10px] font-black uppercase tracking-wider">
                                    {{ $character->gang->members()->count() }} Leden Actief
                                </span>
                            </div>
                            <p class="text-xs text-slate-300 mt-0.5 leading-relaxed">
                                Bende Heists keren <strong>50% persoonlijke contanten</strong> uit en storten direct <strong>50% in jullie bendekas</strong> + extra Bende Respect!
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-4 bg-slate-950/70 border border-purple-500/30 rounded-xl px-4 py-2.5 shrink-0">
                        <div>
                            <span class="text-[9px] uppercase tracking-wider text-slate-400 font-extrabold block">Huidige Bendekas</span>
                            <span class="font-mono text-sm font-black text-emerald-400">€ {{ number_format($character->gang->treasury, 0, ',', '.') }}</span>
                        </div>
                        <a href="{{ route('gangs.index') }}" class="px-3 py-1.5 rounded-lg bg-purple-600 hover:bg-purple-700 text-white font-extrabold text-xs transition">
                            Bendepagina
                        </a>
                    </div>
                </div>
            @else
                <div class="bg-amber-50 dark:bg-amber-950/40 border border-amber-300 dark:border-amber-800/60 rounded-2xl p-6 shadow-xs flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="flex items-center gap-3.5">
                        <div class="w-12 h-12 rounded-xl bg-amber-500 text-white flex items-center justify-center text-xl shrink-0 shadow-xs">
                            <i class="fa-solid fa-triangle-exclamation"></i>
                        </div>
                        <div>
                            <h4 class="text-sm font-black text-slate-900 dark:text-white">Geen Gang of Bende Gevonden</h4>
                            <p class="text-xs text-slate-600 dark:text-slate-300 mt-0.5 leading-relaxed">
                                Bende Heists vereisen een actieve gang. Sluit je aan bij een bende of richt er zelf een op om mee te doen aan grootschalige overvallen!
                            </p>
                        </div>
                    </div>
                    <a href="{{ route('gangs.index') }}" class="shrink-0 px-4 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-700 active:scale-95 text-white font-black text-xs transition shadow-xs flex items-center gap-2">
                        <i class="fa-solid fa-users"></i>
                        <span>Bendes Bekijken & Aansluiten</span>
                    </a>
                </div>
            @endif

            <!-- GANG HEIST TARGETS CARDS -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                @foreach($gangHeists as $heist)
                @php
                    $gang = $character->gang;
                    $gangMemberCount = $gang ? $gang->members()->count() : 0;
                    $hasGang = !empty($gang);
                    $hasGangMembers = $gangMemberCount >= $heist->min_gang_members;
                    $hasLevel = $character->level >= $heist->min_level;
                    $hasMasks = ($character->masks ?? 0) >= $heist->required_mask_count;
                    $hasVehicle = !empty($activeVehicle);
                    $hasCondition = $activeVehicle && ($activeVehicle->current_condition >= $heist->min_condition_required);

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
                    $gangBonus = min(12, $gangMemberCount * 2);
                    $estChance = max(15, min(92, $baseChance + $speedBonus + $condBonus + $statBonus + $gangBonus));

                    $personalMin = (int) round($heist->payout_min * 0.5);
                    $personalMax = (int) round($heist->payout_max * 0.5);
                    $gangShareMin = $heist->payout_min - $personalMin;
                    $gangShareMax = $heist->payout_max - $personalMax;
                @endphp

                <div class="bg-white dark:bg-slate-900 border border-purple-200 dark:border-purple-800/60 hover:border-purple-400 dark:hover:border-purple-500 rounded-2xl p-6 shadow-xs flex flex-col justify-between gap-5 transition group relative overflow-hidden">
                    <div class="space-y-4">
                        <div class="flex items-start justify-between gap-2">
                            <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-purple-600 to-indigo-700 text-white flex items-center justify-center text-2xl shrink-0 group-hover:scale-105 transition-transform shadow-md">
                                <i class="fa-solid {{ $heist->icon }}"></i>
                            </div>
                            <div class="text-right space-y-1">
                                <span class="px-2.5 py-0.5 rounded-full border text-[10px] font-black uppercase tracking-wider {{ $heist->difficulty_badge }}">
                                    {{ $heist->difficulty }}
                                </span>
                                <div class="text-[11px] font-bold text-purple-600 dark:text-purple-400">
                                    Min. {{ $heist->min_gang_members }} Gangleden
                                </div>
                            </div>
                        </div>

                        <div>
                            <div class="flex items-center gap-1.5 mb-1">
                                <span class="px-2 py-0.5 rounded-md bg-amber-100 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 text-[10px] font-black uppercase">
                                    Officiële Gang Heist
                                </span>
                            </div>
                            <h3 class="text-base font-black text-slate-900 dark:text-white group-hover:text-purple-600 dark:group-hover:text-purple-400 transition-colors">{{ $heist->name }}</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 leading-relaxed">{{ $heist->description }}</p>
                        </div>

                        <!-- 50/50 Buitverdeling Box -->
                        <div class="bg-purple-50/60 dark:bg-slate-800/80 rounded-xl p-3.5 border border-purple-100 dark:border-purple-900/50 space-y-2">
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
                            <div class="flex items-center justify-between text-xs pt-1 border-t border-purple-100 dark:border-purple-900/60 text-[11px]">
                                <span class="text-slate-500 dark:text-slate-400">Beloning Bende Respect:</span>
                                <span class="font-extrabold text-purple-700 dark:text-purple-300">+{{ $heist->gang_respect_reward }} Gang Respect ⭐</span>
                            </div>
                        </div>

                        <!-- Checklist Requirements -->
                        <div class="space-y-1.5 text-xs pt-1 border-t border-slate-100 dark:border-slate-800">
                            <div class="flex items-center justify-between {{ $hasGangMembers ? 'text-emerald-700 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400 font-semibold' }}">
                                <span class="flex items-center gap-1.5">
                                    <i class="fa-solid {{ $hasGangMembers ? 'fa-circle-check text-emerald-600 dark:text-emerald-400' : 'fa-circle-xmark text-rose-500' }}"></i>
                                    Minimaal {{ $heist->min_gang_members }} Bendeleden in Gang
                                </span>
                                <span class="font-mono text-[11px] font-bold">{{ $gangMemberCount }}/{{ $heist->min_gang_members }}</span>
                            </div>

                            <div class="flex items-center justify-between {{ $hasMasks ? 'text-emerald-700 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400 font-semibold' }}">
                                <span class="flex items-center gap-1.5">
                                    <i class="fa-solid {{ $hasMasks ? 'fa-circle-check text-emerald-600 dark:text-emerald-400' : 'fa-circle-xmark text-rose-500' }}"></i>
                                    {{ $heist->required_mask_count }}x Bivakmutsen / Maskers
                                </span>
                                <span class="font-mono text-[11px] font-bold">{{ $character->masks ?? 0 }}/{{ $heist->required_mask_count }}</span>
                            </div>

                            <div class="flex items-center justify-between {{ $hasCondition ? 'text-emerald-700 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400 font-semibold' }}">
                                <span class="flex items-center gap-1.5">
                                    <i class="fa-solid {{ $hasCondition ? 'fa-circle-check text-emerald-600 dark:text-emerald-400' : 'fa-circle-xmark text-rose-500' }}"></i>
                                    Min. {{ $heist->min_condition_required }}% Wagen Conditie
                                </span>
                                <span class="font-mono text-[11px] font-bold">{{ $activeVehicle ? $activeVehicle->current_condition : 0 }}%</span>
                            </div>
                        </div>

                        @if($hasVehicle)
                        <div class="space-y-1 pt-2 border-t border-slate-100 dark:border-slate-800">
                            <div class="flex items-center justify-between text-xs font-bold">
                                <span class="text-slate-600 dark:text-slate-400">Slagingskans (incl. Gang Bonus +{{ $gangBonus }}%):</span>
                                <span class="font-mono {{ $estChance >= 60 ? 'text-emerald-600 dark:text-emerald-400' : ($estChance >= 40 ? 'text-amber-500' : 'text-rose-600 dark:text-rose-400') }}">{{ $estChance }}%</span>
                            </div>
                            <div class="w-full bg-slate-100 dark:bg-slate-800 rounded-full h-2 overflow-hidden">
                                <div class="h-full rounded-full transition-all {{ $estChance >= 60 ? 'bg-emerald-500' : ($estChance >= 40 ? 'bg-amber-500' : 'bg-rose-500') }}" style="width: {{ $estChance }}%"></div>
                            </div>
                        </div>
                        @endif
                    </div>

                    <!-- Action Button -->
                    <div class="pt-2 border-t border-slate-100 dark:border-slate-800">
                        @if(! $hasGang)
                            <a href="{{ route('gangs.index') }}" 
                               class="w-full py-3 rounded-xl bg-purple-50 dark:bg-purple-950/50 hover:bg-purple-100 dark:hover:bg-purple-900/60 border border-purple-200 dark:border-purple-800/60 text-purple-700 dark:text-purple-300 font-black text-xs text-center transition flex items-center justify-center gap-1.5">
                                <i class="fa-solid fa-users"></i>
                                <span>Sluit je eerst aan bij een Bende</span>
                            </a>
                        @elseif(! $hasGangMembers)
                            <button type="button" disabled class="w-full py-3 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-slate-500 font-bold text-xs cursor-not-allowed text-center">
                                Onvoldoende Gangleden ({{ $gangMemberCount }}/{{ $heist->min_gang_members }})
                            </button>
                        @elseif(! $hasVehicle)
                            <a href="{{ route('garage.index') }}" 
                               class="w-full py-3 rounded-xl bg-purple-50 dark:bg-purple-950/50 hover:bg-purple-100 border border-purple-200 dark:border-purple-800/60 text-purple-700 dark:text-purple-300 font-black text-xs text-center transition flex items-center justify-center gap-1.5">
                                <i class="fa-solid fa-warehouse"></i>
                                <span>Koop Vluchtwagen in Garage</span>
                            </a>
                        @elseif(! $hasCondition)
                            <a href="{{ route('garage.index') }}" 
                               class="w-full py-3 rounded-xl bg-amber-50 dark:bg-amber-950/50 hover:bg-amber-100 border border-amber-300 text-amber-800 font-black text-xs text-center transition flex items-center justify-center gap-1.5">
                                <i class="fa-solid fa-wrench"></i>
                                <span>Wagen Repareren in Garage</span>
                            </a>
                        @elseif(! $hasMasks)
                            <a href="{{ route('shop.index', ['tab' => 'items']) }}" 
                               class="w-full py-3 rounded-xl bg-indigo-50 dark:bg-indigo-950/50 hover:bg-indigo-100 border border-indigo-200 text-indigo-700 font-black text-xs text-center transition flex items-center justify-center gap-1.5">
                                <i class="fa-solid fa-mask"></i>
                                <span>Koop Maskers in Winkel</span>
                            </a>
                        @elseif($character->energy < $heist->energy_cost)
                            <button type="button" disabled class="w-full py-3 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-slate-500 font-bold text-xs cursor-not-allowed text-center">
                                Niet genoeg energie ({{ $character->energy }}/{{ $heist->energy_cost }})
                            </button>
                        @elseif(! $hasLevel)
                            <button type="button" disabled class="w-full py-3 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-slate-500 font-bold text-xs cursor-not-allowed text-center">
                                Vereist Level {{ $heist->min_level }}
                            </button>
                        @else
                            <form action="{{ route('heists.launch', $heist->id) }}" method="POST">
                                @csrf
                                <button type="submit" 
                                        onclick="return confirm('Weet je zeker dat je de Bende Heist {{ $heist->name }} wilt starten namens je gang?')"
                                        class="w-full py-3.5 rounded-xl bg-gradient-to-r from-rose-600 to-purple-600 hover:from-rose-500 hover:to-purple-500 active:scale-95 text-white font-black text-xs uppercase tracking-wider transition shadow-md flex items-center justify-center gap-2 cursor-pointer">
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

    <!-- RECENT HEISTS TICKER -->
    @if($recentLogs->isNotEmpty())
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-xs space-y-4">
        <h3 class="text-sm font-black text-slate-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
            <i class="fa-solid fa-clock-rotate-left text-purple-600 dark:text-purple-400"></i>
            <span>Recente Heist Operaties</span>
        </h3>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            @foreach($recentLogs as $log)
            <div class="p-3.5 rounded-xl border {{ $log->success ? 'bg-emerald-50/50 dark:bg-emerald-950/30 border-emerald-200 dark:border-emerald-800/50' : 'bg-rose-50/50 dark:bg-rose-950/30 border-rose-200 dark:border-rose-800/50' }} flex items-center justify-between gap-3 text-xs">
                <div class="flex items-center gap-2.5 min-w-0">
                    <div class="w-8 h-8 rounded-lg {{ $log->success ? 'bg-emerald-600 text-white' : 'bg-rose-600 text-white' }} flex items-center justify-center text-xs shrink-0 shadow-2xs font-bold">
                        <i class="fa-solid {{ $log->success ? 'fa-check' : 'fa-xmark' }}"></i>
                    </div>
                    <div class="min-w-0">
                        <div class="font-black text-slate-900 dark:text-white truncate">
                            {{ $log->character->name }}
                            @if($log->gang)
                                <span class="text-amber-500 font-extrabold text-[10px]">[{{ $log->gang->tag }}]</span>
                            @endif
                            &bull; <span class="font-normal text-slate-600 dark:text-slate-300">{{ $log->heist->name }}</span>
                        </div>
                        <div class="text-[11px] text-slate-500 dark:text-slate-400">
                            {{ $log->created_at->diffForHumans() }} &bull; {{ $log->vehicle?->name ?? 'Onbekend' }}
                        </div>
                    </div>
                </div>

                <div class="text-right shrink-0">
                    @if($log->success)
                        <span class="font-black text-emerald-600 dark:text-emerald-400 font-mono block">+€{{ number_format($log->cash_earned, 0, ',', '.') }}</span>
                        @if($log->gang_cash_earned > 0)
                            <span class="text-[10px] text-amber-500 font-bold block">+€{{ number_format($log->gang_cash_earned, 0, ',', '.') }} Gang</span>
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
