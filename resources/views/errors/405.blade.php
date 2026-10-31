@extends('layouts.app')

@section('title', '405 - Actie Niet Toegestaan | TBS Kliniek')

@section('content')
<div class="max-w-3xl mx-auto py-8 sm:py-14 px-4 space-y-6">
    
    <!-- Main 405 Card -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-6 sm:p-10 shadow-xs relative overflow-hidden text-center space-y-6">
        
        <!-- Ambient Glowing Background Accents -->
        <div class="absolute -top-24 -left-24 w-56 h-56 bg-rose-500/10 dark:bg-rose-600/15 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-24 -right-24 w-56 h-56 bg-amber-500/10 dark:bg-amber-600/15 rounded-full blur-3xl pointer-events-none"></div>

        <!-- Sleek High-Impact 405 Typography & Status Tag -->
        <div class="flex flex-col items-center justify-center space-y-3">
            <div class="relative select-none">
                <span class="text-7xl sm:text-9xl font-black tracking-tighter bg-gradient-to-r from-rose-600 via-amber-600 to-yellow-500 bg-clip-text text-transparent drop-shadow-sm leading-none block">
                    405
                </span>
            </div>

            <!-- Security Tag -->
            <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border border-rose-200/80 dark:border-rose-800/70 font-black text-xs uppercase tracking-wider shadow-2xs">
                <i class="fa-solid fa-hand text-rose-600 dark:text-rose-400"></i>
                <span>Code 405 &bull; Actie Niet Toegestaan via GET</span>
            </div>
        </div>

        <!-- Heading & Narrative -->
        <div class="space-y-2 max-w-lg mx-auto">
            <h1 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight">
                Pagina Herladen of Verificatie
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 leading-relaxed">
                Deze actie (zoals een aankoop of donatie) kan niet rechtstreeks via een weblink of na een paginaherlading worden uitgevoerd. Dit gebeurt wanneer een formulier opnieuw wordt opgevraagd of na een tussentijdse Cloudflare verificatie.
            </p>
        </div>

        <!-- Snelmenu Header Divider -->
        <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-left">
            <div>
                <span class="text-xs font-black uppercase tracking-wider text-slate-900 dark:text-white flex items-center gap-1.5">
                    <i class="fa-solid fa-compass text-rose-600 dark:text-rose-400"></i> Direct Veilig Verder
                </span>
                <p class="text-[11px] text-slate-400 dark:text-slate-500">Kies een afdeling om verder te spelen:</p>
            </div>
            <button type="button" onclick="window.history.back()" class="text-xs text-rose-600 dark:text-rose-400 font-bold hover:underline flex items-center gap-1 cursor-pointer">
                <i class="fa-solid fa-arrow-left text-[10px]"></i> Vorige pagina
            </button>
        </div>

        <!-- 6-Grid Interactive Quick Navigation Menu -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3 text-left">
            
            <!-- 1. Home / Dashboard -->
            <a href="{{ Auth::check() ? route('dashboard') : route('login') }}" 
               class="p-3.5 rounded-xl bg-slate-50/90 dark:bg-slate-800/60 hover:bg-rose-50/70 dark:hover:bg-rose-950/40 border border-slate-200/80 dark:border-slate-700/70 hover:border-rose-300 dark:hover:border-rose-700/80 transition-all duration-200 group flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-purple-100 dark:bg-purple-900/60 text-purple-600 dark:text-purple-300 flex items-center justify-center text-base shrink-0 group-hover:scale-105 transition shadow-2xs">
                    <i class="fa-solid fa-house"></i>
                </div>
                <div>
                    <h2 class="text-xs font-black text-slate-900 dark:text-white leading-tight">Mijn Dashboard</h2>
                    <span class="text-[10px] text-slate-500 dark:text-slate-400">Hoofdpagina</span>
                </div>
            </a>

            <!-- 2. Gangs -->
            <a href="{{ route('gangs.index') }}" 
               class="p-3.5 rounded-xl bg-slate-50/90 dark:bg-slate-800/60 hover:bg-rose-50/70 dark:hover:bg-rose-950/40 border border-slate-200/80 dark:border-slate-700/70 hover:border-rose-300 dark:hover:border-rose-700/80 transition-all duration-200 group flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-amber-100 dark:bg-amber-900/60 text-amber-600 dark:text-amber-300 flex items-center justify-center text-base shrink-0 group-hover:scale-105 transition shadow-2xs">
                    <i class="fa-solid fa-users"></i>
                </div>
                <div>
                    <h2 class="text-xs font-black text-slate-900 dark:text-white leading-tight">Bende Hoofdkwartier</h2>
                    <span class="text-[10px] text-slate-500 dark:text-slate-400">Gangs &amp; Winkel</span>
                </div>
            </a>

            <!-- 3. Winkel -->
            <a href="{{ route('shop.index') }}" 
               class="p-3.5 rounded-xl bg-slate-50/90 dark:bg-slate-800/60 hover:bg-rose-50/70 dark:hover:bg-rose-950/40 border border-slate-200/80 dark:border-slate-700/70 hover:border-rose-300 dark:hover:border-rose-700/80 transition-all duration-200 group flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-100 dark:bg-emerald-900/60 text-emerald-600 dark:text-emerald-300 flex items-center justify-center text-base shrink-0 group-hover:scale-105 transition shadow-2xs">
                    <i class="fa-solid fa-cart-shopping"></i>
                </div>
                <div>
                    <h2 class="text-xs font-black text-slate-900 dark:text-white leading-tight">Sterren Shop</h2>
                    <span class="text-[10px] text-slate-500 dark:text-slate-400">Kogels &amp; Voordelen</span>
                </div>
            </a>

            <!-- 4. Bank -->
            <a href="{{ route('bank.index') }}" 
               class="p-3.5 rounded-xl bg-slate-50/90 dark:bg-slate-800/60 hover:bg-rose-50/70 dark:hover:bg-rose-950/40 border border-slate-200/80 dark:border-slate-700/70 hover:border-rose-300 dark:hover:border-rose-700/80 transition-all duration-200 group flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-blue-100 dark:bg-blue-900/60 text-blue-600 dark:text-blue-300 flex items-center justify-center text-base shrink-0 group-hover:scale-105 transition shadow-2xs">
                    <i class="fa-solid fa-vault"></i>
                </div>
                <div>
                    <h2 class="text-xs font-black text-slate-900 dark:text-white leading-tight">De Bank</h2>
                    <span class="text-[10px] text-slate-500 dark:text-slate-400">Veilig geld sparen</span>
                </div>
            </a>

            <!-- 5. Liquidatie / Wapenarsenaal -->
            <a href="{{ route('hitman.index') }}" 
               class="p-3.5 rounded-xl bg-slate-50/90 dark:bg-slate-800/60 hover:bg-rose-50/70 dark:hover:bg-rose-950/40 border border-slate-200/80 dark:border-slate-700/70 hover:border-rose-300 dark:hover:border-rose-700/80 transition-all duration-200 group flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-rose-100 dark:bg-rose-900/60 text-rose-600 dark:text-rose-300 flex items-center justify-center text-base shrink-0 group-hover:scale-105 transition shadow-2xs">
                    <i class="fa-solid fa-crosshairs"></i>
                </div>
                <div>
                    <h2 class="text-xs font-black text-slate-900 dark:text-white leading-tight">Liquidatie PvP</h2>
                    <span class="text-[10px] text-slate-500 dark:text-slate-400">Wapenarsenaal</span>
                </div>
            </a>

            <!-- 6. Kennisbank / FAQ -->
            <a href="{{ route('faq') }}" 
               class="p-3.5 rounded-xl bg-slate-50/90 dark:bg-slate-800/60 hover:bg-rose-50/70 dark:hover:bg-rose-950/40 border border-slate-200/80 dark:border-slate-700/70 hover:border-rose-300 dark:hover:border-rose-700/80 transition-all duration-200 group flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-cyan-100 dark:bg-cyan-900/60 text-cyan-600 dark:text-cyan-300 flex items-center justify-center text-base shrink-0 group-hover:scale-105 transition shadow-2xs">
                    <i class="fa-solid fa-circle-question"></i>
                </div>
                <div>
                    <h2 class="text-xs font-black text-slate-900 dark:text-white leading-tight">Kennisbank &amp; FAQ</h2>
                    <span class="text-[10px] text-slate-500 dark:text-slate-400">Hulp &amp; Uitleg</span>
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
