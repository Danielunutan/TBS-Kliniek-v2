<!-- UNIVERSAL REPORT MODAL -->
<div x-data="tbsGlobalReportModal()" 
     x-show="isOpen" 
     x-cloak 
     style="display: none;" 
     class="fixed inset-0 z-[99999] overflow-y-auto"
     aria-labelledby="modal-title" role="dialog" aria-modal="true">
    
    <div class="fixed inset-0 bg-black/80 transition-opacity" @click="close()"></div>

    <div class="flex min-h-screen items-center justify-center p-4 text-center sm:p-0">
        <div class="relative transform-gpu will-change-transform overflow-hidden rounded-2xl bg-white dark:bg-[#12072b] border border-slate-200 dark:border-purple-800/60 text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-md p-6">
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

            <form @submit.prevent="submitReport()" data-no-async="true" class="space-y-4 pt-4">
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

@php
    $authUser = auth()->user();
    $authCharacter = $authUser?->character;
    $authGang = $authCharacter?->gang;
    $authCanModerate = (bool) ($authUser?->isStaff() || $authUser?->canModerate());
    $myCharacterName = $authCharacter?->name ?? '';
    $myCharacterId = $authCharacter?->id ?? 0;
    $hasGang = (bool) ($authCharacter && $authCharacter->gang_id);
    $gangHasChat = (bool) ($authGang && $authGang->has_gang_chat);
    $gangId = $authCharacter?->gang_id ?? 0;
    $gangName = $authGang?->name ?? 'Bende';
    $gangTag = $authGang?->tag ?? '';
    $gangStars = $authGang?->gang_stars ?? 0;
    $characterStars = $authCharacter?->stars ?? 0;
@endphp

<!-- MINI LIVE CHAT & HELPBOT WIDGET (Docked Bottom Right) -->
<div id="tbs-floating-mini-chat" x-data="tbsMiniLiveChat({
    myId: {{ $myCharacterId }},
    myName: @js($myCharacterName),
    canModerate: @js($authCanModerate),
    hasGang: @js($hasGang),
    gangId: {{ $gangId }},
    gangName: @js($gangName),
    gangTag: @js($gangTag),
    gangHasChat: @js($gangHasChat),
    characterStars: {{ $characterStars }},
    gangStars: {{ $gangStars }},
    myAvatar: @js($authCharacter?->avatar_url ?? asset('assets/characters/avatar_male_1.jpg')),
})" x-effect="document.body.classList.toggle('tbs-chat-open', isOpen)" class="z-[9990]">

<style>
    .tbs-mini-chat-btn {
        position: fixed;
        bottom: calc(env(safe-area-inset-bottom, 0px) + 72px);
        right: 16px;
        z-index: 9998;
    }
    .tbs-mini-chat-toast {
        position: fixed;
        bottom: calc(env(safe-area-inset-bottom, 0px) + 196px);
        right: 16px;
        z-index: 9999;
    }
    .tbs-mini-chat-popup {
        position: fixed;
        bottom: calc(env(safe-area-inset-bottom, 0px) + 68px);
        right: 12px;
        left: 12px;
        width: auto;
        max-width: calc(100vw - 24px);
        height: 540px;
        max-height: calc(100vh - env(safe-area-inset-bottom, 0px) - 80px);
        z-index: 9999;
    }
    @media (min-width: 1024px) {
        .tbs-mini-chat-btn {
            bottom: 24px;
            right: 24px;
        }
        .tbs-mini-chat-toast {
            bottom: 148px;
            right: 24px;
        }
        .tbs-mini-chat-popup {
            bottom: 74px;
            right: 24px;
            left: auto;
            width: 390px;
            max-width: calc(100vw - 30px);
            max-height: calc(100vh - 90px);
        }
    }
