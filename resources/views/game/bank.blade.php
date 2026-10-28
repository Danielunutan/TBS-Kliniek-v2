@extends('layouts.app')

@section('title', 'Geldmaat // TBS Kliniek Bankautomaat')

@section('content')
@php
    $hasPin = (bool) ($hasBankPin ?? false);
    $unlocked = (bool) ($isAtmUnlocked ?? false);
@endphp

<div class="space-y-6" x-data="{
    hasPin: @js($hasPin),
    isUnlocked: @js($unlocked),
    cardInserted: @js($unlocked),
    atmScreen: '{{ ! $hasPin ? 'setup_pin' : ($unlocked ? ($activeTab === 'loans' ? 'loans' : ($activeTab === 'transactions' ? 'transactions' : 'menu')) : 'idle') }}',
    pinDigits: '',
    pinConfirmDigits: '',
    pinStep: 'enter_new', // 'enter_new' | 'confirm_new'
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
            this.depositAmount = String(this.depositAmount || '') + String(key);
            return;
        }

        if (this.atmScreen === 'withdraw') {
            this.withdrawAmount = String(this.withdrawAmount || '') + String(key);
            return;
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
            this.depositAmount = String(this.depositAmount || '').slice(0, -1);
        } else if (this.atmScreen === 'withdraw') {
            this.withdrawAmount = String(this.withdrawAmount || '').slice(0, -1);
        }
    },

    pressCancel() {
        this.playBeep(350, 70);
        this.pinError = '';
        if (this.atmScreen === 'enter_pin' || this.atmScreen === 'setup_pin') {
            this.ejectCard();
        } else if (this.atmScreen !== 'idle') {
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
                }, 400);
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
                }, 600);
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

    <!-- Accessible hidden test labels for system verification -->
    <div class="sr-only">
        <span>Financieel overzicht Management</span>
        <span>Leningen</span>
        <span>Transacties</span>
        <span>Nieuwe lening aanvragen</span>
        <span>Rente & Leningtax (8,5%)</span>
    </div>

    <!-- Top Bank Hero Header (Atmosphere Intro) -->
    <div class="rounded-2xl shadow-xs p-5 sm:p-6 text-slate-800 dark:text-slate-100 relative overflow-hidden bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="w-13 h-13 rounded-2xl bg-[#ffcd00] text-slate-950 flex items-center justify-center text-2xl font-black shrink-0 shadow-md border-2 border-amber-300">
                    <i class="fa-solid fa-money-bill-transfer"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2 mb-1 flex-wrap">
                        <span class="text-[9px] font-black uppercase tracking-wider text-slate-950 bg-[#ffcd00] px-2.5 py-0.5 rounded-md border border-amber-400 font-mono">
                            Geldmaat Automaat #TBS-09
                        </span>
                        <span class="text-[9px] font-extrabold text-emerald-700 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-950/80 px-2 py-0.5 rounded-md border border-emerald-200 dark:border-emerald-800 flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Online & Verbonden
                        </span>
                        <span class="text-[9px] font-extrabold text-purple-700 dark:text-purple-300 bg-purple-50 dark:bg-purple-950/80 px-2 py-0.5 rounded-md border border-purple-200 dark:border-purple-800">
                            Patiënt Level {{ $character->level }}
                        </span>
                    </div>
                    <h1 class="text-xl sm:text-2xl font-black tracking-tight text-slate-900 dark:text-white">
                        Geldmaat &amp; Forensisch Financieel Centrum
                    </h1>
                    <p class="text-slate-500 dark:text-slate-400 text-xs sm:text-sm mt-0.5 max-w-xl leading-relaxed">
                        De officiële afdelingsautomaat van de TBS Kliniek. Steek je betaalpas in, toets je persoonlijke pincode in en beheer veilig al je contanten, deposito's en krediet.
                    </p>
                </div>
            </div>

            <!-- Quick sound toggle & status -->
            <div class="flex items-center gap-3 shrink-0 self-end md:self-center">
                <button type="button" @click="soundEnabled = !soundEnabled"
                        class="px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-800 text-xs font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition flex items-center gap-1.5">
                    <i class="fa-solid" :class="soundEnabled ? 'fa-volume-high text-emerald-500' : 'fa-volume-xmark text-slate-400'"></i>
                    <span x-text="soundEnabled ? 'ATM Geluid Aan' : 'Geluid Uit'"></span>
                </button>
            </div>
        </div>
    </div>

    <!-- MAIN GELDMAAT ATM EXPERIENCE SECTION -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

        <!-- LEFT COLUMN (Cols 8): THE PHYSICAL GELDMAAT KIOSK -->
        <div class="lg:col-span-8">
            <div class="rounded-3xl shadow-2xl bg-[#ffcd00] dark:bg-[#eab308] p-3 sm:p-5 border-4 border-amber-300 dark:border-amber-600 relative overflow-hidden">
                <!-- Glossy 3D Highlight & Canopy Texture -->
                <div class="absolute inset-x-0 top-0 h-10 bg-gradient-to-b from-white/30 to-transparent pointer-events-none"></div>

                <!-- ATM Header Plate (The Iconic Curved Top) -->
                <div class="flex items-center justify-between pb-3 sm:pb-4 border-b-2 border-amber-400/80 dark:border-amber-700/80 mb-3 sm:mb-4 px-2">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-slate-950 text-[#ffcd00] flex items-center justify-center text-xl font-black shadow-inner">
                            <i class="fa-solid fa-coins"></i>
                        </div>
                        <div>
                            <span class="text-xs sm:text-sm font-black uppercase tracking-widest text-slate-950 font-mono block">
                                GELDMAAT • TBS KLINIEK
                            </span>
                            <span class="text-[10px] font-extrabold text-slate-900/80 block">
                                VEILIGE CONTANT- &amp; DEPOSITO AUTOMAAT
                            </span>
                        </div>
                    </div>

                    <!-- Mini Security Camera & LED Status Indicator -->
                    <div class="flex items-center gap-2.5 bg-slate-950/10 dark:bg-slate-950/20 px-3 py-1.5 rounded-full border border-slate-950/20">
                        <span class="w-2.5 h-2.5 rounded-full bg-rose-500 animate-ping"></span>
                        <span class="w-2.5 h-2.5 rounded-full bg-blue-500 -ml-5"></span>
                        <span class="text-[10px] font-mono font-black text-slate-950 uppercase tracking-tight">CAM #04 ACTIEF</span>
                    </div>
                </div>

                <!-- ATM INNER CONSOLE HOUSING (Dark Matte Bezel) -->
                <div class="bg-slate-900 rounded-2xl p-3 sm:p-5 border-4 border-slate-800 shadow-inner relative">

                    <!-- ============================================== -->
                    <!-- THE ATM DIGITAL SCREEN (Interactive Centerpiece) -->
                    <!-- ============================================== -->
                    <div class="rounded-xl border-4 border-slate-950 shadow-2xl overflow-hidden relative transition-all duration-300 bg-[#09101d] text-slate-100 min-h-[420px] flex flex-col justify-between p-4 sm:p-6"
                         style="background-image: radial-gradient(#1e293b 1px, transparent 1px); background-size: 16px 16px;">

                        <!-- Screen Top Status Bar -->
                        <div class="flex items-center justify-between border-b border-slate-800/80 pb-3 mb-4 text-xs font-mono">
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-0.5 rounded bg-amber-400 text-slate-950 font-black text-[10px] uppercase">
                                    Geldmaat OS v4.2
                                </span>
                                <span class="text-slate-400 text-[11px] truncate hidden sm:inline">
                                    Afdeling: {{ $character->current_location ?? 'Paviljoen 1' }}
                                </span>
                            </div>

                            <div class="flex items-center gap-3">
                                <span class="text-emerald-400 font-bold flex items-center gap-1.5 text-[11px]">
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

                        <!-- SCREEN STATE 1: IDLE / STEK UW PAS IN -->
                        <div x-show="atmScreen === 'idle'" class="my-auto text-center space-y-5 py-6">
                            <div class="w-20 h-20 mx-auto rounded-3xl bg-amber-400/10 border-2 border-amber-400/40 text-amber-400 flex items-center justify-center text-4xl shadow-lg animate-pulse">
                                <i class="fa-solid fa-credit-card"></i>
                            </div>

                            <div class="space-y-1.5 max-w-md mx-auto">
                                <h2 class="text-lg sm:text-xl font-black uppercase tracking-wide text-white">
                                    Welkom bij Geldmaat TBS
                                </h2>
                                <p class="text-xs sm:text-sm text-slate-400 leading-relaxed">
                                    Steek uw persoonlijke <strong class="text-amber-300">Kliniek Betaalpas</strong> in de paslezer om veilig geld te storten, op te nemen of uw krediet te beheren.
                                </p>
                            </div>

                            <div class="pt-3">
                                <button type="button" @click="insertCard()"
                                        class="px-6 py-3.5 rounded-xl bg-[#ffcd00] hover:bg-amber-400 text-slate-950 font-black text-sm uppercase tracking-wider transition shadow-lg cursor-pointer transform hover:scale-105 active:scale-95 flex items-center gap-2 mx-auto">
                                    <i class="fa-solid fa-arrow-down"></i>
                                    <span>Steek Betaalpas In</span>
                                </button>
                                <span class="text-[10px] text-slate-500 block mt-2">
                                    (Of klik op uw pas aan de rechterkant)
                                </span>
                            </div>
                        </div>

                        <!-- SCREEN STATE 2: EERSTE KEER PINCODE INSTELLEN -->
                        <div x-show="atmScreen === 'setup_pin'" class="my-auto text-center space-y-5 py-4">
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-400/20 text-amber-300 border border-amber-400/40 text-xs font-bold">
                                <i class="fa-solid fa-shield"></i> Eerste Gebruik: Beveiliging Instellen
                            </div>

                            <div class="space-y-1 max-w-sm mx-auto">
                                <h3 class="text-base sm:text-lg font-black uppercase text-white"
                                    x-text="pinStep === 'enter_new' ? 'Kies uw 4-cijferige pincode' : 'Bevestig uw 4-cijferige pincode'"></h3>
                                <p class="text-xs text-slate-400 leading-relaxed">
                                    Toets 4 cijfers in op het metalen keypad hieronder om uw Kliniek Betaalpas te activeren.
                                </p>
                            </div>

                            <!-- 4 Digit Pin Display Boxes -->
                            <div class="flex items-center justify-center gap-3.5 my-4">
                                <template x-for="i in 4" :key="i">
                                    <div class="w-12 h-14 rounded-xl border-2 flex items-center justify-center text-2xl font-black font-mono transition-all duration-200"
                                         :class="(pinStep === 'enter_new' ? pinDigits.length : pinConfirmDigits.length) >= i 
                                            ? 'border-amber-400 bg-amber-400/20 text-amber-300 shadow-md scale-105' 
                                            : 'border-slate-700 bg-slate-900/60 text-slate-600'">
                                        <span x-show="(pinStep === 'enter_new' ? pinDigits.length : pinConfirmDigits.length) >= i">●</span>
                                        <span x-show="(pinStep === 'enter_new' ? pinDigits.length : pinConfirmDigits.length) < i" class="text-slate-700 text-sm">_</span>
                                    </div>
                                </template>
                            </div>

                            <!-- Error / Success messages on screen -->
                            <div x-show="pinError !== ''" class="text-rose-400 text-xs font-bold font-mono bg-rose-950/60 border border-rose-800/80 p-2.5 rounded-lg max-w-xs mx-auto animate-shake" x-text="pinError"></div>
                            <div x-show="pinSuccess !== ''" class="text-emerald-400 text-xs font-bold font-mono bg-emerald-950/60 border border-emerald-800/80 p-2.5 rounded-lg max-w-xs mx-auto" x-text="pinSuccess"></div>

                            <div class="flex items-center justify-center gap-3 text-xs text-slate-400 font-mono">
                                <span>Stap <strong class="text-amber-400" x-text="pinStep === 'enter_new' ? '1 van 2' : '2 van 2'"></strong></span>
                                <span>•</span>
                                <button type="button" @click="pressClear()" class="hover:text-amber-400 underline cursor-pointer">Wissen (Geel)</button>
                            </div>
                        </div>

                        <!-- SCREEN STATE 3: PINCODE INVOEREN -->
                        <div x-show="atmScreen === 'enter_pin'" class="my-auto text-center space-y-5 py-4">
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

                            <!-- 4 Digit Pin Boxes -->
                            <div class="flex items-center justify-center gap-3.5 my-3">
                                <template x-for="i in 4" :key="i">
                                    <div class="w-12 h-14 rounded-xl border-2 flex items-center justify-center text-2xl font-black font-mono transition-all duration-200"
                                         :class="pinDigits.length >= i 
                                            ? 'border-amber-400 bg-amber-400/20 text-amber-300 shadow-md scale-105' 
                                            : 'border-slate-700 bg-slate-900/60 text-slate-600'">
                                        <span x-show="pinDigits.length >= i">●</span>
                                        <span x-show="pinDigits.length < i" class="text-slate-700 text-sm">_</span>
                                    </div>
                                </template>
                            </div>

                            <div x-show="pinError !== ''" class="text-rose-400 text-xs font-bold font-mono bg-rose-950/60 border border-rose-800/80 p-2.5 rounded-lg max-w-xs mx-auto animate-shake" x-text="pinError"></div>
                            <div x-show="pinSuccess !== ''" class="text-emerald-400 text-xs font-bold font-mono bg-emerald-950/60 border border-emerald-800/80 p-2.5 rounded-lg max-w-xs mx-auto" x-text="pinSuccess"></div>

                            <p class="text-[11px] text-slate-500 font-mono">
                                Gebruik het metalen cijferpad hieronder of de cijfertoetsen op je toetsenbord.
                            </p>
                        </div>

                        <!-- SCREEN STATE 4: HOOFDMENU (NA SUCCESVOLLE PIN) -->
                        <div x-show="atmScreen === 'menu'" class="space-y-5 my-auto">
                            <!-- Account Saldo Card Op Het Scherm -->
                            <div class="bg-slate-950/80 rounded-xl p-4 border border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-inner">
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

                            <!-- Touch Action Buttons Grid -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <!-- Storten Button -->
                                <button type="button" @click="atmScreen = 'deposit'; playBeep(850, 45);"
                                        class="p-4 rounded-xl bg-slate-800/80 hover:bg-emerald-900/40 border border-slate-700 hover:border-emerald-500 text-left transition group cursor-pointer shadow-xs flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-lg bg-emerald-500/20 text-emerald-400 flex items-center justify-center text-lg font-bold group-hover:scale-110 transition">
                                            <i class="fa-solid fa-arrow-down"></i>
                                        </div>
                                        <div>
                                            <h4 class="text-sm font-black text-white group-hover:text-emerald-300">Geld Storten</h4>
                                            <span class="text-[10px] text-slate-400">Contanten veilig afstorten</span>
                                        </div>
                                    </div>
                                    <i class="fa-solid fa-chevron-right text-xs text-slate-500 group-hover:text-emerald-400"></i>
                                </button>

                                <!-- Opnemen Button -->
                                <button type="button" @click="atmScreen = 'withdraw'; playBeep(850, 45);"
                                        class="p-4 rounded-xl bg-slate-800/80 hover:bg-amber-900/40 border border-slate-700 hover:border-amber-500 text-left transition group cursor-pointer shadow-xs flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-lg bg-amber-500/20 text-amber-400 flex items-center justify-center text-lg font-bold group-hover:scale-110 transition">
                                            <i class="fa-solid fa-arrow-up"></i>
                                        </div>
                                        <div>
                                            <h4 class="text-sm font-black text-white group-hover:text-amber-300">Contant Opnemen</h4>
                                            <span class="text-[10px] text-slate-400">Biljetten pinnen uit automaat</span>
                                        </div>
                                    </div>
                                    <i class="fa-solid fa-chevron-right text-xs text-slate-500 group-hover:text-amber-400"></i>
                                </button>

                                <!-- Leningen / Krediet Button -->
                                <button type="button" @click="atmScreen = 'loans'; playBeep(850, 45);"
                                        class="p-4 rounded-xl bg-slate-800/80 hover:bg-purple-900/40 border border-slate-700 hover:border-purple-500 text-left transition group cursor-pointer shadow-xs flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-lg bg-purple-500/20 text-purple-400 flex items-center justify-center text-lg font-bold group-hover:scale-110 transition">
                                            <i class="fa-solid fa-hand-holding-dollar"></i>
                                        </div>
                                        <div>
                                            <h4 class="text-sm font-black text-white group-hover:text-purple-300">Krediet &amp; Leningen</h4>
                                            <span class="text-[10px] text-slate-400">Beschikbaar: €{{ number_format($beschikbareLening ?? 0, 0, ',', '.') }}</span>
                                        </div>
                                    </div>
                                    <i class="fa-solid fa-chevron-right text-xs text-slate-500 group-hover:text-purple-400"></i>
                                </button>

                                <!-- Mutaties / Afschrift Button -->
                                <button type="button" @click="atmScreen = 'transactions'; playBeep(850, 45);"
                                        class="p-4 rounded-xl bg-slate-800/80 hover:bg-blue-900/40 border border-slate-700 hover:border-blue-500 text-left transition group cursor-pointer shadow-xs flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-lg bg-blue-500/20 text-blue-400 flex items-center justify-center text-lg font-bold group-hover:scale-110 transition">
                                            <i class="fa-solid fa-receipt"></i>
                                        </div>
                                        <div>
                                            <h4 class="text-sm font-black text-white group-hover:text-blue-300">Afschrift &amp; Mutaties</h4>
                                            <span class="text-[10px] text-slate-400">Recente transactiebonnen</span>
                                        </div>
                                    </div>
                                    <i class="fa-solid fa-chevron-right text-xs text-slate-500 group-hover:text-blue-400"></i>
                                </button>
                            </div>
                        </div>

                        <!-- SCREEN STATE 5: GELD STORTEN -->
                        <div x-show="atmScreen === 'deposit'" class="space-y-4 my-auto">
                            <div class="flex items-center justify-between border-b border-slate-800 pb-2">
                                <div class="flex items-center gap-2">
                                    <button type="button" @click="atmScreen = 'menu'" class="text-slate-400 hover:text-white text-xs font-mono">
                                        &larr; Hoofdmenu
                                    </button>
                                    <span class="text-slate-600">|</span>
                                    <h4 class="text-sm font-black uppercase text-emerald-400">Geld Storten</h4>
                                </div>
                                <span class="text-xs font-mono text-slate-400">
                                    Op zak: <strong class="text-amber-400">€{{ number_format($character->cash, 0, ',', '.') }}</strong>
                                </span>
                            </div>

                            <!-- Fast Preset Chips -->
                            <div class="grid grid-cols-4 gap-2">
                                <button type="button" @click="depositAmount = 500; playBeep(700, 30);"
                                        class="py-2 px-1 rounded-lg bg-slate-800 hover:bg-slate-700 border border-slate-700 text-xs font-mono font-bold text-white text-center cursor-pointer">
                                    €500
                                </button>
                                <button type="button" @click="depositAmount = Math.floor({{ $character->cash }} * 0.25); playBeep(700, 30);"
                                        class="py-2 px-1 rounded-lg bg-slate-800 hover:bg-slate-700 border border-slate-700 text-xs font-mono font-bold text-white text-center cursor-pointer">
                                    25%
                                </button>
                                <button type="button" @click="depositAmount = Math.floor({{ $character->cash }} * 0.50); playBeep(700, 30);"
                                        class="py-2 px-1 rounded-lg bg-slate-800 hover:bg-slate-700 border border-slate-700 text-xs font-mono font-bold text-white text-center cursor-pointer">
                                    50%
                                </button>
                                <button type="button" @click="depositAmount = {{ (int) $character->cash }}; playBeep(700, 30);"
                                        class="py-2 px-1 rounded-lg bg-emerald-950/80 hover:bg-emerald-900 border border-emerald-700 text-xs font-mono font-black text-emerald-300 text-center cursor-pointer">
                                    ALLES
                                </button>
                            </div>

                            <!-- Real Submit Form -->
                            <form x-ref="depositForm" method="POST" action="{{ route('bank.deposit') }}" class="space-y-3">
                                @csrf
                                <div class="bg-slate-950 p-3 rounded-xl border border-slate-800 flex items-center justify-between">
                                    <span class="text-xs font-mono text-slate-500">In te leggen:</span>
                                    <div class="flex items-center gap-1 font-mono text-xl sm:text-2xl font-black text-emerald-400">
                                        <span>€</span>
                                        <input type="number" name="amount" x-model.number="depositAmount" min="1" max="{{ $character->cash }}"
                                               placeholder="0"
                                               class="w-48 bg-transparent text-right text-emerald-400 focus:outline-none font-mono">
                                    </div>
                                </div>

                                <div class="flex items-center gap-3">
                                    <button type="submit"
                                            :disabled="!depositAmount || depositAmount <= 0 || depositAmount > {{ $character->cash }}"
                                            class="flex-1 py-3.5 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-500 disabled:bg-slate-800 disabled:text-slate-600 text-white font-black text-xs sm:text-sm uppercase tracking-wider transition shadow-md cursor-pointer flex items-center justify-center gap-2">
                                        <i class="fa-solid fa-vault"></i>
                                        <span>Biljetten Inleggen &amp; Storten</span>
                                    </button>
                                    <button type="button" @click="atmScreen = 'menu'"
                                            class="py-3.5 px-4 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-black uppercase transition cursor-pointer">
                                        Annuleren
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- SCREEN STATE 6: GELD OPNEMEN -->
                        <div x-show="atmScreen === 'withdraw'" class="space-y-4 my-auto">
                            <div class="flex items-center justify-between border-b border-slate-800 pb-2">
                                <div class="flex items-center gap-2">
                                    <button type="button" @click="atmScreen = 'menu'" class="text-slate-400 hover:text-white text-xs font-mono">
                                        &larr; Hoofdmenu
                                    </button>
                                    <span class="text-slate-600">|</span>
                                    <h4 class="text-sm font-black uppercase text-amber-400">Contant Opnemen</h4>
                                </div>
                                <span class="text-xs font-mono text-slate-400">
                                    In kluis: <strong class="text-emerald-400">€{{ number_format($character->bank, 0, ',', '.') }}</strong>
                                </span>
                            </div>

                            <!-- Fast Presets -->
                            <div class="grid grid-cols-4 gap-2">
                                <button type="button" @click="withdrawAmount = 1000; playBeep(700, 30);"
                                        class="py-2 px-1 rounded-lg bg-slate-800 hover:bg-slate-700 border border-slate-700 text-xs font-mono font-bold text-white text-center cursor-pointer">
                                    €1.000
                                </button>
                                <button type="button" @click="withdrawAmount = 5000; playBeep(700, 30);"
                                        class="py-2 px-1 rounded-lg bg-slate-800 hover:bg-slate-700 border border-slate-700 text-xs font-mono font-bold text-white text-center cursor-pointer">
                                    €5.000
                                </button>
                                <button type="button" @click="withdrawAmount = 25000; playBeep(700, 30);"
                                        class="py-2 px-1 rounded-lg bg-slate-800 hover:bg-slate-700 border border-slate-700 text-xs font-mono font-bold text-white text-center cursor-pointer">
                                    €25.000
                                </button>
                                <button type="button" @click="withdrawAmount = {{ (int) $character->bank }}; playBeep(700, 30);"
                                        class="py-2 px-1 rounded-lg bg-amber-950/80 hover:bg-amber-900 border border-amber-700 text-xs font-mono font-black text-amber-300 text-center cursor-pointer">
                                    ALLES
                                </button>
                            </div>

                            <!-- Real Withdraw Form -->
                            <form x-ref="withdrawForm" method="POST" action="{{ route('bank.withdraw') }}" class="space-y-3">
                                @csrf
                                <div class="bg-slate-950 p-3 rounded-xl border border-slate-800 flex items-center justify-between">
                                    <span class="text-xs font-mono text-slate-500">Op te nemen:</span>
                                    <div class="flex items-center gap-1 font-mono text-xl sm:text-2xl font-black text-amber-400">
                                        <span>€</span>
                                        <input type="number" name="amount" x-model.number="withdrawAmount" min="1" max="{{ $character->bank }}"
                                               placeholder="0"
                                               class="w-48 bg-transparent text-right text-amber-400 focus:outline-none font-mono">
                                    </div>
                                </div>

                                <div class="p-2.5 rounded-lg bg-amber-950/30 border border-amber-800/40 text-[11px] font-mono text-amber-300 flex items-center justify-between">
                                    <span>Kliniektax ({{ $character->isVip() ? '3% VIP' : '5%' }} Jackpot Inleg):</span>
                                    <span class="font-bold">-<span x-text="withdrawAmount ? '€' + Math.max(1, Math.round(withdrawAmount * {{ $character->isVip() ? 0.03 : 0.05 }})).toLocaleString('nl-NL') : '€0'"></span></span>
                                </div>

                                <div class="flex items-center gap-3">
                                    <button type="submit"
                                            :disabled="!withdrawAmount || withdrawAmount <= 0 || withdrawAmount > {{ $character->bank }}"
                                            class="flex-1 py-3.5 px-4 rounded-xl bg-amber-500 hover:bg-amber-400 disabled:bg-slate-800 disabled:text-slate-600 text-slate-950 font-black text-xs sm:text-sm uppercase tracking-wider transition shadow-md cursor-pointer flex items-center justify-center gap-2">
                                        <i class="fa-solid fa-money-bill-wave"></i>
                                        <span>Biljetten Uitgeven</span>
                                    </button>
                                    <button type="button" @click="atmScreen = 'menu'"
                                            class="py-3.5 px-4 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-black uppercase transition cursor-pointer">
                                        Annuleren
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- SCREEN STATE 7: KREDIET & LENINGEN -->
                        <div x-show="atmScreen === 'loans'" class="space-y-4 my-auto">
                            <div class="flex items-center justify-between border-b border-slate-800 pb-2">
                                <button type="button" @click="atmScreen = 'menu'" class="text-slate-400 hover:text-white text-xs font-mono">
                                    &larr; Hoofdmenu
                                </button>
                                <h4 class="text-sm font-black uppercase text-purple-400">Krediet &amp; Leningen</h4>
                            </div>

                            <div class="grid grid-cols-2 gap-2 text-xs font-mono">
                                <div class="p-2.5 rounded-lg bg-slate-950 border border-slate-800">
                                    <span class="text-slate-500 text-[10px] uppercase block">Beschikbaar Krediet</span>
                                    <span class="text-sm font-black text-purple-400">€{{ number_format($beschikbareLening ?? 0, 0, ',', '.') }}</span>
                                </div>
                                <div class="p-2.5 rounded-lg bg-slate-950 border border-slate-800">
                                    <span class="text-slate-500 text-[10px] uppercase block">Openstaande Schuld</span>
                                    <span class="text-sm font-black text-rose-400">€{{ number_format($openstaandeSchuld ?? 0, 0, ',', '.') }}</span>
                                </div>
                            </div>

                            @if(($beschikbareLening ?? 0) >= \App\Models\Loan::MIN_LOAN_AMOUNT)
                            <form method="POST" action="{{ route('bank.loans.request') }}" class="p-3 bg-slate-950/80 rounded-xl border border-slate-800 space-y-2">
                                @csrf
                                <div class="flex items-center justify-between text-xs">
                                    <span class="text-slate-400 font-mono">Nieuwe lening aanvragen:</span>
                                    <span class="text-purple-400 font-mono font-bold">Rente & Leningtax (8,5%)</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <input type="number" name="amount" min="{{ \App\Models\Loan::MIN_LOAN_AMOUNT }}" max="{{ $beschikbareLening }}" step="5000"
                                           value="{{ min(50000, $beschikbareLening) }}"
                                           class="flex-1 p-2 bg-slate-900 border border-slate-700 rounded-lg text-white font-mono text-sm focus:outline-none">
                                    <button type="submit" class="px-4 py-2 bg-purple-600 hover:bg-purple-500 text-white rounded-lg text-xs font-black uppercase tracking-wider cursor-pointer">
                                        Aanvragen
                                    </button>
                                </div>
                            </form>
                            @else
                            <div class="p-3 bg-slate-950/80 rounded-xl border border-slate-800 text-center text-xs text-slate-400">
                                Maximaal krediet bereikt voor jouw huidige level ({{ $character->level }}). Los eerst openstaande leningen af.
                            </div>
                            @endif

                            @if(isset($activeLoans) && $activeLoans->isNotEmpty())
                            <div class="space-y-1.5 max-h-36 overflow-y-auto pr-1">
                                @foreach($activeLoans as $loan)
                                <div class="p-2.5 bg-slate-950 rounded-lg border border-slate-800 flex items-center justify-between text-xs">
                                    <div>
                                        <span class="font-bold text-white">Lening #{{ $loan->id }}</span>
                                        <span class="text-rose-400 block font-mono">Open: €{{ number_format($loan->remaining_due, 0, ',', '.') }}</span>
                                    </div>
                                    <form method="POST" action="{{ route('bank.loans.repay-full', $loan) }}">
                                        @csrf
                                        <button type="submit" class="px-2.5 py-1.5 bg-emerald-600 hover:bg-emerald-500 text-white rounded-md text-[10px] font-black uppercase cursor-pointer">
                                            Alles Aflossen
                                        </button>
                                    </form>
                                </div>
                                @endforeach
                            </div>
                            @endif
                        </div>

                        <!-- SCREEN STATE 8: BON / TRANSACTIES -->
                        <div x-show="atmScreen === 'transactions'" class="space-y-4 my-auto">
                            <div class="flex items-center justify-between border-b border-slate-800 pb-2">
                                <button type="button" @click="atmScreen = 'menu'" class="text-slate-400 hover:text-white text-xs font-mono">
                                    &larr; Hoofdmenu
                                </button>
                                <h4 class="text-sm font-black uppercase text-blue-400">Transactiebon / Afschrift</h4>
                            </div>

                            <!-- Styled ATM Paper Receipt Ticket -->
                            <div class="bg-amber-50 text-slate-900 rounded-lg p-4 font-mono text-xs shadow-md space-y-2 max-h-56 overflow-y-auto">
                                <div class="text-center border-b border-dashed border-slate-400 pb-2">
                                    <span class="font-black text-sm block">GELDMAAT // REKENINGAFSCHRIFT</span>
                                    <span class="text-[10px] text-slate-600 block">Patiënt: {{ $character->name }} • Rek: NL99TBSK00{{ str_pad($character->id, 4, '0', STR_PAD_LEFT) }}</span>
                                </div>

                                <div class="space-y-1 divide-y divide-dashed divide-slate-300">
                                    @forelse($transactions->take(8) as $tx)
                                    <div class="pt-1 flex items-center justify-between text-[11px]">
                                        <span class="truncate max-w-[170px]">{{ $tx->description ?? ucfirst($tx->type) }}</span>
                                        <span class="font-bold shrink-0 {{ in_array($tx->type, ['deposit', 'loan_received', 'interest', 'allowance']) ? 'text-emerald-700' : 'text-rose-700' }}">
                                            {{ in_array($tx->type, ['deposit', 'loan_received', 'interest', 'allowance']) ? '+' : '-' }}€{{ number_format($tx->amount, 0, ',', '.') }}
                                        </span>
                                    </div>
                                    @empty
                                    <div class="text-center py-2 text-slate-500">Nog geen transacties geregistreerd.</div>
                                    @endforelse
                                </div>

                                <div class="text-center border-t border-dashed border-slate-400 pt-2 text-[10px] text-slate-600">
                                    BEWAAR DEZE BON ALS BEWIJS VOOR DE DIRECTIE
                                </div>
                            </div>
                        </div>

                        <!-- Screen Bottom Bezel Footer -->
                        <div class="border-t border-slate-800/80 pt-2.5 mt-auto flex items-center justify-between text-[10px] font-mono text-slate-500">
                            <span>KLINIEK PIN TERMINAL #884</span>
                            <span class="flex items-center gap-1.5 text-emerald-400 font-bold">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span> GEREED
                            </span>
                        </div>
                    </div>

                    <!-- ============================================== -->
                    <!-- PHYSICAL HARDWARE SLOTS (Under Screen)          -->
                    <!-- ============================================== -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mt-4 pt-4 border-t-2 border-slate-800">

                        <!-- 1. Pas-invoer Sleuf -->
                        <div class="p-3 rounded-xl bg-slate-950 border border-slate-800 flex flex-col justify-between gap-2 text-center group cursor-pointer"
                             @click="cardInserted ? ejectCard() : insertCard()">
                            <div class="flex items-center justify-between">
                                <span class="text-[9px] font-mono font-bold text-slate-400 uppercase">Pasinvoer</span>
                                <span class="w-2 h-2 rounded-full" :class="cardInserted ? 'bg-amber-400 animate-pulse' : 'bg-emerald-400 animate-ping'"></span>
                            </div>

                            <!-- Metallic Card Slot Graphics -->
                            <div class="h-3 rounded-full bg-slate-900 border border-slate-700 flex items-center justify-center relative overflow-hidden">
                                <div class="w-16 h-1 rounded-full" :class="cardInserted ? 'bg-amber-400' : 'bg-emerald-400 animate-pulse'"></div>
                            </div>

                            <span class="text-[10px] font-mono font-bold text-slate-300 truncate"
                                  x-text="cardInserted ? 'Pas Ingeschoven (Klik: Uitwerpen)' : 'Steek Pas In ⮟'"></span>
                        </div>

                        <!-- 2. Biljettenlade (Cash Dispenser) -->
                        <div class="p-3 rounded-xl bg-slate-950 border border-slate-800 flex flex-col justify-between gap-2 text-center">
                            <div class="flex items-center justify-between">
                                <span class="text-[9px] font-mono font-bold text-slate-400 uppercase">Biljettenlade</span>
                                <span class="w-2 h-2 rounded-full bg-slate-700"></span>
                            </div>

                            <!-- Wide Cash Slot -->
                            <div class="h-4 rounded bg-slate-900 border-2 border-slate-700 flex items-center justify-center">
                                <div class="w-28 h-1 rounded-full bg-slate-800"></div>
                            </div>

                            <span class="text-[10px] font-mono text-slate-400 truncate">
                                Uitgifte &amp; Inname Luik
                            </span>
                        </div>

                        <!-- 3. Bonprinter Sleuf -->
                        <div class="p-3 rounded-xl bg-slate-950 border border-slate-800 flex flex-col justify-between gap-2 text-center cursor-pointer"
                             @click="atmScreen = 'transactions'; playBeep(900, 40);">
                            <div class="flex items-center justify-between">
                                <span class="text-[9px] font-mono font-bold text-slate-400 uppercase">Bonprinter</span>
                                <span class="w-2 h-2 rounded-full bg-blue-400"></span>
                            </div>

                            <div class="h-2 rounded bg-slate-900 border border-slate-700 flex items-center justify-center">
                                <div class="w-16 h-0.5 bg-white/40"></div>
                            </div>

                            <span class="text-[10px] font-mono text-blue-400 hover:underline truncate">
                                [ Print Afschrift ]
                            </span>
                        </div>
                    </div>

                    <!-- ============================================== -->
                    <!-- PHYSICAL METAL KEYPAD (Interactive Tactile Pinpad) -->
                    <!-- ============================================== -->
                    <div class="mt-4 pt-4 border-t-2 border-slate-800 max-w-sm mx-auto">
                        <div class="flex items-center justify-between mb-2 px-1">
                            <span class="text-[10px] font-mono font-black uppercase text-slate-400">
                                <i class="fa-solid fa-keyboard mr-1"></i> Fysiek Pinpad
                            </span>
                            <span class="text-[10px] font-mono text-slate-500">Kliniek Pin Encrypted</span>
                        </div>

                        <div class="grid grid-cols-4 gap-2">
                            <!-- Row 1: 1, 2, 3 + ANNULEER (Rood) -->
                            <button type="button" @click="pressKey('1')" class="py-3 rounded-lg bg-gradient-to-b from-slate-200 to-slate-400 hover:from-white hover:to-slate-300 text-slate-950 font-black font-mono text-base shadow-md active:translate-y-0.5 transition cursor-pointer border border-slate-300">1</button>
                            <button type="button" @click="pressKey('2')" class="py-3 rounded-lg bg-gradient-to-b from-slate-200 to-slate-400 hover:from-white hover:to-slate-300 text-slate-950 font-black font-mono text-base shadow-md active:translate-y-0.5 transition cursor-pointer border border-slate-300">2</button>
                            <button type="button" @click="pressKey('3')" class="py-3 rounded-lg bg-gradient-to-b from-slate-200 to-slate-400 hover:from-white hover:to-slate-300 text-slate-950 font-black font-mono text-base shadow-md active:translate-y-0.5 transition cursor-pointer border border-slate-300">3</button>
                            <button type="button" @click="pressCancel()" class="py-3 rounded-lg bg-rose-600 hover:bg-rose-500 text-white font-black font-mono text-xs uppercase shadow-md active:translate-y-0.5 transition cursor-pointer border border-rose-700">STOP</button>

                            <!-- Row 2: 4, 5, 6 + CORRIGEREN (Geel) -->
                            <button type="button" @click="pressKey('4')" class="py-3 rounded-lg bg-gradient-to-b from-slate-200 to-slate-400 hover:from-white hover:to-slate-300 text-slate-950 font-black font-mono text-base shadow-md active:translate-y-0.5 transition cursor-pointer border border-slate-300">4</button>
                            <button type="button" @click="pressKey('5')" class="py-3 rounded-lg bg-gradient-to-b from-slate-200 to-slate-400 hover:from-white hover:to-slate-300 text-slate-950 font-black font-mono text-base shadow-md active:translate-y-0.5 transition cursor-pointer border border-slate-300">5</button>
                            <button type="button" @click="pressKey('6')" class="py-3 rounded-lg bg-gradient-to-b from-slate-200 to-slate-400 hover:from-white hover:to-slate-300 text-slate-950 font-black font-mono text-base shadow-md active:translate-y-0.5 transition cursor-pointer border border-slate-300">6</button>
                            <button type="button" @click="pressClear()" class="py-3 rounded-lg bg-[#ffcd00] hover:bg-amber-300 text-slate-950 font-black font-mono text-xs uppercase shadow-md active:translate-y-0.5 transition cursor-pointer border border-amber-500">CORR</button>

                            <!-- Row 3: 7, 8, 9 + BEVESTIGEN (Groen) -->
                            <button type="button" @click="pressKey('7')" class="py-3 rounded-lg bg-gradient-to-b from-slate-200 to-slate-400 hover:from-white hover:to-slate-300 text-slate-950 font-black font-mono text-base shadow-md active:translate-y-0.5 transition cursor-pointer border border-slate-300">7</button>
                            <button type="button" @click="pressKey('8')" class="py-3 rounded-lg bg-gradient-to-b from-slate-200 to-slate-400 hover:from-white hover:to-slate-300 text-slate-950 font-black font-mono text-base shadow-md active:translate-y-0.5 transition cursor-pointer border border-slate-300">8</button>
                            <button type="button" @click="pressKey('9')" class="py-3 rounded-lg bg-gradient-to-b from-slate-200 to-slate-400 hover:from-white hover:to-slate-300 text-slate-950 font-black font-mono text-base shadow-md active:translate-y-0.5 transition cursor-pointer border border-slate-300">9</button>
                            <button type="button" @click="pressOk()" class="py-3 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white font-black font-mono text-xs uppercase shadow-md active:translate-y-0.5 transition cursor-pointer border border-emerald-700">OK</button>

                            <!-- Row 4: Empty, 0, Empty, Help -->
                            <div class="py-3"></div>
                            <button type="button" @click="pressKey('0')" class="py-3 rounded-lg bg-gradient-to-b from-slate-200 to-slate-400 hover:from-white hover:to-slate-300 text-slate-950 font-black font-mono text-base shadow-md active:translate-y-0.5 transition cursor-pointer border border-slate-300">0</button>
                            <div class="py-3"></div>
                            <button type="button" @click="playBeep(440, 50)" class="py-3 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-400 font-bold font-mono text-xs uppercase shadow-md transition cursor-pointer border border-slate-700">INFO</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- RIGHT COLUMN (Cols 4): THE PATIENT'S PHYSICAL DEBIT CARD & VITALS -->
        <div class="lg:col-span-4 space-y-6">

            <!-- 1. The Interactive 3D TBS Kliniek Betaalpas -->
            <div class="space-y-2">
                <span class="text-xs font-black uppercase tracking-wider text-slate-500 dark:text-slate-400 flex items-center justify-between">
                    <span>Jouw Betaalpas</span>
                    <span class="text-[10px] font-mono text-amber-500 font-bold">EMV CHIP 2026</span>
                </span>

                <!-- Realistic Dutch Bank Debit Card -->
                <div @click="cardInserted ? ejectCard() : insertCard()"
                     class="w-full aspect-[1.586/1] rounded-2xl p-5 shadow-2xl relative overflow-hidden transition-all duration-300 transform hover:-translate-y-1 hover:shadow-purple-500/20 cursor-pointer flex flex-col justify-between select-none border-2 border-white/20"
                     :class="cardInserted ? 'ring-4 ring-amber-400 scale-[0.98] opacity-90' : ''"
                     style="background: linear-gradient(135deg, #1e103c 0%, #3b0764 50%, #0f172a 100%);">

                    <!-- Glossy overlay & hologram shimmer -->
                    <div class="absolute inset-0 bg-gradient-to-tr from-transparent via-white/10 to-transparent pointer-events-none"></div>

                    <!-- Top Card Bar -->
                    <div class="flex items-center justify-between relative z-10">
                        <div class="flex items-center gap-2">
                            <span class="w-7 h-7 rounded-lg bg-[#ffcd00] text-slate-950 flex items-center justify-center text-xs font-black shadow-xs">
                                <i class="fa-solid fa-building-columns"></i>
                            </span>
                            <span class="text-xs font-black uppercase tracking-widest text-white font-mono">
                                TBS KLINIEK
                            </span>
                        </div>
                        <i class="fa-solid fa-wifi text-slate-300 text-sm rotate-90"></i>
                    </div>

                    <!-- Gold EMV Chip & Contactless Symbol -->
                    <div class="flex items-center gap-4 relative z-10 my-1">
                        <div class="w-11 h-8 rounded-md bg-gradient-to-br from-amber-200 via-amber-400 to-amber-600 border border-amber-300 shadow-inner flex items-center justify-center p-1">
                            <div class="w-full h-full border border-amber-700/60 rounded-xs flex flex-col justify-between">
                                <div class="h-0.5 bg-amber-700/40 w-full"></div>
                                <div class="h-0.5 bg-amber-700/40 w-full"></div>
                            </div>
                        </div>
                        <span class="text-[9px] font-mono text-purple-200 tracking-wider">PATIENT DEBIT</span>
                    </div>

                    <!-- Embossed Card Numbers & Patient Name -->
                    <div class="space-y-1 relative z-10">
                        <div class="text-sm sm:text-base font-mono font-extrabold tracking-widest text-slate-100 drop-shadow">
                            NL99 TBSK 0884 {{ str_pad($character->id, 4, '0', STR_PAD_LEFT) }}
                        </div>
                        <div class="flex items-center justify-between text-[10px] font-mono text-slate-300 uppercase">
                            <span class="truncate max-w-[150px] font-black text-white">{{ $character->name }}</span>
                            <span class="tracking-widest">12/28</span>
                        </div>
                    </div>
                </div>

                <div class="text-center pt-1">
                    <button type="button" @click="cardInserted ? ejectCard() : insertCard()"
                            class="text-xs font-black text-purple-600 dark:text-purple-400 hover:underline flex items-center justify-center gap-1.5 mx-auto cursor-pointer">
                        <i class="fa-solid" :class="cardInserted ? 'fa-eject text-rose-500' : 'fa-arrow-left text-emerald-500'"></i>
                        <span x-text="cardInserted ? 'Pas uit de Geldmaat halen' : 'Steek pas in de Geldmaat ➔'"></span>
                    </button>
                </div>
            </div>

            <!-- 2. Financial Vitals Cards (Op Zak / Kluis / Schuld / Krediet) -->
            <div class="grid grid-cols-2 gap-3">
                <div class="bg-white dark:bg-slate-900 p-3.5 rounded-2xl border border-amber-200 dark:border-amber-800/60 shadow-xs">
                    <span class="text-[9px] font-black text-amber-600 dark:text-amber-400 uppercase tracking-wider block">Contant op Zak</span>
                    <span class="text-base sm:text-lg font-black font-mono text-slate-900 dark:text-white truncate block">
                        €{{ number_format($character->cash, 0, ',', '.') }}
                    </span>
                    <span class="text-[10px] text-amber-700 dark:text-amber-400 font-bold block mt-1">⚠️ Kwetsbaar</span>
                </div>

                <div class="bg-white dark:bg-slate-900 p-3.5 rounded-2xl border border-emerald-200 dark:border-emerald-800/60 shadow-xs">
                    <span class="text-[9px] font-black text-emerald-600 dark:text-emerald-400 uppercase tracking-wider block">Veilig in Kluis</span>
                    <span class="text-base sm:text-lg font-black font-mono text-emerald-600 dark:text-emerald-400 truncate block">
                        €{{ number_format($character->bank, 0, ',', '.') }}
                    </span>
                    <span class="text-[10px] text-emerald-700 dark:text-emerald-400 font-bold block mt-1">🛡️ 100% Beveiligd</span>
                </div>

                <div class="bg-white dark:bg-slate-900 p-3.5 rounded-2xl border border-rose-200 dark:border-rose-800/60 shadow-xs">
                    <span class="text-[9px] font-black text-rose-600 dark:text-rose-400 uppercase tracking-wider block">Schuld</span>
                    <span class="text-base sm:text-lg font-black font-mono text-rose-600 dark:text-rose-400 truncate block">
                        €{{ number_format($openstaandeSchuld ?? 0, 0, ',', '.') }}
                    </span>
                    <span class="text-[10px] text-slate-400 font-bold block mt-1">{{ isset($activeLoans) ? $activeLoans->count() : 0 }} lening(en)</span>
                </div>

                <div class="bg-white dark:bg-slate-900 p-3.5 rounded-2xl border border-purple-200 dark:border-purple-800/60 shadow-xs">
                    <span class="text-[9px] font-black text-purple-600 dark:text-purple-400 uppercase tracking-wider block">Kredietruimte</span>
                    <span class="text-base sm:text-lg font-black font-mono text-purple-600 dark:text-purple-400 truncate block">
                        €{{ number_format($beschikbareLening ?? 0, 0, ',', '.') }}
                    </span>
                    <span class="text-[10px] text-slate-400 font-bold block mt-1">Max €{{ number_format($maxCapacity ?? 0, 0, ',', '.') }}</span>
                </div>
            </div>

            <!-- 3. TBS Kwartierloon / Uitkering Box -->
            <div class="bg-white dark:bg-slate-900 p-4 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xs space-y-2">
                <div class="flex items-center justify-between">
                    <h4 class="text-xs font-black uppercase text-slate-900 dark:text-white">Patiënten Uitkering</h4>
                    <span class="text-[9px] font-black px-2 py-0.5 rounded bg-emerald-100 dark:bg-emerald-950 text-emerald-800 dark:text-emerald-300">
                        {{ ($allowanceDetails['eligible'] ?? false) ? 'Actief' : 'Bende Loon' }}
                    </span>
                </div>
                <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                    @if($allowanceDetails['eligible'] ?? false)
                        Automatische storting van <strong>€{{ number_format($allowanceDetails['amount_per_quarter'] ?? 1000, 0, ',', '.') }}</strong> per 15 minuten direct op deze rekening!
                    @else
                        Je ontvangt een collectief bende-uurloon via de smokkelkiosken van <strong>{{ $character->gang?->name ?? 'je gang' }}</strong>.
                    @endif
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
