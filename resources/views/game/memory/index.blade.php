@extends('layouts.app')

@section('title', 'Memory - TBS Kliniek')

@section('content')
<div class="space-y-6 max-w-5xl mx-auto pb-12"
     x-data="memoryGame({
         deckData: @js($deck),
         cooldown: {{ (int) $cooldownRemaining }},
         finishUrl: '{{ route('memory.finish') }}',
         csrfToken: '{{ csrf_token() }}'
     })"
     x-init="initGame()">

    <!-- 1. HEADER & INTRO -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-5 sm:p-7 shadow-xs relative overflow-hidden">
        <div class="absolute -right-12 -top-12 w-48 h-48 bg-purple-500/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -left-12 -bottom-12 w-48 h-48 bg-pink-500/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="flex flex-col md:flex-row md:items-center justify-between gap-5 relative z-10">
            <div class="flex items-start gap-4">
                <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-purple-600 to-pink-600 text-white flex items-center justify-center text-2xl shadow-lg shadow-purple-500/25 shrink-0">
                    <i class="fa-solid fa-brain"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2 flex-wrap mb-1">
                        <span class="text-[10px] font-black uppercase tracking-wider text-purple-700 dark:text-purple-300 bg-purple-50 dark:bg-purple-950/80 px-2.5 py-0.5 rounded-full border border-purple-200 dark:border-purple-800 flex items-center gap-1">
                            <i class="fa-solid fa-bolt text-purple-500"></i> Minigame
                        </span>
                        <span class="text-[10px] font-black uppercase tracking-wider text-pink-700 dark:text-pink-300 bg-pink-50 dark:bg-pink-950/80 px-2.5 py-0.5 rounded-full border border-pink-200 dark:border-pink-800">
                            Psychose Geheugentest
                        </span>
                        <span class="text-[10px] font-black uppercase tracking-wider text-rose-700 dark:text-rose-300 bg-rose-50 dark:bg-rose-950/80 px-2.5 py-0.5 rounded-full border border-rose-200 dark:border-rose-800">
                            Extreem Moeilijk
                        </span>
                    </div>
                    <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight">
                        TBS Kliniek Memory: Psychose Test
                    </h1>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 max-w-xl leading-relaxed">
                        Onthoud in <strong>5 seconden inkijkfase</strong> alle verborgen smokkelwaar. Daarna draaien de kaartjes om en moet je alle 8 paren matchen binnen <strong>60 seconden</strong>.
                        Pas op: je hebt maximaal <strong>3 levens</strong> (foutjes)!
                    </p>
                </div>
            </div>

            <!-- Stats Badge Quick Summary & Sound Toggle -->
            <div class="flex items-center gap-2.5 shrink-0">
                <div class="px-3.5 py-2 rounded-2xl bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700/80 text-center">
                    <span class="text-[9px] font-bold text-slate-400 block uppercase">Gewonnen</span>
                    <span class="text-sm font-black text-purple-600 dark:text-purple-400 font-mono" x-text="stats.games_won">{{ $stat->games_won }}</span>
                </div>
                <div class="px-3.5 py-2 rounded-2xl bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700/80 text-center">
                    <span class="text-[9px] font-bold text-slate-400 block uppercase">Snelste Tijd</span>
                    <span class="text-sm font-black text-amber-500 font-mono">{{ $stat->best_time_seconds ? $stat->best_time_seconds . 's' : '--' }}</span>
                </div>
                <button type="button" 
                        @click="toggleSound()" 
                        class="w-10 h-10 rounded-2xl bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700/80 flex items-center justify-center text-slate-500 hover:text-purple-600 dark:hover:text-purple-300 transition cursor-pointer shadow-2xs"
                        :title="muted ? 'Geluid aanzetten' : 'Geluid dempen'">
                    <i class="fa-solid text-sm" :class="muted ? 'fa-volume-xmark text-rose-500' : 'fa-volume-high text-emerald-500'"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- 2. COOLDOWN SCREEN (SHOWN WHEN ACTIVE) -->
    <template x-if="cooldownRemaining > 0 && gameState === 'idle'">
        <div class="bg-white dark:bg-slate-900 border border-amber-300 dark:border-amber-700/80 rounded-3xl p-6 sm:p-10 shadow-lg text-center space-y-6">
            <div class="w-20 h-20 mx-auto rounded-3xl bg-amber-500/10 text-amber-500 border border-amber-400/40 flex items-center justify-center text-4xl shadow-inner animate-pulse">
                <i class="fa-solid fa-hourglass-half"></i>
            </div>
            
            <div class="space-y-2 max-w-md mx-auto">
                <h3 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white">Brein Even Laten Afkoelen!</h3>
                <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed">
                    Je hersenen draaien overuren na de psychose geheugentest. Over <strong class="text-amber-500 font-mono font-black text-sm" x-text="formatTime(cooldownRemaining)">--:--</strong> kun je weer een nieuwe ronde spelen.
                </p>
            </div>

            <!-- AD SKIP COOLDOWN BUTTON -->
            <div class="p-5 rounded-2xl bg-gradient-to-r from-amber-50 via-purple-50 to-pink-50 dark:from-amber-950/40 dark:via-purple-950/40 dark:to-pink-950/40 border border-amber-300/80 dark:border-purple-800 max-w-md mx-auto space-y-3">
                <div class="flex items-center justify-center gap-2 text-xs font-black text-amber-900 dark:text-amber-200">
                    <i class="fa-solid fa-star text-amber-500 animate-bounce"></i>
                    <span>Geen geduld? Reset direct met Sterren!</span>
                </div>
                <form action="{{ route('memory.skip_cooldown_stars') }}" method="POST">
                    @csrf
                    <button type="submit" 
                            class="btn-star-gold w-full py-3.5 px-6 rounded-xl active:scale-95 text-slate-900 dark:text-white font-black text-xs uppercase tracking-wider transition cursor-pointer shadow-md shadow-amber-500/30 flex items-center justify-center gap-2">
                        <i class="fa-solid fa-star text-[12px]"></i>
                        <span>Direct Opnieuw Spelen (10 Sterren ⭐)</span>
                    </button>
                </form>
                <p class="text-[10px] text-slate-500 dark:text-slate-400">Gebruik 10 Sterren ⭐ om de wachttijd over te slaan en onmiddellijk weer te spelen.</p>
            </div>
        </div>
    </template>

    <!-- 3. GAME HUD BAR (LIVES, TIMER, MOVES, PAIRS) -->
    <div x-show="cooldownRemaining <= 0 || gameState !== 'idle'" class="space-y-4">
        
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-xs">
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-center">
                
                <!-- LEVENS (3 HEARTS) -->
                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/60 flex flex-col items-center justify-center">
                    <span class="text-[10px] font-black uppercase tracking-wider text-slate-400 mb-1">Levens (Fouten)</span>
                    <div class="flex items-center gap-1.5 text-base sm:text-lg">
                        <template x-for="i in 3" :key="i">
                            <i class="fa-solid fa-heart transition-all duration-300"
                               :class="i <= lives ? 'text-rose-500 scale-100 drop-shadow-xs animate-pulse' : 'text-slate-300 dark:text-slate-700 scale-90 opacity-40'"></i>
                        </template>
                    </div>
                </div>

                <!-- TIMER (60s COUNTDOWN) -->
                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/60 flex flex-col items-center justify-center">
                    <span class="text-[10px] font-black uppercase tracking-wider text-slate-400 mb-1">Resterende Tijd</span>
                    <div class="flex items-center gap-1.5 font-mono font-black text-base sm:text-lg"
                         :class="timerSeconds <= 10 ? 'text-rose-500 animate-pulse' : (timerSeconds <= 20 ? 'text-amber-500' : 'text-purple-600 dark:text-purple-400')">
                        <i class="fa-solid fa-clock text-xs"></i>
                        <span x-text="formatTime(timerSeconds)">01:00</span>
                    </div>
                </div>

                <!-- PAREN GEVONDEN (X / 8) -->
                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/60 flex flex-col items-center justify-center">
                    <span class="text-[10px] font-black uppercase tracking-wider text-slate-400 mb-1">Paren Gevonden</span>
                    <div class="flex items-center gap-1.5 font-mono font-black text-base sm:text-lg text-emerald-600 dark:text-emerald-400">
                        <span x-text="matchedPairs">0</span> / 8
                    </div>
                    <!-- 8 mini dots indicator -->
                    <div class="flex items-center gap-1 mt-1">
                        <template x-for="i in 8" :key="i">
                            <span class="w-1.5 h-1.5 rounded-full transition-all duration-300"
                                  :class="i <= matchedPairs ? 'bg-emerald-500 scale-110 shadow-xs' : 'bg-slate-200 dark:bg-slate-700'"></span>
                        </template>
                    </div>
                </div>

                <!-- ZETTEN GEDAAN & COMBO STREAK -->
                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/60 flex flex-col items-center justify-center relative">
                    <span class="text-[10px] font-black uppercase tracking-wider text-slate-400 mb-1">Zetten</span>
                    <div class="font-mono font-black text-base sm:text-lg text-slate-700 dark:text-slate-200">
                        <span x-text="moves">0</span>
                    </div>
                    <div x-show="streak >= 2" x-transition class="absolute -top-2 -right-2 px-1.5 py-0.5 rounded-md bg-amber-500 text-slate-950 font-black text-[9px] uppercase tracking-wider shadow-xs animate-bounce">
                        <span x-text="streak + 'x Combo!'"></span>
                    </div>
                </div>

            </div>

            <!-- INKIJKFASE COUNTDOWN ALERT BANNER -->
            <div x-show="gameState === 'preview'" 
                 x-transition
                 class="mt-4 p-4 rounded-2xl bg-gradient-to-r from-amber-500 via-pink-600 to-purple-600 text-white shadow-lg space-y-2">
                <div class="flex items-center justify-between text-xs sm:text-sm font-black">
                    <span class="flex items-center gap-2">
                        <i class="fa-solid fa-eye text-base animate-bounce"></i>
                        <span>INKIJKFASE: Onthoud alle kaarten!</span>
                    </span>
                    <span class="font-mono text-base px-2 py-0.5 rounded-lg bg-black/30">
                        Nog <span class="underline" x-text="previewSeconds">5</span>s
                    </span>
                </div>
                <!-- Progress bar for 5s preview -->
                <div class="w-full h-1.5 bg-black/20 rounded-full overflow-hidden">
                    <div class="h-full bg-white transition-all duration-1000 ease-linear rounded-full"
                         :style="'width: ' + ((previewSeconds / 5) * 100) + '%'"></div>
                </div>
            </div>

            <!-- START GAME BUTTON (WHEN IDLE & NO COOLDOWN) -->
            <div x-show="gameState === 'idle' && cooldownRemaining <= 0" class="mt-4 text-center">
                <button type="button" 
                        @click="startGame()"
                        class="px-8 py-3.5 rounded-2xl bg-gradient-to-r from-purple-600 via-pink-600 to-purple-700 hover:from-purple-700 hover:to-pink-700 active:scale-95 text-white font-black text-sm uppercase tracking-wider transition shadow-lg shadow-purple-600/30 cursor-pointer inline-flex items-center gap-2.5">
                    <i class="fa-solid fa-play"></i>
                    <span>Start Ronde (5s Inkijkfase + 55s Speeltijd)</span>
                </button>
            </div>
        </div>

        <!-- 4. MEMORY CARDS 4x4 GRID (ROCK-SOLID CSS 3D FLIP) -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-3 sm:p-6 shadow-xs relative">
            
            <!-- OVERLAY WHEN IDLE (PROMPT TO START) -->
            <div x-show="gameState === 'idle' && cooldownRemaining <= 0" 
                 class="absolute inset-0 z-20 backdrop-blur-2xs bg-slate-950/20 dark:bg-slate-950/40 rounded-3xl flex items-center justify-center p-4">
                <div class="bg-white dark:bg-slate-900 border-2 border-purple-500/50 rounded-2xl p-5 shadow-2xl text-center space-y-2 max-w-sm animate-fade-in">
                    <div class="w-12 h-12 rounded-xl bg-purple-100 dark:bg-purple-950/80 text-purple-600 dark:text-purple-400 flex items-center justify-center text-xl mx-auto">
                        <i class="fa-solid fa-shield-halved"></i>
                    </div>
                    <h4 class="text-sm font-black text-slate-900 dark:text-white">Klaar voor de Test?</h4>
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        Klik op <strong>Start Ronde</strong> om de 5 seconden inkijkfase te beginnen waarin alle 16 kaarten getoond worden!
                    </p>
                    <button type="button" 
                            @click="startGame()"
                            class="w-full py-2.5 px-4 rounded-xl bg-purple-600 hover:bg-purple-700 active:scale-95 text-white font-black text-xs uppercase tracking-wider transition shadow-md shadow-purple-600/30 cursor-pointer">
                        Start Ronde
                    </button>
                </div>
            </div>

            <!-- 4x4 CARDS GRID -->
            <div class="grid grid-cols-4 gap-2.5 sm:gap-4 max-w-lg sm:max-w-xl mx-auto select-none memory-grid">
                <template x-for="(card, index) in cards" :key="card.card_uid">
                    <div class="memory-card relative w-full cursor-pointer"
                         @click="flipCard(index)">
                        
                        <!-- 3D FLIP CONTAINER -->
                        <div class="memory-card-inner"
                             :class="{
                                 'is-flipped': card.flipped || card.matched,
                                 'is-matched': card.matched,
                                 'is-shaking': card.shake
                             }">

                            <!-- CARD BACK (WHEN FACE DOWN) -->
                            <div class="memory-card-face memory-card-back bg-gradient-to-br from-slate-50 via-white to-purple-50/70 dark:from-[#110c1f] dark:via-[#191030] dark:to-[#0f0a1c] border-2 border-slate-200/90 dark:border-purple-500/30 hover:border-purple-400 dark:hover:border-purple-400/80 shadow-xs hover:shadow-md transition-colors p-2 sm:p-3">
                                <div class="w-full flex items-center justify-between opacity-50 text-[8px] sm:text-[9px] text-slate-500 dark:text-purple-300 font-mono">
                                    <i class="fa-solid fa-fingerprint"></i>
                                    <span>#TBS</span>
                                </div>
                                <div class="flex flex-col items-center justify-center my-auto">
                                    <div class="w-8 h-8 sm:w-11 sm:h-11 rounded-xl bg-purple-100 dark:bg-purple-900/60 border border-purple-200 dark:border-purple-500/50 text-purple-600 dark:text-purple-300 flex items-center justify-center text-sm sm:text-lg shadow-2xs">
                                        <i class="fa-solid fa-shield-halved"></i>
                                    </div>
                                    <span class="text-[8px] sm:text-[10px] font-black text-purple-700 dark:text-purple-300 uppercase tracking-widest mt-1">MEMORY</span>
                                </div>
                                <div class="w-full text-center opacity-50 text-[7px] sm:text-[8px] text-slate-400 dark:text-purple-400 font-bold uppercase tracking-wider">
                                    Kliniek
                                </div>
                            </div>

                            <!-- CARD FRONT (WHEN FACE UP) -->
                            <div class="memory-card-face memory-card-front bg-white dark:bg-slate-900 border-2 p-1.5 sm:p-2.5 shadow-md"
                                 :class="[
                                     card.matched 
                                         ? 'border-emerald-500 ring-2 ring-emerald-400/60 bg-emerald-50/50 dark:bg-emerald-950/40 shadow-emerald-500/20' 
                                         : (card.shake ? 'border-rose-500 ring-2 ring-rose-400/80 bg-rose-50/50 dark:bg-rose-950/40' : ('card-border-' + card.id + ' bg-white dark:bg-slate-900'))
                                 ]"
                                 :style="(!card.matched && !card.shake && card.border_hex) ? ('border-color: ' + card.border_hex) : ''">
                                
                                <!-- Card Category Pill & Match Indicator -->
                                <div class="w-full flex items-center justify-between text-[7px] sm:text-[9px] font-black uppercase tracking-wider">
                                    <span class="px-1.5 py-0.5 rounded-md font-extrabold"
                                          :class="card.matched ? 'bg-emerald-500 text-white' : ('card-pill-' + card.id)"
                                          :style="(!card.matched && card.badge_bg) ? ('background-color: ' + card.badge_bg + '; color: ' + card.badge_text + ';') : ''"
                                          x-text="card.category"></span>
                                    <i x-show="card.matched" class="fa-solid fa-circle-check text-emerald-500 text-xs"></i>
                                </div>

                                <!-- Center Asset Icon in Glowing Gradient Badge -->
                                <div class="my-auto flex flex-col items-center justify-center">
                                    <div class="w-9 h-9 sm:w-12 sm:h-12 rounded-xl sm:rounded-2xl flex items-center justify-center text-lg sm:text-2xl shadow-sm transition-transform"
                                         :class="[
                                             'card-badge-' + card.id,
                                             card.matched ? 'scale-105 ring-2 ring-emerald-300' : ''
                                         ]"
                                         :style="(card.bg_style ? ('background: ' + card.bg_style + ' !important; ') : '') + 'box-shadow: 0 0 14px ' + (card.glow_color || 'rgba(147, 51, 234, 0.4)')">
                                        <i class="fa-solid text-white drop-shadow-sm pointer-events-none" :class="card.icon"></i>
                                    </div>
                                </div>

                                <!-- Card Title -->
                                <div class="w-full text-center px-0.5">
                                    <span class="text-[8px] sm:text-[11px] font-black text-slate-800 dark:text-slate-100 leading-tight block truncate"
                                          x-text="card.name"></span>
                                </div>

                            </div>

                        </div>
                    </div>
                </template>
            </div>
        </div>

    </div>

    <!-- 5. VICTORY MODAL -->
    <template x-if="gameResult === 'won'">
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm animate-fade-in">
            <div class="bg-white dark:bg-slate-900 border-2 border-emerald-400 rounded-3xl p-6 sm:p-8 max-w-md w-full text-center space-y-5 shadow-2xl relative overflow-hidden">
                <div class="w-20 h-20 mx-auto rounded-3xl bg-emerald-500/10 text-emerald-500 border border-emerald-400 flex items-center justify-center text-4xl shadow-lg animate-bounce">
                    <i class="fa-solid fa-trophy"></i>
                </div>

                <div class="space-y-1">
                    <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-emerald-100 dark:bg-emerald-950/80 text-emerald-700 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-700">
                        Psychose Test Voltooid!
                    </span>
                    <h3 class="text-2xl font-black text-slate-900 dark:text-white mt-2">IJzersterk Geheugen!</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                        Je hebt alle 8 paren feilloos gevonden in <strong class="text-slate-900 dark:text-white font-mono" x-text="timeTaken + 's'">--</strong> met <strong class="text-slate-900 dark:text-white font-mono" x-text="moves + ' zetten'">--</strong>!
                    </p>
                </div>

                <!-- Reward Showcase -->
                <div class="p-4 rounded-2xl bg-gradient-to-r from-emerald-50 to-teal-50 dark:from-emerald-950/40 dark:to-teal-950/40 border border-emerald-200 dark:border-emerald-800 text-left space-y-2">
                    <div class="text-[11px] font-black uppercase text-emerald-800 dark:text-emerald-300 flex items-center gap-1.5">
                        <i class="fa-solid fa-gift"></i>
                        <span>Ontvangen Beloningen:</span>
                    </div>
                    <div class="grid grid-cols-2 gap-2 text-xs font-black">
                        <div class="flex items-center gap-2 text-emerald-700 dark:text-emerald-300">
                            <i class="fa-solid fa-sack-dollar text-emerald-500"></i>
                            <span x-text="'+ €' + formatNumber(lastResult.reward_cash)">+ €35.000</span>
                        </div>
                        <div class="flex items-center gap-2 text-purple-700 dark:text-purple-300">
                            <i class="fa-solid fa-star text-purple-500"></i>
                            <span x-text="'+ ' + lastResult.reward_xp + ' XP & ' + lastResult.reward_respect + ' Resp'">+ 60 XP</span>
                        </div>
                    </div>
                    <template x-if="lastResult.dropped_bag">
                        <div class="mt-2 pt-2 border-t border-emerald-200/60 dark:border-emerald-800/60 flex items-center gap-2 text-xs font-black text-purple-600 dark:text-purple-300">
                            <i class="fa-solid fa-briefcase text-amber-500 animate-bounce"></i>
                            <span>Lucky Drop: +1 Smokkelkoffer ontvangen!</span>
                        </div>
                    </template>
                </div>

                <button type="button" 
                        @click="closeModal()"
                        class="w-full py-3.5 px-6 rounded-2xl bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white font-black text-xs uppercase tracking-wider transition cursor-pointer shadow-md shadow-emerald-600/30">
                    Geweldig, Terug naar Overzicht
                </button>
            </div>
        </div>
    </template>

    <!-- 6. GAME OVER MODAL -->
    <template x-if="gameResult === 'lost'">
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm animate-fade-in">
            <div class="bg-white dark:bg-slate-900 border-2 border-rose-400 rounded-3xl p-6 sm:p-8 max-w-md w-full text-center space-y-5 shadow-2xl relative overflow-hidden">
                <div class="w-20 h-20 mx-auto rounded-3xl bg-rose-500/10 text-rose-500 border border-rose-400 flex items-center justify-center text-4xl shadow-lg animate-pulse">
                    <i class="fa-solid fa-skull"></i>
                </div>

                <div class="space-y-1">
                    <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-rose-100 dark:bg-rose-950/80 text-rose-700 dark:text-rose-300 border border-rose-300 dark:border-rose-700">
                        Geheugenverlies!
                    </span>
                    <h3 class="text-2xl font-black text-slate-900 dark:text-white mt-2">Helaas, Geen Match!</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed" x-text="lossReason">
                        Je hebt al je 3 levens verspeeld door foute paren te kiezen of de 60 seconden zijn verstreken.
                    </p>
                </div>

                <!-- Consolation prize -->
                <div class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 text-left text-xs font-bold text-slate-600 dark:text-slate-300">
                    <span class="text-[10px] uppercase text-slate-400 block font-black mb-1">Troostprijs:</span>
                    <div class="flex items-center gap-3">
                        <span class="text-emerald-600 dark:text-emerald-400 font-mono">+ €3.000 Cash</span>
                        <span class="text-purple-600 dark:text-purple-400 font-mono">+ 15 XP</span>
                    </div>
                </div>

                <button type="button" 
                        @click="closeModal()"
                        class="w-full py-3.5 px-6 rounded-2xl bg-slate-900 hover:bg-slate-800 dark:bg-slate-800 dark:hover:bg-slate-700 text-white font-black text-xs uppercase tracking-wider transition cursor-pointer">
                    Sluiten
                </button>
            </div>
        </div>
    </template>

    <!-- 7. SPELREGELS & INFO FOOTER -->
    <div class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-slate-500 dark:text-slate-400">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-purple-100 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400 flex items-center justify-center text-sm font-black shrink-0">
                <i class="fa-solid fa-brain"></i>
            </div>
            <div>
                <p class="font-bold text-slate-800 dark:text-slate-200">
                    TBS Kliniek Geheugentraining
                </p>
                <p class="text-[11px] text-slate-500 dark:text-slate-400">
                    Spelregels: 5 seconden inkijkfase &bull; 60 seconden speeltijd &bull; 3 levens (max 3 missers) &bull; Vind alle 8 paren.
                </p>
            </div>
        </div>
        <a href="{{ route('faq.index') }}" class="text-[11px] font-black text-purple-600 dark:text-purple-400 hover:underline shrink-0">
            Hulp &amp; Speluitleg &rarr;
        </a>
    </div>

