@extends('layouts.app')

@section('title', '404 - Dwaalgang in de Kliniek | TBS Kliniek')

@section('content')
<div class="max-w-2xl mx-auto py-8 sm:py-16 px-4">
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 sm:p-12 shadow-xl relative overflow-hidden text-center space-y-6">
        
        <!-- Ambient Glowing Background Accents -->
        <div class="absolute -top-24 -left-24 w-48 h-48 bg-purple-500/10 dark:bg-purple-600/15 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-24 -right-24 w-48 h-48 bg-amber-500/10 dark:bg-amber-600/15 rounded-full blur-3xl pointer-events-none"></div>

        <!-- Animated Badge / Visual Icon -->
        <div class="relative inline-flex items-center justify-center">
            <div class="w-24 h-24 sm:w-28 sm:h-28 rounded-3xl bg-purple-50 dark:bg-purple-950/60 border border-purple-200 dark:border-purple-800/60 text-purple-600 dark:text-purple-400 flex items-center justify-center text-4xl sm:text-5xl shadow-inner group">
                <i class="fa-solid fa-map-location-dot animate-bounce"></i>
            </div>
            <span class="absolute -top-2 -right-2 bg-amber-400 text-slate-950 text-[10px] sm:text-xs font-black uppercase px-2.5 py-0.5 rounded-full shadow-md tracking-wider border-2 border-white dark:border-slate-900">
                Code 404
            </span>
        </div>

        <!-- Error Heading -->
        <div class="space-y-2">
            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-black uppercase tracking-wider bg-purple-100 dark:bg-purple-950/70 text-purple-700 dark:text-purple-300 border border-purple-200 dark:border-purple-800/50">
                <i class="fa-solid fa-compass"></i> Verdwaald in de Beveiligde Vleugel
            </div>
            <h1 class="text-3xl sm:text-4xl font-black text-slate-900 dark:text-white tracking-tight">
                Oeps! Verkeerde Afslag Genomen
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 max-w-md mx-auto leading-relaxed">
                Hé boef! Deze cel, gang of pagina bestaat niet (meer) of is hermetisch afgegrendeld door de zaalwacht. Misschien probeerde je stiekem via de luchtkanalen te ontsnappen?
            </p>
        </div>

        <!-- Quick Safe-Passage Nav Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2 text-left">
            <a href="{{ Auth::check() ? route('dashboard') : route('login') }}" 
               class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800/60 hover:bg-purple-50 dark:hover:bg-purple-950/40 border border-slate-200 dark:border-slate-700/60 hover:border-purple-300 dark:hover:border-purple-700 transition group flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-purple-100 dark:bg-purple-900/60 text-purple-600 dark:text-purple-300 flex items-center justify-center text-base shrink-0 group-hover:scale-105 transition">
                    <i class="fa-solid fa-house"></i>
                </div>
                <div>
                    <h3 class="text-xs font-extrabold text-slate-900 dark:text-white group-hover:text-purple-600 dark:group-hover:text-purple-400">
                        {{ Auth::check() ? 'Mijn Cel (Dashboard)' : 'Inloggen / Starten' }}
                    </h3>
                    <p class="text-[10px] text-slate-400">Terug naar veilig terrein</p>
                </div>
            </a>

            @if(Auth::check())
            <a href="{{ route('fight.index') }}" 
               class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800/60 hover:bg-rose-50 dark:hover:bg-rose-950/40 border border-slate-200 dark:border-slate-700/60 hover:border-rose-300 dark:hover:border-rose-700 transition group flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-rose-100 dark:bg-rose-900/60 text-rose-600 dark:text-rose-300 flex items-center justify-center text-base shrink-0 group-hover:scale-105 transition">
                    <i class="fa-solid fa-hand-fist"></i>
                </div>
                <div>
                    <h3 class="text-xs font-extrabold text-slate-900 dark:text-white group-hover:text-rose-600 dark:group-hover:text-rose-400">
                        De Luchtplaats
                    </h3>
                    <p class="text-[10px] text-slate-400">Zoek een potje knokken</p>
                </div>
            </a>
            @else
            <a href="{{ route('faq.index') }}" 
               class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800/60 hover:bg-indigo-50 dark:hover:bg-indigo-950/40 border border-slate-200 dark:border-slate-700/60 hover:border-indigo-300 dark:hover:border-indigo-700 transition group flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-indigo-100 dark:bg-indigo-900/60 text-indigo-600 dark:text-indigo-300 flex items-center justify-center text-base shrink-0 group-hover:scale-105 transition">
                    <i class="fa-solid fa-circle-question"></i>
                </div>
                <div>
                    <h3 class="text-xs font-extrabold text-slate-900 dark:text-white group-hover:text-indigo-600 dark:group-hover:text-indigo-400">
                        Kennisbank & FAQ
                    </h3>
                    <p class="text-[10px] text-slate-400">Lees de spelregels en tips</p>
                </div>
            </a>
            @endif
        </div>

        <!-- Action Buttons -->
        <div class="pt-4 flex flex-col sm:flex-row items-center justify-center gap-3 border-t border-slate-100 dark:border-slate-800">
            <button type="button" onclick="window.history.back()" 
                    class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold text-xs transition border border-slate-200 dark:border-slate-700 flex items-center justify-center gap-2 cursor-pointer">
                <i class="fa-solid fa-arrow-left"></i>
                <span>Vorige Pagina</span>
            </button>
            <a href="{{ route('help.tickets.index') }}" 
               class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-extrabold text-xs transition shadow-sm flex items-center justify-center gap-2">
                <i class="fa-solid fa-headset"></i>
                <span>Hulp Nodig? Maak een Ticket</span>
            </a>
        </div>

    </div>
</div>
@endsection
