@extends('admin.layout')

@section('admin-content')
<div class="space-y-6" x-data="{ 
    activeTab: 'bans', 
    showQuickAction: false, 
    quickMode: 'ban', 
    selectedUserId: '', 
    selectedUserName: '' 
}">

    <!-- Stats Row -->
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
        <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-xs flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-lg shrink-0">
                <i class="fa-solid fa-ban"></i>
            </div>
            <div>
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Actieve Verbanningen</span>
                <span class="text-xl font-black text-rose-700">{{ $totalBannedCount }}</span>
                <span class="text-[10px] text-slate-500 block">Geblokkeerd van toegang</span>
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-xs flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-lg shrink-0">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            <div>
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Waarschuwingen Uitgedeeld</span>
                <span class="text-xl font-black text-amber-700">{{ $totalWarningsCount }}</span>
                <span class="text-[10px] text-slate-500 block">Dossier berispingen</span>
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-xs flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-lg shrink-0">
                <i class="fa-solid fa-bell"></i>
            </div>
            <div>
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Wacht op Bevestiging</span>
                <span class="text-xl font-black text-purple-700">{{ $unackedWarningsCount }}</span>
                <span class="text-[10px] text-slate-500 block">Nog niet afgevinkt door speler</span>
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-xs flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-xl bg-red-50 text-red-600 flex items-center justify-center text-lg shrink-0">
                <i class="fa-solid fa-flag"></i>
            </div>
            <div>
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Openstaande Meldingen</span>
                <span class="text-xl font-black text-red-600">{{ $pendingReportsCount }}</span>
                <span class="text-[10px] text-slate-500 block">Spelers, bendes & chat</span>
            </div>
        </div>
    </div>

    <!-- Main Navigation & Search Bar -->
    <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-xs flex flex-col sm:flex-row items-center justify-between gap-4">
        
        <!-- Tab Switcher -->
        <div class="bg-slate-100 p-1 rounded-xl flex flex-wrap gap-1 w-full sm:w-auto">
            <button type="button" @click="activeTab = 'bans'"
                    :class="activeTab === 'bans' ? 'bg-white text-rose-700 font-extrabold shadow-2xs' : 'text-slate-600 hover:text-slate-900 font-bold'"
                    class="px-4 py-2 text-xs rounded-lg transition cursor-pointer flex items-center gap-1.5">
                <i class="fa-solid fa-ban text-rose-500"></i>
                <span>Verbanningen ({{ $totalBannedCount }})</span>
            </button>
            <button type="button" @click="activeTab = 'warnings'"
                    :class="activeTab === 'warnings' ? 'bg-white text-amber-700 font-extrabold shadow-2xs' : 'text-slate-600 hover:text-slate-900 font-bold'"
                    class="px-4 py-2 text-xs rounded-lg transition cursor-pointer flex items-center gap-1.5">
                <i class="fa-solid fa-triangle-exclamation text-amber-500"></i>
                <span>Waarschuwingen ({{ $totalWarningsCount }})</span>
            </button>
            <button type="button" @click="activeTab = 'reports'"
                    :class="activeTab === 'reports' ? 'bg-white text-red-700 font-extrabold shadow-2xs' : 'text-slate-600 hover:text-slate-900 font-bold'"
                    class="px-4 py-2 text-xs rounded-lg transition cursor-pointer flex items-center gap-1.5">
                <i class="fa-solid fa-flag text-red-500"></i>
                <span>Meldingen ({{ $pendingReportsCount }})</span>
                @if($pendingReportsCount > 0)
                    <span class="w-2 h-2 rounded-full bg-red-500 animate-ping"></span>
                @endif
            </button>
        </div>

        <!-- Search Form -->
        <form method="GET" action="{{ route('admin.moderation.index') }}" class="w-full sm:w-auto flex items-center gap-2">
            <div class="relative flex-1 sm:w-64">
                <input type="text" name="search" value="{{ $search }}" placeholder="Zoek op naam, email of reden..." 
                       class="w-full bg-slate-50 border border-slate-300 rounded-lg px-3.5 py-2 text-xs font-bold text-slate-900 focus:outline-none focus:ring-1 focus:ring-rose-500 focus:bg-white">
                <i class="fa-solid fa-magnifying-glass absolute right-3 top-2.5 text-slate-400 text-xs"></i>
            </div>
            <button type="submit" class="px-4 py-2 rounded-lg bg-slate-900 hover:bg-black text-white text-xs font-bold transition">
                Zoeken
            </button>
            @if($search)
            <a href="{{ route('admin.moderation.index') }}" class="px-3 py-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-bold transition">
                Wissen
            </a>
            @endif
        </form>

    </div>

    <!-- TAB 1: ACTIEVE VERBANNINGEN (BANS) -->
    <div x-show="activeTab === 'bans'" class="space-y-4">
        <div class="bg-white border border-slate-200 rounded-xl shadow-xs overflow-hidden">
            <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="text-xs font-extrabold text-slate-900 uppercase tracking-wider flex items-center gap-1.5">
                        <i class="fa-solid fa-ban text-rose-500"></i> Actueel Verbannen Spelers
                    </h3>
                    <p class="text-[11px] text-slate-500">Deze accounts hebben geen toegang tot de spelwereld.</p>
                </div>
                <a href="{{ route('admin.players') }}" class="text-xs text-purple-700 font-bold hover:underline flex items-center gap-1">
                    <i class="fa-solid fa-users text-xs"></i> Naar Spelerslijst
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="bg-slate-50 text-[10px] font-extrabold uppercase tracking-wider text-slate-400 border-b border-slate-200">
                            <th class="py-3 px-4">PATIËNT</th>
                            <th class="py-3 px-3">VERBANNEN DOOR</th>
                            <th class="py-3 px-3">TYPE / DUUR</th>
                            <th class="py-3 px-3">OPGEGEVEN REDEN</th>
                            <th class="py-3 px-3">DATUM VERBANNEN</th>
                            <th class="py-3 px-4 text-right">ACTIE</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        @forelse($activeBans as $bannedUser)
                        <tr class="hover:bg-rose-50/40 transition">
                            <td class="py-3 px-4">
                                <div class="flex items-center gap-3">
                                    <img src="{{ $bannedUser->character?->avatar_url ?? asset('assets/characters/avatar_male_1.jpg') }}" class="w-8 h-8 rounded-full object-cover border border-slate-200">
                                    <div>
                                        <div class="font-extrabold text-slate-900">{{ $bannedUser->character?->name ?? $bannedUser->name }}</div>
                                        <div class="text-[10px] text-slate-400 font-mono">{{ $bannedUser->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3 px-3 text-slate-700">
                                @if($bannedUser->bannedBy)
                                    <span class="font-bold text-slate-900">{{ $bannedUser->bannedBy->character?->name ?? $bannedUser->bannedBy->name }}</span>
                                @else
                                    <span class="text-slate-400">Directie / Systeem</span>
                                @endif
                            </td>
                            <td class="py-3 px-3">
                                @if($bannedUser->banned_until)
                                    <span class="bg-amber-100 text-amber-800 text-[10px] font-extrabold px-2 py-0.5 rounded-full inline-flex items-center gap-1 border border-amber-200" title="Tot {{ $bannedUser->banned_until->isoFormat('D MMMM YYYY HH:mm') }}">
                                        <i class="fa-solid fa-hourglass-half"></i> Tot {{ $bannedUser->banned_until->diffForHumans() }}
                                    </span>
                                @else
                                    <span class="bg-rose-100 text-rose-800 text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded-full inline-flex items-center gap-1 border border-rose-200">
                                        <i class="fa-solid fa-skull"></i> Permanent
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-3 max-w-xs">
                                <span class="text-slate-800 text-xs font-semibold truncate block" title="{{ $bannedUser->ban_reason }}">
                                    {{ $bannedUser->ban_reason ?: 'Geen reden ingevoerd' }}
                                </span>
                            </td>
                            <td class="py-3 px-3 text-slate-500 text-[11px] font-mono">
                                {{ $bannedUser->banned_at ? $bannedUser->banned_at->isoFormat('D MMM YYYY [om] HH:mm') : '-' }}
                            </td>
                                <form action="{{ route('admin.moderation.unban', $bannedUser) }}" method="POST"
                                      data-confirm="Weet je zeker dat je de ban van {{ $bannedUser->character?->name ?? $bannedUser->name }} wilt opheffen?"
                                      data-confirm-title="Verbanning Opheffen"
                                      data-confirm-btn="Ja, Ban Opheffen"
                                      data-confirm-type="warning"
                                      data-confirm-icon="fa-lock-open">
                                    @csrf
                                    <button type="submit" class="px-3 py-1.5 rounded-lg bg-emerald-50 hover:bg-emerald-600 hover:text-white text-emerald-700 text-xs font-bold transition cursor-pointer shadow-2xs">
                                        <i class="fa-solid fa-lock-open mr-1"></i> Ban Opheffen
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400 text-xs">
                                <i class="fa-solid fa-shield-check text-emerald-500 text-xl block mb-1"></i>
                                Er zijn momenteel geen actieve verbanningen op TBS Kliniek.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-slate-100 flex justify-end">
                {{ $activeBans->links() }}
            </div>
        </div>
    </div>

    <!-- TAB 2: RECENTE WAARSCHUWINGEN (WARNINGS) -->
    <div x-show="activeTab === 'warnings'" class="space-y-4" x-cloak>
        <div class="bg-white border border-slate-200 rounded-xl shadow-xs overflow-hidden">
            <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="text-xs font-extrabold text-slate-900 uppercase tracking-wider flex items-center gap-1.5">
                        <i class="fa-solid fa-triangle-exclamation text-amber-500"></i> Dossier Waarschuwingen
                    </h3>
                    <p class="text-[11px] text-slate-500">Overzicht van officiële waarschuwingen en gelezen-bevestigingen.</p>
                </div>
                <a href="{{ route('admin.players') }}" class="text-xs text-purple-700 font-bold hover:underline flex items-center gap-1">
                    <i class="fa-solid fa-users text-xs"></i> Naar Spelerslijst
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="bg-slate-50 text-[10px] font-extrabold uppercase tracking-wider text-slate-400 border-b border-slate-200">
                            <th class="py-3 px-4">PATIËNT</th>
                            <th class="py-3 px-3">UITGEDEELD DOOR</th>
                            <th class="py-3 px-3">REDEN / BERICHT</th>
                            <th class="py-3 px-3 text-center">STATUS</th>
                            <th class="py-3 px-3">DATUM</th>
                            <th class="py-3 px-4 text-right">ACTIE</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        @forelse($recentWarnings as $warning)
                        <tr class="hover:bg-amber-50/30 transition">
                            <td class="py-3 px-4">
                                <div class="flex items-center gap-3">
                                    <img src="{{ $warning->user?->character?->avatar_url ?? asset('assets/characters/avatar_male_1.jpg') }}" class="w-8 h-8 rounded-full object-cover border border-slate-200">
                                    <div>
                                        <div class="font-extrabold text-slate-900">{{ $warning->user?->character?->name ?? $warning->user?->name ?? 'Onbekend' }}</div>
                                        <div class="text-[10px] text-slate-400 font-mono">{{ $warning->user?->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3 px-3 text-slate-700">
                                @if($warning->issuer)
                                    <span class="font-bold text-slate-900">{{ $warning->issuer->character?->name ?? $warning->issuer->name }}</span>
                                @else
                                    <span class="text-slate-400">Directie / Systeem</span>
                                @endif
                            </td>
                            <td class="py-3 px-3 max-w-sm">
                                <p class="text-slate-800 text-xs leading-relaxed font-semibold">
                                    "{{ $warning->reason }}"
                                </p>
                            </td>
                            <td class="py-3 px-3 text-center">
                                @if($warning->isAcknowledged())
                                    <span class="bg-emerald-100 text-emerald-800 text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded-full inline-flex items-center gap-1 border border-emerald-200" title="Gelezen op {{ $warning->acknowledged_at->isoFormat('D MMM YYYY HH:mm') }}">
                                        <i class="fa-solid fa-check"></i> Bevestigd
                                    </span>
                                @else
                                    <span class="bg-amber-100 text-amber-800 text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded-full inline-flex items-center gap-1 border border-amber-200">
                                        <i class="fa-solid fa-clock"></i> Openstaand
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-3 text-slate-500 text-[11px] font-mono">
                                {{ $warning->created_at->isoFormat('D MMM YYYY [om] HH:mm') }}
                            </td>
                            <td class="py-3 px-4 text-right">
                                <form action="{{ route('admin.moderation.warning.destroy', $warning) }}" method="POST"
                                      data-confirm="Weet je zeker dat je deze waarschuwing uit het dossier wilt wissen?"
                                      data-confirm-title="Waarschuwing Wissen"
                                      data-confirm-btn="Ja, Wissen"
                                      data-confirm-type="danger"
                                      data-confirm-icon="fa-trash">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-2.5 py-1 rounded-lg bg-rose-50 hover:bg-rose-600 hover:text-white text-rose-700 text-xs font-bold transition cursor-pointer shadow-2xs" title="Wis waarschuwing">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400 text-xs">
                                Geen waarschuwingen gevonden.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-slate-100 flex justify-end">
                {{ $recentWarnings->links() }}
            </div>
        </div>
    </div>

    <!-- TAB 3: SPELER & BENDE & CHAT MELDINGEN (REPORTS) -->
    <div x-show="activeTab === 'reports'" class="space-y-4">
        <div class="bg-white border border-slate-200 rounded-xl shadow-xs overflow-hidden">
            <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="text-xs font-extrabold text-slate-900 uppercase tracking-wider flex items-center gap-1.5">
                        <i class="fa-solid fa-flag text-red-500"></i> Binnengekomen Rapporten & Meldingen
                    </h3>
                    <p class="text-[11px] text-slate-500">Ingezonden door patiënten via profielen, bendes of live chat berichten.</p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="px-2.5 py-1 rounded-full text-[10px] font-black bg-red-100 text-red-700 border border-red-200">
                        {{ $pendingReportsCount }} openstaand
                    </span>
                    <span class="px-2.5 py-1 rounded-full text-[10px] font-black bg-slate-100 text-slate-600 border border-slate-200">
                        {{ $totalReportsCount }} totaal
                    </span>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 text-[10px] uppercase tracking-wider">
                            <th class="py-3 px-4 font-extrabold">Status</th>
                            <th class="py-3 px-4 font-extrabold">Onderwerp / Type</th>
                            <th class="py-3 px-4 font-extrabold">Geraporteerde</th>
                            <th class="py-3 px-4 font-extrabold">Reden & Toelichting</th>
                            <th class="py-3 px-4 font-extrabold">Melder</th>
                            <th class="py-3 px-4 font-extrabold">Datum</th>
                            <th class="py-3 px-4 font-extrabold text-right">Acties</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($reports as $report)
                        <tr class="hover:bg-slate-50/80 transition {{ $report->status === 'pending' ? 'bg-red-50/20' : '' }}">
                            <td class="py-3 px-4">
                                @if($report->status === 'pending')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-black bg-rose-100 text-rose-700 border border-rose-200 animate-pulse">
                                        <i class="fa-solid fa-clock"></i> Nieuw
                                    </span>
                                @elseif($report->status === 'resolved')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-black bg-emerald-100 text-emerald-700 border border-emerald-200">
                                        <i class="fa-solid fa-check"></i> Afgehandeld
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-black bg-slate-100 text-slate-600 border border-slate-200">
                                        <i class="fa-solid fa-xmark"></i> Verworpen
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-4">
                                @if($report->reported_type === 'character')
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-extrabold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                        <i class="fa-solid fa-user"></i> Speler Profiel
                                    </span>
                                @elseif($report->reported_type === 'gang')
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-extrabold bg-purple-50 text-purple-700 border border-purple-200">
                                        <i class="fa-solid fa-shield-halved"></i> Bende / Gang
                                    </span>
                                @elseif($report->reported_type === 'chat_message')
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-extrabold bg-amber-50 text-amber-700 border border-amber-200">
                                        <i class="fa-solid fa-comments"></i> Chatbericht
                                    </span>
                                @else
                                    <span class="text-slate-500 font-mono">{{ $report->reported_type }}</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 font-bold text-slate-800">
                                @if($report->reported_type === 'character')
                                    <a href="{{ route('profile.show', $report->reported_name ?? $report->reported_id) }}" target="_blank" class="text-purple-600 hover:underline flex items-center gap-1">
                                        <span>{{ $report->reported_name ?? ('Speler #'.$report->reported_id) }}</span>
                                        <i class="fa-solid fa-arrow-up-right-from-square text-[9px]"></i>
                                    </a>
                                @elseif($report->reported_type === 'gang')
                                    <a href="{{ route('gangs.index') }}" target="_blank" class="text-purple-600 hover:underline flex items-center gap-1">
                                        <span>{{ $report->reported_name ?? ('Bende #'.$report->reported_id) }}</span>
                                        <i class="fa-solid fa-arrow-up-right-from-square text-[9px]"></i>
                                    </a>
                                @else
                                    <span class="font-extrabold text-slate-700">{{ $report->reported_name }}</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 max-w-xs">
                                <div class="font-bold text-rose-700">{{ $report->reason }}</div>
                                @if($report->details)
                                    <p class="text-[11px] text-slate-600 mt-0.5 line-clamp-2 italic">"{{ $report->details }}"</p>
                                @endif
                                @if($report->mod_notes)
                                    <div class="text-[10px] text-purple-700 font-semibold mt-1 bg-purple-50 p-1 rounded border border-purple-100">
                                        Notitie: {{ $report->mod_notes }}
                                    </div>
                                @endif
                            </td>
                            <td class="py-3 px-4">
                                @if($report->reporter)
                                    <a href="{{ route('profile.show', $report->reporter->name) }}" target="_blank" class="font-bold text-slate-700 hover:text-purple-600 hover:underline">
                                        {{ $report->reporter->name }}
                                    </a>
                                @else
                                    <span class="text-slate-400">Onbekend</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-slate-500 text-[11px] font-mono whitespace-nowrap">
                                {{ $report->created_at->isoFormat('D MMM YYYY [om] HH:mm') }}
                            </td>
                            <td class="py-3 px-4 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-1.5">
                                    @if($report->status === 'pending')
                                    <form action="{{ route('admin.moderation.report.resolve', $report) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="px-2 py-1 rounded-lg bg-emerald-50 hover:bg-emerald-600 hover:text-white text-emerald-700 text-xs font-bold transition cursor-pointer" title="Markeer als opgelost">
                                            <i class="fa-solid fa-check"></i> Oplossen
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.moderation.report.dismiss', $report) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="px-2 py-1 rounded-lg bg-slate-100 hover:bg-slate-300 text-slate-700 text-xs font-bold transition cursor-pointer" title="Verwerp melding">
                                            <i class="fa-solid fa-xmark"></i> Verwerpen
                                        </button>
                                    </form>
                                    @endif

                                    <form action="{{ route('admin.moderation.report.destroy', $report) }}" method="POST" class="inline"
                                          data-confirm="Weet je zeker dat je deze melding wilt wissen?"
                                          data-confirm-title="Melding Wissen"
                                          data-confirm-btn="Ja, Verwijderen"
                                          data-confirm-type="danger"
                                          data-confirm-icon="fa-trash">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1 rounded-lg bg-rose-50 hover:bg-rose-600 hover:text-white text-rose-700 text-xs font-bold transition cursor-pointer" title="Wis melding">
                                            <i class="fa-solid fa-trash text-xs"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-400 text-xs">
                                Geen meldingen gevonden. Alles is rustig op de afdelingen!
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-slate-100 flex justify-end">
                {{ $reports->links() }}
            </div>
        </div>
    </div>

</div>
@endsection
