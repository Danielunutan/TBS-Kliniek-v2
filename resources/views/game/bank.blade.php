@extends('layouts.app')

@section('title', 'Kliniekbank & Financieel Centrum - TBS Kliniek')

@section('content')
<div class="space-y-6" x-data="{ activeTab: @js($activeTab ?? 'vault') }">
    <!-- Header -->
    <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3.5">
            <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-purple-600 to-indigo-600 text-white flex items-center justify-center text-xl shadow-md shrink-0">
                <i class="fa-solid fa-building-columns"></i>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-lg font-black text-slate-900 dark:text-white tracking-tight uppercase">Management & Financiën</h1>
                    <span class="px-2 py-0.5 rounded-full bg-purple-100 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 font-extrabold text-[10px] uppercase tracking-wider">
                        Level {{ $character->level }}
                    </span>
                </div>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                    Inzicht in inkomsten & uitgaven, leningen, investeringen en je financiële strategie.
                </p>
            </div>
        </div>

        <!-- TABS SWITCHER -->
        <div class="flex items-center gap-1.5 p-1 bg-slate-100 dark:bg-slate-800/80 rounded-xl shrink-0 text-xs font-bold">
            <button type="button" @click="activeTab = 'vault'" 
                    :class="activeTab === 'vault' ? 'bg-white dark:bg-slate-900 text-purple-600 dark:text-purple-400 font-black shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900'"
                    class="px-3.5 py-2 rounded-lg transition cursor-pointer flex items-center gap-1.5">
                <i class="fa-solid fa-vault text-xs"></i>
                <span>Financieel overzicht</span>
            </button>
            <button type="button" @click="activeTab = 'loans'" 
                    :class="activeTab === 'loans' ? 'bg-white dark:bg-slate-900 text-purple-600 dark:text-purple-400 font-black shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900'"
                    class="px-3.5 py-2 rounded-lg transition cursor-pointer flex items-center gap-1.5 relative">
                <i class="fa-solid fa-hand-holding-dollar text-xs"></i>
                <span>Leningen</span>
                @if(isset($activeLoans) && $activeLoans->count() > 0)
                    <span class="w-2 h-2 rounded-full bg-rose-500 animate-pulse"></span>
                @endif
            </button>
            <button type="button" @click="activeTab = 'transactions'" 
                    :class="activeTab === 'transactions' ? 'bg-white dark:bg-slate-900 text-purple-600 dark:text-purple-400 font-black shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900'"
                    class="px-3.5 py-2 rounded-lg transition cursor-pointer flex items-center gap-1.5">
                <i class="fa-solid fa-receipt text-xs"></i>
                <span>Transacties</span>
            </button>
        </div>
    </div>

    <!-- Balance Overview Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Cash Card -->
        <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-amber-200 dark:border-amber-800/50 shadow-xs relative overflow-hidden">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold text-amber-800 dark:text-amber-400 uppercase tracking-wider">Contant op Zak</span>
                <i class="fa-solid fa-coins text-amber-500 text-xl"></i>
            </div>
            <p class="text-2xl font-bold text-slate-900 dark:text-white font-mono">€{{ number_format($character->cash, 0, ',', '.') }}</p>
            <p class="text-[11px] text-amber-600 dark:text-amber-400 mt-2 font-medium">
                <i class="fa-solid fa-shield-halved mr-1"></i> Kan gerold worden door zakkenrollers.
            </p>
        </div>

        <!-- Bank Card -->
        <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-emerald-200 dark:border-emerald-800/50 shadow-xs relative overflow-hidden">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold text-emerald-800 dark:text-emerald-400 uppercase tracking-wider">Veilig Banktegoed</span>
                <i class="fa-solid fa-vault text-emerald-500 text-xl"></i>
            </div>
            <p class="text-2xl font-bold text-slate-900 dark:text-white font-mono">€{{ number_format($character->bank, 0, ',', '.') }}</p>
            <p class="text-[11px] text-emerald-600 dark:text-emerald-400 mt-2 font-medium">
                <i class="fa-solid fa-shield-check mr-1"></i> 100% gegarandeerd veilig in de kluis.
            </p>
        </div>

        <!-- Open Debt Card -->
        <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-rose-200 dark:border-rose-800/50 shadow-xs relative overflow-hidden">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold text-rose-800 dark:text-rose-400 uppercase tracking-wider">Openstaande Schuld</span>
                <i class="fa-solid fa-hand-holding-dollar text-rose-500 text-xl"></i>
            </div>
            <p class="text-2xl font-bold text-rose-600 dark:text-rose-400 font-mono">€{{ number_format($openstaandeSchuld ?? 0, 0, ',', '.') }}</p>
            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-2 font-medium">
                {{ isset($activeLoans) ? $activeLoans->count() : 0 }} lopende lening(en) actief
            </p>
        </div>

        <!-- Available Borrow Capacity -->
        <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-purple-200 dark:border-purple-800/50 shadow-xs relative overflow-hidden">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold text-purple-800 dark:text-purple-400 uppercase tracking-wider">Beschikbaar Krediet</span>
                <i class="fa-solid fa-chart-line text-purple-500 text-xl"></i>
            </div>
            <p class="text-2xl font-bold text-purple-600 dark:text-purple-400 font-mono">€{{ number_format($beschikbareLening ?? 0, 0, ',', '.') }}</p>
            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-2 font-medium">
                Max €{{ number_format($maxCapacity ?? 0, 0, ',', '.') }} bij Level {{ $character->level }}
            </p>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- TAB 1: FINANCIEEL OVERZICHT & KLUIS        -->
    <!-- ========================================== -->
    <div x-show="activeTab === 'vault'" class="space-y-6">
        <!-- TBS Patiënten Uitkering Overview -->
        <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl border {{ ($allowanceDetails['eligible'] ?? false) ? 'border-emerald-300 dark:border-emerald-800 bg-gradient-to-r from-emerald-50/40 to-white dark:from-emerald-950/20 dark:to-slate-900' : 'border-slate-200 dark:border-slate-800' }} shadow-xs">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-start gap-3.5">
                    <div class="w-12 h-12 rounded-xl {{ ($allowanceDetails['eligible'] ?? false) ? 'bg-emerald-600' : 'bg-slate-200 dark:bg-slate-800' }} text-white flex items-center justify-center shrink-0 shadow-xs">
                        <i class="fa-solid fa-hand-holding-dollar text-xl {{ ($allowanceDetails['eligible'] ?? false) ? 'text-white' : 'text-slate-400' }}"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h2 class="text-sm font-extrabold text-slate-900 dark:text-white uppercase tracking-wide">TBS Patiënten Uitkering</h2>
                            @if($allowanceDetails['eligible'] ?? false)
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-100 dark:bg-emerald-950 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 uppercase">Actief</span>
                            @else
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-700 uppercase">Lid van Bende (Bende Loon)</span>
                            @endif
                        </div>
                        <p class="text-xs text-slate-600 dark:text-slate-400 mt-1 max-w-2xl">
                            @if($allowanceDetails['eligible'] ?? false)
                                Iedere patiënt in de kliniek die <strong>geen lid is van een bende</strong> ontvangt automatisch een basisinkomen van <strong class="text-emerald-600 dark:text-emerald-400 font-extrabold">€1.000 per kwartier (15 minuten)</strong> direct op deze beveiligde bankrekening!
                            @else
                                Omdat je lid bent van bende <strong>{{ $character->gang?->name ?? 'Gang' }}</strong>, ontvang je geen basisuitkering meer, maar verdien je automatisch een uurloon via de smokkelkiosken van jouw bende.
                            @endif
                        </p>
                    </div>
                </div>

                @if($allowanceDetails['eligible'] ?? false)
                    <div class="shrink-0 flex sm:flex-col items-center sm:items-end justify-between bg-emerald-100/60 dark:bg-emerald-950/40 p-3 sm:p-0 rounded-lg"
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
                        <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Volgende uitbetaling</span>
                        <span class="text-base font-extrabold text-emerald-600 dark:text-emerald-400 font-mono" x-text="formatTime()">{{ $allowanceDetails['next_human'] ?? '15m 00s' }}</span>
                        <span class="text-[11px] font-semibold text-emerald-600 dark:text-emerald-400">+€1.000 op bank</span>
                    </div>
                @endif
            </div>
        </div>

        <!-- Deposit and Withdraw Forms -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <!-- Deposit Form -->
            <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xs" x-data="{ cash: {{ $character->cash }} }">
                <h2 class="text-sm font-bold text-slate-800 dark:text-white uppercase tracking-wide flex items-center gap-2 mb-4">
                    <i class="fa-solid fa-arrow-down text-emerald-600"></i>
                    <span>Geld Storten op Bank</span>
                </h2>
                <form method="POST" action="{{ route('bank.deposit') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 mb-1">Bedrag (€ Euro's)</label>
                        <input type="number" name="amount" x-ref="depositInput" min="1" max="{{ $character->cash }}" value="{{ $character->cash }}" required class="w-full px-3 py-2 text-sm border border-slate-300 dark:border-slate-700 rounded-xl bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500 outline-none">
                    </div>

                    <div class="flex items-center gap-2 text-xs">
                        <button type="button" @click="$refs.depositInput.value = Math.floor(cash * 0.5)" class="px-2.5 py-1 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 rounded-lg font-semibold text-slate-700 dark:text-slate-200 cursor-pointer">50%</button>
                        <button type="button" @click="$refs.depositInput.value = cash" class="px-2.5 py-1 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 rounded-lg font-semibold text-slate-700 dark:text-slate-200 cursor-pointer">Alles (€{{ number_format($character->cash, 0, ',', '.') }})</button>
                    </div>

                    <button type="submit" @if($character->cash <= 0) disabled @endif class="w-full bg-emerald-600 hover:bg-emerald-500 disabled:opacity-40 disabled:cursor-not-allowed text-white font-bold py-2.5 px-4 rounded-xl text-xs shadow-xs transition flex items-center justify-center gap-2 cursor-pointer">
                        <i class="fa-solid fa-shield"></i>
                        <span>Storten & Beveiligen</span>
                    </button>
                </form>
            </div>

            <!-- Withdraw Form -->
            <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xs" x-data="{ bank: {{ $character->bank }} }">
                <div class="flex items-center justify-between mb-3">
                    <h2 class="text-sm font-bold text-slate-800 dark:text-white uppercase tracking-wide flex items-center gap-2">
                        <i class="fa-solid fa-arrow-up text-amber-600"></i>
                        <span>Geld Opnemen van Bank</span>
                    </h2>
                    <a href="{{ route('lottery.index') }}" class="text-[10px] font-bold text-purple-600 dark:text-purple-400 hover:underline flex items-center gap-1">
                        <i class="fa-solid fa-ticket"></i> Naar Loterij
                    </a>
                </div>

                <div class="mb-3 p-3 rounded-xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-700/50 flex items-start gap-2.5">
                    <i class="fa-solid fa-percent text-amber-600 dark:text-amber-400 text-xs mt-0.5"></i>
                    <div class="text-[11px] text-amber-900 dark:text-amber-200 leading-snug">
                        <span class="font-extrabold">Kliniek Opnamebelasting: {{ $character->isVip() ? '3% (VIP tarief)' : '5% (Standaard tarief)' }}</span>
                        <span class="text-amber-700 dark:text-amber-300 block mt-0.5">
                            Alle opnamebelasting vloeit 100% door naar de <a href="{{ route('lottery.index') }}" class="underline font-bold text-amber-900 dark:text-amber-100">TBS Afdelingsloterij Jackpot</a>!
                        </span>
                    </div>
                </div>

                <form method="POST" action="{{ route('bank.withdraw') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 mb-1">Bedrag (€ Euro's)</label>
                        <input type="number" name="amount" x-ref="withdrawInput" min="1" max="{{ $character->bank }}" value="100" required class="w-full px-3 py-2 text-sm border border-slate-300 dark:border-slate-700 rounded-xl bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-amber-500 outline-none">
                    </div>

                    <div class="flex items-center gap-2 text-xs">
                        <button type="button" @click="$refs.withdrawInput.value = 100" class="px-2.5 py-1 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 rounded-lg font-semibold text-slate-700 dark:text-slate-200 cursor-pointer">€100</button>
                        <button type="button" @click="$refs.withdrawInput.value = 500" class="px-2.5 py-1 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 rounded-lg font-semibold text-slate-700 dark:text-slate-200 cursor-pointer">€500</button>
                        <button type="button" @click="$refs.withdrawInput.value = bank" class="px-2.5 py-1 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 rounded-lg font-semibold text-slate-700 dark:text-slate-200 cursor-pointer">Alles (€{{ number_format($character->bank, 0, ',', '.') }})</button>
                    </div>

                    <button type="submit" @if($character->bank <= 0) disabled @endif class="w-full bg-slate-900 dark:bg-purple-600 hover:bg-slate-800 dark:hover:bg-purple-700 disabled:opacity-40 disabled:cursor-not-allowed text-white font-bold py-2.5 px-4 rounded-xl text-xs shadow-xs transition flex items-center justify-center gap-2 cursor-pointer">
                        <i class="fa-solid fa-money-bill-wave"></i>
                        <span>Contant Opnemen (-{{ $character->isVip() ? '3%' : '5%' }} tax)</span>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- TAB 2: LENINGEN & KREDIET (112Simulator)   -->
    <!-- ========================================== -->
    <div x-show="activeTab === 'loans'" class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        
        <!-- LINKER KOLOM: Nieuwe lening aanvragen -->
        <div class="lg:col-span-5 bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xs flex flex-col justify-between"
             x-data="{ 
                 loanAmount: {{ min(100000, max(10000, $beschikbareLening ?? 0)) }},
                 maxLoan: {{ $beschikbareLening ?? 0 }},
                 interestRate: 8.5,
                 get interestAmount() { return Math.round((this.loanAmount * this.interestRate) / 100); },
                 get totalDue() { return parseInt(this.loanAmount || 0) + this.interestAmount; },
                 formatEuro(n) { return new Intl.NumberFormat('nl-NL').format(n); }
             }">
            <div>
                <h3 class="text-base font-black text-slate-900 dark:text-white uppercase tracking-wide flex items-center gap-2 mb-4">
                    <i class="fa-solid fa-file-invoice-dollar text-purple-600"></i>
                    <span>Nieuwe lening aanvragen</span>
                </h3>

                <!-- Status Summary Box -->
                <div class="bg-slate-50 dark:bg-slate-800/60 rounded-xl p-4 border border-slate-200 dark:border-slate-700/60 space-y-2 mb-5 text-xs">
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500 dark:text-slate-400">Je level:</span>
                        <span class="font-extrabold text-slate-900 dark:text-white">Level {{ $character->level }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500 dark:text-slate-400">Maximale lening:</span>
                        <span class="font-extrabold text-slate-900 dark:text-white font-mono">€ {{ number_format($maxCapacity ?? 0, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500 dark:text-slate-400">Openstaand:</span>
                        <span class="font-extrabold text-rose-600 dark:text-rose-400 font-mono">€ {{ number_format($openstaandeSchuld ?? 0, 0, ',', '.') }}</span>
                    </div>
                    <div class="pt-2 border-t border-slate-200 dark:border-slate-700 flex items-center justify-between text-sm">
                        <span class="font-bold text-slate-700 dark:text-slate-300">Beschikbaar:</span>
                        <span class="font-extrabold text-emerald-600 dark:text-emerald-400 font-mono">€ {{ number_format($beschikbareLening ?? 0, 0, ',', '.') }}</span>
                    </div>
                </div>

                <!-- Aanvraag Formulier -->
                <form method="POST" action="{{ route('bank.loans.request') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Gewenst bedrag</label>
                        <div class="relative">
                            <span class="absolute left-3 top-2.5 text-sm font-bold text-slate-400">€</span>
                            <input type="number" name="amount" x-model.number="loanAmount" min="10000" max="{{ $beschikbareLening ?? 0 }}" required
                                   class="w-full pl-8 pr-3 py-2.5 text-sm font-mono font-bold border border-slate-300 dark:border-slate-700 rounded-xl bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-purple-500 outline-none">
                        </div>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">
                            Minimaal € 10.000 en maximaal € {{ number_format($beschikbareLening ?? 0, 0, ',', '.') }} beschikbaar om te lenen.
                        </p>
                    </div>

                    <!-- Quick Amount Buttons -->
                    <div class="grid grid-cols-4 gap-1.5 text-[11px]">
                        <button type="button" @click="loanAmount = 10000" class="py-1.5 px-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 rounded-lg font-bold text-slate-700 dark:text-slate-200 cursor-pointer text-center">€10k</button>
                        <button type="button" @click="loanAmount = 25000" class="py-1.5 px-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 rounded-lg font-bold text-slate-700 dark:text-slate-200 cursor-pointer text-center">€25k</button>
                        <button type="button" @click="loanAmount = 50000" class="py-1.5 px-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 rounded-lg font-bold text-slate-700 dark:text-slate-200 cursor-pointer text-center">€50k</button>
                        <button type="button" @click="loanAmount = maxLoan" class="py-1.5 px-2 bg-purple-100 dark:bg-purple-950/60 hover:bg-purple-200 rounded-lg font-extrabold text-purple-700 dark:text-purple-300 cursor-pointer text-center">Max</button>
                    </div>

                    <!-- Uitbetalen Bestemming -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Uitbetalen op</label>
                        <select name="deposit_to" class="w-full px-3 py-2 text-xs font-bold border border-slate-300 dark:border-slate-700 rounded-xl bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white outline-none">
                            <option value="cash">Contant op zak (Direct besteden)</option>
                            <option value="bank">Direct op bankrekening (Veilig)</option>
                        </select>
                    </div>

                    <!-- Renteconstructie Box (Exact zoals 112Simulator) -->
                    <div class="p-3.5 rounded-xl bg-purple-50 dark:bg-purple-950/40 border border-purple-200 dark:border-purple-800/60 text-xs space-y-2 text-purple-900 dark:text-purple-200">
                        <div class="font-extrabold uppercase tracking-wide flex items-center justify-between">
                            <div class="flex items-center gap-1.5">
                                <i class="fa-solid fa-percent text-purple-600 dark:text-purple-400"></i>
                                <span>Rente & Leningtax (8,5%)</span>
                            </div>
                            <a href="{{ route('lottery.index') }}" class="text-[10px] font-bold text-purple-700 dark:text-purple-300 hover:underline flex items-center gap-1">
                                <i class="fa-solid fa-ticket text-amber-500"></i> Naar Loterij
                            </a>
                        </div>
                        <p class="leading-relaxed text-[11px] text-purple-800 dark:text-purple-300">
                            Je betaalt over het geleende bedrag eenmalig <strong>8,5% rente</strong>.
                            Deze leningtax vloeit <strong>100% direct door naar de <a href="{{ route('lottery.index') }}" class="underline font-bold text-purple-900 dark:text-purple-100">TBS Afdelingsloterij Jackpot</a></strong>!
                        </p>
                        <div class="pt-2 border-t border-purple-200 dark:border-purple-800 flex items-center justify-between text-xs">
                            <span class="text-slate-600 dark:text-purple-300">Leningtax naar loterijpot:</span>
                            <span class="font-black text-amber-600 dark:text-amber-400 font-mono">+ € <span x-text="formatEuro(interestAmount)"></span></span>
                        </div>
                        <div class="flex items-center justify-between text-xs pt-1 border-t border-dashed border-purple-200 dark:border-purple-800/60">
                            <span class="text-slate-600 dark:text-purple-300">Totaal terug te betalen:</span>
                            <span class="font-black text-purple-900 dark:text-purple-100 font-mono">€ <span x-text="formatEuro(totalDue)"></span></span>
                        </div>
                    </div>

                    <button type="submit" 
                            @if(($beschikbareLening ?? 0) < 10000) disabled @endif
                            class="w-full py-3 px-4 rounded-xl bg-blue-600 hover:bg-blue-700 disabled:opacity-40 disabled:cursor-not-allowed text-white font-black text-xs shadow-md transition flex items-center justify-center gap-2 cursor-pointer">
                        <i class="fa-solid fa-check"></i>
                        <span>Lening aanvragen</span>
                    </button>
                </form>
            </div>
        </div>

        <!-- RECHTER KOLOM: Lopende leningen (Cards zoals 112Simulator) -->
        <div class="lg:col-span-7 space-y-4">
            <h3 class="text-base font-black text-slate-900 dark:text-white uppercase tracking-wide flex items-center gap-2">
                <i class="fa-solid fa-clock-rotate-left text-purple-600"></i>
                <span>Lopende leningen</span>
            </h3>

            @if(isset($activeLoans) && $activeLoans->count() > 0)
                <div class="space-y-4">
                    @foreach($activeLoans as $loan)
                        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-xs space-y-4"
                             x-data="{ showRepayModal: false, repayAmount: {{ $loan->remaining_due }}, repaySource: 'cash' }">
                            
                            <!-- Header rij -->
                            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 rounded-lg bg-amber-100 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center text-sm font-bold">
                                        <i class="fa-solid fa-file-invoice"></i>
                                    </div>
                                    <div>
                                        <span class="text-xs font-black text-slate-900 dark:text-white">Gestart op {{ $loan->started_at->format('d-m-Y') }}</span>
                                        <span class="text-[10px] text-slate-400 block">Leningnummer: {{ $loan->loan_number }}</span>
                                    </div>
                                </div>
                                <div>
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider {{ $loan->total_paid > 0 ? 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300' : 'bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-300' }}">
                                        {{ $loan->total_paid > 0 ? 'Deels afgelost' : 'Nog niet afgelost' }}
                                    </span>
                                </div>
                            </div>

                            <!-- Cijfers Grid -->
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
                                <div>
                                    <span class="text-[10px] text-slate-400 uppercase font-bold block">Leenbedrag</span>
                                    <span class="font-extrabold text-slate-900 dark:text-white font-mono">€ {{ number_format($loan->amount, 0, ',', '.') }}</span>
                                </div>
                                <div>
                                    <span class="text-[10px] text-slate-400 uppercase font-bold block">Rente (8,5%)</span>
                                    <span class="font-bold text-slate-600 dark:text-slate-300 font-mono">€ {{ number_format($loan->interest_amount, 0, ',', '.') }}</span>
                                </div>
                                <div>
                                    <span class="text-[10px] text-slate-400 uppercase font-bold block">Nog te betalen</span>
                                    <span class="font-black text-rose-600 dark:text-rose-400 font-mono">€ {{ number_format($loan->remaining_due, 0, ',', '.') }}</span>
                                </div>
                                <div>
                                    <span class="text-[10px] text-slate-400 uppercase font-bold block">Afgelost tot nu toe</span>
                                    <span class="font-bold text-emerald-600 dark:text-emerald-400 font-mono">€ {{ number_format($loan->total_paid, 0, ',', '.') }}</span>
                                </div>
                            </div>

                            <!-- Voortgangsbalk -->
                            <div>
                                <div class="flex items-center justify-between text-[11px] font-bold mb-1">
                                    <span class="text-slate-400">Aflosstatus:</span>
                                    <span class="text-purple-600 dark:text-purple-400 font-extrabold">{{ $loan->getProgressPercentage() }}%</span>
                                </div>
                                <div class="w-full bg-slate-100 dark:bg-slate-800 rounded-full h-2 overflow-hidden">
                                    <div class="bg-gradient-to-r from-purple-500 to-indigo-600 h-full rounded-full transition-all duration-300" 
                                         style="width: {{ $loan->getProgressPercentage() }}%;"></div>
                                </div>
                            </div>

                            <!-- Knoppen balk (Exact zoals 112Simulator!) -->
                            <div class="flex items-center justify-end gap-2 pt-2">
                                <button type="button" @click="showRepayModal = !showRepayModal"
                                        class="py-2 px-3.5 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-bold text-xs shadow-xs transition flex items-center gap-1.5 cursor-pointer">
                                    <i class="fa-solid fa-coins text-xs"></i>
                                    <span>Deel aflossen</span>
                                </button>

                                <form method="POST" action="{{ route('bank.loans.repay-full', $loan) }}">
                                    @csrf
                                    <input type="hidden" name="source" value="cash">
                                    <button type="submit" 
                                            class="py-2 px-3.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-xs transition flex items-center gap-1.5 cursor-pointer">
                                        <i class="fa-solid fa-check-double text-xs"></i>
                                        <span>Alles aflossen (€{{ number_format($loan->remaining_due, 0, ',', '.') }})</span>
                                    </button>
                                </form>
                            </div>

                            <!-- Inline Deel Aflossen Formulier -->
                            <div x-show="showRepayModal" x-cloak class="mt-3 p-4 bg-slate-50 dark:bg-slate-800/80 rounded-xl border border-purple-200 dark:border-purple-800/60 space-y-3">
                                <h4 class="text-xs font-black text-slate-800 dark:text-white uppercase">Deel aflossen op {{ $loan->loan_number }}</h4>
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
                                                <option value="bank">Banktegoed (€{{ number_format($character->bank, 0, ',', '.') }})</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="flex items-center justify-end gap-2">
                                        <button type="button" @click="showRepayModal = false" class="py-1.5 px-3 rounded-lg text-slate-500 hover:bg-slate-200 dark:hover:bg-slate-700 font-bold text-xs">Annuleren</button>
                                        <button type="submit" class="py-1.5 px-4 rounded-lg bg-purple-600 hover:bg-purple-700 text-white font-bold text-xs shadow-xs">Aflossen Bevestigen</button>
                                    </div>
                                </form>
                            </div>

                        </div>
                    @endforeach
                </div>
            @else
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-8 text-center shadow-xs">
                    <div class="w-14 h-14 rounded-2xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-2xl mx-auto mb-3">
                        <i class="fa-solid fa-circle-check"></i>
                    </div>
                    <h4 class="text-sm font-black text-slate-900 dark:text-white">Geen openstaande leningen</h4>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 max-w-sm mx-auto">
                        Je hebt op dit moment geen openstaande schulden. Je kunt links direct een lening aanvragen tot maximaal <strong>€ {{ number_format($beschikbareLening ?? 0, 0, ',', '.') }}</strong>!
                    </p>
                </div>
            @endif

            <!-- Afgeloste leningen geschiedenis -->
            @if(isset($paidLoans) && $paidLoans->count() > 0)
                <div class="pt-4 border-t border-slate-200 dark:border-slate-800 space-y-2">
                    <h4 class="text-xs font-black text-slate-500 uppercase tracking-wider">Recent volledig afgelost</h4>
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
    <div x-show="activeTab === 'transactions'" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-xs space-y-4">
        <div class="flex items-center justify-between">
            <h3 class="text-base font-black text-slate-900 dark:text-white uppercase tracking-wide flex items-center gap-2">
                <i class="fa-solid fa-receipt text-purple-600"></i>
                <span>Transactiehistorie & Mutaties</span>
            </h3>
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
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wide
                                        @if(in_array($tx->type, ['deposit', 'allowance', 'loan_received'])) bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300
                                        @else bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300 @endif">
                                        {{ $tx->type }}
                                    </span>
                                </td>
                                <td class="py-3 px-3 font-medium text-slate-800 dark:text-slate-200">
                                    {{ $tx->description }}
                                </td>
                                <td class="py-3 px-3 text-right font-mono font-extrabold {{ $tx->amount >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
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
