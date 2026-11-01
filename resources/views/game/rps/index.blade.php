@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="bg-white dark:bg-slate-900 rounded-2xl p-6 sm:p-8 text-slate-800 dark:text-slate-100 relative overflow-hidden shadow-xs border border-slate-200/90 dark:border-slate-800">
        <div class="relative z-10">
            <div class="inline-flex items-center gap-2 bg-purple-50 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 text-xs font-bold px-3 py-1 rounded-full border border-purple-200/80 dark:border-purple-800/70 mb-3">
                <i class="fa-solid fa-hand-scissors text-purple-600 dark:text-purple-400"></i> Casino Minigame
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white">Steen, Papier, Schaar</h1>
            <p class="text-slate-500 dark:text-slate-400 text-xs sm:text-sm mt-1">Speel tegen het huis. Win verdubbelt je inzet!</p>
        </div>
        <div class="absolute -right-6 -bottom-6 text-slate-100 dark:text-slate-800/30 text-9xl font-black select-none pointer-events-none">
            <i class="fa-solid fa-hand-fist"></i>
        </div>
    </div>

    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/90 dark:border-slate-800 shadow-xs p-6 sm:p-8">
        <form action="{{ route('rps.play') }}" method="POST" class="space-y-6">
            @csrf

            <!-- Choice -->
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 text-center mb-3">Maak je keuze</label>
                <div class="grid grid-cols-3 gap-3">
                    <label class="cursor-pointer">
                        <input type="radio" name="choice" value="steen" class="peer sr-only" required checked>
                        <div class="p-4 rounded-xl bg-slate-50/90 dark:bg-slate-800/60 border-2 border-slate-200/80 dark:border-slate-700/70 peer-checked:border-purple-600 dark:peer-checked:border-purple-500 peer-checked:bg-purple-50/70 dark:peer-checked:bg-purple-950/60 text-center hover:border-purple-300 dark:hover:border-purple-700/80 transition-all duration-150 shadow-xs flex flex-col items-center gap-2">
                            <span class="h-10 flex items-center justify-center text-slate-700 dark:text-slate-300"><i class="fa-solid fa-hand-back-fist text-3xl"></i></span>
                            <span class="text-xs font-extrabold text-slate-800 dark:text-white uppercase">Steen</span>
                        </div>
                    </label>

                    <label class="cursor-pointer">
                        <input type="radio" name="choice" value="papier" class="peer sr-only" required>
                        <div class="p-4 rounded-xl bg-slate-50/90 dark:bg-slate-800/60 border-2 border-slate-200/80 dark:border-slate-700/70 peer-checked:border-purple-600 dark:peer-checked:border-purple-500 peer-checked:bg-purple-50/70 dark:peer-checked:bg-purple-950/60 text-center hover:border-purple-300 dark:hover:border-purple-700/80 transition-all duration-150 shadow-xs flex flex-col items-center gap-2">
                            <span class="h-10 flex items-center justify-center text-amber-600 dark:text-amber-400"><i class="fa-solid fa-hand text-3xl"></i></span>
                            <span class="text-xs font-extrabold text-slate-800 dark:text-white uppercase">Papier</span>
                        </div>
                    </label>

                    <label class="cursor-pointer">
                        <input type="radio" name="choice" value="schaar" class="peer sr-only" required>
                        <div class="p-4 rounded-xl bg-slate-50/90 dark:bg-slate-800/60 border-2 border-slate-200/80 dark:border-slate-700/70 peer-checked:border-purple-600 dark:peer-checked:border-purple-500 peer-checked:bg-purple-50/70 dark:peer-checked:bg-purple-950/60 text-center hover:border-purple-300 dark:hover:border-purple-700/80 transition-all duration-150 shadow-xs flex flex-col items-center gap-2">
                            <span class="h-10 flex items-center justify-center text-purple-600 dark:text-purple-400"><i class="fa-solid fa-hand-scissors text-3xl"></i></span>
                            <span class="text-xs font-extrabold text-slate-800 dark:text-white uppercase">Schaar</span>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Inzet -->
            <div>
                <div class="flex justify-between items-center mb-2">
                    <label class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Inzet (Euro)</label>
                    <span class="text-xs font-bold text-slate-400 dark:text-slate-500">Beschikbaar: <span class="text-slate-700 dark:text-slate-200 font-extrabold">€{{ number_format($character->cash, 0, ',', '.') }}</span></span>
                </div>
                <div class="relative">
                    <input type="number" name="bet" min="50" max="50000" value="100" required
                           class="w-full bg-slate-50 dark:bg-slate-800/90 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-3 text-base sm:text-sm font-extrabold text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:border-purple-500 dark:focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition">
                    <div class="absolute right-3 top-3 text-xs font-bold text-slate-400 dark:text-slate-500">€</div>
                </div>
                <div class="flex gap-2 mt-2">
                    <button type="button" onclick="document.querySelector('input[name=bet]').value = 100" class="px-2.5 py-1 text-xs font-bold rounded-lg bg-slate-100 hover:bg-purple-50 dark:bg-slate-800 dark:hover:bg-purple-950/50 text-slate-700 dark:text-slate-200 hover:text-purple-700 dark:hover:text-purple-300 border border-slate-200/80 dark:border-slate-700 transition-all duration-150 active:scale-95 cursor-pointer">100</button>
                    <button type="button" onclick="document.querySelector('input[name=bet]').value = 500" class="px-2.5 py-1 text-xs font-bold rounded-lg bg-slate-100 hover:bg-purple-50 dark:bg-slate-800 dark:hover:bg-purple-950/50 text-slate-700 dark:text-slate-200 hover:text-purple-700 dark:hover:text-purple-300 border border-slate-200/80 dark:border-slate-700 transition-all duration-150 active:scale-95 cursor-pointer">500</button>
                    <button type="button" onclick="document.querySelector('input[name=bet]').value = 1000" class="px-2.5 py-1 text-xs font-bold rounded-lg bg-slate-100 hover:bg-purple-50 dark:bg-slate-800 dark:hover:bg-purple-950/50 text-slate-700 dark:text-slate-200 hover:text-purple-700 dark:hover:text-purple-300 border border-slate-200/80 dark:border-slate-700 transition-all duration-150 active:scale-95 cursor-pointer">1.000</button>
                    <button type="button" onclick="document.querySelector('input[name=bet]').value = 5000" class="px-2.5 py-1 text-xs font-bold rounded-lg bg-slate-100 hover:bg-purple-50 dark:bg-slate-800 dark:hover:bg-purple-950/50 text-slate-700 dark:text-slate-200 hover:text-purple-700 dark:hover:text-purple-300 border border-slate-200/80 dark:border-slate-700 transition-all duration-150 active:scale-95 cursor-pointer">5.000</button>
                </div>
            </div>

            <button type="submit"
                    {{ $character->isImprisoned() || $character->isComatose() || $character->cash < 50 ? 'disabled' : '' }}
                    class="w-full py-3.5 px-6 rounded-xl font-black text-sm transition-all duration-150 shadow-xs flex items-center justify-center gap-2 {{ $character->isImprisoned() || $character->isComatose() || $character->cash < 50 ? 'bg-slate-100 dark:bg-slate-800/80 text-slate-400 dark:text-slate-500 border border-slate-200/80 dark:border-slate-700/60 cursor-not-allowed' : 'bg-purple-600 hover:bg-purple-700 dark:bg-purple-600 dark:hover:bg-purple-500 text-white active:scale-95 cursor-pointer' }}">
                <i class="fa-solid fa-gamepad mr-2"></i> Spelen
            </button>
        </form>
    </div>
</div>
@endsection
