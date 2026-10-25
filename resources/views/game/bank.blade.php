@extends('layouts.app')

@section('title', 'Kliniekbank & Financieel Centrum - TBS Kliniek')

@section('content')
<div class="space-y-6" x-data="{ activeTab: @js($activeTab ?? 'vault') }">

    <!-- Top Bank Hero Header -->
    <div class="rounded-2xl shadow-xs p-5 sm:p-7 text-slate-800 dark:text-slate-100 relative overflow-hidden bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800">
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-5">
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-2xl bg-emerald-500/10 dark:bg-emerald-950/60 border border-emerald-300 dark:border-emerald-800 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-xl sm:text-2xl shrink-0 shadow-xs">
                    <i class="fa-solid fa-building-columns"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2 mb-1 flex-wrap">
                        <span class="text-[9px] font-black uppercase tracking-wider text-emerald-700 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-950/80 px-2.5 py-0.5 rounded-md border border-emerald-200 dark:border-emerald-800">
                            Beveiligde Kluis
                        </span>
                        <span class="text-[9px] font-extrabold text-purple-700 dark:text-purple-300 bg-purple-50 dark:bg-purple-950/80 px-2.5 py-0.5 rounded-md border border-purple-200 dark:border-purple-800">
                            Patiënt Level {{ $character->level }}
                        </span>
                    </div>
                    <h1 class="text-xl sm:text-2xl md:text-3xl font-black tracking-tight text-slate-900 dark:text-white">
                        Kliniekbank & Financieel Centrum
                    </h1>
                    <p class="text-slate-500 dark:text-slate-400 text-xs sm:text-sm mt-0.5 max-w-xl leading-relaxed">
                        Bescherm je buit tegen zakkenrollers en diefstal. Beheer veilige bankdeposito's, incasseer je kwartier-uitkering en sluit flexibele leningen af.
                    </p>
                </div>
            </div>

            <!-- TABS SWITCHER (Clean Solid Badges) -->
            <div class="flex items-center gap-1.5 p-1.5 bg-slate-100 dark:bg-slate-800/80 rounded-2xl shrink-0 text-xs font-bold border border-slate-200/80 dark:border-slate-700/60 overflow-x-auto">
                <button type="button" @click="activeTab = 'vault'" 
                        :class="activeTab === 'vault' ? 'bg-purple-600 text-white font-black shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'"
                        class="px-3.5 py-2 rounded-xl transition cursor-pointer flex items-center gap-2 whitespace-nowrap">
                    <i class="fa-solid fa-vault text-xs"></i>
                    <span>Kluis & Beheer</span>
                </button>
                <button type="button" @click="activeTab = 'loans'" 
                        :class="activeTab === 'loans' ? 'bg-purple-600 text-white font-black shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'"
                        class="px-3.5 py-2 rounded-xl transition cursor-pointer flex items-center gap-2 whitespace-nowrap relative">
                    <i class="fa-solid fa-hand-holding-dollar text-xs"></i>
                    <span>Krediet & Leningen</span>
                    @if(isset($activeLoans) && $activeLoans->count() > 0)
                        <span class="w-2 h-2 rounded-full bg-rose-400 animate-pulse"></span>
                    @endif
                </button>
                <button type="button" @click="activeTab = 'transactions'" 
                        :class="activeTab === 'transactions' ? 'bg-purple-600 text-white font-black shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'"
                        class="px-3.5 py-2 rounded-xl transition cursor-pointer flex items-center gap-2 whitespace-nowrap">
                    <i class="fa-solid fa-receipt text-xs"></i>
                    <span>Mutaties</span>
                </button>
            </div>
        </div>
    </div>

    <!-- 4 High-End Financial Vitals Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Cash Card -->
        <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-amber-200/90 dark:border-amber-800/60 shadow-xs relative overflow-hidden flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[10px] font-black text-amber-700 dark:text-amber-400 uppercase tracking-wider">Contant op Zak</span>
                    <span class="w-8 h-8 rounded-xl bg-amber-50 dark:bg-amber-950/70 text-amber-500 flex items-center justify-center text-sm">
                        <i class="fa-solid fa-wallet"></i>
                    </span>
                </div>
                <p class="text-2xl font-black text-slate-900 dark:text-white font-mono">
                    €{{ number_format($character->cash, 0, ',', '.') }}
                </p>
            </div>
            <p class="text-[11px] text-amber-700 dark:text-amber-400 font-bold mt-3 flex items-center gap-1.5 pt-2 border-t border-amber-100 dark:border-amber-900/40">
                <i class="fa-solid fa-triangle-exclamation text-[10px]"></i> Kwetsbaar voor zakkenrollers
            </p>
        </div>

        <!-- Bank Card -->
        <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-emerald-200/90 dark:border-emerald-800/60 shadow-xs relative overflow-hidden flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[10px] font-black text-emerald-700 dark:text-emerald-400 uppercase tracking-wider">Veilig Banktegoed</span>
                    <span class="w-8 h-8 rounded-xl bg-emerald-50 dark:bg-emerald-950/70 text-emerald-500 flex items-center justify-center text-sm">
                        <i class="fa-solid fa-vault"></i>
                    </span>
                </div>
                <p class="text-2xl font-black text-emerald-700 dark:text-emerald-400 font-mono">
                    €{{ number_format($character->bank, 0, ',', '.') }}
                </p>
            </div>
            <p class="text-[11px] text-emerald-700 dark:text-emerald-400 font-bold mt-3 flex items-center gap-1.5 pt-2 border-t border-emerald-100 dark:border-emerald-900/40">
                <i class="fa-solid fa-shield-halved text-[10px]"></i> 100% beveiligd tegen beroving
            </p>
        </div>

        <!-- Open Debt Card -->
        <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-rose-200/90 dark:border-rose-800/60 shadow-xs relative overflow-hidden flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[10px] font-black text-rose-700 dark:text-rose-400 uppercase tracking-wider">Openstaande Schuld</span>
                    <span class="w-8 h-8 rounded-xl bg-rose-50 dark:bg-rose-950/70 text-rose-500 flex items-center justify-center text-sm">
                        <i class="fa-solid fa-hand-holding-dollar"></i>
                    </span>
                </div>
                <p class="text-2xl font-black text-rose-600 dark:text-rose-400 font-mono">
                    €{{ number_format($openstaandeSchuld ?? 0, 0, ',', '.') }}
                </p>
            </div>
            <p class="text-[11px] text-slate-500 dark:text-slate-400 font-bold mt-3 flex items-center gap-1.5 pt-2 border-t border-rose-100 dark:border-rose-900/40">
                <i class="fa-solid fa-clock-rotate-left text-[10px]"></i> {{ isset($activeLoans) ? $activeLoans->count() : 0 }} lopende lening(en)
            </p>
        </div>

        <!-- Available Borrow Capacity -->
        <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-purple-200/90 dark:border-purple-800/60 shadow-xs relative overflow-hidden flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[10px] font-black text-purple-700 dark:text-purple-400 uppercase tracking-wider">Beschikbaar Krediet</span>
                    <span class="w-8 h-8 rounded-xl bg-purple-50 dark:bg-purple-950/70 text-purple-500 flex items-center justify-center text-sm">
                        <i class="fa-solid fa-chart-line"></i>
                    </span>
                </div>
                <p class="text-2xl font-black text-purple-700 dark:text-purple-300 font-mono">
                    €{{ number_format($beschikbareLening ?? 0, 0, ',', '.') }}
                </p>
            </div>
            <p class="text-[11px] text-slate-500 dark:text-slate-400 font-bold mt-3 flex items-center gap-1.5 pt-2 border-t border-purple-100 dark:border-purple-900/40">
                <i class="fa-solid fa-arrow-up-right-dots text-[10px]"></i> Max €{{ number_format($maxCapacity ?? 0, 0, ',', '.') }} bij Lvl {{ $character->level }}
            </p>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- TAB 1: FINANCIEEL OVERZICHT & KLUIS        -->
    <!-- ========================================== -->
    <div x-show="activeTab === 'vault'" class="space-y-6">
        
        <!-- TBS Patiënten Uitkering Overview -->
        <div class="bg-white dark:bg-slate-900 p-5 sm:p-6 rounded-2xl border {{ ($allowanceDetails['eligible'] ?? false) ? 'border-emerald-300 dark:border-emerald-800/80' : 'border-slate-200 dark:border-slate-800' }} shadow-xs">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 rounded-xl {{ ($allowanceDetails['eligible'] ?? false) ? 'bg-emerald-600' : 'bg-slate-200 dark:bg-slate-800' }} text-white flex items-center justify-center text-xl shrink-0 shadow-xs">
                        <i class="fa-solid fa-hand-holding-dollar"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <h2 class="text-sm font-black text-slate-900 dark:text-white uppercase tracking-wide">TBS Patiënten Uitkering</h2>
                            @if($allowanceDetails['eligible'] ?? false)
                                <span class="text-[9px] font-black px-2 py-0.5 rounded-md bg-emerald-100 dark:bg-emerald-950 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 uppercase">Actief</span>
                            @else
                                <span class="text-[9px] font-black px-2 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-700 uppercase">Lid van Bende (Bende Loon)</span>
                            @endif
                        </div>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 max-w-2xl leading-relaxed">
                            @if($allowanceDetails['eligible'] ?? false)
                                Iedere patiënt in de kliniek die <strong>geen lid is van een bende</strong> ontvangt automatisch een basisinkomen van <strong class="text-emerald-600 dark:text-emerald-400 font-extrabold">€1.000 per kwartier (15 minuten)</strong> direct op deze beveiligde bankrekening!
                            @else
                                Omdat je aangesloten bent bij bende <strong>{{ $character->gang?->name ?? 'Gang' }}</strong>, ontvang je geen basisuitkering meer, maar verdien je automatisch een collectief uurloon via de smokkelkiosken van jouw factie.
                            @endif
                        </p>
                    </div>
                </div>

                @if($allowanceDetails['eligible'] ?? false)
                    <div class="shrink-0 flex sm:flex-col items-center sm:items-end justify-between bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200/80 dark:border-emerald-800/60 p-3 sm:px-4 sm:py-2.5 rounded-xl"
                         x-data="{ 
                             secondsLeft: {{ $allowanceDetails['seconds_until_next'] ?? 900 }},
                             init() {
                                 setInterval(() => {
                                     if (this.secondsLeft > 0) this.secondsLeft--;
                                     else this.secondsLeft = 900;
                                 }, 1000);
                             },
                             formatTime() {
                                 const m = Math.floor(this.secondsLeft / 60);
                                 const s = this.secondsLeft % 60;
                                 return `${m}m ${s < 10 ? '0' : ''}${s}s`;
                             }
                         }">
                        <span class="text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider">Volgende Storting</span>
                        <span class="text-base font-black text-emerald-700 dark:text-emerald-400 font-mono" x-text="formatTime()">{{ $allowanceDetails['next_human'] ?? '15m 00s' }}</span>
                        <span class="text-[11px] font-extrabold text-emerald-600 dark:text-emerald-400">+€1.000 op bank</span>
                    </div>
                @endif
            </div>
        </div>

        <!-- Deposit and Withdraw Operational Panels -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            
            <!-- Deposit Form (Geld Storten) -->
            <div class="bg-white dark:bg-slate-900 p-5 sm:p-6 rounded-2xl border border-slate-200/90 dark:border-slate-800 shadow-xs flex flex-col justify-between space-y-4" x-data="{ cash: {{ $character->cash }} }">
                <div>
                    <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3 mb-4">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-lg bg-emerald-100 dark:bg-emerald-950/70 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-sm font-black">
                                <i class="fa-solid fa-arrow-down"></i>
                            </div>
                            <div>
                                <h2 class="text-xs sm:text-sm font-black text-slate-900 dark:text-white uppercase tracking-wide">
                                    Geld Storten op Bank
                                </h2>
                                <span class="text-[10px] text-slate-400 font-bold">100% Kosteloos & Beveiligd</span>
                            </div>
                        </div>
                        <span class="text-xs font-mono font-black text-amber-700 dark:text-amber-400">
                            €{{ number_format($character->cash, 0, ',', '.') }} op zak
                        </span>
                    </div>

                    <form method="POST" action="{{ route('bank.deposit') }}" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Te storten bedrag</label>
                            <div class="relative">
                                <span class="absolute left-3.5 top-2.5 text-sm font-black text-slate-400">€</span>
                                <input type="number" 
                                       name="amount" 
                                       x-ref="depositInput" 
                                       min="1" 
                                       max="{{ $character->cash }}" 
                                       value="{{ $character->cash }}" 
                                       required 
                                       class="w-full pl-8 pr-4 py-2.5 text-sm font-mono font-black border border-slate-300 dark:border-slate-700 rounded-xl bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500 outline-none">
                            </div>
                        </div>

                        <!-- Quick Deposit Chips -->
                        <div class="flex items-center gap-2 text-xs">
                            <button type="button" @click="$refs.depositInput.value = Math.floor(cash * 0.25)" class="flex-1 py-1.5 px-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 rounded-lg font-bold text-slate-700 dark:text-slate-300 cursor-pointer text-center">25%</button>
                            <button type="button" @click="$refs.depositInput.value = Math.floor(cash * 0.5)" class="flex-1 py-1.5 px-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 rounded-lg font-bold text-slate-700 dark:text-slate-300 cursor-pointer text-center">50%</button>
                            <button type="button" @click="$refs.depositInput.value = cash" class="flex-2 py-1.5 px-2 bg-emerald-100 dark:bg-emerald-950/70 hover:bg-emerald-200 rounded-lg font-black text-emerald-800 dark:text-emerald-300 cursor-pointer text-center">Alles (€{{ number_format($character->cash, 0, ',', '.') }})</button>
                        </div>

                        <button type="submit" 
                                @if($character->cash <= 0) disabled @endif 
                                class="w-full bg-emerald-600 hover:bg-emerald-700 active:scale-95 disabled:opacity-40 disabled:cursor-not-allowed text-white font-black py-3 px-4 rounded-xl text-xs uppercase tracking-wider shadow-xs transition flex items-center justify-center gap-2 cursor-pointer">
                            <i class="fa-solid fa-shield-halved"></i>
                            <span>Storten in Beveiligde Kluis</span>
                        </button>
                    </form>
                </div>
            </div>

            <!-- Withdraw Form (Geld Opnemen) -->
            <div class="bg-white dark:bg-slate-900 p-5 sm:p-6 rounded-2xl border border-slate-200/90 dark:border-slate-800 shadow-xs flex flex-col justify-between space-y-4" x-data="{ bank: {{ $character->bank }} }">
                <div>
                    <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3 mb-3">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-lg bg-amber-100 dark:bg-amber-950/70 text-amber-600 dark:text-amber-400 flex items-center justify-center text-sm font-black">
                                <i class="fa-solid fa-arrow-up"></i>
                            </div>
                            <div>
                                <h2 class="text-xs sm:text-sm font-black text-slate-900 dark:text-white uppercase tracking-wide">
                                    Contant Geld Opnemen
                                </h2>
                                <span class="text-[10px] text-slate-400 font-bold">Voor Zwarte Markt & Spelershandel</span>
                            </div>
                        </div>
                        <span class="text-xs font-mono font-black text-emerald-700 dark:text-emerald-400">
                            €{{ number_format($character->bank, 0, ',', '.') }} op bank
                        </span>
                    </div>

                    <!-- Tax Explanation Banner -->
                    <div class="p-3 rounded-xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-700/50 flex items-start gap-2.5 mb-4">
                        <i class="fa-solid fa-percent text-amber-600 dark:text-amber-400 text-xs mt-0.5"></i>
                        <div class="text-[11px] text-amber-950 dark:text-amber-200 leading-snug">
                            <span class="font-extrabold">Kliniek Opnamebelasting: {{ $character->isVip() ? '3% (VIP voordeel)' : '5% (Standaard tarief)' }}</span>
                            <span class="text-amber-800 dark:text-amber-300 block mt-0.5">
                                Belasting vloeit 100% door naar de <a href="{{ route('lottery.index') }}" class="underline font-bold text-amber-900 dark:text-amber-100">TBS Afdelingsloterij Jackpot</a>!
                            </span>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('bank.withdraw') }}" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Op te nemen bedrag</label>
                            <div class="relative">
                                <span class="absolute left-3.5 top-2.5 text-sm font-black text-slate-400">€</span>
                                <input type="number" 
                                       name="amount" 
                                       x-ref="withdrawInput" 
                                       min="1" 
                                       max="{{ $character->bank }}" 
                                       value="100" 
                                       required 
                                       class="w-full pl-8 pr-4 py-2.5 text-sm font-mono font-black border border-slate-300 dark:border-slate-700 rounded-xl bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-amber-500 outline-none">
                            </div>
                        </div>

                        <!-- Quick Withdraw Chips -->
                        <div class="flex items-center gap-2 text-xs">
                            <button type="button" @click="$refs.withdrawInput.value = 1000" class="flex-1 py-1.5 px-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 rounded-lg font-bold text-slate-700 dark:text-slate-300 cursor-pointer text-center">€1.000</button>
                            <button type="button" @click="$refs.withdrawInput.value = 5000" class="flex-1 py-1.5 px-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 rounded-lg font-bold text-slate-700 dark:text-slate-300 cursor-pointer text-center">€5.000</button>
                            <button type="button" @click="$refs.withdrawInput.value = bank" class="flex-2 py-1.5 px-2 bg-amber-100 dark:bg-amber-950/70 hover:bg-amber-200 rounded-lg font-black text-amber-800 dark:text-amber-300 cursor-pointer text-center">Alles (€{{ number_format($character->bank, 0, ',', '.') }})</button>
                        </div>

                        <button type="submit" 
                                @if($character->bank <= 0) disabled @endif 
                                class="w-full bg-purple-600 hover:bg-purple-700 active:scale-95 disabled:opacity-40 disabled:cursor-not-allowed text-white font-black py-3 px-4 rounded-xl text-xs uppercase tracking-wider shadow-xs transition flex items-center justify-center gap-2 cursor-pointer">
                            <i class="fa-solid fa-money-bill-wave"></i>
                            <span>Contant Opnemen (-{{ $character->isVip() ? '3%' : '5%' }} Tax)</span>
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </div>

    <!-- ========================================== -->
    <!-- TAB 2: LENINGEN & KREDIET                  -->
    <!-- ========================================== -->
    <div x-show="activeTab === 'loans'" class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        
        <!-- LEFT: Lening Aanvragen Terminal -->
        <div class="lg:col-span-5 bg-white dark:bg-slate-900 p-5 sm:p-6 rounded-2xl border border-slate-200/90 dark:border-slate-800 shadow-xs flex flex-col justify-between space-y-4"
             x-data="{ 
                 loanAmount: {{ min(100000, max(10000, $beschikbareLening ?? 0)) }},
                 maxLoan: {{ $beschikbareLening ?? 0 }},
                 interestRate: 8.5,
                 get interestAmount() { return Math.round((this.loanAmount * this.interestRate) / 100); },
                 get totalDue() { return parseInt(this.loanAmount || 0) + this.interestAmount; },
                 formatEuro(n) { return new Intl.NumberFormat('nl-NL').format(n); }
             }">
            <div>
                <div class="flex items-center gap-2.5 border-b border-slate-100 dark:border-slate-800 pb-3 mb-4">
                    <div class="w-8 h-8 rounded-lg bg-purple-100 dark:bg-purple-950/70 text-purple-600 dark:text-purple-400 flex items-center justify-center text-sm font-black">
                        <i class="fa-solid fa-file-invoice-dollar"></i>
                    </div>
                    <div>
                        <h3 class="text-xs sm:text-sm font-black text-slate-900 dark:text-white uppercase tracking-wide">
                            Nieuwe Lening Aanvragen
                        </h3>
                        <span class="text-[10px] text-slate-400 font-bold">Directe Kredietverlening</span>
                    </div>
                </div>

                <!-- Status Summary Box -->
                <div class="bg-slate-50 dark:bg-slate-800/60 rounded-xl p-4 border border-slate-200/90 dark:border-slate-700/60 space-y-2 mb-4 text-xs">
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500 dark:text-slate-400 font-bold">Patiënt Level:</span>
                        <span class="font-black text-slate-900 dark:text-white">Level {{ $character->level }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500 dark:text-slate-400 font-bold">Maximale Kredietlimiet:</span>
                        <span class="font-black text-slate-900 dark:text-white font-mono">€ {{ number_format($maxCapacity ?? 0, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500 dark:text-slate-400 font-bold">Openstaande Schuld:</span>
                        <span class="font-black text-rose-600 dark:text-rose-400 font-mono">€ {{ number_format($openstaandeSchuld ?? 0, 0, ',', '.') }}</span>
                    </div>
                    <div class="pt-2 border-t border-slate-200 dark:border-slate-700 flex items-center justify-between text-xs">
                        <span class="font-extrabold text-slate-700 dark:text-slate-300">Nog Beschikbaar:</span>
                        <span class="font-black text-emerald-600 dark:text-emerald-400 font-mono text-sm">€ {{ number_format($beschikbareLening ?? 0, 0, ',', '.') }}</span>
                    </div>
                </div>

                <!-- Aanvraag Formulier -->
                <form method="POST" action="{{ route('bank.loans.request') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Gewenst Leenbedrag</label>
                        <div class="relative">
                            <span class="absolute left-3.5 top-2.5 text-sm font-black text-slate-400">€</span>
                            <input type="number" 
                                   name="amount" 
                                   x-model.number="loanAmount" 
                                   min="10000" 
                                   max="{{ $beschikbareLening ?? 0 }}" 
                                   required
                                   class="w-full pl-8 pr-4 py-2.5 text-sm font-mono font-black border border-slate-300 dark:border-slate-700 rounded-xl bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-purple-500 outline-none">
                        </div>
                    </div>

                    <!-- Quick Amount Chips -->
                    <div class="grid grid-cols-4 gap-1.5 text-xs">
                        <button type="button" @click="loanAmount = 10000" class="py-1.5 px-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 rounded-lg font-bold text-slate-700 dark:text-slate-300 cursor-pointer text-center">€10k</button>
                        <button type="button" @click="loanAmount = 25000" class="py-1.5 px-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 rounded-lg font-bold text-slate-700 dark:text-slate-300 cursor-pointer text-center">€25k</button>
                        <button type="button" @click="loanAmount = 50000" class="py-1.5 px-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 rounded-lg font-bold text-slate-700 dark:text-slate-300 cursor-pointer text-center">€50k</button>
                        <button type="button" @click="loanAmount = maxLoan" class="py-1.5 px-2 bg-purple-100 dark:bg-purple-950/70 hover:bg-purple-200 rounded-lg font-black text-purple-700 dark:text-purple-300 cursor-pointer text-center">Max</button>
                    </div>

                    <!-- Uitbetalen Bestemming -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Direct Storten Naar</label>
                        <select name="deposit_to" class="w-full px-3.5 py-2.5 text-xs font-bold border border-slate-300 dark:border-slate-700 rounded-xl bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white outline-none">
                            <option value="cash">Contant op zak (Direct inzetbaar)</option>
                            <option value="bank">Veilig op bankrekening (Kluis)</option>
                        </select>
                    </div>

                    <!-- Renteconstructie Box -->
                    <div class="p-3.5 rounded-xl bg-purple-50 dark:bg-purple-950/40 border border-purple-200 dark:border-purple-800/60 text-xs space-y-2 text-purple-950 dark:text-purple-200">
                        <div class="font-black uppercase tracking-wide flex items-center justify-between">
                            <div class="flex items-center gap-1.5">
                                <i class="fa-solid fa-percent text-purple-600 dark:text-purple-400"></i>
                                <span>Rente & Leningtax (8,5%)</span>
                            </div>
                            <span class="text-[10px] text-amber-600 font-extrabold">&rarr; Naar Loterijpot</span>
                        </div>
                        <p class="leading-relaxed text-[11px] text-purple-900 dark:text-purple-300">
                            Je betaalt eenmalig <strong>8,5% rente</strong>. Deze bijdrage gaat rechtstreeks naar de jackpot van de TBS Loterij.
                        </p>
                        <div class="pt-2 border-t border-purple-200 dark:border-purple-800 flex items-center justify-between text-xs">
                            <span class="text-slate-600 dark:text-purple-300 font-bold">Renteopslag:</span>
                            <span class="font-black text-amber-600 dark:text-amber-400 font-mono">+ € <span x-text="formatEuro(interestAmount)"></span></span>
                        </div>
                        <div class="flex items-center justify-between text-xs pt-1 border-t border-dashed border-purple-200 dark:border-purple-800/60">
                            <span class="text-slate-700 dark:text-purple-200 font-black">Totaal Terug te Betalen:</span>
                            <span class="font-black text-purple-950 dark:text-white font-mono text-sm">€ <span x-text="formatEuro(totalDue)"></span></span>
                        </div>
                    </div>

                    <button type="submit" 
                            @if(($beschikbareLening ?? 0) < 10000) disabled @endif
                            class="w-full py-3 px-4 rounded-xl bg-purple-600 hover:bg-purple-700 active:scale-95 disabled:opacity-40 disabled:cursor-not-allowed text-white font-black text-xs uppercase tracking-wider shadow-xs transition flex items-center justify-center gap-2 cursor-pointer">
                        <i class="fa-solid fa-check"></i>
                        <span>Lening Aanvragen</span>
                    </button>
                </form>
            </div>
        </div>

        <!-- RIGHT: Lopende Leningen Cards -->
        <div class="lg:col-span-7 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-clock-rotate-left text-purple-600 dark:text-purple-400"></i>
                    <h3 class="text-xs sm:text-sm font-black uppercase tracking-wide text-slate-900 dark:text-white">
                        Lopende Leningen
                    </h3>
                </div>
            </div>

            @if(isset($activeLoans) && $activeLoans->count() > 0)
                <div class="space-y-4">
                    @foreach($activeLoans as $loan)
                        <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-5 shadow-xs space-y-4"
                             x-data="{ showRepayModal: false, repayAmount: {{ $loan->remaining_due }}, repaySource: 'cash' }">
                            
                            <!-- Header rij -->
                            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-8 h-8 rounded-lg bg-amber-100 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center text-sm font-bold">
                                        <i class="fa-solid fa-file-invoice"></i>
                                    </div>
                                    <div>
                                        <span class="text-xs font-black text-slate-900 dark:text-white">Gestart op {{ $loan->started_at->format('d-m-Y') }}</span>
                                        <span class="text-[10px] text-slate-400 block font-mono">Dossier: {{ $loan->loan_number }}</span>
                                    </div>
                                </div>
                                <span class="px-2.5 py-1 rounded-md text-[10px] font-black uppercase tracking-wider {{ $loan->total_paid > 0 ? 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300' : 'bg-purple-100 text-purple-800 dark:bg-purple-950 dark:text-purple-300' }}">
                                    {{ $loan->total_paid > 0 ? 'Deels afgelost' : 'Nog niet afgelost' }}
                                </span>
                            </div>

                            <!-- Cijfers Grid -->
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
                                <div>
                                    <span class="text-[10px] text-slate-400 uppercase font-black block">Leenbedrag</span>
                                    <span class="font-black text-slate-900 dark:text-white font-mono">€ {{ number_format($loan->amount, 0, ',', '.') }}</span>
                                </div>
                                <div>
                                    <span class="text-[10px] text-slate-400 uppercase font-black block">Rente (8,5%)</span>
                                    <span class="font-bold text-slate-600 dark:text-slate-300 font-mono">€ {{ number_format($loan->interest_amount, 0, ',', '.') }}</span>
                                </div>
                                <div>
                                    <span class="text-[10px] text-slate-400 uppercase font-black block">Restant Schuld</span>
                                    <span class="font-black text-rose-600 dark:text-rose-400 font-mono">€ {{ number_format($loan->remaining_due, 0, ',', '.') }}</span>
                                </div>
                                <div>
                                    <span class="text-[10px] text-slate-400 uppercase font-black block">Reeds Voldaan</span>
                                    <span class="font-bold text-emerald-600 dark:text-emerald-400 font-mono">€ {{ number_format($loan->total_paid, 0, ',', '.') }}</span>
                                </div>
                            </div>

                            <!-- Voortgangsbalk -->
                            <div class="space-y-1">
                                <div class="flex items-center justify-between text-[11px] font-bold">
                                    <span class="text-slate-400">Aflosstatus:</span>
                                    <span class="text-purple-600 dark:text-purple-400 font-mono font-black">{{ $loan->getProgressPercentage() }}%</span>
                                </div>
                                <div class="w-full bg-slate-100 dark:bg-slate-800 rounded-full h-2 overflow-hidden">
                                    <div class="bg-purple-600 h-full rounded-full transition-all duration-300" 
                                         style="width: {{ $loan->getProgressPercentage() }}%;"></div>
                                </div>
                            </div>

                            <!-- Actieknoppen -->
                            <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100 dark:border-slate-800">
                                <button type="button" @click="showRepayModal = !showRepayModal"
                                        class="py-2 px-3.5 rounded-xl bg-purple-600 hover:bg-purple-700 active:scale-95 text-white font-black text-xs shadow-xs transition flex items-center gap-1.5 cursor-pointer">
                                    <i class="fa-solid fa-coins text-xs"></i>
                                    <span>Deel Aflossen</span>
                                </button>

                                <form method="POST" action="{{ route('bank.loans.repay-full', $loan) }}">
                                    @csrf
                                    <input type="hidden" name="source" value="cash">
                                    <button type="submit" 
                                            class="py-2 px-3.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white font-black text-xs shadow-xs transition flex items-center gap-1.5 cursor-pointer">
                                        <i class="fa-solid fa-check-double text-xs"></i>
                                        <span>Alles Aflossen (€{{ number_format($loan->remaining_due, 0, ',', '.') }})</span>
                                    </button>
                                </form>
                            </div>

                            <!-- Inline Deel Aflossen Formulier Drawer -->
                            <div x-show="showRepayModal" x-cloak class="mt-3 p-4 bg-slate-50 dark:bg-slate-800/80 rounded-xl border border-purple-200 dark:border-purple-800/60 space-y-3">
                                <h4 class="text-xs font-black text-slate-800 dark:text-white uppercase">Aflossen op dossier {{ $loan->loan_number }}</h4>
                                <form method="POST" action="{{ route('bank.loans.repay', $loan) }}" class="space-y-3">
                                    @csrf
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs">
                                        <div>
                                            <label class="block font-bold text-slate-600 dark:text-slate-300 mb-1">Aflosbedrag (€)</label>
                                            <input type="number" name="amount" min="1" max="{{ $loan->remaining_due }}" value="{{ min(25000, $loan->remaining_due) }}" required
                                                   class="w-full px-3 py-2 text-xs font-mono font-bold border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-900 outline-none">
                                        </div>
                                        <div>
                                            <label class="block font-bold text-slate-600 dark:text-slate-300 mb-1">Betalen vanuit</label>
                                            <select name="source" class="w-full px-3 py-2 text-xs font-bold border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-900 outline-none">
                                                <option value="cash">Contant op zak (€{{ number_format($character->cash, 0, ',', '.') }})</option>
                                                <option value="bank">Veilig Banktegoed (€{{ number_format($character->bank, 0, ',', '.') }})</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="flex items-center justify-end gap-2">
                                        <button type="button" @click="showRepayModal = false" class="py-1.5 px-3 rounded-lg text-slate-500 hover:bg-slate-200 dark:hover:bg-slate-700 font-bold text-xs">Annuleren</button>
                                        <button type="submit" class="py-1.5 px-4 rounded-lg bg-purple-600 hover:bg-purple-700 text-white font-black text-xs shadow-xs">Aflossen Bevestigen</button>
                                    </div>
                                </form>
                            </div>

                        </div>
                    @endforeach
                </div>
            @else
                <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-8 text-center shadow-xs">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-xl mx-auto mb-3">
                        <i class="fa-solid fa-circle-check"></i>
                    </div>
                    <h4 class="text-sm font-black text-slate-900 dark:text-white">Geen openstaande schulden</h4>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 max-w-sm mx-auto leading-relaxed">
                        Je financiële status is brandschoon. Je kunt links direct een lening aanvragen tot maximaal <strong>€ {{ number_format($beschikbareLening ?? 0, 0, ',', '.') }}</strong>!
                    </p>
                </div>
            @endif

            <!-- Afgeloste Leningen Geschiedenis -->
            @if(isset($paidLoans) && $paidLoans->count() > 0)
                <div class="pt-4 border-t border-slate-200 dark:border-slate-800 space-y-2">
                    <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-wider">Recent Volledig Afgelost</h4>
                    @foreach($paidLoans as $pLoan)
                        <div class="bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800/60 rounded-xl p-3 flex items-center justify-between text-xs">
                            <div class="flex items-center gap-2">
                                <i class="fa-solid fa-badge-check text-emerald-500"></i>
                                <span class="font-bold text-slate-700 dark:text-slate-200">{{ $pLoan->loan_number }}</span>
                                <span class="text-slate-400 font-mono">(€ {{ number_format($pLoan->amount, 0, ',', '.') }})</span>
                            </div>
                            <span class="text-[10px] font-bold text-emerald-600 bg-emerald-50 dark:bg-emerald-950 px-2 py-0.5 rounded-full">
                                Afgelost op {{ $pLoan->paid_at?->format('d-m-Y') ?? '' }}
                            </span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

    </div>

    <!-- ========================================== -->
    <!-- TAB 3: TRANSACTIEHISTORIE (Logboek)        -->
    <!-- ========================================== -->
    <div x-show="activeTab === 'transactions'" class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-5 sm:p-6 shadow-xs space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-receipt text-purple-600 dark:text-purple-400"></i>
                <h3 class="text-xs sm:text-sm font-black uppercase tracking-wide text-slate-900 dark:text-white">
                    Transactiehistorie & Mutaties
                </h3>
            </div>
        </div>

        @if(isset($transactions) && $transactions->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-slate-200 dark:border-slate-800 text-slate-400 uppercase text-[10px] font-black tracking-wider">
                            <th class="py-3 px-3">Datum & Tijd</th>
                            <th class="py-3 px-3">Type</th>
                            <th class="py-3 px-3">Omschrijving</th>
                            <th class="py-3 px-3 text-right">Bedrag</th>
                            <th class="py-3 px-3 text-right">Saldo Na</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach($transactions as $tx)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition">
                                <td class="py-3 px-3 font-mono text-slate-500 text-[11px]">
                                    {{ $tx->created_at->format('d-m-Y H:i') }}
                                </td>
                                <td class="py-3 px-3">
                                    <span class="px-2 py-0.5 rounded-md text-[9px] font-black uppercase tracking-wide
                                        @if(in_array($tx->type, ['deposit', 'allowance', 'loan_received'])) bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300
                                        @else bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300 @endif">
                                        {{ $tx->type }}
                                    </span>
                                </td>
                                <td class="py-3 px-3 font-medium text-slate-800 dark:text-slate-200">
                                    {{ $tx->description }}
                                </td>
                                <td class="py-3 px-3 text-right font-mono font-black {{ $tx->amount >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                                    {{ $tx->amount >= 0 ? '+' : '' }}€{{ number_format($tx->amount, 0, ',', '.') }}
                                </td>
                                <td class="py-3 px-3 text-right font-mono text-slate-500 font-bold">
                                    €{{ number_format($tx->balance_after, 0, ',', '.') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $transactions->links() }}
            </div>
        @else
            <div class="p-8 text-center text-slate-500 dark:text-slate-400 text-xs">
                <i class="fa-solid fa-receipt text-3xl mb-2 text-slate-300 dark:text-slate-700 block"></i>
                Er zijn nog geen geregistreerde transacties voor dit account.
            </div>
        @endif
    </div>

</div>
@endsection
