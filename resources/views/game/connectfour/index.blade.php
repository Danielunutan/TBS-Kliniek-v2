@extends('layouts.app')

@section('title', 'Vier op \'n Rij met OOHPA Arjan - TBS Kliniek')

@section('content')
<style>
/* ==========================================================================
   CONNECT FOUR DEDICATED CSS GRID & 3D TOKEN ENGINE
   ========================================================================== */
.c4-board-grid {
    display: grid !important;
    grid-template-columns: repeat(7, minmax(0, 1fr)) !important;
    gap: 6px !important;
    width: 100% !important;
}
@media (min-width: 640px) {
    .c4-board-grid {
        gap: 8px !important;
    }
}

.c4-drop-row {
    display: grid !important;
    grid-template-columns: repeat(7, minmax(0, 1fr)) !important;
    gap: 6px !important;
    width: 100% !important;
}
@media (min-width: 640px) {
    .c4-drop-row {
        gap: 8px !important;
    }
}

/* 3D Physical Connect Four Tokens */
.token-base {
    width: 100%;
    height: 100%;
    border-radius: 9999px;
    position: relative;
    box-shadow: 0 3px 8px rgba(0, 0, 0, 0.4), inset 0 2px 3px rgba(255, 255, 255, 0.45), inset 0 -3px 5px rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}

