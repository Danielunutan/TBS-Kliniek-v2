@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="bg-gradient-to-r from-slate-900 to-rose-950 rounded-2xl p-6 sm:p-8 text-white relative overflow-hidden">
        <div class="relative z-10">
            <div class="inline-flex items-center gap-2 bg-rose-500/20 text-rose-300 text-xs font-bold px-3 py-1 rounded-full border border-rose-400/20 mb-3">
                <i class="fa-solid fa-dharmachakra"></i> Frans Roulette
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">Europees Roulette</h1>
            <p class="text-slate-300 text-xs sm:text-sm mt-1">Plaats je inzet op rood, zwart, even, oneven of ga voor de 35x jackpot op Groen 0!</p>
        </div>
        <div class="absolute -right-6 -bottom-6 text-white/5 text-9xl font-black select-none pointer-events-none">
            <i class="fa-solid fa-circle-notch"></i>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-6 sm:p-8">
        <form action="{{ route('roulette.play') }}" method="POST" class="space-y-6">
            @csrf

            <!-- Inzet Opties -->
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 text-center mb-3">Kies je inzet type</label>
                
                <div class="space-y-3">
                    <!-- Kleuren -->
                    <div class="grid grid-cols-3 gap-3">
                        <label class="cursor-pointer">
                            <input type="radio" name="bet_type" value="rood" class="peer sr-only" required checked>
                            <div class="p-4 rounded-xl border-2 border-slate-200 peer-checked:border-rose-600 peer-checked:bg-rose-500 peer-checked:text-white text-center hover:border-rose-300 transition shadow-xs flex flex-col items-center gap-1 text-slate-700">
                                <span class="text-xs font-black uppercase">🔴 Rood</span>
                                <span class="text-[10px] font-bold opacity-80">Uitbetaling: 2x</span>
                            </div>
                        </label>

                        <label class="cursor-pointer">
                            <input type="radio" name="bet_type" value="zwart" class="peer sr-only" required>
                            <div class="p-4 rounded-xl border-2 border-slate-200 peer-checked:border-slate-900 peer-checked:bg-slate-900 peer-checked:text-white text-center hover:border-slate-400 transition shadow-xs flex flex-col items-center gap-1 text-slate-700">
                                <span class="text-xs font-black uppercase">⚫ Zwart</span>
                                <span class="text-[10px] font-bold opacity-80">Uitbetaling: 2x</span>
                            </div>
                        </label>

                        <label class="cursor-pointer">
                            <input type="radio" name="bet_type" value="groen" class="peer sr-only" required>
                            <div class="p-4 rounded-xl border-2 border-slate-200 peer-checked:border-emerald-600 peer-checked:bg-emerald-600 peer-checked:text-white text-center hover:border-emerald-300 transition shadow-xs flex flex-col items-center gap-1 text-slate-700">
                                <span class="text-xs font-black uppercase">🟢 Groen (0)</span>
                                <span class="text-[10px] font-bold opacity-80">Uitbetaling: 35x</span>
                            </div>
                        </label>
                    </div>

                    <!-- Even / Oneven -->
                    <div class="grid grid-cols-2 gap-3">
                        <label class="cursor-pointer">
                            <input type="radio" name="bet_type" value="even" class="peer sr-only" required>
                            <div class="p-3 rounded-xl border-2 border-slate-200 peer-checked:border-indigo-600 peer-checked:bg-indigo-50 peer-checked:text-indigo-900 text-center hover:border-indigo-300 transition shadow-xs flex items-center justify-between px-4">
                                <span class="text-xs font-black uppercase">Even</span>
                                <span class="text-[10px] font-bold text-slate-400">2x</span>
                            </div>
                        </label>

                        <label class="cursor-pointer">
                            <input type="radio" name="bet_type" value="oneven" class="peer sr-only" required>
                            <div class="p-3 rounded-xl border-2 border-slate-200 peer-checked:border-indigo-600 peer-checked:bg-indigo-50 peer-checked:text-indigo-900 text-center hover:border-indigo-300 transition shadow-xs flex items-center justify-between px-4">
                                <span class="text-xs font-black uppercase">Oneven</span>
                                <span class="text-[10px] font-bold text-slate-400">2x</span>
                            </div>
                        </label>
                    </div>

                    <!-- 1-18 / 19-36 -->
                    <div class="grid grid-cols-2 gap-3">
                        <label class="cursor-pointer">
                            <input type="radio" name="bet_type" value="eerste_helft" class="peer sr-only" required>
                            <div class="p-3 rounded-xl border-2 border-slate-200 peer-checked:border-indigo-600 peer-checked:bg-indigo-50 peer-checked:text-indigo-900 text-center hover:border-indigo-300 transition shadow-xs flex items-center justify-between px-4">
                                <span class="text-xs font-black uppercase">1 t/m 18</span>
                                <span class="text-[10px] font-bold text-slate-400">2x</span>
                            </div>
                        </label>

                        <label class="cursor-pointer">
                            <input type="radio" name="bet_type" value="tweede_helft" class="peer sr-only" required>
                            <div class="p-3 rounded-xl border-2 border-slate-200 peer-checked:border-indigo-600 peer-checked:bg-indigo-50 peer-checked:text-indigo-900 text-center hover:border-indigo-300 transition shadow-xs flex items-center justify-between px-4">
                                <span class="text-xs font-black uppercase">19 t/m 36</span>
                                <span class="text-[10px] font-bold text-slate-400">2x</span>
                            </div>
                        </label>
                    </div>
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
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-extrabold text-slate-800 focus:ring-2 focus:ring-rose-500">
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
                    class="w-full py-3.5 px-6 rounded-xl font-extrabold text-sm text-white transition shadow-sm {{ $character->isImprisoned() || $character->cash < 50 ? 'bg-slate-300 cursor-not-allowed' : 'bg-rose-600 hover:bg-rose-700 active:scale-[0.99]' }}">
                <i class="fa-solid fa-dharmachakra mr-2"></i> Draai aan het wiel!
            </button>
        </form>
    </div>
</div>
@endsection
