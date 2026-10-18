@extends('layouts.app')

@section('title', 'Live Chat & Privé Messenger - TBS Kliniek')
@section('meta_description', 'Chat live met alle inmates op het Kliniek Plein of stuur privéberichten, kluiscodes en tips.')

@section('content')
<div class="space-y-4" 
     x-data="tbsLiveMessenger({
        authCharId: {{ $character->id }},
        authCharName: @js($character->name),
        initialTab: @js($initialTab),
        initialRecipient: {{ $initialRecipient }}
     })"
     x-init="initMessenger()">

    <!-- HEADER BAR -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-xs relative overflow-hidden">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 relative z-10">
            <div class="flex items-center gap-3.5">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-purple-600 to-indigo-600 text-white flex items-center justify-center text-xl shadow-md shrink-0">
                    <i class="fa-solid fa-comments"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h1 class="text-lg font-black text-slate-900 dark:text-white tracking-tight">Kliniek Live Chat & DMs</h1>
                        <span class="px-2 py-0.5 rounded-full bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 font-extrabold text-[10px] uppercase tracking-wider flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Live
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                        Praat realtime met alle inmates in de kliniek, chat 1-op-1 en claim interactieve kluiscodes en level-up beloningen.
                    </p>
                </div>
            </div>

            <!-- TAB SWITCHER -->
            <div class="flex items-center gap-2 p-1 bg-slate-100 dark:bg-slate-800/80 rounded-xl shrink-0">
                <button type="button" 
                        @click="switchTab('global')" 
                        :class="activeTab === 'global' ? 'bg-white dark:bg-slate-900 text-purple-600 dark:text-purple-400 font-black shadow-xs' : 'text-slate-600 dark:text-slate-400 font-bold hover:text-slate-900'"
                        class="px-4 py-2 rounded-lg text-xs transition cursor-pointer flex items-center gap-2">
                    <i class="fa-solid fa-bullhorn text-xs"></i>
                    <span>Kliniek Plein (Openbaar)</span>
                </button>
                <button type="button" 
                        @click="switchTab('direct')" 
                        :class="activeTab === 'direct' ? 'bg-white dark:bg-slate-900 text-purple-600 dark:text-purple-400 font-black shadow-xs' : 'text-slate-600 dark:text-slate-400 font-bold hover:text-slate-900'"
                        class="px-4 py-2 rounded-lg text-xs transition cursor-pointer flex items-center gap-2 relative">
                    <i class="fa-solid fa-user-lock text-xs"></i>
                    <span>Privé Chats</span>
                    <span x-show="totalUnread > 0" 
                          x-text="totalUnread" 
                          class="px-1.5 py-0.2 rounded-full bg-rose-500 text-white font-mono text-[10px] font-black animate-pulse">
                        {{ $totalUnreadDms }}
                    </span>
                </button>
            </div>
        </div>
    </div>

    <!-- MAIN MESSENGER CARD (DESKTOP 2-COLUMN & MOBILE RESPONSIVE) -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl shadow-lg overflow-hidden flex flex-col md:flex-row h-[720px] lg:h-[780px] relative">
        
        <!-- ========================================== -->
        <!-- LEFT COLUMN: CHANNELS & CONVERSATIONS LIST -->
        <!-- ========================================== -->
        <div class="w-full md:w-80 lg:w-96 shrink-0 border-r border-slate-200 dark:border-slate-800 flex flex-col bg-slate-50/50 dark:bg-slate-900/50 h-full"
             :class="(mobileView === 'chat' && window.innerWidth < 768) ? 'hidden md:flex' : 'flex'">
            
            <!-- SEARCH & NEW CHAT HEADER -->
            <div class="p-4 border-b border-slate-200 dark:border-slate-800 space-y-3 shrink-0 bg-white dark:bg-slate-900">
                <div class="flex items-center justify-between gap-2">
                    <span class="text-xs font-black uppercase tracking-wider text-slate-500 dark:text-slate-400" 
                          x-text="activeTab === 'global' ? 'Kanalen & Ruimtes' : 'Directe Gesprekken'">
                    </span>

                    <template x-if="activeTab === 'direct'">
                        <!-- START NIEUW GESPREK KNOP -->
                        <div class="relative" x-data="{ open: false }">
                            <button type="button" @click="open = !open" 
                                    class="px-2.5 py-1 rounded-lg bg-purple-600 hover:bg-purple-700 text-white text-[11px] font-black tracking-wide transition flex items-center gap-1.5 shadow-2xs cursor-pointer">
                                <i class="fa-solid fa-pen-to-square text-[10px]"></i>
                                <span>Nieuw</span>
                            </button>
                            <!-- FRIENDS DROPDOWN -->
                            <div x-show="open" @click.away="open = false" x-cloak
                                 class="absolute right-0 top-8 z-30 w-64 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl shadow-xl p-2 space-y-1">
                                <div class="px-2.5 py-1.5 text-[11px] font-extrabold text-slate-400 uppercase tracking-wider">Kies een vriend:</div>
                                @forelse($friends as $fr)
                                    <button type="button" 
                                            @click="selectPartner({{ $fr->id }}, @js($fr->name)); open = false;"
                                            class="w-full px-2.5 py-2 rounded-xl hover:bg-purple-50 dark:hover:bg-purple-950/50 text-left transition flex items-center gap-2.5 cursor-pointer">
                                        <img src="{{ asset('images/' . ($fr->avatar ?? 'avatar-1.png')) }}" 
                                             class="w-7 h-7 rounded-lg object-cover border border-slate-200 dark:border-slate-700"
                                             alt="{{ $fr->name }}"
                                             onerror="this.src='{{ asset('images/avatar-1.png') }}'">
                                        <div class="flex-1 min-w-0">
                                            <div class="text-xs font-black text-slate-900 dark:text-white truncate">{{ $fr->name }}</div>
                                            <div class="text-[10px] text-slate-400">Level {{ $fr->level }}</div>
                                        </div>
                                    </button>
                                @empty
                                    <div class="px-3 py-2 text-xs text-slate-500 text-center">Nog geen vrienden. Voeg spelers toe via de Vriendenlijst!</div>
                                @endforelse
                            </div>
                        </div>
                    </template>
                </div>

                <!-- SEARCH FILTER INPUT -->
                <template x-if="activeTab === 'direct'">
                    <div class="relative">
                        <i class="fa-solid fa-magnifying-glass absolute left-3 top-2.5 text-slate-400 text-xs"></i>
                        <input type="text" 
                               x-model="searchQuery" 
                               placeholder="Zoek in gesprekken..." 
                               class="w-full bg-slate-100 dark:bg-slate-800 border border-transparent focus:border-purple-500 focus:bg-white dark:focus:bg-slate-900 rounded-xl pl-8 pr-3 py-1.5 text-xs text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none transition">
                    </div>
                </template>
            </div>

            <!-- CHANNELS / CONTACTS LIST STREAM -->
            <div class="flex-1 overflow-y-auto p-2 space-y-1.5 custom-scrollbar">

                <!-- 1. PUBLIC CHANNELS (WHEN TAB IS GLOBAL) -->
                <template x-if="activeTab === 'global'">
                    <div class="space-y-1">
                        <button type="button" 
                                class="w-full p-3 rounded-2xl bg-white dark:bg-slate-800/90 border border-purple-300 dark:border-purple-600/50 shadow-2xs text-left transition flex items-center gap-3">
                            <div class="w-11 h-11 rounded-xl bg-purple-500/10 dark:bg-purple-500/20 text-purple-600 dark:text-purple-400 flex items-center justify-center text-lg shrink-0">
                                <i class="fa-solid fa-bullhorn"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between">
                                    <h4 class="text-xs font-black text-slate-900 dark:text-white truncate">Kliniek Plein</h4>
                                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                </div>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400 truncate mt-0.5">Live openbare afdelingschat</p>
                            </div>
                        </button>

                        <div class="p-4 rounded-2xl bg-purple-50/70 dark:bg-purple-950/30 border border-purple-200/60 dark:border-purple-800/40 text-xs text-purple-900 dark:text-purple-300 space-y-1.5 mt-4">
                            <div class="font-extrabold flex items-center gap-1.5">
                                <i class="fa-solid fa-shield-halved text-purple-600"></i>
                                <span>Plein Regels</span>
                            </div>
                            <p class="text-[11px] text-purple-800/80 dark:text-purple-300/80 leading-relaxed">
                                Houd het gezellig en respectvol. Geen spam of phishing. Klik op een speler in de chat om direct een 1-op-1 privégesprek te openen.
                            </p>
                        </div>
                    </div>
                </template>

                <!-- 2. DIRECT CONVERSATIONS (WHEN TAB IS DIRECT) -->
                <template x-if="activeTab === 'direct'">
                    <div class="space-y-2">
                        <!-- QUICK SEARCH & INITIATE CHAT WITH ANY INMATE -->
                        <div class="relative" x-data="{ searchResults: [], isSearching: false, inmateQuery: '' }">
                            <div class="relative">
                                <input type="text" 
                                       x-model="inmateQuery" 
                                       @input.debounce.300ms="
                                           if(inmateQuery.trim().length >= 2) {
                                               isSearching = true;
                                               fetch('{{ route('chat.search-players') }}?q=' + encodeURIComponent(inmateQuery.trim()))
                                                   .then(r => r.json())
                                                   .then(d => { searchResults = d.players || []; isSearching = false; })
                                                   .catch(() => { isSearching = false; });
                                           } else {
                                               searchResults = [];
                                           }
                                       "
                                       placeholder="🔍 Zoek patiënt om te chatten..." 
                                       class="w-full bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700/60 rounded-xl px-3.5 py-2 text-xs font-bold text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-purple-500/30">
                                <template x-if="isSearching">
                                    <i class="fa-solid fa-spinner fa-spin absolute right-3 top-2.5 text-xs text-purple-500"></i>
                                </template>
                            </div>

                            <!-- Live Inmates Search Dropdown -->
                            <template x-if="searchResults.length > 0">
                                <div class="absolute left-0 right-0 top-full mt-1.5 bg-white dark:bg-[#160b33] border border-slate-200 dark:border-purple-800/60 rounded-xl shadow-xl z-50 max-h-56 overflow-y-auto divide-y divide-slate-100 dark:divide-purple-900/30 p-1">
                                    <template x-for="p in searchResults" :key="p.id">
                                        <button type="button" 
                                                @click="selectPartner(p.id, p.name); searchResults = []; inmateQuery = '';"
                                                class="w-full p-2 rounded-lg hover:bg-purple-50 dark:hover:bg-purple-900/40 flex items-center justify-between text-left transition cursor-pointer">
                                            <div class="flex items-center gap-2 min-w-0">
                                                <img :src="p.avatar" class="w-7 h-7 rounded-lg object-cover">
                                                <div class="min-w-0 truncate">
                                                    <span class="font-extrabold text-xs text-slate-900 dark:text-white truncate block" x-text="p.name"></span>
                                                    <span class="text-[10px] text-slate-400">Lvl <span x-text="p.level"></span> &bull; 🔥 <span x-text="p.respect"></span></span>
                                                </div>
                                            </div>
                                            <span class="px-2 py-1 rounded bg-purple-600 text-white text-[10px] font-black shrink-0">
                                                Chat &raquo;
                                            </span>
                                        </button>
                                    </template>
                                </div>
                            </template>
                        </div>

                        <!-- LOADING SKELETON -->
                        <template x-if="loadingConversations">
                            <div class="p-4 text-center text-xs text-slate-400 space-y-2">
                                <div class="w-6 h-6 border-2 border-purple-500 border-t-transparent rounded-full animate-spin mx-auto"></div>
                                <span>Gesprekken laden...</span>
                            </div>
                        </template>

                        <!-- CONVERSATIONS LIST -->
                        <template x-for="conv in filteredConversations" :key="conv.partner_id">
                            <button type="button" 
                                    @click="selectPartner(conv.partner_id, conv.name)"
                                    :class="activePartnerId === conv.partner_id ? 'bg-purple-50 dark:bg-purple-950/60 border-purple-300 dark:border-purple-700/60 shadow-xs' : 'bg-white dark:bg-slate-800/70 border-slate-200/80 dark:border-slate-800 hover:bg-slate-100 dark:hover:bg-slate-800'"
                                    class="w-full p-2.5 rounded-2xl border text-left transition flex items-center gap-3 cursor-pointer group relative">
                                
                                <!-- AVATAR -->
                                <div class="relative shrink-0">
                                    <template x-if="conv.is_bot">
                                        <div class="w-11 h-11 rounded-xl bg-amber-500/10 text-amber-500 border border-amber-300 dark:border-amber-700/60 flex items-center justify-center text-lg shadow-2xs">
                                            <i class="fa-solid fa-building-shield"></i>
                                        </div>
                                    </template>
                                    <template x-if="!conv.is_bot">
                                        <img :src="conv.avatar" 
                                             class="w-11 h-11 rounded-xl object-cover border border-slate-200 dark:border-slate-700 shadow-2xs"
                                             onerror="this.src='{{ asset('assets/characters/avatar_male_1.jpg') }}'">
                                    </template>
                                    <template x-if="conv.is_online">
                                        <span class="absolute -bottom-0.5 -right-0.5 w-3 h-3 bg-emerald-500 border-2 border-white dark:border-slate-900 rounded-full" title="Online"></span>
                                    </template>
                                </div>

                                <!-- CONTENT -->
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between gap-1">
                                        <h4 class="text-xs font-black text-slate-900 dark:text-white truncate flex items-center gap-1.5"
                                            :class="conv.is_bot ? 'text-amber-600 dark:text-amber-400' : ''">
                                            <span x-text="conv.name"></span>
                                            <template x-if="conv.is_bot">
                                                <i class="fa-solid fa-circle-check text-[10px] text-amber-500" title="Geverifieerd"></i>
                                            </template>
                                        </h4>
                                        <span class="text-[10px] text-slate-400 shrink-0 font-medium" x-text="conv.last_time"></span>
                                    </div>
                                    <div class="flex items-center justify-between gap-2 mt-0.5">
                                        <p class="text-[11px] text-slate-500 dark:text-slate-400 truncate" x-text="conv.last_message"></p>
                                        <template x-if="conv.unread_count > 0">
                                            <span class="px-1.5 py-0.2 rounded-full bg-rose-500 text-white font-mono text-[9px] font-black shrink-0" 
                                                  x-text="conv.unread_count"></span>
                                        </template>
                                    </div>
                                </div>
                            </button>
                        </template>

                        <!-- EMPTY STATE -->
                        <template x-if="!loadingConversations && filteredConversations.length === 0">
                            <div class="p-6 text-center text-slate-400 text-xs space-y-2">
                                <i class="fa-solid fa-comment-slash text-2xl text-slate-300"></i>
                                <p>Geen privégesprekken gevonden.</p>
                            </div>
                        </template>
                    </div>
                </template>

            </div>
        </div>

        <!-- ========================================== -->
        <!-- RIGHT COLUMN: ACTIVE CHAT STREAM & INPUT   -->
        <!-- ========================================== -->
        <div class="flex-1 flex flex-col h-full bg-slate-50/40 dark:bg-slate-950/40 overflow-hidden"
             :class="(mobileView === 'list' && window.innerWidth < 768) ? 'hidden md:flex' : 'flex'">
            
            <!-- CHAT HEADER -->
            <div class="p-3.5 sm:p-4 bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between gap-3 shrink-0">
                <div class="flex items-center gap-3">
                    <!-- MOBILE BACK BUTTON -->
                    <button type="button" 
                            @click="mobileView = 'list'" 
                            class="md:hidden w-8 h-8 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 flex items-center justify-center text-xs cursor-pointer">
                        <i class="fa-solid fa-arrow-left"></i>
                    </button>

                    <!-- HEADER TITLE INFO -->
                    <template x-if="activeTab === 'global'">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-purple-600 text-white flex items-center justify-center text-base shadow-xs">
                                <i class="fa-solid fa-bullhorn"></i>
                            </div>
                            <div>
                                <h3 class="text-xs sm:text-sm font-black text-slate-900 dark:text-white tracking-tight">Kliniek Plein (Openbaar)</h3>
                                <div class="text-[11px] text-emerald-600 dark:text-emerald-400 flex items-center gap-1 font-semibold">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                    <span>Realtime Chat met alle TBS'ers</span>
                                </div>
                            </div>
                        </div>
                    </template>

                    <template x-if="activeTab === 'direct'">
                        <div class="flex items-center gap-3">
                            <template x-if="activePartner.is_bot">
                                <div class="w-10 h-10 rounded-xl bg-amber-500/10 text-amber-500 border border-amber-300 dark:border-amber-700/60 flex items-center justify-center text-lg shadow-2xs">
                                    <i class="fa-solid fa-building-shield"></i>
                                </div>
                            </template>
                            <template x-if="!activePartner.is_bot">
                                <img :src="activePartner.avatar" 
                                     class="w-10 h-10 rounded-xl object-cover border border-slate-200 dark:border-slate-700 shadow-2xs"
                                     onerror="this.src='{{ asset('assets/characters/avatar_male_1.jpg') }}'">
                            </template>
                            <div>
                                <h3 class="text-xs sm:text-sm font-black text-slate-900 dark:text-white tracking-tight flex items-center gap-1.5">
                                    <span x-text="activePartner.name"></span>
                                    <template x-if="activePartner.is_bot">
                                        <span class="px-1.5 py-0.2 rounded bg-amber-100 dark:bg-amber-950/70 text-amber-700 dark:text-amber-300 text-[9px] font-black uppercase">Systeem Bot</span>
                                    </template>
                                </h3>
                                <div class="text-[11px] text-slate-500 dark:text-slate-400" x-text="activePartner.status_label"></div>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- RIGHT HEADER ACTIONS -->
                <div class="flex items-center gap-2">
                    <template x-if="activeTab === 'direct' && !activePartner.is_bot && activePartner.id > 0">
                        <a :href="'/speler/' + encodeURIComponent(activePartner.name)" 
                           target="_blank"
                           class="px-3 py-1.5 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-bold transition flex items-center gap-1.5">
                            <i class="fa-solid fa-address-card text-xs"></i>
                            <span class="hidden sm:inline">Dossier</span>
                        </a>
                    </template>
                    <button type="button" 
                            @click="refreshActiveChat()" 
                            class="w-8 h-8 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-500 flex items-center justify-center text-xs transition cursor-pointer"
                            title="Handmatig verversen">
                        <i class="fa-solid fa-arrows-rotate" :class="isRefreshing ? 'animate-spin' : ''"></i>
                    </button>
                </div>
            </div>

            <!-- CHAT STREAM CONTAINER -->
            <div id="tbsChatScrollContainer" 
                 class="flex-1 overflow-y-auto p-4 sm:p-6 space-y-4 custom-scrollbar">
                
                <!-- =============================== -->
                <!-- 1. PUBLIC CHAT MESSAGES STREAM  -->
                <!-- =============================== -->
                <template x-if="activeTab === 'global'">
                    <div class="space-y-3.5">
                        <template x-for="msg in publicMessagesList" :key="msg.id">
                            <div class="flex items-start gap-3 group" :class="msg.is_me ? 'flex-row-reverse' : ''">
                                <!-- AVATAR -->
                                <button type="button" 
                                        @click="if(!msg.is_me) { switchTab('direct'); selectPartner(msg.character_id, msg.name); }"
                                        class="shrink-0 cursor-pointer transition hover:scale-105"
                                        :title="!msg.is_me ? 'Klik om privé te chatten met ' + msg.name : ''">
                                    <img :src="msg.avatar || '{{ asset('assets/characters/avatar_male_1.jpg') }}'" 
                                         class="w-9 h-9 rounded-xl object-cover border border-slate-200 dark:border-purple-800/60 shadow-2xs"
                                         onerror="this.src='{{ asset('assets/characters/avatar_male_1.jpg') }}'">
                                </button>

                                <!-- MESSAGE BUBBLE -->
                                <div class="max-w-[85%] sm:max-w-md space-y-1" :class="msg.is_me ? 'text-right' : 'text-left'">
                                    <!-- SENDER HEADER -->
                                    <div class="flex items-center gap-1.5 text-[11px] font-bold flex-wrap" :class="msg.is_me ? 'justify-end' : ''">
                                        <!-- EIGENAAR STYLING -->
                                        <template x-if="msg.is_owner">
                                            <span class="text-amber-500 dark:text-amber-400 font-black hover:underline cursor-pointer flex items-center gap-1"
                                                  @click="if(!msg.is_me) { switchTab('direct'); selectPartner(msg.character_id, msg.name); }">
                                                <i class="fa-solid fa-crown text-[10px] text-amber-500 animate-bounce"></i>
                                                <span x-text="msg.name"></span>
                                            </span>
                                        </template>

                                        <!-- DIRECTIE / ADMIN STYLING -->
                                        <template x-if="msg.is_admin && !msg.is_owner">
                                            <span class="text-purple-600 dark:text-purple-300 font-black hover:underline cursor-pointer flex items-center gap-1"
                                                  @click="if(!msg.is_me) { switchTab('direct'); selectPartner(msg.character_id, msg.name); }">
                                                <i class="fa-solid fa-shield-halved text-[10px]"></i>
                                                <span x-text="msg.name"></span>
                                            </span>
                                        </template>

                                        <!-- MODERATOR STYLING -->
                                        <template x-if="msg.is_moderator && !msg.is_admin && !msg.is_owner">
                                            <span class="text-cyan-600 dark:text-cyan-400 font-black hover:underline cursor-pointer flex items-center gap-1"
                                                  @click="if(!msg.is_me) { switchTab('direct'); selectPartner(msg.character_id, msg.name); }">
                                                <i class="fa-solid fa-gavel text-[10px]"></i>
                                                <span x-text="msg.name"></span>
                                            </span>
                                        </template>

                                        <!-- REGULAR INMATE -->
                                        <template x-if="!msg.is_owner && !msg.is_admin && !msg.is_moderator">
                                            <span class="text-slate-900 dark:text-white font-black hover:underline cursor-pointer"
                                                  @click="if(!msg.is_me) { switchTab('direct'); selectPartner(msg.character_id, msg.name); }"
                                                  x-text="msg.name"></span>
                                        </template>
                                        
                                        <!-- LEVEL BADGE -->
                                        <span class="px-1.5 py-0.2 rounded bg-slate-200 dark:bg-purple-950 text-slate-700 dark:text-purple-300 text-[9px] font-black">
                                            Lvl <span x-text="msg.level"></span>
                                        </span>

                                        <!-- RESPECT BADGE -->
                                        <span class="px-1.5 py-0.2 rounded bg-amber-100 dark:bg-amber-950/70 text-amber-800 dark:text-amber-300 text-[9px] font-black flex items-center gap-0.5" title="Respect Score">
                                            <i class="fa-solid fa-fire text-amber-500 text-[8px]"></i>
                                            <span x-text="msg.respect"></span>
                                        </span>

                                        <!-- GANG TAG -->
                                        <template x-if="msg.gang_tag">
                                            <span class="px-1.5 py-0.2 rounded text-[9px] font-black uppercase text-purple-700 dark:text-purple-300 bg-purple-100 dark:bg-purple-950/70 border border-purple-200 dark:border-purple-800/60"
                                                  x-text="'[' + msg.gang_tag + ']'"></span>
                                        </template>

                                        <!-- DISTINCT STAFF ROLES -->
                                        <template x-if="msg.is_owner">
                                            <span class="px-1.5 py-0.5 rounded bg-gradient-to-r from-amber-500 to-yellow-400 text-slate-950 text-[9px] font-black uppercase tracking-wider shadow-2xs">
                                                👑 Eigenaar
                                            </span>
                                        </template>
                                        <template x-if="msg.is_admin && !msg.is_owner">
                                            <span class="px-1.5 py-0.5 rounded bg-purple-600 text-white text-[9px] font-black uppercase tracking-wider">
                                                🏛️ Directie
                                            </span>
                                        </template>
                                        <template x-if="msg.is_moderator && !msg.is_admin && !msg.is_owner">
                                            <span class="px-1.5 py-0.5 rounded bg-cyan-600 text-white text-[9px] font-black uppercase tracking-wider">
                                                🛡️ Moderator
                                            </span>
                                        </template>

                                        <span class="text-[10px] text-slate-400 font-normal ml-0.5" x-text="msg.time"></span>

                                        <!-- REPORT MESSAGE BUTTON -->
                                        <template x-if="!msg.is_me">
                                            <button type="button" 
                                                    @click="window.tbsOpenReportModal({ type: 'chat_message', id: msg.id, name: msg.name, text: msg.message })" 
                                                    class="opacity-0 group-hover:opacity-100 text-slate-400 hover:text-red-500 transition px-1 text-[10px] cursor-pointer" 
                                                    title="Rapporteer dit bericht bij de moderatie">
                                                <i class="fa-solid fa-flag"></i>
                                            </button>
                                        </template>
                                    </div>

                                    <!-- BUBBLE -->
                                    <div class="inline-block p-3 rounded-2xl text-xs leading-relaxed shadow-2xs break-words"
                                         :class="msg.is_me 
                                            ? 'bg-purple-600 text-white rounded-tr-xs text-left' 
                                            : (msg.is_owner 
                                                ? 'bg-amber-50 dark:bg-amber-950/30 border border-amber-300 dark:border-amber-700/60 text-slate-900 dark:text-amber-100 rounded-tl-xs'
                                                : (msg.is_admin || msg.is_moderator
                                                    ? 'bg-purple-50/70 dark:bg-purple-950/40 border border-purple-200 dark:border-purple-800/60 text-slate-900 dark:text-purple-100 rounded-tl-xs'
                                                    : 'bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 rounded-tl-xs border border-slate-200 dark:border-slate-700'))">
                                        <span x-text="msg.message"></span>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <template x-if="publicMessagesList.length === 0">
                            <div class="py-12 text-center text-slate-400 text-xs space-y-2">
                                <i class="fa-solid fa-comments text-3xl text-slate-300"></i>
                                <p>Nog geen berichten op het plein. Wees de eerste die iets typt!</p>
                            </div>
                        </template>
                    </div>
                </template>

                <!-- =============================== -->
                <!-- 2. DIRECT MESSAGES STREAM       -->
                <!-- =============================== -->
                <template x-if="activeTab === 'direct'">
                    <div class="space-y-4">
                        <template x-for="dm in directMessagesList" :key="dm.id">
                            <div class="space-y-2">
                                
                                <!-- ===================================== -->
                                <!-- SPECIAL CARD: KLUISCODE VOUCHER CARD  -->
                                <!-- ===================================== -->
                                <template x-if="dm.type === 'kluiscode'">
                                    <div class="mx-auto max-w-md bg-gradient-to-r from-amber-500/10 via-amber-400/5 to-transparent border-2 border-amber-400 dark:border-amber-500/60 rounded-2xl p-4 sm:p-5 shadow-sm text-slate-900 dark:text-white space-y-3">
                                        <div class="flex items-start justify-between gap-3">
                                            <div class="flex items-center gap-2.5">
                                                <div class="w-10 h-10 rounded-xl bg-amber-500 text-slate-950 flex items-center justify-center text-lg shrink-0 shadow-xs">
                                                    <i class="fa-solid fa-vault"></i>
                                                </div>
                                                <div>
                                                    <span class="text-[10px] font-black uppercase tracking-wider text-amber-600 dark:text-amber-400">Exclusieve Voucher</span>
                                                    <h4 class="text-sm font-black tracking-tight" x-text="dm.metadata?.reason || 'Geheime Kluiscode Beloning'"></h4>
                                                </div>
                                            </div>
                                            <span class="text-[10px] font-mono text-slate-400" x-text="dm.time"></span>
                                        </div>

                                        <!-- VOUCHER CODE BOX -->
                                        <div class="bg-white dark:bg-slate-900 border border-amber-300 dark:border-amber-600/60 rounded-xl p-3 flex items-center justify-between gap-3 shadow-inner">
                                            <div class="font-mono font-black text-base text-amber-600 dark:text-amber-400 tracking-wider" 
                                                 x-text="dm.metadata?.code"></div>
                                            <button type="button" 
                                                    @click="copyToClipboard(dm.metadata?.code)" 
                                                    class="px-3 py-1.5 rounded-lg bg-amber-500 hover:bg-amber-600 active:scale-95 text-slate-950 font-black text-xs transition cursor-pointer flex items-center gap-1.5 shadow-2xs">
                                                <i class="fa-solid fa-copy"></i>
                                                <span x-text="copiedCode === dm.metadata?.code ? 'Gekopieerd! ✓' : 'Kopieer'"></span>
                                            </button>
                                        </div>

                                        <!-- CONTENT & DIRECT REDEEM ACTION -->
                                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-1">
                                            <div class="text-[11px] text-slate-600 dark:text-slate-400">
                                                <span class="font-bold text-slate-800 dark:text-slate-200">Beloning: </span>
                                                <span x-text="dm.metadata?.rewards || 'Bankgeld + Sterren ⭐'"></span>
                                            </div>
                                            <a :href="'/kluis-kraak?code=' + encodeURIComponent(dm.metadata?.code)" 
                                               class="px-4 py-2 rounded-xl bg-purple-600 hover:bg-purple-700 active:scale-95 text-white font-black text-xs uppercase tracking-wider transition text-center shadow-xs flex items-center justify-center gap-1.5">
                                                <i class="fa-solid fa-unlock-keyhole"></i>
                                                <span>Direct Inwisselen</span>
                                            </a>
                                        </div>
                                    </div>
                                </template>

                                <!-- ===================================== -->
                                <!-- SPECIAL CARD: LEVEL UP CARD           -->
                                <!-- ===================================== -->
                                <template x-if="dm.type === 'level_up'">
                                    <div class="mx-auto max-w-md bg-gradient-to-r from-purple-600/10 via-indigo-600/5 to-transparent border-2 border-purple-400 dark:border-purple-500/60 rounded-2xl p-4 sm:p-5 shadow-sm text-slate-900 dark:text-white space-y-3">
                                        <div class="flex items-center gap-3">
                                            <div class="w-11 h-11 rounded-2xl bg-purple-600 text-white flex items-center justify-center text-xl shrink-0 shadow-md">
                                                <i class="fa-solid fa-award"></i>
                                            </div>
                                            <div>
                                                <span class="text-[10px] font-black uppercase tracking-wider text-purple-600 dark:text-purple-400">Level Promotie</span>
                                                <h4 class="text-sm font-black tracking-tight" x-text="'Level ' + dm.metadata?.level + ' Bereikt!'"></h4>
                                            </div>
                                        </div>
                                        <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed" x-text="dm.message"></p>
                                        <div class="bg-white/80 dark:bg-slate-900/80 rounded-xl p-2.5 text-[11px] font-bold text-purple-700 dark:text-purple-300 border border-purple-200/60 dark:border-purple-800/60 flex items-center gap-2">
                                            <i class="fa-solid fa-gift text-purple-500"></i>
                                            <span x-text="dm.metadata?.rewards"></span>
                                        </div>
                                    </div>
                                </template>

                                <!-- ===================================== -->
                                <!-- STANDARD & SYSTEM NOTIFICATION BUBBLES -->
                                <!-- ===================================== -->
                                <template x-if="dm.type !== 'kluiscode' && dm.type !== 'level_up'">
                                    <div class="flex items-start gap-3" :class="dm.is_me ? 'flex-row-reverse' : ''">
                                        <div class="max-w-[85%] sm:max-w-md space-y-1" :class="dm.is_me ? 'text-right' : 'text-left'">
                                            <div class="inline-block p-3 rounded-2xl text-xs leading-relaxed break-words whitespace-pre-line shadow-2xs"
                                                 :class="dm.is_me ? 'bg-purple-600 text-white rounded-tr-xs text-left' : 'bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 rounded-tl-xs border border-slate-200 dark:border-slate-700'">
                                                <span x-text="dm.message"></span>
                                            </div>
                                            <div class="text-[10px] text-slate-400 font-mono" x-text="dm.time"></div>
                                        </div>
                                    </div>
                                </template>

                            </div>
                        </template>

                        <template x-if="directMessagesList.length === 0">
                            <div class="py-12 text-center text-slate-400 text-xs space-y-2">
                                <i class="fa-solid fa-comment-dots text-3xl text-slate-300"></i>
                                <p>Nog geen berichten in dit gesprek. Stuur een bericht om te beginnen!</p>
                            </div>
                        </template>
                    </div>
                </template>

            </div>

            <!-- CHAT INPUT BAR (PINNED BOTTOM WITH QUICK SMILEYS) -->
            <div class="p-3 sm:p-4 bg-white dark:bg-slate-900 border-t border-slate-200 dark:border-slate-800 shrink-0 space-y-2">
                <template x-if="activeTab === 'direct' && activePartner.is_bot">
                    <div class="p-3 rounded-xl bg-slate-100 dark:bg-slate-800 text-center text-xs text-slate-500 font-bold">
                        <i class="fa-solid fa-lock text-slate-400 mr-1.5"></i>
                        Dit is een officieel notificatie-gesprek. Je kunt hier niet rechtstreeks op antwoorden.
                    </div>
                </template>

                <template x-if="activeTab === 'global' || !activePartner.is_bot">
                    <div>
                        <!-- QUICK SMILEYS ROW -->
                        <div class="flex items-center gap-1 pb-2 overflow-x-auto no-scrollbar">
                            <span class="text-[10px] font-black uppercase text-slate-400 mr-1 shrink-0"><i class="fa-regular fa-face-smile text-amber-500"></i></span>
                            <template x-for="s in ['😀', '😂', '💀', '🔥', '💰', '💉', '🚓', '🚬', '👍', '🖕', '❤️', '👑', '😎', '🤫', '⚡', '🍻', '🎉']" :key="s">
                                <button type="button" @click="addEmoji(s)" class="w-7 h-7 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 text-sm flex items-center justify-center transition cursor-pointer shrink-0" :title="s">
                                    <span x-text="s"></span>
                                </button>
                            </template>
                        </div>

                        <form @submit.prevent="sendMessage()" class="flex items-center gap-2">
                            <input type="text" 
                                   x-model="messageInput"
                                   x-ref="messageInputField"
                                   @keydown.enter.prevent="if(!event.shiftKey) sendMessage()"
                                   :placeholder="activeTab === 'global' ? 'Typ een bericht aan de afdeling... (Enter om te verzenden)' : 'Stuur een privébericht...'"
                                   maxlength="350"
                                   class="flex-1 bg-slate-100 dark:bg-slate-800 border border-transparent focus:border-purple-500 focus:bg-white dark:focus:bg-slate-900 rounded-xl px-4 py-3 text-xs text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-purple-500/20 transition">
                            
                            <button type="submit" 
                                    :disabled="isSending || messageInput.trim() === ''"
                                    class="px-5 py-3 rounded-xl bg-purple-600 hover:bg-purple-700 active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed text-white font-black text-xs uppercase tracking-wider transition shadow-sm flex items-center justify-center gap-2 cursor-pointer">
                                <i class="fa-solid fa-paper-plane" :class="isSending ? 'animate-pulse' : ''"></i>
                                <span class="hidden sm:inline">Verstuur</span>
                            </button>
                        </form>
                    </div>
                </template>
            </div>

        </div>

    </div>

