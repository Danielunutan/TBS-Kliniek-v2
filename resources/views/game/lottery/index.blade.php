@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- Top Jackpot Hero Banner (Polished Light & Clean Dark Mode, Solid Colors, No Gradients) -->
    <div class="relative rounded-2xl bg-white dark:bg-slate-900 p-6 sm:p-8 border border-slate-200/90 dark:border-slate-800 shadow-xs text-slate-900 dark:text-white">
        <div class="flex flex-col lg:flex-row items-center justify-between gap-6">
            <div class="space-y-2.5 text-center lg:text-left">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-50 dark:bg-amber-950/60 border border-amber-200/80 dark:border-amber-800/70 text-amber-800 dark:text-amber-300 text-xs font-black uppercase tracking-wider">
                    <i class="fa-solid fa-crown text-amber-500"></i>
                    <span>TBS Afdelingsloterij &bull; 14-Daagse Ronde #{{ $round->round_number }}</span>
                </div>
                <h1 class="text-2xl sm:text-4xl font-black tracking-tight text-slate-900 dark:text-white">
                    Grijp de <span class="text-amber-500 dark:text-amber-400">Kliniek Jackpot!</span>
                </h1>
                <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 max-w-xl leading-relaxed font-medium">
                    Iedere cent bank opnamebelasting én leningtax stroomt rechtstreeks in deze pot. Koop loten met contant geld of sterren en maak kans op de volledige pot bij de 14-daagse trekking!
                </p>

                <!-- Tax and Payout info pills -->
                <div class="flex flex-wrap items-center justify-center lg:justify-start gap-3 pt-1">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70 text-xs font-bold text-slate-700 dark:text-slate-200">
                        <i class="fa-solid fa-receipt text-amber-500"></i>
                        <span>Tax in deze pot: <strong class="text-slate-900 dark:text-white">€{{ number_format($round->tax_collected, 0, ',', '.') }}</strong></span>
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70 text-xs font-bold text-slate-700 dark:text-slate-200">
                        <i class="fa-solid fa-ticket text-emerald-600 dark:text-emerald-400"></i>
                        <span>Totaal verkochte loten: <strong class="text-slate-900 dark:text-white">{{ number_format($round->total_tickets_sold, 0, ',', '.') }}</strong></span>
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-sky-50 dark:bg-sky-950/60 border border-sky-200/80 dark:border-sky-800/70 text-xs font-bold text-sky-800 dark:text-sky-300">
                        <i class="fa-solid fa-scale-balanced text-sky-500"></i>
                        <span>Uitbetaling: <strong>30% Bank</strong> / <strong>70% Contant</strong></span>
                    </span>
                </div>
            </div>

            <!-- Jackpot Box & Countdown (Solid Card) -->
            <div class="bg-amber-50/80 dark:bg-slate-800/80 border-2 border-amber-300/80 dark:border-amber-500/50 rounded-2xl p-6 sm:p-7 text-center shrink-0 w-full lg:w-80 shadow-xs flex flex-col items-center">
                <span class="text-[10px] font-black uppercase tracking-widest text-amber-800 dark:text-amber-400 mb-1">HUIDIGE JACKPOT</span>
                <div class="text-3xl sm:text-4xl font-black font-mono tracking-tight text-amber-600 dark:text-amber-400 py-1">
                    €{{ number_format($round->jackpot, 0, ',', '.') }}
                </div>

                <!-- Timer without floating point decimals -->
                @php
                    $secondsLeft = max(0, (int) round(now()->diffInSeconds($round->draws_at, false)));
                @endphp
                <div class="mt-3 pt-3 border-t border-amber-200 dark:border-slate-700 w-full"
                     x-data="{
                         left: {{ $secondsLeft }},
                         format() {
                             if (this.left <= 0) return 'Trekking bezig...';
                             const total = Math.floor(this.left);
                             const d = Math.floor(total / 86400);
                             const h = Math.floor((total % 86400) / 3600);
                             const m = Math.floor((total % 3600) / 60);
                             const s = Math.floor(total % 60);
                             if (d > 0) {
                                 return `${d}d ${h}u ${m}m ${s < 10 ? '0' : ''}${s}s`;
                             }
                             return `${h}u ${m}m ${s < 10 ? '0' : ''}${s}s`;
                         },
                         init() {
                             setInterval(() => { if (this.left > 0) this.left--; }, 1000);
                         }
                     }">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 block">Tijd tot trekking</span>
                    <span class="text-sm font-extrabold text-slate-900 dark:text-white font-mono mt-0.5 block" x-text="format()">
                        {{ now()->diffForHumans($round->draws_at, true) }}
                    </span>
                    @if($isLastQuarter)
                        <span class="inline-flex items-center gap-1 mt-2 px-2 py-0.5 rounded-md bg-rose-500 text-white font-black text-[10px] tracking-wide animate-pulse">
                            🔥 LAATSTE KWARTIER: 50% KORTING!
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if($isLastQuarter)
    <!-- Flash Deal Banner: Last Quarter 50% Discount Active -->
    <div class="rounded-2xl bg-amber-500/10 dark:bg-amber-950/30 border border-amber-400/80 dark:border-amber-500/60 p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4 shadow-xs">
        <div class="flex items-center gap-3.5">
            <div class="w-12 h-12 rounded-xl bg-amber-500 text-slate-950 flex items-center justify-center text-xl font-black shrink-0 shadow-xs">
                <i class="fa-solid fa-bolt"></i>
            </div>
            <div>
                <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-rose-600 text-white text-[10px] font-black uppercase tracking-wider">
                    ⚡ LAATSTE KWARTIER ACTIE
                </div>
                <h3 class="text-sm sm:text-base font-black text-slate-900 dark:text-white mt-1">
                    50% Korting op alle Loterijloten is NU Actief!
                </h3>
                <p class="text-xs text-slate-600 dark:text-slate-300">
                    Er is minder dan 15 minuten tot de ontknoping. Pak extra loten voor slechts <strong class="text-emerald-600 dark:text-emerald-400">€5.000</strong> per lot (normaal €10.000). Bij de start van de nieuwe ronde geldt weer het standaardtijdperk!
                </p>
            </div>
        </div>
        <div class="shrink-0 self-start sm:self-center">
            <span class="px-4 py-2 rounded-xl bg-amber-500 text-slate-950 font-black text-xs shadow-xs inline-block">
                50% PRIJSVERLAGING TOEGEPAST
            </span>
        </div>
    </div>
    @endif

    <!-- MAIN TWO COLUMN: BUY TICKETS & USER TICKETS -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

        <!-- Left: Buy Tickets Card (7 Cols) -->
        <div class="lg:col-span-7 bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-6 shadow-xs space-y-6">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-4">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl bg-purple-50 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400 border border-purple-200/80 dark:border-purple-800/70 flex items-center justify-center font-bold">
                        <i class="fa-solid fa-cart-shopping"></i>
                    </div>
                    <div>
                        <h2 class="text-sm font-extrabold uppercase tracking-wide text-slate-900 dark:text-slate-100">Loten Kopen</h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Vergroot je winkans op de hoofdprijs</p>
                    </div>
                </div>

                <!-- Balance badges -->
                <div class="flex items-center gap-2">
                    <span class="text-[11px] font-bold px-2.5 py-1 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-800 dark:text-slate-200 border border-slate-200/80 dark:border-slate-700">
                        <i class="fa-solid fa-money-bill-wave text-emerald-500 mr-1"></i> €{{ number_format($character->cash, 0, ',', '.') }}
                    </span>
                    <span class="text-[11px] font-bold px-2.5 py-1 rounded-xl bg-amber-50 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 border border-amber-200/80 dark:border-amber-800/70">
                        <i class="fa-solid fa-star text-amber-500 mr-1"></i> {{ number_format($character->stars ?? 0, 0, ',', '.') }}
                    </span>
                </div>
            </div>

            <!-- TAB 1: Kopen met Cash -->
            <div class="p-5 rounded-2xl bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70 space-y-3">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1">
                    <div class="flex items-center gap-2">
                        <span class="w-7 h-7 rounded-lg bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-xs font-bold">€</span>
                        <h3 class="text-xs font-extrabold uppercase tracking-wider text-slate-900 dark:text-slate-100">Kopen met Contant Geld</h3>
                    </div>
                    <span class="text-xs font-bold text-slate-500 dark:text-slate-400">
                        @if($isLastQuarter)
                            <span class="line-through text-slate-400 mr-1">€10.000</span>
                            <span class="text-emerald-600 dark:text-emerald-400 font-black">€{{ number_format($cashPricePerTicket, 0, ',', '.') }} per lot</span>
                            <span class="text-rose-500 text-[10px] font-black uppercase bg-rose-100 dark:bg-rose-950 px-1.5 py-0.5 rounded ml-1">-50%</span>
                        @else
                            €{{ number_format($cashPricePerTicket, 0, ',', '.') }} per lot (80% naar pot)
                        @endif
                    </span>
                </div>

                @php
                    $isLotteryBlocked = $character->isImprisoned() || $character->isComatose();
                @endphp
                <form action="{{ route('lottery.buy') }}" method="POST" class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 pt-1">
                    @csrf
                    <input type="hidden" name="currency" value="cash">

                    <button type="submit" name="quantity" value="1" 
                            {{ $isLotteryBlocked || $character->cash < ($cashPricePerTicket * 1) ? 'disabled' : '' }}
                            class="p-3 rounded-xl border border-slate-200/80 dark:border-slate-700 bg-white dark:bg-slate-800 hover:border-purple-500 dark:hover:border-purple-400 transition-all duration-150 active:scale-95 text-center group cursor-pointer shadow-xs {{ $isLotteryBlocked || $character->cash < ($cashPricePerTicket * 1) ? 'opacity-40 cursor-not-allowed' : '' }}">
                        <span class="block text-xs font-black text-slate-900 dark:text-slate-100">1 Lot</span>
                        <span class="block text-[11px] text-emerald-600 dark:text-emerald-400 font-bold mt-0.5">
                            € {{ number_format($cashPricePerTicket * 1, 0, ',', '.') }}
                            @if($isLastQuarter)
                                <span class="line-through text-slate-400 text-[9px] block">€ 10.000</span>
                            @endif
                        </span>
                    </button>

                    <button type="submit" name="quantity" value="5" 
                            {{ $isLotteryBlocked || $character->cash < ($cashPricePerTicket * 5) ? 'disabled' : '' }}
                            class="p-3 rounded-xl border border-slate-200/80 dark:border-slate-700 bg-white dark:bg-slate-800 hover:border-purple-500 dark:hover:border-purple-400 transition-all duration-150 active:scale-95 text-center group cursor-pointer shadow-xs {{ $isLotteryBlocked || $character->cash < ($cashPricePerTicket * 5) ? 'opacity-40 cursor-not-allowed' : '' }}">
                        <span class="block text-xs font-black text-slate-900 dark:text-slate-100">5 Loten</span>
                        <span class="block text-[11px] text-emerald-600 dark:text-emerald-400 font-bold mt-0.5">
                            € {{ number_format($cashPricePerTicket * 5, 0, ',', '.') }}
                            @if($isLastQuarter)
                                <span class="line-through text-slate-400 text-[9px] block">€ 50.000</span>
                            @endif
                        </span>
                    </button>

                    <button type="submit" name="quantity" value="10" 
                            {{ $isLotteryBlocked || $character->cash < ($cashPricePerTicket * 10) ? 'disabled' : '' }}
                            class="p-3 rounded-xl border border-slate-200/80 dark:border-slate-700 bg-white dark:bg-slate-800 hover:border-purple-500 dark:hover:border-purple-400 transition-all duration-150 active:scale-95 text-center group cursor-pointer shadow-xs {{ $isLotteryBlocked || $character->cash < ($cashPricePerTicket * 10) ? 'opacity-40 cursor-not-allowed' : '' }}">
                        <span class="block text-xs font-black text-slate-900 dark:text-slate-100">10 Loten</span>
                        <span class="block text-[11px] text-emerald-600 dark:text-emerald-400 font-bold mt-0.5">
                            € {{ number_format($cashPricePerTicket * 10, 0, ',', '.') }}
                            @if($isLastQuarter)
                                <span class="line-through text-slate-400 text-[9px] block">€ 100.000</span>
                            @endif
                        </span>
                    </button>

                    <button type="submit" name="quantity" value="25" 
                            {{ $isLotteryBlocked || $character->cash < ($cashPricePerTicket * 25) ? 'disabled' : '' }}
                            class="p-3 rounded-xl border border-purple-500/80 dark:border-purple-500 bg-purple-50 dark:bg-purple-950/40 hover:bg-purple-100 dark:hover:bg-purple-900/40 transition-all duration-150 active:scale-95 text-center group cursor-pointer shadow-xs {{ $isLotteryBlocked || $character->cash < ($cashPricePerTicket * 25) ? 'opacity-40 cursor-not-allowed' : '' }}">
                        <span class="block text-xs font-black text-purple-950 dark:text-purple-100">25 Loten</span>
                        <span class="block text-[11px] text-purple-700 dark:text-purple-300 font-bold mt-0.5">
                            € {{ number_format($cashPricePerTicket * 25, 0, ',', '.') }}
                            @if($isLastQuarter)
                                <span class="line-through text-slate-400 text-[9px] block">€ 250.000</span>
                            @endif
                        </span>
                    </button>
                </form>
            </div>

            <!-- TAB 2: Kopen met VIP Sterren -->
            <div class="p-5 rounded-2xl bg-amber-50/80 dark:bg-slate-800/60 border border-amber-200/80 dark:border-amber-700/50 space-y-3">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1">
                    <div class="flex items-center gap-2">
                        <span class="w-7 h-7 rounded-lg bg-amber-500/20 text-amber-600 dark:text-amber-400 flex items-center justify-center text-xs font-bold">★</span>
                        <h3 class="text-xs font-extrabold uppercase tracking-wider text-slate-900 dark:text-slate-100">Kopen met VIP Sterren</h3>
                    </div>
                    <span class="text-xs font-bold text-amber-700 dark:text-amber-300">
                        @if($isLastQuarter)
                            <span class="line-through text-slate-400 mr-1">15 Sterren</span>
                            <span class="font-black text-amber-600 dark:text-amber-400">{{ $starsPricePerTicket }} Sterren / lot</span>
                            <span class="text-rose-500 text-[10px] font-black uppercase bg-rose-100 dark:bg-rose-950 px-1.5 py-0.5 rounded ml-1">-50%</span>
                        @else
                            {{ $starsPricePerTicket }} Sterren / lot (+€25.000 jackpot bonus!)
                        @endif
                    </span>
                </div>

                <form action="{{ route('lottery.buy') }}" method="POST" class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 pt-1">
                    @csrf
                    <input type="hidden" name="currency" value="stars">

                    <button type="submit" name="quantity" value="1" 
                            {{ $isLotteryBlocked || ($character->stars ?? 0) < ($starsPricePerTicket * 1) ? 'disabled' : '' }}
                            class="p-3 rounded-xl border border-amber-200/80 dark:border-slate-700 bg-white dark:bg-slate-800 hover:border-amber-400 transition-all duration-150 active:scale-95 text-center group cursor-pointer shadow-xs {{ $isLotteryBlocked || ($character->stars ?? 0) < ($starsPricePerTicket * 1) ? 'opacity-40 cursor-not-allowed' : '' }}">
                        <span class="block text-xs font-black text-slate-900 dark:text-slate-100">1 VIP Lot</span>
                        <span class="block text-[11px] text-amber-600 dark:text-amber-400 font-bold mt-0.5">
                            {{ $starsPricePerTicket * 1 }} Sterren
                            @if($isLastQuarter)
                                <span class="line-through text-slate-400 text-[9px] block">15 Sterren</span>
                            @endif
                        </span>
                    </button>

                    <button type="submit" name="quantity" value="5" 
                            {{ $isLotteryBlocked || ($character->stars ?? 0) < ($starsPricePerTicket * 5) ? 'disabled' : '' }}
                            class="p-3 rounded-xl border border-amber-200/80 dark:border-slate-700 bg-white dark:bg-slate-800 hover:border-amber-400 transition-all duration-150 active:scale-95 text-center group cursor-pointer shadow-xs {{ $isLotteryBlocked || ($character->stars ?? 0) < ($starsPricePerTicket * 5) ? 'opacity-40 cursor-not-allowed' : '' }}">
                        <span class="block text-xs font-black text-slate-900 dark:text-slate-100">5 VIP Loten</span>
                        <span class="block text-[11px] text-amber-600 dark:text-amber-400 font-bold mt-0.5">
                            {{ $starsPricePerTicket * 5 }} Sterren
                            @if($isLastQuarter)
                                <span class="line-through text-slate-400 text-[9px] block">75 Sterren</span>
                            @endif
                        </span>
                    </button>

                    <button type="submit" name="quantity" value="10" 
                            {{ $isLotteryBlocked || ($character->stars ?? 0) < ($starsPricePerTicket * 10) ? 'disabled' : '' }}
                            class="p-3 rounded-xl border border-amber-200/80 dark:border-slate-700 bg-white dark:bg-slate-800 hover:border-amber-400 transition-all duration-150 active:scale-95 text-center group cursor-pointer shadow-xs {{ $isLotteryBlocked || ($character->stars ?? 0) < ($starsPricePerTicket * 10) ? 'opacity-40 cursor-not-allowed' : '' }}">
                        <span class="block text-xs font-black text-slate-900 dark:text-slate-100">10 VIP Loten</span>
                        <span class="block text-[11px] text-amber-600 dark:text-amber-400 font-bold mt-0.5">
                            {{ $starsPricePerTicket * 10 }} Sterren
                            @if($isLastQuarter)
                                <span class="line-through text-slate-400 text-[9px] block">150 Sterren</span>
                            @endif
                        </span>
                    </button>

                    <button type="submit" name="quantity" value="25" 
                            {{ $isLotteryBlocked || ($character->stars ?? 0) < ($starsPricePerTicket * 25) ? 'disabled' : '' }}
                            class="p-3 rounded-xl border border-amber-400 bg-amber-100/80 dark:bg-amber-950/50 hover:bg-amber-200/80 dark:hover:bg-amber-900/50 transition-all duration-150 active:scale-95 text-center group cursor-pointer shadow-xs {{ $isLotteryBlocked || ($character->stars ?? 0) < ($starsPricePerTicket * 25) ? 'opacity-40 cursor-not-allowed' : '' }}">
                        <span class="block text-xs font-black text-amber-950 dark:text-amber-100">25 VIP Loten</span>
                        <span class="block text-[11px] text-amber-800 dark:text-amber-300 font-bold mt-0.5">
                            {{ $starsPricePerTicket * 25 }} Sterren
                            @if($isLastQuarter)
                                <span class="line-through text-slate-400 text-[9px] block">375 Sterren</span>
                            @endif
                        </span>
                    </button>
                </form>
            </div>
        </div>

        <!-- Right: My Active Tickets & Win Chance (5 Cols) -->
        <div class="lg:col-span-5 bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-6 shadow-xs space-y-5">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-4">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-ticket-simple text-purple-600 dark:text-purple-400"></i>
                    <h3 class="text-sm font-extrabold uppercase tracking-wide text-slate-900 dark:text-slate-100">Jouw Loten</h3>
                </div>
                <span class="text-xs font-black px-2.5 py-1 rounded-full bg-purple-50 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 border border-purple-200/80 dark:border-purple-800/70">
                    {{ $userTicketsCount }} in bezit
                </span>
            </div>

            <!-- Win Chance Meter -->
            <div class="p-4 rounded-xl bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70 space-y-2">
                <div class="flex items-center justify-between text-xs">
                    <span class="font-bold text-slate-600 dark:text-slate-300">Jouw Winkans</span>
                    <span class="font-black font-mono text-purple-600 dark:text-purple-400 text-sm">{{ $winChance }}%</span>
                </div>
                <div class="w-full bg-slate-200 dark:bg-slate-700 rounded-full h-2.5 overflow-hidden">
                    <div class="bg-purple-600 h-full rounded-full transition-all duration-300" style="width: {{ min(100, max(2, $winChance)) }}%"></div>
                </div>
                <span class="text-[10px] text-slate-400 block">
                    Gebaseerd op {{ $userTicketsCount }} van de {{ $round->total_tickets_sold }} totaal verkochte loten.
                </span>
            </div>

            <!-- List of Tickets -->
            <div class="space-y-2 max-h-60 overflow-y-auto pr-1">
                @forelse($userTickets as $ticket)
                    <div class="flex items-center justify-between p-2.5 rounded-xl border border-slate-200/80 dark:border-slate-700/70 bg-slate-50/90 dark:bg-slate-800/50 text-xs font-mono">
                        <span class="font-black text-slate-900 dark:text-slate-100 flex items-center gap-1.5">
                            <i class="fa-solid fa-ticket text-purple-500 text-[10px]"></i>
                            <span>{{ $ticket->ticket_number }}</span>
                        </span>
                        <span class="text-[10px] uppercase font-bold px-2 py-0.5 rounded-full {{ $ticket->currency === 'stars' ? 'bg-amber-100 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300' : ($ticket->currency === 'ads' ? 'bg-purple-100 dark:bg-purple-950/60 text-purple-800 dark:text-purple-300 border border-purple-200 dark:border-purple-800/60' : 'bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300') }}">
                            {{ $ticket->currency === 'stars' ? '★ VIP' : ($ticket->currency === 'ads' ? '📺 GRATIS AD' : '€ CASH') }}
                        </span>
                    </div>
                @empty
                    <div class="text-center py-8 text-slate-400">
                        <i class="fa-solid fa-ticket text-3xl mb-2 opacity-40"></i>
                        <p class="text-xs">Je hebt nog geen loten voor ronde #{{ $round->round_number }}.</p>
                    </div>
                @endforelse
            </div>
        </div>

    </div>

    <!-- RECENT JACKPOT WINNERS -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-6 shadow-xs space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-trophy text-amber-500"></i>
                <h3 class="text-sm font-extrabold uppercase tracking-wide text-slate-900 dark:text-slate-100">Recente Jackpot Winnaars</h3>
            </div>
            <span class="text-xs text-slate-400">Vorige rondes</span>
        </div>

        @if($pastRounds->isEmpty())
            <p class="text-xs text-slate-500 dark:text-slate-400 py-4 text-center">
                Ronde #1 is momenteel actief! De eerste winnaar wordt bij de volgende trekking bekendgemaakt.
            </p>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                @foreach($pastRounds as $past)
                    <div class="p-4 rounded-xl border border-slate-200/80 dark:border-slate-700/70 bg-slate-50/90 dark:bg-slate-800/60 space-y-1.5">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-black uppercase text-purple-600 dark:text-purple-400">Ronde #{{ $past->round_number }}</span>
                            <span class="text-[10px] text-slate-400">{{ $past->draws_at?->format('d-m-Y H:i') }}</span>
                        </div>
                        <div class="text-sm font-black text-slate-900 dark:text-slate-100 truncate">
                            {{ $past->winner?->display_name ?? 'Geen winnaar' }}
                        </div>
                        <div class="text-xs font-black font-mono text-emerald-600 dark:text-emerald-400">
                            €{{ number_format($past->winner_payout ?? 0, 0, ',', '.') }} gewonnen
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

</div>
@endsection
