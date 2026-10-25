<div id="tbs-global-radio-container" 
     x-data="tbsRadioPlayer()" 
     x-init="initPlayer()"
     x-cloak
     class="fixed right-4 lg:right-6 bottom-[190px] lg:bottom-[138px] z-40 select-none font-sans flex flex-col items-end"
     @tbs-play-station.window="changeStation($event.detail.id, true)">

    <!-- Hidden Audio Element -->
    <audio id="tbs-global-audio" preload="none"></audio>

    <!-- 1. Collapsed Pill Floating Bar -->
    <div x-show="!isExpanded" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-2 scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         class="bg-white/95 dark:bg-[#111827]/95 backdrop-blur-md border border-slate-200/90 dark:border-slate-800 rounded-full shadow-lg p-1.5 pl-3 flex items-center gap-2.5 max-w-[320px]">
        
        <!-- Live Visualizer / Station Logo -->
        <button type="button" @click="toggleExpand()" class="flex items-center gap-2 text-left cursor-pointer group">
            <div class="w-7 h-7 rounded-full flex items-center justify-center text-white text-[11px] font-black shrink-0 shadow-xs"
                 :class="currentStation.color">
                <template x-if="isPlaying">
                    <div class="flex items-end justify-center gap-0.5 h-3.5">
                        <span class="w-0.5 bg-white rounded-full animate-bounce h-3"></span>
                        <span class="w-0.5 bg-white rounded-full animate-bounce h-2" style="animation-delay: 0.15s"></span>
                        <span class="w-0.5 bg-white rounded-full animate-bounce h-3.5" style="animation-delay: 0.3s"></span>
                    </div>
                </template>
                <template x-if="!isPlaying">
                    <i class="fa-solid fa-radio text-[10px]"></i>
                </template>
            </div>
            <div class="truncate max-w-[120px]">
                <span class="text-[11px] font-extrabold text-slate-800 dark:text-slate-100 block leading-tight truncate" x-text="currentStation.name"></span>
                <span class="text-[9px] font-bold text-slate-400 block leading-none flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full" :class="isPlaying ? 'bg-rose-500 animate-pulse' : 'bg-slate-300 dark:bg-slate-600'"></span>
                    <span x-text="isPlaying ? 'On Air' : 'Gepauzeerd'"></span>
                </span>
            </div>
        </button>

        <!-- Quick Play / Pause Button -->
        <button type="button" 
                @click="togglePlay()" 
                class="w-7 h-7 rounded-full bg-purple-600 hover:bg-purple-700 active:scale-95 text-white flex items-center justify-center text-xs shadow-xs transition cursor-pointer"
                :title="isPlaying ? 'Pauzeren' : 'Afspelen'">
            <i class="fa-solid text-[10px]" :class="isPlaying ? 'fa-pause' : 'fa-play ml-0.5'"></i>
        </button>

        <!-- Expand Button -->
        <button type="button" 
                @click="toggleExpand()" 
                class="w-6 h-6 rounded-full hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 flex items-center justify-center transition cursor-pointer"
                title="Zenderlijst en volume openen">
            <i class="fa-solid fa-chevron-up text-[10px]"></i>
        </button>
    </div>

    <!-- 2. Expanded Full Mini-Player Card -->
    <div x-show="isExpanded" 
         x-cloak
         style="display: none;"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-3 scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
         x-transition:leave-end="opacity-0 translate-y-3 scale-95"
         @click.outside="isExpanded = false"
         class="bg-white/95 dark:bg-[#111827]/95 backdrop-blur-md border border-slate-200/90 dark:border-slate-800 rounded-2xl shadow-2xl p-4 w-80 space-y-4">
        
        <!-- Header -->
        <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
            <div class="flex items-center gap-2">
                <div class="w-6 h-6 rounded-lg bg-purple-100 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400 flex items-center justify-center text-xs">
                    <i class="fa-solid fa-tower-broadcast"></i>
                </div>
                <span class="text-xs font-black tracking-tight text-slate-900 dark:text-white uppercase">TBS Radio Live</span>
            </div>
            <div class="flex items-center gap-1">
                <a href="{{ route('radio.index') }}" class="text-[10px] font-bold text-purple-600 dark:text-purple-400 hover:underline px-1.5 py-0.5 rounded bg-purple-50 dark:bg-purple-950/40" title="Ga naar de afdelingsradio & shoutouts">
                    Shoutouts
                </a>
                <button type="button" @click="toggleExpand()" class="w-6 h-6 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 flex items-center justify-center transition cursor-pointer">
                    <i class="fa-solid fa-xmark text-xs"></i>
                </button>
            </div>
        </div>

        <!-- Currently Playing Card -->
        <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/80 border border-slate-100 dark:border-slate-700/60 flex items-center justify-between gap-3">
            <div class="flex items-center gap-2.5 overflow-hidden">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center text-white font-black text-xs shrink-0 shadow-xs"
                     :class="currentStation.color">
                    <span x-text="currentStation.badge"></span>
                </div>
                <div class="truncate">
                    <h4 class="text-xs font-black text-slate-900 dark:text-white truncate" x-text="currentStation.name"></h4>
                    <span class="text-[10px] text-slate-500 dark:text-slate-400 block truncate" x-text="currentStation.genre"></span>
                </div>
            </div>

            <!-- Big Play / Pause -->
            <button type="button" 
                    @click="togglePlay()" 
                    class="w-10 h-10 rounded-xl bg-purple-600 hover:bg-purple-700 active:scale-95 text-white flex items-center justify-center text-sm shadow-md shadow-purple-500/20 transition cursor-pointer shrink-0">
                <template x-if="isLoading">
                    <i class="fa-solid fa-circle-notch fa-spin text-xs"></i>
                </template>
                <template x-if="!isLoading">
                    <i class="fa-solid" :class="isPlaying ? 'fa-pause' : 'fa-play ml-0.5'"></i>
                </template>
            </button>
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
            <span class="text-[10px] font-mono text-slate-400 w-7 text-right" x-text="Math.round(volume * 100) + '%'"></span>
        </div>

        <!-- Station Selector List -->
        <div class="space-y-1 pt-1">
            <span class="text-[10px] font-black uppercase tracking-wider text-slate-400 block px-1">Kies Zender</span>
            <div class="max-h-48 overflow-y-auto space-y-1 pr-1 custom-scrollbar">
                <template x-for="st in stations" :key="st.id">
                    <button type="button" 
                            @click="changeStation(st.id, true)"
                            class="w-full p-2 rounded-xl text-left flex items-center justify-between gap-2 transition cursor-pointer"
                            :class="currentStation.id === st.id ? 'bg-purple-50 dark:bg-purple-950/50 border border-purple-200 dark:border-purple-800' : 'hover:bg-slate-50 dark:hover:bg-slate-800/60 border border-transparent'">
                        <div class="flex items-center gap-2.5 truncate">
                            <span class="w-6 h-6 rounded-lg text-white font-black text-[9px] flex items-center justify-center shrink-0 shadow-2xs"
                                  :class="st.color"
                                  x-text="st.badge"></span>
                            <div class="truncate">
                                <span class="text-xs font-bold block truncate" 
                                      :class="currentStation.id === st.id ? 'text-purple-700 dark:text-purple-300' : 'text-slate-700 dark:text-slate-200'"
                                      x-text="st.name"></span>
                                <span class="text-[9px] text-slate-400 block truncate" x-text="st.genre"></span>
                            </div>
                        </div>

                        <template x-if="currentStation.id === st.id && isPlaying">
                            <span class="w-2 h-2 rounded-full bg-rose-500 animate-pulse shrink-0"></span>
                        </template>
                    </button>
                </template>
            </div>
        </div>

    </div>
