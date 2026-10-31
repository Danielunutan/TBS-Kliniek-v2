@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xs border border-slate-200/90 dark:border-slate-800 p-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span>
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-400">Ondergrondse Gokhal</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">Casino & Kansspelen</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                Welkom in de ketelruimte. Speel om contante Euro's (€). Daag het huis of medespelers uit!
            </p>
        </div>
        <div class="w-10 h-10 rounded-xl bg-rose-50 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 flex items-center justify-center text-lg border border-rose-100 dark:border-rose-900/60">
            <i class="fa-solid fa-dice"></i>
        </div>
    </div>

    <!-- Quick Coinflip Game -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xs border border-slate-200/90 dark:border-slate-800 p-6 text-center max-w-xl mx-auto" x-data="{
        bet: 10,
        flipping: false,
        resultMsg: '',
        isWin: false,
        cash: {{ (int) ($character?->cash ?? 0) }},
        async playCoinflip() {
            if (this.flipping) return;
            if (this.bet < 10 || this.cash < this.bet) {
                this.resultMsg = 'Je hebt onvoldoende contant geld voor deze inzet!';
                this.isWin = false;
                return;
            }
            this.flipping = true;
            this.resultMsg = '';
            
            try {
                const res = await fetch('{{ route('casino.play') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ game: 'coinflip', bet: this.bet })
                });
                const data = await res.json();
                
                setTimeout(() => {
                    this.flipping = false;
                    this.resultMsg = data.message;
                    this.isWin = data.win;
                    if (data.cash !== undefined) this.cash = data.cash;
                }, 1000);
            } catch (e) {
                this.flipping = false;
                this.resultMsg = 'Er is een fout opgetreden bij het opgooien.';
                this.isWin = false;
            }
        }
    }">
        <div class="inline-block p-4 bg-amber-50 dark:bg-amber-950/60 rounded-2xl mb-3 text-amber-500 dark:text-amber-400 text-5xl border border-amber-100 dark:border-amber-900/60 transition-transform duration-700" :class="flipping ? 'animate-spin' : ''">
            <i class="fa-solid fa-coins"></i>
        </div>
        <h2 class="text-lg font-bold text-slate-900 dark:text-white mb-1">Kop of Munt tegen Mo</h2>
        <p class="text-xs text-slate-500 dark:text-slate-400 mb-4">Zet in en laat de munt draaien. Verdubbel je inzet of verlies alles!</p>

        <!-- Live result banner -->
        <div x-show="resultMsg" x-transition class="mb-4 p-3 rounded-xl text-xs font-bold" :class="isWin ? 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800' : 'bg-rose-50 dark:bg-rose-950/60 text-rose-800 dark:text-rose-300 border border-rose-200 dark:border-rose-800'" x-text="resultMsg"></div>

        <form @submit.prevent="playCoinflip()" class="max-w-xs mx-auto space-y-3">
            @csrf
            <input type="hidden" name="game" value="coinflip">
            <div>
                <div class="flex justify-between text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">
                    <span>Inzet (Euro)</span>
                    <span>Beschikbaar: €<span x-text="cash.toLocaleString('nl-NL')"></span></span>
                </div>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <span class="text-slate-400 dark:text-slate-500 font-extrabold text-xs">€</span>
                    </div>
                    <input type="number" name="bet" min="10" x-model.number="bet" :max="Math.max(10, cash)" 
                           class="block w-full pl-8 pr-3 py-2 border border-slate-200 dark:border-slate-700 rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500 dark:focus:border-purple-500 text-xs font-bold bg-slate-50 dark:bg-slate-800/90 text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 transition" 
                           placeholder="Inzet...">
                </div>
            </div>
            <button type="submit" :disabled="cash < 10 || cash < bet || bet < 10 || flipping" class="w-full py-2.5 px-4 bg-purple-600 hover:bg-purple-700 dark:bg-purple-600 dark:hover:bg-purple-500 disabled:opacity-50 disabled:cursor-not-allowed disabled:active:scale-100 text-white rounded-xl font-black text-xs shadow-xs transition-all duration-150 active:scale-95 flex items-center justify-center gap-2 cursor-pointer">
                <i class="fa-solid fa-play text-[10px]" :class="flipping ? 'animate-spin' : ''"></i>
                <span x-text="flipping ? 'Munt draait...' : 'Gooi Munt Op (50% Win Kans)'"></span>
            </button>
        </form>
    </div>

    <!-- Other Casino Games Grid -->
    <div>
        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-3">Casino & Gokspellen</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">

            <!-- LOTERIJ JACKPOT PROMO CARD -->
            <a href="{{ route('lottery.index') }}" class="p-5 bg-white dark:bg-slate-900 rounded-2xl border-2 border-amber-400/60 dark:border-amber-500/60 shadow-xs hover:border-amber-400 dark:hover:border-amber-400 transition-all duration-200 group flex items-center justify-between gap-4 col-span-1 sm:col-span-2 lg:col-span-3">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center text-xl border border-amber-200/80 dark:border-amber-800/70 group-hover:scale-105 transition-transform shrink-0 shadow-2xs">
                        <i class="fa-solid fa-crown"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h4 class="font-extrabold text-slate-900 dark:text-slate-100 text-sm group-hover:text-purple-600 dark:group-hover:text-amber-400 transition">TBS Afdelingsloterij (Jackpot Pot)</h4>
                            <span class="text-[9px] font-black uppercase tracking-wider px-2 py-0.5 rounded-full bg-amber-400 text-slate-950 shadow-2xs">Mega Pot</span>
                        </div>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Iedere cent bankopname-belasting stroomt in deze pot. Koop loten met contant geld of sterren en grijp de hoofdprijs!</p>
                    </div>
                </div>
                <div class="shrink-0 hidden sm:flex items-center text-xs font-black text-amber-700 dark:text-amber-400 group-hover:translate-x-1 transition-transform">
                    <span>Loten Kopen</span> <i class="fa-solid fa-arrow-right ml-1.5 text-xs"></i>
                </div>
            </a>
            
            <a href="{{ route('rps.index') }}" class="p-5 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/90 dark:border-slate-800 shadow-xs hover:border-purple-300 dark:hover:border-purple-700/80 transition-all duration-200 group flex items-start gap-4">
                <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-lg border border-indigo-100 dark:border-indigo-900/60 group-hover:scale-105 transition-transform">
                    <i class="fa-regular fa-hand-scissors"></i>
                </div>
                <div>
                    <h4 class="font-bold text-slate-900 dark:text-white text-xs group-hover:text-purple-600 dark:group-hover:text-purple-400 transition">Steen Papier Schaar</h4>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Speel live tegen andere patiënten voor de pot.</p>
                </div>
            </a>

            <a href="{{ route('highlow.index') }}" class="p-5 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/90 dark:border-slate-800 shadow-xs hover:border-purple-300 dark:hover:border-purple-700/80 transition-all duration-200 group flex items-start gap-4">
                <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-lg border border-emerald-100 dark:border-emerald-900/60 group-hover:scale-105 transition-transform">
                    <i class="fa-solid fa-arrow-down-up-across-line"></i>
                </div>
                <div>
                    <h4 class="font-bold text-slate-900 dark:text-white text-xs group-hover:text-purple-600 dark:group-hover:text-purple-400 transition">Hoger & Lager</h4>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Raad of de volgende kaart hoger of lager is.</p>
                </div>
            </a>

            <a href="{{ route('slots.index') }}" class="p-5 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/90 dark:border-slate-800 shadow-xs hover:border-purple-300 dark:hover:border-purple-700/80 transition-all duration-200 group flex items-start gap-4">
                <div class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center text-lg border border-amber-100 dark:border-amber-900/60 group-hover:scale-105 transition-transform">
                    <i class="fa-solid fa-lemon"></i>
                </div>
                <div>
                    <h4 class="font-bold text-slate-900 dark:text-white text-xs group-hover:text-purple-600 dark:group-hover:text-purple-400 transition">Fruitautomaat</h4>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Draai aan de hendel van de oude gokkast.</p>
                </div>
            </a>

            <a href="{{ route('roulette.index') }}" class="p-5 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/90 dark:border-slate-800 shadow-xs hover:border-purple-300 dark:hover:border-purple-700/80 transition-all duration-200 group flex items-start gap-4">
                <div class="w-10 h-10 rounded-xl bg-rose-50 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 flex items-center justify-center text-lg border border-rose-100 dark:border-rose-900/60 group-hover:scale-105 transition-transform">
                    <i class="fa-solid fa-life-ring"></i>
                </div>
                <div>
                    <h4 class="font-bold text-slate-900 dark:text-white text-xs group-hover:text-purple-600 dark:group-hover:text-purple-400 transition">Roulette Tafel</h4>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Zet in op rood, zwart of jouw geluksgetal.</p>
                </div>
            </a>

            <a href="{{ route('numbergame.index') }}" class="p-5 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/90 dark:border-slate-800 shadow-xs hover:border-purple-300 dark:hover:border-purple-700/80 transition-all duration-200 group flex items-start gap-4">
                <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 flex items-center justify-center text-lg border border-blue-100 dark:border-blue-900/60 group-hover:scale-105 transition-transform">
                    <i class="fa-solid fa-hashtag"></i>
                </div>
                <div>
                    <h4 class="font-bold text-slate-900 dark:text-white text-xs group-hover:text-purple-600 dark:group-hover:text-purple-400 transition">Nummerspel</h4>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Kies het juiste cijfer en win 15x je inzet.</p>
                </div>
            </a>

        </div>
    </div>
</div>
@endsection
