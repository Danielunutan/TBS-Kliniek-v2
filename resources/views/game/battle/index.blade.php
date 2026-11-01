@extends('layouts.app')

@section('content')
@php
    $rushHourData = $rushHour ?? [
        'isActive' => false,
        'focusMultiplier' => 1.5,
        'windowLabel' => '12:00–14:00 & 20:00–22:00',
        'statusText' => 'Kies een Focus-Sector voor 1.5x score (2.0x tijdens spitsuur).',
    ];
    $activeFocus = $focusCategory ?? null;
    $playerTotalEp = (int) ($totalEp ?? 0);
    $milestoneList = $milestones ?? [];
    $epBarPct = min(100, (int) floor(($playerTotalEp / 900) * 100));
@endphp

<div class="space-y-6" x-data="{ 
    modalOpen: false, 
    modalCategory: '', 
    modalFormula: '',
    modalUnit: '',
    modalScores: [], 
    loadingScores: false,
    secReset: {{ (int) ($secondsUntilReset ?? 0) }},
    formatCountdown(sec) {
        const total = Math.max(0, Math.floor(sec));
        const h = String(Math.floor(total / 3600)).padStart(2, '0');
        const m = String(Math.floor((total % 3600) / 60)).padStart(2, '0');
        const s = String(total % 60).padStart(2, '0');
        return `${h}:${m}:${s}`;
    },
    openCategoryDetails(catKey, catTitle, catFormula = '', catUnit = '') {
        this.modalOpen = true;
        this.modalCategory = catTitle;
        this.modalFormula = catFormula;
        this.modalUnit = catUnit;
        this.loadingScores = true;
        this.modalScores = [];
        fetch('{{ url('/24-uur-battle/categorie') }}/' + encodeURIComponent(catKey))
            .then(res => res.json())
            .then(data => {
                this.modalScores = data.scores || [];
                if (data.formula) this.modalFormula = data.formula;
                if (data.unit) this.modalUnit = data.unit;
                this.loadingScores = false;
            })
            .catch(() => {
                this.loadingScores = false;
            });
    }
}" x-init="if (secReset > 0) { const _t = setInterval(() => { secReset--; if (secReset <= 0) { clearInterval(_t); window.location.reload(); } }, 1000); }">

    <!-- Top Command Deck: Etmaal-Escalatie & Spitsuur Radar -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-6 sm:p-8 shadow-xs">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-center">
            <div class="lg:col-span-7 space-y-3">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 text-xs font-black px-3 py-1 rounded-full border border-indigo-200/80 dark:border-indigo-800/70">
                        <i class="fa-solid fa-bolt"></i>
                        <span>Etmaal-Escalatie &amp; Mijlpaal-Systeem</span>
                    </span>
                    @if($rushHourData['isActive'])
                        <span class="inline-flex items-center gap-1.5 bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 text-xs font-black px-3 py-1 rounded-full border border-emerald-300/80 dark:border-emerald-700/70 animate-pulse">
                            <i class="fa-solid fa-fire-flame-curved"></i>
                            <span>SPITSUUR ACTIEF: 2.0x FOCUS-SCORE</span>
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 bg-amber-50 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 text-xs font-bold px-3 py-1 rounded-full border border-amber-200/80 dark:border-amber-800/70">
                            <i class="fa-solid fa-gauge-high"></i>
                            <span>Spitsuur-vensters: {{ $rushHourData['windowLabel'] }} (2.0x Focus)</span>
                        </span>
                    @endif
                </div>

                <h1 class="text-2xl sm:text-4xl font-black tracking-tight text-slate-900 dark:text-white uppercase">
                    HET 24-UURS <span class="text-indigo-600 dark:text-indigo-400">ESCALATIE-PROTOCOL</span>
                </h1>

                <p class="text-slate-600 dark:text-slate-400 text-xs sm:text-sm font-medium leading-relaxed">
                    Domineer zeven ondergrondse sectoren binnen één etmaal. Vergrendel dagelijks jouw <strong class="text-slate-900 dark:text-white">Focus-Sector</strong> voor een directe score-vermenigvuldiger, bouw persoonlijke <strong class="text-slate-900 dark:text-white">Escalatie-Punten (EP)</strong> op voor drietraps mijlpaal-kluizen en verover om middernacht <strong class="text-slate-900 dark:text-white underline">XXL Koffers</strong>, Kluiscodes, Sterren en Bivakmutsen.
                </p>
            </div>

            <!-- Right Telemetry Cards: Spitsuur Status + Midnight Settlement Clock -->
            <div class="lg:col-span-5 grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div class="bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70 rounded-xl p-4 flex flex-col justify-between">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                            Focus-Multiplier
                        </span>
                        <i class="fa-solid fa-bolt-lightning text-amber-500 text-sm"></i>
                    </div>
                    <div class="my-2">
                        <span class="text-2xl font-black font-mono {{ $rushHourData['isActive'] ? 'text-emerald-600 dark:text-emerald-400' : 'text-indigo-600 dark:text-indigo-400' }}">
                            {{ number_format((float) $rushHourData['focusMultiplier'], 1) }}x
                        </span>
                        <span class="text-[11px] font-bold text-slate-600 dark:text-slate-300 block">
                            {{ $rushHourData['isActive'] ? 'Piek-tarief tijdens spitsuur' : 'Standaard Focus-tarief' }}
                        </span>
                    </div>
                    <span class="text-[10px] text-slate-500 dark:text-slate-400 font-medium">
                        Piektijden: {{ $rushHourData['windowLabel'] }}
                    </span>
                </div>

                <div class="bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70 rounded-xl p-4 flex flex-col justify-between">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                            Nachtelijke Eindafrekening
                        </span>
                        <i class="fa-solid fa-hourglass-half text-indigo-500 text-sm"></i>
                    </div>
                    <div class="my-2 text-2xl font-black font-mono text-indigo-700 dark:text-indigo-400 tracking-wider"
                         x-text="formatCountdown(secReset)">
                        {{ $resetTimeFormatted }}
                    </div>
                    <span class="text-[10px] text-indigo-600 dark:text-indigo-400 font-bold">
                        Automatische uitkering om 00:00
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Two-Column Asymmetric Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

        <!-- LEFT COLUMN (4 cols): Personal Escalation Track, Focus Overdrive Selector & Clinic Hierarchy -->
        <div class="lg:col-span-4 space-y-6">

            <!-- Card 1: Personal Escalation Index (EP) & Milestone Crates -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-5 shadow-xs space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                    <div>
                        <span class="text-[10px] font-extrabold uppercase tracking-wider text-indigo-600 dark:text-indigo-400 block">
                            Persoonlijk Dagspoor
                        </span>
                        <h2 class="text-sm font-black uppercase text-slate-900 dark:text-white">
                            Escalatie-Index &amp; Mijlpalen
                        </h2>
                    </div>
                    <div class="text-right">
                        <span class="text-lg font-black font-mono text-indigo-600 dark:text-indigo-400">
                            {{ number_format($playerTotalEp, 0, ',', '.') }} EP
                        </span>
                        <span class="text-[10px] font-bold text-slate-400 block">Doel: 900 EP</span>
                    </div>
                </div>

                <!-- Overall Progress Bar -->
                <div class="space-y-1.5">
                    <div class="flex items-center justify-between text-[11px] font-bold text-slate-600 dark:text-slate-300">
                        <span>Totale Etmaal-Voortgang</span>
                        <span>{{ $epBarPct }}%</span>
                    </div>
                    <div class="w-full h-3 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden border border-slate-200/70 dark:border-slate-700">
                        <div class="h-full bg-indigo-600 dark:bg-indigo-500 rounded-full transition-all duration-300"
                             style="width: {{ $epBarPct }}%"></div>
                    </div>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400">
                        Elke actie in de 7 sectoren levert volgens de sector-formule direct Escalatie-Punten (EP) op.
                    </p>
                </div>

                <!-- 3 Milestone Crates -->
                <div class="space-y-3 pt-1">
                    @foreach($milestoneList as $tier => $ms)
                        <div class="p-3.5 rounded-xl border transition {{ $ms['is_claimed'] ? 'bg-emerald-50/50 dark:bg-emerald-950/20 border-emerald-200 dark:border-emerald-800/60' : ($ms['can_claim'] ? 'bg-amber-50/80 dark:bg-amber-950/30 border-amber-300 dark:border-amber-700' : 'bg-slate-50/90 dark:bg-slate-800/50 border-slate-200/80 dark:border-slate-700/70') }}">
                            <div class="flex items-center justify-between gap-2 mb-1.5">
                                <div class="flex items-center gap-2 min-w-0">
                                    <span class="w-6 h-6 rounded-lg text-xs font-black flex items-center justify-center shrink-0 {{ $ms['is_claimed'] ? 'bg-emerald-600 text-white' : ($ms['can_claim'] ? 'bg-amber-400 text-slate-950' : 'bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-200') }}">
                                        {{ $tier }}
                                    </span>
                                    <div class="min-w-0">
                                        <span class="text-xs font-extrabold text-slate-900 dark:text-white block truncate">
                                            {{ $ms['title'] }}
                                        </span>
                                        <span class="text-[10px] font-bold text-slate-500 dark:text-slate-400">
                                            Vereist: {{ number_format($ms['required_ep'], 0, ',', '.') }} EP
                                        </span>
                                    </div>
                                </div>

                                @if($ms['is_claimed'])
                                    <span class="text-[10px] font-black uppercase px-2.5 py-1 rounded-full bg-emerald-100 dark:bg-emerald-900/60 text-emerald-800 dark:text-emerald-300 shrink-0">
                                        Geclaimd
                                    </span>
                                @elseif($ms['can_claim'])
                                    @if($character->isComatose() || $character->isImprisoned())
                                        <button type="button" disabled title="Je kunt geen premies innen vanuit de isoleercel of in coma" class="px-3 py-1.5 rounded-lg bg-slate-200 dark:bg-slate-700 text-slate-400 dark:text-slate-500 font-black text-[11px] uppercase cursor-not-allowed opacity-60">
                                            Eis Premie Op
                                        </button>
                                    @else
                                        <form method="POST" action="{{ route('battle.milestone', $tier) }}">
                                            @csrf
                                            <button type="submit" class="px-3 py-1.5 rounded-lg bg-amber-500 hover:bg-amber-600 text-slate-950 font-black text-[11px] uppercase shadow-xs transition cursor-pointer">
                                                Eis Premie Op
                                            </button>
                                        </form>
                                    @endif
                                @else
                                    <span class="text-[10px] font-bold text-slate-400 shrink-0">
                                        {{ $ms['progress_pct'] }}%
                                    </span>
                                @endif
                            </div>

                            <div class="text-[11px] font-bold text-indigo-700 dark:text-indigo-300 bg-white/80 dark:bg-slate-900/60 px-2.5 py-1.5 rounded-lg border border-slate-200/60 dark:border-slate-700/60">
                                Buit: {{ $ms['label'] }}
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Card 2: Daily Focus-Overdrive Selector -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-5 shadow-xs space-y-4">
                <div class="border-b border-slate-100 dark:border-slate-800 pb-3">
                    <span class="text-[10px] font-extrabold uppercase tracking-wider text-amber-600 dark:text-amber-400 block">
                        Strategische Multiplier
                    </span>
                    <h2 class="text-sm font-black uppercase text-slate-900 dark:text-white">
                        Dagelijkse Focus-Overdrive
                    </h2>
                </div>

                @if($activeFocus && isset($categories[$activeFocus]))
                    <div class="p-4 rounded-xl bg-indigo-50/80 dark:bg-indigo-950/40 border border-indigo-200 dark:border-indigo-800/70 space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-black uppercase text-indigo-700 dark:text-indigo-300">
                                Actieve Focus-Sector Vandaag
                            </span>
                            <span class="text-xs font-black text-emerald-600 dark:text-emerald-400">
                                {{ number_format((float) $rushHourData['focusMultiplier'], 1) }}x Score
                            </span>
                        </div>
                        <div class="flex items-center gap-2.5">
                            <i class="fa-solid {{ $categories[$activeFocus]['icon'] ?? 'fa-bolt' }} text-indigo-600 dark:text-indigo-400 text-base"></i>
                            <span class="text-sm font-black text-slate-900 dark:text-white">
                                {{ $categories[$activeFocus]['title'] }}
                            </span>
                        </div>
                        <p class="text-[11px] text-slate-600 dark:text-slate-300">
                            Alle punten die je vandaag binnen deze sector behaalt, worden automatisch met <strong>1.5x</strong> vermenigvuldigd (en met <strong>2.0x</strong> tijdens de spitsuuren om 12:00–14:00 &amp; 20:00–22:00).
                        </p>
                    </div>
                @else
                    <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed">
                        Kies één sector waarop jij je vandaag richt. Na vergrendeling telt elke actie in die sector voor <strong>1.5x</strong> mee (en <strong>2.0x</strong> tijdens het spitsuur). Let op: deze keuze staat voor de rest van de dag vast.
                    </p>
                    @if($character->isComatose() || $character->isImprisoned())
                        <div class="p-3 bg-amber-50 dark:bg-amber-950/40 border border-amber-300 dark:border-amber-700/60 rounded-xl text-xs text-amber-800 dark:text-amber-200 font-bold">
                            Je kunt momenteel geen focus-sector vergrendelen omdat je in de isoleercel of in coma verkeert.
                        </div>
                    @else
                        <form method="POST" action="{{ route('battle.focus') }}" class="space-y-3">
                            @csrf
                            <div>
                                <label for="focus-category-select" class="text-[11px] font-bold uppercase text-slate-600 dark:text-slate-300 block mb-1">
                                    Selecteer jouw Focus-Sector
                                </label>
                                <select id="focus-category-select"
                                        name="category"
                                        class="w-full rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 px-3 py-2.5 text-xs font-bold text-slate-900 dark:text-white">
                                    @foreach($categories as $catKey => $cat)
                                        <option value="{{ $catKey }}">{{ $cat['title'] }} ({{ $cat['formula_label'] ?? 'Standaard' }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <button type="submit" class="w-full py-2.5 px-4 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-black text-xs uppercase tracking-wider shadow-xs transition cursor-pointer">
                                <i class="fa-solid fa-lock mr-1"></i> Vergrendel Focus-Overdrive
                            </button>
                        </form>
                    @endif
                @endif
            </div>

            <!-- Card 3: Overall Clinic Hierarchy (Vertical Dossier Cards) -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-5 shadow-xs space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                    <div>
                        <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400 block">
                            Paviljoen Reputatie
                        </span>
                        <h2 class="text-sm font-black uppercase text-slate-900 dark:text-white">
                            Top 10 Gezags-Hiërarchie
                        </h2>
                    </div>
                    <a href="{{ route('leaderboard.index') }}" class="text-[11px] font-bold text-indigo-600 dark:text-indigo-400 hover:underline">
                        Totaaloverzicht &rarr;
                    </a>
                </div>

                <div class="space-y-2">
                    @forelse($topRankings as $rankIdx => $topChar)
                        <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <span class="w-6 h-6 rounded-lg text-[11px] font-black flex items-center justify-center shrink-0 {{ $rankIdx === 0 ? 'bg-amber-400 text-slate-950' : ($rankIdx < 3 ? 'bg-indigo-600 text-white' : 'bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-200') }}">
                                    {{ $rankIdx + 1 }}
                                </span>
                                <img src="{{ $topChar->avatar_url }}"
                                     alt="{{ $topChar->name }}"
                                     onerror="this.onerror=null; this.src='{{ asset('assets/characters/avatar_male_1.jpg') }}';"
                                     class="w-8 h-8 rounded-full object-cover border border-slate-200 dark:border-slate-700 shrink-0">
                                <a href="{{ route('profile.show', $topChar->name) }}"
                                   class="text-xs font-bold text-slate-900 dark:text-white hover:text-indigo-600 dark:hover:text-indigo-400 truncate">
                                    {{ $topChar->name }}
                                </a>
                            </div>
                            <span class="text-xs font-mono font-extrabold text-slate-600 dark:text-slate-300 shrink-0 ml-2">
                                {{ number_format($topChar->respect, 0, ',', '.') }} gezag
                            </span>
                        </div>
                    @empty
                        <div class="text-xs text-slate-400 text-center py-4">
                            Nog geen patiënten geregistreerd.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- RIGHT COLUMN (8 cols): 7 Sector Monitors with Visual 3-Column Podiums -->
        <div class="lg:col-span-8 space-y-5">
            @foreach($categories as $catKey => $cat)
                @php
                    $top3ByRank = collect($cat['top3'] ?? [])->keyBy('rank');
                    $rank1 = $top3ByRank->get(1);
                    $rank2 = $top3ByRank->get(2);
                    $rank3 = $top3ByRank->get(3);
                    $isFocusCard = (bool) ($cat['is_focus'] ?? ($activeFocus === $catKey));
                    $myRankVal = $cat['userPos'] ?? ($cat['myRank'] ?? null);
                    $myScoreVal = (int) ($cat['userScore'] ?? ($cat['myScore'] ?? 0));
                    $myEpVal = (int) ($cat['userEp'] ?? 0);
                    $formulaText = $cat['formula_label'] ?? '1 punt = +1 EP';
                    $unitText = $cat['unit_label'] ?? ($cat['unit'] ?? 'punten');
                @endphp

                <div class="bg-white dark:bg-slate-900 border {{ $isFocusCard ? 'border-indigo-400 dark:border-indigo-500 ring-1 ring-indigo-400/30' : 'border-slate-200/90 dark:border-slate-800' }} rounded-2xl p-5 shadow-xs space-y-4 transition">
                    <!-- Sector Header & Calculation Formula Badge -->
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 dark:border-slate-800 pb-3.5">
                        <div class="space-y-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="w-7 h-7 rounded-lg bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-xs">
                                    <i class="fa-solid {{ $cat['icon'] }}"></i>
                                </span>
                                <h3 class="text-sm font-black uppercase tracking-wide text-slate-900 dark:text-white">
                                    {{ $cat['title'] }}
                                </h3>
                                @if($isFocusCard)
                                    <span class="text-[10px] font-black uppercase px-2 py-0.5 rounded-full bg-indigo-600 text-white">
                                        Jouw Focus-Sector ({{ number_format((float) $rushHourData['focusMultiplier'], 1) }}x)
                                    </span>
                                @endif
                            </div>
                            @if(!empty($cat['subtitle'] ?? ($cat['desc'] ?? null)))
                                <p class="text-xs text-slate-500 dark:text-slate-400">
                                    {{ $cat['subtitle'] ?? $cat['desc'] }}
                                </p>
                            @endif
                        </div>

                        <div class="flex items-center gap-2 flex-wrap shrink-0">
                            @if($catKey === 'breakouts')
                                <a href="{{ route('prison') }}"
                                   class="px-3 py-1.5 rounded-lg bg-rose-600 hover:bg-rose-500 text-white text-xs font-extrabold transition inline-flex items-center gap-1.5 shadow-2xs">
                                    <i class="fa-solid fa-dungeon"></i>
                                    <span>Naar Gevangenis &amp; Uitbreken &rarr;</span>
                                </a>
                            @endif
                            <span class="text-[11px] font-mono font-bold px-2.5 py-1 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200/80 dark:border-slate-700">
                                Berekening: {{ $formulaText }}
                            </span>
                            <button type="button"
                                    @click="openCategoryDetails(@js($catKey), @js($cat['title']), @js($formulaText), @js($unitText))"
                                    class="px-3 py-1.5 rounded-lg bg-indigo-50 dark:bg-indigo-950/60 hover:bg-indigo-100 dark:hover:bg-indigo-900/60 text-indigo-700 dark:text-indigo-300 text-xs font-extrabold transition cursor-pointer">
                                Sector-Dossier &rarr;
                            </button>
                        </div>
                    </div>

                    <!-- 3-Column Podium Stage (#2 Zilver | #1 Goud | #3 Brons) -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 items-stretch">
                        <!-- Rank #1 (Golden Lead) -->
                        <div class="sm:order-2 p-3.5 rounded-xl bg-amber-50/80 dark:bg-amber-950/30 border border-amber-300/80 dark:border-amber-700/70 flex flex-col justify-between space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="inline-flex items-center gap-1 text-[10px] font-black uppercase text-amber-900 dark:text-amber-300">
                                    <i class="fa-solid fa-medal text-amber-500"></i> Koppositie #1
                                </span>
                                <span class="text-[10px] font-extrabold px-2 py-0.5 rounded-full bg-amber-200/70 dark:bg-amber-900/60 text-amber-950 dark:text-amber-200">
                                    {{ $cat['rewards'][1]['label'] ?? '' }}
                                </span>
                            </div>

                            @if($rank1)
                                <div class="flex items-center justify-between pt-1">
                                    <span class="text-xs font-black text-slate-900 dark:text-white truncate">
                                        {{ $rank1['name'] }}
                                    </span>
                                    <div class="text-right shrink-0 ml-2">
                                        <span class="text-xs font-mono font-black text-amber-700 dark:text-amber-300 block">
                                            {{ number_format($rank1['score'], 0, ',', '.') }}
                                        </span>
                                        <span class="text-[10px] font-mono text-slate-500 dark:text-slate-400">
                                            +{{ number_format($rank1['ep'] ?? 0, 0, ',', '.') }} EP
                                        </span>
                                    </div>
                                </div>
                            @else
                                <div class="text-[11px] text-slate-400 italic pt-1">
                                    Positie #1 ligt nog volledig open
                                </div>
                            @endif
                        </div>

                        <!-- Rank #2 (Silver Runner-Up) -->
                        <div class="sm:order-1 p-3.5 rounded-xl bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70 flex flex-col justify-between space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="text-[10px] font-black uppercase text-slate-600 dark:text-slate-300">
                                    Tweede Rang #2
                                </span>
                                <span class="text-[10px] font-bold text-slate-600 dark:text-slate-300">
                                    {{ $cat['rewards'][2]['label'] ?? '' }}
                                </span>
                            </div>

                            @if($rank2)
                                <div class="flex items-center justify-between pt-1">
                                    <span class="text-xs font-bold text-slate-800 dark:text-slate-200 truncate">
                                        {{ $rank2['name'] }}
                                    </span>
                                    <div class="text-right shrink-0 ml-2">
                                        <span class="text-xs font-mono font-extrabold text-indigo-600 dark:text-indigo-400 block">
                                            {{ number_format($rank2['score'], 0, ',', '.') }}
                                        </span>
                                        <span class="text-[10px] font-mono text-slate-400">
                                            +{{ number_format($rank2['ep'] ?? 0, 0, ',', '.') }} EP
                                        </span>
                                    </div>
                                </div>
                            @else
                                <div class="text-[11px] text-slate-400 italic pt-1">
                                    Nog geen tweede klassering
                                </div>
                            @endif
                        </div>

                        <!-- Rank #3 (Bronze Contender) -->
                        <div class="sm:order-3 p-3.5 rounded-xl bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70 flex flex-col justify-between space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="text-[10px] font-black uppercase text-orange-800 dark:text-orange-300">
                                    Derde Rang #3
                                </span>
                                <span class="text-[10px] font-bold text-slate-600 dark:text-slate-300">
                                    {{ $cat['rewards'][3]['label'] ?? '' }}
                                </span>
                            </div>

                            @if($rank3)
                                <div class="flex items-center justify-between pt-1">
                                    <span class="text-xs font-bold text-slate-800 dark:text-slate-200 truncate">
                                        {{ $rank3['name'] }}
                                    </span>
                                    <div class="text-right shrink-0 ml-2">
                                        <span class="text-xs font-mono font-extrabold text-indigo-600 dark:text-indigo-400 block">
                                            {{ number_format($rank3['score'], 0, ',', '.') }}
                                        </span>
                                        <span class="text-[10px] font-mono text-slate-400">
                                            +{{ number_format($rank3['ep'] ?? 0, 0, ',', '.') }} EP
                                        </span>
                                    </div>
                                </div>
                            @else
                                <div class="text-[11px] text-slate-400 italic pt-1">
                                    Nog geen derde klassering
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Personal Sector Telemetry Bar -->
                    <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex flex-wrap items-center justify-between gap-3 text-xs">
                        <div class="flex flex-wrap items-center gap-4">
                            <div>
                                <span class="text-[10px] uppercase font-bold text-slate-400 block">Jouw Klassering</span>
                                <span class="font-mono font-black text-slate-900 dark:text-white">
                                    {{ $myRankVal ? '#' . $myRankVal : 'Niet geklasseerd' }}
                                </span>
                            </div>
                            <div>
                                <span class="text-[10px] uppercase font-bold text-slate-400 block">Ruwe Sector-Score</span>
                                <span class="font-mono font-black text-indigo-600 dark:text-indigo-400">
                                    {{ number_format($myScoreVal, 0, ',', '.') }} {{ $unitText }}
                                </span>
                            </div>
                            <div>
                                <span class="text-[10px] uppercase font-bold text-slate-400 block">EP-Bijdrage</span>
                                <span class="font-mono font-black text-emerald-600 dark:text-emerald-400">
                                    +{{ number_format($myEpVal, 0, ',', '.') }} EP
                                </span>
                            </div>
                        </div>

                        <button type="button"
                                @click="openCategoryDetails(@js($catKey), @js($cat['title']), @js($formulaText), @js($unitText))"
                                class="text-xs font-bold text-indigo-600 dark:text-indigo-400 hover:underline cursor-pointer">
                            Volledige top 25 &amp; EP-tabel bekijken &rarr;
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- MODAL FOR FULL SECTOR DOSSIER & EP CALCULATION TABLE -->
    <div x-show="modalOpen"
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4 pb-24 lg:pb-4"
         @click.self="modalOpen = false">
        <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl max-w-xl w-full p-6 text-slate-900 dark:text-white shadow-xl space-y-4 relative max-h-[85dvh] flex flex-col">
            <!-- Modal Header -->
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                <div>
                    <div class="flex items-center gap-2 font-black text-slate-900 dark:text-white text-sm uppercase tracking-wider">
                        <i class="fa-solid fa-bolt text-indigo-500"></i>
                        <span x-text="modalCategory"></span>
                    </div>
                    <span class="text-[11px] font-mono text-slate-500 dark:text-slate-400 block mt-0.5"
                          x-show="modalFormula"
                          x-text="'Omrekenformule: ' + modalFormula"></span>
                </div>
                <button @click="modalOpen = false"
                        class="w-8 h-8 rounded-xl flex items-center justify-center text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition cursor-pointer"
                        aria-label="Venster sluiten">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>

            <!-- Modal Table Header -->
            <div class="grid grid-cols-12 gap-2 px-3 py-2 rounded-lg bg-slate-100 dark:bg-slate-800 text-[10px] font-black uppercase tracking-wider text-slate-500 dark:text-slate-400">
                <div class="col-span-2">Klassering</div>
                <div class="col-span-5">Gedetineerde</div>
                <div class="col-span-3 text-right">Ruwe Score</div>
                <div class="col-span-2 text-right">EP-Waarde</div>
            </div>

            <!-- Modal Content -->
            <div class="overflow-y-auto flex-1 space-y-2 pr-1">
                <template x-if="loadingScores">
                    <div class="py-12 text-center text-indigo-600 dark:text-indigo-400 font-bold text-xs animate-pulse">
                        <i class="fa-solid fa-spinner fa-spin text-lg mb-2 block"></i>
                        Sector-dossier wordt geladen...
                    </div>
                </template>

                <template x-if="!loadingScores && modalScores.length === 0">
                    <div class="py-12 text-center text-slate-400 font-medium text-xs">
                        Er zijn dit etmaal nog geen acties geregistreerd binnen deze sector.
                    </div>
                </template>

                <template x-if="!loadingScores && modalScores.length > 0">
                    <div class="space-y-1.5">
                        <template x-for="item in modalScores" :key="item.rank">
                            <div class="grid grid-cols-12 gap-2 items-center p-3 rounded-xl bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70">
                                <div class="col-span-2">
                                    <span class="font-mono font-black text-xs px-2 py-0.5 rounded-md bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300"
                                          x-text="'#' + item.rank"></span>
                                </div>
                                <div class="col-span-5 flex items-center gap-2.5 min-w-0">
                                    <img :src="item.avatar"
                                         onerror="this.onerror=null; this.src='{{ asset('assets/characters/avatar_male_1.jpg') }}';"
                                         class="w-7 h-7 rounded-full object-cover border border-slate-200 dark:border-slate-700 shrink-0">
                                    <div class="min-w-0">
                                        <span class="font-bold text-slate-900 dark:text-white text-xs block truncate" x-text="item.name"></span>
                                        <span class="text-[10px] text-slate-400" x-text="'Niveau ' + item.level"></span>
                                    </div>
                                </div>
                                <div class="col-span-3 text-right font-mono font-bold text-slate-800 dark:text-slate-200 text-xs"
                                     x-text="item.score"></div>
                                <div class="col-span-2 text-right font-mono font-extrabold text-emerald-600 dark:text-emerald-400 text-xs"
                                     x-text="'+' + (item.ep ?? 0) + ' EP'"></div>
                            </div>
                        </template>
                    </div>
                </template>
            </div>

            <!-- Modal Footer -->
            <div class="pt-3 border-t border-slate-100 dark:border-slate-800 text-right">
                <button type="button"
                        @click="modalOpen = false"
                        class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-black text-xs uppercase shadow-xs transition cursor-pointer">
                    Dossier Sluiten
                </button>
            </div>
        </div>
    </div>

</div>
@endsection
