<!-- INTERACTIEVE TBS INTAKE TOUR MODAL (Teleported to Body) -->
<div x-data="{
    isOpen: false,
    step: 1,
    totalSteps: 5,
    isSubmitting: false,
    tourCompleted: @js($rookieData['tour_completed'] ?? false),
    playerLevel: {{ (int) ($character->level ?? 1) }},

    init() {
        // Toon automatisch aan elke speler (zowel bestaand als nieuw) die de rondleiding nog niet heeft afgerond in de database
        if (!this.tourCompleted && !sessionStorage.getItem('tbs_tour_dismissed')) {
            setTimeout(() => {
                this.isOpen = true;
            }, 800);
        }

        window.addEventListener('tbs-start-intake-tour', () => {
            this.step = 1;
            this.isOpen = true;
        });
    },

    next() {
        if (this.step < this.totalSteps) {
            this.step++;
        }
    },

    prev() {
        if (this.step > 1) {
            this.step--;
        }
    },

    dismiss() {
        this.isOpen = false;
        sessionStorage.setItem('tbs_tour_dismissed', '1');
        localStorage.setItem('tbs_tour_dismissed', '1');
    },

    async complete() {
        if (this.isSubmitting) return;
        this.isSubmitting = true;
        try {
            const res = await fetch('{{ route('rookie.tour.complete') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=\'csrf-token\']')?.content || '{{ csrf_token() }}'
                }
            });
            const data = await res.json();
            this.isOpen = false;
            this.tourCompleted = true;
            localStorage.setItem('tbs_tour_dismissed', '1');
            if (window.tbsShowToast) {
                window.tbsShowToast(data.message, 'success');
            } else {
                alert(data.message);
            }
            setTimeout(() => window.location.reload(), 600);
        } catch(e) {
            this.isOpen = false;
        } finally {
            this.isSubmitting = false;
        }
    }
}">

    <template x-teleport="body">
        <div x-show="isOpen" 
             x-cloak 
             class="fixed inset-0 flex items-center justify-center p-4 sm:p-6 select-none"
             style="z-index: 999999 !important;">

            <!-- Solid Dark Backdrop -->
            <div class="fixed inset-0 transition-opacity" 
                 style="background-color: rgba(15, 23, 42, 0.75); backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px); z-index: 999998 !important;"
                 x-show="isOpen"
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-150"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @click="dismiss()"></div>

            <!-- Completely Opaque Modal Box (Unified Design Standard) -->
            <div role="dialog"
                 aria-modal="true"
                 class="relative w-full max-w-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 rounded-2xl shadow-2xl border border-slate-200/90 dark:border-slate-800 overflow-hidden transition-all p-6 sm:p-7 text-left"
                 style="z-index: 1000000 !important;"
                 x-show="isOpen"
                 x-transition:enter="ease-out duration-250"
                 x-transition:enter-start="opacity-0 translate-y-4 scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                 x-transition:leave="ease-in duration-150"
                 x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 scale-95"
                 @click.stop>
                
                <!-- Header -->
                <div class="flex items-center justify-between border-b border-slate-200/80 dark:border-slate-800 pb-3.5 mb-4">
                    <div class="flex items-center gap-2.5">
                        <span class="w-9 h-9 rounded-xl bg-purple-600 text-white flex items-center justify-center text-sm font-black shadow-xs">
                            <i class="fa-solid fa-hospital-user"></i>
                        </span>
                        <div>
                            <h3 class="text-sm font-black text-slate-900 dark:text-white uppercase tracking-wider font-['Outfit']">
                                Intake Rondleiding FPC De Balk
                            </h3>
                            <div class="text-[10px] text-purple-700 dark:text-purple-300 font-extrabold">
                                Stap <span x-text="step"></span> van <span x-text="totalSteps"></span>
                            </div>
                        </div>
                    </div>

                    <button type="button" 
                            @click="dismiss()"
                            class="w-8 h-8 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 border border-slate-200/80 dark:border-slate-700 flex items-center justify-center text-xs transition-all duration-150 active:scale-95 cursor-pointer"
                            title="Sluit rondleiding">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <!-- Step Content Slides -->
                <div class="space-y-4 min-h-[200px] flex flex-col justify-center">
                    
                    <!-- Step 1: Welkom -->
                    <div x-show="step === 1" class="space-y-3">
                        <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-purple-500 to-indigo-600 text-white flex items-center justify-center text-3xl mx-auto shadow-lg shadow-purple-500/25">
                            <i class="fa-solid fa-shield-halved"></i>
                        </div>
                        <div class="text-center px-2">
                            <h4 class="text-base font-black text-slate-900 dark:text-white">Welkom in de Inrichting, Patiënt!</h4>
                            <p class="text-xs text-slate-600 dark:text-slate-300 mt-1.5 leading-relaxed">
                                FPC De Balk is geen gewone kliniek. Hier overleven alleen de slimste en sterkste gevangenen. Je bouwt reputatie op, heerst over de respectlijst en richt machtige bendes op. Laten we in 4 snelle stappen doornemen hoe je het spel begrijpt!
                            </p>
                        </div>
                    </div>

                    <!-- Step 2: Energie & Gezondheid -->
                    <div x-show="step === 2" class="space-y-3">
                        <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 text-white flex items-center justify-center text-3xl mx-auto shadow-lg shadow-emerald-500/25">
                            <i class="fa-solid fa-bolt"></i>
                        </div>
                        <div class="text-center px-2">
                            <h4 class="text-base font-black text-slate-900 dark:text-white">Jouw Brandstof: Energie &amp; Gezondheid</h4>
                            <p class="text-xs text-slate-600 dark:text-slate-300 mt-1.5 leading-relaxed">
                                Voor misdaden, vechten en dagbesteding heb je <strong>Energie</strong> nodig. Jouw energie vult automatisch elke 3 minuten aan! Is je Gezondheid (HP) laag na een gevecht? Ga dan direct naar de <strong>Ziekenboeg</strong> om je gratis te laten oplappen door Zuster Brenda.
                            </p>
                        </div>
                    </div>

                    <!-- Step 3: Contant vs Bank -->
                    <div x-show="step === 3" class="space-y-3">
                        <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-amber-400 to-amber-600 text-slate-950 flex items-center justify-center text-3xl mx-auto shadow-lg shadow-amber-500/25">
                            <i class="fa-solid fa-vault"></i>
                        </div>
                        <div class="text-center px-2">
                            <h4 class="text-base font-black text-slate-900 dark:text-white">Gouden Regel: Bescherm je Contanten!</h4>
                            <p class="text-xs text-slate-600 dark:text-slate-300 mt-1.5 leading-relaxed">
                                Andere patiënten kunnen jou op de Luchtplaats aanvallen en tot <strong>15% van je contant geld</strong> stelen! Stort je buit na elke overval direct op de <strong>Bank</strong>. Geld op je bankrekening kan door niemand worden aangeraakt én levert dagelijks rente op.
                            </p>
                        </div>
                    </div>

                    <!-- Step 4: Zaalwacht AI & Hulp -->
                    <div x-show="step === 4" class="space-y-3">
                        <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-indigo-500 to-purple-600 text-white flex items-center justify-center text-3xl mx-auto shadow-lg shadow-indigo-500/25">
                            <i class="fa-solid fa-headset"></i>
                        </div>
                        <div class="text-center px-2">
                            <h4 class="text-base font-black text-slate-900 dark:text-white">Zaalwacht AI staat altijd voor je klaar</h4>
                            <p class="text-xs text-slate-600 dark:text-slate-300 mt-1.5 leading-relaxed">
                                Onderin het scherm vind je de <strong>Live Chat &amp; Zaalwacht AI</strong>. Heb je een vraag over een misdaad, zit je in de isoleercel of wil je snelle tips? Klik simpelweg op de suggestieknoppen in de chat en je krijgt direct antwoord!
                            </p>
                        </div>
                    </div>

                    <!-- Step 5: Starter Beloning -->
                    <div x-show="step === 5" class="space-y-3">
                        <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-amber-400 via-rose-500 to-purple-600 text-white flex items-center justify-center text-3xl mx-auto shadow-lg shadow-amber-500/25 animate-pulse">
                            <i class="fa-solid fa-gift"></i>
                        </div>
                        <div class="text-center px-2">
                            <h4 class="text-base font-black text-slate-900 dark:text-white">Je bent klaar voor de afdeling!</h4>
                            <p class="text-xs text-slate-600 dark:text-slate-300 mt-1.5 leading-relaxed">
                                Voltooi nu je <strong>Rookie Overlevingstaken</strong> op het dashboard voor gratis kogels, sigaretten en VIP status. Klik hieronder om de rondleiding af te ronden en ontvang direct jouw <strong>€1.000 welkomstbonus</strong>!
                            </p>
                        </div>
                    </div>

                </div>

                <!-- Dots Indicator -->
                <div class="flex items-center justify-center gap-1.5 py-3 border-t border-slate-200/80 dark:border-slate-800">
                    <template x-for="i in totalSteps" :key="i">
                        <span class="h-2 rounded-full transition-all duration-300"
                              :class="i === step ? 'w-6 bg-purple-600' : 'w-2 bg-slate-200 dark:bg-slate-700'"></span>
                    </template>
                </div>

                <!-- Footer Navigation -->
                <div class="flex items-center justify-between gap-3 pt-2">
                    <div>
                        <button type="button" 
                                x-show="step > 1" 
                                @click="prev()"
                                class="px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 border border-slate-200/80 dark:border-slate-700 font-bold text-xs transition-all duration-150 active:scale-95 cursor-pointer">
                            <i class="fa-solid fa-arrow-left mr-1"></i> Vorige
                        </button>
                        <button type="button" 
                                x-show="step === 1" 
                                @click="dismiss()"
                                class="text-xs font-bold text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition p-1">
                            Sla over
                        </button>
                    </div>

                    <div>
                        <button type="button" 
                                x-show="step < totalSteps" 
                                @click="next()"
                                class="px-5 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-700 dark:bg-purple-600 dark:hover:bg-purple-500 text-white font-black text-xs shadow-xs transition-all duration-150 active:scale-95 flex items-center gap-2 cursor-pointer">
                            <span>Volgende</span>
                            <i class="fa-solid fa-arrow-right text-[11px]"></i>
                        </button>

                        <button type="button" 
                                x-show="step === totalSteps" 
                                @click="complete()"
                                :disabled="isSubmitting"
                                class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 dark:bg-emerald-600 dark:hover:bg-emerald-500 disabled:opacity-50 text-white font-black text-xs shadow-xs transition-all duration-150 active:scale-95 flex items-center gap-2 cursor-pointer">
                            <i class="fa-solid" :class="isSubmitting ? 'fa-spinner fa-spin' : 'fa-award'"></i>
                            <span>Afronden &amp; Claim €1.000 Bonus</span>
                        </button>
                    </div>
                </div>

            </div>

        </div>
    </template>

</div>
