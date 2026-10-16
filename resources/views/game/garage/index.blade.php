@extends('layouts.app')

@section('title', 'Smokkel Garage & Hangar - Vluchtvoertuigen')
@section('meta_description', 'Koop en beheer je vluchtwagens, speedboten en helikopters in de Smokkel Garage. Verhoog je snelheid en conditie voor Grote Heists.')

@section('content')
<div class="space-y-6" x-data="{ tab: 'showroom' }">

    <!-- HERO HEADER -->
    <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-xs relative overflow-hidden">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 relative z-10">
            <div class="flex items-center gap-3.5">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-purple-600 to-indigo-700 text-white flex items-center justify-center text-xl shadow-md shrink-0">
                    <i class="fa-solid fa-warehouse"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h2 class="text-lg font-black text-slate-900 tracking-tight">Smokkel Garage & Hangar</h2>
                        <span class="px-2 py-0.5 rounded-full bg-purple-100 text-purple-700 font-black text-[10px] uppercase tracking-wider">Nieuw</span>
                    </div>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Gerund door corrupte monteurs bij de expeditiepoort. Schaf vluchtwagens, boten en helikopters aan voor Grote Heists.
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2 shrink-0">
                <a href="{{ route('heists.index') }}" 
                   class="px-4 py-2.5 rounded-xl bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white font-black text-xs uppercase tracking-wider transition shadow-xs flex items-center gap-2 active:scale-95">
                    <i class="fa-solid fa-mask text-xs"></i>
                    <span>Naar Grote Heists</span>
                </a>
            </div>
        </div>

        <!-- QUICK TAB TOGGLE -->
        <div class="flex items-center gap-2 pt-6 border-t border-slate-100 mt-6 overflow-x-auto no-scrollbar">
            <button type="button" @click="tab = 'showroom'" 
                    :class="tab === 'showroom' ? 'bg-purple-600 text-white shadow-xs font-black' : 'bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold'"
                    class="px-4 py-2 rounded-xl text-xs transition cursor-pointer flex items-center gap-2 shrink-0">
                <i class="fa-solid fa-car-side"></i>
                <span>Showroom & Kopen ({{ count($showroomVehicles) }})</span>
            </button>
            <button type="button" @click="tab = 'garage'" 
                    :class="tab === 'garage' ? 'bg-purple-600 text-white shadow-xs font-black' : 'bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold'"
                    class="px-4 py-2 rounded-xl text-xs transition cursor-pointer flex items-center gap-2 shrink-0">
                <i class="fa-solid fa-key"></i>
                <span>Mijn Wagenpark ({{ count($ownedVehicles) }})</span>
            </button>
        </div>
    </div>

    <!-- ACTIVE GETAWAY VEHICLE STATUS (IF OWNED) -->
    @if($activeVehicle)
    <div class="bg-gradient-to-r from-slate-900 via-purple-950 to-slate-900 rounded-2xl p-6 text-white shadow-md border border-purple-800/40 relative overflow-hidden">
        <div class="absolute -right-8 -bottom-8 opacity-10 text-9xl text-white pointer-events-none">
            <i class="fa-solid {{ $activeVehicle->vehicle->icon }}"></i>
        </div>

        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 relative z-10">
            <div class="flex items-start gap-4">
                <div class="w-14 h-14 rounded-2xl bg-purple-600/30 border border-purple-400/30 text-purple-300 flex items-center justify-center text-2xl shrink-0 backdrop-blur-md shadow-inner">
                    <i class="fa-solid {{ $activeVehicle->vehicle->icon }}"></i>
                </div>
                <div class="space-y-1">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="px-2 py-0.5 rounded-full bg-emerald-500/20 border border-emerald-400/40 text-emerald-300 text-[10px] font-black uppercase tracking-wider flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span> Actief Vluchtvoertuig
                        </span>
                        <span class="text-xs text-purple-300/80 font-bold">{{ $activeVehicle->vehicle->type_label }} &bull; {{ $activeVehicle->vehicle->brand }}</span>
                    </div>
                    <h3 class="text-lg font-black text-white tracking-tight">{{ $activeVehicle->vehicle->name }}</h3>
                    <p class="text-xs text-purple-200/80 max-w-xl">{{ $activeVehicle->vehicle->description }}</p>
                </div>
            </div>

            <!-- GAUGE METRICS & REPAIR BUTTON -->
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-4 shrink-0">
                <!-- Snelheid Meter -->
                <div class="bg-slate-950/60 border border-purple-400/20 rounded-xl p-3.5 min-w-[140px] text-center space-y-1 backdrop-blur-sm">
                    <div class="text-[10px] uppercase font-bold tracking-wider text-purple-300 flex items-center justify-center gap-1">
                        <i class="fa-solid fa-gauge-high text-amber-400"></i> Snelheid
                    </div>
                    <div class="text-xl font-black text-amber-300 font-mono">{{ $activeVehicle->effective_speed }}%</div>
                    <div class="w-full bg-slate-800 rounded-full h-1.5 overflow-hidden">
                        <div class="bg-amber-400 h-full rounded-full" style="width: {{ $activeVehicle->effective_speed }}%"></div>
                    </div>
                </div>

                <!-- Conditie Meter -->
                <div class="bg-slate-950/60 border border-purple-400/20 rounded-xl p-3.5 min-w-[140px] text-center space-y-1 backdrop-blur-sm">
                    <div class="text-[10px] uppercase font-bold tracking-wider text-purple-300 flex items-center justify-center gap-1">
                        <i class="fa-solid fa-wrench text-emerald-400"></i> Conditie
                    </div>
                    <div class="text-xl font-black {{ $activeVehicle->current_condition < 50 ? 'text-rose-400' : 'text-emerald-300' }} font-mono">
                        {{ $activeVehicle->current_condition }}%
                    </div>
                    <div class="w-full bg-slate-800 rounded-full h-1.5 overflow-hidden">
                        <div class="{{ $activeVehicle->current_condition < 50 ? 'bg-rose-500' : 'bg-emerald-400' }} h-full rounded-full" style="width: {{ $activeVehicle->current_condition }}%"></div>
                    </div>
                </div>

                <!-- Reparatie Actie -->
                @if($activeVehicle->current_condition < 100)
                <form action="{{ route('garage.repair', $activeVehicle->id) }}" method="POST" class="shrink-0 flex items-center">
                    @csrf
                    <button type="submit" 
                            class="w-full sm:w-auto px-4 py-3 rounded-xl bg-emerald-600 hover:bg-emerald-500 active:scale-95 text-white font-black text-xs uppercase tracking-wider transition shadow-md flex items-center justify-center gap-1.5 cursor-pointer">
                        <i class="fa-solid fa-screwdriver-wrench"></i>
                        <span>Reviseren (€{{ number_format($activeVehicle->repair_cost, 0, ',', '.') }})</span>
                    </button>
                </form>
                @endif
            </div>
        </div>
    </div>
    @endif

    <!-- TAB 1: SHOWROOM CATALOGUS -->
    <div x-show="tab === 'showroom'" class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach($showroomVehicles as $veh)
            @php
                $isOwned = $ownedVehicles->firstWhere('vehicle_id', $veh->id);
            @endphp
            <div class="bg-white border {{ $isOwned ? 'border-purple-300 ring-2 ring-purple-400/20' : 'border-slate-200' }} hover:border-purple-400 rounded-2xl p-5 shadow-xs flex flex-col justify-between gap-4 transition group">
                <div class="space-y-3">
                    <div class="flex items-start justify-between gap-2">
                        <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-700 flex items-center justify-center text-xl shrink-0 group-hover:scale-110 transition-transform shadow-2xs">
                            <i class="fa-solid {{ $veh->icon }}"></i>
                        </div>
                        <div class="text-right">
                            <span class="px-2 py-0.5 rounded-full bg-slate-100 text-slate-700 text-[10px] font-black uppercase tracking-wider">
                                Lvl {{ $veh->min_level }}+
                            </span>
                            <span class="block text-[11px] text-slate-400 font-bold mt-0.5">{{ $veh->type_label }}</span>
                        </div>
                    </div>

                    <div>
                        <h4 class="text-sm font-black text-slate-900">{{ $veh->name }}</h4>
                        <p class="text-[11px] text-slate-500 line-clamp-2 mt-0.5 leading-relaxed">{{ $veh->description }}</p>
                    </div>

                    <!-- Specs: Snelheid & Betrouwbaarheid -->
                    <div class="space-y-2 pt-2 border-t border-slate-100">
                        <div class="space-y-1">
                            <div class="flex items-center justify-between text-[11px] font-bold">
                                <span class="text-slate-500 flex items-center gap-1"><i class="fa-solid fa-gauge text-amber-500 text-[10px]"></i> Snelheid</span>
                                <span class="text-slate-900 font-mono">{{ $veh->speed_pct }}%</span>
                            </div>
                            <div class="w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
                                <div class="bg-amber-500 h-full rounded-full" style="width: {{ $veh->speed_pct }}%"></div>
                            </div>
                        </div>

                        <div class="space-y-1">
                            <div class="flex items-center justify-between text-[11px] font-bold">
                                <span class="text-slate-500 flex items-center gap-1"><i class="fa-solid fa-shield text-emerald-500 text-[10px]"></i> Betrouwbaarheid</span>
                                <span class="text-slate-900 font-mono">{{ $veh->reliability_pct }}%</span>
                            </div>
                            <div class="w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
                                <div class="bg-emerald-500 h-full rounded-full" style="width: {{ $veh->reliability_pct }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Prijs & Aankoop Knop -->
                <div class="pt-3 border-t border-slate-100 space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-slate-400 font-medium">Prijs:</span>
                        <span class="text-xs font-black text-emerald-700 font-mono">{{ $veh->formatted_price }}</span>
                    </div>

                    @if($isOwned)
                        <div class="w-full py-2.5 rounded-xl bg-purple-50 text-purple-700 font-black text-xs text-center border border-purple-200">
                            <i class="fa-solid fa-check mr-1"></i> In Bezit
                        </div>
                    @else
                        @if($character->level < $veh->min_level)
                            <button type="button" disabled class="w-full py-2.5 rounded-xl bg-slate-100 text-slate-400 font-bold text-xs cursor-not-allowed text-center">
                                Vereist Level {{ $veh->min_level }}
                            </button>
                        @else
                            <form action="{{ route('garage.buy', $veh->id) }}" method="POST">
                                @csrf
                                <button type="submit" 
                                        class="w-full py-2.5 rounded-xl bg-purple-600 hover:bg-purple-700 active:scale-95 text-white font-black text-xs uppercase tracking-wider transition shadow-xs cursor-pointer flex items-center justify-center gap-1.5">
                                    <i class="fa-solid fa-cart-shopping text-xs"></i>
                                    <span>Voertuig Aanschaffen</span>
                                </button>
                            </form>
                        @endif
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- TAB 2: MIJN WAGENPARK -->
    <div x-show="tab === 'garage'" class="space-y-4">
        @if($ownedVehicles->isEmpty())
            <div class="bg-white border border-slate-200 rounded-2xl p-12 text-center space-y-3">
                <div class="w-16 h-16 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center text-2xl mx-auto shadow-xs">
                    <i class="fa-solid fa-warehouse"></i>
                </div>
                <h4 class="text-sm font-black text-slate-900">Je garage is momenteel nog leeg</h4>
                <p class="text-xs text-slate-500 max-w-sm mx-auto">
                    Koop een vluchtwagen in de showroom om Grote Heists te kunnen starten en met volle snelheid de politie af te schudden!
                </p>
                <button type="button" @click="tab = 'showroom'" class="px-4 py-2 rounded-xl bg-purple-600 text-white font-bold text-xs cursor-pointer">
                    Showroom Bekijken
                </button>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($ownedVehicles as $ov)
                <div class="bg-white border {{ $ov->is_active ? 'border-emerald-400 ring-2 ring-emerald-400/20' : 'border-slate-200' }} rounded-2xl p-5 shadow-xs flex flex-col justify-between gap-4">
                    <div class="space-y-3">
                        <div class="flex items-start justify-between gap-2">
                            <div class="w-12 h-12 rounded-xl bg-purple-100 text-purple-700 flex items-center justify-center text-xl shrink-0">
                                <i class="fa-solid {{ $ov->vehicle->icon }}"></i>
                            </div>
                            <div>
                                @if($ov->is_active)
                                    <span class="px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 text-[10px] font-black uppercase tracking-wider">
                                        Actief Vluchtvoertuig
                                    </span>
                                @else
                                    <form action="{{ route('garage.set-active', $ov->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-[10px] transition cursor-pointer">
                                            Kies als Actief
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>

                        <div>
                            <h4 class="text-sm font-black text-slate-900">{{ $ov->vehicle->name }}</h4>
                            <p class="text-xs text-slate-500 mt-0.5">{{ $ov->vehicle->brand }} &bull; {{ $ov->total_heists_done }} heists voltooid</p>
                        </div>

                        <!-- Conditie & Snelheid -->
                        <div class="space-y-2 pt-2 border-t border-slate-100">
                            <div class="space-y-1">
                                <div class="flex items-center justify-between text-[11px] font-bold">
                                    <span class="text-slate-500">Conditie (Slijtage)</span>
                                    <span class="{{ $ov->current_condition < 50 ? 'text-rose-600' : 'text-emerald-700' }} font-mono font-black">{{ $ov->current_condition }}%</span>
                                </div>
                                <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden">
                                    <div class="{{ $ov->current_condition < 50 ? 'bg-rose-500' : 'bg-emerald-500' }} h-full rounded-full transition-all" style="width: {{ $ov->current_condition }}%"></div>
                                </div>
                            </div>

                            <div class="flex items-center justify-between text-xs pt-1 text-slate-600">
                                <span class="font-bold">Huidige Topsnelheid:</span>
                                <span class="font-mono font-black text-slate-900">{{ $ov->effective_speed }}%</span>
                            </div>
                        </div>
                    </div>

                    <!-- Reparatie Optie -->
                    <div class="pt-3 border-t border-slate-100">
                        @if($ov->current_condition < 100)
                            <form action="{{ route('garage.repair', $ov->id) }}" method="POST">
                                @csrf
                                <button type="submit" 
                                        class="w-full py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-black text-xs uppercase tracking-wider transition shadow-xs flex items-center justify-center gap-1.5 cursor-pointer active:scale-95">
                                    <i class="fa-solid fa-wrench text-xs"></i>
                                    <span>Reviseren (€{{ number_format($ov->repair_cost, 0, ',', '.') }})</span>
                                </button>
                            </form>
                        @else
                            <div class="text-center py-2 text-xs font-bold text-emerald-700 flex items-center justify-center gap-1">
                                <i class="fa-solid fa-circle-check"></i> In Topconditie (100%)
                            </div>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        @endif
    </div>

</div>
@endsection
