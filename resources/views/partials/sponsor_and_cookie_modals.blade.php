    <!-- GLOBAL REWARDED SPONSOR AD WATCH MODAL (Adsterra Smartlink & Anti-abuse Validation) -->
    <script>
    function tbsSponsorAdModal() {
        return {
        isOpen: false,
        isStarting: false,
        rewardType: 'cash',
        rewardTitle: '€ 2.500 Zakgeld',
        rewardIcon: 'fa-money-bill-wave',
        status: 'idle', // 'loading', 'watching_interstitial', 'claiming', 'claimed', 'adblock_detected', 'error'
        statusMessage: '',
        secondsLeft: 15,
        totalSeconds: 15,
        token: null,
        smartlinkUrl: '',
        robinRewardId: null,
        callback: null,
        async checkAdblock() {
            try {
                const bait = document.createElement('div');
                bait.className = 'pub_300x250 pub_300x250m pub_728x90 text-ad textAd text_ad text_ads text-ads text-ad-links banner-ad ad-unit';
                bait.style.cssText = 'width: 1px !important; height: 1px !important; position: absolute !important; left: -10000px !important; top: -10000px !important;';
                document.body.appendChild(bait);
                const isBlocked = (bait.offsetParent === null || bait.offsetHeight === 0 || window.getComputedStyle(bait).display === 'none');
                bait.remove();
                if (isBlocked) return true;

                // Secondary test: network fetch to standard ad script
                const netCheck = await fetch('https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js', { method: 'HEAD', mode: 'no-cors' }).catch(() => true);
                return netCheck === true;
            } catch(e) {
                return false;
            }
        },

        ensureRobinAdsSdk() {
            if (window.RobinAds && typeof window.RobinAds.showInterstitial === 'function') {
                return Promise.resolve(window.RobinAds);
            }
            return new Promise((resolve) => {
                let script = document.querySelector('script[src*="robinsads.com/ads.js"]');
                if (!script) {
                    script = document.createElement('script');
                    script.src = 'https://robinsads.com/ads.js';
                    script.async = true;
                    // Do NOT add data-website-id on script tag, to prevent automatic site-wide banners!
                    document.head.appendChild(script);
                }
                let count = 0;
                const interval = setInterval(() => {
                    count++;
                    if (window.RobinAds && typeof window.RobinAds.showInterstitial === 'function') {
                        clearInterval(interval);
                        resolve(window.RobinAds);
                    } else if (count >= 20) {
                        clearInterval(interval);
                        resolve(window.RobinAds || null);
                    }
                }, 100);
            });
        },
        init() {
            window.tbsOpenSponsorAd = async (rewardType = 'cash', callback = null) => {
                if (this.isStarting) return;
                this.isStarting = true;
                this.isOpen = true;

                // Normalize reward type aliases
                const typeAliases = {
                    'bullet': 'bullets',
                    'kogels': 'bullets',
                    'kogel': 'bullets',
                    'kogelvest': 'vest',
                    'kogel_vest': 'vest',
                    'armor': 'vest',
                    'coma': 'coma_revive',
                    'ic': 'coma_revive',
                    'intensive_care': 'coma_revive',
                    'revive': 'coma_revive',
                    'stars': 'star'
                };
                rewardType = typeAliases[rewardType] || rewardType;
                this.rewardType = rewardType;
                this.callback = callback;
                this.status = 'loading';
                this.statusMessage = 'Sponsor video-advertentie in het scherm laden...';
                this.secondsLeft = 15;
                this.totalSeconds = 15;

                if (rewardType === 'bullets') {
                    this.rewardTitle = '+50 Scherpe Kogels 💣 (3 Ads)';
                    this.rewardIcon = 'fa-bomb';
                } else if (rewardType === 'vest') {
                    this.rewardTitle = '12u Kogelwerend Vest 🛡️ (3 Ads)';
                    this.rewardIcon = 'fa-shield-halved';
                } else if (rewardType === 'coma_revive') {
                    this.rewardTitle = 'Ontwaken uit Coma 💉 (3 Ads)';
                    this.rewardIcon = 'fa-heart-pulse';
                } else if (rewardType === 'prison') {
                    this.rewardTitle = 'Directe Vrijlating Isoleercel (1x per dag)';
                    this.rewardIcon = 'fa-unlock-keyhole';
                } else if (rewardType === 'respect') {
                    this.rewardTitle = '+1 Respect 🔥';
                    this.rewardIcon = 'fa-fire';
                } else if (rewardType === 'vault_code') {
                    this.rewardTitle = 'Geheime Kluiscode Voucher (3 Ads)';
                    this.rewardIcon = 'fa-key';
                } else if (rewardType === 'true_crime' || rewardType === 'cooldown:true_crime') {
                    this.rewardTitle = '🔍 Caroline Cold Case Dossier (5 Ads)';
                    this.rewardIcon = 'fa-fingerprint';
                } else if (rewardType === 'cooldown' || rewardType.startsWith('cooldown:')) {
                    if (rewardType === 'cooldown:produce_sigaretten') {
                        this.rewardTitle = '⚡ Reset Sigaretten Cooldown';
                    } else if (rewardType === 'cooldown:produce_ritalin') {
                        this.rewardTitle = '⚡ Reset Ritalin Cooldown';
                    } else if (rewardType === 'cooldown:produce_telefoon' || rewardType === 'cooldown:produce_phone') {
                        this.rewardTitle = '⚡ Reset Telefoon Cooldown';
                    } else {
                        this.rewardTitle = '⚡ Reset Cooldown';
                    }
                    this.rewardIcon = 'fa-bolt';
                } else if (rewardType === 'mask') {
                    this.rewardTitle = 'Bivakmuts / Masker (3 Ads)';
                    this.rewardIcon = 'fa-masks-theater';
                } else if (rewardType === 'daily_xp') {
                    this.rewardTitle = '+50 XP (Dagelijks - 3 Ads)';
                    this.rewardIcon = 'fa-bolt';
                } else if (rewardType === 'daily_cash') {
                    this.rewardTitle = '€ 50.000 Cash (Dagelijks - 3 Ads)';
                    this.rewardIcon = 'fa-sack-dollar';
                } else if (rewardType === 'daily_stars') {
                    this.rewardTitle = '5 Sterren ⭐ (Dagelijks - 2 Ads)';
                    this.rewardIcon = 'fa-star';
                } else if (rewardType === 'lottery_ticket') {
                    this.rewardTitle = 'Gratis Loterij Ticket (15 Ads)';
                    this.rewardIcon = 'fa-ticket';
                } else if (rewardType === 'star') {
                    this.rewardTitle = '+1 Gratis Ster ⭐';
                    this.rewardIcon = 'fa-star';
                } else {
                    this.rewardTitle = '€ 2.500 Contant Zakgeld';
                    this.rewardIcon = 'fa-money-bill-wave';
                }

                try {
                    const res = await fetch('{{ route('sponsor.start') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ reward_type: this.rewardType })
                    });
                    const data = await res.json();

                    if (!data.success) {
                        this.status = 'error';
                        this.statusMessage = data.message || 'Kon advertentie niet starten.';
                        return;
                    }

                    this.token = data.token;
                    this.smartlinkUrl = data.smartlink_url;
                    this.robinRewardId = data.robin_reward_id;
                    this.secondsLeft = data.duration || 15;
                    this.totalSeconds = data.duration || 15;

                    // Alleen fullscreen interstitial advertentie tonen (geen dubbele ad in de popup)
                    const robinSdk = await this.ensureRobinAdsSdk();
                    if (robinSdk && typeof robinSdk.showInterstitial === 'function') {
                        this.status = 'watching_interstitial';
                        this.statusMessage = 'Fullscreen video-advertentie wordt afgespeeld...';
                        const startTime = Date.now();

                        try {
                            const result = await robinSdk.showInterstitial({ websiteId: '6ad2b8e95f70fee0a42a5496' });
                            const elapsedSec = (Date.now() - startTime) / 1000;

                            // Als de sponsor de weergave heeft goedgekeurd of de speler minimaal 12 seconden fullscreen heeft gekeken
                            if ((result && result.rewardGranted) || elapsedSec >= 12) {
                                this.claim(result?.token || null, result?.rewardId || null, data.token);
                                return;
                            } else {
                                this.status = 'error';
                                this.statusMessage = 'De advertentie is te vroeg gesloten. Bekijk de fullscreen video volledig om je beloning te ontvangen.';
                                return;
                            }
                        } catch (interstitialErr) {
                            console.warn('RobinAds fullscreen interstitial error or closed:', interstitialErr);
                            const elapsedSec = (Date.now() - startTime) / 1000;
                            if (elapsedSec >= 12) {
                                this.claim(null, null, data.token);
                                return;
                            }
                            const isBlocked = await this.checkAdblock();
                            if (isBlocked) {
                                this.status = 'adblock_detected';
                                return;
                            }
                            this.status = 'error';
                            this.statusMessage = 'Er kon geen fullscreen advertentie geladen worden. Probeer het over een ogenblik opnieuw.';
                        }
                    } else {
                        const isBlocked = await this.checkAdblock();
                        if (isBlocked) {
                            this.status = 'adblock_detected';
                            return;
                        }
                        this.status = 'error';
                        this.statusMessage = 'Advertentienetwerk is tijdelijk niet beschikbaar. Probeer het over een ogenblik opnieuw.';
                    }
                } catch (err) {
                    this.status = 'error';
                    this.statusMessage = 'Er is een netwerkfout opgetreden bij het laden van de sponsor.';
                } finally {
                    this.isStarting = false;
                }
            };
        },
        claim(robinToken = null, robinRewardId = null, fallbackToken = null) {
            this.status = 'claiming';
            this.statusMessage = 'Volledige advertentieweergave verifiëren bij de sponsor...';
            const activeToken = this.token || fallbackToken || '';

            fetch('{{ route('sponsor.claim') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ 
                    token: activeToken,
                    robin_token: robinToken,
                    robin_reward_id: robinRewardId || this.robinRewardId,
                    reward_type: this.rewardType
                })
            })
            .then(res => res.json())
            .then(data => {
                if (!data.success) {
                    this.status = 'error';
                    this.statusMessage = data.message || 'Kon beloning niet verifiëren. Bekijk de advertentie volledig.';
                    return;
                }

                this.status = 'claimed';
                this.statusMessage = data.message;

                setTimeout(() => {
                    this.isOpen = false;
                    if (typeof this.callback === 'function') {
                        this.callback(data);
                    } else {
                        // Return seamlessly to the exact same page
                        window.location.reload();
                    }
                }, 1600);
            })
            .catch(err => {
                this.status = 'error';
                this.statusMessage = 'Netwerkfout bij claimen van beloning.';
            });
        },
        closeModal() {
            this.isOpen = false;
            this.isStarting = false;
        }
    };
}
</script>

