@extends('layouts.app')

@section('title', 'Goederensluis & Kraakkamer - TBS Kliniek')

@section('content')
@php
    $pkgCooldown = $character->cooldownRemaining('package');
    $methods = $crackMethods ?? \App\Http\Controllers\PackageController::CRACK_METHODS;
@endphp

<div class="space-y-6" x-data="{
    seconds: {{ (int) $pkgCooldown }},
    selectedMethod: 'precisie',
    selectedChest: 1,
    methodInfo: {
        precisie: { name: 'Voorzichtig Openen (Precisie-Loper)', rate: 100, multLabel: '1.00x Garantiebuit', risk: '0% kans op schade' },
        thermisch: { name: 'Thermische Snijbrander', rate: 75, multLabel: '1.35x Overwinst', risk: '25% kans op schroeischade' },
        forceren: { name: 'Ruwe Kracht & Forceren', rate: 60, multLabel: '1.75x Topbuit', risk: '40% kans op verfvlam-schade' }
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

    <!-- Top Command Header -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-5 sm:p-7 shadow-xs">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-5">
            <div class="space-y-2">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="text-[10px] font-black uppercase tracking-wider text-indigo-700 dark:text-indigo-300 bg-indigo-50 dark:bg-indigo-950/60 px-2.5 py-1 rounded-full border border-indigo-200/80 dark:border-indigo-800/70">
                        <i class="fa-solid fa-boxes-packing mr-1"></i> Smokkelkoffers &amp; Postpakketten
                    </span>
                    <span class="text-[10px] font-extrabold text-amber-800 dark:text-amber-300 bg-amber-50 dark:bg-amber-950/60 px-2.5 py-1 rounded-full border border-amber-200/80 dark:border-amber-800/70">
                        3 Kraak-Technieken &amp; 3 Kluiskisten
                    </span>
                </div>
                <h1 class="text-xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight uppercase">
                    GOEDERENSLUIS &amp; <span class="text-indigo-600 dark:text-indigo-400">KRAAKKAMER</span>
                </h1>
                <p class="text-slate-600 dark:text-slate-400 text-xs sm:text-sm max-w-2xl leading-relaxed">
                    Grijp bewaakte zendingen weg bij de goederensluis en kies vervolgens in de Kraakkamer zelf hoe je jouw verzegelde koffers opent: ga voor 100% veilige loper-precisie of gok met de snijbrander en het breekijzer voor maximaal +75% extra bankbuit!
                </p>
            </div>

            <!-- Telemetry Stats -->
            <div class="grid grid-cols-3 gap-2.5 shrink-0">
                <div class="bg-slate-50 dark:bg-slate-800/70 border border-slate-200/80 dark:border-slate-700 rounded-xl px-4 py-2.5 text-center">
                    <span class="text-[10px] font-black uppercase tracking-wider text-slate-400 block">Koffers</span>
                    <span class="text-base font-black text-indigo-600 dark:text-indigo-400 font-mono">
                        🧳 {{ $character->unopened_bags ?? 0 }}
                    </span>
                </div>
                <div class="bg-amber-50/80 dark:bg-amber-950/40 border border-amber-200/80 dark:border-amber-800/70 rounded-xl px-4 py-2.5 text-center">
                    <span class="text-[10px] font-black uppercase tracking-wider text-amber-800 dark:text-amber-300 block">XXL Kisten</span>
                    <span class="text-base font-black text-amber-600 dark:text-amber-300 font-mono">
                        👑 {{ $character->xxl_cases ?? 0 }}
                    </span>
                </div>
                <div class="bg-emerald-50/80 dark:bg-emerald-950/40 border border-emerald-200/80 dark:border-emerald-800/70 rounded-xl px-4 py-2.5 text-center">
                    <span class="text-[10px] font-black uppercase tracking-wider text-emerald-700 dark:text-emerald-300 block">Uithouding</span>
                    <span class="text-base font-black text-emerald-700 dark:text-emerald-300 font-mono">
                        {{ $character->energy }}/{{ $character->max_energy }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Cooldown Banner -->
        <div x-show="seconds > 0"
             class="mt-5 p-4 rounded-xl bg-amber-500/10 dark:bg-amber-950/40 border border-amber-300/80 dark:border-amber-700/80 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3"
             style="{{ $pkgCooldown > 0 ? '' : 'display: none;' }}">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-amber-500 text-white flex items-center justify-center text-sm font-black shrink-0">
                    <i class="fa-solid fa-shield-halved"></i>
                </div>
                <div>
                    <h4 class="text-xs font-black text-amber-950 dark:text-amber-200">Verscherpt Toezicht bij de Goederensluis</h4>
                    <p class="text-[11px] text-amber-800 dark:text-amber-300">
                        Patrouille loopt nog <strong class="font-mono" x-text="format(seconds)">{{ gmdate('i:s', $pkgCooldown) }}</strong> door de gang.
                    </p>
                </div>
            </div>
            <form method="POST" action="{{ route('cooldown.skip') }}" class="w-full sm:w-auto">
                @csrf
                <input type="hidden" name="action" value="package">
                <button type="submit"
                        @disabled($character->isImprisoned() || $character->isComatose())
                        class="btn-star-gold w-full sm:w-auto h-9 px-4 rounded-xl font-black text-xs flex items-center justify-center gap-1.5 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">
                    <i class="fa-solid fa-star text-[11px]"></i>
                    <span>Wachtpost Omkopen ({{ $packageSkipCost ?? 10 }} ⭐)</span>
                </button>
            </form>
        </div>
    </div>

    <!-- STEP 1: GOEDERENSLUIS ONDERSCHEPPINGS-MATRIX (Moved to top) -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-5 sm:p-6 shadow-xs space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-100 dark:border-slate-800 pb-3">
            <div>
                <span class="text-[10px] font-extrabold uppercase tracking-wider text-indigo-600 dark:text-indigo-400 block">
                    Fase 1: Buit Binnenhalen
                </span>
                <h2 class="text-sm sm:text-base font-black uppercase text-slate-900 dark:text-white">
                    Zendingen Onderscheppen op de Vleugel
                </h2>
            </div>
            <span class="text-xs font-mono font-bold text-slate-500 dark:text-slate-400">
                🔑 KluisMunten op zak: <strong class="text-slate-900 dark:text-white">{{ $character->vault_tokens ?? 0 }}</strong>
            </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-5 gap-3.5">
            @foreach($packages as $pkg)
                @php
                    $hasTokenCost = ($pkg['token_cost'] ?? 0) > 0;
                    $canAffordTokens = !$hasTokenCost || (($character->vault_tokens ?? 0) >= $pkg['token_cost']);
                    $canAffordEnergy = $character->energy >= $pkg['energy'];
                    $otherBlocked = $character->isImprisoned() || $character->isComatose() || !$canAffordEnergy || !$canAffordTokens;
                    $isDisabled = $otherBlocked || $pkgCooldown > 0;
                @endphp
                <div class="bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70 rounded-xl p-4 flex flex-col justify-between space-y-3">
                    <div class="space-y-2.5">
                        <div class="flex items-center justify-between">
                            <span class="w-9 h-9 rounded-lg bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-sm">
                                <i class="fa-solid {{ $pkg['icon'] }}"></i>
                            </span>
                            <span class="text-[10px] font-mono font-black px-2 py-0.5 rounded bg-slate-200/70 dark:bg-slate-700 text-slate-800 dark:text-slate-200">
                                {{ $pkg['energy'] }}⚡ @if($hasTokenCost) • {{ $pkg['token_cost'] }}🔑 @endif
                            </span>
                        </div>

                        <div>
                            <h3 class="text-xs font-black text-slate-900 dark:text-white leading-snug">
                                {{ $pkg['name'] }}
                            </h3>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1 leading-relaxed">
                                {{ $pkg['desc'] }}
                            </p>
                        </div>

                        <div class="space-y-1">
                            <div class="flex items-center justify-between text-[10px] font-bold">
                                <span class="text-slate-400">Slagingskans</span>
                                <span class="font-mono text-indigo-600 dark:text-indigo-400">{{ $pkg['rate'] }}%</span>
                            </div>
                            <div class="w-full h-1.5 bg-slate-200 dark:bg-slate-700 rounded-full overflow-hidden">
                                <div class="h-full bg-indigo-600" style="width: {{ $pkg['rate'] }}%"></div>
                            </div>
                        </div>

                        <div class="text-[11px] font-mono font-bold text-emerald-600 dark:text-emerald-400">
                            €{{ number_format($pkg['min'], 0, ',', '.') }} – €{{ number_format($pkg['max'], 0, ',', '.') }}
                        </div>
                    </div>

                    <form action="{{ route('packages.intercept') }}" method="POST">
                        @csrf
                        <input type="hidden" name="package_id" value="{{ $pkg['id'] }}">
                        <button type="submit"
                                :disabled="seconds > 0 || {{ $otherBlocked ? 'true' : 'false' }}"
                                {{ $isDisabled ? 'disabled' : '' }}
                                class="w-full py-2 px-3 rounded-lg bg-indigo-600 hover:bg-indigo-700 disabled:bg-slate-200 disabled:dark:bg-slate-800 disabled:text-slate-400 disabled:cursor-not-allowed text-white font-black text-[11px] uppercase tracking-wider transition cursor-pointer">
                            Grijp Zending
                        </button>
                    </form>
                </div>
            @endforeach
        </div>
    </div>

    <!-- STEP 2: TWO-COLUMN INTERACTIVE CRACKING CHAMBER & FORENSIC LOGS -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

        <!-- LEFT/MAIN COLUMN (7 cols): Interactive 3-Chest & 3-Method Cracking Chamber -->
        <div class="lg:col-span-7 space-y-6">

            <!-- Visual 3-Chest + 3-Method Cracking Selector -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-5 sm:p-6 shadow-xs space-y-5">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                    <div>
                        <span class="text-[10px] font-extrabold uppercase tracking-wider text-purple-600 dark:text-purple-400 block">
                            Fase 2: Kies Kist &amp; Openbreek-Techniek
                        </span>
                        <h2 class="text-sm sm:text-base font-black uppercase text-slate-900 dark:text-white">
                            Interactieve Kraaktafel (Voorzichtig vs. Forceren)
                        </h2>
                    </div>
                    <span class="text-xs font-mono font-black px-3 py-1 rounded-full bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300">
                        {{ $character->unopened_bags ?? 0 }} Koffers Paraat
                    </span>
                </div>

                <!-- 1. Visual 3-Chest Selection Screen -->
                <div class="space-y-2">
                    <span class="text-[11px] font-extrabold uppercase tracking-wider text-slate-500 dark:text-slate-400 block">
                        1. Kies één van de 3 Verzegelde Kluiskisten op de Kraaktafel:
                    </span>
                    <div class="grid grid-cols-3 gap-3">
                        <button type="button"
                                @click="selectedChest = 1"
                                :class="selectedChest === 1 ? 'border-indigo-500 bg-indigo-50/80 dark:bg-indigo-950/40 ring-1 ring-indigo-500/40' : 'border-slate-200/80 dark:border-slate-700 bg-slate-50/70 dark:bg-slate-800/50'"
                                class="p-3.5 rounded-xl border text-center transition cursor-pointer space-y-1">
                            <i class="fa-solid fa-briefcase text-lg text-indigo-600 dark:text-indigo-400"></i>
                            <span class="text-xs font-black text-slate-900 dark:text-white block">Kist Alpha</span>
                            <span class="text-[10px] text-slate-500 dark:text-slate-400 block">Cilinderslot #A1</span>
                        </button>

                        <button type="button"
                                @click="selectedChest = 2"
                                :class="selectedChest === 2 ? 'border-purple-500 bg-purple-50/80 dark:bg-purple-950/40 ring-1 ring-purple-500/40' : 'border-slate-200/80 dark:border-slate-700 bg-slate-50/70 dark:bg-slate-800/50'"
                                class="p-3.5 rounded-xl border text-center transition cursor-pointer space-y-1">
                            <i class="fa-solid fa-toolbox text-lg text-purple-600 dark:text-purple-400"></i>
                            <span class="text-xs font-black text-slate-900 dark:text-white block">Kist Beta</span>
                            <span class="text-[10px] text-slate-500 dark:text-slate-400 block">Dubbel Penslot #B2</span>
                        </button>

                        <button type="button"
                                @click="selectedChest = 3"
                                :class="selectedChest === 3 ? 'border-amber-500 bg-amber-50/80 dark:bg-amber-950/40 ring-1 ring-amber-500/40' : 'border-slate-200/80 dark:border-slate-700 bg-slate-50/70 dark:bg-slate-800/50'"
                                class="p-3.5 rounded-xl border text-center transition cursor-pointer space-y-1">
                            <i class="fa-solid fa-vault text-lg text-amber-500"></i>
                            <span class="text-xs font-black text-slate-900 dark:text-white block">Kist Omega</span>
                            <span class="text-[10px] text-slate-500 dark:text-slate-400 block">Pantserkist #Z9</span>
                        </button>
                    </div>
                </div>

                <!-- 2. Cracking Method Choice (Safe vs Gamble/Force) -->
                <div class="space-y-2">
                    <span class="text-[11px] font-extrabold uppercase tracking-wider text-slate-500 dark:text-slate-400 block">
                        2. Kies jouw Openbreek-Methode (Risico vs. Bonuswinst):
                    </span>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <button type="button"
                                @click="selectedMethod = 'precisie'"
                                :class="selectedMethod === 'precisie' ? 'border-emerald-500 bg-emerald-50/80 dark:bg-emerald-950/40 ring-1 ring-emerald-500/40' : 'border-slate-200/80 dark:border-slate-700 bg-slate-50/70 dark:bg-slate-800/50'"
                                class="p-3.5 rounded-xl border text-left transition cursor-pointer space-y-1">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-black text-slate-900 dark:text-white">Voorzichtig</span>
                                <span class="text-[10px] font-mono font-black text-emerald-600 dark:text-emerald-400">100% Veilig</span>
                            </div>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400">1.00x standaardbuit, nooit schade aan inhoud.</p>
                        </button>

                        <button type="button"
                                @click="selectedMethod = 'thermisch'"
                                :class="selectedMethod === 'thermisch' ? 'border-amber-500 bg-amber-50/80 dark:bg-amber-950/40 ring-1 ring-amber-500/40' : 'border-slate-200/80 dark:border-slate-700 bg-slate-50/70 dark:bg-slate-800/50'"
                                class="p-3.5 rounded-xl border text-left transition cursor-pointer space-y-1">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-black text-slate-900 dark:text-white">Snijbrander</span>
                                <span class="text-[10px] font-mono font-black text-amber-600 dark:text-amber-400">75% Kans</span>
                            </div>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400">+35% extra bankgeld &amp; hogere itemkans.</p>
                        </button>

                        <button type="button"
                                @click="selectedMethod = 'forceren'"
                                :class="selectedMethod === 'forceren' ? 'border-rose-500 bg-rose-50/80 dark:bg-rose-950/40 ring-1 ring-rose-500/40' : 'border-slate-200/80 dark:border-slate-700 bg-slate-50/70 dark:bg-slate-800/50'"
                                class="p-3.5 rounded-xl border text-left transition cursor-pointer space-y-1">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-black text-slate-900 dark:text-white">Forceren</span>
                                <span class="text-[10px] font-mono font-black text-rose-600 dark:text-rose-400">60% Gok</span>
                            </div>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400">+75% topbuit &amp; extra ster-kans, of verfvlam-schade!</p>
                        </button>
                    </div>
                </div>

                <!-- Action Forms (Single Crack, Bulk Crack, or Direct Fence Sale) -->
                <div class="space-y-2.5 pt-2 border-t border-slate-100 dark:border-slate-800">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                        <form action="{{ route('packages.open-bag') }}" method="POST">
                            @csrf
                            <input type="hidden" name="crack_method" :value="selectedMethod">
                            <input type="hidden" name="chest_slot" :value="selectedChest">
                            <button type="submit"
                                    {{ $character->isImprisoned() || $character->isComatose() || ($character->unopened_bags ?? 0) <= 0 ? 'disabled' : '' }}
                                    class="w-full py-3 px-4 rounded-xl bg-indigo-600 hover:bg-indigo-700 disabled:bg-slate-200 disabled:dark:bg-slate-800 disabled:text-slate-400 disabled:cursor-not-allowed text-white font-black text-xs uppercase tracking-wider transition cursor-pointer flex items-center justify-center gap-2">
                                <i class="fa-solid fa-unlock-keyhole"></i>
                                <span x-text="'Open 1 Koffer (' + methodInfo[selectedMethod].multLabel + ')'">Open 1 Koffer</span>
                            </button>
                        </form>

                        <form action="{{ route('packages.open-bag-all') }}" method="POST">
                            @csrf
                            <input type="hidden" name="crack_method" :value="selectedMethod">
                            <button type="submit"
                                    {{ $character->isImprisoned() || $character->isComatose() || ($character->unopened_bags ?? 0) <= 0 ? 'disabled' : '' }}
                                    class="w-full py-3 px-4 rounded-xl bg-purple-600 hover:bg-purple-700 disabled:bg-slate-200 disabled:dark:bg-slate-800 disabled:text-slate-400 disabled:cursor-not-allowed text-white font-black text-xs uppercase tracking-wider transition cursor-pointer flex items-center justify-center gap-2">
                                <i class="fa-solid fa-bolt text-amber-300"></i>
                                <span>Open Serie (Max 50x)</span>
                            </button>
                        </form>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                        <form action="{{ route('packages.sell-bag') }}" method="POST">
                            @csrf
                            <input type="hidden" name="amount" value="1">
                            <button type="submit"
                                    {{ $character->isImprisoned() || $character->isComatose() || ($character->unopened_bags ?? 0) <= 0 ? 'disabled' : '' }}
                                    class="w-full py-2.5 px-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 disabled:bg-slate-200 disabled:dark:bg-slate-800 disabled:text-slate-400 disabled:cursor-not-allowed text-white font-black text-xs uppercase tracking-wider transition cursor-pointer">
                                Lever 1 In bij Heler (€ 2.500)
                            </button>
                        </form>

                        @if(($character->unopened_bags ?? 0) > 1)
                            <form action="{{ route('packages.sell-bag') }}" method="POST">
                                @csrf
                                <input type="hidden" name="amount" value="{{ min(100, $character->unopened_bags) }}">
                                <button type="submit"
                                        {{ $character->isImprisoned() || $character->isComatose() || ($character->unopened_bags ?? 0) <= 0 ? 'disabled' : '' }}
                                        class="w-full py-2.5 px-3 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 hover:bg-emerald-100 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 font-black text-xs uppercase tracking-wider transition cursor-pointer">
                                    Lever {{ min(100, $character->unopened_bags) }}x In (€{{ number_format(min(100, $character->unopened_bags) * 2500, 0, ',', '.') }})
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>

            <!-- XXL Vault Cases & Master Bundle Row -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="bg-white dark:bg-slate-900 border border-amber-300/80 dark:border-amber-700/80 rounded-2xl p-5 shadow-xs flex flex-col justify-between space-y-4">
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-black uppercase text-amber-800 dark:text-amber-300">
                                👑 XXL Kluiskoffers
                            </span>
                            <span class="text-xs font-mono font-black text-amber-600 dark:text-amber-400">
                                {{ $character->xxl_cases ?? 0 }} stuks
                            </span>
                        </div>
                        <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed">
                            Zware directiekisten met €25.000–€75.000 bankgeld, 2–4 Sterren, 5–12 Bivakmutsen en meerdere kluiscodes.
                        </p>
                    </div>

                    <div class="space-y-2">
                        @if(($character->xxl_cases ?? 0) > 0)
                            <form action="{{ route('packages.open-xxl') }}" method="POST">
                                @csrf
                                <button type="submit"
                                        {{ $character->isImprisoned() || $character->isComatose() ? 'disabled' : '' }}
                                        class="w-full py-2.5 px-4 rounded-xl bg-amber-500 hover:bg-amber-600 disabled:bg-slate-200 disabled:text-slate-400 text-slate-950 font-black text-xs uppercase cursor-pointer">
                                    Ontgrendel XXL Kluiskoffer
                                </button>
                            </form>
                        @endif

                        <form action="{{ route('packages.buy-xxl') }}" method="POST">
                            @csrf
                            <button type="submit"
                                    {{ $character->isImprisoned() || $character->isComatose() || ($character->stars ?? 0) < 100 ? 'disabled' : '' }}
                                    class="btn-star-gold w-full py-2.5 px-4 rounded-xl font-black text-xs cursor-pointer disabled:opacity-40 disabled:cursor-not-allowed">
                                ⭐ Schaf XXL Koffer Aan (100 Sterren)
                            </button>
                        </form>
                    </div>
                </div>

                <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-5 shadow-xs flex flex-col justify-between space-y-4">
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-black uppercase text-slate-900 dark:text-white">
                                ⚡ Directe Master-Lading
                            </span>
                            <span class="text-xs font-mono font-black text-indigo-600 dark:text-indigo-400">
                                100 ⭐
                            </span>
                        </div>
                        <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed">
                            Sla het onderscheppen over en laat direct een diplomatieke geldzending met bivakmutsen en codes afleveren.
                        </p>
                    </div>

                    <form action="{{ route('shop.buy-star-item') }}" method="POST">
                        @csrf
                        <input type="hidden" name="item_type" value="package_150">
                        <button type="submit"
                                @if(($character->stars ?? 0) < 100 || $character->isImprisoned() || $character->isComatose()) disabled @endif
                                class="w-full py-2.5 px-4 rounded-xl bg-indigo-600 hover:bg-indigo-700 disabled:bg-slate-200 disabled:dark:bg-slate-800 disabled:text-slate-400 disabled:cursor-not-allowed text-white font-black text-xs uppercase cursor-pointer">
                            Bestel Master-Lading
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- RIGHT COLUMN (5 cols): Forensic Cracking Timeline Cards (Replaces old HTML Table) -->
        <div class="lg:col-span-5 bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-5 sm:p-6 shadow-xs space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                <div>
                    <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400 block">
                        Kraak-Historie
                    </span>
                    <h2 class="text-sm font-black uppercase text-slate-900 dark:text-white">
                        Forensisch Buit-Archief
                    </h2>
                </div>
                <i class="fa-solid fa-folder-open text-indigo-500"></i>
            </div>

            <div class="space-y-2.5">
                @forelse($kofferLogs ?? [] as $log)
                    <div class="p-3.5 rounded-xl bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70 space-y-1.5">
                        <div class="flex items-center justify-between text-[10px] font-mono text-slate-400">
                            <span class="font-bold text-indigo-600 dark:text-indigo-400">Registratie #{{ 150000 + $log->id }}</span>
                            <span>{{ $log->created_at->format('d-m-Y H:i') }}</span>
                        </div>
                        <p class="text-xs font-bold text-slate-800 dark:text-slate-200 leading-snug">
                            {{ $log->loot_summary }}
                        </p>
                    </div>
                @empty
                    <div class="py-10 text-center text-xs text-slate-400 italic">
                        Er zijn nog geen geopende kisten vastgelegd in je archief.
                    </div>
                @endforelse
            </div>
        </div>

    </div>

</div>
@endsection
