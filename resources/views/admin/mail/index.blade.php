@extends('admin.layout')

@section('admin-content')
<div class="space-y-6" x-data="{
    activeTab: 'compose',
    selectedSender: 'vragen@tbs-kliniek.nl',
    recipientEmail: '{{ old('recipient_email', '') }}',
    recipientName: '{{ old('recipient_name', '') }}',
    subject: '{{ old('subject', '') }}',
    body: '{{ old('body', '') }}',
    showCredentials: false,
    selectedLog: null,
    showLogModal: false,
    
    applyTemplate(type) {
        if (type === 'support') {
            this.selectedSender = 'vragen@tbs-kliniek.nl';
            this.subject = 'Reactie op jouw vraag aan de TBS Kliniek';
            this.body = 'Beste speler,\n\nBedankt voor je bericht aan het ondersteuningsteam van de TBS Kliniek.\n\nWe hebben je vraag bekeken en willen je graag als volgt informeren:\n\n[Vul hier je antwoord in...]\n\nMet vriendelijke groet,\nTBS Kliniek Ondersteuningsteam\nhttps://tbs-kliniek.nl';
        } else if (type === 'privacy') {
            this.selectedSender = 'privacy@tbs-kliniek.nl';
            this.subject = 'Bevestiging inzake privacy / AVG verzoek - TBS Kliniek';
            this.body = 'Beste heer / mevrouw,\n\nWij hebben uw verzoek met betrekking tot de Algemene Verordening Gegevensbescherming (AVG) in goede orde ontvangen.\n\n[Geef hier toelichting op de verwerking of accountverwijdering...]\n\nMet vriendelijke groet,\nFunctionaris Gegevensbescherming\nTBS Kliniek\nprivacy@tbs-kliniek.nl';
        } else if (type === 'owner') {
            this.selectedSender = 'daniel@tbs-kliniek.nl';
            this.subject = 'Belangrijk bericht van de directie van TBS Kliniek';
            this.body = 'Beste speler,\n\nAls eigenaar van de TBS Kliniek neem ik persoonlijk contact met je op omtrent het volgende:\n\n[Typ hier jouw persoonlijke bericht...]\n\nMet vriendelijke groet,\nDaniël\nEigenaar TBS Kliniek\ndaniel@tbs-kliniek.nl';
        } else if (type === 'sanction') {
            this.selectedSender = 'vragen@tbs-kliniek.nl';
            this.subject = 'Toelichting inzake accountstatus of sanctie - TBS Kliniek';
            this.body = 'Beste speler,\n\nNaar aanleiding van je contactverzoek over een sanctie of accountblokkade hebben wij het dossier opnieuw doorgenomen.\n\n[Plaats hier de beslissing en eventuele voorwaarden...]\n\nMet vriendelijke groet,\nDirectie & Moderatie\nTBS Kliniek';
        }
    },

    selectPlayer(e) {
        const val = e.target.value;
        if (!val) return;
        const parts = val.split('|');
        if (parts.length === 2) {
            this.recipientName = parts[0];
            this.recipientEmail = parts[1];
        }
    },

    openLogModal(logId) {
        fetch('{{ url('/admin/mail/logs') }}/' + logId)
            .then(res => res.json())
            .then(data => {
                this.selectedLog = data;
                this.showLogModal = true;
            })
            .catch(err => alert('Kon log niet laden.'));
    }
}">

    <!-- Flash Messages -->
    @if(session('success'))
    <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-semibold flex items-center justify-between shadow-2xs">
        <div class="flex items-center gap-2.5">
            <i class="fa-solid fa-circle-check text-emerald-600 text-base"></i>
            <span>{{ session('success') }}</span>
        </div>
        <button type="button" @click="$el.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>
    @endif

    @if(session('error'))
    <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-sm font-semibold flex items-center justify-between shadow-2xs">
        <div class="flex items-center gap-2.5">
            <i class="fa-solid fa-circle-exclamation text-rose-600 text-base"></i>
            <span>{{ session('error') }}</span>
        </div>
        <button type="button" @click="$el.parentElement.remove()" class="text-rose-500 hover:text-rose-700">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>
    @endif

    <!-- Top Owner Banner & Webmail Action -->
    <div class="bg-gradient-to-r from-amber-500 via-amber-600 to-yellow-600 rounded-3xl p-6 sm:p-7 text-white shadow-lg relative overflow-hidden">
        <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            <div class="space-y-2 max-w-2xl">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/20 backdrop-blur-md text-[11px] font-black uppercase tracking-wider text-amber-50 border border-white/20">
                    <i class="fa-solid fa-crown text-amber-200"></i> Alleen Toegankelijk Voor Eigenaar
                </div>
                <h2 class="text-2xl sm:text-3xl font-black tracking-tight text-white">
                    TBS Kliniek Mailbox &amp; Brevo E-mailbeheer
                </h2>
                <p class="text-xs sm:text-sm text-amber-100 font-medium leading-relaxed">
                    Beheer alle officiële e-mailadressen van de TBS Kliniek. E-mails worden verzonden via de geauthenticeerde 
                    <strong>Brevo SMTP Relay</strong> en binnenkomende post landt rechtstreeks in de beveiligde Plesk mailbox.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-3 shrink-0">
                <a href="{{ $mailboxConfig['webmail_url'] }}" target="_blank" rel="noopener noreferrer"
                   class="px-5 py-3 rounded-2xl bg-white text-slate-900 font-black text-xs hover:bg-amber-50 transition shadow-md flex items-center gap-2 shrink-0">
                    <i class="fa-solid fa-envelope-open-text text-amber-600"></i> Open Roundcube Webmail ↗
                </a>
                <button type="button" @click="showCredentials = !showCredentials"
                        class="px-4 py-3 rounded-2xl bg-black/20 hover:bg-black/30 backdrop-blur-md text-white font-bold text-xs transition border border-white/20 flex items-center gap-2">
                    <i class="fa-solid fa-key text-amber-200"></i>
                    <span x-text="showCredentials ? 'Verberg Server Instellingen' : 'Server &amp; Inloggegevens'"></span>
                </button>
            </div>
        </div>

        <!-- Background Ambient Glow -->
        <div class="absolute -right-12 -bottom-12 w-64 h-64 rounded-full bg-white/10 blur-2xl pointer-events-none"></div>
    </div>

    <!-- Collapsible Server Credentials Card -->
    <div x-show="showCredentials" x-transition.duration.250ms
         class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-sm">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800">
            <h3 class="text-sm font-black text-slate-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
                <i class="fa-solid fa-server text-purple-600"></i> Plesk &amp; Brevo Technische Configuratie
            </h3>
            <span class="text-[11px] font-bold text-slate-400">SSL/TLS Actief</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mt-5 text-xs">
            <div class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/70 dark:border-slate-700/60 space-y-1">
                <span class="text-[10px] uppercase font-black text-slate-400 block tracking-wider">Hoofd Mailbox Account</span>
                <span class="font-mono font-bold text-slate-800 dark:text-slate-200 block truncate">{{ $mailboxConfig['primary_mailbox'] }}</span>
                <span class="text-[10px] text-emerald-600 font-semibold block">Actief in Plesk (Let's Encrypt SSL)</span>
            </div>

            <div class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/70 dark:border-slate-700/60 space-y-1">
                <span class="text-[10px] uppercase font-black text-slate-400 block tracking-wider">Mailbox Wachtwoord</span>
                <span class="font-mono font-bold text-purple-700 dark:text-purple-400 block select-all">{{ $mailboxConfig['mailbox_password'] }}</span>
                <span class="text-[10px] text-slate-400 block">Voor Roundcube &amp; IMAP/POP3</span>
            </div>

            <div class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/70 dark:border-slate-700/60 space-y-1">
                <span class="text-[10px] uppercase font-black text-slate-400 block tracking-wider">Inkomend (IMAP)</span>
                <span class="font-mono font-bold text-slate-800 dark:text-slate-200 block">{{ $mailboxConfig['imap_host'] }}</span>
                <span class="text-[10px] text-slate-500 block">Poort: {{ $mailboxConfig['imap_port'] }}</span>
            </div>

            <div class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/70 dark:border-slate-700/60 space-y-1">
                <span class="text-[10px] uppercase font-black text-slate-400 block tracking-wider">Uitgaand (Brevo SMTP)</span>
                <span class="font-mono font-bold text-slate-800 dark:text-slate-200 block">{{ $mailboxConfig['smtp_host'] }}</span>
                <span class="text-[10px] text-slate-500 block">Poort {{ $mailboxConfig['smtp_port'] }} &bull; User: {{ $mailboxConfig['smtp_user'] }}</span>
            </div>
        </div>
    </div>

    <!-- 3 Active Mail Addresses Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        @foreach($senders as $email => $info)
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-5 shadow-xs flex flex-col justify-between gap-4 transition hover:border-purple-300 dark:hover:border-purple-700">
            <div class="space-y-2">
                <div class="flex items-center justify-between">
                    <span class="w-10 h-10 rounded-2xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-base text-slate-700 dark:text-slate-300">
                        <i class="fa-solid {{ $info['icon'] }}"></i>
                    </span>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase border {{ $info['badge_color'] }}">
                        Actief
                    </span>
                </div>
                <div>
                    <h3 class="text-sm font-black text-slate-900 dark:text-white">{{ $info['name'] }}</h3>
                    <span class="font-mono text-xs font-bold text-purple-600 dark:text-purple-400 block mt-0.5">{{ $email }}</span>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1 leading-snug">{{ $info['desc'] }}</p>
                </div>
            </div>

            <button type="button" 
                    @click="activeTab = 'compose'; selectedSender = '{{ $email }}'; window.scrollTo({top: 400, behavior: 'smooth'})"
                    class="w-full py-2.5 px-3 rounded-xl bg-slate-50 dark:bg-slate-800 hover:bg-purple-50 dark:hover:bg-purple-950/60 hover:text-purple-600 dark:hover:text-purple-300 text-xs font-bold text-slate-700 dark:text-slate-300 transition flex items-center justify-center gap-1.5 border border-slate-200/80 dark:border-slate-700">
                <i class="fa-solid fa-paper-plane text-[10px]"></i> Verstuur vanaf dit adres
            </button>
        </div>
        @endforeach
    </div>

    <!-- Navigation Sub-Tabs -->
    <div class="flex items-center gap-2 border-b border-slate-200 dark:border-slate-800 pb-3 text-xs font-bold">
        <button type="button" @click="activeTab = 'compose'"
                :class="activeTab === 'compose' ? 'bg-purple-600 text-white shadow-xs' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800'"
                class="px-4 py-2 rounded-xl transition flex items-center gap-2">
            <i class="fa-solid fa-pen-to-square"></i> E-mail Opstellen (Brevo)
        </button>
        <button type="button" @click="activeTab = 'logs'"
                :class="activeTab === 'logs' ? 'bg-purple-600 text-white shadow-xs' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800'"
                class="px-4 py-2 rounded-xl transition flex items-center gap-2">
            <i class="fa-solid fa-clock-rotate-left"></i> Verzonden E-mails Logboek
            @if($logs->total() > 0)
            <span class="px-1.5 py-0.2 rounded-full text-[10px] font-black bg-white/20 text-white" :class="activeTab === 'logs' ? 'bg-white text-purple-700' : 'bg-slate-200 text-slate-700'">
                {{ $logs->total() }}
            </span>
            @endif
        </button>
        <button type="button" @click="activeTab = 'inbox'"
                :class="activeTab === 'inbox' ? 'bg-purple-600 text-white shadow-xs' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800'"
                class="px-4 py-2 rounded-xl transition flex items-center gap-2">
            <i class="fa-solid fa-inbox"></i> Binnenkomend (IMAP)
            @if($inboxMessages['connected'] && $inboxMessages['count'] > 0)
            <span class="px-1.5 py-0.2 rounded-full text-[10px] font-black bg-emerald-500 text-white">
                {{ $inboxMessages['count'] }}
            </span>
            @endif
        </button>
    </div>

    <!-- TAB 1: COMPOSE / SEND VIA BREVO -->
    <div x-show="activeTab === 'compose'" class="space-y-6">
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 sm:p-8 shadow-xs">
            
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-slate-100 dark:border-slate-800">
                <div>
                    <h3 class="text-base sm:text-lg font-black text-slate-900 dark:text-white flex items-center gap-2">
                        <i class="fa-solid fa-paper-plane text-purple-600"></i> Nieuwe E-mail Versturen via Brevo
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                        Kies een afzenderadres en verstuur direct een professioneel opgemaakte e-mail naar een speler of externe ontvanger.
                    </p>
                </div>

                <!-- Quick Templates Dropdown / Buttons -->
                <div class="flex flex-wrap items-center gap-1.5">
                    <span class="text-[11px] font-bold text-slate-400 mr-1">Sjablonen:</span>
                    <button type="button" @click="applyTemplate('support')"
                            class="px-2.5 py-1.5 rounded-lg bg-emerald-50 text-emerald-700 hover:bg-emerald-100 text-[10px] font-extrabold transition border border-emerald-200">
                        Helpdesk Antwoord
                    </button>
                    <button type="button" @click="applyTemplate('privacy')"
                            class="px-2.5 py-1.5 rounded-lg bg-indigo-50 text-indigo-700 hover:bg-indigo-100 text-[10px] font-extrabold transition border border-indigo-200">
                        AVG / Privacy
                    </button>
                    <button type="button" @click="applyTemplate('sanction')"
                            class="px-2.5 py-1.5 rounded-lg bg-rose-50 text-rose-700 hover:bg-rose-100 text-[10px] font-extrabold transition border border-rose-200">
                        Sanctie / Ban Uitleg
                    </button>
                    <button type="button" @click="applyTemplate('owner')"
                            class="px-2.5 py-1.5 rounded-lg bg-amber-50 text-amber-700 hover:bg-amber-100 text-[10px] font-extrabold transition border border-amber-200">
                        Eigenaar Directie
                    </button>
                </div>
            </div>

            <form action="{{ route('admin.mail.send') }}" method="POST" class="mt-6 space-y-6">
                @csrf

                <!-- Sender Selection -->
                <div class="space-y-2">
                    <label class="block text-xs font-black uppercase tracking-wider text-slate-700 dark:text-slate-300">
                        1. Kies Afzender E-mailadres (From &amp; Reply-To)
                    </label>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                        @foreach($senders as $email => $info)
                        <label class="relative flex items-center gap-3 p-4 rounded-2xl border cursor-pointer transition select-none"
                               :class="selectedSender === '{{ $email }}' 
                                   ? 'border-purple-600 bg-purple-50/50 dark:bg-purple-950/40 ring-2 ring-purple-500/20 shadow-xs' 
                                   : 'border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/40 hover:border-slate-300'">
                            <input type="radio" name="sender_email" value="{{ $email }}" x-model="selectedSender" class="sr-only">
                            <span class="w-8 h-8 rounded-xl flex items-center justify-center text-sm shrink-0"
                                  :class="selectedSender === '{{ $email }}' ? 'bg-purple-600 text-white' : 'bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-300'">
                                <i class="fa-solid {{ $info['icon'] }}"></i>
                            </span>
                            <div class="min-w-0">
                                <span class="block text-xs font-black text-slate-900 dark:text-white truncate">{{ $email }}</span>
                                <span class="block text-[10px] font-semibold text-slate-500 dark:text-slate-400 truncate">{{ $info['name'] }}</span>
                            </div>
                        </label>
                        @endforeach
                    </div>
                </div>

                <!-- Recipient Info -->
                <div class="space-y-4 pt-2">
                    <div class="flex items-center justify-between">
                        <label class="block text-xs font-black uppercase tracking-wider text-slate-700 dark:text-slate-300">
                            2. Ontvanger Gegevens
                        </label>

                        <!-- Quick Player Select Dropdown -->
                        <div class="flex items-center gap-2">
                            <span class="text-[11px] font-bold text-slate-400">Of kies speler:</span>
                            <select @change="selectPlayer($event)" 
                                    class="text-xs font-bold rounded-xl border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-700 dark:text-slate-300 py-1.5 px-3 focus:ring-purple-500">
                                <option value="">-- Zoek geregistreerde speler --</option>
                                @foreach($players as $player)
                                <option value="{{ $player->name }}|{{ $player->email }}">{{ $player->name }} ({{ $player->email }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <span class="text-xs font-bold text-slate-600 dark:text-slate-400">E-mailadres Ontvanger *</span>
                            <input type="email" name="recipient_email" x-model="recipientEmail" required
                                   placeholder="bijv. speler@example.com"
                                   class="w-full text-xs font-bold rounded-xl border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white p-3 focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                        </div>

                        <div class="space-y-1">
                            <span class="text-xs font-bold text-slate-600 dark:text-slate-400">Naam Ontvanger (optioneel)</span>
                            <input type="text" name="recipient_name" x-model="recipientName"
                                   placeholder="bijv. Jan Jansen of KarakterNaam"
                                   class="w-full text-xs font-bold rounded-xl border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white p-3 focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                        </div>
                    </div>
                </div>

                <!-- Subject & Body -->
                <div class="space-y-4 pt-2">
                    <label class="block text-xs font-black uppercase tracking-wider text-slate-700 dark:text-slate-300">
                        3. E-mail Inhoud &amp; Onderwerp
                    </label>

                    <div class="space-y-1">
                        <span class="text-xs font-bold text-slate-600 dark:text-slate-400">Onderwerp *</span>
                        <input type="text" name="subject" x-model="subject" required
                               placeholder="Onderwerp van het bericht..."
                               class="w-full text-xs font-bold rounded-xl border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white p-3 focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                    </div>

                    <div class="space-y-1">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-slate-600 dark:text-slate-400">Berichttekst *</span>
                            <span class="text-[10px] text-slate-400">Alinea's en witregels worden automatisch netjes geformatteerd in de template</span>
                        </div>
                        <textarea name="body" x-model="body" rows="9" required
                                  placeholder="Typ hier de volledige inhoud van het e-mailbericht..."
                                  class="w-full text-xs font-medium rounded-2xl border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white p-4 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 leading-relaxed font-sans"></textarea>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400">
                        <i class="fa-solid fa-shield-halved text-emerald-500"></i>
                        <span>Verzonden via Brevo Relay met DKIM, SPF en SSL beveiliging.</span>
                    </div>

                    <button type="submit" 
                            class="px-7 py-3 rounded-2xl bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white font-black text-xs transition shadow-md flex items-center justify-center gap-2">
                        <i class="fa-solid fa-paper-plane"></i> E-mail Nu Versturen via Brevo
                    </button>
                </div>

            </form>

        </div>
    </div>

    <!-- TAB 2: SENT MAIL LOGS -->
    <div x-show="activeTab === 'logs'" class="space-y-4">
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-xs">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800">
                <div>
                    <h3 class="text-base font-black text-slate-900 dark:text-white flex items-center gap-2">
                        <i class="fa-solid fa-clock-rotate-left text-purple-600"></i> Verzonden Berichten Logboek
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        Overzicht van alle e-mails die via het Medewerkers Paneel zijn verzonden.
                    </p>
                </div>
                <span class="text-xs font-bold text-slate-400">Totaal: {{ $logs->total() }}</span>
            </div>

            @if($logs->isEmpty())
            <div class="text-center py-12 space-y-3">
                <div class="w-16 h-16 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-400 text-2xl mx-auto">
                    <i class="fa-solid fa-envelope-circle-check"></i>
                </div>
                <p class="text-xs font-bold text-slate-500 dark:text-slate-400">
                    Er zijn nog geen e-mails verzonden via dit beheerpaneel.
                </p>
            </div>
            @else
            <div class="overflow-x-auto mt-4">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-slate-100 dark:border-slate-800 text-[10px] font-black uppercase tracking-wider text-slate-400">
                            <th class="py-3 px-3">Datum/Tijd</th>
                            <th class="py-3 px-3">Afzender</th>
                            <th class="py-3 px-3">Ontvanger</th>
                            <th class="py-3 px-3">Onderwerp</th>
                            <th class="py-3 px-3 text-center">Status</th>
                            <th class="py-3 px-3 text-right">Actie</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 font-medium text-slate-700 dark:text-slate-300">
                        @foreach($logs as $log)
                        <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition">
                            <td class="py-3.5 px-3 whitespace-nowrap text-[11px] text-slate-500">
                                {{ $log->created_at->format('d-m-Y H:i') }}
                            </td>
                            <td class="py-3.5 px-3 whitespace-nowrap font-mono text-[11px] text-purple-600 dark:text-purple-400 font-bold">
                                {{ $log->sender_email }}
                            </td>
                            <td class="py-3.5 px-3">
                                <span class="font-bold text-slate-900 dark:text-white block">{{ $log->recipient_name ?: '-' }}</span>
                                <span class="text-[10px] text-slate-400 font-mono">{{ $log->recipient_email }}</span>
                            </td>
                            <td class="py-3.5 px-3 max-w-xs truncate font-bold text-slate-800 dark:text-slate-200">
                                {{ $log->subject }}
                            </td>
                            <td class="py-3.5 px-3 text-center whitespace-nowrap">
                                @if($log->status === 'sent')
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase bg-emerald-100 text-emerald-800 border border-emerald-200">
                                    Verzonden
                                </span>
                                @else
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase bg-rose-100 text-rose-800 border border-rose-200" title="{{ $log->error_message }}">
                                    Mislukt
                                </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-3 text-right whitespace-nowrap">
                                <button type="button" @click="openLogModal({{ $log->id }})"
                                        class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-purple-100 text-slate-700 hover:text-purple-700 text-[11px] font-bold transition">
                                    <i class="fa-solid fa-eye"></i> Bekijk
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4 pt-4 border-t border-slate-100 dark:border-slate-800">
                {{ $logs->links() }}
            </div>
            @endif
        </div>
    </div>

    <!-- TAB 3: INBOX / INCOMING IMAP -->
    <div x-show="activeTab === 'inbox'" class="space-y-4">
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-xs">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-100 dark:border-slate-800">
                <div>
                    <h3 class="text-base font-black text-slate-900 dark:text-white flex items-center gap-2">
                        <i class="fa-solid fa-inbox text-purple-600"></i> Binnenkomende E-mails (Plesk Mailbox)
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                        Berichten die verzonden zijn naar <code>vragen@</code>, <code>privacy@</code> of <code>daniel@tbs-kliniek.nl</code>.
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <a href="{{ $mailboxConfig['webmail_url'] }}" target="_blank" rel="noopener noreferrer"
                       class="px-4 py-2 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-bold text-xs transition shadow-xs flex items-center gap-1.5">
                        <i class="fa-solid fa-arrow-up-right-from-square"></i> Open in Roundcube Webmail
                    </a>
                </div>
            </div>

            @if(!$inboxMessages['connected'])
            <div class="p-4 rounded-2xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800 text-amber-800 dark:text-amber-300 text-xs mt-4">
                <div class="flex items-center gap-2 font-black">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    <span>Lokale IMAP Verbinding</span>
                </div>
                <p class="mt-1 leading-relaxed">
                    {{ $inboxMessages['error'] ?? 'IMAP verbinding kon niet direct worden opgebouwd.' }}<br>
                    Alle inkomende e-mails zijn direct live te beheren via <a href="{{ $mailboxConfig['webmail_url'] }}" target="_blank" class="underline font-bold">Roundcube Webmail</a>.
                </p>
            </div>
            @elseif(empty($inboxMessages['messages']))
            <div class="text-center py-12 space-y-2">
                <div class="w-14 h-14 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-400 text-xl mx-auto">
                    <i class="fa-solid fa-envelope-open"></i>
                </div>
                <h4 class="text-xs font-black text-slate-700 dark:text-slate-300">Geen nieuwe e-mails in de inbox</h4>
                <p class="text-[11px] text-slate-400">De mailbox is momenteel helemaal leeg.</p>
            </div>
            @else
            <div class="overflow-x-auto mt-4">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-slate-100 dark:border-slate-800 text-[10px] font-black uppercase tracking-wider text-slate-400">
                            <th class="py-3 px-3">Datum</th>
                            <th class="py-3 px-3">Afzender</th>
                            <th class="py-3 px-3">Aan</th>
                            <th class="py-3 px-3">Onderwerp</th>
                            <th class="py-3 px-3 text-right">Beantwoorden</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800 font-medium">
                        @foreach($inboxMessages['messages'] as $msg)
                        <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition">
                            <td class="py-3 px-3 text-[11px] text-slate-500 whitespace-nowrap">{{ $msg['date'] }}</td>
                            <td class="py-3 px-3 font-bold text-slate-900 dark:text-white">{{ $msg['from'] }}</td>
                            <td class="py-3 px-3 font-mono text-[10px] text-purple-600 dark:text-purple-400">{{ $msg['to'] }}</td>
                            <td class="py-3 px-3 font-bold text-slate-800 dark:text-slate-200">{{ $msg['subject'] }}</td>
                            <td class="py-3 px-3 text-right whitespace-nowrap">
                                <button type="button" 
                                        @click="activeTab = 'compose'; recipientEmail = '{{ addslashes($msg['from']) }}'; subject = 'Re: {{ addslashes($msg['subject']) }}'; window.scrollTo({top: 400, behavior: 'smooth'})"
                                        class="px-3 py-1 rounded-xl bg-purple-50 dark:bg-purple-950/60 hover:bg-purple-100 text-purple-700 dark:text-purple-300 font-bold text-[11px] transition">
                                    <i class="fa-solid fa-reply"></i> Beantwoorden
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif

        </div>
    </div>

    <!-- LOG DETAIL MODAL -->
    <template x-if="showLogModal && selectedLog">
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/75 backdrop-blur-sm"
             @click.self="showLogModal = false">
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 sm:p-7 max-w-2xl w-full shadow-2xl space-y-5 relative">
                
                <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800">
                    <div>
                        <span class="text-[10px] font-black uppercase text-purple-600 tracking-wider">Verzonden E-mail Details</span>
                        <h4 class="text-base font-black text-slate-900 dark:text-white" x-text="selectedLog.subject"></h4>
                    </div>
                    <button type="button" @click="showLogModal = false" class="text-slate-400 hover:text-slate-600 text-lg">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <div class="grid grid-cols-2 gap-4 text-xs">
                    <div>
                        <span class="text-[10px] uppercase font-bold text-slate-400 block">Afzender:</span>
                        <span class="font-bold text-slate-800 dark:text-slate-200" x-text="selectedLog.sender_name + ' <' + selectedLog.sender_email + '>'"></span>
                    </div>
                    <div>
                        <span class="text-[10px] uppercase font-bold text-slate-400 block">Ontvanger:</span>
                        <span class="font-bold text-slate-800 dark:text-slate-200" x-text="(selectedLog.recipient_name || '-') + ' <' + selectedLog.recipient_email + '>'"></span>
                    </div>
                    <div>
                        <span class="text-[10px] uppercase font-bold text-slate-400 block">Verzonden op:</span>
                        <span class="font-mono text-slate-600 dark:text-slate-400" x-text="selectedLog.created_at"></span>
                    </div>
                    <div>
                        <span class="text-[10px] uppercase font-bold text-slate-400 block">Verzonden door medewerker:</span>
                        <span class="font-bold text-slate-800 dark:text-slate-200" x-text="selectedLog.sender_user"></span>
                    </div>
                </div>

                <div class="space-y-1">
                    <span class="text-[10px] uppercase font-bold text-slate-400 block">Verzonden Inhoud:</span>
                    <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/80 text-xs text-slate-700 dark:text-slate-300 whitespace-pre-wrap leading-relaxed max-h-60 overflow-y-auto font-sans"
                         x-text="selectedLog.body"></div>
                </div>

                <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex justify-end">
                    <button type="button" @click="showLogModal = false"
                            class="px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition">
                        Sluiten
                    </button>
                </div>

            </div>
        </div>
    </template>

</div>
@endsection
