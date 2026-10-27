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
                    Patiënten die betrapt zijn bij illegale acties of mislukte uitbraken zitten hier vast. Breek mede-patiënten uit voor respect en aanzien, of bekijk een sponsoradvertentie om direct vrij te komen!
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
         class="bg-rose-50 border border-rose-200 rounded-xl p-5 text-rose-950 shadow-xs flex flex-col md:flex-row items-center justify-between gap-4">
        <div class="flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-xl bg-rose-100 border border-rose-200 text-rose-600 flex items-center justify-center text-xl shrink-0 font-bold">
                <i class="fa-solid fa-lock"></i>
            </div>
            <div>
                <h3 class="text-sm font-extrabold text-rose-950">Je zit vast in de Isoleercel</h3>
                <p class="text-xs text-rose-800 font-medium mt-0.5">
                    Resterende straftijd: <strong class="text-rose-900 font-mono text-xs" x-text="formatTime(sec) + ' (' + sec + 's)'">{{ gmdate('i:s', $remainingSeconds) }} ({{ $remainingSeconds }}s)</strong>. Reden: <span class="text-rose-950 font-bold">{{ $character->prison_reason ?? 'Wangedrag in de kliniek.' }}</span>
                </p>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2 w-full md:w-auto">
            <!-- Watch Ad to reduce sentence and gain instant release -->
            @php
                $reqPrisonAds = $character ? $character->getRequiredPrisonAds() : 1;
                $currPrisonAds = $character ? (int) ($character->ad_prison_progress ?? 0) : 0;
                $leftPrisonAds = max(1, $reqPrisonAds - $currPrisonAds);
            @endphp
            <button type="button" 
                    onclick="window.tbsOpenSponsorAd('prison', () => { window.location.reload(); })" 
                    class="flex-1 md:flex-initial px-4 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-700 active:scale-95 text-white font-black text-xs uppercase tracking-wider transition shadow-xs cursor-pointer flex items-center justify-center gap-2"
                    title="Elke ad vermindert je straftijd met 3 minuten. Bij {{ $reqPrisonAds }} ads word je direct vrijgelaten!">
                <i class="fa-solid fa-tv text-white text-[11px]"></i>
                <span>Kijk Ad ({{ $currPrisonAds }}/{{ $reqPrisonAds }} Ads Vrijlating)</span>
            </button>


            <!-- Regular escape attempt (max 1x per prison sentence) -->
            @if(! ($character->prison_escape_attempted ?? false))
            <form action="{{ route('prison.escape') }}" method="POST" class="flex-1 md:flex-initial">
                @csrf
                <button type="submit" 
                        class="w-full px-4 py-2.5 rounded-xl bg-white hover:bg-rose-100/60 border border-rose-300 text-rose-900 font-bold text-xs transition cursor-pointer flex items-center justify-center gap-1.5 shadow-2xs">
                    <i class="fa-solid fa-person-running text-rose-600"></i>
                    <span>Ontsnappingspoging (1x mogelijk)</span>
                </button>
            </form>
            @else
            <div class="flex-1 md:flex-initial px-4 py-2.5 rounded-xl bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-400 font-bold text-xs flex items-center justify-center gap-1.5 cursor-not-allowed"
                 title="Je hebt je ontsnappingspoging voor deze straf al verbruikt. Bekijk ads om alsnog direct vrijgelaten te worden!">
                <i class="fa-solid fa-ban text-rose-500"></i>
                <span>Ontsnappingspoging Verbruikt (Kijk Ads)</span>
            </div>
            @endif
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
