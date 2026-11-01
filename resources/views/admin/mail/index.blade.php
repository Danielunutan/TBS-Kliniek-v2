@extends('admin.layout')

@section('admin-content')
<!-- INLINE SCRIPT DEFINED FIRST SO ALPINE INITIALIZATION NEVER FAILS -->
<script>
window.tbsMailboxClient = function tbsMailboxClient(config) {
    return {
        activeMailbox: config.activeMailbox || 'all',
        activeFolder: config.activeFolder || 'INBOX',
        messages: config.initialMessages || [],
        stats: config.initialStats || {},
        totalMessages: config.totalMessages || 0,
        currentPage: config.currentPage || 1,
        totalPages: config.totalPages || 1,
        searchQuery: config.searchQuery || '',
        mobileView: (config.initialMessages && config.initialMessages.length > 0) ? 'list' : 'list',
        loading: false,
        bodyLoading: false,
        sending: false,
        selectedMessage: null,
        composeOpen: false,
        setupModalOpen: false,

        compose: {
            mailbox: 'vragen',
            to: '',
            cc: '',
            subject: '',
            body: '',
            isReply: false
        },

        init() {
            // Auto fetch if empty or on boot
            if (!this.messages || this.messages.length === 0) {
                this.fetchFeed();
            }
        },

        get activeMailboxLabel() {
            const labels = {
                'all': 'Alle Postbussen',
                'vragen': 'Vragen & Helpdesk',
                'privacy': 'Privacy & AVG',
                'daniel': 'Daniël (Directie)'
            };
            return labels[this.activeMailbox] || 'TBS Kliniek Mailbox';
        },

        get currentMailboxEmail() {
            const emails = {
                'all': 'vragen, privacy & daniel@tbs-kliniek.nl',
                'vragen': 'vragen@tbs-kliniek.nl',
                'privacy': 'privacy@tbs-kliniek.nl',
                'daniel': 'daniel@tbs-kliniek.nl'
            };
            return emails[this.activeMailbox] || 'daniel@tbs-kliniek.nl';
        },

        folderLabel(folder) {
            const labels = {
                'INBOX': 'Postvak IN',
                'Sent': 'Verzonden',
                'Drafts': 'Concepten',
                'Spam': 'Spam / Ongewenst',
                'Trash': 'Prullenbak'
            };
            return labels[folder] || folder;
        },

        selectMailbox(mb) {
            this.activeMailbox = mb;
            this.currentPage = 1;
            this.selectedMessage = null;
            this.mobileView = 'list';
            this.fetchFeed();
        },

        selectFolder(f) {
            this.activeFolder = f;
            this.currentPage = 1;
            this.selectedMessage = null;
            this.mobileView = 'list';
            this.fetchFeed();
        },

        doSearch() {
            this.currentPage = 1;
            this.mobileView = 'list';
            this.fetchFeed();
        },

        refreshMessages() {
            this.fetchFeed();
        },

        prevPage() {
            if (this.currentPage > 1) {
                this.currentPage--;
                this.fetchFeed();
            }
        },

        nextPage() {
            if (this.currentPage < this.totalPages) {
                this.currentPage++;
                this.fetchFeed();
            }
        },

        fetchFeed() {
            this.loading = true;
            const params = new URLSearchParams({
                mailbox: this.activeMailbox,
                folder: this.activeFolder,
                page: this.currentPage,
                q: this.searchQuery || ''
            });

            fetch(config.feedUrl + '?' + params.toString(), {
                headers: { 
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(res => {
                if (!res.ok) throw new Error('HTTP ' + res.status);
                return res.json();
            })
            .then(data => {
                this.messages = data.messages || [];
                this.totalMessages = data.total || 0;
                this.currentPage = data.page || 1;
                this.totalPages = data.totalPages || 1;
                if (data.stats) {
                    this.stats = data.stats;
                }
                this.loading = false;
            })
            .catch(err => {
                console.error('Fetch feed error:', err);
                this.loading = false;
            });
        },

        selectMessage(msg) {
            this.selectedMessage = msg;
            this.mobileView = 'message';
            msg.unread = false;
            this.bodyLoading = true;

            const params = new URLSearchParams({ folder: this.activeFolder });
            fetch(config.messageBaseUrl + '/' + msg.uid + '?' + params.toString(), {
                headers: { 
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(res => {
                if (!res.ok) throw new Error('HTTP ' + res.status);
                return res.json();
            })
            .then(data => {
                if (data.message) {
                    this.selectedMessage = data.message;
                }
                this.bodyLoading = false;
            })
            .catch(err => {
                console.error('Fetch message body error:', err);
                this.bodyLoading = false;
            });
        },

        openCompose() {
            this.compose = {
                mailbox: (this.activeMailbox !== 'all') ? this.activeMailbox : 'vragen',
                to: '',
                cc: '',
                subject: '',
                body: '',
                isReply: false
            };
            this.composeOpen = true;
        },

        replyToMessage() {
            if (!this.selectedMessage) return;
            const msg = this.selectedMessage;
            const subject = msg.subject.startsWith('Re:') ? msg.subject : ('Re: ' + msg.subject);
            const quote = '\n\n--- Oorspronkelijk bericht ---\n'
                + 'Van: ' + (msg.from_name || msg.from_email) + ' <' + msg.from_email + '>\n'
                + 'Datum: ' + msg.date + '\n'
                + 'Onderwerp: ' + msg.subject + '\n\n'
                + (msg.body_plain || '');

            this.compose = {
                mailbox: (this.activeMailbox !== 'all') ? this.activeMailbox : 'vragen',
                to: msg.from_email,
                cc: '',
                subject: subject,
                body: quote,
                isReply: true
            };
            this.composeOpen = true;
        },

        forwardMessage() {
            if (!this.selectedMessage) return;
            const msg = this.selectedMessage;
            const subject = msg.subject.startsWith('Fwd:') ? msg.subject : ('Fwd: ' + msg.subject);
            const quote = '\n\n---------- Doorgestuurd bericht ---------\n'
                + 'Van: ' + (msg.from_name || msg.from_email) + ' <' + msg.from_email + '>\n'
                + 'Datum: ' + msg.date + '\n'
                + 'Onderwerp: ' + msg.subject + '\n'
                + 'Aan: ' + (msg.to_email || '') + '\n\n'
                + (msg.body_plain || '');

            this.compose = {
                mailbox: (this.activeMailbox !== 'all') ? this.activeMailbox : 'vragen',
                to: '',
                cc: '',
                subject: subject,
                body: quote,
                isReply: false
            };
            this.composeOpen = true;
        },

        selectPlayer(e) {
            const email = e.target.value;
            if (email) {
                this.compose.to = email;
            }
        },

        applyTemplate(type) {
            if (type === 'support') {
                this.compose.mailbox = 'vragen';
                this.compose.subject = 'Ondersteuning TBS Kliniek - Reactie op jouw vraag';
                this.compose.body = 'Beste speler,\n\nBedankt voor je bericht aan het ondersteuningsteam van de TBS Kliniek.\n\nWe hebben je vraag bekeken en willen je als volgt informeren:\n\n[Typ hier jouw antwoord...]\n\nMet vriendelijke groet,\nTBS Kliniek Ondersteuningsteam\nvragen@tbs-kliniek.nl';
            } else if (type === 'privacy') {
                this.compose.mailbox = 'privacy';
                this.compose.subject = 'TBS Kliniek Privacy & AVG - Verzoek In behandeling';
                this.compose.body = 'Beste speler,\n\nWij hebben uw verzoek met betrekking tot uw persoonsgegevens en accountverwijdering conform de AVG in goede orde ontvangen.\n\nWij zullen uw verzoek binnen de wettelijke termijn verwerken en u op de hoogte stellen zodra alle gegevens definitief gewist zijn.\n\nMet vriendelijke groet,\nFunctionaris Gegevensbescherming\nTBS Kliniek\nprivacy@tbs-kliniek.nl';
            } else if (type === 'owner') {
                this.compose.mailbox = 'daniel';
                this.compose.subject = 'Bericht van de directie van TBS Kliniek';
                this.compose.body = 'Beste speler,\n\nAls eigenaar van TBS Kliniek neem ik hierbij contact met je op:\n\n[Typ hier jouw bericht...]\n\nMet vriendelijke groet,\nDaniël\nEigenaar TBS Kliniek\ndaniel@tbs-kliniek.nl';
            } else if (type === 'sanction') {
                this.compose.mailbox = 'vragen';
                this.compose.subject = 'Toelichting inzake accountstatus & sanctie - TBS Kliniek';
                this.compose.body = 'Beste speler,\n\nNaar aanleiding van jouw vraag omtrent een sanctie of blokkade hebben wij het dossier opnieuw doorgenomen:\n\n[Plaats hier de toelichting en eventuele voorwaarden...]\n\nMet vriendelijke groet,\nDirectie & Moderatie\nTBS Kliniek';
            }
        },

        submitCompose() {
            this.sending = true;
            const formData = new FormData();
            formData.append('to', this.compose.to);
            formData.append('subject', this.compose.subject);
            formData.append('body', this.compose.body);
            formData.append('cc', this.compose.cc || '');
            formData.append('mailbox', this.compose.mailbox);

            fetch(config.sendUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': config.csrfToken,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                this.sending = false;
                if (data.ok) {
                    alert('📧 E-mail succesvol verzonden via Brevo!');
                    this.composeOpen = false;
                    this.fetchFeed();
                } else {
                    alert('Fout: ' + (data.error || 'Verzenden mislukt.'));
                }
            })
            .catch(err => {
                this.sending = false;
                console.error('Send mail error:', err);
                alert('Er is een netwerkfout opgetreden bij het verzenden.');
            });
        },

        deleteCurrent() {
            if (!this.selectedMessage || !confirm('Weet je zeker dat je dit bericht wilt verwijderen?')) return;
            const uid = this.selectedMessage.uid;
            const params = new URLSearchParams({ folder: this.activeFolder });

            fetch(config.messageBaseUrl + '/' + uid + '?' + params.toString(), {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': config.csrfToken,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(res => res.json())
            .then(data => {
                this.selectedMessage = null;
                this.mobileView = 'list';
                this.fetchFeed();
            });
        },

        markSpamCurrent() {
            if (!this.selectedMessage) return;
            const uid = this.selectedMessage.uid;
            const params = new URLSearchParams({ folder: this.activeFolder });

            fetch(config.messageBaseUrl + '/' + uid + '/spam?' + params.toString(), {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': config.csrfToken,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(res => res.json())
            .then(data => {
                this.selectedMessage = null;
                this.mobileView = 'list';
                this.fetchFeed();
            });
        },

        toggleReadCurrent() {
            if (!this.selectedMessage) return;
            const uid = this.selectedMessage.uid;
            const params = new URLSearchParams({ folder: this.activeFolder });

            fetch(config.messageBaseUrl + '/' + uid + '/toggle-read?' + params.toString(), {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': config.csrfToken,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ seen: false })
            })
            .then(res => res.json())
            .then(data => {
                this.fetchFeed();
            });
        }
    };
};
</script>

<div x-data="window.tbsMailboxClient({
    activeMailbox: @js($activeMailbox),
    activeFolder: @js($folder),
    initialMessages: @js($messages),
    initialStats: @js($stats),
    totalMessages: {{ (int) $totalMessages }},
    currentPage: {{ (int) $currentPage }},
    totalPages: {{ (int) $totalPages }},
    searchQuery: @js($searchQuery ?? ''),
    feedUrl: @js(route('admin.mail.feed')),
    messageBaseUrl: @js(url('/admin/mail/message')),
    sendUrl: @js(route('admin.mail.send')),
    csrfToken: @js(csrf_token())
})" class="space-y-4">

    <!-- Top Action & Mailbox Header Bar -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-3xl p-4 sm:p-5 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-3 min-w-0">
            <div class="w-11 h-11 rounded-2xl bg-amber-50 dark:bg-amber-950/60 border border-amber-200/80 dark:border-amber-800/70 flex items-center justify-center text-amber-600 dark:text-amber-400 text-lg shadow-2xs shrink-0">
                <i class="fa-solid fa-crown"></i>
            </div>
            <div class="min-w-0">
                <div class="flex items-center gap-2">
                    <h2 class="text-base sm:text-lg font-black tracking-tight text-slate-900 dark:text-white truncate" x-text="activeMailboxLabel"></h2>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-black uppercase bg-amber-50 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 border border-amber-200/80 dark:border-amber-800/70 shrink-0">
                        Eigenaar
                    </span>
                </div>
                <p class="text-xs text-slate-500 dark:text-slate-400 truncate mt-0.5">
                    Actief postbus adres: <strong class="font-mono text-purple-600 dark:text-purple-400" x-text="currentMailboxEmail"></strong>
                </p>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2 shrink-0">
            <!-- Mobile Toggle Folders View Button -->
            <button type="button" @click="mobileView = (mobileView === 'folders' ? 'list' : 'folders')"
                    class="md:hidden px-3.5 py-2 rounded-xl bg-purple-50 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 text-xs font-bold border border-purple-200/80 dark:border-purple-800/70 flex items-center gap-1.5 shadow-2xs transition active:scale-95 cursor-pointer">
                <i class="fa-solid fa-folder-tree"></i>
                <span x-text="mobileView === 'folders' ? 'Berichten' : 'Mappen'"></span>
            </button>

            <!-- Roundcube Webmail Link -->
            <a href="https://webmail.tbs-kliniek.nl" target="_blank" rel="noopener noreferrer"
               class="px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-xs font-bold text-slate-700 dark:text-slate-200 border border-slate-200/80 dark:border-slate-700 transition-all duration-150 active:scale-95 flex items-center gap-1.5 shadow-2xs cursor-pointer">
                <i class="fa-solid fa-arrow-up-right-from-square text-[10px] text-purple-600 dark:text-purple-400"></i>
                <span class="hidden sm:inline">Plesk Roundcube</span> Webmail
            </a>

            <!-- Server Settings Toggle -->
            <button type="button" @click="setupModalOpen = true"
                    class="px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-xs font-bold text-slate-700 dark:text-slate-200 border border-slate-200/80 dark:border-slate-700 transition-all duration-150 active:scale-95 flex items-center gap-1.5 shadow-2xs cursor-pointer">
                <i class="fa-solid fa-key text-[10px] text-amber-500"></i>
                <span>Inloggegevens</span>
            </button>

            <!-- New Message Compose Button -->
            <button type="button" @click="openCompose()"
                    class="px-4 py-2 rounded-xl bg-purple-600 hover:bg-purple-700 dark:bg-purple-600 dark:hover:bg-purple-500 text-white text-xs font-black transition-all duration-150 active:scale-95 shadow-xs flex items-center gap-2 cursor-pointer">
                <i class="fa-solid fa-pen-to-square"></i>
                <span>Nieuw Bericht</span>
            </button>
        </div>
    </div>

    <!-- 3-Column Outlook / Apple Mail Client Layout -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-3xl shadow-sm overflow-hidden flex flex-col md:flex-row h-[760px] max-h-[85vh]">
        
        <!-- ============================================== -->
        <!-- 1. LEFT SIDEBAR: Mailboxes & Folders           -->
        <!-- ============================================== -->
        <aside class="mail-sidebar w-full md:w-64 lg:w-72 shrink-0 border-r border-slate-200/90 dark:border-slate-800 bg-slate-50/70 dark:bg-slate-900/60 p-3.5 flex flex-col justify-between overflow-y-auto"
               :class="mobileView === 'folders' ? 'flex' : 'hidden md:flex'">
            
            <div class="space-y-5">
                
                <!-- Mobile Header inside Folders view -->
                <div class="md:hidden flex items-center justify-between pb-2 border-b border-slate-200/80 dark:border-slate-800">
                    <span class="text-xs font-black uppercase text-slate-800 dark:text-white flex items-center gap-1.5">
                        <i class="fa-solid fa-folder-tree text-purple-600"></i> Mappen &amp; Postbussen
                    </span>
                    <button type="button" @click="mobileView = 'list'" class="px-2.5 py-1 rounded-lg bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700 text-xs font-bold text-purple-600 dark:text-purple-400">
                        Naar Berichten &rarr;
                    </button>
                </div>

                <!-- Postbussen Selector -->
                <div>
                    <span class="px-2 text-[10px] font-black uppercase tracking-wider text-slate-400 dark:text-slate-500 block">Postbussen</span>
                    <div class="mt-1.5 space-y-1">
                        @foreach($mailboxes as $key => $box)
                        <button type="button" @click="selectMailbox('{{ $key }}')"
                                class="w-full flex items-center justify-between gap-2 rounded-2xl px-3 py-2 text-left text-xs transition cursor-pointer"
                                :class="activeMailbox === '{{ $key }}' ? 'bg-white dark:bg-slate-800 text-slate-900 dark:text-white shadow-xs font-black border border-slate-200/80 dark:border-slate-700' : 'text-slate-600 dark:text-slate-400 hover:bg-white/60 dark:hover:bg-slate-800/60 font-semibold'">
                            <span class="flex items-center gap-2.5 truncate">
                                <span class="w-6 h-6 rounded-lg flex items-center justify-center shrink-0 text-xs"
                                      :class="activeMailbox === '{{ $key }}' ? 'bg-purple-100 dark:bg-purple-950 text-purple-700 dark:text-purple-300' : 'bg-slate-200 dark:bg-slate-800 text-slate-500'">
                                    <i class="fa-solid {{ $box['icon'] }} text-[10px]"></i>
                                </span>
                                <span class="truncate">{{ $box['name'] }}</span>
                            </span>
                            @if($key !== 'all')
                            <span class="text-[9px] font-bold px-1.5 py-0.5 rounded-md {{ $box['badge'] }}">
                                {{ $key }}
                            </span>
                            @endif
                        </button>
                        @endforeach
                    </div>
                </div>

                <!-- Mappen Sectie -->
                <div>
                    <span class="px-2 text-[10px] font-black uppercase tracking-wider text-slate-400 dark:text-slate-500 block">Mappen</span>
                    <nav class="mt-1.5 space-y-0.5">
                        
                        <!-- Postvak IN -->
                        <button type="button" @click="selectFolder('INBOX')"
                                class="w-full flex items-center justify-between rounded-2xl px-3 py-2 text-left text-xs transition cursor-pointer"
                                :class="activeFolder === 'INBOX' ? 'bg-white dark:bg-slate-800 text-purple-600 dark:text-purple-400 shadow-xs font-black border border-slate-200/80 dark:border-slate-700' : 'text-slate-600 dark:text-slate-400 hover:bg-white/60 dark:hover:bg-slate-800/60 font-semibold'">
                            <span class="flex items-center gap-2.5">
                                <i class="fa-solid fa-inbox text-xs w-4 text-center"></i>
                                <span>Postvak IN</span>
                            </span>
                            <template x-if="stats['INBOX'] && stats['INBOX'].total > 0">
                                <span class="rounded-full px-2 py-0.5 text-[10px] font-black"
                                      :class="stats['INBOX'].unread > 0 ? 'bg-purple-600 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300'"
                                      x-text="stats['INBOX'].total"></span>
                            </template>
                        </button>

                        <!-- Verzonden -->
                        <button type="button" @click="selectFolder('Sent')"
                                class="w-full flex items-center justify-between rounded-2xl px-3 py-2 text-left text-xs transition cursor-pointer"
                                :class="activeFolder === 'Sent' ? 'bg-white dark:bg-slate-800 text-slate-900 dark:text-white shadow-xs font-black border border-slate-200/80 dark:border-slate-700' : 'text-slate-600 dark:text-slate-400 hover:bg-white/60 dark:hover:bg-slate-800/60 font-semibold'">
                            <span class="flex items-center gap-2.5">
                                <i class="fa-regular fa-paper-plane text-xs w-4 text-center"></i>
                                <span>Verzonden</span>
                            </span>
                            <span class="text-[10px] text-slate-400 font-bold" x-text="stats['Sent'] ? stats['Sent'].total : ''"></span>
                        </button>

                        <!-- Concepten -->
                        <button type="button" @click="selectFolder('Drafts')"
                                class="w-full flex items-center justify-between rounded-2xl px-3 py-2 text-left text-xs transition cursor-pointer"
                                :class="activeFolder === 'Drafts' ? 'bg-white dark:bg-slate-800 text-slate-900 dark:text-white shadow-xs font-black border border-slate-200/80 dark:border-slate-700' : 'text-slate-600 dark:text-slate-400 hover:bg-white/60 dark:hover:bg-slate-800/60 font-semibold'">
                            <span class="flex items-center gap-2.5">
                                <i class="fa-regular fa-file-lines text-xs w-4 text-center"></i>
                                <span>Concepten</span>
                            </span>
                            <span class="text-[10px] text-slate-400 font-bold" x-text="stats['Drafts'] ? stats['Drafts'].total : ''"></span>
                        </button>

                        <!-- Spam / Ongewenst -->
                        <button type="button" @click="selectFolder('Spam')"
                                class="w-full flex items-center justify-between rounded-2xl px-3 py-2 text-left text-xs transition cursor-pointer"
                                :class="activeFolder === 'Spam' ? 'bg-white dark:bg-slate-800 text-amber-600 dark:text-amber-400 shadow-xs font-black border border-slate-200/80 dark:border-slate-700' : 'text-slate-600 dark:text-slate-400 hover:bg-white/60 dark:hover:bg-slate-800/60 font-semibold'">
                            <span class="flex items-center gap-2.5">
                                <i class="fa-solid fa-shield-halved text-xs w-4 text-center text-amber-500"></i>
                                <span>Spam / Ongewenst</span>
                            </span>
                            <template x-if="stats['Spam'] && stats['Spam'].total > 0">
                                <span class="rounded-full bg-amber-50 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 border border-amber-200/80 dark:border-amber-800/70 px-2 py-0.5 text-[10px] font-black" x-text="stats['Spam'].total"></span>
                            </template>
                        </button>

                        <!-- Prullenbak -->
                        <button type="button" @click="selectFolder('Trash')"
                                class="w-full flex items-center justify-between rounded-2xl px-3 py-2 text-left text-xs transition cursor-pointer"
                                :class="activeFolder === 'Trash' ? 'bg-white dark:bg-slate-800 text-slate-900 dark:text-white shadow-xs font-black border border-slate-200/80 dark:border-slate-700' : 'text-slate-600 dark:text-slate-400 hover:bg-white/60 dark:hover:bg-slate-800/60 font-semibold'">
                            <span class="flex items-center gap-2.5">
                                <i class="fa-regular fa-trash-can text-xs w-4 text-center"></i>
                                <span>Prullenbak</span>
                            </span>
                            <span class="text-[10px] text-slate-400 font-bold" x-text="stats['Trash'] ? stats['Trash'].total : ''"></span>
                        </button>

                    </nav>
                </div>

            </div>

            <!-- Server Status Footnote -->
            <div class="mt-4 rounded-2xl border border-slate-200/80 dark:border-slate-800 bg-white dark:bg-slate-800/50 p-3 text-[11px] shadow-2xs space-y-1">
                <div class="flex items-center justify-between text-slate-700 dark:text-slate-300 font-bold">
                    <span>Brevo SMTP Relay</span>
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                </div>
                <p class="text-[10px] text-slate-400 dark:text-slate-500">
                    Geauthenticeerde DKIM &amp; SPF aflevering actief.
                </p>
            </div>
        </aside>

        <!-- ============================================== -->
        <!-- 2. MIDDLE COLUMN: Message List                 -->
        <!-- ============================================== -->
        <section class="w-full md:w-80 lg:w-96 shrink-0 border-r border-slate-200/90 dark:border-slate-800 bg-white dark:bg-slate-900 flex flex-col overflow-hidden"
                 :class="mobileView === 'list' ? 'flex' : 'hidden md:flex'">
            
            <!-- Search & Controls Bar -->
            <div class="p-3 border-b border-slate-100 dark:border-slate-800 bg-slate-50/40 dark:bg-slate-800/30 space-y-2 shrink-0">
                <div class="flex items-center gap-2">
                    <!-- Mobile Button to Switch to Folders -->
                    <button type="button" @click="mobileView = 'folders'"
                            class="md:hidden flex h-8 px-2.5 items-center gap-1.5 rounded-xl border border-slate-200/80 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-bold"
                            title="Mappen bekijken">
                        <i class="fa-solid fa-folder-tree text-purple-600"></i>
                        <span>Mappen</span>
                    </button>

                    <div class="relative flex-1">
                        <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-xs text-slate-400 dark:text-slate-500"></i>
                        <input type="text" x-model="searchQuery" @keydown.enter="doSearch()" placeholder="Zoek in e-mails..."
                               class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/90 pl-8 pr-7 py-1.5 text-xs text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:border-purple-500 dark:focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition">
                        <button type="button" x-show="searchQuery" @click="searchQuery = ''; doSearch()" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-xs text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>

                    <button type="button" @click="refreshMessages()" class="flex h-8 w-8 items-center justify-center rounded-xl border border-slate-200/80 dark:border-slate-700 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 transition-all duration-150 active:scale-95 cursor-pointer shrink-0" title="Vernieuwen">
                        <i class="fa-solid fa-rotate-right text-xs" :class="loading ? 'animate-spin text-purple-600' : ''"></i>
                    </button>
                </div>

                <div class="flex items-center justify-between text-[11px] text-slate-500 dark:text-slate-400 px-1 font-medium">
                    <span x-text="searchQuery ? 'Zoekresultaten' : folderLabel(activeFolder)"></span>
                    <span x-text="totalMessages > 0 ? (totalMessages + ' ' + (totalMessages === 1 ? 'bericht' : 'berichten')) : '0 berichten'"></span>
                </div>
            </div>

            <!-- Messages Scrollable List -->
            <div class="flex-1 overflow-y-auto divide-y divide-slate-100 dark:divide-slate-800/70">
                
                <template x-if="loading && messages.length === 0">
                    <div class="p-8 text-center text-xs text-slate-400 dark:text-slate-500 space-y-2">
                        <i class="fa-solid fa-circle-notch animate-spin text-xl text-purple-600"></i>
                        <p>E-mails ophalen via IMAP...</p>
                    </div>
                </template>

                <template x-if="!loading && messages.length === 0">
                    <div class="p-10 text-center space-y-2">
                        <div class="w-12 h-12 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-400 dark:text-slate-500 text-lg mx-auto">
                            <i class="fa-solid fa-inbox"></i>
                        </div>
                        <h4 class="text-xs font-black text-slate-700 dark:text-slate-300">Geen berichten</h4>
                        <p class="text-[11px] text-slate-400 dark:text-slate-500">Deze map is momenteel leeg.</p>
                    </div>
                </template>

                <template x-for="msg in messages" :key="msg.uid">
                    <div @click="selectMessage(msg)"
                         class="p-3.5 cursor-pointer transition select-none flex flex-col gap-1 border-l-4"
                         :class="[
                             selectedMessage && selectedMessage.uid === msg.uid 
                                 ? 'bg-purple-50/70 dark:bg-purple-950/40 border-purple-600' 
                                 : 'hover:bg-slate-50 dark:hover:bg-slate-800/40 border-transparent',
                             msg.unread ? 'font-bold' : 'font-normal opacity-90'
                         ]">
                        <div class="flex items-center justify-between gap-2">
                            <div class="flex items-center gap-1.5 min-w-0">
                                <span x-show="msg.unread" class="w-2 h-2 rounded-full bg-purple-600 shrink-0"></span>
                                <span class="text-xs font-black text-slate-900 dark:text-white truncate" x-text="msg.from_name || msg.from_email"></span>
                            </div>
                            <span class="text-[10px] text-slate-400 dark:text-slate-500 font-mono shrink-0" x-text="msg.date_short || msg.date"></span>
                        </div>

                        <h4 class="text-xs text-slate-800 dark:text-slate-200 truncate" x-text="msg.subject || '(Geen onderwerp)'"></h4>
                        <span class="text-[10px] text-slate-400 dark:text-slate-500 truncate font-mono" x-text="'Aan: ' + (msg.to_email || 'Kliniek')"></span>
                    </div>
                </template>

            </div>

            <!-- List Pagination Footer -->
            <div x-show="totalPages > 1" class="p-2.5 border-t border-slate-100 dark:border-slate-800 bg-slate-50/40 dark:bg-slate-800/30 flex items-center justify-between text-xs shrink-0">
                <button type="button" @click="prevPage()" :disabled="currentPage <= 1"
                        class="px-2.5 py-1 rounded-lg border border-slate-200/80 dark:border-slate-700 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-[11px] font-bold disabled:opacity-40 cursor-pointer transition active:scale-95">
                    &larr; Vorige
                </button>
                <span class="text-[11px] text-slate-500 dark:text-slate-400 font-mono" x-text="'Pagina ' + currentPage + ' / ' + totalPages"></span>
                <button type="button" @click="nextPage()" :disabled="currentPage >= totalPages"
                        class="px-2.5 py-1 rounded-lg border border-slate-200/80 dark:border-slate-700 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-[11px] font-bold disabled:opacity-40 cursor-pointer transition active:scale-95">
                    Volgende &rarr;
                </button>
            </div>

        </section>

        <!-- ============================================== -->
        <!-- 3. RIGHT COLUMN: Message View & Empty State    -->
        <!-- ============================================== -->
        <main class="flex-1 bg-white dark:bg-slate-900 flex flex-col min-w-0 overflow-hidden"
              :class="mobileView === 'message' ? 'flex' : 'hidden md:flex'">
            
            <!-- Empty State: No message selected -->
            <template x-if="!selectedMessage">
                <div class="flex-1 flex flex-col items-center justify-center p-8 text-center space-y-4">
                    <div class="w-20 h-20 rounded-3xl bg-slate-100 dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700 flex items-center justify-center text-slate-400 dark:text-slate-500 text-3xl shadow-sm">
                        <i class="fa-regular fa-envelope-open"></i>
                    </div>
                    <div class="max-w-xs space-y-1">
                        <h3 class="text-sm font-black text-slate-900 dark:text-white">Geen bericht geselecteerd</h3>
                        <p class="text-xs text-slate-400 dark:text-slate-500 leading-relaxed">
                            Kies een e-mail uit de lijst aan de linkerkant om de volledige inhoud te lezen of stel direct een nieuw bericht op.
                        </p>
                    </div>
                    <button type="button" @click="openCompose()"
                            class="px-4 py-2 rounded-xl bg-purple-600 hover:bg-purple-700 dark:bg-purple-600 dark:hover:bg-purple-500 text-white font-black text-xs shadow-xs transition-all duration-150 active:scale-95 cursor-pointer">
                        <i class="fa-solid fa-pen-to-square mr-1"></i> E-mail Versturen via Brevo
                    </button>
                </div>
            </template>

            <!-- Active Message View -->
            <template x-if="selectedMessage">
                <div class="flex-1 flex flex-col h-full overflow-hidden">
                    
                    <!-- Message Header & Action Toolbar -->
                    <div class="p-4 sm:p-5 border-b border-slate-100 dark:border-slate-800 bg-slate-50/30 dark:bg-slate-800/20 space-y-3 shrink-0">
                        
                        <!-- Top Toolbar Knopjes -->
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <div class="flex items-center gap-1.5">
                                <!-- Mobile Back to List Button -->
                                <button type="button" @click="mobileView = 'list'"
                                        class="md:hidden px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold text-xs transition active:scale-95 cursor-pointer">
                                    <i class="fa-solid fa-arrow-left text-[10px]"></i>
                                    <span>Lijst</span>
                                </button>

                                <button type="button" @click="replyToMessage()"
                                        class="px-3 py-1.5 rounded-xl bg-purple-600 hover:bg-purple-700 dark:bg-purple-600 dark:hover:bg-purple-500 text-white font-black text-xs transition-all duration-150 active:scale-95 shadow-2xs flex items-center gap-1.5 cursor-pointer">
                                    <i class="fa-solid fa-reply text-[11px]"></i> Beantwoorden
                                </button>
                                <button type="button" @click="forwardMessage()"
                                        class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 border border-slate-200/80 dark:border-slate-700 font-bold text-xs transition-all duration-150 active:scale-95 flex items-center gap-1.5 cursor-pointer">
                                    <i class="fa-solid fa-share text-[11px]"></i> Doorsturen
                                </button>
                                <button type="button" @click="toggleReadCurrent()"
                                        class="px-2.5 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-400 border border-slate-200/80 dark:border-slate-700 text-xs transition active:scale-95 cursor-pointer" title="Als ongelezen markeren">
                                    <i class="fa-regular fa-envelope"></i>
                                </button>
                            </div>

                            <div class="flex items-center gap-1.5">
                                <button type="button" @click="markSpamCurrent()"
                                        class="px-2.5 py-1.5 rounded-xl bg-amber-50 hover:bg-amber-100 dark:bg-amber-950/60 dark:hover:bg-amber-900/60 text-amber-700 dark:text-amber-400 border border-amber-200/80 dark:border-amber-800/70 text-xs transition-all duration-150 active:scale-95 font-bold cursor-pointer" title="Verplaats naar Spam">
                                    <i class="fa-solid fa-shield-halved"></i> Spam
                                </button>
                                <button type="button" @click="deleteCurrent()"
                                        class="px-2.5 py-1.5 rounded-xl bg-rose-50 hover:bg-rose-100 dark:bg-rose-950/60 dark:hover:bg-rose-900/60 text-rose-700 dark:text-rose-400 border border-rose-200/80 dark:border-rose-800/70 text-xs transition-all duration-150 active:scale-95 font-bold cursor-pointer" title="Verwijderen">
                                    <i class="fa-regular fa-trash-can"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Subject & Sender Info -->
                        <div class="space-y-1.5">
                            <h2 class="text-base sm:text-lg font-black text-slate-900 dark:text-white leading-tight" x-text="selectedMessage.subject"></h2>
                            
                            <div class="flex items-start justify-between gap-3 text-xs">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <div class="w-8 h-8 rounded-xl bg-gradient-to-br from-purple-600 to-indigo-600 text-white font-black text-xs flex items-center justify-center shrink-0"
                                         x-text="(selectedMessage.from_name || selectedMessage.from_email || '?').substring(0, 1).toUpperCase()"></div>
                                    <div class="min-w-0">
                                        <div class="font-bold text-slate-900 dark:text-white truncate" x-text="selectedMessage.from_name || selectedMessage.from_email"></div>
                                        <div class="text-[11px] text-slate-400 font-mono truncate" x-text="'Van: ' + selectedMessage.from_email"></div>
                                    </div>
                                </div>
                                <span class="text-[11px] font-mono text-slate-400 shrink-0" x-text="selectedMessage.date"></span>
                            </div>

                            <div class="text-[11px] text-slate-400 font-mono">
                                Aan: <span class="text-slate-600 dark:text-slate-300 font-bold" x-text="selectedMessage.to_email || 'Kliniek'"></span>
                            </div>
                        </div>

                    </div>

                    <!-- Message Body Container -->
                    <div class="flex-1 overflow-y-auto p-4 sm:p-6 bg-white dark:bg-slate-900 selection:bg-purple-500/20">
                        <template x-if="bodyLoading">
                            <div class="py-12 text-center text-xs text-slate-400 space-y-2">
                                <i class="fa-solid fa-circle-notch animate-spin text-xl text-purple-600"></i>
                                <p>Berichtinhoud ophalen...</p>
                            </div>
                        </template>

                        <template x-if="!bodyLoading && selectedMessage.body_html">
                            <iframe class="w-full h-96 border-0 bg-white rounded-xl"
                                    sandbox="allow-popups"
                                    :srcdoc="selectedMessage.body_html"></iframe>
                        </template>

                        <template x-if="!bodyLoading && !selectedMessage.body_html && selectedMessage.body_plain">
                            <pre class="whitespace-pre-wrap font-sans text-xs sm:text-sm text-slate-800 dark:text-slate-200 leading-relaxed"
                                 x-text="selectedMessage.body_plain"></pre>
                        </template>

                        <template x-if="!bodyLoading && !selectedMessage.body_html && !selectedMessage.body_plain">
                            <p class="text-xs text-slate-400 italic">(Dit bericht bevat geen tekstinhoud)</p>
                        </template>
                    </div>

                </div>
            </template>

        </main>

    </div>

    <!-- ============================================== -->
    <!-- 4. NIEUW BERICHT / COMPOSE MODAL               -->
    <!-- ============================================== -->
    <div x-show="composeOpen" x-cloak
         @keydown.escape.window="composeOpen = false"
         class="fixed inset-0 z-50 flex items-center justify-center p-3 pb-24 sm:p-4 lg:pb-4 bg-slate-900/60 dark:bg-slate-950/80 backdrop-blur-xs"
         @click.self="composeOpen = false">
        <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-3xl max-w-2xl w-full shadow-2xl flex flex-col max-h-[92vh] overflow-hidden">
            
            <!-- Modal Header -->
            <div class="p-4 sm:p-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between gap-3 shrink-0">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl bg-purple-50 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400 border border-purple-200/80 dark:border-purple-800/70 flex items-center justify-center text-sm font-black">
                        <i class="fa-solid fa-pen-nib"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-black text-slate-900 dark:text-white" x-text="compose.isReply ? 'E-mail Beantwoorden' : 'Nieuw Bericht Opstellen'"></h3>
                        <p class="text-[11px] text-slate-400 dark:text-slate-500">Wordt verzonden via geauthenticeerde Brevo relay</p>
                    </div>
                </div>
                <button type="button" @click="composeOpen = false" class="w-8 h-8 rounded-xl flex items-center justify-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition cursor-pointer" aria-label="Sluiten">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>

            <!-- Form -->
            <form @submit.prevent="submitCompose()" class="flex-1 flex flex-col overflow-y-auto p-4 sm:p-6 space-y-4 text-xs">
                
                <!-- Senders & Templates Row -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pb-2 border-b border-slate-100 dark:border-slate-800">
                    <div>
                        <label class="block text-[10px] uppercase font-black tracking-wider text-slate-500 dark:text-slate-400 mb-1">Verzenden Vanaf:</label>
                        <select x-model="compose.mailbox" class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/90 text-xs font-bold text-slate-900 dark:text-white py-2 px-3 focus:outline-none focus:border-purple-500 dark:focus:border-purple-500 transition">
                            <option value="vragen">Vragen &amp; Helpdesk &lt;vragen@tbs-kliniek.nl&gt;</option>
                            <option value="privacy">Privacy &amp; AVG &lt;privacy@tbs-kliniek.nl&gt;</option>
                            <option value="daniel">Daniël (Eigenaar) &lt;daniel@tbs-kliniek.nl&gt;</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-[10px] uppercase font-black tracking-wider text-slate-500 dark:text-slate-400 mb-1">Snelle Sjablonen:</label>
                        <div class="flex flex-wrap gap-1">
                            <button type="button" @click="applyTemplate('support')" class="px-2 py-1 rounded-lg bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 text-[10px] font-bold border border-emerald-200/80 dark:border-emerald-800/70 hover:bg-emerald-100 dark:hover:bg-emerald-900/50 transition cursor-pointer active:scale-95">Helpdesk</button>
                            <button type="button" @click="applyTemplate('privacy')" class="px-2 py-1 rounded-lg bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 text-[10px] font-bold border border-indigo-200/80 dark:border-indigo-800/70 hover:bg-indigo-100 dark:hover:bg-indigo-900/50 transition cursor-pointer active:scale-95">Privacy/AVG</button>
                            <button type="button" @click="applyTemplate('sanction')" class="px-2 py-1 rounded-lg bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 text-[10px] font-bold border border-rose-200/80 dark:border-rose-800/70 hover:bg-rose-100 dark:hover:bg-rose-900/50 transition cursor-pointer active:scale-95">Sanctie</button>
                            <button type="button" @click="applyTemplate('owner')" class="px-2 py-1 rounded-lg bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 text-[10px] font-bold border border-amber-200/80 dark:border-amber-800/70 hover:bg-amber-100 dark:hover:bg-amber-900/50 transition cursor-pointer active:scale-95">Directie</button>
                        </div>
                    </div>
                </div>

                <!-- Recipient & Speler Autocomplete -->
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <label class="block text-[10px] uppercase font-black tracking-wider text-slate-500 dark:text-slate-400">Ontvanger *</label>
                        
                        <div class="flex items-center gap-1.5">
                            <span class="text-[10px] text-slate-400 dark:text-slate-500">Kies speler:</span>
                            <select @change="selectPlayer($event)" class="text-[11px] font-bold rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/90 text-slate-700 dark:text-slate-300 py-1 px-2 focus:outline-none focus:border-purple-500 dark:focus:border-purple-500 transition">
                                <option value="">-- Zoek speler --</option>
                                @foreach($players as $p)
                                <option value="{{ $p->email }}">{{ $p->name }} ({{ $p->email }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <input type="email" x-model="compose.to" required placeholder="bijv. speler@example.com"
                               class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/90 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 p-2.5 font-bold focus:outline-none focus:border-purple-500 dark:focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition">
                        <input type="text" x-model="compose.cc" placeholder="CC: optioneel adres"
                               class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/90 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 p-2.5 font-mono text-[11px] focus:outline-none focus:border-purple-500 dark:focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition">
                    </div>
                </div>

                <!-- Subject -->
                <div class="space-y-1">
                    <label class="block text-[10px] uppercase font-black tracking-wider text-slate-500 dark:text-slate-400">Onderwerp *</label>
                    <input type="text" x-model="compose.subject" required placeholder="Onderwerp van het bericht..."
                           class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/90 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 p-2.5 font-bold focus:outline-none focus:border-purple-500 dark:focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition">
                </div>

                <!-- Body -->
                <div class="space-y-1">
                    <label class="block text-[10px] uppercase font-black tracking-wider text-slate-500 dark:text-slate-400">Berichttekst *</label>
                    <textarea x-model="compose.body" required rows="9" placeholder="Typ hier jouw bericht..."
                              class="w-full rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/90 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 p-3.5 leading-relaxed font-sans focus:outline-none focus:border-purple-500 dark:focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition"></textarea>
                </div>

                <!-- Modal Actions -->
                <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between gap-3">
                    <span class="text-[10px] text-slate-400 dark:text-slate-500">Automatische handtekening en kopie in Verzonden.</span>
                    
                    <div class="flex items-center gap-2">
                        <button type="button" @click="composeOpen = false" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 border border-slate-200/80 dark:border-slate-700 font-bold text-xs transition-all duration-150 active:scale-95 cursor-pointer">
                            Annuleren
                        </button>
                        <button type="submit" :disabled="sending"
                                class="px-5 py-2 rounded-xl bg-purple-600 hover:bg-purple-700 dark:bg-purple-600 dark:hover:bg-purple-500 text-white font-black text-xs flex items-center gap-2 transition-all duration-150 active:scale-95 disabled:opacity-50 cursor-pointer shadow-xs">
                            <i class="fa-solid fa-paper-plane" :class="sending ? 'animate-bounce' : ''"></i>
                            <span x-text="sending ? 'Verzenden...' : 'Verstuur via Brevo'"></span>
                        </button>
                    </div>
                </div>

            </form>

        </div>
    </div>

    <!-- ============================================== -->
    <!-- 5. SERVER SETUP / CREDENTIALS MODAL            -->
    <!-- ============================================== -->
    <div x-show="setupModalOpen" x-cloak
         @keydown.escape.window="setupModalOpen = false"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 pb-24 lg:pb-4 bg-slate-900/60 dark:bg-slate-950/80 backdrop-blur-xs"
         @click.self="setupModalOpen = false">
        <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-3xl max-w-lg w-full max-h-[85dvh] overflow-y-auto p-6 shadow-2xl space-y-4 text-xs">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                <h3 class="text-sm font-black text-slate-900 dark:text-white flex items-center gap-2">
                    <i class="fa-solid fa-key text-amber-500"></i> Server &amp; Inloggegevens
                </h3>
                <button type="button" @click="setupModalOpen = false" class="w-8 h-8 rounded-xl flex items-center justify-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition cursor-pointer" aria-label="Sluiten">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>

            <div class="space-y-3">
                <div class="p-3 rounded-2xl bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70 space-y-1">
                    <span class="text-[10px] uppercase font-bold text-slate-400 dark:text-slate-500 block">IMAP (Inkomend)</span>
                    <p class="font-mono font-bold text-slate-800 dark:text-slate-200">tbs-kliniek.nl &bull; Poort 993 (SSL)</p>
                </div>
                <div class="p-3 rounded-2xl bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70 space-y-1">
                    <span class="text-[10px] uppercase font-bold text-slate-400 dark:text-slate-500 block">SMTP (Uitgaand Brevo Relay)</span>
                    <p class="font-mono font-bold text-slate-800 dark:text-slate-200">smtp-relay.brevo.com &bull; Poort 587 (TLS)</p>
                </div>
                <div class="p-3 rounded-2xl bg-slate-50/90 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/70 space-y-1">
                    <span class="text-[10px] uppercase font-bold text-slate-400 dark:text-slate-500 block">Account &amp; Wachtwoord</span>
                    <p class="font-mono font-bold text-purple-700 dark:text-purple-400">daniel@tbs-kliniek.nl</p>
                    <p class="font-mono text-slate-600 dark:text-slate-300 font-bold">•••••••• <span class="text-[10px] text-slate-400 font-normal">(geconfigureerd in .env: MAIL_PASSWORD)</span></p>
                </div>
            </div>

            <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex justify-end">
                <button type="button" @click="setupModalOpen = false" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 border border-slate-200/80 dark:border-slate-700 font-bold transition-all duration-150 active:scale-95 cursor-pointer">
                    Sluiten
                </button>
            </div>
        </div>
    </div>

</div>
@endsection
