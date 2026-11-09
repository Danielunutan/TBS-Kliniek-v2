@extends('layouts.app')

@section('title', 'Bende Garage - TBS Kliniek')

@section('content')
@php
    $storedCount = $storedCount ?? ($storedVehicles?->count() ?? 0);
@endphp
<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-xs relative overflow-hidden">
        <div class="absolute -right-10 -bottom-10 w-44 h-44 bg-purple-500/10 rounded-full blur-2xl pointer-events-none"></div>

        <div>
            <div class="flex items-center gap-2 mb-1.5 flex-wrap">
                <a href="{{ route('gangs.index') }}" class="text-xs font-bold text-slate-500 hover:text-purple-600 transition flex items-center gap-1">
                    <i class="fa-solid fa-arrow-left"></i> Bendes
                </a>
                <span class="text-slate-300 dark:text-slate-700">•</span>
                <span class="text-xs font-extrabold uppercase tracking-wider text-purple-700 dark:text-purple-400 bg-purple-50 dark:bg-purple-950/60 px-2.5 py-0.5 rounded-full border border-purple-200 dark:border-purple-800">
                    [{{ $gang->tag }}] {{ $gang->name }}
                </span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white flex items-center gap-3">
                <i class="fa-solid fa-warehouse text-purple-600 dark:text-purple-400"></i>
                <span>Bende Garage & Wagenpark</span>
            </h1>
            <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 mt-1">
                Het gezamenlijke wagenpark van de bende. Stal je vluchtvoertuigen veilig in de bende garage en breid je stallingscapaciteit uit met Bende Panden!
            </p>
        </div>

        <div class="flex items-center gap-2 shrink-0 flex-wrap">
            <a href="{{ route('gangs.panden') }}" class="px-4 py-2.5 rounded-xl bg-purple-50 hover:bg-purple-100 dark:bg-purple-950/60 dark:hover:bg-purple-900/60 text-purple-700 dark:text-purple-300 border border-purple-200/80 dark:border-purple-800/70 text-xs font-black uppercase tracking-wider transition-all duration-150 active:scale-95 flex items-center gap-2 shadow-2xs">
                <i class="fa-solid fa-building"></i>
                <span>Bende Panden</span>
            </a>
            <a href="{{ route('garage.index') }}" class="px-4 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-700 dark:bg-purple-600 dark:hover:bg-purple-500 text-white text-xs font-black uppercase tracking-wider transition-all duration-150 active:scale-95 flex items-center gap-2 shadow-xs cursor-pointer">
                <i class="fa-solid fa-car"></i>
                <span>Smokkel Showroom</span>
            </a>
        </div>
    </div>

    <!-- Stats & Capacity Overview -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Bezetting -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-5 shadow-xs">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Garage Bezetting</span>
                <span class="w-8 h-8 rounded-xl bg-purple-100 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400 flex items-center justify-center text-xs">
                    <i class="fa-solid fa-warehouse"></i>
                </span>
            </div>
            <div class="flex items-baseline gap-2">
                <span class="text-2xl font-black text-slate-900 dark:text-white font-mono">{{ $storedCount }}</span>
                <span class="text-xs text-slate-500 dark:text-slate-400">/ {{ $totalVehicleSlots }} plekken</span>
            </div>
            <div class="mt-3 w-full bg-slate-100 dark:bg-slate-800 rounded-full h-2 overflow-hidden">
                @php $pct = $totalVehicleSlots > 0 ? min(100, round(($storedCount / $totalVehicleSlots) * 100)) : 0; @endphp
                <div class="h-full rounded-full transition-all duration-500 {{ $pct >= 90 ? 'bg-rose-500' : ($pct >= 70 ? 'bg-amber-500' : 'bg-purple-600') }}" style="width: {{ $pct }}%"></div>
            </div>
        </div>

        <!-- Vrije Plekken -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-5 shadow-xs">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Vrije Stallingen</span>
                <span class="w-8 h-8 rounded-xl bg-emerald-100 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-xs">
                    <i class="fa-solid fa-square-parking"></i>
                </span>
            </div>
            <div class="flex items-baseline gap-2">
                <span class="text-2xl font-black {{ $freeSlots > 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }} font-mono">{{ $freeSlots }}</span>
                <span class="text-xs text-slate-500 dark:text-slate-400">beschikbaar</span>
            </div>
            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-2">
                @if($freeSlots > 0)
                    Leden kunnen direct {{ $freeSlots }} wagen(s) stallen.
                @else
                    Garage is vol! Koop extra panden voor meer ruimte.
                @endif
            </p>
        </div>

        <!-- Garage Panden in Bezit -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-5 shadow-xs">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Gekoppelde Panden</span>
                <span class="w-8 h-8 rounded-xl bg-sky-100 dark:bg-sky-950/60 text-sky-600 dark:text-sky-400 flex items-center justify-center text-xs">
                    <i class="fa-solid fa-building-shield"></i>
                </span>
            </div>
            <div class="flex items-baseline gap-2">
                <span class="text-2xl font-black text-slate-900 dark:text-white font-mono">{{ $garageProperties->count() }}</span>
                <span class="text-xs text-slate-500 dark:text-slate-400">panden met garage</span>
            </div>
            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-2">
                Leveren samen {{ $totalVehicleSlots }} wagenstallingen op.
            </p>
        </div>

        <!-- Bendekas -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-5 shadow-xs">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Bendekas</span>
                <span class="w-8 h-8 rounded-xl bg-amber-100 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center text-xs">
                    <i class="fa-solid fa-coins"></i>
                </span>
            </div>
            <div class="flex items-baseline gap-2">
                <span class="text-lg sm:text-xl md:text-2xl font-black text-amber-600 dark:text-amber-400 font-mono truncate">€{{ number_format($gang->treasury, 0, ',', '.') }}</span>
            </div>
            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-2">
                Beschikbaar voor nieuwe panden & upgrades.
            </p>
        </div>
    </div>

    <!-- Alert / Info Notice -->
    @if($totalVehicleSlots === 0)
        <div class="p-4 rounded-2xl bg-amber-50 dark:bg-amber-950/40 border border-amber-300 dark:border-amber-700/60 flex items-start gap-3">
            <div class="w-9 h-9 rounded-xl bg-amber-500/20 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0 mt-0.5">
                <i class="fa-solid fa-triangle-exclamation text-base"></i>
            </div>
            <div class="flex-1">
                <h4 class="text-sm font-black text-amber-900 dark:text-amber-200">Jullie bende bezit nog geen Garage of Opslag Panden!</h4>
                <p class="text-xs text-amber-800 dark:text-amber-300/90 mt-0.5 leading-relaxed">
                    Voertuigen kunnen pas in de Bende Garage gestald worden zodra de bende minstens één garage- of opslagpand bezit (zoals een <em>Kleine Loods</em>, <em>Garageboxen Complex</em>, <em>Havenloods</em> of <em>Ondergrondse Bunker</em>).
                </p>
                <div class="mt-2.5">
                    <a href="{{ route('gangs.panden') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-amber-600 hover:bg-amber-700 text-white font-extrabold text-xs transition shadow-xs">
                        <i class="fa-solid fa-building"></i> Bekijk Bende Panden Showroom
                    </a>
                </div>
            </div>
        </div>
    @else
        <div class="p-3.5 rounded-xl bg-purple-50/70 dark:bg-purple-950/30 border border-purple-200/80 dark:border-purple-800/50 flex items-center justify-between gap-3 text-xs">
            <span class="flex items-center gap-2 text-purple-900 dark:text-purple-200">
                <i class="fa-solid fa-circle-info text-purple-600 dark:text-purple-400"></i>
                <span>Elk aangekocht garagepand breidt de stallingscapaciteit van jullie bende direct uit. Bendeleden kunnen gestalde wagens op elk moment terughalen.</span>
            </span>
            <a href="{{ route('gangs.panden') }}" class="text-purple-700 dark:text-purple-300 font-extrabold hover:underline whitespace-nowrap">
                Meer panden kopen →
            </a>
        </div>
    @endif

    <!-- SECTION 1: Gestalde Bendevoertuigen (Het Bende Wagenpark) -->
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-black text-slate-900 dark:text-white flex items-center gap-2">
                <i class="fa-solid fa-car-rear text-purple-600 dark:text-purple-400"></i>
                <span>Gestalde Bendevoertuigen ({{ $storedCount }} wagens)</span>
            </h2>
            <span class="text-xs text-slate-500 font-mono">{{ $freeSlots }} vrije stallingen over</span>
        </div>

        @if($storedVehicles->isEmpty())
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-10 text-center shadow-xs">
                <div class="w-16 h-16 rounded-2xl bg-purple-100 dark:bg-purple-950 text-purple-600 dark:text-purple-400 mx-auto flex items-center justify-center text-2xl mb-3">
                    <i class="fa-solid fa-warehouse"></i>
                </div>
                <h3 class="text-base font-black text-slate-900 dark:text-white">De Bende Garage is momenteel leeg</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 max-w-md mx-auto mt-1">
                    Er staan nog geen voertuigen gestald in de garage van {{ $gang->name }}. Bendeleden kunnen hieronder hun eigen vluchtwagens parkeren!
                </p>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($storedVehicles as $charVehicle)
                    @php 
                        $veh = $charVehicle->vehicle; 
                        $isOwner = $charVehicle->character_id === $character->id;
                    @endphp
                    <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-5 shadow-xs hover:border-purple-300 dark:hover:border-purple-700/80 transition-all duration-200 flex flex-col justify-between">
                        <div>
                            <!-- Header / Badge -->
                            <div class="flex items-center justify-between gap-2 mb-3">
                                @if($charVehicle->is_gang_owned)
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-amber-500/10 text-amber-700 dark:text-amber-300 border border-amber-300/80 dark:border-amber-700/70 flex items-center gap-1">
                                        <i class="fa-solid fa-shield-halved"></i> Bende Eigendom
                                    </span>
                                @else
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-purple-50 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 border border-purple-200/80 dark:border-purple-800/70">
                                        {{ $veh?->type ?? 'Voertuig' }} (Gestald)
                                    </span>
                                @endif
                                <span class="text-xs font-mono font-bold text-slate-500 dark:text-slate-400">
                                    <i class="fa-solid fa-gauge-high text-purple-500"></i> {{ $charVehicle->effective_speed }}% snelheid
                                </span>
                            </div>

                            <!-- Vehicle Icon & Name -->
                            <div class="flex items-center gap-3.5 mb-3">
                                <div class="w-12 h-12 rounded-xl bg-slate-100 dark:bg-slate-800 text-purple-600 dark:text-purple-400 flex items-center justify-center text-xl shrink-0 border border-slate-200/80 dark:border-slate-700">
                                    <i class="fa-solid {{ $veh?->icon ?? 'fa-car' }}"></i>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <h4 class="font-black text-slate-900 dark:text-white text-sm truncate">{{ $veh?->name ?? 'Voertuig' }}</h4>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 truncate">{{ $veh?->brand ?? 'Onderwereld Fleet' }}</p>
                                </div>
                            </div>

                            <!-- Condition Bar -->
                            <div class="space-y-1 mb-3">
                                <div class="flex justify-between text-[11px] font-bold">
                                    <span class="text-slate-500 dark:text-slate-400">Conditie:</span>
                                    <span class="{{ $charVehicle->current_condition >= 75 ? 'text-emerald-600 dark:text-emerald-400' : ($charVehicle->current_condition >= 40 ? 'text-amber-600 dark:text-amber-400' : 'text-rose-600 dark:text-rose-400') }}">
                                        {{ $charVehicle->current_condition }}%
                                    </span>
                                </div>
                                <div class="w-full bg-slate-100 dark:bg-slate-800 rounded-full h-1.5 overflow-hidden">
                                    <div class="h-full rounded-full {{ $charVehicle->current_condition >= 75 ? 'bg-emerald-500' : ($charVehicle->current_condition >= 40 ? 'bg-amber-500' : 'bg-rose-500') }}" style="width: {{ $charVehicle->current_condition }}%"></div>
                                </div>
                            </div>

                            <!-- Owner Info -->
                            <div class="p-2.5 rounded-xl bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70 mb-3 flex items-center justify-between text-xs">
                                <div class="flex items-center gap-2 min-w-0">
                                    @if($charVehicle->is_gang_owned)
                                        <div class="w-6 h-6 rounded-lg bg-amber-500/20 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0 border border-amber-300 dark:border-amber-700">
                                            <i class="fa-solid fa-crown text-[10px]"></i>
                                        </div>
                                        <span class="truncate">
                                            <span class="text-slate-400 dark:text-slate-500 text-[10px] block">Bende Vloot:</span>
                                            <span class="font-extrabold text-amber-700 dark:text-amber-300">
                                                [{{ $gang->tag }}] {{ $gang->name }}
                                            </span>
                                            <span class="text-[9px] text-slate-400 block truncate">
                                                Gedoneerd door {{ $charVehicle->donatedBy?->name ?? $charVehicle->character?->name ?? 'Lid' }}
                                            </span>
                                        </span>
                                    @else
                                        <img src="{{ $charVehicle->character?->avatar_url ?? asset('assets/characters/avatar_male_1.jpg') }}" alt="{{ $charVehicle->character?->name }}" class="w-6 h-6 rounded-lg object-cover shrink-0 border border-slate-200 dark:border-slate-700">
                                        <span class="truncate">
                                            <span class="text-slate-400 dark:text-slate-500 text-[10px] block">Eigenaar (Gestald):</span>
                                            <a href="{{ route('profile.show', $charVehicle->character?->name ?? 'speler') }}" class="font-extrabold text-slate-800 dark:text-slate-200 hover:text-purple-600 dark:hover:text-purple-400">
                                                {{ $charVehicle->character?->name ?? 'Onbekend' }}
                                            </a>
                                        </span>
                                    @endif
                                </div>
                                @if($charVehicle->donated_at)
                                    <span class="text-[10px] text-amber-600 dark:text-amber-400 font-mono">{{ $charVehicle->donated_at->diffForHumans() }}</span>
                                @elseif($charVehicle->stored_at)
                                    <span class="text-[10px] text-slate-400 dark:text-slate-500 font-mono">{{ $charVehicle->stored_at->diffForHumans() }}</span>
                                @endif
                            </div>
                        </div>

                        <!-- Actions: Repareren (Uitsluitend via Bendekas), Doneren & Terughalen -->
                        <div class="pt-2 border-t border-slate-100 dark:border-slate-800 space-y-2">
                            <!-- Reviseren uitsluitend via Bendekas -->
                            @if(($isOwner || $isOfficer) && $charVehicle->current_condition < 100)
                                @php
                                    $canAffordRepair = (int) ($gang->treasury ?? 0) >= (int) $charVehicle->repair_cost;
                                @endphp
                                <form action="{{ route('garage.repair', $charVehicle->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" 
                                            {{ $character->isImprisoned() || $character->isComatose() || $character->needsReviveSerum() || ! $canAffordRepair ? 'disabled' : '' }}
                                            class="w-full py-2 rounded-xl {{ $canAffordRepair ? 'bg-emerald-600 hover:bg-emerald-700 dark:bg-emerald-600 dark:hover:bg-emerald-500 cursor-pointer text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-slate-500 border border-slate-200/80 dark:border-slate-700/60 cursor-not-allowed font-bold' }} disabled:opacity-50 disabled:cursor-not-allowed font-black text-xs transition-all duration-150 active:scale-95 shadow-xs flex items-center justify-center gap-2">
                                        <i class="fa-solid fa-wrench text-[11px]"></i>
                                        <span>{{ $canAffordRepair ? 'Reviseren via Bendekas (€' . number_format($charVehicle->repair_cost, 0, ',', '.') . ')' : 'Te weinig in bendekas (€' . number_format($charVehicle->repair_cost, 0, ',', '.') . ')' }}</span>
                                    </button>
                                </form>
                            @endif

                            <!-- Doneren aan Bende (als nog niet gedoneerd en huidige gebruiker is eigenaar) -->
                            @if(! $charVehicle->is_gang_owned && $isOwner)
                                <form action="{{ route('gangs.garage.donate-vehicle', $charVehicle) }}" method="POST">
                                    @csrf
                                    <button type="submit" 
                                            data-confirm="Weet je zeker dat je {{ $veh?->name }} definitief wilt doneren aan [{{ $gang->tag }}]? Het voertuig wordt permanent bende-eigendom!"
                                            data-confirm-title="Voertuig Doneren aan Bende"
                                            data-confirm-btn="Ja, Permanent Doneren"
                                            data-confirm-type="warning"
                                            data-confirm-icon="fa-gift"
                                            {{ $character->isImprisoned() || $character->isComatose() || $character->needsReviveSerum() ? 'disabled' : '' }}
                                            class="w-full py-2 rounded-xl bg-amber-500/10 hover:bg-amber-500/20 text-amber-700 dark:text-amber-300 border border-amber-300/70 dark:border-amber-700/60 font-black text-xs transition-all duration-150 active:scale-95 shadow-xs cursor-pointer flex items-center justify-center gap-2">
                                        <i class="fa-solid fa-gift text-[11px]"></i>
                                        <span>Doneer Definitief aan Bende (+50 Respect)</span>
                                    </button>
                                </form>
                            @endif

                            <!-- Terughalen (uitsluitend als NIET gedoneerd aan bende) -->
                            @if(! $charVehicle->is_gang_owned && ($isOwner || $isOfficer))
                                <form action="{{ route('gangs.garage.retrieve-vehicle', $charVehicle) }}" method="POST">
                                    @csrf
                                    <button type="submit" 
                                            {{ $character->isImprisoned() || $character->isComatose() || $character->needsReviveSerum() ? 'disabled' : '' }}
                                            class="w-full py-2 rounded-xl bg-purple-600 hover:bg-purple-700 dark:bg-purple-600 dark:hover:bg-purple-500 disabled:opacity-50 disabled:cursor-not-allowed text-white font-black text-xs transition-all duration-150 active:scale-95 shadow-xs cursor-pointer flex items-center justify-center gap-2">
                                        <i class="fa-solid fa-key text-[11px]"></i>
                                        <span>Terughalen naar Eigen Garage</span>
                                    </button>
                                </form>
                            @elseif($charVehicle->is_gang_owned && $isOfficer)
                                <!-- Slopen voor schroot in bendekas (alleen voor bendebeheerders) -->
                                @php $scrapVal = (int) round(($veh?->price_cash ?? 20000) * 0.25); @endphp
                                <form action="{{ route('gangs.garage.scrap-vehicle', $charVehicle) }}" method="POST">
                                    @csrf
                                    <button type="submit" 
                                            data-confirm="Bendevoertuig {{ $veh?->name }} slopen voor €{{ number_format($scrapVal, 0, ',', '.') }} schrootwaarde in de bendekas?"
                                            data-confirm-title="Bendevoertuig Slopen"
                                            data-confirm-btn="Ja, Slopen"
                                            data-confirm-type="danger"
                                            data-confirm-icon="fa-trash-can"
                                            {{ $character->isImprisoned() || $character->isComatose() || $character->needsReviveSerum() ? 'disabled' : '' }}
                                            class="w-full py-2 rounded-xl bg-rose-500/10 hover:bg-rose-500/20 text-rose-700 dark:text-rose-300 border border-rose-300/70 dark:border-rose-800/60 font-black text-xs transition-all duration-150 active:scale-95 shadow-xs cursor-pointer flex items-center justify-center gap-2">
                                        <i class="fa-solid fa-trash-can text-[11px]"></i>
                                        <span>Slopen voor Bendekas (+€{{ number_format($scrapVal, 0, ',', '.') }})</span>
                                    </button>
                                </form>
                            @elseif(! $charVehicle->is_gang_owned)
                                <div class="text-center py-1 text-slate-400 dark:text-slate-500 text-[11px] font-bold italic">
                                    Alleen eigenaar of leiders kunnen dit voertuig beheren
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- SECTION 2: Plaats een Voertuig in de Bende Garage -->
    <div class="space-y-4 pt-4">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-lg font-black text-slate-900 dark:text-white flex items-center gap-2">
                    <i class="fa-solid fa-arrow-down text-emerald-600 dark:text-emerald-400"></i>
                    <span>Plaats een Voertuig in de Bende Garage</span>
                </h2>
                <p class="text-xs text-slate-500 dark:text-slate-400">Selecteer één van je eigen wagens om toe te voegen aan de gezamenlijke vloot.</p>
            </div>
        </div>

        @if($personalVehicles->isEmpty())
            <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-6 text-center shadow-xs">
                <p class="text-xs text-slate-500 dark:text-slate-400">
                    Je hebt momenteel geen vrije persoonlijke voertuigen die gestald kunnen worden.
                </p>
                <div class="mt-2.5">
                    <a href="{{ route('garage.index') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-purple-600 hover:bg-purple-700 dark:bg-purple-600 dark:hover:bg-purple-500 text-white text-xs font-black transition-all duration-150 active:scale-95 shadow-xs cursor-pointer">
                        <i class="fa-solid fa-car"></i> Koop een wagen in de Smokkel Showroom
                    </a>
                </div>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($personalVehicles as $myVehicle)
                    @php $v = $myVehicle->vehicle; @endphp
                    <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-5 shadow-xs hover:border-purple-300 dark:hover:border-purple-700/80 transition-all duration-200 flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between gap-2 mb-2">
                                <span class="px-2 py-0.5 rounded-md text-[10px] font-black uppercase tracking-wider bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200/80 dark:border-slate-700">
                                    {{ $v?->type ?? 'Auto' }}
                                </span>
                                <span class="text-xs font-mono font-bold text-slate-500 dark:text-slate-400">
                                    {{ $myVehicle->effective_speed }}% snelheid
                                </span>
                            </div>

                            <div class="flex items-center gap-3 mb-3">
                                <div class="w-10 h-10 rounded-xl bg-purple-50 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400 flex items-center justify-center text-lg shrink-0 border border-purple-200/80 dark:border-purple-800/70">
                                    <i class="fa-solid {{ $v?->icon ?? 'fa-car' }}"></i>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <h4 class="font-extrabold text-slate-900 dark:text-white text-sm truncate">{{ $v?->name ?? 'Voertuig' }}</h4>
                                    <p class="text-xs text-slate-400 dark:text-slate-500">Conditie: {{ $myVehicle->current_condition }}%</p>
                                </div>
                            </div>
                        </div>

                        <div class="pt-2 space-y-2">
                            <form action="{{ route('gangs.garage.store-vehicle', $myVehicle) }}" method="POST">
                                @csrf
                                <button type="submit" 
                                        @if($freeSlots <= 0 || $character->isImprisoned() || $character->isComatose() || $character->needsReviveSerum()) disabled @endif
                                        class="w-full py-2 rounded-xl {{ ($freeSlots > 0 && ! $character->isImprisoned() && ! $character->isComatose() && ! $character->needsReviveSerum()) ? 'bg-emerald-600 hover:bg-emerald-700 dark:bg-emerald-600 dark:hover:bg-emerald-500 text-white cursor-pointer active:scale-95' : 'bg-slate-100 dark:bg-slate-800/80 text-slate-400 dark:text-slate-500 border border-slate-200/80 dark:border-slate-700/60 cursor-not-allowed' }} font-black text-xs transition-all duration-150 shadow-xs flex items-center justify-center gap-2">
                                    <i class="fa-solid fa-warehouse text-xs"></i>
                                    <span>{{ ($freeSlots > 0 && ! $character->isImprisoned() && ! $character->isComatose() && ! $character->needsReviveSerum()) ? 'Stallen in Bende Garage' : ($freeSlots <= 0 ? 'Bende Garage Vol' : 'Niet Beschikbaar') }}</span>
                                </button>
                            </form>

                            <form action="{{ route('gangs.garage.donate-vehicle', $myVehicle) }}" method="POST">
                                @csrf
                                <button type="submit" 
                                        data-confirm="Weet je zeker dat je {{ $v?->name }} definitief wilt overdragen aan bende [{{ $gang->tag }}]? Het voertuig wordt permanent bende-eigendom!"
                                        data-confirm-title="Voertuig Doneren aan Bende"
                                        data-confirm-btn="Ja, Definitief Doneren"
                                        data-confirm-type="warning"
                                        data-confirm-icon="fa-gift"
                                        @if($freeSlots <= 0 || $character->isImprisoned() || $character->isComatose() || $character->needsReviveSerum()) disabled @endif
                                        class="w-full py-2 rounded-xl {{ ($freeSlots > 0 && ! $character->isImprisoned() && ! $character->isComatose() && ! $character->needsReviveSerum()) ? 'bg-amber-500/10 hover:bg-amber-500/20 text-amber-700 dark:text-amber-300 border border-amber-300/70 dark:border-amber-700/60 cursor-pointer active:scale-95' : 'bg-slate-100 dark:bg-slate-800/80 text-slate-400 dark:text-slate-500 border border-slate-200/80 dark:border-slate-700/60 cursor-not-allowed' }} font-black text-xs transition-all duration-150 shadow-xs flex items-center justify-center gap-2">
                                    <i class="fa-solid fa-gift text-xs"></i>
                                    <span>Doneer Direct als Bende-Eigendom (+50 Respect)</span>
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- SECTION 3: Gekoppelde Bende Panden met Voertuigplekken -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-6 shadow-xs space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h3 class="text-base font-black text-slate-900 dark:text-white flex items-center gap-2">
                    <i class="fa-solid fa-building text-purple-600 dark:text-purple-400"></i>
                    <span>Gekoppelde Garage & Opslag Panden ({{ $garageProperties->count() }})</span>
                </h3>
                <p class="text-xs text-slate-500 dark:text-slate-400">Deze panden in bendebezit leveren de gezamenlijke voertuigstallingen op.</p>
            </div>
            <a href="{{ route('gangs.panden') }}" class="px-3.5 py-1.5 rounded-xl bg-purple-600 hover:bg-purple-700 dark:bg-purple-600 dark:hover:bg-purple-500 text-white text-xs font-black uppercase tracking-wider transition-all duration-150 active:scale-95 shadow-xs flex items-center gap-1.5 shrink-0 cursor-pointer">
                <i class="fa-solid fa-plus"></i>
                <span>Meer Panden Kopen</span>
            </a>
        </div>

        @if($garageProperties->isEmpty())
            <p class="text-xs text-slate-400 dark:text-slate-500 italic">Jullie bende heeft nog geen garagepanden aangekocht.</p>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                @foreach($garageProperties as $prop)
                    <div class="p-3.5 rounded-xl bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70 flex items-center justify-between gap-3">
                        <div class="min-w-0">
                            <h5 class="font-extrabold text-slate-900 dark:text-white text-xs truncate">{{ $prop->name }}</h5>
                            <span class="text-[11px] text-purple-600 dark:text-purple-400 font-bold">+{{ $prop->vehicle_slots }} wagenplekken</span>
                        </div>
                        <span class="w-8 h-8 rounded-lg bg-purple-100 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400 flex items-center justify-center text-xs shrink-0 border border-purple-200/80 dark:border-purple-800/70">
                            <i class="fa-solid fa-car-side"></i>
                        </span>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

</div>
@endsection
