@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="bg-white dark:bg-slate-900 rounded-2xl p-6 sm:p-8 text-slate-800 dark:text-slate-100 relative overflow-hidden shadow-xs border border-slate-200/90 dark:border-slate-800">
        <div class="relative z-10">
            <div class="inline-flex items-center gap-2 bg-emerald-50 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 text-xs font-bold px-3 py-1 rounded-full border border-emerald-200/80 dark:border-emerald-800/70 mb-3">
                <i class="fa-solid fa-dice-six text-emerald-600 dark:text-emerald-400"></i> Casino Nummer Roulette
            </div>
            <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-slate-900 dark:text-white">Het Nummerspel</h1>
            <p class="text-slate-500 dark:text-slate-400 text-xs sm:text-sm mt-1">Kies een getal tussen 1 en 20. Exact raak = 15x Jackpot! 1 getal ernaast = 2x troostprijs!</p>
        </div>
        <div class="absolute -right-6 -bottom-6 text-slate-100 dark:text-slate-800/40 text-9xl font-black select-none pointer-events-none">
            <i class="fa-solid fa-arrow-down-1-9"></i>
        </div>
    </div>

    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/90 dark:border-slate-800 shadow-xs p-6 sm:p-8">
        <form action="{{ route('numbergame.play') }}" method="POST" class="space-y-6">
            @csrf

            <!-- Number Picker -->
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 text-center mb-3">Kies je geluksgetal (1 t/m 20)</label>
                <div class="grid grid-cols-5 sm:grid-cols-10 gap-2">
                    @for($i = 1; $i <= 20; $i++)
                    <label class="cursor-pointer">
                        <input type="radio" name="number" value="{{ $i }}" class="peer sr-only" required {{ $i === 7 ? 'checked' : '' }}>
                        <div class="h-10 rounded-xl border-2 border-slate-200 dark:border-slate-700 bg-slate-50/90 dark:bg-slate-800/60 peer-checked:border-emerald-600 peer-checked:bg-emerald-600 peer-checked:text-white font-extrabold text-sm flex items-center justify-center hover:border-emerald-300 dark:hover:border-emerald-500 transition text-slate-700 dark:text-slate-200 shadow-xs">
                            {{ $i }}
                        </div>
                    </label>
                    @endfor
                </div>
            </div>

            <!-- Inzet -->
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
                    <button type="button" onclick="document.querySelector('input[name=bet]').value = 100" class="py-2 text-xs font-bold rounded-xl bg-slate-100 hover:bg-purple-50 dark:bg-slate-800 dark:hover:bg-purple-950/50 text-slate-700 dark:text-slate-200 hover:text-purple-700 dark:hover:text-purple-300 border border-slate-200/80 dark:border-slate-700 transition-all duration-150 active:scale-95 cursor-pointer">€100</button>
                    <button type="button" onclick="document.querySelector('input[name=bet]').value = 500" class="py-2 text-xs font-bold rounded-xl bg-slate-100 hover:bg-purple-50 dark:bg-slate-800 dark:hover:bg-purple-950/50 text-slate-700 dark:text-slate-200 hover:text-purple-700 dark:hover:text-purple-300 border border-slate-200/80 dark:border-slate-700 transition-all duration-150 active:scale-95 cursor-pointer">€500</button>
                    <button type="button" onclick="document.querySelector('input[name=bet]').value = 1000" class="py-2 text-xs font-bold rounded-xl bg-slate-100 hover:bg-purple-50 dark:bg-slate-800 dark:hover:bg-purple-950/50 text-slate-700 dark:text-slate-200 hover:text-purple-700 dark:hover:text-purple-300 border border-slate-200/80 dark:border-slate-700 transition-all duration-150 active:scale-95 cursor-pointer">€1.000</button>
                    <button type="button" onclick="document.querySelector('input[name=bet]').value = 5000" class="py-2 text-xs font-bold rounded-xl bg-slate-100 hover:bg-purple-50 dark:bg-slate-800 dark:hover:bg-purple-950/50 text-slate-700 dark:text-slate-200 hover:text-purple-700 dark:hover:text-purple-300 border border-slate-200/80 dark:border-slate-700 transition-all duration-150 active:scale-95 cursor-pointer">€5.000</button>
                </div>
            </div>

            <button type="submit"
                    {{ $character->isImprisoned() || $character->isComatose() || $character->cash < 50 ? 'disabled' : '' }}
                    class="w-full py-3.5 px-6 rounded-xl font-black text-sm transition-all duration-150 shadow-xs cursor-pointer active:scale-95 {{ $character->isImprisoned() || $character->isComatose() || $character->cash < 50 ? 'bg-slate-100 dark:bg-slate-800/80 text-slate-400 dark:text-slate-500 border border-slate-200/80 dark:border-slate-700/60 cursor-not-allowed active:scale-100' : 'bg-emerald-600 hover:bg-emerald-700 dark:bg-emerald-600 dark:hover:bg-emerald-500 text-white' }}">
                <i class="fa-solid fa-dice mr-2"></i> Draaien & Inzetten
            </button>
        </form>
    </div>
</div>
@endsection
