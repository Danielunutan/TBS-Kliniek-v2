@php
    $isMobile = $isMobile ?? false;
    $containerId = $isMobile ? 'tbs-mobile-radio-container' : 'tbs-global-radio-container';
@endphp

<div id="{{ $containerId }}" 
     x-data="tbsRadioPlayer()" 
     x-init="initPlayer()"
     x-cloak
     class="relative select-none font-sans"
     @tbs-play-station.window="changeStation($event.detail.id, true)"
     @tbs-radio-state-changed.window="syncFromEvent($event.detail)">

    <!-- Topbar Radio Pill -->
    <div class="flex items-center gap-1 sm:gap-1.5 px-2 py-1 sm:px-2.5 sm:py-1.5 rounded-xl bg-purple-50 dark:bg-purple-950/60 hover:bg-purple-100 dark:hover:bg-purple-900/60 border border-purple-200/80 dark:border-purple-800/50 shadow-2xs transition">
        <!-- Station Visualizer & Name (Click toggles dropdown) -->
        <button type="button" 
                @click="toggleExpand()" 
                class="flex items-center gap-1.5 text-left cursor-pointer group"
                title="MIXXR Radio openen">
            <span class="w-5 h-5 rounded-lg flex items-center justify-center text-white text-[9px] font-black shrink-0 shadow-2xs bg-purple-600">
                <template x-if="isPlaying">
                    <span class="flex items-end justify-center gap-0.5 h-3">
                        <span class="w-0.5 bg-white rounded-full animate-bounce h-2.5"></span>
                        <span class="w-0.5 bg-white rounded-full animate-bounce h-1.5" style="animation-delay: 0.15s"></span>
                        <span class="w-0.5 bg-white rounded-full animate-bounce h-3" style="animation-delay: 0.3s"></span>
                    </span>
                </template>
                <template x-if="!isPlaying">
                    <i class="fa-solid fa-radio text-[9px]"></i>
                </template>
            </span>
            @if(!$isMobile)
            <div class="hidden xl:block max-w-[85px] 2xl:max-w-[120px] truncate">
                <span class="text-xs font-black text-slate-800 dark:text-purple-200 block leading-tight truncate" 
                      x-text="isPlaying && nowPlaying.title ? nowPlaying.title : 'MIXXR Radio'"></span>
            </div>
            @endif
        </button>

        <!-- Quick Play / Pause Button -->
        <button type="button" 
                @click.stop="togglePlay()" 
                class="w-6 h-6 rounded-xl bg-purple-600 hover:bg-purple-700 dark:bg-purple-600 dark:hover:bg-purple-500 active:scale-95 text-white flex items-center justify-center text-[10px] shadow-2xs transition-all duration-150 cursor-pointer"
                :title="(isPlaying || isLoading) ? 'Radio uitzetten' : 'Radio aanzetten'">
            <template x-if="isLoading">
                <i class="fa-solid fa-circle-notch fa-spin text-[9px]"></i>
            </template>
            <template x-if="!isLoading">
                <i class="fa-solid text-[9px]" :class="isPlaying ? 'fa-pause' : 'fa-play ml-0.5'"></i>
            </template>
        </button>

        <!-- Direct Stop / Turn Off Button when Radio is Active -->
        <template x-if="isPlaying || isLoading">
            <button type="button"
                    @click.stop="stopRadio()"
                    class="w-6 h-6 rounded-xl bg-rose-600 hover:bg-rose-700 dark:bg-rose-600 dark:hover:bg-rose-500 active:scale-95 text-white flex items-center justify-center text-[10px] shadow-2xs transition-all duration-150 cursor-pointer"
                    title="Radio helemaal uitzetten">
                <i class="fa-solid fa-power-off text-[9px]"></i>
            </button>
        </template>

        <!-- Dropdown Arrow -->
        <button type="button" 
                @click.stop="toggleExpand()" 
                class="text-purple-600 dark:text-purple-400 hover:text-purple-800 dark:hover:text-purple-200 text-[10px] pl-0.5 cursor-pointer"
                title="AutoDJ & instellingen">
            <i class="fa-solid" :class="isExpanded ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
        </button>
    </div>

    <!-- Dropdown Panel (Positioned below the pill) -->
    <div x-show="isExpanded" 
         x-cloak
         style="display: none;"
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 translate-y-2 scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
         x-transition:leave-end="opacity-0 translate-y-2 scale-95"
         @click.outside="isExpanded = false"
         class="absolute {{ $isMobile ? '-right-14 sm:right-0' : 'right-0' }} top-full mt-2 w-80 sm:w-96 max-w-[calc(100vw-24px)] bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl shadow-2xl p-3.5 sm:p-4 z-50 space-y-3.5">
        
        <!-- Header -->
        <div class="flex items-center justify-between pb-2.5 border-b border-slate-100 dark:border-slate-800">
            <div class="flex items-center gap-2">
                <div class="w-6 h-6 rounded-lg bg-purple-50 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 border border-purple-200/80 dark:border-purple-800/70 flex items-center justify-center text-xs">
                    <i class="fa-solid fa-tower-broadcast"></i>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="text-xs font-black tracking-tight text-slate-900 dark:text-white uppercase">MIXXR Radio</span>
                    <span class="text-[9px] font-black uppercase tracking-wider px-1.5 py-0.5 rounded-md bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border border-rose-200/80 dark:border-rose-800/70">Live</span>
                </div>
            </div>
            <div class="flex items-center gap-1">
                <a href="https://mixxr.nl" target="_blank" rel="noopener noreferrer" class="text-[10px] font-bold text-purple-700 dark:text-purple-300 hover:underline px-2 py-0.5 rounded-lg bg-purple-50 dark:bg-purple-950/60 border border-purple-200/80 dark:border-purple-800/70 flex items-center gap-1">
                    <span>MIXXR.nl</span>
                    <i class="fa-solid fa-arrow-up-right-from-square text-[8px]"></i>
                </a>
                <button type="button" @click="toggleExpand()" class="w-6 h-6 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 flex items-center justify-center transition-colors cursor-pointer">
                    <i class="fa-solid fa-xmark text-xs"></i>
                </button>
            </div>
        </div>

        <!-- Currently Playing Card with Live Album Art & Metadata -->
        <div class="p-3 rounded-xl bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70 flex items-center justify-between gap-3">
            <div class="flex items-center gap-3 overflow-hidden">
                <div class="w-12 h-12 rounded-xl overflow-hidden bg-slate-900 shadow-xs border border-slate-200 dark:border-slate-700 shrink-0 relative">
                    <img :src="nowPlaying.cover || 'https://mixxr.nl/album-art.png'" 
                         alt="Cover" 
                         class="w-full h-full object-cover"
                         x-on:error="$event.target.src = 'https://mixxr.nl/album-art.png'">
                    <template x-if="isPlaying">
                        <span class="absolute bottom-1 right-1 w-2 h-2 rounded-full bg-emerald-500 ring-2 ring-white dark:ring-slate-800 animate-pulse"></span>
                    </template>
                </div>
                <div class="truncate">
                    <span class="text-[9px] font-black uppercase tracking-wider text-purple-600 dark:text-purple-400 block" x-text="nowPlaying.artist || 'MIXXR Radio Non-Stop'"></span>
                    <h4 class="text-xs font-black text-slate-900 dark:text-white truncate" x-text="nowPlaying.title || 'Live Stream'"></h4>
                    <span class="text-[10px] text-slate-500 dark:text-slate-400 block truncate">24/7 Pop, R&amp;B &amp; House</span>
                </div>
            </div>

            <!-- Big Play / Pause & Stop Buttons -->
            <div class="flex items-center gap-1.5 shrink-0">
                <button type="button" 
                        @click.stop="togglePlay()" 
                        class="w-10 h-10 rounded-xl bg-purple-600 hover:bg-purple-700 dark:bg-purple-600 dark:hover:bg-purple-500 active:scale-95 text-white flex items-center justify-center text-sm shadow-xs transition-all duration-150 cursor-pointer shrink-0"
                        :title="(isPlaying || isLoading) ? 'Pauzeren / Uitzetten' : 'Radio starten'">
                    <template x-if="isLoading">
                        <i class="fa-solid fa-circle-notch fa-spin text-xs"></i>
                    </template>
                    <template x-if="!isLoading">
                        <i class="fa-solid" :class="isPlaying ? 'fa-pause' : 'fa-play ml-0.5'"></i>
                    </template>
                </button>
                <template x-if="isPlaying || isLoading">
                    <button type="button"
                            @click.stop="stopRadio()"
                            class="px-2.5 h-10 rounded-xl bg-rose-600 hover:bg-rose-700 dark:bg-rose-600 dark:hover:bg-rose-500 active:scale-95 text-white flex items-center justify-center gap-1.5 text-[10px] font-black uppercase tracking-wider shadow-xs transition-all duration-150 cursor-pointer shrink-0"
                            title="Radio helemaal uitzetten">
                        <i class="fa-solid fa-power-off text-xs"></i>
                        <span>Uit</span>
                    </button>
                </template>
            </div>
        </div>

        <!-- Volume Slider -->
        <div class="flex items-center gap-2.5 px-1">
            <button type="button" @click="toggleMute()" class="text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 text-xs transition cursor-pointer">
                <i class="fa-solid" :class="isMuted || volume === 0 ? 'fa-volume-xmark text-rose-500' : (volume < 0.5 ? 'fa-volume-low' : 'fa-volume-high')"></i>
            </button>
            <input type="range" 
                   min="0" 
                   max="1" 
                   step="0.05" 
                   x-model="volume" 
                   @input="updateVolume()" 
                   class="w-full h-1.5 bg-slate-200 dark:bg-slate-700 rounded-lg appearance-none cursor-pointer accent-purple-600">
            <span class="text-[10px] font-mono text-slate-400 dark:text-slate-400 w-7 text-right" x-text="Math.round(volume * 100) + '%'"></span>
        </div>

        <!-- AutoDJ Verzoekjes Box met Zoekbalk & Laad Meer -->
        <div class="pt-1 space-y-2 border-t border-slate-100 dark:border-slate-800">
            <div class="flex items-center justify-between px-1">
                <span class="text-[10px] font-black uppercase tracking-wider text-slate-700 dark:text-slate-200 flex items-center gap-1.5">
                    <i class="fa-solid fa-wand-magic-sparkles text-amber-500"></i>
                    <span>AutoDJ Verzoekje</span>
                </span>
                <span class="text-[10px] text-slate-400 dark:text-slate-400" x-text="autoDjList.length > 0 ? (autoDjList.length + ' tracks') : 'Live database'"></span>
            </div>

            <!-- Zoekbalk -->
            <div class="relative">
                <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 dark:text-slate-500 text-xs pointer-events-none"></i>
                <input type="text" 
                       x-model="autoDjQuery" 
                       placeholder="Zoek artiest of nummer..." 
                       class="w-full pl-8 pr-7 py-2 rounded-xl bg-slate-50 dark:bg-slate-800/90 border border-slate-200 dark:border-slate-700 text-xs text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:border-purple-500 dark:focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition">
                <template x-if="autoDjQuery.length > 0">
                    <button type="button" @click="autoDjQuery = ''" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 text-xs">
                        <i class="fa-solid fa-circle-xmark"></i>
                    </button>
                </template>
            </div>

            <!-- Feedback melding na aanvraag -->
            <template x-if="autoDjFeedback">
                <div class="p-2 rounded-xl text-xs flex items-center gap-2"
                     :class="autoDjFeedback.isError ? 'bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border border-rose-200/80 dark:border-rose-800/70' : 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200/80 dark:border-emerald-800/70'">
                    <i class="fa-solid" :class="autoDjFeedback.isError ? 'fa-triangle-exclamation text-rose-500' : 'fa-circle-check text-emerald-500'"></i>
                    <span class="text-[11px] leading-tight font-medium" x-text="autoDjFeedback.message"></span>
                </div>
            </template>

            <!-- Track List -->
            <div class="max-h-48 overflow-y-auto space-y-1 pr-1 custom-scrollbar">
                <template x-if="autoDjLoading && autoDjList.length === 0">
                    <div class="p-4 text-center text-xs text-slate-400 dark:text-slate-400 flex items-center justify-center gap-2">
                        <i class="fa-solid fa-spinner fa-spin text-purple-500"></i>
                        <span>AutoDJ nummers laden...</span>
                    </div>
                </template>

                <template x-for="track in getVisibleAutoDjTracks()" :key="track.requestId || track.id">
                    <div class="p-2 rounded-xl bg-slate-50/90 dark:bg-slate-800/60 hover:bg-purple-50/60 dark:hover:bg-slate-800 border border-slate-200/80 dark:border-slate-700/70 flex items-center justify-between gap-2.5 transition-all duration-150">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <img :src="track.cover || 'https://mixxr.nl/album-art.png'" 
                                 alt="Art" 
                                 class="w-8 h-8 rounded-lg object-cover bg-slate-900 border border-slate-200 dark:border-slate-700 shrink-0"
                                 x-on:error="$event.target.src = 'https://mixxr.nl/album-art.png'">
                            <div class="min-w-0 truncate">
                                <h5 class="text-xs font-bold text-slate-900 dark:text-slate-100 truncate" x-text="track.title"></h5>
                                <span class="text-[10px] text-slate-500 dark:text-slate-400 block truncate" x-text="track.artist"></span>
                            </div>
                        </div>

                        <button type="button" 
                                @click="requestTrack(track)"
                                :disabled="autoDjRequestingId === (track.requestId || track.id)"
                                class="px-2.5 py-1 rounded-xl bg-purple-600 hover:bg-purple-700 dark:bg-purple-600 dark:hover:bg-purple-500 active:scale-95 text-white font-black text-[10px] shrink-0 transition-all duration-150 cursor-pointer flex items-center gap-1 disabled:opacity-50 disabled:cursor-not-allowed">
                            <template x-if="autoDjRequestingId === (track.requestId || track.id)">
                                <i class="fa-solid fa-spinner fa-spin text-[9px]"></i>
                            </template>
                            <template x-if="autoDjRequestingId !== (track.requestId || track.id)">
                                <i class="fa-solid fa-paper-plane text-[9px]"></i>
                            </template>
                            <span>Aanvragen</span>
                        </button>
                    </div>
                </template>

                <template x-if="!autoDjLoading && getFilteredAutoDjTracks().length === 0">
                    <div class="p-4 text-center text-xs text-slate-400 dark:text-slate-400">
                        Geen nummers gevonden voor deze zoekopdracht.
                    </div>
                </template>
            </div>

            <!-- Laad Meer Knop -->
            <template x-if="hasMoreAutoDjTracks()">
                <button type="button" 
                        @click="autoDjVisibleCount += 10" 
                        class="w-full py-2 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 border border-slate-200/80 dark:border-slate-700 text-xs font-bold transition-all duration-150 active:scale-95 cursor-pointer flex items-center justify-center gap-1.5">
                    <i class="fa-solid fa-plus text-[10px]"></i>
                    <span>Laad meer nummers</span>
                    <span class="text-[10px] opacity-70" x-text="'(' + (getFilteredAutoDjTracks().length - autoDjVisibleCount) + ' overig)'"></span>
                </button>
            </template>
        </div>

    </div>