</div>

<script>
function tbsRadioPlayer() {
    return {
        isExpanded: false,
        isPlaying: false,
        isLoading: false,
        isMuted: false,
        volume: 0.7,
        previousVolume: 0.7,
        stations: [
            { id: 'funx', name: 'FunX', badge: 'FX', genre: 'Urban, Hip Hop & Afro', color: 'bg-rose-500', stream: 'https://icecast.omroep.nl/funx-bb-mp3' },
            { id: 'slam', name: 'SLAM!', badge: 'SL', genre: 'Dance, Pop & Beats', color: 'bg-orange-500', stream: 'https://stream.slam.nl/slam_mp3' },
            { id: 'slam00s', name: 'SLAM! 00s & 10s', badge: '00s', genre: 'Throwbacks & Early Club', color: 'bg-amber-500', stream: 'https://stream.slam.nl/slam_00s_mp3' },
            { id: 'radio2', name: 'NPO Radio 2', badge: 'R2', genre: 'Pop, Rock & Soul', color: 'bg-red-600', stream: 'https://icecast.omroep.nl/radio2-bb-mp3' },
            { id: '3fm', name: 'NPO 3FM', badge: '3FM', genre: 'Nieuwe Muziek & Festival', color: 'bg-emerald-600', stream: 'https://icecast.omroep.nl/3fm-bb-mp3' },
            { id: 'skyradio', name: 'Sky Radio', badge: 'SKY', genre: 'Non-stop Greatest Hits', color: 'bg-sky-500', stream: 'https://playerservices.streamtheworld.com/api/livestream-redirect/SKYRADIO.mp3' },
            { id: 'radio538', name: 'Radio 538', badge: '538', genre: 'Top 40 & Dance', color: 'bg-emerald-500', stream: 'https://playerservices.streamtheworld.com/api/livestream-redirect/RADIO538.mp3' }
        ],
        currentStation: null,

        initPlayer() {
            // Restore saved settings
            const savedStationId = localStorage.getItem('tbs_radio_station') || 'funx';
            this.currentStation = this.stations.find(s => s.id === savedStationId) || this.stations[0];
            
            const savedVol = localStorage.getItem('tbs_radio_volume');
            if (savedVol !== null) {
                this.volume = parseFloat(savedVol);
            }

            const audio = document.getElementById('tbs-global-audio');
            if (audio) {
                audio.volume = this.volume;
                audio.src = this.currentStation.stream;

                audio.addEventListener('playing', () => {
                    this.isPlaying = true;
                    this.isLoading = false;
                });
                audio.addEventListener('waiting', () => {
                    this.isLoading = true;
                });
                audio.addEventListener('pause', () => {
                    this.isPlaying = false;
                    this.isLoading = false;
                });
                audio.addEventListener('error', () => {
                    this.isPlaying = false;
                    this.isLoading = false;
                });

                // Auto-resume playback across page navigation if user had radio on
                const wasPlaying = localStorage.getItem('tbs_radio_playing') === 'true';
                if (wasPlaying) {
                    this.isLoading = true;
                    const playPromise = audio.play();
                    if (playPromise !== undefined) {
                        playPromise.then(() => {
                            this.isPlaying = true;
                            this.isLoading = false;
                        }).catch(err => {
                            // Autoplay was prevented by browser policy on fresh page:
                            // Resume on very first user interaction anywhere on the screen!
                            this.isLoading = false;
                            this.isPlaying = false;
                            const resumeOnGesture = () => {
                                if (localStorage.getItem('tbs_radio_playing') === 'true' && !this.isPlaying) {
                                    audio.play().then(() => {
                                        this.isPlaying = true;
                                        this.isLoading = false;
                                    }).catch(() => {});
                                }
                                window.removeEventListener('click', resumeOnGesture);
                                window.removeEventListener('keydown', resumeOnGesture);
                                window.removeEventListener('touchstart', resumeOnGesture);
                            };
                            window.addEventListener('click', resumeOnGesture, { once: true });
                            window.addEventListener('keydown', resumeOnGesture, { once: true });
                            window.addEventListener('touchstart', resumeOnGesture, { once: true });
                        });
                    }
                }
            }

            // Expose globally so other pages/widgets can interact
            window.tbsRadio = this;
        },

        toggleExpand() {
            this.isExpanded = !this.isExpanded;
        },

        togglePlay() {
            const audio = document.getElementById('tbs-global-audio');
            if (!audio) return;

            if (this.isPlaying) {
                audio.pause();
                this.isPlaying = false;
                localStorage.setItem('tbs_radio_playing', 'false');
                sessionStorage.removeItem('tbs_radio_autoplay');
            } else {
                this.isLoading = true;
                audio.src = this.currentStation.stream;
                audio.play().then(() => {
                    this.isPlaying = true;
                    this.isLoading = false;
                    localStorage.setItem('tbs_radio_playing', 'true');
                    sessionStorage.setItem('tbs_radio_autoplay', 'true');
                }).catch(err => {
                    console.warn('Playback error:', err);
                    this.isPlaying = false;
                    this.isLoading = false;
                });
            }
        },

        changeStation(stationId, autoPlay = true) {
            const found = this.stations.find(s => s.id === stationId);
            if (!found) return;

            this.currentStation = found;
            localStorage.setItem('tbs_radio_station', found.id);

            const audio = document.getElementById('tbs-global-audio');
            if (audio) {
                audio.src = found.stream;
                if (autoPlay) {
                    this.isLoading = true;
                    audio.play().then(() => {
                        this.isPlaying = true;
                        this.isLoading = false;
                        localStorage.setItem('tbs_radio_playing', 'true');
                        sessionStorage.setItem('tbs_radio_autoplay', 'true');
                    }).catch(() => {
                        this.isPlaying = false;
                        this.isLoading = false;
                    });
                }
            }
        },

        updateVolume() {
            const audio = document.getElementById('tbs-global-audio');
            if (audio) {
                audio.volume = this.volume;
            }
            localStorage.setItem('tbs_radio_volume', this.volume);
            if (this.volume > 0) {
                this.isMuted = false;
            }
        },

        toggleMute() {
            const audio = document.getElementById('tbs-global-audio');
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
        }
    };
}
</script>
