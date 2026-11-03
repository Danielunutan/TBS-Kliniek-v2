@extends('layouts.app')

@section('title', '🛒 Marktplaats & Ruilhandel - TBS Kliniek')

@section('content')
<div class="space-y-6" x-data="{ activeTab: 'alle', createModalOpen: false, selectedType: 'depot_item' }">

    <!-- Clean TBS Kliniek Header Banner -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl p-6 sm:p-8 text-slate-800 dark:text-slate-100 relative overflow-hidden shadow-xs border border-slate-200/90 dark:border-slate-800">
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <div class="inline-flex items-center gap-2 bg-purple-50 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 text-xs font-black px-3 py-1 rounded-full border border-purple-200/80 dark:border-purple-800/70 mb-3">
                    <i class="fa-solid fa-shop"></i> Speler-tot-Speler Handelsplatform
                </div>
                <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-slate-900 dark:text-white">De Kliniek Marktplaats</h1>
                <p class="text-slate-500 dark:text-slate-400 text-xs sm:text-sm mt-1 max-w-2xl leading-relaxed">
                    Verkoop en koop bezittingen tussen patiënten onderling: verhandel <strong>Depot Items</strong>, complete <strong>Garage Voertuigen</strong>, zeldzame <strong>Bende Panden</strong>, <strong>Sterren</strong>, <strong>Kluiscodes</strong>, <strong>Maskers</strong> en <strong>Smokkeltassen</strong>!
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-3 shrink-0">
                <a href="{{ route('depot.index') }}" class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 border border-slate-200/80 dark:border-slate-700 font-bold text-xs flex items-center gap-2 transition-all duration-150 active:scale-95 cursor-pointer">
                    <i class="fa-solid fa-boxes-stacked text-purple-500"></i>
                    <span>Mijn Depot</span>
                </a>
                <a href="{{ route('garage.index') }}" class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 border border-slate-200/80 dark:border-slate-700 font-bold text-xs flex items-center gap-2 transition-all duration-150 active:scale-95 cursor-pointer">
                    <i class="fa-solid fa-warehouse text-purple-500"></i>
                    <span>Mijn Garage</span>
                </a>
                <button @click="createModalOpen = true" @disabled($character->isImprisoned() || $character->isComatose()) class="px-5 py-3 rounded-xl bg-purple-600 hover:bg-purple-700 dark:bg-purple-600 dark:hover:bg-purple-500 text-white font-black text-xs flex items-center gap-2 transition-all duration-150 active:scale-95 shadow-xs cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">
                    <i class="fa-solid fa-plus-circle text-sm text-purple-200"></i>
                    <span>Aanbod Plaatsen</span>
                </button>
            </div>
        </div>

        <div class="absolute -right-6 -bottom-6 text-slate-100 dark:text-slate-800/40 text-9xl font-black select-none pointer-events-none">
            <i class="fa-solid fa-scale-balanced"></i>
        </div>
    </div>

    <!-- Inventory Quick Overview (What can you sell?) -->
    <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-8 gap-3">
        <!-- Depot Items -->
        <a href="{{ route('depot.index') }}" class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-xl p-3 shadow-xs flex items-center gap-2.5 hover:border-purple-300 dark:hover:border-purple-700/80 transition-all duration-200">
            <div class="w-9 h-9 rounded-lg bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold text-sm shrink-0">
                <i class="fa-solid fa-box-archive"></i>
            </div>
            <div class="min-w-0">
                <span class="text-[9px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wide block truncate">Depot</span>
                <span class="text-xs font-black text-indigo-700 dark:text-indigo-400">{{ $inventoryItems->count() }} types</span>
            </div>
        </a>

        <!-- Garage Voertuigen -->
        <a href="{{ route('garage.index') }}" class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-xl p-3 shadow-xs flex items-center gap-2.5 hover:border-purple-300 dark:hover:border-purple-700/80 transition-all duration-200">
            <div class="w-9 h-9 rounded-lg bg-purple-50 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400 flex items-center justify-center font-bold text-sm shrink-0">
                <i class="fa-solid fa-car-side"></i>
            </div>
            <div class="min-w-0">
                <span class="text-[9px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wide block truncate">Garage</span>
                <span class="text-xs font-black text-purple-700 dark:text-purple-400">{{ $ownedVehicles->count() }} auto's</span>
            </div>
        </a>

        <!-- Bende Panden -->
        <a href="{{ route('gangs.panden') }}" class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-xl p-3 shadow-xs flex items-center gap-2.5 hover:border-purple-300 dark:hover:border-purple-700/80 transition-all duration-200">
            <div class="w-9 h-9 rounded-lg bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-bold text-sm shrink-0">
                <i class="fa-solid fa-building"></i>
            </div>
            <div class="min-w-0">
                <span class="text-[9px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wide block truncate">Panden</span>
                <span class="text-xs font-black text-emerald-700 dark:text-emerald-400">{{ $gangProperties->count() }} panden</span>
            </div>
        </a>

        <!-- Sterren -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-xl p-3 shadow-xs flex items-center gap-2.5">
            <div class="w-9 h-9 rounded-lg bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center font-bold text-sm shrink-0">
                <i class="fa-solid fa-star"></i>
            </div>
            <div class="min-w-0">
                <span class="text-[9px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wide block truncate">Sterren</span>
                <span class="text-xs font-black text-amber-700 dark:text-amber-400">{{ $character->stars ?? 0 }}</span>
            </div>
        </div>

        <!-- Smokkelkoffers -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-xl p-3 shadow-xs flex items-center gap-2.5">
            <div class="w-9 h-9 rounded-lg bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 flex items-center justify-center font-bold text-sm shrink-0">
                <i class="fa-solid fa-suitcase-rolling"></i>
            </div>
            <div class="min-w-0">
                <span class="text-[9px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wide block truncate">Koffers</span>
                <span class="text-xs font-black text-blue-700 dark:text-blue-400">{{ $character->unopened_bags ?? 0 }}</span>
            </div>
        </div>

        <!-- Maskers -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-xl p-3 shadow-xs flex items-center gap-2.5">
            <div class="w-9 h-9 rounded-lg bg-rose-50 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 flex items-center justify-center font-bold text-sm shrink-0">
                <i class="fa-solid fa-mask"></i>
            </div>
            <div class="min-w-0">
                <span class="text-[9px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wide block truncate">Maskers</span>
                <span class="text-xs font-black text-rose-700 dark:text-rose-400">{{ $character->masks ?? 0 }}</span>
            </div>
        </div>

        <!-- Gouden Eikels -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-xl p-3 shadow-xs flex items-center gap-2.5">
            <div class="w-9 h-9 rounded-lg bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center font-bold text-sm shrink-0">
                <i class="fa-solid fa-seedling"></i>
            </div>
            <div class="min-w-0">
                <span class="text-[9px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wide block truncate">Eikels 🌰</span>
                <span class="text-xs font-black text-amber-700 dark:text-amber-400">{{ number_format($character->pumpkin_seeds ?? 0) }}</span>
            </div>
        </div>

        <!-- Contant Geld -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-xl p-3 shadow-xs flex items-center gap-2.5">
            <div class="w-9 h-9 rounded-lg bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-bold text-sm shrink-0">
                <i class="fa-solid fa-coins"></i>
            </div>
            <div class="min-w-0">
                <span class="text-[9px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wide block truncate">Contant</span>
                <span class="text-xs font-black text-emerald-700 dark:text-emerald-400">€{{ number_format($character->cash, 0, ',', '.') }}</span>
            </div>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="flex items-center gap-2 overflow-x-auto pb-1 custom-scrollbar">
        <a href="{{ route('marketplace.index', ['type' => 'all']) }}" 
            class="px-3.5 py-2 rounded-xl text-xs transition-all duration-150 active:scale-95 whitespace-nowrap cursor-pointer {{ $filterType === 'all' ? 'bg-purple-600 text-white font-black shadow-xs' : 'bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:border-purple-300 dark:hover:border-purple-700/80 border border-slate-200/90 dark:border-slate-800 font-bold' }}">
            Alle Aanbiedingen ({{ $listings->total() }})
        </a>
        <a href="{{ route('marketplace.index', ['type' => 'depot_item']) }}" 
            class="px-3.5 py-2 rounded-xl text-xs transition-all duration-150 active:scale-95 whitespace-nowrap cursor-pointer {{ $filterType === 'depot_item' ? 'bg-purple-600 text-white font-black shadow-xs' : 'bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:border-purple-300 dark:hover:border-purple-700/80 border border-slate-200/90 dark:border-slate-800 font-bold' }}">
            <i class="fa-solid fa-box-archive text-indigo-500 mr-1.5"></i> Depot Items
        </a>
        <a href="{{ route('marketplace.index', ['type' => 'vehicle']) }}" 
            class="px-3.5 py-2 rounded-xl text-xs transition-all duration-150 active:scale-95 whitespace-nowrap cursor-pointer {{ $filterType === 'vehicle' ? 'bg-purple-600 text-white font-black shadow-xs' : 'bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:border-purple-300 dark:hover:border-purple-700/80 border border-slate-200/90 dark:border-slate-800 font-bold' }}">
            <i class="fa-solid fa-car-side text-purple-500 mr-1.5"></i> Garage & Voertuigen
        </a>
        <a href="{{ route('marketplace.index', ['type' => 'property']) }}" 
            class="px-3.5 py-2 rounded-xl text-xs transition-all duration-150 active:scale-95 whitespace-nowrap cursor-pointer {{ $filterType === 'property' ? 'bg-purple-600 text-white font-black shadow-xs' : 'bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:border-purple-300 dark:hover:border-purple-700/80 border border-slate-200/90 dark:border-slate-800 font-bold' }}">
            <i class="fa-solid fa-building text-emerald-500 mr-1.5"></i> Bende Panden
        </a>
        <a href="{{ route('marketplace.index', ['type' => 'stars']) }}" 
            class="px-3.5 py-2 rounded-xl text-xs transition-all duration-150 active:scale-95 whitespace-nowrap cursor-pointer {{ $filterType === 'stars' ? 'bg-purple-600 text-white font-black shadow-xs' : 'bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:border-purple-300 dark:hover:border-purple-700/80 border border-slate-200/90 dark:border-slate-800 font-bold' }}">
            <i class="fa-solid fa-star text-amber-500 mr-1.5"></i> Sterren
        </a>
        <a href="{{ route('marketplace.index', ['type' => 'vault_codes']) }}" 
            class="px-3.5 py-2 rounded-xl text-xs transition-all duration-150 active:scale-95 whitespace-nowrap cursor-pointer {{ $filterType === 'vault_codes' ? 'bg-purple-600 text-white font-black shadow-xs' : 'bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:border-purple-300 dark:hover:border-purple-700/80 border border-slate-200/90 dark:border-slate-800 font-bold' }}">
            <i class="fa-solid fa-key text-violet-500 mr-1.5"></i> Kluiscodes
        </a>
        <a href="{{ route('marketplace.index', ['type' => 'packages']) }}" 
            class="px-3.5 py-2 rounded-xl text-xs transition-all duration-150 active:scale-95 whitespace-nowrap cursor-pointer {{ $filterType === 'packages' ? 'bg-purple-600 text-white font-black shadow-xs' : 'bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:border-purple-300 dark:hover:border-purple-700/80 border border-slate-200/90 dark:border-slate-800 font-bold' }}">
            <i class="fa-solid fa-briefcase text-blue-500 mr-1.5"></i> Smokkelkoffers
        </a>
        <a href="{{ route('marketplace.index', ['type' => 'masks']) }}" 
            class="px-3.5 py-2 rounded-xl text-xs transition-all duration-150 active:scale-95 whitespace-nowrap cursor-pointer {{ $filterType === 'masks' ? 'bg-purple-600 text-white font-black shadow-xs' : 'bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:border-purple-300 dark:hover:border-purple-700/80 border border-slate-200/90 dark:border-slate-800 font-bold' }}">
            <i class="fa-solid fa-masks-theater text-rose-500 mr-1.5"></i> Maskers
        </a>
        <a href="{{ route('marketplace.index', ['type' => 'pumpkin_seeds']) }}" 
            class="px-3.5 py-2 rounded-xl text-xs transition-all duration-150 active:scale-95 whitespace-nowrap cursor-pointer {{ $filterType === 'pumpkin_seeds' ? 'bg-purple-600 text-white font-black shadow-xs' : 'bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:border-purple-300 dark:hover:border-purple-700/80 border border-slate-200/90 dark:border-slate-800 font-bold' }}">
            <i class="fa-solid fa-seedling text-amber-500 mr-1.5"></i> Gouden Eikels 🌰
        </a>
        <a href="{{ route('marketplace.index', ['type' => 'cash']) }}" 
            class="px-3.5 py-2 rounded-xl text-xs transition-all duration-150 active:scale-95 whitespace-nowrap cursor-pointer {{ $filterType === 'cash' ? 'bg-purple-600 text-white font-black shadow-xs' : 'bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:border-purple-300 dark:hover:border-purple-700/80 border border-slate-200/90 dark:border-slate-800 font-bold' }}">
            <i class="fa-solid fa-coins text-emerald-500 mr-1.5"></i> Contant Geld
        </a>
    </div>

    <!-- Listings Table / Grid -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xs border border-slate-200/90 dark:border-slate-800 overflow-hidden">
        <div class="p-5 border-b border-slate-200/80 dark:border-slate-800 flex items-center justify-between">
            <h2 class="text-base font-extrabold text-slate-900 dark:text-white">Actuele Aanbiedingen</h2>
            <span class="text-xs font-semibold text-slate-400 dark:text-slate-500">{{ $listings->total() }} artikelen te koop</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs min-w-[580px]">
                <thead>
                    <tr class="bg-slate-50/90 dark:bg-slate-800/60 text-[10px] uppercase tracking-wider font-bold text-slate-500 dark:text-slate-400 border-b border-slate-200/80 dark:border-slate-800">
                        <th class="p-4">Aanbod & Categorie</th>
                        <th class="p-4">Verkoper</th>
                        <th class="p-4 text-center">Aantal</th>
                        <th class="p-4 text-right">Vraagprijs</th>
                        <th class="p-4 text-right">Actie</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80">
                    @forelse($listings as $item)
                        @php
                            $isMine = $item->seller_id === $character->id;
                            $canAfford = ($item->currency === 'cash' ? ($character->cash >= $item->price) : (($character->stars ?? 0) >= $item->price)) && ! $character->isImprisoned() && ! $character->isComatose();
                            $badge = $item->getTypeBadge();
                        @endphp
                        <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors {{ $isMine ? 'bg-purple-50/40 dark:bg-purple-950/20' : '' }}">
                            <td class="p-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-12 h-12 rounded-xl flex items-center justify-center text-xl shrink-0 border {{ $badge['class'] }}">
                                        <i class="fa-solid {{ $badge['icon'] }}"></i>
                                    </div>
                                    <div class="space-y-1">
                                        <div class="flex items-center gap-1.5 flex-wrap">
                                            <span class="font-black text-slate-900 dark:text-white text-xs leading-tight">{{ $item->title }}</span>
                                            <span class="text-[9px] font-extrabold px-1.5 py-0.5 rounded border {{ $badge['class'] }}">
                                                {{ $badge['label'] }}
                                            </span>
                                        </div>

                                        <!-- Extra metadata badges for vehicles / properties / depot items -->
                                        @if($item->isVehicle() && isset($item->metadata['condition']))
                                            <div class="flex items-center gap-2 text-[10px] text-slate-500 dark:text-slate-400">
                                                <span class="inline-flex items-center gap-1">
                                                    <i class="fa-solid fa-wrench text-amber-500"></i> Conditie: <strong>{{ $item->metadata['condition'] }}%</strong>
                                                </span>
                                                <span>&bull;</span>
                                                <span class="inline-flex items-center gap-1">
                                                    <i class="fa-solid fa-gauge-high text-purple-500"></i> Snelheid: <strong>{{ $item->metadata['speed'] ?? 'Normaal' }} km/u</strong>
                                                </span>
                                            </div>
                                        @elseif($item->isProperty())
                                            <div class="flex items-center gap-2 text-[10px] text-slate-500 dark:text-slate-400">
                                                <span class="text-emerald-600 dark:text-emerald-400 font-bold">
                                                    <i class="fa-solid fa-users text-emerald-500"></i> Bende: {{ $item->metadata['seller_gang_name'] ?? 'Bende Vastgoed' }}
                                                </span>
                                            </div>
                                        @elseif($item->isDepotItem() && isset($item->metadata['rarity']))
                                            <div class="text-[10px] text-slate-400 capitalize">
                                                Zeldzaamheid: <strong class="text-indigo-600 dark:text-indigo-400">{{ $item->metadata['rarity'] }}</strong> &bull; Categorie: {{ $item->metadata['category'] ?? 'Depot' }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="p-4">
                                @if($item->seller)
                                    <div class="flex items-center gap-2">
                                        <img src="{{ $item->seller->avatar_url }}" alt="{{ $item->seller->name }}" class="w-6 h-6 rounded-full object-cover">
                                        <a href="{{ route('profile.show', $item->seller->name) }}" class="font-bold text-slate-800 dark:text-slate-200 hover:text-indigo-600 text-xs">
                                            {{ $item->seller->name }}
                                        </a>
                                        @if($isMine)
                                            <span class="text-[9px] bg-purple-100 dark:bg-purple-900/60 text-purple-800 dark:text-purple-300 font-extrabold px-1.5 py-0.5 rounded">Jij</span>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-slate-400">Onbekend</span>
                                @endif
                            </td>
                            <td class="p-4 text-center font-extrabold text-slate-700 dark:text-slate-300">
                                {{ $item->amount }}x
                            </td>
                            <td class="p-4 text-right">
                                <span class="font-black text-sm {{ $item->currency === 'stars' ? 'text-amber-600 dark:text-amber-400' : 'text-emerald-600 dark:text-emerald-400' }}">
                                    @if($item->currency === 'stars')
                                        {{ $item->price }} Sterren ⭐
                                    @else
                                        €{{ number_format($item->price, 0, ',', '.') }}
                                    @endif
                                </span>
                            </td>
                            <td class="p-4 text-right">
                                @if($isMine)
                                    <form action="{{ route('marketplace.cancel', $item->id) }}" method="POST" class="inline"
                                          data-confirm="Weet je zeker dat je deze advertentie wilt intrekken? Items worden direct teruggeplaatst in je bezit."
                                          data-confirm-title="Advertentie Annuleren"
                                          data-confirm-btn="Intrekken"
                                          data-confirm-type="warning">
                                        @csrf
                                        <button type="submit" @disabled($character->isImprisoned() || $character->isComatose()) class="px-3 py-1.5 rounded-xl bg-rose-50 dark:bg-rose-950/60 hover:bg-rose-100 dark:hover:bg-rose-900/80 text-rose-700 dark:text-rose-300 border border-rose-200/80 dark:border-rose-800/70 font-bold text-xs transition-all duration-150 active:scale-95 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">
                                            Annuleren
                                        </button>
                                    </form>
                                @else
                                    <form action="{{ route('marketplace.buy', $item->id) }}" method="POST" class="inline"
                                          data-confirm="Weet je zeker dat je '{{ $item->title }}' wilt kopen voor {{ $item->currency === 'stars' ? $item->price . ' Sterren' : '€' . number_format($item->price, 0, ',', '.') }}?"
                                          data-confirm-title="Aankoop Bevestigen"
                                          data-confirm-btn="Ja, Kopen"
                                          data-confirm-type="info">
                                        @csrf
                                        <button type="submit" 
                                                {{ !$canAfford ? 'disabled' : '' }}
                                                class="px-4 py-1.5 rounded-xl font-black text-xs transition-all duration-150 active:scale-95 shadow-xs cursor-pointer
                                                {{ $canAfford ? 'bg-emerald-600 hover:bg-emerald-700 dark:bg-emerald-600 dark:hover:bg-emerald-500 text-white' : 'bg-slate-100 dark:bg-slate-800/80 text-slate-400 dark:text-slate-500 border border-slate-200/80 dark:border-slate-700/60 cursor-not-allowed' }}">
                                            Kopen
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-12 text-center text-slate-400 dark:text-slate-500">
                                <i class="fa-solid fa-box-open text-3xl mb-2 block"></i>
                                Er zijn momenteel geen aanbiedingen in deze categorie. Plaats als eerste een aanbod!
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($listings->hasPages())
            <div class="p-4 border-t border-slate-200/80 dark:border-slate-800">
                {{ $listings->links() }}
            </div>
        @endif
    </div>

    <!-- CREATE LISTING MODAL -->
    <div x-show="createModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 pb-24 lg:pb-4 bg-slate-900/60 backdrop-blur-xs">
        <div @click.away="createModalOpen = false" class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-3xl shadow-2xl max-w-lg w-full max-h-[85dvh] overflow-y-auto p-6 sm:p-7 space-y-5">
            <div class="flex items-center justify-between border-b border-slate-200/80 dark:border-slate-800 pb-3">
                <h3 class="text-base font-black text-slate-900 dark:text-white flex items-center gap-2">
                    <i class="fa-solid fa-plus-circle text-purple-600"></i>
                    <span>Nieuw Aanbod Plaatsen</span>
                </h3>
                <button @click="createModalOpen = false" aria-label="Sluiten" class="w-8 h-8 rounded-xl flex items-center justify-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 text-lg cursor-pointer transition">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form action="{{ route('marketplace.store') }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Wat wil je verkopen?</label>
                    <select name="item_type" x-model="selectedType" class="w-full text-base sm:text-xs p-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/90 text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:border-purple-500 dark:focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition">
                        <optgroup label="Depot, Garage & Bende">
                            <option value="depot_item">🎒 Depot Item ({{ $inventoryItems->count() }} beschikbaar)</option>
                            <option value="vehicle">🏎️ Garage Voertuig ({{ $ownedVehicles->count() }} beschikbaar)</option>
                            @if($character->gang && $character->gang->isOfficer($character))
                                <option value="property">🏢 Bende Pand ({{ $gangProperties->count() }} beschikbaar)</option>
                            @endif
                        </optgroup>
                        <optgroup label="Spelgoederen & Valuta">
                            <option value="packages">🧳 Smokkelkoffers (beschikbaar: {{ $character->unopened_bags ?? 0 }})</option>
                            <option value="vault_codes">🔑 Geheime Kluiscodes (beschikbaar: {{ $myVaultCodes->count() }})</option>
                            <option value="masks">🎭 Maskers (beschikbaar: {{ $character->masks ?? 0 }})</option>
                            <option value="pumpkin_seeds">🌰 Gouden Eikels (beschikbaar: {{ $character->pumpkin_seeds ?? 0 }})</option>
                            <option value="stars">⭐ Exclusieve Sterren (beschikbaar: {{ $character->stars ?? 0 }})</option>
                            <option value="cash">💶 Contant Geld (voor Sterren)</option>
                        </optgroup>
                    </select>
                </div>

                <!-- 1. DEPOT ITEM SELECTOR -->
                <div x-show="selectedType === 'depot_item'" class="space-y-1">
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">Kies een item uit jouw Depot</label>
                    @if($inventoryItems->count() > 0)
                        <select name="character_item_id" class="w-full text-xs p-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/90 text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:border-purple-500 dark:focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition">
                            @foreach($inventoryItems as $invItem)
                                <option value="{{ $invItem->id }}">{{ $invItem->item?->name ?? 'Onbekend' }} (Aantal in depot: {{ $invItem->quantity }}x) - [{{ ucfirst($invItem->item?->category ?? 'Item') }}]</option>
                            @endforeach
                        </select>
                    @else
                        <div class="p-3 bg-amber-50 dark:bg-amber-950/40 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-800/60 rounded-xl text-xs">
                            Je hebt momenteel geen verkoopbare items in je depot liggen. Koop items in de Kantine of scoor buit via Heists!
                        </div>
                    @endif
                </div>

                <!-- 2. GARAGE VEHICLE SELECTOR -->
                <div x-show="selectedType === 'vehicle'" class="space-y-1">
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">Kies een voertuig uit jouw Garage</label>
                    @if($ownedVehicles->count() > 0)
                        <select name="character_vehicle_id" class="w-full text-xs p-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/90 text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:border-purple-500 dark:focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition">
                            @foreach($ownedVehicles as $veh)
                                <option value="{{ $veh->id }}">{{ $veh->vehicle?->name ?? 'Onbekend voertuig' }} (Conditie: {{ $veh->current_condition }}%, Snelheid: {{ $veh->effective_speed }} km/u){{ $veh->is_active ? ' [Actief]' : '' }}</option>
                            @endforeach
                        </select>
                        <p class="text-[11px] text-purple-600 dark:text-purple-400">
                            Let op: het voertuig wordt in escrow geplaatst tot het verkocht is of je de advertentie intrekt.
                        </p>
                    @else
                        <div class="p-3 bg-amber-50 dark:bg-amber-950/40 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-800/60 rounded-xl text-xs">
                            Je bezit momenteel geen voertuigen in je garage. Koop een wagen in de Smokkel Garage!
                        </div>
                    @endif
                </div>

                <!-- 3. BANDE PAND SELECTOR -->
                <div x-show="selectedType === 'property'" class="space-y-1">
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">Kies een Bende Pand om te verhandelen</label>
                    @if($gangProperties->count() > 0)
                        <select name="gang_property_id" class="w-full text-xs p-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/90 text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:border-purple-500 dark:focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition">
                            @foreach($gangProperties as $prop)
                                <option value="{{ $prop->id }}">{{ $prop->name }} - [{{ ucfirst($prop->type) }}] (Aankoopwaarde: €{{ number_format($prop->purchase_price) }})</option>
                            @endforeach
                        </select>
                        <p class="text-[11px] text-emerald-600 dark:text-emerald-400">
                            De opbrengst van dit pand wordt bij verkoop direct bijgeschreven op de bendekas van jouw bende!
                        </p>
                    @else
                        <div class="p-3 bg-amber-50 dark:bg-amber-950/40 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-800/60 rounded-xl text-xs">
                            Jouw bende bezit momenteel geen panden om te verkopen. Schaf eerst panden aan via Bende Panden!
                        </div>
                    @endif
                </div>

                <!-- 4. VAULT CODES SELECTOR -->
                <div x-show="selectedType === 'vault_codes'" class="space-y-1">
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">Kies jouw kluiscode voucher</label>
                    @if($myVaultCodes->count() > 0)
                        <select name="vault_code_id" class="w-full text-xs p-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/90 text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:border-purple-500 dark:focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition">
                            @foreach($myVaultCodes as $code)
                                <option value="{{ $code->id }}">{{ $code->code }} (+€{{ number_format($code->reward_cash) }} bankgeld)</option>
                            @endforeach
                        </select>
                    @else
                        <div class="p-3 bg-amber-50 dark:bg-amber-950/40 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-800/60 rounded-xl text-xs">
                            Je hebt momenteel 0 ongekraakte kluiscodes in bezit. Onderschep koffers of kraak de Medicijnkluis om kluiscodes te scoren!
                        </div>
                    @endif
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                            Aantal te verkopen
                            <span x-show="['vehicle', 'property', 'vault_codes'].includes(selectedType)" class="text-[10px] text-purple-600 font-semibold">(1 stuk)</span>
                        </label>
                        <input type="number" name="amount" min="1" value="1" 
                               :readonly="['vehicle', 'property', 'vault_codes'].includes(selectedType)" 
                               required 
                               class="w-full text-base sm:text-xs p-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/90 text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:border-purple-500 dark:focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition" 
                               :class="{ 'opacity-60 bg-slate-100 dark:bg-slate-800/50': ['vehicle', 'property', 'vault_codes'].includes(selectedType) }">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Vraagprijs</label>
                        <input type="number" name="price" min="10" max="50000000" value="500" required class="w-full text-base sm:text-xs p-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/90 text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:border-purple-500 dark:focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Valuta waarin koper moet betalen</label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="flex items-center gap-2 p-2.5 rounded-xl border border-slate-200/80 dark:border-slate-700/70 cursor-pointer hover:bg-slate-100/60 dark:hover:bg-slate-800/60 bg-slate-50/90 dark:bg-slate-800/60 transition">
                            <input type="radio" name="currency" value="cash" checked class="text-purple-600 focus:ring-purple-500">
                            <span class="text-xs font-bold text-slate-800 dark:text-slate-200">Contant Geld (€)</span>
                        </label>
                        <label class="flex items-center gap-2 p-2.5 rounded-xl border border-slate-200/80 dark:border-slate-700/70 cursor-pointer hover:bg-slate-100/60 dark:hover:bg-slate-800/60 bg-slate-50/90 dark:bg-slate-800/60 transition">
                            <input type="radio" name="currency" value="stars" class="text-purple-600 focus:ring-purple-500">
                            <span class="text-xs font-bold text-slate-800 dark:text-slate-200 inline-flex items-center gap-1">Sterren <i class="fa-solid fa-star text-amber-500 text-xs"></i></span>
                        </label>
                    </div>
                </div>

                <div class="pt-3 border-t border-slate-200/80 dark:border-slate-800 flex items-center justify-end gap-2">
                    <button @click="createModalOpen = false" type="button" class="px-4 py-2 text-xs font-bold bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 border border-slate-200/80 dark:border-slate-700 rounded-xl transition-all duration-150 active:scale-95 cursor-pointer">
                        Annuleren
                    </button>
                    <button type="submit" @disabled($character->isImprisoned() || $character->isComatose()) class="px-5 py-2.5 bg-purple-600 hover:bg-purple-700 dark:bg-purple-600 dark:hover:bg-purple-500 text-white font-black text-xs rounded-xl shadow-xs transition-all duration-150 active:scale-95 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">
                        Plaats op Marktplaats
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
