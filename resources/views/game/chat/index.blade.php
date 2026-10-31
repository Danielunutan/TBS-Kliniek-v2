@extends('layouts.app')

@section('title', 'Live Chat & Privé Messenger - TBS Kliniek')
@section('meta_description', 'Chat live met alle inmates op het Kliniek Plein of in de besloten Bende Chat, en stuur privéberichten.')

@section('content')
<style>
    .tbs-chat-wrapper {
        height: 740px;
        max-height: calc(100vh - 210px);
        min-height: 520px;
        overscroll-behavior: contain;
    }
    #tbsChatScrollContainer {
        overscroll-behavior-y: contain;
        -webkit-overflow-scrolling: touch;
    }
    @media (max-width: 768px) {
        .tbs-chat-wrapper {
            height: calc(100dvh - 225px) !important;
            max-height: calc(100dvh - 200px) !important;
            min-height: 380px !important;
        }
    }
</style>

<!-- SCRIPT: REALTIME ALPINE MESSENGER LOGIC -->
<script>
window.tbsChatConfig = {
    authCharId: {{ (int) ($character->id ?? 0) }},
    authCharName: @js((string) ($character->name ?? '')),
    initialTab: @js((string) ($initialTab ?? 'global')),
    initialRecipient: {{ (int) ($initialRecipient ?? 0) }},
    hasGang: {{ ($hasGang ?? false) ? "true" : "false" }},
    gangId: {{ (int) ($gangId ?? 0) }},
    gangName: @js((string) ($gangName ?? 'Bende')),
    gangTag: @js((string) ($gangTag ?? '')),
    gangHasChat: {{ ($gangHasChat ?? false) ? "true" : "false" }},
    gangStars: {{ (int) ($gangStars ?? 0) }},
    characterStars: {{ (int) ($characterStars ?? 0) }},
    canModerate: {{ ($canModerate ?? false) ? "true" : "false" }},
    characterNames: @js($characterNames ?? [])
};
</script>

