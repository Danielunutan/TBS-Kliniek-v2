@extends('layouts.app')

@section('title', 'Zwarte Markt & Productie - TBS Kliniek')

@section('content')
<div class="space-y-6">

    <!-- Top Subterranean Workshop Hero -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-5 sm:p-7 shadow-xs relative overflow-hidden">
        <div class="absolute -right-10 -bottom-10 w-48 h-48 bg-purple-500/5 dark:bg-purple-500/10 rounded-full blur-2xl pointer-events-none"></div>
        <div class="absolute -left-10 -top-10 w-48 h-48 bg-emerald-500/5 dark:bg-emerald-500/10 rounded-full blur-2xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-5">
            <div class="flex items-start gap-4">
                <div class="w-13 h-13 sm:w-15 sm:h-15 rounded-2xl bg-gradient-to-br from-purple-600 to-indigo-600 text-white flex items-center justify-center text-2xl shrink-0 shadow-md shadow-purple-600/20">
                    <i class="fa-solid fa-industry"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2 mb-1.5 flex-wrap">
                        <span class="text-[10px] font-black uppercase tracking-wider text-purple-700 dark:text-purple-300 bg-purple-50 dark:bg-purple-950/60 px-2.5 py-0.5 rounded-md border border-purple-200/80 dark:border-purple-800/70">
                            <i class="fa-solid fa-flask-vial mr-1"></i> Ondergronds Smokkelnetwerk
                        </span>
                        <span class="text-[10px] font-extrabold text-emerald-700 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-950/60 px-2.5 py-0.5 rounded-md border border-emerald-200/80 dark:border-emerald-800/70 inline-flex items-center gap-1">
                            <i class="fa-solid fa-arrow-trend-up text-[9px]"></i> +5% Rank XP Per Deal
                        </span>
                        <span class="text-[10px] font-extrabold text-amber-800 dark:text-amber-300 bg-amber-50 dark:bg-amber-950/60 px-2.5 py-0.5 rounded-md border border-amber-200/80 dark:border-amber-800/70">
                            Direct Wit Bankgeld
                        </span>
                    </div>
                    <h1 class="text-xl sm:text-2xl md:text-3xl font-black tracking-tight text-slate-900 dark:text-white">
                        Productiekelder & Zwarte Markt
                    </h1>
                    <p class="text-slate-500 dark:text-slate-400 text-xs sm:text-sm mt-1 max-w-xl leading-relaxed">
                        Draai sigaretten van gesmokkelde shag, ritsel zware ritalin strips en smokkel mini-smartphones binnen. Verkoop je voorraad direct aan patiënten en bewakers voor contant geld en respect!
                    </p>
                </div>
            </div>

            <!-- Header Vitals Matrix -->
            <div class="grid grid-cols-2 sm:grid-cols-4 md:flex items-center gap-2 shrink-0">
                <div class="bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200/80 dark:border-emerald-800/70 rounded-xl px-3.5 py-2 text-center shadow-xs">
                    <span class="text-[9px] font-black uppercase tracking-wider text-emerald-700 dark:text-emerald-400 block">Energie</span>
                    <span class="text-xs sm:text-sm font-black text-emerald-900 dark:text-emerald-300 font-mono">
                        {{ $character->energy }}<span class="text-[10px] text-emerald-600/80 font-semibold">/{{ $character->max_energy }}</span>
                    </span>
                </div>
                <div class="bg-amber-50 dark:bg-amber-950/60 border border-amber-200/80 dark:border-amber-800/70 rounded-xl px-3.5 py-2 text-center shadow-xs">
                    <span class="text-[9px] font-black uppercase tracking-wider text-amber-700 dark:text-amber-400 block">Contant</span>
                    <span class="text-xs sm:text-sm font-black text-amber-900 dark:text-amber-300 font-mono">
                        €{{ $character->cash >= 1000000 ? number_format($character->cash / 1000000, 1) . 'M' : number_format($character->cash, 0, ',', '.') }}
                    </span>
                </div>
                <div class="bg-purple-50 dark:bg-purple-950/60 border border-purple-200/80 dark:border-purple-800/70 rounded-xl px-3.5 py-2 text-center shadow-xs">
                    <span class="text-[9px] font-black uppercase tracking-wider text-purple-700 dark:text-purple-400 block">Respect</span>
                    <span class="text-xs sm:text-sm font-black text-purple-900 dark:text-purple-300 font-mono">
                        {{ number_format($character->respect, 0, ',', '.') }}
                    </span>
                </div>
                <div class="bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200/80 dark:border-slate-700 rounded-xl px-3.5 py-2 text-center shadow-xs">
                    <span class="text-[9px] font-black uppercase tracking-wider text-slate-500 dark:text-slate-400 block">Sterren</span>
                    <span class="text-xs sm:text-sm font-black text-slate-900 dark:text-white font-mono flex items-center justify-center gap-1">
                        <i class="fa-solid fa-star text-amber-500 text-[10px]"></i> {{ $character->stars ?? 0 }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Live Warehouse Inventory Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Sigaretten Stock -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-4 sm:p-5 flex items-center justify-between shadow-xs hover:border-purple-300 dark:hover:border-purple-700/80 transition-all duration-200 group">
            <div class="flex items-center gap-3.5">
                <span class="w-12 h-12 rounded-xl bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center text-xl border border-amber-200/80 dark:border-amber-800/70 shrink-0 group-hover:scale-105 transition-transform">
                    <i class="fa-solid fa-smoking"></i>
                </span>
                <div>
                    <span class="text-[10px] font-black uppercase tracking-wider text-slate-400 dark:text-slate-500 block">Gedraaide Shag</span>
                    <span class="text-xl font-black text-slate-900 dark:text-white font-mono">{{ number_format($cigarettes, 0, ',', '.') }}</span>
                    <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400 block">stuks op voorraad</span>
                </div>
            </div>
            <span class="text-[10px] font-black px-2.5 py-1 rounded-xl bg-amber-50 dark:bg-amber-950/60 border border-amber-200/80 dark:border-amber-800/70 text-amber-800 dark:text-amber-300">
                {{ $cigarettes > 0 ? 'Verkoopklaar' : 'Opgebruikt' }}
            </span>
        </div>

        <!-- Ritalin Stock -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-4 sm:p-5 flex items-center justify-between shadow-xs hover:border-purple-300 dark:hover:border-purple-700/80 transition-all duration-200 group">
            <div class="flex items-center gap-3.5">
                <span class="w-12 h-12 rounded-xl bg-purple-50 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400 flex items-center justify-center text-xl border border-purple-200/80 dark:border-purple-800/70 shrink-0 group-hover:scale-105 transition-transform">
                    <i class="fa-solid fa-pills"></i>
                </span>
                <div>
                    <span class="text-[10px] font-black uppercase tracking-wider text-slate-400 dark:text-slate-500 block">Medicatie Buit</span>
                    <span class="text-xl font-black text-slate-900 dark:text-white font-mono">{{ number_format($ritalin, 0, ',', '.') }}</span>
                    <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400 block">strips Ritalin</span>
                </div>
            </div>
            <span class="text-[10px] font-black px-2.5 py-1 rounded-xl bg-purple-50 dark:bg-purple-950/60 border border-purple-200/80 dark:border-purple-800/70 text-purple-700 dark:text-purple-300">
                {{ $ritalin > 0 ? 'Verkoopklaar' : 'Opgebruikt' }}
            </span>
        </div>

        <!-- Nederwiet Stock -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-4 sm:p-5 flex items-center justify-between shadow-xs hover:border-emerald-300 dark:hover:border-emerald-700/80 transition-all duration-200 group">
            <div class="flex items-center gap-3.5">
                <span class="w-12 h-12 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-xl border border-emerald-200/80 dark:border-emerald-800/70 shrink-0 group-hover:scale-105 transition-transform">
                    <i class="fa-solid fa-cannabis"></i>
                </span>
                <div>
                    <span class="text-[10px] font-black uppercase tracking-wider text-slate-400 dark:text-slate-500 block">Nederwiet Teelt</span>
                    <span class="text-xl font-black text-slate-900 dark:text-white font-mono">{{ number_format($weed ?? 0, 0, ',', '.') }}</span>
                    <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400 block">toppen voorraad</span>
                </div>
            </div>
            <span class="text-[10px] font-black px-2.5 py-1 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200/80 dark:border-emerald-800/70 text-emerald-700 dark:text-emerald-300">
                {{ ($weed ?? 0) > 0 ? 'Verkoopklaar' : 'Opgebruikt' }}
            </span>
        </div>

        <!-- Telefoons Stock -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-4 sm:p-5 flex items-center justify-between shadow-xs hover:border-purple-300 dark:hover:border-purple-700/80 transition-all duration-200 group">
            <div class="flex items-center gap-3.5">
                <span class="w-12 h-12 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-xl border border-indigo-200/80 dark:border-indigo-800/70 shrink-0 group-hover:scale-105 transition-transform">
                    <i class="fa-solid fa-mobile-screen"></i>
                </span>
                <div>
                    <span class="text-[10px] font-black uppercase tracking-wider text-slate-400 dark:text-slate-500 block">Smokkel Hardware</span>
                    <span class="text-xl font-black text-slate-900 dark:text-white font-mono">{{ number_format($phones, 0, ',', '.') }}</span>
                    <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400 block">mini toestellen</span>
                </div>
            </div>
            <span class="text-[10px] font-black px-2.5 py-1 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 border border-indigo-200/80 dark:border-indigo-800/70 text-indigo-700 dark:text-indigo-300">
                {{ $phones > 0 ? 'Verkoopklaar' : 'Opgebruikt' }}
            </span>
        </div>
    </div>

    <!-- MAIN TWO COLUMN FACILITY LAYOUT -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        
        <!-- ========================================================================= -->
        <!-- LEFT: PRODUCTIEKELDER UNITS                                               -->
        <!-- ========================================================================= -->
        <div class="lg:col-span-6 bg-white dark:bg-slate-900 rounded-2xl shadow-xs border border-slate-200/90 dark:border-slate-800 overflow-hidden flex flex-col justify-between space-y-4 p-5 sm:p-6">
            <div>
                <div class="border-b border-slate-100 dark:border-slate-800 pb-3 flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-purple-100 dark:bg-purple-950/70 text-purple-600 dark:text-purple-400 flex items-center justify-center text-sm font-black">
                            <i class="fa-solid fa-screwdriver-wrench"></i>
                        </div>
                        <div>
                            <h2 class="text-xs sm:text-sm font-black uppercase tracking-wide text-slate-900 dark:text-white">
                                Productiewerkplaats
                            </h2>
                            <span class="text-[10px] text-slate-400 font-bold">Draai, Druk & Onderschep Voorraden</span>
                        </div>
                    </div>
                    <span class="text-xs font-black text-emerald-700 dark:text-emerald-400 font-mono">
                        {{ $character->energy }}⚡ Beschikbaar
                    </span>
                </div>
                
                <div class="space-y-3 pt-4">
                    <!-- 1. Sigaretten (5 min cooldown) -->
                    @php
                        $sigCooldown = $character->cooldownRemaining('produce_sigaretten');
                    @endphp
                    <div class="p-4 border border-slate-200/80 dark:border-slate-700/70 rounded-xl bg-slate-50/90 dark:bg-slate-800/60 flex flex-col sm:flex-row sm:items-center justify-between gap-3 hover:border-purple-300 dark:hover:border-purple-700/80 transition-all duration-200">
                        <div class="flex items-center gap-3">
                            <span class="w-11 h-11 rounded-xl bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 border border-amber-200/80 dark:border-amber-800/70 flex items-center justify-center text-base shrink-0 font-bold">
                                <i class="fa-solid fa-smoking"></i>
                            </span>
                            <div>
                                <h4 class="font-black text-slate-900 dark:text-white text-xs sm:text-sm">10x Peuken Rollen</h4>
                                <span class="text-[11px] text-slate-500 dark:text-slate-400 font-bold inline-flex items-center gap-1.5 flex-wrap mt-0.5">
                                    <span class="text-emerald-700 dark:text-emerald-400">5⚡ Energie</span> &bull; <span class="text-purple-600 dark:text-purple-400">+35 XP</span> &bull; <span><i class="fa-solid fa-clock text-amber-500"></i> 5 min</span>
                                </span>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 shrink-0" x-data="{
                            cd: {{ (int) $sigCooldown }},
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
                            <div x-show="cd > 0" class="flex items-center gap-1.5" style="{{ $sigCooldown > 0 ? '' : 'display: none;' }}">
                                <span class="text-[11px] font-black text-amber-800 dark:text-amber-300 bg-amber-50 dark:bg-amber-950/60 border border-amber-200/80 dark:border-amber-800/70 px-2.5 py-1 rounded-xl">
                                    <i class="fa-regular fa-clock mr-1"></i><span class="font-mono" x-text="format(cd)">{{ gmdate('i:s', $sigCooldown) }}</span>
                                </span>
                                <form method="POST" action="{{ route('cooldown.skip') }}" class="inline-block">
                                    @csrf
                                    <input type="hidden" name="action" value="produce_sigaretten">
                                    <button type="submit" @disabled($character->isImprisoned() || $character->isComatose()) title="Omzeil cooldown met 20 Sterren" class="btn-star-gold px-3.5 py-1.5 active:scale-95 text-slate-900 dark:text-white font-black rounded-xl text-xs shadow-xs transition-all duration-150 cursor-pointer inline-flex items-center gap-1.5 disabled:opacity-50 disabled:cursor-not-allowed">
                                        <i class="fa-solid fa-star text-[11px]"></i>
                                        <span>Omzeilen (20 ⭐)</span>
                                    </button>
                                </form>
                            </div>
                            <div x-show="cd <= 0" style="{{ $sigCooldown <= 0 ? '' : 'display: none;' }}">
                                <form action="{{ route('smuggle.produce') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="type" value="sigaretten">
                                    <button type="submit" 
                                            @if($character->energy < 5 || $character->isImprisoned() || $character->isComatose()) disabled @endif
                                            class="bg-purple-600 hover:bg-purple-700 dark:bg-purple-600 dark:hover:bg-purple-500 active:scale-95 disabled:bg-slate-100 disabled:dark:bg-slate-800/80 disabled:text-slate-400 disabled:dark:text-slate-500 disabled:border disabled:border-slate-200/80 disabled:dark:border-slate-700/60 disabled:cursor-not-allowed disabled:shadow-none text-white text-xs font-black px-4 py-2.5 rounded-xl transition-all duration-150 shadow-xs cursor-pointer flex items-center justify-center gap-2">
                                        <i class="fa-solid fa-gear text-[11px]"></i>
                                        <span>Produceren</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- 2. Ritalin (10 min cooldown) -->
                    @php
                        $ritCooldown = $character->cooldownRemaining('produce_ritalin');
                    @endphp
                    <div class="p-4 border border-slate-200/80 dark:border-slate-700/70 rounded-xl bg-slate-50/90 dark:bg-slate-800/60 flex flex-col sm:flex-row sm:items-center justify-between gap-3 hover:border-purple-300 dark:hover:border-purple-700/80 transition-all duration-200">
                        <div class="flex items-center gap-3">
                            <span class="w-11 h-11 rounded-xl bg-purple-50 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400 border border-purple-200/80 dark:border-purple-800/70 flex items-center justify-center text-base shrink-0 font-bold">
                                <i class="fa-solid fa-pills"></i>
                            </span>
                            <div>
                                <h4 class="font-black text-slate-900 dark:text-white text-xs sm:text-sm">5x Ritalin Strips Onderscheppen</h4>
                                <span class="text-[11px] text-slate-500 dark:text-slate-400 font-bold inline-flex items-center gap-1.5 flex-wrap mt-0.5">
                                    <span class="text-emerald-700 dark:text-emerald-400">10⚡ Energie</span> &bull; <span class="text-purple-600 dark:text-purple-400">+65 XP</span> &bull; <span><i class="fa-solid fa-clock text-amber-500"></i> 10 min</span>
                                </span>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 shrink-0" x-data="{
                            cd: {{ (int) $ritCooldown }},
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
                            <div x-show="cd > 0" class="flex items-center gap-1.5" style="{{ $ritCooldown > 0 ? '' : 'display: none;' }}">
                                <span class="text-[11px] font-black text-amber-800 dark:text-amber-300 bg-amber-50 dark:bg-amber-950/60 border border-amber-200/80 dark:border-amber-800/70 px-2.5 py-1 rounded-xl">
                                    <i class="fa-regular fa-clock mr-1"></i><span class="font-mono" x-text="format(cd)">{{ gmdate('i:s', $ritCooldown) }}</span>
                                </span>
                                <form method="POST" action="{{ route('cooldown.skip') }}" class="inline-block">
                                    @csrf
                                    <input type="hidden" name="action" value="produce_ritalin">
                                    <button type="submit" @disabled($character->isImprisoned() || $character->isComatose()) title="Omzeil cooldown met 35 Sterren" class="btn-star-gold px-3.5 py-1.5 active:scale-95 text-slate-900 dark:text-white font-black rounded-xl text-xs shadow-xs transition-all duration-150 cursor-pointer inline-flex items-center gap-1.5 disabled:opacity-50 disabled:cursor-not-allowed">
                                        <i class="fa-solid fa-star text-[11px]"></i>
                                        <span>Omzeilen (35 ⭐)</span>
                                    </button>
                                </form>
                            </div>
                            <div x-show="cd <= 0" style="{{ $ritCooldown <= 0 ? '' : 'display: none;' }}">
                                <form action="{{ route('smuggle.produce') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="type" value="ritalin">
                                    <button type="submit" 
                                            @if($character->energy < 10 || $character->isImprisoned() || $character->isComatose()) disabled @endif
                                            class="bg-purple-600 hover:bg-purple-700 dark:bg-purple-600 dark:hover:bg-purple-500 active:scale-95 disabled:bg-slate-100 disabled:dark:bg-slate-800/80 disabled:text-slate-400 disabled:dark:text-slate-500 disabled:border disabled:border-slate-200/80 disabled:dark:border-slate-700/60 disabled:cursor-not-allowed disabled:shadow-none text-white text-xs font-black px-4 py-2.5 rounded-xl transition-all duration-150 shadow-xs cursor-pointer flex items-center justify-center gap-2">
                                        <i class="fa-solid fa-gear text-[11px]"></i>
                                        <span>Produceren</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- 3. Telefoon (20 min cooldown) -->
                    @php
                        $phoneCooldown = max($character->cooldownRemaining('produce_telefoon'), $character->cooldownRemaining('produce_phone'));
                    @endphp
                    <div class="p-4 border border-slate-200/80 dark:border-slate-700/70 rounded-xl bg-slate-50/90 dark:bg-slate-800/60 flex flex-col sm:flex-row sm:items-center justify-between gap-3 hover:border-purple-300 dark:hover:border-purple-700/80 transition-all duration-200">
                        <div class="flex items-center gap-3">
                            <span class="w-11 h-11 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 border border-indigo-200/80 dark:border-indigo-800/70 flex items-center justify-center text-base shrink-0 font-bold">
                                <i class="fa-solid fa-mobile-screen"></i>
                            </span>
                            <div>
                                <h4 class="font-black text-slate-900 dark:text-white text-xs sm:text-sm">1x Mini Telefoon Smokkelen</h4>
                                <span class="text-[11px] text-slate-500 dark:text-slate-400 font-bold inline-flex items-center gap-1.5 flex-wrap mt-0.5">
                                    <span class="text-emerald-700 dark:text-emerald-400">20⚡ Energie</span> &bull; <span class="text-purple-600 dark:text-purple-400">+150 XP</span> &bull; <span><i class="fa-solid fa-clock text-amber-500"></i> 20 min</span>
                                </span>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 shrink-0" x-data="{
                            cd: {{ (int) $phoneCooldown }},
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
                            <div x-show="cd > 0" class="flex items-center gap-1.5" style="{{ $phoneCooldown > 0 ? '' : 'display: none;' }}">
                                <span class="text-[11px] font-black text-amber-800 dark:text-amber-300 bg-amber-50 dark:bg-amber-950/60 border border-amber-200/80 dark:border-amber-800/70 px-2.5 py-1 rounded-xl">
                                    <i class="fa-regular fa-clock mr-1"></i><span class="font-mono" x-text="format(cd)">{{ gmdate('i:s', $phoneCooldown) }}</span>
                                </span>
                                <form method="POST" action="{{ route('cooldown.skip') }}" class="inline-block">
                                    @csrf
                                    <input type="hidden" name="action" value="produce_telefoon">
                                    <button type="submit" @disabled($character->isImprisoned() || $character->isComatose()) title="Omzeil cooldown met 50 Sterren" class="btn-star-gold px-3.5 py-1.5 active:scale-95 text-slate-900 dark:text-white font-black rounded-xl text-xs shadow-xs transition-all duration-150 cursor-pointer inline-flex items-center gap-1.5 disabled:opacity-50 disabled:cursor-not-allowed">
                                        <i class="fa-solid fa-star text-[11px]"></i>
                                        <span>Omzeilen (50 ⭐)</span>
                                    </button>
                                </form>
                            </div>
                            <div x-show="cd <= 0" style="{{ $phoneCooldown <= 0 ? '' : 'display: none;' }}">
                                <form action="{{ route('smuggle.produce') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="type" value="telefoon">
                                    <button type="submit" 
                                            @if($character->energy < 20 || $character->isImprisoned() || $character->isComatose()) disabled @endif
                                            class="bg-purple-600 hover:bg-purple-700 dark:bg-purple-600 dark:hover:bg-purple-500 active:scale-95 disabled:bg-slate-100 disabled:dark:bg-slate-800/80 disabled:text-slate-400 disabled:dark:text-slate-500 disabled:border disabled:border-slate-200/80 disabled:dark:border-slate-700/60 disabled:cursor-not-allowed disabled:shadow-none text-white text-xs font-black px-4 py-2.5 rounded-xl transition-all duration-150 shadow-xs cursor-pointer flex items-center justify-center gap-2">
                                        <i class="fa-solid fa-gear text-[11px]"></i>
                                        <span>Produceren</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- 4. Nederwiet Toppen (15 min cooldown) -->
                    @php
                        $wietCooldown = $character->cooldownRemaining('produce_wiet');
                    @endphp
                    <div class="p-4 border border-slate-200/80 dark:border-slate-700/70 rounded-xl bg-slate-50/90 dark:bg-slate-800/60 flex flex-col sm:flex-row sm:items-center justify-between gap-3 hover:border-emerald-300 dark:hover:border-emerald-700/80 transition-all duration-200">
                        <div class="flex items-center gap-3">
                            <span class="w-11 h-11 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 border border-emerald-200/80 dark:border-emerald-800/70 flex items-center justify-center text-base shrink-0 font-bold">
                                <i class="fa-solid fa-cannabis"></i>
                            </span>
                            <div>
                                <h4 class="font-black text-slate-900 dark:text-white text-xs sm:text-sm">8x Nederwiet Toppen Kweken</h4>
                                <span class="text-[11px] text-slate-500 dark:text-slate-400 font-bold inline-flex items-center gap-1.5 flex-wrap mt-0.5">
                                    <span class="text-emerald-700 dark:text-emerald-400">15⚡ Energie</span> &bull; <span class="text-purple-600 dark:text-purple-400">+95 XP</span> &bull; <span><i class="fa-solid fa-clock text-amber-500"></i> 15 min</span>
                                </span>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 shrink-0" x-data="{
                            cd: {{ (int) $wietCooldown }},
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
                            <div x-show="cd > 0" class="flex items-center gap-1.5" style="{{ $wietCooldown > 0 ? '' : 'display: none;' }}">
                                <span class="text-[11px] font-black text-amber-800 dark:text-amber-300 bg-amber-50 dark:bg-amber-950/60 border border-amber-200/80 dark:border-amber-800/70 px-2.5 py-1 rounded-xl">
                                    <i class="fa-regular fa-clock mr-1"></i><span class="font-mono" x-text="format(cd)">{{ gmdate('i:s', $wietCooldown) }}</span>
                                </span>
                                <form method="POST" action="{{ route('cooldown.skip') }}" class="inline-block">
                                    @csrf
                                    <input type="hidden" name="action" value="produce_wiet">
                                    <button type="submit" @disabled($character->isImprisoned() || $character->isComatose()) title="Omzeil cooldown met 40 Sterren" class="btn-star-gold px-3.5 py-1.5 active:scale-95 text-slate-900 dark:text-white font-black rounded-xl text-xs shadow-xs transition-all duration-150 cursor-pointer inline-flex items-center gap-1.5 disabled:opacity-50 disabled:cursor-not-allowed">
                                        <i class="fa-solid fa-star text-[11px]"></i>
                                        <span>Omzeilen (40 ⭐)</span>
                                    </button>
                                </form>
                            </div>
                            <div x-show="cd <= 0" style="{{ $wietCooldown <= 0 ? '' : 'display: none;' }}">
                                <form action="{{ route('smuggle.produce') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="type" value="wiet">
                                    <button type="submit" 
                                            @if($character->energy < 15 || $character->isImprisoned() || $character->isComatose()) disabled @endif
                                            class="bg-emerald-600 hover:bg-emerald-700 dark:bg-emerald-600 dark:hover:bg-emerald-500 active:scale-95 disabled:bg-slate-100 disabled:dark:bg-slate-800/80 disabled:text-slate-400 disabled:dark:text-slate-500 disabled:border disabled:border-slate-200/80 disabled:dark:border-slate-700/60 disabled:cursor-not-allowed disabled:shadow-none text-white text-xs font-black px-4 py-2.5 rounded-xl transition-all duration-150 shadow-xs cursor-pointer flex items-center justify-center gap-2">
                                        <i class="fa-solid fa-cannabis text-[11px]"></i>
                                        <span>Kweken</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="pt-3 border-t border-slate-100 dark:border-slate-800 text-[11px] text-slate-500 dark:text-slate-400 font-medium flex items-center justify-between">
                <span>Gebruik Sterren ⭐ om cooldowns direct over te slaan!</span>
                <span class="font-mono text-amber-600 dark:text-amber-400 font-bold">⭐ Directe Doorloop</span>
            </div>
        </div>

        <!-- ========================================================================= -->
        <!-- RIGHT: VERKOPEN AAN PATIËNTEN (ORDERS)                                    -->
        <!-- ========================================================================= -->
        <div class="lg:col-span-6 bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-5 sm:p-6 shadow-xs flex flex-col justify-between space-y-4">
            <div>
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-emerald-100 dark:bg-emerald-950/70 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-sm font-black">
                            <i class="fa-solid fa-cart-shopping"></i>
                        </div>
                        <div>
                            <h2 class="text-xs sm:text-sm font-black uppercase tracking-wide text-slate-900 dark:text-white">
                                Patiënten Afzetmarkt
                            </h2>
                            <span class="text-[10px] text-slate-400 font-bold">Lever Goederen & Pak Wit Geld</span>
                        </div>
                    </div>
                    <span class="text-[11px] font-black text-purple-700 dark:text-purple-300 bg-purple-50 dark:bg-purple-950/60 border border-purple-200/80 dark:border-purple-800/70 px-2.5 py-1 rounded-full">
                        +5% Rank XP
                    </span>
                </div>

                <p class="text-xs text-slate-500 dark:text-slate-400 mt-3 leading-relaxed">
                    Voor elke verkoop pak je direct contant geld, respect én <strong>5% Rank XP</strong>. Hoe meer deals je sluit, hoe sneller je doorgroeit in niveau!
                </p>

                <!-- Deals List -->
                <div class="mt-4 space-y-3">
                    @foreach($orders as $order)
                    <div class="p-4 rounded-xl bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70 flex flex-col sm:flex-row sm:items-center justify-between gap-3.5 transition-all duration-200 hover:border-purple-300 dark:hover:border-purple-700/80">
                        <!-- Buyer info -->
                        <div class="flex items-center gap-3.5 min-w-0">
                            <img src="{{ $order['avatar'] }}" alt="{{ $order['buyer'] }}" 
                                 onerror="this.onerror=null; this.src='{{ asset('assets/characters/henkie.jpg') }}';"
                                 class="w-12 h-12 rounded-2xl object-cover border-2 border-purple-200 dark:border-purple-800 shrink-0 shadow-xs ring-2 ring-purple-50 dark:ring-purple-950">
                            <div class="min-w-0">
                                <div class="text-xs sm:text-sm font-black text-slate-900 dark:text-white truncate">
                                    {{ $order['amount'] }}x {{ strtolower($order['item_name']) }} voor <span class="text-emerald-700 dark:text-emerald-400 font-mono font-black">€{{ number_format($order['reward_cash'], 0, ',', '.') }}</span>
                                </div>
                                <div class="text-[11px] text-slate-500 dark:text-slate-400 font-bold flex items-center gap-2 mt-0.5">
                                    <span>Koper: <strong class="text-slate-700 dark:text-slate-200">{{ $order['buyer'] }}</strong></span>
                                    <span>&bull;</span>
                                    <span class="text-purple-700 dark:text-purple-400 font-black">+{{ $order['reward_respect'] }} Resp</span>
                                </div>
                            </div>
                        </div>

                        <!-- Action buttons -->
                        <div class="flex items-center sm:flex-col gap-2 shrink-0 sm:w-32">
                            <!-- Verkopen -->
                            <form action="{{ route('smuggle.sell') }}" method="POST" class="flex-1 sm:w-full">
                                @csrf
                                <input type="hidden" name="order_id" value="{{ $order['id'] }}">
                                <button type="submit" 
                                        @if(!$order['can_fulfill'] || $character->isImprisoned() || $character->isComatose()) disabled @endif
                                        class="w-full py-2.5 px-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 dark:bg-emerald-600 dark:hover:bg-emerald-500 active:scale-95 disabled:bg-slate-100 disabled:dark:bg-slate-800/80 disabled:text-slate-400 disabled:dark:text-slate-500 disabled:border disabled:border-slate-200/80 disabled:dark:border-slate-700/60 disabled:cursor-not-allowed disabled:shadow-none text-white font-black text-xs uppercase tracking-wider transition-all duration-150 shadow-xs cursor-pointer flex items-center justify-center gap-1.5">
                                    <i class="fa-solid fa-check text-[11px]"></i>
                                    <span>Verkopen</span>
                                </button>
                            </form>

                            <!-- Weigeren -->
                            <form action="{{ route('smuggle.reject') }}" method="POST" class="flex-1 sm:w-full"
                                  data-confirm="Weet je zeker dat je deze order wilt afwijzen?"
                                  data-confirm-title="Order Weigeren"
                                  data-confirm-btn="Weigeren"
                                  data-confirm-type="warning">
                                @csrf
                                <input type="hidden" name="order_id" value="{{ $order['id'] }}">
                                <button type="submit" 
                                        @disabled($character->isImprisoned() || $character->isComatose())
                                        class="w-full py-2 px-3 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 border border-slate-200/80 dark:border-slate-700 text-slate-700 dark:text-slate-200 font-bold text-[11px] transition-all duration-150 active:scale-95 cursor-pointer flex items-center justify-center gap-1 disabled:opacity-50 disabled:cursor-not-allowed">
                                    <i class="fa-solid fa-xmark text-[10px]"></i>
                                    <span>Weigeren</span>
                                </button>
                            </form>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <div class="pt-3 border-t border-slate-100 dark:border-slate-800 text-[11px] text-slate-400 flex items-center justify-between">
                <span>Na elke deal meldt zich direct een nieuwe koper!</span>
                <span class="text-purple-700 dark:text-purple-400 font-black">+5% Rank XP</span>
            </div>
        </div>

    </div>

</div>
@endsection