</div>

<!-- INLINE ALPINESCRIPT COMPONENT FOR ULTRA-RESPONSIVE MEMORY GAMEPLAY -->
<script>
function memoryGame(config) {
    return {
        cards: config.deckData || [],
        cooldownRemaining: config.cooldown || 0,
        gameState: 'idle', // 'idle', 'preview', 'playing', 'finished'
        gameResult: null, // null, 'won', 'lost'
        lossReason: '',
        previewSeconds: 5,
        timerSeconds: 60,
        lives: 3,
        moves: 0,
        matchedPairs: 0,
        streak: 0,
        firstCardIndex: null,
        secondCardIndex: null,
        isLocked: false,
        muted: localStorage.getItem('tbs_memory_muted') === 'true',
        audioCtx: null,
        timerInterval: null,
        previewInterval: null,
        cooldownInterval: null,
        timeTaken: 0,
        lastResult: {},
        stats: {
            games_won: {{ (int) $stat->games_won }},
            games_played: {{ (int) $stat->games_played }}
        },

        initGame() {
            const cardThemes = {
                'smokkelkoffer': { bg: 'linear-gradient(135deg, #9333ea 0%, #4338ca 100%)', border: '#c084fc', badgeBg: 'rgba(168, 85, 247, 0.15)', badgeText: '#9333ea', glow: 'rgba(168, 85, 247, 0.45)', icon: 'fa-briefcase' },
                'medicijnspuit': { bg: 'linear-gradient(135deg, #059669 0%, #0f766e 100%)', border: '#34d399', badgeBg: 'rgba(16, 185, 129, 0.15)', badgeText: '#059669', glow: 'rgba(16, 185, 129, 0.45)', icon: 'fa-syringe' },
                'handboeien': { bg: 'linear-gradient(135deg, #334155 0%, #0f172a 100%)', border: '#64748b', badgeBg: 'rgba(100, 116, 139, 0.15)', badgeText: '#475569', glow: 'rgba(100, 116, 139, 0.45)', icon: 'fa-handcuffs' },
                'bende_masker': { bg: 'linear-gradient(135deg, #e11d48 0%, #be185d 100%)', border: '#fb7185', badgeBg: 'rgba(244, 63, 94, 0.15)', badgeText: '#e11d48', glow: 'rgba(244, 63, 94, 0.45)', icon: 'fa-masks-theater' },
                'dodelijke_pillen': { bg: 'linear-gradient(135deg, #f59e0b 0%, #ea580c 100%)', border: '#fbbf24', badgeBg: 'rgba(245, 158, 11, 0.15)', badgeText: '#d97706', glow: 'rgba(245, 158, 11, 0.45)', icon: 'fa-pills' },
                'sleutelbos': { bg: 'linear-gradient(135deg, #4f46e5 0%, #2563eb 100%)', border: '#818cf8', badgeBg: 'rgba(99, 102, 241, 0.15)', badgeText: '#4f46e5', glow: 'rgba(99, 102, 241, 0.5)', icon: 'fa-key' },
                'steekwapen': { bg: 'linear-gradient(135deg, #dc2626 0%, #991b1b 100%)', border: '#f87171', badgeBg: 'rgba(239, 68, 68, 0.15)', badgeText: '#dc2626', glow: 'rgba(239, 68, 68, 0.45)', icon: 'fa-screwdriver-wrench' },
                'kluis': { bg: 'linear-gradient(135deg, #eab308 0%, #d97706 100%)', border: '#facc15', badgeBg: 'rgba(234, 179, 8, 0.18)', badgeText: '#b45309', glow: 'rgba(234, 179, 8, 0.45)', icon: 'fa-vault' },
                'portofoon': { bg: 'linear-gradient(135deg, #06b6d4 0%, #0d9488 100%)', border: '#22d3ee', badgeBg: 'rgba(6, 182, 212, 0.15)', badgeText: '#0891b2', glow: 'rgba(6, 182, 212, 0.5)', icon: 'fa-walkie-talkie' },
                'traliecel': { bg: 'linear-gradient(135deg, #ea580c 0%, #c2410c 100%)', border: '#fb923c', badgeBg: 'rgba(249, 115, 22, 0.15)', badgeText: '#c2410c', glow: 'rgba(249, 115, 22, 0.5)', icon: 'fa-door-closed' }
            };

            // Ensure cards start facedown and have rich styles
            this.cards.forEach(c => {
                c.flipped = false;
                c.matched = false;
                c.shake = false;
                const theme = cardThemes[c.id];
                if (theme) {
                    c.bg_style = c.bg_style || theme.bg;
                    c.border_hex = c.border_hex || theme.border;
                    c.badge_bg = c.badge_bg || theme.badgeBg;
                    c.badge_text = c.badge_text || theme.badgeText;
                    c.glow_color = c.glow_color || theme.glow;
                    if (!c.icon || c.icon === 'fa-dungeon') {
                        c.icon = theme.icon;
                    }
                }
            });

            if (this.cooldownRemaining > 0) {
                this.startCooldownTimer();
            }
        },

        toggleSound() {
            this.muted = !this.muted;
            localStorage.setItem('tbs_memory_muted', this.muted);
            if (!this.muted) {
                this.playTone(523.25, 'sine', 0.15, 0.1);
            }
        },

        getAudioContext() {
            if (!this.audioCtx) {
                const AudioContextClass = window.AudioContext || window.webkitAudioContext;
                if (AudioContextClass) {
                    this.audioCtx = new AudioContextClass();
                }
            }
            if (this.audioCtx && this.audioCtx.state === 'suspended') {
                this.audioCtx.resume();
            }
            return this.audioCtx;
        },

        playTone(freq, type = 'sine', duration = 0.15, gainVal = 0.1) {
            if (this.muted) return;
            try {
                const ctx = this.getAudioContext();
                if (!ctx) return;
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();
                osc.type = type;
                osc.frequency.setValueAtTime(freq, ctx.currentTime);
                gain.gain.setValueAtTime(gainVal, ctx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + duration);
                osc.connect(gain);
                gain.connect(ctx.destination);
                osc.start();
                osc.stop(ctx.currentTime + duration);
            } catch (e) {}
        },

        playFlipSound() {
            this.playTone(480, 'triangle', 0.08, 0.08);
        },

        playMatchSound() {
            this.playTone(523.25, 'triangle', 0.18, 0.12);
            setTimeout(() => this.playTone(659.25, 'triangle', 0.18, 0.14), 80);
            setTimeout(() => this.playTone(783.99, 'triangle', 0.28, 0.16), 160);
        },

        playMismatchSound() {
            this.playTone(200, 'sawtooth', 0.15, 0.08);
            setTimeout(() => this.playTone(150, 'sawtooth', 0.2, 0.08), 80);
        },

        playVictorySound() {
            const notes = [523.25, 659.25, 783.99, 1046.50];
            notes.forEach((freq, idx) => {
                setTimeout(() => this.playTone(freq, 'triangle', 0.35, 0.15), idx * 110);
            });
        },

        playLoseSound() {
            const notes = [320, 280, 240, 190];
            notes.forEach((freq, idx) => {
                setTimeout(() => this.playTone(freq, 'sawtooth', 0.25, 0.08), idx * 120);
            });
        },

        shuffleDeck() {
            // Fisher-Yates shuffle algorithm
            const array = [...this.cards];
            for (let i = array.length - 1; i > 0; i--) {
                const j = Math.floor(Math.random() * (i + 1));
                [array[i], array[j]] = [array[j], array[i]];
            }
            this.cards = array;
        },

        startCooldownTimer() {
            if (this.cooldownInterval) clearInterval(this.cooldownInterval);
            this.cooldownInterval = setInterval(() => {
                if (this.cooldownRemaining > 0) {
                    this.cooldownRemaining--;
                } else {
                    clearInterval(this.cooldownInterval);
                }
            }, 1000);
        },

        startGame() {
            if (this.cooldownRemaining > 0) return;
            
            // Clear any lingering intervals
            if (this.previewInterval) clearInterval(this.previewInterval);
            if (this.timerInterval) clearInterval(this.timerInterval);

            // Reshuffle deck for every single new game
            this.shuffleDeck();

            // Reset board state
            this.lives = 3;
            this.moves = 0;
            this.matchedPairs = 0;
            this.streak = 0;
            this.firstCardIndex = null;
            this.secondCardIndex = null;
            this.isLocked = true; // cards non-clickable during preview
            this.gameResult = null;
            this.previewSeconds = 5;
            this.timerSeconds = 60;
            this.gameState = 'preview';

            // Flip all cards open for the 5-second preview
            this.cards.forEach(c => {
                c.flipped = true;
                c.matched = false;
                c.shake = false;
            });

            this.playTone(440, 'sine', 0.2, 0.12);

            // Start preview countdown (5s)
            this.previewInterval = setInterval(() => {
                this.previewSeconds--;
                if (this.previewSeconds <= 0) {
                    clearInterval(this.previewInterval);
                    this.endPreviewAndStartPlaying();
                }
            }, 1000);
        },

        endPreviewAndStartPlaying() {
            // Flip all cards face down
            this.cards.forEach(c => c.flipped = false);
            this.playFlipSound();
            
            this.gameState = 'playing';
            this.isLocked = false;

            // Start main game timer (60s countdown)
            if (this.timerInterval) clearInterval(this.timerInterval);

            this.timerInterval = setInterval(() => {
                this.timerSeconds--;
                if (this.timerSeconds <= 0) {
                    clearInterval(this.timerInterval);
                    this.handleLoss('Tijd verstreken! De 60 seconden zijn voorbij voordat je alle paren vond.');
                }
            }, 1000);
        },

        flipCard(index) {
            if (this.isLocked) return;
            if (this.gameState !== 'playing') return;

            const card = this.cards[index];
            if (!card || card.flipped || card.matched) return;
            if (this.firstCardIndex === index) return;

            // Flip card face-up
            card.flipped = true;
            this.playFlipSound();

            if (this.firstCardIndex === null) {
                this.firstCardIndex = index;
            } else {
                this.secondCardIndex = index;
                this.moves++;
                this.checkMatch();
            }
        },

        checkMatch() {
            this.isLocked = true;
            const c1 = this.cards[this.firstCardIndex];
            const c2 = this.cards[this.secondCardIndex];

            if (!c1 || !c2) {
                this.firstCardIndex = null;
                this.secondCardIndex = null;
                this.isLocked = false;
                return;
            }

            if (c1.id === c2.id) {
                // MATCH!
                c1.matched = true;
                c2.matched = true;
                this.matchedPairs++;
                this.streak++;
                this.firstCardIndex = null;
                this.secondCardIndex = null;
                this.isLocked = false;

                this.playMatchSound();

                if (this.matchedPairs === 8) {
                    this.handleVictory();
                }
            } else {
                // MISMATCH: Shake cards and lose 1 life
                this.streak = 0;
                c1.shake = true;
                c2.shake = true;
                this.lives = Math.max(0, this.lives - 1);

                this.playMismatchSound();

                setTimeout(() => {
                    c1.flipped = false;
                    c2.flipped = false;
                    c1.shake = false;
                    c2.shake = false;
                    this.firstCardIndex = null;
                    this.secondCardIndex = null;

                    if (this.lives <= 0 && this.gameState === 'playing') {
                        this.handleLoss('Geheugenverlies! Je hebt al je 3 levenspunten verspeeld door foute combinaties.');
                    } else if (this.gameState === 'playing') {
                        this.isLocked = false;
                    }
                }, 650);
            }
        },

        handleVictory() {
            if (this.timerInterval) clearInterval(this.timerInterval);
            this.timeTaken = Math.max(1, 60 - this.timerSeconds);
            this.gameState = 'finished';

            this.playVictorySound();
            this.sendFinishRequest(true);
        },

        handleLoss(reason) {
            if (this.timerInterval) clearInterval(this.timerInterval);
            this.timeTaken = Math.max(1, 60 - Math.max(0, this.timerSeconds));
            this.lossReason = reason;
            this.gameState = 'finished';

            this.playLoseSound();
            this.sendFinishRequest(false);
        },

        sendFinishRequest(won) {
            fetch(config.finishUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': config.csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    won: won,
                    time_taken: Math.max(1, this.timeTaken),
                    lives_left: Math.max(0, this.lives),
                    moves: Math.max(1, this.moves)
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    this.lastResult = data;
                    this.cooldownRemaining = data.cooldown || 0;
                    this.stats.games_won = data.games_won;
                    this.stats.games_played = data.games_played;
                    this.startCooldownTimer();
                    this.gameResult = won ? 'won' : 'lost';
                }
            })
            .catch(err => {
                console.error('Error submitting memory game result:', err);
                this.gameResult = won ? 'won' : 'lost';
            });
        },

        closeModal() {
            this.gameResult = null;
            this.gameState = 'idle';
            this.cards.forEach(c => {
                c.flipped = false;
                c.matched = false;
                c.shake = false;
            });
        },

        formatTime(totalSecs) {
            if (totalSecs <= 0) return '00:00';
            const m = Math.floor(totalSecs / 60);
            const s = Math.floor(totalSecs % 60);
            return (m < 10 ? '0' : '') + m + ':' + (s < 10 ? '0' : '') + s;
        },

        formatNumber(num) {
            return (num || 0).toLocaleString('nl-NL');
        }
    };
}
</script>

<style>
/* ROCK-SOLID 3D CARD CSS FOR ALL DEVICES */
.memory-grid {
    perspective: 1200px;
    -webkit-perspective: 1200px;
}

.memory-card {
    aspect-ratio: 1 / 1;
    min-height: 75px;
    touch-action: manipulation;
}

.memory-card-inner {
    position: relative;
    width: 100%;
    height: 100%;
    transform-style: preserve-3d;
    -webkit-transform-style: preserve-3d;
    transition: transform 0.4s cubic-bezier(0.34, 1.56, 0.64, 1), box-shadow 0.25s ease;
    border-radius: 1rem;
}

.memory-card-inner.is-flipped {
    transform: rotateY(180deg);
    -webkit-transform: rotateY(180deg);
}

.memory-card-inner.is-matched {
    animation: matchPulse 0.5s ease-out;
}

.memory-card-inner.is-shaking {
    animation: cardShake 0.5s cubic-bezier(.36,.07,.19,.97) both;
}

.memory-card-face {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    backface-visibility: hidden;
    -webkit-backface-visibility: hidden;
    border-radius: 1rem;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: space-between;
    user-select: none;
    -webkit-user-select: none;
}

.memory-card-back {
    transform: rotateY(0deg);
    -webkit-transform: rotateY(0deg);
    z-index: 2;
}

.memory-card-front {
    transform: rotateY(180deg);
    -webkit-transform: rotateY(180deg);
    z-index: 1;
}

@keyframes cardShake {
    0%, 100% { transform: rotateY(180deg) translateX(0); }
    20%, 60% { transform: rotateY(180deg) translateX(-7px); }
    40%, 80% { transform: rotateY(180deg) translateX(7px); }
}

@keyframes matchPulse {
    0% { transform: rotateY(180deg) scale(1); }
    50% { transform: rotateY(180deg) scale(1.08); }
    100% { transform: rotateY(180deg) scale(1); }
}

/* SPECIFIC CARD STYLES TO GUARANTEE VISIBILITY ACROSS ALL PLATFORMS & BROWSERS */
.card-badge-smokkelkoffer { background: linear-gradient(135deg, #9333ea 0%, #4338ca 100%) !important; }
.card-border-smokkelkoffer { border-color: #c084fc !important; }
.card-pill-smokkelkoffer { background-color: rgba(168, 85, 247, 0.15) !important; color: #9333ea !important; }

.card-badge-medicijnspuit { background: linear-gradient(135deg, #059669 0%, #0f766e 100%) !important; }
.card-border-medicijnspuit { border-color: #34d399 !important; }
.card-pill-medicijnspuit { background-color: rgba(16, 185, 129, 0.15) !important; color: #059669 !important; }

.card-badge-handboeien { background: linear-gradient(135deg, #334155 0%, #0f172a 100%) !important; }
.card-border-handboeien { border-color: #64748b !important; }
.card-pill-handboeien { background-color: rgba(100, 116, 139, 0.15) !important; color: #475569 !important; }

.card-badge-bende_masker { background: linear-gradient(135deg, #e11d48 0%, #be185d 100%) !important; }
.card-border-bende_masker { border-color: #fb7185 !important; }
.card-pill-bende_masker { background-color: rgba(244, 63, 94, 0.15) !important; color: #e11d48 !important; }

.card-badge-dodelijke_pillen { background: linear-gradient(135deg, #f59e0b 0%, #ea580c 100%) !important; }
.card-border-dodelijke_pillen { border-color: #fbbf24 !important; }
.card-pill-dodelijke_pillen { background-color: rgba(245, 158, 11, 0.15) !important; color: #d97706 !important; }

.card-badge-sleutelbos { background: linear-gradient(135deg, #4f46e5 0%, #2563eb 100%) !important; }
.card-border-sleutelbos { border-color: #818cf8 !important; }
.card-pill-sleutelbos { background-color: rgba(99, 102, 241, 0.15) !important; color: #4f46e5 !important; }

.card-badge-steekwapen { background: linear-gradient(135deg, #dc2626 0%, #991b1b 100%) !important; }
.card-border-steekwapen { border-color: #f87171 !important; }
.card-pill-steekwapen { background-color: rgba(239, 68, 68, 0.15) !important; color: #dc2626 !important; }

.card-badge-kluis { background: linear-gradient(135deg, #eab308 0%, #d97706 100%) !important; }
.card-border-kluis { border-color: #facc15 !important; }
.card-pill-kluis { background-color: rgba(234, 179, 8, 0.18) !important; color: #b45309 !important; }

.card-badge-portofoon { background: linear-gradient(135deg, #06b6d4 0%, #0d9488 100%) !important; }
.card-border-portofoon { border-color: #22d3ee !important; }
.card-pill-portofoon { background-color: rgba(6, 182, 212, 0.15) !important; color: #0891b2 !important; }

.card-badge-traliecel { background: linear-gradient(135deg, #ea580c 0%, #c2410c 100%) !important; }
.card-border-traliecel { border-color: #fb923c !important; }
.card-pill-traliecel { background-color: rgba(249, 115, 22, 0.15) !important; color: #c2410c !important; }
</style>
@endsection