/* Player 1: Paars (Purple TBS Coin) */
.token-player1 {
    background: radial-gradient(circle at 35% 30%, #c084fc 0%, #9333ea 55%, #581c87 100%);
    border: 2px solid #d8b4fe;
}
.token-player1 .token-inner {
    width: 72%;
    height: 72%;
    border-radius: 9999px;
    background: radial-gradient(circle, #2e1065 0%, #0f0728 100%);
    border: 1.5px solid rgba(192, 132, 252, 0.6);
    box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.85), 0 1px 2px rgba(255, 255, 255, 0.25);
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}

/* Player 2: Groen (Green OOHPA Arjan Coin) */
.token-player2 {
    background: radial-gradient(circle at 35% 30%, #7ef200 0%, #5ec800 55%, #387500 100%);
    border: 2px solid #92fa22;
}
.token-player2 .token-inner {
    width: 72%;
    height: 72%;
    border-radius: 9999px;
    background: radial-gradient(circle, #0e170c 0%, #060905 100%);
    border: 1.5px solid rgba(126, 242, 0, 0.6);
    box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.85), 0 1px 2px rgba(255, 255, 255, 0.25);
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}

/* Winning token pulse highlight */
.token-winning {
    animation: win-token-pulse 1s infinite alternate ease-in-out !important;
    box-shadow: 0 0 25px #a855f7, inset 0 0 12px rgba(255, 255, 255, 0.95) !important;
    border-color: #ffffff !important;
    z-index: 10;
}

@keyframes win-token-pulse {
    0% { transform: scale(0.95); filter: brightness(1); }
    100% { transform: scale(1.1); filter: brightness(1.35) drop-shadow(0 0 12px #c084fc); }
}

/* Connect 4 Board Socket - Clean Light Theme by default, Dark in Dark Mode */
.board-socket {
    background: radial-gradient(circle at 50% 50%, #ffffff 0%, #f1f5f9 65%, #e2e8f0 100%);
    box-shadow: inset 0 3px 6px rgba(0, 0, 0, 0.12), inset 0 1px 2px rgba(0, 0, 0, 0.08), 0 1px 1px rgba(255, 255, 255, 0.9);
    border: 2px solid #cbd5e1;
    transition: all 0.15s ease;
}
.board-socket:hover {
    border-color: #a855f7;
    box-shadow: inset 0 3px 6px rgba(0, 0, 0, 0.12), 0 0 10px rgba(168, 85, 247, 0.35);
}

/* Dark mode board sockets */
html.dark .board-socket,
body.dark .board-socket,
.dark .board-socket {
    background: radial-gradient(circle at 50% 50%, #07040d 0%, #0e081a 70%, #1a102f 100%);
    box-shadow: inset 0 4px 7px rgba(0, 0, 0, 0.95), inset 0 1px 2px rgba(0, 0, 0, 0.8), 0 1px 1px rgba(168, 85, 247, 0.2);
    border: 2px solid #1e1b4b;
}
html.dark .board-socket:hover,
body.dark .board-socket:hover,
.dark .board-socket:hover {
    border-color: #c084fc;
    box-shadow: inset 0 4px 7px rgba(0, 0, 0, 0.95), inset 0 0 8px rgba(168, 85, 247, 0.4), 0 0 4px rgba(168, 85, 247, 0.3);
}

/* Fast animated drop */
.token-dropping {
    animation: drop-fast 0.28s cubic-bezier(0.2, 0.9, 0.3, 1.1) forwards;
}

@keyframes drop-fast {
    0% { transform: translateY(-300px); opacity: 0.5; }
    80% { transform: translateY(0px) scale(1.05, 0.95); opacity: 1; }
    100% { transform: translateY(0px) scale(1, 1); }
}
</style>

<div class="space-y-6 select-none" id="c4-app"
     data-cooldown="{{ (int) ($cooldownRemaining ?? 0) }}"
     data-matches="{{ (int) ($stat->matches_played ?? 0) }}"
     data-player-wins="{{ (int) ($stat->current_player_wins ?? 0) }}"
     data-arjan-wins="{{ (int) ($stat->current_arjan_wins ?? 0) }}"
     data-draws="{{ (int) ($stat->current_draws ?? 0) }}">

    

    <!-- Header Banner -->
    <div class="rounded-2xl bg-white dark:bg-slate-900 p-6 sm:p-8 text-slate-800 dark:text-slate-100 shadow-xs border border-slate-200/90 dark:border-slate-800 relative overflow-hidden">
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="flex items-start sm:items-center gap-4">
                <div class="w-16 h-16 rounded-2xl overflow-hidden border-2 border-purple-300 dark:border-purple-700 shadow-md shrink-0 bg-purple-50 dark:bg-purple-950/70">
                    <img src="{{ asset('assets/characters/oohpa_arjan.jpg') }}?v=2" alt="OOHPA Arjan" class="w-full h-full object-cover">
                </div>
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="text-[10px] font-black uppercase tracking-widest text-purple-700 dark:text-purple-300 bg-purple-50 dark:bg-purple-950/80 px-2.5 py-0.5 rounded-md border border-purple-200/80 dark:border-purple-800/70">
                            Officiële Partner Minigame
                        </span>
                        <a href="https://oohpaarjan.nl/" target="_blank" rel="noopener" class="text-[10px] font-bold text-emerald-700 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-950/80 hover:bg-emerald-100 px-2 py-0.5 rounded-md border border-emerald-200/80 dark:border-emerald-800/70 transition inline-flex items-center gap-1">
                            <i class="fa-solid fa-arrow-up-right-from-square text-[9px]"></i> oohpaarjan.nl
                        </a>
                    </div>
                    <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight mt-1">
                        4 op 'n Rij met OOHPA Arjan
                    </h1>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 max-w-2xl leading-relaxed">
                        Schuif gezellig aan bij <strong>OOHPA Arjan</strong> aan de recreatietafel! Speel een serie van 5 potjes. Pak jij de Grand Slam en die dikke toernooibonus met <strong>10 Sterren ⭐</strong>?
                    </p>
                </div>
            </div>

            <!-- Stats Counters -->
            <div class="flex flex-wrap items-center gap-2 text-xs font-bold shrink-0">
                <span class="bg-purple-50 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 px-3.5 py-2 rounded-xl border border-purple-200/80 dark:border-purple-800/70 shadow-xs flex items-center gap-1.5">
                    <i class="fa-solid fa-trophy text-purple-600 dark:text-purple-400"></i>
                    <span>{{ $stat?->total_tournaments_won ?? 0 }} Toernooien Gewonnen</span>
                </span>
                <span class="bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 px-3.5 py-2 rounded-xl border border-emerald-200/80 dark:border-emerald-800/70 shadow-xs flex items-center gap-1.5">
                    <i class="fa-solid fa-sack-dollar text-emerald-600 dark:text-emerald-400"></i>
                    <span>€ {{ number_format($stat?->total_cash_won ?? 0, 0, ',', '.') }} Gewonnen</span>
                </span>
            </div>
        </div>
    </div>

    <!-- MAIN GRID: GAME BOARD (LEFT, DIRECTLY AT TOP) & SIDEBAR STATS (RIGHT) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

        <!-- LEFT COLUMN: ARENA & BOARD (8 COLS) - DIRECTLY AT TOP (NO SCROLLING NEEDED!) -->
        <div class="lg:col-span-8 space-y-4">

            <!-- COOLDOWN CONTAINER (SHOWN WHEN COOLDOWN > 0: 10 MINS OR AD SKIP) -->
            <div id="cooldown-container" class="{{ ($cooldownRemaining ?? 0) > 0 ? '' : 'hidden' }} bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/90 dark:border-slate-800 shadow-xs p-8 text-center space-y-6">
                <div class="w-24 h-24 mx-auto rounded-2xl overflow-hidden border-2 border-purple-400 dark:border-purple-600 shadow-md">
                    <img src="{{ asset('assets/characters/oohpa_arjan_transparent.png') }}?v=2" alt="OOHPA Arjan" class="w-full h-full object-contain bg-slate-50 dark:bg-slate-800">
                </div>
                <div class="space-y-2 max-w-md mx-auto">
                    <h3 class="text-xl font-black text-slate-900 dark:text-white">Arjan drinkt even een bakkie leeg!</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                        De serie van 5 potjes is voltooid! OOHPA Arjan moet zijn tactische brein even laten afkoelen (10 minuten cooldown). Over <strong id="cooldown-timer-text" class="text-purple-600 dark:text-purple-400">--:--</strong> staat hij weer aan het bord.
                    </p>
                </div>

                <!-- COOLDOWN SKIP BUTTON (STARS OR SPONSOR AD) -->
                <div class="p-5 rounded-xl bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70 max-w-md mx-auto space-y-3">
                    <div class="flex items-center justify-center gap-2 text-xs font-extrabold text-amber-800 dark:text-amber-300">
                        <i class="fa-solid fa-bolt text-amber-500 animate-bounce"></i>
                        <span>Geen zin om 10 minuten te wachten?</span>
                    </div>
                    <form action="{{ route('connect_four.skip_cooldown_stars') }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full py-3.5 px-6 rounded-xl bg-amber-500 hover:bg-amber-600 active:scale-95 text-slate-950 font-black text-xs transition-all duration-150 cursor-pointer shadow-xs flex items-center justify-center gap-2">
                            <i class="fa-solid fa-star"></i>
                            <span>Overslaan met 10 Sterren ⭐ (Direct Revanche)</span>
                        </button>
                    </form>
                    @if(\App\Http\Controllers\SponsorAdController::areAdsEnabled())
                    <form action="{{ route('connect_four.skip_cooldown_ad') }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full py-3.5 px-6 rounded-xl bg-purple-600 hover:bg-purple-700 dark:bg-purple-600 dark:hover:bg-purple-500 active:scale-95 text-white font-black text-xs transition-all duration-150 cursor-pointer shadow-xs flex items-center justify-center gap-2">
                            <i class="fa-solid fa-play"></i>
                            <span>Overslaan via Sponsor-Ad (Direct Revanche)</span>
                        </button>
                    </form>
                    @endif
                    <p class="text-[10px] text-slate-400">Sla de wachttijd direct over en start onmiddellijk weer een nieuwe serie van 5 potjes!</p>
                </div>
            </div>

            <!-- ACTIVE GAME ARENA (SHOWN WHEN COOLDOWN <= 0) -->
            <div id="game-container" class="{{ ($cooldownRemaining ?? 0) > 0 ? 'hidden' : '' }} space-y-4">

                <!-- 1. MATCH HUD (BALANCED ROW, NEVER SQUISHED) -->
                <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-3 sm:p-4 shadow-xs">
                    <div class="flex flex-col sm:flex-row items-center justify-between gap-3">
                        
                        <!-- Player 1 Card (Paars) -->
                        <div id="player1-card" class="w-full sm:w-auto flex-1 flex items-center justify-between p-2.5 rounded-xl bg-purple-50/90 dark:bg-slate-800/80 border-2 border-purple-500/80 dark:border-purple-500 transition-all duration-200">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <div class="relative shrink-0">
                                    <img src="{{ $character->avatar_url }}" alt="{{ $character->name }}" class="w-9 h-9 rounded-lg object-cover border border-purple-300">
                                    <span id="turn-dot-player1" class="absolute -top-1 -right-1 w-2.5 h-2.5 bg-purple-500 border-2 border-white dark:border-slate-900 rounded-full animate-ping"></span>
                                </div>
                                <div class="min-w-0">
                                    <div class="text-[10px] text-purple-700 dark:text-purple-300 font-extrabold uppercase tracking-wide">
                                        Jij (Paars)
                                    </div>
                                    <div class="text-xs font-black text-slate-900 dark:text-white truncate max-w-[110px] sm:max-w-[130px]">{{ $character->name }}</div>
                                </div>
                            </div>
                            <div class="text-right pl-2 border-l border-purple-200 dark:border-slate-700 shrink-0">
                                <div class="text-[9px] text-slate-400 uppercase font-bold">Winst</div>
                                <div id="hud-score-player1" class="text-base font-black text-purple-600 dark:text-purple-400 tabular-nums leading-none">{{ $stat->current_player_wins ?? 0 }}</div>
                            </div>
                        </div>

                        <!-- Center Turn Status & Timer Bar -->
                        <div class="w-full sm:w-48 shrink-0 flex flex-col justify-between py-1 px-2 space-y-1.5 text-center">
                            <div class="flex items-center justify-between text-xs font-black">
                                <span id="turn-banner" class="flex items-center gap-1.5 truncate text-slate-800 dark:text-slate-200">
                                    <span class="w-2 h-2 rounded-full bg-purple-500 animate-pulse shrink-0"></span>
                                    <span class="truncate">Jouw beurt</span>
                                </span>
                                <span id="turn-timer-badge" class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-md bg-purple-50 dark:bg-purple-950/80 border border-purple-200/80 dark:border-purple-800 text-purple-700 dark:text-purple-300 font-mono font-black text-[11px] shrink-0">
                                    <i class="fa-solid fa-stopwatch text-[10px]"></i>
                                    <span id="timer-seconds">15</span>s
                                </span>
                            </div>
                            <div class="w-full bg-slate-100 dark:bg-slate-800 h-1.5 rounded-full overflow-hidden">
                                <div id="turn-timer-bar" class="h-full bg-purple-600 transition-all duration-300" style="width: 100%;"></div>
                            </div>
                        </div>

                        <!-- Player 2 Card (Groen Arjan) -->
                        <div id="player2-card" class="w-full sm:w-auto flex-1 flex items-center justify-between p-2.5 rounded-xl bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70 opacity-75 transition-all duration-200">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <div class="relative shrink-0">
                                    <img src="{{ asset('assets/characters/oohpa_arjan.jpg') }}?v=2" alt="OOHPA Arjan" class="w-9 h-9 rounded-lg object-cover border border-emerald-300">
                                    <span id="turn-dot-player2" class="hidden absolute -top-1 -right-1 w-2.5 h-2.5 bg-emerald-500 border-2 border-white dark:border-slate-900 rounded-full animate-ping"></span>
                                </div>
                                <div class="min-w-0">
                                    <div class="text-[10px] text-emerald-700 dark:text-emerald-400 font-extrabold uppercase tracking-wide">
                                        Arjan (Groen)
                                    </div>
                                    <div class="text-xs font-black text-slate-900 dark:text-white truncate">OOHPA Arjan</div>
                                </div>
                            </div>
                            <div class="text-right pl-2 border-l border-slate-200 dark:border-slate-700 shrink-0">
                                <div class="text-[9px] text-slate-400 uppercase font-bold">Winst</div>
                                <div id="hud-score-player2" class="text-base font-black text-emerald-600 dark:text-emerald-400 tabular-nums leading-none">{{ $stat->current_arjan_wins ?? 0 }}</div>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- 2. THE CONNECT FOUR BOARD (WEBSITE STYLING - SEAMLESS LIGHT & DARK THEME) -->
                <div class="bg-white dark:bg-slate-900 rounded-2xl p-4 sm:p-6 shadow-xs border border-slate-200/90 dark:border-slate-800">
                    
                    <!-- Top Controls Bar (Sound, Aftrap & Restart) -->
                    <div class="flex items-center justify-between pb-3 mb-3 border-b border-slate-100 dark:border-slate-800 text-xs">
                        <div class="flex items-center gap-2">
                            <button type="button" id="sound-toggle-btn" onclick="toggleSound()" class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 border border-slate-200/80 dark:border-slate-700 font-bold transition-all duration-150 active:scale-95 flex items-center gap-1.5 cursor-pointer">
                                <i id="sound-icon" class="fa-solid fa-volume-high text-purple-600 dark:text-purple-400"></i>
                                <span id="sound-label">Geluid Aan</span>
                            </button>
                            <span id="starter-indicator" class="text-[11px] font-bold text-purple-700 dark:text-purple-300 bg-purple-50 dark:bg-purple-950/80 px-2.5 py-1 rounded-lg border border-purple-200/80 dark:border-purple-800/70">
                                Aftrap: Jij 🎲
                            </span>
                        </div>
                        <button type="button" onclick="restartMatch()" id="restart-btn" class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 border border-slate-200/80 dark:border-slate-700 font-bold transition-all duration-150 active:scale-95 flex items-center gap-1.5 cursor-pointer">
                            <i class="fa-solid fa-rotate-left text-slate-500"></i>
                            <span>Herstart Potje</span>
                        </button>
                    </div>

                    <!-- Column Drop Selector Buttons (Top Row: 7 buttons) -->
                    <div class="c4-drop-row mb-2.5">
                        @for ($col = 0; $col < 7; $col++)
                            <button type="button" 
                                    onclick="handleColumnClick({{ $col }})" 
                                    onmouseenter="handleColumnHover({{ $col }})" 
                                    onmouseleave="handleColumnLeave({{ $col }})"
                                    id="col-btn-{{ $col }}"
                                    class="col-drop-btn h-9 sm:h-10 rounded-xl bg-slate-100 hover:bg-purple-600 dark:bg-slate-800 dark:hover:bg-purple-600 border border-slate-200/80 dark:border-slate-700 hover:border-purple-600 dark:hover:border-purple-500 flex items-center justify-center text-slate-700 hover:text-white dark:text-slate-300 dark:hover:text-white transition-all cursor-pointer focus:outline-none shadow-xs group"
                                    title="Munt laten vallen in kolom {{ $col + 1 }}">
                                <i class="fa-solid fa-chevron-down text-xs transition-transform group-hover:translate-y-0.5"></i>
                            </button>
                        @endfor
                    </div>

                    <!-- 7x6 Grid Board Frame -->
                    <div class="board-frame bg-slate-100 dark:bg-slate-950 p-2.5 sm:p-4 rounded-2xl border border-slate-200/90 dark:border-slate-800 shadow-inner">
                        <div id="board-grid" class="c4-board-grid">
                            @for ($row = 0; $row < 6; $row++)
                                @for ($col = 0; $col < 7; $col++)
                                    <div id="cell-{{ $row }}-{{ $col }}" 
                                          onclick="handleColumnClick({{ $col }})"
                                          onmouseenter="handleColumnHover({{ $col }})"
                                          onmouseleave="handleColumnLeave({{ $col }})"
                                          class="aspect-square rounded-full board-socket p-1 flex items-center justify-center cursor-pointer relative overflow-hidden">
                                        <!-- Injected 3D Coin -->
                                    </div>
                                @endfor
                            @endfor
                        </div>
                    </div>

                    <!-- Bottom Legend -->
                    <div class="mt-3.5 pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs text-slate-500 dark:text-slate-400 font-bold">
                        <div class="flex items-center gap-4">
                            <span class="flex items-center gap-1.5">
                                <span class="w-3.5 h-3.5 rounded-full bg-purple-600 border border-purple-300 inline-block shadow-2xs"></span>
                                <span class="text-slate-700 dark:text-slate-200">Jij (Paars)</span>
                            </span>
                            <span class="flex items-center gap-1.5">
                                <span class="w-3.5 h-3.5 rounded-full bg-emerald-500 border border-emerald-300 inline-block shadow-2xs"></span>
                                <span class="text-slate-700 dark:text-slate-200">OOHPA Arjan (Groen)</span>
                            </span>
                        </div>
                        <div class="text-[11px] text-slate-500 dark:text-slate-400">
                            Gelijkspel: <span id="score-ties" class="text-slate-900 dark:text-white font-black">{{ $stat->current_draws ?? 0 }}</span>
                        </div>
                    </div>

                </div>

            </div>

        </div>

        <!-- RIGHT COLUMN: SIDEBAR WITH TOURNAMENT STATS, ARJAN LIVE CHAT & BONUSES (4 COLS) -->
        <div class="lg:col-span-4 space-y-5">

            <!-- 1. TOURNAMENT SCORECARD (MOVED TO RIGHT SIDEBAR SO BOARD SITS DIRECTLY AT TOP!) -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/90 dark:border-slate-800 shadow-xs p-5 space-y-3">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-chess-board text-purple-600 dark:text-purple-400 text-base"></i>
                        <h2 class="text-xs font-black text-slate-900 dark:text-white uppercase tracking-wider">
                            Toernooi Stand (5 Potjes)
                        </h2>
                    </div>
                    <span id="match-progress-badge" class="text-[11px] font-black px-2 py-0.5 rounded-lg bg-purple-50 dark:bg-purple-950/80 text-purple-700 dark:text-purple-300 border border-purple-200/80 dark:border-purple-800/70">
                        Potje {{ min(5, (int) ($stat->matches_played ?? 0) + 1) }} van 5
                    </span>
                </div>

                <div class="grid grid-cols-3 gap-2.5 text-center">
                    <!-- Player Wins -->
                    <div class="p-2.5 rounded-xl bg-purple-50/80 dark:bg-purple-950/40 border border-purple-200/80 dark:border-purple-800/60">
                        <span class="text-[9px] font-bold uppercase text-purple-700 dark:text-purple-300 block">Jij (Paars)</span>
                        <span class="text-lg font-black text-purple-900 dark:text-purple-100" id="stat-player-wins">{{ $stat->current_player_wins ?? 0 }}</span>
                    </div>
                    <!-- Draws -->
                    <div class="p-2.5 rounded-xl bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70">
                        <span class="text-[9px] font-bold uppercase text-slate-500 dark:text-slate-400 block">Gelijk</span>
                        <span class="text-lg font-black text-slate-700 dark:text-slate-300" id="stat-draws">{{ $stat->current_draws ?? 0 }}</span>
                    </div>
                    <!-- Arjan Wins -->
                    <div class="p-2.5 rounded-xl bg-emerald-50/80 dark:bg-emerald-950/40 border border-emerald-200/80 dark:border-emerald-800/60">
                        <span class="text-[9px] font-bold uppercase text-emerald-700 dark:text-emerald-300 block">Arjan (Groen)</span>
                        <span class="text-lg font-black text-emerald-900 dark:text-emerald-100" id="stat-arjan-wins">{{ $stat->current_arjan_wins ?? 0 }}</span>
                    </div>
                </div>
            </div>

            <!-- 2. OOHPA ARJAN LIVE INTERACTION CARD -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/90 dark:border-slate-800 shadow-xs p-5 space-y-3.5">
                <div class="flex items-center gap-3">
                    <div class="w-14 h-14 rounded-2xl overflow-hidden border-2 border-purple-300 dark:border-purple-700 shadow-sm shrink-0 bg-purple-50 dark:bg-purple-950">
                        <img src="{{ asset('assets/characters/oohpa_arjan_transparent.png') }}?v=2" alt="OOHPA Arjan" class="w-full h-full object-contain">
                    </div>
                    <div>
                        <div class="flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span>
                            <span class="text-[10px] font-black uppercase tracking-wider text-purple-700 dark:text-purple-300">Aanwezig aan Tafel</span>
                        </div>
                        <h3 class="text-sm font-black text-slate-900 dark:text-white">OOHPA Arjan</h3>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 font-bold">4 op 'n Rij Veteraan</p>
                    </div>
                </div>

                <!-- Reactive Speech Bubble -->
                <div class="p-3 rounded-xl bg-purple-50/80 dark:bg-slate-800/80 border border-purple-200/80 dark:border-purple-800/60 relative">
                    <div class="absolute -top-2 left-6 w-3 h-3 bg-purple-50 dark:bg-slate-800 border-t border-l border-purple-200/80 dark:border-purple-800/60 rotate-45"></div>
                    <p id="arjan-speech" class="text-xs text-purple-950 dark:text-purple-200 font-medium leading-relaxed italic">
                        &ldquo;{{ $quotes['intro'] ?? 'Laat zien wat je kunt!' }}&rdquo;
                    </p>
                </div>

                <!-- Arjan's Channel & Social Link -->
                <div class="pt-1.5 border-t border-slate-100 dark:border-slate-800">
                    <a href="https://oohpaarjan.nl/" target="_blank" rel="noopener" class="w-full py-2 px-3 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 border border-slate-200/80 dark:border-slate-700 text-xs font-bold transition-all duration-150 active:scale-95 flex items-center justify-between">
                        <span class="flex items-center gap-2">
                            <i class="fa-solid fa-tv text-purple-600"></i> Bekijk OOHPA's Livestream
                        </span>
                        <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i>
                    </a>
                </div>
            </div>

            <!-- 3. TOURNAMENT BONUSES BREAKDOWN (STERREN ⭐ INSTEAD OF VIP) -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/90 dark:border-slate-800 shadow-xs p-5 space-y-3.5">
                <div class="flex items-center gap-2 border-b border-slate-100 dark:border-slate-800 pb-3">
                    <i class="fa-solid fa-gift text-amber-500"></i>
                    <h3 class="text-xs font-black uppercase tracking-wider text-slate-900 dark:text-white">Toernooibonussen (5 Potjes)</h3>
                </div>

                <div class="space-y-2 text-xs">
                    <div class="p-2.5 rounded-xl bg-amber-50/80 dark:bg-slate-800/60 border border-amber-200/80 dark:border-amber-800/60 flex items-center justify-between">
                        <div>
                            <span class="font-extrabold text-amber-950 dark:text-amber-200 block">5 - 0 Grand Slam</span>
                            <span class="text-[10px] text-amber-700 dark:text-amber-400">Onverslagen meester</span>
                        </div>
                        <span class="font-black text-emerald-600 dark:text-emerald-400 text-right">+€ 100.000<br><span class="text-[9px] text-amber-600 dark:text-amber-400 font-extrabold">+10 Sterren ⭐</span></span>
                    </div>

                    <div class="p-2.5 rounded-xl bg-purple-50/80 dark:bg-slate-800/60 border border-purple-200/80 dark:border-purple-800/60 flex items-center justify-between">
                        <div>
                            <span class="font-extrabold text-purple-950 dark:text-purple-200 block">4 - 1 Toernooiwinst</span>
                            <span class="text-[10px] text-purple-700 dark:text-purple-400">Duidelijke overmacht</span>
                        </div>
                        <span class="font-black text-emerald-600 dark:text-emerald-400 text-right">+€ 50.000<br><span class="text-[9px] text-purple-600 dark:text-purple-400">+100 XP, +30 Resp</span></span>
                    </div>

                    <div class="p-2.5 rounded-xl bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70 flex items-center justify-between">
                        <div>
                            <span class="font-extrabold text-slate-900 dark:text-slate-100 block">3 - 2 Serie Winst</span>
                            <span class="text-[10px] text-slate-500">Nipte voorsprong</span>
                        </div>
                        <span class="font-black text-emerald-600 dark:text-emerald-400 text-right">+€ 25.000<br><span class="text-[9px] text-purple-600 dark:text-purple-400">+60 XP, +15 Resp</span></span>
                    </div>
                </div>

                <p class="text-[10px] text-slate-400 italic text-center">
                    Per gewonnen potje ontvang je direct € 5.000 cash, +10 XP en +3 Respect!
                </p>
            </div>

        </div>

    </div>

    <!-- MATCH OVER MODAL -->
    <div id="match-modal" class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-xs flex items-center justify-center p-4 pb-24 lg:pb-4 hidden">
        <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 max-w-sm w-full max-h-[85dvh] overflow-y-auto p-6 text-center rounded-2xl shadow-xl relative space-y-4 animate-in fade-in zoom-in-95 duration-200">
            <div id="match-modal-icon" class="text-5xl">🏆</div>
            <div>
                <h3 id="match-modal-title" class="text-xl font-black text-slate-900 dark:text-white">Gefeliciteerd!</h3>
                <p id="match-modal-desc" class="text-xs text-slate-600 dark:text-slate-300 mt-1 leading-relaxed">
                    Je hebt 4 op 'n rij gemaakt en Arjan verslagen!
                </p>
            </div>
            <div id="match-modal-rewards" class="p-3 rounded-xl bg-purple-50 dark:bg-purple-950/60 border border-purple-200/80 dark:border-purple-800/70 text-xs font-black text-purple-700 dark:text-purple-300">
                +€ 5.000 Cash • +10 XP • +3 Respect
            </div>
            <button type="button" onclick="closeMatchModalAndContinue()" class="w-full py-3 rounded-xl bg-purple-600 hover:bg-purple-700 dark:bg-purple-600 dark:hover:bg-purple-500 text-white font-black text-xs transition-all duration-150 active:scale-95 cursor-pointer shadow-xs flex items-center justify-center gap-2">
                <i class="fa-solid fa-play"></i>
                <span id="match-modal-btn-text">Volgend Potje Spelen</span>
            </button>
        </div>
    </div>

    <!-- TOURNAMENT SUMMARY MODAL (5 MATCHES FINISHED) -->
    <div id="tournament-modal" class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-xs flex items-center justify-center p-4 pb-24 lg:pb-4 hidden">
        <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl max-w-md w-full max-h-[85dvh] overflow-y-auto p-6 sm:p-8 shadow-xl text-center space-y-5 animate-in fade-in zoom-in-95 duration-200">
            <div id="tourney-icon" class="w-16 h-16 mx-auto rounded-2xl flex items-center justify-center text-3xl shadow-xs bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 border border-emerald-200/80 dark:border-emerald-800/70">
                <i class="fa-solid fa-trophy"></i>
            </div>
            <div class="space-y-1">
                <h3 id="tourney-title" class="text-xl font-black text-slate-900 dark:text-white">Toernooi Voltooid!</h3>
                <p id="tourney-desc" class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed"></p>
            </div>

            <div id="tourney-score" class="p-3 rounded-xl bg-purple-50 dark:bg-purple-950/60 border border-purple-200/80 dark:border-purple-800/70 text-xs font-black text-purple-700 dark:text-purple-300">
                Eindstand: 0 - 0 (Gelijk: 0)
            </div>

            <form action="{{ route('connect_four.skip_cooldown_stars') }}" method="POST">
                @csrf
                <button type="submit" class="w-full py-3.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-slate-950 font-black text-xs transition-all duration-150 active:scale-95 cursor-pointer shadow-xs flex items-center justify-center gap-2">
                    <i class="fa-solid fa-star"></i>
                    <span>Direct Revanche voor 10 Sterren ⭐</span>
                </button>
            </form>

            @if(\App\Http\Controllers\SponsorAdController::areAdsEnabled())
            <form action="{{ route('connect_four.skip_cooldown_ad') }}" method="POST">
                @csrf
                <button type="submit" class="w-full py-3.5 rounded-xl bg-purple-600 hover:bg-purple-700 dark:bg-purple-600 dark:hover:bg-purple-500 text-white font-black text-xs transition-all duration-150 active:scale-95 cursor-pointer shadow-xs flex items-center justify-center gap-2">
                    <i class="fa-solid fa-play"></i>
                    <span>Direct Revanche via Sponsor-Ad</span>
                </button>
            </form>
            @endif

            <button type="button" onclick="closeTournamentModal()" class="w-full py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 border border-slate-200/80 dark:border-slate-700 font-bold text-xs transition-all duration-150 active:scale-95 cursor-pointer">
                Sluiten & Cooldown Afwachten
            </button>
        </div>
    </div>

</div>

<!-- ==========================================================================
     JAVASCRIPT: OOPHAARJAN GAME ENGINE WITH TBS KLINIEK INTEGRATION
     ========================================================================== -->
<script>
(function() {
    // Game Constants
    const ROWS = 6;
    const COLS = 7;
    const EMPTY = 0;
    const PLAYER = 1; // Speler (Paars)
    const AI = 2;     // OOHPA Arjan (Groen)
    const TURN_TIME_LIMIT = 15; // 15s

    // Asset URLs
    const tbsLogoUrl = "{{ asset('assets/logo.png') }}";
    const oohpaLogoUrl = "{{ asset('assets/characters/oohpa_logo.webp') }}";

    // Application State from DOM data-attributes
    const appEl = document.getElementById('c4-app');
    let cooldownRemaining = parseInt(appEl.dataset.cooldown || '0', 10);
    let matchesPlayed = parseInt(appEl.dataset.matches || '0', 10);
    let playerWins = parseInt(appEl.dataset.playerWins || '0', 10);
    let arjanWins = parseInt(appEl.dataset.arjanWins || '0', 10);
    let draws = parseInt(appEl.dataset.draws || '0', 10);

    // Board & Match State
    let board = Array(ROWS).fill(null).map(() => Array(COLS).fill(EMPTY));
    let currentPlayer = PLAYER;
    let startingPlayer = PLAYER;
    let isGameOver = false;
    let isAiThinking = false;
    let isDropping = false;
    let moveCount = 0;
    let soundEnabled = true;
    let turnTimeLeft = TURN_TIME_LIMIT;
    let turnTimerInterval = null;
    let aiMoveTimeout = null;
    let cooldownTimerInterval = null;

    // Web Audio Synthesis Context
    let audioCtx = null;
    function getAudioContext() {
        if (!audioCtx) {
            const AudioContext = window.AudioContext || window.webkitAudioContext;
            if (AudioContext) audioCtx = new AudioContext();
        }
        if (audioCtx && audioCtx.state === 'suspended') {
            audioCtx.resume();
        }
        return audioCtx;
    }

    // Synthesized Sound Effects (Pure Web Audio API)
    function playSound(type, pitchModifier = 1) {
        if (!soundEnabled) return;
        try {
            const ctx = getAudioContext();
            if (!ctx) return;

            if (type === 'drop') {
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();
                osc.type = 'triangle';
                const baseFreq = 260 + (pitchModifier * 30);
                osc.frequency.setValueAtTime(baseFreq, ctx.currentTime);
                osc.frequency.exponentialRampToValueAtTime(baseFreq * 0.5, ctx.currentTime + 0.1);
                gain.gain.setValueAtTime(0.2, ctx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.1);
                osc.connect(gain);
                gain.connect(ctx.destination);
                osc.start();
                osc.stop(ctx.currentTime + 0.1);
            } else if (type === 'win') {
                const notes = [261.6, 329.6, 392.0, 523.3];
                notes.forEach((freq, i) => {
                    const osc = ctx.createOscillator();
                    const gain = ctx.createGain();
                    osc.type = 'sine';
                    osc.frequency.value = freq;
                    const start = ctx.currentTime + (i * 0.08);
                    gain.gain.setValueAtTime(0, start);
                    gain.gain.linearRampToValueAtTime(0.2, start + 0.02);
                    gain.gain.exponentialRampToValueAtTime(0.001, start + 0.28);
                    osc.connect(gain);
                    gain.connect(ctx.destination);
                    osc.start(start);
                    osc.stop(start + 0.28);
                });
            } else if (type === 'lose') {
                const notes = [392.0, 329.6, 261.6, 196.0];
                notes.forEach((freq, i) => {
                    const osc = ctx.createOscillator();
                    const gain = ctx.createGain();
                    osc.type = 'sawtooth';
                    osc.frequency.value = freq;
                    const start = ctx.currentTime + (i * 0.1);
                    gain.gain.setValueAtTime(0.12, start);
                    gain.gain.exponentialRampToValueAtTime(0.001, start + 0.22);
                    osc.connect(gain);
                    gain.connect(ctx.destination);
                    osc.start(start);
                    osc.stop(start + 0.22);
                });
            } else if (type === 'tick') {
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();
                osc.type = 'square';
                osc.frequency.setValueAtTime(800, ctx.currentTime);
                gain.gain.setValueAtTime(0.06, ctx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.05);
                osc.connect(gain);
                gain.connect(ctx.destination);
                osc.start();
                osc.stop(ctx.currentTime + 0.05);
            } else if (type === 'timeout') {
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();
                osc.type = 'sawtooth';
                osc.frequency.setValueAtTime(160, ctx.currentTime);
                gain.gain.setValueAtTime(0.2, ctx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.28);
                osc.connect(gain);
                gain.connect(ctx.destination);
                osc.start();
                osc.stop(ctx.currentTime + 0.28);
            }
        } catch (e) {
            // Audio context safely suppressed if user hasn't interacted
        }
    }

    // Reactive Speech Quotes
    const quotes = {
        intro: "Gezellig dat je aanschuift! Vijf potjes om te bepalen wie de echte meester is. Zet 'm op!",
        playerTurn: [
            "Wat is je volgende zet, makker?",
            "Even goed kijken waar je 'm laat vallen!",
            "Ik houd je scherp in de gaten...",
            "Pas maar op voor mijn volgende tegenzet!"
        ],
        aiBlock: [
            "Ho ho ho, dacht je dat ik die open liet staan?",
            "Mooi niet! Die diagonale lijn blokkeer ik direct!",
            "OOHPA let altijd op, die streep zet ik dicht!",
            "Geen vier op 'n rij voor jou via deze kolom!"
        ],
        aiMove: [
            "Zo, die staat als een huis!",
            "Eens kijken hoe jij hierop reageert...",
            "Kijk 'ns aan, tactisch pareltje van OOHPA!",
            "De druk wordt opgevoerd aan tafel!"
        ],
        playerWin: "Potdikkie, die zag ik even niet aankomen! Netjes gespeeld, jij pakt dit potje!",
        aiWin: "Hoppa! Vier op 'n rij voor OOHPA! Je moet scherper opletten op de diagonalen, makker!",
        draw: "Een gelijkspel aan het bord! We zaten elkaar aardig in de weg, haha!"
    };

    function setArjanSpeech(text) {
        const el = document.getElementById('arjan-speech');
        if (el) el.textContent = '“' + text + '”';
    }

    function getRandomQuote(key) {
        const list = quotes[key];
        if (Array.isArray(list)) {
            return list[Math.floor(Math.random() * list.length)];
        }
        return list || '';
    }

    // Turn Timer System
    function startTurnTimer() {
        stopTurnTimer();
        turnTimeLeft = TURN_TIME_LIMIT;
        updateTimerHUD(turnTimeLeft, TURN_TIME_LIMIT, true);

        turnTimerInterval = setInterval(() => {
            turnTimeLeft--;
            updateTimerHUD(turnTimeLeft, TURN_TIME_LIMIT, true);

            if (turnTimeLeft <= 3 && turnTimeLeft > 0) {
                playSound('tick');
            }

            if (turnTimeLeft <= 0) {
                stopTurnTimer();
                handleTurnTimeout();
            }
        }, 1000);
    }

    function stopTurnTimer() {
        if (turnTimerInterval) {
            clearInterval(turnTimerInterval);
            turnTimerInterval = null;
        }
    }

    function updateTimerHUD(remaining, total, isActive = true) {
        const timerText = document.getElementById('timer-seconds');
        const timerBadge = document.getElementById('turn-timer-badge');
        const timerBar = document.getElementById('turn-timer-bar');
        if (!timerText || !timerBadge || !timerBar) return;

        if (!isActive) {
            timerBadge.className = 'inline-flex items-center gap-1 px-1.5 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-400 font-mono font-bold text-[11px] shrink-0';
            timerText.innerText = '--';
            timerBar.style.width = '0%';
            return;
        }

        timerText.innerText = Math.max(0, remaining);
        const pct = Math.max(0, (remaining / total) * 100);
        timerBar.style.width = pct + '%';

        if (remaining <= 3) {
            timerBadge.className = 'inline-flex items-center gap-1 px-1.5 py-0.5 rounded-md bg-red-500/20 border border-red-500 text-red-600 dark:text-red-400 font-mono font-black text-[11px] shrink-0 animate-pulse';
            timerBar.className = 'h-full bg-red-500 transition-all duration-300';
        } else if (remaining <= 6) {
            timerBadge.className = 'inline-flex items-center gap-1 px-1.5 py-0.5 rounded-md bg-amber-500/20 border border-amber-500 text-amber-600 dark:text-amber-400 font-mono font-black text-[11px] shrink-0';
            timerBar.className = 'h-full bg-amber-500 transition-all duration-300';
        } else {
            timerBadge.className = 'inline-flex items-center gap-1 px-1.5 py-0.5 rounded-md bg-purple-50 dark:bg-purple-950/80 border border-purple-200 dark:border-purple-800 text-purple-700 dark:text-purple-300 font-mono font-black text-[11px] shrink-0';
            timerBar.className = 'h-full bg-gradient-to-r from-purple-500 to-indigo-500 transition-all duration-300';
        }
    }

    function handleTurnTimeout() {
        if (isGameOver || isAiThinking || isDropping || currentPlayer !== PLAYER) return;
        setArjanSpeech("Tijd is om! Willekeurige munt!");
        playSound('timeout');

        const validCols = [];
        for (let c = 0; c < COLS; c++) {
            if (getLowestEmptyRow(board, c) !== -1) validCols.push(c);
        }
        if (validCols.length > 0) {
            const randomCol = validCols[Math.floor(Math.random() * validCols.length)];
            window.handleColumnClick(randomCol);
        }
    }

    // HUD Turn Indicator
    function updateTurnHUD(customBannerText = null) {
        const p1Card = document.getElementById('player1-card');
        const p2Card = document.getElementById('player2-card');
        const p1Dot = document.getElementById('turn-dot-player1');
        const p2Dot = document.getElementById('turn-dot-player2');
        const banner = document.getElementById('turn-banner');

        if (currentPlayer === PLAYER) {
            p1Card.className = 'w-full sm:w-auto flex-1 flex items-center justify-between p-2 rounded-xl bg-purple-50/90 dark:bg-slate-800/90 border-2 border-purple-500 shadow-2xs transition-all duration-200';
            p2Card.className = 'w-full sm:w-auto flex-1 flex items-center justify-between p-2 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-700 opacity-65 transition-all duration-200';
            p1Dot.classList.remove('hidden');
            p2Dot.classList.add('hidden');
            banner.innerHTML = `<span class="w-2 h-2 rounded-full bg-purple-500 animate-pulse shrink-0"></span><span class="truncate">${customBannerText || 'Jouw beurt (Paars)'}</span>`;
        } else {
            p1Card.className = 'w-full sm:w-auto flex-1 flex items-center justify-between p-2 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-700 opacity-65 transition-all duration-200';
            p2Card.className = 'w-full sm:w-auto flex-1 flex items-center justify-between p-2 rounded-xl bg-emerald-50/90 dark:bg-slate-800/90 border-2 border-emerald-500 shadow-2xs transition-all duration-200';
            p1Dot.classList.add('hidden');
            p2Dot.classList.remove('hidden');
            banner.innerHTML = `<span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse shrink-0"></span><span class="truncate">${customBannerText || 'Arjan denkt na...'}</span>`;
        }
    }

    // Initialize Board
    function initBoard(starter = startingPlayer) {
        stopTurnTimer();
        if (aiMoveTimeout) {
            clearTimeout(aiMoveTimeout);
            aiMoveTimeout = null;
        }

        board = Array(ROWS).fill(null).map(() => Array(COLS).fill(EMPTY));
        currentPlayer = starter;
        isGameOver = false;
        isAiThinking = false;
        isDropping = false;
        moveCount = 0;

        // Clear visual cells
        for (let r = 0; r < ROWS; r++) {
            for (let c = 0; c < COLS; c++) {
                const cell = document.getElementById(`cell-${r}-${c}`);
                if (cell) cell.innerHTML = '';
            }
        }

        // Indicator
        const indicator = document.getElementById('starter-indicator');
        if (indicator) {
            if (starter === PLAYER) {
                indicator.innerText = 'Aftrap: Jij 🎲';
                indicator.className = 'text-[11px] font-bold text-purple-700 dark:text-purple-300 bg-purple-50 dark:bg-purple-950/80 px-2.5 py-1 rounded-lg border border-purple-200 dark:border-purple-800';
            } else {
                indicator.innerText = 'Aftrap: Arjan 🎲';
                indicator.className = 'text-[11px] font-bold text-emerald-700 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-950/80 px-2.5 py-1 rounded-lg border border-emerald-200 dark:border-emerald-800';
            }
        }

        if (starter === PLAYER) {
            updateTurnHUD('Jouw beurt (Aftrap)');
            startTurnTimer();
            setArjanSpeech(quotes.intro);
        } else {
            isAiThinking = true;
            updateTurnHUD('Arjan trapt dit potje af...');
            updateTimerHUD(0, 15, false);
            setArjanSpeech("Ik begin dit potje! Eens even zien...");
            aiMoveTimeout = setTimeout(() => {
                aiMoveTimeout = null;
                if (!isGameOver && !isDropping && currentPlayer === AI) {
                    makeAiMove();
                }
            }, 750);
        }
    }

    function getLowestEmptyRow(b, c) {
        for (let r = ROWS - 1; r >= 0; r--) {
            if (b[r][c] === EMPTY) return r;
        }
        return -1;
    }

    // Token Injection with 3D Emboss & Drop Physics
    function dropToken(row, col, player, callback) {
        board[row][col] = player;
        moveCount++;
        const cell = document.getElementById(`cell-${row}-${col}`);
        if (!cell) return;

        const token = document.createElement('div');
        token.className = `token-base ${player === PLAYER ? 'token-player1' : 'token-player2'} token-dropping`;

        const inner = document.createElement('div');
        inner.className = 'token-inner';

        const img = document.createElement('img');
        img.src = player === PLAYER ? tbsLogoUrl : oohpaLogoUrl;
        img.alt = player === PLAYER ? 'TBS' : 'OOHPA';
        img.className = 'w-4 h-4 sm:w-5 sm:h-5 object-contain pointer-events-none drop-shadow-sm';
        inner.appendChild(img);

        token.appendChild(inner);
        cell.appendChild(token);

        playSound('drop', 6 - row);

        setTimeout(() => {
            token.classList.remove('token-dropping');
            if (callback) callback();
        }, 280);
    }

    // Column Hover Handlers
    window.handleColumnHover = function(col) {
        if (isGameOver || isAiThinking || isDropping || currentPlayer !== PLAYER) return;
        const btn = document.getElementById(`col-btn-${col}`);
        if (btn) {
            btn.classList.add('bg-purple-600', 'text-white', 'border-purple-600', 'dark:bg-purple-600', 'dark:text-white', 'dark:border-purple-500');
            btn.classList.remove('bg-purple-50', 'text-purple-700', 'border-purple-200', 'dark:bg-slate-800', 'dark:text-purple-300', 'dark:border-slate-700');
        }
    };

    window.handleColumnLeave = function(col) {
        const btn = document.getElementById(`col-btn-${col}`);
        if (btn) {
            btn.classList.remove('bg-purple-600', 'text-white', 'border-purple-600', 'dark:bg-purple-600', 'dark:text-white', 'dark:border-purple-500');
            btn.classList.add('bg-purple-50', 'text-purple-700', 'border-purple-200', 'dark:bg-slate-800', 'dark:text-purple-300', 'dark:border-slate-700');
        }
    };

    // Strict Click Handler (Immediate lock prevents multi-click glitches)
    window.handleColumnClick = function(col) {
        if (isGameOver || isAiThinking || isDropping || currentPlayer !== PLAYER) return;

        const targetRow = getLowestEmptyRow(board, col);
        if (targetRow === -1) return; // Column full

        stopTurnTimer();
        isDropping = true;

        dropToken(targetRow, col, PLAYER, () => {
            const winCoords = checkWin(board, targetRow, col, PLAYER);
            if (winCoords) {
                isDropping = false;
                handleGameOver(PLAYER, winCoords);
                return;
            }

            if (isBoardFull(board)) {
                isDropping = false;
                handleGameOver(EMPTY, null);
                return;
            }

            // AI Turn
            currentPlayer = AI;
            isAiThinking = true;
            isDropping = false;
            updateTurnHUD();
            updateTimerHUD(0, 15, false);

            if (aiMoveTimeout) clearTimeout(aiMoveTimeout);
            aiMoveTimeout = setTimeout(() => {
                aiMoveTimeout = null;
                if (!isGameOver && !isDropping && currentPlayer === AI) {
                    makeAiMove();
                }
            }, 450 + Math.random() * 250);
        });
    };

    // Smart Minimax / Heuristic AI Move for OOHPA Arjan
    function makeAiMove() {
        if (isGameOver || isDropping || currentPlayer !== AI) return;
        isDropping = true;
        isAiThinking = true;

        let chosenCol = -1;

        // 1. Can Arjan win immediately?
        for (let c = 0; c < COLS; c++) {
            const r = getLowestEmptyRow(board, c);
            if (r !== -1) {
                board[r][c] = AI;
                if (checkWin(board, r, c, AI)) {
                    chosenCol = c;
                    board[r][c] = EMPTY;
                    break;
                }
                board[r][c] = EMPTY;
            }
        }

        // 2. Block player immediate win
        if (chosenCol === -1) {
            for (let c = 0; c < COLS; c++) {
                const r = getLowestEmptyRow(board, c);
                if (r !== -1) {
                    board[r][c] = PLAYER;
                    if (checkWin(board, r, c, PLAYER)) {
                        chosenCol = c;
                        board[r][c] = EMPTY;
                        setArjanSpeech(getRandomQuote('aiBlock'));
                        break;
                    }
                    board[r][c] = EMPTY;
                }
            }
        }

        // 3. Minimax lookup (depth 4)
        if (chosenCol === -1) {
            chosenCol = getBestMinimaxMove(board, 4);
        }

        if (chosenCol === -1) chosenCol = getRandomValidMove(board);

        const targetRow = getLowestEmptyRow(board, chosenCol);
        if (targetRow === -1) {
            chosenCol = getRandomValidMove(board);
        }

        const finalRow = getLowestEmptyRow(board, chosenCol);
        dropToken(finalRow, chosenCol, AI, () => {
            isDropping = false;
            const winCoords = checkWin(board, finalRow, chosenCol, AI);
            if (winCoords) {
                handleGameOver(AI, winCoords);
                return;
            }

            if (isBoardFull(board)) {
                handleGameOver(EMPTY, null);
                return;
            }

            currentPlayer = PLAYER;
            isAiThinking = false;
            updateTurnHUD();
            startTurnTimer();
            setArjanSpeech(getRandomQuote('aiMove'));
        });
    }

    function getRandomValidMove(b) {
        const validCols = [];
        for (let c = 0; c < COLS; c++) {
            if (getLowestEmptyRow(b, c) !== -1) validCols.push(c);
        }
        if (validCols.includes(3) && Math.random() > 0.4) return 3;
        return validCols[Math.floor(Math.random() * validCols.length)] ?? 3;
    }

    function getBestMinimaxMove(b, depth) {
        let bestScore = -Infinity;
        let bestCol = 3;
        const colOrder = [3, 2, 4, 1, 5, 0, 6];
        const validCols = colOrder.filter(c => getLowestEmptyRow(b, c) !== -1);

        for (let c of validCols) {
            const r = getLowestEmptyRow(b, c);
            b[r][c] = AI;
            let score = minimax(b, depth - 1, false, -Infinity, Infinity);
            b[r][c] = EMPTY;

            if (score > bestScore) {
                bestScore = score;
                bestCol = c;
            }
        }
        return bestCol;
    }

    function minimax(b, depth, isMaximizing, alpha, beta) {
        if (depth === 0) return evaluateBoard(b);

        const validCols = [3, 2, 4, 1, 5, 0, 6].filter(c => getLowestEmptyRow(b, c) !== -1);
        if (validCols.length === 0) return 0;

        if (isMaximizing) {
            let maxEval = -Infinity;
            for (let c of validCols) {
                const r = getLowestEmptyRow(b, c);
                b[r][c] = AI;
                if (checkWin(b, r, c, AI)) {
                    b[r][c] = EMPTY;
                    return 1000 + depth;
                }
                let ev = minimax(b, depth - 1, false, alpha, beta);
                b[r][c] = EMPTY;
                maxEval = Math.max(maxEval, ev);
                alpha = Math.max(alpha, ev);
                if (beta <= alpha) break;
            }
            return maxEval;
        } else {
            let minEval = Infinity;
            for (let c of validCols) {
                const r = getLowestEmptyRow(b, c);
                b[r][c] = PLAYER;
                if (checkWin(b, r, c, PLAYER)) {
                    b[r][c] = EMPTY;
                    return -1000 - depth;
                }
                let ev = minimax(b, depth - 1, true, alpha, beta);
                b[r][c] = EMPTY;
                minEval = Math.min(minEval, ev);
                beta = Math.min(beta, ev);
                if (beta <= alpha) break;
            }
            return minEval;
        }
    }

    function evaluateBoard(b) {
        let score = 0;
        for (let r = 0; r < ROWS; r++) {
            if (b[r][3] === AI) score += 4;
            if (b[r][3] === PLAYER) score -= 4;
        }
        return score;
    }

    function checkWin(b, row, col, player) {
        const directions = [
            [[0, 1], [0, -1]],
            [[1, 0], [-1, 0]],
            [[1, 1], [-1, -1]],
            [[1, -1], [-1, 1]]
        ];

        for (let dir of directions) {
            let coords = [[row, col]];
            for (let [dr, dc] of dir) {
                let r = row + dr;
                let c = col + dc;
                while (r >= 0 && r < ROWS && c >= 0 && c < COLS && b[r][c] === player) {
                    coords.push([r, c]);
                    r += dr;
                    c += dc;
                }
            }
            if (coords.length >= 4) {
                return coords;
            }
        }
        return null;
    }

    function isBoardFull(b) {
        return b[0].every(cell => cell !== EMPTY);
    }

    // Handle Game End & Backend Communication
    function handleGameOver(winner, winCoords) {
        isGameOver = true;
        isAiThinking = false;
        stopTurnTimer();
        updateTimerHUD(0, 15, false);

        // Highlight winning 4 tokens with pulsating glow
        if (winCoords && Array.isArray(winCoords)) {
            winCoords.forEach(([r, c]) => {
                const cell = document.getElementById(`cell-${r}-${c}`);
                if (cell && cell.firstElementChild) {
                    cell.firstElementChild.classList.add('token-winning');
                }
            });
        }

        const modalIcon = document.getElementById('match-modal-icon');
        const modalTitle = document.getElementById('match-modal-title');
        const modalDesc = document.getElementById('match-modal-desc');
        const modalRewards = document.getElementById('match-modal-rewards');
        const modalBtnText = document.getElementById('match-modal-btn-text');

        let winnerKey = 'draw';
        if (winner === PLAYER) {
            winnerKey = 'player';
            playerWins++;
            playSound('win');
            setArjanSpeech(quotes.playerWin);
            modalIcon.innerText = '🏆';
            modalTitle.innerText = 'GEFELICITEERD!';
            modalTitle.className = 'text-xl font-black text-purple-600 dark:text-purple-400';
            modalDesc.innerText = "Je hebt 4 op 'n rij gemaakt en Arjan AI verslagen!";
            modalRewards.innerHTML = '<strong>Beloning:</strong> +€ 5.000 Cash • +10 XP • +3 Respect';
            modalRewards.classList.remove('hidden');
        } else if (winner === AI) {
            winnerKey = 'arjan';
            arjanWins++;
            playSound('lose');
            setArjanSpeech(quotes.aiWin);
            modalIcon.innerText = '👑';
            modalTitle.innerText = 'OOHPA ARJAN WINT!';
            modalTitle.className = 'text-xl font-black text-emerald-600 dark:text-emerald-400';
            modalDesc.innerText = "Arjan heeft 4 op 'n rij! Volgende potje beter!";
            modalRewards.innerHTML = '<strong>Troostprijs:</strong> +5 XP voor de inzet!';
            modalRewards.classList.remove('hidden');
        } else {
            winnerKey = 'draw';
            draws++;
            setArjanSpeech(quotes.draw);
            modalIcon.innerText = '🤝';
            modalTitle.innerText = 'GELIJKSPEL!';
            modalTitle.className = 'text-xl font-black text-slate-700 dark:text-slate-300';
            modalDesc.innerText = 'Het bord is vol! Rematch?';
            modalRewards.innerHTML = '<strong>Gelijkspelbonus:</strong> +€ 2.000 Cash • +8 XP • +1 Respect';
            modalRewards.classList.remove('hidden');
        }

        // Update local HUD counters immediately
        updateScoreDisplays();

        // Send outcome to backend
        fetch('{{ route('connect_four.finish') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                winner: winnerKey,
                moves: Math.max(4, moveCount)
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                matchesPlayed = data.matches_played;
                playerWins = data.current_player_wins;
                arjanWins = data.current_arjan_wins;
                draws = data.current_draws;
                updateScoreDisplays();

                // If tournament completed (5 matches)
                if (data.tournament_finished && data.tournament_summary) {
                    showTournamentSummaryModal(data.tournament_summary, data.cooldown);
                    return;
                }
            }

            modalBtnText.innerText = `Potje ${Math.min(5, matchesPlayed + 1)} van 5 Spelen`;
            setTimeout(() => {
                document.getElementById('match-modal').classList.remove('hidden');
            }, 650);
        })
        .catch(() => {
            modalBtnText.innerText = `Volgend Potje`;
            setTimeout(() => {
                document.getElementById('match-modal').classList.remove('hidden');
            }, 650);
        });
    }

    function updateScoreDisplays() {
        const spw = document.getElementById('stat-player-wins');
        const saw = document.getElementById('stat-arjan-wins');
        const sd = document.getElementById('stat-draws');
        const hp1 = document.getElementById('hud-score-player1');
        const hp2 = document.getElementById('hud-score-player2');
        const st = document.getElementById('score-ties');
        const badge = document.getElementById('match-progress-badge');

        if (spw) spw.innerText = playerWins;
        if (saw) saw.innerText = arjanWins;
        if (sd) sd.innerText = draws;
        if (hp1) hp1.innerText = playerWins;
        if (hp2) hp2.innerText = arjanWins;
        if (st) st.innerText = draws;
        if (badge) badge.innerText = `Potje ${Math.min(5, matchesPlayed + 1)} van 5`;
    }

    function showTournamentSummaryModal(summary, cdSeconds) {
        const modal = document.getElementById('tournament-modal');
        const title = document.getElementById('tourney-title');
        const desc = document.getElementById('tourney-desc');
        const score = document.getElementById('tourney-score');
        const icon = document.getElementById('tourney-icon');

        if (title) title.innerText = summary.title;
        if (desc) desc.innerText = summary.message;
        if (score) score.innerText = `Eindstand: ${summary.player_wins} - ${summary.arjan_wins} (Gelijk: ${summary.draws})`;

        if (icon) {
            if (summary.won) {
                icon.className = 'w-16 h-16 mx-auto rounded-3xl flex items-center justify-center text-3xl shadow-md bg-emerald-100 dark:bg-emerald-950 text-emerald-600 border border-emerald-300';
                icon.innerHTML = '<i class="fa-solid fa-trophy"></i>';
            } else {
                icon.className = 'w-16 h-16 mx-auto rounded-3xl flex items-center justify-center text-3xl shadow-md bg-amber-100 dark:bg-amber-950 text-amber-600 border border-amber-300';
                icon.innerHTML = '<i class="fa-solid fa-award"></i>';
            }
        }

        cooldownRemaining = cdSeconds;
        startCooldownCountdown();

        setTimeout(() => {
            modal.classList.remove('hidden');
        }, 700);
    }

    // Modal Action Buttons
    window.closeMatchModalAndContinue = function() {
        document.getElementById('match-modal').classList.add('hidden');
        startingPlayer = startingPlayer === PLAYER ? AI : PLAYER;
        initBoard(startingPlayer);
    };

    window.closeTournamentModal = function() {
        document.getElementById('tournament-modal').classList.add('hidden');
        document.getElementById('game-container').classList.add('hidden');
        document.getElementById('cooldown-container').classList.remove('hidden');
    };

    window.restartMatch = function() {
        if (isDropping) return;
        initBoard(PLAYER);
    };

    window.toggleSound = function() {
        soundEnabled = !soundEnabled;
        const icon = document.getElementById('sound-icon');
        const label = document.getElementById('sound-label');
        if (soundEnabled) {
            icon.className = 'fa-solid fa-volume-high text-purple-600 dark:text-purple-400';
            label.innerText = 'Geluid Aan';
            playSound('tick');
        } else {
            icon.className = 'fa-solid fa-volume-xmark text-slate-400';
            label.innerText = 'Geluid Uit';
        }
    };

    // Cooldown Countdown (10 Minutes)
    function startCooldownCountdown() {
        if (cooldownTimerInterval) clearInterval(cooldownTimerInterval);
        const timerText = document.getElementById('cooldown-timer-text');

        cooldownTimerInterval = setInterval(() => {
            cooldownRemaining--;
            if (timerText) {
                const h = Math.floor(cooldownRemaining / 3600);
                const m = Math.floor((cooldownRemaining % 3600) / 60);
                const s = cooldownRemaining % 60;
                if (h > 0) timerText.innerText = `${h}u ${m}m ${s}s`;
                else timerText.innerText = `${m}m ${s}s`;
            }

            if (cooldownRemaining <= 0) {
                clearInterval(cooldownTimerInterval);
                window.location.reload();
            }
        }, 1000);
    }

    // Initialization
    if (cooldownRemaining > 0) {
        startCooldownCountdown();
    } else {
        initBoard(PLAYER);
    }

})();
</script>
@endsection
