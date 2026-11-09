@extends('layouts.app')

@section('title', 'Bende Uurloon & Live Calculator - TBS Kliniek')

@section('content')
@php
    $inGang = (bool) ($gang && $character?->gang_id);
    $breakdown = $breakdown ?? ($wageDetails['breakdown'] ?? []);
    $hourlyWage = $wageDetails['hourly_wage'] ?? 0;
    $baseHourlyWage = $wageDetails['base_hourly_wage'] ?? ($breakdown['collective_hourly_wage'] ?? 0);
    $dailyWage = $wageDetails['daily_wage'] ?? ($hourlyWage * 24);
    $pendingHours = (int) ($wageDetails['pending_hours'] ?? 0);
    $pendingAmount = (int) ($wageDetails['pending_amount'] ?? 0);
    $nextPayout = $wageDetails['next_payout_human'] ?? 'Elk heel uur';
@endphp

<div class="space-y-6" x-data="{
    activeTab: 'calculator',
    // Calculator state
    kiosks: {{ min(1000, max(0, (int) ($breakdown['kiosks_count'] ?? 100))) }},
    pandenBase: {{ (int) ($breakdown['properties_income'] ?? 50000) }},
    pandenBonusPct: {{ (int) ($breakdown['luxury_bonus_pct'] ?? 20) }},
    vehiclesBase: {{ (int) ($breakdown['vehicles_income'] ?? 20000) }},
    npcsIncome: {{ (int) ($breakdown['npcs_income'] ?? 2000) }},
    npcBonusPct: {{ (int) ($breakdown['npc_bonus_pct'] ?? 10) }},
    respect: {{ (int) ($gang?->respect ?? 500000) }},
    hasWarDominance: {{ (!empty($breakdown['war_dominance_pct']) && $breakdown['war_dominance_pct'] > 0) ? 'true' : 'false' }},
    charLevel: {{ max(1, (int) ($character?->level ?? 20)) }},
    charRole: '{{ $character?->gang_role ?? 'member' }}',

    get kioskIncome() {
        return Math.min(1000, Math.max(0, parseInt(this.kiosks) || 0)) * 50;
    },
    get respectBonusPct() {
        return Math.min(50, Math.floor((parseInt(this.respect) || 0) / 100000));
    },
    get warBonusPct() {
        return this.hasWarDominance ? 25 : 0;
    },
    get totalBonusPct() {
        return (parseInt(this.pandenBonusPct) || 0) + this.respectBonusPct + (parseInt(this.npcBonusPct) || 0) + this.warBonusPct;
    },
    get subtotalBase() {
        return 150 + this.kioskIncome + (parseInt(this.pandenBase) || 0) + (parseInt(this.vehiclesBase) || 0) + (parseInt(this.npcsIncome) || 0);
    },
    get collectiveWage() {
        return Math.round(this.subtotalBase * (1 + (this.totalBonusPct / 100)));
    },
    get levelBonusPct() {
        return Math.round((Math.max(1, parseInt(this.charLevel) || 1) - 1) * 2.5);
    },
    get levelMultiplier() {
        return 1.0 + ((Math.max(1, parseInt(this.charLevel) || 1) - 1) * 0.025);
    },
    get roleMultiplier() {
        switch(this.charRole) {
            case 'leader': return 1.25;
            case 'co_leader': return 1.15;
            case 'officer': return 1.10;
            default: return 1.00;
        }
    },
    get personalWage() {
        return Math.round(this.collectiveWage * this.levelMultiplier * this.roleMultiplier);
    },
    get dailyWage() {
        return this.personalWage * 24;
    },
    get weeklyWage() {
        return this.dailyWage * 7;
    },
    formatNumber(num) {
        return new Intl.NumberFormat('nl-NL').format(Math.round(num || 0));
    },
    setPreset(type) {
        if (type === 'starter') {
            this.kiosks = 50;
            this.pandenBase = 4000;
            this.pandenBonusPct = 0;
            this.vehiclesBase = 4000;
            this.npcsIncome = 500;
            this.npcBonusPct = 0;
            this.respect = 50000;
            this.hasWarDominance = false;
            this.charLevel = 5;
            this.charRole = 'member';
        } else if (type === 'medium') {
            this.kiosks = 500;
            this.pandenBase = 45000;
            this.pandenBonusPct = 25;
            this.vehiclesBase = 25000;
            this.npcsIncome = 2500;
            this.npcBonusPct = 20;
            this.respect = 750000;
            this.hasWarDominance = false;
            this.charLevel = 25;
            this.charRole = 'officer';
        } else if (type === 'top') {
            this.kiosks = 1000;
            this.pandenBase = 350000;
            this.pandenBonusPct = 95;
            this.vehiclesBase = 125000;
            this.npcsIncome = 15000;
            this.npcBonusPct = 80;
            this.respect = 2500000;
            this.hasWarDominance = true;
            this.charLevel = 50;
            this.charRole = 'leader';
        } else if (type === 'current') {
            this.kiosks = {{ min(1000, max(0, (int) ($breakdown['kiosks_count'] ?? 0))) }};
            this.pandenBase = {{ (int) ($breakdown['properties_income'] ?? 0) }};
            this.pandenBonusPct = {{ (int) ($breakdown['luxury_bonus_pct'] ?? 0) }};
            this.vehiclesBase = {{ (int) ($breakdown['vehicles_income'] ?? 0) }};
            this.npcsIncome = {{ (int) ($breakdown['npcs_income'] ?? 0) }};
            this.npcBonusPct = {{ (int) ($breakdown['npc_bonus_pct'] ?? 0) }};
            this.respect = {{ (int) ($gang?->respect ?? 0) }};
            this.hasWarDominance = {{ (!empty($breakdown['war_dominance_pct']) && $breakdown['war_dominance_pct'] > 0) ? 'true' : 'false' }};
            this.charLevel = {{ max(1, (int) ($character?->level ?? 1)) }};
            this.charRole = '{{ $character?->gang_role ?? 'member' }}';
        }
    }
}">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-xs relative overflow-hidden">
        <div class="absolute -right-10 -bottom-10 w-48 h-48 bg-emerald-500/10 rounded-full blur-2xl pointer-events-none"></div>

        <div>
            <div class="flex items-center gap-2 mb-1.5 flex-wrap">
                <a href="{{ route('gangs.index') }}" class="text-xs font-bold text-slate-500 hover:text-purple-600 transition flex items-center gap-1">
                    <i class="fa-solid fa-arrow-left"></i> Bendes
                </a>
                <span class="text-slate-300 dark:text-slate-700">•</span>
                @if($inGang)
                    <span class="text-xs font-extrabold uppercase tracking-wider text-purple-700 dark:text-purple-400 bg-purple-50 dark:bg-purple-950/60 px-2.5 py-0.5 rounded-full border border-purple-200 dark:border-purple-800">
                        [{{ $gang->tag }}] {{ $gang->name }}
                    </span>
                @else
                    <span class="text-xs font-extrabold uppercase tracking-wider text-slate-600 dark:text-slate-400 bg-slate-100 dark:bg-slate-800 px-2.5 py-0.5 rounded-full">
                        Geen Bende Lid
                    </span>
                @endif
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white flex items-center gap-3">
                <i class="fa-solid fa-sack-dollar text-emerald-500"></i>
                <span>Bende Uurloon & Live Calculator</span>
            </h1>
            <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 mt-1 max-w-2xl">
                Ieder heel uur automatisch salaris op je bankrekening. Bekijk hieronder de exacte realtime berekening van jouw bende of simuleer wat jouw bende-salaris wordt bij uitbreiding!
            </p>
        </div>

        <div class="flex items-center gap-2 shrink-0 flex-wrap">
            <a href="{{ route('gangs.panden') }}" class="px-3.5 py-2 rounded-xl bg-purple-50 hover:bg-purple-100 dark:bg-purple-950/60 dark:hover:bg-purple-900/60 text-purple-700 dark:text-purple-300 border border-purple-200/80 dark:border-purple-800/70 text-xs font-black uppercase tracking-wider transition-all duration-150 active:scale-95 flex items-center gap-1.5 shadow-2xs">
                <i class="fa-solid fa-building text-[11px]"></i>
                <span>Panden</span>
            </a>
            <a href="{{ route('gangs.garage') }}" class="px-3.5 py-2 rounded-xl bg-purple-50 hover:bg-purple-100 dark:bg-purple-950/60 dark:hover:bg-purple-900/60 text-purple-700 dark:text-purple-300 border border-purple-200/80 dark:border-purple-800/70 text-xs font-black uppercase tracking-wider transition-all duration-150 active:scale-95 flex items-center gap-1.5 shadow-2xs">
                <i class="fa-solid fa-warehouse text-[11px]"></i>
                <span>Garage</span>
            </a>
            @if($inGang && $pendingHours > 0)
            <form method="POST" action="{{ route('gangs.claim-wage') }}">
                @csrf
                <button type="submit" class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-black uppercase tracking-wider transition-all duration-150 active:scale-95 flex items-center gap-2 shadow-xs cursor-pointer">
                    <i class="fa-solid fa-hand-holding-dollar"></i>
                    <span>Claim Nu (+€{{ number_format($pendingAmount, 0, ',', '.') }})</span>
                </button>
            </form>
            @endif
        </div>
    </div>

    <!-- Live KPI Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Jouw Persoonlijk Uurloon -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-5 shadow-xs relative overflow-hidden">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[11px] font-black uppercase tracking-wider text-slate-500 dark:text-slate-400">Jouw Uurloon</span>
                <span class="w-8 h-8 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-xs">
                    <i class="fa-solid fa-wallet"></i>
                </span>
            </div>
            <div class="text-2xl font-black text-emerald-600 dark:text-emerald-400 font-mono">
                +€{{ number_format($hourlyWage, 0, ',', '.') }}<span class="text-xs text-slate-400 font-bold">/u</span>
            </div>
            <div class="mt-2 text-[11px] text-slate-500 dark:text-slate-400 flex items-center gap-1.5">
                <i class="fa-solid fa-circle-check text-emerald-500 text-[10px]"></i>
                <span>Inclusief rol & level scaling</span>
            </div>
        </div>

        <!-- Collectief Bende Uurloon -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-5 shadow-xs">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[11px] font-black uppercase tracking-wider text-slate-500 dark:text-slate-400">Bende Basispot</span>
                <span class="w-8 h-8 rounded-xl bg-purple-50 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400 flex items-center justify-center text-xs">
                    <i class="fa-solid fa-building-columns"></i>
                </span>
            </div>
            <div class="text-2xl font-black text-slate-900 dark:text-white font-mono">
                €{{ number_format($baseHourlyWage, 0, ',', '.') }}<span class="text-xs text-slate-400 font-bold">/u</span>
            </div>
            <div class="mt-2 text-[11px] text-slate-500 dark:text-slate-400 flex items-center gap-1.5">
                <i class="fa-solid fa-users text-purple-500 text-[10px]"></i>
                <span>Gedeelde bende verdiensten</span>
            </div>
        </div>

        <!-- 24-Uurs Dagbuit -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-5 shadow-xs">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[11px] font-black uppercase tracking-wider text-slate-500 dark:text-slate-400">Verwachte Dagbuit</span>
                <span class="w-8 h-8 rounded-xl bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center text-xs">
                    <i class="fa-solid fa-calendar-day"></i>
                </span>
            </div>
            <div class="text-2xl font-black text-amber-600 dark:text-amber-400 font-mono">
                €{{ number_format($dailyWage, 0, ',', '.') }}
            </div>
            <div class="mt-2 text-[11px] text-slate-500 dark:text-slate-400 flex items-center gap-1.5">
                <i class="fa-solid fa-clock text-amber-500 text-[10px]"></i>
                <span>Per 24 uur bij normale inzet</span>
            </div>
        </div>

        <!-- Volgende Uitbetaling -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-5 shadow-xs">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[11px] font-black uppercase tracking-wider text-slate-500 dark:text-slate-400">Volgende Uitbetaling</span>
                <span class="w-8 h-8 rounded-xl bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 flex items-center justify-center text-xs">
                    <i class="fa-solid fa-hourglass-half"></i>
                </span>
            </div>
            <div class="text-2xl font-black text-blue-600 dark:text-blue-400 font-mono">
                {{ $nextPayout }}
            </div>
            <div class="mt-2 text-[11px] text-slate-500 dark:text-slate-400 flex items-center gap-1.5">
                <i class="fa-solid fa-rotate text-blue-500 text-[10px]"></i>
                <span>Elk heel uur automatisch</span>
            </div>
        </div>
    </div>

    <!-- Navigation Tabs Switcher -->
    <div class="flex items-center gap-2 border-b border-slate-200 dark:border-slate-800 pb-3">
        <button type="button"
                @click="activeTab = 'calculator'"
                :class="activeTab === 'calculator' ? 'bg-purple-600 text-white shadow-xs' : 'bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300'"
                class="px-4 py-2 rounded-xl text-xs font-black uppercase tracking-wider transition cursor-pointer flex items-center gap-2">
            <i class="fa-solid fa-calculator"></i>
            <span>Interactieve Calculator (Wat-Als Simulator)</span>
        </button>

        @if($inGang)
        <button type="button"
                @click="activeTab = 'breakdown'"
                :class="activeTab === 'breakdown' ? 'bg-purple-600 text-white shadow-xs' : 'bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300'"
                class="px-4 py-2 rounded-xl text-xs font-black uppercase tracking-wider transition cursor-pointer flex items-center gap-2">
            <i class="fa-solid fa-list-check"></i>
            <span>Mijn Bende Loonpot (Live Specificatie)</span>
        </button>
        @endif
    </div>

    <!-- TAB 1: INTERACTIVE CALCULATOR (WAT-ALS SIMULATOR) -->
    <div x-show="activeTab === 'calculator'" class="space-y-6">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            
            <!-- Controls Column (7 cols) -->
            <div class="lg:col-span-7 space-y-5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-xs">
                
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800 flex-wrap gap-2">
                    <div>
                        <h2 class="text-base font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                            <i class="fa-solid fa-sliders text-purple-600 dark:text-purple-400"></i>
                            <span>Bende & Speler Parameters</span>
                        </h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Pas de schuifknoppen aan om te zien hoe elke aankoop en level direct doorrekent.</p>
                    </div>

                    <!-- Presets -->
                    <div class="flex items-center gap-1.5 flex-wrap">
                        <button type="button" @click="setPreset('starter')" class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-[10px] font-black uppercase transition cursor-pointer">Starter</button>
                        <button type="button" @click="setPreset('medium')" class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-[10px] font-black uppercase transition cursor-pointer">Middelgroot</button>
                        <button type="button" @click="setPreset('top')" class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-[10px] font-black uppercase transition cursor-pointer">Top Syndicaat</button>
                        @if($inGang)
                        <button type="button" @click="setPreset('current')" class="px-2.5 py-1 rounded-lg bg-purple-100 hover:bg-purple-200 dark:bg-purple-950/80 dark:hover:bg-purple-900/80 text-purple-700 dark:text-purple-300 border border-purple-300 dark:border-purple-800 text-[10px] font-black uppercase transition cursor-pointer">Mijn Bende</button>
                        @endif
                    </div>
                </div>

                <!-- 1. Smokkelkiosken -->
                <div class="space-y-2">
                    <div class="flex items-center justify-between text-xs font-bold">
                        <span class="text-slate-700 dark:text-slate-300 flex items-center gap-1.5">
                            <i class="fa-solid fa-store text-purple-500"></i>
                            <span>Smokkelkiosken (max 1.000)</span>
                        </span>
                        <div class="text-right">
                            <span class="text-slate-900 dark:text-white font-mono font-black" x-text="formatNumber(kiosks) + ' stuks'"></span>
                            <span class="text-emerald-600 dark:text-emerald-400 font-mono text-[11px] block font-extrabold" x-text="'+€' + formatNumber(kioskIncome) + '/u basis'"></span>
                        </div>
                    </div>
                    <input type="range" min="0" max="1000" step="10" x-model.number="kiosks" class="w-full accent-purple-600 cursor-pointer">
                    <div class="flex justify-between text-[10px] text-slate-400">
                        <span>0 stuks</span>
                        <span>500 stuks (+€25.000/u)</span>
                        <span>1.000 max (+€50.000/u)</span>
                    </div>
                </div>

                <!-- 2. Bende Panden Basis & Bonus -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2 border-t border-slate-100 dark:border-slate-800/80">
                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                            <i class="fa-solid fa-building text-purple-500 mr-1"></i> Panden Basis-Uurloon (€/u)
                        </label>
                        <input type="number" min="0" max="5000000" step="1000" x-model.number="pandenBase" class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-xs font-mono font-bold text-slate-900 dark:text-white">
                        <p class="text-[10px] text-slate-400">Som van alle panden (bijv. Kleine Loods €1.000, Loodshangar €350.000, Citadel €1.250.000).</p>
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                            <i class="fa-solid fa-gem text-purple-500 mr-1"></i> Luxe Panden Bonus (%)
                        </label>
                        <input type="number" min="0" max="500" step="1" x-model.number="pandenBonusPct" class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-xs font-mono font-bold text-slate-900 dark:text-white">
                        <p class="text-[10px] text-slate-400">Procentuele bonus van luxe hoofdkwartieren (bijv. Penthouse +12%, Villa +15%, Citadel +40%).</p>
                    </div>
                </div>

                <!-- 3. Bende Garage Wagenpark -->
                <div class="space-y-1.5 pt-2 border-t border-slate-100 dark:border-slate-800/80">
                    <div class="flex items-center justify-between text-xs font-bold">
                        <label class="text-slate-700 dark:text-slate-300">
                            <i class="fa-solid fa-warehouse text-purple-500 mr-1"></i> Bende Wagenpark Inkomsten (€/u)
                        </label>
                        <span class="text-emerald-600 dark:text-emerald-400 font-mono" x-text="'+€' + formatNumber(vehiclesBase) + '/u'"></span>
                    </div>
                    <input type="number" min="0" max="1000000" step="1000" x-model.number="vehiclesBase" class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-xs font-mono font-bold text-slate-900 dark:text-white">
                    <p class="text-[10px] text-slate-400">Motoren €1.500/u, Auto's €4.000/u, Boten €12.500/u, Helikopters/Vliegtuigen €25.000/u (geschaald naar conditie).</p>
                </div>

                <!-- 4. NPCs / Personeel -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2 border-t border-slate-100 dark:border-slate-800/80">
                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                            <i class="fa-solid fa-user-tie text-purple-500 mr-1"></i> Personeel Basisbijdrage (€/u)
                        </label>
                        <input type="number" min="0" max="250000" step="500" x-model.number="npcsIncome" class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-xs font-mono font-bold text-slate-900 dark:text-white">
                        <p class="text-[10px] text-slate-400">€500 per ingehuurde specialist.</p>
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                            <i class="fa-solid fa-money-bill-trend-up text-purple-500 mr-1"></i> Witwasser Uurloonbonus (%)
                        </label>
                        <input type="number" min="0" max="500" step="5" x-model.number="npcBonusPct" class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-xs font-mono font-bold text-slate-900 dark:text-white">
                        <p class="text-[10px] text-slate-400">+10% per witwasser specialist in de bende.</p>
                    </div>
                </div>

                <!-- 5. Respect Dominantie & Oorlogsheerschappij -->
                <div class="space-y-2 pt-2 border-t border-slate-100 dark:border-slate-800/80">
                    <div class="flex items-center justify-between text-xs font-bold">
                        <span class="text-slate-700 dark:text-slate-300">
                            <i class="fa-solid fa-star text-amber-500 mr-1"></i> Bende Respect (+1% per 100k, max 50%)
                        </span>
                        <span class="text-amber-600 dark:text-amber-400 font-mono font-black" x-text="formatNumber(respect) + ' respect (+ ' + respectBonusPct + '%)'"></span>
                    </div>
                    <input type="range" min="0" max="5000000" step="50000" x-model.number="respect" class="w-full accent-amber-500 cursor-pointer">

                    <div class="pt-2 flex items-center justify-between bg-purple-50/60 dark:bg-purple-950/30 border border-purple-200/80 dark:border-purple-800/60 rounded-xl p-3">
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-trophy text-amber-500"></i>
                            <div>
                                <span class="text-xs font-bold text-slate-800 dark:text-slate-200 block">Kliniek Heerschappij (+25% Bendeoorlog Bonus)</span>
                                <span class="text-[10px] text-slate-500 dark:text-slate-400">7 dagen lang na winst van een 24-uurs bendeoorlog.</span>
                            </div>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" x-model="hasWarDominance" class="sr-only peer">
                            <div class="w-9 h-5 bg-slate-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-purple-600"></div>
                        </label>
                    </div>
                </div>

                <!-- 6. Persoonlijke Speler Instellingen -->
                <div class="pt-2 border-t border-slate-100 dark:border-slate-800/80 space-y-3">
                    <h3 class="text-xs font-black text-slate-900 dark:text-white uppercase tracking-wider flex items-center gap-1.5">
                        <i class="fa-solid fa-user text-purple-600 dark:text-purple-400"></i>
                        <span>Persoonlijke Rang & Level van Lid</span>
                    </h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between text-xs font-bold">
                                <span class="text-slate-700 dark:text-slate-300">Speler Level (+2.5%/lvl)</span>
                                <span class="text-purple-600 dark:text-purple-400 font-mono font-black" x-text="'Lvl ' + charLevel + ' (+' + levelBonusPct + '%)'"></span>
                            </div>
                            <input type="range" min="1" max="100" step="1" x-model.number="charLevel" class="w-full accent-purple-600 cursor-pointer">
                        </div>

                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">Bende Rang</label>
                            <select x-model="charRole" class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-xs font-bold text-slate-900 dark:text-white">
                                <option value="member">Bendelid (Basis 100%)</option>
                                <option value="officer">Officier (+10% Bonus)</option>
                                <option value="co_leader">Co-Leider (+15% Bonus)</option>
                                <option value="leader">Bendeleider / Boss (+25% Bonus)</option>
                            </select>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Results Output Column (5 cols) -->
            <div class="lg:col-span-5 space-y-5">
                
                <!-- Main Result Card -->
                <div class="bg-gradient-to-br from-slate-900 via-purple-950 to-slate-900 border border-purple-800/50 rounded-2xl p-6 text-white shadow-xl relative overflow-hidden">
                    <div class="absolute -right-12 -top-12 w-48 h-48 bg-emerald-500/20 rounded-full blur-3xl pointer-events-none"></div>

                    <div class="flex items-center justify-between mb-4">
                        <span class="text-xs font-black uppercase tracking-wider text-purple-300 flex items-center gap-1.5">
                            <i class="fa-solid fa-chart-line text-emerald-400"></i>
                            <span>Berekend Persoonlijk Uurloon</span>
                        </span>
                        <span class="px-2.5 py-0.5 rounded-full bg-emerald-500/20 border border-emerald-500/40 text-emerald-300 font-black text-[11px] font-mono">
                            Live Uitkomst
                        </span>
                    </div>

                    <div class="text-3xl sm:text-4xl font-black text-emerald-400 font-mono tracking-tight my-2">
                        +€<span x-text="formatNumber(personalWage)"></span><span class="text-sm text-slate-300 font-bold">/uur</span>
                    </div>

                    <p class="text-xs text-purple-200/80 mb-6 leading-relaxed">
                        Dit salaris wordt elk heel uur automatisch bijgeschreven op je bankrekening zolang je actief deelneemt in de bende.
                    </p>

                    <!-- Period Breakdowns -->
                    <div class="space-y-3 pt-4 border-t border-purple-800/60 text-xs">
                        <div class="flex items-center justify-between py-1">
                            <span class="text-purple-200">Gezamenlijk Bende Uurloon:</span>
                            <span class="font-mono font-bold text-white" x-text="'€' + formatNumber(collectiveWage) + '/u'"></span>
                        </div>
                        <div class="flex items-center justify-between py-1">
                            <span class="text-purple-200">Per 24 Uur (Dagbuit):</span>
                            <span class="font-mono font-black text-amber-300" x-text="'€' + formatNumber(dailyWage)"></span>
                        </div>
                        <div class="flex items-center justify-between py-1">
                            <span class="text-purple-200">Per Week (7 Dagen):</span>
                            <span class="font-mono font-black text-emerald-300" x-text="'€' + formatNumber(weeklyWage)"></span>
                        </div>
                        <div class="flex items-center justify-between py-1">
                            <span class="text-purple-200">Per Maand (30 Dagen):</span>
                            <span class="font-mono font-black text-purple-300" x-text="'€' + formatNumber(dailyWage * 30)"></span>
                        </div>
                    </div>
                </div>

                <!-- Multiplier Breakdown Card -->
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-xs space-y-3.5">
                    <h3 class="text-xs font-black uppercase tracking-wider text-slate-900 dark:text-white flex items-center gap-2">
                        <i class="fa-solid fa-calculator text-purple-600 dark:text-purple-400"></i>
                        <span>Hoe de Formule Rekent</span>
                    </h3>

                    <div class="space-y-2 text-xs divide-y divide-slate-100 dark:divide-slate-800">
                        <div class="pt-2 flex items-center justify-between">
                            <span class="text-slate-600 dark:text-slate-400">Basisbedrag (Kiosken + Panden + Garage + NPCs):</span>
                            <span class="font-mono font-bold text-slate-900 dark:text-white" x-text="'€' + formatNumber(subtotalBase)"></span>
                        </div>
                        <div class="pt-2 flex items-center justify-between">
                            <span class="text-slate-600 dark:text-slate-400">Totale Bende Multiplier:</span>
                            <span class="font-mono font-bold text-purple-600 dark:text-purple-400" x-text="'+' + totalBonusPct + '% (' + (1 + totalBonusPct/100).toFixed(2) + 'x)'"></span>
                        </div>
                        <div class="pt-2 flex items-center justify-between">
                            <span class="text-slate-600 dark:text-slate-400">Speler Level Bonus (Level <span x-text="charLevel"></span>):</span>
                            <span class="font-mono font-bold text-emerald-600 dark:text-emerald-400" x-text="'+' + levelBonusPct + '% (' + levelMultiplier.toFixed(3) + 'x)'"></span>
                        </div>
                        <div class="pt-2 flex items-center justify-between">
                            <span class="text-slate-600 dark:text-slate-400">Bende Rang Bonus:</span>
                            <span class="font-mono font-bold text-amber-600 dark:text-amber-400" x-text="(roleMultiplier === 1.25 ? 'Leider (+25%)' : (roleMultiplier === 1.15 ? 'Co-Leider (+15%)' : (roleMultiplier === 1.10 ? 'Officier (+10%)' : 'Lid (+0%)')))"></span>
                        </div>
                    </div>

                    <div class="p-3 rounded-xl bg-purple-50/70 dark:bg-purple-950/40 border border-purple-200/60 dark:border-purple-800/60 text-[11px] text-purple-800 dark:text-purple-300 leading-relaxed">
                        <strong>Transparante Economie:</strong> Percentagebonussen van luxe panden, respect en oorlogen tellen additief bij elkaar op in één bende-multiplier. Daarna worden jouw persoonlijke level- en rangbonussen toegepast.
                    </div>
                </div>

            </div>

        </div>
    </div>

    @if($inGang)
    <!-- TAB 2: LIVE SPECIFICATION (MY GANG BREAKDOWN) -->
    <div x-show="activeTab === 'breakdown'" class="space-y-6">
        
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-xs space-y-6">
            
            <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800 flex-wrap gap-2">
                <div>
                    <h2 class="text-lg font-black text-slate-900 dark:text-white flex items-center gap-2">
                        <i class="fa-solid fa-receipt text-purple-600 dark:text-purple-400"></i>
                        <span>Live Specificatie Bende: [{{ $gang->tag }}] {{ $gang->name }}</span>
                    </h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                        Exacte opbouw van elke euro die op dit moment binnenkomt in jullie organisatie.
                    </p>
                </div>
                <div class="text-right">
                    <span class="text-xs text-slate-400 font-bold block">Collectief Basis Uurloon</span>
                    <span class="text-xl font-black text-slate-900 dark:text-white font-mono">€{{ number_format($breakdown['collective_hourly_wage'] ?? 0, 0, ',', '.') }}/u</span>
                </div>
            </div>

            <!-- Inkomsten Componenten Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                
                <!-- 1. Smokkelkiosken -->
                <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/60 space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-extrabold text-slate-700 dark:text-slate-300 flex items-center gap-1.5">
                            <i class="fa-solid fa-store text-purple-500"></i>
                            <span>Smokkelkiosken</span>
                        </span>
                        <span class="text-[10px] font-bold text-slate-400 font-mono">{{ $breakdown['kiosks_count'] ?? 0 }} / 1.000</span>
                    </div>
                    <div class="text-xl font-black text-emerald-600 dark:text-emerald-400 font-mono">
                        +€{{ number_format($breakdown['kiosks_income'] ?? 0, 0, ',', '.') }}/u
                    </div>
                    <p class="text-[10px] text-slate-500 dark:text-slate-400">Vaste €50 per kiosk per uur (maximaal 1.000 stuks).</p>
                </div>

                <!-- 2. Panden & Vastgoed -->
                <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/60 space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-extrabold text-slate-700 dark:text-slate-300 flex items-center gap-1.5">
                            <i class="fa-solid fa-building text-purple-500"></i>
                            <span>Bende Panden</span>
                        </span>
                        <span class="text-[10px] font-bold text-slate-400 font-mono">{{ $properties->count() }} panden</span>
                    </div>
                    <div class="text-xl font-black text-emerald-600 dark:text-emerald-400 font-mono">
                        +€{{ number_format($breakdown['properties_income'] ?? 0, 0, ',', '.') }}/u
                    </div>
                    <p class="text-[10px] text-slate-500 dark:text-slate-400">Plús +{{ $breakdown['luxury_bonus_pct'] ?? 0 }}% luxe procentuele bonus.</p>
                </div>

                <!-- 3. Garage Wagenpark -->
                <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/60 space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-extrabold text-slate-700 dark:text-slate-300 flex items-center gap-1.5">
                            <i class="fa-solid fa-warehouse text-purple-500"></i>
                            <span>Bende Garage</span>
                        </span>
                        <span class="text-[10px] font-bold text-slate-400 font-mono">{{ $storedVehicles->count() }} voertuigen</span>
                    </div>
                    <div class="text-xl font-black text-emerald-600 dark:text-emerald-400 font-mono">
                        +€{{ number_format($breakdown['vehicles_income'] ?? 0, 0, ',', '.') }}/u
                    </div>
                    <p class="text-[10px] text-slate-500 dark:text-slate-400">24/7 logistieke opbrengst geschaald naar conditie.</p>
                </div>

                <!-- 4. Gehuurde NPCs -->
                <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/60 space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-extrabold text-slate-700 dark:text-slate-300 flex items-center gap-1.5">
                            <i class="fa-solid fa-users text-purple-500"></i>
                            <span>Personeel (NPCs)</span>
                        </span>
                        <span class="text-[10px] font-bold text-slate-400 font-mono">{{ $npcs->count() }} specialisten</span>
                    </div>
                    <div class="text-xl font-black text-emerald-600 dark:text-emerald-400 font-mono">
                        +€{{ number_format($breakdown['npcs_income'] ?? 0, 0, ',', '.') }}/u
                    </div>
                    <p class="text-[10px] text-slate-500 dark:text-slate-400">Plús +{{ $breakdown['npc_bonus_pct'] ?? 0 }}% uurloonbonus van witwassers.</p>
                </div>

            </div>

            <!-- Multipliers Summary Bar -->
            <div class="p-5 rounded-2xl bg-purple-50/60 dark:bg-purple-950/30 border border-purple-200/80 dark:border-purple-800/60 space-y-3">
                <div class="flex items-center justify-between flex-wrap gap-2">
                    <span class="text-xs font-black uppercase tracking-wider text-purple-900 dark:text-purple-200 flex items-center gap-2">
                        <i class="fa-solid fa-bolt text-amber-500"></i>
                        <span>Totale Bende Vermenigvuldigers (+{{ $breakdown['total_bonus_pct'] ?? 0 }}%)</span>
                    </span>
                    <span class="text-xs font-mono font-black text-purple-700 dark:text-purple-300">
                        Factor: {{ number_format(1 + (($breakdown['total_bonus_pct'] ?? 0) / 100), 2, ',', '.') }}x
                    </span>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
                    <div class="p-2.5 rounded-xl bg-white/80 dark:bg-slate-900/80 border border-purple-200/60 dark:border-purple-900/60">
                        <span class="text-[10px] text-slate-400 uppercase block font-bold">Luxe Panden</span>
                        <span class="font-mono font-black text-purple-700 dark:text-purple-300">+{{ $breakdown['luxury_bonus_pct'] ?? 0 }}%</span>
                    </div>
                    <div class="p-2.5 rounded-xl bg-white/80 dark:bg-slate-900/80 border border-purple-200/60 dark:border-purple-900/60">
                        <span class="text-[10px] text-slate-400 uppercase block font-bold">Respect ({{ number_format($gang->respect ?? 0, 0, ',', '.') }})</span>
                        <span class="font-mono font-black text-amber-600 dark:text-amber-400">+{{ $breakdown['respect_bonus_pct'] ?? 0 }}%</span>
                    </div>
                    <div class="p-2.5 rounded-xl bg-white/80 dark:bg-slate-900/80 border border-purple-200/60 dark:border-purple-900/60">
                        <span class="text-[10px] text-slate-400 uppercase block font-bold">Witwasser NPCs</span>
                        <span class="font-mono font-black text-emerald-600 dark:text-emerald-400">+{{ $breakdown['npc_bonus_pct'] ?? 0 }}%</span>
                    </div>
                    <div class="p-2.5 rounded-xl bg-white/80 dark:bg-slate-900/80 border border-purple-200/60 dark:border-purple-900/60">
                        <span class="text-[10px] text-slate-400 uppercase block font-bold">Oorlogs Heerschappij</span>
                        <span class="font-mono font-black text-blue-600 dark:text-blue-400">+{{ $breakdown['war_dominance_pct'] ?? 0 }}%</span>
                    </div>
                </div>
            </div>

            <!-- Persoonlijk Lid Voordeel -->
            <div class="p-5 rounded-2xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h3 class="text-sm font-black text-slate-900 dark:text-white flex items-center gap-2">
                        <i class="fa-solid fa-id-badge text-purple-600 dark:text-purple-400"></i>
                        <span>Jouw Persoonlijke Salarisuitkering ({{ $character->name }})</span>
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                        Rang: <strong>{{ $breakdown['role_name'] ?? 'Bendelid' }}</strong> &bull; Level: <strong>Level {{ $character->level }} (+{{ $breakdown['level_bonus_pct'] ?? 0 }}%)</strong>
                    </p>
                </div>

                <div class="text-right">
                    <span class="text-[10px] uppercase font-bold text-slate-400 block">Jouw Netto Uurloon</span>
                    <span class="text-2xl font-black text-emerald-600 dark:text-emerald-400 font-mono">+€{{ number_format($hourlyWage, 0, ',', '.') }}/u</span>
                </div>
            </div>

        </div>

    </div>
    @endif

</div>
@endsection
