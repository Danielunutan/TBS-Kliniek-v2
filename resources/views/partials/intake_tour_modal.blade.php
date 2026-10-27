<!-- INTERACTIEVE TBS INTAKE TOUR MODAL -->
<div x-data="{
    isOpen: false,
    step: 1,
    totalSteps: 5,
    isSubmitting: false,
    tourCompleted: @js($rookieData['tour_completed'] ?? false),

    init() {
        // Automatically open for first-time inmates if not yet completed
        if (!this.tourCompleted && !sessionStorage.getItem('tbs_tour_dismissed')) {
            setTimeout(() => {
                this.isOpen = true;
            }, 1200);
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
}" x-cloak x-show="isOpen" class="fixed inset-0 z-[10000] flex items-center justify-center p-4">

    <!-- Backdrop with blur -->
    <div class="fixed inset-0 bg-slate-950/80 backdrop-blur-md transition-opacity" 
         @click="dismiss()"></div>

    <!-- Modal Box -->
    <div class="relative w-full max-w-lg bg-gradient-to-b from-stone-50 via-white to-stone-100/90 dark:from-slate-900 dark:via-slate-900 dark:to-slate-950 rounded-2xl shadow-2xl border-2 border-stone-300 dark:border-purple-800/80 overflow-hidden z-10 transition-all p-6 sm:p-7">
        
        <!-- Header -->
        <div class="flex items-center justify-between border-b border-stone-200 dark:border-slate-800 pb-3 mb-4">
            <div class="flex items-center gap-2">
                <span class="w-8 h-8 rounded-xl bg-purple-600 text-white flex items-center justify-center text-sm font-black shadow-xs">
                    <i class="fa-solid fa-hospital-user"></i>
                </span>
                <div>
                    <h3 class="text-sm font-black text-slate-900 dark:text-white uppercase tracking-wider font-['Outfit']">
                        Intake Rondleiding FPC De Balk
                    </h3>
                    <div class="text-[10px] text-stone-500 dark:text-slate-400 font-bold">
                        Stap <span x-text="step"></span> van <span x-text="totalSteps"></span>
                    </div>
                </div>
            </div>

            <button type="button" 
                    @click="dismiss()"
                    class="w-8 h-8 rounded-lg bg-stone-100 hover:bg-stone-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-stone-500 dark:text-slate-400 flex items-center justify-center text-xs transition cursor-pointer"
                    title="Sluit rondleiding">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <!-- Step Content -->
        <div class="space-y-4 min-h-[220px]">
            
            <!-- Step 1: Welkom -->
            <div x-show="step === 1" class="space-y-3">
                <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-purple-500 to-indigo-600 text-white flex items-center justify-center text-3xl mx-auto shadow-lg shadow-purple-500/20">
                    <i class="fa-solid fa-shield-halved"></i>
                </div>
                <div class="text-center">
                    <h4 class="text-base font-black text-slate-900 dark:text-white">Welkom in de Inrichting, Patiënt!</h4>
                    <p class="text-xs text-stone-600 dark:text-slate-300 mt-1.5 leading-relaxed">
                        FPC De Balk is geen gewone kliniek. Hier overleven alleen de slimste en sterkste gevangenen. Je bouwt reputatie op, heerst over de respectlijst en richt machtige bendes op. Laten we in 4 snelle stappen doornemen hoe je het spel begrijpt!
                    </p>
                </div>
            </div>

            <!-- Step 2: Energie & Gezondheid -->
            <div x-show="step === 2" class="space-y-3">
                <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 text-white flex items-center justify-center text-3xl mx-auto shadow-lg shadow-emerald-500/20">
                    <i class="fa-solid fa-bolt"></i>
                </div>
                <div class="text-center">
                    <h4 class="text-base font-black text-slate-900 dark:text-white">Jouw Brandstof: Energie &amp; Gezondheid</h4>
                    <p class="text-xs text-stone-600 dark:text-slate-300 mt-1.5 leading-relaxed">
                        Voor misdaden, vechten en dagbesteding heb je <strong>Energie</strong> nodig. Jouw energie vult automatisch elke 3 minuten aan! Is je Gezondheid (HP) laag na een gevecht? Ga dan direct naar de <strong>Ziekenboeg</strong> om je gratis te laten oplappen door Zuster Brenda.
                    </p>
                </div>
            </div>

            <!-- Step 3: Contant vs Bank -->
            <div x-show="step === 3" class="space-y-3">
                <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-amber-400 to-amber-600 text-slate-950 flex items-center justify-center text-3xl mx-auto shadow-lg shadow-amber-500/20">
                    <i class="fa-solid fa-vault"></i>
                </div>
                <div class="text-center">
                    <h4 class="text-base font-black text-slate-900 dark:text-white">Gouden Regel: Bescherm je Contanten!</h4>
                    <p class="text-xs text-stone-600 dark:text-slate-300 mt-1.5 leading-relaxed">
                        Andere patiënten kunnen jou op de Luchtplaats aanvallen en tot <strong>15% van je contant geld</strong> stelen! Stort je buit na elke overval direct op de <strong>Bank</strong>. Geld op je bankrekening kan door niemand worden aangeraakt én levert dagelijks rente op.
                    </p>
                </div>
            </div>

            <!-- Step 4: Zaalwacht AI & Hulp -->
            <div x-show="step === 4" class="space-y-3">
                <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-indigo-500 to-purple-600 text-white flex items-center justify-center text-3xl mx-auto shadow-lg shadow-indigo-500/20">
                    <i class="fa-solid fa-headset"></i>
                </div>
                <div class="text-center">
                    <h4 class="text-base font-black text-slate-900 dark:text-white">Zaalwacht AI staat altijd voor je klaar</h4>
                    <p class="text-xs text-stone-600 dark:text-slate-300 mt-1.5 leading-relaxed">
                        Onderin het scherm vind je de <strong>Live Chat &amp; Zaalwacht AI</strong>. Heb je een vraag over een misdaad, zit je in de isoleercel of wil je snelle tips? Klik simpelweg op de suggestieknoppen in de chat en je krijgt per seconde antwoord!
                    </p>
                </div>
            </div>

            <!-- Step 5: Starter Beloning -->
            <div x-show="step === 5" class="space-y-3">
                <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-amber-400 via-rose-500 to-purple-600 text-white flex items-center justify-center text-3xl mx-auto shadow-lg shadow-amber-500/20 animate-pulse">
                    <i class="fa-solid fa-gift"></i>
                </div>
                <div class="text-center">
                    <h4 class="text-base font-black text-slate-900 dark:text-white">Je bent klaar voor de afdeling!</h4>
                    <p class="text-xs text-stone-600 dark:text-slate-300 mt-1.5 leading-relaxed">
                        Voltooi nu je <strong>Rookie Overlevingstaken</strong> op het dashboard voor gratis kogels, sigaretten en VIP status. Klik hieronder om de rondleiding af te ronden en ontvang direct jouw <strong>€1.000 welkomstbonus</strong>!
                    </p>
                </div>
            </div>

        </div>

        <!-- Dots Indicator -->
        <div class="flex items-center justify-center gap-1.5 py-3 border-t border-stone-200 dark:border-slate-800">
            <template x-for="i in totalSteps" :key="i">
                <span class="h-2 rounded-full transition-all duration-300"
                      :class="i === step ? 'w-6 bg-purple-600' : 'w-2 bg-stone-300 dark:bg-slate-700'"></span>
            </template>
        </div>

        <!-- Footer Navigation -->
        <div class="flex items-center justify-between gap-3 pt-2">
            <div>
                <button type="button" 
                        x-show="step > 1" 
                        @click="prev()"
                        class="px-3 py-2 rounded-xl bg-stone-200 hover:bg-stone-300 dark:bg-slate-800 dark:hover:bg-slate-700 text-stone-700 dark:text-slate-300 font-extrabold text-xs transition cursor-pointer">
                    <i class="fa-solid fa-arrow-left mr-1"></i> Vorige
                </button>
                <button type="button" 
                        x-show="step === 1" 
                        @click="dismiss()"
                        class="text-xs font-bold text-stone-400 hover:text-stone-600 dark:hover:text-slate-200 transition">
                    Sla over
                </button>
            </div>

            <div>
                <button type="button" 
                        x-show="step < totalSteps" 
                        @click="next()"
                        class="px-4 py-2 rounded-xl bg-purple-600 hover:bg-purple-500 text-white font-black text-xs transition shadow-sm flex items-center gap-1.5 cursor-pointer">
                    <span>Volgende</span>
                    <i class="fa-solid fa-arrow-right"></i>
                </button>

                <button type="button" 
                        x-show="step === totalSteps" 
                        @click="complete()"
                        :disabled="isSubmitting"
                        class="px-5 py-2 rounded-xl bg-amber-500 hover:bg-amber-400 active:scale-95 text-slate-950 font-black text-xs transition shadow-md shadow-amber-500/20 flex items-center gap-2 cursor-pointer">
                    <i class="fa-solid" :class="isSubmitting ? 'fa-spinner fa-spin' : 'fa-award'"></i>
                    <span>Afronden &amp; Claim €1.000 Bonus</span>
                </button>
            </div>
        </div>

    </div>

</div>
