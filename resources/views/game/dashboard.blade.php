@extends('layouts.app')

@section('content')
@php
    $isOnLeave = $isOnLeave ?? false;
    $leaveRemainingMinutes = $leaveRemainingMinutes ?? 0;
    $treatmentScore = $treatmentScore ?? 50;
    $treatmentCooldown = $treatmentCooldown ?? 0;
    $vaultTokens = $vaultTokens ?? 0;
    $activeVaultCodes = $activeVaultCodes ?? 0;
    $redeemedVaultCodes = $redeemedVaultCodes ?? 0;
    $unopenedBags = $unopenedBags ?? 0;
    $xxlCases = $xxlCases ?? 0;
    $lockerCooldown = $lockerCooldown ?? 0;
    $packageCooldown = $packageCooldown ?? 0;
    $quickMissions = $quickMissions ?? collect();
    $isLoginBonusActive = $isLoginBonusActive ?? false;
    $hasClaimedStreakToday = $hasClaimedStreakToday ?? false;
    $secondsUntilLoginBonus = $secondsUntilLoginBonus ?? 0;
    $secondsRemainingInLoginBonus = $secondsRemainingInLoginBonus ?? 0;
    $nuNlNews = $nuNlNews ?? [];
    $latestNews = $latestNews ?? collect();
@endphp
<!-- Alpine Dashboard Customizer Script (Defined before x-data so initial & Turbo renders always succeed) -->
<script>
window.tbsDashboardCustomizer = function() {
    return {
        showCustomizer: false,
        activeCustomizerTab: 'themes',
        theme: 'clinical',

        weather: @js($clinicWeather ?? null),
        isLocating: false,
        showCityModal: false,
        citySearchQuery: '',
        unlockingKey: null,
        unlockedWidgets: @js(array_values($unlockedWidgets ?? [])),
        widgetPrices: @js($widgetPrices ?? []),
        userStars: {{ (int) ($character->stars ?? 0) }},

        playStation(stId) {
            if (window.tbsRadio) {
                window.tbsRadio.changeStation(stId, true);
            } else {
                window.dispatchEvent(new CustomEvent('tbs-play-station', { detail: { id: stId } }));
            }
        },

        widgets: {
            quickToolbar: true,
            weather: true,
            radio: true,
            nunl: true,
            lottery: true,
            referral: true,
            loginBonus: true,
            attributes: true,
            activities: true,
            clinicNews: true,
            gang: true,
            missions: true,
            tunnel: true,
            treatment: true,
            packages: true,
        },

        widgetList: [
            { key: 'quickToolbar', title: 'Snelle Bajes Actiebalk', desc: 'Directe snelknoppen naar jouw favoriete locaties', icon: 'fa-bolt', bg: 'bg-amber-100 text-amber-700', isPremium: false, cost: 0 },
            { key: 'weather', title: 'Kliniek Weerstation & Buienradar', desc: 'Actueel GPS weer en neerslag buienradar', icon: 'fa-cloud-sun-rain', bg: 'bg-sky-100 text-sky-700', isPremium: true, cost: 15 },
            { key: 'nunl', title: 'Algemeen Buitenwereld Nieuws', desc: 'Actuele krantenkoppen en Teletekst updates', icon: 'fa-newspaper', bg: 'bg-indigo-100 text-indigo-700', isPremium: true, cost: 15 },
            { key: 'radio', title: 'MIXXR Radio Deck', desc: 'In-dashboard speler voor MIXXR Radio & live AutoDJ verzoekjes', icon: 'fa-radio', bg: 'bg-purple-100 text-purple-700', isPremium: true, cost: 20 },
            { key: 'tunnel', title: 'Geheime Ontsnappingstunnel', desc: 'Graafvoortgang, zandsokken legen en uitbraakroute', icon: 'fa-person-digging', bg: 'bg-amber-100 text-amber-700', isPremium: true, cost: 20 },
            { key: 'treatment', title: 'Behandeling & Verloftraject', desc: 'Dossier van Dr. Van Beek, behandelscore en verlof', icon: 'fa-user-doctor', bg: 'bg-teal-100 text-teal-700', isPremium: true, cost: 20 },
            { key: 'packages', title: 'Smokkelkoffers & Postkamer', desc: 'Onderschepte tassen, XXL koffers en kluistokens', icon: 'fa-box-open', bg: 'bg-purple-100 text-purple-700', isPremium: true, cost: 20 },
            { key: 'lottery', title: 'Afdelingsloterij Jackpot', desc: 'De 14-daagse jackpot en trekkingsklok', icon: 'fa-ticket', bg: 'bg-amber-100 text-amber-700', isPremium: false, cost: 0 },
            { key: 'referral', title: 'Gratis VIP & Referral Kaart', desc: 'Vrienden uitnodigen voor gratis VIP lidmaatschap', icon: 'fa-crown', bg: 'bg-purple-100 text-purple-700', isPremium: false, cost: 0 },
            { key: 'loginBonus', title: 'Avond Inlog Bonus (22:00)', desc: '7-daagse streak beloning en gratis kluiscodes', icon: 'fa-gift', bg: 'bg-emerald-100 text-emerald-700', isPremium: false, cost: 0 },
            { key: 'attributes', title: 'Karakter Eigenschappen & Stats', desc: 'Kracht, conditie, intelligentie en discipline', icon: 'fa-dumbbell', bg: 'bg-indigo-100 text-indigo-700', isPremium: false, cost: 0 },
            { key: 'activities', title: 'Recente Activiteiten Feed', desc: 'Log van recente overvallen, missies en winsten', icon: 'fa-clock-rotate-left', bg: 'bg-slate-200 text-slate-700', isPremium: false, cost: 0 },
            { key: 'clinicNews', title: 'TBS Kliniek Berichten', desc: 'Officiële aankondigingen van de inrichting', icon: 'fa-bullhorn', bg: 'bg-purple-100 text-purple-700', isPremium: false, cost: 0 },
            { key: 'gang', title: 'Mijn Bende Kaart', desc: 'Overzicht van je factie, kas en leden', icon: 'fa-users', bg: 'bg-slate-200 text-slate-800', isPremium: false, cost: 0 },
            { key: 'missions', title: 'Snelle Missies Lijst', desc: 'Overzicht van beschikbare missies voor cash & XP', icon: 'fa-list-check', bg: 'bg-purple-100 text-purple-700', isPremium: false, cost: 0 }
        ],

        quickActions: ['crimes', 'arena', 'marketplace', 'radio', 'bank', 'daily'],

        availableActions: [
            { id: 'crimes', label: 'Zakkenrollen', icon: 'fa-mask', url: '{{ route("pickpocket.index") }}', color: 'text-amber-600 bg-amber-50 dark:bg-amber-950/60' },
            { id: 'arena', label: 'Luchtplaats', icon: 'fa-hand-fist', url: '{{ route("fight.index") }}', color: 'text-rose-600 bg-rose-50 dark:bg-rose-950/60' },
            { id: 'marketplace', label: 'Marktplaats', icon: 'fa-store', url: '{{ route("marketplace.index") }}', color: 'text-emerald-600 bg-emerald-50 dark:bg-emerald-950/60' },
            { id: 'packages', label: 'Koffers', icon: 'fa-box-open', url: '{{ route("packages.index") }}', color: 'text-purple-600 bg-purple-50 dark:bg-purple-950/60' },
            { id: 'lockers', label: 'Kluisjes Kraken', icon: 'fa-lock-open', url: '{{ route("lockers.index") }}', color: 'text-blue-600 bg-blue-50 dark:bg-blue-950/60' },
            { id: 'bank', label: 'Bank & Lening', icon: 'fa-building-columns', url: '{{ route("bank.index") }}', color: 'text-indigo-600 bg-indigo-50 dark:bg-indigo-950/60' },
            { id: 'casino', label: 'Casino', icon: 'fa-dice', url: '{{ route("casino.index") }}', color: 'text-yellow-600 bg-yellow-50 dark:bg-yellow-950/60' },
            { id: 'radio', label: 'Afdelingsradio', icon: 'fa-radio', url: '{{ route("radio.index") }}', color: 'text-pink-600 bg-pink-50 dark:bg-pink-950/60' },
            { id: 'daily', label: 'Rad van Fortuin', icon: 'fa-dharmachakra', url: '{{ route("daily.index") }}', color: 'text-purple-600 bg-purple-50 dark:bg-purple-950/60' },
            { id: 'gang', label: 'Mijn Bende', icon: 'fa-users', url: '{{ route("gangs.index") }}', color: 'text-violet-600 bg-violet-50 dark:bg-violet-950/60' }
        ],

        init() {
            const savedTheme = localStorage.getItem('tbs_dashboard_theme');
            if (savedTheme) this.theme = savedTheme;

            const savedWidgets = localStorage.getItem('tbs_dashboard_widgets');
            if (savedWidgets) {
                try {
                    this.widgets = Object.assign({}, this.widgets, JSON.parse(savedWidgets));
                } catch(e) {}
            }

            const savedActions = localStorage.getItem('tbs_dashboard_actions');
            if (savedActions) {
                try {
                    this.quickActions = JSON.parse(savedActions);
                } catch(e) {}
            }

            const savedWeather = localStorage.getItem('tbs_weather_data');
            if (savedWeather && (!this.weather || !this.weather.is_gps)) {
                try {
                    const parsed = JSON.parse(savedWeather);
                    if (parsed && parsed.location) {
                        this.weather = parsed;
                    }
                } catch(e) {}
            }
        },

        isUnlocked(key) {
            const freeWidgets = ['quickToolbar', 'attributes', 'activities', 'lottery', 'referral', 'loginBonus', 'gang', 'missions', 'clinicNews'];
            if (freeWidgets.includes(key)) return true;
            return Array.isArray(this.unlockedWidgets) && this.unlockedWidgets.includes(key);
        },

        async unlockWidgetSubmit(event, key, cost) {
            if (this.unlockingKey) return;
            cost = cost || (this.widgetPrices && this.widgetPrices[key]) || 15;
            if (this.userStars < cost) {
                const msg = 'Je hebt ' + this.userStars + ' sterren, maar deze widget kost ' + cost + ' sterren. Koop of verdien eerst meer sterren!';
                if (window.tbsToast) window.tbsToast(msg, 'error'); else alert(msg);
                return;
            }

            this.unlockingKey = key;
            const form = event ? (event.target && event.target.tagName === 'FORM' ? event.target : event.target.closest('form')) : null;

            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
                const unlockUrl = '{{ route("dashboard.widgets.unlock", [], false) }}';

                const formData = form ? new FormData(form) : new FormData();
                formData.set('widget_key', key);
                formData.set('_token', csrfToken);

                const resp = await fetch(unlockUrl, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                });

                let data = null;
                try {
                    data = await resp.json();
                } catch(e) {}

                if (resp.ok && data && data.success) {
                    if (Array.isArray(data.unlocked_widgets)) {
                        this.unlockedWidgets = [...new Set([...(this.unlockedWidgets || []), ...data.unlocked_widgets])];
                    } else if (Array.isArray(this.unlockedWidgets) && !this.unlockedWidgets.includes(key)) {
                        this.unlockedWidgets.push(key);
                    }
                    this.userStars = data.stars;
                    this.widgets[key] = true;
                    localStorage.setItem('tbs_dashboard_widgets', JSON.stringify(this.widgets));
                    window.dispatchEvent(new CustomEvent('character-stats-updated', { detail: { stars: data.stars } }));

                    const contentEl = document.getElementById('widget-content-' + key);
                    if (contentEl) {
                        contentEl.style.display = '';
                    }

                    const succMsg = data.message || '🎉 Widget succesvol ontgrendeld!';
                    if (window.tbsToast) window.tbsToast(succMsg, 'success'); else alert(succMsg);
                } else {
                    if (form) {
                        form.submit();
                        return;
                    }
                    const errMsg = (data && (data.error || data.message)) || 'Er is een fout opgetreden bij het ontgrendelen.';
                    if (window.tbsToast) window.tbsToast(errMsg, 'error'); else alert(errMsg);
                }
            } catch (e) {
                if (form) {
                    form.submit();
                    return;
                }
                if (window.tbsToast) window.tbsToast('Netwerkfout: ' + (e.message || 'Probeer opnieuw.'), 'error'); else alert('Netwerkfout bij het ontgrendelen.');
            } finally {
                this.unlockingKey = null;
            }
        },

        unlockWidget(key) {
            return this.unlockWidgetSubmit(null, key);
        },

        async fetchUserLocation() {
            this.isLocating = true;

            const tryIpFallback = async (reason) => {
                try {
                    const ipRes = await fetch('https://freeipapi.com/api/json');
                    if (ipRes.ok) {
                        const ipData = await ipRes.json();
                        if (ipData && ipData.latitude && ipData.longitude) {
                            await this.fetchCoordsWeather(ipData.latitude, ipData.longitude, ipData.cityName || ipData.regionName);
                            if (window.tbsToast) {
                                window.tbsToast('📍 Locatie bepaald via IP: ' + (ipData.cityName || 'Nederland'), 'success');
                            }
                            return true;
                        }
                    }
                } catch(e) {}

                try {
                    const ipRes2 = await fetch('https://ipapi.co/json/');
                    if (ipRes2.ok) {
                        const ipData2 = await ipRes2.json();
                        if (ipData2 && ipData2.latitude && ipData2.longitude) {
                            await this.fetchCoordsWeather(ipData2.latitude, ipData2.longitude, ipData2.city || ipData2.region);
                            if (window.tbsToast) {
                                window.tbsToast('📍 Locatie bepaald via IP: ' + (ipData2.city || 'Nederland'), 'success');
                            }
                            return true;
                        }
                    }
                } catch(e) {}

                this.showCityModal = true;
                if (window.tbsToast) {
                    window.tbsToast(reason || 'Kies je woonplaats uit de lijst.', 'info');
                }
                return false;
            };

            if (!navigator.geolocation) {
                await tryIpFallback('Geolocatie wordt niet ondersteund door je browser.');
                this.isLocating = false;
                return;
            }

            navigator.geolocation.getCurrentPosition(
                async (position) => {
                    const lat = position.coords.latitude;
                    const lon = position.coords.longitude;
                    await this.fetchCoordsWeather(lat, lon);
                    this.isLocating = false;
                },
                async (error) => {
                    let reason = 'GPS niet beschikbaar';
                    if (error.code === 1) reason = 'GPS toestemming geweigerd';
                    await tryIpFallback(reason);
                    this.isLocating = false;
                },
                { timeout: 7000, enableHighAccuracy: false }
            );
        },

        async fetchCoordsWeather(lat, lon, locationName) {
            try {
                const weatherUrl = '{{ route("dashboard.weather.coords", [], false) }}';
                const url = `${weatherUrl}?lat=${lat}&lon=${lon}` + (locationName ? `&location=${encodeURIComponent(locationName)}` : '');
                const res = await fetch(url);
                const data = await res.json();
                if (data.success && data.weather) {
                    this.weather = data.weather;
                    localStorage.setItem('tbs_weather_data', JSON.stringify(data.weather));
                    localStorage.setItem('tbs_weather_lat', lat);
                    localStorage.setItem('tbs_weather_lon', lon);
                    if (data.weather.location) {
                        localStorage.setItem('tbs_weather_location', data.weather.location);
                    }
                    if (window.tbsToast) window.tbsToast('🌤️ Weerstation bijgewerkt naar ' + data.weather.location, 'success');
                }
            } catch (err) {
                console.error(err);
                if (window.tbsToast) window.tbsToast('Kon weerdata niet ophalen.', 'error');
            }
        },

        async fetchCityWeather(city) {
            this.isLocating = true;
            this.showCityModal = false;
            try {
                const weatherUrl = '{{ route("dashboard.weather.coords", [], false) }}';
                const res = await fetch(`${weatherUrl}?city=` + encodeURIComponent(city));
                const data = await res.json();
                if (data.success && data.weather) {
                    this.weather = data.weather;
                    localStorage.setItem('tbs_weather_data', JSON.stringify(data.weather));
                    localStorage.setItem('tbs_weather_city', city);
                    if (data.weather.location) {
                        localStorage.setItem('tbs_weather_location', data.weather.location);
                    }
                    if (window.tbsToast) window.tbsToast('🌤️ Weerstation ingesteld op ' + data.weather.location + '!', 'success');
                }
            } catch(e) {
                if (window.tbsToast) window.tbsToast('Kon weer niet ophalen voor ' + city, 'error'); else alert('Kon weer niet ophalen voor ' + city);
            } finally {
                this.isLocating = false;
            }
        },

        async resetToVught() {
            this.isLocating = true;
            try {
                const weatherUrl = '{{ route("dashboard.weather.coords", [], false) }}';
                const res = await fetch(`${weatherUrl}?reset=1`);
                const data = await res.json();
                if (data.success && data.weather) {
                    this.weather = data.weather;
                    localStorage.removeItem('tbs_weather_data');
                    localStorage.removeItem('tbs_weather_lat');
                    localStorage.removeItem('tbs_weather_lon');
                    localStorage.removeItem('tbs_weather_city');
                    localStorage.removeItem('tbs_weather_location');
                    if (window.tbsToast) window.tbsToast('TBS Kliniek Vught weer hersteld.', 'info');
                }
            } catch (e) {
                // ignore
            } finally {
                this.isLocating = false;
            }
        },

        currentThemeName() {
            switch(this.theme) {
                case 'ebi': return 'EBI Vleugel';
                case 'vip': return 'Directiekantoor';
                case 'cyber': return 'Smokkeltunnel';
                case 'orange': return 'Bajes Oranje';
                default: return 'Kliniek Standaard';
            }
        },

        setTheme(t) {
            this.theme = t;
            localStorage.setItem('tbs_dashboard_theme', t);
        },

        toggleWidget(key) {
            this.widgets[key] = !this.widgets[key];
            localStorage.setItem('tbs_dashboard_widgets', JSON.stringify(this.widgets));
        },

        toggleAction(id) {
            if (this.quickActions.includes(id)) {
                if (this.quickActions.length > 2) {
                    this.quickActions = this.quickActions.filter(x => x !== id);
                }
            } else {
                if (this.quickActions.length < 8) {
                    this.quickActions.push(id);
                }
            }
            localStorage.setItem('tbs_dashboard_actions', JSON.stringify(this.quickActions));
        },

        activeActionItems() {
            return this.availableActions.filter(a => this.quickActions.includes(a.id));
        },

        resetDefaults() {
            localStorage.removeItem('tbs_dashboard_theme');
            localStorage.removeItem('tbs_dashboard_widgets');
            localStorage.removeItem('tbs_dashboard_actions');
            this.theme = 'clinical';
            this.widgets = {
                quickToolbar: true,
                weather: true,
                radio: true,
                nunl: true,
                lottery: true,
                referral: true,
                loginBonus: true,
                attributes: true,
                activities: true,
                clinicNews: true,
                gang: true,
                missions: true,
                tunnel: true,
                treatment: true,
                packages: true,
            };
            this.quickActions = ['crimes', 'arena', 'marketplace', 'radio', 'bank', 'daily'];
        }
    };
};
function tbsDashboardCustomizer() {
    return window.tbsDashboardCustomizer();
}
</script>

