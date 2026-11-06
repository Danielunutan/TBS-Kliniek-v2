@extends('layouts.app')

@section('content')
@php
    $lockerCooldown = $character->cooldownRemaining('locker');
    $lockerSkipCost = app(\App\Services\CooldownService::class)->getSkipCost('locker');
@endphp
<div class="space-y-6" x-data="{
    seconds: {{ (int) $lockerCooldown }},
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
    <!-- Clean TBS Kliniek Title Banner -->
    <div class="rounded-2xl bg-white dark:bg-slate-900 p-6 sm:p-8 text-slate-800 dark:text-slate-100 shadow-xs border border-slate-200/90 dark:border-slate-800 relative overflow-hidden">
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-purple-50 dark:bg-purple-950/60 border border-purple-200/80 dark:border-purple-800/70 flex items-center justify-center text-2xl text-purple-600 dark:text-purple-400 shadow-2xs shrink-0">
                    <i class="fa-solid fa-key"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="text-[10px] font-black uppercase tracking-widest text-purple-700 dark:text-purple-300 bg-purple-50 dark:bg-purple-950/60 px-2.5 py-0.5 rounded-full border border-purple-200/80 dark:border-purple-800/70">TBS Kliniek Illegaal</span>
                        @if(($levelMultiplier ?? 1.0) > 1.0)
                        <span class="text-[10px] font-black uppercase tracking-widest text-emerald-700 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-950/60 px-2.5 py-0.5 rounded-full border border-emerald-200/80 dark:border-emerald-800/70">
                            <i class="fa-solid fa-arrow-trend-up"></i> Level Bonus: +{{ round((($levelMultiplier ?? 1.0) - 1.0) * 100) }}% Buit &amp; XP
                        </span>
                        @endif
                    </div>
                    <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight mt-1">
                        Kluisjes Kraken
                    </h1>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 max-w-xl">
                        Crack kluisjes en lockerkasten in de kliniek om contant geld, geheime sleutels en respect te scoren. Hogere kluizen vereisen meer energie maar leveren flink op.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="flex items-center gap-3 bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl px-5 py-3.5 shadow-xs">
        <i class="fa-solid fa-bolt text-emerald-500 text-sm"></i>
        <span class="text-xs font-bold text-slate-700 dark:text-slate-300">Energie: <span class="text-emerald-600 dark:text-emerald-400 font-black font-mono">{{ $character->energy }} / {{ $character->max_energy }}</span></span>
        <div class="flex-1 bg-slate-100 dark:bg-slate-800/80 rounded-full h-2 overflow-hidden border border-slate-200/60 dark:border-slate-700/60">
            <div class="bg-emerald-500 h-full rounded-full transition-all duration-300" style="width: {{ min(100, round(($character->energy / max(1, $character->max_energy)) * 100)) }}%"></div>
        </div>
    </div>

    <div x-show="seconds > 0" class="p-4 rounded-2xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200/80 dark:border-amber-800/70 flex flex-col sm:flex-row items-center justify-between gap-4" style="{{ $lockerCooldown > 0 ? '' : 'display: none;' }}">
        <div class="flex items-center gap-3.5">
            <div class="w-10 h-10 rounded-xl bg-amber-500 text-white flex items-center justify-center text-lg font-black shrink-0 shadow-xs">
                <i class="fa-solid fa-clock"></i>
            </div>
            <div>
                <h4 class="text-xs font-black text-slate-900 dark:text-white">Kluisjes Cooldown Actief</h4>
                <p class="text-xs text-amber-800 dark:text-amber-300 mt-0.5">Wacht nog <strong class="font-mono text-amber-950 dark:text-amber-200 font-black" x-text="format(seconds)">{{ gmdate('i:s', $lockerCooldown) }}</strong> of omzeil direct met Sterren.</p>
            </div>
        </div>
        <div class="flex items-center gap-2 shrink-0">
            <form method="POST" action="{{ route('cooldown.skip') }}">
                @csrf
                <input type="hidden" name="action" value="locker">
                <button type="submit" @disabled($character->isImprisoned() || $character->isComatose() || ($character->stars ?? 0) < ($lockerSkipCost ?? 10)) class="btn-star-gold h-9 px-4 rounded-xl active:scale-95 text-slate-900 dark:text-white text-xs font-black transition-all duration-150 shadow-xs flex items-center justify-center gap-1.5 cursor-pointer whitespace-nowrap disabled:opacity-50 disabled:cursor-not-allowed">
                    <i class="fa-solid fa-star text-[11px]"></i>
                    <span>Omzeilen ({{ $lockerSkipCost ?? 10 }} Sterren)</span>
                </button>
            </form>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        @foreach($lockers as $locker)
        @php
            $isLockedByLevel = ($locker['min_level'] ?? 1) > $character->level;
            $otherBlocked = $isLockedByLevel || $character->isImprisoned() || $character->isComatose() || $character->energy < $locker['energy'];
            $isDisabled = $otherBlocked || $lockerCooldown > 0;
        @endphp
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/90 dark:border-slate-800 hover:border-purple-300 dark:hover:border-purple-700/80 shadow-xs transition-all duration-200 p-5 sm:p-6 flex flex-col justify-between gap-4 {{ $isLockedByLevel ? 'opacity-85' : '' }}">
            <div class="flex items-start justify-between gap-3.5">
                <div class="flex items-start gap-3.5">
                    <div class="w-11 h-11 rounded-xl bg-purple-50 dark:bg-purple-950/60 border border-purple-200/80 dark:border-purple-800/70 text-purple-600 dark:text-purple-400 flex items-center justify-center text-lg shrink-0 shadow-2xs">
                        <i class="fa-solid {{ $locker['icon'] }}"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-black text-slate-900 dark:text-white">{{ $locker['name'] }}</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 leading-relaxed">{{ $locker['desc'] }}</p>
                    </div>
                </div>
                @if(($locker['min_level'] ?? 1) > 1)
                    <span class="text-[10px] font-black uppercase px-2 py-0.5 rounded-full shrink-0 {{ $isLockedByLevel ? 'bg-amber-100 text-amber-800 dark:bg-amber-950/80 dark:text-amber-300 border border-amber-300/80 dark:border-amber-700' : 'bg-purple-50 text-purple-700 dark:bg-purple-950/60 dark:text-purple-300 border border-purple-200/80 dark:border-purple-800/70' }}">
                        <i class="fa-solid {{ $isLockedByLevel ? 'fa-lock' : 'fa-unlock' }} text-[9px] mr-0.5"></i> Level {{ $locker['min_level'] }}+
                    </span>
                @endif
            </div>
            <div class="pt-3.5 border-t border-slate-100 dark:border-slate-800/80 flex items-center justify-between gap-4 flex-wrap">
                <div class="space-y-1">
                    <div class="flex items-center gap-2.5 text-xs">
                        <span class="text-slate-400 dark:text-slate-500 w-20 font-bold">Buit:</span>
                        <span class="font-black text-emerald-600 dark:text-emerald-400 font-mono">€{{ number_format($locker['min'], 0, ',', '.') }}–€{{ number_format($locker['max'], 0, ',', '.') }}</span>
                    </div>
                    <div class="flex items-center gap-2.5 text-xs">
                        <span class="text-slate-400 dark:text-slate-500 w-20 font-bold">Energie:</span>
                        <span class="font-bold text-amber-600 dark:text-amber-400 font-mono">{{ $locker['energy'] }} EP</span>
                    </div>
                    <div class="flex items-center gap-2.5 text-xs">
                        <span class="text-slate-400 dark:text-slate-500 w-20 font-bold">Slagingskans:</span>
                        <span class="font-bold text-purple-600 dark:text-purple-400 font-mono">{{ $locker['rate'] }}%</span>
                    </div>
                    <div class="flex items-center gap-2.5 text-xs">
                        <span class="text-slate-400 dark:text-slate-500 w-20 font-bold">Cooldown:</span>
                        <span class="font-bold text-slate-700 dark:text-slate-300 font-mono"><i class="fa-solid fa-clock text-[10px] mr-1 text-slate-400"></i>{{ gmdate('i:s', $locker['cooldown']) }}</span>
                    </div>
                </div>
                <form action="{{ route('lockers.crack') }}" method="POST" class="w-full sm:w-auto">
                    @csrf
                    <input type="hidden" name="locker_id" value="{{ $locker['id'] }}">
                    <button type="submit"
                            :disabled="seconds > 0 || {{ $otherBlocked ? 'true' : 'false' }}"
                            :class="(seconds > 0 || {{ $otherBlocked ? 'true' : 'false' }}) ? 'bg-slate-100 dark:bg-slate-800/80 text-slate-400 dark:text-slate-500 border border-slate-200/80 dark:border-slate-700/60 cursor-not-allowed shadow-none' : 'bg-purple-600 hover:bg-purple-700 dark:bg-purple-600 dark:hover:bg-purple-500 text-white cursor-pointer active:scale-95 shadow-xs'"
                            {{ $isDisabled ? 'disabled' : '' }}
                            class="w-full sm:w-auto justify-center px-4 py-2.5 rounded-xl text-xs font-black transition-all duration-150 flex items-center gap-1.5 cursor-pointer">
                        @if($isLockedByLevel)
                            <i class="fa-solid fa-lock text-[11px]"></i>
                            <span>Level {{ $locker['min_level'] }}</span>
                        @else
                            <i class="fa-solid fa-key text-[11px]"></i>
                            <span>Kraken</span>
                        @endif
                    </button>
                </form>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection
