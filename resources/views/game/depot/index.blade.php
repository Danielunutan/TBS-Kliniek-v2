@extends('layouts.app')

@section('title', 'Depot & Centrale Opslag - TBS Kliniek')

@section('content')
<div class="space-y-6 pb-12" x-data="{ tab: '{{ $activeTab }}', sellModalItem: null, sellQty: 1 }">

    <!-- HERO HEADER -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-xs relative overflow-hidden">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 relative z-10">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-purple-600 text-white flex items-center justify-center text-2xl shadow-md shrink-0">
                    <i class="fa-solid fa-boxes-stacked"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h1 class="text-xl font-black text-slate-900 dark:text-white tracking-tight">Depot & Centrale Opslag</h1>
                        <span class="px-2.5 py-0.5 rounded-full bg-purple-100 dark:bg-purple-950/80 text-purple-700 dark:text-purple-300 font-black text-[10px] uppercase tracking-wider">
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
            <div class="flex items-center gap-2 shrink-0">
                <a href="{{ route('marketplace.index', ['type' => 'depot_item']) }}" 
                   class="px-4 py-2.5 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 hover:bg-indigo-100 dark:hover:bg-indigo-900/60 text-indigo-700 dark:text-indigo-300 border border-indigo-200/80 dark:border-indigo-800 font-black text-xs uppercase tracking-wider transition shadow-xs flex items-center gap-2">
                    <i class="fa-solid fa-shop text-xs text-indigo-500"></i>
                    <span>Marktplaats</span>
                </a>
                <a href="{{ route('shop.index') }}" 
                   class="px-4 py-2.5 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 font-black text-xs uppercase tracking-wider transition shadow-xs flex items-center gap-2">
                    <i class="fa-solid fa-store text-xs text-purple-500"></i>
                    <span>Kantine & Shop</span>
                </a>
                <a href="{{ route('garage.index') }}" 
                   class="px-4 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-black text-xs uppercase tracking-wider transition shadow-xs flex items-center gap-2">
                    <i class="fa-solid fa-warehouse text-xs"></i>
                    <span>Smokkel Garage</span>
                </a>
            </div>
        </div>

        <!-- CAPACITEIT METERS -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-6 pt-6 border-t border-slate-100 dark:border-slate-800">
            
            <!-- GARAGE CAPACITEIT -->
            <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200/80 dark:border-slate-700/60">
                <div class="flex items-center justify-between gap-2 mb-2">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-car text-purple-500 text-sm"></i>
                        <span class="text-xs font-bold text-slate-700 dark:text-slate-200">Garage Capaciteit</span>
                        @if($character->isVip())
                        <span class="px-1.5 py-0.5 rounded text-[9px] font-black uppercase tracking-wider bg-amber-100 dark:bg-amber-950/70 text-amber-700 dark:text-amber-300 border border-amber-300 dark:border-amber-700/60">
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
            <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200/80 dark:border-slate-700/60">
                <div class="flex items-center justify-between gap-2 mb-2">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-box-archive text-amber-500 text-sm"></i>
                        <span class="text-xs font-bold text-slate-700 dark:text-slate-200">Opbergruimte (Voorraad)</span>
                        @if($character->isVip())
                        <span class="px-1.5 py-0.5 rounded text-[9px] font-black uppercase tracking-wider bg-amber-100 dark:bg-amber-950/70 text-amber-700 dark:text-amber-300 border border-amber-300 dark:border-amber-700/60">
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
        <div class="flex items-center gap-2 pt-6 border-t border-slate-100 dark:border-slate-800 mt-6 overflow-x-auto no-scrollbar">
            <button type="button" @click="tab = 'garage'" 
                    :class="tab === 'garage' ? 'bg-purple-600 text-white shadow-xs font-black' : 'bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold'"
                    class="px-5 py-2.5 rounded-xl text-xs transition cursor-pointer flex items-center gap-2 shrink-0">
                <i class="fa-solid fa-car-side"></i>
                <span>1. Persoonlijke Garage ({{ $ownedVehicles->count() }}/{{ $garageCapacity }})</span>
            </button>
            <button type="button" @click="tab = 'storage'" 
                    :class="tab === 'storage' ? 'bg-purple-600 text-white shadow-xs font-black' : 'bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold'"
                    class="px-5 py-2.5 rounded-xl text-xs transition cursor-pointer flex items-center gap-2 shrink-0">
                <i class="fa-solid fa-box-open"></i>
                <span>2. Persoonlijke Opbergplaats ({{ $storageUsed }}/{{ $storageCapacity }})</span>
            </button>
            <button type="button" @click="tab = 'varia'" 
                    :class="tab === 'varia' ? 'bg-purple-600 text-white shadow-xs font-black' : 'bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold'"
                    class="px-5 py-2.5 rounded-xl text-xs transition cursor-pointer flex items-center gap-2 shrink-0">
                <i class="fa-solid fa-ghost text-amber-400"></i>
                <span>3. Persoonlijk Varia (Event Opslag)</span>
                <span class="px-1.5 py-0.5 rounded text-[9px] font-black uppercase tracking-wider bg-amber-400 text-slate-950">Event</span>
            </button>
        </div>
    </div>

    <!-- ==================== TAB 1: PERSOONLIJKE GARAGE ==================== -->
    <div x-show="tab === 'garage'" class="space-y-6">

        @if($garagePercent >= 100)
        <div class="p-4 rounded-2xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-900/60 text-rose-800 dark:text-rose-200 text-xs flex items-center gap-3">
            <i class="fa-solid fa-triangle-exclamation text-rose-500 text-base shrink-0"></i>
            <div>
                <strong>Je garage zit vol ({{ $garageUsed }}/{{ $garageCapacity }})!</strong>
                Je kunt momenteel geen nieuwe voertuigen meer kopen bij de Smokkel Garage. Verhoog je level of activeer VIP voor +3 extra garageplekken.
            </div>
        </div>
        @endif

        @if($ownedVehicles->isEmpty())
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-12 text-center shadow-xs">
            <div class="w-16 h-16 rounded-2xl bg-slate-100 dark:bg-slate-800 text-slate-400 flex items-center justify-center text-3xl mx-auto mb-4">
                <i class="fa-solid fa-warehouse"></i>
            </div>
            <h3 class="text-base font-black text-slate-900 dark:text-white">Nog Geen Voertuigen in je Garage</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 max-w-md mx-auto mt-1 leading-relaxed">
                Je hebt nog geen vluchtvoertuig aangeschaft. Schaf een scooter, vluchtauto, speedboot of helikopter aan bij de Smokkel Garage voor succesvolle Grote Heists!
            </p>
            <div class="mt-5">
                <a href="{{ route('garage.index') }}" 
                   class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-black text-xs uppercase tracking-wider transition shadow-sm">
                    <i class="fa-solid fa-car"></i>
                    <span>Bekijk Smokkel Garage Showroom</span>
                </a>
            </div>
        </div>
        @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($ownedVehicles as $charVehicle)
            @php $v = $charVehicle->vehicle; @endphp
            <div class="bg-white dark:bg-slate-900 border {{ $charVehicle->is_active ? 'border-purple-500 ring-2 ring-purple-500/20' : 'border-slate-200 dark:border-slate-800' }} rounded-2xl p-5 shadow-xs flex flex-col justify-between">
                
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
                        <span class="px-2 py-0.5 rounded-full bg-purple-100 dark:bg-purple-950/80 text-purple-700 dark:text-purple-300 font-black text-[10px] uppercase tracking-wider border border-purple-200 dark:border-purple-800/60 shrink-0">
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
                <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center gap-2">
                    @if(! $charVehicle->is_active)
                    <form action="{{ route('garage.set-active', $charVehicle->id) }}" method="POST" class="flex-1">
                        @csrf
                        <button type="submit" class="w-full py-2 px-3 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-extrabold text-xs transition shadow-2xs cursor-pointer flex items-center justify-center gap-1.5">
                            <i class="fa-solid fa-check"></i>
                            <span>Als Vluchtwagen</span>
                        </button>
                    </form>
                    @endif

                    @if($charVehicle->current_condition < 100)
                    <form action="{{ route('garage.repair', $charVehicle->id) }}" method="POST" class="{{ $charVehicle->is_active ? 'w-full' : 'shrink-0' }}">
                        @csrf
                        <button type="submit" class="w-full py-2 px-3 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-200 font-extrabold text-xs transition cursor-pointer flex items-center justify-center gap-1.5">
                            <i class="fa-solid fa-wrench text-amber-500"></i>
                            <span>Repareer (€{{ number_format($charVehicle->repair_cost, 0, ',', '.') }})</span>
                        </button>
                    </form>
                    @endif
                </div>

            </div>
            @endforeach
        </div>
        @endif

    </div>

    <!-- ==================== TAB 2: PERSOONLIJKE OPBERGPLAATS ==================== -->
    <div x-show="tab === 'storage'" class="space-y-6">

        @if($storagePercent >= 100)
        <div class="p-4 rounded-2xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-900/60 text-rose-800 dark:text-rose-200 text-xs flex items-center gap-3">
            <i class="fa-solid fa-triangle-exclamation text-rose-500 text-base shrink-0"></i>
            <div>
                <strong>Je opbergplaats zit vol ({{ $storageUsed }}/{{ $storageCapacity }})!</strong>
                Verkoop spullen voor cash of consumeer herstel-items om weer ruimte vrij te maken voor diefstallen, pakketjes en drops.
            </div>
        </div>
        @endif

        <!-- OVERVAL MASKERS KAART -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-xs flex items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-purple-100 dark:bg-purple-950/80 text-purple-600 dark:text-purple-400 flex items-center justify-center text-xl shrink-0">
                    <i class="fa-solid fa-masks-theater"></i>
                </div>
                <div>
                    <h3 class="text-sm font-black text-slate-900 dark:text-white">Overval Maskers (Heist Gear)</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                        Noodzakelijk voor de bemanning tijdens Grote Heists en kraakoperaties.
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-3 shrink-0">
                <span class="text-base font-black text-purple-600 dark:text-purple-400">
                    {{ (int)($character->masks ?? 0) }} Stuks
                </span>
                <a href="{{ route('heists.index') }}" class="px-3.5 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 font-extrabold text-xs transition">
                    Naar Heists
                </a>
            </div>
        </div>

        @if($inventoryItems->isEmpty())
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-12 text-center shadow-xs">
            <div class="w-16 h-16 rounded-2xl bg-slate-100 dark:bg-slate-800 text-slate-400 flex items-center justify-center text-3xl mx-auto mb-4">
                <i class="fa-solid fa-box-open"></i>
            </div>
            <h3 class="text-base font-black text-slate-900 dark:text-white">Je Opbergplaats is Leeg</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 max-w-md mx-auto mt-1 leading-relaxed">
                Je hebt momenteel geen wapens, sigaretten, pillen of telefoons op voorraad. Koop voorraden in de Kantine of smokkel items binnen via droppings!
            </p>
            <div class="mt-5">
                <a href="{{ route('shop.index') }}" 
                   class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-black text-xs uppercase tracking-wider transition shadow-sm">
                    <i class="fa-solid fa-store"></i>
                    <span>Bezoek de Kantine & Winkel</span>
                </a>
            </div>
        </div>
        @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
            @foreach($inventoryItems as $itemRow)
            @php $it = $itemRow->item; @endphp
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-xs flex flex-col justify-between">
                
                <div>
                    <!-- ITEM HEADER -->
                    <div class="flex items-start justify-between gap-2 mb-3">
                        <div class="flex items-center gap-3">
                            <div class="w-11 h-11 rounded-xl bg-slate-100 dark:bg-slate-800 text-purple-600 dark:text-purple-400 flex items-center justify-center text-lg shrink-0">
                                <i class="fa-solid {{ $it->icon ?? 'fa-cube' }}"></i>
                            </div>
                            <div>
                                <h4 class="text-xs font-black text-slate-900 dark:text-white leading-snug">{{ $it->name }}</h4>
                                <span class="text-[10px] uppercase font-bold text-slate-400">{{ $it->category }}</span>
                            </div>
                        </div>
                        <span class="px-2 py-0.5 rounded-md bg-purple-50 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 font-black text-[11px] shrink-0">
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
                    </div>
                </div>

                <!-- ACTIONS -->
                <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center gap-2">
                    @if($it->category !== 'equipment')
                    <form action="{{ route('shop.consume', $itemRow->id) }}" method="POST" class="flex-1">
                        @csrf
                        <input type="hidden" name="quantity" value="1">
                        <button type="submit" class="w-full py-2 px-2.5 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-extrabold text-[11px] transition shadow-2xs cursor-pointer flex items-center justify-center gap-1">
                            <i class="fa-solid fa-bolt"></i>
                            <span>Gebruiken</span>
                        </button>
                    </form>
                    @endif

                    <!-- SELL BUTTON -->
                    <button type="button" 
                            @click="sellModalItem = { id: {{ $itemRow->id }}, name: '{{ addslashes($it->name) }}', qty: {{ $itemRow->quantity }}, unitPrice: {{ max(500, (int)round(($it->price_kc > 0 ? $it->price_kc : 1000) * 0.6)) }} }; sellQty = 1;"
                            class="py-2 px-3 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-extrabold text-[11px] transition cursor-pointer flex items-center justify-center gap-1 {{ $it->category === 'equipment' ? 'w-full' : '' }}">
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

        <!-- INTRO BANNER -->
        <div class="p-6 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs flex flex-col md:flex-row items-center justify-between gap-6">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-amber-500 text-slate-950 flex items-center justify-center text-3xl shadow-sm shrink-0">
                    <i class="fa-solid fa-pumpkin"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h3 class="text-base font-black text-slate-900 dark:text-white">Persoonlijk Varia & Event Kluis</h3>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-emerald-100 dark:bg-emerald-950/80 text-emerald-800 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-700/60">
                            Onbeperkte Capaciteit ♾️
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 max-w-xl leading-relaxed">
                        In tegenstelling tot je Garage en Opbergplaats heeft Varia <strong>geen maximum limiet</strong>.
                        Alle speciale feestdag-drops, seizoensmunten en zeldzame verzamelobjecten worden hier veilig opgeslagen zodat je nooit iets misloopt tijdens events!
                    </p>
                </div>
            </div>

            <div class="shrink-0 text-center md:text-right">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Status</span>
                <span class="text-sm font-black text-emerald-600 dark:text-emerald-400 flex items-center gap-1.5 justify-center md:justify-end">
                    <i class="fa-solid fa-circle-check"></i> Gereed voor Events
                </span>
            </div>
        </div>

        <!-- HALLOWEEN UPDATE TEASER & PREVIEW SLOTS -->
        <div class="bg-white dark:bg-slate-900 border border-amber-500/30 rounded-2xl p-6 shadow-xs">
            <div class="flex items-center justify-between gap-4 mb-4">
                <div class="flex items-center gap-2.5">
                    <i class="fa-solid fa-ghost text-amber-500 text-lg"></i>
                    <h4 class="text-sm font-black text-slate-900 dark:text-white">Aankomend: Halloween 2026 Griezelweek in TBS Kliniek</h4>
                </div>
                <span class="text-[10px] font-black uppercase tracking-wider px-2.5 py-0.5 rounded-full bg-amber-100 dark:bg-amber-950/80 text-amber-800 dark:text-amber-300 border border-amber-300 dark:border-amber-700/60">
                    Binnenkort Actief
                </span>
            </div>

            <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed mb-6">
                Tijdens het Halloween Event dwalen er mysterieuze geesten over de luchtplaats en verschijnen er geheime pompoenkratten bij de expeditiepoort.
                Verzamel Pompoen Munten en wissel ze in voor unieke heists en trofeeën!
            </p>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- SLOT 1 -->
                <div class="p-4 rounded-xl border border-dashed border-slate-300 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/40 text-center">
                    <div class="w-12 h-12 rounded-xl bg-amber-500/10 text-amber-500 flex items-center justify-center text-xl mx-auto mb-2">
                        <i class="fa-solid fa-coins"></i>
                    </div>
                    <h5 class="text-xs font-black text-slate-900 dark:text-white">Halloween Pompoen Munten</h5>
                    <p class="text-[11px] text-slate-400 mt-0.5">Drop kans tijdens klusjes & heists</p>
                    <span class="inline-block mt-3 px-2 py-0.5 rounded text-[10px] font-bold bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-300">
                        0 in bezit
                    </span>
                </div>

                <!-- SLOT 2 -->
                <div class="p-4 rounded-xl border border-dashed border-slate-300 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/40 text-center">
                    <div class="w-12 h-12 rounded-xl bg-purple-500/10 text-purple-400 flex items-center justify-center text-xl mx-auto mb-2">
                        <i class="fa-solid fa-key"></i>
                    </div>
                    <h5 class="text-xs font-black text-slate-900 dark:text-white">Spookhuis Cryptesleutel</h5>
                    <p class="text-[11px] text-slate-400 mt-0.5">Opent de geheime kelderkluis</p>
                    <span class="inline-block mt-3 px-2 py-0.5 rounded text-[10px] font-bold bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-300">
                        0 in bezit
                    </span>
                </div>

                <!-- SLOT 3 -->
                <div class="p-4 rounded-xl border border-dashed border-slate-300 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/40 text-center">
                    <div class="w-12 h-12 rounded-xl bg-rose-500/10 text-rose-400 flex items-center justify-center text-xl mx-auto mb-2">
                        <i class="fa-solid fa-skull"></i>
                    </div>
                    <h5 class="text-xs font-black text-slate-900 dark:text-white">Zeldzaam Geestenrelikwie</h5>
                    <p class="text-[11px] text-slate-400 mt-0.5">Geeft tijdelijke stat boosters</p>
                    <span class="inline-block mt-3 px-2 py-0.5 rounded text-[10px] font-bold bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-300">
                        0 in bezit
                    </span>
                </div>
            </div>
        </div>

    </div>

    <!-- SELL CONFIRMATION MODAL -->
    <div x-show="sellModalItem" 
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-xs"
         @click.self="sellModalItem = null">
        
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 max-w-sm w-full p-6 shadow-xl space-y-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-coins text-amber-500"></i>
                    <h4 class="text-sm font-black text-slate-900 dark:text-white">Item Verkopen</h4>
                </div>
                <button type="button" @click="sellModalItem = null" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 text-sm">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                Verkoop je voorraad om direct cash te ontvangen en opbergruimte vrij te maken in je Depot.
            </p>

            <template x-if="sellModalItem">
                <form :action="'{{ url('/depot/verkopen') }}/' + sellModalItem.id" method="POST" class="space-y-4">
                    @csrf
                    <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700">
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
                                   class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white font-bold focus:ring-2 focus:ring-purple-500">
                            <button type="button" 
                                    @click="sellQty = sellModalItem.qty"
                                    class="px-3 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 text-slate-700 dark:text-slate-300 text-xs font-black uppercase shrink-0">
                                Alles
                            </button>
                        </div>
                    </div>

                    <div class="p-3 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-900/60 text-xs flex justify-between items-center font-black text-emerald-800 dark:text-emerald-300">
                        <span>Totale Cash Opbrengst:</span>
                        <span x-text="'€' + new Intl.NumberFormat('nl-NL').format(sellQty * sellModalItem.unitPrice)"></span>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2">
                        <button type="button" @click="sellModalItem = null" class="px-4 py-2 text-xs font-bold text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition">
                            Annuleren
                        </button>
                        <button type="submit" class="px-5 py-2 text-xs font-black bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl transition shadow-xs cursor-pointer flex items-center gap-1.5">
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
