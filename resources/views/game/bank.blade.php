@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white p-6 rounded-lg border border-slate-200 shadow-sm">
        <h1 class="text-xl font-bold text-slate-900 uppercase tracking-wide flex items-center gap-2">
            <i class="fa-solid fa-building-columns text-purple-700"></i>
            <span>De Kliniekbank & Bewaarkluis</span>
        </h1>
        <p class="text-xs text-slate-600 mt-1">
            Contant geld op zak kan door andere bewoners worden gerold! Geld op de bank is veilig opgeslagen in de zwaar beveiligde kluis van de kliniek.
        </p>
    </div>

    <!-- Balance Overview Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <!-- Cash Card -->
        <div class="bg-white p-5 rounded-lg border border-amber-200 shadow-sm relative overflow-hidden">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold text-amber-800 uppercase tracking-wider">Contant op Zak (Onveilig)</span>
                <i class="fa-solid fa-coins text-amber-500 text-xl"></i>
            </div>
            <p class="text-3xl font-bold text-slate-900">{{ number_format($character->cash, 0, ',', '.') }} <span class="text-lg text-amber-600">KC</span></p>
            <p class="text-[11px] text-rose-600 mt-2 font-medium">
                <i class="fa-solid fa-triangle-exclamation mr-1"></i> Kan ieder moment worden gestolen door zakkenrollers.
            </p>
        </div>

        <!-- Bank Card -->
        <div class="bg-white p-5 rounded-lg border border-emerald-200 shadow-sm relative overflow-hidden">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold text-emerald-800 uppercase tracking-wider">Veilig Banktegoed (Beschermd)</span>
                <i class="fa-solid fa-vault text-emerald-500 text-xl"></i>
            </div>
            <p class="text-3xl font-bold text-slate-900">{{ number_format($character->bank, 0, ',', '.') }} <span class="text-lg text-emerald-600">KC</span></p>
            <p class="text-[11px] text-emerald-700 mt-2 font-medium">
                <i class="fa-solid fa-shield-check mr-1"></i> 100% gegarandeerd veilig tegen diefstallen.
            </p>
        </div>
    </div>

    <!-- TBS Patiënten Uitkering Overview -->
    <div class="bg-white p-5 rounded-lg border {{ ($allowanceDetails['eligible'] ?? false) ? 'border-emerald-300 bg-gradient-to-r from-emerald-50/60 to-white' : 'border-slate-200' }} shadow-sm">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-start gap-3.5">
                <div class="w-12 h-12 rounded-xl {{ ($allowanceDetails['eligible'] ?? false) ? 'bg-emerald-600' : 'bg-slate-200' }} text-white flex items-center justify-center shrink-0 shadow-sm">
                    <i class="fa-solid fa-hand-holding-dollar text-xl {{ ($allowanceDetails['eligible'] ?? false) ? 'text-white' : 'text-slate-400' }}"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h2 class="text-sm font-extrabold text-slate-900 uppercase tracking-wide">TBS Patiënten Uitkering</h2>
                        @if($allowanceDetails['eligible'] ?? false)
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 border border-emerald-200 uppercase">Actief</span>
                        @else
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-slate-100 text-slate-600 border border-slate-200 uppercase">Lid van Bende (Bende Loon)</span>
                        @endif
                    </div>
                    <p class="text-xs text-slate-600 mt-1 max-w-2xl">
                        @if($allowanceDetails['eligible'] ?? false)
                            Iedere patiënt in de kliniek die <strong>geen lid is van een bende</strong> ontvangt automatisch een basisinkomen van <strong class="text-emerald-700 font-extrabold">€1.000 per kwartier (15 minuten)</strong> direct op deze beveiligde bankrekening!
                        @else
                            Omdat je lid bent van bende <strong>{{ $character->gang?->name ?? 'Gang' }}</strong>, ontvang je geen basisuitkering meer, maar verdien je automatisch een uurloon via de smokkelkiosken van jouw bende.
                        @endif
                    </p>
                </div>
            </div>

            @if($allowanceDetails['eligible'] ?? false)
                <div class="shrink-0 flex sm:flex-col items-center sm:items-end justify-between bg-emerald-100/60 sm:bg-transparent p-3 sm:p-0 rounded-lg"
                     x-data="{ 
                         secondsLeft: {{ $allowanceDetails['seconds_until_next'] ?? 900 }},
                         init() {
                             setInterval(() => {
                                 if (this.secondsLeft > 0) {
                                     this.secondsLeft--;
                                 } else {
                                     this.secondsLeft = 900;
                                 }
                             }, 1000);
                         },
                         formatTime() {
                             const m = Math.floor(this.secondsLeft / 60);
                             const s = this.secondsLeft % 60;
                             return `${m}m ${s < 10 ? '0' : ''}${s}s`;
                         }
                     }">
                    <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Volgende uitbetaling</span>
                    <span class="text-base font-extrabold text-emerald-700 font-mono" x-text="formatTime()">{{ $allowanceDetails['next_human'] ?? '15m 00s' }}</span>
                    <span class="text-[11px] font-semibold text-emerald-600">+€1.000 op bank</span>
                </div>
            @endif
        </div>
    </div>

    <!-- Deposit and Withdraw Forms -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
        <!-- Deposit Form -->
        <div class="bg-white p-6 rounded-lg border border-slate-200 shadow-sm" x-data="{ cash: {{ $character->cash }} }">
            <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wide flex items-center gap-2 mb-4">
                <i class="fa-solid fa-arrow-down text-emerald-600"></i>
                <span>Geld Storten op Bank</span>
            </h2>
            <form method="POST" action="{{ route('bank.deposit') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1">Bedrag (€ Euro's)</label>
                    <input type="number" name="amount" x-ref="depositInput" min="1" max="{{ $character->cash }}" value="{{ $character->cash }}" required class="w-full px-3 py-2 text-sm border border-slate-300 rounded focus:ring-2 focus:ring-emerald-500 outline-none">
                </div>

                <div class="flex items-center gap-2 text-xs">
                    <button type="button" @click="$refs.depositInput.value = Math.floor(cash * 0.5)" class="px-2 py-1 bg-slate-100 hover:bg-slate-200 rounded font-semibold text-slate-700">50%</button>
                    <button type="button" @click="$refs.depositInput.value = cash" class="px-2 py-1 bg-slate-100 hover:bg-slate-200 rounded font-semibold text-slate-700">Alles (€{{ number_format($character->cash, 0, ',', '.') }})</button>
                </div>

                <button type="submit" @if($character->cash <= 0) disabled @endif class="w-full bg-emerald-600 hover:bg-emerald-500 disabled:opacity-40 disabled:cursor-not-allowed text-white font-bold py-2.5 px-4 rounded text-xs shadow transition flex items-center justify-center gap-2 cursor-pointer">
                    <i class="fa-solid fa-shield"></i>
                    <span>Storten & Beveiligen</span>
                </button>
            </form>
        </div>

        <!-- Withdraw Form -->
        <div class="bg-white p-6 rounded-lg border border-slate-200 shadow-sm" x-data="{ bank: {{ $character->bank }} }">
            <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wide flex items-center gap-2 mb-4">
                <i class="fa-solid fa-arrow-up text-amber-600"></i>
                <span>Geld Opnemen van Bank</span>
            </h2>
            <form method="POST" action="{{ route('bank.withdraw') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1">Bedrag (€ Euro's)</label>
                    <input type="number" name="amount" x-ref="withdrawInput" min="1" max="{{ $character->bank }}" value="100" required class="w-full px-3 py-2 text-sm border border-slate-300 rounded focus:ring-2 focus:ring-amber-500 outline-none">
                </div>

                <div class="flex items-center gap-2 text-xs">
                    <button type="button" @click="$refs.withdrawInput.value = 100" class="px-2 py-1 bg-slate-100 hover:bg-slate-200 rounded font-semibold text-slate-700">€100</button>
                    <button type="button" @click="$refs.withdrawInput.value = 500" class="px-2 py-1 bg-slate-100 hover:bg-slate-200 rounded font-semibold text-slate-700">€500</button>
                    <button type="button" @click="$refs.withdrawInput.value = bank" class="px-2 py-1 bg-slate-100 hover:bg-slate-200 rounded font-semibold text-slate-700">Alles (€{{ number_format($character->bank, 0, ',', '.') }})</button>
                </div>

                <button type="submit" @if($character->bank <= 0) disabled @endif class="w-full bg-slate-900 hover:bg-slate-800 disabled:opacity-40 disabled:cursor-not-allowed text-white font-bold py-2.5 px-4 rounded-xl text-xs shadow-xs transition flex items-center justify-center gap-2 cursor-pointer">
                    <i class="fa-solid fa-money-bill-wave"></i>
                    <span>Contant Opnemen</span>
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
