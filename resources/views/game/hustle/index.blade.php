@extends('layouts.app')

@section('title', 'Hosselen & Klussen - TBS Kliniek')

@section('content')
@php
    $hustleCooldown = $character->cooldownRemaining('hustle');
    $hustleSkipCost = app(\App\Services\CooldownService::class)->getSkipCost('hustle');
@endphp
<div class="space-y-4 sm:space-y-6" x-data="{
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

    <!-- Mobile-First App Header & Vitals Bar -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xs border border-slate-200/90 dark:border-slate-800 p-4 sm:p-6 overflow-hidden">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 sm:w-12 sm:h-12 rounded-xl bg-purple-50 dark:bg-purple-950/70 border border-purple-200 dark:border-purple-800 text-purple-600 dark:text-purple-400 flex items-center justify-center text-lg sm:text-xl shrink-0 shadow-2xs">
                    <i class="fa-solid fa-comments-dollar"></i>
                </div>
                <div>
                    <div class="flex items-center gap-1.5">
                        <span class="text-[9px] font-black uppercase tracking-wider text-purple-700 dark:text-purple-300 bg-purple-50 dark:bg-purple-950/80 px-2 py-0.5 rounded border border-purple-200 dark:border-purple-800">TBS Hossels</span>
                        <span class="text-[9px] font-extrabold text-amber-700 dark:text-amber-400 bg-amber-50 dark:bg-amber-950/80 px-2 py-0.5 rounded border border-amber-200 dark:border-amber-800 inline-flex items-center gap-1">
                            <i class="fa-solid fa-bomb text-[8px]"></i> 15% Kogel Drop
                        </span>
                    </div>
                    <h1 class="text-lg sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight mt-0.5">Hosselen & Klussen</h1>
                    <p class="text-[11px] sm:text-xs text-slate-500 dark:text-slate-400 mt-0.5">Scharrel bij tussen de corridors voor contanten, respect en zeldzame kogels.</p>
                </div>
            </div>

            <!-- Mobile Touch Vitals Pills -->
            <div class="grid grid-cols-4 sm:flex items-center gap-1.5 sm:gap-2 pt-2 sm:pt-0 border-t sm:border-t-0 border-slate-100 dark:border-slate-800 shrink-0">
                <div class="bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200 dark:border-emerald-800/80 rounded-xl px-2 sm:px-3 py-1.5 text-center">
                    <span class="text-[9px] font-extrabold uppercase text-emerald-800 dark:text-emerald-400 block leading-tight">Energie</span>
                    <span class="text-xs sm:text-sm font-black text-emerald-700 dark:text-emerald-300">{{ $character->energy }}<span class="text-[9px] text-emerald-500 font-semibold">/{{ $character->max_energy }}</span></span>
                </div>
                <div class="bg-amber-50 dark:bg-amber-950/60 border border-amber-200 dark:border-amber-800/80 rounded-xl px-2 sm:px-3 py-1.5 text-center">
                    <span class="text-[9px] font-extrabold uppercase text-amber-800 dark:text-amber-400 block leading-tight">Contant</span>
                    <span class="text-xs sm:text-sm font-black text-amber-700 dark:text-amber-300 font-mono">€{{ $character->cash >= 1000000 ? number_format($character->cash / 1000000, 1) . 'M' : number_format($character->cash, 0, ',', '.') }}</span>
                </div>
                <div class="bg-purple-50 dark:bg-purple-950/60 border border-purple-200 dark:border-purple-800/80 rounded-xl px-2 sm:px-3 py-1.5 text-center">
                    <span class="text-[9px] font-extrabold uppercase text-purple-800 dark:text-purple-400 block leading-tight">Respect</span>
                    <span class="text-xs sm:text-sm font-black text-purple-700 dark:text-purple-300">{{ number_format($character->respect, 0, ',', '.') }}</span>
                </div>
                <div class="bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-2 sm:px-3 py-1.5 text-center">
                    <span class="text-[9px] font-extrabold uppercase text-slate-600 dark:text-slate-400 block leading-tight">Kogels</span>
                    <span class="text-xs sm:text-sm font-black text-slate-900 dark:text-white font-mono">{{ number_format($character->bullets ?? 0, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        <!-- Cooldown Notification Banner -->
        <div x-show="seconds > 0" class="mt-4 p-3 sm:p-4 rounded-xl bg-amber-500/10 dark:bg-amber-950/40 border border-amber-300 dark:border-amber-700 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3" style="{{ $hustleCooldown > 0 ? '' : 'display: none;' }}">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-amber-500 text-white flex items-center justify-center text-sm font-black shrink-0 shadow-xs">
                    <i class="fa-solid fa-clock"></i>
                </div>
                <div>
                    <h4 class="text-xs font-black text-amber-950 dark:text-amber-200">Hossel Cooldown Actief</h4>
                    <p class="text-[11px] text-amber-800 dark:text-amber-300">Wacht nog <strong class="font-mono text-amber-950 dark:text-amber-200 font-bold" x-text="format(seconds)">{{ gmdate('i:s', $hustleCooldown) }}</strong> om opnieuw te klussen.</p>
                </div>
            </div>
            <div class="w-full sm:w-auto shrink-0">
                <button type="button" onclick="window.tbsOpenSponsorAd('cooldown')" class="w-full sm:w-auto h-9 px-4 rounded-xl bg-purple-600 hover:bg-purple-700 active:scale-95 text-white text-xs font-black transition shadow-xs flex items-center justify-center gap-1.5 cursor-pointer whitespace-nowrap">
                    <i class="fa-solid fa-tv text-[11px]"></i>
                    <span>Kijk Ad (Reset Cooldown)</span>
                </button>
            </div>
        </div>
    </div>

    <!-- MAIN HUSTLE SELECTION FORM -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xs border border-slate-200/90 dark:border-slate-800 p-4 sm:p-6">
        <form action="{{ route('hustle.attempt') }}" method="POST">
            @csrf
            
            <div class="space-y-2.5 sm:space-y-3 mb-5">
                @foreach($hustles as $h)
                <label class="block p-3.5 sm:p-4 border rounded-xl cursor-pointer transition group border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/90 hover:bg-slate-50 dark:hover:bg-slate-800/80 has-[:checked]:border-purple-600 dark:has-[:checked]:border-purple-500 has-[:checked]:bg-purple-50/70 dark:has-[:checked]:bg-purple-950/50 has-[:checked]:ring-2 has-[:checked]:ring-purple-500/20"
                       :class="seconds > 0 ? 'opacity-60 cursor-not-allowed' : ''">
                    <div class="flex items-start gap-3">
                        <input type="radio" name="hustle_id" value="{{ $h['id'] }}" 
                               class="w-4 h-4 mt-1 text-purple-600 focus:ring-purple-500 border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 shrink-0" 
                               {{ $loop->first ? 'checked' : '' }} 
                               :disabled="seconds > 0" 
                               {{ $hustleCooldown > 0 ? 'disabled' : '' }} required>

                        <div class="flex-1 min-w-0">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1 mb-1">
                                <span class="font-extrabold text-xs sm:text-sm text-slate-900 dark:text-white group-hover:text-purple-600 dark:group-hover:text-purple-400 transition truncate">
                                    {{ $h['name'] }}
                                </span>
                                <div class="flex flex-wrap items-center gap-1 sm:gap-1.5 shrink-0">
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">{{ $h['cost'] }}⚡ Energie</span>
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 font-mono">€{{ $h['cash_min'] }}-€{{ $h['cash_max'] }}</span>
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-purple-50 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300">{{ $h['rate'] }}% kans</span>
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300" title="Cooldown wachttijd">⏳ {{ gmdate('i:s', $h['cooldown']) }}</span>
                                </div>
                            </div>
                            <p class="text-[11px] sm:text-xs text-slate-500 dark:text-slate-400 leading-snug">
                                {{ $h['description'] }}
                            </p>
                        </div>
                    </div>
                </label>
                @endforeach
            </div>

            <!-- Sticky / Action Bottom Bar for Mobile Ergonomics -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-4 border-t border-slate-100 dark:border-slate-800">
                <span class="text-[11px] font-bold text-slate-400 dark:text-slate-500 inline-flex items-center gap-1.5">
                    <i class="fa-solid fa-stopwatch text-amber-500"></i> Cooldown wachttijd varieert van 1 tot 6 minuten.
                </span>
                <button type="submit" 
                        :disabled="seconds > 0"
                        :class="seconds > 0 ? 'opacity-40 cursor-not-allowed' : 'cursor-pointer active:scale-95 shadow-md'"
                        {{ $hustleCooldown > 0 ? 'disabled' : '' }}
                        class="w-full sm:w-auto bg-purple-600 hover:bg-purple-700 disabled:opacity-40 disabled:cursor-not-allowed text-white font-black text-xs uppercase tracking-wider py-3.5 px-8 rounded-xl transition flex items-center justify-center gap-2">
                    <span>Actie Uitvoeren</span>
                    <i class="fa-solid fa-arrow-right text-[11px]"></i>
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
