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
    <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-6 sm:p-8 shadow-xs relative overflow-hidden">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 relative z-10">
            <div>
                <div class="inline-flex items-center gap-2 bg-purple-50 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 text-xs font-bold px-3 py-1 rounded-full border border-purple-200/80 dark:border-purple-800/70 mb-3">
                    <i class="fa-solid fa-mask"></i> Illegale Zakkenrollerij
                </div>
                <h1 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight">Zakkenrollen</h1>
                <p class="text-slate-500 dark:text-slate-400 text-xs sm:text-sm mt-1 max-w-xl leading-relaxed">
                    Trek je masker op en sla toe in de drukke wandelgangen van de kliniek. Elke move kost 1 masker. Scoor contant zwart geld, smokkelkoffers en respect!
                </p>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <span class="px-3.5 py-2 rounded-xl bg-purple-50 dark:bg-purple-950/60 border border-purple-200/80 dark:border-purple-800/70 text-xs font-bold text-purple-700 dark:text-purple-300 flex items-center gap-1.5 shadow-xs">
                    <i class="fa-solid fa-mask text-purple-600 dark:text-purple-400"></i> {{ $character->masks ?? 0 }} Maskers
                </span>
                <a href="{{ route('shop.index') }}" class="px-4 py-2 rounded-xl bg-purple-600 hover:bg-purple-700 dark:bg-purple-600 dark:hover:bg-purple-500 text-white font-black text-xs uppercase tracking-wider transition-all duration-150 active:scale-95 shadow-xs flex items-center gap-1.5 cursor-pointer">
                    <i class="fa-solid fa-cart-shopping"></i> Koop Maskers
                </a>
            </div>
        </div>
    </div>

    <!-- Mask Inventory Banner -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-5 shadow-xs flex flex-col sm:flex-row items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-purple-50 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400 border border-purple-200/80 dark:border-purple-800/70 flex items-center justify-center text-xl shrink-0 shadow-2xs">
                <i class="fa-solid fa-mask"></i>
            </div>
            <div>
                <h3 class="text-xs font-black text-slate-900 dark:text-white">Maskervoorraad</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400">
                    Je bezit momenteel <strong class="text-slate-900 dark:text-white font-bold">{{ $character->masks ?? 0 }}</strong> masker(s). Je masker wordt verbruikt bij een geslaagde diefstal of bij arrestatie. Ontsnap je bij een mislukte poging? Dan behoud je je masker!
                </p>
            </div>
        </div>
        @if(($character->masks ?? 0) <= 0)
            <span class="text-xs font-bold text-rose-700 dark:text-rose-300 bg-rose-50 dark:bg-rose-950/60 px-3 py-1.5 rounded-xl border border-rose-200/80 dark:border-rose-800/70">
                <i class="fa-solid fa-triangle-exclamation mr-1"></i> Geen maskers beschikbaar
            </span>
        @else
            <div>
                <span x-show="seconds > 0" class="text-xs font-bold text-amber-800 dark:text-amber-300 bg-amber-50 dark:bg-amber-950/60 px-3 py-1.5 rounded-xl border border-amber-200/80 dark:border-amber-800/70" style="{{ $ppCooldown > 0 ? '' : 'display: none;' }}">
                    <i class="fa-solid fa-clock mr-1"></i> Cooldown: <strong class="font-mono text-amber-950 dark:text-amber-200" x-text="format(seconds)">{{ gmdate('i:s', $ppCooldown) }}</strong>
                </span>
                <span x-show="seconds <= 0" class="text-xs font-bold text-emerald-700 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-950/60 px-3 py-1.5 rounded-xl border border-emerald-200/80 dark:border-emerald-800/70" style="{{ $ppCooldown <= 0 ? '' : 'display: none;' }}">
                    <i class="fa-solid fa-check mr-1"></i> Klaar voor actie
                </span>
            </div>
        @endif
    </div>

    <div x-show="seconds > 0" class="p-4 rounded-2xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200/80 dark:border-amber-800/70 flex flex-col sm:flex-row items-center justify-between gap-4" style="{{ $ppCooldown > 0 ? '' : 'display: none;' }}">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-amber-500 text-white flex items-center justify-center text-lg font-black shrink-0 shadow-xs">
                <i class="fa-solid fa-clock"></i>
            </div>
            <div>
                <h4 class="text-xs font-black text-slate-900 dark:text-white">Zakkenrollen Cooldown Actief</h4>
                <p class="text-xs text-amber-800 dark:text-amber-300 mt-0.5">Wacht nog <strong class="font-mono text-amber-950 dark:text-amber-200 font-black" x-text="format(seconds)">{{ gmdate('i:s', $ppCooldown) }}</strong> of omzeil direct met Sterren.</p>
            </div>
        </div>
        <div class="flex items-center gap-2 shrink-0">
            <form method="POST" action="{{ route('cooldown.skip') }}">
                @csrf
                <input type="hidden" name="action" value="pickpocket">
                <button type="submit" class="btn-star-gold h-9 px-4 rounded-xl active:scale-95 text-slate-900 dark:text-white text-xs font-black transition-all duration-150 shadow-xs flex items-center justify-center gap-1.5 cursor-pointer whitespace-nowrap">
                    <i class="fa-solid fa-star text-[11px]"></i>
                    <span>Omzeilen (10 Sterren)</span>
                </button>
            </form>
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
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/90 dark:border-slate-800 hover:border-purple-300 dark:hover:border-purple-700/80 shadow-xs transition-all duration-200 p-5 flex flex-col justify-between gap-4">
            <div class="flex items-start gap-3">
                <div class="w-10 h-10 rounded-xl bg-purple-50 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400 border border-purple-200/80 dark:border-purple-800/70 flex items-center justify-center shrink-0 shadow-2xs">
                    <i class="fa-solid {{ $action['icon'] }}"></i>
                </div>
                <div>
                    <h3 class="text-sm font-black text-slate-900 dark:text-white">{{ $action['name'] }}</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ $action['desc'] }}</p>
                </div>
            </div>

            <div class="pt-3 border-t border-slate-100 dark:border-slate-800/80 space-y-2">
                <div class="text-[11px] space-y-1 text-slate-600 dark:text-slate-400">
                    <div class="flex justify-between">
                        <span class="text-slate-400 dark:text-slate-500 font-semibold">Buit:</span>
                        <span class="font-black text-emerald-600 dark:text-emerald-400 font-mono">€{{ number_format($action['min'], 0, ',', '.') }}–€{{ number_format($action['max'], 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-400 dark:text-slate-500 font-semibold">Kans op slagen:</span>
                        <span class="font-bold text-slate-800 dark:text-slate-200 font-mono">{{ $action['rate'] }}%</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-400 dark:text-slate-500 font-semibold">Cooldown:</span>
                        <span class="font-bold text-amber-600 dark:text-amber-400 font-mono"><i class="fa-solid fa-clock text-[10px] mr-1"></i>{{ gmdate('i:s', $action['cooldown'] ?? 180) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-400 dark:text-slate-500 font-semibold">Energie & Maskers:</span>
                        <span class="font-bold text-purple-600 dark:text-purple-400 font-mono">{{ $action['energy'] }} EP &bull; 1 Masker</span>
                    </div>
                </div>

                <form action="{{ route('pickpocket.steal') }}" method="POST">
                    @csrf
                    <input type="hidden" name="action_id" value="{{ $action['id'] }}">
                    <button type="submit" 
                            :disabled="seconds > 0 || {{ $otherBlocked ? 'true' : 'false' }}"
                            :class="(seconds > 0 || {{ $otherBlocked ? 'true' : 'false' }}) ? 'bg-slate-100 dark:bg-slate-800/80 text-slate-400 dark:text-slate-500 border border-slate-200/80 dark:border-slate-700/60 cursor-not-allowed shadow-none' : 'bg-purple-600 hover:bg-purple-700 dark:bg-purple-600 dark:hover:bg-purple-500 text-white shadow-xs cursor-pointer active:scale-95'"
                            {{ $isDisabled ? 'disabled' : '' }}
                            class="w-full py-2.5 px-3 rounded-xl text-xs font-black transition-all duration-150 flex items-center justify-center gap-1.5 cursor-pointer">
                        <i class="fa-solid fa-hand-holding-dollar text-[11px]"></i>
                        <span>Zakkenrollen</span>
                    </button>
                </form>
            </div>
        </div>
        @endforeach
    </div>

</div>
@endsection
