@extends('layouts.app')

@section('title', '🎃 Halloween Quests - TBS Kliniek Event')

@section('content')
<div class="space-y-6">
    <!-- TBS Kliniek Title Banner -->
    <div class="rounded-2xl bg-white dark:bg-slate-900 p-6 sm:p-8 text-slate-800 dark:text-slate-100 shadow-xs border border-slate-200 dark:border-slate-800 relative overflow-hidden">
        <!-- Subtle theme ambient glow -->
        <div class="absolute -right-10 -bottom-10 w-72 h-72 bg-orange-500/5 dark:bg-orange-500/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="flex items-start sm:items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-orange-50 dark:bg-orange-950/40 border border-orange-100 dark:border-orange-900/60 flex items-center justify-center text-2xl text-orange-600 dark:text-orange-400 shadow-2xs shrink-0">
                    <i class="fa-solid fa-ghost"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="text-[10px] font-black uppercase tracking-widest text-orange-700 dark:text-orange-400 bg-orange-50 dark:bg-orange-950/50 px-2.5 py-0.5 rounded-md border border-orange-200 dark:border-orange-800/60">
                            🎃 Halloween Event 2026
                        </span>
                        <span class="text-[10px] font-bold text-slate-500 dark:text-slate-400 bg-slate-100 dark:bg-slate-800 px-2.5 py-0.5 rounded-md border border-slate-200/60 dark:border-slate-700/60">
                            <i class="fa-regular fa-clock mr-1 text-orange-500"></i> Resterend: {{ $timeRemaining['formatted'] }} (t/m 5 Nov)
                        </span>
                    </div>
                    <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight mt-1.5 flex items-center gap-2">
                        Halloween Quests & Mijlpalen
                    </h1>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 max-w-xl leading-relaxed">
                        Voltooi dagelijkse en wekelijkse griezelopdrachten in de kliniek en verdien <strong class="text-orange-600 dark:text-orange-400">Pompoenpitten 🎃</strong>, contant geld, XP en respect om unieke beloningen te ontgrendelen!
                    </p>
                </div>
            </div>

            <!-- Seeds Wallet Chip & Shop Link -->
            <div class="flex items-center gap-3 shrink-0">
                <div class="bg-slate-50 dark:bg-slate-800/70 border border-slate-200 dark:border-slate-700/80 rounded-2xl px-4 py-3 flex items-center gap-3 shadow-2xs">
                    <div class="w-10 h-10 rounded-xl bg-orange-50 dark:bg-orange-950/50 border border-orange-200/60 dark:border-orange-850/60 flex items-center justify-center text-xl shrink-0">
                        🎃
                    </div>
                    <div>
                        <span class="text-[10px] font-black uppercase tracking-wider text-slate-400 block">Jouw Pompoenpitten</span>
                        <div class="text-lg font-black text-slate-900 dark:text-white font-mono leading-none">
                            {{ number_format($character->pumpkin_seeds ?? 0, 0, ',', '.') }}
                        </div>
                    </div>
                </div>

                <a href="{{ route('halloween.shop') }}" class="bg-orange-600 hover:bg-orange-700 text-white font-bold py-3 px-4 rounded-xl text-xs shadow-xs transition active:scale-95 flex items-center gap-2 shrink-0">
                    <i class="fa-solid fa-store"></i>
                    <span>Shop &raquo;</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Sub-Navigation Tabs Bar -->
    <div class="flex items-center gap-2 border-b border-slate-200 dark:border-slate-800 pb-3">
        <a href="{{ route('halloween.quests') }}" 
           class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 bg-orange-500 text-white shadow-xs">
            <i class="fa-solid fa-scroll"></i>
            <span>Halloween Quests</span>
        </a>
        <a href="{{ route('halloween.shop') }}" 
           class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 border border-slate-200 dark:border-slate-800">
            <i class="fa-solid fa-store"></i>
            <span>Halloween Shop & Beloningen</span>
        </a>
    </div>

    <!-- Flash Notifications -->
    @if(session('success'))
        <div class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 text-xs font-bold flex items-center gap-2.5">
            <i class="fa-solid fa-circle-check text-emerald-600 dark:text-emerald-400 text-base"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="p-4 rounded-xl bg-rose-50 dark:bg-rose-950/30 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-300 text-xs font-bold flex items-center gap-2.5">
            <i class="fa-solid fa-triangle-exclamation text-rose-600 dark:text-rose-400 text-base"></i>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    @if(session('info'))
        <div class="p-4 rounded-xl bg-blue-50 dark:bg-blue-950/30 border border-blue-200 dark:border-blue-800 text-blue-800 dark:text-blue-300 text-xs font-bold flex items-center gap-2.5">
            <i class="fa-solid fa-circle-info text-blue-600 dark:text-blue-400 text-base"></i>
            <span>{{ session('info') }}</span>
        </div>
    @endif

    <!-- SECTION 1: DAGELIJKSE QUESTS -->
    <div class="space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-2 border-b border-slate-200 dark:border-slate-800">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-orange-50 dark:bg-orange-950/40 text-orange-600 dark:text-orange-400 flex items-center justify-center text-sm font-bold border border-orange-200/60 dark:border-orange-850/60">
                    <i class="fa-solid fa-calendar-day"></i>
                </div>
                <div>
                    <h2 class="text-sm sm:text-base font-black text-slate-900 dark:text-white">Dagelijkse Halloween Quests</h2>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400">Verdien elke dag verse pompoenpitten</p>
                </div>
            </div>
            <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400 bg-slate-100 dark:bg-slate-800/80 px-2.5 py-1 rounded-lg border border-slate-200/60 dark:border-slate-700/60 flex items-center gap-1.5 self-start sm:self-auto">
                <i class="fa-solid fa-arrows-rotate text-[10px] text-orange-500"></i> Reset elke nacht om 00:00
            </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($dailyQuests as $quest)
                @php
                    $isClaimed = $quest['is_claimed'];
                    $canClaim = $quest['can_claim'];
                @endphp
                <div class="bg-white dark:bg-slate-900 rounded-2xl border p-5 flex flex-col justify-between gap-4 transition shadow-xs hover:border-slate-300 dark:hover:border-slate-700 {{ $isClaimed ? 'border-emerald-300 dark:border-emerald-800/60 bg-emerald-50/10 dark:bg-emerald-950/10 opacity-85' : ($canClaim ? 'border-orange-400 dark:border-orange-500 ring-2 ring-orange-500/20 shadow-xs shadow-orange-500/10' : 'border-slate-200 dark:border-slate-800') }}">
                    <div class="space-y-3">
                        <!-- Top Row: Icon, Title & Status -->
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <div class="w-11 h-11 rounded-xl flex items-center justify-center text-lg shrink-0 border {{ $isClaimed ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 border-emerald-200 dark:border-emerald-800' : ($canClaim ? 'bg-orange-50 dark:bg-orange-950/40 text-orange-600 dark:text-orange-400 border-orange-200 dark:border-orange-800' : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-700') }}">
                                    <i class="fa-solid {{ $quest['icon'] }}"></i>
                                </div>
                                <div class="min-w-0">
                                    <h3 class="text-xs sm:text-sm font-black text-slate-900 dark:text-white leading-snug truncate">{{ $quest['title'] }}</h3>
                                    <span class="text-[10px] font-black text-orange-600 dark:text-orange-400 uppercase tracking-wider block mt-0.5">
                                        +{{ $quest['seeds'] }} Pompoenpitten 🎃
                                    </span>
                                </div>
                            </div>

                            @if($isClaimed)
                                <span class="px-2 py-0.5 rounded-md bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-400 text-[10px] font-black border border-emerald-200 dark:border-emerald-800/60 shrink-0">
                                    ✓ Geclaimd
                                </span>
                            @elseif($canClaim)
                                <span class="px-2 py-0.5 rounded-md bg-orange-500 text-white text-[10px] font-black animate-pulse shrink-0 shadow-2xs">
                                    Klaar!
                                </span>
                            @else
                                <span class="px-2 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 text-[10px] font-bold font-mono border border-slate-200 dark:border-slate-700 shrink-0">
                                    {{ $quest['current'] }}/{{ $quest['target'] }}
                                </span>
                            @endif
                        </div>

                        <!-- Description -->
                        <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed">{{ $quest['desc'] }}</p>

                        <!-- Reward Chips -->
                        <div class="flex items-center gap-1.5 flex-wrap pt-1">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Beloningen:</span>
                            <span class="px-2 py-0.5 rounded-md bg-orange-50 dark:bg-orange-950/40 text-orange-700 dark:text-orange-400 font-mono text-[10px] font-black border border-orange-200/60 dark:border-orange-850/60">
                                +{{ $quest['seeds'] }} 🎃
                            </span>
                            @if(!empty($quest['xp']))
                                <span class="px-2 py-0.5 rounded-md bg-purple-50 dark:bg-purple-950/40 text-purple-700 dark:text-purple-300 font-mono text-[10px] font-bold border border-purple-200/60 dark:border-purple-850/60">
                                    +{{ $quest['xp'] }} XP
                                </span>
                            @endif
                            @if(!empty($quest['respect']))
                                <span class="px-2 py-0.5 rounded-md bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300 font-mono text-[10px] font-bold border border-amber-200/60 dark:border-amber-850/60">
                                    +{{ $quest['respect'] }} Resp
                                </span>
                            @endif
                            @if(!empty($quest['cash']))
                                <span class="px-2 py-0.5 rounded-md bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 font-mono text-[10px] font-bold border border-emerald-200/60 dark:border-emerald-850/60">
                                    +€{{ number_format($quest['cash'], 0, ',', '.') }}
                                </span>
                            @endif
                            @if(!empty($quest['tokens']))
                                <span class="px-2 py-0.5 rounded-md bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300 font-mono text-[10px] font-bold border border-amber-200/60 dark:border-amber-850/60">
                                    +{{ $quest['tokens'] }} Kluis
                                </span>
                            @endif
                        </div>

                        <!-- Progress Bar -->
                        <div class="space-y-1.5 pt-1">
                            <div class="w-full bg-slate-100 dark:bg-slate-800 rounded-full h-2 overflow-hidden border border-slate-200/60 dark:border-slate-700/60">
                                <div class="h-full rounded-full transition-all duration-300 {{ $isClaimed ? 'bg-emerald-500' : 'bg-linear-to-r from-orange-500 to-amber-500' }}" style="width: {{ $quest['percent'] }}%"></div>
                            </div>
                            <div class="flex items-center justify-between text-[11px] text-slate-400">
                                <span>Voortgang</span>
                                <span class="font-bold font-mono text-slate-700 dark:text-slate-300">{{ $quest['current'] }} / {{ $quest['target'] }} ({{ $quest['percent'] }}%)</span>
                            </div>
                        </div>
                    </div>

                    <!-- Action Button -->
                    <div class="pt-2 border-t border-slate-100 dark:border-slate-800/80">
                        @if($canClaim)
                            <form action="{{ route('halloween.claim-quest', $quest['key']) }}" method="POST">
                                @csrf
                                <button type="submit" class="w-full py-2.5 rounded-xl bg-orange-600 hover:bg-orange-700 text-white font-bold text-xs shadow-xs transition active:scale-95 flex items-center justify-center gap-1.5 cursor-pointer">
                                    <i class="fa-solid fa-gift"></i>
                                    <span>Claim {{ $quest['seeds'] }} Pompoenpitten 🎃</span>
                                </button>
                            </form>
                        @elseif($isClaimed)
                            <div class="w-full py-2.5 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/60 font-bold text-xs text-center flex items-center justify-center gap-1.5">
                                <i class="fa-solid fa-circle-check"></i>
                                <span>Vandaag voltooid & geclaimd</span>
                            </div>
                        @else
                            @php
                                $link = match($quest['category']) {
                                    'theft' => route('pickpocket.index'),
                                    'brawl' => route('fight.index'),
                                    'hustle' => route('hustle.index'),
                                    'training' => route('training.index'),
                                    'vault' => route('vault.index'),
                                    default => route('dashboard'),
                                };
                                $btnLabel = match($quest['category']) {
                                    'theft' => 'Naar Zakkenrollen »',
                                    'brawl' => 'Naar de Luchtplaats »',
                                    'hustle' => 'Naar Hosselen »',
                                    'training' => 'Naar Fitnessruimte »',
                                    'vault' => 'Naar de Kluis »',
                                    default => 'Voer Actie Uit »',
                                };
                            @endphp
                            <a href="{{ $link }}" class="w-full py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 font-bold text-xs transition active:scale-95 text-center block">
                                {{ $btnLabel }}
                            </a>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- SECTION 2: WEKELIJKSE QUESTS -->
    <div class="space-y-4 pt-2">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-2 border-b border-slate-200 dark:border-slate-800">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-purple-50 dark:bg-purple-950/40 text-purple-600 dark:text-purple-400 flex items-center justify-center text-sm font-bold border border-purple-200/60 dark:border-purple-850/60">
                    <i class="fa-solid fa-trophy"></i>
                </div>
                <div>
                    <h2 class="text-sm sm:text-base font-black text-slate-900 dark:text-white">Wekelijkse Halloween Mijlpalen</h2>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400">Grote uitdagingen actief gedurende het hele event</p>
                </div>
            </div>
            <span class="text-[11px] font-bold text-orange-600 dark:text-orange-400 bg-orange-50 dark:bg-orange-950/50 px-2.5 py-1 rounded-lg border border-orange-200 dark:border-orange-850/60 self-start sm:self-auto">
                ⭐ Mega Beloningen t/m 5 November
            </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4.5">
            @foreach($weeklyQuests as $quest)
                @php
                    $isClaimed = $quest['is_claimed'];
                    $canClaim = $quest['can_claim'];
                @endphp
                <div class="bg-white dark:bg-slate-900 rounded-2xl border p-5 sm:p-6 flex flex-col justify-between gap-4 transition shadow-xs hover:border-slate-300 dark:hover:border-slate-700 {{ $isClaimed ? 'border-emerald-300 dark:border-emerald-800/60 bg-emerald-50/10 dark:bg-emerald-950/10 opacity-85' : ($canClaim ? 'border-orange-400 dark:border-orange-500 ring-2 ring-orange-500/20 shadow-xs shadow-orange-500/10' : 'border-slate-200 dark:border-slate-800') }}">
                    <div class="space-y-3.5">
                        <!-- Top Row -->
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 rounded-2xl flex items-center justify-center text-xl shrink-0 border {{ $isClaimed ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 border-emerald-200 dark:border-emerald-800' : ($canClaim ? 'bg-orange-50 dark:bg-orange-950/40 text-orange-600 dark:text-orange-400 border-orange-200 dark:border-orange-800' : 'bg-purple-50 dark:bg-purple-950/40 text-purple-600 dark:text-purple-400 border-purple-200/80 dark:border-purple-800/80') }}">
                                    <i class="fa-solid {{ $quest['icon'] }}"></i>
                                </div>
                                <div class="min-w-0">
                                    <h3 class="text-sm sm:text-base font-black text-slate-900 dark:text-white leading-snug">{{ $quest['title'] }}</h3>
                                    <span class="text-xs font-black text-orange-600 dark:text-orange-400 uppercase tracking-wider block mt-0.5">
                                        +{{ $quest['seeds'] }} Pompoenpitten 🎃
                                    </span>
                                </div>
                            </div>

                            @if($isClaimed)
                                <span class="px-2.5 py-0.5 rounded-md bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-400 text-xs font-black border border-emerald-200 dark:border-emerald-800/60 shrink-0">
                                    ✓ Geclaimd
                                </span>
                            @elseif($canClaim)
                                <span class="px-2.5 py-0.5 rounded-md bg-orange-500 text-white text-xs font-black animate-pulse shrink-0 shadow-2xs">
                                    Klaar!
                                </span>
                            @else
                                <span class="px-2.5 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 text-xs font-bold font-mono border border-slate-200 dark:border-slate-700 shrink-0">
                                    {{ number_format($quest['current'], 0, ',', '.') }}/{{ number_format($quest['target'], 0, ',', '.') }}
                                </span>
                            @endif
                        </div>

                        <!-- Description -->
                        <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed">{{ $quest['desc'] }}</p>

                        <!-- Reward Chips -->
                        <div class="flex items-center gap-2 flex-wrap pt-1">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Beloningen:</span>
                            <span class="px-2.5 py-0.5 rounded-lg bg-orange-50 dark:bg-orange-950/40 text-orange-700 dark:text-orange-400 font-mono text-xs font-black border border-orange-200/60 dark:border-orange-850/60">
                                +{{ $quest['seeds'] }} 🎃
                            </span>
                            @if(!empty($quest['xp']))
                                <span class="px-2.5 py-0.5 rounded-lg bg-purple-50 dark:bg-purple-950/40 text-purple-700 dark:text-purple-300 font-mono text-xs font-bold border border-purple-200/60 dark:border-purple-850/60">
                                    +{{ $quest['xp'] }} XP
                                </span>
                            @endif
                            @if(!empty($quest['respect']))
                                <span class="px-2.5 py-0.5 rounded-lg bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300 font-mono text-xs font-bold border border-amber-200/60 dark:border-amber-850/60">
                                    +{{ $quest['respect'] }} Respect 🔥
                                </span>
                            @endif
                            @if(!empty($quest['cash']))
                                <span class="px-2.5 py-0.5 rounded-lg bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 font-mono text-xs font-bold border border-emerald-200/60 dark:border-emerald-850/60">
                                    +€{{ number_format($quest['cash'], 0, ',', '.') }}
                                </span>
                            @endif
                            @if(!empty($quest['tokens']))
                                <span class="px-2.5 py-0.5 rounded-lg bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300 font-mono text-xs font-bold border border-amber-200/60 dark:border-amber-850/60">
                                    +{{ $quest['tokens'] }} KluisMunten
                                </span>
                            @endif
                        </div>

                        <!-- Progress Bar -->
                        <div class="space-y-1.5 pt-1">
                            <div class="w-full bg-slate-100 dark:bg-slate-800 rounded-full h-2.5 overflow-hidden border border-slate-200/60 dark:border-slate-700/60">
                                <div class="h-full rounded-full transition-all duration-300 {{ $isClaimed ? 'bg-emerald-500' : 'bg-linear-to-r from-orange-500 to-amber-500' }}" style="width: {{ $quest['percent'] }}%"></div>
                            </div>
                            <div class="flex items-center justify-between text-xs text-slate-400">
                                <span>Voortgang</span>
                                <span class="font-bold font-mono text-slate-700 dark:text-slate-300">{{ number_format($quest['current'], 0, ',', '.') }} / {{ number_format($quest['target'], 0, ',', '.') }} ({{ $quest['percent'] }}%)</span>
                            </div>
                        </div>
                    </div>

                    <!-- Action Button -->
                    <div class="pt-2 border-t border-slate-100 dark:border-slate-800/80">
                        @if($canClaim)
                            <form action="{{ route('halloween.claim-quest', $quest['key']) }}" method="POST">
                                @csrf
                                <button type="submit" class="w-full py-2.5 rounded-xl bg-orange-600 hover:bg-orange-700 text-white font-bold text-xs shadow-xs transition active:scale-95 flex items-center justify-center gap-1.5 cursor-pointer">
                                    <i class="fa-solid fa-trophy"></i>
                                    <span>Claim Mijlpaal: +{{ $quest['seeds'] }} Pitten 🎃</span>
                                </button>
                            </form>
                        @elseif($isClaimed)
                            <div class="w-full py-2.5 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/60 font-bold text-xs text-center flex items-center justify-center gap-1.5">
                                <i class="fa-solid fa-circle-check"></i>
                                <span>Mijlpaal voltooid & geclaimd</span>
                            </div>
                        @else
                            @php
                                $link = match($quest['category']) {
                                    'theft' => route('pickpocket.index'),
                                    'brawl' => route('fight.index'),
                                    'cash' => route('theft.index'),
                                    'respect' => route('gangs.index'),
                                    default => route('dashboard'),
                                };
                                $btnLabel = match($quest['category']) {
                                    'theft' => 'Naar Zakkenrollen »',
                                    'brawl' => 'Naar Luchtplaats Gevechten »',
                                    'cash' => 'Verdien Cash via Diefstal »',
                                    'respect' => 'Naar Bende Oorlogen »',
                                    default => 'Werk Aan Mijlpaal »',
                                };
                            @endphp
                            <a href="{{ $link }}" class="w-full py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 font-bold text-xs transition active:scale-95 text-center block">
                                {{ $btnLabel }}
                            </a>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
