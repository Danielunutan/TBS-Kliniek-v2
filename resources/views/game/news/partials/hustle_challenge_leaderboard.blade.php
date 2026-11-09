<div x-data="{
    loading: true,
    lastUpdated: '',
    data: {
        is_active: true,
        has_ended: false,
        remaining_seconds: 0,
        prize_stars: 750,
        prize_cash_formatted: '€150.000.000',
        leaderboard: [],
        my_rank: null,
        total_participants: 0,
        total_hustles_completed_formatted: '0'
    },
    timerDisplay: 'Laden...',
    timerInterval: null,
    pollInterval: null,
    async fetchLeaderboard() {
        try {
            const res = await fetch('{{ route('api.event.hustle_challenge_leaderboard') }}', {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            });
            if (res.ok) {
                this.data = await res.json();
                const now = new Date();
                this.lastUpdated = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' });
                if (this.data.has_ended) {
                    this.timerDisplay = '00.00.00';
                    if (this.pollInterval) { clearInterval(this.pollInterval); this.pollInterval = null; }
                    if (this.timerInterval) { clearInterval(this.timerInterval); this.timerInterval = null; }
                }
            }
        } catch (e) {
            console.error('Fout bij ophalen hossel ranglijst', e);
        } finally {
            this.loading = false;
        }
    },
    updateCountdown() {
        if (this.loading) {
            return;
        }
        if (this.data.remaining_seconds <= 0) {
            this.timerDisplay = '00.00.00';
            return;
        }
        this.data.remaining_seconds--;
        const hours = Math.floor(this.data.remaining_seconds / 3600);
        const minutes = Math.floor((this.data.remaining_seconds % 3600) / 60);
        const seconds = this.data.remaining_seconds % 60;
        this.timerDisplay = String(hours).padStart(2, '0') + '.' +
                            String(minutes).padStart(2, '0') + '.' +
                            String(seconds).padStart(2, '0');
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
                <span x-text="data.is_active ? '⚡ LIVE EVENT BEZIG (TOT DONDERDAG 23.59 UUR)' : (data.has_ended ? '🏁 EVENT AFGELOPEN' : '⏳ EVENT START BINNENKORT')"></span>
            </div>
            <h2 class="text-xl sm:text-2xl font-black tracking-tight text-slate-900 dark:text-white flex items-center gap-2">
                <span>🏆 Live Ranglijst De Grote Hossel Challenge</span>
            </h2>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                Alleen succesvolle beurten op Bookie, Shag Kartel en Telecom tellen mee. De #1 wint <strong>750 Sterren ⭐ én €150.000.000 cash</strong>!
            </p>
        </div>

        <!-- Countdown Clock -->
        <div class="flex flex-col items-start sm:items-end gap-1.5 shrink-0">
            <span class="text-[10px] font-black uppercase tracking-wider text-slate-500 dark:text-purple-300">Tijd resterend tot donderdag 23.59</span>
            <div class="px-4 py-2 rounded-xl bg-slate-900 dark:bg-black/70 border border-slate-800 dark:border-purple-600/50 text-amber-400 font-mono text-lg sm:text-xl font-black tracking-widest flex items-center gap-2 shadow-inner">
                <i class="fa-solid fa-stopwatch text-amber-400 text-sm"></i>
                <span x-text="timerDisplay"></span>
            </div>
            <div class="flex items-center gap-2 text-[10px] text-slate-400 dark:text-purple-300/70">
                <span>Auto update elke 15s</span>
                <span x-show="lastUpdated" x-text="'(' + lastUpdated + ')'"></span>
                <button type="button" @click="fetchLeaderboard()" class="text-purple-600 dark:text-purple-300 hover:underline font-bold cursor-pointer inline-flex items-center gap-1" title="Nu handmatig vernieuwen">
                    <i class="fa-solid fa-arrows-rotate" :class="{ 'fa-spin': loading }"></i>
                    <span>Ververs</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Stats Snapshot (3 clean metrics) -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
        <div class="p-3.5 rounded-xl bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70 flex items-center justify-between">
            <span class="text-xs text-slate-600 dark:text-slate-300 font-bold">Hoofdprijs #1</span>
            <span class="text-sm font-black text-amber-600 dark:text-amber-400 flex items-center gap-1.5">
                <i class="fa-solid fa-star text-amber-500"></i> 750 ⭐ + €150M
            </span>
        </div>
        <div class="p-3.5 rounded-xl bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70 flex items-center justify-between">
            <span class="text-xs text-slate-600 dark:text-slate-300 font-bold">Actieve Deelnemers</span>
            <span class="text-sm font-black text-slate-900 dark:text-white" x-text="data.total_participants + ' Patiënten'"></span>
        </div>
        <div class="p-3.5 rounded-xl bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70 flex items-center justify-between">
            <span class="text-xs text-slate-600 dark:text-slate-300 font-bold">Geregelde Top Klussen</span>
            <span class="text-sm font-black text-emerald-600 dark:text-emerald-400 font-mono" x-text="data.total_hustles_completed_formatted"></span>
        </div>
    </div>

    <!-- My Position Banner -->
    <template x-if="data.my_rank">
        <div class="p-4 rounded-xl bg-purple-50/90 dark:bg-purple-950/50 border border-purple-200/80 dark:border-purple-800/70 flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-2xs">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl overflow-hidden border border-purple-300 dark:border-purple-700 shrink-0">
                    <img :src="data.my_rank.avatar_url || '/assets/characters/avatar_male_1.jpg'" class="w-full h-full object-cover">
                </div>
                <div>
                    <span class="text-[10px] font-black uppercase tracking-wider text-purple-700 dark:text-purple-300">Jouw Huidige Score</span>
                    <div class="text-sm font-black text-slate-900 dark:text-white flex items-center gap-2">
                        <span x-text="data.my_rank.name"></span>
                        <span class="text-xs font-bold text-slate-500 dark:text-slate-400" x-text="'(Lvl ' + data.my_rank.level + ')'"></span>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-4">
                <div class="text-right">
                    <span class="text-[10px] font-bold text-slate-500 dark:text-purple-200 block">Jouw Top Beurten</span>
                    <span class="text-sm sm:text-base font-black text-emerald-600 dark:text-emerald-400 font-mono" x-text="data.my_rank.total_hustles_formatted"></span>
                </div>
                <div class="px-3.5 py-1.5 rounded-xl bg-purple-600 text-white text-center shrink-0 shadow-xs">
                    <span class="text-[9px] font-bold text-purple-200 block uppercase">Positie</span>
                    <span class="text-base font-black text-white" x-text="data.my_rank.rank ? '#' + data.my_rank.rank : '-'"></span>
                </div>
            </div>
        </div>
    </template>

    <!-- Leaderboard Table -->
    <div class="space-y-2">
        <h3 class="text-xs font-black uppercase tracking-wider text-slate-600 dark:text-purple-300 flex items-center gap-2">
            <i class="fa-solid fa-list-ol text-purple-600 dark:text-purple-400"></i>
            <span>Top Klassement (Live Stand)</span>
        </h3>

        <!-- Loading State -->
        <div x-show="loading && data.leaderboard.length === 0" class="py-8 text-center text-xs text-slate-500 dark:text-purple-300 animate-pulse">
            <i class="fa-solid fa-circle-notch fa-spin mr-2"></i>
            <span>Live data ophalen...</span>
        </div>

        <!-- Empty State -->
        <div x-show="!loading && data.leaderboard.length === 0" class="py-8 text-center text-xs text-slate-500 dark:text-purple-300">
            <i class="fa-solid fa-dice text-2xl text-purple-400 mb-2 block"></i>
            <span>Nog geen beurten geregistreerd sinds de start. Voer als eerste Recreatie Bookie, Shag Kartel of Kliniek Telecom uit om bovenaan te staan!</span>
        </div>

        <!-- Table rows -->
        <div x-show="data.leaderboard.length > 0" class="space-y-1.5">
            <template x-for="item in data.leaderboard" :key="item.character_id">
                <div class="p-2.5 sm:p-3 rounded-xl border transition flex items-center justify-between gap-3"
                     :class="item.rank === 1 ? 'bg-amber-50/80 dark:bg-amber-950/40 border-amber-300/80 dark:border-amber-700/60 shadow-xs' : (item.rank === 2 ? 'bg-slate-100/80 dark:bg-slate-800/60 border-slate-300 dark:border-slate-700' : (item.rank === 3 ? 'bg-orange-50/70 dark:bg-orange-950/30 border-orange-200 dark:border-orange-800/50' : 'bg-slate-50/60 dark:bg-slate-800/30 border-slate-200/70 dark:border-slate-800 hover:bg-slate-100 dark:hover:bg-slate-800/60'))">
                    
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-7 h-7 rounded-lg flex items-center justify-center font-black text-xs shrink-0 font-mono"
                             :class="item.rank === 1 ? 'bg-amber-400 text-slate-950 shadow-xs' : (item.rank === 2 ? 'bg-slate-300 text-slate-900' : (item.rank === 3 ? 'bg-orange-300 text-slate-950' : 'bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300'))"
                             x-text="'#' + item.rank">
                        </div>

                        <div class="w-8 h-8 rounded-lg overflow-hidden border border-slate-200 dark:border-slate-700 shrink-0">
                            <img :src="item.avatar_url || '/assets/characters/avatar_male_1.jpg'" class="w-full h-full object-cover">
                        </div>

                        <div class="min-w-0">
                            <div class="text-xs sm:text-sm font-black text-slate-900 dark:text-white truncate flex items-center gap-1.5">
                                <span x-text="item.name"></span>
                                <span x-show="item.rank === 1" class="text-amber-500 text-xs">👑</span>
                            </div>
                            <span class="text-[10px] font-bold text-slate-500 dark:text-slate-400" x-text="'Level ' + item.level"></span>
                        </div>
                    </div>

                    <div class="text-right shrink-0">
                        <span class="text-xs sm:text-sm font-black text-emerald-600 dark:text-emerald-400 font-mono" x-text="item.total_hustles_formatted"></span>
                        <span class="text-[9px] font-bold text-slate-400 block" x-text="item.rank === 1 ? '750⭐ + €150M' : (item.rank === 2 ? 'Ereplaats' : '')"></span>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <!-- Quick Link naar Hosselen -->
    <div class="pt-2 border-t border-slate-200/80 dark:border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs">
        <span class="text-slate-500 dark:text-slate-400">
            Klaar om de leiding te nemen? Start direct met een van de 3 kwalificerende hossels.
        </span>
        <a href="{{ route('hustle.index') }}" class="w-full sm:w-auto px-4 py-2 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-black text-xs text-center transition active:scale-95 shadow-xs flex items-center justify-center gap-2 cursor-pointer">
            <i class="fa-solid fa-handshake"></i>
            <span>Naar Hosselen & Werkplaats</span>
        </a>
    </div>
</div>
