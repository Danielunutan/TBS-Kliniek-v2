<!-- TBS Kliniek App Install Modal & Sleek Floating Advice Banner -->
<div x-data="{
        showModal: false,
        showBanner: false,
        isInstalled: false,
        isIos: false,
        canNativeInstall: false,
        hasClaimedBonus: {{ Auth::user()?->socialRewardClaims()->where('platform', 'push')->exists() ? 'true' : 'false' }},
        claimingBonus: false,
        init() {
            // Check if already running in standalone mode
            if (window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true) {
                this.isInstalled = true;
            }

            // Check iOS / iPadOS Safari (including iPadOS 13+ desktop-class agent)
            const userAgent = window.navigator.userAgent.toLowerCase();
            this.isIos = (/iphone|ipad|ipod/.test(userAgent) || (window.navigator.platform === 'MacIntel' && window.navigator.maxTouchPoints > 1)) && !window.MSStream;

            // Check banner dismissal timestamp (show max once every 7 days)
            const dismissedAt = localStorage.getItem('tbs_app_banner_dismissed');
            const now = Date.now();
            const sevenDays = 7 * 24 * 60 * 60 * 1000;

            if (!this.isInstalled && (!dismissedAt || (now - parseInt(dismissedAt)) > sevenDays)) {
                setTimeout(() => {
                    if (!this.isInstalled) {
                        this.showBanner = true;
                    }
                }, 3000);
            }

            // Capture native beforeinstallprompt
            window.addEventListener('beforeinstallprompt', (e) => {
                e.preventDefault();
                window.tbsDeferredPwaPrompt = e;
                this.canNativeInstall = true;
            });

            // Global trigger to open modal from menu/sidebar buttons
            window.tbsOpenPwaModal = () => {
                this.showModal = true;
            };
        },
        async installNative() {
            if (window.tbsDeferredPwaPrompt) {
                window.tbsDeferredPwaPrompt.prompt();
                const { outcome } = await window.tbsDeferredPwaPrompt.userChoice;
                if (outcome === 'accepted') {
                    this.isInstalled = true;
                    this.showBanner = false;
                    this.showModal = false;
                }
                window.tbsDeferredPwaPrompt = null;
            } else {
                this.showModal = true;
            }
        },
        async claimBonus() {
            this.claimingBonus = true;
            try {
                const csrfToken = document.querySelector('meta[name=\'csrf-token\']')?.getAttribute('content');
                const res = await fetch('{{ route('community.claim', 'push') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-TBS-Standalone': '1'
                    },
                    body: JSON.stringify({ is_standalone: true })
                });
                const data = await res.json();
                if (res.ok && data.success) {
                    this.hasClaimedBonus = true;
                    if (typeof window.tbsToast === 'function') {
                        window.tbsToast(data.message, 'success', 6000);
                    }
                    if (typeof window.tbsRefreshStats === 'function') {
                        window.tbsRefreshStats(true);
                    }
                    if (data.character) {
                        window.dispatchEvent(new CustomEvent('tbs:stats-updated', { detail: data.character }));
                    }
                } else {
                    if (typeof window.tbsToast === 'function') {
                        const errMsg = res.status === 401
                            ? 'Log eerst in om je web app bonus te claimen.'
                            : (data?.message || 'Claimen mislukt.');
                        window.tbsToast(errMsg, 'warning');
                    }
                }
            } catch (e) {
                if (typeof window.tbsToast === 'function') {
                    window.tbsToast('Er is een fout opgetreden bij het claimen.', 'error');
                }
            } finally {
                this.claimingBonus = false;
            }
        },
        dismissBanner() {
            this.showBanner = false;
            localStorage.setItem('tbs_app_banner_dismissed', Date.now().toString());
        }
     }"
     x-cloak>

    <!-- SLEEK FLOATING APP ADVICE BANNER -->
    <div x-show="showBanner && !isInstalled"
         x-transition:enter="transition ease-out duration-300 transform"
         x-transition:enter-start="translate-y-8 opacity-0 scale-95"
         x-transition:enter-end="translate-y-0 opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-200 transform"
         x-transition:leave-start="translate-y-0 opacity-100 scale-100"
         x-transition:leave-end="translate-y-8 opacity-0 scale-95"
         class="fixed bottom-20 lg:bottom-6 right-3 lg:right-6 z-40 max-w-sm w-[calc(100vw-1.5rem)] sm:w-[360px] bg-white/95 dark:bg-slate-900/95 backdrop-blur-md border border-slate-200/90 dark:border-slate-800 rounded-2xl p-4 shadow-xl shadow-slate-900/10 dark:shadow-black/50 flex items-start gap-3.5">
        
        <div class="w-11 h-11 rounded-xl bg-purple-600 shrink-0 flex items-center justify-center text-white shadow-xs overflow-hidden p-1">
            <img src="{{ asset('assets/logo.png') }}?v=3" alt="TBS Kliniek App" class="w-full h-full object-contain">
        </div>

        <div class="flex-1 min-w-0">
            <div class="flex items-center gap-1.5">
                <span class="text-xs font-black text-slate-900 dark:text-white">Download TBS Kliniek App</span>
                <span class="text-[9px] font-black uppercase px-1.5 py-0.5 rounded-md bg-purple-50 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 border border-purple-200/80 dark:border-purple-800/70">App</span>
            </div>
            <p class="text-[11px] text-slate-500 dark:text-slate-400 leading-snug mt-0.5">
                Speel in fullscreen zonder adresbalk, direct vanaf je startscherm!
            </p>
            <div class="flex items-center gap-2 mt-2.5">
                <button type="button" 
                        @click="canNativeInstall ? installNative() : (showModal = true)"
                        class="h-8 px-3.5 rounded-xl bg-purple-600 hover:bg-purple-700 dark:bg-purple-600 dark:hover:bg-purple-500 active:scale-95 text-white font-black text-xs shadow-xs transition-all duration-150 flex items-center gap-1.5 cursor-pointer">
                    <i class="fa-solid fa-download text-[10px]"></i>
                    <span>Nu Installeren</span>
                </button>
                <button type="button" 
                        @click="dismissBanner()" 
                        class="h-8 px-3 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 border border-slate-200/80 dark:border-slate-700 text-xs font-bold transition-all duration-150 active:scale-95 cursor-pointer">
                    Later
                </button>
            </div>
        </div>

        <button type="button" @click="dismissBanner()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1 text-sm cursor-pointer transition-colors duration-150 -mr-1 -mt-1" aria-label="Sluiten">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>

    <!-- APP INSTALL MODAL (INFORMATIVE & STEP-BY-STEP ADVICE) -->
    <div x-show="showModal"
         @keydown.escape.window="showModal = false"
         class="fixed inset-0 z-[80] flex items-center justify-center p-4 pb-24 lg:pb-4 bg-slate-950/80 select-none">
        
        <div @click.away="showModal = false"
             class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-6 sm:p-7 max-w-md w-full max-h-[85dvh] overflow-y-auto shadow-2xl space-y-5 text-slate-800 dark:text-slate-100 animate-in fade-in zoom-in-95 duration-150 transform-gpu will-change-transform">
            
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3.5">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-purple-600 flex items-center justify-center text-white shadow-xs p-1 overflow-hidden">
                        <img src="{{ asset('assets/logo.png') }}?v=3" alt="TBS Kliniek Emblem" class="w-full h-full object-contain">
                    </div>
                    <div>
                        <h3 class="text-base font-extrabold text-slate-900 dark:text-white leading-tight">TBS Kliniek App</h3>
                        <span class="text-[11px] text-purple-600 dark:text-purple-400 font-bold">Web App Installatie</span>
                    </div>
                </div>
                <button type="button" @click="showModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 text-lg cursor-pointer p-1 transition-colors duration-150">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div class="space-y-3.5 text-xs text-slate-600 dark:text-slate-300">
                <div class="p-3.5 rounded-xl bg-purple-50/90 dark:bg-purple-950/60 border border-purple-200/80 dark:border-purple-800/70 space-y-2">
                    <div class="font-extrabold text-purple-900 dark:text-purple-200 flex items-center gap-2 text-xs">
                        <i class="fa-solid fa-bolt text-amber-500"></i> Waarom de App installeren?
                    </div>
                    <ul class="space-y-1.5 text-[11px] text-slate-700 dark:text-slate-300 font-medium">
                        <li class="flex items-center gap-2 text-amber-600 dark:text-amber-400 font-bold">
                            <i class="fa-solid fa-gift text-amber-500"></i>
                            <span><strong>Inclusief Welkomstbonus:</strong> Ontvang direct € 25.000 cash + 25 respect bij eerste opening als Web App!</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <i class="fa-solid fa-check text-emerald-500"></i>
                            <span><strong>Geen browserbalken:</strong> Echte fullscreen gaming ervaring.</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <i class="fa-solid fa-check text-emerald-500"></i>
                            <span><strong>Sneller opstarten:</strong> Direct vanaf je bureaublad of telefoon startscherm.</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <i class="fa-solid fa-check text-emerald-500"></i>
                            <span><strong>Laag data- & batterijverbruik:</strong> Optimaal gecachet en vliegensvlug.</span>
                        </li>
                    </ul>
                </div>

                <!-- Platform Specific Guidance -->
                <template x-if="isIos">
                    <div class="p-3.5 rounded-xl bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70 space-y-2.5">
                        <div class="font-bold text-slate-900 dark:text-white flex items-center gap-2">
                            <i class="fa-brands fa-apple text-slate-800 dark:text-slate-200 text-sm"></i>
                            <span>Installatie op iPhone / iPad (Safari)</span>
                        </div>
                        <ol class="space-y-1.5 text-[11px] list-decimal list-inside text-slate-600 dark:text-slate-300 font-medium leading-relaxed">
                            <li>Tik onderaan op de <strong>Deel-knop</strong> (<i class="fa-solid fa-arrow-up-from-bracket text-indigo-500"></i> in Safari).</li>
                            <li>Scrol omlaag en tik op <strong>'Zet op beginscherm'</strong> (<i class="fa-regular fa-square-plus text-purple-500"></i>).</li>
                            <li>Tik rechtsboven op <strong>'Voeg toe'</strong>. Klaar!</li>
                        </ol>
                    </div>
                </template>

                <template x-if="!isIos">
                    <div class="p-3.5 rounded-xl bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70 space-y-2">
                        <div class="font-bold text-slate-900 dark:text-white flex items-center gap-2">
                            <i class="fa-solid fa-desktop text-slate-600 dark:text-slate-300"></i>
                            <span>Windows, Mac & Android (Chrome / Edge)</span>
                        </div>
                        <p class="text-[11px] leading-relaxed">
                            Klik op de onderstaande knop om de app met 1 klik te installeren. Er verschijnt direct een officieel TBS Kliniek icoon op je taakbalk of startscherm!
                        </p>
                    </div>
                </template>
            </div>

            <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-100 dark:border-slate-800">
                <button type="button" @click="showModal = false"
                        class="py-2.5 px-4 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 border border-slate-200/80 dark:border-slate-700 text-xs font-bold transition-all duration-150 active:scale-95 cursor-pointer">
                    Sluiten
                </button>
                @auth
                <template x-if="isInstalled && !hasClaimedBonus">
                    <button type="button" @click="claimBonus()" :disabled="claimingBonus"
                            class="py-2.5 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-500 active:scale-95 text-white text-xs font-black transition-all duration-150 cursor-pointer shadow-xs flex items-center gap-1.5 disabled:opacity-50">
                        <i class="fa-solid" :class="claimingBonus ? 'fa-spinner fa-spin' : 'fa-gift'"></i>
                        <span x-text="claimingBonus ? 'Bezig...' : 'Claim Web App Bonus (€ 25.000)'"></span>
                    </button>
                </template>
                <template x-if="isInstalled && hasClaimedBonus">
                    <span class="inline-flex items-center gap-1.5 py-2 px-3 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-300 dark:border-emerald-800 text-emerald-700 dark:text-emerald-300 text-xs font-bold">
                        <i class="fa-solid fa-circle-check"></i>
                        <span>Web App Bonus Geclaimd</span>
                    </span>
                </template>
                @endauth
                <template x-if="canNativeInstall">
                    <button type="button" @click="installNative()"
                            class="py-2.5 px-5 rounded-xl bg-purple-600 hover:bg-purple-700 dark:bg-purple-600 dark:hover:bg-purple-500 active:scale-95 text-white text-xs font-black transition-all duration-150 cursor-pointer shadow-xs flex items-center gap-1.5">
                        <i class="fa-solid fa-download"></i>
                        <span>Direct Installeren</span>
                    </button>
                </template>
            </div>

        </div>
    </div>
</div>
