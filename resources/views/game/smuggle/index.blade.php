@extends('layouts.app')

@section('title', 'Synthese-Lab & Koeriersnetwerk - TBS Kliniek')

@section('content')
@php
    $routesConfig = $transportRoutes ?? \App\Http\Controllers\SmuggleController::TRANSPORT_ROUTES;
    $sigStock = (int) ($cigarettes ?? 0);
    $ritStock = (int) ($ritalin ?? 0);
    $weedStock = (int) ($weed ?? 0);
    $phoneStock = (int) ($phones ?? 0);
@endphp

<div class="space-y-6" x-data="{
    routeIndex: 1,
    routes: {
        1: { key: 'sluiproute', name: 'Voorzichtige Sluiproute', rate: 100, mult: 1.00, respBonus: 0, riskLabel: 'Geen onderscheppingsrisico', desc: 'Doorvoer via de oude ventilatieschacht: 100% veilige aankomst tegen het basistarief.' },
        2: { key: 'koerier', name: 'Gecamoufleerde Wasserijkar', rate: 85, mult: 1.15, respBonus: 2, riskLabel: '15% kans op half verlies', desc: 'Verstopt tussen het vuile linnengoed: +15% extra winst en +2 gezag bij een geslaagde rit.' },
        3: { key: 'stormloop', name: 'Roekeloze Snelle Route', rate: 70, mult: 1.25, respBonus: 5, riskLabel: '30% kans op volledig verlies', desc: 'Directe overdracht dwars over het luchtplein: +25% bonuswinst, +5 gezag en +25% XP!' }
    },
    get activeRoute() {
        return this.routes[this.routeIndex] || this.routes[1];
    },
    formatEuro(amount) {
        return '€' + Math.round(amount).toLocaleString('nl-NL');
    }
}">

    <!-- Top Command Banner -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-5 sm:p-7 shadow-xs">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-5">
            <div class="space-y-2">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="text-[10px] font-black uppercase tracking-wider text-emerald-700 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-950/60 px-2.5 py-1 rounded-full border border-emerald-200/80 dark:border-emerald-800/70">
                        <i class="fa-solid fa-route mr-1"></i> Tactisch Koeriers-Dashboard
                    </span>
                    <span class="text-[10px] font-extrabold text-indigo-700 dark:text-indigo-300 bg-indigo-50 dark:bg-indigo-950/60 px-2.5 py-1 rounded-full border border-indigo-200/80 dark:border-indigo-800/70">
                        Tot +25% Risico-Opslag per Levering
                    </span>
                </div>
                <h1 class="text-xl sm:text-3xl font-black tracking-tight text-slate-900 dark:text-white uppercase">
                    SYNTHESE-LAB &amp; <span class="text-emerald-600 dark:text-emerald-400">KOERIERSNETWERK</span>
                </h1>
                <p class="text-slate-600 dark:text-slate-400 text-xs sm:text-sm max-w-2xl leading-relaxed">
                    Bepaal bij elke levering zelf je <strong class="text-slate-900 dark:text-white">Transport-Aanpak</strong> met de tactische route-schuifregelaar. Kies voor een veilige doorgang via het ventilatiekanaal of neem bewust risico met een snelle overdracht voor maximaal +25% extra opbrengst.
                </p>
            </div>

            <!-- Telemetry Strip -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 shrink-0">
                <div class="bg-slate-50 dark:bg-slate-800/70 border border-slate-200/80 dark:border-slate-700 rounded-xl px-3.5 py-2.5">
                    <span class="text-[9px] font-black uppercase tracking-wider text-slate-400 block">Uithouding</span>
                    <span class="text-sm font-black text-emerald-600 dark:text-emerald-400 font-mono">
                        {{ $character->energy }}/{{ $character->max_energy }} ⚡
                    </span>
                </div>
                <div class="bg-slate-50 dark:bg-slate-800/70 border border-slate-200/80 dark:border-slate-700 rounded-xl px-3.5 py-2.5">
                    <span class="text-[9px] font-black uppercase tracking-wider text-slate-400 block">Kasgeld</span>
                    <span class="text-sm font-black text-amber-600 dark:text-amber-400 font-mono">
                        €{{ number_format($character->cash, 0, ',', '.') }}
                    </span>
                </div>
                <div class="bg-slate-50 dark:bg-slate-800/70 border border-slate-200/80 dark:border-slate-700 rounded-xl px-3.5 py-2.5">
                    <span class="text-[9px] font-black uppercase tracking-wider text-slate-400 block">Gezag</span>
                    <span class="text-sm font-black text-indigo-600 dark:text-indigo-400 font-mono">
                        {{ number_format($character->respect, 0, ',', '.') }}
                    </span>
                </div>
                <div class="bg-slate-50 dark:bg-slate-800/70 border border-slate-200/80 dark:border-slate-700 rounded-xl px-3.5 py-2.5">
                    <span class="text-[9px] font-black uppercase tracking-wider text-slate-400 block">Sterren</span>
                    <span class="text-sm font-black text-slate-900 dark:text-white font-mono">
                        ⭐ {{ $character->stars ?? 0 }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Asymmetric Grid (Flipped Columns: Left = Courier & Deals, Right = Storage Silos & Synthesis Units) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

        <!-- LEFT COLUMN (7 cols): Interactive Transport Route Slider & Active Buyer Contracts -->
        <div class="lg:col-span-7 space-y-6">

            <!-- Interactive Transport Strategy Selector Card (Slider + Mode Cards) -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-5 sm:p-6 shadow-xs space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-100 dark:border-slate-800 pb-3">
                    <div>
                        <span class="text-[10px] font-extrabold uppercase tracking-wider text-emerald-600 dark:text-emerald-400 block">
                            Stap 1: Strategische Smokkelkeuze
                        </span>
                        <h2 class="text-sm sm:text-base font-black uppercase text-slate-900 dark:text-white">
                            Transportmethode &amp; Risico-Regelaar
                        </h2>
                    </div>
                    <span class="text-xs font-mono font-black px-3 py-1 rounded-full"
                          :class="routeIndex == 1 ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300' : (routeIndex == 2 ? 'bg-amber-50 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300' : 'bg-rose-50 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300')"
                          x-text="activeRoute.rate + '% Slaagkans • ' + Math.round((activeRoute.mult - 1) * 100) + '% Bonus'">
                    </span>
                </div>

                <!-- Interactive Range Slider -->
                <div class="space-y-2 pt-1">
                    <div class="flex items-center justify-between text-[11px] font-extrabold uppercase tracking-wider">
                        <span class="text-emerald-600 dark:text-emerald-400">1. Sluiproute (Veilig)</span>
                        <span class="text-amber-600 dark:text-amber-400">2. Wasserijkar (+15%)</span>
                        <span class="text-rose-600 dark:text-rose-400">3. Snelle Route (+25%)</span>
                    </div>
                    <input type="range"
                           min="1"
                           max="3"
                           step="1"
                           x-model.number="routeIndex"
                           aria-label="Kies transportmethode"
                           class="w-full h-2.5 bg-slate-200 dark:bg-slate-700 rounded-lg appearance-none cursor-pointer accent-emerald-600">
                </div>

                <!-- 3 Clickable Route Mode Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <button type="button"
                            @click="routeIndex = 1"
                            :class="routeIndex == 1 ? 'border-emerald-500 bg-emerald-50/70 dark:bg-emerald-950/40 ring-1 ring-emerald-500/40' : 'border-slate-200/80 dark:border-slate-700/70 bg-slate-50/70 dark:bg-slate-800/50'"
                            class="p-3.5 rounded-xl border text-left transition cursor-pointer space-y-1">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-black text-slate-900 dark:text-white">Sluiproute</span>
                            <span class="text-[10px] font-mono font-black text-emerald-600 dark:text-emerald-400">100% kans</span>
                        </div>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400">Standaard winst, 0% verlieskans.</p>
                    </button>

                    <button type="button"
                            @click="routeIndex = 2"
                            :class="routeIndex == 2 ? 'border-amber-500 bg-amber-50/70 dark:bg-amber-950/40 ring-1 ring-amber-500/40' : 'border-slate-200/80 dark:border-slate-700/70 bg-slate-50/70 dark:bg-slate-800/50'"
                            class="p-3.5 rounded-xl border text-left transition cursor-pointer space-y-1">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-black text-slate-900 dark:text-white">Wasserijkar</span>
                            <span class="text-[10px] font-mono font-black text-amber-600 dark:text-amber-400">85% kans</span>
                        </div>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400">+15% winst &amp; +2 gezag.</p>
                    </button>

                    <button type="button"
                            @click="routeIndex = 3"
                            :class="routeIndex == 3 ? 'border-rose-500 bg-rose-50/70 dark:bg-rose-950/40 ring-1 ring-rose-500/40' : 'border-slate-200/80 dark:border-slate-700/70 bg-slate-50/70 dark:bg-slate-800/50'"
                            class="p-3.5 rounded-xl border text-left transition cursor-pointer space-y-1">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-black text-slate-900 dark:text-white">Snelle Route</span>
                            <span class="text-[10px] font-mono font-black text-rose-600 dark:text-rose-400">70% kans</span>
                        </div>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400">+25% winst, +5 gezag &amp; +25% XP.</p>
                    </button>
                </div>

                <!-- Selected Route Summary Box -->
                <div class="p-3.5 rounded-xl bg-slate-100/80 dark:bg-slate-800/80 border border-slate-200/80 dark:border-slate-700 flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs">
                    <div>
                        <strong class="text-slate-900 dark:text-white" x-text="activeRoute.name + ': '"></strong>
                        <span class="text-slate-600 dark:text-slate-300" x-text="activeRoute.desc"></span>
                    </div>
                    <span class="text-[11px] font-mono font-bold text-slate-500 dark:text-slate-400 shrink-0" x-text="activeRoute.riskLabel"></span>
                </div>
            </div>

            <!-- Buyer Dispatch Cards -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-5 sm:p-6 shadow-xs space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                    <div>
                        <span class="text-[10px] font-extrabold uppercase tracking-wider text-indigo-600 dark:text-indigo-400 block">
                            Stap 2: Bestellingen Afhandelen
                        </span>
                        <h2 class="text-sm sm:text-base font-black uppercase text-slate-900 dark:text-white">
                            Actieve Afnemers bij het Doorgeefluik
                        </h2>
                    </div>
                    <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400">
                        3 Wachtende Contacten
                    </span>
                </div>

                <div class="grid grid-cols-1 gap-4">
                    @foreach($orders as $order)
                        @php
                            $haveStock = (int) ($order['current_stock'] ?? 0);
                            $needStock = (int) $order['amount'];
                            $stockPct = min(100, (int) floor(($haveStock / max(1, $needStock)) * 100));
                        @endphp
                        <div class="p-4 rounded-2xl bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70 space-y-3">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                <div class="flex items-center gap-3.5 min-w-0">
                                    <img src="{{ $order['avatar'] }}"
                                         alt="{{ $order['buyer'] }}"
                                         onerror="this.onerror=null; this.src='{{ asset('assets/characters/henkie.jpg') }}';"
                                         class="w-12 h-12 rounded-xl object-cover border border-slate-300 dark:border-slate-600 shrink-0">
                                    <div class="min-w-0">
                                        <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400 block">
                                            Afnemer: {{ $order['buyer'] }}
                                        </span>
                                        <h3 class="text-sm font-black text-slate-900 dark:text-white truncate">
                                            Vraagt {{ $order['amount'] }}x {{ $order['item_name'] }}
                                        </h3>
                                        <span class="text-[11px] font-semibold text-slate-500 dark:text-slate-400">
                                            Jouw voorraad: <strong class="{{ $order['can_fulfill'] ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">{{ $haveStock }}/{{ $needStock }}</strong>
                                        </span>
                                    </div>
                                </div>

                                <!-- Dynamic Payout Preview based on selected Transport Route -->
                                <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-700 rounded-xl px-3.5 py-2 text-right shrink-0">
                                    <span class="text-[10px] font-bold uppercase text-slate-400 block" x-text="'Opbrengst (' + activeRoute.name + ')'">
                                        Opbrengst
                                    </span>
                                    <span class="text-base font-black font-mono text-emerald-600 dark:text-emerald-400"
                                          x-text="formatEuro({{ (int) $order['reward_cash'] }} * activeRoute.mult)">
                                        €{{ number_format($order['reward_cash'], 0, ',', '.') }}
                                    </span>
                                    <span class="text-[11px] font-bold text-indigo-600 dark:text-indigo-400 block"
                                          x-text="'+' + ({{ (int) $order['reward_respect'] }} + activeRoute.respBonus) + ' Gezag'">
                                        +{{ $order['reward_respect'] }} Gezag
                                    </span>
                                </div>
                            </div>

                            <!-- Stock Fulfill Progress Bar -->
                            <div class="w-full h-2 bg-slate-200 dark:bg-slate-700 rounded-full overflow-hidden">
                                <div class="h-full {{ $order['can_fulfill'] ? 'bg-emerald-500' : 'bg-amber-500' }} transition-all"
                                     style="width: {{ $stockPct }}%"></div>
                            </div>

                            <!-- Action Buttons Row -->
                            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 pt-1">
                                <form action="{{ route('smuggle.sell') }}" method="POST" class="flex-1">
                                    @csrf
                                    <input type="hidden" name="order_id" value="{{ $order['id'] }}">
                                    <input type="hidden" name="transport_mode" :value="activeRoute.key">
                                    <button type="submit"
                                            @if(!$order['can_fulfill'] || $character->isImprisoned() || $character->isComatose()) disabled @endif
                                            class="w-full py-2.5 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-700 disabled:bg-slate-200 disabled:dark:bg-slate-800 disabled:text-slate-400 disabled:cursor-not-allowed text-white font-black text-xs uppercase tracking-wider transition cursor-pointer flex items-center justify-center gap-2">
                                        <i class="fa-solid fa-truck-fast text-[11px]"></i>
                                        <span x-text="'Verstuur via ' + activeRoute.name + ' (' + activeRoute.rate + '%)'">Verstuur Zending</span>
                                    </button>
                                </form>

                                <form action="{{ route('smuggle.reject') }}" method="POST"
                                      data-confirm="Wil je deze koper wegsturen en een nieuw contact oproepen?"
                                      data-confirm-title="Koper Wegsturen"
                                      data-confirm-btn="Wegsturen"
                                      data-confirm-type="warning">
                                    @csrf
                                    <input type="hidden" name="order_id" value="{{ $order['id'] }}">
                                    <button type="submit"
                                            @disabled($character->isImprisoned() || $character->isComatose())
                                            class="w-full sm:w-auto py-2.5 px-3.5 rounded-xl bg-slate-200/80 hover:bg-slate-300 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 font-bold text-xs transition cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">
                                        <i class="fa-solid fa-rotate mr-1"></i> Andere Koper
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- RIGHT COLUMN (5 cols): Contraband Storage Silos & Secret Synthesis Units -->
        <div class="lg:col-span-5 space-y-6">

            <!-- Storage Silo Gauges Card -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-5 shadow-xs space-y-4">
                <div class="border-b border-slate-100 dark:border-slate-800 pb-3 flex items-center justify-between">
                    <div>
                        <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400 block">
                            Verborgen Opslag
                        </span>
                        <h2 class="text-sm font-black uppercase text-slate-900 dark:text-white">
                            Smokkelkluis Voorraadmeters
                        </h2>
                    </div>
                    <i class="fa-solid fa-boxes-stacked text-slate-400"></i>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70 space-y-1.5">
                        <div class="flex items-center justify-between text-xs">
                            <span class="font-bold text-slate-700 dark:text-slate-200"><i class="fa-solid fa-smoking text-amber-500 mr-1"></i> Shagwikkels</span>
                            <span class="font-mono font-black text-slate-900 dark:text-white">{{ $sigStock }}</span>
                        </div>
                        <div class="w-full h-1.5 bg-slate-200 dark:bg-slate-700 rounded-full overflow-hidden">
                            <div class="h-full bg-amber-500" style="width: {{ min(100, $sigStock * 5) }}%"></div>
                        </div>
                    </div>

                    <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70 space-y-1.5">
                        <div class="flex items-center justify-between text-xs">
                            <span class="font-bold text-slate-700 dark:text-slate-200"><i class="fa-solid fa-pills text-purple-500 mr-1"></i> Ritalin</span>
                            <span class="font-mono font-black text-slate-900 dark:text-white">{{ $ritStock }}</span>
                        </div>
                        <div class="w-full h-1.5 bg-slate-200 dark:bg-slate-700 rounded-full overflow-hidden">
                            <div class="h-full bg-purple-500" style="width: {{ min(100, $ritStock * 10) }}%"></div>
                        </div>
                    </div>

                    <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70 space-y-1.5">
                        <div class="flex items-center justify-between text-xs">
                            <span class="font-bold text-slate-700 dark:text-slate-200"><i class="fa-solid fa-cannabis text-emerald-500 mr-1"></i> Kaswiet</span>
                            <span class="font-mono font-black text-slate-900 dark:text-white">{{ $weedStock }}</span>
                        </div>
                        <div class="w-full h-1.5 bg-slate-200 dark:bg-slate-700 rounded-full overflow-hidden">
                            <div class="h-full bg-emerald-500" style="width: {{ min(100, $weedStock * 8) }}%"></div>
                        </div>
                    </div>

                    <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70 space-y-1.5">
                        <div class="flex items-center justify-between text-xs">
                            <span class="font-bold text-slate-700 dark:text-slate-200"><i class="fa-solid fa-mobile-screen text-indigo-500 mr-1"></i> Burners</span>
                            <span class="font-mono font-black text-slate-900 dark:text-white">{{ $phoneStock }}</span>
                        </div>
                        <div class="w-full h-1.5 bg-slate-200 dark:bg-slate-700 rounded-full overflow-hidden">
                            <div class="h-full bg-indigo-500" style="width: {{ min(100, $phoneStock * 25) }}%"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Secret Synthesis Lab Units Card -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-5 shadow-xs space-y-4">
                <div class="border-b border-slate-100 dark:border-slate-800 pb-3 flex items-center justify-between">
                    <div>
                        <span class="text-[10px] font-extrabold uppercase tracking-wider text-purple-600 dark:text-purple-400 block">
                            Eigen Aanmaak
                        </span>
                        <h2 class="text-sm font-black uppercase text-slate-900 dark:text-white">
                            Synthese-Units &amp; Kweekkasten
                        </h2>
                    </div>
                    <span class="text-xs font-mono font-black text-emerald-600 dark:text-emerald-400">
                        {{ $character->energy }}⚡ Paraat
                    </span>
                </div>

                @php
                    $productionLines = [
                        [
                            'type' => 'sigaretten',
                            'action' => 'produce_sigaretten',
                            'title' => '10x Zware Shagwikkels Draaien',
                            'energy' => 5,
                            'xp' => 35,
                            'mins' => 5,
                            'skip_stars' => 20,
                            'icon' => 'fa-smoking',
                            'cooldown' => $character->cooldownRemaining('produce_sigaretten'),
                        ],
                        [
                            'type' => 'ritalin',
                            'action' => 'produce_ritalin',
                            'title' => '5x Ritalin Blisters Achteroverdrukken',
                            'energy' => 10,
                            'xp' => 65,
                            'mins' => 10,
                            'skip_stars' => 35,
                            'icon' => 'fa-pills',
                            'cooldown' => $character->cooldownRemaining('produce_ritalin'),
                        ],
                        [
                            'type' => 'wiet',
                            'action' => 'produce_wiet',
                            'title' => '8x Kaswiet Toppen Oogsten',
                            'energy' => 15,
                            'xp' => 95,
                            'mins' => 15,
                            'skip_stars' => 40,
                            'icon' => 'fa-cannabis',
                            'cooldown' => $character->cooldownRemaining('produce_wiet'),
                        ],
                        [
                            'type' => 'telefoon',
                            'action' => 'produce_telefoon',
                            'title' => '1x Burner-Toestel Assembleren',
                            'energy' => 20,
                            'xp' => 150,
                            'mins' => 20,
                            'skip_stars' => 50,
                            'icon' => 'fa-mobile-screen',
                            'cooldown' => max($character->cooldownRemaining('produce_telefoon'), $character->cooldownRemaining('produce_phone')),
                        ],
                    ];
                @endphp

                <div class="space-y-3">
                    @foreach($productionLines as $line)
                        <div class="p-3.5 rounded-xl bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70 space-y-2.5"
                             x-data="{
                                cd: {{ (int) $line['cooldown'] }},
                                format(s) {
                                    if (s <= 0) return '00:00';
                                    const m = Math.floor(s / 60);
                                    const sec = Math.floor(s % 60);
                                    return (m < 10 ? '0' : '') + m + ':' + (sec < 10 ? '0' : '') + sec;
                                },
                                init() {
                                    if (this.cd > 0) {
                                        const timer = setInterval(() => {
                                            if (this.cd > 0) this.cd--;
                                            if (this.cd <= 0) clearInterval(timer);
                                        }, 1000);
                                    }
                                }
                             }">
                            <div class="flex items-start justify-between gap-2">
                                <div class="flex items-center gap-2.5">
                                    <span class="w-9 h-9 rounded-lg bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-sm shrink-0">
                                        <i class="fa-solid {{ $line['icon'] }}"></i>
                                    </span>
                                    <div>
                                        <h4 class="text-xs font-black text-slate-900 dark:text-white">{{ $line['title'] }}</h4>
                                        <span class="text-[10px] font-bold text-slate-500 dark:text-slate-400">
                                            Vereist: {{ $line['energy'] }}⚡ • +{{ $line['xp'] }} XP • Cyclus: {{ $line['mins'] }}m
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div x-show="cd > 0" class="flex items-center justify-between gap-2 pt-1" style="{{ $line['cooldown'] > 0 ? '' : 'display: none;' }}">
                                <span class="text-[11px] font-mono font-black text-amber-700 dark:text-amber-300 bg-amber-50 dark:bg-amber-950/60 px-2.5 py-1 rounded-lg border border-amber-200/80 dark:border-amber-800/70">
                                    Afkoeling: <span x-text="format(cd)">{{ gmdate('i:s', $line['cooldown']) }}</span>
                                </span>
                                <form method="POST" action="{{ route('cooldown.skip') }}">
                                    @csrf
                                    <input type="hidden" name="action" value="{{ $line['action'] }}">
                                    <button type="submit"
                                            @disabled($character->isImprisoned() || $character->isComatose())
                                            class="btn-star-gold px-3 py-1.5 rounded-lg text-slate-900 dark:text-white font-black text-[11px] cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">
                                        ⭐ Versnel ({{ $line['skip_stars'] }})
                                    </button>
                                </form>
                            </div>

                            <div x-show="cd <= 0" style="{{ $line['cooldown'] <= 0 ? '' : 'display: none;' }}">
                                <form action="{{ route('smuggle.produce') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="type" value="{{ $line['type'] }}">
                                    <button type="submit"
                                            @if($character->energy < $line['energy'] || $character->isImprisoned() || $character->isComatose()) disabled @endif
                                            class="w-full py-2 px-3 rounded-xl bg-indigo-600 hover:bg-indigo-700 disabled:bg-slate-200 disabled:dark:bg-slate-800 disabled:text-slate-400 disabled:cursor-not-allowed text-white text-xs font-black uppercase tracking-wider transition cursor-pointer">
                                        Start Synthese-Batch
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

    </div>

</div>
@endsection
