@extends('layouts.app')

@section('content')
<div class="space-y-6">

<div class="space-y-6">

    <!-- Top Jackpot Hero Banner (Polished Light & Clean Dark Mode, Solid Colors, No Gradients) -->
    <div class="relative rounded-2xl bg-white dark:bg-[#111827] p-6 sm:p-8 border border-slate-200 dark:border-slate-800 shadow-xs text-slate-900 dark:text-white">
        <div class="flex flex-col lg:flex-row items-center justify-between gap-6">
            <div class="space-y-2.5 text-center lg:text-left">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800/60 text-amber-800 dark:text-amber-300 text-xs font-black uppercase tracking-wider">
                    <i class="fa-solid fa-crown text-amber-500"></i>
                    <span>TBS Afdelingsloterij &bull; 14-Daagse Ronde #{{ $round->round_number }}</span>
                </div>
                <h1 class="text-2xl sm:text-4xl font-black tracking-tight text-slate-900 dark:text-white">
                    Grijp de <span class="text-amber-500 dark:text-amber-400">Kliniek Jackpot!</span>
                </h1>
                <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 max-w-xl leading-relaxed font-medium">
                    Iedere cent bank opnamebelasting én leningtax stroomt rechtstreeks in deze pot. Koop loten met contant geld of sterren en maak kans op de volledige pot bij de 14-daagse trekking!
                </p>

                <!-- Tax info pill -->
                <div class="flex flex-wrap items-center justify-center lg:justify-start gap-3 pt-1">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-bold text-slate-700 dark:text-slate-200">
                        <i class="fa-solid fa-receipt text-amber-500"></i>
                        <span>Tax in deze pot: <strong class="text-slate-900 dark:text-white">€{{ number_format($round->tax_collected, 0, ',', '.') }}</strong></span>
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-bold text-slate-700 dark:text-slate-200">
                        <i class="fa-solid fa-ticket text-emerald-600 dark:text-emerald-400"></i>
                        <span>Totaal verkochte loten: <strong class="text-slate-900 dark:text-white">{{ number_format($round->total_tickets_sold, 0, ',', '.') }}</strong></span>
                    </span>
                </div>
            </div>

            <!-- Jackpot Box & Countdown (Solid Card) -->
            <div class="bg-amber-50/80 dark:bg-slate-800 border-2 border-amber-300 dark:border-amber-500/50 rounded-2xl p-6 sm:p-7 text-center shrink-0 w-full lg:w-80 shadow-xs flex flex-col items-center">
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
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 block">Tijd tot trekking (1x per 14 dagen)</span>
                    <span class="text-sm font-extrabold text-slate-900 dark:text-white font-mono mt-0.5 block" x-text="format()">
                        {{ now()->diffForHumans($round->draws_at, true) }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- MAIN TWO COLUMN: BUY TICKETS & USER TICKETS -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

        <!-- Left: Buy Tickets Card (7 Cols) -->
        <div class="lg:col-span-7 bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-xs space-y-6">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-4">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl bg-purple-50 dark:bg-slate-800 text-purple-600 dark:text-purple-400 flex items-center justify-center font-bold">
                        <i class="fa-solid fa-cart-shopping"></i>
                    </div>
                    <div>
                        <h2 class="text-sm font-extrabold uppercase tracking-wide text-slate-900 dark:text-slate-100">Loten Kopen</h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Vergroot je winkans op de hoofdprijs</p>
                    </div>
                </div>

                <!-- Balance badges -->
                <div class="flex items-center gap-2">
                    <span class="text-[11px] font-bold px-2.5 py-1 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-800 dark:text-slate-200 border border-slate-200 dark:border-slate-700">
                        <i class="fa-solid fa-money-bill-wave text-emerald-500 mr-1"></i> €{{ number_format($character->cash, 0, ',', '.') }}
                    </span>
                    <span class="text-[11px] font-bold px-2.5 py-1 rounded-xl bg-amber-50 dark:bg-amber-950/40 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-700/50">
                        <i class="fa-solid fa-star text-amber-500 mr-1"></i> {{ number_format($character->stars ?? 0, 0, ',', '.') }}
                    </span>
                </div>
            </div>

            <!-- TAB 1: Kopen met Cash -->
            <div class="p-5 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700/60 space-y-3">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="w-7 h-7 rounded-lg bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-xs font-bold">€</span>
                        <h3 class="text-xs font-extrabold uppercase tracking-wider text-slate-900 dark:text-slate-100">Kopen met Contant Geld</h3>
                    </div>
                    <span class="text-xs font-bold text-slate-500 dark:text-slate-400">€10.000 per lot (80% naar pot)</span>
                </div>

                <form action="{{ route('lottery.buy') }}" method="POST" class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 pt-1">
                    @csrf
                    <input type="hidden" name="currency" value="cash">

                    <button type="submit" name="quantity" value="1" 
                            class="p-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:border-purple-500 dark:hover:border-purple-400 transition text-center group cursor-pointer shadow-2xs">
                        <span class="block text-xs font-black text-slate-900 dark:text-slate-100">1 Lot</span>
                        <span class="block text-[11px] text-emerald-600 dark:text-emerald-400 font-bold mt-0.5">€ 10.000</span>
                    </button>

                    <button type="submit" name="quantity" value="5" 
                            class="p-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:border-purple-500 dark:hover:border-purple-400 transition text-center group cursor-pointer shadow-2xs">
                        <span class="block text-xs font-black text-slate-900 dark:text-slate-100">5 Loten</span>
                        <span class="block text-[11px] text-emerald-600 dark:text-emerald-400 font-bold mt-0.5">€ 50.000</span>
                    </button>

                    <button type="submit" name="quantity" value="10" 
                            class="p-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:border-purple-500 dark:hover:border-purple-400 transition text-center group cursor-pointer shadow-2xs">
                        <span class="block text-xs font-black text-slate-900 dark:text-slate-100">10 Loten</span>
                        <span class="block text-[11px] text-emerald-600 dark:text-emerald-400 font-bold mt-0.5">€ 100.000</span>
                    </button>

                    <button type="submit" name="quantity" value="25" 
                            class="p-3 rounded-xl border border-purple-500 dark:border-purple-400 bg-purple-50 dark:bg-purple-950/40 hover:bg-purple-100 dark:hover:bg-purple-900/40 transition text-center group cursor-pointer shadow-2xs">
                        <span class="block text-xs font-black text-purple-950 dark:text-purple-100">25 Loten</span>
                        <span class="block text-[11px] text-purple-700 dark:text-purple-300 font-bold mt-0.5">€ 250.000</span>
                    </button>
                </form>
            </div>

            <!-- TAB 2: Kopen met VIP Sterren -->
            <div class="p-5 rounded-2xl bg-amber-50/70 dark:bg-amber-950/20 border border-amber-200 dark:border-amber-800/40 space-y-3">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="w-7 h-7 rounded-lg bg-amber-500/20 text-amber-600 dark:text-amber-400 flex items-center justify-center text-xs font-bold">★</span>
                        <h3 class="text-xs font-extrabold uppercase tracking-wider text-slate-900 dark:text-slate-100">Kopen met VIP Sterren</h3>
                    </div>
                    <span class="text-xs font-bold text-amber-700 dark:text-amber-300">15 Sterren / lot (+€25.000 jackpot bonus!)</span>
                </div>

                <form action="{{ route('lottery.buy') }}" method="POST" class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 pt-1">
                    @csrf
                    <input type="hidden" name="currency" value="stars">

                    <button type="submit" name="quantity" value="1" 
                            class="p-3 rounded-xl border border-amber-200 dark:border-amber-700/50 bg-white dark:bg-slate-800 hover:border-amber-400 transition text-center group cursor-pointer shadow-2xs">
                        <span class="block text-xs font-black text-slate-900 dark:text-slate-100">1 VIP Lot</span>
                        <span class="block text-[11px] text-amber-600 dark:text-amber-400 font-bold mt-0.5">15 Sterren</span>
                    </button>

                    <button type="submit" name="quantity" value="5" 
                            class="p-3 rounded-xl border border-amber-200 dark:border-amber-700/50 bg-white dark:bg-slate-800 hover:border-amber-400 transition text-center group cursor-pointer shadow-2xs">
                        <span class="block text-xs font-black text-slate-900 dark:text-slate-100">5 VIP Loten</span>
                        <span class="block text-[11px] text-amber-600 dark:text-amber-400 font-bold mt-0.5">75 Sterren</span>
                    </button>

                    <button type="submit" name="quantity" value="10" 
                            class="p-3 rounded-xl border border-amber-200 dark:border-amber-700/50 bg-white dark:bg-slate-800 hover:border-amber-400 transition text-center group cursor-pointer shadow-2xs">
                        <span class="block text-xs font-black text-slate-900 dark:text-slate-100">10 VIP Loten</span>
                        <span class="block text-[11px] text-amber-600 dark:text-amber-400 font-bold mt-0.5">150 Sterren</span>
                    </button>

                    <button type="submit" name="quantity" value="25" 
                            class="p-3 rounded-xl border border-amber-400 bg-amber-100 dark:bg-amber-900/40 hover:bg-amber-200 dark:hover:bg-amber-800/40 transition text-center group cursor-pointer shadow-2xs">
                        <span class="block text-xs font-black text-amber-950 dark:text-amber-100">25 VIP Loten</span>
                        <span class="block text-[11px] text-amber-800 dark:text-amber-300 font-bold mt-0.5">375 Sterren</span>
                    </button>
                </form>
            </div>

            <!-- TAB 3: Gratis Loten via Sponsor Advertenties (15 Ads = 1 Lot) -->
            <div class="p-5 rounded-2xl bg-gradient-to-br from-purple-500/10 via-indigo-500/5 to-transparent border-2 border-purple-300 dark:border-purple-700/60 space-y-3 relative overflow-hidden">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="w-7 h-7 rounded-lg bg-purple-600 text-white flex items-center justify-center text-xs font-bold shadow-2xs">
                            <i class="fa-solid fa-rectangle-ad"></i>
                        </span>
                        <div>
                            <h3 class="text-xs font-black uppercase tracking-wider text-slate-900 dark:text-slate-100 flex items-center gap-2">
                                <span>Gratis Loterij Ticket</span>
                                <span class="px-1.5 py-0.2 rounded bg-emerald-100 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300 text-[9px] font-black uppercase">Onbeperkt Herhaalbaar</span>
                            </h3>
                        </div>
                    </div>
                    <span class="text-xs font-bold text-purple-700 dark:text-purple-300">15 Ads = 1 Gratis Lot</span>
                </div>

                <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed">
                    Bekijk 15 korte sponsor video's (15 sec) en ontvang direct een officieel Loterij Ticket voor de 14-daagse trekking. Je mag dit zo vaak herhalen als je wilt!
                </p>

                <!-- Progress Bar -->
                <div class="space-y-1.5 pt-1">
                    <div class="flex items-center justify-between text-xs font-extrabold">
                        <span class="text-slate-500 dark:text-slate-400">Jouw Voortgang:</span>
                        <span class="text-purple-600 dark:text-purple-400 font-mono">
                            {{ $character->ad_lottery_progress ?? 0 }} / 15 Ads Bekeken
                        </span>
                    </div>
                    <div class="w-full bg-slate-200 dark:bg-slate-700 rounded-full h-3 overflow-hidden p-0.5 border border-slate-300 dark:border-slate-600">
                        <div class="bg-gradient-to-r from-purple-600 to-indigo-500 h-full rounded-full transition-all duration-300 shadow-xs" 
                             style="width: {{ min(100, max(2, (($character->ad_lottery_progress ?? 0) / 15) * 100)) }}%;"></div>
                    </div>
                </div>

                <div class="pt-2">
                    <button type="button" 
                            onclick="if(window.tbsOpenSponsorAd) { window.tbsOpenSponsorAd('lottery_ticket', () => window.location.reload()); } else { window.location.href = '{{ route('sponsor.index') }}'; }" 
                            class="w-full py-3 px-4 rounded-xl bg-purple-600 hover:bg-purple-700 active:scale-98 text-white font-black text-xs uppercase tracking-wider transition shadow-md shadow-purple-900/20 flex items-center justify-center gap-2 cursor-pointer">
                        <i class="fa-solid fa-play"></i>
                        <span>Kijk Sponsor Ad (+1/15) &raquo;</span>
                        @if(($character->ad_lottery_progress ?? 0) >= 14)
                            <span class="ml-1 px-1.5 py-0.5 rounded bg-amber-400 text-slate-950 text-[10px] font-black animate-pulse">Claim Lot!</span>
                        @endif
                    </button>
                </div>
            </div>
        </div>

        <!-- Right: My Active Tickets & Win Chance (5 Cols) -->
        <div class="lg:col-span-5 bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-xs space-y-5">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-4">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-ticket-simple text-purple-600 dark:text-purple-400"></i>
                    <h3 class="text-sm font-extrabold uppercase tracking-wide text-slate-900 dark:text-slate-100">Jouw Loten</h3>
                </div>
                <span class="text-xs font-black px-2.5 py-1 rounded-full bg-purple-100 dark:bg-slate-800 text-purple-800 dark:text-purple-300">
                    {{ $userTicketsCount }} in bezit
                </span>
            </div>

            <!-- Win Chance Meter -->
            <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700/60 space-y-2">
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
                    <div class="flex items-center justify-between p-2.5 rounded-xl border border-slate-200 dark:border-slate-700/60 bg-slate-50/50 dark:bg-slate-800/40 text-xs font-mono">
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
    <div class="bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-xs space-y-4">
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
                    <div class="p-4 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/40 space-y-1.5">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-black uppercase text-purple-600 dark:text-purple-400">Ronde #{{ $past->round_number }}</span>
                            <span class="text-[10px] text-slate-400">{{ $past->draws_at?->format('d-m-Y H:i') }}</span>
                        </div>
                        <div class="text-sm font-black text-slate-900 dark:text-slate-100 truncate">
                            {{ $past->winner ? $past->winner->display_name : 'Geen winnaar' }}
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

</div>
@endsection
