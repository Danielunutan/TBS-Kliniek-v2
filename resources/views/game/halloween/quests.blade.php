@extends('layouts.app')

@section('title', '🎃 Halloween Quests - TBS Kliniek Event')

@section('content')
<div class="space-y-6">
    <!-- TBS Kliniek Title Banner -->
    <div class="rounded-2xl bg-white dark:bg-slate-900 p-6 sm:p-7 text-slate-800 dark:text-slate-100 shadow-xs border border-slate-200 dark:border-slate-800 relative overflow-hidden">
        <!-- Subtle theme ambient glow -->
        <div class="absolute -right-10 -bottom-10 w-72 h-72 bg-purple-500/5 dark:bg-purple-500/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-5">
            <div class="flex items-start sm:items-center gap-4">
                <div class="w-13 h-13 rounded-2xl bg-purple-50 dark:bg-purple-950/50 border border-purple-200 dark:border-purple-800/60 flex items-center justify-center text-2xl text-purple-600 dark:text-purple-400 shadow-2xs shrink-0">
                    <i class="fa-solid fa-ghost"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2 flex-wrap mb-1">
                        <span class="text-[10px] font-black uppercase tracking-widest text-purple-700 dark:text-purple-300 bg-purple-50 dark:bg-purple-950/80 px-2.5 py-0.5 rounded-md border border-purple-200 dark:border-purple-800/60">
                            🎃 Halloween Event 2026
                        </span>
                        <span class="text-[10px] font-bold text-amber-800 dark:text-amber-300 bg-amber-50 dark:bg-amber-950/60 px-2.5 py-0.5 rounded-md border border-amber-200 dark:border-amber-800/60">
                            <i class="fa-regular fa-clock mr-1 text-amber-600 dark:text-amber-400"></i> Resterend: {{ $timeRemaining['formatted'] }} (t/m 5 Nov)
                        </span>
                    </div>
                    <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight">
                        Halloween Quests & Mijlpalen
                    </h1>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 max-w-xl leading-relaxed">
                        Voltooi dagelijkse en wekelijkse griezelopdrachten in de kliniek en verdien <strong class="text-amber-600 dark:text-amber-400">Pompoenpitten 🎃</strong>, contant geld, XP en respect om unieke beloningen te ontgrendelen!
                    </p>
                </div>
            </div>

            <!-- Seeds Wallet Chip & Shop Link -->
            <div class="flex items-center gap-3 shrink-0">
                <div class="bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-2.5 flex items-center gap-3 shadow-2xs">
                    <div class="w-9 h-9 rounded-lg bg-amber-100 dark:bg-amber-950/80 border border-amber-200 dark:border-amber-800/60 flex items-center justify-center text-lg shrink-0">
                        🎃
                    </div>
                    <div>
                        <span class="text-[9px] font-black uppercase tracking-wider text-slate-400 dark:text-slate-400 block">Jouw Pompoenpitten</span>
                        <div class="text-lg font-black text-slate-900 dark:text-white font-mono leading-none">
                            {{ number_format($character->pumpkin_seeds ?? 0, 0, ',', '.') }}
                        </div>
                    </div>
                </div>

                <a href="{{ route('halloween.shop') }}" class="bg-purple-600 hover:bg-purple-700 text-white font-bold py-2.5 px-4 rounded-xl text-xs shadow-xs transition active:scale-95 flex items-center gap-2 shrink-0">
                    <i class="fa-solid fa-store text-xs"></i>
                    <span>Naar Shop &raquo;</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Sub-Navigation Tabs Bar -->
    <div class="flex items-center gap-2 border-b border-slate-200 dark:border-slate-800 pb-3">
        <a href="{{ route('halloween.quests') }}" 
           class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 bg-purple-600 text-white shadow-xs">
            <i class="fa-solid fa-scroll"></i>
            <span>Halloween Quests</span>
        </a>
        <a href="{{ route('halloween.shop') }}" 
           class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 border border-slate-200 dark:border-slate-800 shadow-2xs">
            <i class="fa-solid fa-store"></i>
            <span>Halloween Shop & Beloningen</span>
        </a>
    </div>

    <!-- Flash Notifications -->
    

    <!-- SECTION 1: DAGELIJKSE QUESTS -->
    <div class="space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-2 border-b border-slate-200 dark:border-slate-800">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-purple-50 dark:bg-purple-950/40 text-purple-600 dark:text-purple-400 flex items-center justify-center text-sm font-bold border border-purple-200/80 dark:border-purple-800/60">
                    <i class="fa-solid fa-calendar-day"></i>
                </div>
                <div>
                    <h2 class="text-sm sm:text-base font-black text-slate-900 dark:text-white">Dagelijkse Halloween Quests</h2>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400">Verdien elke dag verse pompoenpitten</p>
                </div>
            </div>
            <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400 bg-slate-100 dark:bg-slate-800/80 px-2.5 py-1 rounded-lg border border-slate-200/60 dark:border-slate-700/60 flex items-center gap-1.5 self-start sm:self-auto">
                <i class="fa-solid fa-arrows-rotate text-[10px] text-purple-600 dark:text-purple-400"></i> Reset elke nacht om 00:00
            </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4.5">
            @foreach($dailyQuests as $quest)
                @php
                    $isClaimed = $quest['is_claimed'];
                    $canClaim = $quest['can_claim'];
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
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/90 dark:border-slate-800 shadow-xs overflow-hidden flex flex-col justify-between hover:border-purple-300 dark:hover:border-purple-600/50 transition">
                    <!-- Card Header (Subtle contrast tint) -->
                    <div class="p-4 bg-slate-50/70 dark:bg-slate-800/50 border-b border-slate-100 dark:border-slate-800 flex items-start justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center text-lg shrink-0 border {{ $isClaimed ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 border-emerald-200 dark:border-emerald-800' : ($canClaim ? 'bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 border-amber-200 dark:border-amber-800' : 'bg-purple-50 dark:bg-purple-950/40 text-purple-600 dark:text-purple-300 border-purple-200 dark:border-purple-800/60') }}">
                                <i class="fa-solid {{ $quest['icon'] }}"></i>
                            </div>
                            <div class="min-w-0">
                                <h3 class="text-xs sm:text-sm font-black text-slate-900 dark:text-white leading-snug truncate">{{ $quest['title'] }}</h3>
                                <span class="text-[10px] font-extrabold text-amber-600 dark:text-amber-400 uppercase tracking-wider block mt-0.5">
                                    +{{ $quest['seeds'] }} Pompoenpitten 🎃
                                </span>
                            </div>
                        </div>

                        @if($isClaimed)
                            <span class="px-2 py-0.5 rounded-md bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 text-[10px] font-black border border-emerald-300 dark:border-emerald-800 shrink-0">
                                ✓ Geclaimd
                            </span>
                        @elseif($canClaim)
                            <span class="px-2.5 py-0.5 rounded-md bg-amber-500 text-slate-950 text-[10px] font-black animate-pulse shrink-0 shadow-2xs">
                                Klaar!
                            </span>
                        @else
                            <span class="px-2 py-0.5 rounded-md bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-[10px] font-black font-mono border border-slate-200 dark:border-slate-700 shadow-2xs shrink-0">
                                {{ $quest['current'] }}/{{ $quest['target'] }}
                            </span>
                        @endif
                    </div>

                    <!-- Card Body -->
                    <div class="p-4 space-y-3.5 grow flex flex-col justify-between">
                        <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed">{{ $quest['desc'] }}</p>

                        <!-- Rewards Chips -->
                        <div class="space-y-1.5 pt-1">
                            <span class="text-[9px] font-black uppercase tracking-wider text-slate-400 block">Beloningen</span>
                            <div class="flex items-center gap-1.5 flex-wrap">
                                <span class="px-2 py-0.5 rounded-md bg-amber-50 dark:bg-amber-950/40 text-amber-800 dark:text-amber-300 font-mono text-[10px] font-black border border-amber-200 dark:border-amber-800/60">
                                    +{{ $quest['seeds'] }} 🎃
                                </span>
                                @if(!empty($quest['xp']))
                                    <span class="px-2 py-0.5 rounded-md bg-purple-50 dark:bg-purple-950/40 text-purple-800 dark:text-purple-300 font-mono text-[10px] font-bold border border-purple-200 dark:border-purple-800/60">
                                        +{{ $quest['xp'] }} XP
                                    </span>
                                @endif
                                @if(!empty($quest['respect']))
                                    <span class="px-2 py-0.5 rounded-md bg-rose-50 dark:bg-rose-950/40 text-rose-800 dark:text-rose-300 font-mono text-[10px] font-bold border border-rose-200 dark:border-rose-800/60">
                                        +{{ $quest['respect'] }} Resp
                                    </span>
                                @endif
                                @if(!empty($quest['cash']))
                                    <span class="px-2 py-0.5 rounded-md bg-emerald-50 dark:bg-emerald-950/40 text-emerald-800 dark:text-emerald-300 font-mono text-[10px] font-bold border border-emerald-200 dark:border-emerald-800/60">
                                        +€{{ number_format($quest['cash'], 0, ',', '.') }}
                                    </span>
                                @endif
                                @if(!empty($quest['tokens']))
                                    <span class="px-2 py-0.5 rounded-md bg-blue-50 dark:bg-blue-950/40 text-blue-800 dark:text-blue-300 font-mono text-[10px] font-bold border border-blue-200 dark:border-blue-800/60">
                                        +{{ $quest['tokens'] }} Kluis
                                    </span>
                                @endif
                            </div>
                        </div>

                        <!-- Progress Bar -->
                        <div class="space-y-1 pt-1">
                            <div class="w-full bg-slate-100 dark:bg-slate-800 rounded-full h-2 overflow-hidden border border-slate-200/80 dark:border-slate-700/80">
                                <div class="h-full rounded-full transition-all duration-300 {{ $isClaimed ? 'bg-emerald-500' : 'bg-purple-600' }}" style="width: {{ $quest['percent'] }}%"></div>
                            </div>
                            <div class="flex items-center justify-between text-[11px] text-slate-400">
                                <span>Voortgang</span>
                                <span class="font-bold font-mono text-slate-700 dark:text-slate-300">{{ $quest['current'] }} / {{ $quest['target'] }} ({{ $quest['percent'] }}%)</span>
                            </div>
                        </div>
                    </div>

                    <!-- Card Action Footer -->
                    <div class="p-3 bg-slate-50/50 dark:bg-slate-800/30 border-t border-slate-100 dark:border-slate-800">
                        @if($canClaim)
                            <form action="{{ route('halloween.claim-quest', $quest['key']) }}" method="POST">
                                @csrf
                                <button type="submit" class="w-full py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-xs transition active:scale-95 flex items-center justify-center gap-1.5 cursor-pointer">
                                    <i class="fa-solid fa-gift"></i>
                                    <span>Claim {{ $quest['seeds'] }} Pompoenpitten 🎃</span>
                                </button>
                            </form>
                        @elseif($isClaimed)
                            <div class="w-full py-2 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/60 font-bold text-xs text-center flex items-center justify-center gap-1.5">
                                <i class="fa-solid fa-circle-check text-emerald-600"></i>
                                <span>Voltooid & geclaimd</span>
                            </div>
                        @else
                            <a href="{{ $link }}" class="w-full py-2 rounded-xl bg-slate-900 hover:bg-slate-800 dark:bg-purple-600 dark:hover:bg-purple-700 text-white font-bold text-xs shadow-2xs transition active:scale-95 text-center flex items-center justify-center gap-1.5">
                                <span>{{ $btnLabel }}</span>
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
                <div class="w-8 h-8 rounded-lg bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 flex items-center justify-center text-sm font-bold border border-amber-200/80 dark:border-amber-800/60">
                    <i class="fa-solid fa-trophy"></i>
                </div>
                <div>
                    <h2 class="text-sm sm:text-base font-black text-slate-900 dark:text-white">Wekelijkse Halloween Mijlpalen</h2>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400">Grote uitdagingen actief gedurende het hele event</p>
                </div>
            </div>
            <span class="text-[11px] font-bold text-amber-800 dark:text-amber-300 bg-amber-50 dark:bg-amber-950/60 px-2.5 py-1 rounded-lg border border-amber-200 dark:border-amber-800/60 self-start sm:self-auto">
                ⭐ Mega Beloningen t/m 5 November
            </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4.5">
            @foreach($weeklyQuests as $quest)
                @php
                    $isClaimed = $quest['is_claimed'];
                    $canClaim = $quest['can_claim'];
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
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/90 dark:border-slate-800 shadow-xs overflow-hidden flex flex-col justify-between hover:border-purple-300 dark:hover:border-purple-600/50 transition">
                    <!-- Card Header -->
                    <div class="p-4 sm:p-5 bg-slate-50/70 dark:bg-slate-800/50 border-b border-slate-100 dark:border-slate-800 flex items-start justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-2xl flex items-center justify-center text-xl shrink-0 border {{ $isClaimed ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 border-emerald-200 dark:border-emerald-800' : ($canClaim ? 'bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 border-amber-200 dark:border-amber-800' : 'bg-purple-100 dark:bg-purple-950/50 text-purple-700 dark:text-purple-300 border-purple-200 dark:border-purple-800/60') }}">
                                <i class="fa-solid {{ $quest['icon'] }}"></i>
                            </div>
                            <div class="min-w-0">
                                <h3 class="text-sm sm:text-base font-black text-slate-900 dark:text-white leading-snug">{{ $quest['title'] }}</h3>
                                <span class="text-xs font-extrabold text-amber-600 dark:text-amber-400 uppercase tracking-wider block mt-0.5">
                                    +{{ $quest['seeds'] }} Pompoenpitten 🎃
                                </span>
                            </div>
                        </div>

                        @if($isClaimed)
                            <span class="px-2.5 py-0.5 rounded-md bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 text-xs font-black border border-emerald-300 dark:border-emerald-800 shrink-0">
                                ✓ Geclaimd
                            </span>
                        @elseif($canClaim)
                            <span class="px-2.5 py-0.5 rounded-md bg-amber-500 text-slate-950 text-xs font-black animate-pulse shrink-0 shadow-2xs">
                                Klaar!
                            </span>
                        @else
                            <span class="px-2.5 py-0.5 rounded-md bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-black font-mono border border-slate-200 dark:border-slate-700 shadow-2xs shrink-0">
                                {{ number_format($quest['current'], 0, ',', '.') }}/{{ number_format($quest['target'], 0, ',', '.') }}
                            </span>
                        @endif
                    </div>

                    <!-- Card Body -->
                    <div class="p-4 sm:p-5 space-y-3.5 grow flex flex-col justify-between">
                        <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed">{{ $quest['desc'] }}</p>

                        <!-- Rewards Chips -->
                        <div class="space-y-1.5 pt-1">
                            <span class="text-[9px] font-black uppercase tracking-wider text-slate-400 block">Beloningen</span>
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="px-2.5 py-0.5 rounded-lg bg-amber-50 dark:bg-amber-950/40 text-amber-800 dark:text-amber-300 font-mono text-xs font-black border border-amber-200 dark:border-amber-800/60">
                                    +{{ $quest['seeds'] }} 🎃
                                </span>
                                @if(!empty($quest['xp']))
                                    <span class="px-2.5 py-0.5 rounded-lg bg-purple-50 dark:bg-purple-950/40 text-purple-800 dark:text-purple-300 font-mono text-xs font-bold border border-purple-200 dark:border-purple-800/60">
                                        +{{ $quest['xp'] }} XP
                                    </span>
                                @endif
                                @if(!empty($quest['respect']))
                                    <span class="px-2.5 py-0.5 rounded-lg bg-rose-50 dark:bg-rose-950/40 text-rose-800 dark:text-rose-300 font-mono text-xs font-bold border border-rose-200 dark:border-rose-800/60">
                                        +{{ $quest['respect'] }} Respect 🔥
                                    </span>
                                @endif
                                @if(!empty($quest['cash']))
                                    <span class="px-2.5 py-0.5 rounded-lg bg-emerald-50 dark:bg-emerald-950/40 text-emerald-800 dark:text-emerald-300 font-mono text-xs font-bold border border-emerald-200 dark:border-emerald-800/60">
                                        +€{{ number_format($quest['cash'], 0, ',', '.') }}
                                    </span>
                                @endif
                                @if(!empty($quest['tokens']))
                                    <span class="px-2.5 py-0.5 rounded-lg bg-blue-50 dark:bg-blue-950/40 text-blue-800 dark:text-blue-300 font-mono text-xs font-bold border border-blue-200 dark:border-blue-800/60">
                                        +{{ $quest['tokens'] }} KluisMunten
                                    </span>
                                @endif
                            </div>
                        </div>

                        <!-- Progress Bar -->
                        <div class="space-y-1.5 pt-1">
                            <div class="w-full bg-slate-100 dark:bg-slate-800 rounded-full h-2.5 overflow-hidden border border-slate-200/80 dark:border-slate-700/80">
                                <div class="h-full rounded-full transition-all duration-300 {{ $isClaimed ? 'bg-emerald-500' : 'bg-purple-600' }}" style="width: {{ $quest['percent'] }}%"></div>
                            </div>
                            <div class="flex items-center justify-between text-xs text-slate-400">
                                <span>Voortgang</span>
                                <span class="font-bold font-mono text-slate-700 dark:text-slate-300">{{ number_format($quest['current'], 0, ',', '.') }} / {{ number_format($quest['target'], 0, ',', '.') }} ({{ $quest['percent'] }}%)</span>
                            </div>
                        </div>
                    </div>

                    <!-- Card Action Footer -->
                    <div class="p-3 bg-slate-50/50 dark:bg-slate-800/30 border-t border-slate-100 dark:border-slate-800">
                        @if($canClaim)
                            <form action="{{ route('halloween.claim-quest', $quest['key']) }}" method="POST">
                                @csrf
                                <button type="submit" class="w-full py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-xs transition active:scale-95 flex items-center justify-center gap-1.5 cursor-pointer">
                                    <i class="fa-solid fa-trophy"></i>
                                    <span>Claim Mijlpaal: +{{ $quest['seeds'] }} Pitten 🎃</span>
                                </button>
                            </form>
                        @elseif($isClaimed)
                            <div class="w-full py-2 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/60 font-bold text-xs text-center flex items-center justify-center gap-1.5">
                                <i class="fa-solid fa-circle-check text-emerald-600"></i>
                                <span>Mijlpaal voltooid & geclaimd</span>
                            </div>
                        @else
                            <a href="{{ $link }}" class="w-full py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 dark:bg-purple-600 dark:hover:bg-purple-700 text-white font-bold text-xs shadow-2xs transition active:scale-95 text-center flex items-center justify-center gap-1.5">
                                <span>{{ $btnLabel }}</span>
                            </a>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
