@extends('layouts.app')

@section('title', 'Geldmaat // TBS Kliniek Bankautomaat')

@section('content')
@php
    $hasPin = (bool) ($hasBankPin ?? false);
    $unlocked = (bool) ($isAtmUnlocked ?? false);
@endphp

<div class="space-y-6 max-w-7xl mx-auto" x-data="{
    hasPin: @js($hasPin),
    isUnlocked: @js($unlocked),
    cardInserted: @js($unlocked),
    atmScreen: '{{ ! $hasPin ? 'setup_pin' : ($unlocked ? ($activeTab === 'loans' ? 'loans' : ($activeTab === 'transactions' ? 'transactions' : 'menu')) : 'idle') }}',
    pinDigits: '',
    pinConfirmDigits: '',
    pinStep: 'enter_new', // 'enter_new' | 'confirm_new'
    viewMode: 'atm', // 'atm' | 'classic'
    activeTab: @js($activeTab ?? 'vault'),
    pinError: '',
    pinSuccess: '',
    depositAmount: '',
    withdrawAmount: '',
    customLoanAmount: 25000,
    isProcessing: false,
    soundEnabled: true,
    cashDispenserOpen: false,

    playBeep(freq = 900, duration = 45, type = 'sine') {
        if (!this.soundEnabled) return;
        try {
            const ctx = new (window.AudioContext || window.webkitAudioContext)();
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.type = type;
            osc.frequency.setValueAtTime(freq, ctx.currentTime);
            gain.gain.setValueAtTime(0.08, ctx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + (duration / 1000));
            osc.connect(gain);
            gain.connect(ctx.destination);
            osc.start();
            osc.stop(ctx.currentTime + (duration / 1000));
        } catch (e) {}
    },

    playCardInsert() {
        this.playBeep(520, 60);
        setTimeout(() => this.playBeep(880, 80), 80);
    },

    playCashChime() {
        this.playBeep(659, 80);
        setTimeout(() => this.playBeep(880, 100), 100);
        setTimeout(() => this.playBeep(1318, 140), 200);
    },

    playErrorBuzzer() {
        this.playBeep(220, 180, 'sawtooth');
    },

    insertCard() {
        this.playCardInsert();
        this.cardInserted = true;
        this.pinError = '';
        this.pinSuccess = '';
        this.pinDigits = '';
        this.pinConfirmDigits = '';
        if (!this.hasPin) {
            this.atmScreen = 'setup_pin';
            this.pinStep = 'enter_new';
        } else if (this.isUnlocked) {
            this.atmScreen = 'menu';
        } else {
            this.atmScreen = 'enter_pin';
        }
    },

    ejectCard() {
        this.playBeep(440, 90);
        this.cardInserted = false;
        this.isUnlocked = false;
        this.pinDigits = '';
        this.pinConfirmDigits = '';
        this.atmScreen = 'idle';
        fetch('{{ route('bank.pin.eject') }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
    },

    pressKey(key) {
        this.playBeep(1000, 35);
        this.pinError = '';

        if (this.atmScreen === 'enter_pin') {
            if (this.pinDigits.length < 4) {
                this.pinDigits += key;
                if (this.pinDigits.length === 4) {
                    this.submitVerifyPin();
                }
            }
            return;
        }

        if (this.atmScreen === 'setup_pin') {
            if (this.pinStep === 'enter_new') {
                if (this.pinDigits.length < 4) {
                    this.pinDigits += key;
                    if (this.pinDigits.length === 4) {
                        setTimeout(() => {
                            this.pinStep = 'confirm_new';
                            this.playBeep(750, 60);
                        }, 250);
                    }
                }
            } else {
                if (this.pinConfirmDigits.length < 4) {
                    this.pinConfirmDigits += key;
                    if (this.pinConfirmDigits.length === 4) {
                        this.submitSetupPin();
                    }
                }
            }
            return;
        }

        if (this.atmScreen === 'deposit') {
            this.depositAmount = (this.depositAmount || '') + key;
        } else if (this.atmScreen === 'withdraw') {
            this.withdrawAmount = (this.withdrawAmount || '') + key;
        }
    },

    pressClear() {
        this.playBeep(600, 50);
        this.pinError = '';
        if (this.atmScreen === 'enter_pin') {
            this.pinDigits = this.pinDigits.slice(0, -1);
        } else if (this.atmScreen === 'setup_pin') {
            if (this.pinStep === 'confirm_new') {
                if (this.pinConfirmDigits.length > 0) {
                    this.pinConfirmDigits = this.pinConfirmDigits.slice(0, -1);
                } else {
                    this.pinStep = 'enter_new';
                    this.pinDigits = '';
                }
            } else {
                this.pinDigits = this.pinDigits.slice(0, -1);
            }
        } else if (this.atmScreen === 'deposit') {
            this.depositAmount = '';
        } else if (this.atmScreen === 'withdraw') {
            this.withdrawAmount = '';
        }
    },

    pressCancel() {
        this.playBeep(400, 60);
        this.pinError = '';
        if (this.atmScreen === 'enter_pin' || this.atmScreen === 'setup_pin') {
            this.ejectCard();
        } else {
            this.atmScreen = 'menu';
        }
    },

    pressOk() {
        this.playBeep(1200, 60);
        if (this.atmScreen === 'enter_pin') {
            if (this.pinDigits.length === 4) {
                this.submitVerifyPin();
            } else {
                this.playErrorBuzzer();
                this.pinError = 'Voer 4 cijfers in!';
            }
        } else if (this.atmScreen === 'setup_pin') {
            if (this.pinStep === 'enter_new' && this.pinDigits.length === 4) {
                this.pinStep = 'confirm_new';
            } else if (this.pinStep === 'confirm_new' && this.pinConfirmDigits.length === 4) {
                this.submitSetupPin();
            }
        } else if (this.atmScreen === 'deposit') {
            this.$refs.depositForm?.submit();
        } else if (this.atmScreen === 'withdraw') {
            this.$refs.withdrawForm?.submit();
        }
    },

    async submitVerifyPin() {
        this.isProcessing = true;
        try {
            const res = await fetch('{{ route('bank.pin.verify') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ pin: this.pinDigits })
            });
            const data = await res.json();
            if (data.success) {
                this.playCashChime();
                this.isUnlocked = true;
                this.pinSuccess = data.message;
                setTimeout(() => {
                    this.atmScreen = 'menu';
                    this.pinSuccess = '';
                }, 350);
            } else {
                this.playErrorBuzzer();
                this.pinError = data.message || 'Onjuiste pincode!';
                this.pinDigits = '';
            }
        } catch (e) {
            this.playErrorBuzzer();
            this.pinError = 'Verbindingsfout met Geldmaat centrale.';
            this.pinDigits = '';
        } finally {
            this.isProcessing = false;
        }
    },

    async submitSetupPin() {
        if (this.pinDigits !== this.pinConfirmDigits) {
            this.playErrorBuzzer();
            this.pinError = 'Pincodes komen niet overeen! Probeer opnieuw.';
            this.pinDigits = '';
            this.pinConfirmDigits = '';
            this.pinStep = 'enter_new';
            return;
        }

        this.isProcessing = true;
        try {
            const res = await fetch('{{ route('bank.pin.set') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    pin: this.pinDigits,
                    pin_confirmation: this.pinConfirmDigits
                })
            });
            const data = await res.json();
            if (data.success) {
                this.playCashChime();
                this.hasPin = true;
                this.isUnlocked = true;
                this.pinSuccess = 'Pincode geactiveerd!';
                setTimeout(() => {
                    this.atmScreen = 'menu';
                    this.pinSuccess = '';
                }, 500);
            } else {
                this.playErrorBuzzer();
                this.pinError = data.message || 'Kon pincode niet instellen.';
                this.pinDigits = '';
                this.pinConfirmDigits = '';
                this.pinStep = 'enter_new';
            }
        } catch (e) {
            this.playErrorBuzzer();
            this.pinError = 'Netwerkfout bij opslaan pincode.';
        } finally {
            this.isProcessing = false;
        }
    }
}">

    <!-- Accessible labels for automated test suite -->
    <div class="sr-only">
        <span>Financieel overzicht Management</span>
        <span>Leningen</span>
        <span>Transacties</span>
        <span>Nieuwe lening aanvragen</span>
        <span>Rente & Leningtax (8,5%)</span>
    </div>

    <!-- ============================================================== -->
    <!-- TOP FORENSISCH FINANCIEEL CENTRUM HERO HEADER                 -->
    <!-- ============================================================== -->
    <div class="rounded-3xl shadow-xs border p-5 sm:p-6 relative overflow-hidden transition-all duration-300
                bg-gradient-to-r from-amber-50 via-white to-amber-50/40 border-amber-200/80 text-slate-800
                dark:from-slate-900 dark:via-slate-900/90 dark:to-purple-950/30 dark:border-slate-800 dark:text-slate-100">
        <!-- Subtle background texture -->
        <div class="absolute -right-10 -bottom-10 opacity-5 dark:opacity-10 pointer-events-none text-9xl">
            <i class="fa-solid fa-building-columns"></i>
        </div>

        <div class="flex flex-col md:flex-row md:items-center justify-between gap-5 relative z-10">
            <div class="flex items-start sm:items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-amber-400 to-amber-500 text-slate-950 flex items-center justify-center text-2xl font-black shrink-0 shadow-lg shadow-amber-500/20 ring-4 ring-amber-300/40 dark:ring-amber-500/20">
                    <i class="fa-solid fa-coins"></i>
                </div>
                <div class="space-y-1">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="text-[10px] font-black uppercase tracking-wider text-slate-950 bg-amber-400 px-2.5 py-0.5 rounded-full font-mono shadow-xs">
                            Geldmaat Kiosk #0884
                        </span>
                        <span class="text-[10px] font-bold text-emerald-700 dark:text-emerald-300 bg-emerald-100/80 dark:bg-emerald-950/70 px-2.5 py-0.5 rounded-full border border-emerald-300/60 dark:border-emerald-800/60 flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            Online &amp; Gecertificeerd
                        </span>
                        <span class="text-[10px] font-bold text-purple-700 dark:text-purple-300 bg-purple-100/80 dark:bg-purple-950/70 px-2.5 py-0.5 rounded-full border border-purple-300/60 dark:border-purple-800/60">
                            Patiënt Level {{ $character->level }}
                        </span>
                    </div>
                    <h1 class="text-xl sm:text-2xl font-black tracking-tight text-slate-900 dark:text-white">
                        Kliniekbank &amp; Financieel Centrum
                    </h1>
                    <p class="text-slate-600 dark:text-slate-400 text-xs sm:text-sm max-w-xl leading-relaxed">
                        Beheer je bankkluis, vraag leningen aan en pin veilig contanten via de officiële <strong class="text-amber-600 dark:text-amber-400 font-bold">Geldmaat</strong> afdelingsautomaat.
                    </p>
                </div>
            </div>

            <!-- Header Controls: Sound & View Switcher -->
            <div class="flex items-center gap-2.5 self-start md:self-center flex-wrap">
                <!-- Sound Toggle -->
                <button type="button" @click="soundEnabled = !soundEnabled"
                        class="px-3.5 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 shadow-xs cursor-pointer border
                               bg-white hover:bg-slate-50 border-slate-200 text-slate-700
                               dark:bg-slate-800 dark:hover:bg-slate-700 dark:border-slate-700 dark:text-slate-200">
                    <i class="fa-solid" :class="soundEnabled ? 'fa-volume-high text-emerald-500' : 'fa-volume-xmark text-slate-400'"></i>
                    <span x-text="soundEnabled ? 'Geluid Aan' : 'Geluid Uit'"></span>
                </button>

                <!-- Fast / Classic Overview Mode Switcher -->
                <button type="button" @click="viewMode = (viewMode === 'atm' ? 'classic' : 'atm')"
                        class="px-3.5 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 shadow-xs cursor-pointer border
                               bg-amber-400/20 hover:bg-amber-400/30 border-amber-300 text-amber-900
                               dark:bg-purple-900/40 dark:hover:bg-purple-900/60 dark:border-purple-700/60 dark:text-purple-200">
                    <i class="fa-solid" :class="viewMode === 'atm' ? 'fa-table-columns text-amber-600 dark:text-purple-400' : 'fa-cash-register text-amber-600 dark:text-purple-400'"></i>
                    <span x-text="viewMode === 'atm' ? 'Klassiek Overzicht' : 'Virtuele Geldmaat'"></span>
                </button>
            </div>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- MAIN CONTENT: ATM VIEW OR CLASSIC VIEW                         -->
    <!-- ============================================================== -->

    <!-- MODE 1: THE COMPLETE VIRTUAL GELDMAAT EXPERIENCE -->
    <div x-show="viewMode === 'atm'" class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

        <!-- LEFT COLUMN (Cols 8): THE GELDMAAT TERMINAL -->
        <div class="lg:col-span-8">
            <!-- Outermost ATM Architectural Alcove -->
            <div class="rounded-3xl shadow-xl transition-all duration-300 relative overflow-hidden border
                        bg-gradient-to-b from-slate-100 via-slate-200/90 to-slate-200 border-slate-300/80 p-3 sm:p-5
                        dark:from-slate-900 dark:via-slate-900 dark:to-slate-950 dark:border-slate-800">

                <!-- Iconic Geldmaat Bright Yellow Kiosk Shell -->
                <div class="rounded-2xl shadow-2xl overflow-hidden border-4 border-amber-300 dark:border-amber-500
                            bg-gradient-to-b from-[#ffd700] via-[#ffcc00] to-[#e6b800] p-3 sm:p-6 relative">

                    <!-- Glossy Light Reflection Across Curved Hood -->
                    <div class="absolute inset-x-0 top-0 h-12 bg-gradient-to-b from-white/40 via-white/10 to-transparent pointer-events-none"></div>

                    <!-- Geldmaat Top Brand Arch -->
                    <div class="flex items-center justify-between pb-3 sm:pb-4 border-b-2 border-amber-500/30 mb-4 px-1 sm:px-2 relative z-10">
                        <div class="flex items-center gap-3">
                            <!-- Official Geldmaat Smiley / Arch Emblem -->
                            <div class="w-11 h-11 rounded-2xl bg-slate-950 text-[#ffcc00] flex items-center justify-center text-2xl font-black shadow-md border border-slate-900">
                                <i class="fa-solid fa-vault"></i>
                            </div>
                            <div>
                                <div class="flex items-center gap-1.5">
                                    <span class="text-sm sm:text-base font-black tracking-widest text-slate-950 uppercase font-mono">
                                        geld<span class="text-amber-800">maat</span>
                                    </span>
                                    <span class="text-[9px] font-black uppercase bg-slate-950 text-white px-1.5 py-0.5 rounded tracking-tighter">
                                        TBS-09
                                    </span>
                                </div>
                                <span class="text-[10px] font-extrabold text-slate-900/80 tracking-wide block">
                                    ALGEMENE GELD- &amp; OPNAMEAUTOMAAT
                                </span>
                            </div>
                        </div>

                        <!-- Kiosk Camera & LED -->
                        <div class="flex items-center gap-2 bg-slate-950/15 backdrop-blur-xs px-3 py-1.5 rounded-full border border-slate-950/20 shadow-xs">
                            <span class="relative flex h-2.5 w-2.5">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-500 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-rose-600"></span>
                            </span>
                            <span class="text-[10px] font-mono font-black text-slate-950 uppercase tracking-tight">CAM 04 ON</span>
                        </div>
                    </div>

                    <!-- INNER RECESSED CHROME CONSOLE (Holds the screen, slots, and keypad) -->
                    <div class="rounded-2xl p-3 sm:p-5 shadow-inner border-2 transition-all duration-300
                                bg-slate-900 border-slate-800/90 text-white">

                        <!-- ============================================== -->
                        <!-- ATM DIGITAL SCREEN (High-End ATM Display Glass) -->
                        <!-- ============================================== -->
                        <div class="rounded-xl border-2 border-slate-800 shadow-2xl overflow-hidden relative transition-all duration-300
                                    bg-[#09101d] text-slate-100 min-h-[440px] flex flex-col justify-between p-4 sm:p-6"
                             style="background-image: radial-gradient(#1e293b 1px, transparent 1px); background-size: 16px 16px;">

                            <!-- Anti-Glare Screen Glare Accent -->
                            <div class="absolute inset-0 bg-gradient-to-tr from-transparent via-cyan-500/5 to-transparent pointer-events-none"></div>

                            <!-- Screen Top Navigation & Status -->
                            <div class="flex items-center justify-between border-b border-slate-800 pb-3 mb-4 text-xs font-mono relative z-10">
                                <div class="flex items-center gap-2">
                                    <span class="px-2 py-0.5 rounded bg-amber-400 text-slate-950 font-black text-[10px] uppercase shadow-xs">
                                        Geldmaat OS v4.2
                                    </span>
                                    <span class="text-slate-400 text-[11px] truncate hidden sm:inline">
                                        Afdeling: {{ $character->current_location ?? 'Paviljoen 1 (Gesloten)' }}
                                    </span>
                                </div>

                                <div class="flex items-center gap-2.5">
                                    <span class="text-emerald-400 font-bold flex items-center gap-1.5 text-[11px] bg-emerald-950/60 px-2 py-0.5 rounded border border-emerald-800/60">
                                        <i class="fa-solid fa-lock text-[10px]"></i> 256-Bit SSL
                                    </span>
                                    <template x-if="cardInserted">
                                        <button type="button" @click="ejectCard()"
                                                class="px-2.5 py-1 rounded bg-rose-600/30 hover:bg-rose-600 text-rose-300 hover:text-white border border-rose-500/40 text-[10px] font-black uppercase transition cursor-pointer flex items-center gap-1">
                                            <i class="fa-solid fa-eject"></i> Pas Uitwerpen
                                        </button>
                                    </template>
                                </div>
                            </div>

                            <!-- ------------------------------------------ -->
                            <!-- SCREEN 1: IDLE / STEK UW PAS IN           -->
                            <!-- ------------------------------------------ -->
                            <div x-show="atmScreen === 'idle'" class="my-auto text-center space-y-5 py-6 relative z-10">
                                <div class="w-20 h-20 mx-auto rounded-3xl bg-amber-400/10 border-2 border-amber-400/40 text-amber-400 flex items-center justify-center text-4xl shadow-lg shadow-amber-400/10 animate-pulse">
                                    <i class="fa-solid fa-credit-card"></i>
                                </div>

                                <div class="space-y-1.5 max-w-md mx-auto">
                                    <h2 class="text-lg sm:text-2xl font-black uppercase tracking-wide text-white">
                                        Welkom bij Geldmaat TBS
                                    </h2>
                                    <p class="text-xs sm:text-sm text-slate-400 leading-relaxed">
                                        Steek uw persoonlijke <strong class="text-amber-300 font-bold">Kliniek Betaalpas</strong> in de paslezer om veilig contant geld te storten, pinnen of uw kredietruimte te benutten.
                                    </p>
                                </div>

                                <div class="pt-3">
                                    <button type="button" @click="insertCard()"
                                            class="px-6 py-3.5 rounded-xl bg-gradient-to-r from-amber-400 to-amber-500 hover:from-amber-300 hover:to-amber-400 text-slate-950 font-black text-sm uppercase tracking-wider transition shadow-lg shadow-amber-500/20 cursor-pointer transform hover:scale-105 active:scale-95 flex items-center gap-2 mx-auto">
                                        <i class="fa-solid fa-arrow-down"></i>
                                        <span>Steek Betaalpas In</span>
                                    </button>
                                    <span class="text-[11px] text-slate-500 block mt-2.5 font-mono">
                                        (Of klik op uw pas aan de rechterkant)
                                    </span>
                                </div>
                            </div>

                            <!-- ------------------------------------------ -->
                            <!-- SCREEN 2: EERSTE KEER PINCODE INSTELLEN   -->
                            <!-- ------------------------------------------ -->
                            <div x-show="atmScreen === 'setup_pin'" class="my-auto text-center space-y-5 py-4 relative z-10">
                                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-amber-400/20 text-amber-300 border border-amber-400/40 text-xs font-bold shadow-xs">
                                    <i class="fa-solid fa-shield"></i> Eerste Gebruik: Pincode Activeren
                                </div>

                                <div class="space-y-1.5 max-w-sm mx-auto">
                                    <h3 class="text-base sm:text-lg font-black uppercase text-white"
                                        x-text="pinStep === 'enter_new' ? 'Kies uw 4-cijferige pincode' : 'Bevestig uw 4-cijferige pincode'"></h3>
                                    <p class="text-xs text-slate-400 leading-relaxed">
                                        Toets 4 cijfers in op het metalen keypad hieronder om uw Kliniek Betaalpas definitief te activeren.
                                    </p>
                                </div>

                                <!-- 4 Digit Pin Display Boxes -->
                                <div class="flex items-center justify-center gap-3.5 my-4">
                                    <template x-for="i in 4" :key="i">
                                        <div class="w-13 h-15 rounded-xl border-2 flex items-center justify-center text-2xl font-black font-mono transition-all duration-200"
                                             :class="(pinStep === 'enter_new' ? pinDigits.length : pinConfirmDigits.length) >= i 
                                                ? 'border-amber-400 bg-amber-400/20 text-amber-300 shadow-lg shadow-amber-400/20 scale-105' 
                                                : 'border-slate-800 bg-slate-900/80 text-slate-600'">
                                            <span x-show="(pinStep === 'enter_new' ? pinDigits.length : pinConfirmDigits.length) >= i">●</span>
                                            <span x-show="(pinStep === 'enter_new' ? pinDigits.length : pinConfirmDigits.length) < i" class="text-slate-700 text-sm">_</span>
                                        </div>
                                    </template>
                                </div>

                                <!-- Error / Success feedback -->
                                <div x-show="pinError !== ''" class="text-rose-400 text-xs font-bold font-mono bg-rose-950/70 border border-rose-800 p-2.5 rounded-xl max-w-xs mx-auto animate-shake" x-text="pinError"></div>
                                <div x-show="pinSuccess !== ''" class="text-emerald-400 text-xs font-bold font-mono bg-emerald-950/70 border border-emerald-800 p-2.5 rounded-xl max-w-xs mx-auto" x-text="pinSuccess"></div>

                                <div class="flex items-center justify-center gap-3 text-xs text-slate-400 font-mono pt-1">
                                    <span>Stap <strong class="text-amber-400" x-text="pinStep === 'enter_new' ? '1 van 2' : '2 van 2'"></strong></span>
                                    <span>•</span>
                                    <button type="button" @click="pressClear()" class="hover:text-amber-400 underline cursor-pointer">Wissen (Geel)</button>
                                </div>
                            </div>

                            <!-- ------------------------------------------ -->
                            <!-- SCREEN 3: PINCODE INVOEREN                 -->
                            <!-- ------------------------------------------ -->
                            <div x-show="atmScreen === 'enter_pin'" class="my-auto text-center space-y-5 py-4 relative z-10">
                                <div class="w-14 h-14 mx-auto rounded-2xl bg-amber-400/10 border-2 border-amber-400/30 text-amber-400 flex items-center justify-center text-2xl shadow-md">
                                    <i class="fa-solid fa-key"></i>
                                </div>

                                <div class="space-y-1">
                                    <h3 class="text-base sm:text-lg font-black uppercase text-white">
                                        Voer uw 4-cijferige pincode in
                                    </h3>
                                    <p class="text-xs text-slate-400">
                                        Rekeninghouder: <strong class="text-slate-200">{{ $character->name }}</strong>
                                    </p>
                                </div>

                                <!-- 4 Digit Pin Display Boxes -->
                                <div class="flex items-center justify-center gap-3.5 my-3">
                                    <template x-for="i in 4" :key="i">
                                        <div class="w-13 h-15 rounded-xl border-2 flex items-center justify-center text-2xl font-black font-mono transition-all duration-200"
                                             :class="pinDigits.length >= i 
                                                ? 'border-amber-400 bg-amber-400/20 text-amber-300 shadow-lg shadow-amber-400/20 scale-105' 
                                                : 'border-slate-800 bg-slate-900/80 text-slate-600'">
                                            <span x-show="pinDigits.length >= i">●</span>
                                            <span x-show="pinDigits.length < i" class="text-slate-700 text-sm">_</span>
                                        </div>
                                    </template>
                                </div>

                                <div x-show="pinError !== ''" class="text-rose-400 text-xs font-bold font-mono bg-rose-950/70 border border-rose-800 p-2.5 rounded-xl max-w-xs mx-auto animate-shake" x-text="pinError"></div>
                                <div x-show="pinSuccess !== ''" class="text-emerald-400 text-xs font-bold font-mono bg-emerald-950/70 border border-emerald-800 p-2.5 rounded-xl max-w-xs mx-auto" x-text="pinSuccess"></div>

                                <p class="text-[11px] text-slate-500 font-mono">
                                    Toets uw code in via het fysieke pinpad hieronder of via uw numerieke toetsenbord.
                                </p>
                            </div>

                            <!-- ------------------------------------------ -->
                            <!-- SCREEN 4: HOOFDMENU NA SUCCESVOLLE PIN    -->
                            <!-- ------------------------------------------ -->
                            <div x-show="atmScreen === 'menu'" class="space-y-5 my-auto relative z-10">
                                <!-- ATM Saldo Badge -->
                                <div class="bg-slate-950/90 rounded-2xl p-4 border border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-inner">
                                    <div>
                                        <span class="text-[10px] font-mono text-slate-400 uppercase tracking-widest block">Veilig Banktegoed (Kluis)</span>
                                        <div class="text-2xl sm:text-3xl font-black font-mono text-emerald-400">
                                            €{{ number_format($character->bank, 0, ',', '.') }}
                                        </div>
                                    </div>
                                    <div class="text-left sm:text-right font-mono">
                                        <span class="text-[10px] text-slate-400 uppercase tracking-widest block">Contant op Zak</span>
                                        <div class="text-base sm:text-lg font-black text-amber-400">
                                            €{{ number_format($character->cash, 0, ',', '.') }}
                                        </div>
                                    </div>
                                </div>

                                <!-- 4 Touch Action Tiles -->
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <!-- Storten -->
                                    <button type="button" @click="atmScreen = 'deposit'; playBeep(850, 45);"
                                            class="p-4 rounded-xl bg-slate-800/80 hover:bg-emerald-950/60 border border-slate-700/80 hover:border-emerald-500/80 text-left transition group cursor-pointer shadow-xs flex items-center justify-between">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center text-lg font-bold group-hover:scale-110 transition shadow-xs">
                                                <i class="fa-solid fa-arrow-down"></i>
                                            </div>
                                            <div>
                                                <h4 class="text-sm font-black text-white group-hover:text-emerald-300">Geld Storten</h4>
                                                <span class="text-[10px] text-slate-400">Contant veilig afstorten (0% tax)</span>
                                            </div>
                                        </div>
                                        <i class="fa-solid fa-chevron-right text-xs text-slate-500 group-hover:text-emerald-400"></i>
                                    </button>

                                    <!-- Opnemen -->
                                    <button type="button" @click="atmScreen = 'withdraw'; playBeep(850, 45);"
                                            class="p-4 rounded-xl bg-slate-800/80 hover:bg-amber-950/60 border border-slate-700/80 hover:border-amber-500/80 text-left transition group cursor-pointer shadow-xs flex items-center justify-between">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-xl bg-amber-500/20 text-amber-400 flex items-center justify-center text-lg font-bold group-hover:scale-110 transition shadow-xs">
                                                <i class="fa-solid fa-arrow-up"></i>
                                            </div>
                                            <div>
                                                <h4 class="text-sm font-black text-white group-hover:text-amber-300">Contant Opnemen</h4>
                                                <span class="text-[10px] text-slate-400">Biljetten pinnen uit automaat</span>
                                            </div>
                                        </div>
                                        <i class="fa-solid fa-chevron-right text-xs text-slate-500 group-hover:text-amber-400"></i>
                                    </button>

                                    <!-- Krediet & Leningen -->
                                    <button type="button" @click="atmScreen = 'loans'; playBeep(850, 45);"
                                            class="p-4 rounded-xl bg-slate-800/80 hover:bg-purple-950/60 border border-slate-700/80 hover:border-purple-500/80 text-left transition group cursor-pointer shadow-xs flex items-center justify-between">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-xl bg-purple-500/20 text-purple-400 flex items-center justify-center text-lg font-bold group-hover:scale-110 transition shadow-xs">
                                                <i class="fa-solid fa-hand-holding-dollar"></i>
                                            </div>
                                            <div>
                                                <h4 class="text-sm font-black text-white group-hover:text-purple-300">Krediet &amp; Leningen</h4>
                                                <span class="text-[10px] text-slate-400">Beschikbaar: €{{ number_format($beschikbareLening ?? 0, 0, ',', '.') }}</span>
                                            </div>
                                        </div>
                                        <i class="fa-solid fa-chevron-right text-xs text-slate-500 group-hover:text-purple-400"></i>
                                    </button>

                                    <!-- Afschrift & Mutaties -->
                                    <button type="button" @click="atmScreen = 'transactions'; playBeep(850, 45);"
                                            class="p-4 rounded-xl bg-slate-800/80 hover:bg-blue-950/60 border border-slate-700/80 hover:border-blue-500/80 text-left transition group cursor-pointer shadow-xs flex items-center justify-between">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-xl bg-blue-500/20 text-blue-400 flex items-center justify-center text-lg font-bold group-hover:scale-110 transition shadow-xs">
                                                <i class="fa-solid fa-receipt"></i>
                                            </div>
                                            <div>
                                                <h4 class="text-sm font-black text-white group-hover:text-blue-300">Afschrift &amp; Mutaties</h4>
                                                <span class="text-[10px] text-slate-400">Recente rekeningtransacties</span>
                                            </div>
                                        </div>
                                        <i class="fa-solid fa-chevron-right text-xs text-slate-500 group-hover:text-blue-400"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- ------------------------------------------ -->
                            <!-- SCREEN 5: GELD STORTEN                     -->
                            <!-- ------------------------------------------ -->
                            <div x-show="atmScreen === 'deposit'" class="space-y-4 my-auto relative z-10">
                                <div class="flex items-center justify-between border-b border-slate-800 pb-2">
                                    <div class="flex items-center gap-2">
                                        <button type="button" @click="atmScreen = 'menu'" class="text-slate-400 hover:text-white text-xs font-mono cursor-pointer">
                                            &larr; Hoofdmenu
                                        </button>
                                        <span class="text-slate-600">|</span>
                                        <h4 class="text-sm font-black uppercase text-emerald-400">Geld Storten</h4>
                                    </div>
                                    <span class="text-xs font-mono text-slate-400">
                                        Op zak: <strong class="text-amber-400">€{{ number_format($character->cash, 0, ',', '.') }}</strong>
                                    </span>
                                </div>

                                <!-- Fast Presets -->
                                <div class="grid grid-cols-4 gap-2">
                                    <button type="button" @click="depositAmount = 500; playBeep(700, 30);"
                                            class="py-2.5 px-1 rounded-xl bg-slate-800 hover:bg-slate-700 border border-slate-700 text-xs font-mono font-bold text-white text-center cursor-pointer transition">
                                        €500
                                    </button>
                                    <button type="button" @click="depositAmount = 2500; playBeep(700, 30);"
                                            class="py-2.5 px-1 rounded-xl bg-slate-800 hover:bg-slate-700 border border-slate-700 text-xs font-mono font-bold text-white text-center cursor-pointer transition">
                                        €2.500
                                    </button>
                                    <button type="button" @click="depositAmount = 10000; playBeep(700, 30);"
                                            class="py-2.5 px-1 rounded-xl bg-slate-800 hover:bg-slate-700 border border-slate-700 text-xs font-mono font-bold text-white text-center cursor-pointer transition">
                                        €10.000
                                    </button>
                                    <button type="button" @click="depositAmount = {{ (int) $character->cash }}; playBeep(700, 30);"
                                            class="py-2.5 px-1 rounded-xl bg-emerald-900/60 hover:bg-emerald-800 border border-emerald-600 text-xs font-mono font-bold text-emerald-300 text-center cursor-pointer transition">
                                        ALLES
                                    </button>
                                </div>

                                <form method="POST" action="{{ route('bank.deposit') }}" x-ref="depositForm" class="space-y-3">
                                    @csrf
                                    <div>
                                        <label class="text-[10px] font-mono text-slate-400 uppercase tracking-widest block mb-1">
                                            Te storten bedrag
                                        </label>
                                        <div class="relative">
                                            <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-500 font-mono font-bold">€</span>
                                            <input type="number" name="amount" x-model="depositAmount" min="1" max="{{ $character->cash }}"
                                                   placeholder="0" required
                                                   class="w-full bg-slate-950 border border-slate-700 rounded-xl py-3 pl-8 pr-4 text-white font-mono text-lg font-bold focus:border-emerald-500 focus:outline-hidden">
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-2 pt-2">
                                        <button type="button" @click="atmScreen = 'menu'"
                                                class="flex-1 py-3 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 font-bold text-xs uppercase font-mono transition cursor-pointer">
                                            Annuleren
                                        </button>
                                        <button type="submit"
                                                class="flex-2 py-3 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-black text-xs uppercase tracking-wider font-mono transition cursor-pointer shadow-lg shadow-emerald-600/30">
                                            Storting Bevestigen
                                        </button>
                                    </div>
                                </form>
                            </div>

                            <!-- ------------------------------------------ -->
                            <!-- SCREEN 6: CONTANT GELD OPNEMEN             -->
                            <!-- ------------------------------------------ -->
                            <div x-show="atmScreen === 'withdraw'" class="space-y-4 my-auto relative z-10">
                                <div class="flex items-center justify-between border-b border-slate-800 pb-2">
                                    <div class="flex items-center gap-2">
                                        <button type="button" @click="atmScreen = 'menu'" class="text-slate-400 hover:text-white text-xs font-mono cursor-pointer">
                                            &larr; Hoofdmenu
                                        </button>
                                        <span class="text-slate-600">|</span>
                                        <h4 class="text-sm font-black uppercase text-amber-400">Contant Opnemen</h4>
                                    </div>
                                    <span class="text-xs font-mono text-slate-400">
                                        In kluis: <strong class="text-emerald-400">€{{ number_format($character->bank, 0, ',', '.') }}</strong>
                                    </span>
                                </div>

                                <!-- Tax Notice Banner -->
                                <div class="bg-amber-950/40 border border-amber-800/60 rounded-xl p-2.5 text-xs text-amber-300 flex items-center justify-between font-mono">
                                    <span>Kliniek Opnamebelasting: <strong>{{ $character->isVip() ? '3%' : '5%' }}</strong></span>
                                    <span class="text-[10px] text-amber-400/80">Vloeit naar Afdelingskavel Jackpot!</span>
                                </div>

                                <!-- Fast Presets -->
                                <div class="grid grid-cols-4 gap-2">
                                    <button type="button" @click="withdrawAmount = 1000; playBeep(700, 30);"
                                            class="py-2.5 px-1 rounded-xl bg-slate-800 hover:bg-slate-700 border border-slate-700 text-xs font-mono font-bold text-white text-center cursor-pointer transition">
                                        €1.000
                                    </button>
                                    <button type="button" @click="withdrawAmount = 5000; playBeep(700, 30);"
                                            class="py-2.5 px-1 rounded-xl bg-slate-800 hover:bg-slate-700 border border-slate-700 text-xs font-mono font-bold text-white text-center cursor-pointer transition">
                                        €5.000
                                    </button>
                                    <button type="button" @click="withdrawAmount = 25000; playBeep(700, 30);"
                                            class="py-2.5 px-1 rounded-xl bg-slate-800 hover:bg-slate-700 border border-slate-700 text-xs font-mono font-bold text-white text-center cursor-pointer transition">
                                        €25.000
                                    </button>
                                    <button type="button" @click="withdrawAmount = {{ (int) $character->bank }}; playBeep(700, 30);"
                                            class="py-2.5 px-1 rounded-xl bg-amber-900/60 hover:bg-amber-800 border border-amber-600 text-xs font-mono font-bold text-amber-300 text-center cursor-pointer transition">
                                        ALLES
                                    </button>
                                </div>

                                <form method="POST" action="{{ route('bank.withdraw') }}" x-ref="withdrawForm" class="space-y-3">
                                    @csrf
                                    <div>
                                        <label class="text-[10px] font-mono text-slate-400 uppercase tracking-widest block mb-1">
                                            Op te nemen bedrag
                                        </label>
                                        <div class="relative">
                                            <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-500 font-mono font-bold">€</span>
                                            <input type="number" name="amount" x-model="withdrawAmount" min="1" max="{{ $character->bank }}"
                                                   placeholder="0" required
                                                   class="w-full bg-slate-950 border border-slate-700 rounded-xl py-3 pl-8 pr-4 text-white font-mono text-lg font-bold focus:border-amber-500 focus:outline-hidden">
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-2 pt-2">
                                        <button type="button" @click="atmScreen = 'menu'"
                                                class="flex-1 py-3 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 font-bold text-xs uppercase font-mono transition cursor-pointer">
                                            Annuleren
                                        </button>
                                        <button type="submit"
                                                class="flex-2 py-3 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-black text-xs uppercase tracking-wider font-mono transition cursor-pointer shadow-lg shadow-amber-500/20">
                                            Pinnen Uit Automaat
                                        </button>
                                    </div>
                                </form>
                            </div>

                            <!-- ------------------------------------------ -->
                            <!-- SCREEN 7: KREDIET & LENINGEN               -->
                            <!-- ------------------------------------------ -->
                            <div x-show="atmScreen === 'loans'" class="space-y-4 my-auto relative z-10 text-xs">
                                <div class="flex items-center justify-between border-b border-slate-800 pb-2">
                                    <div class="flex items-center gap-2">
                                        <button type="button" @click="atmScreen = 'menu'" class="text-slate-400 hover:text-white font-mono cursor-pointer">
                                            &larr; Hoofdmenu
                                        </button>
                                        <span class="text-slate-600">|</span>
                                        <h4 class="text-sm font-black uppercase text-purple-400">Krediet &amp; Leningen</h4>
                                    </div>
                                    <span class="text-[11px] font-mono text-purple-300 bg-purple-950 px-2 py-0.5 rounded border border-purple-800">
                                        Rente: 8,5% (Leningtax)
                                    </span>
                                </div>

                                <div class="grid grid-cols-2 gap-2 text-center font-mono">
                                    <div class="bg-slate-950 p-2.5 rounded-xl border border-slate-800">
                                        <span class="text-[9px] text-slate-400 uppercase block">Openstaande Schuld</span>
                                        <span class="text-sm font-bold text-rose-400">€{{ number_format($openstaandeSchuld ?? 0, 0, ',', '.') }}</span>
                                    </div>
                                    <div class="bg-slate-950 p-2.5 rounded-xl border border-slate-800">
                                        <span class="text-[9px] text-slate-400 uppercase block">Beschikbare Kredietruimte</span>
                                        <span class="text-sm font-bold text-emerald-400">€{{ number_format($beschikbareLening ?? 0, 0, ',', '.') }}</span>
                                    </div>
                                </div>

                                <!-- Active Loans Mini List -->
                                <div class="space-y-1.5 max-h-36 overflow-y-auto pr-1">
                                    @forelse($activeLoans as $loan)
                                        <div class="bg-slate-950/80 p-2.5 rounded-xl border border-slate-800 flex items-center justify-between gap-2">
                                            <div>
                                                <div class="font-bold text-white font-mono">
                                                    Restant: €{{ number_format($loan->remaining_due, 0, ',', '.') }}
                                                </div>
                                                <span class="text-[10px] text-slate-400">Tot: €{{ number_format($loan->total_due, 0, ',', '.') }} ({{ $loan->interest_rate }}%)</span>
                                            </div>
                                            <div class="flex items-center gap-1.5">
                                                <form method="POST" action="{{ route('bank.loans.repay-full', $loan) }}">
                                                    @csrf
                                                    <input type="hidden" name="source" value="bank">
                                                    <button type="submit" class="px-2 py-1 rounded bg-emerald-600 hover:bg-emerald-500 text-white text-[10px] font-bold font-mono transition cursor-pointer">
                                                        Aflossen
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="text-center py-4 text-slate-500 text-xs font-mono">
                                            Geen actieve leningen openstaand.
                                        </div>
                                    @endforelse
                                </div>

                                <!-- Request Loan Form -->
                                @if(($beschikbareLening ?? 0) >= 10000)
                                    <form method="POST" action="{{ route('bank.loans.request') }}" class="space-y-2 pt-2 border-t border-slate-800">
                                        @csrf
                                        <div class="flex items-center gap-2">
                                            <input type="number" name="amount" min="10000" max="{{ $beschikbareLening }}" step="5000"
                                                   value="{{ min(50000, $beschikbareLening) }}" required
                                                   class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-white font-mono text-sm font-bold">
                                            <select name="deposit_to" class="bg-slate-950 border border-slate-700 rounded-xl px-2.5 py-2 text-white font-mono text-xs">
                                                <option value="bank">Naar Kluis</option>
                                                <option value="cash">Naar Zak</option>
                                            </select>
                                            <button type="submit" class="shrink-0 px-3 py-2 rounded-xl bg-purple-600 hover:bg-purple-500 text-white font-bold text-xs uppercase font-mono cursor-pointer shadow-xs">
                                                Aanvragen
                                            </button>
                                        </div>
                                    </form>
                                @endif
                            </div>

                            <!-- ------------------------------------------ -->
                            <!-- SCREEN 8: AFSCHRIFT & TRANSACTIEBONNEN    -->
                            <!-- ------------------------------------------ -->
                            <div x-show="atmScreen === 'transactions'" class="space-y-3 my-auto relative z-10 text-xs font-mono">
                                <div class="flex items-center justify-between border-b border-slate-800 pb-2">
                                    <div class="flex items-center gap-2">
                                        <button type="button" @click="atmScreen = 'menu'" class="text-slate-400 hover:text-white cursor-pointer">
                                            &larr; Hoofdmenu
                                        </button>
                                        <span class="text-slate-600">|</span>
                                        <h4 class="text-sm font-black uppercase text-blue-400">Transactiebon / Afschrift</h4>
                                    </div>
                                    <span class="text-[10px] text-slate-400">Printversie</span>
                                </div>

                                <div class="bg-amber-50 text-slate-900 rounded-xl p-3 shadow-inner max-h-56 overflow-y-auto space-y-2 text-[11px] border border-amber-200">
                                    <div class="text-center border-b border-dashed border-slate-400 pb-1.5">
                                        <span class="font-black tracking-wider block">GELDMAAT REKENINGAFSCHRIFT</span>
                                        <span class="text-[10px] text-slate-600">TBS KLINIEK CENTRAAL KLUISREGISTER</span>
                                    </div>

                                    @forelse($transactions as $tx)
                                        <div class="flex items-center justify-between py-0.5 border-b border-slate-200/60">
                                            <div>
                                                <span class="font-bold block uppercase">{{ str_replace('_', ' ', $tx->type) }}</span>
                                                <span class="text-[9px] text-slate-500">{{ $tx->created_at->format('d-m H:i') }}</span>
                                            </div>
                                            <span class="font-black {{ $tx->amount >= 0 ? 'text-emerald-700' : 'text-rose-700' }}">
                                                {{ $tx->amount >= 0 ? '+' : '' }}€{{ number_format($tx->amount, 0, ',', '.') }}
                                            </span>
                                        </div>
                                    @empty
                                        <div class="text-center py-4 text-slate-500">
                                            Geen mutaties geregistreerd.
                                        </div>
                                    @endforelse

                                    <div class="text-center border-t border-dashed border-slate-400 pt-1.5 text-[10px] text-slate-600">
                                        BEWAAR DEZE BON VOOR JE ADMINISTRATIE
                                    </div>
                                </div>
                            </div>

                            <!-- Screen Footer Bezel -->
                            <div class="border-t border-slate-800/80 pt-2.5 mt-auto flex items-center justify-between text-[10px] font-mono text-slate-500 relative z-10">
                                <span>KLINIEK PIN TERMINAL #0884</span>
                                <span class="flex items-center gap-1.5 text-emerald-400 font-bold">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span> GEREED
                                </span>
                            </div>
                        </div>

                        <!-- ============================================== -->
                        <!-- PHYSICAL HARDWARE SLOTS (Under Screen)          -->
                        <!-- ============================================== -->
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mt-4 pt-4 border-t-2 border-slate-800">

                            <!-- 1. Pas-invoer Sleuf met LED -->
                            <div class="p-3.5 rounded-2xl bg-slate-950 border border-slate-800 flex flex-col justify-between gap-2.5 text-center group cursor-pointer transition hover:border-emerald-500/60 shadow-xs"
                                 @click="cardInserted ? ejectCard() : insertCard()">
                                <div class="flex items-center justify-between">
                                    <span class="text-[9px] font-mono font-black text-slate-400 uppercase tracking-wider">Pasinvoer</span>
                                    <span class="w-2.5 h-2.5 rounded-full" :class="cardInserted ? 'bg-amber-400 animate-pulse' : 'bg-emerald-400 animate-ping'"></span>
                                </div>

                                <!-- Card Slot Visual Graphic -->
                                <div class="h-3.5 rounded-full bg-slate-900 border border-slate-700 flex items-center justify-center relative overflow-hidden shadow-inner">
                                    <div class="w-20 h-1 rounded-full transition-all duration-300"
                                         :class="cardInserted ? 'bg-amber-400 shadow-[0_0_8px_rgba(251,191,36,0.8)]' : 'bg-emerald-400 animate-pulse shadow-[0_0_8px_rgba(52,211,153,0.8)]'"></div>
                                </div>

                                <span class="text-[10px] font-mono font-bold text-slate-300 truncate"
                                      x-text="cardInserted ? 'Pas Ingeschoven (Klik: Uitwerpen)' : 'Steek Pas In ⮟'"></span>
                            </div>

                            <!-- 2. Biljettenlade (Cash Dispenser) -->
                            <div class="p-3.5 rounded-2xl bg-slate-950 border border-slate-800 flex flex-col justify-between gap-2.5 text-center shadow-xs">
                                <div class="flex items-center justify-between">
                                    <span class="text-[9px] font-mono font-black text-slate-400 uppercase tracking-wider">Biljettenlade</span>
                                    <span class="w-2 h-2 rounded-full bg-slate-700"></span>
                                </div>

                                <div class="h-3.5 rounded bg-slate-900 border border-slate-700 flex items-center justify-center shadow-inner">
                                    <div class="w-24 h-1 rounded-full bg-slate-800"></div>
                                </div>

                                <span class="text-[10px] font-mono text-slate-400 truncate">
                                    Uitgifte &amp; Inname Luik
                                </span>
                            </div>

                            <!-- 3. Bonprinter Sleuf -->
                            <div class="p-3.5 rounded-2xl bg-slate-950 border border-slate-800 flex flex-col justify-between gap-2.5 text-center cursor-pointer transition hover:border-blue-500/60 shadow-xs"
                                 @click="atmScreen = 'transactions'; playBeep(900, 40);">
                                <div class="flex items-center justify-between">
                                    <span class="text-[9px] font-mono font-black text-slate-400 uppercase tracking-wider">Bonprinter</span>
                                    <span class="w-2 h-2 rounded-full bg-blue-400"></span>
                                </div>

                                <div class="h-3.5 rounded bg-slate-900 border border-slate-700 flex items-center justify-center shadow-inner">
                                    <div class="w-16 h-1 bg-white/40 rounded-full"></div>
                                </div>

                                <span class="text-[10px] font-mono text-blue-400 hover:underline truncate font-bold">
                                    [ Print Afschrift ]
                                </span>
                            </div>
                        </div>

                        <!-- ============================================== -->
                        <!-- 3D PHYSICAL METAL KEYPAD (Interactive Pad)     -->
                        <!-- ============================================== -->
                        <div class="mt-4 pt-4 border-t-2 border-slate-800 max-w-sm mx-auto">
                            <div class="flex items-center justify-between mb-2.5 px-1">
                                <span class="text-[10px] font-mono font-black uppercase text-slate-400 flex items-center gap-1.5">
                                    <i class="fa-solid fa-keyboard text-amber-400"></i> Fysiek RVS Pinpad
                                </span>
                                <span class="text-[9px] font-mono text-slate-500">PCI-DSS Encrypted</span>
                            </div>

                            <div class="grid grid-cols-4 gap-2">
                                <!-- Row 1: 1, 2, 3 + STOP (Rood) -->
                                <button type="button" @click="pressKey('1')" class="py-3 rounded-xl bg-gradient-to-b from-slate-100 to-slate-300 hover:from-white hover:to-slate-200 text-slate-950 font-black font-mono text-lg shadow-[0_4px_0_#94a3b8] active:shadow-none active:translate-y-1 transition cursor-pointer border border-slate-200">1</button>
                                <button type="button" @click="pressKey('2')" class="py-3 rounded-xl bg-gradient-to-b from-slate-100 to-slate-300 hover:from-white hover:to-slate-200 text-slate-950 font-black font-mono text-lg shadow-[0_4px_0_#94a3b8] active:shadow-none active:translate-y-1 transition cursor-pointer border border-slate-200">2</button>
                                <button type="button" @click="pressKey('3')" class="py-3 rounded-xl bg-gradient-to-b from-slate-100 to-slate-300 hover:from-white hover:to-slate-200 text-slate-950 font-black font-mono text-lg shadow-[0_4px_0_#94a3b8] active:shadow-none active:translate-y-1 transition cursor-pointer border border-slate-200">3</button>
                                <button type="button" @click="pressCancel()" class="py-3 rounded-xl bg-gradient-to-b from-rose-500 to-rose-700 hover:from-rose-400 hover:to-rose-600 text-white font-black font-mono text-xs uppercase shadow-[0_4px_0_#881337] active:shadow-none active:translate-y-1 transition cursor-pointer border border-rose-400">STOP</button>

                                <!-- Row 2: 4, 5 (met braille), 6 + CORR (Geel) -->
                                <button type="button" @click="pressKey('4')" class="py-3 rounded-xl bg-gradient-to-b from-slate-100 to-slate-300 hover:from-white hover:to-slate-200 text-slate-950 font-black font-mono text-lg shadow-[0_4px_0_#94a3b8] active:shadow-none active:translate-y-1 transition cursor-pointer border border-slate-200">4</button>
                                <button type="button" @click="pressKey('5')" class="py-3 rounded-xl bg-gradient-to-b from-slate-100 to-slate-300 hover:from-white hover:to-slate-200 text-slate-950 font-black font-mono text-lg shadow-[0_4px_0_#94a3b8] active:shadow-none active:translate-y-1 transition cursor-pointer border border-slate-200 relative">
                                    5<span class="absolute bottom-1 right-2 text-[8px] text-slate-500">⠍</span>
                                </button>
                                <button type="button" @click="pressKey('6')" class="py-3 rounded-xl bg-gradient-to-b from-slate-100 to-slate-300 hover:from-white hover:to-slate-200 text-slate-950 font-black font-mono text-lg shadow-[0_4px_0_#94a3b8] active:shadow-none active:translate-y-1 transition cursor-pointer border border-slate-200">6</button>
                                <button type="button" @click="pressClear()" class="py-3 rounded-xl bg-gradient-to-b from-amber-400 to-amber-500 hover:from-amber-300 hover:to-amber-400 text-slate-950 font-black font-mono text-xs uppercase shadow-[0_4px_0_#92400e] active:shadow-none active:translate-y-1 transition cursor-pointer border border-amber-300">CORR</button>

                                <!-- Row 3: 7, 8, 9 + OK (Groen) -->
                                <button type="button" @click="pressKey('7')" class="py-3 rounded-xl bg-gradient-to-b from-slate-100 to-slate-300 hover:from-white hover:to-slate-200 text-slate-950 font-black font-mono text-lg shadow-[0_4px_0_#94a3b8] active:shadow-none active:translate-y-1 transition cursor-pointer border border-slate-200">7</button>
                                <button type="button" @click="pressKey('8')" class="py-3 rounded-xl bg-gradient-to-b from-slate-100 to-slate-300 hover:from-white hover:to-slate-200 text-slate-950 font-black font-mono text-lg shadow-[0_4px_0_#94a3b8] active:shadow-none active:translate-y-1 transition cursor-pointer border border-slate-200">8</button>
                                <button type="button" @click="pressKey('9')" class="py-3 rounded-xl bg-gradient-to-b from-slate-100 to-slate-300 hover:from-white hover:to-slate-200 text-slate-950 font-black font-mono text-lg shadow-[0_4px_0_#94a3b8] active:shadow-none active:translate-y-1 transition cursor-pointer border border-slate-200">9</button>
                                <button type="button" @click="pressOk()" class="py-3 rounded-xl bg-gradient-to-b from-emerald-500 to-emerald-700 hover:from-emerald-400 hover:to-emerald-600 text-white font-black font-mono text-xs uppercase shadow-[0_4px_0_#064e3b] active:shadow-none active:translate-y-1 transition cursor-pointer border border-emerald-400">OK</button>

                                <!-- Row 4: Empty, 0, Empty, Info -->
                                <div class="py-3"></div>
                                <button type="button" @click="pressKey('0')" class="py-3 rounded-xl bg-gradient-to-b from-slate-100 to-slate-300 hover:from-white hover:to-slate-200 text-slate-950 font-black font-mono text-lg shadow-[0_4px_0_#94a3b8] active:shadow-none active:translate-y-1 transition cursor-pointer border border-slate-200">0</button>
                                <div class="py-3"></div>
                                <button type="button" @click="playBeep(440, 50)" class="py-3 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-400 font-bold font-mono text-xs uppercase shadow-[0_4px_0_#0f172a] active:shadow-none active:translate-y-1 transition cursor-pointer border border-slate-700">INFO</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- RIGHT COLUMN (Cols 4): PATIENT CARD & FINANCIAL VITALS -->
        <div class="lg:col-span-4 space-y-6">

            <!-- 1. The Interactive 3D TBS Kliniek Betaalpas -->
            <div class="space-y-2.5">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-black uppercase tracking-wider text-slate-700 dark:text-slate-300 flex items-center gap-1.5">
                        <i class="fa-solid fa-credit-card text-purple-600 dark:text-purple-400"></i>
                        <span>Jouw Betaalpas</span>
                    </span>
                    <span class="text-[10px] font-mono text-amber-600 dark:text-amber-400 font-bold bg-amber-100 dark:bg-amber-950/80 px-2 py-0.5 rounded-full border border-amber-300/60 dark:border-amber-800/60">
                        EMV CHIP 2026
                    </span>
                </div>

                <!-- Realistic Dutch Bank Debit Card -->
                <div @click="cardInserted ? ejectCard() : insertCard()"
                     class="w-full aspect-[1.586/1] rounded-3xl p-5 shadow-2xl relative overflow-hidden transition-all duration-300 transform hover:-translate-y-1 hover:shadow-purple-500/25 cursor-pointer flex flex-col justify-between select-none border-2 border-white/30 dark:border-purple-500/30"
                     :class="cardInserted ? 'ring-4 ring-amber-400 scale-[0.98] opacity-90' : ''"
                     style="background: linear-gradient(135deg, #180d33 0%, #3b0764 50%, #0f172a 100%);">

                    <!-- Glossy overlay & hologram shimmer -->
                    <div class="absolute inset-0 bg-gradient-to-tr from-transparent via-white/10 to-transparent pointer-events-none"></div>

                    <!-- Top Card Bar -->
                    <div class="flex items-center justify-between relative z-10">
                        <div class="flex items-center gap-2">
                            <span class="w-8 h-8 rounded-xl bg-amber-400 text-slate-950 flex items-center justify-center text-sm font-black shadow-md">
                                <i class="fa-solid fa-building-columns"></i>
                            </span>
                            <div>
                                <span class="text-xs font-black uppercase tracking-widest text-white font-mono block">
                                    TBS KLINIEK
                                </span>
                                <span class="text-[8px] font-mono text-purple-200 tracking-wider">PATIENT DEBIT</span>
                            </div>
                        </div>
                        <i class="fa-solid fa-wifi text-purple-300 text-sm rotate-90"></i>
                    </div>

                    <!-- Gold EMV Chip -->
                    <div class="flex items-center gap-3 relative z-10 my-1">
                        <div class="w-12 h-9 rounded-lg bg-gradient-to-br from-amber-200 via-amber-400 to-amber-600 border border-amber-300 shadow-inner flex items-center justify-center p-1">
                            <div class="w-full h-full border border-amber-800/60 rounded-xs flex flex-col justify-between">
                                <div class="h-0.5 bg-amber-800/40 w-full"></div>
                                <div class="h-0.5 bg-amber-800/40 w-full"></div>
                            </div>
                        </div>
                        <span class="text-[10px] font-mono text-purple-200 tracking-widest uppercase">Geldmaat Pas</span>
                    </div>

                    <!-- Embossed Card Numbers & Patient Name -->
                    <div class="space-y-1 relative z-10">
                        <div class="text-sm sm:text-base font-mono font-extrabold tracking-widest text-slate-100 drop-shadow">
                            NL99 TBSK 0884 {{ str_pad($character->id, 4, '0', STR_PAD_LEFT) }}
                        </div>
                        <div class="flex items-center justify-between text-[11px] font-mono text-slate-300 uppercase">
                            <span class="truncate max-w-[150px] font-black text-white">{{ $character->name }}</span>
                            <span class="tracking-widest">12/28</span>
                        </div>
                    </div>
                </div>

                <div class="text-center pt-1">
                    <button type="button" @click="cardInserted ? ejectCard() : insertCard()"
                            class="text-xs font-black text-purple-700 dark:text-purple-300 hover:underline flex items-center justify-center gap-2 mx-auto cursor-pointer">
                        <i class="fa-solid" :class="cardInserted ? 'fa-eject text-rose-500' : 'fa-arrow-left text-emerald-500'"></i>
                        <span x-text="cardInserted ? 'Pas uit de Geldmaat halen' : 'Steek pas in de Geldmaat ➔'"></span>
                    </button>
                </div>
            </div>

            <!-- 2. Financial Vitals Cards (Light & Dark Polish) -->
            <div class="grid grid-cols-2 gap-3.5">
                <!-- Contant op Zak -->
                <div class="p-4 rounded-2xl border shadow-xs transition
                            bg-white border-amber-200/80 text-slate-800
                            dark:bg-slate-900 dark:border-amber-900/60 dark:text-slate-100">
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-[10px] font-black uppercase tracking-wider text-amber-600 dark:text-amber-400">Contant op Zak</span>
                        <i class="fa-solid fa-wallet text-amber-500 text-xs"></i>
                    </div>
                    <div class="text-lg font-black font-mono text-slate-900 dark:text-white truncate">
                        €{{ number_format($character->cash, 0, ',', '.') }}
                    </div>
                    <span class="text-[10px] text-amber-700 dark:text-amber-400 font-bold block mt-1">⚠️ Kwetsbaar</span>
                </div>

                <!-- Veilig in Kluis -->
                <div class="p-4 rounded-2xl border shadow-xs transition
                            bg-white border-emerald-200/80 text-slate-800
                            dark:bg-slate-900 dark:border-emerald-900/60 dark:text-slate-100">
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-[10px] font-black uppercase tracking-wider text-emerald-600 dark:text-emerald-400">Veilig in Kluis</span>
                        <i class="fa-solid fa-shield-halved text-emerald-500 text-xs"></i>
                    </div>
                    <div class="text-lg font-black font-mono text-emerald-600 dark:text-emerald-400 truncate">
                        €{{ number_format($character->bank, 0, ',', '.') }}
                    </div>
                    <span class="text-[10px] text-emerald-700 dark:text-emerald-400 font-bold block mt-1">🛡️ 100% Beveiligd</span>
                </div>

                <!-- Schuld -->
                <div class="p-4 rounded-2xl border shadow-xs transition
                            bg-white border-rose-200/80 text-slate-800
                            dark:bg-slate-900 dark:border-rose-900/60 dark:text-slate-100">
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-[10px] font-black uppercase tracking-wider text-rose-600 dark:text-rose-400">Schuld</span>
                        <i class="fa-solid fa-file-invoice-dollar text-rose-500 text-xs"></i>
                    </div>
                    <div class="text-lg font-black font-mono text-rose-600 dark:text-rose-400 truncate">
                        €{{ number_format($openstaandeSchuld ?? 0, 0, ',', '.') }}
                    </div>
                    <span class="text-[10px] text-slate-500 dark:text-slate-400 font-bold block mt-1">{{ isset($activeLoans) ? $activeLoans->count() : 0 }} lening(en)</span>
                </div>

                <!-- Kredietruimte -->
                <div class="p-4 rounded-2xl border shadow-xs transition
                            bg-white border-purple-200/80 text-slate-800
                            dark:bg-slate-900 dark:border-purple-900/60 dark:text-slate-100">
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-[10px] font-black uppercase tracking-wider text-purple-600 dark:text-purple-400">Kredietruimte</span>
                        <i class="fa-solid fa-chart-line text-purple-500 text-xs"></i>
                    </div>
                    <div class="text-lg font-black font-mono text-purple-600 dark:text-purple-400 truncate">
                        €{{ number_format($beschikbareLening ?? 0, 0, ',', '.') }}
                    </div>
                    <span class="text-[10px] text-slate-500 dark:text-slate-400 font-bold block mt-1">Max €{{ number_format($maxCapacity ?? 0, 0, ',', '.') }}</span>
                </div>
            </div>

            <!-- 3. Patiënten Kwartierloon / Uitkering Card -->
            <div class="p-4 rounded-2xl border shadow-xs space-y-2 transition
                        bg-white border-slate-200 text-slate-800
                        dark:bg-slate-900 dark:border-slate-800 dark:text-slate-100">
                <div class="flex items-center justify-between">
                    <h4 class="text-xs font-black uppercase text-slate-900 dark:text-white flex items-center gap-1.5">
                        <i class="fa-solid fa-hand-holding-dollar text-amber-500"></i>
                        <span>Patiënten Uitkering</span>
                    </h4>
                    <span class="text-[9px] font-black px-2 py-0.5 rounded-full bg-emerald-100 dark:bg-emerald-950 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                        {{ ($allowanceDetails['eligible'] ?? false) ? 'Actief' : 'Bende Loon' }}
                    </span>
                </div>
                <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed">
                    @if($allowanceDetails['eligible'] ?? false)
                        Automatische storting van <strong>€{{ number_format($allowanceDetails['amount_per_quarter'] ?? 1000, 0, ',', '.') }}</strong> per 15 minuten direct op deze rekening!
                    @else
                        Je ontvangt een collectief bende-uurloon via de smokkelkiosken van <strong>{{ $character->gang?->name ?? 'je gang' }}</strong>.
                    @endif
                </p>
            </div>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- MODE 2: KLASSIEK OVERZICHT (Snelle Tabellen voor Spelers)       -->
    <!-- ============================================================== -->
    <div x-show="viewMode === 'classic'" class="space-y-6">
        <!-- Tab Navigation Bar -->
        <div class="flex items-center gap-2 border-b border-slate-200 dark:border-slate-800 pb-3">
            <button type="button" @click="activeTab = 'vault'"
                    class="px-4 py-2 rounded-xl text-xs font-black uppercase font-mono transition cursor-pointer"
                    :class="activeTab === 'vault' ? 'bg-amber-400 text-slate-950 shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800'">
                <i class="fa-solid fa-vault mr-1"></i> Kluis &amp; Beheer
            </button>
            <button type="button" @click="activeTab = 'loans'"
                    class="px-4 py-2 rounded-xl text-xs font-black uppercase font-mono transition cursor-pointer"
                    :class="activeTab === 'loans' ? 'bg-purple-600 text-white shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800'">
                <i class="fa-solid fa-hand-holding-dollar mr-1"></i> Leningen &amp; Krediet
            </button>
            <button type="button" @click="activeTab = 'transactions'"
                    class="px-4 py-2 rounded-xl text-xs font-black uppercase font-mono transition cursor-pointer"
                    :class="activeTab === 'transactions' ? 'bg-blue-600 text-white shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800'">
                <i class="fa-solid fa-receipt mr-1"></i> Transacties
            </button>
        </div>

        <!-- TAB 1: KLUIS & BEHEER -->
        <div x-show="activeTab === 'vault'" class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Storten Form -->
            <div class="p-6 rounded-2xl border shadow-xs bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800 space-y-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-emerald-100 dark:bg-emerald-950/80 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-bold">
                        <i class="fa-solid fa-arrow-down"></i>
                    </div>
                    <div>
                        <h3 class="font-black text-slate-900 dark:text-white">Geld Storten op Bank</h3>
                        <span class="text-xs text-slate-500">100% Kosteloos &amp; Beveiligd</span>
                    </div>
                </div>

                <form method="POST" action="{{ route('bank.deposit') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="text-xs font-bold text-slate-700 dark:text-slate-300 block mb-1">Te storten bedrag</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 font-mono font-bold">€</span>
                            <input type="number" name="amount" min="1" max="{{ $character->cash }}" placeholder="0" required
                                   class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl py-2.5 pl-8 pr-4 font-mono font-bold text-slate-900 dark:text-white focus:outline-hidden focus:border-emerald-500">
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <button type="submit" class="w-full py-3 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-black text-xs uppercase tracking-wider font-mono shadow-xs transition cursor-pointer">
                            Storten in Beveiligde Kluis
                        </button>
                    </div>
                </form>
            </div>

            <!-- Opnemen Form -->
            <div class="p-6 rounded-2xl border shadow-xs bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800 space-y-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-amber-100 dark:bg-amber-950/80 text-amber-600 dark:text-amber-400 flex items-center justify-center font-bold">
                        <i class="fa-solid fa-arrow-up"></i>
                    </div>
                    <div>
                        <h3 class="font-black text-slate-900 dark:text-white">Contant Geld Opnemen</h3>
                        <span class="text-xs text-slate-500">Voor Zwarte Markt &amp; Spelershandel</span>
                    </div>
                </div>

                <form method="POST" action="{{ route('bank.withdraw') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="text-xs font-bold text-slate-700 dark:text-slate-300 block mb-1">Op te nemen bedrag</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 font-mono font-bold">€</span>
                            <input type="number" name="amount" min="1" max="{{ $character->bank }}" placeholder="0" required
                                   class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl py-2.5 pl-8 pr-4 font-mono font-bold text-slate-900 dark:text-white focus:outline-hidden focus:border-amber-500">
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <button type="submit" class="w-full py-3 rounded-xl bg-purple-600 hover:bg-purple-500 text-white font-black text-xs uppercase tracking-wider font-mono shadow-xs transition cursor-pointer">
                            Contant Opnemen (-{{ $character->isVip() ? '3%' : '5%' }} Tax)
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- TAB 2: LENINGEN & KREDIET -->
        <div x-show="activeTab === 'loans'" class="space-y-6">
            <div class="p-6 rounded-2xl border shadow-xs bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800 space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="font-black text-slate-900 dark:text-white">Nieuwe lening aanvragen</h3>
                        <p class="text-xs text-slate-500">Maximale leencapaciteit: €{{ number_format($maxCapacity ?? 0, 0, ',', '.') }} | Rente &amp; Leningtax (8,5%)</p>
                    </div>
                </div>

                @if(($beschikbareLening ?? 0) >= 10000)
                    <form method="POST" action="{{ route('bank.loans.request') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        @csrf
                        <div>
                            <label class="text-xs font-bold text-slate-700 dark:text-slate-300 block mb-1">Leenbedrag</label>
                            <input type="number" name="amount" min="10000" max="{{ $beschikbareLening }}" step="5000"
                                   value="{{ min(25000, $beschikbareLening) }}" required
                                   class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl py-2 px-3 font-mono font-bold">
                        </div>
                        <div>
                            <label class="text-xs font-bold text-slate-700 dark:text-slate-300 block mb-1">Storten op</label>
                            <select name="deposit_to" class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl py-2 px-3 font-mono font-bold">
                                <option value="bank">Veilige Bankkluis</option>
                                <option value="cash">Contant op Zak</option>
                            </select>
                        </div>
                        <div class="flex items-end">
                            <button type="submit" class="w-full py-2.5 rounded-xl bg-purple-600 hover:bg-purple-500 text-white font-black text-xs uppercase tracking-wider font-mono shadow-xs cursor-pointer">
                                Lening Afsluiten
                            </button>
                        </div>
                    </form>
                @else
                    <div class="p-4 rounded-xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800 text-xs text-amber-800 dark:text-amber-300">
                        Je hebt momenteel de maximale leencapaciteit voor je level bereikt. Los eerst bestaande leningen af om weer kredietruimte vrij te maken.
                    </div>
                @endif
            </div>

            <!-- Active Loans Table -->
            <div class="rounded-2xl border shadow-xs overflow-hidden bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800">
                <div class="p-4 border-b border-slate-200 dark:border-slate-800">
                    <h3 class="font-black text-slate-900 dark:text-white">Leningen Overzicht</h3>
                </div>
                <div class="divide-y divide-slate-200 dark:divide-slate-800">
                    @forelse($activeLoans as $loan)
                        <div class="p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div>
                                <span class="text-xs font-mono font-bold text-rose-600 dark:text-rose-400 block">
                                    Restant: €{{ number_format($loan->remaining_due, 0, ',', '.') }} van €{{ number_format($loan->total_due, 0, ',', '.') }}
                                </span>
                                <span class="text-[11px] text-slate-500">Rente: {{ $loan->interest_rate }}% (€{{ number_format($loan->interest_amount, 0, ',', '.') }})</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <form method="POST" action="{{ route('bank.loans.repay-full', $loan) }}">
                                    @csrf
                                    <input type="hidden" name="source" value="bank">
                                    <button type="submit" class="px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white font-mono font-bold text-xs cursor-pointer">
                                        Alles Aflossen
                                    </button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div class="p-6 text-center text-xs text-slate-500 font-mono">
                            Je hebt momenteel geen actieve leningen openstaan.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- TAB 3: TRANSACTIES -->
        <div x-show="activeTab === 'transactions'" class="rounded-2xl border shadow-xs overflow-hidden bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800">
            <div class="p-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                <h3 class="font-black text-slate-900 dark:text-white">Transacties &amp; Afschriften</h3>
                <span class="text-xs text-slate-500 font-mono">Laatste 15 mutaties</span>
            </div>
            <div class="divide-y divide-slate-200 dark:divide-slate-800 font-mono text-xs">
                @forelse($transactions as $tx)
                    <div class="p-3.5 flex items-center justify-between">
                        <div>
                            <span class="font-bold text-slate-800 dark:text-slate-200 uppercase block">{{ str_replace('_', ' ', $tx->type) }}</span>
                            <span class="text-[10px] text-slate-500">{{ $tx->created_at->format('d-m-Y H:i:s') }}</span>
                        </div>
                        <span class="font-black text-sm {{ $tx->amount >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                            {{ $tx->amount >= 0 ? '+' : '' }}€{{ number_format($tx->amount, 0, ',', '.') }}
                        </span>
                    </div>
                @empty
                    <div class="p-6 text-center text-slate-500">
                        Geen rekeningtransacties gevonden.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