<div x-data="window.tbsDashboardCustomizer()" 
     x-init="init()"
     class="space-y-6"
     :class="{
         'theme-ebi': theme === 'ebi',
         'theme-vip': theme === 'vip',
         'theme-cyber': theme === 'cyber',
         'theme-orange': theme === 'orange'
     }">

    <!-- Top Dashboard Header with Customizer Trigger -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 pb-1">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center text-base shadow-xs bg-purple-600 text-white"
                 :class="{
                     'bg-purple-600 text-white': !theme || theme === 'clinical',
                     'bg-rose-600 text-white shadow-rose-500/20': theme === 'ebi',
                     'bg-amber-500 text-slate-950 shadow-amber-500/20': theme === 'vip',
                     'bg-cyan-500 text-white shadow-cyan-500/20': theme === 'cyber',
                     'bg-orange-600 text-white shadow-orange-500/20': theme === 'orange'
                 }">
                <i class="fa-solid fa-gauge-high"></i>
            </div>
            <div>
                <h1 class="text-xl font-black text-slate-900 dark:text-white tracking-tight flex flex-wrap items-center gap-2">
                    <span>Mijn Dashboard</span>
                    <span class="text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded-full border bg-purple-50 text-purple-700 border-purple-200 dark:bg-purple-950/60 dark:text-purple-300 dark:border-purple-800"
                          :class="{
                              'bg-purple-50 text-purple-700 border-purple-200 dark:bg-purple-950/60 dark:text-purple-300 dark:border-purple-800': !theme || theme === 'clinical',
                              'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-950/60 dark:text-rose-300 dark:border-rose-800': theme === 'ebi',
                              'bg-amber-50 text-amber-800 border-amber-200 dark:bg-amber-950/60 dark:text-amber-300 dark:border-amber-800': theme === 'vip',
                              'bg-cyan-50 text-cyan-700 border-cyan-200 dark:bg-cyan-950/60 dark:text-cyan-300 dark:border-cyan-800': theme === 'cyber',
                              'bg-orange-50 text-orange-800 border-orange-200 dark:bg-orange-950/60 dark:text-orange-300 dark:border-orange-800': theme === 'orange'
                          }"
                          x-text="currentThemeName()">Kliniek Standaard</span>
                </h1>
                <p class="text-xs text-slate-500 dark:text-slate-400 font-medium">Beheer jouw patiëntencel, dagtaken, buit en kliniekactiviteiten.</p>
            </div>
        </div>

        <!-- Customizer Button -->
        <button type="button" 
                @click="showCustomizer = true"
                class="px-4 py-2 rounded-xl bg-white dark:bg-slate-800/90 border border-slate-200/90 dark:border-slate-700/80 hover:border-purple-400 dark:hover:border-purple-500 text-slate-700 dark:text-purple-200 hover:text-purple-600 dark:hover:text-purple-100 font-bold text-xs transition shadow-2xs flex items-center gap-2 cursor-pointer active:scale-95">
            <i class="fa-solid fa-sliders text-purple-600 dark:text-purple-400"></i>
            <span>Dashboard Inrichten</span>
            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
        </button>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-12 gap-6">

        <!-- Main Content (Left) -->
        <div class="xl:col-span-8 space-y-6">

            <!-- Seizoen 1 Teller Widget (3 Maanden) -->
            @include('partials.season_countdown_widget')

            <!-- SEIZOEN 1 BATTLE PASS BANNER (4 SPOREN) -->
            <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-amber-500/10 via-purple-600/15 to-indigo-600/10 dark:from-amber-950/40 dark:via-purple-950/50 dark:to-indigo-950/40 p-4 sm:p-5 text-slate-900 dark:text-white shadow-md border-2 border-amber-400 dark:border-amber-500/60 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="absolute -right-10 -bottom-10 w-44 h-44 bg-amber-500/10 dark:bg-amber-500/15 rounded-full blur-3xl pointer-events-none"></div>
                <div class="relative z-10 flex items-start gap-3.5">
                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-amber-400 to-amber-600 text-slate-950 flex items-center justify-center text-xl shadow-md shrink-0">
                        <i class="fa-solid fa-trophy"></i>
                    </div>
                    <div class="space-y-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="px-2.5 py-0.5 rounded-full bg-amber-400 text-slate-950 text-[10px] font-black uppercase tracking-wider shadow-2xs">
                                4 Sporen Actief
                            </span>
                            <span class="text-xs font-black text-amber-600 dark:text-amber-400">
                                1x Gratis Spoor + 3x VIP Tiers
                            </span>
                        </div>
                        <h3 class="text-base sm:text-lg font-black tracking-tight text-slate-900 dark:text-white">
                            Seizoen 1 Battle Pass &bull; 20 Niveaus vol Beloningen
                        </h3>
                        <p class="text-xs text-slate-600 dark:text-slate-300 font-medium leading-relaxed">
                            Claim gratis cash, kogels, respect en koffers op basis van je level! Upgrade naar VIP om alle exclusieve VIP Tier tracks tegelijk vrij te spelen.
                        </p>
                    </div>
                </div>
                <div class="relative z-10 shrink-0">
                    <a href="{{ route('battlepass.index') }}" class="btn-star-gold w-full sm:w-auto px-5 py-2.5 rounded-xl active:scale-95 text-slate-900 dark:text-white font-black text-xs uppercase tracking-wider transition shadow-md flex items-center justify-center gap-2 cursor-pointer">
                        <i class="fa-solid fa-gift"></i>
                        <span>Open Battle Pass</span>
                        <i class="fa-solid fa-arrow-right text-[10px]"></i>
                    </a>
                </div>
            </div>

            <!-- Rookie Overlevingstaken & Beloningsspoor voor Beginners -->
            @if(!empty($rookieData))
                @include('partials.rookie_quests_widget')
            @endif
            
            @if(isset($latestNews) && $latestNews->isNotEmpty())
                @php $featuredNews = $latestNews->first(); @endphp
                <!-- PROMINENT NIEUWS HIGHLIGHT BOVENIN DASHBOARD (Licht & Donker Modus) -->
                <div class="relative overflow-hidden rounded-2xl bg-white dark:bg-slate-900 p-5 sm:p-6 text-slate-900 dark:text-white shadow-xs border border-purple-200/90 dark:border-purple-800/80">
                    <div class="absolute -right-8 -bottom-8 w-40 h-40 bg-purple-500/10 rounded-full blur-2xl pointer-events-none"></div>
                    <div class="relative flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 rounded-2xl bg-purple-100 dark:bg-purple-600/30 border border-purple-200 dark:border-purple-400/30 flex items-center justify-center text-2xl shrink-0 shadow-xs">
                                <span>{{ $featuredNews->emoji ?? '🛡️' }}</span>
                            </div>
                            <div class="space-y-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-purple-100 dark:bg-purple-500/20 text-purple-800 dark:text-purple-200 border border-purple-200 dark:border-purple-400/30">
                                        <i class="fa-solid fa-bullhorn text-[9px] text-amber-500 dark:text-amber-300"></i>
                                        Nieuwste Kliniek Update
                                    </span>
                                    <span class="text-[11px] text-slate-500 dark:text-purple-300/80 font-medium font-mono">
                                        {{ $featuredNews->created_at?->diffForHumans() }}
                                    </span>
                                </div>
                                <h3 class="text-base sm:text-lg font-black tracking-tight text-slate-900 dark:text-white hover:text-purple-600 dark:hover:text-purple-200 transition">
                                    <a href="{{ route('news.show', $featuredNews->slug) }}">
                                        {{ $featuredNews->title }}
                                    </a>
                                </h3>
                                <p class="text-xs text-slate-600 dark:text-slate-300 font-medium line-clamp-2 max-w-2xl leading-relaxed">
                                    {{ Str::limit(html_entity_decode(strip_tags($featuredNews->content), ENT_QUOTES, 'UTF-8'), 140) }}
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2.5 shrink-0 pt-2 md:pt-0">
                            <a href="{{ route('news.show', $featuredNews->slug) }}" 
                               class="w-full md:w-auto px-5 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-700 dark:bg-purple-600 dark:hover:bg-purple-500 active:scale-95 text-white font-black text-xs transition shadow-xs flex items-center justify-center gap-2 cursor-pointer">
                                <span>Lees Artikel</span>
                                <i class="fa-solid fa-arrow-right text-[11px]"></i>
                            </a>
                            <a href="{{ route('news.index') }}" 
                                class="hidden sm:inline-flex px-3.5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 font-bold text-xs transition border border-slate-200/80 dark:border-slate-700 items-center justify-center cursor-pointer active:scale-95">
                                <span>Alle Berichten</span>
                            </a>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Welcome Hero Card (Themed) -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xs border transition-all duration-300 overflow-hidden relative"
                 :class="{
                     'border-slate-200/90 dark:border-slate-800': theme === 'clinical',
                     'border-rose-300/80 dark:border-rose-900/60 shadow-rose-950/10': theme === 'ebi',
                     'border-amber-300/80 dark:border-amber-800/60 shadow-amber-950/10': theme === 'vip',
                     'border-cyan-300/80 dark:border-cyan-900/60 shadow-cyan-950/10': theme === 'cyber',
                     'border-orange-300/80 dark:border-orange-900/60 shadow-orange-950/10': theme === 'orange'
                 }">
                
                <div class="relative p-6 sm:p-7 flex flex-col sm:flex-row gap-6 items-center sm:items-start">
                    <!-- Large Avatar with level -->
                    <div class="relative shrink-0">
                        <img src="{{ $character->avatar_url }}" alt="{{ $character->name }}" class="w-28 h-28 sm:w-32 sm:h-32 rounded-2xl object-cover shadow-md border-4 border-white dark:border-slate-800 bg-slate-100 dark:bg-slate-800">
                        <div class="absolute -bottom-2.5 -right-2.5 bg-white dark:bg-slate-800 p-1 rounded-xl shadow-xs border border-slate-100 dark:border-slate-700">
                            <div class="text-white font-black text-xs px-2.5 py-1 rounded-lg flex flex-col items-center leading-none"
                                 :class="{
                                     'bg-purple-600': theme === 'clinical',
                                     'bg-rose-600': theme === 'ebi',
                                     'bg-amber-500 text-slate-950': theme === 'vip',
                                     'bg-cyan-600': theme === 'cyber',
                                     'bg-orange-600': theme === 'orange'
                                 }">
                                <span class="text-[8px] font-black uppercase opacity-90">Level</span>
                                <span class="text-base">{{ $character->level }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="flex-1 w-full text-center sm:text-left">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-1">
                            <h2 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight">Welkom terug, {{ $character->name }}!</h2>
                            <span class="text-xs font-bold text-slate-400">Patiënt #{{ $character->id }}</span>
                        </div>
                        <p class="text-xs text-slate-500 dark:text-slate-400 font-medium mb-5">
                            De kliniek is streng beveiligd, maar jij bouwt hier stap voor stap jouw eigen imperium op.
                        </p>

                        <!-- XP Bar -->
                        @php
                            $lvlService = app(\App\Services\LevelService::class);
                            $reqXp = $lvlService->getXpRequiredForNextLevel($character->level);
                            $pctXp = min(100, max(0, round(($character->xp / max(1, $reqXp)) * 100)));
                        @endphp
                        <div class="space-y-1.5 mb-5">
                            <div class="flex justify-between items-center text-xs">
                                <span class="font-extrabold text-slate-700 dark:text-slate-300 uppercase tracking-wider flex items-center gap-1.5">
                                    <i class="fa-solid fa-shield-halved text-purple-600 dark:text-purple-400 text-[11px]"></i>
                                    <span>Level {{ $character->level }} Voortgang</span>
                                </span>
                                <span class="font-mono font-bold text-slate-800 dark:text-slate-200">
                                    {{ number_format($character->xp, 0, ',', '.') }} / {{ number_format($reqXp, 0, ',', '.') }} XP ({{ $pctXp }}%)
                                </span>
                            </div>
                            <div class="h-2.5 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden border border-slate-200 dark:border-slate-700">
                                <div class="h-full rounded-full transition-all duration-500" 
                                     :class="{
                                         'bg-purple-600': theme === 'clinical',
                                         'bg-rose-600': theme === 'ebi',
                                         'bg-amber-500': theme === 'vip',
                                         'bg-cyan-500': theme === 'cyber',
                                         'bg-orange-500': theme === 'orange'
                                     }"
                                     style="width: {{ $pctXp }}%"></div>
                            </div>
                        </div>

                        <!-- Saldo & Gang Info Directly Inside Hero Card -->
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-left">
                            <!-- Contant Saldo -->
                            <a href="{{ route('bank.index') }}" class="bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70 rounded-xl p-3 hover:border-purple-400 dark:hover:border-purple-500 transition group block shadow-2xs">
                                <div class="flex items-center gap-2 mb-1">
                                    <div class="w-6 h-6 rounded-md bg-amber-100 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center text-[10px]">
                                        <i class="fa-solid fa-coins"></i>
                                    </div>
                                    <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400 dark:text-slate-400">Contant</span>
                                </div>
                                <div class="text-sm sm:text-base font-black text-slate-900 dark:text-white truncate">
                                    €{{ number_format($character->cash, 0, ',', '.') }}
                                </div>
                            </a>

                            <!-- Bank Saldo -->
                            <a href="{{ route('bank.index') }}" class="bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70 rounded-xl p-3 hover:border-indigo-400 dark:hover:border-indigo-500 transition group block shadow-2xs">
                                <div class="flex items-center gap-2 mb-1">
                                    <div class="w-6 h-6 rounded-md bg-indigo-100 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-[10px]">
                                        <i class="fa-solid fa-building-columns"></i>
                                    </div>
                                    <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400 dark:text-slate-400">Bank</span>
                                </div>
                                <div class="text-sm sm:text-base font-black text-slate-900 dark:text-white truncate">
                                    €{{ number_format($character->bank, 0, ',', '.') }}
                                </div>
                            </a>

                            <!-- Bende / Gang Info -->
                            <a href="{{ route('gangs.index') }}" class="bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70 rounded-xl p-3 hover:border-purple-400 dark:hover:border-purple-500 transition group block shadow-2xs">
                                <div class="flex items-center gap-2 mb-1">
                                    <div class="w-6 h-6 rounded-md bg-purple-100 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400 flex items-center justify-center text-[10px]">
                                        <i class="fa-solid fa-users"></i>
                                    </div>
                                    <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400 dark:text-slate-400">Bende</span>
                                </div>
                                <div class="text-sm font-black text-purple-700 dark:text-purple-300 truncate">
                                    {{ $character->gang ? $character->gang->name : 'Geen Bende' }}
                                </div>
                            </a>

                            <!-- TBS Uitkering -->
                            <div class="bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70 rounded-xl p-3 shadow-2xs">
                                <div class="flex items-center gap-2 mb-1">
                                    <div class="w-6 h-6 rounded-md {{ $character->isVip() ? 'bg-purple-100 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400' : 'bg-emerald-100 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400' }} flex items-center justify-center text-[10px]">
                                        <i class="fa-solid {{ $character->isVip() ? 'fa-crown text-amber-500' : 'fa-hand-holding-dollar' }}"></i>
                                    </div>
                                    <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400 dark:text-slate-400">Uitkering</span>
                                </div>
                                <div class="text-sm sm:text-base font-black {{ $character->isVip() ? 'text-purple-600 dark:text-purple-400' : 'text-emerald-600 dark:text-emerald-400' }} truncate">
                                    €{{ number_format($allowanceDetails['amount_per_quarter'] ?? 1000, 0, ',', '.') }} <span class="text-[10px] font-bold text-slate-400">/ 15m</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- FAVORITE QUICK ACTIONS TOOLBAR (Customizable by player) -->
            <div x-show="widgets.quickToolbar" 
                 x-transition
                 class="bg-white dark:bg-slate-900 rounded-2xl p-4 sm:p-5 shadow-xs border border-slate-200/90 dark:border-slate-800">
                <div class="flex items-center justify-between mb-3 pb-2 border-b border-slate-100 dark:border-slate-800">
                    <div class="flex items-center gap-2 text-xs font-black uppercase tracking-wider text-slate-700 dark:text-slate-300">
                        <i class="fa-solid fa-bolt-lightning text-amber-500"></i>
                        <span>Mijn Snelle Bajes Acties</span>
                    </div>
                    <button type="button" @click="activeCustomizerTab = 'actions'; showCustomizer = true" class="text-[11px] font-bold text-purple-600 dark:text-purple-400 hover:underline cursor-pointer">
                        Aanpassen (6 gekozen) &rarr;
                    </button>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-6 gap-2.5">
                    <template x-for="act in activeActionItems()" :key="act.id">
                        <a :href="act.url" 
                           class="p-2.5 rounded-xl border border-slate-100 dark:border-slate-800 hover:border-purple-300 dark:hover:border-purple-700 bg-slate-50/70 dark:bg-slate-800/60 hover:bg-white dark:hover:bg-slate-800 transition flex flex-col items-center text-center gap-2 group shadow-2xs">
                            <div class="w-9 h-9 rounded-xl flex items-center justify-center text-sm shadow-xs transition-transform group-hover:scale-110"
                                 :class="act.color">
                                 <i class="fa-solid" :class="act.icon"></i>
                            </div>
                            <span class="text-[11px] font-bold text-slate-800 dark:text-slate-200 truncate w-full" x-text="act.label"></span>
                        </a>
                    </template>
                </div>
            </div>

            <!-- IN-DASHBOARD RADIO KLINIEK FM WIDGET (Toggleable & Premium) -->
            <div x-show="widgets.radio" 
                 x-transition
                 class="bg-white dark:bg-slate-900 rounded-2xl p-5 shadow-xs border border-purple-200/90 dark:border-purple-900/60 relative overflow-hidden">
                <!-- Locked State -->
                <div x-show="!isUnlocked('radio')" x-cloak @if($character->isWidgetUnlocked('radio')) style="display: none;" @endif class="text-center py-4 space-y-3 bg-gradient-to-b from-rose-500/5 to-transparent">
                    <div class="w-12 h-12 rounded-2xl bg-rose-100 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 flex items-center justify-center mx-auto text-xl shadow-xs border border-rose-200 dark:border-rose-800/80">
                        <i class="fa-solid fa-radio"></i>
                    </div>
                    <div>
                        <h4 class="text-sm font-black text-slate-900 dark:text-white">MIXXR Radio Deck</h4>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 max-w-sm mx-auto">
                            Ontgrendel het officiële MIXXR Radio Deck direct op jouw persoonlijke dashboard. Luister live non-stop en vraag je eigen tracks aan via AutoDJ!
                        </p>
                    </div>
                    <div class="pt-2">
                        <form method="POST" action="{{ route('dashboard.widgets.unlock') }}" class="inline" @submit.prevent="unlockWidgetSubmit($event, 'radio', 20)">
                            @csrf
                            <input type="hidden" name="widget_key" value="radio">
                            <button type="submit" 
                                    :disabled="unlockingKey === 'radio'"
                                    class="btn-star-gold px-5 py-2.5 rounded-xl active:scale-95 text-slate-900 dark:text-white font-black text-xs shadow-md transition cursor-pointer inline-flex items-center gap-2 disabled:opacity-60">
                                <i class="fa-solid" :class="unlockingKey === 'radio' ? 'fa-spinner fa-spin' : 'fa-star text-amber-900 dark:text-amber-200'"></i>
                                <span x-text="unlockingKey === 'radio' ? 'Bezig met ontgrendelen...' : 'Ontgrendel voor 20 Sterren'"></span>
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Unlocked Content -->
                <div id="widget-content-radio" x-show="isUnlocked('radio')" x-cloak @if(!$character->isWidgetUnlocked('radio')) style="display: none;" @endif>
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-100 dark:border-slate-800">
                        <div class="flex items-center gap-3">
                            <div class="w-11 h-11 rounded-2xl bg-purple-50 dark:bg-purple-950/60 border border-purple-200 dark:border-purple-800 text-purple-600 dark:text-purple-400 flex items-center justify-center text-xl shrink-0 shadow-2xs">
                                <i class="fa-solid fa-radio"></i>
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded-full bg-rose-100 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800 animate-pulse">
                                        Live Audio Stream
                                    </span>
                                    <span class="text-xs font-black text-slate-900 dark:text-white">Radio Kliniek FM</span>
                                </div>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                    Luister live naar je favoriete zenders tijdens het spelen op de hele website!
                                </p>
                            </div>
                        </div>

                        <a href="{{ route('radio.index') }}" class="text-xs font-bold text-purple-600 dark:text-purple-400 hover:underline inline-flex items-center gap-1.5 self-start sm:self-center">
                            <i class="fa-solid fa-microphone-lines"></i>
                            <span>Shoutout Sturen (€150)</span>
                        </a>
                    </div>

                    <!-- MIXXR Showcase & AutoDJ Quick Actions -->
                    <div x-data="{
                        autoDjOpen: false,
                        searchQuery: '',
                        visibleLimit: 8,
                        requestingId: null,
                        feedback: null,
                        radioPlaying: !!(window.tbsRadio && window.tbsRadio.isPlaying),
                        radioLoading: !!(window.tbsRadio && window.tbsRadio.isLoading),
                        nowPlaying: (window.tbsRadio && window.tbsRadio.nowPlaying) ? window.tbsRadio.nowPlaying : { title: 'Live Audio Stream', artist: 'MIXXR Radio Non-Stop', cover: 'https://mixxr.nl/album-art.png' },
                        syncRadioState(detail) {
                            if (!detail) return;
                            if (detail.isPlaying !== undefined) this.radioPlaying = detail.isPlaying;
                            if (detail.isLoading !== undefined) this.radioLoading = detail.isLoading;
                            if (detail.nowPlaying) this.nowPlaying = detail.nowPlaying;
                        },
                        getTracks() {
                            const list = window.tbsRadio ? window.tbsRadio.autoDjList : [];
                            if (!this.searchQuery || this.searchQuery.trim() === '') return list;
                            const q = this.searchQuery.toLowerCase().trim();
                            return list.filter(t => 
                                (t.title && t.title.toLowerCase().includes(q)) || 
                                (t.artist && t.artist.toLowerCase().includes(q)) ||
                                (t.genre && t.genre.toLowerCase().includes(q))
                            );
                        },
                        getVisibleTracks() {
                            return this.getTracks().slice(0, this.visibleLimit);
                        },
                        hasMore() {
                            return this.getTracks().length > this.visibleLimit;
                        },
                        requestSong(track) {
                            const tid = track.requestId || track.id;
                            this.requestingId = tid;
                            this.feedback = null;
                            fetch('https://mixxr.nl/api/station/request', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json' },
                                body: JSON.stringify({ requestId: track.requestId, requestUrl: track.requestUrl })
                            })
                            .then(r => r.json())
                            .then(data => {
                                this.requestingId = null;
                                if (data.success) {
                                    this.feedback = { isError: false, message: data.message || 'Verzoeknummer ingepland op MIXXR Radio!' };
                                } else {
                                    this.feedback = { isError: true, message: data.message || 'Kan nummer nu niet aanvragen.' };
                                }
                                setTimeout(() => { this.feedback = null; }, 8000);
                            })
                            .catch(() => {
                                this.requestingId = null;
                                this.feedback = { isError: true, message: 'Verbinding met radio mislukt.' };
                                setTimeout(() => { this.feedback = null; }, 8000);
                            });
                        }
                    }"
                    @tbs-radio-state-changed.window="syncRadioState($event.detail)"
                    class="space-y-3 pt-4">

                        <div class="flex flex-col xl:flex-row xl:items-center justify-between gap-3.5 bg-purple-50/70 dark:bg-purple-950/30 p-3.5 sm:p-4 rounded-2xl border border-purple-200/80 dark:border-purple-900/40 overflow-hidden">
                            <div class="flex items-center gap-2.5 sm:gap-3 min-w-0 flex-1">
                                <div class="w-11 h-11 sm:w-12 sm:h-12 rounded-xl overflow-hidden bg-slate-900 shadow-md border border-purple-300 dark:border-purple-700 shrink-0 relative">
                                    <img :src="(nowPlaying && nowPlaying.cover) ? nowPlaying.cover : 'https://mixxr.nl/album-art.png'" 
                                         alt="MIXXR" 
                                         class="w-full h-full object-cover"
                                         x-on:error="$event.target.src = 'https://mixxr.nl/album-art.png'">
                                </div>
                                <div class="min-w-0 flex-1">
                                    <span class="text-[10px] font-black uppercase tracking-wider text-purple-600 dark:text-purple-400 block truncate"
                                          x-text="(nowPlaying && nowPlaying.artist) ? nowPlaying.artist : 'MIXXR Radio Non-Stop'"></span>
                                    <h4 class="text-xs font-black text-slate-900 dark:text-white truncate" 
                                        x-text="(nowPlaying && nowPlaying.title) ? nowPlaying.title : 'Live Audio Stream'"></h4>
                                    <span class="text-[11px] text-slate-500 dark:text-slate-400 block truncate">24/7 Pop, R&amp;B &amp; House • Vraag tracks aan via AutoDJ!</span>
                                </div>
                            </div>

                            <div class="flex flex-wrap items-center gap-2 shrink-0">
                                <button type="button" 
                                        @click="if (window.tbsRadio) { window.tbsRadio.togglePlay(); }" 
                                        :style="(radioPlaying || radioLoading) ? 'background-color: #e11d48; color: #ffffff;' : 'background-color: #7c3aed; color: #ffffff;'"
                                        class="px-3.5 py-2 rounded-xl active:scale-95 text-white font-bold text-xs uppercase tracking-wider transition cursor-pointer flex items-center justify-center gap-1.5 shadow-md shrink-0"
                                        :class="(radioPlaying || radioLoading) ? 'bg-rose-600 hover:bg-rose-700 shadow-rose-600/30' : 'bg-purple-600 hover:bg-purple-700 shadow-purple-600/30'">
                                    <template x-if="radioLoading">
                                        <i class="fa-solid fa-spinner fa-spin text-xs"></i>
                                    </template>
                                    <template x-if="!radioLoading">
                                        <i class="fa-solid text-xs" :class="radioPlaying ? 'fa-power-off' : 'fa-play'"></i>
                                    </template>
                                    <span x-text="(radioPlaying || radioLoading) ? 'Radio Uitzetten' : 'Luister Live'"></span>
                                </button>
                                <button type="button" 
                                        @click="autoDjOpen = !autoDjOpen; if (autoDjOpen && window.tbsRadio) { window.tbsRadio.loadAutoDj(); }" 
                                        class="px-3 py-2 rounded-xl bg-white dark:bg-purple-950/70 hover:bg-purple-50 dark:hover:bg-purple-900/60 text-purple-700 dark:text-purple-200 font-bold text-xs uppercase tracking-wider border border-purple-200 dark:border-purple-800 transition cursor-pointer flex items-center justify-center gap-1.5 shadow-xs shrink-0">
                                    <i class="fa-solid fa-wand-magic-sparkles text-amber-500 text-xs"></i>
                                    <span x-text="autoDjOpen ? 'Sluit AutoDJ' : 'AutoDJ Verzoekje'"></span>
                                </button>
                                <a href="https://mixxr.nl" target="_blank" rel="noopener noreferrer" class="px-2.5 py-2 rounded-xl bg-purple-100 dark:bg-purple-900/40 hover:bg-purple-200 text-purple-800 dark:text-purple-200 font-bold text-xs uppercase tracking-wider border border-purple-200 dark:border-purple-800 transition flex items-center justify-center gap-1 shrink-0" title="Open officiële website">
                                    <span>MIXXR.nl</span>
                                    <i class="fa-solid fa-arrow-up-right-from-square text-[9px]"></i>
                                </a>
                            </div>
                        </div>

                        <!-- Expandable AutoDJ Search & Track List Panel -->
                        <div x-show="autoDjOpen" 
                             x-cloak
                             x-transition
                             class="p-4 sm:p-5 rounded-2xl bg-white dark:bg-slate-900 border border-purple-200/90 dark:border-purple-900/60 shadow-lg space-y-3.5">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-purple-100 dark:border-purple-900/40 pb-3">
                                <div>
                                    <h4 class="text-sm font-black text-slate-900 dark:text-white flex items-center gap-2">
                                        <i class="fa-solid fa-wand-magic-sparkles text-amber-500"></i>
                                        <span>AutoDJ Muziekdatabase</span>
                                    </h4>
                                    <p class="text-xs text-slate-500 dark:text-purple-300/70 mt-0.5">
                                        Zoek een track en vraag hem direct aan. De radio draait hem binnen enkele minuten live!
                                    </p>
                                </div>
                                <span class="text-xs font-mono font-bold text-purple-600 dark:text-purple-400 bg-purple-50 dark:bg-purple-950/60 px-2.5 py-1 rounded-lg border border-purple-200 dark:border-purple-800/60 self-start sm:self-auto"
                                      x-text="getTracks().length + ' nummers beschikbaar'"></span>
                            </div>

                            <!-- Zoekbalk -->
                            <div class="relative">
                                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-purple-500 text-xs pointer-events-none"></i>
                                <input type="text" 
                                       x-model="searchQuery" 
                                       placeholder="Zoek artiest, titel of genre (bijv. Dua Lipa, Drake, Techno, R&amp;B)..." 
                                       class="w-full pl-9 pr-9 py-2.5 rounded-xl bg-slate-50 dark:bg-purple-950/30 border border-purple-200 dark:border-purple-800 text-xs text-slate-900 dark:text-white placeholder-slate-400 focus:outline-hidden focus:border-purple-500 focus:ring-1 focus:ring-purple-500 transition">
                                <template x-if="searchQuery.length > 0">
                                    <button type="button" @click="searchQuery = ''" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-purple-200 text-xs cursor-pointer">
                                        <i class="fa-solid fa-circle-xmark"></i>
                                    </button>
                                </template>
                            </div>

                            <!-- Feedback melding -->
                            <template x-if="feedback">
                                <div class="p-3 rounded-xl text-xs flex items-center gap-2.5"
                                     :class="feedback.isError ? 'bg-rose-50 dark:bg-rose-950/50 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800' : 'bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800'">
                                    <i class="fa-solid text-sm" :class="feedback.isError ? 'fa-circle-exclamation text-rose-500' : 'fa-circle-check text-emerald-500'"></i>
                                    <span class="font-medium" x-text="feedback.message"></span>
                                </div>
                            </template>

                            <!-- Track Resultaten Grid -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 max-h-80 overflow-y-auto custom-scrollbar pr-1">
                                <template x-if="!window.tbsRadio || (window.tbsRadio.autoDjList.length === 0 && window.tbsRadio.autoDjLoading)">
                                    <div class="col-span-full p-8 text-center text-xs text-slate-400 dark:text-purple-300/60 flex items-center justify-center gap-2">
                                        <i class="fa-solid fa-spinner fa-spin text-purple-500 text-base"></i>
                                        <span>AutoDJ bibliotheek synchroniseren...</span>
                                    </div>
                                </template>

                                <template x-for="track in getVisibleTracks()" :key="track.requestId || track.id">
                                    <div class="p-2.5 rounded-xl bg-slate-50/80 dark:bg-purple-950/30 hover:bg-purple-50/80 dark:hover:bg-purple-950/60 border border-slate-200/80 dark:border-purple-900/40 flex items-center justify-between gap-3 transition">
                                        <div class="flex items-center gap-2.5 min-w-0">
                                            <img :src="track.cover || 'https://mixxr.nl/album-art.png'" 
                                                 alt="Cover" 
                                                 class="w-10 h-10 rounded-lg object-cover bg-slate-900 border border-purple-200 dark:border-purple-800 shrink-0"
                                                 x-on:error="$event.target.src = 'https://mixxr.nl/album-art.png'">
                                            <div class="min-w-0 truncate">
                                                <h5 class="text-xs font-bold text-slate-900 dark:text-white truncate" x-text="track.title"></h5>
                                                <span class="text-[11px] text-slate-500 dark:text-purple-300/70 block truncate" x-text="track.artist"></span>
                                                <span class="text-[9px] font-semibold text-purple-600 dark:text-purple-400 block truncate" x-text="track.genre || 'Pop & R&B'"></span>
                                            </div>
                                        </div>

                                        <button type="button" 
                                                @click="requestSong(track)"
                                                :disabled="requestingId === (track.requestId || track.id)"
                                                class="px-3 py-1.5 rounded-xl bg-purple-600 hover:bg-purple-700 active:scale-95 text-white font-bold text-xs shrink-0 transition cursor-pointer flex items-center gap-1.5 shadow-xs disabled:opacity-50">
                                            <template x-if="requestingId === (track.requestId || track.id)">
                                                <i class="fa-solid fa-spinner fa-spin text-[10px]"></i>
                                            </template>
                                            <template x-if="requestingId !== (track.requestId || track.id)">
                                                <i class="fa-solid fa-paper-plane text-[10px]"></i>
                                            </template>
                                            <span>Vraag aan</span>
                                        </button>
                                    </div>
                                </template>

                                <template x-if="window.tbsRadio && window.tbsRadio.autoDjList.length > 0 && getTracks().length === 0">
                                    <div class="col-span-full p-8 text-center text-xs text-slate-400 dark:text-purple-300/60">
                                        Geen nummers gevonden voor '<span class="font-bold text-slate-600 dark:text-purple-200" x-text="searchQuery"></span>'. Probeer een andere artiest of titel.
                                    </div>
                                </template>
                            </div>

                            <!-- Laad Meer Knop -->
                            <template x-if="hasMore()">
                                <button type="button" 
                                        @click="visibleLimit += 8" 
                                        class="w-full py-2.5 rounded-xl bg-purple-100 dark:bg-purple-950/70 hover:bg-purple-200 dark:hover:bg-purple-900/70 text-purple-700 dark:text-purple-200 text-xs font-bold border border-purple-200 dark:border-purple-800 transition cursor-pointer flex items-center justify-center gap-2">
                                    <i class="fa-solid fa-chevron-down text-[10px]"></i>
                                    <span>Laad meer nummers</span>
                                    <span class="text-[10px] opacity-75 font-mono" x-text="'(' + (getTracks().length - visibleLimit) + ' overig)'"></span>
                                </button>
                            </template>
                        </div>
                    </div>
                </div>
            </div>

            <!-- AFDELINGSLOTERIJ JACKPOT PROMO WIDGET (Toggleable) -->
            @if(isset($lotteryRound))
            <div x-show="widgets.lottery" 
                 x-transition
                 class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-5 sm:p-6 text-slate-900 dark:text-white shadow-xs relative overflow-hidden flex flex-col md:flex-row md:items-center justify-between gap-5">
                <div class="space-y-2 max-w-xl">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-amber-50 dark:bg-amber-950/40 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-700/60">
                            <i class="fa-solid fa-crown text-amber-500"></i> Afdelingsloterij Ronde #{{ $lotteryRound->round_number }}
                        </span>
                        <span class="text-[10px] font-bold text-slate-600 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 px-2 py-0.5 rounded-full border border-slate-200 dark:border-slate-700">
                            1x per 14 dagen
                        </span>
                    </div>
                    <h2 class="text-lg sm:text-xl font-black tracking-tight text-slate-900 dark:text-white flex items-center gap-2">
                        Grijp de Jackpot: <span class="text-amber-600 dark:text-amber-400 font-mono font-black">€{{ number_format($lotteryRound->jackpot, 0, ',', '.') }}</span>
                    </h2>
                    <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed font-medium">
                        Iedere cent opnamebelasting van de bank stroomt rechtstreeks in deze pot. Koop loten met contant geld of sterren en maak kans op de volledige pot bij de trekking!
                    </p>
                </div>

                <div class="flex flex-col sm:flex-row items-center gap-3 shrink-0 self-stretch md:self-center">
                    @php
                        $dashSecondsLeft = ($lotteryRound && $lotteryRound->draws_at) ? max(0, (int) round(now()->diffInSeconds($lotteryRound->draws_at, false))) : 0;
                    @endphp
                    <div class="w-full sm:w-auto p-3 rounded-xl bg-amber-50/70 dark:bg-slate-800/80 border border-amber-200 dark:border-slate-700 text-center shrink-0"
                         x-data="{
                             left: {{ $dashSecondsLeft }},
                             format() {
                                 if (this.left <= 0) return 'Trekking!';
                                 const t = Math.floor(this.left);
                                 const d = Math.floor(t / 86400);
                                 const h = Math.floor((t % 86400) / 3600);
                                 const m = Math.floor((t % 3600) / 60);
                                 const s = Math.floor(t % 60);
                                 return d > 0 ? `${d}d ${h}u ${m}m ${s < 10 ? '0' : ''}${s}s` : `${h}u ${m}m ${s < 10 ? '0' : ''}${s}s`;
                             },
                             init() { setInterval(() => { if (this.left > 0) this.left--; }, 1000); }
                         }">
                        <span class="text-[9px] font-black uppercase tracking-wider text-slate-500 dark:text-slate-400 block">Tijd tot trekking</span>
                        <span class="text-xs font-mono font-black text-slate-900 dark:text-amber-400 mt-0.5 block" x-text="format()">
                            {{ ($lotteryRound && $lotteryRound->draws_at) ? now()->diffForHumans($lotteryRound->draws_at, true) : 'Binnenkort' }}
                        </span>
                    </div>

                    <a href="{{ route('lottery.index') }}" 
                       class="w-full sm:w-auto px-5 py-3 rounded-xl bg-amber-400 hover:bg-amber-300 text-slate-950 font-black text-xs uppercase tracking-wider transition text-center shadow-xs flex items-center justify-center gap-2 shrink-0 active:scale-95 cursor-pointer">
                        <i class="fa-solid fa-ticket"></i>
                        <span>Koop een Lot</span>
                    </a>
                </div>
            </div>
            @endif

            <!-- PERSOONLIJKE REFERRAL & GRATIS VIP CARD (Toggleable) -->
            <div x-show="widgets.referral" 
                 x-transition
                 class="bg-white dark:bg-slate-900 rounded-2xl p-5 sm:p-6 shadow-xs border border-purple-200/90 dark:border-purple-900/60 relative overflow-hidden" 
                 x-data="{ 
                     copied: false,
                     copyLink() {
                         navigator.clipboard.writeText(@js($referralStats['link'] ?? ''));
                         this.copied = true;
                         setTimeout(() => this.copied = false, 2500);
                     }
                 }">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-5 border-b border-slate-100 dark:border-slate-800">
                    <div class="flex items-start gap-3.5">
                        <div class="w-11 h-11 rounded-xl bg-purple-50 dark:bg-purple-950/60 border border-purple-200 dark:border-purple-800 text-purple-600 dark:text-purple-400 flex items-center justify-center text-xl shrink-0 mt-0.5 shadow-2xs">
                            <i class="fa-solid fa-crown text-amber-500"></i>
                        </div>
                        <div>
                            <div class="flex flex-wrap items-center gap-2 mb-1">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-amber-100 dark:bg-amber-950 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
                                    <i class="fa-solid fa-crown text-amber-500 text-[10px]"></i>
                                    Gratis VIP Actie
                                </span>
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-purple-100 dark:bg-purple-950 text-purple-800 dark:text-purple-300 border border-purple-200 dark:border-purple-800">
                                    3 Spelers = 1 Maand VIP
                                </span>
                            </div>
                            <h2 class="text-base sm:text-lg font-black text-slate-900 dark:text-white tracking-tight">
                                Nodig Vrienden Uit & Krijg Gratis VIP
                            </h2>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 max-w-xl leading-relaxed">
                                Deel jouw unieke link. Zodra een vriend level 3 bereikt, telt dit mee voor jouw gratis VIP lidmaatschap en exclusieve bonussen!
                            </p>
                        </div>
                    </div>

                    <button type="button" 
                            @click="copyLink()"
                            class="px-4 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-700 dark:bg-purple-600 dark:hover:bg-purple-500 text-white font-black text-xs transition shadow-xs flex items-center justify-center gap-2 shrink-0 cursor-pointer active:scale-95">
                        <i class="fa-solid" :class="copied ? 'fa-check' : 'fa-link'"></i>
                        <span x-text="copied ? 'Gekopieerd!' : 'Kopieer Link'"></span>
                    </button>
                </div>
            </div>

            <!-- DAGELIJKSE INLOG BONUS & STREAK (Toggleable) -->
            <div x-show="widgets.loginBonus" 
                 x-transition
                 class="bg-white dark:bg-slate-900 rounded-2xl p-4 sm:p-5 shadow-xs border border-slate-200/90 dark:border-slate-800"
                 x-data="{
                     active: {{ !empty($isLoginBonusActive) ? 'true' : 'false' }},
                     claimed: {{ !empty($hasClaimedStreakToday) ? 'true' : 'false' }},
                     secUntilStart: {{ (int) ($secondsUntilLoginBonus ?? 0) }},
                     secRemaining: {{ (int) ($secondsRemainingInLoginBonus ?? 0) }},
                     formatTimer(s) {
                         const m = Math.floor(s / 60);
                         const sec = s % 60;
                         return `${m}m ${sec < 10 ? '0' : ''}${sec}s`;
                     },
                     init() {
                         setInterval(() => {
                             if (this.secUntilStart > 0) this.secUntilStart--;
                             if (this.secRemaining > 0) this.secRemaining--;
                         }, 1000);
                     }
                 }">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                    <div class="flex items-center gap-3.5">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center text-lg shrink-0 shadow-2xs {{ !empty($isLoginBonusActive) ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 dark:bg-slate-800 text-slate-500' }}">
                            <i class="fa-solid fa-gift"></i>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="text-xs font-black uppercase tracking-wider text-slate-900 dark:text-white">Avond Inlog Bonus (22:00 - 22:30)</h3>
                                <template x-if="active && !claimed">
                                    <span class="text-[9px] font-black uppercase px-2 py-0.5 rounded bg-amber-400 text-slate-950 animate-pulse">Nu Open</span>
                                </template>
                            </div>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                Log tussen 22:00 en 22:30 in voor 3 gratis kluiscodes en jouw dagelijkse login streak!
                            </p>
                        </div>
                    </div>

                    <div class="shrink-0 flex items-center gap-2">
                        <template x-if="active && !claimed">
                            <form action="{{ route('daily.claim-streak') }}" method="POST">
                                @csrf
                                <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 dark:bg-emerald-600 dark:hover:bg-emerald-500 text-white font-black text-xs uppercase tracking-wider shadow-xs transition active:scale-95 cursor-pointer">
                                    Claim Dag {{ $nextStreakDay ?? 1 }}
                                </button>
                            </form>
                        </template>
                        <template x-if="!active || claimed">
                            <a href="{{ route('daily.index') }}" class="px-3.5 py-2 rounded-xl bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-950/60 dark:hover:bg-indigo-900/60 border border-indigo-200 dark:border-indigo-800 text-indigo-700 dark:text-indigo-300 font-bold text-xs transition flex items-center gap-1.5 shadow-2xs">
                                <i class="fa-solid fa-calendar-days text-indigo-500"></i>
                                <span>Bekijk Kalender</span>
                            </a>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Karakter Eigenschappen & Actieve Stats (Toggleable) -->
            <div x-show="widgets.attributes" 
                 x-transition
                 class="bg-white dark:bg-slate-900 rounded-2xl shadow-xs border border-slate-200/90 dark:border-slate-800 p-6">
                <div class="flex items-center justify-between mb-4 border-b border-slate-100 dark:border-slate-800 pb-3">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-indigo-500"></span>
                        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">Karakter Eigenschappen & Stats</h3>
                    </div>
                    <a href="{{ route('training.index') }}" class="text-xs font-bold text-indigo-600 dark:text-purple-400 hover:underline flex items-center gap-1 transition">
                        <i class="fa-solid fa-dumbbell text-xs"></i> Trainen in Sportschool &rarr;
                    </a>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-6 gap-3 text-center">
                    @php
                        $attrList = [
                            ['label' => 'Kracht', 'effective' => $character->getEffectiveStrength(), 'icon' => 'fa-hand-fist', 'color' => 'text-rose-500'],
                            ['label' => 'Verdediging', 'effective' => $character->getEffectiveDefense(), 'icon' => 'fa-shield-halved', 'color' => 'text-indigo-500'],
                            ['label' => 'Conditie', 'effective' => $character->getEffectiveStamina(), 'icon' => 'fa-person-running', 'color' => 'text-emerald-500'],
                            ['label' => 'Intelligentie', 'effective' => $character->getEffectiveIntelligence(), 'icon' => 'fa-brain', 'color' => 'text-purple-500'],
                            ['label' => 'Charisma', 'effective' => $character->getEffectiveCharisma(), 'icon' => 'fa-face-smile-wink', 'color' => 'text-amber-500'],
                            ['label' => 'Discipline', 'effective' => $character->getEffectiveDiscipline(), 'icon' => 'fa-scale-balanced', 'color' => 'text-blue-500'],
                        ];
                    @endphp
                    @foreach($attrList as $attr)
                        <div class="p-3 rounded-xl bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70">
                            <i class="fa-solid {{ $attr['icon'] }} {{ $attr['color'] }} text-base mb-1 block"></i>
                            <span class="text-[10px] font-bold text-slate-400 block">{{ $attr['label'] }}</span>
                            <span class="text-sm font-black text-slate-900 dark:text-white">{{ number_format($attr['effective'], 0, ',', '.') }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Recente Activiteiten Feed (Toggleable) -->
            <div x-show="widgets.activities" 
                 x-transition
                 class="bg-white dark:bg-slate-900 rounded-2xl shadow-xs border border-slate-200/90 dark:border-slate-800 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between bg-slate-50/50 dark:bg-slate-800/40">
                    <div class="flex items-center gap-2">
                        <span class="w-6 h-6 rounded-lg bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-xs">
                            <i class="fa-solid fa-clock-rotate-left"></i>
                        </span>
                        <h3 class="text-sm font-extrabold text-slate-900 dark:text-white">Recente activiteiten & buit</h3>
                    </div>
                </div>
                <div class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($recentActivities as $activity)
                        <div class="p-4 hover:bg-slate-50/70 dark:hover:bg-slate-800/50 transition flex items-start gap-4">
                            <div class="w-8 h-8 rounded-full {{ data_get($activity, 'icon_bg', 'bg-indigo-100 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400') }} flex items-center justify-center shrink-0 shadow-2xs">
                                <i class="fa-solid {{ data_get($activity, 'icon', 'fa-clock-rotate-left') }} text-xs"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-bold text-slate-800 dark:text-slate-100 truncate">{{ data_get($activity, 'title', '') }}</p>
                                <p class="text-xs font-bold {{ data_get($activity, 'badge_color', 'text-slate-500') }} mt-0.5">{{ data_get($activity, 'badge', '') }}</p>
                            </div>
                            <div class="text-xs text-slate-400 font-semibold whitespace-nowrap shrink-0">
                                {{ !empty(data_get($activity, 'time')) ? \Carbon\Carbon::parse(data_get($activity, 'time'))->diffForHumans() : '' }}
                            </div>
                        </div>
                    @empty
                        <div class="p-8 text-center text-xs text-slate-400 font-medium">
                            <i class="fa-solid fa-clock-rotate-left text-2xl text-slate-300 dark:text-slate-600 mb-2 block"></i>
                            Nog geen recente activiteiten. Start een missie of hossel!
                        </div>
                    @endforelse
                </div>
            </div>

        </div>

        <!-- Right Sidebar (Col-Span-4) -->
        <div class="xl:col-span-4 space-y-6">

            <!-- 1. LAATSTE KLINIEK NIEUWS (Toggleable) -->
            <div x-show="widgets.clinicNews" 
                 x-transition
                 class="bg-white dark:bg-slate-900 rounded-2xl shadow-xs border border-slate-200/90 dark:border-slate-800 overflow-hidden">
                <div class="px-5 py-3.5 bg-slate-50/70 dark:bg-slate-800/60 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                    <div class="flex items-center gap-2 text-xs font-black tracking-wide text-slate-900 dark:text-white">
                        <span class="w-5 h-5 rounded-md bg-purple-100 dark:bg-purple-950/70 text-purple-700 dark:text-purple-300 flex items-center justify-center text-[10px] border border-purple-200 dark:border-purple-800">
                            <i class="fa-solid fa-newspaper"></i>
                        </span>
                        <span>TBS Kliniek Berichten</span>
                    </div>
                    <a href="{{ route('news.index') }}" class="text-[11px] font-bold text-purple-600 dark:text-purple-400 hover:underline">
                        Alle nieuws &rarr;
                    </a>
                </div>

                <div class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($latestNews ?? [] as $newsItem)
                        @if(is_object($newsItem) && !empty($newsItem->slug))
                        <a href="{{ route('news.show', $newsItem->slug) }}" class="p-3.5 px-5 hover:bg-slate-50/80 dark:hover:bg-slate-800/50 transition block group">
                            <div class="flex items-center justify-between gap-2">
                                <h4 class="text-xs font-bold text-slate-800 dark:text-slate-100 group-hover:text-purple-600 dark:group-hover:text-purple-400 transition truncate">
                                    {{ $newsItem->title }}
                                </h4>
                                <span class="text-[10px] text-slate-400 shrink-0">{{ $newsItem->created_at?->diffForHumans() }}</span>
                            </div>
                        </a>
                        @endif
                    @empty
                        <div class="p-6 text-center text-xs text-slate-400">
                            Nog geen berichten geplaatst.
                        </div>
                    @endforelse
                </div>

                <a href="{{ route('news.index') }}" class="block w-full text-center py-2.5 bg-purple-50/60 dark:bg-purple-950/40 text-[11px] font-extrabold text-purple-700 dark:text-purple-300 hover:bg-purple-100 dark:hover:bg-purple-900/60 border-t border-slate-100 dark:border-purple-900/50 transition">
                    Alle berichten bekijken &rarr;
                </a>
            </div>

            <!-- 2. WEERSTATION & BUIENRADAR WIDGET (Toggleable & Premium) -->
            <div x-show="widgets.weather" 
                 x-transition
                 class="bg-white dark:bg-slate-900 rounded-2xl shadow-xs border border-slate-200/90 dark:border-slate-800 overflow-hidden">
                
                <!-- Locked State -->
                <div x-show="!isUnlocked('weather')" x-cloak @if($character->isWidgetUnlocked('weather')) style="display: none;" @endif class="p-6 text-center space-y-3 bg-gradient-to-b from-amber-500/5 to-transparent">
                    <div class="w-12 h-12 rounded-2xl bg-amber-100 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center mx-auto text-xl shadow-xs border border-amber-200 dark:border-amber-800/80">
                        <i class="fa-solid fa-lock"></i>
                    </div>
                    <div>
                        <h4 class="text-sm font-black text-slate-900 dark:text-white">Kliniek Weerstation & Buienradar</h4>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 max-w-xs mx-auto">
                            Echt live weer via GPS, actuele buienverwachting en luchtplaats status voor jouw locatie!
                        </p>
                    </div>
                    <div class="pt-2">
                        <form method="POST" action="{{ route('dashboard.widgets.unlock') }}" class="inline" @submit.prevent="unlockWidgetSubmit($event, 'weather', 15)">
                            @csrf
                            <input type="hidden" name="widget_key" value="weather">
                            <button type="submit" 
                                    :disabled="unlockingKey === 'weather'"
                                    class="btn-star-gold px-5 py-2.5 rounded-xl active:scale-95 text-slate-900 dark:text-white font-black text-xs shadow-md transition cursor-pointer inline-flex items-center gap-2 disabled:opacity-60">
                                <i class="fa-solid" :class="unlockingKey === 'weather' ? 'fa-spinner fa-spin' : 'fa-star text-amber-900 dark:text-amber-200'"></i>
                                <span x-text="unlockingKey === 'weather' ? 'Bezig met ontgrendelen...' : 'Ontgrendel voor 15 Sterren'"></span>
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Unlocked Live Content -->
                <div id="widget-content-weather" x-show="isUnlocked('weather')" x-cloak @if(!$character->isWidgetUnlocked('weather')) style="display: none;" @endif>
                    <div class="p-4 sm:p-5 bg-gradient-to-br from-slate-900 via-slate-850 to-slate-800 text-white flex items-center justify-between">
                        <div class="min-w-0 pr-3">
                            <div class="flex items-center gap-1.5 mb-1">
                                <span class="text-[10px] font-black uppercase tracking-wider text-amber-400">Live Weerstation</span>
                                <template x-if="weather.is_gps">
                                    <span class="text-[9px] font-extrabold px-1.5 py-0.5 rounded bg-sky-500/30 text-sky-300 border border-sky-400/30">GPS Locatie</span>
                                </template>
                                <template x-if="!weather.is_gps">
                                    <span class="text-[9px] font-extrabold px-1.5 py-0.5 rounded bg-slate-700/60 text-slate-300 border border-slate-600/40">Kliniek Vught</span>
                                </template>
                            </div>
                            <div class="flex items-center gap-1.5 text-slate-100 font-extrabold text-sm sm:text-base">
                                <i class="fa-solid fa-location-dot text-rose-400 shrink-0 text-xs"></i>
                                <span class="text-slate-400 font-medium text-xs">Plaats:</span>
                                <span class="font-black text-white truncate text-sm sm:text-base tracking-tight" x-text="weather.location || 'Vught (TBS Kliniek)'"></span>
                            </div>
                        </div>
                        <div class="text-2xl sm:text-3xl text-amber-400 shrink-0">
                            <i class="fa-solid" :class="weather.icon"></i>
                        </div>
                    </div>

                    <div class="p-4 sm:p-5 space-y-4">
                        <!-- Prominente Plaatsbadge in de widget -->
                        <div class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/80 border border-slate-100 dark:border-slate-700/80 flex items-center justify-between text-xs">
                            <div class="flex items-center gap-2 truncate pr-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 shrink-0 animate-pulse"></span>
                                <span class="text-slate-500 dark:text-slate-400 font-bold text-[11px]">Weerlocatie:</span>
                                <span class="font-black text-slate-900 dark:text-white truncate text-xs" x-text="weather.location || 'Vught (TBS Kliniek)'"></span>
                            </div>
                            <button type="button" 
                                    @click="showCityModal = true"
                                    class="text-[10px] font-black text-sky-600 dark:text-sky-400 hover:underline shrink-0 cursor-pointer flex items-center gap-1">
                                <i class="fa-solid fa-pen text-[9px]"></i>
                                <span>Plaats Wijzigen</span>
                            </button>
                        </div>

                        <div class="flex items-center justify-between">
                            <div>
                                <span class="text-3xl font-black text-slate-900 dark:text-white leading-none font-mono" x-text="weather.temp + '°C'"></span>
                                <span class="text-[11px] text-slate-400 block mt-1" x-text="'Gevoelstemperatuur: ' + weather.feels_like + '°C'"></span>
                            </div>
                            <div class="text-right">
                                <span class="text-xs font-bold text-slate-700 dark:text-slate-200 block" x-text="weather.condition"></span>
                                <span class="text-[10px] text-slate-400 block" x-text="weather.wind"></span>
                            </div>
                        </div>

                        <!-- Real Live Buien & Neerslag Alert -->
                        <div class="p-3 rounded-xl border text-xs font-bold flex items-center gap-2.5 transition"
                             :class="weather.has_buien ? 'bg-blue-50 dark:bg-blue-950/40 text-blue-800 dark:text-blue-300 border-blue-200 dark:border-blue-800' : 'bg-slate-50 dark:bg-slate-800/60 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-700'">
                            <span class="text-base shrink-0" :class="weather.has_buien ? 'animate-bounce text-blue-500' : 'text-amber-500'">
                                <i class="fa-solid" :class="weather.has_buien ? 'fa-cloud-showers-heavy' : 'fa-cloud-sun'"></i>
                            </span>
                            <div class="min-w-0 flex-1">
                                <span class="block truncate" x-html="weather.buien_text"></span>
                                <template x-if="weather.rain_mm > 0">
                                    <span class="text-[10px] font-mono text-blue-600 dark:text-blue-400 block" x-text="'Neerslag: ' + weather.rain_mm + ' mm/u'"></span>
                                </template>
                            </div>
                        </div>

                        <!-- Luchtplaats status bar -->
                        <div class="p-2.5 rounded-xl border text-xs font-bold flex items-center gap-2"
                             :class="weather.yard_open ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-800 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800' : 'bg-rose-50 dark:bg-rose-950/40 text-rose-800 dark:text-rose-300 border-rose-200 dark:border-rose-800'">
                            <span class="w-2 h-2 rounded-full shrink-0"
                                  :class="weather.yard_open ? 'bg-emerald-500 animate-pulse' : 'bg-rose-500'"></span>
                            <span class="block truncate text-[11px]" x-html="weather.yard_status"></span>
                        </div>

                        <!-- GPS Button & Yard Links -->
                        <div class="flex items-center gap-2 pt-1">
                            <button type="button" 
                                    @click="fetchUserLocation()" 
                                    :disabled="isLocating"
                                    class="flex-1 py-2 rounded-xl border border-sky-200 dark:border-sky-800 bg-sky-50 dark:bg-sky-950/40 hover:bg-sky-100 dark:hover:bg-sky-900/60 text-sky-700 dark:text-sky-300 font-extrabold text-[11px] transition flex items-center justify-center gap-1.5 cursor-pointer disabled:opacity-50"
                                    title="Haal automatisch jouw actuele locatie op via GPS of IP">
                                <template x-if="isLocating">
                                    <i class="fa-solid fa-circle-notch fa-spin text-xs"></i>
                                </template>
                                <template x-if="!isLocating">
                                    <i class="fa-solid fa-location-crosshairs text-xs"></i>
                                </template>
                                <span x-text="isLocating ? 'Locatie zoeken...' : '📍 Mijn Locatie Ophalen'"></span>
                            </button>

                            <button type="button" 
                                    @click="showCityModal = true" 
                                    class="py-2 px-3 rounded-xl border border-sky-200 dark:border-sky-800/80 bg-sky-50/70 dark:bg-sky-950/50 hover:bg-sky-100 dark:hover:bg-sky-900/60 text-sky-700 dark:text-sky-300 hover:text-sky-800 dark:hover:text-sky-200 text-xs font-bold transition cursor-pointer flex items-center gap-1"
                                    title="Kies of typ je eigen woonplaats (bijv. Rotterdam, Amsterdam, Utrecht)">
                                <i class="fa-solid fa-city"></i>
                                <span class="hidden sm:inline text-[11px]">Woonplaats</span>
                            </button>

                            <template x-if="weather.is_gps || (weather.location && !weather.location.includes('Vught'))">
                                <button type="button" 
                                        @click="resetToVught()" 
                                        class="p-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-500 hover:text-slate-800 dark:hover:text-white text-xs transition cursor-pointer"
                                        title="Reset naar TBS Kliniek Vught">
                                    <i class="fa-solid fa-rotate-left"></i>
                                </button>
                            </template>
                        </div>
                    </div>
                </div>

            </div>

            <!-- 2. ALGEMEEN BUITENWERELD NIEUWS WIDGET (Toggleable & Premium) -->
            <div x-show="widgets.nunl" 
                 x-transition
                 class="bg-white dark:bg-slate-900 rounded-2xl shadow-xs border border-slate-200/90 dark:border-slate-800 overflow-hidden">
                
                <!-- Locked State -->
                <div x-show="!isUnlocked('nunl')" x-cloak @if($character->isWidgetUnlocked('nunl')) style="display: none;" @endif class="p-6 text-center space-y-3 bg-gradient-to-b from-indigo-500/5 to-transparent">
                    <div class="w-12 h-12 rounded-2xl bg-indigo-100 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center mx-auto text-xl shadow-xs border border-indigo-200 dark:border-indigo-800/80">
                        <i class="fa-solid fa-newspaper"></i>
                    </div>
                    <div>
                        <h4 class="text-sm font-black text-slate-900 dark:text-white">Algemeen Buitenwereld Nieuws</h4>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 max-w-xs mx-auto">
                            Blijf op de hoogte van het laatste landelijke nieuws, politiek en misdaad rechtstreeks op je dashboard.
                        </p>
                    </div>
                    <div class="pt-2">
                        <form method="POST" action="{{ route('dashboard.widgets.unlock') }}" class="inline" @submit.prevent="unlockWidgetSubmit($event, 'nunl', 15)">
                            @csrf
                            <input type="hidden" name="widget_key" value="nunl">
                            <button type="submit" 
                                    :disabled="unlockingKey === 'nunl'"
                                    class="btn-star-gold px-5 py-2.5 rounded-xl active:scale-95 text-slate-900 dark:text-white font-black text-xs shadow-md transition cursor-pointer inline-flex items-center gap-2 disabled:opacity-60">
                                <i class="fa-solid" :class="unlockingKey === 'nunl' ? 'fa-spinner fa-spin' : 'fa-star text-amber-900 dark:text-amber-200'"></i>
                                <span x-text="unlockingKey === 'nunl' ? 'Bezig met ontgrendelen...' : 'Ontgrendel voor 15 Sterren'"></span>
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Unlocked Content -->
                <div id="widget-content-nunl" x-show="isUnlocked('nunl')" x-cloak @if(!$character->isWidgetUnlocked('nunl')) style="display: none;" @endif>
                    <div class="px-5 py-3.5 bg-slate-50/70 dark:bg-slate-800/60 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                        <div class="flex items-center gap-2 text-xs font-black tracking-wide text-slate-900 dark:text-white">
                            <span class="w-6 h-6 rounded-lg bg-indigo-100 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-xs border border-indigo-200 dark:border-indigo-800">
                                <i class="fa-solid fa-earth-europe"></i>
                            </span>
                            <span>Algemeen Buitenwereld Nieuws</span>
                        </div>
                        <span class="text-[9px] font-bold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/60 px-2 py-0.5 rounded-full border border-emerald-200 dark:border-emerald-800 flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Live Teletekst
                        </span>
                    </div>

                    <div class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($nuNlNews ?? [] as $item)
                            <a href="{{ $item['link'] }}" target="_blank" rel="noopener noreferrer" class="p-3.5 px-5 hover:bg-slate-50/80 dark:hover:bg-slate-800/50 transition block group">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center gap-1.5 mb-1">
                                            <span class="text-[9px] font-black uppercase tracking-wider text-indigo-600 dark:text-indigo-400">{{ $item['category'] }}</span>
                                            <span class="text-slate-300 dark:text-slate-600">&bull;</span>
                                            <span class="text-[10px] text-slate-400">{{ $item['time_ago'] }}</span>
                                        </div>
                                        <h4 class="text-xs font-bold text-slate-800 dark:text-slate-100 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition leading-snug line-clamp-2">
                                            {{ $item['title'] }}
                                        </h4>
                                    </div>
                                    @if(!empty($item['image']))
                                        <img src="{{ $item['image'] }}" alt="Nieuws thumbnail" class="w-12 h-12 rounded-lg object-cover shrink-0 border border-slate-200 dark:border-slate-700 shadow-2xs">
                                    @else
                                        <div class="w-7 h-7 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-400 flex items-center justify-center shrink-0">
                                            <i class="fa-solid fa-newspaper text-[10px]"></i>
                                        </div>
                                    @endif
                                </div>
                            </a>
                        @empty
                            <div class="p-6 text-center text-xs text-slate-400">
                                Geen extern nieuws beschikbaar.
                            </div>
                        @endforelse
                    </div>

                    <div class="p-2.5 bg-slate-50/70 dark:bg-slate-800/40 text-center border-t border-slate-100 dark:border-slate-800">
                        <span class="text-[10px] text-slate-400">Automatisch bijgewerkt &bull; Bron: Algemene Nieuwsdienst</span>
                    </div>
                </div>

            </div>

            <!-- 2B. WIDGET: GEHEIME ONTSNAPPINGSTUNNEL (Toggleable & Premium) -->
            <div x-show="widgets.tunnel" 
                 x-transition
                 class="bg-white dark:bg-slate-900 rounded-2xl shadow-xs border border-amber-200/90 dark:border-amber-900/50 overflow-hidden">
                
                <!-- Locked State -->
                <div x-show="!isUnlocked('tunnel')" x-cloak @if($character->isWidgetUnlocked('tunnel')) style="display: none;" @endif class="p-6 text-center space-y-3 bg-gradient-to-b from-amber-500/5 to-transparent">
                    <div class="w-12 h-12 rounded-2xl bg-amber-100 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center mx-auto text-xl shadow-xs border border-amber-200 dark:border-amber-800/80">
                        <i class="fa-solid fa-person-digging"></i>
                    </div>
                    <div>
                        <h4 class="text-sm font-black text-slate-900 dark:text-white">Geheime Ontsnappingstunnel</h4>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 max-w-xs mx-auto">
                            Graaf een vluchtroute onder je cel, dump heimelijk zand op de luchtplaats en ontsnap uit de kliniek!
                        </p>
                    </div>
                    <div class="pt-2">
                        <form method="POST" action="{{ route('dashboard.widgets.unlock') }}" class="inline" @submit.prevent="unlockWidgetSubmit($event, 'tunnel', 20)">
                            @csrf
                            <input type="hidden" name="widget_key" value="tunnel">
                            <button type="submit" 
                                    :disabled="unlockingKey === 'tunnel'"
                                    class="btn-star-gold px-5 py-2.5 rounded-xl active:scale-95 text-slate-900 dark:text-white font-black text-xs shadow-md transition cursor-pointer inline-flex items-center gap-2 disabled:opacity-60">
                                <i class="fa-solid" :class="unlockingKey === 'tunnel' ? 'fa-spinner fa-spin' : 'fa-star text-amber-900 dark:text-amber-200'"></i>
                                <span x-text="unlockingKey === 'tunnel' ? 'Bezig met ontgrendelen...' : 'Ontgrendel voor 20 Sterren'"></span>
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Unlocked Content -->
                <div id="widget-content-tunnel" x-show="isUnlocked('tunnel')" x-cloak @if(!$character->isWidgetUnlocked('tunnel')) style="display: none;" @endif>
                    @if(!empty($tunnel))
                    <div class="px-5 py-3.5 bg-gradient-to-r from-amber-500/10 to-transparent border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                        <div class="flex items-center gap-2 text-xs font-black tracking-wide text-slate-900 dark:text-white">
                            <span class="w-6 h-6 rounded-lg bg-amber-100 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center text-xs">
                                <i class="fa-solid fa-person-digging"></i>
                            </span>
                            <span>Ontsnappingstunnel</span>
                        </div>
                        <span class="text-[9px] font-bold text-amber-700 dark:text-amber-300 bg-amber-50 dark:bg-amber-950/60 px-2 py-0.5 rounded-full border border-amber-200 dark:border-amber-800">
                            Fase {{ $tunnel->stage }}: {{ $tunnelStageInfo['name'] ?? ('Fase ' . $tunnel->stage) }}
                        </span>
                    </div>

                    <div class="p-4 space-y-3.5">
                        <!-- Voortgangsbalk van de graafroute -->
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between text-xs font-bold">
                                <span class="text-slate-600 dark:text-slate-400 text-[11px]">Totale Graafvoortgang</span>
                                <span class="font-mono font-black text-amber-600 dark:text-amber-400">{{ $tunnel->progress_pct }}%</span>
                            </div>
                            <div class="w-full bg-slate-100 dark:bg-slate-800 rounded-full h-2.5 overflow-hidden border border-slate-200/60 dark:border-slate-700/60">
                                <div class="bg-gradient-to-r from-amber-500 to-yellow-500 h-full rounded-full transition-all duration-500" style="width: {{ $tunnel->progress_pct }}%"></div>
                            </div>
                        </div>

                        <!-- 3 Stats Tegels -->
                        <div class="grid grid-cols-3 gap-2 text-center">
                            <div class="p-2 rounded-xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200/80 dark:border-amber-800/60">
                                <span class="text-[9px] font-black uppercase text-amber-700 dark:text-amber-300 block">Fase</span>
                                <span class="text-sm font-black text-slate-900 dark:text-white">{{ $tunnel->stage }} / 5</span>
                            </div>
                            <div class="p-2 rounded-xl border {{ $tunnel->sand_in_socks >= 100 ? 'bg-rose-50 dark:bg-rose-950/40 border-rose-300 dark:border-rose-800' : 'bg-slate-50 dark:bg-slate-800/60 border-slate-200/80 dark:border-slate-700/60' }}">
                                <span class="text-[9px] font-black uppercase {{ $tunnel->sand_in_socks >= 100 ? 'text-rose-600' : 'text-slate-500' }} block">Zandsokken</span>
                                <span class="text-sm font-black {{ $tunnel->sand_in_socks >= 100 ? 'text-rose-600' : 'text-slate-900 dark:text-white' }}">{{ $tunnel->sand_in_socks }} / 100</span>
                            </div>
                            <div class="p-2 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/60">
                                <span class="text-[9px] font-black uppercase text-slate-500 dark:text-slate-400 block">Lepels</span>
                                <span class="text-sm font-black text-slate-900 dark:text-white">{{ $tunnel->spoons_used }}</span>
                            </div>
                        </div>

                        <!-- Directe Actieknoppen -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 pt-1">
                            @if($tunnel->is_completed)
                                @if($tunnel->isFugitive())
                                    <a href="{{ route('tunnel.index') }}" class="col-span-2 py-2.5 px-4 rounded-xl bg-gradient-to-r from-purple-600 via-indigo-600 to-rose-600 hover:from-purple-500 hover:to-rose-500 active:scale-95 text-white font-black text-xs transition text-center shadow-md flex items-center justify-center gap-2 animate-pulse">
                                        <i class="fa-solid fa-mask"></i>
                                        <span>🚨 Voortvluchtig &bull; Voer Heists Uit!</span>
                                    </a>
                                @else
                                    <a href="{{ route('tunnel.index') }}" class="col-span-2 py-2.5 px-4 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-600 hover:to-teal-600 active:scale-95 text-white font-black text-xs transition text-center shadow-md flex items-center justify-center gap-2">
                                        <i class="fa-solid fa-rotate-left"></i>
                                        <span>Tunnel Voltooid &bull; Start Nieuwe Tunnel</span>
                                    </a>
                                @endif
                            @elseif($character->isImprisoned())
                                <div class="col-span-1 sm:col-span-2 py-2 px-3 rounded-xl bg-rose-50 dark:bg-rose-950/40 text-rose-600 font-bold text-[11px] text-center border border-rose-200 dark:border-rose-800">
                                    <i class="fa-solid fa-lock mr-1"></i> In isoleercel &bull; Graven geblokkeerd
                                </div>
                            @elseif($character->isComatose())
                                <div class="col-span-1 sm:col-span-2 py-2 px-3 rounded-xl bg-rose-50 dark:bg-rose-950/40 text-rose-600 font-bold text-[11px] text-center border border-rose-200 dark:border-rose-800">
                                    <i class="fa-solid fa-bed mr-1"></i> In coma &bull; Graven geblokkeerd
                                </div>
                            @else
                                <!-- Knop 1: Graven -->
                                @if(!empty($tunnelDigCooldown) && $tunnelDigCooldown > 0)
                                    <div class="py-2 px-3 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 font-bold text-[11px] text-center flex items-center justify-center gap-1.5">
                                        <i class="fa-solid fa-clock text-amber-500"></i>
                                        <span>Graven over {{ ceil($tunnelDigCooldown / 60) }}m</span>
                                    </div>
                                @elseif($tunnel->sand_in_socks >= 100)
                                    <div class="py-2 px-3 rounded-xl bg-rose-50 dark:bg-rose-950/40 text-rose-600 font-bold text-[11px] text-center flex items-center justify-center gap-1.5 border border-rose-200 dark:border-rose-800">
                                        <i class="fa-solid fa-triangle-exclamation"></i>
                                        <span>Sokken vol! Dump zand</span>
                                    </div>
                                @else
                                    <form method="POST" action="{{ route('tunnel.dig') }}">
                                        @csrf
                                        <button type="submit" @disabled($character->energy < 20) class="w-full py-2 px-3 rounded-xl bg-gradient-to-r from-amber-500 to-yellow-500 hover:from-amber-600 hover:to-yellow-600 active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed text-slate-950 font-black text-[11px] transition shadow-2xs flex items-center justify-center gap-1.5 cursor-pointer">
                                            <i class="fa-solid fa-spoon"></i>
                                            <span>Graaf 1 Meter (20 E)</span>
                                        </button>
                                    </form>
                                @endif

                                <!-- Knop 2: Zand Dumpen -->
                                @if($tunnel->sand_in_socks <= 0)
                                    <div class="py-2 px-3 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-400 font-bold text-[11px] text-center">
                                        Geen zand in sokken
                                    </div>
                                @elseif(!empty($tunnelDumpCooldown) && $tunnelDumpCooldown > 0)
                                    <div class="py-2 px-3 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 font-bold text-[11px] text-center flex items-center justify-center gap-1.5">
                                        <i class="fa-solid fa-clock text-sky-500"></i>
                                        <span>Dumpen over {{ ceil($tunnelDumpCooldown / 60) }}m</span>
                                    </div>
                                @else
                                    <form method="POST" action="{{ route('tunnel.dump') }}">
                                        @csrf
                                        <button type="submit" class="w-full py-2 px-3 rounded-xl bg-sky-600 hover:bg-sky-700 active:scale-95 text-white font-black text-[11px] transition shadow-2xs flex items-center justify-center gap-1.5 cursor-pointer">
                                            <i class="fa-solid fa-socks"></i>
                                            <span>Dump Zand (Luchtplaats)</span>
                                        </button>
                                    </form>
                                @endif
                            @endif
                        </div>

                        <!-- Link naar de tunnelpagina -->
                        <div class="text-center pt-1 border-t border-slate-100 dark:border-slate-800">
                            <a href="{{ route('tunnel.index') }}" class="text-[11px] font-bold text-amber-600 dark:text-amber-400 hover:underline inline-flex items-center gap-1">
                                <span>Volledig Uitgraafplan & Houtbalken Bekijken</span>
                                <i class="fa-solid fa-arrow-right text-[9px]"></i>
                            </a>
                        </div>
                    </div>
                    @else
                    <div class="p-4 text-center text-xs text-slate-400">Tunnelgegevens niet beschikbaar.</div>
                    @endif
                </div>

            </div>

            <!-- 2C. WIDGET: BEHANDELING & VERLOFTRAJECT (Toggleable & Premium) -->
            <div x-show="widgets.treatment" 
                 x-transition
                 class="bg-white dark:bg-slate-900 rounded-2xl shadow-xs border border-teal-200/90 dark:border-teal-900/50 overflow-hidden">
                
                <!-- Locked State -->
                <div x-show="!isUnlocked('treatment')" x-cloak @if($character->isWidgetUnlocked('treatment')) style="display: none;" @endif class="p-6 text-center space-y-3 bg-gradient-to-b from-teal-500/5 to-transparent">
                    <div class="w-12 h-12 rounded-2xl bg-teal-100 dark:bg-teal-950/60 text-teal-600 dark:text-teal-400 flex items-center justify-center mx-auto text-xl shadow-xs border border-teal-200 dark:border-teal-800/80">
                        <i class="fa-solid fa-user-doctor"></i>
                    </div>
                    <div>
                        <h4 class="text-sm font-black text-slate-900 dark:text-white">Behandeling & Verloftraject</h4>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 max-w-xs mx-auto">
                            Houd toezicht op je psychiatrisch dossier, verhoog je behandelscore en bemachtig begeleid verlof buiten de hekken!
                        </p>
                    </div>
                    <div class="pt-2">
                        <form method="POST" action="{{ route('dashboard.widgets.unlock') }}" class="inline" @submit.prevent="unlockWidgetSubmit($event, 'treatment', 20)">
                            @csrf
                            <input type="hidden" name="widget_key" value="treatment">
                            <button type="submit" 
                                    :disabled="unlockingKey === 'treatment'"
                                    class="btn-star-gold px-5 py-2.5 rounded-xl active:scale-95 text-slate-900 dark:text-white font-black text-xs shadow-md transition cursor-pointer inline-flex items-center gap-2 disabled:opacity-60">
                                <i class="fa-solid" :class="unlockingKey === 'treatment' ? 'fa-spinner fa-spin' : 'fa-star text-amber-900 dark:text-amber-200'"></i>
                                <span x-text="unlockingKey === 'treatment' ? 'Bezig met ontgrendelen...' : 'Ontgrendel voor 20 Sterren'"></span>
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Unlocked Content -->
                <div id="widget-content-treatment" x-show="isUnlocked('treatment')" x-cloak @if(!$character->isWidgetUnlocked('treatment')) style="display: none;" @endif>
                    <div class="px-5 py-3.5 bg-gradient-to-r from-teal-500/10 to-transparent border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                        <div class="flex items-center gap-2 text-xs font-black tracking-wide text-slate-900 dark:text-white">
                            <span class="w-6 h-6 rounded-lg bg-teal-100 dark:bg-teal-950/60 text-teal-600 dark:text-teal-400 flex items-center justify-center text-xs">
                                <i class="fa-solid fa-user-doctor"></i>
                            </span>
                            <span>Dossier Dr. H. van Beek</span>
                        </div>
                        @if($isOnLeave)
                            <span class="text-[9px] font-black px-2 py-0.5 rounded-full uppercase border flex items-center gap-1.5 bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border-emerald-300 animate-pulse">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                Op Verlof (nog {{ $leaveRemainingMinutes }}m)
                            </span>
                        @elseif($treatmentScore >= 60)
                            <span class="text-[9px] font-black px-2 py-0.5 rounded-full uppercase border flex items-center gap-1.5 bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border-emerald-300">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                Positief Advies
                            </span>
                        @elseif($treatmentScore >= 35)
                            <span class="text-[9px] font-black px-2 py-0.5 rounded-full uppercase border flex items-center gap-1.5 bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border-amber-300">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                Observatiestatus
                            </span>
                        @else
                            <span class="text-[9px] font-black px-2 py-0.5 rounded-full uppercase border flex items-center gap-1.5 bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border-rose-300">
                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                Verhoogd Risico
                            </span>
                        @endif
                    </div>

                    <div class="p-4 space-y-3.5">
                        <!-- Behandelscore Voortgang -->
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between text-xs font-bold">
                                <span class="text-slate-600 dark:text-slate-400 text-[11px]">Behandelscore & Ziekte-inzicht</span>
                                <span class="font-mono font-black {{ $treatmentScore >= 60 ? 'text-emerald-600 dark:text-emerald-400' : ($treatmentScore >= 35 ? 'text-amber-600 dark:text-amber-400' : 'text-rose-600 dark:text-rose-400') }}">
                                    {{ $treatmentScore }}/100
                                </span>
                            </div>
                            <div class="w-full bg-slate-100 dark:bg-slate-800 rounded-full h-2.5 overflow-hidden border border-slate-200/60 dark:border-slate-700/60">
                                <div class="h-full rounded-full transition-all duration-500 {{ $treatmentScore >= 60 ? 'bg-gradient-to-r from-emerald-500 to-teal-500' : ($treatmentScore >= 35 ? 'bg-gradient-to-r from-amber-500 to-yellow-500' : 'bg-gradient-to-r from-rose-500 to-red-500') }}" style="width: {{ min(100, max(5, $treatmentScore)) }}%"></div>
                            </div>
                        </div>

                        <!-- 2 Status Kaartjes -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs">
                            <div class="p-2.5 rounded-xl bg-teal-50/60 dark:bg-teal-950/30 border border-teal-200/70 dark:border-teal-800/50">
                                <span class="text-[9px] font-black uppercase tracking-wider text-teal-700 dark:text-teal-300 block mb-0.5">Verloftraject</span>
                                @if($isOnLeave)
                                    <span class="font-bold text-emerald-600 dark:text-emerald-400 block truncate">Actief buiten de hekken!</span>
                                @else
                                    <span class="text-slate-600 dark:text-slate-300 text-[11px] block">Scoor &ge; 70 voor 3u verlof</span>
                                @endif
                            </div>

                            <div class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/70 dark:border-slate-700/60">
                                <span class="text-[9px] font-black uppercase tracking-wider text-slate-400 block mb-0.5">Spreekkamer</span>
                                @if($treatmentCooldown > 0)
                                    <span class="font-bold text-slate-500 dark:text-slate-400 block truncate">Sessie over {{ ceil($treatmentCooldown / 60) }}m</span>
                                @else
                                    <span class="font-bold text-teal-600 dark:text-teal-400 block truncate">Psychiater beschikbaar</span>
                                @endif
                            </div>
                        </div>

                        <!-- Actieknop -->
                        <div class="pt-1">
                            @if($isOnLeave)
                                <a href="{{ route('treatment.index') }}" class="w-full py-2.5 px-4 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-600 hover:to-teal-600 active:scale-95 text-white font-black text-xs transition text-center shadow-md flex items-center justify-center gap-2">
                                    <i class="fa-solid fa-person-walking-arrow-right"></i>
                                    <span>Verlof Activiteiten Uitvoeren (Koffie/Winkels) &rarr;</span>
                                </a>
                            @else
                                <a href="{{ route('treatment.index') }}" class="w-full py-2.5 px-4 rounded-xl bg-gradient-to-r from-teal-600 to-cyan-600 hover:from-teal-700 hover:to-cyan-700 active:scale-95 text-white font-black text-xs transition text-center shadow-md flex items-center justify-center gap-2">
                                    <i class="fa-solid fa-couch"></i>
                                    <span>Naar Behandelkamer (Sessie Voeren) &rarr;</span>
                                </a>
                            @endif
                        </div>
                    </div>
                </div>

            </div>

            <!-- 2D. WIDGET: KOFFERS & KLUISCODES (Toggleable & Premium) -->
            <div x-show="widgets.packages" 
                 x-transition
                 class="bg-white dark:bg-slate-900 rounded-2xl shadow-xs border border-purple-200/90 dark:border-purple-900/50 overflow-hidden">
                
                <!-- Locked State -->
                <div x-show="!isUnlocked('packages')" x-cloak @if($character->isWidgetUnlocked('packages')) style="display: none;" @endif class="p-6 text-center space-y-3 bg-gradient-to-b from-purple-500/5 to-transparent">
                    <div class="w-12 h-12 rounded-2xl bg-purple-100 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400 flex items-center justify-center mx-auto text-xl shadow-xs border border-purple-200 dark:border-purple-800/80">
                        <i class="fa-solid fa-vault"></i>
                    </div>
                    <div>
                        <h4 class="text-sm font-black text-slate-900 dark:text-white">Koffers & Kluiscodes</h4>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 max-w-xs mx-auto">
                            Volledig overzicht van al je kluiscodes, kluismunten, smokkeltassen, XXL koffers en kluisjes!
                        </p>
                    </div>
                    <div class="pt-2">
                        <form method="POST" action="{{ route('dashboard.widgets.unlock') }}" class="inline" @submit.prevent="unlockWidgetSubmit($event, 'packages', 20)">
                            @csrf
                            <input type="hidden" name="widget_key" value="packages">
                            <button type="submit" 
                                    :disabled="unlockingKey === 'packages'"
                                    class="btn-star-gold px-5 py-2.5 rounded-xl active:scale-95 text-slate-900 dark:text-white font-black text-xs shadow-md transition cursor-pointer inline-flex items-center gap-2 disabled:opacity-60">
                                <i class="fa-solid" :class="unlockingKey === 'packages' ? 'fa-spinner fa-spin' : 'fa-star text-amber-900 dark:text-amber-200'"></i>
                                <span x-text="unlockingKey === 'packages' ? 'Bezig met ontgrendelen...' : 'Ontgrendel voor 20 Sterren'"></span>
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Unlocked Content -->
                <div id="widget-content-packages" x-show="isUnlocked('packages')" x-cloak @if(!$character->isWidgetUnlocked('packages')) style="display: none;" @endif>
                    <div class="px-5 py-3.5 bg-gradient-to-r from-purple-500/10 to-transparent border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                        <div class="flex items-center gap-2 text-xs font-black tracking-wide text-slate-900 dark:text-white">
                            <span class="w-6 h-6 rounded-lg bg-purple-100 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400 flex items-center justify-center text-xs">
                                <i class="fa-solid fa-vault"></i>
                            </span>
                            <span>Koffers & Kluiscodes</span>
                        </div>
                        <span class="text-[9px] font-black text-purple-700 dark:text-purple-300 bg-purple-50 dark:bg-purple-950/60 px-2.5 py-0.5 rounded-full border border-purple-200 dark:border-purple-800 flex items-center gap-1">
                            <i class="fa-solid fa-coins text-amber-500 text-[10px]"></i>
                            <span>{{ number_format($vaultTokens, 0, ',', '.') }} KluisMunten</span>
                        </span>
                    </div>

                    <div class="p-4 space-y-3.5">
                        <!-- 6 Gedetailleerde Stats Tegels in 3x2 Grid -->
                        <div class="grid grid-cols-3 gap-2 text-center">
                            <!-- 1. Tokens -->
                            <div class="p-2 rounded-xl bg-indigo-50/70 dark:bg-indigo-950/40 border border-indigo-200/80 dark:border-indigo-800/60 flex flex-col justify-between">
                                <span class="text-[9px] font-black uppercase text-indigo-700 dark:text-indigo-300 block leading-tight">Tokens</span>
                                <span class="text-sm font-black text-slate-900 dark:text-white my-0.5">{{ number_format($vaultTokens, 0, ',', '.') }}</span>
                                <span class="text-[8px] font-bold text-slate-400 block leading-tight">Kluis Saldo</span>
                            </div>

                            <!-- 2. Onverzilverde Codes -->
                            <div class="p-2 rounded-xl border flex flex-col justify-between {{ ($activeVaultCodes ?? 0) > 0 ? 'bg-emerald-50 dark:bg-emerald-950/40 border-emerald-300 dark:border-emerald-800' : 'bg-slate-50 dark:bg-slate-800/60 border-slate-200/80 dark:border-slate-700/60' }}">
                                <span class="text-[9px] font-black uppercase {{ ($activeVaultCodes ?? 0) > 0 ? 'text-emerald-700 dark:text-emerald-300' : 'text-slate-500 dark:text-slate-400' }} block leading-tight">Codes</span>
                                <span class="text-sm font-black {{ ($activeVaultCodes ?? 0) > 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-900 dark:text-white' }} my-0.5">
                                    {{ $activeVaultCodes ?? 0 }}
                                </span>
                                <span class="text-[8px] font-bold {{ ($activeVaultCodes ?? 0) > 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400' }} block leading-tight">
                                    {{ ($activeVaultCodes ?? 0) > 0 ? 'Te Verzilveren!' : 'Geen Actief' }}
                                </span>
                            </div>

                            <!-- 3. Verzilverde Codes -->
                            <div class="p-2 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/60 flex flex-col justify-between">
                                <span class="text-[9px] font-black uppercase text-slate-500 dark:text-slate-400 block leading-tight">Gekraakt</span>
                                <span class="text-sm font-black text-slate-900 dark:text-white my-0.5">{{ $redeemedVaultCodes ?? 0 }}</span>
                                <span class="text-[8px] font-bold text-slate-400 block leading-tight">Verzilverd</span>
                            </div>

                            <!-- 4. Smokkeltassen -->
                            <div class="p-2 rounded-xl border flex flex-col justify-between {{ $unopenedBags > 0 ? 'bg-purple-50 dark:bg-purple-950/40 border-purple-300 dark:border-purple-800' : 'bg-slate-50 dark:bg-slate-800/60 border-slate-200/80 dark:border-slate-700/60' }}">
                                <span class="text-[9px] font-black uppercase {{ $unopenedBags > 0 ? 'text-purple-700 dark:text-purple-300' : 'text-slate-500 dark:text-slate-400' }} block leading-tight">Tassen</span>
                                <span class="text-sm font-black {{ $unopenedBags > 0 ? 'text-purple-700 dark:text-purple-300' : 'text-slate-900 dark:text-white' }} my-0.5">{{ $unopenedBags }}</span>
                                <span class="text-[8px] font-bold text-slate-400 block leading-tight">Smokkelbuit</span>
                            </div>

                            <!-- 5. XXL Koffers -->
                            <div class="p-2 rounded-xl border flex flex-col justify-between {{ $xxlCases > 0 ? 'bg-amber-50 dark:bg-amber-950/40 border-amber-300 dark:border-amber-800' : 'bg-slate-50 dark:bg-slate-800/60 border-slate-200/80 dark:border-slate-700/60' }}">
                                <span class="text-[9px] font-black uppercase {{ $xxlCases > 0 ? 'text-amber-700 dark:text-amber-300' : 'text-slate-500 dark:text-slate-400' }} block leading-tight">XXL Koffer</span>
                                <span class="text-sm font-black {{ $xxlCases > 0 ? 'text-amber-700 dark:text-amber-300' : 'text-slate-900 dark:text-white' }} my-0.5">{{ $xxlCases }}</span>
                                <span class="text-[8px] font-bold text-slate-400 block leading-tight">Top Beloning</span>
                            </div>

                            <!-- 6. Kluisjes Status -->
                            <div class="p-2 rounded-xl border flex flex-col justify-between {{ ($lockerCooldown ?? 0) > 0 ? 'bg-rose-50 dark:bg-rose-950/40 border-rose-300 dark:border-rose-800' : 'bg-emerald-50 dark:bg-emerald-950/40 border-emerald-300 dark:border-emerald-800' }}">
                                <span class="text-[9px] font-black uppercase {{ ($lockerCooldown ?? 0) > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-emerald-700 dark:text-emerald-300' }} block leading-tight">Kluisjes</span>
                                <span class="text-sm font-black {{ ($lockerCooldown ?? 0) > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-emerald-600 dark:text-emerald-400' }} my-0.5">
                                    {{ ($lockerCooldown ?? 0) > 0 ? ceil($lockerCooldown / 60).'m' : '6/6' }}
                                </span>
                                <span class="text-[8px] font-bold {{ ($lockerCooldown ?? 0) > 0 ? 'text-rose-500' : 'text-emerald-600 dark:text-emerald-400' }} block leading-tight">
                                    {{ ($lockerCooldown ?? 0) > 0 ? 'Cooldown' : 'Klaar om te Kraken' }}
                                </span>
                            </div>
                        </div>

                        <!-- Directe Acties -->
                        <div class="space-y-2">
                            @if($character->isImprisoned())
                                <div class="py-2.5 px-3 rounded-xl bg-rose-50 dark:bg-rose-950/40 text-rose-600 font-bold text-xs text-center border border-rose-200 dark:border-rose-800">
                                    <i class="fa-solid fa-lock mr-1"></i> In isoleercel &bull; Postkamer geblokkeerd
                                </div>
                            @elseif($character->isComatose())
                                <div class="py-2.5 px-3 rounded-xl bg-rose-50 dark:bg-rose-950/40 text-rose-600 font-bold text-xs text-center border border-rose-200 dark:border-rose-800">
                                    <i class="fa-solid fa-bed mr-1"></i> In coma &bull; Postkamer geblokkeerd
                                </div>
                            @elseif(($activeVaultCodes ?? 0) > 0)
                                <a href="{{ route('vault.index') }}" class="w-full py-2.5 px-3 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 active:scale-95 text-white font-black text-xs transition shadow-md shadow-emerald-600/20 flex items-center justify-center gap-1.5 cursor-pointer">
                                    <i class="fa-solid fa-key"></i>
                                    <span>Verzilver {{ $activeVaultCodes }} Kluiscode(s) Direct &rarr;</span>
                                </a>
                            @endif

                            @if($unopenedBags > 0)
                                <form method="POST" action="{{ route('packages.open-bag') }}">
                                    @csrf
                                    <button type="submit" class="w-full py-2.5 px-3 rounded-xl bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 active:scale-95 text-white font-black text-xs transition shadow-md shadow-purple-600/20 flex items-center justify-center gap-1.5 cursor-pointer">
                                        <i class="fa-solid fa-gift"></i>
                                        <span>Open 1 Smokkeltas Direct (+Items & Buit!)</span>
                                    </button>
                                </form>
                            @elseif($packageCooldown === 0)
                                <form method="POST" action="{{ route('packages.intercept') }}">
                                    @csrf
                                    <input type="hidden" name="package_id" value="2">
                                    <button type="submit" @disabled($character->energy < 10) class="w-full py-2.5 px-3 rounded-xl bg-purple-600 hover:bg-purple-700 active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed text-white font-black text-xs transition shadow-sm shadow-purple-600/25 flex items-center justify-center gap-1.5 cursor-pointer">
                                        <i class="fa-solid fa-box-archive text-amber-300"></i>
                                        <span>Onderschep Koffer in Postkamer (10 E)</span>
                                    </button>
                                </form>
                            @else
                                <div class="py-2 px-3 rounded-xl bg-purple-50/50 dark:bg-purple-950/30 border border-purple-100 dark:border-purple-900/40 text-purple-700 dark:text-purple-300/80 font-bold text-[11px] text-center flex items-center justify-center gap-1.5">
                                    <i class="fa-solid fa-clock text-purple-500"></i>
                                    <span>Postkamer bewaakt (klaar over {{ ceil($packageCooldown / 60) }}m)</span>
                                </div>
                            @endif

                            <!-- Directe Snelle Navigatie Knoppen -->
                            <div class="grid grid-cols-3 gap-1 pt-1 border-t border-slate-100 dark:border-slate-800 text-center">
                                <a href="{{ route('vault.index') }}" class="py-1.5 px-1.5 rounded-lg text-[10px] font-bold text-amber-700 dark:text-amber-300 bg-amber-50 dark:bg-amber-950/50 border border-amber-200/60 dark:border-amber-800/60 hover:bg-amber-100 dark:hover:bg-amber-900/60 transition truncate">
                                    🔑 Kluis & Codes
                                </a>
                                <a href="{{ route('lockers.index') }}" class="py-1.5 px-1.5 rounded-lg text-[10px] font-bold text-sky-700 dark:text-sky-300 bg-sky-50 dark:bg-sky-950/50 border border-sky-200/60 dark:border-sky-800/60 hover:bg-sky-100 dark:hover:bg-sky-900/60 transition truncate">
                                    🔓 Kluisjes
                                </a>
                                <a href="{{ route('packages.index') }}" class="py-1.5 px-1.5 rounded-lg text-[10px] font-bold text-purple-700 dark:text-purple-300 bg-purple-50 dark:bg-purple-950/50 border border-purple-200/60 dark:border-purple-800/60 hover:bg-purple-100 dark:hover:bg-purple-900/60 transition truncate">
                                    📦 Postkamer
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

            </div>


            <!-- 4. BENDE KAART (Toggleable) -->
            <div x-show="widgets.gang" 
                 x-transition
                 class="bg-white dark:bg-slate-900 rounded-2xl shadow-xs border border-slate-200/90 dark:border-slate-800 overflow-hidden">
                @if($character->gang)
                    <div class="p-6 flex flex-col items-center text-center">
                        <div class="w-16 h-16 mb-3 rounded-2xl bg-slate-900 text-amber-400 flex items-center justify-center text-2xl shadow-xs overflow-hidden border border-slate-200 dark:border-slate-700">
                            @if($character->gang->avatar)
                                <img src="{{ $character->gang->avatar_url }}" alt="{{ $character->gang->name }}" class="w-full h-full object-cover">
                            @else
                                <i class="fa-solid {{ $character->gang->emblem ?: 'fa-crown' }}"></i>
                            @endif
                        </div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">{{ $character->gang->name }}</h3>
                        <span class="text-xs font-semibold text-slate-400 mb-4">{{ ucfirst($character->gang_role ?: 'Lid') }}</span>
                        <div class="w-full border-t border-slate-100 dark:border-slate-800 pt-3 mb-4 space-y-2 text-xs font-bold">
                            <div class="flex justify-between items-center">
                                <span class="text-slate-500">Leden:</span>
                                <span class="text-slate-900 dark:text-white">{{ $character->gang->members()->count() }} actief</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-slate-500">Bendekas:</span>
                                <span class="text-emerald-600 font-extrabold">€{{ number_format($character->gang->treasury, 0, ',', '.') }}</span>
                            </div>
                        </div>
                        <a href="{{ route('gangs.index') }}" class="block w-full text-center bg-purple-600 hover:bg-purple-700 dark:bg-purple-600 dark:hover:bg-purple-500 active:scale-95 text-white font-black py-2.5 rounded-xl transition text-xs shadow-xs cursor-pointer">
                            Mijn Bende Bezoeken
                        </a>
                    </div>
                @else
                    <div class="p-6 flex flex-col items-center text-center">
                        <div class="w-14 h-14 mb-3 rounded-2xl bg-purple-50 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400 flex items-center justify-center text-2xl shadow-xs">
                            <i class="fa-solid fa-users"></i>
                        </div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white mb-1">Sluit je aan bij een Bende</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mb-4">
                            Samen sta je sterker in de kliniek. Vecht mee in heists en wars!
                        </p>
                        <a href="{{ route('gangs.index') }}" class="block w-full text-center bg-purple-600 hover:bg-purple-700 dark:bg-purple-600 dark:hover:bg-purple-500 text-white font-black py-2.5 rounded-xl transition text-xs shadow-xs cursor-pointer active:scale-95">
                            Bendes Bekijken
                        </a>
                    </div>
                @endif
            </div>

            <!-- 5. ACTIEVE MISSIES SNELKOPPELING (Toggleable) -->
            <div x-show="widgets.missions" 
                 x-transition
                 class="bg-white dark:bg-slate-900 rounded-2xl shadow-xs border border-slate-200/90 dark:border-slate-800 overflow-hidden">
                <div class="px-5 py-3.5 bg-slate-50/70 dark:bg-slate-800/60 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                    <span class="text-xs font-black text-slate-900 dark:text-white">Snelle Missies</span>
                    <a href="{{ route('missions.index') }}" class="text-[11px] font-bold text-purple-600 dark:text-purple-400 hover:underline">
                        Alle ({{ \App\Models\Mission::count() }}) &rarr;
                    </a>
                </div>

                <div class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($quickMissions->take(3) as $m)
                        <div class="p-3.5 flex items-center justify-between gap-3">
                            <div class="truncate">
                                <h4 class="text-xs font-bold text-slate-900 dark:text-white truncate">{{ $m->title }}</h4>
                                <span class="text-[10px] text-emerald-600 font-bold">+€{{ number_format($m->min_cash_reward, 0, ',', '.') }} &bull; +{{ $m->xp_reward }} XP</span>
                            </div>
                            <a href="{{ route('missions.index') }}" class="px-3.5 py-1.5 rounded-xl bg-purple-600 hover:bg-purple-700 dark:bg-purple-600 dark:hover:bg-purple-500 active:scale-95 text-white font-black text-xs transition shadow-xs shrink-0 cursor-pointer">
                                Start
                            </a>
                        </div>
                    @empty
                        <div class="p-4 text-center text-xs text-slate-400">
                            Geen missies gevonden.
                        </div>
                    @endforelse
                </div>
            </div>

        </div>

    </div>

    <!-- DASHBOARD CUSTOMIZER MODAL / DRAWER -->
    <div x-show="showCustomizer" 
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @keydown.escape.window="showCustomizer = false"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 pb-24 lg:pb-4 bg-slate-950/80 select-none"
         style="display: none;">
        
        <div @click.outside="showCustomizer = false"
             class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/90 dark:border-slate-800 shadow-2xl w-full max-w-xl overflow-hidden flex flex-col max-h-[90vh] transform-gpu will-change-transform">
            
            <!-- Modal Header -->
            <div class="p-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between bg-slate-50/50 dark:bg-slate-800/40">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-purple-100 dark:bg-purple-950/70 text-purple-600 dark:text-purple-400 flex items-center justify-center text-sm shadow-xs">
                        <i class="fa-solid fa-sliders"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-black text-slate-900 dark:text-white">Mijn Dashboard Inrichten</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 font-medium">Pas thema's, widgets en actiebalken aan naar wens.</p>
                    </div>
                </div>

                <button type="button" @click="showCustomizer = false" class="w-8 h-8 rounded-xl hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 flex items-center justify-center transition cursor-pointer" aria-label="Sluiten">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>

            <!-- Customizer Tab Switcher -->
            <div class="flex border-b border-slate-100 dark:border-slate-800 px-5 pt-3 gap-2 bg-slate-50/30 dark:bg-slate-800/20">
                <button type="button" 
                        @click="activeCustomizerTab = 'themes'"
                        class="pb-2.5 px-3 text-xs font-black border-b-2 transition cursor-pointer"
                        :class="activeCustomizerTab === 'themes' ? 'border-purple-600 text-purple-600 dark:text-purple-400' : 'border-transparent text-slate-500 hover:text-slate-800'">
                    <i class="fa-solid fa-palette mr-1"></i> Afdelings Thema
                </button>

                <button type="button" 
                        @click="activeCustomizerTab = 'widgets'"
                        class="pb-2.5 px-3 text-xs font-black border-b-2 transition cursor-pointer"
                        :class="activeCustomizerTab === 'widgets' ? 'border-purple-600 text-purple-600 dark:text-purple-400' : 'border-transparent text-slate-500 hover:text-slate-800'">
                    <i class="fa-solid fa-cubes mr-1"></i> Widgets Aan/Uit
                </button>

                <button type="button" 
                        @click="activeCustomizerTab = 'actions'"
                        class="pb-2.5 px-3 text-xs font-black border-b-2 transition cursor-pointer"
                        :class="activeCustomizerTab === 'actions' ? 'border-purple-600 text-purple-600 dark:text-purple-400' : 'border-transparent text-slate-500 hover:text-slate-800'">
                    <i class="fa-solid fa-bolt-lightning mr-1"></i> Snelle Actiebalk
                </button>
            </div>

            <!-- Modal Body (Scrollable) -->
            <div class="p-5 overflow-y-auto space-y-4 flex-1">
                
                <!-- TAB 1: THEMES -->
                <div x-show="activeCustomizerTab === 'themes'" class="space-y-3">
                    <span class="text-xs font-black uppercase tracking-wider text-slate-400 block">Kies jouw cel sfeer & afdelingsstijl:</span>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <!-- Theme 1: Clinical -->
                        <button type="button" 
                                @click="setTheme('clinical')"
                                class="p-3.5 rounded-2xl border text-left transition cursor-pointer flex items-center justify-between"
                                :class="theme === 'clinical' ? 'border-purple-600 bg-purple-50/50 dark:bg-purple-950/40 shadow-xs ring-2 ring-purple-500/20' : 'border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800'">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-xl bg-purple-600 text-white flex items-center justify-center text-xs shadow-xs">
                                    <i class="fa-solid fa-hospital"></i>
                                </div>
                                <div>
                                    <span class="text-xs font-black text-slate-900 dark:text-white block">Kliniek Standaard</span>
                                    <span class="text-[10px] text-slate-400 block">Strak, slate grijs & paars</span>
                                </div>
                            </div>
                            <i x-show="theme === 'clinical'" class="fa-solid fa-circle-check text-purple-600 text-sm"></i>
                        </button>

                        <!-- Theme 2: EBI High Security -->
                        <button type="button" 
                                @click="setTheme('ebi')"
                                class="p-3.5 rounded-2xl border text-left transition cursor-pointer flex items-center justify-between"
                                :class="theme === 'ebi' ? 'border-rose-600 bg-rose-50/50 dark:bg-rose-950/40 shadow-xs ring-2 ring-rose-500/20' : 'border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800'">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-xl bg-rose-600 text-white flex items-center justify-center text-xs shadow-xs">
                                    <i class="fa-solid fa-triangle-exclamation"></i>
                                </div>
                                <div>
                                    <span class="text-xs font-black text-slate-900 dark:text-white block">EBI Maximum Security</span>
                                    <span class="text-[10px] text-slate-400 block">Alarm rood & donker</span>
                                </div>
                            </div>
                            <i x-show="theme === 'ebi'" class="fa-solid fa-circle-check text-rose-600 text-sm"></i>
                        </button>

                        <!-- Theme 3: VIP Executive -->
                        <button type="button" 
                                @click="setTheme('vip')"
                                class="p-3.5 rounded-2xl border text-left transition cursor-pointer flex items-center justify-between"
                                :class="theme === 'vip' ? 'border-amber-500 bg-amber-50/50 dark:bg-amber-950/40 shadow-xs ring-2 ring-amber-500/20' : 'border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800'">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-xl bg-amber-500 text-slate-950 flex items-center justify-center text-xs shadow-xs font-bold">
                                    <i class="fa-solid fa-crown"></i>
                                </div>
                                <div>
                                    <span class="text-xs font-black text-slate-900 dark:text-white block">Directie Kantoor (VIP)</span>
                                    <span class="text-[10px] text-slate-400 block">Luxe goud & zwart</span>
                                </div>
                            </div>
                            <i x-show="theme === 'vip'" class="fa-solid fa-circle-check text-amber-500 text-sm"></i>
                        </button>

                        <!-- Theme 4: Cyber Tunnel -->
                        <button type="button" 
                                @click="setTheme('cyber')"
                                class="p-3.5 rounded-2xl border text-left transition cursor-pointer flex items-center justify-between"
                                :class="theme === 'cyber' ? 'border-cyan-500 bg-cyan-50/50 dark:bg-cyan-950/40 shadow-xs ring-2 ring-cyan-500/20' : 'border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800'">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-xl bg-cyan-500 text-white flex items-center justify-center text-xs shadow-xs">
                                    <i class="fa-solid fa-terminal"></i>
                                </div>
                                <div>
                                    <span class="text-xs font-black text-slate-900 dark:text-white block">Ondergrondse Tunnel</span>
                                    <span class="text-[10px] text-slate-400 block">Neon cyaan & violet</span>
                                </div>
                            </div>
                            <i x-show="theme === 'cyber'" class="fa-solid fa-circle-check text-cyan-500 text-sm"></i>
                        </button>

                        <!-- Theme 5: Bajes Oranje -->
                        <button type="button" 
                                @click="setTheme('orange')"
                                class="p-3.5 rounded-2xl border text-left transition cursor-pointer flex items-center justify-between sm:col-span-2"
                                :class="theme === 'orange' ? 'border-orange-500 bg-orange-50/50 dark:bg-orange-950/40 shadow-xs ring-2 ring-orange-500/20' : 'border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800'">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-xl bg-orange-600 text-white flex items-center justify-center text-xs shadow-xs">
                                    <i class="fa-solid fa-shirt"></i>
                                </div>
                                <div>
                                    <span class="text-xs font-black text-slate-900 dark:text-white block">Bajes Oranje</span>
                                    <span class="text-[10px] text-slate-400 block">Klassieke gevangenis overall stijl</span>
                                </div>
                            </div>
                            <i x-show="theme === 'orange'" class="fa-solid fa-circle-check text-orange-600 text-sm"></i>
                        </button>
                    </div>
                </div>

                <!-- TAB 2: WIDGETS -->
                <div x-show="activeCustomizerTab === 'widgets'" class="space-y-2">
                    <span class="text-xs font-black uppercase tracking-wider text-slate-400 block mb-2">Schakel widgets in of uit:</span>

                    <!-- Widget Toggle Item -->
                    <template x-for="item in widgetList" :key="item.key">
                        <div class="p-3 rounded-xl border border-slate-200 dark:border-slate-700/80 flex items-center justify-between gap-3 bg-slate-50/50 dark:bg-slate-800/40">
                            <div class="flex items-center gap-2.5 truncate">
                                <div class="w-8 h-8 rounded-lg flex items-center justify-center text-xs shrink-0" :class="item.bg">
                                    <i class="fa-solid" :class="item.icon"></i>
                                </div>
                                <div class="truncate">
                                    <div class="flex items-center gap-1.5">
                                        <span class="text-xs font-bold text-slate-800 dark:text-slate-100 truncate" x-text="item.title"></span>
                                        <template x-if="item.isPremium && !isUnlocked(item.key)">
                                            <span class="text-[9px] font-black px-1.5 py-0.5 rounded-full bg-amber-100 dark:bg-amber-950/70 text-amber-700 dark:text-amber-300 border border-amber-300 dark:border-amber-700 shrink-0">
                                                <i class="fa-solid fa-star text-[8px]"></i> <span x-text="item.cost"></span>
                                            </span>
                                        </template>
                                        <template x-if="item.isPremium && isUnlocked(item.key)">
                                            <span class="text-[8px] font-black px-1.5 py-0.5 rounded-full bg-emerald-100 dark:bg-emerald-950/70 text-emerald-700 dark:text-emerald-300 shrink-0">
                                                ✓ Vrij
                                            </span>
                                        </template>
                                    </div>
                                    <span class="text-[10px] text-slate-400 block truncate" x-text="item.desc"></span>
                                </div>
                            </div>

                            <!-- If locked: Unlock button -->
                            <template x-if="item.isPremium && !isUnlocked(item.key)">
                                <form method="POST" action="{{ route('dashboard.widgets.unlock') }}" class="inline shrink-0" @submit.prevent="unlockWidgetSubmit($event, item.key, item.cost)">
                                    @csrf
                                    <input type="hidden" name="widget_key" :value="item.key">
                                    <button type="submit" 
                                            :disabled="unlockingKey === item.key"
                                            class="btn-star-gold px-2.5 py-1.5 rounded-lg active:scale-95 text-slate-900 dark:text-white font-black text-[10px] shadow-xs transition cursor-pointer shrink-0 inline-flex items-center gap-1 disabled:opacity-60">
                                        <i class="fa-solid" :class="unlockingKey === item.key ? 'fa-spinner fa-spin' : 'fa-star text-[8px]'"></i>
                                        <span>Koop (<span x-text="item.cost"></span>)</span>
                                    </button>
                                </form>
                            </template>

                            <!-- If free or unlocked: Toggle Switch -->
                            <template x-if="!item.isPremium || isUnlocked(item.key)">
                                <button type="button" 
                                        @click="toggleWidget(item.key)"
                                        class="w-11 h-6 rounded-full transition-colors p-0.5 cursor-pointer shrink-0"
                                        :class="widgets[item.key] ? 'bg-purple-600' : 'bg-slate-300 dark:bg-slate-700'">
                                    <div class="w-5 h-5 rounded-full bg-white dark:bg-white transition-transform"
                                         :class="widgets[item.key] ? 'translate-x-5' : 'translate-x-0'"></div>
                                </button>
                            </template>
                        </div>
                    </template>
                </div>

                <!-- TAB 3: QUICK ACTIONS -->
                <div x-show="activeCustomizerTab === 'actions'" class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-black uppercase tracking-wider text-slate-400">Selecteer jouw favoriete acties:</span>
                        <span class="text-xs font-bold text-purple-600" x-text="quickActions.length + '/8 gekozen'"></span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                        <template x-for="act in availableActions" :key="act.id">
                            <button type="button" 
                                    @click="toggleAction(act.id)"
                                    class="p-2.5 rounded-xl border text-left flex items-center justify-between gap-2 transition cursor-pointer"
                                    :class="quickActions.includes(act.id) ? 'border-purple-500 bg-purple-50/60 dark:bg-purple-950/40' : 'border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800'">
                                <div class="flex items-center gap-2.5 truncate">
                                    <div class="w-7 h-7 rounded-lg flex items-center justify-center text-xs" :class="act.color">
                                        <i class="fa-solid" :class="act.icon"></i>
                                    </div>
                                    <span class="text-xs font-bold text-slate-800 dark:text-slate-200 truncate" x-text="act.label"></span>
                                </div>
                                <i class="fa-solid" :class="quickActions.includes(act.id) ? 'fa-square-check text-purple-600' : 'fa-square text-slate-300 dark:text-slate-600'"></i>
                            </button>
                        </template>
                    </div>
                </div>

            </div>

            <!-- Modal Footer -->
            <div class="p-4 border-t border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/40 flex items-center justify-between">
                <button type="button" 
                        @click="resetDefaults()" 
                        class="text-xs font-bold text-slate-500 hover:text-rose-600 transition cursor-pointer">
                    <i class="fa-solid fa-rotate-left mr-1"></i> Fabrieksinstellingen
                </button>

                <button type="button" 
                        @click="showCustomizer = false" 
                        class="px-5 py-2 rounded-xl bg-purple-600 hover:bg-purple-700 dark:bg-purple-600 dark:hover:bg-purple-500 text-white font-black text-xs shadow-xs transition cursor-pointer active:scale-95">
                    Opslaan & Sluiten
                </button>
            </div>

        </div>
    </div>

    <!-- Woonplaats Kiezer Modal (Echt opslaan!) -->
    <div x-show="showCityModal" 
         x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 pb-24 lg:pb-4 bg-slate-950/80"
         @keydown.escape.window="showCityModal = false">
        <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-3xl max-w-md w-full max-h-[85dvh] overflow-y-auto p-6 shadow-2xl space-y-4 transform-gpu will-change-transform" @click.outside="showCityModal = false">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-sky-100 dark:bg-sky-950/60 text-sky-600 dark:text-sky-400 flex items-center justify-center text-sm">
                        <i class="fa-solid fa-cloud-sun"></i>
                    </span>
                    <h3 class="text-sm font-black text-slate-900 dark:text-white">Kies jouw Woonplaats / Regio</h3>
                </div>
                <button type="button" @click="showCityModal = false" class="w-8 h-8 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-400 hover:text-slate-600 dark:hover:text-white flex items-center justify-center transition cursor-pointer" aria-label="Sluiten">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>

            <p class="text-xs text-slate-500 dark:text-slate-400">
                Kies je woonplaats of typ een stad. Jouw weer en actuele buienverwachting worden direct permanent opgeslagen!
            </p>

            <form @submit.prevent="if(citySearchQuery.trim()) fetchCityWeather(citySearchQuery.trim())" class="flex gap-2">
                <input type="text" 
                       x-model="citySearchQuery"
                       placeholder="Bijv. Amsterdam, Tilburg, Zwolle..." 
                       class="flex-1 px-3.5 py-2 rounded-xl text-xs bg-slate-50 dark:bg-slate-800/90 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 rounded-xl focus:outline-none focus:border-purple-500 dark:focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition">
                <button type="submit" 
                        class="px-4 py-2 rounded-xl bg-purple-600 hover:bg-purple-700 dark:bg-purple-600 dark:hover:bg-purple-500 text-white font-black text-xs transition cursor-pointer active:scale-95 shadow-xs">
                    Opslaan
                </button>
            </form>

            <div>
                <span class="text-[10px] font-black uppercase text-slate-400 block mb-2">Populaire steden in Nederland:</span>
                <div class="flex flex-wrap gap-1.5 max-h-44 overflow-y-auto pr-1">
                    @foreach(['Amsterdam', 'Rotterdam', 'Den Haag', 'Utrecht', 'Eindhoven', 'Groningen', 'Tilburg', 'Almere', 'Breda', 'Nijmegen', 'Apeldoorn', 'Haarlem', 'Arnhem', 'Enschede', 'Amersfoort', 'Den Bosch', 'Vught', 'Maastricht', 'Dordrecht', 'Leiden', 'Zwolle', 'Deventer', 'Delft', 'Alkmaar', 'Venlo'] as $topCity)
                        <button type="button" 
                                @click="fetchCityWeather('{{ $topCity }}')"
                                class="px-2.5 py-1 rounded-lg text-xs font-bold bg-slate-100 dark:bg-sky-950/30 hover:bg-sky-100 dark:hover:bg-sky-900/60 hover:text-sky-700 dark:hover:text-sky-300 text-slate-700 dark:text-sky-200 border border-slate-200/80 dark:border-sky-800/50 transition cursor-pointer">
                            {{ $topCity }}
                        </button>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Intake Tour Modal -->
    @include('partials.intake_tour_modal')

</div>
@endsection
