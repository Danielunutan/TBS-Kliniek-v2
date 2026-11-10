@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- Top Jackpot Hero Banner (Polished Light & Clean Dark Mode, Solid Colors, No Gradients) -->
    <div class="relative rounded-2xl bg-white dark:bg-slate-900 p-6 sm:p-8 border border-slate-200/90 dark:border-slate-800 shadow-xs text-slate-900 dark:text-white">
        <div class="flex flex-col lg:flex-row items-center justify-between gap-6">
            <div class="space-y-2.5 text-center lg:text-left">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-50 dark:bg-amber-950/60 border border-amber-200/80 dark:border-amber-800/70 text-amber-800 dark:text-amber-300 text-xs font-black uppercase tracking-wider">
                    <i class="fa-solid fa-crown text-amber-500"></i>
                    <span>TBS Afdelingsloterij &bull; 7-Daagse Ronde #{{ $round->round_number }}</span>
                </div>
                <h1 class="text-2xl sm:text-4xl font-black tracking-tight text-slate-900 dark:text-white">
                    Grijp de <span class="text-amber-500 dark:text-amber-400">Kliniek Jackpot!</span>
                </h1>
                <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 max-w-xl leading-relaxed font-medium">
                    Iedere cent bank opnamebelasting én leningtax stroomt rechtstreeks in deze pot. Koop loten met contant geld of sterren en maak kans op de volledige pot bij de 7-daagse trekking!
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
                         timer: null,
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
                             this.timer = setInterval(() => {
                                 if (this.left > 0) {
                                     this.left--;
                                 } else if (this.timer) {
                                     clearInterval(this.timer);
                                 }
                             }, 1000);
                         },
                         destroy() {
                             if (this.timer) clearInterval(this.timer);
                         }
                     }">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 block">Tijd tot trekking</span>
                    <span class="text-sm font-extrabold text-slate-900 dark:text-white font-mono mt-0.5 block" x-text="format()">
                        {{ now()->diffForHumans($round->draws_at, true) }}
                    </span>
                    @if($isDiscounted)
                        <span class="inline-flex items-center gap-1 mt-2 px-2 py-0.5 rounded-md bg-rose-500 text-white font-black text-[10px] tracking-wide animate-pulse">
                            🔥 50% KORTING ACTIEF &bull; 100% NAAR DE POT!
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if($isDiscounted)
    <!-- Flash Deal Banner: 50% Discount Active -->
    <div class="rounded-2xl bg-amber-500/10 dark:bg-amber-950/30 border border-amber-400/80 dark:border-amber-500/60 p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4 shadow-xs">
        <div class="flex items-center gap-3.5">
            <div class="w-12 h-12 rounded-xl bg-amber-500 text-slate-950 flex items-center justify-center text-xl font-black shrink-0 shadow-xs">
                <i class="fa-solid fa-bolt"></i>
            </div>
            <div>
                <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-rose-600 text-white text-[10px] font-black uppercase tracking-wider">
                    ⚡ LAATSTE KWARTIER KORTING
                </div>
                <h3 class="text-sm sm:text-base font-black text-slate-900 dark:text-white mt-1">
                    50% Laatste Kwartier Korting op alle Loterijloten!
                </h3>
                <p class="text-xs text-slate-600 dark:text-slate-300">
                    Pak extra loten voor slechts <strong class="text-emerald-600 dark:text-emerald-400">€5.000</strong> per lot (normaal €10.000) of <strong class="text-amber-600 dark:text-amber-400">8 VIP Sterren</strong>. Iedere cent die wordt uitgegeven stroomt 100% rechtstreeks in de megapot!
                </p>
            </div>
        </div>
        <div class="shrink-0 self-start sm:self-center">
            <span class="px-4 py-2 rounded-xl bg-amber-500 text-slate-950 font-black text-xs shadow-xs inline-block">
                50% KORTING &bull; 100% POT BOOST
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
            <div class="p-5 rounded-2xl bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70 space-y-4"
                 x-data="{
                     qty: 100,
                     price: {{ $cashPricePerTicket }},
                     userCash: {{ (int) $character->cash }},
                     get maxQty() {
                         if (this.price <= 0) return 1;
                         return Math.min(5000, Math.max(1, Math.floor(this.userCash / this.price)));
                     },
                     format(num) {
                         return new Intl.NumberFormat('nl-NL').format(Math.max(0, Math.round(num)));
                     },
                     setMax() {
                         this.qty = this.maxQty;
                     }
                 }">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1">
                    <div class="flex items-center gap-2">
                        <span class="w-7 h-7 rounded-lg bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-xs font-bold">€</span>
                        <h3 class="text-xs font-extrabold uppercase tracking-wider text-slate-900 dark:text-slate-100">Kopen met Contant Geld</h3>
                    </div>
                    <span class="text-xs font-bold text-slate-500 dark:text-slate-400">
                        @if($isDiscounted)
                            <span class="line-through text-slate-400 mr-1">€10.000</span>
                            <span class="text-emerald-600 dark:text-emerald-400 font-black">€{{ number_format($cashPricePerTicket, 0, ',', '.') }} per lot</span>
                            <span class="text-rose-500 text-[10px] font-black uppercase bg-rose-100 dark:bg-rose-950 px-1.5 py-0.5 rounded ml-1">-50%</span>
                            <span class="text-emerald-600 dark:text-emerald-400 text-[10px] font-bold ml-1">(100% naar pot!)</span>
                        @else
                            €{{ number_format($cashPricePerTicket, 0, ',', '.') }} per lot (100% naar pot)
                        @endif
                    </span>
                </div>

                @php
                    $isLotteryBlocked = $character->isImprisoned() || $character->isComatose();
                    $cashPresets = [10, 25, 50, 100, 250, 500, 1000, 2500];
                @endphp

                <!-- 1-Klik Snelle Aankoopknoppen -->
                <div class="space-y-1.5">
                    <span class="text-[10px] font-black uppercase tracking-wider text-slate-400 block">Snelle Aankoop (1 Klik):</span>
                    <form action="{{ route('lottery.buy') }}" method="POST" class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                        @csrf
                        <input type="hidden" name="currency" value="cash">

                        @foreach($cashPresets as $q)
                            @php
                                $cost = $cashPricePerTicket * $q;
                                $canAfford = ! $isLotteryBlocked && $character->cash >= $cost;
                            @endphp
                            <button type="submit" name="quantity" value="{{ $q }}"
                                    {{ ! $canAfford ? 'disabled' : '' }}
                                    class="p-2.5 rounded-xl border {{ $q >= 250 ? 'border-purple-300 dark:border-purple-700 bg-purple-50/60 dark:bg-purple-950/30 hover:bg-purple-100' : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:border-purple-500' }} transition-all duration-150 active:scale-95 text-center cursor-pointer shadow-2xs touch-manipulation {{ ! $canAfford ? 'opacity-35 cursor-not-allowed' : '' }}">
                                <span class="block text-xs font-black {{ $q >= 250 ? 'text-purple-700 dark:text-purple-300' : 'text-slate-900 dark:text-slate-100' }}">
                                    {{ number_format($q, 0, ',', '.') }} Loten
                                </span>
                                <span class="block text-[11px] text-emerald-600 dark:text-emerald-400 font-bold mt-0.5">
                                    €{{ number_format($cost, 0, ',', '.') }}
                                </span>
                            </button>
                        @endforeach
                    </form>
                </div>

                <!-- Aangepast Aantal & MAX Kopen Balk -->
                <form action="{{ route('lottery.buy') }}" method="POST" class="p-3 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 space-y-2.5 shadow-2xs">
                    @csrf
                    <input type="hidden" name="currency" value="cash">

                    <div class="flex items-center justify-between text-xs">
                        <label class="font-extrabold uppercase text-[10px] tracking-wider text-slate-500 dark:text-slate-400">
                            Aangepast Aantal of MAX Inzetten:
                        </label>
                        <button type="button" @click="setMax()" class="text-[10px] font-black uppercase text-purple-600 dark:text-purple-400 hover:underline cursor-pointer">
                            🚀 Max Haalbaar (<span x-text="format(maxQty)"></span> loten)
                        </button>
                    </div>

                    <div class="flex items-center gap-2">
                        <button type="button" @click="qty = Math.max(1, qty - 50)" class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 font-black text-slate-700 dark:text-slate-300 text-sm flex items-center justify-center shrink-0 cursor-pointer active:scale-95">
                            -50
                        </button>

                        <div class="relative flex-1">
                            <input type="number" name="quantity" x-model.number="qty" min="1" max="5000"
                                   @if($isLotteryBlocked) disabled @endif
                                   class="w-full text-center font-black text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white p-2 focus:ring-2 focus:ring-purple-500">
                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-[10px] font-bold text-slate-400 pointer-events-none">loten</span>
                        </div>

                        <button type="button" @click="qty = Math.min(5000, qty + 50)" class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 font-black text-slate-700 dark:text-slate-300 text-sm flex items-center justify-center shrink-0 cursor-pointer active:scale-95">
                            +50
                        </button>

                        <button type="button" @click="setMax()" class="px-3 h-10 rounded-xl bg-purple-50 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 border border-purple-200 dark:border-purple-800 font-black text-xs uppercase shrink-0 cursor-pointer active:scale-95">
                            MAX
                        </button>
                    </div>

                    <div class="flex items-center justify-between text-xs pt-1 border-t border-slate-100 dark:border-slate-800">
                        <span class="text-slate-500 text-[11px]">
                            Totale Kosten: <strong class="text-emerald-600 dark:text-emerald-400 font-black" x-text="'€' + format(qty * price)"></strong>
                        </span>
                        <button type="submit"
                                :disabled="{{ $isLotteryBlocked ? 'true' : 'false' }} || userCash < (qty * price) || qty < 1"
                                class="py-2 px-5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-black text-xs uppercase tracking-wider transition-all cursor-pointer active:scale-95 shadow-xs disabled:opacity-40 disabled:cursor-not-allowed">
                            <i class="fa-solid fa-cart-shopping mr-1"></i> Koop <span x-text="format(qty)"></span> Loten
                        </button>
                    </div>
                </form>
            </div>

            <!-- TAB 2: Kopen met VIP Sterren -->
            <div class="p-5 rounded-2xl bg-amber-50/80 dark:bg-slate-800/60 border border-amber-200/80 dark:border-amber-700/50 space-y-4"
                 x-data="{
                     qty: 25,
                     price: {{ $starsPricePerTicket }},
                     userStars: {{ (int) ($character->stars ?? 0) }},
                     get maxQty() {
                         if (this.price <= 0) return 1;
                         return Math.min(5000, Math.max(1, Math.floor(this.userStars / this.price)));
                     },
                     format(num) {
                         return new Intl.NumberFormat('nl-NL').format(Math.max(0, Math.round(num)));
                     },
                     setMax() {
                         this.qty = this.maxQty;
                     }
                 }">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1">
                    <div class="flex items-center gap-2">
                        <span class="w-7 h-7 rounded-lg bg-amber-500/20 text-amber-600 dark:text-amber-400 flex items-center justify-center text-xs font-bold">★</span>
                        <h3 class="text-xs font-extrabold uppercase tracking-wider text-slate-900 dark:text-slate-100">Kopen met VIP Sterren</h3>
                    </div>
                    <span class="text-xs font-bold text-amber-700 dark:text-amber-300">
                        @if($isDiscounted)
                            <span class="line-through text-slate-400 mr-1">15 Sterren</span>
                            <span class="font-black text-amber-600 dark:text-amber-400">{{ $starsPricePerTicket }} Sterren / lot</span>
                            <span class="text-rose-500 text-[10px] font-black uppercase bg-rose-100 dark:bg-rose-950 px-1.5 py-0.5 rounded ml-1">-50%</span>
                        @else
                            {{ $starsPricePerTicket }} Sterren / lot (+€25.000 jackpot bonus!)
                        @endif
                    </span>
                </div>

                @php
                    $starPresets = [5, 10, 25, 50, 100, 250, 500];
                @endphp

                <!-- 1-Klik Snelle Aankoopknoppen met Sterren -->
                <div class="space-y-1.5">
                    <span class="text-[10px] font-black uppercase tracking-wider text-amber-800 dark:text-amber-400 block">Snelle Aankoop (1 Klik):</span>
                    <form action="{{ route('lottery.buy') }}" method="POST" class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                        @csrf
                        <input type="hidden" name="currency" value="stars">

                        @foreach($starPresets as $q)
                            @php
                                $cost = $starsPricePerTicket * $q;
                                $canAfford = ! $isLotteryBlocked && ($character->stars ?? 0) >= $cost;
                            @endphp
                            <button type="submit" name="quantity" value="{{ $q }}"
                                    {{ ! $canAfford ? 'disabled' : '' }}
                                    class="p-2.5 rounded-xl border border-amber-300 dark:border-amber-700/60 bg-white dark:bg-slate-800 hover:border-amber-400 transition-all duration-150 active:scale-95 text-center cursor-pointer shadow-2xs touch-manipulation {{ ! $canAfford ? 'opacity-35 cursor-not-allowed' : '' }}">
                                <span class="block text-xs font-black text-amber-950 dark:text-amber-200">
                                    {{ number_format($q, 0, ',', '.') }} VIP Loten
                                </span>
                                <span class="block text-[11px] text-amber-600 dark:text-amber-400 font-bold mt-0.5">
                                    ★ {{ number_format($cost, 0, ',', '.') }}
                                </span>
                            </button>
                        @endforeach
                    </form>
                </div>

                <!-- Aangepast Aantal of MAX Inzetten met Sterren -->
                <form action="{{ route('lottery.buy') }}" method="POST" class="p-3 rounded-xl bg-white dark:bg-slate-900 border border-amber-300 dark:border-amber-700/60 space-y-2.5 shadow-2xs">
                    @csrf
                    <input type="hidden" name="currency" value="stars">

                    <div class="flex items-center justify-between text-xs">
                        <label class="font-extrabold uppercase text-[10px] tracking-wider text-slate-500 dark:text-slate-400">
                            Aangepast Aantal of MAX Inzetten:
                        </label>
                        <button type="button" @click="setMax()" class="text-[10px] font-black uppercase text-amber-600 dark:text-amber-400 hover:underline cursor-pointer">
                            🚀 Max Haalbaar (<span x-text="format(maxQty)"></span> VIP loten)
                        </button>
                    </div>

                    <div class="flex items-center gap-2">
                        <button type="button" @click="qty = Math.max(1, qty - 10)" class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 font-black text-slate-700 dark:text-slate-300 text-sm flex items-center justify-center shrink-0 cursor-pointer active:scale-95">
                            -10
                        </button>

                        <div class="relative flex-1">
                            <input type="number" name="quantity" x-model.number="qty" min="1" max="5000"
                                   @if($isLotteryBlocked) disabled @endif
                                   class="w-full text-center font-black text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white p-2 focus:ring-2 focus:ring-amber-500">
                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-[10px] font-bold text-slate-400 pointer-events-none">loten</span>
                        </div>

                        <button type="button" @click="qty = Math.min(5000, qty + 10)" class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 font-black text-slate-700 dark:text-slate-300 text-sm flex items-center justify-center shrink-0 cursor-pointer active:scale-95">
                            +10
                        </button>

                        <button type="button" @click="setMax()" class="px-3 h-10 rounded-xl bg-amber-500/20 text-amber-700 dark:text-amber-300 border border-amber-300 dark:border-amber-700 font-black text-xs uppercase shrink-0 cursor-pointer active:scale-95">
                            MAX
                        </button>
                    </div>

                    <div class="flex items-center justify-between text-xs pt-1 border-t border-slate-100 dark:border-slate-800">
                        <span class="text-slate-500 text-[11px]">
                            Kosten: <strong class="text-amber-600 dark:text-amber-400 font-black" x-text="format(qty * price) + ' Sterren'"></strong>
                            <span class="text-[10px] text-emerald-600 dark:text-emerald-400 block" x-text="'+€' + format(qty * 25000) + ' jackpot boost!'"></span>
                        </span>
                        <button type="submit"
                                :disabled="{{ $isLotteryBlocked ? 'true' : 'false' }} || userStars < (qty * price) || qty < 1"
                                class="py-2 px-5 rounded-xl bg-amber-500 hover:bg-amber-600 text-slate-950 font-black text-xs uppercase tracking-wider transition-all cursor-pointer active:scale-95 shadow-xs disabled:opacity-40 disabled:cursor-not-allowed">
                            <i class="fa-solid fa-star mr-1"></i> Koop <span x-text="format(qty)"></span> VIP Loten
                        </button>
                    </div>
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
                    {{ number_format($userTicketsCount, 0, ',', '.') }} in bezit
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
                    Gebaseerd op {{ number_format($userTicketsCount, 0, ',', '.') }} van de {{ number_format($round->total_tickets_sold, 0, ',', '.') }} totaal verkochte loten.
                </span>
            </div>

            @if($userTicketsCount > 0)
                <!-- Ticket Categories Breakdown -->
                <div class="grid grid-cols-3 gap-2 text-center text-xs">
                    <div class="p-2.5 rounded-xl bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70">
                        <span class="text-[10px] font-extrabold uppercase text-slate-400 block">Contant</span>
                        <span class="font-black text-slate-900 dark:text-white text-xs mt-0.5 block">{{ number_format($ticketBreakdown['cash'] ?? 0, 0, ',', '.') }}</span>
                    </div>
                    <div class="p-2.5 rounded-xl bg-amber-50/80 dark:bg-amber-950/40 border border-amber-200/80 dark:border-amber-800/60">
                        <span class="text-[10px] font-extrabold uppercase text-amber-700 dark:text-amber-300 block">★ VIP</span>
                        <span class="font-black text-amber-800 dark:text-amber-200 text-xs mt-0.5 block">{{ number_format($ticketBreakdown['stars'] ?? 0, 0, ',', '.') }}</span>
                    </div>
                    <div class="p-2.5 rounded-xl bg-purple-50/80 dark:bg-purple-950/40 border border-purple-200/80 dark:border-purple-800/60">
                        <span class="text-[10px] font-extrabold uppercase text-purple-700 dark:text-purple-300 block">📺 Video Ads</span>
                        <span class="font-black text-purple-800 dark:text-purple-200 text-xs mt-0.5 block">{{ number_format($ticketBreakdown['ads'] ?? 0, 0, ',', '.') }}</span>
                    </div>
                </div>
            @endif

            <!-- List of Tickets (Optimized with hardware scrolling) -->
            <div class="space-y-2 max-h-64 overflow-y-auto overscroll-contain pr-1">
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

                @if($userTicketsCount > 15)
                    <div class="p-2.5 rounded-xl bg-purple-50/70 dark:bg-purple-950/30 border border-purple-200/80 dark:border-purple-900/60 text-center text-xs text-purple-700 dark:text-purple-300 font-bold">
                        <i class="fa-solid fa-circle-check mr-1 text-emerald-500"></i>
                        En nog {{ number_format($userTicketsCount - 15, 0, ',', '.') }} andere geregistreerde loten voor deze ronde
                    </div>
                @endif
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
