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
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 text-center shadow-xs">
            <div class="text-[10px] font-black uppercase tracking-wider text-slate-400">Kracht (Max Lvl Cap)</div>
            <div class="text-xl font-black text-rose-600 dark:text-rose-400 font-mono mt-0.5">{{ number_format($character->strength, 0) }} <span class="text-xs text-slate-400">/ {{ $maxStatCap }}</span></div>
        </div>
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 text-center shadow-xs">
            <div class="text-[10px] font-black uppercase tracking-wider text-slate-400">Verdediging (Max Lvl Cap)</div>
            <div class="text-xl font-black text-blue-600 dark:text-blue-400 font-mono mt-0.5">{{ number_format($character->defense, 0) }} <span class="text-xs text-slate-400">/ {{ $maxStatCap }}</span></div>
        </div>
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 text-center shadow-xs">
            <div class="text-[10px] font-black uppercase tracking-wider text-slate-400">Conditie (Max Lvl Cap)</div>
            <div class="text-xl font-black text-emerald-600 dark:text-emerald-400 font-mono mt-0.5">{{ number_format($character->stamina, 0) }} <span class="text-xs text-slate-400">/ {{ $maxStatCap }}</span></div>
        </div>
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 text-center shadow-xs">
            <div class="text-[10px] font-black uppercase tracking-wider text-slate-400">Discipline (Max Lvl Cap)</div>
            <div class="text-xl font-black text-purple-600 dark:text-purple-400 font-mono mt-0.5">{{ number_format($character->discipline, 0) }} <span class="text-xs text-slate-400">/ {{ $maxStatCap }}</span></div>
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

                <form action="{{ route('combat_training.train') }}" method="POST">
                    @csrf
                    <input type="hidden" name="session_id" value="{{ $session['id'] }}">
                    <button type="submit"
                            {{ $character->isImprisoned() || $character->energy < $session['energy'] || $character->cash < $session['cost'] ? 'disabled' : '' }}
                            class="px-4 py-2.5 rounded-xl text-xs font-black transition shadow-xs flex items-center gap-1.5 {{ $character->isImprisoned() || $character->energy < $session['energy'] || $character->cash < $session['cost'] ? 'bg-slate-200 dark:bg-slate-800 text-slate-400 dark:text-slate-600 cursor-not-allowed' : 'bg-purple-600 hover:bg-purple-700 text-white cursor-pointer active:scale-95 shadow-xs' }}">
                        <i class="fa-solid fa-dumbbell text-[11px]"></i>
                        <span>Trainen</span>
                    </button>
                </form>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection
