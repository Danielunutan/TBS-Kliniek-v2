@extends('admin.layout')

@section('admin-content')
<div class="space-y-6" x-data="{ activeTab: 'verifications' }">

    <!-- Header Banner -->
    <div class="bg-gradient-to-r from-[#180e38] to-[#120a2e] border border-purple-900/60 rounded-2xl p-6 text-white shadow-md flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-2xl bg-[#5865F2]/20 border border-[#5865F2]/40 flex items-center justify-center text-3xl text-[#5865F2] shrink-0 shadow-inner">
                <i class="fa-brands fa-discord"></i>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h2 class="text-xl font-black tracking-tight font-['Outfit']">TBS Discord Shield • Anti-Alt & Beveiliging</h2>
                    <span class="bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 text-[10px] font-black uppercase px-2 py-0.5 rounded-full">
                        Actief
                    </span>
                </div>
                <p class="text-xs text-purple-200/70 mt-1 max-w-2xl leading-relaxed">
                    Geavanceerd Double Counter alternatief. Voorkomt dat verbannen spelers en trollen met alt-accounts binnenkomen via hardware fingerprinting en Cloudflare Turnstile.
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('discord.verify') }}" target="_blank" class="px-4 py-2 rounded-xl bg-purple-600 hover:bg-purple-500 text-white text-xs font-bold transition flex items-center gap-2 shadow-xs">
                <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i>
                <span>Bekijk Verificatie Poort</span>
            </a>
        </div>
    </div>

    <!-- Metrics Strip -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-xs">
            <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400 block mb-1">Geverifieerde Leden</span>
            <div class="flex items-center justify-between">
                <span class="text-2xl font-black text-slate-900 font-mono">{{ number_format($totalVerified, 0, ',', '.') }}</span>
                <i class="fa-solid fa-circle-check text-emerald-500 text-xl"></i>
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-xs">
            <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400 block mb-1">Alts Onderschept</span>
            <div class="flex items-center justify-between">
                <span class="text-2xl font-black text-rose-600 font-mono">{{ number_format($totalAltsCaught, 0, ',', '.') }}</span>
                <i class="fa-solid fa-user-ninja text-rose-500 text-xl"></i>
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-xs">
            <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400 block mb-1">VPNs Geblokkeerd</span>
            <div class="flex items-center justify-between">
                <span class="text-2xl font-black text-amber-500 font-mono">{{ number_format($totalVpnBlocked, 0, ',', '.') }}</span>
                <i class="fa-solid fa-shield-halved text-amber-500 text-xl"></i>
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-xs">
            <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400 block mb-1">Actieve Bans</span>
            <div class="flex items-center justify-between">
                <span class="text-2xl font-black text-purple-700 font-mono">{{ number_format($totalBans, 0, ',', '.') }}</span>
                <i class="fa-solid fa-ban text-purple-500 text-xl"></i>
            </div>
        </div>
    </div>

    <!-- Navigation Sub-tabs -->
    <div class="bg-white border border-slate-200 rounded-xl p-1.5 flex flex-wrap gap-1 shadow-xs">
        <button type="button" @click="activeTab = 'verifications'" :class="activeTab === 'verifications' ? 'bg-purple-600 text-white' : 'text-slate-600 hover:bg-slate-50'" class="px-4 py-2 rounded-lg text-xs font-bold transition flex items-center gap-2 cursor-pointer">
            <i class="fa-solid fa-users-viewfinder"></i>
            <span>Verificaties & Gelinkte Accounts</span>
        </button>

        <button type="button" @click="activeTab = 'logs'" :class="activeTab === 'logs' ? 'bg-purple-600 text-white' : 'text-slate-600 hover:bg-slate-50'" class="px-4 py-2 rounded-lg text-xs font-bold transition flex items-center gap-2 cursor-pointer">
            <i class="fa-solid fa-clock-rotate-left"></i>
            <span>Audit & Security Logs</span>
        </button>

        <button type="button" @click="activeTab = 'bans'" :class="activeTab === 'bans' ? 'bg-purple-600 text-white' : 'text-slate-600 hover:bg-slate-50'" class="px-4 py-2 rounded-lg text-xs font-bold transition flex items-center gap-2 cursor-pointer">
            <i class="fa-solid fa-ban"></i>
            <span>Zwarte Lijst (Hardware Bans)</span>
        </button>

        <button type="button" @click="activeTab = 'settings'" :class="activeTab === 'settings' ? 'bg-purple-600 text-white' : 'text-slate-600 hover:bg-slate-50'" class="px-4 py-2 rounded-lg text-xs font-bold transition flex items-center gap-2 cursor-pointer">
            <i class="fa-solid fa-gear"></i>
            <span>Bot & Server Instellingen</span>
        </button>
    </div>

    <!-- TAB 1: VERIFICATIONS TABLE -->
    <div x-show="activeTab === 'verifications'" class="space-y-4">
        <div class="bg-white border border-slate-200 rounded-2xl shadow-xs overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="font-extrabold text-sm text-slate-900">Geverifieerde Discord Leden</h3>
                    <p class="text-xs text-slate-500">Overzicht van alle Discord accounts met gekoppelde hardware vingerafdrukken.</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-500 uppercase font-black text-[10px] tracking-wider border-b border-slate-100">
                        <tr>
                            <th class="py-3 px-4">Discord Gebruiker</th>
                            <th class="py-3 px-4">Game Speler</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4">IP & Locatie</th>
                            <th class="py-3 px-4">Apparaat & Browser</th>
                            <th class="py-3 px-4">Geverifieerd Op</th>
                            <th class="py-3 px-4 text-right">Actie</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @forelse($verifications as $v)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="py-3 px-4">
                                <div class="flex items-center gap-2.5">
                                    <img src="{{ $v->avatar_url }}" alt="{{ $v->username }}" class="w-8 h-8 rounded-full object-cover border border-slate-200 shrink-0">
                                    <div>
                                        <span class="font-bold text-slate-900 block leading-tight">{{ $v->username }}</span>
                                        <span class="text-[10px] font-mono text-slate-400">ID: {{ $v->discord_id }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3 px-4">
                                @if($v->character)
                                <span class="font-bold text-purple-700">{{ $v->character->name }}</span>
                                <span class="text-[10px] text-slate-400 block">Lvl {{ $v->character->level }}</span>
                                @else
                                <span class="text-slate-400 italic">Niet ingelogd</span>
                                @endif
                            </td>
                            <td class="py-3 px-4">
                                @if($v->status === 'verified')
                                <span class="inline-flex items-center gap-1 text-[10px] font-extrabold uppercase bg-emerald-50 text-emerald-700 border border-emerald-200 px-2 py-0.5 rounded-full">
                                    <i class="fa-solid fa-check text-[9px]"></i> Geverifieerd
                                </span>
                                @elseif($v->status === 'alt_detected')
                                <span class="inline-flex items-center gap-1 text-[10px] font-extrabold uppercase bg-rose-50 text-rose-700 border border-rose-200 px-2 py-0.5 rounded-full" title="Alt van: {{ $v->alt_of_username }}">
                                    <i class="fa-solid fa-triangle-exclamation text-[9px]"></i> Alt Gedetecteerd
                                </span>
                                @elseif($v->status === 'vpn_blocked')
                                <span class="inline-flex items-center gap-1 text-[10px] font-extrabold uppercase bg-amber-50 text-amber-700 border border-amber-200 px-2 py-0.5 rounded-full">
                                    <i class="fa-solid fa-shield-halved text-[9px]"></i> VPN Geblokkeerd
                                </span>
                                @else
                                <span class="inline-flex items-center gap-1 text-[10px] font-extrabold uppercase bg-slate-100 text-slate-700 px-2 py-0.5 rounded-full">
                                    {{ $v->status }}
                                </span>
                                @endif
                            </td>
                            <td class="py-3 px-4">
                                <span class="font-mono text-slate-900 block font-semibold">{{ $v->ip_address }}</span>
                                <span class="text-[10px] text-slate-400">{{ $v->country_code ?? 'NL' }} @if($v->is_vpn) <span class="text-rose-500 font-bold">(VPN)</span> @endif</span>
                            </td>
                            <td class="py-3 px-4">
                                <span class="font-bold text-slate-800 block">{{ $v->os ?? 'Windows' }} • {{ $v->browser ?? 'Chrome' }}</span>
                                <span class="text-[10px] font-mono text-slate-400">Hash: {{ substr($v->fingerprint_hash, 0, 10) }}...</span>
                            </td>
                            <td class="py-3 px-4 font-mono text-slate-500 text-[11px]">
                                {{ $v->verified_at ? $v->verified_at->format('d-m-Y H:i') : $v->created_at->format('d-m-Y H:i') }}
                            </td>
                            <td class="py-3 px-4 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    @if($v->status !== 'verified')
                                    <form method="POST" action="{{ route('admin.discord_shield.approve', $v->id) }}">
                                        @csrf
                                        <button type="submit" class="px-2.5 py-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-[10px] font-extrabold transition" title="Handmatig Goedkeuren & Rol Toekennen">
                                            Goedkeuren
                                        </button>
                                    </form>
                                    @endif

                                    <form method="POST" action="{{ route('admin.discord_shield.ban') }}"
                                          data-confirm="Weet je zeker dat je dit Discord ID en de hardware-vingerafdruk wilt bannen?"
                                          data-confirm-title="Discord ID Bannen"
                                          data-confirm-btn="Ja, Bannen"
                                          data-confirm-type="danger"
                                          data-confirm-icon="fa-gavel">
                                        @csrf
                                        <input type="hidden" name="type" value="discord_id">
                                        <input type="hidden" name="value" value="{{ $v->discord_id }}">
                                        <input type="hidden" name="reason" value="Permanente ban opgelegd door staff">
                                        <button type="submit" class="px-2 py-1 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 rounded-lg text-[10px] font-extrabold transition">
                                            Ban ID
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-400">
                                Nog geen Discord verificaties geregistreerd.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($verifications->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $verifications->links() }}
            </div>
            @endif
        </div>
    </div>

    <!-- TAB 2: AUDIT LOGS -->
    <div x-show="activeTab === 'logs'" class="space-y-4">
        <div class="bg-white border border-slate-200 rounded-2xl shadow-xs overflow-hidden">
            <div class="p-5 border-b border-slate-100">
                <h3 class="font-extrabold text-sm text-slate-900">Beveiligingslogboek</h3>
                <p class="text-xs text-slate-500">Real-time log van alle verificaties, alt-detecties, mislukte checks en moderator overrides.</p>
            </div>

            <div class="divide-y divide-slate-100">
                @forelse($logs as $log)
                <div class="p-4 flex items-start justify-between gap-4 hover:bg-slate-50/70 transition">
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-xl flex items-center justify-center text-xs shrink-0 mt-0.5
                            {{ $log->severity === 'success' ? 'bg-emerald-50 text-emerald-600 border border-emerald-200' : '' }}
                            {{ $log->severity === 'danger' ? 'bg-rose-50 text-rose-600 border border-rose-200' : '' }}
                            {{ $log->severity === 'warning' ? 'bg-amber-50 text-amber-600 border border-amber-200' : '' }}
                            {{ $log->severity === 'info' ? 'bg-blue-50 text-blue-600 border border-blue-200' : '' }}">
                            <i class="fa-solid 
                                {{ $log->severity === 'success' ? 'fa-check' : '' }}
                                {{ $log->severity === 'danger' ? 'fa-triangle-exclamation' : '' }}
                                {{ $log->severity === 'warning' ? 'fa-shield-halved' : '' }}
                                {{ $log->severity === 'info' ? 'fa-info' : '' }}"></i>
                        </div>
                        <div>
                            <span class="font-bold text-xs text-slate-900 block">{{ $log->description }}</span>
                            <div class="flex items-center gap-2 mt-1 text-[11px] text-slate-400">
                                @if($log->username) <span>Gebruiker: <strong class="text-slate-700">{{ $log->username }}</strong></span> &bull; @endif
                                <span>IP: <strong class="text-slate-700 font-mono">{{ $log->ip_address ?? 'Onbekend' }}</strong></span>
                                @if($log->fingerprint_hash) &bull; <span>Hash: <span class="font-mono">{{ substr($log->fingerprint_hash, 0, 8) }}</span></span> @endif
                            </div>
                        </div>
                    </div>

                    <span class="text-[11px] font-mono text-slate-400 shrink-0">
                        {{ $log->created_at->diffForHumans() }}
                    </span>
                </div>
                @empty
                <div class="p-8 text-center text-slate-400 text-xs">
                    Nog geen security logs vastgelegd.
                </div>
                @endforelse
            </div>

            @if($logs->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $logs->links() }}
            </div>
            @endif
        </div>
    </div>

    <!-- TAB 3: BANS / BLACKLIST -->
    <div x-show="activeTab === 'bans'" class="space-y-6">
        <!-- Add Ban Box -->
        <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-xs">
            <h3 class="font-extrabold text-sm text-slate-900 mb-1">Handmatige Ban Toevoegen</h3>
            <p class="text-xs text-slate-500 mb-4">Blokkeer een specifiek Discord ID, IP-adres of hardware-vingerafdruk permanent.</p>

            <form method="POST" action="{{ route('admin.discord_shield.ban') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                @csrf
                <div>
                    <label class="block text-[10px] font-extrabold uppercase text-slate-400 mb-1">Type</label>
                    <select name="type" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-semibold">
                        <option value="discord_id">Discord ID</option>
                        <option value="ip_address">IP Adres</option>
                        <option value="fingerprint">Hardware Vingerafdruk Hash</option>
                    </select>
                </div>

                <div>
                    <label class="block text-[10px] font-extrabold uppercase text-slate-400 mb-1">Waarde / ID</label>
                    <input type="text" name="value" placeholder="bijv. 1558628995439 of 192.168.1.1" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-semibold">
                </div>

                <div>
                    <label class="block text-[10px] font-extrabold uppercase text-slate-400 mb-1">Reden (Optioneel)</label>
                    <input type="text" name="reason" placeholder="bijv. Ban evasion / overlast" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-semibold">
                </div>

                <div class="flex items-end">
                    <button type="submit" class="w-full bg-rose-600 hover:bg-rose-700 text-white font-extrabold py-2 px-4 rounded-xl text-xs transition shadow-xs cursor-pointer">
                        <i class="fa-solid fa-ban mr-1"></i> Ban Toevoegen
                    </button>
                </div>
            </form>
        </div>

        <!-- Bans List Table -->
        <div class="bg-white border border-slate-200 rounded-2xl shadow-xs overflow-hidden">
            <div class="p-5 border-b border-slate-100">
                <h3 class="font-extrabold text-sm text-slate-900">Actieve Zwarte Lijst</h3>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-500 uppercase font-black text-[10px] tracking-wider border-b border-slate-100">
                        <tr>
                            <th class="py-3 px-4">Type</th>
                            <th class="py-3 px-4">Waarde</th>
                            <th class="py-3 px-4">Reden</th>
                            <th class="py-3 px-4">Verbannen Door</th>
                            <th class="py-3 px-4">Datum</th>
                            <th class="py-3 px-4 text-right">Actie</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @forelse($bans as $ban)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="py-3 px-4 font-bold uppercase text-[10px]">
                                <span class="bg-slate-100 px-2 py-0.5 rounded">{{ $ban->type }}</span>
                            </td>
                            <td class="py-3 px-4 font-mono font-bold text-slate-900">{{ $ban->value }}</td>
                            <td class="py-3 px-4 text-slate-600">{{ $ban->reason ?? 'Geen reden opgegeven' }}</td>
                            <td class="py-3 px-4 font-bold text-purple-700">{{ $ban->banned_by ?? 'Systeem' }}</td>
                            <td class="py-3 px-4 font-mono text-slate-500 text-[11px]">{{ $ban->created_at->format('d-m-Y H:i') }}</td>
                            <td class="py-3 px-4 text-right">
                                <form method="POST" action="{{ route('admin.discord_shield.unban', $ban->id) }}"
                                      data-confirm="Weet je zeker dat je deze ban wilt opheffen?"
                                      data-confirm-title="Ban Opheffen"
                                      data-confirm-btn="Ja, Opheffen"
                                      data-confirm-type="warning"
                                      data-confirm-icon="fa-lock-open">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-[10px] font-extrabold transition">
                                        Opheffen
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400">
                                Geen actieve bans op de zwarte lijst.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- TAB 4: SETTINGS & DISCORD POST -->
    <div x-show="activeTab === 'settings'" class="space-y-6">

        <!-- Discord Embed Poster Widget -->
        <div class="bg-gradient-to-r from-purple-50 to-indigo-50 border border-purple-200 rounded-2xl p-5 shadow-xs flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-bullhorn text-purple-600"></i>
                    <h3 class="font-extrabold text-sm text-slate-900">Verificatie Knop in Discord Kanaal Plaatsen</h3>
                </div>
                <p class="text-xs text-slate-600 mt-1">
                    Laat de bot direct de officiële TBS Kliniek verificatie-embed met knop in jullie <code>#verifieer</code> kanaal posten.
                </p>
            </div>

            <form method="POST" action="{{ route('admin.discord_shield.post_message') }}" class="flex items-center gap-2 w-full md:w-auto">
                @csrf
                <input type="text" name="channel_id" value="{{ $settings['verify_channel_id'] }}" placeholder="Kanaal ID (bijv. 123456789)" class="bg-white border border-purple-300 rounded-xl px-3 py-2 text-xs font-semibold min-w-[200px]" required>
                <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white font-extrabold px-4 py-2 rounded-xl text-xs transition shadow-xs shrink-0 cursor-pointer">
                    <i class="fa-solid fa-paper-plane mr-1"></i> Plaats Bericht
                </button>
            </form>
        </div>

        <!-- Main Settings Form -->
        <form method="POST" action="{{ route('admin.discord_shield.settings') }}" class="bg-white border border-slate-200 rounded-2xl p-6 shadow-xs space-y-6">
            @csrf

            <div>
                <h3 class="font-black text-base text-slate-900 font-['Outfit']">Discord API & Bot Gegevens</h3>
                <p class="text-xs text-slate-500 mt-0.5">Vul hier de Bot Token en Server IDs in van je Discord server.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-extrabold text-slate-700 mb-1">Discord Bot Token</label>
                    <input type="password" name="bot_token" value="{{ $settings['bot_token'] }}" placeholder="Plak hier je Bot Token van discord.com/developers" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs font-mono font-semibold">
                    <span class="text-[10px] text-slate-400 mt-1 block">Te vinden onder Developer Portal ➔ Bot ➔ Reset Token.</span>
                </div>

                <div>
                    <label class="block text-xs font-extrabold text-slate-700 mb-1">Discord Server ID (Guild ID)</label>
                    <input type="text" name="guild_id" value="{{ $settings['guild_id'] }}" placeholder="bijv. 109283746592837465" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs font-mono font-semibold">
                    <span class="text-[10px] text-slate-400 mt-1 block">Rechtermuisknop op je Discord servericoon ➔ Kopieer server-ID.</span>
                </div>

                <div>
                    <label class="block text-xs font-extrabold text-slate-700 mb-1">Geverifieerde Rol ID (Verleend aan leden)</label>
                    <input type="text" name="verified_role_id" value="{{ $settings['verified_role_id'] }}" placeholder="bijv. 129384756102938475" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs font-mono font-semibold">
                    <span class="text-[10px] text-slate-400 mt-1 block">Serverinstellingen ➔ Rollen ➔ Rechtermuisknop op rol ➔ Kopieer rol-ID.</span>
                </div>

                <div>
                    <label class="block text-xs font-extrabold text-slate-700 mb-1">Niet-Geverifieerde Rol ID (Optioneel: wordt verwijderd)</label>
                    <input type="text" name="unverified_role_id" value="{{ $settings['unverified_role_id'] }}" placeholder="bijv. 129384756102938476" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs font-mono font-semibold">
                    <span class="text-[10px] text-slate-400 mt-1 block">Als je een speciale rol gebruikt voor nieuwe leden.</span>
                </div>

                <div>
                    <label class="block text-xs font-extrabold text-slate-700 mb-1">Mod Log Kanaal ID (Voor staff rapporten)</label>
                    <input type="text" name="log_channel_id" value="{{ $settings['log_channel_id'] }}" placeholder="bijv. 129384756102938477" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs font-mono font-semibold">
                    <span class="text-[10px] text-slate-400 mt-1 block">Kanaal waarin alts, VPN's en logs gemeld worden.</span>
                </div>

                <div>
                    <label class="block text-xs font-extrabold text-slate-700 mb-1">Verificatie Kanaal ID (Waar de knop staat)</label>
                    <input type="text" name="verify_channel_id" value="{{ $settings['verify_channel_id'] }}" placeholder="bijv. 129384756102938478" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs font-mono font-semibold">
                    <span class="text-[10px] text-slate-400 mt-1 block">Het openbare kanaal waar nieuwe leden binnenkomen.</span>
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100">
                <h4 class="font-extrabold text-sm text-slate-900 mb-3">Beveiligingsbeleid & Automatisering</h4>

                <div class="space-y-3">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" name="block_alts" value="1" {{ $settings['block_alts'] === '1' ? 'checked' : '' }} class="w-4 h-4 rounded text-purple-600 focus:ring-purple-500 border-slate-300">
                        <div>
                            <span class="text-xs font-extrabold text-slate-800 block">Blokkeer Dubbele Accounts (Anti-Alt)</span>
                            <span class="text-[11px] text-slate-500">Weigert verificatie als hetzelfde apparaat/vingerafdruk al aan een ander Discord-account is gekoppeld.</span>
                        </div>
                    </label>

                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" name="auto_kick_alts" value="1" {{ $settings['auto_kick_alts'] === '1' ? 'checked' : '' }} class="w-4 h-4 rounded text-purple-600 focus:ring-purple-500 border-slate-300">
                        <div>
                            <span class="text-xs font-extrabold text-slate-800 block">Kick Alts Automatisch uit de Discord Server</span>
                            <span class="text-[11px] text-slate-500">Als iemand met een alt probeert te verifiëren, kickt de bot het nieuwe account direct uit de server.</span>
                        </div>
                    </label>

                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" name="block_vpns" value="1" {{ $settings['block_vpns'] === '1' ? 'checked' : '' }} class="w-4 h-4 rounded text-purple-600 focus:ring-purple-500 border-slate-300">
                        <div>
                            <span class="text-xs font-extrabold text-slate-800 block">Blokkeer VPN & Proxy verbindingen</span>
                            <span class="text-[11px] text-slate-500">Dwingt gebruikers om hun VPN uit te zetten tijdens de verificatie zodat hun echte IP geregistreerd wordt.</span>
                        </div>
                    </label>

                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" name="turnstile_enabled" value="1" {{ $settings['turnstile_enabled'] === '1' ? 'checked' : '' }} class="w-4 h-4 rounded text-purple-600 focus:ring-purple-500 border-slate-300">
                        <div>
                            <span class="text-xs font-extrabold text-slate-800 block">Cloudflare Turnstile Bot-Beveiliging Actief</span>
                            <span class="text-[11px] text-slate-500">Voorkomt dat geautomatiseerde bots het verificatiesysteem kunnen spammen.</span>
                        </div>
                    </label>
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100 flex items-center justify-end">
                <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white font-extrabold px-6 py-2.5 rounded-xl text-xs transition shadow-xs flex items-center gap-2 cursor-pointer">
                    <i class="fa-solid fa-floppy-disk"></i>
                    <span>Instellingen Opslaan</span>
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
