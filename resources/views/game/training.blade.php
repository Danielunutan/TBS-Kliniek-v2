@extends('layouts.app')

@section('title', '🏋️ De Sportzaal & Krachttraining - TBS Kliniek')

@section('content')
@php
    $maxStatCap = $character->getMaxStatForLevel();
    $initialStats = [];
    $initialCosts = [];
    foreach($stats as $k => $info) {
        $val = min($maxStatCap, (int) $character->{$k});
        $initialStats[$k] = $val;
        $initialCosts[$k] = (int) max(20, round(15 + ($val * 3.5)));
    }
@endphp

<div class="space-y-6" x-data="trainingManager({
    stats: @js($initialStats),
    costs: @js($initialCosts),
    maxStat: {{ (int) $maxStatCap }},
    level: {{ (int) $character->level }},
    xp: {{ (int) $character->xp }},
    xpNext: {{ max(1, (int) $character->xpForNextLevel()) }},
    cash: {{ (int)$character->cash }},
    energy: {{ (int)$character->energy }},
    isImprisoned: {{ $character->isImprisoned() ? 'true' : 'false' }},
    isComatose: {{ $character->isComatose() ? 'true' : 'false' }},
    csrfToken: '{{ csrf_token() }}',
    trainUrl: '{{ route('training.train') }}'
})">

    <!-- Toast Notification Banner (Floating, No page jumps!) -->
    <div x-show="toast.visible" x-cloak
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 -translate-y-4 scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
         x-transition:leave-end="opacity-0 -translate-y-4 scale-95"
         class="fixed top-24 right-4 sm:right-8 z-50 max-w-md shadow-2xl rounded-2xl p-4 border backdrop-blur-md flex items-center gap-3.5"
         :class="toast.isError ? 'bg-rose-950/90 text-rose-100 border-rose-600/60' : 'bg-emerald-950/90 text-emerald-100 border-emerald-500/60'">
        <div class="w-10 h-10 rounded-xl flex items-center justify-center text-lg shrink-0"
             :class="toast.isError ? 'bg-rose-500/20 text-rose-400' : 'bg-emerald-500/20 text-emerald-400'">
            <i class="fa-solid" :class="toast.isError ? 'fa-triangle-exclamation' : 'fa-circle-check'"></i>
        </div>
        <div class="flex-1 text-xs">
            <span class="font-black block text-sm" x-text="toast.isError ? 'Trainen Mislukt' : 'Top Workout!'"></span>
            <span class="text-xs opacity-90 leading-tight" x-text="toast.message"></span>
        </div>
        <button type="button" @click="toast.visible = false" class="text-slate-400 hover:text-white p-1 cursor-pointer">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>

    @include('game.control_points.banner', ['slug' => 'sportzaal'])

    @if($character->isImprisoned())
    <div class="bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 rounded-2xl p-4 text-xs font-bold text-rose-700 dark:text-rose-300 flex items-center gap-3">
        <i class="fa-solid fa-lock text-rose-500 text-base shrink-0"></i>
        <span>Je zit momenteel in de isoleercel. De sportzaal is vergrendeld tijdens je celstraf.</span>
    </div>
    @elseif($character->isComatose() || $character->needsReviveSerum())
    <div class="bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 rounded-2xl p-4 text-xs font-bold text-rose-700 dark:text-rose-300 flex items-center gap-3">
        <i class="fa-solid fa-heart-pulse text-rose-500 text-base shrink-0"></i>
        <span>Je ligt momenteel op de Intensive Care en kunt geen krachttraining volgen.</span>
    </div>
    @endif

    <!-- Header Banner (Clean Modern) -->
    <div class="bg-white dark:bg-slate-900 p-6 sm:p-8 rounded-2xl border border-slate-200/90 dark:border-slate-800 shadow-xs flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight flex items-center gap-2">
                <i class="fa-solid fa-dumbbell text-purple-600 dark:text-purple-400"></i>
                <span>De Sportzaal & Krachttraining</span>
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 max-w-2xl leading-relaxed">
                Train je fysieke en mentale vaardigheden onder leiding van beruchte kliniekfiguren. Upgrades verlopen nu direct <strong>zonder haperingen of verspringende schermen</strong>!
            </p>
        </div>
        <div class="flex items-center gap-2.5">
            <div class="bg-purple-50 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 px-4 py-2.5 rounded-xl text-xs font-bold border border-purple-200/80 dark:border-purple-800/70 shadow-xs flex items-center gap-2">
                <i class="fa-solid fa-wallet text-purple-500"></i>
                <span>Contant: <strong class="text-slate-900 dark:text-white font-mono" x-text="'€' + formatNumber(cash)">€{{ number_format($character->cash, 0, ',', '.') }}</strong></span>
            </div>
            <div class="bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 px-4 py-2.5 rounded-xl text-xs font-bold border border-emerald-200/80 dark:border-emerald-800/70 shadow-xs flex items-center gap-2">
                <i class="fa-solid fa-bolt text-emerald-500"></i>
                <span>Energie: <strong class="text-slate-900 dark:text-white font-mono" x-text="energy + '/{{ $character->max_energy }}'">{{ $character->energy }}/{{ $character->max_energy }}</strong></span>
            </div>
        </div>
    </div>

    <!-- Armpjedruk Arena Promo Banner -->
    <div class="relative overflow-hidden bg-white dark:bg-slate-900 border border-amber-200/80 dark:border-amber-800/70 rounded-2xl p-5 shadow-xs flex flex-col sm:flex-row items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-amber-50 dark:bg-amber-950/60 border border-amber-200/80 dark:border-amber-800/70 text-amber-600 dark:text-amber-400 flex items-center justify-center text-2xl shrink-0 shadow-2xs">
                <i class="fa-solid fa-hand-back-fist"></i>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <span class="text-xs font-black uppercase tracking-wider bg-amber-50 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 border border-amber-200/80 dark:border-amber-800/70 px-2 py-0.5 rounded-full">Nieuw Duel</span>
                    <h3 class="font-extrabold text-base text-slate-900 dark:text-white">De Illegale Armpjedruk Arena</h3>
                </div>
                <p class="text-xs text-slate-600 dark:text-slate-400 mt-0.5">
                    Test je getrainde Kracht & Snelheid aan de armpjedruktafel. Versla 4 beruchte NPC's of daag medespelers uit voor geldpotten tot €100.000!
                </p>
            </div>
        </div>
        <a href="{{ route('armwrestling.index') }}" class="shrink-0 bg-amber-500 hover:bg-amber-400 text-slate-950 font-black text-xs px-5 py-2.5 rounded-xl transition-all duration-150 shadow-xs flex items-center gap-2 cursor-pointer active:scale-95">
            <span>Betreed de Arena</span>
            <i class="fa-solid fa-arrow-right text-[11px]"></i>
        </a>
    </div>

    <!-- Level Plafond & Focus Keuze Banner -->
    <div class="bg-white dark:bg-slate-900 border border-purple-200/80 dark:border-purple-800/70 rounded-2xl p-4 sm:p-5 shadow-xs space-y-3">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-start gap-3">
                <div class="w-10 h-10 rounded-xl bg-purple-50 dark:bg-purple-950/60 border border-purple-200/80 dark:border-purple-800/70 text-purple-600 dark:text-purple-400 flex items-center justify-center text-base shrink-0">
                    <i class="fa-solid fa-scale-balanced"></i>
                </div>
                <div>
                    <h2 class="text-xs sm:text-sm font-black text-slate-900 dark:text-white">
                        Kies je focus: Oefening (+1 Stat) of Level Verhogen (+50 XP)
                    </h2>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5 leading-relaxed">
                        Op <strong class="text-slate-800 dark:text-slate-200" x-text="'Level ' + level">Level {{ $character->level }}</strong> is het stat-plafond <strong class="text-slate-800 dark:text-slate-200" x-text="maxStat">{{ $maxStatCap }}</strong> per eigenschap (+50 per level). Kies tussen <strong class="text-purple-600 dark:text-purple-400">Oefening (+1 Stat &amp; +12 XP)</strong> of <strong class="text-amber-600 dark:text-amber-400">Level Focus (+50 XP)</strong> om in level te stijgen.
                    </p>
                </div>
            </div>
            <div class="shrink-0 bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70 rounded-xl px-3.5 py-2 text-right">
                <div class="text-[10px] font-black uppercase tracking-wider text-slate-400 dark:text-slate-500" x-text="'Level ' + level + ' Plafond: ' + maxStat">Level {{ $character->level }} Plafond: {{ $maxStatCap }}</div>
                <div class="text-xs font-black font-mono text-amber-600 dark:text-amber-400" x-text="formatNumber(xp) + ' / ' + formatNumber(xpNext) + ' XP'">{{ number_format($character->xp, 0, ',', '.') }} / {{ number_format($character->xpForNextLevel(), 0, ',', '.') }} XP</div>
            </div>
        </div>
    </div>

    <!-- Training Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($stats as $key => $info)
        @php
            $icon = match($key) {
                'strength' => 'fa-dumbbell text-rose-500',
                'defense' => 'fa-shield-halved text-blue-500',
                'stamina' => 'fa-person-running text-emerald-500',
                'intelligence' => 'fa-brain text-purple-500',
                'charisma' => 'fa-comments text-amber-500',
                'discipline' => 'fa-medal text-indigo-500',
                default => 'fa-dumbbell text-purple-500',
            };
        @endphp
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/90 dark:border-slate-800 shadow-xs overflow-hidden flex flex-col justify-between transition-all duration-300"
             :class="{
                'ring-2 ring-purple-500 shadow-lg scale-[1.01] bg-purple-50/10': flashingStat === '{{ $key }}',
                'border-amber-300/80 dark:border-amber-700/60': stats['{{ $key }}'] >= maxStat
             }">
            
            <div class="p-5 space-y-3">
                <div class="flex items-center justify-between gap-2">
                    <div>
                        <h2 class="text-base font-black text-slate-900 dark:text-white flex items-center gap-1.5">
                            <i class="fa-solid {{ $icon }} text-sm"></i>
                            <span>{{ $info['name'] }}</span>
                        </h2>
                        <span class="text-[11px] font-bold text-purple-600 dark:text-purple-400">
                            <i class="fa-solid fa-user-ninja mr-1"></i>Trainer: {{ $info['trainer'] }}
                        </span>
                    </div>
                    <!-- Live Stat Counter Badge with Level Cap -->
                    <div class="px-3 py-2 rounded-xl bg-purple-50 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 border border-purple-200/80 dark:border-purple-800/70 font-black text-sm font-mono flex flex-col items-end justify-center shadow-xs transition-transform duration-300"
                         :class="{
                            'scale-110 bg-purple-600 text-white dark:bg-purple-500': flashingStat === '{{ $key }}',
                            'bg-amber-50 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 border-amber-200/80 dark:border-amber-800/70': stats['{{ $key }}'] >= maxStat && flashingStat !== '{{ $key }}'
                         }">
                        <div>
                            <span x-text="stats['{{ $key }}']">{{ min($maxStatCap, (int) $character->{$key}) }}</span>
                            <span class="text-[10px] opacity-70" x-text="'/ ' + maxStat">/ {{ $maxStatCap }}</span>
                        </div>
                        <span x-show="stats['{{ $key }}'] >= maxStat" x-cloak class="text-[9px] uppercase tracking-wider text-amber-600 dark:text-amber-400 font-black">Level Max</span>
                    </div>
                </div>

                <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed bg-slate-50/90 dark:bg-slate-800/60 p-2.5 rounded-xl border border-slate-200/80 dark:border-slate-700/70 min-h-[48px]">
                    {{ $info['desc'] }}
                </p>

                <!-- Costs & Benefits Bar -->
                <div class="flex items-center justify-between text-xs font-semibold pt-1">
                    <span class="text-amber-600 dark:text-amber-400 flex items-center gap-1 font-bold">
                        <i class="fa-solid fa-bolt text-amber-500 text-[10px]"></i> 10 Energie
                    </span>
                    <span class="text-emerald-700 dark:text-emerald-400 font-mono font-bold flex items-center gap-1">
                        <i class="fa-solid fa-coins text-emerald-500 text-[10px]"></i>
                        <span x-text="'€' + formatNumber(costs['{{ $key }}'])">€{{ (int) max(20, round(15 + (min($maxStatCap, (int) $character->{$key}) * 3.5))) }}</span>
                    </span>
                    <span class="text-purple-700 dark:text-purple-300 font-bold" x-text="stats['{{ $key }}'] >= maxStat ? '+50 XP (Focus)' : '+1 {{ $info['name'] }} / +12 XP'">+1 {{ $info['name'] }}</span>
                </div>
            </div>

            <!-- Action Buttons Bar (In-Place Async, No jumping!) -->
            <div class="px-5 py-3 bg-slate-50/90 dark:bg-slate-800/60 border-t border-slate-200/80 dark:border-slate-800 space-y-2">
                <div class="flex items-center gap-2">
                    <!-- Standard +1 Train Button -->
                    <button type="button" 
                            @click="train('{{ $key }}', 1, 'stat')" 
                            :disabled="isTraining || isImprisoned || isComatose || stats['{{ $key }}'] >= maxStat || cash < costs['{{ $key }}'] || energy < 10" 
                            class="flex-1 bg-purple-600 hover:bg-purple-700 dark:bg-purple-600 dark:hover:bg-purple-500 disabled:opacity-40 disabled:cursor-not-allowed text-white font-black py-2.5 px-3 rounded-xl text-xs shadow-xs transition-all duration-150 flex items-center justify-center gap-1.5 cursor-pointer active:scale-95">
                        <i class="fa-solid" :class="stats['{{ $key }}'] >= maxStat ? 'fa-lock text-amber-300' : (isTraining && activeStat === '{{ $key }}' ? 'fa-dumbbell animate-spin' : 'fa-dumbbell')"></i>
                        <span x-text="stats['{{ $key }}'] >= maxStat ? 'Max (Lvl ' + level + ')' : 'Oefening (+1)'">Oefening (+1)</span>
                    </button>

                    <!-- Batch +5 Train Quick Upgrade -->
                    <button type="button" 
                            @click="train('{{ $key }}', 5, 'stat')" 
                            :disabled="isTraining || isImprisoned || isComatose || stats['{{ $key }}'] >= maxStat || cash < (costs['{{ $key }}'] * 5) || energy < 50" 
                            title="Train tot 5x in 1 klik zonder schermverspringing!"
                            class="px-3 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 border border-slate-200/80 dark:border-slate-700 font-black text-xs transition-all duration-150 disabled:opacity-40 disabled:cursor-not-allowed cursor-pointer active:scale-95">
                        +5x
                    </button>
                </div>

                <!-- Level Focus Button (+50 XP) -->
                <button type="button"
                        @click="train('{{ $key }}', 1, 'level')"
                        :disabled="isTraining || isImprisoned || isComatose || cash < costs['{{ $key }}'] || energy < 10"
                        class="w-full py-2 px-3 rounded-xl text-xs font-black transition-all duration-150 shadow-xs flex items-center justify-center gap-1.5 disabled:opacity-40 disabled:cursor-not-allowed cursor-pointer active:scale-95"
                        :class="stats['{{ $key }}'] >= maxStat ? 'bg-amber-500 hover:bg-amber-400 text-slate-950 ring-2 ring-amber-400/50' : 'bg-slate-800 hover:bg-slate-700 dark:bg-slate-700 dark:hover:bg-slate-600 text-amber-300 border border-amber-500/30'">
                    <i class="fa-solid fa-angles-up text-[11px]"></i>
                    <span>Level Focus (+50 XP)</span>
                </button>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Recent Logs -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/90 dark:border-slate-800 shadow-xs p-5 sm:p-6">
        <h3 class="text-xs font-bold uppercase text-slate-800 dark:text-slate-200 tracking-wider flex items-center gap-2 mb-3">
            <i class="fa-solid fa-clock-rotate-left text-purple-600 dark:text-purple-400"></i>
            <span>Recente Trainingssessies</span>
        </h3>
        <div class="divide-y divide-slate-100 dark:divide-slate-800 text-xs">
            <template x-for="log in liveLogs" :key="log.id">
                <div class="py-2.5 flex items-center justify-between animate-fade-in">
                    <div>
                        <span class="font-bold text-slate-800 dark:text-slate-200" x-text="log.stat_name"></span>
                        <template x-if="log.stat_gain > 0">
                            <span>
                                verhoogd met <span class="font-bold text-purple-600 dark:text-purple-400" x-text="'+' + log.stat_gain"></span>
                            </span>
                        </template>
                        <template x-if="log.stat_gain === 0">
                            <span>
                                getraind op <span class="font-bold text-amber-600 dark:text-amber-400" x-text="'+' + log.xp_gain + ' XP (Level Focus)'"></span>
                            </span>
                        </template>
                        door workout met <span class="italic text-slate-600 dark:text-slate-400" x-text="log.trainer"></span>.
                    </div>
                    <div class="text-[11px] text-slate-400 font-mono">Zojuist</div>
                </div>
            </template>

            @forelse($recentLogs as $log)
            <div class="py-2.5 flex items-center justify-between">
                <div>
                    <span class="font-bold text-slate-800 dark:text-slate-200">{{ $stats[$log->stat]['name'] ?? $log->stat }}</span>
                    @if($log->stat_gain > 0)
                        verhoogd met <span class="font-bold text-purple-600 dark:text-purple-400">+{{ $log->stat_gain }}</span>
                    @else
                        getraind op <span class="font-bold text-amber-600 dark:text-amber-400">Level Focus (XP)</span>
                    @endif
                    door workout met <span class="italic text-slate-600 dark:text-slate-400">{{ $log->trainer }}</span>.
                </div>
                <div class="text-[11px] text-slate-400">
                    {{ $log->created_at->diffForHumans() }}
                </div>
            </div>
            @empty
            <p class="text-slate-500 py-2">Nog geen trainingen gelogd. Tijd om die halters op te pakken!</p>
            @endforelse
        </div>
    </div>
