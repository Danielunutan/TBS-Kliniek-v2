@extends('layouts.app')

@section('title', 'Smokkel Garage & Hangar - Vluchtvoertuigen')
@section('meta_description', 'Koop en beheer je vluchtwagens, speedboten en helikopters in de Smokkel Garage. Verhoog je snelheid en conditie voor Grote Heists.')

@section('content')
<div class="space-y-6" x-data="{ tab: 'showroom' }">

    <!-- HERO HEADER -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-6 shadow-xs relative overflow-hidden">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 relative z-10">
            <div class="flex items-center gap-3.5">
                <div class="w-12 h-12 rounded-2xl bg-purple-600 text-white flex items-center justify-center text-xl shadow-md shrink-0">
                    <i class="fa-solid fa-warehouse"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h2 class="text-lg font-black text-slate-900 dark:text-white tracking-tight">Smokkel Garage & Hangar</h2>
                        <span class="px-2 py-0.5 rounded-full bg-purple-100 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 font-black text-[10px] uppercase tracking-wider">Nieuw</span>
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                        Gerund door corrupte monteurs bij de expeditiepoort. Schaf vluchtwagens, boten en helikopters aan voor Grote Heists.
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2 shrink-0">
                <a href="{{ route('marketplace.index', ['type' => 'vehicle']) }}" 
                   class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 border border-slate-200/80 dark:border-slate-700 font-bold text-xs uppercase tracking-wider transition-all duration-150 shadow-xs flex items-center gap-2 active:scale-95 cursor-pointer">
                    <i class="fa-solid fa-shop text-xs text-purple-500"></i>
                    <span>Marktplaats</span>
                </a>
                <a href="{{ route('heists.index') }}" 
                   class="px-4 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-700 dark:bg-purple-600 dark:hover:bg-purple-500 text-white font-black text-xs uppercase tracking-wider transition-all duration-150 shadow-xs flex items-center gap-2 active:scale-95 cursor-pointer">
                    <i class="fa-solid fa-mask text-xs"></i>
                    <span>Naar Grote Heists</span>
                </a>
            </div>
        </div>

        <!-- QUICK TAB TOGGLE -->
        <div class="flex items-center gap-2 pt-6 border-t border-slate-100 dark:border-slate-800 mt-6 overflow-x-auto no-scrollbar">
            <button type="button" @click="tab = 'showroom'" 
                    :class="tab === 'showroom' ? 'bg-purple-600 text-white shadow-xs font-black' : 'bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold border border-slate-200/80 dark:border-slate-700'"
                    class="px-4 py-2 rounded-xl text-xs transition cursor-pointer flex items-center gap-2 shrink-0">
                <i class="fa-solid fa-arrows-rotate"></i>
                <span>Roterende Showroom ({{ count($rotatingListings) }})</span>
            </button>
            <button type="button" @click="tab = 'stars'" 
                    :class="tab === 'stars' ? 'btn-star-gold text-slate-950 shadow-xs font-black' : 'bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold border border-slate-200/80 dark:border-slate-700'"
                    class="px-4 py-2 rounded-xl text-xs transition cursor-pointer flex items-center gap-2 shrink-0">
                <i class="fa-solid fa-star" :class="tab === 'stars' ? 'text-slate-950' : 'text-amber-500 dark:text-amber-400'"></i>
                <span>Sterren Showroom ⭐ ({{ count($starVehicles) }})</span>
            </button>
            <button type="button" @click="tab = 'garage'" 
                    :class="tab === 'garage' ? 'bg-purple-600 text-white shadow-xs font-black' : 'bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold border border-slate-200/80 dark:border-slate-700'"
                    class="px-4 py-2 rounded-xl text-xs transition cursor-pointer flex items-center gap-2 shrink-0">
                <i class="fa-solid fa-key"></i>
                <span>Mijn Wagenpark ({{ count($ownedVehicles) }}/{{ $character->getGarageCapacity() }})</span>
            </button>
            @if(isset($gangStoredVehicles) && $gangStoredVehicles->isNotEmpty())
            <a href="{{ route('gangs.garage') }}" 
               class="px-4 py-2 rounded-xl text-xs transition cursor-pointer flex items-center gap-2 shrink-0 bg-purple-50 dark:bg-purple-950/60 border border-purple-200/80 dark:border-purple-800/70 text-purple-700 dark:text-purple-300 hover:bg-purple-100 dark:hover:bg-purple-900/60 font-bold"
               title="Voertuigen die je hebt gestald in de Bende Garage">
                <i class="fa-solid fa-warehouse text-purple-600 dark:text-purple-400"></i>
                <span>In Bende Garage ({{ $gangStoredVehicles->count() }})</span>
            </a>
            @endif
        </div>
    </div>

    <!-- ACTIVE GETAWAY VEHICLE STATUS (IF OWNED) -->
    @if($activeVehicle)
    <div class="rounded-2xl p-6 shadow-xs border border-purple-200/90 dark:border-purple-800/70 relative overflow-hidden bg-white dark:bg-slate-900 text-slate-900 dark:text-white">
        <div class="absolute -right-8 -bottom-8 opacity-5 dark:opacity-10 text-9xl text-slate-900 dark:text-white pointer-events-none">
            <i class="fa-solid {{ $activeVehicle->vehicle?->icon ?? 'fa-car' }}"></i>
        </div>

        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 relative z-10">
            <div class="flex items-start gap-4">
                <div class="w-14 h-14 rounded-2xl bg-purple-50 dark:bg-purple-950/70 border border-purple-200 dark:border-purple-800 text-purple-600 dark:text-purple-400 flex items-center justify-center text-2xl shrink-0 shadow-2xs">
                    <i class="fa-solid {{ $activeVehicle->vehicle?->icon ?? 'fa-car' }}"></i>
                </div>
                <div class="space-y-1">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="px-2 py-0.5 rounded-full bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-300 text-[10px] font-black uppercase tracking-wider flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Actief Vluchtvoertuig
                        </span>
                        <span class="text-xs text-slate-500 dark:text-slate-400 font-bold">{{ $activeVehicle->vehicle?->type_label ?? 'Voertuig' }} &bull; {{ $activeVehicle->vehicle?->brand ?? 'Fleet' }}</span>
                        @if($activeVehicle->tuned_speed && $activeVehicle->tuned_speed !== ($activeVehicle->vehicle?->speed_pct ?? 0))
                            <span class="px-2 py-0.5 rounded-full bg-amber-50 dark:bg-amber-950/60 border border-amber-200 dark:border-amber-800 text-amber-700 dark:text-amber-300 text-[10px] font-black uppercase tracking-wider">
                                ⚡ Tuned Engine ({{ $activeVehicle->tuned_speed }}%)
                            </span>
                        @endif
                    </div>
                    <h3 class="text-lg font-black text-slate-900 dark:text-white tracking-tight">{{ $activeVehicle->vehicle?->name ?? 'Voertuig' }}</h3>
                    <p class="text-xs text-slate-600 dark:text-slate-400 max-w-xl">{{ $activeVehicle->vehicle?->description ?? '' }}</p>
                </div>
            </div>

            <!-- GAUGE METRICS & REPAIR BUTTON -->
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-4 shrink-0">
                <!-- Snelheid Meter -->
                <div class="bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70 rounded-xl p-3.5 min-w-[140px] text-center space-y-1 shadow-2xs">
                    <div class="text-[10px] uppercase font-bold tracking-wider text-slate-500 dark:text-slate-400 flex items-center justify-center gap-1">
                        <i class="fa-solid fa-gauge-high text-amber-500"></i> Effectieve Snelheid
                    </div>
                    <div class="text-xl font-black text-amber-600 dark:text-amber-400 font-mono">{{ $activeVehicle->effective_speed }}%</div>
                    <div class="w-full bg-slate-200 dark:bg-slate-700 rounded-full h-1.5 overflow-hidden">
                        <div class="bg-amber-500 h-full rounded-full" style="width: {{ $activeVehicle->effective_speed }}%"></div>
                    </div>
                </div>

                <!-- Conditie Meter -->
                <div class="bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70 rounded-xl p-3.5 min-w-[140px] text-center space-y-1 shadow-2xs">
                    <div class="text-[10px] uppercase font-bold tracking-wider text-slate-500 dark:text-slate-400 flex items-center justify-center gap-1">
                        <i class="fa-solid fa-wrench text-emerald-500"></i> Conditie
                    </div>
                    <div class="text-xl font-black {{ $activeVehicle->current_condition < 50 ? 'text-rose-600 dark:text-rose-400' : 'text-emerald-600 dark:text-emerald-400' }} font-mono">
                        {{ $activeVehicle->current_condition }}%
                    </div>
                    <div class="w-full bg-slate-200 dark:bg-slate-700 rounded-full h-1.5 overflow-hidden">
                        <div class="{{ $activeVehicle->current_condition < 50 ? 'bg-rose-500' : 'bg-emerald-500' }} h-full rounded-full" style="width: {{ $activeVehicle->current_condition }}%"></div>
                    </div>
                </div>

                <!-- Reparatie Actie -->
                @if($activeVehicle->current_condition < 100)
                <form action="{{ route('garage.repair', $activeVehicle->id) }}" method="POST" class="shrink-0 flex items-center">
                    @csrf
                    <button type="submit" 
                            @if($character->isImprisoned() || $character->isComatose()) disabled @endif
                            class="w-full sm:w-auto px-4 py-3 rounded-xl {{ ($character->isImprisoned() || $character->isComatose()) ? 'bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-slate-500 cursor-not-allowed' : 'bg-emerald-600 hover:bg-emerald-700 dark:bg-emerald-600 dark:hover:bg-emerald-500 active:scale-95 text-white cursor-pointer' }} font-black text-xs uppercase tracking-wider transition-all duration-150 shadow-xs flex items-center justify-center gap-1.5">
                        <i class="fa-solid fa-screwdriver-wrench"></i>
                        <span>Reviseren (€{{ number_format($activeVehicle->repair_cost, 0, ',', '.') }})</span>
                    </button>
                </form>
                @endif
            </div>
        </div>
    </div>
    @endif

    <!-- TAB 1: 30-MINUTEN ROTERENDE SHOWROOM -->
    <div x-show="tab === 'showroom'" class="space-y-4">

        <!-- ROTATIE HEADER BANNER MET LIVE COUNTDOWN -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-5 shadow-xs flex flex-col sm:flex-row items-center justify-between gap-4 text-slate-900 dark:text-white"
             x-data="{
                 secondsLeft: {{ $secondsUntilRotation }},
                 formatTime(sec) {
                     const m = Math.floor(sec / 60).toString().padStart(2, '0');
                     const s = (sec % 60).toString().padStart(2, '0');
                     return `${m}:${s}`;
                 },
                 init() {
                     const timer = setInterval(() => {
                         if (this.secondsLeft > 0) {
                             this.secondsLeft--;
                         } else {
                             clearInterval(timer);
                             window.location.reload();
                         }
                     }, 1000);
                 }
             }">
            <div class="flex items-center gap-3.5">
                <div class="w-12 h-12 rounded-xl bg-purple-50 dark:bg-purple-950/70 border border-purple-200 dark:border-purple-800 text-purple-600 dark:text-purple-400 flex items-center justify-center text-xl shrink-0 shadow-2xs">
                    <i class="fa-solid fa-arrows-rotate animate-spin" style="animation-duration: 9s;"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="text-sm font-black text-slate-900 dark:text-white uppercase tracking-wider">30-Minuten Smokkelmarkt</span>
                        <span class="px-2 py-0.5 rounded-full bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-300 text-[10px] font-black uppercase tracking-wider">
                            8 Unieke Deals Live
                        </span>
                    </div>
                    <p class="text-xs text-slate-600 dark:text-slate-400 mt-0.5 leading-relaxed">
                        Elk half uur levert de smokkelaar een nieuwe lading van 8 voertuigen af met wisselende condities, project-koopjes en zeldzame getunede snelheden!
                    </p>
                </div>
            </div>

            <!-- Timer Badge -->
            <div class="flex items-center gap-3 shrink-0 bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70 rounded-xl px-4 py-2.5 shadow-2xs">
                <i class="fa-regular fa-clock text-purple-600 dark:text-purple-400 text-sm"></i>
                <div>
                    <span class="text-[9px] uppercase tracking-wider text-slate-400 dark:text-slate-400 font-extrabold block">Volgende Lading Binnen</span>
                    <span class="font-mono text-base font-black text-slate-900 dark:text-white" x-text="formatTime(secondsLeft)">--:--</span>
                </div>
                <button type="button" onclick="window.location.reload()" title="Aanbod herladen" class="ml-2 w-8 h-8 rounded-lg bg-slate-200 hover:bg-slate-300 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 flex items-center justify-center text-xs transition cursor-pointer">
                    <i class="fa-solid fa-rotate-right"></i>
                </button>
            </div>
        </div>

        <!-- 8 DYNAMISCHE ROTERENDE AANBIEDINGEN -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach($rotatingListings as $item)
            @php
                $veh = $item['vehicle'];
                $cond = $item['condition'];
                $tunedSpeed = $item['tuned_speed'];
                $speedVar = $item['speed_variance'];
                $priceCash = $item['price_cash'];
                $priceStars = $item['price_stars'];
                $token = $item['token'];
                $isOwned = $ownedVehicles->firstWhere('vehicle_id', $veh->id);
                $isDiscounted = $priceCash < $item['original_price_cash'];
                $discountPct = $isDiscounted ? round((1 - ($priceCash / $item['original_price_cash'])) * 100) : 0;
            @endphp
            <div class="bg-white dark:bg-slate-900 border {{ $isOwned ? 'border-purple-300 dark:border-purple-600 ring-2 ring-purple-400/20' : 'border-slate-200/90 dark:border-slate-800' }} hover:border-purple-300 dark:hover:border-purple-700/80 rounded-2xl p-5 shadow-xs flex flex-col justify-between gap-4 transition group">
                <div class="space-y-3">
                    <div class="flex items-start justify-between gap-2">
                        <div class="w-12 h-12 rounded-xl bg-purple-50 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 flex items-center justify-center text-xl shrink-0 group-hover:scale-110 transition-transform shadow-2xs">
                            <i class="fa-solid {{ $veh->icon }}"></i>
                        </div>
                        <div class="text-right">
                            <span class="px-2 py-0.5 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-[10px] font-black uppercase tracking-wider">
                                Lvl {{ $veh->min_level }}+
                            </span>
                            <span class="block text-[11px] text-slate-400 dark:text-slate-500 font-bold mt-0.5">{{ $veh->type_label }}</span>
                        </div>
                    </div>

                    <div>
                        <div class="flex items-center gap-1.5 flex-wrap mb-1">
                            <!-- Conditie Badge -->
                            @if($cond >= 90)
                                <span class="px-2 py-0.5 rounded-md bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/60 text-[10px] font-black uppercase">
                                    Topconditie {{ $cond }}%
                                </span>
                            @elseif($cond >= 70)
                                <span class="px-2 py-0.5 rounded-md bg-blue-100 dark:bg-blue-950/60 text-blue-800 dark:text-blue-300 border border-blue-200 dark:border-blue-800/60 text-[10px] font-black uppercase">
                                    Staat: {{ $cond }}%
                                </span>
                            @else
                                <span class="px-2 py-0.5 rounded-md bg-amber-100 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-800/60 text-[10px] font-black uppercase">
                                    Koopje ({{ $cond }}% Conditie)
                                </span>
                            @endif

                            <!-- Tuning Badge -->
                            @if($speedVar > 0)
                                <span class="px-2 py-0.5 rounded-md bg-purple-100 dark:bg-purple-950/60 text-purple-800 dark:text-purple-300 border border-purple-200 dark:border-purple-800/60 text-[10px] font-black uppercase">
                                    ⚡ +{{ $speedVar }}% Getuned
                                </span>
                            @endif
                        </div>

                        <h4 class="text-sm font-black text-slate-900 dark:text-white">{{ $veh->name }}</h4>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 line-clamp-2 mt-0.5 leading-relaxed">{{ $veh->description }}</p>
                    </div>

                    <!-- Specs: Snelheid & Conditie Meters -->
                    <div class="space-y-2 pt-2 border-t border-slate-100 dark:border-slate-800">
                        <div class="space-y-1">
                            <div class="flex items-center justify-between text-[11px] font-bold">
                                <span class="text-slate-500 dark:text-slate-400 flex items-center gap-1">
                                    <i class="fa-solid fa-gauge text-amber-500 text-[10px]"></i> Snelheid (Topsnelheid)
                                </span>
                                <span class="text-slate-900 dark:text-slate-100 font-mono font-black">
                                    {{ $tunedSpeed }}%
                                    @if($speedVar > 0)
                                        <span class="text-emerald-500 text-[10px]">(+{{ $speedVar }}%)</span>
                                    @elseif($speedVar < 0)
                                        <span class="text-rose-500 text-[10px]">({{ $speedVar }}%)</span>
                                    @endif
                                </span>
                            </div>
                            <div class="w-full bg-slate-100 dark:bg-slate-800 rounded-full h-1.5 overflow-hidden">
                                <div class="bg-amber-500 h-full rounded-full" style="width: {{ $tunedSpeed }}%"></div>
                            </div>
                        </div>

                        <div class="space-y-1">
                            <div class="flex items-center justify-between text-[11px] font-bold">
                                <span class="text-slate-500 dark:text-slate-400 flex items-center gap-1">
                                    <i class="fa-solid fa-wrench text-emerald-500 text-[10px]"></i> Aanwezige Conditie
                                </span>
                                <span class="text-slate-900 dark:text-slate-100 font-mono font-black">{{ $cond }}%</span>
                            </div>
                            <div class="w-full bg-slate-100 dark:bg-slate-800 rounded-full h-1.5 overflow-hidden">
                                <div class="{{ $cond < 70 ? 'bg-amber-500' : 'bg-emerald-500' }} h-full rounded-full" style="width: {{ $cond }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Prijs & Aankoop Knop -->
                <div class="pt-3 border-t border-slate-100 dark:border-slate-800 space-y-2">
                    <div class="flex items-center justify-between flex-wrap gap-1">
                        <span class="text-xs text-slate-400 dark:text-slate-500 font-medium">Actuele Prijs:</span>
                        <div class="text-right">
                            @if($isDiscounted)
                                <span class="text-[10px] text-slate-400 line-through mr-1">€{{ number_format($item['original_price_cash'], 0, ',', '.') }}</span>
                                <span class="text-xs font-black text-emerald-600 dark:text-emerald-400 font-mono">
                                    €{{ number_format($priceCash, 0, ',', '.') }}
                                </span>
                                <span class="text-[9px] font-extrabold text-emerald-700 dark:text-emerald-300 bg-emerald-100 dark:bg-emerald-950/70 px-1 py-0.5 rounded ml-1">-{{ $discountPct }}%</span>
                            @else
                                <span class="text-xs font-black text-emerald-600 dark:text-emerald-400 font-mono">
                                    €{{ number_format($priceCash, 0, ',', '.') }}
                                    @if($priceStars > 0)
                                        <span class="text-slate-400 font-normal">of</span> {{ $priceStars }} ⭐
                                    @endif
                                </span>
                            @endif
                        </div>
                    </div>

                    @if($isOwned)
                        <div class="w-full py-2.5 rounded-xl bg-purple-50 dark:bg-purple-950/40 text-purple-700 dark:text-purple-300 font-black text-xs text-center border border-purple-200 dark:border-purple-800/60">
                            <i class="fa-solid fa-check mr-1"></i> In Bezit
                        </div>
                    @else
                        @if($character->level < $veh->min_level)
                            <button type="button" disabled class="w-full py-2.5 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-slate-500 font-bold text-xs cursor-not-allowed text-center">
                                Vereist Level {{ $veh->min_level }}
                            </button>
                        @else
                            <form action="{{ route('garage.buy', $veh->id) }}" method="POST">
                                @csrf
                                <input type="hidden" name="listing_token" value="{{ $token }}">
                                <button type="submit" 
                                        @if($character->isImprisoned() || $character->isComatose()) disabled @endif
                                        class="w-full py-2.5 px-3 rounded-xl {{ ($character->isImprisoned() || $character->isComatose()) ? 'bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-slate-500 cursor-not-allowed' : 'bg-purple-600 hover:bg-purple-700 dark:bg-purple-600 dark:hover:bg-purple-500 active:scale-95 text-white cursor-pointer' }} font-black text-xs uppercase tracking-wider transition-all duration-150 shadow-xs flex items-center justify-center gap-1.5">
                                    <i class="fa-solid fa-cart-shopping text-xs"></i>
                                    <span>Koop Deze Aanbieding</span>
                                </button>
                            </form>
                        @endif
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- TAB: EXCLUSIEVE STERREN SHOWROOM -->
    <div x-show="tab === 'stars'" class="space-y-4">
        <!-- VIP PRESTIGE HEADER -->
        <div class="rounded-2xl p-6 text-slate-900 dark:text-white border border-amber-400/50 dark:border-amber-500/40 shadow-xs relative overflow-hidden bg-white dark:bg-slate-900">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 relative z-10">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <span class="px-2.5 py-0.5 rounded-full bg-amber-500 text-slate-950 font-black text-[10px] uppercase tracking-wider flex items-center gap-1 shadow-xs">
                            <i class="fa-solid fa-crown text-[10px]"></i> Prestige Showroom
                        </span>
                        <span class="text-xs text-amber-600 dark:text-amber-400 font-bold">Uitsluitend voor Sterren ⭐</span>
                    </div>
                    <h3 class="text-lg font-black tracking-tight text-slate-900 dark:text-white">Exclusieve Exotische Vluchtvoertuigen</h3>
                    <p class="text-xs text-slate-600 dark:text-slate-400 max-w-2xl">
                        Deze unieke high-tier voertuigen zijn niet met contant geld te koop. Ze bieden extreme prestaties, titanium bepantsering en ongeëvenaarde heist-bonussen.
                    </p>
                </div>
                <div class="bg-slate-50/90 dark:bg-slate-800/60 border border-amber-300 dark:border-amber-500/40 rounded-xl px-4 py-3 shrink-0 shadow-2xs text-center">
                    <div class="text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Jouw Sterrensaldo</div>
                    <div class="text-xl font-black text-amber-500 font-mono flex items-center justify-center gap-1.5 mt-0.5">
                        <i class="fa-solid fa-star text-base"></i>
                        <span>{{ number_format($character->stars ?? 0, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- STAR VEHICLES GRID -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @php
                $ownedVehicleIds = $ownedVehicles->pluck('vehicle_id')->toArray();
            @endphp
            @foreach($starVehicles as $sVeh)
            @php
                $isStarOwned = in_array($sVeh->id, $ownedVehicleIds);
                $hasEnoughStars = ($character->stars ?? 0) >= $sVeh->price_stars;
            @endphp
            <div class="bg-white dark:bg-slate-900 border border-amber-300/60 dark:border-amber-500/30 hover:border-amber-400 dark:hover:border-amber-400 rounded-2xl p-5 shadow-xs hover:shadow-md transition flex flex-col justify-between gap-4 relative overflow-hidden group">
                <div class="space-y-3">
                    <div class="flex items-start justify-between gap-2">
                        <div class="w-13 h-13 rounded-2xl bg-amber-500/10 border border-amber-400/30 text-amber-500 flex items-center justify-center text-2xl shrink-0 group-hover:scale-105 transition">
                            <i class="fa-solid {{ $sVeh->icon }}"></i>
                        </div>
                        <div class="flex flex-col items-end gap-1">
                            <span class="px-2.5 py-0.5 rounded-full bg-amber-100 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border border-amber-300/60 dark:border-amber-700/50 text-[10px] font-black uppercase tracking-wider flex items-center gap-1">
                                <i class="fa-solid fa-star text-[9px]"></i> {{ $sVeh->price_stars }} Sterren
                            </span>
                            <span class="text-[11px] text-slate-500 dark:text-slate-400 font-bold">{{ $sVeh->brand }}</span>
                        </div>
                    </div>

                    <div>
                        <h4 class="text-base font-black text-slate-900 dark:text-white tracking-tight">{{ $sVeh->name }}</h4>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 line-clamp-2">{{ $sVeh->description }}</p>
                    </div>

                    <!-- STATS -->
                    <div class="grid grid-cols-2 gap-2 pt-1">
                        <div class="bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70 rounded-xl p-2.5 space-y-1">
                            <div class="flex items-center justify-between text-[11px] font-bold">
                                <span class="text-slate-500 dark:text-slate-400 flex items-center gap-1">
                                    <i class="fa-solid fa-gauge-high text-amber-500 text-[10px]"></i> Snelheid
                                </span>
                                <span class="text-slate-900 dark:text-white font-mono font-black">{{ $sVeh->speed_pct }}%</span>
                            </div>
                            <div class="w-full bg-slate-200 dark:bg-slate-700 rounded-full h-1.5 overflow-hidden">
                                <div class="bg-amber-500 h-full rounded-full" style="width: {{ $sVeh->speed_pct }}%"></div>
                            </div>
                        </div>

                        <div class="bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70 rounded-xl p-2.5 space-y-1">
                            <div class="flex items-center justify-between text-[11px] font-bold">
                                <span class="text-slate-500 dark:text-slate-400 flex items-center gap-1">
                                    <i class="fa-solid fa-shield-halved text-emerald-500 text-[10px]"></i> Betrouwbaarheid
                                </span>
                                <span class="text-slate-900 dark:text-white font-mono font-black">{{ $sVeh->reliability_pct }}%</span>
                            </div>
                            <div class="w-full bg-slate-200 dark:bg-slate-700 rounded-full h-1.5 overflow-hidden">
                                <div class="bg-emerald-500 h-full rounded-full" style="width: {{ $sVeh->reliability_pct }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- AANKOOP / STATUS -->
                <div class="pt-3 border-t border-slate-100 dark:border-slate-800">
                    @if($isStarOwned)
                        <div class="w-full py-2.5 rounded-xl bg-purple-50 dark:bg-purple-950/40 text-purple-700 dark:text-purple-300 font-black text-xs text-center border border-purple-200 dark:border-purple-800/60 flex items-center justify-center gap-1.5">
                            <i class="fa-solid fa-circle-check"></i>
                            <span>In Je Wagenpark</span>
                        </div>
                    @else
                        <form action="{{ route('garage.buy', $sVeh->id) }}" method="POST">
                            @csrf
                            <input type="hidden" name="payment" value="stars">
                            <button type="submit" 
                                    @if(! $hasEnoughStars || $character->isImprisoned() || $character->isComatose()) disabled @endif
                                    class="w-full py-2.5 px-4 rounded-xl {{ ($hasEnoughStars && ! $character->isImprisoned() && ! $character->isComatose()) ? 'btn-star-gold text-slate-950 font-black cursor-pointer shadow-xs' : 'bg-slate-100 dark:bg-slate-800/80 text-slate-400 dark:text-slate-500 border border-slate-200/80 dark:border-slate-700/60 font-bold cursor-not-allowed' }} text-xs uppercase tracking-wider transition-all duration-150 active:scale-95 flex items-center justify-center gap-2">
                                <i class="fa-solid fa-star"></i>
                                @if($hasEnoughStars)
                                    <span>Koop voor {{ $sVeh->price_stars }} Sterren</span>
                                @else
                                    <span>Onvoldoende Sterren ({{ $character->stars ?? 0 }}/{{ $sVeh->price_stars }} ⭐)</span>
                                @endif
                            </button>
                        </form>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- TAB 2: MIJN WAGENPARK -->
    <div x-show="tab === 'garage'" class="space-y-4">
        @if($ownedVehicles->isEmpty())
            <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-12 text-center space-y-3">
                <div class="w-16 h-16 rounded-2xl bg-purple-50 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400 flex items-center justify-center text-2xl mx-auto shadow-xs">
                    <i class="fa-solid fa-warehouse"></i>
                </div>
                <h4 class="text-sm font-black text-slate-900 dark:text-white">Je garage is momenteel nog leeg</h4>
                <p class="text-xs text-slate-500 dark:text-slate-400 max-w-sm mx-auto">
                    Koop een vluchtwagen in de showroom om Grote Heists te kunnen starten en met volle snelheid de politie af te schudden!
                </p>
                <button type="button" @click="tab = 'showroom'" class="px-4 py-2 rounded-xl bg-purple-600 hover:bg-purple-700 dark:bg-purple-600 dark:hover:bg-purple-500 text-white font-black text-xs transition-all duration-150 active:scale-95 cursor-pointer shadow-xs">
                    Showroom Bekijken
                </button>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($ownedVehicles as $ov)
                <div class="bg-white dark:bg-slate-900 border {{ $ov->is_active ? 'border-emerald-400 dark:border-emerald-500 ring-2 ring-emerald-400/20' : ($ov->current_condition < 100 ? 'border-rose-300 dark:border-rose-800/80 bg-rose-50/10' : 'border-slate-200/90 dark:border-slate-800') }} rounded-2xl p-5 shadow-xs flex flex-col justify-between gap-4">
                    <div class="space-y-3">
                        <div class="flex items-start justify-between gap-2">
                            <div class="w-12 h-12 rounded-xl {{ $ov->current_condition < 100 ? 'bg-rose-50 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400' : 'bg-purple-50 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300' }} flex items-center justify-center text-xl shrink-0">
                                <i class="fa-solid {{ $ov->vehicle?->icon ?? 'fa-car' }}"></i>
                            </div>
                            <div class="flex items-center gap-1.5 flex-wrap justify-end">
                                @if($ov->current_condition < 100)
                                    <span class="px-2 py-0.5 rounded-full bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border border-rose-300 dark:border-rose-800 text-[10px] font-black uppercase tracking-wider flex items-center gap-1">
                                        <i class="fa-solid fa-wrench text-[9px]"></i> Toe aan Revisie
                                    </span>
                                @endif
                                @if($ov->is_active)
                                    <span class="px-2 py-0.5 rounded-full bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/60 text-[10px] font-black uppercase tracking-wider">
                                        Actief Vluchtvoertuig
                                    </span>
                                @else
                                    <form action="{{ route('garage.set-active', $ov->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" 
                                                @if($character->isImprisoned() || $character->isComatose()) disabled @endif
                                                class="px-2.5 py-1 rounded-xl {{ ($character->isImprisoned() || $character->isComatose()) ? 'bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-slate-500 cursor-not-allowed' : 'bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 cursor-pointer' }} border border-slate-200/80 dark:border-slate-700 font-bold text-[10px] transition-all duration-150 active:scale-95">
                                            Kies als Actief
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>

                        <div>
                            <h4 class="text-sm font-black text-slate-900 dark:text-white">{{ $ov->vehicle?->name ?? 'Voertuig' }}</h4>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ $ov->vehicle?->brand ?? 'Fleet' }} &bull; {{ $ov->total_heists_done }} heists voltooid</p>
                        </div>

                        <!-- Conditie & Snelheid -->
                        <div class="space-y-2 pt-2 border-t border-slate-100 dark:border-slate-800">
                            <div class="space-y-1">
                                <div class="flex items-center justify-between text-[11px] font-bold">
                                    <span class="text-slate-500 dark:text-slate-400">Conditie (Slijtage)</span>
                                    <span class="{{ $ov->current_condition < 50 ? 'text-rose-600 dark:text-rose-400' : 'text-emerald-700 dark:text-emerald-400' }} font-mono font-black">{{ $ov->current_condition }}%</span>
                                </div>
                                <div class="w-full bg-slate-100 dark:bg-slate-800 rounded-full h-2 overflow-hidden">
                                    <div class="{{ $ov->current_condition < 50 ? 'bg-rose-500' : 'bg-emerald-500' }} h-full rounded-full transition-all" style="width: {{ $ov->current_condition }}%"></div>
                                </div>
                            </div>

                            <div class="flex items-center justify-between text-xs pt-1 text-slate-600 dark:text-slate-400">
                                <span class="font-bold">Huidige Topsnelheid:</span>
                                <span class="font-mono font-black text-slate-900 dark:text-white">
                                    {{ $ov->effective_speed }}%
                                    @if($ov->tuned_speed && $ov->tuned_speed > ($ov->vehicle?->speed_pct ?? 0))
                                        <span class="text-emerald-500 font-extrabold text-[10px]">(+{{ $ov->tuned_speed - ($ov->vehicle?->speed_pct ?? 0) }}% Getuned ⚡)</span>
                                    @endif
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Reparatie Optie -->
                    <div class="pt-3 border-t border-slate-100 dark:border-slate-800 space-y-2">
                        @if($ov->current_condition < 100)
                            <form action="{{ route('garage.repair', $ov->id) }}" method="POST">
                                @csrf
                                <button type="submit" 
                                        @if($character->isImprisoned() || $character->isComatose()) disabled @endif
                                        class="w-full py-2.5 rounded-xl {{ ($character->isImprisoned() || $character->isComatose()) ? 'bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-slate-500 cursor-not-allowed' : 'bg-emerald-600 hover:bg-emerald-700 dark:bg-emerald-600 dark:hover:bg-emerald-500 text-white cursor-pointer active:scale-95' }} font-black text-xs uppercase tracking-wider transition-all duration-150 shadow-xs flex items-center justify-center gap-1.5">
                                    <i class="fa-solid fa-wrench text-xs"></i>
                                    <span>Reviseren (€{{ number_format($ov->repair_cost, 0, ',', '.') }})</span>
                                </button>
                            </form>
                        @else
                            <div class="text-center py-2 text-xs font-bold text-emerald-700 dark:text-emerald-400 flex items-center justify-center gap-1">
                                <i class="fa-solid fa-circle-check"></i> In Topconditie (100%)
                            </div>
                        @endif

                        @php
                            $ovBase = (int) ((($ov->vehicle?->price_cash ?? 0) > 0) ? $ov->vehicle->price_cash : (($ov->vehicle?->price_stars ?? 1) * 2000));
                            $ovFactor = max(0.1, ($ov->current_condition / 100));
                            $ovSellVal = max(500, (int) round($ovBase * 0.65 * $ovFactor));
                        @endphp
                        <form action="{{ route('garage.sell', $ov->id) }}" method="POST" onsubmit="return confirm('Weet je zeker dat je {{ $ov->vehicle?->name ?? 'dit voertuig' }} wilt inruilen voor €{{ number_format($ovSellVal, 0, ',', '.') }} op je bank?');">
                            @csrf
                            <button type="submit" 
                                    class="w-full py-2 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 border border-slate-200/80 dark:border-slate-700/80 font-bold text-xs uppercase tracking-wider transition-all duration-150 flex items-center justify-center gap-1.5 cursor-pointer">
                                <i class="fa-solid fa-sack-dollar text-amber-500"></i>
                                <span>Inruilen bij Dealer (€{{ number_format($ovSellVal, 0, ',', '.') }})</span>
                            </button>
                        </form>
                    </div>
                </div>
                @endforeach
            </div>
        @endif
    </div>

</div>
@endsection
