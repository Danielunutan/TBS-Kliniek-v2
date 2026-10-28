@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- Top Hero Banner: Seizoen 1 Battle Pass (Licht & Donker Modus Perfectie) -->
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-purple-900 via-indigo-950 to-slate-950 p-6 sm:p-8 text-white border-2 border-amber-400/40 shadow-xl">
        <div class="absolute -right-16 -top-16 w-64 h-64 bg-amber-500/15 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -left-16 -bottom-16 w-64 h-64 bg-purple-500/20 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            <div class="space-y-2">
                <div class="inline-flex items-center gap-2 bg-amber-400/20 text-amber-300 text-xs font-black px-3.5 py-1.5 rounded-full border border-amber-400/40 shadow-2xs">
                    <i class="fa-solid fa-trophy text-amber-400"></i>
                    <span>Seizoen 1 • Officiële Battle Pass</span>
                </div>
                <h1 class="text-2xl sm:text-4xl font-black tracking-tight text-white flex items-center gap-2.5 flex-wrap">
                    <span>TBS Kliniek Seizoenspas</span>
                    <span class="text-xs sm:text-sm font-extrabold px-3 py-1 rounded-xl bg-purple-600/60 border border-purple-400/50 text-purple-200">
                        20 Niveaus • 4 Sporen
                    </span>
                </h1>
                <p class="text-slate-300 text-xs sm:text-sm max-w-2xl leading-relaxed">
                    Stijg in level door misdaden te plegen, bendes te verslaan en te trainen! Claim gratis geld, kogels en koffers, of ontgrendel de 3 VIP sporen voor extreme syndicaat beloningen en unieke kisten.
                </p>

                <!-- Current Status Pill -->
                <div class="pt-2 flex flex-wrap items-center gap-2">
                    <span class="text-xs font-bold text-slate-300">Jouw Status:</span>
                    <span class="px-2.5 py-1 rounded-lg bg-white/10 border border-white/20 text-white text-xs font-mono font-bold">
                        Level {{ $character->level }} ({{ number_format($character->respect, 0, ',', '.') }} Respect)
                    </span>
                    @if($isVip)
                        @php $badge = $character->vip_badge; @endphp
                        @if($badge)
                            <span class="{{ $badge['class'] }} text-xs font-black uppercase px-2.5 py-1 rounded-lg border flex items-center gap-1.5 shadow-sm">
                                <i class="fa-solid {{ $badge['icon'] }}"></i> {{ $badge['label'] }}
                            </span>
                        @endif
                    @else
                        <span class="px-2.5 py-1 rounded-lg bg-slate-800 border border-slate-700 text-slate-300 text-xs font-bold flex items-center gap-1.5">
                            <i class="fa-solid fa-user"></i> Gratis Deelnemer
                        </span>
                        <a href="{{ route('vip.index') }}" class="px-3 py-1 rounded-lg bg-amber-500 hover:bg-amber-400 text-slate-950 font-black text-xs transition shadow-sm flex items-center gap-1 cursor-pointer">
                            <i class="fa-solid fa-crown text-[10px]"></i> Word VIP
                        </a>
                    @endif
                </div>
            </div>

            <!-- Claim All Action Card -->
            <div class="bg-white/10 dark:bg-slate-900/80 backdrop-blur-md border border-white/15 dark:border-slate-800 rounded-2xl p-5 shrink-0 flex flex-col justify-between space-y-3 min-w-[260px]">
                <div>
                    <span class="text-[10px] font-black uppercase tracking-wider text-amber-300 block">Klaar om te Claimen</span>
                    <div class="text-2xl font-black text-white mt-0.5">
                        {{ $claimableCount }} {{ $claimableCount === 1 ? 'Beloning' : 'Beloningen' }}
                    </div>
                    <p class="text-[11px] text-slate-300 mt-1">
                        Ontgrendelde prijzen wachten op jouw claim.
                    </p>
                </div>

                @if($claimableCount > 0)
                <form action="{{ route('battlepass.claim_all') }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full py-2.5 px-4 rounded-xl bg-gradient-to-r from-amber-400 to-amber-500 hover:from-amber-300 hover:to-amber-400 text-slate-950 font-black text-xs uppercase tracking-wider transition shadow-md flex items-center justify-center gap-2 cursor-pointer active:scale-95 animate-pulse">
                        <i class="fa-solid fa-gifts"></i>
                        <span>Claim Alles In Eén Keer!</span>
                    </button>
                </form>
                @else
                <button type="button" disabled class="w-full py-2.5 px-4 rounded-xl bg-white/10 text-slate-400 font-bold text-xs uppercase tracking-wider cursor-not-allowed">
                    <i class="fa-solid fa-check mr-1"></i> Alles Geclaimd
                </button>
                @endif
            </div>
        </div>
    </div>

    <!-- 4 Sporen Uitleg Balk (Informatief Overzicht) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5 text-xs">
        <!-- Spoor 1: Gratis -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 space-y-2 shadow-2xs">
            <div class="flex items-center justify-between">
                <span class="px-2 py-0.5 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-[10px] font-black uppercase border border-slate-200 dark:border-slate-700">
                    Spoor 1
                </span>
                <i class="fa-solid fa-unlock-keyhole text-slate-400"></i>
            </div>
            <h4 class="font-black text-slate-900 dark:text-white text-sm flex items-center gap-1.5">
                <i class="fa-solid fa-user text-slate-500"></i> Gratis Spoor
            </h4>
            <p class="text-slate-500 dark:text-slate-400 leading-snug text-[11px]">
                Toegankelijk voor elke patiënt. Bevat cash, kogels, respect, koffers en energie herstel.
            </p>
        </div>

        <!-- Spoor 2: Tier 1 VIP -->
        <div class="bg-white dark:bg-slate-900 border border-blue-200 dark:border-blue-900/60 rounded-2xl p-4 space-y-2 shadow-2xs">
            <div class="flex items-center justify-between">
                <span class="px-2 py-0.5 rounded-full bg-blue-100 dark:bg-blue-950 text-blue-700 dark:text-blue-300 text-[10px] font-black uppercase border border-blue-200 dark:border-blue-800">
                    Spoor 2
                </span>
                @if($isVip && $supporterTier >= 1)
                    <span class="text-[10px] font-black text-emerald-600 dark:text-emerald-400">✓ Actief</span>
                @else
                    <i class="fa-solid fa-lock text-blue-400"></i>
                @endif
            </div>
            <h4 class="font-black text-blue-700 dark:text-blue-400 text-sm flex items-center gap-1.5">
                <i class="fa-solid fa-star text-blue-500"></i> Tier 1 V.I.P. Pass
            </h4>
            <p class="text-slate-500 dark:text-slate-400 leading-snug text-[11px]">
                Vereist TBS V.I.P. Lid status. Extra cash, sterren, VIP punten en dubbele koffers.
            </p>
        </div>

        <!-- Spoor 3: Tier 2 Elite -->
        <div class="bg-white dark:bg-slate-900 border border-emerald-200 dark:border-emerald-900/60 rounded-2xl p-4 space-y-2 shadow-2xs">
            <div class="flex items-center justify-between">
                <span class="px-2 py-0.5 rounded-full bg-emerald-100 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300 text-[10px] font-black uppercase border border-emerald-200 dark:border-emerald-800">
                    Spoor 3
                </span>
                @if($isVip && $supporterTier >= 2)
                    <span class="text-[10px] font-black text-emerald-600 dark:text-emerald-400">✓ Actief</span>
                @else
                    <i class="fa-solid fa-lock text-emerald-400"></i>
                @endif
            </div>
            <h4 class="font-black text-emerald-700 dark:text-emerald-400 text-sm flex items-center gap-1.5">
                <i class="fa-solid fa-gem text-emerald-500"></i> Tier 2 Elite Pass
            </h4>
            <p class="text-slate-500 dark:text-slate-400 leading-snug text-[11px]">
                Vereist Gangster Elite status. Royale cash injecties, veel sterren, maskers en XXL Kisten.
            </p>
        </div>

        <!-- Spoor 4: Tier 3 Syndicaat Baas -->
        <div class="bg-white dark:bg-slate-900 border border-purple-200 dark:border-purple-900/60 rounded-2xl p-4 space-y-2 shadow-2xs">
            <div class="flex items-center justify-between">
                <span class="px-2 py-0.5 rounded-full bg-purple-100 dark:bg-purple-950 text-purple-700 dark:text-purple-300 text-[10px] font-black uppercase border border-purple-200 dark:border-purple-800">
                    Spoor 4
                </span>
                @if($isVip && $supporterTier >= 3)
                    <span class="text-[10px] font-black text-emerald-600 dark:text-emerald-400">✓ Actief</span>
                @else
                    <i class="fa-solid fa-lock text-purple-400"></i>
                @endif
            </div>
            <h4 class="font-black text-purple-700 dark:text-purple-400 text-sm flex items-center gap-1.5">
                <i class="fa-solid fa-dragon text-purple-500"></i> Tier 3 Baas Pass
            </h4>
            <p class="text-slate-500 dark:text-slate-400 leading-snug text-[11px]">
                Vereist Kliniek Baas of Lifetime status. Miljoenen cash, megakisten, maskers en respect.
            </p>
        </div>
    </div>

    <!-- 20 Battle Pass Niveaus Lijst -->
    <div class="space-y-4">
        @foreach($config as $lvlNum => $item)
            @php
                $isLevelUnlocked = $character->level >= $item['req_level'];
            @endphp
            <div class="bg-white dark:bg-slate-900 border {{ $isLevelUnlocked ? 'border-purple-300 dark:border-purple-800/80 shadow-sm' : 'border-slate-200 dark:border-slate-800 opacity-80' }} rounded-3xl p-4 sm:p-5 transition hover:shadow-md space-y-4">
                
                <!-- Niveau Header -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 dark:border-slate-800/80 pb-3">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl {{ $isLevelUnlocked ? 'bg-gradient-to-br from-purple-600 to-indigo-600 text-white shadow-purple-500/30' : 'bg-slate-100 dark:bg-slate-800 text-slate-400' }} flex items-center justify-center font-black text-sm shadow-sm shrink-0">
                            {{ $lvlNum }}
                        </div>
                        <div>
                            <h3 class="text-sm sm:text-base font-black text-slate-900 dark:text-white flex items-center gap-2">
                                <span>Battle Pass Niveau {{ $lvlNum }}</span>
                                @if($lvlNum === 20)
                                    <span class="px-2 py-0.5 rounded-full bg-amber-400 text-slate-950 text-[10px] font-black uppercase tracking-wider animate-bounce">
                                        👑 Grand Finale
                                    </span>
                                @elseif($lvlNum % 5 === 0)
                                    <span class="px-2 py-0.5 rounded-full bg-purple-100 dark:bg-purple-950 text-purple-700 dark:text-purple-300 text-[10px] font-black uppercase">
                                        ⭐ Mijlpaal
                                    </span>
                                @endif
                            </h3>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400 font-medium">
                                Vereist: Level {{ $item['req_level'] }}
                                @if($item['req_respect'] > 0)
                                    &bull; {{ number_format($item['req_respect'], 0, ',', '.') }} Respect aanbevolen
                                @endif
                            </p>
                        </div>
                    </div>

                    <div>
                        @if($isLevelUnlocked)
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-100 dark:bg-emerald-950 text-emerald-800 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-800 text-[11px] font-black uppercase tracking-wider">
                                <i class="fa-solid fa-lock-open text-emerald-600 dark:text-emerald-400"></i> Ontgrendeld
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-700 text-[11px] font-bold">
                                <i class="fa-solid fa-lock text-slate-400"></i> Bereik Level {{ $item['req_level'] }}
                            </span>
                        @endif
                    </div>
                </div>

                <!-- 4 Tracks Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">

                    <!-- Spoor 1: Gratis -->
                    @php
                        $tFree = $item['tracks']['free'];
                        $isClaimedFree = in_array("lvl_{$lvlNum}_free", $claims, true);
                    @endphp
                    <div class="p-3.5 rounded-2xl border flex flex-col justify-between space-y-3 transition
                        {{ $isClaimedFree 
                            ? 'bg-slate-50 dark:bg-slate-800/40 border-slate-200 dark:border-slate-800 opacity-70' 
                            : ($isLevelUnlocked 
                                ? 'bg-slate-50/80 dark:bg-slate-850 border-purple-200 dark:border-purple-800/60 shadow-2xs' 
                                : 'bg-slate-50/40 dark:bg-slate-900/40 border-slate-200/60 dark:border-slate-800/60') }}">
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between">
                                <span class="text-[10px] font-black uppercase tracking-wider text-slate-500">Gratis Pass</span>
                                <i class="fa-solid {{ $tFree['icon'] }} text-slate-500 text-xs"></i>
                            </div>
                            <h4 class="font-extrabold text-xs text-slate-900 dark:text-white leading-snug">
                                {{ $tFree['title'] }}
                            </h4>
                            <p class="text-[11px] font-bold text-slate-600 dark:text-slate-300">
                                {{ $tFree['summary'] }}
                            </p>
                        </div>

                        <div>
                            @if($isClaimedFree)
                                <span class="w-full py-1.5 rounded-xl bg-slate-200 dark:bg-slate-800 text-slate-500 text-[11px] font-bold flex items-center justify-center gap-1">
                                    <i class="fa-solid fa-check"></i> Geclaimd
                                </span>
                            @elseif($isLevelUnlocked)
                                <form action="{{ route('battlepass.claim') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="level" value="{{ $lvlNum }}">
                                    <input type="hidden" name="track" value="free">
                                    <button type="submit" class="w-full py-1.5 rounded-xl bg-purple-600 hover:bg-purple-700 text-white text-[11px] font-black uppercase tracking-wider transition shadow cursor-pointer active:scale-95 flex items-center justify-center gap-1.5">
                                        <i class="fa-solid fa-gift"></i> Claimen
                                    </button>
                                </form>
                            @else
                                <span class="w-full py-1.5 rounded-xl bg-slate-100 dark:bg-slate-800/60 text-slate-400 text-[10px] font-bold flex items-center justify-center gap-1">
                                    <i class="fa-solid fa-lock text-[9px]"></i> Lvl {{ $item['req_level'] }}
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- Spoor 2: Tier 1 VIP -->
                    @php
                        $tVip1 = $item['tracks']['vip1'];
                        $isClaimedVip1 = in_array("lvl_{$lvlNum}_vip1", $claims, true);
                        $hasVip1 = $isVip && $supporterTier >= 1;
                    @endphp
                    <div class="p-3.5 rounded-2xl border flex flex-col justify-between space-y-3 transition
                        {{ $isClaimedVip1 
                            ? 'bg-blue-50/40 dark:bg-blue-950/20 border-blue-200 dark:border-blue-900/40 opacity-70' 
                            : ($isLevelUnlocked && $hasVip1 
                                ? 'bg-blue-50/80 dark:bg-blue-950/40 border-blue-300 dark:border-blue-800 shadow-2xs ring-1 ring-blue-400/30' 
                                : 'bg-slate-50/40 dark:bg-slate-900/40 border-slate-200/60 dark:border-slate-800/60') }}">
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between">
                                <span class="text-[10px] font-black uppercase tracking-wider text-blue-700 dark:text-blue-400">Tier 1 V.I.P.</span>
                                <i class="fa-solid {{ $tVip1['icon'] }} text-blue-500 text-xs"></i>
                            </div>
                            <h4 class="font-extrabold text-xs text-blue-900 dark:text-blue-200 leading-snug">
                                {{ $tVip1['title'] }}
                            </h4>
                            <p class="text-[11px] font-bold text-blue-800 dark:text-blue-300">
                                {{ $tVip1['summary'] }}
                            </p>
                        </div>

                        <div>
                            @if($isClaimedVip1)
                                <span class="w-full py-1.5 rounded-xl bg-slate-200 dark:bg-slate-800 text-slate-500 text-[11px] font-bold flex items-center justify-center gap-1">
                                    <i class="fa-solid fa-check"></i> Geclaimd
                                </span>
                            @elseif($isLevelUnlocked && $hasVip1)
                                <form action="{{ route('battlepass.claim') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="level" value="{{ $lvlNum }}">
                                    <input type="hidden" name="track" value="vip1">
                                    <button type="submit" class="w-full py-1.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-[11px] font-black uppercase tracking-wider transition shadow cursor-pointer active:scale-95 flex items-center justify-center gap-1.5">
                                        <i class="fa-solid fa-gift"></i> Claimen
                                    </button>
                                </form>
                            @elseif(! $hasVip1)
                                <a href="{{ route('vip.index') }}" class="w-full py-1.5 rounded-xl bg-blue-100 hover:bg-blue-200 dark:bg-blue-950 dark:hover:bg-blue-900 text-blue-700 dark:text-blue-300 text-[10px] font-black uppercase tracking-wider flex items-center justify-center gap-1 transition">
                                    <i class="fa-solid fa-lock text-[9px]"></i> Vereist VIP Tier 1
                                </a>
                            @else
                                <span class="w-full py-1.5 rounded-xl bg-slate-100 dark:bg-slate-800/60 text-slate-400 text-[10px] font-bold flex items-center justify-center gap-1">
                                    <i class="fa-solid fa-lock text-[9px]"></i> Lvl {{ $item['req_level'] }}
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- Spoor 3: Tier 2 Elite -->
                    @php
                        $tVip2 = $item['tracks']['vip2'];
                        $isClaimedVip2 = in_array("lvl_{$lvlNum}_vip2", $claims, true);
                        $hasVip2 = $isVip && $supporterTier >= 2;
                    @endphp
                    <div class="p-3.5 rounded-2xl border flex flex-col justify-between space-y-3 transition
                        {{ $isClaimedVip2 
                            ? 'bg-emerald-50/40 dark:bg-emerald-950/20 border-emerald-200 dark:border-emerald-900/40 opacity-70' 
                            : ($isLevelUnlocked && $hasVip2 
                                ? 'bg-emerald-50/80 dark:bg-emerald-950/40 border-emerald-300 dark:border-emerald-800 shadow-2xs ring-1 ring-emerald-400/30' 
                                : 'bg-slate-50/40 dark:bg-slate-900/40 border-slate-200/60 dark:border-slate-800/60') }}">
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between">
                                <span class="text-[10px] font-black uppercase tracking-wider text-emerald-700 dark:text-emerald-400">Tier 2 Elite</span>
                                <i class="fa-solid {{ $tVip2['icon'] }} text-emerald-500 text-xs"></i>
                            </div>
                            <h4 class="font-extrabold text-xs text-emerald-900 dark:text-emerald-200 leading-snug">
                                {{ $tVip2['title'] }}
                            </h4>
                            <p class="text-[11px] font-bold text-emerald-800 dark:text-emerald-300">
                                {{ $tVip2['summary'] }}
                            </p>
                        </div>

                        <div>
                            @if($isClaimedVip2)
                                <span class="w-full py-1.5 rounded-xl bg-slate-200 dark:bg-slate-800 text-slate-500 text-[11px] font-bold flex items-center justify-center gap-1">
                                    <i class="fa-solid fa-check"></i> Geclaimd
                                </span>
                            @elseif($isLevelUnlocked && $hasVip2)
                                <form action="{{ route('battlepass.claim') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="level" value="{{ $lvlNum }}">
                                    <input type="hidden" name="track" value="vip2">
                                    <button type="submit" class="w-full py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-[11px] font-black uppercase tracking-wider transition shadow cursor-pointer active:scale-95 flex items-center justify-center gap-1.5">
                                        <i class="fa-solid fa-gift"></i> Claimen
                                    </button>
                                </form>
                            @elseif(! $hasVip2)
                                <a href="{{ route('vip.index') }}" class="w-full py-1.5 rounded-xl bg-emerald-100 hover:bg-emerald-200 dark:bg-emerald-950 dark:hover:bg-emerald-900 text-emerald-700 dark:text-emerald-300 text-[10px] font-black uppercase tracking-wider flex items-center justify-center gap-1 transition">
                                    <i class="fa-solid fa-lock text-[9px]"></i> Vereist VIP Tier 2
                                </a>
                            @else
                                <span class="w-full py-1.5 rounded-xl bg-slate-100 dark:bg-slate-800/60 text-slate-400 text-[10px] font-bold flex items-center justify-center gap-1">
                                    <i class="fa-solid fa-lock text-[9px]"></i> Lvl {{ $item['req_level'] }}
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- Spoor 4: Tier 3 Syndicaat Baas -->
                    @php
                        $tVip3 = $item['tracks']['vip3'];
                        $isClaimedVip3 = in_array("lvl_{$lvlNum}_vip3", $claims, true);
                        $hasVip3 = $isVip && $supporterTier >= 3;
                    @endphp
                    <div class="p-3.5 rounded-2xl border flex flex-col justify-between space-y-3 transition
                        {{ $isClaimedVip3 
                            ? 'bg-purple-50/40 dark:bg-purple-950/20 border-purple-200 dark:border-purple-900/40 opacity-70' 
                            : ($isLevelUnlocked && $hasVip3 
                                ? 'bg-purple-50/80 dark:bg-purple-950/40 border-purple-300 dark:border-purple-800 shadow-2xs ring-1 ring-purple-400/30' 
                                : 'bg-slate-50/40 dark:bg-slate-900/40 border-slate-200/60 dark:border-slate-800/60') }}">
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between">
                                <span class="text-[10px] font-black uppercase tracking-wider text-purple-700 dark:text-purple-400">Tier 3 Baas</span>
                                <i class="fa-solid {{ $tVip3['icon'] }} text-purple-500 text-xs"></i>
                            </div>
                            <h4 class="font-extrabold text-xs text-purple-900 dark:text-purple-200 leading-snug">
                                {{ $tVip3['title'] }}
                            </h4>
                            <p class="text-[11px] font-bold text-purple-800 dark:text-purple-300">
                                {{ $tVip3['summary'] }}
                            </p>
                        </div>

                        <div>
                            @if($isClaimedVip3)
                                <span class="w-full py-1.5 rounded-xl bg-slate-200 dark:bg-slate-800 text-slate-500 text-[11px] font-bold flex items-center justify-center gap-1">
                                    <i class="fa-solid fa-check"></i> Geclaimd
                                </span>
                            @elseif($isLevelUnlocked && $hasVip3)
                                <form action="{{ route('battlepass.claim') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="level" value="{{ $lvlNum }}">
                                    <input type="hidden" name="track" value="vip3">
                                    <button type="submit" class="w-full py-1.5 rounded-xl bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-500 hover:to-indigo-500 text-white text-[11px] font-black uppercase tracking-wider transition shadow cursor-pointer active:scale-95 flex items-center justify-center gap-1.5">
                                        <i class="fa-solid fa-dragon"></i> Claimen
                                    </button>
                                </form>
                            @elseif(! $hasVip3)
                                <a href="{{ route('vip.index') }}" class="w-full py-1.5 rounded-xl bg-purple-100 hover:bg-purple-200 dark:bg-purple-950 dark:hover:bg-purple-900 text-purple-700 dark:text-purple-300 text-[10px] font-black uppercase tracking-wider flex items-center justify-center gap-1 transition">
                                    <i class="fa-solid fa-lock text-[9px]"></i> Vereist VIP Tier 3
                                </a>
                            @else
                                <span class="w-full py-1.5 rounded-xl bg-slate-100 dark:bg-slate-800/60 text-slate-400 text-[10px] font-bold flex items-center justify-center gap-1">
                                    <i class="fa-solid fa-lock text-[9px]"></i> Lvl {{ $item['req_level'] }}
                                </span>
                            @endif
                        </div>
                    </div>

                </div>
            </div>
        @endforeach
    </div>

</div>
@endsection
