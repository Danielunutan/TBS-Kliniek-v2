@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Clean TBS Kliniek Title Banner -->
    <div class="rounded-2xl bg-white p-6 sm:p-8 text-slate-800 shadow-xs border border-slate-200 relative overflow-hidden">
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-purple-50 border border-purple-100 flex items-center justify-center text-2xl text-purple-600 shadow-2xs shrink-0">
                    <i class="fa-solid fa-person-military-pointing"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="text-[10px] font-black uppercase tracking-widest text-purple-700 bg-purple-50 px-2 py-0.5 rounded-md border border-purple-200">TBS Kliniek Illegaal</span>
                    </div>
                    <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight mt-1">
                        Afpersen & Chantage
                    </h1>
                    <p class="text-xs text-slate-500 mt-0.5 max-w-xl">
                        Gebruik jouw status en dreiging om slachtoffers in de kliniek te laten betalen. Hogere functies zoals de directie en beveiliging leveren forse contanten op maar verhogen de arrestatiekans.
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
        $extortCooldown = $character->cooldownRemaining('extort');
    @endphp

    @if($extortCooldown > 0)
        <div class="p-4 rounded-xl bg-amber-500/10 border border-amber-300 flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-amber-500 text-white shadow-xs flex items-center justify-center text-lg font-black shrink-0">
                    <i class="fa-solid fa-clock"></i>
                </div>
                <div>
                    <h4 class="text-xs font-black text-amber-950">Afpersen Cooldown Actief</h4>
                    <p class="text-[11px] text-amber-800">Wacht nog <strong>{{ gmdate('i:s', $extortCooldown) }}</strong> of reset direct via een sponsor advertentie.</p>
                </div>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <button type="button" onclick="window.tbsOpenSponsorAd('cooldown')" class="h-9 px-4 rounded-xl bg-purple-600 hover:bg-purple-700 active:scale-95 text-white text-xs font-black transition shadow-xs flex items-center justify-center gap-1.5 cursor-pointer whitespace-nowrap">
                    <i class="fa-solid fa-tv text-[11px]"></i>
                    <span>Kijk Ad (Reset Cooldown)</span>
                </button>
            </div>
        </div>
    @endif

    <!-- Target cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        @foreach($targets as $target)
        <div class="bg-white rounded-xl border border-slate-200 hover:border-slate-300 shadow-xs transition p-5 flex flex-col justify-between gap-4">
            <div class="flex items-start gap-3">
                <div class="w-10 h-10 rounded-xl bg-orange-50 text-orange-600 flex items-center justify-center shrink-0">
                    <i class="fa-solid {{ $target['icon'] }}"></i>
                </div>
                <div>
                    <h3 class="text-sm font-extrabold text-slate-900">{{ $target['name'] }}</h3>
                    <p class="text-xs text-slate-500 mt-0.5">{{ $target['desc'] }}</p>
                </div>
            </div>
            <div class="pt-3 border-t border-slate-100 flex items-center justify-between gap-4">
                <div class="space-y-1">
                    <div class="flex items-center gap-3 text-xs">
                        <span class="text-slate-400 w-20 font-semibold">Beloning:</span>
                        <span class="font-extrabold text-emerald-700">€{{ number_format($target['min'], 0, ',', '.') }}–€{{ number_format($target['max'], 0, ',', '.') }}</span>
                    </div>
                    <div class="flex items-center gap-3 text-xs">
                        <span class="text-slate-400 w-20 font-semibold">Energie:</span>
                        <span class="font-bold text-amber-700">{{ $target['energy'] }} EP</span>
                    </div>
                    <div class="flex items-center gap-3 text-xs">
                        <span class="text-slate-400 w-20 font-semibold">Slagingskans:</span>
                        <span class="font-bold text-indigo-600">{{ $target['rate'] }}%</span>
                    </div>
                    <div class="flex items-center gap-3 text-xs">
                        <span class="text-slate-400 w-20 font-semibold">Cooldown:</span>
                        <span class="font-bold text-amber-600"><i class="fa-solid fa-clock text-[10px] mr-1"></i>{{ gmdate('i:s', $target['cooldown'] ?? 180) }}</span>
                    </div>
                </div>
                <form action="{{ route('extort.extort') }}" method="POST">
                    @csrf
                    <input type="hidden" name="target_id" value="{{ $target['id'] }}">
                    <button type="submit"
                            {{ $character->isImprisoned() || $character->energy < $target['energy'] || $extortCooldown > 0 ? 'disabled' : '' }}
                            class="px-4 py-2 rounded-lg text-xs font-extrabold text-white transition shadow-xs {{ $character->isImprisoned() || $character->energy < $target['energy'] || $extortCooldown > 0 ? 'bg-slate-300 text-slate-500 cursor-not-allowed' : 'bg-slate-900 hover:bg-slate-800' }}">
                        <i class="fa-solid fa-hand-back-fist mr-1"></i> Afpersen
                    </button>
                </form>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection
