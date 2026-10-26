@extends('layouts.app')

@section('title', 'Koffers & Pakketjes - TBS Kliniek')

@section('content')
@php
    $pkgCooldown = $character->cooldownRemaining('package');
@endphp

<div class="space-y-6" x-data="{
    seconds: {{ (int) $pkgCooldown }},
    format(s) {
        if (s <= 0) return '00:00';
        const m = Math.floor(s / 60);
        const sec = Math.floor(s % 60);
        return (m < 10 ? '0' : '') + m + ':' + (sec < 10 ? '0' : '') + sec;
    },
    init() {
        if (this.seconds > 0) {
            const timer = setInterval(() => {
                if (this.seconds > 0) {
                    this.seconds--;
                }
                if (this.seconds <= 0) {
                    clearInterval(timer);
                }
            }, 1000);
        }
    }
}">

    <!-- Top Interception Operation Header -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-5 sm:p-7 shadow-xs relative overflow-hidden">
        <div class="absolute -right-10 -bottom-10 w-44 h-44 bg-purple-500/5 dark:bg-purple-500/10 rounded-full blur-2xl pointer-events-none"></div>
        <div class="absolute -left-10 -top-10 w-44 h-44 bg-amber-500/5 dark:bg-amber-500/10 rounded-full blur-2xl pointer-events-none"></div>

        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-5 relative z-10">
            <div class="flex items-start gap-4">
                <div class="w-13 h-13 sm:w-15 sm:h-15 rounded-2xl bg-gradient-to-br from-purple-500 to-indigo-600 text-white flex items-center justify-center text-2xl shrink-0 shadow-md shadow-purple-500/20">
                    <i class="fa-solid fa-briefcase"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2 mb-1.5 flex-wrap">
                        <span class="text-[10px] font-black uppercase tracking-wider text-purple-700 dark:text-purple-300 bg-purple-50 dark:bg-purple-950/80 px-2.5 py-0.5 rounded-md border border-purple-200 dark:border-purple-800">
                            <i class="fa-solid fa-shield-halved mr-1"></i> Smokkelsector
                        </span>
                        <span class="text-[10px] font-extrabold text-amber-700 dark:text-amber-400 bg-amber-50 dark:bg-amber-950/80 px-2.5 py-0.5 rounded-md border border-amber-200 dark:border-amber-800 inline-flex items-center gap-1">
                            <i class="fa-solid fa-gem text-[9px]"></i> 25% Koffer Dropkans
                        </span>
                        <span class="text-[10px] font-extrabold text-emerald-700 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/80 px-2.5 py-0.5 rounded-md border border-emerald-200 dark:border-emerald-800">
                            Geheime Kluiscodes
                        </span>
                    </div>
                    <h1 class="text-xl sm:text-2xl md:text-3xl font-black text-slate-900 dark:text-white tracking-tight">
                        Smokkelkoffers & Postpakketten
                    </h1>
                    <p class="text-slate-500 dark:text-slate-400 text-xs sm:text-sm mt-1 max-w-2xl leading-relaxed">
                        Onderschep illegale pakketjes in de wandelgangen van de kliniek. Kraak gevonden smokkelkoffers voor contant bankgeld, exclusieve maskers, sterren en geheime kluiscodes!
                    </p>
                </div>
            </div>

            <!-- Header Inventory Vitals -->
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:flex items-center gap-2.5 shrink-0">
                <div class="bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-2.5 text-center shadow-xs">
                    <span class="text-[10px] font-black uppercase tracking-wider text-slate-400 dark:text-slate-500 block">Standaard Koffers</span>
                    <span class="text-sm sm:text-base font-black text-purple-600 dark:text-purple-400 font-mono flex items-center justify-center gap-1.5 mt-0.5">
                        <i class="fa-solid fa-briefcase text-xs"></i> {{ $character->unopened_bags ?? 0 }}
                    </span>
                </div>
                <div class="bg-amber-50/70 dark:bg-amber-950/40 border border-amber-200/90 dark:border-amber-800/80 rounded-xl px-4 py-2.5 text-center shadow-xs">
                    <span class="text-[10px] font-black uppercase tracking-wider text-amber-700 dark:text-amber-400 block">XXL Koffers</span>
                    <span class="text-sm sm:text-base font-black text-amber-600 dark:text-amber-300 font-mono flex items-center justify-center gap-1.5 mt-0.5">
                        <i class="fa-solid fa-crown text-xs"></i> {{ $character->xxl_cases ?? 0 }}
                    </span>
                </div>
                <div class="col-span-2 sm:col-span-1 bg-emerald-50/70 dark:bg-emerald-950/40 border border-emerald-200/90 dark:border-emerald-800/80 rounded-xl px-4 py-2.5 text-center shadow-xs">
                    <span class="text-[10px] font-black uppercase tracking-wider text-emerald-700 dark:text-emerald-400 block">Beschikbare Energie</span>
                    <span class="text-sm sm:text-base font-black text-emerald-800 dark:text-emerald-300 font-mono mt-0.5 block">
                        {{ $character->energy }} <span class="text-[11px] text-emerald-600/80 font-semibold">/ {{ $character->max_energy }}</span>
                    </span>
                </div>
            </div>
        </div>

        <!-- Live Cooldown Notice Banner -->
        <div x-show="seconds > 0" class="mt-5 p-4 rounded-xl bg-amber-500/10 dark:bg-amber-950/40 border border-amber-300 dark:border-amber-700 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3" style="{{ $pkgCooldown > 0 ? '' : 'display: none;' }}">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-amber-500 text-white flex items-center justify-center text-sm font-black shrink-0 shadow-xs">
                    <i class="fa-solid fa-clock"></i>
                </div>
                <div>
                    <h4 class="text-xs font-black text-amber-950 dark:text-amber-200">Corridor Onderschepping in Cooldown</h4>
                    <p class="text-[11px] text-amber-800 dark:text-amber-300">
                        Wacht nog <strong class="font-mono text-amber-950 dark:text-amber-200 font-bold" x-text="format(seconds)">{{ gmdate('i:s', $pkgCooldown) }}</strong> voordat bewakers weer wegkijken.
                    </p>
                </div>
            </div>
            <button type="button" onclick="window.tbsOpenSponsorAd('cooldown')" class="w-full sm:w-auto h-9 px-4 rounded-xl bg-purple-600 hover:bg-purple-700 active:scale-95 text-white text-xs font-black transition shadow-xs flex items-center justify-center gap-1.5 cursor-pointer whitespace-nowrap">
                <i class="fa-solid fa-tv text-[11px]"></i>
                <span>Kijk Ad (Direct Resetten)</span>
            </button>
        </div>
    </div>

    <!-- MAIN CRACKING BAY & LOGS SECTION -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

        <!-- ========================================================================= -->
        <!-- LEFT: CRACKING TERMINAL & XXL SHOP (5 COLS)                                -->
        <!-- ========================================================================= -->
        <div class="lg:col-span-5 space-y-5">
            
            <!-- Standard Smokkelkoffer Terminal -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-5 sm:p-6 shadow-xs relative overflow-hidden">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3 mb-4">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-purple-100 dark:bg-purple-950/70 text-purple-600 dark:text-purple-400 flex items-center justify-center text-sm font-black">
                            <i class="fa-solid fa-lock-open"></i>
                        </div>
                        <div>
                            <h2 class="text-xs sm:text-sm font-black uppercase tracking-wide text-slate-900 dark:text-white">
                                Smokkelkoffers Kraken
                            </h2>
                            <span class="text-[10px] text-slate-400 font-bold">Kraak voor buit of verkoop direct</span>
                        </div>
                    </div>
                    <span class="text-xs font-black font-mono px-2.5 py-1 rounded-full bg-purple-50 dark:bg-purple-950/80 text-purple-700 dark:text-purple-300 border border-purple-200 dark:border-purple-800">
                        {{ $character->unopened_bags ?? 0 }} Beschikbaar
                    </span>
                </div>

                @if(($character->unopened_bags ?? 0) <= 0)
                    <div class="py-7 px-4 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200/90 dark:border-slate-800 text-center space-y-2">
                        <div class="w-12 h-12 mx-auto rounded-full bg-slate-200/70 dark:bg-slate-800 text-slate-400 flex items-center justify-center text-lg">
                            <i class="fa-solid fa-briefcase"></i>
                        </div>
                        <h4 class="text-xs font-black text-slate-800 dark:text-slate-200">Geen Smokkelkoffers in Bezit</h4>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 max-w-xs mx-auto">
                            Onderschep postpakketjes in de wandelgangen hieronder om koffers en zeldzame kluiscodes binnen te halen!
                        </p>
                        <div class="mt-3 pt-3 border-t border-slate-200/60 dark:border-slate-700/60 inline-flex items-center gap-1.5 text-[11px] font-extrabold text-purple-700 dark:text-purple-300 bg-purple-50 dark:bg-purple-950/60 px-3 py-1 rounded-full border border-purple-200 dark:border-purple-800">
                            <i class="fa-solid fa-bolt text-amber-400"></i>
                            <span>Nieuw: Meerdere koffers in één klap tegelijk kraken!</span>
                        </div>
                    </div>
                @else
                    <div class="space-y-4">
                        <div class="p-4 rounded-xl bg-gradient-to-r from-purple-50 to-indigo-50/70 dark:from-purple-950/40 dark:to-indigo-950/30 border border-purple-200/90 dark:border-purple-800/60 flex items-center justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 rounded-xl bg-purple-600 text-white flex items-center justify-center text-xl shrink-0 shadow-sm shadow-purple-600/30">
                                    <i class="fa-solid fa-briefcase"></i>
                                </div>
                                <div>
                                    <div class="text-sm font-black text-slate-900 dark:text-white flex items-center gap-2">
                                        <span>{{ $character->unopened_bags }}x Smokkelkoffer gereed</span>
                                        <span class="px-2 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider bg-purple-600 text-white shadow-xs">
                                            Bulk Ready
                                        </span>
                                    </div>
                                    <div class="text-[11px] text-purple-700 dark:text-purple-300 font-bold">
                                        Buit: Tot €10.000 cash, maskers, sterren & kluizen per stuk
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Bulk kraak knop: direct prominent aanwezig --}}
                        <form action="{{ route('packages.open-bag-all') }}" method="POST">
                            @csrf
                            <button type="submit"
                                    class="w-full py-3 px-4 rounded-xl bg-gradient-to-r from-purple-600 via-indigo-600 to-purple-600 hover:from-purple-700 hover:to-indigo-700 active:scale-[0.98] text-white font-black text-xs uppercase tracking-wider transition shadow-md shadow-purple-600/30 cursor-pointer flex items-center justify-center gap-2"
                                    title="Open alle {{ $character->unopened_bags }} koffers tegelijk en ontvang alle beloningen in één keer!">
                                <i class="fa-solid fa-bolt text-amber-300"></i>
                                <span>Kraak {{ $character->unopened_bags > 50 ? '50 van je ' : 'Alle ' }}{{ min(50, $character->unopened_bags) }} Koffer{{ min(50, $character->unopened_bags) > 1 ? 's' : '' }} In Één Klap!</span>
                                <span class="px-1.5 py-0.5 rounded text-[9px] bg-amber-400 text-slate-950 font-black">BULK</span>
                            </button>
                        </form>

                        <!-- Direct Single Actions -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                            <form action="{{ route('packages.open-bag') }}" method="POST">
                                @csrf
                                <button type="submit" 
                                        class="w-full py-2.5 px-3 rounded-xl bg-purple-100 hover:bg-purple-200 dark:bg-purple-950/80 dark:hover:bg-purple-900/80 text-purple-800 dark:text-purple-200 border border-purple-300 dark:border-purple-700 active:scale-[0.98] font-black text-xs uppercase tracking-wider transition cursor-pointer flex items-center justify-center gap-2">
                                    <i class="fa-solid fa-key"></i>
                                    <span>Kraak 1 Koffer</span>
                                </button>
                            </form>
                            <form action="{{ route('packages.sell-bag') }}" method="POST">
                                @csrf
                                <input type="hidden" name="amount" value="1">
                                <button type="submit" 
                                        class="w-full py-2.5 px-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 active:scale-[0.98] text-white font-black text-xs uppercase tracking-wider transition shadow-sm shadow-emerald-600/20 cursor-pointer flex items-center justify-center gap-1.5"
                                        title="Verkoop 1 koffer direct voor €2.500 wit bankgeld">
                                    <i class="fa-solid fa-sack-dollar"></i>
                                    <span>Verkoop 1 (€2.500)</span>
                                </button>
                            </form>
                        </div>

                        @if(($character->unopened_bags ?? 0) > 1)
                        <form action="{{ route('packages.sell-bag') }}" method="POST">
                            @csrf
                            <input type="hidden" name="amount" value="{{ $character->unopened_bags }}">
                            <button type="submit"
                                    class="w-full py-2 px-3 rounded-xl bg-emerald-50 hover:bg-emerald-100 dark:bg-emerald-950/40 dark:hover:bg-emerald-950/70 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 text-[11px] font-black transition flex items-center justify-center gap-1.5 cursor-pointer">
                                <i class="fa-solid fa-money-bill-transfer"></i>
                                <span>Verkoop Alle {{ $character->unopened_bags }} Koffers Direct (€{{ number_format($character->unopened_bags * 2500, 0, ',', '.') }} bankgeld)</span>
                            </button>
                        </form>
                        @endif

                        <div class="text-center pt-1">
                            <a href="{{ route('marketplace.index') }}" class="inline-flex items-center gap-1 text-[11px] font-bold text-slate-500 dark:text-slate-400 hover:text-purple-600 dark:hover:text-purple-400 transition">
                                <i class="fa-solid fa-scale-balanced"></i>
                                <span>Verkoop tegen jouw prijs op Spelers Marktplaats &rarr;</span>
                            </a>
                        </div>
                    </div>
                @endif
            </div>

            <!-- XXL Vault Koffers (High-Roller Tier) -->
            <div class="bg-gradient-to-br from-amber-500/10 via-amber-500/5 to-transparent dark:from-amber-950/30 dark:via-amber-950/10 dark:to-transparent border-2 border-amber-300 dark:border-amber-700/80 rounded-2xl p-5 shadow-xs relative overflow-hidden">
                <div class="flex items-center justify-between gap-3 mb-2">
                    <div class="flex items-center gap-2">
                        <span class="w-8 h-8 rounded-lg bg-amber-500 text-white flex items-center justify-center text-sm font-black shadow-xs">
                            <i class="fa-solid fa-crown"></i>
                        </span>
                        <div>
                            <h3 class="text-xs sm:text-sm font-black uppercase tracking-wide text-amber-950 dark:text-amber-200">
                                XXL Kluiskoffers
                            </h3>
                            <span class="text-[10px] text-amber-800 dark:text-amber-400 font-bold">Exclusieve High-Roller Beloningen</span>
                        </div>
                    </div>
                    <span class="text-xs font-black font-mono text-amber-900 dark:text-amber-300 bg-amber-100 dark:bg-amber-900/60 border border-amber-300 dark:border-amber-700 px-3 py-1 rounded-full">
                        {{ $character->xxl_cases ?? 0 }} in bezit
                    </span>
                </div>

                <div class="p-3 rounded-xl bg-white/70 dark:bg-slate-900/70 border border-amber-200 dark:border-amber-800/60 mb-3.5">
                    <div class="text-[11px] text-slate-600 dark:text-slate-300 leading-relaxed">
                        Gigantische buitpot: tot wel <strong class="text-emerald-700 dark:text-emerald-400 font-mono font-black">€75.000 cash</strong> op je bank, <strong class="text-amber-600 dark:text-amber-300 font-mono font-black">25 Sterren</strong>, <strong class="text-purple-600 dark:text-purple-400 font-mono font-black">12 Maskers</strong> én zeldzame kluiscodes!
                    </div>
                </div>

                <div class="space-y-2">
                    @if(($character->xxl_cases ?? 0) > 0)
                    <form action="{{ route('packages.open-xxl') }}" method="POST">
                        @csrf
                        <button type="submit" 
                                class="w-full py-2.5 px-4 rounded-xl bg-amber-500 hover:bg-amber-600 active:scale-[0.98] text-white font-black text-xs uppercase tracking-wider transition shadow-sm shadow-amber-500/30 cursor-pointer flex items-center justify-center gap-2">
                            <i class="fa-solid fa-unlock-keyhole"></i>
                            <span>Kraak XXL Koffer Nu</span>
                        </button>
                    </form>
                    @endif

                    <form action="{{ route('packages.buy-xxl') }}" method="POST">
                        @csrf
                        <button type="submit" 
                                @if(($character->stars ?? 0) < 100) disabled @endif
                                class="w-full py-2 px-4 rounded-xl bg-slate-900 hover:bg-slate-800 dark:bg-slate-800 dark:hover:bg-slate-700 disabled:opacity-40 disabled:cursor-not-allowed text-white font-extrabold text-xs transition cursor-pointer flex items-center justify-center gap-2 shadow-xs">
                            <i class="fa-solid fa-star text-amber-400"></i>
                            <span>Koop XXL Koffer (100 Sterren)</span>
                        </button>
                    </form>
                </div>
            </div>

            <!-- Clandestien Master Pakket Bundle -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-5 shadow-xs">
                <div class="flex items-center justify-between gap-3 mb-2">
                    <div class="flex items-center gap-2">
                        <span class="w-7 h-7 rounded-lg bg-purple-100 dark:bg-purple-950/70 text-purple-600 dark:text-purple-400 flex items-center justify-center text-xs font-black">
                            <i class="fa-solid fa-box-open"></i>
                        </span>
                        <h4 class="text-xs font-black text-slate-900 dark:text-white uppercase tracking-wide">
                            Master Smokkelpakket
                        </h4>
                    </div>
                    <span class="text-[11px] font-black text-amber-700 dark:text-amber-300 bg-amber-100 dark:bg-amber-950/80 border border-amber-300 dark:border-amber-800 px-2.5 py-0.5 rounded-full inline-flex items-center gap-1 font-mono">
                        <i class="fa-solid fa-star text-amber-500 text-[10px]"></i> 150 Sterren
                    </span>
                </div>
                <p class="text-[11px] text-slate-500 dark:text-slate-400 mb-3.5 leading-relaxed">
                    Direct resultaat zonder wachten. Garandeert direct een massaal wit geld bedrag, zeldzame maskers en geheime kluiscodes.
                </p>
                <form action="{{ route('shop.buy-star-item') }}" method="POST">
                    @csrf
                    <input type="hidden" name="item_type" value="package_150">
                    <button type="submit" 
                            @if(($character->stars ?? 0) < 150) disabled @endif
                            class="w-full py-2.5 px-4 rounded-xl bg-purple-600 hover:bg-purple-700 active:scale-[0.98] disabled:opacity-40 disabled:cursor-not-allowed text-white font-black text-xs uppercase tracking-wider transition shadow-sm shadow-purple-600/20 cursor-pointer flex items-center justify-center gap-2">
                        <i class="fa-solid fa-bolt text-amber-300"></i>
                        <span>Ontgrendel Master Pakket</span>
                    </button>
                </form>
            </div>

        </div>

        <!-- ========================================================================= -->
        <!-- RIGHT: RECENT INTERCEPTED LOOT JOURNAL (7 COLS)                          -->
        <!-- ========================================================================= -->
        <div class="lg:col-span-7 bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-5 sm:p-6 shadow-xs flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3 mb-4">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-indigo-100 dark:bg-indigo-950/70 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-sm font-black">
                            <i class="fa-solid fa-clipboard-list"></i>
                        </div>
                        <div>
                            <h2 class="text-xs sm:text-sm font-black uppercase tracking-wide text-slate-900 dark:text-white">
                                Koffer & Buit Logboek
                            </h2>
                            <span class="text-[10px] text-slate-400 font-bold">Recente krakingen en geregistreerde beloningen</span>
                        </div>
                    </div>
                    <span class="text-[11px] font-bold text-slate-400">
                        Laatste acties
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="text-[10px] font-black uppercase tracking-wider text-slate-400 dark:text-slate-500 border-b border-slate-100 dark:border-slate-800 pb-2">
                                <th class="py-2.5 px-2">DOSSIER #</th>
                                <th class="py-2.5 px-2">BUIT SPECIFICATIE</th>
                                <th class="py-2.5 px-2 text-right">TIJDSTIP</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80 text-xs font-medium">
                            @forelse($kofferLogs ?? [] as $log)
                                <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors">
                                    <td class="py-3 px-2 font-mono font-bold text-slate-400 dark:text-slate-500 text-[11px]">
                                        #{{ 150000 + $log->id }}
                                    </td>
                                    <td class="py-3 px-2 text-slate-800 dark:text-slate-200">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            @if(str_contains($log->loot_summary, 'XXL'))
                                                <span class="px-2 py-0.5 rounded bg-amber-50 dark:bg-amber-950/60 border border-amber-200 dark:border-amber-800 text-[10px] font-black text-amber-700 dark:text-amber-300">
                                                    XXL
                                                </span>
                                            @else
                                                <span class="px-2 py-0.5 rounded bg-purple-50 dark:bg-purple-950/60 border border-purple-200 dark:border-purple-800 text-[10px] font-black text-purple-700 dark:text-purple-300">
                                                    KOFFER
                                                </span>
                                            @endif
                                            <span class="font-bold text-slate-800 dark:text-slate-100">{{ $log->loot_summary }}</span>
                                        </div>
                                    </td>
                                    <td class="py-3 px-2 text-right font-mono text-[11px] text-slate-400 dark:text-slate-500 whitespace-nowrap">
                                        {{ $log->created_at->format('d-m-Y H:i') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="py-12 text-center text-slate-400 dark:text-slate-500 text-xs italic">
                                        <div class="w-10 h-10 mx-auto rounded-full bg-slate-100 dark:bg-slate-800 text-slate-400 flex items-center justify-center text-sm mb-2">
                                            <i class="fa-solid fa-clock-rotate-left"></i>
                                        </div>
                                        Nog geen koffers gekraakt. Onderschep er een in de wandelgangen!
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="pt-4 mt-4 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-[11px] text-slate-400">
                <span>Alle buit wordt direct bijgeschreven op je profiel</span>
                <span class="font-mono font-bold text-purple-600 dark:text-purple-400">Beveiligde Transacties</span>
            </div>
        </div>

    </div>

    <!-- ========================================================================= -->
    <!-- BOTTOM SECTION: CORRIDOR INTERCEPT MISSIONS (TACTICAL OPERATIONS)         -->
    <!-- ========================================================================= -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-5 sm:p-7 shadow-xs space-y-5">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 dark:border-slate-800 pb-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-purple-100 dark:bg-purple-950/70 text-purple-600 dark:text-purple-400 flex items-center justify-center text-lg font-black shrink-0">
                    <i class="fa-solid fa-person-walking-luggage"></i>
                </div>
                <div>
                    <h3 class="text-sm sm:text-base font-black text-slate-900 dark:text-white flex items-center gap-2">
                        <span>Pakketjes Onderscheppen in de Wandelgangen</span>
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                        Kies een sector in de kliniek. Elke succesvolle actie kan een zware koffer of geheime kluiscode opleveren!
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-3 text-xs font-bold text-slate-600 dark:text-slate-300">
                <span class="px-3 py-1.5 rounded-lg bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 font-mono text-emerald-800 dark:text-emerald-300">
                    ⚡ Energie: <strong>{{ $character->energy }} / {{ $character->max_energy }}</strong>
                </span>
            </div>
        </div>

        <!-- 5 Intercept Operations Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
            @foreach($packages as $pkg)
            @php
                $hasTokenCost = ($pkg['token_cost'] ?? 0) > 0;
                $canAffordTokens = !$hasTokenCost || ($character->vault_tokens >= $pkg['token_cost']);
                $canAffordEnergy = $character->energy >= $pkg['energy'];
                $otherBlocked = $character->isImprisoned() || !$canAffordEnergy || !$canAffordTokens;
                $isDisabled = $otherBlocked || $pkgCooldown > 0;
            @endphp
            <div class="bg-slate-50/70 hover:bg-white dark:bg-slate-800/40 dark:hover:bg-slate-800/80 border border-slate-200/90 dark:border-slate-800 hover:border-purple-400 dark:hover:border-purple-600/80 rounded-2xl p-5 flex flex-col justify-between gap-4 transition shadow-xs group">
                
                <div class="space-y-3">
                    <!-- Operation Header -->
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <div class="w-11 h-11 rounded-xl bg-purple-50 dark:bg-purple-950/70 border border-purple-200/80 dark:border-purple-800/60 text-purple-600 dark:text-purple-400 flex items-center justify-center text-lg shrink-0 group-hover:scale-105 transition-transform">
                                <i class="fa-solid {{ $pkg['icon'] }}"></i>
                            </div>
                            <div>
                                <span class="text-[9px] font-black uppercase tracking-wider text-purple-600 dark:text-purple-400 block">
                                    Corridor Operatie
                                </span>
                                <h4 class="text-xs sm:text-sm font-black text-slate-900 dark:text-white leading-tight">
                                    {{ $pkg['name'] }}
                                </h4>
                            </div>
                        </div>
                        <span class="text-[10px] font-black font-mono text-purple-700 dark:text-purple-300 bg-purple-50 dark:bg-purple-950/80 border border-purple-200 dark:border-purple-800 px-2 py-0.5 rounded-md shrink-0">
                            {{ $pkg['energy'] }}⚡ EP
                        </span>
                    </div>

                    <p class="text-[11px] text-slate-500 dark:text-slate-400 leading-relaxed min-h-[32px]">
                        {{ $pkg['desc'] }}
                    </p>

                    <!-- Success Rate Progress Bar -->
                    <div class="space-y-1 pt-1">
                        <div class="flex items-center justify-between text-[11px]">
                            <span class="font-bold text-slate-500 dark:text-slate-400 flex items-center gap-1">
                                <i class="fa-solid fa-bullseye text-purple-600 dark:text-purple-400"></i> Kans op slagen:
                            </span>
                            <span class="font-black font-mono text-purple-700 dark:text-purple-300">
                                {{ $pkg['rate'] }}%
                            </span>
                        </div>
                        <div class="w-full h-2 rounded-full bg-slate-200 dark:bg-slate-700 overflow-hidden">
                            <div class="h-full rounded-full transition-all duration-500 {{ $pkg['rate'] >= 50 ? 'bg-emerald-500' : ($pkg['rate'] >= 35 ? 'bg-purple-600' : 'bg-amber-500') }}"
                                 style="width: {{ $pkg['rate'] }}%;"></div>
                        </div>
                    </div>

                    <!-- Loot & Rewards Chips -->
                    <div class="grid grid-cols-2 gap-1.5 pt-1 text-[11px]">
                        <div class="px-2.5 py-1.5 rounded-lg bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200/80 dark:border-emerald-800/60 font-black text-emerald-700 dark:text-emerald-400 font-mono flex items-center gap-1.5">
                            <i class="fa-solid fa-sack-dollar text-[10px]"></i>
                            <span>€{{ number_format($pkg['min'], 0, ',', '.') }}–€{{ number_format($pkg['max'], 0, ',', '.') }}</span>
                        </div>
                        <div class="px-2.5 py-1.5 rounded-lg bg-amber-50 dark:bg-amber-950/40 border border-amber-200/80 dark:border-amber-800/60 font-black text-amber-700 dark:text-amber-400 font-mono flex items-center gap-1.5">
                            <i class="fa-solid fa-clock text-[10px]"></i>
                            <span>{{ gmdate('i:s', $pkg['cooldown'] ?? 180) }}</span>
                        </div>
                    </div>

                    <!-- Drop Badges -->
                    <div class="flex items-center justify-between text-[10px] pt-1 border-t border-slate-200/70 dark:border-slate-800 text-slate-600 dark:text-slate-400">
                        <span class="font-bold flex items-center gap-1 text-purple-700 dark:text-purple-300">
                            <i class="fa-solid fa-briefcase text-[9px]"></i> 25% Koffer Drop
                        </span>
                        <span class="font-bold flex items-center gap-1 text-indigo-600 dark:text-indigo-400">
                            <i class="fa-solid fa-envelope-open-text text-[9px]"></i> Kluiscode Drop
                        </span>
                    </div>
                </div>

                <!-- Intercept Submit Button -->
                <form action="{{ route('packages.intercept') }}" method="POST" class="pt-2">
                    @csrf
                    <input type="hidden" name="package_id" value="{{ $pkg['id'] }}">
                    <button type="submit" 
                            :disabled="seconds > 0 || {{ $otherBlocked ? 'true' : 'false' }}"
                            :class="(seconds > 0 || {{ $otherBlocked ? 'true' : 'false' }}) ? 'opacity-40 cursor-not-allowed bg-slate-300 dark:bg-slate-800 text-slate-500' : 'bg-purple-600 hover:bg-purple-700 active:scale-[0.98] text-white cursor-pointer shadow-xs shadow-purple-600/20'"
                            {{ $isDisabled ? 'disabled' : '' }}
                            class="w-full py-2.5 px-4 rounded-xl font-black text-xs uppercase tracking-wider transition flex items-center justify-center gap-2">
                        <span class="flex items-center gap-1">
                            <i class="fa-solid fa-bolt text-[10px]"></i> {{ $pkg['energy'] }} EP
                        </span>
                        <span>&bull;</span>
                        <span>Onderscheppen</span>
                    </button>
                </form>

            </div>
            @endforeach
        </div>
    </div>

</div>
@endsection