</div>

<!-- SCRIPT: REALTIME ALPINE MESSENGER LOGIC -->
<script>
function tbsLiveMessenger(config) {
    return {
        authCharId: config.authCharId,
        authCharName: config.authCharName,
        activeTab: config.initialTab || 'global',
        mobileView: (config.initialRecipient > 0 || config.initialTab === 'direct') ? 'chat' : 'list',
        searchQuery: '',
        
        // Public chat state
        publicMessagesList: [],
        lastPublicId: 0,
        
        // Direct messages state
        conversationsList: [],
        loadingConversations: false,
        activePartnerId: config.initialRecipient || 0,
        activePartner: {
            id: 0,
            name: '🏥 Kliniek Directie',
            avatar: 'directie-bot.png',
            is_bot: true,
            is_online: true,
            status_label: 'Systeem Bot'
        },
        directMessagesList: [],
        totalUnread: 0,

        // Input state
        messageInput: '',
        isSending: false,
        isRefreshing: false,
        copiedCode: null,
        pollTimer: null,

        get filteredConversations() {
            if (!this.searchQuery.trim()) {
                return this.conversationsList;
            }
            const q = this.searchQuery.toLowerCase();
            return this.conversationsList.filter(c => c.name.toLowerCase().includes(q));
        },

        addEmoji(emoji) {
            this.messageInput += emoji;
            this.$nextTick(() => {
                if (this.$refs.messageInputField) {
                    this.$refs.messageInputField.focus();
                }
            });
        },

        initMessenger() {
            if (this.activeTab === 'global') {
                this.loadPublicMessages();
            } else {
                this.loadConversations();
                this.loadDirectMessages(this.activePartnerId);
            }

            // Polling interval: every 3.5s for snappy live chat
            this.pollTimer = setInterval(() => {
                if (document.visibilityState === 'hidden') return;
                this.pollUpdates();
            }, 3500);

            // Clean up on page navigation
            window.addEventListener('beforeunload', () => {
                if (this.pollTimer) clearInterval(this.pollTimer);
            });
        },

        switchTab(tab) {
            this.activeTab = tab;
            if (tab === 'global') {
                this.mobileView = 'chat';
                this.loadPublicMessages();
            } else {
                this.loadConversations();
                if (this.activePartnerId === 0 && this.directMessagesList.length === 0) {
                    this.loadDirectMessages(0);
                }
            }
        },

        selectPartner(partnerId, partnerName = '') {
            this.activePartnerId = partnerId;
            this.mobileView = 'chat';
            this.loadDirectMessages(partnerId);
        },

        async loadPublicMessages() {
            try {
                const res = await fetch('{{ route("chat.public.messages") }}?channel=global');
                const data = await res.json();
                if (data.success) {
                    this.publicMessagesList = data.messages;
                    this.lastPublicId = data.last_id;
                    this.scrollToBottom();
                }
            } catch(e) {}
        },

        async loadConversations() {
            this.loadingConversations = true;
            try {
                const res = await fetch('{{ route("chat.conversations") }}');
                const data = await res.json();
                if (data.success) {
                    this.conversationsList = data.conversations;
                    this.totalUnread = data.conversations.reduce((acc, c) => acc + (c.unread_count || 0), 0);
                }
            } catch(e) {} finally {
                this.loadingConversations = false;
            }
        },

        async loadDirectMessages(partnerId) {
            try {
                const res = await fetch(`/chat/prive/${partnerId}`);
                const data = await res.json();
                if (data.success) {
                    this.activePartner = data.partner;
                    this.directMessagesList = data.messages;
                    this.scrollToBottom();
                    // Update unread in conversations list
                    const conv = this.conversationsList.find(c => c.partner_id === partnerId);
                    if (conv) conv.unread_count = 0;
                }
            } catch(e) {}
        },

        playChime() {
            try {
                const AudioCtx = window.AudioContext || window.webkitAudioContext;
                if (!AudioCtx) return;
                const ctx = new AudioCtx();
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();
                osc.type = 'sine';
                osc.frequency.setValueAtTime(587.33, ctx.currentTime);
                osc.frequency.exponentialRampToValueAtTime(880, ctx.currentTime + 0.12);
                gain.gain.setValueAtTime(0.15, ctx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.35);
                osc.connect(gain);
                gain.connect(ctx.destination);
                osc.start();
                osc.stop(ctx.currentTime + 0.35);
            } catch(e) {}
        },

        async pollUpdates() {
            if (this.activeTab === 'global') {
                try {
                    const res = await fetch(`{{ route("chat.public.messages") }}?channel=global&after_id=${this.lastPublicId}`);
                    const data = await res.json();
                    if (data.success && data.messages.length > 0) {
                        const newOthers = data.messages.filter(m => !m.is_me);
                        if (newOthers.length > 0) this.playChime();
                        this.publicMessagesList = this.publicMessagesList.concat(data.messages);
                        this.lastPublicId = data.last_id;
                        this.scrollToBottom();
                    }
                } catch(e) {}
            } else {
                // Poll active DM thread
                try {
                    const res = await fetch(`/chat/prive/${this.activePartnerId}`);
                    const data = await res.json();
                    if (data.success) {
                        if (data.messages.length > this.directMessagesList.length) {
                            const newCount = data.messages.length - this.directMessagesList.length;
                            const latestNew = data.messages.slice(-newCount);
                            if (latestNew.some(m => !m.is_me)) this.playChime();
                            this.directMessagesList = data.messages;
                            this.scrollToBottom();
                        }
                    }
                } catch(e) {}
            }
        },

        async sendMessage() {
            const text = this.messageInput.trim();
            if (!text || this.isSending) return;

            this.isSending = true;

            try {
                if (this.activeTab === 'global') {
                    const res = await fetch('{{ route("chat.public.send") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ message: text, channel: 'global' })
                    });
                    const data = await res.json();
                    if (data.success) {
                        this.publicMessagesList.push(data.message);
                        this.lastPublicId = data.message.id;
                        this.messageInput = '';
                        this.scrollToBottom();
                    } else {
                        alert(data.message || 'Kon bericht niet versturen.');
                    }
                } else {
                    const res = await fetch(`/chat/prive/${this.activePartnerId}/verzenden`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ message: text })
                    });
                    const data = await res.json();
                    if (data.success) {
                        this.directMessagesList.push(data.message);
                        this.messageInput = '';
                        this.scrollToBottom();
                        this.loadConversations();
                    } else {
                        alert(data.message || 'Kon bericht niet versturen.');
                    }
                }
            } catch(e) {
                alert('Netwerkfout bij verzenden.');
            } finally {
                this.isSending = false;
            }
        },

        async refreshActiveChat() {
            this.isRefreshing = true;
            if (this.activeTab === 'global') {
                await this.loadPublicMessages();
            } else {
                await this.loadDirectMessages(this.activePartnerId);
                await this.loadConversations();
            }
            setTimeout(() => { this.isRefreshing = false; }, 400);
        },

        copyToClipboard(code) {
            if (!code) return;
            navigator.clipboard.writeText(code).then(() => {
                this.copiedCode = code;
                setTimeout(() => { this.copiedCode = null; }, 2500);
            });
        },

        scrollToBottom(smooth = false) {
            const doScroll = () => {
                const el = document.getElementById('tbsChatScrollContainer');
                if (el) {
                    if (smooth) {
                        el.scrollTo({ top: el.scrollHeight, behavior: 'smooth' });
                    } else {
                        el.scrollTop = el.scrollHeight;
                    }
                }
            };
            doScroll();
            this.$nextTick(() => {
                doScroll();
                requestAnimationFrame(() => {
                    doScroll();
                    setTimeout(doScroll, 40);
                    setTimeout(doScroll, 100);
                    setTimeout(doScroll, 200);
                    setTimeout(doScroll, 400);
                });
            });
        }
    };
}
</script>
@endsection
