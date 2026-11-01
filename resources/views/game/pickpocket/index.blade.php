@extends('layouts.app')

@section('title', 'Vingervlugheid & Observatie - TBS Kliniek')

@section('content')
@php
    $ppCooldown = $character->cooldownRemaining('pickpocket');
    $currentAlert = (int) ($alertness ?? 20);
    $scoutActive = (bool) ($isScouted ?? false);
    $currentAlertMod = (int) ($alertMod ?? 6);
@endphp

<div class="space-y-6" x-data="{
    seconds: {{ (int) $ppCooldown }},
    selectedTech: 'schaduw',
    alertMod: {{ $currentAlertMod }},
    techMap: {
        schaduw: { name: 'Schaduwstap', rateMod: 8, cashMult: 0.90, xpLabel: '1.00x XP', summary: '+8% Trefkans • 0.90x Buit • +10% Alertheid' },
        afleiding: { name: 'Schijnbotsing', rateMod: 0, cashMult: 1.00, xpLabel: '+15% XP', summary: '+0% Trefkans • 1.00x Buit • +18% Alertheid' },
        snelle_greep: { name: 'Bliksemgreep', rateMod: -6, cashMult: 1.35, xpLabel: '+10% XP & +1 Gezag', summary: '-6% Trefkans • +35% Buit • +28% Alertheid' }
    },
    effectiveRate(baseRate) {
        const raw = baseRate + (this.techMap[this.selectedTech]?.rateMod || 0) + this.alertMod;
        return Math.max(5, Math.min(95, raw));
    },
    projectedCash(val) {
        const mult = this.techMap[this.selectedTech]?.cashMult || 1.0;
        return Math.round(val * mult).toLocaleString('nl-NL');
    },
    format(s) {
        if (s <= 0) return '00:00';
        const m = Math.floor(s / 60);
        const sec = Math.floor(s % 60);
        return (m < 10 ? '0' : '') + m + ':' + (sec < 10 ? '0' : '') + sec;
    },
    init() {
        if (this.seconds > 0) {
            const timer = setInterval(() => {
                if (this.seconds > 0) this.seconds--;
                if (this.seconds <= 0) clearInterval(timer);
            }, 1000);
        }
    }
}">

    <!-- Top Command Banner -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-6 sm:p-7 shadow-xs">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-5">
            <div class="space-y-2">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 text-xs font-black px-3 py-1 rounded-full border border-indigo-200/80 dark:border-indigo-800/70">
                        <i class="fa-solid fa-hand-sparkles"></i>
                        <span>Doelwit, Techniek &amp; Alertheid-Systeem</span>
                    </span>
                    @if($scoutActive)
                        <span class="inline-flex items-center gap-1.5 bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 text-xs font-black px-3 py-1 rounded-full border border-emerald-300/80 dark:border-emerald-700/70">
                            <i class="fa-solid fa-binoculars"></i>
                            <span>VERKENNING ACTIEF: +5% EXTRA KANS</span>
                        </span>
                    @endif
                </div>
                <h1 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight uppercase">
                    VINGERVLUGHEID &amp; <span class="text-indigo-600 dark:text-indigo-400">OBSERVATIE</span>
                </h1>
                <p class="text-slate-600 dark:text-slate-400 text-xs sm:text-sm max-w-2xl leading-relaxed">
                    Houd de <strong class="text-slate-900 dark:text-white">Vleugel-Alertheid</strong> laag door eerst de wachtpatronen te verkennen, kies daarna je <strong class="text-slate-900 dark:text-white">Vingervlugheid-Techniek</strong> en sla toe op een doelwit in het complex. Elke ingreep vereist 1 Bivakmuts.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2.5 shrink-0">
                <div class="px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-center">
                    <span class="text-[10px] font-black uppercase text-slate-400 block">Bivakmutsen</span>
                    <span class="text-sm font-black font-mono text-indigo-600 dark:text-indigo-400">
                        🎭 {{ $character->masks ?? 0 }}
                    </span>
                </div>
                <div class="px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-center">
                    <span class="text-[10px] font-black uppercase text-slate-400 block">Alertheid</span>
                    <span class="text-sm font-black font-mono {{ $currentAlert <= 25 ? 'text-emerald-600 dark:text-emerald-400' : ($currentAlert >= 70 ? 'text-rose-600 dark:text-rose-400' : 'text-amber-600 dark:text-amber-400') }}">
                        {{ $currentAlert }}%
                    </span>
                </div>
                <a href="{{ route('shop.index') }}"
                   class="px-4 py-3 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-black text-xs uppercase tracking-wider shadow-xs transition cursor-pointer flex items-center gap-1.5">
                    <i class="fa-solid fa-mask"></i> Bivakmutsen Halen
                </a>
            </div>
        </div>
    </div>

    <!-- Main 12-Column Tactical Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

        <!-- LEFT COLUMN (4 cols): Observation Meter, Technique Selector & Cooldown -->
        <div class="lg:col-span-4 space-y-6">

            <!-- Card 1: Observation & Alertness Meter -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-5 shadow-xs space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                    <div>
                        <span class="text-[10px] font-extrabold uppercase tracking-wider text-emerald-600 dark:text-emerald-400 block">
                            Stap 1: Omgevingsanalyse
                        </span>
                        <h2 class="text-sm font-black uppercase text-slate-900 dark:text-white">
                            Observatie &amp; Alertheid-Meter
                        </h2>
                    </div>
                    <span class="text-xs font-mono font-black px-2.5 py-1 rounded-full {{ $currentAlertMod >= 0 ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300' : 'bg-rose-50 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300' }}">
                        {{ $currentAlertMod >= 0 ? '+' . $currentAlertMod : $currentAlertMod }}% Kans
                    </span>
                </div>

                <!-- Alertness Bar -->
                <div class="space-y-1.5">
                    <div class="flex items-center justify-between text-xs font-bold">
                        <span class="text-slate-600 dark:text-slate-300">Wachtpost Waakzaamheid</span>
                        <span class="font-mono font-black {{ $currentAlert <= 25 ? 'text-emerald-600 dark:text-emerald-400' : ($currentAlert >= 70 ? 'text-rose-600 dark:text-rose-400' : 'text-amber-600 dark:text-amber-400') }}">
                            {{ $currentAlert }}%
                        </span>
                    </div>
                    <div class="w-full h-3 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden border border-slate-200/70 dark:border-slate-700">
                        <div class="h-full {{ $currentAlert <= 25 ? 'bg-emerald-500' : ($currentAlert >= 70 ? 'bg-rose-500' : 'bg-amber-500') }} transition-all duration-300"
                             style="width: {{ $currentAlert }}%"></div>
                    </div>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 leading-relaxed">
                        Bij een lage alertheid (&le;25%) krijg je <strong>+6% slaagkans</strong>. Loopt de spanning op (&ge;70%), dan daalt je kans met <strong>-8%</strong>.
                    </p>
                </div>

                <form method="POST" action="{{ route('pickpocket.observe') }}">
                    @csrf
                    <button type="submit"
                            @if($character->energy < 2 || $character->isImprisoned() || $character->isComatose()) disabled @endif
                            class="w-full py-2.5 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-700 disabled:bg-slate-200 disabled:dark:bg-slate-800 disabled:text-slate-400 disabled:cursor-not-allowed text-white font-black text-xs uppercase tracking-wider transition cursor-pointer flex items-center justify-center gap-2">
                        <i class="fa-solid fa-binoculars"></i>
                        <span>Wachtpatroon Observeren (2⚡ • -25% Alertheid)</span>
                    </button>
                </form>
            </div>

            <!-- Card 2: Technique Selector -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-5 shadow-xs space-y-4">
                <div class="border-b border-slate-100 dark:border-slate-800 pb-3">
                    <span class="text-[10px] font-extrabold uppercase tracking-wider text-indigo-600 dark:text-indigo-400 block">
                        Stap 2: Kies Handgreep
                    </span>
                    <h2 class="text-sm font-black uppercase text-slate-900 dark:text-white">
                        Vingervlugheid-Techniek
                    </h2>
                </div>

                <div class="space-y-2.5">
                    <button type="button"
                            @click="selectedTech = 'schaduw'"
                            :class="selectedTech === 'schaduw' ? 'border-emerald-500 bg-emerald-50/80 dark:bg-emerald-950/40 ring-1 ring-emerald-500/40' : 'border-slate-200/80 dark:border-slate-700 bg-slate-50/70 dark:bg-slate-800/50'"
                            class="w-full p-3.5 rounded-xl border text-left transition cursor-pointer space-y-1">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-black text-slate-900 dark:text-white">1. Schaduwstap</span>
                            <span class="text-[10px] font-mono font-black text-emerald-600 dark:text-emerald-400">+8% Slaagkans</span>
                        </div>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400">
                            Voorzichtige greep uit buitenste vakken (0.90x buit, minimale alertheid).
                        </p>
                    </button>

                    <button type="button"
                            @click="selectedTech = 'afleiding'"
                            :class="selectedTech === 'afleiding' ? 'border-indigo-500 bg-indigo-50/80 dark:bg-indigo-950/40 ring-1 ring-indigo-500/40' : 'border-slate-200/80 dark:border-slate-700 bg-slate-50/70 dark:bg-slate-800/50'"
                            class="w-full p-3.5 rounded-xl border text-left transition cursor-pointer space-y-1">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-black text-slate-900 dark:text-white">2. Schijnbotsing</span>
                            <span class="text-[10px] font-mono font-black text-indigo-600 dark:text-indigo-400">+15% Extra XP</span>
                        </div>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400">
                            Afleidingstruc in de looproute (1.00x standaard buit en +15% XP).
                        </p>
                    </button>

                    <button type="button"
                            @click="selectedTech = 'snelle_greep'"
                            :class="selectedTech === 'snelle_greep' ? 'border-amber-500 bg-amber-50/80 dark:bg-amber-950/40 ring-1 ring-amber-500/40' : 'border-slate-200/80 dark:border-slate-700 bg-slate-50/70 dark:bg-slate-800/50'"
                            class="w-full p-3.5 rounded-xl border text-left transition cursor-pointer space-y-1">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-black text-slate-900 dark:text-white">3. Bliksemgreep</span>
                            <span class="text-[10px] font-mono font-black text-amber-600 dark:text-amber-400">+35% Bonusbuit</span>
                        </div>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400">
                            Diepe binnenzak-roof (-6% kans, maar +35% zwart geld en +1 extra gezag).
                        </p>
                    </button>
                </div>
            </div>

            <!-- Card 3: Cooldown Status -->
            <div x-show="seconds > 0"
                 class="p-4 rounded-2xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200/80 dark:border-amber-800/70 space-y-3"
                 style="{{ $ppCooldown > 0 ? '' : 'display: none;' }}">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-black text-amber-950 dark:text-amber-200">Spannings-Afkoeling</span>
                    <span class="font-mono font-black text-sm text-amber-700 dark:text-amber-300" x-text="format(seconds)">
                        {{ gmdate('i:s', $ppCooldown) }}
                    </span>
                </div>
                <form method="POST" action="{{ route('cooldown.skip') }}">
                    @csrf
                    <input type="hidden" name="action" value="pickpocket">
                    <button type="submit"
                            @disabled($character->isImprisoned() || $character->isComatose() || ($character->stars ?? 0) < ($ppSkipCost ?? 10))
                            class="btn-star-gold w-full py-2 px-4 rounded-xl text-slate-900 dark:text-white text-xs font-black cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">
                        ⭐ Wachttijd Afkopen ({{ $ppSkipCost ?? 10 }} Sterren)
                    </button>
                </form>
            </div>
        </div>

        <!-- RIGHT COLUMN (8 cols): 6 Target & Location Dossiers -->
        <div class="lg:col-span-8 space-y-4">
            <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-5 sm:p-6 shadow-xs space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-100 dark:border-slate-800 pb-3">
                    <div>
                        <span class="text-[10px] font-extrabold uppercase tracking-wider text-purple-600 dark:text-purple-400 block">
                            Stap 3: Selecteer Doelwit &amp; Locatie
                        </span>
                        <h2 class="text-sm sm:text-base font-black uppercase text-slate-900 dark:text-white">
                            Doelwitten in het Complex
                        </h2>
                    </div>
                    <span class="text-xs font-mono font-bold text-indigo-600 dark:text-indigo-400"
                          x-text="'Actieve techniek: ' + techMap[selectedTech].name"></span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    @foreach($actions as $action)
                        @php
                            $hasMasks = ($character->masks ?? 0) >= 1;
                            $hasEnergy = $character->energy >= $action['energy'];
                            $otherBlocked = !$hasMasks || !$hasEnergy || $character->isImprisoned() || $character->isComatose();
                            $isDisabled = $otherBlocked || $ppCooldown > 0;
                        @endphp
                        <div class="p-4 rounded-2xl bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70 flex flex-col justify-between space-y-3.5">
                            <div class="space-y-2.5">
                                <div class="flex items-start justify-between gap-2">
                                    <div class="flex items-center gap-2.5">
                                        <span class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-base shrink-0">
                                            <i class="fa-solid {{ $action['icon'] }}"></i>
                                        </span>
                                        <div>
                                            <span class="text-[10px] font-black uppercase tracking-wider text-indigo-600 dark:text-indigo-400 block">
                                                Locatie: {{ $action['location'] ?? 'Wandelgang' }}
                                            </span>
                                            <h3 class="text-xs sm:text-sm font-black text-slate-900 dark:text-white">
                                                {{ $action['name'] }}
                                            </h3>
                                        </div>
                                    </div>
                                    <span class="text-[10px] font-mono font-black px-2 py-0.5 rounded bg-slate-200/70 dark:bg-slate-700 text-slate-700 dark:text-slate-200 shrink-0">
                                        {{ $action['energy'] }}⚡ • 1🎭
                                    </span>
                                </div>

                                <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                                    {{ $action['desc'] }}
                                </p>

                                <!-- Dynamic Effective Rate Bar -->
                                <div class="space-y-1">
                                    <div class="flex items-center justify-between text-[11px] font-bold">
                                        <span class="text-slate-500 dark:text-slate-400">Effectieve Slaagkans:</span>
                                        <span class="font-mono font-black text-indigo-600 dark:text-indigo-400"
                                              x-text="effectiveRate({{ (int) $action['rate'] }}) + '%'">
                                            {{ $action['rate'] }}%
                                        </span>
                                    </div>
                                    <div class="w-full h-2 bg-slate-200 dark:bg-slate-700 rounded-full overflow-hidden">
                                        <div class="h-full bg-indigo-600 transition-all duration-300"
                                             :style="'width: ' + effectiveRate({{ (int) $action['rate'] }}) + '%'"></div>
                                    </div>
                                </div>

                                <!-- Projected Cash & Cooldown -->
                                <div class="flex items-center justify-between text-[11px] pt-1 border-t border-slate-200/70 dark:border-slate-700/70">
                                    <span class="font-mono font-black text-emerald-600 dark:text-emerald-400"
                                          x-text="'€' + projectedCash({{ (int) $action['min'] }}) + ' – €' + projectedCash({{ (int) $action['max'] }})">
                                        €{{ number_format($action['min'], 0, ',', '.') }}–€{{ number_format($action['max'], 0, ',', '.') }}
                                    </span>
                                    <span class="font-mono text-slate-400">
                                        <i class="fa-regular fa-clock mr-1"></i>{{ gmdate('i:s', $action['cooldown'] ?? 180) }}
                                    </span>
                                </div>
                            </div>

                            <form action="{{ route('pickpocket.steal') }}" method="POST">
                                @csrf
                                <input type="hidden" name="action_id" value="{{ $action['id'] }}">
                                <input type="hidden" name="technique" value="schaduw" :value="selectedTech">
                                <button type="submit"
                                        :disabled="seconds > 0 || {{ $otherBlocked ? 'true' : 'false' }}"
                                        {{ $isDisabled ? 'disabled' : '' }}
                                        class="w-full py-2.5 px-3 rounded-xl bg-indigo-600 hover:bg-indigo-700 disabled:bg-slate-200 disabled:dark:bg-slate-800 disabled:text-slate-400 disabled:cursor-not-allowed text-white text-xs font-black uppercase tracking-wider transition cursor-pointer flex items-center justify-center gap-1.5">
                                    <i class="fa-solid fa-hand-holding-dollar text-[11px]"></i>
                                    <span x-text="'Toeslaan met ' + techMap[selectedTech].name">Toeslaan</span>
                                </button>
                            </form>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

    </div>

</div>
@endsection
