@extends('layouts.app')

@section('title', 'Toegang Ontzegd - Account Verbannen | TBS Kliniek')

@section('content')
<div class="max-w-2xl mx-auto py-6 sm:py-12 px-4">
    <div class="bg-white dark:bg-slate-900 border-2 border-rose-500/40 dark:border-rose-500/50 rounded-2xl p-6 sm:p-10 shadow-xs relative overflow-hidden text-center space-y-6">
        
        <!-- Top Security Stripe Banner -->
        <div class="absolute top-0 inset-x-0 h-2 bg-gradient-to-r from-rose-600 via-red-500 to-amber-500"></div>

        <!-- Big Ban Shield / Lock Icon with Pulse Effect -->
        <div class="relative inline-flex items-center justify-center">
            <div class="w-20 h-20 rounded-2xl bg-rose-50 dark:bg-rose-950/80 text-rose-600 dark:text-rose-400 flex items-center justify-center text-3xl shadow-inner border border-rose-200/80 dark:border-rose-800/70">
                <i class="fa-solid fa-ban"></i>
            </div>
            <span class="absolute -bottom-1 -right-1 w-7 h-7 rounded-full bg-slate-900 text-amber-400 border-2 border-white dark:border-slate-900 flex items-center justify-center text-xs shadow">
                <i class="fa-solid fa-lock"></i>
            </span>
        </div>

        <!-- Title & Subtitle -->
        <div class="space-y-2">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black uppercase tracking-wider bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border border-rose-200/80 dark:border-rose-800/70">
                <i class="fa-solid fa-triangle-exclamation"></i> Beveiligingsmaatregel Directie
            </span>
            <h1 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight">
                Toegang Ontzegd tot de Kliniek
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 max-w-lg mx-auto leading-relaxed">
                Je patiëntaccount (<strong>{{ $user->character?->name ?? $user->name }}</strong>) is door de zaalwacht of directie van TBS Kliniek uitgesloten van deelname wegens het schenden van de huisregels.
            </p>
        </div>

        <!-- Dossier Sanctie Details Box -->
        <div class="bg-rose-50/60 dark:bg-rose-950/30 border border-rose-200/80 dark:border-rose-900/50 rounded-xl p-5 text-left space-y-3 shadow-xs">
            <div class="flex items-center justify-between border-b border-rose-200/70 dark:border-rose-900/40 pb-2.5">
                <span class="text-[11px] font-black uppercase tracking-wider text-rose-900 dark:text-rose-300 flex items-center gap-1.5">
                    <i class="fa-solid fa-file-shield"></i> Dossier Sanctie Overzicht
                </span>
                <span class="text-[10px] font-mono text-rose-700 dark:text-rose-400 font-bold">
                    ID #{{ $user->id }}
                </span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                <div>
                    <span class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider block">Status Verbanning</span>
                    <span class="font-extrabold {{ $user->banned_until ? 'text-amber-600 dark:text-amber-400' : 'text-rose-600 dark:text-rose-400' }}">
                        @if($user->banned_until)
                            <i class="fa-solid fa-hourglass-half mr-1"></i> Tijdelijk uitgesloten
                        @else
                            <i class="fa-solid fa-skull-crossbones mr-1"></i> Definitief / Permanent
                        @endif
                    </span>
                </div>

                <div>
                    <span class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider block">Geldig Tot</span>
                    <span class="font-bold text-slate-800 dark:text-slate-200">
                        @if($user->banned_until)
                            {{ $user->banned_until->locale('nl')->isoFormat('D MMMM YYYY [om] HH:mm') }}
                            <span class="text-[10px] text-slate-500 dark:text-slate-400 block">({{ $user->banned_until->diffForHumans() }})</span>
                        @else
                            Levenslange isolatie (Permanent)
                        @endif
                    </span>
                </div>
            </div>

            <div class="pt-2 border-t border-rose-200/50 dark:border-rose-900/30">
                <span class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider block mb-1">Opgegeven Reden:</span>
                <div class="p-3 bg-white dark:bg-slate-800/80 rounded-xl border border-rose-200/80 dark:border-rose-900/60 text-xs font-semibold text-rose-950 dark:text-rose-200 leading-relaxed italic">
                    "{{ $user->ban_reason ?: 'Geen specifieke toelichting verstrekt door beheer.' }}"
                </div>
            </div>
        </div>

        <!-- Appeals & Help Instructions -->
        <div class="p-4 bg-slate-50/90 dark:bg-slate-800/60 rounded-xl border border-slate-200/80 dark:border-slate-700/70 text-xs text-slate-600 dark:text-slate-300 space-y-2 text-left">
            <h4 class="font-extrabold text-slate-900 dark:text-white flex items-center gap-1.5 text-xs">
                <i class="fa-solid fa-circle-question text-purple-600 dark:text-purple-400"></i> Meen je dat dit een misverstand is?
            </h4>
            <p class="text-[11px] leading-relaxed text-slate-500 dark:text-slate-400">
                Je kunt contact opnemen met de hoofdleiding van TBS Kliniek via onze officiële Discord server of per e-mail. Vermeld hierbij altijd je geregistreerde e-mailadres en spelersnaam.
            </p>
            <div class="flex flex-wrap items-center gap-3 pt-1">
                <a href="https://discord.gg/tbs" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1.5 font-bold text-[#5865F2] hover:underline text-[11px]">
                    <i class="fa-brands fa-discord"></i> Discord Appeal Server
                </a>
                <span class="text-slate-300 dark:text-slate-700">&bull;</span>
                <a href="mailto:support@tbs-kliniek.nl" class="inline-flex items-center gap-1.5 font-bold text-purple-600 dark:text-purple-400 hover:underline text-[11px]">
                    <i class="fa-solid fa-envelope"></i> support@tbs-kliniek.nl
                </a>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="pt-2 flex flex-col sm:flex-row items-center justify-center gap-3">
            <form action="{{ route('logout') }}" method="POST" class="w-full sm:w-auto">
                @csrf
                <button type="submit" class="w-full sm:w-auto px-6 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 dark:bg-rose-600 dark:hover:bg-rose-500 text-white font-black text-xs transition-all duration-150 active:scale-95 shadow-xs flex items-center justify-center gap-2 cursor-pointer">
                    <i class="fa-solid fa-right-from-bracket"></i>
                    <span>Afmelden & Uitloggen</span>
                </button>
            </form>
            <a href="https://tbs-kliniek.nl/disclaimer" class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 font-bold text-xs transition-all duration-150 active:scale-95 border border-slate-200/80 dark:border-slate-700 flex items-center justify-center gap-1.5 cursor-pointer">
                <i class="fa-solid fa-scale-balanced text-slate-500 dark:text-slate-400"></i>
                <span>Huisregels & Voorwaarden</span>
            </a>
        </div>

    </div>
</div>
@endsection