</div>

@once
<script>
// Persistent in-memory HTML5 Audio instance that survives Turbo DOM swaps
function getGlobalRadioAudio() {
    if (!window.tbsRadioAudio) {
        const audio = new Audio();
        audio.preload = 'none';
        audio.setAttribute('playsinline', '');
        audio.setAttribute('webkit-playsinline', '');

        audio.addEventListener('playing', () => {
            window.dispatchEvent(new CustomEvent('tbs-radio-state-changed', {
                detail: { isPlaying: true, isLoading: false }
            }));
        });
        audio.addEventListener('waiting', () => {
            window.dispatchEvent(new CustomEvent('tbs-radio-state-changed', {
                detail: { isLoading: true }
            }));
        });
        audio.addEventListener('pause', () => {
            window.dispatchEvent(new CustomEvent('tbs-radio-state-changed', {
                detail: { isPlaying: false, isLoading: false }
            }));
        });
        audio.addEventListener('error', () => {
            window.dispatchEvent(new CustomEvent('tbs-radio-state-changed', {
                detail: { isPlaying: false, isLoading: false }
            }));
        });

        window.tbsRadioAudio = audio;
    }
    return window.tbsRadioAudio;
}

// Synchronize state on Turbo page navigation
document.addEventListener('turbo:load', () => {
    const audio = window.tbsRadioAudio;
    if (audio && audio.src && !audio.paused) {
        window.dispatchEvent(new CustomEvent('tbs-radio-state-changed', {
            detail: {
                isPlaying: true,
                isLoading: false,
                volume: audio.volume,
                isMuted: audio.muted
            }
        }));
    }
});

