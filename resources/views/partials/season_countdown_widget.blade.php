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
class="relative overflow-hidden rounded-2xl p-5 sm:p-6 bg-white dark:bg-slate-900 border border-amber-300 dark:border-amber-500/40 shadow-xs text-slate-800 dark:text-slate-100">
    
    <!-- Ambient Golden Flare -->
    <div class="absolute -right-10 -top-10 w-44 h-44 bg-amber-500/10 dark:bg-amber-500/15 rounded-full blur-3xl pointer-events-none"></div>

    <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-5">
        
        <!-- Left: Info & Season Meta -->
        <div class="space-y-1.5 max-w-xl">
            <div class="flex flex-wrap items-center gap-2">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest bg-amber-50 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 border border-amber-200/80 dark:border-amber-800/70">
                    <i class="fa-solid fa-trophy text-amber-500 text-[10px]"></i>
                    SEIZOEN 1 AFTELLER
                </span>
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-purple-50 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 border border-purple-200/80 dark:border-purple-800/70">
                    Duur: Precies 3 Maanden
                </span>
            </div>
            <h2 class="text-lg sm:text-xl font-black tracking-tight text-slate-900 dark:text-white flex items-center gap-2">
                <span>Strijd om de Seizoen 1 Kampioenstitel</span>
            </h2>
            <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed">
                Iedereen start gelijk op Level 1 met €250 cash. Over 3 maanden sluit de seizoenscompetitie en worden de officiële TBS Kampioenen gekroond met exclusieve prijzen en badges!
            </p>
        </div>

        <!-- Right: 4-Segment Countdown Timer -->
        <div class="flex items-center gap-2 sm:gap-3 shrink-0 select-none">
            
            <!-- Days -->
            <div class="flex flex-col items-center justify-center w-16 sm:w-18 py-2.5 rounded-xl bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70 shadow-xs text-center">
                <span class="font-mono text-xl sm:text-2xl font-black text-amber-600 dark:text-amber-400" x-text="days">90</span>
                <span class="text-[9px] font-extrabold uppercase tracking-wider text-slate-500 dark:text-slate-400">Dagen</span>
            </div>

            <span class="text-amber-500 font-black text-lg">:</span>

            <!-- Hours -->
            <div class="flex flex-col items-center justify-center w-14 sm:w-16 py-2.5 rounded-xl bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70 shadow-xs text-center">
                <span class="font-mono text-xl sm:text-2xl font-black text-slate-900 dark:text-white" x-text="String(hours).padStart(2, '0')">00</span>
                <span class="text-[9px] font-extrabold uppercase tracking-wider text-slate-500 dark:text-slate-400">Uur</span>
            </div>

            <span class="text-amber-500 font-black text-lg">:</span>

            <!-- Minutes -->
            <div class="flex flex-col items-center justify-center w-14 sm:w-16 py-2.5 rounded-xl bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70 shadow-xs text-center">
                <span class="font-mono text-xl sm:text-2xl font-black text-slate-900 dark:text-white" x-text="String(minutes).padStart(2, '0')">00</span>
                <span class="text-[9px] font-extrabold uppercase tracking-wider text-slate-500 dark:text-slate-400">Min</span>
            </div>

            <span class="text-amber-500 font-black text-lg">:</span>

            <!-- Seconds -->
            <div class="flex flex-col items-center justify-center w-14 sm:w-16 py-2.5 rounded-xl bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70 shadow-xs text-center">
                <span class="font-mono text-xl sm:text-2xl font-black text-amber-600 dark:text-amber-400" x-text="String(seconds).padStart(2, '0')">00</span>
                <span class="text-[9px] font-extrabold uppercase tracking-wider text-slate-500 dark:text-slate-400">Sec</span>
            </div>

        </div>

    </div>

    <!-- Bottom Action Badges -->
    <div class="relative z-10 mt-4 pt-3 border-t border-slate-200/80 dark:border-slate-800 flex flex-wrap items-center justify-between gap-3 text-xs">
        <div class="flex flex-wrap items-center gap-3 text-slate-600 dark:text-slate-300 text-[11px]">
            <span class="flex items-center gap-1.5">
                <i class="fa-solid fa-check text-amber-500"></i> Gelijke Start
            </span>
            <span class="flex items-center gap-1.5">
                <i class="fa-solid fa-check text-amber-500"></i> Behoud van Sterren &amp; VIP Punten
            </span>
            <span class="flex items-center gap-1.5">
                <i class="fa-solid fa-check text-amber-500"></i> Bende Controleposten Neutraal
            </span>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('leaderboard.index') }}" class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 border border-slate-200/80 dark:border-slate-700 font-bold text-[11px] transition-all duration-150 active:scale-95 inline-flex items-center gap-1.5 cursor-pointer shadow-xs">
                <i class="fa-solid fa-ranking-star text-amber-500"></i>
                <span>Ranglijst</span>
            </a>
            <a href="{{ route('vip.index') }}" class="btn-star-gold px-3.5 py-1.5 rounded-xl text-slate-900 dark:text-white font-black text-[11px] transition-all duration-150 active:scale-95 inline-flex items-center gap-1.5 shadow-xs cursor-pointer">
                <i class="fa-solid fa-crown text-[10px]"></i>
                <span>VIP &amp; Shop</span>
            </a>
        </div>
    </div>

</div>
