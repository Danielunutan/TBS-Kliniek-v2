@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Purple TBS Kliniek Title Banner -->
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-purple-950 via-indigo-900 to-purple-900 p-6 sm:p-8 text-white shadow-lg border border-purple-800/40">
        <div class="absolute -right-10 -bottom-10 w-64 h-64 bg-purple-500/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-purple-500/20 border border-purple-400/30 flex items-center justify-center text-2xl text-purple-300 shadow-inner">
                    <i class="fa-solid fa-key"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="text-[10px] font-black uppercase tracking-widest text-purple-300 bg-purple-900/80 px-2 py-0.5 rounded-md border border-purple-700/50">TBS Kliniek Illegaal</span>
                    </div>
                    <h1 class="text-xl sm:text-2xl font-black text-white tracking-tight mt-1">
                        Kluisjes Kraken
                    </h1>
                    <p class="text-xs text-purple-200/80 mt-0.5 max-w-xl">
                        Crack kluisjes en lockerkasten in de kliniek om contant geld, geheime sleutels en respect te scoren. Hogere kluizen vereisen meer energie maar leveren flink op.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="flex items-center gap-3 bg-white border border-slate-200 rounded-xl px-5 py-3 shadow-xs">
        <i class="fa-solid fa-bolt text-emerald-500"></i>
        <span class="text-xs font-bold text-slate-700">Energie: <span class="text-emerald-700">{{ $character->energy }} / {{ $character->max_energy }}</span></span>
        <div class="flex-1 bg-slate-100 rounded-full h-2 overflow-hidden border border-slate-200">
            <div class="bg-emerald-500 h-full rounded-full" style="width: {{ min(100, round(($character->energy / max(1, $character->max_energy)) * 100)) }}%"></div>
        </div>
    </div>

    @php
        $lockerCooldown = $character->cooldownRemaining('locker');
    @endphp

    @if($lockerCooldown > 0)
        <div class="p-4 rounded-xl bg-amber-500/10 border border-amber-300 flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-amber-500 text-white flex items-center justify-center text-lg font-black shrink-0 shadow-xs">
                    <i class="fa-solid fa-clock"></i>
                </div>
                <div>
                    <h4 class="text-xs font-black text-amber-950">Kluisjes Cooldown Actief</h4>
                    <p class="text-[11px] text-amber-800">Wacht nog <strong>{{ gmdate('i:s', $lockerCooldown) }}</strong> of sla direct over met 1 Ster.</p>
                </div>
            </div>
            <form action="{{ route('cooldown.skip') }}" method="POST">
                @csrf
                <input type="hidden" name="action" value="locker">
                <button type="submit" class="bg-amber-500 hover:bg-amber-600 active:scale-95 text-white text-xs font-black px-4 py-2 rounded-xl transition shadow-xs flex items-center gap-2 cursor-pointer">
                    <i class="fa-solid fa-star text-white text-[11px]"></i>
                    <span>Cooldown Overslaan (1 Ster)</span>
                </button>
            </form>
        </div>
    @endif

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        @foreach($lockers as $locker)
        <div class="bg-white rounded-xl border border-slate-200 hover:border-slate-300 shadow-xs transition p-5 flex flex-col justify-between gap-4">
            <div class="flex items-start gap-3">
                <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                    <i class="fa-solid {{ $locker['icon'] }}"></i>
                </div>
                <div>
                    <h3 class="text-sm font-extrabold text-slate-900">{{ $locker['name'] }}</h3>
                    <p class="text-xs text-slate-500 mt-0.5">{{ $locker['desc'] }}</p>
                </div>
            </div>
            <div class="pt-3 border-t border-slate-100 flex items-center justify-between gap-4">
                <div class="space-y-1">
                    <div class="flex items-center gap-3 text-xs">
                        <span class="text-slate-400 w-20 font-semibold">Buit:</span>
                        <span class="font-extrabold text-emerald-700">€{{ number_format($locker['min'], 0, ',', '.') }}–€{{ number_format($locker['max'], 0, ',', '.') }}</span>
                    </div>
                    <div class="flex items-center gap-3 text-xs">
                        <span class="text-slate-400 w-20 font-semibold">Energie:</span>
                        <span class="font-bold text-amber-700">{{ $locker['energy'] }} EP</span>
                    </div>
                    <div class="flex items-center gap-3 text-xs">
                        <span class="text-slate-400 w-20 font-semibold">Slagingskans:</span>
                        <span class="font-bold text-indigo-600">{{ $locker['rate'] }}%</span>
                    </div>
                </div>
                <form action="{{ route('lockers.crack') }}" method="POST">
                    @csrf
                    <input type="hidden" name="locker_id" value="{{ $locker['id'] }}">
                    <button type="submit"
                            {{ $character->isImprisoned() || $character->energy < $locker['energy'] || $lockerCooldown > 0 ? 'disabled' : '' }}
                            class="px-4 py-2 rounded-lg text-xs font-extrabold text-white transition shadow-xs {{ $character->isImprisoned() || $character->energy < $locker['energy'] || $lockerCooldown > 0 ? 'bg-slate-300 text-slate-500 cursor-not-allowed' : 'bg-slate-900 hover:bg-slate-800' }}">
                        <i class="fa-solid fa-key mr-1"></i> Kraken
                    </button>
                </form>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection
