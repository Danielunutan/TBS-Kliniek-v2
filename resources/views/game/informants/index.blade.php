@extends('layouts.app')

@section('content')
@php
    $totalWealth = (int) $character->cash + (int) $character->bank;
@endphp
<div class="space-y-6" x-data="{ targetName: @js($patients->first()?->name ?? '') }">
    <div class="bg-white dark:bg-slate-900 rounded-2xl p-6 sm:p-8 text-slate-800 dark:text-slate-100 relative overflow-hidden shadow-xs border border-slate-200/90 dark:border-slate-800">
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <div class="inline-flex items-center gap-2 bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 text-xs font-bold px-3 py-1 rounded-full border border-indigo-200/80 dark:border-indigo-800/70 mb-3">
                    <i class="fa-solid fa-user-secret text-indigo-600 dark:text-indigo-400"></i> Inlichtingendienst &amp; Schaduw-Opsporing
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white">Informanten &amp; Schaduw-Inlichtingen</h1>
                <p class="text-slate-500 dark:text-slate-400 text-xs sm:text-sm mt-1 max-w-xl">
                    Spoor het exacte celblok of de kliniek-vleugel van je doelwit op met 15-minuten Camera-Schaduwing voor een liquidatie, of huur informanten in voor zwarte cash.
                </p>
            </div>
            <a href="{{ route('hitman.index') }}" class="py-2.5 px-4 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 border border-slate-200/80 dark:border-slate-700 text-xs font-bold transition-all duration-150 active:scale-95 cursor-pointer flex items-center gap-2 shrink-0">
                <i class="fa-solid fa-crosshairs text-rose-500"></i>
                <span>Naar Liquidatie Pagina &rarr;</span>
            </a>
        </div>
    </div>

    <!-- Schaduw-Inlichtingen (15m Doelwit Opsporing voor Liquidatie) -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-6 shadow-xs space-y-5">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-100 dark:border-slate-800 pb-4">
            <div>
                <h2 class="text-sm font-extrabold text-slate-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
                    <i class="fa-solid fa-video text-indigo-600 dark:text-indigo-400"></i> Doelwit Lokaliseren (15-Minuten Schaduw-Inlichtingen)
                </h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                    Om een speler te kunnen liquideren moet je in dezelfde kliniek-ruimte staan. Laat ons bewakingsteam een speler schaduwen.
                </p>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 items-end">
            <div class="space-y-1">
                <label class="block text-xs font-extrabold text-slate-700 dark:text-slate-300 uppercase">Typ of kies naam van doelwit</label>
                <input type="text" x-model="targetName" placeholder="Spelersnaam..."
                       class="w-full p-2.5 bg-slate-50 dark:bg-slate-800/90 border border-slate-200 dark:border-slate-700 rounded-xl text-base sm:text-sm font-bold text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:border-purple-500 dark:focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition">
            </div>
            <div class="space-y-1">
                <label class="block text-xs font-extrabold text-slate-700 dark:text-slate-300 uppercase">Of selecteer uit actieve patiënten</label>
                <select @change="targetName = $event.target.value" class="w-full p-2.5 bg-slate-50 dark:bg-slate-800/90 border border-slate-200 dark:border-slate-700 rounded-xl text-base sm:text-sm font-bold text-slate-900 dark:text-slate-100 focus:outline-none focus:border-purple-500 dark:focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition">
                    @foreach($patients as $p)
                        <option value="{{ $p->name }}">{{ $p->name }} (Lvl {{ $p->level }})</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @foreach($intelCatalog as $intel)
            <div class="p-4 rounded-xl border border-slate-200/80 dark:border-slate-700/70 bg-slate-50/90 dark:bg-slate-800/60 flex flex-col justify-between gap-3">
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <span class="text-xs font-extrabold text-slate-900 dark:text-white">{{ $intel['name'] }}</span>
                        <span class="px-2 py-0.5 rounded-full bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-700 text-[10px] font-black text-indigo-700 dark:text-indigo-400">
                            {{ $intel['wait_minutes'] }} min
                        </span>
                    </div>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400">{{ $intel['subtitle'] }}</p>
                </div>

                <div class="grid grid-cols-2 gap-2 pt-2 border-t border-slate-200/80 dark:border-slate-700/70">
                    @php
                        $canAffordIntelCash = $totalWealth >= $intel['cost_cash'] && !$character->isImprisoned() && !$character->isComatose();
                        $canAffordIntelStars = (int) ($character->stars ?? 0) >= $intel['cost_stars'] && !$character->isImprisoned() && !$character->isComatose();
                    @endphp
                    <form action="{{ route('hitman.intel') }}" method="POST">
                        @csrf
                        <input type="hidden" name="target_name" :value="targetName">
                        <input type="hidden" name="intel_type" value="{{ $intel['id'] }}">
                        <input type="hidden" name="currency" value="cash">
                        <button type="submit" {{ !$canAffordIntelCash ? 'disabled' : '' }} class="w-full py-2 px-3 rounded-xl {{ $canAffordIntelCash ? 'bg-purple-600 hover:bg-purple-700 dark:bg-purple-600 dark:hover:bg-purple-500 text-white shadow-xs cursor-pointer active:scale-95' : 'bg-slate-100 dark:bg-slate-800/80 text-slate-400 dark:text-slate-500 border border-slate-200/80 dark:border-slate-700/60 cursor-not-allowed' }} text-xs font-black transition-all duration-150">
                            €{{ number_format($intel['cost_cash'] / 1000, 0, ',', '.') }}k
                        </button>
                    </form>
                    <form action="{{ route('hitman.intel') }}" method="POST">
                        @csrf
                        <input type="hidden" name="target_name" :value="targetName">
                        <input type="hidden" name="intel_type" value="{{ $intel['id'] }}">
                        <input type="hidden" name="currency" value="stars">
                        <button type="submit" {{ !$canAffordIntelStars ? 'disabled' : '' }} class="w-full py-2 px-3 rounded-xl {{ $canAffordIntelStars ? 'btn-star-gold text-white shadow-xs cursor-pointer active:scale-95' : 'bg-slate-100 dark:bg-slate-800/80 text-slate-400 dark:text-slate-500 border border-slate-200/80 dark:border-slate-700/60 cursor-not-allowed' }} text-xs font-black transition-all duration-150">
                            {{ $intel['cost_stars'] }} ⭐
                        </button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>

        @if(! empty($enrichedIntelOps))
        <div class="pt-4 border-t border-slate-100 dark:border-slate-800 space-y-3">
            <h3 class="text-xs font-extrabold text-slate-800 dark:text-slate-200 uppercase tracking-wider">Actieve Observatie-Dossiers</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                @foreach($enrichedIntelOps as $op)
                <div class="p-4 rounded-xl bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70 flex items-center justify-between gap-3">
                    <div>
                        <div class="text-xs font-extrabold text-slate-900 dark:text-white">{{ $op['target_name'] }} — {{ $op['type_name'] }}</div>
                        @if($op['is_ready'])
                            <div class="text-xs font-black text-emerald-700 dark:text-emerald-400 mt-0.5">
                                <i class="fa-solid fa-location-dot"></i> Gezien op: {{ $op['revealed_location'] }} ({{ $op['cash_formatted'] }} contant)
                            </div>
                        @else
                            <div class="text-xs text-indigo-700 dark:text-indigo-400 font-bold mt-0.5">
                                <i class="fa-solid fa-clock"></i> Rapport klaar over ~{{ ceil($op['wait_remaining_sec'] / 60) }} min
                            </div>
                        @endif
                    </div>
                    @if($op['is_ready'] && ! $op['same_wing'])
                        <form action="{{ route('hitman.quick_travel') }}" method="POST">
                            @csrf
                            <input type="hidden" name="destination" value="{{ $op['revealed_location'] }}">
                            <button type="submit" 
                                    {{ $character->isImprisoned() || $character->isComatose() ? 'disabled' : '' }}
                                    class="py-2 px-3 rounded-xl {{ $character->isImprisoned() || $character->isComatose() ? 'bg-slate-100 dark:bg-slate-800/80 text-slate-400 dark:text-slate-500 border border-slate-200/80 dark:border-slate-700/60 cursor-not-allowed' : 'bg-emerald-600 hover:bg-emerald-700 dark:bg-emerald-600 dark:hover:bg-emerald-500 text-white cursor-pointer active:scale-95' }} text-xs font-black shadow-xs transition-all duration-150">
                                Reis naar Vleugel
                            </button>
                        </form>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    <!-- Energy Info -->
    <div class="flex items-center gap-3 bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-xl px-5 py-3 shadow-xs">
        <i class="fa-solid fa-bolt text-emerald-500"></i>
        <span class="text-xs font-bold text-slate-700 dark:text-slate-300">Energie: <span class="text-emerald-700 dark:text-emerald-400">{{ $character->energy }} / {{ $character->max_energy }}</span></span>
        <div class="flex-1 bg-slate-100 dark:bg-slate-800 rounded-full h-2 overflow-hidden border border-slate-200/80 dark:border-slate-700/70">
            <div class="bg-emerald-500 dark:bg-emerald-400 h-full rounded-full" style="width: {{ min(100, round(($character->energy / max(1, $character->max_energy)) * 100)) }}%"></div>
        </div>
        <span class="text-xs font-bold text-slate-500 dark:text-slate-400">Cash: <span class="text-slate-800 dark:text-white font-extrabold">€{{ number_format($character->cash, 0, ',', '.') }}</span></span>
    </div>

    <!-- Informants Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        @foreach($informants as $inf)
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/90 dark:border-slate-800 hover:border-purple-300 dark:hover:border-purple-700/80 shadow-xs transition-all duration-200 p-5 flex flex-col justify-between gap-4">
            <div class="flex items-start gap-3">
                <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 border border-indigo-200/60 dark:border-indigo-800/50 flex items-center justify-center shrink-0">
                    <i class="fa-solid {{ $inf['icon'] }}"></i>
                </div>
                <div>
                    <h3 class="text-sm font-extrabold text-slate-900 dark:text-white">{{ $inf['name'] }}</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ $inf['desc'] }}</p>
                </div>
            </div>

            <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between gap-4">
                <div class="space-y-1">
                    <div class="flex items-center gap-2 text-xs">
                        <span class="text-slate-400 dark:text-slate-500 font-semibold w-16">Kosten:</span>
                        <span class="font-bold text-slate-700 dark:text-slate-200">€{{ number_format($inf['cost'], 0, ',', '.') }}</span>
                    </div>
                    <div class="flex items-center gap-2 text-xs">
                        <span class="text-slate-400 dark:text-slate-500 font-semibold w-16">Buit:</span>
                        <span class="font-extrabold text-emerald-700 dark:text-emerald-400">€{{ number_format($inf['reward_min'], 0, ',', '.') }}–€{{ number_format($inf['reward_max'], 0, ',', '.') }}</span>
                    </div>
                    <div class="flex items-center gap-2 text-xs">
                        <span class="text-slate-400 dark:text-slate-500 font-semibold w-16">Energie:</span>
                        <span class="font-bold text-amber-600 dark:text-amber-400">{{ $inf['energy'] }} EP</span>
                    </div>
                </div>

                <form action="{{ route('informants.hire') }}" method="POST">
                    @csrf
                    <input type="hidden" name="informant_id" value="{{ $inf['id'] }}">
                    <button type="submit"
                            {{ $character->isImprisoned() || $character->isComatose() || $character->energy < $inf['energy'] || $character->cash < $inf['cost'] ? 'disabled' : '' }}
                            class="px-4 py-2 rounded-xl text-xs font-black transition-all duration-150 shadow-xs {{ $character->isImprisoned() || $character->isComatose() || $character->energy < $inf['energy'] || $character->cash < $inf['cost'] ? 'bg-slate-100 dark:bg-slate-800/80 text-slate-400 dark:text-slate-500 border border-slate-200/80 dark:border-slate-700/60 cursor-not-allowed' : 'bg-purple-600 hover:bg-purple-700 dark:bg-purple-600 dark:hover:bg-purple-500 text-white active:scale-95 cursor-pointer' }}">
                        <i class="fa-solid fa-user-secret mr-1"></i> Inhuren
                    </button>
                </form>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection
