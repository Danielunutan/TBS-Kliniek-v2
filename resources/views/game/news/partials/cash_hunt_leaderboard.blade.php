<div x-data="{
    loading: true,
    lastUpdated: '',
    data: {
        is_active: false,
        has_ended: false,
        remaining_seconds: 0,
        prize_stars: 750,
        leaderboard: [],
        my_rank: null,
        total_participants: 0,
        total_cash_generated_formatted: '€0'
    },
    timerDisplay: 'Laden...',
    timerInterval: null,
    pollInterval: null,
    async fetchLeaderboard() {
        try {
            const res = await fetch('{{ route('api.event.cash_hunt_leaderboard') }}', {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            });
            if (res.ok) {
                this.data = await res.json();
                const now = new Date();
                this.lastUpdated = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' });
                if (this.data.has_ended) {
                    this.timerDisplay = '00:00:00';
                    if (this.pollInterval) { clearInterval(this.pollInterval); this.pollInterval = null; }
                    if (this.timerInterval) { clearInterval(this.timerInterval); this.timerInterval = null; }
                }
            }
        } catch (e) {
            console.error('Fout bij ophalen ranglijst:', e);
        } finally {
            this.loading = false;
        }
    },
    updateCountdown() {
        if (this.loading) {
            return;
        }
        if (this.data.remaining_seconds <= 0) {
            this.timerDisplay = '00:00:00';
            return;
        }
        this.data.remaining_seconds--;
        const hours = Math.floor(this.data.remaining_seconds / 3600);
        const minutes = Math.floor((this.data.remaining_seconds % 3600) / 60);
        const seconds = this.data.remaining_seconds % 60;
        this.timerDisplay = String(hours).padStart(2, '0') + ':' +
                            String(minutes).padStart(2, '0') + ':' +
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
                <span x-text="data.is_active ? '⚡ LIVE EVENT BEZIG (16:30 - 00:00)' : (data.has_ended ? '🏁 EVENT AFGELOPEN' : '⏳ EVENT START OM 16:30')"></span>
            </div>
            <h2 class="text-xl sm:text-2xl font-black tracking-tight text-slate-900 dark:text-white flex items-center gap-2">
                <span>🏆 Live Ranglijst - De Grote Geldjacht</span>
            </h2>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                Al het verdiende geld sinds 16:30 telt mee. De #1 om 00:00 wint automatisch <strong>750 Sterren ⭐</strong>!
            </p>
        </div>

        <!-- Countdown Clock (Always clear high-contrast display) -->
        <div class="flex flex-col items-start sm:items-end gap-1.5 shrink-0">
            <span class="text-[10px] font-black uppercase tracking-wider text-slate-500 dark:text-purple-300">Resterende tijd tot 00:00</span>
            <div class="px-4 py-2 rounded-xl bg-slate-900 dark:bg-black/70 border border-slate-800 dark:border-purple-600/50 text-amber-400 font-mono text-lg sm:text-xl font-black tracking-widest flex items-center gap-2 shadow-inner">
                <i class="fa-solid fa-stopwatch text-amber-400 text-sm"></i>
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
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
        <div class="p-3.5 rounded-xl bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70 flex items-center justify-between">
            <span class="text-xs text-slate-600 dark:text-slate-300 font-bold">Hoofdprijs</span>
            <span class="text-sm font-black text-amber-600 dark:text-amber-400 flex items-center gap-1.5">
                <i class="fa-solid fa-star text-amber-500"></i> 750 Sterren
            </span>
        </div>
        <div class="p-3.5 rounded-xl bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70 flex items-center justify-between">
            <span class="text-xs text-slate-600 dark:text-slate-300 font-bold">Actieve Deelnemers</span>
            <span class="text-sm font-black text-slate-900 dark:text-white" x-text="data.total_participants + ' Patiënten'"></span>
        </div>
        <div class="p-3.5 rounded-xl bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70 flex items-center justify-between">
            <span class="text-xs text-slate-600 dark:text-slate-300 font-bold">Totaal Binnengehaald</span>
            <span class="text-sm font-black text-emerald-600 dark:text-emerald-400 font-mono" x-text="data.total_cash_generated_formatted"></span>
        </div>
    </div>

    <!-- My Position Banner (Personalized for logged-in player) -->
    <template x-if="data.my_rank">
        <div class="p-4 rounded-xl bg-purple-50/90 dark:bg-purple-950/50 border border-purple-200/80 dark:border-purple-800/70 flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-2xs">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl overflow-hidden border border-purple-300 dark:border-purple-700 shrink-0">
                    <img :src="data.my_rank.avatar_url || '/assets/characters/avatar_male_1.jpg'" class="w-full h-full object-cover">
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
                    <span class="text-[10px] font-bold text-slate-500 dark:text-purple-200 block">Jouw Verdienste</span>
                    <span class="text-sm sm:text-base font-black text-emerald-600 dark:text-emerald-400 font-mono" x-text="data.my_rank.total_earned_formatted"></span>
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
        <h3 class="text-xs font-black uppercase tracking-wider text-slate-600 dark:text-purple-300 flex items-center gap-2">
            <i class="fa-solid fa-list-ol text-purple-600 dark:text-purple-400"></i>
            <span>Top Klassement (Live Stand)</span>
        </h3>

        <!-- Loading State -->
        <div x-show="loading && data.leaderboard.length === 0" class="py-8 text-center text-xs text-slate-500 dark:text-purple-300 animate-pulse">
            <i class="fa-solid fa-spinner fa-spin text-lg mb-2 block text-purple-600"></i>
            <span>Ranglijst synchroniseren met kliniekadministratie...</span>
        </div>

        <!-- Empty State -->
        <div x-show="!loading && data.leaderboard.length === 0" class="py-8 px-4 text-center rounded-xl bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70 text-xs text-slate-600 dark:text-slate-300 space-y-1">
            <i class="fa-solid fa-coins text-2xl text-amber-500 mb-1"></i>
            <p class="font-extrabold text-sm text-slate-900 dark:text-white">Nog geen verdiensten geregistreerd sinds 16:30!</p>
            <p class="text-slate-500 dark:text-slate-400 max-w-md mx-auto">Ga direct aan de slag met zakkenrollen, vechten op de luchtplaats, dagbesteding of klusjes om als eerste bovenaan te staan!</p>
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
                        <img :src="player.avatar_url || '/assets/characters/avatar_male_1.jpg'" class="w-9 h-9 rounded-xl object-cover border border-slate-200 dark:border-purple-400/30 shrink-0">

                        <!-- Name & Level -->
                        <div class="min-w-0 truncate">
                            <div class="text-xs sm:text-sm font-black text-slate-900 dark:text-white truncate flex items-center gap-1.5">
                                <span x-text="player.name"></span>
                                <span x-show="player.rank === 1" class="text-[10px] font-black uppercase px-2 py-0.5 rounded-full bg-amber-400 text-slate-950 shadow-xs">Koploper</span>
                            </div>
                            <span class="text-[10px] text-slate-500 dark:text-slate-400 font-semibold" x-text="'Level ' + player.level"></span>
                        </div>
                    </div>

                    <!-- Money Earned -->
                    <div class="text-right shrink-0">
                        <span class="text-[10px] font-bold text-slate-400 dark:text-purple-300 block">Verdiend vanavond</span>
                        <span class="text-xs sm:text-sm font-black text-emerald-600 dark:text-emerald-400 font-mono" x-text="player.total_earned_formatted"></span>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <!-- Live Event Footnote / Anti-Cheat Note -->
    <div class="pt-4 border-t border-slate-200/80 dark:border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-[11px] text-slate-500 dark:text-purple-300/70">
        <span class="flex items-center gap-1.5">
            <i class="fa-solid fa-shield-halved text-emerald-600 dark:text-emerald-400"></i>
            <span>Anti-Cheat Actief - Bankopnames en onderlinge spelertransfers zijn uitgesloten van deze telling.</span>
        </span>
        <span class="text-amber-700 dark:text-amber-300 font-extrabold">Prijsuitreiking om 00:00:00 vannacht</span>
    </div>
</div>
