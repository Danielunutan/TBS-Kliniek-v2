@extends('layouts.app')

@section('content')
@php
    $coldBloodedPct = $character->getColdBloodedPct();
    $canOneTap = $character->canOneTapExecute();
    $minBullets = $character->getMinimumBulletsRequired();
    $activeVest = $character->getActiveVestInfo();
    $vestSeconds = $character->getVestRemainingSeconds();
    $vestHours = (int) floor($vestSeconds / 3600);
    $vestMins = (int) ceil(($vestSeconds % 3600) / 60);
    $streakDeaths = $character->getLiquidationStreakDeaths();
    $needsSerum = $character->needsReviveSerum();
    $currentWing = $character->current_location ?? 'Paviljoen 1 (Gesloten)';
    $totalWealth = (int) $character->cash + (int) $character->bank;

    $initialTarget = $targets->firstWhere('in_same_wing', true)
        ?? $targets->firstWhere('has_ready_intel', true)
        ?? $targets->first();
@endphp

<div class="space-y-6" x-data="{
    activeTab: @js(request('tab', 'liquidatie')),
    selectedTargetId: @js((string) ($initialTarget?->id ?? '')),
    selectedTargetName: @js($initialTarget?->name ?? ''),
    selectedTargetSameWing: {{ $initialTarget && $initialTarget->in_same_wing ? 'true' : 'false' }},
    selectedTargetRevealedWing: @js($initialTarget?->revealed_location ?? ''),
    bulletAmount: {{ $canOneTap ? 1 : $minBullets }},
    minBullets: {{ $minBullets }},
    canOneTap: {{ $canOneTap ? 'true' : 'false' }},
    searchQuery: '',
    searchResults: [],
    isSearching: false,
    hasIsolation: {{ $character->isInVoluntaryIsolation() ? 'true' : 'false' }},
    isolationModalOpen: false,
    pendingForm: null,
    handleHitmanAction(event) {
        if (this.hasIsolation) {
            event.preventDefault();
            this.pendingForm = event.target;
            this.isolationModalOpen = true;
            return false;
        }
        return true;
    },
    confirmBreakAndHit() {
        if (!this.pendingForm) return;
        let hidden = this.pendingForm.querySelector('input[name=confirm_break_isolation]');
        if (!hidden) {
            hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.name = 'confirm_break_isolation';
            this.pendingForm.appendChild(hidden);
        }
        hidden.value = '1';
        this.isolationModalOpen = false;
        this.hasIsolation = false;
        this.pendingForm.submit();
    },
    selectTarget(id, name, sameWing, revealedWing) {
        this.selectedTargetId = String(id);
        this.selectedTargetName = name;
        this.selectedTargetSameWing = Boolean(sameWing);
        this.selectedTargetRevealedWing = revealedWing || '';
        this.$nextTick(() => {
            const el = document.getElementById('selected-target-controls');
            if (el) el.scrollIntoView({ behavior: 'smooth', block: 'center' });
        });
    },
    async performSearch() {
        if (this.searchQuery.trim().length < 1) {
            this.searchResults = [];
            return;
        }
        this.isSearching = true;
        try {
            const res = await fetch('{{ route('hitman.search') }}?q=' + encodeURIComponent(this.searchQuery.trim()), {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            });
            const data = await res.json();
            this.searchResults = data.targets || [];
        } catch (e) {
            this.searchResults = [];
        } finally {
            this.isSearching = false;
        }
    }
}">

    @if($character->isInVoluntaryIsolation())
    <div class="p-4 rounded-2xl bg-amber-50 dark:bg-amber-950/40 border-2 border-amber-300 dark:border-amber-700/70 flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-xs">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-amber-500 text-white flex items-center justify-center font-bold text-lg shrink-0 shadow-xs">
                <i class="fa-solid fa-shield-halved"></i>
            </div>
            <div>
                <div class="text-xs font-black text-amber-950 dark:text-amber-200 uppercase tracking-wider flex items-center gap-1.5">
                    <span>Vrijwillige Isolatie Actief</span>
                    <span class="px-2 py-0.5 rounded-full bg-amber-200/80 dark:bg-amber-900/80 text-amber-900 dark:text-amber-200 text-[10px]">100% Beschermd</span>
                </div>
                <p class="text-xs text-amber-900 dark:text-amber-300 mt-0.5">
                    Je cel is vergrendeld tot <strong>{{ $character->protection_until?->format('d-m H:i') }}</strong>. Als je een liquidatie uitvoert of een contract uitzet, vraagt het spel eerst een bevestiging en vervalt je isolatie.
                </p>
            </div>
        </div>
        <a href="{{ route('isolation.index') }}" class="inline-flex items-center justify-center px-3.5 py-2 rounded-xl bg-amber-600 hover:bg-amber-700 text-white text-xs font-black shrink-0 transition shadow-xs">
            Bekijk Isolatie &raquo;
        </a>
    </div>
    @elseif($character->hasMedicalShield())
    <div class="p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 border-2 border-emerald-300 dark:border-emerald-700/70 flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-xs">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-emerald-500 text-white flex items-center justify-center font-bold text-lg shrink-0 shadow-xs">
                <i class="fa-solid fa-heart-pulse"></i>
            </div>
            <div>
                <div class="text-xs font-black text-emerald-950 dark:text-emerald-200 uppercase tracking-wider flex items-center gap-1.5">
                    <span>Medisch Herstelschild Actief</span>
                    <span class="px-2 py-0.5 rounded-full bg-emerald-200/80 dark:bg-emerald-900/80 text-emerald-900 dark:text-emerald-200 text-[10px]">Herstelbescherming</span>
                </div>
                <p class="text-xs text-emerald-900 dark:text-emerald-300 mt-0.5">
                    Je herstelt van een liquidatie of IC-opname tot <strong>{{ $character->protection_until?->format('d-m H:i') }}</strong>. Niemand kan jou beschieten en je celdeur zit <strong>niet</strong> op slot. Als je zelf een aanval uitvoert, vervalt je herstelschild direct.
                </p>
            </div>
        </div>
    </div>
    @endif

    <!-- Header Card (Clean Light & Dark Mode) -->
    <div class="rounded-2xl shadow-xs p-6 sm:p-8 text-slate-800 dark:text-slate-100 bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <div>
                <div class="inline-flex items-center gap-2 bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 text-xs font-bold px-3 py-1 rounded-full border border-rose-200/80 dark:border-rose-800/70 mb-3">
                    <i class="fa-solid fa-crosshairs text-rose-600 dark:text-rose-400"></i> Forensische Afrekeningen &amp; Schaduw-Inlichtingen 2.0
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white">Liquidatie &amp; Wapenarsenaal</h1>
                <p class="text-slate-500 dark:text-slate-400 text-xs sm:text-sm mt-1 max-w-2xl leading-relaxed">
                    Spoor doelwitten op met 15-minuten Schaduw-Inlichtingen, reis naar exact dezelfde kliniek-afdeling en beroof rivalen van <strong class="text-slate-800 dark:text-slate-200">45% contant zakgeld (kluis en bank 100% veilig)</strong>. Vanaf <strong class="text-slate-800 dark:text-slate-200">Dossier-Graad 25</strong> met minimaal <strong class="text-slate-800 dark:text-slate-200">30% Koudbloedigheid</strong> schiet je met een dodelijk <strong class="text-slate-800 dark:text-slate-200">1-Patroon Genadeschot</strong>.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2 shrink-0">
                <span class="px-3.5 py-2 rounded-xl bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70 text-xs font-extrabold text-emerald-700 dark:text-emerald-400 flex items-center gap-1.5 shadow-xs">
                    <i class="fa-solid fa-wallet text-emerald-600 dark:text-emerald-400"></i> €{{ number_format($totalWealth, 0, ',', '.') }}
                </span>
                <span class="px-3.5 py-2 rounded-xl bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70 text-xs font-extrabold text-amber-700 dark:text-amber-400 flex items-center gap-1.5 shadow-xs">
                    <i class="fa-solid fa-star text-amber-500"></i> {{ number_format($character->stars ?? 0, 0, ',', '.') }} Sterren
                </span>
                <span class="px-3.5 py-2 rounded-xl bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70 text-xs font-extrabold text-slate-800 dark:text-slate-200 flex items-center gap-1.5 shadow-xs">
                    <i class="fa-solid fa-gun text-rose-600 dark:text-rose-400"></i> {{ number_format($character->bullets ?? 0, 0, ',', '.') }} Patronen
                </span>
            </div>
        </div>
    </div>

    <!-- Code Blauw / 10x Geliquideerd Alert Banner -->
    @if($needsSerum || $streakDeaths >= 7)
    <div class="bg-white dark:bg-slate-900 border-2 {{ $needsSerum ? 'border-rose-500' : 'border-amber-400' }} rounded-2xl p-5 shadow-xs flex flex-col md:flex-row items-center justify-between gap-4">
        <div class="flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-xl {{ $needsSerum ? 'bg-rose-600 text-white animate-pulse' : 'bg-amber-500 text-slate-950' }} flex items-center justify-center text-lg font-bold shrink-0 shadow-xs">
                <i class="fa-solid fa-syringe"></i>
            </div>
            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <span class="text-[10px] font-black uppercase tracking-wider px-2.5 py-0.5 rounded-full {{ $needsSerum ? 'bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border border-rose-200/80 dark:border-rose-800/70' : 'bg-amber-50 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 border border-amber-200/80 dark:border-amber-800/70' }}">
                        Reanimatie-Dossier: {{ $streakDeaths }} / 10 Liquidaties
                    </span>
                    <h3 class="text-sm font-extrabold text-slate-900 dark:text-white">
                        {{ $needsSerum ? 'CODE BLAUW: 10/10 Geliquideerd — Hartstilstand!' : 'Waarschuwing: Je nadert de 10/10 Liquidatiegrens!' }}
                    </h3>
                </div>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                    Bij 10 liquidaties treedt Code Blauw in en moet je het <strong>{{ \App\Models\Character::REVIVE_SERUM_NAME }}</strong> (€1.500.000 of {{ \App\Models\Character::REVIVE_SERUM_STARS }} Sterren) aanschaffen om weer tot leven te komen en je teller te resetten naar 0/10.
                </p>
            </div>
        </div>
        <div class="flex flex-wrap items-center gap-2 shrink-0 w-full md:w-auto">
            @if($needsSerum && \App\Http\Controllers\SponsorAdController::areAdsEnabled())
            <button type="button"
                    @disabled($character->getRemainingAdsToday() <= 0)
                    onclick="window.tbsOpenSponsorAd('coma_revive', () => { window.location.reload(); })"
                    class="w-full md:w-auto py-2.5 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-700 dark:bg-emerald-600 dark:hover:bg-emerald-500 text-white text-xs font-black transition-all duration-150 active:scale-95 cursor-pointer flex items-center justify-center gap-1.5 whitespace-nowrap shadow-xs disabled:opacity-50 disabled:cursor-not-allowed">
                <i class="fa-solid fa-play text-xs"></i>
                <span>Gratis via Sponsor-Ad ({{ min(3, (int)($character->ad_coma_progress ?? 0)) }}/3)</span>
            </button>
            @endif
            <form action="{{ route('hitman.revive_serum') }}" method="POST" class="flex-1 md:flex-initial">
                @csrf
                <input type="hidden" name="currency" value="cash">
                <button type="submit" class="w-full py-2.5 px-4 rounded-xl bg-purple-600 hover:bg-purple-700 dark:bg-purple-600 dark:hover:bg-purple-500 text-white text-xs font-black transition-all duration-150 active:scale-95 cursor-pointer flex items-center justify-center gap-1.5 whitespace-nowrap shadow-xs">
                    <i class="fa-solid fa-syringe text-emerald-400"></i>
                    <span>Koop Ampul (€1,5M)</span>
                </button>
            </form>
            <form action="{{ route('hitman.revive_serum') }}" method="POST" class="flex-1 md:flex-initial">
                @csrf
                <input type="hidden" name="currency" value="stars">
                <button type="submit" class="w-full py-2.5 px-4 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 text-xs font-black transition-all duration-150 active:scale-95 cursor-pointer flex items-center justify-center gap-1.5 whitespace-nowrap shadow-xs">
                    <i class="fa-solid fa-star text-amber-200"></i>
                    <span>{{ \App\Models\Character::REVIVE_SERUM_STARS }} Sterren</span>
                </button>
            </form>
        </div>
    </div>
    @endif

    <!-- Forensisch Status HUD (4 overzichtelijke kaarten) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- 1. Dossier-Graad (1 - 30) -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-4 shadow-xs flex flex-col justify-between gap-3">
            <div class="flex items-center justify-between gap-2">
                <span class="text-[11px] font-black uppercase tracking-wider text-purple-700 dark:text-purple-400">Dossier-Graad</span>
                <span class="px-2.5 py-0.5 rounded-full bg-purple-50 dark:bg-purple-950/60 border border-purple-200/80 dark:border-purple-800/70 text-purple-700 dark:text-purple-300 text-[11px] font-extrabold shrink-0">
                    Graad {{ $gradeInfo['grade'] }} / 30
                </span>
            </div>
            <div>
                <div class="text-base font-extrabold text-slate-900 dark:text-white">{{ $gradeInfo['title'] }}</div>
                <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">{{ $gradeInfo['phase'] }}</p>
            </div>
            <div class="pt-2.5 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs">
                <span class="text-slate-500 dark:text-slate-400 font-medium">Vereist salvo:</span>
                @if($canOneTap)
                    <span class="font-black text-emerald-600 dark:text-emerald-400 flex items-center gap-1">
                        <i class="fa-solid fa-bolt"></i> 1 Patroon (Genadeschot)
                    </span>
                @else
                    <span class="font-black text-slate-900 dark:text-white">{{ number_format($gradeInfo['min_bullets'], 0, ',', '.') }} patronen</span>
                @endif
            </div>
        </div>

        <!-- 2. Koudbloedigheid (%) -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-4 shadow-xs flex flex-col justify-between gap-3">
            <div class="flex items-center justify-between gap-2">
                <span class="text-[11px] font-black uppercase tracking-wider text-indigo-700 dark:text-indigo-400">Koudbloedigheid</span>
                <span class="px-2.5 py-0.5 rounded-full {{ $coldBloodedPct >= 30 ? 'bg-emerald-50 dark:bg-emerald-950/60 border-emerald-200/80 dark:border-emerald-800/70 text-emerald-700 dark:text-emerald-300' : 'bg-rose-50 dark:bg-rose-950/60 border-rose-200/80 dark:border-rose-800/70 text-rose-700 dark:text-rose-300' }} border text-[11px] font-extrabold shrink-0">
                    {{ $coldBloodedPct }}% Focus
                </span>
            </div>
            <div>
                <div class="w-full h-2.5 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden border border-slate-200/80 dark:border-slate-700">
                    <div class="h-full rounded-full transition-all duration-300 {{ $coldBloodedPct >= 30 ? 'bg-emerald-500' : 'bg-rose-500' }}"
                         style="width: {{ $coldBloodedPct }}%"></div>
                </div>
                <div class="flex items-center justify-between text-[10px] text-slate-500 dark:text-slate-400 font-bold mt-1.5">
                    <span>0%</span>
                    <span class="{{ $coldBloodedPct >= 30 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">30% Genadeschot Grens</span>
                    <span>100%</span>
                </div>
            </div>
            <div class="pt-2.5 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs">
                <span class="text-slate-500 dark:text-slate-400 font-medium">Pantser-penetratie:</span>
                <span class="font-black text-slate-900 dark:text-white">+{{ $gradeInfo['armor_pierce'] }}%</span>
            </div>
        </div>

        <!-- 3. Huidige Kliniek-Ruimte (Gefixte uitlijning voor dropdown + knop) -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-4 shadow-xs flex flex-col justify-between gap-3 overflow-hidden">
            <div class="flex items-center justify-between gap-2">
                <span class="text-[11px] font-black uppercase tracking-wider text-emerald-700 dark:text-emerald-400">Huidige Afdeling</span>
                @if($character->isWingLocked())
                    <span class="px-2 py-0.5 rounded-full bg-rose-50 dark:bg-rose-950/60 border border-rose-200/80 dark:border-rose-800/70 text-rose-700 dark:text-rose-300 text-[10px] font-extrabold shrink-0">
                        <i class="fa-solid fa-lock"></i> Lockdown ({{ ceil($character->getWingLockRemainingSeconds() / 60) }}m)
                    </span>
                @else
                    <span class="px-2 py-0.5 rounded-full bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200/80 dark:border-emerald-800/70 text-emerald-700 dark:text-emerald-300 text-[10px] font-extrabold shrink-0">
                        Vrij Verplaatsbaar
                    </span>
                @endif
            </div>
            <div>
                <div class="text-sm font-extrabold text-slate-900 dark:text-white truncate" title="{{ $currentWing }}">
                    <i class="fa-solid fa-location-dot text-emerald-600 dark:text-emerald-400 mr-1"></i> {{ $currentWing }}
                </div>
                <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Je moet in dezelfde ruimte staan als je doelwit.</p>
            </div>
            <form action="{{ route('hitman.quick_travel') }}" method="POST" class="pt-2.5 border-t border-slate-100 dark:border-slate-800 flex items-center gap-1.5 min-w-0">
                @csrf
                <select name="destination" class="flex-1 min-w-0 py-1.5 px-2 bg-slate-50 dark:bg-slate-800/90 border border-slate-200 dark:border-slate-700 rounded-xl text-base sm:text-[11px] font-bold text-slate-800 dark:text-slate-200 focus:outline-none focus:border-purple-500 truncate">
                    @foreach($locations as $locKey => $locItem)
                        <option value="{{ $locKey }}" {{ $currentWing === $locItem['name'] ? 'selected' : '' }}>
                            {{ $locItem['name'] }}
                        </option>
                    @endforeach
                </select>
                <button type="submit" 
                        {{ $character->isImprisoned() || $character->isComatose() || $character->needsReviveSerum() || $character->cash < $travelCost ? 'disabled' : '' }}
                        class="py-1.5 px-2.5 rounded-xl {{ ($character->isImprisoned() || $character->isComatose() || $character->needsReviveSerum() || $character->cash < $travelCost) ? 'bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-slate-500 cursor-not-allowed' : 'bg-purple-600 hover:bg-purple-700 dark:bg-purple-600 dark:hover:bg-purple-500 text-white cursor-pointer active:scale-95' }} text-[11px] font-black transition-all duration-150 shrink-0 whitespace-nowrap shadow-xs">
                    Reis (€{{ number_format($travelCost, 0, ',', '.') }})
                </button>
            </form>
        </div>

        <!-- 4. Actief Backfire-Pantser & Reanimatie-Dossier -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-4 shadow-xs flex flex-col justify-between gap-3">
            <div class="flex items-center justify-between gap-2">
                <span class="text-[11px] font-black uppercase tracking-wider text-amber-700 dark:text-amber-400">Pantser &amp; Status</span>
                <span class="px-2.5 py-0.5 rounded-full {{ $streakDeaths >= 8 ? 'bg-rose-50 dark:bg-rose-950/60 border-rose-200/80 dark:border-rose-800/70 text-rose-700 dark:text-rose-300' : 'bg-slate-100 dark:bg-slate-800 border-slate-200/80 dark:border-slate-700 text-slate-700 dark:text-slate-300' }} border text-[11px] font-extrabold shrink-0">
                    Dossier: {{ $streakDeaths }} / 10 Kills
                </span>
            </div>
            <div>
                @if($activeVest)
                    <div class="text-sm font-extrabold text-emerald-700 dark:text-emerald-400 flex items-center gap-1.5 truncate">
                        <i class="fa-solid fa-shield-halved shrink-0"></i>
                        <span class="truncate">{{ $activeVest['name'] }}</span>
                    </div>
                    <p x-data="{
                            left: {{ (int) $character->getVestRemainingSeconds() }},
                            format(s) {
                                if (s <= 0) return 'Verlopen';
                                const h = Math.floor(s / 3600);
                                const m = Math.floor((s % 3600) / 60);
                                const sec = Math.floor(s % 60);
                                const pad = (n) => String(n).padStart(2, '0');
                                return `${pad(h)}:${pad(m)}:${pad(sec)} (${h}u ${m}m)`;
                            },
                            init() {
                                setInterval(() => { if (this.left > 0) this.left--; }, 1000);
                            }
                        }"
                        data-testid="hitman-vest-countdown"
                        class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">
                        Nog <span class="font-mono font-bold text-emerald-700 dark:text-emerald-300 tabular-nums" x-text="format(left)">{{ $vestHours }}u {{ $vestMins }}m</span> • +{{ $activeVest['defense_bonus'] }} Def • {{ $activeVest['backfire_chance'] }}% Backfire
                    </p>
                @else
                    <div class="text-sm font-extrabold text-slate-800 dark:text-slate-200 flex items-center gap-1.5">
                        <i class="fa-solid fa-shirt text-slate-400"></i> Geen Backfire-Vest Actief
                    </div>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">
                        Koop een 3-uurs Backfire-Vest in het Wapenarsenaal!
                    </p>
                @endif
            </div>
            <div class="pt-2.5 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs">
                <button type="button" @click="activeTab = 'arsenaal'" class="text-purple-700 dark:text-purple-400 hover:underline font-extrabold text-[11px] cursor-pointer">
                    <i class="fa-solid fa-shield-halved mr-1"></i> Open Pantser &amp; Munitie Shop &rarr;
                </button>
            </div>
        </div>
    </div>

    <!-- Active Blood Feuds Banner (Bloedwraak) -->
    @if($bloodFeuds->isNotEmpty())
    <div class="bg-white dark:bg-slate-900 border-2 border-rose-300 dark:border-rose-800/80 rounded-2xl p-5 shadow-xs space-y-3">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <span class="w-8 h-8 rounded-xl bg-rose-600 text-white flex items-center justify-center text-sm font-bold shrink-0">
                    <i class="fa-solid fa-droplet"></i>
                </span>
                <div>
                    <h3 class="text-sm font-extrabold text-slate-900 dark:text-white uppercase tracking-wider">Actieve Bloedwraak Vetes (24 Uur)</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Geen levelgrens tegen deze daders + extra €25.000 &amp; +50 Respect wraakpremie!</p>
                </div>
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            @foreach($bloodFeuds as $feud)
                @if($feud->attacker)
                <div class="p-3.5 rounded-xl bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70 flex items-center justify-between gap-3 shadow-xs">
                    <div class="min-w-0">
                        <div class="text-xs font-extrabold text-slate-900 dark:text-white truncate">
                            Dader: <span class="text-rose-600 dark:text-rose-400">{{ $feud->attacker->name }}</span> (Lvl {{ $feud->attacker->level }})
                        </div>
                        <div class="text-[11px] text-slate-500 dark:text-slate-400 truncate mt-0.5">
                            Slachtoffer: {{ $feud->victim?->name ?? 'Bendelid' }} • Verloopt {{ $feud->expires_at?->diffForHumans() ?? 'Verlopen' }}
                        </div>
                    </div>
                    <button type="button"
                            @click="activeTab = 'inlichtingen'; selectTarget(@js((string) $feud->attacker->id), @js($feud->attacker->name), false, '')"
                            class="py-2 px-3 rounded-xl bg-rose-600 hover:bg-rose-700 dark:bg-rose-600 dark:hover:bg-rose-500 text-white text-xs font-black transition-all duration-150 active:scale-95 cursor-pointer shrink-0 whitespace-nowrap shadow-xs">
                        <i class="fa-solid fa-crosshairs mr-1"></i> Spoor Op
                    </button>
                </div>
                @endif
            @endforeach
        </div>
    </div>
    @endif

    <!-- Navigation Tabs -->
    <div class="bg-slate-100/90 dark:bg-slate-800/80 border border-slate-200/80 dark:border-slate-700/70 rounded-2xl p-1.5 shadow-xs flex flex-wrap gap-2">
        <button type="button" @click="activeTab = 'liquidatie'"
                :class="activeTab === 'liquidatie' ? 'bg-white dark:bg-slate-900 text-purple-700 dark:text-purple-300 shadow-xs border border-purple-200/80 dark:border-purple-800/70 font-black' : 'text-slate-600 dark:text-slate-400 font-bold hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60'"
                class="flex-1 min-w-[130px] sm:min-w-[160px] py-2.5 px-3 sm:px-4 rounded-xl text-xs font-extrabold transition flex items-center justify-center gap-2 cursor-pointer">
            <i class="fa-solid fa-crosshairs"></i>
            <span>1. Liquidatie &amp; Schietbaan</span>
        </button>

        <button type="button" @click="activeTab = 'inlichtingen'"
                :class="activeTab === 'inlichtingen' ? 'bg-white dark:bg-slate-900 text-purple-700 dark:text-purple-300 shadow-xs border border-purple-200/80 dark:border-purple-800/70 font-black' : 'text-slate-600 dark:text-slate-400 font-bold hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60'"
                class="flex-1 min-w-[130px] sm:min-w-[160px] py-2.5 px-3 sm:px-4 rounded-xl text-xs font-extrabold transition flex items-center justify-center gap-2 cursor-pointer">
            <i class="fa-solid fa-user-secret"></i>
            <span>2. Schaduw-Inlichtingen (15m)</span>
            @if(count($enrichedIntelOps) > 0)
                <span class="px-2 py-0.5 rounded-full bg-emerald-500 text-white text-[10px] font-black">{{ count($enrichedIntelOps) }}</span>
            @endif
        </button>

        <button type="button" @click="activeTab = 'arsenaal'"
                :class="activeTab === 'arsenaal' ? 'bg-white dark:bg-slate-900 text-purple-700 dark:text-purple-300 shadow-xs border border-purple-200/80 dark:border-purple-800/70 font-black' : 'text-slate-600 dark:text-slate-400 font-bold hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60'"
                class="flex-1 min-w-[130px] sm:min-w-[160px] py-2.5 px-3 sm:px-4 rounded-xl text-xs font-extrabold transition flex items-center justify-center gap-2 cursor-pointer">
            <i class="fa-solid fa-shield-halved"></i>
            <span>3. Pantser &amp; Munitie Arsenaal</span>
        </button>

        <button type="button" @click="activeTab = 'contracten'"
                :class="activeTab === 'contracten' ? 'bg-white dark:bg-slate-900 text-purple-700 dark:text-purple-300 shadow-xs border border-purple-200/80 dark:border-purple-800/70 font-black' : 'text-slate-600 dark:text-slate-400 font-bold hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60'"
                class="flex-1 min-w-[130px] sm:min-w-[160px] py-2.5 px-3 sm:px-4 rounded-xl text-xs font-extrabold transition flex items-center justify-center gap-2 cursor-pointer">
            <i class="fa-solid fa-file-contract"></i>
            <span>4. Onderwereld Contracten</span>
        </button>
    </div>

    <!-- ========================================================================= -->
    <!-- TAB 1: LIQUIDATIE & ONDERGRONDSE SCHIETBAAN                               -->
    <!-- ========================================================================= -->
    <div x-show="activeTab === 'liquidatie'" class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

        <!-- Left Column (7 cols): Target Selection & Fire Controls -->
        <div class="lg:col-span-7 space-y-6">
            <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-6 shadow-xs space-y-5">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-100 dark:border-slate-800 pb-4">
                    <div>
                        <h2 class="text-base font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                            <i class="fa-solid fa-gun text-rose-600 dark:text-rose-400"></i> Doelwit Viseren &amp; Vuren
                        </h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                            Buit bij voltreffer: <strong class="text-slate-800 dark:text-slate-200">45% Contant Zakgeld (Bank &amp; Kluis 100% veilig)</strong> + 60 Respect + 400 XP.
                        </p>
                    </div>
                    <span class="px-3 py-1 rounded-xl bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70 text-xs font-bold text-slate-700 dark:text-slate-300 shrink-0 self-start sm:self-auto shadow-xs">
                        20 Energie per aanslag
                    </span>
                </div>

                <!-- Live Patient Search -->
                <div class="space-y-2 relative" @click.outside="searchResults = []">
                    <label class="block text-xs font-extrabold text-slate-700 dark:text-slate-300 uppercase tracking-wider">Zoek Patiënt op Naam</label>
                    <div class="relative">
                        <input type="text"
                               x-model="searchQuery"
                               @input.debounce.250ms="performSearch()"
                               placeholder="Typ de naam van je doelwit..."
                               class="w-full p-3 pl-9 bg-slate-50 dark:bg-slate-800/90 border border-slate-200 dark:border-slate-700 rounded-xl text-base sm:text-sm font-bold text-slate-900 dark:text-slate-100 placeholder:text-slate-400 dark:placeholder:text-slate-500 focus:outline-none focus:border-purple-500 dark:focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition shadow-2xs">
                        <i class="fa-solid fa-magnifying-glass text-slate-400 absolute left-3.5 top-3.5 text-xs"></i>
                    </div>

                    <!-- Search Dropdown Results -->
                    <div x-show="searchResults.length > 0" class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl divide-y divide-slate-100 dark:divide-slate-700 max-h-56 overflow-y-auto shadow-md">
                        <template x-for="item in searchResults" :key="item.id">
                            <div @click="selectTarget(item.id, item.name, item.inSameWing, item.revealedLocation); searchResults = []; searchQuery = '';"
                                 class="p-3 hover:bg-slate-50/80 dark:hover:bg-slate-700/60 flex items-center justify-between gap-3 cursor-pointer transition-colors">
                                <div class="min-w-0">
                                    <div class="text-xs font-extrabold text-slate-900 dark:text-white truncate" x-text="item.name + ' (Lvl ' + item.level + ')'"></div>
                                    <div class="text-[11px] text-slate-500 dark:text-slate-400 truncate">
                                        <span x-show="item.inSameWing" class="text-emerald-600 dark:text-emerald-400 font-bold">✅ Zelfde Afdeling (<span x-text="item.revealedLocation"></span>)</span>
                                        <span x-show="!item.inSameWing && item.revealedLocation" class="text-amber-600 dark:text-amber-400 font-bold">📍 Gezien op: <span x-text="item.revealedLocation"></span></span>
                                        <span x-show="!item.inSameWing && !item.revealedLocation" class="text-slate-500 dark:text-slate-400">🕵️ Afdeling Onbekend (Start 15m Schaduwing)</span>
                                    </div>
                                </div>
                                <span class="px-2.5 py-1 rounded-xl bg-slate-100 dark:bg-slate-700 text-slate-800 dark:text-slate-200 text-[11px] font-bold shrink-0 border border-slate-200/80 dark:border-slate-600">Selecteer</span>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Target List Cards (Met nette binnenruimte zodat de active ring nooit afsnijdt) -->
                <div class="space-y-2">
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-xs font-extrabold text-slate-700 dark:text-slate-300 uppercase tracking-wider">Actieve Doelwitten op de Afdelingen</span>
                        <span class="text-[11px] text-slate-500 dark:text-slate-400 truncate">Jouw locatie: <strong class="text-slate-800 dark:text-slate-200">{{ $currentWing }}</strong></span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 max-h-80 overflow-y-auto p-1 -mx-1">
                        @foreach($targets as $t)
                        <div @click="selectTarget(@js((string) $t->id), @js($t->name), {{ $t->in_same_wing ? 'true' : 'false' }}, @js($t->revealed_location ?? ''))"
                             :class="selectedTargetId === '{{ $t->id }}' ? 'border-purple-600 dark:border-purple-500 bg-purple-50/60 dark:bg-purple-900/40 ring-2 ring-purple-500/40 shadow-xs' : 'border-slate-200/80 dark:border-slate-700/70 bg-slate-50/90 dark:bg-slate-800/60 hover:border-purple-300 dark:hover:border-purple-700/80'"
                             class="p-3.5 rounded-xl border transition-all duration-200 cursor-pointer flex flex-col justify-between gap-2">
                            <div class="flex items-center justify-between gap-2">
                                <div class="truncate">
                                    <span class="text-xs font-extrabold text-slate-900 dark:text-white">{{ $t->name }}</span>
                                    <span class="text-[10px] font-bold text-slate-500 dark:text-slate-400 ml-1">Lvl {{ $t->level }}</span>
                                </div>
                                <span class="text-[10px] font-extrabold px-2 py-0.5 rounded-full shrink-0 {{ $t->streak_deaths >= 10 ? 'bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border border-rose-200/80 dark:border-rose-800/70' : 'bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 border border-slate-200/80 dark:border-slate-600' }}">
                                    {{ $t->streak_deaths }}/10
                                </span>
                            </div>

                            <div class="flex items-center justify-between gap-2 text-[11px]">
                                @if($t->in_same_wing)
                                    <span class="text-emerald-700 dark:text-emerald-400 font-extrabold flex items-center gap-1 truncate">
                                        <i class="fa-solid fa-circle-check text-emerald-600 dark:text-emerald-400 shrink-0"></i>
                                        <span class="truncate">Zelfde ruimte!</span>
                                    </span>
                                @elseif($t->has_ready_intel)
                                    <span class="text-amber-700 dark:text-amber-400 font-extrabold truncate" title="{{ $t->revealed_location }}">
                                        <i class="fa-solid fa-location-crosshairs shrink-0"></i> {{ $t->revealed_location }}
                                    </span>
                                @elseif($t->has_pending_intel)
                                    <span class="text-indigo-700 dark:text-indigo-300 font-bold truncate">
                                        <i class="fa-solid fa-spinner fa-spin shrink-0"></i> Schaduwen ({{ ceil($t->intel_wait_seconds / 60) }}m)
                                    </span>
                                @else
                                    <span class="text-slate-500 dark:text-slate-400 font-medium truncate">
                                        <i class="fa-solid fa-eye-slash shrink-0"></i> Afdeling onbekend
                                    </span>
                                @endif

                                @if($t->isProtected())
                                    <span class="text-amber-600 dark:text-amber-400 font-bold shrink-0"><i class="fa-solid fa-shield"></i> Schild</span>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                <!-- Selected Target Status & Quick Action Bar -->
                <div id="selected-target-controls" x-show="selectedTargetId !== ''" class="p-4 rounded-xl bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70 space-y-3">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                        <div class="min-w-0">
                            <span class="text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider">Geselecteerd Doelwit:</span>
                            <div class="text-sm font-extrabold text-slate-900 dark:text-white truncate" x-text="selectedTargetName"></div>
                        </div>

                        <div class="min-w-0">
                            <span x-show="selectedTargetSameWing" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 text-xs font-extrabold border border-emerald-200/80 dark:border-emerald-800/70">
                                <i class="fa-solid fa-check-circle shrink-0"></i>
                                <span>In dezelfde ruimte ({{ $currentWing }}) — +25% Sluipaanval-bonus actief!</span>
                            </span>
                            <span x-show="!selectedTargetSameWing && selectedTargetRevealedWing !== ''" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl bg-amber-50 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 text-xs font-extrabold border border-amber-200/80 dark:border-amber-800/70">
                                <i class="fa-solid fa-location-dot shrink-0"></i>
                                <span>Op <span x-text="selectedTargetRevealedWing"></span> — Afstandsschot mogelijk (Reis voor +25% bonus)</span>
                            </span>
                            <span x-show="!selectedTargetSameWing && selectedTargetRevealedWing === ''" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-800 dark:text-indigo-300 text-xs font-extrabold border border-indigo-200/80 dark:border-indigo-800/70">
                                <i class="fa-solid fa-crosshairs shrink-0"></i>
                                <span>Schot op afstand mogelijk — Start Schaduwing voor locatie &amp; bonus!</span>
                            </span>
                        </div>
                    </div>

                    <!-- Helper action if not in same wing -->
                    <div x-show="!selectedTargetSameWing && selectedTargetRevealedWing !== ''" class="pt-2.5 border-t border-slate-200/80 dark:border-slate-700/70 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                        <span class="text-xs text-slate-600 dark:text-slate-300 font-medium">Wil je de <strong>+25% Sluipaanval-bonus</strong>? Reis direct naar dezelfde vleugel:</span>
                        <form action="{{ route('hitman.quick_travel') }}" method="POST" class="shrink-0">
                            @csrf
                            <input type="hidden" name="destination" :value="selectedTargetRevealedWing">
                            <button type="submit" 
                                    {{ $character->isImprisoned() || $character->isComatose() || $character->needsReviveSerum() || $character->cash < $travelCost ? 'disabled' : '' }}
                                    class="w-full sm:w-auto py-2 px-3.5 rounded-xl {{ ($character->isImprisoned() || $character->isComatose() || $character->needsReviveSerum() || $character->cash < $travelCost) ? 'bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-slate-500 cursor-not-allowed' : 'bg-emerald-600 hover:bg-emerald-700 dark:bg-emerald-600 dark:hover:bg-emerald-500 text-white cursor-pointer active:scale-95' }} text-xs font-black transition-all duration-150 flex items-center justify-center gap-1.5 whitespace-nowrap shadow-xs">
                                <i class="fa-solid fa-person-running"></i>
                                <span>Reis direct naar <span x-text="selectedTargetRevealedWing"></span> (€{{ number_format($travelCost, 0, ',', '.') }})</span>
                            </button>
                        </form>
                    </div>

                    <div x-show="!selectedTargetSameWing && selectedTargetRevealedWing === ''" class="pt-2.5 border-t border-slate-200/80 dark:border-slate-700/70 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                        <span class="text-xs text-slate-600 dark:text-slate-300 font-medium">Je kunt direct op afstand vuren, of schaduwen om zijn afdeling te vinden (+25% bonus):</span>
                        <form action="{{ route('hitman.intel') }}" method="POST" class="shrink-0">
                            @csrf
                            <input type="hidden" name="target_id" :value="selectedTargetId" value="{{ $initialTarget?->id ?? '' }}">
                            <input type="hidden" name="intel_type" value="standard_15m">
                            <input type="hidden" name="currency" value="cash">
                            <button type="submit" 
                                    {{ $character->isImprisoned() || $character->isComatose() || $character->needsReviveSerum() || $character->cash < 350000 ? 'disabled' : '' }}
                                    class="w-full sm:w-auto py-2 px-3.5 rounded-xl {{ ($character->isImprisoned() || $character->isComatose() || $character->needsReviveSerum() || $character->cash < 350000) ? 'bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-slate-500 cursor-not-allowed' : 'bg-purple-600 hover:bg-purple-700 dark:bg-purple-600 dark:hover:bg-purple-500 text-white cursor-pointer active:scale-95' }} text-xs font-black transition-all duration-150 flex items-center justify-center gap-1.5 whitespace-nowrap shadow-xs">
                                <i class="fa-solid fa-video"></i>
                                <span>Start 15m Schaduwing (€350.000)</span>
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Shooting Form -->
                <form action="{{ route('hitman.shoot') }}" method="POST"
                      @submit="if (!handleHitmanAction($event)) return false;"
                      class="space-y-4 pt-2 border-t border-slate-100 dark:border-slate-800">
                    @csrf
                    <input type="hidden" name="confirm_break_isolation" value="0">
                    <input type="hidden" name="target_id" :value="selectedTargetId" value="{{ $initialTarget?->id ?? '' }}">
                    <input type="hidden" name="target_name" :value="selectedTargetName" value="{{ $initialTarget?->name ?? '' }}">

                    @if($canOneTap)
                    <div class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200/80 dark:border-emerald-800/70 flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-xs">
                        <div>
                            <span class="text-xs font-black text-emerald-900 dark:text-emerald-300 uppercase tracking-wider flex items-center gap-1.5">
                                <i class="fa-solid fa-bolt text-emerald-600 dark:text-emerald-400"></i> Genadeschot Ontgrendeld (Graad {{ $gradeInfo['grade'] }} &bull; {{ $coldBloodedPct }}% Focus)
                            </span>
                            <p class="text-xs text-emerald-800 dark:text-emerald-200/80 mt-0.5">
                                Je kunt met slechts <strong>1 patroon</strong> een dodelijke precisie-executie uitvoeren!
                            </p>
                        </div>
                        <button type="button" @click="bulletAmount = 1"
                                class="py-2 px-3.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 dark:bg-emerald-600 dark:hover:bg-emerald-500 text-white text-xs font-black transition-all duration-150 active:scale-95 cursor-pointer shrink-0 whitespace-nowrap shadow-xs">
                            Zet op 1 Patroon
                        </button>
                    </div>
                    @else
                    <div class="p-4 rounded-xl bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70 flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-xs">
                        <div>
                            <span class="text-xs font-extrabold text-slate-900 dark:text-white flex items-center gap-1.5">
                                <i class="fa-solid fa-lock text-slate-500 dark:text-slate-400"></i> 1-Patroon Genadeschot Vereiste
                            </span>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 leading-relaxed">
                                Vereist <strong class="text-slate-800 dark:text-slate-200">Dossier-Graad 25+</strong> (nu Graad {{ $gradeInfo['grade'] }}) én <strong class="text-slate-800 dark:text-slate-200">minimaal 30% Koudbloedigheid</strong> (nu {{ $coldBloodedPct }}%). Minimaal <strong>10 patronen</strong> per salvo; voor jouw graad is een salvo van <strong class="text-slate-800 dark:text-slate-200">{{ number_format($minBullets, 0, ',', '.') }} patronen</strong> aanbevolen (+20% Barrage Bonus).
                            </p>
                        </div>
                        <button type="button" @click="bulletAmount = {{ $minBullets }}"
                                class="py-2 px-3 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-800 dark:text-slate-100 border border-slate-200/80 dark:border-slate-600 text-xs font-black transition-all duration-150 active:scale-95 cursor-pointer shrink-0 whitespace-nowrap shadow-xs">
                            Aanbevolen ({{ $minBullets }})
                        </button>
                    </div>
                    @endif

                    <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-end">
                        <div class="sm:col-span-5 space-y-1">
                            <label class="block text-xs font-extrabold text-slate-700 dark:text-slate-300">Aantal Patronen Vuren</label>
                            <input type="number" name="bullets" x-model.number="bulletAmount" value="{{ $canOneTap ? 1 : $minBullets }}" min="1" max="10000"
                                   class="w-full p-3 bg-slate-50 dark:bg-slate-800/90 border border-slate-200 dark:border-slate-700 rounded-xl text-base sm:text-sm font-black text-slate-900 dark:text-slate-100 focus:outline-none focus:border-rose-500 dark:focus:border-rose-500 focus:ring-2 focus:ring-rose-500/20 transition shadow-2xs">
                        </div>

                        <div class="sm:col-span-7">
                            <button type="submit"
                                    {{ $character->isImprisoned() || $character->isComatose() || $character->needsReviveSerum() || $character->energy < 20 || ($character->bullets ?? 0) < 1 ? 'disabled' : '' }}
                                    class="w-full py-3.5 px-5 rounded-xl {{ ($character->isImprisoned() || $character->isComatose() || $character->needsReviveSerum() || $character->energy < 20 || ($character->bullets ?? 0) < 1) ? 'bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-slate-500 cursor-not-allowed' : 'bg-rose-600 hover:bg-rose-700 dark:bg-rose-600 dark:hover:bg-rose-500 text-white cursor-pointer active:scale-[0.99]' }} font-black text-xs sm:text-sm uppercase tracking-wider transition-all duration-150 shadow-xs flex items-center justify-center gap-2">
                                <i class="fa-solid fa-crosshairs"></i>
                                <span x-text="bulletAmount === 1 ? 'Voer 1-Patroon Genadeschot Uit' : 'Vuur Salvo (' + bulletAmount + ' Patronen)'">
                                    Vuur Salvo ({{ $canOneTap ? 1 : $minBullets }} Patronen)
                                </span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Right Column (5 cols): Ondergrondse Schietbaan, Reanimatie-Serum & 30 Dossier-Gradaties -->
        <div class="lg:col-span-5 space-y-6">

            <!-- Ondergrondse Schietbaan (Train Koudbloedigheid %) -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-6 shadow-xs space-y-4">
                <div class="flex items-start justify-between gap-3 border-b border-slate-100 dark:border-slate-800 pb-3">
                    <div class="min-w-0">
                        <h3 class="text-sm font-extrabold text-slate-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
                            <i class="fa-solid fa-bullseye text-indigo-600 dark:text-indigo-400 shrink-0"></i>
                            <span class="truncate">Ondergrondse Schietbaan</span>
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Verhoog je Koudbloedigheid (%) om met 1 patroon te kunnen schieten.</p>
                    </div>
                    <span class="px-2.5 py-1 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 border border-indigo-200/80 dark:border-indigo-800/70 text-xs font-black shrink-0 whitespace-nowrap">
                        {{ $coldBloodedPct }}% / 100%
                    </span>
                </div>

                <div class="p-3.5 rounded-xl bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70 text-xs space-y-2">
                    <div class="flex items-center justify-between gap-2 font-bold text-slate-700 dark:text-slate-300">
                        <span>Standaard Schiettraining (+10% Focus &amp; +120 XP):</span>
                        <span class="text-slate-900 dark:text-white font-black shrink-0 whitespace-nowrap">€350.000 + 15 E</span>
                    </div>
                    <div class="flex items-center justify-between gap-2 font-bold text-slate-700 dark:text-slate-300">
                        <span>VIP Precisie-Sessie (+15% Focus &amp; +120 XP):</span>
                        <span class="text-amber-600 dark:text-amber-400 font-black shrink-0 whitespace-nowrap">25 Sterren ⭐</span>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-2.5">
                    <form action="{{ route('hitman.train_cold_blooded') }}" method="POST">
                        @csrf
                        <input type="hidden" name="currency" value="cash">
                        <button type="submit" 
                                {{ $character->isImprisoned() || $character->isComatose() || $character->needsReviveSerum() || $character->energy < 15 || $character->cash < 350000 ? 'disabled' : '' }}
                                class="w-full py-2.5 px-3 rounded-xl {{ ($character->isImprisoned() || $character->isComatose() || $character->needsReviveSerum() || $character->energy < 15 || $character->cash < 350000) ? 'bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-slate-500 cursor-not-allowed' : 'bg-purple-600 hover:bg-purple-700 dark:bg-purple-600 dark:hover:bg-purple-500 text-white cursor-pointer active:scale-95' }} text-xs font-black transition-all duration-150 flex items-center justify-center gap-1.5 whitespace-nowrap shadow-xs">
                            <i class="fa-solid fa-money-bill-wave"></i>
                            <span>Train (€350k)</span>
                        </button>
                    </form>
                    <form action="{{ route('hitman.train_cold_blooded') }}" method="POST">
                        @csrf
                        <input type="hidden" name="currency" value="stars">
                        <button type="submit" 
                                {{ $character->isImprisoned() || $character->isComatose() || $character->needsReviveSerum() || ($character->stars ?? 0) < 25 ? 'disabled' : '' }}
                                class="w-full py-2.5 px-3 rounded-xl {{ ($character->isImprisoned() || $character->isComatose() || $character->needsReviveSerum() || ($character->stars ?? 0) < 25) ? 'bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-slate-500 cursor-not-allowed' : 'bg-amber-500 hover:bg-amber-400 text-slate-950 cursor-pointer active:scale-95' }} text-xs font-black transition-all duration-150 flex items-center justify-center gap-1.5 whitespace-nowrap shadow-xs">
                            <i class="fa-solid fa-star text-amber-200"></i>
                            <span>Train (25 ⭐)</span>
                        </button>
                    </form>
                </div>
            </div>

            <!-- Forensisch Reanimatie-Serum (Epinefrine-Ampul) Card -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-6 shadow-xs space-y-4">
                <div class="flex items-start justify-between gap-3 border-b border-slate-100 dark:border-slate-800 pb-3">
                    <div class="min-w-0">
                        <h3 class="text-sm font-extrabold text-slate-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
                            <i class="fa-solid fa-syringe text-rose-600 dark:text-rose-400 shrink-0"></i>
                            <span>Reanimatie-Serum (Epinefrine-Ampul)</span>
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Reset je 10x liquidatieteller naar 0/10 en wek jezelf direct tot leven bij Code Blauw.</p>
                    </div>
                    <span class="px-2.5 py-1 rounded-xl bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border border-rose-200/80 dark:border-rose-800/70 text-xs font-black shrink-0 whitespace-nowrap">
                        {{ $streakDeaths }} / 10
                    </span>
                </div>

                <div class="p-3.5 rounded-xl bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70 text-xs space-y-2">
                    <div class="flex items-center justify-between gap-2 font-bold text-slate-700 dark:text-slate-300">
                        <span>Prijs in geld (Contant + Bank):</span>
                        <span class="text-slate-900 dark:text-white font-black shrink-0 whitespace-nowrap">€{{ number_format(\App\Models\Character::REVIVE_SERUM_CASH, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex items-center justify-between gap-2 font-bold text-slate-700 dark:text-slate-300">
                        <span>Prijs in sterren:</span>
                        <span class="text-amber-600 dark:text-amber-400 font-black shrink-0 whitespace-nowrap">{{ \App\Models\Character::REVIVE_SERUM_STARS }} Sterren ⭐</span>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-2.5">
                    <form action="{{ route('hitman.revive_serum') }}" method="POST">
                        @csrf
                        <input type="hidden" name="currency" value="cash">
                        <button type="submit" class="w-full py-2.5 px-3 rounded-xl bg-purple-600 hover:bg-purple-700 dark:bg-purple-600 dark:hover:bg-purple-500 text-white text-xs font-black transition-all duration-150 active:scale-95 cursor-pointer flex items-center justify-center gap-1.5 whitespace-nowrap shadow-xs">
                            <i class="fa-solid fa-syringe"></i>
                            <span>€1.500.000</span>
                        </button>
                    </form>
                    <form action="{{ route('hitman.revive_serum') }}" method="POST">
                        @csrf
                        <input type="hidden" name="currency" value="stars">
                        <button type="submit" class="w-full py-2.5 px-3 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 text-xs font-black transition-all duration-150 active:scale-95 cursor-pointer flex items-center justify-center gap-1.5 whitespace-nowrap shadow-xs">
                            <i class="fa-solid fa-star text-amber-200"></i>
                            <span>{{ \App\Models\Character::REVIVE_SERUM_STARS }} Sterren</span>
                        </button>
                    </form>
                </div>
            </div>

            <!-- 30 TBS Dossier-Gradaties Ladder -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-6 shadow-xs space-y-3">
                <div class="flex items-center justify-between gap-2 border-b border-slate-100 dark:border-slate-800 pb-3">
                    <h3 class="text-sm font-extrabold text-slate-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
                        <i class="fa-solid fa-folder-open text-purple-600 dark:text-purple-400"></i> De 30 TBS Dossier-Gradaties
                    </h3>
                    <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400 shrink-0">Graad 25+ = 1-Patroon Schot</span>
                </div>

                <div class="max-h-64 overflow-y-auto divide-y divide-slate-100 dark:divide-slate-800 pr-1 text-xs">
                    @foreach($gradesCatalog as $g)
                    <div class="py-2 px-2.5 flex items-center justify-between rounded-lg {{ $g['grade'] === $gradeInfo['grade'] ? 'bg-purple-50 dark:bg-purple-900/50 border border-purple-200 dark:border-purple-700 font-extrabold' : '' }}">
                        <div class="flex items-center gap-2 min-w-0">
                            <span class="w-6 h-6 rounded-md {{ $g['can_one_tap'] ? 'bg-emerald-600 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-purple-200' }} flex items-center justify-center text-[10px] font-black shrink-0">
                                {{ $g['grade'] }}
                            </span>
                            <div class="truncate">
                                <span class="text-slate-900 dark:text-white font-bold">{{ $g['title'] }}</span>
                                @if($g['grade'] === $gradeInfo['grade'])
                                    <span class="ml-1 text-[10px] text-purple-700 dark:text-purple-300 font-black">(Jij)</span>
                                @endif
                            </div>
                        </div>
                        <div class="text-right shrink-0 ml-2">
                            @if($g['can_one_tap'])
                                <span class="text-emerald-600 dark:text-emerald-400 font-black">1 Patroon (bij &ge;30%)</span>
                            @else
                                <span class="text-slate-600 dark:text-slate-400 font-bold">{{ number_format($g['min_bullets'], 0, ',', '.') }} patronen</span>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- TAB 2: SCHADUW-INLICHTINGEN (15 MINUTEN OPSPORING & VLEUGEL-LOCKDOWN)     -->
    <!-- ========================================================================= -->
    <div x-show="activeTab === 'inlichtingen'" class="space-y-6">

        <!-- Start New Intel Operation -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-6 shadow-xs space-y-5">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-100 dark:border-slate-800 pb-4">
                <div>
                    <h2 class="text-base font-extrabold text-slate-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
                        <i class="fa-solid fa-user-secret text-indigo-600 dark:text-indigo-400"></i> Schaduw-Inlichtingen Operatie Starten
                    </h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                        Om een speler te liquideren moet je weten in welke kliniek-vleugel hij zich bevindt. Onze corrupte bewaarders lokaliseren elk doelwit binnen 15 minuten!
                    </p>
                </div>
            </div>

            <div class="max-w-md space-y-1.5">
                <label class="block text-xs font-extrabold text-slate-700 dark:text-slate-300 uppercase">Kies of typ de naam van het doelwit</label>
                <input type="text" x-model="selectedTargetName" placeholder="Typ exacte spelersnaam..."
                       class="w-full p-3 bg-slate-50 dark:bg-slate-800/90 border border-slate-200 dark:border-slate-700 rounded-xl text-base sm:text-sm font-bold text-slate-900 dark:text-slate-100 placeholder:text-slate-400 dark:placeholder:text-slate-500 focus:outline-none focus:border-purple-500 dark:focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition shadow-2xs">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @foreach($intelCatalog as $intel)
                @php
                    $canCash = $totalWealth >= $intel['cost_cash'];
                    $canStars = ($character->stars ?? 0) >= $intel['cost_stars'];
                @endphp
                <div class="p-5 rounded-2xl border border-slate-200/80 dark:border-slate-700/70 bg-slate-50/90 dark:bg-slate-800/60 flex flex-col justify-between gap-4 shadow-xs">
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 border border-indigo-200/80 dark:border-indigo-800/70 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-base font-bold shadow-2xs">
                                <i class="{{ $intel['icon'] }}"></i>
                            </span>
                            <span class="px-2.5 py-1 rounded-full bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-700 text-xs font-extrabold text-slate-800 dark:text-slate-200 shadow-2xs">
                                <i class="fa-solid fa-clock text-indigo-600 dark:text-indigo-400 mr-1"></i> {{ $intel['wait_minutes'] }} min zoektijd
                            </span>
                        </div>
                        <h3 class="text-sm font-extrabold text-slate-900 dark:text-white">{{ $intel['name'] }}</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">{{ $intel['subtitle'] }}</p>
                    </div>

                    <div class="p-3 rounded-xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-700/70 text-xs space-y-1">
                        <div class="flex items-center justify-between font-bold text-slate-700 dark:text-slate-300">
                            <span>Kosten in geld:</span>
                            <span class="text-slate-900 dark:text-white font-black">€{{ number_format($intel['cost_cash'], 0, ',', '.') }}</span>
                        </div>
                        <div class="flex items-center justify-between font-bold text-slate-700 dark:text-slate-300">
                            <span>Kosten in sterren:</span>
                            <span class="text-amber-600 dark:text-amber-400 font-black">{{ $intel['cost_stars'] }} Sterren ⭐</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-2 pt-2 border-t border-slate-200/80 dark:border-slate-700/70">
                        <form action="{{ route('hitman.intel') }}" method="POST">
                            @csrf
                            <input type="hidden" name="target_id" :value="selectedTargetId">
                            <input type="hidden" name="target_name" :value="selectedTargetName">
                            <input type="hidden" name="intel_type" value="{{ $intel['id'] }}">
                            <input type="hidden" name="currency" value="cash">
                            <button type="submit" {{ ! $canCash ? 'disabled' : '' }}
                                    class="w-full py-2.5 px-3 rounded-xl text-xs font-black transition-all duration-150 active:scale-95 flex items-center justify-center gap-1.5 shadow-xs {{ $canCash ? 'bg-purple-600 hover:bg-purple-700 dark:bg-purple-600 dark:hover:bg-purple-500 text-white cursor-pointer' : 'bg-slate-100 dark:bg-slate-800/80 text-slate-400 dark:text-slate-500 border border-slate-200/80 dark:border-slate-700/60 cursor-not-allowed' }}">
                                <i class="fa-solid fa-money-bill-wave"></i>
                                <span>€{{ number_format($intel['cost_cash'] / 1000, 0, ',', '.') }}k</span>
                            </button>
                        </form>

                        <form action="{{ route('hitman.intel') }}" method="POST">
                            @csrf
                            <input type="hidden" name="target_id" :value="selectedTargetId">
                            <input type="hidden" name="target_name" :value="selectedTargetName">
                            <input type="hidden" name="intel_type" value="{{ $intel['id'] }}">
                            <input type="hidden" name="currency" value="stars">
                            <button type="submit" {{ ! $canStars ? 'disabled' : '' }}
                                    class="w-full py-2.5 px-3 rounded-xl text-xs font-black transition-all duration-150 active:scale-95 flex items-center justify-center gap-1.5 shadow-xs {{ $canStars ? 'bg-amber-500 hover:bg-amber-400 text-slate-950 cursor-pointer' : 'bg-slate-100 dark:bg-slate-800/80 text-slate-400 dark:text-slate-500 border border-slate-200/80 dark:border-slate-700/60 cursor-not-allowed' }}">
                                <i class="fa-solid fa-star text-amber-200"></i>
                                <span>{{ $intel['cost_stars'] }} ⭐</span>
                            </button>
                        </form>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Active & Ready Intel Dossiers -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-6 shadow-xs space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                <h3 class="text-sm font-extrabold text-slate-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
                    <i class="fa-solid fa-satellite-dish text-emerald-600 dark:text-emerald-400"></i> Jouw Actieve Observatie-Dossiers
                </h3>
                <span class="text-xs text-slate-500 dark:text-slate-400">{{ count($enrichedIntelOps) }} actief</span>
            </div>

            @if(empty($enrichedIntelOps))
                <div class="p-8 rounded-xl bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70 text-center space-y-2">
                    <i class="fa-solid fa-folder-closed text-2xl text-slate-400 dark:text-slate-500"></i>
                    <div class="text-sm font-extrabold text-slate-800 dark:text-slate-200">Geen lopende Schaduw-Inlichtingen</div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 max-w-md mx-auto">
                        Start hierboven een 15-minuten Camera-Schaduwing om te achterhalen op welke kliniek-afdeling je doelwit rondloopt.
                    </p>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($enrichedIntelOps as $op)
                    <div x-data="{
                            waitSec: {{ (int) $op['wait_remaining_sec'] }},
                            validSec: {{ (int) $op['valid_remaining_sec'] }},
                            fmt(s) {
                                if (s <= 0) return '00:00';
                                const m = Math.floor(s / 60);
                                const r = s % 60;
                                return (m < 10 ? '0' : '') + m + ':' + (r < 10 ? '0' : '') + r;
                            },
                            init() {
                                setInterval(() => {
                                    if (this.waitSec > 0) {
                                        this.waitSec--;
                                        if (this.waitSec === 0) window.location.reload();
                                    } else if (this.validSec > 0) {
                                        this.validSec--;
                                    }
                                }, 1000);
                            }
                         }"
                         class="p-5 rounded-2xl border {{ $op['is_ready'] ? 'border-emerald-300 dark:border-emerald-800 bg-emerald-50/40 dark:bg-emerald-950/30' : 'border-slate-200/80 dark:border-slate-700/70 bg-slate-50/90 dark:bg-slate-800/60' }} flex flex-col justify-between gap-4 shadow-xs">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <span class="text-[10px] font-black uppercase tracking-wider px-2.5 py-0.5 rounded-full {{ $op['is_ready'] ? 'bg-emerald-600 text-white' : 'bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 border border-indigo-200/80 dark:border-indigo-800/70' }}">
                                    {{ $op['is_ready'] ? 'RAPPORT BINNEN' : 'BEZIG MET SCHADUWEN...' }}
                                </span>
                                <h4 class="text-base font-extrabold text-slate-900 dark:text-white mt-1.5">{{ $op['target_name'] }}</h4>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400">{{ $op['type_name'] }}</p>
                            </div>

                            <div class="text-right">
                                @if(! $op['is_ready'])
                                    <div class="text-xs font-bold text-slate-500 dark:text-slate-400">Klaar over:</div>
                                    <div class="text-lg font-mono font-black text-indigo-600 dark:text-indigo-400" x-text="fmt(waitSec)"></div>
                                @else
                                    <div class="text-xs font-bold text-slate-500 dark:text-slate-400">Geldig:</div>
                                    <div class="text-sm font-mono font-black text-emerald-700 dark:text-emerald-400" x-text="fmt(validSec)"></div>
                                @endif
                            </div>
                        </div>

                        @if($op['is_ready'])
                            <div class="p-3.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-700/70 text-xs space-y-1.5 shadow-2xs">
                                <div class="flex items-center justify-between">
                                    <span class="text-slate-500 dark:text-slate-400 font-bold">Huidige Kliniek-Ruimte:</span>
                                    <span class="font-black text-emerald-700 dark:text-emerald-400"><i class="fa-solid fa-location-dot mr-1"></i> {{ $op['revealed_location'] }}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-slate-500 dark:text-slate-400 font-bold">Contant / Bank op naam:</span>
                                    <span class="font-black text-slate-900 dark:text-white">{{ $op['cash_formatted'] }} / {{ $op['bank_formatted'] }}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-slate-500 dark:text-slate-400 font-bold">Gedragen Pantser:</span>
                                    <span class="font-black {{ $op['has_vest'] ? 'text-amber-600 dark:text-amber-400' : 'text-slate-700 dark:text-slate-300' }}">
                                        {{ $op['has_vest'] ? $op['vest_name'] : 'Geen Vest' }}
                                    </span>
                                </div>
                                @if($op['is_wing_locked'])
                                <div class="flex items-center justify-between text-rose-600 dark:text-rose-400 font-black pt-1 border-t border-slate-100 dark:border-slate-800">
                                    <span>Celblok-Lockdown:</span>
                                    <span><i class="fa-solid fa-lock"></i> Doelwit zit vast op deze afdeling!</span>
                                </div>
                                @endif
                            </div>

                            <div class="flex items-center gap-2">
                                @if(! $op['same_wing'])
                                    <form action="{{ route('hitman.quick_travel') }}" method="POST" class="flex-1">
                                        @csrf
                                        <input type="hidden" name="destination" value="{{ $op['revealed_location'] }}">
                                        <button type="submit" class="w-full py-2.5 px-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 dark:bg-emerald-600 dark:hover:bg-emerald-500 text-white text-xs font-black transition-all duration-150 active:scale-95 cursor-pointer flex items-center justify-center gap-1.5 shadow-xs">
                                            <i class="fa-solid fa-person-running"></i>
                                            <span>Reis naar {{ $op['revealed_location'] }}</span>
                                        </button>
                                    </form>
                                @endif
                                <button type="button"
                                        @click="selectTarget(@js((string) $op['target_id']), @js($op['target_name']), {{ $op['same_wing'] ? 'true' : 'false' }}, @js($op['revealed_location'] ?? '')); activeTab = 'liquidatie';"
                                        class="flex-1 py-2.5 px-3 rounded-xl bg-rose-600 hover:bg-rose-700 dark:bg-rose-600 dark:hover:bg-rose-500 text-white text-xs font-black transition-all duration-150 active:scale-95 cursor-pointer flex items-center justify-center gap-1.5 shadow-xs">
                                    <i class="fa-solid fa-crosshairs"></i>
                                    <span>Direct Viseren</span>
                                </button>
                            </div>
                        @else
                            <div class="p-3 rounded-xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-700/70 text-xs text-slate-500 dark:text-slate-400 flex items-center gap-2">
                                <i class="fa-solid fa-hourglass-half text-indigo-500"></i>
                                <span>Bewaarders bekijken momenteel de camerabeelden en gangpaden om {{ $op['target_name'] }} te lokaliseren...</span>
                            </div>
                        @endif
                    </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- TAB 3: PANTSER & MUNITIE ARSENAAL (INCL. 3-UUR BACKFIRE VEST VOOR GELD)   -->
    <!-- ========================================================================= -->
    <div x-show="activeTab === 'arsenaal'" class="space-y-6">

        <!-- Backfire Vests Section -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-6 shadow-xs space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-100 dark:border-slate-800 pb-3">
                <div>
                    <h2 class="text-sm font-extrabold text-slate-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
                        <i class="fa-solid fa-shield-halved text-indigo-600 dark:text-indigo-400"></i> Zwarte Markt Backfire-Vesten &amp; Pantsers
                    </h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                        Bescherm jezelf tegen liquidaties. Als een schutter te weinig vuurkracht heeft, ketst het schot terug (Backfire) en raakt de aanvaller zelf zwaargewond!
                    </p>
                </div>
                <span class="text-xs text-slate-400 dark:text-slate-500 font-medium">Betaal met Geld (Contant/Bank) of Sterren</span>
            </div>

            @if($activeVest)
                <div x-data="{
                        left: {{ (int) $character->getVestRemainingSeconds() }},
                        format(s) {
                            if (s <= 0) return 'Verlopen';
                            const h = Math.floor(s / 3600);
                            const m = Math.floor((s % 3600) / 60);
                            const sec = Math.floor(s % 60);
                            const pad = (n) => String(n).padStart(2, '0');
                            return `${pad(h)}:${pad(m)}:${pad(sec)} (${h}u ${m}m ${sec}s)`;
                        },
                        init() {
                            setInterval(() => { if (this.left > 0) this.left--; }, 1000);
                        }
                    }"
                    class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200/80 dark:border-emerald-800/70 flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-xs">
                    <div class="flex items-center gap-3">
                        <span class="w-9 h-9 rounded-lg bg-emerald-100 dark:bg-emerald-900/60 text-emerald-700 dark:text-emerald-300 flex items-center justify-center shrink-0">
                            <i class="fa-solid fa-shield-halved"></i>
                        </span>
                        <div>
                            <div class="text-xs font-black text-emerald-900 dark:text-emerald-200 uppercase tracking-wider">Actief Pantser: {{ $activeVest['name'] }}</div>
                            <div class="text-[11px] text-emerald-700 dark:text-emerald-400">Verloopt om {{ $character->vest_until->format('d-m-Y H:i:s') }} uur • Nieuwe aankoop verlengt direct!</div>
                        </div>
                    </div>
                    <div class="px-3.5 py-2 rounded-xl bg-white dark:bg-slate-900 border border-emerald-200/80 dark:border-emerald-800/70 text-right shrink-0 shadow-2xs">
                        <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Resterende Vest-Tijd</div>
                        <div class="text-sm font-black font-mono text-emerald-700 dark:text-emerald-300 tabular-nums" x-text="format(left)">
                            {{ gmdate('H:i:s', $character->getVestRemainingSeconds()) }}
                        </div>
                    </div>
                </div>
            @endif

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                @foreach($vestCatalog as $vKey => $vest)
                @php
                    $canCashVest = ! $character->isImprisoned() && ! $character->isComatose() && ! $character->needsReviveSerum() && $totalWealth >= $vest['cost_cash'];
                    $canStarVest = ! $character->isImprisoned() && ! $character->isComatose() && ! $character->needsReviveSerum() && ($character->stars ?? 0) >= $vest['cost_stars'];
                    $isPopular = ($vKey === 'tactical_3h');
                @endphp
                <div class="p-5 rounded-2xl border {{ $isPopular ? 'border-purple-500 dark:border-purple-600 ring-1 ring-purple-400 dark:ring-purple-600/50' : 'border-slate-200/80 dark:border-slate-700/70' }} bg-slate-50/90 dark:bg-slate-800/60 flex flex-col justify-between gap-4 shadow-xs relative">
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="w-9 h-9 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 border border-indigo-200/80 dark:border-indigo-800/70 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-sm font-bold shadow-2xs">
                                <i class="{{ $vest['icon'] }}"></i>
                            </span>
                            <span class="px-2.5 py-0.5 rounded-full {{ $isPopular ? 'bg-purple-600 text-white' : 'bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-700 text-slate-800 dark:text-slate-200' }} text-[10px] font-black shadow-2xs">
                                {{ $isPopular ? '🔥 POPULAIRST (3 UUR)' : $vest['badge'] }}
                            </span>
                        </div>
                        <h3 class="text-sm font-extrabold text-slate-900 dark:text-white">{{ $vest['name'] }}</h3>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 leading-relaxed">{{ $vest['subtitle'] }}</p>
                    </div>

                    <div class="p-3 rounded-xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-700/70 text-xs space-y-1 shadow-2xs">
                        <div class="flex items-center justify-between font-bold text-slate-700 dark:text-slate-300">
                            <span>Verdediging / Backfire:</span>
                            <span class="text-emerald-700 dark:text-emerald-400 font-black">+{{ $vest['defense_bonus'] }} Def • {{ $vest['backfire_chance'] }}%</span>
                        </div>
                        <div class="flex items-center justify-between font-bold text-slate-700 dark:text-slate-300">
                            <span>Prijs in geld:</span>
                            <span class="text-slate-900 dark:text-white font-black">€{{ number_format($vest['cost_cash'], 0, ',', '.') }}</span>
                        </div>
                        <div class="flex items-center justify-between font-bold text-slate-700 dark:text-slate-300">
                            <span>Prijs in sterren:</span>
                            <span class="text-amber-600 dark:text-amber-400 font-black">{{ $vest['cost_stars'] }} Sterren ⭐</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-2 pt-2 border-t border-slate-200/80 dark:border-slate-700/70">
                        <form action="{{ route('hitman.buy_vest') }}" method="POST">
                            @csrf
                            <input type="hidden" name="vest_tier" value="{{ $vest['id'] }}">
                            <input type="hidden" name="currency" value="cash">
                            <button type="submit" {{ ! $canCashVest ? 'disabled' : '' }}
                                    class="w-full py-2.5 px-2 rounded-xl text-xs font-black transition-all duration-150 active:scale-95 flex items-center justify-center gap-1 shadow-xs {{ $canCashVest ? 'bg-purple-600 hover:bg-purple-700 dark:bg-purple-600 dark:hover:bg-purple-500 text-white cursor-pointer' : 'bg-slate-100 dark:bg-slate-800/80 text-slate-400 dark:text-slate-500 border border-slate-200/80 dark:border-slate-700/60 cursor-not-allowed' }}">
                                <i class="fa-solid fa-money-bill-wave"></i>
                                <span>€{{ number_format($vest['cost_cash'] / 1000, 0, ',', '.') }}k</span>
                            </button>
                        </form>

                        <form action="{{ route('hitman.buy_vest') }}" method="POST">
                            @csrf
                            <input type="hidden" name="vest_tier" value="{{ $vest['id'] }}">
                            <input type="hidden" name="currency" value="stars">
                            <button type="submit" {{ ! $canStarVest ? 'disabled' : '' }}
                                    class="w-full py-2.5 px-2 rounded-xl text-xs font-black transition-all duration-150 active:scale-95 flex items-center justify-center gap-1 shadow-xs {{ $canStarVest ? 'bg-amber-500 hover:bg-amber-400 text-slate-950 cursor-pointer' : 'bg-slate-100 dark:bg-slate-800/80 text-slate-400 dark:text-slate-500 border border-slate-200/80 dark:border-slate-700/60 cursor-not-allowed' }}">
                                <i class="fa-solid fa-star text-amber-200"></i>
                                <span>{{ $vest['cost_stars'] }} ⭐</span>
                            </button>
                        </form>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Ammunition Crates Section -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-6 shadow-xs space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-100 dark:border-slate-800 pb-3">
                <div>
                    <h2 class="text-sm font-extrabold text-slate-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
                        <i class="fa-solid fa-boxes-stacked text-rose-600 dark:text-rose-400"></i> Zwarte Markt Munitiekisten
                    </h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                        Sla zware patronen in voor salvo's of precisie-executies. Grote kisten verhogen direct je Koudbloedigheid (%)!
                    </p>
                </div>
                <span class="text-xs font-extrabold text-slate-700 dark:text-slate-300">Voorraad: {{ number_format($character->bullets ?? 0, 0, ',', '.') }} patronen</span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                @foreach($ammoCatalog as $ammo)
                @php
                    $canCashAmmo = ! $character->isImprisoned() && ! $character->isComatose() && ! $character->needsReviveSerum() && $totalWealth >= $ammo['cost_cash'];
                    $canStarAmmo = ! $character->isImprisoned() && ! $character->isComatose() && ! $character->needsReviveSerum() && ($character->stars ?? 0) >= $ammo['cost_stars'];
                @endphp
                <div class="p-5 rounded-2xl border border-slate-200/80 dark:border-slate-700/70 bg-slate-50/90 dark:bg-slate-800/60 flex flex-col justify-between gap-4 shadow-xs">
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="w-9 h-9 rounded-xl bg-rose-50 dark:bg-rose-950/60 border border-rose-200/80 dark:border-rose-800/70 text-rose-600 dark:text-rose-400 flex items-center justify-center text-sm font-bold shadow-2xs">
                                <i class="{{ $ammo['icon'] }}"></i>
                            </span>
                            <span class="px-2.5 py-0.5 rounded-full bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-700 text-[11px] font-black text-slate-800 dark:text-slate-200 shadow-2xs">
                                +{{ number_format($ammo['bullets'], 0, ',', '.') }} Patronen
                            </span>
                        </div>
                        <h3 class="text-sm font-extrabold text-slate-900 dark:text-white">{{ $ammo['name'] }}</h3>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 leading-relaxed">{{ $ammo['subtitle'] }}</p>
                    </div>

                    <div class="p-3 rounded-xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-700/70 text-xs space-y-1 shadow-2xs">
                        <div class="flex items-center justify-between font-bold text-slate-700 dark:text-slate-300">
                            <span>Prijs in geld:</span>
                            <span class="text-slate-900 dark:text-white font-black">€{{ number_format($ammo['cost_cash'], 0, ',', '.') }}</span>
                        </div>
                        <div class="flex items-center justify-between font-bold text-slate-700 dark:text-slate-300">
                            <span>Prijs in sterren:</span>
                            <span class="text-amber-600 dark:text-amber-400 font-black">{{ $ammo['cost_stars'] }} Sterren ⭐</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-2 pt-2 border-t border-slate-200/80 dark:border-slate-700/70">
                        <form action="{{ route('hitman.buy_bullets') }}" method="POST">
                            @csrf
                            <input type="hidden" name="ammo_tier" value="{{ $ammo['id'] }}">
                            <input type="hidden" name="currency" value="cash">
                            <button type="submit" {{ ! $canCashAmmo ? 'disabled' : '' }}
                                    class="w-full py-2.5 px-2 rounded-xl text-xs font-black transition-all duration-150 active:scale-95 flex items-center justify-center gap-1 shadow-xs {{ $canCashAmmo ? 'bg-purple-600 hover:bg-purple-700 dark:bg-purple-600 dark:hover:bg-purple-500 text-white cursor-pointer' : 'bg-slate-100 dark:bg-slate-800/80 text-slate-400 dark:text-slate-500 border border-slate-200/80 dark:border-slate-700/60 cursor-not-allowed' }}">
                                <i class="fa-solid fa-money-bill-wave"></i>
                                <span>€{{ number_format($ammo['cost_cash'] / 1000, 0, ',', '.') }}k</span>
                            </button>
                        </form>

                        <form action="{{ route('hitman.buy_bullets') }}" method="POST">
                            @csrf
                            <input type="hidden" name="ammo_tier" value="{{ $ammo['id'] }}">
                            <input type="hidden" name="currency" value="stars">
                            <button type="submit" {{ ! $canStarAmmo ? 'disabled' : '' }}
                                    class="w-full py-2.5 px-2 rounded-xl text-xs font-black transition-all duration-150 active:scale-95 flex items-center justify-center gap-1 shadow-xs {{ $canStarAmmo ? 'bg-amber-500 hover:bg-amber-400 text-slate-950 cursor-pointer' : 'bg-slate-100 dark:bg-slate-800/80 text-slate-400 dark:text-slate-500 border border-slate-200/80 dark:border-slate-700/60 cursor-not-allowed' }}">
                                <i class="fa-solid fa-star text-amber-200"></i>
                                <span>{{ $ammo['cost_stars'] }} ⭐</span>
                            </button>
                        </form>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- TAB 4: ONDERWERELD CONTRACTEN                                             -->
    <!-- ========================================================================= -->
    <div x-show="activeTab === 'contracten'" class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-6 shadow-xs space-y-5">
        <div class="border-b border-slate-100 dark:border-slate-800 pb-3">
            <h2 class="text-sm font-extrabold text-slate-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
                <i class="fa-solid fa-file-contract text-purple-600 dark:text-purple-400"></i> Onderwereld Intimidatie-Contracten
            </h2>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                Laat een handlanger een rivaal verzwakken zonder zelf kogels te verbruiken.
            </p>
        </div>

        <div class="max-w-md space-y-1.5">
            <label class="block text-xs font-extrabold text-slate-700 dark:text-slate-300 uppercase">Doelwit Naam</label>
            <input type="text" x-model="selectedTargetName" placeholder="Typ spelersnaam..."
                   class="w-full p-3 bg-slate-50 dark:bg-slate-800/90 border border-slate-200 dark:border-slate-700 rounded-xl text-base sm:text-sm font-bold text-slate-900 dark:text-slate-100 placeholder:text-slate-400 dark:placeholder:text-slate-500 focus:outline-none focus:border-purple-500 dark:focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition shadow-2xs">
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @foreach($contracts as $c)
            <div class="p-5 rounded-2xl border border-slate-200/80 dark:border-slate-700/70 bg-slate-50/90 dark:bg-slate-800/60 flex flex-col justify-between gap-4 shadow-xs">
                <div class="space-y-1.5">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-extrabold text-slate-900 dark:text-white">{{ $c['name'] }}</span>
                        <span class="px-2 py-0.5 rounded-lg bg-rose-50 dark:bg-rose-950/60 border border-rose-200/80 dark:border-rose-800/70 text-[11px] font-black text-rose-600 dark:text-rose-400">
                            -{{ $c['damage'] }} HP
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ $c['desc'] }}</p>
                </div>

                <div class="p-3 rounded-xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-700/70 text-xs space-y-1 shadow-2xs">
                    <div class="flex justify-between font-bold text-slate-700 dark:text-slate-300">
                        <span>Kosten:</span>
                        <span class="text-slate-900 dark:text-white font-black">€{{ number_format($c['cost'], 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between font-bold text-slate-700 dark:text-slate-300">
                        <span>Energie / Slagingskans:</span>
                        <span class="text-slate-900 dark:text-white font-black">{{ $c['energy'] }} E • {{ $c['rate'] }}%</span>
                    </div>
                </div>

                <form action="{{ route('hitman.contract') }}" method="POST"
                      @submit="if (!handleHitmanAction($event)) return false;">
                    @csrf
                    <input type="hidden" name="confirm_break_isolation" value="0">
                    <input type="hidden" name="target_id" :value="selectedTargetId">
                    <input type="hidden" name="target_name" :value="selectedTargetName">
                    <input type="hidden" name="contract_id" value="{{ $c['id'] }}">
                    <button type="submit" 
                            {{ $character->isImprisoned() || $character->isComatose() || $character->needsReviveSerum() || $character->cash < $c['cost'] || $character->energy < $c['energy'] ? 'disabled' : '' }}
                            class="w-full py-2.5 px-4 rounded-xl {{ ($character->isImprisoned() || $character->isComatose() || $character->needsReviveSerum() || $character->cash < $c['cost'] || $character->energy < $c['energy']) ? 'bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-slate-500 cursor-not-allowed' : 'bg-rose-600 hover:bg-rose-700 dark:bg-rose-600 dark:hover:bg-rose-500 text-white cursor-pointer active:scale-95' }} text-xs font-black transition-all duration-150 shadow-xs">
                        Voer Contract Uit (€{{ number_format($c['cost'], 0, ',', '.') }})
                    </button>
                </form>
            </div>
            @endforeach
        </div>
    </div>

    <!-- MODAL: ISOLATIE VERBREKEN BEVESTIGING -->
    <div x-show="isolationModalOpen"
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/80 backdrop-blur-xs transition-opacity duration-200"
         x-transition:enter="ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        <div class="bg-white dark:bg-slate-900 border-2 border-amber-300 dark:border-amber-700 rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-4 relative"
             @click.away="isolationModalOpen = false"
             x-transition:enter="ease-out duration-200"
             x-transition:enter-start="scale-95 opacity-0"
             x-transition:enter-end="scale-100 opacity-100">
            
            <div class="flex items-center gap-3.5">
                <div class="w-12 h-12 rounded-2xl bg-amber-500 text-white flex items-center justify-center text-xl shrink-0 shadow-xs">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
                <div>
                    <h3 class="text-base font-extrabold text-slate-900 dark:text-white">Isolatie Vroegtijdig Beëindigen?</h3>
                    <span class="text-xs font-bold text-amber-600 dark:text-amber-400">Let op: je verlaat je veilige cel!</span>
                </div>
            </div>

            <div class="p-3.5 rounded-2xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800 text-xs text-amber-900 dark:text-amber-200 space-y-2">
                <p>
                    Je hebt momenteel <strong>Vrijwillige Isolatie</strong> aanstaan (tot <strong>{{ $character->protection_until?->format('d-m H:i') }}</strong>).
                </p>
                <p>
                    Als je nu <span class="font-extrabold text-slate-900 dark:text-white" x-text="selectedTargetName ? selectedTargetName : 'dit doelwit'"></span> aanvalt of liquideert, verlaat je je cel en <strong>vervalt je isolatiebescherming direct</strong>. Andere patiënten kunnen jou daarna ook weer aanvallen!
                </p>
            </div>

            <div class="grid grid-cols-2 gap-2 pt-2">
                <button type="button"
                        @click="isolationModalOpen = false"
                        class="py-3 px-4 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-extrabold text-xs transition cursor-pointer">
                    Nee, veilig blijven
                </button>
                <button type="button"
                        @click="confirmBreakAndHit()"
                        class="py-3 px-4 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-black text-xs transition shadow-xs active:scale-95 cursor-pointer">
                    Ja, val toch aan!
                </button>
            </div>
        </div>
    </div>

</div>
@endsection
