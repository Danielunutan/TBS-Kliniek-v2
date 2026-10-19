@extends('layouts.app')

@section('title', 'Bende Panden, Fabrieken & Garage - ' . $gang->name . ' - TBS Kliniek')

@section('content')
<div class="space-y-6" x-data="{
    activeCategory: 'all',
    selectedProperty: null,
    showNpcModal: false,
    propCd: {{ $rotatingProperties['seconds_left'] ?? 1800 }},
    npcCd: {{ $rotatingNpcs['seconds_left'] ?? 1800 }},
    init() {
        setInterval(() => {
            if (this.propCd > 0) this.propCd--;
            if (this.npcCd > 0) this.npcCd--;
        }, 1000);
    },
    formatTime(sec) {
        const m = Math.floor(sec / 60);
        const s = sec % 60;
        return (m < 10 ? '0' : '') + m + ':' + (s < 10 ? '0' : '') + s;
    }
}">

    <!-- Top Hero Header -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 sm:p-7 shadow-xs relative overflow-hidden">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-5 relative z-10">
            <div class="space-y-2">
                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ route('gangs.index') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-purple-600 dark:text-purple-400 hover:underline">
                        <i class="fa-solid fa-arrow-left"></i> Terug naar Bende
                    </a>
                    <span class="text-slate-300 dark:text-slate-700">•</span>
                    <a href="{{ route('gangs.garage') }}" class="inline-flex items-center gap-1.5 text-xs font-black text-white bg-purple-600 hover:bg-purple-700 px-2.5 py-0.5 rounded-full shadow-2xs transition">
                        <i class="fa-solid fa-warehouse"></i> Bende Garage ({{ $storedVehiclesCount ?? 0 }}/{{ $totalVehicles ?? 0 }} wagens)
                    </a>
                    <span class="text-slate-300 dark:text-slate-700">•</span>
                    <a href="{{ route('marketplace.index', ['type' => 'property']) }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-emerald-600 dark:text-emerald-400 hover:underline">
                        <i class="fa-solid fa-shop"></i> Marktplaats Panden
                    </a>
                    <span class="text-slate-300 dark:text-slate-700">•</span>
                    <span class="px-2.5 py-0.5 rounded-full bg-purple-100 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 font-black text-[10px] uppercase tracking-wider">
                        [{{ $gang->tag }}] {{ $gang->name }}
                    </span>
                    @if($isOfficer)
                        <span class="px-2 py-0.5 rounded-full bg-amber-100 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 font-extrabold text-[10px]">
                            <i class="fa-solid fa-crown mr-1"></i> Beheerder
                        </span>
                    @endif
                </div>
                <h1 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight">
                    Bende Panden, Garage & Vastgoed
                </h1>
                <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 max-w-2xl leading-relaxed">
                    Breid het territorium van je bende uit. Koop garage- en opslagpanden voor jullie wagenpark, start winstgevende fabrieken en vestig luxe villa's om gespecialiseerde NPC bendeleden te huisvesten!
                </p>
            </div>

            <!-- Gang Treasury Box -->
            <div class="bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-2xl p-4 sm:p-5 shrink-0 flex flex-col items-center lg:items-end justify-center">
                <span class="text-[10px] font-black uppercase tracking-wider text-slate-400">Beschikbare Bendekas</span>
                <span class="text-2xl sm:text-3xl font-black font-mono text-emerald-600 dark:text-emerald-400 mt-0.5">
                    € {{ number_format($gang->treasury, 0, ',', '.') }}
                </span>
                <span class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">
                    Panden & NPC's worden gefinancierd via de bendekas
                </span>
            </div>
        </div>
    </div>

    <!-- Stats Overview Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Storage Capacity -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 sm:p-5 shadow-xs">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-black uppercase text-slate-500">Collectieve Opslag</span>
                <div class="w-8 h-8 rounded-xl bg-amber-50 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400 flex items-center justify-center text-sm">
                    <i class="fa-solid fa-boxes-stacked"></i>
                </div>
            </div>
            <div class="text-2xl font-black font-mono text-slate-900 dark:text-white">
                +{{ number_format($totalStorage, 0, ',', '.') }} <span class="text-xs text-slate-400 font-sans font-bold">slots</span>
            </div>
            <p class="text-[11px] text-slate-500 mt-1">Extra kluis- & buitcapaciteit</p>
        </div>

        <!-- Vehicle Slots & Bende Garage Link -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 sm:p-5 shadow-xs">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-black uppercase text-slate-500">Voertuig Stallingen</span>
                <div class="w-8 h-8 rounded-xl bg-blue-50 dark:bg-blue-950/50 text-blue-600 dark:text-blue-400 flex items-center justify-center text-sm">
                    <i class="fa-solid fa-warehouse"></i>
                </div>
            </div>
            <div class="flex items-baseline gap-2">
                <span class="text-2xl font-black font-mono text-slate-900 dark:text-white">{{ $storedVehiclesCount ?? 0 }} / {{ $totalVehicles }}</span>
                <span class="text-xs text-slate-400 font-sans font-bold">wagens</span>
            </div>
            <div class="mt-2">
                <a href="{{ route('gangs.garage') }}" class="text-[11px] font-black text-purple-600 dark:text-purple-400 hover:underline flex items-center gap-1">
                    <span>Open Bende Garage</span>
                    <i class="fa-solid fa-arrow-right text-[10px]"></i>
                </a>
            </div>
        </div>

        <!-- NPC Capacity -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 sm:p-5 shadow-xs">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-black uppercase text-slate-500">NPC Bendeleden</span>
                <div class="w-8 h-8 rounded-xl bg-purple-50 dark:bg-purple-950/50 text-purple-600 dark:text-purple-400 flex items-center justify-center text-sm">
                    <i class="fa-solid fa-user-ninja"></i>
                </div>
            </div>
            <div class="text-2xl font-black font-mono text-purple-600 dark:text-purple-400">
                {{ $hiredNpcs->count() }} / {{ $totalNpcCap }}
            </div>
            <p class="text-[11px] text-slate-500 mt-1">
                @if($availableNpcSlots > 0)
                    <span class="text-emerald-600 dark:text-emerald-400 font-bold">{{ $availableNpcSlots }} vrije kamer(s) in villa</span>
                @else
                    Koop een villa voor kamers
                @endif
            </p>
        </div>

        <!-- Owned Properties -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 sm:p-5 shadow-xs">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-black uppercase text-slate-500">In Bendebezit</span>
                <div class="w-8 h-8 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-sm">
                    <i class="fa-solid fa-city"></i>
                </div>
            </div>
            <div class="text-2xl font-black font-mono text-slate-900 dark:text-white">
                {{ $ownedProperties->count() }} / {{ count($catalog) }} <span class="text-xs text-slate-400 font-sans font-bold">panden</span>
            </div>
            <p class="text-[11px] text-slate-500 mt-1">Actief kliniek vastgoed</p>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- 30-MINUTE ROTATING PANDEN SHOWROOM BANNER  -->
    <!-- ========================================== -->
    <div class="bg-gradient-to-r from-purple-900/90 via-slate-900 to-indigo-950 border border-purple-500/30 rounded-2xl p-5 shadow-lg relative overflow-hidden text-white">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 relative z-10">
            <div class="space-y-1">
                <div class="flex items-center gap-2">
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-amber-400 text-slate-950 animate-pulse">
                        30-Min Rotatie
                    </span>
                    <span class="text-xs font-bold text-purple-200">Elk half uur nieuwe panden & exclusieve kortingen!</span>
                </div>
                <h3 class="text-lg font-black tracking-tight">Actuele Vastgoed Rotatie Aanbiedingen</h3>
                <p class="text-xs text-slate-300">
                    Net als bij de Smokkel Garage roteert het aanbod van bende panden elk half uur (:00 en :30). Grijp tijdelijke kortingen met de bendekas!
                </p>
            </div>

            <!-- Countdown Timer -->
            <div class="bg-black/40 border border-purple-500/30 rounded-xl px-4 py-2.5 flex items-center gap-3 shrink-0">
                <div class="w-8 h-8 rounded-lg bg-purple-500/20 text-purple-300 flex items-center justify-center text-sm">
                    <i class="fa-solid fa-stopwatch animate-pulse"></i>
                </div>
                <div>
                    <span class="text-[10px] uppercase font-bold text-slate-400 block">Nieuwe Panden Over</span>
                    <span class="text-base font-black font-mono text-amber-300" x-text="formatTime(propCd)"></span>
                </div>
            </div>
        </div>

        <!-- Rotating Cards Strip -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 mt-4 pt-3 border-t border-purple-500/20">
            @foreach($rotatingProperties['listings'] ?? [] as $offer)
                @php
                    $p = $offer['property'];
                    $alreadyOwned = $ownedProperties->has($p['key']);
                    $dynPrice = $offer['dynamic_price'];
                    $canAffordRot = (int) $gang->treasury >= $dynPrice;
                @endphp
                <div class="bg-slate-900/80 border {{ $alreadyOwned ? 'border-emerald-500/50' : 'border-purple-500/30' }} rounded-xl p-3.5 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between gap-1 mb-1.5">
                            <span class="text-[10px] font-black uppercase tracking-wider text-purple-300">{{ $p['category_label'] }}</span>
                            @if($offer['discount_pct'] > 0)
                                <span class="px-1.5 py-0.2 rounded text-[9px] font-black bg-emerald-500 text-slate-950">-{{ $offer['discount_pct'] }}%</span>
                            @endif
                        </div>
                        <h4 class="font-extrabold text-xs text-white truncate">{{ $p['name'] }}</h4>
                        <div class="text-[11px] text-purple-200 mt-1 flex items-center gap-2">
                            <span><i class="fa-solid fa-car text-blue-400"></i> {{ $p['vehicle_slots'] ?? 0 }} wagens</span>
                            <span>•</span>
                            <span><i class="fa-solid fa-boxes-stacked text-amber-400"></i> +{{ $p['storage_capacity'] ?? 0 }} opslag</span>
                        </div>
                    </div>

                    <div class="mt-3 pt-2 border-t border-slate-800 flex items-center justify-between">
                        <span class="text-xs font-black font-mono text-emerald-400">€ {{ number_format($dynPrice, 0, ',', '.') }}</span>
                        @if($alreadyOwned)
                            <span class="text-[10px] font-black text-emerald-400">Bezit ✓</span>
                        @elseif($isOfficer)
                            <form action="{{ route('gangs.panden.buy', $p['key']) }}" method="POST">
                                @csrf
                                <input type="hidden" name="token" value="{{ $offer['token'] }}">
                                <input type="hidden" name="dynamic_price" value="{{ $dynPrice }}">
                                <button type="submit" 
                                        @if(!$canAffordRot) disabled @endif
                                        onclick="return confirm('Koop {{ $p['name'] }} voor €{{ number_format($dynPrice, 0, ',', '.') }} uit de bendekas?')"
                                        class="px-2.5 py-1 rounded-lg {{ $canAffordRot ? 'bg-purple-600 hover:bg-purple-500 text-white cursor-pointer' : 'bg-slate-800 text-slate-500 cursor-not-allowed' }} text-[10px] font-black uppercase transition">
                                    Kopen
                                </button>
                            </form>
                        @else
                            <span class="text-[10px] text-slate-500 italic">Beheerder</span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Category Filter Tabs -->
    <div class="flex items-center gap-2 p-1.5 bg-slate-100 dark:bg-slate-800/80 rounded-2xl overflow-x-auto text-xs font-bold">
        <button type="button" @click="activeCategory = 'all'"
                :class="activeCategory === 'all' ? 'bg-white dark:bg-slate-900 text-purple-600 dark:text-purple-400 shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'"
                class="px-4 py-2.5 rounded-xl transition cursor-pointer flex items-center gap-2 shrink-0">
            <i class="fa-solid fa-border-all"></i>
            <span>Alle Panden ({{ count($catalog) }})</span>
        </button>
        <button type="button" @click="activeCategory = 'storage'"
                :class="activeCategory === 'storage' ? 'bg-white dark:bg-slate-900 text-amber-600 dark:text-amber-400 shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'"
                class="px-4 py-2.5 rounded-xl transition cursor-pointer flex items-center gap-2 shrink-0">
            <i class="fa-solid fa-warehouse text-amber-500"></i>
            <span>Garages & Opslag (Loodsen / Bunker)</span>
        </button>
        <button type="button" @click="activeCategory = 'production'"
                :class="activeCategory === 'production' ? 'bg-white dark:bg-slate-900 text-emerald-600 dark:text-emerald-400 shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'"
                class="px-4 py-2.5 rounded-xl transition cursor-pointer flex items-center gap-2 shrink-0">
            <i class="fa-solid fa-industry text-emerald-500"></i>
            <span>Productie & Fabrieken</span>
        </button>
        <button type="button" @click="activeCategory = 'luxury'"
                :class="activeCategory === 'luxury' ? 'bg-white dark:bg-slate-900 text-purple-600 dark:text-purple-400 shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'"
                class="px-4 py-2.5 rounded-xl transition cursor-pointer flex items-center gap-2 shrink-0">
            <i class="fa-solid fa-landmark-dome text-purple-500"></i>
            <span>Luxe Panden & Villa's (HQ)</span>
        </button>
        <button type="button" @click="activeCategory = 'npcs'"
                :class="activeCategory === 'npcs' ? 'bg-white dark:bg-slate-900 text-cyan-600 dark:text-cyan-400 shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'"
                class="px-4 py-2.5 rounded-xl transition cursor-pointer flex items-center gap-2 shrink-0">
            <i class="fa-solid fa-users-gear text-cyan-500"></i>
            <span>100+ NPC Rekruten ({{ $hiredNpcs->count() }})</span>
        </button>
    </div>

    <!-- ========================================== -->
    <!-- PROPERTIES LISTING GRID                    -->
    <!-- ========================================== -->
    <div x-show="activeCategory !== 'npcs'" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @foreach($catalog as $key => $prop)
        @php
            $owned = $ownedProperties->get($key);
            $isOwned = $owned !== null;
            $canAfford = (int) $gang->treasury >= $prop['price'];
        @endphp
        <div x-show="activeCategory === 'all' || activeCategory === '{{ $prop['category'] }}'"
             class="bg-white dark:bg-slate-900 border {{ $isOwned ? 'border-emerald-400/80 dark:border-emerald-500/80 ring-2 ring-emerald-400/20' : 'border-slate-200 dark:border-slate-800' }} rounded-2xl p-6 shadow-xs flex flex-col justify-between gap-5 relative overflow-hidden transition hover:shadow-md">

            @if($isOwned)
                <div class="absolute -right-12 top-6 bg-emerald-500 text-slate-950 font-black text-[10px] uppercase tracking-wider py-1 px-12 rotate-45 shadow-xs text-center pointer-events-none">
                    In Bezit ✓
                </div>
            @endif

            <div class="space-y-4">
                <!-- Header Icon & Category Badge -->
                <div class="flex items-start justify-between gap-3">
                    <div class="w-14 h-14 rounded-2xl {{ $isOwned ? 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400' : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200' }} border border-slate-200 dark:border-slate-700 flex items-center justify-center text-2xl shrink-0">
                        <i class="fa-solid {{ $prop['icon'] }}"></i>
                    </div>
                    <div class="text-right">
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                            {{ $prop['category_label'] }}
                        </span>
                        <div class="text-base font-black font-mono text-emerald-600 dark:text-emerald-400 mt-1">
                            € {{ number_format($prop['price'], 0, ',', '.') }}
                        </div>
                    </div>
                </div>

                <!-- Info Block -->
                <div>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-purple-600 dark:text-purple-400 block">{{ $prop['tagline'] }}</span>
                    <h3 class="text-base font-black text-slate-900 dark:text-white tracking-tight mt-0.5">{{ $prop['name'] }}</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-2 leading-relaxed">
                        {{ $prop['description'] }}
                    </p>
                </div>

                <!-- Feature Pill -->
                <div class="bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-800 rounded-xl p-3 flex items-center gap-2.5 text-xs font-bold text-slate-700 dark:text-slate-300">
                    <i class="fa-solid fa-circle-check text-emerald-500 shrink-0"></i>
                    <span>{{ $prop['perks_summary'] }}</span>
                </div>

                <!-- Production Status if Owned & Production Type -->
                @if($isOwned && $prop['category'] === 'production')
                <div class="border-t border-slate-100 dark:border-slate-800 pt-3 space-y-2">
                    <div class="flex items-center justify-between text-xs">
                        <span class="font-bold text-slate-500">Productie Status:</span>
                        @if($owned->isProductionReady())
                            <span class="px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300 font-extrabold text-[10px]">
                                Klaar om te claimen!
                            </span>
                        @elseif($owned->isProducing())
                            <span class="px-2 py-0.5 rounded-full bg-amber-100 text-amber-700 dark:bg-amber-950 dark:text-amber-300 font-extrabold text-[10px]">
                                Bezig ({{ $owned->remaining_production_minutes }} min)
                            </span>
                        @else
                            <span class="text-slate-400 text-xs italic">Inactief</span>
                        @endif
                    </div>

                    @if($owned->isProductionReady() && $isOfficer)
                    <form method="POST" action="{{ route('gangs.panden.claim-production', $owned) }}">
                        @csrf
                        <button type="submit" class="w-full py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-black text-xs uppercase tracking-wider transition shadow-xs flex items-center justify-center gap-2 cursor-pointer">
                            <i class="fa-solid fa-sack-dollar"></i>
                            <span>Claim Opbrengst (+€{{ number_format($owned->effective_reward_cash, 0, ',', '.') }})</span>
                        </button>
                    </form>
                    @elseif(! $owned->isProducing() && $isOfficer)
                    <form method="POST" action="{{ route('gangs.panden.start-production', $owned) }}">
                        @csrf
                        <button type="submit" class="w-full py-2.5 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-black text-xs uppercase tracking-wider transition shadow-xs flex items-center justify-center gap-2 cursor-pointer">
                            <i class="fa-solid fa-play"></i>
                            <span>Start Productiecyclus ({{ $owned->production_duration_minutes }} min)</span>
                        </button>
                    </form>
                    @endif
                </div>
                @endif
            </div>

            <!-- Action Button: Buy or Owned Badge -->
            <div class="pt-2">
                @if($isOwned)
                    <div class="w-full py-3 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 font-black text-xs text-center border border-emerald-200 dark:border-emerald-800/60 flex items-center justify-center gap-2">
                        <i class="fa-solid fa-circle-check"></i>
                        <span>Eigendom van [{{ $gang->tag }}]</span>
                    </div>
                @elseif($isOfficer)
                    <form method="POST" action="{{ route('gangs.panden.buy', $key) }}">
                        @csrf
                        <button type="submit"
                                @if(! $canAfford) disabled @endif
                                onclick="return confirm('Weet je zeker dat je {{ $prop['name'] }} wilt kopen voor €{{ number_format($prop['price'], 0, ',', '.') }} uit de bendekas?')"
                                class="w-full py-3 px-4 rounded-xl {{ $canAfford ? 'bg-purple-600 hover:bg-purple-700 text-white cursor-pointer shadow-xs active:scale-95' : 'bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-slate-500 cursor-not-allowed font-bold' }} font-black text-xs uppercase tracking-wider transition flex items-center justify-center gap-2">
                            <i class="fa-solid fa-building-circle-check"></i>
                            @if($canAfford)
                                <span>Kopen voor € {{ number_format($prop['price'], 0, ',', '.') }}</span>
                            @else
                                <span>Te weinig in kas (€ {{ number_format($gang->treasury, 0, ',', '.') }})</span>
                            @endif
                        </button>
                    </form>
                @else
                    <div class="w-full py-3 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-400 font-bold text-xs text-center">
                        Alleen bendebeheerders kunnen kopen
                    </div>
                @endif
            </div>

        </div>
        @endforeach
    </div>

    <!-- ========================================== -->
    <!-- 100+ NPC RECRUITS SECTION WITH 30-MIN ROTATION -->
    <!-- ========================================== -->
    <div x-show="activeCategory === 'npcs' || activeCategory === 'all'" class="space-y-4 pt-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-200 dark:border-slate-800 pb-3">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-purple-100 dark:bg-purple-950 text-purple-700 dark:text-purple-300">
                        100+ Karakters Pool
                    </span>
                    <span class="text-xs text-slate-500">Roterende Werving</span>
                </div>
                <h2 class="text-base font-black text-slate-900 dark:text-white uppercase tracking-wide flex items-center gap-2">
                    <i class="fa-solid fa-users-gear text-purple-600"></i>
                    <span>NPC Bendeleden & Specialisten (Rotatie Showroom)</span>
                </h2>
                <p class="text-xs text-slate-500 dark:text-slate-400">
                    Huur ervaren criminelen, chauffeurs, tuners, chemici en witwassers in om je bende bonussen te geven!
                </p>
            </div>
            <div class="flex items-center gap-3">
                <div class="bg-slate-100 dark:bg-slate-800 px-3 py-1.5 rounded-xl flex items-center gap-2 text-xs font-mono font-bold">
                    <i class="fa-solid fa-stopwatch text-purple-500"></i>
                    <span class="text-slate-500">Ververst in:</span>
                    <span class="text-purple-600 dark:text-purple-400 font-black" x-text="formatTime(npcCd)"></span>
                </div>
                <span class="px-3 py-1.5 rounded-xl bg-purple-50 dark:bg-purple-950/50 border border-purple-200 dark:border-purple-800 text-xs font-black text-purple-700 dark:text-purple-300">
                    Capaciteit: {{ $hiredNpcs->count() }} / {{ $totalNpcCap }}
                </span>
            </div>
        </div>

        @if($totalNpcCap <= 0)
        <!-- Warning when no luxury villa or penthouse is owned -->
        <div class="p-4 rounded-2xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800/60 flex items-start gap-3">
            <i class="fa-solid fa-triangle-exclamation text-amber-500 text-lg mt-0.5"></i>
            <div class="text-xs text-amber-900 dark:text-amber-200 space-y-1">
                <span class="font-black block">Geen woonruimte voor NPC bendeleden!</span>
                <p class="text-amber-800 dark:text-amber-300">
                    Om NPC bendeleden aan te nemen moet je bende eerst een <strong>Luxe Sky-Penthouse</strong>, <strong>Havenjachthuis</strong>, <strong>Grachtenpand</strong> of <strong>Gang Villa</strong> bezitten.
                </p>
            </div>
        </div>
        @endif

        <!-- Rotating NPC Showroom Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            @foreach($rotatingNpcs['listings'] ?? [] as $item)
            @php
                $npcDef = $item['npc'];
                $npcKey = $npcDef['key'];
                $hiredInstances = $hiredNpcs->where('npc_key', $npcKey);
                $isHired = $hiredInstances->count() > 0;
                $canAffordNpc = (int) $gang->treasury >= $npcDef['cost'];
                $hasFreeSlot = $availableNpcSlots > 0;
            @endphp
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-xs flex flex-col justify-between gap-5 relative overflow-hidden transition hover:shadow-md">
                <div class="space-y-4">
                    <div class="flex items-start justify-between gap-3">
                        <div class="w-14 h-14 rounded-2xl bg-purple-50 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400 border border-purple-200 dark:border-purple-800 flex items-center justify-center text-2xl shrink-0">
                            <i class="fa-solid {{ $npcDef['icon'] }}"></i>
                        </div>
                        <div class="text-right">
                            <span class="text-base font-black font-mono text-emerald-600 dark:text-emerald-400 block">
                                € {{ number_format($npcDef['cost'], 0, ',', '.') }}
                            </span>
                            <span class="text-[10px] font-bold text-slate-400 uppercase">Eenmalige wervingskosten</span>
                        </div>
                    </div>

                    <div>
                        <span class="text-[11px] font-bold uppercase tracking-wider text-purple-600 dark:text-purple-400 block">{{ $npcDef['role_title'] }}</span>
                        <h3 class="text-base font-black text-slate-900 dark:text-white tracking-tight mt-0.5">{{ $npcDef['name'] }}</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-2 leading-relaxed">
                            {{ $npcDef['description'] }}
                        </p>
                    </div>

                    <!-- Benefit pill -->
                    <div class="bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/60 rounded-xl p-3 flex items-center gap-2.5 text-xs font-black text-emerald-800 dark:text-emerald-300">
                        <i class="fa-solid fa-sparkles text-emerald-500"></i>
                        <span>{{ $npcDef['benefit_label'] }}</span>
                    </div>

                    @if($isHired)
                    <div class="text-xs font-bold text-slate-600 dark:text-slate-300 pt-1">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-slate-100 dark:bg-slate-800">
                            <i class="fa-solid fa-check text-emerald-500"></i> {{ $hiredInstances->count() }}x in dienst van bende
                        </span>
                    </div>
                    @endif
                </div>

                <!-- Hire Button -->
                <div class="pt-2">
                    @if($isOfficer)
                        <form method="POST" action="{{ route('gangs.panden.hire-npc', $npcKey) }}">
                            @csrf
                            <button type="submit"
                                    @if(! $hasFreeSlot || ! $canAffordNpc) disabled @endif
                                    onclick="return confirm('Weet je zeker dat je {{ $npcDef['name'] }} wilt aannemen voor €{{ number_format($npcDef['cost'], 0, ',', '.') }} uit de bendekas?')"
                                    class="w-full py-3 px-4 rounded-xl {{ $hasFreeSlot && $canAffordNpc ? 'bg-purple-600 hover:bg-purple-700 text-white cursor-pointer shadow-xs active:scale-95' : 'bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-slate-500 cursor-not-allowed font-bold' }} font-black text-xs uppercase tracking-wider transition flex items-center justify-center gap-2">
                                <i class="fa-solid fa-user-plus"></i>
                                @if(! $hasFreeSlot)
                                    <span>Geen Villa Ruimte</span>
                                @elseif(! $canAffordNpc)
                                    <span>Te weinig in kas</span>
                                @else
                                    <span>Aannemen (€ {{ number_format($npcDef['cost'], 0, ',', '.') }})</span>
                                @endif
                            </button>
                        </form>
                    @else
                        <div class="w-full py-3 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-400 font-bold text-xs text-center">
                            Alleen bendebeheerders kunnen werven
                        </div>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>

</div>
@endsection