</style>

    <!-- FLOATING NEW MESSAGE TOAST BANNER (TBS Kliniek Website Styling) -->
    <template x-if="toastNotification && !isOpen">
        <div x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             @click="openToastDestination()"
             :class="toastNotification.type === 'direct' 
                ? (toastNotification.is_bot ? 'border-amber-300 dark:border-amber-700/80 shadow-amber-900/10' : 'border-purple-300 dark:border-purple-800/80 shadow-purple-900/10') 
                : 'border-emerald-300 dark:border-emerald-800/80 shadow-emerald-900/10'"
             class="tbs-mini-chat-toast bg-white/95 dark:bg-slate-900/95 backdrop-blur-md border rounded-2xl p-3.5 shadow-xl flex items-start gap-3.5 cursor-pointer max-w-sm w-[360px] transform hover:-translate-y-0.5 transition-all relative overflow-hidden group">
            
            <!-- Top Accent Gradient Line -->
            <div class="absolute top-0 inset-x-4 h-[2px] rounded-full"
                 :class="toastNotification.type === 'direct' 
                    ? (toastNotification.is_bot ? 'bg-gradient-to-r from-amber-400 via-amber-500 to-amber-600' : 'bg-gradient-to-r from-purple-500 via-purple-600 to-indigo-600') 
                    : 'bg-gradient-to-r from-emerald-400 via-emerald-500 to-teal-500'"></div>

            <!-- Avatar / Icon Box -->
            <div class="relative w-11 h-11 rounded-xl overflow-hidden shrink-0 border mt-0.5 shadow-2xs"
                 :class="toastNotification.is_bot ? 'border-amber-200 dark:border-amber-800/80' : 'border-slate-200 dark:border-slate-700'">
                <template x-if="toastNotification.is_bot">
                    <div class="w-full h-full bg-gradient-to-br from-amber-500 to-amber-600 text-white flex items-center justify-center text-lg shadow-inner">
                        <i class="fa-solid fa-hospital-user"></i>
                    </div>
                </template>
                <template x-if="!toastNotification.is_bot">
                    <img :src="toastNotification.avatar || '/characters/avatar-default.png'" class="w-full h-full object-cover">
                </template>
                <span class="absolute top-0 right-0 w-2.5 h-2.5 rounded-full ring-2 ring-white dark:ring-slate-900"
                      :class="toastNotification.type === 'direct' ? (toastNotification.is_bot ? 'bg-amber-500 animate-ping' : 'bg-purple-500 animate-ping') : 'bg-emerald-500 animate-ping'"></span>
            </div>

            <!-- Content -->
            <div class="min-w-0 flex-1">
                <div class="flex items-center justify-between gap-1.5">
                    <div class="flex items-center gap-1.5 min-w-0">
                        <span class="text-xs font-black text-slate-900 dark:text-white truncate tracking-tight" x-text="toastNotification.name"></span>
                    </div>

                    <div class="flex items-center gap-1 shrink-0">
                        <!-- Badges -->
                        <template x-if="toastNotification.type === 'direct' && toastNotification.is_bot">
                            <span class="text-[9px] font-black uppercase tracking-wider text-amber-800 dark:text-amber-300 bg-amber-50 dark:bg-amber-950/80 px-2 py-0.5 rounded-full border border-amber-200 dark:border-amber-800 flex items-center gap-1">
                                <i class="fa-solid fa-bullhorn text-[8px] text-amber-600"></i>
                                <span>Directie</span>
                            </span>
                        </template>
                        <template x-if="toastNotification.type === 'direct' && !toastNotification.is_bot">
                            <span class="text-[9px] font-black uppercase tracking-wider text-purple-700 dark:text-purple-300 bg-purple-50 dark:bg-purple-950/80 px-2 py-0.5 rounded-full border border-purple-200 dark:border-purple-800 flex items-center gap-1">
                                <i class="fa-solid fa-lock text-[8px]"></i>
                                <span>Privé</span>
                            </span>
                        </template>
                        <template x-if="toastNotification.type !== 'direct'">
                            <span class="text-[9px] font-black uppercase tracking-wider text-emerald-700 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-950/80 px-2 py-0.5 rounded-full border border-emerald-200 dark:border-emerald-800 flex items-center gap-1">
                                <i class="fa-solid fa-comments text-[8px]"></i>
                                <span>Plein</span>
                            </span>
                        </template>

                        <!-- Dismiss Cross Button -->
                        <button type="button" 
                                @click.stop="toastNotification = null" 
                                title="Sluiten"
                                class="w-5 h-5 rounded-md hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 flex items-center justify-center text-xs transition cursor-pointer">
                            &times;
                        </button>
                    </div>
                </div>

                <!-- Clean message text without raw markdown asterisks -->
                <p class="text-xs text-slate-600 dark:text-slate-300 line-clamp-2 leading-relaxed mt-1 font-medium" 
                   x-text="(toastNotification.text || '').replace(/\*\*/g, '')"></p>

                <!-- Footer: Timestamp & Action Link -->
                <div class="text-[10px] text-slate-400 dark:text-slate-500 font-bold flex items-center justify-between mt-2 pt-1.5 border-t border-slate-100 dark:border-slate-800/80">
                    <span class="flex items-center gap-1 font-mono">
                        <i class="fa-regular fa-clock text-[9px]"></i>
                        <span x-text="toastNotification.time || 'Zojuist'"></span>
                    </span>
                    <span :class="toastNotification.is_bot ? 'text-amber-600 dark:text-amber-400 group-hover:text-amber-700' : 'text-purple-600 dark:text-purple-400 group-hover:text-purple-700'" class="font-black text-[11px] flex items-center gap-1 transition-colors">
                        <span>Klik om te openen</span>
                        <i class="fa-solid fa-arrow-right text-[10px] group-hover:translate-x-0.5 transition-transform"></i>
                    </span>
                </div>
            </div>
        </div>
    </template>

    <!-- FLOATING CHAT WINDOW POPUP -->
    <div x-show="isOpen" x-cloak style="display: none;"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-4 scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
         x-transition:leave-end="opacity-0 translate-y-4 scale-95"
         class="tbs-mini-chat-popup bg-white dark:bg-[#110726] border border-slate-200 dark:border-purple-900/60 rounded-2xl shadow-2xl flex flex-col overflow-hidden text-slate-800 dark:text-slate-100">
        
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
                    <button type="button" @click="toggleSound()" :title="isSoundMuted ? 'Geluid aanzetten' : 'Geluid dempen'" class="p-1.5 rounded-lg text-white/80 hover:text-white hover:bg-white/10 text-xs transition cursor-pointer">
                        <i :class="isSoundMuted ? 'fa-solid fa-volume-xmark text-rose-300' : 'fa-solid fa-volume-high text-white'"></i>
                    </button>
                    <a href="{{ route('chat.index') }}" title="Volledig scherm openen" class="p-1.5 rounded-lg text-white/80 hover:text-white hover:bg-white/10 text-xs transition">
                        <i class="fa-solid fa-up-right-and-down-left-from-center"></i>
                    </a>
                    <button type="button" @click="isOpen = false" class="p-1.5 rounded-lg text-white/80 hover:text-white hover:bg-white/10 text-sm transition cursor-pointer">
                        &times;
                    </button>
                </div>
            </div>

            <!-- TABS SWITCHER (4 Tabs: Plein, Bende, Privé, Hulp AI) -->
            <div class="grid grid-cols-4 gap-1 bg-black/20 p-1 rounded-xl text-[10px] font-extrabold text-center">
                <button type="button" @click="setTab('global')" 
                        :class="activeTab === 'global' ? 'bg-white text-purple-900 shadow-xs' : 'text-purple-200 hover:text-white'"
                        class="py-1 rounded-lg transition cursor-pointer flex items-center justify-center gap-1">
                    <i class="fa-solid fa-bullhorn text-[9px]"></i>
                    <span>Plein</span>
                </button>
                <button type="button" @click="setTab('gang')" 
                        :class="activeTab === 'gang' ? 'bg-white text-purple-900 shadow-xs' : 'text-purple-200 hover:text-white'"
                        class="py-1 rounded-lg transition cursor-pointer flex items-center justify-center gap-1 relative">
                    <i class="fa-solid fa-users-viewfinder text-[9px]"></i>
                    <span>Bende</span>
                    <template x-if="!gangHasChat && hasGang">
                        <i class="fa-solid fa-lock text-[8px] text-amber-300"></i>
                    </template>
                </button>
                <button type="button" @click="setTab('direct')" 
                        :class="activeTab === 'direct' ? 'bg-white text-purple-900 shadow-xs' : 'text-purple-200 hover:text-white'"
                        class="py-1 rounded-lg transition cursor-pointer flex items-center justify-center gap-1 relative">
                    <i class="fa-solid fa-envelope text-[9px]"></i>
                    <span>Privé</span>
                    <template x-if="unreadDmsCount > 0">
                        <span class="w-2 h-2 rounded-full bg-rose-500 animate-pulse"></span>
                    </template>
                </button>
                <button type="button" @click="setTab('help')" 
                        :class="activeTab === 'help' ? 'bg-white text-purple-900 shadow-xs' : 'text-purple-200 hover:text-white'"
                        class="py-1 rounded-lg transition cursor-pointer flex items-center justify-center gap-1">
                    <i class="fa-solid fa-robot text-[9px]"></i>
                    <span>Hulp AI</span>
                </button>
            </div>
        </div>

        <!-- 1. TAB: ALGEMENE CHAT (KLINIEK PLEIN) -->
        <div x-show="activeTab === 'global'" class="flex-1 flex flex-col min-h-0">
            <!-- Messages Stream -->
            <div x-ref="miniChatScroll" id="tbsMiniChatScroll" class="flex-1 min-h-0 p-3 overflow-y-auto overflow-x-hidden space-y-3 text-xs bg-slate-50/70 dark:bg-[#0d041e]/50 max-w-full">
                <template x-for="msg in publicMessages" :key="msg.id">
                    <div class="flex items-start gap-2 group min-w-0 max-w-full" :class="msg.is_me ? 'flex-row-reverse' : ''">
                        <button type="button" @click="if(!msg.is_me) openDirectChatWithPlayer(msg.character_id, msg.name, msg.avatar)"
                                class="shrink-0 cursor-pointer transition hover:scale-105"
                                :title="!msg.is_me ? 'Chat privé met ' + msg.name : ''">
                            <img :src="msg.avatar || '{{ asset('assets/characters/avatar_male_1.jpg') }}'" 
                                 class="w-7 h-7 rounded-lg object-cover border border-slate-200 dark:border-purple-800/60 shrink-0"
                                 onerror="this.src='{{ asset('assets/characters/avatar_male_1.jpg') }}'">
                        </button>

                        <div class="max-w-[84%] min-w-0 space-y-0.5" :class="msg.is_me ? 'text-right' : 'text-left'">
                            <!-- Sender Meta -->
                            <div class="flex items-center gap-1 text-[10px] font-bold flex-wrap min-w-0 max-w-full" :class="msg.is_me ? 'justify-end' : ''">
                                <!-- EIGENAAR -->
                                <template x-if="msg.is_owner">
                                    <span class="text-amber-500 dark:text-amber-400 font-black flex items-center gap-0.5 hover:underline cursor-pointer"
                                          @click="tagPlayer(msg.name)" title="Klik om te taggen in chat">
                                        <i class="fa-solid fa-crown text-[9px] text-amber-500"></i>
                                        <span x-text="msg.name"></span>
                                    </span>
                                </template>
                                <!-- ADMIN / DIRECTIE -->
                                <template x-if="msg.is_admin && !msg.is_owner">
                                    <span class="text-purple-600 dark:text-purple-300 font-black flex items-center gap-0.5 hover:underline cursor-pointer"
                                          @click="tagPlayer(msg.name)" title="Klik om te taggen in chat">
                                        <i class="fa-solid fa-shield-halved text-[9px]"></i>
                                        <span x-text="msg.name"></span>
                                    </span>
                                </template>
                                <!-- MODERATOR -->
                                <template x-if="msg.is_moderator && !msg.is_admin && !msg.is_owner">
                                    <span class="text-cyan-600 dark:text-cyan-400 font-black flex items-center gap-0.5 hover:underline cursor-pointer"
                                          @click="tagPlayer(msg.name)" title="Klik om te taggen in chat">
                                        <i class="fa-solid fa-gavel text-[9px]"></i>
                                        <span x-text="msg.name"></span>
                                    </span>
                                </template>
                                <!-- REGULAR -->
                                <template x-if="!msg.is_owner && !msg.is_admin && !msg.is_moderator">
                                    <span class="text-slate-900 dark:text-white font-extrabold hover:underline cursor-pointer"
                                          @click="tagPlayer(msg.name)" title="Klik om te taggen in chat"
                                          x-text="msg.name"></span>
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

                                <!-- GANG TAG -->
                                <template x-if="msg.gang_tag">
                                    <span class="px-1 py-0.2 rounded bg-purple-100 dark:bg-purple-900/50 text-purple-700 dark:text-purple-300 text-[8px] font-black" x-text="'[' + msg.gang_tag + ']'"></span>
                                </template>

                                <!-- TIME -->
                                <span class="text-slate-400 text-[9px]" x-text="msg.time"></span>

                                <!-- REAGEER KNOP -->
                                <button type="button" @click="setReplyTo(msg)" title="Reageer op dit bericht" class="text-slate-400 hover:text-purple-500 transition px-0.5 text-[9px] cursor-pointer">
                                    <i class="fa-solid fa-reply"></i>
                                </button>

                                <!-- MODERATOR DELETE -->
                                <template x-if="canModerate">
                                    <button type="button" @click="deletePublicMessage(msg.id)" title="Verwijder bericht (Moderator)" class="text-slate-400 hover:text-rose-500 transition px-0.5 text-[9px] cursor-pointer">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </template>

                                <!-- REPORT -->
                                <template x-if="!msg.is_me">
                                    <button type="button" @click="window.tbsOpenReportModal({ type: 'chat_message', id: msg.id, name: msg.name, text: msg.message })" class="opacity-0 group-hover:opacity-100 text-slate-400 hover:text-red-500 transition px-0.5 text-[9px] cursor-pointer" title="Rapporteer">
                                        <i class="fa-solid fa-flag"></i>
                                    </button>
                                </template>
                            </div>

                            <!-- Reply Quote Bubble (if replied) -->
                            <template x-if="msg.reply_to_name">
                                <div class="text-[10px] text-purple-300 bg-purple-950/60 px-2 py-0.5 rounded-lg border-l-2 border-purple-400 mb-1 flex items-center gap-1.5 truncate max-w-full min-w-0">
                                    <i class="fa-solid fa-reply text-[8px] text-purple-400"></i>
                                    <span class="font-extrabold text-purple-300 shrink-0" x-text="'@' + msg.reply_to_name"></span>
                                    <span class="truncate opacity-75 italic" x-text="msg.reply_to_snippet"></span>
                                </div>
                            </template>

                            <!-- Message Bubble & Heart Reaction -->
                            <div class="flex items-center gap-1.5 min-w-0 max-w-full" :class="msg.is_me ? 'flex-row-reverse' : ''">
                                <div class="inline-block px-3 py-1.5 rounded-2xl text-xs leading-relaxed shadow-2xs break-words [overflow-wrap:anywhere] [word-break:break-word] whitespace-pre-line max-w-full"
                                     :class="msg.is_me 
                                        ? 'bg-purple-600 text-white rounded-tr-xs text-left' 
                                        : (msg.is_owner 
                                            ? 'bg-amber-50 dark:bg-amber-950/30 border border-amber-300 dark:border-amber-700/60 text-slate-900 dark:text-amber-100 rounded-tl-xs text-left'
                                            : (msg.is_admin || msg.is_moderator 
                                                ? 'bg-purple-50/70 dark:bg-purple-950/40 border border-purple-200 dark:border-purple-800/60 text-slate-900 dark:text-purple-100 rounded-tl-xs text-left'
                                                : 'bg-white dark:bg-purple-950/40 text-slate-800 dark:text-purple-100 rounded-tl-xs border border-slate-200 dark:border-purple-800/40 text-left'))"
                                     x-html="formatMessage(msg.message, msg.is_me)"></div>

                                <!-- Paars Hartje Reactie Knop 💜 -->
                                <button type="button" @click="togglePublicHeart(msg)" 
                                        :title="msg.has_hearted ? 'Paars hartje intrekken' : 'Paars hartje geven (💜)'"
                                        class="shrink-0 p-1 rounded-full text-xs transition cursor-pointer flex items-center gap-0.5"
                                        :class="msg.has_hearted ? 'text-purple-400 scale-110 font-black' : 'text-slate-400/70 hover:text-purple-400'">
                                    <span>💜</span>
                                    <span x-show="msg.hearts_count > 0" x-text="msg.hearts_count" class="text-[9px] font-black font-mono"></span>
                                </button>
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

            <!-- INPUT BAR & CLEAN EMOJI DRAWER -->
            <div class="p-2.5 bg-white dark:bg-[#150a30] border-t border-slate-200 dark:border-purple-900/40 shrink-0 space-y-1.5">
                <!-- Reply Quoting Preview Banner -->
                <template x-if="replyingTo">
                    <div class="flex items-center justify-between bg-purple-50 dark:bg-purple-950/80 px-2.5 py-1.5 rounded-xl border border-purple-200 dark:border-purple-800 text-[11px] text-purple-900 dark:text-purple-200">
                        <div class="flex items-center gap-1.5 truncate">
                            <i class="fa-solid fa-reply text-purple-500"></i>
                            <span class="font-bold">Reageren op <span class="font-black text-purple-600 dark:text-purple-300" x-text="'@' + replyingTo.name"></span>:</span>
                            <span class="truncate italic opacity-80" x-text="'\"' + replyingTo.text + '\"'"></span>
                        </div>
                        <button type="button" @click="cancelReply()" class="text-slate-400 hover:text-rose-500 ml-2 font-black cursor-pointer text-xs" title="Annuleren">
                            &times;
                        </button>
                    </div>
                </template>

                <form @submit.prevent="sendPublicMessage()" data-no-async="true" class="flex items-center gap-1.5 relative">
                    <!-- Clean Emoji Picker Toggle Button -->
                    <div class="relative">
                        <button type="button" @click="showEmojiPicker = !showEmojiPicker" class="w-8 h-8 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-purple-900/50 text-slate-600 dark:text-purple-300 flex items-center justify-center text-sm transition cursor-pointer" title="Smileys & Emojis">
                            <i class="fa-regular fa-face-smile"></i>
                        </button>

                        <!-- Clean Emoji Drawer Popup -->
                        <div x-show="showEmojiPicker" @click.outside="showEmojiPicker = false" x-cloak
                             class="absolute bottom-full left-0 mb-2 w-64 bg-white dark:bg-[#160a33] border border-slate-200 dark:border-purple-800 rounded-2xl p-2.5 shadow-2xl z-50 space-y-2">
                            <div class="flex items-center justify-between pb-1 border-b border-slate-100 dark:border-purple-900/40">
                                <span class="text-[10px] font-black uppercase tracking-wider text-slate-400">Smileys</span>
                                <button type="button" @click="showEmojiPicker = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-white text-xs">&times;</button>
                            </div>
                            <div class="space-y-1.5 max-h-44 overflow-y-auto pr-1">
                                <div>
                                    <span class="text-[9px] font-bold text-slate-400 block mb-0.5">Gezichten</span>
                                    <div class="grid grid-cols-6 gap-1">
                                        <template x-for="s in ['😀','😂','🤣','😎','😏','🤫','😡','💀','🤡','😈','😱','🥳']" :key="s">
                                            <button type="button" @click="insertEmoji(s)" class="h-7 rounded hover:bg-purple-100 dark:hover:bg-purple-900/50 text-sm flex items-center justify-center transition cursor-pointer" x-text="s"></button>
                                        </template>
                                    </div>
                                </div>
                                <div>
                                    <span class="text-[9px] font-bold text-slate-400 block mb-0.5">TBS & Misdaad</span>
                                    <div class="grid grid-cols-6 gap-1">
                                        <template x-for="s in ['🔥','💰','💉','🚓','🚬','🔪','💣','💊','🩸','⛓️','🔒','🥊']" :key="s">
                                            <button type="button" @click="insertEmoji(s)" class="h-7 rounded hover:bg-purple-100 dark:hover:bg-purple-900/50 text-sm flex items-center justify-center transition cursor-pointer" x-text="s"></button>
                                        </template>
                                    </div>
                                </div>
                                <div>
                                    <span class="text-[9px] font-bold text-slate-400 block mb-0.5">Symbolen</span>
                                    <div class="grid grid-cols-6 gap-1">
                                        <template x-for="s in ['💜','❤️','👑','⭐','⚡','🎉','👍','👎','🖕','💯','🎯','👀']" :key="s">
                                            <button type="button" @click="insertEmoji(s)" class="h-7 rounded hover:bg-purple-100 dark:hover:bg-purple-900/50 text-sm flex items-center justify-center transition cursor-pointer" x-text="s"></button>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- FLOATING MENTION AUTOCOMPLETE DROPDOWN (Above Plein Input) -->
                    <div x-show="showMentionDropdown && mentionTargetField === 'global' && mentionUsers.length > 0"
                         @click.outside="showMentionDropdown = false"
                         x-cloak
                         class="absolute bottom-full left-0 right-0 mb-2 bg-white dark:bg-[#160a33] border border-slate-200 dark:border-purple-800 rounded-2xl shadow-2xl z-50 overflow-hidden divide-y divide-slate-100 dark:divide-purple-900/40">
                        <div class="px-3 py-1.5 bg-slate-50 dark:bg-purple-950/70 flex items-center justify-between text-[10px] font-black uppercase text-purple-700 dark:text-purple-300">
                            <span class="flex items-center gap-1">
                                <i class="fa-solid fa-at text-purple-500"></i>
                                <span>Speler Vermelden</span>
                            </span>
                            <span class="text-[9px] text-slate-400 font-normal lowercase">↑↓ navigeer • enter</span>
                        </div>
                        <div class="max-h-48 overflow-y-auto p-1 space-y-0.5">
                            <template x-for="(user, idx) in mentionUsers" :key="user.id">
                                <div @click="selectMention(user)"
                                     @mouseenter="mentionSelectedIndex = idx"
                                     :class="mentionSelectedIndex === idx ? 'bg-purple-100 dark:bg-purple-900/60 border-l-2 border-purple-500' : 'hover:bg-slate-50 dark:hover:bg-purple-950/30 border-l-2 border-transparent'"
                                     class="px-2.5 py-1.5 rounded-lg flex items-center justify-between gap-2 cursor-pointer transition">
                                    <div class="flex items-center gap-2 min-w-0">
                                        <div class="relative shrink-0">
                                            <img :src="user.avatar || '{{ asset('assets/characters/avatar_male_1.jpg') }}'" 
                                                 class="w-6 h-6 rounded-md object-cover border border-slate-200 dark:border-purple-800"
                                                 onerror="this.src='{{ asset('assets/characters/avatar_male_1.jpg') }}'">
                                            <span x-show="user.is_online" 
                                                  class="absolute -bottom-0.5 -right-0.5 w-1.5 h-1.5 rounded-full bg-emerald-500 ring-1 ring-white dark:ring-slate-900"></span>
                                        </div>
                                        <div class="min-w-0">
                                            <div class="flex items-center gap-1">
                                                <span class="text-xs font-black text-slate-900 dark:text-white truncate" x-text="user.name"></span>
                                                <template x-if="user.gang_tag">
                                                    <span class="text-[8px] font-black uppercase text-purple-600 dark:text-purple-400" x-text="'[' + user.gang_tag + ']'"></span>
                                                </template>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-1 shrink-0">
                                        <span class="px-1 py-0.2 rounded bg-slate-200 dark:bg-purple-950 text-slate-700 dark:text-purple-300 text-[8px] font-black" x-text="'L' + user.level"></span>
                                        <template x-if="user.staff_role">
                                            <span class="text-[8px] font-extrabold text-amber-500" x-text="user.staff_role"></span>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <div class="flex-1 relative flex items-center">
                        <input type="text" x-ref="publicInputField" x-model="inputMessage" 
                               @input="onChatInput($event, 'global')"
                               @keydown="onChatKeydown($event, 'global')"
                               placeholder="Typ @naam of een bericht... (Enter)" maxlength="250"
                               class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-purple-800/50 rounded-xl px-3 py-2 pr-12 text-xs text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-purple-500">
                        <span x-show="inputMessage.length > 150" 
                              class="absolute right-2 text-[9px] font-mono pointer-events-none"
                              :class="inputMessage.length >= 240 ? 'text-rose-500 font-black' : (inputMessage.length >= 200 ? 'text-amber-500 font-bold' : 'text-slate-400')"
                              x-text="inputMessage.length + '/250'"></span>
                    </div>
                    <button type="submit" :disabled="isSending || !inputMessage.trim()" class="w-8 h-8 rounded-xl bg-purple-600 hover:bg-purple-700 disabled:opacity-40 text-white flex items-center justify-center text-xs transition cursor-pointer shadow-xs shrink-0">
                        <i class="fa-solid fa-paper-plane"></i>
                    </button>
                </form>
            </div>
        </div>

        <!-- 1B. TAB: BENDE CHAT (BESLOTEN GANG KANAAL) -->
        <div x-show="activeTab === 'gang'" class="flex-1 flex flex-col min-h-0">
            <!-- SCENARIO A: NOT IN A GANG -->
            <template x-if="!hasGang">
                <div class="flex-1 flex flex-col items-center justify-center p-6 text-center space-y-3">
                    <div class="w-14 h-14 rounded-2xl bg-purple-100 dark:bg-purple-950/60 text-purple-600 dark:text-purple-300 flex items-center justify-center text-2xl">
                        <i class="fa-solid fa-users-slash"></i>
                    </div>
                    <div class="space-y-1">
                        <h4 class="text-sm font-black text-slate-900 dark:text-white">Geen Bende Lidmaatschap</h4>
                        <p class="text-xs text-slate-500 dark:text-purple-300/80">Sluit je aan bij een bende of richt je eigen factie op om de besloten bende chat te gebruiken.</p>
                    </div>
                    <a href="{{ route('gangs.index') }}" class="px-4 py-2 rounded-xl bg-purple-600 hover:bg-purple-700 text-white text-xs font-black shadow-md transition">
                        Bekijk Bendes &raquo;
                    </a>
                </div>
            </template>

            <!-- SCENARIO B: IN GANG, BUT CHAT NOT YET PURCHASED (500 STARS) -->
            <template x-if="hasGang && !gangHasChat">
                <div class="flex-1 flex flex-col items-center justify-center p-6 text-center space-y-4">
                    <div class="w-16 h-16 rounded-2xl bg-amber-100 dark:bg-amber-950/60 text-amber-500 border border-amber-300 dark:border-amber-700/60 flex items-center justify-center text-3xl shadow-lg">
                        <i class="fa-solid fa-walkie-talkie"></i>
                    </div>
                    <div class="space-y-1.5">
                        <span class="px-2.5 py-0.5 rounded-full bg-amber-100 dark:bg-amber-950 text-amber-700 dark:text-amber-300 text-[10px] font-black uppercase tracking-wider border border-amber-300 dark:border-amber-800">
                            🔒 Exclusief Kanaal
                        </span>
                        <h4 class="text-base font-black text-slate-900 dark:text-white" x-text="gangName + ' Portofoon'"></h4>
                        <p class="text-xs text-slate-600 dark:text-purple-200 max-w-xs mx-auto leading-relaxed">
                            Jullie bende heeft de eigen besloten Bende Chat nog niet geactiveerd. Deze chat is exclusief voor leden van <span class="font-bold" x-text="gangName"></span>.
                        </p>
                    </div>

                    <div class="p-3 bg-purple-50 dark:bg-purple-950/40 rounded-xl border border-purple-200 dark:border-purple-800/50 text-xs text-purple-900 dark:text-purple-200 w-full max-w-xs space-y-1">
                        <div class="flex items-center justify-between font-bold">
                            <span>Eenmalige Kosten:</span>
                            <span class="text-amber-500 font-black">500 Sterren ⭐</span>
                        </div>
                        <div class="flex items-center justify-between text-[11px] text-slate-500 dark:text-purple-300/80">
                            <span>Jouw saldo:</span>
                            <span x-text="characterStars + ' ⭐'"></span>
                        </div>
                    </div>

                    <form action="{{ route('gangs.shop-buy') }}" method="POST" class="w-full max-w-xs">
                        @csrf
                        <input type="hidden" name="item_type" value="gang_chat">
                        <input type="hidden" name="quantity" value="1">
                        <button type="submit" :disabled="characterStars < 500 && gangStars < 500" class="w-full py-2.5 rounded-xl bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-slate-950 font-black text-xs shadow-md transition disabled:opacity-50 cursor-pointer flex items-center justify-center gap-1.5">
                            <i class="fa-solid fa-cart-shopping"></i>
                            <span>Koop Bende Chat (500 ⭐)</span>
                        </button>
                    </form>
                </div>
            </template>

            <!-- SCENARIO C: IN GANG & CHAT UNLOCKED -->
            <template x-if="hasGang && gangHasChat">
                <div class="flex-1 flex flex-col min-h-0">
                    <!-- Gang Header Sub-bar -->
                    <div class="px-3 py-1.5 bg-purple-100/70 dark:bg-purple-950/80 border-b border-purple-200 dark:border-purple-900/40 flex items-center justify-between text-[11px] font-bold shrink-0">
                        <div class="flex items-center gap-1.5 text-purple-900 dark:text-purple-200">
                            <i class="fa-solid fa-walkie-talkie text-amber-500"></i>
                            <span x-text="gangName + ' [Portofoon]'"></span>
                        </div>
                        <span class="text-[9px] px-2 py-0.2 rounded-full bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 font-extrabold border border-emerald-500/30">
                            Besloten
                        </span>
                    </div>

                    <!-- Gang Messages Stream -->
                    <div x-ref="miniGangScroll" id="tbsMiniGangScroll" class="flex-1 min-h-0 p-3 overflow-y-auto overflow-x-hidden space-y-3 text-xs bg-slate-50/70 dark:bg-[#0d041e]/50 max-w-full">
                        <template x-for="msg in gangMessages" :key="msg.id">
                            <div class="flex items-start gap-2 group min-w-0 max-w-full" :class="msg.is_me ? 'flex-row-reverse' : ''">
                                <button type="button" @click="if(!msg.is_me) openDirectChatWithPlayer(msg.character_id, msg.name, msg.avatar)"
                                        class="shrink-0 cursor-pointer transition hover:scale-105"
                                        :title="!msg.is_me ? 'Chat privé met ' + msg.name : ''">
                                    <img :src="msg.avatar || '{{ asset('assets/characters/avatar_male_1.jpg') }}'" 
                                         class="w-7 h-7 rounded-lg object-cover border border-slate-200 dark:border-purple-800/60 shrink-0"
                                         onerror="this.src='{{ asset('assets/characters/avatar_male_1.jpg') }}'">
                                </button>

                                <div class="max-w-[84%] min-w-0 space-y-0.5" :class="msg.is_me ? 'text-right' : 'text-left'">
                                    <!-- Sender Meta -->
                                    <div class="flex items-center gap-1 text-[10px] font-bold flex-wrap min-w-0 max-w-full" :class="msg.is_me ? 'justify-end' : ''">
                                        <span class="text-slate-900 dark:text-white font-extrabold hover:underline cursor-pointer"
                                              @click="tagPlayer(msg.name)" title="Klik om te taggen"
                                              x-text="msg.name"></span>

                                        <span class="px-1 py-0.2 rounded bg-slate-200 dark:bg-purple-950 text-slate-700 dark:text-purple-300 text-[8px] font-black">
                                            L<span x-text="msg.level"></span>
                                        </span>

                                        <span class="text-slate-400 text-[9px]" x-text="msg.time"></span>

                                        <!-- Reageer -->
                                        <button type="button" @click="setReplyTo(msg)" title="Reageer" class="text-slate-400 hover:text-purple-500 transition px-0.5 text-[9px] cursor-pointer">
                                            <i class="fa-solid fa-reply"></i>
                                        </button>

                                        <!-- Moderator/Leader Delete -->
                                        <template x-if="canModerate">
                                            <button type="button" @click="deletePublicMessage(msg.id)" title="Verwijder bericht" class="text-slate-400 hover:text-rose-500 transition px-0.5 text-[9px] cursor-pointer">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>
                                        </template>
                                    </div>

                                    <!-- Reply Quote -->
                                    <template x-if="msg.reply_to_name">
                                        <div class="text-[10px] text-purple-300 bg-purple-950/60 px-2 py-0.5 rounded-lg border-l-2 border-purple-400 mb-1 flex items-center gap-1.5 truncate max-w-full min-w-0">
                                            <i class="fa-solid fa-reply text-[8px] text-purple-400"></i>
                                            <span class="font-extrabold text-purple-300 shrink-0" x-text="'@' + msg.reply_to_name"></span>
                                            <span class="truncate opacity-75 italic" x-text="msg.reply_to_snippet"></span>
                                        </div>
                                    </template>

                                    <!-- Bubble & Heart -->
                                    <div class="flex items-center gap-1.5 min-w-0 max-w-full" :class="msg.is_me ? 'flex-row-reverse' : ''">
                                        <div class="inline-block px-3 py-1.5 rounded-2xl text-xs leading-relaxed shadow-2xs break-words [overflow-wrap:anywhere] [word-break:break-word] whitespace-pre-line max-w-full"
                                             :class="msg.is_me ? 'bg-purple-600 text-white rounded-tr-xs text-left' : 'bg-white dark:bg-purple-950/40 text-slate-800 dark:text-purple-100 rounded-tl-xs border border-slate-200 dark:border-purple-800/40 text-left'"
                                             x-html="formatMessage(msg.message, msg.is_me)"></div>

                                        <!-- Paars Hartje Reactie Knop 💜 -->
                                        <button type="button" @click="togglePublicHeart(msg)" 
                                                :title="msg.has_hearted ? 'Paars hartje intrekken' : 'Paars hartje geven (💜)'"
                                                class="shrink-0 p-1 rounded-full text-xs transition cursor-pointer flex items-center gap-0.5"
                                                :class="msg.has_hearted ? 'text-purple-400 scale-110 font-black' : 'text-slate-400/70 hover:text-purple-400'">
                                            <span>💜</span>
                                            <span x-show="msg.hearts_count > 0" x-text="msg.hearts_count" class="text-[9px] font-black font-mono"></span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <template x-if="gangMessages.length === 0">
                            <div class="py-12 text-center text-slate-400 text-xs">
                                Nog geen berichten in de bende chat. Bespreek strategieën en overvallen met je maten!
                            </div>
                        </template>
                    </div>

                    <!-- Gang Chat Input Bar -->
                    <div class="p-2.5 bg-white dark:bg-[#150a30] border-t border-slate-200 dark:border-purple-900/40 shrink-0 space-y-1.5">
                        <template x-if="replyingTo">
                            <div class="flex items-center justify-between bg-purple-50 dark:bg-purple-950/80 px-2.5 py-1.5 rounded-xl border border-purple-200 dark:border-purple-800 text-[11px] text-purple-900 dark:text-purple-200">
                                <div class="flex items-center gap-1.5 truncate">
                                    <i class="fa-solid fa-reply text-purple-500"></i>
                                    <span class="font-bold">Reageren op <span class="font-black text-purple-600 dark:text-purple-300" x-text="'@' + replyingTo.name"></span>:</span>
                                    <span class="truncate italic opacity-80" x-text="'\"' + replyingTo.text + '\"'"></span>
                                </div>
                                <button type="button" @click="cancelReply()" class="text-slate-400 hover:text-rose-500 ml-2 font-black cursor-pointer text-xs">&times;</button>
                            </div>
                        </template>

                        <form @submit.prevent="sendGangMessage()" data-no-async="true" class="flex items-center gap-1.5 relative">
                            <!-- Clean Emoji Picker Toggle -->
                            <div class="relative">
                                <button type="button" @click="showGangEmojiPicker = !showGangEmojiPicker" class="w-8 h-8 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-purple-900/50 text-slate-600 dark:text-purple-300 flex items-center justify-center text-sm transition cursor-pointer" title="Smileys">
                                    <i class="fa-regular fa-face-smile"></i>
                                </button>

                                <div x-show="showGangEmojiPicker" @click.outside="showGangEmojiPicker = false" x-cloak
                                     class="absolute bottom-full left-0 mb-2 w-64 bg-white dark:bg-[#160a33] border border-slate-200 dark:border-purple-800 rounded-2xl p-2.5 shadow-2xl z-50 space-y-2">
                                    <div class="flex items-center justify-between pb-1 border-b border-slate-100 dark:border-purple-900/40">
                                        <span class="text-[10px] font-black uppercase tracking-wider text-slate-400">Smileys</span>
                                        <button type="button" @click="showGangEmojiPicker = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-white text-xs">&times;</button>
                                    </div>
                                    <div class="space-y-1.5 max-h-44 overflow-y-auto pr-1">
                                        <div>
                                            <span class="text-[9px] font-bold text-slate-400 block mb-0.5">Gezichten</span>
                                            <div class="grid grid-cols-6 gap-1">
                                                <template x-for="s in ['😀','😂','🤣','😎','😏','🤫','😡','💀','🤡','😈','😱','🥳']" :key="s">
                                                    <button type="button" @click="insertGangEmoji(s)" class="h-7 rounded hover:bg-purple-100 dark:hover:bg-purple-900/50 text-sm flex items-center justify-center transition cursor-pointer" x-text="s"></button>
                                                </template>
                                            </div>
                                        </div>
                                        <div>
                                            <span class="text-[9px] font-bold text-slate-400 block mb-0.5">TBS & Misdaad</span>
                                            <div class="grid grid-cols-6 gap-1">
                                                <template x-for="s in ['🔥','💰','💉','🚓','🚬','🔪','💣','💊','🩸','⛓️','🔒','🥊']" :key="s">
                                                    <button type="button" @click="insertGangEmoji(s)" class="h-7 rounded hover:bg-purple-100 dark:hover:bg-purple-900/50 text-sm flex items-center justify-center transition cursor-pointer" x-text="s"></button>
                                                </template>
                                            </div>
                                        </div>
                                        <div>
                                            <span class="text-[9px] font-bold text-slate-400 block mb-0.5">Symbolen</span>
                                            <div class="grid grid-cols-6 gap-1">
                                                <template x-for="s in ['💜','❤️','👑','⭐','⚡','🎉','👍','👎','🖕','💯','🎯','👀']" :key="s">
                                                    <button type="button" @click="insertGangEmoji(s)" class="h-7 rounded hover:bg-purple-100 dark:hover:bg-purple-900/50 text-sm flex items-center justify-center transition cursor-pointer" x-text="s"></button>
                                                </template>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                    <!-- FLOATING MENTION AUTOCOMPLETE DROPDOWN (Above Gang Input) -->
                    <div x-show="showMentionDropdown && mentionTargetField === 'gang' && mentionUsers.length > 0"
                         @click.outside="showMentionDropdown = false"
                         x-cloak
                         class="absolute bottom-full left-0 right-0 mb-2 bg-white dark:bg-[#160a33] border border-slate-200 dark:border-purple-800 rounded-2xl shadow-2xl z-50 overflow-hidden divide-y divide-slate-100 dark:divide-purple-900/40">
                        <div class="px-3 py-1.5 bg-slate-50 dark:bg-purple-950/70 flex items-center justify-between text-[10px] font-black uppercase text-purple-700 dark:text-purple-300">
                            <span class="flex items-center gap-1">
                                <i class="fa-solid fa-at text-purple-500"></i>
                                <span>Bende Lid Vermelden</span>
                            </span>
                            <span class="text-[9px] text-slate-400 font-normal lowercase">↑↓ navigeer • enter</span>
                        </div>
                        <div class="max-h-48 overflow-y-auto p-1 space-y-0.5">
                            <template x-for="(user, idx) in mentionUsers" :key="user.id">
                                <div @click="selectMention(user)"
                                     @mouseenter="mentionSelectedIndex = idx"
                                     :class="mentionSelectedIndex === idx ? 'bg-purple-100 dark:bg-purple-900/60 border-l-2 border-purple-500' : 'hover:bg-slate-50 dark:hover:bg-purple-950/30 border-l-2 border-transparent'"
                                     class="px-2.5 py-1.5 rounded-lg flex items-center justify-between gap-2 cursor-pointer transition">
                                    <div class="flex items-center gap-2 min-w-0">
                                        <div class="relative shrink-0">
                                            <img :src="user.avatar || '{{ asset('assets/characters/avatar_male_1.jpg') }}'" 
                                                 class="w-6 h-6 rounded-md object-cover border border-slate-200 dark:border-purple-800"
                                                 onerror="this.src='{{ asset('assets/characters/avatar_male_1.jpg') }}'">
                                            <span x-show="user.is_online" 
                                                  class="absolute -bottom-0.5 -right-0.5 w-1.5 h-1.5 rounded-full bg-emerald-500 ring-1 ring-white dark:ring-slate-900"></span>
                                        </div>
                                        <div class="min-w-0">
                                            <div class="flex items-center gap-1">
                                                <span class="text-xs font-black text-slate-900 dark:text-white truncate" x-text="user.name"></span>
                                                <template x-if="user.gang_tag">
                                                    <span class="text-[8px] font-black uppercase text-purple-600 dark:text-purple-400" x-text="'[' + user.gang_tag + ']'"></span>
                                                </template>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-1 shrink-0">
                                        <span class="px-1 py-0.2 rounded bg-slate-200 dark:bg-purple-950 text-slate-700 dark:text-purple-300 text-[8px] font-black" x-text="'L' + user.level"></span>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <div class="flex-1 relative flex items-center">
                        <input type="text" x-ref="gangInputField" x-model="gangInputMessage" 
                               @input="onChatInput($event, 'gang')"
                               @keydown="onChatKeydown($event, 'gang')"
                               placeholder="Typ @naam of een bendebericht... (Enter)" maxlength="250"
                               class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-purple-800/50 rounded-xl px-3 py-2 pr-12 text-xs text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-purple-500">
                        <span x-show="gangInputMessage.length > 150" 
                              class="absolute right-2 text-[9px] font-mono pointer-events-none"
                              :class="gangInputMessage.length >= 240 ? 'text-rose-500 font-black' : (gangInputMessage.length >= 200 ? 'text-amber-500 font-bold' : 'text-slate-400')"
                              x-text="gangInputMessage.length + '/250'"></span>
                    </div>
                            <button type="submit" :disabled="isSendingGang || !gangInputMessage.trim()" class="w-8 h-8 rounded-xl bg-purple-600 hover:bg-purple-700 disabled:opacity-40 text-white flex items-center justify-center text-xs transition cursor-pointer shadow-xs shrink-0">
                                <i class="fa-solid fa-paper-plane"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </template>
        </div>

        <!-- 2. TAB: PRIVÉ CHAT & CONTACTEN -->
        <div x-show="activeTab === 'direct'" class="flex-1 flex flex-col min-h-0 bg-slate-50/70 dark:bg-[#0d041e]/50">
            <!-- VIEW A: OVERVIEW / CONVERSATIONS LIST -->
            <template x-if="!activeDirectPartner">
                <div class="flex-1 flex flex-col min-h-0 p-3 overflow-y-auto space-y-2.5">
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
                                    <button type="button" @click="openDirectChatWithPlayer(p.id, p.name, p.avatar); searchResults = []; q = '';" 
                                            class="w-full p-2 rounded-lg hover:bg-purple-50 dark:hover:bg-purple-900/40 flex items-center justify-between transition cursor-pointer text-left">
                                        <div class="flex items-center gap-2 truncate">
                                            <img :src="p.avatar || '{{ asset('assets/characters/avatar_male_1.jpg') }}'" class="w-6 h-6 rounded-md object-cover" onerror="this.src='{{ asset('assets/characters/avatar_male_1.jpg') }}'">
                                            <span class="text-xs font-bold text-slate-900 dark:text-white truncate" x-text="p.name"></span>
                                        </div>
                                        <span class="px-2 py-0.5 rounded bg-purple-600 text-white text-[10px] font-black shrink-0">Chat &raquo;</span>
                                    </button>
                                </template>
                            </div>
                        </template>
                    </div>

                    <!-- CONVERSATIONS LIST -->
                    <div class="space-y-1.5 flex-1">
                        <template x-for="conv in directConversations" :key="conv.partner_id">
                            <button type="button" @click="openDirectChat(conv)"
                                    class="w-full p-2.5 rounded-xl border border-slate-200/80 dark:border-purple-900/40 bg-white dark:bg-purple-950/30 hover:border-purple-400 dark:hover:border-purple-600/60 text-left transition flex items-center justify-between gap-2.5 cursor-pointer shadow-2xs group">
                                <div class="flex items-center gap-2.5 truncate">
                                    <div class="relative shrink-0">
                                        <template x-if="conv.is_bot">
                                            <div class="w-8 h-8 rounded-lg bg-amber-500/20 text-amber-500 border border-amber-300 dark:border-amber-700/60 flex items-center justify-center text-sm font-black">
                                                <i class="fa-solid fa-building-shield"></i>
                                            </div>
                                        </template>
                                        <template x-if="!conv.is_bot">
                                            <img :src="conv.avatar || '{{ asset('assets/characters/avatar_male_1.jpg') }}'" class="w-8 h-8 rounded-lg object-cover border border-slate-200 dark:border-purple-800/60" onerror="this.src='{{ asset('assets/characters/avatar_male_1.jpg') }}'">
                                        </template>
                                        <template x-if="conv.is_online && !conv.is_bot">
                                            <span class="absolute -bottom-0.5 -right-0.5 w-2 h-2 rounded-full bg-emerald-500 border border-white dark:border-slate-900"></span>
                                        </template>
                                    </div>
                                    <div class="min-w-0 truncate">
                                        <div class="flex items-center gap-1.5">
                                            <span class="text-xs font-black truncate" :class="conv.is_bot ? 'text-amber-500' : 'text-slate-900 dark:text-white'" x-text="conv.name"></span>
                                            <span class="text-[9px] text-slate-400 shrink-0 font-medium" x-text="conv.last_time"></span>
                                        </div>
                                        <p class="text-[11px] text-slate-500 dark:text-slate-400 truncate" x-text="conv.last_message"></p>
                                    </div>
                                </div>
                                <div class="shrink-0 flex items-center gap-1">
                                    <template x-if="conv.unread_count > 0">
                                        <span class="px-1.5 py-0.2 rounded-full bg-rose-500 text-white font-mono text-[9px] font-black" x-text="conv.unread_count"></span>
                                    </template>
                                    <i class="fa-solid fa-chevron-right text-[10px] text-slate-400 group-hover:text-purple-500 transition"></i>
                                </div>
                            </button>
                        </template>

                        <template x-if="!isLoadingConversations && directConversations.length === 0">
                            <div class="py-8 text-center text-xs text-slate-400 space-y-1">
                                <i class="fa-solid fa-comment-slash text-xl text-slate-300 dark:text-slate-600"></i>
                                <p>Nog geen privégesprekken.</p>
                                <p class="text-[10px]">Zoek een patiënt hierboven om te starten.</p>
                            </div>
                        </template>
                    </div>

                    <div class="pt-1 text-center">
                        <a href="{{ route('chat.index', ['tab' => 'direct']) }}" class="text-[11px] font-black text-purple-600 dark:text-purple-400 hover:underline inline-flex items-center gap-1">
                            <span>Open Grote Messenger</span>
                            <i class="fa-solid fa-arrow-up-right-from-square text-[9px]"></i>
                        </a>
                    </div>
                </div>
            </template>

            <!-- VIEW B: ACTIVE DIRECT CONVERSATION THREAD -->
            <template x-if="activeDirectPartner">
                <div class="flex-1 flex flex-col min-h-0">
                    <!-- Partner Sub-Header -->
                    <div class="p-2 bg-white dark:bg-[#150a30] border-b border-slate-200 dark:border-purple-900/40 flex items-center justify-between shrink-0 shadow-2xs">
                        <div class="flex items-center gap-2 min-w-0">
                            <button type="button" @click="closeDirectChat()" class="p-1 text-slate-400 hover:text-purple-600 transition cursor-pointer text-xs" title="Terug naar overzicht">
                                <i class="fa-solid fa-arrow-left"></i>
                            </button>
                            <img :src="activeDirectPartner.avatar || '{{ asset('assets/characters/avatar_male_1.jpg') }}'" class="w-6 h-6 rounded-md object-cover border border-slate-200 dark:border-purple-800" onerror="this.src='{{ asset('assets/characters/avatar_male_1.jpg') }}'">
                            <span class="text-xs font-black text-slate-900 dark:text-white truncate" x-text="activeDirectPartner.name"></span>
                        </div>
                        <template x-if="!activeDirectPartner.is_bot && (activeDirectPartner.partner_id || activeDirectPartner.id) > 0">
                            <a :href="'/speler/' + encodeURIComponent(activeDirectPartner.name)" class="px-2 py-0.5 rounded bg-slate-100 dark:bg-purple-950/60 text-slate-600 dark:text-purple-300 hover:text-purple-500 text-[10px] font-bold transition">
                                Dossier &raquo;
                            </a>
                        </template>
                    </div>

                    <!-- Direct Messages Stream -->
                    <div x-ref="miniDirectScroll" id="tbsMiniDirectScroll" class="flex-1 min-h-0 p-3 overflow-y-auto overflow-x-hidden space-y-2.5 text-xs max-w-full">
                        <template x-for="dm in activeDirectMessages" :key="dm.id">
                            <div class="space-y-1">
                                <!-- Kluiscode Card -->
                                <template x-if="dm.type === 'kluiscode'">
                                    <div class="p-2.5 rounded-xl bg-amber-500/10 border border-amber-400 dark:border-amber-600/60 space-y-1.5 text-slate-900 dark:text-amber-100 min-w-0 max-w-full">
                                        <div class="flex items-center justify-between text-[10px] font-black uppercase text-amber-600 dark:text-amber-400">
                                            <span>🔐 Exclusieve Kluiscode</span>
                                            <span x-text="dm.time"></span>
                                        </div>
                                        <div class="p-1.5 bg-white dark:bg-slate-900 rounded font-mono font-black text-xs text-amber-600 text-center select-all" x-text="dm.metadata?.code"></div>
                                        <a :href="'/kluis-kraak?code=' + encodeURIComponent(dm.metadata?.code)" class="block text-center py-1 rounded bg-purple-600 text-white font-black text-[10px]">
                                            Direct Inwisselen &raquo;
                                        </a>
                                    </div>
                                </template>

                                <!-- Level Up Card -->
                                <template x-if="dm.type === 'level_up'">
                                    <div class="p-2.5 rounded-xl bg-purple-600/10 border border-purple-400 dark:border-purple-600/60 space-y-1 text-slate-900 dark:text-purple-100 min-w-0 max-w-full">
                                        <div class="flex items-center gap-1.5 text-[10px] font-black text-purple-600 dark:text-purple-400 uppercase">
                                            <i class="fa-solid fa-award"></i>
                                            <span x-text="'Level ' + dm.metadata?.level + ' Bereikt!'"></span>
                                        </div>
                                        <p class="text-[11px] break-words [overflow-wrap:anywhere]" x-text="dm.message"></p>
                                    </div>
                                </template>

                                <!-- Standard & System Notification Bubbles -->
                                <template x-if="dm.type !== 'kluiscode' && dm.type !== 'level_up'">
                                    <div class="flex items-start gap-2 group min-w-0 max-w-full" :class="dm.is_me ? 'flex-row-reverse' : ''">
                                        <!-- Avatar Image -->
                                        <template x-if="!dm.is_me">
                                            <img :src="activeDirectPartner?.avatar || '{{ asset('assets/characters/avatar_male_1.jpg') }}'" 
                                                 class="w-7 h-7 rounded-lg object-cover border border-slate-200 dark:border-purple-800/60 shrink-0"
                                                 onerror="this.src='{{ asset('assets/characters/avatar_male_1.jpg') }}'">
                                        </template>
                                        <template x-if="dm.is_me">
                                            <img :src="myAvatar || '{{ asset('assets/characters/avatar_male_1.jpg') }}'" 
                                                 class="w-7 h-7 rounded-lg object-cover border border-slate-200 dark:border-purple-800/60 shrink-0"
                                                 onerror="this.src='{{ asset('assets/characters/avatar_male_1.jpg') }}'">
                                        </template>

                                        <div class="max-w-[82%] min-w-0 space-y-0.5" :class="dm.is_me ? 'text-right' : 'text-left'">
                                            <!-- Sender Meta: Name, Level, Time -->
                                            <div class="flex items-center gap-1.5 text-[10px] font-bold flex-wrap mb-0.5 min-w-0 max-w-full" :class="dm.is_me ? 'justify-end' : ''">
                                                <span class="text-slate-900 dark:text-white font-extrabold" x-text="dm.is_me ? myName : (activeDirectPartner?.name || 'Inmate')"></span>
                                                <template x-if="!dm.is_me && activeDirectPartner && activeDirectPartner.level">
                                                    <span class="px-1 py-0.2 rounded bg-slate-200 dark:bg-purple-950 text-slate-700 dark:text-purple-300 text-[8px] font-black">
                                                        L<span x-text="activeDirectPartner.level"></span>
                                                    </span>
                                                </template>
                                                <span class="text-slate-400 text-[9px]" x-text="dm.time"></span>
                                            </div>

                                            <!-- Reply Quote if any -->
                                            <template x-if="dm.reply_to_name">
                                                <div class="text-[10px] text-purple-300 bg-purple-950/60 px-2 py-0.5 rounded-lg border-l-2 border-purple-400 mb-1 flex items-center gap-1.5 truncate max-w-full min-w-0">
                                                    <i class="fa-solid fa-reply text-[8px] text-purple-400"></i>
                                                    <span class="font-extrabold text-purple-300 shrink-0" x-text="'@' + dm.reply_to_name"></span>
                                                    <span class="truncate opacity-75 italic" x-text="dm.reply_to_snippet"></span>
                                                </div>
                                            </template>

                                            <!-- Bubble & Reactions -->
                                            <div class="flex items-center gap-1.5 min-w-0 max-w-full" :class="dm.is_me ? 'flex-row-reverse' : ''">
                                                <div class="inline-block px-3 py-1.5 rounded-2xl text-xs leading-relaxed shadow-2xs break-words [overflow-wrap:anywhere] [word-break:break-word] whitespace-pre-line max-w-full"
                                                     :class="dm.is_me ? 'bg-purple-600 text-white rounded-tr-xs text-left' : 'bg-white dark:bg-purple-950/40 text-slate-800 dark:text-purple-100 rounded-tl-xs border border-slate-200 dark:border-purple-800/40 text-left'"
                                                     x-html="formatMessage(dm.message, dm.is_me)"></div>

                                                <!-- Action Buttons: Heart, Reageer, Delete -->
                                                <div class="flex items-center gap-0.5 opacity-0 group-hover:opacity-100 transition shrink-0">
                                                    <!-- Paars Hartje Reactie 💜 -->
                                                    <button type="button" @click="toggleDirectHeart(dm)" 
                                                            :title="dm.has_hearted ? 'Paars hartje intrekken' : 'Paars hartje geven (💜)'"
                                                            class="p-1 rounded-full text-xs transition cursor-pointer flex items-center gap-0.5"
                                                            :class="dm.has_hearted ? 'text-purple-400 font-black' : 'text-slate-400/70 hover:text-purple-400'">
                                                        <span>💜</span>
                                                        <span x-show="dm.hearts_count > 0" x-text="dm.hearts_count" class="text-[9px] font-black font-mono"></span>
                                                    </button>

                                                    <!-- Reageer -->
                                                    <button type="button" @click="setReplyTo(dm, 'direct')" title="Reageer" class="text-slate-400 hover:text-purple-500 transition px-0.5 text-[9px] cursor-pointer">
                                                        <i class="fa-solid fa-reply"></i>
                                                    </button>

                                                    <!-- Verwijder -->
                                                    <template x-if="dm.is_me || canModerate">
                                                        <button type="button" @click="deleteDirectMessage(dm.id)" title="Verwijder bericht" class="text-slate-400 hover:text-rose-500 transition px-0.5 text-[9px] cursor-pointer">
                                                            <i class="fa-solid fa-trash-can"></i>
                                                        </button>
                                                    </template>
                                                </div>
                                            </div>

                                            <!-- Read Receipt (Checkmarks) for own messages -->
                                            <template x-if="dm.is_me">
                                                <div class="flex items-center justify-end gap-1 text-[9px] text-slate-400 font-mono">
                                                    <span class="inline-flex items-center ml-0.5">
                                                        <template x-if="dm.is_read">
                                                            <span class="text-purple-400 font-bold flex items-center" :title="'Gelezen om ' + (dm.read_at || dm.time)">
                                                                <i class="fa-solid fa-check-double text-[9px]"></i>
                                                            </span>
                                                        </template>
                                                        <template x-if="!dm.is_read">
                                                            <span class="text-slate-400" title="Verzonden">
                                                                <i class="fa-solid fa-check text-[9px]"></i>
                                                            </span>
                                                        </template>
                                                    </span>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </template>

                        <template x-if="activeDirectMessages.length === 0">
                            <div class="py-8 text-center text-slate-400 text-xs">
                                Nog geen berichten in dit gesprek. Stuur een bericht om te starten!
                            </div>
                        </template>
                    </div>

                    <!-- Direct Message Input Bar -->
                    <template x-if="activeDirectPartner.is_bot">
                        <div class="p-2 bg-slate-100 dark:bg-purple-950/30 text-center text-[10px] font-bold text-slate-400 shrink-0 border-t border-slate-200 dark:border-purple-900/40">
                            <i class="fa-solid fa-lock text-slate-400 mr-1"></i>
                            Officiële directieberichten (geen antwoord mogelijk).
                        </div>
                    </template>

                    <template x-if="!activeDirectPartner.is_bot">
                        <div class="p-2.5 bg-white dark:bg-[#150a30] border-t border-slate-200 dark:border-purple-900/40 shrink-0 space-y-1.5">
                            <!-- Direct Reply Preview -->
                            <template x-if="directReplyingTo">
                                <div class="flex items-center justify-between bg-purple-50 dark:bg-purple-950/80 px-2.5 py-1.5 rounded-xl border border-purple-200 dark:border-purple-800 text-[11px] text-purple-900 dark:text-purple-200">
                                    <div class="flex items-center gap-1.5 truncate">
                                        <i class="fa-solid fa-reply text-purple-500"></i>
                                        <span class="font-bold">Reageren op <span class="font-black text-purple-600 dark:text-purple-300" x-text="'@' + directReplyingTo.name"></span>:</span>
                                        <span class="truncate italic opacity-80" x-text="'\"' + directReplyingTo.text + '\"'"></span>
                                    </div>
                                    <button type="button" @click="cancelDirectReply()" class="text-slate-400 hover:text-rose-500 ml-2 font-black cursor-pointer text-xs">&times;</button>
                                </div>
                            </template>

                            <form @submit.prevent="sendDirectMessage()" data-no-async="true" class="flex items-center gap-1.5 relative">
                                <!-- Clean Emoji Toggle -->
                                <div class="relative">
                                    <button type="button" @click="showDirectEmojiPicker = !showDirectEmojiPicker" class="w-8 h-8 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-purple-900/50 text-slate-600 dark:text-purple-300 flex items-center justify-center text-sm transition cursor-pointer" title="Smileys">
                                        <i class="fa-regular fa-face-smile"></i>
                                    </button>

                                    <div x-show="showDirectEmojiPicker" @click.outside="showDirectEmojiPicker = false" x-cloak
                                         class="absolute bottom-full left-0 mb-2 w-64 bg-white dark:bg-[#160a33] border border-slate-200 dark:border-purple-800 rounded-2xl p-2.5 shadow-2xl z-50 space-y-2">
                                        <div class="flex items-center justify-between pb-1 border-b border-slate-100 dark:border-purple-900/40">
                                            <span class="text-[10px] font-black uppercase tracking-wider text-slate-400">Smileys</span>
                                            <button type="button" @click="showDirectEmojiPicker = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-white text-xs">&times;</button>
                                        </div>
                                        <div class="space-y-1.5 max-h-44 overflow-y-auto pr-1">
                                            <div>
                                                <span class="text-[9px] font-bold text-slate-400 block mb-0.5">Gezichten</span>
                                                <div class="grid grid-cols-6 gap-1">
                                                    <template x-for="s in ['😀','😂','🤣','😎','😏','🤫','😡','💀','🤡','😈','😱','🥳']" :key="s">
                                                        <button type="button" @click="insertDirectEmoji(s)" class="h-7 rounded hover:bg-purple-100 dark:hover:bg-purple-900/50 text-sm flex items-center justify-center transition cursor-pointer" x-text="s"></button>
                                                    </template>
                                                </div>
                                            </div>
                                            <div>
                                                <span class="text-[9px] font-bold text-slate-400 block mb-0.5">TBS & Misdaad</span>
                                                <div class="grid grid-cols-6 gap-1">
                                                    <template x-for="s in ['🔥','💰','💉','🚓','🚬','🔪','💣','💊','🩸','⛓️','🔒','🥊']" :key="s">
                                                        <button type="button" @click="insertDirectEmoji(s)" class="h-7 rounded hover:bg-purple-100 dark:hover:bg-purple-900/50 text-sm flex items-center justify-center transition cursor-pointer" x-text="s"></button>
                                                    </template>
                                                </div>
                                            </div>
                                            <div>
                                                <span class="text-[9px] font-bold text-slate-400 block mb-0.5">Symbolen</span>
                                                <div class="grid grid-cols-6 gap-1">
                                                    <template x-for="s in ['💜','❤️','👑','⭐','⚡','🎉','👍','👎','🖕','💯','🎯','👀']" :key="s">
                                                        <button type="button" @click="insertDirectEmoji(s)" class="h-7 rounded hover:bg-purple-100 dark:hover:bg-purple-900/50 text-sm flex items-center justify-center transition cursor-pointer" x-text="s"></button>
                                                    </template>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- FLOATING MENTION AUTOCOMPLETE DROPDOWN (Above Direct Input) -->
                                <div x-show="showMentionDropdown && mentionTargetField === 'direct' && mentionUsers.length > 0"
                                     @click.outside="showMentionDropdown = false"
                                     x-cloak
                                     class="absolute bottom-full left-0 right-0 mb-2 bg-white dark:bg-[#160a33] border border-slate-200 dark:border-purple-800 rounded-2xl shadow-2xl z-50 overflow-hidden divide-y divide-slate-100 dark:divide-purple-900/40">
                                    <div class="px-3 py-1.5 bg-slate-50 dark:bg-purple-950/70 flex items-center justify-between text-[10px] font-black uppercase text-purple-700 dark:text-purple-300">
                                        <span class="flex items-center gap-1">
                                            <i class="fa-solid fa-at text-purple-500"></i>
                                            <span>Speler Vermelden</span>
                                        </span>
                                        <span class="text-[9px] text-slate-400 font-normal lowercase">↑↓ navigeer • enter</span>
                                    </div>
                                    <div class="max-h-48 overflow-y-auto p-1 space-y-0.5">
                                        <template x-for="(user, idx) in mentionUsers" :key="user.id">
                                            <div @click="selectMention(user)"
                                                 @mouseenter="mentionSelectedIndex = idx"
                                                 :class="mentionSelectedIndex === idx ? 'bg-purple-100 dark:bg-purple-900/60 border-l-2 border-purple-500' : 'hover:bg-slate-50 dark:hover:bg-purple-950/30 border-l-2 border-transparent'"
                                                 class="px-2.5 py-1.5 rounded-lg flex items-center justify-between gap-2 cursor-pointer transition">
                                                <div class="flex items-center gap-2 min-w-0">
                                                    <div class="relative shrink-0">
                                                        <img :src="user.avatar || '{{ asset('assets/characters/avatar_male_1.jpg') }}'" 
                                                             class="w-6 h-6 rounded-md object-cover border border-slate-200 dark:border-purple-800"
                                                             onerror="this.src='{{ asset('assets/characters/avatar_male_1.jpg') }}'">
                                                        <span x-show="user.is_online" 
                                                              class="absolute -bottom-0.5 -right-0.5 w-1.5 h-1.5 rounded-full bg-emerald-500 ring-1 ring-white dark:ring-slate-900"></span>
                                                    </div>
                                                    <div class="min-w-0">
                                                        <div class="flex items-center gap-1">
                                                            <span class="text-xs font-black text-slate-900 dark:text-white truncate" x-text="user.name"></span>
                                                            <template x-if="user.gang_tag">
                                                                <span class="text-[8px] font-black uppercase text-purple-600 dark:text-purple-400" x-text="'[' + user.gang_tag + ']'"></span>
                                                            </template>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="flex items-center gap-1 shrink-0">
                                                    <span class="px-1 py-0.2 rounded bg-slate-200 dark:bg-purple-950 text-slate-700 dark:text-purple-300 text-[8px] font-black" x-text="'L' + user.level"></span>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </div>

                                <div class="relative flex-1 flex items-center min-w-0">
                                    <input type="text" x-ref="directInputField" x-model="directInput" 
                                           @input="onChatInput($event, 'direct')"
                                           @keydown="onChatKeydown($event, 'direct')"
                                           placeholder="Typ @naam of een privébericht... (Enter)" maxlength="500"
                                           class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-purple-800/50 rounded-xl px-3 py-1.5 pr-14 text-xs text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-purple-500">
                                    <span x-show="directInput.length > 350" x-cloak
                                          class="absolute right-2 text-[10px] font-mono pointer-events-none"
                                          :class="directInput.length >= 500 ? 'text-red-500 font-bold' : 'text-slate-400 dark:text-purple-300'"
                                          x-text="directInput.length + '/500'"></span>
                                </div>
                                <button type="submit" :disabled="isSendingDirect || !directInput.trim()" class="w-8 h-8 rounded-xl bg-purple-600 hover:bg-purple-700 disabled:opacity-40 text-white flex items-center justify-center text-xs transition cursor-pointer shadow-xs shrink-0">
                                    <i class="fa-solid fa-paper-plane text-[10px]"></i>
                                </button>
                            </form>
                        </div>
                    </template>
                </div>
            </template>
        </div>

        <!-- 3. TAB: AI KENNISBANK & HELPBOT -->
        <div x-show="activeTab === 'help'" class="flex-1 flex flex-col min-h-0">
            <div x-ref="helpBox" class="flex-1 min-h-0 p-3 overflow-y-auto overflow-x-hidden space-y-2.5 text-xs bg-slate-50/70 dark:bg-[#0d041e]/50">
                <template x-for="(msg, i) in helpMessages" :key="i">
                    <div :class="msg.from === 'bot' ? 'flex flex-col items-start' : 'flex flex-col items-end'">
                        <div :class="msg.from === 'bot' ? 'bg-white dark:bg-purple-950/40 border border-slate-200 dark:border-purple-800/40 text-slate-800 dark:text-purple-100' : 'bg-purple-600 text-white font-medium'"
                             class="rounded-2xl p-2.5 max-w-[88%] space-y-1.5 shadow-2xs break-words [overflow-wrap:anywhere] [word-break:break-word]">
                            <p class="whitespace-pre-line leading-relaxed break-words [overflow-wrap:anywhere] [word-break:break-word]" x-text="msg.text"></p>
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
            <form @submit.prevent="sendHelpQuery()" data-no-async="true" class="p-2.5 bg-white dark:bg-[#150a30] border-t border-slate-200 dark:border-purple-900/40 flex items-center gap-1.5 shrink-0">
                <input type="text" x-model="helpInput" placeholder="Stel een vraag aan de kennisbank..." 
                       class="flex-1 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-purple-800/50 rounded-xl px-3 py-2 text-xs text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-purple-500">
                <button type="submit" class="w-8 h-8 rounded-xl bg-purple-600 hover:bg-purple-700 text-white flex items-center justify-center text-xs transition cursor-pointer shadow-xs shrink-0">
                    <i class="fa-solid fa-paper-plane"></i>
                </button>
            </form>
        </div>

    </div>

    <!-- FLOATING LAUNCHER BUTTON (Bottom Right Corner) -->
    <button type="button" @click="isOpen = !isOpen; if(isOpen) { $nextTick(() => { if(activeTab === 'global') scrollChatBottom(); else if(activeTab === 'gang') scrollGangBottom(); else if(activeTab === 'direct' && activeDirectPartner) scrollDirectBottom(); }); }"
            class="tbs-mini-chat-btn px-4 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-black text-xs shadow-lg shadow-purple-900/25 flex items-center gap-2.5 cursor-pointer transition transform hover:scale-102 active:scale-98 border border-purple-400">
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
                if (this.reportedType === 'gang') return 'Bende';
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
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}',
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

    // Global crystal-clear notification chime synthesizer (no clicks, no distortion, anti-overlap)
    window.playTBSChime = function() {
        if (localStorage.getItem('tbs_chat_muted') === '1') return;
        const now = Date.now();
        if (window.__tbsLastChime && (now - window.__tbsLastChime < 2500)) return;

        try {
            const AudioCtx = window.AudioContext || window.webkitAudioContext;
            if (!AudioCtx) return;
            if (!window.__tbsAudioCtx || window.__tbsAudioCtx.state === 'closed') {
                window.__tbsAudioCtx = new AudioCtx();
            }
            const ctx = window.__tbsAudioCtx;
            
            // If context is suspended (no user gesture yet), resume it asynchronously but do NOT queue sounds at time 0!
            if (ctx.state === 'suspended') {
                ctx.resume().catch(() => {});
                return;
            }

            window.__tbsLastChime = now;
            const t0 = ctx.currentTime;
            
            // Note 1: Warm soft bell (D5 - 587.33 Hz)
            const osc1 = ctx.createOscillator();
            const gain1 = ctx.createGain();
            osc1.type = 'sine';
            osc1.frequency.setValueAtTime(587.33, t0);
            
            gain1.gain.setValueAtTime(0.0001, t0);
            gain1.gain.exponentialRampToValueAtTime(0.08, t0 + 0.015);
            gain1.gain.exponentialRampToValueAtTime(0.0001, t0 + 0.22);
            
            osc1.connect(gain1);
            gain1.connect(ctx.destination);
            osc1.start(t0);
            osc1.stop(t0 + 0.24);

            // Note 2: Harmonious high note (A5 - 880.00 Hz) starting 80ms later
            const t1 = t0 + 0.08;
            const osc2 = ctx.createOscillator();
            const gain2 = ctx.createGain();
            osc2.type = 'sine';
            osc2.frequency.setValueAtTime(880.00, t1);

            gain2.gain.setValueAtTime(0.0001, t1);
            gain2.gain.exponentialRampToValueAtTime(0.06, t1 + 0.015);
            gain2.gain.exponentialRampToValueAtTime(0.0001, t1 + 0.28);

            osc2.connect(gain2);
            gain2.connect(ctx.destination);
            osc2.start(t1);
            osc2.stop(t1 + 0.30);
        } catch (e) {}
    };

    // Unlock AudioContext on first user interaction so it is running and never piles up
    ['click', 'touchstart', 'keydown'].forEach(evt => {
        window.addEventListener(evt, () => {
            if (window.__tbsAudioCtx && window.__tbsAudioCtx.state === 'suspended') {
                window.__tbsAudioCtx.resume().catch(() => {});
            }
        }, { once: true, passive: true });
    });

    // Mini Live Chat Widget Handler with Synthesized Audio Ping & Notification Toasts
    function tbsMiniLiveChat(cfg = {}) {
        return {
            myId: cfg.myId || 0,
            myName: cfg.myName || '',
            myAvatar: cfg.myAvatar || '{{ asset('assets/characters/avatar_male_1.jpg') }}',
            canModerate: cfg.canModerate || false,
            hasGang: cfg.hasGang || false,
            gangId: cfg.gangId || 0,
            gangName: cfg.gangName || 'Bende',
            gangTag: cfg.gangTag || '',
            gangHasChat: cfg.gangHasChat || false,
            characterStars: cfg.characterStars || 0,
            gangStars: cfg.gangStars || 0,

            isOpen: false,
            activeTab: 'global',

            // Plein chat state
            publicMessages: [],
            lastPublicId: 0,
            inputMessage: '',
            isSending: false,
            isLoadingPublic: false,
            showEmojiPicker: false,

            // Bende chat state
            gangMessages: [],
            lastGangId: 0,
            gangInputMessage: '',
            isSendingGang: false,
            isLoadingGang: false,
            showGangEmojiPicker: false,

            // Replies
            replyingTo: null,
            directReplyingTo: null,

            // Direct Messages in mini chat
            directConversations: [],
            isLoadingConversations: false,
            activeDirectPartner: null,
            activeDirectMessages: [],
            directInput: '',
            isSendingDirect: false,
            showDirectEmojiPicker: false,

            unreadDmsCount: 0,
            toastNotification: null,
            lastDirectDmId: 0,
            isSoundMuted: localStorage.getItem('tbs_chat_muted') === '1',
            lastChimeTime: 0,

            // Mention autocomplete state
            showMentionDropdown: false,
            mentionUsers: [],
            mentionSelectedIndex: 0,
            mentionTargetField: 'global',
            mentionQuery: '',

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

            getCsrf() {
                return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
            },

            init() {
                this.loadPublicMessages(true);
                if (this.hasGang && this.gangHasChat) {
                    this.loadGangMessages(true);
                }
                this.loadConversations();

                // Pre-seed lastDirectDmId so already-existing unread messages don't chime on page load
                fetch('{{ route('chat.poll-incoming') }}', { headers: { 'Accept': 'application/json' } })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success && data.new_dm && data.new_dm.id) {
                            this.lastDirectDmId = data.new_dm.id;
                        }
                    })
                    .catch(() => {});

                this.pollLoop();

                this.$watch('isOpen', (open) => {
                    if (open) {
                        if (this.activeTab === 'direct' && this.activeDirectPartner) {
                            this.scrollDirectBottom();
                        } else if (this.activeTab === 'gang') {
                            this.scrollGangBottom();
                        } else if (this.activeTab === 'global') {
                            this.scrollChatBottom();
                        }
                    }
                });

                this.$watch('activeTab', (tab) => {
                    if (tab === 'direct' && this.activeDirectPartner) {
                        this.scrollDirectBottom();
                    } else if (tab === 'gang') {
                        this.scrollGangBottom();
                    } else if (tab === 'global') {
                        this.scrollChatBottom();
                    }
                });

                this.$watch('activeDirectMessages', () => {
                    this.scrollDirectBottom();
                });

                window.addEventListener('tbs-open-direct-chat', (e) => {
                    if (e.detail && e.detail.id !== undefined) {
                        this.openDirectChatWithPlayer(e.detail.id, e.detail.name || 'Inmate', e.detail.avatar || '/characters/avatar-default.png');
                    }
                });

                window.openTBSDirectChat = (id, name, avatar) => {
                    this.openDirectChatWithPlayer(id, name || 'Inmate', avatar || '/characters/avatar-default.png');
                };
            },

            toggleSound() {
                this.isSoundMuted = !this.isSoundMuted;
                localStorage.setItem('tbs_chat_muted', this.isSoundMuted ? '1' : '0');
            },

            playChime() {
                if (this.isSoundMuted) return;
                if (window.playTBSChime) {
                    window.playTBSChime();
                }
            },

            setTab(tab) {
                this.activeTab = tab;
                if (tab === 'global') {
                    this.scrollChatBottom();
                } else if (tab === 'gang') {
                    if (this.hasGang && this.gangHasChat) {
                        this.loadGangMessages();
                    }
                    this.scrollGangBottom();
                } else if (tab === 'direct') {
                    this.loadConversations();
                    if (this.activeDirectPartner) {
                        this.scrollDirectBottom();
                    }
                }
            },

            scrollChatBottom(smooth = false) {
                const doScroll = () => {
                    const el = this.$refs.miniChatScroll || document.getElementById('tbsMiniChatScroll');
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
                        setTimeout(doScroll, 120);
                        setTimeout(doScroll, 250);
                    });
                });
            },

            scrollGangBottom(smooth = false) {
                const doScroll = () => {
                    const el = this.$refs.miniGangScroll || document.getElementById('tbsMiniGangScroll');
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
                        setTimeout(doScroll, 120);
                        setTimeout(doScroll, 250);
                    });
                });
            },

            scrollDirectBottom(smooth = false) {
                const doScroll = () => {
                    const el = this.$refs.miniDirectScroll || document.getElementById('tbsMiniDirectScroll');
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
                        setTimeout(doScroll, 30);
                        setTimeout(doScroll, 80);
                        setTimeout(doScroll, 160);
                        setTimeout(doScroll, 300);
                        setTimeout(doScroll, 600);
                    });
                });
            },

            formatMessage(text, isMe = false) {
                if (!text) return '';
                // Prevent XSS by HTML encoding first
                const tempDiv = document.createElement('div');
                tempDiv.textContent = text;
                let safe = tempDiv.innerHTML;

                // Convert @username into clean, sleek clickable badges linking to dossier profile without opening new tab
                const buildMentionPill = (username) => {
                    const url = '/speler/' + encodeURIComponent(username);
                    if (isMe) {
                        return `<a href="${url}" onclick="event.stopPropagation();" style="text-decoration:none;" class="inline-flex items-center font-bold no-underline px-1.5 py-0.5 mx-0.5 rounded-md bg-white/20 hover:bg-white/30 text-white transition cursor-pointer tracking-tight shadow-xs"><span class="opacity-75 text-[11px] font-black mr-0.5">@</span><span>${username}</span></a>`;
                    } else {
                        return `<a href="${url}" onclick="event.stopPropagation();" style="text-decoration:none;" class="inline-flex items-center font-bold no-underline px-1.5 py-0.5 mx-0.5 rounded-md bg-purple-100/90 hover:bg-purple-200 dark:bg-purple-950/80 dark:hover:bg-purple-900/90 text-purple-700 dark:text-purple-300 border border-purple-200/80 dark:border-purple-800/60 transition cursor-pointer tracking-tight shadow-2xs"><span class="text-purple-500 dark:text-purple-400 text-[11px] font-black mr-0.5">@</span><span>${username}</span></a>`;
                    }
                };

                // Supports Unicode letters (e.g. ë, é, ï, etc.), digits, hyphens, underscores and dots
                const mentionRegex = /(?<!<a[^>]*>)@([\p{L}\p{N}_\.\-]+)(?![^<]*<\/a>)/gu;
                safe = safe.replace(mentionRegex, (match, username) => buildMentionPill(username));

                return safe;
            },

            async onChatInput(e, fieldType = 'global') {
                this.mentionTargetField = fieldType;
                let inputEl = null;
                let text = '';
                if (fieldType === 'gang') {
                    inputEl = this.$refs.gangInputField;
                    text = this.gangInputMessage;
                } else if (fieldType === 'direct') {
                    inputEl = this.$refs.directInputField;
                    text = this.directInput;
                } else {
                    inputEl = this.$refs.publicInputField;
                    text = this.inputMessage;
                }

                if (!inputEl) return;
                const cursorPos = inputEl.selectionStart || 0;
                const textBeforeCursor = text.slice(0, cursorPos);
                const match = textBeforeCursor.match(/(?:^|\s)@([\p{L}\p{N}_\.\-]*)$/u);

                if (match) {
                    const query = match[1] || '';
                    this.mentionQuery = query;
                    await this.fetchMentionSuggestions(query);
                } else {
                    this.showMentionDropdown = false;
                    this.mentionUsers = [];
                }
            },

            async fetchMentionSuggestions(q) {
                try {
                    const res = await fetch(`/chat/zoek-spelers?q=${encodeURIComponent(q)}`);
                    if (res.ok) {
                        const data = await res.json();
                        if (data.success && Array.isArray(data.players) && data.players.length > 0) {
                            this.mentionUsers = data.players;
                            this.mentionSelectedIndex = 0;
                            this.showMentionDropdown = true;
                            return;
                        }
                    }
                } catch (e) {
                    console.error('Mention fetch error:', e);
                }
                this.showMentionDropdown = false;
                this.mentionUsers = [];
            },

            onChatKeydown(e, fieldType = 'global') {
                if (this.showMentionDropdown && this.mentionTargetField === fieldType && this.mentionUsers.length > 0) {
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
            },

            selectMention(user) {
                let inputEl = null;
                let currentText = '';
                if (this.mentionTargetField === 'gang') {
                    inputEl = this.$refs.gangInputField;
                    currentText = this.gangInputMessage;
                } else if (this.mentionTargetField === 'direct') {
                    inputEl = this.$refs.directInputField;
                    currentText = this.directInput;
                } else {
                    inputEl = this.$refs.publicInputField;
                    currentText = this.inputMessage;
                }

                const cursorPos = inputEl ? inputEl.selectionStart : currentText.length;
                const textBeforeCursor = currentText.slice(0, cursorPos);
                const textAfterCursor = currentText.slice(cursorPos);

                const match = textBeforeCursor.match(/(?:^|\s)@([\p{L}\p{N}_\.\-]*)$/u);
                if (match) {
                    const atIndex = match.index + (match[0].startsWith('@') ? 0 : 1);
                    const prefix = textBeforeCursor.slice(0, atIndex);
                    const inserted = '@' + user.name + ' ';
                    const updated = prefix + inserted + textAfterCursor;
                    if (this.mentionTargetField === 'gang') {
                        this.gangInputMessage = updated;
                    } else if (this.mentionTargetField === 'direct') {
                        this.directInput = updated;
                    } else {
                        this.inputMessage = updated;
                    }

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

            tagPlayer(name) {
                if (!name) return;
                const tag = '@' + name.trim() + ' ';
                if (this.activeTab === 'gang') {
                    if (!this.gangInputMessage.includes(tag)) {
                        this.gangInputMessage = tag + this.gangInputMessage;
                    }
                    this.$nextTick(() => {
                        if (this.$refs.gangInputField) this.$refs.gangInputField.focus();
                    });
                } else {
                    if (!this.inputMessage.includes(tag)) {
                        this.inputMessage = tag + this.inputMessage;
                    }
                    this.$nextTick(() => {
                        if (this.$refs.publicInputField) this.$refs.publicInputField.focus();
                    });
                }
            },

            setReplyTo(msg, type = 'public') {
                if (type === 'direct') {
                    this.directReplyingTo = {
                        id: msg.id,
                        name: msg.is_me ? this.myName : (this.activeDirectPartner?.name || 'Inmate'),
                        text: msg.message,
                    };
                } else {
                    this.replyingTo = {
                        id: msg.id,
                        character_id: msg.character_id,
                        name: msg.name,
                        text: msg.message,
                    };
                }
            },

            cancelReply() {
                this.replyingTo = null;
            },

            cancelDirectReply() {
                this.directReplyingTo = null;
            },

            insertEmoji(emoji) {
                this.inputMessage += emoji;
                this.showEmojiPicker = false;
            },

            insertGangEmoji(emoji) {
                this.gangInputMessage += emoji;
                this.showGangEmojiPicker = false;
            },

            insertDirectEmoji(emoji) {
                this.directInput += emoji;
                this.showDirectEmojiPicker = false;
            },

            async togglePublicHeart(msg) {
                try {
                    const res = await fetch(`/chat/openbaar/${msg.id}/hartje`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': this.getCsrf(),
                            'Accept': 'application/json',
                        }
                    });
                    const data = await res.json();
                    if (data.success) {
                        msg.hearts_count = data.hearts_count;
                        msg.has_hearted = data.has_hearted;
                    }
                } catch(e) {}
            },

            async toggleDirectHeart(dm) {
                try {
                    const res = await fetch(`/chat/prive-bericht/${dm.id}/hartje`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': this.getCsrf(),
                            'Accept': 'application/json',
                        }
                    });
                    const data = await res.json();
                    if (data.success) {
                        dm.hearts_count = data.hearts_count;
                        dm.has_hearted = data.has_hearted;
                    }
                } catch(e) {}
            },

            async deletePublicMessage(msgId) {
                if (!confirm('Weet je zeker dat je dit bericht wilt verwijderen?')) return;
                try {
                    const res = await fetch(`/chat/openbaar/${msgId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': this.getCsrf(),
                            'Accept': 'application/json',
                        }
                    });
                    const data = await res.json();
                    if (data.success) {
                        this.publicMessages = this.publicMessages.filter(m => m.id !== msgId);
                        this.gangMessages = this.gangMessages.filter(m => m.id !== msgId);
                    } else {
                        alert(data.message || 'Kon bericht niet verwijderen.');
                    }
                } catch(e) {
                    alert('Er is een fout opgetreden.');
                }
            },

            async deleteDirectMessage(dmId) {
                if (!confirm('Bericht verwijderen?')) return;
                try {
                    const res = await fetch(`/chat/prive-bericht/${dmId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': this.getCsrf(),
                            'Accept': 'application/json',
                        }
                    });
                    const data = await res.json();
                    if (data.success) {
                        this.activeDirectMessages = this.activeDirectMessages.filter(m => m.id !== dmId);
                    }
                } catch(e) {}
            },

            async loadPublicMessages(isFirst = false) {
                if (document.visibilityState === 'hidden' && !isFirst) return;
                if (this.isLoadingPublic) return;
                this.isLoadingPublic = true;
                try {
                    const url = '{{ route('chat.public.messages') }}?channel=global' + (this.lastPublicId > 0 ? ('&after_id=' + this.lastPublicId) : '');
                    const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
                    const data = await res.json();
                    if (data.success && data.messages) {
                        if (this.lastPublicId === 0) {
                            // Safe merge to avoid wiping out any locally-sent messages while initial fetch was in-flight
                            const existingMap = new Map();
                            data.messages.forEach(m => existingMap.set(m.id, m));
                            this.publicMessages.forEach(m => {
                                if (!existingMap.has(m.id)) {
                                    existingMap.set(m.id, m);
                                }
                            });
                            const merged = Array.from(existingMap.values());
                            merged.sort((a, b) => a.id - b.id);
                            this.publicMessages = merged;
                            this.scrollChatBottom();
                        } else if (data.messages.length > 0) {
                            const newIncoming = data.messages.filter(m => !m.is_me);
                            if (newIncoming.length > 0) {
                                const myNameLower = (this.myName || '').toLowerCase();
                                
                                // Only pop-up toast and sound chime if tagged (@MyName) or replied to!
                                const notifyMsg = newIncoming.find(m => {
                                    const isTagged = myNameLower && m.message && m.message.toLowerCase().includes('@' + myNameLower);
                                    const isReplied = (m.reply_to_name && m.reply_to_name.toLowerCase() === myNameLower);
                                    return isTagged || isReplied;
                                });

                                if (notifyMsg) {
                                    this.playChime();
                                    if (!this.isOpen) {
                                        const isTagged = myNameLower && notifyMsg.message.toLowerCase().includes('@' + myNameLower);
                                        this.toastNotification = {
                                            type: 'global',
                                            name: notifyMsg.name,
                                            text: (isTagged ? '🔔 Je bent getagd: ' : '↩️ Reactie op jou: ') + notifyMsg.message,
                                            avatar: notifyMsg.avatar,
                                            time: notifyMsg.time || 'Zojuist',
                                        };
                                        setTimeout(() => { 
                                            if (this.toastNotification && this.toastNotification.type === 'global') {
                                                this.toastNotification = null; 
                                            }
                                        }, 7000);
                                    }
                                }
                            }

                            // Strict deduplication by ID so Alpine :key never collides and messages never disappear
                            const existingIds = new Set(this.publicMessages.map(m => m.id));
                            const toAdd = data.messages.filter(m => !existingIds.has(m.id));
                            if (toAdd.length > 0) {
                                this.publicMessages = [...this.publicMessages, ...toAdd];
                                this.scrollChatBottom();
                            }
                        }
                        if (data.last_id > this.lastPublicId) {
                            this.lastPublicId = data.last_id;
                        }
                        if (this.publicMessages.length > 0) {
                            const maxId = Math.max(...this.publicMessages.map(m => m.id));
                            if (maxId > this.lastPublicId) {
                                this.lastPublicId = maxId;
                            }
                        }
                    }
                } catch (e) {
                } finally {
                    this.isLoadingPublic = false;
                }
            },

            async loadGangMessages(isFirst = false) {
                if (!this.hasGang || !this.gangHasChat || (document.visibilityState === 'hidden' && !isFirst)) return;
                if (this.isLoadingGang) return;
                this.isLoadingGang = true;
                try {
                    const channel = 'gang_' + this.gangId;
                    const url = `{{ route('chat.public.messages') }}?channel=${channel}` + (this.lastGangId > 0 ? ('&after_id=' + this.lastGangId) : '');
                    const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
                    const data = await res.json();
                    if (data.success && data.messages) {
                        if (this.lastGangId === 0) {
                            const existingMap = new Map();
                            data.messages.forEach(m => existingMap.set(m.id, m));
                            this.gangMessages.forEach(m => {
                                if (!existingMap.has(m.id)) {
                                    existingMap.set(m.id, m);
                                }
                            });
                            const merged = Array.from(existingMap.values());
                            merged.sort((a, b) => a.id - b.id);
                            this.gangMessages = merged;
                            this.scrollGangBottom();
                        } else if (data.messages.length > 0) {
                            const newIncoming = data.messages.filter(m => !m.is_me);
                            if (newIncoming.length > 0) {
                                const myNameLower = (this.myName || '').toLowerCase();
                                const notifyMsg = newIncoming.find(m => {
                                    const isTagged = myNameLower && m.message && m.message.toLowerCase().includes('@' + myNameLower);
                                    const isReplied = (m.reply_to_name && m.reply_to_name.toLowerCase() === myNameLower);
                                    return isTagged || isReplied;
                                });

                                if (notifyMsg) {
                                    this.playChime();
                                    if (!this.isOpen) {
                                        const isTagged = myNameLower && notifyMsg.message.toLowerCase().includes('@' + myNameLower);
                                        this.toastNotification = {
                                            type: 'gang',
                                            name: `[${this.gangTag || 'Bende'}] ` + notifyMsg.name,
                                            text: (isTagged ? '🔔 Bende tag: ' : '↩️ Bende reactie: ') + notifyMsg.message,
                                            avatar: notifyMsg.avatar,
                                            time: notifyMsg.time || 'Zojuist',
                                        };
                                        setTimeout(() => { 
                                            if (this.toastNotification && this.toastNotification.type === 'gang') {
                                                this.toastNotification = null; 
                                            }
                                        }, 7000);
                                    }
                                }
                            }

                            const existingIds = new Set(this.gangMessages.map(m => m.id));
                            const toAdd = data.messages.filter(m => !existingIds.has(m.id));
                            if (toAdd.length > 0) {
                                this.gangMessages = [...this.gangMessages, ...toAdd];
                                this.scrollGangBottom();
                            }
                        }
                        if (data.last_id > this.lastGangId) {
                            this.lastGangId = data.last_id;
                        }
                        if (this.gangMessages.length > 0) {
                            const maxId = Math.max(...this.gangMessages.map(m => m.id));
                            if (maxId > this.lastGangId) {
                                this.lastGangId = maxId;
                            }
                        }
                    }
                } catch(e) {
                } finally {
                    this.isLoadingGang = false;
                }
            },

            openToastDestination() {
                if (!this.toastNotification) return;
                const note = this.toastNotification;
                this.toastNotification = null;
                this.isOpen = true;
                if (note.type === 'direct' && note.partner_id !== undefined) {
                    this.openDirectChat({
                        id: note.partner_id,
                        partner_id: note.partner_id,
                        name: note.name,
                        avatar: note.avatar,
                        is_bot: note.is_bot || false,
                    });
                } else if (note.type === 'gang') {
                    this.setTab('gang');
                } else {
                    this.setTab('global');
                }
            },

            async sendPublicMessage() {
                const text = this.inputMessage.trim();
                if (!text || this.isSending) return;
                this.isSending = true;
                const replyId = this.replyingTo ? this.replyingTo.id : null;
                try {
                    const res = await fetch('{{ route('chat.public.send') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': this.getCsrf(),
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({ 
                            channel: 'global', 
                            message: text,
                            reply_to_id: replyId,
                        })
                    });
                    const data = await res.json();
                    if (data.success && data.message) {
                        this.inputMessage = '';
                        this.cancelReply();
                        if (!this.publicMessages.some(m => m.id === data.message.id)) {
                            this.publicMessages.push(data.message);
                        }
                        this.lastPublicId = Math.max(this.lastPublicId, data.message.id);
                        this.scrollChatBottom();
                    } else if (data.message) {
                        alert(data.message);
                    }
                } catch (e) {
                    alert('Kon bericht niet verzenden.');
                } finally {
                    this.isSending = false;
                }
            },

            async sendGangMessage() {
                const text = this.gangInputMessage.trim();
                if (!text || this.isSendingGang) return;
                this.isSendingGang = true;
                const replyId = this.replyingTo ? this.replyingTo.id : null;
                try {
                    const res = await fetch('{{ route('chat.public.send') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': this.getCsrf(),
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({ 
                            channel: 'gang_' + this.gangId, 
                            message: text,
                            reply_to_id: replyId,
                        })
                    });
                    const data = await res.json();
                    if (data.success && data.message) {
                        this.gangInputMessage = '';
                        this.cancelReply();
                        if (!this.gangMessages.some(m => m.id === data.message.id)) {
                            this.gangMessages.push(data.message);
                        }
                        this.lastGangId = Math.max(this.lastGangId, data.message.id);
                        this.scrollGangBottom();
                    } else if (data.message) {
                        alert(data.message);
                    }
                } catch (e) {
                    alert('Kon bendebericht niet verzenden.');
                } finally {
                    this.isSendingGang = false;
                }
            },

            async loadConversations() {
                this.isLoadingConversations = true;
                try {
                    const res = await fetch('{{ route('chat.conversations') }}', { headers: { 'Accept': 'application/json' } });
                    const data = await res.json();
                    if (data.success && data.conversations) {
                        this.directConversations = data.conversations;
                        this.unreadDmsCount = data.conversations.reduce((acc, c) => acc + (c.unread_count || 0), 0);
                    }
                } catch(e) {} finally {
                    this.isLoadingConversations = false;
                }
            },

            async openDirectChat(partner) {
                this.activeDirectPartner = partner;
                this.activeTab = 'direct';
                this.activeDirectMessages = [];
                const pId = partner.partner_id !== undefined ? partner.partner_id : partner.id;
                try {
                    const res = await fetch(`/chat/prive/${pId}`, { headers: { 'Accept': 'application/json' } });
                    const data = await res.json();
                    if (data.success) {
                        if (data.partner) {
                            this.activeDirectPartner = data.partner;
                        }
                        this.activeDirectMessages = data.messages || [];
                        this.scrollDirectBottom();
                        setTimeout(() => this.scrollDirectBottom(), 100);
                        setTimeout(() => this.scrollDirectBottom(), 300);
                        const conv = this.directConversations.find(c => c.partner_id === pId);
                        if (conv) conv.unread_count = 0;
                    }
                } catch(e) {}
            },

            openDirectChatWithPlayer(id, name, avatar) {
                this.isOpen = true;
                this.openDirectChat({ id: id, partner_id: id, name: name, avatar: avatar, is_bot: false });
            },

            closeDirectChat() {
                this.activeDirectPartner = null;
                this.activeDirectMessages = [];
                this.cancelDirectReply();
                this.loadConversations();
            },

            async sendDirectMessage() {
                if (!this.activeDirectPartner) return;
                const text = this.directInput.trim();
                if (!text || this.isSendingDirect) return;
                const pId = this.activeDirectPartner.partner_id !== undefined ? this.activeDirectPartner.partner_id : this.activeDirectPartner.id;
                this.isSendingDirect = true;
                const replyId = this.directReplyingTo ? this.directReplyingTo.id : null;
                try {
                    const res = await fetch(`/chat/prive/${pId}/verzenden`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': this.getCsrf(),
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({ 
                            message: text,
                            reply_to_id: replyId,
                        })
                    });
                    const data = await res.json();
                    if (data.success && data.message) {
                        this.directInput = '';
                        this.cancelDirectReply();
                        if (!this.activeDirectMessages.some(m => m.id === data.message.id)) {
                            this.activeDirectMessages.push(data.message);
                        }
                        this.scrollDirectBottom();
                        this.loadConversations();
                    } else if (data.message) {
                        alert(data.message);
                    }
                } catch(e) {
                    alert('Kon privébericht niet verzenden.');
                } finally {
                    this.isSendingDirect = false;
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
                if (window.tbsMiniChatInterval) {
                    clearInterval(window.tbsMiniChatInterval);
                    window.tbsMiniChatInterval = null;
                }

                let pollCounter = 0;
                window.tbsMiniChatInterval = setInterval(() => {
                    // 1. If tab is in background, skip completely to save server resources & battery
                    if (document.visibilityState === 'hidden') {
                        return;
                    }

                    pollCounter++;

                    // 2. Direct Messages & Badges:
                    // When chat is OPEN: check every 4 seconds. When chat is CLOSED: check every 16 seconds (4 ticks * 4s).
                    const checkDms = this.isOpen || (pollCounter % 4 === 0);
                    if (checkDms) {
                        const pollDmUrl = '{{ route('chat.poll-incoming') }}' + (this.lastDirectDmId > 0 ? ('?after_dm_id=' + this.lastDirectDmId) : '');
                        fetch(pollDmUrl, { headers: { 'Accept': 'application/json' } })
                            .then(r => r.json())
                            .then(data => {
                                if (data && data.success) {
                                    if (data.unread_dms !== undefined) {
                                        this.unreadDmsCount = data.unread_dms;
                                    }

                                    if (data.new_dm && data.new_dm.id) {
                                        const dm = data.new_dm;
                                        const isNewer = this.lastDirectDmId > 0 && dm.id > this.lastDirectDmId;
                                        this.lastDirectDmId = Math.max(this.lastDirectDmId, dm.id);

                                        if (isNewer) {
                                            const isChattingWithSender = this.isOpen && 
                                                this.activeTab === 'direct' && 
                                                this.activeDirectPartner && 
                                                (this.activeDirectPartner.id === dm.sender_id || this.activeDirectPartner.partner_id === dm.sender_id);

                                            this.playChime();
                                            this.loadConversations();

                                            if (isChattingWithSender) {
                                                if (!this.activeDirectMessages.some(m => m.id === dm.id)) {
                                                    this.activeDirectMessages.push({
                                                        id: dm.id,
                                                        sender_id: dm.sender_id,
                                                        recipient_id: 0,
                                                        message: dm.message,
                                                        type: 'chat',
                                                        metadata: null,
                                                        time: dm.time,
                                                        is_me: false,
                                                        is_read: true,
                                                        read_at: null,
                                                        reply_to_id: dm.reply_to_id || null,
                                                        reply_to_name: dm.reply_to_name || null,
                                                        reply_to_snippet: dm.reply_to_snippet || null,
                                                        hearts_count: 0,
                                                        has_hearted: false,
                                                    });
                                                    this.scrollDirectBottom();

                                                    // Immediately mark as read on backend so it doesn't stay unread
                                                    fetch(`/chat/prive/${dm.sender_id}`, { headers: { 'Accept': 'application/json' } })
                                                        .then(() => {
                                                            const conv = this.directConversations.find(c => c.partner_id === dm.sender_id);
                                                            if (conv) conv.unread_count = 0;
                                                        })
                                                        .catch(() => {});
                                                }
                                            } else {
                                                this.toastNotification = {
                                                    type: 'direct',
                                                    partner_id: dm.sender_id,
                                                    name: dm.sender_name,
                                                    text: dm.message,
                                                    avatar: dm.sender_avatar,
                                                    time: dm.time,
                                                    is_bot: dm.is_bot,
                                                };
                                                setTimeout(() => { 
                                                    if (this.toastNotification && this.toastNotification.type === 'direct') {
                                                        this.toastNotification = null; 
                                                    }
                                                }, 7000);
                                            }
                                        }
                                    }
                                }
                            })
                            .catch(() => {});
                    }

                    // 3. Public Messages & Gang Messages:
                    // ONLY poll if chat modal is explicitly OPEN! (Saves massive server CPU when players are just browsing)
                    if (this.isOpen) {
                        if (this.activeTab === 'global') {
                            this.loadPublicMessages();
                        } else if (this.activeTab === 'gang' && this.hasGang && this.gangHasChat) {
                            this.loadGangMessages();
                        }
                    }
                }, 4000);
            }
        };
    }
</script>
