<div x-data="{
    // Exact 3 months from launch (27 Jan 2027 22:00:00 CET)
    target: new Date('2027-01-27T22:00:00+01:00').getTime(),
    days: 0,
    hours: 0,
    minutes: 0,
    seconds: 0,
    update() {
        const diff = Math.max(0, this.target - Date.now());
        this.days = Math.floor(diff / (1000 * 60 * 60 * 24));
        this.hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        this.minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
        this.seconds = Math.floor((diff % (1000 * 60)) / 1000);
    },
    init() {
        this.update();
        setInterval(() => this.update(), 1000);
    }
}" 
x-init="init()"
class="relative overflow-hidden rounded-2xl p-5 sm:p-6 text-white shadow-lg border border-amber-500/40"
style="background: radial-gradient(circle at 15% 50%, #200d43 0%, #0d0621 100%);">
    
    <!-- Ambient Golden Flare -->
    <div class="absolute -right-10 -top-10 w-44 h-44 bg-amber-500/15 rounded-full blur-3xl pointer-events-none"></div>

    <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-5">
        
        <!-- Left: Info & Season Meta -->
        <div class="space-y-1.5 max-w-xl">
            <div class="flex flex-wrap items-center gap-2">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest bg-amber-500/20 text-amber-300 border border-amber-400/50 shadow-inner">
                    <i class="fa-solid fa-trophy text-amber-400 text-[10px]"></i>
                    SEIZOEN 1 AFTELLER
                </span>
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-purple-900/60 text-purple-200 border border-purple-700/50">
                    Duur: Precies 3 Maanden
                </span>
            </div>
            <h2 class="text-lg sm:text-xl font-black tracking-tight text-white flex items-center gap-2">
                <span>Strijd om de Seizoen 1 Kampioenstitel</span>
            </h2>
            <p class="text-xs text-purple-200/80 leading-relaxed">
                Iedereen start gelijk op Level 1 met €250 cash. Over 3 maanden sluit de seizoenscompetitie en worden de officiële TBS Kampioenen gekroond met exclusieve prijzen en badges!
            </p>
        </div>

        <!-- Right: 4-Segment Countdown Timer -->
        <div class="flex items-center gap-2 sm:gap-3 shrink-0 select-none">
            
            <!-- Days -->
            <div class="flex flex-col items-center justify-center w-16 sm:w-18 py-2.5 rounded-xl bg-purple-950/70 border border-purple-700/40 shadow-inner text-center">
                <span class="font-mono text-xl sm:text-2xl font-black text-amber-300" x-text="days">90</span>
                <span class="text-[9px] font-extrabold uppercase tracking-wider text-purple-300">Dagen</span>
            </div>

            <span class="text-amber-400 font-black text-lg">:</span>

            <!-- Hours -->
            <div class="flex flex-col items-center justify-center w-14 sm:w-16 py-2.5 rounded-xl bg-purple-950/70 border border-purple-700/40 shadow-inner text-center">
                <span class="font-mono text-xl sm:text-2xl font-black text-white" x-text="String(hours).padStart(2, '0')">00</span>
                <span class="text-[9px] font-extrabold uppercase tracking-wider text-purple-300">Uur</span>
            </div>

            <span class="text-amber-400 font-black text-lg">:</span>

            <!-- Minutes -->
            <div class="flex flex-col items-center justify-center w-14 sm:w-16 py-2.5 rounded-xl bg-purple-950/70 border border-purple-700/40 shadow-inner text-center">
                <span class="font-mono text-xl sm:text-2xl font-black text-white" x-text="String(minutes).padStart(2, '0')">00</span>
                <span class="text-[9px] font-extrabold uppercase tracking-wider text-purple-300">Min</span>
            </div>

            <span class="text-amber-400 font-black text-lg">:</span>

            <!-- Seconds -->
            <div class="flex flex-col items-center justify-center w-14 sm:w-16 py-2.5 rounded-xl bg-purple-950/70 border border-purple-700/40 shadow-inner text-center">
                <span class="font-mono text-xl sm:text-2xl font-black text-amber-400" x-text="String(seconds).padStart(2, '0')">00</span>
                <span class="text-[9px] font-extrabold uppercase tracking-wider text-purple-300">Sec</span>
            </div>

        </div>

    </div>

    <!-- Bottom Action Badges -->
    <div class="relative z-10 mt-4 pt-3 border-t border-purple-800/40 flex flex-wrap items-center justify-between gap-3 text-xs">
        <div class="flex flex-wrap items-center gap-3 text-purple-300 text-[11px]">
            <span class="flex items-center gap-1.5">
                <i class="fa-solid fa-check text-amber-400"></i> Gelijke Start
            </span>
            <span class="flex items-center gap-1.5">
                <i class="fa-solid fa-check text-amber-400"></i> Behoud van Sterren &amp; VIP Punten
            </span>
            <span class="flex items-center gap-1.5">
                <i class="fa-solid fa-check text-amber-400"></i> Bende Controleposten Neutraal
            </span>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('leaderboard.index') }}" class="px-3 py-1.5 rounded-lg bg-white/10 hover:bg-white/20 text-white font-extrabold text-[11px] transition inline-flex items-center gap-1.5">
                <i class="fa-solid fa-ranking-star text-amber-400"></i>
                <span>Ranglijst</span>
            </a>
            <a href="{{ route('vip.index') }}" class="px-3 py-1.5 rounded-lg bg-amber-500 hover:bg-amber-400 text-slate-950 font-black text-[11px] transition inline-flex items-center gap-1.5 shadow-sm">
                <i class="fa-solid fa-crown text-[10px]"></i>
                <span>VIP &amp; Shop</span>
            </a>
        </div>
    </div>

</div>
