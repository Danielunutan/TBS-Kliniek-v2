    @if(\App\Http\Controllers\SponsorAdController::areAdsEnabled())
    <!-- ROBINADS REWARDED AD MODAL (DESKTOP & MOBILE) -->
    <script src="https://robinsads.com/sdk/robinads.min.js" async onerror="window.__tbsRobinSdkBlocked = true;"></script>
    <div x-data="tbsSponsorAdModal()"
         x-show="isOpen"
         x-cloak
         @keydown.escape.window="if (isOpen && state !== 'claiming') close()"
         class="fixed inset-0 z-[80] flex items-center justify-center p-4 pb-24 lg:pb-4 bg-slate-950/80 backdrop-blur-xs select-none">

        <div @click.away="if (state !== 'watching' && state !== 'claiming') close()"
             class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl max-w-md w-full max-h-[85dvh] overflow-y-auto p-5 sm:p-6 shadow-2xl space-y-4 text-slate-800 dark:text-slate-100 relative">

            <!-- Header -->
            <div class="flex items-center justify-between border-b border-slate-200/80 dark:border-slate-800 pb-3">
                <div class="flex items-center gap-2.5 min-w-0">
                    <div class="w-9 h-9 rounded-xl bg-purple-600 text-white flex items-center justify-center text-sm shadow-xs shrink-0">
                        <i class="fa-solid fa-tv"></i>
                    </div>
                    <div class="min-w-0">
                        <span class="text-[10px] font-black uppercase tracking-wider text-purple-600 dark:text-purple-400 block">TBS Sponsorkamer</span>
                        <h3 class="text-sm sm:text-base font-black text-slate-900 dark:text-white truncate" x-text="rewardTitle">Sponsor Beloning</h3>
                    </div>
                </div>
                <button type="button"
                        @click="close()"
                        :disabled="state === 'claiming'"
                        class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-slate-600 dark:hover:text-white text-lg cursor-pointer transition-colors disabled:opacity-40"
                        aria-label="Sluiten">
                    &times;
                </button>
            </div>

            <!-- State: Starting -->
            <div x-show="state === 'starting'" class="py-6 text-center space-y-3">
                <div class="w-12 h-12 rounded-2xl bg-purple-50 dark:bg-purple-950/60 border border-purple-200/80 dark:border-purple-800/70 text-purple-600 dark:text-purple-400 flex items-center justify-center text-xl mx-auto">
                    <i class="fa-solid fa-circle-notch fa-spin"></i>
                </div>
                <p class="text-xs font-bold text-slate-700 dark:text-slate-200">Sponsor-sessie wordt voorbereid...</p>
                <p class="text-[11px] text-slate-400 dark:text-slate-500">Even geduld, je beloning wordt gekoppeld aan je karakter.</p>
            </div>

            <!-- State: Watching (15s Countdown + Sponsor Showcase) -->
            <div x-show="state === 'watching'" class="space-y-4 py-2">
                <div class="p-4 rounded-xl bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70 text-center space-y-3">
                    <div class="w-14 h-14 mx-auto rounded-2xl bg-purple-600/10 dark:bg-purple-500/20 border border-purple-300/80 dark:border-purple-700 text-purple-600 dark:text-purple-300 flex items-center justify-center text-xl font-mono font-black shadow-2xs">
                        <span x-text="secondsLeft + 's'">15s</span>
                    </div>
                    <div class="space-y-1">
                        <h4 class="text-xs sm:text-sm font-black text-slate-900 dark:text-white">Sponsor-uitzending actief</h4>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 leading-relaxed">
                            Blijf nog <strong class="text-purple-600 dark:text-purple-400 font-mono" x-text="secondsLeft">15</strong> seconden op dit scherm om je beloning automatisch vrij te spelen.
                        </p>
                    </div>
                    <!-- Progress Bar -->
                    <div class="w-full bg-slate-200 dark:bg-slate-700 rounded-full h-2.5 overflow-hidden p-0.5">
                        <div class="bg-gradient-to-r from-purple-600 to-amber-500 h-full rounded-full transition-all duration-1000 ease-linear"
                             :style="'width: ' + Math.min(100, Math.max(0, ((totalSeconds - secondsLeft) / totalSeconds) * 100)) + '%'"></div>
                    </div>
                </div>

                <div class="p-3.5 rounded-xl bg-amber-50/80 dark:bg-amber-950/40 border border-amber-200/80 dark:border-amber-800/70 flex items-center justify-between gap-3">
                    <div class="text-[11px] text-amber-900 dark:text-amber-200 leading-snug">
                        <span class="font-black block">Steun TBS Kliniek ❤️</span>
                        <span>Elke sponsor-ad houdt de game 100% gratis en reclamevrij op andere pagina's.</span>
                    </div>
                    <a href="{{ route('vip.index') }}" class="shrink-0 px-2.5 py-1.5 rounded-lg bg-amber-500/20 border border-amber-400/40 text-amber-900 dark:text-amber-200 font-black text-[10px] hover:bg-amber-500/30 transition">
                        VIP Info
                    </a>
                </div>
            </div>

            <!-- State: Ready to Claim -->
            <div x-show="state === 'ready' || state === 'claiming'" class="space-y-4 py-2 text-center">
                <div class="w-14 h-14 mx-auto rounded-2xl bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200/80 dark:border-emerald-800/70 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-2xl shadow-xs">
                    <i class="fa-solid" :class="state === 'claiming' ? 'fa-circle-notch fa-spin' : 'fa-gift'"></i>
                </div>
                <div class="space-y-1">
                    <h4 class="text-sm font-black text-slate-900 dark:text-white" x-text="state === 'claiming' ? 'Beloning wordt uitgekeerd...' : 'Advertentie Voltooid!'"></h4>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Je sponsor-sessie is geverifieerd. Claim hieronder direct je beloning.</p>
                </div>
                <button type="button"
                        @click="claim()"
                        :disabled="state === 'claiming'"
                        class="w-full py-3 px-5 rounded-xl bg-emerald-600 hover:bg-emerald-700 dark:bg-emerald-600 dark:hover:bg-emerald-500 active:scale-95 text-white font-black text-xs uppercase tracking-wider transition shadow-sm cursor-pointer flex items-center justify-center gap-2 disabled:opacity-50">
                    <i class="fa-solid fa-check-double"></i>
                    <span x-text="state === 'claiming' ? 'Bezig met claimen...' : 'Beloning Nu Claimen'"></span>
                </button>
            </div>

            <!-- State: Claimed -->
            <div x-show="state === 'claimed'" class="py-5 text-center space-y-3">
                <div class="w-14 h-14 mx-auto rounded-2xl bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200/80 dark:border-emerald-800/70 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-2xl shadow-xs">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
                <h4 class="text-sm font-black text-emerald-700 dark:text-emerald-300">Beloning Geclaimd!</h4>
                <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed" x-text="successMsg"></p>
            </div>

            <!-- State: Error -->
            <div x-show="state === 'error'" class="py-4 text-center space-y-3">
                <div class="w-12 h-12 mx-auto rounded-2xl bg-rose-50 dark:bg-rose-950/60 border border-rose-200/80 dark:border-rose-800/70 text-rose-600 dark:text-rose-400 flex items-center justify-center text-xl">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
                <p class="text-xs font-bold text-rose-600 dark:text-rose-400 leading-relaxed" x-text="errorMsg"></p>
                <div class="flex items-center justify-center gap-2 pt-1">
                    <button type="button"
                            @click="close()"
                            class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 border border-slate-200/80 dark:border-slate-700 text-xs font-bold transition cursor-pointer">
                        Sluiten
                    </button>
                </div>
            </div>

        </div>
    </div>

    <script>
    window.tbsSponsorAdModal = function() {
        return {
            isOpen: false,
            state: 'idle',
            rewardType: 'cash',
            rewardTitle: '€ 2.500 Zakgeld',
            token: null,
            robinToken: null,
            robinRewardId: null,
            secondsLeft: 15,
            totalSeconds: 15,
            timerInterval: null,
            errorMsg: '',
            successMsg: '',
            onCompleteCallback: null,

            init() {
                window.tbsOpenSponsorAd = (rewardType = 'cash', callback = null) => {
                    this.open(rewardType, callback);
                };

                try {
                    if (window.RobinAds && typeof window.RobinAds.init === 'function') {
                        window.RobinAds.init({
                            websiteId: '{{ config("services.robinads.website_id", "6ad2b8e95f70fee0a42a5496") }}'
                        });
                    }
                } catch (e) {}
            },

            clearWatchTimer() {
                if (this.timerInterval) {
                    clearInterval(this.timerInterval);
                    this.timerInterval = null;
                }
            },

            getRewardTitle(type) {
                const titles = {
                    'cash': '€ 2.500 Contant Zakgeld',
                    'daily_stars': 'Dagelijkse +5 Sterren ⭐',
                    'daily_cash': 'Dagelijkse € 50.000 Cash',
                    'daily_xp': 'Dagelijkse +50 XP Boost',
                    'prison': 'Isoleercel Strafvermindering & Vrijlating',
                    'bullets': '+50 Scherpe Kogels 💣',
                    'bullets_50': '+50 Scherpe Kogels 💣',
                    'vest': '12 Uur Kogelwerend Vest 🛡️',
                    'vest_12h': '12 Uur Kogelwerend Vest 🛡️',
                    'coma_revive': 'IC Coma Ontwaken 💉',
                    'coma': 'IC Coma Ontwaken 💉',
                    'respect': '+1 Kliniek Respect 🔥',
                    'lottery_ticket': '14-Daagse Loterij Ticket 🎟️',
                    'mask': '+1 Bivakmuts / Masker 🎭',
                    'vault_code': 'Geheime Kluiscode Voucher 🔐',
                    'true_crime': 'True Crime Dossier Wachttijd',
                    'cooldown_true_crime': 'True Crime Dossier Wachttijd',
                    'cooldown:true_crime': 'True Crime Dossier Wachttijd',
                    'cooldown_connect_four': 'Vier op een Rij Wachttijd Reset',
                    'cooldown:connect_four': 'Vier op een Rij Wachttijd Reset',
                    'cooldown_memory': 'Memory Wachttijd Reset',
                    'cooldown:memory': 'Memory Wachttijd Reset',
                    'cooldown': 'Alle Cooldowns Wissen ⚡',
                    'cooldown:produce_sigaretten': 'Sigaretten Cooldown Wissen ⚡',
                    'cooldown:produce_ritalin': 'Ritalin Cooldown Wissen ⚡',
                    'cooldown:produce_telefoon': 'Telefoon Cooldown Wissen ⚡',
                    'cooldown:produce_phone': 'Telefoon Cooldown Wissen ⚡'
                };
                return titles[type] || 'Sponsor Beloning Claimen';
            },

            async open(rewardType = 'cash', callback = null) {
                this.clearWatchTimer();
                this.rewardType = rewardType || 'cash';
                this.rewardTitle = this.getRewardTitle(this.rewardType);
                this.onCompleteCallback = typeof callback === 'function' ? callback : null;
                this.isOpen = true;
                this.state = 'starting';
                this.errorMsg = '';
                this.successMsg = '';
                this.token = null;
                this.robinToken = null;
                this.robinRewardId = null;
                this.totalSeconds = 15;
                this.secondsLeft = 15;

                try {
                    const response = await fetch('{{ route("sponsor.start") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({ reward_type: this.rewardType })
                    });

                    const data = await response.json();
                    if (!response.ok || !data.success) {
                        this.state = 'error';
                        this.errorMsg = data.message || 'Kon de sponsor-sessie niet starten.';
                        return;
                    }

                    this.token = data.token;
                    this.robinRewardId = data.robin_reward_id || null;
                    this.totalSeconds = Number(data.duration) > 0 ? Number(data.duration) : 15;
                    this.secondsLeft = this.totalSeconds;

                    this.startWatchFlow();
                } catch (e) {
                    this.state = 'error';
                    this.errorMsg = 'Netwerkfout bij het verbinden met de Sponsorkamer. Controleer je verbinding en probeer het opnieuw.';
                }
            },

            startWatchFlow() {
                this.state = 'watching';
                this.clearWatchTimer();

                // Start the 15-second countdown timer so mobile and desktop users are never stuck if external SDK has no fill
                this.timerInterval = setInterval(() => {
                    if (this.secondsLeft > 0) {
                        this.secondsLeft--;
                    }
                    if (this.secondsLeft <= 0) {
                        this.clearWatchTimer();
                        if (this.state === 'watching') {
                            this.state = 'ready';
                            this.claim();
                        }
                    }
                }, 1000);

                // Attempt RobinAds interstitial overlay if loaded
                try {
                    if (window.RobinAds && typeof window.RobinAds.showInterstitial === 'function') {
                        window.RobinAds.showInterstitial({
                            websiteId: '{{ config("services.robinads.website_id", "6ad2b8e95f70fee0a42a5496") }}',
                            rewardId: this.robinRewardId,
                            onComplete: (adResult) => {
                                this.robinToken = adResult && adResult.token ? adResult.token : this.token;
                                if (adResult && adResult.impressionId) {
                                    this.robinRewardId = adResult.impressionId;
                                }
                                this.clearWatchTimer();
                                this.secondsLeft = 0;
                                if (this.state === 'watching') {
                                    this.state = 'ready';
                                    this.claim();
                                }
                            },
                            onNoFill: () => {
                                // Keep the 15-second fallback countdown running seamlessly
                            },
                            onError: () => {
                                // Keep the 15-second fallback countdown running seamlessly
                            }
                        });
                    }
                } catch (e) {
                    // Fallback countdown is already running
                }
            },

            async claim() {
                if (this.state === 'claiming' || this.state === 'claimed') {
                    return;
                }
                this.clearWatchTimer();
                this.state = 'claiming';

                try {
                    const response = await fetch('{{ route("sponsor.claim") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({
                            token: this.token,
                            robin_token: this.robinToken,
                            robin_reward_id: this.robinRewardId,
                            reward_type: this.rewardType
                        })
                    });

                    const data = await response.json();
                    if (!response.ok || !data.success) {
                        this.state = 'error';
                        this.errorMsg = data.message || 'Beloning kon niet worden geclaimd.';
                        return;
                    }

                    this.state = 'claimed';
                    this.successMsg = data.reward_details || data.message || 'Beloning succesvol ontvangen!';

                    if (typeof window.tbsToast === 'function') {
                        window.tbsToast(this.successMsg, 'success');
                    }
                    if (typeof window.tbsRefreshStats === 'function') {
                        window.tbsRefreshStats(true);
                    }

                    setTimeout(() => {
                        const cb = this.onCompleteCallback;
                        this.close();
                        if (typeof cb === 'function') {
                            cb(data);
                        } else if (window.Turbo && typeof window.Turbo.visit === 'function') {
                            window.Turbo.visit(window.location.href, { action: 'replace' });
                        } else {
                            window.location.reload();
                        }
                    }, 1000);
                } catch (e) {
                    this.state = 'error';
                    this.errorMsg = 'Er trad een verbindingsfout op bij het claimen. Probeer het opnieuw.';
                }
            },

            close() {
                this.clearWatchTimer();
                this.isOpen = false;
                this.state = 'idle';
            }
        };
    };
    window.tbsOpenSponsorAd = function(rewardType = 'cash', callback = null) {
        window.location.href = '{{ route("sponsor.index") }}';
    };
    </script>
    @else
    <!-- SPONSOR ADS DISABLED - SAFE REDIRECT TO SHOP -->
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
    @endif

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
             class="fixed bottom-20 lg:bottom-4 left-4 right-4 md:left-auto md:right-4 md:max-w-md z-[60] bg-white/95 dark:bg-slate-900/95 backdrop-blur-md border border-slate-200/90 dark:border-slate-800 rounded-2xl p-5 shadow-2xl space-y-3.5 text-slate-800 dark:text-slate-100">
            
            <div class="flex items-start gap-3">
                <div class="w-10 h-10 rounded-xl bg-purple-50 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400 border border-purple-200/80 dark:border-purple-800/70 flex items-center justify-center text-lg shrink-0 shadow-2xs">
                    <i class="fa-solid fa-cookie-bite"></i>
                </div>
                <div class="space-y-1">
                    <div class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full bg-amber-50 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 border border-amber-200/80 dark:border-amber-800/70 text-[9px] font-black uppercase tracking-wider">
                        Hobbyproject &bull; Volledig gratis &bull; Vrijblijvend doneren voor extra voordelen
                    </div>
                    <h4 class="text-sm font-black text-slate-900 dark:text-white tracking-tight">Wij respecteren jouw privacy &amp; cookies</h4>
                    <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                        TBS Kliniek is een particulier hobbyproject en voor iedereen gratis toegankelijk. Wij gebruiken veilige functionele cookies voor inloggen en sessiebeheer. Vrijwillig doneren voor extra voordelen mag altijd om het project te steunen.
                    </p>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row items-center gap-2 pt-1">
                <button type="button" @click="acceptAll()"
                        class="w-full sm:flex-1 py-2 px-3.5 rounded-xl bg-purple-600 hover:bg-purple-700 dark:bg-purple-600 dark:hover:bg-purple-500 text-white font-black text-xs transition-all duration-150 active:scale-95 cursor-pointer shadow-xs text-center">
                    Alles Accepteren
                </button>
                <button type="button" @click="acceptNecessary()"
                        class="w-full sm:flex-1 py-2 px-3.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 border border-slate-200/80 dark:border-slate-700 font-bold text-xs transition-all duration-150 active:scale-95 cursor-pointer text-center">
                    Alleen Noodzakelijk
                </button>
            </div>

            <div class="flex items-center justify-between text-[11px] text-slate-400 dark:text-slate-500 pt-1 border-t border-slate-100 dark:border-slate-800">
                <button type="button" @click="showModal = true" class="text-purple-600 dark:text-purple-400 hover:underline font-bold cursor-pointer">
                    Voorkeuren Aanpassen &raquo;
                </button>
                <a href="{{ route('legal.cookies') }}" class="text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200 hover:underline transition">Lees ons Cookiebeleid</a>
            </div>
        </div>

        <!-- COOKIE PREFERENCES MODAL -->
        <div x-show="showModal" x-cloak
             class="fixed inset-0 z-[80] flex items-center justify-center p-4 pb-24 lg:pb-4 bg-slate-950/80 select-none backdrop-blur-xs">
            
            <div @click.away="showModal = false"
                 class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl max-w-lg w-full max-h-[85dvh] overflow-y-auto p-6 sm:p-7 shadow-2xl space-y-5 text-slate-800 dark:text-slate-100 transform-gpu will-change-transform">
                
                <div class="flex items-center justify-between border-b border-slate-200/80 dark:border-slate-800 pb-3">
                    <div class="flex items-center gap-2.5">
                        <i class="fa-solid fa-cookie-bite text-purple-600 dark:text-purple-400 text-lg"></i>
                        <h3 class="text-base font-black text-slate-900 dark:text-white">Cookie-instellingen Beheren</h3>
                    </div>
                    <button type="button" @click="showModal = false" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-slate-600 dark:hover:text-white text-lg cursor-pointer transition-colors" aria-label="Sluiten">
                        &times;
                    </button>
                </div>

                <!-- Hobby Project / Donatie Callout -->
                <div class="p-3 bg-amber-50/80 dark:bg-amber-950/40 border border-amber-200/80 dark:border-amber-800/70 rounded-xl text-[11px] text-amber-900 dark:text-amber-200 leading-relaxed space-y-0.5">
                    <div class="font-extrabold uppercase tracking-wider text-[10px] text-amber-800 dark:text-amber-300">
                        Hobbyproject &bull; Volledig gratis &bull; Vrijblijvend doneren voor extra voordelen
                    </div>
                    <p>
                        TBS Kliniek is een hobbymatig project en voor iedereen gratis toegankelijk. Alle optionele bijdragen (zoals VIP, Sterren en koffie) zijn vrijwillige donaties ter dekking van server- en hostingkosten en bieden leuke extra voordelen.
                    </p>
                </div>

                <div class="space-y-3.5 max-h-80 overflow-y-auto text-xs pr-1">
                    <!-- 1. Noodzakelijk -->
                    <div class="p-3.5 rounded-xl bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70 flex items-start justify-between gap-3">
                        <div class="space-y-0.5">
                            <h5 class="font-bold text-slate-900 dark:text-white">Noodzakelijke Cookies</h5>
                            <p class="text-slate-500 dark:text-slate-400 text-[11px]">Vereist voor inloggen, sessiebeheer en CSRF-beveiliging. Kunnen niet worden uitgeschakeld.</p>
                        </div>
                        <input type="checkbox" checked disabled class="mt-1 rounded border-slate-300 dark:border-slate-600 text-purple-600 bg-slate-200 dark:bg-slate-700 cursor-not-allowed">
                    </div>

                    <!-- 2. Functioneel -->
                    <div class="p-3.5 rounded-xl bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70 flex items-start justify-between gap-3">
                        <div class="space-y-0.5">
                            <h5 class="font-bold text-slate-900 dark:text-white">Functionele Voorkeuren</h5>
                            <p class="text-slate-500 dark:text-slate-400 text-[11px]">Onthoudt interface-instellingen, sidebar-status en helpbot gesprekken.</p>
                        </div>
                        <input type="checkbox" x-model="prefs.functional" class="mt-1 rounded border-slate-300 dark:border-slate-600 text-purple-600 focus:ring-purple-500/20 cursor-pointer">
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-200/80 dark:border-slate-800">
                    <button type="button" @click="acceptAll()"
                            class="py-2 px-3.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 border border-slate-200/80 dark:border-slate-700 text-xs font-bold transition-all duration-150 active:scale-95 cursor-pointer">
                        Alles Accepteren
                    </button>
                    <button type="button" @click="savePreferences()"
                            class="py-2 px-4 rounded-xl bg-purple-600 hover:bg-purple-700 dark:bg-purple-600 dark:hover:bg-purple-500 text-white text-xs font-black transition-all duration-150 active:scale-95 cursor-pointer shadow-xs">
                        Voorkeuren Opslaan
                    </button>
                </div>

            </div>
        </div>

    </div>
