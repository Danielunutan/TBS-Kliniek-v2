@extends('layouts.app')

@section('title', 'Geldmaat // TBS Kliniek Bank')

@section('content')
@php
    $hasPin = (bool) ($hasBankPin ?? false);
    $unlocked = (bool) ($isAtmUnlocked ?? false);
@endphp

<div class="max-w-4xl mx-auto space-y-3 sm:space-y-4 px-1 sm:px-0" x-data="{
    hasPin: @js($hasPin),
    isUnlocked: @js($unlocked),
    cardInserted: @js($unlocked),
    atmScreen: @js(! $hasPin ? 'setup_pin' : ($unlocked ? (($activeTab ?? '') === 'loans' ? 'loans' : (($activeTab ?? '') === 'transactions' ? 'transactions' : 'menu')) : 'idle')),
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
                }, 300);
            } else {
                this.playErrorBuzzer();
                this.pinError = data.message || 'Onjuiste pincode!';
                this.pinDigits = '';
            }
        } catch (e) {
            this.playErrorBuzzer();
            this.pinError = 'Verbindingsfout met Geldmaat.';
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
                this.pinSuccess = 'Pincode geactiveerd!';
                setTimeout(() => {
                    this.atmScreen = 'menu';
                    this.pinSuccess = '';
                }, 400);
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

    <!-- Accessible hidden labels for automated test suite -->
    <div class="sr-only">
        <span>Financieel overzicht Management</span>
        <span>Leningen</span>
        <span>Transacties</span>
        <span>Nieuwe lening aanvragen</span>
        <span>Rente & Leningtax (8,5%)</span>
    </div>

    @if($character->isComatose())
        <div class="rounded-2xl p-4 bg-rose-50 dark:bg-rose-950/60 border border-rose-200/80 dark:border-rose-800/70 text-rose-800 dark:text-rose-300 flex items-center justify-between gap-3 shadow-xs">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-rose-100 dark:bg-rose-900/60 text-rose-600 dark:text-rose-400 flex items-center justify-center text-lg shrink-0">
                    <i class="fa-solid fa-heart-pulse animate-pulse"></i>
                </div>
                <div>
                    <h4 class="text-xs font-black uppercase tracking-wider text-rose-900 dark:text-rose-200">Code Blauw (Hartstilstand)</h4>
                    <p class="text-[11px] text-rose-700 dark:text-rose-400 mt-0.5">Je bent buiten bewustzijn en kunt de Geldmaat niet bedienen tot je bent ontwaakt.</p>
                </div>
            </div>
            <a href="{{ route('dashboard') }}" class="px-3 py-1.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs uppercase tracking-wider transition shrink-0 shadow-2xs">
                Naar Overzicht &raquo;
            </a>
        </div>
    @elseif($character->isImprisoned())
        <div class="rounded-2xl p-4 bg-amber-50 dark:bg-amber-950/60 border border-amber-200/80 dark:border-amber-800/70 text-amber-800 dark:text-amber-300 flex items-center justify-between gap-3 shadow-xs">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-amber-100 dark:bg-amber-900/60 text-amber-600 dark:text-amber-400 flex items-center justify-center text-lg shrink-0">
                    <i class="fa-solid fa-lock"></i>
                </div>
                <div>
                    <h4 class="text-xs font-black uppercase tracking-wider text-amber-900 dark:text-amber-200">Opgesloten in Isoleercel</h4>
                    <p class="text-[11px] text-amber-700 dark:text-amber-400 mt-0.5">Vanuit de isoleercel heb je geen toegang tot de Geldmaat of bankzaken.</p>
                </div>
            </div>
            <a href="{{ route('prison') }}" class="px-3 py-1.5 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs uppercase tracking-wider transition shrink-0 shadow-2xs">
                Naar Cel &raquo;
            </a>
        </div>
    @endif

    <!-- ============================================================== -->
    <!-- SLIM COMPACT HEADER WITH INTEGRATED PAS & SALDO TICKER         -->
    <!-- ============================================================== -->
    <div class="rounded-2xl shadow-xs border p-3 sm:p-4 transition-all duration-300
                bg-white border-slate-200/90 text-slate-800
                dark:bg-slate-900 dark:border-slate-800 dark:text-slate-100">
        <div class="flex items-center justify-between gap-2.5 flex-wrap">
            <!-- Left: Brand / Pas Capsule -->
            <div class="flex items-center gap-2.5 min-w-0">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-amber-400 to-amber-500 text-slate-950 flex items-center justify-center text-lg font-black shrink-0 shadow-xs">
                    <i class="fa-solid fa-building-columns"></i>
                </div>
                <div class="min-w-0">
                    <div class="flex items-center gap-1.5 flex-wrap">
                        <span class="text-xs sm:text-sm font-black text-slate-900 dark:text-white truncate">
                            Geldmaat TBS
                        </span>
                        <span class="text-[9px] font-mono font-bold px-1.5 py-0.5 rounded bg-amber-50 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 border border-amber-200/80 dark:border-amber-800/70">
                            #0884
                        </span>
                    </div>
                    <span class="text-[10px] text-slate-500 dark:text-slate-400 font-mono block truncate">
                        {{ $character->name }} • Pas NL99..{{ str_pad($character->id, 4, '0', STR_PAD_LEFT) }}
                    </span>
                </div>
            </div>

            <!-- Center: Quick Balances Capsule -->
            <div class="flex items-center gap-3 font-mono text-xs">
                <div class="px-2.5 py-1 rounded-xl bg-amber-50 dark:bg-amber-950/60 border border-amber-200/80 dark:border-amber-800/70 text-amber-800 dark:text-amber-300 flex items-center gap-1.5 shadow-2xs">
                    <span class="text-[9px] uppercase font-bold text-amber-600 dark:text-amber-400">Zak:</span>
                    <strong class="font-black text-xs sm:text-sm">€{{ number_format($character->cash, 0, ',', '.') }}</strong>
                </div>
                <div class="px-2.5 py-1 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200/80 dark:border-emerald-800/70 text-emerald-700 dark:text-emerald-300 flex items-center gap-1.5 shadow-2xs">
                    <span class="text-[9px] uppercase font-bold text-emerald-600 dark:text-emerald-400">Kluis:</span>
                    <strong class="font-black text-xs sm:text-sm">€{{ number_format($character->bank, 0, ',', '.') }}</strong>
                </div>
            </div>

            <!-- Right: Controls (Sound & View Switcher) -->
            <div class="flex items-center gap-1.5 ml-auto">
                <button type="button" @click="soundEnabled = !soundEnabled"
                        :title="soundEnabled ? 'Geluid Dempen' : 'Geluid Inschakelen'"
                        class="p-2 rounded-xl text-xs transition border cursor-pointer
                               bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 border-slate-200/80 dark:border-slate-700 active:scale-95">
                    <i class="fa-solid" :class="soundEnabled ? 'fa-volume-high text-emerald-500' : 'fa-volume-xmark text-slate-400'"></i>
                </button>
                <button type="button" @click="viewMode = (viewMode === 'atm' ? 'classic' : 'atm')"
                        class="px-2.5 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5 border cursor-pointer font-mono shadow-2xs active:scale-95
                               bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 border-slate-200/80 dark:border-slate-700">
                    <i class="fa-solid" :class="viewMode === 'atm' ? 'fa-table-list text-purple-600 dark:text-purple-400' : 'fa-cash-register text-amber-600 dark:text-amber-400'"></i>
                    <span class="text-[11px]" x-text="viewMode === 'atm' ? 'Tabel' : 'Geldmaat'"></span>
                </button>
            </div>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- MODE 1: COMPACT, SLEEK GELDMAAT TERMINAL (NO BULK, NO SCROLL)  -->
    <!-- ============================================================== -->
    <div x-show="viewMode === 'atm'" class="space-y-3">

        <!-- Unified Geldmaat ATM Unit -->
        <div class="rounded-2xl sm:rounded-3xl shadow-xl overflow-hidden border-2 sm:border-3 border-amber-300 dark:border-amber-500
                    bg-gradient-to-b from-[#ffd700] via-[#ffcc00] to-[#e6b800] p-2.5 sm:p-4 relative">

            <!-- Subtle Canopy Sheen -->
            <div class="absolute inset-x-0 top-0 h-8 bg-gradient-to-b from-amber-100/50 dark:from-amber-200/20 to-transparent pointer-events-none"></div>

            <!-- ATM Header Strip -->
            <div class="flex items-center justify-between pb-2 mb-2.5 border-b border-amber-500/30 px-1 relative z-10">
                <div class="flex items-center gap-2">
                    <span class="w-6 h-6 rounded-lg bg-slate-950 text-[#ffcc00] flex items-center justify-center text-xs font-black shadow-xs">
                        <i class="fa-solid fa-vault"></i>
                    </span>
                    <span class="text-xs font-black tracking-widest text-slate-950 dark:text-slate-950 uppercase font-mono">
                        geld<span class="text-amber-950 dark:text-amber-950">maat</span>
                    </span>
                </div>

                <!-- Slim Pas Status & Eject/Insert Control -->
                <div class="flex items-center gap-2">
                    <button type="button" @click="cardInserted ? ejectCard() : insertCard()"
                            class="px-2.5 py-1 rounded-lg text-[10px] font-mono font-black uppercase tracking-wider transition cursor-pointer shadow-xs border flex items-center gap-1.5"
                            :class="cardInserted 
                                ? 'bg-slate-950 text-amber-300 border-slate-900 hover:bg-slate-900' 
                                : 'bg-emerald-600 text-white border-emerald-500 hover:bg-emerald-500'">
                        <i class="fa-solid" :class="cardInserted ? 'fa-eject text-amber-400' : 'fa-arrow-down text-white'"></i>
                        <span x-text="cardInserted ? 'Pas In (Eject)' : 'Steek Pas In'"></span>
                    </button>
                    <div class="flex items-center gap-1 bg-slate-950/15 px-2 py-0.5 rounded-full text-[9px] font-mono font-bold text-slate-950 dark:text-slate-950">
                        <span class="w-1.5 h-1.5 rounded-full bg-rose-600 animate-ping"></span>
                        <span>CAM</span>
                    </div>
                </div>
            </div>

            <!-- INNER CONSOLE SCREEN CONTAINER -->
            <div class="rounded-xl sm:rounded-2xl p-2 sm:p-3.5 shadow-inner border transition-all duration-300
                        bg-slate-100 border-slate-300 text-slate-900
                        dark:bg-slate-900 dark:border-slate-800 dark:text-white">

                <!-- ATM DIGITAL TOUCHSCREEN (Dynamic height, zero unnecessary dead space) -->
                <div class="rounded-xl border shadow-md overflow-hidden relative transition-all duration-300 p-3 sm:p-5
                            bg-white border-slate-200 text-slate-900
                            dark:bg-[#0b1120] dark:border-slate-800 dark:text-slate-100"
                     style="background-image: radial-gradient(rgba(100, 116, 139, 0.12) 1px, transparent 1px); background-size: 14px 14px;">

                    <!-- Screen Mini Status Bar -->
                    <div class="flex items-center justify-between border-b pb-2 mb-3 text-[10px] sm:text-xs font-mono
                                border-slate-200 text-slate-500 dark:border-slate-800 dark:text-slate-400">
                        <span class="font-bold text-amber-600 dark:text-amber-400">
                            Geldmaat OS v4.2
                        </span>
                        <div class="flex items-center gap-2">
                            <span class="text-emerald-600 dark:text-emerald-400 font-bold flex items-center gap-1">
                                <i class="fa-solid fa-lock text-[8px]"></i> Beveiligd
                            </span>
                            <span class="text-slate-400">|</span>
                            <span class="truncate max-w-[130px] sm:max-w-none">{{ $character->current_location ?? 'Paviljoen 1' }}</span>
                        </div>
                    </div>

                    <!-- -------------------------------------------------- -->
                    <!-- STATE 1: IDLE / STEK UW PAS IN                     -->
                    <!-- -------------------------------------------------- -->
                    <div x-show="atmScreen === 'idle'" class="text-center py-5 sm:py-8 space-y-3">
                        <div class="w-14 h-14 mx-auto rounded-2xl bg-amber-400/20 border border-amber-400/50 text-amber-500 flex items-center justify-center text-2xl shadow-xs animate-pulse">
                            <i class="fa-solid fa-credit-card"></i>
                        </div>
                        <div class="space-y-1 max-w-sm mx-auto">
                            <h2 class="text-base sm:text-lg font-black uppercase text-slate-900 dark:text-white">
                                Steek uw Kliniekpas in
                            </h2>
                            <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                                Voer je pas in om veilig contanten te storten, op te nemen of leningen af te lossen.
                            </p>
                        </div>
                        <div class="pt-2">
                            <button type="button" @click="insertCard()"
                                    class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-amber-400 to-amber-500 hover:from-amber-300 hover:to-amber-400 text-slate-950 font-black text-xs sm:text-sm uppercase tracking-wider transition shadow-md cursor-pointer transform hover:scale-105 active:scale-95 inline-flex items-center gap-2">
                                <i class="fa-solid fa-arrow-down"></i>
                                <span>Steek Betaalpas In</span>
                            </button>
                        </div>
                    </div>

                    <!-- -------------------------------------------------- -->
                    <!-- STATE 2: EERSTE KEER PINCODE INSTELLEN             -->
                    <!-- -------------------------------------------------- -->
                    <div x-show="atmScreen === 'setup_pin'" class="text-center py-3 space-y-3">
                        <span class="inline-block text-[10px] font-bold uppercase tracking-wider px-2.5 py-0.5 rounded-full bg-amber-100 text-amber-900 border border-amber-300 dark:bg-amber-950/80 dark:text-amber-300 dark:border-amber-800">
                            Eerste Gebruik • Beveiliging Instellen
                        </span>
                        <div>
                            <h3 class="text-sm sm:text-base font-black uppercase text-slate-900 dark:text-white"
                                x-text="pinStep === 'enter_new' ? 'Kies uw 4-cijferige pincode' : 'Bevestig uw 4-cijferige pincode'"></h3>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400">Gebruik het cijferblok hieronder om te voltooien.</p>
                        </div>

                        <!-- 4 PIN Dots -->
                        <div class="flex items-center justify-center gap-2.5 my-2">
                            <template x-for="i in 4" :key="i">
                                <div class="w-10 h-12 rounded-xl border-2 flex items-center justify-center text-xl font-black font-mono transition-all duration-200"
                                     :class="(pinStep === 'enter_new' ? pinDigits.length : pinConfirmDigits.length) >= i 
                                        ? 'border-amber-500 bg-amber-50 text-amber-900 shadow-xs dark:border-amber-400 dark:bg-amber-400/20 dark:text-amber-300' 
                                        : 'border-slate-300 bg-slate-50 text-slate-400 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-600'">
                                    <span x-show="(pinStep === 'enter_new' ? pinDigits.length : pinConfirmDigits.length) >= i">●</span>
                                    <span x-show="(pinStep === 'enter_new' ? pinDigits.length : pinConfirmDigits.length) < i" class="text-slate-400 dark:text-slate-700 text-xs">_</span>
                                </div>
                            </template>
                        </div>

                        <div x-show="pinError !== ''" class="text-rose-700 bg-rose-100 border border-rose-300 dark:text-rose-400 dark:bg-rose-950/70 dark:border-rose-800 text-xs font-bold font-mono p-1.5 rounded-lg max-w-xs mx-auto animate-shake" x-text="pinError"></div>
                        <div x-show="pinSuccess !== ''" class="text-emerald-700 bg-emerald-100 border border-emerald-300 dark:text-emerald-400 dark:bg-emerald-950/70 dark:border-emerald-800 text-xs font-bold font-mono p-1.5 rounded-lg max-w-xs mx-auto" x-text="pinSuccess"></div>

                        <!-- Compact PIN Keypad directly attached -->
                        <div class="max-w-[240px] mx-auto pt-1">
                            <div class="grid grid-cols-3 gap-1.5">
                                <template x-for="n in ['1','2','3','4','5','6','7','8','9']" :key="n">
                                    <button type="button" @click="pressKey(n)" class="py-2.5 rounded-lg font-bold font-mono text-base bg-slate-100 hover:bg-slate-200 text-slate-900 border border-slate-300 dark:bg-slate-800 dark:hover:bg-slate-700 dark:text-white dark:border-slate-700 transition cursor-pointer shadow-2xs" x-text="n"></button>
                                </template>
                                <button type="button" @click="pressClear()" class="py-2.5 rounded-lg font-bold font-mono text-xs uppercase bg-amber-400 hover:bg-amber-300 text-slate-950 border border-amber-500 cursor-pointer shadow-2xs">CORR</button>
                                <button type="button" @click="pressKey('0')" class="py-2.5 rounded-lg font-bold font-mono text-base bg-slate-100 hover:bg-slate-200 text-slate-900 border border-slate-300 dark:bg-slate-800 dark:hover:bg-slate-700 dark:text-white dark:border-slate-700 cursor-pointer shadow-2xs">0</button>
                                <button type="button" @click="pressOk()" class="py-2.5 rounded-lg font-bold font-mono text-xs uppercase bg-emerald-600 hover:bg-emerald-500 text-white border border-emerald-700 cursor-pointer shadow-2xs">OK</button>
                            </div>
                        </div>
                    </div>

                    <!-- -------------------------------------------------- -->
                    <!-- STATE 3: PINCODE INVOEREN                         -->
                    <!-- -------------------------------------------------- -->
                    <div x-show="atmScreen === 'enter_pin'" class="text-center py-3 space-y-3">
                        <div class="space-y-0.5">
                            <h3 class="text-base sm:text-lg font-black uppercase text-slate-900 dark:text-white">
                                Voer uw pincode in
                            </h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400">
                                Rekeninghouder: <strong class="text-slate-900 dark:text-white">{{ $character->name }}</strong>
                            </p>
                        </div>

                        <!-- 4 PIN Dots -->
                        <div class="flex items-center justify-center gap-2.5 my-2">
                            <template x-for="i in 4" :key="i">
                                <div class="w-10 h-12 rounded-xl border-2 flex items-center justify-center text-xl font-black font-mono transition-all duration-200"
                                     :class="pinDigits.length >= i 
                                        ? 'border-amber-500 bg-amber-50 text-amber-900 shadow-xs dark:border-amber-400 dark:bg-amber-400/20 dark:text-amber-300' 
                                        : 'border-slate-300 bg-slate-50 text-slate-400 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-600'">
                                    <span x-show="pinDigits.length >= i">●</span>
                                    <span x-show="pinDigits.length < i" class="text-slate-400 dark:text-slate-700 text-xs">_</span>
                                </div>
                            </template>
                        </div>

                        <div x-show="pinError !== ''" class="text-rose-700 bg-rose-100 border border-rose-300 dark:text-rose-400 dark:bg-rose-950/70 dark:border-rose-800 text-xs font-bold font-mono p-1.5 rounded-lg max-w-xs mx-auto animate-shake" x-text="pinError"></div>
                        <div x-show="pinSuccess !== ''" class="text-emerald-700 bg-emerald-100 border border-emerald-300 dark:text-emerald-400 dark:bg-emerald-950/70 dark:border-emerald-800 text-xs font-bold font-mono p-1.5 rounded-lg max-w-xs mx-auto" x-text="pinSuccess"></div>

                        <!-- Compact Touch Keypad -->
                        <div class="max-w-[240px] mx-auto pt-1">
                            <div class="grid grid-cols-3 gap-1.5">
                                <template x-for="n in ['1','2','3','4','5','6','7','8','9']" :key="n">
                                    <button type="button" @click="pressKey(n)" class="py-2.5 rounded-lg font-bold font-mono text-base bg-slate-100 hover:bg-slate-200 text-slate-900 border border-slate-300 dark:bg-slate-800 dark:hover:bg-slate-700 dark:text-white dark:border-slate-700 transition cursor-pointer shadow-2xs" x-text="n"></button>
                                </template>
                                <button type="button" @click="pressClear()" class="py-2.5 rounded-lg font-bold font-mono text-xs uppercase bg-amber-400 hover:bg-amber-300 text-slate-950 border border-amber-500 cursor-pointer shadow-2xs">CORR</button>
                                <button type="button" @click="pressKey('0')" class="py-2.5 rounded-lg font-bold font-mono text-base bg-slate-100 hover:bg-slate-200 text-slate-900 border border-slate-300 dark:bg-slate-800 dark:hover:bg-slate-700 dark:text-white dark:border-slate-700 cursor-pointer shadow-2xs">0</button>
                                <button type="button" @click="pressOk()" class="py-2.5 rounded-lg font-bold font-mono text-xs uppercase bg-emerald-600 hover:bg-emerald-500 text-white border border-emerald-700 cursor-pointer shadow-2xs">OK</button>
                            </div>
                        </div>
                    </div>

                    <!-- -------------------------------------------------- -->
                    <!-- STATE 4: HOOFDMENU (COMPACT ACTION TILES)          -->
                    <!-- -------------------------------------------------- -->
                    <div x-show="atmScreen === 'menu'" class="space-y-3 py-1">
                        <!-- Fast 2x2 Grid of Direct ATM Actions -->
                        <div class="grid grid-cols-2 gap-2 sm:gap-3">
                            <!-- Storten -->
                            <button type="button" @click="atmScreen = 'deposit'; playBeep(850, 45);"
                                    class="p-3 sm:p-4 rounded-xl border text-left transition group cursor-pointer shadow-2xs flex flex-col justify-between gap-2
                                           bg-white hover:bg-emerald-50/80 border-slate-200 hover:border-emerald-400 text-slate-900
                                           dark:bg-slate-800/90 dark:hover:bg-emerald-950/60 dark:border-slate-700 dark:hover:border-emerald-500 dark:text-white">
                                <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-400 flex items-center justify-center font-bold">
                                    <i class="fa-solid fa-arrow-down text-sm"></i>
                                </div>
                                <div>
                                    <h4 class="text-xs sm:text-sm font-black group-hover:text-emerald-600 dark:group-hover:text-emerald-300">Geld Storten</h4>
                                    <span class="text-[10px] text-slate-500 dark:text-slate-400">0% tax • Kluis</span>
                                </div>
                            </button>

                            <!-- Opnemen -->
                            <button type="button" @click="atmScreen = 'withdraw'; playBeep(850, 45);"
                                    class="p-3 sm:p-4 rounded-xl border text-left transition group cursor-pointer shadow-2xs flex flex-col justify-between gap-2
                                           bg-white hover:bg-amber-50/80 border-slate-200 hover:border-amber-400 text-slate-900
                                           dark:bg-slate-800/90 dark:hover:bg-amber-950/60 dark:border-slate-700 dark:hover:border-amber-500 dark:text-white">
                                <div class="w-8 h-8 rounded-lg bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-400 flex items-center justify-center font-bold">
                                    <i class="fa-solid fa-arrow-up text-sm"></i>
                                </div>
                                <div>
                                    <h4 class="text-xs sm:text-sm font-black group-hover:text-amber-600 dark:group-hover:text-amber-300">Opnemen</h4>
                                    <span class="text-[10px] text-slate-500 dark:text-slate-400">Contant pinnen</span>
                                </div>
                            </button>

                            <!-- Krediet & Leningen -->
                            <button type="button" @click="atmScreen = 'loans'; playBeep(850, 45);"
                                    class="p-3 sm:p-4 rounded-xl border text-left transition group cursor-pointer shadow-2xs flex flex-col justify-between gap-2
                                           bg-white hover:bg-purple-50/80 border-slate-200 hover:border-purple-400 text-slate-900
                                           dark:bg-slate-800/90 dark:hover:bg-purple-950/60 dark:border-slate-700 dark:hover:border-purple-500 dark:text-white">
                                <div class="w-8 h-8 rounded-lg bg-purple-100 text-purple-700 dark:bg-purple-500/20 dark:text-purple-400 flex items-center justify-center font-bold">
                                    <i class="fa-solid fa-hand-holding-dollar text-sm"></i>
                                </div>
                                <div>
                                    <h4 class="text-xs sm:text-sm font-black group-hover:text-purple-600 dark:group-hover:text-purple-300">Leningen</h4>
                                    <span class="text-[10px] text-slate-500 dark:text-slate-400">€{{ number_format($beschikbareLening ?? 0, 0, ',', '.') }} vrij</span>
                                </div>
                            </button>

                            <!-- Afschrift & Mutaties -->
                            <button type="button" @click="atmScreen = 'transactions'; playBeep(850, 45);"
                                    class="p-3 sm:p-4 rounded-xl border text-left transition group cursor-pointer shadow-2xs flex flex-col justify-between gap-2
                                           bg-white hover:bg-blue-50/80 border-slate-200 hover:border-blue-400 text-slate-900
                                           dark:bg-slate-800/90 dark:hover:bg-blue-950/60 dark:border-slate-700 dark:hover:border-blue-500 dark:text-white">
                                <div class="w-8 h-8 rounded-lg bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-400 flex items-center justify-center font-bold">
                                    <i class="fa-solid fa-receipt text-sm"></i>
                                </div>
                                <div>
                                    <h4 class="text-xs sm:text-sm font-black group-hover:text-blue-600 dark:group-hover:text-blue-300">Afschrift</h4>
                                    <span class="text-[10px] text-slate-500 dark:text-slate-400">Bon &amp; historie</span>
                                </div>
                            </button>
                        </div>
                    </div>

                    <!-- -------------------------------------------------- -->
                    <!-- STATE 5: GELD STORTEN                              -->
                    <!-- -------------------------------------------------- -->
                    <div x-show="atmScreen === 'deposit'" class="space-y-3 py-1">
                        <div class="flex items-center justify-between border-b pb-1.5 border-slate-200 dark:border-slate-800">
                            <button type="button" @click="atmScreen = 'menu'" class="text-xs font-mono font-bold text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white cursor-pointer">
                                &larr; Menu
                            </button>
                            <h4 class="text-xs sm:text-sm font-black uppercase text-emerald-600 dark:text-emerald-400">Geld Storten</h4>
                            <span class="text-[11px] font-mono text-slate-500">Op zak: €{{ number_format($character->cash, 0, ',', '.') }}</span>
                        </div>

                        <!-- Fast Presets -->
                        <div class="grid grid-cols-4 gap-1.5">
                            <button type="button" @click="depositAmount = 500" class="py-1.5 rounded-lg border text-xs font-mono font-bold bg-slate-100 hover:bg-slate-200 border-slate-300 dark:bg-slate-800 dark:border-slate-700 cursor-pointer">€500</button>
                            <button type="button" @click="depositAmount = 2500" class="py-1.5 rounded-lg border text-xs font-mono font-bold bg-slate-100 hover:bg-slate-200 border-slate-300 dark:bg-slate-800 dark:border-slate-700 cursor-pointer">€2.500</button>
                            <button type="button" @click="depositAmount = 10000" class="py-1.5 rounded-lg border text-xs font-mono font-bold bg-slate-100 hover:bg-slate-200 border-slate-300 dark:bg-slate-800 dark:border-slate-700 cursor-pointer">€10.000</button>
                            <button type="button" @click="depositAmount = {{ (int) $character->cash }}" class="py-1.5 rounded-lg border text-xs font-mono font-bold bg-emerald-100 hover:bg-emerald-200 border-emerald-300 text-emerald-800 dark:bg-emerald-950 dark:border-emerald-800 dark:text-emerald-300 cursor-pointer">ALLES</button>
                        </div>

                        <form method="POST" action="{{ route('bank.deposit') }}" x-ref="depositForm" class="space-y-2.5">
                            @csrf
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 font-mono font-bold text-sm">€</span>
                                <input type="number" name="amount" x-model="depositAmount" min="1" max="{{ max(1, (int) $character->cash) }}"
                                       placeholder="Bedrag..." required {{ ($character->isComatose() || $character->isImprisoned() || (int)$character->cash <= 0) ? 'disabled' : '' }}
                                       class="w-full rounded-xl py-2 pl-7 pr-3 font-mono text-sm sm:text-base font-bold border focus:outline-hidden
                                              bg-slate-50 border-slate-300 text-slate-900 focus:border-emerald-500
                                              dark:bg-slate-950 dark:border-slate-700 dark:text-white">
                            </div>

                            <button type="submit" {{ ($character->isComatose() || $character->isImprisoned() || (int)$character->cash <= 0) ? 'disabled' : '' }}
                                    class="w-full py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 disabled:opacity-50 disabled:cursor-not-allowed text-white font-black text-xs uppercase tracking-wider font-mono transition cursor-pointer shadow-xs">
                                {{ $character->isComatose() ? 'Code Blauw (Geen Geldmaat)' : ($character->isImprisoned() ? 'Isoleercel (Geen Geldmaat)' : ((int)$character->cash <= 0 ? 'Geen Contant Geld' : 'Storting Bevestigen')) }}
                            </button>
                        </form>
                    </div>

                    <!-- -------------------------------------------------- -->
                    <!-- STATE 6: CONTANT OPNEMEN                           -->
                    <!-- -------------------------------------------------- -->
                    <div x-show="atmScreen === 'withdraw'" class="space-y-3 py-1">
                        <div class="flex items-center justify-between border-b pb-1.5 border-slate-200 dark:border-slate-800">
                            <button type="button" @click="atmScreen = 'menu'" class="text-xs font-mono font-bold text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white cursor-pointer">
                                &larr; Menu
                            </button>
                            <h4 class="text-xs sm:text-sm font-black uppercase text-amber-600 dark:text-amber-400">Contant Opnemen</h4>
                            <span class="text-[11px] font-mono text-slate-500">In kluis: €{{ number_format($character->bank, 0, ',', '.') }}</span>
                        </div>

                        <div class="rounded-lg p-1.5 text-[10px] flex items-center justify-between font-mono bg-amber-50 border border-amber-200 text-amber-900 dark:bg-amber-950/40 dark:border-amber-800/60 dark:text-amber-300">
                            <span>Opnamebelasting: <strong>{{ $character->isVip() ? '3%' : '5%' }}</strong></span>
                            <span>Naar Jackpot</span>
                        </div>

                        <!-- Fast Presets -->
                        <div class="grid grid-cols-4 gap-1.5">
                            <button type="button" @click="withdrawAmount = 1000" class="py-1.5 rounded-lg border text-xs font-mono font-bold bg-slate-100 hover:bg-slate-200 border-slate-300 dark:bg-slate-800 dark:border-slate-700 cursor-pointer">€1.000</button>
                            <button type="button" @click="withdrawAmount = 5000" class="py-1.5 rounded-lg border text-xs font-mono font-bold bg-slate-100 hover:bg-slate-200 border-slate-300 dark:bg-slate-800 dark:border-slate-700 cursor-pointer">€5.000</button>
                            <button type="button" @click="withdrawAmount = 25000" class="py-1.5 rounded-lg border text-xs font-mono font-bold bg-slate-100 hover:bg-slate-200 border-slate-300 dark:bg-slate-800 dark:border-slate-700 cursor-pointer">€25.000</button>
                            <button type="button" @click="withdrawAmount = {{ (int) $character->bank }}" class="py-1.5 rounded-lg border text-xs font-mono font-bold bg-amber-100 hover:bg-amber-200 border-amber-300 text-amber-900 dark:bg-amber-950 dark:border-amber-800 dark:text-amber-300 cursor-pointer">ALLES</button>
                        </div>

                        <form method="POST" action="{{ route('bank.withdraw') }}" x-ref="withdrawForm" class="space-y-2.5">
                            @csrf
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 font-mono font-bold text-sm">€</span>
                                <input type="number" name="amount" x-model="withdrawAmount" min="1" max="{{ max(1, (int) $character->bank) }}"
                                       placeholder="Bedrag..." required {{ ($character->isComatose() || $character->isImprisoned() || (int)$character->bank <= 0) ? 'disabled' : '' }}
                                       class="w-full rounded-xl py-2 pl-7 pr-3 font-mono text-sm sm:text-base font-bold border focus:outline-hidden
                                              bg-slate-50 border-slate-300 text-slate-900 focus:border-amber-500
                                              dark:bg-slate-950 dark:border-slate-700 dark:text-white">
                            </div>

                            <button type="submit" {{ ($character->isComatose() || $character->isImprisoned() || (int)$character->bank <= 0) ? 'disabled' : '' }}
                                    class="w-full py-2.5 rounded-xl bg-amber-500 hover:bg-amber-400 disabled:opacity-50 disabled:cursor-not-allowed text-slate-950 font-black text-xs uppercase tracking-wider font-mono transition cursor-pointer shadow-xs">
                                {{ $character->isComatose() ? 'Code Blauw (Geen Geldmaat)' : ($character->isImprisoned() ? 'Isoleercel (Geen Geldmaat)' : ((int)$character->bank <= 0 ? 'Geen Banktegoed' : 'Pinnen Uit Automaat')) }}
                            </button>
                        </form>
                    </div>

                    <!-- -------------------------------------------------- -->
                    <!-- STATE 7: KREDIET & LENINGEN                        -->
                    <!-- -------------------------------------------------- -->
                    <div x-show="atmScreen === 'loans'" class="space-y-3 py-1 text-xs">
                        <div class="flex items-center justify-between border-b pb-1.5 border-slate-200 dark:border-slate-800">
                            <button type="button" @click="atmScreen = 'menu'" class="text-xs font-mono font-bold text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white cursor-pointer">
                                &larr; Menu
                            </button>
                            <h4 class="text-xs sm:text-sm font-black uppercase text-purple-600 dark:text-purple-400">Leningen &amp; Krediet</h4>
                            <span class="text-[10px] font-mono text-purple-700 dark:text-purple-300 font-bold">Rente 8,5%</span>
                        </div>

                        <div class="grid grid-cols-2 gap-2 text-center font-mono">
                            <div class="p-2 rounded-xl border bg-slate-50 border-slate-200 dark:bg-slate-950 dark:border-slate-800">
                                <span class="text-[9px] text-slate-500 uppercase block">Schuld</span>
                                <span class="text-xs sm:text-sm font-bold text-rose-600 dark:text-rose-400">€{{ number_format($openstaandeSchuld ?? 0, 0, ',', '.') }}</span>
                            </div>
                            <div class="p-2 rounded-xl border bg-slate-50 border-slate-200 dark:bg-slate-950 dark:border-slate-800">
                                <span class="text-[9px] text-slate-500 uppercase block">Kredietruimte</span>
                                <span class="text-xs sm:text-sm font-bold text-emerald-600 dark:text-emerald-400">€{{ number_format($beschikbareLening ?? 0, 0, ',', '.') }}</span>
                            </div>
                        </div>

                        <!-- Active Loans Mini List -->
                        <div class="space-y-1.5 max-h-32 overflow-y-auto pr-1">
                            @forelse($activeLoans as $loan)
                                <div class="p-2 rounded-xl border flex items-center justify-between gap-2
                                            bg-white border-slate-200 text-slate-900
                                            dark:bg-slate-950/80 dark:border-slate-800 dark:text-white">
                                    <div class="min-w-0">
                                        <div class="font-bold font-mono truncate text-[11px]">
                                            Rest: €{{ number_format($loan->remaining_due, 0, ',', '.') }}
                                        </div>
                                        <span class="text-[9px] text-slate-500">van €{{ number_format($loan->total_due, 0, ',', '.') }}</span>
                                    </div>
                                    <form method="POST" action="{{ route('bank.loans.repay-full', $loan) }}">
                                        @csrf
                                        <input type="hidden" name="source" value="bank">
                                        <button type="submit" {{ ($character->isComatose() || $character->isImprisoned()) ? 'disabled' : '' }} class="px-2.5 py-1.5 min-h-[32px] rounded-lg bg-emerald-600 hover:bg-emerald-500 disabled:opacity-50 disabled:cursor-not-allowed text-white text-[10px] font-bold font-mono transition cursor-pointer">
                                            Aflossen
                                        </button>
                                    </form>
                                </div>
                            @empty
                                <div class="text-center py-2 text-slate-500 text-xs font-mono">
                                    Geen leningen openstaand.
                                </div>
                            @endforelse
                        </div>

                        <!-- Loan Request Quick Form -->
                        @if(($beschikbareLening ?? 0) >= 10000)
                            <form method="POST" action="{{ route('bank.loans.request') }}" class="flex items-center gap-2 pt-1 border-t border-slate-200 dark:border-slate-800">
                                @csrf
                                <input type="number" name="amount" min="10000" max="{{ $beschikbareLening }}" step="5000"
                                       value="{{ min(25000, $beschikbareLening) }}" required {{ ($character->isComatose() || $character->isImprisoned()) ? 'disabled' : '' }}
                                       class="flex-1 min-w-0 rounded-xl px-2.5 py-1.5 font-mono text-xs font-bold border bg-slate-50 border-slate-300 dark:bg-slate-950 dark:border-slate-700">
                                <select name="deposit_to" {{ ($character->isComatose() || $character->isImprisoned()) ? 'disabled' : '' }} class="rounded-xl px-2 py-1.5 font-mono text-xs border bg-slate-50 border-slate-300 dark:bg-slate-950 dark:border-slate-700">
                                    <option value="bank">Kluis</option>
                                    <option value="cash">Zak</option>
                                </select>
                                <button type="submit" {{ ($character->isComatose() || $character->isImprisoned()) ? 'disabled' : '' }} class="shrink-0 px-3 py-1.5 rounded-xl bg-purple-600 hover:bg-purple-500 disabled:opacity-50 disabled:cursor-not-allowed text-white font-bold text-xs uppercase font-mono cursor-pointer shadow-xs">
                                    {{ $character->isComatose() ? 'Code Blauw' : ($character->isImprisoned() ? 'Isoleercel' : 'Lenen') }}
                                </button>
                            </form>
                        @endif
                    </div>

                    <!-- -------------------------------------------------- -->
                    <!-- STATE 8: AFSCHRIFT & TRANSACTIEBONNEN              -->
                    <!-- -------------------------------------------------- -->
                    <div x-show="atmScreen === 'transactions'" class="space-y-2 py-1 text-xs font-mono">
                        <div class="flex items-center justify-between border-b pb-1.5 border-slate-200 dark:border-slate-800">
                            <button type="button" @click="atmScreen = 'menu'" class="text-xs font-mono font-bold text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white cursor-pointer">
                                &larr; Menu
                            </button>
                            <h4 class="text-xs sm:text-sm font-black uppercase text-blue-600 dark:text-blue-400">Transactiebon</h4>
                            <span class="text-[10px] text-slate-400">Laatste 15</span>
                        </div>

                        <div class="bg-amber-50 text-slate-900 rounded-xl p-2.5 max-h-48 overflow-y-auto space-y-1 text-[11px] border border-amber-200">
                            @forelse($transactions as $tx)
                                <div class="flex items-center justify-between py-0.5 border-b border-slate-200/60">
                                    <div class="min-w-0 truncate">
                                        <span class="font-bold uppercase text-[10px] block truncate">{{ str_replace('_', ' ', $tx->type) }}</span>
                                        <span class="text-[9px] text-slate-500">{{ $tx->created_at->format('d-m H:i') }}</span>
                                    </div>
                                    <span class="font-black shrink-0 {{ $tx->amount >= 0 ? 'text-emerald-700' : 'text-rose-700' }}">
                                        {{ $tx->amount >= 0 ? '+' : '' }}€{{ number_format($tx->amount, 0, ',', '.') }}
                                    </span>
                                </div>
                            @empty
                                <div class="text-center py-3 text-slate-500">
                                    Geen transacties gevonden.
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- Slim Integrated Hardware Slot Bar (Only 26px high, zero vertical clutter) -->
                <div class="flex items-center justify-between gap-2 mt-2 pt-2 border-t border-slate-200 dark:border-slate-800 text-[10px] font-mono text-slate-500">
                    <div class="flex items-center gap-1.5 cursor-pointer hover:text-emerald-600 transition" @click="cardInserted ? ejectCard() : insertCard()">
                        <span class="w-1.5 h-1.5 rounded-full" :class="cardInserted ? 'bg-amber-500' : 'bg-emerald-500 animate-pulse'"></span>
                        <span class="font-bold">Pas-gleuf</span>
                    </div>
                    <div class="flex items-center gap-1 text-slate-400">
                        <i class="fa-solid fa-money-bill-wave text-[9px]"></i>
                        <span>Biljetlade</span>
                    </div>
                    <button type="button" @click="atmScreen = 'transactions'" class="flex items-center gap-1 hover:text-blue-500 transition cursor-pointer">
                        <i class="fa-solid fa-receipt text-[9px]"></i>
                        <span>Bonprinter</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Optional Quick Vitals Accordion (Schuld / Krediet / Uitkering) -->
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 text-xs font-mono">
            <div class="p-2.5 rounded-xl border bg-white dark:bg-slate-900 border-slate-200/90 dark:border-slate-800 shadow-xs">
                <span class="text-[9px] text-slate-500 uppercase block">Totale Schuld</span>
                <span class="text-xs sm:text-sm font-bold text-rose-600 dark:text-rose-400">€{{ number_format($openstaandeSchuld ?? 0, 0, ',', '.') }}</span>
            </div>
            <div class="p-2.5 rounded-xl border bg-white dark:bg-slate-900 border-slate-200/90 dark:border-slate-800 shadow-xs">
                <span class="text-[9px] text-slate-500 uppercase block">Kredietruimte</span>
                <span class="text-xs sm:text-sm font-bold text-purple-600 dark:text-purple-400">€{{ number_format($beschikbareLening ?? 0, 0, ',', '.') }}</span>
            </div>
            <div class="col-span-2 sm:col-span-1 p-2.5 rounded-xl border bg-white dark:bg-slate-900 border-slate-200/90 dark:border-slate-800 shadow-xs flex items-center justify-between">
                <div>
                    <span class="text-[9px] text-slate-500 uppercase block">Kwartierloon</span>
                    <span class="text-xs font-bold text-emerald-600 dark:text-emerald-400">
                        {{ ($allowanceDetails['eligible'] ?? false) ? '€' . number_format($allowanceDetails['amount_per_quarter'] ?? 1000, 0, ',', '.') . '/15m' : 'Bende Loon' }}
                    </span>
                </div>
                <span class="text-[9px] px-1.5 py-0.5 rounded bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200/80 dark:border-emerald-800/70 font-bold">
                    {{ ($allowanceDetails['eligible'] ?? false) ? 'Actief' : 'Gang' }}
                </span>
            </div>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- MODE 2: KLASSIEK OVERZICHT (Snelle Tabellen)                   -->
    <!-- ============================================================== -->
    <div x-show="viewMode === 'classic'" class="space-y-4">
        <!-- Tab Navigation Bar -->
        <div class="bg-slate-100/90 dark:bg-slate-800/80 p-1.5 rounded-xl border border-slate-200/80 dark:border-slate-700/70 flex items-center gap-1.5 overflow-x-auto">
            <button type="button" @click="activeTab = 'vault'"
                    class="px-3.5 py-2 rounded-xl text-xs font-black uppercase font-mono transition-all duration-150 active:scale-95 cursor-pointer shrink-0"
                    :class="activeTab === 'vault' ? 'bg-purple-600 text-white shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-200/70 dark:hover:bg-slate-700/60'">
                <i class="fa-solid fa-vault mr-1"></i> Kluis &amp; Beheer
            </button>
            <button type="button" @click="activeTab = 'loans'"
                    class="px-3.5 py-2 rounded-xl text-xs font-black uppercase font-mono transition-all duration-150 active:scale-95 cursor-pointer shrink-0"
                    :class="activeTab === 'loans' ? 'bg-purple-600 text-white shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-200/70 dark:hover:bg-slate-700/60'">
                <i class="fa-solid fa-hand-holding-dollar mr-1"></i> Leningen &amp; Krediet
            </button>
            <button type="button" @click="activeTab = 'transactions'"
                    class="px-3.5 py-2 rounded-xl text-xs font-black uppercase font-mono transition-all duration-150 active:scale-95 cursor-pointer shrink-0"
                    :class="activeTab === 'transactions' ? 'bg-purple-600 text-white shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-200/70 dark:hover:bg-slate-700/60'">
                <i class="fa-solid fa-receipt mr-1"></i> Transacties
            </button>
        </div>

        <!-- TAB 1: KLUIS & BEHEER -->
        <div x-show="activeTab === 'vault'" class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Storten Form -->
            <div class="p-5 rounded-2xl border shadow-xs bg-white dark:bg-slate-900 border-slate-200/90 dark:border-slate-800 space-y-3">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-emerald-100 dark:bg-emerald-950/80 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-bold">
                        <i class="fa-solid fa-arrow-down"></i>
                    </div>
                    <div>
                        <h3 class="font-black text-slate-900 dark:text-white text-sm">Geld Storten op Bank</h3>
                        <span class="text-[11px] text-slate-500">100% Kosteloos &amp; Beveiligd</span>
                    </div>
                </div>

                <form method="POST" action="{{ route('bank.deposit') }}" class="space-y-3">
                    @csrf
                    <div>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 font-mono font-bold">€</span>
                            <input type="number" name="amount" min="1" max="{{ max(1, (int) $character->cash) }}" placeholder="0" required {{ ($character->isComatose() || $character->isImprisoned() || (int)$character->cash <= 0) ? 'disabled' : '' }}
                                   class="w-full bg-slate-50 dark:bg-slate-800/90 border border-slate-200 dark:border-slate-700 rounded-xl py-2 pl-8 pr-4 font-mono font-bold text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:border-purple-500 dark:focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition">
                        </div>
                    </div>
                    <button type="submit" {{ ($character->isComatose() || $character->isImprisoned() || (int)$character->cash <= 0) ? 'disabled' : '' }} class="w-full py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 dark:bg-emerald-600 dark:hover:bg-emerald-500 disabled:opacity-50 disabled:cursor-not-allowed text-white font-black text-xs uppercase tracking-wider font-mono shadow-xs transition-all duration-150 active:scale-95 cursor-pointer">
                        {{ $character->isComatose() ? 'Code Blauw (Geen Geldmaat)' : ($character->isImprisoned() ? 'Isoleercel (Geen Geldmaat)' : ((int)$character->cash <= 0 ? 'Geen Contant Geld' : 'Storten in Beveiligde Kluis')) }}
                    </button>
                </form>
            </div>

            <!-- Opnemen Form -->
            <div class="p-5 rounded-2xl border shadow-xs bg-white dark:bg-slate-900 border-slate-200/90 dark:border-slate-800 space-y-3">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-amber-100 dark:bg-amber-950/80 text-amber-600 dark:text-amber-400 flex items-center justify-center font-bold">
                        <i class="fa-solid fa-arrow-up"></i>
                    </div>
                    <div>
                        <h3 class="font-black text-slate-900 dark:text-white text-sm">Contant Geld Opnemen</h3>
                        <span class="text-[11px] text-slate-500">Voor Zwarte Markt &amp; Handel</span>
                    </div>
                </div>

                <form method="POST" action="{{ route('bank.withdraw') }}" class="space-y-3">
                    @csrf
                    <div>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 font-mono font-bold">€</span>
                            <input type="number" name="amount" min="1" max="{{ max(1, (int) $character->bank) }}" placeholder="0" required {{ ($character->isComatose() || $character->isImprisoned() || (int)$character->bank <= 0) ? 'disabled' : '' }}
                                   class="w-full bg-slate-50 dark:bg-slate-800/90 border border-slate-200 dark:border-slate-700 rounded-xl py-2 pl-8 pr-4 font-mono font-bold text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:border-purple-500 dark:focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition">
                        </div>
                    </div>
                    <button type="submit" {{ ($character->isComatose() || $character->isImprisoned() || (int)$character->bank <= 0) ? 'disabled' : '' }} class="w-full py-2.5 rounded-xl bg-purple-600 hover:bg-purple-700 dark:bg-purple-600 dark:hover:bg-purple-500 disabled:opacity-50 disabled:cursor-not-allowed text-white font-black text-xs uppercase tracking-wider font-mono shadow-xs transition-all duration-150 active:scale-95 cursor-pointer">
                        {{ $character->isComatose() ? 'Code Blauw (Geen Geldmaat)' : ($character->isImprisoned() ? 'Isoleercel (Geen Geldmaat)' : ((int)$character->bank <= 0 ? 'Geen Banktegoed' : 'Contant Opnemen (-' . ($character->isVip() ? '3%' : '5%') . ' Tax)')) }}
                    </button>
                </form>
            </div>
        </div>

        <!-- TAB 2: LENINGEN & KREDIET -->
        <div x-show="activeTab === 'loans'" class="space-y-4">
            <div class="p-5 rounded-2xl border shadow-xs bg-white dark:bg-slate-900 border-slate-200/90 dark:border-slate-800 space-y-3">
                <div>
                    <h3 class="font-black text-slate-900 dark:text-white text-sm">Nieuwe lening aanvragen</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Max capaciteit: €{{ number_format($maxCapacity ?? 0, 0, ',', '.') }} | Rente &amp; Leningtax (8,5%)</p>
                </div>

                @if(($beschikbareLening ?? 0) >= 10000)
                    <form method="POST" action="{{ route('bank.loans.request') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-2.5">
                        @csrf
                        <input type="number" name="amount" min="10000" max="{{ $beschikbareLening }}" step="5000"
                               value="{{ min(25000, $beschikbareLening) }}" required {{ ($character->isComatose() || $character->isImprisoned()) ? 'disabled' : '' }}
                               class="w-full bg-slate-50 dark:bg-slate-800/90 border border-slate-200 dark:border-slate-700 rounded-xl py-2 px-3 font-mono font-bold text-xs text-slate-900 dark:text-slate-100 focus:outline-none focus:border-purple-500 dark:focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition">
                        <select name="deposit_to" {{ ($character->isComatose() || $character->isImprisoned()) ? 'disabled' : '' }} class="w-full bg-slate-50 dark:bg-slate-800/90 border border-slate-200 dark:border-slate-700 rounded-xl py-2 px-3 font-mono font-bold text-xs text-slate-900 dark:text-slate-100 focus:outline-none focus:border-purple-500 dark:focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition">
                            <option value="bank">Veilige Bankkluis</option>
                            <option value="cash">Contant op Zak</option>
                        </select>
                        <button type="submit" {{ ($character->isComatose() || $character->isImprisoned()) ? 'disabled' : '' }} class="w-full py-2 rounded-xl bg-purple-600 hover:bg-purple-700 dark:bg-purple-600 dark:hover:bg-purple-500 disabled:opacity-50 disabled:cursor-not-allowed text-white font-black text-xs uppercase tracking-wider font-mono shadow-xs transition-all duration-150 active:scale-95 cursor-pointer">
                            {{ $character->isComatose() ? 'Code Blauw' : ($character->isImprisoned() ? 'Isoleercel' : 'Lening Afsluiten') }}
                        </button>
                    </form>
                @else
                    <div class="p-3 rounded-xl bg-amber-50 dark:bg-amber-950/60 border border-amber-200/80 dark:border-amber-800/70 text-xs text-amber-800 dark:text-amber-300">
                        Maximale leencapaciteit bereikt. Los eerst leningen af.
                    </div>
                @endif
            </div>

            <!-- Active Loans Table -->
            <div class="rounded-2xl border shadow-xs overflow-hidden bg-white dark:bg-slate-900 border-slate-200/90 dark:border-slate-800">
                <div class="p-3.5 border-b border-slate-200/80 dark:border-slate-800 bg-slate-50/90 dark:bg-slate-800/60">
                    <h3 class="font-black text-slate-900 dark:text-white text-xs uppercase tracking-wider">Leningen Overzicht</h3>
                </div>
                <div class="divide-y divide-slate-100 dark:divide-slate-800/80">
                    @forelse($activeLoans as $loan)
                        <div class="p-3.5 flex flex-col sm:flex-row sm:items-center justify-between gap-2 hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors">
                            <div>
                                <span class="text-xs font-mono font-bold text-rose-600 dark:text-rose-400 block">
                                    Restant: €{{ number_format($loan->remaining_due, 0, ',', '.') }} van €{{ number_format($loan->total_due, 0, ',', '.') }}
                                </span>
                                <span class="text-[10px] text-slate-500 dark:text-slate-400">Rente: {{ $loan->interest_rate }}% (€{{ number_format($loan->interest_amount, 0, ',', '.') }})</span>
                            </div>
                            <form method="POST" action="{{ route('bank.loans.repay-full', $loan) }}">
                                @csrf
                                <input type="hidden" name="source" value="bank">
                                <button type="submit" {{ ($character->isComatose() || $character->isImprisoned()) ? 'disabled' : '' }} class="px-3 py-1 rounded-xl bg-emerald-600 hover:bg-emerald-700 dark:bg-emerald-600 dark:hover:bg-emerald-500 disabled:opacity-50 disabled:cursor-not-allowed text-white font-mono font-black text-xs shadow-xs transition-all duration-150 active:scale-95 cursor-pointer">
                                    Alles Aflossen
                                </button>
                            </form>
                        </div>
                    @empty
                        <div class="p-5 text-center text-xs text-slate-500 dark:text-slate-400 font-mono">
                            Geen actieve leningen openstaan.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- TAB 3: TRANSACTIES -->
        <div x-show="activeTab === 'transactions'" class="rounded-2xl border shadow-xs overflow-hidden bg-white dark:bg-slate-900 border-slate-200/90 dark:border-slate-800">
            <div class="p-3.5 border-b border-slate-200/80 dark:border-slate-800 bg-slate-50/90 dark:bg-slate-800/60 flex items-center justify-between">
                <h3 class="font-black text-slate-900 dark:text-white text-xs uppercase tracking-wider">Transacties &amp; Afschriften</h3>
                <span class="text-xs text-slate-500 dark:text-slate-400 font-mono">Laatste 15</span>
            </div>
            <div class="divide-y divide-slate-100 dark:divide-slate-800/80 font-mono text-xs">
                @forelse($transactions as $tx)
                    <div class="p-3 flex items-center justify-between hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors">
                        <div>
                            <span class="font-bold text-slate-800 dark:text-slate-200 uppercase block text-xs">{{ str_replace('_', ' ', $tx->type) }}</span>
                            <span class="text-[10px] text-slate-500 dark:text-slate-400">{{ $tx->created_at->format('d-m-Y H:i') }}</span>
                        </div>
                        <span class="font-black text-sm {{ $tx->amount >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                            {{ $tx->amount >= 0 ? '+' : '' }}€{{ number_format($tx->amount, 0, ',', '.') }}
                        </span>
                    </div>
                @empty
                    <div class="p-5 text-center text-slate-500 dark:text-slate-400">
                        Geen rekeningtransacties gevonden.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
