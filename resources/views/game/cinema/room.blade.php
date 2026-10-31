@extends('layouts.app')

@section('title', $room->title . ' - TBS Cinema Watchparty')

@section('content')
<div class="space-y-6" x-data="cinemaTheater({{ $room->id }}, '{{ $room->slug }}', '{{ $room->youtube_video_id }}', {{ $initialPlaybackSeconds ?? 0 }}, {{ $messages->last()?->id ?? 0 }}, @js($queue ?? []), {{ ($room->host_character_id === $character->id || Auth::user()->isStaff()) ? 'true' : 'false' }})">

    <!-- Top Cinema Header & Navigation -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-4 sm:p-5 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-3.5 min-w-0">
            <a href="{{ route('cinema.index') }}" 
               class="w-10 h-10 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 border border-slate-200/80 dark:border-slate-700 flex items-center justify-center text-sm transition-all duration-150 active:scale-95 shrink-0 shadow-xs">
                <i class="fa-solid fa-arrow-left"></i>
            </a>

            <div class="min-w-0 space-y-1">
                <div class="flex items-center gap-2 flex-wrap">
                    <h1 class="text-base sm:text-xl font-black text-slate-900 dark:text-white tracking-tight truncate">
                        {{ $room->title }}
                    </h1>

                    @if($room->is_official)
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black bg-amber-500 text-slate-950 flex items-center gap-1 shadow-xs">
                        <i class="fa-solid fa-crown text-[9px]"></i>
                        <span>HOOFDZAAL</span>
                    </span>
                    @endif

                    @if($room->is_private)
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-rose-50 dark:bg-rose-950/80 text-rose-700 dark:text-rose-300 border border-rose-200/80 dark:border-rose-800/70">
                        <i class="fa-solid fa-lock text-[9px]"></i> PRIVÉ
                    </span>
                    @endif
                </div>

                <div class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400 flex-wrap">
                    <span>Host: <strong class="text-slate-800 dark:text-slate-200">{{ $room->host?->name ?? 'TBS Staff' }}</strong></span>
                    <span>•</span>
                    <span class="truncate max-w-sm">{{ $room->description }}</span>
                </div>
            </div>
        </div>

        <!-- Right Action Controls -->
        <div class="flex items-center gap-2 flex-wrap self-end md:self-center shrink-0">
            <!-- Synced Status Badge -->
            <span class="px-3 py-1.5 rounded-xl bg-purple-50 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 font-black text-xs flex items-center gap-1.5 border border-purple-200/80 dark:border-purple-800/70">
                <span class="w-2 h-2 rounded-full bg-purple-500 animate-ping"></span>
                <span x-text="syncStatus">Synchroon</span>
            </span>

            <button type="button" @click="copyShareLink"
                    class="px-3 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 border border-slate-200/80 dark:border-slate-700 font-bold text-xs transition-all duration-150 active:scale-95 flex items-center gap-1.5 cursor-pointer"
                    title="Kopieer Zaallink">
                <i class="fa-solid fa-share-nodes"></i>
                <span x-text="copiedLink ? 'Gekopieerd!' : 'Delen'">Delen</span>
            </button>

            @if($room->host_character_id === $character->id || Auth::user()->isStaff())
            <button type="button" @click="showChangeVideoModal = true"
                    class="px-3.5 py-2 rounded-xl bg-purple-600 hover:bg-purple-700 dark:bg-purple-600 dark:hover:bg-purple-500 text-white font-black text-xs transition-all duration-150 shadow-xs flex items-center gap-1.5 cursor-pointer active:scale-95">
                <i class="fa-solid fa-film"></i>
                <span>Video Wisselen</span>
            </button>

            <!-- Zaal Sluiten Knop -->
            <button type="button" @click="showCloseModal = true"
                    class="px-3.5 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 dark:bg-rose-600 dark:hover:bg-rose-500 text-white font-black text-xs transition-all duration-150 shadow-xs flex items-center gap-1.5 cursor-pointer active:scale-95"
                    title="Sluit deze bioscoopzaal voor iedereen">
                <i class="fa-solid fa-power-off"></i>
                <span>Zaal Sluiten</span>
            </button>
            @endif
        </div>
    </div>

    <!-- Main Watchparty Layout: 16:9 Cinema Screen & Queue (Left 8 cols) + Interactive Session Chat (Right 4 cols) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

        <!-- THEATER SCREEN & REALTIME VIDEO QUEUE (8 cols) -->
        <div class="lg:col-span-8 space-y-5">
            
            <!-- 16:9 Screen Container with YouTube IFrame Player API -->
            <div class="rounded-2xl overflow-hidden bg-black border border-slate-900 dark:border-purple-950/80 shadow-2xl relative aspect-video ring-4 ring-purple-950/20">
                <div id="cinema-player" class="w-full h-full"></div>
            </div>

            <!-- Video Wachtrij (Queue / Playlist) Section - Realtime Synchronized -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-5 shadow-xs space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800/80 flex-wrap gap-2">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-xl bg-purple-50 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400 border border-purple-200/80 dark:border-purple-800/70 flex items-center justify-center text-sm shadow-2xs">
                            <i class="fa-solid fa-list-ol"></i>
                        </div>
                        <div>
                            <h3 class="font-black text-sm text-slate-900 dark:text-white flex items-center gap-2">
                                <span>Realtime Wachtrij &amp; Playlist</span>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-purple-50 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 border border-purple-200/80 dark:border-purple-800/70"
                                      x-text="queue.length + ' video(\'s)'"></span>
                            </h3>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400">Speelt automatisch synchroon af voor iedereen in de zaal</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        @if($room->host_character_id === $character->id || Auth::user()->isStaff())
                        <template x-if="queue.length > 0">
                            <button type="button" @click="playNextFromQueue"
                                    class="px-3 py-2 rounded-xl bg-amber-500 hover:bg-amber-600 text-slate-950 font-black text-xs uppercase tracking-wider transition-all duration-150 shadow-xs flex items-center gap-1.5 cursor-pointer active:scale-95">
                                <i class="fa-solid fa-forward-step"></i>
                                <span>Volgende Afspelen</span>
                            </button>
                        </template>
                        @endif

                        <button type="button" @click="showAddQueueModal = true"
                                class="px-3.5 py-2 rounded-xl bg-purple-600 hover:bg-purple-700 dark:bg-purple-600 dark:hover:bg-purple-500 text-white font-black text-xs uppercase tracking-wider transition-all duration-150 shadow-xs flex items-center gap-1.5 cursor-pointer active:scale-95">
                            <i class="fa-solid fa-plus"></i>
                            <span>Voeg Video Toe</span>
                        </button>
                    </div>
                </div>

                <!-- Queue Items List -->
                <div class="space-y-2.5">
                    <template x-if="queue.length === 0">
                        <div class="p-8 text-center rounded-xl border border-dashed border-slate-200/80 dark:border-slate-700/70 bg-slate-50/50 dark:bg-slate-800/30 text-slate-500 dark:text-slate-400 space-y-2">
                            <i class="fa-solid fa-clapperboard text-2xl text-slate-400"></i>
                            <p class="text-xs font-bold text-slate-700 dark:text-slate-300">De wachtrij is momenteel leeg.</p>
                            <p class="text-[11px]">Voeg YouTube tracks of video's toe; ze spelen synchroon af voor iedereen!</p>
                            <button type="button" @click="showAddQueueModal = true" class="mt-2 text-xs font-black text-purple-600 dark:text-purple-400 hover:underline cursor-pointer">
                                + Voeg nu een video toe &rarr;
                            </button>
                        </div>
                    </template>

                    <template x-for="(item, idx) in queue" :key="item.id">
                        <div class="p-3 rounded-xl bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70 flex items-center justify-between gap-3 hover:border-purple-300 dark:hover:border-purple-700/80 transition-all duration-200">
                            <div class="flex items-center gap-3 min-w-0">
                                <span class="w-6 text-center font-mono font-black text-xs text-slate-400" x-text="'#' + (idx + 1)"></span>
                                <img :src="item.thumbnail" class="w-20 h-12 object-cover rounded-xl shrink-0 bg-slate-900 border border-slate-200/80 dark:border-slate-700">
                                <div class="min-w-0 space-y-0.5">
                                    <h4 class="text-xs font-black text-slate-900 dark:text-white truncate" x-text="item.title"></h4>
                                    <div class="flex items-center gap-2 text-[10px] text-slate-500 dark:text-slate-400">
                                        <span x-text="'Door ' + item.added_by_name"></span>
                                        <span>•</span>
                                        <span x-text="item.added_at"></span>
                                    </div>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="flex items-center gap-2 shrink-0">
                                @if($room->host_character_id === $character->id || Auth::user()->isStaff())
                                <button type="button" @click="playQueueItem(item.id)"
                                        class="px-3 py-1.5 rounded-xl bg-purple-600 hover:bg-purple-700 dark:bg-purple-600 dark:hover:bg-purple-500 text-white font-black text-xs transition-all duration-150 shadow-xs flex items-center gap-1 cursor-pointer active:scale-95"
                                        title="Speel deze video nu direct af voor iedereen">
                                    <i class="fa-solid fa-play text-[10px]"></i>
                                    <span class="hidden sm:inline">Nu Afspelen</span>
                                </button>

                                <button type="button" @click="removeQueueItem(item.id)"
                                        class="px-2.5 py-1.5 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-700 dark:bg-rose-950/60 dark:hover:bg-rose-900/60 dark:text-rose-300 font-bold text-xs transition-all duration-150 border border-rose-200/80 dark:border-rose-800/70 cursor-pointer active:scale-95"
                                        title="Verwijder uit wachtrij">
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>
                                @endif
                            </div>
                        </div>
                    </template>
                </div>
            </div>

        </div>

        <!-- SESSION LIVE CHAT (4 cols) -->
        <div class="lg:col-span-4 bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-5 shadow-xs flex flex-col h-[650px] sticky top-20">
            
            <!-- Chat Header -->
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800/80 shrink-0">
                <div class="flex items-center gap-2.5">
                    <div class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></div>
                    <div>
                        <h3 class="text-xs font-black uppercase tracking-wider text-slate-900 dark:text-white flex items-center gap-1.5">
                            <i class="fa-solid fa-comments text-purple-600 dark:text-purple-400"></i>
                            <span>Live Zaalchat</span>
                        </h3>
                    </div>
                </div>

                <div class="flex items-center gap-2 text-[10px]">
                    <span class="font-mono font-bold text-emerald-600 dark:text-emerald-400" x-text="pollStatus"></span>
                </div>
            </div>

            <!-- Messages Stream -->
            <div id="cinema-chat-box" @scroll="checkScrollPosition" class="flex-1 overflow-y-auto py-3 space-y-2.5 pr-1 text-xs">
                <template x-for="msg in messages" :key="msg.id">
                    <div class="flex items-start gap-2.5 p-2.5 rounded-xl border transition-all duration-150"
                         :class="msg.pending ? 'bg-purple-50/60 dark:bg-purple-950/30 border-purple-200/80 dark:border-purple-800/70 opacity-70' : 'bg-slate-50/90 dark:bg-slate-800/60 border-slate-200/80 dark:border-slate-700/70 shadow-2xs'">
                        <img :src="msg.avatar" class="w-7 h-7 rounded-full object-cover shrink-0 mt-0.5 border border-slate-200/80 dark:border-slate-700">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between gap-1 mb-0.5">
                                <div class="flex items-center gap-1.5">
                                    <span class="font-black text-slate-900 dark:text-white truncate" x-text="msg.character_name"></span>
                                    <template x-if="msg.is_staff">
                                        <span class="px-1.5 py-0.5 rounded text-[8px] font-black bg-purple-600 text-white">STAFF</span>
                                    </template>
                                </div>
                                <span class="text-[10px] text-slate-400 font-mono" x-text="msg.time"></span>
                            </div>

                            <!-- Text Message -->
                            <template x-if="msg.text">
                                <p class="text-slate-800 dark:text-slate-200 break-words leading-relaxed font-medium" x-html="formatChatMessage(msg.text)"></p>
                            </template>

                            <!-- Animated GIF -->
                            <template x-if="msg.gif_url">
                                <div class="mt-1.5 rounded-xl overflow-hidden border border-slate-200/80 dark:border-slate-700 max-w-[220px]">
                                    <img :src="msg.gif_url" class="w-full h-auto object-cover max-h-40">
                                </div>
                            </template>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Chat Inputs Bar & Modals -->
            <div class="pt-3 border-t border-slate-100 dark:border-slate-800/80 shrink-0 space-y-2 relative">
                
                <!-- GIF Picker Drawer -->
                <div x-show="showGifPicker" x-cloak @click.away="showGifPicker = false"
                     class="absolute bottom-16 inset-x-0 bg-white dark:bg-slate-900 border border-purple-300 dark:border-purple-700 rounded-2xl p-3 shadow-2xl z-30 max-h-64 overflow-y-auto space-y-2">
                    <div class="flex items-center justify-between pb-1 border-b border-slate-100 dark:border-slate-800/80">
                        <span class="text-[11px] font-black text-purple-600 dark:text-purple-400">Kies een Meme GIF Reactie:</span>
                        <button type="button" @click="showGifPicker = false" class="text-slate-400 hover:text-slate-700 dark:hover:text-white text-xs cursor-pointer">&times;</button>
                    </div>
                    <div class="grid grid-cols-3 gap-2">
                        @foreach($gifs as $gif)
                        <div @click="sendGif('{{ $gif['url'] }}')" 
                             class="rounded-xl overflow-hidden border border-slate-200/80 dark:border-slate-700 hover:border-purple-400 dark:hover:border-purple-600 transition-all duration-150 cursor-pointer aspect-video bg-slate-950 group">
                            <img src="{{ $gif['url'] }}" alt="{{ $gif['title'] }}" class="w-full h-full object-cover group-hover:scale-105 transition" loading="lazy">
                        </div>
                        @endforeach
                    </div>
                </div>

                <!-- Input Form -->
                <form @submit.prevent="sendMessage" class="space-y-2 relative">
                    <!-- Mention Autocomplete Dropdown Above Chat Input -->
                    <div x-show="showMentionDropdown && mentionUsers.length > 0"
                         @click.away="showMentionDropdown = false"
                         x-cloak
                         class="absolute bottom-full left-0 mb-3 w-72 sm:w-80 bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl shadow-2xl z-50 overflow-hidden divide-y divide-slate-100 dark:divide-slate-800/80">
                        <div class="px-3.5 py-2 bg-slate-50/90 dark:bg-slate-800/60 flex items-center justify-between text-[11px] font-extrabold text-slate-500 dark:text-slate-400 border-b border-slate-100 dark:border-slate-800/80">
                            <span class="flex items-center gap-1.5">
                                <i class="fa-solid fa-at text-purple-600 dark:text-purple-400 text-xs"></i>
                                <span>Speler Vermelden</span>
                            </span>
                            <span class="text-[10px] text-slate-400 font-normal">↑↓ navigeer • enter kies</span>
                        </div>
                        <div class="max-h-52 overflow-y-auto p-1 space-y-0.5">
                            <template x-for="(user, idx) in mentionUsers" :key="user.id">
                                <div @click="selectMention(user)"
                                     @mouseenter="mentionSelectedIndex = idx"
                                     :class="mentionSelectedIndex === idx ? 'bg-purple-50 dark:bg-purple-950/70 border-l-4 border-purple-600' : 'hover:bg-slate-50 dark:hover:bg-slate-800/60 border-l-4 border-transparent'"
                                     class="px-3 py-2 rounded-xl flex items-center justify-between gap-2.5 cursor-pointer transition">
                                    <div class="flex items-center gap-2.5 min-w-0">
                                        <div class="relative shrink-0">
                                            <img :src="user.avatar || '{{ asset('assets/characters/avatar_male_1.jpg') }}'" 
                                                 class="w-7 h-7 rounded-lg object-cover border border-slate-200 dark:border-slate-700"
                                                 onerror="this.src='{{ asset('assets/characters/avatar_male_1.jpg') }}'">
                                            <span x-show="user.is_online" 
                                                  class="absolute -bottom-0.5 -right-0.5 w-2 h-2 rounded-full bg-emerald-500 border border-white dark:border-slate-900"></span>
                                        </div>
                                        <div class="min-w-0">
                                            <div class="text-xs font-black text-slate-900 dark:text-white truncate" x-text="user.name"></div>
                                            <div class="text-[10px] text-slate-400" x-text="'Lvl ' + user.level"></div>
                                        </div>
                                    </div>
                                    <span class="text-[10px] font-bold text-purple-700 dark:text-purple-300 px-2 py-0.5 rounded-md bg-purple-50 dark:bg-purple-950/60 border border-purple-200/80 dark:border-purple-800/70 shrink-0">
                                        Kies
                                    </span>
                                </div>
                            </template>
                        </div>
                    </div>

                    <div class="flex items-center gap-1.5">
                        <input type="text" x-model="chatInput" x-ref="cinemaChatInputField"
                               @input="onChatInput($event)"
                               @keydown="onChatKeydown($event)"
                               placeholder="Typ je reactie of @speler..."
                               class="flex-1 px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/90 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 text-xs font-medium focus:outline-none focus:border-purple-500 dark:focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition">
                        
                        <button type="button" @click="showGifPicker = !showGifPicker"
                                class="px-3 py-2.5 rounded-xl bg-purple-50 hover:bg-purple-100 dark:bg-purple-950/60 dark:hover:bg-purple-900/60 text-purple-700 dark:text-purple-300 font-black text-xs transition-all duration-150 cursor-pointer border border-purple-200/80 dark:border-purple-800/70 active:scale-95"
                                title="Kies een GIF reactie">
                            GIF
                        </button>

                        <button type="submit" 
                                class="px-4 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-700 dark:bg-purple-600 dark:hover:bg-purple-500 text-white font-black text-xs transition-all duration-150 shadow-xs flex items-center justify-center cursor-pointer active:scale-95">
                            <i class="fa-solid fa-paper-plane"></i>
                        </button>
                    </div>
                </form>
            </div>

        </div>

    </div>

    <!-- ADD TO QUEUE MODAL -->
    <div x-show="showAddQueueModal" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/75 backdrop-blur-xs">
        <div @click.away="showAddQueueModal = false; showQueueDropdown = false;"
             class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-6 max-w-md w-full shadow-2xl space-y-4">
            
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800/80">
                <h3 class="font-black text-sm text-slate-900 dark:text-white flex items-center gap-2">
                    <i class="fa-solid fa-plus-circle text-purple-600 dark:text-purple-400"></i>
                    <span>Voeg Video Toe Aan Wachtrij</span>
                </h3>
                <button type="button" @click="showAddQueueModal = false" class="text-slate-400 hover:text-slate-700 dark:hover:text-white text-base cursor-pointer">&times;</button>
            </div>

            <form @submit.prevent="submitAddToQueue" class="space-y-4">
                <!-- Search or Paste URL with Dropdown -->
                <div class="relative">
                    <label class="block text-xs font-black text-slate-900 dark:text-slate-200 mb-1">
                        YouTube Video Zoeken of Link Plakken:
                    </label>
                    <div class="relative">
                        <i class="fa-brands fa-youtube absolute left-3.5 top-1/2 -translate-y-1/2 text-rose-500 text-sm"></i>
                        <input type="text" x-model="queueSearchQuery" @input.debounce.350ms="onQueueSearchInput" @focus="if(queueSearchResults.length > 0) showQueueDropdown = true;"
                               placeholder="Typ zoekterm of plak link..."
                               class="w-full pl-9 pr-8 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/90 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 text-xs font-bold focus:outline-none focus:border-purple-500 dark:focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition">
                        
                        <div class="absolute right-3 top-1/2 -translate-y-1/2">
                            <template x-if="isSearchingQueue">
                                <i class="fa-solid fa-spinner animate-spin text-purple-600 text-xs"></i>
                            </template>
                        </div>
                    </div>

                    <!-- Search Results Dropdown -->
                    <div x-show="showQueueDropdown && queueSearchResults.length > 0" 
                         @click.away="showQueueDropdown = false"
                         x-cloak
                         class="absolute z-50 inset-x-0 top-full mt-1.5 bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl shadow-2xl max-h-56 overflow-y-auto divide-y divide-slate-100 dark:divide-slate-800/80">
                        <template x-for="item in queueSearchResults" :key="item.id">
                            <div @click="selectQueueVideo(item)" 
                                 class="p-2.5 flex items-center gap-3 hover:bg-purple-50 dark:hover:bg-purple-950/40 cursor-pointer transition-colors">
                                <img :src="item.thumbnail" class="w-14 h-9 object-cover rounded-lg shrink-0 bg-slate-950">
                                <div class="min-w-0 flex-1">
                                    <h5 class="text-xs font-bold text-slate-900 dark:text-white truncate" x-text="item.title"></h5>
                                    <span class="text-[10px] text-slate-500 dark:text-slate-400 truncate block" x-text="item.channel"></span>
                                </div>
                                <span class="px-2 py-0.5 rounded-lg bg-purple-50 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 border border-purple-200/80 dark:border-purple-800/70 text-[10px] font-black shrink-0">
                                    Kies
                                </span>
                            </div>
                        </template>
                    </div>
                </div>

                <div x-show="newQueueUrl" class="rounded-xl border border-purple-300 dark:border-purple-800/80 bg-purple-50/60 dark:bg-purple-950/40 p-2.5 text-xs text-purple-900 dark:text-purple-200 flex items-center justify-between gap-2">
                    <span class="truncate" x-text="'Gekozen: ' + (newQueueTitle || newQueueUrl)"></span>
                    <i class="fa-solid fa-check text-emerald-500 dark:text-emerald-400 shrink-0"></i>
                </div>

                <div>
                    <label class="block text-xs font-black text-slate-900 dark:text-slate-200 mb-1">Titel in Wachtrij:</label>
                    <input type="text" x-model="newQueueTitle" placeholder="bijv. Vette Track of Docu Deel 2" maxlength="80"
                           class="w-full px-3.5 py-2 rounded-xl bg-slate-50 dark:bg-slate-800/90 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 text-xs font-medium focus:outline-none focus:border-purple-500 dark:focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition">
                </div>

                <div class="flex items-center justify-end gap-2.5 pt-2">
                    <button type="button" @click="showAddQueueModal = false" class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 border border-slate-200/80 dark:border-slate-700 text-xs font-bold cursor-pointer transition-all duration-150 active:scale-95">
                        Annuleren
                    </button>
                    <button type="submit" :disabled="!newQueueUrl" class="px-5 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-700 dark:bg-purple-600 dark:hover:bg-purple-500 disabled:opacity-50 text-white font-black text-xs uppercase tracking-wider transition-all duration-150 shadow-xs cursor-pointer active:scale-95">
                        Toevoegen
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- CHANGE VIDEO MODAL (Host only) -->
    <div x-show="showChangeVideoModal" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/75 backdrop-blur-xs">
        <div @click.away="showChangeVideoModal = false; showQueueDropdown = false;"
             class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-6 max-w-md w-full shadow-2xl space-y-4">
            
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800/80">
                <h3 class="font-black text-sm text-slate-900 dark:text-white flex items-center gap-2">
                    <i class="fa-solid fa-film text-purple-600 dark:text-purple-400"></i>
                    <span>Zaal Video Wijzigen</span>
                </h3>
                <button type="button" @click="showChangeVideoModal = false" class="text-slate-400 hover:text-slate-700 dark:hover:text-white text-base cursor-pointer">&times;</button>
            </div>

            <form action="{{ route('cinema.update-video', $room->slug) }}" method="POST" class="space-y-4">
                @csrf
                <div class="relative">
                    <label class="block text-xs font-black text-slate-900 dark:text-slate-200 mb-1">YouTube Video Zoeken of Link:</label>
                    <div class="relative">
                        <i class="fa-brands fa-youtube absolute left-3.5 top-1/2 -translate-y-1/2 text-rose-500 text-sm"></i>
                        <input type="text" x-model="queueSearchQuery" @input.debounce.350ms="onQueueSearchInput" @focus="if(queueSearchResults.length > 0) showQueueDropdown = true;"
                               placeholder="Typ zoekterm of plak YouTube URL..."
                               class="w-full pl-9 pr-8 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/90 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 text-xs font-bold focus:outline-none focus:border-purple-500 dark:focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition">
                    </div>

                    <!-- Dropdown -->
                    <div x-show="showQueueDropdown && queueSearchResults.length > 0" 
                         @click.away="showQueueDropdown = false"
                         x-cloak
                         class="absolute z-50 inset-x-0 top-full mt-1.5 bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl shadow-2xl max-h-56 overflow-y-auto divide-y divide-slate-100 dark:divide-slate-800/80">
                        <template x-for="item in queueSearchResults" :key="item.id">
                            <div @click="selectQueueVideo(item)" 
                                 class="p-2.5 flex items-center gap-3 hover:bg-purple-50 dark:hover:bg-purple-950/40 cursor-pointer transition-colors">
                                <img :src="item.thumbnail" class="w-14 h-9 object-cover rounded-lg shrink-0 bg-slate-950">
                                <div class="min-w-0 flex-1">
                                    <h5 class="text-xs font-bold text-slate-900 dark:text-white truncate" x-text="item.title"></h5>
                                    <span class="text-[10px] text-slate-500 dark:text-slate-400 truncate block" x-text="item.channel"></span>
                                </div>
                                <span class="px-2 py-0.5 rounded-lg bg-purple-50 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 border border-purple-200/80 dark:border-purple-800/70 text-[10px] font-black shrink-0">
                                    Kies
                                </span>
                            </div>
                        </template>
                    </div>

                    <input type="hidden" name="youtube_url" :value="newQueueUrl" required>
                </div>

                <div x-show="newQueueUrl" class="rounded-xl border border-purple-300 dark:border-purple-800/80 bg-purple-50/60 dark:bg-purple-950/40 p-2.5 text-xs text-purple-900 dark:text-purple-200 flex items-center justify-between gap-2">
                    <span class="truncate" x-text="'Geselecteerd: ' + (newQueueTitle || newQueueUrl)"></span>
                    <i class="fa-solid fa-check text-emerald-500 dark:text-emerald-400 shrink-0"></i>
                </div>

                <div class="flex items-center justify-end gap-2.5 pt-2">
                    <button type="button" @click="showChangeVideoModal = false" class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 border border-slate-200/80 dark:border-slate-700 text-xs font-bold cursor-pointer transition-all duration-150 active:scale-95">
                        Annuleren
                    </button>
                    <button type="submit" :disabled="!newQueueUrl" class="px-5 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-700 dark:bg-purple-600 dark:hover:bg-purple-500 disabled:opacity-50 text-white font-black text-xs uppercase tracking-wider transition-all duration-150 shadow-xs cursor-pointer active:scale-95">
                        Video Starten
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- CLOSE ROOM CONFIRMATION MODAL (Host & Staff only) -->
    <div x-show="showCloseModal" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-xs">
        <div @click.away="showCloseModal = false"
             class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-6 max-w-md w-full shadow-2xl space-y-4">
            
            <div class="flex items-center gap-3 text-rose-600">
                <div class="w-12 h-12 rounded-2xl bg-rose-50 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 border border-rose-200/80 dark:border-rose-800/70 flex items-center justify-center text-xl shrink-0">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
                <div>
                    <h3 class="font-black text-base text-slate-900 dark:text-white">Bioscoopzaal Sluiten?</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Deze actie kan niet ongedaan worden gemaakt.</p>
                </div>
            </div>

            <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed">
                Als je deze zaal sluit, worden alle actieve kijkers netjes teruggestuurd naar de lobby en verdwijnt de zaal uit de actieve zalenlijst.
            </p>

            <form action="{{ route('cinema.close', $room->slug) }}" method="POST" class="flex items-center justify-end gap-2.5 pt-2">
                @csrf
                <button type="button" @click="showCloseModal = false" class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 border border-slate-200/80 dark:border-slate-700 text-xs font-bold cursor-pointer transition-all duration-150 active:scale-95">
                    Annuleren
                </button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 dark:bg-rose-600 dark:hover:bg-rose-500 text-white font-black text-xs uppercase tracking-wider transition-all duration-150 shadow-xs cursor-pointer active:scale-95 flex items-center gap-1.5">
                    <i class="fa-solid fa-power-off"></i>
                    <span>Ja, Sluit Zaal</span>
                </button>
            </form>
        </div>
    </div>

