    <!-- SPONSOR ADS REMOVED - SAFE REDIRECT TO SHOP -->
    <script>
    window.tbsSponsorAdModal = function() {
        return {
            isOpen: false,
            open() {
                window.location.href = '{{ route("shop.index") }}';
            }
        };
    };
    window.tbsOpenSponsorAd = function() {
        window.location.href = '{{ route("shop.index") }}';
    };
    </script>

    <!-- GLOBAL AVG / GDPR INTERACTIEVE COOKIE BANNER & VOORKEUREN MODAL -->
    <div x-data="{
        showBanner: false,
        showModal: false,
        prefs: {
            necessary: true,
            functional: true
        },
        init() {
            window.tbsOpenCookieSettings = () => {
                this.showModal = true;
            };

            const consent = localStorage.getItem('tbs_cookie_consent');
            if (!consent) {
                this.showBanner = true;
            } else {
                try {
                    this.prefs = JSON.parse(consent);
                } catch(e) {
                    this.showBanner = true;
                }
            }
        },
        acceptAll() {
            this.prefs = { necessary: true, functional: true };
            this.savePreferences();
        },
        acceptNecessary() {
            this.prefs = { necessary: true, functional: false };
            this.savePreferences();
        },
        savePreferences() {
            localStorage.setItem('tbs_cookie_consent', JSON.stringify(this.prefs));
            this.showBanner = false;
            this.showModal = false;
        }
    }">

        <!-- FLOATING BOTTOM COOKIE CONSENT BANNER -->
        <div x-show="showBanner" x-cloak
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-8"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 translate-y-8"
             class="fixed bottom-4 left-4 right-4 md:left-auto md:right-4 md:max-w-md z-40 bg-white/95 dark:bg-slate-900/95 backdrop-blur-md border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-2xl space-y-3.5 text-slate-800 dark:text-slate-100">
            
            <div class="flex items-start gap-3">
                <div class="w-10 h-10 rounded-xl bg-purple-50 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400 border border-purple-200 dark:border-purple-800 flex items-center justify-center text-lg shrink-0 shadow-2xs">
                    <i class="fa-solid fa-cookie-bite"></i>
                </div>
                <div class="space-y-1">
                    <h4 class="text-sm font-black text-slate-900 dark:text-white tracking-tight">Wij respecteren jouw privacy & cookies</h4>
                    <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                        TBS Kliniek gebruikt veilige functionele cookies voor inloggen, sessiebeheer en beveiliging. Wij tonen geen externe advertentie-trackers.
                    </p>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row items-center gap-2 pt-1">
                <button type="button" @click="acceptAll()"
                        class="w-full sm:flex-1 py-2 px-3.5 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-extrabold text-xs transition cursor-pointer shadow-xs text-center">
                    Alles Accepteren
                </button>
                <button type="button" @click="acceptNecessary()"
                        class="w-full sm:flex-1 py-2 px-3.5 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 font-bold text-xs transition cursor-pointer text-center">
                    Alleen Noodzakelijk
                </button>
            </div>

            <div class="flex items-center justify-between text-[11px] text-slate-400 pt-1 border-t border-slate-100 dark:border-slate-800">
                <button type="button" @click="showModal = true" class="text-purple-600 dark:text-purple-400 hover:underline font-bold cursor-pointer">
                    Voorkeuren Aanpassen &raquo;
                </button>
                <a href="{{ route('legal.cookies') }}" class="hover:underline">Lees ons Cookiebeleid</a>
            </div>
        </div>

        <!-- COOKIE PREFERENCES MODAL -->
        <div x-show="showModal" x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 select-none">
            
            <div @click.away="showModal = false"
                 class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 sm:p-7 max-w-lg w-full shadow-2xl space-y-5 text-slate-800 dark:text-slate-100 transform-gpu will-change-transform">
                
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                    <div class="flex items-center gap-2.5">
                        <i class="fa-solid fa-cookie-bite text-purple-600 text-lg"></i>
                        <h3 class="text-base font-black text-slate-900 dark:text-white">Cookie-instellingen Beheren</h3>
                    </div>
                    <button type="button" @click="showModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-white text-lg cursor-pointer p-1">
                        &times;
                    </button>
                </div>

                <div class="space-y-3.5 max-h-80 overflow-y-auto text-xs pr-1">
                    <!-- 1. Noodzakelijk -->
                    <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 flex items-start justify-between gap-3">
                        <div class="space-y-0.5">
                            <h5 class="font-bold text-slate-900 dark:text-white">Noodzakelijke Cookies</h5>
                            <p class="text-slate-500 dark:text-slate-400 text-[11px]">Vereist voor inloggen, sessiebeheer en CSRF-beveiliging. Kunnen niet worden uitgeschakeld.</p>
                        </div>
                        <input type="checkbox" checked disabled class="mt-1 rounded text-purple-600 cursor-not-allowed">
                    </div>

                    <!-- 2. Functioneel -->
                    <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 flex items-start justify-between gap-3">
                        <div class="space-y-0.5">
                            <h5 class="font-bold text-slate-900 dark:text-white">Functionele Voorkeuren</h5>
                            <p class="text-slate-500 dark:text-slate-400 text-[11px]">Onthoudt interface-instellingen, sidebar-status en helpbot gesprekken.</p>
                        </div>
                        <input type="checkbox" x-model="prefs.functional" class="mt-1 rounded text-purple-600 focus:ring-purple-500 cursor-pointer">
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" @click="acceptAll()"
                            class="py-2 px-3.5 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 text-slate-700 dark:text-slate-200 text-xs font-bold transition cursor-pointer">
                        Alles Accepteren
                    </button>
                    <button type="button" @click="savePreferences()"
                            class="py-2 px-4 rounded-xl bg-purple-600 hover:bg-purple-700 text-white text-xs font-extrabold transition cursor-pointer shadow-xs">
                        Voorkeuren Opslaan
                    </button>
                </div>

            </div>
        </div>

    </div>
