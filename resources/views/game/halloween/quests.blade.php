@extends('layouts.app')

@section('title', '🎃 Halloween Quests - TBS Kliniek Event')

@section('content')
<div class="space-y-6">
    <!-- EVENT HERO BANNER -->
    <div class="relative overflow-hidden rounded-3xl bg-linear-to-br from-slate-950 via-[#180928] to-[#2a0e05] border-2 border-orange-500/40 p-6 sm:p-8 shadow-2xl text-white">
        <!-- Spooky background elements -->
        <div class="absolute -right-6 -bottom-6 text-9xl opacity-15 pointer-events-none select-none text-orange-500">
            🎃
        </div>
        <div class="absolute right-36 top-4 text-4xl opacity-20 pointer-events-none select-none animate-bounce">
            🦇
        </div>
        <div class="absolute left-1/2 -top-10 text-8xl opacity-10 pointer-events-none select-none text-purple-400">
            👻
        </div>

        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="space-y-2 max-w-2xl">
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-orange-500/20 text-orange-400 border border-orange-500/40 shadow-xs flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-orange-500 animate-ping"></span>
                        🎃 Halloween Event 2026
                    </span>
                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-purple-900/60 text-purple-300 border border-purple-700/50">
                        ⏳ Resterend: {{ $timeRemaining['formatted'] }} (t/m 5 November)
                    </span>
                </div>
                <h1 class="text-2xl sm:text-3xl lg:text-4xl font-black tracking-tight text-white flex items-center gap-2.5">
                    <span>Halloween Quests</span>
                    <span class="text-orange-500">🎃</span>
                </h1>
                <p class="text-xs sm:text-sm text-slate-300 leading-relaxed">
                    Voltooi dagelijkse en wekelijkse griezelopdrachten in de kliniek. Voor elke voltooide quest verdien je waardevolle <strong class="text-orange-400">Pompoenpitten 🎃</strong>, contant geld, XP en respect. Ruil je pitten in de Halloween Shop in voor unieke profielranden en speciale boosts!
                </p>
            </div>

            <!-- PLAYER PUMPKIN SEEDS WALLET & SHOP SHORTCUT -->
            <div class="shrink-0 flex flex-col sm:flex-row md:flex-col gap-3 justify-center">
                <div class="bg-black/40 backdrop-blur-md rounded-2xl border border-orange-500/30 p-4 text-center space-y-1">
                    <span class="text-[10px] font-black uppercase tracking-wider text-orange-400/80 block">Jouw Pompoenpitten</span>
                    <div class="text-3xl font-black text-orange-400 flex items-center justify-center gap-2 font-mono">
                        <span>🎃</span>
                        <span>{{ number_format($character->pumpkin_seeds ?? 0, 0, ',', '.') }}</span>
                    </div>
                    <span class="text-[10px] text-slate-400 block">Te besteden in de Halloween Shop</span>
                </div>

                <a href="{{ route('halloween.shop') }}" class="px-5 py-3 rounded-2xl bg-linear-to-r from-orange-600 to-amber-600 hover:from-orange-500 hover:to-amber-500 text-white font-black text-xs text-center shadow-lg hover:shadow-orange-500/25 transition-all flex items-center justify-center gap-2">
                    <i class="fa-solid fa-store"></i>
                    <span>Naar Halloween Shop &raquo;</span>
                </a>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-xs font-bold flex items-center gap-2.5">
            <i class="fa-solid fa-circle-check text-base"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="p-4 rounded-2xl bg-rose-500/10 border border-rose-500/30 text-rose-400 text-xs font-bold flex items-center gap-2.5">
            <i class="fa-solid fa-triangle-exclamation text-base"></i>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    @if(session('info'))
        <div class="p-4 rounded-2xl bg-purple-500/10 border border-purple-500/30 text-purple-300 text-xs font-bold flex items-center gap-2.5">
            <i class="fa-solid fa-circle-info text-base"></i>
            <span>{{ session('info') }}</span>
        </div>
    @endif

    <!-- SECTION 1: DAGELIJKSE QUESTS -->
    <div class="space-y-3">
        <div class="flex items-center justify-between pb-1 border-b border-slate-200 dark:border-purple-900/40">
            <div class="flex items-center gap-2">
                <span class="text-lg">🕯️</span>
                <h2 class="text-base font-black text-slate-900 dark:text-white">Dagelijkse Halloween Quests</h2>
            </div>
            <span class="text-[11px] font-bold text-slate-400 dark:text-purple-300/70 flex items-center gap-1">
                <i class="fa-solid fa-arrows-rotate text-[10px]"></i> Reset elke nacht om 00:00
            </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
            @foreach($dailyQuests as $quest)
                <div class="bg-white dark:bg-[#150a2b] rounded-2xl border p-4.5 flex flex-col justify-between gap-4 transition shadow-xs hover:border-orange-500/60 {{ $quest['is_claimed'] ? 'border-emerald-500/30 opacity-80' : ($quest['can_claim'] ? 'border-orange-500 shadow-orange-500/10 ring-2 ring-orange-500/30' : 'border-slate-200 dark:border-purple-900/40') }}">
                    <div class="space-y-2.5">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex items-center gap-2.5">
                                <div class="w-10 h-10 rounded-xl flex items-center justify-center text-lg {{ $quest['is_claimed'] ? 'bg-emerald-500/20 text-emerald-400' : ($quest['is_completed'] ? 'bg-orange-500/20 text-orange-400' : 'bg-purple-100 dark:bg-purple-950/60 text-purple-600 dark:text-purple-300') }}">
                                    <i class="fa-solid {{ $quest['icon'] }}"></i>
                                </div>
                                <div>
                                    <h3 class="text-xs font-black text-slate-900 dark:text-white leading-snug">{{ $quest['title'] }}</h3>
                                    <span class="text-[10px] font-bold text-orange-500 uppercase tracking-wider">+{{ $quest['seeds'] }} Pompoenpitten 🎃</span>
                                </div>
                            </div>

                            @if($quest['is_claimed'])
                                <span class="px-2 py-0.5 rounded-full bg-emerald-500/20 text-emerald-400 text-[10px] font-black border border-emerald-500/30 shrink-0">
                                    ✓ Geclaimd
                                </span>
                            @elseif($quest['can_claim'])
                                <span class="px-2 py-0.5 rounded-full bg-orange-500 text-white text-[10px] font-black animate-pulse shrink-0">
                                    Klaar!
                                </span>
                            @else
                                <span class="px-2 py-0.5 rounded-full bg-slate-100 dark:bg-purple-950 text-slate-500 dark:text-purple-300 text-[10px] font-bold shrink-0">
                                    {{ $quest['current'] }}/{{ $quest['target'] }}
                                </span>
                            @endif
                        </div>

                        <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed">{{ $quest['desc'] }}</p>

                        <!-- Extra rewards preview -->
                        <div class="flex items-center gap-1.5 flex-wrap text-[10px] font-bold text-slate-400 dark:text-purple-300/80">
                            <span>Beloningen:</span>
                            <span class="px-1.5 py-0.2 rounded bg-orange-500/10 text-orange-400 font-mono font-black">+{{ $quest['seeds'] }} 🎃</span>
                            @if(!empty($quest['xp']))
                                <span class="px-1.5 py-0.2 rounded bg-purple-500/10 text-purple-400 font-mono">+{{ $quest['xp'] }} XP</span>
                            @endif
                            @if(!empty($quest['respect']))
                                <span class="px-1.5 py-0.2 rounded bg-amber-500/10 text-amber-400 font-mono">+{{ $quest['respect'] }} Resp</span>
                            @endif
                            @if(!empty($quest['cash']))
                                <span class="px-1.5 py-0.2 rounded bg-emerald-500/10 text-emerald-400 font-mono">+€{{ number_format($quest['cash'], 0, ',', '.') }}</span>
                            @endif
                            @if(!empty($quest['tokens']))
                                <span class="px-1.5 py-0.2 rounded bg-amber-500/10 text-amber-400 font-mono">+{{ $quest['tokens'] }} KluisMunt</span>
                            @endif
                        </div>

                        <!-- Progress Bar -->
                        <div class="space-y-1">
                            <div class="w-full bg-slate-100 dark:bg-purple-950/80 rounded-full h-2 overflow-hidden border border-slate-200 dark:border-purple-900/50">
                                <div class="h-full rounded-full transition-all duration-300 {{ $quest['is_claimed'] ? 'bg-emerald-500' : 'bg-linear-to-r from-orange-500 to-amber-500' }}" style="width: {{ $quest['percent'] }}%"></div>
                            </div>
                            <div class="flex items-center justify-between text-[10px] text-slate-400 font-medium">
                                <span>Voortgang</span>
                                <span class="font-bold text-slate-700 dark:text-purple-200">{{ $quest['current'] }} / {{ $quest['target'] }} ({{ $quest['percent'] }}%)</span>
                            </div>
                        </div>
                    </div>

                    <!-- Action Button -->
                    <div class="pt-2 border-t border-slate-100 dark:border-purple-900/30">
                        @if($quest['can_claim'])
                            <form action="{{ route('halloween.claim-quest', $quest['key']) }}" method="POST">
                                @csrf
                                <button type="submit" class="w-full py-2 rounded-xl bg-linear-to-r from-orange-600 to-amber-600 hover:from-orange-500 hover:to-amber-500 text-white font-black text-xs shadow-md shadow-orange-500/20 transition-all flex items-center justify-center gap-1.5 cursor-pointer">
                                    <span>🎃 Claim {{ $quest['seeds'] }} Pitten &raquo;</span>
                                </button>
                            </form>
                        @elseif($quest['is_claimed'])
                            <div class="text-center text-[11px] font-bold text-emerald-500 py-1 flex items-center justify-center gap-1">
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
                            <a href="{{ $link }}" class="w-full py-2 rounded-xl bg-slate-100 dark:bg-purple-950/60 hover:bg-slate-200 dark:hover:bg-purple-900/50 text-slate-700 dark:text-purple-200 font-bold text-xs transition text-center block">
                                {{ $btnLabel }}
                            </a>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- SECTION 2: WEKELIJKSE QUESTS -->
    <div class="space-y-3 pt-4">
        <div class="flex items-center justify-between pb-1 border-b border-slate-200 dark:border-purple-900/40">
            <div class="flex items-center gap-2">
                <span class="text-lg">👑</span>
                <h2 class="text-base font-black text-slate-900 dark:text-white">Wekelijkse Halloween Mijlpalen</h2>
            </div>
            <span class="text-[11px] font-bold text-orange-400 bg-orange-500/10 px-2 py-0.5 rounded-full border border-orange-500/20">
                Grote Beloningen t/m 5 November
            </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach($weeklyQuests as $quest)
                <div class="bg-white dark:bg-[#150a2b] rounded-2xl border p-5 flex flex-col justify-between gap-4 transition shadow-xs hover:border-orange-500/60 {{ $quest['is_claimed'] ? 'border-emerald-500/30 opacity-80' : ($quest['can_claim'] ? 'border-orange-500 shadow-orange-500/10 ring-2 ring-orange-500/30' : 'border-slate-200 dark:border-purple-900/40') }}">
                    <div class="space-y-3">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 rounded-2xl flex items-center justify-center text-2xl {{ $quest['is_claimed'] ? 'bg-emerald-500/20 text-emerald-400' : ($quest['is_completed'] ? 'bg-orange-500/20 text-orange-400' : 'bg-purple-100 dark:bg-purple-950/60 text-purple-600 dark:text-purple-300') }}">
                                    <i class="fa-solid {{ $quest['icon'] }}"></i>
                                </div>
                                <div>
                                    <h3 class="text-sm font-black text-slate-900 dark:text-white leading-snug">{{ $quest['title'] }}</h3>
                                    <span class="text-xs font-black text-orange-500 uppercase tracking-wider">+{{ $quest['seeds'] }} Pompoenpitten 🎃</span>
                                </div>
                            </div>

                            @if($quest['is_claimed'])
                                <span class="px-2.5 py-0.5 rounded-full bg-emerald-500/20 text-emerald-400 text-xs font-black border border-emerald-500/30 shrink-0">
                                    ✓ Geclaimd
                                </span>
                            @elseif($quest['can_claim'])
                                <span class="px-2.5 py-0.5 rounded-full bg-orange-500 text-white text-xs font-black animate-pulse shrink-0">
                                    Klaar!
                                </span>
                            @else
                                <span class="px-2.5 py-0.5 rounded-full bg-slate-100 dark:bg-purple-950 text-slate-500 dark:text-purple-300 text-xs font-bold shrink-0">
                                    {{ number_format($quest['current'], 0, ',', '.') }}/{{ number_format($quest['target'], 0, ',', '.') }}
                                </span>
                            @endif
                        </div>

                        <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed">{{ $quest['desc'] }}</p>

                        <!-- Extra rewards preview -->
                        <div class="flex items-center gap-2 flex-wrap text-xs font-bold text-slate-400 dark:text-purple-300/80">
                            <span>Beloningen:</span>
                            <span class="px-2 py-0.5 rounded-md bg-orange-500/10 text-orange-400 font-mono font-black">+{{ $quest['seeds'] }} 🎃</span>
                            @if(!empty($quest['xp']))
                                <span class="px-2 py-0.5 rounded-md bg-purple-500/10 text-purple-400 font-mono">+{{ $quest['xp'] }} XP</span>
                            @endif
                            @if(!empty($quest['respect']))
                                <span class="px-2 py-0.5 rounded-md bg-amber-500/10 text-amber-400 font-mono">+{{ $quest['respect'] }} Respect 🔥</span>
                            @endif
                        </div>

                        <!-- Progress Bar -->
                        <div class="space-y-1">
                            <div class="w-full bg-slate-100 dark:bg-purple-950/80 rounded-full h-2.5 overflow-hidden border border-slate-200 dark:border-purple-900/50">
                                <div class="h-full rounded-full transition-all duration-300 {{ $quest['is_claimed'] ? 'bg-emerald-500' : 'bg-linear-to-r from-orange-500 to-amber-500' }}" style="width: {{ $quest['percent'] }}%"></div>
                            </div>
                            <div class="flex items-center justify-between text-[11px] text-slate-400 font-medium">
                                <span>Wekelijkse Voortgang</span>
                                <span class="font-bold text-slate-700 dark:text-purple-200">{{ number_format($quest['current'], 0, ',', '.') }} / {{ number_format($quest['target'], 0, ',', '.') }} ({{ $quest['percent'] }}%)</span>
                            </div>
                        </div>
                    </div>

                    <!-- Action Button -->
                    <div class="pt-2 border-t border-slate-100 dark:border-purple-900/30">
                        @if($quest['can_claim'])
                            <form action="{{ route('halloween.claim-quest', $quest['key']) }}" method="POST">
                                @csrf
                                <button type="submit" class="w-full py-2.5 rounded-xl bg-linear-to-r from-orange-600 to-amber-600 hover:from-orange-500 hover:to-amber-500 text-white font-black text-xs shadow-md shadow-orange-500/20 transition-all flex items-center justify-center gap-1.5 cursor-pointer">
                                    <span>🎃 Claim {{ $quest['seeds'] }} Pompoenpitten &raquo;</span>
                                </button>
                            </form>
                        @elseif($quest['is_claimed'])
                            <div class="text-center text-xs font-bold text-emerald-500 py-1 flex items-center justify-center gap-1">
                                <i class="fa-solid fa-circle-check"></i>
                                <span>Wekelijkse beloning succesvol geclaimd</span>
                            </div>
                        @else
                            @php
                                $link = match($quest['category']) {
                                    'theft' => route('pickpocket.index'),
                                    'brawl_win' => route('fight.index'),
                                    'cash_earned' => route('theft.index'),
                                    'hustle' => route('hustle.index'),
                                    default => route('dashboard'),
                                };
                            @endphp
                            <a href="{{ $link }}" class="w-full py-2.5 rounded-xl bg-slate-100 dark:bg-purple-950/60 hover:bg-slate-200 dark:hover:bg-purple-900/50 text-slate-700 dark:text-purple-200 font-bold text-xs transition text-center block">
                                Ga naar Uitdaging &raquo;
                            </a>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
