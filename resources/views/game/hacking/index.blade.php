@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="tbsHackingTerminal({
    canHack: {{ ($canHack && ! $character->isComatose() && ! $character->isImprisoned()) ? 'true' : 'false' }},
    initialStage: '{{ $currentStage }}',
    secondsLeftDaily: {{ $secondsUntilMidnight }},
    csrfToken: '{{ csrf_token() }}',
    puzzleRoute: '{{ route('hacking.puzzle') }}',
    spinRoute: '{{ route('hacking.spin') }}',
    sectors: {{ Js::from($wheelSectors) }}
})">

    <!-- Terminal Header Banner -->
    <div class="bg-slate-950 rounded-2xl shadow-xl border border-emerald-500/30 p-5 sm:p-7 relative overflow-hidden text-emerald-400 font-mono">
        <!-- Scanline Overlay -->
        <div class="absolute inset-0 bg-gradient-to-b from-transparent via-emerald-500/5 to-transparent pointer-events-none opacity-40"></div>
        <div class="absolute -right-10 -bottom-10 w-64 h-64 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="flex flex-col md:flex-row md:items-center justify-between gap-5 relative z-10">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-emerald-950/80 border-2 border-emerald-500/50 text-emerald-400 flex items-center justify-center text-2xl shadow-[0_0_20px_rgba(16,185,129,0.3)] shrink-0">
                    <i class="fa-solid fa-terminal"></i>
                </div>
                <div>
                    <div class="inline-flex items-center gap-2 bg-emerald-950/90 text-emerald-300 text-[10px] font-black uppercase tracking-widest px-2.5 py-0.5 rounded-md border border-emerald-500/40 mb-1">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        TBS Kliniek Beveiligde Server Terminal
                    </div>
                    <h1 class="text-xl sm:text-2xl font-black text-white tracking-tight flex items-center gap-2">
                        <span>Cyber Inbraak & Mainframe Root</span>
                        <span class="text-xs px-2 py-0.5 rounded-full bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">v4.2.0</span>
                    </h1>
                    <p class="text-xs sm:text-sm text-slate-400 mt-0.5">
                        Omzeil de 3 beveiligingslagen binnen 60s per puzzel en kraak de Hacker Spinner voor contanten, koffers, maskers en kluiscodes!
                    </p>
                </div>
            </div>

            <!-- Terminal Status Indicators -->
            <div class="flex items-center gap-3 shrink-0">
                <div class="bg-slate-900/90 border border-emerald-500/30 rounded-xl px-4 py-2.5 text-center min-w-[120px] shadow-inner">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Terminal Status</span>
                    <span class="text-sm font-black" :class="canHack && stage != 'done' ? 'text-emerald-400' : 'text-amber-400'">
                        <span x-text="canHack && stage != 'done' ? 'TOEGANG OPEN' : 'VERGRENDELD'"></span>
                    </span>
                    <span class="text-[9px] text-slate-500 block">1x Poging Per Dag</span>
                </div>

                <div class="bg-slate-900/90 border border-emerald-500/30 rounded-xl px-4 py-2.5 text-center min-w-[120px] shadow-inner">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Volgende Reset</span>
                    <span class="text-sm font-black text-white font-mono" x-text="formatTime(secondsLeftDaily)"></span>
                    <span class="text-[9px] text-slate-500 block">Middernacht (00:00)</span>
                </div>
            </div>
        </div>
    </div>

    <!-- MAIN TERMINAL SCREEN CONTAINER -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- Left Column: The Interactive Terminal Workspace (8 cols) -->
        <div class="lg:col-span-8 space-y-6">

            <!-- CRT TERMINAL SCREEN -->
            <div class="bg-slate-950 rounded-2xl border-2 border-emerald-500/40 shadow-[0_0_30px_rgba(16,185,129,0.15)] overflow-hidden font-mono relative">
                
                <!-- Monitor Top Bar -->
                <div class="bg-slate-900/90 border-b border-emerald-500/30 px-4 py-3 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-rose-500/80 inline-block"></span>
                        <span class="w-3 h-3 rounded-full bg-amber-500/80 inline-block"></span>
                        <span class="w-3 h-3 rounded-full bg-emerald-500/80 inline-block"></span>
                        <span class="text-xs text-slate-400 font-bold ml-2">root@tbs-mainframe:~#</span>
                    </div>
                    <div class="flex items-center gap-2 text-xs">
                        <div x-show="canHack && (stage == 1 || stage == 2 || stage == 3)" class="flex items-center gap-2 px-3 py-1 rounded-lg bg-rose-950/80 border border-rose-500/50 text-rose-300 font-black">
                            <i class="fa-solid fa-stopwatch animate-pulse"></i>
                            <span>ALARM TIMER: </span>
                            <span class="text-sm font-black text-rose-400" x-text="timerSeconds + 's'">60s</span>
                        </div>
                        <span class="text-[11px] px-2.5 py-0.5 rounded-full bg-emerald-950 text-emerald-400 border border-emerald-500/30 font-bold" x-text="'STAGE: ' + getStageLabel()"></span>
                    </div>
                </div>

                <!-- CRT Screen Body -->
                <div class="p-6 relative min-h-[440px] flex flex-col justify-between" style="background: radial-gradient(circle at center, #022c22 0%, #020617 80%);">

                    <!-- STATE 1: ALREADY COMPLETED TODAY (LOCKED UNTIL RESET) -->
                    <div x-show="!canHack || stage == 'done'" class="flex flex-col items-center justify-center text-center py-12 space-y-5 my-auto">
                        <div class="w-20 h-20 rounded-2xl bg-emerald-950 border-2 border-emerald-500/40 text-emerald-400 flex items-center justify-center text-3xl shadow-[0_0_30px_rgba(16,185,129,0.3)]">
                            <i class="fa-solid fa-lock"></i>
                        </div>
                        <div class="space-y-1">
                            @if($character->isComatose())
                                <h3 class="text-xl font-black text-rose-400 tracking-wide">PATIËNT IN COMA (CODE BLAUW)</h3>
                                <p class="text-xs text-rose-300/80 max-w-md mx-auto">
                                    Je ligt buiten bewustzijn op de intensive care. Je kunt geen mainframe hacken zolang je comateus bent!
                                </p>
                            @elseif($character->isImprisoned())
                                <h3 class="text-xl font-black text-amber-400 tracking-wide">OPGESLOTEN IN DE ISOLEERCEL</h3>
                                <p class="text-xs text-amber-300/80 max-w-md mx-auto">
                                    Je zit achter slot en grendel. Je hebt geen toegang tot terminals vanuit de isoleercel!
                                </p>
                            @else
                                <h3 class="text-xl font-black text-white tracking-wide">MAINFRAME LOCKOUT ACTIEF</h3>
                                <p class="text-xs text-emerald-300/80 max-w-md mx-auto">
                                    Je hebt vandaag je dagelijkse hackpoging al voltooid of de kliniekbeveiliging heeft de trace afgerond. De poorten resetten automatisch om middernacht!
                                </p>
                            @endif
                        </div>
                        <div class="p-4 rounded-xl bg-slate-900/80 border border-emerald-500/30 text-center">
                            <span class="text-[10px] text-slate-400 uppercase tracking-widest block mb-1">Volgende inbraakpoging beschikbaar over:</span>
                            <span class="text-2xl font-black text-emerald-400 font-mono tracking-wider" x-text="formatTime(secondsLeftDaily)">00:00:00</span>
                        </div>
                    </div>

                    <!-- PUZZLE 1: FIREWALL BYPASS (HEX NODE SEQUENCE) -->
                    <div x-show="canHack && stage == 1" class="space-y-5">
                        <div class="border-b border-emerald-500/20 pb-3 flex items-center justify-between">
                            <div>
                                <div class="text-[10px] text-emerald-500 uppercase tracking-widest font-black">Puzzel 1 van 3 (Moeilijkheid: Basis)</div>
                                <h2 class="text-base font-black text-white">FIREWALL ENCRYPTIE BYPASS</h2>
                            </div>
                            <span class="text-xs text-slate-400">Match de 3 cryptografische nodes in de juiste volgorde</span>
                        </div>

                        <!-- Target Sequence -->
                        <div class="p-3.5 rounded-xl bg-slate-900/90 border border-emerald-500/40 flex flex-wrap items-center justify-between gap-2">
                            <div class="text-xs font-bold text-slate-300">VEREISTE SLEUTEL REEKS:</div>
                            <div class="flex flex-wrap items-center gap-1.5 sm:gap-2">
                                <template x-for="(code, idx) in p1Target" :key="idx">
                                    <div class="px-3 py-1.5 rounded-lg text-xs font-black font-mono transition-all border"
                                         :class="p1UserStep > idx ? 'bg-emerald-500 text-slate-950 border-emerald-300 shadow-[0_0_10px_rgba(16,185,129,0.5)]' : (p1UserStep === idx ? 'bg-amber-500/20 text-amber-300 border-amber-400 animate-pulse' : 'bg-slate-800 text-slate-400 border-slate-700')">
                                        <span x-text="code"></span>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- 4x4 Grid of Hex Nodes -->
                        <div class="grid grid-cols-4 gap-2.5 max-w-md mx-auto pt-2">
                            <template x-for="(node, idx) in p1Grid" :key="idx">
                                <button type="button" @click="handleP1Click(node)"
                                        class="py-3 px-2 rounded-xl text-xs font-black font-mono border transition-all transform active:scale-95 cursor-pointer flex flex-col items-center justify-center gap-0.5"
                                        :class="node.status === 'selected' ? 'bg-emerald-500 text-slate-950 border-emerald-300 font-black shadow-[0_0_15px_rgba(16,185,129,0.6)]' : (node.status === 'wrong' ? 'bg-rose-500 text-white border-rose-300' : 'bg-slate-900/80 hover:bg-emerald-950/60 text-emerald-400 border-emerald-500/30 hover:border-emerald-400')">
                                    <span x-text="node.val"></span>
                                    <span class="text-[8px] text-slate-500 uppercase tracking-tighter">NODE-<span x-text="idx+1"></span></span>
                                </button>
                            </template>
                        </div>

                        <p class="text-center text-[11px] text-slate-400 italic">
                            Klik op de overeenkomende hex-codes volgens de reeks bovenaan. Foute invoer reset de buffer!
                        </p>
                    </div>

                    <!-- PUZZLE 2: CYPHER DECRYPTIE (POORT FREQUENTIES) -->
                    <div x-show="canHack && stage == 2" class="space-y-5">
                        <div class="border-b border-emerald-500/20 pb-3 flex items-center justify-between">
                            <div>
                                <div class="text-[10px] text-emerald-500 uppercase tracking-widest font-black">Puzzel 2 van 3 (Moeilijkheid: Gemiddeld)</div>
                                <h2 class="text-base font-black text-white">CYPHER POORT SYNCHRONISATIE</h2>
                            </div>
                            <span class="text-xs text-slate-400">Verbind alle 4 data-kanalen met het mainframe</span>
                        </div>

                        <!-- 4 Sync Ports -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
                            <template x-for="(port, idx) in p2Ports" :key="idx">
                                <div class="p-3.5 rounded-xl border transition-all"
                                     :class="port.synced ? 'bg-emerald-950/40 border-emerald-500/60 shadow-[0_0_15px_rgba(16,185,129,0.2)]' : 'bg-slate-900/80 border-slate-700'">
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="text-xs font-black text-white" x-text="port.name"></span>
                                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full"
                                              :class="port.synced ? 'bg-emerald-500 text-slate-950 font-black' : 'bg-amber-500/20 text-amber-400'">
                                            <span x-text="port.synced ? 'LOCKED' : 'DESYNC'"></span>
                                        </span>
                                    </div>
                                    <div class="space-y-1.5">
                                        <div class="flex items-center justify-between text-[11px] text-slate-400 font-mono">
                                            <span>Frequentie: <span class="text-emerald-400" x-text="port.current + ' MHz'"></span></span>
                                            <span>Doel: <span class="text-amber-300" x-text="port.target + ' MHz'"></span></span>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <button type="button" @click="adjustPort(idx, -5)" :disabled="port.synced"
                                                    class="w-7 h-7 rounded-lg bg-slate-800 hover:bg-slate-700 text-white font-black text-xs disabled:opacity-30 disabled:cursor-not-allowed cursor-pointer">-</button>
                                            <div class="flex-1 bg-slate-950 rounded-full h-2.5 border border-slate-700 overflow-hidden relative">
                                                <div class="bg-gradient-to-r from-emerald-500 to-cyan-400 h-full rounded-full transition-all"
                                                     :style="'width: ' + Math.min(100, Math.max(0, (port.current / port.target) * 100)) + '%'"></div>
                                            </div>
                                            <button type="button" @click="adjustPort(idx, +5)" :disabled="port.synced"
                                                    class="w-7 h-7 rounded-lg bg-slate-800 hover:bg-slate-700 text-white font-black text-xs disabled:opacity-30 disabled:cursor-not-allowed cursor-pointer">+</button>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <p class="text-center text-[11px] text-slate-400 italic">
                            Stel de frequenties van alle 4 de poorten af op exact hun doelwaarde om de tunnel te openen.
                        </p>
                    </div>

                    <!-- PUZZLE 3: HOOFDSERVER ROOT ACCESS (CIRCUIT OVERRIDE) -->
                    <div x-show="canHack && stage == 3" class="space-y-5">
                        <div class="border-b border-emerald-500/20 pb-3 flex items-center justify-between">
                            <div>
                                <div class="text-[10px] text-emerald-500 uppercase tracking-widest font-black">Puzzel 3 van 3 (Moeilijkheid: Expert)</div>
                                <h2 class="text-base font-black text-white">HOOFDSERVER ROOT ADMIN OVERRIDE</h2>
                            </div>
                            <span class="text-xs text-slate-400">Verbind de 3 hoofd-nodes en activeer de override</span>
                        </div>

                        <!-- 3 Core Processor Nodes -->
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-2">
                            <template x-for="(core, idx) in p3Cores" :key="idx">
                                <button type="button" @click="toggleCore(idx)"
                                        class="p-4 rounded-xl border transition-all text-center space-y-2 cursor-pointer transform active:scale-95"
                                        :class="core.active ? 'bg-emerald-950/80 border-emerald-400 shadow-[0_0_20px_rgba(16,185,129,0.4)]' : 'bg-slate-900/90 border-slate-700 hover:border-slate-500'">
                                    <div class="w-12 h-12 mx-auto rounded-xl flex items-center justify-center text-xl transition-all"
                                         :class="core.active ? 'bg-emerald-500 text-slate-950 shadow-md' : 'bg-slate-800 text-slate-500'">
                                        <i class="fa-solid" :class="core.icon"></i>
                                    </div>
                                    <div>
                                        <span class="text-xs font-black block text-white" x-text="core.name"></span>
                                        <span class="text-[10px] font-bold block" :class="core.active ? 'text-emerald-400' : 'text-slate-500'" x-text="core.active ? 'ONLINE (100%)' : 'OFFLINE (0%)'"></span>
                                    </div>
                                </button>
                            </template>
                        </div>

                        <!-- Master Override Switch -->
                        <div class="pt-4 text-center">
                            <button type="button" @click="checkP3Solution()"
                                    :disabled="!p3AllActive"
                                    class="px-8 py-3 rounded-xl font-black text-xs uppercase tracking-wider transition-all flex items-center gap-2 mx-auto"
                                    :class="p3AllActive ? 'bg-emerald-500 hover:bg-emerald-400 text-slate-950 shadow-[0_0_25px_rgba(16,185,129,0.7)] cursor-pointer active:scale-95' : 'bg-slate-800 text-slate-500 cursor-not-allowed'">
                                <i class="fa-solid fa-bolt"></i>
                                <span>EXECUTE ROOT OVERRIDE</span>
                            </button>
                        </div>
                    </div>

                    <!-- STAGE 4: THE 10-SECTOR HACKER SPINNER REWARD WHEEL -->
                    <div x-show="canHack && stage == 'wheel'" class="space-y-6 text-center py-2">
                        <div>
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-500/20 text-emerald-400 text-xs font-black border border-emerald-500/40 mb-2">
                                <i class="fa-solid fa-trophy"></i> ROOTHACK SUCCESVOL!
                            </div>
                            <h2 class="text-xl font-black text-white tracking-tight">HACKER SPINNER RAD (10 KANSEN)</h2>
                            <p class="text-xs text-slate-300">
                                Alle 3 de beveiligingslagen zijn omzeild! Draai nu aan het rad om je buit direct binnen te halen.
                            </p>
                        </div>

                        <!-- VIP Extra Bonus Banner if VIP -->
                        @if($character->isVip())
                            <div class="max-w-md mx-auto p-3 rounded-xl bg-gradient-to-r from-amber-500/20 to-yellow-500/20 border border-amber-400/50 flex items-center gap-3 text-left">
                                <div class="w-10 h-10 rounded-xl bg-amber-400 text-slate-950 flex items-center justify-center text-lg font-black shrink-0">
                                    <i class="fa-solid fa-crown"></i>
                                </div>
                                <div class="min-w-0">
                                    <span class="text-[10px] font-black uppercase text-amber-300 tracking-wider block">VIP Lidmaatschap Actief</span>
                                    <span class="text-xs font-bold text-white block">Je ontvangt automatisch de exclusieve VIP bonus (+€25K, +3⭐, +1XXL, +4🎭) bovenop je winst!</span>
                                </div>
                            </div>
                        @endif

                        <!-- Wheel Canvas Container -->
                        <div class="relative my-4 flex items-center justify-center">
                            <!-- Wheel Top Pointer -->
                            <div class="absolute -top-3 z-30 flex flex-col items-center pointer-events-none drop-shadow-md">
                                <div class="w-8 h-8 bg-emerald-400 clip-arrow shadow-md flex items-center justify-center text-slate-950 text-xs pt-0.5 font-black border-t-2 border-emerald-200">
                                    <i class="fa-solid fa-caret-down text-lg text-slate-950"></i>
                                </div>
                            </div>

                            <!-- The Wheel Canvas -->
                            <div class="p-2 rounded-full bg-slate-950 shadow-[0_0_35px_rgba(16,185,129,0.3)] border-4 border-emerald-500/40">
                                <canvas id="hackingWheelCanvas" width="360" height="360" class="max-w-[300px] sm:max-w-[340px] aspect-square rounded-full transition-transform duration-75"></canvas>
                            </div>

                            <!-- Center Centerpiece -->
                            <div class="absolute z-20 w-16 h-16 rounded-full bg-slate-950 border-4 border-emerald-400 text-emerald-400 flex flex-col items-center justify-center shadow-lg pointer-events-none">
                                <i class="fa-solid fa-laptop-code text-base"></i>
                                <span class="text-[8px] font-black uppercase tracking-wider text-white">ROOT</span>
                            </div>
                        </div>

                        <!-- Spin Button -->
                        <div class="max-w-xs mx-auto">
                            <button type="button" @click="spinWheel()" :disabled="isSpinning"
                                    class="w-full py-3.5 px-6 rounded-xl font-black text-sm uppercase tracking-wider transition-all duration-150 flex items-center justify-center gap-2 shadow-xs"
                                    :class="isSpinning ? 'bg-slate-800 text-slate-500 border border-slate-700 cursor-not-allowed' : 'bg-emerald-600 hover:bg-emerald-500 text-white cursor-pointer active:scale-95'">
                                <i class="fa-solid fa-dharmachakra" :class="isSpinning ? 'fa-spin' : ''"></i>
                                <span x-text="isSpinning ? 'BEZIG MET DRAAIEN...' : 'DRAAI HACKER SPINNER'"></span>
                            </button>
                        </div>
                    </div>

                    <!-- Terminal Status Notification Bar -->
                    <div class="mt-4 pt-3 border-t border-emerald-500/20 flex items-center justify-between text-xs text-slate-400">
                        <div class="flex items-center gap-2">
                            <span class="inline-block w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
                            <span x-text="statusLog">Systeem gereed voor inbraakcyclus.</span>
                        </div>
                        <span class="text-[10px] text-slate-500">AES-256 SSL CONNECTED</span>
                    </div>

                </div>
            </div>

        </div>

        <!-- Right Column: Rewards Legend & Recent Terminal Hackers (4 cols) -->
        <div class="lg:col-span-4 space-y-6">

            <!-- 10 REWARDS LEGEND CARD -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xs border border-slate-200/90 dark:border-slate-800 p-5 space-y-4">
                <div class="flex items-center gap-2.5 pb-3 border-b border-slate-100 dark:border-slate-800">
                    <span class="w-7 h-7 rounded-lg bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 border border-emerald-200/80 dark:border-emerald-800/70 flex items-center justify-center text-xs font-black">
                        <i class="fa-solid fa-gift"></i>
                    </span>
                    <div>
                        <h3 class="text-sm font-black text-slate-900 dark:text-white">10 Spinner Beloningen</h3>
                        <p class="text-[10px] text-slate-500 dark:text-slate-400">Alle mogelijke prijzen op het rad</p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-2 text-xs">
                    @foreach($wheelSectors as $sec)
                        <div class="p-2 rounded-xl bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70 flex items-center gap-2">
                            <span class="text-base shrink-0">{{ $sec['emoji'] }}</span>
                            <div class="min-w-0">
                                <span class="font-extrabold text-slate-900 dark:text-white block truncate">{{ $sec['short'] }}</span>
                                <span class="text-[9px] text-slate-400 block truncate">{{ $sec['label'] }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- VIP Perk Note -->
                <div class="p-3 rounded-xl bg-purple-50/80 dark:bg-slate-800/60 border border-purple-200/80 dark:border-purple-800/70 text-xs space-y-1">
                    <div class="flex items-center gap-1.5 font-black text-purple-700 dark:text-purple-300">
                        <i class="fa-solid fa-crown text-amber-500"></i>
                        <span>Exclusieve VIP Bonus</span>
                    </div>
                    <p class="text-[11px] text-purple-600 dark:text-purple-400 leading-relaxed">
                        Spelers met actieve VIP status krijgen bij elke succesvolle inbraak: <strong>+€25.000 Cash</strong>, <strong>+3 VIP Sterren ⭐</strong>, <strong>+1 XXL Koffer 🧳</strong> én <strong>+4 Heist Maskers 🎭</strong> extra bovenop hun hoofdprijs!
                    </p>
                </div>
            </div>

            <!-- RECENT TERMINAL HACKS FEED -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xs border border-slate-200/90 dark:border-slate-800 p-5 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                    <div class="flex items-center gap-2.5">
                        <span class="w-7 h-7 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200/80 dark:border-slate-700 flex items-center justify-center text-xs font-black">
                            <i class="fa-solid fa-list-check"></i>
                        </span>
                        <div>
                            <h3 class="text-sm font-black text-slate-900 dark:text-white">Recente Inbraken</h3>
                            <p class="text-[10px] text-slate-500 dark:text-slate-400">Succesvol gekraakte systemen</p>
                        </div>
                    </div>
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                </div>

                <div class="space-y-2 max-h-72 overflow-y-auto pr-1">
                    @forelse($recentLogs as $log)
                        <div class="p-2.5 rounded-xl bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70 flex items-center justify-between gap-3 text-xs">
                            <div class="min-w-0">
                                <span class="font-extrabold text-slate-900 dark:text-white block truncate">
                                    {{ $log->character?->name ?? 'Onbekende Hacker' }}
                                </span>
                                <span class="text-[10px] text-emerald-600 dark:text-emerald-400 font-bold block truncate">
                                    {{ $log->reward_label }}
                                </span>
                            </div>
                            <div class="text-right shrink-0">
                                <span class="text-[9px] text-slate-400 block">{{ $log->created_at->diffForHumans(null, true) }}</span>
                                @if($log->vip_bonus_applied)
                                    <span class="text-[9px] font-black text-amber-500"><i class="fa-solid fa-crown"></i> VIP</span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-slate-400 italic text-center py-4">Nog geen inbraken geregistreerd vandaag.</p>
                    @endforelse
                </div>
            </div>

        </div>

    </div>

    <!-- WIN MODAL (SUCCESS DIALOG) -->
    <div x-show="showWinModal" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 pb-24 lg:pb-4 bg-slate-950/80 backdrop-blur-sm"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        
        <div class="bg-slate-900 border-2 border-emerald-400 text-white rounded-3xl max-w-md w-full max-h-[85dvh] overflow-y-auto p-6 text-center space-y-5 shadow-[0_0_50px_rgba(16,185,129,0.4)] relative font-mono">
            <div class="w-16 h-16 mx-auto rounded-2xl bg-emerald-500 text-slate-950 flex items-center justify-center text-3xl shadow-[0_0_20px_rgba(16,185,129,0.6)]">
                <i class="fa-solid fa-trophy"></i>
            </div>

            <div class="space-y-1">
                <span class="text-xs font-black uppercase text-emerald-400 tracking-widest block">BUIT BINNENGEHAALD!</span>
                <h3 class="text-xl font-black text-white" x-text="wonReward ? wonReward.label : ''"></h3>
            </div>

            <!-- VIP Extra Bonus Display -->
            <template x-if="vipBonusApplied">
                <div class="p-3 rounded-2xl bg-amber-500/20 border border-amber-400/60 text-xs space-y-1 text-center">
                    <span class="text-amber-300 font-black block" x-text="vipBonusLabel"></span>
                    <span class="text-[10px] text-amber-200/80 block">Automatisch toegevoegd aan je inventaris & saldo!</span>
                </div>
            </template>

            <button type="button" @click="closeWinModal()"
                    class="w-full py-3 px-6 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-black text-xs uppercase tracking-wider transition cursor-pointer shadow-[0_0_20px_rgba(16,185,129,0.5)] active:scale-95">
                SLUITEN & TERUG NAAR TERMINAL
            </button>
        </div>
    </div>

</div>

<script>
function tbsHackingTerminal(config) {
    const rawStage = config.initialStage;
    const initialStageParsed = (!isNaN(rawStage) && rawStage !== '') ? parseInt(rawStage, 10) : rawStage;

    return {
        canHack: Boolean(config.canHack),
        stage: initialStageParsed, // 1, 2, 3, 'wheel', 'done'
        secondsLeftDaily: Math.max(0, Math.floor(Number(config.secondsLeftDaily) || 0)),
        csrfToken: config.csrfToken,
        puzzleRoute: config.puzzleRoute,
        spinRoute: config.spinRoute,
        sectors: config.sectors || [],

        // Timer
        timerSeconds: 60,
        timerInterval: null,
        dailyInterval: null,
        statusLog: 'Terminal online. Verbinding met TBS mainframe tot stand gebracht.',

        // Puzzle 1: Hex Sequence
        p1Target: ['0x7F', '0x3A', '0x9C'],
        p1UserStep: 0,
        p1Grid: [],

        // Puzzle 2: Port Sync
        p2Ports: [
            { name: 'Port 21 (FTP Data)', current: 20, target: 45, synced: false },
            { name: 'Port 80 (HTTP Tunnel)', current: 50, target: 80, synced: false },
            { name: 'Port 443 (SSL Secure)', current: 30, target: 90, synced: false },
            { name: 'Port 8080 (Proxy)', current: 15, target: 60, synced: false },
        ],

        // Puzzle 3: Core Override
        p3Cores: [
            { name: 'Firewall Daemon', icon: 'fa-shield-halved', active: false },
            { name: 'Mainframe CPU Alpha', icon: 'fa-microchip', active: false },
            { name: 'Database Root Access', icon: 'fa-database', active: false },
        ],

        // Wheel state
        wheelCanvas: null,
        wheelCtx: null,
        wheelAngle: 0,
        isSpinning: false,
        wonReward: null,
        vipBonusApplied: false,
        vipBonusLabel: '',
        showWinModal: false,

        get p3AllActive() {
            return this.p3Cores.every(c => c.active);
        },

        init() {
            this.initP1Grid();
            this.startDailyTimer();

            if (this.canHack && (this.stage == 1 || this.stage == 2 || this.stage == 3)) {
                this.startTimer();
            }

            if (this.stage == 'wheel') {
                this.$nextTick(() => {
                    this.initWheel();
                });
            }
        },

        getStageLabel() {
            if (!this.canHack || this.stage == 'done') return 'LOCKED';
            if (this.stage == 1) return '1/3 (FIREWALL)';
            if (this.stage == 2) return '2/3 (CYPHER)';
            if (this.stage == 3) return '3/3 (ROOT)';
            if (this.stage == 'wheel') return 'HACKER SPINNER';
            return 'SYSTEM';
        },

        formatTime(seconds) {
            const totalSecs = Math.max(0, Math.floor(Number(seconds) || 0));
            const h = Math.floor(totalSecs / 3600);
            const m = Math.floor((totalSecs % 3600) / 60);
            const s = totalSecs % 60;
            return String(h).padStart(2, '0') + ':' + String(m).padStart(2, '0') + ':' + String(s).padStart(2, '0');
        },

        startDailyTimer() {
            if (this.dailyInterval) clearInterval(this.dailyInterval);
            this.dailyInterval = setInterval(() => {
                if (this.secondsLeftDaily > 0) {
                    this.secondsLeftDaily--;
                }
            }, 1000);
        },

        startTimer() {
            this.timerSeconds = 60;
            if (this.timerInterval) clearInterval(this.timerInterval);
            this.timerInterval = setInterval(() => {
                if (this.timerSeconds > 0) {
                    this.timerSeconds--;
                } else {
                    clearInterval(this.timerInterval);
                    this.statusLog = 'ALARM! Tijd verstreken (60s). Puzzel gereset!';
                    this.resetCurrentPuzzle();
                    this.startTimer();
                }
            }, 1000);
        },

        resetCurrentPuzzle() {
            if (this.stage == 1) {
                this.p1UserStep = 0;
                this.initP1Grid();
            } else if (this.stage == 2) {
                this.p2Ports.forEach(p => p.synced = false);
            } else if (this.stage == 3) {
                this.p3Cores.forEach(c => c.active = false);
            }
        },

        // --- PUZZLE 1 LOGIC ---
        initP1Grid() {
            const pool = ['0x1A', '0x7F', '0x4B', '0x3A', '0x8E', '0x9C', '0x2D', '0x6F', '0x5C', '0xE1', '0xBB', '0xA4', '0xD8', '0xC3', '0xF0', '0x09'];
            // Shuffle pool
            const shuffled = [...pool].sort(() => 0.5 - Math.random());
            this.p1Grid = shuffled.map(val => ({ val, status: 'idle' }));
        },

        handleP1Click(node) {
            const requiredVal = this.p1Target[this.p1UserStep];
            if (node.val === requiredVal) {
                node.status = 'selected';
                this.p1UserStep++;
                this.statusLog = `Hex node ${node.val} correct gevalideerd! (${this.p1UserStep}/3)`;
                if (this.p1UserStep === this.p1Target.length) {
                    this.submitPuzzle(1);
                }
            } else {
                node.status = 'wrong';
                this.statusLog = `Onjuiste hex node ${node.val}! Sequentie buffer gewist.`;
                setTimeout(() => {
                    this.p1UserStep = 0;
                    this.p1Grid.forEach(n => n.status = 'idle');
                }, 600);
            }
        },

        // --- PUZZLE 2 LOGIC ---
        adjustPort(idx, delta) {
            const port = this.p2Ports[idx];
            port.current = Math.min(100, Math.max(0, port.current + delta));
            if (port.current === port.target) {
                port.synced = true;
                this.statusLog = `${port.name} poort gesynchroniseerd op ${port.target} MHz!`;
                if (this.p2Ports.every(p => p.synced)) {
                    this.submitPuzzle(2);
                }
            }
        },

        // --- PUZZLE 3 LOGIC ---
        toggleCore(idx) {
            this.p3Cores[idx].active = !this.p3Cores[idx].active;
            this.statusLog = `Core ${this.p3Cores[idx].name} geschakeld naar: ${this.p3Cores[idx].active ? 'ONLINE' : 'OFFLINE'}`;
        },

        checkP3Solution() {
            if (this.p3AllActive) {
                this.submitPuzzle(3);
            }
        },

        // Submit puzzle to server
        async submitPuzzle(num) {
            try {
                this.statusLog = `Bezig met authenticeren van Puzzel ${num}...`;
                const response = await fetch(this.puzzleRoute, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ puzzle: num })
                });

                const data = await response.json();
                if (data.success) {
                    const nextStageRaw = data.next_stage;
                    this.stage = (!isNaN(nextStageRaw) && nextStageRaw !== '') ? parseInt(nextStageRaw, 10) : nextStageRaw;
                    this.statusLog = data.message;
                    if (this.stage == 'wheel') {
                        if (this.timerInterval) clearInterval(this.timerInterval);
                        this.$nextTick(() => {
                            this.initWheel();
                        });
                    } else {
                        this.startTimer();
                    }
                } else {
                    this.statusLog = 'Fout: ' + data.message;
                }
            } catch (err) {
                this.statusLog = 'Verbindingsfout met mainframe server!';
            }
        },

        // --- STAGE 4: WHEEL OF FORTUNE ---
        initWheel() {
            this.wheelCanvas = document.getElementById('hackingWheelCanvas');
            if (!this.wheelCanvas) return;
            this.wheelCtx = this.wheelCanvas.getContext('2d');
            this.drawWheel();
        },

        drawWheel() {
            if (!this.wheelCtx || !this.wheelCanvas) return;
            const ctx = this.wheelCtx;
            const w = this.wheelCanvas.width;
            const h = this.wheelCanvas.height;
            const cx = w / 2;
            const cy = h / 2;
            const radius = cx - 8;
            const numSectors = this.sectors.length;
            if (numSectors === 0) return;
            const arc = (2 * Math.PI) / numSectors;

            ctx.clearRect(0, 0, w, h);

            for (let i = 0; i < numSectors; i++) {
                const angle = this.wheelAngle + (i * arc);
                const sector = this.sectors[i];

                ctx.save();
                ctx.beginPath();
                ctx.moveTo(cx, cy);
                ctx.arc(cx, cy, radius, angle, angle + arc);
                ctx.closePath();
                ctx.fillStyle = sector.color || '#059669';
                ctx.fill();
                ctx.lineWidth = 2;
                ctx.strokeStyle = '#022c22';
                ctx.stroke();

                // Draw sector label text
                ctx.save();
                ctx.translate(cx, cy);
                ctx.rotate(angle + arc / 2);
                ctx.textAlign = 'right';
                ctx.fillStyle = '#ffffff';
                ctx.font = 'bold 11px monospace';
                ctx.shadowColor = 'rgba(0,0,0,0.8)';
                ctx.shadowBlur = 4;
                ctx.fillText(sector.short + ' ' + (sector.emoji || ''), radius - 16, 4);
                ctx.restore();

                ctx.restore();
            }
        },

        async spinWheel() {
            if (this.isSpinning) return;
            this.isSpinning = true;
            this.statusLog = 'Hacker Spinner geactiveerd! Willekeurige sector wordt berekend...';

            try {
                const response = await fetch(this.spinRoute, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                        'Accept': 'application/json',
                    }
                });

                const data = await response.json();
                if (!data.success) {
                    this.statusLog = 'Fout: ' + data.message;
                    this.isSpinning = false;
                    return;
                }

                // Sector index is 0..9
                const winningIndex = data.sector_index;
                const numSectors = this.sectors.length;
                const arc = (2 * Math.PI) / numSectors;

                // We want the winning sector to land at the TOP (angle = 3*PI/2)
                const targetSectorCenter = (winningIndex * arc) + (arc / 2);
                const targetAngle = (3 * Math.PI / 2) - targetSectorCenter;

                // Add 6 full rotations
                const extraRotations = 6 * 2 * Math.PI;
                const finalAngle = targetAngle + extraRotations;

                const startAngle = this.wheelAngle % (2 * Math.PI);
                const totalChange = finalAngle - startAngle;
                const duration = 5000;
                const startTime = performance.now();

                const animate = (currentTime) => {
                    const elapsed = currentTime - startTime;
                    const progress = Math.min(1, elapsed / duration);
                    // Ease out cubic
                    const easeOut = 1 - Math.pow(1 - progress, 3);

                    this.wheelAngle = startAngle + (totalChange * easeOut);
                    this.drawWheel();

                    if (progress < 1) {
                        requestAnimationFrame(animate);
                    } else {
                        this.isSpinning = false;
                        this.stage = 'done';
                        this.canHack = false;
                        this.wonReward = data.reward;
                        this.vipBonusApplied = data.vip_bonus_applied;
                        this.vipBonusLabel = data.vip_bonus_label;
                        this.showWinModal = true;
                        this.statusLog = data.message;
                    }
                };

                requestAnimationFrame(animate);

            } catch (err) {
                this.statusLog = 'Netwerkfout tijdens het draaien!';
                this.isSpinning = false;
            }
        },

        closeWinModal() {
            this.showWinModal = false;
            window.location.reload();
        }
    };
}
</script>
@endsection
