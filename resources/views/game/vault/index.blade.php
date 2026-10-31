@extends('layouts.app')

@section('title', 'Medicijnkluis & Vouchers - TBS Kliniek')

@section('content')
<div class="space-y-6">

    <!-- Top Forensic Hero Banner -->
    <div class="rounded-2xl shadow-xs p-5 sm:p-7 text-slate-800 dark:text-slate-100 relative overflow-hidden bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800">
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-5">
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-2xl bg-purple-100 dark:bg-purple-950/70 border border-purple-200 dark:border-purple-800 text-purple-600 dark:text-purple-400 flex items-center justify-center text-xl sm:text-2xl shrink-0 shadow-xs">
                    <i class="fa-solid fa-vault"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2 mb-1 flex-wrap">
                        <span class="text-[9px] font-black uppercase tracking-wider text-purple-700 dark:text-purple-300 bg-purple-50 dark:bg-purple-950/80 px-2.5 py-0.5 rounded-md border border-purple-200 dark:border-purple-800">
                            Beveiligd Domein
                        </span>
                        <span class="text-[9px] font-extrabold text-emerald-700 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/80 px-2.5 py-0.5 rounded-md border border-emerald-200 dark:border-emerald-800">
                            100% Wit Bankgeld
                        </span>
                    </div>
                    <h1 class="text-xl sm:text-2xl md:text-3xl font-black tracking-tight text-slate-900 dark:text-white">
                        Medicijnkluis & Vouchers
                    </h1>
                    <p class="text-slate-500 dark:text-slate-400 text-xs sm:text-sm mt-0.5 max-w-xl leading-relaxed">
                        Verzilver gevonden en gekochte kluiscodes voor gegarandeerd wit bankgeld, sterren en respect. Beheer je voorraad maskers voor operaties of verkoop ze direct aan de heler.
                    </p>
                </div>
            </div>

            <!-- Vitals Pills -->
            <div class="grid grid-cols-2 sm:grid-cols-4 md:flex items-center gap-2 shrink-0">
                <div class="bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800/60 rounded-xl px-3 py-1.5 text-center">
                    <span class="text-[9px] font-black uppercase tracking-wider text-amber-700 dark:text-amber-400 block">Sterren</span>
                    <span class="text-xs sm:text-sm font-black text-amber-800 dark:text-amber-300 font-mono flex items-center justify-center gap-1">
                        <i class="fa-solid fa-star text-amber-500 text-[10px]"></i> {{ number_format($character->stars ?? 0, 0, ',', '.') }}
                    </span>
                </div>
                <div class="bg-purple-50 dark:bg-purple-950/40 border border-purple-200 dark:border-purple-800/60 rounded-xl px-3 py-1.5 text-center">
                    <span class="text-[9px] font-black uppercase tracking-wider text-purple-700 dark:text-purple-400 block">Maskers</span>
                    <span class="text-xs sm:text-sm font-black text-purple-800 dark:text-purple-300 font-mono flex items-center justify-center gap-1">
                        <i class="fa-solid fa-mask text-purple-600 dark:text-purple-400 text-[10px]"></i> {{ number_format($character->masks ?? 0, 0, ',', '.') }}
                    </span>
                </div>
                <div class="bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/60 rounded-xl px-3 py-1.5 text-center">
                    <span class="text-[9px] font-black uppercase tracking-wider text-emerald-700 dark:text-emerald-400 block">Kluiscodes</span>
                    <span class="text-xs sm:text-sm font-black text-emerald-800 dark:text-emerald-300 font-mono flex items-center justify-center gap-1">
                        <i class="fa-solid fa-key text-emerald-600 dark:text-emerald-400 text-[10px]"></i> {{ isset($myCodes) ? $myCodes->count() : 0 }}
                    </span>
                </div>
                <a href="{{ route('marketplace.index') }}" 
                   class="bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 rounded-xl px-3 py-1.5 text-center flex flex-col justify-center items-center transition-all duration-150 active:scale-95 border border-slate-200/80 dark:border-slate-700">
                    <span class="text-[9px] font-black uppercase tracking-wider text-slate-500 dark:text-slate-400">Markt</span>
                    <span class="text-xs font-black text-purple-700 dark:text-purple-300 flex items-center gap-1">
                        <i class="fa-solid fa-scale-balanced text-[10px]"></i> Handel
                    </span>
                </a>
            </div>
        </div>
    </div>

    <!-- Controlepunt Banner (Kliniek Thema) -->
    @include('game.control_points.banner', ['slug' => 'medicijnkluis'])

    <!-- TWO COLUMN OPERATIONAL GRID -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

        <!-- ========================================================================= -->
        <!-- LEFT: MASKERS & VERMOMMINGEN BEHEER                                       -->
        <!-- ========================================================================= -->
        <div class="lg:col-span-5 bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-5 sm:p-6 shadow-xs space-y-5">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-purple-100 dark:bg-purple-950/70 text-purple-600 dark:text-purple-400 flex items-center justify-center text-sm font-black">
                        <i class="fa-solid fa-mask"></i>
                    </div>
                    <div>
                        <h2 class="text-xs sm:text-sm font-black uppercase tracking-wide text-slate-900 dark:text-white">
                            Maskers & Vermommingen
                        </h2>
                        <span class="text-[10px] text-slate-400 font-bold">Tactische Identiteitsbescherming</span>
                    </div>
                </div>
                <span class="px-2.5 py-1 rounded-lg bg-purple-50 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 font-mono text-xs font-black border border-purple-200 dark:border-purple-800">
                    {{ number_format($character->masks ?? 0, 0, ',', '.') }} op zak
                </span>
            </div>

            <!-- Maskers Info Cards -->
            <div class="space-y-3">
                <div class="p-3.5 rounded-xl bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70 flex items-start gap-3">
                    <div class="w-9 h-9 rounded-xl bg-purple-100 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400 flex items-center justify-center text-sm shrink-0 mt-0.5">
                        <i class="fa-solid fa-user-secret"></i>
                    </div>
                    <div class="text-xs">
                        <h4 class="font-extrabold text-slate-900 dark:text-white">Identiteitsbescherming</h4>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5 leading-snug">
                            Maskers beschermen je identiteit tijdens acties en zijn vereist voor zware <strong>Bende Heists</strong> en risicovolle operaties.
                        </p>
                    </div>
                </div>

                <div class="p-3.5 rounded-xl bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70 flex items-start gap-3">
                    <div class="w-9 h-9 rounded-xl bg-emerald-100 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-sm shrink-0 mt-0.5">
                        <i class="fa-solid fa-sack-dollar"></i>
                    </div>
                    <div class="text-xs flex-1">
                        <div class="flex items-center justify-between">
                            <h4 class="font-extrabold text-slate-900 dark:text-white">Opkoper Waarde</h4>
                            <span class="text-[11px] font-black text-emerald-600 dark:text-emerald-400 font-mono">€1.500 / stuk</span>
                        </div>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5 leading-snug">
                            Heb je overtollige maskers? De heler in de kliniek koopt ze direct op voor gegarandeerd wit bankgeld.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Maskers Waarde Calculator & Actieknoppen -->
            <div class="p-4 rounded-xl bg-emerald-50/70 dark:bg-slate-800/60 border border-emerald-200/80 dark:border-emerald-800/70 space-y-3">
                <div class="flex items-center justify-between text-xs">
                    <span class="font-bold text-slate-600 dark:text-slate-300">Totale Handelswaarde:</span>
                    <span class="font-black text-emerald-700 dark:text-emerald-400 text-sm font-mono">
                        €{{ number_format(($character->masks ?? 0) * 1500, 0, ',', '.') }} Bank
                    </span>
                </div>

                @if(($character->masks ?? 0) > 0)
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 pt-1">
                        <form action="{{ route('vault.sell-mask') }}" method="POST">
                            @csrf
                            <input type="hidden" name="amount" value="1">
                            <button type="submit" 
                                    @disabled($character->isImprisoned() || $character->isComatose())
                                    class="w-full py-2.5 px-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 dark:bg-emerald-600 dark:hover:bg-emerald-500 active:scale-95 text-white font-black text-xs transition-all duration-150 shadow-xs cursor-pointer flex items-center justify-center gap-1.5 disabled:opacity-50 disabled:cursor-not-allowed">
                                <i class="fa-solid fa-sack-dollar text-[11px]"></i>
                                <span>Verkoop 1 (€1.500)</span>
                            </button>
                        </form>

                        @if(($character->masks ?? 0) > 1)
                        <form action="{{ route('vault.sell-mask') }}" method="POST">
                            @csrf
                            <input type="hidden" name="amount" value="{{ $character->masks }}">
                            <button type="submit" 
                                    @disabled($character->isImprisoned() || $character->isComatose())
                                    class="w-full py-2.5 px-3 rounded-xl bg-emerald-700 hover:bg-emerald-800 dark:bg-emerald-600 dark:hover:bg-emerald-500 active:scale-95 text-white font-black text-xs transition-all duration-150 shadow-xs cursor-pointer flex items-center justify-center gap-1.5 disabled:opacity-50 disabled:cursor-not-allowed">
                                <i class="fa-solid fa-money-bill-transfer text-[11px]"></i>
                                <span>Verkoop Alle ({{ $character->masks }})</span>
                            </button>
                        </form>
                        @endif
                    </div>
                @else
                    <div class="text-center py-2">
                        <span class="text-[11px] font-bold text-slate-400 dark:text-slate-500">
                            Geen maskers op zak om te verkopen.
                        </span>
                    </div>
                @endif
            </div>

            <!-- Snelle Links voor Maskers -->
            <div class="grid grid-cols-2 gap-2 pt-2 border-t border-slate-100 dark:border-slate-800">
                <a href="{{ route('shop.index', ['tab' => 'items']) }}" 
                   class="px-3 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 border border-slate-200/80 dark:border-slate-700 text-slate-700 dark:text-slate-300 font-extrabold text-[11px] text-center transition-all duration-150 active:scale-95 flex items-center justify-center gap-1.5">
                    <i class="fa-solid fa-cart-shopping text-purple-600 dark:text-purple-400"></i>
                    <span>Winkel Maskers</span>
                </a>
                <a href="{{ route('gangs.index') }}" 
                   class="px-3 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 border border-slate-200/80 dark:border-slate-700 text-slate-700 dark:text-slate-300 font-extrabold text-[11px] text-center transition-all duration-150 active:scale-95 flex items-center justify-center gap-1.5">
                    <i class="fa-solid fa-users-viewfinder text-amber-500"></i>
                    <span>Doneer aan Bende</span>
                </a>
            </div>
        </div>

        <!-- ========================================================================= -->
        <!-- RIGHT: KLUISCODE INWISSEL TERMINAL                                         -->
        <!-- ========================================================================= -->
        <div class="lg:col-span-7 bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-5 sm:p-6 shadow-xs space-y-5">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-emerald-100 dark:bg-emerald-950/70 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-sm font-black">
                        <i class="fa-solid fa-key"></i>
                    </div>
                    <div>
                        <h2 class="text-xs sm:text-sm font-black uppercase tracking-wide text-slate-900 dark:text-white">
                            Kluiscode Decoder Terminal
                        </h2>
                        <span class="text-[10px] text-slate-400 font-bold">Verzilver Geheime Vouchers & Prijzen</span>
                    </div>
                </div>

                @if(isset($myCodes) && $myCodes->count() > 0)
                    <span class="px-2.5 py-1 rounded-lg bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 font-mono text-xs font-black border border-emerald-200/80 dark:border-emerald-800/70">
                        {{ $myCodes->count() }} klaar
                    </span>
                @endif
            </div>

            <!-- Batch Redeem Banner (Indien Codes Beschikbaar) -->
            @if(isset($myCodes) && $myCodes->count() > 0)
                <div class="p-4 sm:p-5 rounded-2xl bg-purple-50/80 dark:bg-slate-800/60 border border-purple-200/80 dark:border-purple-800/70 space-y-3">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                        <div>
                            <div class="text-sm font-black text-purple-950 dark:text-purple-200 flex items-center gap-2">
                                <i class="fa-solid fa-unlock-keyhole text-purple-600 dark:text-purple-400"></i>
                                <span>{{ $myCodes->count() }} Kluiscode Voucher(s) Klaar voor Verzilvering</span>
                            </div>
                            <div class="text-[11px] text-purple-700 dark:text-purple-400 mt-0.5">
                                Verzilver al je codes direct in één snelle actie zonder handmatig overtypen.
                            </div>
                        </div>
                    </div>

                    <form action="{{ route('vault.redeem') }}" method="POST">
                        @csrf
                        <input type="hidden" name="redeem_all" value="1">
                        <button type="submit" 
                                @disabled($character->isImprisoned() || $character->isComatose())
                                class="w-full py-3.5 px-5 rounded-xl bg-purple-600 hover:bg-purple-700 dark:bg-purple-600 dark:hover:bg-purple-500 active:scale-95 text-white font-black text-xs uppercase tracking-wider transition-all duration-150 shadow-xs cursor-pointer flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                            <i class="fa-solid fa-bolt"></i>
                            <span>Kraak & Verzilver Alle Codes ({{ $myCodes->count() }})</span>
                        </button>
                    </form>
                </div>
            @endif

            <!-- Handmatige Code Invoer Terminal -->
            <div class="p-4 rounded-xl bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70 space-y-3">
                <div class="text-xs font-extrabold text-slate-800 dark:text-slate-200 flex items-center gap-2">
                    <i class="fa-solid fa-terminal text-purple-600 dark:text-purple-400"></i>
                    <span>Voer Handmatig een Kluiscode of Promotiecode in</span>
                </div>

                <form action="{{ route('vault.redeem') }}" method="POST" class="flex flex-col sm:flex-row gap-2">
                    @csrf
                    <input type="text" 
                           name="code" 
                           value="{{ old('code', $prefillCode ?? '') }}" 
                           placeholder="BIJV. KLUIS-L5-XXXX OF SPONSOR-XXX" 
                           class="flex-1 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 focus:border-purple-500 dark:focus:border-purple-500 rounded-xl px-4 py-2.5 text-xs font-mono font-black text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 uppercase focus:outline-none focus:ring-2 focus:ring-purple-500/20 transition"
                           required>
                    <button type="submit" 
                            @disabled($character->isImprisoned() || $character->isComatose())
                            class="px-6 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-700 dark:bg-purple-600 dark:hover:bg-purple-500 active:scale-95 text-white font-black text-xs uppercase tracking-wider transition-all duration-150 cursor-pointer flex items-center justify-center gap-1.5 shrink-0 shadow-xs disabled:opacity-50 disabled:cursor-not-allowed">
                        <i class="fa-solid fa-check"></i>
                        <span>Verzilveren</span>
                    </button>
                </form>
            </div>

            <!-- Sterren Booster -->
            <div class="p-4 rounded-xl bg-amber-50/70 dark:bg-slate-800/60 border border-amber-200/80 dark:border-amber-800/60 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-amber-500 text-slate-950 flex items-center justify-center text-sm font-black shrink-0 shadow-xs">
                        <i class="fa-solid fa-star"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h4 class="text-xs font-black text-slate-900 dark:text-white">Extra Kluiscodes &amp; Tokens Aanschaffen</h4>
                            <span class="text-[9px] font-black uppercase text-amber-700 dark:text-amber-400 bg-amber-100 dark:bg-amber-900/60 px-2 py-0.5 rounded">
                                Direct Beschikbaar
                            </span>
                        </div>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">
                            Koop direct extra kluiscodes, tokens en megabuit met Sterren ⭐ in de Sterren Shop!
                        </p>
                    </div>
                </div>

                <a href="{{ route('shop.index') }}" 
                   class="btn-star-gold px-5 py-2.5 rounded-xl active:scale-95 text-slate-900 font-black text-xs transition-all duration-150 shadow-xs flex items-center justify-center gap-1.5 shrink-0 cursor-pointer">
                    <i class="fa-solid fa-star text-[10px]"></i>
                    <span>Naar Sterren Shop</span>
                </a>
            </div>
        </div>

    </div>

    <!-- ========================================================================= -->
    <!-- MIJN KLUISCODES DOSSIER GRID                                              -->
    <!-- ========================================================================= -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-5 sm:p-6 shadow-xs space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-folder-closed text-purple-600 dark:text-purple-400 text-sm"></i>
                <h3 class="text-xs sm:text-sm font-black uppercase tracking-wide text-slate-900 dark:text-white">
                    Mijn Kluiscodes Inventaris
                </h3>
            </div>
            
            @if(isset($myCodes) && $myCodes->count() > 0)
                <div class="flex items-center gap-3">
                    <a href="{{ route('marketplace.index') }}" class="text-xs font-bold text-slate-500 dark:text-slate-400 hover:text-purple-600 dark:hover:text-purple-400 transition flex items-center gap-1">
                        <i class="fa-solid fa-scale-balanced text-[11px]"></i>
                        <span>Aanbieden op Marktplaats &rarr;</span>
                    </a>
                </div>
            @endif
        </div>

        @if(isset($myCodes) && $myCodes->count() > 0)
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                @foreach($myCodes as $c)
                    <div class="p-4 rounded-xl bg-slate-50/90 dark:bg-slate-800/60 hover:bg-white dark:hover:bg-slate-800/90 border border-slate-200/80 dark:border-slate-700/70 hover:border-purple-300 dark:hover:border-purple-700/80 transition-all duration-200 shadow-xs flex flex-col justify-between gap-3">
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <span class="px-2.5 py-0.5 rounded-md bg-purple-100 dark:bg-purple-950/70 text-purple-700 dark:text-purple-300 font-mono text-xs font-black tracking-wider inline-flex items-center gap-1.5">
                                    <i class="fa-solid fa-key text-[10px]"></i>
                                    <span>{{ $c->code }}</span>
                                </span>
                                <span class="text-[10px] text-emerald-600 dark:text-emerald-400 font-extrabold uppercase">
                                    <i class="fa-solid fa-circle-check text-[9px]"></i> Actief
                                </span>
                            </div>

                            <div class="text-xs space-y-1 pt-1">
                                <div class="font-black text-emerald-700 dark:text-emerald-400 text-sm font-mono">
                                    +€{{ number_format($c->reward_cash, 0, ',', '.') }} Bankgeld
                                </div>
                                <div class="text-[11px] text-slate-500 dark:text-slate-400 font-bold flex items-center gap-2 flex-wrap">
                                    <span>+{{ $c->reward_stars }} ⭐ Sterren</span>
                                    <span>&bull;</span>
                                    <span>+1 🎭 Masker</span>
                                    <span>&bull;</span>
                                    <span>+5 ⚡ Respect</span>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-2 pt-2 border-t border-slate-200/70 dark:border-slate-700/60">
                            <form action="{{ route('vault.redeem') }}" method="POST">
                                @csrf
                                <input type="hidden" name="code_id" value="{{ $c->id }}">
                                <button type="submit" 
                                        @disabled($character->isImprisoned() || $character->isComatose())
                                        class="w-full py-2 px-2.5 rounded-xl bg-purple-600 hover:bg-purple-700 dark:bg-purple-600 dark:hover:bg-purple-500 active:scale-95 text-white text-[11px] font-black uppercase tracking-wider transition-all duration-150 flex items-center justify-center gap-1 shadow-xs cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed"
                                        title="Verzilver deze kluiscode direct">
                                    <i class="fa-solid fa-unlock-keyhole text-[10px]"></i>
                                    <span>Verzilveren</span>
                                </button>
                            </form>

                            <form action="{{ route('vault.sell-code') }}" method="POST">
                                @csrf
                                <input type="hidden" name="code_id" value="{{ $c->id }}">
                                <button type="submit" 
                                        @disabled($character->isImprisoned() || $character->isComatose())
                                        class="w-full py-2 px-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 dark:bg-emerald-600 dark:hover:bg-emerald-500 active:scale-95 text-white text-[11px] font-black uppercase tracking-wider transition-all duration-150 flex items-center justify-center gap-1 shadow-xs cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed"
                                        title="Verkoop deze code aan de opkoper voor €3.500 wit bankgeld">
                                    <i class="fa-solid fa-sack-dollar text-[10px]"></i>
                                    <span>Verkoop (€3.5k)</span>
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <!-- Lege status -->
            <div class="py-10 text-center space-y-3">
                <div class="w-12 h-12 rounded-2xl bg-slate-100 dark:bg-slate-800 text-slate-400 flex items-center justify-center mx-auto text-xl">
                    <i class="fa-solid fa-key"></i>
                </div>
                <div class="text-xs font-bold text-slate-500 dark:text-slate-400 max-w-sm mx-auto">
                    Je hebt momenteel geen kluiscodes in je bezit. Verdien codes door te levelen, via missies, random drops of in de winkel!
                </div>
                <div>
                    <a href="{{ route('shop.index') }}" 
                       class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-700 active:scale-95 text-white font-black text-xs uppercase tracking-wider transition shadow-xs">
                        <i class="fa-solid fa-cart-shopping"></i>
                        <span>Bekijk Kluiscodes in Winkel</span>
                    </a>
                </div>
            </div>
        @endif
    </div>

    <!-- ========================================================================= -->
    <!-- TOP 10 ERELIJST (TBS KLINIEK STYLE)                                      -->
    <!-- ========================================================================= -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-5 sm:p-6 shadow-xs space-y-4">
        <div class="flex items-center gap-2 text-xs font-black uppercase tracking-wider text-slate-900 dark:text-white border-b border-slate-100 dark:border-slate-800 pb-3">
            <i class="fa-solid fa-trophy text-amber-500"></i>
            <span>TBS Kliniek Erelijst (Top 10 Respect)</span>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-5 lg:grid-cols-10 gap-3 pt-1">
            @foreach($topRankings ?? [] as $index => $player)
            <div class="flex flex-col items-center text-center group">
                <div class="relative mb-2">
                    <img src="{{ $player->avatar_url }}" alt="{{ $player->name }}" 
                         onerror="this.onerror=null; this.src='{{ asset('assets/characters/henkie.jpg') }}';"
                         class="w-12 h-12 sm:w-14 sm:h-14 rounded-full object-cover border-2 {{ $index === 0 ? 'border-amber-400 ring-2 ring-amber-400/30' : ($index === 1 ? 'border-purple-400' : 'border-slate-200 dark:border-slate-700') }} group-hover:scale-105 transition-transform shadow-2xs">
                    
                    <span class="absolute -top-1 -left-1 w-5 h-5 rounded-full {{ $index === 0 ? 'bg-amber-400 text-slate-950 font-black' : ($index < 3 ? 'bg-purple-600 text-white font-black' : 'bg-slate-800 text-white font-bold') }} text-[10px] flex items-center justify-center shadow-xs">
                        {{ $index + 1 }}
                    </span>
                </div>

                <a href="{{ route('profile.show', $player->name) }}" class="text-[11px] font-extrabold text-slate-900 dark:text-white group-hover:text-purple-600 dark:group-hover:text-purple-400 transition truncate w-full block">
                    {{ $player->name }}
                </a>
                <span class="text-[10px] font-bold text-slate-400 font-mono">
                    {{ $player->respect >= 1000000 ? number_format($player->respect / 1000000, 2) . 'M' : ($player->respect >= 1000 ? number_format($player->respect / 1000, 1) . 'K' : $player->respect) }}
                </span>
            </div>
            @endforeach
        </div>
    </div>

</div>
@endsection

