@extends('layouts.app')

@section('title', 'Depot & Centrale Opslag - TBS Kliniek')

@section('content')
@php
    $unlockedBorders = $unlockedBorders ?? [];
    $borderCatalog = $borderCatalog ?? [];
    $equippedBorder = $equippedBorder ?? null;
@endphp
<div class="space-y-6 pb-12" x-data="{ tab: @js($activeTab ?? 'garage'), sellModalItem: null, sellQty: 1 }">

    @include('game.control_points.banner', ['slug' => 'depot'])

    @if($character->isImprisoned())
    <div class="p-4 rounded-2xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200/80 dark:border-rose-800/70 flex items-center gap-3 text-rose-800 dark:text-rose-200 text-sm">
        <i class="fa-solid fa-lock text-base text-rose-600 shrink-0"></i>
        <span>Je zit in de isoleercel. Depot functies zoals wagenbeheer en consumpties zijn momenteel vergrendeld.</span>
    </div>
    @endif

    @if($character->isComatose())
    <div class="p-4 rounded-2xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200/80 dark:border-rose-800/70 flex items-center gap-3 text-rose-800 dark:text-rose-200 text-sm">
        <i class="fa-solid fa-bed-pulse text-base text-rose-600 shrink-0"></i>
        <span>Je ligt in het ziekenhuis/coma. Depot functies zoals wagenbeheer en consumpties zijn momenteel vergrendeld.</span>
    </div>
    @endif

    <!-- HERO HEADER -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-6 shadow-xs relative overflow-hidden">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 relative z-10">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-purple-600 text-white flex items-center justify-center text-2xl shadow-md shrink-0">
                    <i class="fa-solid fa-boxes-stacked"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h1 class="text-xl font-black text-slate-900 dark:text-white tracking-tight">Depot & Centrale Opslag</h1>
                        <span class="px-2.5 py-0.5 rounded-full bg-purple-50 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 border border-purple-200/80 dark:border-purple-800/70 font-black text-[10px] uppercase tracking-wider">
                            Voorraadbeheer
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 max-w-2xl leading-relaxed">
                        Centraal overzicht van al jouw bezittingen in de TBS Kliniek. Beheer je wagenpark, voorraad en zeldzame event-items.
                        Level omhoog of activeer VIP voor extra opslagcapaciteit!
                    </p>
                </div>
            </div>

            <!-- QUICK SHORTCUTS -->
            <div class="flex flex-wrap items-center gap-2 shrink-0 w-full sm:w-auto">
                <a href="{{ route('marketplace.index', ['type' => 'depot_item']) }}" 
                   class="px-4 py-2.5 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 hover:bg-indigo-100 dark:hover:bg-indigo-900/60 text-indigo-700 dark:text-indigo-300 border border-indigo-200/80 dark:border-indigo-800/70 font-black text-xs uppercase tracking-wider transition-all duration-150 active:scale-95 shadow-xs flex items-center gap-2 cursor-pointer">
                    <i class="fa-solid fa-shop text-xs text-indigo-500"></i>
                    <span>Marktplaats</span>
                </a>
                <a href="{{ route('shop.index') }}" 
                   class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 border border-slate-200/80 dark:border-slate-700 font-bold text-xs uppercase tracking-wider transition-all duration-150 active:scale-95 shadow-xs flex items-center gap-2 cursor-pointer">
                    <i class="fa-solid fa-store text-xs text-purple-500"></i>
                    <span>Kantine & Shop</span>
                </a>
                <a href="{{ route('garage.index') }}" 
                   class="px-4 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-700 dark:bg-purple-600 dark:hover:bg-purple-500 text-white font-black text-xs uppercase tracking-wider transition-all duration-150 active:scale-95 shadow-xs flex items-center gap-2 cursor-pointer">
                    <i class="fa-solid fa-warehouse text-xs"></i>
                    <span>Smokkel Garage</span>
                </a>
            </div>
        </div>

        <!-- CAPACITEIT METERS -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-6 pt-6 border-t border-slate-200/80 dark:border-slate-800">
            
            <!-- GARAGE CAPACITEIT -->
            <div class="p-4 rounded-xl bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70">
                <div class="flex items-center justify-between gap-2 mb-2">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-car text-purple-500 text-sm"></i>
                        <span class="text-xs font-bold text-slate-700 dark:text-slate-200">Garage Capaciteit</span>
                        @if($character->isVip())
                        <span class="px-1.5 py-0.5 rounded text-[9px] font-black uppercase tracking-wider bg-amber-50 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 border border-amber-200/80 dark:border-amber-800/70">
                            +3 VIP
                        </span>
                        @endif
                    </div>
                    <span class="text-xs font-black {{ $garagePercent >= 100 ? 'text-rose-500' : 'text-slate-900 dark:text-white' }}">
                        {{ $garageUsed }} / {{ $garageCapacity }} Plekken ({{ $garagePercent }}%)
                    </span>
                </div>
                <div class="w-full bg-slate-200 dark:bg-slate-700 h-2.5 rounded-full overflow-hidden">
                    <div class="h-full rounded-full transition-all duration-300 {{ $garagePercent >= 100 ? 'bg-rose-500' : ($garagePercent >= 80 ? 'bg-amber-500' : 'bg-purple-500') }}"
                         style="width: {{ $garagePercent }}%;"></div>
                </div>
                <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-2 flex items-center justify-between">
                    <span>Level {{ $character->level ?? 1 }} limiet: {{ $garageCapacity - ($character->isVip() ? 3 : 0) }} basis</span>
                    @if($nextTierLevel)
                    <span class="text-purple-600 dark:text-purple-400 font-bold">Volgende upgrade: Lvl {{ $nextTierLevel }}</span>
                    @endif
                </p>
            </div>

            <!-- OPBERGPLAATS CAPACITEIT -->
            <div class="p-4 rounded-xl bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70">
                <div class="flex items-center justify-between gap-2 mb-2">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-box-archive text-amber-500 text-sm"></i>
                        <span class="text-xs font-bold text-slate-700 dark:text-slate-200">Opbergruimte (Voorraad)</span>
                        @if($character->isVip())
                        <span class="px-1.5 py-0.5 rounded text-[9px] font-black uppercase tracking-wider bg-amber-50 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 border border-amber-200/80 dark:border-amber-800/70">
                            +50 VIP
                        </span>
                        @endif
                    </div>
                    <span class="text-xs font-black {{ $storagePercent >= 100 ? 'text-rose-500' : 'text-slate-900 dark:text-white' }}">
                        {{ $storageUsed }} / {{ $storageCapacity }} Plekken ({{ $storagePercent }}%)
                    </span>
                </div>
                <div class="w-full bg-slate-200 dark:bg-slate-700 h-2.5 rounded-full overflow-hidden">
                    <div class="h-full rounded-full transition-all duration-300 {{ $storagePercent >= 100 ? 'bg-rose-500' : ($storagePercent >= 80 ? 'bg-amber-500' : 'bg-amber-500') }}"
                         style="width: {{ $storagePercent }}%;"></div>
                </div>
                <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-2 flex items-center justify-between">
                    <span>Level {{ $character->level ?? 1 }} limiet: {{ $storageCapacity - ($character->isVip() ? 50 : 0) }} basis</span>
                    @if($nextTierLevel)
                    <span class="text-amber-600 dark:text-amber-400 font-bold">Volgende upgrade: Lvl {{ $nextTierLevel }}</span>
                    @endif
                </p>
            </div>

        </div>

        <!-- 3 TABS SELECTOR -->
        <div class="pt-6 border-t border-slate-200/80 dark:border-slate-800 mt-6">
            <div class="flex items-center gap-1.5 p-1 rounded-xl bg-slate-100/90 dark:bg-slate-800/80 border border-slate-200/80 dark:border-slate-700/70 overflow-x-auto no-scrollbar">
                <button type="button" @click="tab = 'garage'" 
                        :class="tab === 'garage' ? 'bg-purple-600 text-white shadow-xs font-black' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60 font-bold'"
                        class="px-4 py-2.5 rounded-lg text-xs transition-all duration-150 active:scale-95 cursor-pointer flex items-center gap-2 shrink-0">
                    <i class="fa-solid fa-car-side"></i>
                    <span>1. Persoonlijke Garage ({{ $ownedVehicles->count() }}/{{ $garageCapacity }})</span>
                </button>
                <button type="button" @click="tab = 'storage'" 
                        :class="tab === 'storage' ? 'bg-purple-600 text-white shadow-xs font-black' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60 font-bold'"
                        class="px-4 py-2.5 rounded-lg text-xs transition-all duration-150 active:scale-95 cursor-pointer flex items-center gap-2 shrink-0">
                    <i class="fa-solid fa-box-open"></i>
                    <span>2. Persoonlijke Opbergplaats ({{ $storageUsed }}/{{ $storageCapacity }})</span>
                </button>
                <button type="button" @click="tab = 'varia'" 
                        :class="tab === 'varia' ? 'bg-purple-600 text-white shadow-xs font-black' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60 font-bold'"
                        class="px-4 py-2.5 rounded-lg text-xs transition-all duration-150 active:scale-95 cursor-pointer flex items-center gap-2 shrink-0">
                    <i class="fa-solid fa-ghost text-amber-400"></i>
                    <span>3. Persoonlijk Varia (Event Opslag: {{ $eventItems->count() }} items &bull; {{ number_format($pumpkinSeeds, 0, ',', '.') }} 🎃)</span>
                    <span class="px-1.5 py-0.5 rounded text-[9px] font-black uppercase tracking-wider bg-amber-400 text-slate-950">Event</span>
                </button>
            </div>
        </div>
    </div>

    <!-- ==================== TAB 1: PERSOONLIJKE GARAGE ==================== -->
    <div x-show="tab === 'garage'" class="space-y-6">

        @if($garagePercent >= 100)
        <div class="p-4 rounded-2xl bg-rose-50 dark:bg-rose-950/60 border border-rose-200/80 dark:border-rose-800/70 text-rose-800 dark:text-rose-200 text-xs flex items-center gap-3">
            <i class="fa-solid fa-triangle-exclamation text-rose-500 text-base shrink-0"></i>
            <div>
                <strong>Je garage zit vol ({{ $garageUsed }}/{{ $garageCapacity }})!</strong>
                Je kunt momenteel geen nieuwe voertuigen meer kopen bij de Smokkel Garage. Verhoog je level of activeer VIP voor +3 extra garageplekken.
            </div>
        </div>
        @endif

        @if($ownedVehicles->isEmpty())
        <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-12 text-center shadow-xs">
            <div class="w-16 h-16 rounded-2xl bg-slate-100 dark:bg-slate-800 text-slate-400 flex items-center justify-center text-3xl mx-auto mb-4">
                <i class="fa-solid fa-warehouse"></i>
            </div>
            <h3 class="text-base font-black text-slate-900 dark:text-white">Nog Geen Voertuigen in je Garage</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 max-w-md mx-auto mt-1 leading-relaxed">
                Je hebt nog geen vluchtvoertuig aangeschaft. Schaf een scooter, vluchtauto, speedboot of helikopter aan bij de Smokkel Garage voor succesvolle Grote Heists!
            </p>
            <div class="mt-5">
                <a href="{{ route('garage.index') }}" 
                   class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-700 dark:bg-purple-600 dark:hover:bg-purple-500 text-white font-black text-xs uppercase tracking-wider transition-all duration-150 active:scale-95 shadow-xs cursor-pointer">
                    <i class="fa-solid fa-car"></i>
                    <span>Bekijk Smokkel Garage Showroom</span>
                </a>
            </div>
        </div>
        @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($ownedVehicles as $charVehicle)
            @php $v = $charVehicle->vehicle; @endphp
            <div class="bg-white dark:bg-slate-900 border {{ $charVehicle->is_active ? 'border-purple-500 ring-2 ring-purple-500/20' : 'border-slate-200/90 dark:border-slate-800' }} rounded-2xl p-5 shadow-xs flex flex-col justify-between hover:border-purple-300 dark:hover:border-purple-700/80 transition-all duration-200">
                
                <div>
                    <!-- HEADER & ACTIVE BADGE -->
                    <div class="flex items-start justify-between gap-3 mb-4">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-xl {{ $charVehicle->is_active ? 'bg-purple-600 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300' }} flex items-center justify-center text-xl shrink-0">
                                <i class="fa-solid {{ $v->icon }}"></i>
                            </div>
                            <div>
                                <h4 class="text-sm font-black text-slate-900 dark:text-white">{{ $v->name }}</h4>
                                <span class="text-[10px] uppercase font-black text-slate-400">{{ $v->brand ?? 'Vluchtvoertuig' }}</span>
                            </div>
                        </div>

                        @if($charVehicle->is_active)
                        <span class="px-2 py-0.5 rounded-full bg-purple-50 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 font-black text-[10px] uppercase tracking-wider border border-purple-200/80 dark:border-purple-800/70 shrink-0">
                            Actief
                        </span>
                        @endif
                    </div>

                    <!-- STATS BARS -->
                    <div class="space-y-2.5 mb-4 text-xs">
                        <div>
                            <div class="flex items-center justify-between text-[11px] mb-1 font-bold text-slate-500 dark:text-slate-400">
                                <span>Conditie</span>
                                <span class="{{ $charVehicle->current_condition < 50 ? 'text-rose-500' : 'text-emerald-500' }} font-black">
                                    {{ $charVehicle->current_condition }}%
                                </span>
                            </div>
                            <div class="w-full bg-slate-100 dark:bg-slate-800 h-2 rounded-full overflow-hidden">
                                <div class="h-full rounded-full {{ $charVehicle->current_condition < 50 ? 'bg-rose-500' : 'bg-emerald-500' }}"
                                     style="width: {{ $charVehicle->current_condition }}%;"></div>
                            </div>
                        </div>

                        <div>
                            <div class="flex items-center justify-between text-[11px] mb-1 font-bold text-slate-500 dark:text-slate-400">
                                <span>Vluchtsnelheid</span>
                                <span class="text-purple-600 dark:text-purple-400 font-black">{{ $charVehicle->effective_speed }}%</span>
                            </div>
                            <div class="w-full bg-slate-100 dark:bg-slate-800 h-2 rounded-full overflow-hidden">
                                <div class="h-full bg-purple-500 rounded-full"
                                     style="width: {{ $charVehicle->effective_speed }}%;"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ACTIONS -->
                <div class="pt-3 border-t border-slate-200/80 dark:border-slate-800 flex items-center gap-2">
                    @if(! $charVehicle->is_active)
                    <form action="{{ route('garage.set-active', $charVehicle->id) }}" method="POST" class="flex-1">
                        @csrf
                        <button type="submit" @disabled($character->isImprisoned() || $character->isComatose()) class="w-full py-2 px-3 rounded-xl bg-purple-600 hover:bg-purple-700 dark:bg-purple-600 dark:hover:bg-purple-500 disabled:opacity-50 disabled:cursor-not-allowed text-white font-black text-xs transition-all duration-150 active:scale-95 shadow-xs cursor-pointer flex items-center justify-center gap-1.5">
                            <i class="fa-solid fa-check"></i>
                            <span>Als Vluchtwagen</span>
                        </button>
                    </form>
                    @endif

                    @if($charVehicle->current_condition < 100)
                    <form action="{{ route('garage.repair', $charVehicle->id) }}" method="POST" class="{{ $charVehicle->is_active ? 'flex-1' : 'shrink-0' }}">
                        @csrf
                        <button type="submit" @disabled($character->isImprisoned() || $character->isComatose()) class="w-full py-2 px-3 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 disabled:opacity-50 disabled:cursor-not-allowed text-slate-700 dark:text-slate-200 border border-slate-200/80 dark:border-slate-700 font-bold text-xs transition-all duration-150 active:scale-95 cursor-pointer flex items-center justify-center gap-1.5">
                            <i class="fa-solid fa-wrench text-amber-500"></i>
                            <span>Repareer (€{{ number_format($charVehicle->repair_cost, 0, ',', '.') }})</span>
                        </button>
                    </form>
                    @endif

                    <a href="{{ route('marketplace.index', ['type' => 'vehicle']) }}" 
                       class="py-2 px-2.5 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 hover:bg-indigo-100 dark:hover:bg-indigo-900/60 text-indigo-700 dark:text-indigo-300 border border-indigo-200/80 dark:border-indigo-800/70 font-bold text-xs transition-all duration-150 active:scale-95 flex items-center justify-center gap-1"
                       title="Bied dit voertuig te koop aan op de Marktplaats">
                        <i class="fa-solid fa-shop text-[10px]"></i>
                        <span class="hidden sm:inline">Markt</span>
                    </a>
                </div>

            </div>
            @endforeach
        </div>
        @endif

    </div>

    <!-- ==================== TAB 2: PERSOONLIJKE OPBERGPLAATS ==================== -->
    <div x-show="tab === 'storage'" class="space-y-6">

        @if($storageUsed >= $storageCapacity)
        <div class="p-4 rounded-2xl bg-rose-50 dark:bg-rose-950/60 border-2 border-rose-500/80 text-rose-900 dark:text-rose-100 text-xs flex items-center justify-between gap-4 shadow-lg shadow-rose-900/10">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-rose-500 text-white flex items-center justify-center text-lg shrink-0 animate-pulse">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
                <div>
                    <h4 class="font-black text-sm text-rose-700 dark:text-rose-300">⚠️ OPBERGPLAATS OVERVOL ({{ $storageUsed }}/{{ $storageCapacity }} plaatsen bezet)!</h4>
                    <p class="text-xs text-rose-600 dark:text-rose-400 mt-0.5">
                        Je hebt je maximale voorraad overschreden. Zolang je opslag vol zit kun je <strong>geen nieuwe kluizen kraken, pakketjes aannemen of buit ontvangen</strong>. Verkoop hieronder direct overtollige maskers of spullen aan de heler om ruimte vrij te maken!
                    </p>
                </div>
            </div>
        </div>
        @endif

        <!-- OVERVAL MASKERS KAART -->
        <div x-data="{ showSellModal: false, sellQty: {{ min(10, (int)($character->masks ?? 0)) }} }" class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-5 shadow-xs">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-purple-50 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400 flex items-center justify-center text-xl shrink-0">
                        <i class="fa-solid fa-masks-theater"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-sm font-black text-slate-900 dark:text-white">Overval Maskers (Heist Gear)</h3>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-purple-50 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 border border-purple-200/80 dark:border-purple-800/70">
                                €1.500 / stuk helerwaarde
                            </span>
                        </div>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                            Noodzakelijk voor de bemanning tijdens heists, of verkoop ze direct voor contant zwart geld.
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-2.5 shrink-0 w-full sm:w-auto justify-between sm:justify-end">
                    <span class="text-lg font-black text-purple-600 dark:text-purple-400">
                        {{ (int)($character->masks ?? 0) }} <span class="text-xs font-bold text-slate-400">Stuks</span>
                    </span>
                    <div class="flex items-center gap-2">
                        @if((int)($character->masks ?? 0) > 0)
                        <button type="button" @click="showSellModal = true"
                                class="px-3.5 py-2 rounded-xl btn-star-gold text-slate-950 font-black text-xs transition-all duration-150 active:scale-95 flex items-center gap-1.5 shadow-xs cursor-pointer">
                            <i class="fa-solid fa-coins"></i>
                            <span>Maskers Verkopen</span>
                        </button>
                        @endif
                        <a href="{{ route('heists.index') }}" class="px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 border border-slate-200/80 dark:border-slate-700 font-bold text-xs transition-all duration-150 active:scale-95 cursor-pointer">
                            Heists
                        </a>
                    </div>
                </div>
            </div>

            <!-- MODAL VOOR MASKERS VERKOPEN -->
            <div x-show="showSellModal" x-cloak
                 class="fixed inset-0 z-50 flex items-center justify-center p-4 pb-24 lg:pb-4 bg-slate-950/70 backdrop-blur-xs">
                <div @click.away="showSellModal = false"
                     class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-3xl max-w-md w-full max-h-[85dvh] overflow-y-auto p-6 shadow-2xl">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-200/80 dark:border-slate-800">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-lg bg-amber-500/20 text-amber-500 flex items-center justify-center">
                                <i class="fa-solid fa-masks-theater text-sm"></i>
                            </div>
                            <h3 class="font-black text-sm text-slate-900 dark:text-white">Maskers Verkopen aan Heler</h3>
                        </div>
                        <button type="button" @click="showSellModal = false" aria-label="Sluiten" class="w-8 h-8 rounded-xl flex items-center justify-center text-slate-400 hover:text-slate-600 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 cursor-pointer transition">
                            <i class="fa-solid fa-xmark text-sm"></i>
                        </button>
                    </div>

                    <form action="{{ route('depot.sell-masks') }}" method="POST" class="mt-4 space-y-4">
                        @csrf
                        <div class="p-3.5 rounded-2xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200/80 dark:border-amber-800/60 text-xs text-amber-900 dark:text-amber-200">
                            De heler biedt <strong>€1.500 contant per masker</strong>. Verkoop overtollige maskers om direct contant geld te ontvangen!
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 mb-1.5">
                                Aantal maskers te verkopen (Je bezit: {{ (int)($character->masks ?? 0) }}):
                            </label>
                            <div class="flex items-center gap-2">
                                <input type="number" name="quantity" x-model.number="sellQty" min="1" max="{{ (int)($character->masks ?? 0) }}"
                                       class="w-full px-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/90 font-bold text-slate-900 dark:text-slate-100 text-sm placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:border-purple-500 dark:focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition" required>
                                <button type="button" @click="sellQty = {{ (int)($character->masks ?? 0) }}"
                                        class="px-3 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 font-black text-xs text-slate-700 dark:text-slate-200 border border-slate-200/80 dark:border-slate-700 shrink-0 transition-all duration-150 active:scale-95 cursor-pointer">
                                    Alles
                                </button>
                            </div>
                        </div>

                        <div class="p-3 rounded-xl bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70 flex items-center justify-between text-xs font-bold">
                            <span class="text-slate-500 dark:text-slate-400">Verwachte Opbrengst:</span>
                            <span class="text-emerald-600 dark:text-emerald-400 font-black text-sm" x-text="'€' + new Intl.NumberFormat('nl-NL').format(Math.max(0, sellQty * 1500))"></span>
                        </div>

                        <div class="flex items-center justify-end gap-2 pt-2">
                            <button type="button" @click="showSellModal = false" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 border border-slate-200/80 dark:border-slate-700 font-bold text-xs transition-all duration-150 active:scale-95 cursor-pointer">
                                Annuleren
                            </button>
                            <button type="submit" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 dark:bg-emerald-600 dark:hover:bg-emerald-500 text-white font-black text-xs transition-all duration-150 active:scale-95 shadow-xs cursor-pointer">
                                <i class="fa-solid fa-check mr-1"></i> Bevestig Verkoop
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        @if($inventoryItems->isEmpty())
        <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-12 text-center shadow-xs">
            <div class="w-16 h-16 rounded-2xl bg-slate-100 dark:bg-slate-800 text-slate-400 flex items-center justify-center text-3xl mx-auto mb-4">
                <i class="fa-solid fa-box-open"></i>
            </div>
            <h3 class="text-base font-black text-slate-900 dark:text-white">Je Opbergplaats is Leeg</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 max-w-md mx-auto mt-1 leading-relaxed">
                Je hebt momenteel geen wapens, sigaretten, pillen of telefoons op voorraad. Koop voorraden in de Kantine of smokkel items binnen via droppings!
            </p>
            <div class="mt-5">
                <a href="{{ route('shop.index') }}" 
                   class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-700 dark:bg-purple-600 dark:hover:bg-purple-500 text-white font-black text-xs uppercase tracking-wider transition-all duration-150 active:scale-95 shadow-xs cursor-pointer">
                    <i class="fa-solid fa-store"></i>
                    <span>Bezoek de Kantine & Winkel</span>
                </a>
            </div>
        </div>
        @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
            @foreach($inventoryItems as $itemRow)
            @php
                $it = $itemRow->item;
                $unitSellPrice = $it->getDepotSellPrice();
                $depotIcon = match($it->icon ?? '') {
                    'fa-key-skeleton' => 'fa-key',
                    'fa-mitten' => 'fa-hand-fist',
                    'fa-vest' => 'fa-shirt',
                    'fa-utensils' => 'fa-bowl-food',
                    default => ($it->icon ?: 'fa-cube'),
                };
            @endphp
            <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-4 shadow-xs flex flex-col justify-between hover:border-purple-300 dark:hover:border-purple-700/80 transition-all duration-200">
                
                <div>
                    <!-- ITEM HEADER -->
                    <div class="flex items-start justify-between gap-2 mb-3">
                        <div class="flex items-center gap-3">
                            <div class="w-11 h-11 rounded-xl bg-slate-50 dark:bg-slate-800/80 text-purple-600 dark:text-purple-400 flex items-center justify-center text-lg shrink-0 border border-slate-200/60 dark:border-slate-700/50">
                                <i class="fa-solid {{ $depotIcon }}"></i>
                            </div>
                            <div>
                                <h4 class="text-xs font-black text-slate-900 dark:text-white leading-snug">{{ $it->name }}</h4>
                                <span class="text-[10px] uppercase font-bold text-slate-400">{{ $it->category }}</span>
                            </div>
                        </div>
                        <span class="px-2 py-0.5 rounded-md bg-purple-50 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 border border-purple-200/80 dark:border-purple-800/70 font-black text-[11px] shrink-0">
                            {{ $itemRow->quantity }}x
                        </span>
                    </div>

                    <p class="text-[11px] text-slate-500 dark:text-slate-400 line-clamp-2 mb-3">
                        {{ $it->description ?? 'Geen beschrijving beschikbaar.' }}
                    </p>

                    <!-- BONUSES -->
                    <div class="flex flex-wrap gap-1.5 mb-4 text-[10px] font-bold">
                        @if($it->health_restore > 0)
                        <span class="px-2 py-0.5 rounded bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300">
                            +{{ $it->health_restore }} HP
                        </span>
                        @endif
                        @if($it->energy_restore > 0)
                        <span class="px-2 py-0.5 rounded bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300">
                            +{{ $it->energy_restore }} Energie
                        </span>
                        @endif
                        @if($it->strength_bonus > 0)
                        <span class="px-2 py-0.5 rounded bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300">
                            +{{ $it->strength_bonus }} Kracht
                        </span>
                        @endif
                        @if($it->defense_bonus > 0)
                        <span class="px-2 py-0.5 rounded bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300">
                            +{{ $it->defense_bonus }} Verdediging
                        </span>
                        @endif
                        @if(($it->stamina_bonus ?? 0) > 0)
                        <span class="px-2 py-0.5 rounded bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300">
                            +{{ $it->stamina_bonus }} Conditie
                        </span>
                        @endif
                        @if(($it->intelligence_bonus ?? 0) > 0)
                        <span class="px-2 py-0.5 rounded bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300">
                            +{{ $it->intelligence_bonus }} Intelligentie
                        </span>
                        @endif
                        @if(($it->charisma_bonus ?? 0) > 0)
                        <span class="px-2 py-0.5 rounded bg-purple-50 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300">
                            +{{ $it->charisma_bonus }} Charisma
                        </span>
                        @endif
                        @if(($it->discipline_bonus ?? 0) > 0)
                        <span class="px-2 py-0.5 rounded bg-cyan-50 dark:bg-cyan-950/60 text-cyan-700 dark:text-cyan-300">
                            +{{ $it->discipline_bonus }} Discipline
                        </span>
                        @endif
                        <span class="px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-emerald-700 dark:text-emerald-400 font-mono">
                            Verkoop: €{{ number_format($unitSellPrice, 0, ',', '.') }} / stuk
                        </span>
                    </div>
                </div>

                <!-- ACTIONS -->
                <div class="pt-3 border-t border-slate-200/80 dark:border-slate-800 flex items-center gap-2">
                    @php
                        $isConsumable = $it->isConsumableItem();
                    @endphp

                    @if($isConsumable)
                    <form action="{{ route('shop.consume', $itemRow->id) }}" method="POST" class="flex-1">
                        @csrf
                        <input type="hidden" name="quantity" value="1">
                        <button type="submit" @disabled($character->isComatose()) class="w-full py-2 px-2.5 rounded-xl bg-purple-600 hover:bg-purple-700 dark:bg-purple-600 dark:hover:bg-purple-500 disabled:opacity-50 disabled:cursor-not-allowed text-white font-black text-[11px] transition-all duration-150 active:scale-95 shadow-xs cursor-pointer flex items-center justify-center gap-1">
                            <i class="fa-solid fa-bolt"></i>
                            <span>Gebruiken</span>
                        </button>
                    </form>
                    @else
                    <a href="{{ route('marketplace.index', ['type' => 'depot_item']) }}" class="flex-1 py-2 px-2.5 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 hover:bg-indigo-100 dark:hover:bg-indigo-900/60 text-indigo-700 dark:text-indigo-300 border border-indigo-200/80 dark:border-indigo-800/70 font-bold text-[11px] transition-all duration-150 active:scale-95 shadow-xs flex items-center justify-center gap-1">
                        <i class="fa-solid fa-shop text-[10px]"></i>
                        <span>Marktplaats</span>
                    </a>
                    @endif

                    <!-- SELL BUTTON -->
                    <button type="button" 
                            @click="sellModalItem = { id: {{ (int) $itemRow->id }}, name: @js($it->name), qty: {{ (int) $itemRow->quantity }}, unitPrice: {{ (int) $unitSellPrice }} }; sellQty = 1;"
                            class="py-2 px-3 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 border border-slate-200/80 dark:border-slate-700 font-bold text-[11px] transition-all duration-150 active:scale-95 cursor-pointer flex items-center justify-center gap-1">
                        <i class="fa-solid fa-coins text-amber-500"></i>
                        <span>Verkopen</span>
                    </button>
                </div>

            </div>
            @endforeach
        </div>
        @endif

    </div>

    <!-- ==================== TAB 3: PERSOONLIJK VARIA (EVENT OPSLAG) ==================== -->
    <div x-show="tab === 'varia'" class="space-y-6">

        <!-- INTRO BANNER MET LIVE STATUS & COUNTDOWN -->
        <div class="p-6 rounded-2xl bg-gradient-to-r from-amber-500/10 via-orange-500/5 to-emerald-600/10 border border-amber-500/30 dark:border-amber-500/40 shadow-xs flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-amber-500 text-slate-950 flex items-center justify-center text-3xl shadow-sm shrink-0">
                    <i class="fa-solid fa-leaf animate-pulse"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <h3 class="text-base font-black text-slate-900 dark:text-white">Persoonlijk Varia &amp; Event Kluis</h3>
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-amber-600 text-white shadow-2xs">
                            🍂 De Gouden Herfstoogst 2026 Actief
                        </span>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-emerald-100 dark:bg-emerald-950/80 text-emerald-800 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-700/60">
                            Onbeperkte Capaciteit &bull; Verzamelobjecten Blijven Voor Altijd ♾️
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1.5 max-w-2xl leading-relaxed">
                        In tegenstelling tot je reguliere Opbergplaats heeft de Event Kluis <strong>geen maximum opslaglimiet</strong>.
                        Alle geclaimde <strong>Halloween-items, profielkaders en seizoens-collectibles</strong> zijn permanente verzamelobjecten en worden hier voor altijd veilig bewaard!
                    </p>
                </div>
            </div>

            <div class="shrink-0 text-left md:text-right w-full md:w-auto p-3 md:p-0 rounded-xl bg-white/60 dark:bg-slate-900/60 md:bg-transparent border md:border-0 border-amber-200 dark:border-amber-950/60">
                <span class="text-[10px] font-black text-slate-400 uppercase tracking-wider block">Resterende Event Tijd</span>
                <span class="text-sm font-black text-amber-600 dark:text-amber-400 flex items-center gap-1.5 md:justify-end">
                    <i class="fa-solid fa-hourglass-half animate-spin text-xs"></i>
                    <span>{{ $timeRemaining['formatted'] ?? 'Actief' }}</span>
                </span>
                <span class="text-[10px] text-slate-400 font-medium block mt-0.5">Tot 30 november 23:59u</span>
            </div>
        </div>

        <!-- EVENT VALUTA & QUICK METRICS -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            
            <!-- GOUDEN EIKELS SALDO -->
            <div class="bg-white dark:bg-slate-900 border border-amber-500/30 rounded-2xl p-5 shadow-xs flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-[10px] font-black uppercase tracking-wider text-amber-600 dark:text-amber-400 flex items-center gap-1">
                            <i class="fa-solid fa-coins"></i> Evenement Valuta
                        </span>
                        <span class="px-2 py-0.5 rounded text-[9px] font-black bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border border-amber-200/80 dark:border-amber-800/70">
                            Gouden Eikels
                        </span>
                    </div>
                    <div class="text-2xl font-black text-slate-900 dark:text-white flex items-center gap-2">
                        <span>{{ number_format($pumpkinSeeds, 0, ',', '.') }}</span>
                        <span class="text-xl">🍂</span>
                    </div>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">
                        Verdiend met Herfstoogst Quests, zakkenrollen, vechten en trainingen.
                    </p>
                </div>
                <div class="pt-4 mt-4 border-t border-slate-200/80 dark:border-slate-800 flex items-center gap-2">
                    <a href="{{ route('halloween.shop') }}" class="flex-1 py-2 px-3 rounded-xl bg-amber-600 hover:bg-amber-700 active:scale-95 text-white font-black text-xs uppercase tracking-wider transition text-center shadow-xs flex items-center justify-center gap-1.5 cursor-pointer">
                        <i class="fa-solid fa-store text-xs"></i>
                        <span>Winkel</span>
                    </a>
                    <a href="{{ route('halloween.quests') }}" class="flex-1 py-2 px-3 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 border border-slate-200/80 dark:border-slate-700 font-bold text-xs uppercase tracking-wider transition-all duration-150 active:scale-95 text-center flex items-center justify-center gap-1.5 cursor-pointer">
                        <i class="fa-solid fa-leaf text-xs text-amber-500"></i>
                        <span>Quests</span>
                    </a>
                </div>
            </div>

            <!-- OVERVAL & EVENT MASKERS -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-5 shadow-xs flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-[10px] font-black uppercase tracking-wider text-purple-600 dark:text-purple-400 flex items-center gap-1">
                            <i class="fa-solid fa-masks-theater"></i> Event Uitrusting
                        </span>
                        <span class="px-2 py-0.5 rounded text-[9px] font-black bg-purple-50 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 border border-purple-200/80 dark:border-purple-800/70">
                            Maskers
                        </span>
                    </div>
                    <div class="text-2xl font-black text-slate-900 dark:text-white flex items-center gap-2">
                        <span>{{ (int)($character->masks ?? 0) }}</span>
                        <span class="text-lg text-purple-500">Stuks 🎭</span>
                    </div>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">
                        Jack-O'-Lantern & overvalmaskers voor ongemerkt zakkenrollen en heists.
                    </p>
                </div>
                <div class="pt-4 mt-4 border-t border-slate-200/80 dark:border-slate-800 flex items-center gap-2">
                    <a href="{{ route('theft.index') }}" class="w-full py-2 px-3 rounded-xl bg-purple-600 hover:bg-purple-700 dark:bg-purple-600 dark:hover:bg-purple-500 active:scale-95 text-white font-black text-xs uppercase tracking-wider transition-all duration-150 text-center shadow-xs flex items-center justify-center gap-1.5 cursor-pointer">
                        <i class="fa-solid fa-mask"></i>
                        <span>Zakkenrollen</span>
                    </a>
                </div>
            </div>

            <!-- EVENT OPSLAG CAPACITEIT -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-5 shadow-xs flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-[10px] font-black uppercase tracking-wider text-emerald-600 dark:text-emerald-400 flex items-center gap-1">
                            <i class="fa-solid fa-boxes-packing"></i> Kluis Status
                        </span>
                        <span class="px-2 py-0.5 rounded text-[9px] font-black bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200/80 dark:border-emerald-800/70">
                            Varia
                        </span>
                    </div>
                    <div class="text-2xl font-black text-slate-900 dark:text-white flex items-center gap-2">
                        <span>{{ $eventItems->count() }} Items</span>
                        <span class="text-lg text-emerald-500">♾️</span>
                    </div>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">
                        Geen limiet! Event-items belasten je normale opslagcapaciteit niet.
                    </p>
                </div>
                <div class="pt-4 mt-4 border-t border-slate-200/80 dark:border-slate-800 flex items-center gap-2">
                    <a href="{{ route('marketplace.index', ['type' => 'depot_item']) }}" class="w-full py-2 px-3 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 border border-slate-200/80 dark:border-slate-700 font-bold text-xs uppercase tracking-wider transition-all duration-150 active:scale-95 text-center flex items-center justify-center gap-1.5 cursor-pointer">
                        <i class="fa-solid fa-shop text-xs text-indigo-500"></i>
                        <span>Marktplaats Verkoop</span>
                    </a>
                </div>
            </div>

        </div>

        <!-- ACTIEVE EVENT PERKS / ZEGENINGEN -->
        @if($character->hasPerk('magere_hein_zegening'))
        <div class="p-5 rounded-2xl bg-gradient-to-r from-amber-500/15 via-purple-600/15 to-amber-500/10 dark:to-slate-900/40 border border-amber-400/60 shadow-xs flex items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-amber-400 text-slate-950 flex items-center justify-center text-xl shrink-0 shadow-md animate-bounce">
                    <i class="fa-solid fa-skull"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h4 class="text-sm font-black text-slate-900 dark:text-white">💀 Magere Hein Zegening Actief!</h4>
                        <span class="px-2 py-0.5 rounded text-[9px] font-black uppercase tracking-wider bg-amber-400 text-slate-950">
                            +15% Stats
                        </span>
                    </div>
                    <p class="text-xs text-slate-600 dark:text-slate-300 mt-0.5">
                        Je geniet van <strong>+15% extra Kracht &amp; Verdediging</strong> bij alle gevechten en berovingen op de luchtplaats!
                    </p>
                </div>
            </div>
            <div class="text-right shrink-0">
                <span class="text-[10px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider block">Resterende Duur</span>
                <span class="text-xs font-black text-amber-600 dark:text-amber-500 font-mono">{{ $character->getPerkRemainingHours('magere_hein_zegening') }} uur resterend</span>
            </div>
        </div>
        @endif

        <!-- ============================================== -->
        <!-- 1. EVENT ITEMS & VERZAMELOBJECTEN IN BEZIT    -->
        <!-- ============================================== -->
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <i class="fa-solid fa-box-archive text-amber-500 text-base"></i>
                    <h3 class="text-sm font-black text-slate-900 dark:text-white">Fysieke Event Items &amp; Permanente Collectibles ({{ $eventItems->count() }})</h3>
                </div>
                <a href="{{ route('halloween.shop') }}" class="text-xs font-bold text-amber-600 dark:text-amber-400 hover:underline flex items-center gap-1">
                    <span>Meer scoren in Herfstoogst Winkel</span> &raquo;
                </a>
            </div>

            @if($eventItems->isEmpty())
            <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-8 text-center shadow-xs space-y-4">
                <div class="w-14 h-14 rounded-2xl bg-amber-100 dark:bg-amber-950/60 text-amber-600 flex items-center justify-center text-2xl mx-auto">
                    <i class="fa-solid fa-leaf"></i>
                </div>
                <div class="max-w-md mx-auto">
                    <h4 class="text-sm font-black text-slate-900 dark:text-white">Geen fysieke event-items in bezit</h4>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 leading-relaxed">
                        Scoor unieke Herfstoogst collectibles zoals de <strong>Warme Kruidencider (+35 Energie)</strong>,
                        de <strong>Gouden Eikel Trofee</strong> of de <strong>Koperen Bosuil</strong> in de Herfstoogst Winkel!
                    </p>
                </div>
                <div class="flex items-center justify-center gap-3 pt-2">
                    <a href="{{ route('halloween.shop') }}" class="px-5 py-2.5 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-black text-xs uppercase tracking-wider transition-all duration-150 active:scale-95 shadow-xs flex items-center gap-2 cursor-pointer">
                        <i class="fa-solid fa-store"></i>
                        <span>Naar Herfstoogst Winkel</span>
                    </a>
                    <a href="{{ route('halloween.quests') }}" class="px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 border border-slate-200/80 dark:border-slate-700 font-bold text-xs uppercase tracking-wider transition-all duration-150 active:scale-95 flex items-center gap-2 cursor-pointer">
                        <i class="fa-solid fa-leaf text-amber-500"></i>
                        <span>Quests Spelen</span>
                    </a>
                </div>
            </div>
            @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                @foreach($eventItems as $eRow)
                @php
                    $eItem = $eRow->item;
                    $eventSellPrice = $eItem->getDepotSellPrice();
                    $isHalloweenItem = str_starts_with((string) ($eItem->slug ?? ''), 'halloween_');
                @endphp
                <div class="bg-white dark:bg-slate-900 border border-amber-500/30 rounded-2xl p-4 shadow-xs flex flex-col justify-between hover:border-amber-500/60 transition-all duration-200">
                    <div>
                        <!-- ITEM HEADER -->
                        <div class="flex items-start justify-between gap-2 mb-3">
                            <div class="flex items-center gap-3">
                                <div class="w-11 h-11 rounded-xl bg-amber-100 dark:bg-amber-950/60 text-amber-600 flex items-center justify-center text-lg shrink-0">
                                    <i class="fa-solid {{ $eItem->icon ?? 'fa-leaf' }}"></i>
                                </div>
                                <div>
                                    <h4 class="text-xs font-black text-slate-900 dark:text-white leading-snug">{{ $eItem->name }}</h4>
                                    @if($isHalloweenItem)
                                        <span class="text-[10px] uppercase font-bold text-rose-500">🎃 Halloween Verzamelobject</span>
                                    @else
                                        <span class="text-[10px] uppercase font-bold text-amber-500">🍂 Event Collectible</span>
                                    @endif
                                </div>
                            </div>
                            <span class="px-2 py-0.5 rounded-md bg-amber-50 dark:bg-amber-950/80 text-amber-700 dark:text-amber-300 border border-amber-200/80 dark:border-amber-800/70 font-black text-[11px] shrink-0">
                                {{ $eRow->quantity }}x
                            </span>
                        </div>

                        <p class="text-[11px] text-slate-500 dark:text-slate-400 line-clamp-2 mb-3">
                            {{ $eItem->description ?? 'Zeldzaam evenement-item uit de TBS Kliniek.' }}
                        </p>

                        <!-- EVENT BADGES -->
                        <div class="flex flex-wrap gap-1.5 mb-4 text-[10px] font-bold">
                            @if($eItem->energy_restore > 0)
                            <span class="px-2 py-0.5 rounded bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border border-amber-200/80 dark:border-amber-800/70 flex items-center gap-1">
                                <i class="fa-solid fa-bolt text-[9px]"></i> +{{ $eItem->energy_restore }} Energie
                            </span>
                            @endif
                            @if($isHalloweenItem)
                            <span class="px-2 py-0.5 rounded bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border border-rose-200/80 dark:border-rose-800/70">
                                Niet meer haalbaar &bull; Blijft voor altijd behouden
                            </span>
                            @else
                            <span class="px-2 py-0.5 rounded bg-purple-50 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 border border-purple-200/80 dark:border-purple-800/70">
                                Zeldzaamheid: Legendarisch
                            </span>
                            <span class="px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-emerald-700 dark:text-emerald-400 font-mono">
                                Verkoop: €{{ number_format($eventSellPrice, 0, ',', '.') }} / stuk
                            </span>
                            @endif
                        </div>
                    </div>

                    <!-- ACTIONS -->
                    <div class="pt-3 border-t border-slate-200/80 dark:border-slate-800 flex items-center gap-2">
                        @if($eItem->isConsumableItem())
                        <form action="{{ route('shop.consume', $eRow->id) }}" method="POST" class="flex-1">
                            @csrf
                            <input type="hidden" name="quantity" value="1">
                            <button type="submit" class="w-full py-2 px-2.5 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-black text-[11px] transition-all duration-150 active:scale-95 shadow-xs cursor-pointer flex items-center justify-center gap-1">
                                <i class="fa-solid fa-bolt"></i>
                                <span>Consumeren</span>
                            </button>
                        </form>
                        @endif

                        @if($isHalloweenItem)
                        <div class="py-2 px-2.5 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-300 border border-emerald-200/80 dark:border-emerald-800/70 font-bold text-[10px] flex items-center justify-center gap-1 {{ $eItem->isConsumableItem() ? '' : 'flex-1' }}">
                            <i class="fa-solid fa-shield-heart"></i>
                            <span>Permanent Verzamelobject</span>
                        </div>
                        @else
                        <a href="{{ route('marketplace.index', ['type' => 'depot_item']) }}" 
                           class="py-2 px-2.5 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 hover:bg-indigo-100 dark:hover:bg-indigo-900/60 text-indigo-700 dark:text-indigo-300 border border-indigo-200/80 dark:border-indigo-800/70 font-bold text-[11px] transition-all duration-150 active:scale-95 flex items-center justify-center gap-1 {{ $eItem->isConsumableItem() ? '' : 'flex-1' }}">
                            <i class="fa-solid fa-shop text-[10px]"></i>
                            <span>Marktplaats</span>
                        </a>

                        <button type="button" 
                                @click="sellModalItem = { id: {{ (int) $eRow->id }}, name: @js($eItem->name), qty: {{ (int) $eRow->quantity }}, unitPrice: {{ (int) $eventSellPrice }} }; sellQty = 1;"
                                class="py-2 px-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 border border-slate-200/80 dark:border-slate-700 font-bold text-[11px] transition-all duration-150 active:scale-95 cursor-pointer flex items-center justify-center gap-1">
                            <i class="fa-solid fa-coins text-amber-500"></i>
                            <span>Verkopen</span>
                        </button>
                        @endif
                    </div>

                </div>
                @endforeach
            </div>
            @endif
        </div>

        <!-- ============================================== -->
        <!-- 2. PROFIELKADERS (FRAMES) SHOWCASE             -->
        <!-- ============================================== -->
        <div class="space-y-4 pt-4 border-t border-slate-200/80 dark:border-slate-800">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <div class="flex items-center gap-2.5">
                    <i class="fa-solid fa-palette text-amber-500 text-base"></i>
                    <div>
                        <h3 class="text-sm font-black text-slate-900 dark:text-white">Profielkaders &amp; Avatar Frames Collectie</h3>
                        <p class="text-[11px] text-slate-400">Inclusief jouw permanente Halloween-verzamelobjecten (Niet meer haalbaar) en nieuwe Herfstoogst-kaders (Haalbaar).</p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-xs font-bold text-slate-500 dark:text-slate-400">
                        {{ count($unlockedBorders) }} / {{ count($borderCatalog) }} Vrijgespeeld
                    </span>
                    <a href="{{ route('borders.index') }}" class="px-3 py-1.5 rounded-xl bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800/60 text-xs font-black hover:bg-amber-100 transition flex items-center gap-1.5">
                        <i class="fa-solid fa-id-badge"></i>
                        <span>Overzichtspagina Profielkaders &raquo;</span>
                    </a>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                @foreach($borderCatalog as $bKey => $border)
                @php
                    $isUnlocked = in_array($bKey, $unlockedBorders, true);
                    $isEquipped = ($equippedBorder === $bKey);
                    $isObtainable = (bool) ($border['obtainable'] ?? true);
                    $statusLabel = $border['status_label'] ?? ($isObtainable ? 'Haalbaar' : 'Niet meer haalbaar');
                @endphp
                <div class="bg-white dark:bg-slate-900 border {{ $isEquipped ? 'border-amber-500 ring-2 ring-amber-500/30' : ($isUnlocked ? 'border-slate-200/90 dark:border-slate-700' : 'border-slate-200/90 dark:border-slate-800 opacity-80') }} rounded-2xl p-4 shadow-xs flex flex-col justify-between hover:border-amber-300 dark:hover:border-amber-700/80 transition-all duration-200">
                    <div>
                        <!-- BORDER PREVIEW WITH AVATAR -->
                        <div class="relative w-20 h-20 mx-auto my-3 flex items-center justify-center">
                            <img src="{{ $character->avatar_url ?? asset('assets/characters/avatar_male_1.jpg') }}" 
                                 class="w-16 h-16 rounded-2xl object-cover {{ $border['preview_class'] ?? '' }}"
                                 alt="{{ $border['name'] }}"
                                 onerror="this.src='{{ asset('assets/characters/avatar_male_1.jpg') }}'">
                            @if($isEquipped)
                            <span class="absolute -top-1 -right-1 w-6 h-6 rounded-full bg-amber-500 text-slate-950 flex items-center justify-center text-xs shadow-md">
                                <i class="fa-solid fa-check"></i>
                            </span>
                            @endif
                        </div>

                        <div class="text-center mt-2">
                            <div class="flex items-center justify-center gap-1 flex-wrap">
                                @if($isObtainable)
                                    <span class="text-[9px] font-black uppercase tracking-wider px-2 py-0.5 rounded-full bg-emerald-100 dark:bg-emerald-950/80 text-emerald-700 dark:text-emerald-300 border border-emerald-300/60 dark:border-emerald-700/60">
                                        Haalbaar
                                    </span>
                                @else
                                    <span class="text-[9px] font-black uppercase tracking-wider px-2 py-0.5 rounded-full bg-rose-100 dark:bg-rose-950/80 text-rose-700 dark:text-rose-300 border border-rose-300/60 dark:border-rose-700/60">
                                        {{ $statusLabel }}
                                    </span>
                                @endif
                            </div>
                            <h4 class="text-xs font-black text-slate-900 dark:text-white mt-1.5">{{ $border['emoji'] ?? '' }} {{ $border['name'] }}</h4>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1 line-clamp-2">
                                {{ $border['description'] }}
                            </p>
                        </div>
                    </div>

                    <!-- ACTION BUTTONS -->
                    <div class="pt-3 mt-3 border-t border-slate-200/80 dark:border-slate-800">
                        @if($isUnlocked)
                            @if($isEquipped)
                            <form action="{{ route('halloween.equip-border') }}" method="POST">
                                @csrf
                                <input type="hidden" name="border_key" value="none">
                                <button type="submit" class="w-full py-2 px-3 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 border border-slate-200/80 dark:border-slate-700 font-bold text-[11px] transition-all duration-150 active:scale-95 cursor-pointer flex items-center justify-center gap-1.5">
                                    <i class="fa-solid fa-xmark"></i>
                                    <span>Deactiveren</span>
                                </button>
                            </form>
                            @else
                            <form action="{{ route('halloween.equip-border') }}" method="POST">
                                @csrf
                                <input type="hidden" name="border_key" value="{{ $bKey }}">
                                <button type="submit" class="w-full py-2 px-3 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-black text-[11px] transition-all duration-150 active:scale-95 shadow-xs cursor-pointer flex items-center justify-center gap-1.5">
                                    <i class="fa-solid fa-wand-magic-sparkles"></i>
                                    <span>Activeer Rand</span>
                                </button>
                            </form>
                            @endif
                        @elseif($isObtainable)
                            <a href="{{ route('halloween.shop') }}" class="w-full py-2 px-3 rounded-xl bg-slate-100 hover:bg-amber-600 hover:text-white dark:bg-slate-800 dark:hover:bg-amber-600 text-slate-700 dark:text-slate-300 border border-slate-200/80 dark:border-slate-700 font-bold text-[11px] transition-all duration-150 active:scale-95 text-center flex items-center justify-center gap-1.5 cursor-pointer">
                                <i class="fa-solid fa-lock text-[10px]"></i>
                                <span>Kopen ({{ $border['price_seeds'] }} 🍂)</span>
                            </a>
                        @else
                            <div class="w-full py-2 px-3 rounded-xl bg-slate-100 dark:bg-slate-800/80 text-slate-400 dark:text-slate-500 border border-slate-200/80 dark:border-slate-700 font-bold text-[11px] text-center flex items-center justify-center gap-1.5 select-none">
                                <i class="fa-solid fa-lock text-[10px]"></i>
                                <span>Niet meer haalbaar</span>
                            </div>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- ============================================== -->
        <!-- 3. GEHEIME KLICODES & VOUCHERS                -->
        <!-- ============================================== -->
        @if($unredeemedCodes->isNotEmpty())
        <div class="space-y-4 pt-4 border-t border-slate-200/80 dark:border-slate-800">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <i class="fa-solid fa-vault text-amber-500 text-base"></i>
                    <div>
                        <h3 class="text-sm font-black text-slate-900 dark:text-white">Onverzilverde Kluiscodes &amp; Vouchers ({{ $unredeemedCodes->count() }})</h3>
                        <p class="text-[11px] text-slate-400">Verzilver jouw vouchers direct voor contant bankgeld, exclusieve sterren en kluistokens.</p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($unredeemedCodes as $code)
                <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-amber-300/80 dark:border-amber-700/60 shadow-xs flex items-center justify-between gap-4">
                    <div class="space-y-1">
                        <span class="text-[10px] font-black uppercase text-amber-500 tracking-wider">Kluis Voucher</span>
                        <div class="font-mono font-black text-sm text-slate-900 dark:text-white tracking-wider">
                            {{ $code->code }}
                        </div>
                        <div class="text-[11px] text-slate-500 dark:text-slate-400 font-bold">
                            +€{{ number_format($code->reward_cash, 0, ',', '.') }} bankgeld &bull; +{{ $code->reward_stars }} ⭐
                        </div>
                    </div>
                    <a href="{{ route('vault.index', ['code' => $code->code]) }}" 
                       class="px-4 py-2 rounded-xl btn-star-gold text-slate-950 font-black text-xs uppercase tracking-wider transition-all duration-150 active:scale-95 shadow-xs flex items-center gap-1.5 shrink-0 cursor-pointer">
                        <i class="fa-solid fa-unlock-keyhole"></i>
                        <span>Inwisselen</span>
                    </a>
                </div>
                @endforeach
            </div>
        </div>
        @endif

    </div>

    <!-- SELL CONFIRMATION MODAL -->
    <div x-show="sellModalItem" 
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 pb-24 lg:pb-4 backdrop-blur-xs"
         @click.self="sellModalItem = null">
        
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/90 dark:border-slate-800 max-w-sm w-full max-h-[85dvh] overflow-y-auto p-6 shadow-xl space-y-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-coins text-amber-500"></i>
                    <h4 class="text-sm font-black text-slate-900 dark:text-white">Item Verkopen</h4>
                </div>
                <button type="button" @click="sellModalItem = null" aria-label="Sluiten" class="w-8 h-8 rounded-xl flex items-center justify-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 text-sm cursor-pointer transition">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                Verkoop je voorraad om direct cash te ontvangen en opbergruimte vrij te maken in je Depot.
            </p>

            <template x-if="sellModalItem">
                <form :action="'{{ route('depot.sell-item', ':id') }}'.replace(':id', sellModalItem.id)" method="POST" class="space-y-4">
                    @csrf
                    <div class="p-3 rounded-xl bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70">
                        <div class="flex justify-between items-center text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                            <span x-text="sellModalItem.name"></span>
                            <span class="text-purple-600 dark:text-purple-400" x-text="'Max: ' + sellModalItem.qty + 'x'"></span>
                        </div>
                        <div class="text-[11px] text-slate-500 dark:text-slate-400">
                            Opbrengst per stuk: <strong class="text-emerald-600 dark:text-emerald-400" x-text="'€' + new Intl.NumberFormat('nl-NL').format(sellModalItem.unitPrice)"></strong>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Aantal stuks verkopen:</label>
                        <div class="flex items-center gap-2">
                            <input type="number" 
                                   name="quantity" 
                                   min="1" 
                                   :max="sellModalItem.qty" 
                                   x-model="sellQty"
                                   class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/90 text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 font-bold focus:outline-none focus:border-purple-500 dark:focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition">
                            <button type="button" 
                                    @click="sellQty = sellModalItem.qty"
                                    class="px-3 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 border border-slate-200/80 dark:border-slate-700 text-xs font-black uppercase shrink-0 transition-all duration-150 active:scale-95 cursor-pointer">
                                Alles
                            </button>
                        </div>
                    </div>

                    <div class="p-3 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200/80 dark:border-emerald-800/70 text-xs flex justify-between items-center font-black text-emerald-800 dark:text-emerald-300">
                        <span>Totale Cash Opbrengst:</span>
                        <span x-text="'€' + new Intl.NumberFormat('nl-NL').format(sellQty * sellModalItem.unitPrice)"></span>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2">
                        <button type="button" @click="sellModalItem = null" class="px-4 py-2 text-xs font-bold bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 border border-slate-200/80 dark:border-slate-700 rounded-xl transition-all duration-150 active:scale-95 cursor-pointer">
                            Annuleren
                        </button>
                        <button type="submit" class="px-5 py-2 text-xs font-black bg-emerald-600 hover:bg-emerald-700 dark:bg-emerald-600 dark:hover:bg-emerald-500 text-white rounded-xl transition-all duration-150 active:scale-95 shadow-xs cursor-pointer flex items-center gap-1.5">
                            <i class="fa-solid fa-coins"></i>
                            <span>Verkoop Bevestigen</span>
                        </button>
                    </div>
                </form>
            </template>
        </div>
    </div>

</div>
@endsection
