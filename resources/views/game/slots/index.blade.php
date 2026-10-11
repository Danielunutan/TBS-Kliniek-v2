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
    <div class="bg-white rounded-2xl p-6 sm:p-7 text-slate-800 relative overflow-hidden shadow-xs border border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="relative z-10 space-y-1">
            <div class="inline-flex items-center gap-2 bg-purple-50 text-purple-700 text-xs font-bold px-3 py-1 rounded-full border border-purple-200">
                <i class="fa-solid fa-gamepad text-purple-600"></i>
                <span class="uppercase tracking-wider">Kliniek Gokhal &bull; Live Slots</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-slate-900">Fruitautomaat</h1>
            <p class="text-slate-500 text-xs sm:text-sm">
                Trek aan de hendel en laat de rollen in real-time draaien! 3 dezelfde symbolen leveren tot 50x jackpot op!
            </p>
        </div>
        <div class="relative z-10 flex items-center gap-3 self-start sm:self-auto">
            <div class="px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-right">
                <span class="text-[10px] font-extrabold uppercase text-slate-400 block tracking-wider">Jouw Saldo</span>
                <span class="text-sm sm:text-base font-black text-emerald-700 font-mono">
                    €<span x-text="cash.toLocaleString('nl-NL')"></span>
                </span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-purple-600 text-white flex items-center justify-center text-xl shadow-md shadow-purple-600/20 shrink-0">
                <i class="fa-solid fa-dice text-amber-300"></i>
            </div>
        </div>
    </div>

    <!-- Live Result Alert Banner -->
    <template x-if="lastResult">
        <div x-transition class="p-4 rounded-2xl border text-xs sm:text-sm font-extrabold flex items-center justify-between gap-3 shadow-xs"
             :class="{
                 'bg-amber-50 border-amber-300 text-amber-950': lastResult.type === 'jackpot',
                 'bg-emerald-50 border-emerald-300 text-emerald-950': lastResult.type === 'two_match',
                 'bg-slate-100 border-slate-300 text-slate-800': lastResult.type === 'loss',
                 'bg-rose-50 border-rose-300 text-rose-900': lastResult.type === 'error'
             }">
            <div class="flex items-center gap-3">
                <span class="text-2xl" x-text="lastResult.type === 'jackpot' ? '🎉' : (lastResult.type === 'two_match' ? '💰' : (lastResult.type === 'loss' ? '💀' : '⚠️'))"></span>
                <span x-text="lastResult.message"></span>
            </div>
            <template x-if="lastResult.net_profit && lastResult.net_profit > 0">
                <span class="px-3 py-1 rounded-full bg-emerald-600 text-white text-xs font-black shadow-xs whitespace-nowrap">
                    +€<span x-text="lastResult.net_profit.toLocaleString('nl-NL')"></span> Winst
                </span>
            </template>
        </div>
    </template>

    <!-- Slot Machine Box -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-6 sm:p-8 space-y-7">
        
        <!-- Physical Machine Reel Housing -->
        <div class="relative max-w-lg mx-auto">
            <!-- Machine Casing Outer Frame -->
            <div class="bg-gradient-to-b from-slate-900 via-purple-950 to-slate-950 p-6 sm:p-7 rounded-3xl border-4 border-purple-500/40 shadow-2xl relative overflow-hidden">
                
                <!-- Top Marquee Flashing Lights -->
                <div class="flex items-center justify-between px-2 mb-4">
                    <div class="flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-full bg-amber-400 animate-ping"></span>
                        <span class="w-2.5 h-2.5 rounded-full bg-amber-400"></span>
                        <span class="w-2.5 h-2.5 rounded-full bg-purple-400"></span>
                    </div>
                    <span class="text-[11px] font-black uppercase tracking-widest text-amber-400 drop-shadow flex items-center gap-1.5">
                        <i class="fa-solid fa-crown text-amber-300"></i> TBS CASINO SLOTS
                    </span>
                    <div class="flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-full bg-purple-400"></span>
                        <span class="w-2.5 h-2.5 rounded-full bg-amber-400"></span>
                        <span class="w-2.5 h-2.5 rounded-full bg-amber-400 animate-ping"></span>
                    </div>
                </div>

                <!-- 3 Reels Display Window -->
                <div class="bg-slate-950 p-3 sm:p-4 rounded-2xl border-2 border-purple-400/50 shadow-inner">
                    <div class="grid grid-cols-3 gap-2.5 sm:gap-4">
                        
                        <!-- Reel 1 -->
                        <div class="bg-gradient-to-b from-slate-100 via-white to-slate-200 rounded-xl h-28 sm:h-32 flex items-center justify-center text-5xl sm:text-6xl shadow-lg border-2 border-slate-300 relative overflow-hidden transition-all duration-150"
                             :class="isSpinning ? 'scale-98 ring-2 ring-purple-400' : ''">
                            <div class="absolute inset-x-0 top-0 h-4 bg-gradient-to-b from-black/25 to-transparent pointer-events-none"></div>
                            <span class="transition-transform duration-100 select-none transform" 
                                  :class="isSpinning ? 'animate-bounce' : ''"
                                  x-text="rollingReels[0]"></span>
                            <div class="absolute inset-x-0 bottom-0 h-4 bg-gradient-to-t from-black/25 to-transparent pointer-events-none"></div>
                        </div>

                        <!-- Reel 2 -->
                        <div class="bg-gradient-to-b from-slate-100 via-white to-slate-200 rounded-xl h-28 sm:h-32 flex items-center justify-center text-5xl sm:text-6xl shadow-lg border-2 border-slate-300 relative overflow-hidden transition-all duration-150"
                             :class="isSpinning ? 'scale-98 ring-2 ring-purple-400' : ''">
                            <div class="absolute inset-x-0 top-0 h-4 bg-gradient-to-b from-black/25 to-transparent pointer-events-none"></div>
                            <span class="transition-transform duration-100 select-none transform" 
                                  :class="isSpinning ? 'animate-bounce' : ''"
                                  x-text="rollingReels[1]"></span>
                            <div class="absolute inset-x-0 bottom-0 h-4 bg-gradient-to-t from-black/25 to-transparent pointer-events-none"></div>
                        </div>

                        <!-- Reel 3 -->
                        <div class="bg-gradient-to-b from-slate-100 via-white to-slate-200 rounded-xl h-28 sm:h-32 flex items-center justify-center text-5xl sm:text-6xl shadow-lg border-2 border-slate-300 relative overflow-hidden transition-all duration-150"
                             :class="isSpinning ? 'scale-98 ring-2 ring-purple-400' : ''">
                            <div class="absolute inset-x-0 top-0 h-4 bg-gradient-to-b from-black/25 to-transparent pointer-events-none"></div>
                            <span class="transition-transform duration-100 select-none transform" 
                                  :class="isSpinning ? 'animate-bounce' : ''"
                                  x-text="rollingReels[2]"></span>
                            <div class="absolute inset-x-0 bottom-0 h-4 bg-gradient-to-t from-black/25 to-transparent pointer-events-none"></div>
                        </div>

                    </div>
                </div>

                <!-- Bottom Glass Payout Ribbon -->
                <div class="mt-4 pt-3 border-t border-purple-800/60 flex items-center justify-between text-[10px] sm:text-xs font-black text-amber-300/90 tracking-wide uppercase px-1">
                    <span class="flex items-center gap-1">
                        <i class="fa-solid fa-star text-amber-400"></i> 3x 7️⃣ = 50x JACKPOT
                    </span>
                    <span class="text-purple-300/80">•</span>
                    <span>3x 💎 = 25x</span>
                    <span class="text-purple-300/80">•</span>
                    <span>2 Gelijke = 1.5x</span>
                </div>

            </div>
        </div>

        <!-- Controls: Bet Selection and Spin Button -->
        <div class="max-w-md mx-auto space-y-4">
            <div>
                <div class="flex justify-between items-center mb-1.5">
                    <label class="text-xs font-extrabold uppercase tracking-wider text-slate-600">Inzet (Euro)</label>
                    <span class="text-xs font-bold text-slate-400">
                        Beschikbaar: <strong class="text-slate-800 font-mono">€<span x-text="cash.toLocaleString('nl-NL')"></span></strong>
                    </span>
                </div>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 font-extrabold text-sm">
                        €
                    </div>
                    <input type="number" name="bet" min="50" max="50000" x-model.number="bet" :disabled="isSpinning"
                           class="w-full bg-slate-50 border border-slate-300 rounded-xl pl-8 pr-16 py-3 text-base font-black text-slate-900 focus:outline-none focus:border-purple-600 focus:bg-white transition">
                    <div class="absolute right-3.5 top-3.5 text-xs font-black text-purple-700 bg-purple-50 px-2 py-0.5 rounded-md border border-purple-200">
                        EURO
                    </div>
                </div>

                <!-- Quick Bet Presets -->
                <div class="grid grid-cols-5 gap-2 mt-2.5">
                    <button type="button" @click="setBet(100)" :disabled="isSpinning" class="py-1.5 text-xs font-black rounded-lg bg-slate-100 hover:bg-purple-100 hover:text-purple-700 text-slate-700 transition cursor-pointer">€100</button>
                    <button type="button" @click="setBet(500)" :disabled="isSpinning" class="py-1.5 text-xs font-black rounded-lg bg-slate-100 hover:bg-purple-100 hover:text-purple-700 text-slate-700 transition cursor-pointer">€500</button>
                    <button type="button" @click="setBet(1000)" :disabled="isSpinning" class="py-1.5 text-xs font-black rounded-lg bg-slate-100 hover:bg-purple-100 hover:text-purple-700 text-slate-700 transition cursor-pointer">€1.000</button>
                    <button type="button" @click="setBet(5000)" :disabled="isSpinning" class="py-1.5 text-xs font-black rounded-lg bg-slate-100 hover:bg-purple-100 hover:text-purple-700 text-slate-700 transition cursor-pointer">€5.000</button>
                    <button type="button" @click="setMaxBet()" :disabled="isSpinning" class="py-1.5 text-xs font-black rounded-lg bg-purple-600 hover:bg-purple-700 text-white transition cursor-pointer shadow-xs">MAX</button>
                </div>
            </div>

            <!-- Big Spin Button -->
            <button type="button" @click="spin()"
                    :disabled="isSpinning || cash < bet || {{ $character->isImprisoned() ? 'true' : 'false' }}}"
                    class="w-full py-4 px-6 rounded-2xl font-black text-base text-white transition shadow-lg cursor-pointer flex items-center justify-center gap-2"
                    :class="isSpinning || cash < bet ? 'bg-slate-300 cursor-not-allowed shadow-none' : 'bg-gradient-to-r from-purple-600 via-indigo-600 to-purple-700 hover:from-purple-700 hover:to-indigo-700 active:scale-[0.98] shadow-purple-600/30'">
                <i class="fa-solid fa-rotate-right text-lg" :class="isSpinning ? 'animate-spin' : ''"></i>
                <span x-text="isSpinning ? 'Rollen Draaien...' : 'Rollen Draaien (Spin!)'"></span>
            </button>
        </div>

        <!-- Full Payout Table -->
        <div class="border-t border-slate-100 pt-6">
            <h3 class="text-xs font-extrabold uppercase tracking-wider text-slate-500 mb-3 text-center">
                Uitbetalingstabel & Kansen
            </h3>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-center text-xs">
                <div class="p-3 rounded-xl bg-purple-50/70 border border-purple-100">
                    <div class="text-xl mb-1">7️⃣ 7️⃣ 7️⃣</div>
                    <span class="font-extrabold text-purple-900 block">50x Inzet</span>
                    <span class="text-[10px] text-purple-600 font-bold">+40 XP &bull; +3 Respect</span>
                </div>
                <div class="p-3 rounded-xl bg-slate-50 border border-slate-200">
                    <div class="text-xl mb-1">💎 💎 💎</div>
                    <span class="font-extrabold text-slate-800 block">25x Inzet</span>
                    <span class="text-[10px] text-slate-500 font-bold">+40 XP</span>
                </div>
                <div class="p-3 rounded-xl bg-slate-50 border border-slate-200">
                    <div class="text-xl mb-1">🔔 🔔 🔔</div>
                    <span class="font-extrabold text-slate-800 block">15x Inzet</span>
                    <span class="text-[10px] text-slate-500 font-bold">+40 XP</span>
                </div>
                <div class="p-3 rounded-xl bg-slate-50 border border-slate-200">
                    <div class="text-xl mb-1">⭐ ⭐ ⭐</div>
                    <span class="font-extrabold text-slate-800 block">10x Inzet</span>
                    <span class="text-[10px] text-slate-500 font-bold">+40 XP</span>
                </div>
                <div class="p-3 rounded-xl bg-slate-50 border border-slate-200">
                    <div class="text-xl mb-1">🍀 🍀 🍀</div>
                    <span class="font-extrabold text-slate-800 block">6x Inzet</span>
                    <span class="text-[10px] text-slate-500 font-bold">+40 XP</span>
                </div>
                <div class="p-3 rounded-xl bg-slate-50 border border-slate-200">
                    <div class="text-xl mb-1">🍒 🍒 🍒</div>
                    <span class="font-extrabold text-slate-800 block">4x Inzet</span>
                    <span class="text-[10px] text-slate-500 font-bold">+40 XP</span>
                </div>
                <div class="p-3 rounded-xl bg-slate-50 border border-slate-200">
                    <div class="text-xl mb-1">🍋 🍋 🍋</div>
                    <span class="font-extrabold text-slate-800 block">3x Inzet</span>
                    <span class="text-[10px] text-slate-500 font-bold">+40 XP</span>
                </div>
                <div class="p-3 rounded-xl bg-emerald-50/70 border border-emerald-100">
                    <div class="text-xl mb-1">🍒 🍒 ❓</div>
                    <span class="font-extrabold text-emerald-900 block">1.5x Inzet</span>
                    <span class="text-[10px] text-emerald-600 font-bold">2 Gelijke symbolen</span>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