<div x-data="tbsSponsorAdModal()"
     x-show="isOpen"
     x-cloak
     class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 select-none">
        
        <div class="bg-white border border-purple-200 rounded-3xl p-6 sm:p-8 w-full max-w-md shadow-2xl relative overflow-hidden text-center space-y-5 transition-all transform-gpu will-change-transform">
            
            <!-- Top Badges -->
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-purple-50 text-purple-700 text-[11px] font-black border border-purple-200">
                    <i class="fa-solid fa-tv text-purple-600"></i> TBS Sponsorkamer (RobinAds)
                </div>
                <button type="button" @click="closeModal()" class="text-slate-400 hover:text-slate-600 text-lg transition cursor-pointer p-1">
                    &times;
                </button>
            </div>

            <!-- Reward Display Box -->
            <div class="p-4 rounded-2xl bg-gradient-to-br from-purple-50 to-indigo-50/50 border border-purple-100 space-y-2">
                <div class="w-14 h-14 mx-auto rounded-2xl bg-white text-purple-600 border border-purple-200 flex items-center justify-center text-2xl shadow-xs">
                    <i class="fa-solid" :class="rewardIcon"></i>
                </div>
                <div>
                    <span class="text-[10px] font-extrabold uppercase tracking-wider text-purple-600">Jouw Gekozen Beloning</span>
                    <h3 class="text-lg font-black text-slate-900" x-text="rewardTitle"></h3>
                </div>
            </div>

            <!-- LOADING SPINNER -->
            <template x-if="status === 'loading'">
                <div class="py-6 space-y-3">
                    <div class="w-10 h-10 border-4 border-purple-200 border-t-purple-600 rounded-full animate-spin mx-auto"></div>
                    <p class="text-xs text-slate-600 font-bold" x-text="statusMessage"></p>
                </div>
            </template>

            <!-- WATCHING INTERSTITIAL NOTICE -->
            <template x-if="status === 'watching_interstitial'">
                <div class="py-4 space-y-3">
                    <div class="w-12 h-12 mx-auto rounded-2xl bg-purple-100 text-purple-600 flex items-center justify-center text-xl animate-pulse">
                        <i class="fa-solid fa-play"></i>
                    </div>
                    <p class="text-xs text-slate-700 font-bold leading-relaxed" x-text="statusMessage"></p>
                    <span class="text-[11px] text-slate-400 block">Kijk de fullscreen video advertentie volledig tot het einde om je beloning te ontgrendelen.</span>
                </div>
            </template>

            <!-- CLAIMING SPINNER -->
            <template x-if="status === 'claiming'">
                <div class="py-6 space-y-3">
                    <div class="w-10 h-10 border-4 border-purple-200 border-t-purple-600 rounded-full animate-spin mx-auto"></div>
                    <p class="text-xs text-slate-600 font-bold" x-text="statusMessage"></p>
                </div>
            </template>

            <!-- CLAIMED CELEBRATION -->
            <template x-if="status === 'claimed'">
                <div class="py-4 space-y-3 animate-fade-in">
                    <div class="w-14 h-14 mx-auto rounded-full bg-emerald-500 text-white flex items-center justify-center text-2xl shadow-lg shadow-emerald-500/30">
                        <i class="fa-solid fa-trophy"></i>
                    </div>
                    <h4 class="text-base font-black text-emerald-800">Gelukt!</h4>
                    <p class="text-xs text-slate-700 font-semibold" x-text="statusMessage"></p>
                    <span class="text-[11px] text-slate-400 block">Je wordt direct teruggebracht naar de pagina...</span>
                </div>
            </template>

            <!-- ADBLOCK DETECTED STATE -->
            <template x-if="status === 'adblock_detected'">
                <div class="space-y-4 animate-fade-in">
                    <div class="w-14 h-14 mx-auto rounded-2xl bg-amber-100 text-amber-700 border border-amber-200 flex items-center justify-center text-2xl shadow-xs">
                        <i class="fa-solid fa-shield-halved"></i>
                    </div>
                    <div>
                        <h4 class="text-base font-black text-slate-900">Adblocker Actief</h4>
                        <p class="text-xs text-slate-600 mt-1 leading-relaxed">
                            Advertenties houden de <strong>TBS Kliniek gratis online</strong> voor iedereen. Schakel je adblocker uit voor deze website of zet <code>tbs-kliniek.nl</code> op de whitelist om advertenties te bekijken en beloningen te claimen!
                        </p>
                    </div>
                    <div class="pt-2 flex flex-col gap-2">
                        <button type="button" @click="window.tbsOpenSponsorAd(rewardType, callback)" 
                                class="w-full py-3 px-4 rounded-xl bg-purple-600 hover:bg-purple-700 active:scale-95 text-white font-extrabold text-xs shadow-xs transition flex items-center justify-center gap-2 cursor-pointer">
                            <i class="fa-solid fa-rotate-right"></i>
                            <span>Ik heb hem uitgeschakeld (Opnieuw proberen)</span>
                        </button>
                        <button type="button" @click="closeModal()" class="w-full py-2 px-4 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-bold transition">
                            Sluiten
                        </button>
                    </div>
                </div>
            </template>

            <!-- ERROR STATE -->
            <template x-if="status === 'error'">
                <div class="space-y-4">
                    <div class="w-12 h-12 mx-auto rounded-full bg-rose-100 text-rose-600 flex items-center justify-center text-xl">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                    </div>
                    <p class="text-xs text-rose-700 font-bold" x-text="statusMessage"></p>
                    <div class="flex gap-2">
                        <button type="button" @click="window.tbsOpenSponsorAd(rewardType, callback)" class="flex-1 py-2.5 px-4 rounded-xl bg-purple-600 hover:bg-purple-700 text-white text-xs font-bold transition">
                            Opnieuw Proberen
                        </button>
                        <button type="button" @click="closeModal()" class="py-2.5 px-4 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition">
                            Sluiten
                        </button>
                    </div>
                </div>
            </template>

        </div>
    </div>

    <!-- GLOBAL AVG / GDPR INTERACTIEVE COOKIE BANNER & VOORKEUREN MODAL -->
    <div x-data="{
        showBanner: false,
        showModal: false,
        prefs: {
            necessary: true,
            functional: true,
            marketing: true
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
            this.prefs = { necessary: true, functional: true, marketing: true };
            this.savePreferences();
        },
        acceptNecessary() {
            this.prefs = { necessary: true, functional: false, marketing: false };
            this.savePreferences();
        },
        savePreferences() {
            localStorage.setItem('tbs_cookie_consent', JSON.stringify(this.prefs));
            this.showBanner = false;
            this.showModal = false;
        }
    }">

        <!-- FLOATING BOTTOM COOKIE CONSENT BANNER (Website Style: Clean Light Card) -->
        <div x-show="showBanner" x-cloak
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-8"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 translate-y-8"
             class="fixed bottom-4 left-4 right-4 md:left-auto md:right-4 md:max-w-md z-40 bg-white/95 backdrop-blur-md border border-slate-200 rounded-2xl p-5 shadow-2xl space-y-3.5 text-slate-800">
            
            <div class="flex items-start gap-3">
                <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 border border-purple-200 flex items-center justify-center text-lg shrink-0 shadow-2xs">
                    <i class="fa-solid fa-cookie-bite"></i>
                </div>
                <div class="space-y-1">
                    <h4 class="text-sm font-black text-slate-900 tracking-tight">Wij respecteren jouw privacy & cookies</h4>
                    <p class="text-xs text-slate-500 leading-relaxed">
                        TBS Kliniek gebruikt functionele cookies voor inloggen en beveiliging, en optionele advertentiecookies van partners zoals Google AdSense en de gratis Sponsorkamer. Conform de AVG bepalen wij dit samen met jou.
                    </p>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row items-center gap-2 pt-1">
                <button type="button" @click="acceptAll()"
                        class="w-full sm:flex-1 py-2 px-3.5 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-extrabold text-xs transition cursor-pointer shadow-xs text-center">
                    Alles Accepteren
                </button>
                <button type="button" @click="acceptNecessary()"
                        class="w-full sm:flex-1 py-2 px-3.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition cursor-pointer text-center">
                    Alleen Noodzakelijk
                </button>
            </div>

            <div class="flex items-center justify-between text-[11px] text-slate-400 pt-1 border-t border-slate-100">
                <button type="button" @click="showModal = true" class="text-purple-600 hover:underline font-bold cursor-pointer">
                    Voorkeuren Aanpassen &raquo;
                </button>
                <a href="{{ route('legal.cookies') }}" class="hover:underline">Lees ons Cookiebeleid</a>
            </div>
        </div>

        <!-- COOKIE PREFERENCES MODAL (AVG Conform) -->
        <div x-show="showModal" x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 select-none">
            
            <div @click.away="showModal = false"
                 class="bg-white border border-slate-200 rounded-3xl p-6 sm:p-7 max-w-lg w-full shadow-2xl space-y-5 text-slate-800 transform-gpu will-change-transform">
                
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <div class="flex items-center gap-2.5">
                        <i class="fa-solid fa-cookie-bite text-purple-600 text-lg"></i>
                        <h3 class="text-base font-black text-slate-900">Cookie-instellingen Beheren</h3>
                    </div>
                    <button type="button" @click="showModal = false" class="text-slate-400 hover:text-slate-600 text-lg cursor-pointer p-1">
                        &times;
                    </button>
                </div>

                <div class="space-y-3.5 max-h-80 overflow-y-auto text-xs pr-1">
                    
                    <!-- 1. Noodzakelijk -->
                    <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200 flex items-start justify-between gap-3">
                        <div class="space-y-0.5">
                            <h5 class="font-bold text-slate-900">Noodzakelijke Cookies</h5>
                            <p class="text-slate-500 text-[11px]">Vereist voor inloggen, sessiebeheer en CSRF-beveiliging. Kunnen niet worden uitgeschakeld.</p>
                        </div>
                        <input type="checkbox" checked disabled class="mt-1 rounded text-purple-600 cursor-not-allowed">
                    </div>

                    <!-- 2. Functioneel -->
                    <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200 flex items-start justify-between gap-3">
                        <div class="space-y-0.5">
                            <h5 class="font-bold text-slate-900">Functionele Voorkeuren</h5>
                            <p class="text-slate-500 text-[11px]">Onthoudt interface-instellingen, menu-status en helpbot gesprekken.</p>
                        </div>
                        <input type="checkbox" x-model="prefs.functional" class="mt-1 rounded text-purple-600 focus:ring-purple-500 cursor-pointer">
                    </div>

                    <!-- 3. Advertenties / Sponsoren -->
                    <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200 flex items-start justify-between gap-3">
                        <div class="space-y-0.5">
                            <h5 class="font-bold text-slate-900">Advertenties & Google AdSense</h5>
                            <p class="text-slate-500 text-[11px]">Maakt het mogelijk om advertenties van Google AdSense en sponsoren te tonen om het spel gratis te houden en beloningen te verdienen.</p>
                        </div>
                        <input type="checkbox" x-model="prefs.marketing" class="mt-1 rounded text-purple-600 focus:ring-purple-500 cursor-pointer">
                    </div>

                </div>

                <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-100">
                    <button type="button" @click="acceptAll()"
                            class="py-2 px-3.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition cursor-pointer">
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