</div>

<script>
function trainingManager(config) {
    return {
        stats: config.stats,
        costs: config.costs,
        maxStat: config.maxStat,
        level: config.level,
        xp: config.xp,
        xpNext: config.xpNext,
        cash: config.cash,
        energy: config.energy,
        isImprisoned: config.isImprisoned,
        isComatose: config.isComatose,
        isTraining: false,
        activeStat: null,
        flashingStat: null,
        liveLogs: [],
        toast: {
            visible: false,
            isError: false,
            message: ''
        },
        formatNumber(n) {
            return new Intl.NumberFormat('nl-NL').format(n);
        },
        showToast(message, isError = false) {
            this.toast.message = message;
            this.toast.isError = isError;
            this.toast.visible = true;
            setTimeout(() => {
                this.toast.visible = false;
            }, 3500);
        },
        async train(statKey, times = 1, mode = 'stat') {
            if (this.isTraining) return;
            if (this.isComatose) {
                this.showToast('Je ligt in Code Blauw (Coma) en kunt nu niet trainen!', true);
                return;
            }
            if (this.isImprisoned) {
                this.showToast('Je zit in de isoleercel en kunt nu niet trainen!', true);
                return;
            }

            if (mode === 'stat' && this.stats[statKey] >= this.maxStat) {
                this.showToast('Maximum voor dit level bereikt! Kies Level Focus (+50 XP) om in level te stijgen.', true);
                return;
            }

            const currentCost = this.costs[statKey] * times;
            const requiredEnergy = 10 * times;

            if (this.cash < currentCost) {
                this.showToast('Niet genoeg contant geld voor deze training!', true);
                return;
            }

            if (this.energy < requiredEnergy) {
                this.showToast('Niet genoeg energie! Eet wat in de kantine of wacht even.', true);
                return;
            }

            this.isTraining = true;
            this.activeStat = statKey;

            try {
                const response = await fetch(config.trainUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': config.csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        stat: statKey,
                        times: times,
                        mode: mode
                    })
                });

                const data = await response.json();

                if (!response.ok || !data.success) {
                    this.showToast(data.message || 'Er is een fout opgetreden bij het trainen.', true);
                    return;
                }

                // Smooth in-place updates!
                this.stats[statKey] = data.new_value;
                this.costs[statKey] = data.next_cost;
                if (data.max_stat) this.maxStat = data.max_stat;
                if (data.level) this.level = data.level;
                if (typeof data.xp === 'number') this.xp = data.xp;
                if (data.xp_next) this.xpNext = data.xp_next;
                this.cash = data.cash;
                this.energy = data.energy;

                // Trigger subtle glow/pop animation
                this.flashingStat = statKey;
                setTimeout(() => {
                    this.flashingStat = null;
                }, 700);

                // Add to live log
                this.liveLogs.unshift({
                    id: Date.now() + Math.random(),
                    stat_name: data.stat_name,
                    stat_gain: data.stat_gain,
                    xp_gain: data.xp_gain || 12,
                    trainer: 'Jouw Trainer'
                });

                // Update site topbar realtime tracker directly without an extra HTTP request
                if (typeof window.tbsApplyStats === 'function') {
                    window.tbsApplyStats({
                        cash: data.cash,
                        energy: data.energy,
                        level: data.level,
                        xp: data.xp
                    });
                }

                // Show success feedback
                this.showToast(data.message);

            } catch (err) {
                this.showToast('Verbindingsfout bij het versturen van training.', true);
            } finally {
                this.isTraining = false;
                this.activeStat = null;
            }
        }
    };
}
</script>
@endsection
