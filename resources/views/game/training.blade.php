@extends('layouts.app')

@section('title', '🏋️ De Sportzaal & Krachttraining - TBS Kliniek')

@section('content')
@php
    $initialStats = [];
    $initialCosts = [];
    foreach($stats as $k => $info) {
        $val = (int) $character->{$k};
        $initialStats[$k] = $val;
        $initialCosts[$k] = (int) max(20, round(15 + ($val * 3.5)));
    }
@endphp

<div class="space-y-6" x-data="trainingManager({
    stats: {{ json_encode($initialStats) }},
    costs: {{ json_encode($initialCosts) }},
    cash: {{ (int)$character->cash }},
    energy: {{ (int)$character->energy }},
    isImprisoned: {{ $character->isImprisoned() ? 'true' : 'false' }},
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

    <!-- Header Banner (Clean Modern) -->
    <div class="bg-white dark:bg-[#110726] p-6 sm:p-8 rounded-2xl border border-slate-200 dark:border-purple-900/40 shadow-xs flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight flex items-center gap-2">
                <i class="fa-solid fa-dumbbell text-purple-600 dark:text-purple-400"></i>
                <span>De Sportzaal & Krachttraining</span>
            </h1>
            <p class="text-xs text-slate-500 dark:text-purple-300/80 mt-1 max-w-2xl leading-relaxed">
                Train je fysieke en mentale vaardigheden onder leiding van beruchte kliniekfiguren. Upgrades verlopen nu direct <strong>zonder haperingen of verspringende schermen</strong>!
            </p>
        </div>
        <div class="flex items-center gap-2.5">
            <div class="bg-purple-50 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 px-4 py-2.5 rounded-xl text-xs font-bold border border-purple-200 dark:border-purple-800/50 shadow-xs flex items-center gap-2">
                <i class="fa-solid fa-wallet text-purple-500"></i>
                <span>Contant: <strong class="text-slate-900 dark:text-white font-mono" x-text="'€' + formatNumber(cash)">€{{ number_format($character->cash, 0, ',', '.') }}</strong></span>
            </div>
            <div class="bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 px-4 py-2.5 rounded-xl text-xs font-bold border border-emerald-200 dark:border-emerald-800/50 shadow-xs flex items-center gap-2">
                <i class="fa-solid fa-bolt text-emerald-500"></i>
                <span>Energie: <strong class="text-slate-900 dark:text-white font-mono" x-text="energy + '/{{ $character->max_energy }}'">{{ $character->energy }}/{{ $character->max_energy }}</strong></span>
            </div>
        </div>
    </div>

    <!-- Armpjedruk Arena Promo Banner -->
    <div class="relative overflow-hidden bg-white dark:bg-slate-900 border border-amber-200 dark:border-amber-800/60 rounded-2xl p-5 shadow-xs flex flex-col sm:flex-row items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-amber-50 dark:bg-amber-950/60 border border-amber-200 dark:border-amber-800 text-amber-600 dark:text-amber-400 flex items-center justify-center text-2xl shrink-0 shadow-2xs">
                <i class="fa-solid fa-hand-back-fist"></i>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <span class="text-xs font-black uppercase tracking-wider bg-amber-50 dark:bg-amber-950/80 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800/60 px-2 py-0.5 rounded-full">Nieuw Duel</span>
                    <h3 class="font-extrabold text-base text-slate-900 dark:text-white">De Illegale Armpjedruk Arena</h3>
                </div>
                <p class="text-xs text-slate-600 dark:text-slate-400 mt-0.5">
                    Test je getrainde Kracht & Snelheid aan de armpjedruktafel. Versla 4 beruchte NPC's of daag medespelers uit voor geldpotten tot €100.000!
                </p>
            </div>
        </div>
        <a href="{{ route('armwrestling.index') }}" class="shrink-0 bg-amber-500 hover:bg-amber-400 text-slate-950 font-extrabold text-xs px-5 py-2.5 rounded-xl transition shadow-xs flex items-center gap-2 cursor-pointer active:scale-95">
            <span>Betreed de Arena</span>
            <i class="fa-solid fa-arrow-right text-[11px]"></i>
        </a>
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
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xs overflow-hidden flex flex-col justify-between transition-all duration-300"
             :class="{ 'ring-2 ring-purple-500 shadow-lg scale-[1.01] bg-purple-50/10': flashingStat === '{{ $key }}' }">
            
            <div class="p-5 space-y-3">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-base font-black text-slate-900 dark:text-white flex items-center gap-1.5">
                            <i class="fa-solid {{ $icon }} text-sm"></i>
                            <span>{{ $info['name'] }}</span>
                        </h2>
                        <span class="text-[11px] font-bold text-purple-600 dark:text-purple-400">
                            <i class="fa-solid fa-user-ninja mr-1"></i>Trainer: {{ $info['trainer'] }}
                        </span>
                    </div>
                    <!-- Live Stat Counter Badge with Pop Animation -->
                    <div class="w-12 h-12 rounded-xl bg-purple-50 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 border border-purple-200 dark:border-purple-800/50 font-black text-lg flex items-center justify-center shadow-xs transition-transform duration-300"
                         :class="{ 'scale-125 bg-purple-600 text-white dark:bg-purple-500': flashingStat === '{{ $key }}' }">
                        <span x-text="stats['{{ $key }}']">{{ $character->{$key} }}</span>
                    </div>
                </div>

                <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed bg-slate-50 dark:bg-slate-800/50 p-2.5 rounded-xl border border-slate-100 dark:border-slate-800 min-h-[48px]">
                    {{ $info['desc'] }}
                </p>

                <!-- Costs & Benefits Bar -->
                <div class="flex items-center justify-between text-xs font-semibold pt-1">
                    <span class="text-yellow-700 dark:text-yellow-400 flex items-center gap-1">
                        <i class="fa-solid fa-bolt text-yellow-500 text-[10px]"></i> 10 Energie
                    </span>
                    <span class="text-amber-700 dark:text-amber-400 font-mono flex items-center gap-1">
                        <i class="fa-solid fa-coins text-amber-500 text-[10px]"></i>
                        <span x-text="'€' + formatNumber(costs['{{ $key }}'])">€{{ (int) max(20, round(15 + ($character->{$key} * 3.5))) }}</span>
                    </span>
                    <span class="text-purple-700 dark:text-purple-300 font-bold">+1 {{ $info['name'] }}</span>
                </div>
            </div>

            <!-- Action Buttons Bar (In-Place Async, No jumping!) -->
            <div class="px-5 py-3 bg-slate-50 dark:bg-slate-800/40 border-t border-slate-200 dark:border-slate-800 flex items-center gap-2">
                <!-- Standard +1 Train Button -->
                <button type="button" 
                        @click="train('{{ $key }}', 1)" 
                        :disabled="isTraining || isImprisoned || cash < costs['{{ $key }}'] || energy < 10" 
                        class="flex-1 bg-purple-600 hover:bg-purple-700 disabled:opacity-40 disabled:cursor-not-allowed text-white font-bold py-2.5 px-3 rounded-xl text-xs shadow-xs transition flex items-center justify-center gap-1.5 cursor-pointer active:scale-95">
                    <i class="fa-solid fa-dumbbell" :class="{ 'animate-spin': isTraining && activeStat === '{{ $key }}' }"></i>
                    <span>Trainen (+1)</span>
                </button>

                <!-- Batch +5 Train Quick Upgrade -->
                <button type="button" 
                        @click="train('{{ $key }}', 5)" 
                        :disabled="isTraining || isImprisoned || cash < (costs['{{ $key }}'] * 5) || energy < 50" 
                        title="Train 5x in 1 klik zonder schermverspringing!"
                        class="px-3 py-2.5 rounded-xl bg-slate-200 dark:bg-slate-700 hover:bg-slate-300 dark:hover:bg-slate-600 text-slate-800 dark:text-slate-200 font-black text-xs transition disabled:opacity-30 disabled:cursor-not-allowed cursor-pointer active:scale-95">
                    +5x
                </button>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Recent Logs -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm p-5 sm:p-6">
        <h3 class="text-xs font-bold uppercase text-slate-800 dark:text-slate-200 tracking-wider flex items-center gap-2 mb-3">
            <i class="fa-solid fa-clock-rotate-left text-purple-600 dark:text-purple-400"></i>
            <span>Recente Trainingssessies</span>
        </h3>
        <div class="divide-y divide-slate-100 dark:divide-slate-800 text-xs">
            <template x-for="log in liveLogs" :key="log.id">
                <div class="py-2.5 flex items-center justify-between animate-fade-in">
                    <div>
                        <span class="font-bold text-slate-800 dark:text-slate-200" x-text="log.stat_name"></span> verhoogd met 
                        <span class="font-bold text-purple-600 dark:text-purple-400" x-text="'+' + log.stat_gain"></span> door workout met 
                        <span class="italic text-slate-600 dark:text-slate-400" x-text="log.trainer"></span>.
                    </div>
                    <div class="text-[11px] text-slate-400 font-mono">Zojuist</div>
                </div>
            </template>

            @forelse($recentLogs as $log)
            <div class="py-2.5 flex items-center justify-between">
                <div>
                    <span class="font-bold text-slate-800 dark:text-slate-200">{{ $stats[$log->stat]['name'] ?? $log->stat }}</span> verhoogd met 
                    <span class="font-bold text-purple-600 dark:text-purple-400">+{{ $log->stat_gain }}</span> door workout met 
                    <span class="italic text-slate-600 dark:text-slate-400">{{ $log->trainer }}</span>.
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
        cash: config.cash,
        energy: config.energy,
        isImprisoned: config.isImprisoned,
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
        async train(statKey, times = 1) {
            if (this.isTraining) return;
            if (this.isImprisoned) {
                this.showToast('Je zit in de isoleercel en kunt nu niet trainen!', true);
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
                        times: times
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
                    trainer: 'Jouw Trainer'
                });

                // Update site topbar realtime tracker directly without an extra HTTP request
                if (typeof window.tbsApplyStats === 'function') {
                    window.tbsApplyStats({
                        cash: data.cash,
                        energy: data.energy
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
