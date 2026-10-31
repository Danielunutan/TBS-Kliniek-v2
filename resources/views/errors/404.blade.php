@extends('layouts.app')

@section('title', '404 - Pagina Niet Gevonden | TBS Kliniek')

@section('content')
<div class="max-w-3xl mx-auto py-8 sm:py-14 px-4 space-y-6">
    
    <!-- Main 404 Card -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-6 sm:p-10 shadow-xs relative overflow-hidden text-center space-y-6">
        
        <!-- Ambient Glowing Background Accents -->
        <div class="absolute -top-24 -left-24 w-56 h-56 bg-purple-500/10 dark:bg-purple-600/15 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-24 -right-24 w-56 h-56 bg-amber-500/10 dark:bg-amber-600/15 rounded-full blur-3xl pointer-events-none"></div>

        <!-- Sleek High-Impact 404 Typography & Status Tag -->
        <div class="flex flex-col items-center justify-center space-y-3">
            <div class="relative select-none">
                <span class="text-7xl sm:text-9xl font-black tracking-tighter bg-gradient-to-r from-purple-600 via-indigo-600 to-amber-500 bg-clip-text text-transparent drop-shadow-sm leading-none block">
                    404
                </span>
            </div>

            <!-- Balanced Crisp Security Tag -->
            <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-amber-50 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 border border-amber-200/80 dark:border-amber-800/70 font-black text-xs uppercase tracking-wider shadow-2xs">
                <i class="fa-solid fa-triangle-exclamation text-amber-600 dark:text-amber-400"></i>
                <span>Code 404 &bull; Afdeling Niet Gevonden</span>
            </div>
        </div>

        <!-- Heading & Narrative -->
        <div class="space-y-2 max-w-lg mx-auto">
            <h1 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight">
                Verdwaald in de Beveiligde Vleugel
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 leading-relaxed">
                Hé boef! Deze cel, gang of pagina bestaat niet (meer) of is hermetisch afgegrendeld door de zaalwacht. Geen paniek, via het onderstaande menu vind je direct weer de uitgang.
            </p>
        </div>

        <!-- Snelmenu Header Divider -->
        <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-left">
            <div>
                <span class="text-xs font-black uppercase tracking-wider text-slate-900 dark:text-white flex items-center gap-1.5">
                    <i class="fa-solid fa-compass text-purple-600 dark:text-purple-400"></i> Kliniek Snelmenu & Afdelingen
                </span>
                <p class="text-[11px] text-slate-400 dark:text-slate-500">Kies een afdeling om direct verder te gaan:</p>
            </div>
            <button type="button" onclick="window.history.back()" class="text-xs text-purple-600 dark:text-purple-400 font-bold hover:underline flex items-center gap-1 cursor-pointer">
                <i class="fa-solid fa-arrow-left text-[10px]"></i> Vorige pagina
            </button>
        </div>

        <!-- 6-Grid Interactive Quick Navigation Menu -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3 text-left">
            
            <!-- 1. Home / Dashboard -->
            <a href="{{ Auth::check() ? route('dashboard') : route('login') }}" 
               class="p-3.5 rounded-xl bg-slate-50/90 dark:bg-slate-800/60 hover:bg-purple-50/70 dark:hover:bg-purple-950/40 border border-slate-200/80 dark:border-slate-700/70 hover:border-purple-300 dark:hover:border-purple-700/80 transition-all duration-200 group flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-purple-100 dark:bg-purple-900/60 text-purple-600 dark:text-purple-300 flex items-center justify-center text-base shrink-0 group-hover:scale-105 transition shadow-2xs">
                    <i class="fa-solid fa-house"></i>
                </div>
                <div>
                    <h3 class="text-xs font-extrabold text-slate-900 dark:text-white group-hover:text-purple-600 dark:group-hover:text-purple-400">
                        {{ Auth::check() ? 'Mijn Cel (Dashboard)' : 'Inloggen / Starten' }}
                    </h3>
                    <p class="text-[10px] text-slate-500 dark:text-slate-400">Naar het hoofdkwartier</p>
                </div>
            </a>

            <!-- 2. Kennisbank & FAQ -->
            <a href="{{ route('faq.index') }}" 
               class="p-3.5 rounded-xl bg-slate-50/90 dark:bg-slate-800/60 hover:bg-amber-50/70 dark:hover:bg-amber-950/40 border border-slate-200/80 dark:border-slate-700/70 hover:border-amber-300 dark:hover:border-amber-700/80 transition-all duration-200 group flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-amber-100 dark:bg-amber-900/60 text-amber-600 dark:text-amber-300 flex items-center justify-center text-base shrink-0 group-hover:scale-105 transition shadow-2xs">
                    <i class="fa-solid fa-circle-question"></i>
                </div>
                <div>
                    <h3 class="text-xs font-extrabold text-slate-900 dark:text-white group-hover:text-amber-600 dark:group-hover:text-amber-400">
                        Kennisbank & FAQ
                    </h3>
                    <p class="text-[10px] text-slate-500 dark:text-slate-400">Spelregels en speluitleg</p>
                </div>
            </a>

            <!-- 3. De Luchtplaats (PvP) -->
            <a href="{{ route('fight.index') }}" 
               class="p-3.5 rounded-xl bg-slate-50/90 dark:bg-slate-800/60 hover:bg-rose-50/70 dark:hover:bg-rose-950/40 border border-slate-200/80 dark:border-slate-700/70 hover:border-rose-300 dark:hover:border-rose-700/80 transition-all duration-200 group flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-rose-100 dark:bg-rose-900/60 text-rose-600 dark:text-rose-300 flex items-center justify-center text-base shrink-0 group-hover:scale-105 transition shadow-2xs">
                    <i class="fa-solid fa-hand-fist"></i>
                </div>
                <div>
                    <h3 class="text-xs font-extrabold text-slate-900 dark:text-white group-hover:text-rose-600 dark:group-hover:text-rose-400">
                        De Luchtplaats
                    </h3>
                    <p class="text-[10px] text-slate-500 dark:text-slate-400">Vecht voor respect</p>
                </div>
            </a>

            <!-- 4. MedicijnKluis -->
            <a href="{{ route('vault.index') }}" 
               class="p-3.5 rounded-xl bg-slate-50/90 dark:bg-slate-800/60 hover:bg-blue-50/70 dark:hover:bg-blue-950/40 border border-slate-200/80 dark:border-slate-700/70 hover:border-blue-300 dark:hover:border-blue-700/80 transition-all duration-200 group flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-blue-100 dark:bg-blue-900/60 text-blue-600 dark:text-blue-300 flex items-center justify-center text-base shrink-0 group-hover:scale-105 transition shadow-2xs">
                    <i class="fa-solid fa-vault"></i>
                </div>
                <div>
                    <h3 class="text-xs font-extrabold text-slate-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400">
                        MedicijnKluis
                    </h3>
                    <p class="text-[10px] text-slate-500 dark:text-slate-400">Kraak codes voor jackpots</p>
                </div>
            </a>

            <!-- 5. Ranglijsten -->
            <a href="{{ route('leaderboard.index') }}" 
               class="p-3.5 rounded-xl bg-slate-50/90 dark:bg-slate-800/60 hover:bg-emerald-50/70 dark:hover:bg-emerald-950/40 border border-slate-200/80 dark:border-slate-700/70 hover:border-emerald-300 dark:hover:border-emerald-700/80 transition-all duration-200 group flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-100 dark:bg-emerald-900/60 text-emerald-600 dark:text-emerald-300 flex items-center justify-center text-base shrink-0 group-hover:scale-105 transition shadow-2xs">
                    <i class="fa-solid fa-trophy"></i>
                </div>
                <div>
                    <h3 class="text-xs font-extrabold text-slate-900 dark:text-white group-hover:text-emerald-600 dark:group-hover:text-emerald-400">
                        Ranglijsten
                    </h3>
                    <p class="text-[10px] text-slate-500 dark:text-slate-400">Top patiënten en bendes</p>
                </div>
            </a>

            <!-- 6. Support Tickets -->
            <a href="{{ route('help.tickets.index') }}" 
               class="p-3.5 rounded-xl bg-slate-50/90 dark:bg-slate-800/60 hover:bg-indigo-50/70 dark:hover:bg-indigo-950/40 border border-slate-200/80 dark:border-slate-700/70 hover:border-indigo-300 dark:hover:border-indigo-700/80 transition-all duration-200 group flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-indigo-100 dark:bg-indigo-900/60 text-indigo-600 dark:text-indigo-300 flex items-center justify-center text-base shrink-0 group-hover:scale-105 transition shadow-2xs">
                    <i class="fa-solid fa-headset"></i>
                </div>
                <div>
                    <h3 class="text-xs font-extrabold text-slate-900 dark:text-white group-hover:text-indigo-600 dark:group-hover:text-indigo-400">
                        Help & Support
                    </h3>
                    <p class="text-[10px] text-slate-500 dark:text-slate-400">Vraag de zaalwacht</p>
                </div>
            </a>

        </div>

        <!-- Bottom Action CTA Buttons -->
        <div class="pt-3 flex flex-col sm:flex-row items-center justify-center gap-3">
            <button type="button" onclick="window.history.back()" 
                    class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 font-bold text-xs transition-all duration-150 active:scale-95 border border-slate-200/80 dark:border-slate-700 flex items-center justify-center gap-2 cursor-pointer">
                <i class="fa-solid fa-arrow-left"></i>
                <span>Terug naar Vorige Pagina</span>
            </button>
            <a href="{{ Auth::check() ? route('dashboard') : route('login') }}" 
               class="w-full sm:w-auto px-6 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-700 dark:bg-purple-600 dark:hover:bg-purple-500 text-white font-black text-xs transition-all duration-150 active:scale-95 shadow-xs flex items-center justify-center gap-2 cursor-pointer">
                <i class="fa-solid fa-house"></i>
                <span>{{ Auth::check() ? 'Ga naar Mijn Dashboard' : 'Naar Startpagina' }}</span>
            </a>
        </div>

    </div>
</div>
@endsection
