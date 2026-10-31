@extends('admin.layout')

@section('admin-content')
<div class="space-y-6">

    <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-6 shadow-xs text-slate-900 dark:text-white">
        <div class="border-b border-slate-100 dark:border-slate-800 pb-4 mb-6">
            <h3 class="text-sm font-extrabold uppercase tracking-wider text-slate-900 dark:text-white flex items-center gap-2">
                <i class="fa-solid fa-box-open text-purple-600 dark:text-purple-400"></i> Kisten & Smokkeltassen Beheer
            </h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                Beheer de aankoopprijzen, verkoopwaardes en de minimum en maximum buit van de koffers en pakketjes in de TBS kliniek.
            </p>
        </div>

        <form action="{{ route('admin.chests.update') }}" method="POST" class="space-y-6 max-w-3xl">
            @csrf

            <!-- XXL Koffer Section -->
            <div class="space-y-4">
                <h4 class="text-xs font-black uppercase text-purple-900 dark:text-purple-300 tracking-wider flex items-center gap-2">
                    <i class="fa-solid fa-gift text-purple-600 dark:text-purple-400"></i> XXL Koffer Configuratie
                </h4>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70 rounded-xl p-4">
                        <label class="text-xs font-black uppercase text-slate-900 dark:text-white block mb-1">Prijs in Contant (€)</label>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 mb-2">Kosten om een XXL koffer direct te kopen.</p>
                        <input type="number" name="case_xxl_price_cash" value="{{ $settings['case_xxl_price_cash'] }}" min="0" 
                               class="w-full bg-white dark:bg-slate-900/90 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-xs font-mono font-black text-purple-600 dark:text-purple-400 focus:outline-none focus:border-purple-500 dark:focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition">
                    </div>

                    <div class="bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70 rounded-xl p-4">
                        <label class="text-xs font-black uppercase text-slate-900 dark:text-white block mb-1">Prijs in Sterren</label>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 mb-2">Aantal sterren vereist in de winkel.</p>
                        <input type="number" name="case_xxl_price_stars" value="{{ $settings['case_xxl_price_stars'] }}" min="0" 
                               class="w-full bg-white dark:bg-slate-900/90 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-xs font-mono font-black text-purple-600 dark:text-purple-400 focus:outline-none focus:border-purple-500 dark:focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70 rounded-xl p-4">
                        <label class="text-xs font-black uppercase text-slate-900 dark:text-white block mb-1">Minimale Cash Beloning (€)</label>
                        <input type="number" name="case_xxl_min_cash" value="{{ $settings['case_xxl_min_cash'] }}" min="0" 
                               class="w-full bg-white dark:bg-slate-900/90 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-xs font-mono font-black text-emerald-600 dark:text-emerald-400 focus:outline-none focus:border-purple-500 dark:focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition">
                    </div>

                    <div class="bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70 rounded-xl p-4">
                        <label class="text-xs font-black uppercase text-slate-900 dark:text-white block mb-1">Maximale Cash Beloning (€)</label>
                        <input type="number" name="case_xxl_max_cash" value="{{ $settings['case_xxl_max_cash'] }}" min="0" 
                               class="w-full bg-white dark:bg-slate-900/90 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-xs font-mono font-black text-emerald-600 dark:text-emerald-400 focus:outline-none focus:border-purple-500 dark:focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition">
                    </div>

                    <div class="bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70 rounded-xl p-4">
                        <label class="text-xs font-black uppercase text-slate-900 dark:text-white block mb-1">Bonus Sterren Kans</label>
                        <input type="number" name="case_xxl_stars_reward" value="{{ $settings['case_xxl_stars_reward'] }}" min="0" 
                               class="w-full bg-white dark:bg-slate-900/90 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-xs font-mono font-black text-amber-500 dark:text-amber-400 focus:outline-none focus:border-purple-500 dark:focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition">
                    </div>
                </div>
            </div>

            <!-- Regular Smokkeltas Section -->
            <div class="space-y-4 pt-4 border-t border-slate-100 dark:border-slate-800">
                <h4 class="text-xs font-black uppercase text-purple-900 dark:text-purple-300 tracking-wider flex items-center gap-2">
                    <i class="fa-solid fa-briefcase text-purple-600 dark:text-purple-400"></i> Smokkeltas & Koffers Inhoud
                </h4>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70 rounded-xl p-4">
                        <label class="text-xs font-black uppercase text-slate-900 dark:text-white block mb-1">Directe Verkoopwaarde (€)</label>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 mb-2">Opbrengst bij verkoop aan heler.</p>
                        <input type="number" name="bag_sell_reward" value="{{ $settings['bag_sell_reward'] }}" min="0" 
                               class="w-full bg-white dark:bg-slate-900/90 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-xs font-mono font-black text-purple-600 dark:text-purple-400 focus:outline-none focus:border-purple-500 dark:focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition">
                    </div>

                    <div class="bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70 rounded-xl p-4">
                        <label class="text-xs font-black uppercase text-slate-900 dark:text-white block mb-1">Openen Min Cash (€)</label>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 mb-2">Minimale buit bij openritsen.</p>
                        <input type="number" name="bag_open_min_cash" value="{{ $settings['bag_open_min_cash'] }}" min="0" 
                               class="w-full bg-white dark:bg-slate-900/90 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-xs font-mono font-black text-emerald-600 dark:text-emerald-400 focus:outline-none focus:border-purple-500 dark:focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition">
                    </div>

                    <div class="bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70 rounded-xl p-4">
                        <label class="text-xs font-black uppercase text-slate-900 dark:text-white block mb-1">Openen Max Cash (€)</label>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 mb-2">Maximale buit bij openritsen.</p>
                        <input type="number" name="bag_open_max_cash" value="{{ $settings['bag_open_max_cash'] }}" min="0" 
                               class="w-full bg-white dark:bg-slate-900/90 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-xs font-mono font-black text-emerald-600 dark:text-emerald-400 focus:outline-none focus:border-purple-500 dark:focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition">
                    </div>
                </div>
            </div>

            <div class="flex justify-end pt-4">
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-700 dark:bg-purple-600 dark:hover:bg-purple-500 text-white font-black text-xs uppercase tracking-wider transition-all duration-150 active:scale-95 shadow-xs cursor-pointer">
                    <i class="fa-solid fa-floppy-disk mr-1.5"></i> Kist Instellingen Opslaan
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
