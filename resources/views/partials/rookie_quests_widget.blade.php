<!-- ROOKIE OVERLEVINGSTAKEN & BELONINGSSPOOR -->
@if(! ($rookieData['grand_claimed'] ?? false))
<div x-data="{
    collapsed: localStorage.getItem('tbs_rookie_collapsed') === '1',
    isClaiming: null,
    async claim(key) {
        if (this.isClaiming) return;
        this.isClaiming = key;
        try {
            const res = await fetch('{{ url('/rookie/claim') }}/' + key, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=\'csrf-token\']')?.content || '{{ csrf_token() }}'
                }
            });
            const data = await res.json();
            if (data.success) {
                if (window.tbsShowToast) {
                    window.tbsShowToast(data.message, 'success');
                } else {
                    alert(data.message);
                }
                setTimeout(() => window.location.reload(), 800);
            } else {
                alert(data.message || 'Kon beloning niet claimen.');
            }
        } catch (e) {
            alert('Er is een fout opgetreden bij het claimen.');
        } finally {
            this.isClaiming = null;
        }
    },
    toggleCollapse() {
        this.collapsed = !this.collapsed;
        localStorage.setItem('tbs_rookie_collapsed', this.collapsed ? '1' : '0');
    }
}" class="relative overflow-hidden rounded-2xl bg-gradient-to-b from-white via-white to-purple-50/40 dark:from-slate-900 dark:via-slate-900 dark:to-purple-950/20 border-2 border-purple-300 dark:border-purple-800/80 shadow-md transition-all">

    <!-- Top Glow & Header -->
    <div class="p-5 sm:p-6 border-b border-purple-100 dark:border-purple-900/40">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-start gap-3.5">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-amber-400 to-amber-600 text-slate-950 flex items-center justify-center text-2xl shrink-0 shadow-md shadow-amber-500/20">
                    <i class="fa-solid fa-graduation-cap"></i>
                </div>
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-amber-100 dark:bg-amber-950/80 text-amber-900 dark:text-amber-300 border border-amber-300 dark:border-amber-800">
                            <i class="fa-solid fa-star text-[9px] text-amber-500"></i>
                            Rookie Beloningsspoor
                        </span>
                        <span class="text-xs font-black text-purple-700 dark:text-purple-300">
                            {{ $rookieData['completed_count'] }} van de {{ $rookieData['total_count'] }} Voltooid
                        </span>
                    </div>
                    <h2 class="text-base sm:text-lg font-black text-slate-900 dark:text-white tracking-tight mt-0.5">
                        Kliniek Overlevingstaken voor Beginners
                    </h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400 font-medium">
                        Leer de basis van het bajesleven, claim directe beloningen én ontgrendel de Hoofdprijs: <strong>€5.000 contant</strong> + de titel <strong>Kliniek Overlever</strong>.
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2 self-start md:self-center shrink-0">
                <button type="button" 
                        @click="window.dispatchEvent(new CustomEvent('tbs-start-intake-tour'))"
                        class="px-3 py-2 rounded-xl bg-purple-50 hover:bg-purple-100 dark:bg-purple-950/60 dark:hover:bg-purple-900/60 text-purple-700 dark:text-purple-300 text-xs font-bold transition flex items-center gap-1.5 border border-purple-200 dark:border-purple-800 cursor-pointer shadow-2xs">
                    <i class="fa-solid fa-compass"></i>
                    <span>Start Rondleiding</span>
                </button>
                <button type="button" 
                        @click="toggleCollapse()" 
                        class="w-9 h-9 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 flex items-center justify-center text-xs transition cursor-pointer"
                        :title="collapsed ? 'Klap uit' : 'Klap in'">
                    <i class="fa-solid" :class="collapsed ? 'fa-chevron-down' : 'fa-chevron-up'"></i>
                </button>
            </div>
        </div>

        <!-- Progress Bar -->
        <div class="mt-4 pt-3 border-t border-purple-100/60 dark:border-purple-900/30 flex items-center gap-3">
            <div class="flex-1 h-3 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden border border-slate-200 dark:border-slate-700 p-0.5">
                <div class="h-full bg-gradient-to-r from-amber-400 via-purple-500 to-indigo-500 rounded-full transition-all duration-500" style="width: {{ $rookieData['progress_pct'] }}%"></div>
            </div>
            <span class="text-xs font-black text-slate-700 dark:text-slate-300 font-mono">{{ $rookieData['progress_pct'] }}%</span>
        </div>
    </div>

    <!-- Task List (Collapsible) -->
    <div x-show="!collapsed" x-collapse class="p-5 sm:p-6 space-y-3">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3.5">
            @foreach($rookieData['tasks'] as $key => $task)
            <div class="rounded-xl border p-4 transition-all duration-200 flex flex-col justify-between gap-3 {{ $task['is_claimed'] ? 'bg-slate-50 dark:bg-slate-800/40 border-slate-200 dark:border-slate-800 opacity-80' : ($task['can_claim'] ? 'bg-amber-50/60 dark:bg-amber-950/30 border-amber-400 dark:border-amber-700 shadow-md ring-1 ring-amber-400/30' : 'bg-white dark:bg-slate-800/80 border-slate-200 dark:border-slate-700') }}">
                
                <div>
                    <div class="flex items-start justify-between gap-2 mb-2">
                        <div class="w-9 h-9 rounded-xl flex items-center justify-center text-sm shrink-0 {{ $task['is_claimed'] ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300' : ($task['can_claim'] ? 'bg-amber-100 text-amber-700 dark:bg-amber-900 dark:text-amber-200 animate-bounce' : 'bg-purple-100 text-purple-700 dark:bg-purple-950 dark:text-purple-300') }}">
                            <i class="fa-solid {{ $task['is_claimed'] ? 'fa-check' : $task['icon'] }}"></i>
                        </div>

                        <div>
                            @if($task['is_claimed'])
                                <span class="px-2 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-700">
                                    <i class="fa-solid fa-check text-[8px]"></i> Geclaimd
                                </span>
                            @elseif($task['can_claim'])
                                <span class="px-2 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider bg-amber-400 text-slate-950 animate-pulse font-mono">
                                    Klaar om te Claimen!
                                </span>
                            @else
                                <span class="px-2 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300">
                                    In Uitvoering
                                </span>
                            @endif
                        </div>
                    </div>

                    <h4 class="text-xs font-black text-slate-900 dark:text-white leading-tight">
                        {{ $task['title'] }}
                    </h4>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1 leading-snug">
                        {{ $task['desc'] }}
                    </p>
                </div>

                <div class="pt-2 border-t border-slate-100 dark:border-slate-700/60 flex items-center justify-between gap-2">
                    <div class="text-[10px] font-extrabold text-amber-600 dark:text-amber-400 flex items-center gap-1">
                        <i class="fa-solid fa-gift text-[9px]"></i>
                        <span>{{ $task['reward_label'] }}</span>
                    </div>

                    <div>
                        @if($task['can_claim'])
                            <button type="button" 
                                    @click="claim('{{ $key }}')" 
                                    :disabled="isClaiming === '{{ $key }}'"
                                    class="px-3 py-1.5 rounded-lg bg-amber-500 hover:bg-amber-400 active:scale-95 text-slate-950 font-black text-xs transition shadow-xs flex items-center gap-1 cursor-pointer">
                                <i class="fa-solid" :class="isClaiming === '{{ $key }}' ? 'fa-spinner fa-spin' : 'fa-award'"></i>
                                <span>Claim</span>
                            </button>
                        @elseif(! $task['is_claimed'])
                            <a href="{{ $task['route'] }}" 
                               class="px-2.5 py-1.5 rounded-lg bg-purple-600 hover:bg-purple-500 text-white font-extrabold text-[11px] transition flex items-center gap-1 shadow-2xs">
                                <span>{{ $task['route_label'] }}</span>
                                <i class="fa-solid fa-arrow-right text-[9px]"></i>
                            </a>
                        @endif
                    </div>
                </div>

            </div>
            @endforeach

            <!-- Grand Prize Banner Card -->
            <div class="rounded-xl border-2 border-dashed border-amber-400/80 dark:border-amber-500/60 bg-gradient-to-br from-amber-500/10 via-purple-500/10 to-transparent p-4 flex flex-col justify-between gap-3">
                <div>
                    <div class="flex items-center gap-2 mb-1.5">
                        <span class="w-7 h-7 rounded-lg bg-amber-500 text-slate-950 flex items-center justify-center text-xs font-black shadow-xs">
                            <i class="fa-solid fa-trophy"></i>
                        </span>
                        <h4 class="text-xs font-black text-amber-900 dark:text-amber-300 uppercase tracking-wide">
                            Grote Finale Beloning
                        </h4>
                    </div>
                    <p class="text-xs font-bold text-slate-800 dark:text-slate-200 leading-snug">
                        Claim alle 5 overlevingstaken en ontvang direct:
                    </p>
                    <ul class="text-[11px] text-slate-600 dark:text-slate-300 mt-1 space-y-0.5">
                        <li class="flex items-center gap-1.5"><i class="fa-solid fa-coins text-amber-500"></i> €5.000 contant startkapitaal</li>
                        <li class="flex items-center gap-1.5"><i class="fa-solid fa-star text-purple-500"></i> +50 Bonus XP</li>
                        <li class="flex items-center gap-1.5"><i class="fa-solid fa-certificate text-rose-500"></i> Exclusieve titel: "Kliniek Overlever"</li>
                    </ul>
                </div>
                <div class="text-[10px] font-black text-purple-700 dark:text-purple-300">
                    Wordt automatisch uitgereikt bij 5/5!
                </div>
            </div>
        </div>
    </div>

</div>
@endif
