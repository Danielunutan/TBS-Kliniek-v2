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
            }
        } catch (e) {
            console.error('Fout bij ophalen ranglijst:', e);
        } finally {
            this.loading = false;
        }
    },
    updateCountdown() {
        if (this.data.remaining_seconds <= 0) {
            this.timerDisplay = '00:00:00 (Event Beëindigd)';
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
}" class="my-8 rounded-2xl bg-gradient-to-b from-slate-900 via-[#190e38] to-[#120726] border-2 border-purple-500/40 p-5 sm:p-7 text-white shadow-xl space-y-6">

    <!-- Top Event Header & Live Countdown Badge -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-purple-500/30 pb-5">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-black uppercase tracking-wider mb-2"
                 :class="data.is_active ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/40 animate-pulse' : (data.has_ended ? 'bg-slate-700/50 text-slate-300 border border-slate-600' : 'bg-amber-500/20 text-amber-300 border border-amber-500/40')">
                <span class="w-2 h-2 rounded-full" :class="data.is_active ? 'bg-emerald-400' : 'bg-amber-400'"></span>
                <span x-text="data.is_active ? '⚡ LIVE EVENT BEZIG (16:30 - 00:00)' : (data.has_ended ? '🏁 EVENT AFGELOPEN' : '⏳ EVENT START OM 16:30')"></span>
            </div>
            <h2 class="text-xl sm:text-2xl font-black tracking-tight text-white flex items-center gap-2">
                <span>🏆 Live Ranglijst: De Grote Geldjacht</span>
            </h2>
            <p class="text-xs text-purple-200/80 mt-1">
                Al het verdiende geld sinds 16:30 telt mee. De #1 om 00:00 wint automatisch <strong>750 Sterren ⭐</strong>!
            </p>
        </div>

        <!-- Countdown Clock -->
        <div class="flex flex-col items-start sm:items-end gap-1.5 shrink-0">
            <span class="text-[10px] font-black uppercase tracking-wider text-purple-300">Tijd tot middernacht (00:00)</span>
            <div class="px-4 py-2 rounded-xl bg-black/60 border border-purple-500/50 text-amber-300 font-mono text-lg sm:text-xl font-black tracking-widest flex items-center gap-2 shadow-inner">
                <i class="fa-solid fa-stopwatch text-amber-400 text-sm"></i>
                <span x-text="timerDisplay"></span>
            </div>
            <div class="flex items-center gap-2 text-[10px] text-purple-300/70">
                <span>Live update elke 15s</span>
                <span x-show="lastUpdated" x-text="'(Laatst: ' + lastUpdated + ')'"></span>
                <button type="button" @click="fetchLeaderboard()" class="text-purple-300 hover:text-white cursor-pointer ml-1" title="Nu handmatig vernieuwen">
                    <i class="fa-solid fa-arrows-rotate" :class="{ 'fa-spin': loading }"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Stats Snapshot (3 quick metrics) -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
        <div class="p-3.5 rounded-xl bg-white/5 border border-purple-500/20 flex items-center justify-between">
            <span class="text-xs text-purple-200 font-bold">Hoofdprijs</span>
            <span class="text-sm font-black text-amber-300 flex items-center gap-1.5">
                <i class="fa-solid fa-star text-amber-400"></i> 750 Sterren
            </span>
        </div>
        <div class="p-3.5 rounded-xl bg-white/5 border border-purple-500/20 flex items-center justify-between">
            <span class="text-xs text-purple-200 font-bold">Actieve Deelnemers</span>
            <span class="text-sm font-black text-white" x-text="data.total_participants + ' Patiënten'"></span>
        </div>
        <div class="p-3.5 rounded-xl bg-white/5 border border-purple-500/20 flex items-center justify-between">
            <span class="text-xs text-purple-200 font-bold">Totaal Binnengehaald</span>
            <span class="text-sm font-black text-emerald-400" x-text="data.total_cash_generated_formatted"></span>
        </div>
    </div>

    <!-- My Position Banner (Personalized for logged-in player) -->
    <template x-if="data.my_rank">
        <div class="p-4 rounded-xl bg-gradient-to-r from-purple-900/60 via-indigo-900/50 to-purple-900/60 border border-purple-400/50 flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-md">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl overflow-hidden border border-purple-300/40 shrink-0">
                    <img :src="data.my_rank.avatar_url" class="w-full h-full object-cover">
                </div>
                <div>
                    <span class="text-[10px] font-black uppercase tracking-wider text-purple-300">Jouw Huidige Status</span>
                    <div class="text-sm font-black text-white flex items-center gap-2">
                        <span x-text="data.my_rank.name"></span>
                        <span class="text-xs font-bold text-purple-200" x-text="'(Lvl ' + data.my_rank.level + ')'"></span>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-4">
                <div class="text-right">
                    <span class="text-[10px] font-bold text-purple-200 block">Jouw Verdienste</span>
                    <span class="text-sm sm:text-base font-black text-emerald-400" x-text="data.my_rank.total_earned_formatted"></span>
                </div>
                <div class="px-3.5 py-1.5 rounded-xl bg-purple-500/30 border border-purple-400/40 text-center shrink-0">
                    <span class="text-[9px] font-bold text-purple-200 block uppercase">Positie</span>
                    <span class="text-base font-black text-amber-300" x-text="'#' + data.my_rank.rank"></span>
                </div>
            </div>
        </div>
    </template>

    <!-- Leaderboard Table -->
    <div class="space-y-2">
        <h3 class="text-xs font-black uppercase tracking-wider text-purple-300 flex items-center gap-2">
            <i class="fa-solid fa-list-ol"></i>
            <span>Top Klassement (Live Stand)</span>
        </h3>

        <!-- Loading State -->
        <div x-show="loading && data.leaderboard.length === 0" class="py-8 text-center text-xs text-purple-300 animate-pulse">
            <i class="fa-solid fa-spinner fa-spin text-lg mb-2 block"></i>
            <span>Ranglijst synchroniseren met kliniekadministratie...</span>
        </div>

        <!-- Empty State -->
        <div x-show="!loading && data.leaderboard.length === 0" class="py-8 px-4 text-center rounded-xl bg-white/5 border border-purple-500/20 text-xs text-purple-200 space-y-1">
            <i class="fa-solid fa-coins text-2xl text-amber-400 mb-1"></i>
            <p class="font-bold text-sm text-white">Nog geen verdiensten geregistreerd sinds 16:30!</p>
            <p class="text-purple-300">Ga direct aan de slag met zakkenrollen, vechten op de luchtplaats, dagbesteding of klusjes om als eerste bovenaan te staan!</p>
        </div>

        <!-- Populated List -->
        <div x-show="data.leaderboard.length > 0" class="space-y-1.5">
            <template x-for="player in data.leaderboard" :key="player.character_id">
                <div class="p-3 sm:p-3.5 rounded-xl border transition-all flex items-center justify-between gap-3"
                     :class="player.rank === 1 ? 'bg-gradient-to-r from-amber-500/20 via-yellow-500/10 to-amber-500/20 border-amber-400/60 shadow-lg ring-1 ring-amber-400/30' : (player.rank === 2 ? 'bg-slate-700/30 border-slate-400/40' : (player.rank === 3 ? 'bg-amber-900/20 border-amber-700/40' : 'bg-white/5 border-purple-500/20 hover:bg-white/10'))">
                    
                    <div class="flex items-center gap-3 min-w-0">
                        <!-- Rank Badge -->
                        <div class="w-8 h-8 rounded-xl flex items-center justify-center font-black text-xs shrink-0"
                             :class="player.rank === 1 ? 'bg-amber-400 text-slate-950 shadow-md shadow-amber-400/50' : (player.rank === 2 ? 'bg-slate-300 text-slate-950' : (player.rank === 3 ? 'bg-amber-600 text-white' : 'bg-purple-950/80 border border-purple-500/30 text-purple-300'))">
                            <template x-if="player.rank === 1">
                                <i class="fa-solid fa-crown text-xs"></i>
                            </template>
                            <template x-if="player.rank !== 1">
                                <span x-text="'#' + player.rank"></span>
                            </template>
                        </div>

                        <!-- Avatar -->
                        <img :src="player.avatar_url" class="w-9 h-9 rounded-xl object-cover border border-purple-400/30 shrink-0">

                        <!-- Name & Level -->
                        <div class="min-w-0 truncate">
                            <div class="text-xs sm:text-sm font-black text-white truncate flex items-center gap-1.5">
                                <span x-text="player.name"></span>
                                <span x-show="player.rank === 1" class="text-[10px] font-black uppercase px-2 py-0.5 rounded-full bg-amber-400 text-slate-950 shadow-xs">Koploper</span>
                            </div>
                            <span class="text-[10px] text-purple-300 font-semibold" x-text="'Level ' + player.level"></span>
                        </div>
                    </div>

                    <!-- Money Earned -->
                    <div class="text-right shrink-0">
                        <span class="text-[10px] font-bold text-purple-300 block">Verdiend vanavond</span>
                        <span class="text-xs sm:text-sm font-black text-emerald-400 font-mono" x-text="player.total_earned_formatted"></span>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <!-- Live Event Footnote / Anti-Cheat Note -->
    <div class="pt-4 border-t border-purple-500/30 flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-[11px] text-purple-200/70">
        <span class="flex items-center gap-1.5">
            <i class="fa-solid fa-shield-halved text-emerald-400"></i>
            <span>Anti-Cheat Actief: Bankopnames &amp; onderlinge spelertransfers zijn uitgesloten van deze telling.</span>
        </span>
        <span class="text-amber-300 font-semibold">Prijsuitreiking: Exact om 00:00:00 vannacht</span>
    </div>
</div>
