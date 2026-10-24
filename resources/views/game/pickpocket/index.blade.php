@extends('layouts.app')

@section('content')
@php
    $ppCooldown = $character->cooldownRemaining('pickpocket');
@endphp
<div class="space-y-6" x-data="{
    seconds: {{ (int) $ppCooldown }},
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

    <!-- Top Hero Banner (Clean TBS White Style) -->
    <div class="bg-white border border-slate-200 rounded-2xl p-6 sm:p-8 shadow-xs relative overflow-hidden">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 relative z-10">
            <div>
                <div class="inline-flex items-center gap-2 bg-purple-50 text-purple-700 text-xs font-bold px-3 py-1 rounded-full border border-purple-200 mb-3">
                    <i class="fa-solid fa-mask"></i> Illegale Zakkenrollerij
                </div>
                <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">Zakkenrollen</h1>
                <p class="text-slate-500 text-xs sm:text-sm mt-1 max-w-xl leading-relaxed">
                    Trek je masker op en sla toe in de drukke wandelgangen van de kliniek. Elke move kost 1 masker. Scoor contant zwart geld, smokkelkoffers en respect!
                </p>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <span class="px-3.5 py-2 rounded-xl bg-purple-50 border border-purple-200 text-xs font-bold text-purple-700 flex items-center gap-1.5 shadow-xs">
                    <i class="fa-solid fa-mask text-purple-600"></i> {{ $character->masks ?? 0 }} Maskers
                </span>
                <a href="{{ route('shop.index') }}" class="px-4 py-2 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-extrabold text-xs uppercase tracking-wider transition shadow-sm flex items-center gap-1.5">
                    <i class="fa-solid fa-cart-shopping"></i> Koop Maskers
                </a>
            </div>
        </div>
    </div>

    <!-- Mask Inventory Banner -->
    <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-xs flex flex-col sm:flex-row items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-purple-100 text-purple-700 flex items-center justify-center text-xl shrink-0">
                <i class="fa-solid fa-mask"></i>
            </div>
            <div>
                <h3 class="text-xs font-extrabold text-slate-900">Maskervoorraad</h3>
                <p class="text-xs text-slate-500">
                    Je bezit momenteel <strong class="text-slate-900">{{ $character->masks ?? 0 }}</strong> masker(s). Je masker wordt verbruikt bij een geslaagde diefstal of bij arrestatie. Ontsnap je bij een mislukte poging? Dan behoud je je masker!
                </p>
            </div>
        </div>
        @if(($character->masks ?? 0) <= 0)
            <span class="text-xs font-bold text-rose-600 bg-rose-50 px-3 py-1.5 rounded-lg border border-rose-200">
                <i class="fa-solid fa-triangle-exclamation mr-1"></i> Geen maskers beschikbaar
            </span>
        @else
            <div>
                <span x-show="seconds > 0" class="text-xs font-bold text-amber-700 bg-amber-50 px-3 py-1.5 rounded-lg border border-amber-200" style="{{ $ppCooldown > 0 ? '' : 'display: none;' }}">
                    <i class="fa-solid fa-clock mr-1"></i> Cooldown: <strong class="font-mono" x-text="format(seconds)">{{ gmdate('i:s', $ppCooldown) }}</strong>
                </span>
                <span x-show="seconds <= 0" class="text-xs font-bold text-emerald-700 bg-emerald-50 px-3 py-1.5 rounded-lg border border-emerald-200" style="{{ $ppCooldown <= 0 ? '' : 'display: none;' }}">
                    <i class="fa-solid fa-check mr-1"></i> Klaar voor actie
                </span>
            </div>
        @endif
    </div>

    <div x-show="seconds > 0" class="p-4 rounded-xl bg-amber-50 border border-amber-200 flex flex-col sm:flex-row items-center justify-between gap-4" style="{{ $ppCooldown > 0 ? '' : 'display: none;' }}">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-amber-500 text-white flex items-center justify-center text-lg font-black shrink-0 shadow-xs">
                <i class="fa-solid fa-clock"></i>
            </div>
            <div>
                <h4 class="text-xs font-extrabold text-amber-950">Zakkenrollen Cooldown Actief</h4>
                <p class="text-[11px] text-amber-800">Wacht nog <strong class="font-mono text-amber-950 font-bold" x-text="format(seconds)">{{ gmdate('i:s', $ppCooldown) }}</strong> of reset direct via een sponsor advertentie.</p>
            </div>
        </div>
        <div class="flex items-center gap-2 shrink-0">
            <button type="button" onclick="window.tbsOpenSponsorAd('cooldown')" class="h-9 px-4 rounded-xl bg-purple-600 hover:bg-purple-700 active:scale-95 text-white text-xs font-black transition shadow-xs flex items-center justify-center gap-1.5 cursor-pointer whitespace-nowrap">
                <i class="fa-solid fa-tv text-[11px]"></i>
                <span>Kijk Ad (Reset Cooldown)</span>
            </button>
        </div>
    </div>

    <!-- Moves List -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($actions as $action)
        @php
            $hasMasks = ($character->masks ?? 0) >= 1;
            $hasEnergy = $character->energy >= $action['energy'];
            $otherBlocked = !$hasMasks || !$hasEnergy || $character->isImprisoned();
            $isDisabled = $otherBlocked || $ppCooldown > 0;
        @endphp
        <div class="bg-white rounded-xl border border-slate-200 hover:border-purple-300 shadow-xs transition p-5 flex flex-col justify-between gap-4">
            <div class="flex items-start gap-3">
                <div class="w-10 h-10 rounded-xl bg-purple-100 text-purple-700 flex items-center justify-center shrink-0">
                    <i class="fa-solid {{ $action['icon'] }}"></i>
                </div>
                <div>
                    <h3 class="text-sm font-extrabold text-slate-900">{{ $action['name'] }}</h3>
                    <p class="text-xs text-slate-500 mt-0.5">{{ $action['desc'] }}</p>
                </div>
            </div>

            <div class="pt-3 border-t border-slate-100 space-y-2">
                <div class="text-[11px] space-y-1 text-slate-600">
                    <div class="flex justify-between">
                        <span class="text-slate-400 font-semibold">Buit:</span>
                        <span class="font-extrabold text-emerald-700">€{{ number_format($action['min'], 0, ',', '.') }}–€{{ number_format($action['max'], 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-400 font-semibold">Kans op slagen:</span>
                        <span class="font-bold text-slate-800">{{ $action['rate'] }}%</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-400 font-semibold">Cooldown:</span>
                        <span class="font-bold text-amber-600"><i class="fa-solid fa-clock text-[10px] mr-1"></i>{{ gmdate('i:s', $action['cooldown'] ?? 180) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-400 font-semibold">Energie & Maskers:</span>
                        <span class="font-bold text-purple-600">{{ $action['energy'] }} EP &bull; 1 Masker</span>
                    </div>
                </div>

                <form action="{{ route('pickpocket.steal') }}" method="POST">
                    @csrf
                    <input type="hidden" name="action_id" value="{{ $action['id'] }}">
                    <button type="submit" 
                            :disabled="seconds > 0 || {{ $otherBlocked ? 'true' : 'false' }}"
                            :class="(seconds > 0 || {{ $otherBlocked ? 'true' : 'false' }}) ? 'bg-slate-200 text-slate-400 cursor-not-allowed' : 'bg-slate-900 hover:bg-slate-800 text-white shadow-xs cursor-pointer'"
                            {{ $isDisabled ? 'disabled' : '' }}
                            class="w-full py-2.5 px-3 rounded-lg text-xs font-bold transition {{ $isDisabled ? 'bg-slate-200 text-slate-400 cursor-not-allowed' : 'bg-slate-900 hover:bg-slate-800 text-white shadow-xs cursor-pointer' }}">
                        Zakkenrollen
                    </button>
                </form>
            </div>
        </div>
        @endforeach
    </div>

</div>
@endsection
