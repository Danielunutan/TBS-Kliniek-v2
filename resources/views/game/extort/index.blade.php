@extends('layouts.app')

@section('title', 'Afpersen & Chantage - TBS Kliniek')

@section('content')
@php
    $extortCooldown = $character->cooldownRemaining('extort');
@endphp
<div class="space-y-6" x-data="{
    seconds: {{ (int) $extortCooldown }},
    format(s) {
        if (s <= 0) return '00:00';
        const m = Math.floor(s / 60);
        const sec = Math.floor(s % 60);
        return (m < 10 ? '0' : '') + m + ':' + (sec < 10 ? '0' : '') + sec;
    },
    init() {
        if (this.seconds > 0) {
            const timer = setInterval(() => {
                if (this.seconds > 0) {
                    this.seconds--;
                }
                if (this.seconds <= 0) {
                    clearInterval(timer);
                }
            }, 1000);
        }
    }
}">
    <!-- HERO HEADER -->
    <div class="rounded-2xl bg-white dark:bg-slate-900 p-6 sm:p-7 text-slate-800 dark:text-slate-100 shadow-xs border border-slate-200/90 dark:border-slate-800 relative overflow-hidden">
        <div class="absolute -right-10 -bottom-10 w-48 h-48 bg-purple-500/10 dark:bg-purple-500/5 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-purple-50 dark:bg-purple-950/60 border border-purple-200/80 dark:border-purple-800/70 flex items-center justify-center text-2xl text-purple-600 dark:text-purple-400 shadow-2xs shrink-0">
                    <i class="fa-solid fa-person-military-pointing"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="text-[10px] font-black uppercase tracking-widest text-purple-700 dark:text-purple-300 bg-purple-50 dark:bg-purple-950/60 px-2.5 py-0.5 rounded-full border border-purple-200/80 dark:border-purple-800/70">
                            TBS Kliniek Illegaal
                        </span>
                    </div>
                    <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight mt-1">
                        Afpersen &amp; Chantage
                    </h1>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 max-w-xl leading-relaxed">
                        Gebruik jouw status en dreiging om slachtoffers in de kliniek te laten betalen. Hogere functies zoals de directie en beveiliging leveren forse contanten op maar verhogen de arrestatiekans.
                    </p>
                </div>
            </div>
        </div>
    </div>

    @if($character->isImprisoned())
    <div class="p-4 rounded-2xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200/80 dark:border-rose-800/70 flex items-center gap-3 text-rose-800 dark:text-rose-200 text-sm">
        <i class="fa-solid fa-lock text-base text-rose-600 shrink-0"></i>
        <span>Je zit in de isoleercel en kunt op dit moment niemand afpersen.</span>
    </div>
    @endif

    @if($character->isComatose())
    <div class="p-4 rounded-2xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200/80 dark:border-rose-800/70 flex items-center gap-3 text-rose-800 dark:text-rose-200 text-sm">
        <i class="fa-solid fa-bed-pulse text-base text-rose-600 shrink-0"></i>
        <span>Je ligt in het ziekenhuis/coma en kunt op dit moment niemand afpersen.</span>
    </div>
    @endif

    <!-- ENERGY BAR CARD -->
    <div class="flex items-center gap-3.5 bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl px-5 py-3.5 shadow-xs">
        <i class="fa-solid fa-bolt text-emerald-500 text-sm"></i>
        <span class="text-xs font-bold text-slate-700 dark:text-slate-300">Energie: <span class="text-emerald-600 dark:text-emerald-400 font-black font-mono">{{ $character->energy }} / {{ $character->max_energy }}</span></span>
        <div class="flex-1 bg-slate-100 dark:bg-slate-800/80 rounded-full h-2 overflow-hidden border border-slate-200/60 dark:border-slate-700/60">
            <div class="bg-emerald-500 h-full rounded-full transition-all duration-300" style="width: {{ min(100, round(($character->energy / max(1, $character->max_energy)) * 100)) }}%"></div>
        </div>
    </div>

    <!-- COOLDOWN BANNER -->
    <div x-show="seconds > 0" class="p-4 rounded-2xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200/80 dark:border-amber-800/70 flex flex-col sm:flex-row items-center justify-between gap-4" style="{{ $extortCooldown > 0 ? '' : 'display: none;' }}">
        <div class="flex items-center gap-3.5">
            <div class="w-10 h-10 rounded-xl bg-amber-500 text-white shadow-xs flex items-center justify-center text-lg font-black shrink-0">
                <i class="fa-solid fa-clock"></i>
            </div>
            <div>
                <h4 class="text-xs font-black text-slate-900 dark:text-white">Afpersen Cooldown Actief</h4>
                <p class="text-xs text-amber-800 dark:text-amber-300 mt-0.5">Wacht nog <strong class="font-mono text-amber-950 dark:text-amber-200 font-black" x-text="format(seconds)">{{ gmdate('i:s', $extortCooldown) }}</strong> of omzeil direct met Sterren.</p>
            </div>
        </div>
        <div class="flex items-center gap-2 shrink-0">
            <form method="POST" action="{{ route('cooldown.skip') }}">
                @csrf
                <input type="hidden" name="action" value="extort">
                <button type="submit" 
                        @disabled($character->isImprisoned() || $character->isComatose() || ($character->stars ?? 0) < ($extortSkipCost ?? 20))
                        class="btn-star-gold h-9 px-4 rounded-xl active:scale-95 text-slate-900 dark:text-white text-xs font-black transition-all duration-150 shadow-xs flex items-center justify-center gap-1.5 cursor-pointer whitespace-nowrap disabled:opacity-50 disabled:cursor-not-allowed">
                    <i class="fa-solid fa-star text-[11px]"></i>
                    <span>Omzeilen ({{ $extortSkipCost ?? 20 }} Sterren)</span>
                </button>
            </form>
        </div>
    </div>

    <!-- TARGET CARDS GRID -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        @foreach($targets as $target)
        @php
            $otherBlocked = $character->isImprisoned() || $character->isComatose() || $character->energy < $target['energy'];
            $isDisabled = $otherBlocked || $extortCooldown > 0;
        @endphp
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/90 dark:border-slate-800 hover:border-purple-300 dark:hover:border-purple-700/80 shadow-xs transition-all duration-200 p-5 sm:p-6 flex flex-col justify-between gap-4">
            <div class="flex items-start gap-3.5">
                <div class="w-11 h-11 rounded-xl bg-purple-50 dark:bg-purple-950/60 border border-purple-200/80 dark:border-purple-800/70 text-purple-600 dark:text-purple-400 flex items-center justify-center text-lg shrink-0 shadow-2xs">
                    <i class="fa-solid {{ $target['icon'] }}"></i>
                </div>
                <div>
                    <h3 class="text-sm font-black text-slate-900 dark:text-white">{{ $target['name'] }}</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 leading-relaxed">{{ $target['desc'] }}</p>
                </div>
            </div>
            <div class="pt-3.5 border-t border-slate-100 dark:border-slate-800/80 flex items-center justify-between gap-4 flex-wrap">
                <div class="space-y-1">
                    <div class="flex items-center gap-2.5 text-xs">
                        <span class="text-slate-400 dark:text-slate-500 w-20 font-bold">Beloning:</span>
                        <span class="font-black text-emerald-600 dark:text-emerald-400 font-mono">€{{ number_format($target['min'], 0, ',', '.') }}–€{{ number_format($target['max'], 0, ',', '.') }}</span>
                    </div>
                    <div class="flex items-center gap-2.5 text-xs">
                        <span class="text-slate-400 dark:text-slate-500 w-20 font-bold">Energie:</span>
                        <span class="font-bold text-amber-600 dark:text-amber-400 font-mono">{{ $target['energy'] }} EP</span>
                    </div>
                    <div class="flex items-center gap-2.5 text-xs">
                        <span class="text-slate-400 dark:text-slate-500 w-20 font-bold">Slagingskans:</span>
                        <span class="font-bold text-purple-600 dark:text-purple-400 font-mono">{{ $target['rate'] }}%</span>
                    </div>
                    <div class="flex items-center gap-2.5 text-xs">
                        <span class="text-slate-400 dark:text-slate-500 w-20 font-bold">Cooldown:</span>
                        <span class="font-bold text-slate-700 dark:text-slate-300 font-mono"><i class="fa-solid fa-clock text-[10px] mr-1 text-slate-400"></i>{{ gmdate('i:s', $target['cooldown'] ?? 180) }}</span>
                    </div>
                </div>
                <form action="{{ route('extort.extort') }}" method="POST">
                    @csrf
                    <input type="hidden" name="target_id" value="{{ $target['id'] }}">
                    <button type="submit"
                            :disabled="seconds > 0 || {{ $otherBlocked ? 'true' : 'false' }}"
                            :class="(seconds > 0 || {{ $otherBlocked ? 'true' : 'false' }}) ? 'bg-slate-100 dark:bg-slate-800/80 text-slate-400 dark:text-slate-500 border border-slate-200/80 dark:border-slate-700/60 cursor-not-allowed shadow-none' : 'bg-purple-600 hover:bg-purple-700 dark:bg-purple-600 dark:hover:bg-purple-500 text-white cursor-pointer active:scale-95 shadow-xs'"
                            {{ $isDisabled ? 'disabled' : '' }}
                            class="px-4 py-2.5 rounded-xl text-xs font-black transition-all duration-150 flex items-center gap-1.5">
                        <i class="fa-solid fa-hand-back-fist text-[11px]"></i>
                        <span>Afpersen</span>
                    </button>
                </form>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection
