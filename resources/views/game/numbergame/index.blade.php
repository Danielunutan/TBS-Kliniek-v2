@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="bg-gradient-to-r from-slate-900 to-emerald-950 rounded-2xl p-6 sm:p-8 text-white relative overflow-hidden">
        <div class="relative z-10">
            <div class="inline-flex items-center gap-2 bg-emerald-500/20 text-emerald-300 text-xs font-bold px-3 py-1 rounded-full border border-emerald-400/20 mb-3">
                <i class="fa-solid fa-dice-six"></i> Casino Nummer Roulette
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">Het Nummerspel</h1>
            <p class="text-slate-300 text-xs sm:text-sm mt-1">Kies een getal tussen 1 en 20. Exact raak = 15x Jackpot! Binnen 2 getallen = 2x troostprijs!</p>
        </div>
        <div class="absolute -right-6 -bottom-6 text-white/5 text-9xl font-black select-none pointer-events-none">
            <i class="fa-solid fa-arrow-down-1-9"></i>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-6 sm:p-8">
        <form action="{{ route('numbergame.play') }}" method="POST" class="space-y-6">
            @csrf

            <!-- Number Picker -->
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 text-center mb-3">Kies je geluksgetal (1 t/m 20)</label>
                <div class="grid grid-cols-5 sm:grid-cols-10 gap-2">
                    @for($i = 1; $i <= 20; $i++)
                    <label class="cursor-pointer">
                        <input type="radio" name="number" value="{{ $i }}" class="peer sr-only" required {{ $i === 7 ? 'checked' : '' }}>
                        <div class="h-10 rounded-xl border-2 border-slate-200 peer-checked:border-emerald-600 peer-checked:bg-emerald-600 peer-checked:text-white font-extrabold text-sm flex items-center justify-center hover:border-emerald-300 transition text-slate-700 shadow-xs">
                            {{ $i }}
                        </div>
                    </label>
                    @endfor
                </div>
            </div>

            <!-- Inzet -->
            <div>
                <div class="flex justify-between items-center mb-2">
                    <label class="text-xs font-bold uppercase tracking-wider text-slate-500">Inzet (KC)</label>
                    <span class="text-xs font-bold text-slate-400">Cash: <span class="text-slate-700 font-extrabold">{{ number_format($character->cash, 0, ',', '.') }} KC</span></span>
                </div>
                <div class="relative">
                    <input type="number" name="bet" min="50" max="50000" value="100" required
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-extrabold text-slate-800 focus:ring-2 focus:ring-emerald-500">
                    <div class="absolute right-3 top-3 text-xs font-bold text-slate-400">KC</div>
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
                    class="w-full py-3.5 px-6 rounded-xl font-extrabold text-sm text-white transition shadow-sm {{ $character->isImprisoned() || $character->cash < 50 ? 'bg-slate-300 cursor-not-allowed' : 'bg-emerald-600 hover:bg-emerald-700 active:scale-[0.99]' }}">
                <i class="fa-solid fa-dice mr-2"></i> Draaien & Inzetten
            </button>
        </form>
    </div>
</div>
@endsection
