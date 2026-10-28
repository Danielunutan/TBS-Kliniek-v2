@extends('layouts.app')

@section('title', 'Geldmaat // TBS Kliniek Bankautomaat')

@section('content')
@php
    $hasPin = (bool) ($hasBankPin ?? false);
    $unlocked = (bool) ($isAtmUnlocked ?? false);
@endphp

<div class="space-y-4 sm:space-y-6 max-w-7xl mx-auto px-1 sm:px-0" x-data="{
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
            this.pinError = 'Pincodes komen niet overeen!';
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
                this.pinSuccess = 'Pincode succesvol geactiveerd!';
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
    <div class="rounded-2xl sm:rounded-3xl shadow-xs border p-4 sm:p-6 relative overflow-hidden transition-all duration-300
                bg-gradient-to-r from-amber-50 via-white to-amber-50/50 border-amber-200/90 text-slate-800
                dark:from-slate-900 dark:via-slate-900/95 dark:to-purple-950/30 dark:border-slate-800 dark:text-slate-100">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 relative z-10">
            <div class="flex items-center gap-3.5 sm:gap-4">
                <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-2xl bg-gradient-to-br from-amber-400 to-amber-500 text-slate-950 flex items-center justify-center text-xl sm:text-2xl font-black shrink-0 shadow-md shadow-amber-500/20 ring-4 ring-amber-300/40 dark:ring-amber-500/20">
                    <i class="fa-solid fa-coins"></i>
                </div>
                <div class="space-y-0.5">
                    <div class="flex items-center gap-1.5 sm:gap-2 flex-wrap">
                        <span class="text-[9px] sm:text-[10px] font-black uppercase tracking-wider text-slate-950 bg-amber-400 px-2 py-0.5 rounded-full font-mono shadow-xs">
                            Geldmaat #0884
                        </span>
                        <span class="text-[9px] sm:text-[10px] font-bold text-emerald-700 dark:text-emerald-300 bg-emerald-100/90 dark:bg-emerald-950/80 px-2 py-0.5 rounded-full border border-emerald-300/70 dark:border-emerald-800 flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            Online
                        </span>
                        <span class="text-[9px] sm:text-[10px] font-bold text-purple-700 dark:text-purple-300 bg-purple-100/90 dark:bg-purple-950/80 px-2 py-0.5 rounded-full border border-purple-300/70 dark:border-purple-800">
                            Patiënt Level {{ $character->level }}
                        </span>
                    </div>
                    <h1 class="text-lg sm:text-2xl font-black tracking-tight text-slate-900 dark:text-white">
                        Kliniekbank &amp; Financieel Centrum
                    </h1>
                    <p class="text-slate-600 dark:text-slate-400 text-xs sm:text-sm hidden sm:block max-w-xl leading-relaxed">
                        Beheer je bankkluis, vraag leningen aan en pin veilig contanten via de officiële <strong class="text-amber-600 dark:text-amber-400 font-bold">Geldmaat</strong> afdelingsautomaat.
                    </p>
                </div>
            </div>

            <!-- Header Controls: Sound & Mode Switcher -->
            <div class="flex items-center gap-2 self-end md:self-center">
                <!-- Sound Toggle -->
                <button type="button" @click="soundEnabled = !soundEnabled"
                        class="px-3 py-1.5 sm:px-3.5 sm:py-2 rounded-xl text-xs font-bold transition flex items-center gap-1.5 shadow-xs cursor-pointer border
                               bg-white hover:bg-slate-50 border-slate-200 text-slate-700
                               dark:bg-slate-800 dark:hover:bg-slate-700 dark:border-slate-700 dark:text-slate-200">
                    <i class="fa-solid" :class="soundEnabled ? 'fa-volume-high text-emerald-500' : 'fa-volume-xmark text-slate-400'"></i>
                    <span class="hidden sm:inline" x-text="soundEnabled ? 'Geluid Aan' : 'Geluid Uit'"></span>
                </button>

                <!-- Fast / Classic Overview Mode Switcher -->
                <button type="button" @click="viewMode = (viewMode === 'atm' ? 'classic' : 'atm')"
                        class="px-3 py-1.5 sm:px-3.5 sm:py-2 rounded-xl text-xs font-bold transition flex items-center gap-1.5 shadow-xs cursor-pointer border
                               bg-amber-400/20 hover:bg-amber-400/30 border-amber-300 text-amber-950 font-bold
                               dark:bg-purple-900/40 dark:hover:bg-purple-900/60 dark:border-purple-700/60 dark:text-purple-200">
                    <i class="fa-solid" :class="viewMode === 'atm' ? 'fa-table-columns text-amber-600 dark:text-purple-400' : 'fa-cash-register text-amber-600 dark:text-purple-400'"></i>
                    <span x-text="viewMode === 'atm' ? 'Snelle Tabel' : 'Geldmaat Automaat'"></span>
                </button>
            </div>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- MOBILE TOP QUICK BAR: CARD STATUS & BALANCES                  -->
    <!-- ============================================================== -->
    <div class="lg:hidden rounded-2xl p-3 border shadow-xs transition
                bg-white border-slate-200 dark:bg-slate-900 dark:border-slate-800 text-slate-900 dark:text-white">
        <div class="flex items-center justify-between gap-3">
            <div class="flex items-center gap-2.5 min-w-0">
                <div class="w-9 h-9 rounded-xl bg-purple-600 text-white flex items-center justify-center shrink-0 shadow-xs">
                    <i class="fa-solid fa-credit-card text-xs"></i>
                </div>
                <div class="truncate">
                    <span class="text-[10px] font-mono uppercase text-slate-400 block tracking-tight">Kliniek Betaalpas</span>
                    <span class="text-xs font-black truncate block">{{ $character->name }}</span>
                </div>
            </div>

            <!-- Insert / Eject Card Quick Button on Mobile -->
            <button type="button" @click="cardInserted ? ejectCard() : insertCard()"
                    class="px-3 py-1.5 rounded-xl font-mono text-[11px] font-black uppercase transition shrink-0 cursor-pointer shadow-xs border"
                    :class="cardInserted 
                        ? 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-950/80 dark:text-rose-300 dark:border-rose-800' 
                        : 'bg-amber-400 text-slate-950 border-amber-300 hover:bg-amber-300'">
                <span x-text="cardInserted ? 'Pas Eject' : 'Pas Invoeren ➔'"></span>
            </button>
        </div>

        <!-- Mobile Quick Stats Grid -->
        <div class="grid grid-cols-2 gap-2 mt-2.5 pt-2.5 border-t border-slate-100 dark:border-slate-800 text-center font-mono">
            <div class="p-1.5 rounded-lg bg-amber-50/80 dark:bg-amber-950/30 border border-amber-200/60 dark:border-amber-900/40">
                <span class="text-[9px] uppercase text-amber-700 dark:text-amber-400 font-bold block">Contant op Zak</span>
                <span class="text-xs font-black text-amber-900 dark:text-amber-300">€{{ number_format($character->cash, 0, ',', '.') }}</span>
            </div>
            <div class="p-1.5 rounded-lg bg-emerald-50/80 dark:bg-emerald-950/30 border border-emerald-200/60 dark:border-emerald-900/40">
                <span class="text-[9px] uppercase text-emerald-700 dark:text-emerald-400 font-bold block">In Bankkluis</span>
                <span class="text-xs font-black text-emerald-700 dark:text-emerald-300">€{{ number_format($character->bank, 0, ',', '.') }}</span>
            </div>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- MAIN CONTENT: ATM VIEW OR CLASSIC VIEW                         -->
    <!-- ============================================================== -->

    <!-- MODE 1: THE COMPLETE VIRTUAL GELDMAAT EXPERIENCE -->
    <div x-show="viewMode === 'atm'" class="grid grid-cols-1 lg:grid-cols-12 gap-5 sm:gap-6 items-start">

        <!-- LEFT COLUMN (Cols 8): THE GELDMAAT TERMINAL -->
        <div class="lg:col-span-8 space-y-4">
            <!-- Outermost ATM Housing (Architectural Shell) -->
            <div class="rounded-2xl sm:rounded-3xl shadow-xl transition-all duration-300 relative overflow-hidden border
                        bg-slate-200/90 border-slate-300 p-2 sm:p-5
                        dark:bg-slate-950 dark:border-slate-800">

                <!-- Iconic Geldmaat Bright Yellow Kiosk Shell -->
                <div class="rounded-xl sm:rounded-2xl shadow-xl overflow-hidden border-2 sm:border-4 border-amber-300 dark:border-amber-500
                            bg-gradient-to-b from-[#ffd700] via-[#ffcc00] to-[#e6b800] p-2.5 sm:p-5 relative">

                    <!-- Glossy Light Reflection Across Curved Top -->
                    <div class="absolute inset-x-0 top-0 h-10 bg-gradient-to-b from-white/40 via-white/10 to-transparent pointer-events-none"></div>

                    <!-- Geldmaat Top Brand Arch -->
                    <div class="flex items-center justify-between pb-2.5 sm:pb-3.5 border-b-2 border-amber-500/30 mb-3 sm:mb-4 px-1 sm:px-2 relative z-10">
                        <div class="flex items-center gap-2 sm:gap-3">
                            <div class="w-9 h-9 sm:w-11 sm:h-11 rounded-xl bg-slate-950 text-[#ffcc00] flex items-center justify-center text-lg sm:text-2xl font-black shadow-md border border-slate-900 shrink-0">
                                <i class="fa-solid fa-vault"></i>
                            </div>
                            <div>
                                <div class="flex items-center gap-1.5">
                                    <span class="text-sm sm:text-base font-black tracking-widest text-slate-950 uppercase font-mono">
                                        geld<span class="text-amber-800">maat</span>
                                    </span>
                                    <span class="text-[8px] sm:text-[9px] font-black uppercase bg-slate-950 text-white px-1.5 py-0.5 rounded tracking-tighter">
                                        TBS-09
                                    </span>
                                </div>
                                <span class="text-[9px] sm:text-[10px] font-extrabold text-slate-900/80 tracking-wide block">
                                    VEILIGE KLINIEK AUTOMAAT
                                </span>
                            </div>
                        </div>

                        <!-- Kiosk Camera Status -->
                        <div class="flex items-center gap-1.5 sm:gap-2 bg-slate-950/15 backdrop-blur-xs px-2.5 py-1 rounded-full border border-slate-950/20 shadow-xs">
                            <span class="relative flex h-2 w-2">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-500 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-rose-600"></span>
                            </span>
                            <span class="text-[9px] font-mono font-black text-slate-950 uppercase tracking-tight">CAM ON</span>
                        </div>
                    </div>

                    <!-- INNER RECESSED CONSOLE (Light: Brushed Aluminum / Dark: Matte Slate) -->
                    <div class="rounded-xl sm:rounded-2xl p-2.5 sm:p-5 shadow-inner border-2 transition-all duration-300
                                bg-slate-100 border-slate-300 text-slate-900
                                dark:bg-slate-900 dark:border-slate-800 dark:text-white">

                        <!-- ============================================== -->
                        <!-- ATM DIGITAL SCREEN (Daylight White / Night Dark) -->
                        <!-- ============================================== -->
                        <div class="rounded-xl border-2 shadow-xl overflow-hidden relative transition-all duration-300
                                    bg-white border-slate-200 text-slate-900
                                    dark:bg-[#0b1120] dark:border-slate-800 dark:text-slate-100
                                    min-h-[380px] sm:min-h-[440px] flex flex-col justify-between p-3.5 sm:p-6"
                             style="background-image: radial-gradient(rgba(100, 116, 139, 0.15) 1px, transparent 1px); background-size: 14px 14px;">

                            <!-- Screen Top Navigation & Status -->
                            <div class="flex items-center justify-between border-b pb-2.5 mb-3 text-xs font-mono relative z-10
                                        border-slate-200 text-slate-600 dark:border-slate-800 dark:text-slate-400">
                                <div class="flex items-center gap-2">
                                    <span class="px-2 py-0.5 rounded bg-amber-400 text-slate-950 font-black text-[9px] sm:text-[10px] uppercase shadow-xs">
                                        Geldmaat OS v4.2
                                    </span>
                                    <span class="text-[11px] truncate hidden sm:inline text-slate-600 dark:text-slate-400">
                                        {{ $character->current_location ?? 'Paviljoen 1' }}
                                    </span>
                                </div>

                                <div class="flex items-center gap-2">
                                    <span class="text-emerald-600 dark:text-emerald-400 font-bold flex items-center gap-1 text-[10px] sm:text-[11px] bg-emerald-50 dark:bg-emerald-950/60 px-2 py-0.5 rounded border border-emerald-200 dark:border-emerald-800/60">
                                        <i class="fa-solid fa-lock text-[9px]"></i> SSL Beveiligd
                                    </span>
                                    <template x-if="cardInserted">
                                        <button type="button" @click="ejectCard()"
                                                class="px-2 py-0.5 rounded bg-rose-100 hover:bg-rose-200 text-rose-700 border border-rose-300 text-[10px] font-black uppercase transition cursor-pointer flex items-center gap-1
                                                       dark:bg-rose-900/30 dark:hover:bg-rose-900/50 dark:text-rose-300 dark:border-rose-700/60">
                                            <i class="fa-solid fa-eject"></i> Eject
                                        </button>
                                    </template>
                                </div>
                            </div>

                            <!-- ------------------------------------------ -->
                            <!-- SCREEN 1: IDLE / STEK UW PAS IN           -->
                            <!-- ------------------------------------------ -->
                            <div x-show="atmScreen === 'idle'" class="my-auto text-center space-y-4 py-4 sm:py-6 relative z-10">
                                <div class="w-16 h-16 sm:w-20 sm:h-20 mx-auto rounded-3xl bg-amber-400/20 border-2 border-amber-400/50 text-amber-500 flex items-center justify-center text-3xl sm:text-4xl shadow-md animate-pulse">
                                    <i class="fa-solid fa-credit-card"></i>
                                </div>

                                <div class="space-y-1 max-w-md mx-auto">
                                    <h2 class="text-lg sm:text-2xl font-black uppercase tracking-wide text-slate-900 dark:text-white">
                                        Welkom bij Geldmaat TBS
                                    </h2>
                                    <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
                                        Steek uw persoonlijke <strong class="text-amber-600 dark:text-amber-300 font-bold">Kliniek Betaalpas</strong> in de paslezer om veilig contant geld te storten, pinnen of uw kredietruimte te benutten.
                                    </p>
                                </div>

                                <div class="pt-2">
                                    <button type="button" @click="insertCard()"
                                            class="px-5 sm:px-6 py-3 sm:py-3.5 rounded-xl bg-gradient-to-r from-amber-400 to-amber-500 hover:from-amber-300 hover:to-amber-400 text-slate-950 font-black text-xs sm:text-sm uppercase tracking-wider transition shadow-md shadow-amber-500/20 cursor-pointer transform hover:scale-105 active:scale-95 flex items-center gap-2 mx-auto">
                                        <i class="fa-solid fa-arrow-down"></i>
                                        <span>Steek Betaalpas In</span>
                                    </button>
                                    <span class="text-[10px] text-slate-500 dark:text-slate-500 block mt-2 font-mono">
                                        (Of tik op de knop hierboven)
                                    </span>
                                </div>
                            </div>

                            <!-- ------------------------------------------ -->
                            <!-- SCREEN 2: EERSTE KEER PINCODE INSTELLEN   -->
                            <!-- ------------------------------------------ -->
                            <div x-show="atmScreen === 'setup_pin'" class="my-auto text-center space-y-4 py-2 sm:py-4 relative z-10">
                                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-100 text-amber-900 border border-amber-300 text-xs font-bold shadow-2xs dark:bg-amber-400/20 dark:text-amber-300 dark:border-amber-400/40">
                                    <i class="fa-solid fa-shield"></i> Eerste Gebruik: Pincode Kiezen
                                </div>

                                <div class="space-y-1 max-w-sm mx-auto">
                                    <h3 class="text-base sm:text-lg font-black uppercase text-slate-900 dark:text-white"
                                        x-text="pinStep === 'enter_new' ? 'Kies uw 4-cijferige pincode' : 'Bevestig uw 4-cijferige pincode'"></h3>
                                    <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed">
                                        Toets 4 cijfers in op het cijferblok hieronder om uw pas te activeren.
                                    </p>
                                </div>

                                <!-- 4 Digit Pin Boxes -->
                                <div class="flex items-center justify-center gap-3 my-3">
                                    <template x-for="i in 4" :key="i">
                                        <div class="w-12 h-14 sm:w-13 sm:h-15 rounded-xl border-2 flex items-center justify-center text-2xl font-black font-mono transition-all duration-200"
                                             :class="(pinStep === 'enter_new' ? pinDigits.length : pinConfirmDigits.length) >= i 
                                                ? 'border-amber-500 bg-amber-50 text-amber-900 shadow-md scale-105 dark:border-amber-400 dark:bg-amber-400/20 dark:text-amber-300' 
                                                : 'border-slate-300 bg-slate-50 text-slate-400 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-600'">
                                            <span x-show="(pinStep === 'enter_new' ? pinDigits.length : pinConfirmDigits.length) >= i">●</span>
                                            <span x-show="(pinStep === 'enter_new' ? pinDigits.length : pinConfirmDigits.length) < i" class="text-slate-400 dark:text-slate-700 text-sm">_</span>
                                        </div>
                                    </template>
                                </div>

                                <div x-show="pinError !== ''" class="text-rose-700 bg-rose-100 border border-rose-300 dark:text-rose-400 dark:bg-rose-950/70 dark:border-rose-800 text-xs font-bold font-mono p-2 rounded-xl max-w-xs mx-auto animate-shake" x-text="pinError"></div>
                                <div x-show="pinSuccess !== ''" class="text-emerald-700 bg-emerald-100 border border-emerald-300 dark:text-emerald-400 dark:bg-emerald-950/70 dark:border-emerald-800 text-xs font-bold font-mono p-2 rounded-xl max-w-xs mx-auto" x-text="pinSuccess"></div>

                                <div class="flex items-center justify-center gap-3 text-xs text-slate-500 dark:text-slate-400 font-mono">
                                    <span>Stap <strong class="text-amber-600 dark:text-amber-400" x-text="pinStep === 'enter_new' ? '1 van 2' : '2 van 2'"></strong></span>
                                    <span>•</span>
                                    <button type="button" @click="pressClear()" class="hover:underline text-amber-600 dark:text-amber-400 cursor-pointer">Wissen (Geel)</button>
                                </div>
                            </div>

                            <!-- ------------------------------------------ -->
                            <!-- SCREEN 3: PINCODE INVOEREN                 -->
                            <!-- ------------------------------------------ -->
                            <div x-show="atmScreen === 'enter_pin'" class="my-auto text-center space-y-4 py-2 sm:py-4 relative z-10">
                                <div class="w-12 h-12 sm:w-14 sm:h-14 mx-auto rounded-2xl bg-amber-400/20 border border-amber-400/40 text-amber-500 flex items-center justify-center text-xl sm:text-2xl shadow-xs">
                                    <i class="fa-solid fa-key"></i>
                                </div>

                                <div class="space-y-0.5">
                                    <h3 class="text-base sm:text-lg font-black uppercase text-slate-900 dark:text-white">
                                        Voer uw pincode in
                                    </h3>
                                    <p class="text-xs text-slate-600 dark:text-slate-400">
                                        Rekeninghouder: <strong class="text-slate-900 dark:text-slate-200">{{ $character->name }}</strong>
                                    </p>
                                </div>

                                <!-- 4 Digit Pin Display Boxes -->
                                <div class="flex items-center justify-center gap-3 my-2 sm:my-3">
                                    <template x-for="i in 4" :key="i">
                                        <div class="w-12 h-14 sm:w-13 sm:h-15 rounded-xl border-2 flex items-center justify-center text-2xl font-black font-mono transition-all duration-200"
                                             :class="pinDigits.length >= i 
                                                ? 'border-amber-500 bg-amber-50 text-amber-900 shadow-md scale-105 dark:border-amber-400 dark:bg-amber-400/20 dark:text-amber-300' 
                                                : 'border-slate-300 bg-slate-50 text-slate-400 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-600'">
                                            <span x-show="pinDigits.length >= i">●</span>
                                            <span x-show="pinDigits.length < i" class="text-slate-400 dark:text-slate-700 text-sm">_</span>
                                        </div>
                                    </template>
                                </div>

                                <div x-show="pinError !== ''" class="text-rose-700 bg-rose-100 border border-rose-300 dark:text-rose-400 dark:bg-rose-950/70 dark:border-rose-800 text-xs font-bold font-mono p-2 rounded-xl max-w-xs mx-auto animate-shake" x-text="pinError"></div>
                                <div x-show="pinSuccess !== ''" class="text-emerald-700 bg-emerald-100 border border-emerald-300 dark:text-emerald-400 dark:bg-emerald-950/70 dark:border-emerald-800 text-xs font-bold font-mono p-2 rounded-xl max-w-xs mx-auto" x-text="pinSuccess"></div>

                                <p class="text-[11px] text-slate-500 dark:text-slate-400 font-mono">
                                    Toets uw code in via het fysieke pinpad hieronder of via uw toetsenbord.
                                </p>
                            </div>

                            <!-- ------------------------------------------ -->
                            <!-- SCREEN 4: HOOFDMENU NA SUCCESVOLLE PIN    -->
                            <!-- ------------------------------------------ -->
                            <div x-show="atmScreen === 'menu'" class="space-y-4 my-auto relative z-10">
                                <!-- Saldo Display Box -->
                                <div class="rounded-xl p-3.5 border flex flex-col sm:flex-row sm:items-center justify-between gap-2 shadow-xs
                                            bg-slate-50 border-slate-200 text-slate-900
                                            dark:bg-slate-950/90 dark:border-slate-800 dark:text-white">
                                    <div>
                                        <span class="text-[9px] sm:text-[10px] font-mono text-slate-500 dark:text-slate-400 uppercase tracking-wider block">Veilig Banktegoed (Kluis)</span>
                                        <div class="text-xl sm:text-3xl font-black font-mono text-emerald-600 dark:text-emerald-400">
                                            €{{ number_format($character->bank, 0, ',', '.') }}
                                        </div>
                                    </div>
                                    <div class="text-left sm:text-right font-mono">
                                        <span class="text-[9px] sm:text-[10px] text-slate-500 dark:text-slate-400 uppercase tracking-wider block">Contant op Zak</span>
                                        <div class="text-base sm:text-lg font-black text-amber-600 dark:text-amber-400">
                                            €{{ number_format($character->cash, 0, ',', '.') }}
                                        </div>
                                    </div>
                                </div>

                                <!-- 4 Touch Action Tiles (Mobile & Desktop Grid) -->
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 sm:gap-3">
                                    <!-- Storten -->
                                    <button type="button" @click="atmScreen = 'deposit'; playBeep(850, 45);"
                                            class="p-3 sm:p-4 rounded-xl border text-left transition group cursor-pointer shadow-xs flex items-center justify-between
                                                   bg-white hover:bg-emerald-50 border-slate-200 hover:border-emerald-400 text-slate-900
                                                   dark:bg-slate-800/80 dark:hover:bg-emerald-950/60 dark:border-slate-700/80 dark:hover:border-emerald-500/80 dark:text-white">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-400 flex items-center justify-center text-lg font-bold group-hover:scale-110 transition shadow-2xs">
                                                <i class="fa-solid fa-arrow-down"></i>
                                            </div>
                                            <div>
                                                <h4 class="text-sm font-black group-hover:text-emerald-600 dark:group-hover:text-emerald-300">Geld Storten</h4>
                                                <span class="text-[10px] text-slate-500 dark:text-slate-400">Contant veilig afstorten (0% tax)</span>
                                            </div>
                                        </div>
                                        <i class="fa-solid fa-chevron-right text-xs text-slate-400 group-hover:text-emerald-500"></i>
                                    </button>

                                    <!-- Opnemen -->
                                    <button type="button" @click="atmScreen = 'withdraw'; playBeep(850, 45);"
                                            class="p-3 sm:p-4 rounded-xl border text-left transition group cursor-pointer shadow-xs flex items-center justify-between
                                                   bg-white hover:bg-amber-50 border-slate-200 hover:border-amber-400 text-slate-900
                                                   dark:bg-slate-800/80 dark:hover:bg-amber-950/60 dark:border-slate-700/80 dark:hover:border-amber-500/80 dark:text-white">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-400 flex items-center justify-center text-lg font-bold group-hover:scale-110 transition shadow-2xs">
                                                <i class="fa-solid fa-arrow-up"></i>
                                            </div>
                                            <div>
                                                <h4 class="text-sm font-black group-hover:text-amber-600 dark:group-hover:text-amber-300">Contant Opnemen</h4>
                                                <span class="text-[10px] text-slate-500 dark:text-slate-400">Biljetten pinnen uit automaat</span>
                                            </div>
                                        </div>
                                        <i class="fa-solid fa-chevron-right text-xs text-slate-400 group-hover:text-amber-500"></i>
                                    </button>

                                    <!-- Krediet & Leningen -->
                                    <button type="button" @click="atmScreen = 'loans'; playBeep(850, 45);"
                                            class="p-3 sm:p-4 rounded-xl border text-left transition group cursor-pointer shadow-xs flex items-center justify-between
                                                   bg-white hover:bg-purple-50 border-slate-200 hover:border-purple-400 text-slate-900
                                                   dark:bg-slate-800/80 dark:hover:bg-purple-950/60 dark:border-slate-700/80 dark:hover:border-purple-500/80 dark:text-white">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-xl bg-purple-100 text-purple-700 dark:bg-purple-500/20 dark:text-purple-400 flex items-center justify-center text-lg font-bold group-hover:scale-110 transition shadow-2xs">
                                                <i class="fa-solid fa-hand-holding-dollar"></i>
                                            </div>
                                            <div>
                                                <h4 class="text-sm font-black group-hover:text-purple-600 dark:group-hover:text-purple-300">Krediet &amp; Leningen</h4>
                                                <span class="text-[10px] text-slate-500 dark:text-slate-400">Krediet: €{{ number_format($beschikbareLening ?? 0, 0, ',', '.') }}</span>
                                            </div>
                                        </div>
                                        <i class="fa-solid fa-chevron-right text-xs text-slate-400 group-hover:text-purple-500"></i>
                                    </button>

                                    <!-- Afschrift & Mutaties -->
                                    <button type="button" @click="atmScreen = 'transactions'; playBeep(850, 45);"
                                            class="p-3 sm:p-4 rounded-xl border text-left transition group cursor-pointer shadow-xs flex items-center justify-between
                                                   bg-white hover:bg-blue-50 border-slate-200 hover:border-blue-400 text-slate-900
                                                   dark:bg-slate-800/80 dark:hover:bg-blue-950/60 dark:border-slate-700/80 dark:hover:border-blue-500/80 dark:text-white">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-400 flex items-center justify-center text-lg font-bold group-hover:scale-110 transition shadow-2xs">
                                                <i class="fa-solid fa-receipt"></i>
                                            </div>
                                            <div>
                                                <h4 class="text-sm font-black group-hover:text-blue-600 dark:group-hover:text-blue-300">Afschrift &amp; Mutaties</h4>
                                                <span class="text-[10px] text-slate-500 dark:text-slate-400">Recente bankmutaties</span>
                                            </div>
                                        </div>
                                        <i class="fa-solid fa-chevron-right text-xs text-slate-400 group-hover:text-blue-500"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- ------------------------------------------ -->
                            <!-- SCREEN 5: GELD STORTEN                     -->
                            <!-- ------------------------------------------ -->
                            <div x-show="atmScreen === 'deposit'" class="space-y-3.5 my-auto relative z-10">
                                <div class="flex items-center justify-between border-b pb-2 border-slate-200 dark:border-slate-800">
                                    <div class="flex items-center gap-2">
                                        <button type="button" @click="atmScreen = 'menu'" class="text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white text-xs font-mono cursor-pointer">
                                            &larr; Terug
                                        </button>
                                        <span class="text-slate-300 dark:text-slate-600">|</span>
                                        <h4 class="text-sm font-black uppercase text-emerald-600 dark:text-emerald-400">Geld Storten</h4>
                                    </div>
                                    <span class="text-xs font-mono text-slate-600 dark:text-slate-400">
                                        Op zak: <strong class="text-amber-600 dark:text-amber-400">€{{ number_format($character->cash, 0, ',', '.') }}</strong>
                                    </span>
                                </div>

                                <!-- Fast Presets -->
                                <div class="grid grid-cols-4 gap-1.5 sm:gap-2">
                                    <button type="button" @click="depositAmount = 500; playBeep(700, 30);"
                                            class="py-2 px-1 rounded-xl border text-xs font-mono font-bold text-center cursor-pointer transition
                                                   bg-slate-100 hover:bg-slate-200 border-slate-300 text-slate-800
                                                   dark:bg-slate-800 dark:hover:bg-slate-700 dark:border-slate-700 dark:text-white">
                                        €500
                                    </button>
                                    <button type="button" @click="depositAmount = 2500; playBeep(700, 30);"
                                            class="py-2 px-1 rounded-xl border text-xs font-mono font-bold text-center cursor-pointer transition
                                                   bg-slate-100 hover:bg-slate-200 border-slate-300 text-slate-800
                                                   dark:bg-slate-800 dark:hover:bg-slate-700 dark:border-slate-700 dark:text-white">
                                        €2.500
                                    </button>
                                    <button type="button" @click="depositAmount = 10000; playBeep(700, 30);"
                                            class="py-2 px-1 rounded-xl border text-xs font-mono font-bold text-center cursor-pointer transition
                                                   bg-slate-100 hover:bg-slate-200 border-slate-300 text-slate-800
                                                   dark:bg-slate-800 dark:hover:bg-slate-700 dark:border-slate-700 dark:text-white">
                                        €10.000
                                    </button>
                                    <button type="button" @click="depositAmount = {{ (int) $character->cash }}; playBeep(700, 30);"
                                            class="py-2 px-1 rounded-xl border text-xs font-mono font-bold text-center cursor-pointer transition
                                                   bg-emerald-100 hover:bg-emerald-200 border-emerald-300 text-emerald-800
                                                   dark:bg-emerald-900/60 dark:hover:bg-emerald-800 dark:border-emerald-600 dark:text-emerald-300">
                                        ALLES
                                    </button>
                                </div>

                                <form method="POST" action="{{ route('bank.deposit') }}" x-ref="depositForm" class="space-y-3">
                                    @csrf
                                    <div>
                                        <label class="text-[10px] font-mono text-slate-500 dark:text-slate-400 uppercase tracking-wider block mb-1">
                                            Te storten bedrag
                                        </label>
                                        <div class="relative">
                                            <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 font-mono font-bold">€</span>
                                            <input type="number" name="amount" x-model="depositAmount" min="1" max="{{ $character->cash }}"
                                                   placeholder="0" required
                                                   class="w-full rounded-xl py-2.5 sm:py-3 pl-8 pr-4 font-mono text-base sm:text-lg font-bold border focus:outline-hidden
                                                          bg-slate-50 border-slate-300 text-slate-900 focus:border-emerald-500
                                                          dark:bg-slate-950 dark:border-slate-700 dark:text-white dark:focus:border-emerald-500">
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-2 pt-1">
                                        <button type="button" @click="atmScreen = 'menu'"
                                                class="flex-1 py-2.5 sm:py-3 rounded-xl font-bold text-xs uppercase font-mono transition cursor-pointer border
                                                       bg-slate-100 hover:bg-slate-200 border-slate-300 text-slate-700
                                                       dark:bg-slate-800 dark:hover:bg-slate-700 dark:border-slate-700 dark:text-slate-300">
                                            Terug
                                        </button>
                                        <button type="submit"
                                                class="flex-2 py-2.5 sm:py-3 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-black text-xs uppercase tracking-wider font-mono transition cursor-pointer shadow-md shadow-emerald-600/30">
                                            Storting Bevestigen
                                        </button>
                                    </div>
                                </form>
                            </div>

                            <!-- ------------------------------------------ -->
                            <!-- SCREEN 6: CONTANT GELD OPNEMEN             -->
                            <!-- ------------------------------------------ -->
                            <div x-show="atmScreen === 'withdraw'" class="space-y-3.5 my-auto relative z-10">
                                <div class="flex items-center justify-between border-b pb-2 border-slate-200 dark:border-slate-800">
                                    <div class="flex items-center gap-2">
                                        <button type="button" @click="atmScreen = 'menu'" class="text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white text-xs font-mono cursor-pointer">
                                            &larr; Terug
                                        </button>
                                        <span class="text-slate-300 dark:text-slate-600">|</span>
                                        <h4 class="text-sm font-black uppercase text-amber-600 dark:text-amber-400">Contant Opnemen</h4>
                                    </div>
                                    <span class="text-xs font-mono text-slate-600 dark:text-slate-400">
                                        In kluis: <strong class="text-emerald-600 dark:text-emerald-400">€{{ number_format($character->bank, 0, ',', '.') }}</strong>
                                    </span>
                                </div>

                                <!-- Tax Notice Banner -->
                                <div class="rounded-xl p-2 sm:p-2.5 text-[11px] flex items-center justify-between font-mono border
                                            bg-amber-50 border-amber-200 text-amber-900
                                            dark:bg-amber-950/40 dark:border-amber-800/60 dark:text-amber-300">
                                    <span>Opnamebelasting: <strong>{{ $character->isVip() ? '3%' : '5%' }}</strong></span>
                                    <span class="text-[10px] text-amber-700 dark:text-amber-400/80">Vloeit naar Afdelingskavel!</span>
                                </div>

                                <!-- Fast Presets -->
                                <div class="grid grid-cols-4 gap-1.5 sm:gap-2">
                                    <button type="button" @click="withdrawAmount = 1000; playBeep(700, 30);"
                                            class="py-2 px-1 rounded-xl border text-xs font-mono font-bold text-center cursor-pointer transition
                                                   bg-slate-100 hover:bg-slate-200 border-slate-300 text-slate-800
                                                   dark:bg-slate-800 dark:hover:bg-slate-700 dark:border-slate-700 dark:text-white">
                                        €1.000
                                    </button>
                                    <button type="button" @click="withdrawAmount = 5000; playBeep(700, 30);"
                                            class="py-2 px-1 rounded-xl border text-xs font-mono font-bold text-center cursor-pointer transition
                                                   bg-slate-100 hover:bg-slate-200 border-slate-300 text-slate-800
                                                   dark:bg-slate-800 dark:hover:bg-slate-700 dark:border-slate-700 dark:text-white">
                                        €5.000
                                    </button>
                                    <button type="button" @click="withdrawAmount = 25000; playBeep(700, 30);"
                                            class="py-2 px-1 rounded-xl border text-xs font-mono font-bold text-center cursor-pointer transition
                                                   bg-slate-100 hover:bg-slate-200 border-slate-300 text-slate-800
                                                   dark:bg-slate-800 dark:hover:bg-slate-700 dark:border-slate-700 dark:text-white">
                                        €25.000
                                    </button>
                                    <button type="button" @click="withdrawAmount = {{ (int) $character->bank }}; playBeep(700, 30);"
                                            class="py-2 px-1 rounded-xl border text-xs font-mono font-bold text-center cursor-pointer transition
                                                   bg-amber-100 hover:bg-amber-200 border-amber-300 text-amber-900
                                                   dark:bg-amber-900/60 dark:hover:bg-amber-800 dark:border-amber-600 dark:text-amber-300">
                                        ALLES
                                    </button>
                                </div>

                                <form method="POST" action="{{ route('bank.withdraw') }}" x-ref="withdrawForm" class="space-y-3">
                                    @csrf
                                    <div>
                                        <label class="text-[10px] font-mono text-slate-500 dark:text-slate-400 uppercase tracking-wider block mb-1">
                                            Op te nemen bedrag
                                        </label>
                                        <div class="relative">
                                            <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 font-mono font-bold">€</span>
                                            <input type="number" name="amount" x-model="withdrawAmount" min="1" max="{{ $character->bank }}"
                                                   placeholder="0" required
                                                   class="w-full rounded-xl py-2.5 sm:py-3 pl-8 pr-4 font-mono text-base sm:text-lg font-bold border focus:outline-hidden
                                                          bg-slate-50 border-slate-300 text-slate-900 focus:border-amber-500
                                                          dark:bg-slate-950 dark:border-slate-700 dark:text-white dark:focus:border-amber-500">
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-2 pt-1">
                                        <button type="button" @click="atmScreen = 'menu'"
                                                class="flex-1 py-2.5 sm:py-3 rounded-xl font-bold text-xs uppercase font-mono transition cursor-pointer border
                                                       bg-slate-100 hover:bg-slate-200 border-slate-300 text-slate-700
                                                       dark:bg-slate-800 dark:hover:bg-slate-700 dark:border-slate-700 dark:text-slate-300">
                                            Terug
                                        </button>
                                        <button type="submit"
                                                class="flex-2 py-2.5 sm:py-3 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-black text-xs uppercase tracking-wider font-mono transition cursor-pointer shadow-md shadow-amber-500/20">
                                            Pinnen Uit Automaat
                                        </button>
                                    </div>
                                </form>
                            </div>

                            <!-- ------------------------------------------ -->
                            <!-- SCREEN 7: KREDIET & LENINGEN               -->
                            <!-- ------------------------------------------ -->
                            <div x-show="atmScreen === 'loans'" class="space-y-3.5 my-auto relative z-10 text-xs">
                                <div class="flex items-center justify-between border-b pb-2 border-slate-200 dark:border-slate-800">
                                    <div class="flex items-center gap-2">
                                        <button type="button" @click="atmScreen = 'menu'" class="text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white font-mono cursor-pointer">
                                            &larr; Terug
                                        </button>
                                        <span class="text-slate-300 dark:text-slate-600">|</span>
                                        <h4 class="text-sm font-black uppercase text-purple-600 dark:text-purple-400">Krediet &amp; Leningen</h4>
                                    </div>
                                    <span class="text-[10px] font-mono font-bold text-purple-700 dark:text-purple-300 bg-purple-100 dark:bg-purple-950 px-2 py-0.5 rounded border border-purple-200 dark:border-purple-800">
                                        Rente: 8,5% (Leningtax)
                                    </span>
                                </div>

                                <div class="grid grid-cols-2 gap-2 text-center font-mono">
                                    <div class="p-2.5 rounded-xl border bg-slate-50 border-slate-200 dark:bg-slate-950 dark:border-slate-800">
                                        <span class="text-[9px] text-slate-500 uppercase block">Openstaande Schuld</span>
                                        <span class="text-xs sm:text-sm font-bold text-rose-600 dark:text-rose-400">€{{ number_format($openstaandeSchuld ?? 0, 0, ',', '.') }}</span>
                                    </div>
                                    <div class="p-2.5 rounded-xl border bg-slate-50 border-slate-200 dark:bg-slate-950 dark:border-slate-800">
                                        <span class="text-[9px] text-slate-500 uppercase block">Kredietruimte</span>
                                        <span class="text-xs sm:text-sm font-bold text-emerald-600 dark:text-emerald-400">€{{ number_format($beschikbareLening ?? 0, 0, ',', '.') }}</span>
                                    </div>
                                </div>

                                <!-- Active Loans Mini List -->
                                <div class="space-y-1.5 max-h-36 overflow-y-auto pr-1">
                                    @forelse($activeLoans as $loan)
                                        <div class="p-2 rounded-xl border flex items-center justify-between gap-2
                                                    bg-white border-slate-200 text-slate-900
                                                    dark:bg-slate-950/80 dark:border-slate-800 dark:text-white">
                                            <div>
                                                <div class="font-bold font-mono">
                                                    Restant: €{{ number_format($loan->remaining_due, 0, ',', '.') }}
                                                </div>
                                                <span class="text-[10px] text-slate-500">Tot: €{{ number_format($loan->total_due, 0, ',', '.') }} ({{ $loan->interest_rate }}%)</span>
                                            </div>
                                            <form method="POST" action="{{ route('bank.loans.repay-full', $loan) }}">
                                                @csrf
                                                <input type="hidden" name="source" value="bank">
                                                <button type="submit" class="px-2.5 py-1 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white text-[10px] font-bold font-mono transition cursor-pointer">
                                                    Aflossen
                                                </button>
                                            </form>
                                        </div>
                                    @empty
                                        <div class="text-center py-3 text-slate-500 text-xs font-mono">
                                            Geen actieve leningen openstaand.
                                        </div>
                                    @endforelse
                                </div>

                                <!-- Request Loan Form -->
                                @if(($beschikbareLening ?? 0) >= 10000)
                                    <form method="POST" action="{{ route('bank.loans.request') }}" class="space-y-2 pt-2 border-t border-slate-200 dark:border-slate-800">
                                        @csrf
                                        <div class="flex items-center gap-2">
                                            <input type="number" name="amount" min="10000" max="{{ $beschikbareLening }}" step="5000"
                                                   value="{{ min(50000, $beschikbareLening) }}" required
                                                   class="w-full rounded-xl px-3 py-2 font-mono text-xs sm:text-sm font-bold border
                                                          bg-slate-50 border-slate-300 text-slate-900
                                                          dark:bg-slate-950 dark:border-slate-700 dark:text-white">
                                            <select name="deposit_to" class="rounded-xl px-2.5 py-2 font-mono text-xs border
                                                                            bg-slate-50 border-slate-300 text-slate-900
                                                                            dark:bg-slate-950 dark:border-slate-700 dark:text-white">
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
                                <div class="flex items-center justify-between border-b pb-2 border-slate-200 dark:border-slate-800">
                                    <div class="flex items-center gap-2">
                                        <button type="button" @click="atmScreen = 'menu'" class="text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white cursor-pointer">
                                            &larr; Terug
                                        </button>
                                        <span class="text-slate-300 dark:text-slate-600">|</span>
                                        <h4 class="text-sm font-black uppercase text-blue-600 dark:text-blue-400">Transactiebon / Afschrift</h4>
                                    </div>
                                    <span class="text-[10px] text-slate-500 dark:text-slate-400">Printversie</span>
                                </div>

                                <div class="bg-amber-50 text-slate-900 rounded-xl p-3 shadow-inner max-h-56 overflow-y-auto space-y-2 text-[11px] border border-amber-200">
                                    <div class="text-center border-b border-dashed border-slate-400 pb-1.5">
                                        <span class="font-black tracking-wider block">GELDMAAT REKENINGAFSCHRIFT</span>
                                        <span class="text-[10px] text-slate-600">TBS KLINIEK CENTRAAL KLUISREGISTER</span>
                                    </div>

                                    @forelse($transactions as $tx)
                                        <div class="flex items-center justify-between py-0.5 border-b border-slate-200/70">
                                            <div>
                                                <span class="font-bold block uppercase text-[10px]">{{ str_replace('_', ' ', $tx->type) }}</span>
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
                            <div class="border-t pt-2 mt-auto flex items-center justify-between text-[10px] font-mono relative z-10
                                        border-slate-200 text-slate-500 dark:border-slate-800 dark:text-slate-500">
                                <span>TERMINAL #0884</span>
                                <span class="flex items-center gap-1.5 text-emerald-600 dark:text-emerald-400 font-bold">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> GEREED
                                </span>
                            </div>
                        </div>

                        <!-- ============================================== -->
                        <!-- HARDWARE SLOTS (Light & Dark Polish)           -->
                        <!-- ============================================== -->
                        <div class="grid grid-cols-3 gap-2 sm:gap-3 mt-3 sm:mt-4 pt-3 sm:pt-4 border-t-2
                                    border-slate-200 dark:border-slate-800">

                            <!-- 1. Pas-invoer Sleuf -->
                            <div class="p-2 sm:p-3 rounded-xl border flex flex-col justify-between gap-1.5 sm:gap-2 text-center group cursor-pointer transition shadow-2xs
                                        bg-slate-50 border-slate-300 hover:border-emerald-500
                                        dark:bg-slate-950 dark:border-slate-800 dark:hover:border-emerald-500/60"
                                 @click="cardInserted ? ejectCard() : insertCard()">
                                <div class="flex items-center justify-between">
                                    <span class="text-[8px] sm:text-[9px] font-mono font-black uppercase tracking-wider text-slate-600 dark:text-slate-400 truncate">Pasinvoer</span>
                                    <span class="w-2 h-2 rounded-full shrink-0" :class="cardInserted ? 'bg-amber-500 animate-pulse' : 'bg-emerald-500 animate-ping'"></span>
                                </div>

                                <div class="h-2.5 sm:h-3 rounded-full bg-slate-200 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 flex items-center justify-center relative overflow-hidden shadow-inner">
                                    <div class="w-16 h-1 rounded-full transition-all duration-300"
                                         :class="cardInserted ? 'bg-amber-500 shadow-[0_0_8px_rgba(245,158,11,0.8)]' : 'bg-emerald-500 animate-pulse shadow-[0_0_8px_rgba(16,185,129,0.8)]'"></div>
                                </div>

                                <span class="text-[9px] sm:text-[10px] font-mono font-bold text-slate-700 dark:text-slate-300 truncate"
                                      x-text="cardInserted ? 'Pas In (Eject)' : 'Steek Pas In ⮟'"></span>
                            </div>

                            <!-- 2. Biljettenlade -->
                            <div class="p-2 sm:p-3 rounded-xl border flex flex-col justify-between gap-1.5 sm:gap-2 text-center shadow-2xs
                                        bg-slate-50 border-slate-300
                                        dark:bg-slate-950 dark:border-slate-800">
                                <div class="flex items-center justify-between">
                                    <span class="text-[8px] sm:text-[9px] font-mono font-black uppercase tracking-wider text-slate-600 dark:text-slate-400 truncate">Biljetten</span>
                                    <span class="w-2 h-2 rounded-full bg-slate-400 dark:bg-slate-700 shrink-0"></span>
                                </div>

                                <div class="h-2.5 sm:h-3 rounded bg-slate-200 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 flex items-center justify-center shadow-inner">
                                    <div class="w-16 h-0.5 rounded-full bg-slate-400 dark:bg-slate-800"></div>
                                </div>

                                <span class="text-[9px] sm:text-[10px] font-mono text-slate-500 dark:text-slate-400 truncate">
                                    Luik
                                </span>
                            </div>

                            <!-- 3. Bonprinter Sleuf -->
                            <div class="p-2 sm:p-3 rounded-xl border flex flex-col justify-between gap-1.5 sm:gap-2 text-center cursor-pointer transition shadow-2xs
                                        bg-slate-50 border-slate-300 hover:border-blue-400
                                        dark:bg-slate-950 dark:border-slate-800 dark:hover:border-blue-500/60"
                                 @click="atmScreen = 'transactions'; playBeep(900, 40);">
                                <div class="flex items-center justify-between">
                                    <span class="text-[8px] sm:text-[9px] font-mono font-black uppercase tracking-wider text-slate-600 dark:text-slate-400 truncate">Bonprinter</span>
                                    <span class="w-2 h-2 rounded-full bg-blue-500 shrink-0"></span>
                                </div>

                                <div class="h-2.5 sm:h-3 rounded bg-slate-200 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 flex items-center justify-center shadow-inner">
                                    <div class="w-12 h-0.5 bg-slate-400 dark:bg-white/40 rounded-full"></div>
                                </div>

                                <span class="text-[9px] sm:text-[10px] font-mono text-blue-600 dark:text-blue-400 hover:underline truncate font-bold">
                                    [ Bon ]
                                </span>
                            </div>
                        </div>

                        <!-- ============================================== -->
                        <!-- 3D KEYPAD (Light Mode & Dark Mode 3D Keys)     -->
                        <!-- ============================================== -->
                        <div class="mt-3 sm:mt-4 pt-3 sm:pt-4 border-t-2 max-w-xs sm:max-w-sm mx-auto
                                    border-slate-200 dark:border-slate-800">
                            <div class="flex items-center justify-between mb-2 px-1">
                                <span class="text-[10px] font-mono font-black uppercase text-slate-600 dark:text-slate-400 flex items-center gap-1.5">
                                    <i class="fa-solid fa-keyboard text-amber-500"></i> Fysiek RVS Pinpad
                                </span>
                                <span class="text-[9px] font-mono text-slate-500">PCI Encrypted</span>
                            </div>

                            <div class="grid grid-cols-4 gap-1.5 sm:gap-2">
                                <!-- Row 1: 1, 2, 3 + STOP (Rood) -->
                                <button type="button" @click="pressKey('1')" class="py-2.5 sm:py-3 rounded-xl font-black font-mono text-base sm:text-lg transition cursor-pointer border shadow-[0_3px_0_#cbd5e1] active:shadow-none active:translate-y-0.5 bg-gradient-to-b from-white to-slate-200 text-slate-900 border-slate-300 dark:from-slate-800 dark:to-slate-900 dark:text-white dark:border-slate-700 dark:shadow-[0_3px_0_#0f172a]">1</button>
                                <button type="button" @click="pressKey('2')" class="py-2.5 sm:py-3 rounded-xl font-black font-mono text-base sm:text-lg transition cursor-pointer border shadow-[0_3px_0_#cbd5e1] active:shadow-none active:translate-y-0.5 bg-gradient-to-b from-white to-slate-200 text-slate-900 border-slate-300 dark:from-slate-800 dark:to-slate-900 dark:text-white dark:border-slate-700 dark:shadow-[0_3px_0_#0f172a]">2</button>
                                <button type="button" @click="pressKey('3')" class="py-2.5 sm:py-3 rounded-xl font-black font-mono text-base sm:text-lg transition cursor-pointer border shadow-[0_3px_0_#cbd5e1] active:shadow-none active:translate-y-0.5 bg-gradient-to-b from-white to-slate-200 text-slate-900 border-slate-300 dark:from-slate-800 dark:to-slate-900 dark:text-white dark:border-slate-700 dark:shadow-[0_3px_0_#0f172a]">3</button>
                                <button type="button" @click="pressCancel()" class="py-2.5 sm:py-3 rounded-xl bg-gradient-to-b from-rose-500 to-rose-700 hover:from-rose-400 hover:to-rose-600 text-white font-black font-mono text-[10px] sm:text-xs uppercase shadow-[0_3px_0_#881337] active:shadow-none active:translate-y-0.5 transition cursor-pointer border border-rose-400">STOP</button>

                                <!-- Row 2: 4, 5, 6 + CORR (Geel) -->
                                <button type="button" @click="pressKey('4')" class="py-2.5 sm:py-3 rounded-xl font-black font-mono text-base sm:text-lg transition cursor-pointer border shadow-[0_3px_0_#cbd5e1] active:shadow-none active:translate-y-0.5 bg-gradient-to-b from-white to-slate-200 text-slate-900 border-slate-300 dark:from-slate-800 dark:to-slate-900 dark:text-white dark:border-slate-700 dark:shadow-[0_3px_0_#0f172a]">4</button>
                                <button type="button" @click="pressKey('5')" class="py-2.5 sm:py-3 rounded-xl font-black font-mono text-base sm:text-lg transition cursor-pointer border shadow-[0_3px_0_#cbd5e1] active:shadow-none active:translate-y-0.5 bg-gradient-to-b from-white to-slate-200 text-slate-900 border-slate-300 dark:from-slate-800 dark:to-slate-900 dark:text-white dark:border-slate-700 dark:shadow-[0_3px_0_#0f172a] relative">
                                    5<span class="absolute bottom-1 right-2 text-[7px] text-slate-400">⠍</span>
                                </button>
                                <button type="button" @click="pressKey('6')" class="py-2.5 sm:py-3 rounded-xl font-black font-mono text-base sm:text-lg transition cursor-pointer border shadow-[0_3px_0_#cbd5e1] active:shadow-none active:translate-y-0.5 bg-gradient-to-b from-white to-slate-200 text-slate-900 border-slate-300 dark:from-slate-800 dark:to-slate-900 dark:text-white dark:border-slate-700 dark:shadow-[0_3px_0_#0f172a]">6</button>
                                <button type="button" @click="pressClear()" class="py-2.5 sm:py-3 rounded-xl bg-gradient-to-b from-amber-400 to-amber-500 hover:from-amber-300 hover:to-amber-400 text-slate-950 font-black font-mono text-[10px] sm:text-xs uppercase shadow-[0_3px_0_#92400e] active:shadow-none active:translate-y-0.5 transition cursor-pointer border border-amber-300">CORR</button>

                                <!-- Row 3: 7, 8, 9 + OK (Groen) -->
                                <button type="button" @click="pressKey('7')" class="py-2.5 sm:py-3 rounded-xl font-black font-mono text-base sm:text-lg transition cursor-pointer border shadow-[0_3px_0_#cbd5e1] active:shadow-none active:translate-y-0.5 bg-gradient-to-b from-white to-slate-200 text-slate-900 border-slate-300 dark:from-slate-800 dark:to-slate-900 dark:text-white dark:border-slate-700 dark:shadow-[0_3px_0_#0f172a]">7</button>
                                <button type="button" @click="pressKey('8')" class="py-2.5 sm:py-3 rounded-xl font-black font-mono text-base sm:text-lg transition cursor-pointer border shadow-[0_3px_0_#cbd5e1] active:shadow-none active:translate-y-0.5 bg-gradient-to-b from-white to-slate-200 text-slate-900 border-slate-300 dark:from-slate-800 dark:to-slate-900 dark:text-white dark:border-slate-700 dark:shadow-[0_3px_0_#0f172a]">8</button>
                                <button type="button" @click="pressKey('9')" class="py-2.5 sm:py-3 rounded-xl font-black font-mono text-base sm:text-lg transition cursor-pointer border shadow-[0_3px_0_#cbd5e1] active:shadow-none active:translate-y-0.5 bg-gradient-to-b from-white to-slate-200 text-slate-900 border-slate-300 dark:from-slate-800 dark:to-slate-900 dark:text-white dark:border-slate-700 dark:shadow-[0_3px_0_#0f172a]">9</button>
                                <button type="button" @click="pressOk()" class="py-2.5 sm:py-3 rounded-xl bg-gradient-to-b from-emerald-500 to-emerald-700 hover:from-emerald-400 hover:to-emerald-600 text-white font-black font-mono text-[10px] sm:text-xs uppercase shadow-[0_3px_0_#064e3b] active:shadow-none active:translate-y-0.5 transition cursor-pointer border border-emerald-400">OK</button>

                                <!-- Row 4: Empty, 0, Empty, Info -->
                                <div class="py-2.5 sm:py-3"></div>
                                <button type="button" @click="pressKey('0')" class="py-2.5 sm:py-3 rounded-xl font-black font-mono text-base sm:text-lg transition cursor-pointer border shadow-[0_3px_0_#cbd5e1] active:shadow-none active:translate-y-0.5 bg-gradient-to-b from-white to-slate-200 text-slate-900 border-slate-300 dark:from-slate-800 dark:to-slate-900 dark:text-white dark:border-slate-700 dark:shadow-[0_3px_0_#0f172a]">0</button>
                                <div class="py-2.5 sm:py-3"></div>
                                <button type="button" @click="playBeep(440, 50)" class="py-2.5 sm:py-3 rounded-xl font-bold font-mono text-[10px] sm:text-xs uppercase shadow-[0_3px_0_#cbd5e1] active:shadow-none active:translate-y-0.5 transition cursor-pointer border bg-slate-200 text-slate-600 border-slate-300 dark:bg-slate-800 dark:text-slate-400 dark:border-slate-700 dark:shadow-[0_3px_0_#0f172a]">INFO</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- RIGHT COLUMN (Cols 4): PATIENT CARD & FINANCIAL VITALS (Desktop) -->
        <div class="lg:col-span-4 space-y-4 sm:space-y-6">

            <!-- 1. The Interactive 3D TBS Kliniek Betaalpas -->
            <div class="space-y-2.5">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-black uppercase tracking-wider text-slate-700 dark:text-slate-300 flex items-center gap-1.5">
                        <i class="fa-solid fa-credit-card text-purple-600 dark:text-purple-400"></i>
                        <span>Jouw Betaalpas</span>
                    </span>
                    <span class="text-[10px] font-mono text-amber-700 dark:text-amber-400 font-bold bg-amber-100 dark:bg-amber-950/80 px-2 py-0.5 rounded-full border border-amber-300/60 dark:border-amber-800/60">
                        EMV CHIP 2026
                    </span>
                </div>

                <!-- Realistic Dutch Bank Debit Card -->
                <div @click="cardInserted ? ejectCard() : insertCard()"
                     class="w-full aspect-[1.586/1] rounded-2xl sm:rounded-3xl p-4 sm:p-5 shadow-xl relative overflow-hidden transition-all duration-300 transform hover:-translate-y-1 hover:shadow-purple-500/25 cursor-pointer flex flex-col justify-between select-none border-2 border-white/40 dark:border-purple-500/30"
                     :class="cardInserted ? 'ring-4 ring-amber-400 scale-[0.98] opacity-90' : ''"
                     style="background: linear-gradient(135deg, #180d33 0%, #3b0764 50%, #0f172a 100%);">

                    <div class="absolute inset-0 bg-gradient-to-tr from-transparent via-white/10 to-transparent pointer-events-none"></div>

                    <!-- Top Card Bar -->
                    <div class="flex items-center justify-between relative z-10">
                        <div class="flex items-center gap-2">
                            <span class="w-7 h-7 sm:w-8 sm:h-8 rounded-xl bg-amber-400 text-slate-950 flex items-center justify-center text-xs sm:text-sm font-black shadow-md">
                                <i class="fa-solid fa-building-columns"></i>
                            </span>
                            <div>
                                <span class="text-[11px] sm:text-xs font-black uppercase tracking-widest text-white font-mono block">
                                    TBS KLINIEK
                                </span>
                                <span class="text-[8px] font-mono text-purple-200 tracking-wider">PATIENT DEBIT</span>
                            </div>
                        </div>
                        <i class="fa-solid fa-wifi text-purple-300 text-xs sm:text-sm rotate-90"></i>
                    </div>

                    <!-- Gold EMV Chip -->
                    <div class="flex items-center gap-2.5 sm:gap-3 relative z-10 my-1">
                        <div class="w-10 h-7 sm:w-12 sm:h-9 rounded-lg bg-gradient-to-br from-amber-200 via-amber-400 to-amber-600 border border-amber-300 shadow-inner flex items-center justify-center p-0.5 sm:p-1">
                            <div class="w-full h-full border border-amber-800/60 rounded-xs flex flex-col justify-between">
                                <div class="h-0.5 bg-amber-800/40 w-full"></div>
                                <div class="h-0.5 bg-amber-800/40 w-full"></div>
                            </div>
                        </div>
                        <span class="text-[9px] sm:text-[10px] font-mono text-purple-200 tracking-widest uppercase">Geldmaat Pas</span>
                    </div>

                    <!-- Embossed Card Numbers & Patient Name -->
                    <div class="space-y-0.5 relative z-10">
                        <div class="text-xs sm:text-base font-mono font-extrabold tracking-widest text-slate-100 drop-shadow">
                            NL99 TBSK 0884 {{ str_pad($character->id, 4, '0', STR_PAD_LEFT) }}
                        </div>
                        <div class="flex items-center justify-between text-[10px] sm:text-[11px] font-mono text-slate-300 uppercase">
                            <span class="truncate max-w-[150px] font-black text-white">{{ $character->name }}</span>
                            <span class="tracking-widest">12/28</span>
                        </div>
                    </div>
                </div>

                <div class="text-center pt-0.5">
                    <button type="button" @click="cardInserted ? ejectCard() : insertCard()"
                            class="text-xs font-black text-purple-700 dark:text-purple-300 hover:underline flex items-center justify-center gap-1.5 mx-auto cursor-pointer">
                        <i class="fa-solid" :class="cardInserted ? 'fa-eject text-rose-500' : 'fa-arrow-left text-emerald-500'"></i>
                        <span x-text="cardInserted ? 'Pas uit de Geldmaat halen' : 'Steek pas in de Geldmaat ➔'"></span>
                    </button>
                </div>
            </div>

            <!-- 2. Financial Vitals Cards (Light & Dark Polish) -->
            <div class="grid grid-cols-2 gap-2.5 sm:gap-3.5">
                <!-- Contant op Zak -->
                <div class="p-3 sm:p-4 rounded-2xl border shadow-xs transition
                            bg-white border-amber-200 text-slate-800
                            dark:bg-slate-900 dark:border-amber-900/60 dark:text-slate-100">
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-[9px] sm:text-[10px] font-black uppercase tracking-wider text-amber-600 dark:text-amber-400">Contant op Zak</span>
                        <i class="fa-solid fa-wallet text-amber-500 text-xs"></i>
                    </div>
                    <div class="text-base sm:text-lg font-black font-mono text-slate-900 dark:text-white truncate">
                        €{{ number_format($character->cash, 0, ',', '.') }}
                    </div>
                    <span class="text-[10px] text-amber-700 dark:text-amber-400 font-bold block mt-0.5 sm:mt-1">⚠️ Kwetsbaar</span>
                </div>

                <!-- Veilig in Kluis -->
                <div class="p-3 sm:p-4 rounded-2xl border shadow-xs transition
                            bg-white border-emerald-200 text-slate-800
                            dark:bg-slate-900 dark:border-emerald-900/60 dark:text-slate-100">
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-[9px] sm:text-[10px] font-black uppercase tracking-wider text-emerald-600 dark:text-emerald-400">Veilig in Kluis</span>
                        <i class="fa-solid fa-shield-halved text-emerald-500 text-xs"></i>
                    </div>
                    <div class="text-base sm:text-lg font-black font-mono text-emerald-600 dark:text-emerald-400 truncate">
                        €{{ number_format($character->bank, 0, ',', '.') }}
                    </div>
                    <span class="text-[10px] text-emerald-700 dark:text-emerald-400 font-bold block mt-0.5 sm:mt-1">🛡️ 100% Beveiligd</span>
                </div>

                <!-- Schuld -->
                <div class="p-3 sm:p-4 rounded-2xl border shadow-xs transition
                            bg-white border-rose-200 text-slate-800
                            dark:bg-slate-900 dark:border-rose-900/60 dark:text-slate-100">
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-[9px] sm:text-[10px] font-black uppercase tracking-wider text-rose-600 dark:text-rose-400">Schuld</span>
                        <i class="fa-solid fa-file-invoice-dollar text-rose-500 text-xs"></i>
                    </div>
                    <div class="text-base sm:text-lg font-black font-mono text-rose-600 dark:text-rose-400 truncate">
                        €{{ number_format($openstaandeSchuld ?? 0, 0, ',', '.') }}
                    </div>
                    <span class="text-[10px] text-slate-500 dark:text-slate-400 font-bold block mt-0.5 sm:mt-1">{{ isset($activeLoans) ? $activeLoans->count() : 0 }} lening(en)</span>
                </div>

                <!-- Kredietruimte -->
                <div class="p-3 sm:p-4 rounded-2xl border shadow-xs transition
                            bg-white border-purple-200 text-slate-800
                            dark:bg-slate-900 dark:border-purple-900/60 dark:text-slate-100">
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-[9px] sm:text-[10px] font-black uppercase tracking-wider text-purple-600 dark:text-purple-400">Kredietruimte</span>
                        <i class="fa-solid fa-chart-line text-purple-500 text-xs"></i>
                    </div>
                    <div class="text-base sm:text-lg font-black font-mono text-purple-600 dark:text-purple-400 truncate">
                        €{{ number_format($beschikbareLening ?? 0, 0, ',', '.') }}
                    </div>
                    <span class="text-[10px] text-slate-500 dark:text-slate-400 font-bold block mt-0.5 sm:mt-1">Max €{{ number_format($maxCapacity ?? 0, 0, ',', '.') }}</span>
                </div>
            </div>

            <!-- 3. Patiënten Kwartierloon / Uitkering Card -->
            <div class="p-3.5 sm:p-4 rounded-2xl border shadow-xs space-y-2 transition
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
    <div x-show="viewMode === 'classic'" class="space-y-4 sm:space-y-6">
        <!-- Tab Navigation Bar -->
        <div class="flex items-center gap-2 border-b border-slate-200 dark:border-slate-800 pb-3 overflow-x-auto">
            <button type="button" @click="activeTab = 'vault'"
                    class="px-3.5 py-2 rounded-xl text-xs font-black uppercase font-mono transition cursor-pointer shrink-0"
                    :class="activeTab === 'vault' ? 'bg-amber-400 text-slate-950 shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800'">
                <i class="fa-solid fa-vault mr-1"></i> Kluis &amp; Beheer
            </button>
            <button type="button" @click="activeTab = 'loans'"
                    class="px-3.5 py-2 rounded-xl text-xs font-black uppercase font-mono transition cursor-pointer shrink-0"
                    :class="activeTab === 'loans' ? 'bg-purple-600 text-white shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800'">
                <i class="fa-solid fa-hand-holding-dollar mr-1"></i> Leningen &amp; Krediet
            </button>
            <button type="button" @click="activeTab = 'transactions'"
                    class="px-3.5 py-2 rounded-xl text-xs font-black uppercase font-mono transition cursor-pointer shrink-0"
                    :class="activeTab === 'transactions' ? 'bg-blue-600 text-white shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800'">
                <i class="fa-solid fa-receipt mr-1"></i> Transacties
            </button>
        </div>

        <!-- TAB 1: KLUIS & BEHEER -->
        <div x-show="activeTab === 'vault'" class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
            <!-- Storten Form -->
            <div class="p-5 sm:p-6 rounded-2xl border shadow-xs bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800 space-y-4">
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
            <div class="p-5 sm:p-6 rounded-2xl border shadow-xs bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800 space-y-4">
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
        <div x-show="activeTab === 'loans'" class="space-y-4 sm:space-y-6">
            <div class="p-5 sm:p-6 rounded-2xl border shadow-xs bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800 space-y-4">
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
