@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- Top Hero Banner -->
    <div class="rounded-2xl shadow-xs p-6 sm:p-8 text-slate-800 relative overflow-hidden bg-white border border-slate-200">
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <div class="inline-flex items-center gap-2 bg-purple-50 text-purple-700 text-xs font-bold px-3 py-1 rounded-full border border-purple-200 mb-2">
                    <i class="fa-solid fa-lock text-purple-600"></i> De Gevangenis & Isoleercel
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900">Gevangenis</h1>
                <p class="text-slate-500 text-xs sm:text-sm mt-1 max-w-xl leading-relaxed">
                    Patiënten die betrapt zijn bij illegale acties of mislukte uitbraken zitten hier vast. Breek mede-patiënten uit voor respect en aanzien, of koop jezelf direct vrij met 10 Gouden Sterren ⭐!
                </p>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <span class="px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs font-bold text-amber-700 flex items-center gap-1.5 shadow-2xs">
                    <i class="fa-solid fa-star text-amber-500"></i> {{ $character->stars ?? 0 }} Sterren
                </span>
                <span class="px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs font-bold text-emerald-700 flex items-center gap-1.5 shadow-2xs">
                    <i class="fa-solid fa-bolt text-emerald-600"></i> {{ $character->energy }} / {{ $character->max_energy }} Energie
                </span>
            </div>
        </div>
    </div>

    <!-- IF PLAYER IS IN JAIL: BUYOUT & ESCAPE PROMPT -->
    @if($isImprisoned)
    <div x-data="{ 
            sec: {{ $remainingSeconds }},
            reloaded: false,
            attempted: {{ ($character->prison_escape_attempted ?? false) ? 'true' : 'false' }},
            isEscaping: false,
            formatTime(s) {
                if (s <= 0) return '00:00';
                const m = Math.floor(s / 60);
                const remainder = s % 60;
                return (m < 10 ? '0' : '') + m + ':' + (remainder < 10 ? '0' : '') + remainder;
            },
            reloadSite() {
                if (this.reloaded) return;
                this.reloaded = true;
                this.sec = 0;
                if (window.Turbo) {
                    window.Turbo.clearCache();
                    window.Turbo.visit(window.location.href, { action: 'replace' });
                } else {
                    window.location.reload();
                }
            },
            async attemptEscape() {
                if (this.attempted || this.isEscaping) return;
                this.isEscaping = true;
                try {
                    const res = await fetch('{{ route('prison.escape') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    const data = await res.json();
                    if (data.success) {
                        if (typeof window.tbsToast === 'function') {
                            window.tbsToast(data.message, 'success');
                        }
                        setTimeout(() => {
                            window.location.href = data.redirect || '{{ route('dashboard') }}';
                        }, 1200);
                    } else {
                        this.attempted = true;
                        if (data.remaining_seconds !== undefined) {
                            this.sec = data.remaining_seconds;
                        } else {
                            this.sec += 90;
                        }
                        if (typeof window.tbsToast === 'function') {
                            window.tbsToast(data.message, 'error');
                        }
                    }
                } catch (err) {
                    if (typeof window.tbsToast === 'function') {
                        window.tbsToast('Er is een fout opgetreden bij de ontsnappingspoging.', 'error');
                    }
                } finally {
                    this.isEscaping = false;
                }
            },
            init() {
                if (this.sec <= 0) {
                    this.reloadSite();
                    return;
                }
                const timer = setInterval(() => {
                    this.sec--;
                    if (this.sec <= 0) {
                        clearInterval(timer);
                        this.reloadSite();
                    }
                }, 1000);
            }
         }" 
         x-show="sec > 0"
         class="bg-white dark:bg-slate-900 border-2 border-rose-300 dark:border-rose-900/80 rounded-2xl p-4 sm:p-5 shadow-sm space-y-4 relative overflow-hidden">
        
        <!-- Ambient Background Glow -->
        <div class="absolute -right-12 -top-12 w-48 h-48 bg-rose-500/10 rounded-full blur-2xl pointer-events-none"></div>

        <!-- Header Row: Icon, Title, Countdown Pill & Reason (Full width) -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 relative z-10">
            <div class="flex items-start gap-3 min-w-0 flex-1">
                <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-xl bg-rose-100 dark:bg-rose-950/80 border border-rose-300 dark:border-rose-800 text-rose-600 dark:text-rose-400 flex items-center justify-center text-lg sm:text-xl shrink-0 shadow-xs mt-0.5">
                    <i class="fa-solid fa-lock"></i>
                </div>
                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-2 flex-wrap">
                        <h3 class="text-sm sm:text-base font-black text-slate-900 dark:text-white flex items-center gap-1.5">
                            <i class="fa-solid fa-triangle-exclamation text-rose-500 text-xs"></i>
                            <span>Je zit vast in de Isoleercel</span>
                        </h3>
                    </div>
                    <p class="text-xs text-slate-600 dark:text-slate-300 font-medium mt-1 leading-relaxed">
                        Reden: <span class="text-rose-600 dark:text-rose-400 font-bold">{{ $character->prison_reason ?? 'Wangedrag in de kliniek.' }}</span>
                    </p>
                </div>
            </div>

            <!-- Live Countdown Pill -->
            <div class="shrink-0 self-start sm:self-auto">
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-rose-100 dark:bg-rose-950/80 text-rose-700 dark:text-rose-300 text-xs font-mono font-black border border-rose-300 dark:border-rose-800 shadow-2xs whitespace-nowrap">
                    <i class="fa-solid fa-stopwatch text-rose-500 text-xs animate-pulse"></i>
                    <span x-text="formatTime(sec) + ' (' + sec + 's)'">{{ gmdate('i:s', $remainingSeconds) }} ({{ $remainingSeconds }}s)</span>
                </span>
            </div>
        </div>

        <!-- Actions Row: Responsive 2-column Grid (100% on mobile, 50/50 on sm+) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 pt-3 border-t border-rose-100 dark:border-rose-900/40 relative z-10">
            <!-- Watch Ad Button -->
            @php
                $reqPrisonAds = $character ? $character->getRequiredPrisonAds() : 1;
                $currPrisonAds = $character ? (int) ($character->ad_prison_progress ?? 0) : 0;
            @endphp
            <form action="{{ route('shop.buy-star-item') }}" method="POST" class="w-full">
                @csrf
                <input type="hidden" name="item_type" value="prison_escape">
                <button type="submit" 
                        class="btn-star-gold w-full py-2.5 px-4 rounded-xl active:scale-95 text-slate-900 dark:text-white font-extrabold text-xs uppercase tracking-wider transition shadow-sm hover:shadow-md cursor-pointer flex items-center justify-between gap-2"
                        title="Direct vrijkomen voor 10 Gouden Sterren ⭐">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-star text-xs"></i>
                        <span>Vrijkopen</span>
                    </div>
                    <span class="px-2 py-0.5 rounded-md bg-amber-400/30 text-[10px] font-black text-slate-900 dark:text-white border border-amber-300/40">10 Sterren ⭐</span>
                </button>
            </form>

            <!-- Escape Button -->
            <template x-if="!attempted">
                <button type="button" 
                        @click="attemptEscape()"
                        :disabled="isEscaping"
                        class="w-full py-2.5 px-4 rounded-xl bg-rose-600 hover:bg-rose-700 active:scale-95 text-white font-extrabold text-xs transition shadow-sm hover:shadow-md cursor-pointer flex items-center justify-between gap-2 disabled:opacity-50">
                    <div class="flex items-center gap-2">
                        <template x-if="isEscaping">
                            <i class="fa-solid fa-circle-notch fa-spin text-white text-xs"></i>
                        </template>
                        <template x-if="!isEscaping">
                            <i class="fa-solid fa-person-running text-white text-xs"></i>
                        </template>
                        <span x-text="isEscaping ? 'Ontsnappen...' : 'Ontsnappingspoging'"></span>
                    </div>
                    <span class="px-2 py-0.5 rounded-md bg-rose-900/80 text-[10px] font-black text-rose-200">1x mogelijk</span>
                </button>
            </template>

            <template x-if="attempted">
                <button type="button" 
                        disabled
                        class="w-full py-2.5 px-4 rounded-xl bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-400 dark:text-slate-500 font-bold text-xs flex items-center justify-between gap-2 cursor-not-allowed opacity-80"
                        title="Je hebt je ontsnappingspoging voor deze straf al verbruikt. Koop jezelf vrij met 10 Gouden Sterren om alsnog direct vrijgelaten te worden!">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-lock text-slate-400 dark:text-slate-500 text-xs"></i>
                        <span>Poging Verbruikt</span>
                    </div>
                    <span class="px-2 py-0.5 rounded-md bg-amber-500/10 text-[10px] font-semibold text-amber-500 border border-amber-500/20">Vrijkopen ⭐</span>
                </button>
            </template>
        </div>
    </div>
    @endif

    <!-- MAIN TWO COLUMN PRISON GRID -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

        <!-- Left Column: Inmates Table Card -->
        <div class="lg:col-span-8 bg-white border border-slate-200 rounded-xl p-6 shadow-xs space-y-4"
             x-data="{
                inmates: [
                    @foreach($jailList as $inmate)
                    {
                        id: {{ $inmate['id'] }},
                        name: @js($inmate['name']),
                        reason: @js($inmate['reason']),
                        avatar: @js($inmate['avatar']),
                        chance: {{ (int) $inmate['chance'] }},
                        profileUrl: @js(route('profile.show', $inmate['name'])),
                        breakoutUrl: @js(route('prison.breakout', $inmate['id'])),
                        remainingSeconds: {{ (int) $inmate['remaining_seconds'] }}
                    },
                    @endforeach
                ],
                formatTime(sec) {
                    if (sec <= 0) return '00:00';
                    const m = Math.floor(sec / 60);
                    const s = sec % 60;
                    return (m < 10 ? '0' : '') + m + ':' + (s < 10 ? '0' : '') + s;
                },
                init() {
                    if (this.inmates.length > 0) {
                        const timer = setInterval(() => {
                            let hasActive = false;
                            this.inmates.forEach(inmate => {
                                if (inmate.remainingSeconds > 0) {
                                    inmate.remainingSeconds--;
                                    if (inmate.remainingSeconds > 0) {
                                        hasActive = true;
                                    }
                                }
                            });
                            if (!hasActive) {
                                clearInterval(timer);
                            }
                        }, 1000);
                    }
                },
                get activeInmates() {
                    return this.inmates.filter(i => i.remainingSeconds > 0);
                }
             }">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2 text-sm font-extrabold text-slate-900 uppercase tracking-wide">
                    <i class="fa-solid fa-lock text-purple-600"></i>
                    <span>Gevangenen in de Isoleercel</span>
                    <span class="text-xs font-bold text-purple-700 bg-purple-50 px-2.5 py-0.5 rounded-full border border-purple-200" 
                          x-text="activeInmates.length">
                        {{ count($jailList) }}
                    </span>
                </div>
                <span class="text-xs font-bold text-slate-400">
                    Kost 15 Energie per uitbraak
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400 border-b border-slate-100 pb-2">
                            <th class="py-3 px-3">PATIËNT</th>
                            <th class="py-3 px-3 text-center">TIJD</th>
                            <th class="py-3 px-3 text-center">KANS</th>
                            <th class="py-3 px-3 text-right">ACTIE</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        <template x-for="inmate in activeInmates" :key="inmate.id">
                            <tr class="hover:bg-slate-50 transition-colors">
                                <!-- Inmate Avatar & Name/Reason -->
                                <td class="py-3 px-3">
                                    <div class="flex items-center gap-3">
                                        <img :src="inmate.avatar" :alt="inmate.name" 
                                             class="w-9 h-9 rounded-full object-cover border border-slate-200 shadow-xs shrink-0">
                                        <div>
                                            <div class="font-extrabold text-slate-900 text-xs leading-tight">
                                                <a :href="inmate.profileUrl" class="hover:text-purple-600 transition" x-text="inmate.name"></a>
                                            </div>
                                            <div class="text-[11px] text-slate-400 truncate max-w-[200px] sm:max-w-xs" x-text="inmate.reason"></div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Time Countdown -->
                                <td class="py-3 px-3 text-center font-mono font-bold text-xs text-slate-700">
                                    <span class="px-2 py-0.5 rounded-md bg-slate-100 border border-slate-200 font-bold" x-text="formatTime(inmate.remainingSeconds)"></span>
                                </td>

                                <!-- Chance -->
                                <td class="py-3 px-3 text-center font-bold text-xs text-purple-700">
                                    <span x-text="inmate.chance + '%'"></span>
                                </td>

                                <!-- Action Button: Breek uit -->
                                <td class="py-3 px-3 text-right">
                                    <form :action="inmate.breakoutUrl" method="POST">
                                        @csrf
                                        <button type="submit" 
                                                {{ $character->isImprisoned() || $character->energy < 15 ? 'disabled' : '' }}
                                                class="px-3.5 py-1.5 rounded-lg bg-purple-600 hover:bg-purple-700 disabled:opacity-40 disabled:cursor-not-allowed text-white font-bold text-xs uppercase tracking-wider transition shadow-xs cursor-pointer inline-flex items-center gap-1.5">
                                            <i class="fa-solid fa-unlock text-[10px]"></i>
                                            <span>Breek uit</span>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        </template>

                        <tr x-show="activeInmates.length === 0">
                            <td colspan="4" class="py-10 text-center">
                                <div class="flex flex-col items-center justify-center gap-2 text-slate-400">
                                    <div class="w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center text-slate-400 text-xl">
                                        <i class="fa-solid fa-lock-open"></i>
                                    </div>
                                    <span class="font-bold text-slate-700 text-xs">De isoleercel is momenteel leeg!</span>
                                    <span class="text-[11px] text-slate-400 max-w-sm">Er zitten op dit moment geen medepatiënten vast. Zodra een speler betrapt wordt bij een diefstal of mislukte uitbraak, verschijnt die hier!</span>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Right Column: Breakout Stats & Top Breakers -->
        <div class="lg:col-span-4 space-y-6">

            <!-- Card 1: Stats & Aanzien regels -->
            <div class="bg-white border border-slate-200 rounded-xl p-6 shadow-xs space-y-4">
                <div class="text-xs font-extrabold uppercase tracking-wide text-slate-900 flex items-center gap-2 border-b border-slate-100 pb-2.5">
                    <i class="fa-solid fa-fire text-purple-600"></i>
                    <span>Uitbraak Statistieken</span>
                </div>
                <p class="text-xs text-slate-500 leading-relaxed font-medium">
                    Help mede-patiënten ontsnappen en verdien aanzien! Per succesvolle uitbraak scoor je <strong>0.1 respect & aanzien</strong>.
                </p>

                <div class="pt-2 space-y-2.5 text-xs">
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500 font-medium flex items-center gap-2">
                            <i class="fa-solid fa-link-slash text-purple-600 text-xs"></i>
                            <span>Uitbraken Vandaag:</span>
                        </span>
                        <div class="text-right">
                            <span class="font-extrabold text-slate-900">{{ $stats['escapes_today'] }}</span>
                            <span class="text-slate-400 text-[10px] ml-1">Pogingen: {{ $stats['attempts_today'] }}</span>
                        </div>
                    </div>

                    <div class="flex items-center justify-between">
                        <span class="text-slate-500 font-medium flex items-center gap-2">
                            <i class="fa-solid fa-percent text-purple-600 text-xs"></i>
                            <span>Uitbreek XP:</span>
                        </span>
                        <span class="font-extrabold text-slate-900">{{ $stats['breakout_xp'] }}</span>
                    </div>

                    <div class="flex items-center justify-between">
                        <span class="text-slate-500 font-medium flex items-center gap-2">
                            <i class="fa-solid fa-star text-amber-500 text-xs"></i>
                            <span>Aanzien:</span>
                        </span>
                        <span class="font-extrabold text-purple-700 font-mono">{{ $stats['slay'] }}</span>
                    </div>

                    <div class="flex items-center justify-between pt-2 border-t border-slate-100 text-xs">
                        <span class="text-slate-400 flex items-center gap-1.5">
                            <i class="fa-solid fa-calendar-days text-slate-400"></i>
                            <span>Beste dag:</span>
                        </span>
                        <span class="font-bold text-slate-700">
                            {{ $stats['best_day_date'] }} ({{ $stats['best_day_count'] }}x)
                        </span>
                    </div>
                </div>
            </div>

            <!-- Card 2: Uitbrekers van de dag -->
            <div class="bg-white border border-slate-200 rounded-xl p-6 shadow-xs space-y-4">
                <div class="flex items-center gap-2 text-xs font-extrabold uppercase tracking-wider text-slate-900 border-b border-slate-100 pb-2.5">
                    <i class="fa-solid fa-chart-line text-purple-600"></i>
                    <span>Top Uitbrekers Vandaag</span>
                </div>

                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400 border-b border-slate-100 pb-2">
                            <th class="py-2">PATIËNT</th>
                            <th class="py-2 text-right">UITBRAKEN</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($topBreakers as $b)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="py-2.5 flex items-center gap-2.5">
                                <img src="{{ $b->avatar_url }}" alt="{{ $b->name }}" class="w-7 h-7 rounded-full object-cover border border-slate-200 shadow-2xs">
                                <a href="{{ route('profile.show', $b->name) }}" class="font-bold text-slate-800 hover:text-purple-600 transition truncate max-w-[140px]">
                                    {{ $b->name }}
                                </a>
                            </td>
                            <td class="py-2.5 text-right font-mono font-bold text-purple-700">
                                {{ $b->prison_escapes }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="2" class="py-4 text-center text-slate-400 text-xs italic">
                                Nog geen uitbraken geregistreerd.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>

    </div>

    <!-- Bottom Podium Ranglijst -->
    <div class="bg-white border border-slate-200 rounded-xl p-6 shadow-xs space-y-5">
        <div class="flex items-center gap-2 text-xs font-extrabold uppercase tracking-wider text-slate-900 border-b border-slate-100 pb-3">
            <i class="fa-solid fa-trophy text-amber-500"></i>
            <span>RANGLIJST TOP PATIËNTEN</span>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-5 lg:grid-cols-10 gap-3 pt-2">
            @foreach($topRankings ?? [] as $index => $player)
            <div class="flex flex-col items-center text-center group">
                <div class="relative mb-2">
                    <img src="{{ $player->avatar_url }}" alt="{{ $player->name }}" 
                         class="w-14 h-14 sm:w-16 sm:h-16 rounded-full object-cover border-2 {{ $index === 0 ? 'border-amber-400 ring-2 ring-amber-400/30' : ($index === 1 ? 'border-purple-400' : 'border-slate-200') }} group-hover:scale-105 transition-transform shadow-xs">
                    
                    <span class="absolute -top-1 -left-1 w-6 h-6 rounded-full {{ $index === 0 ? 'bg-amber-400 text-slate-950 font-black' : ($index < 3 ? 'bg-purple-600 text-white font-bold' : 'bg-slate-800 text-white font-bold') }} text-xs flex items-center justify-center shadow">
                        {{ $index + 1 }}
                    </span>

                    <span class="absolute -bottom-1 -right-1 w-4 h-4 rounded-full bg-emerald-500 text-white text-[9px] flex items-center justify-center">
                        <i class="fa-solid fa-check"></i>
                    </span>
                </div>

                <a href="{{ route('profile.show', $player->name) }}" class="text-xs font-bold text-slate-900 group-hover:text-purple-600 transition truncate w-full block">
                    {{ $player->name }}
                </a>
                <span class="text-[10px] font-bold text-slate-400">
                    {{ $player->respect >= 1000000 ? number_format($player->respect / 1000000, 2) . 'M' : ($player->respect >= 1000 ? number_format($player->respect / 1000, 1) . 'K' : $player->respect) }}
                </span>
            </div>
            @endforeach
        </div>
    </div>

</div>
@endsection
