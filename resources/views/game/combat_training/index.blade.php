@extends('layouts.app')

@section('title', 'Vechtoefening & Training - TBS Kliniek')

@section('content')
<div class="space-y-6">
    <!-- HERO HEADER -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl p-6 sm:p-7 text-slate-800 dark:text-slate-100 relative overflow-hidden shadow-xs border border-slate-200/90 dark:border-slate-800">
        <div class="absolute -right-10 -bottom-10 w-48 h-48 bg-rose-500/10 dark:bg-rose-500/5 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-rose-50 dark:bg-rose-950/60 border border-rose-200/80 dark:border-rose-800/80 flex items-center justify-center text-2xl text-rose-600 dark:text-rose-400 shadow-2xs shrink-0">
                    <i class="fa-solid fa-dumbbell"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="text-[10px] font-black uppercase tracking-widest text-rose-700 dark:text-rose-300 bg-rose-50 dark:bg-rose-950/60 px-2.5 py-0.5 rounded-full border border-rose-200/80 dark:border-rose-800/70">
                            Kracht &amp; Conditie
                        </span>
                    </div>
                    <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight mt-1">
                        Vechtoefening &amp; Training
                    </h1>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 max-w-xl leading-relaxed">
                        Bouw je fysieke en mentale statistieken op om sterker te worden op de luchtplaats en in bendeoorlogen.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- STATS OVERVIEW CARDS -->
    @php
        $maxStatCap = $character->getMaxStatForLevel();
        $xpCurrent = (int) $character->xp;
        $xpNext = max(1, (int) $character->xpForNextLevel());
        $xpProgressPct = min(100, round(($xpCurrent / $xpNext) * 100));
    @endphp
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3.5">
        <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-4 text-center shadow-xs flex flex-col justify-between">
            <div class="text-[10px] font-black uppercase tracking-wider text-slate-400 dark:text-slate-500">Kracht (Lvl {{ $character->level }} Max)</div>
            <div class="text-xl font-black {{ $character->strength >= $maxStatCap ? 'text-amber-500 dark:text-amber-400' : 'text-rose-600 dark:text-rose-400' }} font-mono mt-0.5">
                {{ number_format(min($character->strength, $maxStatCap), 0) }}
                <span class="text-xs text-slate-400 dark:text-slate-500">/ {{ number_format($maxStatCap, 0) }}</span>
            </div>
            @if($character->strength >= $maxStatCap)
                <span class="inline-block mt-1 text-[9px] font-black uppercase px-2 py-0.5 rounded-full bg-amber-50 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 border border-amber-200/80 dark:border-amber-800/70 self-center">
                    Level Max
                </span>
            @endif
        </div>
        <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-4 text-center shadow-xs flex flex-col justify-between">
            <div class="text-[10px] font-black uppercase tracking-wider text-slate-400 dark:text-slate-500">Verdediging (Lvl {{ $character->level }} Max)</div>
            <div class="text-xl font-black {{ $character->defense >= $maxStatCap ? 'text-amber-500 dark:text-amber-400' : 'text-blue-600 dark:text-blue-400' }} font-mono mt-0.5">
                {{ number_format(min($character->defense, $maxStatCap), 0) }}
                <span class="text-xs text-slate-400 dark:text-slate-500">/ {{ number_format($maxStatCap, 0) }}</span>
            </div>
            @if($character->defense >= $maxStatCap)
                <span class="inline-block mt-1 text-[9px] font-black uppercase px-2 py-0.5 rounded-full bg-amber-50 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 border border-amber-200/80 dark:border-amber-800/70 self-center">
                    Level Max
                </span>
            @endif
        </div>
        <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-4 text-center shadow-xs flex flex-col justify-between">
            <div class="text-[10px] font-black uppercase tracking-wider text-slate-400 dark:text-slate-500">Conditie (Lvl {{ $character->level }} Max)</div>
            <div class="text-xl font-black {{ $character->stamina >= $maxStatCap ? 'text-amber-500 dark:text-amber-400' : 'text-emerald-600 dark:text-emerald-400' }} font-mono mt-0.5">
                {{ number_format(min($character->stamina, $maxStatCap), 0) }}
                <span class="text-xs text-slate-400 dark:text-slate-500">/ {{ number_format($maxStatCap, 0) }}</span>
            </div>
            @if($character->stamina >= $maxStatCap)
                <span class="inline-block mt-1 text-[9px] font-black uppercase px-2 py-0.5 rounded-full bg-amber-50 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 border border-amber-200/80 dark:border-amber-800/70 self-center">
                    Level Max
                </span>
            @endif
        </div>
        <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-4 text-center shadow-xs flex flex-col justify-between">
            <div class="text-[10px] font-black uppercase tracking-wider text-slate-400 dark:text-slate-500">Discipline (Lvl {{ $character->level }} Max)</div>
            <div class="text-xl font-black {{ $character->discipline >= $maxStatCap ? 'text-amber-500 dark:text-amber-400' : 'text-purple-600 dark:text-purple-400' }} font-mono mt-0.5">
                {{ number_format(min($character->discipline, $maxStatCap), 0) }}
                <span class="text-xs text-slate-400 dark:text-slate-500">/ {{ number_format($maxStatCap, 0) }}</span>
            </div>
            @if($character->discipline >= $maxStatCap)
                <span class="inline-block mt-1 text-[9px] font-black uppercase px-2 py-0.5 rounded-full bg-amber-50 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 border border-amber-200/80 dark:border-amber-800/70 self-center">
                    Level Max
                </span>
            @endif
        </div>
    </div>

    <!-- STRATEGY BANNER: LEVEL VERHOGEN VS OEFENINGEN DOEN -->
    <div class="bg-white dark:bg-slate-900 border border-purple-200/80 dark:border-purple-800/60 rounded-2xl p-4 sm:p-5 shadow-xs space-y-3">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-start gap-3">
                <div class="w-10 h-10 rounded-xl bg-purple-50 dark:bg-purple-950/60 border border-purple-200/80 dark:border-purple-800 text-purple-600 dark:text-purple-400 flex items-center justify-center text-base shrink-0">
                    <i class="fa-solid fa-scale-balanced"></i>
                </div>
                <div>
                    <h2 class="text-xs sm:text-sm font-black text-slate-900 dark:text-white">
                        Kies je focus: Oefening (+1 Stat) of Level Verhogen (Dubbele XP)
                    </h2>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5 leading-relaxed">
                        Elke eigenschap heeft een maximum van <strong class="text-slate-800 dark:text-slate-200">{{ number_format($maxStatCap, 0) }}</strong> op <strong class="text-slate-800 dark:text-slate-200">Level {{ $character->level }}</strong> (elke level-up verhoogt het plafond met +50). Kies per sessie of je traint voor directe <strong class="text-purple-600 dark:text-purple-400">+1 Stat</strong> óf voor <strong class="text-amber-600 dark:text-amber-400">Dubbele XP</strong> om sneller in level te stijgen.
                    </p>
                </div>
            </div>
            <div class="shrink-0 bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70 rounded-xl px-3.5 py-2 text-right">
                <div class="text-[10px] font-black uppercase tracking-wider text-slate-400 dark:text-slate-500">Level {{ $character->level }} &rarr; Level {{ $character->level + 1 }}</div>
                <div class="text-xs font-black font-mono text-amber-600 dark:text-amber-400">{{ number_format($xpCurrent, 0, ',', '.') }} / {{ number_format($xpNext, 0, ',', '.') }} XP ({{ $xpProgressPct }}%)</div>
            </div>
        </div>
        <div class="w-full bg-slate-100 dark:bg-slate-800 rounded-full h-2 overflow-hidden border border-slate-200/80 dark:border-slate-700/70">
            <div class="bg-gradient-to-r from-purple-500 to-amber-500 h-full rounded-full transition-all duration-300" style="width: {{ $xpProgressPct }}%"></div>
        </div>
    </div>

    <!-- ENERGY & CASH INFO -->
    <div class="flex items-center gap-3.5 bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl px-5 py-3.5 shadow-xs">
        <i class="fa-solid fa-bolt text-emerald-500 text-sm"></i>
        <span class="text-xs font-bold text-slate-700 dark:text-slate-300">Energie: <span class="text-emerald-600 dark:text-emerald-400 font-black font-mono">{{ $character->energy }} / {{ $character->max_energy }}</span></span>
        <div class="flex-1 bg-slate-100 dark:bg-slate-800 rounded-full h-2 overflow-hidden border border-slate-200/80 dark:border-slate-700/70">
            <div class="bg-emerald-500 h-full rounded-full transition-all duration-300" style="width: {{ min(100, round(($character->energy / max(1, $character->max_energy)) * 100)) }}%"></div>
        </div>
        <span class="text-xs font-bold text-slate-500 dark:text-slate-400">Cash: <span class="text-slate-900 dark:text-white font-black font-mono">€{{ number_format($character->cash, 0, ',', '.') }}</span></span>
    </div>

    <!-- TRAINING SESSIONS GRID -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        @foreach($sessions as $session)
        @php
            $currentStatVal = min($maxStatCap, (int) $character->{$session['stat']});
            $isStatCapped = $currentStatVal >= $maxStatCap;
            $canAfford = $character->cash >= $session['cost'];
            $hasEnergy = $character->energy >= $session['energy'];
            $baseDisabled = $character->isImprisoned() || $character->isComatose() || ! $hasEnergy || ! $canAfford;
            $statTrainDisabled = $baseDisabled || $isStatCapped;
        @endphp
        <div class="bg-white dark:bg-slate-900 rounded-2xl border {{ $isStatCapped ? 'border-amber-300/80 dark:border-amber-700/60' : 'border-slate-200/90 dark:border-slate-800 hover:border-purple-300 dark:hover:border-purple-700/80' }} shadow-xs transition-all duration-200 p-5 sm:p-6 flex flex-col justify-between gap-4">
            <div class="flex items-start justify-between gap-3.5">
                <div class="flex items-start gap-3.5">
                    <div class="w-11 h-11 rounded-xl bg-rose-50 dark:bg-rose-950/60 border border-rose-200/80 dark:border-rose-800/80 text-rose-600 dark:text-rose-400 flex items-center justify-center text-lg shrink-0 shadow-2xs">
                        <i class="fa-solid {{ $session['icon'] }}"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-black text-slate-900 dark:text-white">{{ $session['name'] }}</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 leading-relaxed">{{ $session['desc'] }}</p>
                    </div>
                </div>
                <span class="shrink-0 text-[10px] font-black font-mono px-2.5 py-1 rounded-xl border {{ $isStatCapped ? 'bg-amber-50 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 border-amber-200/80 dark:border-amber-800/70' : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200/80 dark:border-slate-700' }}">
                    {{ $currentStatVal }} / {{ $maxStatCap }}
                </span>
            </div>

            <div class="pt-3.5 border-t border-slate-100 dark:border-slate-800 space-y-3">
                <div class="grid grid-cols-2 gap-2 text-xs">
                    <div class="bg-slate-50/90 dark:bg-slate-800/60 rounded-xl px-3 py-2 border border-slate-200/80 dark:border-slate-700/70">
                        <span class="text-[10px] uppercase font-bold text-slate-400 dark:text-slate-500 block">Kosten &amp; Energie</span>
                        <span class="font-bold text-slate-800 dark:text-slate-200 font-mono">€{{ number_format($session['cost'], 0, ',', '.') }}</span>
                        <span class="text-slate-400 dark:text-slate-500 mx-1">&bull;</span>
                        <span class="font-bold text-amber-600 dark:text-amber-400 font-mono">{{ $session['energy'] }} EP</span>
                    </div>
                    <div class="bg-slate-50/90 dark:bg-slate-800/60 rounded-xl px-3 py-2 border border-slate-200/80 dark:border-slate-700/70">
                        <span class="text-[10px] uppercase font-bold text-slate-400 dark:text-slate-500 block">Opbrengst Keuze</span>
                        <span class="font-black text-purple-600 dark:text-purple-400">+{{ $session['gain'] }} {{ $session['label'] }} (+{{ $session['xp'] }} XP)</span>
                        <span class="text-slate-400 dark:text-slate-500 block text-[10px]">óf <strong class="text-amber-600 dark:text-amber-400">+{{ $session['level_xp'] }} XP</strong> (Level focus)</span>
                    </div>
                </div>

                <form action="{{ route('combat_training.train') }}" method="POST" class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                    @csrf
                    <input type="hidden" name="session_id" value="{{ $session['id'] }}">

                    @if($isStatCapped)
                        <button type="button" disabled
                                class="w-full px-3 py-2.5 rounded-xl text-xs font-black transition-all duration-150 flex items-center justify-center gap-1.5 bg-slate-100 dark:bg-slate-800/80 text-slate-400 dark:text-slate-500 border border-slate-200/80 dark:border-slate-700/60 cursor-not-allowed">
                            <i class="fa-solid fa-lock text-[11px] text-amber-500"></i>
                            <span>Max {{ $session['label'] }} (Lvl {{ $character->level }})</span>
                        </button>
                    @else
                        <button type="submit" name="mode" value="stat"
                                {{ $statTrainDisabled ? 'disabled' : '' }}
                                class="w-full px-3 py-2.5 rounded-xl text-xs font-black transition-all duration-150 flex items-center justify-center gap-1.5 {{ $statTrainDisabled ? 'bg-slate-100 dark:bg-slate-800/80 text-slate-400 dark:text-slate-500 border border-slate-200/80 dark:border-slate-700/60 cursor-not-allowed' : 'bg-purple-600 hover:bg-purple-700 dark:bg-purple-600 dark:hover:bg-purple-500 text-white cursor-pointer active:scale-95 shadow-xs' }}">
                            <i class="fa-solid fa-dumbbell text-[11px]"></i>
                            <span>Oefening (+1 {{ $session['label'] }})</span>
                        </button>
                    @endif

                    <button type="submit" name="mode" value="level"
                            {{ $baseDisabled ? 'disabled' : '' }}
                            class="w-full px-3 py-2.5 rounded-xl text-xs font-black transition-all duration-150 flex items-center justify-center gap-1.5 {{ $baseDisabled ? 'bg-slate-100 dark:bg-slate-800/80 text-slate-400 dark:text-slate-500 border border-slate-200/80 dark:border-slate-700/60 cursor-not-allowed' : ($isStatCapped ? 'bg-amber-500 hover:bg-amber-400 text-slate-950 cursor-pointer active:scale-95 ring-2 ring-amber-400/50 shadow-xs' : 'bg-amber-500 hover:bg-amber-400 text-slate-950 cursor-pointer active:scale-95 shadow-xs') }}">
                        <i class="fa-solid fa-angles-up text-[11px]"></i>
                        <span>Level Focus (+{{ $session['level_xp'] }} XP)</span>
                    </button>
                </form>

                @if($isStatCapped)
                    <p class="text-[11px] text-amber-600 dark:text-amber-400 font-bold text-center">
                        <i class="fa-solid fa-circle-info mr-1"></i>{{ $session['label'] }} zit op het maximum ({{ $maxStatCap }}) voor Level {{ $character->level }}. Gebruik <strong>Level Focus (+{{ $session['level_xp'] }} XP)</strong> om naar Level {{ $character->level + 1 }} te stijgen!
                    </p>
                @endif
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection
