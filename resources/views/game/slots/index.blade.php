@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{
    bet: 100,
    cash: {{ $character->cash }},
    isSpinning: false,
    reels: ['🍒', '7️⃣', '💎'],
    rollingReels: ['🍒', '7️⃣', '💎'],
    spinIntervals: [null, null, null],
    lastResult: null,
    symbols: ['🍋', '🍒', '🍀', '⭐', '💎', '🔔', '7️⃣'],
    soundEnabled: true,
    
    setBet(amount) {
        if (this.isSpinning) return;
        this.bet = Math.min(50000, Math.max(50, amount));
    },
    setMaxBet() {
        if (this.isSpinning) return;
        this.bet = Math.min(50000, Math.max(50, this.cash));
    },
    
    async spin() {
        if (this.isSpinning || this.cash < this.bet || {{ $character->isImprisoned() ? 'true' : 'false' }}) return;
        
        this.isSpinning = true;
        this.lastResult = null;
        
        // Start reel animation ticker
        this.spinIntervals[0] = setInterval(() => {
            this.rollingReels[0] = this.symbols[Math.floor(Math.random() * this.symbols.length)];
        }, 80);
        this.spinIntervals[1] = setInterval(() => {
            this.rollingReels[1] = this.symbols[Math.floor(Math.random() * this.symbols.length)];
        }, 80);
        this.spinIntervals[2] = setInterval(() => {
            this.rollingReels[2] = this.symbols[Math.floor(Math.random() * this.symbols.length)];
        }, 80);

        try {
            const res = await fetch('{{ route('slots.spin') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ bet: this.bet })
            });
            const data = await res.json();
            
            if (!data.success) {
                this.stopAllSpins();
                this.lastResult = {
                    type: 'error',
                    message: data.message || 'Er is een fout opgetreden.'
                };
                this.isSpinning = false;
                return;
            }

            // Staggered stop sequence: Reel 1 (600ms), Reel 2 (1000ms), Reel 3 (1400ms)
            setTimeout(() => {
                clearInterval(this.spinIntervals[0]);
                this.rollingReels[0] = data.reels[0];
            }, 600);

            setTimeout(() => {
                clearInterval(this.spinIntervals[1]);
                this.rollingReels[1] = data.reels[1];
            }, 1000);

            setTimeout(() => {
                clearInterval(this.spinIntervals[2]);
                this.rollingReels[2] = data.reels[2];
                this.isSpinning = false;
                this.lastResult = data;
                if (data.cash !== undefined) {
                    this.cash = data.cash;
                }
            }, 1400);

        } catch (err) {
            this.stopAllSpins();
            this.lastResult = {
                type: 'error',
                message: 'Verbindingsfout met de speelautomaat.'
            };
            this.isSpinning = false;
        }
    },
    
    stopAllSpins() {
        this.spinIntervals.forEach(i => i && clearInterval(i));
    }
}">
    <!-- Header Banner in TBS Kliniek Styling -->
    <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200/90 dark:border-slate-800 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-900 dark:text-white uppercase tracking-wide flex items-center gap-2">
                <i class="fa-solid fa-dice text-purple-600 dark:text-purple-400"></i>
                <span>Fruitautomaat</span>
            </h1>
            <p class="text-xs text-slate-600 dark:text-slate-400 mt-1">
                Beproef je geluk in de recreatieruimte van de kliniek. Draai 3 dezelfde symbolen voor jackpots tot 50x je inzet!
            </p>
        </div>

        <div class="flex items-center gap-3">
            <div class="px-4 py-2 rounded-xl bg-amber-50 dark:bg-amber-950/60 border border-amber-200/80 dark:border-amber-800/70 text-right">
                <span class="text-[10px] font-bold uppercase text-amber-800 dark:text-amber-300 block tracking-wider">Contant op Zak</span>
                <span class="text-lg font-bold text-slate-900 dark:text-white font-mono">
                    €<span x-text="cash.toLocaleString('nl-NL')"></span>
                </span>
            </div>
        </div>
    </div>

    <!-- Live Result Alert Banner -->
    <template x-if="lastResult">
        <div x-transition class="p-4 rounded-xl border text-sm font-bold flex items-center justify-between gap-3 shadow-xs"
             :class="{
                 'bg-amber-50 dark:bg-amber-950/60 border-amber-300 dark:border-amber-800 text-amber-900 dark:text-amber-200': lastResult.type === 'jackpot',
                 'bg-emerald-50 dark:bg-emerald-950/60 border-emerald-300 dark:border-emerald-800 text-emerald-900 dark:text-emerald-200': lastResult.type === 'two_match',
                 'bg-slate-50 dark:bg-slate-800/90 border-slate-300 dark:border-slate-700 text-slate-800 dark:text-slate-200': lastResult.type === 'loss',
                 'bg-rose-50 dark:bg-rose-950/60 border-rose-300 dark:border-rose-800 text-rose-900 dark:text-rose-200': lastResult.type === 'error'
             }">
            <div class="flex items-center gap-3">
                <span class="text-2xl" x-text="lastResult.type === 'jackpot' ? '🎉' : (lastResult.type === 'two_match' ? '💰' : (lastResult.type === 'loss' ? '💀' : '⚠️'))"></span>
                <span x-text="lastResult.message"></span>
            </div>
            <template x-if="lastResult.net_profit && lastResult.net_profit > 0">
                <span class="px-3 py-1 rounded-full bg-emerald-600 text-white text-xs font-bold shadow-xs whitespace-nowrap">
                    +€<span x-text="lastResult.net_profit.toLocaleString('nl-NL')"></span> Winst
                </span>
            </template>
        </div>
    </template>

    <!-- Slot Machine Box in Clean TBS Kliniek Style -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/90 dark:border-slate-800 shadow-xs p-6 space-y-6">
        
        <!-- Physical Machine Reel Housing (TBS Light Theme) -->
        <div class="relative max-w-lg mx-auto">
            <div class="bg-slate-50/90 dark:bg-slate-800/60 p-5 rounded-xl border border-slate-200/80 dark:border-slate-700/70 shadow-inner relative overflow-hidden">
                
                <!-- Top Header Bar -->
                <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-200/80 dark:border-slate-700/70 text-xs font-bold text-slate-700 dark:text-slate-200">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-gamepad text-purple-600 dark:text-purple-400"></i>
                        <span class="uppercase tracking-wide">Kliniek Gokkast</span>
                    </div>
                    <span class="text-[11px] font-bold px-2.5 py-0.5 rounded-full bg-purple-50 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 border border-purple-200/80 dark:border-purple-800/70">
                        Max 50x Jackpot
                    </span>
                </div>

                <!-- 3 Reels Display Window -->
                <div class="bg-slate-200/50 dark:bg-slate-900/60 p-3 sm:p-4 rounded-xl border border-slate-200/80 dark:border-slate-700/70">
                    <div class="grid grid-cols-3 gap-2.5 sm:gap-4">
                        
                        <!-- Reel 1 -->
                        <div class="bg-white dark:bg-slate-800 rounded-xl h-28 sm:h-36 flex items-center justify-center text-5xl sm:text-6xl shadow-xs border border-slate-200/80 dark:border-slate-700 relative overflow-hidden transition-all duration-150"
                             :class="isSpinning ? 'scale-[0.98] ring-2 ring-purple-400' : ''">
                            <div class="absolute inset-x-0 top-0 h-6 bg-gradient-to-b from-slate-200/60 dark:from-slate-950/80 to-transparent pointer-events-none"></div>
                            <span class="transition-transform duration-100 select-none transform" 
                                  :class="isSpinning ? 'animate-bounce' : ''"
                                  x-text="rollingReels[0]"></span>
                            <div class="absolute inset-x-0 bottom-0 h-6 bg-gradient-to-t from-slate-200/60 dark:from-slate-950/80 to-transparent pointer-events-none"></div>
                        </div>

                        <!-- Reel 2 -->
                        <div class="bg-white dark:bg-slate-800 rounded-xl h-28 sm:h-36 flex items-center justify-center text-5xl sm:text-6xl shadow-xs border border-slate-200/80 dark:border-slate-700 relative overflow-hidden transition-all duration-150"
                             :class="isSpinning ? 'scale-[0.98] ring-2 ring-purple-400' : ''">
                            <div class="absolute inset-x-0 top-0 h-6 bg-gradient-to-b from-slate-200/60 dark:from-slate-950/80 to-transparent pointer-events-none"></div>
                            <span class="transition-transform duration-100 select-none transform" 
                                  :class="isSpinning ? 'animate-bounce' : ''"
                                  x-text="rollingReels[1]"></span>
                            <div class="absolute inset-x-0 bottom-0 h-6 bg-gradient-to-t from-slate-200/60 dark:from-slate-950/80 to-transparent pointer-events-none"></div>
                        </div>

                        <!-- Reel 3 -->
                        <div class="bg-white dark:bg-slate-800 rounded-xl h-28 sm:h-36 flex items-center justify-center text-5xl sm:text-6xl shadow-xs border border-slate-200/80 dark:border-slate-700 relative overflow-hidden transition-all duration-150"
                             :class="isSpinning ? 'scale-[0.98] ring-2 ring-purple-400' : ''">
                            <div class="absolute inset-x-0 top-0 h-6 bg-gradient-to-b from-slate-200/60 dark:from-slate-950/80 to-transparent pointer-events-none"></div>
                            <span class="transition-transform duration-100 select-none transform" 
                                  :class="isSpinning ? 'animate-bounce' : ''"
                                  x-text="rollingReels[2]"></span>
                            <div class="absolute inset-x-0 bottom-0 h-6 bg-gradient-to-t from-slate-200/60 dark:from-slate-950/80 to-transparent pointer-events-none"></div>
                        </div>

                    </div>
                </div>

                <!-- Bottom Payout Summary Ribbon -->
                <div class="mt-3.5 pt-3 border-t border-slate-200/80 dark:border-slate-700/70 flex items-center justify-between text-[11px] font-bold text-slate-600 dark:text-slate-400 px-1">
                    <span class="flex items-center gap-1 text-slate-900 dark:text-slate-200">
                        <i class="fa-solid fa-star text-amber-500"></i> 3x 7️⃣ = 50x Jackpot
                    </span>
                    <span class="text-slate-300 dark:text-slate-600">&bull;</span>
                    <span class="text-purple-700 dark:text-purple-300">3x 💎 = 25x</span>
                    <span class="text-slate-300 dark:text-slate-600">&bull;</span>
                    <span class="text-emerald-700 dark:text-emerald-300">2 Gelijke = 1.5x</span>
                </div>

            </div>
        </div>

        <!-- Controls: Bet Selection and Spin Button -->
        <div class="max-w-md mx-auto space-y-4">
            <div>
                <div class="flex justify-between items-center mb-1.5">
                    <label class="text-xs font-bold uppercase tracking-wide text-slate-700 dark:text-slate-300">Inzet (Euro)</label>
                    <span class="text-xs text-slate-500 dark:text-slate-400">
                        Min. <strong>€50</strong> &bull; Max. <strong>€50.000</strong>
                    </span>
                </div>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 dark:text-slate-500 font-bold text-sm">
                        €
                    </div>
                    <input type="number" name="bet" min="50" max="50000" x-model.number="bet" :disabled="isSpinning"
                           class="w-full bg-slate-50 dark:bg-slate-800/90 border border-slate-200 dark:border-slate-700 rounded-xl pl-8 pr-16 py-2.5 text-base font-bold text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:border-purple-500 dark:focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition shadow-xs">
                    <div class="absolute right-3.5 top-2.5 text-xs font-bold text-purple-700 dark:text-purple-300 bg-purple-50 dark:bg-purple-950/60 px-2 py-0.5 rounded-lg border border-purple-200/80 dark:border-purple-800/70">
                        EURO
                    </div>
                </div>

                <!-- Quick Bet Presets -->
                <div class="grid grid-cols-5 gap-2 mt-2.5">
                    <button type="button" @click="setBet(100)" :disabled="isSpinning" class="py-2 text-xs font-bold rounded-xl bg-slate-100 hover:bg-purple-50 dark:bg-slate-800 dark:hover:bg-purple-950/50 text-slate-700 dark:text-slate-200 hover:text-purple-700 dark:hover:text-purple-300 border border-slate-200/80 dark:border-slate-700 transition-all duration-150 active:scale-95 cursor-pointer">€100</button>
                    <button type="button" @click="setBet(500)" :disabled="isSpinning" class="py-2 text-xs font-bold rounded-xl bg-slate-100 hover:bg-purple-50 dark:bg-slate-800 dark:hover:bg-purple-950/50 text-slate-700 dark:text-slate-200 hover:text-purple-700 dark:hover:text-purple-300 border border-slate-200/80 dark:border-slate-700 transition-all duration-150 active:scale-95 cursor-pointer">€500</button>
                    <button type="button" @click="setBet(1000)" :disabled="isSpinning" class="py-2 text-xs font-bold rounded-xl bg-slate-100 hover:bg-purple-50 dark:bg-slate-800 dark:hover:bg-purple-950/50 text-slate-700 dark:text-slate-200 hover:text-purple-700 dark:hover:text-purple-300 border border-slate-200/80 dark:border-slate-700 transition-all duration-150 active:scale-95 cursor-pointer">€1.000</button>
                    <button type="button" @click="setBet(5000)" :disabled="isSpinning" class="py-2 text-xs font-bold rounded-xl bg-slate-100 hover:bg-purple-50 dark:bg-slate-800 dark:hover:bg-purple-950/50 text-slate-700 dark:text-slate-200 hover:text-purple-700 dark:hover:text-purple-300 border border-slate-200/80 dark:border-slate-700 transition-all duration-150 active:scale-95 cursor-pointer">€5.000</button>
                    <button type="button" @click="setMaxBet()" :disabled="isSpinning" class="py-2 text-xs font-bold rounded-xl bg-purple-50 hover:bg-purple-100 dark:bg-purple-950/60 dark:hover:bg-purple-900/60 text-purple-700 dark:text-purple-300 border border-purple-200/80 dark:border-purple-800/70 transition-all duration-150 active:scale-95 cursor-pointer shadow-xs">MAX</button>
                </div>
            </div>

            <!-- Big Spin Button in TBS Purple Brand Theme -->
            <button type="button" @click="spin()"
                    :disabled="isSpinning || cash < bet || {{ $character->isImprisoned() ? 'true' : 'false' }}"
                    class="w-full py-3.5 px-6 rounded-xl font-black text-base transition-all duration-150 shadow-xs cursor-pointer flex items-center justify-center gap-2 active:scale-95"
                    :class="isSpinning || cash < bet ? 'bg-slate-100 dark:bg-slate-800/80 text-slate-400 dark:text-slate-500 border border-slate-200/80 dark:border-slate-700/60 cursor-not-allowed shadow-none active:scale-100' : 'bg-purple-600 hover:bg-purple-700 dark:bg-purple-600 dark:hover:bg-purple-500 text-white shadow-xs'">
                <i class="fa-solid fa-rotate-right text-base" :class="isSpinning ? 'animate-spin' : ''"></i>
                <span x-text="isSpinning ? 'Rollen Draaien...' : 'Draai Rollen (Spin!)'"></span>
            </button>
        </div>

        <!-- Full Payout Table -->
        <div class="border-t border-slate-200/80 dark:border-slate-800 pt-6">
            <h3 class="text-xs font-bold uppercase tracking-wide text-slate-700 dark:text-slate-300 mb-3 text-center">
                Uitbetalingstabel & Kansen
            </h3>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-center text-xs">
                <div class="p-3 rounded-xl bg-purple-50/70 dark:bg-purple-950/40 border border-purple-200/80 dark:border-purple-800/70 shadow-xs">
                    <div class="text-xl mb-1">7️⃣ 7️⃣ 7️⃣</div>
                    <span class="font-bold text-purple-900 dark:text-purple-200 block">50x Inzet</span>
                    <span class="text-[10px] text-purple-600 dark:text-purple-400 font-bold">+40 XP &bull; +3 Respect</span>
                </div>
                <div class="p-3 rounded-xl bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70 shadow-xs">
                    <div class="text-xl mb-1">💎 💎 💎</div>
                    <span class="font-bold text-slate-800 dark:text-slate-200 block">25x Inzet</span>
                    <span class="text-[10px] text-slate-500 dark:text-slate-400 font-bold">+40 XP</span>
                </div>
                <div class="p-3 rounded-xl bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70 shadow-xs">
                    <div class="text-xl mb-1">🔔 🔔 🔔</div>
                    <span class="font-bold text-slate-800 dark:text-slate-200 block">15x Inzet</span>
                    <span class="text-[10px] text-slate-500 dark:text-slate-400 font-bold">+40 XP</span>
                </div>
                <div class="p-3 rounded-xl bg-amber-50/60 dark:bg-amber-950/40 border border-amber-200/80 dark:border-amber-800/70 shadow-xs">
                    <div class="text-xl mb-1">⭐ ⭐ ⭐</div>
                    <span class="font-bold text-amber-900 dark:text-amber-200 block">10x Inzet</span>
                    <span class="text-[10px] text-amber-700 dark:text-amber-400 font-bold">+40 XP</span>
                </div>
                <div class="p-3 rounded-xl bg-emerald-50/60 dark:bg-emerald-950/40 border border-emerald-200/80 dark:border-emerald-800/70 shadow-xs">
                    <div class="text-xl mb-1">🍀 🍀 🍀</div>
                    <span class="font-bold text-emerald-900 dark:text-emerald-200 block">6x Inzet</span>
                    <span class="text-[10px] text-emerald-700 dark:text-emerald-400 font-bold">+40 XP</span>
                </div>
                <div class="p-3 rounded-xl bg-rose-50/60 dark:bg-rose-950/40 border border-rose-200/80 dark:border-rose-800/70 shadow-xs">
                    <div class="text-xl mb-1">🍒 🍒 🍒</div>
                    <span class="font-bold text-rose-900 dark:text-rose-200 block">4x Inzet</span>
                    <span class="text-[10px] text-rose-700 dark:text-rose-400 font-bold">+40 XP</span>
                </div>
                <div class="p-3 rounded-xl bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70 shadow-xs">
                    <div class="text-xl mb-1">🍋 🍋 🍋</div>
                    <span class="font-bold text-slate-800 dark:text-slate-200 block">3x Inzet</span>
                    <span class="text-[10px] text-slate-500 dark:text-slate-400 font-bold">+40 XP</span>
                </div>
                <div class="p-3 rounded-xl bg-emerald-50/70 dark:bg-emerald-950/40 border border-emerald-200/80 dark:border-emerald-800/70 shadow-xs">
                    <div class="text-xl mb-1">🍒 🍒 ❓</div>
                    <span class="font-bold text-emerald-900 dark:text-emerald-200 block">1.5x Inzet</span>
                    <span class="text-[10px] text-emerald-700 dark:text-emerald-400 font-bold">2 Gelijke symbolen</span>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
