@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="bg-gradient-to-r from-slate-900 to-sky-950 rounded-2xl p-6 sm:p-8 text-white relative overflow-hidden">
        <div class="relative z-10">
            <div class="inline-flex items-center gap-2 bg-sky-500/20 text-sky-300 text-xs font-bold px-3 py-1 rounded-full border border-sky-400/20 mb-3">
                <i class="fa-solid fa-user-secret"></i> Inlichtingendienst
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">Informantennetwerk</h1>
            <p class="text-slate-300 text-xs sm:text-sm mt-1 max-w-xl">Huur spionnen en informanten in binnen de kliniek om geheime info en waardevolle binnengesmokkelde cash te verzamelen.</p>
        </div>
        <div class="absolute -right-6 -bottom-6 text-white/5 text-9xl font-black select-none pointer-events-none">
            <i class="fa-solid fa-user-secret"></i>
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

    <!-- Informants Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        @foreach($informants as $inf)
        <div class="bg-white rounded-xl border border-slate-200 hover:border-slate-300 shadow-xs transition p-5 flex flex-col justify-between gap-4">
            <div class="flex items-start gap-3">
                <div class="w-10 h-10 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center shrink-0">
                    <i class="fa-solid {{ $inf['icon'] }}"></i>
                </div>
                <div>
                    <h3 class="text-sm font-extrabold text-slate-900">{{ $inf['name'] }}</h3>
                    <p class="text-xs text-slate-500 mt-0.5">{{ $inf['desc'] }}</p>
                </div>
            </div>

            <div class="pt-3 border-t border-slate-100 flex items-center justify-between gap-4">
                <div class="space-y-1">
                    <div class="flex items-center gap-2 text-xs">
                        <span class="text-slate-400 font-semibold w-16">Kosten:</span>
                        <span class="font-bold text-slate-700">{{ number_format($inf['cost'], 0, ',', '.') }} KC</span>
                    </div>
                    <div class="flex items-center gap-2 text-xs">
                        <span class="text-slate-400 font-semibold w-16">Buit:</span>
                        <span class="font-extrabold text-emerald-700">{{ number_format($inf['reward_min'], 0, ',', '.') }}–{{ number_format($inf['reward_max'], 0, ',', '.') }} KC</span>
                    </div>
                    <div class="flex items-center gap-2 text-xs">
                        <span class="text-slate-400 font-semibold w-16">Energie:</span>
                        <span class="font-bold text-amber-600">{{ $inf['energy'] }} EP</span>
                    </div>
                </div>

                <form action="{{ route('informants.hire') }}" method="POST">
                    @csrf
                    <input type="hidden" name="informant_id" value="{{ $inf['id'] }}">
                    <button type="submit"
                            {{ $character->isImprisoned() || $character->energy < $inf['energy'] || $character->cash < $inf['cost'] ? 'disabled' : '' }}
                            class="px-4 py-2 rounded-lg text-xs font-extrabold text-white transition shadow-xs {{ $character->isImprisoned() || $character->energy < $inf['energy'] || $character->cash < $inf['cost'] ? 'bg-slate-300 text-slate-500 cursor-not-allowed' : 'bg-slate-900 hover:bg-slate-800 active:scale-95' }}">
                        <i class="fa-solid fa-user-secret mr-1"></i> Inhuren
                    </button>
                </form>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection
