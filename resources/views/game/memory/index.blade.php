@extends('layouts.app')

@section('title', 'Memory Minigame (Rainbow) - TBS Kliniek')

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
        <div class="absolute -right-12 -top-12 w-48 h-48 bg-pink-500/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -left-12 -bottom-12 w-48 h-48 bg-purple-500/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="flex flex-col md:flex-row md:items-center justify-between gap-5 relative z-10">
            <div class="flex items-start gap-4">
                <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-pink-600 to-purple-600 text-white flex items-center justify-center text-2xl shadow-lg shadow-pink-500/25 shrink-0">
                    <i class="fa-solid fa-brain"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2 flex-wrap mb-1">
                        <span class="text-[10px] font-black uppercase tracking-wider text-pink-700 dark:text-pink-300 bg-pink-50 dark:bg-pink-950/80 px-2.5 py-0.5 rounded-full border border-pink-200 dark:border-pink-800 flex items-center gap-1">
                            <i class="fa-solid fa-bolt text-pink-500"></i> Minigame
                        </span>
                        <span class="text-[10px] font-black uppercase tracking-wider text-purple-700 dark:text-purple-300 bg-purple-50 dark:bg-purple-950/80 px-2.5 py-0.5 rounded-full border border-purple-200 dark:border-purple-800">
                            Community Idee: Rainbow
                        </span>
                        <span class="text-[10px] font-black uppercase tracking-wider text-rose-700 dark:text-rose-300 bg-rose-50 dark:bg-rose-950/80 px-2.5 py-0.5 rounded-full border border-rose-200 dark:border-rose-800">
                            Extreem Moeilijk
                        </span>
                    </div>
                    <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight">
                        TBS Kliniek Memory: Psychose Test
                    </h1>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 max-w-xl leading-relaxed">
                        Onthoud in <strong>5 seconden</strong> alle verborgen smokkelwaar. Daarna draaien de kaartjes om en moet je alle 8 paren matchen binnen <strong>60 seconden</strong>.
                        Pas op: je hebt maar <strong>3 levens</strong> (foutjes)!
                    </p>
                </div>
            </div>

            <!-- Stats Badge Quick Summary -->
            <div class="flex items-center gap-3 shrink-0">
                <div class="px-4 py-2.5 rounded-2xl bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700/80 text-center">
                    <span class="text-[10px] font-bold text-slate-400 block uppercase">Gewonnen</span>
                    <span class="text-base font-black text-purple-600 dark:text-purple-400 font-mono" x-text="stats.games_won">{{ $stat->games_won }}</span>
                </div>
                <div class="px-4 py-2.5 rounded-2xl bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700/80 text-center">
                    <span class="text-[10px] font-bold text-slate-400 block uppercase">Snelste Tijd</span>
                    <span class="text-base font-black text-amber-500 font-mono">{{ $stat->best_time_seconds ? $stat->best_time_seconds . 's' : '--' }}</span>
                </div>
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
                <div class="flex items-center justify-center gap-2 text-xs font-black text-purple-900 dark:text-purple-200">
                    <i class="fa-solid fa-bolt text-amber-500 animate-bounce"></i>
                    <span>Geen geduld? Reset direct via een sponsor-ad!</span>
                </div>
                <form action="{{ route('memory.skip_cooldown_ad') }}" method="POST">
                    @csrf
                    <button type="submit" 
                            class="w-full py-3.5 px-6 rounded-xl bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 active:scale-95 text-white font-black text-xs uppercase tracking-wider transition cursor-pointer shadow-md shadow-purple-600/30 flex items-center justify-center gap-2">
                        <i class="fa-solid fa-play"></i>
                        <span>Bekijk Sponsor-Ad (Direct Opnieuw Spelen)</span>
                    </button>
                </form>
                <p class="text-[10px] text-slate-400">Bekijk 1 sponsor-video en begin onmiddellijk weer een nieuwe Memory sessie.</p>
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
                               :class="i <= lives ? 'text-rose-500 scale-100 drop-shadow-xs' : 'text-slate-300 dark:text-slate-700 scale-90'"></i>
                        </template>
                    </div>
                </div>

                <!-- TIMER (60s COUNTDOWN) -->
                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/60 flex flex-col items-center justify-center">
                    <span class="text-[10px] font-black uppercase tracking-wider text-slate-400 mb-1">Resterende Tijd</span>
                    <div class="flex items-center gap-1.5 font-mono font-black text-base sm:text-lg"
                         :class="timerSeconds <= 15 ? 'text-rose-500 animate-pulse' : 'text-purple-600 dark:text-purple-400'">
                        <i class="fa-solid fa-clock text-xs"></i>
                        <span x-text="formatTime(timerSeconds)">01:00</span>
                    </div>
                </div>

                <!-- PAREN GEVONDEN (X / 8) -->
                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/60 flex flex-col items-center justify-center">
                    <span class="text-[10px] font-black uppercase tracking-wider text-slate-400 mb-1">Paren Gevonden</span>
                    <div class="font-mono font-black text-base sm:text-lg text-emerald-600 dark:text-emerald-400">
                        <span x-text="matchedPairs">0</span> / 8
                    </div>
                </div>

                <!-- ZETTEN GEDAAN -->
                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/60 flex flex-col items-center justify-center">
                    <span class="text-[10px] font-black uppercase tracking-wider text-slate-400 mb-1">Zetten</span>
                    <div class="font-mono font-black text-base sm:text-lg text-slate-700 dark:text-slate-200">
                        <span x-text="moves">0</span>
                    </div>
                </div>

            </div>

            <!-- INKIJKFASE COUNTDOWN ALERT BANNER -->
            <div x-show="gameState === 'preview'" 
                 x-transition
                 class="mt-3 p-3.5 rounded-xl bg-gradient-to-r from-amber-500 via-orange-500 to-pink-500 text-white font-black text-xs sm:text-sm text-center flex items-center justify-center gap-2 shadow-md">
                <i class="fa-solid fa-eye text-base animate-bounce"></i>
                <span>INKIJKFASE: Onthoud alle kaarten! Draaien om over <span class="font-mono text-base underline" x-text="previewSeconds">5</span>s</span>
            </div>

            <!-- START GAME BUTTON (WHEN IDLE & NO COOLDOWN) -->
            <div x-show="gameState === 'idle' && cooldownRemaining <= 0" class="mt-4 text-center">
                <button type="button" 
                        @click="startGame()"
                        class="px-8 py-3.5 rounded-2xl bg-gradient-to-r from-pink-600 via-purple-600 to-indigo-600 hover:from-pink-700 hover:to-indigo-700 active:scale-95 text-white font-black text-sm uppercase tracking-wider transition shadow-lg shadow-purple-600/30 cursor-pointer inline-flex items-center gap-2.5">
                    <i class="fa-solid fa-play"></i>
                    <span>Start Ronde (5s Inkijkfase + 55s Speeltijd)</span>
                </button>
            </div>
        </div>

        <!-- 4. MEMORY CARDS 4x4 GRID (MOBILE & DESKTOP PERFECTLY BALANCED) -->
        <div class="grid grid-cols-4 gap-2 sm:gap-3.5 max-w-xl mx-auto select-none" style="perspective: 1000px;">
            <template x-for="(card, index) in cards" :key="card.card_uid">
                <div class="relative w-full aspect-[4/5] sm:aspect-square cursor-pointer transition-transform duration-200 active:scale-95"
                     @click="flipCard(index)">
                    
                    <!-- 3D FLIP CONTAINER -->
                    <div class="w-full h-full duration-500 transition-transform rounded-2xl shadow-sm"
                         :style="card.flipped || card.matched ? 'transform: rotateY(180deg); transform-style: preserve-3d;' : 'transform-style: preserve-3d;'"
                         :class="card.shake ? 'animate-shake' : ''">

                        <!-- CARD BACK (TBS KLINIEK BRANDED SHIELD) -->
                        <div class="absolute inset-0 w-full h-full rounded-2xl bg-gradient-to-br from-slate-900 via-purple-950 to-slate-900 border-2 border-purple-500/40 p-2 sm:p-3 flex flex-col items-center justify-between text-center overflow-hidden shadow-md"
                             style="backface-visibility: hidden; -webkit-backface-visibility: hidden;">
                            <div class="w-full flex items-center justify-between opacity-30 text-[9px] text-purple-300 font-mono">
                                <i class="fa-solid fa-fingerprint"></i>
                                <span>#TBS</span>
                            </div>
                            <div class="flex flex-col items-center justify-center my-auto">
                                <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-purple-900/60 border border-purple-500/50 text-purple-300 flex items-center justify-center text-lg sm:text-xl shadow-inner group-hover:scale-105 transition-transform">
                                    <i class="fa-solid fa-shield-halved"></i>
                                </div>
                                <span class="text-[9px] sm:text-[10px] font-black text-purple-200/80 uppercase tracking-widest mt-1.5">MEMORY</span>
                            </div>
                            <div class="w-full text-center opacity-40 text-[8px] text-purple-400 font-bold uppercase tracking-wider">
                                Kliniek
                            </div>
                        </div>

                        <!-- CARD FRONT (TBS ASSET REVEALED) -->
                        <div class="absolute inset-0 w-full h-full rounded-2xl bg-gradient-to-br border-2 p-2 sm:p-2.5 flex flex-col items-center justify-between text-center overflow-hidden shadow-lg transition-all"
                             :class="[
                                 card.matched ? 'border-emerald-400 ring-2 ring-emerald-400/50 shadow-emerald-500/20' : card.border_color,
                                 'from-slate-900 via-slate-900 to-slate-950'
                             ]"
                             style="backface-visibility: hidden; -webkit-backface-visibility: hidden; transform: rotateY(180deg);">
                            
                            <!-- Card Category Pill -->
                            <div class="w-full flex items-center justify-between text-[8px] sm:text-[9px] font-black uppercase tracking-wider">
                                <span class="px-1.5 py-0.5 rounded-md"
                                      :class="card.matched ? 'bg-emerald-500 text-white' : 'bg-slate-800 text-slate-300'"
                                      x-text="card.category"></span>
                                <i x-show="card.matched" class="fa-solid fa-circle-check text-emerald-400 text-xs"></i>
                            </div>

                            <!-- Big Center Asset Icon in Glowing Badge -->
                            <div class="my-auto flex flex-col items-center justify-center">
                                <div class="w-11 h-11 sm:w-14 sm:h-14 rounded-2xl flex items-center justify-center text-xl sm:text-2xl shadow-md transition-transform"
                                     :class="[
                                         'bg-gradient-to-br ' + card.bg_gradient,
                                         card.matched ? 'scale-105 ring-2 ring-emerald-300' : ''
                                     ]"
                                     :style="'box-shadow: 0 0 15px ' + card.glow_color">
                                    <i class="fa-solid text-white" :class="card.icon"></i>
                                </div>
                            </div>

                            <!-- Card Title -->
                            <div class="w-full text-center">
                                <span class="text-[9px] sm:text-[11px] font-black text-white leading-tight block truncate"
                                      x-text="card.name"></span>
                            </div>

                        </div>

                    </div>
                </div>
            </template>
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
                        class="w-full py-3.5 px-6 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white font-black text-xs uppercase tracking-wider transition cursor-pointer shadow-md shadow-emerald-600/30">
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

    <!-- 7. COMMUNITY CREDITS & RULES FOOTER -->
    <div class="p-5 rounded-2xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-slate-500 dark:text-slate-400">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-purple-100 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400 flex items-center justify-center text-sm font-black shrink-0">
                <i class="fa-solid fa-lightbulb"></i>
            </div>
            <div>
                <p class="font-bold text-slate-700 dark:text-slate-200">
                    Ontworpen op suggestie van patiënt <strong>Rainbow</strong> (Support Ticket TBS-KYS-735).
                </p>
                <p class="text-[11px] text-slate-400">
                    Regels: 5 seconden inkijkfase &bull; 60s totaaltijd &bull; 3 levens (max 3 missers) &bull; 8 paren te vinden.
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
        firstCardIndex: null,
        secondCardIndex: null,
        isLocked: false,
        timerInterval: null,
        cooldownInterval: null,
        timeTaken: 0,
        lastResult: {},
        stats: {
            games_won: {{ (int) $stat->games_won }},
            games_played: {{ (int) $stat->games_played }}
        },

        initGame() {
            if (this.cooldownRemaining > 0) {
                this.startCooldownTimer();
            }
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
            
            // Reset board state
            this.lives = 3;
            this.moves = 0;
            this.matchedPairs = 0;
            this.firstCardIndex = null;
            this.secondCardIndex = null;
            this.isLocked = true; // no clicking during preview
            this.gameResult = null;
            this.previewSeconds = 5;
            this.timerSeconds = 60;
            this.gameState = 'preview';

            // All cards are flipped open for preview
            this.cards.forEach(c => {
                c.flipped = true;
                c.matched = false;
                c.shake = false;
            });

            // Start preview countdown (5s)
            const previewTimer = setInterval(() => {
                this.previewSeconds--;
                if (this.previewSeconds <= 0) {
                    clearInterval(previewTimer);
                    this.endPreviewAndStartPlaying();
                }
            }, 1000);
        },

        endPreviewAndStartPlaying() {
            // Flip all cards face down
            this.cards.forEach(c => c.flipped = false);
            this.gameState = 'playing';
            this.isLocked = false;

            // Start main game timer (60s countdown)
            if (this.timerInterval) clearInterval(this.timerInterval);
            const startTime = Date.now();

            this.timerInterval = setInterval(() => {
                this.timerSeconds--;
                if (this.timerSeconds <= 0) {
                    clearInterval(this.timerInterval);
                    this.handleLoss('Tijd verstreken! De 60 seconden zijn om voordat je alle paren vond.');
                }
            }, 1000);
        },

        flipCard(index) {
            if (this.isLocked) return;
            if (this.gameState !== 'playing') return;

            const card = this.cards[index];
            if (card.flipped || card.matched) return;

            // Flip current card
            card.flipped = true;

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

            if (c1.id === c2.id) {
                // MATCH!
                c1.matched = true;
                c2.matched = true;
                this.matchedPairs++;
                this.firstCardIndex = null;
                this.secondCardIndex = null;
                this.isLocked = false;

                if (this.matchedPairs === 8) {
                    this.handleVictory();
                }
            } else {
                // MISMATCH: Shake and lose 1 life
                c1.shake = true;
                c2.shake = true;
                this.lives--;

                setTimeout(() => {
                    c1.flipped = false;
                    c2.flipped = false;
                    c1.shake = false;
                    c2.shake = false;
                    this.firstCardIndex = null;
                    this.secondCardIndex = null;

                    if (this.lives <= 0) {
                        this.handleLoss('Geheugenverlies! Je hebt al je 3 levenspunten verspeeld door foute paren.');
                    } else {
                        this.isLocked = false;
                    }
                }, 800);
            }
        },

        handleVictory() {
            if (this.timerInterval) clearInterval(this.timerInterval);
            this.timeTaken = 60 - this.timerSeconds;
            this.gameState = 'finished';

            this.sendFinishRequest(true);
        },

        handleLoss(reason) {
            if (this.timerInterval) clearInterval(this.timerInterval);
            this.timeTaken = 60 - Math.max(0, this.timerSeconds);
            this.lossReason = reason;
            this.gameState = 'finished';

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
@keyframes shake {
    0%, 100% { transform: rotateY(180deg) translateX(0); }
    20%, 60% { transform: rotateY(180deg) translateX(-6px); }
    40%, 80% { transform: rotateY(180deg) translateX(6px); }
}
.animate-shake {
    animation: shake 0.5s cubic-bezier(.36,.07,.19,.97) both;
}
</style>
@endsection