</div>

<!-- YouTube Iframe API Script -->
<script src="https://www.youtube.com/iframe_api"></script>

<script>
let globalCinemaPlayer = null;

function cinemaTheater(roomId, roomSlug, initialVideoId, initialPlaybackSeconds, lastMessageId, initialQueue, isHost) {
    return {
        roomId: roomId,
        roomSlug: roomSlug,
        currentVideoId: initialVideoId,
        playbackSeconds: initialPlaybackSeconds,
        lastId: lastMessageId,
        isHost: isHost,
        chatInput: '',
        showGifPicker: false,
        showChangeVideoModal: false,
        showAddQueueModal: false,
        showCloseModal: false,
        newQueueUrl: '',
        newQueueTitle: '',
        queueSearchQuery: '',
        queueSearchResults: [],
        isSearchingQueue: false,
        showQueueDropdown: false,
        syncStatus: 'Synchroon',
        pollStatus: 'Realtime Verbonden',
        messages: @json($initialMessages ?? []),
        queue: initialQueue || [],
        copiedLink: false,
        isPolling: false,
        userScrolledUp: false,
        pollTimer: null,
        syncHostTimer: null,

        // Mention autocomplete state
        showMentionDropdown: false,
        mentionUsers: [],
        mentionSelectedIndex: 0,

        formatChatMessage(text) {
            if (!text) return '';
            const div = document.createElement('div');
            div.textContent = text;
            let safe = div.innerHTML;

            const mentionRegex = /(?<!<a[^>]*>)@([\p{L}\p{N}_\.\-]+)(?![^<]*<\/a>)/gu;
            safe = safe.replace(mentionRegex, (match, username) => {
                const url = '/speler/' + encodeURIComponent(username);
                return `<a href="${url}" onclick="event.stopPropagation();" style="text-decoration:none;" class="inline-flex items-center font-bold no-underline px-1.5 py-0.5 mx-0.5 rounded-md bg-purple-100/90 hover:bg-purple-200 dark:bg-purple-950/80 dark:hover:bg-purple-900/90 text-purple-700 dark:text-purple-300 border border-purple-200/80 dark:border-purple-800/60 transition cursor-pointer tracking-tight shadow-2xs"><span class="text-purple-600 dark:text-purple-400 text-[11px] font-black mr-0.5">@</span><span>${username}</span></a>`;
            });

            return safe;
        },

        async onChatInput(e) {
            const inputEl = this.$refs.cinemaChatInputField;
            if (!inputEl) return;

            const cursorPos = inputEl.selectionStart || 0;
            const textBeforeCursor = this.chatInput.slice(0, cursorPos);
            const match = textBeforeCursor.match(/(?:^|\s)@([\p{L}\p{N}_\.\-]*)$/u);

            if (match) {
                const query = match[1] || '';
                try {
                    const res = await fetch(`/chat/zoek-spelers?q=${encodeURIComponent(query)}`);
                    if (res.ok) {
                        const data = await res.json();
                        if (data.success && Array.isArray(data.players) && data.players.length > 0) {
                            this.mentionUsers = data.players;
                            this.mentionSelectedIndex = 0;
                            this.showMentionDropdown = true;
                            return;
                        }
                    }
                } catch (err) {}
            }
            this.showMentionDropdown = false;
            this.mentionUsers = [];
        },

        onChatKeydown(e) {
            if (this.showMentionDropdown && this.mentionUsers.length > 0) {
                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    this.mentionSelectedIndex = (this.mentionSelectedIndex + 1) % this.mentionUsers.length;
                    return;
                }
                if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    this.mentionSelectedIndex = (this.mentionSelectedIndex - 1 + this.mentionUsers.length) % this.mentionUsers.length;
                    return;
                }
                if (e.key === 'Enter' || e.key === 'Tab') {
                    e.preventDefault();
                    if (this.mentionUsers[this.mentionSelectedIndex]) {
                        this.selectMention(this.mentionUsers[this.mentionSelectedIndex]);
                    }
                    return;
                }
                if (e.key === 'Escape') {
                    e.preventDefault();
                    this.showMentionDropdown = false;
                    return;
                }
            }

            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                this.sendMessage();
            }
        },

        selectMention(user) {
            const inputEl = this.$refs.cinemaChatInputField;
            const cursorPos = inputEl ? inputEl.selectionStart : this.chatInput.length;
            const textBeforeCursor = this.chatInput.slice(0, cursorPos);
            const textAfterCursor = this.chatInput.slice(cursorPos);

            const match = textBeforeCursor.match(/(?:^|\s)@([\p{L}\p{N}_\.\-]*)$/u);
            if (match) {
                const atIndex = match.index + (match[0].startsWith('@') ? 0 : 1);
                const prefix = textBeforeCursor.slice(0, atIndex);
                const inserted = '@' + user.name + ' ';
                this.chatInput = prefix + inserted + textAfterCursor;
                const nextPos = prefix.length + inserted.length;

                this.$nextTick(() => {
                    if (inputEl) {
                        inputEl.focus();
                        inputEl.setSelectionRange(nextPos, nextPos);
                    }
                });
            }

            this.showMentionDropdown = false;
            this.mentionUsers = [];
        },

        async onQueueSearchInput() {
            const q = this.queueSearchQuery.trim();
            if (!q) {
                this.queueSearchResults = [];
                this.showQueueDropdown = false;
                return;
            }

            // Direct URL / ID check
            const patterns = [
                /(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^&?\/ ]{11})/i,
                /^[a-zA-Z0-9_-]{11}$/
            ];
            for (const p of patterns) {
                const m = q.match(p);
                if (m) {
                    this.newQueueUrl = 'https://www.youtube.com/watch?v=' + (m[1] || m[0]);
                    this.queueSearchResults = [];
                    this.showQueueDropdown = false;
                    return;
                }
            }

            if (q.length >= 2) {
                this.isSearchingQueue = true;
                try {
                    const res = await fetch(`/cinema/search-youtube?q=${encodeURIComponent(q)}`);
                    if (res.ok) {
                        const data = await res.json();
                        this.queueSearchResults = data.results || [];
                        this.showQueueDropdown = this.queueSearchResults.length > 0;
                    }
                } catch (e) {
                    console.error('YouTube search error:', e);
                } finally {
                    this.isSearchingQueue = false;
                }
            }
        },

        selectQueueVideo(item) {
            this.newQueueUrl = item.url;
            this.newQueueTitle = item.title;
            this.queueSearchQuery = item.title;
            this.showQueueDropdown = false;
        },

        init() {
            this.scrollToBottom(true);
            this.initYouTubePlayer();

            // Ultra-snappy realtime polling (1200ms) with debounce
            this.pollTimer = setInterval(() => this.pollState(), 1200);

            // If host, periodically sync the exact host playback time to DB (every 6s)
            if (this.isHost) {
                this.syncHostTimer = setInterval(() => this.syncHostPlayback(), 6000);
            }

            // Handle visibility changes
            document.addEventListener('visibilitychange', () => {
                if (document.hidden) {
                    if (this.pollTimer) clearInterval(this.pollTimer);
                } else {
                    this.pollState();
                    this.pollTimer = setInterval(() => this.pollState(), 1200);
                }
            });
        },

        initYouTubePlayer() {
            const self = this;
            const startSec = Math.max(0, this.playbackSeconds || 0);

            const createPlayer = () => {
                globalCinemaPlayer = new YT.Player('cinema-player', {
                    videoId: self.currentVideoId,
                    playerVars: {
                        autoplay: 1,
                        start: startSec,
                        rel: 0,
                        modestbranding: 1,
                        playsinline: 1,
                        enablejsapi: 1,
                    },
                    events: {
                        onReady: (event) => {
                            event.target.playVideo();
                            if (startSec > 0) {
                                event.target.seekTo(startSec, true);
                            }
                        },
                        onStateChange: (event) => {
                            // When video ends (YT.PlayerState.ENDED = 0)
                            if (event.data === YT.PlayerState.ENDED) {
                                if (self.isHost && self.queue.length > 0) {
                                    self.playNextFromQueue();
                                }
                            }
                        }
                    }
                });
            };

            if (window.YT && window.YT.Player) {
                createPlayer();
            } else {
                window.onYouTubeIframeAPIReady = createPlayer;
            }
        },

        async syncHostPlayback() {
            if (!this.isHost || !globalCinemaPlayer || typeof globalCinemaPlayer.getCurrentTime !== 'function') return;
            const currentTime = Math.floor(globalCinemaPlayer.getCurrentTime());
            if (currentTime < 0) return;

            try {
                await fetch(`/cinema/${this.roomSlug}/sync`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ current_seconds: currentTime }),
                });
            } catch (e) {
                // background sync silent
            }
        },

        checkScrollPosition() {
            const box = document.getElementById('cinema-chat-box');
            if (!box) return;
            const distanceToBottom = box.scrollHeight - box.scrollTop - box.clientHeight;
            this.userScrolledUp = distanceToBottom > 100;
        },

        scrollToBottom(force = false) {
            this.$nextTick(() => {
                const box = document.getElementById('cinema-chat-box');
                if (box && (force || !this.userScrolledUp)) {
                    box.scrollTop = box.scrollHeight;
                }
            });
        },

        copyShareLink() {
            navigator.clipboard.writeText(window.location.href);
            this.copiedLink = true;
            setTimeout(() => this.copiedLink = false, 2500);
        },

        async sendMessage() {
            if (!this.chatInput.trim()) return;
            const text = this.chatInput.trim();
            this.chatInput = '';

            // Optimistic message append for instantaneous UI feedback
            const tempId = 'temp-' + Date.now();
            const optimisticMsg = {
                id: tempId,
                text: text,
                gif_url: null,
                sticker_key: null,
                character_name: @js($character->name),
                avatar: @js(asset($character->avatar ?? 'characters/avatar-default.png')),
                is_staff: {{ Auth::user()->isStaff() ? 'true' : 'false' }},
                time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
                pending: true,
            };

            this.messages.push(optimisticMsg);
            this.scrollToBottom(true);

            try {
                const res = await fetch(`/cinema/${this.roomId}/chat`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ message: text }),
                });

                if (res.ok) {
                    const data = await res.json();
                    if (data.success && data.message) {
                        const idx = this.messages.findIndex(m => m.id === tempId);
                        if (idx !== -1) {
                            this.messages.splice(idx, 1, data.message);
                        } else {
                            this.messages.push(data.message);
                        }
                        this.lastId = Math.max(this.lastId, data.message.id);
                        this.scrollToBottom(true);
                    }
                }
            } catch (e) {
                console.error('Chat error:', e);
            }
        },

        async sendGif(gifUrl) {
            this.showGifPicker = false;
            try {
                const res = await fetch(`/cinema/${this.roomId}/chat`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ gif_url: gifUrl }),
                });

                if (res.ok) {
                    const data = await res.json();
                    if (data.success && data.message) {
                        this.messages.push(data.message);
                        this.lastId = Math.max(this.lastId, data.message.id);
                        this.scrollToBottom(true);
                    }
                }
            } catch (e) {
                console.error('GIF send error:', e);
            }
        },

        async submitAddToQueue() {
            if (!this.newQueueUrl.trim()) return;
            const url = this.newQueueUrl.trim();
            const title = this.newQueueTitle.trim();
            this.showAddQueueModal = false;
            this.newQueueUrl = '';
            this.newQueueTitle = '';

            try {
                const res = await fetch(`/cinema/${this.roomSlug}/queue/add`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ youtube_url: url, title: title }),
                });

                if (res.ok) {
                    const data = await res.json();
                    if (data.success && data.queue) {
                        this.queue = data.queue;
                    }
                } else {
                    const err = await res.json();
                    alert(err.message || 'Kon video niet toevoegen aan de wachtrij.');
                }
            } catch (e) {
                console.error('Queue add error:', e);
            }
        },

        async playNextFromQueue() {
            if (this.queue.length === 0) return;
            await this.playQueueItem(this.queue[0].id);
        },

        async playQueueItem(queueId) {
            try {
                const res = await fetch(`/cinema/${this.roomSlug}/queue/play/${queueId}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                });

                if (res.ok) {
                    const data = await res.json();
                    if (data.success) {
                        this.currentVideoId = data.video_id;
                        this.queue = data.queue;
                        if (globalCinemaPlayer && typeof globalCinemaPlayer.loadVideoById === 'function') {
                            globalCinemaPlayer.loadVideoById({
                                videoId: data.video_id,
                                startSeconds: 0
                            });
                        }
                    }
                }
            } catch (e) {
                console.error('Play queue item error:', e);
            }
        },

        async removeQueueItem(queueId) {
            try {
                const res = await fetch(`/cinema/${this.roomSlug}/queue/remove/${queueId}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                });

                if (res.ok) {
                    const data = await res.json();
                    if (data.success) {
                        this.queue = data.queue;
                    }
                }
            } catch (e) {
                console.error('Remove queue item error:', e);
            }
        },

        async pollState() {
            if (this.isPolling) return;
            this.isPolling = true;

            try {
                const res = await fetch(`/cinema/${this.roomId}/messages?after_id=${this.lastId}`);
                if (res.ok) {
                    const data = await res.json();
                    
                    // Check if room was closed by host
                    if (data.is_closed) {
                        this.pollStatus = 'Zaal Gesloten';
                        if (this.pollTimer) clearInterval(this.pollTimer);
                        alert('De bioscoopzaal is gesloten door de zaalhost.');
                        window.location.href = "{{ route('cinema.index') }}";
                        return;
                    }

                    // Synchronized Video Switch
                    if (data.video_id && data.video_id !== this.currentVideoId) {
                        this.currentVideoId = data.video_id;
                        if (globalCinemaPlayer && typeof globalCinemaPlayer.loadVideoById === 'function') {
                            globalCinemaPlayer.loadVideoById({
                                videoId: data.video_id,
                                startSeconds: data.playback_seconds || 0
                            });
                        }
                    } else if (!this.isHost && globalCinemaPlayer && typeof globalCinemaPlayer.getCurrentTime === 'function') {
                        // Viewer Synchronized Time Alignment (if drift > 3.0s)
                        const myTime = globalCinemaPlayer.getCurrentTime();
                        const serverTime = data.playback_seconds || 0;
                        if (serverTime > 0 && Math.abs(myTime - serverTime) > 3.0) {
                            globalCinemaPlayer.seekTo(serverTime, true);
                            if (typeof globalCinemaPlayer.getPlayerState === 'function' && globalCinemaPlayer.getPlayerState() !== YT.PlayerState.PLAYING) {
                                globalCinemaPlayer.playVideo();
                            }
                        }
                    }

                    // Synchronize Queue Playlist in Real Time
                    if (data.video_queue !== undefined) {
                        this.queue = data.video_queue;
                    }

                    // Append new incoming messages
                    if (data.messages && data.messages.length > 0) {
                        let hasNew = false;
                        data.messages.forEach(m => {
                            if (!this.messages.some(existing => existing.id === m.id)) {
                                this.messages.push(m);
                                this.lastId = Math.max(this.lastId, m.id);
                                hasNew = true;
                            }
                        });
                        if (hasNew) {
                            this.scrollToBottom(false);
                        }
                    }
                }
            } catch (e) {
                // Background poll silent catch
            } finally {
                this.isPolling = false;
            }
        }
    }
}
</script>
@endsection
