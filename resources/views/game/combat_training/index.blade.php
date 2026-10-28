@extends('layouts.app')

@section('title', 'Vechtoefening & Training - TBS Kliniek')

@section('content')
<div class="space-y-6">
    <!-- HERO HEADER -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl p-6 sm:p-7 text-slate-800 dark:text-slate-100 relative overflow-hidden shadow-xs border border-slate-200 dark:border-slate-800">
        <div class="absolute -right-10 -bottom-10 w-48 h-48 bg-rose-500/10 dark:bg-rose-500/5 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-rose-50 dark:bg-rose-950/60 border border-rose-100 dark:border-rose-800/80 flex items-center justify-center text-2xl text-rose-600 dark:text-rose-400 shadow-2xs shrink-0">
                    <i class="fa-solid fa-dumbbell"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="text-[10px] font-black uppercase tracking-widest text-rose-700 dark:text-rose-300 bg-rose-50 dark:bg-rose-950/60 px-2.5 py-0.5 rounded-full border border-rose-200 dark:border-rose-800">
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
    @php $maxStatCap = $character->getMaxStatForLevel(); @endphp
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3.5">
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 text-center shadow-xs flex flex-col justify-between">
            <div class="text-[10px] font-black uppercase tracking-wider text-slate-400">Kracht (Lvl {{ $character->level }} Max)</div>
            <div class="text-xl font-black {{ $character->strength >= $maxStatCap ? 'text-amber-500 dark:text-amber-400' : 'text-rose-600 dark:text-rose-400' }} font-mono mt-0.5">
                {{ number_format($character->strength, 0) }}
                <span class="text-xs text-slate-400">/ {{ number_format($maxStatCap, 0) }}</span>
            </div>
            @if($character->strength >= $maxStatCap)
                <span class="inline-block mt-1 text-[9px] font-black uppercase px-2 py-0.5 rounded-full bg-amber-100 dark:bg-amber-950/80 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-800/60 self-center">
                    Level Max
                </span>
            @endif
        </div>
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 text-center shadow-xs flex flex-col justify-between">
            <div class="text-[10px] font-black uppercase tracking-wider text-slate-400">Verdediging (Lvl {{ $character->level }} Max)</div>
            <div class="text-xl font-black {{ $character->defense >= $maxStatCap ? 'text-amber-500 dark:text-amber-400' : 'text-blue-600 dark:text-blue-400' }} font-mono mt-0.5">
                {{ number_format($character->defense, 0) }}
                <span class="text-xs text-slate-400">/ {{ number_format($maxStatCap, 0) }}</span>
            </div>
            @if($character->defense >= $maxStatCap)
                <span class="inline-block mt-1 text-[9px] font-black uppercase px-2 py-0.5 rounded-full bg-amber-100 dark:bg-amber-950/80 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-800/60 self-center">
                    Level Max
                </span>
            @endif
        </div>
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 text-center shadow-xs flex flex-col justify-between">
            <div class="text-[10px] font-black uppercase tracking-wider text-slate-400">Conditie (Lvl {{ $character->level }} Max)</div>
            <div class="text-xl font-black {{ $character->stamina >= $maxStatCap ? 'text-amber-500 dark:text-amber-400' : 'text-emerald-600 dark:text-emerald-400' }} font-mono mt-0.5">
                {{ number_format($character->stamina, 0) }}
                <span class="text-xs text-slate-400">/ {{ number_format($maxStatCap, 0) }}</span>
            </div>
            @if($character->stamina >= $maxStatCap)
                <span class="inline-block mt-1 text-[9px] font-black uppercase px-2 py-0.5 rounded-full bg-amber-100 dark:bg-amber-950/80 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-800/60 self-center">
                    Level Max
                </span>
            @endif
        </div>
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 text-center shadow-xs flex flex-col justify-between">
            <div class="text-[10px] font-black uppercase tracking-wider text-slate-400">Discipline (Lvl {{ $character->level }} Max)</div>
            <div class="text-xl font-black {{ $character->discipline >= $maxStatCap ? 'text-amber-500 dark:text-amber-400' : 'text-purple-600 dark:text-purple-400' }} font-mono mt-0.5">
                {{ number_format($character->discipline, 0) }}
                <span class="text-xs text-slate-400">/ {{ number_format($maxStatCap, 0) }}</span>
            </div>
            @if($character->discipline >= $maxStatCap)
                <span class="inline-block mt-1 text-[9px] font-black uppercase px-2 py-0.5 rounded-full bg-amber-100 dark:bg-amber-950/80 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-800/60 self-center">
                    Level Max
                </span>
            @endif
        </div>
    </div>

    <!-- ENERGY & CASH INFO -->
    <div class="flex items-center gap-3.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl px-5 py-3.5 shadow-xs">
        <i class="fa-solid fa-bolt text-emerald-500 text-sm"></i>
        <span class="text-xs font-bold text-slate-700 dark:text-slate-300">Energie: <span class="text-emerald-600 dark:text-emerald-400 font-black font-mono">{{ $character->energy }} / {{ $character->max_energy }}</span></span>
        <div class="flex-1 bg-slate-100 dark:bg-slate-800 rounded-full h-2 overflow-hidden border border-slate-200/60 dark:border-slate-700/60">
            <div class="bg-emerald-500 h-full rounded-full transition-all duration-300" style="width: {{ min(100, round(($character->energy / max(1, $character->max_energy)) * 100)) }}%"></div>
        </div>
        <span class="text-xs font-bold text-slate-500 dark:text-slate-400">Cash: <span class="text-slate-900 dark:text-white font-black font-mono">€{{ number_format($character->cash, 0, ',', '.') }}</span></span>
    </div>

    <!-- TRAINING SESSIONS GRID -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        @foreach($sessions as $session)
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 hover:border-purple-300 dark:hover:border-purple-600 shadow-xs transition p-5 sm:p-6 flex flex-col justify-between gap-4">
            <div class="flex items-start gap-3.5">
                <div class="w-11 h-11 rounded-xl bg-rose-50 dark:bg-rose-950/60 border border-rose-100 dark:border-rose-800/80 text-rose-600 dark:text-rose-400 flex items-center justify-center text-lg shrink-0 shadow-2xs">
                    <i class="fa-solid {{ $session['icon'] }}"></i>
                </div>
                <div>
                    <h3 class="text-sm font-black text-slate-900 dark:text-white">{{ $session['name'] }}</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 leading-relaxed">{{ $session['desc'] }}</p>
                </div>
            </div>

            <div class="pt-3.5 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between gap-4 flex-wrap">
                <div class="space-y-1">
                    <div class="flex items-center gap-2.5 text-xs">
                        <span class="text-slate-400 dark:text-slate-500 font-bold w-16">Stat:</span>
                        <span class="font-black text-slate-900 dark:text-white">+{{ $session['gain'] }} {{ ucfirst($session['stat']) }}</span>
                    </div>
                    <div class="flex items-center gap-2.5 text-xs">
                        <span class="text-slate-400 dark:text-slate-500 font-bold w-16">Kosten:</span>
                        <span class="font-bold text-slate-700 dark:text-slate-300 font-mono">€{{ number_format($session['cost'], 0, ',', '.') }}</span>
                    </div>
                    <div class="flex items-center gap-2.5 text-xs">
                        <span class="text-slate-400 dark:text-slate-500 font-bold w-16">Energie:</span>
                        <span class="font-bold text-amber-600 dark:text-amber-400 font-mono">{{ $session['energy'] }} EP</span>
                    </div>
                </div>

                @php
                    $isStatCapped = ((int) $character->{$session['stat']}) >= $maxStatCap;
                    $canAfford = $character->cash >= $session['cost'];
                    $hasEnergy = $character->energy >= $session['energy'];
                    $isDisabled = $character->isImprisoned() || ! $hasEnergy || ! $canAfford || $isStatCapped;
                @endphp
                <div class="flex flex-col items-end gap-1">
                    <form action="{{ route('combat_training.train') }}" method="POST">
                        @csrf
                        <input type="hidden" name="session_id" value="{{ $session['id'] }}">
                        @if($isStatCapped)
                            <button type="button" disabled
                                    class="px-4 py-2.5 rounded-xl text-xs font-black transition shadow-xs flex items-center gap-1.5 bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-slate-500 border border-slate-200 dark:border-slate-700/60 cursor-not-allowed">
                                <i class="fa-solid fa-lock text-[11px] text-amber-500"></i>
                                <span>Max Lvl {{ $character->level }} Bereikt</span>
                            </button>
                        @else
                            <button type="submit"
                                    {{ $isDisabled ? 'disabled' : '' }}
                                    class="px-4 py-2.5 rounded-xl text-xs font-black transition shadow-xs flex items-center gap-1.5 {{ $isDisabled ? 'bg-slate-200 dark:bg-slate-800 text-slate-400 dark:text-slate-600 cursor-not-allowed' : 'bg-purple-600 hover:bg-purple-700 text-white cursor-pointer active:scale-95 shadow-xs' }}">
                                <i class="fa-solid fa-dumbbell text-[11px]"></i>
                                <span>Trainen</span>
                            </button>
                        @endif
                    </form>
                    @if($isStatCapped)
                        <span class="text-[10px] text-amber-600 dark:text-amber-400 font-bold">Stijg naar Lvl {{ $character->level + 1 }} voor meer stats</span>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection
