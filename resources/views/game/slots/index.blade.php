@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="bg-gradient-to-r from-slate-900 to-amber-950 rounded-2xl p-6 sm:p-8 text-white relative overflow-hidden">
        <div class="relative z-10">
            <div class="inline-flex items-center gap-2 bg-amber-500/20 text-amber-300 text-xs font-bold px-3 py-1 rounded-full border border-amber-400/20 mb-3">
                <i class="fa-solid fa-gem"></i> Gokkasten & Slots
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">Fruitautomaat</h1>
            <p class="text-slate-300 text-xs sm:text-sm mt-1">Draai aan de rollen! 3 dezelfde symbolen leveren tot 50x jackpot op!</p>
        </div>
        <div class="absolute -right-6 -bottom-6 text-white/5 text-9xl font-black select-none pointer-events-none">
            <i class="fa-solid fa-coins"></i>
        </div>
    </div>

    <!-- Machine Visual -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-6 sm:p-8 space-y-6 text-center">
        <!-- Reel Window -->
        <div class="bg-gradient-to-b from-slate-900 to-slate-800 p-6 rounded-2xl border-4 border-amber-400/30 shadow-inner max-w-sm mx-auto">
            <div class="grid grid-cols-3 gap-3">
                <div class="bg-white rounded-xl h-24 flex items-center justify-center text-4xl shadow-md border-2 border-slate-200">
                    <span>🍒</span>
                </div>
                <div class="bg-white rounded-xl h-24 flex items-center justify-center text-4xl shadow-md border-2 border-slate-200">
                    <span>7️⃣</span>
                </div>
                <div class="bg-white rounded-xl h-24 flex items-center justify-center text-4xl shadow-md border-2 border-slate-200">
                    <span>💎</span>
                </div>
            </div>
            <div class="text-[11px] font-bold text-amber-300 tracking-wider uppercase mt-3 flex items-center justify-center gap-1.5">
                <i class="fa-solid fa-star text-amber-400"></i>
                <span>3x 7 = 50x JACKPOT &bull; 3x Diamant = 30x &bull; 2 gelijke = 1.5x</span>
                <i class="fa-solid fa-star text-amber-400"></i>
            </div>
        </div>

        <form action="{{ route('slots.spin') }}" method="POST" class="space-y-6 max-w-md mx-auto">
            @csrf

            <!-- Inzet -->
            <div>
                <div class="flex justify-between items-center mb-2">
                    <label class="text-xs font-bold uppercase tracking-wider text-slate-500">Inzet (KC)</label>
                    <span class="text-xs font-bold text-slate-400">Cash: <span class="text-slate-700 font-extrabold">{{ number_format($character->cash, 0, ',', '.') }} KC</span></span>
                </div>
                <div class="relative">
                    <input type="number" name="bet" min="50" max="50000" value="100" required
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-extrabold text-slate-800 focus:ring-2 focus:ring-amber-500">
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
                    class="w-full py-3.5 px-6 rounded-xl font-extrabold text-sm text-white transition shadow-sm {{ $character->isImprisoned() || $character->cash < 50 ? 'bg-slate-300 cursor-not-allowed' : 'bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 active:scale-[0.99]' }}">
                <i class="fa-solid fa-rotate-right mr-2"></i> Rollen Draaien (Spin!)
            </button>
        </form>
    </div>
</div>
@endsection
