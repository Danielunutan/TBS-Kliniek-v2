@extends('layouts.app')

@section('content')
@php
    $cardLabels = [1 => 'A', 2 => '2', 3 => '3', 4 => '4', 5 => '5', 6 => '6', 7 => '7',
                   8 => '8', 9 => '9', 10 => '10', 11 => 'J', 12 => 'Q', 13 => 'K'];
    $displayCard = $cardLabels[$currentCard] ?? $currentCard;
@endphp

<div class="space-y-6">
    <div class="bg-white rounded-2xl p-6 sm:p-8 text-slate-800 relative overflow-hidden shadow-xs border border-slate-200">
        <div class="relative z-10">
            <div class="inline-flex items-center gap-2 bg-amber-50 text-amber-800 text-xs font-bold px-3 py-1 rounded-full border border-amber-200 mb-3">
                <i class="fa-solid fa-up-down text-amber-600"></i> Casino Kaartenspel
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900">Hoger of Lager</h1>
            <p class="text-slate-500 text-xs sm:text-sm mt-1">Raad of de volgende kaart hoger of lager is dan de huidige kaart (Aas is 1, Koning is 13).</p>
        </div>
        <div class="absolute -right-6 -bottom-6 text-slate-100 text-9xl font-black select-none pointer-events-none">
            <i class="fa-solid fa-dice"></i>
        </div>
    </div>

    <!-- Active Card Display -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-6 sm:p-8 text-center space-y-6">
        <div>
            <div class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Huidige Kaart</div>
            <div class="inline-flex items-center justify-center w-24 h-36 bg-gradient-to-br from-white to-slate-100 rounded-2xl border-2 border-slate-300 shadow-lg text-4xl font-black {{ in_array($currentCard, [1, 7, 13]) ? 'text-rose-600' : 'text-slate-900' }}">
                {{ $displayCard }}
            </div>
        </div>

        <form action="{{ route('highlow.play') }}" method="POST" class="space-y-6 max-w-md mx-auto">
            @csrf

            <!-- Bet selection -->
            <div>
                <div class="flex justify-between items-center mb-2">
                    <label class="text-xs font-bold uppercase tracking-wider text-slate-500">Inzet (Euro)</label>
                    <span class="text-xs font-bold text-slate-400">Cash: <span class="text-slate-700 font-extrabold">€{{ number_format($character->cash, 0, ',', '.') }}</span></span>
                </div>
                <div class="relative">
                    <input type="number" name="bet" min="50" max="50000" value="100" required
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-extrabold text-slate-800 focus:ring-2 focus:ring-amber-500">
                    <div class="absolute right-3 top-3 text-xs font-bold text-slate-400">€</div>
                </div>
                <div class="flex gap-2 mt-2">
                    <button type="button" onclick="document.querySelector('input[name=bet]').value = 100" class="px-2.5 py-1 text-xs font-bold rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700">100</button>
                    <button type="button" onclick="document.querySelector('input[name=bet]').value = 500" class="px-2.5 py-1 text-xs font-bold rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700">500</button>
                    <button type="button" onclick="document.querySelector('input[name=bet]').value = 1000" class="px-2.5 py-1 text-xs font-bold rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700">1.000</button>
                    <button type="button" onclick="document.querySelector('input[name=bet]').value = 5000" class="px-2.5 py-1 text-xs font-bold rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700">5.000</button>
                </div>
            </div>

            <!-- Choice -->
            <div class="grid grid-cols-2 gap-3">
                <button type="submit" name="choice" value="lager"
                        {{ $character->isImprisoned() || $character->cash < 50 ? 'disabled' : '' }}
                        class="p-4 rounded-xl border-2 border-rose-300 hover:border-rose-500 bg-rose-50/50 hover:bg-rose-100/50 transition font-extrabold text-sm text-rose-700 flex flex-col items-center gap-1 shadow-xs disabled:opacity-50">
                    <i class="fa-solid fa-arrow-down text-2xl"></i>
                    <span>LAGER</span>
                </button>
                <button type="submit" name="choice" value="hoger"
                        {{ $character->isImprisoned() || $character->cash < 50 ? 'disabled' : '' }}
                        class="p-4 rounded-xl border-2 border-emerald-300 hover:border-emerald-500 bg-emerald-50/50 hover:bg-emerald-100/50 transition font-extrabold text-sm text-emerald-700 flex flex-col items-center gap-1 shadow-xs disabled:opacity-50">
                    <i class="fa-solid fa-arrow-up text-2xl"></i>
                    <span>HOGER</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
