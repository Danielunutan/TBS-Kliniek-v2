<!-- UNIVERSAL REPORT MODAL -->
<div x-data="tbsGlobalReportModal()" 
     x-show="isOpen" 
     x-cloak 
     style="display: none;" 
     class="fixed inset-0 z-[99999] overflow-y-auto"
     aria-labelledby="modal-title" role="dialog" aria-modal="true">
    
    <div class="fixed inset-0 bg-black/70 backdrop-blur-xs transition-opacity" @click="close()"></div>

    <div class="flex min-h-screen items-center justify-center p-4 text-center sm:p-0">
        <div class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-[#12072b] border border-slate-200 dark:border-purple-800/60 text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-md p-6">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-purple-900/40">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl bg-red-100 dark:bg-red-950/60 text-red-600 dark:text-red-400 flex items-center justify-center text-base">
                        <i class="fa-solid fa-flag"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-black text-slate-900 dark:text-white" id="modal-title">Melding Indienen</h3>
                        <p class="text-[11px] text-slate-400">Kliniek Moderatie & Beveiliging</p>
                    </div>
                </div>
                <button type="button" @click="close()" class="text-slate-400 hover:text-slate-600 dark:hover:text-white text-xl p-1 cursor-pointer">
                    &times;
                </button>
            </div>

            <form @submit.prevent="submitReport()" class="space-y-4 pt-4">
                <div class="p-3 rounded-xl bg-slate-50 dark:bg-purple-950/30 border border-slate-200/80 dark:border-purple-800/40 text-xs">
                    <span class="text-[10px] font-black uppercase text-slate-400 block mb-0.5">Geraporteerd Onderwerp:</span>
                    <div class="font-extrabold text-slate-800 dark:text-white flex items-center gap-2">
                        <span class="px-2 py-0.5 rounded bg-purple-100 dark:bg-purple-900/60 text-purple-700 dark:text-purple-300 text-[10px] uppercase font-black" x-text="reportedTypeLabel"></span>
                        <span class="text-slate-900 dark:text-white font-black truncate" x-text="reportedName"></span>
                    </div>
                    <template x-if="reportedText">
                        <p class="mt-1.5 text-[11px] text-slate-500 dark:text-purple-300 italic line-clamp-2" x-text="'\"' + reportedText + '\"'"></p>
                    </template>
                </div>

                <div>
                    <label class="block text-xs font-black text-slate-700 dark:text-slate-300 mb-1">Reden van de melding *</label>
                    <select x-model="reason" required class="w-full bg-slate-100 dark:bg-slate-900 border border-slate-200 dark:border-purple-800/50 rounded-xl p-2.5 text-xs font-bold text-slate-800 dark:text-white focus:ring-2 focus:ring-purple-500 focus:outline-none">
                        <option value="">-- Kies een reden --</option>
                        <option value="Belediging / Schelden">Belediging / Schelden</option>
                        <option value="Ongepaste of kwetsende naam">Ongepaste of kwetsende naam</option>
                        <option value="Oplichting / Scam">Oplichting / Scam</option>
                        <option value="Spam / Ongewenste reclame">Spam / Ongewenste reclame</option>
                        <option value="Valsspelen / Multi-accounting">Valsspelen / Multi-accounting</option>
                        <option value="Bedreiging of intimidatie">Bedreiging of intimidatie</option>
                        <option value="Overige misdraging">Overige misdraging</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-black text-slate-700 dark:text-slate-300 mb-1">Toelichting (optioneel)</label>
                    <textarea x-model="details" rows="3" placeholder="Wat is er gebeurd? Extra details helpen de moderatoren..." class="w-full bg-slate-100 dark:bg-slate-900 border border-slate-200 dark:border-purple-800/50 rounded-xl p-2.5 text-xs text-slate-800 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-purple-500 focus:outline-none"></textarea>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" @click="close()" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-bold transition cursor-pointer">
                        Annuleren
                    </button>
                    <button type="submit" :disabled="isSubmitting || !reason" class="px-5 py-2 rounded-xl bg-red-600 hover:bg-red-700 disabled:opacity-50 text-white text-xs font-black transition flex items-center gap-1.5 shadow-sm cursor-pointer">
                        <i class="fa-solid fa-flag"></i>
                        <span x-text="isSubmitting ? 'Bezig met versturen...' : 'Melding Versturen'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MINI LIVE CHAT & HELPBOT WIDGET (Docked Bottom Right) -->
<div id="tbs-floating-mini-chat" x-data="tbsMiniLiveChat()" class="z-[9990]">

    <!-- FLOATING NEW MESSAGE TOAST BANNER -->
    <template x-if="toastNotification && !isOpen">
        <div style="position: fixed; bottom: 80px; right: 20px; z-index: 9999;"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             @click="isOpen = true; toastNotification = null;"
             class="bg-white dark:bg-[#160a33] border-2 border-emerald-500/80 rounded-2xl p-3 shadow-2xl flex items-center gap-3 cursor-pointer max-w-xs transform hover:scale-102 transition">
            <div class="relative w-9 h-9 rounded-xl overflow-hidden shrink-0 border border-slate-200 dark:border-purple-800">
                <img :src="toastNotification.avatar || '/characters/avatar-default.png'" class="w-full h-full object-cover">
                <span class="absolute top-0 right-0 w-2.5 h-2.5 rounded-full bg-emerald-500 ring-2 ring-white animate-ping"></span>
            </div>
            <div class="min-w-0 flex-1">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-black text-slate-900 dark:text-white truncate" x-text="toastNotification.name"></span>
                    <span class="text-[9px] font-bold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950 px-1 rounded">NIEUW</span>
                </div>
                <p class="text-[11px] text-slate-600 dark:text-purple-200 truncate" x-text="toastNotification.text"></p>
            </div>
        </div>
    </template>

    <!-- FLOATING CHAT WINDOW POPUP -->
    <div x-show="isOpen" x-cloak style="display: none; position: fixed; bottom: 74px; right: 20px; width: 390px; max-width: calc(100vw - 30px); height: 540px; max-height: calc(100vh - 90px); z-index: 9999;"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-4 scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
         x-transition:leave-end="opacity-0 translate-y-4 scale-95"
         class="bg-white dark:bg-[#110726] border border-slate-200 dark:border-purple-900/60 rounded-2xl shadow-2xl flex flex-col overflow-hidden text-slate-800 dark:text-slate-100">
        
        <!-- HEADER WITH TABS -->
        <div class="bg-purple-700 dark:bg-purple-900/90 p-2.5 border-b border-purple-800 text-white shrink-0 space-y-2 shadow-xs">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-xl bg-white/20 border border-white/30 flex items-center justify-center p-1 shadow-xs overflow-hidden">
                        <img src="{{ asset('assets/logo.png') }}?v=3" alt="TBS Logo" class="w-full h-full object-contain">
                    </div>
                    <div>
                        <h4 class="text-xs font-black tracking-tight flex items-center gap-1.5">
                            <span>TBS Kliniek Chat</span>
                            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        </h4>
                    </div>
                </div>

                <div class="flex items-center gap-1">
                    <a href="{{ route('chat.index') }}" title="Volledig scherm openen" class="p-1.5 rounded-lg text-white/80 hover:text-white hover:bg-white/10 text-xs transition">
                        <i class="fa-solid fa-up-right-and-down-left-from-center"></i>
                    </a>
                    <button type="button" @click="isOpen = false" class="p-1.5 rounded-lg text-white/80 hover:text-white hover:bg-white/10 text-sm transition cursor-pointer">
                        &times;
                    </button>
                </div>
            </div>

            <!-- TABS SWITCHER -->
            <div class="grid grid-cols-3 gap-1 bg-black/20 p-1 rounded-xl text-[11px] font-extrabold text-center">
                <button type="button" @click="setTab('global')" 
                        :class="activeTab === 'global' ? 'bg-white text-purple-900 shadow-xs' : 'text-purple-200 hover:text-white'"
                        class="py-1 rounded-lg transition cursor-pointer flex items-center justify-center gap-1">
                    <i class="fa-solid fa-bullhorn text-[10px]"></i>
                    <span>Plein</span>
                </button>
                <button type="button" @click="setTab('direct')" 
                        :class="activeTab === 'direct' ? 'bg-white text-purple-900 shadow-xs' : 'text-purple-200 hover:text-white'"
                        class="py-1 rounded-lg transition cursor-pointer flex items-center justify-center gap-1 relative">
                    <i class="fa-solid fa-envelope text-[10px]"></i>
                    <span>Privé</span>
                    <template x-if="unreadDmsCount > 0">
                        <span class="w-2 h-2 rounded-full bg-rose-500 animate-pulse"></span>
                    </template>
                </button>
                <button type="button" @click="setTab('help')" 
                        :class="activeTab === 'help' ? 'bg-white text-purple-900 shadow-xs' : 'text-purple-200 hover:text-white'"
                        class="py-1 rounded-lg transition cursor-pointer flex items-center justify-center gap-1">
                    <i class="fa-solid fa-robot text-[10px]"></i>
                    <span>Hulp AI</span>
                </button>
            </div>
        </div>

        <!-- 1. TAB: ALGEMENE CHAT (KLINIEK PLEIN) -->
        <div x-show="activeTab === 'global'" class="flex-1 flex flex-col min-h-0">
            <!-- Messages Stream -->
            <div x-ref="miniChatScroll" class="flex-1 min-h-0 p-3 overflow-y-auto space-y-3 text-xs bg-slate-50/70 dark:bg-[#0d041e]/50">
                <template x-for="msg in publicMessages" :key="msg.id">
                    <div class="flex items-start gap-2 group" :class="msg.is_me ? 'flex-row-reverse' : ''">
                        <img :src="msg.avatar || '/characters/avatar-default.png'" 
                             class="w-7 h-7 rounded-lg object-cover border border-slate-200 dark:border-purple-800/60 shrink-0"
                             onerror="this.src='/characters/avatar-default.png'">

                        <div class="max-w-[82%] space-y-0.5" :class="msg.is_me ? 'text-right' : 'text-left'">
                            <!-- Sender Meta -->
                            <div class="flex items-center gap-1 text-[10px] font-bold flex-wrap" :class="msg.is_me ? 'justify-end' : ''">
                                <!-- EIGENAAR -->
                                <template x-if="msg.is_owner">
                                    <span class="text-amber-500 dark:text-amber-400 font-black flex items-center gap-0.5">
                                        <i class="fa-solid fa-crown text-[9px] text-amber-500"></i>
                                        <span x-text="msg.name"></span>
                                    </span>
                                </template>
                                <!-- ADMIN / DIRECTIE -->
                                <template x-if="msg.is_admin && !msg.is_owner">
                                    <span class="text-purple-600 dark:text-purple-300 font-black flex items-center gap-0.5">
                                        <i class="fa-solid fa-shield-halved text-[9px]"></i>
                                        <span x-text="msg.name"></span>
                                    </span>
                                </template>
                                <!-- MODERATOR -->
                                <template x-if="msg.is_moderator && !msg.is_admin && !msg.is_owner">
                                    <span class="text-cyan-600 dark:text-cyan-400 font-black flex items-center gap-0.5">
                                        <i class="fa-solid fa-gavel text-[9px]"></i>
                                        <span x-text="msg.name"></span>
                                    </span>
                                </template>
                                <!-- REGULAR -->
                                <template x-if="!msg.is_owner && !msg.is_admin && !msg.is_moderator">
                                    <span class="text-slate-900 dark:text-white font-extrabold" x-text="msg.name"></span>
                                </template>

                                <!-- LEVEL -->
                                <span class="px-1 py-0.2 rounded bg-slate-200 dark:bg-purple-950 text-slate-700 dark:text-purple-300 text-[8px] font-black">
                                    L<span x-text="msg.level"></span>
                                </span>

                                <!-- RESPECT -->
                                <span class="px-1 py-0.2 rounded bg-amber-100 dark:bg-amber-950/70 text-amber-800 dark:text-amber-300 text-[8px] font-black flex items-center gap-0.5">
                                    <i class="fa-solid fa-fire text-amber-500 text-[7px]"></i>
                                    <span x-text="msg.respect"></span>
                                </span>

                                <!-- STAFF BADGE -->
                                <template x-if="msg.is_owner">
                                    <span class="px-1 rounded bg-amber-500 text-slate-950 text-[8px] font-black">👑 EIGENAAR</span>
                                </template>
                                <template x-if="msg.is_admin && !msg.is_owner">
                                    <span class="px-1 rounded bg-purple-600 text-white text-[8px] font-black">🏛️ DIRECTIE</span>
                                </template>
                                <template x-if="msg.is_moderator && !msg.is_admin && !msg.is_owner">
                                    <span class="px-1 rounded bg-cyan-600 text-white text-[8px] font-black">🛡️ MOD</span>
                                </template>

                                <span class="text-slate-400 text-[9px]" x-text="msg.time"></span>

                                <!-- REPORT -->
                                <template x-if="!msg.is_me">
                                    <button type="button" @click="window.tbsOpenReportModal({ type: 'chat_message', id: msg.id, name: msg.name, text: msg.message })" class="opacity-0 group-hover:opacity-100 text-slate-400 hover:text-red-500 transition px-0.5 text-[9px] cursor-pointer">
                                        <i class="fa-solid fa-flag"></i>
                                    </button>
                                </template>
                            </div>

                            <!-- Bubble -->
                            <div class="inline-block p-2.5 rounded-2xl text-xs leading-relaxed break-words shadow-2xs"
                                 :class="msg.is_me 
                                    ? 'bg-purple-600 text-white rounded-tr-xs text-left' 
                                    : (msg.is_owner 
                                        ? 'bg-amber-50 dark:bg-amber-950/30 border border-amber-300 dark:border-amber-700/60 text-slate-900 dark:text-amber-100 rounded-tl-xs'
                                        : (msg.is_admin || msg.is_moderator 
                                            ? 'bg-purple-50/70 dark:bg-purple-950/40 border border-purple-200 dark:border-purple-800/60 text-slate-900 dark:text-purple-100 rounded-tl-xs'
                                            : 'bg-white dark:bg-purple-950/40 text-slate-800 dark:text-purple-100 rounded-tl-xs border border-slate-200 dark:border-purple-800/40'))">
                                <span x-text="msg.message"></span>
                            </div>
                        </div>
                    </div>
                </template>

                <template x-if="publicMessages.length === 0">
                    <div class="py-12 text-center text-slate-400 text-xs">
                        Nog geen berichten op het plein. Start het gesprek!
                    </div>
                </template>
            </div>

            <!-- SMILEYS & INPUT BAR -->
            <div class="p-2.5 bg-white dark:bg-[#150a30] border-t border-slate-200 dark:border-purple-900/40 shrink-0 space-y-1.5">
                <div class="flex items-center gap-1 overflow-x-auto no-scrollbar">
                    <template x-for="s in ['😀', '😂', '💀', '🔥', '💰', '💉', '🚓', '🚬', '👍', '🖕', '❤️', '👑', '😎', '⚡', '🎉']" :key="s">
                        <button type="button" @click="inputMessage += s" class="w-6 h-6 rounded hover:bg-slate-100 dark:hover:bg-purple-900/40 text-xs flex items-center justify-center transition cursor-pointer shrink-0" :title="s">
                            <span x-text="s"></span>
                        </button>
                    </template>
                </div>

                <form @submit.prevent="sendPublicMessage()" class="flex items-center gap-1.5">
                    <input type="text" x-model="inputMessage" placeholder="Typ een bericht... (Enter)" maxlength="350"
                           class="flex-1 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-purple-800/50 rounded-xl px-3 py-2 text-xs text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-purple-500">
                    <button type="submit" :disabled="isSending || !inputMessage.trim()" class="w-8 h-8 rounded-xl bg-purple-600 hover:bg-purple-700 disabled:opacity-40 text-white flex items-center justify-center text-xs transition cursor-pointer shadow-xs shrink-0">
                        <i class="fa-solid fa-paper-plane"></i>
                    </button>
                </form>
            </div>
        </div>

        <!-- 2. TAB: PRIVÉ CHAT & CONTACTEN -->
        <div x-show="activeTab === 'direct'" class="flex-1 flex flex-col min-h-0 p-3 overflow-y-auto space-y-3 bg-slate-50/70 dark:bg-[#0d041e]/50">
            <!-- QUICK INMATE SEARCH -->
            <div class="relative" x-data="{ searchResults: [], isSearching: false, q: '' }">
                <input type="text" x-model="q" 
                       @input.debounce.300ms="
                           if(q.trim().length >= 2) {
                               isSearching = true;
                               fetch('{{ route('chat.search-players') }}?q=' + encodeURIComponent(q.trim()))
                                   .then(r => r.json())
                                   .then(d => { searchResults = d.players || []; isSearching = false; })
                                   .catch(() => { isSearching = false; });
                           } else {
                               searchResults = [];
                           }
                       "
                       placeholder="🔍 Zoek patiënt om te chatten..." 
                       class="w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-purple-800/50 rounded-xl px-3 py-2 text-xs font-bold text-slate-800 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-purple-500">
                
                <template x-if="searchResults.length > 0">
                    <div class="absolute left-0 right-0 top-full mt-1 bg-white dark:bg-[#160b33] border border-slate-200 dark:border-purple-800/60 rounded-xl shadow-xl z-50 max-h-48 overflow-y-auto p-1 divide-y divide-slate-100 dark:divide-purple-900/30">
                        <template x-for="p in searchResults" :key="p.id">
                            <a :href="'/chat?tab=direct&recipient=' + p.id" class="w-full p-2 rounded-lg hover:bg-purple-50 dark:hover:bg-purple-900/40 flex items-center justify-between transition">
                                <div class="flex items-center gap-2 truncate">
                                    <img :src="p.avatar" class="w-6 h-6 rounded-md object-cover">
                                    <span class="text-xs font-bold text-slate-900 dark:text-white truncate" x-text="p.name"></span>
                                </div>
                                <span class="px-2 py-0.5 rounded bg-purple-600 text-white text-[10px] font-black shrink-0">Chat &raquo;</span>
                            </a>
                        </template>
                    </div>
                </template>
            </div>

            <!-- PINNED DIRECTIE BOT -->
            <a href="{{ route('chat.index', ['tab' => 'direct', 'recipient' => 0]) }}" class="p-2.5 rounded-xl bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800/40 flex items-center justify-between transition hover:border-amber-400">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-amber-500/20 text-amber-600 flex items-center justify-center text-sm font-black">
                        <i class="fa-solid fa-building-shield"></i>
                    </div>
                    <div>
                        <div class="text-xs font-black text-amber-900 dark:text-amber-300">🏥 Kliniek Directie</div>
                        <div class="text-[10px] text-amber-700/80 dark:text-amber-400/80">Kluiscodes & Level-Up Beloningen</div>
                    </div>
                </div>
                <i class="fa-solid fa-chevron-right text-xs text-amber-500"></i>
            </a>

            <div class="pt-2 text-center space-y-2">
                <p class="text-xs text-slate-500 dark:text-slate-400">Open de complete messenger voor al je 1-op-1 gesprekken en geschiedenis.</p>
                <a href="{{ route('chat.index', ['tab' => 'direct']) }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-purple-600 hover:bg-purple-700 text-white text-xs font-black transition shadow-xs">
                    <i class="fa-solid fa-comments"></i>
                    <span>Naar Messenger Inbox</span>
                </a>
            </div>
        </div>

        <!-- 3. TAB: AI KENNISBANK & HELPBOT -->
        <div x-show="activeTab === 'help'" class="flex-1 flex flex-col min-h-0">
            <div x-ref="helpBox" class="flex-1 min-h-0 p-3 overflow-y-auto space-y-2.5 text-xs bg-slate-50/70 dark:bg-[#0d041e]/50">
                <template x-for="(msg, i) in helpMessages" :key="i">
                    <div :class="msg.from === 'bot' ? 'flex flex-col items-start' : 'flex flex-col items-end'">
                        <div :class="msg.from === 'bot' ? 'bg-white dark:bg-purple-950/40 border border-slate-200 dark:border-purple-800/40 text-slate-800 dark:text-purple-100' : 'bg-purple-600 text-white font-medium'"
                             class="rounded-2xl p-2.5 max-w-[88%] space-y-1.5 shadow-2xs">
                            <p class="whitespace-pre-line leading-relaxed" x-text="msg.text"></p>
                            <template x-if="msg.links && msg.links.length > 0">
                                <div class="pt-1 flex flex-wrap gap-1 border-t border-slate-100 dark:border-purple-800/30">
                                    <template x-for="lnk in msg.links">
                                        <a :href="lnk.url" class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-purple-50 dark:bg-purple-900/50 text-purple-700 dark:text-purple-200 text-[10px] font-extrabold border border-purple-200">
                                            <span x-text="lnk.text"></span> &raquo;
                                        </a>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>

                <!-- Quick FAQ Topic Chips -->
                <div class="pt-1.5 flex flex-wrap gap-1">
                    <button type="button" @click="sendHelpQuery('Hoe krijg ik energie?')" class="px-2 py-0.5 rounded-full bg-white dark:bg-purple-950/40 border border-slate-200 dark:border-purple-800/40 text-slate-700 dark:text-slate-200 text-[10px] font-bold">
                        ⚡ Energie
                    </button>
                    <button type="button" @click="sendHelpQuery('Hoe kom ik sneller uit de isoleercel?')" class="px-2 py-0.5 rounded-full bg-white dark:bg-purple-950/40 border border-slate-200 dark:border-purple-800/40 text-slate-700 dark:text-slate-200 text-[10px] font-bold">
                        🔒 Isoleercel
                    </button>
                    <button type="button" @click="sendHelpQuery('Hoe kraak ik de kluis?')" class="px-2 py-0.5 rounded-full bg-white dark:bg-purple-950/40 border border-slate-200 dark:border-purple-800/40 text-slate-700 dark:text-slate-200 text-[10px] font-bold">
                        🔐 Kluis Kraken
                    </button>
                    <button type="button" @click="sendHelpQuery('Hoe verdien ik gratis VIP?')" class="px-2 py-0.5 rounded-full bg-white dark:bg-purple-950/40 border border-slate-200 dark:border-purple-800/40 text-slate-700 dark:text-slate-200 text-[10px] font-bold">
                        👑 Gratis VIP
                    </button>
                </div>
            </div>

            <!-- Help input -->
            <form @submit.prevent="sendHelpQuery()" class="p-2.5 bg-white dark:bg-[#150a30] border-t border-slate-200 dark:border-purple-900/40 flex items-center gap-1.5 shrink-0">
                <input type="text" x-model="helpInput" placeholder="Stel een vraag aan de kennisbank..." 
                       class="flex-1 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-purple-800/50 rounded-xl px-3 py-2 text-xs text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-purple-500">
                <button type="submit" class="w-8 h-8 rounded-xl bg-purple-600 hover:bg-purple-700 text-white flex items-center justify-center text-xs transition cursor-pointer shadow-xs shrink-0">
                    <i class="fa-solid fa-paper-plane"></i>
                </button>
            </form>
        </div>

    </div>

    <!-- FLOATING LAUNCHER BUTTON (Bottom Right Corner) -->
    <button type="button" @click="isOpen = !isOpen"
            style="position: fixed; bottom: 20px; right: 20px; z-index: 9998;"
            class="px-4 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-black text-xs shadow-lg shadow-purple-900/25 flex items-center gap-2.5 cursor-pointer transition transform hover:scale-102 active:scale-98 border border-purple-400">
        <div class="relative w-5 h-5 shrink-0 flex items-center justify-center">
            <i class="fa-solid fa-comments text-white text-sm" x-show="!isOpen"></i>
            <i class="fa-solid fa-xmark text-white text-sm" x-show="isOpen"></i>
            <span class="absolute -top-1 -right-1 w-2 h-2 rounded-full bg-emerald-400 ring-2 ring-purple-600 animate-pulse"></span>
        </div>
        <span class="tracking-wide" x-text="isOpen ? 'Sluit Chat' : 'Live Chat'">Live Chat</span>
        <template x-if="unreadDmsCount > 0">
            <span class="bg-rose-500 text-white text-[9px] font-extrabold px-1.5 py-0.2 rounded-full animate-bounce" x-text="unreadDmsCount"></span>
        </template>
    </button>
</div>

<!-- SCRIPTS FOR REPORT MODAL AND MINI CHAT -->
<script>
    // Universal Report Modal Handler
    function tbsGlobalReportModal() {
        return {
            isOpen: false,
            reportedType: 'character',
            reportedId: 0,
            reportedName: '',
            reportedText: '',
            reason: '',
            details: '',
            isSubmitting: false,
            get reportedTypeLabel() {
                if (this.reportedType === 'character') return 'Patiënt Profiel';
                if (this.reportedType === 'gang') return 'Bende / Gang';
                if (this.reportedType === 'chat_message') return 'Chatbericht';
                return this.reportedType;
            },
            init() {
                window.tbsOpenReportModal = (opts) => {
                    this.reportedType = opts.type || 'character';
                    this.reportedId = opts.id;
                    this.reportedName = opts.name || '';
                    this.reportedText = opts.text || '';
                    this.reason = '';
                    this.details = '';
                    this.isSubmitting = false;
                    this.isOpen = true;
                };
            },
            close() {
                this.isOpen = false;
            },
            async submitReport() {
                if (!this.reason) return;
                this.isSubmitting = true;
                try {
                    const res = await fetch('{{ route('report.store') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({
                            reported_type: this.reportedType,
                            reported_id: this.reportedId,
                            reason: this.reason,
                            details: this.details || (this.reportedText ? ('Chat: ' + this.reportedText) : null),
                        })
                    });
                    const data = await res.json();
                    if (data.success) {
                        if (window.tbsAlert) {
                            window.tbsAlert({ message: data.message });
                        } else {
                            alert(data.message);
                        }
                        this.close();
                    } else {
                        alert(data.message || 'Kon melding niet indienen.');
                    }
                } catch (e) {
                    alert('Er is een fout opgetreden bij het indienen van de melding.');
                } finally {
                    this.isSubmitting = false;
                }
            }
        };
    }

    // Mini Live Chat Widget Handler with Synthesized Audio Ping & Notification Toasts
    function tbsMiniLiveChat() {
        return {
            isOpen: false,
            activeTab: 'global',
            publicMessages: [],
            lastPublicId: 0,
            inputMessage: '',
            isSending: false,
            unreadDmsCount: 0,
            toastNotification: null,
            audioCtx: null,

            // Helpbot state
            helpInput: '',
            helpMessages: [
                {
                    from: 'bot',
                    text: 'Hoi patiënt! Ik ben de TBS Zaalwacht AI. Ik weet álles over energie, kluizen, de isoleercel en gangs. Stel gerust je vraag!',
                    links: []
                }
            ],
            faqKnowledge: {!! json_encode(\App\Http\Controllers\FaqController::getBotKnowledge()) !!},

            init() {
                this.loadPublicMessages(true);
                this.pollLoop();
            },

            playChime() {
                try {
                    if (!this.audioCtx) {
                        this.audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                    }
                    if (this.audioCtx.state === 'suspended') {
                        this.audioCtx.resume();
                    }
                    const osc = this.audioCtx.createOscillator();
                    const gain = this.audioCtx.createGain();
                    osc.type = 'sine';
                    osc.frequency.setValueAtTime(587.33, this.audioCtx.currentTime); // D5
                    osc.frequency.exponentialRampToValueAtTime(880, this.audioCtx.currentTime + 0.12); // A5
                    gain.gain.setValueAtTime(0.15, this.audioCtx.currentTime);
                    gain.gain.exponentialRampToValueAtTime(0.001, this.audioCtx.currentTime + 0.35);
                    osc.connect(gain);
                    gain.connect(this.audioCtx.destination);
                    osc.start();
                    osc.stop(this.audioCtx.currentTime + 0.35);
                } catch (e) {}
            },

            setTab(tab) {
                this.activeTab = tab;
                if (tab === 'global') {
                    this.scrollChatBottom();
                }
            },

            scrollChatBottom() {
                this.$nextTick(() => {
                    if (this.$refs.miniChatScroll) {
                        this.$refs.miniChatScroll.scrollTop = this.$refs.miniChatScroll.scrollHeight;
                    }
                });
            },

            async loadPublicMessages(isFirst = false) {
                if (document.visibilityState === 'hidden' && !isFirst) return;
                try {
                    const url = '{{ route('chat.public.messages') }}?channel=global' + (this.lastPublicId > 0 ? ('&after_id=' + this.lastPublicId) : '');
                    const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
                    const data = await res.json();
                    if (data.success && data.messages) {
                        if (this.lastPublicId === 0) {
                            this.publicMessages = data.messages;
                            this.scrollChatBottom();
                        } else if (data.messages.length > 0) {
                            // Incoming new messages
                            const newIncoming = data.messages.filter(m => !m.is_me);
                            if (newIncoming.length > 0) {
                                const latest = newIncoming[newIncoming.length - 1];
                                this.playChime();
                                if (!this.isOpen) {
                                    this.toastNotification = {
                                        name: latest.name,
                                        text: latest.message,
                                        avatar: latest.avatar,
                                    };
                                    setTimeout(() => { this.toastNotification = null; }, 5000);
                                }
                            }
                            this.publicMessages = [...this.publicMessages, ...data.messages];
                            this.scrollChatBottom();
                        }
                        if (data.last_id > this.lastPublicId) {
                            this.lastPublicId = data.last_id;
                        }
                    }
                } catch (e) {}
            },

            async sendPublicMessage() {
                const text = this.inputMessage.trim();
                if (!text || this.isSending) return;
                this.isSending = true;
                try {
                    const res = await fetch('{{ route('chat.public.send') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({ channel: 'global', message: text })
                    });
                    const data = await res.json();
                    if (data.success && data.message) {
                        this.inputMessage = '';
                        this.publicMessages.push(data.message);
                        this.lastPublicId = Math.max(this.lastPublicId, data.message.id);
                        this.scrollChatBottom();
                    } else if (data.message) {
                        alert(data.message);
                    }
                } catch (e) {
                } finally {
                    this.isSending = false;
                }
            },

            sendHelpQuery(custom = null) {
                const query = (custom || this.helpInput).trim();
                if (!query) return;
                this.helpMessages.push({ from: 'user', text: query });
                this.helpInput = '';
                this.$nextTick(() => {
                    if (this.$refs.helpBox) this.$refs.helpBox.scrollTop = this.$refs.helpBox.scrollHeight;
                });

                setTimeout(() => {
                    const qRaw = query.toLowerCase();
                    let best = this.faqKnowledge.find(i => qRaw.includes(i.title.toLowerCase()) || i.title.toLowerCase().includes(qRaw));
                    if (!best && this.faqKnowledge[0]) best = this.faqKnowledge[0];
                    if (best) {
                        this.helpMessages.push({
                            from: 'bot',
                            text: best.title + ':\n' + best.answer,
                            links: best.links || []
                        });
                    }
                    this.$nextTick(() => {
                        if (this.$refs.helpBox) this.$refs.helpBox.scrollTop = this.$refs.helpBox.scrollHeight;
                    });
                }, 300);
            },

            pollLoop() {
                setInterval(() => {
                    this.loadPublicMessages();
                }, 2500);
            }
        };
    }
</script>
