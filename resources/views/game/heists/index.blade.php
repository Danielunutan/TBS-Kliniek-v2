@extends('layouts.app')

@section('title', 'Grote Heists - Het Planningsbord')
@section('meta_description', 'Plan en voer grootschalige overvallen en heists uit in TBS Kliniek. Gebruik je vluchtwagen en maskers voor gigantische cash buit en respect.')

@section('content')
<div class="space-y-6">

    <!-- HERO HEADER -->
    <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-xs relative overflow-hidden">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 relative z-10">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-purple-700 via-indigo-700 to-slate-900 text-white flex items-center justify-center text-2xl shadow-md shrink-0">
                    <i class="fa-solid fa-masks-theater"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h2 class="text-xl font-black text-slate-900 tracking-tight">Het Grote Heist Planningsbord</h2>
                        <span class="px-2.5 py-0.5 rounded-full bg-rose-100 text-rose-700 font-black text-[10px] uppercase tracking-wider animate-pulse">High Stakes</span>
                    </div>
                    <p class="text-xs text-slate-500 mt-1 max-w-2xl leading-relaxed">
                        Bereid grootschalige overvallen voor. Elke heist vereist een <strong>Vluchtvoertuig</strong> uit de garage en <strong>Bivakmutsen/Maskers</strong> om je identiteit geheim te houden. Hoe sneller je wagen en hoe hoger de conditie, hoe groter de slaagkans!
                    </p>
                </div>
            </div>

            <!-- PLAYER HEIST INVENTORY BADGES -->
            <div class="flex items-center gap-3 flex-wrap">
                <!-- Maskers Voorraad -->
                <div class="bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 flex items-center gap-3 shadow-2xs">
                    <div class="w-9 h-9 rounded-lg bg-indigo-100 text-indigo-700 flex items-center justify-center text-base shrink-0">
                        <i class="fa-solid fa-mask"></i>
                    </div>
                    <div>
                        <span class="text-[10px] uppercase font-bold text-slate-400 block tracking-wider">Voorraad Maskers</span>
                        <span class="text-sm font-black text-slate-900 font-mono">{{ $character->masks ?? 0 }}x 🎭</span>
                    </div>
                    <a href="{{ route('shop.index', ['tab' => 'items']) }}" title="Koop meer maskers in de kliniekwinkel" class="ml-1 text-xs text-purple-600 hover:text-purple-700 font-extrabold">
                        + Koop
                    </a>
                </div>

                <!-- Actief Voertuig Badge -->
                <div class="bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 flex items-center gap-3 shadow-2xs">
                    <div class="w-9 h-9 rounded-lg bg-purple-100 text-purple-700 flex items-center justify-center text-base shrink-0">
                        <i class="fa-solid {{ $activeVehicle ? $activeVehicle->vehicle->icon : 'fa-car' }}"></i>
                    </div>
                    <div>
                        <span class="text-[10px] uppercase font-bold text-slate-400 block tracking-wider">Vluchtvoertuig</span>
                        <span class="text-xs font-black text-slate-900 truncate max-w-[140px] block">
                            {{ $activeVehicle ? $activeVehicle->vehicle->name : 'Geen voertuig' }}
                        </span>
                    </div>
                    <a href="{{ route('garage.index') }}" title="Beheer je garage" class="ml-1 text-xs text-purple-600 hover:text-purple-700 font-extrabold">
                        Garage
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- HEIST TARGETS GRID -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @foreach($heists as $heist)
        @php
            $hasLevel = $character->level >= $heist->min_level;
            $hasMasks = ($character->masks ?? 0) >= $heist->required_mask_count;
            $hasVehicle = !empty($activeVehicle);
            $hasCondition = $activeVehicle && ($activeVehicle->current_condition >= $heist->min_condition_required);
            $canLaunch = $hasLevel && $hasMasks && $hasVehicle && $hasCondition && ($character->energy >= $heist->energy_cost);

            // Estimated success chance calculation preview
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

        <div class="bg-white border border-slate-200 hover:border-purple-300 rounded-2xl p-5 shadow-xs flex flex-col justify-between gap-5 transition group">
            <div class="space-y-3.5">
                <!-- Card Header -->
                <div class="flex items-start justify-between gap-2">
                    <div class="w-12 h-12 rounded-xl bg-purple-100 text-purple-700 flex items-center justify-center text-xl shrink-0 group-hover:scale-105 transition-transform shadow-2xs">
                        <i class="fa-solid {{ $heist->icon }}"></i>
                    </div>
                    <div class="text-right space-y-1">
                        <span class="px-2.5 py-0.5 rounded-full border text-[10px] font-black uppercase tracking-wider {{ $heist->difficulty_badge }}">
                            {{ $heist->difficulty }}
                        </span>
                        <div class="text-[11px] font-bold text-slate-400">Level {{ $heist->min_level }}+</div>
                    </div>
                </div>

                <!-- Info -->
                <div>
                    <h3 class="text-sm font-black text-slate-900 group-hover:text-purple-700 transition-colors">{{ $heist->name }}</h3>
                    <p class="text-xs text-slate-500 mt-1 leading-relaxed">{{ $heist->description }}</p>
                </div>

                <!-- Rewards Preview -->
                <div class="bg-slate-50 rounded-xl p-3 border border-slate-100 space-y-1.5">
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-slate-500 font-medium flex items-center gap-1.5">
                            <i class="fa-solid fa-coins text-amber-500"></i> Geschatte Buit:
                        </span>
                        <span class="font-black text-emerald-700 font-mono">
                            €{{ number_format($heist->payout_min, 0, ',', '.') }} &ndash; €{{ number_format($heist->payout_max, 0, ',', '.') }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-slate-500 font-medium flex items-center gap-1.5">
                            <i class="fa-solid fa-star text-purple-500"></i> XP & Respect:
                        </span>
                        <span class="font-bold text-slate-900">
                            +{{ $heist->xp_reward }} XP / +{{ $heist->respect_reward }} Respect
                        </span>
                    </div>
                </div>

                <!-- Requirements Checklist -->
                <div class="space-y-1.5 text-xs pt-1 border-t border-slate-100">
                    <div class="flex items-center justify-between {{ $hasLevel ? 'text-emerald-700' : 'text-slate-400' }}">
                        <span class="flex items-center gap-1.5">
                            <i class="fa-solid {{ $hasLevel ? 'fa-circle-check text-emerald-600' : 'fa-circle-xmark text-slate-300' }}"></i>
                            Minimaal Level {{ $heist->min_level }}
                        </span>
                        <span class="font-mono text-[11px] font-bold">{{ $character->level }}/{{ $heist->min_level }}</span>
                    </div>

                    <div class="flex items-center justify-between {{ $hasMasks ? 'text-emerald-700' : 'text-rose-600 font-semibold' }}">
                        <span class="flex items-center gap-1.5">
                            <i class="fa-solid {{ $hasMasks ? 'fa-circle-check text-emerald-600' : 'fa-circle-xmark text-rose-500' }}"></i>
                            {{ $heist->required_mask_count }}x Bivakmuts / Masker
                        </span>
                        <span class="font-mono text-[11px] font-bold">{{ $character->masks ?? 0 }}/{{ $heist->required_mask_count }}</span>
                    </div>

                    <div class="flex items-center justify-between {{ ($hasVehicle && $hasCondition) ? 'text-emerald-700' : 'text-rose-600 font-semibold' }}">
                        <span class="flex items-center gap-1.5">
                            <i class="fa-solid {{ ($hasVehicle && $hasCondition) ? 'fa-circle-check text-emerald-600' : 'fa-circle-xmark text-rose-500' }}"></i>
                            Vluchtwagen (Min. {{ $heist->min_condition_required }}% Conditie)
                        </span>
                        <span class="font-mono text-[11px] font-bold">
                            {{ $activeVehicle ? $activeVehicle->current_condition . '%' : 'Geen' }}
                        </span>
                    </div>

                    <div class="flex items-center justify-between {{ ($character->energy >= $heist->energy_cost) ? 'text-emerald-700' : 'text-amber-700' }}">
                        <span class="flex items-center gap-1.5">
                            <i class="fa-solid fa-bolt text-amber-500"></i>
                            Energie Nodig
                        </span>
                        <span class="font-mono text-[11px] font-bold">{{ $heist->energy_cost }} Energie</span>
                    </div>
                </div>

                <!-- Slaagkans Bar -->
                @if($hasVehicle)
                <div class="pt-2">
                    <div class="flex items-center justify-between text-xs font-bold mb-1">
                        <span class="text-slate-600">Geschatte Slagingskans:</span>
                        <span class="font-mono {{ $estChance >= 60 ? 'text-emerald-700' : ($estChance >= 40 ? 'text-amber-600' : 'text-rose-600') }}">{{ $estChance }}%</span>
                    </div>
                    <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden">
                        <div class="h-full rounded-full transition-all {{ $estChance >= 60 ? 'bg-emerald-500' : ($estChance >= 40 ? 'bg-amber-500' : 'bg-rose-500') }}" style="width: {{ $estChance }}%"></div>
                    </div>
                </div>
                @endif
            </div>

            <!-- Action Button -->
            <div class="pt-2 border-t border-slate-100">
                @if(! $hasVehicle)
                    <a href="{{ route('garage.index') }}" 
                       class="w-full py-2.5 rounded-xl bg-purple-50 hover:bg-purple-100 border border-purple-200 text-purple-700 font-black text-xs text-center transition flex items-center justify-center gap-1.5">
                        <i class="fa-solid fa-warehouse"></i>
                        <span>Koop Vluchtwagen in Garage</span>
                    </a>
                @elseif(! $hasCondition)
                    <a href="{{ route('garage.index') }}" 
                       class="w-full py-2.5 rounded-xl bg-amber-50 hover:bg-amber-100 border border-amber-300 text-amber-800 font-black text-xs text-center transition flex items-center justify-center gap-1.5">
                        <i class="fa-solid fa-wrench"></i>
                        <span>Wagen Repareren in Garage</span>
                    </a>
                @elseif(! $hasMasks)
                    <a href="{{ route('shop.index', ['tab' => 'items']) }}" 
                       class="w-full py-2.5 rounded-xl bg-indigo-50 hover:bg-indigo-100 border border-indigo-200 text-indigo-700 font-black text-xs text-center transition flex items-center justify-center gap-1.5">
                        <i class="fa-solid fa-mask"></i>
                        <span>Koop Maskers in Winkel</span>
                    </a>
                @elseif($character->energy < $heist->energy_cost)
                    <button type="button" disabled class="w-full py-2.5 rounded-xl bg-slate-100 text-slate-400 font-bold text-xs cursor-not-allowed text-center">
                        Niet genoeg energie ({{ $character->energy }}/{{ $heist->energy_cost }})
                    </button>
                @elseif(! $hasLevel)
                    <button type="button" disabled class="w-full py-2.5 rounded-xl bg-slate-100 text-slate-400 font-bold text-xs cursor-not-allowed text-center">
                        Ontgrendelt op Level {{ $heist->min_level }}
                    </button>
                @else
                    <form action="{{ route('heists.launch', $heist->id) }}" method="POST">
                        @csrf
                        <button type="submit" 
                                onclick="return confirm('Weet je zeker dat je de {{ $heist->name }} wilt starten? Bij mislukking riskeer je de isoleercel!')"
                                class="w-full py-3 rounded-xl bg-gradient-to-r from-rose-600 via-purple-600 to-indigo-600 hover:from-rose-500 hover:to-indigo-500 active:scale-95 text-white font-black text-xs uppercase tracking-wider transition shadow-md flex items-center justify-center gap-2 cursor-pointer">
                            <i class="fa-solid fa-bomb text-xs"></i>
                            <span>Start Heist Operatie</span>
                        </button>
                    </form>
                @endif
            </div>
        </div>
        @endforeach
    </div>

    <!-- RECENT HEISTS TICKER -->
    @if($recentLogs->isNotEmpty())
    <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-xs space-y-4">
        <h3 class="text-sm font-black text-slate-900 uppercase tracking-wider flex items-center gap-2">
            <i class="fa-solid fa-clock-rotate-left text-purple-600"></i>
            <span>Recente Heist Operaties</span>
        </h3>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            @foreach($recentLogs as $log)
            <div class="p-3.5 rounded-xl border {{ $log->success ? 'bg-emerald-50/50 border-emerald-200' : 'bg-rose-50/50 border-rose-200' }} flex items-center justify-between gap-3 text-xs">
                <div class="flex items-center gap-2.5 min-w-0">
                    <div class="w-8 h-8 rounded-lg {{ $log->success ? 'bg-emerald-600 text-white' : 'bg-rose-600 text-white' }} flex items-center justify-center text-xs shrink-0 shadow-2xs font-bold">
                        <i class="fa-solid {{ $log->success ? 'fa-check' : 'fa-xmark' }}"></i>
                    </div>
                    <div class="min-w-0">
                        <div class="font-black text-slate-900 truncate">
                            {{ $log->character->name }} &bull; <span class="font-normal text-slate-600">{{ $log->heist->name }}</span>
                        </div>
                        <div class="text-[11px] text-slate-500">
                            {{ $log->created_at->diffForHumans() }} &bull; {{ $log->vehicle?->name ?? 'Onbekend' }}
                        </div>
                    </div>
                </div>

                <div class="text-right shrink-0">
                    @if($log->success)
                        <span class="font-black text-emerald-700 font-mono block">+€{{ number_format($log->cash_earned, 0, ',', '.') }}</span>
                        <span class="text-[10px] text-emerald-600 font-bold">+{{ $log->respect_earned }} Respect</span>
                    @else
                        <span class="font-black text-rose-700 block">Mislukt</span>
                        <span class="text-[10px] text-rose-600 font-bold">In Isoleercel</span>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

</div>
@endsection
