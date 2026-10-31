@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="bg-white dark:bg-slate-900 rounded-2xl p-6 sm:p-8 text-slate-800 dark:text-slate-100 relative overflow-hidden shadow-xs border border-slate-200/90 dark:border-slate-800">
        <div class="relative z-10">
            <div class="inline-flex items-center gap-2 bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 text-xs font-bold px-3 py-1 rounded-full border border-rose-200/80 dark:border-rose-800/70 mb-3">
                <i class="fa-solid fa-dharmachakra text-rose-600 dark:text-rose-400"></i> Frans Roulette
            </div>
            <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-slate-900 dark:text-white">Europees Roulette</h1>
            <p class="text-slate-500 dark:text-slate-400 text-xs sm:text-sm mt-1">Plaats je inzet op rood, zwart, even, oneven of ga voor de 35x jackpot op Groen 0!</p>
        </div>
        <div class="absolute -right-6 -bottom-6 text-slate-100 dark:text-slate-800/40 text-9xl font-black select-none pointer-events-none">
            <i class="fa-solid fa-circle-notch"></i>
        </div>
    </div>

    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/90 dark:border-slate-800 shadow-xs p-6 sm:p-8">
        <form action="{{ route('roulette.play') }}" method="POST" class="space-y-6">
            @csrf

            <!-- Inzet Opties -->
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 text-center mb-3">Kies je inzet type</label>
                
                <div class="space-y-3">
                    <!-- Kleuren -->
                    <div class="grid grid-cols-3 gap-3">
                        <label class="cursor-pointer">
                            <input type="radio" name="bet_type" value="rood" class="peer sr-only" required checked>
                            <div class="p-4 rounded-xl border-2 border-slate-200 dark:border-slate-700 bg-slate-50/90 dark:bg-slate-800/60 peer-checked:border-rose-600 peer-checked:bg-rose-500 peer-checked:text-white text-center hover:border-rose-300 transition shadow-xs flex flex-col items-center gap-1 text-slate-700 dark:text-slate-200 group">
                                <span class="text-xs font-black uppercase flex items-center gap-1.5"><i class="fa-solid fa-circle text-[8px] text-rose-500 group-[.peer:checked+&]:text-white"></i> Rood</span>
                                <span class="text-[10px] font-bold opacity-80">Uitbetaling: 2x</span>
                            </div>
                        </label>

                        <label class="cursor-pointer">
                            <input type="radio" name="bet_type" value="zwart" class="peer sr-only" required>
                            <div class="p-4 rounded-xl border-2 border-slate-200 dark:border-slate-700 bg-slate-50/90 dark:bg-slate-800/60 peer-checked:border-slate-900 dark:peer-checked:border-purple-500 peer-checked:bg-slate-900 dark:peer-checked:bg-purple-600 peer-checked:text-white text-center hover:border-slate-400 transition shadow-xs flex flex-col items-center gap-1 text-slate-700 dark:text-slate-200 group">
                                <span class="text-xs font-black uppercase flex items-center gap-1.5"><i class="fa-solid fa-circle text-[8px] text-slate-800 dark:text-slate-300 group-[.peer:checked+&]:text-white"></i> Zwart</span>
                                <span class="text-[10px] font-bold opacity-80">Uitbetaling: 2x</span>
                            </div>
                        </label>

                        <label class="cursor-pointer">
                            <input type="radio" name="bet_type" value="groen" class="peer sr-only" required>
                            <div class="p-4 rounded-xl border-2 border-slate-200 dark:border-slate-700 bg-slate-50/90 dark:bg-slate-800/60 peer-checked:border-emerald-600 peer-checked:bg-emerald-600 peer-checked:text-white text-center hover:border-emerald-300 transition shadow-xs flex flex-col items-center gap-1 text-slate-700 dark:text-slate-200 group">
                                <span class="text-xs font-black uppercase flex items-center gap-1.5"><i class="fa-solid fa-circle text-[8px] text-emerald-500 group-[.peer:checked+&]:text-white"></i> Groen (0)</span>
                                <span class="text-[10px] font-bold opacity-80">Uitbetaling: 35x</span>
                            </div>
                        </label>
                    </div>

                    <!-- Even / Oneven -->
                    <div class="grid grid-cols-2 gap-3">
                        <label class="cursor-pointer">
                            <input type="radio" name="bet_type" value="even" class="peer sr-only" required>
                            <div class="p-3 rounded-xl border-2 border-slate-200 dark:border-slate-700 bg-slate-50/90 dark:bg-slate-800/60 peer-checked:border-purple-600 peer-checked:bg-purple-50 dark:peer-checked:bg-purple-950/60 peer-checked:text-purple-900 dark:peer-checked:text-purple-200 text-center hover:border-purple-300 transition shadow-xs flex items-center justify-between px-4 text-slate-700 dark:text-slate-200">
                                <span class="text-xs font-black uppercase">Even</span>
                                <span class="text-[10px] font-bold text-slate-400">2x</span>
                            </div>
                        </label>

                        <label class="cursor-pointer">
                            <input type="radio" name="bet_type" value="oneven" class="peer sr-only" required>
                            <div class="p-3 rounded-xl border-2 border-slate-200 dark:border-slate-700 bg-slate-50/90 dark:bg-slate-800/60 peer-checked:border-purple-600 peer-checked:bg-purple-50 dark:peer-checked:bg-purple-950/60 peer-checked:text-purple-900 dark:peer-checked:text-purple-200 text-center hover:border-purple-300 transition shadow-xs flex items-center justify-between px-4 text-slate-700 dark:text-slate-200">
                                <span class="text-xs font-black uppercase">Oneven</span>
                                <span class="text-[10px] font-bold text-slate-400">2x</span>
                            </div>
                        </label>
                    </div>

                    <!-- 1-18 / 19-36 -->
                    <div class="grid grid-cols-2 gap-3">
                        <label class="cursor-pointer">
                            <input type="radio" name="bet_type" value="eerste_helft" class="peer sr-only" required>
                            <div class="p-3 rounded-xl border-2 border-slate-200 dark:border-slate-700 bg-slate-50/90 dark:bg-slate-800/60 peer-checked:border-purple-600 peer-checked:bg-purple-50 dark:peer-checked:bg-purple-950/60 peer-checked:text-purple-900 dark:peer-checked:text-purple-200 text-center hover:border-purple-300 transition shadow-xs flex items-center justify-between px-4 text-slate-700 dark:text-slate-200">
                                <span class="text-xs font-black uppercase">1 t/m 18</span>
                                <span class="text-[10px] font-bold text-slate-400">2x</span>
                            </div>
                        </label>

                        <label class="cursor-pointer">
                            <input type="radio" name="bet_type" value="tweede_helft" class="peer sr-only" required>
                            <div class="p-3 rounded-xl border-2 border-slate-200 dark:border-slate-700 bg-slate-50/90 dark:bg-slate-800/60 peer-checked:border-purple-600 peer-checked:bg-purple-50 dark:peer-checked:bg-purple-950/60 peer-checked:text-purple-900 dark:peer-checked:text-purple-200 text-center hover:border-purple-300 transition shadow-xs flex items-center justify-between px-4 text-slate-700 dark:text-slate-200">
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

            <button type="submit"
                    {{ $character->isImprisoned() || $character->isComatose() || $character->cash < 50 ? 'disabled' : '' }}
                    class="w-full py-3.5 px-6 rounded-xl font-black text-sm transition-all duration-150 shadow-xs cursor-pointer active:scale-95 {{ $character->isImprisoned() || $character->isComatose() || $character->cash < 50 ? 'bg-slate-100 dark:bg-slate-800/80 text-slate-400 dark:text-slate-500 border border-slate-200/80 dark:border-slate-700/60 cursor-not-allowed active:scale-100' : 'bg-rose-600 hover:bg-rose-700 dark:bg-rose-600 dark:hover:bg-rose-500 text-white' }}">
                <i class="fa-solid fa-dharmachakra mr-2"></i> Draai aan het wiel!
            </button>
        </form>
    </div>
</div>
@endsection
