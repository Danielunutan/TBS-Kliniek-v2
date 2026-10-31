@extends('layouts.app')

@section('content')
<div class="space-y-6">
    
    <!-- Title Card (Clean & Modern) -->
    <div class="rounded-2xl shadow-xs p-6 sm:p-8 text-slate-800 dark:text-slate-100 relative overflow-hidden bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800">
        <div class="relative z-10">
            <div class="inline-flex items-center gap-2 bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 text-xs font-bold px-3 py-1 rounded-full border border-indigo-200/80 dark:border-indigo-800/70 mb-3">
                <i class="fa-solid fa-ghost text-indigo-600 dark:text-indigo-400"></i> Persoonlijke Bescherming &amp; Beveiliging
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white">Vrijwillige Isolatie</h1>
            <p class="text-slate-500 dark:text-slate-400 text-xs sm:text-sm mt-1 max-w-xl leading-relaxed">
                Heb je ruzie met gevaarlijke patiënten op de luchtplaats? Trek je tijdelijk terug in je cel achter slot en grendel zodat niemand je kan aanvallen.
            </p>
        </div>
    </div>

    <!-- Pricing Summary Banner -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-4 sm:p-5 flex flex-col sm:flex-row items-center justify-between gap-4 text-slate-700 dark:text-slate-200 shadow-xs">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-purple-50 dark:bg-purple-950/60 border border-purple-200/80 dark:border-purple-800/70 flex items-center justify-center text-purple-600 dark:text-purple-400 text-lg shrink-0">
                <i class="fa-solid fa-tags"></i>
            </div>
            <div>
                <span class="text-xs font-black uppercase tracking-wider text-purple-700 dark:text-purple-300">Vast Tarief per uur</span>
                <p class="text-xs text-slate-500 dark:text-slate-400 font-medium">
                    Bescherming kost <span class="font-extrabold text-slate-900 dark:text-white">€50.000</span> óf <span class="font-extrabold text-amber-600 dark:text-amber-400">25 Sterren</span> per uur.
                </p>
            </div>
        </div>
        <div class="flex items-center gap-2 shrink-0">
            <span class="px-3 py-1.5 rounded-xl bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70 text-xs font-bold text-emerald-700 dark:text-emerald-400 flex items-center gap-1.5">
                <i class="fa-solid fa-wallet text-emerald-600 dark:text-emerald-400"></i> €{{ number_format($character->cash + $character->bank, 0, ',', '.') }}
            </span>
            <span class="px-3 py-1.5 rounded-xl bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70 text-xs font-bold text-amber-700 dark:text-amber-400 flex items-center gap-1.5">
                <i class="fa-solid fa-star text-amber-500"></i> {{ $character->stars ?? 0 }} Sterren
            </span>
        </div>
    </div>

    <!-- Status Card with Live Countdown Timer -->
    @if($isProtected)
    <div data-testid="isolation-countdown-timer"
         x-data="{
             sec: {{ max(0, (int) ($remainingSeconds ?? 0)) }},
             formatHuman(s) {
                 if (s <= 0) return '0u 00m 00s (Verlopen)';
                 const total = Math.floor(s);
                 const h = Math.floor(total / 3600);
                 const m = Math.floor((total % 3600) / 60);
                 const rem = Math.floor(total % 60);
                 return `${h}u ${m < 10 ? '0' : ''}${m}m ${rem < 10 ? '0' : ''}${rem}s`;
             },
             formatClock(s) {
                 if (s <= 0) return '00:00:00';
                 const total = Math.floor(s);
                 const h = Math.floor(total / 3600);
                 const m = Math.floor((total % 3600) / 60);
                 const rem = Math.floor(total % 60);
                 return `${h < 10 ? '0' : ''}${h}:${m < 10 ? '0' : ''}${m}:${rem < 10 ? '0' : ''}${rem}`;
             },
             init() {
                 if (this.sec > 0) {
                     const timer = setInterval(() => {
                         if (this.sec > 0) {
                             this.sec--;
                         } else {
                             clearInterval(timer);
                         }
                     }, 1000);
                 }
             }
         }"
         :class="sec > 0 && sec <= 1800 ? 'bg-amber-50 dark:bg-amber-950/40 border-amber-300 dark:border-amber-700/70' : (sec <= 0 ? 'bg-rose-50 dark:bg-rose-950/40 border-rose-300 dark:border-rose-800/70' : 'bg-emerald-50 dark:bg-emerald-950/40 border-emerald-200 dark:border-emerald-800/60')"
         class="border-2 rounded-2xl p-5 flex flex-col md:flex-row md:items-center justify-between gap-4 shadow-xs transition">
        <div class="flex items-start sm:items-center gap-3.5">
            <div :class="sec > 0 && sec <= 1800 ? 'bg-amber-500' : (sec <= 0 ? 'bg-rose-600' : 'bg-emerald-600')"
                 class="w-11 h-11 rounded-xl text-white flex items-center justify-center font-bold text-lg shrink-0 shadow-xs">
                <i class="fa-solid fa-shield-halved"></i>
            </div>
            <div class="space-y-1">
                <div class="flex flex-wrap items-center gap-2">
                    <h3 class="text-sm sm:text-base font-extrabold"
                        :class="sec > 0 && sec <= 1800 ? 'text-amber-950 dark:text-amber-200' : (sec <= 0 ? 'text-rose-950 dark:text-rose-200' : 'text-emerald-950 dark:text-emerald-200')">
                        Isolatiebescherming Actief!
                    </h3>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider"
                          :class="sec > 0 && sec <= 1800 ? 'bg-amber-200/80 dark:bg-amber-900/80 text-amber-900 dark:text-amber-200' : (sec <= 0 ? 'bg-rose-200/80 dark:bg-rose-900/80 text-rose-900 dark:text-rose-200' : 'bg-emerald-200/80 dark:bg-emerald-900/80 text-emerald-900 dark:text-emerald-200')">
                        <span x-text="sec > 0 ? (sec <= 1800 ? 'Bijna Verlopen — Verleng Nu' : '100% Veilig') : 'Zojuist Verlopen'">100% Veilig</span>
                    </span>
                </div>
                <p class="text-xs leading-relaxed"
                   :class="sec > 0 && sec <= 1800 ? 'text-amber-900 dark:text-amber-300' : (sec <= 0 ? 'text-rose-900 dark:text-rose-300' : 'text-emerald-800 dark:text-emerald-300')">
                    Je cel is vergrendeld tot <span class="font-extrabold">{{ $character->protection_until->format('d-m-Y \o\m H:i') }} uur</span> (nog ~{{ $remainingHours }} uur / {{ $remainingMinutes }} min). Andere patiënten kunnen je niet aanvallen of liquideren. Verleng je beveiliging hieronder op tijd!
                </p>
                <p x-show="sec > 0 && sec <= 1800" x-cloak class="text-xs font-extrabold text-amber-800 dark:text-amber-300 flex items-center gap-1.5 pt-0.5">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    <span>Let op: je beveiliging loopt binnen 30 minuten af! Koop nu extra uren bij om beschermd te blijven.</span>
                </p>
            </div>
        </div>

        <div class="flex flex-col sm:flex-row md:flex-col items-stretch sm:items-center md:items-end justify-between gap-1.5 bg-white/90 dark:bg-slate-900/80 border rounded-xl px-4 py-3 shrink-0 shadow-2xs"
             :class="sec > 0 && sec <= 1800 ? 'border-amber-200 dark:border-amber-800/60' : (sec <= 0 ? 'border-rose-200 dark:border-rose-800/60' : 'border-emerald-200 dark:border-emerald-800/60')">
            <span class="text-[10px] font-black uppercase tracking-wider text-slate-500 dark:text-slate-400 flex items-center gap-1.5">
                <i class="fa-solid fa-stopwatch animate-pulse"
                   :class="sec > 0 && sec <= 1800 ? 'text-amber-600 dark:text-amber-400' : (sec <= 0 ? 'text-rose-600 dark:text-rose-400' : 'text-emerald-600 dark:text-emerald-400')"></i>
                <span>Resterende beveiligingstijd</span>
            </span>
            <div class="flex items-baseline gap-2">
                <span class="text-lg sm:text-xl font-black font-mono tracking-tight"
                      :class="sec > 0 && sec <= 1800 ? 'text-amber-700 dark:text-amber-400' : (sec <= 0 ? 'text-rose-700 dark:text-rose-400' : 'text-emerald-700 dark:text-emerald-400')"
                      x-text="formatClock(sec)">
                    {{ sprintf('%02d:%02d:%02d', intdiv((int) ($remainingSeconds ?? 0), 3600), intdiv(((int) ($remainingSeconds ?? 0)) % 3600, 60), ((int) ($remainingSeconds ?? 0)) % 60) }}
                </span>
                <span class="text-[11px] font-bold font-mono text-slate-500 dark:text-slate-400"
                      x-text="'(' + formatHuman(sec) + ')'">
                    ({{ intdiv((int) ($remainingSeconds ?? 0), 3600) }}u {{ sprintf('%02d', intdiv(((int) ($remainingSeconds ?? 0)) % 3600, 60)) }}m {{ sprintf('%02d', ((int) ($remainingSeconds ?? 0)) % 60) }}s)
                </span>
            </div>
        </div>
    </div>
    @else
    <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-5 flex items-center justify-between gap-4 shadow-xs">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 flex items-center justify-center font-bold text-lg shrink-0">
                <i class="fa-solid fa-door-open"></i>
            </div>
            <div>
                <h3 class="text-sm font-extrabold text-slate-800 dark:text-white">Geen Actieve Isolatie</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400">
                    Je loopt momenteel vrij rond op de afdeling en kunt worden aangevallen op de luchtplaats.
                </p>
            </div>
        </div>
        <span class="bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200/80 dark:border-slate-700 text-xs font-bold px-3 py-1 rounded-xl shrink-0">
            Kwetsbaar
        </span>
    </div>
    @endif

    @if(!empty($hasCashProtection) || !empty($hasVest))
    <div class="grid grid-cols-1 {{ (!empty($hasCashProtection) && !empty($hasVest)) ? 'md:grid-cols-2' : '' }} gap-4">
        @if(!empty($hasCashProtection))
        <div class="bg-white dark:bg-slate-900 border border-emerald-200/80 dark:border-emerald-800/70 rounded-2xl p-4 flex items-center justify-between gap-3 shadow-xs"
             x-data="{
                 sec: {{ max(0, (int) ($cashProtectionSeconds ?? 0)) }},
                 format() {
                     if (this.sec <= 0) return 'Verlopen';
                     const h = Math.floor(this.sec / 3600);
                     const m = Math.floor((this.sec % 3600) / 60);
                     const s = Math.floor(this.sec % 60);
                     return `${h}u ${m < 10 ? '0' : ''}${m}m ${s < 10 ? '0' : ''}${s}s`;
                 },
                 init() {
                     setInterval(() => { if (this.sec > 0) this.sec--; }, 1000);
                 }
             }">
            <div class="flex items-center gap-3 min-w-0">
                <div class="w-9 h-9 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-sm shrink-0">
                    <i class="fa-solid fa-shield-halved"></i>
                </div>
                <div class="min-w-0">
                    <div class="text-xs font-extrabold text-slate-900 dark:text-white truncate">VIP 24u Cash Bescherming Actief</div>
                    <div class="text-[11px] text-slate-500 dark:text-slate-400">Tot {{ $character->cash_protection_until?->format('d-m H:i') }} uur</div>
                </div>
            </div>
            <span class="px-3 py-1.5 rounded-xl bg-emerald-50 dark:bg-emerald-950/80 border border-emerald-200/80 dark:border-emerald-800 text-emerald-700 dark:text-emerald-300 text-xs font-black font-mono shrink-0"
                  x-text="format()"></span>
        </div>
        @endif

        @if(!empty($hasVest))
        <div class="bg-white dark:bg-slate-900 border border-indigo-200/80 dark:border-indigo-800/70 rounded-2xl p-4 flex items-center justify-between gap-3 shadow-xs"
             x-data="{
                 sec: {{ max(0, (int) ($vestSeconds ?? 0)) }},
                 format() {
                     if (this.sec <= 0) return 'Verlopen';
                     const h = Math.floor(this.sec / 3600);
                     const m = Math.floor((this.sec % 3600) / 60);
                     const s = Math.floor(this.sec % 60);
                     return `${h}u ${m < 10 ? '0' : ''}${m}m ${s < 10 ? '0' : ''}${s}s`;
                 },
                 init() {
                     setInterval(() => { if (this.sec > 0) this.sec--; }, 1000);
                 }
             }">
            <div class="flex items-center gap-3 min-w-0">
                <div class="w-9 h-9 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-sm shrink-0">
                    <i class="fa-solid fa-vest"></i>
                </div>
                <div class="min-w-0">
                    <div class="text-xs font-extrabold text-slate-900 dark:text-white truncate">{{ $character->getActiveVestInfo()['name'] ?? 'Kogelwerend Backfire-Vest' }}</div>
                    <div class="text-[11px] text-slate-500 dark:text-slate-400">Tot {{ $character->vest_until?->format('d-m H:i') }} uur</div>
                </div>
            </div>
            <span class="px-3 py-1.5 rounded-xl bg-indigo-50 dark:bg-indigo-950/80 border border-indigo-200/80 dark:border-indigo-800 text-indigo-700 dark:text-indigo-300 text-xs font-black font-mono shrink-0"
                  x-text="format()"></span>
        </div>
        @endif
    </div>
    @endif

    <!-- Package Selection -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xs border border-slate-200/90 dark:border-slate-800 p-6 space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-100 dark:border-slate-800 pb-3">
            <h2 class="text-sm font-extrabold text-slate-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
                <i class="fa-solid fa-lock text-indigo-600 dark:text-indigo-400"></i>
                <span>{{ $isProtected ? 'Verleng jouw actieve beveiliging' : 'Kies de duur van jouw isolatie' }}</span>
            </h2>
            <span class="text-xs text-slate-400 dark:text-slate-500 font-medium">Betaal per uur met Geld of Sterren</span>
        </div>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 pt-2">
            @foreach($packages as $pkg)
            @php
                $canAffordCash = ($character->cash + $character->bank) >= $pkg['cost_cash'];
                $canAffordStars = ($character->stars ?? 0) >= $pkg['cost_stars'];
                $newExpiry = ($isProtected && $character->protection_until ? $character->protection_until->copy() : now())->addHours($pkg['hours']);
            @endphp
            <div class="p-5 rounded-2xl border border-slate-200/80 dark:border-slate-700/70 hover:border-purple-300 dark:hover:border-purple-600 bg-slate-50/90 dark:bg-slate-800/60 flex flex-col justify-between gap-4 transition-all duration-200 shadow-xs group">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-base font-extrabold text-slate-900 dark:text-white group-hover:text-purple-600 dark:group-hover:text-purple-300 transition">{{ $pkg['label'] }}</span>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">
                            {{ $isProtected ? 'Verlengt tot ' . $newExpiry->format('d-m H:i') . ' uur' : 'Volledige immuniteit op de luchtplaats' }}
                        </p>
                    </div>
                    <span class="w-9 h-9 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 border border-indigo-200/60 dark:border-indigo-800/50 flex items-center justify-center text-sm font-bold shadow-xs">
                        <i class="fa-solid fa-clock"></i>
                    </span>
                </div>

                <div class="p-3 rounded-xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-700 text-xs space-y-1">
                    <div class="flex items-center justify-between font-bold text-slate-700 dark:text-slate-300">
                        <span>Kosten in geld:</span>
                        <span class="text-slate-900 dark:text-white font-black">€{{ number_format($pkg['cost_cash'], 0, ',', '.') }}</span>
                    </div>
                    <div class="flex items-center justify-between font-bold text-slate-700 dark:text-slate-300">
                        <span>Kosten in sterren:</span>
                        <span class="text-amber-500 font-black flex items-center gap-1">
                            <i class="fa-solid fa-star text-[10px]"></i> {{ $pkg['cost_stars'] }} Sterren
                        </span>
                    </div>
                </div>

                <div class="pt-3 border-t border-slate-200/80 dark:border-slate-700/70 grid grid-cols-2 gap-2">
                    <!-- Pay with cash -->
                    <form action="{{ route('isolation.activate') }}" method="POST">
                        @csrf
                        <input type="hidden" name="hours" value="{{ $pkg['hours'] }}">
                        <input type="hidden" name="currency" value="cash">
                        <button type="submit" 
                                {{ ! $canAffordCash ? 'disabled' : '' }}
                                title="Betaal met contant/bankgeld (€{{ number_format($pkg['cost_cash'], 0, ',', '.') }})"
                                class="w-full py-2.5 px-3 rounded-xl text-xs font-black transition-all duration-150 flex items-center justify-center gap-1.5 {{ $canAffordCash ? 'bg-purple-600 hover:bg-purple-700 dark:bg-purple-600 dark:hover:bg-purple-500 text-white cursor-pointer shadow-xs active:scale-95' : 'bg-slate-100 dark:bg-slate-800/80 text-slate-400 dark:text-slate-500 border border-slate-200/80 dark:border-slate-700/60 cursor-not-allowed' }}">
                            <i class="fa-solid fa-money-bill-wave"></i>
                            <span>€{{ $pkg['hours'] * 50 }}k</span>
                        </button>
                    </form>

                    <!-- Pay with stars -->
                    <form action="{{ route('isolation.activate') }}" method="POST">
                        @csrf
                        <input type="hidden" name="hours" value="{{ $pkg['hours'] }}">
                        <input type="hidden" name="currency" value="stars">
                        <button type="submit" 
                                {{ ! $canAffordStars ? 'disabled' : '' }}
                                title="Betaal met sterren ({{ $pkg['cost_stars'] }} Sterren)"
                                class="w-full py-2.5 px-3 rounded-xl text-xs font-black transition-all duration-150 flex items-center justify-center gap-1.5 {{ $canAffordStars ? 'btn-star-gold text-white cursor-pointer shadow-xs active:scale-95' : 'bg-slate-100 dark:bg-slate-800/80 text-slate-400 dark:text-slate-500 border border-slate-200/80 dark:border-slate-700/60 cursor-not-allowed' }}">
                            <i class="fa-solid fa-star text-amber-200"></i>
                            <span>{{ $pkg['cost_stars'] }} Sterren</span>
                        </button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Custom Hours Card -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xs border border-slate-200/90 dark:border-slate-800 p-6 space-y-4" x-data="{ hours: 1 }">
        <div class="flex items-center gap-2 text-sm font-extrabold text-slate-900 dark:text-white uppercase tracking-wider">
            <i class="fa-solid fa-sliders text-indigo-600 dark:text-indigo-400"></i>
            <span>Of kies zelf het aantal uren isolatie</span>
        </div>
        <p class="text-xs text-slate-500 dark:text-slate-400">
            Kies precies hoeveel uur je achter slot en grendel wilt blijven (1 tot 24 uur). Het tarief is €50.000 of 25 Sterren per uur.
        </p>

        <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-center bg-slate-50/90 dark:bg-slate-800/60 p-4 rounded-xl border border-slate-200/80 dark:border-slate-700/70">
            <div class="md:col-span-4 space-y-1">
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">Aantal Uren (1 - 24)</label>
                <input type="number" min="1" max="24" x-model.number="hours" 
                       class="w-full p-2.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-extrabold text-slate-900 dark:text-white focus:outline-none focus:border-purple-500 dark:focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition">
            </div>

            <div class="md:col-span-4 space-y-1">
                <span class="block text-xs font-bold text-slate-700 dark:text-slate-300">Totaal Prijs</span>
                <div class="text-xs space-y-0.5">
                    <div class="font-extrabold text-slate-900 dark:text-white flex items-center gap-1.5">
                        <i class="fa-solid fa-coins text-emerald-600"></i>
                        <span>€<span x-text="(hours * 50000).toLocaleString('nl-NL')"></span></span>
                    </div>
                    <div class="font-extrabold text-amber-600 dark:text-amber-400 flex items-center gap-1.5">
                        <i class="fa-solid fa-star text-amber-500"></i>
                        <span><span x-text="hours * 25"></span> Sterren</span>
                    </div>
                </div>
            </div>

            <div class="md:col-span-4 flex items-center gap-2">
                <form action="{{ route('isolation.activate') }}" method="POST" class="flex-1">
                    @csrf
                    <input type="hidden" name="hours" :value="hours">
                    <input type="hidden" name="currency" value="cash">
                    <button type="submit" class="w-full py-2.5 px-3 bg-purple-600 hover:bg-purple-700 dark:bg-purple-600 dark:hover:bg-purple-500 text-white font-black text-xs rounded-xl transition-all duration-150 shadow-xs flex items-center justify-center gap-1.5 cursor-pointer active:scale-95">
                        <i class="fa-solid fa-money-bill-wave"></i>
                        <span>Betaal Geld</span>
                    </button>
                </form>

                <form action="{{ route('isolation.activate') }}" method="POST" class="flex-1">
                    @csrf
                    <input type="hidden" name="hours" :value="hours">
                    <input type="hidden" name="currency" value="stars">
                    <button type="submit" class="w-full py-2.5 px-3 btn-star-gold text-white font-black text-xs rounded-xl transition-all duration-150 shadow-xs flex items-center justify-center gap-1.5 cursor-pointer active:scale-95">
                        <i class="fa-solid fa-star"></i>
                        <span>Betaal Sterren</span>
                    </button>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection
