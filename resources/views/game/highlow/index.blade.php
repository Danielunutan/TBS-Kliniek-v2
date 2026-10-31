@extends('layouts.app')

@section('content')
@php
    $cardLabels = [1 => 'A', 2 => '2', 3 => '3', 4 => '4', 5 => '5', 6 => '6', 7 => '7',
                   8 => '8', 9 => '9', 10 => '10', 11 => 'J', 12 => 'Q', 13 => 'K'];
    $displayCard = $cardLabels[$currentCard] ?? $currentCard;
@endphp

<div class="space-y-6">
    <div class="bg-white dark:bg-slate-900 rounded-2xl p-6 sm:p-8 text-slate-800 dark:text-slate-100 relative overflow-hidden shadow-xs border border-slate-200/90 dark:border-slate-800">
        <div class="relative z-10">
            <div class="inline-flex items-center gap-2 bg-amber-50 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 text-xs font-bold px-3 py-1 rounded-full border border-amber-200/80 dark:border-amber-800/70 mb-3">
                <i class="fa-solid fa-up-down text-amber-600 dark:text-amber-400"></i> Casino Kaartenspel
            </div>
            <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-slate-900 dark:text-white">Hoger of Lager</h1>
            <p class="text-slate-500 dark:text-slate-400 text-xs sm:text-sm mt-1">Raad of de volgende kaart hoger of lager is dan de huidige kaart (Aas is 1, Koning is 13).</p>
        </div>
        <div class="absolute -right-6 -bottom-6 text-slate-100 dark:text-slate-800/40 text-9xl font-black select-none pointer-events-none">
            <i class="fa-solid fa-dice"></i>
        </div>
    </div>

    <!-- Active Card Display -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/90 dark:border-slate-800 shadow-xs p-6 sm:p-8 text-center space-y-6">
        <div>
            <div class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-2">Huidige Kaart</div>
            <div class="inline-flex items-center justify-center w-24 h-36 bg-gradient-to-br from-white to-slate-100 dark:from-slate-800 dark:to-slate-900 rounded-2xl border-2 border-slate-300 dark:border-slate-700 shadow-lg text-4xl font-black {{ in_array($currentCard, [1, 7, 13]) ? 'text-rose-600 dark:text-rose-400' : 'text-slate-900 dark:text-white' }}">
                {{ $displayCard }}
            </div>
        </div>

        <form action="{{ route('highlow.play') }}" method="POST" class="space-y-6 max-w-md mx-auto">
            @csrf

            <!-- Bet selection -->
            <div>
                <div class="flex justify-between items-center mb-2">
                    <label class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Inzet (Euro)</label>
                    <span class="text-xs font-bold text-slate-400 dark:text-slate-500">Cash: <span class="text-slate-700 dark:text-slate-200 font-extrabold">€{{ number_format($character->cash, 0, ',', '.') }}</span></span>
                </div>
                <div class="relative">
                    <input type="number" name="bet" min="50" max="50000" value="100" required
                           class="w-full bg-slate-50 dark:bg-slate-800/90 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-3 text-sm font-extrabold text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:border-purple-500 dark:focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition">
                    <div class="absolute right-3 top-3 text-xs font-bold text-slate-400 dark:text-slate-500">€</div>
                </div>
                <div class="grid grid-cols-4 gap-2 mt-2">
                    <button type="button" onclick="document.querySelector('input[name=bet]').value = 100" class="py-2 px-2.5 text-xs font-bold rounded-xl bg-slate-100 hover:bg-purple-50 dark:bg-slate-800 dark:hover:bg-purple-950/50 text-slate-700 dark:text-slate-200 hover:text-purple-700 dark:hover:text-purple-300 border border-slate-200/80 dark:border-slate-700 transition-all duration-150 active:scale-95 cursor-pointer">€100</button>
                    <button type="button" onclick="document.querySelector('input[name=bet]').value = 500" class="py-2 px-2.5 text-xs font-bold rounded-xl bg-slate-100 hover:bg-purple-50 dark:bg-slate-800 dark:hover:bg-purple-950/50 text-slate-700 dark:text-slate-200 hover:text-purple-700 dark:hover:text-purple-300 border border-slate-200/80 dark:border-slate-700 transition-all duration-150 active:scale-95 cursor-pointer">€500</button>
                    <button type="button" onclick="document.querySelector('input[name=bet]').value = 1000" class="py-2 px-2.5 text-xs font-bold rounded-xl bg-slate-100 hover:bg-purple-50 dark:bg-slate-800 dark:hover:bg-purple-950/50 text-slate-700 dark:text-slate-200 hover:text-purple-700 dark:hover:text-purple-300 border border-slate-200/80 dark:border-slate-700 transition-all duration-150 active:scale-95 cursor-pointer">€1.000</button>
                    <button type="button" onclick="document.querySelector('input[name=bet]').value = 5000" class="py-2 px-2.5 text-xs font-bold rounded-xl bg-slate-100 hover:bg-purple-50 dark:bg-slate-800 dark:hover:bg-purple-950/50 text-slate-700 dark:text-slate-200 hover:text-purple-700 dark:hover:text-purple-300 border border-slate-200/80 dark:border-slate-700 transition-all duration-150 active:scale-95 cursor-pointer">€5.000</button>
                </div>
            </div>

@php
    $higherCount = 13 - $currentCard;
    $lowerCount = $currentCard - 1;
    $higherMult = round(max(1.15, min(4.5, (12 / max(1, $higherCount)) * 0.95)), 2);
    $lowerMult = round(max(1.15, min(4.5, (12 / max(1, $lowerCount)) * 0.95)), 2);
@endphp

            <!-- Choice Buttons with Multipliers -->
            <div class="grid grid-cols-2 gap-3">
                <button type="submit" name="choice" value="lager"
                        {{ $character->isImprisoned() || $character->isComatose() || $character->cash < 50 ? 'disabled' : '' }}
                        class="p-4 rounded-xl border-2 border-rose-300 dark:border-rose-800 hover:border-rose-500 dark:hover:border-rose-600 bg-rose-50/50 dark:bg-rose-950/40 hover:bg-rose-100/60 dark:hover:bg-rose-900/60 transition-all duration-150 font-black text-sm text-rose-700 dark:text-rose-300 flex flex-col items-center gap-1 shadow-xs disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer active:scale-95">
                    <i class="fa-solid fa-arrow-down text-2xl"></i>
                    <span>LAGER</span>
                    <span class="text-[11px] font-black text-rose-600 dark:text-rose-300 bg-white dark:bg-slate-800 px-2 py-0.5 rounded-full border border-rose-200 dark:border-rose-700">
                        {{ $lowerMult }}x uitkering
                    </span>
                </button>
                <button type="submit" name="choice" value="hoger"
                        {{ $character->isImprisoned() || $character->isComatose() || $character->cash < 50 ? 'disabled' : '' }}
                        class="p-4 rounded-xl border-2 border-emerald-300 dark:border-emerald-800 hover:border-emerald-500 dark:hover:border-emerald-600 bg-emerald-50/50 dark:bg-emerald-950/40 hover:bg-emerald-100/60 dark:hover:bg-emerald-900/60 transition-all duration-150 font-black text-sm text-emerald-700 dark:text-emerald-300 flex flex-col items-center gap-1 shadow-xs disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer active:scale-95">
                    <i class="fa-solid fa-arrow-up text-2xl"></i>
                    <span>HOGER</span>
                    <span class="text-[11px] font-black text-emerald-600 dark:text-emerald-300 bg-white dark:bg-slate-800 px-2 py-0.5 rounded-full border border-emerald-200 dark:border-emerald-700">
                        {{ $higherMult }}x uitkering
                    </span>
                </button>
            </div>

            <!-- Fair Play & Cooldown Info -->
            <div class="bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70 rounded-xl p-3 text-[11px] text-slate-500 dark:text-slate-400 flex items-center justify-between gap-3 text-left">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-shield-halved text-amber-500 text-sm"></i>
                    <span><strong>Eerlijk spel:</strong> Multiplier past zich aan op risico. Bij gelijkspel wint de bank.</span>
                </div>
                <span class="text-[10px] font-bold text-slate-500 dark:text-slate-400 bg-white dark:bg-slate-900 px-2 py-0.5 rounded border border-slate-200/80 dark:border-slate-700/80 whitespace-nowrap">
                    <i class="fa-solid fa-stopwatch text-slate-400"></i> 6s cooldown
                </span>
            </div>
        </form>
    </div>
</div>
@endsection
