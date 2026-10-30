@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-xl shadow-xs border border-slate-200/80 p-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span>
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Ondergrondse Gokhal</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Casino & Kansspelen</h1>
            <p class="text-xs text-slate-500 mt-1">
                Welkom in de ketelruimte. Speel om contante Euro's (€). Daag het huis of medespelers uit!
            </p>
        </div>
        <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-lg border border-rose-100">
            <i class="fa-solid fa-dice"></i>
        </div>
    </div>

    <!-- Quick Coinflip Game -->
    <div class="bg-white rounded-xl shadow-xs border border-slate-200/80 p-6 text-center max-w-xl mx-auto" x-data="{
        bet: 10,
        flipping: false,
        resultMsg: '',
        isWin: false,
        cash: {{ $character->cash }},
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
        <div class="inline-block p-4 bg-amber-50 rounded-2xl mb-3 text-amber-500 text-5xl border border-amber-100 transition-transform duration-700" :class="flipping ? 'animate-spin' : ''">
            <i class="fa-solid fa-coins"></i>
        </div>
        <h2 class="text-lg font-bold text-slate-900 mb-1">Kop of Munt tegen Mo</h2>
        <p class="text-xs text-slate-500 mb-4">Zet in en laat de munt draaien. Verdubbel je inzet of verlies alles!</p>

        <!-- Live result banner -->
        <div x-show="resultMsg" x-transition class="mb-4 p-3 rounded-xl text-xs font-bold" :class="isWin ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-rose-50 text-rose-800 border border-rose-200'" x-text="resultMsg"></div>

        <form @submit.prevent="playCoinflip()" class="max-w-xs mx-auto space-y-3">
            @csrf
            <input type="hidden" name="game" value="coinflip">
            <div>
                <div class="flex justify-between text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">
                    <span>Inzet (Euro)</span>
                    <span>Beschikbaar: €<span x-text="cash.toLocaleString('nl-NL')"></span></span>
                </div>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <span class="text-slate-400 font-extrabold text-xs">€</span>
                    </div>
                    <input type="number" name="bet" min="10" x-model.number="bet" :max="Math.max(10, cash)" 
                           class="block w-full pl-8 pr-3 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-xs font-bold bg-slate-50 text-slate-900" 
                           placeholder="Inzet...">
                </div>
            </div>
            <button type="submit" :disabled="cash < 10 || cash < bet || bet < 10 || flipping" class="w-full py-2.5 px-4 bg-slate-900 hover:bg-slate-800 disabled:opacity-40 text-white rounded-xl font-bold text-xs shadow-xs transition flex items-center justify-center gap-2 cursor-pointer">
                <i class="fa-solid fa-play text-[10px]" :class="flipping ? 'animate-spin' : ''"></i>
                <span x-text="flipping ? 'Munt draait...' : 'Gooi Munt Op (50% Win Kans)'"></span>
            </button>
        </form>
    </div>

    <!-- Other Casino Games Grid -->
    <div>
        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-3">Casino & Gokspellen</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">

            <!-- LOTERIJ JACKPOT PROMO CARD -->
            <a href="{{ route('lottery.index') }}" class="p-5 bg-gradient-to-r from-amber-50/70 via-purple-50/50 to-white dark:bg-[#180d2e] rounded-xl border-2 border-amber-400/60 shadow-xs hover:border-amber-400 transition group flex items-center justify-between gap-4 col-span-1 sm:col-span-2 lg:col-span-3">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-amber-400/25 text-amber-600 dark:text-amber-400 flex items-center justify-center text-xl border border-amber-400/40 group-hover:scale-105 transition-transform shrink-0 shadow-2xs">
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
            
            <a href="{{ route('rps.index') }}" class="p-5 bg-white rounded-xl border border-slate-200/80 shadow-xs hover:border-slate-400 transition group flex items-start gap-4">
                <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-lg border border-indigo-100 group-hover:scale-105 transition-transform">
                    <i class="fa-regular fa-hand-scissors"></i>
                </div>
                <div>
                    <h4 class="font-bold text-slate-900 text-xs group-hover:text-indigo-600 transition">Steen Papier Schaar</h4>
                    <p class="text-[11px] text-slate-500 mt-0.5">Speel live tegen andere patiënten voor de pot.</p>
                </div>
            </a>

            <a href="{{ route('highlow.index') }}" class="p-5 bg-white rounded-xl border border-slate-200/80 shadow-xs hover:border-slate-400 transition group flex items-start gap-4">
                <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg border border-emerald-100 group-hover:scale-105 transition-transform">
                    <i class="fa-solid fa-arrow-down-up-across-line"></i>
                </div>
                <div>
                    <h4 class="font-bold text-slate-900 text-xs group-hover:text-indigo-600 transition">Hoger & Lager</h4>
                    <p class="text-[11px] text-slate-500 mt-0.5">Raad of de volgende kaart hoger of lager is.</p>
                </div>
            </a>

            <a href="{{ route('slots.index') }}" class="p-5 bg-white rounded-xl border border-slate-200/80 shadow-xs hover:border-slate-400 transition group flex items-start gap-4">
                <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-lg border border-amber-100 group-hover:scale-105 transition-transform">
                    <i class="fa-solid fa-lemon"></i>
                </div>
                <div>
                    <h4 class="font-bold text-slate-900 text-xs group-hover:text-indigo-600 transition">Fruitautomaat</h4>
                    <p class="text-[11px] text-slate-500 mt-0.5">Draai aan de hendel van de oude gokkast.</p>
                </div>
            </a>

            <a href="{{ route('roulette.index') }}" class="p-5 bg-white rounded-xl border border-slate-200/80 shadow-xs hover:border-slate-400 transition group flex items-start gap-4">
                <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-lg border border-rose-100 group-hover:scale-105 transition-transform">
                    <i class="fa-solid fa-life-ring"></i>
                </div>
                <div>
                    <h4 class="font-bold text-slate-900 text-xs group-hover:text-indigo-600 transition">Roulette Tafel</h4>
                    <p class="text-[11px] text-slate-500 mt-0.5">Zet in op rood, zwart of jouw geluksgetal.</p>
                </div>
            </a>

            <a href="{{ route('numbergame.index') }}" class="p-5 bg-white rounded-xl border border-slate-200/80 shadow-xs hover:border-slate-400 transition group flex items-start gap-4">
                <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-lg border border-blue-100 group-hover:scale-105 transition-transform">
                    <i class="fa-solid fa-hashtag"></i>
                </div>
                <div>
                    <h4 class="font-bold text-slate-900 text-xs group-hover:text-indigo-600 transition">Nummerspel</h4>
                    <p class="text-[11px] text-slate-500 mt-0.5">Kies het juiste cijfer en win 15x je inzet.</p>
                </div>
            </a>

        </div>
    </div>
</div>
@endsection