function tbsRadioPlayer() {
    return {
        isExpanded: false,
        isPlaying: false,
        isLoading: false,
        isMuted: false,
        volume: 0.7,
        previousVolume: 0.7,
        playRequestId: 0,
        
        // Single Primary Station: MIXXR Radio
        currentStation: {
            id: 'mixxr',
            name: 'MIXXR Radio Non-Stop',
            badge: 'MIXXR',
            genre: '24/7 Pop, R&B & House',
            color: 'bg-purple-600',
            stream: 'https://stream.mixxr.nl/listen/mixxr/radio.mp3'
        },

        // Realtime Metadata from https://mixxr.nl/api/nowplaying
        nowPlaying: {
            title: 'MIXXR Radio',
            artist: 'Non-Stop Hits',
            cover: 'https://mixxr.nl/album-art.png'
        },
        metadataTimer: null,

        // AutoDJ Search & Requests
        autoDjList: [],
        autoDjQuery: '',
        autoDjVisibleCount: 10,
        autoDjLoading: false,
        autoDjRequestingId: null,
        autoDjFeedback: null,

        initPlayer() {
            const savedVol = localStorage.getItem('tbs_radio_volume');
            if (savedVol !== null) {
                this.volume = parseFloat(savedVol);
            }

            const audio = getGlobalRadioAudio();

            if (audio.src && !audio.paused) {
                this.isPlaying = true;
                this.isLoading = false;
                this.volume = audio.volume;
                this.isMuted = audio.muted;
                window.tbsRadio = this;
                this.fetchNowPlaying();
                this.startMetadataPolling();
                this.loadAutoDj();
                return;
            }

            audio.volume = this.volume;

            const wasPlaying = localStorage.getItem('tbs_radio_playing') === 'true';
            if (wasPlaying && audio.paused) {
                const reqId = ++this.playRequestId;
                this.isLoading = true;
                if (!audio.src || !audio.src.includes('stream.mixxr.nl')) {
                    audio.src = this.currentStation.stream;
                }
                audio.play().then(() => {
                    if (reqId !== this.playRequestId || localStorage.getItem('tbs_radio_playing') !== 'true') {
                        audio.pause();
                        return;
                    }
                    this.isPlaying = true;
                    this.isLoading = false;
                    this.broadcastState();
                }).catch(() => {
                    this.isLoading = false;
                    this.isPlaying = false;
                    this.broadcastState();
                });
            }

            window.tbsRadio = this;
            this.fetchNowPlaying();
            this.startMetadataPolling();
            this.loadAutoDj();
        },

        fetchNowPlaying() {
            fetch('https://mixxr.nl/api/nowplaying')
                .then(r => r.json())
                .then(data => {
                    if (data.success && data.nowPlaying) {
                        this.nowPlaying = {
                            title: data.nowPlaying.title || 'MIXXR Radio',
                            artist: data.nowPlaying.artist || 'Non-Stop Hits',
                            cover: data.nowPlaying.cover || 'https://mixxr.nl/album-art.png'
                        };
                        this.updateMediaSession();
                    }
                })
                .catch(() => {});
        },

        startMetadataPolling() {
            if (this.metadataTimer) clearInterval(this.metadataTimer);
            this.metadataTimer = setInterval(() => {
                this.fetchNowPlaying();
            }, 8000);
        },

        updateMediaSession() {
            if ('mediaSession' in navigator) {
                navigator.mediaSession.metadata = new MediaMetadata({
                    title: this.nowPlaying.title || 'MIXXR Radio',
                    artist: this.nowPlaying.artist || 'Non-Stop Hits',
                    album: 'MIXXR.nl • TBS Kliniek FM',
                    artwork: [
                        { src: this.nowPlaying.cover || 'https://mixxr.nl/album-art.png', sizes: '512x512', type: 'image/jpeg' }
                    ]
                });
                navigator.mediaSession.setActionHandler('play', () => this.togglePlay());
                navigator.mediaSession.setActionHandler('pause', () => this.stopRadio());
                try {
                    navigator.mediaSession.setActionHandler('stop', () => this.stopRadio());
                } catch (e) {}
            }
        },

        loadAutoDj() {
            if (this.autoDjList.length > 0) return;
            this.autoDjLoading = true;
            fetch('https://mixxr.nl/api/station/requests')
                .then(r => r.json())
                .then(data => {
                    this.autoDjLoading = false;
                    if (data.success && Array.isArray(data.items)) {
                        this.autoDjList = data.items;
                    }
                })
                .catch(() => {
                    this.autoDjLoading = false;
                });
        },

        getFilteredAutoDjTracks() {
            if (!this.autoDjQuery || this.autoDjQuery.trim() === '') {
                return this.autoDjList;
            }
            const q = this.autoDjQuery.toLowerCase().trim();
            return this.autoDjList.filter(t => 
                (t.title && t.title.toLowerCase().includes(q)) || 
                (t.artist && t.artist.toLowerCase().includes(q)) ||
                (t.genre && t.genre.toLowerCase().includes(q))
            );
        },

        getVisibleAutoDjTracks() {
            return this.getFilteredAutoDjTracks().slice(0, this.autoDjVisibleCount);
        },

        hasMoreAutoDjTracks() {
            return this.getFilteredAutoDjTracks().length > this.autoDjVisibleCount;
        },

        requestTrack(track) {
            const trackId = track.requestId || track.id;
            this.autoDjRequestingId = trackId;
            this.autoDjFeedback = null;

            fetch('https://mixxr.nl/api/station/request', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ requestId: track.requestId, requestUrl: track.requestUrl })
            })
            .then(r => r.json())
            .then(data => {
                this.autoDjRequestingId = null;
                if (data.success) {
                    this.autoDjFeedback = {
                        isError: false,
                        message: data.message || 'Verzoeknummer ingepland op MIXXR Radio!'
                    };
                } else {
                    this.autoDjFeedback = {
                        isError: true,
                        message: data.message || 'Kan verzoeknummer op dit moment niet voltooien.'
                    };
                }
                setTimeout(() => {
                    this.autoDjFeedback = null;
                }, 8000);
            })
            .catch(() => {
                this.autoDjRequestingId = null;
                this.autoDjFeedback = {
                    isError: true,
                    message: 'Verzoeknummer mislukt door een verbindingsfout.'
                };
                setTimeout(() => {
                    this.autoDjFeedback = null;
                }, 8000);
            });
        },

        broadcastState() {
            window.dispatchEvent(new CustomEvent('tbs-radio-state-changed', {
                detail: {
                    isPlaying: this.isPlaying,
                    isLoading: this.isLoading,
                    currentStationId: 'mixxr',
                    volume: this.volume,
                    isMuted: this.isMuted,
                    nowPlaying: this.nowPlaying
                }
            }));
        },

        syncFromEvent(detail) {
            if (detail.isPlaying !== undefined) this.isPlaying = detail.isPlaying;
            if (detail.isLoading !== undefined) this.isLoading = detail.isLoading;
            if (detail.volume !== undefined) this.volume = detail.volume;
            if (detail.isMuted !== undefined) this.isMuted = detail.isMuted;
            if (detail.nowPlaying !== undefined) this.nowPlaying = detail.nowPlaying;
        },

        toggleExpand() {
            this.isExpanded = !this.isExpanded;
            if (this.isExpanded) {
                this.loadAutoDj();
            }
        },

        stopRadio() {
            this.playRequestId++;
            this.isPlaying = false;
            this.isLoading = false;
            localStorage.setItem('tbs_radio_playing', 'false');
            localStorage.removeItem('tbs_radio_playing');

            const audio = getGlobalRadioAudio();
            if (audio) {
                try {
                    audio.pause();
                    audio.removeAttribute('src');
                    audio.load();
                } catch (e) {}
            }

            this.broadcastState();
        },

        togglePlay() {
            const audio = getGlobalRadioAudio();
            if (!audio) return;

            if (this.isPlaying || this.isLoading || !audio.paused) {
                this.stopRadio();
            } else {
                const reqId = ++this.playRequestId;
                this.isLoading = true;
                localStorage.setItem('tbs_radio_playing', 'true');
                if (!audio.src || !audio.src.includes('stream.mixxr.nl')) {
                    audio.src = this.currentStation.stream;
                }
                this.broadcastState();
                audio.play().then(() => {
                    if (reqId !== this.playRequestId || localStorage.getItem('tbs_radio_playing') !== 'true') {
                        audio.pause();
                        return;
                    }
                    this.isPlaying = true;
                    this.isLoading = false;
                    this.broadcastState();
                }).catch(() => {
                    if (reqId === this.playRequestId) {
                        this.isPlaying = false;
                        this.isLoading = false;
                        this.broadcastState();
                    }
                });
            }
        },

        changeStation(stationId, autoPlay = true) {
            this.togglePlay();
        },

        updateVolume() {
            const audio = getGlobalRadioAudio();
            if (audio) {
                audio.volume = this.volume;
            }
            localStorage.setItem('tbs_radio_volume', this.volume);
            if (this.volume > 0) {
                this.isMuted = false;
            }
            this.broadcastState();
        },

        toggleMute() {
            const audio = getGlobalRadioAudio();
            if (!audio) return;

            if (this.isMuted) {
                this.isMuted = false;
                this.volume = this.previousVolume || 0.7;
                audio.volume = this.volume;
            } else {
                this.previousVolume = this.volume;
                this.isMuted = true;
                this.volume = 0;
                audio.volume = 0;
            }
            this.broadcastState();
        }
    };
}
</script>
@endonce
