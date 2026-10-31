@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="bg-white rounded-2xl p-6 sm:p-8 text-slate-800 relative overflow-hidden shadow-xs border border-slate-200">
        <div class="relative z-10">
            <div class="inline-flex items-center gap-2 bg-indigo-50 text-indigo-700 text-xs font-bold px-3 py-1 rounded-full border border-indigo-200 mb-3">
                <i class="fa-solid fa-hand-scissors text-indigo-600"></i> Casino Minigame
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900">Steen, Papier, Schaar</h1>
            <p class="text-slate-500 text-xs sm:text-sm mt-1">Speel tegen het huis. Win verdubbelt je inzet!</p>
        </div>
        <div class="absolute -right-6 -bottom-6 text-slate-100 dark:text-slate-800/40 text-9xl font-black select-none pointer-events-none">
            <i class="fa-solid fa-hand-fist"></i>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-6 sm:p-8">
        <form action="{{ route('rps.play') }}" method="POST" class="space-y-6">
            @csrf

            <!-- Choice -->
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 text-center mb-3">Maak je keuze</label>
                <div class="grid grid-cols-3 gap-3">
                    <label class="cursor-pointer">
                        <input type="radio" name="choice" value="steen" class="peer sr-only" required>
                        <div class="p-4 rounded-xl border-2 border-slate-200 dark:border-slate-700 peer-checked:border-indigo-600 peer-checked:bg-indigo-50/50 dark:peer-checked:bg-indigo-950/60 text-center hover:border-indigo-300 transition shadow-xs flex flex-col items-center gap-2">
                            <span class="h-10 flex items-center justify-center text-slate-700 dark:text-slate-300"><i class="fa-solid fa-hand-back-fist text-3xl"></i></span>
                            <span class="text-xs font-extrabold text-slate-800 dark:text-white uppercase">Steen</span>
                        </div>
                    </label>

                    <label class="cursor-pointer">
                        <input type="radio" name="choice" value="papier" class="peer sr-only" required>
                        <div class="p-4 rounded-xl border-2 border-slate-200 dark:border-slate-700 peer-checked:border-indigo-600 peer-checked:bg-indigo-50/50 dark:peer-checked:bg-indigo-950/60 text-center hover:border-indigo-300 transition shadow-xs flex flex-col items-center gap-2">
                            <span class="h-10 flex items-center justify-center text-amber-600 dark:text-amber-400"><i class="fa-solid fa-hand text-3xl"></i></span>
                            <span class="text-xs font-extrabold text-slate-800 dark:text-white uppercase">Papier</span>
                        </div>
                    </label>

                    <label class="cursor-pointer">
                        <input type="radio" name="choice" value="schaar" class="peer sr-only" required>
                        <div class="p-4 rounded-xl border-2 border-slate-200 dark:border-slate-700 peer-checked:border-indigo-600 peer-checked:bg-indigo-50/50 dark:peer-checked:bg-indigo-950/60 text-center hover:border-indigo-300 transition shadow-xs flex flex-col items-center gap-2">
                            <span class="h-10 flex items-center justify-center text-purple-600 dark:text-purple-400"><i class="fa-solid fa-hand-scissors text-3xl"></i></span>
                            <span class="text-xs font-extrabold text-slate-800 dark:text-white uppercase">Schaar</span>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Inzet -->
            <div>
                <div class="flex justify-between items-center mb-2">
                    <label class="text-xs font-bold uppercase tracking-wider text-slate-500">Inzet (Euro)</label>
                    <span class="text-xs font-bold text-slate-400">Beschikbaar: <span class="text-slate-700 font-extrabold">€{{ number_format($character->cash, 0, ',', '.') }}</span></span>
                </div>
                <div class="relative">
                    <input type="number" name="bet" min="50" max="50000" value="100" required
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-extrabold text-slate-800 focus:ring-2 focus:ring-indigo-500">
                    <div class="absolute right-3 top-3 text-xs font-bold text-slate-400">€</div>
                </div>
                <div class="flex gap-2 mt-2">
                    <button type="button" onclick="document.querySelector('input[name=bet]').value = 100" class="px-2.5 py-1 text-xs font-bold rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700">100</button>
                    <button type="button" onclick="document.querySelector('input[name=bet]').value = 500" class="px-2.5 py-1 text-xs font-bold rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700">500</button>
                    <button type="button" onclick="document.querySelector('input[name=bet]').value = 1000" class="px-2.5 py-1 text-xs font-bold rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700">1.000</button>
                    <button type="button" onclick="document.querySelector('input[name=bet]').value = 5000" class="px-2.5 py-1 text-xs font-bold rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700">5.000</button>
                </div>
            </div>

            <button type="submit"
                    {{ $character->isImprisoned() || $character->cash < 50 ? 'disabled' : '' }}
                    class="w-full py-3.5 px-6 rounded-xl font-extrabold text-sm transition shadow-sm {{ $character->isImprisoned() || $character->cash < 50 ? 'bg-slate-200 dark:bg-slate-800 text-slate-500 dark:text-slate-400 cursor-not-allowed' : 'bg-indigo-600 hover:bg-indigo-700 text-white active:scale-[0.99]' }}">
                <i class="fa-solid fa-gamepad mr-2"></i> Spelen
            </button>
        </form>
    </div>
</div>
@endsection
