<div x-data="{
    loading: true,
    lastUpdated: '',
    data: {
        is_active: false,
        has_ended: false,
        remaining_seconds: 0,
        prize_cash: 5000000,
        prize_cash_formatted: '€5.000.000',
        prize_stars: 1000,
        leaderboard: [],
        my_rank: null,
        total_participants: 0,
        total_drugs_produced_formatted: '0 stuks'
    },
    timerDisplay: 'Laden...',
    timerInterval: null,
    pollInterval: null,
    async fetchLeaderboard() {
        try {
            const res = await fetch('{{ route('api.event.drugs_production_leaderboard') }}', {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            });
            if (res.ok) {
                this.data = await res.json();
                const now = new Date();
                this.lastUpdated = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' });
            }
        } catch (e) {
            console.error('Fout bij ophalen ranglijst drugsproductie:', e);
        } finally {
            this.loading = false;
        }
    },
    updateCountdown() {
        if (this.data.remaining_seconds <= 0) {
            this.timerDisplay = '00:00:00';
            return;
        }
        this.data.remaining_seconds--;
        const days = Math.floor(this.data.remaining_seconds / 86400);
        const hours = Math.floor((this.data.remaining_seconds % 86400) / 3600);
        const minutes = Math.floor((this.data.remaining_seconds % 3600) / 60);
        const seconds = this.data.remaining_seconds % 60;

        if (days > 0) {
            this.timerDisplay = days + 'd ' +
                                String(hours).padStart(2, '0') + ':' +
                                String(minutes).padStart(2, '0') + ':' +
                                String(seconds).padStart(2, '0');
        } else {
            this.timerDisplay = String(hours).padStart(2, '0') + ':' +
                                String(minutes).padStart(2, '0') + ':' +
                                String(seconds).padStart(2, '0');
        }
    },
    init() {
        this.fetchLeaderboard();
        this.timerInterval = setInterval(() => this.updateCountdown(), 1000);
        this.pollInterval = setInterval(() => this.fetchLeaderboard(), 15000);
    }
}" class="my-6 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 p-5 sm:p-7 shadow-xs text-slate-800 dark:text-slate-100 space-y-6">

    <!-- Top Event Header & Live Countdown Badge -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-200/80 dark:border-slate-800 pb-5">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-black uppercase tracking-wider mb-2"
                 :class="data.is_active ? 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/60' : (data.has_ended ? 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700' : 'bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800/60')">
                <span class="w-2 h-2 rounded-full" :class="data.is_active ? 'bg-emerald-500 animate-pulse' : 'bg-amber-500'"></span>
                <span x-text="data.is_active ? '⚡ LIVE EVENT BEZIG (Tot Zondag 23:59)' : (data.has_ended ? '🏁 EVENT AFGELOPEN' : '⏳ EVENT START BINNENKORT')"></span>
            </div>
            <h2 class="text-xl sm:text-2xl font-black tracking-tight text-slate-900 dark:text-white flex items-center gap-2">
                <span>🏆 Live Ranglijst - De Grote Drugsproductie</span>
            </h2>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                Alle geproduceerde drugs (wiet, ritalin strips, sigaretten en medicatie) tellen mee. De #1 op zondag om 23:59 wint automatisch <strong>€5.000.000 contant geld</strong> en <strong>1.000 Sterren ⭐</strong>!
            </p>
        </div>

        <!-- Countdown Clock -->
        <div class="flex flex-col items-start sm:items-end gap-1.5 shrink-0">
            <span class="text-[10px] font-black uppercase tracking-wider text-slate-500 dark:text-purple-300">Resterende tijd tot zondag 23:59</span>
            <div class="px-4 py-2 rounded-xl bg-slate-900 dark:bg-black/70 border border-slate-800 dark:border-emerald-600/50 text-emerald-400 font-mono text-lg sm:text-xl font-black tracking-widest flex items-center gap-2 shadow-inner">
                <i class="fa-solid fa-stopwatch text-emerald-400 text-sm"></i>
                <span x-text="timerDisplay"></span>
            </div>
            <div class="flex items-center gap-2 text-[10px] text-slate-400 dark:text-purple-300/70">
                <span>Auto-update elke 15s</span>
                <span x-show="lastUpdated" x-text="'(' + lastUpdated + ')'"></span>
                <button type="button" @click="fetchLeaderboard()" class="text-purple-600 dark:text-purple-300 hover:underline font-bold cursor-pointer inline-flex items-center gap-1" title="Nu handmatig vernieuwen">
                    <i class="fa-solid fa-arrows-rotate" :class="{ 'fa-spin': loading }"></i>
                    <span>Ververs</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Stats Snapshot (3 clean metrics) -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-3.5">
        <!-- 1. Hoofdprijs -->
        <div class="p-4 rounded-2xl bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70 shadow-xs flex flex-col justify-between gap-2">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-black uppercase tracking-wider text-slate-400 dark:text-slate-400 flex items-center gap-1.5">
                    <i class="fa-solid fa-trophy text-amber-500 text-xs"></i>
                    <span>Hoofdprijs</span>
                </span>
                <span class="text-[10px] font-extrabold uppercase px-2 py-0.5 rounded-md bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 border border-amber-200/80 dark:border-amber-800/60">
                    Plek #1
                </span>
            </div>
            <div class="flex flex-wrap items-baseline gap-2 pt-0.5">
                <span class="text-lg sm:text-xl font-black text-emerald-600 dark:text-emerald-400 font-mono tracking-tight">€5.000.000</span>
                <span class="text-xs font-bold text-slate-400 dark:text-slate-500">+</span>
                <span class="inline-flex items-center gap-1 text-xs sm:text-sm font-black text-amber-500 dark:text-amber-400 font-mono">
                    <i class="fa-solid fa-star text-amber-500 text-xs"></i>
                    <span>1.000 Sterren</span>
                </span>
            </div>
        </div>

        <!-- 2. Actieve Producenten -->
        <div class="p-4 rounded-2xl bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70 shadow-xs flex flex-col justify-between gap-2">
            <span class="text-[10px] font-black uppercase tracking-wider text-slate-400 dark:text-slate-400 flex items-center gap-1.5">
                <i class="fa-solid fa-users text-purple-500 text-xs"></i>
                <span>Actieve Producenten</span>
            </span>
            <div class="text-lg sm:text-xl font-black text-slate-900 dark:text-white font-mono pt-0.5" x-text="data.total_participants + (data.total_participants === 1 ? ' Patiënt' : ' Patiënten')">
            </div>
        </div>

        <!-- 3. Totaal Geproduceerd -->
        <div class="p-4 rounded-2xl bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70 shadow-xs flex flex-col justify-between gap-2">
            <span class="text-[10px] font-black uppercase tracking-wider text-slate-400 dark:text-slate-400 flex items-center gap-1.5">
                <i class="fa-solid fa-cannabis text-emerald-500 text-xs"></i>
                <span>Totaal Geproduceerd</span>
            </span>
            <div class="text-lg sm:text-xl font-black text-emerald-600 dark:text-emerald-400 font-mono pt-0.5" x-text="data.total_drugs_produced_formatted">
            </div>
        </div>
    </div>

    <!-- My Position Banner (Personalized for logged-in player) -->
    <template x-if="data.my_rank">
        <div class="p-4 rounded-xl bg-purple-50/90 dark:bg-purple-950/50 border border-purple-200/80 dark:border-purple-800/70 flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-2xs">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl overflow-hidden border border-purple-300 dark:border-purple-700 shrink-0">
                    <img :src="data.my_rank.avatar_url" class="w-full h-full object-cover">
                </div>
                <div>
                    <span class="text-[10px] font-black uppercase tracking-wider text-purple-700 dark:text-purple-300">Jouw Huidige Status</span>
                    <div class="text-sm font-black text-slate-900 dark:text-white flex items-center gap-2">
                        <span x-text="data.my_rank.name"></span>
                        <span class="text-xs font-bold text-slate-500 dark:text-slate-400" x-text="'(Lvl ' + data.my_rank.level + ')'"></span>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-4">
                <div class="text-right">
                    <span class="text-[10px] font-bold text-slate-500 dark:text-purple-200 block">Jouw Productie</span>
                    <span class="text-sm sm:text-base font-black text-emerald-600 dark:text-emerald-400 font-mono" x-text="data.my_rank.total_produced_formatted"></span>
                </div>
                <div class="px-3.5 py-1.5 rounded-xl bg-purple-600 text-white text-center shrink-0 shadow-xs">
                    <span class="text-[9px] font-bold text-purple-200 block uppercase">Positie</span>
                    <span class="text-base font-black text-white" x-text="'#' + data.my_rank.rank"></span>
                </div>
            </div>
        </div>
    </template>

    <!-- Leaderboard Table -->
    <div class="space-y-2">
        <div class="flex items-center justify-between">
            <h3 class="text-xs font-black uppercase tracking-wider text-slate-600 dark:text-purple-300 flex items-center gap-2">
                <i class="fa-solid fa-cannabis text-emerald-600 dark:text-emerald-400"></i>
                <span>Top Producenten (Live Stand)</span>
            </h3>
            <a href="{{ route('smuggle.index') }}" class="text-xs font-black text-purple-600 dark:text-purple-400 hover:underline flex items-center gap-1">
                <span>Direct Produceren</span>
                <i class="fa-solid fa-arrow-right text-[10px]"></i>
            </a>
        </div>

        <!-- Loading State -->
        <div x-show="loading && data.leaderboard.length === 0" class="py-8 text-center text-xs text-slate-500 dark:text-purple-300 animate-pulse">
            <i class="fa-solid fa-spinner fa-spin text-lg mb-2 block text-purple-600"></i>
            <span>Ranglijst synchroniseren met de ondergrondse administratie...</span>
        </div>

        <!-- Empty State -->
        <div x-show="!loading && data.leaderboard.length === 0" class="py-8 px-4 text-center rounded-xl bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70 text-xs text-slate-600 dark:text-slate-300 space-y-2">
            <i class="fa-solid fa-flask-vial text-2xl text-emerald-500 mb-1"></i>
            <p class="font-extrabold text-sm text-slate-900 dark:text-white">Nog geen drugsproductie geregistreerd!</p>
            <p class="text-slate-500 dark:text-slate-400 max-w-md mx-auto">Ga direct naar de Productiekelder of ritsel pillen in de Ziekenboeg om als allereerste bovenaan te staan!</p>
            <div class="pt-2">
                <a href="{{ route('smuggle.index') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-black text-xs shadow-xs transition">
                    <i class="fa-solid fa-industry"></i>
                    <span>Open Productiekelder</span>
                </a>
            </div>
        </div>

        <!-- Populated List -->
        <div x-show="data.leaderboard.length > 0" class="space-y-1.5">
            <template x-for="player in data.leaderboard" :key="player.character_id">
                <div class="p-3 sm:p-3.5 rounded-xl border transition-all flex items-center justify-between gap-3"
                     :class="player.rank === 1 ? 'bg-amber-50/90 dark:bg-amber-950/40 border-amber-300 dark:border-amber-700/60 shadow-xs ring-1 ring-amber-300/60' : (player.rank === 2 ? 'bg-slate-50/90 dark:bg-slate-800/60 border-slate-200/80 dark:border-slate-700/70' : (player.rank === 3 ? 'bg-amber-50/50 dark:bg-amber-950/20 border-amber-200 dark:border-amber-800/40' : 'bg-slate-50/70 dark:bg-slate-800/40 border-slate-200/80 dark:border-slate-700/60 hover:bg-slate-100/80 dark:hover:bg-slate-800/80'))">
                    
                    <div class="flex items-center gap-3 min-w-0">
                        <!-- Rank Badge -->
                        <div class="w-8 h-8 rounded-xl flex items-center justify-center font-black text-xs shrink-0"
                             :class="player.rank === 1 ? 'bg-amber-400 text-slate-950 shadow-xs' : (player.rank === 2 ? 'bg-slate-200 dark:bg-slate-700 text-slate-800 dark:text-slate-200' : (player.rank === 3 ? 'bg-amber-700 text-white' : 'bg-slate-100 dark:bg-purple-950 text-slate-700 dark:text-purple-300 border border-slate-200 dark:border-purple-800/50'))">
                            <template x-if="player.rank === 1">
                                <i class="fa-solid fa-crown text-xs"></i>
                            </template>
                            <template x-if="player.rank !== 1">
                                <span x-text="'#' + player.rank"></span>
                            </template>
                        </div>

                        <!-- Avatar -->
                        <img :src="player.avatar_url" class="w-9 h-9 rounded-xl object-cover border border-slate-200 dark:border-purple-400/30 shrink-0">

                        <!-- Name & Level -->
                        <div class="min-w-0 truncate">
                            <div class="text-xs sm:text-sm font-black text-slate-900 dark:text-white truncate flex items-center gap-1.5">
                                <span x-text="player.name"></span>
                                <span x-show="player.rank === 1" class="text-[10px] font-black uppercase px-2 py-0.5 rounded-full bg-amber-400 text-slate-950 shadow-xs">Koploper</span>
                            </div>
                            <span class="text-[10px] text-slate-500 dark:text-slate-400 font-semibold" x-text="'Level ' + player.level"></span>
                        </div>
                    </div>

                    <!-- Drugs Produced -->
                    <div class="text-right shrink-0">
                        <span class="text-[10px] font-bold text-slate-400 dark:text-purple-300 block">Geproduceerd</span>
                        <span class="text-xs sm:text-sm font-black text-emerald-600 dark:text-emerald-400 font-mono" x-text="player.total_produced_formatted"></span>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <!-- Quick Action / CTA -->
    <div class="p-4 rounded-xl bg-emerald-50/70 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/60 flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-2xs">
        <div class="flex items-center gap-3">
            <span class="w-10 h-10 rounded-xl bg-emerald-600 text-white flex items-center justify-center text-lg shrink-0 shadow-xs">
                <i class="fa-solid fa-industry"></i>
            </span>
            <div>
                <h4 class="text-xs font-black text-slate-900 dark:text-white">Klim naar de 1e Plaats!</h4>
                <p class="text-[11px] text-slate-600 dark:text-slate-300">Produceer direct in de kelder of ritsel pillen in de ziekenboeg om je score te verhogen.</p>
            </div>
        </div>
        <a href="{{ route('smuggle.index') }}" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-black text-xs shadow-xs transition inline-flex items-center justify-center gap-1.5 shrink-0">
            <span>Naar Productiekelder</span>
            <i class="fa-solid fa-arrow-right text-[10px]"></i>
        </a>
    </div>

    <!-- Footnote & Automatic Payout Note -->
    <div class="pt-4 border-t border-slate-200/80 dark:border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-[11px] text-slate-500 dark:text-purple-300/70">
        <span class="flex items-center gap-1.5">
            <i class="fa-solid fa-shield-halved text-emerald-600 dark:text-emerald-400"></i>
            <span>Anti-Cheat Actief - Alleen daadwerkelijk geproduceerde goederen in de kliniek worden geregistreerd.</span>
        </span>
        <span class="text-amber-700 dark:text-amber-300 font-extrabold">Automatische prijsuitreiking op zondag 23:59:59</span>
    </div>
</div>
