@extends('admin.layout')

@section('admin-content')
<div class="space-y-6">

    <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-6 shadow-xs text-slate-900 dark:text-white">
        <div class="border-b border-slate-100 dark:border-slate-800 pb-4 mb-6">
            <h3 class="text-sm font-extrabold uppercase tracking-wider text-slate-900 dark:text-white flex items-center gap-2">
                <i class="fa-solid fa-sliders text-purple-600 dark:text-purple-400"></i> Winkansen & Balans Instellingen
            </h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                Pas de percentages en limieten direct aan. Wijzigingen zijn onmiddellijk van kracht in het spel.
            </p>
        </div>

        <form action="{{ route('admin.gameplay.update') }}" method="POST" class="space-y-6 max-w-3xl">
            @csrf

            <!-- Casino Win Rate -->
            <div class="bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70 rounded-xl p-4 space-y-2">
                <div class="flex items-center justify-between">
                    <div>
                        <label class="text-xs font-black uppercase text-slate-900 dark:text-white block">Casino Winkans Basis (%)</label>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400">Kans dat een gokker wint bij Roulette, Fruitautomaat of Hoger/Lager.</p>
                    </div>
                    <div class="w-24">
                        <input type="number" name="win_rate_casino" value="{{ $settings['win_rate_casino'] }}" min="1" max="100" 
                               class="w-full bg-white dark:bg-slate-900/90 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-xs font-mono font-black text-center text-purple-600 dark:text-purple-400 focus:outline-none focus:border-purple-500 dark:focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition">
                    </div>
                </div>
            </div>

            <!-- Theft Win Rate -->
            <div class="bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70 rounded-xl p-4 space-y-2">
                <div class="flex items-center justify-between">
                    <div>
                        <label class="text-xs font-black uppercase text-slate-900 dark:text-white block">Diefstal Succeskans (%)</label>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400">Algemene slaagkans voor diefstallen (Kantine overvallen, Zaalwacht beroven, etc).</p>
                    </div>
                    <div class="w-24">
                        <input type="number" name="win_rate_theft" value="{{ $settings['win_rate_theft'] }}" min="1" max="100" 
                               class="w-full bg-white dark:bg-slate-900/90 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-xs font-mono font-black text-center text-purple-600 dark:text-purple-400 focus:outline-none focus:border-purple-500 dark:focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition">
                    </div>
                </div>
            </div>

            <!-- Hustle Win Rate -->
            <div class="bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70 rounded-xl p-4 space-y-2">
                <div class="flex items-center justify-between">
                    <div>
                        <label class="text-xs font-black uppercase text-slate-900 dark:text-white block">Hosselen Succeskans (%)</label>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400">Slagingspercentage voor dagelijkse hossel activiteiten in de kliniek.</p>
                    </div>
                    <div class="w-24">
                        <input type="number" name="win_rate_hustle" value="{{ $settings['win_rate_hustle'] }}" min="1" max="100" 
                               class="w-full bg-white dark:bg-slate-900/90 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-xs font-mono font-black text-center text-purple-600 dark:text-purple-400 focus:outline-none focus:border-purple-500 dark:focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition">
                    </div>
                </div>
            </div>

            <!-- Pickpocket & Lockers -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70 rounded-xl p-4">
                    <label class="text-xs font-black uppercase text-slate-900 dark:text-white block mb-1">Zakkenrollen Slaagkans (%)</label>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mb-2">Kans om contant geld te rollen.</p>
                    <input type="number" name="win_rate_pickpocket" value="{{ $settings['win_rate_pickpocket'] }}" min="1" max="100" 
                           class="w-full bg-white dark:bg-slate-900/90 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-xs font-mono font-black text-center text-purple-600 dark:text-purple-400 focus:outline-none focus:border-purple-500 dark:focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition">
                </div>

                <div class="bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70 rounded-xl p-4">
                    <label class="text-xs font-black uppercase text-slate-900 dark:text-white block mb-1">Kluisjes Kraken Kans (%)</label>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mb-2">Slagingskans om patiëntenkluisjes te openen.</p>
                    <input type="number" name="win_rate_lockers" value="{{ $settings['win_rate_lockers'] }}" min="1" max="100" 
                           class="w-full bg-white dark:bg-slate-900/90 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-xs font-mono font-black text-center text-purple-600 dark:text-purple-400 focus:outline-none focus:border-purple-500 dark:focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition">
                </div>
            </div>

            <!-- Attack energy cost & Daily attacks limit & VIP hourly points -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70 rounded-xl p-4">
                    <label class="text-xs font-black uppercase text-slate-900 dark:text-white block mb-1">Energie per Aanval</label>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mb-2">Verbruik op de luchtplaats (standaard 20).</p>
                    <input type="number" name="attack_energy_cost" value="{{ $settings['attack_energy_cost'] }}" min="1" max="100" 
                           class="w-full bg-white dark:bg-slate-900/90 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-xs font-mono font-black text-center text-purple-600 dark:text-purple-400 focus:outline-none focus:border-purple-500 dark:focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition">
                </div>

                <div class="bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70 rounded-xl p-4">
                    <label class="text-xs font-black uppercase text-slate-900 dark:text-white block mb-1">Dagelijkse Aanvallen Limiet</label>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mb-2">Max aanvallen per dag (standaard 100).</p>
                    <input type="number" name="daily_attacks_limit" value="{{ $settings['daily_attacks_limit'] }}" min="10" max="500" 
                           class="w-full bg-white dark:bg-slate-900/90 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-xs font-mono font-black text-center text-purple-600 dark:text-purple-400 focus:outline-none focus:border-purple-500 dark:focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition">
                </div>

                <div class="bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70 rounded-xl p-4">
                    <label class="text-xs font-black uppercase text-slate-900 dark:text-white block mb-1">VIP Beloning per Uur</label>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mb-2">Punten voor actieve VIP spelers (standaard 5).</p>
                    <input type="number" name="vip_hourly_points" value="{{ $settings['vip_hourly_points'] }}" min="1" max="50" 
                           class="w-full bg-white dark:bg-slate-900/90 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-xs font-mono font-black text-center text-purple-600 dark:text-purple-400 focus:outline-none focus:border-purple-500 dark:focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition">
                </div>
            </div>

            <div class="flex justify-end pt-4">
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-700 dark:bg-purple-600 dark:hover:bg-purple-500 text-white font-black text-xs uppercase tracking-wider transition-all duration-150 active:scale-95 shadow-xs cursor-pointer">
                    <i class="fa-solid fa-floppy-disk mr-1.5"></i> Winkansen Opslaan
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
