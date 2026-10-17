@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- Top Hero Banner (Clean & Modern TBS Style) -->
    <div class="rounded-2xl shadow-xs p-6 sm:p-8 text-slate-800 relative overflow-hidden bg-white border border-slate-200">
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <div class="inline-flex items-center gap-2 bg-purple-50 text-purple-700 text-xs font-bold px-3 py-1 rounded-full border border-purple-200 mb-3">
                    <i class="fa-solid fa-vault text-purple-600"></i> MedicijnKluis & Vouchers
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900">Suikerkluis</h1>
                <p class="text-slate-500 text-xs sm:text-sm mt-1 max-w-xl leading-relaxed">
                    Kluiscodes die je scoort of koopt worden automatisch aan je account gekoppeld. Wissel ze hieronder direct in per code of kraak ze allemaal in één klik voor 100% wit bankgeld, sterren, respect en maskers!
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-2 shrink-0">
                <span class="px-3.5 py-2 rounded-xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-700/50 text-xs font-bold text-amber-800 dark:text-amber-300 flex items-center gap-1.5 shadow-2xs">
                    <i class="fa-solid fa-star text-amber-500"></i> {{ number_format($character->stars ?? 0, 0, ',', '.') }} Sterren
                </span>
                <span class="px-3.5 py-2 rounded-xl bg-purple-50 dark:bg-purple-950/40 border border-purple-200 dark:border-purple-700/50 text-xs font-bold text-purple-800 dark:text-purple-300 flex items-center gap-1.5 shadow-2xs">
                    <i class="fa-solid fa-mask text-purple-600 dark:text-purple-400"></i> {{ number_format($character->masks ?? 0, 0, ',', '.') }} Maskers
                </span>
                @if(($character->masks ?? 0) > 0)
                    <form action="{{ route('vault.sell-mask') }}" method="POST" class="inline">
                        @csrf
                        <input type="hidden" name="amount" value="1">
                        <button type="submit" 
                                title="Verkoop 1 masker direct voor €1.500 wit bankgeld"
                                class="px-3.5 py-2 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 hover:bg-emerald-100 dark:hover:bg-emerald-900/50 border border-emerald-300 dark:border-emerald-700/60 text-xs font-extrabold text-emerald-800 dark:text-emerald-300 flex items-center gap-1.5 shadow-2xs cursor-pointer transition">
                            <i class="fa-solid fa-sack-dollar text-emerald-600 dark:text-emerald-400"></i> Verkoop 1 Masker (€1.500)
                        </button>
                    </form>
                    @if(($character->masks ?? 0) > 1)
                    <form action="{{ route('vault.sell-mask') }}" method="POST" class="inline">
                        @csrf
                        <input type="hidden" name="amount" value="{{ $character->masks }}">
                        <button type="submit" 
                                title="Verkoop alle {{ $character->masks }} maskers voor €{{ number_format($character->masks * 1500, 0, ',', '.') }} bankgeld"
                                class="px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 border border-emerald-600 text-xs font-extrabold text-white flex items-center gap-1.5 shadow-xs cursor-pointer transition">
                            <i class="fa-solid fa-money-bill-transfer"></i> Verkoop Alle ({{ $character->masks }})
                        </button>
                    </form>
                    @endif
                @endif
                <a href="{{ route('marketplace.index') }}" 
                   class="px-3.5 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 dark:bg-purple-600 dark:hover:bg-purple-700 border border-slate-800 dark:border-purple-500 text-xs font-extrabold text-white flex items-center gap-2 shadow-2xs transition">
                    <i class="fa-solid fa-scale-balanced text-amber-400"></i>
                    <span>Marktplaats</span>
                </a>
            </div>
        </div>
    </div>

    <!-- MAIN TWO COLUMN GRID -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-stretch">

        <!-- Left Column: Wat kun je scoren? Card -->
        <div class="lg:col-span-4 bg-white border border-slate-200 rounded-xl p-6 shadow-xs flex flex-col justify-between">
            <div>
                <div class="flex items-center gap-2 text-xs font-extrabold text-slate-900 uppercase tracking-wider mb-5">
                    <i class="fa-solid fa-circle-info text-purple-600"></i>
                    <span>Wat kun je scoren?</span>
                </div>

                <div class="space-y-3.5">
                    <!-- Reward 1: Kluisgeld -->
                    <div class="flex items-center gap-3.5 p-3.5 rounded-xl bg-slate-50 border border-slate-100">
                        <div class="w-11 h-11 rounded-xl bg-purple-100 text-purple-600 flex items-center justify-center text-xl shrink-0">
                            <i class="fa-solid fa-sack-dollar"></i>
                        </div>
                        <div>
                            <div class="text-base font-extrabold text-slate-900">€32.078.000</div>
                            <div class="text-[10px] font-bold uppercase tracking-wide text-slate-400">Kluisgeld (Wit Bankgeld)</div>
                        </div>
                    </div>

                    <!-- Reward 2: Diamanten / Sterren -->
                    <div class="flex items-center gap-3.5 p-3.5 rounded-xl bg-slate-50 border border-slate-100">
                        <div class="w-11 h-11 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center text-xl shrink-0">
                            <i class="fa-solid fa-gem"></i>
                        </div>
                        <div>
                            <div class="text-base font-extrabold text-slate-900">20 Diamanten</div>
                            <div class="text-[10px] font-bold uppercase tracking-wide text-slate-400">Exclusieve Sterren</div>
                        </div>
                    </div>

                    <!-- Reward 3: Slay / Respect -->
                    <div class="flex items-center gap-3.5 p-3.5 rounded-xl bg-slate-50 border border-slate-100">
                        <div class="w-11 h-11 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center text-xl shrink-0">
                            <i class="fa-solid fa-fire"></i>
                        </div>
                        <div>
                            <div class="text-base font-extrabold text-slate-900">748.44</div>
                            <div class="text-[10px] font-bold uppercase tracking-wide text-slate-400">Slay & Respect</div>
                        </div>
                    </div>

                    <!-- Reward 4: Maskers -->
                    <div class="flex items-center gap-3.5 p-3.5 rounded-xl bg-slate-50 border border-slate-100">
                        <div class="w-11 h-11 rounded-xl bg-indigo-100 text-indigo-600 flex items-center justify-center text-xl shrink-0">
                            <i class="fa-solid fa-mask"></i>
                        </div>
                        <div>
                            <div class="text-base font-extrabold text-slate-900">1 Masker</div>
                            <div class="text-[10px] font-bold uppercase tracking-wide text-slate-400">Voor Zakkenrollen</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="pt-5 mt-5 border-t border-slate-100 text-xs text-slate-500 flex items-center gap-2">
                <i class="fa-solid fa-building-columns text-emerald-600"></i>
                <span>Alle gekraakte bedragen worden direct 100% wit op je bankrekening gestort.</span>
            </div>
        </div>

        <!-- Right Column: Suikerkluis Card -->
        <div class="lg:col-span-8 bg-white border border-slate-200 rounded-xl p-6 sm:p-7 shadow-xs flex flex-col justify-between space-y-5">
            <div class="space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <div class="flex items-center gap-2.5 text-base font-extrabold text-slate-900">
                        <i class="fa-solid fa-vault text-purple-600 text-lg"></i>
                        <span>Suikerkluis Kraakpaneel</span>
                    </div>
                    @if(isset($myCodes) && $myCodes->count() > 0)
                        <span class="px-3 py-1 rounded-full bg-purple-50 border border-purple-200 text-purple-700 text-xs font-bold font-mono">
                            {{ $myCodes->count() }} code(s) beschikbaar
                        </span>
                    @endif
                </div>

                <p class="text-xs text-slate-600 leading-relaxed">
                    Kluiscodes die je koopt of scoort via drops en battles worden automatisch aan je account gekoppeld. Wissel ze hieronder direct in of kraak ze allemaal in één keer!
                </p>

                @if(isset($myCodes) && $myCodes->count() > 0)
                    <!-- Active Instant Batch Crack Banner -->
                    <div class="p-5 rounded-xl bg-purple-50/70 border border-purple-200 space-y-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="text-sm font-extrabold text-purple-950 flex items-center gap-2">
                                    <i class="fa-solid fa-key text-purple-600"></i>
                                    <span>Je hebt {{ $myCodes->count() }} kluiscode(s) klaarstaan!</span>
                                </div>
                                <div class="text-xs text-purple-700 mt-0.5">
                                    Geen gedoe met overtikken: kraak ze allemaal direct in één klik voor alle prijzen.
                                </div>
                            </div>
                        </div>

                        <form action="{{ route('vault.redeem') }}" method="POST">
                            @csrf
                            <input type="hidden" name="redeem_all" value="1">
                            <button type="submit" 
                                    class="w-full py-3.5 px-6 rounded-xl bg-purple-600 hover:bg-purple-700 active:scale-[0.99] text-white font-extrabold text-xs uppercase tracking-wider transition shadow-sm cursor-pointer flex items-center justify-center gap-2">
                                <i class="fa-solid fa-unlock-keyhole"></i>
                                <span>Kraak Alle Kluiscodes ({{ $myCodes->count() }})!</span>
                            </button>
                        </form>
                    </div>
                @else
                    <!-- No Codes State Banner -->
                    <div class="p-6 rounded-xl bg-slate-50 border border-slate-200 text-center space-y-3">
                        <div class="text-xs text-slate-500">
                            Momenteel geen actieve kluiscodes op je account. Scoor codes via missies, drops of in de winkel!
                        </div>
                        <a href="{{ route('shop.index') }}" 
                           class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs uppercase tracking-wider shadow-xs transition">
                            <i class="fa-solid fa-cart-shopping"></i> Koop Kluiscodes in de Winkel
                        </a>
                    </div>
                @endif

                <!-- Optional Compact Manual Code Input (for promo codes) -->
                <details class="group pt-2" {{ !empty($prefillCode) ? 'open' : '' }}>
                    <summary class="text-xs font-bold text-slate-500 hover:text-slate-800 cursor-pointer flex items-center gap-1.5 list-none select-none">
                        <i class="fa-solid fa-chevron-right text-[10px] group-open:rotate-90 transition-transform"></i>
                        <span>Heb je een externe / voucher kluiscode? Typ hem hier in</span>
                    </summary>
                    <form action="{{ route('vault.redeem') }}" method="POST" class="mt-3 flex gap-2">
                        @csrf
                        <input type="text" name="code" value="{{ old('code', $prefillCode ?? '') }}" placeholder="Bijv. SPONSOR-1234 of KLUIS-X" 
                               class="flex-1 bg-white border border-slate-300 focus:border-purple-500 rounded-xl px-3.5 py-2 text-xs font-mono font-bold text-slate-900 placeholder-slate-400 uppercase focus:outline-none focus:ring-2 focus:ring-purple-500/20 transition">
                        <button type="submit" 
                                class="px-5 py-2 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-bold text-xs uppercase tracking-wider transition cursor-pointer">
                            Inwisselen
                        </button>
                    </form>
                </details>
            </div>

            <div class="pt-4 border-t border-slate-100 flex items-center gap-2 text-xs text-slate-500">
                <i class="fa-solid fa-shield-halved text-purple-600"></i>
                <span>Direct 100% wit geld op je bankrekening gestort zonder verlies.</span>
            </div>
        </div>

    </div>

    <!-- Mijn Kluiscodes List (Directly Clickable Cards) -->
    <div class="bg-white border border-slate-200 rounded-xl p-6 shadow-xs space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <div class="flex items-center gap-2 text-xs font-extrabold text-slate-900 uppercase tracking-wider">
                <i class="fa-solid fa-key text-purple-600"></i>
                <span>Mijn kluiscodes</span>
            </div>
            @if(isset($myCodes) && $myCodes->count() > 0)
                <div class="flex items-center gap-3">
                    <form action="{{ route('vault.redeem') }}" method="POST">
                        @csrf
                        <input type="hidden" name="redeem_all" value="1">
                        <button type="submit" class="text-xs font-bold text-purple-600 hover:text-purple-700 transition cursor-pointer flex items-center gap-1.5">
                            <i class="fa-solid fa-bolt"></i>
                            <span>Kraak alle ({{ $myCodes->count() }})</span>
                        </button>
                    </form>
                    <a href="{{ route('marketplace.index') }}" class="text-xs font-bold text-slate-500 hover:text-purple-600 transition flex items-center gap-1">
                        <i class="fa-solid fa-scale-balanced"></i>
                        <span>Aanbieden op Marktplaats &rarr;</span>
                    </a>
                </div>
            @endif
        </div>

        @if(isset($myCodes) && $myCodes->count() > 0)
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                @foreach($myCodes as $c)
                    <div class="p-4 rounded-xl bg-slate-50 hover:bg-white border border-slate-200 hover:border-purple-300 transition shadow-xs flex flex-col justify-between gap-3">
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <span class="px-2.5 py-0.5 rounded-lg bg-purple-100 text-purple-700 font-mono text-xs font-extrabold tracking-wider inline-flex items-center gap-1.5">
                                    <i class="fa-solid fa-key text-purple-600 text-[10px]"></i>
                                    <span>{{ $c->code }}</span>
                                </span>
                                <span class="text-[10px] text-slate-400 font-bold">Klaar</span>
                            </div>
                            <div class="text-xs space-y-0.5 pt-1">
                                <div class="font-extrabold text-emerald-700">
                                    +€{{ number_format($c->reward_cash, 0, ',', '.') }} wit bankgeld
                                </div>
                                <div class="text-[11px] text-slate-500 font-medium">
                                    +{{ $c->reward_stars }} Sterren &bull; +1 Masker &bull; +5 Respect
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-2">
                            <form action="{{ route('vault.redeem') }}" method="POST">
                                @csrf
                                <input type="hidden" name="code_id" value="{{ $c->id }}">
                                <button type="submit" 
                                        class="w-full py-2 px-2.5 rounded-lg bg-slate-900 hover:bg-slate-800 text-white text-[11px] font-bold uppercase tracking-wider transition flex items-center justify-center gap-1 shadow-xs cursor-pointer"
                                        title="Kraak de kluis met deze code">
                                    <i class="fa-solid fa-unlock"></i>
                                    <span>Kraken</span>
                                </button>
                            </form>
                            <form action="{{ route('vault.sell-code') }}" method="POST">
                                @csrf
                                <input type="hidden" name="code_id" value="{{ $c->id }}">
                                <button type="submit" 
                                        class="w-full py-2 px-2.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-[11px] font-bold uppercase tracking-wider transition flex items-center justify-center gap-1 shadow-xs cursor-pointer"
                                        title="Verkoop deze kluiscode direct aan de opkoper voor €3.500 wit bankgeld">
                                    <i class="fa-solid fa-sack-dollar"></i>
                                    <span>Verkoop (€3.5k)</span>
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <!-- Empty State matching site layout -->
            <div class="py-10 text-center space-y-4">
                <p class="text-sm font-bold text-slate-600">
                    Je hebt 0 kluiscodes klaar voor actie, ouleh.
                </p>
                <div>
                    <a href="{{ route('shop.index') }}" 
                       class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-extrabold text-xs uppercase tracking-wider transition shadow-xs">
                        <i class="fa-solid fa-cart-shopping"></i>
                        <span>KOOP KLUISCODES</span>
                    </a>
                </div>
            </div>
        @endif
    </div>

    <!-- Bottom Podium Ranglijst (Top 10 Avatars, Clean TBS Style) -->
    <div class="bg-white border border-slate-200 rounded-xl p-6 shadow-xs space-y-5">
        <div class="flex items-center gap-2 text-xs font-extrabold uppercase tracking-wider text-slate-900">
            <i class="fa-solid fa-trophy text-amber-500"></i>
            <span>RANGLIJST TOP 10</span>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-5 lg:grid-cols-10 gap-3 pt-2">
            @foreach($topRankings ?? [] as $index => $player)
            <div class="flex flex-col items-center text-center group">
                <div class="relative mb-2">
                    <img src="{{ $player->avatar_url }}" alt="{{ $player->name }}" 
                         class="w-14 h-14 sm:w-16 sm:h-16 rounded-full object-cover border-2 {{ $index === 0 ? 'border-amber-400 ring-2 ring-amber-400/30' : ($index === 1 ? 'border-purple-400' : 'border-slate-200') }} group-hover:scale-105 transition-transform shadow-xs">
                    
                    <!-- Rank Badge Number -->
                    <span class="absolute -top-1 -left-1 w-5 h-5 rounded-full {{ $index === 0 ? 'bg-amber-400 text-slate-950 font-black' : ($index < 3 ? 'bg-purple-600 text-white font-black' : 'bg-slate-800 text-white font-bold') }} text-[10px] flex items-center justify-center shadow-xs">
                        {{ $index + 1 }}
                    </span>
                </div>

                <a href="{{ route('profile.show', $player->name) }}" class="text-xs font-bold text-slate-900 group-hover:text-purple-600 transition truncate w-full block">
                    {{ $player->name }}
                </a>
                <span class="text-[10px] font-bold text-slate-400">
                    {{ $player->respect >= 1000000 ? number_format($player->respect / 1000000, 2) . 'M' : ($player->respect >= 1000 ? number_format($player->respect / 1000, 1) . 'K' : $player->respect) }}
                </span>
            </div>
            @endforeach
        </div>
    </div>

</div>
@endsection
