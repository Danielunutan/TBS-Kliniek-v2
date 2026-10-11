@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="bg-white rounded-2xl p-6 sm:p-8 text-slate-800 relative overflow-hidden shadow-xs border border-slate-200">
        <div class="relative z-10">
            <div class="inline-flex items-center gap-2 bg-rose-50 text-rose-700 text-xs font-bold px-3 py-1 rounded-full border border-rose-200 mb-3">
                <i class="fa-solid fa-dumbbell text-rose-600"></i> Kracht & Conditie
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900">Vechtoefening & Training</h1>
            <p class="text-slate-500 text-xs sm:text-sm mt-1 max-w-xl">Bouw je fysieke en mentale statistieken op om sterker te worden op de luchtplaats en in bendeoorlogen.</p>
        </div>
        <div class="absolute -right-6 -bottom-6 text-slate-100 text-9xl font-black select-none pointer-events-none">
            <i class="fa-solid fa-hand-fist"></i>
        </div>
    </div>

    <!-- Status Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        <div class="bg-white border border-slate-200 rounded-xl p-3 text-center shadow-xs">
            <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Kracht</div>
            <div class="text-lg font-black text-rose-600 mt-0.5">{{ number_format($character->strength, 0) }}</div>
        </div>
        <div class="bg-white border border-slate-200 rounded-xl p-3 text-center shadow-xs">
            <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Verdediging</div>
            <div class="text-lg font-black text-blue-600 mt-0.5">{{ number_format($character->defense, 0) }}</div>
        </div>
        <div class="bg-white border border-slate-200 rounded-xl p-3 text-center shadow-xs">
            <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Uithouding</div>
            <div class="text-lg font-black text-emerald-600 mt-0.5">{{ number_format($character->stamina, 0) }}</div>
        </div>
        <div class="bg-white border border-slate-200 rounded-xl p-3 text-center shadow-xs">
            <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Discipline</div>
            <div class="text-lg font-black text-purple-600 mt-0.5">{{ number_format($character->discipline, 0) }}</div>
        </div>
    </div>

    <!-- Energy Info -->
    <div class="flex items-center gap-3 bg-white border border-slate-200 rounded-xl px-5 py-3 shadow-xs">
        <i class="fa-solid fa-bolt text-emerald-500"></i>
        <span class="text-xs font-bold text-slate-700">Energie: <span class="text-emerald-700">{{ $character->energy }} / {{ $character->max_energy }}</span></span>
        <div class="flex-1 bg-slate-100 rounded-full h-2 overflow-hidden border border-slate-200">
            <div class="bg-emerald-500 h-full rounded-full" style="width: {{ min(100, round(($character->energy / max(1, $character->max_energy)) * 100)) }}%"></div>
        </div>
        <span class="text-xs font-bold text-slate-500">Cash: <span class="text-slate-800 font-extrabold">{{ number_format($character->cash, 0, ',', '.') }} KC</span></span>
    </div>

    <!-- Training Sessions -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        @foreach($sessions as $session)
        <div class="bg-white rounded-xl border border-slate-200 hover:border-slate-300 shadow-xs transition p-5 flex flex-col justify-between gap-4">
            <div class="flex items-start gap-3">
                <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center shrink-0">
                    <i class="fa-solid {{ $session['icon'] }}"></i>
                </div>
                <div>
                    <h3 class="text-sm font-extrabold text-slate-900">{{ $session['name'] }}</h3>
                    <p class="text-xs text-slate-500 mt-0.5">{{ $session['desc'] }}</p>
                </div>
            </div>

            <div class="pt-3 border-t border-slate-100 flex items-center justify-between gap-4">
                <div class="space-y-1">
                    <div class="flex items-center gap-2 text-xs">
                        <span class="text-slate-400 font-semibold w-16">Stat:</span>
                        <span class="font-extrabold text-slate-800">+{{ $session['gain'] }} {{ ucfirst($session['stat']) }}</span>
                    </div>
                    <div class="flex items-center gap-2 text-xs">
                        <span class="text-slate-400 font-semibold w-16">Kosten:</span>
                        <span class="font-bold text-slate-700">{{ number_format($session['cost'], 0, ',', '.') }} KC</span>
                    </div>
                    <div class="flex items-center gap-2 text-xs">
                        <span class="text-slate-400 font-semibold w-16">Energie:</span>
                        <span class="font-bold text-amber-600">{{ $session['energy'] }} EP</span>
                    </div>
                </div>

                <form action="{{ route('combat_training.train') }}" method="POST">
                    @csrf
                    <input type="hidden" name="session_id" value="{{ $session['id'] }}">
                    <button type="submit"
                            {{ $character->isImprisoned() || $character->energy < $session['energy'] || $character->cash < $session['cost'] ? 'disabled' : '' }}
                            class="px-4 py-2 rounded-lg text-xs font-extrabold text-white transition shadow-xs {{ $character->isImprisoned() || $character->energy < $session['energy'] || $character->cash < $session['cost'] ? 'bg-slate-300 text-slate-500 cursor-not-allowed' : 'bg-slate-900 hover:bg-slate-800 active:scale-95' }}">
                        <i class="fa-solid fa-dumbbell mr-1"></i> Trainen
                    </button>
                </form>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection
