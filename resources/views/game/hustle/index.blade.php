@extends('layouts.app')

@section('title', 'Hosselen & Klussen - TBS Kliniek')

@section('content')
@php
    $hustleCooldown = $character->cooldownRemaining('hustle');
@endphp
<div class="space-y-6" x-data="{
    seconds: {{ (int) $hustleCooldown }},
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

    <!-- Top Forensic Dossier Header -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xs border border-slate-200/90 dark:border-slate-800 p-5 sm:p-7 overflow-hidden">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-5">
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-2xl bg-purple-100 dark:bg-purple-950/70 border border-purple-200 dark:border-purple-800 text-purple-600 dark:text-purple-400 flex items-center justify-center text-xl sm:text-2xl shrink-0 shadow-xs">
                    <i class="fa-solid fa-hand-holding-dollar"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2 mb-1 flex-wrap">
                        <span class="text-[9px] font-black uppercase tracking-wider text-purple-700 dark:text-purple-300 bg-purple-50 dark:bg-purple-950/60 px-2.5 py-0.5 rounded-md border border-purple-200/80 dark:border-purple-800/70">
                            Gangencorridors
                        </span>
                        <span class="text-[9px] font-extrabold text-amber-800 dark:text-amber-300 bg-amber-50 dark:bg-amber-950/60 px-2.5 py-0.5 rounded-md border border-amber-200/80 dark:border-amber-800/70 inline-flex items-center gap-1">
                            <i class="fa-solid fa-wand-magic-sparkles text-[8px]"></i> +{{ $statBonus ?? 0 }}% Slagingskans Bonus
                        </span>
                        <span class="text-[9px] font-extrabold text-emerald-700 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-950/60 px-2.5 py-0.5 rounded-md border border-emerald-200/80 dark:border-emerald-800/70">
                            20% Kogel Drop
                        </span>
                    </div>
                    <h1 class="text-xl sm:text-2xl md:text-3xl font-black text-slate-900 dark:text-white tracking-tight">
                        Hosselen & Klussen
                    </h1>
                    <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5 max-w-xl leading-relaxed">
                        Scharrel contanten, respect en munitie bij in de hoeken van de inrichting. Je <strong>Charisma ({{ $character->getEffectiveCharisma() }})</strong> en <strong>Intelligentie ({{ $character->getEffectiveIntelligence() }})</strong> verhogen je slagingskans aanzienlijk!
                    </p>
                </div>
            </div>

            <!-- Vitals Pills -->
            <div class="grid grid-cols-2 sm:grid-cols-4 md:flex items-center gap-2 shrink-0">
                <div class="bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200/80 dark:border-emerald-800/70 rounded-xl px-3 py-1.5 text-center">
                    <span class="text-[9px] font-black uppercase text-emerald-700 dark:text-emerald-400 block">Energie</span>
                    <span class="text-xs sm:text-sm font-black text-emerald-800 dark:text-emerald-300 font-mono">
                        {{ $character->energy }}<span class="text-[10px] text-emerald-500 font-semibold">/{{ $character->max_energy }}</span>
                    </span>
                </div>
                <div class="bg-amber-50 dark:bg-amber-950/60 border border-amber-200/80 dark:border-amber-800/70 rounded-xl px-3 py-1.5 text-center">
                    <span class="text-[9px] font-black uppercase text-amber-700 dark:text-amber-400 block">Contant</span>
                    <span class="text-xs sm:text-sm font-black text-amber-800 dark:text-amber-300 font-mono">
                        €{{ $character->cash >= 1000000 ? number_format($character->cash / 1000000, 1) . 'M' : number_format($character->cash, 0, ',', '.') }}
                    </span>
                </div>
                <div class="bg-purple-50 dark:bg-purple-950/60 border border-purple-200/80 dark:border-purple-800/70 rounded-xl px-3 py-1.5 text-center">
                    <span class="text-[9px] font-black uppercase text-purple-700 dark:text-purple-400 block">Respect</span>
                    <span class="text-xs sm:text-sm font-black text-purple-800 dark:text-purple-300 font-mono">
                        {{ number_format($character->respect, 0, ',', '.') }}
                    </span>
                </div>
                <div class="bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200/80 dark:border-slate-700 rounded-xl px-3 py-1.5 text-center">
                    <span class="text-[9px] font-black uppercase text-slate-500 dark:text-slate-400 block">Kogels</span>
                    <span class="text-xs sm:text-sm font-black text-slate-900 dark:text-white font-mono">
                        {{ number_format($character->bullets ?? 0, 0, ',', '.') }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Cooldown Notification Banner -->
        <div x-show="seconds > 0" class="mt-5 p-4 rounded-xl bg-amber-50 dark:bg-amber-950/50 border border-amber-200/80 dark:border-amber-800/70 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3" style="{{ $hustleCooldown > 0 ? '' : 'display: none;' }}">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-amber-500 text-white flex items-center justify-center text-sm font-black shrink-0 shadow-xs">
                    <i class="fa-solid fa-clock"></i>
                </div>
                <div>
                    <h4 class="text-xs font-black text-amber-950 dark:text-amber-200">Hossel Cooldown Actief</h4>
                    <p class="text-[11px] text-amber-800 dark:text-amber-300">
                        Wacht nog <strong class="font-mono text-amber-950 dark:text-amber-200 font-bold" x-text="format(seconds)">{{ gmdate('i:s', $hustleCooldown) }}</strong> om opnieuw te klussen.
                    </p>
                </div>
            </div>
            <form method="POST" action="{{ route('cooldown.skip') }}" class="w-full sm:w-auto">
                @csrf
                <input type="hidden" name="action" value="hustle">
                <button type="submit" 
                        {{ $character->isImprisoned() || $character->isComatose() || ($character->stars ?? 0) < 10 ? 'disabled' : '' }}
                        class="btn-star-gold w-full sm:w-auto h-9 px-4 rounded-xl font-black text-slate-900 dark:text-white text-xs transition-all duration-150 active:scale-95 shadow-xs flex items-center justify-center gap-1.5 cursor-pointer whitespace-nowrap disabled:opacity-50 disabled:cursor-not-allowed">
                    <i class="fa-solid fa-star text-[11px]"></i>
                    <span>Omzeilen (10 Sterren)</span>
                </button>
            </form>
        </div>
    </div>

    <!-- HUSTLE OPERATION CARDS GRID -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($hustles as $h)
        @php
            $hasEnergy = $character->energy >= $h['cost'];
            $isBlocked = ! $hasEnergy || $character->isImprisoned() || $character->isComatose();
            $riskBg = match($h['risk_level'] ?? 'medium') {
                'low' => 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border-emerald-200/80 dark:border-emerald-800/70',
                'medium' => 'bg-amber-50 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 border-amber-200/80 dark:border-amber-800/70',
                'high' => 'bg-amber-100 dark:bg-amber-950/70 text-amber-900 dark:text-amber-200 border-amber-300 dark:border-amber-700',
                'extreme' => 'bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border-rose-200/80 dark:border-rose-800/70',
                default => 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border-slate-200/80 dark:border-slate-700',
            };
        @endphp
        <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 hover:border-purple-300 dark:hover:border-purple-700/80 rounded-2xl p-5 shadow-xs transition-all duration-200 flex flex-col justify-between gap-4 group">
            
            <div class="space-y-3">
                <!-- Header with Icon & Tier -->
                <div class="flex items-start justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-purple-50 dark:bg-purple-950/60 border border-purple-200/80 dark:border-purple-800/70 text-purple-600 dark:text-purple-400 flex items-center justify-center text-base shrink-0 group-hover:scale-105 transition-transform">
                            <i class="fa-solid {{ $h['icon'] ?? 'fa-handshake' }}"></i>
                        </div>
                        <div>
                            <span class="text-[10px] font-black uppercase tracking-wider text-purple-600 dark:text-purple-400 block">
                                {{ $h['tier_name'] ?? 'Klus' }}
                            </span>
                            <h3 class="font-black text-sm text-slate-900 dark:text-white leading-tight">
                                {{ $h['name'] }}
                            </h3>
                        </div>
                    </div>
                </div>

                <!-- Description -->
                <p class="text-[11px] text-slate-500 dark:text-slate-400 leading-relaxed min-h-[34px]">
                    {{ $h['description'] }}
                </p>

                <!-- Slagingskans Meter Bar -->
                <div class="space-y-1 pt-1">
                    <div class="flex items-center justify-between text-[11px]">
                        <span class="font-bold text-slate-500 dark:text-slate-400 flex items-center gap-1">
                            <i class="fa-solid fa-bullseye text-purple-600 dark:text-purple-400"></i> Slagingskans:
                        </span>
                        <span class="font-black font-mono text-purple-700 dark:text-purple-300">
                            {{ $h['effective_rate'] }}%
                            <span class="text-[9px] text-slate-400 font-normal">({{ $h['rate'] }}% + {{ $statBonus ?? 0 }}%)</span>
                        </span>
                    </div>
                    <div class="w-full h-2 rounded-full bg-slate-100 dark:bg-slate-800 overflow-hidden">
                        <div class="h-full rounded-full transition-all duration-500 {{ $h['effective_rate'] >= 75 ? 'bg-emerald-500' : ($h['effective_rate'] >= 50 ? 'bg-purple-600' : 'bg-amber-500') }}"
                             style="width: {{ $h['effective_rate'] }}%;"></div>
                    </div>
                </div>

                <!-- Reward Badges -->
                <div class="grid grid-cols-2 gap-1.5 pt-1 text-[11px]">
                    <div class="px-2.5 py-1 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200/80 dark:border-emerald-800/70 font-black text-emerald-700 dark:text-emerald-300 font-mono flex items-center gap-1.5">
                        <i class="fa-solid fa-sack-dollar text-[10px]"></i>
                        <span>€{{ number_format($h['cash_min'], 0, ',', '.') }} - €{{ number_format($h['cash_max'], 0, ',', '.') }}</span>
                    </div>
                    <div class="px-2.5 py-1 rounded-xl bg-purple-50 dark:bg-purple-950/60 border border-purple-200/80 dark:border-purple-800/70 font-black text-purple-700 dark:text-purple-300 font-mono flex items-center gap-1.5">
                        <i class="fa-solid fa-bolt text-[10px]"></i>
                        <span>+{{ $h['xp'] }} XP &bull; +{{ $h['respect'] }} Resp</span>
                    </div>
                </div>

                <!-- Risk & Requirements Chips -->
                <div class="flex items-center justify-between text-[10px] pt-1 border-t border-slate-100 dark:border-slate-800">
                    <span class="px-2 py-0.5 rounded border font-extrabold {{ $riskBg }}">
                        <i class="fa-solid fa-shield-halved text-[9px] mr-0.5"></i> {{ $h['risk_label'] }}
                    </span>
                    <span class="font-bold text-slate-400 dark:text-slate-500 flex items-center gap-1">
                        <i class="fa-solid fa-clock text-amber-500"></i> {{ gmdate('i:s', $h['cooldown']) }}
                    </span>
                </div>
            </div>

            <!-- Direct Submit Button -->
            <form action="{{ route('hustle.attempt') }}" method="POST" class="pt-2">
                @csrf
                <input type="hidden" name="hustle_id" value="{{ $h['id'] }}">
                <button type="submit" 
                        @if($isBlocked || $hustleCooldown > 0) disabled @endif
                        :disabled="seconds > 0 || {{ $isBlocked ? 'true' : 'false' }}"
                        :class="seconds > 0 || {{ $isBlocked ? 'true' : 'false' }} ? 'bg-slate-100 dark:bg-slate-800/80 text-slate-400 dark:text-slate-500 border border-slate-200/80 dark:border-slate-700/60 cursor-not-allowed shadow-none' : 'bg-purple-600 hover:bg-purple-700 dark:bg-purple-600 dark:hover:bg-purple-500 text-white cursor-pointer active:scale-95 shadow-xs'"
                        class="w-full py-2.5 px-4 rounded-xl font-black text-xs uppercase tracking-wider transition-all duration-150 flex items-center justify-center gap-2">
                    <span class="flex items-center gap-1">
                        <i class="fa-solid fa-bolt text-[10px]"></i> {{ $h['cost'] }} Energie
                    </span>
                    <span>&bull;</span>
                    <span>Hossel Nu</span>
                </button>
            </form>

        </div>
        @endforeach
    </div>

</div>
@endsection