<script>
function tbsLiveMessenger(config) {
    return {
        authCharId: config.authCharId,
        authCharName: config.authCharName,
        activeTab: config.initialTab || 'global',
        mobileView: (config.initialTab === 'direct' && !(config.initialRecipient > 0)) ? 'list' : 'chat',
        searchQuery: '',

        // Gang configuration
        hasGang: config.hasGang,
        gangId: config.gangId,
        gangName: config.gangName,
        gangTag: config.gangTag,
        gangHasChat: config.gangHasChat,
        gangStars: config.gangStars,
        characterStars: config.characterStars,
        canModerate: config.canModerate,
        characterNames: Array.isArray(config.characterNames) ? config.characterNames : [],
        
        // Public & Gang chat state
        publicMessagesList: [],
        gangMessagesList: [],
        lastPublicId: 0,
        lastGangId: 0,
        
        // Direct messages state
        conversationsList: [],
        directMessagesList: [],
        activePartnerId: (config.initialRecipient && config.initialRecipient > 0) ? config.initialRecipient : null,
        activePartner: {
            id: null,
            name: 'Kies een gesprek',
            avatar: '{{ asset('assets/characters/avatar_male_1.jpg') }}',
            is_bot: false,
            status_label: 'Selecteer een gesprek uit de lijst'
        },
        loadingConversations: false,
        totalUnread: 0,

        // Input & Interactive state
        messageInput: '',
        isSending: false,
        isRefreshing: false,
        isPolling: false,
        pollTimer: null,
        replyingTo: null,
        showEmojiPicker: false,

        // Mention autocomplete
        showMentionDropdown: false,
        mentionQuery: '',
        mentionUsers: [],
        mentionSelectedIndex: 0,

        getCsrf() {
            return '{{ csrf_token() }}';
        },

        formatMessage(text, isMe = false) {
            if (!text) return '';
            const tempDiv = document.createElement('div');
            tempDiv.textContent = text;
            let safe = tempDiv.innerHTML;

            const buildMentionPill = (username) => {
                const url = '/speler/' + encodeURIComponent(username);
                if (isMe) {
                    return `<a href="${url}" onclick="event.stopPropagation();" style="text-decoration:none;" class="inline-flex items-center font-bold no-underline px-1.5 py-0.5 mx-0.5 rounded-md bg-white/20 hover:bg-white/30 text-white transition cursor-pointer tracking-tight shadow-xs"><span class="opacity-75 text-[11px] font-black mr-0.5">@</span><span>${username}</span></a>`;
                } else {
                    return `<a href="${url}" onclick="event.stopPropagation();" style="text-decoration:none;" class="inline-flex items-center font-bold no-underline px-1.5 py-0.5 mx-0.5 rounded-md bg-purple-100/90 hover:bg-purple-200 dark:bg-purple-950/80 dark:hover:bg-purple-900/90 text-purple-700 dark:text-purple-300 border border-purple-200/80 dark:border-purple-800/60 transition cursor-pointer tracking-tight shadow-2xs"><span class="text-purple-600 dark:text-purple-400 text-[11px] font-black mr-0.5">@</span><span>${username}</span></a>`;
                }
            };

            // 1. Highlight known player names (including multi-word / accented names like @OOHPA Arjan, @Daniël)
            if (Array.isArray(this.characterNames) && this.characterNames.length > 0) {
                const sortedNames = [...this.characterNames].sort((a, b) => b.length - a.length);
                const escapedPatterns = sortedNames.map(n => n.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'));
                const knownRegex = new RegExp('@(' + escapedPatterns.join('|') + ')(?=$|[^\\p{L}\\p{N}])', 'gu');
                safe = safe.replace(knownRegex, (match, username) => buildMentionPill(username));
            }

            // 2. Fallback regex for single-word mentions with Unicode letters, numbers, dashes, underscores, dots
            // Only match @ if not inside an HTML tag or already wrapped in an <a> tag
            const fallbackRegex = /(?<!<a[^>]*>)@([\p{L}\p{N}_\.\-]+)(?![^<]*<\/a>)/gu;
            safe = safe.replace(fallbackRegex, (match, username) => {
                return buildMentionPill(username);
            });

            // 3. Convert links (https:// or http://) into safe clickable links
            const urlRegex = /(?<!href="|href=')(https?:\/\/[^\s<]+)/gi;
            const linkColorClass = isMe
                ? 'text-amber-200 hover:text-white'
                : 'text-purple-700 dark:text-amber-400 hover:text-purple-900 dark:hover:text-amber-300';
            safe = safe.replace(urlRegex, (url) => {
                const cleanUrl = url.replace(/"/g, '&quot;').replace(/'/g, '&#39;');
                return `<a href="${cleanUrl}" target="_blank" rel="noopener noreferrer" onclick="event.stopPropagation();" class="underline font-bold ${linkColorClass} break-all transition">${cleanUrl}</a>`;
            });

            return safe;
        },

        setReplyTo(msg) {
            this.replyingTo = {
                id: msg.id,
                name: msg.name,
                text: (msg.message || '').slice(0, 70)
            };
            this.$nextTick(() => {
                if (this.$refs.messageInputField) {
                    this.$refs.messageInputField.focus();
                }
            });
        },

        cancelReply() {
            this.replyingTo = null;
        },

        tagPlayer(name) {
            if (!name) return;
            const tag = '@' + name + ' ';
            if (!this.messageInput.includes(tag)) {
                this.messageInput = tag + this.messageInput;
            }
            this.$nextTick(() => {
                if (this.$refs.messageInputField) {
                    this.$refs.messageInputField.focus();
                }
            });
        },

        insertEmoji(emoji) {
            this.messageInput += emoji;
            this.$nextTick(() => {
                if (this.$refs.messageInputField) {
                    this.$refs.messageInputField.focus();
                }
            });
        },

        async onMessageInput(e) {
            const inputEl = this.$refs.messageInputField;
            if (!inputEl) return;

            const cursorPos = inputEl.selectionStart || 0;
            const textBeforeCursor = this.messageInput.slice(0, cursorPos);
            const match = textBeforeCursor.match(/(?:^|\s)@([\p{L}\p{N}_\.\-\s]*)$/u);

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
                let url = `/chat/zoek-spelers?q=${encodeURIComponent(q)}`;
                if (this.activeTab === 'gang') {
                    url += '&gang_only=1';
                }
                const res = await fetch(url);
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

        onMessageKeydown(e) {
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
            const inputEl = this.$refs.messageInputField;
            const cursorPos = inputEl ? inputEl.selectionStart : this.messageInput.length;
            const textBeforeCursor = this.messageInput.slice(0, cursorPos);
            const textAfterCursor = this.messageInput.slice(cursorPos);

            const match = textBeforeCursor.match(/(?:^|\s)@([\p{L}\p{N}_\.\-\s]*)$/u);
            if (match) {
                const atIndex = match.index + (match[0].startsWith('@') ? 0 : 1);
                const prefix = textBeforeCursor.slice(0, atIndex);
                const inserted = '@' + user.name + ' ';
                this.messageInput = prefix + inserted + textAfterCursor;
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

        get filteredConversations() {
            if (!this.searchQuery.trim()) {
                return this.conversationsList;
            }
            const q = this.searchQuery.toLowerCase();
            return this.conversationsList.filter(c => c.name.toLowerCase().includes(q));
        },

        initMessenger() {
            if (this.activeTab === 'global') {
                this.loadPublicMessages();
            } else if (this.activeTab === 'gang') {
                this.loadGangMessages();
            } else {
                this.loadConversations();
                if (this.activePartnerId !== null && this.activePartnerId !== undefined) {
                    this.loadDirectMessages(this.activePartnerId);
                }
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
            this.replyingTo = null;
            if (tab === 'direct') {
                if (this.activePartnerId === null || this.activePartnerId === undefined) {
                    this.mobileView = 'list';
                }
                this.loadConversations();
                if (this.activePartnerId !== null && this.activePartnerId !== undefined) {
                    this.loadDirectMessages(this.activePartnerId);
                }
            } else {
                this.mobileView = 'chat';
                if (tab === 'global') {
                    this.loadPublicMessages();
                } else if (tab === 'gang') {
                    this.loadGangMessages();
                }
            }
        },

        selectPartner(partnerId, partnerName = '') {
            this.activePartnerId = partnerId;
            this.mobileView = 'chat';
            this.replyingTo = null;
            this.loadDirectMessages(partnerId);
        },

        async loadPublicMessages() {
            try {
                const res = await fetch('{{ route("chat.public.messages") }}?channel=global');
                const data = await res.json();
                if (data.success) {
                    if (this.publicMessagesList.length === 0) {
                        this.publicMessagesList = data.messages;
                    } else {
                        const existingMap = new Map();
                        data.messages.forEach(m => existingMap.set(m.id, m));
                        this.publicMessagesList.forEach(m => {
                            if (!existingMap.has(m.id)) {
                                existingMap.set(m.id, m);
                            }
                        });
                        const merged = Array.from(existingMap.values());
                        merged.sort((a, b) => a.id - b.id);
                        this.publicMessagesList = merged;
                    }
                    this.lastPublicId = Math.max(this.lastPublicId, data.last_id || 0);
                    if (this.publicMessagesList.length > 0) {
                        const maxId = Math.max(...this.publicMessagesList.map(m => m.id));
                        if (maxId > this.lastPublicId) this.lastPublicId = maxId;
                    }
                    this.scrollToBottom();
                }
            } catch(e) {}
        },

        async loadGangMessages() {
            if (!this.hasGang || !this.gangHasChat) return;
            try {
                const res = await fetch(`{{ route("chat.public.messages") }}?channel=gang_${this.gangId}`);
                const data = await res.json();
                if (data.success) {
                    if (this.gangMessagesList.length === 0) {
                        this.gangMessagesList = data.messages;
                    } else {
                        const existingMap = new Map();
                        data.messages.forEach(m => existingMap.set(m.id, m));
                        this.gangMessagesList.forEach(m => {
                            if (!existingMap.has(m.id)) {
                                existingMap.set(m.id, m);
                            }
                        });
                        const merged = Array.from(existingMap.values());
                        merged.sort((a, b) => a.id - b.id);
                        this.gangMessagesList = merged;
                    }
                    this.lastGangId = Math.max(this.lastGangId, data.last_id || 0);
                    if (this.gangMessagesList.length > 0) {
                        const maxId = Math.max(...this.gangMessagesList.map(m => m.id));
                        if (maxId > this.lastGangId) this.lastGangId = maxId;
                    }
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
                    if (this.directMessagesList.length === 0) {
                        this.directMessagesList = data.messages;
                    } else {
                        const existingMap = new Map();
                        data.messages.forEach(m => existingMap.set(m.id, m));
                        this.directMessagesList.forEach(m => {
                            if (!existingMap.has(m.id)) {
                                existingMap.set(m.id, m);
                            }
                        });
                        const merged = Array.from(existingMap.values());
                        merged.sort((a, b) => a.id - b.id);
                        this.directMessagesList = merged;
                    }
                    this.scrollToBottom();
                    const conv = this.conversationsList.find(c => c.partner_id === partnerId);
                    if (conv) conv.unread_count = 0;
                }
            } catch(e) {}
        },

        playChime() {
            if (window.playTBSChime) {
                window.playTBSChime();
            }
        },

        async pollUpdates() {
            if (this.isPolling) return;
            this.isPolling = true;

            try {
                if (this.activeTab === 'global') {
                    const res = await fetch(`{{ route("chat.public.messages") }}?channel=global&after_id=${this.lastPublicId}`);
                    const data = await res.json();
                    if (data.success) {
                        if (data.deleted_ids && Array.isArray(data.deleted_ids) && data.deleted_ids.length > 0) {
                            const delSet = new Set(data.deleted_ids);
                            this.publicMessagesList = this.publicMessagesList.filter(m => !delSet.has(m.id));
                        }
                        if (data.messages && data.messages.length > 0) {
                            const newOthers = data.messages.filter(m => !m.is_me);
                            if (newOthers.length > 0) {
                                const myNameLower = (this.authCharName || '').toLowerCase();
                                const notifyMsg = newOthers.find(m => {
                                    const isTagged = myNameLower && m.message && m.message.toLowerCase().includes('@' + myNameLower);
                                    const isReplied = (m.reply_to_name && m.reply_to_name.toLowerCase() === myNameLower);
                                    return isTagged || isReplied;
                                });
                                if (notifyMsg) this.playChime();
                            }

                            const existingIds = new Set(this.publicMessagesList.map(m => m.id));
                            const toAdd = data.messages.filter(m => !existingIds.has(m.id));
                            if (toAdd.length > 0) {
                                this.publicMessagesList = [...this.publicMessagesList, ...toAdd];
                                this.scrollToBottom();
                            }
                            if (data.last_id > this.lastPublicId) {
                                this.lastPublicId = data.last_id;
                            }
                            if (this.publicMessagesList.length > 0) {
                                const maxId = Math.max(...this.publicMessagesList.map(m => m.id));
                                if (maxId > this.lastPublicId) this.lastPublicId = maxId;
                            }
                        }
                    }
                } else if (this.activeTab === 'gang' && this.hasGang && this.gangHasChat) {
                    const res = await fetch(`{{ route("chat.public.messages") }}?channel=gang_${this.gangId}&after_id=${this.lastGangId}`);
                    const data = await res.json();
                    if (data.success) {
                        if (data.deleted_ids && Array.isArray(data.deleted_ids) && data.deleted_ids.length > 0) {
                            const delSet = new Set(data.deleted_ids);
                            this.gangMessagesList = this.gangMessagesList.filter(m => !delSet.has(m.id));
                        }
                        if (data.messages && data.messages.length > 0) {
                            const newOthers = data.messages.filter(m => !m.is_me);
                            if (newOthers.length > 0) {
                                const myNameLower = (this.authCharName || '').toLowerCase();
                                const notifyMsg = newOthers.find(m => {
                                    const isTagged = myNameLower && m.message && m.message.toLowerCase().includes('@' + myNameLower);
                                    const isReplied = (m.reply_to_name && m.reply_to_name.toLowerCase() === myNameLower);
                                    return isTagged || isReplied;
                                });
                                if (notifyMsg) this.playChime();
                            }

                            const existingIds = new Set(this.gangMessagesList.map(m => m.id));
                            const toAdd = data.messages.filter(m => !existingIds.has(m.id));
                            if (toAdd.length > 0) {
                                this.gangMessagesList = [...this.gangMessagesList, ...toAdd];
                                this.scrollToBottom();
                            }
                            if (data.last_id > this.lastGangId) {
                                this.lastGangId = data.last_id;
                            }
                            if (this.gangMessagesList.length > 0) {
                                const maxId = Math.max(...this.gangMessagesList.map(m => m.id));
                                if (maxId > this.lastGangId) this.lastGangId = maxId;
                            }
                        }
                    }
                } else if (this.activeTab === 'direct') {
                    if (this.activePartnerId === null || this.activePartnerId === undefined) {
                        return;
                    }
                    let maxDmId = 0;
                    if (this.directMessagesList.length > 0) {
                        for (const m of this.directMessagesList) {
                            if (m.id && m.id > maxDmId) maxDmId = m.id;
                        }
                    }
                    const url = `/chat/prive/${this.activePartnerId}` + (maxDmId > 0 ? `?after_id=${maxDmId}` : '');
                    const res = await fetch(url);
                    const data = await res.json();
                    if (data.success) {
                        if (data.deleted_ids && Array.isArray(data.deleted_ids) && data.deleted_ids.length > 0) {
                            const delSet = new Set(data.deleted_ids);
                            this.directMessagesList = this.directMessagesList.filter(m => !delSet.has(m.id));
                        }
                        if (data.messages) {
                            const existingIds = new Set(this.directMessagesList.map(m => m.id));
                            const toAdd = data.messages.filter(m => !existingIds.has(m.id));
                            if (toAdd.length > 0) {
                                if (toAdd.some(m => !m.is_me)) this.playChime();
                                this.directMessagesList = [...this.directMessagesList, ...toAdd];
                                this.scrollToBottom();
                            }
                        }
                    }
                }
            } catch(e) {
            } finally {
                this.isPolling = false;
            }
        },

        async sendMessage() {
            const text = this.messageInput.trim();
            if (!text || this.isSending) return;

            this.isSending = true;

            try {
                if (this.activeTab === 'global' || this.activeTab === 'gang') {
                    const targetChannel = this.activeTab === 'gang' ? `gang_${this.gangId}` : 'global';
                    const payload = {
                        message: text,
                        channel: targetChannel
                    };
                    if (this.replyingTo && this.replyingTo.id) {
                        payload.reply_to_id = this.replyingTo.id;
                    }

                    const res = await fetch('{{ route("chat.public.send") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': this.getCsrf()
                        },
                        body: JSON.stringify(payload)
                    });
                    const data = await res.json();
                    if (data.success) {
                        if (this.activeTab === 'global') {
                            if (!this.publicMessagesList.some(m => m.id === data.message.id)) {
                                this.publicMessagesList.push(data.message);
                            }
                            this.lastPublicId = Math.max(this.lastPublicId, data.message.id);
                        } else {
                            if (!this.gangMessagesList.some(m => m.id === data.message.id)) {
                                this.gangMessagesList.push(data.message);
                            }
                            this.lastGangId = Math.max(this.lastGangId, data.message.id);
                        }
                        this.messageInput = '';
                        this.replyingTo = null;
                        this.scrollToBottom();
                    } else {
                        alert(data.message || 'Kon bericht niet versturen.');
                    }
                } else {
                    if (this.activePartnerId === null || this.activePartnerId === undefined) return;
                    const res = await fetch(`/chat/prive/${this.activePartnerId}/verzenden`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': this.getCsrf()
                        },
                        body: JSON.stringify({ message: text })
                    });
                    const data = await res.json();
                    if (data.success) {
                        if (!this.directMessagesList.some(m => m.id === data.message.id)) {
                            this.directMessagesList.push(data.message);
                        }
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

        async deleteDirectMessage(dmId) {
            if (!confirm('Weet je zeker dat je dit privébericht wilt verwijderen?')) return;
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
                    this.directMessagesList = this.directMessagesList.filter(m => m.id !== dmId);
                } else {
                    alert(data.message || 'Kon privébericht niet verwijderen.');
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
                    this.publicMessagesList = this.publicMessagesList.filter(m => m.id !== msgId);
                    this.gangMessagesList = this.gangMessagesList.filter(m => m.id !== msgId);
                } else {
                    alert(data.message || 'Kon bericht niet verwijderen.');
                }
            } catch(e) {}
        },

        async refreshActiveChat() {
            this.isRefreshing = true;
            if (this.activeTab === 'global') {
                await this.loadPublicMessages();
            } else if (this.activeTab === 'gang') {
                await this.loadGangMessages();
            } else {
                if (this.activePartnerId !== null && this.activePartnerId !== undefined) {
                    await this.loadDirectMessages(this.activePartnerId);
                }
                await this.loadConversations();
            }
            setTimeout(() => { this.isRefreshing = false; }, 400);
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
                });
            });
        }
    };
}

if (window.Alpine) {
    window.Alpine.data("tbsLiveMessenger", (cfg = window.tbsChatConfig) => tbsLiveMessenger(cfg));
} else {
    document.addEventListener("alpine:init", () => {
        Alpine.data("tbsLiveMessenger", (cfg = window.tbsChatConfig) => tbsLiveMessenger(cfg));
    });
}
window.tbsLiveMessenger = tbsLiveMessenger;

</script>

<div class="space-y-4" x-data="tbsLiveMessenger(window.tbsChatConfig)" x-init="initMessenger()">

    <!-- HEADER BAR (Mobile-Optimized Compact & Desktop Rich) -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-2.5 sm:p-5 shadow-xs relative overflow-hidden">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 sm:gap-4 relative z-10">
            <div class="flex items-center gap-2.5 sm:gap-3.5">
                <div class="w-9 h-9 sm:w-12 sm:h-12 rounded-xl sm:rounded-2xl bg-purple-600 text-white flex items-center justify-center text-base sm:text-xl shadow-md shrink-0">
                    <i class="fa-solid fa-comments"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h1 class="text-sm sm:text-lg font-black text-slate-900 dark:text-white tracking-tight">Kliniek Live Chat & DMs</h1>
                        <span class="px-2 py-0.5 rounded-full bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200/80 dark:border-emerald-800/70 font-extrabold text-[9px] sm:text-[10px] uppercase tracking-wider flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Live
                        </span>
                    </div>
                    <p class="hidden sm:block text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                        Praat realtime met alle inmates in de kliniek, stem af met je bende via de portofoon en chat 1-op-1 via privéberichten.
                    </p>
                </div>
            </div>

            <!-- TAB SWITCHER (Full-Width Touch Pills on Mobile) -->
            <div class="flex items-center gap-1 p-1 bg-slate-100/90 dark:bg-slate-800/80 border border-slate-200/80 dark:border-slate-700/70 rounded-xl shrink-0 w-full sm:w-auto">
                <!-- KLINIEK PLEIN TAB -->
                <button type="button" 
                        @click="switchTab('global')" 
                        :class="activeTab === 'global' ? 'bg-white dark:bg-slate-900 text-purple-600 dark:text-purple-400 font-black shadow-xs border border-slate-200/80 dark:border-slate-700' : 'text-slate-600 dark:text-slate-400 font-bold hover:text-slate-900 dark:hover:text-white'"
                        class="flex-1 sm:flex-initial px-2.5 sm:px-3.5 py-1.5 sm:py-2 rounded-lg text-[11px] sm:text-xs transition cursor-pointer flex items-center justify-center gap-1.5">
                    <i class="fa-solid fa-bullhorn text-[11px]"></i>
                    <span>Plein</span>
                </button>

                <!-- BENDE CHAT TAB -->
                <button type="button" 
                        @click="switchTab('gang')" 
                        :class="activeTab === 'gang' ? 'bg-white dark:bg-slate-900 text-amber-500 dark:text-amber-400 font-black shadow-xs border border-slate-200/80 dark:border-slate-700' : 'text-slate-600 dark:text-slate-400 font-bold hover:text-slate-900 dark:hover:text-white'"
                        class="flex-1 sm:flex-initial px-2.5 sm:px-3.5 py-1.5 sm:py-2 rounded-lg text-[11px] sm:text-xs transition cursor-pointer flex items-center justify-center gap-1.5 relative">
                    <i class="fa-solid fa-walkie-talkie text-[11px]"></i>
                    <span>Bende</span>
                    @if($hasGang)
                        @if($gangHasChat)
                            <span class="px-1 py-0.2 rounded-full bg-purple-50 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 border border-purple-200/80 dark:border-purple-800/70 font-mono text-[8px] font-black hidden xs:inline">
                                [{{ $gangTag }}]
                            </span>
                        @else
                            <span class="text-amber-500 text-[9px]" title="Portofoon niet geactiveerd (500 ⭐)">
                                <i class="fa-solid fa-lock"></i>
                            </span>
                        @endif
                    @else
                        <span class="text-slate-400 text-[9px]" title="Geen bende">
                            <i class="fa-solid fa-lock"></i>
                        </span>
                    @endif
                </button>

                <!-- PRIVÉ CHATS TAB -->
                <button type="button" 
                        @click="switchTab('direct')" 
                        :class="activeTab === 'direct' ? 'bg-white dark:bg-slate-900 text-purple-600 dark:text-purple-400 font-black shadow-xs border border-slate-200/80 dark:border-slate-700' : 'text-slate-600 dark:text-slate-400 font-bold hover:text-slate-900 dark:hover:text-white'"
                        class="flex-1 sm:flex-initial px-2.5 sm:px-3.5 py-1.5 sm:py-2 rounded-lg text-[11px] sm:text-xs transition cursor-pointer flex items-center justify-center gap-1.5 relative">
                    <i class="fa-solid fa-user-lock text-[11px]"></i>
                    <span>Privé</span>
                    <span x-show="totalUnread > 0" 
                          x-text="totalUnread" 
                          class="px-1.5 py-0.2 rounded-full bg-rose-500 text-white font-mono text-[9px] font-black animate-pulse">
                        {{ $totalUnreadDms }}
                    </span>
                </button>
            </div>
        </div>
    </div>

    <!-- MAIN MESSENGER CARD (CONSTRAINED HEIGHT & INTERNALLY SCROLLABLE) -->
    <div class="tbs-chat-wrapper bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl shadow-lg overflow-hidden flex flex-col md:flex-row relative">
        
        <!-- ========================================== -->
        <!-- LEFT COLUMN: CHANNELS & CONVERSATIONS LIST -->
        <!-- ========================================== -->
        <div class="w-full md:w-80 lg:w-96 shrink-0 border-r border-slate-200/90 dark:border-slate-800 flex flex-col bg-slate-50/90 dark:bg-slate-900/80 h-full min-h-0 overflow-hidden"
             :class="mobileView === 'chat' ? 'hidden md:flex' : 'flex'">
            
            <!-- HEADER -->
            <div class="p-4 border-b border-slate-200/90 dark:border-slate-800 space-y-3 shrink-0 bg-white dark:bg-slate-900">
                <div class="flex items-center justify-between gap-2">
                    <span class="text-xs font-black uppercase tracking-wider text-slate-500 dark:text-slate-400" 
                          x-text="activeTab === 'direct' ? 'Directe Gesprekken' : 'Kanalen & Ruimtes'">
                    </span>

                    <template x-if="activeTab === 'direct'">
                        <!-- START NIEUW GESPREK KNOP -->
                        <div class="relative" x-data="{ open: false }">
                            <button type="button" @click="open = !open" 
                                    class="px-2.5 py-1 rounded-xl bg-purple-600 hover:bg-purple-700 text-white text-[11px] font-black tracking-wide transition flex items-center gap-1.5 shadow-2xs cursor-pointer">
                                <i class="fa-solid fa-pen-to-square text-[10px]"></i>
                                <span>Nieuw</span>
                            </button>
                            <!-- FRIENDS DROPDOWN -->
                            <div x-show="open" @click.away="open = false" x-cloak
                                 class="absolute right-0 top-8 z-30 w-64 bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl shadow-xl p-2 space-y-1">
                                <div class="px-2.5 py-1.5 text-[11px] font-extrabold text-slate-400 uppercase tracking-wider">Kies een vriend:</div>
                                @forelse($friends ?? [] as $fr)
                                    <button type="button" 
                                            @click="selectPartner({{ $fr->id }}, @js($fr->name)); open = false;"
                                            class="w-full px-2.5 py-2 rounded-xl hover:bg-purple-50 dark:hover:bg-purple-950/50 text-left transition flex items-center gap-2.5 cursor-pointer">
                                        <img src="{{ $fr->avatar_url ?? asset('assets/characters/avatar_male_1.jpg') }}" 
                                             class="w-7 h-7 rounded-lg object-cover border border-slate-200 dark:border-slate-700"
                                             alt="{{ $fr->name }}"
                                             onerror="this.src='{{ asset('assets/characters/avatar_male_1.jpg') }}'">
                                        <div class="flex-1 min-w-0">
                                            <div class="text-xs font-black text-slate-900 dark:text-white truncate">{{ $fr->name }}</div>
                                            <div class="text-[10px] text-slate-400">Level {{ $fr->level }}</div>
                                        </div>
                                    </button>
                                @empty
                                    <div class="p-3 text-center text-xs text-slate-400">Nog geen vrienden toegevoegd</div>
                                @endforelse
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- CHANNELS LIST (GLOBAL / GANG) -->
            <template x-if="activeTab === 'global' || activeTab === 'gang'">
                <div class="p-3 space-y-2 flex-1 min-h-0 overflow-y-auto custom-scrollbar">
                    
                    <!-- KANAAL 1: KLINIEK PLEIN -->
                    <button type="button" 
                            @click="switchTab('global')"
                            :class="activeTab === 'global' 
                                ? 'bg-purple-50/90 dark:bg-purple-950/60 border-purple-400 dark:border-purple-700/80 shadow-xs ring-1 ring-purple-500/20' 
                                : 'bg-white dark:bg-slate-800/80 border-slate-200/80 dark:border-slate-700/70 hover:bg-slate-100 dark:hover:bg-slate-800'"
                            class="w-full p-3 rounded-2xl border text-left transition flex items-center gap-3 cursor-pointer group">
                        <div class="w-10 h-10 rounded-xl bg-purple-600 text-white flex items-center justify-center text-base shadow-sm shrink-0">
                            <i class="fa-solid fa-bullhorn"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between gap-1">
                                <span class="font-black text-xs text-slate-900 dark:text-white truncate">Kliniek Plein</span>
                                <span class="w-2 h-2 rounded-full bg-emerald-500 shrink-0" title="Openbaar Kanaal"></span>
                            </div>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400 truncate mt-0.5">Live openbare afdelingschat</p>
                        </div>
                    </button>

                    <!-- KANAAL 2: BENDE CHAT (PORTOFOON) -->
                    <button type="button" 
                            @click="switchTab('gang')"
                            :class="activeTab === 'gang' 
                                ? 'bg-amber-50/80 dark:bg-amber-950/40 border-amber-400 dark:border-amber-700/80 shadow-xs ring-1 ring-amber-500/20' 
                                : 'bg-white dark:bg-slate-800/80 border-slate-200/80 dark:border-slate-700/70 hover:bg-slate-100 dark:hover:bg-slate-800'"
                            class="w-full p-3 rounded-2xl border text-left transition flex items-center gap-3 cursor-pointer group">
                        <div class="w-10 h-10 rounded-xl bg-amber-500 text-slate-950 flex items-center justify-center text-base shadow-sm shrink-0">
                            <i class="fa-solid fa-walkie-talkie"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between gap-1">
                                <span class="font-black text-xs text-slate-900 dark:text-white truncate">
                                    @if($hasGang)
                                        [{{ $gangTag }}] {{ $gangName }}
                                    @else
                                        Bende Chat
                                    @endif
                                </span>
                                @if(!$hasGang)
                                    <span class="text-slate-400 text-xs shrink-0" title="Geen lid"><i class="fa-solid fa-lock"></i></span>
                                @elseif(!$gangHasChat)
                                    <span class="px-1.5 py-0.2 rounded bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 text-[9px] font-black shrink-0 border border-amber-200/80 dark:border-amber-800/70">500 ⭐</span>
                                @else
                                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse shrink-0" title="Portofoon actief"></span>
                                @endif
                            </div>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400 truncate mt-0.5">
                                @if(!$hasGang)
                                    Geen bende lidmaatschap
                                @elseif(!$gangHasChat)
                                    Portofoon vergrendeld (500 ⭐)
                                @else
                                    Besloten bendekanaal
                                @endif
                            </p>
                        </div>
                    </button>

                    <!-- PLEIN REGELS CARD -->
                    <div class="p-3.5 rounded-xl bg-purple-50/90 dark:bg-purple-950/50 border border-purple-200/80 dark:border-purple-800/60 text-xs space-y-2 mt-4">
                        <div class="flex items-center gap-2 text-purple-700 dark:text-purple-300 font-black">
                            <i class="fa-solid fa-shield-halved text-sm"></i>
                            <span>Plein & Bende Regels</span>
                        </div>
                        <p class="text-[11px] text-slate-600 dark:text-purple-200/80 leading-relaxed">
                            Houd het gezellig en respectvol. Geen spam, externe links of phishing. Klik op een avatar om direct een privégesprek te openen.
                        </p>
                    </div>

                    @if($hasGang)
                        <!-- GANG QUICK STATS CARD -->
                        <div class="p-3.5 rounded-xl bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70 text-xs space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="font-extrabold text-slate-800 dark:text-white flex items-center gap-1.5">
                                    <i class="fa-solid fa-users text-purple-500"></i>
                                    <span>{{ $gangName }}</span>
                                </span>
                                <span class="px-2 py-0.5 rounded-md bg-purple-50 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 border border-purple-200/80 dark:border-purple-800/70 font-black text-[10px]">
                                    [{{ $gangTag }}]
                                </span>
                            </div>
                            <div class="flex items-center justify-between text-[11px] text-slate-500 dark:text-slate-400">
                                <span>Bende Kluis:</span>
                                <span class="font-black text-amber-500">{{ $gangStars }} ⭐</span>
                            </div>
                            <a href="{{ route('gangs.show', $gangId) }}" class="block text-center py-1.5 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-black text-[11px] transition shadow-2xs">
                                Naar Bende Pagina &raquo;
                            </a>
                        </div>
                    @else
                        <!-- JOIN GANG CALLOUT -->
                        <div class="p-3.5 rounded-xl bg-amber-50/90 dark:bg-amber-950/50 border border-amber-200/80 dark:border-amber-800/60 text-xs space-y-2">
                            <div class="font-black text-amber-800 dark:text-amber-300 flex items-center gap-1.5">
                                <i class="fa-solid fa-users-viewfinder"></i>
                                <span>Word lid van een Bende</span>
                            </div>
                            <p class="text-[11px] text-slate-600 dark:text-amber-200/80 leading-relaxed">
                                Richt je eigen bende op of sluit je aan bij een factie om mee te vechten voor respect en de bendechat te ontgrendelen.
                            </p>
                            <a href="{{ route('gangs.index') }}" class="block text-center py-1.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-slate-950 font-black text-[11px] transition shadow-2xs">
                                Bekijk Bendes &raquo;
                            </a>
                        </div>
                    @endif

                </div>
            </template>

            <!-- DIRECT MESSAGES SEARCH & LIST -->
            <template x-if="activeTab === 'direct'">
                <div class="p-3 space-y-2 flex-1 min-h-0 overflow-y-auto custom-scrollbar">
                    <!-- QUICK SEARCH & INITIATE CHAT -->
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
                                   class="w-full bg-slate-50 dark:bg-slate-800/90 border border-slate-200 dark:border-slate-700 rounded-xl px-3.5 py-2 text-xs font-bold text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:border-purple-500 dark:focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition">
                            <template x-if="isSearching">
                                <i class="fa-solid fa-spinner fa-spin absolute right-3 top-2.5 text-xs text-purple-500"></i>
                            </template>
                        </div>

                        <!-- Search Dropdown -->
                        <template x-if="searchResults.length > 0">
                            <div class="absolute left-0 right-0 top-full mt-1.5 bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-xl shadow-xl z-50 max-h-56 overflow-y-auto divide-y divide-slate-100 dark:divide-slate-800 p-1">
                                <template x-for="p in searchResults" :key="p.id">
                                    <button type="button" 
                                            @click="selectPartner(p.id, p.name); searchResults = []; inmateQuery = '';"
                                            class="w-full p-2 rounded-lg hover:bg-purple-50 dark:hover:bg-purple-900/40 flex items-center justify-between text-left transition cursor-pointer">
                                        <div class="flex items-center gap-2 min-w-0">
                                            <img :src="p.avatar || '{{ asset('assets/characters/avatar_male_1.jpg') }}'" class="w-7 h-7 rounded-lg object-cover">
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
                                :class="activePartnerId === conv.partner_id ? 'bg-purple-50/90 dark:bg-purple-950/60 border-purple-300 dark:border-purple-700/60 shadow-xs ring-1 ring-purple-500/20' : 'bg-white dark:bg-slate-800/80 border-slate-200/80 dark:border-slate-700/70 hover:bg-slate-100 dark:hover:bg-slate-800'"
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
                                         class="w-11 h-11 rounded-xl object-cover border border-slate-200 dark:border-purple-800/50 shadow-2xs"
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

                    <template x-if="!loadingConversations && filteredConversations.length === 0">
                        <div class="p-8 text-center text-xs text-slate-400">
                            Geen actieve gesprekken gevonden.
                        </div>
                    </template>
                </div>
            </template>

        </div>

        <!-- ========================================== -->
        <!-- RIGHT COLUMN: MAIN ACTIVE CHAT WINDOW      -->
        <!-- ========================================== -->
        <div class="flex-1 flex flex-col h-full min-h-0 min-w-0 bg-slate-50/40 dark:bg-slate-900/60 overflow-hidden"
             :class="mobileView === 'list' ? 'hidden md:flex' : 'flex'">
            
            <!-- CHAT TOP BAR -->
            <div class="p-3.5 sm:p-4 border-b border-slate-200/90 dark:border-slate-800 flex items-center justify-between bg-white dark:bg-slate-900 shrink-0">
                <div class="flex items-center gap-3 min-w-0">
                    <!-- Mobile Back Button (Direct Chats) -->
                    <button type="button" 
                            x-show="activeTab === 'direct'"
                            @click="activePartnerId = null; mobileView = 'list'; if(window.tbsVibrate) window.tbsVibrate(10);"
                            class="md:hidden px-2.5 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 flex items-center gap-1.5 text-xs font-bold cursor-pointer shrink-0 border border-slate-200/80 dark:border-slate-700 shadow-2xs transition-all duration-150">
                        <i class="fa-solid fa-chevron-left text-[11px]"></i>
                        <span class="text-[11px]">Gesprekken</span>
                    </button>

                    <!-- GLOBAL HEADER -->
                    <template x-if="activeTab === 'global'">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-10 h-10 rounded-xl bg-purple-600 text-white flex items-center justify-center text-base shadow-sm shrink-0">
                                <i class="fa-solid fa-bullhorn"></i>
                            </div>
                            <div class="min-w-0">
                                <h3 class="text-xs sm:text-sm font-black text-slate-900 dark:text-white tracking-tight truncate">
                                    Kliniek Plein (Openbaar)
                                </h3>
                                <div class="text-[11px] text-emerald-600 dark:text-emerald-400 flex items-center gap-1 font-semibold">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                    <span>Realtime Chat met alle TBS'ers</span>
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- GANG HEADER -->
                    <template x-if="activeTab === 'gang'">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-10 h-10 rounded-xl bg-amber-500 text-slate-950 flex items-center justify-center text-base shadow-sm shrink-0">
                                <i class="fa-solid fa-walkie-talkie"></i>
                            </div>
                            <div class="min-w-0">
                                <h3 class="text-xs sm:text-sm font-black text-slate-900 dark:text-white tracking-tight truncate flex items-center gap-2">
                                    <span x-text="gangName + ' Portofoon'"></span>
                                    <template x-if="gangTag">
                                        <span class="px-1.5 py-0.2 rounded bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border border-amber-200/80 dark:border-amber-800/70 font-mono text-[9px] font-black" x-text="'[' + gangTag + ']'"></span>
                                    </template>
                                </h3>
                                <div class="text-[11px] text-amber-600 dark:text-amber-400 flex items-center gap-1 font-semibold truncate">
                                    <template x-if="hasGang && gangHasChat">
                                        <span class="flex items-center gap-1">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                            <span>Besloten & versleuteld bendekanaal</span>
                                        </span>
                                    </template>
                                    <template x-if="hasGang && !gangHasChat">
                                        <span class="text-amber-500 font-bold">🔒 Portofoon nog niet geactiveerd</span>
                                    </template>
                                    <template x-if="!hasGang">
                                        <span class="text-slate-400">🔒 Geen bende lidmaatschap</span>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- DIRECT HEADER -->
                    <template x-if="activeTab === 'direct'">
                        <div class="flex items-center gap-3 min-w-0">
                            <template x-if="activePartnerId === null">
                                <div class="w-10 h-10 rounded-xl bg-purple-50 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400 border border-purple-200/80 dark:border-purple-800/70 flex items-center justify-center text-lg shadow-2xs shrink-0">
                                    <i class="fa-solid fa-comments"></i>
                                </div>
                            </template>
                            <template x-if="activePartnerId !== null && activePartner.is_bot">
                                <div class="w-10 h-10 rounded-xl bg-amber-500/10 text-amber-500 border border-amber-300 dark:border-amber-700/60 flex items-center justify-center text-lg shadow-2xs shrink-0">
                                    <i class="fa-solid fa-building-shield"></i>
                                </div>
                            </template>
                            <template x-if="activePartnerId !== null && !activePartner.is_bot">
                                <img :src="activePartner.avatar" 
                                     class="w-10 h-10 rounded-xl object-cover border border-slate-200 dark:border-purple-800/50 shadow-2xs shrink-0"
                                     onerror="this.src='{{ asset('assets/characters/avatar_male_1.jpg') }}'">
                            </template>
                            <div class="min-w-0">
                                <h3 class="text-xs sm:text-sm font-black text-slate-900 dark:text-white tracking-tight flex items-center gap-1.5 truncate">
                                    <span x-text="activePartnerId === null ? 'Privéberichten' : activePartner.name"></span>
                                    <template x-if="activePartnerId !== null && activePartner.is_bot">
                                        <span class="px-1.5 py-0.2 rounded bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border border-amber-200/80 dark:border-amber-800/70 text-[9px] font-black uppercase">Systeem Bot</span>
                                    </template>
                                </h3>
                                <div class="text-[11px] text-slate-500 dark:text-slate-400 truncate" x-text="activePartnerId === null ? 'Selecteer links een gesprek' : activePartner.status_label"></div>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- RIGHT HEADER ACTIONS -->
                <div class="flex items-center gap-2 shrink-0">
                    <template x-if="activeTab === 'direct' && activePartnerId !== null && !activePartner.is_bot && activePartner.id > 0">
                        <a :href="'/speler/' + encodeURIComponent(activePartner.name)" 
                           class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 border border-slate-200/80 dark:border-slate-700 text-xs font-bold transition flex items-center gap-1.5">
                            <i class="fa-solid fa-address-card text-xs"></i>
                            <span class="hidden sm:inline">Dossier</span>
                        </a>
                    </template>
                    <button type="button" 
                            @click="refreshActiveChat()" 
                            class="w-8 h-8 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 border border-slate-200/80 dark:border-slate-700 flex items-center justify-center text-xs transition cursor-pointer"
                            title="Handmatig verversen">
                        <i class="fa-solid fa-arrows-rotate" :class="isRefreshing ? 'animate-spin' : ''"></i>
                    </button>
                </div>
            </div>

            <!-- CHAT STREAM CONTAINER (CONSTRAINED WITH AUTO-SCROLL) -->
            <div id="tbsChatScrollContainer" 
                 class="flex-1 min-h-0 overflow-y-auto overflow-x-hidden max-w-full p-4 sm:p-5 space-y-3.5 custom-scrollbar">
                
                <!-- =============================== -->
                <!-- 1. PUBLIC CHAT MESSAGES STREAM  -->
                <!-- =============================== -->
                <template x-if="activeTab === 'global'">
                    <div class="space-y-3">
                        <template x-for="msg in publicMessagesList" :key="msg.id">
                            <div class="flex items-start gap-2.5 sm:gap-3 group min-w-0 max-w-full" :class="msg.is_me ? 'flex-row-reverse' : ''">
                                <!-- AVATAR -->
                                <button type="button" 
                                        @click="if(!msg.is_me) { switchTab('direct'); selectPartner(msg.character_id, msg.name); }"
                                        class="shrink-0 cursor-pointer transition hover:scale-105"
                                        :title="!msg.is_me ? 'Klik om privé te chatten met ' + msg.name : ''">
                                    <img :src="msg.avatar || '{{ asset('assets/characters/avatar_male_1.jpg') }}'" 
                                         class="w-8 h-8 sm:w-9 sm:h-9 rounded-xl object-cover border border-slate-200 dark:border-purple-800/60 shadow-2xs"
                                         onerror="this.src='{{ asset('assets/characters/avatar_male_1.jpg') }}'">
                                </button>

                                <!-- MESSAGE BUBBLE & META -->
                                <div class="max-w-[85%] sm:max-w-lg min-w-0 space-y-1" :class="msg.is_me ? 'text-right' : 'text-left'">
                                    
                                    <!-- SENDER META HEADER -->
                                    <div class="flex items-center gap-1.5 text-[11px] font-bold flex-wrap min-w-0 max-w-full" :class="msg.is_me ? 'justify-end' : ''">
                                        <!-- EIGENAAR STYLING -->
                                        <template x-if="msg.is_owner">
                                            <span class="font-black hover:underline cursor-pointer flex items-center gap-1"
                                                  @click="tagPlayer(msg.name)" title="Klik om te taggen">
                                                <i class="fa-solid fa-crown text-[10px] text-amber-500"></i>
                                                <span :class="msg.name_classes ? msg.name_classes : 'text-amber-500 dark:text-amber-400'"
                                                      :style="msg.name_style || ''"
                                                      x-text="msg.name"></span>
                                                <span class="px-1.5 py-0.2 rounded bg-amber-500 text-slate-950 text-[8px] font-black uppercase">Eigenaar</span>
                                            </span>
                                        </template>

                                        <!-- DIRECTIE / ADMIN STYLING -->
                                        <template x-if="msg.is_admin && !msg.is_owner">
                                            <span class="font-black hover:underline cursor-pointer flex items-center gap-1"
                                                  @click="tagPlayer(msg.name)" title="Klik om te taggen">
                                                <i class="fa-solid fa-shield-halved text-[10px] text-purple-500"></i>
                                                <span :class="msg.name_classes ? msg.name_classes : 'text-purple-600 dark:text-purple-300'"
                                                      :style="msg.name_style || ''"
                                                      x-text="msg.name"></span>
                                                <span class="px-1.5 py-0.2 rounded bg-purple-600 text-white text-[8px] font-black uppercase">Directie</span>
                                            </span>
                                        </template>

                                        <!-- MODERATOR STYLING -->
                                        <template x-if="msg.is_moderator && !msg.is_admin && !msg.is_owner">
                                            <span class="font-black hover:underline cursor-pointer flex items-center gap-1"
                                                  @click="tagPlayer(msg.name)" title="Klik om te taggen">
                                                <i class="fa-solid fa-gavel text-[10px] text-cyan-500"></i>
                                                <span :class="msg.name_classes ? msg.name_classes : 'text-cyan-600 dark:text-cyan-400'"
                                                      :style="msg.name_style || ''"
                                                      x-text="msg.name"></span>
                                                <span class="px-1.5 py-0.2 rounded bg-cyan-600 text-white text-[8px] font-black uppercase">Moderator</span>
                                            </span>
                                        </template>

                                        <!-- REGULAR INMATE / VIP -->
                                        <template x-if="!msg.is_owner && !msg.is_admin && !msg.is_moderator">
                                            <span class="font-extrabold hover:underline cursor-pointer"
                                                  :class="msg.name_classes ? msg.name_classes : (msg.is_vip ? 'text-amber-500 dark:text-amber-400' : 'text-slate-900 dark:text-white')"
                                                  :style="msg.name_style || (msg.name_color && !msg.has_sparkle ? 'color: ' + msg.name_color : '')"
                                                  @click="tagPlayer(msg.name)" title="Klik om te taggen"
                                                  x-text="msg.name"></span>
                                        </template>

                                        <!-- SPARKLE ICON -->
                                        <template x-if="msg.has_sparkle">
                                            <i class="fa-solid fa-wand-magic-sparkles text-[9px] text-amber-400 animate-pulse" title="VIP Glitter Shimmer"></i>
                                        </template>

                                        <!-- VIP BADGE PILL -->
                                        <template x-if="msg.is_vip && msg.vip_badge_label">
                                            <span class="px-1.5 py-0.2 rounded text-[8px] font-black uppercase border inline-flex items-center gap-1 shrink-0"
                                                  :class="msg.vip_badge_class || 'bg-amber-400 text-slate-950 border-amber-300'"
                                                  :title="msg.vip_badge_full || 'VIP Lid'">
                                                <i class="fa-solid text-[8px]" :class="msg.vip_badge_icon || 'fa-crown'"></i>
                                                <span x-text="msg.vip_badge_label"></span>
                                            </span>
                                        </template>

                                        <!-- LEVEL PILL -->
                                        <span class="px-1.5 py-0.2 rounded bg-slate-200 dark:bg-purple-950 text-slate-700 dark:text-purple-300 text-[9px] font-black">
                                            L<span x-text="msg.level"></span>
                                        </span>

                                        <!-- RESPECT PILL -->
                                        <span class="px-1.5 py-0.2 rounded bg-amber-100 dark:bg-amber-950/70 text-amber-800 dark:text-amber-300 text-[9px] font-black flex items-center gap-0.5">
                                            <i class="fa-solid fa-fire text-amber-500 text-[8px]"></i>
                                            <span x-text="msg.respect"></span>
                                        </span>

                                        <!-- GANG TAG -->
                                        <template x-if="msg.gang_tag">
                                            <span class="px-1.5 py-0.2 rounded bg-purple-100 dark:bg-purple-900/50 text-purple-700 dark:text-purple-300 text-[9px] font-black" x-text="'[' + msg.gang_tag + ']'"></span>
                                        </template>

                                        <!-- TIME -->
                                        <span class="text-slate-400 text-[10px]" x-text="msg.time"></span>

                                        <!-- REAGEER KNOP -->
                                        <button type="button" @click="setReplyTo(msg)" title="Reageer op dit bericht" class="text-slate-400 hover:text-purple-500 transition px-0.5 text-[10px] cursor-pointer">
                                            <i class="fa-solid fa-reply"></i>
                                        </button>

                                        <!-- DELETE (MODERATOR / STAFF / AUTHOR) -->
                                        <template x-if="canModerate || msg.is_me">
                                            <button type="button" @click="deletePublicMessage(msg.id)" :title="msg.is_me ? 'Verwijder je eigen bericht' : 'Verwijder bericht (Moderator)'" class="text-slate-400 hover:text-rose-500 transition px-1 py-0.5 text-[11px] cursor-pointer inline-flex items-center">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>
                                        </template>

                                        <!-- REPORT -->
                                        <template x-if="!msg.is_me">
                                            <button type="button" @click="window.tbsOpenReportModal ? window.tbsOpenReportModal({ type: 'chat_message', id: msg.id, name: msg.name, text: msg.message }) : null" class="opacity-70 sm:opacity-0 sm:group-hover:opacity-100 text-slate-400 hover:text-red-500 transition px-0.5 text-[10px] cursor-pointer" title="Rapporteer">
                                                <i class="fa-solid fa-flag"></i>
                                            </button>
                                        </template>
                                    </div>

                                    <!-- REPLY QUOTE BUBBLE -->
                                    <template x-if="msg.reply_to_name">
                                        <div class="text-[11px] text-purple-700 dark:text-purple-300 bg-purple-100/70 dark:bg-purple-950/60 px-2.5 py-1 rounded-xl border-l-2 border-purple-500 mb-1 flex items-center gap-1.5 truncate max-w-full min-w-0"
                                             :class="msg.is_me ? 'ml-auto' : ''">
                                            <i class="fa-solid fa-reply text-[9px] text-purple-500"></i>
                                            <span class="font-extrabold text-purple-800 dark:text-purple-200 shrink-0" x-text="'@' + msg.reply_to_name"></span>
                                            <span class="truncate opacity-80 italic" x-text="msg.reply_to_snippet"></span>
                                        </div>
                                    </template>

                                    <!-- BUBBLE & HEART REACTION (COLORS IDENTICAL TO MINI CHAT) -->
                                    <div class="flex items-center gap-1.5 min-w-0 max-w-full" :class="msg.is_me ? 'flex-row-reverse' : ''">
                                        <div class="inline-block px-3.5 py-2 rounded-2xl text-xs sm:text-sm leading-relaxed shadow-2xs break-words [overflow-wrap:anywhere] [word-break:break-word] whitespace-pre-line max-w-full"
                                             :class="msg.is_me 
                                                ? 'bg-purple-600 text-white rounded-tr-xs text-left' 
                                                : (msg.is_owner 
                                                    ? 'bg-amber-50 dark:bg-amber-950/60 border border-amber-300 dark:border-amber-700/60 text-slate-900 dark:text-amber-100 rounded-tl-xs text-left'
                                                    : (msg.is_admin || msg.is_moderator 
                                                        ? 'bg-purple-50/70 dark:bg-purple-950/60 border border-purple-200 dark:border-purple-800/60 text-slate-900 dark:text-purple-100 rounded-tl-xs text-left'
                                                        : 'bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 rounded-tl-xs border border-slate-200/90 dark:border-slate-700 text-left'))"
                                             x-html="formatMessage(msg.message, msg.is_me)"></div>

                                        <!-- PAARS HARTJE REACTIE KNOP 💜 -->
                                        <button type="button" @click="togglePublicHeart(msg)" 
                                                :title="msg.has_hearted ? 'Paars hartje intrekken' : 'Paars hartje geven (💜)'"
                                                class="shrink-0 p-1.5 rounded-full text-xs transition cursor-pointer flex items-center gap-1"
                                                :class="msg.has_hearted ? 'text-purple-400 scale-110 font-black' : 'text-slate-400/70 hover:text-purple-400'">
                                            <span>💜</span>
                                            <span x-show="msg.hearts_count > 0" x-text="msg.hearts_count" class="text-[10px] font-black font-mono"></span>
                                        </button>
                                    </div>

                                </div>
                            </div>
                        </template>

                        <template x-if="publicMessagesList.length === 0">
                            <div class="py-16 text-center text-slate-400 text-xs">
                                <i class="fa-solid fa-comments text-3xl mb-2 block opacity-40"></i>
                                Nog geen berichten op het Kliniek Plein. Start als eerste het gesprek!
                            </div>
                        </template>
                    </div>
                </template>

                <!-- =============================== -->
                <!-- 2. BENDE CHAT (GANG PORTOFOON)  -->
                <!-- =============================== -->
                <template x-if="activeTab === 'gang'">
                    <div class="h-full flex flex-col min-h-0">
                        
                        <!-- SCENARIO A: NOT IN A GANG -->
                        <template x-if="!hasGang">
                            <div class="flex-1 flex flex-col items-center justify-center p-8 text-center space-y-4 my-auto">
                                <div class="w-16 h-16 rounded-2xl bg-purple-50 dark:bg-purple-950/60 text-purple-600 dark:text-purple-300 border border-purple-200/80 dark:border-purple-800/70 flex items-center justify-center text-3xl shadow-sm">
                                    <i class="fa-solid fa-users-slash"></i>
                                </div>
                                <div class="space-y-1.5 max-w-sm">
                                    <h4 class="text-base font-black text-slate-900 dark:text-white">Geen Bende Lidmaatschap</h4>
                                    <p class="text-xs text-slate-500 dark:text-purple-300/80 leading-relaxed">
                                        Sluit je aan bij een bestaande bende of richt je eigen factie op om de besloten bende chat en gezamenlijke kluis te gebruiken.
                                    </p>
                                </div>
                                <a href="{{ route('gangs.index') }}" class="px-5 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-700 text-white text-xs font-black shadow-md transition flex items-center gap-2">
                                    <i class="fa-solid fa-users"></i>
                                    <span>Bekijk Bendes &raquo;</span>
                                </a>
                            </div>
                        </template>

                        <!-- SCENARIO B: IN GANG, BUT CHAT NOT UNLOCKED (500 STARS) -->
                        <template x-if="hasGang && !gangHasChat">
                            <div class="flex-1 flex flex-col items-center justify-center p-8 text-center space-y-4 max-w-md mx-auto my-auto">
                                <div class="w-16 h-16 rounded-2xl bg-amber-50 dark:bg-amber-950/60 text-amber-500 border border-amber-200/80 dark:border-amber-700/60 flex items-center justify-center text-3xl shadow-lg">
                                    <i class="fa-solid fa-walkie-talkie"></i>
                                </div>
                                <div class="space-y-1.5">
                                    <span class="px-2.5 py-0.5 rounded-full bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 text-[10px] font-black uppercase tracking-wider border border-amber-200/80 dark:border-amber-800/70">
                                        🔒 Exclusief Kanaal
                                    </span>
                                    <h4 class="text-base font-black text-slate-900 dark:text-white" x-text="gangName + ' Portofoon'"></h4>
                                    <p class="text-xs text-slate-600 dark:text-purple-200 leading-relaxed">
                                        Jullie bende heeft de eigen besloten Gang Chat nog niet geactiveerd. Deze chat is 100% besloten en beveiligd voor leden van <span class="font-bold" x-text="gangName"></span>.
                                    </p>
                                </div>

                                <div class="p-3 bg-slate-50/90 dark:bg-slate-800/60 rounded-xl border border-slate-200/80 dark:border-slate-700/70 text-xs text-slate-900 dark:text-slate-100 w-full space-y-1.5">
                                    <div class="flex items-center justify-between font-bold">
                                        <span>Eenmalige Kosten:</span>
                                        <span class="text-amber-500 font-black">500 Sterren ⭐</span>
                                    </div>
                                    <div class="flex items-center justify-between text-[11px] text-slate-500 dark:text-slate-400">
                                        <span>Jouw saldo:</span>
                                        <span class="font-bold" x-text="characterStars + ' ⭐'"></span>
                                    </div>
                                    <div class="flex items-center justify-between text-[11px] text-slate-500 dark:text-slate-400">
                                        <span>Bende kluis saldo:</span>
                                        <span class="font-bold" x-text="gangStars + ' ⭐'"></span>
                                    </div>
                                </div>

                                <form action="{{ route('gangs.shop-buy') }}" method="POST" class="w-full">
                                    @csrf
                                    <input type="hidden" name="item_type" value="gang_chat">
                                    <input type="hidden" name="quantity" value="1">
                                    <button type="submit" :disabled="characterStars < 500 && gangStars < 500" class="w-full py-2.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-slate-950 font-black text-xs shadow-md transition disabled:opacity-50 cursor-pointer flex items-center justify-center gap-1.5">
                                        <i class="fa-solid fa-cart-shopping"></i>
                                        <span>Koop Bende Chat (500 ⭐)</span>
                                    </button>
                                </form>
                            </div>
                        </template>

                        <!-- SCENARIO C: IN GANG & CHAT UNLOCKED -->
                        <template x-if="hasGang && gangHasChat">
                            <div class="space-y-3">
                                <template x-for="msg in gangMessagesList" :key="msg.id">
                                    <div class="flex items-start gap-2.5 sm:gap-3 group min-w-0 max-w-full" :class="msg.is_me ? 'flex-row-reverse' : ''">
                                        <!-- AVATAR -->
                                        <button type="button" 
                                                @click="if(!msg.is_me) { switchTab('direct'); selectPartner(msg.character_id, msg.name); }"
                                                class="shrink-0 cursor-pointer transition hover:scale-105"
                                                :title="!msg.is_me ? 'Klik om privé te chatten met ' + msg.name : ''">
                                            <img :src="msg.avatar || '{{ asset('assets/characters/avatar_male_1.jpg') }}'" 
                                                 class="w-8 h-8 sm:w-9 sm:h-9 rounded-xl object-cover border border-slate-200 dark:border-purple-800/60 shadow-2xs"
                                                 onerror="this.src='{{ asset('assets/characters/avatar_male_1.jpg') }}'">
                                        </button>

                                        <!-- MESSAGE BUBBLE & META -->
                                        <div class="max-w-[85%] sm:max-w-lg min-w-0 space-y-1" :class="msg.is_me ? 'text-right' : 'text-left'">
                                            
                                            <!-- SENDER META HEADER -->
                                            <div class="flex items-center gap-1.5 text-[11px] font-bold flex-wrap min-w-0 max-w-full" :class="msg.is_me ? 'justify-end' : ''">
                                                <!-- EIGENAAR STYLING -->
                                                <template x-if="msg.is_owner">
                                                    <span class="font-black hover:underline cursor-pointer flex items-center gap-1"
                                                          @click="tagPlayer(msg.name)" title="Klik om te taggen">
                                                        <i class="fa-solid fa-crown text-[10px] text-amber-500"></i>
                                                        <span :class="msg.name_classes ? msg.name_classes : 'text-amber-500 dark:text-amber-400'"
                                                              :style="msg.name_style || ''"
                                                              x-text="msg.name"></span>
                                                        <span class="px-1.5 py-0.2 rounded bg-amber-500 text-slate-950 text-[8px] font-black uppercase">Eigenaar</span>
                                                    </span>
                                                </template>

                                                <!-- DIRECTIE / ADMIN STYLING -->
                                                <template x-if="msg.is_admin && !msg.is_owner">
                                                    <span class="font-black hover:underline cursor-pointer flex items-center gap-1"
                                                          @click="tagPlayer(msg.name)" title="Klik om te taggen">
                                                        <i class="fa-solid fa-shield-halved text-[10px] text-purple-500"></i>
                                                        <span :class="msg.name_classes ? msg.name_classes : 'text-purple-600 dark:text-purple-300'"
                                                              :style="msg.name_style || ''"
                                                              x-text="msg.name"></span>
                                                        <span class="px-1.5 py-0.2 rounded bg-purple-600 text-white text-[8px] font-black uppercase">Directie</span>
                                                    </span>
                                                </template>

                                                <!-- MODERATOR STYLING -->
                                                <template x-if="msg.is_moderator && !msg.is_admin && !msg.is_owner">
                                                    <span class="font-black hover:underline cursor-pointer flex items-center gap-1"
                                                          @click="tagPlayer(msg.name)" title="Klik om te taggen">
                                                        <i class="fa-solid fa-gavel text-[10px] text-cyan-500"></i>
                                                        <span :class="msg.name_classes ? msg.name_classes : 'text-cyan-600 dark:text-cyan-400'"
                                                              :style="msg.name_style || ''"
                                                              x-text="msg.name"></span>
                                                        <span class="px-1.5 py-0.2 rounded bg-cyan-600 text-white text-[8px] font-black uppercase">Moderator</span>
                                                    </span>
                                                </template>

                                                <!-- REGULAR MEMBER / VIP -->
                                                <template x-if="!msg.is_owner && !msg.is_admin && !msg.is_moderator">
                                                    <span class="font-extrabold hover:underline cursor-pointer"
                                                          :class="msg.name_classes ? msg.name_classes : (msg.is_vip ? 'text-amber-500 dark:text-amber-400' : 'text-slate-900 dark:text-white')"
                                                          :style="msg.name_style || (msg.name_color && !msg.has_sparkle ? 'color: ' + msg.name_color : '')"
                                                          @click="tagPlayer(msg.name)" title="Klik om te taggen"
                                                          x-text="msg.name"></span>
                                                </template>

                                                <!-- SPARKLE ICON -->
                                                <template x-if="msg.has_sparkle">
                                                    <i class="fa-solid fa-wand-magic-sparkles text-[9px] text-amber-400 animate-pulse" title="VIP Glitter Shimmer"></i>
                                                </template>

                                                <!-- VIP BADGE PILL -->
                                                <template x-if="msg.is_vip && msg.vip_badge_label">
                                                    <span class="px-1.5 py-0.2 rounded text-[8px] font-black uppercase border inline-flex items-center gap-1 shrink-0"
                                                          :class="msg.vip_badge_class || 'bg-amber-400 text-slate-950 border-amber-300'"
                                                          :title="msg.vip_badge_full || 'VIP Lid'">
                                                        <i class="fa-solid text-[8px]" :class="msg.vip_badge_icon || 'fa-crown'"></i>
                                                        <span x-text="msg.vip_badge_label"></span>
                                                    </span>
                                                </template>

                                                <!-- LEVEL PILL -->
                                                <span class="px-1.5 py-0.2 rounded bg-slate-200 dark:bg-purple-950 text-slate-700 dark:text-purple-300 text-[9px] font-black">
                                                    L<span x-text="msg.level"></span>
                                                </span>

                                                <!-- RESPECT PILL -->
                                                <span class="px-1.5 py-0.2 rounded bg-amber-100 dark:bg-amber-950/70 text-amber-800 dark:text-amber-300 text-[9px] font-black flex items-center gap-0.5">
                                                    <i class="fa-solid fa-fire text-amber-500 text-[8px]"></i>
                                                    <span x-text="msg.respect"></span>
                                                </span>

                                                <!-- GANG TAG -->
                                                <template x-if="msg.gang_tag">
                                                    <span class="px-1.5 py-0.2 rounded bg-amber-100 dark:bg-amber-950 text-amber-700 dark:text-amber-300 text-[9px] font-black" x-text="'[' + msg.gang_tag + ']'"></span>
                                                </template>

                                                <!-- TIME -->
                                                <span class="text-slate-400 text-[10px]" x-text="msg.time"></span>

                                                <!-- REAGEER KNOP -->
                                                <button type="button" @click="setReplyTo(msg)" title="Reageer op dit bericht" class="text-slate-400 hover:text-purple-500 transition px-0.5 text-[10px] cursor-pointer">
                                                    <i class="fa-solid fa-reply"></i>
                                                </button>

                                                <!-- DELETE (MODERATOR / STAFF / AUTHOR) -->
                                                <template x-if="canModerate || msg.is_me">
                                                    <button type="button" @click="deletePublicMessage(msg.id)" :title="msg.is_me ? 'Verwijder je eigen bericht' : 'Verwijder bericht (Moderator)'" class="text-slate-400 hover:text-rose-500 transition px-1 py-0.5 text-[11px] cursor-pointer inline-flex items-center">
                                                        <i class="fa-solid fa-trash-can"></i>
                                                    </button>
                                                </template>

                                                <!-- REPORT -->
                                                <template x-if="!msg.is_me">
                                                    <button type="button" @click="window.tbsOpenReportModal ? window.tbsOpenReportModal({ type: 'chat_message', id: msg.id, name: msg.name, text: msg.message }) : null" class="opacity-70 sm:opacity-0 sm:group-hover:opacity-100 text-slate-400 hover:text-red-500 transition px-0.5 text-[10px] cursor-pointer" title="Rapporteer">
                                                        <i class="fa-solid fa-flag"></i>
                                                    </button>
                                                </template>
                                            </div>

                                            <!-- REPLY QUOTE BUBBLE -->
                                            <template x-if="msg.reply_to_name">
                                                <div class="text-[11px] text-amber-700 dark:text-amber-300 bg-amber-100/70 dark:bg-amber-950/60 px-2.5 py-1 rounded-xl border-l-2 border-amber-500 mb-1 flex items-center gap-1.5 truncate max-w-full min-w-0"
                                                     :class="msg.is_me ? 'ml-auto' : ''">
                                                    <i class="fa-solid fa-reply text-[9px] text-amber-500"></i>
                                                    <span class="font-extrabold text-amber-800 dark:text-amber-200 shrink-0" x-text="'@' + msg.reply_to_name"></span>
                                                    <span class="truncate opacity-80 italic" x-text="msg.reply_to_snippet"></span>
                                                </div>
                                            </template>

                                            <!-- BUBBLE & HEART REACTION -->
                                            <div class="flex items-center gap-1.5 min-w-0 max-w-full" :class="msg.is_me ? 'flex-row-reverse' : ''">
                                                <div class="inline-block px-3.5 py-2 rounded-2xl text-xs sm:text-sm leading-relaxed shadow-2xs break-words [overflow-wrap:anywhere] [word-break:break-word] whitespace-pre-line max-w-full"
                                                     :class="msg.is_me 
                                                        ? 'bg-purple-600 text-white rounded-tr-xs text-left' 
                                                        : 'bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 rounded-tl-xs border border-slate-200/90 dark:border-slate-700 text-left'"
                                                     x-html="formatMessage(msg.message, msg.is_me)"></div>

                                                <!-- PAARS HARTJE REACTIE KNOP 💜 -->
                                                <button type="button" @click="togglePublicHeart(msg)" 
                                                        :title="msg.has_hearted ? 'Paars hartje intrekken' : 'Paars hartje geven (💜)'"
                                                        class="shrink-0 p-1.5 rounded-full text-xs transition cursor-pointer flex items-center gap-1"
                                                        :class="msg.has_hearted ? 'text-purple-400 scale-110 font-black' : 'text-slate-400/70 hover:text-purple-400'">
                                            <span>💜</span>
                                            <span x-show="msg.hearts_count > 0" x-text="msg.hearts_count" class="text-[10px] font-black font-mono"></span>
                                        </button>
                                    </div>

                                </div>
                            </div>
                        </template>

                        <template x-if="gangMessagesList.length === 0">
                            <div class="py-16 text-center text-slate-400 text-xs">
                                <i class="fa-solid fa-walkie-talkie text-3xl mb-2 block opacity-40"></i>
                                Nog geen berichten in de bende portofoon. Bespreek strategieën en overvallen met je bendeleden!
                            </div>
                        </template>
                    </div>
                </template>
                    </div>
                </template>

            <!-- =============================== -->
            <!-- 3. DIRECT MESSAGES STREAM       -->
            <!-- =============================== -->
            <template x-if="activeTab === 'direct'">
                <div class="space-y-3">
                    <template x-if="activePartnerId === null">
                        <div class="flex-1 flex flex-col items-center justify-center py-20 text-center space-y-3 max-w-sm mx-auto">
                            <div class="w-16 h-16 rounded-2xl bg-purple-50 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400 border border-purple-200/80 dark:border-purple-800/70 flex items-center justify-center text-2xl shadow-sm">
                                <i class="fa-solid fa-comments"></i>
                            </div>
                            <div class="space-y-1">
                                <h4 class="text-sm font-black text-slate-900 dark:text-white">Geen gesprek geselecteerd</h4>
                                <p class="text-xs text-slate-500 dark:text-purple-300/80 leading-relaxed">
                                    Kies links een gesprek uit de lijst of zoek een medepatiënt om een nieuw privégesprek te starten.
                                </p>
                            </div>
                        </div>
                    </template>

                    <template x-if="activePartnerId !== null">
                        <div class="space-y-3">
                            <template x-for="dm in directMessagesList" :key="dm.id">
                                <div class="flex items-start gap-2.5 sm:gap-3 min-w-0 max-w-full" :class="dm.is_me ? 'flex-row-reverse' : ''">
                                    <img :src="dm.sender_avatar" 
                                         class="w-8 h-8 sm:w-9 sm:h-9 rounded-xl object-cover border border-slate-200 dark:border-purple-800/50 shadow-2xs shrink-0"
                                         onerror="this.src='{{ asset('assets/characters/avatar_male_1.jpg') }}'">
                                    <div class="max-w-[85%] sm:max-w-lg min-w-0 space-y-1" :class="dm.is_me ? 'text-right' : 'text-left'">
                                        <div class="flex items-center gap-1.5 text-[11px] font-bold" :class="dm.is_me ? 'justify-end' : ''">
                                            <span class="text-slate-900 dark:text-white font-extrabold" x-text="dm.sender_name"></span>
                                            <span class="text-slate-400 text-[10px]" x-text="dm.created_at"></span>
                                            <template x-if="canModerate || dm.is_me">
                                                <button type="button" @click="deleteDirectMessage(dm.id)" :title="dm.is_me ? 'Verwijder je bericht' : 'Verwijder bericht (Moderator)'" class="text-slate-400 hover:text-rose-500 transition px-1 py-0.5 text-[10px] cursor-pointer inline-flex items-center">
                                                    <i class="fa-solid fa-trash-can"></i>
                                                </button>
                                            </template>
                                        </div>
                                        <div class="inline-block px-3.5 py-2 rounded-2xl text-xs sm:text-sm leading-relaxed shadow-2xs break-words [overflow-wrap:anywhere] [word-break:break-word] whitespace-pre-line max-w-full"
                                             :class="dm.is_me ? 'bg-purple-600 text-white rounded-tr-xs text-left' : 'bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 rounded-tl-xs border border-slate-200/90 dark:border-slate-700 text-left'"
                                             x-html="formatMessage(dm.message, dm.is_me)"></div>
                                    </div>
                                </div>
                            </template>

                            <template x-if="directMessagesList.length === 0">
                                <div class="py-16 text-center text-slate-400 text-xs">
                                    Geen berichten in dit gesprek. Stuur een bericht om te beginnen!
                                </div>
                            </template>
                        </div>
                    </template>
                </div>
            </template>

        </div>

        <!-- CHAT INPUT BAR (GLOBAL / GANG / DIRECT) -->
        <div class="p-3 sm:p-4 bg-white dark:bg-slate-900 border-t border-slate-200/90 dark:border-slate-800 shrink-0 space-y-2">
            
            <!-- Disable input on direct chat when no partner selected -->
            <template x-if="activeTab === 'direct' && activePartnerId === null">
                <div class="text-center py-2 text-xs text-slate-400 italic">
                    Selecteer eerst een gesprek uit de lijst om berichten te sturen.
                </div>
            </template>

            <!-- Disable input on locked gang states -->
            <template x-if="activeTab === 'gang' && (!hasGang || !gangHasChat)">
                <div class="text-center py-2 text-xs text-slate-400 italic">
                    <span x-text="!hasGang ? 'Sluit je aan bij een bende om berichten te sturen.' : 'Ontgrendel de portofoon om berichten te sturen.'"></span>
                </div>
            </template>

            <template x-if="activeTab === 'global' || (activeTab === 'direct' && activePartnerId !== null) || (activeTab === 'gang' && hasGang && gangHasChat)">
                <div class="space-y-2">
                    
                    <!-- REPLY QUOTE PREVIEW BANNER -->
                    <template x-if="replyingTo">
                        <div class="flex items-center justify-between bg-purple-50 dark:bg-purple-950/80 px-3 py-1.5 rounded-xl border border-purple-200 dark:border-purple-800 text-[11px] text-purple-900 dark:text-purple-200">
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

                    <!-- QUICK EMOJI TOOLBAR -->
                    <div class="flex items-center gap-1 overflow-x-auto pb-1 text-sm select-none custom-scrollbar">
                        <template x-for="s in ['💜','🔥','😂','👑','💀','🔫','💊','💉','💰','👊','👀','💯','🚨','⭐']" :key="s">
                            <button type="button" 
                                    @click="insertEmoji(s)" 
                                    class="w-7 h-7 flex items-center justify-center rounded-lg hover:bg-slate-100 dark:hover:bg-purple-950/60 transition cursor-pointer shrink-0" 
                                    :title="'Voeg ' + s + ' toe'">
                                <span x-text="s"></span>
                            </button>
                        </template>
                    </div>

                    <!-- INPUT FORM WITH EMOJI DRAWER & MENTION AUTOCOMPLETE -->
                    <form @submit.prevent="sendMessage()" data-no-async="true" class="flex items-center gap-2 relative">
                        
                        <!-- EMOJI DRAWER TOGGLE BUTTON & POPUP -->
                        <div class="relative">
                            <button type="button" @click="showEmojiPicker = !showEmojiPicker" class="w-9 h-9 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 border border-slate-200/80 dark:border-slate-700 flex items-center justify-center text-sm transition cursor-pointer" title="Smileys & Emojis">
                                <i class="fa-regular fa-face-smile"></i>
                            </button>

                            <!-- Emoji Drawer Popup -->
                            <div x-show="showEmojiPicker" @click.outside="showEmojiPicker = false" x-cloak
                                 class="absolute bottom-full left-0 mb-3 w-64 max-w-[calc(100vw-2rem)] bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-3 shadow-2xl z-50 space-y-2.5">
                                <div class="flex items-center justify-between pb-1 border-b border-slate-100 dark:border-slate-800">
                                    <span class="text-[10px] font-black uppercase tracking-wider text-slate-400">Smileys & Misdaad</span>
                                    <button type="button" @click="showEmojiPicker = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-white text-xs">&times;</button>
                                </div>
                                <div class="space-y-2 max-h-48 overflow-y-auto pr-1 custom-scrollbar">
                                    <div>
                                        <span class="text-[9px] font-bold text-slate-400 block mb-0.5">Gezichten</span>
                                        <div class="grid grid-cols-6 gap-1">
                                            <template x-for="s in ['😀','😂','🤣','😎','😍','🤫','😡','💀','🤡','😈','😱','🥳']" :key="s">
                                                <button type="button" @click="insertEmoji(s)" class="h-7 rounded hover:bg-purple-100 dark:hover:bg-purple-900/50 text-sm flex items-center justify-center transition cursor-pointer" x-text="s"></button>
                                            </template>
                                        </div>
                                    </div>
                                    <div>
                                        <span class="text-[9px] font-bold text-slate-400 block mb-0.5">TBS & Misdaad</span>
                                        <div class="grid grid-cols-6 gap-1">
                                            <template x-for="s in ['🔥','💰','💉','🚬','🔪','💣','💊','🩸','⛓️','🔒','🥊']" :key="s">
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

                        <!-- MENTION AUTOCOMPLETE DROPDOWN (Above Chat Input) -->
                        <div x-show="showMentionDropdown && mentionUsers.length > 0"
                             @click.away="showMentionDropdown = false"
                             x-cloak
                             class="absolute bottom-full left-0 mb-3 w-72 sm:w-80 max-w-[calc(100vw-2rem)] bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl shadow-2xl z-50 overflow-hidden divide-y divide-slate-100 dark:divide-slate-800">
                            
                            <div class="px-3.5 py-2 bg-slate-50/90 dark:bg-slate-800/80 flex items-center justify-between text-[11px] font-extrabold text-slate-500 dark:text-purple-300 border-b border-slate-100 dark:border-slate-800">
                                <span class="flex items-center gap-1.5">
                                    <i class="fa-solid fa-at text-purple-600 dark:text-purple-400 text-xs"></i>
                                    <span>Speler Vermelden</span>
                                </span>
                                <span class="text-[10px] text-slate-400 font-normal">↑↓ navigeer • enter kies</span>
                            </div>

                            <div class="max-h-56 overflow-y-auto p-1 space-y-0.5 custom-scrollbar">
                                <template x-for="(user, idx) in mentionUsers" :key="user.id">
                                    <div @click="selectMention(user)"
                                         @mouseenter="mentionSelectedIndex = idx"
                                         :class="mentionSelectedIndex === idx ? 'bg-purple-50 dark:bg-purple-950/70 border-l-4 border-purple-600' : 'hover:bg-slate-50 dark:hover:bg-purple-950/40 border-l-4 border-transparent'"
                                         class="px-3 py-2 rounded-xl flex items-center justify-between gap-2.5 cursor-pointer transition">
                                        <div class="flex items-center gap-2.5 min-w-0">
                                            <div class="relative shrink-0">
                                                <img :src="user.avatar || '{{ asset('assets/characters/avatar_male_1.jpg') }}'" 
                                                     class="w-7 h-7 rounded-lg object-cover border border-slate-200 dark:border-purple-800/60"
                                                     onerror="this.src='{{ asset('assets/characters/avatar_male_1.jpg') }}'">
                                                <span x-show="user.is_online" 
                                                      class="absolute -bottom-0.5 -right-0.5 w-2 h-2 rounded-full bg-emerald-500 border border-white dark:border-slate-900" 
                                                      title="Online"></span>
                                            </div>
                                            <div class="min-w-0">
                                                <div class="flex items-center gap-1.5">
                                                    <span class="text-xs font-black text-slate-900 dark:text-white truncate" x-text="user.name"></span>
                                                    <template x-if="user.gang_tag">
                                                        <span class="text-[9px] font-black uppercase text-purple-600 dark:text-purple-400" x-text="'[' + user.gang_tag + ']'"></span>
                                                    </template>
                                                </div>
                                                <div class="text-[10px] text-slate-400 flex items-center gap-1.5">
                                                    <span x-text="'Lvl ' + user.level"></span>
                                                    <template x-if="user.staff_role">
                                                        <span class="font-bold text-amber-500" x-text="user.staff_role"></span>
                                                    </template>
                                                </div>
                                            </div>
                                        </div>

                                        <span class="text-[10px] font-bold text-purple-700 dark:text-purple-300 px-2 py-0.5 rounded-md bg-purple-100 dark:bg-purple-950/80 shrink-0">
                                            Kies
                                        </span>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- TEXT INPUT FIELD (text-base on mobile prevents iOS auto-zoom, text-xs on desktop) -->
                        <div class="relative flex-1 flex items-center min-w-0">
                            <input type="text" 
                                   x-model="messageInput"
                                   x-ref="messageInputField"
                                   @input="onMessageInput($event)"
                                   @keydown="onMessageKeydown($event)"
                                   inputmode="text"
                                   enterkeyhint="send"
                                   autocomplete="off"
                                   autocorrect="on"
                                   :placeholder="activeTab === 'direct' ? 'Stuur een privébericht of @speler... (max 500 tekens)' : (activeTab === 'gang' ? 'Typ een bericht voor je bende... (max 250 tekens)' : 'Typ een bericht of @speler... (max 250 tekens)')"
                                   :maxlength="activeTab === 'direct' ? 500 : 250"
                                   class="w-full bg-slate-50 dark:bg-slate-800/90 border border-slate-200 dark:border-slate-700 focus:border-purple-500 dark:focus:border-purple-500 rounded-xl px-3.5 sm:px-4 py-2 sm:py-3 pr-14 sm:pr-16 text-base sm:text-xs text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20 transition">
                            <span x-show="messageInput.length > (activeTab === 'direct' ? 350 : 150)" x-cloak
                                  class="absolute right-3 text-[10px] sm:text-[11px] font-mono pointer-events-none"
                                  :class="messageInput.length >= (activeTab === 'direct' ? 500 : 250) ? 'text-red-500 font-bold' : 'text-slate-400 dark:text-purple-300'"
                                  x-text="messageInput.length + '/' + (activeTab === 'direct' ? 500 : 250)"></span>
                        </div>
                        
                        <!-- SEND BUTTON -->
                        <button type="submit" 
                                :disabled="isSending || messageInput.trim() === ''"
                                @click="if(window.tbsVibrate) window.tbsVibrate(15);"
                                class="px-3.5 sm:px-5 py-2 sm:py-3 rounded-xl bg-purple-600 hover:bg-purple-700 dark:bg-purple-600 dark:hover:bg-purple-500 active:scale-95 disabled:opacity-40 disabled:cursor-not-allowed text-white font-black text-xs uppercase tracking-wider transition-all duration-150 shadow-sm flex items-center justify-center gap-2 cursor-pointer shrink-0">
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
@endsection
